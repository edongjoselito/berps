import 'dart:async';

import 'package:flutter/material.dart';
import 'package:lucide_icons_flutter/lucide_icons.dart';

import '../../../core/network/api_exception.dart';
import '../../../core/theme/app_theme.dart';
import '../../../core/widgets/desktop_kit.dart';
import '../../../core/utils/date_formatters.dart';
import '../../../core/utils/haptics.dart';
import '../../../core/utils/responsive.dart';
import '../../../core/widgets/animations.dart';
import '../../../core/widgets/app_toast.dart';
import '../../../core/widgets/mobile_header.dart';
import '../../../core/widgets/skeleton.dart';
import '../../auth/domain/staff_session.dart';
import '../../home/data/staff_api.dart';
import '../../notifications/presentation/notification_bell.dart';
import '../domain/staff_attendance.dart';

class StaffAttendanceTab extends StatefulWidget {
  const StaffAttendanceTab({
    super.key,
    required this.session,
    this.onMenu,
    this.dtrMonthView = false,
  });

  final StaffSession session;
  final VoidCallback? onMenu;

  /// When true, pre-populates the range to the current month (My DTR view).
  final bool dtrMonthView;

  @override
  State<StaffAttendanceTab> createState() => _StaffAttendanceTabState();
}

class _StaffAttendanceTabState extends State<StaffAttendanceTab> {
  final StaffApi _api = StaffApi();
  late String _from = _today();
  late String _to = _today();
  Future<StaffAttendanceData>? _future;

  @override
  void initState() {
    super.initState();
    if (widget.dtrMonthView) {
      final now = DateTime.now();
      final firstOfMonth = DateTime(now.year, now.month, 1);
      _from = _isoDate(firstOfMonth);
      _to = _isoDate(now);
    }
    _reload();
  }

  void _reload() {
    setState(() {
      _future = _api.fetchAttendance(
        baseUrl: widget.session.baseUrl,
        token: widget.session.token,
        from: _from,
        to: _to,
      );
    });
  }

  Future<void> _pickDateRange() async {
    Haptics.light();
    final fromDate = DateTime.tryParse(_from) ?? DateTime.now();
    final toDate = DateTime.tryParse(_to) ?? fromDate;

    final start = await showDatePicker(
      context: context,
      firstDate: DateTime(2020),
      lastDate: DateTime.now().add(const Duration(days: 365)),
      initialDate: fromDate,
      helpText: 'Select start date',
    );
    if (!mounted || start == null) return;

    final end = await showDatePicker(
      context: context,
      firstDate: DateTime(2020),
      lastDate: DateTime.now().add(const Duration(days: 365)),
      initialDate: toDate.isBefore(start) ? start : toDate,
      helpText: 'Select end date',
    );
    if (!mounted || end == null) return;

    setState(() {
      _from = _isoDate(start);
      _to = _isoDate(end);
    });
    _reload();
  }

  Future<void> _runPunchAction(Future<String> Function() action) async {
    Haptics.medium();
    try {
      final message = await action();
      if (!mounted) return;
      AppToast.success(context, message);
      _reload();
    } on ApiException catch (error) {
      if (!mounted) return;
      AppToast.error(context, error.message);
    }
  }

  void _setPreset({required String from, required String to}) {
    Haptics.light();
    setState(() {
      _from = from;
      _to = to;
    });
    _reload();
  }

  /// Which quick range is active, for the desktop segmented control.
  String get _activePreset {
    final today = _today();
    final yesterday = _isoDate(
      DateTime.now().subtract(const Duration(days: 1)),
    );
    final weekStart = _isoDate(
      DateTime.now().subtract(const Duration(days: 6)),
    );
    if (_from == today && _to == today) return 'today';
    if (_from == yesterday && _to == yesterday) return 'yesterday';
    if (_from == weekStart && _to == today) return 'week';
    return 'custom';
  }

  void _applyPreset(String preset) {
    final now = DateTime.now();
    switch (preset) {
      case 'today':
        _setPreset(from: _today(), to: _today());
      case 'yesterday':
        final iso = _isoDate(now.subtract(const Duration(days: 1)));
        _setPreset(from: iso, to: iso);
      case 'week':
        _setPreset(
          from: _isoDate(now.subtract(const Duration(days: 6))),
          to: _isoDate(now),
        );
    }
  }

  /// Desktop composition: range toolbar, summary and records on the left;
  /// the punch clock as a fixed right rail.
  Widget _buildDesktop(StaffAttendanceData data) {
    final main = Column(
      crossAxisAlignment: CrossAxisAlignment.stretch,
      children: [
        Row(
          children: [
            const Icon(
              LucideIcons.calendarDays,
              size: 16,
              color: AppTheme.textSecondary,
            ),
            const SizedBox(width: 8),
            Expanded(
              child: Text(
                formatRangeLabel(_from, _to),
                maxLines: 1,
                overflow: TextOverflow.ellipsis,
                style: const TextStyle(
                  fontSize: 14,
                  fontWeight: FontWeight.w700,
                  color: AppTheme.textPrimary,
                ),
              ),
            ),
            DeskSegmented(
              options: const {
                'today': 'Today',
                'yesterday': 'Yesterday',
                'week': 'Last 7 days',
              },
              value: _activePreset,
              onChanged: _applyPreset,
            ),
            const SizedBox(width: 8),
            DeskButton(
              label: 'Custom range',
              icon: LucideIcons.calendarSearch,
              primary: false,
              onTap: _pickDateRange,
            ),
          ],
        ),
        const SizedBox(height: 16),
        _SummaryRow(summary: data.summary),
        const SizedBox(height: 20),
        const _SectionTitle('Entries'),
        const SizedBox(height: 10),
        if (data.records.isEmpty)
          const _EmptyState(
            message: 'No attendance entries were found for this range.',
          )
        else
          for (final record in data.records)
            Padding(
              padding: const EdgeInsets.only(bottom: 10),
              child: _AttendanceRecordCard(record: record),
            ),
      ],
    );

    return FadeSlide(
      delay: const Duration(milliseconds: 60),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Expanded(child: main),
          const SizedBox(width: 20),
          SizedBox(
            width: 340,
            child: _PunchHeroCard(
              data: data,
              onTimeIn: () => _runPunchAction(
                () => _api.timeIn(
                  baseUrl: widget.session.baseUrl,
                  token: widget.session.token,
                ),
              ),
              onTimeOut: () => _runPunchAction(
                () => _api.timeOut(
                  baseUrl: widget.session.baseUrl,
                  token: widget.session.token,
                ),
              ),
            ),
          ),
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return RefreshIndicator(
      color: AppTheme.primary,
      onRefresh: () async {
        Haptics.light();
        _reload();
        await _future;
      },
      child: FutureBuilder<StaffAttendanceData>(
        future: _future,
        builder: (context, snapshot) {
          return ListView(
            physics: const AlwaysScrollableScrollPhysics(),
            padding: EdgeInsets.fromLTRB(
              context.gutter,
              12,
              context.gutter,
              28,
            ),
            children: [
              SafeArea(
                bottom: false,
                child: MobileHeader(
                  title: 'Attendance',
                  leadingIcon: LucideIcons.list,
                  onLeadingTap: widget.onMenu == null
                      ? null
                      : () {
                          Haptics.light();
                          widget.onMenu!();
                        },
                  trailing: NotificationBell(session: widget.session),
                ),
              ),
              const SizedBox(height: 16),
              if (!AppTheme.isDesktop)
                FadeSlide(
                  delay: const Duration(milliseconds: 60),
                  child: _RangeCard(
                    label: formatRangeLabel(_from, _to),
                    onFilter: _pickDateRange,
                    onToday: () => _setPreset(from: _today(), to: _today()),
                    onYesterday: () {
                      final date = DateTime.now().subtract(
                        const Duration(days: 1),
                      );
                      final iso = _isoDate(date);
                      _setPreset(from: iso, to: iso);
                    },
                    onWeek: () {
                      final now = DateTime.now();
                      _setPreset(
                        from: _isoDate(now.subtract(const Duration(days: 6))),
                        to: _isoDate(now),
                      );
                    },
                  ),
                ),
              if (!AppTheme.isDesktop) const SizedBox(height: 18),
              if (snapshot.connectionState == ConnectionState.waiting)
                const _AttendanceSkeleton()
              else if (snapshot.hasError)
                _AttendanceError(
                  message: snapshot.error is ApiException
                      ? (snapshot.error as ApiException).message
                      : snapshot.error.toString(),
                  onRetry: () {
                    Haptics.medium();
                    _reload();
                  },
                )
              else if (!snapshot.hasData)
                _AttendanceError(
                  message: 'Attendance data is unavailable right now.',
                  onRetry: () {
                    Haptics.medium();
                    _reload();
                  },
                )
              else if (AppTheme.isDesktop)
                _buildDesktop(snapshot.data!)
              else ...[
                FadeSlide(
                  delay: const Duration(milliseconds: 120),
                  child: _PunchHeroCard(
                    data: snapshot.data!,
                    onTimeIn: () => _runPunchAction(
                      () => _api.timeIn(
                        baseUrl: widget.session.baseUrl,
                        token: widget.session.token,
                      ),
                    ),
                    onTimeOut: () => _runPunchAction(
                      () => _api.timeOut(
                        baseUrl: widget.session.baseUrl,
                        token: widget.session.token,
                      ),
                    ),
                  ),
                ),
                const SizedBox(height: 18),
                FadeSlide(
                  delay: const Duration(milliseconds: 180),
                  child: _SummaryRow(summary: snapshot.data!.summary),
                ),
                const SizedBox(height: 20),
                FadeSlide(
                  delay: const Duration(milliseconds: 240),
                  child: const _SectionTitle(
                    'Recent Entries',
                    icon: LucideIcons.rotateCcw,
                  ),
                ),
                const SizedBox(height: 10),
                ...snapshot.data!.records.asMap().entries.map(
                  (entry) => Padding(
                    padding: const EdgeInsets.only(bottom: 12),
                    child: FadeSlide(
                      delay: Duration(milliseconds: 280 + 50 * entry.key),
                      child: _AttendanceRecordCard(record: entry.value),
                    ),
                  ),
                ),
                if (snapshot.data!.records.isEmpty)
                  const _EmptyState(
                    message: 'No attendance entries were found for this range.',
                  ),
              ],
            ],
          );
        },
      ),
    );
  }
}

class _SectionTitle extends StatelessWidget {
  const _SectionTitle(this.text, {this.icon});
  final String text;
  final IconData? icon;

  @override
  Widget build(BuildContext context) {
    return Row(
      children: [
        if (icon != null) ...[
          Container(
            width: 28,
            height: 28,
            decoration: BoxDecoration(
              color: AppTheme.primarySoft,
              borderRadius: BorderRadius.circular(8),
            ),
            child: Icon(icon, size: 14, color: AppTheme.primaryDark),
          ),
          const SizedBox(width: 10),
        ],
        Text(
          text,
          style: const TextStyle(
            fontSize: 15,
            fontWeight: FontWeight.w900,
            color: AppTheme.textPrimary,
            letterSpacing: -0.2,
          ),
        ),
      ],
    );
  }
}

class _RangeCard extends StatelessWidget {
  const _RangeCard({
    required this.label,
    required this.onToday,
    required this.onYesterday,
    required this.onWeek,
    required this.onFilter,
  });

  final String label;
  final VoidCallback onToday;
  final VoidCallback onYesterday;
  final VoidCallback onWeek;
  final VoidCallback onFilter;

  @override
  Widget build(BuildContext context) {
    return Container(
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
            children: [
              Container(
                width: 30,
                height: 30,
                decoration: BoxDecoration(
                  color: AppTheme.primarySoft,
                  borderRadius: BorderRadius.circular(9),
                ),
                child: const Icon(
                  LucideIcons.calendarDays,
                  color: AppTheme.primary,
                  size: 14,
                ),
              ),
              const SizedBox(width: 10),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    const Text(
                      'Range',
                      style: TextStyle(
                        fontSize: 10,
                        fontWeight: FontWeight.w800,
                        color: AppTheme.textMuted,
                        letterSpacing: 1.2,
                      ),
                    ),
                    const SizedBox(height: 2),
                    Text(
                      label,
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                      style: const TextStyle(
                        fontSize: 13.5,
                        fontWeight: FontWeight.w800,
                        color: AppTheme.textPrimary,
                        letterSpacing: -0.1,
                      ),
                    ),
                  ],
                ),
              ),
              const SizedBox(width: 8),
              PressScale(
                onTap: () {
                  Haptics.light();
                  onFilter();
                },
                child: Container(
                  padding: const EdgeInsets.symmetric(
                    horizontal: 12,
                    vertical: 8,
                  ),
                  decoration: BoxDecoration(
                    color: AppTheme.primarySoft,
                    borderRadius: BorderRadius.circular(10),
                  ),
                  child: Row(
                    mainAxisSize: MainAxisSize.min,
                    children: const [
                      Icon(
                        LucideIcons.filter,
                        size: 12,
                        color: AppTheme.primaryDark,
                      ),
                      SizedBox(width: 6),
                      Text(
                        'Filter',
                        style: TextStyle(
                          fontSize: 11.5,
                          fontWeight: FontWeight.w800,
                          color: AppTheme.primaryDark,
                          letterSpacing: 0.2,
                        ),
                      ),
                    ],
                  ),
                ),
              ),
            ],
          ),
          const SizedBox(height: 12),
          Row(
            children: [
              Expanded(child: _RangePill('Today', onToday)),
              const SizedBox(width: 8),
              Expanded(child: _RangePill('Yesterday', onYesterday)),
              const SizedBox(width: 8),
              Expanded(child: _RangePill('7 Days', onWeek)),
            ],
          ),
        ],
      ),
    );
  }
}

class _RangePill extends StatelessWidget {
  const _RangePill(this.label, this.onTap);
  final String label;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    return PressScale(
      onTap: onTap,
      child: Container(
        height: 38,
        alignment: Alignment.center,
        decoration: BoxDecoration(
          color: AppTheme.surfaceMuted,
          borderRadius: BorderRadius.circular(12),
          border: Border.all(color: AppTheme.border),
        ),
        child: Text(
          label,
          style: const TextStyle(
            color: AppTheme.primaryDark,
            fontWeight: FontWeight.w800,
            fontSize: 12.5,
          ),
        ),
      ),
    );
  }
}

class _PunchHeroCard extends StatelessWidget {
  const _PunchHeroCard({
    required this.data,
    required this.onTimeIn,
    required this.onTimeOut,
  });

  final StaffAttendanceData data;
  final VoidCallback onTimeIn;
  final VoidCallback onTimeOut;

  @override
  Widget build(BuildContext context) {
    final hasOpenSlot = data.status.openSlotLabel.isNotEmpty;

    if (AppTheme.isDesktop) {
      final status = data.status;
      // The next sensible action drives the primary button — if a shift is
      // open the only sensible action is Time Out, and vice versa.
      final canIn = status.canTimeIn;
      final canOut = status.canTimeOut;
      final primaryIsOut = canOut;

      return DeskPanel(
        title: "Today's attendance",
        action: hasOpenSlot
            ? Container(
                margin: const EdgeInsets.only(right: 6),
                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                decoration: BoxDecoration(
                  color: AppTheme.primary.withValues(alpha: 0.10),
                  borderRadius: BorderRadius.circular(6),
                ),
                child: Text(
                  '${status.openSlotLabel} shift open',
                  style: const TextStyle(
                    color: AppTheme.primaryDark,
                    fontSize: 11,
                    fontWeight: FontWeight.w700,
                  ),
                ),
              )
            : canIn
            ? Container(
                margin: const EdgeInsets.only(right: 6),
                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                decoration: BoxDecoration(
                  color: AppTheme.success.withValues(alpha: 0.10),
                  borderRadius: BorderRadius.circular(6),
                ),
                child: const Text(
                  'Ready to time in',
                  style: TextStyle(
                    color: AppTheme.success,
                    fontSize: 11,
                    fontWeight: FontWeight.w700,
                  ),
                ),
              )
            : null,
        padding: const EdgeInsets.fromLTRB(18, 14, 18, 18),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            const _LiveClock(),
            const SizedBox(height: 10),
            Text(
              status.statusLabel,
              style: const TextStyle(
                color: AppTheme.textSecondary,
                height: 1.5,
                fontWeight: FontWeight.w600,
                fontSize: 12.5,
              ),
            ),
            const SizedBox(height: 14),
            Row(
              children: [
                Expanded(
                  child: _DeskPunchMetric(
                    label: 'Time in',
                    value: status.latestTimeInLabel.isEmpty
                        ? '--'
                        : status.latestTimeInLabel,
                    icon: LucideIcons.logIn,
                    color: AppTheme.success,
                  ),
                ),
                const SizedBox(width: 10),
                Expanded(
                  child: _DeskPunchMetric(
                    label: 'Time out',
                    value: status.latestTimeOutLabel.isEmpty
                        ? '--'
                        : status.latestTimeOutLabel,
                    icon: LucideIcons.logOut,
                    color: AppTheme.primaryDark,
                  ),
                ),
              ],
            ),
            const SizedBox(height: 16),
            _PunchButton(
              label: primaryIsOut ? 'Time Out' : 'Time In',
              icon: primaryIsOut ? LucideIcons.logOut : LucideIcons.logIn,
              primary: true,
              onTap: primaryIsOut ? onTimeOut : onTimeIn,
              enabled: primaryIsOut ? canOut : canIn,
            ),
            if (canIn || canOut) ...[
              const SizedBox(height: 8),
              _PunchButton(
                label: primaryIsOut ? 'Time In' : 'Time Out',
                icon: primaryIsOut ? LucideIcons.logIn : LucideIcons.logOut,
                primary: false,
                onTap: primaryIsOut ? onTimeIn : onTimeOut,
                enabled: primaryIsOut ? canIn : canOut,
              ),
            ],
          ],
        ),
      );
    }

    return Container(
      padding: const EdgeInsets.all(18),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(22),
        border: Border.all(color: AppTheme.border),
        boxShadow: AppTheme.shadowMedium,
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Container(
            padding: const EdgeInsets.all(16),
            decoration: BoxDecoration(
              gradient: const LinearGradient(
                colors: [
                  Color(0xFF0A2F73),
                  Color(0xFF114CB3),
                  Color(0xFF1B5ED6),
                ],
                begin: Alignment.topLeft,
                end: Alignment.bottomRight,
              ),
              borderRadius: BorderRadius.circular(18),
              boxShadow: [
                BoxShadow(
                  color: AppTheme.primaryDark.withValues(alpha: 0.30),
                  blurRadius: 20,
                  offset: const Offset(0, 12),
                ),
              ],
            ),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  children: [
                    const Expanded(
                      child: Text(
                        "Today's Attendance",
                        style: TextStyle(
                          color: Colors.white,
                          fontSize: 15,
                          fontWeight: FontWeight.w900,
                          letterSpacing: -0.2,
                        ),
                      ),
                    ),
                    if (data.status.canTimeIn)
                      Container(
                        padding: const EdgeInsets.symmetric(
                          horizontal: 8,
                          vertical: 4,
                        ),
                        decoration: BoxDecoration(
                          color: AppTheme.accent.withValues(alpha: 0.92),
                          borderRadius: BorderRadius.circular(999),
                        ),
                        child: const Text(
                          'READY',
                          style: TextStyle(
                            color: Colors.white,
                            fontSize: 9.5,
                            fontWeight: FontWeight.w900,
                            letterSpacing: 1.2,
                          ),
                        ),
                      ),
                  ],
                ),
                const SizedBox(height: 10),
                Text(
                  data.status.statusLabel,
                  style: TextStyle(
                    color: Colors.white.withValues(alpha: 0.85),
                    height: 1.35,
                    fontWeight: FontWeight.w500,
                    fontSize: 12.5,
                  ),
                ),
                if (hasOpenSlot) ...[
                  const SizedBox(height: 12),
                  Container(
                    padding: const EdgeInsets.symmetric(
                      horizontal: 10,
                      vertical: 6,
                    ),
                    decoration: BoxDecoration(
                      color: Colors.white.withValues(alpha: 0.18),
                      borderRadius: BorderRadius.circular(999),
                    ),
                    child: Row(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        const Icon(
                          LucideIcons.clock,
                          color: Colors.white,
                          size: 12,
                        ),
                        const SizedBox(width: 6),
                        Text(
                          '${data.status.openSlotLabel} shift open',
                          style: const TextStyle(
                            color: Colors.white,
                            fontWeight: FontWeight.w800,
                            fontSize: 11.5,
                          ),
                        ),
                      ],
                    ),
                  ),
                ],
              ],
            ),
          ),
          const SizedBox(height: 14),
          Row(
            children: [
              Expanded(
                child: _PunchMetricCard(
                  label: 'Latest Time In',
                  value: data.status.latestTimeInLabel.isEmpty
                      ? '--'
                      : data.status.latestTimeInLabel,
                  icon: LucideIcons.logIn,
                  color: AppTheme.success,
                ),
              ),
              const SizedBox(width: 10),
              Expanded(
                child: _PunchMetricCard(
                  label: 'Latest Time Out',
                  value: data.status.latestTimeOutLabel.isEmpty
                      ? '--'
                      : data.status.latestTimeOutLabel,
                  icon: LucideIcons.logOut,
                  color: AppTheme.primaryDark,
                ),
              ),
            ],
          ),
          const SizedBox(height: 14),
          Row(
            children: [
              Expanded(
                child: _PunchButton(
                  label: 'Time In',
                  icon: LucideIcons.logIn,
                  primary: true,
                  onTap: onTimeIn,
                ),
              ),
              const SizedBox(width: 10),
              Expanded(
                child: _PunchButton(
                  label: 'Time Out',
                  icon: LucideIcons.logOut,
                  primary: false,
                  onTap: onTimeOut,
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }
}

class _PunchButton extends StatelessWidget {
  const _PunchButton({
    required this.label,
    required this.icon,
    required this.primary,
    required this.onTap,
    this.enabled = true,
  });

  final String label;
  final IconData icon;
  final bool primary;
  final VoidCallback onTap;
  final bool enabled;

  @override
  Widget build(BuildContext context) {
    if (AppTheme.isDesktop) {
      final fg = !enabled
          ? AppTheme.textMuted
          : primary
          ? Colors.white
          : AppTheme.primaryDark;
      return Material(
        color: !enabled
            ? AppTheme.surfaceMuted
            : primary
            ? AppTheme.primaryDark
            : Colors.white,
        borderRadius: BorderRadius.circular(10),
        child: InkWell(
          onTap: enabled ? onTap : null,
          borderRadius: BorderRadius.circular(10),
          child: Container(
            height: 44,
            alignment: Alignment.center,
            decoration: BoxDecoration(
              borderRadius: BorderRadius.circular(10),
              border: primary || !enabled
                  ? null
                  : Border.all(color: AppTheme.borderStrong),
            ),
            child: Row(
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                Icon(icon, size: 15, color: fg),
                const SizedBox(width: 8),
                Text(
                  label,
                  style: TextStyle(
                    color: fg,
                    fontWeight: FontWeight.w700,
                    fontSize: 13.5,
                  ),
                ),
              ],
            ),
          ),
        ),
      );
    }

    final fg = primary ? Colors.white : AppTheme.primaryDark;

    return PressScale(
      onTap: enabled ? onTap : () {},
      child: Opacity(
        opacity: enabled ? 1 : 0.5,
        child: Container(
          height: 50,
          alignment: Alignment.center,
          decoration: BoxDecoration(
            gradient: primary
                ? const LinearGradient(
                    colors: [AppTheme.primary, AppTheme.primaryDark],
                    begin: Alignment.topLeft,
                    end: Alignment.bottomRight,
                  )
                : null,
            color: primary ? null : Colors.white,
            borderRadius: BorderRadius.circular(14),
            border: primary ? null : Border.all(color: AppTheme.borderStrong),
            boxShadow: primary
                ? [
                    BoxShadow(
                      color: AppTheme.primaryDark.withValues(alpha: 0.28),
                      blurRadius: 18,
                      offset: const Offset(0, 10),
                    ),
                  ]
                : null,
          ),
          child: Row(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Icon(icon, size: 16, color: fg),
              const SizedBox(width: 8),
              Text(
                label,
                style: TextStyle(
                  color: fg,
                  fontWeight: FontWeight.w800,
                  fontSize: 14,
                  letterSpacing: 0.2,
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}

/// Ticking clock shown in the desktop punch panel — makes the rail read as a
/// real time clock rather than a static card.
class _LiveClock extends StatefulWidget {
  const _LiveClock();

  @override
  State<_LiveClock> createState() => _LiveClockState();
}

class _LiveClockState extends State<_LiveClock> {
  Timer? _timer;
  DateTime _now = DateTime.now();

  @override
  void initState() {
    super.initState();
    _timer = Timer.periodic(const Duration(seconds: 1), (_) {
      if (mounted) setState(() => _now = DateTime.now());
    });
  }

  @override
  void dispose() {
    _timer?.cancel();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final hour12 = _now.hour % 12 == 0 ? 12 : _now.hour % 12;
    final mm = _now.minute.toString().padLeft(2, '0');
    final ss = _now.second.toString().padLeft(2, '0');
    final ampm = _now.hour < 12 ? 'AM' : 'PM';
    const weekdays = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
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
    final dateLabel =
        '${weekdays[_now.weekday - 1]}, ${months[_now.month - 1]} ${_now.day}';

    return Row(
      crossAxisAlignment: CrossAxisAlignment.end,
      children: [
        Text(
          '$hour12:$mm',
          style: const TextStyle(
            fontSize: 34,
            fontWeight: FontWeight.w800,
            color: AppTheme.textPrimary,
            letterSpacing: -1.2,
            height: 1.0,
          ),
        ),
        Padding(
          padding: const EdgeInsets.only(bottom: 3),
          child: Text(
            ':$ss',
            style: const TextStyle(
              fontSize: 16,
              fontWeight: FontWeight.w700,
              color: AppTheme.textMuted,
            ),
          ),
        ),
        const SizedBox(width: 8),
        Padding(
          padding: const EdgeInsets.only(bottom: 4),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(
                ampm,
                style: const TextStyle(
                  fontSize: 12,
                  fontWeight: FontWeight.w800,
                  color: AppTheme.primaryDark,
                ),
              ),
              Text(
                dateLabel,
                style: const TextStyle(
                  fontSize: 11,
                  fontWeight: FontWeight.w600,
                  color: AppTheme.textMuted,
                ),
              ),
            ],
          ),
        ),
      ],
    );
  }
}

/// Compact horizontal metric used by the desktop punch hero.
class _DeskPunchMetric extends StatelessWidget {
  const _DeskPunchMetric({
    required this.label,
    required this.value,
    required this.icon,
    required this.color,
  });

  final String label;
  final String value;
  final IconData icon;
  final Color color;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
      decoration: BoxDecoration(
        color: AppTheme.surfaceMuted,
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: AppTheme.border),
      ),
      child: Row(
        children: [
          Icon(icon, size: 15, color: color),
          const SizedBox(width: 10),
          Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(
                label,
                style: const TextStyle(
                  fontSize: 10.5,
                  fontWeight: FontWeight.w800,
                  letterSpacing: 0.3,
                  color: AppTheme.textMuted,
                ),
              ),
              const SizedBox(height: 1),
              Text(
                value,
                style: const TextStyle(
                  fontSize: 14.5,
                  fontWeight: FontWeight.w900,
                  color: AppTheme.textPrimary,
                  letterSpacing: -0.2,
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }
}

class _PunchMetricCard extends StatelessWidget {
  const _PunchMetricCard({
    required this.label,
    required this.value,
    required this.icon,
    required this.color,
  });

  final String label;
  final String value;
  final IconData icon;
  final Color color;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        color: AppTheme.surfaceMuted,
        borderRadius: BorderRadius.circular(14),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Container(
            width: 30,
            height: 30,
            decoration: BoxDecoration(
              color: color.withValues(alpha: 0.12),
              borderRadius: BorderRadius.circular(10),
            ),
            child: Icon(icon, color: color, size: 15),
          ),
          const SizedBox(height: 12),
          Text(
            value,
            style: const TextStyle(
              fontSize: 18,
              fontWeight: FontWeight.w900,
              color: AppTheme.textPrimary,
              letterSpacing: -0.3,
            ),
          ),
          const SizedBox(height: 2),
          Text(
            label,
            style: const TextStyle(
              color: AppTheme.textSecondary,
              fontWeight: FontWeight.w700,
              fontSize: 11.5,
            ),
          ),
        ],
      ),
    );
  }
}

class _SummaryRow extends StatelessWidget {
  const _SummaryRow({required this.summary});

  final AttendanceSummary summary;

  @override
  Widget build(BuildContext context) {
    final items = <_SummaryItem>[
      _SummaryItem(
        'Present',
        '${summary.presentDays}',
        AppTheme.success,
        LucideIcons.circleCheck,
        const [Color(0xFF16A34A), Color(0xFF15803D)],
      ),
      _SummaryItem(
        'Pending',
        '${summary.pendingDays}',
        AppTheme.warning,
        LucideIcons.clock,
        const [Color(0xFFF59E0B), Color(0xFFD97706)],
      ),
      _SummaryItem(
        'Hours',
        summary.totalHoursLabel,
        AppTheme.primaryDark,
        LucideIcons.rotateCcw,
        const [Color(0xFF1E3A5F), Color(0xFF2D5A8A)],
      ),
      _SummaryItem(
        AppTheme.isDesktop ? 'Accomplished' : 'Accomplishments',
        '${summary.accomplishmentCount}',
        AppTheme.primary,
        LucideIcons.squareCheck,
        const [Color(0xFF2563EB), Color(0xFF1D4ED8)],
      ),
    ];

    if (AppTheme.isDesktop) {
      return LayoutBuilder(
        builder: (context, constraints) {
          final perRow = constraints.maxWidth >= 720 ? items.length : 2;
          final width = (constraints.maxWidth - 12 * (perRow - 1)) / perRow;
          return Wrap(
            spacing: 12,
            runSpacing: 12,
            children: [
              for (final item in items)
                SizedBox(
                  width: width,
                  child: DeskStatCard(
                    icon: item.icon,
                    label: item.label,
                    value: item.value,
                    accent: item.color,
                  ),
                ),
            ],
          );
        },
      );
    }

    return LayoutBuilder(
      builder: (context, constraints) {
        final tileWidth = (constraints.maxWidth - 12) / 2;
        return Wrap(
          spacing: 12,
          runSpacing: 12,
          children: items
              .map(
                (item) => SizedBox(
                  width: tileWidth,
                  child: Container(
                    padding: const EdgeInsets.all(14),
                    decoration: BoxDecoration(
                      gradient: LinearGradient(
                        colors: item.gradient,
                        begin: Alignment.topLeft,
                        end: Alignment.bottomRight,
                      ),
                      borderRadius: BorderRadius.circular(18),
                      boxShadow: [
                        BoxShadow(
                          color: item.color.withValues(alpha: 0.25),
                          blurRadius: 14,
                          offset: const Offset(0, 6),
                        ),
                      ],
                    ),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Container(
                          width: 30,
                          height: 30,
                          decoration: BoxDecoration(
                            color: Colors.white.withValues(alpha: 0.15),
                            borderRadius: BorderRadius.circular(10),
                          ),
                          child: Icon(item.icon, size: 16, color: Colors.white),
                        ),
                        const SizedBox(height: 12),
                        TweenAnimationBuilder<int>(
                          tween: IntTween(
                            begin: 0,
                            end: int.tryParse(item.value) ?? 0,
                          ),
                          duration: const Duration(milliseconds: 900),
                          curve: Curves.easeOutCubic,
                          builder: (context, val, child) {
                            return Text(
                              int.tryParse(item.value) != null
                                  ? '$val'
                                  : item.value,
                              style: const TextStyle(
                                fontSize: 20,
                                fontWeight: FontWeight.w900,
                                color: Colors.white,
                                letterSpacing: -0.3,
                              ),
                            );
                          },
                        ),
                        const SizedBox(height: 2),
                        Text(
                          item.label,
                          style: TextStyle(
                            color: Colors.white.withValues(alpha: 0.85),
                            fontWeight: FontWeight.w700,
                            fontSize: 12,
                          ),
                        ),
                      ],
                    ),
                  ),
                ),
              )
              .toList(),
        );
      },
    );
  }
}

class _SummaryItem {
  const _SummaryItem(
    this.label,
    this.value,
    this.color,
    this.icon,
    this.gradient,
  );
  final String label;
  final String value;
  final Color color;
  final IconData icon;
  final List<Color> gradient;
}

class _AttendanceRecordCard extends StatelessWidget {
  const _AttendanceRecordCard({required this.record});

  final AttendanceRecord record;

  @override
  Widget build(BuildContext context) {
    final statusColor = switch (record.status) {
      'pending' => AppTheme.warning,
      'absent' => AppTheme.danger,
      _ => AppTheme.success,
    };

    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(20),
        border: Border.all(color: AppTheme.border),
        boxShadow: AppTheme.shadowSoft,
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Container(
                width: 4,
                height: 32,
                decoration: BoxDecoration(
                  color: statusColor,
                  borderRadius: BorderRadius.circular(2),
                ),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: Text(
                  formatDisplayDate(record.date),
                  style: const TextStyle(
                    fontWeight: FontWeight.w900,
                    color: AppTheme.textPrimary,
                    fontSize: 14,
                  ),
                ),
              ),
              Container(
                padding: const EdgeInsets.symmetric(
                  horizontal: 10,
                  vertical: 5,
                ),
                decoration: BoxDecoration(
                  color: statusColor.withValues(alpha: 0.10),
                  borderRadius: BorderRadius.circular(999),
                ),
                child: Text(
                  record.status.toUpperCase(),
                  style: TextStyle(
                    color: statusColor,
                    fontWeight: FontWeight.w900,
                    fontSize: 10.5,
                    letterSpacing: 0.8,
                  ),
                ),
              ),
            ],
          ),
          const SizedBox(height: 12),
          Row(
            children: [
              _MetricPill(
                icon: LucideIcons.timer,
                label: record.totalHoursLabel,
              ),
              const SizedBox(width: 8),
              _MetricPill(
                icon: LucideIcons.squareCheck,
                label: '${record.accomplishmentCount} done',
              ),
            ],
          ),
          const SizedBox(height: 14),
          if (record.intervals.isEmpty)
            const Text(
              'No punch intervals recorded.',
              style: TextStyle(color: AppTheme.textSecondary, fontSize: 12.5),
            )
          else
            ...record.intervals.map(
              (interval) => Padding(
                padding: const EdgeInsets.only(bottom: 8),
                child: _IntervalRow(interval: interval),
              ),
            ),
        ],
      ),
    );
  }
}

class _IntervalRow extends StatelessWidget {
  const _IntervalRow({required this.interval});
  final AttendanceInterval interval;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        color: AppTheme.surfaceMuted,
        borderRadius: BorderRadius.circular(12),
      ),
      child: Column(
        children: [
          Row(
            children: [
              const Icon(LucideIcons.logIn, size: 14, color: AppTheme.success),
              const SizedBox(width: 8),
              const Text(
                'Time In',
                style: TextStyle(
                  color: AppTheme.textSecondary,
                  fontWeight: FontWeight.w700,
                  fontSize: 12.5,
                ),
              ),
              const Spacer(),
              Text(
                interval.timeInLabel.isEmpty ? '--' : interval.timeInLabel,
                style: const TextStyle(
                  color: AppTheme.textPrimary,
                  fontWeight: FontWeight.w800,
                  fontSize: 12.5,
                ),
              ),
            ],
          ),
          const SizedBox(height: 8),
          Row(
            children: [
              Icon(
                interval.isOpen ? LucideIcons.clock4 : LucideIcons.logOut,
                size: 14,
                color: interval.isOpen
                    ? AppTheme.warning
                    : AppTheme.primaryDark,
              ),
              const SizedBox(width: 8),
              const Text(
                'Time Out',
                style: TextStyle(
                  color: AppTheme.textSecondary,
                  fontWeight: FontWeight.w700,
                  fontSize: 12.5,
                ),
              ),
              const Spacer(),
              Text(
                interval.timeOutLabel.isEmpty
                    ? 'Pending'
                    : interval.timeOutLabel,
                style: TextStyle(
                  color: interval.timeOutLabel.isEmpty
                      ? AppTheme.warning
                      : AppTheme.textPrimary,
                  fontWeight: FontWeight.w800,
                  fontSize: 12.5,
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }
}

class _MetricPill extends StatelessWidget {
  const _MetricPill({required this.icon, required this.label});
  final IconData icon;
  final String label;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
      decoration: BoxDecoration(
        color: AppTheme.surfaceMuted,
        borderRadius: BorderRadius.circular(999),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Icon(icon, size: 13, color: AppTheme.primaryDark),
          const SizedBox(width: 6),
          Text(
            label,
            style: const TextStyle(
              color: AppTheme.textPrimary,
              fontWeight: FontWeight.w700,
              fontSize: 11.5,
            ),
          ),
        ],
      ),
    );
  }
}

class _AttendanceSkeleton extends StatelessWidget {
  const _AttendanceSkeleton();

  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        SkeletonCard(
          radius: 22,
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: const [
              Skeleton(width: double.infinity, height: 100, radius: 18),
              SizedBox(height: 14),
              Row(
                children: [
                  Expanded(child: Skeleton(height: 76, radius: 14)),
                  SizedBox(width: 10),
                  Expanded(child: Skeleton(height: 76, radius: 14)),
                ],
              ),
              SizedBox(height: 14),
              Skeleton(height: 48, radius: 14),
              SizedBox(height: 10),
              Skeleton(height: 48, radius: 14),
            ],
          ),
        ),
        const SizedBox(height: 18),
        LayoutBuilder(
          builder: (context, constraints) {
            final w = (constraints.maxWidth - 12) / 2;
            return Wrap(
              spacing: 12,
              runSpacing: 12,
              children: [
                for (var i = 0; i < 4; i++)
                  SizedBox(
                    width: w,
                    child: SkeletonCard(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: const [
                          Skeleton(width: 30, height: 30, radius: 10),
                          SizedBox(height: 12),
                          Skeleton(width: 60, height: 20),
                          SizedBox(height: 6),
                          Skeleton(width: 80, height: 10),
                        ],
                      ),
                    ),
                  ),
              ],
            );
          },
        ),
        const SizedBox(height: 18),
        const SkeletonCard(child: Skeleton(height: 120, radius: 12)),
      ],
    );
  }
}

class _AttendanceError extends StatelessWidget {
  const _AttendanceError({required this.message, required this.onRetry});

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
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Text(
            'Unable to load attendance',
            style: TextStyle(
              fontWeight: FontWeight.w900,
              color: AppTheme.textPrimary,
            ),
          ),
          const SizedBox(height: 8),
          Text(
            message,
            style: const TextStyle(color: AppTheme.textSecondary, fontSize: 13),
          ),
          const SizedBox(height: 14),
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

class _EmptyState extends StatelessWidget {
  const _EmptyState({required this.message});

  final String message;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(24),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(20),
        border: Border.all(color: AppTheme.border),
      ),
      child: Row(
        children: [
          Container(
            width: 38,
            height: 38,
            decoration: BoxDecoration(
              color: AppTheme.primarySoft,
              borderRadius: BorderRadius.circular(12),
            ),
            child: const Icon(
              LucideIcons.inbox,
              color: AppTheme.primaryDark,
              size: 18,
            ),
          ),
          const SizedBox(width: 12),
          Expanded(
            child: Text(
              message,
              style: const TextStyle(
                color: AppTheme.textSecondary,
                fontSize: 13,
              ),
            ),
          ),
        ],
      ),
    );
  }
}

String _today() => _isoDate(DateTime.now());

String _isoDate(DateTime value) =>
    '${value.year.toString().padLeft(4, '0')}-${value.month.toString().padLeft(2, '0')}-${value.day.toString().padLeft(2, '0')}';
