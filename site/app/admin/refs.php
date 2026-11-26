<?php
/** Справочники: категории, марки деталей, автомобили, характеристики (фильтры). Правки публикуются сразу и пишутся в историю. */
if (!defined('AVTOM')) { http_response_code(404); exit; }
$kind = $a[0];
$sub = $a[1] ?? '';

/* порядок перетаскиванием */
if ($sub === 'order') {
  if (!a_require_post()) json_out(['ok' => false], 405);
  $table = ['categories' => 'categories', 'brands' => 'brands', 'attributes' => 'attributes', 'makes' => 'makes', 'models' => 'models'][(string)($_POST['table'] ?? $kind)] ?? null;
  if (!$table) json_out(['ok' => false], 400);
  foreach (array_values((array)($_POST['ids'] ?? [])) as $i => $id) q("UPDATE $table SET sort = ? WHERE id = ?", [($i + 1) * 10, (int)$id]);
  json_out(['ok' => true]);
}

/* ================= категории ================= */
if ($kind === 'categories') {
  $id = $sub === 'new' ? 0 : (int)$sub;
  if ($sub !== '') {
    $c = $id ? q_one('SELECT * FROM categories WHERE id = ?', [$id]) : ['name' => '', 'slug' => '', 'h1' => '', 'intro' => '', 'description' => '', 'photo_id' => null, 'published' => 1, 'seo_title' => '', 'seo_desc' => '', 'sort' => 100];
    if (!$c) redirect('/admin/categories');
    $err = [];
    if (a_require_post()) {
      if (($_POST['action'] ?? '') === 'delete' && $id) {
        $n = (int)q_val('SELECT COUNT(*) FROM products WHERE category_id = ?', [$id]);
        if ($n) { flash("В категории $n " . plural($n, 'товар', 'товара', 'товаров') . ' — сначала перенесите их в другую.', 'err'); redirect('/admin/categories/' . $id); }
        version_add('category', $id, $c, 'delete', 'Удалена'); q('DELETE FROM categories WHERE id = ?', [$id]);
        flash('Категория удалена.'); redirect('/admin/categories');
      }
      $d = ['name' => trim($_POST['name'] ?? ''), 'slug' => trim($_POST['slug'] ?? ''), 'h1' => trim($_POST['h1'] ?? ''), 'intro' => trim($_POST['intro'] ?? ''), 'description' => trim($_POST['description'] ?? ''),
        'photo_id' => (int)($_POST['photo_id'] ?? 0) ?: null, 'published' => !empty($_POST['published']) ? 1 : 0, 'seo_title' => trim($_POST['seo_title'] ?? ''), 'seo_desc' => trim($_POST['seo_desc'] ?? ''), 'updated_at' => now()];
      if ($d['slug'] === '') $d['slug'] = slugify($d['name']);
      if ($d['name'] === '') $err['name'] = 'Укажите название.';
      if (!preg_match('~^[a-z0-9-]+$~', $d['slug'])) $err['slug'] = 'Только латиница, цифры и дефис.';
      elseif (q_val('SELECT id FROM categories WHERE slug = ? AND id != ?', [$d['slug'], $id])) $err['slug'] = 'Такой адрес уже есть.';
      if (!$err) {
        if ($id) q_upd('categories', $d, $id); else { $d['sort'] = (int)q_val('SELECT COALESCE(MAX(sort), 0) + 10 FROM categories'); $id = q_ins('categories', $d); }
        version_add('category', $id, $d, 'publish', 'Сохранено');
        flash('Категория сохранена.'); redirect('/admin/categories');
      }
      $c = array_merge($c, $d);
    }
    a_head($id ? $c['name'] : 'Новая категория', '/categories');
    ?>
    <form method="post" data-dirty-guard>
      <?= csrf_field() ?>
      <div class="a-page-head"><div><a class="a-back" href="/admin/categories"><?= a_icon('arrow-left') ?>Категории</a><h1><?= $id ? h($c['name']) : 'Новая категория' ?></h1></div>
        <?php if ($id && $c['published']): ?><div class="a-head-btns"><a class="a-btn a-btn-ghost" href="/catalog/<?= h($c['slug']) ?>" target="_blank"><?= a_icon('external') ?><span>На сайте</span></a></div><?php endif; ?></div>
      <div class="a-edit-grid">
        <div class="a-edit-main">
          <section class="a-card">
            <div class="a-row2"><?= a_field('name', 'Название', $c['name'], ['required' => true, 'error' => $err['name'] ?? '', 'attrs' => ' data-slug-src']) ?><?= a_field('h1', 'Заголовок на странице', $c['h1'], ['optional' => true, 'hint' => 'Пусто — как название.']) ?></div>
            <?= a_field('intro', 'Подзаголовок', $c['intro'], ['optional' => true]) ?>
            <?= a_field('description', 'Текст внизу страницы', $c['description'], ['type' => 'textarea', 'rows' => 6, 'optional' => true, 'hint' => 'Полезен для поисковиков: чем отличаются детали, как выбрать.']) ?>
          </section>
          <section class="a-card"><h2>Поисковики</h2>
            <?= a_field('seo_title', 'Заголовок страницы', $c['seo_title'], ['optional' => true, 'attrs' => ' data-count="70"']) ?>
            <?= a_field('seo_desc', 'Описание', $c['seo_desc'], ['type' => 'textarea', 'rows' => 2, 'optional' => true, 'attrs' => ' data-count="160"']) ?>
            <?= a_field('slug', 'Адрес: /catalog/…', $c['slug'], ['error' => $err['slug'] ?? '', 'attrs' => ' data-slug']) ?>
          </section>
        </div>
        <aside class="a-edit-side">
          <section class="a-card"><?= a_photo_pick('photo_id', $c['photo_id'], 'Фото категории') ?><p class="a-hint">Пусто — возьмём фото первого товара.</p></section>
          <section class="a-card a-sticky-actions">
            <?= a_check('published', 'Показывать на сайте', $c['published'], 'Категория без товаров скрыта автоматически') ?>
            <button class="a-btn a-btn-primary a-btn-block" type="submit" name="action" value="save">Сохранить</button>
            <?php if ($id): ?><button class="a-link-btn a-danger" type="submit" name="action" value="delete" data-confirm="Удалить категорию?">Удалить категорию</button><?php endif; ?>
          </section>
        </aside>
      </div>
    </form>
    <?php
    a_foot(); return;
  }
  $rows = categories_all(false);
  a_head('Категории', '/categories');
  ?>
  <div class="a-page-head"><div><h1>Категории</h1><p class="a-muted">Порядок здесь = порядок в меню и на главной.</p></div><div class="a-head-btns"><a class="a-btn a-btn-primary" href="/admin/categories/new"><?= a_icon('plus') ?>Добавить</a></div></div>
  <div class="a-card a-card-flush"><ul class="a-plist" data-sortable="categories">
    <?php foreach ($rows as $c): $ph = photo_row($c['photo_id']); ?>
      <li class="a-prow" data-id="<?= (int)$c['id'] ?>"><span class="a-drag" aria-hidden="true"><?= a_icon('dots') ?></span>
        <a class="a-prow-img" href="/admin/categories/<?= (int)$c['id'] ?>"><?= $ph ? '<img src="' . h(img_url($ph, 400)) . '" alt="">' : a_icon('catalog') ?></a>
        <a class="a-prow-main" href="/admin/categories/<?= (int)$c['id'] ?>"><b><?= h($c['name']) ?></b><small>/catalog/<?= h($c['slug']) ?> · <?= (int)$c['cnt'] ?> <?= plural($c['cnt'], 'товар', 'товара', 'товаров') ?> на сайте</small></a>
        <?= !$c['published'] ? '<span class="a-pill a-pill-gray">Скрыта</span>' : ($c['cnt'] ? '<span class="a-pill a-pill-ok">На сайте</span>' : '<span class="a-pill a-pill-gray">Нет товаров</span>') ?>
        <a class="a-btn a-btn-ghost a-btn-sm" href="/admin/products/new?cat=<?= (int)$c['id'] ?>"><?= a_icon('plus') ?><span>Товар</span></a>
      </li>
    <?php endforeach; ?>
  </ul></div>
  <?php
  a_foot(); return;
}

/* ================= марки деталей ================= */
if ($kind === 'brands') {
  $id = $sub === 'new' ? 0 : (int)$sub;
  if ($sub !== '') {
    $b = $id ? q_one('SELECT * FROM brands WHERE id = ?', [$id]) : ['name' => '', 'slug' => '', 'tagline' => '', 'description' => '', 'facts' => '[]', 'country' => '', 'logo_photo_id' => null, 'published' => 1, 'seo_title' => '', 'seo_desc' => ''];
    if (!$b) redirect('/admin/brands');
    $err = [];
    if (a_require_post()) {
      if (($_POST['action'] ?? '') === 'delete' && $id) { q('UPDATE products SET brand_id = NULL WHERE brand_id = ?', [$id]); version_add('brand', $id, $b, 'delete', 'Удалена'); q('DELETE FROM brands WHERE id = ?', [$id]); flash('Марка удалена.'); redirect('/admin/brands'); }
      $facts = [];
      foreach ((array)($_POST['facts'] ?? []) as $f) if (trim($f['k'] ?? '') !== '' && trim($f['v'] ?? '') !== '') $facts[] = [trim($f['k']), trim($f['v'])];
      $d = ['name' => trim($_POST['name'] ?? ''), 'slug' => trim($_POST['slug'] ?? '') ?: slugify($_POST['name'] ?? ''), 'tagline' => trim($_POST['tagline'] ?? ''), 'description' => trim($_POST['description'] ?? ''),
        'facts' => json_encode($facts, JSON_UNESCAPED_UNICODE), 'country' => trim($_POST['country'] ?? ''), 'logo_photo_id' => (int)($_POST['logo_photo_id'] ?? 0) ?: null, 'published' => !empty($_POST['published']) ? 1 : 0,
        'seo_title' => trim($_POST['seo_title'] ?? ''), 'seo_desc' => trim($_POST['seo_desc'] ?? ''), 'updated_at' => now()];
      if ($d['name'] === '') $err['name'] = 'Укажите название.';
      if (q_val('SELECT id FROM brands WHERE slug = ? AND id != ?', [$d['slug'], $id])) $err['slug'] = 'Такой адрес уже есть.';
      if (!$err) { if ($id) q_upd('brands', $d, $id); else $id = q_ins('brands', $d + ['sort' => (int)q_val('SELECT COALESCE(MAX(sort),0)+10 FROM brands')]); version_add('brand', $id, $d, 'publish', 'Сохранено'); flash('Марка сохранена.'); redirect('/admin/brands'); }
      $b = array_merge($b, $d);
    }
    $facts = json_decode($b['facts'] ?: '[]', true) ?: [];
    a_head($id ? $b['name'] : 'Новая марка', '/brands');
    ?>
    <form method="post" data-dirty-guard><?= csrf_field() ?>
      <div class="a-page-head"><div><a class="a-back" href="/admin/brands"><?= a_icon('arrow-left') ?>Марки деталей</a><h1><?= $id ? h($b['name']) : 'Новая марка' ?></h1></div></div>
      <div class="a-edit-grid"><div class="a-edit-main">
        <section class="a-card">
          <div class="a-row2"><?= a_field('name', 'Название', $b['name'], ['required' => true, 'error' => $err['name'] ?? '', 'attrs' => ' data-slug-src']) ?><?= a_field('country', 'Страна производства', $b['country'], ['optional' => true]) ?></div>
          <?= a_field('tagline', 'Коротко', $b['tagline'], ['placeholder' => 'Газовые упоры для прямой замены']) ?>
          <?= a_field('description', 'Описание', $b['description'], ['type' => 'textarea', 'rows' => 8]) ?>
        </section>
        <section class="a-card"><h2>Факты в карточке марки</h2>
          <div class="a-repeat" data-repeat>
            <?php foreach (array_merge($facts, [['', '']]) as $i => [$k, $v]): ?><div class="a-rep-row" data-rep-row><input name="facts[<?= $i ?>][k]" value="<?= h($k) ?>" placeholder="Гарантия" aria-label="Подпись"><input name="facts[<?= $i ?>][v]" value="<?= h($v) ?>" placeholder="1 год" aria-label="Значение"><button type="button" class="a-icon-btn" data-rep-del aria-label="Убрать"><?= a_icon('trash') ?></button></div><?php endforeach; ?>
          </div>
          <button type="button" class="a-btn a-btn-soft a-btn-sm" data-rep-add><?= a_icon('plus') ?>Ещё факт</button>
        </section>
        <section class="a-card"><h2>Поисковики</h2><?= a_field('seo_title', 'Заголовок', $b['seo_title'], ['optional' => true]) ?><?= a_field('seo_desc', 'Описание', $b['seo_desc'], ['type' => 'textarea', 'rows' => 2, 'optional' => true]) ?><?= a_field('slug', 'Адрес: /brand/…', $b['slug'], ['error' => $err['slug'] ?? '', 'attrs' => ' data-slug']) ?></section>
      </div><aside class="a-edit-side">
        <section class="a-card"><?= a_photo_pick('logo_photo_id', $b['logo_photo_id'], 'Логотип') ?><p class="a-hint">Без логотипа название показывается шрифтом сайта.</p></section>
        <section class="a-card a-sticky-actions"><?= a_check('published', 'Показывать на сайте', $b['published']) ?><button class="a-btn a-btn-primary a-btn-block" type="submit" name="action" value="save">Сохранить</button>
          <?php if ($id): ?><button class="a-link-btn a-danger" type="submit" name="action" value="delete" data-confirm="Удалить марку? У товаров поле «Производитель» станет пустым.">Удалить марку</button><?php endif; ?></section>
      </aside></div>
    </form>
    <?php
    a_foot(); return;
  }
  a_head('Марки деталей', '/brands');
  ?>
  <div class="a-page-head"><div><h1>Марки деталей</h1><p class="a-muted">Производители: LIFTAUTOPRO, AUTOPLASTIC и другие. По ним работает фильтр «Производитель».</p></div><div class="a-head-btns"><a class="a-btn a-btn-primary" href="/admin/brands/new"><?= a_icon('plus') ?>Добавить</a></div></div>
  <div class="a-card a-card-flush"><ul class="a-plist" data-sortable="brands">
    <?php foreach (brands_all(false) as $b): $lg = photo_row($b['logo_photo_id']); ?><li class="a-prow" data-id="<?= (int)$b['id'] ?>"><span class="a-drag" aria-hidden="true"><?= a_icon('dots') ?></span><a class="a-prow-img" href="/admin/brands/<?= (int)$b['id'] ?>"><?= $lg ? '<img src="' . h(img_url($lg, 400)) . '" alt="">' : a_icon('shield') ?></a><a class="a-prow-main" href="/admin/brands/<?= (int)$b['id'] ?>"><b><?= h($b['name']) ?></b><small><?= h($b['tagline']) ?> · <?= (int)$b['cnt'] ?> <?= plural($b['cnt'], 'товар', 'товара', 'товаров') ?></small></a><?= $b['published'] ? '<span class="a-pill a-pill-ok">На сайте</span>' : '<span class="a-pill a-pill-gray">Скрыта</span>' ?></li><?php endforeach; ?>
  </ul></div>
  <?php
  a_foot(); return;
}

/* ================= характеристики и фильтры ================= */
if ($kind === 'attributes') {
  if (a_require_post()) {
    $act = $_POST['action'] ?? '';
    if ($act === 'delete') { $aid = (int)$_POST['id']; $used = (int)q_val('SELECT COUNT(*) FROM product_attrs WHERE attribute_id = ?', [$aid]); q('DELETE FROM attributes WHERE id = ?', [$aid]); flash('Характеристика удалена' . ($used ? " (была заполнена у $used " . plural($used, 'товара', 'товаров', 'товаров') . ')' : '') . '.'); redirect('/admin/attributes'); }
    foreach ((array)($_POST['attr'] ?? []) as $aid => $r) {
      $row = ['name' => trim($r['name'] ?? ''), 'unit' => trim($r['unit'] ?? ''), 'filter' => !empty($r['filter']) ? 1 : 0, 'in_card' => !empty($r['in_card']) ? 1 : 0];
      if ($row['name'] === '') continue;
      if ($aid === 'new') {
        $code = slugify($row['name']); $base = $code; $k = 2; while (q_val('SELECT id FROM attributes WHERE code = ?', [$code])) $code = $base . '-' . $k++;
        q_ins('attributes', $row + ['code' => $code, 'type' => ($r['type'] ?? 'number') === 'text' ? 'text' : 'number', 'sort' => (int)q_val('SELECT COALESCE(MAX(sort),0)+10 FROM attributes')]);
      } else q_upd('attributes', $row, (int)$aid);
    }
    version_add('attributes', 'all', q_all('SELECT * FROM attributes'), 'publish', 'Сохранено');
    flash('Характеристики сохранены.'); redirect('/admin/attributes');
  }
  a_head('Фильтры и характеристики', '/attributes');
  ?>
  <div class="a-page-head"><div><h1>Фильтры и характеристики</h1><p class="a-muted">Характеристики заполняются в карточке товара. Числовые можно включить фильтром «от — до» в каталоге: он появится в категориях, где у товаров есть это значение.</p></div></div>
  <form method="post" data-dirty-guard><?= csrf_field() ?>
    <div class="a-card a-card-flush">
      <div class="a-attr a-attr-h"><span></span><span>Название</span><span>Ед. изм.</span><span>Тип</span><span>Фильтр в каталоге</span><span>В карточке</span><span></span></div>
      <ul class="a-plist" data-sortable="attributes">
        <?php foreach (attributes_all() as $at): $n = (int)q_val('SELECT COUNT(*) FROM product_attrs WHERE attribute_id = ?', [$at['id']]); ?>
        <li class="a-attr" data-id="<?= (int)$at['id'] ?>">
          <span class="a-drag" aria-hidden="true"><?= a_icon('dots') ?></span>
          <input name="attr[<?= (int)$at['id'] ?>][name]" value="<?= h($at['name']) ?>" aria-label="Название">
          <input name="attr[<?= (int)$at['id'] ?>][unit]" value="<?= h($at['unit']) ?>" aria-label="Единица" placeholder="мм">
          <span class="a-muted"><?= $at['type'] === 'number' ? 'число' : 'текст' ?> · <?= $n ?> тов.</span>
          <label class="a-switch a-switch-sm"><input type="checkbox" name="attr[<?= (int)$at['id'] ?>][filter]" value="1"<?= $at['filter'] ? ' checked' : '' ?><?= $at['type'] !== 'number' ? ' disabled' : '' ?>><span class="a-sw"></span><span class="a-sr-m">Фильтр</span></label>
          <label class="a-switch a-switch-sm"><input type="checkbox" name="attr[<?= (int)$at['id'] ?>][in_card]" value="1"<?= $at['in_card'] ? ' checked' : '' ?>><span class="a-sw"></span><span class="a-sr-m">В карточке</span></label>
          <button class="a-icon-btn" type="submit" name="action" value="delete" formnovalidate onclick="this.form.querySelector('[name=id]').value='<?= (int)$at['id'] ?>'" data-confirm="Удалить характеристику «<?= h($at['name']) ?>»? Значения у товаров пропадут." aria-label="Удалить"><?= a_icon('trash') ?></button>
        </li>
        <?php endforeach; ?>
      </ul>
      <input type="hidden" name="id" value="">
      <div class="a-attr a-attr-new"><span><?= a_icon('plus') ?></span><input name="attr[new][name]" placeholder="Новая: например, Ход штока" aria-label="Название новой характеристики"><input name="attr[new][unit]" placeholder="мм" aria-label="Единица"><select name="attr[new][type]" aria-label="Тип"><option value="number">число</option><option value="text">текст</option></select><label class="a-switch a-switch-sm"><input type="checkbox" name="attr[new][filter]" value="1"><span class="a-sw"></span><span class="a-sr-m">Фильтр</span></label><label class="a-switch a-switch-sm"><input type="checkbox" name="attr[new][in_card]" value="1" checked><span class="a-sw"></span><span class="a-sr-m">В карточке</span></label><span></span></div>
    </div>
    <div class="a-bar"><button class="a-btn a-btn-primary" type="submit" name="action" value="save">Сохранить</button></div>
  </form>
  <div class="a-card"><h2>Какие фильтры есть в каталоге</h2><ul class="a-mini"><li><b>Автомобиль</b> — марка, модель, год, кузов, двигатель. Берётся из совместимости товаров и справочника «Автомобили».</li><li><b>Производитель</b> — из раздела «Марки деталей».</li><li><b>Числовые характеристики</b> с включённым фильтром — диапазон «от — до».</li></ul></div>
  <?php
  a_foot(); return;
}

/* ================= автомобили ================= */
if ($kind === 'cars') {
  if (a_require_post()) {
    $act = $_POST['action'] ?? '';
    if ($act === 'make_save') {
      $mid = (int)($_POST['make_id'] ?? 0); $name = trim($_POST['name'] ?? '');
      if ($name !== '') {
        $row = ['name' => $name, 'aliases' => trim($_POST['aliases'] ?? '')];
        $slug = slugify($name);
        if ($mid) q_upd('makes', $row, $mid);
        elseif (q_val('SELECT id FROM makes WHERE slug = ?', [$slug])) flash('Такая марка уже есть.', 'err');
        else $mid = q_ins('makes', $row + ['slug' => $slug, 'sort' => (int)q_val('SELECT COALESCE(MAX(sort),0)+10 FROM makes')]);
      }
      flash('Сохранено.'); redirect('/admin/cars' . ($mid ? '?make=' . $mid : ''));
    }
    if ($act === 'make_delete') {
      $mid = (int)$_POST['make_id'];
      $used = (int)q_val('SELECT COUNT(*) FROM compat c JOIN models m ON m.id = c.model_id WHERE m.make_id = ?', [$mid]);
      if ($used) flash("Марка используется в совместимости ($used " . plural($used, 'строка', 'строки', 'строк') . '). Сначала уберите её из товаров.', 'err');
      else { q('DELETE FROM makes WHERE id = ?', [$mid]); flash('Марка удалена.'); }
      redirect('/admin/cars');
    }
    if ($act === 'models_save') {
      $mid = (int)$_POST['make_id'];
      foreach ((array)($_POST['m'] ?? []) as $k => $r) {
        $name = trim($r['name'] ?? ''); if ($name === '') continue;
        $row = ['name' => $name, 'aliases' => trim($r['aliases'] ?? ''), 'year_from' => (int)($r['yf'] ?? 0) ?: null, 'year_to' => (int)($r['yt'] ?? 0) ?: null, 'bodies' => implode(', ', (array)($r['bodies'] ?? []))];
        if ($k === 'new') {
          $slug = slugify($name); $b = $slug; $i = 2; while (q_val('SELECT id FROM models WHERE make_id = ? AND slug = ?', [$mid, $slug])) $slug = $b . '-' . $i++;
          q_ins('models', $row + ['make_id' => $mid, 'slug' => $slug, 'sort' => (int)q_val('SELECT COALESCE(MAX(sort),0)+10 FROM models WHERE make_id = ?', [$mid])]);
        } else q_upd('models', $row, (int)$k);
      }
      version_add('cars', (string)$mid, q_all('SELECT * FROM models WHERE make_id = ?', [$mid]), 'publish', 'Модели сохранены');
      flash('Модели сохранены.'); redirect('/admin/cars?make=' . $mid);
    }
    if ($act === 'model_delete') {
      $id = (int)$_POST['model_id']; $mid = (int)q_val('SELECT make_id FROM models WHERE id = ?', [$id]);
      $used = (int)q_val('SELECT COUNT(*) FROM compat WHERE model_id = ?', [$id]);
      if ($used) flash("Модель есть в совместимости у $used " . plural($used, 'товара', 'товаров', 'товаров') . '. Сначала уберите её из карточек.', 'err');
      else { q('DELETE FROM models WHERE id = ?', [$id]); flash('Модель удалена.'); }
      redirect('/admin/cars?make=' . $mid);
    }
  }
  $makes = q_all('SELECT mk.*, (SELECT COUNT(*) FROM models m WHERE m.make_id = mk.id) AS nm FROM makes mk ORDER BY sort, name');
  $cur = (int)($_GET['make'] ?? ($makes[0]['id'] ?? 0));
  $make = null; foreach ($makes as $m) if ($m['id'] == $cur) $make = $m;
  $models = $make ? q_all('SELECT m.*, (SELECT COUNT(*) FROM compat c WHERE c.model_id = m.id) AS used FROM models m WHERE make_id = ? ORDER BY sort, name', [$cur]) : [];
  $bodies = site('bodies');
  a_head('Автомобили', '/cars');
  ?>
  <div class="a-page-head"><div><h1>Справочник автомобилей</h1><p class="a-muted">Марки и модели для подбора. В подборе на сайте видны только модели, у которых есть товары.</p></div></div>
  <div class="a-cars">
    <div class="a-card a-card-flush a-makes">
      <ul class="a-plist" data-sortable="makes">
        <?php foreach ($makes as $m): ?><li class="a-prow<?= $m['id'] == $cur ? ' is-on' : '' ?>" data-id="<?= (int)$m['id'] ?>"><span class="a-drag" aria-hidden="true"><?= a_icon('dots') ?></span><a class="a-prow-main" href="/admin/cars?make=<?= (int)$m['id'] ?>"><b><?= h($m['name']) ?></b><small><?= (int)$m['nm'] ?> <?= plural($m['nm'], 'модель', 'модели', 'моделей') ?></small></a></li><?php endforeach; ?>
      </ul>
      <form class="a-pad a-inline-add" method="post"><?= csrf_field() ?><input type="hidden" name="action" value="make_save"><input name="name" placeholder="Новая марка, например Kia" aria-label="Новая марка" required><button class="a-btn a-btn-soft a-btn-sm" type="submit"><?= a_icon('plus') ?>Добавить</button></form>
    </div>
    <?php if ($make): ?>
    <div class="a-cars-main">
      <form class="a-card" method="post"><?= csrf_field() ?><input type="hidden" name="make_id" value="<?= (int)$make['id'] ?>">
        <div class="a-row2"><?= a_field('name', 'Марка', $make['name'], ['required' => true]) ?><?= a_field('aliases', 'Как ещё ищут', $make['aliases'], ['optional' => true, 'placeholder' => 'форд', 'hint' => 'Русское написание и сленг через пробел — для поиска.']) ?></div>
        <div class="a-btns"><button class="a-btn a-btn-soft a-btn-sm" type="submit" name="action" value="make_save">Сохранить марку</button><button class="a-link-btn a-danger" type="submit" name="action" value="make_delete" data-confirm="Удалить марку <?= h($make['name']) ?> со всеми моделями?">Удалить марку</button></div>
      </form>
      <form class="a-card a-card-flush" method="post" data-dirty-guard><?= csrf_field() ?><input type="hidden" name="make_id" value="<?= (int)$make['id'] ?>"><input type="hidden" name="model_id" value="">
        <div class="a-card-head a-pad"><h2>Модели <?= h($make['name']) ?></h2></div>
        <ul class="a-plist" data-sortable="models">
          <?php foreach (array_merge($models, [['id' => 'new', 'name' => '', 'aliases' => '', 'year_from' => '', 'year_to' => '', 'bodies' => '', 'used' => 0]]) as $m): $isNew = $m['id'] === 'new'; $sel = list_split($m['bodies']); ?>
          <li class="a-model<?= $isNew ? ' a-model-new' : '' ?>"<?= $isNew ? '' : ' data-id="' . (int)$m['id'] . '"' ?>>
            <span class="a-drag" aria-hidden="true"><?= $isNew ? a_icon('plus') : a_icon('dots') ?></span>
            <div class="a-model-f">
              <div class="a-row4">
                <div class="a-field"><label>Модель</label><input name="m[<?= $m['id'] ?>][name]" value="<?= h($m['name']) ?>" placeholder="<?= $isNew ? 'Новая модель, например Focus IV' : '' ?>"></div>
                <div class="a-field"><label>Как ещё ищут</label><input name="m[<?= $m['id'] ?>][aliases]" value="<?= h($m['aliases']) ?>" placeholder="фокус 4"></div>
                <div class="a-field"><label>Годы выпуска</label><div class="a-range"><input name="m[<?= $m['id'] ?>][yf]" value="<?= h($m['year_from']) ?>" inputmode="numeric" placeholder="с" aria-label="с"><span>—</span><input name="m[<?= $m['id'] ?>][yt]" value="<?= h($m['year_to']) ?>" inputmode="numeric" placeholder="по" aria-label="по"></div></div>
                <div class="a-field a-model-used"><span class="a-lbl">В товарах</span><span><?= (int)$m['used'] ?></span></div>
              </div>
              <div class="a-chips"><?php foreach ($bodies as $b): ?><label class="a-chip"><input type="checkbox" name="m[<?= $m['id'] ?>][bodies][]" value="<?= h($b) ?>"<?= in_array($b, $sel, true) ? ' checked' : '' ?>><span><?= h($b) ?></span></label><?php endforeach; ?></div>
            </div>
            <?php if (!$isNew): ?><button class="a-icon-btn" type="submit" name="action" value="model_delete" onclick="this.form.model_id.value='<?= (int)$m['id'] ?>'" data-confirm="Удалить модель <?= h($m['name']) ?>?" aria-label="Удалить модель"><?= a_icon('trash') ?></button><?php endif; ?>
          </li>
          <?php endforeach; ?>
        </ul>
        <div class="a-bar a-pad"><button class="a-btn a-btn-primary" type="submit" name="action" value="models_save">Сохранить модели</button></div>
      </form>
      <p class="a-hint">Кузова модели подсказывают, какие варианты отметить в совместимости товара. Список кузовов меняется в «Настройках».</p>
    </div>
    <?php endif; ?>
  </div>
  <?php
  a_foot(); return;
}
