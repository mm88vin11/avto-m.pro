<?php
if (!defined('AVTOM')) { http_response_code(404); exit; }

function api_route($name, $method) {
  header('X-Robots-Tag: noindex');
  switch ($name) {
    case 'lead':
      if ($method !== 'POST') json_out(['ok' => false], 405);
      $ct = $_SERVER['CONTENT_TYPE'] ?? '';
      $in = stripos($ct, 'application/json') !== false ? (json_decode(file_get_contents('php://input'), true) ?: []) : $_POST;
      $isJson = stripos($ct, 'application/json') !== false || ($_SERVER['HTTP_ACCEPT'] ?? '') === 'application/json';
      // проверяем, что запрос пришёл с нашего сайта
      $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
      if ($origin && parse_url($origin, PHP_URL_HOST) !== parse_url(cfg('site_url'), PHP_URL_HOST) && !cfg('dev') && parse_url($origin, PHP_URL_HOST) !== ($_SERVER['HTTP_HOST'] ?? '')) json_out(['ok' => false, 'errors' => ['form' => 'Запрос отклонён.']], 403);
      if (!empty($in['website'])) json_out(['ok' => true, 'id' => 0]); // ловушка для ботов
      if (is_string($in['items'] ?? null)) $in['items'] = json_decode($in['items'], true) ?: [];
      $r = lead_create($in);
      if ($isJson) json_out($r, $r['ok'] ? 200 : 422);
      // без JS: возвращаемся на страницу с сообщением
      $back = $in['page'] ?? '/';
      if (!is_string($back) || $back === '' || $back[0] !== '/') $back = '/';
      site_headers();
      page('lead_result', ['r' => $r, 'back' => $back]);
      exit;

    case 'search':
      $qs = trim(mb_substr((string)($_GET['q'] ?? ''), 0, 80));
      if (mb_strlen($qs) < 2) json_out(['items' => [], 'total' => 0]);
      $ids = search_ids($qs);
      $items = products_public_json(array_slice($ids, 0, 6));
      $cats = [];
      $qn = search_norm($qs);
      foreach (categories_visible() as $c) if (mb_strpos(search_norm($c['name']), $qn) !== false) $cats[] = ['name' => $c['name'], 'url' => '/catalog/' . $c['slug']];
      $models = [];
      foreach (car_tree() as $mk) foreach ($mk['models'] as $m) {
        $t = search_norm($mk['name'] . ' ' . $m['name']);
        if (strpos(' ' . $t, ' ' . explode(' ', $qn)[0]) !== false && count($models) < 3) $models[] = ['name' => $mk['name'] . ' ' . $m['name'], 'url' => '/avto/' . $mk['slug'] . '/' . $m['slug']];
      }
      json_out(['items' => $items, 'total' => count($ids), 'cats' => $cats, 'models' => $models]);

    case 'products':
      $ids = array_slice(array_filter(array_map('intval', explode(',', (string)($_GET['ids'] ?? '')))), 0, 50);
      json_out(['items' => products_public_json($ids), 'attrs' => array_values(array_map(fn($a) => $a['name'], array_filter(attributes_all(), fn($a) => $a['in_card'])))]);

    case 'count':
      $cat = null;
      if (!empty($_GET['cat'])) $cat = category_by_slug((string)$_GET['cat']);
      $r = catalog_query($cat['id'] ?? null, filters_from_query());
      json_out(['n' => count($r['items'])]);

    default:
      json_out(['ok' => false], 404);
  }
}
