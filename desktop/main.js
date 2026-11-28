const { app, BrowserWindow, Menu, Notification, Tray, clipboard, dialog, globalShortcut, ipcMain, nativeImage, nativeTheme, net, safeStorage, shell, systemPreferences } = require('electron');
const fs = require('fs');
const os = require('os');
const path = require('path');

// Optional dependency — auto-update works once a publish target is configured.
let autoUpdater = null;
try { ({ autoUpdater } = require('electron-updater')); } catch (_) { /* not installed */ }

// BERPS_URL lets you point the app at a staging/local portal for development.
const PORTAL_ORIGIN = (process.env.BERPS_URL || 'https://berps.online').replace(/\/+$/, '');
const PORTAL_URL = `${PORTAL_ORIGIN}/page/staff`;
const API_BASE = `${PORTAL_ORIGIN}/api/mobile`;

const isMac = process.platform === 'darwin';
const isDev = !app.isPackaged;
const extensionsDir = path.join(__dirname, 'extensions');
const deskStateFile = () => path.join(app.getPath('userData'), 'window-state.json');
const portalStateFile = () => path.join(app.getPath('userData'), 'portal-window-state.json');
const settingsFile = () => path.join(app.getPath('userData'), 'settings.json');
const authFile = () => path.join(app.getPath('userData'), 'auth.json');

let deskWindow = null;   // the app — always local file://, never hosts web pages
let portalWindow = null; // the website — its own window with its own chrome
let tray = null;
let extensions = [];
let skinCss = '';
let isQuitting = false;  // portal hides on close instead of dying — except at quit

const trayState = { phase: 'unknown', canIn: false, canOut: false, unseen: 0 };

function isPortalUrl(url) {
  return String(url || '').startsWith(PORTAL_ORIGIN);
}

/* ------------------------------ window state ----------------------------- */

function loadBounds(file, fallback) {
  try {
    const s = JSON.parse(fs.readFileSync(file, 'utf8'));
    if (s && Number.isFinite(s.width) && Number.isFinite(s.height)) return s;
  } catch (_) { /* first run or corrupt state */ }
  return fallback;
}

function watchBounds(win, file) {
  let saveTimer = null;
  const save = () => {
    if (win.isDestroyed() || win.isMinimized()) return;
    try {
      const b = win.getNormalBounds();
      fs.writeFileSync(file, JSON.stringify({
        width: b.width, height: b.height, x: b.x, y: b.y,
        maximized: win.isMaximized(),
      }));
    } catch (_) { /* ignore */ }
  };
  const debounced = () => { clearTimeout(saveTimer); saveTimer = setTimeout(save, 400); };
  win.on('resize', debounced);
  win.on('move', debounced);
  win.on('close', save);
}

/* -------------------------------- settings ------------------------------- */

let settings = { theme: 'system', touchId: false };

function loadSettings() {
  try {
    const s = JSON.parse(fs.readFileSync(settingsFile(), 'utf8'));
    if (s && ['system', 'light', 'dark'].includes(s.theme)) settings.theme = s.theme;
    if (s && typeof s.touchId === 'boolean') settings.touchId = s.touchId;
  } catch (_) { /* defaults */ }
}

function saveSettings() {
  try { fs.writeFileSync(settingsFile(), JSON.stringify(settings)); } catch (_) { /* ignore */ }
}

// The Dock icon follows system appearance — dark tile variant in dark mode.
// On macOS 26+ the packaged app already gets that from the Icon Composer asset
// (Assets.car supplies light/dark/tinted variants and the system applies the
// icon shape), so the PNG swap only runs in dev and on older macOS. The PNGs
// must have the tile baked in — dock.setIcon() draws the image verbatim.
const systemThemedIcon = isMac && app.isPackaged && parseInt(os.release(), 10) >= 25;

function updateDockIcon(dark) {
  if (!isMac || !app.dock || !app.isReady() || systemThemedIcon) return;
  const p = path.join(__dirname, 'build', dark ? 'icon-dark.png' : 'icon-light.png');
  try { app.dock.setIcon(nativeImage.createFromPath(p)); } catch (_) { /* non-fatal */ }
}

function broadcastTheme() {
  const dark = nativeTheme.shouldUseDarkColors;
  updateDockIcon(dark);
  for (const w of [deskWindow, portalWindow]) {
    if (w && !w.isDestroyed()) w.webContents.send('berps:theme-changed', { dark });
  }
}

function setTheme(theme) {
  if (!['system', 'light', 'dark'].includes(theme)) return;
  settings.theme = theme;
  saveSettings();
  nativeTheme.themeSource = theme;
  buildMenu(); // refresh the radio checkmarks
}

nativeTheme.on('updated', broadcastTheme);

/* ------------------------------ auth storage ----------------------------- */
// The bearer token is encrypted with the OS keychain (safeStorage) before it
// touches disk. Plaintext fallback only if the OS can't encrypt.

let authCache = null; // decrypted once per launch; keychain access is slow

function readAuth() {
  if (authCache) return authCache;
  authCache = readAuthFromDisk();
  return authCache;
}

function readAuthFromDisk() {
  try {
    const raw = JSON.parse(fs.readFileSync(authFile(), 'utf8'));
    if (!raw || typeof raw !== 'object') return { token: null, user: null };
    let token = null;
    if (raw.token_enc && safeStorage.isEncryptionAvailable()) {
      token = safeStorage.decryptString(Buffer.from(raw.token_enc, 'base64'));
    } else if (raw.token_plain) {
      token = raw.token_plain;
    }
    return { token, user: raw.user || null };
  } catch (_) {
    return { token: null, user: null };
  }
}

function writeAuth(token, user) {
  authCache = { token: token || null, user: user || null };
  try {
    const rec = { user: user || null };
    if (token && safeStorage.isEncryptionAvailable()) {
      rec.token_enc = safeStorage.encryptString(token).toString('base64');
    } else if (token) {
      rec.token_plain = token;
    }
    fs.writeFileSync(authFile(), JSON.stringify(rec));
  } catch (err) {
    console.warn('[auth] could not persist session:', err.message);
  }
}

function clearAuth() {
  authCache = { token: null, user: null };
  try { fs.unlinkSync(authFile()); } catch (_) { /* no session to clear */ }
}

/* --------------------------------- API ----------------------------------- */
// All API traffic goes through the main process — the bearer token never
// enters the renderer, and there is no CORS to fight.

const API_TIMEOUT_MS = 20000;

async function apiFetchOnce(method, apiPath, body, withAuth) {
  const headers = { Accept: 'application/json' };
  if (body !== undefined) headers['Content-Type'] = 'application/json';
  if (withAuth) {
    const { token } = readAuth();
    if (token) headers.Authorization = `Bearer ${token}`;
  }

  const ctrl = new AbortController();
  const timer = setTimeout(() => ctrl.abort(), API_TIMEOUT_MS);
  try {
    const res = await net.fetch(`${API_BASE}${apiPath}`, {
      method,
      headers,
      body: body !== undefined ? JSON.stringify(body) : undefined,
      signal: ctrl.signal,
      cache: 'no-store',
    });
    const text = await res.text();
    let json = null;
    try { json = JSON.parse(text); } catch (_) { /* non-JSON body */ }
    return { status: res.status, json };
  } catch (err) {
    const msg = err && err.name === 'AbortError'
      ? 'The server took too long to respond.'
      : `Cannot reach the server. ${(err && err.message) || ''}`.trim();
    return { status: 0, json: { ok: false, message: msg } };
  } finally {
    clearTimeout(timer);
  }
}

async function apiFetch(method, apiPath, body, withAuth = true) {
  let res = await apiFetchOnce(method, apiPath, body, withAuth);
  // The server occasionally returns a transient 5xx / empty body. Reads are
  // safe to retry once; writes are not (they may have been applied).
  if (method === 'GET' && (res.status === 0 || res.status >= 500 || res.json === null)) {
    await new Promise((r) => setTimeout(r, 400));
    res = await apiFetchOnce(method, apiPath, body, withAuth);
  }
  return res;
}

/* ------------------------------- extensions ------------------------------ */
// Portal-page extensions (injected by preload when a portal page is open in
// the app). See extensions/README.md.

function globToRegex(glob) {
  const esc = glob.replace(/[.+^${}()|[\]\\]/g, '\\$&');
  return new RegExp('^' + esc.replace(/\*/g, '.*').replace(/\?/g, '.') + '$', 'i');
}

function loadExtensions() {
  extensions = [];
  let dirs = [];
  try { dirs = fs.readdirSync(extensionsDir, { withFileTypes: true }); } catch (_) { return; }

  for (const dir of dirs) {
    if (!dir.isDirectory()) continue;
    const dirPath = path.join(extensionsDir, dir.name);
    try {
      const manifest = JSON.parse(fs.readFileSync(path.join(dirPath, 'manifest.json'), 'utf8'));
      if (manifest.enabled === false) continue;

      const matches = (manifest.matches || [`{origin}/*`])
        .map((g) => globToRegex(g.replace(/\{origin\}/g, PORTAL_ORIGIN)));
      const read = (file) => {
        try { return fs.readFileSync(path.join(dirPath, file), 'utf8'); }
        catch (_) { console.warn(`[extensions] ${dir.name}: cannot read ${file}`); return ''; }
      };

      extensions.push({
        id: manifest.id || dir.name,
        name: manifest.name || dir.name,
        version: manifest.version || '0.0.0',
        css: (manifest.css || []).map(read).filter(Boolean),
        js: (manifest.js || []).map(read).filter(Boolean),
        matches,
      });
    } catch (err) {
      console.warn(`[extensions] skipping ${dir.name}: ${err.message}`);
    }
  }
  console.log(`[extensions] loaded: ${extensions.map((e) => e.id).join(', ') || 'none'}`);
}

function matchingExtensions(url) {
  return extensions
    .filter((ext) => ext.matches.some((re) => re.test(url)))
    .map(({ id, name, version, css, js }) => ({ id, name, version, css, js }));
}

/* --------------------------------- windows -------------------------------- */

const APP_INDEX = path.join(__dirname, 'app', 'index.html');

function baseWindowOpts(extra) {
  return {
    show: false,
    icon: path.join(__dirname, 'build', 'icon.png'),
    backgroundColor: '#0d1830',
    autoHideMenuBar: !isMac,
    // macOS: traffic lights float over the window; the apps leave room and
    // mark their header strips as drag regions.
    ...(isMac ? {
      titleBarStyle: 'hiddenInset',
      trafficLightPosition: { x: 16, y: 18 },
      vibrancy: 'under-window',
      visualEffectState: 'active',
    } : {}),
    webPreferences: {
      preload: path.join(__dirname, 'preload.js'),
      contextIsolation: true,
      nodeIntegration: false,
      spellcheck: true,
    },
    ...extra,
  };
}

function createDeskWindow() {
  const state = loadBounds(deskStateFile(), { width: 1360, height: 880 });

  deskWindow = new BrowserWindow(baseWindowOpts({
    width: state.width,
    height: state.height,
    x: state.x,
    y: state.y,
    minWidth: 460,
    minHeight: 480,
    title: 'BERPS Desk',
  }));

  if (state.maximized) deskWindow.maximize();
  deskWindow.once('ready-to-show', () => deskWindow.show());
  deskWindow.loadFile(APP_INDEX);

  const wc = deskWindow.webContents;

  // The Desk window never leaves the app shell. Portal links go to the portal
  // window; everything else goes to the default browser.
  wc.setWindowOpenHandler(({ url }) => {
    if (isPortalUrl(url)) openPortal(url);
    else if (/^https?:\/\//i.test(url)) shell.openExternal(url);
    return { action: 'deny' };
  });

  wc.on('will-navigate', (event, url) => {
    if (url.startsWith('file://')) return;
    event.preventDefault();
    if (isPortalUrl(url)) openPortal(url);
    else if (/^https?:\/\//i.test(url)) shell.openExternal(url);
  });

  wc.on('dom-ready', () => wc.send('berps:theme-changed', { dark: nativeTheme.shouldUseDarkColors }));

  deskWindow.on('closed', () => { deskWindow = null; });
  deskWindow.on('focus', () => {
    if (deskWindow && !deskWindow.isDestroyed()) deskWindow.flashFrame(false);
  });
  watchBounds(deskWindow, deskStateFile());
}

function ensureDeskWindow(onReady) {
  if (!deskWindow || deskWindow.isDestroyed()) {
    createDeskWindow();
    if (onReady) deskWindow.webContents.once('did-finish-load', onReady);
    return;
  }
  if (onReady) onReady();
}

function focusDeskWindow() {
  ensureDeskWindow(() => {
    if (deskWindow.isMinimized()) deskWindow.restore();
    deskWindow.focus();
  });
}

function gotoInApp(hash) {
  ensureDeskWindow(() => {
    deskWindow.webContents.send('berps:menu-goto', typeof hash === 'string' && hash ? hash : '#/home');
    if (deskWindow.isMinimized()) deskWindow.restore();
    deskWindow.focus();
  });
}

function sendMenuAction(action) {
  ensureDeskWindow(() => {
    deskWindow.webContents.send('berps:menu-action', action);
    if (deskWindow.isMinimized()) deskWindow.restore();
    deskWindow.focus();
  });
}

// Opens the app on a specific thing: { type: 'task'|'ticket'|'note'|'route', id }.
// Used by notification clicks and berps:// deep links.
function sendOpenTarget(target) {
  ensureDeskWindow(() => {
    const send = () => {
      deskWindow.webContents.send('berps:open-target', target);
      if (!deskWindow.isVisible()) deskWindow.show();
      if (deskWindow.isMinimized()) deskWindow.restore();
      deskWindow.focus();
    };
    // Deep links can land while the renderer is still booting — hold them
    // until app.js has a chance to register its listener.
    if (deskWindow.webContents.isLoading()) deskWindow.webContents.once('did-finish-load', send);
    else send();
  });
}

/* ------------------------------ global hotkey ---------------------------- */

// Ctrl+Alt+B summons or dismisses the Desk window from anywhere — the app
// equivalent of pressing ⌘Tab to it, without the tab.
const SUMMON_HOTKEY = 'Control+Alt+B';

function registerHotkey() {
  try {
    const ok = globalShortcut.register(SUMMON_HOTKEY, () => {
      if (!deskWindow || deskWindow.isDestroyed()) { focusDeskWindow(); return; }
      if (deskWindow.isVisible() && deskWindow.isFocused()) {
        deskWindow.hide();
        return;
      }
      if (!deskWindow.isVisible()) deskWindow.show();
      if (deskWindow.isMinimized()) deskWindow.restore();
      deskWindow.focus();
    });
    if (!ok) console.warn(`[hotkey] ${SUMMON_HOTKEY} is taken by another app — summon shortcut unavailable`);
  } catch (err) {
    console.warn('[hotkey]', err.message);
  }
}

/* -------------------------------- deep links ------------------------------ */
// berps://task/42 opens task 42; berps://notes or berps://open/notes navigate.
// The OS launches or foregrounds the app with the URL.

const DEEP_LINK_ROUTES = ['home', 'tasks', 'support', 'calendar', 'notes', 'reminders', 'attendance', 'goals', 'ranking', 'alerts', 'settings'];
const DEEP_LINK_ALIASES = { today: 'home', notifications: 'alerts' };

function parseDeepLink(url) {
  const m = String(url || '').match(/^berps:\/\/([^/?#]+)(?:\/([^/?#]+))?/i);
  if (!m) return null;
  const kind = m[1].toLowerCase();
  const id = m[2] ? decodeURIComponent(m[2]) : null;
  if (kind === 'open') {
    const r = DEEP_LINK_ALIASES[id] || id;
    return r && DEEP_LINK_ROUTES.includes(r) ? { type: 'route', id: r } : null;
  }
  if (kind === 'task' || kind === 'tasks') return id ? { type: 'task', id } : { type: 'route', id: 'tasks' };
  if (kind === 'ticket' || kind === 'issue' || kind === 'support') return id ? { type: 'ticket', id } : { type: 'route', id: 'support' };
  if (kind === 'note' || kind === 'notes') return id ? { type: 'note', id } : { type: 'route', id: 'notes' };
  const r = DEEP_LINK_ALIASES[kind] || kind;
  return DEEP_LINK_ROUTES.includes(r) ? { type: 'route', id: r } : null;
}

function handleDeepLink(url) {
  const target = parseDeepLink(url);
  if (target) sendOpenTarget(target);
}

function deepLinkFromArgv(argv) {
  return (argv || []).find((a) => /^berps:\/\//i.test(String(a))) || null;
}

function createPortalWindow() {
  const state = loadBounds(portalStateFile(), { width: 1280, height: 860 });

  portalWindow = new BrowserWindow(baseWindowOpts({
    width: state.width,
    height: state.height,
    x: state.x,
    y: state.y,
    minWidth: 700,
    minHeight: 480,
    title: 'BERPS Portal',
  }));

  if (state.maximized) portalWindow.maximize();
  portalWindow.once('ready-to-show', () => portalWindow.show());

  const wc = portalWindow.webContents;

  // New windows from portal pages reuse the portal window; foreign links go
  // out to the default browser.
  wc.setWindowOpenHandler(({ url }) => {
    if (isPortalUrl(url)) wc.loadURL(url);
    else if (/^https?:\/\//i.test(url)) shell.openExternal(url);
    return { action: 'deny' };
  });

  wc.on('will-navigate', (event, url) => {
    if (url.startsWith('file://') || isPortalUrl(url)) return;
    event.preventDefault();
    shell.openExternal(url);
  });

  const notifyNav = () => {
    if (portalWindow && !portalWindow.isDestroyed()) wc.send('berps:navigated', wc.getURL());
  };
  wc.on('did-navigate', notifyNav);
  wc.on('did-navigate-in-page', notifyNav);

  // Skin goes in before the page paints — dom-ready beats preload's IPC path.
  wc.on('dom-ready', () => {
    if (isPortalUrl(wc.getURL()) && skinCss) wc.insertCSS(skinCss);
    wc.send('berps:theme-changed', { dark: nativeTheme.shouldUseDarkColors });
  });

  wc.on('did-fail-load', (event, errorCode, description, url) => {
    if (errorCode === -3) return; // aborted navigation, ignore
    if (url && url.startsWith('file://')) return; // local pages always resolve
    wc.loadFile(path.join(__dirname, 'offline.html'), { query: { origin: PORTAL_ORIGIN, target: url || '' } });
  });

  wc.on('page-title-updated', (e) => e.preventDefault()); // keep "BERPS Portal"

  // Closing the portal hides it — like a satellite app, the web session stays
  // warm so ⌘⇧P reopens instantly with the page exactly where the user left it.
  portalWindow.on('close', (e) => {
    if (isQuitting) return;
    e.preventDefault();
    portalWindow.hide();
  });
  portalWindow.on('closed', () => { portalWindow = null; });
  watchBounds(portalWindow, portalStateFile());
}

async function openPortal(target) {
  const url = typeof target === 'string' && target.startsWith('/')
    ? `${PORTAL_ORIGIN}${target}`
    : (isPortalUrl(String(target || '')) ? String(target) : null);

  if (portalWindow && !portalWindow.isDestroyed()) {
    // Warm window: a specific target navigates, otherwise the user gets the
    // portal back exactly where they left it.
    if (url && portalWindow.webContents.getURL() !== url) portalWindow.loadURL(url);
    if (!portalWindow.isVisible()) portalWindow.show();
    if (portalWindow.isMinimized()) portalWindow.restore();
    portalWindow.focus();
    return;
  }

  createPortalWindow();
  try { await portalWindow.loadFile(path.join(__dirname, 'splash.html')); } catch (_) { /* non-fatal */ }
  portalWindow.loadURL(url || PORTAL_URL);
  if (portalWindow.isMinimized()) portalWindow.restore();
  portalWindow.focus();
}

/* ---------------------------------- tray --------------------------------- */

function rebuildTray() {
  if (!tray) return;
  const t = trayState;
  const status = t.phase === 'in' ? 'Timed in — clock running'
    : t.phase === 'out' ? 'Timed out'
    : t.phase === 'off' ? 'Not timed in yet'
    : 'Attendance status unknown';
  tray.setContextMenu(Menu.buildFromTemplate([
    { label: status, enabled: false },
    { type: 'separator' },
    { label: 'Time In', enabled: t.canIn, click: () => sendMenuAction('time-in') },
    { label: 'Time Out', enabled: t.canOut, click: () => sendMenuAction('time-out') },
    { type: 'separator' },
    { label: 'Open BERPS Desk', click: () => focusDeskWindow() },
    { label: 'Open Web Portal', click: () => openPortal() },
    { type: 'separator' },
    { label: t.unseen ? `${t.unseen} unread notification${t.unseen === 1 ? '' : 's'}` : 'No unread notifications', enabled: false },
    { type: 'separator' },
    { label: 'Quit BERPS Desk', click: () => app.quit() },
  ]));
  tray.setToolTip(`BERPS Desk${t.unseen ? ` — ${t.unseen} unread` : ''}`);
}

function createTray() {
  let img = nativeImage.createFromPath(path.join(__dirname, 'build', 'icon.png'));
  img = img.resize({ width: isMac ? 18 : 16, height: isMac ? 18 : 16 });
  if (isMac) img.setTemplateImage(true);
  tray = new Tray(img);
  tray.on('click', () => focusDeskWindow());
  rebuildTray();
}

/* --------------------------------- updates -------------------------------- */

function checkForUpdates(fromMenu) {
  if (!autoUpdater || isDev) {
    dialog.showMessageBox({
      type: 'info',
      title: 'Check for Updates',
      message: `BERPS Desk ${app.getVersion()}`,
      detail: autoUpdater
        ? 'Update checks run in packaged builds only.'
        : 'This build has no update feed configured.',
    });
    return;
  }
  autoUpdater.checkForUpdates().then((r) => {
    if (fromMenu && r && !r.updateInfo) {
      dialog.showMessageBox({ type: 'info', message: 'BERPS Desk is up to date.' });
    }
  }).catch((err) => {
    if (fromMenu) {
      dialog.showMessageBox({ type: 'error', message: 'Could not check for updates.', detail: err.message });
    }
  });
}

function wireUpdater() {
  if (!autoUpdater) return;
  autoUpdater.autoDownload = true;
  autoUpdater.on('update-downloaded', () => {
    dialog.showMessageBox({
      type: 'info',
      title: 'Update ready',
      message: 'A new version of BERPS Desk was downloaded.',
      detail: 'Restart now to apply the update?',
      buttons: ['Restart', 'Later'],
      defaultId: 1,
    }).then((r) => { if (r.response === 0) autoUpdater.quitAndInstall(); });
  });
}

/* ---------------------------------- menu --------------------------------- */

function buildMenu() {
  const goto = (label, accel, hash) => ({ label, accelerator: accel, click: () => gotoInApp(hash) });
  const act = (label, accel, action) => ({ label, accelerator: accel, click: () => sendMenuAction(action) });

  const template = [
    ...(isMac ? [{ role: 'appMenu' }] : []),
    {
      label: 'File',
      submenu: [
        act('New Item', 'CmdOrCtrl+N', 'new'),
        { type: 'separator' },
        {
          label: 'Print…',
          accelerator: 'CmdOrCtrl+P',
          click: () => {
            const w = BrowserWindow.getFocusedWindow() || deskWindow;
            if (w) w.webContents.print();
          },
        },
        { type: 'separator' },
        { label: 'Check for Updates…', click: () => checkForUpdates(true) },
        { type: 'separator' },
        isMac ? { role: 'close' } : { role: 'quit' },
      ],
    },
    { role: 'editMenu' },
    {
      label: 'Navigate',
      submenu: [
        goto('Today', 'CmdOrCtrl+1', '#/home'),
        goto('Tasks', 'CmdOrCtrl+2', '#/tasks'),
        goto('Support', 'CmdOrCtrl+3', '#/support'),
        goto('Calendar', 'CmdOrCtrl+4', '#/calendar'),
        goto('Notes', 'CmdOrCtrl+5', '#/notes'),
        goto('Reminders', 'CmdOrCtrl+6', '#/reminders'),
        goto('Attendance', 'CmdOrCtrl+7', '#/attendance'),
        goto('Goals', 'CmdOrCtrl+8', '#/goals'),
        goto('Ranking', 'CmdOrCtrl+9', '#/ranking'),
        goto('Notifications', 'CmdOrCtrl+0', '#/alerts'),
        goto('Settings', 'CmdOrCtrl+,', '#/settings'),
        { type: 'separator' },
        act('Command Palette…', 'CmdOrCtrl+K', 'palette'),
        { type: 'separator' },
        { label: 'Open Web Portal', accelerator: 'CmdOrCtrl+Shift+P', click: () => openPortal() },
        { label: 'Focus BERPS Desk', accelerator: 'CmdOrCtrl+Shift+D', click: () => focusDeskWindow() },
        ...(isDev ? [{ type: 'separator' }, { role: 'reload' }, { role: 'forceReload' }, { role: 'toggleDevTools' }] : []),
      ],
    },
    {
      label: 'View',
      submenu: [
        act('Toggle Sidebar', 'CmdOrCtrl+Alt+S', 'toggle-sidebar'),
        act('Refresh Data', 'CmdOrCtrl+R', 'refresh'),
        { type: 'separator' },
        {
          label: 'Appearance',
          submenu: [
            { label: 'System', type: 'radio', checked: settings.theme === 'system', click: () => setTheme('system') },
            { label: 'Light', type: 'radio', checked: settings.theme === 'light', click: () => setTheme('light') },
            { label: 'Dark', type: 'radio', checked: settings.theme === 'dark', click: () => setTheme('dark') },
          ],
        },
        { type: 'separator' },
        act('Mark All Read', 'CmdOrCtrl+Shift+0', 'mark-read'),
        { type: 'separator' },
        { role: 'togglefullscreen' },
      ],
    },
    { role: 'windowMenu' },
  ];
  Menu.setApplicationMenu(Menu.buildFromTemplate(template));
}

/* -------------------------------- downloads ------------------------------- */
// Portal exports (reports, payslips) get a real save dialog instead of
// silently dropping into ~/Downloads.

function watchDownloads() {
  const ses = require('electron').session.defaultSession;
  ses.on('will-download', (event, item, wc) => {
    const win = BrowserWindow.fromWebContents(wc) || deskWindow;
    const savePath = dialog.showSaveDialogSync(win, {
      title: 'Save file',
      defaultPath: item.getFilename(),
    });
    if (savePath) item.setSavePath(savePath);
    else item.cancel();
  });
}

/* ----------------------------------- IPC ---------------------------------- */

// Auth + API calls are only honoured from the app's own pages (file://) —
// portal pages and anything remote get a reduced bridge.
function isDeskSender(event) {
  try {
    const url = (event.senderFrame && event.senderFrame.url) || event.sender.getURL() || '';
    return url.startsWith('file://');
  } catch (_) {
    return false;
  }
}

const DENIED = { status: 0, json: { ok: false, message: 'Not available outside BERPS Desk.' } };
const deskOnly = (fn) => (event, ...args) => (isDeskSender(event) ? fn(event, ...args) : DENIED);

ipcMain.handle('berps:portal-origin', () => PORTAL_ORIGIN);

ipcMain.handle('berps:window-role', (event) => (
  portalWindow && !portalWindow.isDestroyed() && event.sender === portalWindow.webContents ? 'portal' : 'desk'
));

ipcMain.handle('berps:theme', () => ({
  dark: nativeTheme.shouldUseDarkColors,
  source: nativeTheme.themeSource,
}));

ipcMain.handle('berps:theme-set', (event, theme) => setTheme(String(theme || '')));

ipcMain.handle('berps:auth-state', deskOnly(() => {
  const { token, user } = readAuth();
  return { loggedIn: !!token, user, server: PORTAL_ORIGIN };
}));

ipcMain.handle('berps:auth-login', deskOnly(async (event, { username, password }) => {
  const res = await apiFetch('POST', '/auth/login', { username, password }, false);
  const body = res.json || {};
  if (res.status === 200 && body.ok && body.token) {
    writeAuth(body.token, body.user || null);
    return { ok: true, user: body.user || null };
  }
  return { ok: false, status: res.status, message: body.message || 'Login failed.' };
}));

ipcMain.handle('berps:auth-me', deskOnly(async () => {
  const { token, user } = readAuth();
  if (!token) return { ok: false, status: 401, user: null };
  const res = await apiFetch('GET', '/auth/me');
  if (res.status === 401 || res.status === 403) {
    clearAuth();
    return { ok: false, status: res.status, user: null };
  }
  const body = res.json || {};
  if (body.ok && body.user) {
    writeAuth(token, body.user);
    return { ok: true, user: body.user };
  }
  return { ok: false, status: res.status, user };
}));

ipcMain.handle('berps:auth-logout', deskOnly(async () => {
  await apiFetch('POST', '/auth/logout').catch(() => {});
  clearAuth();
  return { ok: true };
}));

ipcMain.handle('berps:api', deskOnly(async (event, { method, path: apiPath, body }) => {
  const res = await apiFetch(String(method || 'GET').toUpperCase(), String(apiPath || ''), body);
  if (res.status === 401 || res.status === 403) clearAuth();
  return res;
}));

ipcMain.handle('berps:portal-open', (event, target) => openPortal(target));

ipcMain.handle('berps:app-open', (event, hash) => gotoInApp(hash));

ipcMain.handle('berps:open-external', (event, url) => {
  const u = String(url || '');
  if (/^https?:\/\//i.test(u)) shell.openExternal(u);
});

ipcMain.handle('berps:skin', () => skinCss);

ipcMain.handle('berps:extensions', (event, url) => matchingExtensions(String(url || '')));

ipcMain.handle('berps:context-menu', (event, items) => new Promise((resolve) => {
  const win = BrowserWindow.fromWebContents(event.sender) || deskWindow;
  const template = (Array.isArray(items) ? items : []).map((it) => {
    if (!it || it.sep) return { type: 'separator' };
    if (it.role) return { role: it.role, enabled: it.enabled !== false };
    return {
      label: String(it.label || ''),
      enabled: it.enabled !== false,
      click: () => {
        if (it.copyText != null) clipboard.writeText(String(it.copyText));
        resolve(it.id != null ? it.id : null);
      },
    };
  });
  const menu = Menu.buildFromTemplate(template);
  menu.popup({ window: win, callback: () => resolve(null) });
}));

ipcMain.handle('berps:tray-status', deskOnly((event, s) => {
  const t = s || {};
  trayState.phase = ['in', 'out', 'off', 'unknown'].includes(t.phase) ? t.phase : 'unknown';
  trayState.canIn = !!t.canIn;
  trayState.canOut = !!t.canOut;
  rebuildTray();
}));

ipcMain.handle('berps:notify', (event, { title, body, hash, target }) => {
  if (!Notification.isSupported()) return;
  const n = new Notification({ title: String(title), body: String(body || '') });
  n.on('click', () => {
    if (target && typeof target === 'object' && target.type) sendOpenTarget(target);
    else gotoInApp(typeof hash === 'string' && hash ? hash : '#/alerts');
  });
  n.show();
  if (deskWindow && !deskWindow.isDestroyed() && !deskWindow.isFocused()) {
    deskWindow.flashFrame(true); // cleared on focus
    if (isMac && app.dock) app.dock.bounce('informational');
  }
});

/* --------------------------- settings + security -------------------------- */

function touchIdSupported() {
  return isMac && systemPreferences.canPromptTouchID();
}

ipcMain.handle('berps:settings', deskOnly(() => ({
  theme: settings.theme,
  touchId: settings.touchId,
  touchIdSupported: touchIdSupported(),
  openAtLogin: app.getLoginItemSettings().openAtLogin,
  summonHotkey: 'Ctrl+Alt+B',
})));

ipcMain.handle('berps:settings-set', deskOnly((event, { key, value }) => {
  if (key === 'touchId') { settings.touchId = !!value; saveSettings(); return { ok: true }; }
  if (key === 'theme') { setTheme(String(value || '')); return { ok: true }; }
  return { ok: false };
}));

ipcMain.handle('berps:login-item-set', deskOnly((event, open) => {
  try { app.setLoginItemSettings({ openAtLogin: !!open }); } catch (_) { /* dev builds */ }
  return { openAtLogin: app.getLoginItemSettings().openAtLogin };
}));

// Gates session entry behind Touch ID when the user enabled it. Failing or
// cancelling just lands the user on the normal sign-in form — the token never
// leaves this process either way.
ipcMain.handle('berps:unlock', deskOnly(async () => {
  if (!touchIdSupported()) return { ok: true, supported: false };
  try {
    await systemPreferences.promptTouchID('Unlock BERPS Desk');
    return { ok: true, supported: true };
  } catch (err) {
    return { ok: false, supported: true, message: (err && err.message) || 'Unlock cancelled.' };
  }
}));

ipcMain.handle('berps:badge', deskOnly((event, count) => {
  const n = +count || 0;
  if (app.dock) app.dock.setBadge(n > 0 ? String(n) : '');
  trayState.unseen = n;
  rebuildTray();
}));

/* --------------------------------- lifecycle ------------------------------ */

app.setAppUserModelId('com.berps.staff');

// berps://task/42 style links open the app on that item. In dev the scheme is
// registered against the Electron binary so links resolve while running it.
if (isDev) app.setAsDefaultProtocolClient('berps', process.execPath, [path.resolve(process.argv[1])]);
else app.setAsDefaultProtocolClient('berps');

const gotLock = app.requestSingleInstanceLock();
if (!gotLock) {
  app.quit();
} else {
  app.on('second-instance', (event, argv) => {
    // Windows/Linux deep links arrive as an argument on a second launch.
    const link = deepLinkFromArgv(argv);
    if (link) handleDeepLink(link);
    else focusDeskWindow();
  });

  app.on('open-url', (event, url) => { // macOS deep links
    event.preventDefault();
    handleDeepLink(url);
  });

  app.on('before-quit', () => { isQuitting = true; });
  app.on('will-quit', () => globalShortcut.unregisterAll());

  app.whenReady().then(() => {
    loadSettings();
    nativeTheme.themeSource = settings.theme;
    updateDockIcon(nativeTheme.shouldUseDarkColors);
    loadExtensions();
    try { skinCss = fs.readFileSync(path.join(__dirname, 'skin.css'), 'utf8'); } catch (_) { skinCss = ''; }
    buildMenu();
    createTray();
    createDeskWindow();
    watchDownloads();
    wireUpdater();
    registerHotkey();
    if (autoUpdater && !isDev) autoUpdater.checkForUpdates().catch(() => {});
    // A berps:// link that launched this instance (Windows/Linux path).
    const launchLink = deepLinkFromArgv(process.argv);
    if (launchLink) handleDeepLink(launchLink);
    app.on('activate', () => {
      if (BrowserWindow.getAllWindows().length === 0) createDeskWindow();
      else focusDeskWindow();
    });
  });
}

app.on('window-all-closed', () => {
  if (!isMac) app.quit();
});
