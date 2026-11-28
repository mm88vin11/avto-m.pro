<?php
$c = content('about');
meta('title', $c['seo_title']); meta('desc', $c['seo_desc']); meta('canonical', '/about');
$s = site();
$bset = site_img_srcset('about-building');
?>
<div class="wrap">
  <?= breadcrumbs([['О компании', null]]) ?>
  <section class="about2 about-hero">
    <div class="about2-copy">
      <h1><?= h($c['title']) ?></h1>
      <p class="about2-motto"><?= h($s['tagline']) ?></p>
      <p class="about2-text"><?= h($c['lead']) ?></p>
      <?php if ($c['facts']): ?><ul class="about-facts2"><?php foreach ($c['facts'] as $f): ?><li><b><?= h($f['value']) ?></b><span><?= h($f['label']) ?></span></li><?php endforeach; ?></ul><?php endif; ?>
    </div>
    <div class="about2-pic"><img src="<?= h(site_img('about-building')) ?>"<?= $bset ? ' srcset="' . h($bset) . '" sizes="(max-width: 899px) 92vw, 640px"' : '' ?> width="1400" height="731" alt="Магазин AVTO-M" fetchpriority="high"></div>
  </section>
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
