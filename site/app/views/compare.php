<?php
meta('title', 'Сравнение товаров | AVTO-M');
meta('robots', 'noindex, follow');
meta('canonical', '/compare');
?>
<div class="wrap">
  <?= breadcrumbs([['Сравнение', null]]) ?>
  <div class="page-head page-head-row">
    <div><h1>Сравнение</h1><p class="lead">Характеристики выбранных деталей рядом. Отличия подсвечены.</p></div>
    <label class="check" data-diff-toggle hidden><input type="checkbox" data-only-diff><span class="check-box" aria-hidden="true"><?= icon('check') ?></span><span>Только отличия</span></label>
  </div>
  <div data-compare-page>
    <div class="skeleton skeleton-tall"></div>
  </div>
  <div class="empty panel" data-compare-empty hidden>
    <?= empty_art('compare', 'compare') ?>
    <h2>Нечего сравнивать</h2>
    <p>Нажмите значок сравнения на карточках двух или больше деталей.</p>
    <div class="empty-btns"><a class="btn btn-primary" href="/catalog"><?= icon('catalog') ?>В каталог</a></div>
  </div>
</div>
