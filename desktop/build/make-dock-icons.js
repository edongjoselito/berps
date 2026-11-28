/* Generates the Dock icon PNGs (build/icon-light.png, build/icon-dark.png):
 * the BERPS glyph on a squircle tile, used as the runtime Dock icon swap.
 * The tile is baked into the image because app.dock.setIcon() draws the image
 * verbatim — macOS applies no icon mask to it, unlike the bundle icon.
 * The source icon's opaque white background is removed via a border flood-fill
 * so it composites cleanly on each tile.
 * Run: ../node_modules/.bin/electron make-dock-icons.js
 */
const { app, BrowserWindow } = require('electron');
const fs = require('fs');
const path = require('path');

const SRC = path.join(__dirname, 'icon.png');
const VARIANTS = [
  { out: 'icon-light.png', stops: ['#ffffff', '#dfe3ea'], stroke: 'rgba(15,23,42,0.12)' },
  { out: 'icon-dark.png', stops: ['#1e3060', '#0d1830'], stroke: 'rgba(255,255,255,0.08)' },
];

app.whenReady().then(async () => {
  const win = new BrowserWindow({
    width: 1024, height: 1024, show: false, transparent: true,
    webPreferences: { offscreen: true },
  });

  const iconData = 'data:image/png;base64,' + fs.readFileSync(SRC).toString('base64');

  await win.loadURL('data:text/html,' + encodeURIComponent(`<!doctype html>
<canvas id="c" width="1024" height="1024"></canvas>
<script>
  const c = document.getElementById('c');
  const ctx = c.getContext('2d');
  let glyph = null;
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
    glyph = oc;
    window.__ready = true;
  };
  img.src = ${JSON.stringify(iconData)};

  // 2. Tile: ~82% of canvas, corner radius ~22.5%; glyph ~64% of canvas,
  //    nudged down slightly. Returns the PNG as a data URL.
  window.render = (spec) => {
    ctx.clearRect(0, 0, 1024, 1024);
    const tile = 840, tx = (1024 - tile) / 2, r = 190;
    ctx.beginPath();
    ctx.roundRect(tx, tx, tile, tile, r);
    const g = ctx.createLinearGradient(0, tx, 0, tx + tile);
    g.addColorStop(0, spec.stops[0]);
    g.addColorStop(1, spec.stops[1]);
    ctx.fillStyle = g;
    ctx.fill();
    ctx.lineWidth = 2;
    ctx.strokeStyle = spec.stroke;
    ctx.stroke();
    const s = 660, ix = (1024 - s) / 2, iy = (1024 - s) / 2 + 14;
    ctx.drawImage(glyph, ix, iy, s, s);
    return c.toDataURL('image/png');
  };
</script>`));

  while (!(await win.webContents.executeJavaScript('!!window.__ready'))) {
    await new Promise((r) => setTimeout(r, 50));
  }

  for (const v of VARIANTS) {
    const dataUrl = await win.webContents.executeJavaScript(`window.render(${JSON.stringify(v)})`);
    const out = path.join(__dirname, v.out);
    fs.writeFileSync(out, Buffer.from(dataUrl.split(',')[1], 'base64'));
    console.log('wrote', out);
  }
  app.quit();
});
