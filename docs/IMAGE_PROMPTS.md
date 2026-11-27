# Пакет графики AVTO-M — одним заходом

Это единое задание на всю графику сайта: один стиль, один чат, один стартовый промпт. Вы вставляете **один** большой промпт в ChatGPT. Дальше пишете только «Дальше», и он по очереди делает все картинки серии в одном свете и цвете.

Что уже есть и стоит на сайте (из вашего архива, 10 штук): фон баннера для компьютера и для телефона, упор, пара упоров, бачок, упор с бачком, плитки «капот», «багажник» и «крепления», фото «упор под капотом». Их не переделываем: новая серия должна выглядеть как их продолжение.

---

## 1. Что получится в итоге

| № | Файл | Формат ChatGPT | Где на сайте | Куда загрузить |
|---|------|----------------|--------------|----------------|
| 1 | `banner-car.png` | 1536×1024, прозрачный фон | 3-й слайд баннера «Подбор по вашему автомобилю» | Страницы → Главная → Слайды → 3-й → «Товар на прозрачном фоне» |
| 2 | `cover-tanks.png` | 1536×1024, фон есть | Обложка раздела «Расширительные бачки» | Категории → Расширительные бачки → «Обложка страницы» |
| 3 | `cover-hood.png` | 1536×1024, фон есть | Обложка раздела «Упоры капота» (когда появятся товары) | Категории → Упоры капота → «Обложка страницы» |
| 4 | `card-params.png` | 1024×1024, прозрачный | Карточка «Подбор по параметрам» на главной | Страницы → Главная → «Подбор — картинка» |
| 5 | `card-lead.png` | 1024×1024, прозрачный | Карточка «Оставить заявку» на главной | Страницы → Главная → «Заявка — картинка» |
| 6 | `empty-cart.png` | 1024×1024, прозрачный | Пустая корзина | Файл → папка `assets/img/empty/` на хостинге, имя `cart.png` |
| 7 | `empty-search.png` | 1024×1024, прозрачный | «Ничего не нашли» в поиске | `assets/img/empty/search.png` |
| 8 | `empty-compare.png` | 1024×1024, прозрачный | Пустое сравнение | `assets/img/empty/compare.png` |
| 9 | `empty-404.png` | 1024×1024, прозрачный | Страница 404 | `assets/img/empty/404.png` |
| 10 | `tile-template.png` | 1536×1024, прозрачный | Плитка любой новой категории | Категории → нужная → «Фото категории» |

Сайт работает и без этих картинок: где их нет, стоят нарисованные кодом ползунки, облачко, значки и обычный заголовок. Картинки 4–9 — «вишенка», а 1–3 заметно усиливают первый экран и разделы.

Файлы 6–9 сайт подхватывает сам, если положить их в папку `site/assets/img/empty/` (на хостинге — `assets/img/empty/`) с именами `cart`, `search`, `compare`, `404` (`.png` или `.webp`). Админка для них не нужна.

---

## 2. Стартовый промпт — вставить в новый чат ChatGPT целиком

```
You are the art director and 3D artist for AVTO-M, an online catalog of car parts: gas struts for car tailgates and hoods, and coolant expansion tanks. You will produce a SERIES of 10 images, one per message. After each image I will write "Дальше" (next) — then make the next image in the list. If I write a correction, redo the current image only.

SERIES STYLE — identical for every image:
- Photorealistic 3D product render, studio quality, like premium auto parts catalog key visuals (Bosch, Stabilus, Yandex Market promo).
- Light, airy palette: white, very light blue (#EEF3FC), cornflower blue accents (#0A5CFF), deep navy (#0C2F78) only in small details. No dark or moody scenes.
- Lighting: large softbox from top-left, gentle fill from the right, soft contact shadow under objects. No harsh specular blowouts.
- Materials: gas strut body = matte black powder-coated steel; piston rod = polished chrome with a soft reflection; end fittings = black plastic ball sockets. Coolant tank = semi-translucent milky-white polypropylene with a black screw cap and short hose spigots. Plastic UI objects = glossy white with rounded edges and blue elements.
- Camera: 3/4 view, slightly from above, 50–85 mm lens look, no fisheye distortion.
- ABSOLUTELY NO TEXT, letters, numbers, logos, brand names, labels, stickers, watermarks or license plates anywhere in the image. Plain surfaces only.
- No people, no hands, no faces.
- Transparent-background images: real alpha channel PNG, no painted checkerboard, no white box, object fills 80–90% of the frame with even margins, nothing cropped at the edges.

THE LIST:

1) banner-car — 1536x1024, TRANSPARENT background.
A modern white compact crossover (generic design, no real brand, no badges, no plate), 3/4 rear view, tailgate fully open, two black gas struts clearly visible holding the tailgate. Clean studio render, soft shadow under the wheels. The car must look generic and friendly, not a specific model.

2) cover-tanks — 1536x1024, WITH background (wide cover, the left 45% will be covered by a heading).
Close-up inside a clean modern engine bay: a milky-white coolant expansion tank with black cap in sharp focus on the RIGHT third of the frame. The LEFT 45% is calm and softly out of focus (dark grey engine cover and soft blue-grey tones) so white text can sit on top. Daylight, light-blue cold color grading consistent with the series, no dirt, no visible text on any part.

3) cover-hood — 1536x1024, WITH background, same composition rules as #2.
A silver car with an open hood photographed from the front-right, one black hood gas strut with chrome rod in sharp focus on the RIGHT third. Left 45% calm and softly out of focus. Bright daylight, light-blue grading.

4) card-params — 1024x1024, TRANSPARENT background.
A floating glossy white rounded-square panel, tilted about -6 degrees, with three horizontal slider tracks; each track has a cornflower-blue filled part and a round white knob with a blue ring, knobs at different positions (60%, 35%, 75%). Soft blue ambient shadow under the panel. Minimal, like a 3D app icon. No text or numbers.

5) card-lead — 1024x1024, TRANSPARENT background.
A glossy white 3D speech bubble with a thick navy outline and three navy dots inside, slightly tilted, and a small cornflower-blue 3D phone handset peeking from behind its lower right side. Soft shadow. Same plastic material as #4.

6) empty-cart — 1024x1024, TRANSPARENT background.
An empty 3D shopping basket made of glossy white plastic with cornflower-blue handles, slightly tilted, with soft shadow. Friendly and calm, not cartoonish.

7) empty-search — 1024x1024, TRANSPARENT background.
A 3D magnifying glass with a white handle and blue ring, lying diagonally over a single black gas strut with chrome rod. The lens shows a slightly magnified part of the rod. Soft shadow.

8) empty-compare — 1024x1024, TRANSPARENT background.
Minimalist 3D balance scales in glossy white with blue details; on each pan lies a small black gas strut. The scales are balanced. Soft shadow.

9) empty-404 — 1024x1024, TRANSPARENT background.
A glossy white and cornflower-blue 3D traffic cone next to a single black gas strut lying on the ground, as if a part fell off the road. Calm, a little humorous, no text, no numbers.

10) tile-template — 1536x1024, TRANSPARENT background.
A pair of black gas struts with chrome rods lying parallel at a 35-degree diagonal, bottom-right to top-left, the lower ends slightly cropped by the bottom-right corner of the frame (this is intentional for a catalog tile). Same light as the rest. This image is a template: later I may ask you to replace the struts with another part keeping exactly this angle, crop and light.

Start with image 1 now.
```

После каждой картинки пишите **«Дальше»**. Если что-то не так, уточняйте в том же чате, не начиная заново:

- «Фон должен быть по-настоящему прозрачным, без шахматки. Пересохрани как PNG с альфа-каналом».
- «Убери надпись/значок с детали».
- «Объект крупнее, на 85 % кадра, отступы ровные».
- «Свет мягче, как на предыдущей картинке».
- «Сделай левую половину спокойнее и темнее, на ней будет заголовок» (для обложек 2–3).

Для новой категории в будущем, в том же чате: *«Сделай как картинку 10, но вместо упоров — [например: шаровые наконечники упоров, пара]. Тот же угол, обрез и свет»*.

---

## 3. Проверка перед загрузкой (30 секунд на картинку)

1. Нет ни одной буквы, цифры, логотипа и номерного знака, даже мелких.
2. У «прозрачных» картинок фон действительно прозрачный: откройте файл в браузере на тёмной вкладке, белого прямоугольника быть не должно.
3. Деталь не обрезана по краям (кроме картинки 10 — там обрез задуман).
4. У упора гладкий цилиндр, шток хромированный, на концах шарнирные наконечники, без лишних рычагов.
5. У обложек 2–3 левая часть спокойная, заголовок на ней будет читаться.

Загружайте PNG как есть: сайт сам уменьшит картинки, сделает версии для телефона и сохранит прозрачность.

---

## 4. Что нельзя генерировать — нужны реальные фото

Это требование закона о защите прав потребителей и наших правил: покупатель должен видеть тот товар, который получит.

**Фото товаров LIFTAUTOPRO.** Сейчас у двух упоров (LAP-10993, LAP-10965) нет фото, на сайте стоит заглушка «Фото скоро появится». Как снять самим за 15 минут:
- Белый лист ватмана на столе у окна, дневной свет, без вспышки.
- Комплект — два упора рядом, по диагонали, как на картинке 10. Телефон держать горизонтально, сверху под углом 30–45°.
- 3–4 кадра: комплект целиком; наконечник крупно; шток крупно; упор рядом с рулеткой (видна длина).
- Если на корпусе есть наклейка LIFTAUTOPRO, её можно и нужно показать: это реальный товар.
- Загрузить в админке: Товары → нужный → Фото. Белый фон сайт оставит белым.

**Фото «О компании».** Реальные снимки склада или пункта выдачи в Балашихе: полки с коробками, упаковка заказа, проверка упора. Без лиц, если сотрудники не дали согласия. Без вывесок с текстом крупным планом. 2 фото, горизонтальные. Загрузить: Страницы → Главная → «О компании» → Фото 1 и 2.

**Логотип.** Нужен векторный файл (SVG, PDF или AI) от того, кто рисовал логотип. Сейчас на сайте логотип, аккуратно вырезанный из присланных картинок: на экране он чёткий, а для печати и вывески нужен вектор.

---

## 5. Как это легло в дизайн

- Баннер: светлый голубой фон, справа деталь на прозрачном фоне. Она плавает и чуть следует за курсором. Слайды с заголовком в фирменном наклонном шрифте, как в презентации.
- Плитки каталога: деталь «выезжает» из правого нижнего угла белой карточки, как на концепте IMG_1789.
- Карточки «Подбор по параметрам» и «Оставить заявку» с 3D-иллюстрацией на мягком сиреневом пятне. Пятно рисует сайт, картинка — только сам объект.
- Обложки разделов: фото на всю ширину, заголовок белым слева поверх затемнения.
- Пустые состояния — та же серия 3D-объектов. Человек видит понятную картинку и кнопки «Что делать дальше».
- Превью для соцсетей и мессенджеров (`og-default.jpg`) сайт уже собрал из логотипа и картинки «упор + бачок».
