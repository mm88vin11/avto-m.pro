<?php
if (!defined('AVTOM')) { http_response_code(404); exit; }

/*
 * Черновики, публикация и история.
 * Сущности: product (id товара), content (ключ страницы), а также category, brand, site, car — их правки сразу публикуются, но попадают в историю.
 * Витрина показывает только опубликованное; ?preview=1 для вошедшего сотрудника подставляет черновик.
 */

function draft_get($entity, $id) {
  $d = q_val('SELECT data FROM drafts WHERE entity = ? AND entity_id = ?', [$entity, (string)$id]);
  return $d ? json_decode($d, true) : null;
}
function draft_save($entity, $id, array $data) {
  $u = admin_user();
  q('REPLACE INTO drafts (entity, entity_id, data, user_id, updated_at) VALUES (?, ?, ?, ?, ?)', [$entity, (string)$id, json_encode($data, JSON_UNESCAPED_UNICODE), $u['id'] ?? null, now()]);
}
function draft_drop($entity, $id) { q('DELETE FROM drafts WHERE entity = ? AND entity_id = ?', [$entity, (string)$id]); }
function draft_info($entity, $id) { return q_one('SELECT d.updated_at, u.name FROM drafts d LEFT JOIN users u ON u.id = d.user_id WHERE entity = ? AND entity_id = ?', [$entity, (string)$id]); }

function version_add($entity, $id, $data, $action, $note = '') {
  $u = admin_user();
  q_ins('versions', ['entity' => $entity, 'entity_id' => (string)$id, 'data' => json_encode($data, JSON_UNESCAPED_UNICODE), 'action' => $action,
    'user_id' => $u['id'] ?? null, 'user_name' => $u['name'] ?? 'Система', 'note' => mb_substr($note, 0, 200), 'created_at' => now()]);
  // храним последние 50 версий каждой сущности
  q('DELETE FROM versions WHERE entity = ? AND entity_id = ? AND id NOT IN (SELECT id FROM versions WHERE entity = ? AND entity_id = ? ORDER BY id DESC LIMIT 50)', [$entity, (string)$id, $entity, (string)$id]);
}
function versions_list($entity, $id, $limit = 30) {
  return q_all('SELECT id, action, user_name, note, created_at FROM versions WHERE entity = ? AND entity_id = ? ORDER BY id DESC LIMIT ' . (int)$limit, [$entity, (string)$id]);
}

/* ---------- товар ---------- */

/** Текущее опубликованное состояние товара в формате черновика */
function product_snapshot($id) {
  $p = q_one('SELECT * FROM products WHERE id = ?', [$id]);
  if (!$p) return null;
  $d = [];
  foreach (['name', 'slug', 'sku', 'category_id', 'brand_id', 'short', 'description', 'oem', 'kit', 'wb_url', 'ozon_url', 'price_from', 'stock', 'badge', 'sort', 'seo_title', 'seo_desc', 'published'] as $f) $d[$f] = $p[$f];
  $d['attrs'] = [];
  foreach (q_all('SELECT a.code, pa.value_num, pa.value_text FROM product_attrs pa JOIN attributes a ON a.id = pa.attribute_id WHERE pa.product_id = ?', [$id]) as $a)
    $d['attrs'][$a['code']] = $a['value_num'] !== null ? (float)$a['value_num'] : $a['value_text'];
  $d['photos'] = array_map(fn($r) => ['id' => (int)$r['photo_id'], 'alt' => $r['alt']], q_all('SELECT photo_id, alt FROM product_photos WHERE product_id = ? ORDER BY sort', [$id]));
  $d['compat'] = array_map(fn($r) => ['model_id' => (int)$r['model_id'], 'year_from' => $r['year_from'], 'year_to' => $r['year_to'], 'bodies' => $r['bodies'], 'engines' => $r['engines'], 'note' => $r['note']],
    q_all('SELECT * FROM compat WHERE product_id = ? ORDER BY sort, id', [$id]));
  return $d;
}

/** Проверка данных товара. Возвращает массив ошибок [поле => текст]. */
function product_validate($id, array $d) {
  $e = [];
  if (trim($d['name'] ?? '') === '') $e['name'] = 'Укажите название.';
  $sku = trim($d['sku'] ?? '');
  if ($sku === '') $e['sku'] = 'Укажите артикул.';
  elseif (q_val('SELECT id FROM products WHERE sku = ? AND id != ?', [$sku, (int)$id])) $e['sku'] = 'Такой артикул уже есть у другого товара.';
  $slug = trim($d['slug'] ?? '');
  if ($slug !== '' && !preg_match('~^[a-z0-9-]+$~', $slug)) $e['slug'] = 'Адрес — только латиница, цифры и дефис.';
  elseif ($slug !== '' && q_val('SELECT id FROM products WHERE slug = ? AND id != ?', [$slug, (int)$id])) $e['slug'] = 'Такой адрес уже занят.';
  if (empty($d['category_id']) || !q_val('SELECT id FROM categories WHERE id = ?', [(int)$d['category_id']])) $e['category_id'] = 'Выберите категорию.';
  if (!mp_url_ok($d['wb_url'] ?? '', 'wb')) $e['wb_url'] = 'Нужна ссылка вида https://www.wildberries.ru/catalog/…';
  if (!mp_url_ok($d['ozon_url'] ?? '', 'ozon')) $e['ozon_url'] = 'Нужна ссылка вида https://www.ozon.ru/product/…';
  foreach ($d['compat'] ?? [] as $i => $c) {
    if (!q_val('SELECT id FROM models WHERE id = ?', [(int)($c['model_id'] ?? 0)])) { $e['compat'] = 'В строке ' . ($i + 1) . ' совместимости не выбрана модель.'; break; }
    $yf = (int)($c['year_from'] ?? 0); $yt = (int)($c['year_to'] ?? 0);
    if (($yf && ($yf < 1950 || $yf > 2100)) || ($yt && ($yt < 1950 || $yt > 2100)) || ($yf && $yt && $yf > $yt)) { $e['compat'] = 'В строке ' . ($i + 1) . ' совместимости проверьте годы.'; break; }
  }
  foreach (attributes_all() as $a) {
    $v = $d['attrs'][$a['code']] ?? '';
    if ($a['type'] === 'number' && $v !== '' && $v !== null && !is_numeric(str_replace(',', '.', (string)$v))) $e['attr_' . $a['code']] = 'Нужно число.';
  }
  return $e;
}

/** Публикация: переносит данные черновика в таблицы, пишет версию в историю */
function product_publish($id, array $d, $note = 'Опубликовано') {
  db()->beginTransaction();
  try {
    $sku = trim($d['sku']);
    $slug = trim($d['slug'] ?? '') ?: slugify($sku . ' ' . $d['name']);
    $oem = trim((string)($d['oem'] ?? ''));
    $row = [
      'slug' => $slug, 'sku' => $sku, 'sku_norm' => norm_code($sku), 'name' => trim($d['name']),
      'category_id' => (int)$d['category_id'] ?: null, 'brand_id' => (int)($d['brand_id'] ?? 0) ?: null,
      'short' => trim((string)($d['short'] ?? '')), 'description' => trim((string)($d['description'] ?? '')),
      'oem' => $oem, 'oem_norm' => implode(' ', array_filter(array_map('norm_code', list_split($oem)))), 'kit' => trim((string)($d['kit'] ?? '')),
      'wb_url' => mp_clean($d['wb_url'] ?? ''), 'ozon_url' => mp_clean($d['ozon_url'] ?? ''),
      'price_from' => ($d['price_from'] ?? '') !== '' && $d['price_from'] !== null ? (int)$d['price_from'] : null,
      'stock' => in_array($d['stock'] ?? '', ['in', 'order', 'request'], true) ? $d['stock'] : 'request',
      'badge' => trim((string)($d['badge'] ?? '')), 'sort' => (int)($d['sort'] ?? 0),
      'seo_title' => trim((string)($d['seo_title'] ?? '')), 'seo_desc' => trim((string)($d['seo_desc'] ?? '')),
      'published' => !empty($d['published']) ? 1 : 0, 'updated_at' => now(),
    ];
    if ($row['published']) $row['published_at'] = now();
    q_upd('products', $row, $id);
    q('DELETE FROM product_attrs WHERE product_id = ?', [$id]);
    foreach (attributes_all() as $a) {
      $v = $d['attrs'][$a['code']] ?? '';
      if ($v === '' || $v === null) continue;
      $num = $a['type'] === 'number' ? (float)str_replace(',', '.', (string)$v) : null;
      q('INSERT INTO product_attrs (product_id, attribute_id, value_num, value_text) VALUES (?, ?, ?, ?)', [$id, $a['id'], $num, $a['type'] === 'number' ? null : trim((string)$v)]);
    }
    q('DELETE FROM product_photos WHERE product_id = ?', [$id]);
    foreach (array_values($d['photos'] ?? []) as $i => $ph) {
      if (!q_val('SELECT id FROM photos WHERE id = ?', [(int)$ph['id']])) continue;
      q('INSERT OR IGNORE INTO product_photos (product_id, photo_id, sort, alt) VALUES (?, ?, ?, ?)', [$id, (int)$ph['id'], $i, trim((string)($ph['alt'] ?? ''))]);
    }
    q('DELETE FROM compat WHERE product_id = ?', [$id]);
    foreach (array_values($d['compat'] ?? []) as $i => $c) {
      q_ins('compat', ['product_id' => $id, 'model_id' => (int)$c['model_id'], 'year_from' => (int)($c['year_from'] ?? 0) ?: null, 'year_to' => (int)($c['year_to'] ?? 0) ?: null,
        'bodies' => implode(', ', list_split($c['bodies'] ?? '')), 'engines' => implode(', ', list_split($c['engines'] ?? '')), 'note' => trim((string)($c['note'] ?? '')), 'sort' => $i]);
    }
    draft_drop('product', $id);
    version_add('product', $id, product_snapshot($id), 'publish', $note);
    db()->commit();
  } catch (Throwable $e) {
    db()->rollBack();
    throw $e;
  }
}

/* ---------- страницы ---------- */

function content_publish($key, array $data, $note = 'Опубликовано') {
  setting_save('content.' . $key, $data);
  draft_drop('content', $key);
  version_add('content', $key, $data, 'publish', $note);
}
