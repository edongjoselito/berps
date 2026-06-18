import 'package:flutter/material.dart';
import 'package:phosphor_flutter/phosphor_flutter.dart';

import '../../../core/network/api_exception.dart';
import '../../../core/theme/app_theme.dart';
import '../../../core/utils/haptics.dart';
import '../../../core/utils/responsive.dart';
import '../../../core/widgets/animations.dart';
import '../../../core/widgets/mobile_header.dart';
import '../../../core/widgets/skeleton.dart';
import '../../auth/domain/staff_session.dart';
import '../../home/data/staff_api.dart';
import '../../support/presentation/support_issue_view_screen.dart';
import '../domain/support_dashboard.dart';

class SupportDashboardScreen extends StatefulWidget {
  const SupportDashboardScreen({super.key, required this.session});
  final StaffSession session;

  @override
  State<SupportDashboardScreen> createState() => _SupportDashboardScreenState();
}

class _SupportDashboardScreenState extends State<SupportDashboardScreen> {
  final StaffApi _api = StaffApi();
  Future<SupportDashboard>? _future;

  @override
  void initState() {
    super.initState();
    _reload();
  }

  void _reload() {
    setState(() {
      _future = _api.fetchSupportDashboard(
        baseUrl: widget.session.baseUrl,
        token: widget.session.token,
      );
    });
  }

  void _openTicket(int id) {
    Haptics.light();
    Navigator.of(context).push(
      MaterialPageRoute(
        builder: (_) =>
            SupportIssueViewScreen(session: widget.session, issueId: id),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppTheme.background,
      body: SafeArea(
        bottom: false,
        child: FutureBuilder<SupportDashboard>(
          future: _future,
          builder: (context, snapshot) {
            final loading = snapshot.connectionState == ConnectionState.waiting;
            final error = snapshot.error;
            final data = snapshot.data;

            return RefreshIndicator(
              color: AppTheme.primary,
              onRefresh: () async => _reload(),
              child: ListView(
                physics: const AlwaysScrollableScrollPhysics(),
                padding: EdgeInsets.fromLTRB(
                  context.gutter,
                  12,
                  context.gutter,
                  32,
                ),
                children: [
                  MobileHeader(
                    title: 'Support Dashboard',
                    subtitle: 'Tickets across your workspace',
                    leadingIcon: PhosphorIconsBold.caretLeft,
                    onLeadingTap: () {
                      Haptics.light();
                      Navigator.of(context).maybePop();
                    },
                  ),
                  const SizedBox(height: 14),
                  if (loading && data == null)
                    Column(
                      children: List.generate(
                        3,
                        (_) => const Padding(
                          padding: EdgeInsets.only(bottom: 12),
                          child: SkeletonCard(child: SizedBox(height: 100)),
                        ),
                      ),
                    )
                  else if (error != null && data == null)
                    _ErrorBlock(
                      message: error is ApiException
                          ? error.message
                          : 'Unable to load dashboard.',
                      onRetry: _reload,
                    )
                  else if (data != null) ...[
                    FadeSlide(
                      delay: const Duration(milliseconds: 60),
                      child: _TotalsGrid(totals: data.totals),
                    ),
                    const SizedBox(height: 16),
                    FadeSlide(
                      delay: const Duration(milliseconds: 100),
                      child: _MonthCard(
                        month: data.thisMonth,
                        avgHours: data.avgResolutionHours,
                      ),
                    ),
                    if (data.byPriority.isNotEmpty) ...[
                      const SizedBox(height: 18),
                      _SectionHeader(
                        icon: PhosphorIconsBold.warning,
                        title: 'By priority',
                      ),
                      const SizedBox(height: 10),
                      FadeSlide(
                        delay: const Duration(milliseconds: 140),
                        child: _BreakdownList(
                          rows: data.byPriority
                              .map(
                                (r) => _BreakdownRowData(
                                  title: _capitalize(r.key),
                                  value: r.count,
                                  color: _priorityColor(r.key),
                                ),
                              )
                              .toList(),
                        ),
                      ),
                    ],
                    if (data.byStatus.isNotEmpty) ...[
                      const SizedBox(height: 18),
                      _SectionHeader(
                        icon: PhosphorIconsBold.clipboardText,
                        title: 'By status',
                      ),
                      const SizedBox(height: 10),
                      FadeSlide(
                        delay: const Duration(milliseconds: 180),
                        child: _BreakdownList(
                          rows: data.byStatus
                              .map(
                                (r) => _BreakdownRowData(
                                  title: _capitalize(r.key),
                                  value: r.count,
                                  color: _statusColor(r.key),
                                ),
                              )
                              .toList(),
                        ),
                      ),
                    ],
                    if (data.byDepartment.isNotEmpty) ...[
                      const SizedBox(height: 18),
                      _SectionHeader(
                        icon: PhosphorIconsBold.buildings,
                        title: 'By department',
                      ),
                      const SizedBox(height: 10),
                      FadeSlide(
                        delay: const Duration(milliseconds: 220),
                        child: _DepartmentList(rows: data.byDepartment),
                      ),
                    ],
                    if (data.trend.isNotEmpty) ...[
                      const SizedBox(height: 18),
                      _SectionHeader(
                        icon: PhosphorIconsBold.trendUp,
                        title: 'Last 14 days',
                      ),
                      const SizedBox(height: 10),
                      FadeSlide(
                        delay: const Duration(milliseconds: 260),
                        child: _TrendCard(points: data.trend),
                      ),
                    ],
                    if (data.recent.isNotEmpty) ...[
                      const SizedBox(height: 18),
                      _SectionHeader(
                        icon: PhosphorIconsBold.ticket,
                        title: 'Recent tickets',
                      ),
                      const SizedBox(height: 10),
                      for (var i = 0; i < data.recent.length; i++)
                        Padding(
                          padding: const EdgeInsets.only(bottom: 8),
                          child: _TicketRow(
                            ticket: data.recent[i],
                            onTap: () => _openTicket(data.recent[i].id),
                          ),
                        ),
                    ],
                    if (data.oldestOpen.isNotEmpty) ...[
                      const SizedBox(height: 18),
                      _SectionHeader(
                        icon: PhosphorIconsBold.hourglass,
                        title: 'Oldest open',
                      ),
                      const SizedBox(height: 10),
                      for (var i = 0; i < data.oldestOpen.length; i++)
                        Padding(
                          padding: const EdgeInsets.only(bottom: 8),
                          child: _TicketRow(
                            ticket: data.oldestOpen[i],
                            onTap: () => _openTicket(data.oldestOpen[i].id),
                          ),
                        ),
                    ],
                  ],
                ],
              ),
            );
          },
        ),
      ),
    );
  }
}

String _capitalize(String raw) {
  if (raw.isEmpty) return raw;
  return raw[0].toUpperCase() + raw.substring(1).replaceAll('_', ' ');
}

Color _priorityColor(String key) {
  switch (key.toLowerCase()) {
    case 'high':
    case 'urgent':
    case 'critical':
      return AppTheme.danger;
    case 'medium':
      return AppTheme.accent;
    case 'low':
      return AppTheme.success;
    default:
      return AppTheme.primaryDark;
  }
}

Color _statusColor(String key) {
  switch (key.toLowerCase()) {
    case 'closed':
    case 'resolved':
    case 'done':
    case 'completed':
      return AppTheme.success;
    case 'awaiting_reply':
      return AppTheme.accent;
    case 'open':
    case 'in_progress':
      return AppTheme.primaryDark;
    default:
      return AppTheme.textSecondary;
  }
}

class _TotalsGrid extends StatelessWidget {
  const _TotalsGrid({required this.totals});
  final SupportTotals totals;

  @override
  Widget build(BuildContext context) {
    final cells = <_TotalCellData>[
      _TotalCellData('Total', totals.total, PhosphorIconsBold.ticket, const [
        Color(0xFF1E3A5F),
        Color(0xFF2D5A8A),
      ]),
      _TotalCellData('Open', totals.open, PhosphorIconsBold.folderOpen, const [
        Color(0xFF2563EB),
        Color(0xFF1D4ED8),
      ]),
      _TotalCellData(
        'Closed',
        totals.closed,
        PhosphorIconsBold.checkCircle,
        const [Color(0xFF16A34A), Color(0xFF15803D)],
      ),
      _TotalCellData(
        'Unassigned',
        totals.unassigned,
        PhosphorIconsBold.warningCircle,
        const [Color(0xFFDC2626), Color(0xFFB91C1C)],
      ),
      _TotalCellData(
        'Awaiting reply',
        totals.awaitingReply,
        PhosphorIconsBold.chatCircleDots,
        const [Color(0xFF0891B2), Color(0xFF0E7490)],
      ),
    ];

    return LayoutBuilder(
      builder: (context, constraints) {
        final tileWidth = (constraints.maxWidth - 12) / 2;
        return Wrap(
          spacing: 12,
          runSpacing: 12,
          children: cells.map((cell) {
            return SizedBox(
              width: tileWidth,
              child: Container(
                padding: const EdgeInsets.all(14),
                decoration: BoxDecoration(
                  gradient: LinearGradient(
                    colors: cell.gradient,
                    begin: Alignment.topLeft,
                    end: Alignment.bottomRight,
                  ),
                  borderRadius: BorderRadius.circular(16),
                  boxShadow: [
                    BoxShadow(
                      color: cell.gradient[0].withValues(alpha: 0.25),
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
                        color: AppTheme.surface.withValues(alpha: 0.15),
                        borderRadius: BorderRadius.circular(10),
                      ),
                      child: Icon(cell.icon, size: 16, color: Colors.white),
                    ),
                    const SizedBox(height: 12),
                    TweenAnimationBuilder<int>(
                      tween: IntTween(begin: 0, end: cell.value),
                      duration: const Duration(milliseconds: 900),
                      curve: Curves.easeOutCubic,
                      builder: (context, val, child) {
                        return Text(
                          '$val',
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
                      cell.label,
                      style: TextStyle(
                        color: Colors.white.withValues(alpha: 0.85),
                        fontWeight: FontWeight.w700,
                        fontSize: 12,
                      ),
                    ),
                  ],
                ),
              ),
            );
          }).toList(),
        );
      },
    );
  }
}

class _TotalCellData {
  const _TotalCellData(this.label, this.value, this.icon, this.gradient);
  final String label;
  final int value;
  final IconData icon;
  final List<Color> gradient;
}

class _SectionHeader extends StatelessWidget {
  const _SectionHeader({required this.icon, required this.title});

  final IconData icon;
  final String title;

  @override
  Widget build(BuildContext context) {
    return Row(
      children: [
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
        Text(
          title.toUpperCase(),
          style: TextStyle(
            fontSize: 11,
            fontWeight: FontWeight.w900,
            letterSpacing: 1.5,
            color: AppTheme.textMuted,
          ),
        ),
      ],
    );
  }
}

class _MonthCard extends StatelessWidget {
  const _MonthCard({required this.month, required this.avgHours});
  final SupportMonth month;
  final double avgHours;

  @override
  Widget build(BuildContext context) {
    return MobileSurfaceCard(
      child: Row(
        children: [
          Expanded(
            child: _MonthMetric(
              label: 'Created this month',
              value: month.created.toString(),
              color: AppTheme.primaryDark,
            ),
          ),
          Container(
            width: 1,
            height: 36,
            color: AppTheme.border,
            margin: const EdgeInsets.symmetric(horizontal: 8),
          ),
          Expanded(
            child: _MonthMetric(
              label: 'Closed this month',
              value: month.closed.toString(),
              color: AppTheme.success,
            ),
          ),
          Container(
            width: 1,
            height: 36,
            color: AppTheme.border,
            margin: const EdgeInsets.symmetric(horizontal: 8),
          ),
          Expanded(
            child: _MonthMetric(
              label: 'Avg resolve (h)',
              value: avgHours.toStringAsFixed(1),
              color: AppTheme.accent,
            ),
          ),
        ],
      ),
    );
  }
}

class _MonthMetric extends StatelessWidget {
  const _MonthMetric({
    required this.label,
    required this.value,
    required this.color,
  });
  final String label;
  final String value;
  final Color color;

  @override
  Widget build(BuildContext context) {
    return Column(
      children: [
        Text(
          value,
          style: TextStyle(
            color: color,
            fontWeight: FontWeight.w900,
            fontSize: 18,
            letterSpacing: -0.4,
          ),
        ),
        const SizedBox(height: 4),
        Text(
          label,
          maxLines: 2,
          textAlign: TextAlign.center,
          style: TextStyle(
            color: AppTheme.textSecondary,
            fontSize: 11,
            fontWeight: FontWeight.w700,
          ),
        ),
      ],
    );
  }
}

class _BreakdownRowData {
  const _BreakdownRowData({
    required this.title,
    required this.value,
    required this.color,
  });
  final String title;
  final int value;
  final Color color;
}

class _BreakdownList extends StatelessWidget {
  const _BreakdownList({required this.rows});
  final List<_BreakdownRowData> rows;

  @override
  Widget build(BuildContext context) {
    final maxValue = rows.fold<int>(
      0,
      (prev, r) => r.value > prev ? r.value : prev,
    );
    return MobileSurfaceCard(
      padding: const EdgeInsets.all(14),
      child: Column(
        children: [
          for (final row in rows)
            Padding(
              padding: const EdgeInsets.symmetric(vertical: 6),
              child: Row(
                children: [
                  SizedBox(
                    width: 110,
                    child: Text(
                      row.title,
                      style: TextStyle(
                        color: AppTheme.textPrimary,
                        fontWeight: FontWeight.w800,
                        fontSize: 12.5,
                      ),
                    ),
                  ),
                  Expanded(
                    child: ClipRRect(
                      borderRadius: BorderRadius.circular(999),
                      child: LinearProgressIndicator(
                        value: maxValue == 0 ? 0 : row.value / maxValue,
                        minHeight: 8,
                        backgroundColor: AppTheme.surfaceMuted,
                        valueColor: AlwaysStoppedAnimation(row.color),
                      ),
                    ),
                  ),
                  const SizedBox(width: 10),
                  SizedBox(
                    width: 36,
                    child: Text(
                      row.value.toString(),
                      textAlign: TextAlign.right,
                      style: TextStyle(
                        color: AppTheme.textPrimary,
                        fontWeight: FontWeight.w900,
                        fontSize: 13,
                      ),
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

class _DepartmentList extends StatelessWidget {
  const _DepartmentList({required this.rows});
  final List<SupportDepartment> rows;

  @override
  Widget build(BuildContext context) {
    return MobileSurfaceCard(
      padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 6),
      child: Column(
        children: [
          for (var i = 0; i < rows.length; i++) ...[
            Padding(
              padding: const EdgeInsets.symmetric(vertical: 10),
              child: Row(
                children: [
                  Expanded(
                    child: Text(
                      rows[i].name.isEmpty ? 'Unassigned' : rows[i].name,
                      style: TextStyle(
                        color: AppTheme.textPrimary,
                        fontWeight: FontWeight.w800,
                        fontSize: 13,
                      ),
                    ),
                  ),
                  _Pill(
                    label: '${rows[i].open} open',
                    color: AppTheme.primaryDark,
                  ),
                  const SizedBox(width: 6),
                  _Pill(
                    label: '${rows[i].total} total',
                    color: AppTheme.textSecondary,
                  ),
                ],
              ),
            ),
            if (i != rows.length - 1)
              Divider(height: 1, color: AppTheme.border),
          ],
        ],
      ),
    );
  }
}

class _Pill extends StatelessWidget {
  const _Pill({required this.label, required this.color});
  final String label;
  final Color color;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
      decoration: BoxDecoration(
        color: color.withValues(alpha: 0.10),
        borderRadius: BorderRadius.circular(999),
      ),
      child: Text(
        label,
        style: TextStyle(
          color: color,
          fontSize: 10.5,
          fontWeight: FontWeight.w900,
          letterSpacing: 0.6,
        ),
      ),
    );
  }
}

class _TrendCard extends StatelessWidget {
  const _TrendCard({required this.points});
  final List<SupportTrendPoint> points;

  @override
  Widget build(BuildContext context) {
    final maxCount = points.fold<int>(0, (prev, p) {
      final v = p.created > p.closed ? p.created : p.closed;
      return v > prev ? v : prev;
    });
    return MobileSurfaceCard(
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: const [
              _LegendDot(color: AppTheme.primaryDark, label: 'Created'),
              SizedBox(width: 12),
              _LegendDot(color: AppTheme.success, label: 'Closed'),
            ],
          ),
          const SizedBox(height: 14),
          SizedBox(
            height: 120,
            child: Row(
              crossAxisAlignment: CrossAxisAlignment.end,
              children: [
                for (final p in points)
                  Expanded(
                    child: Padding(
                      padding: const EdgeInsets.symmetric(horizontal: 1.5),
                      child: Column(
                        mainAxisAlignment: MainAxisAlignment.end,
                        children: [
                          _BarPair(
                            created: maxCount == 0 ? 0 : p.created / maxCount,
                            closed: maxCount == 0 ? 0 : p.closed / maxCount,
                            availableHeight: 96,
                          ),
                          const SizedBox(height: 6),
                          Text(
                            p.label,
                            style: TextStyle(
                              color: AppTheme.textMuted,
                              fontSize: 8,
                              fontWeight: FontWeight.w700,
                            ),
                          ),
                        ],
                      ),
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

class _BarPair extends StatelessWidget {
  const _BarPair({
    required this.created,
    required this.closed,
    required this.availableHeight,
  });
  final double created;
  final double closed;
  final double availableHeight;

  @override
  Widget build(BuildContext context) {
    return SizedBox(
      height: availableHeight,
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.end,
        children: [
          Expanded(
            child: Container(
              height: (availableHeight * created).clamp(2.0, availableHeight),
              decoration: BoxDecoration(
                color: AppTheme.primaryDark,
                borderRadius: BorderRadius.circular(2),
              ),
            ),
          ),
          const SizedBox(width: 2),
          Expanded(
            child: Container(
              height: (availableHeight * closed).clamp(2.0, availableHeight),
              decoration: BoxDecoration(
                color: AppTheme.success,
                borderRadius: BorderRadius.circular(2),
              ),
            ),
          ),
        ],
      ),
    );
  }
}

class _LegendDot extends StatelessWidget {
  const _LegendDot({required this.color, required this.label});
  final Color color;
  final String label;

  @override
  Widget build(BuildContext context) {
    return Row(
      mainAxisSize: MainAxisSize.min,
      children: [
        Container(
          width: 10,
          height: 10,
          decoration: BoxDecoration(
            color: color,
            borderRadius: BorderRadius.circular(3),
          ),
        ),
        const SizedBox(width: 6),
        Text(
          label,
          style: TextStyle(
            color: AppTheme.textSecondary,
            fontSize: 11.5,
            fontWeight: FontWeight.w800,
          ),
        ),
      ],
    );
  }
}

class _TicketRow extends StatelessWidget {
  const _TicketRow({required this.ticket, required this.onTap});
  final SupportTicketLite ticket;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    final priorityColor = _priorityColor(ticket.priority);
    final statusColor = _statusColor(ticket.status);
    return MobileSurfaceCard(
      padding: const EdgeInsets.all(12),
      child: InkWell(
        onTap: onTap,
        borderRadius: BorderRadius.circular(18),
        child: Row(
          children: [
            Container(
              width: 4,
              height: 44,
              decoration: BoxDecoration(
                color: priorityColor,
                borderRadius: BorderRadius.circular(4),
              ),
            ),
            const SizedBox(width: 10),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    children: [
                      if (ticket.ticketNumber.isNotEmpty) ...[
                        Text(
                          '#${ticket.ticketNumber}',
                          style: TextStyle(
                            color: AppTheme.textMuted,
                            fontSize: 11,
                            fontWeight: FontWeight.w900,
                          ),
                        ),
                        const SizedBox(width: 6),
                      ],
                      Expanded(
                        child: Text(
                          ticket.title,
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                          style: TextStyle(
                            color: AppTheme.textPrimary,
                            fontWeight: FontWeight.w900,
                            fontSize: 13.5,
                          ),
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 4),
                  Wrap(
                    spacing: 6,
                    runSpacing: 4,
                    children: [
                      _Pill(
                        label: _capitalize(ticket.status),
                        color: statusColor,
                      ),
                      if (ticket.department.isNotEmpty)
                        _Pill(
                          label: ticket.department,
                          color: AppTheme.textSecondary,
                        ),
                      if (ticket.assignee.isNotEmpty)
                        _Pill(
                          label: ticket.assignee,
                          color: AppTheme.primaryDark,
                        ),
                    ],
                  ),
                  if (ticket.createdLabel.isNotEmpty) ...[
                    const SizedBox(height: 4),
                    Text(
                      ticket.createdLabel,
                      style: TextStyle(
                        color: AppTheme.textMuted,
                        fontSize: 11,
                        fontWeight: FontWeight.w700,
                      ),
                    ),
                  ],
                ],
              ),
            ),
            Icon(
              PhosphorIconsBold.caretRight,
              size: 14,
              color: AppTheme.textMuted,
            ),
          ],
        ),
      ),
    );
  }
}

class _ErrorBlock extends StatelessWidget {
  const _ErrorBlock({required this.message, required this.onRetry});
  final String message;
  final VoidCallback onRetry;

  @override
  Widget build(BuildContext context) {
    return MobileSurfaceCard(
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            'Unable to load',
            style: TextStyle(
              fontWeight: FontWeight.w900,
              color: AppTheme.textPrimary,
              fontSize: 14.5,
            ),
          ),
          const SizedBox(height: 6),
          Text(
            message,
            style: TextStyle(color: AppTheme.textSecondary, fontSize: 13),
          ),
          const SizedBox(height: 12),
          Align(
            alignment: Alignment.centerRight,
            child: FilledButton(
              onPressed: onRetry,
              style: FilledButton.styleFrom(
                backgroundColor: AppTheme.primaryDark,
              ),
              child: const Text('Retry'),
            ),
          ),
        ],
      ),
    );
  }
}
