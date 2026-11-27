<?php
/**
 * Главная: структура утверждённой презентации + блоки концепта заказчика (IMG_1789).
 * Баннер-слайдер → «Ваш автомобиль» → каталог плитками → подбор/заявка → товары → признаки износа → почему мы → марки → о компании → вопросы.
 */
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
foreach (array_keys(home_block_names()) as $bn) if (!in_array($bn, $have, true)) $c['blocks'][] = ['id' => $bn, 'on' => !in_array($bn, ['quiz', 'howto'], true)];
?>
<?php
$blkOn = []; foreach ($c['blocks'] as $bb) $blkOn[$bb['id']] = !empty($bb['on']);
$cc = $c['cards']; $pi = !empty($cc['params_img']) ? photo_row($cc['params_img']) : null; $li = !empty($cc['lead_img']) ? photo_row($cc['lead_img']) : null;
$side = !empty($blkOn['cards']);
?>
<section class="wrap h5<?= $side ? '' : ' h5-solo' ?>" aria-label="Главное">
  <?php if ($slides): ?>
  <div class="h5-slider" aria-roledescription="карусель" aria-label="Баннер" data-slider>
    <?php foreach ($slides as $i => $sl):
      $bg = !empty($sl['bg_id']) ? photo_row($sl['bg_id']) : null;
      $bgm = !empty($sl['bg_m_id']) ? photo_row($sl['bg_m_id']) : null;
      $img = !empty($sl['img_id']) ? photo_row($sl['img_id']) : null;
      if (!$img && !empty($sl['cat']) && isset($catBySlug[$sl['cat']])) $img = $catPhoto($catBySlug[$sl['cat']]);
      $chips = array_values(array_filter(array_map('trim', preg_split("~\r?\n~", (string)($sl['chips'] ?? ''))), 'strlen'));
      $theme = in_array($sl['theme'] ?? '', ['blue', 'light', 'navy', 'white'], true) ? $sl['theme'] : 'blue';
    ?>
    <div class="slide t-<?= $theme ?><?= $i ? '' : ' is-on' ?>" role="group" aria-roledescription="слайд" aria-label="<?= $i + 1 ?> из <?= count($slides) ?>"<?= $i ? ' aria-hidden="true"' : '' ?>>
      <?php if ($bg): ?><picture class="slide-bgp"><?php if ($bgm): ?><source media="(max-width: 699px)" srcset="<?= h(img_srcset($bgm)) ?>" sizes="100vw"><?php endif; ?><img class="slide-bg" src="<?= h(img_url($bg, 1400)) ?>" srcset="<?= h(img_srcset($bg)) ?>" sizes="(max-width: 1240px) 100vw, 880px" alt="" <?= $i ? 'loading="lazy"' : 'fetchpriority="high"' ?>></picture><?php endif; ?>
      <div class="slide-copy">
        <?= $i === 0 ? '<h1 class="slide-title">' : '<p class="slide-title">' ?><span class="ln"><span><?= h($sl['title']) ?></span></span><?php if (!empty($sl['sub'])): ?><span class="ln slide-sub"><span><?= h($sl['sub']) ?></span></span><?php endif; ?><?= $i === 0 ? '</h1>' : '</p>' ?>
        <?php if (!empty($sl['text'])): ?><p class="slide-text"><?= h($sl['text']) ?></p><?php endif; ?>
        <?php if ($chips): ?><ul class="slide-chips"><?php foreach (array_slice($chips, 0, 3) as $ch): ?><li><?= icon('check') ?><?= h($ch) ?></li><?php endforeach; ?></ul><?php endif; ?>
        <div class="slide-btns">
          <?php if (!empty($sl['btn'])): ?><a class="btn btn-lg slide-btn" href="<?= h($sl['url']) ?>"<?= $i ? ' tabindex="-1"' : '' ?>><?= h($sl['btn']) ?><?= icon('arrow') ?></a><?php endif; ?>
          <?php if (!empty($sl['btn2'])): ?><a class="btn btn-lg slide-btn2" href="<?= h($sl['url2'] ?: '/catalog') ?>"<?= $i ? ' tabindex="-1"' : '' ?>><?= h($sl['btn2']) ?></a><?php endif; ?>
        </div>
      </div>
      <?php if ($img): ?>
      <div class="slide-art" aria-hidden="true">
        <img class="slide-obj<?= !empty($img['alpha']) ? ' is-cut' : '' ?>" src="<?= h(img_url($img, 800)) ?>" srcset="<?= h(img_url($img, 800)) ?> 800w, <?= h(img_url($img, 1400)) ?> 1400w" sizes="(max-width: 699px) 90vw, 560px" alt="" <?= $i ? 'loading="lazy"' : 'fetchpriority="high"' ?> data-parallax>
      </div>
      <?php endif; ?>
    </div>
    <?php endforeach; ?>
    <?php if (count($slides) > 1): ?>
    <div class="h5-nav">
      <div class="h5-dots" role="tablist" aria-label="Слайды"><?php foreach ($slides as $i => $sl): ?><button type="button" role="tab" aria-selected="<?= $i ? 'false' : 'true' ?>" aria-label="<?= h($sl['title']) ?>" data-slide="<?= $i ?>"><i></i></button><?php endforeach; ?></div>
      <div class="h5-arrows"><button type="button" data-slide-step="-1" aria-label="Предыдущий слайд"><?= icon('arrow-left') ?></button><button type="button" data-slide-step="1" aria-label="Следующий слайд"><?= icon('arrow') ?></button></div>
    </div>
    <?php endif; ?>
  </div>
  <?php endif; ?>
  <?php if ($side): ?>
  <div class="h5-side">
    <a class="qcard qcard-params" href="<?= h($cc['params_url']) ?>">
      <span class="qcard-t"><?= h($cc['params_title']) ?></span>
      <span class="qcard-p"><?= h($cc['params_text']) ?></span>
      <span class="qcard-go"><?= h($cc['params_btn']) ?><?= icon('arrow') ?></span>
      <?php if ($pi): ?><img class="qcard-img" src="<?= h(img_url($pi, 400)) ?>" srcset="<?= h(img_url($pi, 400)) ?> 400w, <?= h(img_url($pi, 800)) ?> 800w" sizes="180px" alt="" fetchpriority="low"><?php else: ?><span class="qcard-img sliders" aria-hidden="true"><span style="--v:.32"><i></i></span><span style="--v:.68"><i></i></span><span style="--v:.4"><i></i></span></span><?php endif; ?>
    </a>
    <button class="qcard qcard-lead" type="button" data-lead="pick" data-title="Помощь с подбором">
      <span class="qcard-t"><?= h($cc['lead_title']) ?></span>
      <span class="qcard-p"><?= h($cc['lead_text']) ?></span>
      <span class="qcard-go"><?= h($cc['lead_btn']) ?><?= icon('arrow') ?></span>
      <?php if ($li): ?><img class="qcard-img" src="<?= h(img_url($li, 400)) ?>" srcset="<?= h(img_url($li, 400)) ?> 400w, <?= h(img_url($li, 800)) ?> 800w" sizes="180px" alt="" fetchpriority="low"><?php else: ?><svg class="qcard-img bubble" viewBox="0 0 120 100" aria-hidden="true"><path d="M20 12h80a14 14 0 0 1 14 14v38a14 14 0 0 1-14 14H52L30 94V78H20A14 14 0 0 1 6 64V26a14 14 0 0 1 14-14z"/><circle cx="40" cy="45" r="6"/><circle cx="60" cy="45" r="6"/><circle cx="80" cy="45" r="6"/></svg><?php endif; ?>
    </button>
  </div>
  <?php endif; ?>
</section>

<?php foreach ($c['blocks'] as $blk): if (empty($blk['on'])) continue; switch ($blk['id']):

case 'picker': ?>
<section class="wrap pick2" aria-labelledby="pick-title">
  <div class="pick2-card" data-reveal>
    <div class="pick2-head">
      <span class="pick2-ic" aria-hidden="true"><?= icon('car') ?></span>
      <div><h2 id="pick-title"><?= h($c['picker']['title']) ?></h2><p><?= h($c['picker']['lead']) ?></p></div>
      <div class="garage-inline" data-garage-inline hidden></div>
    </div>
    <?php partial('picker', ['id' => 'hp', 'compact' => true]); ?>
  </div>
</section>
<?php break;

case 'categories': if (!$cats) break; ?>
<section class="wrap hs" aria-labelledby="cat-title">
  <div class="hs-head"><h2 id="cat-title"><?= h($c['categories']['title']) ?></h2><a class="pill-link" href="/catalog">Весь каталог<?= icon('arrow') ?></a></div>
  <div class="tiles tiles5">
    <?php foreach ($cats as $k => $cat): $ph = $catPhoto($cat); ?>
      <a class="tile" href="/catalog/<?= h($cat['slug']) ?>" data-reveal style="--d:<?= $k * 60 ?>ms">
        <span class="tile-t"><?= h($cat['name']) ?><?= icon('arrow') ?></span>
        <span class="tile-n"><?= (int)$cat['cnt'] ?> <?= plural($cat['cnt'], 'товар', 'товара', 'товаров') ?></span>
        <?php if ($ph): ?><img class="tile-img<?= !empty($ph['alpha']) ? ' is-cut' : '' ?>" src="<?= h(img_url($ph, 800)) ?>" alt="" loading="lazy"><?php endif; ?>
      </a>
    <?php endforeach; ?>
    <a class="tile tile-pick" href="/podbor" data-reveal style="--d:<?= count($cats) * 60 ?>ms">
      <span class="tile-t">Подбор по автомобилю<?= icon('arrow') ?></span>
      <span class="tile-n">Марка, модель, год — покажем, что подходит</span>
      <?php $pimg = !empty($c['categories']['pick_img']) ? photo_row($c['categories']['pick_img']) : null; if ($pimg): ?><img class="tile-img is-cut tile-img-car" src="<?= h(img_url($pimg, 800)) ?>" alt="" loading="lazy"><?php else: ?><span class="tile-gauge" aria-hidden="true"><?= icon('car') ?></span><?php endif; ?>
    </a>
  </div>
</section>
<?php break;

case 'cards': /* карточки подбора и заявки стоят справа от баннера */ break;

case 'products':
  $list = array_values($all);
  usort($list, fn($a, $b) => [($b['wb_url'] || $b['ozon_url']) ? 1 : 0, $b['photos'] ? 1 : 0, -$a['sort']] <=> [($a['wb_url'] || $a['ozon_url']) ? 1 : 0, $a['photos'] ? 1 : 0, -$b['sort']]);
  $list = array_slice($list, 0, 8); if (!$list) break; ?>
<section class="wrap hs" aria-labelledby="prod-title">
  <div class="hs-head"><h2 id="prod-title"><?= h($c['products']['title']) ?></h2><a class="pill-link" href="/catalog">Все товары<?= icon('arrow') ?></a></div>
  <div class="grid"><?php foreach ($list as $p) partial('card', ['p' => $p]); ?></div>
</section>
<?php break;

case 'signs': $sg = $c['signs']; $sph = !empty($sg['photo_id']) ? photo_row($sg['photo_id']) : null; ?>
<section class="wrap hs" aria-labelledby="signs-title">
  <div class="signs" data-reveal>
    <?php if ($sph): ?><div class="signs-pic"><img src="<?= h(img_url($sph, 800)) ?>" srcset="<?= h(img_srcset($sph)) ?>" sizes="(max-width: 900px) 100vw, 520px" alt="Открытый капот, крышку держит газовый упор" loading="lazy"></div><?php endif; ?>
    <div class="signs-copy">
      <h2 id="signs-title"><?= h($sg['title']) ?></h2>
      <p class="signs-lead"><?= h($sg['text']) ?></p>
      <ol class="signs-list"><?php foreach ($sg['items'] as $i => $it): ?><li><span class="signs-n"><?= $i + 1 ?></span><span><b><?= h($it['title']) ?></b><?= h($it['text']) ?></span></li><?php endforeach; ?></ol>
      <?php if (!empty($sg['note'])): ?><p class="signs-note"><?= icon('info') ?><?= h($sg['note']) ?></p><?php endif; ?>
      <div class="signs-btns"><a class="btn btn-primary btn-lg" href="<?= h($sg['url']) ?>"><?= h($sg['btn']) ?><?= icon('arrow') ?></a><a class="btn btn-soft btn-lg" href="/podbor#quiz">Подбор за 30 секунд</a></div>
    </div>
  </div>
</section>
<?php break;

case 'advantages': ?>
<section class="wrap hs" aria-labelledby="adv-title">
  <div class="hs-head"><h2 id="adv-title"><?= h($c['advantages']['title']) ?></h2></div>
  <div class="why"><?php foreach ($c['advantages']['items'] as $i => $a): ?><div class="why-item" data-reveal style="--d:<?= $i * 60 ?>ms"><span class="why-ic"><?= icon($a['icon'] ?? 'check') ?></span><div><h3><?= h($a['title']) ?></h3><p><?= h($a['text']) ?></p></div></div><?php endforeach; ?></div>
</section>
<?php break;

case 'brands': $brands = array_filter(brands_all(), fn($b) => $b['cnt'] > 0); if (!$brands) break; ?>
<section class="wrap hs" aria-labelledby="brand-title">
  <div class="hs-head"><h2 id="brand-title"><?= h($c['brands']['title']) ?></h2></div>
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
<section class="wrap hs" aria-labelledby="how-title">
  <div class="hs-head"><h2 id="how-title"><?= h($c['howto']['title']) ?></h2><a class="pill-link" href="/how-to-buy">Подробнее<?= icon('arrow') ?></a></div>
  <ol class="steps"><?php foreach ($c['howto']['steps'] as $i => $st): ?><li class="step" data-reveal><span class="step-n"><?= $i + 1 ?></span><h3><?= h($st['title']) ?></h3><p><?= h($st['text']) ?></p></li><?php endforeach; ?></ol>
</section>
<?php break;

case 'about': $ab = $c['about']; $p1 = !empty($ab['photo_id']) ? photo_row($ab['photo_id']) : null; $p2 = !empty($ab['photo2_id']) ? photo_row($ab['photo2_id']) : null; ?>
<section class="wrap hs about3" aria-labelledby="about-title">
  <div class="about3-card" data-reveal>
    <?php if ($p1 || $p2): ?><div class="about3-pics"><?php foreach (array_filter([$p1, $p2]) as $ph): ?><img src="<?= h(img_url($ph, 800)) ?>" alt="" loading="lazy"><?php endforeach; ?></div><?php endif; ?>
    <h2 id="about-title"><?= h($ab['title']) ?></h2>
    <p><?= h($ab['text']) ?></p>
    <a class="link-arrow" href="/about"><?= h($ab['btn']) ?><?= icon('arrow') ?></a>
  </div>
  <div class="help3" data-reveal style="--d:80ms">
    <h2><?= h($c['help']['title']) ?></h2>
    <p><?= h($c['help']['text']) ?></p>
    <a class="help3-phone" href="<?= h(phone_href($s['phone'])) ?>" data-goal="phone_click"><?= icon('phone') ?><?= h($s['phone']) ?></a>
    <span class="help3-hours"><?= h($s['hours']) ?></span>
    <div class="help3-btns"><button class="btn btn-primary" type="button" data-lead="question" data-title="Задать вопрос">Связаться с нами</button><a class="btn btn-soft" href="/contacts">Как нас найти</a></div>
  </div>
</section>
<?php break;

case 'faq': $faq = content('faq')['items']; if (!$faq) break;
  jsonld(['@context' => 'https://schema.org', '@type' => 'FAQPage', 'mainEntity' => array_map(fn($x) => ['@type' => 'Question', 'name' => $x['q'], 'acceptedAnswer' => ['@type' => 'Answer', 'text' => $x['a']]], $faq)]); ?>
<section class="wrap hs" aria-labelledby="faq-title">
  <div class="hs-head"><h2 id="faq-title"><?= h($c['faq']['title']) ?></h2></div>
  <div class="faq faq-2"><?php foreach ($faq as $x): ?><details class="faq-item"><summary><span><?= h($x['q']) ?></span><i aria-hidden="true"></i></summary><div class="faq-a"><p><?= h($x['a']) ?></p></div></details><?php endforeach; ?></div>
</section>
<?php break;

case 'quiz': ?>
<section class="wrap hs"><?php partial('quiz', ['id' => 'quiz']); ?></section>
<?php break;
endswitch; endforeach; ?>

<section class="wrap hs" data-recent hidden aria-labelledby="recent-title">
  <div class="hs-head"><h2 id="recent-title">Вы смотрели</h2></div>
  <div class="recent" data-recent-list></div>
</section>
