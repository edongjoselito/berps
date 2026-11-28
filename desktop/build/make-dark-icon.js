/* Generates build/icon-dark.png: the BERPS glyph on a navy tile, used as the
 * dark-mode variant of the app icon (Dock runtime swap + .icon bundle layer).
 * The source icon's opaque white background is removed via a border flood-fill
 * so it composites cleanly on the tile.
 * Run: ../node_modules/.bin/electron make-dark-icon.js
 */
const { app, BrowserWindow } = require('electron');
const fs = require('fs');
const path = require('path');

const SRC = path.join(__dirname, 'icon.png');
const OUT = path.join(__dirname, 'icon-dark.png');

app.whenReady().then(async () => {
  const win = new BrowserWindow({
    width: 1024, height: 1024, show: false, transparent: true,
    webPreferences: { offscreen: true },
  });

  const iconData = 'data:image/png;base64,' + fs.readFileSync(SRC).toString('base64');

  await win.loadURL('data:text/html,' + encodeURIComponent(`<!doctype html>
<style>html,body{margin:0;padding:0;background:transparent}canvas{display:block}</style>
<canvas id="c" width="1024" height="1024"></canvas>
<script>
  const c = document.getElementById('c');
  const ctx = c.getContext('2d');
  const img = new Image();
  img.onload = () => {
    // 1. Draw the source icon offscreen and strip its white background:
    //    flood-fill from the border over near-white pixels -> alpha 0.
    const oc = document.createElement('canvas');
    oc.width = oc.height = 1024;
    const octx = oc.getContext('2d');
    octx.drawImage(img, 0, 0);
    const id = octx.getImageData(0, 0, 1024, 1024);
    const d = id.data;
    const W = 1024, H = 1024;
    const isWhite = (i) => d[i] >= 238 && d[i+1] >= 238 && d[i+2] >= 238;
    const stack = [];
    const pushIfWhite = (p) => { if (d[p*4+3] !== 0 && isWhite(p*4)) { d[p*4+3] = 0; stack.push(p); } };
    for (let x = 0; x < W; x++) { pushIfWhite(x); pushIfWhite((H-1)*W + x); }
    for (let y = 0; y < H; y++) { pushIfWhite(y*W); pushIfWhite(y*W + W-1); }
    while (stack.length) {
      const p = stack.pop();
      const x = p % W, y = (p / W) | 0;
      if (x > 0) pushIfWhite(p - 1);
      if (x < W-1) pushIfWhite(p + 1);
      if (y > 0) pushIfWhite(p - W);
      if (y < H-1) pushIfWhite(p + W);
    }
    octx.putImageData(id, 0, 0);

    // 2. Navy squircle tile: ~82% of canvas, corner radius ~22.5%.
    const tile = 840, tx = (1024 - tile) / 2, r = 190;
    ctx.beginPath();
    ctx.roundRect(tx, tx, tile, tile, r);
    const g = ctx.createLinearGradient(0, tx, 0, tx + tile);
    g.addColorStop(0, '#1e3060');
    g.addColorStop(1, '#0d1830');
    ctx.fillStyle = g;
    ctx.fill();

    // 3. Glyph ~64% of canvas, nudged down slightly.
    const s = 660, ix = (1024 - s) / 2, iy = (1024 - s) / 2 + 14;
    ctx.drawImage(oc, ix, iy, s, s);
    window.__done = true;
  };
  img.src = ${JSON.stringify(iconData)};
</script>`));

  await new Promise((resolve) => {
    const t = setInterval(async () => {
      const done = await win.webContents.executeJavaScript('!!window.__done');
      if (done) { clearInterval(t); resolve(); }
    }, 50);
  });

  const shot = await win.webContents.capturePage();
  fs.writeFileSync(OUT, shot.toPNG());
  console.log('wrote', OUT);
  app.quit();
});
