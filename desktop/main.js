const { app, BrowserWindow, Menu, Notification, ipcMain, net, safeStorage, shell } = require('electron');
const fs = require('fs');
const path = require('path');

// BERPS_URL lets you point the app at a staging/local portal for development.
const PORTAL_ORIGIN = (process.env.BERPS_URL || 'https://berps.online').replace(/\/+$/, '');
const PORTAL_URL = `${PORTAL_ORIGIN}/page/staff`;
const API_BASE = `${PORTAL_ORIGIN}/api/mobile`;

const isMac = process.platform === 'darwin';
const extensionsDir = path.join(__dirname, 'extensions');
const stateFile = () => path.join(app.getPath('userData'), 'window-state.json');
const authFile = () => path.join(app.getPath('userData'), 'auth.json');

let mainWindow = null;
let extensions = [];

function isPortalUrl(url) {
  return url.startsWith(PORTAL_ORIGIN);
}

/* ------------------------------ window state ----------------------------- */

function loadWindowState() {
  try {
    const s = JSON.parse(fs.readFileSync(stateFile(), 'utf8'));
    if (s && Number.isFinite(s.width) && Number.isFinite(s.height)) return s;
  } catch (_) { /* first run or corrupt state */ }
  return { width: 1360, height: 880 };
}

function saveWindowState() {
  if (!mainWindow || mainWindow.isDestroyed() || mainWindow.isMinimized()) return;
  try {
    const b = mainWindow.getNormalBounds();
    fs.writeFileSync(stateFile(), JSON.stringify({
      width: b.width, height: b.height, x: b.x, y: b.y,
      maximized: mainWindow.isMaximized(),
    }));
  } catch (_) { /* ignore */ }
}

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

/* ---------------------------------- menu --------------------------------- */

const APP_INDEX = path.join(__dirname, 'app', 'index.html');

function isAppPage() {
  if (!mainWindow || mainWindow.isDestroyed()) return false;
  const url = mainWindow.webContents.getURL();
  return url.startsWith('file://') && url.includes('/app/index.html');
}

function openApp(hash) {
  if (!mainWindow || mainWindow.isDestroyed()) return;
  const h = typeof hash === 'string' && hash.startsWith('#') ? hash.slice(1) : undefined;
  mainWindow.loadFile(APP_INDEX, h ? { hash: h } : undefined);
}

async function openPortal(target) {
  if (!mainWindow || mainWindow.isDestroyed()) return;
  const url = typeof target === 'string' && target.startsWith('/')
    ? `${PORTAL_ORIGIN}${target}`
    : (isPortalUrl(String(target || '')) ? String(target) : PORTAL_URL);
  await mainWindow.loadFile(path.join(__dirname, 'splash.html'));
  mainWindow.loadURL(url);
}

function gotoInApp(hash) {
  if (!mainWindow || mainWindow.isDestroyed()) return;
  if (isAppPage()) mainWindow.webContents.send('berps:menu-goto', hash);
  else openApp(hash); // portal / offline page: switch back to the app first
  if (mainWindow.isMinimized()) mainWindow.restore();
  mainWindow.focus();
}

function buildMenu() {
  const goto = (label, accel, hash) => ({ label, accelerator: accel, click: () => gotoInApp(hash) });

  const template = [
    ...(isMac ? [{ role: 'appMenu' }] : []),
    { role: 'fileMenu' },
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
        { label: 'Open Web Portal', accelerator: 'CmdOrCtrl+Shift+P', click: () => openPortal() },
        { label: 'Back to BERPS Desk', accelerator: 'CmdOrCtrl+Shift+D', click: () => openApp() },
        { type: 'separator' },
        { role: 'reload' },
        { role: 'forceReload' },
      ],
    },
    {
      label: 'View',
      submenu: [
        { role: 'resetZoom' }, { role: 'zoomIn' }, { role: 'zoomOut' },
        { type: 'separator' },
        { role: 'togglefullscreen' },
        { role: 'toggleDevTools' },
      ],
    },
    { role: 'windowMenu' },
  ];
  Menu.setApplicationMenu(Menu.buildFromTemplate(template));
}

/* --------------------------------- window --------------------------------- */

function createWindow() {
  const state = loadWindowState();

  mainWindow = new BrowserWindow({
    width: state.width,
    height: state.height,
    x: state.x,
    y: state.y,
    minWidth: 460,
    minHeight: 480,
    show: false,
    title: 'BERPS Desk',
    icon: path.join(__dirname, 'build', 'icon.png'),
    backgroundColor: '#0d1830',
    autoHideMenuBar: !isMac,
    // macOS: traffic lights float over the app rail; app.css leaves room.
    ...(isMac
      ? { titleBarStyle: 'hiddenInset', trafficLightPosition: { x: 16, y: 18 } }
      : {}),
    webPreferences: {
      preload: path.join(__dirname, 'preload.js'),
      contextIsolation: true,
      nodeIntegration: false,
      spellcheck: false,
    },
  });

  if (state.maximized) mainWindow.maximize();
  mainWindow.once('ready-to-show', () => mainWindow.show());
  mainWindow.loadFile(APP_INDEX);

  // Links inside the portal open in-app; everything else goes to the default browser
  mainWindow.webContents.setWindowOpenHandler(({ url }) => {
    if (isPortalUrl(url)) return { action: 'allow' };
    shell.openExternal(url);
    return { action: 'deny' };
  });

  mainWindow.webContents.on('will-navigate', (event, url) => {
    if (url.startsWith('file://')) return;
    if (!isPortalUrl(url)) {
      event.preventDefault();
      shell.openExternal(url);
    }
  });

  const notifyNav = () => {
    if (mainWindow && !mainWindow.isDestroyed()) {
      mainWindow.webContents.send('berps:navigated', mainWindow.webContents.getURL());
    }
  };
  mainWindow.webContents.on('did-navigate', notifyNav);
  mainWindow.webContents.on('did-navigate-in-page', notifyNav);

  mainWindow.webContents.on('did-fail-load', (event, errorCode, description, url) => {
    if (errorCode === -3) return; // aborted navigation, ignore
    if (url && url.startsWith('file://')) return; // local pages always resolve
    mainWindow.loadFile(path.join(__dirname, 'offline.html'), { query: { origin: PORTAL_ORIGIN, target: url || '' } });
  });

  mainWindow.on('closed', () => { mainWindow = null; });
  let saveTimer = null;
  mainWindow.on('resize', () => { clearTimeout(saveTimer); saveTimer = setTimeout(saveWindowState, 400); });
  mainWindow.on('move', () => { clearTimeout(saveTimer); saveTimer = setTimeout(saveWindowState, 400); });
  mainWindow.on('close', saveWindowState);
}

/* ----------------------------------- IPC ---------------------------------- */

ipcMain.handle('berps:portal-origin', () => PORTAL_ORIGIN);

ipcMain.handle('berps:auth-state', () => {
  const { token, user } = readAuth();
  return { loggedIn: !!token, user, server: PORTAL_ORIGIN };
});

ipcMain.handle('berps:auth-login', async (event, { username, password }) => {
  const res = await apiFetch('POST', '/auth/login', { username, password }, false);
  const body = res.json || {};
  if (res.status === 200 && body.ok && body.token) {
    writeAuth(body.token, body.user || null);
    return { ok: true, user: body.user || null };
  }
  return { ok: false, status: res.status, message: body.message || 'Login failed.' };
});

ipcMain.handle('berps:auth-me', async () => {
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
});

ipcMain.handle('berps:auth-logout', async () => {
  await apiFetch('POST', '/auth/logout').catch(() => {});
  clearAuth();
  return { ok: true };
});

ipcMain.handle('berps:api', async (event, { method, path: apiPath, body }) => {
  const res = await apiFetch(String(method || 'GET').toUpperCase(), String(apiPath || ''), body);
  if (res.status === 401 || res.status === 403) clearAuth();
  return res;
});

ipcMain.handle('berps:portal-open', (event, target) => openPortal(target));

ipcMain.handle('berps:app-open', (event, hash) => openApp(hash));

ipcMain.handle('berps:open-external', (event, url) => {
  const u = String(url || '');
  if (/^https?:\/\//i.test(u)) shell.openExternal(u);
});

ipcMain.handle('berps:skin', () => {
  try { return fs.readFileSync(path.join(__dirname, 'skin.css'), 'utf8'); }
  catch (_) { return ''; }
});

ipcMain.handle('berps:extensions', (event, url) => matchingExtensions(String(url || '')));

ipcMain.handle('berps:notify', (event, { title, body, hash }) => {
  if (!Notification.isSupported()) return;
  const n = new Notification({ title: String(title), body: String(body || '') });
  n.on('click', () => gotoInApp(typeof hash === 'string' && hash ? hash : '#/alerts'));
  n.show();
});

ipcMain.handle('berps:badge', (event, count) => {
  if (app.dock) app.dock.setBadge(count > 0 ? String(count) : '');
});

/* --------------------------------- lifecycle ------------------------------ */

const gotLock = app.requestSingleInstanceLock();
if (!gotLock) {
  app.quit();
} else {
  app.on('second-instance', () => {
    if (mainWindow) {
      if (mainWindow.isMinimized()) mainWindow.restore();
      mainWindow.focus();
    }
  });

  app.whenReady().then(() => {
    loadExtensions();
    buildMenu();
    createWindow();
    app.on('activate', () => {
      if (BrowserWindow.getAllWindows().length === 0) createWindow();
    });
  });
}

app.on('window-all-closed', () => {
  if (!isMac) app.quit();
});
