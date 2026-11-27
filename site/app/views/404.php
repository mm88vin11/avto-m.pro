<?php meta('title', 'Страница не найдена | AVTO-M'); ?>
<div class="wrap">
  <div class="nf panel">
    <?php $nf = empty_art('404', 'gauge'); if (str_starts_with($nf, '<img')): ?><?= $nf ?><?php else: ?><div class="nf-code" aria-hidden="true"><span>4</span><span class="nf-wheel"><?= icon('gauge') ?></span><span>4</span></div><?php endif; ?>
    <h1>Такой страницы нет</h1>
    <p>Возможно, деталь сняли с продажи или ссылка устарела. Найдите нужное через поиск или подбор по автомобилю.</p>
    <form class="search-big" action="/search" method="get" role="search">
      <label class="sr" for="q404">Поиск</label>
      <input id="q404" name="q" type="search" placeholder="Название, артикул или номер">
      <button class="btn btn-primary btn-lg" type="submit"><?= icon('search') ?>Найти</button>
    </form>
    <div class="empty-btns"><a class="btn btn-soft" href="/"><?= icon('home') ?>На главную</a><a class="btn btn-soft" href="/podbor"><?= icon('car') ?>Подбор по авто</a></div>
  </div>
</div>
