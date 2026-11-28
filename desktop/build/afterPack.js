/* electron-builder afterPack hook — injects the Icon Composer app icon.
 *
 * Compiles build/BERPS.icon (light/dark/tinted variants) into Assets.car and
 * points CFBundleIconName at it, so the Finder/Launchpad/Dock icon follows the
 * system appearance on macOS 26+. icon.icns stays as the fallback for older
 * systems. Requires Xcode 26+ (actool).
 */
const { execFileSync } = require('child_process');
const fs = require('fs');
const os = require('os');
const path = require('path');

const ICON_NAME = 'BERPS';

exports.default = async function afterPack(context) {
  if (context.electronPlatformName !== 'darwin') return;

  const iconBundle = path.join(context.packager.projectDir, 'build', 'BERPS.icon');
  const out = fs.mkdtempSync(path.join(os.tmpdir(), 'berps-icon-'));

  execFileSync('actool', [
    iconBundle,
    '--compile', out,
    '--output-format', 'human-readable-text',
    '--notices', '--warnings',
    '--output-partial-info-plist', path.join(out, 'info.plist'),
    '--app-icon', ICON_NAME,
    '--include-all-app-icons',
    '--enable-on-demand-resources', 'NO',
    '--development-region', 'en',
    '--target-device', 'mac',
    '--minimum-deployment-target', '26.0',
    '--platform', 'macosx',
  ], { stdio: 'inherit' });

  const car = path.join(out, 'Assets.car');
  const icns = path.join(out, `${ICON_NAME}.icns`);
  const appDir = path.join(context.appOutDir, `${context.packager.appInfo.productFilename}.app`, 'Contents');
  const resources = path.join(appDir, 'Resources');
  fs.copyFileSync(car, path.join(resources, 'Assets.car'));
  if (fs.existsSync(icns)) {
    fs.copyFileSync(icns, path.join(resources, `${ICON_NAME}.icns`));
    fs.rmSync(path.join(resources, 'icon.icns'), { force: true });
  }

  const plistPath = path.join(appDir, 'Info.plist');
  let plist = fs.readFileSync(plistPath, 'utf8');
  const keys =
    `<key>CFBundleIconFile</key><string>${ICON_NAME}</string>` +
    `<key>CFBundleIconName</key><string>${ICON_NAME}</string>`;
  if (/<key>CFBundleIconFile<\/key>/.test(plist)) {
    plist = plist.replace(
      /<key>CFBundleIconFile<\/key>\s*<string>[^<]*<\/string>(\s*<key>CFBundleIconName<\/key>\s*<string>[^<]*<\/string>)?/,
      keys);
  } else {
    plist = plist.replace(/<\/dict>\s*<\/plist>/, `    ${keys}\n</dict>\n</plist>`);
  }
  fs.writeFileSync(plistPath, plist);

  fs.rmSync(out, { recursive: true, force: true });

  fs.closeSync(fs.openSync(path.join(context.outDir, '.metadata_never_index'), 'w'));

  try {
    execFileSync('codesign', ['--force', '--deep', '--sign', '-', path.join(context.appOutDir, `${context.packager.appInfo.productFilename}.app`)], { stdio: 'inherit' });
  } catch (_) { /* ad-hoc reseal best-effort */ }

  console.log('[afterPack] Icon Composer icon installed (CFBundleIconName=' + ICON_NAME + ')');
};
