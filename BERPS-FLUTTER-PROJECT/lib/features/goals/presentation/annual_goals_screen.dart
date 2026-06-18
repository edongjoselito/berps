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
import 'annual_goal_detail_screen.dart';

class AnnualGoalsScreen extends StatefulWidget {
  const AnnualGoalsScreen({super.key, required this.session});
  final StaffSession session;

  @override
  State<AnnualGoalsScreen> createState() => _AnnualGoalsScreenState();
}

class _AnnualGoalsScreenState extends State<AnnualGoalsScreen> {
  final StaffApi _api = StaffApi();
  Future<AnnualGoalsData>? _future;

  @override
  void initState() {
    super.initState();
    _reload();
  }

  void _reload() {
    setState(() {
      _future = _api.fetchAnnualGoals(
        baseUrl: widget.session.baseUrl,
        token: widget.session.token,
      );
    });
  }

  void _openYear(int year) {
    Haptics.light();
    Navigator.of(context).push(
      MaterialPageRoute(
        builder: (_) =>
            AnnualGoalDetailScreen(session: widget.session, year: year),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppTheme.background,
      body: SafeArea(
        bottom: false,
        child: FutureBuilder<AnnualGoalsData>(
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
                    title: 'Annual Goals',
                    subtitle: 'Yearly targets & progress',
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
                          child: SkeletonCard(child: SizedBox(height: 110)),
                        ),
                      ),
                    )
                  else if (error != null && data == null)
                    _ErrorCard(
                      message: error is ApiException
                          ? error.message
                          : 'Unable to load goals.',
                      onRetry: _reload,
                    )
                  else if (data != null) ...[
                    if (data.current != null) ...[
                      FadeSlide(
                        delay: const Duration(milliseconds: 60),
                        child: _CurrentGoalCard(
                          goal: data.current!,
                          onTap: () => _openYear(data.current!.year),
                        ),
                      ),
                      const SizedBox(height: 18),
                    ],
                    _SectionHeader(
                      icon: PhosphorIconsBold.calendarDots,
                      title: 'All years',
                      count: data.goals.length,
                    ),
                    const SizedBox(height: 10),
                    if (data.goals.isEmpty)
                      const _EmptyState()
                    else
                      for (var i = 0; i < data.goals.length; i++)
                        Padding(
                          padding: const EdgeInsets.only(bottom: 10),
                          child: FadeSlide(
                            delay: Duration(milliseconds: 120 + i * 40),
                            child: _YearCard(
                              goal: data.goals[i],
                              onTap: () => _openYear(data.goals[i].year),
                            ),
                          ),
                        ),
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

class _CurrentGoalCard extends StatelessWidget {
  const _CurrentGoalCard({required this.goal, required this.onTap});
  final AnnualGoal goal;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    return MobileSurfaceCard(
      child: InkWell(
        onTap: onTap,
        borderRadius: BorderRadius.circular(18),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              children: [
                Container(
                  padding: const EdgeInsets.symmetric(
                    horizontal: 8,
                    vertical: 4,
                  ),
                  decoration: BoxDecoration(
                    color: AppTheme.primaryDark.withValues(alpha: 0.08),
                    borderRadius: BorderRadius.circular(999),
                  ),
                  child: const Text(
                    'CURRENT YEAR',
                    style: TextStyle(
                      color: AppTheme.primaryDark,
                      fontSize: 10,
                      fontWeight: FontWeight.w900,
                      letterSpacing: 1.2,
                    ),
                  ),
                ),
                const Spacer(),
                Text(
                  '${goal.year}',
                  style: TextStyle(
                    color: AppTheme.textPrimary,
                    fontSize: 22,
                    fontWeight: FontWeight.w900,
                    letterSpacing: -0.5,
                  ),
                ),
              ],
            ),
            const SizedBox(height: 14),
            _ProgressRow(
              label: 'Clients',
              actual: goal.actualClients.toDouble(),
              target: goal.targetClients.toDouble(),
              percent: goal.clientsProgressPct,
              isCurrency: false,
              color: AppTheme.primaryDark,
            ),
            const SizedBox(height: 12),
            _ProgressRow(
              label: 'Income',
              actual: goal.actualIncome,
              target: goal.targetIncome,
              percent: goal.incomeProgressPct,
              isCurrency: true,
              color: AppTheme.success,
            ),
            if (goal.notes.isNotEmpty) ...[
              const SizedBox(height: 14),
              Container(
                padding: const EdgeInsets.all(12),
                decoration: BoxDecoration(
                  color: AppTheme.surfaceMuted,
                  borderRadius: BorderRadius.circular(12),
                ),
                child: Text(
                  goal.notes,
                  style: TextStyle(
                    color: AppTheme.textSecondary,
                    fontSize: 12.5,
                    height: 1.4,
                  ),
                ),
              ),
            ],
          ],
        ),
      ),
    );
  }
}

class _YearCard extends StatelessWidget {
  const _YearCard({required this.goal, required this.onTap});
  final AnnualGoal goal;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    return MobileSurfaceCard(
      padding: const EdgeInsets.all(14),
      child: InkWell(
        onTap: onTap,
        borderRadius: BorderRadius.circular(18),
        child: Row(
          children: [
            Container(
              width: 56,
              height: 56,
              decoration: BoxDecoration(
                color: AppTheme.primarySoft,
                borderRadius: BorderRadius.circular(14),
              ),
              child: Center(
                child: Text(
                  '${goal.year}',
                  style: const TextStyle(
                    color: AppTheme.primaryDark,
                    fontSize: 15,
                    fontWeight: FontWeight.w900,
                  ),
                ),
              ),
            ),
            const SizedBox(width: 12),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    children: [
                      Expanded(
                        child: _ProgressMini(
                          label: 'Clients',
                          actual: goal.actualClients.toDouble(),
                          target: goal.targetClients.toDouble(),
                          percent: goal.clientsProgressPct,
                          color: AppTheme.primaryDark,
                          isCurrency: false,
                        ),
                      ),
                      const SizedBox(width: 12),
                      Expanded(
                        child: _ProgressMini(
                          label: 'Income',
                          actual: goal.actualIncome,
                          target: goal.targetIncome,
                          percent: goal.incomeProgressPct,
                          color: AppTheme.success,
                          isCurrency: true,
                        ),
                      ),
                    ],
                  ),
                ],
              ),
            ),
            const SizedBox(width: 6),
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

class _ProgressRow extends StatelessWidget {
  const _ProgressRow({
    required this.label,
    required this.actual,
    required this.target,
    required this.percent,
    required this.isCurrency,
    required this.color,
  });

  final String label;
  final double actual;
  final double target;
  final double percent;
  final bool isCurrency;
  final Color color;

  String _formatNumber(double value) {
    if (isCurrency) return '₱${_thousands(value.toStringAsFixed(0))}';
    return _thousands(value.toStringAsFixed(0));
  }

  static String _thousands(String raw) {
    final buffer = StringBuffer();
    for (var i = 0; i < raw.length; i++) {
      if (i > 0 && (raw.length - i) % 3 == 0) buffer.write(',');
      buffer.write(raw[i]);
    }
    return buffer.toString();
  }

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
              style: TextStyle(
                color: AppTheme.textSecondary,
                fontSize: 12,
                fontWeight: FontWeight.w800,
                letterSpacing: 0.4,
              ),
            ),
            const Spacer(),
            Text(
              '${percent.toStringAsFixed(1)}%',
              style: TextStyle(
                color: color,
                fontSize: 12.5,
                fontWeight: FontWeight.w900,
              ),
            ),
          ],
        ),
        const SizedBox(height: 6),
        ClipRRect(
          borderRadius: BorderRadius.circular(999),
          child: LinearProgressIndicator(
            value: pct,
            minHeight: 8,
            backgroundColor: AppTheme.surfaceMuted,
            valueColor: AlwaysStoppedAnimation(color),
          ),
        ),
        const SizedBox(height: 6),
        Text(
          '${_formatNumber(actual)} of ${_formatNumber(target)}',
          style: TextStyle(
            color: AppTheme.textPrimary,
            fontSize: 12.5,
            fontWeight: FontWeight.w800,
          ),
        ),
      ],
    );
  }
}

class _ProgressMini extends StatelessWidget {
  const _ProgressMini({
    required this.label,
    required this.actual,
    required this.target,
    required this.percent,
    required this.color,
    required this.isCurrency,
  });

  final String label;
  final double actual;
  final double target;
  final double percent;
  final Color color;
  final bool isCurrency;

  @override
  Widget build(BuildContext context) {
    final pct = (percent / 100).clamp(0.0, 1.0);
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          label,
          style: TextStyle(
            color: AppTheme.textSecondary,
            fontSize: 11,
            fontWeight: FontWeight.w800,
            letterSpacing: 0.4,
          ),
        ),
        const SizedBox(height: 4),
        ClipRRect(
          borderRadius: BorderRadius.circular(999),
          child: LinearProgressIndicator(
            value: pct,
            minHeight: 5,
            backgroundColor: AppTheme.surfaceMuted,
            valueColor: AlwaysStoppedAnimation(color),
          ),
        ),
        const SizedBox(height: 4),
        Text(
          '${percent.toStringAsFixed(0)}%',
          style: TextStyle(
            color: color,
            fontSize: 11,
            fontWeight: FontWeight.w900,
          ),
        ),
      ],
    );
  }
}

class _SectionHeader extends StatelessWidget {
  const _SectionHeader({
    required this.icon,
    required this.title,
    required this.count,
  });

  final IconData icon;
  final String title;
  final int count;

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

class _EmptyState extends StatelessWidget {
  const _EmptyState();

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(24),
      decoration: BoxDecoration(
        color: AppTheme.surface,
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
          Text(
            'No goals set',
            style: TextStyle(
              fontWeight: FontWeight.w900,
              color: AppTheme.textPrimary,
              fontSize: 15,
            ),
          ),
          const SizedBox(height: 6),
          Text(
            'Annual goals are set by an admin from the web app.',
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

class _ErrorCard extends StatelessWidget {
  const _ErrorCard({required this.message, required this.onRetry});
  final String message;
  final VoidCallback onRetry;

  @override
  Widget build(BuildContext context) {
    return MobileSurfaceCard(
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            'Goals unavailable',
            style: TextStyle(
              fontWeight: FontWeight.w900,
              color: AppTheme.textPrimary,
              fontSize: 15,
            ),
          ),
          const SizedBox(height: 6),
          Text(
            message,
            style: TextStyle(color: AppTheme.textSecondary, fontSize: 13),
          ),
          const SizedBox(height: 14),
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
