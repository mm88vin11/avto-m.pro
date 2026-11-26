// Квиз и «упор по старому»: проходим все ветки, снимаем экраны.
import { createRequire } from 'module';
import fs from 'fs';
const { chromium } = createRequire(import.meta.url)('playwright');
const base = process.argv[2] || 'http://localhost:8080', out = process.argv[3] || 'qa-shots';
fs.mkdirSync(out, { recursive: true });
const b = await chromium.launch();
for (const [w, h, tag] of [[1440, 900, 'd'], [390, 844, 'm']]) {
  const ctx = await b.newContext({ viewport: { width: w, height: h }, isMobile: w < 800, hasTouch: w < 800 });
  await ctx.addCookies([{ name: 'avtom_consent', value: 'necessary', url: base }]);
  const p = await ctx.newPage(); const errs = [];
  p.on('pageerror', e => errs.push(String(e))); p.on('console', m => m.type() === 'error' && errs.push(m.text()));
  await p.goto(base + '/'); const q = p.locator('[data-quiz]'); await q.scrollIntoViewIfNeeded(); await p.waitForTimeout(900);
  await q.screenshot({ path: `${out}/${tag}-q1.png` });
  await q.locator('[data-q-cat="gazovye-upory-bagazhnika"]').click(); await p.waitForTimeout(600);
  await q.locator('[data-q-make="ford"]').click(); await p.waitForTimeout(600);
  await q.screenshot({ path: `${out}/${tag}-q3.png` });
  await q.locator('[data-q-model="focus-ii"]').click(); await p.waitForTimeout(600);
  await q.screenshot({ path: `${out}/${tag}-q4.png` });
  await q.locator('[data-q-body="Хэтчбек"]').click(); await p.waitForTimeout(1200);
  console.log(tag, 'результат:', await q.locator('.q-h').textContent(), '·', await q.locator('.q-card-n').allTextContents());
  await q.screenshot({ path: `${out}/${tag}-q5.png` });
  // по признакам
  await q.locator('.q-chip').first().click(); await p.waitForTimeout(500);
  await q.locator('[data-q-next="symptom"]').click(); await p.waitForTimeout(500);
  await q.screenshot({ path: `${out}/${tag}-qs.png` });
  await q.locator('[data-q-sym="2"]').click(); await p.waitForTimeout(500);
  await q.locator('[data-q-make="renault"]').click(); await p.waitForTimeout(500);
  await q.locator('[data-q-model="duster"]').click(); await p.waitForTimeout(900);
  console.log(tag, 'бачок Duster:', await q.locator('.q-card-n').allTextContents());
  // по номеру
  await q.locator('[data-q-go="start"]').last().click(); await p.waitForTimeout(500);
  await q.locator('[data-q-next="code"]').click(); await p.waitForTimeout(500);
  await q.locator('[data-q-code]').fill('3M5H 8K218 AJ'); await p.waitForTimeout(300);
  console.log(tag, 'по номеру:', await q.locator('.q-card-n').allTextContents());
  await q.screenshot({ path: `${out}/${tag}-qc.png` });
  // упор по старому
  await p.goto(base + '/podbor'); const m = p.locator('[data-measure]'); await m.scrollIntoViewIfNeeded(); await p.waitForTimeout(1400);
  await m.locator('[data-ms-len]').fill('480'); await m.locator('[data-ms-len]').dispatchEvent('change'); await p.waitForTimeout(300);
  console.log(tag, '480 мм →', await m.locator('.ms-card-n').first().textContent(), '·', await m.locator('.ms-card .fit').first().textContent());
  const hb = await m.locator('[data-ms-handle]').boundingBox();
  await p.mouse.move(hb.x + hb.width / 2, hb.y + hb.height / 2); await p.mouse.down(); await p.mouse.move(hb.x + hb.width / 2 + (w > 800 ? 120 : 40), hb.y + hb.height / 2, { steps: 6 }); await p.mouse.up();
  await p.waitForTimeout(300);
  console.log(tag, 'после перетаскивания:', await m.locator('[data-ms-out] b').textContent(), 'мм');
  await m.screenshot({ path: `${out}/${tag}-ms.png` });
  console.log(tag, 'ошибки:', errs.length ? errs : 'нет');
  await ctx.close();
}
await b.close();
