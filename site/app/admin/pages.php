<?php
/** Страницы и блоки сайта: тексты, кнопки, фото, порядок блоков главной, вопросы, документы. Черновик → предпросмотр → публикация. */
if (!defined('AVTOM')) { http_response_code(404); exit; }
$keys = content_keys();
$key = $a[1] ?? '';

/**
 * Схема полей. Типы: text, textarea, md (текст с разметкой), photo, list (строки), repeat (повторяющиеся группы), blocks (порядок блоков главной).
 * Путь «hero.title» — вложенный ключ.
 */
function page_schema($key) {
  $seo = [['h', 'Поисковики'], ['seo_title', 'text', 'Заголовок страницы', ['count' => 70]], ['seo_desc', 'textarea', 'Описание для поиска', ['count' => 160]]];
  $legal = [['title', 'text', 'Заголовок'], ['body', 'md', 'Текст', ['rows' => 26, 'hint' => 'В тексте можно использовать {org_name}, {inn}, {ogrnip}, {address}, {email}, {privacy_email}, {phone}, {site}, {date} — подставятся из «Настроек».']]];
  $s = [
    'home' => [
      ['h', 'Баннер — слайды'],
      ['p', 'Готовый баннер: загрузите картинку целиком (пропорции 2:1, например 1536×768) и укажите ссылку — весь баннер станет кнопкой. В поле «Заголовок» напишите текст с баннера: его прочитают поисковики и экранный диктор. Для телефона можно загрузить отдельный вариант баннера.'],
      ['p', 'Слайд без баннера собирается из цвета, заголовка, текста и кнопок; картинка справа — PNG на прозрачном фоне (машина, деталь).'],
      ['slides', 'repeat', 'Слайды', [['banner_id', 'photo', 'Готовый баннер — картинка целиком'], ['banner_m_id', 'photo', 'Баннер для телефона (необязательно)'], ['theme', 'sel', 'Цвет слайда', ['blue' => 'Синий', 'light' => 'Голубой', 'navy' => 'Тёмно-синий', 'white' => 'Белый']], ['title', 'text', 'Заголовок (у баннера — текст с картинки)'], ['sub', 'text', 'Строка под заголовком'], ['text', 'textarea', 'Текст'], ['chips', 'textarea', 'Плашки (необязательно, каждая с новой строки, до трёх)'], ['btn', 'text', 'Кнопка'], ['url', 'text', 'Ссылка кнопки (у баннера — куда ведёт баннер)'], ['btn2', 'text', 'Вторая кнопка (необязательно)'], ['url2', 'text', 'Ссылка второй кнопки'], ['cat', 'cat', 'Категория (для картинки)'], ['img_id', 'photo', 'Картинка на прозрачном фоне'], ['bg_id', 'photo', 'Фоновое фото для компьютера'], ['bg_m_id', 'photo', 'Фоновое фото для телефона']]],
      ['slider_sec', 'select', 'Смена слайдов', ['options' => ['3' => 'Каждые 3 секунды', '4' => 'Каждые 4 секунды', '5' => 'Каждые 5 секунд', '6' => 'Каждые 6 секунд', '8' => 'Каждые 8 секунд', '10' => 'Каждые 10 секунд', '0' => 'Не листать — только пальцем и стрелками'], 'hint' => 'Пока посетитель держит палец или мышь на баннере, слайд не меняется.']],
      ['h', 'Порядок и видимость блоков'], ['blocks', 'blocks', ''],
      ['h', 'Подбор, каталог, товары'],
      ['row', [['picker.title', 'text', 'Подбор — заголовок'], ['picker.lead', 'text', 'Подбор — текст']]],
      ['row', [['categories.title', 'text', 'Каталог — заголовок'], ['products.title', 'text', 'Товары — заголовок']]],
      ['categories.pick_img', 'photo', 'Плитка «Подбор по автомобилю» — картинка машины на прозрачном фоне'],
      ['h', 'Две плитки после категорий: подбор и заявка'],
      ['row', [['cards.params_title', 'text', 'Подбор — заголовок'], ['cards.params_btn', 'text', 'Подбор — кнопка']]],
      ['row', [['cards.params_text', 'text', 'Подбор — текст'], ['cards.params_url', 'text', 'Подбор — ссылка']]],
      ['cards.params_img', 'photo', 'Подбор — 3D-картинка на прозрачном фоне'],
      ['row', [['cards.lead_title', 'text', 'Заявка — заголовок'], ['cards.lead_btn', 'text', 'Заявка — кнопка']]],
      ['cards.lead_text', 'textarea', 'Заявка — текст'],
      ['cards.lead_img', 'photo', 'Заявка — 3D-картинка на прозрачном фоне'],
      ['h', 'Когда пора менять'],
      ['signs.title', 'text', 'Заголовок'],
      ['signs.text', 'textarea', 'Вступление'],
      ['signs.items', 'repeat', 'Признаки', [['title', 'text', 'Признак'], ['text', 'textarea', 'Пояснение']]],
      ['signs.note', 'textarea', 'Подсказка в жёлтой плашке'],
      ['row', [['signs.btn', 'text', 'Кнопка'], ['signs.url', 'text', 'Ссылка кнопки']]],
      ['h', 'Преимущества'],
      ['advantages.title', 'text', 'Заголовок'],
      ['advantages.items', 'repeat', 'Карточки', [['icon', 'icon', 'Значок'], ['title', 'text', 'Заголовок'], ['text', 'textarea', 'Текст']]],
      ['h', 'О компании и помощь'],
      ['row', [['about.title', 'text', 'О компании — заголовок'], ['about.btn', 'text', 'Текст ссылки']]],
      ['about.text', 'textarea', 'О компании — текст'],
      ['p', 'Картинка здания в этом блоке меняется в разделе «Картинки сайта».'],
      ['row', [['help.title', 'text', 'Карточка «Поможем» — заголовок'], ['help.text', 'text', 'Карточка «Поможем» — текст']]],
      ['h', 'Другие блоки'],
      ['row', [['brands.title', 'text', 'Марки — заголовок'], ['brands.lead', 'text', 'Марки — текст']]],
      ['howto.title', 'text', 'Как купить — заголовок'],
      ['howto.steps', 'repeat', 'Как купить — шаги', [['title', 'text', 'Шаг'], ['text', 'textarea', 'Пояснение']]],
      ['faq.title', 'text', 'Вопросы — заголовок'],
      ['row', [['quiz.badge', 'text', 'Квиз — метка'], ['quiz.title', 'text', 'Квиз — заголовок']]],
      ['quiz.lead', 'textarea', 'Квиз — текст'],
      ['quiz.symptoms', 'repeat', 'Квиз — признаки поломки → категория', [['text', 'text', 'Признак'], ['cat', 'cat', 'Категория']]],
      ...$seo,
      ['seo_h1', 'text', 'Главный заголовок страницы (виден поисковикам; на экране — только если первый слайд без баннера)'],
    ],
    'about' => [['title', 'text', 'Заголовок'], ['lead', 'textarea', 'Подзаголовок'], ['facts', 'repeat', 'Цифры', [['value', 'text', 'Число'], ['label', 'text', 'Подпись']]], ['body', 'md', 'Текст', ['rows' => 16]], ...$seo],
    'partners' => [['title', 'text', 'Заголовок'], ['lead', 'textarea', 'Подзаголовок'], ['cards', 'repeat', 'Четыре карточки', [['icon', 'icon', 'Значок'], ['title', 'text', 'Заголовок'], ['text', 'textarea', 'Текст']]],
      ['h', 'Обращение для партнёров'], ['b2b_title', 'text', 'Заголовок'], ['b2b_text', 'md', 'Текст', ['rows' => 8]], ['b2b_btn', 'text', 'Кнопка'], ...$seo],
    'contacts' => [['title', 'text', 'Заголовок'], ['lead', 'textarea', 'Подзаголовок'], ['note', 'text', 'Подсказка под часами работы'], ['p', 'Телефон, почта, адрес и часы меняются в «Настройках».'], ...$seo],
    'howto' => [['title', 'text', 'Заголовок'], ['lead', 'textarea', 'Подзаголовок'], ['body', 'md', 'Текст', ['rows' => 16]], ['p', 'Три шага вверху страницы берутся из блока «Как купить» на главной.'], ...$seo],
    'popup' => [
      ['p', 'Окно появляется, когда посетитель уводит курсор к закрытию вкладки (на компьютере). На телефоне — по таймеру, если включить. Одному человеку показывается не чаще выбранного срока и не показывается после отправленной заявки. Пишите только правду: реальную акцию с реальным сроком или помощь с подбором.'],
      ['row', [['on', 'select', 'Показывать окно', ['options' => ['1' => 'Да', '0' => 'Нет']]], ['days', 'select', 'Как часто одному посетителю', ['options' => ['1' => 'Раз в день', '3' => 'Раз в 3 дня', '7' => 'Раз в неделю', '30' => 'Раз в месяц']]]]],
      ['row', [['delay', 'select', 'Не раньше чем через', ['options' => ['0' => 'Сразу', '10' => '10 секунд на сайте', '30' => '30 секунд', '60' => '1 минуту']]], ['mobile', 'select', 'На телефоне', ['options' => ['0' => 'Не показывать', '30' => 'Через 30 секунд', '60' => 'Через 1 минуту', '120' => 'Через 2 минуты']]]]],
      ['h', 'Содержимое'],
      ['badge', 'text', 'Метка над заголовком (например, «Акция до 31 декабря»)'],
      ['title', 'text', 'Заголовок'],
      ['text', 'textarea', 'Текст'],
      ['row', [['code', 'text', 'Промокод (необязательно — покажется с кнопкой «Скопировать»)'], ['note', 'text', 'Мелкая приписка (условия акции)']]],
      ['row', [['btn', 'text', 'Главная кнопка'], ['action', 'select', 'Что делает главная кнопка', ['options' => ['lead' => 'Открывает форму «Перезвоните мне»', 'url' => 'Ведёт по ссылке']]]]],
      ['url', 'text', 'Ссылка главной кнопки (если выбрано «Ведёт по ссылке»)'],
      ['row', [['btn2', 'text', 'Вторая кнопка (необязательно)'], ['url2', 'text', 'Ссылка второй кнопки']]],
      ['img_id', 'photo', 'Картинка на прозрачном фоне (3D-иллюстрация, подарок, деталь)'],
    ],
    'faq' => [['items', 'repeat', 'Вопросы и ответы', [['q', 'text', 'Вопрос'], ['a', 'textarea', 'Ответ']]], ['p', 'Вопросы показываются на главной и попадают в разметку для поисковиков. Отвечайте только фактами.']],
    'legal_privacy' => array_merge($legal, $seo), 'legal_consent' => array_merge($legal, $seo), 'legal_cookies' => array_merge($legal, $seo), 'legal_terms' => array_merge($legal, $seo),
  ];
  return $s[$key] ?? [];
}
function arr_get($arr, $path) { foreach (explode('.', $path) as $k) { if (!is_array($arr) || !array_key_exists($k, $arr)) return null; $arr = $arr[$k]; } return $arr; }
function arr_set(&$arr, $path, $v) { $ref = &$arr; foreach (explode('.', $path) as $k) { if (!isset($ref[$k]) || !is_array($ref[$k])) $ref[$k] = $ref[$k] ?? []; $ref = &$ref[$k]; } $ref = $v; }
function fname($path) { $p = explode('.', $path); return 'c[' . implode('][', $p) . ']'; }
const PAGE_ICONS = ['shield' => 'Щит с галочкой', 'truck' => 'Грузовик — доставка', 'kit' => 'Коробка — комплект', 'chat' => 'Сообщение', 'support' => 'Наушники — поддержка', 'price' => 'Ценник', 'car' => 'Машина', 'gauge' => 'Ползунки — подбор', 'wrench' => 'Шестерёнка с ключом', 'check-circle' => 'Гарантия', 'clock' => 'Заказ оформлен', 'building' => 'Метка на карте', 'payment' => 'Кошелёк — оплата', 'sparkle' => 'Избранное'];

/* ---------- список ---------- */
if ($key === '' || !isset($keys[$key])) {
  a_head('Страницы', '/pages');
  ?>
  <div class="a-page-head"><div><h1>Страницы и тексты</h1><p class="a-muted">Правки сохраняются черновиком. Посмотрите предпросмотр и опубликуйте.</p></div></div>
  <div class="a-card a-card-flush"><ul class="a-plist">
    <?php foreach ($keys as $k => $t): $di = draft_info('content', $k); $pub = setting_raw('content.' . $k); ?>
      <li class="a-prow"><span class="a-prow-img"><?= a_icon(strpos($k, 'legal') === 0 ? 'lock' : 'clipboard') ?></span><a class="a-prow-main" href="/admin/pages/<?= h($k) ?>"><b><?= h($t) ?></b><small><?= $di ? 'Черновик от ' . h(ru_date($di['updated_at'])) : ($pub ? 'Опубликовано' : 'Стартовый текст') ?></small></a><?= $di ? '<span class="a-pill a-pill-warn">Есть изменения</span>' : '<span class="a-pill a-pill-ok">На сайте</span>' ?></li>
    <?php endforeach; ?>
  </ul></div>
  <?php
  a_foot(); return;
}

$schema = page_schema($key);
$urlMap = ['home' => '/', 'about' => '/about', 'partners' => '/partners', 'contacts' => '/contacts', 'howto' => '/how-to-buy', 'faq' => '/', 'popup' => '/?xpop=1', 'legal_privacy' => '/legal/privacy', 'legal_consent' => '/legal/consent', 'legal_cookies' => '/legal/cookies', 'legal_terms' => '/legal/terms'];

/* ---------- история ---------- */
if (($a[2] ?? '') === 'history') {
  if (a_require_post()) {
    $v = q_one("SELECT * FROM versions WHERE id = ? AND entity = 'content' AND entity_id = ?", [(int)$_POST['version'], $key]);
    if ($v) { draft_save('content', $key, json_decode($v['data'], true)); flash('Версия загружена в черновик. Проверьте и опубликуйте.'); }
    redirect('/admin/pages/' . $key);
  }
  a_head('История: ' . $keys[$key], '/pages');
  $vs = versions_list('content', $key, 50);
  ?>
  <div class="a-page-head"><div><a class="a-back" href="/admin/pages/<?= h($key) ?>"><?= a_icon('arrow-left') ?><?= h($keys[$key]) ?></a><h1>История публикаций</h1></div></div>
  <div class="a-card a-card-flush"><?php if (!$vs): ?><p class="a-pad a-muted">Публикаций ещё не было — на сайте стартовый текст.</p><?php endif; ?><ul class="a-timeline"><?php foreach ($vs as $i => $v): ?><li><div><b><?= h($v['note']) ?></b><small><?= h(ru_date($v['created_at'])) ?> · <?= h($v['user_name']) ?></small></div><?php if ($i): ?><form method="post"><?= csrf_field() ?><input type="hidden" name="version" value="<?= (int)$v['id'] ?>"><button class="a-btn a-btn-soft a-btn-sm">Вернуть</button></form><?php else: ?><span class="a-pill a-pill-ok">Текущая</span><?php endif; ?></li><?php endforeach; ?></ul></div>
  <?php
  a_foot(); return;
}

/* ---------- сохранение ---------- */
$data = draft_get('content', $key) ?? (setting_raw('content.' . $key) ? content_merge(content_defaults($key), setting_raw('content.' . $key)) : content_defaults($key));
if (a_require_post()) {
  $act = $_POST['action'] ?? 'save';
  if ($act === 'discard') { draft_drop('content', $key); flash('Черновик удалён.'); redirect('/admin/pages/' . $key); }
  if ($act === 'reset') { draft_save('content', $key, content_defaults($key)); flash('В черновик загружен стартовый текст. Опубликуйте, если всё верно.'); redirect('/admin/pages/' . $key); }
  $in = $_POST['c'] ?? [];
  $walk = function ($fields) use (&$walk, &$data, $in) {
    foreach ($fields as $f) {
      if ($f[0] === 'h' || $f[0] === 'p') continue;
      if ($f[0] === 'row') { $walk($f[1]); continue; }
      [$path, $type] = $f;
      $v = arr_get($in, $path);
      if ($type === 'list') $v = array_values(array_filter(array_map('trim', preg_split("~\r?\n~", (string)$v)), 'strlen'));
      elseif ($type === 'repeat') {
        $photoKeys = array_column(array_filter($f[3] ?? [], fn($x) => $x[1] === 'photo'), 0);
        // строка пустая, если не заполнено ни одно текстовое поле (значок и категория выбраны всегда — их не считаем)
        $textKeys = array_column(array_filter($f[3] ?? [], fn($x) => in_array($x[1], ['text', 'textarea'], true)), 0);
        $v = array_values(array_filter((array)$v, function ($r) use ($textKeys) { if (!is_array($r)) return false; foreach ($textKeys as $k) if (trim((string)($r[$k] ?? '')) !== '') return true; return false; }));
        foreach ($v as &$r) foreach ($r as $k => $x) $r[$k] = in_array($k, $photoKeys, true) ? ((int)$x ?: null) : trim((string)$x);
        unset($r);
      }
      elseif ($type === 'blocks') { $out = []; foreach ((array)$v as $b) if (isset(home_block_names()[$b['id'] ?? ''])) $out[] = ['id' => $b['id'], 'on' => !empty($b['on'])]; $v = $out; }
      elseif ($type === 'photo') $v = (int)$v ?: null;
      else $v = trim(str_replace("\r", '', (string)$v));
      arr_set($data, $path, $v);
    }
  };
  $walk($schema);
  if ($act === 'publish') { content_publish($key, $data); flash('Опубликовано. <a href="' . h($urlMap[$key]) . '" target="_blank">Открыть страницу</a>'); redirect('/admin/pages/' . $key); }
  draft_save('content', $key, $data);
  if ($act === 'preview') redirect($urlMap[$key] . (strpos($urlMap[$key], '?') ? '&' : '?') . 'preview=1');
  flash('Черновик сохранён. На сайте пока прежняя версия.');
  redirect('/admin/pages/' . $key);
}

$di = draft_info('content', $key);
a_head($keys[$key], '/pages');
?>
<form class="a-edit" method="post" data-dirty-guard><?= csrf_field() ?>
  <div class="a-page-head"><div><a class="a-back" href="/admin/pages"><?= a_icon('arrow-left') ?>Страницы</a><h1><?= h($keys[$key]) ?></h1><p class="a-muted"><?= $di ? '<span class="a-pill a-pill-warn">Есть изменения</span> черновик от ' . h(ru_date($di['updated_at'])) . ' · на сайте опубликованная версия' : 'Изменения сохраняются черновиком' ?></p></div>
    <div class="a-head-btns"><a class="a-btn a-btn-ghost" href="/admin/pages/<?= h($key) ?>/history"><?= a_icon('clock') ?><span>История</span></a><a class="a-btn a-btn-ghost" href="<?= h($urlMap[$key]) ?>" target="_blank"><?= a_icon('external') ?><span>На сайте</span></a></div></div>
  <div class="a-edit-grid">
    <div class="a-edit-main"><section class="a-card">
      <?php
      $render = function ($fields) use (&$render, $data) {
        foreach ($fields as $f) {
          if ($f[0] === 'h') { echo '<h2 class="a-sec-h">' . h($f[1]) . '</h2>'; continue; }
          if ($f[0] === 'p') { echo '<p class="a-hint">' . h($f[1]) . '</p>'; continue; }
          if ($f[0] === 'row') { echo '<div class="a-row2">'; $render($f[1]); echo '</div>'; continue; }
          [$path, $type, $label] = $f; $o = $f[3] ?? [];
          $v = arr_get($data, $path);
          $attrs = isset($o['count']) ? ' data-count="' . (int)$o['count'] . '"' : '';
          switch ($type) {
            case 'text': echo a_field(fname($path), $label, $v, ['attrs' => $attrs, 'hint' => $o['hint'] ?? '']); break;
            case 'select': echo '<div class="a-field"><label for="f-' . h(str_replace('.', '-', $path)) . '">' . h($label) . '</label><select id="f-' . h(str_replace('.', '-', $path)) . '" name="' . h(fname($path)) . '">'; foreach ($o['options'] as $ok => $ov) echo '<option value="' . h($ok) . '"' . ((string)$v === (string)$ok ? ' selected' : '') . '>' . h($ov) . '</option>'; echo '</select>' . (!empty($o['hint']) ? '<p class="a-hint">' . h($o['hint']) . '</p>' : '') . '</div>'; break;
            case 'textarea': echo a_field(fname($path), $label, $v, ['type' => 'textarea', 'rows' => $o['rows'] ?? 3, 'attrs' => $attrs]); break;
            case 'md': echo a_field(fname($path), $label, $v, ['type' => 'textarea', 'rows' => $o['rows'] ?? 10, 'hint' => ($o['hint'] ?? '') . ' Разметка: пустая строка — абзац, «## » — подзаголовок, «- » — пункт списка, **жирный**, [текст](ссылка).']); break;
            case 'list': echo a_field(fname($path), $label, implode("\n", (array)$v), ['type' => 'textarea', 'rows' => 3, 'hint' => 'Каждый пункт — с новой строки.']); break;
            case 'photo': echo a_photo_pick(fname($path), $v, $label); if (!empty($o['hint'])) echo '<p class="a-hint">' . h($o['hint']) . '</p>'; break;
            case 'blocks':
              $names = home_block_names(); $have = array_column((array)$v, 'id');
              foreach (array_keys($names) as $n) if (!in_array($n, $have, true)) $v[] = ['id' => $n, 'on' => false];
              echo '<ul class="a-blocks" data-sortable-local>';
              foreach ($v as $i => $b) { if (!isset($names[$b['id']])) continue;
                echo '<li class="a-block" draggable="true"><span class="a-drag">' . a_icon('dots') . '</span><input type="hidden" name="c[blocks][' . $i . '][id]" value="' . h($b['id']) . '"><b>' . h($names[$b['id']]) . '</b>'
                  . '<span class="a-move"><button type="button" class="a-icon-btn" data-move="-1" aria-label="Выше">' . a_icon('chevron') . '</button><button type="button" class="a-icon-btn" data-move="1" aria-label="Ниже">' . a_icon('chevron') . '</button></span>'
                  . '<label class="a-switch a-switch-sm"><input type="checkbox" name="c[blocks][' . $i . '][on]" value="1"' . (!empty($b['on']) ? ' checked' : '') . '><span class="a-sw"></span><span class="a-sr-m">Показывать</span></label></li>'; }
              echo '</ul><p class="a-hint">Перетащите или нажмите стрелки, чтобы поменять порядок. Переключатель скрывает блок.</p>';
              break;
            case 'repeat':
              $sub = $o; $rows = array_values((array)$v);
              echo '<div class="a-field"><span class="a-lbl">' . h($label) . '</span><div class="a-repeat a-repeat-cards" data-repeat data-sortable-local>';
              foreach (array_merge($rows, [array_fill_keys(array_column($sub, 0), '')]) as $i => $r) {
                echo '<div class="a-rep-card" data-rep-row draggable="true"><span class="a-drag">' . a_icon('dots') . '</span><div class="a-rep-f">';
                foreach ($sub as $sd) { [$sk, $st, $sl] = $sd;
                  $nm = fname($path) . '[' . $i . '][' . $sk . ']'; $sv = $r[$sk] ?? '';
                  if ($st === 'photo') { echo a_photo_pick($nm, (int)$sv, $sl); }
                  elseif ($st === 'cat') { echo '<div class="a-field"><label>' . h($sl) . '</label><select name="' . h($nm) . '"><option value="">—</option>'; foreach (categories_all(false) as $cc) echo '<option value="' . h($cc['slug']) . '"' . ($sv === $cc['slug'] ? ' selected' : '') . '>' . h($cc['name']) . '</option>'; echo '</select></div>'; }
                  elseif ($st === 'sel') { echo '<div class="a-field"><label>' . h($sl) . '</label><select name="' . h($nm) . '">'; foreach ($sd[3] as $ok => $ov) echo '<option value="' . h($ok) . '"' . ((string)$sv === (string)$ok ? ' selected' : '') . '>' . h($ov) . '</option>'; echo '</select></div>'; }
                  elseif ($st === 'icon') { echo '<div class="a-field a-field-icon"><label>' . h($sl) . '</label><select name="' . h($nm) . '">'; foreach (PAGE_ICONS as $ik => $iv) echo '<option value="' . $ik . '"' . ($sv === $ik ? ' selected' : '') . '>' . $iv . '</option>'; echo '</select></div>'; }
                  elseif ($st === 'textarea') echo '<div class="a-field"><label>' . h($sl) . '</label><textarea name="' . h($nm) . '" rows="2">' . h($sv) . '</textarea></div>';
                  else echo '<div class="a-field"><label>' . h($sl) . '</label><input name="' . h($nm) . '" value="' . h($sv) . '"></div>';
                }
                echo '</div><button type="button" class="a-icon-btn" data-rep-del aria-label="Убрать">' . a_icon('trash') . '</button></div>';
              }
              echo '</div><button type="button" class="a-btn a-btn-soft a-btn-sm" data-rep-add>' . a_icon('plus') . 'Добавить</button></div>';
              break;
          }
        }
      };
      $render($schema);
      ?>
    </section></div>
    <aside class="a-edit-side"><section class="a-card a-sticky-actions"><h2>Публикация</h2>
      <div class="a-actions">
        <button class="a-btn a-btn-primary a-btn-block" type="submit" name="action" value="publish"><?= a_icon('check') ?>Опубликовать</button>
        <button class="a-btn a-btn-soft a-btn-block" type="submit" name="action" value="save" data-hotkey="s">Сохранить черновик</button>
        <button class="a-btn a-btn-ghost a-btn-block" type="submit" name="action" value="preview" formtarget="_blank"><?= a_icon('external') ?>Предпросмотр</button>
      </div>
      <div class="a-actions-more"><?php if ($di): ?><button class="a-link-btn" type="submit" name="action" value="discard" data-confirm="Удалить черновик?">Отменить черновик</button><?php endif; ?><button class="a-link-btn" type="submit" name="action" value="reset" data-confirm="Загрузить в черновик стартовый текст страницы?">Вернуть стартовый текст</button></div>
    </section></aside>
  </div>
</form>
<?php a_foot();
