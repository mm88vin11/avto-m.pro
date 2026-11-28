<?php
/**
 * Главная: слева каталог, фото, преимущества и «Есть вопросы?»; справа карусель баннеров, плитки каталога и «О компании».
 * Ниже — блоки в порядке из админки (Страницы → Главная → «Порядок и видимость блоков»).
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
$slides = array_values(array_filter($c['slides'], fn($x) => !empty($x['banner_id']) || trim($x['title'] ?? '') !== ''));
$have = array_column($c['blocks'], 'id');
foreach (array_keys(home_block_names()) as $bn) if (!in_array($bn, $have, true)) $c['blocks'][] = ['id' => $bn, 'on' => !in_array($bn, ['quiz', 'howto'], true)];
?>
<?php
$blkOn = []; foreach ($c['blocks'] as $bb) $blkOn[$bb['id']] = !empty($bb['on']);
$cc = $c['cards']; $pi = !empty($cc['params_img']) ? photo_row($cc['params_img']) : null; $li = !empty($cc['lead_img']) ? photo_row($cc['lead_img']) : null;
$pimg = !empty($c['categories']['pick_img']) ? photo_row($c['categories']['pick_img']) : null;
$sec = (int)($c['slider_sec'] ?? 3);
// плитка каталога: картинка, название в две строки, одна короткая подпись — у всех плиток одинаковая высота
$tile = function ($tag, $attrs, $title, $sub, $pic) {
  return '<' . $tag . ' class="cc" ' . $attrs . '><span class="cc-pic">' . $pic . '</span><span class="cc-t">' . h($title) . '</span><span class="cc-n">' . h($sub) . '</span></' . $tag . '>';
};
$tileImg = fn($ph, $w = 400) => $ph ? '<img class="' . (!empty($ph['alpha']) ? 'is-cut' : 'is-photo') . '" src="' . h(img_url($ph, $w)) . '" srcset="' . h(img_url($ph, 400)) . ' 400w, ' . h(img_url($ph, 800)) . ' 800w" sizes="(max-width: 599px) 45vw, 200px" alt="" loading="lazy" decoding="async">' : '';
?>
<div class="wrap hm">
  <aside class="hm-side" aria-label="Каталог и помощь">
    <nav class="sb-cat" aria-label="Каталог продукции">
      <ul>
        <?php foreach ($cats as $cat): ?><li><a href="/catalog/<?= h($cat['slug']) ?>"><span><?= h($cat['name']) ?></span><small><?= (int)$cat['cnt'] ?></small><?= icon('chevron-r') ?></a></li><?php endforeach; ?>
        <li><a href="/podbor"><span>Подбор по автомобилю</span><?= icon('chevron-r') ?></a></li>
        <li><a href="/catalog"><span>Весь каталог</span><?= icon('chevron-r') ?></a></li>
      </ul>
    </nav>
    <?php if (!empty($blkOn['advantages'])): ?>
    <section class="sb-adv" aria-labelledby="sb-adv-t">
      <h2 class="sb-h" id="sb-adv-t">Преимущества</h2>
      <ul><?php foreach ($c['advantages']['items'] as $a): ?><li><span class="sb-adv-ic"><?= icon3d($a['icon'] ?? 'check', 44) ?></span><span><b><?= h($a['title']) ?></b><small><?= h($a['text']) ?></small></span></li><?php endforeach; ?></ul>
    </section>
    <?php endif; ?>
    <section class="sb-help" aria-labelledby="sb-help-t">
      <img class="sb-help-art" src="<?= h(site_img('ui-support')) ?>" alt="" width="84" height="84" loading="lazy">
      <h2 id="sb-help-t">Есть вопросы?</h2>
      <p><?= h($c['help']['text']) ?></p>
      <a class="sb-help-phone" href="<?= h(phone_href($s['phone'])) ?>" data-goal="phone_click"><?= h($s['phone']) ?></a>
      <button class="btn btn-white" type="button" data-lead="question" data-title="Задать вопрос">Связаться с нами<?= icon('chevron-r') ?></button>
    </section>
  </aside>

  <div class="hm-main">
  <?php if ($slides): $n = count($slides); $allBanners = !array_filter($slides, fn($x) => empty($x['banner_id'])); ?>
  <?php if (!empty($slides[0]['banner_id'])): ?><h1 class="sr"><?= h($c['seo_h1'] ?: 'Автозапчасти с подбором по автомобилю') ?></h1><?php endif; ?>
  <div class="bnr<?= $allBanners ? ' is-banners' : '' ?>" role="region" aria-roledescription="карусель" aria-label="Предложения магазина" data-slider style="--sec: <?= max(1, $sec) ?>s"<?= $sec ? '' : ' data-manual' ?>>
    <div class="bnr-view">
      <div class="bnr-track" data-slider-track aria-live="off">
      <?php foreach ($slides as $i => $sl):
        $label = ($i + 1) . ' из ' . $n;
        $banner = !empty($sl['banner_id']) ? photo_row($sl['banner_id']) : null;
        if ($banner):
          $bm = !empty($sl['banner_m_id']) ? photo_row($sl['banner_m_id']) : null; ?>
        <a class="slide slide-banner<?= $i ? '' : ' is-on' ?>" href="<?= h($sl['url'] ?: '/catalog') ?>" role="group" aria-roledescription="слайд" aria-label="<?= $label ?>"<?= $i ? ' aria-hidden="true" tabindex="-1"' : '' ?>>
          <picture>
            <?php if ($bm): ?><source media="(max-width: 699px)" srcset="<?= h(img_srcset($bm)) ?>" sizes="100vw"><?php endif; ?>
            <img src="<?= h(img_url($banner, 1400)) ?>" srcset="<?= h(img_srcset($banner)) ?>" sizes="(max-width: 999px) 100vw, (max-width: 1239px) 66vw, 920px" width="<?= (int)$banner['w'] ?>" height="<?= (int)$banner['h'] ?>" alt="<?= h($sl['title']) ?>"<?= $i ? ' loading="lazy" decoding="async"' : ' fetchpriority="high"' ?>>
          </picture>
        </a>
        <?php continue; endif;
        $bg = !empty($sl['bg_id']) ? photo_row($sl['bg_id']) : null;
        $bgm = !empty($sl['bg_m_id']) ? photo_row($sl['bg_m_id']) : null;
        $img = !empty($sl['img_id']) ? photo_row($sl['img_id']) : null;
        if (!$img && !empty($sl['cat']) && isset($catBySlug[$sl['cat']])) $img = $catPhoto($catBySlug[$sl['cat']]);
        $chips = array_values(array_filter(array_map('trim', preg_split("~\r?\n~", (string)($sl['chips'] ?? ''))), 'strlen'));
        $theme = in_array($sl['theme'] ?? '', ['blue', 'light', 'navy', 'white'], true) ? $sl['theme'] : 'blue'; ?>
        <div class="slide t-<?= $theme ?><?= $i ? '' : ' is-on' ?>" role="group" aria-roledescription="слайд" aria-label="<?= $label ?>"<?= $i ? ' aria-hidden="true"' : '' ?>>
          <?php if ($bg): ?><picture class="slide-bgp"><?php if ($bgm): ?><source media="(max-width: 699px)" srcset="<?= h(img_srcset($bgm)) ?>" sizes="100vw"><?php endif; ?><img class="slide-bg" src="<?= h(img_url($bg, 1400)) ?>" srcset="<?= h(img_srcset($bg)) ?>" sizes="(max-width: 1240px) 100vw, 900px" alt=""<?= $i ? ' loading="lazy"' : ' fetchpriority="high"' ?>></picture><?php else: ?><span class="slide-rays" aria-hidden="true"></span><?php endif; ?>
          <div class="slide-copy">
            <?= $i === 0 ? '<h1 class="slide-title">' : '<p class="slide-title">' ?><span class="ln"><span><?= h($sl['title']) ?></span></span><?php if (!empty($sl['sub'])): ?><span class="ln slide-sub"><span><?= h($sl['sub']) ?></span></span><?php endif; ?><?= $i === 0 ? '</h1>' : '</p>' ?>
            <?php if (!empty($sl['text'])): ?><p class="slide-text"><?= h($sl['text']) ?></p><?php endif; ?>
            <?php if ($chips): ?><ul class="slide-chips"><?php foreach (array_slice($chips, 0, 3) as $ch): ?><li><?= icon('check') ?><?= h($ch) ?></li><?php endforeach; ?></ul><?php endif; ?>
            <div class="slide-btns">
              <?php if (!empty($sl['btn'])): ?><a class="btn slide-btn" href="<?= h($sl['url']) ?>"<?= $i ? ' tabindex="-1"' : '' ?>><?= h($sl['btn']) ?><?= icon('chevron-r') ?></a><?php endif; ?>
              <?php if (!empty($sl['btn2'])): ?><a class="btn slide-btn2" href="<?= h($sl['url2'] ?: '/catalog') ?>"<?= $i ? ' tabindex="-1"' : '' ?>><?= h($sl['btn2']) ?></a><?php endif; ?>
            </div>
          </div>
          <?php if ($img): ?><div class="slide-art" aria-hidden="true"><img class="slide-obj<?= !empty($img['alpha']) ? ' is-cut' : '' ?>" src="<?= h(img_url($img, 800)) ?>" srcset="<?= h(img_url($img, 800)) ?> 800w, <?= h(img_url($img, 1400)) ?> 1400w" sizes="(max-width: 699px) 80vw, 480px" alt=""<?= $i ? ' loading="lazy"' : ' fetchpriority="high"' ?>></div><?php endif; ?>
        </div>
      <?php endforeach; ?>
      </div>
      <?php if ($n > 1): ?>
      <button class="bnr-arrow bnr-prev" type="button" data-slide-step="-1" aria-label="Предыдущий слайд"><?= icon('arrow-left') ?></button>
      <button class="bnr-arrow bnr-next" type="button" data-slide-step="1" aria-label="Следующий слайд"><?= icon('arrow') ?></button>
      <?php endif; ?>
    </div>
    <?php if ($n > 1): ?>
    <div class="bnr-bars" role="tablist" aria-label="Слайды">
      <?php foreach ($slides as $i => $sl): ?><button type="button" role="tab" aria-selected="<?= $i ? 'false' : 'true' ?>" aria-label="<?= h(($i + 1) . '. ' . $sl['title']) ?>" data-slide="<?= $i ?>"><i></i></button><?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>
  <?php endif; ?>

  <?php if (!empty($blkOn['categories']) || !empty($blkOn['cards'])): ?>
  <section class="hm-cats" aria-labelledby="cat-title">
    <div class="sec-h"><h2 id="cat-title"><?= h($c['categories']['title']) ?></h2><a class="pill-link" href="/catalog">Весь каталог<?= icon('arrow') ?></a></div>
    <div class="cc-grid">
      <?php if (!empty($blkOn['categories'])):
        foreach ($cats as $cat) echo $tile('a', 'href="/catalog/' . h($cat['slug']) . '"', $cat['name'], $cat['cnt'] . ' ' . plural($cat['cnt'], 'товар', 'товара', 'товаров'), $tileImg($catPhoto($cat)));
        echo $tile('a', 'href="/podbor"', 'Подбор по автомобилю', 'Марка, модель, год', $pimg ? $tileImg($pimg) : '<span class="cc-3d">' . icon3d('car', 160) . '</span>');
      endif;
      if (!empty($blkOn['cards'])) {
        echo $tile('a', 'href="' . h($cc['params_url']) . '"', $cc['params_title'], $cc['params_text'], $pi ? $tileImg($pi) : '<span class="cc-3d">' . icon3d('filter', 160) . '</span>');
        echo $tile('button', 'type="button" data-lead="pick" data-title="Помощь с подбором"', $cc['lead_title'], $cc['lead_text'], $li ? $tileImg($li) : '<span class="cc-3d">' . icon3d('chat', 160) . '</span>');
      } ?>
    </div>
  </section>
  <?php endif; ?>
  </div>
</div>

<?php foreach ($c['blocks'] as $blk): if (empty($blk['on'])) continue; switch ($blk['id']):

case 'picker': ?>
<section class="wrap band band-soft pick2" aria-labelledby="pick-title">
  <div class="pick2-head">
    <span class="pick2-ic" aria-hidden="true"><?= icon3d('car', 56) ?></span>
    <div><h2 id="pick-title"><?= h($c['picker']['title']) ?></h2><p><?= h($c['picker']['lead']) ?></p></div>
    <div class="garage-inline" data-garage-inline hidden></div>
  </div>
  <?php partial('picker', ['id' => 'hp', 'compact' => true]); ?>
</section>
<?php break;

case 'categories': case 'cards': /* в верхней сетке */ break;

case 'advantages': /* на компьютере — в левой колонке, на телефоне — здесь */ ?>
<section class="wrap band band-adv" aria-labelledby="adv-title">
  <div class="sec-h"><h2 id="adv-title"><?= h($c['advantages']['title']) ?></h2></div>
  <ul class="adv2"><?php foreach ($c['advantages']['items'] as $a): ?><li><span class="adv2-ic"><?= icon3d($a['icon'] ?? 'check', 48) ?></span><b><?= h($a['title']) ?></b><small><?= h($a['text']) ?></small></li><?php endforeach; ?></ul>
</section>
<?php break;

case 'about': $ab = $c['about']; $bset = site_img_srcset('about-building'); ?>
<section class="wrap band about2" aria-labelledby="about-title">
  <div class="about2-copy">
    <h2 id="about-title"><?= h($ab['title']) ?></h2>
    <p class="about2-motto"><?= h($s['tagline']) ?></p>
    <p class="about2-text"><?= h($ab['text']) ?></p>
    <a class="btn btn-primary" href="/about"><?= h($ab['btn']) ?><?= icon('chevron-r') ?></a>
  </div>
  <div class="about2-pic"><img src="<?= h(site_img('about-building')) ?>"<?= $bset ? ' srcset="' . h($bset) . '" sizes="(max-width: 899px) 92vw, 640px"' : '' ?> width="1400" height="731" alt="Магазин AVTO-M" loading="lazy" decoding="async"></div>
</section>
<?php break;

case 'products':
  $list = array_values($all);
  usort($list, fn($a, $b) => [($b['wb_url'] || $b['ozon_url']) ? 1 : 0, $b['photos'] ? 1 : 0, -$a['sort']] <=> [($a['wb_url'] || $a['ozon_url']) ? 1 : 0, $a['photos'] ? 1 : 0, -$b['sort']]);
  $list = array_slice($list, 0, 8); if (!$list) break; ?>
<section class="wrap band" aria-labelledby="prod-title">
  <div class="sec-h"><h2 id="prod-title"><?= h($c['products']['title']) ?></h2><a class="pill-link" href="/catalog">Все товары<?= icon('arrow') ?></a></div>
  <div class="grid grid-home"><?php foreach ($list as $p) partial('card', ['p' => $p]); ?></div>
</section>
<?php break;

case 'signs': $sg = $c['signs']; ?>
<section class="wrap band" aria-labelledby="signs-title">
  <div class="sec-h"><h2 id="signs-title"><?= h($sg['title']) ?></h2></div>
  <ol class="signs-list"><?php foreach ($sg['items'] as $i => $it): ?><li><span class="signs-n"><?= $i + 1 ?></span><span><b><?= h($it['title']) ?></b><?= h($it['text']) ?></span></li><?php endforeach; ?></ol>
  <div class="band-btns"><a class="btn btn-primary" href="<?= h($sg['url']) ?>"><?= h($sg['btn']) ?><?= icon('arrow') ?></a></div>
</section>
<?php break;

case 'brands': $brands = array_filter(brands_all(), fn($b) => $b['cnt'] > 0); if (!$brands) break; ?>
<section class="wrap band" aria-labelledby="brand-title">
  <div class="sec-h"><h2 id="brand-title"><?= h($c['brands']['title']) ?></h2></div>
  <div class="brands">
    <?php foreach ($brands as $b): $facts = json_decode($b['facts'] ?: '[]', true); $logo = photo_row($b['logo_photo_id']); ?>
      <a class="brand-card" href="/brand/<?= h($b['slug']) ?>">
        <span class="brand-mark"><?php if ($logo): ?><img src="<?= h(img_url($logo, 400)) ?>" alt="<?= h($b['name']) ?>" loading="lazy"><?php else: ?><?= h($b['name']) ?><?php endif; ?></span>
        <span class="brand-tag"><?= h($b['tagline']) ?></span>
        <?php if ($facts): ?><span class="brand-facts"><?php foreach (array_slice($facts, 0, 4) as [$k, $v]): ?><span><small><?= h($k) ?></small><b><?= h(preg_replace("~ (°|мм|Н|лет|год|мл)\b~u", "\u{00A0}$1", $v)) ?></b></span><?php endforeach; ?></span><?php endif; ?>
        <span class="link-arrow"><?= (int)$b['cnt'] ?> <?= plural($b['cnt'], 'товар', 'товара', 'товаров') ?><?= icon('arrow') ?></span>
      </a>
    <?php endforeach; ?>
  </div>
</section>
<?php break;

case 'howto': ?>
<section class="wrap band" aria-labelledby="how-title">
  <div class="sec-h"><h2 id="how-title"><?= h($c['howto']['title']) ?></h2><a class="pill-link" href="/how-to-buy">Подробнее<?= icon('arrow') ?></a></div>
  <ol class="steps"><?php foreach ($c['howto']['steps'] as $i => $st): ?><li class="step"><span class="step-n"><?= $i + 1 ?></span><h3><?= h($st['title']) ?></h3><p><?= h($st['text']) ?></p></li><?php endforeach; ?></ol>
</section>
<?php break;

case 'faq': $faq = content('faq')['items']; if (!$faq) break;
  jsonld(['@context' => 'https://schema.org', '@type' => 'FAQPage', 'mainEntity' => array_map(fn($x) => ['@type' => 'Question', 'name' => $x['q'], 'acceptedAnswer' => ['@type' => 'Answer', 'text' => $x['a']]], $faq)]); ?>
<section class="wrap band" aria-labelledby="faq-title">
  <div class="sec-h"><h2 id="faq-title"><?= h($c['faq']['title']) ?></h2></div>
  <?php partial('faq', ['items' => $faq]); ?>
</section>
<?php break;

case 'quiz': ?>
<section class="wrap band band-flat"><?php partial('quiz', ['id' => 'quiz']); ?></section>
<?php break;
endswitch; endforeach; ?>

<section class="wrap band band-help" aria-labelledby="help-m-title">
  <img src="<?= h(site_img('ui-support')) ?>" alt="" width="84" height="84" loading="lazy">
  <div>
    <h2 id="help-m-title">Есть вопросы?</h2>
    <p><?= h($c['help']['text']) ?></p>
  </div>
  <div class="band-btns">
    <a class="btn btn-white" href="<?= h(phone_href($s['phone'])) ?>" data-goal="phone_click"><?= icon('phone') ?><?= h($s['phone']) ?></a>
    <button class="btn btn-glass" type="button" data-lead="question" data-title="Задать вопрос">Написать нам</button>
  </div>
</section>

<section class="wrap band" data-recent hidden aria-labelledby="recent-title">
  <div class="sec-h"><h2 id="recent-title">Вы смотрели</h2></div>
  <div class="recent" data-recent-list></div>
</section>
