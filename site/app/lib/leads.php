<?php
if (!defined('AVTOM')) { http_response_code(404); exit; }

function lead_kinds() {
  return [
    'availability' => 'Уточнить наличие',
    'cart' => 'Запрос по списку',
    'pick' => 'Помощь с подбором',
    'question' => 'Вопрос',
    'partner' => 'Сотрудничество',
  ];
}

/** Телефон в виде +7 XXX XXX-XX-XX или null */
function phone_norm($p) {
  $d = preg_replace('~\D~', '', (string)$p);
  if (strlen($d) === 11 && ($d[0] === '8' || $d[0] === '7')) $d = substr($d, 1);
  elseif (strlen($d) !== 10) return null;
  if (!preg_match('~^[3-9]~', $d)) return null;
  return '+7 ' . substr($d, 0, 3) . ' ' . substr($d, 3, 3) . '-' . substr($d, 6, 2) . '-' . substr($d, 8, 2);
}

/**
 * Проверка и сохранение заявки. $in — данные формы. Возвращает ['ok'=>true,'id'=>..] или ['ok'=>false,'errors'=>[поле=>текст]].
 * Сервер проверяет обязательные поля, телефон, состав запроса и совместимость указанных деталей с указанной машиной.
 */
function lead_create(array $in) {
  $errors = [];
  $kind = array_key_exists($in['kind'] ?? '', lead_kinds()) ? $in['kind'] : 'question';
  $name = trim(mb_substr(strip_tags((string)($in['name'] ?? '')), 0, 80));
  $phone = phone_norm($in['phone'] ?? '');
  $email = trim(mb_substr((string)($in['email'] ?? ''), 0, 120));
  $comment = trim(mb_substr(strip_tags((string)($in['comment'] ?? '')), 0, 2000));
  $carText = trim(mb_substr(strip_tags((string)($in['car'] ?? '')), 0, 200));
  $company = trim(mb_substr(strip_tags((string)($in['company'] ?? '')), 0, 120));

  if ($name === '') $errors['name'] = 'Как к вам обращаться?';
  if (!$phone) $errors['phone'] = 'Проверьте номер: нужно 10 цифр после +7.';
  if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors['email'] = 'Проверьте адрес почты.';
  if (empty($in['consent'])) $errors['consent'] = 'Нужно согласие на обработку персональных данных.';
  if ($kind === 'partner' && $company === '') $errors['company'] = 'Укажите компанию или сервис.';
  if ($kind === 'pick' && $carText === '' && $comment === '') $errors['car'] = 'Напишите марку, модель и год или VIN-номер.';

  // состав запроса: только существующие опубликованные товары
  $all = products_all();
  $items = [];
  foreach (array_slice((array)($in['items'] ?? []), 0, 50) as $it) {
    $id = (int)(is_array($it) ? ($it['id'] ?? 0) : $it);
    $qty = max(1, min(99, (int)(is_array($it) ? ($it['qty'] ?? 1) : 1)));
    if (!isset($all[$id])) continue;
    $items[$id] = ['id' => $id, 'sku' => $all[$id]['sku'], 'name' => $all[$id]['name'], 'qty' => $qty];
  }
  if (in_array($kind, ['availability', 'cart'], true) && !$items) $errors['items'] = 'Список пуст или товары сняты с продажи — обновите страницу.';

  // совместимость: если указана машина из подбора, отмечаем, что подходит, а что нет
  $car = null;
  if (!empty($in['car_make'])) $car = car_from_query(['make' => $in['car_make'], 'model' => $in['car_model'] ?? '', 'year' => $in['car_year'] ?? '', 'body' => $in['car_body'] ?? '', 'engine' => $in['car_engine'] ?? '']);
  if ($car) {
    foreach ($items as $id => &$it) $it['match'] = compat_match($all[$id], $car);
    unset($it);
    if ($carText === '') $carText = car_label($car);
  }
  if ($errors) return ['ok' => false, 'errors' => $errors];

  if (!rate_hit('lead:' . ip_hash(), 8, 3600)) return ['ok' => false, 'errors' => ['form' => 'Слишком много заявок подряд. Позвоните нам или попробуйте через час.']];

  $utm = [];
  foreach (['utm_source', 'utm_medium', 'utm_campaign', 'utm_content', 'utm_term', 'yclid'] as $k) if (!empty($in[$k])) $utm[$k] = mb_substr((string)$in[$k], 0, 100);
  if ($company !== '') $comment = 'Компания: ' . $company . ($comment !== '' ? "\n" . $comment : '');

  $id = q_ins('leads', [
    'kind' => $kind, 'status' => 'new', 'name' => $name, 'phone' => $phone, 'email' => $email, 'car' => $carText, 'comment' => $comment,
    'items' => json_encode(array_values($items), JSON_UNESCAPED_UNICODE), 'page' => mb_substr((string)($in['page'] ?? ''), 0, 300),
    'utm' => $utm ? json_encode($utm, JSON_UNESCAPED_UNICODE) : null, 'ip_hash' => ip_hash(), 'ua' => mb_substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 250),
    'consent_at' => now(), 'consent_version' => site('consent_version'), 'created_at' => now(), 'updated_at' => now(),
  ]);
  q_ins('lead_events', ['lead_id' => $id, 'at' => now(), 'user_name' => 'Сайт', 'text' => 'Заявка создана']);
  $err = lead_notify($id);
  q('UPDATE leads SET mail_status = ? WHERE id = ?', [$err ? 'Ошибка: ' . mb_substr($err, 0, 200) : 'Отправлено ' . date('d.m H:i'), $id]);
  if ($err) log_err("lead #$id mail: $err");
  return ['ok' => true, 'id' => $id];
}

function lead_notify($id) {
  $l = q_one('SELECT * FROM leads WHERE id = ?', [$id]);
  if (!$l) return 'нет заявки';
  $kinds = lead_kinds();
  $items = json_decode($l['items'] ?: '[]', true);
  $row = fn($k, $v) => $v === '' || $v === null ? '' : '<tr><td style="padding:6px 12px 6px 0;color:#5b6b82;vertical-align:top;white-space:nowrap">' . h($k) . '</td><td style="padding:6px 0;color:#0e1a2b">' . $v . '</td></tr>';
  $list = '';
  foreach ($items as $it) {
    $m = isset($it['match']) ? ($it['match'] === 'yes' ? ' — подходит' : ($it['match'] === 'check' ? ' — уточнить совместимость' : ' — не подходит к указанной машине')) : '';
    $list .= '<li><b>' . h($it['sku']) . '</b> · ' . h($it['name']) . ' × ' . (int)$it['qty'] . h($m) . '</li>';
  }
  $admin = site_url('/admin/leads/' . $id);
  $html = '<div style="font:15px/1.5 Arial,sans-serif;color:#0e1a2b;max-width:620px">'
    . '<h2 style="margin:0 0 4px;font-size:20px">' . h($kinds[$l['kind']] ?? 'Заявка') . ' · №' . $id . '</h2>'
    . '<p style="margin:0 0 16px;color:#5b6b82">' . h(ru_date($l['created_at'])) . '</p><table style="border-collapse:collapse">'
    . $row('Имя', h($l['name'])) . $row('Телефон', '<a href="' . h(phone_href($l['phone'])) . '">' . h($l['phone']) . '</a>')
    . $row('Почта', h($l['email'])) . $row('Автомобиль', h($l['car'])) . $row('Комментарий', nl2br(h($l['comment'])))
    . ($list ? $row('Детали', '<ul style="margin:0;padding-left:18px">' . $list . '</ul>') : '') . $row('Страница', h($l['page']))
    . '</table><p style="margin:20px 0 0"><a href="' . h($admin) . '" style="display:inline-block;background:#0a5cff;color:#fff;padding:10px 18px;border-radius:10px;text-decoration:none">Открыть в админке</a></p></div>';
  $subj = ($kinds[$l['kind']] ?? 'Заявка') . ' №' . $id . ' — ' . $l['name'] . ', ' . $l['phone'];
  return mail_send(site('notify_emails'), $subj, $html, '', $l['email']);
}

function lead_status_label($s) { return site('lead_statuses')[$s] ?? $s; }
