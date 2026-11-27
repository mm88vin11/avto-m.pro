<?php
/** Главная в стиле утверждённого примера: слева колонка (каталог, подбор, преимущества, вопросы), справа баннер, категории, товары, о компании. */
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
$have = array_column($c['blocks'], 'id');
foreach (array_keys(home_block_names()) as $bn) if (!in_array($bn, $have, true)) $c['blocks'][] = ['id' => $bn, 'on' => !in_array($bn, ['quiz', 'help'], true)];
$on = []; foreach ($c['blocks'] as $b) $on[$b['id']] = !empty($b['on']);
$adv = $c['advantages'];
$sidePh = !empty($c['side']['photo_id']) ? photo_row($c['side']['photo_id']) : null;
?>
<div class="wrap home">
  <aside class="home-side" aria-label="Каталог и подбор">
    <nav class="side-box side-cats" aria-label="Каталог продукции">
      <p class="side-h"><?= icon('menu') ?>Каталог продукции</p>
      <ul>
        <?php foreach ($cats as $cat): ?><li><a href="/catalog/<?= h($cat['slug']) ?>"><span><?= h($cat['name']) ?></span><?= icon('chevron-r') ?></a></li><?php endforeach; ?>
        <li><a href="/catalog"><span>Весь каталог</span><?= icon('chevron-r') ?></a></li>
        <li><a href="/podbor"><span>Подбор по автомобилю</span><?= icon('chevron-r') ?></a></li>
      </ul>
    </nav>

    <?php if ($sidePh): ?><div class="side-photo"><img src="<?= h(img_url($sidePh, 800)) ?>" alt="" loading="lazy" width="400" height="460"></div><?php endif; ?>

    <?php if (!empty($on['picker'])): ?>
    <section class="side-box side-pick" aria-labelledby="sp-title">
      <h2 id="sp-title" class="side-t"><?= h($c['picker']['title']) ?></h2>
      <p class="side-lead"><?= h($c['picker']['lead']) ?></p>
      <?php partial('picker', ['id' => 'sp']); ?>
    </section>
    <?php endif; ?>

    <?php if (!empty($on['advantages'])): ?>
    <section class="side-adv" aria-labelledby="sa-title">
      <h2 id="sa-title" class="side-t"><?= h($adv['title']) ?></h2>
      <ul><?php foreach ($adv['items'] as $a): ?><li><?= icon($a['icon'] ?? 'check') ?><span><b><?= h($a['title']) ?></b><?= h($a['text']) ?></span></li><?php endforeach; ?></ul>
    </section>
    <?php endif; ?>

    <section class="side-ask" aria-labelledby="sq-title">
      <span class="side-ask-ic"><?= icon('chat') ?></span>
      <h2 id="sq-title"><?= h($c['help']['title']) ?></h2>
      <p><?= h($c['help']['text']) ?></p>
      <a class="side-ask-phone" href="<?= h(phone_href($s['phone'])) ?>" data-goal="phone_click"><?= h($s['phone']) ?></a>
      <button class="btn btn-white btn-block" type="button" data-lead="question" data-title="Задать вопрос">Связаться с нами<?= icon('chevron-r') ?></button>
    </section>
  </aside>

  <div class="home-main">
    <?php if ($slides): ?>
    <section class="bnr" aria-roledescription="карусель" aria-label="Баннер" data-slider>
      <div class="bnr-track">
        <?php foreach ($slides as $i => $sl):
          $bg = !empty($sl['bg_id']) ? photo_row($sl['bg_id']) : null;
          $bgm = !empty($sl['bg_m_id']) ? photo_row($sl['bg_m_id']) : null;
          $imgs = [];
          if (!empty($sl['img_id']) && ($x = photo_row($sl['img_id']))) $imgs[] = $x;
          elseif (!empty($sl['cat']) && isset($catBySlug[$sl['cat']])) { $x = $catPhoto($catBySlug[$sl['cat']]); if ($x) $imgs[] = $x; }
          else foreach (array_slice($cats, 0, 2) as $cc) { $x = $catPhoto($cc); if ($x) $imgs[] = $x; }
        ?>
        <div class="slide<?= $i ? '' : ' is-on' ?><?= $bg ? ' has-bg' : '' ?>" role="group" aria-roledescription="слайд" aria-label="<?= $i + 1 ?> из <?= count($slides) ?>"<?= $i ? ' aria-hidden="true"' : '' ?>>
          <?php if ($bg): ?><picture><?php if ($bgm): ?><source media="(max-width: 700px)" srcset="<?= h(img_srcset($bgm)) ?>" sizes="100vw"><?php endif; ?><img class="slide-bg" src="<?= h(img_url($bg, 1400)) ?>" srcset="<?= h(img_srcset($bg)) ?>" sizes="(max-width: 1100px) 100vw, 900px" alt="" <?= $i ? 'loading="lazy"' : 'fetchpriority="high"' ?>></picture><?php endif; ?>
          <div class="slide-copy">
            <?= $i === 0 ? '<h1 class="slide-title">' : '<p class="slide-title">' ?><span><?= h($sl['title']) ?></span><?php if (!empty($sl['sub'])): ?><span class="slide-sub"><?= h($sl['sub']) ?></span><?php endif; ?><?= $i === 0 ? '</h1>' : '</p>' ?>
            <?php if (!empty($sl['text'])): ?><p class="slide-text"><?= h($sl['text']) ?></p><?php endif; ?>
            <?php if (!empty($sl['btn'])): ?><a class="btn btn-primary" href="<?= h($sl['url']) ?>"<?= $i ? ' tabindex="-1"' : '' ?>><?= h($sl['btn']) ?><?= icon('chevron-r') ?></a><?php endif; ?>
          </div>
          <?php if ($imgs): ?>
          <div class="slide-art n<?= count($imgs) ?>" aria-hidden="true">
            <?php foreach ($imgs as $k => $im): ?><img class="slide-obj o<?= $k ?><?= !empty($im['alpha']) ? ' is-cut' : '' ?>" src="<?= h(img_url($im, 800)) ?>" srcset="<?= h(img_url($im, 800)) ?> 800w, <?= h(img_url($im, 1400)) ?> 1400w" sizes="(max-width: 700px) 60vw, 440px" alt="" <?= $i ? 'loading="lazy"' : '' ?>><?php endforeach; ?>
          </div>
          <?php endif; ?>
        </div>
        <?php endforeach; ?>
      </div>
      <?php if (count($slides) > 1): ?>
      <div class="bnr-dots" role="tablist" aria-label="Слайды"><?php foreach ($slides as $i => $sl): ?><button type="button" role="tab" aria-selected="<?= $i ? 'false' : 'true' ?>" aria-label="Слайд <?= $i + 1 ?>: <?= h($sl['title']) ?>" data-slide="<?= $i ?>"><i></i></button><?php endforeach; ?></div>
      <div class="bnr-arrows"><button type="button" data-slide-step="-1" aria-label="Предыдущий слайд"><?= icon('chevron-r') ?></button><button type="button" data-slide-step="1" aria-label="Следующий слайд"><?= icon('chevron-r') ?></button></div>
      <?php endif; ?>
    </section>
    <?php endif; ?>

    <?php if (!empty($on['picker'])): ?>
    <section class="mob-pick panel" aria-label="Подбор по автомобилю">
      <h2 class="side-t"><?= h($c['picker']['title']) ?></h2>
      <?php partial('picker', ['id' => 'mp', 'compact' => true]); ?>
    </section>
    <?php endif; ?>

    <?php foreach ($c['blocks'] as $blk): if (empty($blk['on'])) continue; switch ($blk['id']):

    case 'categories': if (!$cats) break; ?>
    <section class="hsec" aria-labelledby="cat-title">
      <div class="hsec-head"><h2 id="cat-title" class="slash-title"><?= h($c['categories']['title']) ?></h2><a class="link-arrow" href="/catalog">Весь каталог<?= icon('arrow') ?></a></div>
      <div class="cats3">
        <?php foreach ($cats as $cat): $ph = $catPhoto($cat); ?>
          <article class="cat3" data-reveal>
            <h3><a href="/catalog/<?= h($cat['slug']) ?>"><?= h($cat['name']) ?></a></h3>
            <a class="cat3-img<?= ($ph && !empty($ph['alpha'])) ? ' is-cut' : '' ?>" href="/catalog/<?= h($cat['slug']) ?>" tabindex="-1" aria-hidden="true"><?php if ($ph): ?><img src="<?= h(img_url($ph, 800)) ?>" alt="" loading="lazy" width="400" height="260"><?php endif; ?></a>
            <a class="btn btn-navy btn-sm" href="/catalog/<?= h($cat['slug']) ?>">Узнать подробнее<?= icon('chevron-r') ?></a>
          </article>
        <?php endforeach; ?>
        <article class="cat3 cat3-pick" data-reveal>
          <h3><a href="/podbor">Подбор по параметрам</a></h3>
          <a class="cat3-img" href="/podbor" tabindex="-1" aria-hidden="true">
            <?php $first = $cats ? $catPhoto(reset($cats)) : null; if ($first): ?><img class="is-dim" src="<?= h(img_url($first, 800)) ?>" alt="" loading="lazy"><?php endif; ?>
            <span class="cat3-badge"><?= icon('filter') ?></span>
          </a>
          <a class="btn btn-navy btn-sm" href="/podbor">Узнать подробнее<?= icon('chevron-r') ?></a>
        </article>
      </div>
    </section>
    <?php break;

    case 'products':
      $list = array_values($all);
      usort($list, fn($a, $b) => [($b['wb_url'] || $b['ozon_url']) ? 1 : 0, $b['photos'] ? 1 : 0, -$a['sort']] <=> [($a['wb_url'] || $a['ozon_url']) ? 1 : 0, $a['photos'] ? 1 : 0, -$b['sort']]);
      $list = array_slice($list, 0, 6); if (!$list) break; ?>
    <section class="hsec" aria-labelledby="prod-title">
      <div class="hsec-head"><h2 id="prod-title" class="slash-title"><?= h($c['products']['title']) ?></h2><a class="link-arrow" href="/catalog">Все товары<?= icon('arrow') ?></a></div>
      <div class="grid grid-home"><?php foreach ($list as $p) partial('card', ['p' => $p]); ?></div>
    </section>
    <?php break;

    case 'brands': $brands = array_filter(brands_all(), fn($b) => $b['cnt'] > 0); if (!$brands) break; ?>
    <section class="hsec" aria-labelledby="brand-title">
      <div class="hsec-head"><h2 id="brand-title" class="slash-title"><?= h($c['brands']['title']) ?></h2></div>
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
    </section>
    <?php break;

    case 'howto': ?>
    <section class="hsec" aria-labelledby="how-title">
      <div class="hsec-head"><h2 id="how-title" class="slash-title"><?= h($c['howto']['title']) ?></h2><a class="link-arrow" href="/how-to-buy">Подробнее<?= icon('arrow') ?></a></div>
      <ol class="steps"><?php foreach ($c['howto']['steps'] as $i => $st): ?><li class="step" data-reveal><span class="step-n"><?= $i + 1 ?></span><h3><?= h($st['title']) ?></h3><p><?= h($st['text']) ?></p></li><?php endforeach; ?></ol>
    </section>
    <?php break;

    case 'about': $ab = $c['about']; $aph = !empty($ab['photo_id']) ? photo_row($ab['photo_id']) : null; ?>
    <section class="hsec about2" aria-labelledby="about-title" data-reveal>
      <div class="about2-pic<?= $aph ? '' : ' is-empty' ?>"><?php if ($aph): ?><img src="<?= h(img_url($aph, 800)) ?>" alt="" loading="lazy"><?php else: ?><span class="logo-img logo-img-white" aria-hidden="true"></span><?php endif; ?></div>
      <div class="about2-copy">
        <h2 id="about-title" class="slash-title"><?= h($ab['title']) ?></h2>
        <p><?= h($ab['text']) ?></p>
        <a class="link-arrow" href="/about"><?= h($ab['btn']) ?><?= icon('arrow') ?></a>
      </div>
    </section>
    <?php break;

    case 'faq': $faq = content('faq')['items']; if (!$faq) break;
      jsonld(['@context' => 'https://schema.org', '@type' => 'FAQPage', 'mainEntity' => array_map(fn($x) => ['@type' => 'Question', 'name' => $x['q'], 'acceptedAnswer' => ['@type' => 'Answer', 'text' => $x['a']]], $faq)]); ?>
    <section class="hsec" aria-labelledby="faq-title">
      <div class="hsec-head"><h2 id="faq-title" class="slash-title"><?= h($c['faq']['title']) ?></h2></div>
      <div class="faq"><?php foreach ($faq as $x): ?><details class="faq-item"><summary><span><?= h($x['q']) ?></span><i aria-hidden="true"></i></summary><div class="faq-a"><p><?= h($x['a']) ?></p></div></details><?php endforeach; ?></div>
    </section>
    <?php break;

    case 'quiz': ?>
    <section class="hsec"><?php partial('quiz', ['id' => 'quiz']); ?></section>
    <?php break;
    endswitch; endforeach; ?>

    <section class="mob-adv" aria-label="<?= h($adv['title']) ?>">
      <h2 class="slash-title"><?= h($adv['title']) ?></h2>
      <ul><?php foreach ($adv['items'] as $a): ?><li><?= icon($a['icon'] ?? 'check') ?><span><b><?= h($a['title']) ?></b><?= h($a['text']) ?></span></li><?php endforeach; ?></ul>
    </section>
  </div>
</div>
