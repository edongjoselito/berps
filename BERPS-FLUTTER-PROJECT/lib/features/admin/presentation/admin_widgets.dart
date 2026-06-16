import 'package:flutter/material.dart';
import 'package:phosphor_flutter/phosphor_flutter.dart';

import '../../../core/theme/app_theme.dart';
import '../../../core/utils/responsive.dart';
import '../../../core/widgets/mobile_header.dart';
import '../../../core/widgets/staff_avatar.dart';
import '../../auth/domain/staff_session.dart';
import '../domain/admin_models.dart';

/// Shared building blocks for the admin screens. These deliberately mirror the
/// staff app's design language (lib/features/home/presentation/*) so the two
/// experiences feel identical.

/// Screen header. On bottom-nav tabs pass [onMenu] (renders the hamburger that
/// opens the drawer, exactly like the staff tabs). On pushed detail screens
/// pass [onBack] (renders a back caret).
class AdminHeader extends StatelessWidget {
  const AdminHeader({
    super.key,
    required this.title,
    this.subtitle,
    this.onMenu,
    this.onBack,
    this.trailing,
    this.trailingIcon,
    this.onTrailingTap,
  });

  final String title;
  final String? subtitle;
  final VoidCallback? onMenu;
  final VoidCallback? onBack;
  final Widget? trailing;
  final IconData? trailingIcon;
  final VoidCallback? onTrailingTap;

  @override
  Widget build(BuildContext context) {
    final IconData leading = onBack != null
        ? PhosphorIconsBold.caretLeft
        : PhosphorIconsBold.list;
    return SafeArea(
      bottom: false,
      child: MobileHeader(
        title: title,
        subtitle: subtitle,
        leadingIcon: leading,
        onLeadingTap: onBack ?? onMenu ?? () {},
        trailing: trailing,
        trailingIcon: trailingIcon,
        onTrailingTap: onTrailingTap,
      ),
    );
  }
}

/// White greeting card with avatar, used at the top of the dashboard — matches
/// the staff dashboard's `_GreetingCard`.
class AdminGreetingCard extends StatelessWidget {
  const AdminGreetingCard({super.key, required this.session});
  final StaffSession session;

  static const _months = [
    'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun',
    'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec',
  ];
  static const _days = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];

  String _greeting() {
    final h = DateTime.now().hour;
    if (h < 12) return 'Good morning';
    if (h < 18) return 'Good afternoon';
    return 'Good evening';
  }

  IconData _greetingIcon() {
    final h = DateTime.now().hour;
    if (h < 12) return PhosphorIconsFill.sunHorizon;
    if (h < 18) return PhosphorIconsFill.sun;
    return PhosphorIconsFill.moonStars;
  }

  @override
  Widget build(BuildContext context) {
    final now = DateTime.now();
    final today = '${_days[now.weekday - 1]} · ${_months[now.month - 1]} ${now.day}';
    return Container(
      padding: const EdgeInsets.all(18),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(20),
        border: Border.all(color: AppTheme.border),
        boxShadow: AppTheme.shadowSoft,
      ),
      child: Row(
        children: [
          StaffAvatar(
            url: session.avatarUrl,
            size: 56,
            radius: 18,
            background: AppTheme.primarySoft,
            placeholderColor: AppTheme.primary,
            placeholderSize: 30,
          ),
          const SizedBox(width: 14),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  children: [
                    Icon(_greetingIcon(), size: 13, color: AppTheme.accent),
                    const SizedBox(width: 6),
                    Text(
                      _greeting(),
                      style: const TextStyle(
                        color: AppTheme.textSecondary,
                        fontWeight: FontWeight.w700,
                        fontSize: 12,
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 4),
                Text(
                  session.formalName,
                  maxLines: 1,
                  overflow: TextOverflow.ellipsis,
                  style: TextStyle(
                    fontSize: context.isSmallPhone ? 17 : 18.5,
                    fontWeight: FontWeight.w900,
                    color: AppTheme.textPrimary,
                    letterSpacing: -0.3,
                    height: 1.2,
                  ),
                ),
                const SizedBox(height: 4),
                Row(
                  children: [
                    Flexible(
                      child: Text(
                        session.position.isEmpty ? 'Administrator' : session.position,
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis,
                        style: const TextStyle(
                          color: AppTheme.primaryDark,
                          fontSize: 11.5,
                          fontWeight: FontWeight.w700,
                        ),
                      ),
                    ),
                    Container(
                      width: 3,
                      height: 3,
                      margin: const EdgeInsets.symmetric(horizontal: 6),
                      decoration: const BoxDecoration(
                        color: AppTheme.textMuted,
                        shape: BoxShape.circle,
                      ),
                    ),
                    Flexible(
                      child: Text(
                        today,
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis,
                        style: const TextStyle(
                          color: AppTheme.textMuted,
                          fontSize: 11.5,
                          fontWeight: FontWeight.w600,
                        ),
                      ),
                    ),
                  ],
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}

/// Section header with the accent bar — matches the staff `_SectionHeader`.
class AdminSectionHeader extends StatelessWidget {
  const AdminSectionHeader({super.key, required this.title, this.action});
  final String title;
  final Widget? action;

  @override
  Widget build(BuildContext context) {
    return Row(
      children: [
        Container(
          width: 3,
          height: 16,
          decoration: BoxDecoration(
            color: AppTheme.primary,
            borderRadius: BorderRadius.circular(2),
          ),
        ),
        const SizedBox(width: 10),
        Expanded(
          child: Text(
            title,
            style: const TextStyle(
              fontSize: 14.5,
              fontWeight: FontWeight.w900,
              color: AppTheme.textPrimary,
              letterSpacing: -0.1,
            ),
          ),
        ),
        ?action,
      ],
    );
  }
}

class AdminMetricCardData {
  const AdminMetricCardData({
    required this.label,
    required this.value,
    required this.icon,
    required this.accent,
    this.onTap,
  });
  final String label;
  final String value;
  final IconData icon;
  final Color accent;
  final VoidCallback? onTap;
}

/// Two-up metric grid using a [Wrap] of fixed-width, intrinsic-height tiles.
/// This is the exact staff approach and is overflow-proof (no aspect ratio).
class AdminMetricGrid extends StatelessWidget {
  const AdminMetricGrid({super.key, required this.cards});
  final List<AdminMetricCardData> cards;

  @override
  Widget build(BuildContext context) {
    return LayoutBuilder(
      builder: (context, constraints) {
        final tileWidth = (constraints.maxWidth - 12) / 2;
        return Wrap(
          spacing: 12,
          runSpacing: 12,
          children: [
            for (final card in cards)
              SizedBox(width: tileWidth, child: _AdminMetricCard(data: card)),
          ],
        );
      },
    );
  }
}

class _AdminMetricCard extends StatelessWidget {
  const _AdminMetricCard({required this.data});
  final AdminMetricCardData data;

  @override
  Widget build(BuildContext context) {
    return InkWell(
      onTap: data.onTap,
      borderRadius: BorderRadius.circular(18),
      child: Container(
        padding: const EdgeInsets.all(14),
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(18),
          border: Border.all(color: AppTheme.border),
          boxShadow: AppTheme.shadowSoft,
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              children: [
                Container(
                  width: 32,
                  height: 32,
                  decoration: BoxDecoration(
                    color: data.accent.withValues(alpha: 0.10),
                    borderRadius: BorderRadius.circular(10),
                  ),
                  child: Icon(data.icon, size: 17, color: data.accent),
                ),
                const Spacer(),
                Container(
                  width: 6,
                  height: 6,
                  decoration: BoxDecoration(
                    color: data.accent,
                    shape: BoxShape.circle,
                  ),
                ),
              ],
            ),
            const SizedBox(height: 14),
            Text(
              data.value,
              maxLines: 1,
              overflow: TextOverflow.ellipsis,
              style: TextStyle(
                fontSize: context.isSmallPhone ? 20 : 22,
                fontWeight: FontWeight.w900,
                color: AppTheme.textPrimary,
                letterSpacing: -0.5,
              ),
            ),
            const SizedBox(height: 2),
            Text(
              data.label,
              maxLines: 1,
              overflow: TextOverflow.ellipsis,
              style: const TextStyle(
                color: AppTheme.textSecondary,
                fontWeight: FontWeight.w700,
                fontSize: 12,
              ),
            ),
          ],
        ),
      ),
    );
  }
}

class AdminErrorView extends StatelessWidget {
  const AdminErrorView({super.key, required this.message, required this.onRetry});
  final String message;
  final VoidCallback onRetry;

  @override
  Widget build(BuildContext context) {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(22),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(20),
        border: Border.all(color: AppTheme.border),
        boxShadow: AppTheme.shadowSoft,
      ),
      child: Column(
        children: [
          Container(
            width: 52,
            height: 52,
            decoration: BoxDecoration(
              color: AppTheme.danger.withValues(alpha: 0.10),
              borderRadius: BorderRadius.circular(16),
            ),
            child: const Icon(PhosphorIconsFill.warningCircle,
                color: AppTheme.danger, size: 24),
          ),
          const SizedBox(height: 14),
          const Text(
            'Something went wrong',
            style: TextStyle(
              fontWeight: FontWeight.w800,
              fontSize: 15.5,
              color: AppTheme.textPrimary,
            ),
          ),
          const SizedBox(height: 6),
          Text(
            message,
            textAlign: TextAlign.center,
            style: const TextStyle(color: AppTheme.textSecondary, fontSize: 13),
          ),
          const SizedBox(height: 16),
          OutlinedButton.icon(
            onPressed: onRetry,
            icon: const Icon(PhosphorIconsBold.arrowClockwise, size: 18),
            label: const Text('Try again'),
            style: OutlinedButton.styleFrom(minimumSize: const Size(0, 46)),
          ),
        ],
      ),
    );
  }
}

class AdminEmptyView extends StatelessWidget {
  const AdminEmptyView({
    super.key,
    required this.icon,
    required this.title,
    this.message,
  });
  final IconData icon;
  final String title;
  final String? message;

  @override
  Widget build(BuildContext context) {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(26),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(20),
        border: Border.all(color: AppTheme.border),
        boxShadow: AppTheme.shadowSoft,
      ),
      child: Column(
        children: [
          Container(
            width: 60,
            height: 60,
            decoration: BoxDecoration(
              color: AppTheme.primarySoft,
              borderRadius: BorderRadius.circular(18),
            ),
            child: Icon(icon, color: AppTheme.primaryDark, size: 28),
          ),
          const SizedBox(height: 14),
          Text(
            title,
            textAlign: TextAlign.center,
            style: const TextStyle(
              fontWeight: FontWeight.w800,
              fontSize: 15.5,
              color: AppTheme.textPrimary,
            ),
          ),
          if ((message ?? '').isNotEmpty) ...[
            const SizedBox(height: 6),
            Text(
              message!,
              textAlign: TextAlign.center,
              style: const TextStyle(color: AppTheme.textSecondary, fontSize: 13),
            ),
          ],
        ],
      ),
    );
  }
}

/// Horizontal pill filter.
class AdminFilterBar extends StatelessWidget {
  const AdminFilterBar({
    super.key,
    required this.options,
    required this.selected,
    required this.onSelected,
  });

  final Map<String, String> options;
  final String selected;
  final ValueChanged<String> onSelected;

  @override
  Widget build(BuildContext context) {
    return SizedBox(
      height: 38,
      child: ListView(
        scrollDirection: Axis.horizontal,
        children: options.entries.map((e) {
          final active = e.key == selected;
          return Padding(
            padding: const EdgeInsets.only(right: 8),
            child: GestureDetector(
              onTap: () => onSelected(e.key),
              child: Container(
                alignment: Alignment.center,
                padding: const EdgeInsets.symmetric(horizontal: 16),
                decoration: BoxDecoration(
                  color: active ? AppTheme.primary : Colors.white,
                  borderRadius: BorderRadius.circular(999),
                  border: Border.all(
                    color: active ? AppTheme.primary : AppTheme.border,
                  ),
                ),
                child: Text(
                  e.value,
                  style: TextStyle(
                    fontSize: 13,
                    fontWeight: FontWeight.w700,
                    color: active ? Colors.white : AppTheme.textSecondary,
                  ),
                ),
              ),
            ),
          );
        }).toList(),
      ),
    );
  }
}

Color priorityColor(String priorityValue) {
  switch (priorityValue) {
    case '1':
      return AppTheme.danger;
    case '3':
      return AppTheme.success;
    default:
      return AppTheme.warning;
  }
}

Color dueMetaColor(String type) {
  switch (type) {
    case 'overdue':
      return AppTheme.danger;
    case 'due_today':
      return AppTheme.warning;
    case 'upcoming':
      return AppTheme.primaryDark;
    default:
      return AppTheme.textMuted;
  }
}

const List<String> kMonthNames = [
  'January', 'February', 'March', 'April', 'May', 'June',
  'July', 'August', 'September', 'October', 'November', 'December',
];

/// A single accomplishment row, shared by the admin and per-employee reports.
class AccomplishmentCard extends StatelessWidget {
  const AccomplishmentCard({super.key, required this.item});
  final AdminAccomplishment item;

  @override
  Widget build(BuildContext context) {
    final isCalendar = item.type == 'calendar';
    return Container(
      margin: const EdgeInsets.only(bottom: 10),
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: AppTheme.border),
        boxShadow: AppTheme.shadowSoft,
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Icon(
                isCalendar
                    ? PhosphorIconsFill.calendarCheck
                    : PhosphorIconsFill.checkCircle,
                size: 18,
                color: isCalendar ? AppTheme.primaryDark : AppTheme.success,
              ),
              const SizedBox(width: 8),
              Expanded(
                child: Text(
                  item.title,
                  style: const TextStyle(
                    fontWeight: FontWeight.w800,
                    fontSize: 14.5,
                    color: AppTheme.textPrimary,
                  ),
                ),
              ),
              if (item.points != null) ...[
                const SizedBox(width: 8),
                Container(
                  padding:
                      const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                  decoration: BoxDecoration(
                    color: AppTheme.primarySoft,
                    borderRadius: BorderRadius.circular(999),
                  ),
                  child: Text(
                    '${item.points} pts',
                    style: const TextStyle(
                      fontSize: 10.5,
                      fontWeight: FontWeight.w800,
                      color: AppTheme.primaryDark,
                    ),
                  ),
                ),
              ],
            ],
          ),
          if (item.note.trim().isNotEmpty) ...[
            const SizedBox(height: 8),
            Text(
              item.note,
              style: const TextStyle(
                fontSize: 12.5,
                color: AppTheme.textSecondary,
                height: 1.4,
              ),
            ),
          ],
          const SizedBox(height: 10),
          Wrap(
            spacing: 10,
            runSpacing: 6,
            children: [
              if (item.assignedName.trim().isNotEmpty)
                _meta(PhosphorIconsBold.user, item.assignedName),
              if (item.projectName.trim().isNotEmpty)
                _meta(PhosphorIconsBold.folderSimple, item.projectName),
              if (item.datePosted.trim().isNotEmpty)
                _meta(PhosphorIconsBold.calendarBlank,
                    item.datePosted.split(' ').first),
            ],
          ),
        ],
      ),
    );
  }

  Widget _meta(IconData icon, String text) {
    return Row(
      mainAxisSize: MainAxisSize.min,
      children: [
        Icon(icon, size: 12, color: AppTheme.textMuted),
        const SizedBox(width: 4),
        Text(
          text,
          style: const TextStyle(fontSize: 11.5, color: AppTheme.textSecondary),
        ),
      ],
    );
  }
}

/// Button that opens a month/year picker and reports the chosen values.
class MonthYearButton extends StatelessWidget {
  const MonthYearButton({
    super.key,
    required this.month,
    required this.year,
    required this.onChanged,
    this.allowAll = false,
    this.isAll = false,
  });

  final int month;
  final int year;
  final bool allowAll;
  final bool isAll;
  final void Function(int? month, int? year) onChanged;

  Future<void> _open(BuildContext context) async {
    int selMonth = month;
    int selYear = year;
    bool selAll = isAll;
    final now = DateTime.now();
    final result = await showModalBottomSheet<bool>(
      context: context,
      backgroundColor: Colors.transparent,
      builder: (context) => StatefulBuilder(
        builder: (context, setSheet) => Container(
          decoration: const BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
          ),
          padding: const EdgeInsets.fromLTRB(20, 16, 20, 24),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              const Text(
                'Select period',
                style: TextStyle(
                  fontWeight: FontWeight.w900,
                  fontSize: 18,
                  color: AppTheme.textPrimary,
                ),
              ),
              const SizedBox(height: 14),
              if (allowAll)
                CheckboxListTile(
                  contentPadding: EdgeInsets.zero,
                  value: selAll,
                  onChanged: (v) => setSheet(() => selAll = v ?? false),
                  title: const Text('All months'),
                  controlAffinity: ListTileControlAffinity.leading,
                ),
              if (!selAll)
                Row(
                  children: [
                    Expanded(
                      child: _picker<int>(
                        label: 'Month',
                        value: selMonth,
                        items: [
                          for (var m = 1; m <= 12; m++)
                            DropdownMenuItem(
                                value: m, child: Text(kMonthNames[m - 1])),
                        ],
                        onChanged: (v) => setSheet(() => selMonth = v ?? selMonth),
                      ),
                    ),
                    const SizedBox(width: 12),
                    Expanded(
                      child: _picker<int>(
                        label: 'Year',
                        value: selYear,
                        items: [
                          for (var y = now.year; y >= now.year - 5; y--)
                            DropdownMenuItem(value: y, child: Text('$y')),
                        ],
                        onChanged: (v) => setSheet(() => selYear = v ?? selYear),
                      ),
                    ),
                  ],
                ),
              const SizedBox(height: 18),
              FilledButton(
                onPressed: () => Navigator.pop(context, true),
                child: const Text('Apply'),
              ),
            ],
          ),
        ),
      ),
    );
    if (result == true) {
      if (selAll) {
        onChanged(null, null);
      } else {
        onChanged(selMonth, selYear);
      }
    }
  }

  static Widget _picker<T>({
    required String label,
    required T value,
    required List<DropdownMenuItem<T>> items,
    required ValueChanged<T?> onChanged,
  }) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          label,
          style: const TextStyle(
            fontWeight: FontWeight.w700,
            fontSize: 12.5,
            color: AppTheme.textSecondary,
          ),
        ),
        const SizedBox(height: 6),
        Container(
          padding: const EdgeInsets.symmetric(horizontal: 12),
          decoration: BoxDecoration(
            color: AppTheme.surfaceMuted,
            borderRadius: BorderRadius.circular(12),
            border: Border.all(color: AppTheme.border),
          ),
          child: DropdownButtonHideUnderline(
            child: DropdownButton<T>(
              value: value,
              isExpanded: true,
              items: items,
              onChanged: onChanged,
            ),
          ),
        ),
      ],
    );
  }

  @override
  Widget build(BuildContext context) {
    final label =
        isAll ? 'All months · $year' : '${kMonthNames[month - 1]} $year';
    return GestureDetector(
      onTap: () => _open(context),
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 11),
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(14),
          border: Border.all(color: AppTheme.border),
          boxShadow: AppTheme.shadowSoft,
        ),
        child: Row(
          mainAxisSize: MainAxisSize.min,
          children: [
            const Icon(PhosphorIconsBold.calendarDots,
                size: 16, color: AppTheme.primaryDark),
            const SizedBox(width: 8),
            Flexible(
              child: Text(
                label,
                overflow: TextOverflow.ellipsis,
                style: const TextStyle(
                  fontWeight: FontWeight.w800,
                  fontSize: 13,
                  color: AppTheme.textPrimary,
                ),
              ),
            ),
            const SizedBox(width: 6),
            const Icon(PhosphorIconsBold.caretDown,
                size: 13, color: AppTheme.textMuted),
          ],
        ),
      ),
    );
  }
}
