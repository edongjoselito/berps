import 'package:flutter/material.dart';
import 'package:lucide_icons_flutter/lucide_icons.dart';

import '../../../core/theme/app_theme.dart';
import '../../../core/utils/haptics.dart';
import '../../../core/widgets/brand_logo.dart';
import '../../../core/widgets/staff_avatar.dart';
import '../../auth/domain/mobile_config.dart';
import '../../auth/domain/staff_session.dart';

class StaffDrawer extends StatelessWidget {
  const StaffDrawer({
    super.key,
    required this.session,
    required this.config,
    required this.onSelectDashboard,
    required this.onSelectAttendance,
    required this.onSelectTasks,
    required this.onSelectAccount,
    required this.onSelectMyDtr,
    required this.onSelectCalendar,
    required this.onSelectNotes,
    required this.onSelectReminders,
    required this.onSelectAnnualGoals,
    required this.onSelectSupportDashboard,
    required this.onSelectUnassignedTickets,
    required this.onSelectForwardedTasks,
    required this.onSelectTickets,
    required this.onSignOut,
    this.activeItemId = 'dashboard',
    this.sidebar = false,
  });

  final StaffSession session;
  final MobileConfig? config;
  final VoidCallback onSelectDashboard;
  final VoidCallback onSelectAttendance;
  final VoidCallback onSelectTasks;
  final VoidCallback onSelectAccount;
  final VoidCallback onSelectMyDtr;
  final VoidCallback onSelectCalendar;
  final VoidCallback onSelectNotes;
  final VoidCallback onSelectReminders;
  final VoidCallback onSelectAnnualGoals;
  final VoidCallback onSelectSupportDashboard;
  final VoidCallback onSelectUnassignedTickets;
  final VoidCallback onSelectForwardedTasks;
  final VoidCallback onSelectTickets;
  final Future<void> Function() onSignOut;
  final String activeItemId;

  /// When true, renders as the docked desktop sidebar instead of a modal
  /// mobile [Drawer].
  final bool sidebar;

  /// Navigation model shared by the mobile drawer and the desktop sidebar,
  /// grouped into labelled sections and filtered by workspace features.
  List<_NavSection> _sections() {
    return [
      _NavSection('Main', LucideIcons.layoutGrid, [
        _NavSpec('dashboard', LucideIcons.layoutGrid, 'Dashboard',
            onSelectDashboard),
        if (session.hasTasks)
          _NavSpec('tasks', LucideIcons.listChecks, 'Tasks', onSelectTasks),
        if (session.hasSupport)
          _NavSpec('unassigned-tickets', LucideIcons.userMinus,
              'Unassigned Tickets', onSelectUnassignedTickets),
        if (session.hasForwardedTasks)
          _NavSpec('forwarded-tasks', LucideIcons.arrowLeftRight,
              'Forwarded Tasks', onSelectForwardedTasks),
        if (session.hasSupport)
          _NavSpec('tickets', LucideIcons.lifeBuoy, 'Tickets', onSelectTickets),
        if (session.hasSupport)
          _NavSpec('support-dashboard', LucideIcons.trendingUp,
              'Support Dashboard', onSelectSupportDashboard),
      ]),
      _NavSection('Productivity', LucideIcons.notebookText, [
        if (session.hasAttendance)
          _NavSpec('attendance', LucideIcons.calendarDays, 'Attendance',
              onSelectAttendance),
        if (session.hasMyDtr)
          _NavSpec('my-dtr', LucideIcons.clock, 'My DTR', onSelectMyDtr),
        if (session.hasCalendar)
          _NavSpec('calendar', LucideIcons.calendarRange, 'Calendar',
              onSelectCalendar),
        if (session.hasNotes)
          _NavSpec('notes', LucideIcons.notebookText, 'Notes', onSelectNotes),
        if (session.hasReminders)
          _NavSpec('reminders', LucideIcons.bellRing, 'Reminders',
              onSelectReminders),
        if (session.hasRanking)
          _NavSpec('annual-goals', LucideIcons.trophy, 'Annual Goals',
              onSelectAnnualGoals),
      ]),
      _NavSection('Account', LucideIcons.circleUser, [
        _NavSpec('account', LucideIcons.circleUser, 'Account', onSelectAccount),
      ]),
    ].where((s) => s.items.isNotEmpty).toList();
  }

  @override
  Widget build(BuildContext context) {
    if (sidebar) return _DesktopSidebar(drawer: this);

    return Drawer(
      backgroundColor: Colors.white,
      width: MediaQuery.of(context).size.width * 0.84,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.only(
          topRight: Radius.circular(28),
          bottomRight: Radius.circular(28),
        ),
      ),
      child: SafeArea(
        child: Column(
          children: [
            _Header(session: session),
            const SizedBox(height: 4),
            Expanded(
              child: ListView(
                padding: const EdgeInsets.symmetric(vertical: 4),
                children: [
                  for (final section in _sections()) ...[
                    _SectionLabel(section.label, section.icon),
                    for (final item in section.items)
                      _NavItem(spec: item, activeItemId: activeItemId),
                  ],
                ],
              ),
            ),
            const Divider(height: 1, color: AppTheme.border),
            Padding(
              padding: const EdgeInsets.fromLTRB(12, 8, 12, 6),
              child: Builder(
                builder: (context) => _DrawerAction(
                  icon: LucideIcons.logOut,
                  label: 'Sign out',
                  danger: true,
                  onTap: () async {
                    Navigator.of(context).pop();
                    await onSignOut();
                  },
                ),
              ),
            ),
            const _DrawerFooter(),
          ],
        ),
      ),
    );
  }
}

class _NavSection {
  const _NavSection(this.label, this.icon, this.items);
  final String label;
  final IconData icon;
  final List<_NavSpec> items;
}

class _NavSpec {
  const _NavSpec(this.id, this.icon, this.label, this.onTap);
  final String id;
  final IconData icon;
  final String label;
  final VoidCallback onTap;
}

// ── Desktop sidebar ─────────────────────────────────────────────────────────

class _Sidebar {
  static const background = Color(0xFF0E1A2D);
  static const divider = Color(0x14FFFFFF);
  static const textDim = Color(0x8CFFFFFF);
  static const textFaint = Color(0x59FFFFFF);
  static const activeFill = Color(0x1AFFFFFF);
  static const hoverFill = Color(0x0DFFFFFF);
}

class _DesktopSidebar extends StatelessWidget {
  const _DesktopSidebar({required this.drawer});

  final StaffDrawer drawer;

  @override
  Widget build(BuildContext context) {
    final session = drawer.session;
    return Container(
      width: 248,
      color: _Sidebar.background,
      child: SafeArea(
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            Padding(
              padding: const EdgeInsets.fromLTRB(20, 22, 20, 18),
              child: Row(
                children: [
                  Container(
                    width: 30,
                    height: 30,
                    padding: const EdgeInsets.all(4),
                    decoration: BoxDecoration(
                      color: Colors.white,
                      borderRadius: BorderRadius.circular(8),
                    ),
                    child: BrandLogo(
                      url: drawer.config?.logoUrl ?? '',
                      size: 22,
                      framed: false,
                    ),
                  ),
                  const SizedBox(width: 11),
                  const Text(
                    'BERPS',
                    style: TextStyle(
                      color: Colors.white,
                      fontSize: 16,
                      fontWeight: FontWeight.w800,
                      letterSpacing: 1.4,
                    ),
                  ),
                ],
              ),
            ),
            Expanded(
              child: ListView(
                padding: const EdgeInsets.fromLTRB(12, 0, 12, 12),
                children: [
                  for (final section in drawer._sections()) ...[
                    Padding(
                      padding: const EdgeInsets.fromLTRB(10, 16, 10, 6),
                      child: Text(
                        section.label.toUpperCase(),
                        style: const TextStyle(
                          color: _Sidebar.textFaint,
                          fontSize: 10.5,
                          fontWeight: FontWeight.w700,
                          letterSpacing: 1.4,
                        ),
                      ),
                    ),
                    for (final item in section.items)
                      _SidebarItem(
                        spec: item,
                        active: drawer.activeItemId == item.id,
                      ),
                  ],
                ],
              ),
            ),
            const Divider(height: 1, color: _Sidebar.divider),
            Padding(
              padding: const EdgeInsets.fromLTRB(12, 10, 8, 12),
              child: Row(
                children: [
                  Expanded(
                    child: InkWell(
                      onTap: drawer.onSelectAccount,
                      borderRadius: BorderRadius.circular(10),
                      hoverColor: _Sidebar.hoverFill,
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
                                  Text(
                                    session.position.isEmpty
                                        ? 'Staff'
                                        : session.position,
                                    maxLines: 1,
                                    overflow: TextOverflow.ellipsis,
                                    style: const TextStyle(
                                      color: _Sidebar.textDim,
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
                    onPressed: drawer.onSignOut,
                    hoverColor: _Sidebar.hoverFill,
                    icon: const Icon(
                      LucideIcons.logOut,
                      size: 17,
                      color: _Sidebar.textDim,
                    ),
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }
}

class _SidebarItem extends StatelessWidget {
  const _SidebarItem({required this.spec, required this.active});

  final _NavSpec spec;
  final bool active;

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 1),
      child: Material(
        color: active ? _Sidebar.activeFill : Colors.transparent,
        borderRadius: BorderRadius.circular(9),
        child: InkWell(
          onTap: active
              ? null
              : () {
                  Haptics.light();
                  spec.onTap();
                },
          borderRadius: BorderRadius.circular(9),
          hoverColor: _Sidebar.hoverFill,
          child: SizedBox(
            height: 38,
            child: Row(
              children: [
                AnimatedContainer(
                  duration: const Duration(milliseconds: 180),
                  width: 3,
                  height: active ? 18 : 0,
                  decoration: BoxDecoration(
                    color: AppTheme.primary,
                    borderRadius: BorderRadius.circular(2),
                  ),
                ),
                const SizedBox(width: 9),
                Icon(
                  spec.icon,
                  size: 17,
                  color: active ? Colors.white : _Sidebar.textDim,
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: Text(
                    spec.label,
                    maxLines: 1,
                    overflow: TextOverflow.ellipsis,
                    style: TextStyle(
                      color: active ? Colors.white : _Sidebar.textDim,
                      fontSize: 13.5,
                      fontWeight: active ? FontWeight.w700 : FontWeight.w600,
                    ),
                  ),
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }
}

// ── Mobile drawer pieces ────────────────────────────────────────────────────

class _Header extends StatelessWidget {
  const _Header({required this.session});

  final StaffSession session;

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.fromLTRB(18, 14, 18, 14),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              const Text(
                'BERPS',
                style: TextStyle(
                  fontWeight: FontWeight.w900,
                  fontSize: 13,
                  letterSpacing: 2.4,
                  color: AppTheme.primaryDark,
                ),
              ),
              const Spacer(),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                decoration: BoxDecoration(
                  color: AppTheme.surfaceMuted,
                  borderRadius: BorderRadius.circular(999),
                  border: Border.all(color: AppTheme.border),
                ),
                child: const Text(
                  'MOBILE',
                  style: TextStyle(
                    fontSize: 9,
                    fontWeight: FontWeight.w900,
                    letterSpacing: 1.4,
                    color: AppTheme.textSecondary,
                  ),
                ),
              ),
            ],
          ),
          const SizedBox(height: 14),
          Container(
            padding: const EdgeInsets.all(14),
            decoration: BoxDecoration(
              color: AppTheme.primarySoft,
              borderRadius: BorderRadius.circular(18),
              border: Border.all(color: AppTheme.border),
            ),
            child: Row(
              children: [
                StaffAvatar(
                  url: session.avatarUrl,
                  size: 52,
                  radius: 16,
                  background: Colors.white,
                  placeholderColor: AppTheme.primary,
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        session.formalName,
                        maxLines: 2,
                        overflow: TextOverflow.ellipsis,
                        style: const TextStyle(
                          color: AppTheme.textPrimary,
                          fontWeight: FontWeight.w900,
                          fontSize: 14.5,
                          letterSpacing: -0.2,
                          height: 1.2,
                        ),
                      ),
                      const SizedBox(height: 4),
                      Text(
                        session.position.isEmpty ? 'Staff' : session.position,
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis,
                        style: const TextStyle(
                          color: AppTheme.primaryDark,
                          fontSize: 11.5,
                          fontWeight: FontWeight.w700,
                        ),
                      ),
                      if (session.email.isNotEmpty) ...[
                        const SizedBox(height: 4),
                        Text(
                          session.email,
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                          style: const TextStyle(
                            color: AppTheme.textSecondary,
                            fontSize: 11,
                            fontWeight: FontWeight.w600,
                          ),
                        ),
                      ],
                    ],
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}

class _SectionLabel extends StatelessWidget {
  const _SectionLabel(this.text, this.icon);
  final String text;
  final IconData icon;

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.fromLTRB(16, 18, 22, 8),
      child: Row(
        children: [
          Icon(icon, size: 13, color: AppTheme.textMuted),
          const SizedBox(width: 8),
          Text(
            text.toUpperCase(),
            style: const TextStyle(
              fontSize: 10.5,
              fontWeight: FontWeight.w900,
              letterSpacing: 1.2,
              color: AppTheme.textMuted,
            ),
          ),
        ],
      ),
    );
  }
}

class _NavItem extends StatelessWidget {
  const _NavItem({required this.spec, required this.activeItemId});

  final _NavSpec spec;
  final String activeItemId;

  @override
  Widget build(BuildContext context) {
    final isActive = activeItemId == spec.id;

    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 2),
      child: Material(
        color: isActive
            ? AppTheme.primary.withValues(alpha: 0.10)
            : Colors.transparent,
        borderRadius: BorderRadius.circular(14),
        child: InkWell(
          onTap: () {
            Navigator.of(context).maybePop();
            if (!isActive) {
              Haptics.light();
              spec.onTap();
            }
          },
          borderRadius: BorderRadius.circular(14),
          child: Padding(
            padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 11),
            child: Row(
              children: [
                AnimatedContainer(
                  duration: const Duration(milliseconds: 220),
                  width: 34,
                  height: 34,
                  decoration: BoxDecoration(
                    color: isActive
                        ? AppTheme.primary.withValues(alpha: 0.14)
                        : AppTheme.surfaceMuted,
                    borderRadius: BorderRadius.circular(11),
                  ),
                  child: Icon(
                    spec.icon,
                    size: 17,
                    color: isActive
                        ? AppTheme.primaryDark
                        : AppTheme.textSecondary,
                  ),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: Text(
                    spec.label,
                    style: TextStyle(
                      color: isActive
                          ? AppTheme.textPrimary
                          : AppTheme.textSecondary,
                      fontSize: 13.5,
                      fontWeight: isActive ? FontWeight.w800 : FontWeight.w600,
                    ),
                  ),
                ),
                AnimatedOpacity(
                  duration: const Duration(milliseconds: 220),
                  opacity: isActive ? 1 : 0,
                  child: const Icon(
                    LucideIcons.chevronRight,
                    size: 12,
                    color: AppTheme.primaryDark,
                  ),
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }
}

class _DrawerAction extends StatelessWidget {
  const _DrawerAction({
    required this.icon,
    required this.label,
    required this.onTap,
    this.danger = false,
  });

  final IconData icon;
  final String label;
  final VoidCallback onTap;
  final bool danger;

  @override
  Widget build(BuildContext context) {
    final color = danger ? AppTheme.danger : AppTheme.textSecondary;
    return InkWell(
      onTap: onTap,
      borderRadius: BorderRadius.circular(12),
      child: Padding(
        padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 11),
        child: Row(
          children: [
            Icon(icon, size: 17, color: color),
            const SizedBox(width: 12),
            Text(
              label,
              style: TextStyle(
                fontSize: 13.5,
                fontWeight: FontWeight.w700,
                color: color,
              ),
            ),
          ],
        ),
      ),
    );
  }
}

class _DrawerFooter extends StatelessWidget {
  const _DrawerFooter();

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.fromLTRB(20, 6, 20, 12),
      child: Row(
        children: [
          Icon(
            LucideIcons.shieldCheck,
            size: 11,
            color: AppTheme.textMuted.withValues(alpha: 0.9),
          ),
          const SizedBox(width: 6),
          Text(
            'BERPS Mobile · v1.0',
            style: TextStyle(
              fontSize: 10.5,
              fontWeight: FontWeight.w800,
              letterSpacing: 0.4,
              color: AppTheme.textMuted.withValues(alpha: 0.9),
            ),
          ),
        ],
      ),
    );
  }
}
