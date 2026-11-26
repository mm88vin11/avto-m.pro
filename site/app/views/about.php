<?php
$c = content('about');
meta('title', $c['seo_title']); meta('desc', $c['seo_desc']); meta('canonical', '/about');
$s = site();
?>
<div class="wrap">
  <?= breadcrumbs([['О компании', null]]) ?>
  <div class="page-head"><h1 data-split><?= h($c['title']) ?></h1><p class="lead"><?= h($c['lead']) ?></p></div>
  <div class="about-band" data-reveal>
    <div class="about-copy"><p class="motto">«<?= h($s['tagline']) ?>»</p></div>
    <div class="about-facts"><?php foreach ($c['facts'] as $f): ?><div class="fact"><b data-count-to="<?= h($f['value']) ?>"><?= h($f['value']) ?></b><span><?= h($f['label']) ?></span></div><?php endforeach; ?></div>
  </div>
  <div class="two-col">
    <article class="panel prose-panel"><div class="prose"><?= md($c['body']) ?></div></article>
    <aside class="panel side-contact">
      <h2>Поможем с подбором</h2>
      <p class="muted">Подскажем, что подойдёт к вашему автомобилю, и уточним наличие.</p>
      <a class="big-phone" href="<?= h(phone_href($s['phone'])) ?>" data-goal="phone_click"><?= h($s['phone']) ?></a>
      <p class="muted"><?= h($s['hours']) ?></p>
      <button class="btn btn-primary btn-block" type="button" data-lead="question" data-title="Задать вопрос"><?= icon('chat') ?>Задать вопрос</button>
      <a class="btn btn-soft btn-block" href="/contacts"><?= icon('pin') ?>Контакты и адрес</a>
    </aside>
  </div>
</div>
