<?php
$make = $make ?? null;
$tree = $make ? [$make] : car_tree();
if ($make) {
  meta('title', 'Запчасти для ' . $make['name'] . ' — газовые упоры и расширительные бачки | AVTO-M');
  meta('desc', 'Газовые упоры и расширительные бачки для ' . $make['name'] . ': ' . implode(', ', array_column(array_slice($make['models'], 0, 6), 'name')) . '. Подбор по году и кузову.');
  meta('canonical', '/avto/' . $make['slug']);
} else {
  meta('title', 'Марки автомобилей — подбор запчастей | AVTO-M');
  meta('desc', 'Марки и модели автомобилей, для которых в каталоге AVTO-M есть газовые упоры и расширительные бачки.');
  meta('canonical', '/avto');
}
?>
<div class="wrap">
  <?= breadcrumbs($make ? [['Марки', '/avto'], [$make['name'], null]] : [['Марки автомобилей', null]]) ?>
  <div class="page-head"><h1 data-split><?= $make ? 'Запчасти для ' . h($make['name']) : 'Марки автомобилей' ?></h1><p class="lead">Выберите модель — покажем детали, которые к ней подходят.</p></div>
  <div class="makes">
    <?php foreach ($tree as $mk): ?>
      <div class="make panel" data-reveal>
        <h2><a href="/avto/<?= h($mk['slug']) ?>"><?= h($mk['name']) ?></a></h2>
        <ul><?php foreach ($mk['models'] as $m): ?><li><a href="/avto/<?= h($mk['slug']) ?>/<?= h($m['slug']) ?>"><?= h($m['name']) ?><small><?= h(years_label($m['yf'], $m['yt'])) ?> · <?= (int)$m['n'] ?> <?= plural($m['n'], 'деталь', 'детали', 'деталей') ?></small></a></li><?php endforeach; ?></ul>
      </div>
    <?php endforeach; ?>
  </div>
</div>
