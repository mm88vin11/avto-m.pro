<?php
$c = content('contacts');
$s = site();
meta('title', $c['seo_title']); meta('desc', $c['seo_desc']); meta('canonical', '/contacts');
$mapQ = rawurlencode($s['map_query']);
$routeUrl = 'https://yandex.ru/maps/?mode=routes&rtext=~' . $mapQ;
$mapUrl = 'https://yandex.ru/maps/?text=' . $mapQ;
?>
<div class="wrap">
  <?= breadcrumbs([['Контакты', null]]) ?>
  <div class="page-head"><h1><?= h($c['title']) ?></h1><p class="lead"><?= h($c['lead']) ?></p></div>
  <div class="contacts">
    <div class="contact-cards">
      <a class="ccard panel" href="<?= h(phone_href($s['phone'])) ?>" data-goal="phone_click" data-reveal><span class="ccard-ic"><?= icon('phone') ?></span><small>Телефон</small><b><?= h($s['phone']) ?></b><span class="ccard-go">Позвонить<?= icon('arrow') ?></span></a>
      <a class="ccard panel" href="mailto:<?= h($s['email']) ?>" data-reveal><span class="ccard-ic"><?= icon('mail') ?></span><small>Почта</small><b><?= h($s['email']) ?></b><span class="ccard-go">Написать<?= icon('arrow') ?></span></a>
      <a class="ccard panel" href="<?= h($routeUrl) ?>" target="_blank" rel="noopener" data-goal="route_click" data-reveal><span class="ccard-ic"><?= icon('pin') ?></span><small>Адрес</small><b><?= h($s['address']) ?></b><span class="ccard-go">Маршрут в Яндекс Картах<?= icon('external') ?></span></a>
      <div class="ccard panel" data-reveal><span class="ccard-ic"><?= icon('clock') ?></span><small>Часы работы</small><b><?= h($s['hours']) ?></b><span class="ccard-note"><?= h($c['note']) ?></span></div>
    </div>
    <div class="map panel" data-map="https://yandex.ru/map-widget/v1/?text=<?= h($mapQ) ?>&amp;z=16">
      <div class="map-ph">
        <span class="map-pin" aria-hidden="true"><?= icon('pin') ?></span>
        <p><b><?= h($s['address_short']) ?></b><br>Карта загрузится с сервиса «Яндекс Карты».</p>
        <div class="map-btns">
          <button class="btn btn-primary" type="button" data-map-load><?= icon('route') ?>Показать карту</button>
          <a class="btn btn-soft" href="<?= h($mapUrl) ?>" target="_blank" rel="noopener">Открыть в Яндекс Картах<?= icon('external') ?></a>
        </div>
      </div>
    </div>
    <section class="panel contact-form" aria-labelledby="cf-title">
      <h2 id="cf-title">Напишите нам</h2>
      <p class="muted">Спросите о наличии, совместимости или условиях для сервисов — ответим в рабочее время.</p>
      <?php partial('lead_form', ['kind' => 'question', 'id' => 'ctf', 'withCar' => true, 'btn' => 'Отправить', 'commentLabel' => 'Вопрос']); ?>
    </section>
    <section class="panel requisites" aria-labelledby="rq-title">
      <h2 id="rq-title">Реквизиты</h2>
      <dl class="specs">
        <div><dt>Продавец</dt><dd><?= h($s['org_name']) ?></dd></div>
        <div><dt>ИНН</dt><dd><?= h($s['inn']) ?></dd></div>
        <div><dt>ОГРНИП</dt><dd><?= h($s['ogrnip']) ?></dd></div>
      </dl>
    </section>
  </div>
</div>
