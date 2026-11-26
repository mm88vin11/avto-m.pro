<?php
$cat = $cat ?? null;
$f = filters_from_query();
$r = catalog_query($cat['id'] ?? null, $f);
$items = $r['items'];
$facets = $r['facets'];
$car = $f['car'];
$base = $cat ? '/catalog/' . $cat['slug'] : '/catalog';
$n = count($items);

if ($cat) {
  meta('title', $cat['seo_title'] ?: $cat['name'] . ' — купить с подбором по автомобилю | AVTO-M');
  meta('desc', $cat['seo_desc'] ?: md_plain($cat['intro'] . ' ' . $cat['description'], 160));
} else {
  meta('title', 'Каталог автозапчастей с подбором по автомобилю | AVTO-M');
  meta('desc', 'Газовые упоры багажника и расширительные бачки: подбор по марке, модели, году, кузову и двигателю. Покупка на Wildberries и Ozon или по заявке.');
}
if ($car) meta('title', 'Запчасти для ' . trim($car['make_name'] . ' ' . $car['model_name']) . ($cat ? ' — ' . mb_strtolower($cat['name']) : '') . ' | AVTO-M');
meta('canonical', $base);
$filtered = array_diff(array_keys($_GET), ['sort', 'view']);
if ($filtered || ($_GET['sort'] ?? '') || ($_GET['view'] ?? '')) meta('robots', 'noindex, follow');
meta('body_class', 'page-catalog');

$h1 = $cat ? ($cat['h1'] ?: $cat['name']) : 'Каталог';
$crumbs = $cat ? [['Каталог', '/catalog'], [$cat['name'], null]] : [['Каталог', null]];
$checkCount = count(array_filter($items, fn($p) => ($p['match'] ?? '') === 'check'));
$activeCount = ($car ? 1 : 0) + count($f['brand']) + count($f['a']);

// активные фильтры — снимаются по одному
$chips = [];
if ($car) $chips[] = [car_label($car), qs_without(['make', 'model', 'year', 'body', 'engine'])];
foreach ($f['brand'] as $bs) { $bn = $facets['brands'][$bs]['name'] ?? $bs; $q = $_GET; $q['brand'] = array_values(array_diff((array)$q['brand'], [$bs])); $chips[] = [$bn, '?' . http_build_query(array_filter($q))]; }
foreach ($f['a'] as $code => $rg) { $a = attribute_by_code($code); $q = $_GET; unset($q['a'][$code]); $lbl = $a['name'] . ': ' . ($rg['min'] !== null ? 'от ' . fmt_num($rg['min']) . ' ' : '') . ($rg['max'] !== null ? 'до ' . fmt_num($rg['max']) . ' ' : '') . $a['unit']; $chips[] = [trim($lbl), '?' . http_build_query(array_filter($q))]; }
$viewUrl = fn($v) => $base . qs_without(['view'], $v === 'grid' ? [] : ['view' => $v]);
?>
<div class="wrap" id="catalog-root" data-catalog>
  <?= breadcrumbs($crumbs) ?>
  <div class="page-head">
    <h1 data-split><?= h($h1) ?></h1>
    <?php if ($cat && $cat['intro']): ?><p class="lead"><?= h($cat['intro']) ?></p><?php endif; ?>
  </div>

  <?php if (!$cat && ($cats = categories_visible())): ?>
  <nav class="chips-nav" aria-label="Категории">
    <a class="chip is-on" href="/catalog" aria-current="page">Все товары</a>
    <?php foreach ($cats as $c): ?><a class="chip" href="/catalog/<?= h($c['slug']) ?><?= h(qs_without(['brand', 'a'])) ?>"><?= h($c['name']) ?> <small><?= (int)$c['cnt'] ?></small></a><?php endforeach; ?>
  </nav>
  <?php elseif ($cat): ?>
  <nav class="chips-nav" aria-label="Категории">
    <a class="chip" href="/catalog<?= h(qs_without(['brand', 'a'])) ?>">Все товары</a>
    <?php foreach (categories_visible() as $c): ?><a class="chip<?= $c['id'] == $cat['id'] ? ' is-on' : '' ?>" href="/catalog/<?= h($c['slug']) ?><?= h(qs_without(['brand', 'a'])) ?>"<?= $c['id'] == $cat['id'] ? ' aria-current="page"' : '' ?>><?= h($c['name']) ?> <small><?= (int)$c['cnt'] ?></small></a><?php endforeach; ?>
  </nav>
  <?php endif; ?>

  <div class="carbar panel<?= $car ? ' has-car' : '' ?>">
    <span class="carbar-ic"><?= icon('car') ?></span>
    <?php if ($car): ?>
      <div class="carbar-t"><small>Подбор для автомобиля</small><b><?= h(car_label($car)) ?></b></div>
      <button class="btn btn-soft" type="button" data-open-filters="car"><?= icon('car') ?><span>Изменить</span></button>
      <a class="icon-btn" href="<?= h($base . qs_without(['make', 'model', 'year', 'body', 'engine'])) ?>" aria-label="Сбросить автомобиль"><?= icon('close') ?></a>
    <?php else: ?>
      <div class="carbar-t"><b>Выберите автомобиль</b><small>Покажем только то, что подходит, и отметим совместимость</small></div>
      <button class="btn btn-primary" type="button" data-open-filters="car"><span>Подобрать</span></button>
    <?php endif; ?>
  </div>

  <div class="cat-layout">
    <aside class="filters" id="filters" aria-label="Фильтры">
      <form class="filters-form" action="<?= h($base) ?>" method="get" data-filters>
        <div class="filters-head"><h2>Фильтры</h2><button class="icon-btn" type="button" data-close-filters aria-label="Закрыть фильтры"><?= icon('close') ?></button></div>
        <div class="filters-scroll">
          <input type="hidden" name="sort" value="<?= $f['sort'] === 'popular' ? '' : h($f['sort']) ?>">
          <input type="hidden" name="view" value="<?= $f['view'] === 'grid' ? '' : h($f['view']) ?>">
          <?php if ($f['q'] !== ''): ?><input type="hidden" name="q" value="<?= h($f['q']) ?>"><?php endif; ?>
          <fieldset class="fgroup" id="f-car">
            <legend>Автомобиль</legend>
            <?php partial('picker', ['id' => 'fc', 'car' => $car, 'action' => $base, 'embedded' => true]); ?>
          </fieldset>
          <?php if (count($facets['brands']) > 0): ?>
          <fieldset class="fgroup">
            <legend>Производитель</legend>
            <?php foreach ($facets['brands'] as $slug => $fb): ?>
              <label class="check"><input type="checkbox" name="brand[]" value="<?= h($slug) ?>"<?= in_array($slug, $f['brand'], true) ? ' checked' : '' ?>><span class="check-box" aria-hidden="true"><?= icon('check') ?></span><span><?= h($fb['name']) ?> <small class="muted"><?= (int)$fb['n'] ?></small></span></label>
            <?php endforeach; ?>
          </fieldset>
          <?php endif; ?>
          <?php foreach ($facets['attrs'] as $code => $fa): $cur = $f['a'][$code] ?? ['min' => null, 'max' => null]; ?>
          <fieldset class="fgroup">
            <legend><?= h($fa['name']) ?><?= $fa['unit'] ? ', ' . h($fa['unit']) : '' ?></legend>
            <div class="range">
              <label><span class="sr"><?= h($fa['name']) ?> от</span><input type="number" inputmode="decimal" name="a[<?= h($code) ?>][min]" value="<?= $cur['min'] !== null ? h(fmt_num($cur['min'])) : '' ?>" placeholder="от <?= h(fmt_num($fa['min'])) ?>"></label>
              <span aria-hidden="true">—</span>
              <label><span class="sr"><?= h($fa['name']) ?> до</span><input type="number" inputmode="decimal" name="a[<?= h($code) ?>][max]" value="<?= $cur['max'] !== null ? h(fmt_num($cur['max'])) : '' ?>" placeholder="до <?= h(fmt_num($fa['max'])) ?>"></label>
            </div>
          </fieldset>
          <?php endforeach; ?>
        </div>
        <div class="filters-foot">
          <button class="btn btn-primary btn-block" type="submit" data-apply><span>Показать <?= $n ?> <?= plural($n, 'товар', 'товара', 'товаров') ?></span></button>
          <a class="btn btn-soft btn-block" href="<?= h($base) ?>">Сбросить фильтры</a>
        </div>
      </form>
    </aside>
    <div class="filters-backdrop" data-close-filters hidden></div>

    <section class="results" aria-live="polite" aria-busy="false" data-results>
      <div class="toolbar">
        <button class="btn btn-soft filters-open" type="button" data-open-filters><?= icon('filter') ?><span>Фильтры</span><?php if ($activeCount): ?><b class="badge"><?= $activeCount ?></b><?php endif; ?></button>
        <p class="found"><?= $n ? 'Найдено ' . $n . ' ' . plural($n, 'товар', 'товара', 'товаров') : 'Ничего не нашли' ?></p>
        <label class="sort"><span class="sr">Сортировка</span>
          <select data-sort>
            <?php foreach (['popular' => 'Сначала популярные', 'name' => 'По названию', 'new' => 'Сначала новые'] as $k => $v): ?>
              <option value="<?= h($base . qs_without(['sort'], $k === 'popular' ? [] : ['sort' => $k])) ?>"<?= $f['sort'] === $k ? ' selected' : '' ?>><?= $v ?></option>
            <?php endforeach; ?>
          </select>
        </label>
        <div class="viewsw" role="group" aria-label="Вид списка">
          <a class="icon-btn<?= $f['view'] === 'grid' ? ' is-on' : '' ?>" href="<?= h($viewUrl('grid')) ?>" aria-label="Плиткой"<?= $f['view'] === 'grid' ? ' aria-current="true"' : '' ?>><?= icon('grid') ?></a>
          <a class="icon-btn<?= $f['view'] === 'list' ? ' is-on' : '' ?>" href="<?= h($viewUrl('list')) ?>" aria-label="Списком"<?= $f['view'] === 'list' ? ' aria-current="true"' : '' ?>><?= icon('rows') ?></a>
        </div>
      </div>
      <?php if ($chips): ?>
      <div class="active-chips">
        <?php foreach ($chips as [$t, $u]): ?><a class="chip chip-x" href="<?= h($base . ($u === '?' ? '' : $u)) ?>"><?= h($t) ?><?= icon('close') ?><span class="sr"> — убрать</span></a><?php endforeach; ?>
        <a class="link-reset" href="<?= h($base) ?>">Сбросить всё</a>
      </div>
      <?php endif; ?>
      <?php if ($checkCount): ?>
      <div class="note note-warn"><?= icon('alert') ?><div><b>Для <?= $checkCount ?> <?= plural($checkCount, 'детали', 'деталей', 'деталей') ?> не хватает данных о кузове или двигателе.</b> Модель совпадает, но точную совместимость лучше уточнить. <button class="linklike" type="button" data-lead="pick" data-title="Уточнить совместимость">Спросить менеджера</button></div></div>
      <?php endif; ?>

      <?php if ($items): ?>
        <div class="grid<?= $f['view'] === 'list' ? ' grid-list' : '' ?>">
          <?php foreach ($items as $i => $p) partial('card', ['p' => $p, 'view' => $f['view'], 'eager' => $i < 2]); ?>
        </div>
      <?php else: ?>
        <div class="empty panel">
          <div class="empty-ic" aria-hidden="true"><?= icon('search') ?></div>
          <h2>По этим условиям ничего нет</h2>
          <p>Попробуйте убрать часть фильтров или напишите нам — проверим совместимость по VIN-номеру и подскажем, что подойдёт.</p>
          <div class="empty-btns">
            <a class="btn btn-soft" href="<?= h($base) ?>">Сбросить фильтры</a>
            <button class="btn btn-primary" type="button" data-lead="pick" data-title="Помощь с подбором"><?= icon('chat') ?>Помогите подобрать</button>
          </div>
        </div>
      <?php endif; ?>
    </section>
  </div>

  <?php if ($cat && !$filtered): ?><div class="section"><?php partial('measure', ['catId' => $cat['id']]); ?></div><?php endif; ?>

  <?php if ($cat && $cat['description'] && !$filtered): ?>
  <section class="seo-text panel"><div class="prose"><?= md($cat['description']) ?></div></section>
  <?php endif; ?>
</div>
<?php
jsonld(['@context' => 'https://schema.org', '@type' => 'ItemList', 'name' => $h1, 'numberOfItems' => $n,
  'itemListElement' => array_map(fn($p, $i) => ['@type' => 'ListItem', 'position' => $i + 1, 'url' => site_url(product_url($p)), 'name' => $p['name']], array_slice($items, 0, 30), array_keys(array_slice($items, 0, 30)))]);
