<?php
$c = content('partners');
meta('title', $c['seo_title']); meta('desc', $c['seo_desc']); meta('canonical', '/partners');
?>
<div class="wrap">
  <?= breadcrumbs([['Преимущества и сотрудничество', null]]) ?>
  <div class="page-head"><h1 data-split><?= h($c['title']) ?></h1><p class="lead"><?= h($c['lead']) ?></p></div>
  <div class="adv">
    <?php foreach ($c['cards'] as $i => $a): ?>
      <div class="adv-item" data-reveal style="--d:<?= $i * 70 ?>ms"><span class="adv-ic"><?= icon($a['icon'] ?? 'check') ?></span><h2 class="h3"><?= h($a['title']) ?></h2><p><?= h($a['text']) ?></p></div>
    <?php endforeach; ?>
  </div>
  <section class="section">
    <div class="b2b panel" data-reveal>
      <div class="b2b-copy">
        <span class="b2b-ic" aria-hidden="true"><?= icon('building') ?></span>
        <h2><?= h($c['b2b_title']) ?></h2>
        <div class="prose"><?= md($c['b2b_text']) ?></div>
      </div>
      <div class="b2b-form" id="partner-form">
        <h3><?= h($c['b2b_btn']) ?></h3>
        <?php partial('lead_form', ['kind' => 'partner', 'id' => 'pf2', 'withCompany' => true, 'btn' => $c['b2b_btn'], 'commentLabel' => 'Что интересует']); ?>
      </div>
    </div>
  </section>
</div>
