<?php
/** Вопросы и ответы. $items — [['q' => …, 'a' => …], …]. Открыт один ответ за раз; на компьютере — две колонки. */
$half = (int)ceil(count($items) / 2);
?>
<div class="faq" data-faq>
  <?php foreach ([array_slice($items, 0, $half), array_slice($items, $half)] as $col): if (!$col) continue; ?>
  <div class="faq-col">
    <?php foreach ($col as $x): ?>
    <details class="faq-item"><summary><span><?= h($x['q']) ?></span><i aria-hidden="true"></i></summary><div class="faq-a"><p><?= h($x['a']) ?></p></div></details>
    <?php endforeach; ?>
  </div>
  <?php endforeach; ?>
</div>
