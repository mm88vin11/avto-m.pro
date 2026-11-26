<?php
if (!defined('AVTOM')) { http_response_code(404); exit; }

function session_start_admin() {
  if (session_status() === PHP_SESSION_ACTIVE) return;
  $dir = cfg('data_dir') . '/sessions';
  if (!is_dir($dir)) @mkdir($dir, 0700, true);
  if (is_writable($dir)) session_save_path($dir);
  ini_set('session.use_strict_mode', '1');
  ini_set('session.use_only_cookies', '1');
  ini_set('session.gc_maxlifetime', '43200');
  session_name('avtom_admin');
  session_set_cookie_params(['lifetime' => 0, 'path' => '/', 'secure' => is_https(), 'httponly' => true, 'samesite' => 'Lax']);
  session_start();
  if (isset($_SESSION['seen']) && time() - $_SESSION['seen'] > 12 * 3600) { $_SESSION = []; session_regenerate_id(true); }
  $_SESSION['seen'] = time();
}

/** Текущий сотрудник или null */
function admin_user() {
  static $u = false;
  if ($u !== false) return $u;
  session_start_admin();
  $u = null;
  if (!empty($_SESSION['uid'])) {
    $u = q_one('SELECT * FROM users WHERE id = ? AND active = 1', [$_SESSION['uid']]);
    if ($u && ($_SESSION['ph'] ?? '') !== substr($u['pass_hash'], -12)) $u = null; // пароль сменён — выходим везде
  }
  return $u;
}
/** Проверка входа без старта сессии для обычных посетителей */
function admin_user_quiet() {
  if (empty($_COOKIE['avtom_admin'])) return null;
  return admin_user();
}
function is_owner() { $u = admin_user(); return $u && $u['role'] === 'owner'; }

function admin_login($login, $pass) {
  $ip = ip_hash();
  $since = time() - 900;
  q('DELETE FROM login_attempts WHERE at < ?', [time() - 86400]);
  if (q_val('SELECT COUNT(*) FROM login_attempts WHERE ip = ? AND at > ?', [$ip, $since]) >= 8) return 'Слишком много попыток. Подождите 15 минут.';
  $u = q_one('SELECT * FROM users WHERE login = ? AND active = 1', [trim($login)]);
  if (!$u || !password_verify($pass, $u['pass_hash'])) {
    q('INSERT INTO login_attempts (ip, login, at) VALUES (?, ?, ?)', [$ip, mb_substr($login, 0, 60), time()]);
    usleep(400000);
    return 'Неверный логин или пароль.';
  }
  if (password_needs_rehash($u['pass_hash'], PASSWORD_DEFAULT)) q('UPDATE users SET pass_hash = ? WHERE id = ?', [password_hash($pass, PASSWORD_DEFAULT), $u['id']]);
  session_regenerate_id(true);
  $_SESSION['uid'] = $u['id'];
  $_SESSION['ph'] = substr(q_val('SELECT pass_hash FROM users WHERE id = ?', [$u['id']]), -12);
  q('UPDATE users SET last_login = ? WHERE id = ?', [now(), $u['id']]);
  q('DELETE FROM login_attempts WHERE ip = ?', [$ip]);
  return null;
}
function admin_logout() { session_start_admin(); $_SESSION = []; session_regenerate_id(true); session_destroy(); }

function csrf_token() { session_start_admin(); if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(24)); return $_SESSION['csrf']; }
function csrf_field() { return '<input type="hidden" name="_csrf" value="' . h(csrf_token()) . '">'; }
function csrf_ok() { $t = (string)($_POST['_csrf'] ?? $_SERVER['HTTP_X_CSRF'] ?? ''); return $t !== '' && hash_equals(csrf_token(), $t); }

/** Код первичной настройки — лежит в data/setup-code.txt, пока не создан первый пользователь */
function setup_code() {
  $f = cfg('data_dir') . '/setup-code.txt';
  if (!is_file($f)) @file_put_contents($f, strtoupper(bin2hex(random_bytes(4))));
  return trim((string)@file_get_contents($f));
}
function users_exist() { return (int)q_val('SELECT COUNT(*) FROM users') > 0; }
function password_problem($p) {
  if (mb_strlen($p) < 10) return 'Пароль — не короче 10 символов.';
  if (preg_match('~^(.)\1+$~u', $p) || preg_match('~^(1234567890|qwerty|password|пароль)~iu', $p)) return 'Слишком простой пароль.';
  return null;
}

function private_headers() {
  header('X-Frame-Options: SAMEORIGIN');
  header('X-Content-Type-Options: nosniff');
  header('Referrer-Policy: same-origin');
  header('Cache-Control: no-store, max-age=0');
  header('X-Robots-Tag: noindex, nofollow');
  header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline'; img-src 'self' data: blob:; font-src 'self'; connect-src 'self'; frame-src 'self'; frame-ancestors 'self'; form-action 'self'; base-uri 'none'");
}
