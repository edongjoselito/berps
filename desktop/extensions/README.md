# BERPS Desktop Extensions

Extensions are small JS/CSS bundles injected into portal pages by the desktop
app — same idea as browser extensions, but they live inside this app and need
no server deploy.

## Layout

```
extensions/
  my-extension/
    manifest.json      required
    my-extension.js    injected on matching pages
    my-extension.css   optional, injected before js
```

## manifest.json

```json
{
  "id": "my-extension",
  "name": "My Extension",
  "version": "1.0.0",
  "description": "What it does",
  "enabled": true,
  "matches": ["*://berps.online/page/project*"],
  "js": ["my-extension.js"],
  "css": ["my-extension.css"]
}
```

- `matches` — simple globs matched against the full page URL (`*` = any chars,
  `?` = one char). `{origin}` expands to the portal origin, so
  `["{origin}/*"]` runs on every portal page regardless of `BERPS_URL`.
- `enabled: false` disables without deleting the folder.
- Changes are picked up on **app restart** (manifests are read at launch) and
  injected on every matching page load and in-page navigation.

## Runtime contract

- Scripts run in an isolated world — full DOM access, but **no jQuery/`$`**
  and no access to the page's own JS globals. Write vanilla JS.
- Wrap everything in your own scope; the loader already wraps each file in an
  IIFE.
- Scripts must be **idempotent** — they may re-run after in-page navigation.
  Guard DOM changes with a marker (`data-*` attribute) or check before adding.
- `window.berps` bridge (when available):
  - `berps.isDesktop`, `berps.platform`, `berps.version`
  - `berps.notify(title, body)` — native notification
  - `berps.setBadge(n)` — macOS dock badge
  - `berps.onNavigate(cb)` — called with the new URL on every navigation
- Useful page hooks: `<meta name="berps-base-url">` holds the site root;
  `html.berps-desktop` / `html.berps-platform-darwin|win32|linux` classes are
  set before injection.

## Bundled extensions

- **task-tools** — adds a one-click "Close task" action next to every
  "Modify Status" control on task list pages (posts the same `Page/addTaskNote`
  form the modal uses), plus `Alt+T` to jump to the task list.
