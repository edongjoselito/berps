import 'dart:async';
import 'dart:io' show Platform;

import 'package:flutter/foundation.dart' show kIsWeb;
import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:window_manager/window_manager.dart';

import 'app/app.dart';
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
