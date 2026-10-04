/* Run with Playwright available on NODE_PATH; no application/database writes.
 * NODE_PATH=/tmp/berps-navigation-qa/node_modules node tests/page_experience_browser_test.cjs
 * Uses installed Google Chrome, or set BERPS_TEST_BROWSER to a Playwright channel.
 */
const { chromium } = require('playwright');
const assert = require('node:assert/strict');
const http = require('node:http');
const fs = require('node:fs');
const path = require('node:path');

const root = path.resolve(__dirname, '..');
const pause = ms => new Promise(resolve => setTimeout(resolve, ms));
let version = 0;
let posts = [];
let passed = 0;

function html(url) {
  return `<!doctype html><html><head>
    <link rel="stylesheet" href="/assets/css/berps-page-experience.css">
    <script src="/assets/js/berps-page-experience.js"></script>
    <script src="/assets/js/berps-ui.js" defer></script>
    </head><body data-version="${++version}">
    <nav class="navbar-custom"><button data-berps-sidebar-toggle>Menu</button></nav>
    <aside class="left-side-menu">Sidebar</aside><main class="content-page">
    <h1>Working page</h1><input id="editable" value="unsaved work">
    <a id="slow" href="/slow">Slow page</a><a id="fast" href="/fast">Fast page</a>
    <a id="cancelled" href="/slow" onclick="event.preventDefault()">Cancelled</a>
    <a id="optout" href="/slow" data-berps-loading="off">Opt out</a>
    <a id="hash" href="#section">Section</a><div id="section">Section</div>
    <a id="download" href="/export" download>Download</a>
    <a id="tab" href="/slow" target="_blank">New tab</a>
    <a id="frame" href="/slow" target="preview">Frame</a>
    <a id="confirm" href="/slow" data-berps-confirm="Continue?">Confirm</a>
    <form id="form" method="post" action="/save" enctype="multipart/form-data">
      <input id="required" name="title" required><input type="file" name="file" id="file">
      <button id="save" name="operation" value="save">Save</button>
    </form>
    <form id="cancel-form" method="post" action="/save" onsubmit="event.preventDefault()"><button>Cancel submit</button></form>
    <form id="confirm-form" method="post" action="/save" data-berps-confirm="Save?">
      <button name="operation" value="confirmed">Confirm submit</button>
    </form>
    </main><iframe name="preview"></iframe>
    <script>window.fixtureInitializations = (window.fixtureInitializations || 0) + 1;</script>
    ${url.pathname === '/initial-slow' ? '<script src="/slow-script.js"></script>' : ''}
    </body></html>`;
}

const server = http.createServer(async (req, res) => {
  const url = new URL(req.url, 'http://localhost');
  if (url.pathname.startsWith('/assets/')) {
    res.setHeader('Content-Type', url.pathname.endsWith('.css') ? 'text/css' : 'text/javascript');
    return res.end(fs.readFileSync(path.join(root, url.pathname)));
  }
  if (req.method === 'POST') {
    const chunks = [];
    for await (const chunk of req) chunks.push(chunk);
    posts.push(Buffer.concat(chunks).toString());
  }
  if (['/slow', '/save', '/slow-script.js'].includes(url.pathname)) await pause(1500);
  if (url.pathname === '/slow-script.js') {
    res.setHeader('Content-Type', 'text/javascript');
    return res.end('window.slowScriptReady = true;');
  }
  if (url.pathname === '/export') {
    res.setHeader('Content-Disposition', 'attachment; filename="report.csv"');
    return res.end('test,data');
  }
  res.setHeader('Content-Type', 'text/html');
  res.end(html(url));
});

(async () => {
  await new Promise(resolve => server.listen(0, '127.0.0.1', resolve));
  const base = `http://127.0.0.1:${server.address().port}`;
  const browser = await chromium.launch({ channel: process.env.BERPS_TEST_BROWSER || 'chrome', headless: true });
  async function check(name, fn) {
    await fn();
    passed++;
    console.log('PASS ' + name);
  }
  try {
    for (const fallback of [false, true]) {
      const context = await browser.newContext({ viewport: { width: 1200, height: 800 } });
      if (fallback) await context.addInitScript(() => Object.defineProperty(window, 'navigation', { value: undefined }));
      await context.addInitScript(() => {
        if (window !== window.top) return;
        setInterval(() => {
          const el = document.querySelector('.berps-page-loading');
          console.debug('BERPS_QA:' + JSON.stringify({
            visible: !!el && !el.hidden && getComputedStyle(el).display !== 'none',
            content: (document.querySelector('h1')?.getBoundingClientRect().height || 0) > 0,
            value: document.querySelector('#editable')?.value
          }));
        }, 40);
      });
      const page = await context.newPage();
      let sample = {};
      page.on('console', event => {
        if (event.text().startsWith('BERPS_QA:')) sample = JSON.parse(event.text().slice(9));
      });
      const errors = [];
      page.on('pageerror', e => errors.push(e.message));
      const mode = fallback ? 'fallback: ' : 'native: ';
      const reset = async () => { await page.goto(base + '/'); await page.waitForTimeout(50); };
      // Browser samples avoid locator reads waiting for navigation to complete.
      const visible = async () => { await pause(60); return sample.visible; };
      const noSpinner = async () => { await page.waitForTimeout(750); assert.equal(await visible(), false); };

      await check(mode + 'slow navigation keeps content visible, loads a fresh document and clears spinner', async () => {
        await reset();
        const oldVersion = await page.locator('body').getAttribute('data-version');
        await page.locator('#slow').click({ noWaitAfter: true });
        await page.waitForTimeout(800);
        assert.equal(await visible(), true);
        assert.equal(sample.content, true);
        assert.equal(sample.value, 'unsaved work');
        await page.waitForURL(base + '/slow');
        await page.waitForTimeout(50);
        assert.notEqual(await page.locator('body').getAttribute('data-version'), oldVersion);
        assert.equal(await page.evaluate(() => window.fixtureInitializations), 1);
        assert.equal(await visible(), false);
      });
      await check(mode + 'fast navigation and history do not leave a loader', async () => {
        await page.locator('#fast').click();
        await page.waitForURL(base + '/fast');
        await page.goBack();
        await page.goForward();
        await noSpinner();
      });
      await check(mode + 'canceled clicks, hashes, invalid and canceled forms stay idle', async () => {
        await reset();
        await page.locator('#cancelled').click();
        await noSpinner();
        await page.locator('#hash').click();
        await noSpinner();
        await page.locator('#save').click();
        await noSpinner();
        await page.locator('#cancel-form button').click();
        await noSpinner();
        assert.equal(posts.length, fallback ? 2 : 0);
      });
      await check(mode + 'confirmation cancellation does not start navigation', async () => {
        await page.locator('#confirm').click();
        await noSpinner();
        assert.equal(await page.locator('.berps-confirm-dialog').isVisible(), true);
        await page.locator('.berps-confirm-dialog__actions [data-berps-confirm-cancel]').click();
        await noSpinner();
      });
      await check(mode + 'multipart form preserves upload and submitter and posts exactly once', async () => {
        const count = posts.length;
        await page.locator('#required').fill('Saved title');
        await page.locator('#file').setInputFiles({name:'sample.txt', mimeType:'text/plain', buffer:Buffer.from('sample file data')});
        await page.locator('#save').click({ noWaitAfter: true });
        await page.waitForTimeout(800);
        assert.equal(await visible(), true);
        await page.waitForURL(base + '/save');
        assert.equal(posts.length, count + 1);
        assert.match(posts.at(-1), /Saved title/);
        assert.match(posts.at(-1), /sample file data/);
        assert.match(posts.at(-1), /name="operation"\r\n\r\nsave/);
        await noSpinner();
      });
      await check(mode + 'confirmed form submits only once and retains submitter', async () => {
        const count = posts.length;
        await reset();
        await page.locator('#confirm-form button').click();
        await noSpinner();
        await page.locator('[data-berps-confirm-accept]').click({ noWaitAfter:true });
        await page.waitForURL(base + '/save');
        assert.equal(posts.length, count + 1);
        assert.match(posts.at(-1), /operation=confirmed/);
      });
      await check(mode + 'downloads, frames and new tabs leave the current page idle', async () => {
        await reset();
        const download = page.waitForEvent('download');
        await page.locator('#download').click();
        await download;
        await noSpinner();
        await page.locator('#frame').click();
        await noSpinner();
        const popup = page.waitForEvent('popup');
        await page.locator('#tab').click();
        const tab = await popup;
        await noSpinner();
        await tab.close();
      });
      await check(mode + 'background fetch stays silent; foreground spinner can be dismissed', async () => {
        await page.evaluate(() => { fetch('/slow'); });
        await noSpinner();
        await page.evaluate(() => { window.finishWork = window.BerpsLoading.start('Loading report…'); });
        await page.waitForTimeout(750);
        assert.equal(await visible(), true);
        await page.locator('.berps-page-loading__dismiss').click();
        assert.equal(await visible(), false);
        await page.evaluate(() => window.finishWork());
      });
      await check(mode + 'sidebar preference survives navigation and mobile drawer stays closed', async () => {
        await page.locator('[data-berps-sidebar-toggle]').click();
        await page.waitForTimeout(30);
        await page.locator('#fast').click();
        assert.equal(await page.locator('body').evaluate(el => el.classList.contains('berps-sidebar-collapsed')), true);
        await page.setViewportSize({width:390,height:844});
        await page.reload();
        assert.equal(await page.locator('body').evaluate(el => el.classList.contains('berps-sidebar-open')), false);
        await page.locator('[data-berps-sidebar-toggle]').click();
        assert.equal(await page.locator('body').evaluate(el => el.classList.contains('berps-sidebar-open')), true);
      });
      if (!fallback) {
        await check(mode + 'reload shows progress and clears it after the new document initializes', async () => {
          await page.goto(base + '/slow');
          await page.waitForTimeout(250);
          const reload = page.reload().catch(error => ({error}));
          await page.waitForTimeout(800);
          assert.equal(await visible(), true);
          assert.equal((await reload)?.error, undefined);
          await noSpinner();
        });
        await check(mode + 'stopped navigation and explicit opt-out do not strand the spinner', async () => {
          await reset();
          await page.evaluate(() => {
            document.querySelector('#slow').addEventListener('click', () => {
              setTimeout(() => window.stop(), 900);
            }, {once:true});
          });
          await page.locator('#slow').click({noWaitAfter:true});
          await page.waitForTimeout(1100);
          assert.equal(await visible(), false);
          await reset();
          await page.locator('#optout').click({noWaitAfter:true});
          await page.waitForTimeout(750);
          assert.equal(await visible(), false);
          await page.waitForURL(base + '/slow');
        });
      }
      await check(mode + 'initial slow scripts get a spinner that clears on readiness', async () => {
        const loading = page.goto(base + '/initial-slow');
        await page.waitForTimeout(850);
        assert.equal(await visible(), true);
        await loading;
        await noSpinner();
      });
      await check(mode + 'reduced motion and printing suppress animations/indicator', async () => {
        await page.emulateMedia({reducedMotion:'reduce'});
        await page.evaluate(() => window.BerpsLoading.start());
        await page.waitForTimeout(750);
        assert.equal(await page.locator('.berps-page-loading__spinner').evaluate(el => getComputedStyle(el).animationName), 'none');
        await page.emulateMedia({media:'print'});
        assert.equal(await visible(), false);
      });
      assert.deepEqual(errors, []);
      await context.close();
    }
    await check('native links still work with JavaScript disabled', async () => {
      const context = await browser.newContext({javaScriptEnabled:false});
      const page = await context.newPage();
      await page.goto(base + '/');
      await page.locator('#fast').click();
      assert.equal(new URL(page.url()).pathname, '/fast');
      await context.close();
    });
    console.log(`Passed ${passed} browser checks.`);
  } finally {
    await browser.close();
    server.close();
  }
})().catch(error => { console.error(error); server.close(); process.exitCode = 1; });
