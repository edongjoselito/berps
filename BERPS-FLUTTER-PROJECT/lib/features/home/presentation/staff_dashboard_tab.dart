import 'dart:math' as math;

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
import '../../../core/widgets/staff_avatar.dart';
import '../../auth/domain/staff_session.dart';
import '../../calendar/domain/calendar_event.dart';
import '../../notifications/presentation/notification_bell.dart';
import '../../notes/data/notes_api.dart';
import '../../notes/domain/note.dart';
import '../data/staff_api.dart';
import '../domain/staff_dashboard.dart';
import '../domain/staff_ranking.dart';

class StaffDashboardTab extends StatefulWidget {
  const StaffDashboardTab({
    super.key,
    required this.session,
    required this.onMenu,
    required this.onOpenAttendance,
    required this.onOpenTasks,
    required this.onOpenMyDtr,
    required this.onOpenForwardedTasks,
    required this.onOpenUnassignedTickets,
    required this.onOpenSupportTickets,
    required this.onOpenReminders,
    required this.onOpenCalendar,
    required this.onOpenNotes,
  });

  final StaffSession session;
  final VoidCallback onMenu;
  final VoidCallback onOpenAttendance;
  final VoidCallback onOpenTasks;
  final VoidCallback onOpenMyDtr;
  final VoidCallback onOpenForwardedTasks;
  final VoidCallback onOpenUnassignedTickets;
  final VoidCallback onOpenSupportTickets;
  final VoidCallback onOpenReminders;
  final VoidCallback onOpenCalendar;
  final VoidCallback onOpenNotes;

  @override
  State<StaffDashboardTab> createState() => _StaffDashboardTabState();
}

class _StaffDashboardTabState extends State<StaffDashboardTab> {
  final StaffApi _api = StaffApi();
  final NotesApi _notesApi = NotesApi();
  late Future<StaffDashboard> _future;
  late Future<StaffRanking> _rankingFuture;
  late Future<List<CalendarEvent>> _eventsFuture;
  late Future<List<Note>> _notesFuture;

  @override
  void initState() {
    super.initState();
    _future = _loadDashboard();
    _rankingFuture = _loadRanking();
    _eventsFuture = _loadEvents();
    _notesFuture = _loadNotes();
  }

  Future<StaffDashboard> _loadDashboard() {
    return _api.fetchDashboard(
      baseUrl: widget.session.baseUrl,
      token: widget.session.token,
    );
  }

  Future<StaffRanking> _loadRanking() {
    return _api.fetchRanking(
      baseUrl: widget.session.baseUrl,
      token: widget.session.token,
    );
  }

  Future<List<CalendarEvent>> _loadEvents() {
    return _api.fetchCalendarEvents(
      baseUrl: widget.session.baseUrl,
      token: widget.session.token,
    );
  }

  Future<List<Note>> _loadNotes() {
    return _notesApi.fetchNotes(
      baseUrl: widget.session.baseUrl,
      token: widget.session.token,
    );
  }

  void _reload() {
    Haptics.light();
    setState(() {
      _future = _loadDashboard();
      _rankingFuture = _loadRanking();
      _eventsFuture = _loadEvents();
      _notesFuture = _loadNotes();
    });
  }

  String _greeting() {
    final hour = DateTime.now().hour;
    if (hour < 12) return 'Good morning';
    if (hour < 18) return 'Good afternoon';
    return 'Good evening';
  }

  IconData _greetingIcon() {
    final hour = DateTime.now().hour;
    if (hour < 12) return PhosphorIconsFill.sunHorizon;
    if (hour < 18) return PhosphorIconsFill.sun;
    return PhosphorIconsFill.moonStars;
  }

  @override
  Widget build(BuildContext context) {
    final gutter = context.gutter;
    return RefreshIndicator(
      color: AppTheme.primary,
      onRefresh: () async {
        Haptics.light();
        setState(() {
          _future = _loadDashboard();
          _rankingFuture = _loadRanking();
          _eventsFuture = _loadEvents();
          _notesFuture = _loadNotes();
        });
        await _future;
        await _eventsFuture;
        await _notesFuture;
      },
      child: FutureBuilder<StaffDashboard>(
        future: _future,
        builder: (context, snapshot) {
          return ListView(
            physics: const AlwaysScrollableScrollPhysics(),
            padding: EdgeInsets.fromLTRB(gutter, 12, gutter, 28),
            children: [
              SafeArea(
                bottom: false,
                child: MobileHeader(
                  title: 'Dashboard',
                  leadingIcon: PhosphorIconsBold.list,
                  onLeadingTap: () {
                    Haptics.light();
                    widget.onMenu();
                  },
                  trailing: NotificationBell(session: widget.session),
                ),
              ),
              const SizedBox(height: 14),
              FadeSlide(
                delay: const Duration(milliseconds: 60),
                child: _GreetingCard(
                  greeting: _greeting(),
                  greetingIcon: _greetingIcon(),
                  session: widget.session,
                ),
              ),
              const SizedBox(height: 18),
              if (snapshot.connectionState == ConnectionState.waiting)
                const _DashboardSkeleton()
              else if (snapshot.hasError)
                _ErrorState(
                  message: snapshot.error is ApiException
                      ? (snapshot.error as ApiException).message
                      : snapshot.error.toString(),
                  onRetry: _reload,
                )
              else if (!snapshot.hasData)
                _ErrorState(
                  message: 'Dashboard data is unavailable right now.',
                  onRetry: _reload,
                )
              else
                _DashboardContent(
                  data: snapshot.data!,
                  session: widget.session,
                  rankingFuture: _rankingFuture,
                  eventsFuture: _eventsFuture,
                  notesFuture: _notesFuture,
                  onOpenTasks: widget.onOpenTasks,
                  onOpenMyDtr: widget.onOpenMyDtr,
                  onOpenForwardedTasks: widget.onOpenForwardedTasks,
                  onOpenUnassignedTickets: widget.onOpenUnassignedTickets,
                  onOpenSupportTickets: widget.onOpenSupportTickets,
                  onOpenReminders: widget.onOpenReminders,
                  onOpenCalendar: widget.onOpenCalendar,
                  onOpenNotes: widget.onOpenNotes,
                ),
            ],
          );
        },
      ),
    );
  }
}

class _GreetingCard extends StatelessWidget {
  const _GreetingCard({
    required this.greeting,
    required this.greetingIcon,
    required this.session,
  });

  final String greeting;
  final IconData greetingIcon;
  final StaffSession session;

  @override
  Widget build(BuildContext context) {
    final today = _formatDate(DateTime.now());
    final name = session.formalName;
    final position = session.position;

    return Container(
      padding: const EdgeInsets.fromLTRB(18, 18, 18, 18),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(20),
        border: Border.all(color: AppTheme.border),
        boxShadow: AppTheme.shadowSoft,
      ),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.center,
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
                    Icon(greetingIcon, size: 13, color: AppTheme.accent),
                    const SizedBox(width: 6),
                    Text(
                      greeting,
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
                  name,
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
                    if (position.isNotEmpty) ...[
                      Flexible(
                        child: Text(
                          position,
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
                    ],
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
  static const _days = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];

  String _formatDate(DateTime now) {
    return '${_days[now.weekday - 1]} · ${_months[now.month - 1]} ${now.day}';
  }
}

// ── Content ────────────────────────────────────────────────────────────────

class _DashboardContent extends StatelessWidget {
  const _DashboardContent({
    required this.data,
    required this.session,
    required this.rankingFuture,
    required this.eventsFuture,
    required this.notesFuture,
    required this.onOpenTasks,
    required this.onOpenMyDtr,
    required this.onOpenForwardedTasks,
    required this.onOpenUnassignedTickets,
    required this.onOpenSupportTickets,
    required this.onOpenReminders,
    required this.onOpenCalendar,
    required this.onOpenNotes,
  });

  final StaffDashboard data;
  final StaffSession session;
  final Future<StaffRanking> rankingFuture;
  final Future<List<CalendarEvent>> eventsFuture;
  final Future<List<Note>> notesFuture;
  final VoidCallback onOpenTasks;
  final VoidCallback onOpenMyDtr;
  final VoidCallback onOpenForwardedTasks;
  final VoidCallback onOpenUnassignedTickets;
  final VoidCallback onOpenSupportTickets;
  final VoidCallback onOpenReminders;
  final VoidCallback onOpenCalendar;
  final VoidCallback onOpenNotes;

  @override
  Widget build(BuildContext context) {
    // Quick actions are gated by the workspace's enabled features so restricted
    // staff don't see attendance/support shortcuts they can't use.
    final quickActions = <_QuickAction>[
      if (session.hasMyDtr)
        _QuickAction(
          label: 'My DTR',
          sublabel: 'This month',
          icon: PhosphorIconsBold.calendarCheck,
          accent: AppTheme.primaryDark,
          onTap: onOpenMyDtr,
        ),
      if (session.hasReminders)
        _QuickAction(
          label: 'Reminders',
          sublabel: data.remindersDueTodayCount == 0
              ? 'Stay on track'
              : '${data.remindersDueTodayCount} due today',
          icon: PhosphorIconsBold.bellRinging,
          accent: AppTheme.accent,
          badge: data.remindersDueTodayCount > 0
              ? '${data.remindersDueTodayCount}'
              : null,
          onTap: onOpenReminders,
        ),
      if (session.hasNotes)
        _QuickAction(
          label: 'Notes',
          sublabel: 'Your notes',
          icon: PhosphorIconsBold.notebook,
          accent: AppTheme.primaryDark,
          onTap: onOpenNotes,
        ),
      if (session.hasForwardedTasks)
        _QuickAction(
          label: 'Forwarded',
          sublabel: data.forwardedTaskCount == 0
              ? 'Inbox empty'
              : '${data.forwardedTaskCount} waiting',
          icon: PhosphorIconsBold.arrowsLeftRight,
          accent: AppTheme.warning,
          badge: data.forwardedTaskCount > 0
              ? '${data.forwardedTaskCount}'
              : null,
          onTap: onOpenForwardedTasks,
        ),
      if (session.hasSupport)
        _QuickAction(
          label: 'Unassigned',
          sublabel: data.unassignedSupportCount == 0
              ? 'Support queue'
              : '${data.unassignedSupportCount} waiting',
          icon: PhosphorIconsBold.userMinus,
          accent: AppTheme.danger,
          badge: data.unassignedSupportCount > 0
              ? '${data.unassignedSupportCount}'
              : null,
          onTap: onOpenUnassignedTickets,
        ),
      if (session.hasSupport)
        _QuickAction(
          label: 'Tickets',
          sublabel: 'All support',
          icon: PhosphorIconsBold.lifebuoy,
          accent: AppTheme.primary,
          onTap: onOpenSupportTickets,
        ),
    ];

    final metricCards = <_MetricCardData>[
      _MetricCardData(
        label: 'Done Today',
        value: '${data.accomplishmentsToday}',
        icon: PhosphorIconsBold.checkCircle,
        accent: AppTheme.success,
      ),
      if (session.hasAttendance)
        _MetricCardData(
          label: 'Hours Today',
          value: data.todayHoursLabel,
          icon: PhosphorIconsBold.clockCounterClockwise,
          accent: AppTheme.primary,
        ),
      _MetricCardData(
        label: 'Due Today',
        value: '${data.tasksDueToday}',
        icon: PhosphorIconsBold.calendarDot,
        accent: AppTheme.accent,
      ),
      _MetricCardData(
        label: 'Overdue',
        value: '${data.tasksOverdue}',
        icon: PhosphorIconsBold.warningCircle,
        accent: AppTheme.danger,
      ),
    ];

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        if (quickActions.isNotEmpty) ...[
          FadeSlide(
            delay: const Duration(milliseconds: 90),
            child: _QuickActionsStrip(actions: quickActions),
          ),
          const SizedBox(height: 20),
        ],
        if (session.hasAttendance) ...[
          FadeSlide(
            delay: const Duration(milliseconds: 105),
            child: _DtrPreviewCard(
              statusLabel: data.attendanceStatusLabel,
              hoursLabel: data.todayHoursLabel,
              notice: data.attendanceNotice,
              onViewDtr: onOpenMyDtr,
            ),
          ),
          const SizedBox(height: 20),
        ],
        FadeSlide(
          delay: const Duration(milliseconds: 120),
          child: const _SectionHeader(title: 'Snapshot'),
        ),
        const SizedBox(height: 12),
        FadeSlide(
          delay: const Duration(milliseconds: 180),
          child: _MetricGrid(cards: metricCards),
        ),
        if (data.remindersDueTodayCount > 0) ...[
          const SizedBox(height: 12),
          FadeSlide(
            delay: const Duration(milliseconds: 240),
            child: _RemindersBanner(
              count: data.remindersDueTodayCount,
              onTap: session.hasReminders ? onOpenReminders : null,
            ),
          ),
        ],
        if (session.hasCalendar || session.hasNotes) ...[
          const SizedBox(height: 20),
          FadeSlide(
            delay: const Duration(milliseconds: 260),
            child: _SectionHeader(
              title: 'Events & Notes',
              action: session.hasCalendar
                  ? _ViewAllLink(onTap: onOpenCalendar)
                  : null,
            ),
          ),
          const SizedBox(height: 12),
          FadeSlide(
            delay: const Duration(milliseconds: 280),
            child: _EventsNotesSnapshot(
              eventsFuture: eventsFuture,
              notesFuture: notesFuture,
              onOpenCalendar: onOpenCalendar,
              onOpenNotes: onOpenNotes,
              session: session,
            ),
          ),
        ],
        if (session.hasRanking) ...[
          const SizedBox(height: 20),
          FadeSlide(
            delay: const Duration(milliseconds: 300),
            child: FutureBuilder<StaffRanking>(
              future: rankingFuture,
              builder: (context, snapshot) {
                if (snapshot.connectionState == ConnectionState.waiting) {
                  return const _RankingSkeleton();
                }
                if (snapshot.hasError || !snapshot.hasData) {
                  return _RankingFallback(
                    message: snapshot.error is ApiException
                        ? (snapshot.error as ApiException).message
                        : 'Ranking unavailable right now.',
                  );
                }
                return _LeaderboardCard(ranking: snapshot.data!);
              },
            ),
          ),
        ],
        if (session.hasTasks) ...[
          const SizedBox(height: 20),
          FadeSlide(
            delay: const Duration(milliseconds: 340),
            child: _SectionHeader(
              title: 'In Progress',
              action: _ViewAllLink(onTap: onOpenTasks),
            ),
          ),
          const SizedBox(height: 12),
          FadeSlide(
            delay: const Duration(milliseconds: 360),
            child: _TaskPanel(
              tasks: data.ongoingTasks,
              onOpenTasks: onOpenTasks,
            ),
          ),
        ],
      ],
    );
  }
}

class _QuickActionsStrip extends StatelessWidget {
  const _QuickActionsStrip({required this.actions});

  final List<_QuickAction> actions;

  @override
  Widget build(BuildContext context) {
    final items = actions;

    return LayoutBuilder(
      builder: (context, constraints) {
        final perRow = constraints.maxWidth >= 520 ? 4 : 2;
        final spacing = 12.0;
        final tileWidth = (constraints.maxWidth - spacing * (perRow - 1)) / perRow;
        return Wrap(
          spacing: spacing,
          runSpacing: 12,
          children: [
            for (final item in items)
              SizedBox(
                width: tileWidth,
                child: _QuickActionTile(action: item),
              ),
          ],
        );
      },
    );
  }
}

class _QuickAction {
  const _QuickAction({
    required this.label,
    required this.sublabel,
    required this.icon,
    required this.accent,
    required this.onTap,
    this.badge,
  });
  final String label;
  final String sublabel;
  final IconData icon;
  final Color accent;
  final VoidCallback onTap;
  final String? badge;
}

class _QuickActionTile extends StatelessWidget {
  const _QuickActionTile({required this.action});
  final _QuickAction action;

  @override
  Widget build(BuildContext context) {
    return PressScale(
      onTap: () {
        Haptics.light();
        action.onTap();
      },
      child: Container(
        padding: const EdgeInsets.symmetric(vertical: 16, horizontal: 10),
        decoration: BoxDecoration(
          color: AppTheme.surface,
          borderRadius: BorderRadius.circular(16),
          boxShadow: AppTheme.shadowSoft,
        ),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Stack(
              clipBehavior: Clip.none,
              children: [
                Container(
                  width: 36,
                  height: 36,
                  decoration: BoxDecoration(
                    color: action.accent.withValues(alpha: 0.12),
                    borderRadius: BorderRadius.circular(11),
                  ),
                  child: Icon(action.icon, size: 17, color: action.accent),
                ),
                if (action.badge != null)
                  Positioned(
                    right: -6,
                    top: -4,
                    child: Container(
                      padding: const EdgeInsets.symmetric(
                        horizontal: 5,
                        vertical: 1,
                      ),
                      decoration: BoxDecoration(
                        color: AppTheme.danger,
                        borderRadius: BorderRadius.circular(999),
                        border: Border.all(
                          color: AppTheme.surfaceMuted,
                          width: 2,
                        ),
                      ),
                      child: Text(
                        action.badge!,
                        style: const TextStyle(
                          color: Colors.white,
                          fontWeight: FontWeight.w900,
                          fontSize: 9,
                          height: 1.1,
                        ),
                      ),
                    ),
                  ),
              ],
            ),
            const SizedBox(height: 8),
            Text(
              action.label,
              maxLines: 2,
              overflow: TextOverflow.ellipsis,
              textAlign: TextAlign.center,
              style: const TextStyle(
                fontWeight: FontWeight.w800,
                color: AppTheme.textPrimary,
                fontSize: 12.5,
              ),
            ),
            const SizedBox(height: 1),
            Text(
              action.sublabel,
              maxLines: 2,
              overflow: TextOverflow.ellipsis,
              textAlign: TextAlign.center,
              style: const TextStyle(
                color: AppTheme.textSecondary,
                fontSize: 10.5,
                fontWeight: FontWeight.w600,
              ),
            ),
          ],
        ),
      ),
    );
  }
}

class _DtrPreviewCard extends StatelessWidget {
  const _DtrPreviewCard({
    required this.statusLabel,
    required this.hoursLabel,
    required this.notice,
    required this.onViewDtr,
  });

  final String statusLabel;
  final String hoursLabel;
  final String notice;
  final VoidCallback onViewDtr;

  Color get _statusColor {
    final label = statusLabel.toLowerCase();
    if (label.contains('present')) {
      return AppTheme.success;
    }
    if (label.contains('absent')) {
      return AppTheme.danger;
    }
    if (label.contains('pending') || label.contains('open')) {
      return AppTheme.warning;
    }
    return AppTheme.textMuted;
  }

  IconData get _statusIcon {
    final label = statusLabel.toLowerCase();
    if (label.contains('present')) {
      return PhosphorIconsBold.checkCircle;
    }
    if (label.contains('absent')) {
      return PhosphorIconsBold.xCircle;
    }
    if (label.contains('pending') || label.contains('open')) {
      return PhosphorIconsBold.clock;
    }
    return PhosphorIconsBold.clock;
  }

  @override
  Widget build(BuildContext context) {
    final today = _formatDate(DateTime.now());
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        gradient: const LinearGradient(
          colors: [Color(0xFF1E3A5F), Color(0xFF2D5A8A)],
        ),
        borderRadius: BorderRadius.circular(18),
        boxShadow: [
          BoxShadow(
            color: const Color(0xFF1E3A5F).withValues(alpha: 0.20),
            blurRadius: 16,
            offset: const Offset(0, 6),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Container(
                padding: const EdgeInsets.all(8),
                decoration: BoxDecoration(
                  color: Colors.white.withValues(alpha: 0.12),
                  borderRadius: BorderRadius.circular(10),
                ),
                child: const Icon(
                  PhosphorIconsBold.clock,
                  color: Colors.white,
                  size: 18,
                ),
              ),
              const SizedBox(width: 10),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const Text(
                      'Today\'s Attendance',
                      style: TextStyle(
                        fontSize: 12,
                        fontWeight: FontWeight.w700,
                        color: Colors.white70,
                      ),
                    ),
                    const SizedBox(height: 1),
                    Text(
                      today,
                      style: const TextStyle(
                        fontSize: 13,
                        fontWeight: FontWeight.w900,
                        color: Colors.white,
                      ),
                    ),
                  ],
                ),
              ),
              Material(
                color: Colors.white.withValues(alpha: 0.12),
                borderRadius: BorderRadius.circular(10),
                child: InkWell(
                  onTap: () {
                    Haptics.light();
                    onViewDtr();
                  },
                  borderRadius: BorderRadius.circular(10),
                  child: const Padding(
                    padding: EdgeInsets.symmetric(horizontal: 10, vertical: 8),
                    child: Row(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        Text(
                          'View DTR',
                          style: TextStyle(
                            fontSize: 11,
                            fontWeight: FontWeight.w800,
                            color: Colors.white,
                          ),
                        ),
                        SizedBox(width: 4),
                        Icon(
                          PhosphorIconsBold.caretRight,
                          size: 12,
                          color: Colors.white,
                        ),
                      ],
                    ),
                  ),
                ),
              ),
            ],
          ),
          const SizedBox(height: 14),
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
            decoration: BoxDecoration(
              color: Colors.white.withValues(alpha: 0.10),
              borderRadius: BorderRadius.circular(14),
            ),
            child: Row(
              children: [
                Icon(_statusIcon, size: 22, color: _statusColor),
                const SizedBox(width: 10),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        statusLabel.isNotEmpty ? statusLabel : 'No status',
                        style: const TextStyle(
                          fontSize: 15,
                          fontWeight: FontWeight.w900,
                          color: Colors.white,
                        ),
                      ),
                      if (notice.isNotEmpty)
                        Text(
                          notice,
                          style: TextStyle(
                            fontSize: 11,
                            fontWeight: FontWeight.w600,
                            color: Colors.white.withValues(alpha: 0.70),
                          ),
                        ),
                    ],
                  ),
                ),
                Container(
                  padding: const EdgeInsets.symmetric(
                    horizontal: 10,
                    vertical: 5,
                  ),
                  decoration: BoxDecoration(
                    color: Colors.white.withValues(alpha: 0.12),
                    borderRadius: BorderRadius.circular(10),
                  ),
                  child: Text(
                    hoursLabel,
                    style: const TextStyle(
                      fontSize: 13,
                      fontWeight: FontWeight.w900,
                      color: Colors.white,
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
  static const _days = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];

  String _formatDate(DateTime now) {
    return '${_days[now.weekday - 1]} · ${_months[now.month - 1]} ${now.day}';
  }
}

class _SectionHeader extends StatelessWidget {
  const _SectionHeader({required this.title, this.action});

  final String title;
  final Widget? action;

  @override
  Widget build(BuildContext context) {
    return Row(
      crossAxisAlignment: CrossAxisAlignment.center,
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

class _MetricCardData {
  const _MetricCardData({
    required this.label,
    required this.value,
    required this.icon,
    required this.accent,
  });
  final String label;
  final String value;
  final IconData icon;
  final Color accent;
}

class _MetricGrid extends StatelessWidget {
  const _MetricGrid({required this.cards});
  final List<_MetricCardData> cards;

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
              SizedBox(
                width: tileWidth,
                child: _MetricCard(data: card),
              ),
          ],
        );
      },
    );
  }
}

class _MetricCard extends StatelessWidget {
  const _MetricCard({required this.data});
  final _MetricCardData data;

  @override
  Widget build(BuildContext context) {
    return Container(
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
              fontSize: context.isSmallPhone ? 22 : 24,
              fontWeight: FontWeight.w900,
              color: AppTheme.textPrimary,
              letterSpacing: -0.5,
            ),
          ),
          const SizedBox(height: 2),
          Text(
            data.label,
            style: const TextStyle(
              color: AppTheme.textSecondary,
              fontWeight: FontWeight.w700,
              fontSize: 12,
            ),
          ),
        ],
      ),
    );
  }
}

class _RemindersBanner extends StatelessWidget {
  const _RemindersBanner({required this.count, this.onTap});
  final int count;
  final VoidCallback? onTap;

  @override
  Widget build(BuildContext context) {
    final banner = Container(
      padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
      decoration: BoxDecoration(
        color: AppTheme.accentSoft,
        borderRadius: BorderRadius.circular(14),
        border: Border.all(color: AppTheme.accent.withValues(alpha: 0.28)),
      ),
      child: Row(
        children: [
          Container(
            width: 30,
            height: 30,
            decoration: BoxDecoration(
              color: Colors.white,
              borderRadius: BorderRadius.circular(9),
            ),
            child: const Icon(
              PhosphorIconsBold.bellRinging,
              color: AppTheme.accent,
              size: 14,
            ),
          ),
          const SizedBox(width: 12),
          Expanded(
            child: Text(
              '$count reminder${count == 1 ? '' : 's'} due today.',
              style: const TextStyle(
                color: AppTheme.textPrimary,
                fontWeight: FontWeight.w700,
                fontSize: 12.5,
              ),
            ),
          ),
          if (onTap != null)
            const Icon(
              PhosphorIconsBold.caretRight,
              color: AppTheme.accent,
              size: 14,
            ),
        ],
      ),
    );

    if (onTap == null) return banner;
    return PressScale(onTap: onTap!, child: banner);
  }
}

// ── Leaderboard (Accomplishment Summary, redesigned) ───────────────────────

class _LeaderboardCard extends StatelessWidget {
  const _LeaderboardCard({required this.ranking});
  final StaffRanking ranking;

  @override
  Widget build(BuildContext context) {
    final entries = ranking.entries;
    final hasData = entries.isNotEmpty;

    return Container(
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(20),
        border: Border.all(color: AppTheme.border),
        boxShadow: AppTheme.shadowSoft,
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Header band — sits on top with a gentle gold tint
          Container(
            padding: const EdgeInsets.fromLTRB(16, 16, 16, 14),
            decoration: BoxDecoration(
              gradient: LinearGradient(
                colors: [
                  const Color(0xFFFFFBEC),
                  Colors.white.withValues(alpha: 0.6),
                ],
                begin: Alignment.topLeft,
                end: Alignment.bottomRight,
              ),
              borderRadius: const BorderRadius.vertical(
                top: Radius.circular(20),
              ),
            ),
            child: Row(
              children: [
                Container(
                  width: 38,
                  height: 38,
                  decoration: BoxDecoration(
                    gradient: const LinearGradient(
                      colors: [Color(0xFFF59E0B), Color(0xFFFBBF24)],
                      begin: Alignment.topLeft,
                      end: Alignment.bottomRight,
                    ),
                    borderRadius: BorderRadius.circular(12),
                    boxShadow: [
                      BoxShadow(
                        color: const Color(0xFFF59E0B).withValues(alpha: 0.35),
                        blurRadius: 14,
                        offset: const Offset(0, 6),
                      ),
                    ],
                  ),
                  child: const Icon(
                    PhosphorIconsBold.trophy,
                    color: Colors.white,
                    size: 19,
                  ),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      const Text(
                        'Accomplishment Summary',
                        style: TextStyle(
                          fontSize: 14.5,
                          fontWeight: FontWeight.w900,
                          color: AppTheme.textPrimary,
                          letterSpacing: -0.2,
                        ),
                      ),
                      const SizedBox(height: 2),
                      Text(
                        '${ranking.periodLabel} · ${ranking.totalPoints} pts total',
                        style: const TextStyle(
                          color: AppTheme.textSecondary,
                          fontWeight: FontWeight.w600,
                          fontSize: 11.5,
                        ),
                      ),
                    ],
                  ),
                ),
                if (ranking.currentRank != null)
                  Container(
                    padding: const EdgeInsets.symmetric(
                      horizontal: 10,
                      vertical: 5,
                    ),
                    decoration: BoxDecoration(
                      color: AppTheme.primaryDark,
                      borderRadius: BorderRadius.circular(999),
                      boxShadow: [
                        BoxShadow(
                          color: AppTheme.primaryDark.withValues(alpha: 0.30),
                          blurRadius: 8,
                          offset: const Offset(0, 4),
                        ),
                      ],
                    ),
                    child: Row(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        const Icon(
                          PhosphorIconsBold.user,
                          size: 10,
                          color: Colors.white,
                        ),
                        const SizedBox(width: 5),
                        Text(
                          '#${ranking.currentRank} You',
                          style: const TextStyle(
                            color: Colors.white,
                            fontWeight: FontWeight.w900,
                            fontSize: 10.5,
                            letterSpacing: 0.2,
                          ),
                        ),
                      ],
                    ),
                  ),
              ],
            ),
          ),
          const Divider(height: 1, color: AppTheme.border),
          if (!hasData)
            const Padding(
              padding: EdgeInsets.symmetric(horizontal: 16, vertical: 18),
              child: _RankingEmpty(),
            )
          else
            Padding(
              padding: const EdgeInsets.fromLTRB(8, 6, 8, 8),
              child: Column(
                children: [
                  for (var i = 0; i < math.min(entries.length, 5); i++)
                    _LeaderboardRow(entry: entries[i]),
                ],
              ),
            ),
        ],
      ),
    );
  }
}

class _LeaderboardRow extends StatelessWidget {
  const _LeaderboardRow({required this.entry});
  final RankingEntry entry;

  Color get _rankAccent {
    switch (entry.rank) {
      case 1:
        return const Color(0xFFF59E0B);
      case 2:
        return const Color(0xFF94A3B8);
      case 3:
        return const Color(0xFFB45309);
      default:
        return AppTheme.primaryDark;
    }
  }

  IconData? get _rankIcon {
    if (entry.rank == 1) return PhosphorIconsFill.crown;
    if (entry.rank == 2) return PhosphorIconsFill.medal;
    if (entry.rank == 3) return PhosphorIconsFill.medal;
    return null;
  }

  @override
  Widget build(BuildContext context) {
    final isTop3 = entry.rank <= 3;
    final accent = _rankAccent;
    final isCurrent = entry.isCurrent;

    return Container(
      margin: const EdgeInsets.symmetric(vertical: 3, horizontal: 4),
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 10),
      decoration: BoxDecoration(
        color: isCurrent
            ? AppTheme.primary.withValues(alpha: 0.06)
            : Colors.transparent,
        borderRadius: BorderRadius.circular(12),
        border: Border.all(
          color: isCurrent
              ? AppTheme.primary.withValues(alpha: 0.30)
              : Colors.transparent,
        ),
      ),
      child: Row(
        children: [
          // Rank pill
          SizedBox(
            width: 32,
            child: isTop3
                ? Container(
                    width: 32,
                    height: 32,
                    decoration: BoxDecoration(
                      gradient: LinearGradient(
                        colors: [accent, accent.withValues(alpha: 0.75)],
                        begin: Alignment.topLeft,
                        end: Alignment.bottomRight,
                      ),
                      borderRadius: BorderRadius.circular(10),
                      boxShadow: [
                        BoxShadow(
                          color: accent.withValues(alpha: 0.35),
                          blurRadius: 10,
                          offset: const Offset(0, 4),
                        ),
                      ],
                    ),
                    alignment: Alignment.center,
                    child: Icon(_rankIcon, color: Colors.white, size: 14),
                  )
                : Container(
                    width: 32,
                    height: 32,
                    decoration: BoxDecoration(
                      color: AppTheme.surfaceMuted,
                      borderRadius: BorderRadius.circular(10),
                    ),
                    alignment: Alignment.center,
                    child: Text(
                      '${entry.rank}',
                      style: const TextStyle(
                        color: AppTheme.textSecondary,
                        fontWeight: FontWeight.w800,
                        fontSize: 12.5,
                      ),
                    ),
                  ),
          ),
          const SizedBox(width: 10),
          // Avatar
          Container(
            width: 34,
            height: 34,
            decoration: BoxDecoration(
              gradient: const LinearGradient(
                colors: [AppTheme.primary, AppTheme.primaryDark],
                begin: Alignment.topLeft,
                end: Alignment.bottomRight,
              ),
              borderRadius: BorderRadius.circular(11),
              border: isCurrent
                  ? Border.all(color: AppTheme.success, width: 2)
                  : null,
            ),
            alignment: Alignment.center,
            child: Text(
              entry.initials,
              style: const TextStyle(
                color: Colors.white,
                fontWeight: FontWeight.w900,
                fontSize: 11.5,
                letterSpacing: 0.4,
              ),
            ),
          ),
          const SizedBox(width: 10),
          // Name + role
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  children: [
                    Flexible(
                      child: Text(
                        entry.name.isEmpty ? '—' : entry.name,
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis,
                        style: const TextStyle(
                          fontWeight: FontWeight.w800,
                          color: AppTheme.textPrimary,
                          fontSize: 13,
                        ),
                      ),
                    ),
                    if (isCurrent) ...[
                      const SizedBox(width: 6),
                      Container(
                        padding: const EdgeInsets.symmetric(
                          horizontal: 6,
                          vertical: 1,
                        ),
                        decoration: BoxDecoration(
                          color: AppTheme.success.withValues(alpha: 0.12),
                          borderRadius: BorderRadius.circular(999),
                        ),
                        child: const Text(
                          'YOU',
                          style: TextStyle(
                            color: AppTheme.success,
                            fontWeight: FontWeight.w900,
                            fontSize: 8.5,
                            letterSpacing: 0.8,
                          ),
                        ),
                      ),
                    ],
                  ],
                ),
                if (entry.role.isNotEmpty) ...[
                  const SizedBox(height: 1),
                  Text(
                    entry.role,
                    maxLines: 1,
                    overflow: TextOverflow.ellipsis,
                    style: const TextStyle(
                      color: AppTheme.textSecondary,
                      fontSize: 11,
                    ),
                  ),
                ],
              ],
            ),
          ),
          const SizedBox(width: 8),
          // Points
          Column(
            crossAxisAlignment: CrossAxisAlignment.end,
            children: [
              Text(
                '${entry.points}',
                style: TextStyle(
                  color: isTop3 ? accent : AppTheme.primaryDark,
                  fontWeight: FontWeight.w900,
                  fontSize: 15,
                  letterSpacing: -0.3,
                ),
              ),
              const Text(
                'pts',
                style: TextStyle(
                  color: AppTheme.textMuted,
                  fontSize: 9.5,
                  fontWeight: FontWeight.w800,
                  letterSpacing: 0.6,
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }
}

class _RankingEmpty extends StatelessWidget {
  const _RankingEmpty();

  @override
  Widget build(BuildContext context) {
    return Row(
      children: [
        Container(
          width: 40,
          height: 40,
          decoration: BoxDecoration(
            color: AppTheme.surfaceMuted,
            borderRadius: BorderRadius.circular(12),
          ),
          child: const Icon(
            PhosphorIconsBold.trophy,
            color: AppTheme.textMuted,
            size: 18,
          ),
        ),
        const SizedBox(width: 12),
        const Expanded(
          child: Text(
            'No rankings yet for this period.',
            style: TextStyle(color: AppTheme.textSecondary, fontSize: 12.5),
          ),
        ),
      ],
    );
  }
}

class _RankingFallback extends StatelessWidget {
  const _RankingFallback({required this.message});
  final String message;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(20),
        border: Border.all(color: AppTheme.border),
        boxShadow: AppTheme.shadowSoft,
      ),
      child: Row(
        children: [
          Container(
            width: 36,
            height: 36,
            decoration: BoxDecoration(
              color: AppTheme.warning.withValues(alpha: 0.10),
              borderRadius: BorderRadius.circular(11),
            ),
            child: const Icon(
              PhosphorIconsBold.trophy,
              color: AppTheme.warning,
              size: 18,
            ),
          ),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const Text(
                  'Accomplishment Summary',
                  style: TextStyle(
                    fontWeight: FontWeight.w900,
                    color: AppTheme.textPrimary,
                    fontSize: 13.5,
                  ),
                ),
                const SizedBox(height: 4),
                Text(
                  message,
                  style: const TextStyle(
                    color: AppTheme.textSecondary,
                    fontSize: 12,
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

class _RankingSkeleton extends StatelessWidget {
  const _RankingSkeleton();

  @override
  Widget build(BuildContext context) {
    return SkeletonCard(
      radius: 20,
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: const [
          Row(
            children: [
              Skeleton(width: 38, height: 38, radius: 12),
              SizedBox(width: 12),
              Expanded(child: Skeleton(width: 160, height: 14)),
              SizedBox(width: 8),
              Skeleton(width: 52, height: 22, radius: 999),
            ],
          ),
          SizedBox(height: 16),
          Skeleton(height: 48, radius: 12),
          SizedBox(height: 8),
          Skeleton(height: 48, radius: 12),
          SizedBox(height: 8),
          Skeleton(height: 48, radius: 12),
        ],
      ),
    );
  }
}

// ── Ongoing tasks panel ────────────────────────────────────────────────────

class _ViewAllLink extends StatelessWidget {
  const _ViewAllLink({required this.onTap});
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    return PressScale(
      onTap: () {
        Haptics.light();
        onTap();
      },
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
        decoration: BoxDecoration(
          color: AppTheme.primarySoft,
          borderRadius: BorderRadius.circular(999),
        ),
        child: Row(
          mainAxisSize: MainAxisSize.min,
          children: const [
            Text(
              'View all',
              style: TextStyle(
                fontSize: 11,
                fontWeight: FontWeight.w800,
                color: AppTheme.primaryDark,
                letterSpacing: 0.2,
              ),
            ),
            SizedBox(width: 4),
            Icon(
              PhosphorIconsBold.arrowRight,
              size: 10,
              color: AppTheme.primaryDark,
            ),
          ],
        ),
      ),
    );
  }
}

class _TaskPanel extends StatelessWidget {
  const _TaskPanel({required this.tasks, required this.onOpenTasks});

  final List<OngoingTask> tasks;
  final VoidCallback onOpenTasks;

  @override
  Widget build(BuildContext context) {
    if (tasks.isEmpty) {
      return Container(
        padding: const EdgeInsets.all(16),
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(18),
          border: Border.all(color: AppTheme.border),
          boxShadow: AppTheme.shadowSoft,
        ),
        child: Row(
          children: [
            Container(
              width: 36,
              height: 36,
              decoration: BoxDecoration(
                color: AppTheme.primarySoft,
                borderRadius: BorderRadius.circular(11),
              ),
              child: const Icon(
                PhosphorIconsBold.checkSquareOffset,
                size: 16,
                color: AppTheme.primary,
              ),
            ),
            const SizedBox(width: 12),
            const Expanded(
              child: Text(
                'No active tasks in your deadline window.',
                style: TextStyle(color: AppTheme.textSecondary, fontSize: 12.5),
              ),
            ),
          ],
        ),
      );
    }

    return Column(
      children: [
        for (var i = 0; i < tasks.length; i++) ...[
          FadeSlide(
            delay: Duration(milliseconds: 60 * i),
            child: _OngoingTaskTile(task: tasks[i]),
          ),
          if (i != tasks.length - 1) const SizedBox(height: 10),
        ],
      ],
    );
  }
}

class _OngoingTaskTile extends StatelessWidget {
  const _OngoingTaskTile({required this.task});
  final OngoingTask task;

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
              Expanded(
                child: Text(
                  task.title,
                  maxLines: 2,
                  overflow: TextOverflow.ellipsis,
                  style: const TextStyle(
                    fontWeight: FontWeight.w800,
                    color: AppTheme.textPrimary,
                    fontSize: 13.5,
                  ),
                ),
              ),
              const SizedBox(width: 8),
              Text(
                '${task.progress}%',
                style: const TextStyle(
                  fontWeight: FontWeight.w900,
                  fontSize: 12,
                  color: AppTheme.primaryDark,
                ),
              ),
            ],
          ),
          if (task.subtitle.isNotEmpty) ...[
            const SizedBox(height: 4),
            Text(
              task.subtitle,
              maxLines: 1,
              overflow: TextOverflow.ellipsis,
              style: const TextStyle(
                color: AppTheme.textSecondary,
                fontSize: 12,
              ),
            ),
          ],
          const SizedBox(height: 10),
          ClipRRect(
            borderRadius: BorderRadius.circular(999),
            child: LinearProgressIndicator(
              minHeight: 5,
              value: task.progress / 100,
              backgroundColor: AppTheme.surfaceMuted,
              valueColor: const AlwaysStoppedAnimation(AppTheme.primary),
            ),
          ),
          const SizedBox(height: 8),
          Row(
            children: [
              Expanded(
                child: Text(
                  'Due ${task.dueDate.isEmpty ? 'Not set' : task.dueDate}',
                  maxLines: 1,
                  overflow: TextOverflow.ellipsis,
                  style: const TextStyle(
                    color: AppTheme.textSecondary,
                    fontWeight: FontWeight.w600,
                    fontSize: 11,
                  ),
                ),
              ),
              if (task.priority.isNotEmpty)
                Container(
                  padding: const EdgeInsets.symmetric(
                    horizontal: 8,
                    vertical: 3,
                  ),
                  decoration: BoxDecoration(
                    color: AppTheme.primary.withValues(alpha: 0.10),
                    borderRadius: BorderRadius.circular(999),
                  ),
                  child: Text(
                    task.priority,
                    style: const TextStyle(
                      color: AppTheme.primaryDark,
                      fontWeight: FontWeight.w800,
                      fontSize: 10,
                      letterSpacing: 0.4,
                    ),
                  ),
                ),
            ],
          ),
        ],
      ),
    );
  }
}

// ── Events & Notes snapshot ────────────────────────────────────────────────

class _EventsNotesSnapshot extends StatelessWidget {
  const _EventsNotesSnapshot({
    required this.eventsFuture,
    required this.notesFuture,
    required this.onOpenCalendar,
    required this.onOpenNotes,
    required this.session,
  });

  final Future<List<CalendarEvent>> eventsFuture;
  final Future<List<Note>> notesFuture;
  final VoidCallback onOpenCalendar;
  final VoidCallback onOpenNotes;
  final StaffSession session;

  static bool _eventCoversDay(CalendarEvent event, DateTime day) {
    final dayStart = DateTime(day.year, day.month, day.day);
    final start = DateTime(event.start.year, event.start.month, event.start.day);
    final end = DateTime(event.end.year, event.end.month, event.end.day);
    return !dayStart.isBefore(start) && !dayStart.isAfter(end);
  }

  @override
  Widget build(BuildContext context) {
    return FutureBuilder<List<CalendarEvent>>(
      future: eventsFuture,
      builder: (context, eventsSnapshot) {
        return FutureBuilder<List<Note>>(
          future: notesFuture,
          builder: (context, notesSnapshot) {
            final now = DateTime.now();
            final today = DateTime(now.year, now.month, now.day);
            final events = (eventsSnapshot.data ?? const <CalendarEvent>[])
                .where((e) => _eventCoversDay(e, today) || e.start.isAfter(today))
                .toList()
              ..sort((a, b) => a.start.compareTo(b.start));
            final notes = (notesSnapshot.data ?? const <Note>[])
                .where((n) => n.date == _formatDate(today))
                .toList();

            final hasEvents = events.isNotEmpty;
            final hasNotes = notes.isNotEmpty;
            final loading = eventsSnapshot.connectionState == ConnectionState.waiting ||
                notesSnapshot.connectionState == ConnectionState.waiting;

            if (loading && !hasEvents && !hasNotes) {
              return const _SnapshotSkeleton();
            }

            if (!hasEvents && !hasNotes) {
              return _EmptySnapshot(
                onOpenCalendar: session.hasCalendar ? onOpenCalendar : null,
                onOpenNotes: session.hasNotes ? onOpenNotes : null,
              );
            }

            return Column(
              crossAxisAlignment: CrossAxisAlignment.stretch,
              children: [
                if (hasEvents) ...[
                  _SnapshotSubHeader(icon: PhosphorIconsBold.calendarBlank, title: 'Today & upcoming events'),
                  const SizedBox(height: 8),
                  for (final event in events.take(3)) _EventSnapshotRow(event: event),
                ],
                if (hasEvents && hasNotes) const SizedBox(height: 16),
                if (hasNotes) ...[
                  _SnapshotSubHeader(icon: PhosphorIconsBold.notebook, title: 'Notes for today'),
                  const SizedBox(height: 8),
                  for (final note in notes.take(3)) _NoteSnapshotRow(note: note),
                ],
              ],
            );
          },
        );
      },
    );
  }

  static String _formatDate(DateTime d) {
    final y = d.year.toString().padLeft(4, '0');
    final m = d.month.toString().padLeft(2, '0');
    final day = d.day.toString().padLeft(2, '0');
    return '$y-$m-$day';
  }
}

class _SnapshotSubHeader extends StatelessWidget {
  const _SnapshotSubHeader({required this.icon, required this.title});
  final IconData icon;
  final String title;

  @override
  Widget build(BuildContext context) {
    return Row(
      children: [
        Icon(icon, size: 14, color: AppTheme.primaryDark),
        const SizedBox(width: 8),
        Text(
          title,
          style: const TextStyle(
            fontSize: 12.5,
            fontWeight: FontWeight.w900,
            color: AppTheme.textPrimary,
          ),
        ),
      ],
    );
  }
}

class _EventSnapshotRow extends StatelessWidget {
  const _EventSnapshotRow({required this.event});
  final CalendarEvent event;

  Color get _color {
    try {
      final hex = event.color.replaceAll('#', '');
      if (hex.length == 6) return Color(int.parse('FF$hex', radix: 16));
    } catch (_) {}
    return AppTheme.primaryDark;
  }

  String _timeLabel(CalendarEvent e) {
    if (e.allDay) return 'All day';
    final h = e.start.hour == 0 ? 12 : (e.start.hour > 12 ? e.start.hour - 12 : e.start.hour);
    final m = e.start.minute.toString().padLeft(2, '0');
    final ampm = e.start.hour >= 12 ? 'PM' : 'AM';
    return '$h:$m $ampm';
  }

  String _dayLabel(CalendarEvent e) {
    final now = DateTime.now();
    final today = DateTime(now.year, now.month, now.day);
    final start = DateTime(e.start.year, e.start.month, e.start.day);
    if (start == today) return 'Today';
    const names = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
    return '${names[start.month - 1]} ${start.day}';
  }

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(12),
      margin: const EdgeInsets.only(bottom: 8),
      decoration: BoxDecoration(
        color: AppTheme.surface,
        borderRadius: BorderRadius.circular(14),
        boxShadow: AppTheme.shadowSoft,
      ),
      child: Row(
        children: [
          Container(
            width: 10,
            height: 10,
            decoration: BoxDecoration(color: _color, shape: BoxShape.circle),
          ),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  event.title,
                  maxLines: 1,
                  overflow: TextOverflow.ellipsis,
                  style: const TextStyle(
                    fontWeight: FontWeight.w800,
                    fontSize: 13.5,
                    color: AppTheme.textPrimary,
                  ),
                ),
                const SizedBox(height: 2),
                Text(
                  '${_dayLabel(event)} · ${_timeLabel(event)}',
                  style: const TextStyle(
                    color: AppTheme.textSecondary,
                    fontSize: 11.5,
                    fontWeight: FontWeight.w600,
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

class _NoteSnapshotRow extends StatelessWidget {
  const _NoteSnapshotRow({required this.note});
  final Note note;

  @override
  Widget build(BuildContext context) {
    final title = note.title.trim();
    final description = note.description.trim();
    return Container(
      padding: const EdgeInsets.all(12),
      margin: const EdgeInsets.only(bottom: 8),
      decoration: BoxDecoration(
        color: AppTheme.surface,
        borderRadius: BorderRadius.circular(14),
        boxShadow: AppTheme.shadowSoft,
      ),
      child: Row(
        children: [
          Container(
            width: 10,
            height: 10,
            decoration: const BoxDecoration(
              color: AppTheme.primary,
              shape: BoxShape.circle,
            ),
          ),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                if (title.isNotEmpty)
                  Text(
                    title,
                    maxLines: 1,
                    overflow: TextOverflow.ellipsis,
                    style: const TextStyle(
                      fontWeight: FontWeight.w800,
                      fontSize: 13.5,
                      color: AppTheme.textPrimary,
                    ),
                  ),
                if (description.isNotEmpty) ...[
                  if (title.isNotEmpty) const SizedBox(height: 2),
                  Text(
                    description,
                    maxLines: 2,
                    overflow: TextOverflow.ellipsis,
                    style: const TextStyle(
                      color: AppTheme.textSecondary,
                      fontSize: 12,
                      fontWeight: FontWeight.w600,
                    ),
                  ),
                ],
                if (title.isEmpty && description.isEmpty)
                  const Text(
                    'Empty note',
                    style: TextStyle(
                      color: AppTheme.textMuted,
                      fontSize: 12,
                      fontStyle: FontStyle.italic,
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

class _EmptySnapshot extends StatelessWidget {
  const _EmptySnapshot({this.onOpenCalendar, this.onOpenNotes});
  final VoidCallback? onOpenCalendar;
  final VoidCallback? onOpenNotes;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: AppTheme.surface,
        borderRadius: BorderRadius.circular(16),
        boxShadow: AppTheme.shadowSoft,
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          const Text(
            'No events or notes for today yet.',
            textAlign: TextAlign.center,
            style: TextStyle(
              color: AppTheme.textSecondary,
              fontSize: 13.5,
              fontWeight: FontWeight.w600,
            ),
          ),
          const SizedBox(height: 12),
          if (onOpenCalendar != null)
            FilledButton.icon(
              style: FilledButton.styleFrom(backgroundColor: AppTheme.primaryDark),
              onPressed: onOpenCalendar,
              icon: const Icon(PhosphorIconsBold.calendarBlank, size: 16),
              label: const Text('Open calendar'),
            ),
          if (onOpenCalendar != null && onOpenNotes != null) const SizedBox(height: 8),
          if (onOpenNotes != null)
            OutlinedButton.icon(
              onPressed: onOpenNotes,
              icon: const Icon(PhosphorIconsBold.notebook, size: 16),
              label: const Text('Open notes'),
            ),
        ],
      ),
    );
  }
}

class _SnapshotSkeleton extends StatelessWidget {
  const _SnapshotSkeleton();

  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: const [
        Skeleton(width: 140, height: 12),
        SizedBox(height: 8),
        Skeleton(height: 56, radius: 14),
        SizedBox(height: 8),
        Skeleton(height: 56, radius: 14),
      ],
    );
  }
}

// ── Skeleton + states ──────────────────────────────────────────────────────

class _DashboardSkeleton extends StatelessWidget {
  const _DashboardSkeleton();

  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        const Skeleton(width: 180, height: 16),
        const SizedBox(height: 8),
        const Skeleton(width: 120, height: 10),
        const SizedBox(height: 18),
        LayoutBuilder(
          builder: (context, constraints) {
            final tileWidth = (constraints.maxWidth - 12) / 2;
            return Wrap(
              spacing: 12,
              runSpacing: 12,
              children: [
                for (var i = 0; i < 4; i++)
                  SizedBox(
                    width: tileWidth,
                    child: SkeletonCard(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: const [
                          Skeleton(width: 32, height: 32, radius: 10),
                          SizedBox(height: 14),
                          Skeleton(width: 70, height: 22),
                          SizedBox(height: 6),
                          Skeleton(width: 56, height: 10),
                        ],
                      ),
                    ),
                  ),
              ],
            );
          },
        ),
        const SizedBox(height: 20),
        SkeletonCard(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: const [
              Skeleton(width: 140, height: 14),
              SizedBox(height: 14),
              Row(
                children: [
                  Expanded(child: Skeleton(height: 56, radius: 12)),
                  SizedBox(width: 8),
                  Expanded(child: Skeleton(height: 56, radius: 12)),
                  SizedBox(width: 8),
                  Expanded(child: Skeleton(height: 56, radius: 12)),
                ],
              ),
              SizedBox(height: 12),
              Skeleton(height: 44, radius: 12),
            ],
          ),
        ),
      ],
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
        color: Colors.white,
        borderRadius: BorderRadius.circular(18),
        border: Border.all(color: AppTheme.border),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Container(
                width: 36,
                height: 36,
                decoration: BoxDecoration(
                  color: AppTheme.danger.withValues(alpha: 0.10),
                  borderRadius: BorderRadius.circular(12),
                ),
                child: const Icon(
                  PhosphorIconsBold.warningCircle,
                  color: AppTheme.danger,
                  size: 18,
                ),
              ),
              const SizedBox(width: 12),
              const Expanded(
                child: Text(
                  'Unable to load dashboard',
                  style: TextStyle(
                    fontWeight: FontWeight.w900,
                    color: AppTheme.textPrimary,
                  ),
                ),
              ),
            ],
          ),
          const SizedBox(height: 10),
          Text(
            message,
            style: const TextStyle(
              color: AppTheme.textSecondary,
              fontSize: 12.5,
            ),
          ),
          const SizedBox(height: 14),
          FilledButton.icon(
            onPressed: () {
              Haptics.medium();
              onRetry();
              AppToast.info(context, 'Reloading dashboard…');
            },
            icon: const Icon(PhosphorIconsBold.arrowsClockwise, size: 16),
            label: const Text('Try again'),
          ),
        ],
      ),
    );
  }
}
