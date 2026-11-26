<?php
/**
 * Форма заявки. Переменные: $kind, $btn, $withCar (bool), $withCompany (bool), $commentLabel, $id (префикс полей), $withItems (bool)
 * Отправка — /api/lead (JS), без JS — обычный POST с возвратом на страницу.
 */
$id = $id ?? 'lf';
$btn = $btn ?? 'Отправить заявку';
$commentLabel = $commentLabel ?? 'Комментарий';
?>
<form class="lead-form" action="/api/lead" method="post" novalidate data-lead-form>
  <input type="hidden" name="kind" value="<?= h($kind) ?>">
  <input type="hidden" name="items" value="">
  <input type="hidden" name="page" value="">
  <div class="hp" aria-hidden="true"><label>Сайт <input type="text" name="website" tabindex="-1" autocomplete="off"></label></div>
  <div class="form-error" data-form-error role="alert" hidden></div>
  <div class="lead-items" data-items hidden></div>
  <div class="field-row">
    <div class="field">
      <label for="<?= $id ?>-name">Имя</label>
      <input id="<?= $id ?>-name" name="name" type="text" autocomplete="given-name" required maxlength="80" placeholder="Как к вам обращаться">
      <p class="field-err" hidden></p>
    </div>
    <div class="field">
      <label for="<?= $id ?>-phone">Телефон</label>
      <input id="<?= $id ?>-phone" name="phone" type="tel" inputmode="tel" autocomplete="tel" required placeholder="+7 (___) ___-__-__" data-phone>
      <p class="field-err" hidden></p>
    </div>
  </div>
  <?php if (!empty($withCompany)): ?>
  <div class="field">
    <label for="<?= $id ?>-company">Компания или автосервис</label>
    <input id="<?= $id ?>-company" name="company" type="text" autocomplete="organization" maxlength="120" required placeholder="Название">
    <p class="field-err" hidden></p>
  </div>
  <?php endif; ?>
  <?php if (!empty($withCar)): ?>
  <div class="field">
    <label for="<?= $id ?>-car">Автомобиль <span class="opt">марка, модель, год или VIN-номер</span></label>
    <input id="<?= $id ?>-car" name="car" type="text" maxlength="200" placeholder="Например, Ford Focus II, 2008, седан" data-car-text>
    <p class="field-err" hidden></p>
  </div>
  <?php endif; ?>
  <div class="field">
    <label for="<?= $id ?>-comment"><?= h($commentLabel) ?> <span class="opt">необязательно</span></label>
    <textarea id="<?= $id ?>-comment" name="comment" rows="3" maxlength="2000" placeholder="Что важно знать менеджеру"></textarea>
  </div>
  <div class="field">
    <label for="<?= $id ?>-email">Почта <span class="opt">необязательно</span></label>
    <input id="<?= $id ?>-email" name="email" type="email" autocomplete="email" maxlength="120" placeholder="name@example.ru">
    <p class="field-err" hidden></p>
  </div>
  <div class="field field-check">
    <label class="check"><input type="checkbox" name="consent" value="1" required><span class="check-box" aria-hidden="true"><?= icon('check') ?></span>
      <span>Даю <a href="/legal/consent" target="_blank">согласие на обработку персональных данных</a> и принимаю <a href="/legal/privacy" target="_blank">политику</a></span></label>
    <p class="field-err" hidden></p>
  </div>
  <button class="btn btn-primary btn-lg btn-block" type="submit"><span><?= h($btn) ?></span></button>
  <p class="form-note">Перезвоним в рабочее время: <?= h(mb_strtolower(site('hours'))) ?>.</p>
</form>
