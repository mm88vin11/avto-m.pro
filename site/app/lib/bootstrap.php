<?php
/**
 * AVTO-M · общая точка входа. Подключается из /index.php.
 * Раскладка: /app — код (закрыт .htaccess), /app/data — база SQLite, журналы, копии (закрыт), /uploads — фото.
 */
if (!defined('AVTOM')) define('AVTOM', 1);
if (defined('AVTOM_BOOTED')) return;
define('AVTOM_BOOTED', 1);
define('APP_DIR', dirname(__DIR__));
define('WEB_DIR', dirname(APP_DIR));
define('APP_VERSION', '1.9.0');

ini_set('display_errors', '0');
ini_set('log_errors', '1');
mb_internal_encoding('UTF-8');
date_default_timezone_set('Europe/Moscow');

$c = null;
$GLOBALS['CFG'] = [
  'site_url' => 'https://avto-m.pro',
  'data_dir' => APP_DIR . '/data',
  'dev' => false,
  // SMTP: пароль только здесь, в config.php (в архив и git не попадает)
  'smtp_host' => '', 'smtp_port' => 465, 'smtp_secure' => 'ssl', 'smtp_user' => '', 'smtp_pass' => '',
  'mail_from' => '', 'mail_from_name' => 'Сайт AVTO-M',
  'backup_keep' => 14,
  'cron_key' => '',
];
if (is_file(APP_DIR . '/config.php')) {
  $c = require APP_DIR . '/config.php';
  if (is_array($c)) $GLOBALS['CFG'] = array_merge($GLOBALS['CFG'], $c);
}
function cfg($k, $d = null) { return $GLOBALS['CFG'][$k] ?? $d; }

/*
 * Сайт может жить в корне домена (avto-m.pro) или в подпапке (например, домен.ру/avto-m).
 * BASE — подпапка без косой черты в конце ('' для корня). Определяется сама по расположению index.php,
 * её можно задать в config.php: 'base' => '/avto-m'.
 * Код сайта пишет адреса от корня ('/catalog'); подпапку к ним добавляет base_filter() при выводе,
 * а из адреса запроса она убирается здесь же — маршруты не знают о подпапке.
 */
if (!defined('BASE')) {
  $b = cfg('base');
  if ($b === null) {
    $sn = str_replace('\\', '/', (string)($_SERVER['SCRIPT_NAME'] ?? ''));
    $b = preg_match('~^(.*)/index\.php$~', $sn, $m) ? $m[1] : '';
  }
  define('BASE', rtrim((string)$b, '/'));
}
if (BASE !== '' && isset($_SERVER['REQUEST_URI'])) {
  $_SERVER['AVTOM_FULL_URI'] = $_SERVER['REQUEST_URI'];
  $u = $_SERVER['REQUEST_URI'];
  if (strpos($u, BASE . '/') === 0 || $u === BASE || strpos($u, BASE . '?') === 0) $_SERVER['REQUEST_URI'] = '/' . ltrim((string)substr($u, strlen(BASE)), '/');
}
// Адрес сайта: из config.php или по текущему запросу (тогда сайт работает на любом домене и в любой подпапке)
$host = $_SERVER['HTTP_HOST'] ?? '';
if (!(is_array($c) && !empty($c['site_url'])) && $host !== '' && preg_match('~^[a-z0-9.:\[\]-]+$~i', $host)) {
  $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https') || (($_SERVER['SERVER_PORT'] ?? '') == 443);
  $GLOBALS['CFG']['site_url'] = ($https ? 'https://' : 'http://') . strtolower($host) . BASE;
}
unset($c, $b, $sn, $m, $u, $host, $https);

if (!is_dir(cfg('data_dir'))) @mkdir(cfg('data_dir'), 0750, true);
ini_set('error_log', cfg('data_dir') . '/error.log');

require __DIR__ . '/helpers.php';
require __DIR__ . '/db.php';
require __DIR__ . '/content.php';
require __DIR__ . '/catalog.php';
require __DIR__ . '/images.php';
require __DIR__ . '/site_images.php';
require __DIR__ . '/auth.php';
require __DIR__ . '/mail.php';
require __DIR__ . '/leads.php';
require __DIR__ . '/drafts.php';
require __DIR__ . '/backup.php';
