<?php
/** Настройки, сотрудники, профиль, резервные копии, история правок, инструкция */
if (!defined('AVTOM')) { http_response_code(404); exit; }
$sec = $a[0];
$u = admin_user();

/* ================= настройки ================= */
if ($sec === 'settings') {
  $s = site();
  $fields = [
    'Контакты на сайте' => [['phone', 'Телефон'], ['email', 'Почта на сайте'], ['hours', 'Часы работы — полностью', 'Ежедневно с 9:00 до 18:00'], ['hours_short', 'Часы работы — коротко', 'Пн–Вс 9:00–18:00'], ['hours_open', 'Открываемся (для поисковиков)', '09:00'], ['hours_close', 'Закрываемся', '18:00']],
    'Адрес' => [['address', 'Адрес полностью'], ['address_short', 'Адрес коротко (в шапке)'], ['region', 'Регион'], ['city', 'Город'], ['street', 'Улица и дом'], ['map_query', 'Адрес для Яндекс Карт', 'Как найти точку в поиске Яндекс Карт']],
    'Реквизиты и документы' => [['org_name', 'Продавец полностью'], ['org_short', 'Коротко'], ['inn', 'ИНН'], ['ogrnip', 'ОГРНИП'], ['legal_address', 'Адрес для документов', 'Пусто — берётся адрес выше'], ['privacy_email', 'Почта для вопросов о персональных данных', 'Пусто — почта на сайте'], ['consent_version', 'Дата редакции документов', 'Меняйте, когда правите политику: дата попадёт в документы и в согласия заявок']],
    'Заявки и аналитика' => [['notify_emails', 'Куда присылать заявки', 'Можно несколько адресов через запятую'], ['metrika_id', 'Номер счётчика Яндекс Метрики', 'Только цифры. Счётчик включается после согласия посетителя на cookie'], ['yandex_verification', 'Код подтверждения Яндекс Вебмастера', 'Из мета-тега yandex-verification, только значение content']],
    'Ссылки' => [['wb_store_url', 'Магазин на Wildberries', 'Необязательно'], ['ozon_store_url', 'Магазин на Ozon', 'Необязательно'], ['vk_url', 'ВКонтакте', 'Необязательно'], ['tg_url', 'Телеграм-канал', 'Необязательно'], ['max_url', 'MAX', 'Необязательно'], ['mp_tags', 'Метки переходов на Wildberries и Ozon', 'Например: utm_source=avto-m.pro&utm_medium=referral&utm_campaign=site. Добавляются к ссылке, если в ней нет своих меток. По ним в кабинетах WB и Ozon видно продажи с сайта. Пусто — без меток']],
  ];
  if (a_require_post()) {
    if (($_POST['action'] ?? '') === 'test_mail') {
      $e = mail_send(site('notify_emails'), 'Проверка почты сайта AVTO-M', '<p>Если вы читаете это письмо, уведомления о заявках настроены правильно.</p><p>' . h(ru_date(now())) . '</p>');
      flash($e ? 'Письмо не ушло: ' . h($e) . '. Проверьте SMTP в app/config.php.' : 'Тестовое письмо отправлено на ' . h(site('notify_emails')) . '.', $e ? 'err' : 'ok');
      redirect('/admin/settings');
    }
    $new = setting_raw('site') ?: [];
    foreach ($fields as $group) foreach ($group as $f) $new[$f[0]] = trim((string)($_POST[$f[0]] ?? ''));
    $new['metrika_id'] = preg_replace('~\D~', '', $new['metrika_id']);
    $new['bodies'] = array_values(array_filter(array_map('trim', preg_split("~\r?\n|,~", (string)($_POST['bodies'] ?? ''))), 'strlen'));
    $errs = [];
    if (!phone_norm($new['phone'])) $errs[] = 'Проверьте телефон.';
    foreach (preg_split('~[,;\s]+~', $new['notify_emails'], -1, PREG_SPLIT_NO_EMPTY) as $em) if (!filter_var($em, FILTER_VALIDATE_EMAIL)) $errs[] = 'Проверьте адрес ' . h($em) . '.';
    if ($new['email'] && !filter_var($new['email'], FILTER_VALIDATE_EMAIL)) $errs[] = 'Проверьте почту на сайте.';
    if ($errs) { flash(implode(' ', $errs), 'err'); }
    else { setting_save('site', $new); version_add('site', 'site', $new, 'publish', 'Настройки сохранены'); flash('Настройки сохранены и уже на сайте.'); }
    redirect('/admin/settings');
  }
  a_head('Настройки', '/settings');
  ?>
  <form method="post" data-dirty-guard><?= csrf_field() ?>
    <div class="a-page-head"><div><h1>Настройки</h1><p class="a-muted">Контакты, реквизиты, почта для заявок и аналитика. Сохраняются сразу.</p></div><div class="a-head-btns"><button class="a-btn a-btn-ghost" type="submit" name="action" value="test_mail" formnovalidate><?= a_icon('mail') ?><span>Проверить почту</span></button><button class="a-btn a-btn-primary" type="submit" name="action" value="save">Сохранить</button></div></div>
    <?php foreach ($fields as $title => $group): ?>
      <section class="a-card"><h2><?= h($title) ?></h2><div class="a-row2">
        <?php foreach ($group as $f): ?><?= a_field($f[0], $f[1], $s[$f[0]] ?? '', ['hint' => isset($f[2]) ? h($f[2]) : '']) ?><?php endforeach; ?>
      </div></section>
    <?php endforeach; ?>
    <section class="a-card"><h2>Кузова для подбора</h2><?= a_field('bodies', 'Список', implode("\n", $s['bodies']), ['type' => 'textarea', 'rows' => 5, 'hint' => 'По одному на строку. Порядок — как в выпадающем списке.']) ?></section>
    <section class="a-card"><h2>Почта (SMTP)</h2><p>Сервер, логин и пароль почты хранятся в файле <code>app/config.php</code> на хостинге — их не видно из админки. Сейчас: <b><?= cfg('smtp_host') ? h(cfg('smtp_host')) . ', ' . h(cfg('smtp_user')) : 'не настроено — письма уходят через почтовую функцию хостинга' ?></b>.</p></section>
    <div class="a-bar"><button class="a-btn a-btn-primary" type="submit" name="action" value="save">Сохранить</button></div>
  </form>
  <?php
  a_foot(); return;
}

/* ================= профиль ================= */
if ($sec === 'account') {
  if (a_require_post()) {
    $p = (string)($_POST['password'] ?? '');
    if (!password_verify((string)($_POST['current'] ?? ''), $u['pass_hash'])) flash('Текущий пароль неверный.', 'err');
    elseif ($e = password_problem($p)) flash($e, 'err');
    elseif ($p !== ($_POST['password2'] ?? '')) flash('Пароли не совпадают.', 'err');
    else { $h = password_hash($p, PASSWORD_DEFAULT); q('UPDATE users SET pass_hash = ?, name = ? WHERE id = ?', [$h, trim($_POST['name'] ?? $u['name']) ?: $u['name'], $u['id']]); $_SESSION['ph'] = substr($h, -12); flash('Пароль изменён. На других устройствах нужно войти заново.'); }
    redirect('/admin/account');
  }
  a_head('Профиль', '/account');
  ?>
  <div class="a-page-head"><div><h1>Профиль</h1><p class="a-muted">Логин: <?= h($u['login']) ?> · <?= $u['role'] === 'owner' ? 'владелец' : 'сотрудник' ?></p></div></div>
  <form class="a-card a-narrow" method="post"><?= csrf_field() ?>
    <?= a_field('name', 'Имя', $u['name']) ?>
    <?= a_field('current', 'Текущий пароль', '', ['type' => 'password', 'required' => true, 'attrs' => ' autocomplete="current-password"']) ?>
    <div class="a-row2"><?= a_field('password', 'Новый пароль — от 10 символов', '', ['type' => 'password', 'required' => true, 'attrs' => ' autocomplete="new-password"']) ?><?= a_field('password2', 'Ещё раз', '', ['type' => 'password', 'required' => true, 'attrs' => ' autocomplete="new-password"']) ?></div>
    <button class="a-btn a-btn-primary" type="submit">Сменить пароль</button>
  </form>
  <?php
  a_foot(); return;
}

/* ================= сотрудники ================= */
if ($sec === 'users') {
  if (!is_owner()) { flash('Раздел доступен только владельцу.', 'err'); redirect('/admin'); }
  $shown = null;
  if (a_require_post()) {
    $act = $_POST['action'] ?? '';
    if ($act === 'add') {
      $login = trim($_POST['login'] ?? ''); $name = trim($_POST['name'] ?? '');
      if ($login === '' || $name === '') flash('Укажите имя и логин.', 'err');
      elseif (q_val('SELECT id FROM users WHERE login = ?', [$login])) flash('Такой логин уже есть.', 'err');
      else { $pw = substr(strtr(base64_encode(random_bytes(12)), '+/=', 'xyz'), 0, 14); q_ins('users', ['login' => $login, 'name' => $name, 'pass_hash' => password_hash($pw, PASSWORD_DEFAULT), 'role' => ($_POST['role'] ?? '') === 'owner' ? 'owner' : 'manager', 'active' => 1, 'created_at' => now()]); $_SESSION['shown_pw'] = [$login, $pw]; }
    }
    $uid = (int)($_POST['id'] ?? 0);
    if ($uid && $uid !== (int)$u['id']) {
      if ($act === 'toggle') q('UPDATE users SET active = 1 - active WHERE id = ?', [$uid]);
      if ($act === 'reset') { $pw = substr(strtr(base64_encode(random_bytes(12)), '+/=', 'xyz'), 0, 14); q('UPDATE users SET pass_hash = ? WHERE id = ?', [password_hash($pw, PASSWORD_DEFAULT), $uid]); $_SESSION['shown_pw'] = [q_val('SELECT login FROM users WHERE id = ?', [$uid]), $pw]; }
    }
    redirect('/admin/users');
  }
  $shown = $_SESSION['shown_pw'] ?? null; unset($_SESSION['shown_pw']);
  a_head('Сотрудники', '/users');
  ?>
  <div class="a-page-head"><div><h1>Сотрудники</h1><p class="a-muted">Сотрудник видит товары, страницы и заявки. Владелец — ещё сотрудников и удаление заявок.</p></div></div>
  <?php if ($shown): ?><div class="a-card a-ok-card"><h2>Пароль для <?= h($shown[0]) ?></h2><p class="a-big-code" data-copy="<?= h($shown[1]) ?>"><?= h($shown[1]) ?></p><p class="a-hint">Показываем один раз. Передайте сотруднику лично или в защищённом мессенджере — не по почте. Сотрудник сменит пароль в профиле.</p></div><?php endif; ?>
  <div class="a-card a-card-flush"><ul class="a-plist"><?php foreach (q_all('SELECT * FROM users ORDER BY id') as $x): ?>
    <li class="a-prow"><span class="a-prow-img a-avatar"><?= h(mb_substr($x['name'], 0, 1)) ?></span><span class="a-prow-main"><b><?= h($x['name']) ?></b><small><?= h($x['login']) ?> · <?= $x['role'] === 'owner' ? 'владелец' : 'сотрудник' ?> · вход: <?= $x['last_login'] ? h(ru_date($x['last_login'])) : 'ещё не входил' ?></small></span>
      <?php if ($x['id'] != $u['id']): ?><form method="post" class="a-btns"><?= csrf_field() ?><input type="hidden" name="id" value="<?= (int)$x['id'] ?>"><button class="a-btn a-btn-ghost a-btn-sm" name="action" value="reset" data-confirm="Сбросить пароль для <?= h($x['login']) ?>?">Новый пароль</button><button class="a-btn a-btn-<?= $x['active'] ? 'ghost' : 'soft' ?> a-btn-sm" name="action" value="toggle"><?= $x['active'] ? 'Отключить' : 'Включить' ?></button></form><?php else: ?><span class="a-pill a-pill-ok">Это вы</span><?php endif; ?></li>
  <?php endforeach; ?></ul></div>
  <form class="a-card" method="post"><?= csrf_field() ?><input type="hidden" name="action" value="add"><h2>Добавить сотрудника</h2>
    <div class="a-row3"><?= a_field('name', 'Имя', '') ?><?= a_field('login', 'Логин', '', ['attrs' => ' autocomplete="off"']) ?><?= a_field('role', 'Роль', 'manager', ['type' => 'select', 'options' => ['manager' => 'Сотрудник', 'owner' => 'Владелец']]) ?></div>
    <button class="a-btn a-btn-primary" type="submit">Добавить — пароль покажем один раз</button></form>
  <?php
  a_foot(); return;
}

/* ================= резервные копии ================= */
if ($sec === 'backups') {
  if (($a[1] ?? '') === 'download') {
    $f = basename((string)($_GET['f'] ?? ''));
    $path = backups_dir() . '/' . $f;
    if (!preg_match('~^avtom-[\d-]+(-db)?\.zip$~', $f) || !is_file($path)) redirect('/admin/backups');
    while (ob_get_level()) ob_end_clean(); header('Content-Type: application/zip'); header('Content-Disposition: attachment; filename="' . $f . '"'); header('Content-Length: ' . filesize($path)); readfile($path); exit;
  }
  if (a_require_post()) {
    try { $n = backup_create(($_POST['what'] ?? '') !== 'db'); flash('Копия создана: ' . h($n)); } catch (Throwable $e) { flash('Не получилось: ' . h($e->getMessage()), 'err'); }
    redirect('/admin/backups');
  }
  a_head('Резервные копии', '/backups');
  $list = backups_list();
  ?>
  <div class="a-page-head"><div><h1>Резервные копии</h1><p class="a-muted">Копия базы и фото создаётся сама раз в сутки, хранятся последние <?= (int)cfg('backup_keep', 14) ?>. Скачивайте копию себе раз в неделю.</p></div>
    <form class="a-head-btns" method="post"><?= csrf_field() ?><button class="a-btn a-btn-ghost" name="what" value="db">Только база</button><button class="a-btn a-btn-primary" name="what" value="all"><?= a_icon('plus') ?>Создать копию сейчас</button></form></div>
  <div class="a-card a-card-flush"><?php if (!$list): ?><p class="a-pad a-muted">Копий пока нет.</p><?php endif; ?><ul class="a-plist"><?php foreach ($list as $b): ?><li class="a-prow"><span class="a-prow-img"><?= a_icon('lock') ?></span><span class="a-prow-main"><b><?= h(date('d.m.Y H:i', $b['time'])) ?></b><small><?= h($b['name']) ?> · <?= number_format($b['size'] / 1048576, 1, ',', ' ') ?> МБ</small></span><a class="a-btn a-btn-soft a-btn-sm" href="/admin/backups/download?f=<?= h(rawurlencode($b['name'])) ?>">Скачать</a></li><?php endforeach; ?></ul></div>
  <div class="a-card"><h2>Как восстановить</h2><ol class="a-mini"><li>Распакуйте архив.</li><li>Через файловый менеджер хостинга замените <code>app/data/avtom.sqlite</code> файлом из папки <code>data</code> архива.</li><li>Скопируйте папку <code>uploads/p</code> из архива в <code>uploads/p</code> на хостинге.</li></ol><p class="a-hint">Подробно — в инструкции, раздел «Резервные копии».</p></div>
  <?php
  a_foot(); return;
}

/* ================= история правок ================= */
if ($sec === 'history') {
  $names = ['product' => 'Товар', 'content' => 'Страница', 'category' => 'Категория', 'brand' => 'Марка', 'site' => 'Настройки', 'cars' => 'Автомобили', 'attributes' => 'Характеристики', 'site_image' => 'Картинка сайта'];
  $rows = q_all('SELECT v.id, v.entity, v.entity_id, v.action, v.user_name, v.note, v.created_at, p.name AS pname FROM versions v LEFT JOIN products p ON v.entity = \'product\' AND p.id = CAST(v.entity_id AS INTEGER) ORDER BY v.id DESC LIMIT 200');
  $ck = content_keys();
  if (a_require_post() && ($_POST['action'] ?? '') === 'restore_product') {
    $v = q_one("SELECT * FROM versions WHERE id = ? AND entity = 'product' AND action = 'delete'", [(int)$_POST['id']]);
    if ($v) {
      $d = json_decode($v['data'], true);
      $sku = $d['sku']; if (q_val('SELECT id FROM products WHERE sku = ?', [$sku])) $sku .= '-восст';
      $slug = $d['slug']; if (q_val('SELECT id FROM products WHERE slug = ?', [$slug])) $slug .= '-' . time();
      $nid = q_ins('products', ['slug' => $slug, 'sku' => $sku, 'sku_norm' => norm_code($sku), 'name' => $d['name'], 'category_id' => q_val('SELECT id FROM categories WHERE id = ?', [$d['category_id']]) ?: null, 'published' => 0, 'created_at' => now(), 'updated_at' => now()]);
      $d['sku'] = $sku; $d['slug'] = $slug; $d['published'] = 0;
      draft_save('product', $nid, $d);
      flash('Товар восстановлен как черновик — проверьте и опубликуйте.'); redirect('/admin/products/' . $nid);
    }
  }
  a_head('История правок', '/history');
  ?>
  <div class="a-page-head"><div><h1>История правок</h1><p class="a-muted">Кто, что и когда опубликовал. Последние 200 записей.</p></div></div>
  <div class="a-card a-card-flush"><ul class="a-timeline">
    <?php foreach ($rows as $r):
      $what = $names[$r['entity']] ?? $r['entity'];
      $label = $r['entity'] === 'product' ? ($r['pname'] ?: json_decode(q_val('SELECT data FROM versions WHERE id = ?', [$r['id']]), true)['name'] ?? '') : ($r['entity'] === 'content' ? ($ck[$r['entity_id']] ?? $r['entity_id']) : '');
      $link = $r['entity'] === 'product' && $r['pname'] ? '/admin/products/' . (int)$r['entity_id'] : ($r['entity'] === 'content' ? '/admin/pages/' . $r['entity_id'] : null); if ($r['entity'] === 'site_image') { $label = site_image_slot($r['entity_id'])[0] ?? $r['entity_id']; $link = '/admin/images'; } ?>
      <li><div><b><?= h($what) ?><?= $label ? ' «' . ($link ? '<a href="' . h($link) . '">' . h($label) . '</a>' : h($label)) . '»' : '' ?> — <?= h(mb_strtolower($r['note'] ?: $r['action'])) ?></b><small><?= h(ru_date($r['created_at'])) ?> · <?= h($r['user_name']) ?></small></div>
        <?php if ($r['entity'] === 'product' && $r['action'] === 'delete'): ?><form method="post"><?= csrf_field() ?><input type="hidden" name="action" value="restore_product"><input type="hidden" name="id" value="<?= (int)$r['id'] ?>"><button class="a-btn a-btn-soft a-btn-sm">Восстановить</button></form><?php endif; ?></li>
    <?php endforeach; ?>
  </ul></div>
  <?php
  a_foot(); return;
}

/* ================= инструкция ================= */
if ($sec === 'help') {
  a_head('Инструкция', '/help');
  $md = is_file(APP_DIR . '/ADMIN_GUIDE.md') ? file_get_contents(APP_DIR . '/ADMIN_GUIDE.md') : 'Инструкция не найдена.';
  echo '<div class="a-page-head"><div><h1>Инструкция</h1><p class="a-muted">Как пользоваться панелью управления.</p></div></div><article class="a-card a-prose">' . md(preg_replace('~^# .*\n~', '', $md)) . '</article>';
  a_foot(); return;
}
