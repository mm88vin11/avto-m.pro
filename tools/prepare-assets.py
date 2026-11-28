#!/usr/bin/env python3
"""Готовит статические ассеты из assets-src/: логотипы, фавиконки, превью для соцсетей, фото для начального наполнения.
Запуск из корня репозитория: python3 tools/prepare-assets.py  (нужен Pillow).
Значок сайта, превью ссылки, 3D-значки и плитки теперь из графики заказчика от 26.11 — после этого скрипта
запустите tools/cut-sheets.py и tools/prepare-2611.py, иначе они вернутся к прежним."""
from pathlib import Path
from PIL import Image, ImageDraw, ImageFilter

ROOT = Path(__file__).resolve().parent.parent
SRC = ROOT / 'assets-src'
IMG = ROOT / 'site' / 'assets' / 'img'
ICO = ROOT / 'site' / 'assets' / 'icons'
SEED = ROOT / 'site' / 'app' / 'seed' / 'photos'
for d in (IMG, ICO, SEED):
    d.mkdir(parents=True, exist_ok=True)

NAVY = (10, 31, 77)
BLUE = (10, 92, 255)

# 1. Логотип: белый (для тёмного фона) и маска для шапки
logo = Image.open(SRC / 'logo-avto-m-white.png').convert('RGBA')
logo = logo.crop(logo.getchannel('A').getbbox())
w = 640
logo_s = logo.resize((w, round(logo.height * w / logo.width)), Image.LANCZOS)
logo_s.save(IMG / 'logo-white.png', optimize=True)
logo_s.save(IMG / 'logo-white.webp', quality=92, method=6)
print('logo', logo_s.size)

# 2. Знак AM: вырезаем по непрозрачным небелым пикселям
am = Image.open(SRC / 'avatar-am.png').convert('RGBA')
px = am.load()
mask = Image.new('L', am.size, 0)
mp = mask.load()
for y in range(am.height):
    for x in range(am.width):
        r, g, b, a = px[x, y]
        if a > 20 and min(r, g, b) < 235:
            mp[x, y] = 255
box = mask.getbbox()
mark = am.crop(box)
mark_alpha = mask.crop(box).filter(ImageFilter.GaussianBlur(0.6))
mark.putalpha(mark_alpha)
mark.save(IMG / 'mark-am.png', optimize=True)


def icon(size, pad=0.14, bg=(255, 255, 255, 255), radius=0.22):
    im = Image.new('RGBA', (size, size), (0, 0, 0, 0))
    d = ImageDraw.Draw(im)
    if bg:
        d.rounded_rectangle([0, 0, size - 1, size - 1], radius=round(size * radius), fill=bg)
    inner = size * (1 - 2 * pad)
    k = min(inner / mark.width, inner / mark.height)
    m = mark.resize((max(1, round(mark.width * k)), max(1, round(mark.height * k))), Image.LANCZOS)
    im.alpha_composite(m, ((size - m.width) // 2, (size - m.height) // 2))
    return im


icon(32, pad=0.04, radius=0.18).save(ICO / 'favicon-32.png')
icon(16, pad=0.02, radius=0.15).save(ICO / 'favicon-16.png')
icon(48, pad=0.04, radius=0.18).save(ROOT / 'site' / 'favicon.ico', sizes=[(16, 16), (32, 32), (48, 48)])
icon(180, pad=0.1, radius=0).convert('RGB').save(ICO / 'apple-touch-icon.png')
icon(192).save(ICO / 'icon-192.png')
icon(512).save(ICO / 'icon-512.png')
icon(512, pad=0.22, radius=0).save(ICO / 'icon-maskable-512.png')

# 3. Превью для соцсетей 1200×630: тёмно-синий градиент, логотип, фото упоров
W, H = 1200, 630
og = Image.new('RGB', (W, H), NAVY)
grad = Image.new('RGB', (W, H))
gd = ImageDraw.Draw(grad)
for x in range(W):
    t = x / W
    gd.line([(x, 0), (x, H)], fill=(round(8 + 10 * t), round(24 + 40 * t), round(64 + 120 * t)))
og = grad
glow = Image.new('L', (W, H), 0)
ImageDraw.Draw(glow).ellipse([700, 60, 1260, 620], fill=150)
glow = glow.filter(ImageFilter.GaussianBlur(90))
og = Image.composite(Image.new('RGB', (W, H), (60, 130, 255)), og, glow)
lw = 520
lg = logo.resize((lw, round(logo.height * lw / logo.width)), Image.LANCZOS)
og.paste(lg, (80, (H - lg.height) // 2), lg)
# упоры и бачок без фона (сгенерированная иллюстрация из assets-src/gen)
art = Image.open(SRC / 'gen' / 'banner-combo.png').convert('RGBA')
art = art.crop(art.getchannel('A').getbbox())
k = min(480 / art.width, 420 / art.height)
art = art.resize((round(art.width * k), round(art.height * k)), Image.LANCZOS)
sh = Image.new('L', (W, H), 0)
ImageDraw.Draw(sh).ellipse([700, 520, 1160, 572], fill=110)
sh = sh.filter(ImageFilter.GaussianBlur(18))
og = Image.composite(Image.new('RGB', (W, H), (4, 14, 40)), og, sh)
og.paste(art, (920 - art.width // 2, 530 - art.height), art)
og.save(IMG / 'og-default.jpg', quality=88, optimize=True)

# 4. Фото для начального наполнения (единый формат 900×1200, jpg)
for f in sorted(SRC.iterdir()):
    if f.name.startswith(('lap-', 'av-')) and f.suffix in ('.png', '.jpg', '.webp'):
        im = Image.open(f).convert('RGB')
        if f.name.startswith('lap-'):  # у исходников упоров тёмная кромка по краю — срезаем 8 точек
            im = im.crop((8, 8, im.width - 8, im.height - 8))
        if f.name.startswith('lap-') and im.size != (900, 1200):
            im = im.resize((900, 1200), Image.LANCZOS)
        elif max(im.size) > 1800:
            im.thumbnail((1800, 1800), Image.LANCZOS)
        im.save(SEED / (f.stem + '.jpg'), quality=90, optimize=True)
print('ok')
