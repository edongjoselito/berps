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
import '../../shell/presentation/staff_drawer.dart';
import '../../support/presentation/support_issues_screen.dart';
import '../../support_dashboard/presentation/support_dashboard_screen.dart';
import '../../tasks/presentation/staff_tasks_tab.dart';
import 'my_dtr_screen.dart';
import 'staff_account_tab.dart';
import 'staff_dashboard_tab.dart';
import 'staff_profile_screen.dart';

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
  int _currentIndex = 0;
  String _pendingTasksScope = '';
  bool _pendingDtrView = false;
  int _dashboardReopenKey = 0;
  int _tasksReopenKey = 0;
  int _attendanceReopenKey = 0;

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

  void _selectIndex(int index) {
    if (index != _currentIndex) Haptics.light();
    setState(() {
      _currentIndex = index;
      // Tab nav resets any pending scopes/ranges so the user gets the default
      // view when they hop tabs manually.
      _pendingTasksScope = '';
      _pendingDtrView = false;
    });
    Navigator.of(context).maybePop();
  }

  void _openForwardedTasks() {
    Haptics.light();
    setState(() {
      _currentIndex = 2;
      _pendingTasksScope = 'forwarded';
      _tasksReopenKey++;
    });
  }

  void _openMyDtr() {
    Haptics.light();
    setState(() {
      _currentIndex = 1;
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
    return Scaffold(
      key: _scaffoldKey,
      backgroundColor: AppTheme.background,
      drawer: StaffDrawer(
        session: widget.session,
        config: widget.config,
        activeItemId: switch (_currentIndex) {
          0 => 'dashboard',
          1 => 'attendance',
          2 => 'tasks',
          _ => 'account',
        },
        onSelectDashboard: () => _selectIndex(0),
        onSelectAttendance: () => _selectIndex(1),
        onSelectTasks: () => _selectIndex(2),
        onSelectAccount: () => _selectIndex(3),
        onSelectMyDtr: _openMyDTR,
        onSelectCalendar: _openCalendar,
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
          key: ValueKey(_currentIndex),
          child: _buildCurrentPage(),
        ),
      ),
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
          selectedIndex: _currentIndex,
          onDestinationSelected: _selectIndex,
          labelBehavior: NavigationDestinationLabelBehavior.alwaysShow,
          destinations: const [
            NavigationDestination(
              icon: Icon(PhosphorIconsRegular.squaresFour),
              selectedIcon: Icon(PhosphorIconsFill.squaresFour),
              label: 'Dashboard',
            ),
            NavigationDestination(
              icon: Icon(PhosphorIconsRegular.calendarDots),
              selectedIcon: Icon(PhosphorIconsFill.calendarDots),
              label: 'Attendance',
            ),
            NavigationDestination(
              icon: Icon(PhosphorIconsRegular.listChecks),
              selectedIcon: Icon(PhosphorIconsFill.listChecks),
              label: 'Tasks',
            ),
            NavigationDestination(
              icon: Icon(PhosphorIconsRegular.userCircle),
              selectedIcon: Icon(PhosphorIconsFill.userCircle),
              label: 'Account',
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildCurrentPage() {
    switch (_currentIndex) {
      case 0:
        return StaffDashboardTab(
          key: ValueKey('dashboard-$_dashboardReopenKey'),
          session: widget.session,
          onMenu: () => _scaffoldKey.currentState?.openDrawer(),
          onOpenAttendance: () => _selectIndex(1),
          onOpenTasks: () => _selectIndex(2),
          onOpenMyDtr: _openMyDtr,
          onOpenForwardedTasks: _openForwardedTasks,
          onOpenUnassignedTickets: () =>
              _openSupportIssues(scope: 'unassigned'),
          onOpenSupportTickets: () => _openSupportIssues(scope: 'open'),
        );
      case 1:
        return StaffAttendanceTab(
          key: ValueKey('attendance-$_attendanceReopenKey'),
          session: widget.session,
          onMenu: () => _scaffoldKey.currentState?.openDrawer(),
          dtrMonthView: _pendingDtrView,
        );
      case 2:
        return StaffTasksTab(
          key: ValueKey('tasks-$_tasksReopenKey'),
          session: widget.session,
          onMenu: () => _scaffoldKey.currentState?.openDrawer(),
          initialScope: _pendingTasksScope,
        );
      case 3:
      default:
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
