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
define('APP_VERSION', '1.2.0');

ini_set('display_errors', '0');
ini_set('log_errors', '1');
mb_internal_encoding('UTF-8');
date_default_timezone_set('Europe/Moscow');

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

if (!is_dir(cfg('data_dir'))) @mkdir(cfg('data_dir'), 0750, true);
ini_set('error_log', cfg('data_dir') . '/error.log');

require __DIR__ . '/helpers.php';
require __DIR__ . '/db.php';
require __DIR__ . '/content.php';
require __DIR__ . '/catalog.php';
require __DIR__ . '/images.php';
require __DIR__ . '/auth.php';
require __DIR__ . '/mail.php';
require __DIR__ . '/leads.php';
require __DIR__ . '/drafts.php';
require __DIR__ . '/backup.php';
