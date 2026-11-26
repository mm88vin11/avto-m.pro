<?php
if (!defined('AVTOM')) { http_response_code(404); exit; }

const IMG_WIDTHS = [400, 800, 1400];
const IMG_MAX_BYTES = 20 * 1024 * 1024;

function uploads_dir() { $d = WEB_DIR . '/uploads/p'; if (!is_dir($d)) @mkdir($d, 0755, true); return $d; }

/** Адрес варианта фото нужной ширины. $ph — строка photos или массив с file/ext. */
function img_url($ph, $w = 800) {
  if (!$ph) return '';
  $w = in_array($w, IMG_WIDTHS) ? $w : 800;
  return '/uploads/p/' . $ph['file'] . '-' . $w . '.' . ($ph['ext'] ?? 'webp');
}
function img_srcset($ph) { return implode(', ', array_map(fn($w) => img_url($ph, $w) . ' ' . $w . 'w', IMG_WIDTHS)); }
/** Большой JPEG для соцсетей и разметки Product */
function img_og($ph) { return $ph ? '/uploads/p/' . $ph['file'] . '-og.jpg' : ''; }

/**
 * Принимает файл (путь), проверяет, что это изображение, пересохраняет в нужные размеры (EXIF и прочие метаданные удаляются).
 * Возвращает id записи в photos или бросает исключение с понятным текстом.
 */
function image_import($path, $origName = '') {
  if (!is_file($path) || filesize($path) > IMG_MAX_BYTES) throw new RuntimeException('Файл больше 20 МБ.');
  $info = @getimagesize($path);
  if (!$info || !in_array($info[2], [IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_WEBP, IMAGETYPE_GIF], true)) throw new RuntimeException('Нужен файл JPG, PNG или WebP.');
  [$w, $h] = $info;
  if ($w < 200 || $h < 200) throw new RuntimeException('Фото слишком маленькое — нужно хотя бы 200×200 точек.');
  if ($w * $h > 40000000) throw new RuntimeException('Слишком большое разрешение — уменьшите фото до 6000 точек по большей стороне.');
  @ini_set('memory_limit', '512M');
  switch ($info[2]) {
    case IMAGETYPE_JPEG: $src = @imagecreatefromjpeg($path); break;
    case IMAGETYPE_PNG: $src = @imagecreatefrompng($path); break;
    case IMAGETYPE_WEBP: $src = @imagecreatefromwebp($path); break;
    default: $src = @imagecreatefromgif($path);
  }
  if (!$src) throw new RuntimeException('Не получилось прочитать фото.');
  if ($info[2] === IMAGETYPE_JPEG && function_exists('exif_read_data')) {
    $exif = @exif_read_data($path);
    $o = (int)($exif['Orientation'] ?? 1);
    if ($o === 3) $src = imagerotate($src, 180, 0);
    elseif ($o === 6) $src = imagerotate($src, -90, 0);
    elseif ($o === 8) $src = imagerotate($src, 90, 0);
    $w = imagesx($src); $h = imagesy($src);
  }
  // прозрачность → белый фон
  $flat = imagecreatetruecolor($w, $h);
  imagefill($flat, 0, 0, imagecolorallocate($flat, 255, 255, 255));
  imagecopy($flat, $src, 0, 0, 0, 0, $w, $h);
  imagedestroy($src);

  $ext = function_exists('imagewebp') ? 'webp' : 'jpg';
  $file = date('ym') . '-' . bin2hex(random_bytes(6));
  $dir = uploads_dir();
  foreach (IMG_WIDTHS as $tw) image_save_resized($flat, $w, $h, $tw, "$dir/$file-$tw.$ext", $ext);
  image_save_resized($flat, $w, $h, 1200, "$dir/$file-og.jpg", 'jpg');
  imagedestroy($flat);
  return q_ins('photos', ['file' => $file, 'ext' => $ext, 'w' => $w, 'h' => $h, 'orig_name' => mb_substr($origName, 0, 120), 'created_at' => now()]);
}

function image_save_resized($im, $w, $h, $tw, $dest, $ext) {
  if ($w > $tw) { $nw = $tw; $nh = (int)round($h * $tw / $w); } else { $nw = $w; $nh = $h; }
  $dst = imagecreatetruecolor($nw, $nh);
  imagecopyresampled($dst, $im, 0, 0, 0, 0, $nw, $nh, $w, $h);
  if ($ext === 'webp') imagewebp($dst, $dest, 84); else { imageinterlace($dst, true); imagejpeg($dst, $dest, 86); }
  imagedestroy($dst);
}

/** Удаляет фото, если оно больше нигде не используется */
function photo_delete_if_orphan($id) {
  $used = q_val('SELECT COUNT(*) FROM product_photos WHERE photo_id = ?', [$id])
    + q_val('SELECT COUNT(*) FROM categories WHERE photo_id = ?', [$id])
    + q_val('SELECT COUNT(*) FROM brands WHERE logo_photo_id = ?', [$id])
    + q_val("SELECT COUNT(*) FROM drafts WHERE data LIKE ?", ['%"id":' . (int)$id . '%']);
  foreach (array_keys(content_keys()) as $k) { $c = setting_raw('content.' . $k); if ($c && strpos(json_encode($c), '"photo_id":' . (int)$id) !== false) $used++; }
  if ($used) return false;
  $ph = q_one('SELECT * FROM photos WHERE id = ?', [$id]);
  if (!$ph) return false;
  foreach (glob(uploads_dir() . '/' . $ph['file'] . '-*') as $f) @unlink($f);
  q('DELETE FROM photos WHERE id = ?', [$id]);
  return true;
}
