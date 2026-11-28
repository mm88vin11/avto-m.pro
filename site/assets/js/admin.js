/* AVTO-M · панель управления */
(function () {
  'use strict';
  var d = document, $ = function (s, r) { return (r || d).querySelector(s); }, $$ = function (s, r) { return Array.prototype.slice.call((r || d).querySelectorAll(s)); };
  var csrf = d.body.getAttribute('data-csrf'), B = d.documentElement.getAttribute('data-base') || '';
  var esc = function (s) { return String(s).replace(/[&<>"]/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[c]; }); };
  var toastEl = $('#a-toast'), tt;
  function toast(m) { if (!toastEl) return; toastEl.textContent = m; toastEl.hidden = false; clearTimeout(tt); tt = setTimeout(function () { toastEl.hidden = true; }, 2600); }
  function post(url, data) { var fd = data instanceof FormData ? data : new FormData(); if (!(data instanceof FormData)) Object.keys(data || {}).forEach(function (k) { [].concat(data[k]).forEach(function (v) { fd.append(k + (Array.isArray(data[k]) ? '[]' : ''), v); }); }); fd.append('_csrf', csrf); return fetch(url, { method: 'POST', body: fd, headers: { 'X-CSRF': csrf } }).then(function (r) { return r.json(); }); }

  // меню на телефоне
  var side = $('.a-side');
  d.addEventListener('click', function (e) {
    if (e.target.closest('[data-a-menu]')) side.classList.toggle('is-open');
    else if (side && side.classList.contains('is-open') && !e.target.closest('.a-side')) side.classList.remove('is-open');
  });

  // подтверждение опасных действий
  d.addEventListener('click', function (e) { var b = e.target.closest('[data-confirm]'); if (b && !confirm(b.getAttribute('data-confirm'))) { e.preventDefault(); e.stopImmediatePropagation(); } }, true);

  // копирование
  d.addEventListener('click', function (e) { var c = e.target.closest('[data-copy]'); if (c && navigator.clipboard) navigator.clipboard.writeText(c.getAttribute('data-copy')).then(function () { toast('Скопировано'); }); });

  // автоотправка фильтров
  $$('[data-autosubmit]').forEach(function (f) { var t; f.addEventListener('input', function (e) { clearTimeout(t); t = setTimeout(function () { f.submit(); }, e.target.type === 'search' ? 450 : 0); }); });

  // защита от потери несохранённых правок
  $$('[data-dirty-guard]').forEach(function (f) {
    var dirty = false;
    f.addEventListener('input', function () { dirty = true; });
    f.addEventListener('change', function () { dirty = true; });
    f.addEventListener('submit', function (e) {
      dirty = false;
      var b = e.submitter; if (b && b.getAttribute('formtarget') === '_blank') { setTimeout(function () { location.reload(); }, 400); return; }
      if (b) { b.classList.add('is-loading'); setTimeout(function () { b.classList.remove('is-loading'); }, 6000); }
    });
    window.addEventListener('beforeunload', function (e) { if (dirty) { e.preventDefault(); e.returnValue = ''; } });
    d.addEventListener('keydown', function (e) { if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 's') { var b = $('[data-hotkey="s"]', f); if (b) { e.preventDefault(); b.click(); } } });
  });

  // счётчик символов для заголовков и описаний
  $$('[data-count]').forEach(function (inp) {
    var lim = +inp.getAttribute('data-count'), p = d.createElement('p'); p.className = 'a-count'; inp.after(p);
    var upd = function () { var n = inp.value.length; p.textContent = n + ' / ' + lim + (n > lim ? ' — поисковик обрежет' : ''); p.classList.toggle('over', n > lim); };
    inp.addEventListener('input', upd); upd();
  });

  // адрес страницы из названия (если пусто)
  var slug = $('[data-slug]'), src = $('[data-slug-src]');
  if (slug && src && !slug.value) slug.placeholder = 'соберётся из названия';

  // проверка ссылок маркетплейсов на лету
  $$('[data-mp-url]').forEach(function (inp) {
    inp.addEventListener('blur', function () {
      var v = inp.value.trim(), mp = inp.getAttribute('data-mp-url'), fld = inp.closest('.a-field');
      var ok = !v || (mp === 'wb' ? /^https:\/\/([a-z0-9-]+\.)?(wildberries\.ru|wb\.ru)\//i : /^https:\/\/([a-z0-9-]+\.)?ozon\.ru\//i).test(v);
      fld.classList.toggle('is-err', !ok);
      var e = $('.a-err', fld); if (!e) { e = d.createElement('p'); e.className = 'a-err'; fld.appendChild(e); }
      e.textContent = ok ? '' : (mp === 'wb' ? 'Нужна ссылка с wildberries.ru' : 'Нужна ссылка с ozon.ru'); e.hidden = ok;
    });
  });

  // перетаскивание строк (сохранение порядка на сервере) и локальное (внутри формы)
  function sortable(list, onDone) {
    var drag = null;
    list.addEventListener('dragstart', function (e) { drag = e.target.closest('li, [data-rep-row], .a-ph'); if (!drag || drag.parentNode !== list) { drag = null; return; } drag.classList.add('is-dragging'); e.dataTransfer.effectAllowed = 'move'; try { e.dataTransfer.setData('text/plain', ''); } catch (x) {} });
    list.addEventListener('dragover', function (e) {
      if (!drag) return; e.preventDefault();
      var over = e.target.closest('li, [data-rep-row], .a-ph'); if (!over || over === drag || over.parentNode !== list) return;
      var r = over.getBoundingClientRect(), vertical = list.classList.contains('a-photos') ? (e.clientX - r.left) / r.width < .5 : (e.clientY - r.top) / r.height < .5;
      list.insertBefore(drag, vertical ? over : over.nextSibling);
    });
    list.addEventListener('dragend', function () { if (!drag) return; drag.classList.remove('is-dragging'); drag = null; if (onDone) onDone(); });
    list.addEventListener('mousedown', function (e) { var h = e.target.closest('.a-drag'); var row = e.target.closest('li, [data-rep-row]'); if (h && row) row.setAttribute('draggable', 'true'); });
  }
  $$('[data-sortable]').forEach(function (list) {
    var table = list.getAttribute('data-sortable'), base = { products: B + '/admin/products/order' }[table] || (B + '/admin/' + ({ makes: 'cars', models: 'cars' }[table] || table) + '/order');
    $$('li[data-id]', list).forEach(function (li) { li.setAttribute('draggable', 'true'); });
    sortable(list, function () {
      var ids = $$('li[data-id]', list).map(function (li) { return li.getAttribute('data-id'); });
      post(base, { ids: ids, table: table }).then(function (r) { toast(r.ok ? 'Порядок сохранён' : 'Не удалось сохранить порядок'); });
    });
  });
  $$('[data-sortable-local]').forEach(function (list) { sortable(list, function () { list.dispatchEvent(new Event('change', { bubbles: true })); }); });
  d.addEventListener('click', function (e) {
    var m = e.target.closest('[data-move]'); if (!m) return;
    var li = m.closest('li'), dir = +m.getAttribute('data-move');
    if (dir < 0 && li.previousElementSibling) li.parentNode.insertBefore(li, li.previousElementSibling);
    if (dir > 0 && li.nextElementSibling) li.parentNode.insertBefore(li.nextElementSibling, li);
    li.closest('form').dispatchEvent(new Event('change'));
  });

  // повторяющиеся поля (факты марки, шаги, вопросы)
  d.addEventListener('click', function (e) {
    var add = e.target.closest('[data-rep-add]');
    if (add) {
      var box = add.previousElementSibling.matches('[data-repeat]') ? add.previousElementSibling : $('[data-repeat]', add.parentNode);
      var rows = $$('[data-rep-row]', box), tpl = rows[rows.length - 1].cloneNode(true), idx = Date.now();
      $$('input, textarea, select', tpl).forEach(function (i) { if (i.name) i.name = i.name.replace(/\[\d+\](?=\[[^\]]+\]$)/, '[' + idx + ']'); if (i.tagName !== 'SELECT' && i.type !== 'file') i.value = ''; });
      $$('.a-photo-prev', tpl).forEach(function (p) { p.innerHTML = '<svg class="i"><use href="#i-photo"/></svg>'; });
      $$('[data-photo-clear]', tpl).forEach(function (b) { b.hidden = true; });
      box.appendChild(tpl); var f = $('input, textarea', tpl); if (f) f.focus();
    }
    var del = e.target.closest('[data-rep-del]');
    if (del) { var row = del.closest('[data-rep-row]'), bx = row.parentNode; if ($$('[data-rep-row]', bx).length > 1) row.remove(); else $$('input, textarea', row).forEach(function (i) { i.value = ''; }); bx.dispatchEvent(new Event('change', { bubbles: true })); }
  });

  // совместимость: модели зависят от марки
  var models = []; try { models = JSON.parse(($('[data-models]') || {}).textContent || '[]'); } catch (x) {}
  function bindCrow(row) {
    var mk = $('[data-c-make]', row), md = $('[data-c-model]', row);
    mk.addEventListener('change', function () {
      md.innerHTML = '<option value="">—</option>' + models.filter(function (m) { return String(m.mk) === mk.value; }).map(function (m) { return '<option value="' + m.id + '">' + esc(m.n) + '</option>'; }).join('');
    });
    md.addEventListener('change', function () {
      var m = models.filter(function (x) { return String(x.id) === md.value; })[0]; if (!m) return;
      var yf = $('[name$="[year_from]"]', row), yt = $('[name$="[year_to]"]', row);
      if (!yf.value && m.yf) yf.value = m.yf; if (!yt.value && m.yt) yt.value = m.yt;
      $$('.a-bodies input', row).forEach(function (c) { c.closest('.a-chip').style.opacity = m.b.length && m.b.indexOf(c.value) < 0 ? '.45' : ''; });
    });
  }
  $$('[data-crow]').forEach(bindCrow);
  var cAdd = $('[data-compat-add]');
  if (cAdd) cAdd.addEventListener('click', function () {
    var tpl = $('[data-compat-tpl]').innerHTML.replace(/__i__/g, 'n' + Date.now());
    var box = $('[data-compat]'); box.insertAdjacentHTML('beforeend', tpl);
    var row = box.lastElementChild; bindCrow(row); $('[data-c-make]', row).focus();
  });
  d.addEventListener('click', function (e) { var b = e.target.closest('[data-crow-del]'); if (b) { b.closest('[data-crow]').remove(); d.querySelector('[data-dirty-guard]').dispatchEvent(new Event('change')); } });

  // загрузка фото
  function upload(file) { var fd = new FormData(); fd.append('photo', file); return post(B + '/admin/upload', fd); }
  var phBox = $('[data-photos]'), phInput = $('[data-ph-input]'), phDrop = $('[data-ph-drop]');
  function renum() { $$('.a-ph', phBox).forEach(function (el, i) { $$('input', el).forEach(function (inp) { inp.name = inp.name.replace(/photos\[[^\]]*\]/, 'photos[' + i + ']'); }); }); }
  function addFiles(files) {
    Array.prototype.forEach.call(files, function (file) {
      var el = d.createElement('div'); el.className = 'a-ph is-uploading'; el.draggable = true;
      el.innerHTML = '<img alt="">';
      var rd = new FileReader(); rd.onload = function () { $('img', el).src = rd.result; }; rd.readAsDataURL(file);
      phBox.appendChild(el);
      upload(file).then(function (r) {
        if (!r.ok) { el.remove(); toast(r.error || 'Не получилось загрузить'); return; }
        el.classList.remove('is-uploading');
        el.innerHTML = '<img src="' + r.url + '" alt=""><input type="hidden" name="photos[0][id]" value="' + r.id + '"><input class="a-ph-alt" name="photos[0][alt]" placeholder="Подпись к фото" aria-label="Подпись к фото"><button type="button" class="a-ph-del" data-ph-del aria-label="Убрать фото"><svg class="i"><use href="#i-close"/></svg></button>';
        renum(); phBox.closest('form').dispatchEvent(new Event('change'));
      }).catch(function () { el.remove(); toast('Нет связи с сервером'); });
    });
  }
  if (phBox) {
    phInput.addEventListener('change', function () { addFiles(phInput.files); phInput.value = ''; });
    ['dragenter', 'dragover'].forEach(function (t) { phDrop.addEventListener(t, function (e) { e.preventDefault(); phDrop.classList.add('is-over'); }); });
    ['dragleave', 'drop'].forEach(function (t) { phDrop.addEventListener(t, function (e) { e.preventDefault(); phDrop.classList.remove('is-over'); }); });
    phDrop.addEventListener('drop', function (e) { if (e.dataTransfer.files.length) addFiles(e.dataTransfer.files); });
    sortable(phBox, function () { renum(); phBox.closest('form').dispatchEvent(new Event('change')); });
    phBox.addEventListener('click', function (e) { var b = e.target.closest('[data-ph-del]'); if (b) { b.closest('.a-ph').remove(); renum(); phBox.closest('form').dispatchEvent(new Event('change')); } });
  }
  // одиночное фото (категория, марка, баннер) — через делегирование, работает и в добавленных строках
  d.addEventListener('change', function (e) {
    var file = e.target.closest('[data-photo-file]'); if (!file || !file.files[0]) return;
    var w = file.closest('[data-photo-pick]'), inp = $('input[type=hidden]', w), prev = $('.a-photo-prev', w), clr = $('[data-photo-clear]', w);
    prev.style.opacity = '.4';
    upload(file.files[0]).then(function (r) { prev.style.opacity = ''; if (!r.ok) { toast(r.error); return; } inp.value = r.id; prev.innerHTML = '<img src="' + r.url + '" alt="">'; clr.hidden = false; w.closest('form').dispatchEvent(new Event('change')); });
    file.value = '';
  });
  d.addEventListener('click', function (e) {
    var clr = e.target.closest('[data-photo-clear]'); if (!clr) return;
    var w = clr.closest('[data-photo-pick]');
    $('input[type=hidden]', w).value = ''; $('.a-photo-prev', w).innerHTML = '<svg class="i"><use href="#i-photo"/></svg>'; clr.hidden = true; w.closest('form').dispatchEvent(new Event('change'));
  });
})();
