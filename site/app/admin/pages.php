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
      ['h', 'Первый экран'],
      ['hero.eyebrow', 'text', 'Надпись над заголовком'], ['hero.title', 'text', 'Заголовок'], ['hero.lead', 'textarea', 'Текст'],
      ['row', [['hero.btn1_text', 'text', 'Главная кнопка'], ['hero.btn1_url', 'text', 'Ссылка кнопки']]],
      ['row', [['hero.btn2_text', 'text', 'Вторая кнопка'], ['hero.btn2_url', 'text', 'Ссылка второй кнопки']]],
      ['hero.pills', 'list', 'Пункты с галочкой'],
      ['hero.photo_id', 'photo', 'Фото на фоне', ['hint' => 'Необязательно. Без фото справа показываются карточки товаров. Фото без надписей, горизонтальное, от 1600 точек.']],
      ['h', 'Порядок и видимость блоков'], ['blocks', 'blocks', ''],
      ['h', 'Тексты блоков'],
      ['row', [['picker.title', 'text', 'Подбор — заголовок'], ['picker.lead', 'text', 'Подбор — текст']]],
      ['row', [['categories.title', 'text', 'Каталог — заголовок'], ['brands.title', 'text', 'Марки — заголовок']]],
      ['row', [['products.title', 'text', 'Товары — заголовок'], ['products.lead', 'text', 'Товары — текст']]],
      ['brands.lead', 'text', 'Марки — текст'],
      ['howto.title', 'text', 'Как купить — заголовок'],
      ['howto.steps', 'repeat', 'Шаги', [['title', 'text', 'Шаг'], ['text', 'textarea', 'Пояснение']]],
      ['advantages.title', 'text', 'Преимущества — заголовок'],
      ['advantages.items', 'repeat', 'Преимущества', [['icon', 'icon', 'Значок'], ['title', 'text', 'Заголовок'], ['text', 'textarea', 'Текст']]],
      ['row', [['about.title', 'text', 'О компании — заголовок'], ['about.btn', 'text', 'Текст кнопки']]],
      ['about.text', 'textarea', 'О компании — текст'],
      ['faq.title', 'text', 'Вопросы — заголовок'],
      ['row', [['help.title', 'text', 'Помощь — заголовок'], ['help.text', 'text', 'Помощь — текст']]],
      ...$seo,
    ],
    'about' => [['title', 'text', 'Заголовок'], ['lead', 'textarea', 'Подзаголовок'], ['facts', 'repeat', 'Цифры', [['value', 'text', 'Число'], ['label', 'text', 'Подпись']]], ['body', 'md', 'Текст', ['rows' => 16]], ...$seo],
    'partners' => [['title', 'text', 'Заголовок'], ['lead', 'textarea', 'Подзаголовок'], ['cards', 'repeat', 'Четыре карточки', [['icon', 'icon', 'Значок'], ['title', 'text', 'Заголовок'], ['text', 'textarea', 'Текст']]],
      ['h', 'Обращение для партнёров'], ['b2b_title', 'text', 'Заголовок'], ['b2b_text', 'md', 'Текст', ['rows' => 8]], ['b2b_btn', 'text', 'Кнопка'], ...$seo],
    'contacts' => [['title', 'text', 'Заголовок'], ['lead', 'textarea', 'Подзаголовок'], ['note', 'text', 'Подсказка под часами работы'], ['p', 'Телефон, почта, адрес и часы меняются в «Настройках».'], ...$seo],
    'howto' => [['title', 'text', 'Заголовок'], ['lead', 'textarea', 'Подзаголовок'], ['body', 'md', 'Текст', ['rows' => 16]], ['p', 'Три шага вверху страницы берутся из блока «Как купить» на главной.'], ...$seo],
    'faq' => [['items', 'repeat', 'Вопросы и ответы', [['q', 'text', 'Вопрос'], ['a', 'textarea', 'Ответ']]], ['p', 'Вопросы показываются на главной и попадают в разметку для поисковиков. Отвечайте только фактами.']],
    'legal_privacy' => array_merge($legal, $seo), 'legal_consent' => array_merge($legal, $seo), 'legal_cookies' => array_merge($legal, $seo), 'legal_terms' => array_merge($legal, $seo),
  ];
  return $s[$key] ?? [];
}
function arr_get($arr, $path) { foreach (explode('.', $path) as $k) { if (!is_array($arr) || !array_key_exists($k, $arr)) return null; $arr = $arr[$k]; } return $arr; }
function arr_set(&$arr, $path, $v) { $ref = &$arr; foreach (explode('.', $path) as $k) { if (!isset($ref[$k]) || !is_array($ref[$k])) $ref[$k] = $ref[$k] ?? []; $ref = &$ref[$k]; } $ref = $v; }
function fname($path) { $p = explode('.', $path); return 'c[' . implode('][', $p) . ']'; }
const PAGE_ICONS = ['price' => 'Ценник', 'shield' => 'Щит', 'kit' => 'Коробка', 'chat' => 'Сообщение', 'car' => 'Машина', 'truck' => 'Доставка', 'wrench' => 'Ключ', 'check-circle' => 'Галочка', 'clock' => 'Часы', 'building' => 'Здание', 'gauge' => 'Датчик', 'sparkle' => 'Звёздочка'];

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
$urlMap = ['home' => '/', 'about' => '/about', 'partners' => '/partners', 'contacts' => '/contacts', 'howto' => '/how-to-buy', 'faq' => '/', 'legal_privacy' => '/legal/privacy', 'legal_consent' => '/legal/consent', 'legal_cookies' => '/legal/cookies', 'legal_terms' => '/legal/terms'];

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
      elseif ($type === 'repeat') { $v = array_values(array_filter((array)$v, fn($r) => is_array($r) && array_filter(array_map('trim', array_map('strval', $r))))); foreach ($v as &$r) $r = array_map(fn($x) => trim((string)$x), $r); unset($r); }
      elseif ($type === 'blocks') { $out = []; foreach ((array)$v as $b) if (isset(home_block_names()[$b['id'] ?? ''])) $out[] = ['id' => $b['id'], 'on' => !empty($b['on'])]; $v = $out; }
      elseif ($type === 'photo') $v = (int)$v ?: null;
      else $v = trim(str_replace("\r", '', (string)$v));
      arr_set($data, $path, $v);
    }
  };
  $walk($schema);
  if ($act === 'publish') { content_publish($key, $data); flash('Опубликовано. <a href="' . h($urlMap[$key]) . '" target="_blank">Открыть страницу</a>'); redirect('/admin/pages/' . $key); }
  draft_save('content', $key, $data);
  if ($act === 'preview') redirect($urlMap[$key] . '?preview=1');
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
                foreach ($sub as [$sk, $st, $sl]) {
                  $nm = fname($path) . '[' . $i . '][' . $sk . ']'; $sv = $r[$sk] ?? '';
                  if ($st === 'icon') { echo '<div class="a-field a-field-icon"><label>' . h($sl) . '</label><select name="' . h($nm) . '">'; foreach (PAGE_ICONS as $ik => $iv) echo '<option value="' . $ik . '"' . ($sv === $ik ? ' selected' : '') . '>' . $iv . '</option>'; echo '</select></div>'; }
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
