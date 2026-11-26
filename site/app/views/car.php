<?php
/** Посадочная страница модели: /avto/ford/focus-ii */
$car = ['make' => $make['slug'], 'model' => $model['slug'], 'year' => null, 'body' => '', 'engine' => '', 'make_name' => $make['name'], 'model_name' => $model['name']];
$name = $make['name'] . ' ' . $model['name'];
$items = [];
foreach (products_all() as $p) if (compat_match($p, $car) !== 'no') $items[] = $p;
$byCat = [];
foreach ($items as $p) $byCat[$p['category_id']][] = $p;
$cats = categories_all();
$years = years_label($model['yf'], $model['yt']);
meta('title', 'Запчасти для ' . $name . ($years ? ' (' . $years . ')' : '') . ' — купить | AVTO-M');
meta('desc', 'Газовые упоры и расширительные бачки для ' . $name . ($years ? ' ' . $years . ' годов' : '') . '. Совместимость по году, кузову и двигателю. Wildberries, Ozon или заявка.');
meta('canonical', '/avto/' . $make['slug'] . '/' . $model['slug']);
$q = http_build_query(['make' => $make['slug'], 'model' => $model['slug']]);
?>
<div class="wrap">
  <?= breadcrumbs([['Марки', '/avto'], [$make['name'], '/avto/' . $make['slug']], [$model['name'], null]]) ?>
  <div class="page-head">
    <h1>Запчасти для <?= h($name) ?></h1>
    <p class="lead"><?= $years ? 'Годы выпуска в каталоге: ' . h($years) . '. ' : '' ?><?= $model['bodies'] ? 'Кузова: ' . h(mb_strtolower(implode(', ', $model['bodies']))) . '. ' : '' ?>Уточните год и кузов, чтобы отсеять лишнее.</p>
  </div>
  <div class="panel picker-panel">
    <?php partial('picker', ['id' => 'cp', 'compact' => true, 'car' => $car]); ?>
  </div>
  <?php foreach ($byCat as $cid => $list): $c = $cats[$cid] ?? null; ?>
    <section class="section">
      <div class="section-head"><h2><?= h($c['name'] ?? 'Товары') ?></h2><?php if ($c): ?><a class="link-arrow" href="/catalog/<?= h($c['slug']) ?>?<?= h($q) ?>">Открыть с фильтрами<?= icon('arrow') ?></a><?php endif; ?></div>
      <div class="grid"><?php foreach ($list as $p) partial('card', ['p' => $p]); ?></div>
    </section>
  <?php endforeach; ?>
</div>
