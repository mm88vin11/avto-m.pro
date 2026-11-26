<?php
if (!defined('AVTOM')) { http_response_code(404); exit; }

function backups_dir() { $d = cfg('data_dir') . '/backups'; if (!is_dir($d)) @mkdir($d, 0750, true); return $d; }

/** Копия базы и фото в zip. $withPhotos=false — только база (быстро). */
function backup_create($withPhotos = true) {
  @set_time_limit(300);
  $dir = backups_dir();
  $stamp = date('Ymd-His');
  $tmpDb = "$dir/db-$stamp.sqlite";
  db()->exec('VACUUM INTO ' . db()->quote($tmpDb));
  $zipPath = "$dir/avtom-$stamp" . ($withPhotos ? '' : '-db') . '.zip';
  $z = new ZipArchive();
  if ($z->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) { @unlink($tmpDb); throw new RuntimeException('Не удалось создать архив.'); }
  $z->addFile($tmpDb, 'data/avtom.sqlite');
  if ($withPhotos) foreach (glob(uploads_dir() . '/*') as $f) $z->addFile($f, 'uploads/p/' . basename($f));
  $z->addFromString('README.txt', "Резервная копия сайта AVTO-M от " . date('d.m.Y H:i') . ".\nВосстановление: data/avtom.sqlite → app/data/avtom.sqlite, uploads/p → uploads/p (см. docs/DEPLOY.md, раздел «Резервные копии»).\n");
  $z->close();
  @unlink($tmpDb);
  backup_prune();
  return basename($zipPath);
}
function backup_prune() {
  $files = glob(backups_dir() . '/avtom-*.zip');
  rsort($files);
  foreach (array_slice($files, (int)cfg('backup_keep', 14)) as $f) @unlink($f);
}
function backups_list() {
  $out = [];
  foreach (glob(backups_dir() . '/avtom-*.zip') as $f) $out[] = ['name' => basename($f), 'size' => filesize($f), 'time' => filemtime($f)];
  usort($out, fn($a, $b) => $b['time'] <=> $a['time']);
  return $out;
}
/** Ежедневная автокопия: вызывается из админки и из /cron. Не чаще раза в 20 часов. */
function backup_auto() {
  $last = 0;
  foreach (glob(backups_dir() . '/avtom-*.zip') as $f) $last = max($last, filemtime($f));
  if (time() - $last < 20 * 3600) return null;
  try { return backup_create(true); } catch (Throwable $e) { log_err('backup: ' . $e->getMessage()); return null; }
}
