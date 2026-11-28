<?php
$c = content('howto');
meta('title', $c['seo_title']); meta('desc', $c['seo_desc']); meta('canonical', '/how-to-buy');
$home = content('home');
// текст страницы разбит заголовками «## …» — каждый раздел показываем отдельной карточкой
$parts = preg_split('~^##\s+~m', trim($c['body']));
$intro = trim(array_shift($parts));
$cards = [];
foreach ($parts as $part) { [$t, $b] = array_pad(explode("\n", $part, 2), 2, ''); $cards[] = [trim($t), trim($b)]; }
$icons = ['bag', 'clipboard', 'car', 'building'];
?>
<div class="wrap">
  <?= breadcrumbs([['Как купить', null]]) ?>
  <div class="page-head"><h1><?= h($c['title']) ?></h1><p class="lead"><?= h($c['lead']) ?></p></div>
  <ol class="steps2">
    <?php foreach ($home['howto']['steps'] as $i => $st): ?>
      <li><span class="step2-n"><?= $i + 1 ?></span><div><h2><?= h($st['title']) ?></h2><p><?= h($st['text']) ?></p></div></li>
    <?php endforeach; ?>
  </ol>
  <?php if ($intro): ?><div class="prose narrow"><?= md($intro) ?></div><?php endif; ?>
  <?php if ($cards): ?>
  <div class="howto-cards">
    <?php foreach ($cards as $k => [$t, $b]): ?>
      <section class="howto-card"><span class="howto-ic"><?= icon3d($icons[$k % 4], 48) ?></span><h2><?= h($t) ?></h2><div class="prose"><?= md($b) ?></div></section>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
</div>
