<?php
/** Карточка товара в выдаче. $p — товар, $view — grid|list, $eager — первая строка без ленивой загрузки */
$view = $view ?? 'grid';
$b = product_brand($p);
$ph = product_photo($p);
$specs = product_key_specs($p, 2);
$url = product_url($p);
$match = $p['match'] ?? null;
?>
<article class="card <?= $view === 'list' ? 'card-row' : '' ?>" data-reveal data-product="<?= (int)$p['id'] ?>">
  <a class="card-media" href="<?= h($url) ?>" tabindex="-1" aria-hidden="true">
    <?= picture($ph, $p['name'], $view === 'list' ? '220px' : '(max-width: 640px) 92vw, (max-width: 1100px) 45vw, 300px', !empty($eager)) ?>
    <?php if ($p['badge']): ?><span class="tag tag-accent"><?= h($p['badge']) ?></span><?php endif; ?>
  </a>
  <button class="card-compare" type="button" data-compare="<?= (int)$p['id'] ?>" aria-pressed="false" aria-label="Добавить к сравнению"><?= icon('compare') ?></button>
  <div class="card-body">
    <p class="card-meta"><?= h($b['name'] ?? '') ?> <span>·</span> <?= h($p['sku']) ?></p>
    <h3 class="card-title"><a href="<?= h($url) ?>"><?= h($p['name']) ?></a></h3>
    <?php if ($match === 'yes'): ?><p class="fit fit-yes"><?= icon('check-circle') ?>Подходит к вашему автомобилю</p>
    <?php elseif ($match === 'check'): ?><p class="fit fit-check"><?= icon('alert') ?>Уточните кузов или двигатель</p>
    <?php else: ?><p class="card-compat"><?= icon('car') ?><span><?= h(compat_short($p, 2)) ?></span></p><?php endif; ?>
    <?php if ($specs || $p['kit']): ?>
    <ul class="card-specs">
      <?php foreach ($specs as [$n, $v]): ?><li><span><?= h($n) ?></span><b><?= h($v) ?></b></li><?php endforeach; ?>
      <?php if ($p['kit']): ?><li><span>В комплекте</span><b><?= h($p['kit']) ?></b></li><?php endif; ?>
    </ul>
    <?php endif; ?>
    <div class="card-actions">
      <?php if ($p['wb_url'] || $p['ozon_url']): ?>
        <div class="mp-row"><?= mp_buttons($p) ?></div>
      <?php else: ?>
        <button class="btn btn-primary" type="button" data-lead="availability" data-product-id="<?= (int)$p['id'] ?>" data-title="Уточнить наличие"><?= icon('chat') ?>Уточнить наличие</button>
      <?php endif; ?>
      <button class="btn btn-soft btn-add" type="button" data-cart="<?= (int)$p['id'] ?>" aria-pressed="false"><?= icon('plus') ?><span>В запрос</span></button>
    </div>
  </div>
</article>
