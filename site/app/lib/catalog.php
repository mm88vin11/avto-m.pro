<?php
if (!defined('AVTOM')) { http_response_code(404); exit; }

/* ================= справочники ================= */

function categories_all($onlyPublished = true) {
  static $c = [];
  $k = (int)$onlyPublished;
  if (!isset($c[$k])) {
    $rows = q_all('SELECT c.*, (SELECT COUNT(*) FROM products p WHERE p.category_id = c.id AND p.published = 1) AS cnt FROM categories c ' . ($onlyPublished ? 'WHERE c.published = 1 ' : '') . 'ORDER BY c.sort, c.name');
    $c[$k] = array_column($rows, null, 'id');
  }
  return $c[$k];
}
/** Категории для витрины: опубликованные и с товарами */
function categories_visible() { return array_filter(categories_all(), fn($c) => $c['cnt'] > 0); }
function category_by_slug($slug) { return q_one('SELECT * FROM categories WHERE slug = ? AND published = 1', [$slug]); }

function brands_all($onlyPublished = true) {
  static $b = [];
  $k = (int)$onlyPublished;
  if (!isset($b[$k])) $b[$k] = array_column(q_all('SELECT b.*, (SELECT COUNT(*) FROM products p WHERE p.brand_id = b.id AND p.published = 1) AS cnt FROM brands b ' . ($onlyPublished ? 'WHERE b.published = 1 ' : '') . 'ORDER BY b.sort, b.name'), null, 'id');
  return $b[$k];
}
function attributes_all() {
  static $a = null;
  if ($a === null) $a = array_column(q_all('SELECT * FROM attributes ORDER BY sort, name'), null, 'id');
  return $a;
}
function attribute_by_code($code) { foreach (attributes_all() as $a) if ($a['code'] === $code) return $a; return null; }

function photo_row($id) {
  static $c = [];
  if (!$id) return null;
  if (!array_key_exists($id, $c)) $c[$id] = q_one('SELECT * FROM photos WHERE id = ?', [$id]);
  return $c[$id];
}

/* ================= товары ================= */

/** Все опубликованные товары с характеристиками, фото и совместимостью — каталог небольшой, фильтруем в памяти */
function products_all($includeHidden = false) {
  static $cache = [];
  $k = (int)$includeHidden;
  if (isset($cache[$k])) return $cache[$k];
  $rows = q_all('SELECT * FROM products ' . ($includeHidden ? '' : 'WHERE published = 1 ') . 'ORDER BY sort, id');
  $list = [];
  foreach ($rows as $r) { $r['attrs'] = []; $r['photos'] = []; $r['compat'] = []; $list[$r['id']] = $r; }
  if (!$list) return $cache[$k] = [];
  $ids = implode(',', array_map('intval', array_keys($list)));
  $attrs = attributes_all();
  foreach (q_all("SELECT * FROM product_attrs WHERE product_id IN ($ids)") as $a) {
    if (!isset($attrs[$a['attribute_id']])) continue;
    $at = $attrs[$a['attribute_id']];
    $list[$a['product_id']]['attrs'][$at['code']] = $a['value_num'] !== null ? (float)$a['value_num'] : $a['value_text'];
  }
  foreach (q_all("SELECT pp.product_id, pp.alt, ph.* FROM product_photos pp JOIN photos ph ON ph.id = pp.photo_id WHERE pp.product_id IN ($ids) ORDER BY pp.sort") as $p) {
    $list[$p['product_id']]['photos'][] = ['id' => (int)$p['id'], 'file' => $p['file'], 'ext' => $p['ext'], 'w' => $p['w'], 'h' => $p['h'], 'alt' => $p['alt']];
  }
  foreach (q_all("SELECT c.*, m.name AS model_name, m.slug AS model_slug, m.aliases AS model_aliases, mk.name AS make_name, mk.slug AS make_slug, mk.aliases AS make_aliases
      FROM compat c JOIN models m ON m.id = c.model_id JOIN makes mk ON mk.id = m.make_id WHERE c.product_id IN ($ids) ORDER BY c.sort, c.id") as $c) {
    $list[$c['product_id']]['compat'][] = compat_row_norm($c);
  }
  return $cache[$k] = $list;
}
function compat_row_norm($c) {
  return [
    'model_id' => (int)$c['model_id'], 'make' => $c['make_slug'], 'make_name' => $c['make_name'], 'model' => $c['model_slug'], 'model_name' => $c['model_name'],
    'year_from' => $c['year_from'] ? (int)$c['year_from'] : null, 'year_to' => $c['year_to'] ? (int)$c['year_to'] : null,
    'bodies' => list_split($c['bodies'] ?? ''), 'engines' => list_split($c['engines'] ?? ''), 'note' => $c['note'] ?? '',
    'make_aliases' => $c['make_aliases'] ?? '', 'model_aliases' => $c['model_aliases'] ?? '',
  ];
}
function list_split($s) { return array_values(array_filter(array_map('trim', preg_split('~[,;]+~', (string)$s)), 'strlen')); }

function product_by_slug($slug) {
  $id = q_val('SELECT id FROM products WHERE slug = ?', [$slug]);
  if (!$id) return null;
  $all = products_all(true);
  $p = $all[$id] ?? null;
  if (!$p) return null;
  if (preview_mode()) {
    $d = draft_get('product', $id);
    if ($d) $p = product_from_draft($p, $d);
    return $p;
  }
  return $p['published'] ? $p : null;
}

/** Товар в виде для витрины из данных черновика (предпросмотр) */
function product_from_draft($p, $d) {
  foreach (['name', 'sku', 'short', 'description', 'oem', 'kit', 'wb_url', 'ozon_url', 'price_from', 'stock', 'badge', 'seo_title', 'seo_desc'] as $f) if (array_key_exists($f, $d)) $p[$f] = $d[$f];
  if (isset($d['category_id'])) $p['category_id'] = $d['category_id'];
  if (isset($d['brand_id'])) $p['brand_id'] = $d['brand_id'];
  if (isset($d['attrs'])) {
    $p['attrs'] = [];
    foreach ($d['attrs'] as $code => $v) if ($v !== '' && $v !== null) $p['attrs'][$code] = is_numeric($v) ? (float)$v : $v;
  }
  if (isset($d['photos'])) {
    $p['photos'] = [];
    foreach ($d['photos'] as $ph) { $r = photo_row($ph['id']); if ($r) $p['photos'][] = ['id' => (int)$r['id'], 'file' => $r['file'], 'ext' => $r['ext'], 'w' => $r['w'], 'h' => $r['h'], 'alt' => $ph['alt'] ?? '']; }
  }
  if (isset($d['compat'])) {
    $p['compat'] = [];
    foreach ($d['compat'] as $c) {
      $m = q_one('SELECT m.*, mk.name AS make_name, mk.slug AS make_slug, mk.aliases AS make_aliases FROM models m JOIN makes mk ON mk.id = m.make_id WHERE m.id = ?', [(int)$c['model_id']]);
      if (!$m) continue;
      $p['compat'][] = compat_row_norm(['model_id' => $m['id'], 'model_name' => $m['name'], 'model_slug' => $m['slug'], 'model_aliases' => $m['aliases'], 'make_name' => $m['make_name'], 'make_slug' => $m['make_slug'], 'make_aliases' => $m['make_aliases'],
        'year_from' => $c['year_from'] ?? null, 'year_to' => $c['year_to'] ?? null, 'bodies' => $c['bodies'] ?? '', 'engines' => $c['engines'] ?? '', 'note' => $c['note'] ?? '']);
    }
  }
  return $p;
}

function product_url($p) { return '/product/' . $p['slug']; }
function product_photo($p, $i = 0) { return $p['photos'][$i] ?? null; }
function product_category($p) { $c = categories_all(false); return $c[$p['category_id']] ?? null; }
function product_brand($p) { $b = brands_all(false); return $b[$p['brand_id']] ?? null; }
function stock_label($s) { return ['in' => 'В наличии', 'order' => 'Под заказ', 'request' => 'Наличие по запросу'][$s] ?? 'Наличие по запросу'; }

/** Короткая строка совместимости для карточки: «Ford Focus II · 2004–2011 · седан» */
function compat_short($p, $max = 2) {
  $parts = [];
  foreach ($p['compat'] as $c) {
    $s = $c['make_name'] . ' ' . $c['model_name'];
    $key = $s;
    if (isset($parts[$key])) continue;
    $y = years_label($c['year_from'], $c['year_to']);
    $parts[$key] = $s . ($y ? ' · ' . $y : '');
  }
  $vals = array_values($parts);
  $more = count($vals) - $max;
  $out = implode(', ', array_slice($vals, 0, $max));
  if ($more > 0) $out .= ' и ещё ' . $more . ' ' . plural($more, 'модель', 'модели', 'моделей');
  return $out;
}
function years_label($f, $t) {
  if ($f && $t) return $f == $t ? (string)$f : $f . '–' . $t;
  if ($f) return 'с ' . $f;
  if ($t) return 'до ' . $t;
  return '';
}
function compat_makes($p) { return array_values(array_unique(array_map(fn($c) => $c['make_name'], $p['compat']))); }

/* ================= подбор по автомобилю ================= */

/** Дерево марок и моделей, по которым есть опубликованные товары. Для выпадающих списков подбора. */
function car_tree() {
  static $tree = null;
  if ($tree !== null) return $tree;
  $makes = [];
  foreach (products_all() as $p) foreach ($p['compat'] as $c) {
    $mk = &$makes[$c['make']];
    if (!$mk) $mk = ['slug' => $c['make'], 'name' => $c['make_name'], 'models' => []];
    $md = &$mk['models'][$c['model']];
    if (!$md) $md = ['slug' => $c['model'], 'name' => $c['model_name'], 'yf' => null, 'yt' => null, 'bodies' => [], 'engines' => [], 'n' => 0];
    $md['n']++;
    if ($c['year_from'] && (!$md['yf'] || $c['year_from'] < $md['yf'])) $md['yf'] = $c['year_from'];
    if ($c['year_to'] && (!$md['yt'] || $c['year_to'] > $md['yt'])) $md['yt'] = $c['year_to'];
    $md['bodies'] = array_values(array_unique(array_merge($md['bodies'], $c['bodies'])));
    $md['engines'] = array_values(array_unique(array_merge($md['engines'], $c['engines'])));
    unset($mk, $md);
  }
  uasort($makes, fn($a, $b) => strcmp($a['name'], $b['name']));
  foreach ($makes as &$m) {
    uasort($m['models'], fn($a, $b) => strnatcasecmp($a['name'], $b['name']));
    foreach ($m['models'] as &$md) { sort($md['engines'], SORT_NATURAL); $order = site('bodies'); usort($md['bodies'], fn($a, $b) => body_rank($a, $order) <=> body_rank($b, $order)); }
    unset($md);
    $m['models'] = array_values($m['models']);
  }
  unset($m);
  return $tree = array_values($makes);
}
function body_rank($b, $order) { $i = array_search($b, $order, true); return $i === false ? 99 : $i; }
function car_tree_find($make, $model = null) {
  foreach (car_tree() as $mk) if ($mk['slug'] === $make) {
    if ($model === null) return $mk;
    foreach ($mk['models'] as $md) if ($md['slug'] === $model) return ['make' => $mk, 'model' => $md];
  }
  return null;
}

/** Автомобиль из параметров запроса */
function car_from_query($src = null) {
  $src = $src ?? $_GET;
  $car = [
    'make' => preg_replace('~[^a-z0-9-]~', '', (string)($src['make'] ?? '')),
    'model' => preg_replace('~[^a-z0-9-]~', '', (string)($src['model'] ?? '')),
    'year' => (int)($src['year'] ?? 0) ?: null,
    'body' => trim(mb_substr((string)($src['body'] ?? ''), 0, 40)),
    'engine' => trim(mb_substr((string)($src['engine'] ?? ''), 0, 20)),
  ];
  if (!$car['make']) return null;
  $f = car_tree_find($car['make']);
  if (!$f) return null;
  $car['make_name'] = $f['name'];
  $car['model_name'] = '';
  if ($car['model']) {
    $m = car_tree_find($car['make'], $car['model']);
    if ($m) $car['model_name'] = $m['model']['name']; else $car['model'] = '';
  }
  return $car;
}
function car_label($car) {
  if (!$car) return '';
  $s = trim($car['make_name'] . ' ' . ($car['model_name'] ?? ''));
  $extra = array_filter([$car['year'] ?? null, $car['body'] ? mb_strtolower($car['body']) : '', $car['engine'] ? $car['engine'] . ' л' : '']);
  return $s . ($extra ? ' · ' . implode(' · ', $extra) : '');
}

/**
 * Совместимость товара с автомобилем:
 * 'yes' — подходит по всем указанным параметрам; 'check' — модель совпала, но данных о кузове, годе или двигателе недостаточно;
 * 'no' — не подходит.
 */
function compat_match($p, $car) {
  if (!$car) return 'yes';
  $best = 'no';
  // кузов и двигатель важны, только если производитель указывает их хотя бы в одной строке совместимости этого товара
  $bodiesMatter = (bool)array_filter($p['compat'], fn($c) => $c['bodies']);
  $enginesMatter = (bool)array_filter($p['compat'], fn($c) => $c['engines']);
  foreach ($p['compat'] as $c) {
    if ($c['make'] !== $car['make']) continue;
    if ($car['model'] && $c['model'] !== $car['model']) continue;
    $state = 'yes';
    if ($car['year']) {
      if (!$c['year_from'] && !$c['year_to']) $state = 'check';
      elseif (($c['year_from'] && $car['year'] < $c['year_from']) || ($c['year_to'] && $car['year'] > $c['year_to'])) continue;
    }
    if ($car['body'] && $bodiesMatter) {
      if (!$c['bodies']) $state = 'check';
      elseif (!in_array(mb_strtolower($car['body']), array_map('mb_strtolower', $c['bodies']), true)) continue;
    }
    if ($car['engine'] && $enginesMatter) {
      if (!$c['engines']) $state = 'check';
      elseif (!in_array(engine_norm($car['engine']), array_map('engine_norm', $c['engines']), true)) continue;
    }
    if ($state === 'yes') return 'yes';
    $best = 'check';
  }
  return $best;
}
function engine_norm($e) { return str_replace(',', '.', preg_replace('~[^0-9.,]~', '', (string)$e)); }

/* ================= выдача с фильтрами ================= */

/**
 * Параметры фильтра из запроса: cat, brand[], make/model/year/body/engine, a[код][min|max], sort, view, q.
 */
function filters_from_query() {
  $f = [
    'brand' => array_values(array_filter(array_map(fn($s) => preg_replace('~[^a-z0-9-]~', '', (string)$s), (array)($_GET['brand'] ?? [])))),
    'car' => car_from_query(),
    'a' => [],
    'sort' => in_array($_GET['sort'] ?? '', ['popular', 'name', 'new'], true) ? $_GET['sort'] : 'popular',
    'view' => ($_GET['view'] ?? '') === 'list' ? 'list' : 'grid',
    'q' => trim(mb_substr((string)($_GET['q'] ?? ''), 0, 80)),
  ];
  foreach ((array)($_GET['a'] ?? []) as $code => $r) {
    if (!is_array($r) || !attribute_by_code((string)$code)) continue;
    $min = isset($r['min']) && $r['min'] !== '' ? (float)str_replace(',', '.', $r['min']) : null;
    $max = isset($r['max']) && $r['max'] !== '' ? (float)str_replace(',', '.', $r['max']) : null;
    if ($min !== null || $max !== null) $f['a'][$code] = ['min' => $min, 'max' => $max];
  }
  return $f;
}

/** Применяет фильтры. Возвращает ['items' => [...товар + match], 'facets' => ...] */
function catalog_query($catId, $f) {
  $all = products_all();
  $base = array_filter($all, fn($p) => !$catId || $p['category_id'] == $catId);
  if ($f['q'] !== '') $base = array_intersect_key($base, array_flip(search_ids($f['q'])));
  $brands = brands_all();
  $items = [];
  foreach ($base as $p) {
    if ($f['brand']) { $b = $brands[$p['brand_id']] ?? null; if (!$b || !in_array($b['slug'], $f['brand'], true)) continue; }
    $ok = true;
    foreach ($f['a'] as $code => $r) {
      $v = $p['attrs'][$code] ?? null;
      if (!is_numeric($v)) { $ok = false; break; }
      if ($r['min'] !== null && $v < $r['min']) { $ok = false; break; }
      if ($r['max'] !== null && $v > $r['max']) { $ok = false; break; }
    }
    if (!$ok) continue;
    $m = compat_match($p, $f['car']);
    if ($m === 'no') continue;
    $p['match'] = $f['car'] ? $m : null;
    $items[] = $p;
  }
  usort($items, function ($a, $b) use ($f) {
    if ($f['car'] && $a['match'] !== $b['match']) return $a['match'] === 'yes' ? -1 : 1;
    if ($f['sort'] === 'name') return strnatcasecmp($a['name'], $b['name']);
    if ($f['sort'] === 'new') return $b['id'] <=> $a['id'];
    return [$a['sort'], -$a['views'], $a['id']] <=> [$b['sort'], -$b['views'], $b['id']];
  });
  return ['items' => $items, 'facets' => catalog_facets($base)];
}

/** Доступные значения фильтров в пределах категории */
function catalog_facets($products) {
  $brands = brands_all();
  $fb = []; $fa = [];
  $attrs = attributes_all();
  foreach ($products as $p) {
    if (isset($brands[$p['brand_id']])) { $b = $brands[$p['brand_id']]; $fb[$b['slug']] = ['name' => $b['name'], 'n' => ($fb[$b['slug']]['n'] ?? 0) + 1]; }
    foreach ($p['attrs'] as $code => $v) {
      if (!is_numeric($v)) continue;
      $a = null; foreach ($attrs as $x) if ($x['code'] === $code) $a = $x;
      if (!$a || !$a['filter']) continue;
      $fa[$code] = $fa[$code] ?? ['name' => $a['name'], 'unit' => $a['unit'], 'min' => $v, 'max' => $v, 'sort' => $a['sort']];
      $fa[$code]['min'] = min($fa[$code]['min'], $v);
      $fa[$code]['max'] = max($fa[$code]['max'], $v);
    }
  }
  uasort($fa, fn($x, $y) => $x['sort'] <=> $y['sort']);
  return ['brands' => $fb, 'attrs' => $fa];
}

/* ================= поиск ================= */

function search_norm($s) {
  $s = mb_strtolower((string)$s);
  $s = str_replace('ё', 'е', $s);
  $s = preg_replace('~[^\p{L}\p{N}]+~u', ' ', $s);
  $roman = ['i' => '1', 'ii' => '2', 'iii' => '3', 'iv' => '4', 'v' => '5', 'vi' => '6'];
  $t = array_map(fn($w) => $roman[$w] ?? $w, preg_split('~\s+~', trim($s)));
  return implode(' ', array_filter($t, 'strlen'));
}
function product_search_text($p) {
  $parts = [$p['name'], $p['sku'], $p['oem'], $p['short']];
  $b = product_brand($p); if ($b) $parts[] = $b['name'];
  $c = product_category($p); if ($c) $parts[] = $c['name'];
  foreach ($p['compat'] as $x) array_push($parts, $x['make_name'], $x['model_name'], $x['make_aliases'], $x['model_aliases'], implode(' ', $x['bodies']));
  return ' ' . search_norm(implode(' ', $parts)) . ' ';
}
/** id товаров, подходящих под запрос: все слова запроса (начало слова) или совпадение кода артикула/номера */
function search_ids($q) {
  $qn = search_norm($q);
  $code = norm_code($q);
  if ($qn === '' && $code === '') return [];
  $words = array_filter(explode(' ', $qn), 'strlen');
  $ids = [];
  foreach (products_all() as $p) {
    $hit = false;
    if (strlen($code) >= 4 && (strpos(norm_code($p['sku']), $code) !== false || strpos(' ' . $p['oem_norm'] . ' ', $code) !== false)) $hit = true;
    if (!$hit && $words) {
      $t = product_search_text($p);
      $hit = true;
      foreach ($words as $w) if (mb_strpos($t, ' ' . $w) === false) { $hit = false; break; }
    }
    if ($hit) $ids[] = $p['id'];
  }
  return $ids;
}
function search_log($q, $n) {
  $q = mb_substr(search_norm($q), 0, 60);
  if ($q === '' || mb_strlen($q) < 2) return;
  try { q('INSERT INTO searches (day, q, results, n) VALUES (?, ?, ?, 1) ON CONFLICT(day, q) DO UPDATE SET n = n + 1, results = excluded.results', [date('Y-m-d'), $q, $n]); } catch (Throwable $e) {}
}

/** Похожие: та же категория, затем общие модели авто */
function related_products($p, $limit = 4) {
  $models = array_column($p['compat'], 'model');
  $scored = [];
  foreach (products_all() as $x) {
    if ($x['id'] == $p['id']) continue;
    $s = count(array_intersect($models, array_column($x['compat'], 'model'))) * 2 + ($x['category_id'] == $p['category_id'] ? 1 : 0);
    if ($s > 0) $scored[$x['id']] = [$s, $x];
  }
  uasort($scored, fn($a, $b) => $b[0] <=> $a[0]);
  return array_map(fn($v) => $v[1], array_slice($scored, 0, $limit, true));
}

/** Данные для корзины и сравнения (по id) */
function products_public_json($ids) {
  $all = products_all();
  $out = [];
  foreach ($ids as $id) {
    $p = $all[(int)$id] ?? null;
    if (!$p) continue;
    $ph = product_photo($p);
    $attrs = [];
    foreach (attributes_all() as $a) if (isset($p['attrs'][$a['code']]) && $a['in_card']) $attrs[] = [$a['name'], attr_value($a, $p['attrs'][$a['code']])];
    $b = product_brand($p); $c = product_category($p);
    $out[] = [
      'id' => (int)$p['id'], 'name' => $p['name'], 'sku' => $p['sku'], 'url' => product_url($p),
      'img' => $ph ? img_url($ph, 400) : null, 'brand' => $b['name'] ?? '', 'category' => $c['name'] ?? '',
      'compat' => compat_short($p, 3), 'kit' => $p['kit'], 'stock' => stock_label($p['stock']), 'attrs' => $attrs,
      'wb' => $p['wb_url'] ? '/go/wb/' . $p['id'] : '', 'ozon' => $p['ozon_url'] ? '/go/ozon/' . $p['id'] : '',
      'oem' => $p['oem'],
    ];
  }
  return $out;
}
function attr_value($a, $v) { return (is_numeric($v) ? fmt_num($v) : $v) . ($a['unit'] ? ' ' . $a['unit'] : ''); }
