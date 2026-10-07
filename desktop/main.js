const { app, BrowserWindow, shell, dialog } = require('electron');
const path = require('path');

const PORTAL_URL = 'https://berps.online/page/staff';
const PORTAL_ORIGIN = 'https://berps.online';

let mainWindow = null;

function isPortalUrl(url) {
  return url.startsWith(PORTAL_ORIGIN);
}

function createWindow() {
  mainWindow = new BrowserWindow({
    width: 1280,
    height: 840,
    minWidth: 1024,
    minHeight: 640,
    title: 'BERPS Staff',
    icon: path.join(__dirname, 'build', 'icon.png'),
    webPreferences: {
      preload: path.join(__dirname, 'preload.js'),
      contextIsolation: true,
      nodeIntegration: false,
    },
  });

  // Links inside the portal open in-app; everything else goes to the default browser
  mainWindow.webContents.setWindowOpenHandler(({ url }) => {
    if (isPortalUrl(url)) return { action: 'allow' };
    shell.openExternal(url);
    return { action: 'deny' };
  });

  mainWindow.webContents.on('will-navigate', (event, url) => {
    if (!isPortalUrl(url)) {
      event.preventDefault();
      shell.openExternal(url);
    }
  });

  mainWindow.webContents.on('did-fail-load', (event, errorCode) => {
    if (errorCode === -3) return; // aborted navigation, ignore
    mainWindow.loadFile('offline.html');
  });

  mainWindow.on('closed', () => {
    mainWindow = null;
  });

  mainWindow.loadURL(PORTAL_URL);
}

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
    createWindow();
    app.on('activate', () => {
      if (BrowserWindow.getAllWindows().length === 0) createWindow();
    });
  });
}

app.on('window-all-closed', () => {
  if (process.platform !== 'darwin') app.quit();
});
