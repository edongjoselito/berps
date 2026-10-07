import 'dart:async';

import 'package:flutter/material.dart';
import 'package:lucide_icons_flutter/lucide_icons.dart';
import 'package:window_manager/window_manager.dart';

import '../../../core/theme/app_theme.dart';
import '../../../core/utils/haptics.dart';
import '../../../core/widgets/brand_logo.dart';
import '../../../core/widgets/desk_nav.dart';
import '../../../core/widgets/desktop_kit.dart';
import '../../../core/widgets/staff_avatar.dart';
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
  String _desktopNavId = 'dashboard';
  bool _sidebarCollapsed = false;

  @override
  void initState() {
    super.initState();
    _sidebarCollapsed = widget.store.readSidebarCollapsed();
    if (AppTheme.isDesktop) {
      AppTheme.compactDensity.value = widget.store.readDensityCompact();
    }
  }

  void _openDrawer() => _scaffoldKey.currentState?.openDrawer();

  void _toggleSidebar() {
    setState(() => _sidebarCollapsed = !_sidebarCollapsed);
    unawaited(widget.store.saveSidebarCollapsed(_sidebarCollapsed));
  }

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
    if (confirmed == true) await widget.onSignOut();
  }

  // ── Desktop shell ───────────────────────────────────────────────────────

  void _selectDesktop(String id) {
    if (id != _desktopNavId) Haptics.light();
    setState(() => _desktopNavId = id);
  }

  List<DeskNavSectionSpec> get _desktopSections => [
    DeskNavSectionSpec(
      label: 'Main',
      items: [
        DeskNavItemSpec(
          id: 'dashboard',
          icon: LucideIcons.layoutGrid,
          label: 'Dashboard',
        ),
        DeskNavItemSpec(
          id: 'tasks',
          icon: LucideIcons.listChecks,
          label: 'Tasks',
        ),
        DeskNavItemSpec(
          id: 'clients',
          icon: LucideIcons.users,
          label: 'Clients',
        ),
      ],
    ),
    DeskNavSectionSpec(
      label: 'Workforce',
      items: [
        DeskNavItemSpec(
          id: 'employee-tasks',
          icon: LucideIcons.users,
          label: 'Employee Tasks',
        ),
        DeskNavItemSpec(
          id: 'accomplishments',
          icon: LucideIcons.trophy,
          label: 'Accomplishments',
        ),
        DeskNavItemSpec(
          id: 'employee-accomplishment',
          icon: LucideIcons.userSearch,
          label: 'Employee Report',
        ),
      ],
    ),
    DeskNavSectionSpec(
      label: 'Attendance',
      items: [
        DeskNavItemSpec(
          id: 'attendance',
          icon: LucideIcons.calendarDays,
          label: 'Attendance List',
        ),
        DeskNavItemSpec(
          id: 'dtr',
          icon: LucideIcons.calendarCheck,
          label: 'Employee DTR',
        ),
      ],
    ),
    DeskNavSectionSpec(
      label: 'Workspace',
      items: [
        DeskNavItemSpec(
          id: 'calendar',
          icon: LucideIcons.calendarDays,
          label: 'Calendar',
        ),
        DeskNavItemSpec(
          id: 'more',
          icon: LucideIcons.slidersHorizontal,
          label: 'Settings',
        ),
      ],
    ),
  ];

  Widget _desktopPage() => switch (_desktopNavId) {
    'tasks' => AdminTasksTab(session: widget.session, onMenu: _openDrawer),
    'clients' => AdminClientsTab(session: widget.session, onMenu: _openDrawer),
    'employee-tasks' => EmployeeTasksScreen(session: widget.session),
    'accomplishments' => AdminAccomplishmentsScreen(session: widget.session),
    'employee-accomplishment' => EmployeeAccomplishmentScreen(
      session: widget.session,
    ),
    'attendance' => AdminAttendanceScreen(session: widget.session),
    'dtr' => EmpDtrScreen(session: widget.session),
    'calendar' => CalendarScreen(session: widget.session),
    'more' => AdminMoreTab(
      session: widget.session,
      config: widget.config,
      onMenu: _openDrawer,
      onSignOut: _confirmSignOut,
    ),
    _ => AdminDashboardTab(
      session: widget.session,
      onMenu: _openDrawer,
      onOpenTasks: () => _selectDesktop('tasks'),
      onOpenClients: () => _selectDesktop('clients'),
    ),
  };

  Widget _sidebarFooter(bool collapsed) {
    final session = widget.session;
    if (collapsed) {
      return Padding(
        padding: const EdgeInsets.fromLTRB(0, 10, 0, 12),
        child: Column(
          children: [
            Tooltip(
              message: session.formalName,
              child: InkWell(
                onTap: () => _selectDesktop('more'),
                borderRadius: BorderRadius.circular(10),
                hoverColor: DeskNavColors.hoverFill,
                child: Padding(
                  padding: const EdgeInsets.all(4),
                  child: StaffAvatar(
                    url: session.avatarUrl,
                    size: 34,
                    radius: 10,
                    background: Colors.white,
                    placeholderColor: AppTheme.primary,
                  ),
                ),
              ),
            ),
            const SizedBox(height: 6),
            IconButton(
              tooltip: 'Sign out',
              onPressed: _confirmSignOut,
              hoverColor: DeskNavColors.hoverFill,
              visualDensity: VisualDensity.compact,
              icon: const Icon(
                LucideIcons.logOut,
                size: 17,
                color: DeskNavColors.textDim,
              ),
            ),
          ],
        ),
      );
    }
    return Padding(
      padding: const EdgeInsets.fromLTRB(12, 10, 8, 12),
      child: Row(
        children: [
          Expanded(
            child: InkWell(
              onTap: () => _selectDesktop('more'),
              borderRadius: BorderRadius.circular(10),
              hoverColor: DeskNavColors.hoverFill,
              child: Padding(
                padding: const EdgeInsets.all(6),
                child: Row(
                  children: [
                    StaffAvatar(
                      url: session.avatarUrl,
                      size: 34,
                      radius: 10,
                      background: Colors.white,
                      placeholderColor: AppTheme.primary,
                    ),
                    const SizedBox(width: 10),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            session.formalName,
                            maxLines: 1,
                            overflow: TextOverflow.ellipsis,
                            style: const TextStyle(
                              color: Colors.white,
                              fontSize: 13,
                              fontWeight: FontWeight.w700,
                            ),
                          ),
                          const SizedBox(height: 2),
                          const Text(
                            'Administrator',
                            maxLines: 1,
                            overflow: TextOverflow.ellipsis,
                            style: TextStyle(
                              color: DeskNavColors.textDim,
                              fontSize: 11.5,
                              fontWeight: FontWeight.w600,
                            ),
                          ),
                        ],
                      ),
                    ),
                  ],
                ),
              ),
            ),
          ),
          IconButton(
            tooltip: 'Sign out',
            onPressed: _confirmSignOut,
            hoverColor: DeskNavColors.hoverFill,
            icon: const Icon(
              LucideIcons.logOut,
              size: 17,
              color: DeskNavColors.textDim,
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildDesktopShell() {
    final content = AnimatedSwitcher(
      duration: const Duration(milliseconds: 220),
      transitionBuilder: (child, animation) =>
          FadeTransition(opacity: animation, child: child),
      child: KeyedSubtree(
        key: ValueKey(_desktopNavId),
        child: DeskRootScope(child: _desktopPage()),
      ),
    );

    return Scaffold(
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
                    DeskSideNav(
                      logo: Container(
                        width: 30,
                        height: 30,
                        padding: const EdgeInsets.all(4),
                        decoration: BoxDecoration(
                          color: Colors.white,
                          borderRadius: BorderRadius.circular(8),
                        ),
                        child: BrandLogo(
                          url: widget.config?.logoUrl ?? '',
                          size: 22,
                          framed: false,
                        ),
                      ),
                      title: 'BERPS',
                      sections: _desktopSections,
                      activeId: _desktopNavId,
                      onSelect: _selectDesktop,
                      collapsed: _sidebarCollapsed,
                      onToggleCollapse: _toggleSidebar,
                      footerBuilder: _sidebarFooter,
                    ),
                    Expanded(
                      child: Padding(
                        padding: EdgeInsets.only(top: AppTheme.titleBarInset),
                        child: Align(
                          alignment: Alignment.topCenter,
                          child: ConstrainedBox(
                            constraints: const BoxConstraints(maxWidth: 1360),
                            child: content,
                          ),
                        ),
                      ),
                    ),
                  ],
                ),
              ),
              DeskStatusBar(
                domain: Uri.tryParse(widget.session.baseUrl)?.host ?? '',
                actions: const [
                  Text(
                    'Admin console',
                    style: TextStyle(
                      fontSize: 11.5,
                      fontWeight: FontWeight.w600,
                      color: AppTheme.textMuted,
                    ),
                  ),
                ],
              ),
            ],
          ),
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
    );
  }

  String get _activeItemId => switch (_currentIndex) {
    0 => 'dashboard',
    1 => 'tasks',
    2 => 'clients',
    _ => 'more',
  };

  @override
  Widget build(BuildContext context) {
    if (AppTheme.isDesktop) return _buildDesktopShell();
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
              icon: Icon(LucideIcons.layoutGrid),
              selectedIcon: Icon(LucideIcons.layoutGrid),
              label: 'Dashboard',
            ),
            NavigationDestination(
              icon: Icon(LucideIcons.listChecks),
              selectedIcon: Icon(LucideIcons.listChecks),
              label: 'Tasks',
            ),
            NavigationDestination(
              icon: Icon(LucideIcons.users),
              selectedIcon: Icon(LucideIcons.users),
              label: 'Clients',
            ),
            NavigationDestination(
              icon: Icon(LucideIcons.ellipsisVertical),
              selectedIcon: Icon(LucideIcons.ellipsisVertical),
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
