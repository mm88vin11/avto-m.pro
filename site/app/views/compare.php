<?php
meta('title', 'Сравнение товаров | AVTO-M');
meta('robots', 'noindex, follow');
meta('canonical', '/compare');
$rows = ['Производитель', 'Артикул', 'Автомобиль', 'Длина', 'Усилие', 'В комплекте'];
?>
<div class="wrap">
  <?= breadcrumbs([['Сравнение', null]]) ?>
  <div class="page-head page-head-row">
    <h1>Сравнение</h1>
    <label class="check" data-diff-toggle hidden><input type="checkbox" data-only-diff><span class="check-box" aria-hidden="true"><?= icon('check') ?></span><span>Только отличия</span></label>
  </div>
  <div data-compare-page>
    <div class="skeleton skeleton-tall"></div>
  </div>
  <div class="cmp-ghost" data-compare-empty hidden>
    <div class="cmp-ghost-table" aria-hidden="true">
      <div class="cmp-ghost-row cmp-ghost-head"><span></span><?php for ($i = 0; $i < 3; $i++): ?><span><i class="g-img"></i><i class="g-line"></i><i class="g-line g-short"></i></span><?php endfor; ?></div>
      <?php foreach ($rows as $r): ?><div class="cmp-ghost-row"><span><?= h($r) ?></span><?php for ($i = 0; $i < 3; $i++): ?><span><i class="g-line<?= $i === 1 ? ' g-short' : '' ?>"></i></span><?php endfor; ?></div><?php endforeach; ?>
    </div>
    <div class="cmp-ghost-card">
      <?= empty_art('compare', 'compare') ?>
      <h2>Сравните детали</h2>
      <p>Нажмите <span class="cmp-ghost-ic"><?= icon('compare') ?></span> на двух карточках — характеристики встанут рядом.</p>
      <a class="btn btn-primary" href="/catalog"><?= icon('catalog') ?>В каталог</a>
    </div>
  </div>
</div>
