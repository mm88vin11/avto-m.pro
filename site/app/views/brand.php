<?php
$facts = json_decode($b['facts'] ?: '[]', true);
$items = array_values(array_filter(products_all(), fn($p) => $p['brand_id'] == $b['id']));
$logo = photo_row($b['logo_photo_id']);
meta('title', $b['seo_title'] ?: $b['name'] . ' — ' . mb_strtolower($b['tagline']) . ' | AVTO-M');
meta('desc', $b['seo_desc'] ?: md_plain($b['description'], 160));
meta('canonical', '/brand/' . $b['slug']);
jsonld(['@context' => 'https://schema.org', '@type' => 'Brand', 'name' => $b['name'], 'description' => md_plain($b['description'], 300), 'url' => site_url('/brand/' . $b['slug'])]);
?>
<div class="wrap">
  <?= breadcrumbs([['Марки деталей', null], [$b['name'], null]]) ?>
  <div class="brand-hero panel" data-reveal>
    <div>
      <p class="brand-mark brand-mark-lg"><?php if ($logo): ?><img src="<?= h(img_url($logo, 800)) ?>" alt="<?= h($b['name']) ?>"><?php else: ?><?= h($b['name']) ?><?php endif; ?></p>
      <h1 class="brand-h1"><?= h($b['tagline']) ?></h1>
      <?php if ($b['country']): ?><p class="muted">Страна производства: <?= h($b['country']) ?></p><?php endif; ?>
    </div>
    <?php if ($facts): ?><div class="brand-facts brand-facts-big"><?php foreach ($facts as [$k, $v]): ?><span><small><?= h($k) ?></small><b><?= h(preg_replace("~ (°|мм|Н|лет|год|мл)\b~u", "\u{00A0}$1", $v)) ?></b></span><?php endforeach; ?></div><?php endif; ?>
  </div>
  <article class="panel prose-panel narrow"><div class="prose"><?= md($b['description']) ?></div></article>
  <?php if ($items): ?>
  <section class="section">
    <div class="section-head"><h2>Товары <?= h($b['name']) ?></h2></div>
    <div class="grid"><?php foreach ($items as $p) partial('card', ['p' => $p]); ?></div>
  </section>
  <?php endif; ?>
</div>
