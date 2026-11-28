/* AVTO-M · витрина. Без библиотек. Всё работает и без скрипта — здесь улучшения. */
(function () {
  'use strict';
  window.__avtom = true;
  var d = document, html = d.documentElement, body = d.body;
  var B = html.getAttribute('data-base') || ''; // подпапка сайта ('' — корень домена)
  var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  var $ = function (s, r) { return (r || d).querySelector(s); };
  var $$ = function (s, r) { return Array.prototype.slice.call((r || d).querySelectorAll(s)); };
  var store = {
    get: function (k, def) { try { var v = localStorage.getItem(k); return v ? JSON.parse(v) : def; } catch (e) { return def; } },
    set: function (k, v) { try { localStorage.setItem(k, JSON.stringify(v)); } catch (e) {} }
  };
  var esc = function (s) { return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]; }); };
  var icon = function (n) { return '<svg class="i" aria-hidden="true"><use href="#i-' + n + '"/></svg>'; };
  var plural = function (n, a, b, c) { n = Math.abs(n) % 100; var n1 = n % 10; if (n > 10 && n < 20) return c; if (n1 > 1 && n1 < 5) return b; if (n1 === 1) return a; return c; };
  html.classList.add('ready');

  /* ---------- Метрика (только после согласия) ---------- */
  var mid = body.getAttribute('data-metrika');
  var consent = (d.cookie.match(/(?:^|; )avtom_consent=([^;]+)/) || [])[1];
  function metrika() {
    if (!mid || window.ym) return;
    (function (m, e, t, r, i, k, a) { m[i] = m[i] || function () { (m[i].a = m[i].a || []).push(arguments); }; m[i].l = 1 * new Date(); k = e.createElement(t); a = e.getElementsByTagName(t)[0]; k.async = 1; k.src = r; a.parentNode.insertBefore(k, a); })(window, d, 'script', 'https://mc.yandex.ru/metrika/tag.js', 'ym');
    window.ym(+mid, 'init', { clickmap: true, trackLinks: true, accurateTrackBounce: true, webvisor: false });
  }
  function goal(name, params) { try { if (window.ym && mid) window.ym(+mid, 'reachGoal', name, params || {}); } catch (e) {} }
  window.avtomGoal = goal;
  function setConsent(v) { d.cookie = 'avtom_consent=' + v + '; path=/; max-age=31536000; SameSite=Lax' + (location.protocol === 'https:' ? '; Secure' : ''); consent = v; }
  // плашка cookie встроена в нижнюю панель: появляется снизу, после выбора уезжает обратно под панель
  var cookieBox = $('#cookie'), cookieT;
  function cookieShow() { if (!cookieBox) return; clearTimeout(cookieT); cookieBox.classList.remove('is-out'); cookieBox.hidden = false; cookieBox.classList.add('is-in'); }
  function cookieHide() { if (!cookieBox || cookieBox.hidden) return; cookieBox.classList.remove('is-in'); cookieBox.classList.add('is-out'); cookieT = setTimeout(function () { cookieBox.hidden = true; }, reduce ? 0 : 520); }
  if (!consent && cookieBox) setTimeout(cookieShow, 1200);
  if (consent === 'all') metrika();
  d.addEventListener('click', function (e) {
    var b = e.target.closest('[data-cookie]');
    if (b) { setConsent(b.getAttribute('data-cookie')); cookieHide(); if (consent === 'all') metrika(); }
    if (e.target.closest('[data-cookie-settings]')) cookieShow();
    var g = e.target.closest('[data-goal]');
    if (g) goal(g.getAttribute('data-goal'), g.getAttribute('data-mp') ? { mp: g.getAttribute('data-mp') } : null);
  });

  /* ---------- метки рекламы для заявок ---------- */
  try {
    var sp = new URLSearchParams(location.search), utm = {};
    ['utm_source', 'utm_medium', 'utm_campaign', 'utm_content', 'utm_term', 'yclid'].forEach(function (k) { if (sp.get(k)) utm[k] = sp.get(k); });
    if (Object.keys(utm).length) sessionStorage.setItem('avtom-utm', JSON.stringify(utm));
  } catch (e) {}

  /* ---------- тост ---------- */
  var toastEl = $('#toast'), toastT, toastT2;
  function toast(msg, link) {
    if (!toastEl) return;
    clearTimeout(toastT); clearTimeout(toastT2);
    toastEl.innerHTML = icon('check-circle') + '<span>' + esc(msg) + '</span>' + (link ? '<a href="' + link[1] + '">' + esc(link[0]) + '</a>' : '');
    toastEl.classList.remove('is-out'); toastEl.hidden = false; toastEl.style.animation = 'none'; void toastEl.offsetWidth; toastEl.style.animation = '';
    toastT = setTimeout(function () { toastEl.classList.add('is-out'); toastT2 = setTimeout(function () { toastEl.hidden = true; }, 320); }, 3000);
  }

  /* ---------- шапка ----------
     Прокрутили вниз — шапка уезжает (на компьютере остаётся строка «Каталог продукции» с поиском, на телефоне
     прячется целиком). Чтобы вернуть её, достаточно немного прокрутить вверх — на 48 px, а не на любой случайный
     сдвиг пальца. Пока открыто меню, подсказки поиска или фокус в поиске, шапка не прячется. */
  var head = $('[data-head]'), headTop = head && $('.hd', head);
  var lastY = window.scrollY, upRun = 0, headHidden = false, headTick = false;
  function headShift() { return headTop && headTop.offsetHeight ? headTop.offsetHeight : 0; }
  function setHead(hide) {
    if (hide === headHidden) return;
    headHidden = hide;
    head.style.setProperty('--head-shift', headShift() + 'px');
    head.classList.toggle('is-hidden', hide);
  }
  function onScroll() {
    headTick = false;
    var y = Math.max(0, window.scrollY), dy = y - lastY; lastY = y;
    head.classList.toggle('is-scrolled', y > 4);
    var busy = html.classList.contains('menu-on') || (d.activeElement && d.activeElement.closest && d.activeElement.closest('[data-search]'));
    if (busy || y < head.offsetHeight + 24) { upRun = 0; setHead(false); return; }
    if (dy > 0) { upRun = 0; if (y > head.offsetHeight + 80) setHead(true); }
    else if (dy < 0) { upRun -= dy; if (upRun > 48) setHead(false); }
  }
  if (head) {
    window.addEventListener('scroll', function () { if (!headTick) { headTick = true; requestAnimationFrame(onScroll); } }, { passive: true });
    window.addEventListener('resize', function () { if (headHidden) head.style.setProperty('--head-shift', headShift() + 'px'); }, { passive: true });
    head.addEventListener('focusin', function () { setHead(false); });
    onScroll();
  }

  /* ---------- боковое меню: на компьютере выезжает слева («Каталог продукции»), на телефоне справа (кнопка меню) ---------- */
  var menu = $('#menu'), menuPanel = menu && $('.drawer-panel', menu), menuFrom = null, menuT;
  function menuOpen() { return menu && !menu.hidden && menu.classList.contains('is-open'); }
  function setMenu(open, from) {
    if (!menu || open === menuOpen()) return;
    clearTimeout(menuT);
    if (open) {
      menuFrom = from || null;
      menu.hidden = false;
      void menu.offsetWidth;
      menu.classList.add('is-open');
      html.classList.add('menu-on');
      setTimeout(function () { var c = $('.drawer-close', menu); if (c) c.focus({ preventScroll: true }); }, 60);
    } else {
      menu.classList.remove('is-open');
      html.classList.remove('menu-on');
      menuT = setTimeout(function () { menu.hidden = true; }, reduce ? 0 : 420);
      if (menuFrom) menuFrom.focus({ preventScroll: true });
    }
    $$('[data-menu-open]').forEach(function (b) { b.setAttribute('aria-expanded', open ? 'true' : 'false'); });
  }
  $$('[data-menu-open]').forEach(function (b) { b.addEventListener('click', function () { setMenu(!menuOpen(), b); }); });
  if (menu) {
    menu.addEventListener('click', function (e) { if (e.target.closest('[data-menu-close]') || e.target.closest('a')) setMenu(false); });
    menu.addEventListener('keydown', function (e) { // фокус остаётся внутри открытого меню
      if (e.key !== 'Tab') return;
      var f = $$('a[href], button', menuPanel), i = f.indexOf(d.activeElement);
      if (e.shiftKey && i <= 0) { e.preventDefault(); f[f.length - 1].focus(); }
      else if (!e.shiftKey && i === f.length - 1) { e.preventDefault(); f[0].focus(); }
    });
  }
  d.addEventListener('keydown', function (e) { if (e.key === 'Escape') { if (menuOpen()) setMenu(false); closeFilters(); closeLightbox(); } });

  /* ---------- поиск с подсказками ---------- */
  $$('[data-search]').forEach(function (form) {
    var input = $('input', form), box = $('.suggest', form), timer, ctrl, idx = -1;
    function hide() { box.hidden = true; idx = -1; input.setAttribute('aria-expanded', 'false'); }
    function mark(t, q) { var w = q.trim().split(/\s+/)[0]; if (!w || w.length < 2) return esc(t); var i = t.toLowerCase().indexOf(w.toLowerCase()); return i < 0 ? esc(t) : esc(t.slice(0, i)) + '<mark>' + esc(t.slice(i, i + w.length)) + '</mark>' + esc(t.slice(i + w.length)); }
    function render(r, q) {
      var h = r.fixed ? '<p class="sg-fix">Искали «' + esc(r.fixed) + '»</p>' : (r.fuzzy && r.items.length ? '<p class="sg-fix">Похожие результаты</p>' : '');
      if (r.models && r.models.length) { h += '<p class="sg-h">Автомобили</p>'; r.models.forEach(function (m) { h += '<a class="sg-item" role="option" href="' + m.url + '"><span class="sg-ph" style="display:grid;place-items:center;color:#0a5cff">' + icon('car') + '</span><span><b>' + mark(m.name, q) + '</b><small>Все детали для модели</small></span></a>'; }); }
      if (r.items.length) { h += '<p class="sg-h">Товары</p>'; r.items.forEach(function (p) { h += '<a class="sg-item" role="option" href="' + p.url + '">' + (p.img ? '<img src="' + p.img + '" alt="" width="48" height="48">' : '<span class="sg-ph"></span>') + '<span><b>' + mark(p.name, q) + '</b><small>' + esc(p.sku) + ' · ' + esc(p.compat) + '</small></span></a>'; }); }
      if (!r.items.length && !(r.models || []).length) h = '<p class="sg-empty">Ничего не нашли. Попробуйте артикул, номер детали или модель автомобиля.</p>';
      else if (r.total > r.items.length) h += '<a class="sg-all" href="' + B + '/search?q=' + encodeURIComponent(q) + '">Показать все — ' + r.total + icon('arrow') + '</a>';
      box.innerHTML = h; box.hidden = false; input.setAttribute('aria-expanded', 'true');
    }
    input.addEventListener('input', function () {
      clearTimeout(timer); var q = input.value.trim();
      if (q.length < 2) { hide(); return; }
      timer = setTimeout(function () {
        if (ctrl) ctrl.abort(); ctrl = window.AbortController ? new AbortController() : null;
        fetch(B + '/api/search?q=' + encodeURIComponent(q), ctrl ? { signal: ctrl.signal } : {}).then(function (r) { return r.json(); }).then(function (r) { render(r, q); }).catch(function () {});
      }, 180);
    });
    input.addEventListener('keydown', function (e) {
      var items = $$('.sg-item, .sg-all', box); if (box.hidden || !items.length) return;
      if (e.key === 'ArrowDown' || e.key === 'ArrowUp') { e.preventDefault(); idx = (idx + (e.key === 'ArrowDown' ? 1 : -1) + items.length) % items.length; items.forEach(function (x, i) { x.classList.toggle('is-on', i === idx); }); items[idx].scrollIntoView({ block: 'nearest' }); }
      if (e.key === 'Enter' && idx >= 0) { e.preventDefault(); location.href = items[idx].href; }
      if (e.key === 'Escape') hide();
    });
    d.addEventListener('click', function (e) { if (!form.contains(e.target)) hide(); });
    input.addEventListener('focus', function () { if (box.innerHTML && input.value.trim().length > 1) box.hidden = false; });
  });

  /* ---------- подбор по автомобилю ---------- */
  var tree = []; try { tree = JSON.parse(($('#car-tree') || {}).textContent || '[]'); } catch (e) {}
  var findMake = function (s) { for (var i = 0; i < tree.length; i++) if (tree[i].s === s) return tree[i]; return null; };
  var findModel = function (mk, s) { if (!mk) return null; for (var i = 0; i < mk.m.length; i++) if (mk.m[i].s === s) return mk.m[i]; return null; };
  function fill(sel, opts, first, keep) {
    var cur = keep ? sel.value : '';
    sel.innerHTML = '<option value="">' + first + '</option>' + opts.map(function (o) { return '<option value="' + esc(o[0]) + '"' + (o[0] === cur ? ' selected' : '') + '>' + esc(o[1]) + '</option>'; }).join('');
    sel.disabled = !opts.length;
  }
  function pickerInit(p) {
    var f = {}; ['make', 'model', 'year', 'body', 'engine'].forEach(function (k) { f[k] = $('[data-pk="' + k + '"]', p); });
    if (!f.make) return;
    function mark() { ['make', 'model', 'year', 'body', 'engine'].forEach(function (k) { if (f[k]) f[k].classList.toggle('has-value', !!f[k].value); }); }
    function onMake() { var mk = findMake(f.make.value); fill(f.model, mk ? mk.m.map(function (m) { return [m.s, m.n]; }) : [], mk ? 'Любая модель' : 'Сначала марка'); onModel(); }
    function onModel() {
      var md = findModel(findMake(f.make.value), f.model.value), years = [];
      if (md && md.yf) for (var y = md.yt || new Date().getFullYear(); y >= md.yf; y--) years.push([String(y), String(y)]);
      fill(f.year, years, 'Любой год');
      fill(f.body, md ? md.b.map(function (b) { return [b, b]; }) : [], 'Любой кузов');
      fill(f.engine, md ? md.e.map(function (e) { return [e, e + ' л']; }) : [], 'Любой');
      mark();
    }
    f.make.addEventListener('change', onMake);
    f.model.addEventListener('change', onModel);
    ['year', 'body', 'engine'].forEach(function (k) { f[k].addEventListener('change', mark); });
    mark();
    // подставляем сохранённый автомобиль, если поле пустое и машины нет в адресе
    var g = store.get('avtom-car', null);
    if (g && !f.make.value && !p.closest('[data-filters]') && findMake(g.make)) {
      f.make.value = g.make; onMake();
      if (g.model && findModel(findMake(g.make), g.model)) { f.model.value = g.model; onModel(); }
      ['year', 'body', 'engine'].forEach(function (k) { if (g[k]) { f[k].value = g[k]; } }); mark();
    }
    var form = p.tagName === 'FORM' ? p : null;
    if (form) form.addEventListener('submit', function () { saveCar(f); });
    p.__fields = f;
  }
  function carFromFields(f) { var o = {}; ['make', 'model', 'year', 'body', 'engine'].forEach(function (k) { o[k] = f[k] && !f[k].disabled ? f[k].value : ''; }); return o; }
  function saveCar(f) {
    var c = carFromFields(f); if (!c.make) return;
    var mk = findMake(c.make), md = findModel(mk, c.model);
    c.label = [mk ? mk.n : '', md ? md.n : '', c.year, c.body ? c.body.toLowerCase() : ''].filter(Boolean).join(' ');
    store.set('avtom-car', c); goal('pick_used');
  }
  $$('[data-picker]').forEach(pickerInit);
  var pf = $('[data-param-form]');
  if (pf) { var cs = $('[data-cat-select]', pf); cs.addEventListener('change', function () { pf.action = cs.value; }); pf.addEventListener('submit', function () { pf.action = cs.value; $$('input', pf).forEach(function (i) { if (!i.value) i.disabled = true; }); var b = $('select[name="brand[]"]', pf); if (b && !b.value) b.disabled = true; goal('pick_used'); }); }
  // «мой автомобиль» в шапке
  function carQuery(c) { return ['make', 'model', 'year', 'body', 'engine'].filter(function (k) { return c[k]; }).map(function (k) { return k + '=' + encodeURIComponent(c[k]); }).join('&'); }
  function renderGarage() {
    var c = store.get('avtom-car', null);
    $$('[data-garage], [data-garage-inline]').forEach(function (g) {
      if (!c || !c.label) { g.hidden = true; return; }
      g.hidden = false;
      g.className = 'garage';
      g.innerHTML = '<a href="' + B + '/catalog?' + carQuery(c) + '" title="Показать детали для моей машины">' + icon('car') + '<span>' + esc(c.label) + '</span></a><button type="button" aria-label="Забыть автомобиль" data-garage-clear>' + icon('close') + '</button>';
    });
  }
  renderGarage();
  window.avtomGarage = renderGarage;
  d.addEventListener('click', function (e) { if (e.target.closest('[data-garage-clear]')) { store.set('avtom-car', null); renderGarage(); toast('Автомобиль забыт'); } });

  /* ---------- список запроса и сравнение ---------- */
  var cart = store.get('avtom-cart', []), cmp = store.get('avtom-cmp', []);
  function inCart(id) { return cart.some(function (x) { return x.id === id; }); }
  function counts(bump) {
    $$('[data-count="cart"]').forEach(function (b) { var n = cart.length; b.textContent = n; b.hidden = !n; if (bump === 'cart') { b.classList.remove('bump'); void b.offsetWidth; b.classList.add('bump'); } });
    $$('[data-count-text="cart"]').forEach(function (b) { var n = cart.length; b.textContent = n + ' ' + plural(n, 'товар', 'товара', 'товаров'); });
    $$('[data-count="compare"]').forEach(function (b) { var n = cmp.length; b.textContent = n; b.hidden = !n; if (bump === 'cmp') { b.classList.remove('bump'); void b.offsetWidth; b.classList.add('bump'); } });
    $$('[data-cart]').forEach(function (b) { var on = inCart(+b.getAttribute('data-cart')); b.setAttribute('aria-pressed', on ? 'true' : 'false'); var s = $('span', b); if (s) s.textContent = on ? 'В корзине' : 'В корзину'; var u = $('use', b); if (u) u.setAttribute('href', on ? '#i-check' : '#i-plus'); });
    $$('[data-compare]').forEach(function (b) { var on = cmp.indexOf(+b.getAttribute('data-compare')) >= 0; b.setAttribute('aria-pressed', on ? 'true' : 'false'); var s = $('span', b); if (s) s.textContent = on ? 'В сравнении' : 'Сравнить'; });
  }
  counts();
  window.avtomCounts = counts;
  window.avtomToast = toast;
  window.addEventListener('storage', function () { cart = store.get('avtom-cart', []); cmp = store.get('avtom-cmp', []); counts(); });
  d.addEventListener('click', function (e) {
    var b = e.target.closest('[data-cart]');
    if (b) {
      var id = +b.getAttribute('data-cart');
      if (inCart(id)) { cart = cart.filter(function (x) { return x.id !== id; }); toast('Убрали из корзины'); }
      else { cart.push({ id: id, qty: 1 }); toast('Добавили в корзину', ['Открыть', B + '/cart']); }
      store.set('avtom-cart', cart); counts('cart'); return;
    }
    var c = e.target.closest('[data-compare]');
    if (c) {
      var cid = +c.getAttribute('data-compare'), i = cmp.indexOf(cid);
      if (i >= 0) { cmp.splice(i, 1); toast('Убрали из сравнения'); }
      else { if (cmp.length >= 6) cmp.shift(); cmp.push(cid); toast('Добавили к сравнению', ['Сравнить', B + '/compare']); }
      store.set('avtom-cmp', cmp); counts('cmp');
    }
  });

  /* ---------- формы заявок ---------- */
  function maskPhone(inp) {
    inp.addEventListener('input', function () {
      var v = inp.value.replace(/\D/g, '');
      if (v[0] === '8') v = '7' + v.slice(1);
      if (v && v[0] !== '7') v = '7' + v;
      v = v.slice(0, 11);
      var r = '+7';
      if (v.length > 1) r += ' (' + v.slice(1, 4);
      if (v.length >= 4) r += ') ' + v.slice(4, 7);
      if (v.length >= 7) r += '-' + v.slice(7, 9);
      if (v.length >= 9) r += '-' + v.slice(9, 11);
      inp.value = v.length ? r : '';
    });
    inp.addEventListener('focus', function () { if (!inp.value) inp.value = '+7 ('; });
    inp.addEventListener('blur', function () { if (inp.value.replace(/\D/g, '').length <= 1) inp.value = ''; });
  }
  $$('[data-phone]').forEach(maskPhone);
  function setErr(form, name, msg) {
    var inp = form.elements[name]; if (!inp || !inp.closest) return false;
    var fld = inp.closest('.field'); if (!fld) return false;
    fld.classList.toggle('is-err', !!msg);
    var p = $('.field-err', fld); if (p) { p.textContent = msg || ''; p.hidden = !msg; }
    if (msg) inp.setAttribute('aria-invalid', 'true'); else inp.removeAttribute('aria-invalid');
    return true;
  }
  function itemsHtml(items) { return items.map(function (p) { return '<div><b>' + esc(p.sku) + '</b> · ' + esc(p.name) + (p.qty > 1 ? ' <small>× ' + p.qty + '</small>' : '') + '</div>'; }).join(''); }
  function bindLeadForm(form) {
    form.addEventListener('input', function (e) { if (e.target.name) setErr(form, e.target.name, ''); });
    form.addEventListener('change', function (e) { if (e.target.name === 'consent') setErr(form, 'consent', ''); });
    form.addEventListener('submit', function (e) {
      e.preventDefault();
      var btn = $('button[type="submit"]', form), errBox = $('[data-form-error]', form);
      errBox.hidden = true;
      var ok = true;
      if (!form.elements.name.value.trim()) { setErr(form, 'name', 'Как к вам обращаться?'); ok = false; }
      if (form.elements.phone.value.replace(/\D/g, '').length !== 11) { setErr(form, 'phone', 'Проверьте номер: нужно 10 цифр после +7.'); ok = false; }
      if (!form.elements.consent.checked) { setErr(form, 'consent', 'Нужно согласие на обработку персональных данных.'); ok = false; }
      if (form.elements.company && !form.elements.company.value.trim()) { setErr(form, 'company', 'Укажите компанию или сервис.'); ok = false; }
      if (!ok) { var f = $('.is-err input, .is-err textarea', form); if (f) f.focus(); return; }
      var data = {}; new FormData(form).forEach(function (v, k) { data[k] = v; });
      data.consent = form.elements.consent.checked ? 1 : 0;
      try { data.items = JSON.parse(data.items || '[]'); } catch (x) { data.items = []; }
      data.page = location.pathname + location.search;
      var car = store.get('avtom-car', null);
      if (car && car.make) { data.car_make = car.make; data.car_model = car.model; data.car_year = car.year; data.car_body = car.body; data.car_engine = car.engine; }
      try { var u = JSON.parse(sessionStorage.getItem('avtom-utm') || '{}'); for (var k in u) data[k] = u[k]; } catch (x) {}
      btn.classList.add('is-loading'); btn.disabled = true;
      fetch(B + '/api/lead', { method: 'POST', headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' }, body: JSON.stringify(data) })
        .then(function (r) { return r.json(); })
        .then(function (r) {
          btn.classList.remove('is-loading'); btn.disabled = false;
          if (!r.ok) {
            var rest = [];
            Object.keys(r.errors || {}).forEach(function (k) { if (!setErr(form, k, r.errors[k])) rest.push(r.errors[k]); });
            if (rest.length) { errBox.textContent = rest.join(' '); errBox.hidden = false; }
            var f = $('.is-err input, .is-err textarea', form); if (f) f.focus();
            return;
          }
          goal('lead_sent', { kind: data.kind }); goal('lead_' + data.kind);
          if (data.kind === 'cart') { cart = []; store.set('avtom-cart', cart); counts(); }
          onLeadDone(form, r.id);
        })
        .catch(function () { btn.classList.remove('is-loading'); btn.disabled = false; errBox.textContent = 'Нет связи с сервером. Проверьте интернет и попробуйте ещё раз — введённые данные сохранились.'; errBox.hidden = false; });
    });
  }
  $$('[data-lead-form]').forEach(bindLeadForm);
  function onLeadDone(form, id) {
    store.set('avtom-lead-sent', Date.now());
    var modal = form.closest('dialog');
    if (modal) { $('[data-modal-body]', modal).hidden = true; var dn = $('[data-modal-done]', modal); dn.hidden = false; $('[data-done-id]', dn).textContent = id; $('button', dn).focus(); }
    else {
      var box = d.createElement('div'); box.className = 'form-done';
      box.innerHTML = '<div class="done-mark" aria-hidden="true"><svg viewBox="0 0 52 52"><circle cx="26" cy="26" r="24"/><path d="m15 27 7 7 15-16"/></svg></div><h3>Заявка отправлена</h3><p class="muted">Менеджер перезвонит в рабочее время. Номер заявки: <b>' + id + '</b>.</p>';
      form.replaceWith(box);
      if (d.querySelector('[data-cart-page]')) renderCart();
    }
    form.reset();
  }
  // модальное окно
  var modal = $('#lead-modal');
  function openLead(kind, title, ids) {
    if (!modal) return;
    var form = $('form', modal);
    $('[data-modal-body]', modal).hidden = false; $('[data-modal-done]', modal).hidden = true;
    form.elements.kind.value = kind;
    $('[data-modal-title]', modal).textContent = title || 'Заявка';
    var leads = { availability: 'Оставьте телефон — менеджер проверит наличие и совместимость и перезвонит.', pick: 'Напишите марку, модель и год или VIN-номер — подскажем, что подойдёт.', question: 'Задайте вопрос — ответим в рабочее время.' };
    $('[data-modal-lead]', modal).textContent = leads[kind] || leads.question;
    var itemsBox = $('[data-items]', form);
    form.elements.items.value = JSON.stringify(ids.map(function (i) { return { id: i, qty: 1 }; }));
    itemsBox.hidden = !ids.length; itemsBox.innerHTML = '';
    if (ids.length) fetch(B + '/api/products?ids=' + ids.join(',')).then(function (r) { return r.json(); }).then(function (r) { itemsBox.innerHTML = itemsHtml(r.items); });
    var car = store.get('avtom-car', null), ct = $('[data-car-text]', form);
    if (ct && car && car.label && !ct.value) ct.value = car.label;
    if (modal.showModal) modal.showModal(); else modal.setAttribute('open', '');
    setTimeout(function () { form.elements.name.focus(); }, 60);
  }
  d.addEventListener('click', function (e) {
    var b = e.target.closest('[data-lead]');
    if (b) { e.preventDefault(); var pid = b.getAttribute('data-product-id'); openLead(b.getAttribute('data-lead'), b.getAttribute('data-title'), pid ? [+pid] : []); }
    if (e.target.closest('[data-modal-close]') && modal) modal.close();
  });
  if (modal) modal.addEventListener('click', function (e) { if (e.target === modal) modal.close(); });

  /* ---------- окно при уходе с сайта (настраивается в админке: Страницы → Окно при уходе) ---------- */
  var xp = $('[data-xpop]');
  if (xp && xp.showModal) {
    var xpForce = xp.hasAttribute('data-force'), xpDays = +xp.getAttribute('data-days') || 3, xpDelay = (+xp.getAttribute('data-delay') || 0) * 1000, xpMob = (+xp.getAttribute('data-mobile') || 0) * 1000, xpT0 = Date.now(), xpDone = false;
    var xpCan = function () {
      if (xpForce) return true;
      if (xpDone || $('dialog[open]') || (cookieBox && !cookieBox.hidden)) return false;
      if (Date.now() - xpT0 < xpDelay) return false;
      if (Date.now() - (+store.get('avtom-xpop', 0)) < xpDays * 864e5) return false;
      return !store.get('avtom-lead-sent', 0) || Date.now() - store.get('avtom-lead-sent', 0) > 30 * 864e5;
    };
    var xpShow = function () { if (!xpCan()) return; xpDone = true; xpForce = false; store.set('avtom-xpop', Date.now()); xp.showModal(); goal('xpop_show'); };
    if (xpForce) setTimeout(xpShow, 500);
    if (matchMedia('(hover: hover) and (pointer: fine)').matches) {
      d.addEventListener('mouseout', function (e) { if (!e.relatedTarget && e.clientY <= 4) xpShow(); });
    } else if (xpMob) setTimeout(xpShow, xpMob);
    xp.addEventListener('click', function (e) {
      if (e.target === xp || e.target.closest('[data-xpop-close]')) xp.close();
      if (e.target.closest('[data-lead]')) xp.close();
      var cp = e.target.closest('[data-xpop-copy]');
      if (cp) { var code = $('[data-xpop-code]', xp).textContent; try { navigator.clipboard.writeText(code).then(function () { cp.textContent = 'Скопировано'; }); } catch (er) {} }
    });
  }

  /* ---------- каталог: фильтры ---------- */
  var filters = $('#filters'), backdrop = $('.filters-backdrop');
  function openFilters(focusCar) { if (!filters) return; filters.classList.add('is-open'); if (backdrop) backdrop.hidden = false; body.style.overflow = 'hidden'; if (focusCar) { var m = $('[data-pk="make"]', filters); if (m) setTimeout(function () { m.focus(); }, 300); } }
  function closeFilters() { if (!filters || !filters.classList.contains('is-open')) return; filters.classList.remove('is-open'); if (backdrop) backdrop.hidden = true; body.style.overflow = ''; }
  window.closeFilters = closeFilters;
  var isDesktop = function () { return window.matchMedia('(min-width: 1000px)').matches; };
  d.addEventListener('click', function (e) {
    var o = e.target.closest('[data-open-filters]');
    if (o) { if (isDesktop()) { var m = $('#f-car [data-pk="make"]'); if (m) { m.scrollIntoView({ block: 'center', behavior: reduce ? 'auto' : 'smooth' }); m.focus({ preventScroll: true }); } } else openFilters(o.getAttribute('data-open-filters') === 'car'); }
    if (e.target.closest('[data-close-filters]')) closeFilters();
  });
  function cleanQuery(form) {
    var p = new URLSearchParams(new FormData(form)), out = new URLSearchParams();
    p.forEach(function (v, k) { if (v !== '') out.append(k, v); });
    return out.toString();
  }
  function bindCatalog(root) {
    var form = $('[data-filters]', root); if (!form) return;
    $$('[data-picker]', form).forEach(pickerInit);
    var applyBtn = $('[data-apply] span', form), t;
    function liveCount() {
      var q = cleanQuery(form), cat = location.pathname.slice(B.length).split('/')[2] || '';
      fetch(B + '/api/count?' + q + (cat ? '&cat=' + encodeURIComponent(cat) : '')).then(function (r) { return r.json(); }).then(function (r) { applyBtn.textContent = 'Показать ' + r.n + ' ' + plural(r.n, 'товар', 'товара', 'товаров'); });
    }
    function load(url, push) {
      var res = $('[data-results]', root); if (res) res.setAttribute('aria-busy', 'true');
      fetch(url, { headers: { 'X-Partial': '1' } }).then(function (r) { return r.text(); }).then(function (htmlText) {
        var doc = new DOMParser().parseFromString(htmlText, 'text/html'), fresh = doc.querySelector('[data-catalog]');
        if (!fresh) { location.href = url; return; }
        var swap = function () {
          var keepOpen = filters && filters.classList.contains('is-open');
          root.replaceWith(fresh); root = fresh; filters = $('#filters'); backdrop = $('.filters-backdrop');
          if (keepOpen) openFilters();
          bindCatalog(fresh); counts(); reveal(fresh, true); d.title = doc.title; d.dispatchEvent(new CustomEvent('avtom:swap', { detail: fresh }));
        };
        if (d.startViewTransition && !reduce) d.startViewTransition(swap); else swap();
        if (push) history.pushState({ catalog: 1 }, '', url);
      }).catch(function () { location.href = url; });
    }
    form.addEventListener('change', function (e) {
      var c = form.elements.make ? form : null;
      if (e.target.name === 'make' || e.target.name === 'model' || e.target.name === 'year' || e.target.name === 'body' || e.target.name === 'engine') { var pk = $('[data-picker]', form); if (pk && pk.__fields) saveCar(pk.__fields); renderGarage(); }
      clearTimeout(t);
      if (isDesktop()) t = setTimeout(function () { load(form.action + (cleanQuery(form) ? '?' + cleanQuery(form) : ''), true); }, 250);
      else t = setTimeout(liveCount, 200);
    });
    form.addEventListener('submit', function (e) { e.preventDefault(); closeFilters(); load(form.action + (cleanQuery(form) ? '?' + cleanQuery(form) : ''), true); });
    root.addEventListener('click', function (e) {
      var a = e.target.closest('.active-chips a, .viewsw a, .carbar a, .empty a');
      if (a && a.href.indexOf(location.origin + B + '/catalog') === 0) { e.preventDefault(); if (a.closest('.carbar')) { store.set('avtom-car', null); renderGarage(); } load(a.href, true); }
    });
    var sort = $('[data-sort]', root); if (sort) sort.addEventListener('change', function () { load(sort.value, true); });
  }
  var catRoot = $('[data-catalog]');
  if (catRoot) { bindCatalog(catRoot); window.addEventListener('popstate', function () { location.reload(); }); }

  /* ---------- карточка товара ---------- */
  var gal = $('[data-gallery]');
  var lbList = []; try { lbList = JSON.parse(($('[data-lb-list]') || {}).textContent || '[]'); } catch (e) {}
  var galIdx = 0;
  function galGo(i) {
    var slides = $$('.gal-slide', gal); if (!slides.length) return;
    galIdx = (i + slides.length) % slides.length;
    slides.forEach(function (s, k) { s.hidden = k !== galIdx; s.classList.toggle('is-on', k === galIdx); });
    $$('.gal-thumb', gal).forEach(function (t, k) { t.classList.toggle('is-on', k === galIdx); t.setAttribute('aria-selected', k === galIdx ? 'true' : 'false'); });
    var c = $('.gal-count b', gal); if (c) c.textContent = galIdx + 1;
  }
  if (gal) {
    gal.addEventListener('click', function (e) {
      var n = e.target.closest('[data-gal]'); if (n) galGo(galIdx + +n.getAttribute('data-gal'));
      var t = e.target.closest('[data-thumb]'); if (t) galGo(+t.getAttribute('data-thumb'));
      var z = e.target.closest('[data-zoom]'); if (z && lbList.length) openLightbox(+z.getAttribute('data-zoom'));
    });
    swipe($('.gal-main', gal), function (dir) { galGo(galIdx + dir); });
  }
  var lb = $('[data-lightbox]'), lbIdx = 0;
  function openLightbox(i) { if (!lb) return; lbIdx = i; $('[data-lb-img]', lb).src = lbList[i]; lb.hidden = false; body.style.overflow = 'hidden'; $('[data-lb-close]', lb).focus(); }
  function closeLightbox() { if (!lb || lb.hidden) return; lb.hidden = true; body.style.overflow = ''; }
  if (lb) {
    lb.addEventListener('click', function (e) {
      if (e.target.closest('[data-lb-close]') || e.target === lb) closeLightbox();
      var n = e.target.closest('[data-lb]'); if (n) { lbIdx = (lbIdx + +n.getAttribute('data-lb') + lbList.length) % lbList.length; $('[data-lb-img]', lb).src = lbList[lbIdx]; }
    });
    swipe(lb, function (dir) { lbIdx = (lbIdx + dir + lbList.length) % lbList.length; $('[data-lb-img]', lb).src = lbList[lbIdx]; });
  }
  function swipe(el, cb) {
    if (!el) return; var x0 = null, y0 = null;
    el.addEventListener('touchstart', function (e) { x0 = e.touches[0].clientX; y0 = e.touches[0].clientY; }, { passive: true });
    el.addEventListener('touchend', function (e) { if (x0 === null) return; var dx = e.changedTouches[0].clientX - x0, dy = e.changedTouches[0].clientY - y0; if (Math.abs(dx) > 40 && Math.abs(dx) > Math.abs(dy)) cb(dx < 0 ? 1 : -1); x0 = null; }, { passive: true });
  }
  // проверка совместимости в карточке
  var fit = $('[data-fitbox]');
  if (fit) {
    var compat = []; try { compat = JSON.parse(($('[data-fit-compat]') || {}).textContent || '[]'); } catch (e) {}
    var fForm = $('[data-fit-form]', fit), fOpen = $('[data-fit-open]', fit), fState = $('[data-fit-state]', fit);
    var bodiesMatter = compat.some(function (c) { return c.b.length; }), enginesMatter = compat.some(function (c) { return c.e.length; });
    function match(car) {
      var best = 'no';
      compat.forEach(function (c) {
        if (best === 'yes' || c.mk !== car.make || (car.model && c.md !== car.model)) return;
        var st = 'yes', y = +car.year;
        if (y) { if (!c.yf && !c.yt) st = 'check'; else if ((c.yf && y < c.yf) || (c.yt && y > c.yt)) return; }
        if (car.body && bodiesMatter) { if (!c.b.length) st = 'check'; else if (c.b.map(function (s) { return s.toLowerCase(); }).indexOf(car.body.toLowerCase()) < 0) return; }
        if (car.engine && enginesMatter) { if (!c.e.length) st = 'check'; else if (c.e.indexOf(car.engine) < 0) return; }
        if (st === 'yes') best = 'yes'; else best = 'check';
      });
      return best;
    }
    function show(car) {
      var r = match(car), label = car.label || '';
      fit.setAttribute('data-state', r);
      var txt = { yes: ['Подходит к вашему автомобилю', label], check: ['Модель подходит — уточните кузов или двигатель', label + '. Напишите менеджеру, проверим по VIN-номеру.'], no: ['Не подходит к вашему автомобилю', label + '. Посмотрите подбор для своей машины.'] }[r];
      fState.innerHTML = '<span class="fitbox-ic">' + icon(r === 'yes' ? 'check-circle' : (r === 'check' ? 'alert' : 'close')) + '</span><div><b>' + esc(txt[0]) + '</b><small>' + esc(txt[1]) + (r === 'no' && car.make ? ' <a href="' + B + '/catalog?' + carQuery(car) + '">Подобрать</a>' : '') + '</small></div>';
      fOpen.innerHTML = icon('car') + 'Другой автомобиль'; fOpen.hidden = false; fForm.hidden = true;
    }
    fOpen.addEventListener('click', function () { fForm.hidden = false; fOpen.hidden = true; var m = $('[data-pk="make"]', fForm); if (m) m.focus(); });
    $('[data-fit-check]', fit).addEventListener('click', function () {
      var pk = $('[data-picker]', fForm); var c = carFromFields(pk.__fields);
      if (!c.make) { $('[data-pk="make"]', fForm).focus(); return; }
      saveCar(pk.__fields); renderGarage(); show(store.get('avtom-car', c));
    });
    var saved = store.get('avtom-car', null); if (saved && saved.make) show(saved);
  }
  var buybar = $('[data-buybar]'), buyBox = $('.pp-buy');
  if (buybar && buyBox && 'IntersectionObserver' in window) new IntersectionObserver(function (es) { buybar.classList.toggle('is-on', !es[0].isIntersecting && es[0].boundingClientRect.top < 0); }).observe(buyBox);
  d.addEventListener('click', function (e) {
    var c = e.target.closest('[data-copy]'); if (!c) return;
    var v = c.getAttribute('data-copy');
    (navigator.clipboard ? navigator.clipboard.writeText(v) : Promise.reject()).then(function () { toast('Скопировано: ' + v); c.classList.add('is-copied'); setTimeout(function () { c.classList.remove('is-copied'); }, 1200); }).catch(function () {});
  });

  /* ---------- страница запроса ---------- */
  function renderCart() {
    var box = $('[data-cart-items]'); if (!box) return;
    var empty = $('[data-cart-empty]'), formBox = $('[data-cart-form]');
    if (!cart.length) { box.innerHTML = ''; empty.hidden = false; formBox.hidden = true; return; }
    fetch(B + '/api/products?ids=' + cart.map(function (x) { return x.id; }).join(',')).then(function (r) { return r.json(); }).then(function (r) {
      var byId = {}; r.items.forEach(function (p) { byId[p.id] = p; });
      cart = cart.filter(function (x) { return byId[x.id]; }); store.set('avtom-cart', cart); counts();
      if (!cart.length) { renderCart(); return; }
      box.innerHTML = cart.map(function (x) {
        var p = byId[x.id];
        var mp = (p.wb ? '<a class="btn btn-wb" href="' + p.wb + '" target="_blank" rel="nofollow noopener" data-goal="mp_wb" data-mp="wb">Wildberries</a>' : '')
          + (p.ozon ? '<a class="btn btn-ozon" href="' + p.ozon + '" target="_blank" rel="nofollow noopener" data-goal="mp_ozon" data-mp="ozon">Ozon</a>' : '');
        return '<article class="ci" data-ci="' + p.id + '">'
          + '<a class="ci-img" href="' + p.url + '">' + (p.img ? '<img src="' + p.img + '" alt="" width="96" height="96">' : '') + '</a>'
          + '<div class="ci-body"><small>' + esc(p.brand) + ' · ' + esc(p.sku) + '</small><a class="ci-name" href="' + p.url + '">' + esc(p.name) + '</a>'
          + (p.price ? '<b class="ci-price">от ' + esc(p.price) + '</b>' : '') + '<span class="ci-compat">' + esc(p.compat) + '</span></div>'
          + '<div class="ci-foot">' + (mp ? '<div class="ci-mp">' + mp + '</div>' : '<span class="ci-note">Наличие уточнит менеджер</span>')
          + '<div class="ci-side"><div class="qty" role="group" aria-label="Количество"><button type="button" data-qty="-1" aria-label="Меньше">' + icon('minus') + '</button><output>' + x.qty + '</output><button type="button" data-qty="1" aria-label="Больше">' + icon('plus') + '</button></div>'
          + '<button class="icon-btn ci-del" type="button" data-del aria-label="Убрать из корзины">' + icon('trash') + '</button></div></div></article>';
      }).join('');
      empty.hidden = true; formBox.hidden = false; syncCartForm();
    });
  }
  function syncCartForm() {
    var form = $('[data-cart-form] form'); if (!form) return;
    form.elements.items.value = JSON.stringify(cart);
    var n = cart.reduce(function (s, x) { return s + x.qty; }, 0);
    $('[data-cart-sum]').textContent = cart.length + ' ' + plural(cart.length, 'позиция', 'позиции', 'позиций') + ', ' + n + ' шт. Менеджер проверит наличие и перезвонит.';
    var car = store.get('avtom-car', null), ct = $('[data-car-text]', form); if (ct && car && car.label && !ct.value) ct.value = car.label;
  }
  var cartPage = $('[data-cart-page]');
  if (cartPage) {
    renderCart();
    cartPage.addEventListener('click', function (e) {
      var ci = e.target.closest('[data-ci]'); if (!ci) return; var id = +ci.getAttribute('data-ci');
      var q = e.target.closest('[data-qty]');
      if (q) { cart.forEach(function (x) { if (x.id === id) x.qty = Math.max(1, Math.min(99, x.qty + +q.getAttribute('data-qty'))); $('output', ci).textContent = cart.filter(function (y) { return y.id === id; })[0].qty; }); store.set('avtom-cart', cart); syncCartForm(); }
      if (e.target.closest('[data-del]')) { ci.classList.add('is-removing'); setTimeout(function () { cart = cart.filter(function (x) { return x.id !== id; }); store.set('avtom-cart', cart); counts(); ci.remove(); if (!cart.length) renderCart(); else syncCartForm(); }, reduce ? 0 : 300); }
    });
  }

  /* ---------- сравнение ---------- */
  var cmpBox = $('[data-compare-page]');
  function renderCompare() {
    if (!cmp.length) { cmpBox.innerHTML = ''; $('[data-compare-empty]').hidden = false; $('[data-diff-toggle]').hidden = true; return; }
    fetch(B + '/api/products?ids=' + cmp.join(',')).then(function (r) { return r.json(); }).then(function (r) {
      var items = r.items; if (!items.length) { cmp = []; store.set('avtom-cmp', cmp); counts(); renderCompare(); return; }
      var rows = [['Производитель', function (p) { return p.brand; }], ['Категория', function (p) { return p.category; }], ['Артикул', function (p) { return p.sku; }], ['Совместимость', function (p) { return p.compat; }]];
      r.attrs.forEach(function (a) { rows.push([a, function (p) { var v = ''; p.attrs.forEach(function (x) { if (x[0] === a) v = x[1]; }); return v || '—'; }]); });
      rows.push(['В комплекте', function (p) { return p.kit || '—'; }]); rows.push(['Наличие', function (p) { return p.stock; }]);
      var h = '<div class="cmp-wrap"><table class="cmp"><thead><tr class="cmp-keep"><th scope="row"><span class="sr">Товар</span></th>' + items.map(function (p) {
        return '<td><div class="cmp-head">' + (p.img ? '<img src="' + p.img + '" alt="" loading="lazy">' : '<span class="sg-ph"></span>') + '<a href="' + p.url + '">' + esc(p.name) + '</a>'
          + '<div class="mp-row">' + (p.wb ? '<a class="btn btn-wb" href="' + p.wb + '" target="_blank" rel="nofollow noopener" data-goal="mp_wb" data-mp="wb">WB</a>' : '') + (p.ozon ? '<a class="btn btn-ozon" href="' + p.ozon + '" target="_blank" rel="nofollow noopener" data-goal="mp_ozon" data-mp="ozon">Ozon</a>' : '') + '</div>'
          + '<button class="btn btn-soft" type="button" data-cmp-del="' + p.id + '">' + icon('trash') + 'Убрать</button></div></td>';
      }).join('') + '</tr></thead><tbody>';
      rows.forEach(function (row) {
        var vals = items.map(row[1]), diff = vals.some(function (v) { return v !== vals[0]; });
        h += '<tr class="' + (diff && items.length > 1 ? 'is-diff' : '') + '"><th scope="row">' + esc(row[0]) + '</th>' + vals.map(function (v) { return '<td>' + esc(v) + '</td>'; }).join('') + '</tr>';
      });
      cmpBox.innerHTML = h + '</tbody></table></div>';
      $('[data-compare-empty]').hidden = true; $('[data-diff-toggle]').hidden = items.length < 2;
    });
  }
  if (cmpBox) {
    renderCompare();
    cmpBox.addEventListener('click', function (e) { var b = e.target.closest('[data-cmp-del]'); if (b) { cmp = cmp.filter(function (x) { return x !== +b.getAttribute('data-cmp-del'); }); store.set('avtom-cmp', cmp); counts(); renderCompare(); } });
    var od = $('[data-only-diff]'); if (od) od.addEventListener('change', function () { cmpBox.classList.toggle('cmp-only-diff', od.checked); });
  }

  /* ---------- появление, маска заголовков, счётчики ---------- */
  function splitWords(el) {
    if (el.__split) return; el.__split = 1;
    var words = el.textContent.trim().split(/\s+/);
    el.setAttribute('aria-label', el.textContent.trim());
    el.innerHTML = words.map(function (w, i) { return '<span class="w" aria-hidden="true"><span style="--i:' + i + '">' + esc(w) + '</span></span>'; }).join(' ');
  }
  function countUp(el) {
    var raw = el.getAttribute('data-count-to'), m = raw.match(/^(\d+)(.*)$/); if (!m || reduce) return;
    var to = +m[1], suf = m[2], t0 = null, dur = 1200;
    function step(t) { if (!t0) t0 = t; var k = Math.min(1, (t - t0) / dur), e = 1 - Math.pow(1 - k, 3); el.textContent = Math.round(to * e) + suf; if (k < 1) requestAnimationFrame(step); }
    requestAnimationFrame(step);
  }
  var io = 'IntersectionObserver' in window && !reduce ? new IntersectionObserver(function (es) {
    es.forEach(function (en) { if (!en.isIntersecting) return; en.target.classList.add('is-in'); io.unobserve(en.target); $$('[data-count-to]', en.target).forEach(countUp); });
  }, { rootMargin: '0px 0px -6% 0px', threshold: 0 }) : null;
  // Появление при прокрутке. То, что видно сразу после загрузки, не прячем и не анимируем — первый экран не мигает.
  function reveal(root, instant) {
    var vh = window.innerHeight;
    $$('[data-reveal]', root).forEach(function (el) {
      if (!io || instant || el.getBoundingClientRect().top < vh) return;
      el.classList.add('rv-wait');
      io.observe(el);
    });
  }
  reveal(d);


  /* ---------- вопросы и ответы ----------
     Открыт один ответ: открываем новый — предыдущий закрывается. Высота меняется плавно, а вопрос, на который нажали,
     остаётся на месте экрана: пока соседний ответ сворачивается выше, страница докручивается на ту же величину. */
  $$('[data-faq]').forEach(function (faq) {
    var items = $$('details', faq);
    function animate(det, open) {
      var a = $('.faq-a', det);
      if (reduce || !a.animate) { det.open = open; return; }
      if (open) det.open = true;
      var h = a.scrollHeight;
      a.style.overflow = 'hidden';
      var an = a.animate(open ? [{ height: '0px', opacity: 0 }, { height: h + 'px', opacity: 1 }] : [{ height: h + 'px', opacity: 1 }, { height: '0px', opacity: 0 }], { duration: 360, easing: 'cubic-bezier(.16,1,.3,1)' });
      an.onfinish = function () { a.style.overflow = ''; if (!open) det.open = false; };
    }
    items.forEach(function (det) {
      $('summary', det).addEventListener('click', function (e) {
        e.preventDefault();
        var opening = !det.open, sum = this, top0 = sum.getBoundingClientRect().top, t0 = performance.now();
        if (opening) items.forEach(function (o) { if (o !== det && o.open) animate(o, false); });
        animate(det, opening);
        (function keep() {
          var dy = sum.getBoundingClientRect().top - top0;
          if (Math.abs(dy) > .5) window.scrollBy(0, dy);
          if (performance.now() - t0 < 420) requestAnimationFrame(keep);
        })();
      });
    });
  });

  /* ---------- карусель баннеров на главной ----------
     Лента слайдов двигается transform'ом. Полоски под баннером показывают, сколько слайдов и сколько осталось до следующего:
     заполнение — CSS-анимация, по её окончании листаем дальше. Наведение, фокус, палец на баннере и скрытая вкладка ставят паузу. */
  var slider = $('[data-slider]');
  if (slider) (function () {
    var track = $('[data-slider-track]', slider), view = track.parentNode;
    var slides = $$('.slide', track), bars = $$('[data-slide]', slider), n = slides.length, cur = 0;
    if (n < 2) return;
    var holds = {};
    function hold(why, on) {
      if (on) holds[why] = 1; else delete holds[why];
      slider.classList.toggle('is-paused', Object.keys(holds).length > 0);
    }
    function show(i, instant) {
      cur = (i + n) % n;
      track.classList.toggle('is-instant', !!instant);
      track.style.transform = 'translate3d(' + (-cur * 100) + '%, 0, 0)';
      slides.forEach(function (s, k) {
        var on = k === cur;
        s.classList.toggle('is-on', on);
        if (on) s.removeAttribute('aria-hidden'); else s.setAttribute('aria-hidden', 'true');
        (s.matches('a') ? [s] : $$('a', s)).forEach(function (a) { if (on) a.removeAttribute('tabindex'); else a.tabIndex = -1; });
      });
      bars.forEach(function (b, k) {
        b.setAttribute('aria-selected', k === cur ? 'true' : 'false');
        b.classList.toggle('is-done', k < cur);
        if (k === cur) { var f = b.firstChild; f.style.animation = 'none'; void f.offsetWidth; f.style.animation = ''; }
      });
    }
    bars.forEach(function (b) { b.addEventListener('click', function () { show(+b.getAttribute('data-slide')); }); });
    $$('[data-slide-step]', slider).forEach(function (b) { b.addEventListener('click', function () { show(cur + +b.getAttribute('data-slide-step')); }); });
    slider.addEventListener('animationend', function (e) { if (e.target.parentNode === bars[cur] && !reduce) show(cur + 1); });
    slider.addEventListener('mouseenter', function () { hold('hover', true); });
    slider.addEventListener('mouseleave', function () { hold('hover', false); });
    slider.addEventListener('focusin', function () { hold('focus', true); });
    slider.addEventListener('focusout', function () { hold('focus', false); });
    d.addEventListener('visibilitychange', function () { hold('tab', d.hidden); });
    // баннер за пределами экрана не листается
    if ('IntersectionObserver' in window) new IntersectionObserver(function (es) { hold('away', !es[0].isIntersecting); }).observe(slider);

    // свайп пальцем и перетаскивание мышью
    var x0 = 0, y0 = 0, dx = 0, t0 = 0, drag = false, axis = '';
    view.addEventListener('pointerdown', function (e) {
      if (e.button !== 0 || e.target.closest('.bnr-arrow')) return;
      x0 = e.clientX; y0 = e.clientY; dx = 0; t0 = Date.now(); drag = true; axis = '';
      hold('drag', true);
    });
    view.addEventListener('pointermove', function (e) {
      if (!drag) return;
      var mx = e.clientX - x0, my = e.clientY - y0;
      if (!axis && Math.abs(mx) + Math.abs(my) > 8) {
        axis = Math.abs(mx) > Math.abs(my) ? 'x' : 'y';
        if (axis === 'x') { try { view.setPointerCapture(e.pointerId); } catch (err) {} track.classList.add('is-instant'); }
      }
      if (axis !== 'x') return;
      dx = mx;
      var edge = (cur === 0 && dx > 0) || (cur === n - 1 && dx < 0);
      track.style.transform = 'translate3d(calc(' + (-cur * 100) + '% + ' + (edge ? dx / 3 : dx) + 'px), 0, 0)';
    });
    function release() {
      if (!drag) return;
      drag = false; hold('drag', false);
      if (axis !== 'x') return;
      var w = view.offsetWidth, fast = Math.abs(dx) / Math.max(1, Date.now() - t0) > 0.45;
      var step = (Math.abs(dx) > w * 0.18 || (fast && Math.abs(dx) > 24)) ? (dx < 0 ? 1 : -1) : 0;
      var to = Math.min(n - 1, Math.max(0, cur + step));
      show(to);
    }
    view.addEventListener('pointerup', release);
    view.addEventListener('pointercancel', release);
    // после перетаскивания баннер не открывается как ссылка
    view.addEventListener('click', function (e) { if (axis === 'x' && Math.abs(dx) > 8) { e.preventDefault(); e.stopPropagation(); } axis = ''; }, true);
    view.addEventListener('dragstart', function (e) { e.preventDefault(); });
    slider.addEventListener('keydown', function (e) {
      if (e.key === 'ArrowRight' || e.key === 'ArrowLeft') { e.preventDefault(); show(cur + (e.key === 'ArrowRight' ? 1 : -1)); var b = bars[cur]; if (b && e.target.closest('[role=tablist]')) b.focus(); }
    });
    show(0, true);
    slider.classList.add('is-ready');
  })();
})();
