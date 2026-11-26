<?php
meta('title', 'Карта сайта | AVTO-M');
meta('desc', 'Все разделы сайта AVTO-M: каталог, подбор по автомобилю, марки, товары, информация для покупателей и документы.');
meta('canonical', '/karta-sajta');
$all = products_all();
$byCat = [];
foreach ($all as $p) $byCat[$p['category_id']][] = $p;
?>
<div class="wrap">
  <?= breadcrumbs([['Карта сайта', null]]) ?>
  <div class="page-head"><h1>Карта сайта</h1><p class="lead">От главной до заявки на деталь можно дойти через каталог, поиск по артикулу или подбор по автомобилю.</p></div>
  <div class="smap">
    <section class="panel smap-col">
      <h2>Покупателю</h2>
      <ul>
        <li><a href="/">Главная</a></li>
        <li><a href="/catalog">Каталог продукции</a></li>
        <li><a href="/podbor">Подбор по автомобилю и по параметрам</a></li>
        <li><a href="/search">Поиск по артикулу и номеру</a></li>
        <li><a href="/cart">Корзина</a></li>
        <li><a href="/compare">Сравнение</a></li>
        <li><a href="/how-to-buy">Доставка и покупка</a></li>
        <li><a href="/about">О компании</a></li>
        <li><a href="/partners">Преимущества и сотрудничество</a></li>
        <li><a href="/contacts">Контакты</a></li>
      </ul>
      <h2>Документы</h2>
      <ul>
        <li><a href="/legal/privacy">Политика обработки персональных данных</a></li>
        <li><a href="/legal/consent">Согласие на обработку персональных данных</a></li>
        <li><a href="/legal/cookies">Использование файлов cookie</a></li>
        <li><a href="/legal/terms">Правовая информация</a></li>
      </ul>
    </section>
    <section class="panel smap-col">
      <h2>Товары</h2>
      <?php foreach (categories_visible() as $c): ?>
        <h3><a href="/catalog/<?= h($c['slug']) ?>"><?= h($c['name']) ?></a></h3>
        <ul><?php foreach ($byCat[$c['id']] ?? [] as $p): ?><li><a href="<?= h(product_url($p)) ?>"><?= h($p['name']) ?></a> <small class="muted"><?= h($p['sku']) ?></small></li><?php endforeach; ?></ul>
      <?php endforeach; ?>
      <h2>Производители</h2>
      <ul><?php foreach (brands_all() as $b): if ($b['cnt']): ?><li><a href="/brand/<?= h($b['slug']) ?>"><?= h($b['name']) ?></a></li><?php endif; endforeach; ?></ul>
    </section>
    <section class="panel smap-col">
      <h2>Марки автомобилей</h2>
      <?php foreach (car_tree() as $mk): ?>
        <h3><a href="/avto/<?= h($mk['slug']) ?>"><?= h($mk['name']) ?></a></h3>
        <ul class="smap-inline"><?php foreach ($mk['models'] as $m): ?><li><a href="/avto/<?= h($mk['slug']) ?>/<?= h($m['slug']) ?>"><?= h($m['name']) ?></a></li><?php endforeach; ?></ul>
      <?php endforeach; ?>
    </section>
  </div>
</div>
