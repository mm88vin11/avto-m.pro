#!/usr/bin/env python3
"""Раскладывает графику заказчика от 26.11 по местам на сайте.

Сначала: python3 tools/cut-sheets.py (чистит фон и нарезает листы).
Потом:   python3 tools/prepare-2611.py

Что получается:
  site/assets/img/ui/*.webp       — 3D-значки преимуществ и карточек
  site/assets/img/empty/*.webp    — картинки пустой корзины, сравнения, поиска, страницы 404
  site/app/seed/photos/*          — плитки каталога, баннеры (уходят в базу при первом запуске)
  site/assets/icons/*, favicon.ico — значок сайта
  site/assets/img/og-default.jpg  — превью ссылки в мессенджерах и соцсетях
"""
import struct
from io import BytesIO
from pathlib import Path
from PIL import Image

ROOT = Path(__file__).resolve().parent.parent
SRC = ROOT / 'assets-src' / '2611'
CUT = SRC / 'cut'
SITE = ROOT / 'site'
UI = SITE / 'assets' / 'img' / 'ui'
EMPTY = SITE / 'assets' / 'img' / 'empty'
ICONS = SITE / 'assets' / 'icons'
SEED = SITE / 'app' / 'seed' / 'photos'


def fit(im, box):
    """Уменьшает так, чтобы большая сторона была не больше box (без увеличения)."""
    im = im.copy()
    im.thumbnail((box, box), Image.LANCZOS)
    return im


def square(im, size, pad=0.06):
    """Предмет по центру прозрачного квадрата."""
    inner = round(size * (1 - 2 * pad))
    im = fit(im, inner) if max(im.size) > inner else im.resize((round(im.width * inner / max(im.size)), round(im.height * inner / max(im.size))), Image.LANCZOS)
    out = Image.new('RGBA', (size, size), (0, 0, 0, 0))
    out.alpha_composite(im, ((size - im.width) // 2, (size - im.height) // 2))
    return out


def cut(name):
    return Image.open(CUT / f'{name}.png').convert('RGBA')


# 1. значки (имена — как в icon3d() в app/lib/render.php)
for n, name in enumerate(['shield', 'truck', 'box', 'chat', 'tag', 'car', 'sliders', 'gear'], 1):
    square(cut(f'sheet-icons-8-{n}'), 256).save(UI / f'{name}.webp', quality=90, method=6)
    print('ui', name)

# 2. пустые состояния
for n, name in enumerate(['404', 'cart', 'compare', 'search'], 1):
    fit(cut(f'sheet-cone-basket-scales-search-{n}'), 480).save(EMPTY / f'{name}.webp', quality=90, method=6)
    print('empty', name)

# 3. плитки каталога и карточки главной — в начальное наполнение
for src, name in [('sheet-tiles-4', 'tile-struts'), ('sheet-struts-tanks-3', 'tile-tank'), ('sheet-tiles-1', 'tile-car'),
                  ('sheet-tiles-3', 'tile-params'), ('sheet-tiles-2', 'tile-lead'),
                  ('sheet-struts-tanks-2', 'art-strut-one'), ('sheet-struts-tanks-4', 'art-tank-kit')]:
    fit(cut(src), 900).save(SEED / f'{name}.png', optimize=True)
    print('seed', name)

# 4. баннеры главной
for src, name in [('banner-avtozapchasti', 'banner-1'), ('banner-upory', 'banner-2'), ('banner-bachki', 'banner-3')]:
    Image.open(SRC / f'{src}.png').convert('RGB').save(SEED / f'{name}.jpg', quality=90, optimize=True, progressive=True)
    print('banner', name)

# 5. значок сайта
mark = cut('favicon-am')


def icon(size, pad, bg=None, radius=0.22):
    out = Image.new('RGBA', (size, size), (0, 0, 0, 0))
    if bg:
        from PIL import ImageDraw
        ImageDraw.Draw(out).rounded_rectangle([0, 0, size - 1, size - 1], radius=round(size * radius), fill=bg)
    inner = size * (1 - 2 * pad)
    k = min(inner / mark.width, inner / mark.height)
    m = mark.resize((max(1, round(mark.width * k)), max(1, round(mark.height * k))), Image.LANCZOS)
    out.alpha_composite(m, ((size - m.width) // 2, (size - m.height) // 2))
    return out


WHITE = (255, 255, 255, 255)
icon(16, 0.02, WHITE, 0.2).save(ICONS / 'favicon-16.png', optimize=True)
icon(32, 0.04, WHITE, 0.2).save(ICONS / 'favicon-32.png', optimize=True)
icon(180, 0.12, WHITE, 0).convert('RGB').save(ICONS / 'apple-touch-icon.png', optimize=True)
icon(192, 0.1, WHITE).save(ICONS / 'icon-192.png', optimize=True)
icon(512, 0.1, WHITE).save(ICONS / 'icon-512.png', optimize=True)
icon(512, 0.2, WHITE, 0).save(ICONS / 'icon-maskable-512.png', optimize=True)


def write_ico(path, images):
    """ICO с PNG внутри (так поддерживают все браузеры с Windows Vista)."""
    blobs = []
    for im in images:
        b = BytesIO()
        im.save(b, 'PNG', optimize=True)
        blobs.append((im.size[0], b.getvalue()))
    head = struct.pack('<HHH', 0, 1, len(blobs))
    offset = 6 + 16 * len(blobs)
    entries, data = b'', b''
    for size, blob in blobs:
        entries += struct.pack('<BBBBHHII', size % 256, size % 256, 0, 0, 1, 32, len(blob), offset)
        offset += len(blob)
        data += blob
    Path(path).write_bytes(head + entries + data)


write_ico(SITE / 'favicon.ico', [icon(16, 0.02, WHITE, 0.2), icon(32, 0.04, WHITE, 0.2), icon(48, 0.04, WHITE, 0.2)])
print('favicon')

# 6. превью ссылки: 1200×630
og = Image.open(SRC / 'og-preview.png').convert('RGB')
w, h = og.size
th = round(w * 630 / 1200)
og = og.crop((0, (h - th) // 2, w, (h - th) // 2 + th)) if th <= h else og
og.resize((1200, 630), Image.LANCZOS).save(SITE / 'assets' / 'img' / 'og-default.jpg', quality=88, optimize=True, progressive=True)
print('og')
