<?php
if (!defined('AVTOM')) { http_response_code(404); exit; }

const DB_SCHEMA_VERSION = 5;

function db() {
  static $pdo = null;
  if ($pdo) return $pdo;
  $file = cfg('data_dir') . '/avtom.sqlite';
  $new = !is_file($file);
  $pdo = new PDO('sqlite:' . $file, null, null, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
  ]);
  $pdo->exec('PRAGMA foreign_keys = ON; PRAGMA journal_mode = WAL; PRAGMA busy_timeout = 5000; PRAGMA synchronous = NORMAL;');
  $v = (int)$pdo->query('PRAGMA user_version')->fetchColumn();
  if ($v < DB_SCHEMA_VERSION) {
    db_migrate($pdo, $v);
    require_once APP_DIR . '/seed/seed.php';
    if ($v === 0) {
      $pdo->beginTransaction();
      seed_all($pdo);
      $pdo->commit();
    } else {
      if ($v < 4) seed_upgrade_2611(); // графика заказчика от 26.11: баннеры, чистые картинки плиток
      if ($v < 5) seed_upgrade_2811(); // короче тексты главной, баннер раз в 3 секунды
    }
  }
  return $pdo;
}

function q($sql, $p = []) { $s = db()->prepare($sql); $s->execute($p); return $s; }
function q_all($sql, $p = []) { return q($sql, $p)->fetchAll(); }
function q_one($sql, $p = []) { $r = q($sql, $p)->fetch(); return $r === false ? null : $r; }
function q_val($sql, $p = []) { $r = q($sql, $p)->fetchColumn(); return $r === false ? null : $r; }
function q_ins($table, array $row) {
  $cols = array_keys($row);
  q('INSERT INTO ' . $table . ' (' . implode(',', $cols) . ') VALUES (' . implode(',', array_fill(0, count($cols), '?')) . ')', array_values($row));
  return (int)db()->lastInsertId();
}
function q_upd($table, array $row, $id) {
  $set = implode(',', array_map(fn($c) => "$c = ?", array_keys($row)));
  q("UPDATE $table SET $set WHERE id = ?", array_merge(array_values($row), [$id]));
}

function db_migrate(PDO $pdo, $from) {
  $pdo->beginTransaction();
  if ($from < 1) {
    $pdo->exec(<<<SQL
CREATE TABLE settings (key TEXT PRIMARY KEY, value TEXT NOT NULL, updated_at TEXT);
CREATE TABLE drafts (entity TEXT NOT NULL, entity_id TEXT NOT NULL, data TEXT NOT NULL, user_id INTEGER, updated_at TEXT, PRIMARY KEY (entity, entity_id));
CREATE TABLE versions (id INTEGER PRIMARY KEY, entity TEXT NOT NULL, entity_id TEXT NOT NULL, data TEXT NOT NULL, action TEXT NOT NULL, user_id INTEGER, user_name TEXT, note TEXT, created_at TEXT NOT NULL);
CREATE INDEX versions_entity ON versions (entity, entity_id, id);

CREATE TABLE users (id INTEGER PRIMARY KEY, login TEXT NOT NULL UNIQUE COLLATE NOCASE, name TEXT NOT NULL, pass_hash TEXT NOT NULL, role TEXT NOT NULL DEFAULT 'manager', active INTEGER NOT NULL DEFAULT 1, created_at TEXT, last_login TEXT);
CREATE TABLE login_attempts (id INTEGER PRIMARY KEY, ip TEXT, login TEXT, at INTEGER);

CREATE TABLE photos (id INTEGER PRIMARY KEY, file TEXT NOT NULL, ext TEXT NOT NULL DEFAULT 'webp', w INTEGER, h INTEGER, orig_name TEXT, created_at TEXT);

CREATE TABLE categories (id INTEGER PRIMARY KEY, slug TEXT NOT NULL UNIQUE, name TEXT NOT NULL, h1 TEXT, intro TEXT, description TEXT, photo_id INTEGER, sort INTEGER NOT NULL DEFAULT 0, published INTEGER NOT NULL DEFAULT 1, seo_title TEXT, seo_desc TEXT, updated_at TEXT);
CREATE TABLE brands (id INTEGER PRIMARY KEY, slug TEXT NOT NULL UNIQUE, name TEXT NOT NULL, tagline TEXT, description TEXT, facts TEXT, country TEXT, logo_photo_id INTEGER, sort INTEGER NOT NULL DEFAULT 0, published INTEGER NOT NULL DEFAULT 1, seo_title TEXT, seo_desc TEXT, updated_at TEXT);

CREATE TABLE attributes (id INTEGER PRIMARY KEY, code TEXT NOT NULL UNIQUE, name TEXT NOT NULL, unit TEXT, type TEXT NOT NULL DEFAULT 'number', filter INTEGER NOT NULL DEFAULT 1, in_card INTEGER NOT NULL DEFAULT 1, sort INTEGER NOT NULL DEFAULT 0);

CREATE TABLE products (
  id INTEGER PRIMARY KEY, slug TEXT NOT NULL UNIQUE, sku TEXT NOT NULL UNIQUE COLLATE NOCASE, sku_norm TEXT, name TEXT NOT NULL,
  category_id INTEGER REFERENCES categories(id) ON DELETE SET NULL, brand_id INTEGER REFERENCES brands(id) ON DELETE SET NULL,
  short TEXT, description TEXT, oem TEXT, oem_norm TEXT, kit TEXT,
  wb_url TEXT, ozon_url TEXT, price_from INTEGER, stock TEXT NOT NULL DEFAULT 'request', badge TEXT,
  published INTEGER NOT NULL DEFAULT 0, sort INTEGER NOT NULL DEFAULT 0, views INTEGER NOT NULL DEFAULT 0,
  seo_title TEXT, seo_desc TEXT, created_at TEXT, updated_at TEXT, published_at TEXT);
CREATE INDEX products_cat ON products (category_id, published);

CREATE TABLE product_attrs (product_id INTEGER NOT NULL REFERENCES products(id) ON DELETE CASCADE, attribute_id INTEGER NOT NULL REFERENCES attributes(id) ON DELETE CASCADE, value_num REAL, value_text TEXT, PRIMARY KEY (product_id, attribute_id));
CREATE TABLE product_photos (product_id INTEGER NOT NULL REFERENCES products(id) ON DELETE CASCADE, photo_id INTEGER NOT NULL REFERENCES photos(id) ON DELETE CASCADE, sort INTEGER NOT NULL DEFAULT 0, alt TEXT, PRIMARY KEY (product_id, photo_id));

CREATE TABLE makes (id INTEGER PRIMARY KEY, slug TEXT NOT NULL UNIQUE, name TEXT NOT NULL, aliases TEXT, sort INTEGER NOT NULL DEFAULT 0);
CREATE TABLE models (id INTEGER PRIMARY KEY, make_id INTEGER NOT NULL REFERENCES makes(id) ON DELETE CASCADE, slug TEXT NOT NULL, name TEXT NOT NULL, aliases TEXT, year_from INTEGER, year_to INTEGER, bodies TEXT, sort INTEGER NOT NULL DEFAULT 0, UNIQUE (make_id, slug));
CREATE TABLE compat (id INTEGER PRIMARY KEY, product_id INTEGER NOT NULL REFERENCES products(id) ON DELETE CASCADE, model_id INTEGER NOT NULL REFERENCES models(id) ON DELETE CASCADE, year_from INTEGER, year_to INTEGER, bodies TEXT, engines TEXT, note TEXT, sort INTEGER NOT NULL DEFAULT 0);
CREATE INDEX compat_model ON compat (model_id);
CREATE INDEX compat_product ON compat (product_id);

CREATE TABLE leads (
  id INTEGER PRIMARY KEY, kind TEXT NOT NULL, status TEXT NOT NULL DEFAULT 'new', name TEXT, phone TEXT, email TEXT, car TEXT, comment TEXT,
  items TEXT, page TEXT, utm TEXT, manager_note TEXT, mail_status TEXT, ip_hash TEXT, ua TEXT,
  consent_at TEXT, consent_version TEXT, created_at TEXT NOT NULL, updated_at TEXT);
CREATE INDEX leads_status ON leads (status, id);
CREATE TABLE lead_events (id INTEGER PRIMARY KEY, lead_id INTEGER NOT NULL REFERENCES leads(id) ON DELETE CASCADE, at TEXT NOT NULL, user_name TEXT, text TEXT NOT NULL);

CREATE TABLE clicks (day TEXT NOT NULL, product_id INTEGER NOT NULL, mp TEXT NOT NULL, n INTEGER NOT NULL DEFAULT 0, PRIMARY KEY (day, product_id, mp));
CREATE TABLE searches (day TEXT NOT NULL, q TEXT NOT NULL, results INTEGER NOT NULL DEFAULT 0, n INTEGER NOT NULL DEFAULT 0, PRIMARY KEY (day, q));
CREATE TABLE rate (k TEXT PRIMARY KEY, n INTEGER NOT NULL, reset_at INTEGER NOT NULL);
SQL);
  }
  if ($from < 2) {
    // v2: прозрачность у фото (вырезанные товары и иллюстрации)
    $cols = array_column($pdo->query('PRAGMA table_info(photos)')->fetchAll(), 'name');
    if (!in_array('alpha', $cols, true)) $pdo->exec('ALTER TABLE photos ADD COLUMN alpha INTEGER NOT NULL DEFAULT 0');
  }
  if ($from < 3) {
    // v3: обложка категории (фото-сцена над списком товаров)
    $cols = array_column($pdo->query('PRAGMA table_info(categories)')->fetchAll(), 'name');
    if (!in_array('cover_id', $cols, true)) $pdo->exec('ALTER TABLE categories ADD COLUMN cover_id INTEGER');
  }
  $pdo->exec('PRAGMA user_version = ' . DB_SCHEMA_VERSION);
  $pdo->commit();
}

/** Простой ограничитель частоты: не больше $max событий за $window секунд на ключ */
function rate_hit($key, $max, $window) {
  $t = time();
  $r = q_one('SELECT n, reset_at FROM rate WHERE k = ?', [$key]);
  if (!$r || $r['reset_at'] < $t) { q('REPLACE INTO rate (k, n, reset_at) VALUES (?, 1, ?)', [$key, $t + $window]); return true; }
  if ($r['n'] >= $max) return false;
  q('UPDATE rate SET n = n + 1 WHERE k = ?', [$key]);
  return true;
}
