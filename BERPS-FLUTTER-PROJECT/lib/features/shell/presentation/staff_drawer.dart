import 'package:flutter/material.dart';
import 'package:phosphor_flutter/phosphor_flutter.dart';

import '../../../core/theme/app_theme.dart';
import '../../../core/utils/haptics.dart';
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
    required this.onSignOut,
    this.activeItemId = 'dashboard',
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
  final Future<void> Function() onSignOut;
  final String activeItemId;

  @override
  Widget build(BuildContext context) {
    return Drawer(
      backgroundColor: AppTheme.surface,
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
            _Header(session: session, config: config),
            const SizedBox(height: 4),
            Expanded(
              child: ListView(
                padding: const EdgeInsets.symmetric(vertical: 4),
                children: [
                  const _SectionLabel('Navigation', PhosphorIconsBold.compass),
                  _NavItem(
                    id: 'dashboard',
                    icon: PhosphorIconsRegular.squaresFour,
                    iconActive: PhosphorIconsFill.squaresFour,
                    label: 'Dashboard',
                    activeItemId: activeItemId,
                    onTap: onSelectDashboard,
                  ),
                  if (session.hasAttendance)
                    _NavItem(
                      id: 'attendance',
                      icon: PhosphorIconsRegular.calendarDots,
                      iconActive: PhosphorIconsFill.calendarDots,
                      label: 'Attendance',
                      activeItemId: activeItemId,
                      onTap: onSelectAttendance,
                    ),
                  if (session.hasMyDtr)
                    _NavItem(
                      id: 'my-dtr',
                      icon: PhosphorIconsRegular.clock,
                      iconActive: PhosphorIconsFill.clock,
                      label: 'My DTR',
                      activeItemId: activeItemId,
                      onTap: onSelectMyDtr,
                    ),
                  if (session.hasTasks)
                    _NavItem(
                      id: 'tasks',
                      icon: PhosphorIconsRegular.listChecks,
                      iconActive: PhosphorIconsFill.listChecks,
                      label: 'Tasks',
                      activeItemId: activeItemId,
                      onTap: onSelectTasks,
                    ),
                  _NavItem(
                    id: 'account',
                    icon: PhosphorIconsRegular.userCircle,
                    iconActive: PhosphorIconsFill.userCircle,
                    label: 'Account',
                    activeItemId: activeItemId,
                    onTap: onSelectAccount,
                  ),
                  const _SectionLabel('Workspace', PhosphorIconsBold.buildings),
                  if (session.hasCalendar)
                    _NavItem(
                      id: 'calendar',
                      icon: PhosphorIconsRegular.calendarBlank,
                      iconActive: PhosphorIconsFill.calendarBlank,
                      label: 'Calendar',
                      activeItemId: activeItemId,
                      onTap: onSelectCalendar,
                    ),
                  if (session.hasReminders)
                    _NavItem(
                      id: 'reminders',
                      icon: PhosphorIconsRegular.bellRinging,
                      iconActive: PhosphorIconsFill.bellRinging,
                      label: 'Reminders',
                      activeItemId: activeItemId,
                      onTap: onSelectReminders,
                    ),
                  if (session.hasNotes)
                    _NavItem(
                      id: 'notes',
                      icon: PhosphorIconsRegular.notebook,
                      iconActive: PhosphorIconsFill.notebook,
                      label: 'Notes',
                      activeItemId: activeItemId,
                      onTap: onSelectNotes,
                    ),
                  if (session.hasRanking)
                    _NavItem(
                      id: 'annual-goals',
                      icon: PhosphorIconsRegular.trophy,
                      iconActive: PhosphorIconsFill.trophy,
                      label: 'Annual Goals',
                      activeItemId: activeItemId,
                      onTap: onSelectAnnualGoals,
                    ),
                  if (session.hasSupport)
                    _NavItem(
                      id: 'support-dashboard',
                      icon: PhosphorIconsRegular.chartLineUp,
                      iconActive: PhosphorIconsFill.chartLineUp,
                      label: 'Support Dashboard',
                      activeItemId: activeItemId,
                      onTap: onSelectSupportDashboard,
                    ),
                ],
              ),
            ),
            Divider(height: 1, color: AppTheme.border),
            Padding(
              padding: const EdgeInsets.fromLTRB(12, 8, 12, 6),
              child: Column(
                children: [
                  _DrawerAction(
                    icon: PhosphorIconsRegular.signOut,
                    label: 'Sign out',
                    danger: true,
                    onTap: () async {
                      Navigator.of(context).pop();
                      await onSignOut();
                    },
                  ),
                ],
              ),
            ),
            const _DrawerFooter(),
          ],
        ),
      ),
    );
  }
}

class _Header extends StatelessWidget {
  const _Header({required this.session, required this.config});

  final StaffSession session;
  final MobileConfig? config;

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
                child: Text(
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
                  background: AppTheme.surface,
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
                        style: TextStyle(
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
                          style: TextStyle(
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
  const _SectionLabel(this.text, [this.icon]);
  final String text;
  final IconData? icon;

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.fromLTRB(16, 18, 22, 8),
      child: Row(
        children: [
          if (icon != null) ...[
            Icon(icon, size: 13, color: AppTheme.textMuted),
            const SizedBox(width: 8),
          ],
          Text(
            text.toUpperCase(),
            style: TextStyle(
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
  const _NavItem({
    required this.id,
    required this.icon,
    required this.iconActive,
    required this.label,
    required this.activeItemId,
    required this.onTap,
  });

  final String id;
  final IconData icon;
  final IconData iconActive;
  final String label;
  final String activeItemId;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    final isActive = activeItemId == id;

    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 2),
      child: Material(
        color: isActive
            ? AppTheme.primary.withValues(alpha: 0.10)
            : Colors.transparent,
        borderRadius: BorderRadius.circular(14),
        child: InkWell(
          onTap: () {
            Navigator.of(context).pop();
            if (!isActive) {
              Haptics.light();
              onTap();
            }
          },
          borderRadius: BorderRadius.circular(14),
          child: Padding(
            padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 11),
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
                    isActive ? iconActive : icon,
                    size: 17,
                    color: isActive
                        ? AppTheme.primaryDark
                        : AppTheme.textSecondary,
                  ),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: Text(
                    label,
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
                    PhosphorIconsBold.caretRight,
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
            PhosphorIconsBold.shieldCheck,
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
