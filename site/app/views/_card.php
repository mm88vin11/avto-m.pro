<?php
/** Карточка товара в выдаче. $p — товар, $view — grid|list, $eager — первая строка без ленивой загрузки */
$view = $view ?? 'grid';
$b = product_brand($p);
$ph = product_photo($p);
$url = product_url($p);
$match = $p['match'] ?? null;
$rowIcons = ['length' => 'ruler', 'force' => 'gauge', 'volume' => 'kit'];
$rows = [];
foreach (attributes_all() as $a) {
  if (!$a['in_card'] || !isset($p['attrs'][$a['code']]) || $a['type'] !== 'number') continue;
  $rows[] = [$rowIcons[$a['code']] ?? 'hash', $a['name'] === 'Выталкивающая сила' ? 'Усилие' : $a['name'], attr_value($a, $p['attrs'][$a['code']])];
  if (count($rows) >= 2) break;
}
$hasMp = $p['wb_url'] || $p['ozon_url'];
?>
<article class="pc<?= $view === 'list' ? ' pc-row' : '' ?>" data-reveal data-product="<?= (int)$p['id'] ?>">
  <a class="pc-img" href="<?= h($url) ?>" tabindex="-1" aria-hidden="true">
    <?= picture($ph, $p['name'], $view === 'list' ? '220px' : '(max-width: 560px) 92vw, (max-width: 1100px) 45vw, 290px', !empty($eager)) ?>
    <?php if ($p['badge']): ?><span class="pc-badge"><?= h($p['badge']) ?></span><?php endif; ?>
  </a>
  <button class="pc-cmp" type="button" data-compare="<?= (int)$p['id'] ?>" aria-pressed="false" aria-label="Добавить к сравнению"><?= icon('compare') ?></button>
  <div class="pc-body">
    <h3 class="pc-title"><a href="<?= h($url) ?>"><?= h($p['name']) ?></a></h3>
    <?php if ($b): ?><a class="pc-brand" href="/brand/<?= h($b['slug']) ?>"><?= h($b['name']) ?></a><?php endif; ?>
    <p class="pc-sku">Артикул: <?= h($p['sku']) ?></p>
    <ul class="pc-rows">
      <?php if ($match === 'yes'): ?><li class="ok"><?= icon('check-circle') ?><span>Подходит к вашему автомобилю</span></li>
      <?php elseif ($match === 'check'): ?><li class="warn"><?= icon('alert') ?><span>Уточните кузов или модификацию</span></li>
      <?php else: ?><li><?= icon('car') ?><span>Совместимость: <?= h(compat_short($p, 1)) ?></span></li><?php endif; ?>
      <?php foreach ($rows as [$ic, $n, $v]): ?><li><?= icon($ic) ?><span><?= h($n) ?>: <?= h($v) ?></span></li><?php endforeach; ?>
      <?php if ($p['kit']): ?><li><?= icon('kit') ?><span>В комплекте: <?= h($p['kit']) ?></span></li><?php endif; ?>
    </ul>
    <div class="pc-btns">
      <?php if ($hasMp): ?>
        <?php if ($p['wb_url']): ?><a class="btn btn-wb" href="/go/wb/<?= (int)$p['id'] ?>" target="_blank" rel="nofollow noopener" data-goal="mp_wb" data-mp="wb">Wildberries</a><?php endif; ?>
        <?php if ($p['ozon_url']): ?><a class="btn btn-ozon" href="/go/ozon/<?= (int)$p['id'] ?>" target="_blank" rel="nofollow noopener" data-goal="mp_ozon" data-mp="ozon">Ozon</a><?php endif; ?>
      <?php else: ?>
        <a class="btn btn-primary" href="<?= h($url) ?>">Подробнее<?= icon('chevron-r') ?></a>
        <button class="btn btn-line" type="button" data-lead="availability" data-product-id="<?= (int)$p['id'] ?>" data-title="Уточнить наличие">Уточнить наличие</button>
      <?php endif; ?>
    </div>
    <button class="pc-cart" type="button" data-cart="<?= (int)$p['id'] ?>" aria-pressed="false"><?= icon('plus') ?><span>В корзину</span></button>
  </div>
</article>
