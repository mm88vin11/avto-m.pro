<?php
$qs = trim(mb_substr((string)($_GET['q'] ?? ''), 0, 80));
$all = products_all();
$items = $qs !== '' ? array_values(array_intersect_key($all, array_flip(search_ids($qs)))) : [];
if ($qs !== '') search_log($qs, count($items));
meta('title', ($qs !== '' ? 'Поиск: ' . $qs : 'Поиск') . ' | AVTO-M');
meta('robots', 'noindex, follow');
meta('canonical', '/search');
$n = count($items);
?>
<div class="wrap">
  <?= breadcrumbs([['Поиск', null]]) ?>
  <div class="page-head">
    <h1><?= $qs !== '' ? 'Поиск: «' . h($qs) . '»' : 'Поиск' ?></h1>
    <?php if ($qs !== ''): ?><p class="lead"><?= $n ? 'Нашли ' . $n . ' ' . plural($n, 'товар', 'товара', 'товаров') : 'Ничего не нашли' ?></p><?php endif; ?>
  </div>
  <form class="search-big panel" action="/search" method="get" role="search">
    <label class="sr" for="q2">Что ищем</label>
    <input id="q2" name="q" type="search" value="<?= h($qs) ?>" placeholder="Например: Focus 2 седан, LAP-10262 или 1336159" autofocus>
    <button class="btn btn-primary btn-lg" type="submit"><?= icon('search') ?>Найти</button>
  </form>
  <?php if ($items): ?>
    <div class="grid"><?php foreach ($items as $p) partial('card', ['p' => $p]); ?></div>
  <?php else: ?>
    <div class="empty panel">
      <?= empty_art('search', 'search') ?>
      <h2><?= $qs !== '' ? 'По запросу ничего нет' : 'Введите название, артикул или номер' ?></h2>
      <p>Ищите по модели автомобиля («Focus 2 универсал»), артикулу («LAP-10262») или оригинальному номеру детали. Пробелы и дефисы не важны.</p>
      <div class="empty-btns">
        <a class="btn btn-soft" href="/podbor"><?= icon('car') ?>Подбор по автомобилю</a>
        <button class="btn btn-primary" type="button" data-lead="pick" data-title="Помощь с подбором"><?= icon('chat') ?>Спросить менеджера</button>
      </div>
    </div>
  <?php endif; ?>
</div>
