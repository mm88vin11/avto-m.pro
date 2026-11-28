<?php
/**
 * Карточка товара в выдаче. $p — товар, $view — grid|list, $eager — первая строка без ленивой загрузки.
 * Строки карточки фиксированной высоты (название — до трёх строк, остальное — по одной), поэтому
 * у соседних карточек цена, артикул и кнопки стоят на одной линии.
 */
$view = $view ?? 'grid';
$b = product_brand($p);
$ph = product_photo($p);
$url = product_url($p);
$match = $p['match'] ?? null;
$spec = [];
foreach (attributes_all() as $a) {
  if (!$a['in_card'] || !isset($p['attrs'][$a['code']]) || $a['type'] !== 'number') continue;
  $spec[] = attr_value($a, $p['attrs'][$a['code']]);
  if (count($spec) >= 2) break;
}
// совместимость коротко: первая модель с годами и сколько ещё моделей
$models = []; foreach ($p['compat'] as $c) $models[$c['make_name'] . ' ' . $c['model_name']] ??= years_label($c['year_from'], $c['year_to']);
$fit = $models ? array_key_first($models) . ($models[array_key_first($models)] ? ' · ' . $models[array_key_first($models)] : '') . (count($models) > 1 ? ' и ещё ' . (count($models) - 1) : '') : 'Совместимость уточняйте';
// марка уже видна на фото, поэтому в названии карточки её не повторяем — название короче и помещается целиком
$title = $b ? trim(preg_replace('~\s{2,}~u', ' ', str_ireplace($b['name'], '', $p['name']))) : $p['name'];
$badge = $p['badge'] ? mb_strtolower($p['badge']) : '';
$bc = $badge === '' ? '' : (str_contains($badge, 'нов') ? ' b-new' : (str_contains($badge, 'акци') ? ' b-sale' : (str_contains($badge, 'хит') ? ' b-hit' : '')));
?>
<article class="pc<?= $view === 'list' ? ' pc-row' : '' ?>" data-product="<?= (int)$p['id'] ?>">
  <a class="pc-img" href="<?= h($url) ?>" tabindex="-1" aria-hidden="true">
    <?= picture($ph, $p['name'], $view === 'list' ? '200px' : '(max-width: 599px) 46vw, (max-width: 1099px) 30vw, 240px', !empty($eager)) ?>
    <?php if ($p['badge']): ?><span class="pc-badge<?= $bc ?>"><?= h($p['badge']) ?></span><?php endif; ?>
    <?php if ($b): ?><span class="pc-brand"><?= h($b['name']) ?></span><?php endif; ?>
  </a>
  <button class="pc-cmp" type="button" data-compare="<?= (int)$p['id'] ?>" aria-pressed="false" aria-label="Добавить к сравнению"><?= icon('compare') ?></button>
  <div class="pc-body">
    <?php if ($p['price_from']): ?><p class="pc-price">от <?= h(fmt_price($p['price_from'])) ?></p><?php endif; ?>
    <h3 class="pc-title"><a href="<?= h($url) ?>" title="<?= h($p['name']) ?>"><?= h($title) ?></a></h3>
    <p class="pc-meta">Арт. <?= h($p['sku']) ?></p>
    <?php if ($match === 'yes'): ?><p class="pc-fit ok"><?= icon('check-circle') ?>Подходит к вашей машине</p>
    <?php elseif ($match === 'check'): ?><p class="pc-fit warn"><?= icon('alert') ?>Уточните кузов</p>
    <?php else: ?><p class="pc-fit"><?= icon('car') ?><span><?= h($fit) ?></span></p><?php endif; ?>
    <p class="pc-spec"><?= h(implode(' · ', $spec)) ?></p>
    <div class="pc-btns">
      <?php if ($p['wb_url'] || $p['ozon_url']): ?>
        <?php if ($p['wb_url']): ?><a class="btn btn-wb" href="/go/wb/<?= (int)$p['id'] ?>" target="_blank" rel="nofollow noopener" data-goal="mp_wb" data-mp="wb"><span class="mp-l">Wildberries</span><span class="mp-s">WB</span></a><?php endif; ?>
        <?php if ($p['ozon_url']): ?><a class="btn btn-ozon" href="/go/ozon/<?= (int)$p['id'] ?>" target="_blank" rel="nofollow noopener" data-goal="mp_ozon" data-mp="ozon">Ozon</a><?php endif; ?>
      <?php else: ?>
        <button class="btn btn-line" type="button" data-lead="availability" data-product-id="<?= (int)$p['id'] ?>" data-title="Уточнить наличие">Узнать наличие</button>
      <?php endif; ?>
    </div>
    <button class="pc-cart" type="button" data-cart="<?= (int)$p['id'] ?>" aria-pressed="false"><?= icon('plus') ?><span>В корзину</span></button>
  </div>
</article>
