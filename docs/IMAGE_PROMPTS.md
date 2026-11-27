# Изображения для сайта AVTO-M в стиле примеров IMG_1667 и IMG_1668

Стиль сайта — светлый каталог автозапчастей: белые карточки, голубой баннер, синие кнопки, реалистичные фото деталей. Ниже — каждая нужная картинка: зачем она, размер, имя файла, куда загрузить в админке и подробный промпт для ChatGPT.

Сайт **работает и без этих картинок**: сейчас стоят детали, вырезанные из реальных фото заказчика. Картинки делают сайт ближе к примерам — с фото в карточках категорий, как на IMG_1667.

---

## Как генерировать, чтобы получилось ровно и в одном стиле

1. **Всё делайте в одном чате ChatGPT.** Так свет, цвет и ракурсы получатся одинаковыми. Первым сообщением вставьте «Общий стиль» (ниже) и дождитесь ответа.
2. **Картинки на прозрачном фоне.** В промпте есть строка `Transparent background, PNG with alpha channel`. Если фон всё равно белый или в «шахматку» нарисованную — напишите: *«Фон должен быть по-настоящему прозрачным, без нарисованной шахматки. Пересохрани как PNG с альфа-каналом»*.
3. **Скачивайте PNG**, не JPG и не WEBP.
4. **Проверьте по чек-листу** в конце, затем загрузите в админку. Сайт сам уменьшит и сожмёт файл, прозрачность сохранится.
5. Если результат не нравится — не начинайте заново, а уточняйте в том же чате: *«Сделай деталь крупнее, на 85 % кадра»*, *«Убери надпись на корпусе»*, *«Свет мягче, без бликов на штоке»*.

**Чего нельзя генерировать:**
- **Фото конкретных товаров** (LAP-10262, AV-11001 и т. д.) для их карточек. Нейросеть меняет длину, крепления и патрубки — покупатель получит не то, что видел. Это нарушение прав потребителя. Для карточек товаров — только реальные фото (раздел 7).
- **Логотипы** AVTO-M, LIFTAUTOPRO, AUTOPLASTIC, Wildberries, Ozon и надписи на деталях. На примере IMG_1667 на упоре написано «AUTO-M GAS SPRING» — это нужно делать на реальном товаре, а не рисовать.
- **Фото «нашего офиса» или «нашего склада»**, которых не существует.

---

## Общий стиль — вставить первым сообщением

```
I will ask you to create a series of images for an online auto parts catalog (gas struts for car hoods and tailgates, coolant expansion tanks, brackets and ball studs).
Keep ONE consistent style across the whole series:

STYLE
- Photorealistic, like professional e-commerce product photography for a premium auto parts store.
- Bright, clean and fresh look. Soft, even studio daylight from the upper left, gentle fill light from the right, soft natural shadows, no harsh glare.
- Color palette of the website: white, very light blue (#EEF3FB, #E3EDFF), sky blue (#1D6FE8), deep navy (#0C2F78). Car paint and backgrounds should stay within cool white, silver, light grey and blue tones.
- Materials must look real: black powder-coated steel tube, polished chrome piston rod with subtle reflections, black plastic end fittings, translucent milky-white polypropylene for tanks.
- Ultra sharp focus on the part, high detail, no noise, no film grain, no artistic filters, no fantasy elements.

STRICT RULES FOR EVERY IMAGE
- No text, letters, numbers, labels, stickers, barcodes, logos, brand badges, emblems, watermarks or signatures anywhere — not on the parts, not on the car, not in the background.
- No license plates (or a completely blank plate area).
- Parts must look like real, physically correct automotive parts: a gas strut has exactly one cylinder, one rod and two end fittings; no extra hoses, no melted or bent shapes.
- Do not add people or hands unless I ask.

Reply "OK" and wait for my first request.
```

---

## 1. Баннер на главной: деталь справа на прозрачном фоне

На примере IMG_1667 справа в баннере — крупный газовый упор по диагонали. На сайте баннер светло-голубой, деталь кладётся поверх.

Загружать: **Админка → Страницы → Главная → «Баннер — слайды» → поле «Товар на прозрачном фоне»** нужного слайда.
Размер: **1536×1024** (горизонталь), прозрачный фон.

### 1.1 Один крупный газовый упор (слайд «Газовые упоры»)
Файл: `banner-strut-single.png`
```
Create a hero product image: ONE automotive gas strut (gas spring) for a car tailgate, shown large and diagonal, going from the lower left to the upper right at about 20 degrees, filling about 90% of the image width.
Details: a glossy black powder-coated steel cylinder (about 60% of the length) with a thin black rubber seal ring at the end, then a polished chrome piston rod with crisp reflections, black plastic eyelet end fittings on both ends with round metal bushings.
Camera: three-quarter view, slightly from above, product photography lens, everything in sharp focus.
Lighting: bright soft studio light from the upper left, a soft cool-blue rim light along the top edge of the cylinder, a gentle realistic shadow falling down-right.
No text or markings on the cylinder.
Isolated object. Transparent background, PNG with alpha channel. 1536x1024.
```

### 1.2 Пара упоров (слайд «Газовые упоры», вариант)
Файл: `banner-strut-pair.png`
```
Same style and lighting as the previous image: a PAIR of identical automotive tailgate gas struts lying parallel and diagonal (lower left to upper right), slightly overlapping in depth, the front one fully visible, the back one about 15% smaller and slightly higher.
Black cylinders, polished chrome rods, black plastic eyelet end fittings, no markings.
Soft realistic shadow under each strut.
Transparent background, PNG with alpha channel. 1536x1024.
```

### 1.3 Расширительный бачок (слайд «Расширительные бачки»)
Файл: `banner-tank.png`
```
Same series style: a car coolant expansion tank, translucent milky-white polypropylene with visible wall thickness, a threaded neck with a black screw cap, two small hose outlets on the side, a mounting bracket molded into the body.
Three-quarter view from slightly above, rotated about 15 degrees, filling about 75% of the image.
Bright soft studio light, a subtle cool-blue rim light, the plastic should look slightly translucent with soft internal light scattering, a gentle shadow under it.
No text, no embossed letters or numbers, no "MAX/MIN" marks.
Transparent background, PNG with alpha channel. 1536x1024.
```

### 1.4 Композиция «упор + бачок» (слайд «Подбор по автомобилю»)
Файл: `banner-combo.png`
```
Same series style: a composition of one black gas strut with a chrome rod lying diagonally in the back, and a translucent milky-white coolant expansion tank with a black cap standing in front of it on the right.
Both parts in sharp focus, realistic scale relative to each other, soft studio light from the upper left, soft shadows, gentle blue rim light.
No text, no markings.
Transparent background, PNG with alpha channel. 1536x1024.
```

---

## 2. Баннер: светлый фон (необязательно)

Сейчас фон баннера нарисован кодом: голубой градиент с диагональными бликами, как на IMG_1667. Можно заменить фотографическим.
Загружать: поле **«Фон для компьютера»** слайда (и «Фон для телефона» — второй вариант).
Текст сайта стоит слева, деталь — справа, поэтому **левая половина фона — спокойная и светлая**.

### 2.1 Фон для компьютера
Файл: `banner-bg-desktop.png`, **1536×1024** (сайт покажет центральную горизонтальную полосу — главное держите посередине по высоте).
```
Abstract light background for a website banner of an auto parts store, horizontal 1536x1024.
Very light blue to white gradient (#F7FAFF on the left to #CDDCFB on the right), with two or three soft diagonal translucent white light streaks and a smooth flowing light-blue wave shape across the lower right, like clean glossy glass or brushed light.
Left half: calm, almost plain, very light — text will be placed there.
Right half: slightly richer light blue with soft glow — a product will be placed there.
No objects, no cars, no parts, no text, no logos. Smooth, premium, minimal.
```

### 2.2 Фон для телефона
Файл: `banner-bg-mobile.png`, **1024×1536** (вертикаль).
```
Same abstract light blue background as the previous image, but vertical 1024x1536: top 45% calm and very light (for text), bottom part with soft diagonal light streaks and a gentle light-blue glow (a product will be placed there). No objects, no text.
```

---

## 3. Карточки «Каталог продукции» — фото как на IMG_1667

На примере в карточках категорий реальные сцены: открытый капот с упором, открытый багажник и т. д. Это иллюстрации **категорий**, а не фото конкретных товаров, поэтому их можно генерировать.

Загружать: **Админка → Категории → категория → «Фото категории»**.
Размер: **1536×1024** (сайт обрежет до 4:3 по центру — главное держите в центре).
**Обычный фон**, не прозрачный — это фото.

### 3.1 Газовые упоры капота
Файл: `cat-hood.png`
```
Photorealistic close-up photo of an open car hood of a modern silver hatchback, shot from the front-left side at the height of the engine bay.
The hood is raised and held by a black gas strut with a chrome rod clearly visible in the foreground (sharp focus), the engine bay is clean and modern but softly out of focus.
Bright overcast daylight outdoors, soft shadows, cool neutral colors, a light blurred background with sky and greenery.
No brand emblems on the car, no text, no logos, no license plate.
4:3 composition with the gas strut in the center, 1536x1024.
```

### 3.2 Газовые упоры багажника
Файл: `cat-tailgate.png`
```
Photorealistic photo from the rear-left side of a modern silver hatchback with its tailgate fully open.
The left tailgate gas strut (black cylinder, chrome rod) is in sharp focus in the foreground, the empty clean trunk and the raised tailgate are visible, the rest softly out of focus.
Bright soft daylight, cool neutral tones, light blurred background.
No emblems, no badges, no text, no license plate (rear bumper area plain).
4:3 composition, strut in the center, 1536x1024.
```

### 3.3 Пневмоупоры для универсала
Файл: `cat-wagon.png`
```
Photorealistic photo of a modern white station wagon with the rear tailgate open, seen from the rear three-quarter angle.
Both tailgate gas struts are visible, the nearest one in sharp focus. Clean empty cargo area.
Bright soft daylight, light neutral background (blurred parking area with greenery).
No emblems, no badges, no text, no license plate.
4:3 composition, 1536x1024.
```

### 3.4 Расширительные бачки
Файл: `cat-tank-engine.png`
```
Photorealistic close-up of a clean modern car engine bay: a translucent milky-white coolant expansion tank with a black screw cap is in sharp focus in the center, pink coolant visible through the plastic at a normal level, hoses attached.
The rest of the engine bay is softly out of focus, clean, dark grey and black plastics.
Bright soft daylight from above, cool neutral tones.
No text, no labels, no MAX/MIN marks, no brand logos on engine covers.
4:3 composition, 1536x1024.
```

### 3.5 Крепления и наконечники
Файл: `cat-brackets.png`
```
Photorealistic e-commerce product photo on a very light grey-blue seamless studio background (#EEF3FB):
a black powder-coated steel L-shaped mounting bracket with two round holes, next to it two chrome ball studs with threaded ends, a small black plastic ball-socket end fitting and a metal washer, neatly arranged on a diagonal.
Soft studio light from the upper left, soft realistic shadows, sharp focus.
No text, no labels.
4:3 composition, 1536x1024.
```

### 3.6 Подбор по параметрам
Файл: `cat-params.png`
На сайте поверх этой картинки уже стоит значок фильтра в белом квадрате (как на IMG_1667), поэтому **правый нижний угол оставьте пустым**.
```
Photorealistic e-commerce photo on a very light blue seamless background (#E3EDFF):
one black gas strut with a chrome rod lying diagonally from the lower left to the upper right, next to a steel tape measure partly extended along it (the tape has NO numbers or text, plain yellow tape with simple tick marks only).
Soft studio light, soft shadows, sharp focus.
Keep the lower right corner empty and calm.
No text, no numbers, no logos.
4:3 composition, 1536x1024.
```

### 3.7 Для будущих категорий (по презентации заказчика)
Тот же шаблон, что в 3.5, — меняется только предмет:
```
Photorealistic e-commerce product photo on a very light grey-blue seamless studio background (#EEF3FB): {ПРЕДМЕТ}, neatly arranged on a diagonal, soft studio light from the upper left, soft realistic shadows, sharp focus. No text, no labels, no logos. 4:3 composition, 1536x1024.
```

| Категория | Файл | {ПРЕДМЕТ} |
|---|---|---|
| Бачки омывателя | `cat-washer.png` | `a translucent white windshield washer fluid reservoir with a blue cap and a small electric pump attached` |
| Комплекты фильтров для ТО | `cat-filters.png` | `a car service filter kit: a dark blue oil filter, a white pleated air filter panel and a cabin filter, arranged together` |
| Стойки стабилизатора | `cat-links.png` | `a pair of car stabilizer link rods with ball joints, black rubber boots and nuts` |
| Тормозные диски и колодки | `cat-brakes.png` | `a ventilated steel brake disc with a pair of brake pads leaning against it` |

---

## 4. Фото в боковой колонке главной (как на IMG_1667 слева)

На примере под меню «Каталог продукции» — вертикальное фото упора под капотом.
Загружать: **Админка → Страницы → Главная → «Фото в боковой колонке»**.
Размер: **1024×1536** (вертикаль), обычный фон.
Файл: `side-hood.png`
```
Vertical photorealistic close-up: a raised car hood held by a black gas strut with a chrome rod, the strut runs diagonally through the frame and is in sharp focus.
Below it the clean modern engine bay softly out of focus, above it a soft blurred sky. Bright soft daylight, cool neutral and light blue tones.
No emblems, no text, no logos.
Vertical 2:3, 1024x1536.
```

---

## 5. «О компании» на главной

На примере — фасад здания с вывеской. Нарисованный «наш офис» — неправда о компании, так делать нельзя. Варианты:
- **Лучше всего — реальное фото** склада, стеллажей с товаром или входа на Граничной, 36. Без людей (или с их согласия).
- Если реального фото пока нет, можно нейтральную картинку, которая не выдаётся за «наш склад»:

Загружать: **Админка → Страницы → Главная → «Фото склада или офиса»**. Размер **1536×1024**.
```
Photorealistic interior of a clean, bright auto parts storage room: tall light-grey metal shelves with neatly stacked plain white and kraft cardboard boxes, a few black gas struts in an open box in the foreground (soft focus), cool daylight, light blue and white color grading, shallow depth of field.
No text, no labels on boxes, no logos, no people.
3:2, 1536x1024.
```

---

## 6. Значки и иллюстрации

Значки (преимущества, контакты, меню) на сайте — линейные, как на IMG_1667, и рисуются кодом. **Картинки для них не нужны.**

---

## 7. Фото товаров — только реальные

### Как снять на телефон
- Белый лист ватмана (или фотобокс), дневной свет из окна сбоку, без вспышки.
- Телефон на уровне детали, 2-кратный зум (меньше искажений).
- Обязательные ракурсы: деталь целиком по диагонали; оба крепления крупно; маркировка или номер на корпусе крупно; комплект целиком (упоры — пара).
- Вертикальный кадр 3:4, от 2000 точек по длинной стороне.

### Убрать фон с реального фото в ChatGPT
Загрузите фото и напишите:
```
Remove the background from this photo and make it truly transparent.
Do NOT change, redraw, smooth, straighten or "improve" the product in any way — keep its exact shape, length, proportions, holes, threads, markings and colors.
Keep a soft natural contact shadow only if it looks realistic, otherwise no shadow.
Output: PNG with alpha channel, same resolution.
```
Обязательно сравните результат с оригиналом: если что-то изменилось (длина, форма креплений, надписи), берите оригинал.

---

## 8. Логотипы — не генерировать

LIFTAUTOPRO и AUTOPLASTIC — товарные знаки правообладателей. Нужны файлы от владельцев: SVG, PDF или PNG с прозрачным фоном. Логотип AUTOPLASTIC уже вырезан из присланных фото. Логотип AVTO-M лучше получить в векторе (SVG) — сейчас используется PNG.

---

## Чек-лист перед загрузкой

- [ ] Смотрите в 100 % масштабе: **нет букв, цифр, логотипов**, эмблем на машине, номеров (нейросеть часто дорисовывает их мелко).
- [ ] Прозрачный фон — **настоящий**: в просмотрщике видна «шахматка» программы, а не нарисованная.
- [ ] Вокруг вырезанных деталей **нет белой каймы**.
- [ ] Детали **физически правильные**: у упора один цилиндр, один шток, два крепления; у бачка нет лишних патрубков.
- [ ] Все картинки серии — **с одинаковым светом и цветом** (сделаны в одном чате).
- [ ] Для баннера: деталь крупная и по диагонали, без обрезанных краёв.

## Сводная таблица

| № | Что | Файл | Размер | Фон | Куда в админке |
|---|---|---|---|---|---|
| 1.1 | Упор крупно | `banner-strut-single.png` | 1536×1024 | прозрачный | Страницы → Главная → слайд «Газовые упоры» → Товар |
| 1.2 | Пара упоров | `banner-strut-pair.png` | 1536×1024 | прозрачный | то же (вариант) |
| 1.3 | Бачок | `banner-tank.png` | 1536×1024 | прозрачный | слайд «Расширительные бачки» → Товар |
| 1.4 | Упор + бачок | `banner-combo.png` | 1536×1024 | прозрачный | слайд «Подбор» → Товар |
| 2.1 | Фон баннера ПК | `banner-bg-desktop.png` | 1536×1024 | светлый | каждый слайд → Фон для компьютера |
| 2.2 | Фон баннера телефон | `banner-bg-mobile.png` | 1024×1536 | светлый | каждый слайд → Фон для телефона |
| 3.1–3.6 | Фото категорий | `cat-*.png` | 1536×1024 | фото | Категории → Фото категории |
| 4 | Боковое фото | `side-hood.png` | 1024×1536 | фото | Страницы → Главная → Фото в боковой колонке |
| 5 | О компании | реальное фото | 1536×1024 | фото | Страницы → Главная → Фото склада или офиса |
| 7 | Товары | реальные фото | 3:4, от 2000 | белый | Товары → товар → Фото |
