<?php
/** Панель управления AVTO-M. Все адреса /admin/... проходят здесь. */
if (!defined('AVTOM')) { http_response_code(404); exit; }
require __DIR__ . '/ui.php';

session_start_admin();
$a = array_slice($seg, 1);
$route = '/' . implode('/', $a);
$isPost = ($_SERVER['REQUEST_METHOD'] ?? '') === 'POST';

/* --- первый запуск: создание владельца --- */
if (!users_exist()) {
  $code = setup_code();
  $err = '';
  if ($isPost && csrf_ok()) {
    $p = (string)($_POST['password'] ?? '');
    if (!hash_equals($code, strtoupper(trim((string)($_POST['code'] ?? ''))))) $err = 'Неверный код настройки.';
    elseif (trim($_POST['login'] ?? '') === '' || trim($_POST['name'] ?? '') === '') $err = 'Заполните логин и имя.';
    elseif ($e = password_problem($p)) $err = $e;
    elseif ($p !== ($_POST['password2'] ?? '')) $err = 'Пароли не совпадают.';
    else {
      q_ins('users', ['login' => trim($_POST['login']), 'name' => trim($_POST['name']), 'pass_hash' => password_hash($p, PASSWORD_DEFAULT), 'role' => 'owner', 'active' => 1, 'created_at' => now()]);
      @unlink(cfg('data_dir') . '/setup-code.txt');
      admin_login(trim($_POST['login']), $p);
      flash('Готово! Вы вошли как владелец. Начните с раздела «Настройки» — проверьте контакты.');
      redirect('/admin');
    }
  }
  a_head('Первый запуск');
  ?>
  <form class="a-auth-box" method="post" action="/admin">
    <span class="logo-img" aria-hidden="true"></span>
    <h1>Первый запуск</h1>
    <p class="a-muted">Создайте учётную запись владельца. Код настройки лежит на хостинге в файле <code>app/data/setup-code.txt</code>.</p>
    <?php if ($err): ?><div class="a-flash a-flash-err"><?= a_icon('alert') ?><span><?= h($err) ?></span></div><?php endif; ?>
    <?= csrf_field() ?>
    <?= a_field('code', 'Код настройки', '', ['required' => true, 'attrs' => ' autocomplete="off"']) ?>
    <?= a_field('name', 'Ваше имя', $_POST['name'] ?? '', ['required' => true]) ?>
    <?= a_field('login', 'Логин', $_POST['login'] ?? '', ['required' => true, 'attrs' => ' autocomplete="username"']) ?>
    <?= a_field('password', 'Пароль — от 10 символов', '', ['type' => 'password', 'required' => true, 'attrs' => ' autocomplete="new-password"']) ?>
    <?= a_field('password2', 'Пароль ещё раз', '', ['type' => 'password', 'required' => true, 'attrs' => ' autocomplete="new-password"']) ?>
    <button class="a-btn a-btn-primary a-btn-block" type="submit">Создать и войти</button>
  </form>
  <?php
  a_foot();
  exit;
}

/* --- вход и выход --- */
if ($route === '/logout') { if ($isPost && csrf_ok()) admin_logout(); redirect('/admin/login'); }
if (!admin_user()) {
  $err = '';
  if ($isPost && $route === '/login' && csrf_ok()) {
    $err = admin_login((string)($_POST['login'] ?? ''), (string)($_POST['password'] ?? ''));
    if (!$err) { $to = (string)($_SESSION['after_login'] ?? '/admin'); unset($_SESSION['after_login']); redirect(strpos($to, '/admin') === 0 ? $to : '/admin'); }
  } elseif ($route !== '/login') { $_SESSION['after_login'] = $_SERVER['REQUEST_URI']; redirect('/admin/login'); }
  a_head('Вход');
  ?>
  <form class="a-auth-box" method="post" action="/admin/login">
    <span class="logo-img" aria-hidden="true"></span>
    <h1>Вход в панель управления</h1>
    <?php if ($err): ?><div class="a-flash a-flash-err"><?= a_icon('alert') ?><span><?= h($err) ?></span></div><?php endif; ?>
    <?= csrf_field() ?>
    <?= a_field('login', 'Логин', $_POST['login'] ?? '', ['required' => true, 'attrs' => ' autocomplete="username" autofocus']) ?>
    <?= a_field('password', 'Пароль', '', ['type' => 'password', 'required' => true, 'attrs' => ' autocomplete="current-password"']) ?>
    <button class="a-btn a-btn-primary a-btn-block" type="submit">Войти</button>
    <p class="a-muted a-center">Забыли пароль — попросите владельца сбросить его в разделе «Сотрудники».</p>
  </form>
  <?php
  a_foot();
  exit;
}

/* --- разделы --- */
backup_auto(); // ежедневная копия при первом заходе в админку за сутки
$r0 = $a[0] ?? '';
switch ($r0) {
  case '': case 'login': require __DIR__ . '/dashboard.php'; break;
  case 'products': require __DIR__ . '/products.php'; break;
  case 'upload': require __DIR__ . '/upload.php'; break;
  case 'categories': case 'brands': case 'cars': case 'attributes': require __DIR__ . '/refs.php'; break;
  case 'pages': require __DIR__ . '/pages.php'; break;
  case 'leads': require __DIR__ . '/leads.php'; break;
  case 'settings': case 'users': case 'account': case 'backups': case 'history': case 'help': require __DIR__ . '/system.php'; break;
  default: http_response_code(404); a_head('Не найдено'); echo '<div class="a-card"><h1>Раздел не найден</h1><p><a href="/admin">На главную админки</a></p></div>'; a_foot();
}
