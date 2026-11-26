<?php
meta('title', 'Корзина | AVTO-M');
meta('robots', 'noindex, follow');
meta('canonical', '/cart');
meta('body_class', 'page-cart');
?>
<div class="wrap">
  <?= breadcrumbs([['Корзина', null]]) ?>
  <div class="page-head"><h1>Корзина</h1><p class="lead">Отправьте детали одним запросом — менеджер проверит наличие и совместимость и перезвонит. Это не оплата и ни к чему не обязывает. Купить сразу можно на Wildberries и Ozon по кнопкам у товаров.</p></div>
  <div class="cart-layout" data-cart-page>
    <section class="cart-list" aria-label="Детали в корзине">
      <div class="cart-items" data-cart-items><div class="skeleton"></div><div class="skeleton"></div></div>
      <div class="empty panel" data-cart-empty hidden>
        <div class="empty-ic" aria-hidden="true"><?= icon('clipboard') ?></div>
        <h2>Корзина пока пуста</h2>
        <p>Добавляйте детали кнопкой «В корзину» в каталоге — они сохранятся, даже если закрыть страницу.</p>
        <div class="empty-btns"><a class="btn btn-primary" href="/catalog"><?= icon('catalog') ?>В каталог</a><a class="btn btn-soft" href="/podbor"><?= icon('car') ?>Подбор по авто</a></div>
      </div>
    </section>
    <aside class="panel cart-form" data-cart-form hidden>
      <h2>Отправить заявку</h2>
      <p class="muted" data-cart-sum></p>
      <?php partial('lead_form', ['kind' => 'cart', 'id' => 'cf', 'withCar' => true, 'btn' => 'Отправить запрос']); ?>
    </aside>
  </div>
</div>
