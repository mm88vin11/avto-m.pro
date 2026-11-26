<?php
/**
 * Квиз «Подбор за 30 секунд». Шаги строятся в браузере из данных ниже, без запросов к серверу.
 * Без скрипта показывается обычная ссылка на подбор.
 */
$q = content('home')['quiz'];
$cats = [];
foreach (categories_visible() as $c) {
  $kind = strpos($c['slug'], 'upor') !== false ? 'strut' : (strpos($c['slug'], 'bach') !== false ? 'tank' : 'box');
  $cats[] = ['s' => $c['slug'], 'n' => $c['name'], 'k' => $kind, 'c' => (int)$c['cnt']];
}
$items = [];
foreach (products_all() as $p) {
  $ph = product_photo($p);
  $cat = product_category($p);
  $codes = array_values(array_filter(array_merge([norm_code($p['sku'])], explode(' ', (string)$p['oem_norm']))));
  $items[] = [
    'id' => (int)$p['id'], 'n' => $p['name'], 'sku' => $p['sku'], 'u' => product_url($p), 'cat' => $cat['slug'] ?? '',
    'img' => $ph ? img_url($ph, 400) : '', 'wb' => $p['wb_url'] ? '/go/wb/' . $p['id'] : '', 'oz' => $p['ozon_url'] ? '/go/ozon/' . $p['id'] : '',
    'sp' => array_map(fn($x) => $x[0] . ': ' . $x[1], product_key_specs($p, 2)), 'kit' => $p['kit'], 'codes' => $codes,
    'cp' => array_map(fn($c) => ['mk' => $c['make'], 'mkn' => $c['make_name'], 'md' => $c['model'], 'mdn' => $c['model_name'], 'yf' => $c['year_from'], 'yt' => $c['year_to'], 'b' => $c['bodies'], 'e' => $c['engines']], $p['compat']),
  ];
}
$symptoms = array_values(array_filter($q['symptoms'], fn($s) => in_array($s['cat'] ?? '', array_column($cats, 's'), true)));
$data = ['cats' => $cats, 'items' => $items, 'symptoms' => $symptoms, 'bodies' => site('bodies')];
$id = $id ?? 'quiz';
?>
<div class="quiz panel" id="<?= h($id) ?>" data-quiz data-reveal>
  <div class="quiz-side">
    <span class="quiz-badge"><?= h($q['badge']) ?></span>
    <h2 class="quiz-title"><?= h($q['title']) ?></h2>
    <p class="quiz-lead"><?= h($q['lead']) ?></p>
    <ol class="quiz-steps" aria-hidden="true" data-q-dots><li></li><li></li><li></li><li></li></ol>
    <div class="quiz-trail" data-q-trail></div>
  </div>
  <div class="quiz-main">
    <div class="quiz-stage" data-q-stage aria-live="polite">
      <noscript><p>Для быстрого подбора включите JavaScript или воспользуйтесь <a href="/podbor">формой подбора</a>.</p></noscript>
    </div>
  </div>
  <script type="application/json" data-q-data><?= json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP) ?></script>
</div>
