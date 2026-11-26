<?php meta('robots', 'noindex'); meta('title', ($r['ok'] ? 'Заявка отправлена' : 'Проверьте форму') . ' | AVTO-M'); ?>
<div class="wrap">
  <div class="nf panel">
    <?php if ($r['ok']): ?>
      <h1>Заявка отправлена</h1>
      <p>Менеджер перезвонит в рабочее время: <?= h(mb_strtolower(site('hours'))) ?>. Номер заявки: <b><?= (int)$r['id'] ?></b>.</p>
    <?php else: ?>
      <h1>Не получилось отправить</h1>
      <ul class="err-list"><?php foreach ($r['errors'] as $e): ?><li><?= h($e) ?></li><?php endforeach; ?></ul>
    <?php endif; ?>
    <div class="empty-btns"><a class="btn btn-primary" href="<?= h($back) ?>">Вернуться</a></div>
  </div>
</div>
