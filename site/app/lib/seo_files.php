<?php
if (!defined('AVTOM')) { http_response_code(404); exit; }

function robots_txt() {
  header('Content-Type: text/plain; charset=utf-8');
  echo "User-agent: *\n";
  foreach (['/admin', '/api/', '/go/', '/cart', '/compare', '/search', '/cron', '/*?*sort=', '/*?*view=', '/*?*preview='] as $d) echo "Disallow: $d\n";
  echo "Clean-param: utm_source&utm_medium&utm_campaign&utm_content&utm_term&yclid&from\n";
  echo "\nSitemap: " . site_url('/sitemap.xml') . "\n";
}

function sitemap_xml() {
  header('Content-Type: application/xml; charset=utf-8');
  $urls = [];
  $add = function ($path, $mod = null, $prio = '0.6', $img = []) use (&$urls) { $urls[] = [$path, $mod, $prio, $img]; };
  $last = q_val('SELECT MAX(updated_at) FROM products WHERE published = 1');
  $add('/', $last, '1.0');
  $add('/catalog', $last, '0.9');
  foreach (categories_visible() as $c) $add('/catalog/' . $c['slug'], $c['updated_at'], '0.9');
  foreach (products_all() as $p) $add(product_url($p), $p['updated_at'], '0.8', array_map(fn($x) => site_url(img_og($x)), array_slice($p['photos'], 0, 5)));
  foreach (brands_all() as $b) if ($b['cnt']) $add('/brand/' . $b['slug'], $b['updated_at'], '0.6');
  $add('/avto', $last, '0.6');
  foreach (car_tree() as $mk) { $add('/avto/' . $mk['slug'], $last, '0.6'); foreach ($mk['models'] as $m) $add('/avto/' . $mk['slug'] . '/' . $m['slug'], $last, '0.7'); }
  foreach (['/podbor' => '0.7', '/how-to-buy' => '0.5', '/about' => '0.5', '/partners' => '0.5', '/contacts' => '0.6', '/legal/privacy' => '0.2', '/legal/consent' => '0.2', '/legal/cookies' => '0.2', '/legal/terms' => '0.2'] as $u => $pr) $add($u, null, $pr);
  echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
  echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">' . "\n";
  foreach ($urls as [$u, $m, $pr, $imgs]) {
    echo '<url><loc>' . h(site_url($u)) . '</loc>' . ($m ? '<lastmod>' . date('Y-m-d', strtotime($m)) . '</lastmod>' : '') . '<priority>' . $pr . '</priority>';
    foreach ($imgs as $i) echo '<image:image><image:loc>' . h($i) . '</image:loc></image:image>';
    echo "</url>\n";
  }
  echo '</urlset>';
}

function webmanifest() {
  header('Content-Type: application/manifest+json; charset=utf-8');
  echo json_encode([
    'name' => 'AVTO-M — автозапчасти', 'short_name' => 'AVTO-M', 'lang' => 'ru', 'start_url' => '/', 'display' => 'standalone',
    'background_color' => '#f1f4f9', 'theme_color' => '#0a1f4d',
    'icons' => [
      ['src' => '/assets/icons/icon-192.png', 'sizes' => '192x192', 'type' => 'image/png'],
      ['src' => '/assets/icons/icon-512.png', 'sizes' => '512x512', 'type' => 'image/png'],
      ['src' => '/assets/icons/icon-maskable-512.png', 'sizes' => '512x512', 'type' => 'image/png', 'purpose' => 'maskable'],
    ],
  ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
}
