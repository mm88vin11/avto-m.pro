<?php
$c = content('home');
$s = site();
meta('title', $c['seo_title']);
meta('desc', $c['seo_desc']);
meta('canonical', '/');
meta('body_class', 'page-home');
$all = products_all();
$cats = categories_visible();
$catBySlug = array_column($cats, null, 'slug');
$catPhoto = function ($cat) use ($all) {
  $ph = photo_row($cat['photo_id']);
  if (!$ph) foreach ($all as $p) if ($p['category_id'] == $cat['id'] && $p['photos']) return $p['photos'][0];
  return $ph;
};
$slides = array_values(array_filter($c['slides'], fn($x) => trim($x['title'] ?? '') !== ''));
// блоки, появившиеся после публикации страницы, показываем в конце, пока владелец не расставит их в админке
$have = array_column($c['blocks'], 'id');
foreach (array_keys(home_block_names()) as $bn) if (!in_array($bn, $have, true)) $c['blocks'][] = ['id' => $bn, 'on' => !in_array($bn, ['quiz', 'help'], true)];
?>
<section class="hero" aria-roledescription="карусель" aria-label="Главный баннер" data-slider>
  <div class="hero-track">
    <?php foreach ($slides as $i => $sl):
      $bg = !empty($sl['bg_id']) ? photo_row($sl['bg_id']) : null;
      $imgs = [];
      if (!empty($sl['img_id']) && ($x = photo_row($sl['img_id']))) $imgs[] = $x;
      elseif (!empty($sl['cat']) && isset($catBySlug[$sl['cat']])) { $x = $catPhoto($catBySlug[$sl['cat']]); if ($x) $imgs[] = $x; }
      else foreach (array_slice($cats, 0, 2) as $cc) { $x = $catPhoto($cc); if ($x) $imgs[] = $x; }
    ?>
    <div class="slide<?= $i ? '' : ' is-on' ?><?= $bg ? ' has-bg' : '' ?>" role="group" aria-roledescription="слайд" aria-label="<?= $i + 1 ?> из <?= count($slides) ?>"<?= $i ? ' aria-hidden="true"' : '' ?>>
      <?php $bgm = !empty($sl['bg_m_id']) ? photo_row($sl['bg_m_id']) : null; if ($bg): ?><picture><?php if ($bgm): ?><source media="(max-width: 700px)" srcset="<?= h(img_srcset($bgm)) ?>" sizes="100vw"><?php endif; ?><img class="slide-bg" src="<?= h(img_url($bg, 1400)) ?>" srcset="<?= h(img_srcset($bg)) ?>" sizes="100vw" alt="" <?= $i ? 'loading="lazy"' : 'fetchpriority="high"' ?>></picture><?php else: ?><span class="slide-scene" aria-hidden="true"></span><?php endif; ?>
      <div class="wrap slide-in">
        <p class="slide-kicker"><?= h($sl['kicker']) ?></p>
        <?= $i === 0 ? '<h1 class="slide-title">' : '<p class="slide-title">' ?><?= h($sl['title']) ?><?= $i === 0 ? '</h1>' : '</p>' ?>
        <?php if ($imgs): ?>
        <div class="slide-art n<?= count($imgs) ?>" aria-hidden="true">
          <?php foreach ($imgs as $k => $im): ?>
            <img class="slide-obj o<?= $k ?><?= !empty($im['alpha']) ? ' is-cut' : '' ?>" src="<?= h(img_url($im, 800)) ?>" srcset="<?= h(img_url($im, 800)) ?> 800w, <?= h(img_url($im, 1400)) ?> 1400w" sizes="(max-width: 700px) 80vw, 560px" alt="" <?= $i ? 'loading="lazy"' : '' ?>>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>
        <?php if ($sl['pill1']): ?><span class="slide-pill p1"><?= icon('shield') ?><?= h($sl['pill1']) ?></span><?php endif; ?>
        <?php if ($sl['pill2']): ?><span class="slide-pill p2"><?= icon('car') ?><?= h($sl['pill2']) ?></span><?php endif; ?>
        <a class="slide-btn" href="<?= h($sl['url']) ?>"<?= $i ? ' tabindex="-1"' : '' ?>><?= h($sl['btn']) ?><?= icon('arrow') ?></a>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
  <?php if (count($slides) > 1): ?>
  <div class="hero-dots" role="tablist" aria-label="Слайды">
    <?php foreach ($slides as $i => $sl): ?><button type="button" role="tab" aria-selected="<?= $i ? 'false' : 'true' ?>" aria-label="Слайд <?= $i + 1 ?>: <?= h($sl['title']) ?>" data-slide="<?= $i ?>"><i></i></button><?php endforeach; ?>
  </div>
  <?php endif; ?>
</section>

<?php foreach ($c['blocks'] as $blk): if (empty($blk['on'])) continue; switch ($blk['id']):

case 'search': ?>
<div class="wrap home-search-wrap">
  <form class="home-search" action="/search" method="get" role="search">
    <label class="sr" for="hq">Поиск по каталогу</label>
    <?= icon('search') ?>
    <input id="hq" name="q" type="search" placeholder="<?= h($c['search']['placeholder']) ?>" autocomplete="off" enterkeyhint="search">
    <button class="btn btn-primary" type="submit">Найти</button>
  </form>
</div>
<?php break;

case 'categories': if (!$cats) break; ?>
<section class="section" aria-labelledby="cat-title">
  <div class="wrap">
    <div class="sec-head"><h2 id="cat-title"><?= h($c['categories']['title']) ?></h2><a class="pill-link" href="/catalog">Весь каталог<?= icon('arrow') ?></a></div>
    <div class="tiles">
      <?php foreach ($cats as $cat): $ph = $catPhoto($cat); ?>
        <a class="tile" href="/catalog/<?= h($cat['slug']) ?>" data-reveal>
          <span class="tile-t"><?= h($cat['name']) ?><?= icon('arrow') ?></span>
          <span class="tile-n"><?= (int)$cat['cnt'] ?> <?= plural($cat['cnt'], 'товар', 'товара', 'товаров') ?></span>
          <?php if ($ph): ?><img class="tile-img<?= !empty($ph['alpha']) ? ' is-cut' : '' ?>" src="<?= h(img_url($ph, 800)) ?>" width="400" height="300" alt="" loading="lazy"><?php endif; ?>
        </a>
      <?php endforeach; ?>
      <a class="tile tile-all" href="/catalog" data-reveal>
        <span class="tile-t">Весь каталог<?= icon('arrow') ?></span>
        <span class="tile-n"><?= count($all) ?> <?= plural(count($all), 'товар', 'товара', 'товаров') ?> с подбором по авто</span>
        <span class="tile-stack" aria-hidden="true"><?php foreach (array_slice($cats, 0, 2) as $k => $cc): $ph = $catPhoto($cc); if ($ph): ?><img class="is-cut s<?= $k ?>" src="<?= h(img_url($ph, 400)) ?>" alt="" loading="lazy"><?php endif; endforeach; ?></span>
      </a>
    </div>
  </div>
</section>
<?php break;

case 'picker': ?>
<section class="section" aria-labelledby="pick-title">
  <div class="wrap">
    <div class="panel picker-panel" data-reveal>
      <div class="picker-head"><div><h2 id="pick-title"><?= h($c['picker']['title']) ?></h2><p class="muted"><?= h($c['picker']['lead']) ?></p></div><div class="garage-inline" data-garage-inline hidden></div></div>
      <?php partial('picker', ['id' => 'hp', 'compact' => true]); ?>
    </div>
  </div>
</section>
<?php break;

case 'cards': $cc = $c['cards']; $pi = !empty($cc['params_img']) ? photo_row($cc['params_img']) : null; $li = !empty($cc['lead_img']) ? photo_row($cc['lead_img']) : null; ?>
<section class="section">
  <div class="wrap duo">
    <div class="duo-card" data-reveal>
      <div class="duo-copy"><h2><?= h($cc['params_title']) ?></h2><p><?= h($cc['params_text']) ?></p><a class="btn btn-primary btn-lg" href="<?= h($cc['params_url']) ?>"><?= h($cc['params_btn']) ?><?= icon('arrow') ?></a></div>
      <div class="duo-art" aria-hidden="true">
        <?php if ($pi): ?><img src="<?= h(img_url($pi, 800)) ?>" alt="" loading="lazy"><?php else: ?>
        <span class="duo-blob"></span>
        <span class="sliders"><span style="--v:.38"><i></i></span><span style="--v:.62"><i></i></span><span style="--v:.3"><i></i></span></span>
        <?php endif; ?>
      </div>
    </div>
    <div class="duo-card duo-card-soft" data-reveal>
      <div class="duo-copy"><h2><?= h($cc['lead_title']) ?></h2><p><?= h($cc['lead_text']) ?></p><button class="btn btn-white-soft btn-lg" type="button" data-lead="pick" data-title="Помощь с подбором"><?= h($cc['lead_btn']) ?><?= icon('arrow') ?></button></div>
      <div class="duo-art" aria-hidden="true">
        <?php if ($li): ?><img src="<?= h(img_url($li, 800)) ?>" alt="" loading="lazy"><?php else: ?>
        <span class="duo-blob duo-blob-round"></span>
        <svg class="bubble" viewBox="0 0 120 100"><path d="M20 12h80a14 14 0 0 1 14 14v38a14 14 0 0 1-14 14H52L30 94V78H20A14 14 0 0 1 6 64V26a14 14 0 0 1 14-14z"/><circle cx="40" cy="45" r="6"/><circle cx="60" cy="45" r="6"/><circle cx="80" cy="45" r="6"/></svg>
        <?php endif; ?>
      </div>
    </div>
  </div>
</section>
<?php break;

case 'quiz': ?>
<section class="section" aria-label="Подбор за 30 секунд"><div class="wrap"><?php partial('quiz', ['id' => 'quiz']); ?></div></section>
<?php break;

case 'products':
  $list = array_values($all);
  usort($list, fn($a, $b) => [($b['wb_url'] || $b['ozon_url']) ? 1 : 0, $b['photos'] ? 1 : 0, -$a['sort']] <=> [($a['wb_url'] || $a['ozon_url']) ? 1 : 0, $a['photos'] ? 1 : 0, -$b['sort']]);
  $list = array_slice($list, 0, 8); if (!$list) break; ?>
<section class="section" aria-labelledby="prod-title">
  <div class="wrap">
    <div class="sec-head"><h2 id="prod-title"><?= h($c['products']['title']) ?></h2><a class="pill-link" href="/catalog">Все товары<?= icon('arrow') ?></a></div>
    <div class="grid"><?php foreach ($list as $p) partial('card', ['p' => $p]); ?></div>
  </div>
</section>
<?php break;

case 'advantages': ?>
<section class="section" aria-labelledby="adv-title">
  <div class="wrap">
    <div class="sec-head"><h2 id="adv-title"><?= h($c['advantages']['title']) ?></h2></div>
    <div class="why">
      <?php foreach ($c['advantages']['items'] as $a): ?>
        <div class="why-item" data-reveal><span class="why-ic"><?= icon($a['icon'] ?? 'check') ?></span><div><h3><?= h($a['title']) ?></h3><p><?= h($a['text']) ?></p></div></div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php break;

case 'brands': $brands = array_filter(brands_all(), fn($b) => $b['cnt'] > 0); if (!$brands) break; ?>
<section class="section" aria-labelledby="brand-title">
  <div class="wrap">
    <div class="sec-head"><h2 id="brand-title"><?= h($c['brands']['title']) ?></h2></div>
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
    <div class="sec-head"><h2 id="how-title"><?= h($c['howto']['title']) ?></h2><a class="pill-link" href="/how-to-buy">Подробнее<?= icon('arrow') ?></a></div>
    <ol class="steps">
      <?php foreach ($c['howto']['steps'] as $i => $st): ?><li class="step" data-reveal><span class="step-n"><?= $i + 1 ?></span><h3><?= h($st['title']) ?></h3><p><?= h($st['text']) ?></p></li><?php endforeach; ?>
    </ol>
  </div>
</section>
<?php break;

case 'about': $ab = $c['about']; $aph = !empty($ab['photo_id']) ? photo_row($ab['photo_id']) : null; ?>
<section class="section" aria-labelledby="about-title">
  <div class="wrap about-row">
    <div class="about-card" data-reveal>
      <div class="about-pic<?= $aph ? '' : ' is-empty' ?>"><?php if ($aph): ?><img src="<?= h(img_url($aph, 800)) ?>" alt="" loading="lazy"><?php else: ?><span class="logo-img logo-img-white" aria-hidden="true"></span><?php endif; ?></div>
      <div class="about-copy"><h2 id="about-title" class="bar-title"><?= h($ab['title']) ?></h2><p><?= h($ab['text']) ?></p><a class="link-arrow" href="/about"><?= h($ab['btn']) ?><?= icon('arrow') ?></a></div>
    </div>
    <div class="ask-card" data-reveal>
      <span class="ask-ic"><?= icon('chat') ?></span>
      <h2><?= h($c['help']['title']) ?></h2>
      <p><?= h($c['help']['text']) ?></p>
      <a class="ask-phone" href="<?= h(phone_href($s['phone'])) ?>" data-goal="phone_click"><?= h($s['phone']) ?></a>
      <small><?= h($s['hours']) ?></small>
      <button class="btn btn-white" type="button" data-lead="question" data-title="Задать вопрос">Связаться с нами<?= icon('chevron-r') ?></button>
    </div>
  </div>
</section>
<?php break;

case 'faq': $faq = content('faq')['items']; if (!$faq) break;
  jsonld(['@context' => 'https://schema.org', '@type' => 'FAQPage', 'mainEntity' => array_map(fn($x) => ['@type' => 'Question', 'name' => $x['q'], 'acceptedAnswer' => ['@type' => 'Answer', 'text' => $x['a']]], $faq)]); ?>
<section class="section" aria-labelledby="faq-title">
  <div class="wrap">
    <div class="sec-head"><h2 id="faq-title"><?= h($c['faq']['title']) ?></h2></div>
    <div class="faq faq-2">
      <?php foreach ($faq as $x): ?><details class="faq-item"><summary><span><?= h($x['q']) ?></span><i aria-hidden="true"></i></summary><div class="faq-a"><p><?= h($x['a']) ?></p></div></details><?php endforeach; ?>
    </div>
  </div>
</section>
<?php break;

case 'help': ?>
<section class="section"><div class="wrap"><div class="help panel"><div class="help-copy"><h2><?= h($c['help']['title']) ?></h2><p><?= h($c['help']['text']) ?></p></div><div class="help-btns"><button class="btn btn-primary btn-lg" type="button" data-lead="pick" data-title="Помощь с подбором">Помогите подобрать</button></div></div></div></section>
<?php break;
endswitch; endforeach;
