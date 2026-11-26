#!/usr/bin/env python3
"""
Очищает студийные фото бачков AUTOPLASTIC от надписей по краям (название, модели, OEM, значки, QR, логотип):
находим всё, что отличается от голубого фона, удаляем пятна, целиком лежащие в «зонах надписей», и восстанавливаем фон.
Затем дополняем кадр фоном до квадрата. Отдельно вырезает логотип AUTOPLASTIC из левого нижнего угла.

python3 tools/clean-photos.py <папка_с_jpg> <папка_результата>      (нужны numpy и opencv-python-headless)
"""
import sys, os, glob
import numpy as np
import cv2

src, dst = sys.argv[1], sys.argv[2]
os.makedirs(dst, exist_ok=True)

# Зоны надписей в долях кадра: (x0, y0, x1, y1)
ZONES = [(0.0, 0.0, 0.205, 1.0),      # левая колонка: значки, QR, логотип
         (0.70, 0.0, 1.0, 0.255),     # справа сверху: «Бачок расширительный», модели
         (0.58, 0.80, 1.0, 1.0)]      # справа снизу: артикул и OEM


def in_zone(x, y, w, h, W, H):
    for zx0, zy0, zx1, zy1 in ZONES:
        if x >= zx0 * W - 2 and y >= zy0 * H - 2 and x + w <= zx1 * W + 2 and y + h <= zy1 * H + 2:
            return True
    return False


def segment(path):
    """Возвращает (изображение float, маска товара 0..1, цвет фона)."""
    img = cv2.imread(path)
    H, W = img.shape[:2]
    f = img.astype(np.float32)
    # цвет фона — медиана по верхнему, нижнему и правому краю
    border = np.concatenate([img[:20].reshape(-1, 3), img[-20:].reshape(-1, 3), img[:, -20:].reshape(-1, 3)]).astype(np.float32)
    bg = np.median(border, axis=0)
    dist = np.linalg.norm(f - bg, axis=2)
    soft = np.clip((dist - 24) / 36, 0, 1)                    # мягкая маска: полупрозрачный пластик и тени
    # грубая маска в 1/4 масштаба для поиска пятен
    s = 4
    sm = cv2.resize((dist > 40).astype(np.uint8), (W // s, H // s), interpolation=cv2.INTER_NEAREST)
    h, w = sm.shape
    zone = np.zeros_like(sm)
    for zx0, zy0, zx1, zy1 in ZONES:
        zone[int(zy0 * h):int(zy1 * h), int(zx0 * w):int(zx1 * w)] = 1
    opened = cv2.morphologyEx(sm, cv2.MORPH_OPEN, cv2.getStructuringElement(cv2.MORPH_ELLIPSE, (11, 11)))
    m = np.where(zone == 1, opened, sm)                        # в зонах надписей тонкие буквы исчезают, товар остаётся
    m = cv2.morphologyEx(m, cv2.MORPH_CLOSE, np.ones((5, 5), np.uint8))
    n, lab, st, _ = cv2.connectedComponentsWithStats(m, 8)
    if n <= 1:
        raise RuntimeError('товар не найден: ' + path)
    biggest = st[1:, cv2.CC_STAT_AREA].max()
    keep = np.zeros_like(m)
    for i in range(1, n):
        x, y, ww, hh, area = st[i]
        if area < biggest * 0.02 or in_zone(x, y, ww, hh, w, h):
            continue
        keep[lab == i] = 1
    tight = cv2.dilate(keep, np.ones((3, 3), np.uint8))        # в зонах надписей — только сам товар
    keep = cv2.dilate(keep, np.ones((9, 9), np.uint8))         # вне зон захватываем край и тень
    keep = np.where(zone == 1, tight, keep)
    keep = cv2.resize(keep.astype(np.float32), (W, H), interpolation=cv2.INTER_LINEAR)
    keep = cv2.GaussianBlur(keep, (0, 0), 5)
    alpha = np.clip(soft * np.clip(keep * 1.5, 0, 1), 0, 1)
    return f, alpha, bg, img


def cutout(path, size=1600):
    """Товар на прозрачном фоне (PNG RGBA), обрезан по краям."""
    f, alpha, bg, img = segment(path)
    # без тени: тень — тёмные полупрозрачные пиксели фона, отсекаем по яркости
    lum = f.mean(axis=2)
    alpha = alpha * np.clip((lum - 120) / 60, 0, 1)
    ys, xs = np.where(alpha > 0.04)
    y0, y1, x0, x1 = ys.min(), ys.max() + 1, xs.min(), xs.max() + 1
    rgb = f[y0:y1, x0:x1]
    # снимаем голубой оттенок фона, просвечивающий через пластик
    a = alpha[y0:y1, x0:x1][..., None]
    rgb = np.clip((rgb - bg * (1 - a) * 0.0), 0, 255)
    grey = rgb.mean(axis=2, keepdims=True)
    rgb = rgb * 0.55 + grey * 0.45
    rgba = np.dstack([rgb, alpha[y0:y1, x0:x1] * 255]).astype(np.uint8)
    k = size / max(rgba.shape[:2])
    if k < 1:
        rgba = cv2.resize(rgba, (int(rgba.shape[1] * k), int(rgba.shape[0] * k)), interpolation=cv2.INTER_AREA)
    return rgba


def clean(path, out_size=1800):
    f, alpha, bg, img = segment(path)
    ys, xs = np.where(alpha > 0.05)
    y0, y1, x0, x1 = ys.min(), ys.max() + 1, xs.min(), xs.max() + 1
    prod, a = f[y0:y1, x0:x1], alpha[y0:y1, x0:x1]
    # новый чистый фон: тот же голубой, мягкий свет из центра
    S = out_size
    yy, xx = np.mgrid[0:S, 0:S].astype(np.float32)
    r = np.sqrt(((xx - S * .5) / S) ** 2 + ((yy - S * .46) / S) ** 2)
    light = np.clip(1 - r * 1.6, 0, 1)[..., None]
    canvas = bg[None, None, :] * (0.93 + 0.1 * light)
    # товар — до 86% ширины и 80% высоты, чуть ниже центра
    k = min(S * 0.86 / prod.shape[1], S * 0.80 / prod.shape[0])
    pw, ph = int(prod.shape[1] * k), int(prod.shape[0] * k)
    prod = cv2.resize(prod, (pw, ph), interpolation=cv2.INTER_AREA)
    a = cv2.resize(a, (pw, ph), interpolation=cv2.INTER_AREA)[..., None]
    px, py = (S - pw) // 2, int((S - ph) * 0.56)
    region = canvas[py:py + ph, px:px + pw]
    canvas[py:py + ph, px:px + pw] = prod * a + region * (1 - a)
    noise = np.random.default_rng(1).normal(0, 1.2, canvas.shape).astype(np.float32)
    return np.clip(canvas + noise, 0, 255).astype(np.uint8), img


def logo(img):
    """Белый логотип AUTOPLASTIC из левого нижнего угла → PNG с прозрачностью (без QR и края товара)."""
    H, W = img.shape[:2]
    crop = img[int(H * 0.78):int(H * 0.97), int(W * 0.02):int(W * 0.18)]
    hsv = cv2.cvtColor(crop, cv2.COLOR_BGR2HSV)
    alpha = np.clip((255 - hsv[..., 1].astype(np.float32) * 2.2) * (hsv[..., 2] > 180), 0, 255).astype(np.uint8)
    n, lab, st, _ = cv2.connectedComponentsWithStats((alpha > 90).astype(np.uint8), 8)
    ch, cw = alpha.shape
    keep = np.zeros_like(alpha)
    for i in range(1, n):
        x, y, w, h, a = st[i]
        if a < 40 or y <= 1 or x + w >= cw - 1 or y + h < ch * 0.4:   # отбрасываем QR сверху и край товара справа
            continue
        keep[lab == i] = 1
    keep = cv2.dilate(keep, np.ones((5, 5), np.uint8))
    alpha = alpha * keep
    ys, xs = np.where(alpha > 60)
    alpha = alpha[ys.min():ys.max() + 1, xs.min():xs.max() + 1]
    rgba = np.zeros((*alpha.shape, 4), np.uint8)
    rgba[..., :3] = 255
    rgba[..., 3] = alpha
    return rgba


files = sorted(glob.glob(os.path.join(src, '**', '*.jpg'), recursive=True))
if os.environ.get('CUTOUT'):
    for f in files:
        cv2.imwrite(os.path.join(dst, os.path.splitext(os.path.basename(f))[0] + '-cut.png'), cutout(f))
    sys.exit()
for f in files:
    name = os.path.splitext(os.path.basename(f))[0]
    res, orig = clean(f)
    cv2.imwrite(os.path.join(dst, name + '.jpg'), res, [cv2.IMWRITE_JPEG_QUALITY, 90])
    print(name)
if files:
    cv2.imwrite(os.path.join(dst, 'logo-autoplastic-white.png'), logo(cv2.imread(files[0])))
