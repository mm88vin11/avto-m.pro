<?php
/**
 * Начальное наполнение из таблицы заказчика (assets-src/assortment.xlsx) и карточек на Wildberries.
 * Запускается один раз — при создании пустой базы. Дальше всё меняется в админке.
 */
if (!defined('AVTOM')) { http_response_code(404); exit; }

function seed_all(PDO $pdo) {
  $t = now();
  $ins = function ($table, $row) use ($pdo) {
    $cols = array_keys($row);
    $st = $pdo->prepare('INSERT INTO ' . $table . ' (' . implode(',', $cols) . ') VALUES (' . implode(',', array_fill(0, count($cols), '?')) . ')');
    $st->execute(array_values($row));
    return (int)$pdo->lastInsertId();
  };

  /* --- характеристики (фильтры) --- */
  $attr = [];
  foreach ([
    ['force', 'Выталкивающая сила', 'Н', 'number', 1, 1, 10],
    ['length', 'Длина', 'мм', 'number', 1, 1, 20],
    ['body_d', 'Диаметр корпуса', 'мм', 'number', 0, 1, 30],
    ['rod_d', 'Диаметр штока', 'мм', 'number', 0, 1, 40],
    ['volume', 'Объём', 'мл', 'number', 0, 1, 50],
    ['place', 'Место установки', '', 'text', 0, 1, 60],
  ] as [$code, $name, $unit, $type, $filter, $inCard, $sort]) {
    $attr[$code] = $ins('attributes', compact('code', 'name', 'unit', 'type', 'filter') + ['in_card' => $inCard, 'sort' => $sort]);
  }

  /* --- фото --- */
  $photo = function ($name) {
    $f = APP_DIR . '/seed/photos/' . $name . '.jpg';
    if (!is_file($f)) $f = APP_DIR . '/seed/photos/' . $name . '.png';
    if (!is_file($f)) return null;
    try { return image_import($f, basename($f)); } catch (Throwable $e) { log_err('seed photo ' . $name . ': ' . $e->getMessage()); return null; }
  };

  /* --- категории (фото — вырезанный товар на прозрачном фоне для плиток каталога) --- */
  // плитки — детали на прозрачном фоне, обложки — фото-сцены (иллюстрации категорий, не фото конкретных товаров)
  $cutStrut = $photo('tile-struts') ?: $photo('art-strut-pair');
  $cutTank = $photo('tile-tank') ?: $photo('art-tank');
  $coverTail = $photo('cover-tailgate');
  $coverHood = $photo('cover-hood');
  $photoBrackets = $photo('cover-brackets');
  $catStrut = $ins('categories', ['slug' => 'gazovye-upory-bagazhnika', 'name' => 'Газовые упоры багажника', 'h1' => 'Газовые упоры крышки багажника',
    'intro' => 'Продаются комплектом из двух штук. Подбор по марке, модели, году выпуска и кузову.',
    'description' => "Газовый упор поднимает крышку багажника плавно и без усилий, держит её открытой и не даёт резко захлопнуться. Если крышка перестала держаться или опускается сама — упоры пора менять, причём сразу оба.\n\nЧтобы подобрать упоры, важен кузов: у седана, хэтчбека и универсала одной модели упоры разной длины и силы. Проверьте оригинальный номер или выберите автомобиль в подборе.",
    'photo_id' => $cutStrut, 'cover_id' => $coverTail, 'sort' => 10, 'published' => 1, 'seo_title' => 'Газовые упоры багажника — купить комплект с подбором по авто',
    'seo_desc' => 'Газовые упоры крышки багажника LIFTAUTOPRO комплектом из двух штук. Подбор по марке, модели, году и кузову. Купить на Wildberries и Ozon.', 'updated_at' => $t]);
  $catTank = $ins('categories', ['slug' => 'rasshiritelnye-bachki', 'name' => 'Расширительные бачки', 'h1' => 'Расширительные бачки системы охлаждения',
    'intro' => 'Бачки AUTOPLASTIC с утолщёнными стенками. Подбор по марке, модели, году и двигателю.',
    'description' => "Расширительный бачок принимает лишнюю охлаждающую жидкость, когда она нагревается и расширяется. Со временем пластик стареет: появляются трещины у креплений, бачок вздувается и подтекает.\n\nБачки AUTOPLASTIC сделаны с утолщёнными стенками — они дольше служат в местах крепления и лучше держат давление в системе.",
    'photo_id' => $cutTank, 'sort' => 20, 'published' => 1, 'seo_title' => 'Расширительные бачки — купить с подбором по авто',
    'seo_desc' => 'Расширительные бачки AUTOPLASTIC для Ford, Volvo, Mazda и Renault. Утолщённые стенки, производство — Россия. Купить на Wildberries и Ozon.', 'updated_at' => $t]);
  $ins('categories', ['slug' => 'gazovye-upory-kapota', 'name' => 'Газовые упоры капота', 'h1' => 'Газовые упоры капота', 'intro' => 'Подбор по марке, модели и году выпуска.', 'description' => '', 'cover_id' => $coverHood, 'sort' => 30, 'published' => 1, 'updated_at' => $t]);
  $ins('categories', ['slug' => 'krepleniya-i-nakonechniki', 'name' => 'Крепления и наконечники', 'h1' => 'Крепления и наконечники для газовых упоров', 'intro' => 'Кронштейны, шаровые пальцы и наконечники.', 'description' => '', 'photo_id' => $photoBrackets, 'sort' => 40, 'published' => 1, 'updated_at' => $t]);

  /* --- марки --- */
  $logoPlast = $photo('logo-autoplastic');
  $brLift = $ins('brands', ['slug' => 'liftautopro', 'name' => 'LIFTAUTOPRO', 'tagline' => 'Газовые упоры для прямой замены',
    'description' => "LIFTAUTOPRO выпускает автомобильные газовые упоры — пневмоупоры для капота и багажника. Их ставят вместо изношенной штатной детали или дополнительно, чтобы открывать крышку было удобно и безопасно.\n\nУ производителя полный производственный цикл: больше 70 единиц современного оборудования, контроль качества от подбора комплектующих до проверки готовых изделий. Линейка постепенно расширяется моделями под конкретные марки автомобилей.",
    'facts' => json_encode([['Гарантия', '1 год'], ['Срок службы', '5 лет'], ['Ресурс', 'от 10 000 циклов'], ['Температура', 'от −45 до +60 °C']], JSON_UNESCAPED_UNICODE),
    'country' => '', 'sort' => 10, 'published' => 1, 'updated_at' => $t]);
  $brPlast = $ins('brands', ['slug' => 'autoplastic', 'name' => 'AUTOPLASTIC', 'tagline' => 'Расширительные бачки с утолщёнными стенками',
    'description' => "Главное отличие бачков AUTOPLASTIC — увеличенный срок службы за счёт более толстых стенок.\n\n- **Меньше протирается.** Посадочные места и металлические кронштейны постоянно трутся о пластик. Толстая стенка дольше выдерживает такой износ и не трескается.\n- **Держит давление.** Нагретая жидкость создаёт давление в системе охлаждения. Тонкие стенки вздуваются и быстрее разрушаются, а утолщённые дольше сохраняют форму.\n\nПроизводство — Россия.",
    'facts' => json_encode([['Стенки', 'утолщённые'], ['Производство', 'Россия'], ['В комплекте', '1 бачок']], JSON_UNESCAPED_UNICODE),
    'country' => 'Россия', 'logo_photo_id' => $logoPlast, 'sort' => 20, 'published' => 1, 'updated_at' => $t]);

  /* --- справочник автомобилей --- */
  $mk = []; $md = [];
  $cars = [
    'Ford' => ['форд', [
      ['Focus I', 'фокус 1', 1998, 2005, 'Седан, Хэтчбек, Универсал'], ['Focus II', 'фокус 2', 2004, 2011, 'Седан, Хэтчбек, Универсал'],
      ['Focus III', 'фокус 3', 2011, 2017, 'Седан, Хэтчбек, Универсал'], ['C-MAX', 'си макс с макс фокус с макс', 2003, 2010, 'Компактвэн'],
      ['C-MAX II', 'си макс 2 с макс 2', 2010, 2019, 'Компактвэн'], ['Grand C-MAX', 'гранд си макс', 2010, 2019, 'Компактвэн'],
      ['Kuga I', 'куга 1', 2008, 2012, 'Кроссовер'], ['Kuga II', 'куга 2', 2012, 2019, 'Кроссовер'], ['Tourneo Connect', 'турнео коннект', 2013, 2022, 'Компактвэн'],
    ]],
    'Volvo' => ['вольво', [['C30', 'с30', 2006, 2013, 'Хэтчбек'], ['S40 II', 'с40 2', 2004, 2012, 'Седан'], ['V50', 'в50', 2004, 2012, 'Универсал']]],
    'Mazda' => ['мазда', [['3 (BK)', 'мазда 3 бк', 2003, 2009, 'Седан, Хэтчбек'], ['3 (BL)', 'мазда 3 бл', 2008, 2014, 'Седан, Хэтчбек']]],
    'Renault' => ['рено', [
      ['Logan I', 'логан 1', 2004, 2015, 'Седан, Универсал'], ['Sandero', 'сандеро', 2008, 2013, 'Хэтчбек'], ['Duster', 'дастер', 2010, 2018, 'Кроссовер'],
      ['Symbol I', 'симбол 1 талия', 2000, 2009, 'Седан'], ['Symbol II', 'симбол 2 талия 2', 2008, 2014, 'Седан'], ['Kangoo', 'кангу канго', 1997, 2018, 'Компактвэн, Фургон'], ['Clio II', 'клио 2', 1998, 2016, 'Хэтчбек, Седан, Фургон'],
    ]],
  ];
  $s = 0;
  foreach ($cars as $make => [$alias, $models]) {
    $mk[$make] = $ins('makes', ['slug' => slugify($make), 'name' => $make, 'aliases' => $alias, 'sort' => $s += 10]);
    $ms = 0;
    foreach ($models as [$name, $al, $yf, $yt, $bodies]) {
      $md["$make|$name"] = $ins('models', ['make_id' => $mk[$make], 'slug' => slugify($name), 'name' => $name, 'aliases' => $al, 'year_from' => $yf, 'year_to' => $yt, 'bodies' => $bodies, 'sort' => $ms += 10]);
    }
  }

  /* --- товары --- */
  $strutDesc = "Газовые упоры поднимают крышку багажника плавно и без усилий, надёжно держат её открытой и не дают резко захлопнуться. Мягкий ход в крайних точках бережёт петли, уплотнители и саму крышку.\n\n- ставятся вместо штатных упоров;\n- работают при температуре от −45 до +60 °C;\n- не требуют обслуживания;\n- гарантия производителя — 1 год, расчётный срок службы — 5 лет, не меньше 10 000 циклов.\n\nВ комплекте два упора — менять их лучше парой, чтобы крышка не перекашивалась.";
  $tankDesc = "Расширительный бачок системы охлаждения двигателя. Стенки толще, чем у обычного бачка, поэтому он дольше служит:\n\n- меньше протирается в местах крепления к кронштейнам;\n- лучше держит давление нагретой жидкости и не вздувается;\n- производство — Россия.\n\nПеред покупкой сверьте оригинальный номер или пришлите нам VIN-номер — проверим применимость.";

  $products = [
    ['LAP-10488', 'Газовые упоры багажника LIFTAUTOPRO для Ford Focus II седан', $catStrut, $brLift, '1376673, 5M51F406A10AB, 1336159, 1692162', ['force' => 510, 'length' => 279], [['Ford|Focus II', 2004, 2011, 'Седан', '']], ['lap-10488-1'], 'Крышка багажника', '', '', 10],
    ['LAP-10262', 'Газовые упоры багажника LIFTAUTOPRO для Ford Focus II хэтчбек', $catStrut, $brLift, '1321021, 4M51A406A10AB, 1502096, 8M51A406A10AB, 1492364, 1684312', ['force' => 530, 'length' => 485], [['Ford|Focus II', 2004, 2011, 'Хэтчбек', '']], ['lap-10262-1'], 'Крышка багажника', '', '', 20],
    ['LAP-10375', 'Газовые упоры багажника LIFTAUTOPRO для Ford Focus II универсал', $catStrut, $brLift, '1349309, 4M51N406A10AB, 1539291, 4M51N406A10AC, 1336230, 1684275', ['force' => 420, 'length' => 585], [['Ford|Focus II', 2004, 2011, 'Универсал', '']], ['lap-10375-1'], 'Крышка багажника', '', '', 30],
    ['LAP-10995', 'Газовые упоры багажника LIFTAUTOPRO для Ford Focus I седан', $catStrut, $brLift, '1124003, XS41F406A10AF, 1071732, 1072394, 1092108, 1114031', ['force' => 465, 'length' => 280], [['Ford|Focus I', 1998, 2005, 'Седан', '']], [], 'Крышка багажника', '', '', 40],
    ['LAP-10965', 'Газовые упоры багажника LIFTAUTOPRO для Ford Focus I хэтчбек', $catStrut, $brLift, '3M51A406A10AB, XS41A406A10AF, 1313218, 1150277, 1229461', ['force' => 700, 'length' => 455], [['Ford|Focus I', 1998, 2005, 'Хэтчбек', '']], [], 'Крышка багажника', '', '', 50],
    ['LAP-10993', 'Газовые упоры багажника LIFTAUTOPRO для Ford Focus I универсал', $catStrut, $brLift, '1136483, 1146649, XS41N406A10AH, XS41N406A10AJ', ['force' => 450, 'length' => 525], [['Ford|Focus I', 1998, 2005, 'Универсал', '']], [], 'Крышка багажника', '', '', 60],
    ['AV-11001', 'Расширительный бачок AUTOPLASTIC для Ford Focus II', $catTank, $brPlast,
      '1224268, 1230994, 1330955, 1355243, 1425193, 3M5H8K218AH, 3M5H8K218AJ, 3M5K8K218AC, LF8B15350B, 3M5H8K218AF, 30680957, 30636701, 30722615, 30776150, LF5015350C, LF8B15360A, LFR515360',
      ['volume' => 1000], [['Ford|Focus II', 2004, 2011, '', '1.4, 1.6, 1.8, 2.0'], ['Ford|C-MAX', 2007, 2010, '', ''], ['Ford|Kuga I', 2005, 2012, '', ''],
        ['Volvo|C30', 2006, 2012, '', '1.6, 1.8, 2.0'], ['Volvo|S40 II', 2004, 2012, '', ''], ['Volvo|V50', 2004, 2012, '', ''], ['Mazda|3 (BK)', 2003, 2009, '', ''], ['Mazda|3 (BL)', 2008, 2014, '', '']],
      ['av-11001-1', 'av-11001-2', 'av-11001-3', 'av-11001-4'], 'Моторный отсек', 'https://www.wildberries.ru/catalog/238466344/detail.aspx', 'https://www.ozon.ru/product/bachok-rasshiritelnyy-dlya-ford-fokus-2-1610686752/', 70],
    ['AV-11002', 'Расширительный бачок AUTOPLASTIC для Ford Focus III', $catTank, $brPlast, '1708512, 1717053, CV6Z8A080A, 8V618K218AE',
      ['volume' => 1000], [['Ford|Focus III', 2011, 2017, '', '1.0, 1.5, 1.6, 2.0'], ['Ford|C-MAX II', 2010, 2019, '', '1.0, 1.6, 2.0'], ['Ford|Grand C-MAX', 2010, 2019, '', '1.0, 1.6, 2.0'],
        ['Ford|Kuga II', 2013, 2018, '', '1.5, 1.6, 2.0'], ['Ford|Tourneo Connect', 2013, 2022, '', '1.0, 1.6']],
      ['av-11002-1', 'av-11002-2', 'av-11002-3'], 'Моторный отсек', 'https://www.wildberries.ru/catalog/238465949/detail.aspx', 'https://www.ozon.ru/product/bachok-rasshiritelnyy-ford-focus-3-1610692760/', 80],
    ['AV-11023', 'Расширительный бачок AUTOPLASTIC для Renault Logan, Sandero, Duster', $catTank, $brPlast, '7701470460, 7701466975',
      ['volume' => 1000], [['Renault|Logan I', 2004, 2015, '', ''], ['Renault|Sandero', 2008, 2013, '', ''], ['Renault|Duster', 2010, 2018, '', ''], ['Renault|Symbol I', 2000, 2009, '', ''],
        ['Renault|Symbol II', 2008, 2014, '', ''], ['Renault|Kangoo', 1997, 2018, '', ''], ['Renault|Clio II', 1998, 2016, '', '']],
      ['av-11023-1', 'av-11023-2', 'av-11023-3', 'av-11023-4'], 'Моторный отсек', 'https://www.wildberries.ru/catalog/1995200521/detail.aspx', 'https://www.ozon.ru/product/bachok-rasshiritelnyy-renault-6452205460/', 90],
  ];
  $short = [
    'AV-11001' => 'Также подходит для Ford C-MAX, Kuga I, Volvo C30, S40 II, V50 и Mazda 3.',
    'AV-11002' => 'Также подходит для Ford C-MAX II, Grand C-MAX, Kuga II и Tourneo Connect.',
    'AV-11023' => 'Также подходит для Renault Symbol, Kangoo и Clio II.',
  ];
  foreach ($products as [$sku, $name, $cat, $brand, $oem, $attrs, $compat, $photos, $place, $wb, $ozon, $sort]) {
    $isStrut = $cat === $catStrut;
    $pid = $ins('products', [
      'slug' => slugify($name . ' ' . $sku), 'sku' => $sku, 'sku_norm' => norm_code($sku), 'name' => $name, 'category_id' => $cat, 'brand_id' => $brand,
      'short' => $short[$sku] ?? 'Комплект из двух упоров. Ставятся вместо штатных.', 'description' => $isStrut ? $strutDesc : $tankDesc,
      'oem' => $oem, 'oem_norm' => implode(' ', array_map('norm_code', list_split($oem))), 'kit' => $isStrut ? '2 шт.' : '1 шт.',
      'wb_url' => $wb, 'ozon_url' => $ozon, 'stock' => 'request', 'published' => 1, 'sort' => $sort, 'created_at' => $t, 'updated_at' => $t, 'published_at' => $t,
    ]);
    $attrs['place'] = $place;
    if ($isStrut) { $attrs['body_d'] = 18; $attrs['rod_d'] = 8; }
    foreach ($attrs as $code => $v) {
      $pdo->prepare('INSERT INTO product_attrs (product_id, attribute_id, value_num, value_text) VALUES (?, ?, ?, ?)')->execute([$pid, $attr[$code], is_numeric($v) ? $v : null, is_numeric($v) ? null : $v]);
    }
    foreach ($compat as $i => [$key, $yf, $yt, $bodies, $engines]) {
      $ins('compat', ['product_id' => $pid, 'model_id' => $md[$key], 'year_from' => $yf, 'year_to' => $yt, 'bodies' => $bodies, 'engines' => $engines, 'sort' => $i]);
    }
    foreach ($photos as $i => $ph) {
      $id = $photo($ph);
      if ($id) $pdo->prepare('INSERT INTO product_photos (product_id, photo_id, sort, alt) VALUES (?, ?, ?, ?)')->execute([$pid, $id, $i, $name . ($i ? ', фото ' . ($i + 1) : '')]);
    }
  }

  /* --- главная: картинки баннера и блоков --- */
  $home = content_defaults('home');
  $home['slides'] = seed_banner_slides($photo);
  $home['cards']['params_img'] = $photo('tile-params');
  $home['cards']['lead_img'] = $photo('tile-lead');
  // плитка «Подбор по автомобилю» — 3D-машина из «Картинок сайта»; фото машины с открытым багажником не ставим
  $tanksCover = $photo('cover-tanks');
  $pop = content_defaults('popup'); $pop['img_id'] = $home['cards']['lead_img'];
  $pdo->prepare('REPLACE INTO settings (key, value, updated_at) VALUES (?, ?, ?)')->execute(['content.popup', json_encode($pop, JSON_UNESCAPED_UNICODE), $t]);
  if ($tanksCover) $pdo->prepare('UPDATE categories SET cover_id = ? WHERE slug = ?')->execute([$tanksCover, 'rasshiritelnye-bachki']);
  $pdo->prepare('REPLACE INTO settings (key, value, updated_at) VALUES (?, ?, ?)')->execute(['content.home', json_encode($home, JSON_UNESCAPED_UNICODE), $t]);
}

/** Баннеры главной: готовые картинки заказчика, весь баннер — ссылка. Подпись — текст с баннера (для поиска и экранного диктора). */
function seed_banner_slides($photo) {
  $out = [];
  foreach ([
    ['banner-1', 'На твою машину — автозапчасти. Подобрать по авто', '/podbor'],
    ['banner-2', 'Газовые упоры. Перейти в каталог', '/catalog/gazovye-upory-bagazhnika'],
    ['banner-3', 'Расширительные бачки. Перейти в каталог', '/catalog/rasshiritelnye-bachki'],
  ] as [$file, $title, $url]) {
    $id = $photo($file);
    if ($id) $out[] = ['theme' => 'navy', 'title' => $title, 'sub' => '', 'text' => '', 'chips' => '', 'btn' => '', 'url' => $url, 'btn2' => '', 'url2' => '', 'cat' => '', 'bg_id' => null, 'bg_m_id' => null, 'img_id' => null, 'banner_id' => $id, 'banner_m_id' => null];
  }
  return $out;
}

/**
 * Обновление уже работающего сайта до графики от 26.11:
 * новые картинки плиток заменяют старые везде, где стояли старые (категории, блоки главной, окно, черновики),
 * баннеры заказчика встают в карусель (вместо стартовых слайдов; если слайды меняли в админке — добавляются перед ними).
 */
function seed_upgrade_2611() {
  $photo = function ($name) {
    foreach (['png', 'jpg'] as $ext) {
      $f = APP_DIR . '/seed/photos/' . $name . '.' . $ext;
      if (is_file($f)) { try { return image_import($f, basename($f)); } catch (Throwable $e) { log_err('upgrade photo ' . $name . ': ' . $e->getMessage()); } }
    }
    return null;
  };
  $swap = [];
  // прежние картинки в базе записаны под старыми именами файлов
  foreach (['gen3-struts' => 'tile-struts', 'gen3-tank' => 'tile-tank', 'gen3-car' => 'tile-car', 'gen3-card-params' => 'tile-params', 'gen3-card-lead' => 'tile-lead'] as $was => $name) {
    $old = array_map('intval', array_column(q_all('SELECT id FROM photos WHERE orig_name = ?', [$was . '.png']), 'id'));
    if (!$old) continue;
    $new = $photo($name);
    if ($new) foreach ($old as $o) $swap[$o] = $new;
  }
  if ($swap) {
    foreach ($swap as $o => $n) {
      q('UPDATE categories SET photo_id = ? WHERE photo_id = ?', [$n, $o]);
      q('UPDATE categories SET cover_id = ? WHERE cover_id = ?', [$n, $o]);
    }
    $walk = function ($v) use (&$walk, $swap) {
      if (!is_array($v)) return $v;
      foreach ($v as $k => $x) {
        if (is_array($x)) $v[$k] = $walk($x);
        elseif (is_int($x) && isset($swap[$x]) && preg_match('~(_id|_img)$~', (string)$k)) $v[$k] = $swap[$x];
      }
      return $v;
    };
    foreach (q_all("SELECT key, value FROM settings WHERE key LIKE 'content.%'") as $r) {
      $d = json_decode($r['value'], true);
      if (is_array($d)) setting_save($r['key'], $walk($d));
    }
    foreach (q_all("SELECT entity_id, data FROM drafts WHERE entity = 'content'") as $r) {
      $d = json_decode($r['data'], true);
      if (is_array($d)) q("UPDATE drafts SET data = ? WHERE entity = 'content' AND entity_id = ?", [json_encode($walk($d), JSON_UNESCAPED_UNICODE), $r['entity_id']]);
    }
  }
  $home = setting_raw('content.home') ?: content_defaults('home');
  $banners = seed_banner_slides($photo);
  if ($banners) {
    $titles = array_map(fn($x) => $x['title'] ?? '', $home['slides'] ?? []);
    $stock = $titles === ['Автозапчасти', 'Расширительные бачки', 'Газовые упоры'];
    $home['slides'] = $stock ? $banners : array_merge($banners, $home['slides'] ?? []);
    setting_save('content.home', $home);
  }
}

/**
 * Правки 28.11: короче тексты главной, баннер листается раз в 3 секунды, блок «Когда пора менять» уходит с главной
 * (остаётся в админке). Меняем только то, что совпадает со старым стартовым текстом, — правки владельца не трогаем.
 */
function seed_upgrade_2811() {
  $fix = function ($key, callable $fn) {
    $saved = setting_raw('content.' . $key);
    if (is_array($saved)) setting_save('content.' . $key, $fn($saved));
    $d = q_val("SELECT data FROM drafts WHERE entity = 'content' AND entity_id = ?", [$key]);
    if ($d && is_array($dd = json_decode($d, true))) q("UPDATE drafts SET data = ? WHERE entity = 'content' AND entity_id = ?", [json_encode($fn($dd), JSON_UNESCAPED_UNICODE), $key]);
  };
  $was = function (&$arr, $path, $old, $new) {
    $ref = &$arr; foreach (explode('.', $path) as $p) { if (!is_array($ref) || !array_key_exists($p, $ref)) return; $ref = &$ref[$p]; }
    if ($ref === $old) $ref = $new;
  };
  $fix('home', function ($h) use ($was) {
    if (!isset($h['slider_sec'])) $h['slider_sec'] = '3';
    $was($h, 'cards.params_text', 'По марке, модели и году — или по длине и усилию', 'По длине и усилию');
    $was($h, 'cards.lead_text', 'Не уверены, что подойдёт? Перезвоним и поможем', 'Перезвоним и подберём');
    $was($h, 'picker.lead', 'Выберите марку, модель и год выпуска — покажем подходящие детали.', 'Покажем детали, которые подходят');
    $was($h, 'quiz.title', 'Не знаете, что искать? Ответьте на пару вопросов', 'Не знаете, что искать?');
    $was($h, 'quiz.lead', 'Покажем детали, которые подходят к вашей машине. Можно начать с признаков поломки или с номера со старой детали.', 'Ответьте на пару вопросов.');
    $was($h, 'about.text', 'AVTO-M продаёт автозапчасти для автовладельцев и автосервисов: газовые упоры, расширительные бачки, крепления, а дальше — новые категории. Наш принцип простой: качественная деталь не должна стоить дорого. Работаем напрямую с производителями и помогаем с подбором.', 'Продаём автозапчасти для автовладельцев и автосервисов. Работаем напрямую с производителями, поэтому держим доступную цену и отвечаем за качество.');
    if (!empty($h['blocks'])) foreach ($h['blocks'] as &$b) if (($b['id'] ?? '') === 'signs') $b['on'] = false;
    unset($b);
    // фото машины с открытым багажником в плитке подбора заменяется 3D-машиной
    $pi = (int)($h['categories']['pick_img'] ?? 0);
    if ($pi && in_array(q_val('SELECT orig_name FROM photos WHERE id = ?', [$pi]), ['tile-car.png', 'gen3-car.png'], true)) $h['categories']['pick_img'] = null;
    return $h;
  });
  $fix('legal_cookies', function ($c) {
    if (isset($c['body'])) $c['body'] = str_replace(
      ['Карта на странице контактов загружается только после нажатия кнопки «Показать карту». После этого «Яндекс Карты» могут установить свои cookie.', 'Он подключается, только если вы нажали «Разрешить» в окне о cookie.'],
      ['На странице «Контакты» встроена карта сервиса «Яндекс Карты» (ООО «Яндекс»), чтобы было видно, как нас найти. Карта загружается, когда вы открываете эту страницу, и может установить свои cookie.', 'Он подключается, только если вы нажали «Принять» в плашке о cookie.'],
      $c['body']);
    return $c;
  });
  $fix('about', function ($a) use ($was) {
    $was($a, 'lead', 'AVTO-M продаёт автозапчасти и автотовары для автовладельцев и профессионалов. Девиз — «Качество по доступной цене».', 'Продаём автозапчасти и автотовары для автовладельцев и автосервисов.');
    return $a;
  });
}

