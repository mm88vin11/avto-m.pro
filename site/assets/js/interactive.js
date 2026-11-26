/* AVTO-M · интерактивы: квиз «Подбор за 30 секунд» и «Подберите упор по старому». Без библиотек. */
(function () {
  'use strict';
  var d = document;
  var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  var $ = function (s, r) { return (r || d).querySelector(s); };
  var $$ = function (s, r) { return Array.prototype.slice.call((r || d).querySelectorAll(s)); };
  var esc = function (s) { return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]; }); };
  var icon = function (n) { return '<svg class="i" aria-hidden="true"><use href="#i-' + n + '"/></svg>'; };
  var plural = function (n, a, b, c) { n = Math.abs(n) % 100; var n1 = n % 10; if (n > 10 && n < 20) return c; if (n1 > 1 && n1 < 5) return b; if (n1 === 1) return a; return c; };
  var goal = function (g) { if (window.avtomGoal) window.avtomGoal(g); };
  var refresh = function () { if (window.avtomCounts) window.avtomCounts(); };
  var norm = function (s) { return String(s || '').toLowerCase().replace(/[^a-z0-9а-яё]/gi, ''); };

  /* ---------- рисунки для плиток (свои, в стиле иконок сайта) ---------- */
  var ART = {
    strut: '<svg viewBox="0 0 64 64" aria-hidden="true"><path class="a-body" d="M14 50 30 34"/><path class="a-rod" d="M30 34 48 16"/><circle cx="12" cy="52" r="5"/><circle cx="50" cy="14" r="5"/><path class="a-ring" d="m27 31 6 6"/></svg>',
    tank: '<svg viewBox="0 0 64 64" aria-hidden="true"><rect x="12" y="20" width="36" height="30" rx="9"/><rect x="21" y="11" width="13" height="9" rx="2.5"/><path class="a-level" d="M12 36c6-3 12 3 18 0s12-3 18 0"/><path d="M48 40h8M8 30h4"/></svg>',
    box: '<svg viewBox="0 0 64 64" aria-hidden="true"><path d="M52 21 32 10 12 21v22l20 11 20-11z"/><path d="m12 21 20 11 20-11M32 32v22"/></svg>',
    unsure: '<svg viewBox="0 0 64 64" aria-hidden="true"><circle cx="32" cy="32" r="21"/><path d="M25 26a7 7 0 1 1 10 6.3c-2 1-3 2.5-3 4.7"/><circle class="a-dot" cx="32" cy="44" r="1.6"/></svg>',
    code: '<svg viewBox="0 0 64 64" aria-hidden="true"><rect x="9" y="16" width="46" height="32" rx="7"/><path d="M18 26v12M23 26v12M29 26v12M33 26v12M39 26v12M45 26v12"/></svg>',
    car: '<svg viewBox="0 0 64 64" aria-hidden="true"><path d="M12 42H8v-9l5-11a4 4 0 0 1 3.6-2.4h30.8A4 4 0 0 1 51 22l5 11v9h-4"/><path d="M8 33h48M24 42h16"/><circle cx="18" cy="42" r="5"/><circle cx="46" cy="42" r="5"/></svg>'
  };
  // силуэты кузовов (вид сбоку, перед слева)
  var BODY = {
    'Седан': 'M6 33v-7q0-4 6-5l17-2 11-10h21l11 9 16 2q4 1 4 5v8',
    'Хэтчбек': 'M6 33v-7q0-4 6-5l17-2 11-10h29q4 1 6 6l4 9v9',
    'Универсал': 'M6 33v-7q0-4 6-5l17-2 11-10h40q4 1 5 6l2 9v9',
    'Лифтбек': 'M6 33v-7q0-4 6-5l17-2 11-10h18l24 9q4 1 4 5v8',
    'Кроссовер': 'M6 33v-9q0-5 6-6l15-2 10-10h40q5 1 7 6l3 11v10',
    'Минивэн': 'M6 33v-10q0-5 7-7l16-12h50q5 1 6 6l2 13v10',
    'Компактвэн': 'M6 33v-9q0-5 7-6l16-11h46q5 1 6 6l2 11v9',
    'Фургон': 'M6 33v-9q0-5 7-6l12-11h20v-3h41q3 0 3 3v26',
    'Купе': 'M6 33v-7q0-4 6-5l19-2 13-8h15l14 8 13 2q4 1 4 5v7'
  };
  function bodyArt(b) {
    var p = BODY[b] || BODY['Седан'];
    return '<svg class="q-body-art" viewBox="0 0 100 44" aria-hidden="true"><path d="' + p + '"/><path class="q-ground" d="M2 39h96"/><circle cx="24" cy="34" r="5.5"/><circle cx="74" cy="34" r="5.5"/></svg>';
  }

  /* =========================== квиз =========================== */
  function Quiz(root) {
    var data; try { data = JSON.parse($('[data-q-data]', root).textContent); } catch (e) { return; }
    var stage = $('[data-q-stage]', root), dots = $$('[data-q-dots] li', root), trail = $('[data-q-trail]', root);
    var st = { cat: null, make: null, model: null, body: null, via: null }, history = [];
    var catBy = {}; data.cats.forEach(function (c) { catBy[c.s] = c; });
    var store = { get: function (k) { try { return JSON.parse(localStorage.getItem(k)); } catch (e) { return null; } }, set: function (k, v) { try { localStorage.setItem(k, JSON.stringify(v)); } catch (e) {} } };

    function itemsOf(cat) { return data.items.filter(function (it) { return !cat || it.cat === cat; }); }
    function makes(cat) {
      var m = {}; itemsOf(cat).forEach(function (it) { it.cp.forEach(function (c) { m[c.mk] = m[c.mk] || { s: c.mk, n: c.mkn, models: {} }; m[c.mk].models[c.md] = 1; }); });
      return Object.keys(m).map(function (k) { return m[k]; }).sort(function (a, b) { return a.n.localeCompare(b.n); });
    }
    function models(cat, mk) {
      var m = {}; itemsOf(cat).forEach(function (it) { it.cp.forEach(function (c) { if (c.mk !== mk) return; var x = m[c.md] = m[c.md] || { s: c.md, n: c.mdn, yf: c.yf, yt: c.yt, b: {}, cnt: 0 }; x.cnt++; if (c.yf && (!x.yf || c.yf < x.yf)) x.yf = c.yf; if (c.yt && (!x.yt || c.yt > x.yt)) x.yt = c.yt; c.b.forEach(function (b) { x.b[b] = 1; }); }); });
      return Object.keys(m).map(function (k) { return m[k]; }).sort(function (a, b) { return a.n.localeCompare(b.n, 'ru', { numeric: true }); });
    }
    function years(m) { return m.yf && m.yt ? m.yf + '–' + m.yt : (m.yf ? 'с ' + m.yf : ''); }
    function match() {
      return itemsOf(st.cat).filter(function (it) {
        return it.cp.some(function (c) { return c.mk === st.make && c.md === st.model && (!st.body || !c.b.length || c.b.indexOf(st.body) >= 0); });
      });
    }

    function setDots(n) { dots.forEach(function (li, i) { li.className = i < n ? 'is-done' : (i === n ? 'is-on' : ''); }); }
    function setTrail() {
      var bits = [];
      if (st.cat) bits.push(['start', catBy[st.cat] ? catBy[st.cat].n : '']);
      if (st.make) { var mk = makes(st.cat).filter(function (x) { return x.s === st.make; })[0]; bits.push(['make', mk ? mk.n : st.make]); }
      if (st.model) { var md = models(st.cat, st.make).filter(function (x) { return x.s === st.model; })[0]; bits.push(['model', md ? md.n : st.model]); }
      if (st.body) bits.push(['body', st.body]);
      trail.innerHTML = bits.map(function (b) { return '<button type="button" class="q-chip" data-q-go="' + b[0] + '">' + esc(b[1]) + icon('close') + '<span class="sr"> — изменить</span></button>'; }).join('');
    }
    function render(html, dir) {
      var swap = function () {
        stage.innerHTML = html;
        var h = $('.q-h', stage); if (h) { h.setAttribute('tabindex', '-1'); if (root.__touched) h.focus({ preventScroll: true }); }
        setTrail();
        root.classList.toggle('is-started', history.length > 1);
        if (root.__touched) {
          var top = root.getBoundingClientRect().top, hh = (document.getElementById('header') || {}).offsetHeight || 0;
          if (top < hh || top > window.innerHeight * 0.6 || (window.innerWidth < 960 && Math.abs(top - hh - 12) > 24)) window.scrollTo({ top: window.scrollY + top - hh - 12, behavior: reduce ? 'auto' : 'smooth' });
        }
        if (!reduce) { stage.classList.remove('q-out', 'q-out-back'); stage.classList.add(dir < 0 ? 'q-in-back' : 'q-in'); setTimeout(function () { stage.classList.remove('q-in', 'q-in-back'); }, 360); }
      };
      if (reduce || !stage.children.length) { swap(); return; }
      stage.classList.add(dir < 0 ? 'q-out-back' : 'q-out');
      setTimeout(swap, 170);
    }
    function tile(attrs, art, title, sub) {
      return '<button type="button" class="q-tile" ' + attrs + '><span class="q-art">' + art + '</span><span class="q-t"><b>' + esc(title) + '</b>' + (sub ? '<small>' + esc(sub) + '</small>' : '') + '</span><span class="q-go">' + icon('arrow') + '</span></button>';
    }
    function back(to) { return '<button type="button" class="q-back" data-q-go="' + to + '">' + icon('arrow-left') + 'Назад</button>'; }

    var steps = {
      start: function () {
        setDots(0);
        var h = '<p class="q-step">Шаг 1</p><h3 class="q-h">Что нужно заменить?</h3><div class="q-grid">';
        data.cats.forEach(function (c) { h += tile('data-q-cat="' + esc(c.s) + '"', ART[c.k] || ART.box, c.n, c.c + ' ' + plural(c.c, 'товар', 'товара', 'товаров')); });
        h += tile('data-q-next="symptom"', ART.unsure, 'Не уверен', 'Подскажем по признакам');
        h += tile('data-q-next="code"', ART.code, 'Знаю номер детали', 'Артикул или номер со старой');
        return h + '</div><p class="q-note">' + icon('lock') + 'Без регистрации. Ничего никуда не отправляем, пока вы сами не попросите.</p>';
      },
      symptom: function () {
        setDots(0);
        var h = back('start') + '<p class="q-step">Шаг 1 · по признакам</p><h3 class="q-h">Что происходит с машиной?</h3><div class="q-list">';
        data.symptoms.forEach(function (s, i) { var k = catBy[s.cat] ? catBy[s.cat].k : 'box'; h += '<button type="button" class="q-row" data-q-sym="' + i + '"><span class="q-art q-art-sm">' + (ART[k] || ART.box) + '</span><span>' + esc(s.text) + '</span>' + icon('arrow') + '</button>'; });
        return h + '</div><p class="q-note">' + icon('info') + 'Это подсказка, а не диагноз. Сомневаетесь — покажите машину мастеру или спросите нас.</p>';
      },
      code: function () {
        setDots(0);
        return back('start') + '<p class="q-step">Поиск по номеру</p><h3 class="q-h">Введите артикул или номер со старой детали</h3>'
          + '<label class="q-code"><span class="sr">Номер детали</span>' + icon('hash') + '<input type="text" inputmode="text" autocomplete="off" placeholder="Например, 1425193 или LAP-10262" data-q-code></label>'
          + '<p class="q-note">' + icon('info') + 'Номер обычно выдавлен на корпусе детали или напечатан на наклейке. Пробелы и дефисы можно не ставить.</p><div class="q-results" data-q-coderes></div>';
      },
      make: function () {
        setDots(1);
        var list = makes(st.cat), h = back(st.via || 'start') + '<p class="q-step">Шаг 2</p><h3 class="q-h">Какая марка?</h3><div class="q-grid q-grid-makes">';
        list.forEach(function (m) { var n = Object.keys(m.models).length; h += '<button type="button" class="q-tile q-tile-make" data-q-make="' + esc(m.s) + '"><span class="q-mono" aria-hidden="true">' + esc(m.n.charAt(0)) + '</span><span class="q-t"><b>' + esc(m.n) + '</b><small>' + n + ' ' + plural(n, 'модель', 'модели', 'моделей') + '</small></span></button>'; });
        return h + '</div><p class="q-note">Нет вашей марки? <button type="button" class="linklike" data-lead="pick" data-title="Помощь с подбором">Напишите нам</button> — поищем.</p>';
      },
      model: function () {
        setDots(2);
        var h = back('make') + '<p class="q-step">Шаг 3</p><h3 class="q-h">Какая модель?</h3><div class="q-grid q-grid-models">';
        models(st.cat, st.make).forEach(function (m) { h += '<button type="button" class="q-tile q-tile-model" data-q-model="' + esc(m.s) + '"><span class="q-t"><b>' + esc(m.n) + '</b><small>' + esc(years(m) || 'годы уточняйте') + '</small></span><span class="q-go">' + icon('arrow') + '</span></button>'; });
        return h + '</div>';
      },
      body: function () {
        setDots(3);
        var m = models(st.cat, st.make).filter(function (x) { return x.s === st.model; })[0], bodies = Object.keys(m.b);
        var order = data.bodies; bodies.sort(function (a, b) { return order.indexOf(a) - order.indexOf(b); });
        var h = back('model') + '<p class="q-step">Шаг 4</p><h3 class="q-h">Какой кузов?</h3><p class="q-sub">Для разных кузовов упоры отличаются длиной и силой.</p><div class="q-grid q-grid-bodies">';
        bodies.forEach(function (b) { h += '<button type="button" class="q-tile q-tile-body" data-q-body="' + esc(b) + '">' + bodyArt(b) + '<b>' + esc(b) + '</b></button>'; });
        h += '<button type="button" class="q-tile q-tile-body q-tile-skip" data-q-body=""><span class="q-art">' + ART.unsure + '</span><b>Не знаю</b></button>';
        return h + '</div>';
      },
      result: function () {
        setDots(4);
        var list = match(), mk = makes(st.cat).filter(function (x) { return x.s === st.make; })[0], md = models(st.cat, st.make).filter(function (x) { return x.s === st.model; })[0];
        var car = [mk && mk.n, md && md.n, st.body && st.body.toLowerCase()].filter(Boolean).join(' ');
        saveCar(car);
        goal('quiz_done');
        if (!list.length) {
          return back('model') + '<div class="q-empty"><span class="q-art">' + ART.car + '</span><h3 class="q-h">Для ' + esc(car) + ' в каталоге пока ничего нет</h3><p>Оставьте заявку — проверим у поставщиков и перезвоним.</p><div class="q-actions"><button type="button" class="btn btn-primary" data-lead="pick" data-title="Помощь с подбором">' + icon('chat') + 'Помогите подобрать</button><button type="button" class="btn btn-soft" data-q-go="start">Начать заново</button></div></div>';
        }
        var bodiesDiffer = !st.body && list.length > 1 && list.some(function (it) { return it.cp.some(function (c) { return c.b.length; }); });
        var q = 'make=' + encodeURIComponent(st.make) + '&model=' + encodeURIComponent(st.model) + (st.body ? '&body=' + encodeURIComponent(st.body) : '');
        var h = back(hasBodies() ? 'body' : 'model') + '<div class="q-done"><span class="q-check" aria-hidden="true"><svg viewBox="0 0 52 52"><circle cx="26" cy="26" r="24"/><path d="m15 27 7 7 15-16"/></svg></span><div><p class="q-step">Готово</p><h3 class="q-h">' + (list.length === 1 ? 'Подходит 1 деталь' : 'Подходят ' + list.length + ' ' + plural(list.length, 'деталь', 'детали', 'деталей')) + '</h3><p class="q-sub">для ' + esc(car) + '. Машину запомнили — в каталоге отметим совместимость.</p></div></div>';
        if (bodiesDiffer) h += '<p class="q-note q-note-warn">' + icon('alert') + 'Упоры для разных кузовов отличаются. Выберите свой в карточке или сверьте длину.</p>';
        h += '<div class="q-results">' + list.map(miniCard).join('') + '</div>';
        h += '<div class="q-actions"><a class="btn btn-soft" href="/catalog/' + esc(st.cat) + '?' + q + '">Открыть в каталоге' + icon('arrow') + '</a><button type="button" class="linklike" data-q-go="start">Подобрать другую деталь</button></div>';
        return h;
      }
    };
    function hasBodies() { var m = models(st.cat, st.make).filter(function (x) { return x.s === st.model; })[0]; return m && Object.keys(m.b).length > 0; }
    function miniCard(it) {
      var buy = (it.wb ? '<a class="btn btn-wb" href="' + it.wb + '" target="_blank" rel="nofollow noopener" data-goal="mp_wb" data-mp="wb">' + icon('bag') + 'Wildberries</a>' : '') + (it.oz ? '<a class="btn btn-ozon" href="' + it.oz + '" target="_blank" rel="nofollow noopener" data-goal="mp_ozon" data-mp="ozon">' + icon('bag') + 'Ozon</a>' : '');
      if (!buy) buy = '<button type="button" class="btn btn-primary" data-lead="availability" data-product-id="' + it.id + '" data-title="Уточнить наличие">' + icon('chat') + 'Уточнить наличие</button>';
      return '<article class="q-card"><a class="q-card-img" href="' + it.u + '">' + (it.img ? '<img src="' + it.img + '" alt="" width="96" height="96" loading="lazy">' : ART.box) + '</a><div class="q-card-b"><small>' + esc(it.sku) + (it.kit ? ' · ' + esc(it.kit) : '') + '</small><a class="q-card-n" href="' + it.u + '">' + esc(it.n) + '</a>' + (it.sp.length ? '<span class="q-card-sp">' + esc(it.sp.join(' · ')) + '</span>' : '') + '<div class="q-card-btns">' + buy + '<button type="button" class="btn btn-soft btn-add" data-cart="' + it.id + '" aria-pressed="false">' + icon('plus') + '<span>В корзину</span></button></div></div></article>';
    }
    function saveCar(label) {
      var cur = store.get('avtom-car') || {};
      var c = { make: st.make, model: st.model, year: '', body: st.body || '', engine: '', label: label };
      if (cur.make === c.make && cur.model === c.model) { c.year = cur.year || ''; c.engine = cur.engine || ''; }
      store.set('avtom-car', c);
      if (window.avtomGarage) window.avtomGarage();
    }
    function go(step, dir) {
      if (dir !== -1) history.push(step);
      render(steps[step](), dir || 1);
      if (step === 'result' || step === 'code') setTimeout(refresh, 200);
      if (step === 'code') setTimeout(function () { var i = $('[data-q-code]', stage); if (i && root.__touched) i.focus(); }, 220);
    }
    function reset(to) {
      if (to === 'start') { st = { cat: null, make: null, model: null, body: null, via: null }; }
      if (to === 'make') { st.make = null; st.model = null; st.body = null; }
      if (to === 'model') { st.model = null; st.body = null; }
      if (to === 'body') { st.body = null; }
      if (to === 'symptom' || to === 'code') { st.cat = null; st.make = null; st.model = null; st.body = null; }
    }

    root.addEventListener('click', function (e) {
      root.__touched = true;
      var t;
      if ((t = e.target.closest('[data-q-cat]'))) { st.cat = t.getAttribute('data-q-cat'); st.via = 'start'; go('make'); }
      else if ((t = e.target.closest('[data-q-sym]'))) { st.cat = data.symptoms[+t.getAttribute('data-q-sym')].cat; st.via = 'symptom'; go('make'); }
      else if ((t = e.target.closest('[data-q-next]'))) { go(t.getAttribute('data-q-next')); }
      else if ((t = e.target.closest('[data-q-make]'))) { st.make = t.getAttribute('data-q-make'); go('model'); }
      else if ((t = e.target.closest('[data-q-model]'))) { st.model = t.getAttribute('data-q-model'); st.body = null; go(hasBodies() ? 'body' : 'result'); }
      else if ((t = e.target.closest('[data-q-body]'))) { st.body = t.getAttribute('data-q-body') || null; go('result'); }
      else if ((t = e.target.closest('[data-q-go]'))) {
        var to = t.getAttribute('data-q-go');
        if (to === 'start' && t.classList.contains('q-chip')) to = 'start';
        reset(to); go(to, -1);
      }
    });
    root.addEventListener('input', function (e) {
      if (!e.target.matches('[data-q-code]')) return;
      var q = norm(e.target.value), box = $('[data-q-coderes]', stage);
      if (q.length < 4) { box.innerHTML = ''; return; }
      var found = data.items.filter(function (it) { return it.codes.some(function (c) { return c.indexOf(q) >= 0; }); });
      box.innerHTML = found.length ? '<p class="q-found">' + icon('check-circle') + (found.length === 1 ? 'Нашли деталь' : 'Нашли ' + found.length + ' ' + plural(found.length, 'деталь', 'детали', 'деталей')) + '</p>' + found.map(miniCard).join('')
        : '<div class="q-empty q-empty-sm"><p>По номеру <b>' + esc(e.target.value) + '</b> ничего нет. Проверьте цифры или <button type="button" class="linklike" data-lead="pick" data-title="Поиск по номеру">отправьте номер менеджеру</button>.</p></div>';
      if (found.length) goal('quiz_code_found');
      refresh();
    });
    go('start');
  }

  /* =========================== упор по старому =========================== */
  function Measure(root) {
    var list; try { list = JSON.parse($('[data-ms-data]', root).textContent); } catch (e) { return; }
    var min = +root.getAttribute('data-min'), max = +root.getAttribute('data-max');
    var range = $('[data-ms-range]', root), lenIn = $('[data-ms-len]', root), forceIn = $('[data-ms-force]', root);
    var body = $('[data-ms-body]', root), hl = $('[data-ms-bodyhl]', root), rod = $('[data-ms-rod]', root), seal = $('[data-ms-seal]', root), handle = $('[data-ms-handle]', root);
    var dimR = $('[data-ms-dimr]', root), dimLine = $('[data-ms-dimline]', root), out = $('[data-ms-out]', root), res = $('[data-ms-results]', root), svg = $('.ms-svg', root);
    var X0 = 40, k = 700 / max, cur = +range.value, raf, lastKey = '';

    function draw(L) {
      var W = L * k, bodyW = W * 0.54, start = X0 + 14, rodStart = start + bodyW - 8, end = X0 + W;
      body.setAttribute('x', start); body.setAttribute('width', bodyW);
      hl.setAttribute('x', start + 10); hl.setAttribute('width', Math.max(0, bodyW - 20));
      rod.setAttribute('x', rodStart); rod.setAttribute('width', Math.max(0, end - rodStart - 12));
      seal.setAttribute('transform', 'translate(' + (start + bodyW) + ' 0)');
      handle.setAttribute('transform', 'translate(' + end + ' 0)');
      dimR.setAttribute('x1', end); dimR.setAttribute('x2', end); dimLine.setAttribute('x2', end);
      handle.setAttribute('aria-valuenow', Math.round(L)); handle.setAttribute('aria-valuetext', Math.round(L) + ' миллиметров');
      out.innerHTML = '<b>' + Math.round(L) + '</b> мм';
      out.style.transform = 'translateX(calc(' + ((X0 + W / 2) / 800 * svg.clientWidth).toFixed(1) + 'px - 50%))';
    }
    function results(L) {
      var F = +forceIn.value || 0;
      var scored = list.map(function (p) { var dl = Math.abs(p.len - L), df = F && p.f ? Math.abs(p.f - F) : 0; return { p: p, dl: dl, s: dl + df / 8 }; }).sort(function (a, b) { return a.s - b.s; }).slice(0, 3);
      var key = scored.map(function (x) { return x.p.id + ':' + Math.round(x.dl); }).join(',') + F;
      if (key === lastKey) return; lastKey = key;
      res.innerHTML = scored.map(function (x, i) {
        var p = x.p, d = Math.round(p.len - L);
        var tag = Math.abs(d) <= 3 ? '<span class="fit fit-yes">' + icon('check-circle') + 'Совпадает по длине</span>' : '<span class="fit ' + (Math.abs(d) <= 10 ? 'fit-check' : 'fit-far') + '">' + (d > 0 ? 'длиннее' : 'короче') + ' на ' + Math.abs(d) + ' мм</span>';
        var buy = (p.wb ? '<a class="btn btn-wb" href="' + p.wb + '" target="_blank" rel="nofollow noopener" data-goal="mp_wb" data-mp="wb">WB</a>' : '') + (p.oz ? '<a class="btn btn-ozon" href="' + p.oz + '" target="_blank" rel="nofollow noopener" data-goal="mp_ozon" data-mp="ozon">Ozon</a>' : '');
        return '<article class="ms-card' + (i === 0 ? ' is-best' : '') + '"><a class="ms-card-img" href="' + p.u + '">' + (p.img ? '<img src="' + p.img + '" alt="" width="80" height="80" loading="lazy">' : '') + '</a><div class="ms-card-b">' + tag + '<a class="ms-card-n" href="' + p.u + '">' + esc(p.n) + '</a><small>' + esc(p.sku) + ' · ' + Math.round(p.len) + ' мм' + (p.f ? ' · ' + Math.round(p.f) + ' Н' : '') + '</small></div><div class="ms-card-btns">' + buy + '<a class="btn btn-soft" href="' + p.u + '">Подробнее</a></div></article>';
      }).join('');
    }
    function set(L, from) {
      L = Math.max(min, Math.min(max, Math.round(L))); cur = L;
      if (from !== 'range') range.value = L;
      if (from !== 'input') lenIn.value = L;
      range.style.setProperty('--p', ((L - min) / (max - min) * 100).toFixed(1) + '%');
      cancelAnimationFrame(raf); raf = requestAnimationFrame(function () { draw(L); results(L); });
    }
    range.addEventListener('input', function () { set(+range.value, 'range'); root.classList.add('is-used'); });
    lenIn.addEventListener('input', function () { if (lenIn.value.length >= 3) set(+lenIn.value, 'input'); });
    lenIn.addEventListener('change', function () { set(+lenIn.value || cur); });
    forceIn.addEventListener('input', function () { lastKey = ''; results(cur); });
    // перетаскивание крепления
    var drag = false;
    function toMm(ev) { var pt = svg.createSVGPoint(); pt.x = ev.clientX; pt.y = ev.clientY; var p = pt.matrixTransform(svg.getScreenCTM().inverse()); return (p.x - X0) / k; }
    handle.addEventListener('pointerdown', function (e) { drag = true; handle.setPointerCapture(e.pointerId); root.classList.add('is-dragging', 'is-used'); e.preventDefault(); });
    handle.addEventListener('pointermove', function (e) { if (drag) set(toMm(e)); });
    handle.addEventListener('pointerup', function () { drag = false; root.classList.remove('is-dragging'); goal('measure_used'); });
    handle.addEventListener('keydown', function (e) {
      var st = e.shiftKey ? 10 : 1;
      if (e.key === 'ArrowRight' || e.key === 'ArrowUp') { set(cur + st); e.preventDefault(); }
      if (e.key === 'ArrowLeft' || e.key === 'ArrowDown') { set(cur - st); e.preventDefault(); }
    });
    // при первом появлении упор «выдвигается»
    set(cur);
    if (!reduce && 'IntersectionObserver' in window) {
      var target = cur; draw(min);
      var io = new IntersectionObserver(function (es) {
        if (!es[0].isIntersecting) return; io.disconnect();
        var t0 = null; function step(t) { if (!t0) t0 = t; var q = Math.min(1, (t - t0) / 1100), e = 1 - Math.pow(1 - q, 4); draw(min + (target - min) * e); if (q < 1 && !root.classList.contains('is-used')) requestAnimationFrame(step); else draw(cur); }
        requestAnimationFrame(step);
      }, { threshold: .4 });
      io.observe(root);
    }
  }

  function init(scope) {
    $$('[data-quiz]', scope).forEach(function (r) { if (!r.__q) { r.__q = 1; Quiz(r); } });
    $$('[data-measure]', scope).forEach(function (r) { if (!r.__m) { r.__m = 1; Measure(r); } });
  }
  init(d);
  d.addEventListener('avtom:swap', function (e) { init(e.detail || d); });
})();
