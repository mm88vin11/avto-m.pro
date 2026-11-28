<?php
if (!defined('AVTOM')) { http_response_code(404); exit; }

function a_url($path = '') { return '/admin' . $path; }
function a_icon($n) { return '<svg class="i" aria-hidden="true"><use href="#i-' . h($n) . '"/></svg>'; }

function a_nav() {
  $new = (int)q_val("SELECT COUNT(*) FROM leads WHERE status = 'new'");
  $drafts = (int)q_val("SELECT COUNT(*) FROM drafts");
  return [
    ['', 'home', 'Сводка', null],
    ['/products', 'kit', 'Товары', $drafts ? ['draft', $drafts] : null],
    ['/categories', 'catalog', 'Категории', null],
    ['/cars', 'car', 'Автомобили', null],
    ['/attributes', 'filter', 'Фильтры', null],
    ['/brands', 'shield', 'Марки деталей', null],
    ['/pages', 'clipboard', 'Страницы', null],
    ['/images', 'photo', 'Картинки сайта', null],
    ['/leads', 'chat', 'Заявки', $new ? ['new', $new] : null],
    ['/settings', 'wrench', 'Настройки', null],
  ];
}

/** Шапка админки */
function a_head($title, $active = '') {
  private_headers();
  header('Content-Type: text/html; charset=utf-8');
  $u = admin_user();
  ?><!doctype html>
<html lang="ru" data-base="<?= h(BASE) ?>"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title><?= h($title) ?> · Админка AVTO-M</title>
<meta name="robots" content="noindex, nofollow"><link rel="icon" href="/favicon.ico"><link rel="stylesheet" href="<?= asset('css/admin.css') ?>"><script src="<?= asset('js/admin.js') ?>" defer></script></head>
<body class="adm" data-csrf="<?= h(csrf_token()) ?>">
<?php require APP_DIR . '/views/_icons.php'; ?>
<?php if ($u): ?>
<header class="a-top">
  <button class="a-burger" type="button" data-a-menu aria-label="Меню"><?= a_icon('menu') ?></button>
  <a class="a-logo" href="/admin"><span class="logo-img" aria-hidden="true"></span><span>Управление</span></a>
  <div class="a-top-r">
    <a class="a-btn a-btn-ghost" href="/" target="_blank" rel="noopener"><?= a_icon('external') ?><span>Открыть сайт</span></a>
    <a class="a-user" href="/admin/account" title="Мой профиль"><span><?= h(mb_substr($u['name'], 0, 1)) ?></span><b><?= h($u['name']) ?></b></a>
  </div>
</header>
<div class="a-wrap">
<nav class="a-side" aria-label="Разделы">
  <?php foreach (a_nav() as [$p, $ic, $t, $b]): $on = $active === $p; ?>
    <a href="/admin<?= $p ?>"<?= $on ? ' aria-current="page"' : '' ?>><?= a_icon($ic) ?><span><?= h($t) ?></span><?php if ($b): ?><b class="a-badge a-badge-<?= $b[0] ?>"><?= (int)$b[1] ?></b><?php endif; ?></a>
  <?php endforeach; ?>
  <div class="a-side-sep"></div>
  <a href="/admin/history"<?= $active === '/history' ? ' aria-current="page"' : '' ?>><?= a_icon('clock') ?><span>История правок</span></a>
  <a href="/admin/backups"<?= $active === '/backups' ? ' aria-current="page"' : '' ?>><?= a_icon('lock') ?><span>Резервные копии</span></a>
  <?php if (is_owner()): ?><a href="/admin/users"<?= $active === '/users' ? ' aria-current="page"' : '' ?>><?= a_icon('building') ?><span>Сотрудники</span></a><?php endif; ?>
  <a href="/admin/help"<?= $active === '/help' ? ' aria-current="page"' : '' ?>><?= a_icon('info') ?><span>Инструкция</span></a>
  <form method="post" action="/admin/logout"><?= csrf_field() ?><button type="submit"><?= a_icon('arrow-left') ?><span>Выйти</span></button></form>
</nav>
<main class="a-main" id="main">
<?php foreach (flash() as [$k, $m]): ?><div class="a-flash a-flash-<?= h($k) ?>" role="status"><?= a_icon($k === 'ok' ? 'check-circle' : 'alert') ?><span><?= $m ?></span></div><?php endforeach; ?>
<?php else: ?>
<main class="a-auth">
<?php endif;
}

function a_foot() {
  if (admin_user()) echo '</main></div>';
  else echo '</main>';
  echo '<div class="a-toast" id="a-toast" hidden role="status"></div></body></html>';
}

function flash($msg = null, $kind = 'ok') {
  session_start_admin();
  if ($msg !== null) { $_SESSION['flash'][] = [$kind, $msg]; return []; }
  $f = $_SESSION['flash'] ?? [];
  unset($_SESSION['flash']);
  return $f;
}

/** Поле формы */
function a_field($name, $label, $value, $opts = []) {
  $type = $opts['type'] ?? 'text';
  $id = 'f_' . preg_replace('~[^a-z0-9_]~i', '_', $name);
  $err = $opts['error'] ?? '';
  $hint = $opts['hint'] ?? '';
  $req = !empty($opts['required']) ? ' required' : '';
  $ph = isset($opts['placeholder']) ? ' placeholder="' . h($opts['placeholder']) . '"' : '';
  $extra = $opts['attrs'] ?? '';
  $out = '<div class="a-field' . ($err ? ' is-err' : '') . (!empty($opts['wide']) ? ' a-wide' : '') . '"><label for="' . $id . '">' . h($label) . (!empty($opts['optional']) ? ' <span class="a-opt">необязательно</span>' : '') . '</label>';
  if ($type === 'textarea') $out .= '<textarea id="' . $id . '" name="' . h($name) . '" rows="' . ($opts['rows'] ?? 4) . '"' . $req . $ph . $extra . '>' . h($value) . '</textarea>';
  elseif ($type === 'select') {
    $out .= '<select id="' . $id . '" name="' . h($name) . '"' . $req . $extra . '>';
    foreach ($opts['options'] as $k => $v) $out .= '<option value="' . h($k) . '"' . ((string)$k === (string)$value ? ' selected' : '') . '>' . h($v) . '</option>';
    $out .= '</select>';
  } else $out .= '<input id="' . $id . '" type="' . h($type) . '" name="' . h($name) . '" value="' . h($value) . '"' . $req . $ph . $extra . '>';
  if ($hint) $out .= '<p class="a-hint">' . $hint . '</p>';
  if ($err) $out .= '<p class="a-err">' . h($err) . '</p>';
  return $out . '</div>';
}
function a_check($name, $label, $checked, $hint = '') {
  return '<label class="a-switch"><input type="checkbox" name="' . h($name) . '" value="1"' . ($checked ? ' checked' : '') . '><span class="a-sw" aria-hidden="true"></span><span><b>' . h($label) . '</b>' . ($hint ? '<small>' . h($hint) . '</small>' : '') . '</span></label>';
}

/** Выбор фото: превью + кнопка загрузки, значение — id фото */
function a_photo_pick($name, $photoId, $label = 'Фото') {
  $ph = $photoId ? photo_row($photoId) : null;
  return '<div class="a-field"><span class="a-lbl">' . h($label) . '</span><div class="a-photo-pick" data-photo-pick><input type="hidden" name="' . h($name) . '" value="' . (int)$photoId . '">'
    . '<div class="a-photo-prev">' . ($ph ? '<img src="' . h(img_url($ph, 400)) . '" alt="">' : a_icon('photo')) . '</div>'
    . '<div class="a-photo-btns"><label class="a-btn a-btn-soft"><input type="file" accept="image/jpeg,image/png,image/webp" hidden data-photo-file>' . a_icon('plus') . 'Загрузить</label>'
    . '<button type="button" class="a-btn a-btn-ghost" data-photo-clear' . ($ph ? '' : ' hidden') . '>Убрать</button></div></div></div>';
}

function a_status_badge($p, $hasDraft) {
  if (!$p['published']) return '<span class="a-pill a-pill-gray">' . ($hasDraft ? 'Черновик' : 'Скрыт') . '</span>';
  return $hasDraft ? '<span class="a-pill a-pill-warn">Есть изменения</span>' : '<span class="a-pill a-pill-ok">На сайте</span>';
}

function a_require_post() {
  if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') return false;
  if (!csrf_ok()) { http_response_code(400); a_head('Ошибка'); echo '<div class="a-card"><h1>Страница устарела</h1><p>Обновите страницу и повторите действие.</p></div>'; a_foot(); exit; }
  return true;
}
