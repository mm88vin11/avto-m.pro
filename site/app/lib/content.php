<?php
if (!defined('AVTOM')) { http_response_code(404); exit; }

/* ---------- настройки и тексты ---------- */

function setting_raw($key) {
  static $cache = [];
  if (!array_key_exists($key, $cache)) {
    $v = q_val('SELECT value FROM settings WHERE key = ?', [$key]);
    $cache[$key] = $v === null ? null : json_decode($v, true);
  }
  return $cache[$key];
}
function setting_save($key, $value) {
  q('REPLACE INTO settings (key, value, updated_at) VALUES (?, ?, ?)', [$key, json_encode($value, JSON_UNESCAPED_UNICODE), now()]);
}

function site_defaults() {
  return [
    'name' => 'AVTO-M',
    'tagline' => 'Качество по доступной цене',
    'phone' => '+7 963 759-00-02',
    'email' => 'info@avto-m.pro',
    'address' => 'Московская область, г. Балашиха, ул. Граничная, д. 36, помещение 4',
    'address_short' => 'Балашиха, ул. Граничная, 36',
    'city' => 'Балашиха',
    'region' => 'Московская область',
    'street' => 'ул. Граничная, д. 36, помещение 4',
    'map_query' => 'Московская область, Балашиха, улица Граничная, 36',
    'hours' => 'Ежедневно с 9:00 до 18:00',
    'hours_short' => 'Пн–Вс 9:00–18:00',
    'hours_open' => '09:00', 'hours_close' => '18:00',
    'org_name' => 'ИП Чернов Михаил Александрович',
    'org_short' => 'ИП Чернов М. А.',
    'inn' => '623410045296',
    'ogrnip' => '322508100258555',
    'legal_address' => '',
    'privacy_email' => '',
    'notify_emails' => 'arsapel@yandex.ru',
    'metrika_id' => '',
    'yandex_verification' => '',
    'wb_store_url' => '',
    'ozon_store_url' => '',
    'vk_url' => '', 'tg_url' => '', 'max_url' => '',
    'bodies' => ['Седан', 'Хэтчбек', 'Универсал', 'Лифтбек', 'Кроссовер', 'Минивэн', 'Компактвэн', 'Фургон', 'Купе'],
    'lead_statuses' => ['new' => 'Новая', 'work' => 'В работе', 'wait' => 'Ждём клиента', 'done' => 'Выполнена', 'cancel' => 'Отменена'],
    'consent_version' => '2026-11-26',
  ];
}
function site($k = null) {
  static $s = null;
  if ($s === null) $s = array_replace(site_defaults(), setting_raw('site') ?: []);
  return $k === null ? $s : ($s[$k] ?? '');
}

/** Ключи текстов, которые редактируются с черновиком и предпросмотром */
function content_keys() {
  return [
    'home' => 'Главная',
    'about' => 'О компании',
    'partners' => 'Преимущества и сотрудничество',
    'contacts' => 'Контакты',
    'howto' => 'Как купить',
    'faq' => 'Вопросы и ответы',
    'legal_privacy' => 'Политика обработки персональных данных',
    'legal_consent' => 'Согласие на обработку персональных данных',
    'legal_cookies' => 'Использование файлов cookie',
    'legal_terms' => 'Правовая информация',
  ];
}

function content($key) {
  static $cache = [];
  $pv = preview_mode();
  $ck = $key . ($pv ? ':pv' : '');
  if (isset($cache[$ck])) return $cache[$ck];
  $def = content_defaults($key);
  $data = null;
  if ($pv) {
    $d = q_val("SELECT data FROM drafts WHERE entity = 'content' AND entity_id = ?", [$key]);
    if ($d) $data = json_decode($d, true);
  }
  if ($data === null) $data = setting_raw('content.' . $key);
  $out = is_array($data) ? content_merge($def, $data) : $def;
  return $cache[$ck] = $out;
}
/** Слияние с умолчаниями: списки берутся целиком из сохранённых данных, словари — по ключам */
function content_merge($def, $data) {
  if (!is_array($def) || !is_array($data)) return $data ?? $def;
  if (array_is_list($def) || array_is_list($data)) return $data;
  foreach ($data as $k => $v) $def[$k] = array_key_exists($k, $def) ? content_merge($def[$k], $v) : $v;
  return $def;
}

function preview_mode() {
  static $pv = null;
  if ($pv === null) $pv = !empty($_GET['preview']) && admin_user_quiet();
  return $pv;
}

function content_defaults($key) {
  static $all = null;
  if ($all === null) {
    $all = require APP_DIR . '/seed/content.php';
  }
  return $all[$key] ?? [];
}

/** Список блоков главной, которые можно переставлять и скрывать */
function home_block_names() {
  return [
    'picker' => 'Подбор по автомобилю',
    'categories' => 'Каталог',
    'products' => 'Товары',
    'brands' => 'Наши марки',
    'howto' => 'Как купить',
    'advantages' => 'Преимущества',
    'about' => 'О компании',
    'faq' => 'Вопросы и ответы',
    'help' => 'Помощь с подбором',
  ];
}
