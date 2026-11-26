<?php
/** Загрузка фото из админки: POST /admin/upload (файл photo) → {ok, id, url} */
if (!defined('AVTOM')) { http_response_code(404); exit; }
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST' || !csrf_ok()) json_out(['ok' => false, 'error' => 'Обновите страницу и повторите.'], 400);
if (empty($_FILES['photo']) || $_FILES['photo']['error'] !== UPLOAD_ERR_OK) {
  $code = $_FILES['photo']['error'] ?? -1;
  json_out(['ok' => false, 'error' => in_array($code, [UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE], true) ? 'Файл слишком большой для хостинга — уменьшите фото.' : 'Файл не загрузился.'], 400);
}
try {
  $id = image_import($_FILES['photo']['tmp_name'], (string)$_FILES['photo']['name']);
  $ph = photo_row($id);
  json_out(['ok' => true, 'id' => $id, 'url' => img_url($ph, 400), 'w' => $ph['w'], 'h' => $ph['h']]);
} catch (Throwable $e) {
  json_out(['ok' => false, 'error' => $e instanceof RuntimeException ? $e->getMessage() : 'Не получилось обработать фото.'], 400);
}
