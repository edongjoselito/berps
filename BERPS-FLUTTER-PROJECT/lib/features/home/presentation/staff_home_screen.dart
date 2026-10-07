import 'dart:async';

import 'package:flutter/foundation.dart';
import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:lucide_icons_flutter/lucide_icons.dart';
import 'package:window_manager/window_manager.dart';

import '../../../core/network/api_exception.dart';
import '../../../core/services/notification_service.dart';
import '../../../core/theme/app_theme.dart';
import '../../../core/utils/haptics.dart';
import '../../../core/widgets/app_toast.dart';
import '../../../core/widgets/desktop_kit.dart';
import '../../shell/presentation/command_palette.dart';
import '../../attendance/presentation/staff_attendance_tab.dart';
import '../../auth/data/session_store.dart';
import '../../auth/domain/mobile_config.dart';
import '../../auth/domain/staff_session.dart';
import '../../calendar/presentation/calendar_screen.dart';
import '../../goals/presentation/annual_goals_screen.dart';
import '../../notes/presentation/notes_screen.dart';
import '../../notifications/presentation/notifications_screen.dart';
import '../../reminders/data/reminders_api.dart';
import '../../reminders/domain/reminder.dart';
import '../../reminders/presentation/reminders_screen.dart';
import '../../shell/presentation/staff_drawer.dart';
import '../../support/presentation/support_issues_screen.dart';
import '../../support_dashboard/presentation/support_dashboard_screen.dart';
import '../../tasks/presentation/staff_task_editor_screen.dart';
import '../../tasks/presentation/staff_tasks_tab.dart';
import '../data/staff_api.dart';
import 'my_dtr_screen.dart';
import 'staff_account_tab.dart';
import 'staff_dashboard_tab.dart';
import 'staff_profile_screen.dart';

/// Bottom-navigation destinations. Only the ones enabled for the workspace's
/// company features are shown (see [_StaffHomeScreenState._tabs]).
enum _StaffTab { dashboard, attendance, tasks, account }

class StaffHomeScreen extends StatefulWidget {
  const StaffHomeScreen({
    super.key,
    required this.session,
    required this.config,
    required this.onSignOut,
    required this.onAvatarUpdated,
    required this.store,
  });

  final StaffSession session;
  final MobileConfig? config;
  final Future<void> Function() onSignOut;
  final Future<void> Function(String avatarUrl) onAvatarUpdated;
  final SessionStore store;

  @override
  State<StaffHomeScreen> createState() => _StaffHomeScreenState();
}

class _StaffHomeScreenState extends State<StaffHomeScreen> {
  final _scaffoldKey = GlobalKey<ScaffoldState>();
  final GlobalKey<NavigatorState> _contentNavKey = GlobalKey<NavigatorState>();
  final StaffApi _api = StaffApi();

  /// Drives rebuilds of the nested content navigator's root page on wide
  /// layouts — the route is cached, so setState alone never refreshes it.
  final ValueNotifier<_StaffTab> _tabNotifier = ValueNotifier(
    _StaffTab.dashboard,
  );

  /// Cached per-build so navigation helpers know which navigator to target.
  bool _isWide = false;

  /// Sidebar destinations as palette commands (refreshed every wide build).
  List<DeskCommand> _navCommands = const [];

  /// Collapsed-to-icons desktop sidebar (persisted across launches).
  bool _sidebarCollapsed = false;

  /// Unread/pending counts shown on sidebar items (desktop only).
  Map<String, int> _badges = const {};
  Timer? _badgeTimer;

  /// Reminders already surfaced as in-app notifications this session, so the
  /// poll doesn't re-toast the same one every cycle.
  final Set<int> _notifiedReminders = {};

  @override
  void initState() {
    super.initState();
    if (AppTheme.isDesktop) {
      _sidebarCollapsed = widget.store.readSidebarCollapsed();
      AppTheme.compactDensity.value = widget.store.readDensityCompact();
      // Restore the last open tab, but only if the session has that feature.
      final lastTab = widget.store.readLastTab();
      final allowed = <_StaffTab>[
        _StaffTab.dashboard,
        if (widget.session.hasAttendance) _StaffTab.attendance,
        if (widget.session.hasTasks) _StaffTab.tasks,
        _StaffTab.account,
      ];
      if (lastTab > 0 && lastTab < _StaffTab.values.length) {
        final candidate = _StaffTab.values[lastTab];
        if (allowed.contains(candidate)) {
          _currentTab = candidate;
          _tabNotifier.value = candidate;
        }
      }
      _refreshBadges();
      _badgeTimer = Timer.periodic(
        const Duration(seconds: 90),
        (_) => _refreshBadges(),
      );
      NotificationService.instance.openRequest.addListener(_consumeOpenRequest);
    }
  }

  /// A system notification was clicked — jump to the tab it targets.
  void _consumeOpenRequest() {
    final request = NotificationService.instance.openRequest.value;
    if (request == null || !mounted) return;
    NotificationService.instance.openRequest.value = null;
    final target = switch (request) {
      'tasks' => _StaffTab.tasks,
      'attendance' => _StaffTab.attendance,
      _ => _StaffTab.dashboard,
    };
    if (_tabs.contains(target)) _selectTab(target);
  }

  /// Polls the same endpoints the screens use to keep sidebar badge counts
  /// (unread notifications, open tasks, reminders due today) fresh.
  Future<void> _refreshBadges() async {
    if (!mounted) return;
    final badges = <String, int>{};
    try {
      final notifications = await _api.fetchNotifications(
        baseUrl: widget.session.baseUrl,
        token: widget.session.token,
        limit: 1,
      );
      if (notifications.unseenTotal > 0) {
        badges['notifications'] = notifications.unseenTotal;
      }
    } catch (_) {}
    if (widget.session.hasTasks) {
      try {
        final tasks = await _api.fetchTasks(
          baseUrl: widget.session.baseUrl,
          token: widget.session.token,
        );
        if (tasks.stats.open > 0) badges['tasks'] = tasks.stats.open;
        if (widget.session.hasForwardedTasks && tasks.stats.forwarded > 0) {
          badges['forwarded-tasks'] = tasks.stats.forwarded;
        }
      } catch (_) {}
    }
    if (widget.session.hasReminders) {
      try {
        final reminders = await RemindersApi().fetchReminders(
          baseUrl: widget.session.baseUrl,
          token: widget.session.token,
        );
        if (reminders.dueTodayCount > 0) {
          badges['reminders'] = reminders.dueTodayCount;
        }
        _surfaceDueReminders(reminders.reminders);
      } catch (_) {}
    }
    if (mounted) setState(() => _badges = badges);
  }

  /// In-app "desktop notification" for reminders that came due since the last
  /// poll — surfaces a toast the first time each reminder goes past its time.
  void _surfaceDueReminders(List<Reminder> reminders) {
    if (!mounted) return;
    final now = DateTime.now();
    // Only notify for reminders that went due within the last poll window —
    // older ones are already covered by the badge count, not a popup.
    final window = now.subtract(const Duration(minutes: 3));
    for (final reminder in reminders) {
      final due = reminder.remindAtDate;
      if (due == null || _notifiedReminders.contains(reminder.id)) continue;
      if (!due.isAfter(now) && due.isAfter(window)) {
        _notifiedReminders.add(reminder.id);
        AppToast.info(
          context,
          'Reminder: ${reminder.title.isEmpty ? reminder.remindAtLabel : reminder.title}',
        );
      }
    }
  }

  void _toggleSidebar() {
    Haptics.light();
    setState(() => _sidebarCollapsed = !_sidebarCollapsed);
    unawaited(widget.store.saveSidebarCollapsed(_sidebarCollapsed));
  }

  /// Rebuilds the visible tab's root page, which refetches its data — the
  /// desktop equivalent of pull-to-refresh (⌘R).
  void _reloadCurrentTab() {
    Haptics.light();
    setState(() {
      switch (_currentTab) {
        case _StaffTab.dashboard:
          _dashboardReopenKey++;
        case _StaffTab.attendance:
          _attendanceReopenKey++;
        case _StaffTab.tasks:
          _tasksReopenKey++;
        case _StaffTab.account:
      }
    });
    _refreshBadges();
  }

  void _showShortcutsHelp() {
    final mod = defaultTargetPlatform == TargetPlatform.macOS ? '⌘' : 'Ctrl+';
    final alt = defaultTargetPlatform == TargetPlatform.macOS ? '⌥' : 'Alt+';
    showDialog<void>(
      context: context,
      builder: (dialogContext) => Dialog(
        insetPadding: const EdgeInsets.all(32),
        child: ConstrainedBox(
          constraints: const BoxConstraints(maxWidth: 400),
          child: Padding(
            padding: const EdgeInsets.fromLTRB(24, 22, 24, 20),
            child: Column(
              mainAxisSize: MainAxisSize.min,
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const Text(
                  'Keyboard shortcuts',
                  style: TextStyle(
                    fontSize: 17,
                    fontWeight: FontWeight.w800,
                    color: AppTheme.textPrimary,
                    letterSpacing: -0.3,
                  ),
                ),
                const SizedBox(height: 16),
                _shortcutRow('${mod}K', 'Command palette'),
                _shortcutRow('${mod}1–9', 'Jump to sidebar section'),
                _shortcutRow('${mod}R', 'Reload current page'),
                _shortcutRow('${mod}N', 'New task / note'),
                _shortcutRow('$mod/', 'This cheat sheet'),
                _shortcutRow('${alt}W', 'Close current page'),
                const SizedBox(height: 14),
                Align(
                  alignment: Alignment.centerRight,
                  child: DeskButton(
                    label: 'Done',
                    icon: LucideIcons.check,
                    primary: false,
                    onTap: () => Navigator.of(dialogContext).pop(),
                  ),
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }

  static Widget _shortcutRow(String keys, String action) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 5),
      child: Row(
        children: [
          KeyHint(keys),
          const SizedBox(width: 12),
          Expanded(
            child: Text(
              action,
              style: const TextStyle(
                fontSize: 13,
                fontWeight: FontWeight.w600,
                color: AppTheme.textSecondary,
              ),
            ),
          ),
        ],
      ),
    );
  }

  /// Quick actions exposed through the command palette.
  Future<void> _newTask() async {
    if (!widget.session.hasTasks) return;
    Haptics.light();
    try {
      final data = await _api.fetchTasks(
        baseUrl: widget.session.baseUrl,
        token: widget.session.token,
      );
      if (!mounted) return;
      if (AppTheme.isDesktop) {
        await showAppSheet<bool>(
          context: context,
          maxWidth: 940,
          builder: (_) => StaffTaskEditorScreen(
            session: widget.session,
            projects: data.projects,
            staffOptions: data.staffOptions,
            modal: true,
          ),
        );
      } else {
        await _pushContent(
          StaffTaskEditorScreen(
            session: widget.session,
            projects: data.projects,
            staffOptions: data.staffOptions,
          ),
          navId: 'tasks',
        );
      }
      if (!mounted) return;
      setState(() => _tasksReopenKey++);
    } on ApiException catch (e) {
      if (mounted) AppToast.error(context, e.message);
    }
  }

  Future<void> _newNote() async {
    Haptics.light();
    await _pushContent(
      NotesScreen(session: widget.session, openEditorOnMount: true),
      navId: 'notes',
    );
  }

  Future<void> _newReminder() async {
    Haptics.light();
    await _pushContent(
      RemindersScreen(session: widget.session, openEditorOnMount: true),
      navId: 'reminders',
    );
  }

  /// Punches in or out depending on the live attendance status — the desktop
  /// "quick punch" used by the palette and the dashboard Today card.
  Future<void> _quickPunch() async {
    if (!widget.session.hasAttendance) return;
    Haptics.medium();
    try {
      final data = await _api.fetchAttendance(
        baseUrl: widget.session.baseUrl,
        token: widget.session.token,
      );
      if (!mounted) return;
      final String message;
      if (data.status.canTimeIn) {
        message = await _api.timeIn(
          baseUrl: widget.session.baseUrl,
          token: widget.session.token,
        );
      } else if (data.status.canTimeOut) {
        message = await _api.timeOut(
          baseUrl: widget.session.baseUrl,
          token: widget.session.token,
        );
      } else {
        AppToast.info(context, 'No punch slot available right now.');
        return;
      }
      if (!mounted) return;
      AppToast.success(context, message);
      setState(() {
        _attendanceReopenKey++;
        _dashboardReopenKey++;
      });
      _refreshBadges();
    } on ApiException catch (e) {
      if (mounted) AppToast.error(context, e.message);
    }
  }

  Future<void> _openPalette() {
    final session = widget.session;
    return showCommandPalette(context, [
      DeskCommand(
        label: 'New task',
        section: 'Actions',
        icon: LucideIcons.squarePen,
        keywords: 'create add task',
        shortcut: '${modKeyLabel}N',
        onRun: _newTask,
      ),
      if (session.hasNotes)
        DeskCommand(
          label: 'New note',
          section: 'Actions',
          icon: LucideIcons.notebookPen,
          keywords: 'create add note write',
          onRun: _newNote,
        ),
      if (session.hasReminders)
        DeskCommand(
          label: 'New reminder',
          section: 'Actions',
          icon: LucideIcons.bellPlus,
          keywords: 'create add reminder alert',
          onRun: _newReminder,
        ),
      if (session.hasAttendance)
        DeskCommand(
          label: 'Time in / out',
          section: 'Actions',
          icon: LucideIcons.timer,
          keywords: 'punch clock attendance dtr',
          onRun: _quickPunch,
        ),
      ..._navCommands,
      DeskCommand(
        label: 'My profile',
        section: 'Account',
        icon: LucideIcons.userPen,
        keywords: 'avatar photo edit',
        onRun: () => _openProfile(),
      ),
      DeskCommand(
        label: 'Keyboard shortcuts',
        section: 'Account',
        icon: LucideIcons.keyboard,
        keywords: 'keys help cheat sheet',
        shortcut: '$modKeyLabel/',
        onRun: _showShortcutsHelp,
      ),
      DeskCommand(
        label: 'Sign out',
        section: 'Account',
        icon: LucideIcons.logOut,
        keywords: 'logout log out exit',
        onRun: _confirmSignOut,
      ),
    ]);
  }

  /// Sidebar id of the page currently pushed over the tab content (wide
  /// layouts), so the sidebar highlights the page actually on screen.
  String? _pushedNavId;

  /// Pushes a page. On wide layouts this goes onto the content-area navigator
  /// so the sidebar stays visible; on narrow layouts it's a normal push.
  /// Sidebar destinations ([navId] set) replace any page already pushed
  /// instead of stacking on top of it.
  Future<T?> _pushContent<T>(Widget page, {String? navId}) async {
    if (!_isWide) {
      return Navigator.of(
        context,
      ).push<T>(MaterialPageRoute<T>(builder: (_) => page));
    }
    final route = MaterialPageRoute<T>(
      builder: (_) => navId != null ? DeskRootScope(child: page) : page,
    );

    final nav = _contentNavKey.currentState!;
    if (navId != null) nav.popUntil((r) => r.isFirst);
    setState(() => _pushedNavId = navId);
    final result = await nav.push<T>(route);
    if (mounted && _pushedNavId == navId) {
      setState(() => _pushedNavId = null);
    }
    return result;
  }

  _StaffTab _currentTab = _StaffTab.dashboard;
  String _pendingTasksScope = '';
  String _pendingTasksStatFilter = '';
  bool _pendingDtrView = false;
  int _dashboardReopenKey = 0;
  int _tasksReopenKey = 0;
  int _attendanceReopenKey = 0;

  /// The enabled bottom-nav tabs for this workspace, in display order.
  List<_StaffTab> get _tabs {
    final session = widget.session;
    return [
      _StaffTab.dashboard,
      if (session.hasAttendance) _StaffTab.attendance,
      if (session.hasTasks) _StaffTab.tasks,
      _StaffTab.account,
    ];
  }

  Future<void> _confirmSignOut() async {
    if (AppTheme.isDesktop) {
      final ok = await showDeskConfirm(
        context: context,
        title: 'Sign out of BERPS?',
        message: 'You will need to sign in again to continue.',
        confirmLabel: 'Sign out',
        danger: true,
      );
      if (ok) await widget.onSignOut();
      return;
    }
    final confirmed = await showDialog<bool>(
      context: context,
      barrierDismissible: false,
      builder: (context) => AlertDialog(
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(AppTheme.isDesktop ? 16 : 24),
        ),
        backgroundColor: AppTheme.surface,
        contentPadding: const EdgeInsets.fromLTRB(24, 28, 24, 20),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Container(
              width: 64,
              height: 64,
              decoration: BoxDecoration(
                color: AppTheme.danger.withValues(alpha: 0.08),
                shape: BoxShape.circle,
              ),
              child: const Icon(
                LucideIcons.logOut,
                color: AppTheme.danger,
                size: 28,
              ),
            ),
            const SizedBox(height: 18),
            const Text(
              'Sign out?',
              style: TextStyle(
                fontWeight: FontWeight.w900,
                fontSize: 20,
                color: AppTheme.textPrimary,
              ),
            ),
            const SizedBox(height: 8),
            const Text(
              'You will need to sign in again to continue.',
              textAlign: TextAlign.center,
              style: TextStyle(
                color: AppTheme.textSecondary,
                fontSize: 14,
                height: 1.4,
              ),
            ),
            const SizedBox(height: 24),
            Row(
              children: [
                Expanded(
                  child: OutlinedButton(
                    onPressed: () => Navigator.of(context).pop(false),
                    style: OutlinedButton.styleFrom(
                      minimumSize: const Size(0, 48),
                    ),
                    child: const Text('Cancel'),
                  ),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: FilledButton(
                    onPressed: () => Navigator.of(context).pop(true),
                    style: FilledButton.styleFrom(
                      backgroundColor: AppTheme.danger,
                      minimumSize: const Size(0, 48),
                    ),
                    child: const Text('Sign out'),
                  ),
                ),
              ],
            ),
          ],
        ),
      ),
    );

    if (confirmed == true) {
      await widget.onSignOut();
    }
  }

  void _selectTab(_StaffTab tab) {
    if (tab != _currentTab) Haptics.light();
    unawaited(widget.store.saveLastTab(tab.index));
    setState(() {
      _currentTab = tab;
      _pushedNavId = null;
      // Tab nav resets any pending scopes/ranges so the user gets the default
      // view when they hop tabs manually.
      _pendingTasksScope = '';
      _pendingTasksStatFilter = '';
      _pendingDtrView = false;
    });
    if (_isWide) {
      _contentNavKey.currentState?.popUntil((route) => route.isFirst);
      _tabNotifier.value = tab;
    } else {
      Navigator.of(context).maybePop();
    }
  }

  /// After a tab switch triggered outside [_selectTab] (e.g. dashboard
  /// shortcuts), sync the nested navigator: pop pushed pages and rebuild.
  void _syncContentNav() {
    if (!_isWide) return;
    _pushedNavId = null;
    _contentNavKey.currentState?.popUntil((route) => route.isFirst);
    _tabNotifier.value = _currentTab;
  }

  void _openTasksWithStatFilter(String statFilter) {
    Haptics.light();
    setState(() {
      _currentTab = _StaffTab.tasks;
      _pendingTasksScope = '';
      _pendingTasksStatFilter = statFilter;
      _tasksReopenKey++;
    });
    _syncContentNav();
  }

  void _onDestinationSelected(int index) {
    final tabs = _tabs;
    if (index < 0 || index >= tabs.length) return;
    _selectTab(tabs[index]);
  }

  void _openForwardedTasks() {
    Haptics.light();
    setState(() {
      _currentTab = _StaffTab.tasks;
      _pendingTasksScope = 'forwarded';
      _pendingTasksStatFilter = '';
      _tasksReopenKey++;
    });
    _syncContentNav();
  }

  void _openMyDtr() {
    Haptics.light();
    setState(() {
      _currentTab = _StaffTab.attendance;
      _pendingDtrView = true;
      _attendanceReopenKey++;
    });
    _syncContentNav();
  }

  Future<void> _openProfile({bool openPhotoPicker = false}) async {
    Haptics.light();
    await _pushContent(
      StaffProfileScreen(
        session: widget.session,
        onAvatarUpdated: widget.onAvatarUpdated,
        openPhotoPicker: openPhotoPicker,
      ),
    );
  }

  Future<void> _openMyDTR() async {
    Haptics.light();
    await _pushContent(MyDtrScreen(session: widget.session), navId: 'my-dtr');
  }

  Future<void> _openCalendar() async {
    Haptics.light();
    // Desktop gets the real month/week/day calendar; the year-at-a-glance
    // grid stays for mobile where it suits the narrow canvas.
    await _pushContent(
      AppTheme.isDesktop
          ? CalendarDashboardTab(session: widget.session)
          : CalendarScreen(session: widget.session),
      navId: 'calendar',
    );
  }

  Future<void> _openNotes() async {
    Haptics.light();
    await _pushContent(NotesScreen(session: widget.session), navId: 'notes');
  }

  Future<void> _openReminders() async {
    Haptics.light();
    await _pushContent(
      RemindersScreen(session: widget.session),
      navId: 'reminders',
    );
  }

  Future<void> _openNotifications() async {
    Haptics.light();
    await _pushContent(
      NotificationsScreen(session: widget.session),
      navId: 'notifications',
    );
    // Opening the list marks everything seen — refresh badges now rather
    // than waiting for the next poll cycle.
    _refreshBadges();
  }

  Future<void> _openAnnualGoals() async {
    Haptics.light();
    await _pushContent(
      AnnualGoalsScreen(session: widget.session),
      navId: 'annual-goals',
    );
  }

  Future<void> _openSupportDashboard() async {
    Haptics.light();
    await _pushContent(
      SupportDashboardScreen(session: widget.session),
      navId: 'support-dashboard',
    );
  }

  Future<void> _openSupportIssues({String scope = 'unassigned'}) async {
    Haptics.light();
    await _pushContent(
      SupportIssuesScreen(session: widget.session, initialScope: scope),
      navId: 'tickets',
    );
    if (!mounted) return;
    setState(() {
      _dashboardReopenKey++;
      _tasksReopenKey++;
    });
  }

  @override
  Widget build(BuildContext context) {
    final tabs = _tabs;
    final selectedIndex = tabs.indexOf(_currentTab).clamp(0, tabs.length - 1);

    final isWide = MediaQuery.sizeOf(context).width >= 1024;
    _isWide = isWide;

    final nav = StaffDrawer(
      session: widget.session,
      config: widget.config,
      sidebar: isWide,
      activeItemId:
          _pushedNavId ??
          switch (_currentTab) {
            _StaffTab.dashboard => 'dashboard',
            _StaffTab.attendance => 'attendance',
            _StaffTab.tasks =>
              _pendingTasksScope == 'forwarded' ? 'forwarded-tasks' : 'tasks',
            _StaffTab.account => 'account',
          },
      onSelectDashboard: () => _selectTab(_StaffTab.dashboard),
      onSelectAttendance: () => _selectTab(_StaffTab.attendance),
      onSelectTasks: () => _selectTab(_StaffTab.tasks),
      onSelectAccount: () => _selectTab(_StaffTab.account),
      onSelectMyDtr: _openMyDTR,
      onSelectCalendar: _openCalendar,
      onSelectNotes: _openNotes,
      onSelectReminders: _openReminders,
      onSelectAnnualGoals: _openAnnualGoals,
      onSelectSupportDashboard: _openSupportDashboard,
      onSelectForwardedTasks: _openForwardedTasks,
      onSelectTickets: () => _openSupportIssues(scope: 'open'),
      onSelectNotifications: _openNotifications,
      onSignOut: _confirmSignOut,
      onOpenCommandPalette: isWide ? () => _openPalette() : null,
      badges: _badges,
      collapsed: isWide && _sidebarCollapsed,
      onToggleCollapse: isWide ? _toggleSidebar : null,
    );

    final body = _animatedBody(_buildCurrentPage());

    if (isWide) {
      _navCommands = nav.navCommands();
      final mod = defaultTargetPlatform == TargetPlatform.macOS;
      final digits = [
        LogicalKeyboardKey.digit1,
        LogicalKeyboardKey.digit2,
        LogicalKeyboardKey.digit3,
        LogicalKeyboardKey.digit4,
        LogicalKeyboardKey.digit5,
        LogicalKeyboardKey.digit6,
        LogicalKeyboardKey.digit7,
        LogicalKeyboardKey.digit8,
        LogicalKeyboardKey.digit9,
      ];
      return CallbackShortcuts(
        bindings: {
          SingleActivator(LogicalKeyboardKey.keyK, meta: mod, control: !mod):
              _openPalette,
          SingleActivator(LogicalKeyboardKey.keyR, meta: mod, control: !mod):
              _reloadCurrentTab,
          SingleActivator(LogicalKeyboardKey.keyN, meta: mod, control: !mod):
              widget.session.hasTasks ? _newTask : _newNote,
          SingleActivator(LogicalKeyboardKey.slash, meta: mod, control: !mod):
              _showShortcutsHelp,
          for (var i = 0; i < _navCommands.length && i < 9; i++)
            SingleActivator(digits[i], meta: mod, control: !mod):
                _navCommands[i].onRun,
        },
        child: Focus(
          autofocus: true,
          child: Scaffold(
            key: _scaffoldKey,
            backgroundColor: AppTheme.background,
            body: Stack(
              children: [
                Column(
                  crossAxisAlignment: CrossAxisAlignment.stretch,
                  children: [
                    Expanded(
                      child: Row(
                        crossAxisAlignment: CrossAxisAlignment.stretch,
                        children: [
                          nav,
                          Expanded(
                            child: Padding(
                              padding: EdgeInsets.only(
                                top: AppTheme.titleBarInset,
                              ),
                              child: Align(
                                alignment: Alignment.topCenter,
                                child: ConstrainedBox(
                                  constraints: const BoxConstraints(
                                    maxWidth: 1360,
                                  ),
                                  child: Navigator(
                                    key: _contentNavKey,
                                    onGenerateRoute: (_) => MaterialPageRoute(
                                      builder: (_) => AnimatedBuilder(
                                        animation: _tabNotifier,
                                        builder: (_, _) =>
                                            _animatedBody(_buildCurrentPage()),
                                      ),
                                    ),
                                  ),
                                ),
                              ),
                            ),
                          ),
                        ],
                      ),
                    ),
                    DeskStatusBar(
                      domain: Uri.tryParse(widget.session.baseUrl)?.host ?? '',
                      actions: [
                        Text(
                          '${modKeyLabel}K Search',
                          style: const TextStyle(
                            fontSize: 11.5,
                            fontWeight: FontWeight.w600,
                            color: AppTheme.textMuted,
                          ),
                        ),
                      ],
                    ),
                  ],
                ),
                // macOS runs the content edge-to-edge under a hidden title
                // bar — this strip gives the window a draggable region.
                if (AppTheme.titleBarInset > 0)
                  Positioned(
                    top: 0,
                    left: 0,
                    right: 0,
                    height: AppTheme.titleBarInset,
                    child: const DragToMoveArea(
                      child: ColoredBox(color: Colors.transparent),
                    ),
                  ),
              ],
            ),
          ),
        ),
      );
    }

    return Scaffold(
      key: _scaffoldKey,
      backgroundColor: AppTheme.background,
      drawer: nav,
      body: body,
      bottomNavigationBar: Container(
        decoration: const BoxDecoration(
          border: Border(top: BorderSide(color: AppTheme.border)),
          color: Colors.white,
          boxShadow: [
            BoxShadow(
              color: Color(0x0A0F1E3A),
              blurRadius: 16,
              offset: Offset(0, -4),
            ),
          ],
        ),
        child: NavigationBar(
          selectedIndex: selectedIndex,
          onDestinationSelected: _onDestinationSelected,
          labelBehavior: NavigationDestinationLabelBehavior.alwaysShow,
          destinations: [for (final tab in tabs) _destinationFor(tab)],
        ),
      ),
    );
  }

  @override
  void dispose() {
    NotificationService.instance.openRequest.removeListener(
      _consumeOpenRequest,
    );
    _badgeTimer?.cancel();
    _tabNotifier.dispose();
    super.dispose();
  }

  Widget _animatedBody(Widget page) {
    return AnimatedSwitcher(
      duration: const Duration(milliseconds: 280),
      switchInCurve: Curves.easeOut,
      switchOutCurve: Curves.easeIn,
      transitionBuilder: (child, animation) {
        return FadeTransition(
          opacity: animation,
          child: SlideTransition(
            position: Tween<Offset>(
              begin: const Offset(0, 0.02),
              end: Offset.zero,
            ).animate(animation),
            child: child,
          ),
        );
      },
      child: KeyedSubtree(key: ValueKey(_currentTab), child: page),
    );
  }

  NavigationDestination _destinationFor(_StaffTab tab) {
    switch (tab) {
      case _StaffTab.dashboard:
        return const NavigationDestination(
          icon: Icon(LucideIcons.layoutGrid),
          selectedIcon: Icon(LucideIcons.layoutGrid),
          label: 'Dashboard',
        );
      case _StaffTab.attendance:
        return const NavigationDestination(
          icon: Icon(LucideIcons.calendarDays),
          selectedIcon: Icon(LucideIcons.calendarDays),
          label: 'Attendance',
        );
      case _StaffTab.tasks:
        return const NavigationDestination(
          icon: Icon(LucideIcons.listChecks),
          selectedIcon: Icon(LucideIcons.listChecks),
          label: 'Tasks',
        );
      case _StaffTab.account:
        return const NavigationDestination(
          icon: Icon(LucideIcons.circleUser),
          selectedIcon: Icon(LucideIcons.circleUser),
          label: 'Account',
        );
    }
  }

  Widget _buildCurrentPage() {
    switch (_currentTab) {
      case _StaffTab.dashboard:
        // Workspaces configured for the calendar dashboard get a pure
        // full-month calendar in place of the snapshot widgets.
        if (widget.session.dashboardIsCalendar) {
          return CalendarDashboardTab(
            key: ValueKey('calendar-dashboard-$_dashboardReopenKey'),
            session: widget.session,
            onMenu: _isWide
                ? null
                : () => _scaffoldKey.currentState?.openDrawer(),
          );
        }
        return StaffDashboardTab(
          key: ValueKey('dashboard-$_dashboardReopenKey'),
          session: widget.session,
          onMenu: _isWide
              ? null
              : () => _scaffoldKey.currentState?.openDrawer(),
          onOpenAttendance: () => _selectTab(_StaffTab.attendance),
          onOpenTasks: () => _selectTab(_StaffTab.tasks),
          onOpenMyDtr: _openMyDtr,
          onOpenForwardedTasks: _openForwardedTasks,
          onOpenUnassignedTickets: () =>
              _openSupportIssues(scope: 'unassigned'),
          onOpenSupportTickets: () => _openSupportIssues(scope: 'open'),
          onOpenReminders: _openReminders,
          onOpenCalendar: _openCalendar,
          onOpenNotes: _openNotes,
          onOpenTasksWithFilter: _openTasksWithStatFilter,
        );
      case _StaffTab.attendance:
        return StaffAttendanceTab(
          key: ValueKey('attendance-$_attendanceReopenKey'),
          session: widget.session,
          onMenu: _isWide
              ? null
              : () => _scaffoldKey.currentState?.openDrawer(),
          dtrMonthView: _pendingDtrView,
        );
      case _StaffTab.tasks:
        return StaffTasksTab(
          key: ValueKey('tasks-$_tasksReopenKey'),
          session: widget.session,
          onMenu: _isWide
              ? null
              : () => _scaffoldKey.currentState?.openDrawer(),
          initialScope: _pendingTasksScope,
          initialStatFilter: _pendingTasksStatFilter,
        );
      case _StaffTab.account:
        return StaffAccountTab(
          session: widget.session,
          config: widget.config,
          onMenu: _isWide
              ? null
              : () => _scaffoldKey.currentState?.openDrawer(),
          onSignOut: _confirmSignOut,
          onOpenMyProfile: () => _openProfile(),
          store: widget.store,
        );
    }
  }
}
