<?php
// Локальный просмотр: php -S localhost:8080 -t site tools/dev-router.php
$f = __DIR__ . '/../site' . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
if ($_SERVER['REQUEST_URI'] !== '/' && is_file($f) && !preg_match('~^/app/~', $_SERVER['REQUEST_URI'])) return false;
require __DIR__ . '/../site/index.php';
