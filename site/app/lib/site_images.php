<?php
/**
 * Картинки оформления: логотип, значок сайта, превью ссылки, 3D-значки, картинки пустых состояний.
 * По умолчанию берутся из /assets. Загруженная в админке замена лежит в /uploads/site и записана в настройке site_images.
 * Фото товаров, категорий, баннеры и фото блоков главной хранятся отдельно — в таблице photos (их меняют в своих разделах).
 */
if (!defined('AVTOM')) { http_response_code(404); exit; }

/** Места для картинок: ключ => [название, файл по умолчанию, вид, подсказка] */
function site_image_slots() {
  $icon = fn($name, $label) => [$label, '/assets/img/ui/' . $name . '.webp', 'icon', 'PNG на прозрачном фоне, квадрат от 256×256'];
  return [
    'Логотип и значок' => [
      'logo' => ['Логотип в шапке, меню и подвале', '/assets/img/logo@2x.webp', 'logo', 'PNG на прозрачном фоне, ширина от 440 точек'],
      'favicon' => ['Значок сайта (вкладка браузера, ярлык на телефоне)', '/assets/icons/icon-512.png', 'favicon', 'PNG на прозрачном фоне, квадрат от 512×512; маленькие размеры сделаются сами'],
      'og' => ['Превью ссылки в мессенджерах и соцсетях', '/assets/img/og-default.jpg', 'og', 'Картинка 1200×630 (обрежется по центру до этих пропорций)'],
    ],
    'Блок «О компании»' => [
      'about-building' => ['Здание магазина (главная и страница «О компании»)', '/assets/img/about/building.webp', 'wide', 'PNG на прозрачном фоне, ширина от 1200 точек'],
    ],
    '3D-значки (преимущества, карточки, сотрудничество)' => [
      'ui-shield' => $icon('shield', 'Щит — гарантия, качество'),
      'ui-truck' => $icon('truck', 'Грузовик — доставка'),
      'ui-box' => $icon('box', 'Коробка — комплекты'),
      'ui-chat' => $icon('chat', 'Сообщение — поддержка, помощь'),
      'ui-tag' => $icon('tag', 'Ценник — цена, прямые поставки'),
      'ui-car' => $icon('car', 'Автомобиль — подбор'),
      'ui-sliders' => $icon('sliders', 'Ползунки — подбор по параметрам'),
      'ui-gear' => $icon('gear', 'Шестерня с ключом — сервис, установка'),
      'ui-support' => $icon('support', 'Гарнитура — блок «Есть вопросы?»'),
      'ui-warranty' => $icon('warranty', 'Галочка — выполнено, проверено'),
      'ui-location' => $icon('location', 'Метка — адрес'),
      'ui-order-confirmed' => $icon('order-confirmed', 'Заказ подтверждён — заявка, время'),
      'ui-payment' => $icon('payment', 'Оплата'),
      'ui-favorites' => $icon('favorites', 'Звезда — особенное'),
    ],
    'Картинки пустых страниц' => [
      'empty-cart' => ['Корзина пуста', '/assets/img/empty/cart.webp', 'art', 'PNG на прозрачном фоне, от 480 точек'],
      'empty-compare' => ['Сравнение пусто', '/assets/img/empty/compare.webp', 'art', 'PNG на прозрачном фоне, от 480 точек'],
      'empty-search' => ['Поиск ничего не нашёл', '/assets/img/empty/search.webp', 'art', 'PNG на прозрачном фоне, от 480 точек'],
      'empty-404' => ['Страница не найдена', '/assets/img/empty/404.webp', 'art', 'PNG на прозрачном фоне, от 480 точек'],
    ],
  ];
}
function site_image_slot($key) {
  foreach (site_image_slots() as $group) if (isset($group[$key])) return $group[$key];
  return null;
}

function site_images_dir() { $d = WEB_DIR . '/uploads/site'; if (!is_dir($d)) @mkdir($d, 0755, true); return $d; }

/** Адрес картинки оформления (замена из админки или файл по умолчанию) */
function site_img($key) {
  static $map = null;
  if ($map === null) $map = setting_raw('site_images') ?: [];
  if (!empty($map[$key]) && is_file(WEB_DIR . '/uploads/site/' . $map[$key])) return '/uploads/site/' . $map[$key];
  $slot = site_image_slot($key);
  if (!$slot) return '';
  $f = WEB_DIR . $slot[1];
  return $slot[1] . (is_file($f) ? '?v=' . substr(md5((string)filemtime($f)), 0, 6) : '');
}

/** Значок сайта нужного размера: 16, 32, 180 (iPhone), 192, 512, maskable */
function site_icon($size) {
  $map = setting_raw('site_images') ?: [];
  if (!empty($map['favicon'])) {
    $f = preg_replace('~\.png$~', '', $map['favicon']) . ($size === 'ico' ? '-ico.ico' : '-' . $size . '.png');
    if (is_file(WEB_DIR . '/uploads/site/' . $f)) return '/uploads/site/' . $f;
  }
  if ($size === 'ico') return '/favicon.ico';
  $def = ['16' => 'favicon-16.png', '32' => 'favicon-32.png', '180' => 'apple-touch-icon.png', '192' => 'icon-192.png', '512' => 'icon-512.png', 'maskable' => 'icon-maskable-512.png'];
  return '/assets/icons/' . ($def[(string)$size] ?? 'icon-512.png');
}

/**
 * Сохраняет загруженную замену. Картинка пересобирается через GD (метаданные и лишнее удаляются),
 * прозрачность сохраняется. Возвращает адрес новой картинки или бросает RuntimeException с понятным текстом.
 */
function site_image_save($key, $path) {
  $slot = site_image_slot($key);
  if (!$slot) throw new RuntimeException('Неизвестное место для картинки.');
  $kind = $slot[2];
  $info = @getimagesize($path);
  if (!$info || !in_array($info[2], [IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_WEBP], true)) throw new RuntimeException('Нужен файл JPG, PNG или WebP.');
  if (filesize($path) > IMG_MAX_BYTES) throw new RuntimeException('Файл больше 20 МБ.');
  [$w, $h] = $info;
  $min = ['logo' => 200, 'favicon' => 128, 'og' => 600, 'icon' => 96, 'art' => 200, 'wide' => 600][$kind];
  if (max($w, $h) < $min) throw new RuntimeException("Картинка слишком маленькая — нужно хотя бы $min точек по большей стороне.");
  @ini_set('memory_limit', '512M');
  $src = $info[2] === IMAGETYPE_JPEG ? @imagecreatefromjpeg($path) : ($info[2] === IMAGETYPE_PNG ? @imagecreatefrompng($path) : @imagecreatefromwebp($path));
  if (!$src) throw new RuntimeException('Не получилось прочитать картинку.');
  imagealphablending($src, false); imagesavealpha($src, true);

  $dir = site_images_dir();
  $base = $key . '-' . bin2hex(random_bytes(4));
  if ($kind === 'og') {
    // обрезка по центру до 1200×630
    $tw = 1200; $th = 630; $k = max($tw / $w, $th / $h);
    $cw = (int)round($tw / $k); $ch = (int)round($th / $k);
    $dst = imagecreatetruecolor($tw, $th);
    imagefill($dst, 0, 0, imagecolorallocate($dst, 255, 255, 255));
    imagecopyresampled($dst, $src, 0, 0, (int)(($w - $cw) / 2), (int)(($h - $ch) / 2), $tw, $th, $cw, $ch);
    imageinterlace($dst, true);
    imagejpeg($dst, "$dir/$base.jpg", 88);
    $file = "$base.jpg";
  } elseif ($kind === 'favicon') {
    $file = "$base.png";
    $make = function ($size, $pad, $bg = true) use ($src, $w, $h) {
      $im = imagecreatetruecolor($size, $size);
      imagealphablending($im, false); imagesavealpha($im, true);
      imagefill($im, 0, 0, $bg ? imagecolorallocatealpha($im, 255, 255, 255, 0) : imagecolorallocatealpha($im, 0, 0, 0, 127));
      imagealphablending($im, true);
      $inner = $size * (1 - 2 * $pad); $k = min($inner / $w, $inner / $h);
      $nw = max(1, (int)round($w * $k)); $nh = max(1, (int)round($h * $k));
      imagecopyresampled($im, $src, (int)(($size - $nw) / 2), (int)(($size - $nh) / 2), 0, 0, $nw, $nh, $w, $h);
      return $im;
    };
    foreach (['16' => [16, .02], '32' => [32, .04], '48' => [48, .04], '180' => [180, .12], '192' => [192, .1], '512' => [512, .1], 'maskable' => [512, .2]] as $name => [$size, $pad]) {
      $im = $make($size, $pad);
      imagesavealpha($im, true);
      imagepng($im, "$dir/$base-$name.png", 9);
      imagedestroy($im);
    }
    copy("$dir/$base-512.png", "$dir/$file");
    // ICO с PNG внутри: 16, 32, 48
    $blobs = array_map(fn($s) => file_get_contents("$dir/$base-$s.png"), [16, 32, 48]);
    $ico = pack('vvv', 0, 1, 3); $off = 6 + 16 * 3; $data = '';
    foreach ([16, 32, 48] as $i => $s) { $ico .= pack('CCCCvvVV', $s, $s, 0, 0, 1, 32, strlen($blobs[$i]), $off); $off += strlen($blobs[$i]); $data .= $blobs[$i]; }
    file_put_contents("$dir/$base-ico.ico", $ico . $data);
  } else {
    $max = ['logo' => 880, 'icon' => 256, 'art' => 480, 'wide' => 1400][$kind];
    $k = min(1, $max / max($w, $h));
    $nw = max(1, (int)round($w * $k)); $nh = max(1, (int)round($h * $k));
    $dst = imagecreatetruecolor($nw, $nh);
    imagealphablending($dst, false); imagesavealpha($dst, true);
    imagefill($dst, 0, 0, imagecolorallocatealpha($dst, 0, 0, 0, 127));
    imagecopyresampled($dst, $src, 0, 0, 0, 0, $nw, $nh, $w, $h);
    if (function_exists('imagewebp')) { imagewebp($dst, "$dir/$base.webp", 90); $file = "$base.webp"; }
    else { imagepng($dst, "$dir/$base.png", 9); $file = "$base.png"; }
  }
  imagedestroy($src);
  site_image_forget($key);
  $map = setting_raw('site_images') ?: [];
  $map[$key] = $file;
  setting_save('site_images', $map);
  return '/uploads/site/' . $file;
}

/** Убирает замену — снова показывается картинка по умолчанию */
function site_image_reset($key) {
  site_image_forget($key);
  $map = setting_raw('site_images') ?: [];
  unset($map[$key]);
  setting_save('site_images', $map);
}
function site_image_forget($key) {
  $map = setting_raw('site_images') ?: [];
  if (empty($map[$key])) return;
  $stem = preg_replace('~\.(png|webp|jpg)$~', '', $map[$key]);
  foreach (glob(site_images_dir() . '/' . $stem . '*') ?: [] as $f) @unlink($f);
}

/** srcset для картинки оформления: у файла по умолчанию есть уменьшенная копия «-760» */
function site_img_srcset($key) {
  $u = site_img($key);
  $path = strtok($u, '?');
  $small = preg_replace('~\.webp$~', '-760.webp', $path);
  if ($small !== $path && is_file(WEB_DIR . $small)) return $small . ' 760w, ' . $u . ' 1400w';
  return '';
}

