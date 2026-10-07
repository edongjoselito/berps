import 'dart:math' as math;

import 'package:flutter/material.dart';
import 'package:flutter_html/flutter_html.dart';
import 'package:lucide_icons_flutter/lucide_icons.dart';
import 'package:url_launcher/url_launcher.dart';

import '../../../core/network/api_exception.dart';
import '../../../core/theme/app_theme.dart';
import '../../../core/widgets/desktop_kit.dart';
import '../../../core/utils/haptics.dart';
import '../../../core/utils/responsive.dart';
import '../../../core/widgets/animations.dart';
import '../../../core/widgets/app_toast.dart';
import '../../../core/widgets/mobile_header.dart';
import '../../../core/widgets/skeleton.dart';
import '../../../core/widgets/staff_avatar.dart';
import '../../auth/domain/staff_session.dart';
import '../../notifications/presentation/notification_bell.dart';
import '../../notes/data/notes_api.dart';
import '../../notes/domain/note.dart';
import '../../reminders/data/reminders_api.dart';
import '../../reminders/domain/reminder.dart';
import '../data/staff_api.dart';
import '../domain/staff_dashboard.dart';
import '../domain/staff_ranking.dart';

class StaffDashboardTab extends StatefulWidget {
  const StaffDashboardTab({
    super.key,
    required this.session,
    this.onMenu,
    required this.onOpenAttendance,
    required this.onOpenTasks,
    required this.onOpenMyDtr,
    required this.onOpenForwardedTasks,
    required this.onOpenUnassignedTickets,
    required this.onOpenSupportTickets,
    required this.onOpenReminders,
    required this.onOpenCalendar,
    required this.onOpenNotes,
    this.onOpenTasksWithFilter,
  });

  final StaffSession session;
  final VoidCallback? onMenu;
  final VoidCallback onOpenAttendance;
  final VoidCallback onOpenTasks;
  final VoidCallback onOpenMyDtr;
  final VoidCallback onOpenForwardedTasks;
  final VoidCallback onOpenUnassignedTickets;
  final VoidCallback onOpenSupportTickets;
  final VoidCallback onOpenReminders;
  final VoidCallback onOpenCalendar;
  final VoidCallback onOpenNotes;
  final void Function(String statFilter)? onOpenTasksWithFilter;

  @override
  State<StaffDashboardTab> createState() => _StaffDashboardTabState();
}

class _StaffDashboardTabState extends State<StaffDashboardTab> {
  final StaffApi _api = StaffApi();
  final NotesApi _notesApi = NotesApi();
  final RemindersApi _remindersApi = RemindersApi();
  late Future<StaffDashboard> _future;
  late Future<StaffRanking> _rankingFuture;
  late Future<List<Note>> _notesFuture;
  late Future<List<Reminder>> _remindersFuture;

  @override
  void initState() {
    super.initState();
    _future = _loadDashboard();
    _rankingFuture = _loadRanking();
    _notesFuture = _loadNotes();
    _remindersFuture = _loadReminders();
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

  Future<List<Note>> _loadNotes() {
    return _notesApi.fetchNotes(
      baseUrl: widget.session.baseUrl,
      token: widget.session.token,
    );
  }

  Future<List<Reminder>> _loadReminders() {
    return _remindersApi.fetchReminders(
      baseUrl: widget.session.baseUrl,
      token: widget.session.token,
    ).then((data) => data.reminders);
  }

  void _reload() {
    Haptics.light();
    setState(() {
      _future = _loadDashboard();
      _rankingFuture = _loadRanking();
      _notesFuture = _loadNotes();
      _remindersFuture = _loadReminders();
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
    if (hour < 12) return LucideIcons.sunrise;
    if (hour < 18) return LucideIcons.sun;
    return LucideIcons.moonStar;
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
          _notesFuture = _loadNotes();
          _remindersFuture = _loadReminders();
        });
        await _future;
        await _notesFuture;
        await _remindersFuture;
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
                  subtitle: AppTheme.isDesktop
                      ? '${_greeting()}, ${widget.session.firstName.isNotEmpty ? widget.session.firstName : widget.session.formalName} · ${_GreetingCard.formatDate(DateTime.now())}'
                      : null,
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
              const SizedBox(height: 14),
              if (!AppTheme.isDesktop) ...[
                FadeSlide(
                  delay: const Duration(milliseconds: 60),
                  child: _GreetingCard(
                    greeting: _greeting(),
                    greetingIcon: _greetingIcon(),
                    session: widget.session,
                  ),
                ),
                const SizedBox(height: 18),
              ],
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
                  notesFuture: _notesFuture,
                  remindersFuture: _remindersFuture,
                  onOpenTasks: widget.onOpenTasks,
                  onOpenMyDtr: widget.onOpenMyDtr,
                  onOpenForwardedTasks: widget.onOpenForwardedTasks,
                  onOpenUnassignedTickets: widget.onOpenUnassignedTickets,
                  onOpenSupportTickets: widget.onOpenSupportTickets,
                  onOpenReminders: widget.onOpenReminders,
                  onOpenCalendar: widget.onOpenCalendar,
                  onOpenNotes: widget.onOpenNotes,
                  onOpenTasksWithFilter: widget.onOpenTasksWithFilter,
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
        borderRadius:
            BorderRadius.circular(AppTheme.isDesktop ? 14 : 20),
        border: Border.all(color: AppTheme.border),
        boxShadow: AppTheme.isDesktop ? null : AppTheme.shadowSoft,
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

  String _formatDate(DateTime now) => formatDate(now);

  static String formatDate(DateTime now) {
    return '${_days[now.weekday - 1]} · ${_months[now.month - 1]} ${now.day}';
  }
}

// ── Content ────────────────────────────────────────────────────────────────

class _DashboardContent extends StatelessWidget {
  const _DashboardContent({
    required this.data,
    required this.session,
    required this.rankingFuture,
    required this.notesFuture,
    required this.remindersFuture,
    required this.onOpenTasks,
    required this.onOpenMyDtr,
    required this.onOpenForwardedTasks,
    required this.onOpenUnassignedTickets,
    required this.onOpenSupportTickets,
    required this.onOpenReminders,
    required this.onOpenCalendar,
    required this.onOpenNotes,
    this.onOpenTasksWithFilter,
  });

  final StaffDashboard data;
  final StaffSession session;
  final Future<StaffRanking> rankingFuture;
  final Future<List<Note>> notesFuture;
  final Future<List<Reminder>> remindersFuture;
  final VoidCallback onOpenTasks;
  final VoidCallback onOpenMyDtr;
  final VoidCallback onOpenForwardedTasks;
  final VoidCallback onOpenUnassignedTickets;
  final VoidCallback onOpenSupportTickets;
  final VoidCallback onOpenReminders;
  final VoidCallback onOpenCalendar;
  final VoidCallback onOpenNotes;
  final void Function(String statFilter)? onOpenTasksWithFilter;

  @override
  Widget build(BuildContext context) {
    // Quick actions have been moved to the sidebar drawer to keep the
    // dashboard focused on metrics and snapshots. Notes, Forwarded Tasks,
    // and Tickets are all accessible from the drawer, ordered by importance.
    final quickActions = const <_QuickAction>[];

    final metricCards = <_MetricCardData>[
      _MetricCardData(
        label: 'Done Today',
        value: '${data.accomplishmentsToday}',
        icon: LucideIcons.circleCheck,
        accent: AppTheme.success,
        onTap: null,
      ),
      if (session.hasAttendance)
        _MetricCardData(
          label: 'Hours Today',
          value: data.todayHoursLabel,
          icon: LucideIcons.rotateCcw,
          accent: AppTheme.primary,
          onTap: onOpenMyDtr,
        ),
      _MetricCardData(
        label: 'Due Today',
        value: '${data.tasksDueToday}',
        icon: LucideIcons.calendarCheck,
        accent: AppTheme.accent,
        onTap: onOpenTasksWithFilter != null
            ? () => onOpenTasksWithFilter!('due_today')
            : null,
      ),
      _MetricCardData(
        label: 'Overdue',
        value: '${data.tasksOverdue}',
        icon: LucideIcons.circleAlert,
        accent: AppTheme.danger,
        onTap: onOpenTasksWithFilter != null
            ? () => onOpenTasksWithFilter!('overdue')
            : null,
      ),
    ];

    if (AppTheme.isDesktop) return _buildDesktop(metricCards);

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
        if (session.hasNotes || session.hasReminders) ...[
          const SizedBox(height: 20),
          FadeSlide(
            delay: const Duration(milliseconds: 260),
            child: _SectionHeader(
              title: 'Notes & Reminders',
              action: session.hasNotes
                  ? _ViewAllLink(onTap: onOpenNotes)
                  : null,
            ),
          ),
          const SizedBox(height: 12),
          FadeSlide(
            delay: const Duration(milliseconds: 280),
            child: _NotesRemindersSnapshot(
              notesFuture: notesFuture,
              remindersFuture: remindersFuture,
              onOpenNotes: onOpenNotes,
              onOpenReminders: onOpenReminders,
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

  /// Desktop composition: work (metrics + in-progress) in the main column,
  /// glanceable context (today, notes, leaderboard) in a fixed right rail.
  Widget _buildDesktop(List<_MetricCardData> metricCards) {
    final main = Column(
      crossAxisAlignment: CrossAxisAlignment.stretch,
      children: [
        _MetricGrid(cards: metricCards),
        if (data.remindersDueTodayCount > 0) ...[
          const SizedBox(height: 14),
          _RemindersBanner(
            count: data.remindersDueTodayCount,
            onTap: session.hasReminders ? onOpenReminders : null,
          ),
        ],
        if (session.hasTasks) ...[
          const SizedBox(height: 20),
          DeskPanel(
            title: 'In progress',
            count: data.ongoingTasks.length,
            action: DeskLink(label: 'View all tasks', onTap: onOpenTasks),
            padding: EdgeInsets.zero,
            child: _DeskTaskList(tasks: data.ongoingTasks),
          ),
        ],
      ],
    );

    final rail = Column(
      crossAxisAlignment: CrossAxisAlignment.stretch,
      children: [
        if (session.hasAttendance) ...[
          _DtrPreviewCard(
            statusLabel: data.attendanceStatusLabel,
            hoursLabel: data.todayHoursLabel,
            notice: data.attendanceNotice,
            onViewDtr: onOpenMyDtr,
          ),
          const SizedBox(height: 20),
        ],
        if (session.hasNotes || session.hasReminders) ...[
          DeskPanel(
            title: 'Notes & reminders',
            action: session.hasNotes
                ? DeskLink(label: 'Open', onTap: onOpenNotes)
                : null,
            padding: const EdgeInsets.fromLTRB(18, 14, 18, 16),
            child: _NotesRemindersSnapshot(
              notesFuture: notesFuture,
              remindersFuture: remindersFuture,
              onOpenNotes: onOpenNotes,
              onOpenReminders: onOpenReminders,
              session: session,
            ),
          ),
          const SizedBox(height: 20),
        ],
        if (session.hasRanking)
          FutureBuilder<StaffRanking>(
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
      ],
    );

    return FadeSlide(
      delay: const Duration(milliseconds: 80),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Expanded(child: main),
          const SizedBox(width: 20),
          SizedBox(width: 360, child: rail),
        ],
      ),
    );
  }
}

/// Desktop table-style list of in-progress tasks: one row per task with
/// progress, due date and priority aligned in columns.
class _DeskTaskList extends StatelessWidget {
  const _DeskTaskList({required this.tasks});

  final List<OngoingTask> tasks;

  @override
  Widget build(BuildContext context) {
    if (tasks.isEmpty) {
      return const Padding(
        padding: EdgeInsets.symmetric(horizontal: 18, vertical: 22),
        child: Text(
          'No active tasks in your deadline window.',
          style: TextStyle(
            color: AppTheme.textSecondary,
            fontSize: 13,
            fontWeight: FontWeight.w600,
          ),
        ),
      );
    }
    return Column(
      children: [
        for (var i = 0; i < tasks.length; i++) ...[
          if (i > 0) const Divider(height: 1, color: AppTheme.border),
          Padding(
            padding: const EdgeInsets.symmetric(horizontal: 18, vertical: 13),
            child: Row(
              children: [
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        tasks[i].title,
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis,
                        style: const TextStyle(
                          fontSize: 13.5,
                          fontWeight: FontWeight.w700,
                          color: AppTheme.textPrimary,
                        ),
                      ),
                      if (tasks[i].subtitle.isNotEmpty) ...[
                        const SizedBox(height: 3),
                        Text(
                          tasks[i].subtitle,
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                          style: const TextStyle(
                            fontSize: 12,
                            color: AppTheme.textSecondary,
                          ),
                        ),
                      ],
                    ],
                  ),
                ),
                const SizedBox(width: 20),
                SizedBox(
                  width: 150,
                  child: Row(
                    children: [
                      Expanded(
                        child: ClipRRect(
                          borderRadius: BorderRadius.circular(999),
                          child: LinearProgressIndicator(
                            minHeight: 5,
                            value: tasks[i].progress / 100,
                            backgroundColor: AppTheme.surfaceMuted,
                            valueColor: const AlwaysStoppedAnimation(
                              AppTheme.primary,
                            ),
                          ),
                        ),
                      ),
                      const SizedBox(width: 10),
                      SizedBox(
                        width: 36,
                        child: Text(
                          '${tasks[i].progress}%',
                          textAlign: TextAlign.right,
                          style: const TextStyle(
                            fontSize: 12,
                            fontWeight: FontWeight.w700,
                            color: AppTheme.textSecondary,
                          ),
                        ),
                      ),
                    ],
                  ),
                ),
                const SizedBox(width: 24),
                SizedBox(
                  width: 110,
                  child: Text(
                    tasks[i].dueDate.isEmpty ? 'No due date' : tasks[i].dueDate,
                    maxLines: 1,
                    overflow: TextOverflow.ellipsis,
                    style: const TextStyle(
                      fontSize: 12,
                      fontWeight: FontWeight.w600,
                      color: AppTheme.textSecondary,
                    ),
                  ),
                ),
                SizedBox(
                  width: 72,
                  child: tasks[i].priority.isEmpty
                      ? null
                      : Align(
                          alignment: Alignment.centerRight,
                          child: _PriorityTag(label: tasks[i].priority),
                        ),
                ),
              ],
            ),
          ),
        ],
      ],
    );
  }
}

class _PriorityTag extends StatelessWidget {
  const _PriorityTag({required this.label});

  final String label;

  Color get _color {
    final p = label.toLowerCase();
    if (p.contains('high') || p.contains('urgent')) return AppTheme.danger;
    if (p.contains('medium')) return AppTheme.warning;
    if (p.contains('low')) return AppTheme.success;
    return AppTheme.textSecondary;
  }

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
      decoration: BoxDecoration(
        color: _color.withValues(alpha: 0.10),
        borderRadius: BorderRadius.circular(6),
      ),
      child: Text(
        int.tryParse(label) != null ? 'P$label' : label,
        style: TextStyle(
          color: _color,
          fontSize: 11,
          fontWeight: FontWeight.w700,
        ),
      ),
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
  });
  final String label;
  final String sublabel;
  final IconData icon;
  final Color accent;
  final VoidCallback onTap;
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
            Container(
              width: 36,
              height: 36,
              decoration: BoxDecoration(
                color: action.accent.withValues(alpha: 0.12),
                borderRadius: BorderRadius.circular(11),
              ),
              child: Icon(action.icon, size: 17, color: action.accent),
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
      return LucideIcons.circleCheck;
    }
    if (label.contains('absent')) {
      return LucideIcons.circleX;
    }
    if (label.contains('pending') || label.contains('open')) {
      return LucideIcons.clock;
    }
    return LucideIcons.clock;
  }

  Widget _buildDesktop() {
    final showNotice = notice.isNotEmpty && notice.trim() != statusLabel.trim();
    return DeskPanel(
      title: 'Today',
      action: DeskLink(label: 'View DTR', onTap: onViewDtr),
      padding: const EdgeInsets.fromLTRB(18, 16, 18, 18),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            _formatDate(DateTime.now()),
            style: const TextStyle(
              fontSize: 12,
              fontWeight: FontWeight.w700,
              color: AppTheme.textMuted,
            ),
          ),
          const SizedBox(height: 6),
          Row(
            crossAxisAlignment: CrossAxisAlignment.baseline,
            textBaseline: TextBaseline.alphabetic,
            children: [
              Text(
                hoursLabel,
                style: const TextStyle(
                  fontSize: 30,
                  fontWeight: FontWeight.w800,
                  color: AppTheme.textPrimary,
                  letterSpacing: -1,
                ),
              ),
              const SizedBox(width: 8),
              const Text(
                'hours logged',
                style: TextStyle(
                  fontSize: 12.5,
                  fontWeight: FontWeight.w600,
                  color: AppTheme.textSecondary,
                ),
              ),
            ],
          ),
          const SizedBox(height: 14),
          Container(
            padding: const EdgeInsets.all(12),
            decoration: BoxDecoration(
              color: _statusColor.withValues(alpha: 0.07),
              borderRadius: BorderRadius.circular(10),
            ),
            child: Row(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Icon(_statusIcon, size: 16, color: _statusColor),
                const SizedBox(width: 10),
                Expanded(
                  child: Text(
                    statusLabel.isNotEmpty ? statusLabel : 'No status yet',
                    style: const TextStyle(
                      fontSize: 12.5,
                      height: 1.45,
                      fontWeight: FontWeight.w600,
                      color: AppTheme.textPrimary,
                    ),
                  ),
                ),
              ],
            ),
          ),
          if (showNotice) ...[
            const SizedBox(height: 10),
            Text(
              notice,
              style: const TextStyle(
                fontSize: 12,
                height: 1.45,
                color: AppTheme.textSecondary,
              ),
            ),
          ],
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    if (AppTheme.isDesktop) return _buildDesktop();
    final today = _formatDate(DateTime.now());
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        gradient: const LinearGradient(
          colors: [Color(0xFF1E3A5F), Color(0xFF2D5A8A)],
        ),
        borderRadius:
            BorderRadius.circular(AppTheme.isDesktop ? 14 : 18),
        boxShadow: AppTheme.isDesktop
            ? null
            : [
                BoxShadow(
                  color:
                      const Color(0xFF1E3A5F).withValues(alpha: 0.20),
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
                  LucideIcons.clock,
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
                          LucideIcons.chevronRight,
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
    this.onTap,
  });
  final String label;
  final String value;
  final IconData icon;
  final Color accent;
  final VoidCallback? onTap;
}

class _MetricGrid extends StatelessWidget {
  const _MetricGrid({required this.cards});
  final List<_MetricCardData> cards;

  @override
  Widget build(BuildContext context) {
    return LayoutBuilder(
      builder: (context, constraints) {
        if (AppTheme.isDesktop) {
          final perRow = constraints.maxWidth >= 720 ? cards.length : 2;
          final width =
              (constraints.maxWidth - 12 * (perRow - 1)) / perRow;
          return Wrap(
            spacing: 12,
            runSpacing: 12,
            children: [
              for (final card in cards)
                SizedBox(
                  width: width,
                  child: DeskStatCard(
                    icon: card.icon,
                    label: card.label,
                    value: card.value,
                    accent: card.accent,
                    onTap: card.onTap,
                  ),
                ),
            ],
          );
        }
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
    final card = Container(
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius:
            BorderRadius.circular(AppTheme.isDesktop ? 14 : 18),
        border: Border.all(color: AppTheme.border),
        boxShadow: AppTheme.isDesktop ? null : AppTheme.shadowSoft,
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
              if (data.onTap != null)
                Icon(
                  LucideIcons.chevronRight,
                  size: 14,
                  color: AppTheme.textMuted,
                )
              else
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

    if (data.onTap == null) return card;
    return PressScale(
      onTap: () {
        Haptics.light();
        data.onTap!();
      },
      child: card,
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
              LucideIcons.bellRing,
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
              LucideIcons.chevronRight,
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

    final desktop = AppTheme.isDesktop;
    return Container(
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(desktop ? 14 : 20),
        border: Border.all(color: AppTheme.border),
        boxShadow: desktop ? null : AppTheme.shadowSoft,
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Header band — sits on top with a gentle gold tint
          Container(
            padding: const EdgeInsets.fromLTRB(16, 16, 16, 14),
            decoration: BoxDecoration(
              gradient: desktop
                  ? null
                  : LinearGradient(
                      colors: [
                        const Color(0xFFFFFBEC),
                        Colors.white.withValues(alpha: 0.6),
                      ],
                      begin: Alignment.topLeft,
                      end: Alignment.bottomRight,
                    ),
              borderRadius: BorderRadius.vertical(
                top: Radius.circular(desktop ? 14 : 20),
              ),
            ),
            child: Row(
              children: [
                Container(
                  width: desktop ? 34 : 38,
                  height: desktop ? 34 : 38,
                  decoration: BoxDecoration(
                    color: desktop
                        ? const Color(0xFFF59E0B).withValues(alpha: 0.12)
                        : null,
                    gradient: desktop
                        ? null
                        : const LinearGradient(
                            colors: [Color(0xFFF59E0B), Color(0xFFFBBF24)],
                            begin: Alignment.topLeft,
                            end: Alignment.bottomRight,
                          ),
                    borderRadius: BorderRadius.circular(desktop ? 10 : 12),
                    boxShadow: desktop
                        ? null
                        : [
                            BoxShadow(
                              color: const Color(0xFFF59E0B)
                                  .withValues(alpha: 0.35),
                              blurRadius: 14,
                              offset: const Offset(0, 6),
                            ),
                          ],
                  ),
                  child: Icon(
                    LucideIcons.trophy,
                    color: desktop ? const Color(0xFFD97706) : Colors.white,
                    size: desktop ? 16 : 19,
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
                          LucideIcons.user,
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
    if (entry.rank == 1) return LucideIcons.crown;
    if (entry.rank == 2) return LucideIcons.medal;
    if (entry.rank == 3) return LucideIcons.medal;
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
            LucideIcons.trophy,
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
              LucideIcons.trophy,
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
              LucideIcons.arrowRight,
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
                LucideIcons.squareCheck,
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

class _NotesRemindersSnapshot extends StatelessWidget {
  const _NotesRemindersSnapshot({
    required this.notesFuture,
    required this.remindersFuture,
    required this.onOpenNotes,
    required this.onOpenReminders,
    required this.session,
  });

  final Future<List<Note>> notesFuture;
  final Future<List<Reminder>> remindersFuture;
  final VoidCallback onOpenNotes;
  final VoidCallback onOpenReminders;
  final StaffSession session;

  @override
  Widget build(BuildContext context) {
    return FutureBuilder<List<Reminder>>(
      future: remindersFuture,
      builder: (context, remindersSnapshot) {
        return FutureBuilder<List<Note>>(
          future: notesFuture,
          builder: (context, notesSnapshot) {
            final now = DateTime.now();
            final today = DateTime(now.year, now.month, now.day);
            final todayStr = _formatDate(today);

            // Notes: show today's notes + upcoming (most recent first)
            final notes = (notesSnapshot.data ?? const <Note>[])
                .where((n) => n.date == todayStr)
                .toList();

            // Reminders: show all, sorted by remind_at
            final reminders = (remindersSnapshot.data ?? const <Reminder>[])
                .toList()
              ..sort((a, b) => a.remindAt.compareTo(b.remindAt));

            final hasNotes = notes.isNotEmpty;
            final hasReminders = reminders.isNotEmpty;
            final loading =
                notesSnapshot.connectionState == ConnectionState.waiting ||
                    remindersSnapshot.connectionState == ConnectionState.waiting;

            if (loading && !hasNotes && !hasReminders) {
              return const _SnapshotSkeleton();
            }

            if (!hasNotes && !hasReminders) {
              return _EmptySnapshot(
                onOpenNotes: session.hasNotes ? onOpenNotes : null,
                onOpenReminders: session.hasReminders ? onOpenReminders : null,
              );
            }

            return Column(
              crossAxisAlignment: CrossAxisAlignment.stretch,
              children: [
                if (hasReminders) ...[
                  _SnapshotSubHeader(
                    icon: LucideIcons.bellRing,
                    title: 'Reminders',
                  ),
                  const SizedBox(height: 8),
                  for (final reminder in reminders.take(3))
                    _ReminderSnapshotRow(reminder: reminder),
                ],
                if (hasReminders && hasNotes) const SizedBox(height: 16),
                if (hasNotes) ...[
                  _SnapshotSubHeader(
                    icon: LucideIcons.notebookText,
                    title: 'Notes for today',
                  ),
                  const SizedBox(height: 8),
                  for (final note in notes.take(3))
                    _NoteSnapshotRow(note: note),
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

class _ReminderSnapshotRow extends StatelessWidget {
  const _ReminderSnapshotRow({required this.reminder});
  final Reminder reminder;

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
            decoration: const BoxDecoration(
              color: Color(0xFFFF9500),
              shape: BoxShape.circle,
            ),
          ),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  reminder.title,
                  maxLines: 1,
                  overflow: TextOverflow.ellipsis,
                  style: const TextStyle(
                    fontWeight: FontWeight.w800,
                    fontSize: 13.5,
                    color: AppTheme.textPrimary,
                  ),
                ),
                if (reminder.remindAtLabel.isNotEmpty) ...[
                  const SizedBox(height: 2),
                  Text(
                    reminder.remindAtLabel,
                    style: const TextStyle(
                      color: AppTheme.textSecondary,
                      fontSize: 11.5,
                      fontWeight: FontWeight.w600,
                    ),
                  ),
                ],
              ],
            ),
          ),
          if (reminder.recurrence != 'once')
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 3),
              decoration: BoxDecoration(
                color: const Color(0xFFFED7AA),
                borderRadius: BorderRadius.circular(99),
              ),
              child: Text(
                reminder.recurrenceLabel,
                style: const TextStyle(
                  fontSize: 9.5,
                  fontWeight: FontWeight.w800,
                  color: Color(0xFF9A3412),
                ),
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
                  Html(
                    data: description,
                    shrinkWrap: true,
                    style: {
                      'p': Style(
                        margin: Margins.zero,
                        color: AppTheme.textSecondary,
                        fontSize: FontSize(12),
                        fontWeight: FontWeight.w600,
                        maxLines: 2,
                        textOverflow: TextOverflow.ellipsis,
                      ),
                      'a': Style(
                        color: AppTheme.primaryDark,
                        fontSize: FontSize(12),
                        fontWeight: FontWeight.w700,
                        textDecoration: TextDecoration.underline,
                      ),
                    },
                    onLinkTap: (url, attributes, element) async {
                      if (url == null) return;
                      final uri = Uri.tryParse(url);
                      if (uri == null) return;
                      Haptics.light();
                      await launchUrl(uri,
                          mode: LaunchMode.externalApplication);
                    },
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
  const _EmptySnapshot({this.onOpenNotes, this.onOpenReminders});
  final VoidCallback? onOpenNotes;
  final VoidCallback? onOpenReminders;

  @override
  Widget build(BuildContext context) {
    if (AppTheme.isDesktop) {
      return Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Text(
            'Nothing scheduled for today.',
            style: TextStyle(
              color: AppTheme.textSecondary,
              fontSize: 13,
              fontWeight: FontWeight.w600,
            ),
          ),
          const SizedBox(height: 12),
          Wrap(
            spacing: 8,
            runSpacing: 8,
            children: [
              if (onOpenReminders != null)
                DeskButton(
                  label: 'Add reminder',
                  icon: LucideIcons.bellPlus,
                  primary: false,
                  onTap: onOpenReminders,
                ),
              if (onOpenNotes != null)
                DeskButton(
                  label: 'Write a note',
                  icon: LucideIcons.notebookPen,
                  primary: false,
                  onTap: onOpenNotes,
                ),
            ],
          ),
        ],
      );
    }
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
            'No notes or reminders for today yet.',
            textAlign: TextAlign.center,
            style: TextStyle(
              color: AppTheme.textSecondary,
              fontSize: 13.5,
              fontWeight: FontWeight.w600,
            ),
          ),
          const SizedBox(height: 12),
          if (onOpenReminders != null)
            FilledButton.icon(
              style: FilledButton.styleFrom(backgroundColor: AppTheme.primaryDark),
              onPressed: onOpenReminders,
              icon: const Icon(LucideIcons.bellRing, size: 16),
              label: const Text('Open reminders'),
            ),
          if (onOpenReminders != null && onOpenNotes != null) const SizedBox(height: 8),
          if (onOpenNotes != null)
            OutlinedButton.icon(
              onPressed: onOpenNotes,
              icon: const Icon(LucideIcons.notebookText, size: 16),
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
                  LucideIcons.circleAlert,
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
            icon: const Icon(LucideIcons.refreshCw, size: 16),
            label: const Text('Try again'),
          ),
        ],
      ),
    );
  }
}
