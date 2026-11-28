#!/usr/bin/env python3
"""Чистит и нарезает листы с 3D-графикой заказчика (assets-src/2611/sheet-*.png).

У присланных PNG объекты почти непрозрачные (альфа 252–253), а вокруг — россыпь
полупрозрачных точек. Скрипт:
  1. убирает слабую альфу (< 48) и делает объекты полностью непрозрачными;
  2. выбрасывает мелкие островки (точки, пыль);
  3. делит лист на отдельные предметы и сохраняет каждый с прозрачным полем.

Запуск: python3 tools/cut-sheets.py   (нужны Pillow, numpy, scipy)
Результат: assets-src/2611/cut/<лист>-<n>.png — дальше их раскладывает prepare-2611.py.
"""
from pathlib import Path
import numpy as np
from PIL import Image
from scipy import ndimage as nd

ROOT = Path(__file__).resolve().parent.parent
SRC = ROOT / 'assets-src' / '2611'
OUT = SRC / 'cut'
OUT.mkdir(exist_ok=True)


def clean(im):
    a = np.asarray(im.getchannel('A')).astype(np.int32)
    a[a < 48] = 0
    a[a >= 232] = 255
    core = a >= 160
    lab, n = nd.label(core)
    if n:
        sizes = nd.sum(core, lab, range(1, n + 1))
        keep = np.zeros(n + 1, bool)
        keep[1:] = sizes >= core.size * 0.0004
        core = keep[lab]
    # мягкий край оставляем только рядом с крупными объектами
    near = nd.binary_dilation(core, iterations=3)
    a[~near] = 0
    out = im.copy()
    out.putalpha(Image.fromarray(a.astype(np.uint8)))
    return out, core


def split(name, gap=8, min_share=0.004, cut=None):
    im = Image.open(SRC / f'{name}.png').convert('RGBA')
    im, core = clean(im)
    grouped = nd.binary_dilation(core, iterations=gap)
    if cut:  # объекты касаются друг друга — режем по горизонтали: (x0, x1, y)
        x0, x1, y = cut
        grouped[y - 1:y + 1, x0:x1] = False
    lab, n = nd.label(grouped)
    parts = []
    for i, sl in enumerate(nd.find_objects(lab), 1):
        if core[sl].sum() < core.size * min_share:
            continue
        mask = (lab == i)
        a = np.asarray(im.getchannel('A')).copy()
        a[~mask] = 0
        piece = im.copy()
        piece.putalpha(Image.fromarray(a))
        box = piece.getchannel('A').getbbox()
        piece = piece.crop(box)
        parts.append((box[1] // 120, box[0], piece))   # строка, затем столбец
    parts.sort(key=lambda p: (p[0], p[1]))
    for k, (_, _, piece) in enumerate(parts, 1):
        pad = round(max(piece.size) * 0.04)
        canvas = Image.new('RGBA', (piece.width + 2 * pad, piece.height + 2 * pad), (0, 0, 0, 0))
        canvas.alpha_composite(piece, (pad, pad))
        canvas.save(OUT / f'{name}-{k}.png', optimize=True)
        print(name, k, canvas.size)


split('sheet-icons-8')
split('sheet-cone-basket-scales-search', cut=(0, 720, 548))
split('sheet-struts-tanks', gap=18)
split('sheet-tiles', gap=24)

# знак для фавиконки
fav, _ = clean(Image.open(SRC / 'favicon-am.png').convert('RGBA'))
fav = fav.crop(fav.getchannel('A').getbbox())
fav.save(OUT / 'favicon-am.png', optimize=True)
print('favicon', fav.size)
