#!/usr/bin/env python3
"""Ищет в site.css правила для классов, которых нет ни в шаблонах, ни в скриптах.

python3 tools/css-unused.py          — показать неиспользуемые селекторы
python3 tools/css-unused.py --fix    — удалить их (правило целиком, если в нём не осталось селекторов)

Классы, которые собираются в коде из частей (t-<тема>, b-<метка>), считаются используемыми.
"""
import re
import sys
from pathlib import Path

ROOT = Path(__file__).resolve().parent.parent / 'site'
CSS = ROOT / 'assets' / 'css' / 'site.css'
corpus = ''
for pattern in ('app/views/*.php', 'app/lib/*.php', 'assets/js/site.js', 'assets/js/interactive.js', 'index.php'):
    for f in ROOT.glob(pattern):
        corpus += f.read_text(encoding='utf-8')
DYNAMIC = ('t-', 'b-', 'q-', 'ms-')  # темы слайдов, метки товаров, квиз и «упор по старому» собирают классы в коде

def used(cls):
    if cls.startswith(DYNAMIC):
        return True
    return re.search(r'(?<![\w-])' + re.escape(cls) + r'(?![\w-])', corpus) is not None

def dead(selector):
    classes = re.findall(r'\.([a-zA-Z_][\w-]*)', re.sub(r'url\([^)]*\)', '', selector))
    return any(not used(c) for c in classes)

src = CSS.read_text(encoding='utf-8')
out, i, removed = [], 0, []
# простой разбор: правило = селекторы { тело без вложенных скобок }; @-правила обходим рекурсивно по скобкам
def walk(text):
    res, pos = [], 0
    while pos < len(text):
        m = re.compile(r'([^{}]*)\{').match(text, pos)
        if not m:
            res.append(text[pos:]); break
        head = m.group(1)
        depth, j = 1, m.end()
        while depth and j < len(text):
            depth += {'{': 1, '}': -1}.get(text[j], 0); j += 1
        body = text[m.end():j - 1]
        h = head.strip()
        if h.startswith('@media') or h.startswith('@supports') or h.startswith('@container'):
            inner = walk(body)
            res.append(head + '{' + inner + '}' if inner.strip() else '')
        elif h.startswith('@'):
            res.append(text[pos:j])
        else:
            # комментарии перед правилом отделяем от селекторов
            cm = re.match(r'((?:\s*/\*.*?\*/)*\s*)(.*)', head, re.S)
            prefix, sel = cm.group(1), cm.group(2)
            sels = [x.strip() for x in sel.split(',')]
            keep = [x for x in sels if not dead(x)]
            removed.extend(x for x in sels if x not in keep)
            if keep:
                res.append(prefix + ', '.join(keep) + ' {' + body + '}')
            else:
                # заголовки разделов оставляем, пояснения к удалённому правилу — нет
                res.append(''.join(re.findall(r'\s*/\* -{5,}.*?\*/|\s*/\* ={5,}.*?\*/', prefix, re.S)))
        pos = j
    return ''.join(res)

new = walk(src)
new = re.sub(r'\n{3,}', '\n\n', new)
for s in removed:
    print(s)
print(f'— неиспользуемых селекторов: {len(removed)}', file=sys.stderr)
if '--fix' in sys.argv:
    CSS.write_text(new, encoding='utf-8')
