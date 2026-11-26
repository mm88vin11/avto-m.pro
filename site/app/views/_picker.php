<?php
/**
 * Подбор по автомобилю: марка → модель → год → кузов → двигатель. Значения зависят от предыдущего выбора (JS),
 * без JS работает как обычная форма. $action — куда отправлять, $car — выбранный автомобиль, $compact — в одну строку.
 */
$car = $car ?? null;
$action = $action ?? '/catalog';
$id = $id ?? 'pk';
$tree = car_tree();
$curMake = $car['make'] ?? '';
$models = $curMake ? (car_tree_find($curMake)['models'] ?? []) : [];
$curModel = null;
foreach ($models as $m) if ($m['slug'] === ($car['model'] ?? '')) $curModel = $m;
$hidden = $hidden ?? [];
?>
<?php if (empty($embedded)): ?><form class="picker <?= !empty($compact) ? 'picker-row' : '' ?>" action="<?= h($action) ?>" method="get" data-picker><?php else: ?><div class="picker picker-col" data-picker><?php endif; ?>
  <?php foreach ($hidden as $k => $v): ?><input type="hidden" name="<?= h($k) ?>" value="<?= h($v) ?>"><?php endforeach; ?>
  <div class="field sel">
    <label for="<?= $id ?>-make">Марка автомобиля</label>
    <select id="<?= $id ?>-make" name="make" data-pk="make">
      <option value="">Выберите марку</option>
      <?php foreach ($tree as $mk): ?><option value="<?= h($mk['slug']) ?>"<?= $curMake === $mk['slug'] ? ' selected' : '' ?>><?= h($mk['name']) ?></option><?php endforeach; ?>
    </select>
  </div>
  <div class="field sel">
    <label for="<?= $id ?>-model">Модель</label>
    <select id="<?= $id ?>-model" name="model" data-pk="model"<?= $models ? '' : ' disabled' ?>>
      <option value=""><?= $models ? 'Любая модель' : 'Сначала марка' ?></option>
      <?php foreach ($models as $m): ?><option value="<?= h($m['slug']) ?>"<?= ($car['model'] ?? '') === $m['slug'] ? ' selected' : '' ?>><?= h($m['name']) ?></option><?php endforeach; ?>
    </select>
  </div>
  <div class="field sel">
    <label for="<?= $id ?>-year">Год выпуска</label>
    <select id="<?= $id ?>-year" name="year" data-pk="year"<?= $curModel ? '' : ' disabled' ?>>
      <option value="">Любой год</option>
      <?php if ($curModel && $curModel['yf']): for ($y = (int)($curModel['yt'] ?: date('Y')); $y >= (int)$curModel['yf']; $y--): ?><option<?= ($car['year'] ?? null) == $y ? ' selected' : '' ?>><?= $y ?></option><?php endfor; endif; ?>
    </select>
  </div>
  <div class="field sel">
    <label for="<?= $id ?>-body">Кузов</label>
    <select id="<?= $id ?>-body" name="body" data-pk="body"<?= $curModel && $curModel['bodies'] ? '' : ' disabled' ?>>
      <option value="">Любой кузов</option>
      <?php if ($curModel): foreach ($curModel['bodies'] as $bd): ?><option<?= ($car['body'] ?? '') === $bd ? ' selected' : '' ?>><?= h($bd) ?></option><?php endforeach; endif; ?>
    </select>
  </div>
  <div class="field sel">
    <label for="<?= $id ?>-engine">Модификация</label>
    <select id="<?= $id ?>-engine" name="engine" data-pk="engine"<?= $curModel && $curModel['engines'] ? '' : ' disabled' ?>>
      <option value="">Любой</option>
      <?php if ($curModel): foreach ($curModel['engines'] as $e): ?><option value="<?= h($e) ?>"<?= ($car['engine'] ?? '') === $e ? ' selected' : '' ?>><?= h($e) ?> л</option><?php endforeach; endif; ?>
    </select>
  </div>
<?php if (empty($embedded)): ?>
  <button class="btn btn-primary btn-lg picker-go" type="submit" data-goal="pick_used"><?= icon('search') ?><span>Показать детали</span></button>
</form>
<?php else: ?></div><?php endif; ?>
