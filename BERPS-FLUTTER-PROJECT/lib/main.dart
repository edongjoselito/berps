import 'dart:async';
import 'dart:io' show Platform;

import 'package:flutter/foundation.dart' show kIsWeb, kReleaseMode;
import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:launch_at_startup/launch_at_startup.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:tray_manager/tray_manager.dart';
import 'package:window_manager/window_manager.dart';

import 'app/app.dart';
import 'core/services/notification_service.dart';
import 'features/auth/data/session_store.dart';

Future<void> main() async {
  WidgetsFlutterBinding.ensureInitialized();
  await SystemChrome.setPreferredOrientations([
    DeviceOrientation.portraitUp,
    DeviceOrientation.portraitDown,
  ]);

  // Enforce the desktop minimum window size through the plugin so Windows and
  // Linux can't shrink below the sidebar breakpoint (macOS sets its minimum
  // natively in MainFlutterWindow.swift), and restore the last window frame.
  if (!kIsWeb && (Platform.isMacOS || Platform.isWindows || Platform.isLinux)) {
    await windowManager.ensureInitialized();
    final store = SessionStore(await SharedPreferences.getInstance());
    final saved = _parseBounds(store.readWindowBounds());

    if (Platform.isWindows || Platform.isLinux) {
      await windowManager.waitUntilReadyToShow(
        WindowOptions(
          minimumSize: const Size(1024, 700),
          size: saved?.size,
          title: 'BERPS',
        ),
        () async {
          await windowManager.show();
          await windowManager.focus();
        },
      );
    }
    if (saved != null) {
      await windowManager.setBounds(saved);
    }
    _WindowStatePersister(store).attach();

    // Background notifications + tray: closing the window hides it (keeps
    // polling alive); the app can also start at login so alerts arrive even
    // when the window was never opened.
    await NotificationService.instance.initialize();
    await _DesktopShell().attach();
  }

  runApp(const BerpsMobileApp());
}

Rect? _parseBounds(String? raw) {
  if (raw == null) return null;
  final parts = raw.split(',');
  if (parts.length != 4) return null;
  final values = parts.map(double.tryParse).toList();
  if (values.any((v) => v == null)) return null;
  final width = values[2]!;
  final height = values[3]!;
  if (width < 640 || height < 480) return null;
  return Rect.fromLTWH(values[0]!, values[1]!, width, height);
}

/// Saves the window frame (debounced) whenever the window is moved or resized
/// so the next launch restores the same placement.
class _WindowStatePersister with WindowListener {
  _WindowStatePersister(this._store);

  final SessionStore _store;
  Timer? _debounce;

  void attach() => windowManager.addListener(this);

  void _scheduleSave() {
    _debounce?.cancel();
    _debounce = Timer(const Duration(milliseconds: 600), () async {
      final bounds = await windowManager.getBounds();
      await _store.saveWindowBounds(
        '${bounds.left},${bounds.top},${bounds.width},${bounds.height}',
      );
    });
  }

  @override
  void onWindowResized() => _scheduleSave();

  @override
  void onWindowMoved() => _scheduleSave();
}

/// Desktop "stay alive" shell: system-tray icon, hide-instead-of-quit on the
/// close button, and launch-at-login — the trio that lets the notification
/// poller run even when the window is never opened.
class _DesktopShell with TrayListener, WindowListener {
  bool _quitting = false;

  Future<void> attach() async {
    windowManager.addListener(this);
    await windowManager.setPreventClose(true);

    // Tray icon (macOS menubar / Windows notification area).
    try {
      await trayManager.setIcon('assets/logo.png');
      trayManager.addListener(this);
      trayManager.setToolTip('BERPS Staff');
      await _refreshMenu();
    } catch (_) {
      // Tray is a nicety — keep going without it.
    }

    // Launch at login (release builds only get the auto-enable; debug runs
    // leave it off but the tray toggle still works).
    try {
      launchAtStartup.setup(
        appName: 'BERPS Staff',
        appPath: Platform.resolvedExecutable,
      );
      if (kReleaseMode && !(await launchAtStartup.isEnabled())) {
        await launchAtStartup.enable();
      }
    } catch (_) {}
  }

  Future<void> reveal() async {
    await windowManager.setSkipTaskbar(false);
    await windowManager.show();
    await windowManager.focus();
  }

  Future<void> _quit() async {
    _quitting = true;
    await windowManager.setPreventClose(false);
    await windowManager.destroy();
  }

  Future<void> _refreshMenu() async {
    bool launchEnabled = false;
    try {
      launchEnabled = await launchAtStartup.isEnabled();
    } catch (_) {}

    await trayManager.setContextMenu(
      Menu(
        items: [
          MenuItem(label: 'Open BERPS Staff', onClick: (_) => reveal()),
          MenuItem.separator(),
          MenuItem.checkbox(
            label: 'Notifications',
            checked: NotificationService.instance.enabled,
            onClick: (item) async {
              // item.checked holds the OLD state at click time — flip it.
              final next = !(item.checked ?? false);
              item.checked = next;
              await NotificationService.instance.setEnabled(next);
              await _refreshMenu();
            },
          ),
          MenuItem.checkbox(
            label: 'Launch at login',
            checked: launchEnabled,
            onClick: (item) async {
              final next = !(item.checked ?? false);
              item.checked = next;
              try {
                if (next) {
                  await launchAtStartup.enable();
                } else {
                  await launchAtStartup.disable();
                }
              } catch (_) {}
              await _refreshMenu();
            },
          ),
          MenuItem.separator(),
          MenuItem(label: 'Quit', onClick: (_) => _quit()),
        ],
      ),
    );
  }

  // ── TrayListener ─────────────────────────────────────────────────────
  @override
  void onTrayIconMouseDown() => unawaited(reveal());

  @override
  void onTrayIconRightMouseDown() => trayManager.popUpContextMenu();

  // ── WindowListener ───────────────────────────────────────────────────
  @override
  void onWindowClose() async {
    if (_quitting) return;
    // Hide instead of quitting — the poller keeps running in the tray.
    await windowManager.hide();
    await windowManager.setSkipTaskbar(true);
  }
}
