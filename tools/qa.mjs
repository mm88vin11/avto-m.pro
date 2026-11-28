// Проверка вёрстки: скриншоты страниц и ошибки консоли.
// node tools/qa.mjs [адрес] [папка] [ширина] [высота] [пути через запятую]
import { createRequire } from 'module';
const { chromium } = createRequire(import.meta.url)('playwright');
const base = process.argv[2] || 'http://localhost:8080';
const out = process.argv[3] || 'qa-shots';
const w = +(process.argv[4] || 1440), hgt = +(process.argv[5] || 900);
const paths = (process.argv[6] || '/,/catalog/gazovye-upory-bagazhnika,/product/gazovye-upory-bagazhnika-liftautopro-dlya-ford-focus-ii-hetchbek-lap-10262').split(',');
import fs from 'fs';
fs.mkdirSync(out, { recursive: true });
const browser = await chromium.launch();
const ctx = await browser.newContext({ viewport: { width: w, height: hgt }, deviceScaleFactor: 1, reducedMotion: 'reduce', hasTouch: w < 800, isMobile: w < 800 });
const page = await ctx.newPage();
const errors = [];
page.on('console', m => { if (m.type() === 'error') errors.push(m.text()); });
page.on('pageerror', e => errors.push(String(e)));
for (const p of paths) {
  await page.goto(base + p, { waitUntil: 'networkidle' });
  await page.evaluate(async () => { for (let y = 0; y < document.body.scrollHeight; y += 500) { window.scrollTo(0, y); await new Promise(r => setTimeout(r, 60)); } window.scrollTo(0, 0); });
  await page.waitForTimeout(500);
  const overflow = await page.evaluate(() => document.documentElement.scrollWidth - window.innerWidth);
  const name = (p.replace(/[^a-z0-9]+/gi, '_') || 'home').slice(0, 60);
  await page.screenshot({ path: `${out}/${w}-${name}.png`, fullPage: true });
  console.log(p, 'overflow:', overflow);
}
console.log('errors:', errors.length ? errors : 'нет');
await browser.close();
