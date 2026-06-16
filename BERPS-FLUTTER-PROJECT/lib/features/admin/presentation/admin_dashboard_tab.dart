import 'package:flutter/material.dart';
import 'package:phosphor_flutter/phosphor_flutter.dart';

import '../../../core/network/api_exception.dart';
import '../../../core/theme/app_theme.dart';
import '../../../core/utils/date_formatters.dart';
import '../../../core/utils/haptics.dart';
import '../../../core/utils/responsive.dart';
import '../../../core/widgets/animations.dart';
import '../../../core/widgets/skeleton.dart';
import '../../auth/domain/staff_session.dart';
import '../data/admin_api.dart';
import '../domain/admin_models.dart';
import 'admin_widgets.dart';

class AdminDashboardTab extends StatefulWidget {
  const AdminDashboardTab({
    super.key,
    required this.session,
    required this.onMenu,
    required this.onOpenTasks,
    required this.onOpenClients,
  });

  final StaffSession session;
  final VoidCallback onMenu;
  final VoidCallback onOpenTasks;
  final VoidCallback onOpenClients;

  @override
  State<AdminDashboardTab> createState() => _AdminDashboardTabState();
}

class _AdminDashboardTabState extends State<AdminDashboardTab> {
  final AdminApi _api = AdminApi();
  late Future<AdminDashboard> _future;

  @override
  void initState() {
    super.initState();
    _future = _load();
  }

  Future<AdminDashboard> _load() => _api.fetchDashboard(
        baseUrl: widget.session.baseUrl,
        token: widget.session.token,
      );

  void _reload() {
    Haptics.light();
    setState(() => _future = _load());
  }

  String _peso(double v) {
    final s = v
        .toStringAsFixed(v.truncateToDouble() == v ? 0 : 2)
        .replaceAllMapped(
          RegExp(r'(\d)(?=(\d{3})+(?!\d))'),
          (m) => '${m[1]},',
        );
    return '₱$s';
  }

  @override
  Widget build(BuildContext context) {
    final gutter = context.gutter;
    return RefreshIndicator(
      color: AppTheme.primary,
      onRefresh: () async {
        setState(() => _future = _load());
        await _future;
      },
      child: FutureBuilder<AdminDashboard>(
        future: _future,
        builder: (context, snapshot) {
          return ListView(
            physics: const AlwaysScrollableScrollPhysics(),
            padding: EdgeInsets.fromLTRB(gutter, 12, gutter, 28),
            children: [
              AdminHeader(
                title: 'Dashboard',
                onMenu: widget.onMenu,
                trailingIcon: PhosphorIconsBold.arrowClockwise,
                onTrailingTap: _reload,
              ),
              const SizedBox(height: 14),
              FadeSlide(
                delay: const Duration(milliseconds: 60),
                child: AdminGreetingCard(session: widget.session),
              ),
              const SizedBox(height: 18),
              if (snapshot.connectionState == ConnectionState.waiting)
                _loading()
              else if (snapshot.hasError)
                AdminErrorView(
                  message: snapshot.error is ApiException
                      ? (snapshot.error as ApiException).message
                      : snapshot.error.toString(),
                  onRetry: _reload,
                )
              else
                _content(context, snapshot.data!),
            ],
          );
        },
      ),
    );
  }

  Widget _loading() {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: const [
        Skeleton(width: 140, height: 16),
        SizedBox(height: 14),
        Skeleton(height: 150, radius: 18),
        SizedBox(height: 16),
        Skeleton(height: 120, radius: 18),
      ],
    );
  }

  Widget _content(BuildContext context, AdminDashboard d) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        FadeSlide(
          delay: const Duration(milliseconds: 120),
          child: const AdminSectionHeader(title: 'Today'),
        ),
        const SizedBox(height: 12),
        FadeSlide(
          delay: const Duration(milliseconds: 160),
          child: AdminMetricGrid(
            cards: [
              AdminMetricCardData(
                label: 'Payments',
                value: _peso(d.todaysPayments),
                icon: PhosphorIconsBold.coins,
                accent: AppTheme.success,
              ),
              AdminMetricCardData(
                label: 'Expenses',
                value: _peso(d.todaysExpenses),
                icon: PhosphorIconsBold.receipt,
                accent: AppTheme.danger,
              ),
              AdminMetricCardData(
                label: 'Receivables',
                value: _peso(d.openReceivable),
                icon: PhosphorIconsBold.scales,
                accent: AppTheme.warning,
              ),
              AdminMetricCardData(
                label: 'Clients',
                value: '${d.totalClients}',
                icon: PhosphorIconsBold.usersThree,
                accent: AppTheme.primary,
                onTap: widget.onOpenClients,
              ),
            ],
          ),
        ),
        const SizedBox(height: 22),
        FadeSlide(
          delay: const Duration(milliseconds: 200),
          child: const AdminSectionHeader(title: 'Tasks'),
        ),
        const SizedBox(height: 12),
        FadeSlide(
          delay: const Duration(milliseconds: 240),
          child: _taskCountsRow(d.taskCounts),
        ),
        const SizedBox(height: 22),
        FadeSlide(
          delay: const Duration(milliseconds: 280),
          child: AdminSectionHeader(
            title: 'Due queue',
            action: GestureDetector(
              onTap: widget.onOpenTasks,
              child: const Text(
                'View all',
                style: TextStyle(
                  fontSize: 12.5,
                  fontWeight: FontWeight.w800,
                  color: AppTheme.primaryDark,
                ),
              ),
            ),
          ),
        ),
        const SizedBox(height: 12),
        if (d.taskQueue.isEmpty)
          _miniEmpty('No upcoming tasks in the next 7 days.')
        else
          ...d.taskQueue.map(
            (t) => FadeSlide(
              delay: const Duration(milliseconds: 300),
              child: _queueCard(t),
            ),
          ),
        const SizedBox(height: 22),
        FadeSlide(
          delay: const Duration(milliseconds: 320),
          child: const AdminSectionHeader(title: 'Top performers this month'),
        ),
        const SizedBox(height: 12),
        if (d.accomplished.isEmpty)
          _miniEmpty('No accomplishments recorded yet this month.')
        else
          FadeSlide(
            delay: const Duration(milliseconds: 340),
            child: Container(
              padding: const EdgeInsets.all(16),
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.circular(20),
                border: Border.all(color: AppTheme.border),
                boxShadow: AppTheme.shadowSoft,
              ),
              child: Column(
                children: [
                  for (var i = 0; i < d.accomplished.length; i++) ...[
                    if (i > 0) const Divider(height: 18),
                    Row(
                      children: [
                        _rankBadge(i + 1),
                        const SizedBox(width: 12),
                        Expanded(
                          child: Text(
                            d.accomplished[i].name,
                            maxLines: 1,
                            overflow: TextOverflow.ellipsis,
                            style: const TextStyle(
                              fontWeight: FontWeight.w700,
                              fontSize: 14,
                              color: AppTheme.textPrimary,
                            ),
                          ),
                        ),
                        const SizedBox(width: 8),
                        Text(
                          '${d.accomplished[i].total} pts',
                          style: const TextStyle(
                            fontWeight: FontWeight.w800,
                            color: AppTheme.primaryDark,
                          ),
                        ),
                      ],
                    ),
                  ],
                ],
              ),
            ),
          ),
      ],
    );
  }

  Widget _taskCountsRow(TaskCounts c) {
    Widget chip(String label, int value, Color color, IconData icon) {
      return Expanded(
        child: Container(
          padding: const EdgeInsets.symmetric(vertical: 14, horizontal: 8),
          decoration: BoxDecoration(
            color: color.withValues(alpha: 0.08),
            borderRadius: BorderRadius.circular(16),
            border: Border.all(color: color.withValues(alpha: 0.18)),
          ),
          child: Column(
            children: [
              Icon(icon, color: color, size: 20),
              const SizedBox(height: 6),
              Text(
                '$value',
                style: TextStyle(
                  fontWeight: FontWeight.w900,
                  fontSize: 18,
                  color: color,
                ),
              ),
              Text(
                label,
                maxLines: 1,
                overflow: TextOverflow.ellipsis,
                style: const TextStyle(
                  fontSize: 11,
                  fontWeight: FontWeight.w600,
                  color: AppTheme.textSecondary,
                ),
              ),
            ],
          ),
        ),
      );
    }

    return Row(
      children: [
        chip('Due today', c.dueToday, AppTheme.warning, PhosphorIconsBold.clock),
        const SizedBox(width: 10),
        chip('Due soon', c.dueSoon, AppTheme.primaryDark,
            PhosphorIconsBold.calendarDots),
        const SizedBox(width: 10),
        chip('Overdue', c.overdue, AppTheme.danger,
            PhosphorIconsBold.warningCircle),
      ],
    );
  }

  Widget _queueCard(TaskQueueItem t) {
    return Container(
      margin: const EdgeInsets.only(bottom: 10),
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
          Text(
            t.title,
            maxLines: 2,
            overflow: TextOverflow.ellipsis,
            style: const TextStyle(
              fontWeight: FontWeight.w800,
              fontSize: 14.5,
              color: AppTheme.textPrimary,
            ),
          ),
          const SizedBox(height: 4),
          Text(
            '${t.assignedName.isEmpty ? 'Unassigned' : t.assignedName} · ${t.subtitle.isEmpty ? 'No project' : t.subtitle}',
            maxLines: 1,
            overflow: TextOverflow.ellipsis,
            style: const TextStyle(fontSize: 12, color: AppTheme.textSecondary),
          ),
          const SizedBox(height: 10),
          Row(
            children: [
              const Icon(PhosphorIconsBold.calendarBlank,
                  size: 13, color: AppTheme.textMuted),
              const SizedBox(width: 5),
              Expanded(
                child: Text(
                  t.dueDate.isEmpty
                      ? 'No due date'
                      : formatCompactDate(t.dueDate),
                  maxLines: 1,
                  overflow: TextOverflow.ellipsis,
                  style: const TextStyle(
                    fontSize: 11.5,
                    fontWeight: FontWeight.w600,
                    color: AppTheme.textSecondary,
                  ),
                ),
              ),
              const SizedBox(width: 8),
              Text(
                '${t.progress}%',
                style: const TextStyle(
                  fontSize: 11.5,
                  fontWeight: FontWeight.w800,
                  color: AppTheme.primaryDark,
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }

  Widget _rankBadge(int rank) {
    return Container(
      width: 26,
      height: 26,
      alignment: Alignment.center,
      decoration: BoxDecoration(
        color: rank <= 3 ? AppTheme.accentSoft : AppTheme.surfaceMuted,
        borderRadius: BorderRadius.circular(8),
      ),
      child: Text(
        '$rank',
        style: TextStyle(
          fontWeight: FontWeight.w900,
          fontSize: 12,
          color: rank <= 3 ? AppTheme.accent : AppTheme.textSecondary,
        ),
      ),
    );
  }

  Widget _miniEmpty(String message) {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.symmetric(vertical: 22, horizontal: 16),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: AppTheme.border),
      ),
      child: Text(
        message,
        textAlign: TextAlign.center,
        style: const TextStyle(color: AppTheme.textSecondary, fontSize: 13),
      ),
    );
  }
}
