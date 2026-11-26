<?php
if (!defined('AVTOM')) { http_response_code(404); exit; }
$sub = $a[1] ?? '';
$statuses = site('lead_statuses');
$kinds = lead_kinds();

if ($sub !== '' && ctype_digit($sub)) {
  $id = (int)$sub;
  $l = q_one('SELECT * FROM leads WHERE id = ?', [$id]) ?: redirect('/admin/leads');
  if (a_require_post()) {
    $u = admin_user();
    $act = $_POST['action'] ?? '';
    if ($act === 'status' && isset($statuses[$_POST['status'] ?? ''])) {
      if ($_POST['status'] !== $l['status']) { q('UPDATE leads SET status = ?, updated_at = ? WHERE id = ?', [$_POST['status'], now(), $id]); q_ins('lead_events', ['lead_id' => $id, 'at' => now(), 'user_name' => $u['name'], 'text' => 'Статус: ' . $statuses[$_POST['status']]]); }
      flash('Статус обновлён.');
    }
    if ($act === 'note' && trim($_POST['note'] ?? '') !== '') { q_ins('lead_events', ['lead_id' => $id, 'at' => now(), 'user_name' => $u['name'], 'text' => mb_substr(trim($_POST['note']), 0, 2000)]); flash('Заметка добавлена.'); }
    if ($act === 'resend') { $e = lead_notify($id); q('UPDATE leads SET mail_status = ? WHERE id = ?', [$e ? 'Ошибка: ' . mb_substr($e, 0, 200) : 'Отправлено ' . date('d.m H:i'), $id]); flash($e ? 'Письмо не ушло: ' . h($e) : 'Письмо отправлено повторно.', $e ? 'err' : 'ok'); }
    if ($act === 'delete' && is_owner()) { q('DELETE FROM leads WHERE id = ?', [$id]); flash('Заявка удалена.'); redirect('/admin/leads'); }
    redirect('/admin/leads/' . $id);
  }
  if ($l['status'] === 'new') { q("UPDATE leads SET status = 'work', updated_at = ? WHERE id = ?", [now(), $id]); q_ins('lead_events', ['lead_id' => $id, 'at' => now(), 'user_name' => admin_user()['name'], 'text' => 'Открыта — статус «В работе»']); $l['status'] = 'work'; }
  $items = json_decode($l['items'] ?: '[]', true);
  $events = q_all('SELECT * FROM lead_events WHERE lead_id = ? ORDER BY id DESC', [$id]);
  $utm = json_decode($l['utm'] ?: '[]', true);
  a_head('Заявка №' . $id, '/leads');
  ?>
  <div class="a-page-head"><div><a class="a-back" href="/admin/leads"><?= a_icon('arrow-left') ?>Заявки</a><h1>Заявка №<?= $id ?> · <?= h($kinds[$l['kind']] ?? '') ?></h1><p class="a-muted"><?= h(ru_date($l['created_at'])) ?></p></div></div>
  <div class="a-edit-grid">
    <div class="a-edit-main">
      <section class="a-card">
        <div class="a-lead-contact">
          <div><small>Имя</small><b><?= h($l['name']) ?></b></div>
          <div><small>Телефон</small><b><a href="<?= h(phone_href($l['phone'])) ?>"><?= h($l['phone']) ?></a></b></div>
          <?php if ($l['email']): ?><div><small>Почта</small><b><a href="mailto:<?= h($l['email']) ?>"><?= h($l['email']) ?></a></b></div><?php endif; ?>
          <?php if ($l['car']): ?><div><small>Автомобиль</small><b><?= h($l['car']) ?></b></div><?php endif; ?>
        </div>
        <div class="a-btns"><a class="a-btn a-btn-primary" href="<?= h(phone_href($l['phone'])) ?>"><?= a_icon('phone') ?>Позвонить</a><button class="a-btn a-btn-soft" type="button" data-copy="<?= h($l['phone']) ?>"><?= a_icon('copy') ?>Скопировать номер</button></div>
        <?php if ($l['comment']): ?><h3 class="a-sub">Комментарий</h3><p class="a-quote"><?= nl2br(h($l['comment'])) ?></p><?php endif; ?>
        <?php if ($items): ?><h3 class="a-sub">Детали</h3><table class="a-table"><thead><tr><th>Артикул</th><th>Товар</th><th>Кол-во</th><th>Совместимость</th></tr></thead><tbody>
          <?php foreach ($items as $it): $m = $it['match'] ?? null; ?><tr><td><b><?= h($it['sku']) ?></b></td><td><a href="/admin/products/<?= (int)$it['id'] ?>"><?= h($it['name']) ?></a></td><td><?= (int)$it['qty'] ?></td><td><?= $m === 'yes' ? '<span class="a-pill a-pill-ok">подходит</span>' : ($m === 'check' ? '<span class="a-pill a-pill-warn">уточнить</span>' : ($m === 'no' ? '<span class="a-pill a-pill-err">не подходит</span>' : '—')) ?></td></tr><?php endforeach; ?>
        </tbody></table><?php endif; ?>
      </section>
      <section class="a-card">
        <h2>История</h2>
        <form class="a-note" method="post"><?= csrf_field() ?><input type="hidden" name="action" value="note"><textarea name="note" rows="2" placeholder="Заметка: о чём договорились, когда перезвонить" aria-label="Заметка"></textarea><button class="a-btn a-btn-soft" type="submit">Добавить</button></form>
        <ul class="a-timeline"><?php foreach ($events as $e): ?><li><div><b><?= nl2br(h($e['text'])) ?></b><small><?= h(ru_date($e['at'])) ?> · <?= h($e['user_name']) ?></small></div></li><?php endforeach; ?></ul>
      </section>
    </div>
    <aside class="a-edit-side">
      <form class="a-card" method="post"><?= csrf_field() ?><input type="hidden" name="action" value="status"><h2>Статус</h2>
        <div class="a-status-pick"><?php foreach ($statuses as $k => $v): ?><label class="a-chip a-chip-st a-st-<?= h($k) ?>"><input type="radio" name="status" value="<?= h($k) ?>"<?= $l['status'] === $k ? ' checked' : '' ?> onchange="this.form.submit()"><span><?= h($v) ?></span></label><?php endforeach; ?></div>
        <noscript><button class="a-btn a-btn-soft" type="submit">Сохранить</button></noscript>
      </form>
      <section class="a-card"><h2>Служебное</h2>
        <dl class="a-dl"><div><dt>Письмо</dt><dd><?= h($l['mail_status'] ?: '—') ?></dd></div><div><dt>Страница</dt><dd><?= $l['page'] ? '<a href="' . h($l['page']) . '" target="_blank">' . h(mb_strimwidth($l['page'], 0, 50, '…')) . '</a>' : '—' ?></dd></div>
          <?php foreach ($utm as $k => $v): ?><div><dt><?= h($k) ?></dt><dd><?= h($v) ?></dd></div><?php endforeach; ?>
          <div><dt>Согласие</dt><dd><?= h(ru_date($l['consent_at'])) ?>, ред. <?= h($l['consent_version']) ?></dd></div></dl>
        <form method="post" class="a-btns"><?= csrf_field() ?><button class="a-btn a-btn-ghost a-btn-sm" type="submit" name="action" value="resend">Отправить письмо ещё раз</button><?php if (is_owner()): ?><button class="a-link-btn a-danger" type="submit" name="action" value="delete" data-confirm="Удалить заявку №<?= $id ?> безвозвратно? Используйте, если человек попросил удалить свои данные.">Удалить</button><?php endif; ?></form>
      </section>
    </aside>
  </div>
  <?php
  a_foot(); return;
}

if ($sub === 'export') {
  header('Content-Type: text/csv; charset=utf-8');
  header('Content-Disposition: attachment; filename="avto-m-zayavki-' . date('Y-m-d') . '.csv"');
  $o = fopen('php://output', 'w'); fwrite($o, "\xEF\xBB\xBF");
  fputcsv($o, ['№', 'Дата', 'Тип', 'Статус', 'Имя', 'Телефон', 'Почта', 'Автомобиль', 'Детали', 'Комментарий'], ';');
  foreach (q_all('SELECT * FROM leads ORDER BY id DESC') as $l) fputcsv($o, [$l['id'], $l['created_at'], $kinds[$l['kind']] ?? $l['kind'], $statuses[$l['status']] ?? $l['status'], $l['name'], $l['phone'], $l['email'], $l['car'], implode(', ', array_map(fn($i) => $i['sku'] . ' × ' . $i['qty'], json_decode($l['items'] ?: '[]', true))), $l['comment']], ';');
  exit;
}

$st = (string)($_GET['status'] ?? '');
$qs = trim((string)($_GET['q'] ?? ''));
$where = []; $p = [];
if (isset($statuses[$st])) { $where[] = 'status = ?'; $p[] = $st; }
if ($qs !== '') { $where[] = '(name LIKE ? OR phone LIKE ? OR items LIKE ? OR car LIKE ?)'; $lk = '%' . $qs . '%'; array_push($p, $lk, '%' . preg_replace('~\D~', '', $qs) . '%', $lk, $lk); }
$rows = q_all('SELECT * FROM leads' . ($where ? ' WHERE ' . implode(' AND ', $where) : '') . ' ORDER BY id DESC LIMIT 300', $p);
$cnt = array_column(q_all('SELECT status, COUNT(*) AS n FROM leads GROUP BY status'), 'n', 'status');
a_head('Заявки', '/leads');
?>
<div class="a-page-head"><div><h1>Заявки</h1><p class="a-muted">Копия каждой заявки приходит на почту: <?= h(site('notify_emails')) ?></p></div><div class="a-head-btns"><a class="a-btn a-btn-ghost" href="/admin/leads/export"><?= a_icon('external') ?><span>Выгрузить в Excel</span></a></div></div>
<div class="a-tabs"><a href="/admin/leads"<?= $st === '' ? ' aria-current="page"' : '' ?>>Все <small><?= array_sum($cnt) ?></small></a><?php foreach ($statuses as $k => $v): ?><a href="/admin/leads?status=<?= $k ?>"<?= $st === $k ? ' aria-current="page"' : '' ?>><?= h($v) ?> <small><?= (int)($cnt[$k] ?? 0) ?></small></a><?php endforeach; ?></div>
<form class="a-filters" method="get"><?php if ($st): ?><input type="hidden" name="status" value="<?= h($st) ?>"><?php endif; ?><label class="a-search"><?= a_icon('search') ?><input type="search" name="q" value="<?= h($qs) ?>" placeholder="Имя, телефон, артикул или машина"></label></form>
<?php if (!$rows): ?><div class="a-card a-empty"><p>Заявок нет.</p></div><?php else: ?>
<div class="a-card a-card-flush"><ul class="a-plist">
  <?php foreach ($rows as $l): $its = json_decode($l['items'] ?: '[]', true); ?>
  <li class="a-prow a-lead-row<?= $l['status'] === 'new' ? ' is-new' : '' ?>"><a class="a-prow-main" href="/admin/leads/<?= (int)$l['id'] ?>"><b>№<?= (int)$l['id'] ?> · <?= h($l['name']) ?> · <?= h($l['phone']) ?></b><small><?= h($kinds[$l['kind']] ?? '') ?><?= $its ? ' · ' . h(implode(', ', array_map(fn($i) => $i['sku'], $its))) : '' ?><?= $l['car'] ? ' · ' . h($l['car']) : '' ?> · <?= h(ru_date($l['created_at'])) ?></small></a><span class="a-pill a-st-<?= h($l['status']) ?>"><?= h($statuses[$l['status']] ?? $l['status']) ?></span></li>
  <?php endforeach; ?>
</ul></div>
<?php endif;
a_foot();
