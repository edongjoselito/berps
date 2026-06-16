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
import '../domain/annual_goal.dart';

class AnnualGoalDetailScreen extends StatefulWidget {
  const AnnualGoalDetailScreen({
    super.key,
    required this.session,
    required this.year,
  });

  final StaffSession session;
  final int year;

  @override
  State<AnnualGoalDetailScreen> createState() => _AnnualGoalDetailScreenState();
}

class _AnnualGoalDetailScreenState extends State<AnnualGoalDetailScreen> {
  final StaffApi _api = StaffApi();
  Future<AnnualGoalDetail>? _future;

  @override
  void initState() {
    super.initState();
    _reload();
  }

  void _reload() {
    setState(() {
      _future = _api.fetchAnnualGoalDetail(
        baseUrl: widget.session.baseUrl,
        token: widget.session.token,
        year: widget.year,
      );
    });
  }

  String _formatCurrency(double v) {
    final raw = v.toStringAsFixed(0);
    final buffer = StringBuffer();
    for (var i = 0; i < raw.length; i++) {
      if (i > 0 && (raw.length - i) % 3 == 0) buffer.write(',');
      buffer.write(raw[i]);
    }
    return '₱$buffer';
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppTheme.background,
      body: SafeArea(
        bottom: false,
        child: FutureBuilder<AnnualGoalDetail>(
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
                    title: '${widget.year}',
                    subtitle: 'Annual goal',
                    leadingIcon: PhosphorIconsBold.caretLeft,
                    onLeadingTap: () {
                      Haptics.light();
                      Navigator.of(context).maybePop();
                    },
                  ),
                  const SizedBox(height: 14),
                  if (loading && data == null)
                    const SkeletonCard(child: SizedBox(height: 220))
                  else if (error != null && data == null)
                    _ErrorBlock(
                      message: error is ApiException
                          ? error.message
                          : 'Unable to load detail.',
                      onRetry: _reload,
                    )
                  else if (data != null) ...[
                    if (data.goal != null) ...[
                      FadeSlide(
                        delay: const Duration(milliseconds: 60),
                        child: _SummaryCard(goal: data.goal!),
                      ),
                      const SizedBox(height: 18),
                    ] else
                      const _NoGoalSet(),
                    if (data.monthly.isNotEmpty) ...[
                      _SectionHeader(
                        icon: PhosphorIconsBold.calendarDots,
                        title: 'Monthly breakdown',
                      ),
                      const SizedBox(height: 10),
                      FadeSlide(
                        delay: const Duration(milliseconds: 120),
                        child: _MonthlyTable(
                          months: data.monthly,
                          formatCurrency: _formatCurrency,
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

class _SummaryCard extends StatelessWidget {
  const _SummaryCard({required this.goal});
  final AnnualGoal goal;

  String _formatNumber(double v, {bool currency = false}) {
    final raw = v.toStringAsFixed(0);
    final buffer = StringBuffer();
    for (var i = 0; i < raw.length; i++) {
      if (i > 0 && (raw.length - i) % 3 == 0) buffer.write(',');
      buffer.write(raw[i]);
    }
    return currency ? '₱$buffer' : buffer.toString();
  }

  @override
  Widget build(BuildContext context) {
    return MobileSurfaceCard(
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          _MetricBlock(
            label: 'Clients',
            actual: _formatNumber(goal.actualClients.toDouble()),
            target: _formatNumber(goal.targetClients.toDouble()),
            percent: goal.clientsProgressPct,
            color: AppTheme.primaryDark,
          ),
          const SizedBox(height: 16),
          _MetricBlock(
            label: 'Income',
            actual: _formatNumber(goal.actualIncome, currency: true),
            target: _formatNumber(goal.targetIncome, currency: true),
            percent: goal.incomeProgressPct,
            color: AppTheme.success,
          ),
          if (goal.notes.isNotEmpty) ...[
            const SizedBox(height: 16),
            const Text(
              'Notes',
              style: TextStyle(
                color: AppTheme.textSecondary,
                fontSize: 11,
                fontWeight: FontWeight.w900,
                letterSpacing: 1.2,
              ),
            ),
            const SizedBox(height: 6),
            Text(
              goal.notes,
              style: const TextStyle(
                color: AppTheme.textPrimary,
                fontSize: 13,
                height: 1.4,
              ),
            ),
          ],
          if (goal.createdBy.isNotEmpty) ...[
            const SizedBox(height: 12),
            Text(
              'Set by ${goal.createdBy}',
              style: const TextStyle(
                color: AppTheme.textMuted,
                fontSize: 11,
                fontWeight: FontWeight.w700,
              ),
            ),
          ],
        ],
      ),
    );
  }
}

class _MetricBlock extends StatelessWidget {
  const _MetricBlock({
    required this.label,
    required this.actual,
    required this.target,
    required this.percent,
    required this.color,
  });

  final String label;
  final String actual;
  final String target;
  final double percent;
  final Color color;

  @override
  Widget build(BuildContext context) {
    final pct = (percent / 100).clamp(0.0, 1.0);
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Row(
          children: [
            Text(
              label,
              style: const TextStyle(
                color: AppTheme.textSecondary,
                fontSize: 12,
                fontWeight: FontWeight.w900,
                letterSpacing: 0.4,
              ),
            ),
            const Spacer(),
            Text(
              '${percent.toStringAsFixed(1)}%',
              style: TextStyle(
                color: color,
                fontSize: 13.5,
                fontWeight: FontWeight.w900,
              ),
            ),
          ],
        ),
        const SizedBox(height: 8),
        ClipRRect(
          borderRadius: BorderRadius.circular(999),
          child: LinearProgressIndicator(
            value: pct,
            minHeight: 10,
            backgroundColor: AppTheme.surfaceMuted,
            valueColor: AlwaysStoppedAnimation(color),
          ),
        ),
        const SizedBox(height: 6),
        Text(
          '$actual of $target',
          style: const TextStyle(
            color: AppTheme.textPrimary,
            fontSize: 13.5,
            fontWeight: FontWeight.w900,
          ),
        ),
      ],
    );
  }
}

class _MonthlyTable extends StatelessWidget {
  const _MonthlyTable({required this.months, required this.formatCurrency});

  final List<AnnualGoalMonth> months;
  final String Function(double) formatCurrency;

  @override
  Widget build(BuildContext context) {
    return MobileSurfaceCard(
      padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 6),
      child: Column(
        children: [
          Padding(
            padding: const EdgeInsets.symmetric(vertical: 10),
            child: Row(
              children: const [
                SizedBox(
                  width: 48,
                  child: Text(
                    'MO',
                    style: TextStyle(
                      color: AppTheme.textMuted,
                      fontSize: 11,
                      fontWeight: FontWeight.w900,
                      letterSpacing: 1.2,
                    ),
                  ),
                ),
                Expanded(
                  child: Text(
                    'CLIENTS',
                    style: TextStyle(
                      color: AppTheme.textMuted,
                      fontSize: 11,
                      fontWeight: FontWeight.w900,
                      letterSpacing: 1.2,
                    ),
                  ),
                ),
                Expanded(
                  child: Text(
                    'INCOME',
                    textAlign: TextAlign.right,
                    style: TextStyle(
                      color: AppTheme.textMuted,
                      fontSize: 11,
                      fontWeight: FontWeight.w900,
                      letterSpacing: 1.2,
                    ),
                  ),
                ),
              ],
            ),
          ),
          const Divider(height: 1, color: AppTheme.border),
          for (final m in months) ...[
            Padding(
              padding: const EdgeInsets.symmetric(vertical: 10),
              child: Row(
                children: [
                  SizedBox(
                    width: 48,
                    child: Text(
                      m.label,
                      style: const TextStyle(
                        color: AppTheme.textPrimary,
                        fontSize: 13,
                        fontWeight: FontWeight.w800,
                      ),
                    ),
                  ),
                  Expanded(
                    child: Text(
                      m.clients.toString(),
                      style: const TextStyle(
                        color: AppTheme.textPrimary,
                        fontSize: 13,
                        fontWeight: FontWeight.w700,
                      ),
                    ),
                  ),
                  Expanded(
                    child: Text(
                      formatCurrency(m.income),
                      textAlign: TextAlign.right,
                      style: const TextStyle(
                        color: AppTheme.textPrimary,
                        fontSize: 13,
                        fontWeight: FontWeight.w700,
                      ),
                    ),
                  ),
                ],
              ),
            ),
            if (m != months.last)
              const Divider(height: 1, color: AppTheme.border),
          ],
        ],
      ),
    );
  }
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
          style: const TextStyle(
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

class _NoGoalSet extends StatelessWidget {
  const _NoGoalSet();

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(24),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(18),
        border: Border.all(color: AppTheme.border),
      ),
      child: Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          Container(
            width: 56,
            height: 56,
            decoration: BoxDecoration(
              color: AppTheme.primarySoft,
              shape: BoxShape.circle,
            ),
            child: const Icon(
              PhosphorIconsBold.trophy,
              size: 26,
              color: AppTheme.primaryDark,
            ),
          ),
          const SizedBox(height: 14),
          const Text(
            'No goal set for this year',
            style: TextStyle(
              color: AppTheme.textPrimary,
              fontWeight: FontWeight.w900,
              fontSize: 15,
            ),
          ),
          const SizedBox(height: 6),
          const Text(
            'An admin can set the targets from the web app.',
            textAlign: TextAlign.center,
            style: TextStyle(
              color: AppTheme.textSecondary,
              fontSize: 13,
              height: 1.4,
            ),
          ),
        ],
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
          const Text(
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
            style: const TextStyle(color: AppTheme.textSecondary, fontSize: 13),
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
