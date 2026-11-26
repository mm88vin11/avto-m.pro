<?php
/** AVTO-M · единая точка входа: витрина, API, служебные файлы и панель управления. */
require __DIR__ . '/app/lib/bootstrap.php';
require APP_DIR . '/lib/render.php';

$path = rawurldecode(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/');
if ($path !== '/' && substr($path, -1) === '/') redirect(rtrim($path, '/') . (($qs = $_SERVER['QUERY_STRING'] ?? '') ? '?' . $qs : ''), 301);
if (preg_match('~//|\.\.~', $path)) not_found();
$seg = array_values(array_filter(explode('/', $path), 'strlen'));
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

try {
  if (($seg[0] ?? '') === 'admin') { require APP_DIR . '/admin/router.php'; exit; }
  if (($seg[0] ?? '') === 'api') { require APP_DIR . '/lib/api.php'; api_route($seg[1] ?? '', $method); exit; }

  site_headers();
  switch ($seg[0] ?? '') {
    case '': page('home'); break;
    case 'catalog':
      if (count($seg) === 1) page('catalog');
      elseif (count($seg) === 2 && ($cat = category_by_slug($seg[1]))) page('catalog', ['cat' => $cat]);
      else not_found();
      break;
    case 'product':
      if (count($seg) === 2 && ($p = product_by_slug($seg[1]))) page('product', ['p' => $p]);
      else not_found();
      break;
    case 'brand':
      $b = count($seg) === 2 ? q_one('SELECT * FROM brands WHERE slug = ? AND published = 1', [$seg[1]]) : null;
      $b ? page('brand', ['b' => $b]) : not_found();
      break;
    case 'avto':
      if (count($seg) === 1) page('cars');
      elseif (count($seg) === 2 && ($mk = car_tree_find($seg[1]))) page('cars', ['make' => $mk]);
      elseif (count($seg) === 3 && ($m = car_tree_find($seg[1], $seg[2]))) page('car', ['make' => $m['make'], 'model' => $m['model']]);
      else not_found();
      break;
    case 'podbor': count($seg) === 1 ? page('pick') : not_found(); break;
    case 'search': page('search'); break;
    case 'cart': page('cart'); break;
    case 'compare': page('compare'); break;
    case 'about': page('about'); break;
    case 'partners': page('partners'); break;
    case 'contacts': page('contacts'); break;
    case 'how-to-buy': page('howto'); break;
    case 'karta-sajta': page('sitemap'); break;
    case 'legal':
      $map = ['privacy' => 'legal_privacy', 'consent' => 'legal_consent', 'cookies' => 'legal_cookies', 'terms' => 'legal_terms'];
      (count($seg) === 2 && isset($map[$seg[1]])) ? page('legal', ['key' => $map[$seg[1]], 'slug' => $seg[1]]) : not_found();
      break;
    case 'go':
      // переход на маркетплейс: считаем клик (без персональных данных) и уводим на карточку
      $mp = $seg[1] ?? ''; $id = (int)($seg[2] ?? 0);
      $p = products_all()[$id] ?? null;
      $url = $p ? ($mp === 'wb' ? $p['wb_url'] : ($mp === 'ozon' ? $p['ozon_url'] : '')) : '';
      if (!$url || !mp_url_ok($url, $mp)) not_found();
      if (!preg_match('~bot|crawl|spider|preview~i', $_SERVER['HTTP_USER_AGENT'] ?? '')) {
        try { q('INSERT INTO clicks (day, product_id, mp, n) VALUES (?, ?, ?, 1) ON CONFLICT(day, product_id, mp) DO UPDATE SET n = n + 1', [date('Y-m-d'), $id, $mp]); } catch (Throwable $e) {}
      }
      header('X-Robots-Tag: noindex, nofollow');
      redirect($url, 302);
    case 'robots.txt': require APP_DIR . '/lib/seo_files.php'; robots_txt(); break;
    case 'sitemap.xml': require APP_DIR . '/lib/seo_files.php'; sitemap_xml(); break;
    case 'site.webmanifest': require APP_DIR . '/lib/seo_files.php'; webmanifest(); break;
    case 'cron':
      if (cfg('cron_key') && hash_equals((string)cfg('cron_key'), (string)($_GET['key'] ?? ''))) { $b = backup_auto(); echo $b ? "backup: $b" : 'ok'; }
      else not_found();
      break;
    default: not_found();
  }
} catch (Throwable $e) {
  log_err($e->getMessage() . ' @ ' . $e->getFile() . ':' . $e->getLine());
  if (!headers_sent()) http_response_code(500);
  if (cfg('dev')) echo '<pre>' . h($e) . '</pre>';
  else readfile(__DIR__ . '/assets/500.html');
}
