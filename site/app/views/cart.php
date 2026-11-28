<?php
meta('title', 'Корзина | AVTO-M');
meta('robots', 'noindex, follow');
meta('canonical', '/cart');
meta('body_class', 'page-cart');
// что предложить, если корзина пуста: по одной-две детали из каждой категории, сначала те, что есть на маркетплейсах
$sugg = [];
$byCat = [];
foreach (products_all() as $p) $byCat[$p['category_id']][] = $p;
foreach ($byCat as $list) {
  usort($list, fn($a, $b) => [($b['wb_url'] || $b['ozon_url']) ? 1 : 0, $b['photos'] ? 1 : 0] <=> [($a['wb_url'] || $a['ozon_url']) ? 1 : 0, $a['photos'] ? 1 : 0]);
  $sugg = array_merge($sugg, array_slice($list, 0, 2));
}
$sugg = array_slice($sugg, 0, 4);
?>
<div class="wrap">
  <?= breadcrumbs([['Корзина', null]]) ?>
  <div class="page-head page-head-row"><h1>Корзина</h1><span class="cart-count" data-count-text="cart"></span></div>
  <div class="cart-layout" data-cart-page>
    <section class="cart-list" aria-label="Детали в корзине">
      <div class="cart-items" data-cart-items><div class="skeleton"></div><div class="skeleton"></div></div>
      <div class="cart-empty panel" data-cart-empty hidden>
        <?= empty_art('cart', 'clipboard') ?>
        <div>
          <h2>Корзина пуста</h2>
          <p>Загляните в каталог или подберите детали по машине.</p>
          <div class="empty-btns"><a class="btn btn-primary" href="/catalog"><?= icon('catalog') ?>В каталог</a><a class="btn btn-soft" href="/podbor"><?= icon('car') ?>Подбор по авто</a></div>
        </div>
      </div>
    </section>
    <aside class="panel cart-form" data-cart-form hidden>
      <h2>Отправить заявку</h2>
      <p class="muted" data-cart-sum></p>
      <?php partial('lead_form', ['kind' => 'cart', 'id' => 'cf', 'withCar' => true, 'btn' => 'Отправить запрос']); ?>
      <p class="cart-note">Это не оплата. Купить сразу можно на Wildberries и Ozon — кнопки у каждой детали.</p>
    </aside>
  </div>
  <?php if ($sugg): ?>
  <section class="band" aria-labelledby="sugg-title">
    <div class="sec-h"><h2 id="sugg-title">Часто берут</h2><a class="pill-link" href="/catalog">Весь каталог<?= icon('arrow') ?></a></div>
    <div class="grid grid-home"><?php foreach ($sugg as $p) partial('card', ['p' => $p]); ?></div>
  </section>
  <?php endif; ?>
</div>
