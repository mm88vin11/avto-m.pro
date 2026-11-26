<?php
if (!defined('AVTOM')) { http_response_code(404); exit; }
$u = admin_user();
$since = date('Y-m-d', strtotime('-29 days'));
$leadsNew = (int)q_val("SELECT COUNT(*) FROM leads WHERE status = 'new'");
$leads30 = (int)q_val('SELECT COUNT(*) FROM leads WHERE created_at >= ?', [$since]);
$clicks = q_all('SELECT mp, SUM(n) AS n FROM clicks WHERE day >= ? GROUP BY mp', [$since]);
$cl = ['wb' => 0, 'ozon' => 0]; foreach ($clicks as $c) $cl[$c['mp']] = (int)$c['n'];
$prodPub = (int)q_val('SELECT COUNT(*) FROM products WHERE published = 1');
$prodAll = (int)q_val('SELECT COUNT(*) FROM products');
$noLinks = q_all("SELECT id, name, sku FROM products WHERE published = 1 AND COALESCE(wb_url,'') = '' AND COALESCE(ozon_url,'') = '' ORDER BY sort LIMIT 8");
$noPhoto = q_all('SELECT id, name, sku FROM products p WHERE published = 1 AND NOT EXISTS (SELECT 1 FROM product_photos pp WHERE pp.product_id = p.id) ORDER BY sort LIMIT 8');
$drafts = q_all("SELECT d.entity, d.entity_id, d.updated_at, p.name FROM drafts d LEFT JOIN products p ON d.entity = 'product' AND p.id = d.entity_id ORDER BY d.updated_at DESC LIMIT 8");
$topClicks = q_all('SELECT c.product_id, p.name, p.sku, SUM(CASE WHEN mp = \'wb\' THEN n ELSE 0 END) AS wb, SUM(CASE WHEN mp = \'ozon\' THEN n ELSE 0 END) AS ozon, SUM(n) AS t FROM clicks c JOIN products p ON p.id = c.product_id WHERE day >= ? GROUP BY c.product_id ORDER BY t DESC LIMIT 6', [$since]);
$zero = q_all('SELECT q, SUM(n) AS n FROM searches WHERE results = 0 AND day >= ? GROUP BY q ORDER BY n DESC LIMIT 8', [$since]);
$lastLeads = q_all('SELECT * FROM leads ORDER BY id DESC LIMIT 6');
$days = []; for ($i = 13; $i >= 0; $i--) $days[date('Y-m-d', strtotime("-$i days"))] = 0;
foreach (q_all('SELECT day, SUM(n) AS n FROM clicks WHERE day >= ? GROUP BY day', [array_key_first($days)]) as $r) $days[$r['day']] = (int)$r['n'];
$maxD = max(1, max($days));
$contentKeys = content_keys();
a_head('Сводка', '');
$hour = (int)date('G');
?>
<div class="a-page-head"><div><h1><?= $hour < 5 ? 'Доброй ночи' : ($hour < 12 ? 'Доброе утро' : ($hour < 18 ? 'Добрый день' : 'Добрый вечер')) ?>, <?= h($u['name']) ?></h1><p class="a-muted">Сводка за 30 дней · <?= h(ru_date(now(), false)) ?></p></div>
  <div class="a-head-btns"><a class="a-btn a-btn-primary" href="/admin/products/new"><?= a_icon('plus') ?>Добавить товар</a></div></div>

<div class="a-stats">
  <a class="a-stat<?= $leadsNew ? ' is-hot' : '' ?>" href="/admin/leads?status=new"><small>Новые заявки</small><b><?= $leadsNew ?></b><span>всего за 30 дней: <?= $leads30 ?></span></a>
  <div class="a-stat"><small>Переходы на Wildberries</small><b><?= $cl['wb'] ?></b><span>по кнопкам на сайте</span></div>
  <div class="a-stat"><small>Переходы на Ozon</small><b><?= $cl['ozon'] ?></b><span>по кнопкам на сайте</span></div>
  <a class="a-stat" href="/admin/products"><small>Товары на сайте</small><b><?= $prodPub ?></b><span>из <?= $prodAll ?> в базе</span></a>
</div>

<div class="a-grid2">
  <section class="a-card">
    <h2>Переходы на маркетплейсы за 2 недели</h2>
    <div class="a-bars" role="img" aria-label="Переходы по дням">
      <?php foreach ($days as $dd => $n): ?><div class="a-bar" title="<?= h(date('d.m', strtotime($dd))) ?>: <?= $n ?>"><i style="height:<?= round($n / $maxD * 100) ?>%"></i><small><?= date('j', strtotime($dd)) ?></small></div><?php endforeach; ?>
    </div>
    <?php if ($topClicks): ?>
    <table class="a-table a-table-compact"><thead><tr><th>Товар</th><th>WB</th><th>Ozon</th></tr></thead><tbody>
      <?php foreach ($topClicks as $t): ?><tr><td><a href="/admin/products/<?= (int)$t['product_id'] ?>"><?= h($t['name']) ?></a><small class="a-muted"> <?= h($t['sku']) ?></small></td><td><?= (int)$t['wb'] ?></td><td><?= (int)$t['ozon'] ?></td></tr><?php endforeach; ?>
    </tbody></table>
    <?php else: ?><p class="a-muted">Пока переходов нет — они появятся, когда посетители начнут нажимать кнопки маркетплейсов.</p><?php endif; ?>
  </section>
  <section class="a-card">
    <div class="a-card-head"><h2>Последние заявки</h2><a href="/admin/leads">Все заявки</a></div>
    <?php if ($lastLeads): ?><ul class="a-list">
      <?php foreach ($lastLeads as $l): ?><li><a href="/admin/leads/<?= (int)$l['id'] ?>"><span class="a-pill a-st-<?= h($l['status']) ?>"><?= h(lead_status_label($l['status'])) ?></span><b><?= h($l['name']) ?></b><span class="a-muted"><?= h(lead_kinds()[$l['kind']] ?? '') ?> · <?= h(ru_date($l['created_at'])) ?></span></a></li><?php endforeach; ?>
    </ul><?php else: ?><p class="a-muted">Заявок пока нет.</p><?php endif; ?>
  </section>
</div>

<div class="a-grid3">
  <section class="a-card">
    <h2>Требуют внимания</h2>
    <?php if (!$noLinks && !$noPhoto): ?><p class="a-muted">Всё в порядке: у всех товаров есть фото и ссылки.</p><?php endif; ?>
    <?php if ($noLinks): ?><p class="a-sub">Без ссылок на маркетплейсы</p><ul class="a-mini"><?php foreach ($noLinks as $p): ?><li><a href="/admin/products/<?= (int)$p['id'] ?>#mp"><?= h($p['sku']) ?> · <?= h($p['name']) ?></a></li><?php endforeach; ?></ul><?php endif; ?>
    <?php if ($noPhoto): ?><p class="a-sub">Без фото</p><ul class="a-mini"><?php foreach ($noPhoto as $p): ?><li><a href="/admin/products/<?= (int)$p['id'] ?>#photos"><?= h($p['sku']) ?> · <?= h($p['name']) ?></a></li><?php endforeach; ?></ul><?php endif; ?>
  </section>
  <section class="a-card">
    <h2>Неопубликованные черновики</h2>
    <?php if ($drafts): ?><ul class="a-mini"><?php foreach ($drafts as $d): ?><li><a href="<?= $d['entity'] === 'product' ? '/admin/products/' . (int)$d['entity_id'] : '/admin/pages/' . h($d['entity_id']) ?>"><?= h($d['entity'] === 'product' ? ($d['name'] ?: 'Товар') : ('Страница «' . ($contentKeys[$d['entity_id']] ?? $d['entity_id']) . '»')) ?></a> <small class="a-muted"><?= h(ru_date($d['updated_at'])) ?></small></li><?php endforeach; ?></ul>
    <?php else: ?><p class="a-muted">Черновиков нет — всё опубликовано.</p><?php endif; ?>
  </section>
  <section class="a-card">
    <h2>Искали и не нашли</h2>
    <?php if ($zero): ?><ul class="a-mini"><?php foreach ($zero as $z): ?><li>«<?= h($z['q']) ?>» <small class="a-muted">× <?= (int)$z['n'] ?></small></li><?php endforeach; ?></ul><p class="a-hint">Подсказка: добавьте такие товары или впишите варианты названий в «Автомобили» → поле «Как ещё ищут».</p>
    <?php else: ?><p class="a-muted">Пустых поисков нет.</p><?php endif; ?>
  </section>
</div>
<?php a_foot();
