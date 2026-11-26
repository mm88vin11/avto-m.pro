<?php
$c = content('home');
$s = site();
meta('title', $c['seo_title']);
meta('desc', $c['seo_desc']);
meta('canonical', '/');
meta('body_class', 'page-home');
$hero = $c['hero'];
$heroPh = !empty($hero['photo_id']) ? photo_row($hero['photo_id']) : null;
$all = products_all();
$stage = [];
foreach ($all as $p) { if ($p['photos']) $stage[$p['category_id']] = $stage[$p['category_id']] ?? $p; }
$stage = array_slice(array_values($stage), 0, 2);
foreach ($all as $p) { if (count($stage) >= 3) break; if ($p['photos'] && !in_array($p, $stage, true)) $stage[] = $p; }
?>
<section class="hero<?= $heroPh ? ' hero-photo' : '' ?>" aria-labelledby="hero-title">
  <div class="wrap">
    <div class="hero-panel">
      <?php if ($heroPh): ?><img class="hero-bg" src="<?= h(img_url($heroPh, 1400)) ?>" srcset="<?= h(img_srcset($heroPh)) ?>" sizes="100vw" alt="" fetchpriority="high"><?php endif; ?>
      <svg class="hero-arcs" viewBox="0 0 1200 520" preserveAspectRatio="xMidYMid slice" aria-hidden="true">
        <defs>
          <linearGradient id="arcg" x1="0" x2="1"><stop offset="0" stop-color="#5b9bff" stop-opacity="0"/><stop offset=".45" stop-color="#7fb2ff" stop-opacity=".9"/><stop offset="1" stop-color="#2f7bff" stop-opacity="0"/></linearGradient>
          <radialGradient id="glowg" cx=".72" cy=".45" r=".55"><stop offset="0" stop-color="#2f7bff" stop-opacity=".55"/><stop offset="1" stop-color="#2f7bff" stop-opacity="0"/></radialGradient>
        </defs>
        <rect width="1200" height="520" fill="url(#glowg)"/>
        <path class="arc a1" d="M-40 430 C 220 250, 520 190, 760 210 S 1120 300, 1260 190"/>
        <path class="arc a2" d="M-60 470 C 260 330, 560 280, 820 300 S 1140 380, 1280 300"/>
        <path class="arc a3" d="M120 120 C 380 40, 700 30, 900 110"/>
      </svg>
      <div class="hero-copy">
        <p class="eyebrow"><?= icon('sparkle') ?><?= h($hero['eyebrow']) ?></p>
        <h1 id="hero-title" class="hero-title" data-split><?= h($hero['title']) ?></h1>
        <p class="hero-lead"><?= h($hero['lead']) ?></p>
        <div class="hero-btns">
          <a class="btn btn-white btn-lg" href="<?= h($hero['btn1_url']) ?>" data-magnet><?= icon('car') ?><?= h($hero['btn1_text']) ?></a>
          <a class="btn btn-glass btn-lg" href="<?= h($hero['btn2_url']) ?>"><?= h($hero['btn2_text']) ?><?= icon('arrow') ?></a>
        </div>
        <?php if (!empty($hero['pills'])): ?>
        <ul class="hero-pills"><?php foreach ($hero['pills'] as $pl): ?><li><?= icon('check') ?><?= h($pl) ?></li><?php endforeach; ?></ul>
        <?php endif; ?>
      </div>
      <?php if (!$heroPh && $stage): ?>
      <div class="hero-stage" data-tilt aria-hidden="true">
        <?php foreach ($stage as $i => $p): $ph = product_photo($p); ?>
          <a class="stage-card s<?= $i ?>" href="<?= h(product_url($p)) ?>" tabindex="-1">
            <img src="<?= h(img_url($ph, 400)) ?>" srcset="<?= h(img_url($ph, 400)) ?> 400w, <?= h(img_url($ph, 800)) ?> 800w" sizes="240px" width="400" height="533" alt="" <?= $i === 0 ? 'fetchpriority="high"' : 'loading="lazy"' ?>>
            <span class="stage-label"><b><?= h(product_brand($p)['name'] ?? '') ?></b><?= h(product_category($p)['name'] ?? '') ?></span>
          </a>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
    </div>
  </div>
</section>

<?php foreach ($c['blocks'] as $blk): if (empty($blk['on'])) continue; switch ($blk['id']):

case 'picker': ?>
<section class="section section-picker" aria-labelledby="pick-title">
  <div class="wrap">
    <div class="panel picker-panel" data-reveal>
      <div class="picker-head">
        <div>
          <h2 id="pick-title"><?= h($c['picker']['title']) ?></h2>
          <p class="muted"><?= h($c['picker']['lead']) ?></p>
        </div>
        <div class="garage-inline" data-garage-inline hidden></div>
      </div>
      <?php partial('picker', ['id' => 'hp', 'compact' => true]); ?>
    </div>
  </div>
</section>
<?php break;

case 'categories': $cats = categories_visible(); if (!$cats) break; ?>
<section class="section" aria-labelledby="cat-title">
  <div class="wrap">
    <div class="section-head"><h2 id="cat-title" data-split><?= h($c['categories']['title']) ?></h2><a class="link-arrow" href="/catalog">Весь каталог<?= icon('arrow') ?></a></div>
    <div class="cats">
      <?php foreach ($cats as $cat):
        $cph = photo_row($cat['photo_id']);
        if (!$cph) foreach ($all as $p) if ($p['category_id'] == $cat['id'] && $p['photos']) { $cph = $p['photos'][0]; break; } ?>
        <a class="cat" href="/catalog/<?= h($cat['slug']) ?>" data-reveal>
          <span class="cat-text"><b><?= h($cat['name']) ?></b><small><?= (int)$cat['cnt'] ?> <?= plural($cat['cnt'], 'товар', 'товара', 'товаров') ?></small></span>
          <span class="cat-img"><?php if ($cph): ?><img src="<?= h(img_url($cph, 400)) ?>" width="400" height="533" alt="" loading="lazy"><?php endif; ?></span>
          <span class="cat-go"><?= icon('arrow') ?></span>
        </a>
      <?php endforeach; ?>
      <a class="cat cat-pick" href="/podbor" data-reveal>
        <span class="cat-text"><b>Подбор по параметрам</b><small>Длина, усилие, производитель</small></span>
        <span class="cat-icon"><?= icon('filter') ?></span>
        <span class="cat-go"><?= icon('arrow') ?></span>
      </a>
    </div>
  </div>
</section>
<?php break;

case 'products':
  $list = array_values($all);
  usort($list, fn($a, $b) => [($b['wb_url'] || $b['ozon_url']) ? 1 : 0, $b['photos'] ? 1 : 0, -$a['sort']] <=> [($a['wb_url'] || $a['ozon_url']) ? 1 : 0, $a['photos'] ? 1 : 0, -$b['sort']]);
  $list = array_slice($list, 0, 8); if (!$list) break; ?>
<section class="section" aria-labelledby="prod-title">
  <div class="wrap">
    <div class="section-head"><div><h2 id="prod-title" data-split><?= h($c['products']['title']) ?></h2><p class="muted"><?= h($c['products']['lead']) ?></p></div><a class="link-arrow" href="/catalog">Все товары<?= icon('arrow') ?></a></div>
    <div class="grid" data-car-aware>
      <?php foreach ($list as $p) partial('card', ['p' => $p]); ?>
    </div>
  </div>
</section>
<?php break;

case 'brands': $brands = array_filter(brands_all(), fn($b) => $b['cnt'] > 0); if (!$brands) break; ?>
<section class="section" aria-labelledby="brand-title">
  <div class="wrap">
    <div class="section-head"><div><h2 id="brand-title" data-split><?= h($c['brands']['title']) ?></h2><p class="muted"><?= h($c['brands']['lead']) ?></p></div></div>
    <div class="brands">
      <?php foreach ($brands as $b): $facts = json_decode($b['facts'] ?: '[]', true); $logo = photo_row($b['logo_photo_id']); ?>
        <a class="brand-card" href="/brand/<?= h($b['slug']) ?>" data-reveal>
          <span class="brand-mark"><?php if ($logo): ?><img src="<?= h(img_url($logo, 400)) ?>" alt="<?= h($b['name']) ?>" loading="lazy"><?php else: ?><?= h($b['name']) ?><?php endif; ?></span>
          <span class="brand-tag"><?= h($b['tagline']) ?></span>
          <?php if ($facts): ?><span class="brand-facts"><?php foreach (array_slice($facts, 0, 4) as [$k, $v]): ?><span><small><?= h($k) ?></small><b><?= h($v) ?></b></span><?php endforeach; ?></span><?php endif; ?>
          <span class="link-arrow"><?= (int)$b['cnt'] ?> <?= plural($b['cnt'], 'товар', 'товара', 'товаров') ?><?= icon('arrow') ?></span>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php break;

case 'howto': ?>
<section class="section" aria-labelledby="how-title">
  <div class="wrap">
    <div class="section-head"><h2 id="how-title" data-split><?= h($c['howto']['title']) ?></h2><a class="link-arrow" href="/how-to-buy">Подробнее<?= icon('arrow') ?></a></div>
    <ol class="steps">
      <?php foreach ($c['howto']['steps'] as $i => $st): ?>
        <li class="step" data-reveal style="--d:<?= $i * 90 ?>ms"><span class="step-n"><?= $i + 1 ?></span><h3><?= h($st['title']) ?></h3><p><?= h($st['text']) ?></p></li>
      <?php endforeach; ?>
    </ol>
    <div class="mp-strip" data-reveal>
      <span class="mp-logo mp-logo-wb">Wildberries</span><span class="mp-logo mp-logo-ozon">Ozon</span>
      <p>Оплата, доставка в пункт выдачи и возврат — по правилам маркетплейса. Кнопки в карточках ведут сразу на нужную деталь.</p>
    </div>
  </div>
</section>
<?php break;

case 'advantages': ?>
<section class="section" aria-labelledby="adv-title">
  <div class="wrap">
    <div class="section-head"><h2 id="adv-title" data-split><?= h($c['advantages']['title']) ?></h2></div>
    <div class="adv">
      <?php foreach ($c['advantages']['items'] as $i => $a): ?>
        <div class="adv-item" data-reveal style="--d:<?= $i * 70 ?>ms"><span class="adv-ic"><?= icon($a['icon'] ?? 'check') ?></span><h3><?= h($a['title']) ?></h3><p><?= h($a['text']) ?></p></div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php break;

case 'about': $ab = content('about'); ?>
<section class="section" aria-labelledby="about-title">
  <div class="wrap">
    <div class="about-band" data-reveal>
      <div class="about-copy">
        <h2 id="about-title"><?= h($c['about']['title']) ?></h2>
        <p><?= h($c['about']['text']) ?></p>
        <a class="btn btn-white" href="/about"><?= h($c['about']['btn']) ?><?= icon('arrow') ?></a>
      </div>
      <div class="about-facts">
        <?php foreach ($ab['facts'] as $f): ?><div class="fact"><b data-count-to="<?= h($f['value']) ?>"><?= h($f['value']) ?></b><span><?= h($f['label']) ?></span></div><?php endforeach; ?>
      </div>
    </div>
  </div>
</section>
<?php break;

case 'faq': $faq = content('faq')['items']; if (!$faq) break;
  jsonld(['@context' => 'https://schema.org', '@type' => 'FAQPage', 'mainEntity' => array_map(fn($x) => ['@type' => 'Question', 'name' => $x['q'], 'acceptedAnswer' => ['@type' => 'Answer', 'text' => $x['a']]], $faq)]); ?>
<section class="section" aria-labelledby="faq-title">
  <div class="wrap faq-wrap">
    <div class="faq-side"><h2 id="faq-title" data-split><?= h($c['faq']['title']) ?></h2><p class="muted">Не нашли ответ — спросите, ответим в рабочее время.</p><button class="btn btn-soft" type="button" data-lead="question" data-title="Задать вопрос"><?= icon('chat') ?>Задать вопрос</button></div>
    <div class="faq">
      <?php foreach ($faq as $x): ?>
        <details class="faq-item" data-reveal><summary><span><?= h($x['q']) ?></span><i aria-hidden="true"></i></summary><div class="faq-a"><p><?= h($x['a']) ?></p></div></details>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php break;

case 'help': ?>
<section class="section" aria-labelledby="help-title">
  <div class="wrap">
    <div class="help" data-reveal>
      <div class="help-ic" aria-hidden="true"><?= icon('chat') ?></div>
      <div class="help-copy"><h2 id="help-title"><?= h($c['help']['title']) ?></h2><p><?= h($c['help']['text']) ?></p></div>
      <div class="help-btns">
        <button class="btn btn-primary btn-lg" type="button" data-lead="pick" data-title="Помощь с подбором"><?= icon('car') ?>Помогите подобрать</button>
        <a class="btn btn-soft btn-lg" href="<?= h(phone_href($s['phone'])) ?>" data-goal="phone_click"><?= icon('phone') ?><?= h($s['phone']) ?></a>
      </div>
    </div>
  </div>
</section>
<?php break;
endswitch; endforeach; ?>
