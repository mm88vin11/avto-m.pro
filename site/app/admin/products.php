<?php
if (!defined('AVTOM')) { http_response_code(404); exit; }
$sub = $a[1] ?? '';

/* ================= список ================= */
if ($sub === '') {
  $qs = trim((string)($_GET['q'] ?? ''));
  $fc = (int)($_GET['cat'] ?? 0);
  $fs = (string)($_GET['st'] ?? '');
  $rows = q_all('SELECT p.*, (SELECT ph.file || \'|\' || ph.ext FROM product_photos pp JOIN photos ph ON ph.id = pp.photo_id WHERE pp.product_id = p.id ORDER BY pp.sort LIMIT 1) AS ph,
    (SELECT COUNT(*) FROM compat c WHERE c.product_id = p.id) AS nc, (SELECT updated_at FROM drafts d WHERE d.entity = \'product\' AND d.entity_id = CAST(p.id AS TEXT)) AS draft_at
    FROM products p ORDER BY p.sort, p.id');
  if ($qs !== '') {
    $code = norm_code($qs); $qn = mb_strtolower($qs);
    $rows = array_filter($rows, fn($p) => ($code !== '' && (strpos((string)$p['sku_norm'], $code) !== false || strpos(' ' . $p['oem_norm'], $code) !== false)) || mb_strpos(mb_strtolower($p['name']), $qn) !== false);
  }
  if ($fc) $rows = array_filter($rows, fn($p) => $p['category_id'] == $fc);
  if ($fs === 'pub') $rows = array_filter($rows, fn($p) => $p['published'] && !$p['draft_at']);
  if ($fs === 'draft') $rows = array_filter($rows, fn($p) => $p['draft_at']);
  if ($fs === 'hidden') $rows = array_filter($rows, fn($p) => !$p['published']);
  if ($fs === 'nolinks') $rows = array_filter($rows, fn($p) => !$p['wb_url'] && !$p['ozon_url']);
  $cats = categories_all(false);
  a_head('Товары', '/products');
  ?>
  <div class="a-page-head"><div><h1>Товары</h1><p class="a-muted"><?= count($rows) ?> <?= plural(count($rows), 'товар', 'товара', 'товаров') ?></p></div>
    <div class="a-head-btns">
      <a class="a-btn a-btn-ghost" href="/admin/products/export"><?= a_icon('external') ?><span>Выгрузить в Excel</span></a>
      <a class="a-btn a-btn-soft" href="/admin/products/import"><?= a_icon('plus') ?><span>Загрузить таблицу</span></a>
      <a class="a-btn a-btn-primary" href="/admin/products/new"><?= a_icon('plus') ?>Добавить товар</a>
    </div></div>
  <form class="a-filters" method="get" action="/admin/products" data-autosubmit>
    <label class="a-search"><?= a_icon('search') ?><input type="search" name="q" value="<?= h($qs) ?>" placeholder="Артикул, номер или название" autofocus></label>
    <select name="cat" aria-label="Категория"><option value="">Все категории</option><?php foreach ($cats as $c): ?><option value="<?= (int)$c['id'] ?>"<?= $fc == $c['id'] ? ' selected' : '' ?>><?= h($c['name']) ?></option><?php endforeach; ?></select>
    <select name="st" aria-label="Статус"><?php foreach (['' => 'Любой статус', 'pub' => 'На сайте', 'draft' => 'Есть черновик', 'hidden' => 'Скрытые', 'nolinks' => 'Без ссылок на маркетплейсы'] as $k => $v): ?><option value="<?= $k ?>"<?= $fs === $k ? ' selected' : '' ?>><?= $v ?></option><?php endforeach; ?></select>
  </form>
  <?php if (!$rows): ?><div class="a-card a-empty"><p>Ничего не нашли.</p><a class="a-btn a-btn-primary" href="/admin/products/new">Добавить товар</a></div>
  <?php else: ?>
  <div class="a-card a-card-flush">
    <ul class="a-plist" data-sortable="products">
      <?php foreach ($rows as $p): [$pf, $pe] = $p['ph'] ? explode('|', $p['ph']) : [null, null]; ?>
      <li class="a-prow" data-id="<?= (int)$p['id'] ?>">
        <span class="a-drag" title="Перетащите, чтобы изменить порядок" aria-hidden="true"><?= a_icon('dots') ?></span>
        <a class="a-prow-img" href="/admin/products/<?= (int)$p['id'] ?>"><?php if ($pf): ?><img src="<?= h(img_url(['file' => $pf, 'ext' => $pe], 400)) ?>" alt="" loading="lazy"><?php else: ?><?= a_icon('photo') ?><?php endif; ?></a>
        <a class="a-prow-main" href="/admin/products/<?= (int)$p['id'] ?>"><b><?= h($p['name']) ?></b><small><?= h($p['sku']) ?> · <?= h($cats[$p['category_id']]['name'] ?? 'без категории') ?> · <?= (int)$p['nc'] ?> <?= plural($p['nc'], 'авто', 'авто', 'авто') ?></small></a>
        <span class="a-prow-mp"><span class="a-mp a-mp-wb<?= $p['wb_url'] ? ' on' : '' ?>" title="<?= $p['wb_url'] ? 'Есть ссылка на Wildberries' : 'Нет ссылки на Wildberries' ?>">WB</span><span class="a-mp a-mp-oz<?= $p['ozon_url'] ? ' on' : '' ?>" title="<?= $p['ozon_url'] ? 'Есть ссылка на Ozon' : 'Нет ссылки на Ozon' ?>">Oz</span></span>
        <?= a_status_badge($p, (bool)$p['draft_at']) ?>
      </li>
      <?php endforeach; ?>
    </ul>
  </div>
  <p class="a-hint">Порядок в списке = порядок на сайте («Сначала популярные»). Перетащите строку за значок слева.</p>
  <?php endif;
  a_foot();
  return;
}

/* ================= порядок (перетаскивание) ================= */
if ($sub === 'order') {
  if (!a_require_post()) json_out(['ok' => false], 405);
  foreach (array_values((array)($_POST['ids'] ?? [])) as $i => $id) q('UPDATE products SET sort = ? WHERE id = ?', [($i + 1) * 10, (int)$id]);
  json_out(['ok' => true]);
}

/* ================= выгрузка ================= */
if ($sub === 'export') {
  $attrs = attributes_all();
  header('Content-Type: text/csv; charset=utf-8');
  header('Content-Disposition: attachment; filename="avto-m-tovary-' . date('Y-m-d') . '.csv"');
  $out = fopen('php://output', 'w');
  fwrite($out, "\xEF\xBB\xBF");
  $head = ['Артикул', 'Название', 'Категория', 'Производитель', 'Оригинальные номера', 'В комплекте', 'Ссылка Wildberries', 'Ссылка Ozon', 'Цена от', 'Наличие', 'На сайте'];
  foreach ($attrs as $at) $head[] = $at['name'] . ($at['unit'] ? ', ' . $at['unit'] : '');
  $head[] = 'Совместимость';
  fputcsv($out, $head, ';');
  $cats = categories_all(false); $brands = brands_all(false);
  foreach (products_all(true) as $p) {
    $row = [$p['sku'], $p['name'], $cats[$p['category_id']]['name'] ?? '', $brands[$p['brand_id']]['name'] ?? '', $p['oem'], $p['kit'], $p['wb_url'], $p['ozon_url'], $p['price_from'], stock_label($p['stock']), $p['published'] ? 'да' : 'нет'];
    foreach ($attrs as $at) $row[] = isset($p['attrs'][$at['code']]) ? (is_numeric($p['attrs'][$at['code']]) ? fmt_num($p['attrs'][$at['code']]) : $p['attrs'][$at['code']]) : '';
    $row[] = implode('; ', array_map(fn($c) => trim($c['make_name'] . ' ' . $c['model_name'] . ' ' . years_label($c['year_from'], $c['year_to']) . ' ' . implode('/', $c['bodies']) . ' ' . implode('/', $c['engines'])), $p['compat']));
    fputcsv($out, $row, ';');
  }
  exit;
}

/* ================= загрузка таблицы ================= */
if ($sub === 'import') {
  $report = null;
  if (a_require_post()) {
    $f = $_FILES['csv']['tmp_name'] ?? '';
    if (!$f || !is_uploaded_file($f)) flash('Выберите файл CSV.', 'err');
    else {
      $raw = file_get_contents($f);
      if (!mb_check_encoding($raw, 'UTF-8')) $raw = mb_convert_encoding($raw, 'UTF-8', 'Windows-1251');
      $raw = preg_replace('~^\xEF\xBB\xBF~', '', $raw);
      $lines = array_map(fn($l) => str_getcsv($l, substr_count($raw, ';') >= substr_count($raw, ',') ? ';' : ','), preg_split("~\r?\n~", trim($raw)));
      $head = array_map(fn($x) => mb_strtolower(trim($x)), array_shift($lines));
      $col = function ($row, $names) use ($head) { foreach ($names as $n) { $i = array_search($n, $head, true); if ($i !== false) return trim((string)($row[$i] ?? '')); } return null; };
      $upd = 0; $new = 0; $errs = [];
      foreach ($lines as $n => $row) {
        if (!array_filter($row)) continue;
        $sku = $col($row, ['артикул', 'sku']);
        if (!$sku) { $errs[] = 'Строка ' . ($n + 2) . ': нет артикула'; continue; }
        $id = q_val('SELECT id FROM products WHERE sku = ?', [$sku]);
        $d = $id ? (draft_get('product', $id) ?: product_snapshot($id)) : ['sku' => $sku, 'name' => '', 'category_id' => null, 'attrs' => [], 'photos' => [], 'compat' => [], 'published' => 0, 'stock' => 'request'];
        $map = ['name' => ['название', 'наименование'], 'oem' => ['оригинальные номера', 'оем', 'oem'], 'kit' => ['в комплекте'], 'wb_url' => ['ссылка wildberries', 'ссылка wb', 'cсылка wb', 'wb'], 'ozon_url' => ['ссылка ozon', 'ozon'], 'price_from' => ['цена от', 'цена']];
        foreach ($map as $k => $names) { $v = $col($row, $names); if ($v !== null && $v !== '') $d[$k] = in_array($k, ['wb_url', 'ozon_url'], true) ? mp_clean($v) : $v; }
        $cv = $col($row, ['категория']); if ($cv) { $cid = q_val('SELECT id FROM categories WHERE name = ? COLLATE NOCASE', [$cv]); if ($cid) $d['category_id'] = $cid; }
        $bv = $col($row, ['производитель', 'бренд']); if ($bv) { $bid = q_val('SELECT id FROM brands WHERE name = ? COLLATE NOCASE', [$bv]); if ($bid) $d['brand_id'] = $bid; }
        $sv = mb_strtolower((string)$col($row, ['наличие'])); if ($sv) $d['stock'] = strpos($sv, 'налич') === 0 && strpos($sv, 'запрос') === false ? 'in' : (strpos($sv, 'заказ') !== false ? 'order' : 'request');
        foreach (attributes_all() as $at) { $v = $col($row, [mb_strtolower($at['name'] . ($at['unit'] ? ', ' . $at['unit'] : '')), mb_strtolower($at['name'])]); if ($v !== null && $v !== '') $d['attrs'][$at['code']] = str_replace([' ', ','], ['', '.'], $v); }
        if (!$id) {
          if (empty($d['name'])) { $errs[] = "Строка " . ($n + 2) . " ($sku): нет названия"; continue; }
          if (empty($d['category_id'])) { $first = q_val('SELECT id FROM categories ORDER BY sort LIMIT 1'); $d['category_id'] = $first; }
          $id = q_ins('products', ['slug' => slugify($d['name'] . ' ' . $sku), 'sku' => $sku, 'sku_norm' => norm_code($sku), 'name' => $d['name'], 'category_id' => $d['category_id'], 'published' => 0, 'sort' => 1000, 'created_at' => now(), 'updated_at' => now()]);
          $new++;
        } else $upd++;
        $e = product_validate($id, $d);
        if ($e) { $errs[] = "$sku: " . implode(' ', $e); }
        draft_save('product', $id, $d);
      }
      $report = [$upd, $new, $errs];
      flash("Готово: обновлено $upd, новых $new. Изменения сохранены черновиками — проверьте и опубликуйте." . ($errs ? ' Есть замечания ниже.' : ''), $errs ? 'warn' : 'ok');
      $_SESSION['import_errs'] = $errs;
      redirect('/admin/products/import');
    }
  }
  $errs = $_SESSION['import_errs'] ?? []; unset($_SESSION['import_errs']);
  a_head('Загрузка таблицы', '/products');
  ?>
  <div class="a-page-head"><div><a class="a-back" href="/admin/products"><?= a_icon('arrow-left') ?>Товары</a><h1>Загрузка таблицы</h1><p class="a-muted">Массово обновить ссылки, цены, наличие и характеристики или добавить новые товары.</p></div></div>
  <?php if ($errs): ?><div class="a-card a-warn"><h2>Замечания</h2><ul class="a-mini"><?php foreach ($errs as $e): ?><li><?= h($e) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
  <div class="a-grid2">
    <form class="a-card" method="post" enctype="multipart/form-data">
      <?= csrf_field() ?>
      <h2>Файл CSV</h2>
      <p>Сохраните таблицу из Excel как «CSV UTF-8 (разделитель — точка с запятой)». Товары ищутся по артикулу: найденные обновятся, новые создадутся скрытыми.</p>
      <label class="a-drop"><input type="file" name="csv" accept=".csv,text/csv" required><?= a_icon('plus') ?><span>Выберите файл</span></label>
      <button class="a-btn a-btn-primary" type="submit">Загрузить</button>
      <p class="a-hint">Все изменения попадают в черновики — на сайте ничего не поменяется, пока вы не опубликуете товары.</p>
    </form>
    <div class="a-card">
      <h2>Какие столбцы понимает</h2>
      <p>Первая строка — заголовки. Обязателен только «Артикул».</p>
      <ul class="a-mini"><li>Артикул, Название, Категория, Производитель</li><li>Оригинальные номера, В комплекте</li><li>Ссылка Wildberries, Ссылка Ozon, Цена от, Наличие</li><?php foreach (attributes_all() as $at): ?><li><?= h($at['name'] . ($at['unit'] ? ', ' . $at['unit'] : '')) ?></li><?php endforeach; ?></ul>
      <p class="a-hint">Удобно начать с выгрузки: <a href="/admin/products/export">скачайте текущую таблицу</a>, поправьте и загрузите обратно. Совместимость с автомобилями правится в карточке товара.</p>
    </div>
  </div>
  <?php
  a_foot();
  return;
}

/* ================= история товара ================= */
if (ctype_digit($sub) && ($a[2] ?? '') === 'history') {
  $id = (int)$sub;
  $p = q_one('SELECT * FROM products WHERE id = ?', [$id]) ?: redirect('/admin/products');
  if (a_require_post()) {
    $v = q_one("SELECT * FROM versions WHERE id = ? AND entity = 'product' AND entity_id = ?", [(int)$_POST['version'], (string)$id]);
    if ($v) { draft_save('product', $id, json_decode($v['data'], true)); flash('Версия от ' . h(ru_date($v['created_at'])) . ' загружена в черновик. Проверьте и нажмите «Опубликовать».'); }
    redirect('/admin/products/' . $id);
  }
  a_head('История: ' . $p['name'], '/products');
  $vs = versions_list('product', $id, 50);
  ?>
  <div class="a-page-head"><div><a class="a-back" href="/admin/products/<?= $id ?>"><?= a_icon('arrow-left') ?><?= h($p['name']) ?></a><h1>История публикаций</h1><p class="a-muted">Можно вернуть любую версию: она откроется черновиком, сайт не изменится до публикации.</p></div></div>
  <div class="a-card a-card-flush"><?php if (!$vs): ?><p class="a-pad a-muted">Публикаций пока не было.</p><?php endif; ?>
    <ul class="a-timeline"><?php foreach ($vs as $i => $v): ?><li><div><b><?= h($v['note'] ?: 'Опубликовано') ?></b><small><?= h(ru_date($v['created_at'])) ?> · <?= h($v['user_name']) ?></small></div>
      <?php if ($i > 0): ?><form method="post"><?= csrf_field() ?><input type="hidden" name="version" value="<?= (int)$v['id'] ?>"><button class="a-btn a-btn-soft a-btn-sm" type="submit">Вернуть эту версию</button></form><?php else: ?><span class="a-pill a-pill-ok">Текущая</span><?php endif; ?></li><?php endforeach; ?></ul></div>
  <?php
  a_foot();
  return;
}

/* ================= редактирование ================= */
$id = $sub === 'new' ? 0 : (int)$sub;
$row = $id ? q_one('SELECT * FROM products WHERE id = ?', [$id]) : null;
if ($id && !$row) redirect('/admin/products');
$errors = [];

if (a_require_post()) {
  $act = (string)($_POST['action'] ?? 'save');
  if ($act === 'delete' && $id) {
    $snap = product_snapshot($id);
    version_add('product', $id, $snap, 'delete', 'Удалён');
    q('DELETE FROM products WHERE id = ?', [$id]);
    draft_drop('product', $id);
    flash('Товар «' . h($row['name']) . '» удалён. Копия осталась в истории правок.');
    redirect('/admin/products');
  }
  if ($act === 'discard' && $id) { draft_drop('product', $id); flash('Черновик удалён, в форме — опубликованная версия.'); redirect('/admin/products/' . $id); }
  if ($act === 'duplicate' && $id) {
    $d = draft_get('product', $id) ?: product_snapshot($id);
    $nsku = $d['sku'] . '-копия'; $k = 2; while (q_val('SELECT id FROM products WHERE sku = ?', [$nsku])) $nsku = $d['sku'] . '-копия-' . $k++;
    $nid = q_ins('products', ['slug' => slugify($d['name'] . ' ' . $nsku), 'sku' => $nsku, 'sku_norm' => norm_code($nsku), 'name' => $d['name'] . ' (копия)', 'category_id' => $d['category_id'], 'published' => 0, 'sort' => (int)$d['sort'] + 1, 'created_at' => now(), 'updated_at' => now()]);
    $d['sku'] = $nsku; $d['name'] .= ' (копия)'; $d['slug'] = ''; $d['published'] = 0;
    draft_save('product', $nid, $d);
    flash('Создана копия. Поменяйте артикул, название и совместимость.');
    redirect('/admin/products/' . $nid);
  }

  // данные формы → массив черновика
  $P = $_POST;
  $d = [];
  foreach (['name', 'slug', 'sku', 'short', 'description', 'oem', 'kit', 'wb_url', 'ozon_url', 'price_from', 'stock', 'badge', 'sort', 'seo_title', 'seo_desc'] as $f) $d[$f] = trim((string)($P[$f] ?? ''));
  $d['wb_url'] = mp_clean($d['wb_url']); $d['ozon_url'] = mp_clean($d['ozon_url']);
  $d['price_from'] = preg_replace('~\D~', '', $d['price_from']);
  $d['category_id'] = (int)($P['category_id'] ?? 0);
  $d['brand_id'] = (int)($P['brand_id'] ?? 0);
  $d['published'] = !empty($P['published']) ? 1 : 0;
  $d['attrs'] = [];
  foreach ((array)($P['attrs'] ?? []) as $k => $v) $d['attrs'][(string)$k] = trim(str_replace(',', '.', (string)$v));
  $d['photos'] = [];
  foreach ((array)($P['photos'] ?? []) as $ph) if (!empty($ph['id'])) $d['photos'][] = ['id' => (int)$ph['id'], 'alt' => trim((string)($ph['alt'] ?? ''))];
  $d['compat'] = [];
  foreach ((array)($P['compat'] ?? []) as $c) {
    if (empty($c['model_id'])) continue;
    $d['compat'][] = ['model_id' => (int)$c['model_id'], 'year_from' => (int)($c['year_from'] ?? 0) ?: null, 'year_to' => (int)($c['year_to'] ?? 0) ?: null,
      'bodies' => implode(', ', (array)($c['bodies'] ?? [])), 'engines' => trim((string)($c['engines'] ?? '')), 'note' => trim((string)($c['note'] ?? ''))];
  }
  if ($act === 'publish' || $act === 'unpublish') $d['published'] = $act === 'publish' ? 1 : 0;

  $errors = product_validate($id, $d);
  if (!$errors || ($act === 'save' && !isset($errors['sku']) && ($id || !isset($errors['name'])))) {
    if (!$id) {
      $id = q_ins('products', ['slug' => $d['slug'] ?: slugify($d['name'] . ' ' . $d['sku']), 'sku' => $d['sku'], 'sku_norm' => norm_code($d['sku']), 'name' => $d['name'], 'category_id' => $d['category_id'] ?: null, 'published' => 0, 'sort' => (int)$d['sort'] ?: 1000, 'created_at' => now(), 'updated_at' => now()]);
    }
    if (in_array($act, ['publish', 'unpublish'], true) && !$errors) {
      product_publish($id, $d, $act === 'publish' ? 'Опубликовано' : 'Снято с сайта');
      flash($act === 'publish' ? 'Опубликовано — изменения уже на сайте. <a href="' . h(product_url(['slug' => q_val('SELECT slug FROM products WHERE id = ?', [$id])])) . '" target="_blank">Посмотреть</a>' : 'Товар скрыт с сайта.');
      redirect('/admin/products/' . $id);
    }
    if ($act === 'save' || $act === 'preview' || $errors) {
      draft_save('product', $id, $d);
      if ($act === 'preview') redirect(product_url(['slug' => q_val('SELECT slug FROM products WHERE id = ?', [$id])]) . '?preview=1');
      if (!$errors) { flash('Черновик сохранён. На сайте пока прежняя версия.'); redirect('/admin/products/' . $id); }
    }
  }
  if ($errors) flash('Проверьте выделенные поля — ' . (in_array($act, ['publish'], true) ? 'публикация не выполнена' : 'черновик сохранён частично') . '.', 'err');
  $row = $id ? q_one('SELECT * FROM products WHERE id = ?', [$id]) : null;
  $data = $d;
} else {
  $data = $id ? (draft_get('product', $id) ?: product_snapshot($id)) : ['name' => '', 'slug' => '', 'sku' => '', 'category_id' => (int)($_GET['cat'] ?? 0), 'brand_id' => 0, 'short' => '', 'description' => '', 'oem' => '', 'kit' => '', 'wb_url' => '', 'ozon_url' => '', 'price_from' => '', 'stock' => 'request', 'badge' => '', 'sort' => 1000, 'seo_title' => '', 'seo_desc' => '', 'published' => 1, 'attrs' => [], 'photos' => [], 'compat' => []];
}
$dInfo = $id ? draft_info('product', $id) : null;
$cats = categories_all(false);
$brands = brands_all(false);
$makes = q_all('SELECT * FROM makes ORDER BY sort, name');
$models = q_all('SELECT * FROM models ORDER BY sort, name');
$bodies = site('bodies');
$title = $id ? $row['name'] : 'Новый товар';
a_head($title, '/products');
?>
<form class="a-edit" method="post" action="/admin/products/<?= $id ?: 'new' ?>" data-dirty-guard novalidate>
  <?= csrf_field() ?>
  <div class="a-page-head">
    <div><a class="a-back" href="/admin/products"><?= a_icon('arrow-left') ?>Товары</a><h1><?= h($title) ?></h1>
      <p class="a-muted"><?php if ($row): ?><?= a_status_badge($row, (bool)$dInfo) ?> <?php if ($dInfo): ?>черновик от <?= h(ru_date($dInfo['updated_at'])) ?><?= $dInfo['name'] ? ', ' . h($dInfo['name']) : '' ?> · <?php endif; ?>на сайте видна опубликованная версия<?php else: ?>Заполните карточку и нажмите «Опубликовать» или «Сохранить черновик»<?php endif; ?></p></div>
    <?php if ($row): ?><div class="a-head-btns">
      <a class="a-btn a-btn-ghost" href="/admin/products/<?= $id ?>/history"><?= a_icon('clock') ?><span>История</span></a>
      <?php if ($row['published']): ?><a class="a-btn a-btn-ghost" href="<?= h(product_url($row)) ?>" target="_blank"><?= a_icon('external') ?><span>На сайте</span></a><?php endif; ?>
    </div><?php endif; ?>
  </div>

  <div class="a-edit-grid">
    <div class="a-edit-main">
      <section class="a-card" id="main">
        <h2>Карточка</h2>
        <?= a_field('name', 'Название', $data['name'], ['required' => true, 'error' => $errors['name'] ?? '', 'attrs' => ' data-slug-src', 'placeholder' => 'Газовые упоры багажника LIFTAUTOPRO для Ford Focus II седан']) ?>
        <div class="a-row3">
          <?= a_field('sku', 'Артикул', $data['sku'], ['required' => true, 'error' => $errors['sku'] ?? '', 'placeholder' => 'LAP-10488']) ?>
          <?= a_field('category_id', 'Категория', $data['category_id'], ['type' => 'select', 'options' => ['' => 'Выберите'] + array_column($cats, 'name', 'id'), 'error' => $errors['category_id'] ?? '']) ?>
          <?= a_field('brand_id', 'Производитель', $data['brand_id'], ['type' => 'select', 'options' => ['' => 'Не указан'] + array_column($brands, 'name', 'id')]) ?>
        </div>
        <?= a_field('short', 'Коротко — под названием', $data['short'], ['optional' => true, 'placeholder' => 'Также подходит для…']) ?>
        <?= a_field('description', 'Описание', $data['description'], ['type' => 'textarea', 'rows' => 8, 'hint' => 'Пустая строка — новый абзац. «- » в начале строки — пункт списка. **жирный**. Ссылка: [текст](/catalog).']) ?>
        <div class="a-row2">
          <?= a_field('oem', 'Оригинальные номера', $data['oem'], ['type' => 'textarea', 'rows' => 3, 'optional' => true, 'hint' => 'Через запятую. По ним работает поиск.']) ?>
          <?= a_field('kit', 'В комплекте', $data['kit'], ['optional' => true, 'placeholder' => '2 шт.']) ?>
        </div>
      </section>

      <section class="a-card" id="mp">
        <h2>Где купить</h2>
        <p class="a-muted">Кнопки на сайте ведут на эти страницы. Метки рекламы из ссылок убираются сами.</p>
        <div class="a-row2">
          <?= a_field('wb_url', 'Ссылка на Wildberries', $data['wb_url'], ['type' => 'url', 'optional' => true, 'placeholder' => 'https://www.wildberries.ru/catalog/…/detail.aspx', 'error' => $errors['wb_url'] ?? '', 'attrs' => ' data-mp-url="wb"']) ?>
          <?= a_field('ozon_url', 'Ссылка на Ozon', $data['ozon_url'], ['type' => 'url', 'optional' => true, 'placeholder' => 'https://www.ozon.ru/product/…', 'error' => $errors['ozon_url'] ?? '', 'attrs' => ' data-mp-url="ozon"']) ?>
        </div>
        <div class="a-row3">
          <?= a_field('stock', 'Наличие', $data['stock'], ['type' => 'select', 'options' => ['request' => 'По запросу', 'in' => 'В наличии', 'order' => 'Под заказ']]) ?>
          <?= a_field('price_from', 'Цена от, ₽', $data['price_from'], ['optional' => true, 'attrs' => ' inputmode="numeric"', 'hint' => 'Пусто — цена не показывается.']) ?>
          <?= a_field('badge', 'Метка на фото', $data['badge'], ['optional' => true, 'placeholder' => 'Новинка', 'attrs' => ' list="badges"']) ?>
          <datalist id="badges"><option value="Новинка"><option value="Хит продаж"><option value="Скоро в продаже"></datalist>
        </div>
      </section>

      <section class="a-card" id="specs">
        <div class="a-card-head"><h2>Характеристики</h2><a href="/admin/attributes" class="a-link-sm">Добавить характеристику</a></div>
        <div class="a-row3">
          <?php foreach (attributes_all() as $at): $v = $data['attrs'][$at['code']] ?? ''; ?>
            <?= a_field('attrs[' . $at['code'] . ']', $at['name'] . ($at['unit'] ? ', ' . $at['unit'] : ''), is_numeric($v) ? fmt_num($v) : $v, ['optional' => true, 'error' => $errors['attr_' . $at['code']] ?? '', 'attrs' => $at['type'] === 'number' ? ' inputmode="decimal"' : '']) ?>
          <?php endforeach; ?>
        </div>
      </section>

      <section class="a-card" id="compat">
        <div class="a-card-head"><h2>Совместимость</h2><a href="/admin/cars" class="a-link-sm">Справочник автомобилей</a></div>
        <p class="a-muted">По этим строкам работает подбор. Пустой кузов или двигатель — подходит к любому (если в других строках товара они не указаны).</p>
        <?php if (!empty($errors['compat'])): ?><p class="a-err"><?= h($errors['compat']) ?></p><?php endif; ?>
        <div class="a-compat" data-compat>
          <?php foreach ($data['compat'] as $i => $c) a_compat_row($i, $c, $makes, $models, $bodies); ?>
        </div>
        <template data-compat-tpl><?php a_compat_row('__i__', ['model_id' => 0, 'year_from' => '', 'year_to' => '', 'bodies' => '', 'engines' => '', 'note' => ''], $makes, $models, $bodies); ?></template>
        <button class="a-btn a-btn-soft" type="button" data-compat-add><?= a_icon('plus') ?>Добавить автомобиль</button>
        <script type="application/json" data-models><?= json_encode(array_map(fn($m) => ['id' => (int)$m['id'], 'mk' => (int)$m['make_id'], 'n' => $m['name'], 'yf' => $m['year_from'], 'yt' => $m['year_to'], 'b' => list_split($m['bodies'])], $models), JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) ?></script>
      </section>

      <section class="a-card" id="seo">
        <h2>Поисковики</h2>
        <p class="a-muted">Можно не заполнять — заголовок и описание соберутся из названия, артикула и совместимости.</p>
        <?= a_field('seo_title', 'Заголовок страницы', $data['seo_title'], ['optional' => true, 'attrs' => ' maxlength="90" data-count="70"']) ?>
        <?= a_field('seo_desc', 'Описание для поиска', $data['seo_desc'], ['type' => 'textarea', 'rows' => 2, 'optional' => true, 'attrs' => ' maxlength="250" data-count="160"']) ?>
        <div class="a-row2">
          <?= a_field('slug', 'Адрес страницы', $data['slug'], ['optional' => true, 'error' => $errors['slug'] ?? '', 'hint' => 'Латиница и дефис. Пусто — соберётся из названия. Менять у опубликованного товара не стоит: старые ссылки перестанут работать.', 'attrs' => ' data-slug']) ?>
          <?= a_field('sort', 'Порядок', $data['sort'], ['attrs' => ' inputmode="numeric"', 'hint' => 'Меньше — выше в каталоге.']) ?>
        </div>
      </section>
    </div>

    <aside class="a-edit-side">
      <section class="a-card" id="photos">
        <h2>Фото</h2>
        <div class="a-photos" data-photos>
          <?php foreach ($data['photos'] as $i => $ph): $r = photo_row($ph['id']); if (!$r) continue; ?>
            <div class="a-ph" draggable="true"><img src="<?= h(img_url($r, 400)) ?>" alt=""><input type="hidden" name="photos[<?= $i ?>][id]" value="<?= (int)$r['id'] ?>"><input class="a-ph-alt" name="photos[<?= $i ?>][alt]" value="<?= h($ph['alt']) ?>" placeholder="Подпись к фото" aria-label="Подпись к фото"><button type="button" class="a-ph-del" data-ph-del aria-label="Убрать фото"><?= a_icon('close') ?></button></div>
          <?php endforeach; ?>
        </div>
        <label class="a-drop" data-ph-drop><input type="file" accept="image/jpeg,image/png,image/webp" multiple hidden data-ph-input><?= a_icon('photo') ?><span><b>Добавить фото</b><small>Перетащите сюда или нажмите. JPG, PNG, WebP до 20 МБ — уменьшим и сожмём сами.</small></span></label>
        <p class="a-hint">Первое фото — главное в каталоге. Порядок меняется перетаскиванием.</p>
      </section>

      <section class="a-card a-sticky-actions">
        <h2>Публикация</h2>
        <?= a_check('published', 'Показывать на сайте', !empty($data['published']), 'Снимите, чтобы скрыть товар') ?>
        <div class="a-actions">
          <button class="a-btn a-btn-primary a-btn-block" type="submit" name="action" value="publish"><?= a_icon('check') ?>Опубликовать</button>
          <button class="a-btn a-btn-soft a-btn-block" type="submit" name="action" value="save" data-hotkey="s">Сохранить черновик</button>
          <button class="a-btn a-btn-ghost a-btn-block" type="submit" name="action" value="preview" formtarget="_blank"><?= a_icon('external') ?>Предпросмотр</button>
        </div>
        <?php if ($row): ?>
        <div class="a-actions-more">
          <?php if ($dInfo): ?><button class="a-link-btn" type="submit" name="action" value="discard" data-confirm="Удалить черновик и вернуть опубликованную версию?">Отменить черновик</button><?php endif; ?>
          <button class="a-link-btn" type="submit" name="action" value="duplicate">Сделать копию</button>
          <button class="a-link-btn a-danger" type="submit" name="action" value="delete" data-confirm="Удалить товар «<?= h($row['name']) ?>»? Его можно будет найти в истории правок.">Удалить товар</button>
        </div>
        <?php endif; ?>
        <p class="a-hint">Ctrl + S — сохранить черновик.</p>
      </section>
    </aside>
  </div>
</form>
<?php
a_foot();

function a_compat_row($i, $c, $makes, $models, $bodies) {
  $mid = (int)($c['model_id'] ?? 0);
  $mk = 0; foreach ($models as $m) if ($m['id'] == $mid) $mk = $m['make_id'];
  $sel = list_split($c['bodies'] ?? '');
  ?>
  <div class="a-crow" data-crow>
    <div class="a-field"><label>Марка</label><select data-c-make><option value="">—</option><?php foreach ($makes as $m): ?><option value="<?= (int)$m['id'] ?>"<?= $mk == $m['id'] ? ' selected' : '' ?>><?= h($m['name']) ?></option><?php endforeach; ?></select></div>
    <div class="a-field"><label>Модель</label><select name="compat[<?= $i ?>][model_id]" data-c-model><option value="">—</option><?php foreach ($models as $m): if ($m['make_id'] != $mk) continue; ?><option value="<?= (int)$m['id'] ?>"<?= $mid == $m['id'] ? ' selected' : '' ?>><?= h($m['name']) ?></option><?php endforeach; ?></select></div>
    <div class="a-field a-yrs"><label>Годы</label><div class="a-range"><input name="compat[<?= $i ?>][year_from]" value="<?= h($c['year_from'] ?? '') ?>" inputmode="numeric" placeholder="с" aria-label="Год с"><span>—</span><input name="compat[<?= $i ?>][year_to]" value="<?= h($c['year_to'] ?? '') ?>" inputmode="numeric" placeholder="по" aria-label="Год по"></div></div>
    <div class="a-field a-bodies"><span class="a-lbl">Кузов</span><div class="a-chips"><?php foreach ($bodies as $b): ?><label class="a-chip"><input type="checkbox" name="compat[<?= $i ?>][bodies][]" value="<?= h($b) ?>"<?= in_array($b, $sel, true) ? ' checked' : '' ?>><span><?= h($b) ?></span></label><?php endforeach; ?></div></div>
    <div class="a-field"><label>Двигатель, л</label><input name="compat[<?= $i ?>][engines]" value="<?= h($c['engines'] ?? '') ?>" placeholder="1.6, 2.0"></div>
    <div class="a-field"><label>Примечание</label><input name="compat[<?= $i ?>][note]" value="<?= h($c['note'] ?? '') ?>" placeholder="необязательно"></div>
    <button type="button" class="a-icon-btn a-crow-del" data-crow-del aria-label="Убрать строку"><?= a_icon('trash') ?></button>
  </div>
  <?php
}
