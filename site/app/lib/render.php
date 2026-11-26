<?php
if (!defined('AVTOM')) { http_response_code(404); exit; }

$GLOBALS['META'] = [];

const JS_BOOT = "document.documentElement.classList.add('js');setTimeout(function(){if(!window.__avtom)document.documentElement.classList.remove('js')},2500);";
function js_boot_hash() { return "'sha256-" . base64_encode(hash('sha256', JS_BOOT, true)) . "'"; }

function site_headers() {
  header('Content-Type: text/html; charset=utf-8');
  header('X-Content-Type-Options: nosniff');
  header('Referrer-Policy: strict-origin-when-cross-origin');
  header('X-Frame-Options: SAMEORIGIN');
  header('Permissions-Policy: camera=(), microphone=(), geolocation=(), payment=(), usb=(), interest-cohort=()');
  header("Content-Security-Policy: default-src 'self'; script-src 'self' " . js_boot_hash() . " https://mc.yandex.ru https://yastatic.net; style-src 'self' 'unsafe-inline'; img-src 'self' data: https://mc.yandex.ru https://mc.yandex.com; font-src 'self'; connect-src 'self' https://mc.yandex.ru https://mc.yandex.com; frame-src https://yandex.ru https://mc.yandex.ru https://mc.yandex.com; frame-ancestors 'self'; form-action 'self'; base-uri 'self'; object-src 'none'");
  if (is_https()) header('Strict-Transport-Security: max-age=31536000');
  if (preview_mode()) header('X-Robots-Tag: noindex, nofollow');
}

function meta($k, $v = null) { if ($v === null) return $GLOBALS['META'][$k] ?? null; $GLOBALS['META'][$k] = $v; }
function jsonld($data) { $GLOBALS['META']['jsonld'][] = $data; }

/** Рендер страницы: вид из app/views/{name}.php внутри общего шаблона */
function page($name, $vars = []) {
  extract($vars);
  ob_start();
  require APP_DIR . '/views/' . $name . '.php';
  $content = ob_get_clean();
  require APP_DIR . '/views/layout.php';
}
function partial($name, $vars = []) { extract($vars); require APP_DIR . '/views/_' . $name . '.php'; }

function not_found() {
  if (!headers_sent()) { site_headers(); http_response_code(404); }
  meta('robots', 'noindex, follow');
  page('404');
  exit;
}

/** Иконка из спрайта */
function icon($name, $cls = '') { return '<svg class="i ' . h($cls) . '" aria-hidden="true" focusable="false"><use href="#i-' . h($name) . '"/></svg>'; }

function breadcrumbs(array $items) {
  // $items: [[название, адрес|null], ...]; первый — «Главная»
  $all = array_merge([['Главная', '/']], $items);
  $ld = ['@context' => 'https://schema.org', '@type' => 'BreadcrumbList', 'itemListElement' => []];
  $html = '<nav class="crumbs" aria-label="Навигационная цепочка"><ol>';
  foreach ($all as $i => [$t, $u]) {
    $last = $i === count($all) - 1;
    $html .= '<li>' . ($u && !$last ? '<a href="' . h($u) . '">' . h($t) . '</a>' : '<span' . ($last ? ' aria-current="page"' : '') . '>' . h($t) . '</span>') . '</li>';
    $ld['itemListElement'][] = ['@type' => 'ListItem', 'position' => $i + 1, 'name' => $t] + ($u ? ['item' => site_url($u)] : []);
  }
  jsonld($ld);
  return $html . '</ol></nav>';
}

/** Фото товара с адаптивными размерами */
function picture($ph, $alt, $sizes = '(max-width: 640px) 50vw, 300px', $eager = false, $cls = '') {
  if (!$ph) return '<div class="ph-empty ' . h($cls) . '" role="img" aria-label="' . h($alt) . ' — фото скоро появится">' . icon('photo') . '<span>Фото скоро появится</span></div>';
  $w = 800; $hgt = $ph['h'] && $ph['w'] ? (int)round($ph['h'] * 800 / $ph['w']) : 1067;
  return '<img class="' . h($cls) . '" src="' . h(img_url($ph, 800)) . '" srcset="' . h(img_srcset($ph)) . '" sizes="' . h($sizes) . '" width="' . $w . '" height="' . $hgt . '" alt="' . h($alt) . '"'
    . ($eager ? ' fetchpriority="high"' : ' loading="lazy" decoding="async"') . '>';
}

/** Кнопки маркетплейсов */
function mp_buttons($p, $size = '') {
  $out = '';
  if ($p['wb_url']) $out .= '<a class="btn btn-wb ' . $size . '" href="/go/wb/' . (int)$p['id'] . '" target="_blank" rel="nofollow noopener" data-goal="mp_wb" data-mp="wb">' . icon('bag') . '<span>Wildberries</span></a>';
  if ($p['ozon_url']) $out .= '<a class="btn btn-ozon ' . $size . '" href="/go/ozon/' . (int)$p['id'] . '" target="_blank" rel="nofollow noopener" data-goal="mp_ozon" data-mp="ozon">' . icon('bag') . '<span>Ozon</span></a>';
  return $out;
}

/** Ключевые характеристики товара для карточки в списке */
function product_key_specs($p, $limit = 3) {
  $out = [];
  foreach (attributes_all() as $a) {
    if (!$a['in_card'] || !isset($p['attrs'][$a['code']]) || $a['type'] !== 'number') continue;
    $out[] = [$a['name'], attr_value($a, $p['attrs'][$a['code']])];
    if (count($out) >= $limit) break;
  }
  return $out;
}

function legal_tokens($text) {
  $s = site();
  $map = [
    '{org_name}' => $s['org_name'], '{inn}' => $s['inn'], '{ogrnip}' => $s['ogrnip'], '{address}' => $s['legal_address'] ?: $s['address'],
    '{email}' => $s['email'], '{privacy_email}' => $s['privacy_email'] ?: $s['email'], '{phone}' => $s['phone'],
    '{site}' => preg_replace('~^https?://~', '', site_url()), '{date}' => date('d.m.Y', strtotime($s['consent_version'] ?: 'today')),
  ];
  return strtr($text, $map);
}

/** Данные для подбора (встраиваются в страницу, без лишних запросов) */
function car_tree_json() {
  $out = [];
  foreach (car_tree() as $mk) {
    $models = [];
    foreach ($mk['models'] as $m) $models[] = ['s' => $m['slug'], 'n' => $m['name'], 'yf' => $m['yf'], 'yt' => $m['yt'], 'b' => $m['bodies'], 'e' => $m['engines']];
    $out[] = ['s' => $mk['slug'], 'n' => $mk['name'], 'm' => $models];
  }
  return json_encode($out, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP);
}

function org_jsonld() {
  $s = site();
  return [
    '@context' => 'https://schema.org', '@type' => 'AutoPartsStore', '@id' => site_url('/#org'), 'name' => $s['name'],
    'slogan' => $s['tagline'], 'url' => site_url('/'), 'logo' => site_url('/assets/icons/icon-512.png'), 'image' => site_url('/assets/img/og-default.jpg'),
    'telephone' => preg_replace('~[^\d+]~', '', $s['phone']), 'email' => $s['email'],
    'address' => ['@type' => 'PostalAddress', 'addressCountry' => 'RU', 'addressRegion' => $s['region'], 'addressLocality' => $s['city'], 'streetAddress' => $s['street']],
    'openingHoursSpecification' => [['@type' => 'OpeningHoursSpecification', 'dayOfWeek' => ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'], 'opens' => $s['hours_open'], 'closes' => $s['hours_close']]],
    'taxID' => $s['inn'],
  ];
}

/** Нормализованная строка запроса без служебных параметров — для canonical и ссылок */
function qs_without(array $drop, array $extra = []) {
  $q = $_GET;
  foreach ($drop as $k) unset($q[$k]);
  $q = array_merge($q, $extra);
  $q = array_filter($q, fn($v) => $v !== '' && $v !== null && $v !== []);
  return $q ? '?' . http_build_query($q) : '';
}
