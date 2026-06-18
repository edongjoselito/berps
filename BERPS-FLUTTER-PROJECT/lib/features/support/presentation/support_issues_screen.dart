import 'package:flutter/material.dart';
import 'package:phosphor_flutter/phosphor_flutter.dart';

import '../../../core/network/api_exception.dart';
import '../../../core/theme/app_theme.dart';
import '../../../core/utils/haptics.dart';
import '../../../core/utils/responsive.dart';
import '../../../core/widgets/animations.dart';
import '../../../core/widgets/app_toast.dart';
import '../../../core/widgets/mobile_header.dart';
import '../../../core/widgets/skeleton.dart';
import '../../auth/domain/staff_session.dart';
import '../../home/data/staff_api.dart';
import '../domain/support_issue.dart';
import 'support_issue_view_screen.dart';

class SupportIssuesScreen extends StatefulWidget {
  const SupportIssuesScreen({
    super.key,
    required this.session,
    this.initialScope = 'open',
  });

  final StaffSession session;
  final String initialScope;

  @override
  State<SupportIssuesScreen> createState() => _SupportIssuesScreenState();
}

class _SupportIssuesScreenState extends State<SupportIssuesScreen> {
  final StaffApi _api = StaffApi();
  late String _scope;
  Future<SupportIssuesData>? _future;

  static const _scopes = <_ScopeOption>[
    _ScopeOption('open', 'Open', PhosphorIconsBold.envelopeOpen),
    _ScopeOption('unassigned', 'Unassigned', PhosphorIconsBold.userMinus),
    _ScopeOption('closed', 'Closed', PhosphorIconsBold.checkCircle),
    _ScopeOption('all', 'All', PhosphorIconsBold.rows),
  ];

  @override
  void initState() {
    super.initState();
    _scope = widget.initialScope;
    _reload();
  }

  void _reload() {
    setState(() {
      _future = _api.fetchSupportIssues(
        baseUrl: widget.session.baseUrl,
        token: widget.session.token,
        scope: _scope,
      );
    });
  }

  Future<void> _openIssue(SupportIssue issue) async {
    Haptics.light();
    await Navigator.of(context).push(
      MaterialPageRoute(
        builder: (_) =>
            SupportIssueViewScreen(session: widget.session, issueId: issue.id),
      ),
    );
    if (mounted) _reload();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppTheme.background,
      body: RefreshIndicator(
        color: AppTheme.primary,
        onRefresh: () async {
          Haptics.light();
          _reload();
          await _future;
        },
        child: FutureBuilder<SupportIssuesData>(
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
                    title: 'Support',
                    subtitle: 'Customer issues and follow-ups',
                    leadingIcon: PhosphorIconsBold.arrowLeft,
                    onLeadingTap: () => Navigator.of(context).maybePop(),
                  ),
                ),
                const SizedBox(height: 14),
                FadeSlide(
                  delay: const Duration(milliseconds: 60),
                  child: _ScopeFilter(
                    scope: _scope,
                    counts: snapshot.data?.counts,
                    onChanged: (value) {
                      Haptics.light();
                      setState(() => _scope = value);
                      _reload();
                    },
                  ),
                ),
                const SizedBox(height: 18),
                if (snapshot.connectionState == ConnectionState.waiting)
                  const _ListSkeleton()
                else if (snapshot.hasError)
                  _ErrorState(
                    message: snapshot.error is ApiException
                        ? (snapshot.error as ApiException).message
                        : snapshot.error.toString(),
                    onRetry: _reload,
                  )
                else if ((snapshot.data?.issues ?? []).isEmpty)
                  const _EmptyState()
                else
                  ...snapshot.data!.issues.asMap().entries.map(
                    (entry) => Padding(
                      padding: const EdgeInsets.only(bottom: 10),
                      child: FadeSlide(
                        delay: Duration(milliseconds: 40 * entry.key),
                        child: _IssueCard(
                          issue: entry.value,
                          onTap: () => _openIssue(entry.value),
                        ),
                      ),
                    ),
                  ),
              ],
            );
          },
        ),
      ),
    );
  }
}

class _ScopeOption {
  const _ScopeOption(this.value, this.label, this.icon);
  final String value;
  final String label;
  final IconData icon;
}

class _ScopeFilter extends StatelessWidget {
  const _ScopeFilter({
    required this.scope,
    required this.onChanged,
    this.counts,
  });

  final String scope;
  final ValueChanged<String> onChanged;
  final SupportIssueCounts? counts;

  int _countFor(String value) {
    switch (value) {
      case 'open':
        return counts?.open ?? 0;
      case 'unassigned':
        return counts?.unassigned ?? 0;
      case 'closed':
        return counts?.closed ?? 0;
      case 'all':
      default:
        return counts?.all ?? 0;
    }
  }

  @override
  Widget build(BuildContext context) {
    return SingleChildScrollView(
      scrollDirection: Axis.horizontal,
      child: Row(
        children: [
          for (final option in _SupportIssuesScreenState._scopes) ...[
            _ScopeChip(
              icon: option.icon,
              label: option.label,
              count: counts == null ? null : _countFor(option.value),
              active: scope == option.value,
              onTap: () => onChanged(option.value),
            ),
            if (option != _SupportIssuesScreenState._scopes.last)
              const SizedBox(width: 8),
          ],
        ],
      ),
    );
  }
}

class _ScopeChip extends StatelessWidget {
  const _ScopeChip({
    required this.icon,
    required this.label,
    required this.active,
    required this.onTap,
    this.count,
  });

  final IconData icon;
  final String label;
  final bool active;
  final VoidCallback onTap;
  final int? count;

  @override
  Widget build(BuildContext context) {
    return PressScale(
      onTap: onTap,
      child: AnimatedContainer(
        duration: const Duration(milliseconds: 220),
        padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 9),
        decoration: BoxDecoration(
          color: active ? AppTheme.primary : AppTheme.surface,
          borderRadius: BorderRadius.circular(12),
          border: Border.all(
            color: active ? AppTheme.primary : AppTheme.border,
          ),
          boxShadow: active
              ? [
                  BoxShadow(
                    color: AppTheme.primaryDark.withValues(alpha: 0.24),
                    blurRadius: 12,
                    offset: const Offset(0, 6),
                  ),
                ]
              : null,
        ),
        child: Row(
          mainAxisSize: MainAxisSize.min,
          children: [
            Icon(
              icon,
              size: 13,
              color: active ? Colors.white : AppTheme.primaryDark,
            ),
            const SizedBox(width: 6),
            Text(
              label,
              style: TextStyle(
                fontSize: 12.5,
                fontWeight: FontWeight.w800,
                color: active ? Colors.white : AppTheme.textPrimary,
              ),
            ),
            if (count != null) ...[
              const SizedBox(width: 6),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 1),
                decoration: BoxDecoration(
                  color: active
                      ? Colors.white.withValues(alpha: 0.22)
                      : AppTheme.surfaceMuted,
                  borderRadius: BorderRadius.circular(999),
                ),
                child: Text(
                  '$count',
                  style: TextStyle(
                    fontWeight: FontWeight.w900,
                    fontSize: 10.5,
                    color: active ? Colors.white : AppTheme.primaryDark,
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

class _IssueCard extends StatelessWidget {
  const _IssueCard({required this.issue, required this.onTap});
  final SupportIssue issue;
  final VoidCallback onTap;

  Color _priorityColor() {
    switch (issue.priority.toLowerCase()) {
      case 'urgent':
      case 'high':
        return AppTheme.danger;
      case 'low':
        return AppTheme.success;
      case 'medium':
      default:
        return AppTheme.warning;
    }
  }

  Color _statusColor() {
    if (issue.isClosed) return AppTheme.success;
    if (issue.isUnassigned) return AppTheme.warning;
    return AppTheme.primaryDark;
  }

  String _statusLabel() {
    if (issue.isClosed) return 'CLOSED';
    if (issue.isUnassigned) return 'UNASSIGNED';
    return issue.status.toUpperCase();
  }

  @override
  Widget build(BuildContext context) {
    final priority = _priorityColor();
    final statusColor = _statusColor();

    return PressScale(
      onTap: onTap,
      child: Container(
        padding: const EdgeInsets.all(14),
        decoration: BoxDecoration(
          color: AppTheme.surface,
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
                  width: 4,
                  height: 30,
                  decoration: BoxDecoration(
                    color: priority,
                    borderRadius: BorderRadius.circular(2),
                  ),
                ),
                const SizedBox(width: 10),
                Expanded(
                  child: Text(
                    issue.ticketNumber.isEmpty
                        ? 'Ticket #${issue.id}'
                        : issue.ticketNumber,
                    style: const TextStyle(
                      color: AppTheme.primaryDark,
                      fontWeight: FontWeight.w900,
                      fontSize: 12,
                      letterSpacing: 0.3,
                    ),
                  ),
                ),
                Container(
                  padding: const EdgeInsets.symmetric(
                    horizontal: 8,
                    vertical: 4,
                  ),
                  decoration: BoxDecoration(
                    color: statusColor.withValues(alpha: 0.10),
                    borderRadius: BorderRadius.circular(999),
                  ),
                  child: Text(
                    _statusLabel(),
                    style: TextStyle(
                      color: statusColor,
                      fontWeight: FontWeight.w900,
                      fontSize: 9.5,
                      letterSpacing: 0.8,
                    ),
                  ),
                ),
              ],
            ),
            const SizedBox(height: 8),
            Padding(
              padding: const EdgeInsets.only(left: 14),
              child: Text(
                issue.title,
                maxLines: 2,
                overflow: TextOverflow.ellipsis,
                style: TextStyle(
                  fontWeight: FontWeight.w900,
                  fontSize: 14,
                  color: AppTheme.textPrimary,
                  height: 1.3,
                ),
              ),
            ),
            const SizedBox(height: 10),
            Padding(
              padding: const EdgeInsets.only(left: 14),
              child: Row(
                children: [
                  Icon(
                    PhosphorIconsBold.user,
                    size: 11,
                    color: AppTheme.textSecondary,
                  ),
                  const SizedBox(width: 5),
                  Expanded(
                    child: Text(
                      issue.customerName.isEmpty ? '—' : issue.customerName,
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                      style: TextStyle(
                        color: AppTheme.textSecondary,
                        fontSize: 11.5,
                        fontWeight: FontWeight.w600,
                      ),
                    ),
                  ),
                ],
              ),
            ),
            const SizedBox(height: 6),
            Padding(
              padding: const EdgeInsets.only(left: 14),
              child: Wrap(
                spacing: 6,
                runSpacing: 6,
                crossAxisAlignment: WrapCrossAlignment.center,
                children: [
                  if (issue.departmentName.isNotEmpty)
                    _MetaPill(
                      icon: PhosphorIconsBold.buildingOffice,
                      label: issue.departmentName,
                    ),
                  _MetaPill(
                    icon: PhosphorIconsBold.flag,
                    label: issue.priority.toUpperCase(),
                    accent: priority,
                  ),
                  if (issue.assignedToMe)
                    const _MetaPill(
                      icon: PhosphorIconsBold.userCircleCheck,
                      label: 'YOU',
                      accent: AppTheme.success,
                    ),
                  if (issue.createdLabel.isNotEmpty)
                    _MetaPill(
                      icon: PhosphorIconsBold.clock,
                      label: issue.createdLabel,
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

class _MetaPill extends StatelessWidget {
  const _MetaPill({required this.icon, required this.label, this.accent});

  final IconData icon;
  final String label;
  final Color? accent;

  @override
  Widget build(BuildContext context) {
    final color = accent ?? AppTheme.textSecondary;
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
      decoration: BoxDecoration(
        color: AppTheme.surfaceMuted,
        borderRadius: BorderRadius.circular(999),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Icon(icon, size: 10, color: color),
          const SizedBox(width: 5),
          Text(
            label,
            style: TextStyle(
              fontWeight: FontWeight.w700,
              fontSize: 10.5,
              color: AppTheme.textPrimary,
            ),
          ),
        ],
      ),
    );
  }
}

class _ListSkeleton extends StatelessWidget {
  const _ListSkeleton();

  @override
  Widget build(BuildContext context) {
    return Column(
      children: [
        for (var i = 0; i < 4; i++)
          Padding(
            padding: const EdgeInsets.only(bottom: 10),
            child: SkeletonCard(
              radius: 18,
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: const [
                  Row(
                    children: [
                      Skeleton(width: 80, height: 12),
                      Spacer(),
                      Skeleton(width: 60, height: 18, radius: 999),
                    ],
                  ),
                  SizedBox(height: 10),
                  Skeleton(height: 14),
                  SizedBox(height: 6),
                  Skeleton(width: 140, height: 10),
                ],
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
              PhosphorIconsBold.tray,
              size: 26,
              color: AppTheme.primaryDark,
            ),
          ),
          const SizedBox(height: 14),
          Text(
            'No tickets found',
            style: TextStyle(
              fontWeight: FontWeight.w900,
              color: AppTheme.textPrimary,
              fontSize: 15,
            ),
          ),
          const SizedBox(height: 6),
          Text(
            'No support tickets in this scope.',
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

class _ErrorState extends StatelessWidget {
  const _ErrorState({required this.message, required this.onRetry});
  final String message;
  final VoidCallback onRetry;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(18),
      decoration: BoxDecoration(
        color: AppTheme.surface,
        borderRadius: BorderRadius.circular(18),
        border: Border.all(color: AppTheme.border),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            'Unable to load tickets',
            style: TextStyle(
              fontWeight: FontWeight.w900,
              color: AppTheme.textPrimary,
            ),
          ),
          const SizedBox(height: 6),
          Text(
            message,
            style: TextStyle(
              color: AppTheme.textSecondary,
              fontSize: 12.5,
            ),
          ),
          const SizedBox(height: 12),
          FilledButton.icon(
            onPressed: () {
              Haptics.medium();
              onRetry();
              AppToast.info(context, 'Reloading…');
            },
            icon: const Icon(PhosphorIconsBold.arrowsClockwise, size: 16),
            label: const Text('Try again'),
          ),
        ],
      ),
    );
  }
}
