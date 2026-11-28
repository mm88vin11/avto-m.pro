// Сценарий: первый запуск админки → вход → правка товара → черновик → предпросмотр → публикация → заявка с сайта → заявка в админке.
// node tools/qa-admin.mjs http://localhost:8080 папка_для_снимков
import { createRequire } from 'module';
import fs from 'fs';
const { chromium } = createRequire(import.meta.url)('playwright');
const base = process.argv[2] || 'http://localhost:8080';
const out = process.argv[3] || 'qa-shots';
fs.mkdirSync(out, { recursive: true });
const codeFile = new URL('../site/app/data/setup-code.txt', import.meta.url);
const browser = await chromium.launch();
const ctx = await browser.newContext({ viewport: { width: 1440, height: 950 } });
const page = await ctx.newPage();
const errors = [];
page.on('pageerror', e => errors.push(String(e)));
page.on('console', m => { if (m.type() === 'error') errors.push(m.text()); });
const shot = async n => page.screenshot({ path: `${out}/adm-${n}.png`, fullPage: false });
const step = (s) => console.log('✓', s);

await page.goto(base + '/admin');
if (await page.locator('input[name=code]').count()) {
  const code = fs.readFileSync(codeFile, 'utf8').trim();
  await page.fill('input[name=code]', code);
  await page.fill('input[name=name]', 'Михаил');
  await page.fill('input[name=login]', 'owner');
  await page.fill('input[name=password]', 'Test-password-123');
  await page.fill('input[name=password2]', 'Test-password-123');
  await page.click('button[type=submit]');
  step('первый запуск');
} else {
  await page.fill('input[name=login]', 'owner');
  await page.fill('input[name=password]', 'Test-password-123');
  await page.click('button[type=submit]');
  step('вход');
}
// обучение при первом входе: показываем один раз, пропускаем и проверяем, что больше не всплывает
if (await page.locator('.a-tour').count()) { await shot('tour'); await page.click('[data-tour=skip]'); await page.waitForTimeout(500); step('обучение показано и пропущено'); }
await page.reload(); if (await page.locator('.a-tour').count()) throw new Error('обучение показалось повторно');
await page.waitForSelector('.a-stats'); await shot('dashboard');
await page.goto(base + '/admin/products'); await shot('products');
await page.click('text=LAP-10262');
await page.waitForSelector('#f_wb_url');
await page.fill('#f_wb_url', 'https://www.wildberries.ru/catalog/137348969/detail.aspx?targetUrl=GP');
await page.fill('#f_ozon_url', 'https://www.ozon.ru/product/amortizator-kryshki-bagazhnika-ford-focus-ii-hatchback-798137836/?sh=1HncS4dpxg');
await page.click('button[value=save]');
await page.waitForSelector('.a-flash-ok'); step('черновик сохранён'); await shot('product-edit');
const pv = await ctx.newPage();
await pv.goto(base + '/product/gazovye-upory-bagazhnika-liftautopro-dlya-ford-focus-ii-hetchbek-lap-10262?preview=1');
console.log('  предпросмотр: кнопка WB', await pv.locator('.pp-buy .btn-wb').count(), '· превью-полоса', await pv.locator('.preview-bar').count());
const anon = await browser.newContext(); const ap = await anon.newPage();
await ap.goto(base + '/product/gazovye-upory-bagazhnika-liftautopro-dlya-ford-focus-ii-hetchbek-lap-10262');
console.log('  посетитель до публикации: кнопка WB', await ap.locator('.pp-buy .btn-wb').count());
await page.click('button[value=publish]');
await page.waitForSelector('.a-flash-ok'); step('опубликовано');
await ap.reload();
console.log('  посетитель после публикации: кнопка WB', await ap.locator('.pp-buy .btn-wb').count(), '→', await ap.locator('.pp-buy .btn-wb').getAttribute('href'));
// откат, чтобы не оставлять образцовые ссылки
await page.goto(base + '/admin/products'); await page.click('text=LAP-10262');
await page.fill('#f_wb_url', ''); await page.fill('#f_ozon_url', '');
await page.click('button[value=publish]'); await page.waitForSelector('.a-flash-ok'); step('ссылки убраны обратно');
// заявка с сайта
await ap.goto(base + '/product/rasshiritelnyy-bachok-autoplastic-dlya-ford-focus-ii-av-11001');
await ap.click('.pp-buy [data-lead=availability]');
await ap.fill('#mf-name', 'Тест');
await ap.click('#mf-phone'); await ap.keyboard.type('9001234567');
await ap.click('#lead-modal button[type=submit]');
await ap.waitForTimeout(300);
console.log('  ошибка согласия показана:', await ap.locator('#lead-modal .field-check.is-err').count());
await ap.check('#lead-modal input[name=consent]', { force: true });
await ap.click('#lead-modal button[type=submit]');
await ap.waitForSelector('[data-modal-done]:not([hidden])', { timeout: 15000 });
console.log('  заявка №', await ap.locator('[data-done-id]').textContent());
await ap.screenshot({ path: `${out}/site-lead-done.png` });
await page.goto(base + '/admin/leads'); await shot('leads');
await page.click('.a-lead-row a'); await page.waitForSelector('.a-lead-contact'); await shot('lead');
step('заявка в админке');
for (const p of ['/admin/pages/home', '/admin/cars', '/admin/attributes', '/admin/categories', '/admin/settings', '/admin/backups', '/admin/history', '/admin/help', '/admin/products/import']) { await page.goto(base + p); await shot(p.replace(/\W+/g, '_')); }
step('разделы открываются');
// страница: правка заголовка → публикация
await page.goto(base + '/admin/pages/home');
const t = page.locator('input[name="c[slides][0][title]"]'); const old = await t.inputValue();
await t.fill(old + ' ✓'); await page.click('button[value=publish]'); await page.waitForSelector('.a-flash-ok');
await ap.goto(base + '/');
// у готового баннера заголовок слайда — подпись картинки, у слайда без баннера — видимый заголовок
const first = ap.locator('.bnr .slide').first();
console.log('  первый слайд на сайте:', await first.evaluate(el => (el.querySelector('img[alt]') && el.classList.contains('slide-banner')) ? el.querySelector('img').alt : el.querySelector('.slide-title').textContent));
await page.goto(base + '/admin/pages/home'); await page.fill('input[name="c[slides][0][title]"]', old); await page.click('button[value=publish]');
step('страница опубликована и возвращена');
// телефон
const m = await browser.newContext({ viewport: { width: 390, height: 844 }, isMobile: true, hasTouch: true, storageState: await ctx.storageState() });
const mp = await m.newPage(); await mp.goto(base + '/admin/products/1'); await mp.screenshot({ path: `${out}/adm-mobile-product.png`, fullPage: true });
await mp.goto(base + '/admin'); await mp.screenshot({ path: `${out}/adm-mobile-dash.png` });
console.log('ошибки:', errors.length ? errors : 'нет');
await browser.close();
