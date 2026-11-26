<?php
meta('title', 'Подбор автозапчастей по марке, модели и году | AVTO-M');
meta('desc', 'Подберите газовые упоры и расширительные бачки по марке, модели, году выпуска, кузову и двигателю или по длине и усилию детали.');
meta('canonical', '/podbor');
$cats = categories_visible();
$attrsF = array_filter(attributes_all(), fn($a) => $a['filter'] && $a['type'] === 'number');
?>
<div class="wrap">
  <?= breadcrumbs([['Подбор', null]]) ?>
  <div class="page-head"><h1 data-split>Подбор деталей</h1><p class="lead">Выберите автомобиль или укажите параметры детали — покажем подходящие позиции.</p></div>

  <section class="panel pick-block" aria-labelledby="pk1">
    <div class="pick-block-h"><span class="num">1</span><div><h2 id="pk1">По автомобилю</h2><p class="muted">Модели зависят от марки, годы и кузова — от модели.</p></div></div>
    <?php partial('picker', ['id' => 'pp', 'compact' => true]); ?>
  </section>

  <section class="panel pick-block" aria-labelledby="pk2">
    <div class="pick-block-h"><span class="num">2</span><div><h2 id="pk2">По параметрам детали</h2><p class="muted">Если знаете длину и выталкивающую силу старого упора.</p></div></div>
    <form class="picker picker-row" action="/catalog" method="get" data-param-form>
      <div class="field sel"><label for="pr-cat">Тип детали</label>
        <select id="pr-cat" data-cat-select>
          <option value="/catalog">Любой</option>
          <?php foreach ($cats as $c): ?><option value="/catalog/<?= h($c['slug']) ?>"><?= h($c['name']) ?></option><?php endforeach; ?>
        </select></div>
      <?php foreach ($attrsF as $a): ?>
        <div class="field"><span class="lbl"><?= h($a['name']) ?>, <?= h($a['unit']) ?></span>
          <div class="range"><label><span class="sr">от</span><input type="number" inputmode="decimal" name="a[<?= h($a['code']) ?>][min]" placeholder="от"></label><span aria-hidden="true">—</span><label><span class="sr">до</span><input type="number" inputmode="decimal" name="a[<?= h($a['code']) ?>][max]" placeholder="до"></label></div></div>
      <?php endforeach; ?>
      <div class="field sel"><label for="pr-brand">Производитель</label>
        <select id="pr-brand" name="brand[]"><option value="">Любой</option><?php foreach (brands_all() as $b): if ($b['cnt']): ?><option value="<?= h($b['slug']) ?>"><?= h($b['name']) ?></option><?php endif; endforeach; ?></select></div>
      <button class="btn btn-primary btn-lg picker-go" type="submit" data-goal="pick_used"><?= icon('search') ?><span>Найти</span></button>
    </form>
  </section>

  <section class="section" aria-labelledby="pk3">
    <div class="section-head"><h2 id="pk3">Марки и модели в каталоге</h2></div>
    <div class="makes">
      <?php foreach (car_tree() as $mk): ?>
        <div class="make panel" data-reveal>
          <h3><a href="/avto/<?= h($mk['slug']) ?>"><?= h($mk['name']) ?></a></h3>
          <ul><?php foreach ($mk['models'] as $m): ?><li><a href="/avto/<?= h($mk['slug']) ?>/<?= h($m['slug']) ?>"><?= h($m['name']) ?><small><?= h(years_label($m['yf'], $m['yt'])) ?></small></a></li><?php endforeach; ?></ul>
        </div>
      <?php endforeach; ?>
    </div>
  </section>

  <section class="section">
    <div class="help panel">
      <div class="help-ic" aria-hidden="true"><?= icon('chat') ?></div>
      <div class="help-copy"><h2>Нет вашей машины?</h2><p>Напишите марку, модель, год или VIN-номер — поищем деталь и проверим совместимость.</p></div>
      <div class="help-btns"><button class="btn btn-primary btn-lg" type="button" data-lead="pick" data-title="Помощь с подбором"><?= icon('car') ?>Помогите подобрать</button></div>
    </div>
  </section>
</div>
