<?php
$c = content('howto');
meta('title', $c['seo_title']); meta('desc', $c['seo_desc']); meta('canonical', '/how-to-buy');
$home = content('home');
?>
<div class="wrap">
  <?= breadcrumbs([['Как купить', null]]) ?>
  <div class="page-head"><h1 data-split><?= h($c['title']) ?></h1><p class="lead"><?= h($c['lead']) ?></p></div>
  <ol class="steps">
    <?php foreach ($home['howto']['steps'] as $i => $st): ?>
      <li class="step" data-reveal style="--d:<?= $i * 90 ?>ms"><span class="step-n"><?= $i + 1 ?></span><h2 class="h3"><?= h($st['title']) ?></h2><p><?= h($st['text']) ?></p></li>
    <?php endforeach; ?>
  </ol>
  <article class="panel prose-panel narrow"><div class="prose"><?= md($c['body']) ?></div></article>
</div>
