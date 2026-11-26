<?php
/** @var array $p */
$b = product_brand($p);
$cat = product_category($p);
$ph0 = product_photo($p);
$url = product_url($p);
if (!preview_mode()) { try { q('UPDATE products SET views = views + 1 WHERE id = ?', [$p['id']]); } catch (Throwable $e) {} }

$title = $p['seo_title'] ?: $p['name'] . ' — артикул ' . $p['sku'] . ' | AVTO-M';
$desc = $p['seo_desc'] ?: md_plain($p['name'] . '. ' . ($p['short'] ?: '') . ' Совместимость: ' . compat_short($p, 4) . '.', 165);
meta('title', $title);
meta('desc', $desc);
meta('canonical', $url);
meta('og_type', 'product');
meta('og_title', $p['name']);
if ($ph0) meta('og_image', site_url(img_og($ph0)));
meta('body_class', 'page-product');

$attrs = [];
foreach (attributes_all() as $a) if (isset($p['attrs'][$a['code']])) $attrs[] = [$a['name'], attr_value($a, $p['attrs'][$a['code']])];
$oems = list_split($p['oem']);
$showBodies = (bool)array_filter($p['compat'], fn($c) => $c['bodies']);
$showEngines = (bool)array_filter($p['compat'], fn($c) => $c['engines']);
$byMake = [];
foreach ($p['compat'] as $c) $byMake[$c['make_name']][] = $c;
$hasMp = $p['wb_url'] || $p['ozon_url'];

$ld = ['@context' => 'https://schema.org', '@type' => 'Product', 'name' => $p['name'], 'sku' => $p['sku'], 'mpn' => $p['sku'],
  'description' => md_plain($p['description'] ?: $p['short'], 500), 'url' => site_url($url), 'category' => $cat['name'] ?? ''];
if ($b) $ld['brand'] = ['@type' => 'Brand', 'name' => $b['name']];
if ($p['photos']) $ld['image'] = array_map(fn($x) => site_url(img_og($x)), $p['photos']);
foreach ($attrs as [$n, $v]) $ld['additionalProperty'][] = ['@type' => 'PropertyValue', 'name' => $n, 'value' => $v];
if ($p['price_from']) $ld['offers'] = ['@type' => 'AggregateOffer', 'priceCurrency' => 'RUB', 'lowPrice' => (int)$p['price_from'], 'offerCount' => ($p['wb_url'] ? 1 : 0) + ($p['ozon_url'] ? 1 : 0) ?: 1, 'availability' => 'https://schema.org/InStock'];
jsonld($ld);
?>
<div class="wrap">
  <?= breadcrumbs(array_filter([['Каталог', '/catalog'], $cat ? [$cat['name'], '/catalog/' . $cat['slug']] : null, [$p['name'], null]])) ?>
  <div class="pp" data-pp="<?= (int)$p['id'] ?>">
    <div class="pp-head">
      <h1><?= h($p['name']) ?></h1>
      <p class="pp-sub">
        <?php if ($b): ?><a href="/brand/<?= h($b['slug']) ?>"><?= h($b['name']) ?></a><span>·</span><?php endif; ?>
        Артикул <b><?= h($p['sku']) ?></b>
        <button class="copy" type="button" data-copy="<?= h($p['sku']) ?>" aria-label="Скопировать артикул"><?= icon('copy') ?></button>
      </p>
    </div>

    <div class="pp-gallery panel" data-gallery>
      <div class="gal-main">
        <?php if ($p['photos']): foreach ($p['photos'] as $i => $ph): ?>
          <button class="gal-slide<?= $i ? '' : ' is-on' ?>" type="button" data-zoom="<?= $i ?>" aria-label="Увеличить фото <?= $i + 1 ?>" <?= $i ? 'hidden' : '' ?>>
            <?= picture($ph, $ph['alt'] ?: $p['name'], '(max-width: 900px) 100vw, 560px', $i === 0) ?>
          </button>
        <?php endforeach; else: ?>
          <?= picture(null, $p['name']) ?>
        <?php endif; ?>
        <?php if ($p['badge']): ?><span class="tag tag-accent"><?= h($p['badge']) ?></span><?php endif; ?>
        <?php if (count($p['photos']) > 1): ?>
          <button class="gal-nav prev" type="button" data-gal="-1" aria-label="Предыдущее фото"><?= icon('arrow-left') ?></button>
          <button class="gal-nav next" type="button" data-gal="1" aria-label="Следующее фото"><?= icon('arrow') ?></button>
          <span class="gal-count" aria-hidden="true"><b>1</b> / <?= count($p['photos']) ?></span>
        <?php endif; ?>
      </div>
      <?php if (count($p['photos']) > 1): ?>
      <div class="gal-thumbs" role="tablist" aria-label="Фото товара">
        <?php foreach ($p['photos'] as $i => $ph): ?>
          <button class="gal-thumb<?= $i ? '' : ' is-on' ?>" type="button" role="tab" aria-selected="<?= $i ? 'false' : 'true' ?>" data-thumb="<?= $i ?>" aria-label="Фото <?= $i + 1 ?>"><img src="<?= h(img_url($ph, 400)) ?>" alt="" width="80" height="107" loading="lazy"></button>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
    </div>

    <aside class="pp-buy panel" aria-label="Покупка">
      <div class="fitbox" data-fitbox>
        <div class="fitbox-state" data-fit-state>
          <span class="fitbox-ic"><?= icon('car') ?></span>
          <div><b>Подойдёт ли к вашей машине?</b><small>Выберите автомобиль — проверим по данным производителя</small></div>
        </div>
        <button class="btn btn-soft btn-block" type="button" data-fit-open><?= icon('car') ?>Проверить мой автомобиль</button>
        <div class="fitbox-form" data-fit-form hidden>
          <?php partial('picker', ['id' => 'pf', 'embedded' => true]); ?>
          <button class="btn btn-primary btn-block" type="button" data-fit-check>Проверить</button>
        </div>
      </div>
      <script type="application/json" data-fit-compat><?= json_encode(array_map(fn($c) => ['mk' => $c['make'], 'md' => $c['model'], 'yf' => $c['year_from'], 'yt' => $c['year_to'], 'b' => $c['bodies'], 'e' => $c['engines']], $p['compat']), JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) ?></script>

      <dl class="specs">
        <?php foreach (array_slice($attrs, 0, 4) as [$n, $v]): ?><div><dt><?= h($n) ?></dt><dd><?= h($v) ?></dd></div><?php endforeach; ?>
        <?php if ($p['kit']): ?><div><dt>В комплекте</dt><dd><?= h($p['kit']) ?></dd></div><?php endif; ?>
        <div><dt>Наличие</dt><dd><?= h(stock_label($p['stock'])) ?></dd></div>
        <?php if ($p['price_from']): ?><div><dt>Цена на маркетплейсе</dt><dd>от <?= h(fmt_price($p['price_from'])) ?></dd></div><?php endif; ?>
      </dl>

      <?php if ($hasMp): ?>
        <p class="buy-h">Купить на маркетплейсе</p>
        <div class="mp-row mp-row-lg"><?= mp_buttons($p, 'btn-lg') ?></div>
        <p class="buy-note"><?= icon('info') ?>Откроется карточка этой детали. Оплата и доставка — по правилам площадки.</p>
      <?php else: ?>
        <div class="note"><?= icon('info') ?><div>Деталь скоро появится на маркетплейсах. Пока можно оставить заявку — уточним наличие и перезвоним.</div></div>
      <?php endif; ?>
      <div class="buy-btns">
        <button class="btn <?= $hasMp ? 'btn-soft' : 'btn-primary' ?> btn-lg" type="button" data-lead="availability" data-product-id="<?= (int)$p['id'] ?>" data-title="Уточнить наличие"><?= icon('chat') ?>Уточнить наличие</button>
        <div class="buy-row">
          <button class="btn btn-soft" type="button" data-cart="<?= (int)$p['id'] ?>" aria-pressed="false"><?= icon('plus') ?><span>В корзину</span></button>
          <button class="btn btn-soft" type="button" data-compare="<?= (int)$p['id'] ?>" aria-pressed="false"><?= icon('compare') ?><span>Сравнить</span></button>
        </div>
      </div>
    </aside>

    <div class="pp-info">
      <?php if ($p['short']): ?><p class="pp-short"><?= h($p['short']) ?></p><?php endif; ?>

      <section class="panel pp-sec" aria-labelledby="sec-compat">
        <h2 id="sec-compat">Совместимость</h2>
        <?php if ($byMake): ?>
        <div class="table-wrap">
          <table class="ctable">
            <thead><tr><th scope="col">Автомобиль</th><th scope="col">Годы</th><?php if ($showBodies): ?><th scope="col">Кузов</th><?php endif; ?><?php if ($showEngines): ?><th scope="col">Модификация, л</th><?php endif; ?></tr></thead>
            <tbody>
              <?php foreach ($byMake as $mk => $rows): foreach ($rows as $c): ?>
                <tr>
                  <th scope="row"><a href="/avto/<?= h($c['make']) ?>/<?= h($c['model']) ?>"><?= h($mk . ' ' . $c['model_name']) ?></a></th>
                  <td data-l="Годы"><?= h(years_label($c['year_from'], $c['year_to']) ?: '—') ?></td>
                  <?php if ($showBodies): ?><td data-l="Кузов"><?= h($c['bodies'] ? implode(', ', $c['bodies']) : 'уточняйте') ?></td><?php endif; ?>
                  <?php if ($showEngines): ?><td data-l="Двигатель"><?= h($c['engines'] ? implode(', ', $c['engines']) : 'уточняйте') ?></td><?php endif; ?>
                </tr>
              <?php endforeach; endforeach; ?>
            </tbody>
          </table>
        </div>
        <?php else: ?><p class="muted">Совместимость уточняйте у менеджера.</p><?php endif; ?>
        <p class="hint"><?= icon('info') ?>Не уверены? Напишите VIN-номер в заявке — проверим применимость.</p>
      </section>

      <?php if ($p['description']): ?>
      <section class="panel pp-sec" aria-labelledby="sec-desc">
        <h2 id="sec-desc">Описание</h2>
        <div class="prose"><?= md($p['description']) ?></div>
      </section>
      <?php endif; ?>

      <section class="panel pp-sec" aria-labelledby="sec-specs">
        <h2 id="sec-specs">Характеристики</h2>
        <dl class="specs specs-full">
          <div><dt>Артикул</dt><dd><?= h($p['sku']) ?></dd></div>
          <?php if ($b): ?><div><dt>Производитель</dt><dd><?= h($b['name']) ?></dd></div><?php endif; ?>
          <?php if ($cat): ?><div><dt>Категория</dt><dd><?= h($cat['name']) ?></dd></div><?php endif; ?>
          <?php foreach ($attrs as [$n, $v]): ?><div><dt><?= h($n) ?></dt><dd><?= h($v) ?></dd></div><?php endforeach; ?>
          <?php if ($p['kit']): ?><div><dt>В комплекте</dt><dd><?= h($p['kit']) ?></dd></div><?php endif; ?>
        </dl>
        <?php if ($oems): ?>
          <h3 class="oem-h">Оригинальные номера</h3>
          <ul class="oems">
            <?php foreach ($oems as $o): ?><li><button type="button" class="oem" data-copy="<?= h($o) ?>" title="Скопировать"><?= h($o) ?><?= icon('copy') ?></button></li><?php endforeach; ?>
          </ul>
        <?php endif; ?>
      </section>

      <?php if ($b): $facts = json_decode($b['facts'] ?: '[]', true); ?>
      <section class="panel pp-sec pp-brand" aria-labelledby="sec-brand">
        <h2 id="sec-brand">О производителе <?= h($b['name']) ?></h2>
        <?php if ($facts): ?><div class="brand-facts brand-facts-row"><?php foreach ($facts as [$k, $v]): ?><span><small><?= h($k) ?></small><b><?= h($v) ?></b></span><?php endforeach; ?></div><?php endif; ?>
        <p><?= h(md_plain($b['description'], 260)) ?></p>
        <a class="link-arrow" href="/brand/<?= h($b['slug']) ?>">Подробнее о марке<?= icon('arrow') ?></a>
      </section>
      <?php endif; ?>
    </div>
  </div>

  <?php $rel = related_products($p); if ($rel): ?>
  <section class="section" aria-labelledby="rel-title">
    <div class="section-head"><h2 id="rel-title">Ещё для этих автомобилей</h2></div>
    <div class="grid"><?php foreach ($rel as $x) partial('card', ['p' => $x]); ?></div>
  </section>
  <?php endif; ?>
</div>

<div class="buybar" data-buybar>
  <?php if ($hasMp): ?><?= mp_buttons($p) ?><?php else: ?><button class="btn btn-primary" type="button" data-lead="availability" data-product-id="<?= (int)$p['id'] ?>" data-title="Уточнить наличие"><?= icon('chat') ?>Уточнить наличие</button><?php endif; ?>
  <button class="btn btn-soft btn-square" type="button" data-cart="<?= (int)$p['id'] ?>" aria-pressed="false" aria-label="В корзину"><?= icon('plus') ?></button>
</div>

<div class="lightbox" data-lightbox hidden role="dialog" aria-modal="true" aria-label="Фото товара">
  <button class="icon-btn lb-close" type="button" data-lb-close aria-label="Закрыть"><?= icon('close') ?></button>
  <button class="icon-btn lb-nav prev" type="button" data-lb="-1" aria-label="Предыдущее"><?= icon('arrow-left') ?></button>
  <img alt="" data-lb-img>
  <button class="icon-btn lb-nav next" type="button" data-lb="1" aria-label="Следующее"><?= icon('arrow') ?></button>
  <script type="application/json" data-lb-list><?= json_encode(array_map(fn($x) => img_url($x, 1400), $p['photos']), JSON_UNESCAPED_SLASHES) ?></script>
</div>
