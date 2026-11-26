<?php
$c = content($key);
meta('title', $c['seo_title'] ?? $c['title']);
meta('desc', $c['seo_desc'] ?? '');
meta('canonical', '/legal/' . $slug);
$docs = ['privacy' => 'Политика обработки данных', 'consent' => 'Согласие на обработку', 'cookies' => 'Cookie', 'terms' => 'Правовая информация'];
?>
<div class="wrap">
  <?= breadcrumbs([['Документы', null], [$c['title'], null]]) ?>
  <div class="page-head"><h1><?= h($c['title']) ?></h1></div>
  <div class="legal-layout">
    <nav class="legal-nav panel" aria-label="Документы">
      <?php foreach ($docs as $k => $t): ?><a href="/legal/<?= $k ?>"<?= $k === $slug ? ' aria-current="page"' : '' ?>><?= h($t) ?></a><?php endforeach; ?>
    </nav>
    <article class="panel prose-panel"><div class="prose"><?= md(legal_tokens($c['body'])) ?></div></article>
  </div>
</div>
