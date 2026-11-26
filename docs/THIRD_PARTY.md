# Сторонние компоненты

Сайт и панель управления написаны специально для AVTO-M, без готовой CMS, фреймворков и JS-библиотек. Используются только:

| Компонент | Где | Лицензия |
|---|---|---|
| Шрифт **Onest** (вариативный, кириллица и латиница), Onest Project Authors | `site/assets/fonts/` | SIL Open Font License 1.1 (`OFL.txt` рядом) |
| Шрифт **Exo 2** (наклонный, 900), Exo 2 Project Authors — заголовки баннера и страниц | `site/assets/fonts/` | SIL Open Font License 1.1 (`OFL-Exo2.txt`) |
| Контуры иконок на основе набора **Lucide** | `site/app/views/_icons.php` | ISC |
| **PHP 8**, **SQLite**, расширение **GD** | хостинг | лицензии PHP и SQLite (public domain) |
| **Яндекс Метрика** (подключается после согласия), **Яндекс Карты** (по кнопке) | внешние сервисы ООО «Яндекс» | условия использования Яндекса |

Инструменты разработки, не входящие в сайт: Python + Pillow (`tools/prepare-assets.py`), Playwright (`tools/qa*.mjs`).
