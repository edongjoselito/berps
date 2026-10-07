import 'dart:async';
import 'dart:convert';
import 'dart:io' show Platform;

import 'package:flutter/foundation.dart' show kIsWeb, ValueNotifier;
import 'package:local_notifier/local_notifier.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:window_manager/window_manager.dart';

import '../../features/admin/data/admin_api.dart';
import '../../features/admin/domain/admin_models.dart';
import '../../features/attendance/domain/staff_attendance.dart';
import '../../features/auth/domain/staff_session.dart';
import '../../features/home/data/staff_api.dart';
import '../../features/home/domain/staff_dashboard.dart';
import '../../features/tasks/domain/staff_tasks.dart';

/// Background notification poller for the BERPS desktop app (macOS/Windows/
/// Linux builds — inert everywhere else).
///
/// Uses the persisted 30-day mobile API token, so polling starts the moment
/// the app launches — including hidden-at-login — no window or sign-in screen
/// required. Combined with close-to-tray and launch-at-login (main.dart),
/// alerts keep arriving even when the app is never opened.
class NotificationService {
  NotificationService._();

  static final NotificationService instance = NotificationService._();

  static const _pollInterval = Duration(seconds: 60);

  // Missed time-in escalation ladder (device-local time).
  static const _attReminder = '08:05';
  static const _attEscalate = '08:15';
  static const _unassignedRealert = '09:00';
  static const _digestTime = '07:45';

  // Forgotten clock-out ladder — staff still clocked in after the evening
  // window. Attendance gets polled only once `_clockOutWatch` is reached.
  static const _clockOutWatch = '20:45';
  static const _clockOutReminder = '21:30';
  static const _clockOutEscalate = '23:00';

  // SharedPreferences keys for dedupe/baseline state.
  static const _kEnabled = 'notif_enabled';
  static const _kBaseline = 'notif_baseline_v1';
  static const _kSeenTaskIds = 'notif_seen_task_ids_v1';
  static const _kLastUnassigned = 'notif_last_unassigned_v1';
  static const _kUnassignedRealert = 'notif_unassigned_realert_v1';
  static const _kAtt = 'notif_att_stages_v1';
  static const _kDigest = 'notif_digest_v1';

  static bool get isDesktop =>
      !kIsWeb && (Platform.isMacOS || Platform.isWindows || Platform.isLinux);

  final StaffApi _api = StaffApi();
  final AdminApi _adminApi = AdminApi();

  SharedPreferences? _prefs;
  StaffSession? _session;
  Timer? _timer;
  bool _ticking = false;
  int _consecutiveFailures = 0;
  bool _initialized = false;

  /// Tab navigation requests raised by notification clicks. The home screen
  /// listens and consumes; value is the tab key ('dashboard'|'tasks'|'attendance').
  final ValueNotifier<String?> openRequest = ValueNotifier<String?>(null);

  bool get enabled => _prefs?.getBool(_kEnabled) ?? true;

  Future<void> setEnabled(bool value) async {
    await _prefs?.setBool(_kEnabled, value);
    if (value) {
      _restartTimer();
    } else {
      _timer?.cancel();
      _timer = null;
    }
  }

  /// Call once at startup (desktop only).
  Future<void> initialize() async {
    if (!isDesktop || _initialized) return;
    _prefs = await SharedPreferences.getInstance();
    try {
      await localNotifier.setup(appName: 'BERPS Staff');
      _initialized = true;
    } catch (_) {
      // Plugin unavailable — stay inert rather than crashing the app.
    }
  }

  /// Called whenever the auth state lands on signedIn. Idempotent — safe to
  /// call repeatedly (e.g. token refresh swaps the session object).
  void start(StaffSession session) {
    if (!isDesktop) return;
    _session = session;
    _consecutiveFailures = 0;
    _restartTimer();
  }

  /// Called on sign-out — token is gone, so stop polling.
  void stop() {
    _session = null;
    _timer?.cancel();
    _timer = null;
  }

  void _restartTimer() {
    _timer?.cancel();
    if (_session == null || !enabled) {
      _timer = null;
      return;
    }
    unawaited(_tick());
    _timer = Timer.periodic(_pollInterval, (_) => unawaited(_tick()));
  }

  Future<void> _tick() async {
    final session = _session;
    if (session == null || _ticking || !enabled) return;
    _ticking = true;
    try {
      final isAdmin = session.position.trim().toLowerCase() == 'admin';
      final _Snapshot snap;
      if (isAdmin) {
        // Admin tokens can't hit staff endpoints — use the admin task list
        // and treat tasks with no assignee as the watch set.
        final data = await _adminApi.fetchTasks(
          baseUrl: session.baseUrl,
          token: session.token,
        );
        snap = _Snapshot.forAdmin(data);
      } else {
        final tasksFuture = session.hasTasks
            ? _api.fetchTasks(baseUrl: session.baseUrl, token: session.token)
            : Future<StaffTasksData?>.value(null);
        // Attendance is fetched when it carries something tasks can't tell
        // us: the time-in flag (attendance-only workspaces), or the open-slot
        // flag needed for the evening "still clocked in" ladder.
        final watchClockOut = _hhmm().compareTo(_clockOutWatch) >= 0;
        final attendanceFuture =
            session.hasAttendance && (!session.hasTasks || watchClockOut)
            ? _api.fetchAttendance(
                baseUrl: session.baseUrl,
                token: session.token,
              )
            : Future<StaffAttendanceData?>.value(null);
        final dashboardFuture = session.hasSupport
            ? _api.fetchDashboard(
                baseUrl: session.baseUrl,
                token: session.token,
              )
            : Future<StaffDashboard?>.value(null);

        snap = _Snapshot.forStaff(
          await tasksFuture,
          await attendanceFuture,
          await dashboardFuture,
        );
      }
      _consecutiveFailures = 0;
      await _process(snap, session);
    } catch (_) {
      // Network hiccups and expired tokens just retry next tick; give up after
      // a sustained streak so we don't hammer a dead session forever.
      _consecutiveFailures += 1;
      if (_consecutiveFailures >= 20) {
        _timer?.cancel();
        _timer = null;
      }
    } finally {
      _ticking = false;
    }
  }

  Future<void> _process(_Snapshot snap, StaffSession session) async {
    final prefs = _prefs;
    if (prefs == null) return;

    // First successful poll only seeds the baseline — no catch-up flood.
    if (!(prefs.getBool(_kBaseline) ?? false)) {
      await prefs.setBool(_kBaseline, true);
      await prefs.setStringList(
        _kSeenTaskIds,
        snap.watchIds.map((e) => e.toString()).toList(),
      );
      await prefs.setInt(_kLastUnassigned, snap.unassignedCount ?? 0);
      return;
    }

    // ── New items in the watch set ──────────────────────────────────────
    final seen = (prefs.getStringList(_kSeenTaskIds) ?? const <String>[])
        .map((e) => int.tryParse(e) ?? 0)
        .toSet();
    final fresh = snap.watchIds.where((id) => !seen.contains(id)).toList();
    for (final id in fresh.take(3)) {
      _show(
        id: 'task-$id',
        title: snap.newItemTitle,
        body: _clip(snap.titles[id] ?? 'Untitled task'),
        openTab: 'tasks',
      );
    }
    await prefs.setStringList(
      _kSeenTaskIds,
      snap.watchIds.map((e) => e.toString()).toList(),
    );

    final now = _hhmm();
    final today = _todayKey();

    // ── Morning digest (once/day, staff + admin) ────────────────────────
    if (snap.hasDigest &&
        now.compareTo(_digestTime) >= 0 &&
        prefs.getString(_kDigest) != today &&
        (snap.openCount > 0 || snap.dueToday > 0 || snap.overdue > 0)) {
      await prefs.setString(_kDigest, today);
      final parts = <String>[
        '${snap.openCount} open task${snap.openCount == 1 ? '' : 's'}',
        if (snap.dueToday > 0) '${snap.dueToday} due today',
        if (snap.overdue > 0) '${snap.overdue} overdue',
      ];
      _show(
        id: 'digest',
        title: 'Good morning — today\'s workload',
        body: parts.join(' · '),
        openTab: 'tasks',
      );
    }

    // ── Unassigned tickets (staff support queue) ────────────────────────
    final unassigned = snap.unassignedCount;
    if (unassigned != null) {
      final last = prefs.getInt(_kLastUnassigned) ?? 0;
      if (unassigned > last) {
        _show(
          id: 'unassigned',
          title: 'Unassigned support ticket',
          body:
              '$unassigned ticket${unassigned == 1 ? '' : 's'} waiting for an assignee.',
          openTab: 'dashboard',
        );
      } else if (unassigned > 0 &&
          now.compareTo(_unassignedRealert) >= 0 &&
          prefs.getString(_kUnassignedRealert) != today) {
        await prefs.setString(_kUnassignedRealert, today);
        _show(
          id: 'unassigned-realert',
          title: 'Unassigned tickets — still open',
          body:
              '$unassigned ticket${unassigned == 1 ? '' : 's'} still need an assignee.',
          openTab: 'dashboard',
        );
      }
      await prefs.setInt(_kLastUnassigned, unassigned);
    }

    // ── Attendance ladders (staff) ──────────────────────────────────────
    if (session.hasAttendance &&
        (snap.hasTimeIn == false || snap.openSlot != null)) {
      final att = _readAttStages(today);

      // Never timed in today.
      if (snap.hasTimeIn == false) {
        if (now.compareTo(_attReminder) >= 0 && !(att['s1'] ?? false)) {
          att['s1'] = true;
          _attendanceNotify(
            session: session,
            title: 'Time-in reminder',
            body: 'Good morning! You haven\'t timed in yet today.',
            actionLabel: 'Time In Now',
            run: () =>
                _api.timeIn(baseUrl: session.baseUrl, token: session.token),
            successTitle: 'Timed in',
          );
        }
        if (now.compareTo(_attEscalate) >= 0 && !(att['s2'] ?? false)) {
          att['s2'] = true;
          _attendanceNotify(
            session: session,
            title: 'Still not timed in',
            body: 'Your attendance for today is still unrecorded.',
            actionLabel: 'Time In Now',
            run: () =>
                _api.timeIn(baseUrl: session.baseUrl, token: session.token),
            successTitle: 'Timed in',
          );
        }
      }

      // Clocked in but never clocked out — works for any open slot (AM, PM,
      // or an extra overtime row), regardless of how many in/out cycles ran.
      if (snap.openSlot != null) {
        final since = snap.latestTimeIn.isEmpty
            ? ''
            : ' — on the clock since ${snap.latestTimeIn}';
        if (now.compareTo(_clockOutReminder) >= 0 && !(att['e1'] ?? false)) {
          att['e1'] = true;
          _attendanceNotify(
            session: session,
            title: 'Still clocked in?',
            body:
                'Your ${snap.openSlot} slot is still open$since. Don\'t forget to time out.',
            actionLabel: 'Time Out Now',
            run: () =>
                _api.timeOut(baseUrl: session.baseUrl, token: session.token),
            successTitle: 'Timed out',
          );
        }
        if (now.compareTo(_clockOutEscalate) >= 0 && !(att['e2'] ?? false)) {
          att['e2'] = true;
          _attendanceNotify(
            session: session,
            title: 'You\'re still on the clock',
            body:
                'Your ${snap.openSlot} slot was never closed$since. Time out now so today\'s DTR isn\'t broken.',
            actionLabel: 'Time Out Now',
            run: () =>
                _api.timeOut(baseUrl: session.baseUrl, token: session.token),
            successTitle: 'Timed out',
          );
        }
      }
      await _writeAttStages(att);
    }
  }

  // ── Notifications ─────────────────────────────────────────────────────

  /// Shows an attendance notification with a one-tap action button (Time In
  /// Now / Time Out Now) that calls [run] against the API. The button exists
  /// only on macOS; elsewhere clicking the body opens the Attendance tab.
  void _attendanceNotify({
    required StaffSession session,
    required String title,
    required String body,
    required String actionLabel,
    required Future<String> Function() run,
    required String successTitle,
  }) {
    final actions = Platform.isMacOS
        ? [LocalNotificationAction(text: actionLabel)]
        : <LocalNotificationAction>[];

    final n = _show(
      id: 'attendance',
      title: title,
      body: body,
      openTab: 'attendance',
      actions: actions,
    );

    if (n != null && actions.isNotEmpty) {
      n.onClickAction = (index) async {
        try {
          final message = await run();
          _show(id: 'att-result', title: successTitle, body: message);
        } catch (_) {
          _show(
            id: 'att-result',
            title: '$successTitle failed',
            body: 'No connection to BERPS right now.',
          );
        }
      };
    }
  }

  LocalNotification? _show({
    required String id,
    required String title,
    required String body,
    String? openTab,
    List<LocalNotificationAction>? actions,
  }) {
    if (!_initialized || !enabled) return null;

    final notification = LocalNotification(
      identifier: id,
      title: title,
      body: body,
      actions: actions,
    );
    notification.onClick = () {
      if (openTab != null) openRequest.value = openTab;
      unawaited(_revealWindow());
    };
    unawaited(notification.show());
    return notification;
  }

  Future<void> _revealWindow() async {
    try {
      await windowManager.setSkipTaskbar(false);
      await windowManager.show();
      await windowManager.focus();
    } catch (_) {}
  }

  // ── Small helpers ─────────────────────────────────────────────────────

  /// Stage flags for today's attendance reminders ({s1, s2}).
  Map<String, bool> _readAttStages(String today) {
    final raw = _prefs?.getString(_kAtt);
    if (raw != null && raw.isNotEmpty) {
      try {
        final decoded = jsonDecode(raw);
        if (decoded is Map && decoded[today] is Map) {
          final day = decoded[today] as Map;
          return {
            's1': day['s1'] == true,
            's2': day['s2'] == true,
            'e1': day['e1'] == true,
            'e2': day['e2'] == true,
          };
        }
      } catch (_) {}
    }
    return {'s1': false, 's2': false, 'e1': false, 'e2': false};
  }

  Future<void> _writeAttStages(Map<String, bool> stages) async {
    await _prefs?.setString(
      _kAtt,
      jsonEncode({
        _todayKey(): {
          's1': stages['s1'] ?? false,
          's2': stages['s2'] ?? false,
          'e1': stages['e1'] ?? false,
          'e2': stages['e2'] ?? false,
        },
      }),
    );
  }

  String _hhmm() {
    final now = DateTime.now();
    return '${now.hour.toString().padLeft(2, '0')}:${now.minute.toString().padLeft(2, '0')}';
  }

  String _todayKey() {
    final now = DateTime.now();
    return '${now.year}${now.month.toString().padLeft(2, '0')}${now.day.toString().padLeft(2, '0')}';
  }

  String _clip(String value, [int max = 160]) =>
      value.length <= max ? value : '${value.substring(0, max)}…';
}

/// Normalized poll result — staff and admin feeds collapse into one shape so
/// [_process] stays role-agnostic.
class _Snapshot {
  _Snapshot._({
    required this.watchIds,
    required this.titles,
    required this.newItemTitle,
    required this.hasDigest,
    this.unassignedCount,
    this.hasTimeIn,
    this.openSlot,
    this.latestTimeIn = '',
    this.openCount = 0,
    this.dueToday = 0,
    this.overdue = 0,
  });

  /// Staff view: watch = my open tasks; unassigned = support-ticket count.
  factory _Snapshot.forStaff(
    StaffTasksData? tasks,
    StaffAttendanceData? attendance,
    StaffDashboard? dashboard,
  ) {
    final ids = <int>[];
    final titles = <int, String>{};
    for (final task in tasks?.tasks ?? const <StaffTask>[]) {
      if (task.id > 0) {
        ids.add(task.id);
        titles[task.id] = task.title.isEmpty ? 'Untitled task' : task.title;
      }
    }
    final openSlot = attendance?.status.openSlotLabel.trim() ?? '';
    return _Snapshot._(
      watchIds: ids,
      titles: titles,
      newItemTitle: 'New task assigned to you',
      hasDigest: tasks != null,
      unassignedCount: dashboard?.unassignedSupportCount,
      hasTimeIn: tasks?.hasTimeInToday ?? attendance?.status.hasRecordToday,
      openSlot: openSlot.isEmpty ? null : openSlot,
      latestTimeIn: attendance?.status.latestTimeInLabel ?? '',
      openCount: tasks?.stats.open ?? 0,
      dueToday: tasks?.stats.dueToday ?? 0,
      overdue: tasks?.stats.overdue ?? 0,
    );
  }

  /// Admin view: watch = open tasks with no assignee.
  factory _Snapshot.forAdmin(AdminTasksData data) {
    final ids = <int>[];
    final titles = <int, String>{};
    for (final task in data.tasks) {
      if (task.id > 0 && task.assignedName.trim().isEmpty) {
        ids.add(task.id);
        titles[task.id] = task.title.isEmpty ? 'Untitled task' : task.title;
      }
    }
    return _Snapshot._(
      watchIds: ids,
      titles: titles,
      newItemTitle: 'New unassigned task',
      hasDigest: true,
      hasTimeIn: null,
      openCount: data.counts.open,
      dueToday: data.counts.dueToday,
      overdue: data.counts.overdue,
    );
  }

  /// IDs diffed between polls — new entries trigger a notification.
  final List<int> watchIds;
  final Map<int, String> titles;
  final String newItemTitle;

  /// Whether digest stats are available this session.
  final bool hasDigest;

  /// Support-ticket unassigned count (staff only; admins watch IDs directly).
  final int? unassignedCount;

  /// Whether today's time-in exists (staff only; null skips the ladder).
  final bool? hasTimeIn;

  /// 'AM'/'PM' while a time-in has no matching time-out (staff only).
  final String? openSlot;
  final String latestTimeIn;
  final int openCount;
  final int dueToday;
  final int overdue;
}
