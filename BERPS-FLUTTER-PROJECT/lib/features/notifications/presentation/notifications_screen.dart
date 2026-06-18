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
import '../domain/staff_notification.dart';

class NotificationsScreen extends StatefulWidget {
  const NotificationsScreen({super.key, required this.session});

  final StaffSession session;

  @override
  State<NotificationsScreen> createState() => _NotificationsScreenState();
}

class _NotificationsScreenState extends State<NotificationsScreen> {
  final StaffApi _api = StaffApi();
  Future<StaffNotificationsData>? _future;
  bool _markedSeen = false;

  @override
  void initState() {
    super.initState();
    _load();
  }

  void _load() {
    setState(() {
      _future = _api.fetchNotifications(
        baseUrl: widget.session.baseUrl,
        token: widget.session.token,
        limit: 40,
      );
    });
    _markSeenSilent();
  }

  Future<void> _markSeenSilent() async {
    if (_markedSeen) return;
    _markedSeen = true;
    try {
      await _api.markNotificationsSeen(
        baseUrl: widget.session.baseUrl,
        token: widget.session.token,
      );
    } catch (_) {
      // Soft fail — the badge will catch up on next refresh.
    }
  }

  void _openItem(StaffNotification item) {
    Haptics.light();
    if (item.isSupport && item.issueId > 0) {
      Navigator.of(context).push(
        MaterialPageRoute(
          builder: (_) => SupportIssueViewScreen(
            session: widget.session,
            issueId: item.issueId,
          ),
        ),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppTheme.background,
      body: RefreshIndicator(
        color: AppTheme.primary,
        onRefresh: () async {
          Haptics.light();
          _markedSeen = false;
          _load();
          await _future;
        },
        child: FutureBuilder<StaffNotificationsData>(
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
                    title: 'Notifications',
                    subtitle:
                        snapshot.hasData && snapshot.data!.notifications.isEmpty
                        ? 'You are all caught up'
                        : 'Recent activity for your account',
                    leadingIcon: PhosphorIconsBold.arrowLeft,
                    onLeadingTap: () => Navigator.of(context).maybePop(),
                  ),
                ),
                const SizedBox(height: 14),
                if (snapshot.connectionState == ConnectionState.waiting)
                  const _NotificationsSkeleton()
                else if (snapshot.hasError)
                  _ErrorCard(
                    message: snapshot.error is ApiException
                        ? (snapshot.error as ApiException).message
                        : snapshot.error.toString(),
                    onRetry: _load,
                  )
                else if (snapshot.data == null ||
                    snapshot.data!.notifications.isEmpty)
                  const _EmptyState()
                else
                  ...snapshot.data!.notifications.asMap().entries.map(
                    (entry) => Padding(
                      padding: const EdgeInsets.only(bottom: 10),
                      child: FadeSlide(
                        delay: Duration(milliseconds: 50 * entry.key),
                        child: _NotificationCard(
                          item: entry.value,
                          onTap: () => _openItem(entry.value),
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

class _NotificationCard extends StatelessWidget {
  const _NotificationCard({required this.item, required this.onTap});

  final StaffNotification item;
  final VoidCallback onTap;

  IconData get _icon {
    if (item.isSupport) return PhosphorIconsBold.lifebuoy;
    return PhosphorIconsBold.checkCircle;
  }

  Color get _accent {
    if (item.isSupport) return AppTheme.accent;
    return AppTheme.primary;
  }

  @override
  Widget build(BuildContext context) {
    final unread = !item.isSeen;
    return InkWell(
      borderRadius: BorderRadius.circular(16),
      onTap: onTap,
      child: Container(
        padding: const EdgeInsets.all(14),
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(16),
          border: Border.all(
            color: unread ? _accent.withValues(alpha: 0.32) : AppTheme.border,
          ),
          boxShadow: AppTheme.shadowSoft,
        ),
        child: Row(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Container(
              width: 38,
              height: 38,
              decoration: BoxDecoration(
                color: _accent.withValues(alpha: 0.12),
                borderRadius: BorderRadius.circular(11),
              ),
              child: Icon(_icon, size: 18, color: _accent),
            ),
            const SizedBox(width: 12),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    children: [
                      Expanded(
                        child: Text(
                          item.title.isEmpty ? 'Notification' : item.title,
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                          style: const TextStyle(
                            fontWeight: FontWeight.w800,
                            fontSize: 13.5,
                            color: AppTheme.textPrimary,
                            height: 1.3,
                          ),
                        ),
                      ),
                      if (unread)
                        Container(
                          width: 8,
                          height: 8,
                          margin: const EdgeInsets.only(left: 6, top: 4),
                          decoration: BoxDecoration(
                            color: AppTheme.accent,
                            shape: BoxShape.circle,
                          ),
                        ),
                    ],
                  ),
                  if (item.message.isNotEmpty) ...[
                    const SizedBox(height: 4),
                    Text(
                      item.message,
                      maxLines: 2,
                      overflow: TextOverflow.ellipsis,
                      style: const TextStyle(
                        color: AppTheme.textSecondary,
                        fontSize: 12.5,
                        height: 1.4,
                      ),
                    ),
                  ],
                  const SizedBox(height: 8),
                  Row(
                    children: [
                      Icon(
                        PhosphorIconsBold.user,
                        size: 11,
                        color: AppTheme.textMuted,
                      ),
                      const SizedBox(width: 4),
                      Flexible(
                        child: Text(
                          item.actorName,
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                          style: const TextStyle(
                            color: AppTheme.textMuted,
                            fontSize: 11,
                            fontWeight: FontWeight.w700,
                          ),
                        ),
                      ),
                      if (item.createdLabel.isNotEmpty) ...[
                        const SizedBox(width: 8),
                        Container(
                          width: 3,
                          height: 3,
                          decoration: BoxDecoration(
                            color: AppTheme.textMuted,
                            shape: BoxShape.circle,
                          ),
                        ),
                        const SizedBox(width: 8),
                        Flexible(
                          child: Text(
                            item.createdLabel,
                            maxLines: 1,
                            overflow: TextOverflow.ellipsis,
                            style: const TextStyle(
                              color: AppTheme.textMuted,
                              fontSize: 11,
                              fontWeight: FontWeight.w600,
                            ),
                          ),
                        ),
                      ],
                    ],
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

class _EmptyState extends StatelessWidget {
  const _EmptyState();

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
              PhosphorIconsBold.bellSlash,
              size: 26,
              color: AppTheme.primaryDark,
            ),
          ),
          const SizedBox(height: 14),
          const Text(
            'No notifications yet',
            style: TextStyle(
              fontWeight: FontWeight.w900,
              color: AppTheme.textPrimary,
              fontSize: 15,
            ),
          ),
          const SizedBox(height: 6),
          const Text(
            'You will see task updates and support replies here.',
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

class _NotificationsSkeleton extends StatelessWidget {
  const _NotificationsSkeleton();

  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        for (var i = 0; i < 4; i++)
          Padding(
            padding: const EdgeInsets.only(bottom: 10),
            child: SkeletonCard(
              radius: 16,
              child: Row(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: const [
                  Skeleton(width: 38, height: 38, radius: 11),
                  SizedBox(width: 12),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Skeleton(width: 180, height: 12),
                        SizedBox(height: 8),
                        Skeleton(height: 10),
                        SizedBox(height: 6),
                        Skeleton(width: 120, height: 10),
                      ],
                    ),
                  ),
                ],
              ),
            ),
          ),
      ],
    );
  }
}

class _ErrorCard extends StatelessWidget {
  const _ErrorCard({required this.message, required this.onRetry});

  final String message;
  final VoidCallback onRetry;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(18),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(18),
        border: Border.all(color: AppTheme.border),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Text(
            'Unable to load notifications',
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
            icon: const Icon(PhosphorIconsBold.arrowsClockwise, size: 16),
            label: const Text('Try again'),
          ),
        ],
      ),
    );
  }
}
