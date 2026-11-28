<?php
/** @var string $content */
$s = site();
$M = $GLOBALS['META'];
$title = $M['title'] ?? ($s['name'] . ' — ' . $s['tagline']);
$desc = $M['desc'] ?? content('home')['seo_desc'];
$canon = $M['canonical'] ?? null;
$og = $M['og_image'] ?? site_url('/assets/img/og-default.jpg');
$robots = (preview_mode() || subfolder_noindex()) ? 'noindex, nofollow' : ($M['robots'] ?? 'index, follow, max-image-preview:large');
$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$cats = categories_visible();
$navActive = fn($p) => ($path === $p || ($p !== '/' && strpos($path, $p . '/') === 0)) ? ' aria-current="page"' : '';
?><!doctype html>
<html lang="ru" data-base="<?= h(BASE) ?>">
<head>
<meta charset="utf-8">
<script><?= JS_BOOT ?></script>
<meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, viewport-fit=cover">
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
<link rel="preload" href="/assets/fonts/montserrat-cyrillic-800-italic.woff2" as="font" type="font/woff2" crossorigin>
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

<header class="hd" id="top">
  <div class="wrap hd-top">
    <a class="logo" href="/" aria-label="AVTO-M — на главную"><img class="logo-img" src="/assets/img/logo.webp" srcset="/assets/img/logo.webp 1x, /assets/img/logo@2x.webp 2x" width="220" height="68" alt="AVTO-M. Качество по доступной цене" fetchpriority="high"></a>
    <p class="hd-slogan"><?= h($s['header_note']) ?></p>
    <span class="hd-info"><?= icon('pin') ?><span><?= h($s['address_short']) ?><small><?= h($s['region']) ?></small></span></span>
    <span class="hd-info"><?= icon('clock') ?><span><?= h($s['hours']) ?><small>Перед визитом позвоните</small></span></span>
    <div class="hd-contacts">
      <a class="hd-phone" href="<?= h(phone_href($s['phone'])) ?>" data-goal="phone_click"><?= icon('phone') ?><b><?= h($s['phone']) ?></b></a>
      <a class="hd-mail" href="mailto:<?= h($s['email']) ?>"><?= icon('mail') ?><?= h($s['email']) ?></a>
    </div>
  </div>
  <nav class="hd-nav" aria-label="Основное меню">
    <div class="wrap hd-nav-in">
      <a href="/"<?= $navActive('/') ?>>Главная</a>
      <a href="/catalog"<?= $navActive('/catalog') ?>>Продукция</a>
      <a href="/podbor"<?= $navActive('/podbor') ?>>Подбор по авто</a>
      <a href="/about"<?= $navActive('/about') ?>>О компании</a>
      <a href="/partners"<?= $navActive('/partners') ?>>Автосервисам</a>
      <a href="/how-to-buy"<?= $navActive('/how-to-buy') ?>>Как купить</a>
      <a href="/contacts"<?= $navActive('/contacts') ?>>Контакты</a>
    </div>
  </nav>
</header>
<div class="hbar" id="header">
  <div class="wrap hbar-in">
    <a class="hb-logo" href="/" aria-label="AVTO-M — на главную"><img src="/assets/img/logo.webp" srcset="/assets/img/logo.webp 1x, /assets/img/logo@2x.webp 2x" width="220" height="68" alt="AVTO-M"></a>
    <button class="hd-cat" type="button" aria-expanded="false" aria-controls="mega" data-mega><span class="burger" aria-hidden="true"><i></i><i></i><i></i></span><span>Каталог продукции</span></button>
    <form class="search" action="/search" method="get" role="search" data-search>
      <label class="sr" for="q">Поиск по названию, артикулу или номеру</label>
      <?= icon('search', 'search-i') ?>
      <input id="q" name="q" type="search" placeholder="Название или артикул детали" autocomplete="off" value="<?= h($_GET['q'] ?? '') ?>" aria-autocomplete="list" aria-controls="suggest" enterkeyhint="search">
      <button class="search-go" type="submit" aria-label="Найти"><?= icon('search') ?></button>
      <div class="suggest" id="suggest" role="listbox" hidden></div>
    </form>
    <a class="hd-ic hd-ic-cmp" href="/compare" aria-label="Сравнение"><?= icon('compare') ?><span>Сравнение</span><b class="badge" data-count="compare" hidden>0</b></a>
    <a class="hd-ic hd-ic-call hb-call" href="<?= h(phone_href($s['phone'])) ?>" data-goal="phone_click" aria-label="Позвонить: <?= h($s['phone']) ?>"><?= icon('phone') ?></a>
    <a class="hd-ic hd-ic-cart" href="/cart" aria-label="Корзина" data-cart-target><?= icon('bag') ?><span>Корзина</span><b class="badge" data-count="cart" hidden>0</b></a>
    <button class="menu-btn" type="button" aria-expanded="false" aria-controls="menu" aria-label="Открыть меню" data-menu><span class="burger" aria-hidden="true"><i></i><i></i><i></i></span></button>
    <span class="garage" data-garage hidden></span>
  </div>
  <div class="mega" id="mega" hidden>
    <div class="wrap mega-in">
      <div class="mega-cats">
        <p class="mega-h">Каталог продукции</p>
        <?php foreach ($cats as $c): $ph = photo_row($c['photo_id']); ?>
          <a class="mega-cat" href="/catalog/<?= h($c['slug']) ?>"><span class="mega-img"><?php if ($ph): ?><img src="<?= h(img_url($ph, 400)) ?>" alt="" loading="lazy"><?php endif; ?></span><span><?= h($c['name']) ?><small><?= (int)$c['cnt'] ?> <?= plural($c['cnt'], 'товар', 'товара', 'товаров') ?></small></span><?= icon('chevron-r') ?></a>
        <?php endforeach; ?>
        <a class="mega-cat mega-all" href="/catalog"><span>Весь каталог</span><?= icon('arrow') ?></a>
      </div>
      <div class="mega-side">
        <p class="mega-h">Подбор по автомобилю</p>
        <div class="mega-makes">
          <?php foreach (car_tree() as $mk): ?><a href="/avto/<?= h($mk['slug']) ?>"><?= h($mk['name']) ?></a><?php endforeach; ?>
        </div>
        <a class="btn btn-primary" href="/podbor"><?= icon('car') ?>Подобрать по марке и модели</a>
        <p class="mega-h">Производители</p>
        <div class="mega-makes">
          <?php foreach (brands_all() as $b): if (!$b['cnt']) continue; ?><a href="/brand/<?= h($b['slug']) ?>"><?= h($b['name']) ?></a><?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>
</div>

<main id="main" tabindex="-1">
<?= $content ?>
</main>

<footer class="ft">
  <div class="wrap ft-in">
    <div class="ft-brand">
      <img src="/assets/img/logo@2x.webp" width="220" height="68" alt="AVTO-M — Качество по доступной цене" decoding="async" loading="lazy">
      <p>Газовые упоры, расширительные бачки и другие автозапчасти с подбором по автомобилю. Покупка на Wildberries и Ozon или по заявке.</p>
    </div>
    <nav class="ft-col" aria-label="Разделы">
      <a href="/">Главная</a>
      <a href="/catalog">Продукция</a>
      <?php foreach ($cats as $c): ?><a href="/catalog/<?= h($c['slug']) ?>"><?= h($c['name']) ?></a><?php endforeach; ?>
      <a href="/podbor">Подбор по автомобилю</a>
    </nav>
    <nav class="ft-col" aria-label="Покупателям">
      <a href="/about">О компании</a>
      <a href="/partners">Преимущества</a>
      <a href="/how-to-buy">Доставка и покупка</a>
      <a href="/compare">Сравнение</a>
      <a href="/cart">Корзина</a>
      <a href="/karta-sajta">Карта сайта</a>
    </nav>
    <div class="ft-col ft-contacts">
      <a class="ft-phone" href="<?= h(phone_href($s['phone'])) ?>" data-goal="phone_click"><?= icon('phone') ?><?= h($s['phone']) ?></a>
      <a href="mailto:<?= h($s['email']) ?>"><?= icon('mail') ?><?= h($s['email']) ?></a>
      <span><?= icon('pin') ?><?= h($s['address']) ?></span>
      <span><?= icon('clock') ?><?= h($s['hours']) ?></span>
    </div>
  </div>
  <div class="ft-bottom">
    <div class="wrap ft-bottom-in">
      <p>© <?= date('Y') ?> AVTO-M · <?= h($s['org_name']) ?> · ИНН <?= h($s['inn']) ?> · ОГРНИП <?= h($s['ogrnip']) ?>. Информация на сайте не является публичной офертой. Wildberries и Ozon — товарные знаки их правообладателей.</p>
      <nav aria-label="Документы">
        <a href="/legal/privacy">Политика обработки данных</a>
        <a href="/legal/consent">Согласие на обработку</a>
        <a href="/legal/cookies">Cookie</a>
        <a href="/legal/terms">Правовая информация</a>
        <button type="button" class="linklike" data-cookie-settings>Настройки cookie</button>
      </nav>
    </div>
  </div>
</footer>

<div class="menu" id="menu" hidden>
  <div class="menu-bg" aria-hidden="true"></div>
  <div class="menu-in" role="dialog" aria-modal="true" aria-label="Меню сайта">
    <div class="wrap menu-grid">
      <nav class="menu-main" aria-label="Разделы">
        <a class="menu-a" href="/"<?= $navActive('/') ?>><span>Главная</span><?= icon('arrow') ?></a>
        <a class="menu-a" href="/catalog"<?= $navActive('/catalog') ?>><span>Каталог</span><?= icon('arrow') ?></a>
        <a class="menu-a" href="/podbor"<?= $navActive('/podbor') ?>><span>Подбор по авто</span><?= icon('arrow') ?></a>
        <a class="menu-a" href="/about"<?= $navActive('/about') ?>><span>О компании</span><?= icon('arrow') ?></a>
        <a class="menu-a" href="/partners"<?= $navActive('/partners') ?>><span>Автосервисам</span><?= icon('arrow') ?></a>
        <a class="menu-a" href="/how-to-buy"<?= $navActive('/how-to-buy') ?>><span>Как купить</span><?= icon('arrow') ?></a>
        <a class="menu-a" href="/contacts"<?= $navActive('/contacts') ?>><span>Контакты</span><?= icon('arrow') ?></a>
      </nav>
      <div class="menu-side">
        <p class="menu-h">Каталог</p>
        <div class="menu-cats">
          <?php foreach ($cats as $c): $ph = photo_row($c['photo_id']); ?>
            <a class="menu-cat" href="/catalog/<?= h($c['slug']) ?>"><span class="menu-cat-img"><?php if ($ph): ?><img src="<?= h(img_url($ph, 400)) ?>" alt="" loading="lazy" decoding="async"><?php endif; ?></span><span class="menu-cat-t"><?= h($c['name']) ?><small><?= (int)$c['cnt'] ?> <?= plural($c['cnt'], 'товар', 'товара', 'товаров') ?></small></span></a>
          <?php endforeach; ?>
        </div>
        <div class="menu-quick">
          <a href="/compare"><?= icon('compare') ?>Сравнение<b class="badge" data-count="compare" hidden>0</b></a>
          <a href="/cart"><?= icon('bag') ?>Корзина<b class="badge" data-count="cart" hidden>0</b></a>
        </div>
        <div class="menu-contacts">
          <a class="btn btn-primary btn-lg btn-block" href="<?= h(phone_href($s['phone'])) ?>" data-goal="phone_click"><?= icon('phone') ?><?= h($s['phone']) ?></a>
          <a class="menu-mail" href="mailto:<?= h($s['email']) ?>"><?= icon('mail') ?><?= h($s['email']) ?></a>
          <p><?= icon('clock') ?><span><?= h($s['hours']) ?></span></p>
          <p><?= icon('pin') ?><span><?= h($s['address_short']) ?>, <?= h($s['region']) ?></span></p>
        </div>
      </div>
    </div>
  </div>
</div>

<?php partial('lead_modal'); ?>
<?php $pp = content('popup'); $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$force = !empty($_GET['xpop']);
if (($pp['on'] ?? '0') === '1' && trim($pp['title'] ?? '') !== '' && ($force || !preg_match('~^/(cart|legal|lead|karta-sajta)~', $path))): $pimg = !empty($pp['img_id']) ? photo_row($pp['img_id']) : null; ?>
<dialog class="modal xpop" aria-labelledby="xpop-title" data-xpop data-days="<?= (int)$pp['days'] ?>" data-delay="<?= (int)$pp['delay'] ?>" data-mobile="<?= (int)$pp['mobile'] ?>"<?= $force ? ' data-force' : '' ?>>
  <div class="xpop-box">
    <button class="icon-btn modal-close" type="button" data-xpop-close aria-label="Закрыть"><?= icon('close') ?></button>
    <?php if ($pimg): ?><div class="xpop-art" aria-hidden="true"><img src="<?= h(img_url($pimg, 400)) ?>" srcset="<?= h(img_url($pimg, 400)) ?> 400w, <?= h(img_url($pimg, 800)) ?> 800w" sizes="220px" alt="" loading="lazy"></div><?php endif; ?>
    <div class="xpop-copy">
      <?php if (!empty($pp['badge'])): ?><span class="xpop-badge"><?= h($pp['badge']) ?></span><?php endif; ?>
      <h2 id="xpop-title"><?= h($pp['title']) ?></h2>
      <?php if (!empty($pp['text'])): ?><p class="xpop-text"><?= h($pp['text']) ?></p><?php endif; ?>
      <?php if (!empty($pp['code'])): ?><div class="xpop-code"><span>Промокод</span><b data-xpop-code><?= h($pp['code']) ?></b><button class="btn btn-sm btn-soft" type="button" data-xpop-copy>Скопировать</button></div><?php endif; ?>
      <div class="xpop-btns">
        <?php if (($pp['action'] ?? 'lead') === 'url'): ?><a class="btn btn-primary btn-lg" href="<?= h($pp['url'] ?: '/catalog') ?>" data-goal="xpop_click"><?= h($pp['btn'] ?: 'Подробнее') ?><?= icon('arrow') ?></a>
        <?php else: ?><button class="btn btn-primary btn-lg" type="button" data-lead="question" data-title="<?= h($pp['btn'] ?: 'Перезвоните мне') ?>" data-goal="xpop_click"> <?= h($pp['btn'] ?: 'Перезвоните мне') ?><?= icon('arrow') ?></button><?php endif; ?>
        <?php if (!empty($pp['btn2'])): ?><a class="btn btn-soft btn-lg" href="<?= h($pp['url2'] ?: '/catalog') ?>"><?= h($pp['btn2']) ?></a><?php endif; ?>
      </div>
      <?php if (!empty($pp['note'])): ?><p class="xpop-note"><?= h($pp['note']) ?></p><?php endif; ?>
    </div>
  </div>
</dialog>
<?php endif; ?>

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
