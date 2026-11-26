<?php
/** @var string $content */
$s = site();
$M = $GLOBALS['META'];
$title = $M['title'] ?? ($s['name'] . ' — ' . $s['tagline']);
$desc = $M['desc'] ?? content('home')['seo_desc'];
$canon = $M['canonical'] ?? null;
$og = $M['og_image'] ?? site_url('/assets/img/og-default.jpg');
$robots = preview_mode() ? 'noindex, nofollow' : ($M['robots'] ?? 'index, follow, max-image-preview:large');
$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$cats = categories_visible();
$navActive = fn($p) => ($path === $p || ($p !== '/' && strpos($path, $p . '/') === 0)) ? ' aria-current="page"' : '';
?><!doctype html>
<html lang="ru">
<head>
<meta charset="utf-8">
<script><?= JS_BOOT ?></script>
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cut">
<title><?= h($title) ?></title>
<meta name="description" content="<?= h($desc) ?>">
<meta name="robots" content="<?= h($robots) ?>">
<?php if ($canon): ?><link rel="canonical" href="<?= h(site_url($canon)) ?>">
<?php endif; ?>
<meta name="theme-color" content="#0a1f4d">
<meta name="format-detection" content="telephone=no">
<link rel="icon" href="/favicon.ico" sizes="48x48">
<link rel="icon" type="image/png" sizes="32x32" href="/assets/icons/favicon-32.png">
<link rel="apple-touch-icon" href="/assets/icons/apple-touch-icon.png">
<link rel="manifest" href="/site.webmanifest">
<meta property="og:type" content="<?= h($M['og_type'] ?? 'website') ?>">
<meta property="og:site_name" content="AVTO-M">
<meta property="og:locale" content="ru_RU">
<meta property="og:title" content="<?= h($M['og_title'] ?? $title) ?>">
<meta property="og:description" content="<?= h($desc) ?>">
<?php if ($canon): ?><meta property="og:url" content="<?= h(site_url($canon)) ?>">
<?php endif; ?>
<meta property="og:image" content="<?= h($og) ?>">
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="630">
<meta name="twitter:card" content="summary_large_image">
<?php if ($s['yandex_verification']): ?><meta name="yandex-verification" content="<?= h($s['yandex_verification']) ?>">
<?php endif; ?>
<link rel="preload" href="/assets/fonts/onest-cyrillic-wght-normal.woff2" as="font" type="font/woff2" crossorigin>
<link rel="stylesheet" href="<?= asset('css/site.css') ?>">
<script src="<?= asset('js/site.js') ?>" defer></script>
<script src="<?= asset('js/interactive.js') ?>" defer></script>
<?php foreach (array_merge([org_jsonld(), ['@context' => 'https://schema.org', '@type' => 'WebSite', 'url' => site_url('/'), 'name' => 'AVTO-M', 'inLanguage' => 'ru', 'potentialAction' => ['@type' => 'SearchAction', 'target' => site_url('/search?q={q}'), 'query-input' => 'required name=q']]], $M['jsonld'] ?? []) as $ld): ?>
<script type="application/ld+json"><?= json_encode($ld, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) ?></script>
<?php endforeach; ?>
</head>
<body class="<?= h($M['body_class'] ?? '') ?>" data-metrika="<?= h($s['metrika_id']) ?>">
<?php require __DIR__ . '/_icons.php'; ?>
<a class="skip" href="#main">Перейти к содержимому</a>
<?php if (preview_mode()): ?>
<div class="preview-bar" role="status"><?= icon('info') ?> Предпросмотр: показан черновик. Посетители видят опубликованную версию. <a href="/admin">Вернуться в админку</a></div>
<?php endif; ?>

<div class="topbar">
  <div class="wrap topbar-in">
    <span class="topbar-item"><?= icon('pin') ?><?= h($s['address_short']) ?></span>
    <span class="topbar-item"><?= icon('clock') ?><?= h($s['hours']) ?></span>
    <nav class="topbar-nav" aria-label="Покупателям">
      <a href="/how-to-buy"<?= $navActive('/how-to-buy') ?>>Как купить</a>
      <a href="/partners"<?= $navActive('/partners') ?>>Сотрудничество</a>
      <a href="mailto:<?= h($s['email']) ?>"><?= h($s['email']) ?></a>
    </nav>
  </div>
</div>

<header class="header" id="header">
  <div class="wrap header-in">
    <a class="logo" href="/" aria-label="AVTO-M — на главную"><span class="logo-img" role="img" aria-label="AVTO-M. Качество по доступной цене"></span></a>
    <button class="btn btn-primary catalog-btn" type="button" aria-expanded="false" aria-controls="mega" data-mega>
      <span class="burger" aria-hidden="true"><i></i><i></i><i></i></span><span>Каталог</span>
    </button>
    <form class="search" action="/search" method="get" role="search" data-search>
      <label class="sr" for="q">Поиск по названию, артикулу или номеру</label>
      <?= icon('search', 'search-i') ?>
      <input id="q" name="q" type="search" placeholder="Название, артикул или номер детали" autocomplete="off" value="<?= h($_GET['q'] ?? '') ?>" aria-autocomplete="list" aria-controls="suggest" enterkeyhint="search">
      <button class="search-go" type="submit" aria-label="Найти"><?= icon('arrow') ?></button>
      <div class="suggest" id="suggest" role="listbox" hidden></div>
    </form>
    <a class="header-phone" href="<?= h(phone_href($s['phone'])) ?>" data-goal="phone_click"><b><?= h($s['phone']) ?></b><small><?= h($s['hours_short']) ?></small></a>
    <div class="header-actions">
      <a class="hbtn hbtn-call" href="<?= h(phone_href($s['phone'])) ?>" data-goal="phone_click" aria-label="Позвонить"><?= icon('phone') ?></a>
      <a class="hbtn" href="/compare" aria-label="Сравнение"><?= icon('compare') ?><span class="hbtn-t">Сравнить</span><span class="badge" data-count="compare" hidden>0</span></a>
      <a class="hbtn" href="/cart" aria-label="Список запроса"><?= icon('clipboard') ?><span class="hbtn-t">Запрос</span><span class="badge" data-count="cart" hidden>0</span></a>
    </div>
  </div>
  <div class="wrap header-nav">
    <nav aria-label="Основное меню">
      <a href="/podbor"<?= $navActive('/podbor') ?>><?= icon('car') ?>Подбор по авто</a>
      <?php foreach ($cats as $c): ?><a href="/catalog/<?= h($c['slug']) ?>"<?= $navActive('/catalog/' . $c['slug']) ?>><?= h($c['name']) ?></a><?php endforeach; ?>
      <a href="/about"<?= $navActive('/about') ?>>О компании</a>
      <a href="/partners"<?= $navActive('/partners') ?>>Преимущества</a>
      <a href="/contacts"<?= $navActive('/contacts') ?>>Контакты</a>
    </nav>
    <div class="garage" data-garage hidden></div>
  </div>
  <div class="mega" id="mega" hidden>
    <div class="wrap mega-in">
      <div class="mega-cats">
        <p class="mega-h">Каталог</p>
        <?php foreach ($cats as $c): $ph = photo_row($c['photo_id']); ?>
          <a class="mega-cat" href="/catalog/<?= h($c['slug']) ?>"><span><?= h($c['name']) ?></span><small><?= (int)$c['cnt'] ?> <?= plural($c['cnt'], 'товар', 'товара', 'товаров') ?></small><?= icon('chevron-r') ?></a>
        <?php endforeach; ?>
        <a class="mega-cat mega-all" href="/catalog"><span>Весь каталог</span><?= icon('arrow') ?></a>
      </div>
      <div class="mega-side">
        <p class="mega-h">Подбор по автомобилю</p>
        <div class="mega-makes">
          <?php foreach (car_tree() as $mk): ?><a href="/avto/<?= h($mk['slug']) ?>"><?= h($mk['name']) ?></a><?php endforeach; ?>
        </div>
        <a class="btn btn-soft" href="/podbor"><?= icon('car') ?>Подобрать по марке и модели</a>
        <p class="mega-h">Марки деталей</p>
        <div class="mega-makes">
          <?php foreach (brands_all() as $b): if (!$b['cnt']) continue; ?><a href="/brand/<?= h($b['slug']) ?>"><?= h($b['name']) ?></a><?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>
</header>

<main id="main" tabindex="-1">
<?= $content ?>
</main>

<footer class="footer">
  <div class="wrap">
    <div class="footer-top">
      <div class="footer-brand">
        <img src="/assets/img/logo-white.webp" width="200" height="68" alt="AVTO-M — Качество по доступной цене" decoding="async">
        <p>Газовые упоры и расширительные бачки с подбором по автомобилю. Покупка на Wildberries и Ozon или по заявке.</p>
      </div>
      <nav class="footer-col" aria-label="Каталог">
        <p class="footer-h">Каталог</p>
        <?php foreach ($cats as $c): ?><a href="/catalog/<?= h($c['slug']) ?>"><?= h($c['name']) ?></a><?php endforeach; ?>
        <a href="/podbor">Подбор по автомобилю</a>
        <a href="/avto">Марки автомобилей</a>
      </nav>
      <nav class="footer-col" aria-label="Покупателям">
        <p class="footer-h">Покупателям</p>
        <a href="/how-to-buy">Как купить</a>
        <a href="/cart">Список запроса</a>
        <a href="/compare">Сравнение</a>
        <a href="/partners">Сотрудничество</a>
        <a href="/about">О компании</a>
      </nav>
      <div class="footer-col footer-contacts">
        <p class="footer-h">Контакты</p>
        <a class="footer-phone" href="<?= h(phone_href($s['phone'])) ?>" data-goal="phone_click"><?= h($s['phone']) ?></a>
        <a href="mailto:<?= h($s['email']) ?>"><?= h($s['email']) ?></a>
        <span><?= h($s['address']) ?></span>
        <span><?= h($s['hours']) ?></span>
        <button class="btn btn-ghost-light" type="button" data-lead="question" data-title="Задать вопрос"><?= icon('chat') ?>Задать вопрос</button>
      </div>
    </div>
    <div class="footer-bottom">
      <p>© <?= date('Y') ?> AVTO-M · <?= h($s['org_name']) ?> · ИНН <?= h($s['inn']) ?> · ОГРНИП <?= h($s['ogrnip']) ?></p>
      <p>Информация на сайте не является публичной офертой. Wildberries и Ozon — товарные знаки их правообладателей.</p>
      <nav aria-label="Документы">
        <a href="/legal/privacy">Политика обработки персональных данных</a>
        <a href="/legal/consent">Согласие на обработку данных</a>
        <a href="/legal/cookies">Cookie</a>
        <a href="/legal/terms">Правовая информация</a>
        <button type="button" class="linklike" data-cookie-settings>Настройки cookie</button>
      </nav>
    </div>
  </div>
</footer>

<nav class="tabbar" aria-label="Быстрое меню">
  <a href="/"<?= $navActive('/') ?>><?= icon('home') ?><span>Главная</span></a>
  <a href="/catalog"<?= $navActive('/catalog') ?>><?= icon('catalog') ?><span>Каталог</span></a>
  <a href="/podbor"<?= $navActive('/podbor') ?>><?= icon('car') ?><span>Подбор</span></a>
  <a href="/cart"<?= $navActive('/cart') ?>><?= icon('clipboard') ?><span>Запрос</span><b class="badge" data-count="cart" hidden>0</b></a>
  <button type="button" data-drawer aria-controls="drawer" aria-expanded="false"><?= icon('menu') ?><span>Ещё</span></button>
</nav>

<div class="drawer" id="drawer" hidden>
  <div class="drawer-panel" role="dialog" aria-modal="true" aria-label="Меню">
    <div class="drawer-head"><span class="logo-img" aria-hidden="true"></span><button class="icon-btn" type="button" data-drawer-close aria-label="Закрыть меню"><?= icon('close') ?></button></div>
    <nav class="drawer-nav">
      <a href="/catalog"><?= icon('catalog') ?>Каталог</a>
      <?php foreach ($cats as $c): ?><a class="sub" href="/catalog/<?= h($c['slug']) ?>"><?= h($c['name']) ?></a><?php endforeach; ?>
      <a href="/podbor"><?= icon('car') ?>Подбор по автомобилю</a>
      <a href="/compare"><?= icon('compare') ?>Сравнение <b class="badge" data-count="compare" hidden>0</b></a>
      <a href="/how-to-buy"><?= icon('bag') ?>Как купить</a>
      <a href="/about"><?= icon('building') ?>О компании</a>
      <a href="/partners"><?= icon('wrench') ?>Сотрудничество</a>
      <a href="/contacts"><?= icon('pin') ?>Контакты</a>
    </nav>
    <div class="drawer-contacts">
      <a class="btn btn-primary btn-block" href="<?= h(phone_href($s['phone'])) ?>" data-goal="phone_click"><?= icon('phone') ?><?= h($s['phone']) ?></a>
      <p><?= h($s['hours']) ?><br><?= h($s['address_short']) ?></p>
    </div>
  </div>
</div>

<?php partial('lead_modal'); ?>

<div class="cookie" id="cookie" hidden role="dialog" aria-live="polite" aria-label="Файлы cookie">
  <p>Мы используем cookie, чтобы сайт работал. Аналитику «Яндекс Метрики» включим только с вашего согласия. <a href="/legal/cookies">Подробнее</a></p>
  <div class="cookie-btns">
    <button class="btn btn-primary" type="button" data-cookie="all">Разрешить</button>
    <button class="btn btn-soft" type="button" data-cookie="necessary">Только необходимые</button>
  </div>
</div>
<div class="toast" id="toast" role="status" aria-live="polite" hidden></div>
<script type="application/json" id="car-tree"><?= car_tree_json() ?></script>
</body>
</html>
