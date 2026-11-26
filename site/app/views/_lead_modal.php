<dialog class="modal" id="lead-modal" aria-labelledby="lead-modal-title">
  <div class="modal-box">
    <button class="icon-btn modal-close" type="button" data-modal-close aria-label="Закрыть"><?= icon('close') ?></button>
    <div class="modal-body" data-modal-body>
      <h2 id="lead-modal-title" data-modal-title>Уточнить наличие</h2>
      <p class="modal-lead" data-modal-lead>Оставьте телефон — менеджер проверит наличие и совместимость и перезвонит.</p>
      <?php partial('lead_form', ['kind' => 'availability', 'id' => 'mf', 'withCar' => true, 'btn' => 'Отправить']); ?>
    </div>
    <div class="modal-done" data-modal-done hidden>
      <div class="done-mark" aria-hidden="true"><svg viewBox="0 0 52 52"><circle cx="26" cy="26" r="24"/><path d="m15 27 7 7 15-16"/></svg></div>
      <h2>Заявка отправлена</h2>
      <p data-done-text>Менеджер перезвонит в рабочее время. Номер заявки: <b data-done-id></b>.</p>
      <button class="btn btn-soft" type="button" data-modal-close>Хорошо</button>
    </div>
  </div>
</dialog>
