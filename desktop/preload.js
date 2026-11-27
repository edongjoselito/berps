const { contextBridge, ipcRenderer, webFrame } = require('electron');

const invoke = (channel, ...args) => ipcRenderer.invoke(channel, ...args);

const api = {
  isDesktop: true,
  platform: process.platform,
  version: '2.1.0',

  // Session + API (used by the native app UI in app/index.html).
  // The main process only honours these from file:// pages — portal pages
  // get the shell APIs below but not the authenticated API surface.
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
  notify: (title, body, hash, target) => invoke('berps:notify', { title, body, hash, target }),
  setBadge: (count) => invoke('berps:badge', count),
  trayStatus: (s) => invoke('berps:tray-status', s),
  contextMenu: (items) => invoke('berps:context-menu', items),
  theme: () => invoke('berps:theme'),
  setTheme: (t) => invoke('berps:theme-set', t),

  // desktop settings + security
  settings: () => invoke('berps:settings'),
  setSetting: (key, value) => invoke('berps:settings-set', { key, value }),
  setLoginItem: (open) => invoke('berps:login-item-set', open),
  unlock: () => invoke('berps:unlock'),

  onNavigate: (cb) => ipcRenderer.on('berps:navigated', (_e, url) => cb(url)),
  onMenuGoto: (cb) => ipcRenderer.on('berps:menu-goto', (_e, hash) => cb(hash)),
  onMenuAction: (cb) => ipcRenderer.on('berps:menu-action', (_e, action) => cb(action)),
  onOpenTarget: (cb) => ipcRenderer.on('berps:open-target', (_e, target) => cb(target)),
};

contextBridge.exposeInMainWorld('berps', api);
try { window.berps = api; } catch (_) { /* non-fatal */ }

/* ---------------------------------------------------------------------------
 * Theme — the main process owns nativeTheme; preload mirrors it onto <html>
 * as .berps-dark so both the app and the skinned portal can react.
 * ------------------------------------------------------------------------- */

let currentDark = false;
function applyTheme() {
  if (document.documentElement) {
    document.documentElement.classList.toggle('berps-dark', currentDark);
  }
}
invoke('berps:theme').then((t) => { currentDark = !!(t && t.dark); applyTheme(); });
ipcRenderer.on('berps:theme-changed', (_e, t) => { currentDark = !!(t && t.dark); applyTheme(); });

/* ---------------------------------------------------------------------------
 * Portal pages get the desktop skin + matching extensions injected. Local
 * app pages (file://) skip straight past this.
 * ------------------------------------------------------------------------- */

const skinnedDocs = new WeakSet();
const extensionRuns = new WeakMap();
const EXTENSION_WORLD = 745; // isolated world id for extension scripts

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
        // Isolated world: extensions get the DOM but not the page's JS globals.
        await webFrame.executeJavaScriptInIsolatedWorld(EXTENSION_WORLD, [{ code: `(function(){\n${js}\n})();` }]);
        ran.add(key);
      } catch (err) {
        console.warn(`[berps] ${ext.id} js:`, err);
      }
    }
  }
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
}

window.addEventListener('DOMContentLoaded', () => {
  const el = document.documentElement;
  el.classList.add('berps-desktop', `berps-platform-${process.platform}`);
  applyTheme();
  decoratePortalPage();
});

ipcRenderer.on('berps:navigated', () => decoratePortalPage());
