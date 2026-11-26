# Изображения для сайта AVTO-M: промпты для ChatGPT

Что генерировать, в каком размере, куда загружать. Промпты — на английском: так ChatGPT точнее соблюдает требования. Пояснения — на русском.

## Как работать

1. Откройте ChatGPT (модель с генерацией изображений). **Сначала вставьте блок «Общий стиль»**, затем промпт нужной картинки.
2. Для картинок **на прозрачном фоне** в конце промпта уже есть фраза `transparent background, PNG with alpha channel`. Если фон всё равно получился белым, напишите отдельным сообщением: **«Сделай фон прозрачным и сохрани как PNG»**.
3. Скачайте **PNG** (не JPG — JPG не бывает прозрачным).
4. Проверьте по чек-листу внизу и загрузите в админку. Сайт сам уменьшит и сожмёт файл, прозрачность сохранится.

**Нельзя генерировать фото конкретных товаров** (упор LAP-10262, бачок AV-11001 и т. д.). Покупатель решит, что так выглядит деталь, а нейросеть меняет форму, крепления и длину. Это обман потребителя. Для карточек товаров — только реальные фото (как снимать — раздел 6). Генерируем фоны, иллюстрации категорий и декоративные элементы.

## Общий стиль — вставлять первым сообщением

```
You are creating images for an automotive spare parts website (gas struts, coolant expansion tanks).
Visual style for ALL images in this chat:
- photorealistic, premium automotive advertising look, like a high-end car commercial;
- color palette: deep navy #0B2D72, night blue #050C20, electric blue accent #0A5CFF, cool white;
- lighting: soft key light from upper left, subtle blue rim light, gentle reflections;
- ultra sharp, high detail, clean, no noise, no film grain;
- STRICT: no text, no letters, no numbers, no logos, no brand badges, no emblems, no license plates, no watermarks, no signatures, no UI elements.
Confirm and wait for my first image request.
```

## 1. Главный баннер (как на примере с Ford Focus)

Загружать: **Админка → Страницы → Главная → «Баннер — слайды» → поле «Фон»** нужного слайда. Заголовок, кнопка и плашки — текст сайта, на картинке их быть не должно.

### 1.1 Фон слайда «На твою машину / Автозапчасти» — ПК
Файл: `hero-1-desktop.png`, горизонталь **1536×1024**.
Верхние 40 % кадра — тёмные и пустые (там будет крупный заголовок), машина — в центре снизу.
```
Wide cinematic automotive hero background, 3:2 landscape, 1536x1024.
A modern dark blue compact hatchback car (similar in shape to a 2012 European C-segment hatchback), front three-quarter view, centered in the lower half of the frame, headlights on, parked on wet black asphalt with glossy reflections and small puddles.
Background: dark night pine forest in fog, deep navy blue atmosphere, cold blue backlight glowing behind the car, light rain mist.
The upper 40% of the image must be dark, calm and empty (only fog and dark trees) — space for a large headline.
Left and right thirds of the middle area slightly darker — space for small text badges.
No brand badge on the grille, no emblem, no license plate, no text anywhere.
```

### 1.2 Тот же фон — телефон
Файл: `hero-1-mobile.png`, вертикаль **1024×1536**. Загружать в поле «Фон для телефона» того же слайда.
```
Same scene as the previous image but vertical 2:3 portrait, 1024x1536: the same dark blue hatchback, front three-quarter view, placed in the middle of the frame, wet asphalt with reflections, foggy night forest, blue backlight.
Top 35% dark and empty for a headline, bottom 20% dark asphalt for a button.
No badge, no emblem, no license plate, no text.
```

### 1.3 Фон слайда «Газовые упоры»
Файл: `hero-2.png`, **1536×1024**. Товар (упоры) сайт кладёт поверх сам, поэтому на фоне — только пространство.
```
Dark premium studio background, 3:2, 1536x1024, for placing a product on top.
Glossy black reflective floor in the lower third, deep navy to black gradient walls, two thin soft electric-blue light strips diagonally in the background, a soft blue spotlight pool on the floor in the center.
Center of the frame empty — a product will be placed there later.
No objects, no text, no logos.
```

### 1.4 Фон слайда «Расширительные бачки»
Файл: `hero-3.png`, **1536×1024**.
```
Dark premium studio background, 3:2, 1536x1024. Cool cyan-blue atmosphere, soft volumetric light from above, glossy dark floor with a subtle cyan reflection in the center, faint blurred engine-bay shapes far in the background (out of focus, unrecognizable).
Center empty for a product placed later. No text, no logos.
```

### 1.5 Машина отдельно, на прозрачном фоне (необязательно)
Файл: `car-cutout.png`, **1536×1024**. Если машина вырезана отдельно, её можно положить на любой фон и плавно двигать при прокрутке.
```
Photorealistic dark blue modern compact hatchback (generic European C-segment shape), front three-quarter view, headlights on, isolated on a transparent background, soft contact shadow under the wheels only.
No brand badge, no emblem, no license plate, no text.
Transparent background, PNG with alpha channel.
```

## 2. Плитки «Каталог запчастей» (как на примере: деталь в правом нижнем углу плитки)

Загружать: **Админка → Категории → категория → «Фото категории»**. Размер — **1024×1024**, деталь по диагонали, **прозрачный фон**. Это иллюстрации категорий, а не фото конкретных товаров.

Шаблон для всех плиток (подставьте предмет):
```
Photorealistic studio product render of {ПРЕДМЕТ}, three-quarter view, placed diagonally from lower left to upper right, filling 85% of the frame,
soft key light from upper left, subtle blue rim light, realistic materials, high detail.
Isolated on a transparent background, soft contact shadow only. No text, no logos, no labels, no stickers.
1024x1024, transparent background, PNG with alpha channel.
```

| Категория | Файл | Что подставить вместо {ПРЕДМЕТ} |
|---|---|---|
| Газовые упоры капота | `cat-hood-struts.png` | `a pair of black automotive hood gas struts with chrome piston rods and black plastic ball-socket end fittings, lying parallel` |
| Газовые упоры багажника | `cat-trunk-struts.png` | `a pair of black automotive tailgate gas struts with chrome piston rods and black plastic eyelet end fittings` |
| Пневмоупоры | `cat-gas-springs.png` | `a single long black gas spring with a polished chrome rod and metal eyelet ends` |
| Расширительные бачки | `cat-expansion-tank.png` | `a translucent milky-white plastic car coolant expansion tank with a black screw cap and two small hose outlets` |
| Бачки омывателя | `cat-washer-tank.png` | `a translucent white plastic windshield washer fluid reservoir with a blue cap and a small electric pump` |
| Крепления и кронштейны | `cat-brackets.png` | `a black powder-coated steel L-shaped mounting bracket with two holes, next to a small chrome ball stud` |
| Наконечники | `cat-ends.png` | `two black ball-socket end fittings and one chrome ball stud with thread, neatly arranged` |
| Комплекты фильтров ТО | `cat-filter-kit.png` | `a car maintenance filter kit: oil filter, air filter panel and cabin filter, arranged together` |
| Стойки стабилизатора | `cat-stabilizer-links.png` | `a pair of car stabilizer link rods with ball joints and rubber boots` |
| Тормозные диски и колодки | `cat-brakes.png` | `a ventilated brake disc with a pair of brake pads leaning against it` |

Сейчас на плитках стоят вырезанные из **реальных** фото упоры и бачок. Сгенерированные картинки нужны для новых категорий и если хотите единый «рендерный» стиль.

## 3. Иллюстрации карточек «Подбор по параметрам» и «Оставить заявку»

Загружать: **Админка → Страницы → Главная → «Левая/Правая — иллюстрация»**. **1024×1024, прозрачный фон.** Сейчас на этих местах нарисованные кодом ползунки и облачко — можно оставить их.

### 3.1 Ползунки
Файл: `illu-sliders.png`
```
Soft 3D illustration in a clean modern fintech style: a white rounded rectangular card floating at a slight tilt, with three horizontal slider controls, electric blue #0A5CFF tracks and white round knobs at different positions,
behind it a soft lavender-blue rounded blob shape, gentle soft shadow, glossy subtle highlights, pastel lighting.
Isolated on a transparent background. No text, no numbers, no letters.
1024x1024, transparent background, PNG with alpha channel.
```

### 3.2 Облачко сообщения
Файл: `illu-chat.png`
```
Soft 3D illustration: a white glossy speech bubble with a dark navy #0B2D72 outline and three dark navy dots inside, floating in front of a soft lavender-blue circle, gentle shadow, smooth rounded shapes, pastel studio lighting.
Isolated on a transparent background. No text, no letters.
1024x1024, transparent background, PNG with alpha channel.
```

## 4. Значки преимуществ (необязательно — сейчас линейные значки, как в примере)

Если хотите объёмные: **512×512, прозрачный фон**, по одному на картинку, в одной серии. Сейчас значки выбираются в админке из набора — для объёмных нужна небольшая доработка (поле «картинка» в карточке), скажите — добавлю.
```
Soft 3D icon, glossy, rounded, navy #0B2D72 and electric blue #0A5CFF with white highlights, front view, centered, consistent series style:
{ЗНАЧОК}.
Isolated on a transparent background. No text.
512x512, transparent background, PNG with alpha channel.
```
`{ЗНАЧОК}`: `a shield with a check mark` · `a small delivery truck` · `a closed cardboard parcel box` · `a headset with a microphone`.

## 5. «О компании»

Загружать: **Админка → Страницы → Главная → «Фото склада или офиса»**.
**Лучше реальное фото** склада, полок с товаром или входа (без людей, если нет их согласия). Сгенерированный «наш склад» — это неправда о компании, так делать не стоит. Если реального фото нет, можно нейтральную картинку без претензии на «это мы»:
```
Photorealistic clean modern auto parts warehouse interior, tall metal shelves with neatly stacked plain cardboard boxes, cool daylight, shallow depth of field, navy and blue color grading, wide 3:2 1536x1024.
No text, no labels on boxes, no logos, no people.
```

## 6. Фото товаров — только реальные (как снять на телефон)

- Белый лист ватмана или фотобокс, дневной свет из окна сбоку, без вспышки.
- Телефон на уровне детали, 2-кратный зум — меньше искажений.
- Ракурсы: общий вид по диагонали; оба крепления крупно; маркировка или номер на корпусе крупно; комплект целиком (упоры — пара).
- Вертикальный кадр 3:4, от 2000 точек по длинной стороне.

Очистить фон реального фото в ChatGPT можно, но проверьте, что форма и крепления не изменились:
```
Remove the background from this photo and make it transparent. Do not change, redraw, smooth or "improve" the product in any way — keep its exact shape, proportions, holes, threads and markings. Output PNG with alpha channel.
```

## 7. Логотипы — не генерировать

LIFTAUTOPRO и AUTOPLASTIC — чужие товарные знаки. Нужны файлы от правообладателя (SVG, PDF или PNG с прозрачным фоном). Логотип AUTOPLASTIC уже вырезан из присланных фото.

## Чек-лист перед загрузкой

- [ ] Нет букв, цифр, логотипов, эмблем на машине, номерных знаков (нейросеть часто дорисовывает их мелко — смотрите в 100 % масштабе).
- [ ] Прозрачный фон действительно прозрачный: в просмотрщике видна «шахматка», а не белый цвет.
- [ ] Вокруг вырезанных деталей нет белой каймы.
- [ ] У баннера верх кадра тёмный и пустой, машина не перекрыта заголовком.
- [ ] Детали похожи на настоящие: у упора одна труба и один шток, у бачка нет лишних патрубков.
