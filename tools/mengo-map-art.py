#!/usr/bin/env python3
"""
Arte do 1º banner da Mengo: mapas do Rio de Janeiro, do Brasil e da América do Sul
em vermelho, com fumaça, coroas e as marcas (CRF e âncora) — no estilo do banner da Palestra.

Uso: mengo-map-art.py <brazil-states.geojson> <countries.geo.json> <pasta-arte> <saida.webp>
"""
import json, math, random, sys
from PIL import Image, ImageDraw, ImageFilter, ImageChops

STATES, WORLD, ART, OUT = sys.argv[1:5]
random.seed(7)
S = 2
W, H = 736 * S, 980 * S
RED = (214, 18, 44, 255)
RED_DARK = (150, 8, 28, 255)
SMOKE = (225, 20, 45)

def polys(geom):
    if geom['type'] == 'Polygon':
        return [geom['coordinates'][0]]
    return [p[0] for p in geom['coordinates']]

states = json.load(open(STATES))
world = json.load(open(WORLD))
rj = [p for f in states['features'] if f['properties'].get('sigla') == 'RJ' for p in polys(f['geometry'])]
br = [p for f in world['features'] if f['id'] == 'BRA' for p in polys(f['geometry'])]
SA = {'ARG', 'BOL', 'BRA', 'CHL', 'COL', 'ECU', 'GUY', 'PER', 'PRY', 'SUR', 'URY', 'VEN', 'GUF'}
sa = [p for f in world['features'] if f['id'] in SA for p in polys(f['geometry'])]
sa = [p for p in sa if min(pt[0] for pt in p) > -95]  # sem Galápagos

def project(rings, box):
    lat0 = sum(pt[1] for r in rings for pt in r) / sum(len(r) for r in rings)
    k = math.cos(math.radians(lat0))
    xs = [pt[0] * k for r in rings for pt in r]; ys = [-pt[1] for r in rings for pt in r]
    minx, maxx, miny, maxy = min(xs), max(xs), min(ys), max(ys)
    bx, by, bw, bh = box
    sc = min(bw / (maxx - minx), bh / (maxy - miny))
    ox = bx + (bw - (maxx - minx) * sc) / 2; oy = by + (bh - (maxy - miny) * sc) / 2
    out = [[(ox + (pt[0] * k - minx) * sc, oy + (-pt[1] - miny) * sc) for pt in r] for r in rings]
    big = max(out, key=len)
    cx = sum(p[0] for p in big) / len(big); cy = sum(p[1] for p in big) / len(big)
    return out, (cx, cy), (ox, oy, (maxx - minx) * sc, (maxy - miny) * sc)

def smoke(img, cx, cy, rx, ry, side):
    layer = Image.new('L', img.size, 0); d = ImageDraw.Draw(layer)
    for _ in range(70):
        a = random.uniform(0, 2 * math.pi); r = random.uniform(0, 1) ** 0.6
        x = cx + side * rx * 0.45 + math.cos(a) * rx * 0.55 * r; y = cy + math.sin(a) * ry * 0.7 * r
        s = random.uniform(0.06, 0.16) * rx
        d.ellipse([x - s, y - s, x + s, y + s], fill=random.randint(60, 140))
    layer = layer.filter(ImageFilter.GaussianBlur(18 * S))
    for _ in range(90):  # respingos
        a = random.uniform(0, 2 * math.pi); r = random.uniform(0.35, 0.8)
        x = cx + side * rx * 0.45 + math.cos(a) * rx * r; y = cy + math.sin(a) * ry * r
        s = random.uniform(0.6, 2.2) * S
        ImageDraw.Draw(layer).ellipse([x - s, y - s, x + s, y + s], fill=random.randint(120, 220))
    col = Image.new('RGBA', img.size, SMOKE + (255,)); col.putalpha(layer)
    img.alpha_composite(col)

def crown(d, x, y, w, color):
    h = w * 0.55
    pts = [(x - w / 2, y), (x - w / 2, y - h), (x - w / 4, y - h * 0.45), (x, y - h * 1.05),
           (x + w / 4, y - h * 0.45), (x + w / 2, y - h), (x + w / 2, y)]
    d.polygon(pts, fill=color)
    for px in (x - w / 2, x, x + w / 2):
        py = y - h * (1.05 if px == x else 1.0)
        d.ellipse([px - w * 0.07, py - w * 0.07, px + w * 0.07, py + w * 0.07], fill=color)
    d.rectangle([x - w / 2, y + w * 0.06, x + w / 2, y + w * 0.16], fill=color)

def star(d, x, y, r, color):
    pts = []
    for i in range(10):
        a = -math.pi / 2 + i * math.pi / 5; rr = r if i % 2 == 0 else r * 0.45
        pts.append((x + math.cos(a) * rr, y + math.sin(a) * rr))
    d.polygon(pts, fill=color)

def stamp(img, mask_path, cx, cy, h):
    m = Image.open(mask_path).convert('L')
    w = int(m.width * h / m.height); m = m.resize((w, int(h)), Image.LANCZOS)
    white = Image.new('RGBA', m.size, (255, 255, 255, 255)); white.putalpha(m)
    img.alpha_composite(white, (int(cx - w / 2), int(cy - h / 2)))

img = Image.new('RGBA', (W, H), (0, 0, 0, 0))
items = [
    (rj, (W * 0.30, H * 0.03, W * 0.46, H * 0.25), +1, ART + '/crest-anchor.png', 0.48),
    (br, (W * 0.22, H * 0.30, W * 0.50, H * 0.33), -1, ART + '/crest-crf.png', 0.36),
    (sa, (W * 0.30, H * 0.62, W * 0.42, H * 0.37), -1, ART + '/crest-crf.png', 0.26),
]
shapes = []
for rings, box, side, crest, ch in items:
    out, c, bb = project(rings, box)
    smoke(img, c[0], c[1], bb[2] * 0.75, bb[3] * 0.55, side)
    shapes.append((out, c, bb, crest, ch))
d = ImageDraw.Draw(img)
for out, c, bb, crest, ch in shapes:
    for r in out:
        if len(r) > 2:
            d.polygon(r, fill=RED)
for out, c, bb, crest, ch in shapes:
    h = min(bb[2], bb[3]) * ch
    stamp(img, crest, c[0], c[1] + h * 0.05, h)
    d = ImageDraw.Draw(img)
    if 'anchor' in crest:
        star(d, c[0], c[1] - h * 0.68, h * 0.09, (255, 255, 255, 255))
    crown(d, bb[0] + bb[2] * 0.82, bb[1] + bb[3] * 0.08, bb[2] * 0.11, (255, 255, 255, 235))
img = img.resize((W // S, H // S), Image.LANCZOS)
img.save(OUT, 'WEBP', quality=88, method=6)
print('ok', OUT, img.size)
