<?php
if (!defined('AVTOM')) { http_response_code(404); exit; }

function h($s) { return htmlspecialchars((string)$s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
function now() { return date('Y-m-d H:i:s'); }
function is_https() { return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https') || (($_SERVER['SERVER_PORT'] ?? '') == 443); }
function site_url($path = '') { return rtrim(cfg('site_url'), '/') . $path; }
function asset($path) {
  $f = WEB_DIR . '/assets/' . $path;
  $v = is_file($f) ? substr(md5((string)filemtime($f)), 0, 8) : APP_VERSION;
  return '/assets/' . $path . '?v=' . $v;
}
function json_out($data, $code = 200) {
  http_response_code($code);
  header('Content-Type: application/json; charset=utf-8');
  header('Cache-Control: no-store');
  echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
  exit;
}
function redirect($to, $code = 302) { header('Location: ' . $to, true, $code); exit; }
function client_ip() { return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0'; }
function ip_hash() { return substr(hash('sha256', client_ip() . '|' . secret_salt()), 0, 16); }
function secret_salt() {
  $f = cfg('data_dir') . '/salt.txt';
  if (!is_file($f)) @file_put_contents($f, bin2hex(random_bytes(24)));
  return (string)@file_get_contents($f);
}

/** Транслит для адресов страниц */
function slugify($s) {
  $map = ['а'=>'a','б'=>'b','в'=>'v','г'=>'g','д'=>'d','е'=>'e','ё'=>'e','ж'=>'zh','з'=>'z','и'=>'i','й'=>'y','к'=>'k','л'=>'l','м'=>'m','н'=>'n','о'=>'o','п'=>'p','р'=>'r','с'=>'s','т'=>'t','у'=>'u','ф'=>'f','х'=>'h','ц'=>'ts','ч'=>'ch','ш'=>'sh','щ'=>'sch','ъ'=>'','ы'=>'y','ь'=>'','э'=>'e','ю'=>'yu','я'=>'ya'];
  $s = mb_strtolower(trim((string)$s));
  $s = strtr($s, $map);
  $s = preg_replace('~[^a-z0-9]+~', '-', $s);
  return trim(preg_replace('~-+~', '-', $s), '-') ?: 'item';
}
/** Артикул/OEM для поиска: без пробелов, дефисов и точек */
function norm_code($s) { return preg_replace('~[^a-z0-9а-я]~u', '', mb_strtolower((string)$s)); }

function plural($n, $one, $few, $many) {
  $n = abs((int)$n) % 100; $n1 = $n % 10;
  if ($n > 10 && $n < 20) return $many;
  if ($n1 > 1 && $n1 < 5) return $few;
  if ($n1 == 1) return $one;
  return $many;
}
function fmt_num($n) { return rtrim(rtrim(number_format((float)$n, 1, ',', ' '), '0'), ','); }
function fmt_price($n) { return number_format((int)$n, 0, ',', ' ') . ' ₽'; }
function phone_href($p) { $d = preg_replace('~\D~', '', (string)$p); if (strlen($d) == 11 && $d[0] == '8') $d = '7' . substr($d, 1); return 'tel:+' . $d; }
function ru_date($dt, $time = true) {
  if (!$dt) return '';
  $t = strtotime($dt);
  $m = ['', 'января', 'февраля', 'марта', 'апреля', 'мая', 'июня', 'июля', 'августа', 'сентября', 'октября', 'ноября', 'декабря'];
  $s = date('j', $t) . ' ' . $m[(int)date('n', $t)];
  if (date('Y', $t) != date('Y')) $s .= ' ' . date('Y', $t);
  return $time ? $s . ', ' . date('H:i', $t) : $s;
}

/**
 * Простая разметка текстов из админки:
 * пустая строка — абзац; «- » — список; «## » — подзаголовок; **жирный**; [текст](ссылка).
 */
function md($text) {
  $text = str_replace("\r", '', (string)$text);
  $blocks = preg_split("~\n{2,}~", trim($text));
  $out = '';
  foreach ($blocks as $b) {
    $b = trim($b);
    if ($b === '') continue;
    $lines = explode("\n", $b);
    if (preg_match('~^#{2,3}\s+~', $b)) {
      $lvl = strpos($b, '###') === 0 ? 3 : 2;
      $out .= "<h$lvl>" . md_inline(preg_replace('~^#{2,3}\s+~', '', $lines[0])) . "</h$lvl>";
      if (count($lines) > 1) $out .= md(implode("\n", array_slice($lines, 1)));
      continue;
    }
    if (preg_match('~^(-|\d+\.)\s+~', $lines[0])) {
      $ol = (bool)preg_match('~^\d+\.~', $lines[0]);
      $out .= $ol ? '<ol>' : '<ul>';
      foreach ($lines as $l) {
        if (preg_match('~^(-|\d+\.)\s+(.*)$~', $l, $m)) $out .= '<li>' . md_inline($m[2]) . '</li>';
        else $out = preg_replace('~</li>$~', ' ' . md_inline($l) . '</li>', $out);
      }
      $out .= $ol ? '</ol>' : '</ul>';
      continue;
    }
    $out .= '<p>' . implode('<br>', array_map('md_inline', $lines)) . '</p>';
  }
  return $out;
}
function md_inline($s) {
  $s = h($s);
  $s = preg_replace('~\*\*(.+?)\*\*~u', '<strong>$1</strong>', $s);
  $s = preg_replace_callback('~\[([^\]]+)\]\(([^)\s]+)\)~u', function ($m) {
    $url = html_entity_decode($m[2], ENT_QUOTES);
    if (!preg_match('~^(https?://|/|mailto:|tel:|#)~i', $url)) return $m[1];
    $ext = preg_match('~^https?://~i', $url) && strpos($url, site_url()) !== 0;
    return '<a href="' . h($url) . '"' . ($ext ? ' target="_blank" rel="noopener"' : '') . '>' . $m[1] . '</a>';
  }, $s);
  return $s;
}
function md_plain($text, $len = 160) {
  $t = preg_replace(['~\*\*~', '~\[([^\]]+)\]\([^)]+\)~', '~^#+\s*~m', '~^-\s+~m', '~\s+~u'], ['', '$1', '', '', ' '], (string)$text);
  $t = trim($t);
  if (mb_strlen($t) > $len) $t = rtrim(mb_substr($t, 0, $len - 1), " ,.;:—-") . '…';
  return $t;
}

/** Проверка адреса маркетплейса: только https и только домены WB/Ozon */
function mp_url_ok($url, $mp) {
  if ($url === '') return true;
  $p = parse_url($url);
  if (!$p || ($p['scheme'] ?? '') !== 'https' || empty($p['host'])) return false;
  $host = strtolower($p['host']);
  $allowed = $mp === 'wb' ? ['wildberries.ru', 'wb.ru'] : ['ozon.ru'];
  foreach ($allowed as $d) if ($host === $d || substr($host, -strlen('.' . $d)) === '.' . $d) return true;
  return false;
}
/** Убираем служебные метки поиска маркетплейса. Метки utm_* оставляем: ими WB и Ozon считают внешний трафик продавца */
function mp_clean($url) {
  $url = trim((string)$url);
  if ($url === '') return '';
  $p = parse_url($url);
  if (!$p) return $url;
  $q = [];
  if (!empty($p['query'])) parse_str($p['query'], $q);
  foreach (['at', 'sh', 'from', 'asb', 'asb2', 'keywords', 'targetUrl'] as $k) unset($q[$k]);
  $s = ($p['scheme'] ?? 'https') . '://' . ($p['host'] ?? '') . ($p['path'] ?? '');
  if ($q) $s .= '?' . http_build_query($q);
  return $s;
}

/** Метки перехода с сайта (Настройки → «Метки переходов»), если в ссылке своих меток нет */
function mp_tagged($url) {
  $tags = trim((string)site('mp_tags'), " ?&");
  if ($tags === '' || preg_match('~[?&]utm_source=~', $url)) return $url;
  parse_str($tags, $t); $t = array_filter($t, fn($v, $k) => preg_match('~^utm_(source|medium|campaign|content|term)$~', $k) && is_string($v) && $v !== '', ARRAY_FILTER_USE_BOTH);
  return $t ? $url . (strpos($url, '?') === false ? '?' : '&') . http_build_query($t) : $url;
}

function log_err($msg) { error_log('[avtom] ' . $msg); }
