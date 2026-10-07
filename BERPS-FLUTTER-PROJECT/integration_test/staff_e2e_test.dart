import 'dart:io';
import 'dart:ui' as ui;

import 'package:berps_mobile/app/app.dart';
import 'package:flutter/material.dart';
import 'package:flutter/rendering.dart';
import 'package:flutter/services.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:integration_test/integration_test.dart';
import 'package:lucide_icons_flutter/lucide_icons.dart';

/// E2E smoke test: real app, real network, real account.
/// Run: flutter test integration_test/staff_e2e_test.dart -d macos
const _server = 'https://berps.online';
const _user = 'clarksteven.edong@softtechservices.net';
const _pass = 'compu7er';
const _shotDir = '/tmp/berps_e2e';
final _shotKey = GlobalKey();

Future<void> _shot(String name) async {
  try {
    final boundary = _shotKey.currentContext!.findRenderObject()
        as RenderRepaintBoundary;
    final image = await boundary.toImage(pixelRatio: 1.0);
    final bytes = await image.toByteData(format: ui.ImageByteFormat.png);
    await File('$_shotDir/$name.png')
        .writeAsBytes(bytes!.buffer.asUint8List());
    debugPrint('shot saved: $name');
  } catch (e) {
    debugPrint('shot failed $name: $e');
  }
}

Future<void> _settle(WidgetTester tester, [int secs = 8]) async {
  try {
    await tester.pumpAndSettle(
      const Duration(milliseconds: 100),
      EnginePhase.sendSemanticsUpdate,
      Duration(seconds: secs),
    );
  } catch (_) {
    await tester.pump(const Duration(seconds: 1));
  }
}

void main() {
  IntegrationTestWidgetsFlutterBinding.ensureInitialized();

  testWidgets('staff desktop walkthrough', (tester) async {
    await Process.run('mkdir', ['-p', _shotDir]);

    await tester.pumpWidget(
      RepaintBoundary(key: _shotKey, child: const BerpsMobileApp()),
    );
    await _settle(tester, 6);
    await _shot('00_boot');

    // ── Pair workspace if asked ──
    final urlField = find.widgetWithText(TextFormField, 'Workspace URL');
    if (urlField.evaluate().isNotEmpty) {
      await tester.enterText(urlField, _server);
      await tester.tap(find.text('Connect workspace'));
      await _settle(tester, 15);
      await _shot('01_after_pair');
    }

    // ── Login ──
    final fields = find.byType(TextFormField);
    final onDashboard = find.text('Dashboard').evaluate().isNotEmpty;
    if (!onDashboard) {
      if (fields.evaluate().length < 2) {
        final texts = find.byType(Text).evaluate()
            .map((e) => (e.widget as Text).data ?? '')
            .where((t) => t.isNotEmpty).take(30).toList();
        debugPrint('NO_FIELDS — visible texts: $texts');
        await _shot('01b_unknown_screen');
      }
      expect(fields.evaluate().length, greaterThanOrEqualTo(2),
          reason: 'expected username+password fields');
      await tester.enterText(fields.at(0), _user);
      await tester.enterText(fields.at(1), _pass);
      await _shot('02_login_filled');
      await tester.tap(find.text('Sign in'), warnIfMissed: false);
      await _settle(tester, 8);

      // Privacy consent gate may intercept sign-in
      if (find.text('Data Privacy Consent').evaluate().isNotEmpty) {
        final box = find.byType(Checkbox);
        if (box.evaluate().isNotEmpty) {
          await tester.tap(box.first, warnIfMissed: false);
        } else {
          await tester.tap(
              find.textContaining('I agree'), warnIfMissed: false);
        }
        await _settle(tester, 3);
        await _shot('03a_consent_checked');
        await tester.tap(find.text('Continue'), warnIfMissed: false);
        await _settle(tester, 20);
      }
      await _settle(tester, 15);
    }
    await _shot('03_dashboard');

    expect(find.text('Dashboard'), findsWidgets,
        reason: 'dashboard should render after sign in');

    // ── Notifications slide-over ──
    final bell = find.byTooltip('Notifications');
    if (bell.evaluate().isNotEmpty) {
      await tester.tap(bell.first, warnIfMissed: false);
      await _settle(tester, 8);
      await _shot('03b_notifications_panel');
      await tester.sendKeyEvent(LogicalKeyboardKey.escape);
      await _settle(tester, 3);
    }

    // ── Command palette (⌘K) ──
    final search = find.text('Search');
    if (search.evaluate().isNotEmpty) {
      await tester.tap(search.first, warnIfMissed: false);
      await _settle(tester, 3);
      await tester.enterText(find.byType(TextField).last, 'dtr');
      await _settle(tester, 2);
      await _shot('03c_command_palette');
      await tester.sendKeyEvent(LogicalKeyboardKey.escape);
      await _settle(tester, 3);
    }

    // ── Sign-out confirm (cancelled) ──
    final signOut = find.byIcon(LucideIcons.logOut);
    if (signOut.evaluate().isNotEmpty) {
      await tester.tap(signOut.first);
      await _settle(tester, 3);
      await _shot('03d_signout_confirm');
      expect(find.text('Sign out of BERPS?'), findsOneWidget,
          reason: 'desktop sign-out confirm should open');
      await tester.tap(find.text('Cancel').last);
      await _settle(tester, 3);
    }

    // ── Walk every sidebar module ──
    final modules = <String>[
      'Tasks',
      'Unassigned Tickets',
      'Forwarded Tasks',
      'Tickets',
      'Support Dashboard',
      'Attendance',
      'My DTR',
      'Calendar',
      'Notes',
      'Reminders',
      'Annual Goals',
      'Account',
    ];

    var i = 4;
    for (final label in modules) {
      final hit = find.text(label);
      if (hit.evaluate().isEmpty) {
        debugPrint('SKIP: "$label" not present (permission flag off?)');
        continue;
      }
      final scrollables2 = find.byType(Scrollable);
      for (var s = 0; s < scrollables2.evaluate().length; s++) {
        try {
          await tester.scrollUntilVisible(hit, -250,
              scrollable: scrollables2.at(s), maxScrolls: 15);
          break;
        } catch (_) {}
      }
      try {
        await tester.ensureVisible(hit.first);
      } catch (_) {}
      await tester.tap(hit.first, warnIfMissed: false);
      await _settle(tester, 10);
      await _shot(
          '${i.toString().padLeft(2, '0')}_${label.toLowerCase().replaceAll(' ', '_')}');
      expect(tester.takeException(), isNull,
          reason: 'exception while viewing "$label"');
      if (label == 'Tasks') {
        final row = find.textContaining('Overdue by');
        if (row.evaluate().isNotEmpty) {
          await tester.tap(row.first, warnIfMissed: false);
          await _settle(tester, 4);
          await _shot('04b_task_action_dialog');
          await tester.sendKeyEvent(LogicalKeyboardKey.escape);
          await _settle(tester, 3);
        }
      }
      i++;
    }

    // ── Back to dashboard (item may be scrolled out of the lazy list) ──
    final dashItem = find.text('Dashboard');
    final scrollables = find.byType(Scrollable);
    for (var s = 0; s < scrollables.evaluate().length; s++) {
      try {
        await tester.scrollUntilVisible(dashItem, -300,
            scrollable: scrollables.at(s), maxScrolls: 20);
        break;
      } catch (_) {}
    }
    await tester.tap(dashItem.first, warnIfMissed: false);
    await _settle(tester);
    await _shot('99_back_dashboard');

    debugPrint('E2E COMPLETE');
  }, timeout: const Timeout(Duration(minutes: 8)));
}
