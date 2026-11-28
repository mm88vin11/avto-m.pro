<?php
/** Картинки сайта: логотип, значок, превью ссылки, 3D-значки, картинки пустых страниц. Где меняются остальные — подсказка вверху. */
if (!defined('AVTOM')) { http_response_code(404); exit; }

if (a_require_post()) {
  $key = (string)($_POST['key'] ?? '');
  $slot = site_image_slot($key);
  if (!$slot) { flash('Неизвестная картинка.', 'err'); redirect('/admin/images'); }
  if (($_POST['action'] ?? '') === 'reset') {
    site_image_reset($key);
    version_add('site_image', $key, ['file' => null], 'reset', 'Вернули картинку по умолчанию: ' . $slot[0]);
    flash('Вернули картинку по умолчанию: «' . h($slot[0]) . '».');
  } else {
    $f = $_FILES['file'] ?? null;
    if (!$f || $f['error'] !== UPLOAD_ERR_OK) {
      $big = $f && in_array($f['error'], [UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE], true);
      flash($big ? 'Файл слишком большой для хостинга — уменьшите картинку.' : 'Выберите файл картинки.', 'err');
    } else {
      try {
        $url = site_image_save($key, $f['tmp_name']);
        version_add('site_image', $key, ['file' => $url, 'name' => (string)$f['name']], 'publish', 'Заменили картинку: ' . $slot[0]);
        flash('Картинка «' . h($slot[0]) . '» заменена. На сайте она уже новая.');
      } catch (RuntimeException $e) {
        flash(h($e->getMessage()), 'err');
      }
    }
  }
  redirect('/admin/images#img-' . $key);
}

$map = setting_raw('site_images') ?: [];
a_head('Картинки сайта', '/images');
?>
<div class="a-page-head"><div><h1>Картинки сайта</h1><p class="a-muted">Логотип, значок, превью ссылки и 3D-значки. Новая картинка появляется на сайте сразу после загрузки; старую можно вернуть кнопкой «По умолчанию».</p></div></div>

<div class="a-card a-img-where">
  <h2>Где меняются остальные картинки</h2>
  <ul>
    <li><b>Баннеры главной</b>, картинки плиток «Подбор» и «Заявка», фото «О компании» и «Когда пора менять» — <a href="/admin/pages/home">Страницы → Главная</a></li>
    <li><b>Картинки и обложки категорий</b> — <a href="/admin/categories">Категории</a></li>
    <li><b>Фото товаров</b> — <a href="/admin/products">Товары</a> → карточка товара</li>
    <li><b>Логотипы марок</b> (LIFTAUTOPRO, AUTOPLASTIC) — <a href="/admin/brands">Марки деталей</a></li>
    <li><b>Картинка в окне при уходе с сайта</b> — <a href="/admin/pages/popup">Страницы → Окно при уходе</a></li>
  </ul>
  <p class="a-muted">Любую картинку сайт пересохраняет сам: уменьшает до нужных размеров, переводит в лёгкий формат WebP и сохраняет прозрачный фон. Загружайте исходник покрупнее — JPG, PNG или WebP до 20 МБ.</p>
</div>

<?php foreach (site_image_slots() as $group => $slots): ?>
<section class="a-card">
  <h2><?= h($group) ?></h2>
  <div class="a-img-grid">
    <?php foreach ($slots as $key => [$label, $def, $kind, $hint]): $own = !empty($map[$key]); ?>
    <div class="a-img-slot" id="img-<?= h($key) ?>">
      <div class="a-img-prev a-img-<?= h($kind) ?>"><img src="<?= h($kind === 'favicon' ? site_icon(192) : site_img($key)) ?>" alt="" loading="lazy"></div>
      <div class="a-img-info">
        <b><?= h($label) ?></b>
        <small><?= h($hint) ?></small>
        <span class="a-pill <?= $own ? 'a-pill-ok' : 'a-pill-gray' ?>"><?= $own ? 'Своя картинка' : 'По умолчанию' ?></span>
      </div>
      <form class="a-img-btns" method="post" enctype="multipart/form-data" data-autosubmit>
        <?= csrf_field() ?><input type="hidden" name="key" value="<?= h($key) ?>">
        <label class="a-btn a-btn-soft"><input type="file" name="file" accept="image/jpeg,image/png,image/webp" hidden><?= a_icon('plus') ?>Заменить</label>
        <?php if ($own): ?><button class="a-btn a-btn-ghost" type="submit" name="action" value="reset" data-confirm="Вернуть картинку по умолчанию?">По умолчанию</button><?php endif; ?>
      </form>
    </div>
    <?php endforeach; ?>
  </div>
</section>
<?php endforeach; ?>
<?php a_foot();
