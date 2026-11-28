<?php
/**
 * «Измерьте старый упор»: схема упора меняет длину вслед за ползунком или перетаскиванием крепления,
 * ниже — ближайшие упоры из каталога по длине (и по силе, если указана).
 */
$catId = $catId ?? null;
$list = [];
foreach (products_all() as $p) {
  if ($catId && $p['category_id'] != $catId) continue;
  if (!isset($p['attrs']['length']) || !is_numeric($p['attrs']['length'])) continue;
  $ph = product_photo($p);
  $list[] = ['id' => (int)$p['id'], 'n' => $p['name'], 'sku' => $p['sku'], 'u' => product_url($p), 'img' => $ph ? img_url($ph, 400) : '',
    'len' => (float)$p['attrs']['length'], 'f' => isset($p['attrs']['force']) ? (float)$p['attrs']['force'] : null, 'cp' => compat_short($p, 1),
    'wb' => $p['wb_url'] ? '/go/wb/' . $p['id'] : '', 'oz' => $p['ozon_url'] ? '/go/ozon/' . $p['id'] : ''];
}
if (count($list) < 2) return;
$lens = array_column($list, 'len');
$min = (int)(floor((min($lens) - 60) / 10) * 10); $max = (int)(ceil((max($lens) + 60) / 10) * 10);
$start = (int)round(array_sum($lens) / count($lens) / 5) * 5;
?>
<section class="measure panel" data-measure data-min="<?= $min ?>" data-max="<?= $max ?>" aria-labelledby="ms-title" data-reveal>
  <div class="measure-head">
    <div>
      <span class="quiz-badge"><?= icon('ruler') ?>Нет данных о машине?</span>
      <h2 id="ms-title">Подберите упор по старому</h2>
      <p class="muted">Измерьте снятый упор <b>в выдвинутом виде</b> — от центра до центра креплений. Потяните крепление на схеме или введите длину.</p>
    </div>
  </div>
  <div class="ms-viz">
    <svg class="ms-svg" viewBox="0 0 800 150" role="img" aria-label="Схема газового упора, длина меняется вместе с ползунком">
      <defs>
        <linearGradient id="ms-body" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#4a505a"/><stop offset=".28" stop-color="#1a1d22"/><stop offset=".6" stop-color="#0b0d10"/><stop offset="1" stop-color="#2b3038"/></linearGradient>
        <linearGradient id="ms-rod" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#f4f6f9"/><stop offset=".45" stop-color="#a9b1bd"/><stop offset=".55" stop-color="#8a93a0"/><stop offset="1" stop-color="#dfe3e9"/></linearGradient>
        <linearGradient id="ms-hl" x1="0" y1="0" x2="1" y2="0"><stop offset="0" stop-color="#fff" stop-opacity="0"/><stop offset=".5" stop-color="#fff" stop-opacity=".35"/><stop offset="1" stop-color="#fff" stop-opacity="0"/></linearGradient>
      </defs>
      <g class="ms-dim" data-ms-dim>
        <line x1="40" y1="122" x2="40" y2="134"/><line class="ms-dim-r" x1="0" y1="122" x2="0" y2="134" data-ms-dimr/>
        <line class="ms-dim-line" x1="40" y1="128" x2="100" y2="128" data-ms-dimline/>
      </g>
      <g class="ms-strut">
        <g class="ms-eye"><circle cx="40" cy="60" r="17" fill="#1a1d22"/><circle cx="40" cy="60" r="7" fill="#f1f4f9"/></g>
        <rect class="ms-body" x="0" y="42" width="100" height="36" rx="16" fill="url(#ms-body)" data-ms-body/>
        <rect class="ms-body-hl" x="0" y="47" width="100" height="5" rx="2.5" fill="url(#ms-hl)" data-ms-bodyhl/>
        <rect class="ms-rod" x="0" y="54" width="100" height="12" rx="6" fill="url(#ms-rod)" data-ms-rod/>
        <g data-ms-seal><rect x="-6" y="46" width="12" height="28" rx="5" fill="#2a2e34"/></g>
        <g class="ms-eye ms-handle" data-ms-handle tabindex="0" role="slider" aria-label="Длина упора, перетащите" aria-valuemin="<?= $min ?>" aria-valuemax="<?= $max ?>">
          <circle cx="0" cy="60" r="26" fill="#0a5cff" fill-opacity=".12" class="ms-halo"/>
          <circle cx="0" cy="60" r="17" fill="#1a1d22"/><circle cx="0" cy="60" r="7" fill="#f1f4f9"/>
        </g>
      </g>
    </svg>
    <output class="ms-value" data-ms-out><b>—</b> мм</output>
  </div>
  <div class="ms-controls">
    <label class="ms-range"><span class="sr">Длина упора, мм</span><input type="range" min="<?= $min ?>" max="<?= $max ?>" step="1" value="<?= $start ?>" data-ms-range></label>
    <div class="ms-inputs">
      <label class="field"><span class="lbl">Длина, мм</span><input type="number" inputmode="numeric" min="<?= $min ?>" max="<?= $max ?>" value="<?= $start ?>" data-ms-len></label>
      <label class="field"><span class="lbl">Сила, Н</span><input type="number" inputmode="numeric" placeholder="если указана" data-ms-force></label>
    </div>
  </div>
  <div class="ms-results" data-ms-results aria-live="polite"></div>
  <p class="hint"><?= icon('info') ?>Разница больше 10 мм — уточните у менеджера, упор может не подойти.</p>
  <script type="application/json" data-ms-data><?= json_encode($list, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) ?></script>
</section>
