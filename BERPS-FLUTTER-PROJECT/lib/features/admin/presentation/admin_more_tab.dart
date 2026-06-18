import 'package:flutter/material.dart';
import 'package:phosphor_flutter/phosphor_flutter.dart';

import '../../../core/theme/app_theme.dart';
import '../../../core/utils/haptics.dart';
import '../../../core/utils/responsive.dart';
import '../../../core/widgets/mobile_header.dart';
import '../../auth/domain/mobile_config.dart';
import '../../auth/domain/staff_session.dart';
import 'admin_accomplishments_screen.dart';
import 'admin_attendance_screen.dart';
import 'admin_widgets.dart';
import 'emp_dtr_screen.dart';
import 'employee_accomplishment_screen.dart';
import 'employee_tasks_screen.dart';

class AdminMoreTab extends StatelessWidget {
  const AdminMoreTab({
    super.key,
    required this.session,
    required this.config,
    required this.onMenu,
    required this.onSignOut,
  });

  final StaffSession session;
  final MobileConfig? config;
  final VoidCallback onMenu;
  final Future<void> Function() onSignOut;

  void _push(BuildContext context, Widget screen) {
    Haptics.light();
    Navigator.of(context).push(MaterialPageRoute(builder: (_) => screen));
  }

  @override
  Widget build(BuildContext context) {
    final gutter = context.gutter;
    return ListView(
      padding: EdgeInsets.fromLTRB(gutter, 12, gutter, 28),
      children: [
        AdminHeader(
          title: 'More',
          subtitle: 'Management & reports',
          onMenu: onMenu,
        ),
        const SizedBox(height: 18),
        _profileCard(),
        const SizedBox(height: 22),
        const MobileSectionLabel('Workforce'),
        const SizedBox(height: 10),
        _menuCard([
          _MenuItem(
            icon: PhosphorIconsFill.listChecks,
            color: AppTheme.primary,
            title: 'Employee tasks',
            subtitle: 'Pending tasks per employee',
            onTap: () => _push(context, EmployeeTasksScreen(session: session)),
          ),
          _MenuItem(
            icon: PhosphorIconsFill.trophy,
            color: AppTheme.accent,
            title: 'Accomplishments',
            subtitle: 'Closed tasks across the team',
            onTap: () =>
                _push(context, AdminAccomplishmentsScreen(session: session)),
          ),
          _MenuItem(
            icon: PhosphorIconsFill.userFocus,
            color: AppTheme.primaryDeeper,
            title: 'Employee accomplishment',
            subtitle: 'Per-employee report by period',
            onTap: () =>
                _push(context, EmployeeAccomplishmentScreen(session: session)),
          ),
        ]),
        const SizedBox(height: 22),
        const MobileSectionLabel('Attendance'),
        const SizedBox(height: 10),
        _menuCard([
          _MenuItem(
            icon: PhosphorIconsFill.clock,
            color: AppTheme.success,
            title: 'Attendance list',
            subtitle: 'Daily time logs (all staff)',
            onTap: () =>
                _push(context, AdminAttendanceScreen(session: session)),
          ),
          _MenuItem(
            icon: PhosphorIconsFill.calendarCheck,
            color: AppTheme.warning,
            title: 'Employee DTR',
            subtitle: 'Monthly daily time record',
            onTap: () => _push(context, EmpDtrScreen(session: session)),
          ),
        ]),
        const SizedBox(height: 22),
        const MobileSectionLabel('Account'),
        const SizedBox(height: 10),
        _menuCard([
          _MenuItem(
            icon: PhosphorIconsFill.signOut,
            color: AppTheme.danger,
            title: 'Sign out',
            subtitle: 'End this admin session',
            onTap: () {
              Haptics.light();
              onSignOut();
            },
          ),
        ]),
      ],
    );
  }

  Widget _profileCard() {
    return MobileSurfaceCard(
      child: Row(
        children: [
          Container(
            width: 54,
            height: 54,
            alignment: Alignment.center,
            decoration: BoxDecoration(
              color: AppTheme.primarySoft,
              borderRadius: BorderRadius.circular(16),
            ),
            child: const Icon(
              PhosphorIconsFill.shieldStar,
              color: AppTheme.primaryDark,
              size: 26,
            ),
          ),
          const SizedBox(width: 14),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  session.formalName,
                  maxLines: 1,
                  overflow: TextOverflow.ellipsis,
                  style: const TextStyle(
                    fontWeight: FontWeight.w900,
                    fontSize: 16,
                    color: AppTheme.textPrimary,
                  ),
                ),
                const SizedBox(height: 2),
                Text(
                  session.email.isNotEmpty ? session.email : session.username,
                  maxLines: 1,
                  overflow: TextOverflow.ellipsis,
                  style: const TextStyle(
                    fontSize: 12.5,
                    color: AppTheme.textSecondary,
                  ),
                ),
                const SizedBox(height: 6),
                const MobileInlineBadge(
                  icon: PhosphorIconsFill.shieldCheck,
                  label: 'Administrator',
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _menuCard(List<_MenuItem> items) {
    return Container(
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(20),
        border: Border.all(color: AppTheme.border),
        boxShadow: AppTheme.shadowSoft,
      ),
      child: Column(
        children: [
          for (var i = 0; i < items.length; i++) ...[
            if (i > 0)
              const Divider(height: 1, indent: 64, color: AppTheme.border),
            items[i],
          ],
        ],
      ),
    );
  }
}

class _MenuItem extends StatelessWidget {
  const _MenuItem({
    required this.icon,
    required this.color,
    required this.title,
    required this.subtitle,
    required this.onTap,
  });

  final IconData icon;
  final Color color;
  final String title;
  final String subtitle;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    return InkWell(
      onTap: onTap,
      borderRadius: BorderRadius.circular(20),
      child: Padding(
        padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 14),
        child: Row(
          children: [
            Container(
              width: 38,
              height: 38,
              alignment: Alignment.center,
              decoration: BoxDecoration(
                color: color.withValues(alpha: 0.12),
                borderRadius: BorderRadius.circular(11),
              ),
              child: Icon(icon, color: color, size: 19),
            ),
            const SizedBox(width: 14),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    title,
                    style: const TextStyle(
                      fontWeight: FontWeight.w800,
                      fontSize: 14.5,
                      color: AppTheme.textPrimary,
                    ),
                  ),
                  const SizedBox(height: 1),
                  Text(
                    subtitle,
                    style: const TextStyle(
                      fontSize: 12,
                      color: AppTheme.textSecondary,
                    ),
                  ),
                ],
              ),
            ),
            const Icon(
              PhosphorIconsBold.caretRight,
              size: 16,
              color: AppTheme.textMuted,
            ),
          ],
        ),
      ),
    );
  }
}
