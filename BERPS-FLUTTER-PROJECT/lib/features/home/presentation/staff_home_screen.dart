import 'package:flutter/material.dart';
import 'package:phosphor_flutter/phosphor_flutter.dart';

import '../../../core/theme/app_theme.dart';
import '../../../core/utils/haptics.dart';
import '../../attendance/presentation/staff_attendance_tab.dart';
import '../../auth/data/session_store.dart';
import '../../auth/domain/mobile_config.dart';
import '../../auth/domain/staff_session.dart';
import '../../calendar/presentation/calendar_screen.dart';
import '../../goals/presentation/annual_goals_screen.dart';
import '../../notes/presentation/notes_screen.dart';
import '../../reminders/presentation/reminders_screen.dart';
import '../../shell/presentation/staff_drawer.dart';
import '../../support/presentation/support_issues_screen.dart';
import '../../support_dashboard/presentation/support_dashboard_screen.dart';
import '../../tasks/presentation/staff_tasks_tab.dart';
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
  _StaffTab _currentTab = _StaffTab.dashboard;
  String _pendingTasksScope = '';
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
    final confirmed = await showDialog<bool>(
      context: context,
      barrierDismissible: false,
      builder: (context) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(24)),
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
                PhosphorIconsBold.signOut,
                color: AppTheme.danger,
                size: 28,
              ),
            ),
            const SizedBox(height: 18),
            Text(
              'Sign out?',
              style: TextStyle(
                fontWeight: FontWeight.w900,
                fontSize: 20,
                color: AppTheme.textPrimary,
              ),
            ),
            const SizedBox(height: 8),
            Text(
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
    setState(() {
      _currentTab = tab;
      // Tab nav resets any pending scopes/ranges so the user gets the default
      // view when they hop tabs manually.
      _pendingTasksScope = '';
      _pendingDtrView = false;
    });
    Navigator.of(context).maybePop();
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
      _tasksReopenKey++;
    });
  }

  void _openMyDtr() {
    Haptics.light();
    setState(() {
      _currentTab = _StaffTab.attendance;
      _pendingDtrView = true;
      _attendanceReopenKey++;
    });
  }

  Future<void> _openProfile({bool openPhotoPicker = false}) async {
    Haptics.light();
    await Navigator.of(context).push(
      MaterialPageRoute(
        builder: (_) => StaffProfileScreen(
          session: widget.session,
          onAvatarUpdated: widget.onAvatarUpdated,
          openPhotoPicker: openPhotoPicker,
        ),
      ),
    );
  }

  Future<void> _openMyDTR() async {
    Haptics.light();
    await Navigator.of(context).push(
      MaterialPageRoute(builder: (_) => MyDtrScreen(session: widget.session)),
    );
  }

  Future<void> _openCalendar() async {
    Haptics.light();
    await Navigator.of(context).push(
      MaterialPageRoute(
        builder: (_) => CalendarScreen(session: widget.session),
      ),
    );
  }

  Future<void> _openNotes() async {
    Haptics.light();
    await Navigator.of(context).push(
      MaterialPageRoute(builder: (_) => NotesScreen(session: widget.session)),
    );
  }

  Future<void> _openReminders() async {
    Haptics.light();
    await Navigator.of(context).push(
      MaterialPageRoute(
        builder: (_) => RemindersScreen(session: widget.session),
      ),
    );
  }

  Future<void> _openAnnualGoals() async {
    Haptics.light();
    await Navigator.of(context).push(
      MaterialPageRoute(
        builder: (_) => AnnualGoalsScreen(session: widget.session),
      ),
    );
  }

  Future<void> _openSupportDashboard() async {
    Haptics.light();
    await Navigator.of(context).push(
      MaterialPageRoute(
        builder: (_) => SupportDashboardScreen(session: widget.session),
      ),
    );
  }

  Future<void> _openSupportIssues({String scope = 'unassigned'}) async {
    Haptics.light();
    await Navigator.of(context).push(
      MaterialPageRoute(
        builder: (_) =>
            SupportIssuesScreen(session: widget.session, initialScope: scope),
      ),
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

    return Scaffold(
      key: _scaffoldKey,
      backgroundColor: AppTheme.background,
      drawer: StaffDrawer(
        session: widget.session,
        config: widget.config,
        activeItemId: switch (_currentTab) {
          _StaffTab.dashboard => 'dashboard',
          _StaffTab.attendance => 'attendance',
          _StaffTab.tasks => 'tasks',
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
        onSignOut: _confirmSignOut,
      ),
      body: AnimatedSwitcher(
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
        child: KeyedSubtree(
          key: ValueKey(_currentTab),
          child: _buildCurrentPage(),
        ),
      ),
      bottomNavigationBar: Container(
        decoration: BoxDecoration(
          border: Border(top: BorderSide(color: AppTheme.border)),
          color: AppTheme.surface,
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
          destinations: [
            for (final tab in tabs) _destinationFor(tab),
          ],
        ),
      ),
    );
  }

  NavigationDestination _destinationFor(_StaffTab tab) {
    switch (tab) {
      case _StaffTab.dashboard:
        return const NavigationDestination(
          icon: Icon(PhosphorIconsRegular.squaresFour),
          selectedIcon: Icon(PhosphorIconsFill.squaresFour),
          label: 'Dashboard',
        );
      case _StaffTab.attendance:
        return const NavigationDestination(
          icon: Icon(PhosphorIconsRegular.calendarDots),
          selectedIcon: Icon(PhosphorIconsFill.calendarDots),
          label: 'Attendance',
        );
      case _StaffTab.tasks:
        return const NavigationDestination(
          icon: Icon(PhosphorIconsRegular.listChecks),
          selectedIcon: Icon(PhosphorIconsFill.listChecks),
          label: 'Tasks',
        );
      case _StaffTab.account:
        return const NavigationDestination(
          icon: Icon(PhosphorIconsRegular.userCircle),
          selectedIcon: Icon(PhosphorIconsFill.userCircle),
          label: 'Account',
        );
    }
  }

  Widget _buildCurrentPage() {
    switch (_currentTab) {
      case _StaffTab.dashboard:
        return StaffDashboardTab(
          key: ValueKey('dashboard-$_dashboardReopenKey'),
          session: widget.session,
          onMenu: () => _scaffoldKey.currentState?.openDrawer(),
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
        );
      case _StaffTab.attendance:
        return StaffAttendanceTab(
          key: ValueKey('attendance-$_attendanceReopenKey'),
          session: widget.session,
          onMenu: () => _scaffoldKey.currentState?.openDrawer(),
          dtrMonthView: _pendingDtrView,
        );
      case _StaffTab.tasks:
        return StaffTasksTab(
          key: ValueKey('tasks-$_tasksReopenKey'),
          session: widget.session,
          onMenu: () => _scaffoldKey.currentState?.openDrawer(),
          initialScope: _pendingTasksScope,
        );
      case _StaffTab.account:
        return StaffAccountTab(
          session: widget.session,
          config: widget.config,
          onMenu: () => _scaffoldKey.currentState?.openDrawer(),
          onSignOut: _confirmSignOut,
          onOpenMyProfile: () => _openProfile(),
          store: widget.store,
        );
    }
  }
}
