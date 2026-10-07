import 'package:flutter/material.dart';
import 'package:lucide_icons_flutter/lucide_icons.dart';

import '../../../core/network/api_exception.dart';
import '../../../core/theme/app_theme.dart';
import '../../../core/widgets/desktop_kit.dart';
import '../../../core/widgets/mobile_header.dart';
import '../../../core/utils/haptics.dart';
import '../../../core/utils/responsive.dart';
import '../../../core/widgets/animations.dart';
import '../../../core/widgets/skeleton.dart';
import '../../auth/domain/staff_session.dart';
import '../data/staff_api.dart';
import '../domain/my_dtr.dart';

class MyDtrScreen extends StatefulWidget {
  const MyDtrScreen({super.key, required this.session});

  final StaffSession session;

  @override
  State<MyDtrScreen> createState() => _MyDtrScreenState();
}

class _MyDtrScreenState extends State<MyDtrScreen> {
  final StaffApi _api = StaffApi();
  Future<MyDtrData>? _future;
  DateTime _selected = DateTime.now();
  final ScrollController _scrollController = ScrollController();

  @override
  void initState() {
    super.initState();
    _reload();
  }

  @override
  void dispose() {
    _scrollController.dispose();
    super.dispose();
  }

  void _reload() {
    setState(() {
      _future = _api.fetchMyDTR(
        baseUrl: widget.session.baseUrl,
        token: widget.session.token,
        month: _selected.month,
        year: _selected.year,
      );
    });
  }

  Future<void> _pickMonth() async {
    Haptics.light();
    final picked = await showDatePicker(
      context: context,
      initialDate: _selected,
      firstDate: DateTime(2020),
      lastDate: DateTime.now().add(const Duration(days: 365)),
      helpText: 'Select month & year',
      selectableDayPredicate: (_) => true,
    );
    if (picked != null && mounted) {
      setState(() => _selected = DateTime(picked.year, picked.month));
      _reload();
    }
  }

  void _prevMonth() {
    Haptics.light();
    setState(() {
      _selected = DateTime(_selected.year, _selected.month - 1);
    });
    _reload();
  }

  void _nextMonth() {
    Haptics.light();
    setState(() {
      _selected = DateTime(_selected.year, _selected.month + 1);
    });
    _reload();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppTheme.background,
      body: GestureDetector(
        onHorizontalDragEnd: (details) {
          if (details.primaryVelocity != null &&
              details.primaryVelocity!.abs() > 200) {
            if (details.primaryVelocity! > 0) {
              _prevMonth();
            } else {
              _nextMonth();
            }
          }
        },
        child: SafeArea(
          bottom: false,
          child: FutureBuilder<MyDtrData>(
            future: _future,
            builder: (context, snapshot) {
              final loading =
                  snapshot.connectionState == ConnectionState.waiting;
              final error = snapshot.error;
              final data = snapshot.data;

              // Auto-scroll removed to prevent jumpy UX on load

              return RefreshIndicator(
                color: AppTheme.primary,
                onRefresh: () async => _reload(),
                child: ListView(
                  controller: _scrollController,
                  physics: const AlwaysScrollableScrollPhysics(),
                  padding: EdgeInsets.fromLTRB(
                    context.gutter,
                    0,
                    context.gutter,
                    32,
                  ),
                  children: [
                    _Header(
                      onBack: () {
                        Haptics.light();
                        Navigator.of(context).maybePop();
                      },
                      selected: _selected,
                      onPrev: _prevMonth,
                      onNext: _nextMonth,
                      onPick: _pickMonth,
                    ),
                    const SizedBox(height: 16),
                    if (loading && data == null)
                      const _DtrSkeleton()
                    else if (error != null && data == null)
                      _ErrorCard(
                        message: error is ApiException
                            ? error.message
                            : 'Unable to load DTR.',
                        onRetry: _reload,
                      )
                    else if (data != null && AppTheme.isDesktop) ...[
                      _DeskDtrSummary(data: data),
                      const SizedBox(height: 20),
                    ] else if (data != null) ...[
                      FadeSlide(
                        delay: const Duration(milliseconds: 60),
                        child: _MonthSelector(
                          month: data.month,
                          year: data.year,
                          onTap: _pickMonth,
                          onPrev: _prevMonth,
                          onNext: _nextMonth,
                        ),
                      ),
                      const SizedBox(height: 16),
                      FadeSlide(
                        delay: const Duration(milliseconds: 100),
                        child: _SummaryCard(data: data),
                      ),
                      const SizedBox(height: 24),
                    ],
                    if (data != null) ...[
                      if (AppTheme.isDesktop)
                        DeskPanel(
                          title: 'Daily records',
                          count: data.rows.length,
                          padding: EdgeInsets.zero,
                          child: data.rows.isEmpty
                              ? Padding(
                                  padding: const EdgeInsets.all(24),
                                  child: _EmptyState(
                                    month: data.month,
                                    year: data.year,
                                  ),
                                )
                              : _DeskDtrTable(rows: data.rows),
                        )
                      else ...[
                        FadeSlide(
                          delay: const Duration(milliseconds: 140),
                          child: _SectionHeader(
                            title: 'Daily Records',
                            count: data.rows.length,
                          ),
                        ),
                        const SizedBox(height: 12),
                        if (data.rows.isEmpty) ...[
                          const SizedBox(height: 40),
                          _EmptyState(month: data.month, year: data.year),
                        ] else ...[
                          ...data.rows.asMap().entries.map((entry) {
                            final weekNumber = (entry.key ~/ 7) + 1;
                            final isWeekBoundary =
                                entry.key > 0 && entry.key % 7 == 0;
                            final rowDate = DateTime.tryParse(entry.value.date);
                            final isToday =
                                rowDate != null &&
                                rowDate.year == DateTime.now().year &&
                                rowDate.month == DateTime.now().month &&
                                rowDate.day == DateTime.now().day;
                            final isWeekend =
                                rowDate != null &&
                                (rowDate.weekday == 6 || rowDate.weekday == 7);
                            return Column(
                              mainAxisSize: MainAxisSize.min,
                              children: [
                                if (isWeekBoundary)
                                  _WeekSeparator(weekNumber: weekNumber),
                                Padding(
                                  padding: const EdgeInsets.only(bottom: 12),
                                  child: FadeSlide(
                                    delay: Duration(
                                      milliseconds: 40 * entry.key,
                                    ),
                                    child: _DtrRowCard(
                                      row: entry.value,
                                      isToday: isToday,
                                      isWeekend: isWeekend,
                                    ),
                                  ),
                                ),
                              ],
                            );
                          }),
                        ],
                      ],
                    ],
                  ],
                ),
              );
            },
          ),
        ),
      ),
    );
  }
}

/* ── Header ─────────────────────────────────────────────────────────────── */

class _Header extends StatelessWidget {
  const _Header({
    required this.onBack,
    required this.selected,
    required this.onPrev,
    required this.onNext,
    required this.onPick,
  });

  final VoidCallback onBack;
  final DateTime selected;
  final VoidCallback onPrev;
  final VoidCallback onNext;
  final VoidCallback onPick;

  static const _monthNames = [
    'January',
    'February',
    'March',
    'April',
    'May',
    'June',
    'July',
    'August',
    'September',
    'October',
    'November',
    'December',
  ];

  @override
  Widget build(BuildContext context) {
    if (AppTheme.isDesktop) {
      // Month navigation lives in the toolbar on desktop.
      return Padding(
        padding: const EdgeInsets.only(top: 12),
        child: MobileHeader(
          title: 'My DTR',
          subtitle: 'Daily time record',
          leadingIcon: LucideIcons.arrowLeft,
          onLeadingTap: onBack,
          trailing: Row(
            mainAxisSize: MainAxisSize.min,
            children: [
              MobileHeaderButton(icon: LucideIcons.chevronLeft, onTap: onPrev),
              const SizedBox(width: 6),
              DeskButton(
                label: '${_monthNames[selected.month - 1]} ${selected.year}',
                icon: LucideIcons.calendarDays,
                primary: false,
                onTap: onPick,
              ),
              const SizedBox(width: 6),
              MobileHeaderButton(icon: LucideIcons.chevronRight, onTap: onNext),
            ],
          ),
        ),
      );
    }
    return Container(
      padding: EdgeInsets.fromLTRB(context.gutter, 12, context.gutter, 20),
      decoration: const BoxDecoration(
        gradient: LinearGradient(
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
          colors: [Color(0xFF1E3A5F), Color(0xFF2D5A8A)],
        ),
        borderRadius: BorderRadius.only(
          bottomLeft: Radius.circular(24),
          bottomRight: Radius.circular(24),
        ),
      ),
      child: SafeArea(
        bottom: false,
        child: Row(
          children: [
            Material(
              color: Colors.white.withValues(alpha: 0.12),
              borderRadius: BorderRadius.circular(10),
              child: InkWell(
                onTap: onBack,
                borderRadius: BorderRadius.circular(10),
                child: const Padding(
                  padding: EdgeInsets.all(8),
                  child: Icon(
                    LucideIcons.chevronLeft,
                    color: Colors.white,
                    size: 20,
                  ),
                ),
              ),
            ),
            const SizedBox(width: 12),
            const Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    'My DTR',
                    style: TextStyle(
                      fontSize: 22,
                      fontWeight: FontWeight.w900,
                      color: Colors.white,
                      letterSpacing: -0.5,
                    ),
                  ),
                  SizedBox(height: 2),
                  Text(
                    'Daily Time Record',
                    style: TextStyle(
                      fontSize: 13,
                      fontWeight: FontWeight.w600,
                      color: Colors.white70,
                    ),
                  ),
                ],
              ),
            ),
            Container(
              padding: const EdgeInsets.all(10),
              decoration: BoxDecoration(
                color: Colors.white.withValues(alpha: 0.12),
                borderRadius: BorderRadius.circular(12),
              ),
              child: const Icon(
                LucideIcons.clock,
                color: Colors.white,
                size: 22,
              ),
            ),
          ],
        ),
      ),
    );
  }
}

/* ── Month Selector ───────────────────────────────────────────────────── */

class _MonthSelector extends StatelessWidget {
  const _MonthSelector({
    required this.month,
    required this.year,
    required this.onTap,
    required this.onPrev,
    required this.onNext,
  });

  final int month;
  final int year;
  final VoidCallback onTap;
  final VoidCallback onPrev;
  final VoidCallback onNext;

  static const _months = [
    'January',
    'February',
    'March',
    'April',
    'May',
    'June',
    'July',
    'August',
    'September',
    'October',
    'November',
    'December',
  ];

  @override
  Widget build(BuildContext context) {
    final label = _months[month - 1];
    return Container(
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: AppTheme.border),
        boxShadow: [
          BoxShadow(
            color: AppTheme.primary.withValues(alpha: 0.06),
            blurRadius: 20,
            offset: const Offset(0, 6),
          ),
        ],
      ),
      child: Material(
        color: Colors.transparent,
        borderRadius: BorderRadius.circular(16),
        child: InkWell(
          onTap: onTap,
          borderRadius: BorderRadius.circular(16),
          child: Padding(
            padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
            child: Row(
              children: [
                Container(
                  width: 42,
                  height: 42,
                  decoration: const BoxDecoration(
                    gradient: LinearGradient(
                      colors: [Color(0xFF2D5A8A), Color(0xFF1E3A5F)],
                    ),
                    borderRadius: BorderRadius.all(Radius.circular(12)),
                  ),
                  child: const Icon(
                    LucideIcons.calendarDays,
                    color: Colors.white,
                    size: 20,
                  ),
                ),
                const SizedBox(width: 14),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        '$label $year',
                        style: const TextStyle(
                          fontWeight: FontWeight.w900,
                          fontSize: 17,
                          color: AppTheme.textPrimary,
                        ),
                      ),
                      const SizedBox(height: 2),
                      const Text(
                        'Tap to change month',
                        style: TextStyle(
                          fontSize: 13,
                          color: AppTheme.textSecondary,
                          fontWeight: FontWeight.w600,
                        ),
                      ),
                    ],
                  ),
                ),
                Row(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    _MonthArrow(icon: LucideIcons.chevronLeft, onTap: onPrev),
                    const SizedBox(width: 6),
                    _MonthArrow(icon: LucideIcons.chevronRight, onTap: onNext),
                    const SizedBox(width: 8),
                    Material(
                      color: AppTheme.primarySoft,
                      borderRadius: BorderRadius.circular(10),
                      child: InkWell(
                        onTap: onTap,
                        borderRadius: BorderRadius.circular(10),
                        child: const Padding(
                          padding: EdgeInsets.symmetric(
                            horizontal: 10,
                            vertical: 8,
                          ),
                          child: Row(
                            mainAxisSize: MainAxisSize.min,
                            children: [
                              Icon(
                                LucideIcons.calendarDays,
                                size: 14,
                                color: AppTheme.primaryDark,
                              ),
                            ],
                          ),
                        ),
                      ),
                    ),
                  ],
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }
}

class _MonthArrow extends StatelessWidget {
  const _MonthArrow({required this.icon, required this.onTap});

  final IconData icon;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    return Material(
      color: AppTheme.surfaceMuted,
      borderRadius: BorderRadius.circular(10),
      child: InkWell(
        onTap: onTap,
        borderRadius: BorderRadius.circular(10),
        child: Container(
          width: 32,
          height: 32,
          alignment: Alignment.center,
          child: Icon(icon, size: 16, color: AppTheme.textPrimary),
        ),
      ),
    );
  }
}

/* ── Summary Card ─────────────────────────────────────────────────────── */

class _SummaryCard extends StatelessWidget {
  const _SummaryCard({required this.data});

  final MyDtrData data;

  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.stretch,
      children: [
        if (AppTheme.isDesktop)
          Row(
            children: [
              Expanded(
                child: DeskStatCard(
                  icon: LucideIcons.circleCheck,
                  label: 'Present',
                  value: data.presentDays.toString(),
                  accent: AppTheme.success,
                ),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: DeskStatCard(
                  icon: LucideIcons.circleX,
                  label: 'Absent',
                  value: data.absentDays.toString(),
                  accent: AppTheme.danger,
                ),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: DeskStatCard(
                  icon: LucideIcons.clock,
                  label: 'Pending',
                  value: data.pendingDays.toString(),
                  accent: AppTheme.warning,
                ),
              ),
            ],
          )
        else
          Row(
            children: [
              Expanded(
                child: _StatCard(
                  icon: LucideIcons.circleCheck,
                  label: 'Present',
                  value: data.presentDays.toString(),
                  color: AppTheme.success,
                  gradient: const [Color(0xFF16A34A), Color(0xFF15803D)],
                ),
              ),
              const SizedBox(width: 10),
              Expanded(
                child: _StatCard(
                  icon: LucideIcons.circleX,
                  label: 'Absent',
                  value: data.absentDays.toString(),
                  color: AppTheme.danger,
                  gradient: const [Color(0xFFDC2626), Color(0xFFB91C1C)],
                ),
              ),
              const SizedBox(width: 10),
              Expanded(
                child: _StatCard(
                  icon: LucideIcons.clock,
                  label: 'Pending',
                  value: data.pendingDays.toString(),
                  color: AppTheme.warning,
                  gradient: const [Color(0xFFF59E0B), Color(0xFFD97706)],
                ),
              ),
            ],
          ),
        const SizedBox(height: 14),
        if (AppTheme.isDesktop)
          DeskStatCard(
            icon: LucideIcons.timer,
            label: data.presentDays > 0
                ? 'Total hours this month · ${_avgLabel(data)}'
                : 'Total hours this month',
            value: data.monthTotalLabel,
            accent: AppTheme.primaryDark,
          )
        else
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
            decoration: BoxDecoration(
              gradient: const LinearGradient(
                colors: [Color(0xFF1E3A5F), Color(0xFF2D5A8A)],
              ),
              borderRadius: BorderRadius.circular(16),
            ),
            child: Row(
              children: [
                Container(
                  width: 38,
                  height: 38,
                  decoration: BoxDecoration(
                    color: Colors.white.withValues(alpha: 0.15),
                    borderRadius: BorderRadius.circular(10),
                  ),
                  child: const Icon(
                    LucideIcons.timer,
                    color: Colors.white,
                    size: 18,
                  ),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      const Text(
                        'Total Hours',
                        style: TextStyle(
                          fontWeight: FontWeight.w700,
                          fontSize: 13,
                          color: Colors.white70,
                        ),
                      ),
                      if (data.presentDays > 0) ...[
                        const SizedBox(height: 2),
                        Text(
                          _avgLabel(data),
                          style: TextStyle(
                            fontSize: 10,
                            fontWeight: FontWeight.w700,
                            color: Colors.white.withValues(alpha: 0.60),
                          ),
                        ),
                      ],
                    ],
                  ),
                ),
                Text(
                  data.monthTotalLabel,
                  style: const TextStyle(
                    fontWeight: FontWeight.w900,
                    fontSize: 20,
                    color: Colors.white,
                  ),
                ),
              ],
            ),
          ),
      ],
    );
  }

  String _avgLabel(MyDtrData data) {
    final days = data.presentDays > 0 ? data.presentDays : 1;
    final avg = data.monthTotalSeconds ~/ days;
    final hours = avg ~/ 3600;
    final mins = (avg % 3600) ~/ 60;
    return '${hours}h ${mins.toString().padLeft(2, '0')}m avg / day';
  }
}

class _StatCard extends StatelessWidget {
  const _StatCard({
    required this.icon,
    required this.label,
    required this.value,
    required this.color,
    required this.gradient,
  });

  final IconData icon;
  final String label;
  final String value;
  final Color color;
  final List<Color> gradient;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(vertical: 14, horizontal: 8),
      decoration: BoxDecoration(
        gradient: LinearGradient(colors: gradient),
        borderRadius: BorderRadius.circular(14),
        boxShadow: [
          BoxShadow(
            color: color.withValues(alpha: 0.25),
            blurRadius: 12,
            offset: const Offset(0, 4),
          ),
        ],
      ),
      child: Column(
        children: [
          Icon(icon, size: 22, color: Colors.white),
          const SizedBox(height: 8),
          TweenAnimationBuilder<int>(
            tween: IntTween(begin: 0, end: int.tryParse(value) ?? 0),
            duration: const Duration(milliseconds: 900),
            curve: Curves.easeOutCubic,
            builder: (context, val, child) {
              return Text(
                '$val',
                style: const TextStyle(
                  fontSize: 20,
                  fontWeight: FontWeight.w900,
                  color: Colors.white,
                ),
              );
            },
          ),
          const SizedBox(height: 2),
          Text(
            label,
            style: TextStyle(
              fontSize: 11,
              fontWeight: FontWeight.w700,
              color: Colors.white.withValues(alpha: 0.85),
            ),
          ),
        ],
      ),
    );
  }
}

/* ── Section Header ───────────────────────────────────────────────────── */

class _SectionHeader extends StatelessWidget {
  const _SectionHeader({required this.title, required this.count});

  final String title;
  final int count;

  @override
  Widget build(BuildContext context) {
    return Row(
      children: [
        Container(
          width: 4,
          height: 22,
          decoration: BoxDecoration(
            color: AppTheme.primary,
            borderRadius: BorderRadius.circular(2),
          ),
        ),
        const SizedBox(width: 10),
        Text(
          title,
          style: const TextStyle(
            fontSize: 15,
            fontWeight: FontWeight.w900,
            color: AppTheme.textPrimary,
          ),
        ),
        const SizedBox(width: 8),
        Container(
          padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
          decoration: BoxDecoration(
            color: AppTheme.primarySoft,
            borderRadius: BorderRadius.circular(10),
          ),
          child: Text(
            count.toString(),
            style: const TextStyle(
              fontSize: 11,
              fontWeight: FontWeight.w900,
              color: AppTheme.primaryDark,
            ),
          ),
        ),
      ],
    );
  }
}

/* ── DTR Row Card ─────────────────────────────────────────────────────── */

/// Desktop month summary: one strip of figures split by hairline dividers.
class _DeskDtrSummary extends StatelessWidget {
  const _DeskDtrSummary({required this.data});

  final MyDtrData data;

  @override
  Widget build(BuildContext context) {
    final days = data.presentDays > 0 ? data.presentDays : 1;
    final avg = data.monthTotalSeconds ~/ days;
    final avgLabel =
        '${avg ~/ 3600}h ${((avg % 3600) ~/ 60).toString().padLeft(2, '0')}m';

    Widget figure(String label, String value, Color color, {String? note}) {
      return Expanded(
        child: Padding(
          padding: const EdgeInsets.symmetric(horizontal: 22, vertical: 18),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(
                children: [
                  Container(
                    width: 7,
                    height: 7,
                    decoration: BoxDecoration(
                      color: color,
                      shape: BoxShape.circle,
                    ),
                  ),
                  const SizedBox(width: 8),
                  Text(
                    label,
                    style: const TextStyle(
                      fontSize: 12,
                      fontWeight: FontWeight.w700,
                      color: AppTheme.textSecondary,
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 8),
              Row(
                crossAxisAlignment: CrossAxisAlignment.baseline,
                textBaseline: TextBaseline.alphabetic,
                children: [
                  Text(
                    value,
                    style: const TextStyle(
                      fontSize: 26,
                      fontWeight: FontWeight.w800,
                      color: AppTheme.textPrimary,
                      letterSpacing: -0.8,
                    ),
                  ),
                  if (note != null) ...[
                    const SizedBox(width: 8),
                    Text(
                      note,
                      style: const TextStyle(
                        fontSize: 12,
                        fontWeight: FontWeight.w600,
                        color: AppTheme.textMuted,
                      ),
                    ),
                  ],
                ],
              ),
            ],
          ),
        ),
      );
    }

    const divider = SizedBox(
      height: 46,
      child: VerticalDivider(width: 1, color: AppTheme.border),
    );

    return Container(
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(14),
        border: Border.all(color: AppTheme.border),
      ),
      child: Row(
        children: [
          figure(
            'Total hours',
            data.monthTotalLabel,
            AppTheme.primaryDark,
            note: data.presentDays > 0 ? '$avgLabel avg/day' : null,
          ),
          divider,
          figure(
            'Present',
            '${data.presentDays}',
            AppTheme.success,
            note: 'days',
          ),
          divider,
          figure('Absent', '${data.absentDays}', AppTheme.danger, note: 'days'),
          divider,
          figure(
            'Pending',
            '${data.pendingDays}',
            AppTheme.warning,
            note: 'days',
          ),
        ],
      ),
    );
  }
}

/// Desktop timesheet: one compact row per day with AM / PM / total columns.
class _DeskDtrTable extends StatelessWidget {
  const _DeskDtrTable({required this.rows});

  final List<MyDtrRow> rows;

  static const _days = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
  static const _months = [
    'Jan',
    'Feb',
    'Mar',
    'Apr',
    'May',
    'Jun',
    'Jul',
    'Aug',
    'Sep',
    'Oct',
    'Nov',
    'Dec',
  ];

  Color _statusColor(String status) => switch (status) {
    'Present' => AppTheme.success,
    'Absent' => AppTheme.danger,
    'Pending' => AppTheme.warning,
    _ => AppTheme.textMuted,
  };

  @override
  Widget build(BuildContext context) {
    const headStyle = TextStyle(
      fontSize: 11,
      fontWeight: FontWeight.w700,
      letterSpacing: 0.6,
      color: AppTheme.textMuted,
    );
    final now = DateTime.now();

    Widget cell(List<String> intervals) => Text(
      intervals.isEmpty ? '—' : intervals.join('\n'),
      style: TextStyle(
        fontSize: 12.5,
        height: 1.5,
        fontWeight: FontWeight.w600,
        color: intervals.isEmpty ? AppTheme.textMuted : AppTheme.textPrimary,
      ),
    );

    return Column(
      children: [
        Container(
          color: AppTheme.surfaceMuted.withValues(alpha: 0.6),
          padding: const EdgeInsets.symmetric(horizontal: 18, vertical: 9),
          child: const Row(
            children: [
              SizedBox(width: 150, child: Text('DATE', style: headStyle)),
              Expanded(child: Text('MORNING', style: headStyle)),
              Expanded(child: Text('AFTERNOON', style: headStyle)),
              SizedBox(width: 100, child: Text('TOTAL', style: headStyle)),
              SizedBox(
                width: 96,
                child: Text(
                  'STATUS',
                  textAlign: TextAlign.right,
                  style: headStyle,
                ),
              ),
            ],
          ),
        ),
        for (final row in rows) ...[
          const Divider(height: 1, color: AppTheme.border),
          Builder(
            builder: (context) {
              final date = DateTime.tryParse(row.date);
              final isToday =
                  date != null &&
                  date.year == now.year &&
                  date.month == now.month &&
                  date.day == now.day;
              final isWeekend = date != null && date.weekday >= 6;
              final color = _statusColor(row.status);
              return Container(
                color: isToday
                    ? AppTheme.primarySoft.withValues(alpha: 0.6)
                    : isWeekend
                    ? AppTheme.surfaceMuted.withValues(alpha: 0.35)
                    : null,
                padding: const EdgeInsets.symmetric(
                  horizontal: 18,
                  vertical: 12,
                ),
                child: Row(
                  children: [
                    SizedBox(
                      width: 150,
                      child: Text(
                        date == null
                            ? row.date
                            : '${_days[date.weekday - 1]}, ${_months[date.month - 1]} ${date.day}',
                        style: TextStyle(
                          fontSize: 13,
                          fontWeight: isToday
                              ? FontWeight.w800
                              : FontWeight.w700,
                          color: AppTheme.textPrimary,
                        ),
                      ),
                    ),
                    Expanded(child: cell(row.amBreakdown)),
                    Expanded(child: cell(row.pmBreakdown)),
                    SizedBox(
                      width: 100,
                      child: Text(
                        row.totalSeconds > 0 ? row.totalHours : '—',
                        style: TextStyle(
                          fontSize: 13,
                          fontWeight: FontWeight.w800,
                          color: row.totalSeconds > 28800
                              ? AppTheme.success
                              : row.totalSeconds > 0
                              ? AppTheme.textPrimary
                              : AppTheme.textMuted,
                        ),
                      ),
                    ),
                    SizedBox(
                      width: 96,
                      child: Align(
                        alignment: Alignment.centerRight,
                        child: row.status.isEmpty
                            ? null
                            : Container(
                                padding: const EdgeInsets.symmetric(
                                  horizontal: 8,
                                  vertical: 3,
                                ),
                                decoration: BoxDecoration(
                                  color: color.withValues(alpha: 0.10),
                                  borderRadius: BorderRadius.circular(6),
                                ),
                                child: Text(
                                  row.status,
                                  style: TextStyle(
                                    color: color,
                                    fontSize: 11,
                                    fontWeight: FontWeight.w700,
                                  ),
                                ),
                              ),
                      ),
                    ),
                  ],
                ),
              );
            },
          ),
        ],
      ],
    );
  }
}

class _DtrRowCard extends StatelessWidget {
  const _DtrRowCard({
    required this.row,
    this.isToday = false,
    this.isWeekend = false,
  });

  final MyDtrRow row;
  final bool isToday;
  final bool isWeekend;

  Color get _statusColor {
    switch (row.status) {
      case 'Present':
        return AppTheme.success;
      case 'Absent':
        return AppTheme.danger;
      case 'Pending':
        return AppTheme.warning;
      default:
        return AppTheme.textMuted;
    }
  }

  IconData get _statusIcon {
    switch (row.status) {
      case 'Present':
        return LucideIcons.circleCheck;
      case 'Absent':
        return LucideIcons.circleX;
      case 'Pending':
        return LucideIcons.clock;
      default:
        return LucideIcons.circleHelp;
    }
  }

  @override
  Widget build(BuildContext context) {
    final date = DateTime.tryParse(row.date);
    final dayNum = date?.day.toString().padLeft(2, '0') ?? '';
    final weekday = date != null ? _weekDayLong(date.weekday) : '';
    final monthShort = date != null ? _monthShort(date.month) : '';

    final today = DateTime.now();
    final todayStr =
        '${today.year}-${today.month.toString().padLeft(2, '0')}-${today.day.toString().padLeft(2, '0')}';
    final isTodayRow = isToday || row.date == todayStr;

    return PressScale(
      onTap: () => Haptics.light(),
      child: Container(
        decoration: BoxDecoration(
          color: isWeekend ? const Color(0xFFF8F9FB) : Colors.white,
          borderRadius: BorderRadius.circular(16),
          border: Border.all(
            color: isTodayRow ? AppTheme.primary : AppTheme.border,
            width: isTodayRow ? 1.5 : 1,
          ),
          boxShadow: isTodayRow
              ? [
                  BoxShadow(
                    color: AppTheme.primary.withValues(alpha: 0.08),
                    blurRadius: 12,
                    offset: const Offset(0, 4),
                  ),
                ]
              : null,
        ),
        child: ClipRRect(
          borderRadius: BorderRadius.circular(16),
          child: IntrinsicHeight(
            child: Row(
              crossAxisAlignment: CrossAxisAlignment.stretch,
              children: [
                Container(width: 4, color: _statusColor),
                Expanded(
                  child: Padding(
                    padding: const EdgeInsets.all(14),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Row(
                          children: [
                            Container(
                              width: 40,
                              height: 40,
                              decoration: BoxDecoration(
                                color: _statusColor.withValues(alpha: 0.08),
                                borderRadius: BorderRadius.circular(10),
                              ),
                              child: Center(
                                child: Text(
                                  dayNum,
                                  style: TextStyle(
                                    fontSize: 16,
                                    fontWeight: FontWeight.w900,
                                    color: _statusColor,
                                  ),
                                ),
                              ),
                            ),
                            const SizedBox(width: 10),
                            Expanded(
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  Text(
                                    '$weekday, $monthShort',
                                    style: const TextStyle(
                                      fontWeight: FontWeight.w900,
                                      fontSize: 14,
                                      color: AppTheme.textPrimary,
                                    ),
                                  ),
                                  const SizedBox(height: 2),
                                  Text(
                                    row.date,
                                    style: const TextStyle(
                                      fontSize: 11,
                                      fontWeight: FontWeight.w700,
                                      color: AppTheme.textMuted,
                                    ),
                                  ),
                                ],
                              ),
                            ),
                            Container(
                              padding: const EdgeInsets.symmetric(
                                horizontal: 8,
                                vertical: 4,
                              ),
                              decoration: BoxDecoration(
                                color: _statusColor.withValues(alpha: 0.10),
                                borderRadius: BorderRadius.circular(20),
                              ),
                              child: Row(
                                mainAxisSize: MainAxisSize.min,
                                children: [
                                  Icon(
                                    _statusIcon,
                                    size: 12,
                                    color: _statusColor,
                                  ),
                                  const SizedBox(width: 4),
                                  Text(
                                    row.status,
                                    style: TextStyle(
                                      fontSize: 11,
                                      fontWeight: FontWeight.w900,
                                      color: _statusColor,
                                    ),
                                  ),
                                ],
                              ),
                            ),
                          ],
                        ),
                        const SizedBox(height: 12),
                        _TimelineBreakdown(
                          label: 'AM',
                          intervals: row.amBreakdown,
                          icon: LucideIcons.sun,
                          color: AppTheme.warning,
                        ),
                        const SizedBox(height: 8),
                        _TimelineBreakdown(
                          label: 'PM',
                          intervals: row.pmBreakdown,
                          icon: LucideIcons.moon,
                          color: AppTheme.primaryDark,
                        ),
                        if (row.totalSeconds > 0) ...[
                          const SizedBox(height: 10),
                          const Divider(height: 1),
                          const SizedBox(height: 10),
                          Row(
                            children: [
                              const Icon(
                                LucideIcons.timer,
                                size: 14,
                                color: AppTheme.textSecondary,
                              ),
                              const SizedBox(width: 6),
                              const Text(
                                'Total Hours',
                                style: TextStyle(
                                  fontSize: 12,
                                  fontWeight: FontWeight.w700,
                                  color: AppTheme.textSecondary,
                                ),
                              ),
                              const Spacer(),
                              Row(
                                mainAxisSize: MainAxisSize.min,
                                children: [
                                  if (row.totalSeconds > 28800)
                                    Container(
                                      padding: const EdgeInsets.symmetric(
                                        horizontal: 5,
                                        vertical: 2,
                                      ),
                                      decoration: BoxDecoration(
                                        color: AppTheme.success.withValues(
                                          alpha: 0.12,
                                        ),
                                        borderRadius: BorderRadius.circular(6),
                                      ),
                                      child: const Text(
                                        'OT',
                                        style: TextStyle(
                                          fontSize: 9,
                                          fontWeight: FontWeight.w900,
                                          color: AppTheme.success,
                                        ),
                                      ),
                                    ),
                                  if (row.totalSeconds > 28800)
                                    const SizedBox(width: 5),
                                  Text(
                                    row.totalHours,
                                    style: TextStyle(
                                      fontSize: 15,
                                      fontWeight: FontWeight.w900,
                                      color: row.totalSeconds > 28800
                                          ? AppTheme.success
                                          : AppTheme.primaryDark,
                                    ),
                                  ),
                                ],
                              ),
                            ],
                          ),
                        ],
                      ],
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

  String _weekDayLong(int d) {
    const days = [
      'Monday',
      'Tuesday',
      'Wednesday',
      'Thursday',
      'Friday',
      'Saturday',
      'Sunday',
    ];
    return days[d - 1];
  }

  String _monthShort(int m) {
    const months = [
      'Jan',
      'Feb',
      'Mar',
      'Apr',
      'May',
      'Jun',
      'Jul',
      'Aug',
      'Sep',
      'Oct',
      'Nov',
      'Dec',
    ];
    return months[m - 1];
  }
}

/* ── Timeline Breakdown ───────────────────────────────────────────────── */

class _TimelineBreakdown extends StatelessWidget {
  const _TimelineBreakdown({
    required this.label,
    required this.intervals,
    required this.icon,
    required this.color,
  });

  final String label;
  final List<String> intervals;
  final IconData icon;
  final Color color;

  @override
  Widget build(BuildContext context) {
    return Row(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Container(
          width: 26,
          height: 26,
          decoration: BoxDecoration(
            color: color.withValues(alpha: 0.08),
            borderRadius: BorderRadius.circular(7),
          ),
          child: Icon(icon, size: 13, color: color),
        ),
        const SizedBox(width: 10),
        Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              label,
              style: TextStyle(
                fontSize: 11,
                fontWeight: FontWeight.w800,
                color: color,
              ),
            ),
            const SizedBox(height: 4),
            if (intervals.isEmpty)
              Container(
                padding: const EdgeInsets.symmetric(
                  horizontal: 10,
                  vertical: 5,
                ),
                decoration: BoxDecoration(
                  color: AppTheme.surfaceMuted,
                  borderRadius: BorderRadius.circular(8),
                ),
                child: const Text(
                  'No record',
                  style: TextStyle(
                    fontSize: 12,
                    fontWeight: FontWeight.w700,
                    color: AppTheme.textMuted,
                  ),
                ),
              )
            else
              Wrap(
                spacing: 6,
                runSpacing: 6,
                children: intervals
                    .map(
                      (txt) => Container(
                        padding: const EdgeInsets.symmetric(
                          horizontal: 10,
                          vertical: 5,
                        ),
                        decoration: BoxDecoration(
                          color: AppTheme.surfaceMuted,
                          borderRadius: BorderRadius.circular(8),
                          border: Border.all(color: AppTheme.border),
                        ),
                        child: Text(
                          txt,
                          style: const TextStyle(
                            fontSize: 12,
                            fontWeight: FontWeight.w700,
                            color: AppTheme.textPrimary,
                          ),
                        ),
                      ),
                    )
                    .toList(),
              ),
          ],
        ),
      ],
    );
  }
}

/* ── Skeleton ─────────────────────────────────────────────────────────── */

class _DtrSkeleton extends StatelessWidget {
  const _DtrSkeleton();

  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.stretch,
      children: [
        const SkeletonCard(child: SizedBox(height: 56, child: Skeleton())),
        const SizedBox(height: 16),
        const SkeletonCard(child: SizedBox(height: 140, child: Skeleton())),
        const SizedBox(height: 24),
        ...List.generate(
          5,
          (_) => const Padding(
            padding: EdgeInsets.only(bottom: 12),
            child: SkeletonCard(
              child: SizedBox(height: 180, child: Skeleton()),
            ),
          ),
        ),
      ],
    );
  }
}

/* ── Error Card ───────────────────────────────────────────────────────── */

class _ErrorCard extends StatelessWidget {
  const _ErrorCard({required this.message, required this.onRetry});

  final String message;
  final VoidCallback onRetry;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(20),
        border: Border.all(color: AppTheme.border),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.center,
        children: [
          Container(
            width: 56,
            height: 56,
            decoration: BoxDecoration(
              color: AppTheme.danger.withValues(alpha: 0.08),
              shape: BoxShape.circle,
            ),
            child: const Icon(
              LucideIcons.circleAlert,
              color: AppTheme.danger,
              size: 26,
            ),
          ),
          const SizedBox(height: 14),
          const Text(
            'Unable to load DTR',
            style: TextStyle(
              fontWeight: FontWeight.w900,
              fontSize: 16,
              color: AppTheme.textPrimary,
            ),
          ),
          const SizedBox(height: 6),
          Text(
            message,
            textAlign: TextAlign.center,
            style: const TextStyle(
              color: AppTheme.textSecondary,
              fontSize: 13.5,
              height: 1.4,
            ),
          ),
          const SizedBox(height: 18),
          FilledButton.icon(
            onPressed: onRetry,
            icon: const Icon(LucideIcons.refreshCw, size: 16),
            label: const Text('Try again'),
          ),
        ],
      ),
    );
  }
}

/* ── Empty State ──────────────────────────────────────────────────────── */

class _EmptyState extends StatelessWidget {
  const _EmptyState({required this.month, required this.year});

  final int month;
  final int year;

  static const _months = [
    'January',
    'February',
    'March',
    'April',
    'May',
    'June',
    'July',
    'August',
    'September',
    'October',
    'November',
    'December',
  ];

  @override
  Widget build(BuildContext context) {
    final label = _months[month - 1];
    return Container(
      padding: const EdgeInsets.all(32),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(20),
        border: Border.all(color: AppTheme.border),
      ),
      child: Column(
        children: [
          Container(
            width: 72,
            height: 72,
            decoration: BoxDecoration(
              color: AppTheme.primarySoft,
              shape: BoxShape.circle,
            ),
            child: const Icon(
              LucideIcons.clipboardList,
              size: 32,
              color: AppTheme.primaryDark,
            ),
          ),
          const SizedBox(height: 16),
          Text(
            'No records for $label $year',
            textAlign: TextAlign.center,
            style: const TextStyle(
              fontSize: 15,
              fontWeight: FontWeight.w900,
              color: AppTheme.textPrimary,
            ),
          ),
          const SizedBox(height: 6),
          const Text(
            'Attendance records will appear once your time-in and time-out are logged.',
            textAlign: TextAlign.center,
            style: TextStyle(
              fontSize: 13,
              color: AppTheme.textSecondary,
              height: 1.4,
            ),
          ),
        ],
      ),
    );
  }
}

/* ── Week Separator ───────────────────────────────────────────────────── */

class _WeekSeparator extends StatelessWidget {
  const _WeekSeparator({required this.weekNumber});

  final int weekNumber;

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 12, top: 4),
      child: Row(
        children: [
          Expanded(child: Container(height: 1, color: AppTheme.border)),
          const SizedBox(width: 10),
          Text(
            'Week $weekNumber',
            style: const TextStyle(
              fontSize: 10,
              fontWeight: FontWeight.w800,
              color: AppTheme.textMuted,
              letterSpacing: 0.5,
            ),
          ),
          const SizedBox(width: 10),
          Expanded(child: Container(height: 1, color: AppTheme.border)),
        ],
      ),
    );
  }
}
