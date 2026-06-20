import 'package:flutter/material.dart';
import 'package:phosphor_flutter/phosphor_flutter.dart';

import '../../../core/theme/app_theme.dart';
import '../../../core/utils/haptics.dart';
import '../../auth/data/session_store.dart';
import '../../auth/domain/mobile_config.dart';
import '../../auth/domain/staff_session.dart';
import '../../calendar/presentation/calendar_screen.dart';
import '../presentation/admin_accomplishments_screen.dart';
import '../presentation/admin_attendance_screen.dart';
import '../presentation/admin_clients_tab.dart';
import '../presentation/admin_dashboard_tab.dart';
import '../presentation/admin_more_tab.dart';
import '../presentation/admin_tasks_tab.dart';
import '../presentation/emp_dtr_screen.dart';
import '../presentation/employee_accomplishment_screen.dart';
import '../presentation/employee_tasks_screen.dart';
import 'admin_drawer.dart';

/// Admin shell — single Scaffold owning the drawer + bottom nav, mirroring the
/// staff shell. The hamburger in each tab header opens the drawer.
class AdminHomeScreen extends StatefulWidget {
  const AdminHomeScreen({
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
  State<AdminHomeScreen> createState() => _AdminHomeScreenState();
}

class _AdminHomeScreenState extends State<AdminHomeScreen> {
  final _scaffoldKey = GlobalKey<ScaffoldState>();
  int _currentIndex = 0;

  void _openDrawer() => _scaffoldKey.currentState?.openDrawer();

  void _selectIndex(int index) {
    if (index != _currentIndex) Haptics.light();
    setState(() => _currentIndex = index);
  }

  void _push(Widget screen) {
    Haptics.light();
    Navigator.of(context).push(MaterialPageRoute(builder: (_) => screen));
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
    if (confirmed == true) await widget.onSignOut();
  }

  String get _activeItemId => switch (_currentIndex) {
    0 => 'dashboard',
    1 => 'tasks',
    2 => 'clients',
    _ => 'more',
  };

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      key: _scaffoldKey,
      backgroundColor: AppTheme.background,
      drawer: AdminDrawer(
        session: widget.session,
        config: widget.config,
        activeItemId: _activeItemId,
        onSelectDashboard: () => _selectIndex(0),
        onSelectTasks: () => _selectIndex(1),
        onSelectClients: () => _selectIndex(2),
        onSelectEmployeeTasks: () =>
            _push(EmployeeTasksScreen(session: widget.session)),
        onSelectAccomplishments: () =>
            _push(AdminAccomplishmentsScreen(session: widget.session)),
        onSelectEmployeeAccomplishment: () =>
            _push(EmployeeAccomplishmentScreen(session: widget.session)),
        onSelectAttendance: () =>
            _push(AdminAttendanceScreen(session: widget.session)),
        onSelectDtr: () => _push(EmpDtrScreen(session: widget.session)),
        onSelectCalendar: () => _push(CalendarScreen(session: widget.session)),
        onSignOut: _confirmSignOut,
      ),
      body: AnimatedSwitcher(
        duration: const Duration(milliseconds: 260),
        transitionBuilder: (child, animation) => FadeTransition(
          opacity: animation,
          child: SlideTransition(
            position: Tween<Offset>(
              begin: const Offset(0, 0.02),
              end: Offset.zero,
            ).animate(animation),
            child: child,
          ),
        ),
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
              icon: Icon(PhosphorIconsRegular.listChecks),
              selectedIcon: Icon(PhosphorIconsFill.listChecks),
              label: 'Tasks',
            ),
            NavigationDestination(
              icon: Icon(PhosphorIconsRegular.usersThree),
              selectedIcon: Icon(PhosphorIconsFill.usersThree),
              label: 'Clients',
            ),
            NavigationDestination(
              icon: Icon(PhosphorIconsRegular.dotsThreeOutline),
              selectedIcon: Icon(PhosphorIconsFill.dotsThreeOutline),
              label: 'More',
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildCurrentPage() {
    switch (_currentIndex) {
      case 0:
        return AdminDashboardTab(
          session: widget.session,
          onMenu: _openDrawer,
          onOpenTasks: () => _selectIndex(1),
          onOpenClients: () => _selectIndex(2),
        );
      case 1:
        return AdminTasksTab(session: widget.session, onMenu: _openDrawer);
      case 2:
        return AdminClientsTab(session: widget.session, onMenu: _openDrawer);
      case 3:
      default:
        return AdminMoreTab(
          session: widget.session,
          config: widget.config,
          onMenu: _openDrawer,
          onSignOut: _confirmSignOut,
        );
    }
  }
}
