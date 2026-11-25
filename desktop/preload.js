const { contextBridge, ipcRenderer, webFrame } = require('electron');

const invoke = (channel, ...args) => ipcRenderer.invoke(channel, ...args);

const api = {
  isDesktop: true,
  platform: process.platform,
  version: '2.0.2',

  // Session + API (used by the native app UI in app/index.html)
  auth: {
    login: (username, password) => invoke('berps:auth-login', { username, password }),
    me: () => invoke('berps:auth-me'),
    state: () => invoke('berps:auth-state'),
    logout: () => invoke('berps:auth-logout'),
  },
  api: (method, path, body) => invoke('berps:api', { method, path, body }),
  portal: {
    open: (target) => invoke('berps:portal-open', target),
    openApp: (hash) => invoke('berps:app-open', hash),
    origin: () => invoke('berps:portal-origin'),
  },
  openExternal: (url) => invoke('berps:open-external', url),

  // shell
  notify: (title, body, hash) => invoke('berps:notify', { title, body, hash }),
  setBadge: (count) => invoke('berps:badge', count),
  onNavigate: (cb) => ipcRenderer.on('berps:navigated', (_e, url) => cb(url)),
  onMenuGoto: (cb) => ipcRenderer.on('berps:menu-goto', (_e, hash) => cb(hash)),
};

contextBridge.exposeInMainWorld('berps', api);
try { window.berps = api; } catch (_) { /* non-fatal */ }

/* ---------------------------------------------------------------------------
 * Portal pages get the desktop skin + matching extensions injected, plus a
 * floating "Back to BERPS Desk" button so the web portal is never a dead end.
 * Local app pages (file://) skip straight past this.
 * ------------------------------------------------------------------------- */

const skinnedDocs = new WeakSet();
const extensionRuns = new WeakMap();

function runsFor(doc) {
  let set = extensionRuns.get(doc);
  if (!set) { set = new Set(); extensionRuns.set(doc, set); }
  return set;
}

async function injectSkin() {
  if (skinnedDocs.has(document)) return;
  skinnedDocs.add(document);
  const css = await invoke('berps:skin');
  if (css) await webFrame.insertCSS(css);
}

async function runExtensions() {
  const ran = runsFor(document);
  const exts = await invoke('berps:extensions', location.href);
  for (const ext of exts) {
    for (const css of ext.css) {
      try { await webFrame.insertCSS(css); } catch (err) { console.warn(`[berps] ${ext.id} css:`, err); }
    }
    const key = `${ext.id}@${location.pathname}`;
    if (ran.has(key)) continue;
    for (const js of ext.js) {
      try {
        await webFrame.executeJavaScript(`(function(){\n${js}\n})();`);
        ran.add(key);
      } catch (err) {
        console.warn(`[berps] ${ext.id} js:`, err);
      }
    }
  }
}

function addBackButton() {
  if (!document.body || document.getElementById('berps-desk-back')) return;
  const btn = document.createElement('button');
  btn.id = 'berps-desk-back';
  btn.type = 'button';
  btn.textContent = '← BERPS Desk';
  btn.title = 'Back to the BERPS Desk app (⌘⇧D)';
  btn.setAttribute('style', [
    'position:fixed', 'left:14px', 'bottom:14px', 'z-index:2147483646',
    'padding:8px 14px', 'border:0', 'border-radius:999px',
    'background:#0e1630', 'color:#fff', 'font:600 12px -apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif',
    'box-shadow:0 8px 24px rgba(14,22,48,.35)', 'cursor:pointer', '-webkit-app-region:no-drag',
  ].join(';'));
  btn.addEventListener('click', () => invoke('berps:app-open'));
  document.body.appendChild(btn);
}

let portalOriginPromise = null;
function portalOrigin() {
  if (!portalOriginPromise) portalOriginPromise = invoke('berps:portal-origin');
  return portalOriginPromise;
}

async function decoratePortalPage() {
  const origin = await portalOrigin();
  if (!location.href.startsWith(origin)) return; // not a portal page
  injectSkin();
  runExtensions();
  addBackButton();
}

window.addEventListener('DOMContentLoaded', () => {
  const el = document.documentElement;
  el.classList.add('berps-desktop', `berps-platform-${process.platform}`);
  decoratePortalPage();
});

ipcRenderer.on('berps:navigated', () => decoratePortalPage());
