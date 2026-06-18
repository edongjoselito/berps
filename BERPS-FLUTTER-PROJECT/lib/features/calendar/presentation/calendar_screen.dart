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
import '../domain/calendar_event.dart';
import 'calendar_event_editor.dart';

class CalendarScreen extends StatefulWidget {
  const CalendarScreen({super.key, required this.session});
  final StaffSession session;

  @override
  State<CalendarScreen> createState() => _CalendarScreenState();
}

class _CalendarScreenState extends State<CalendarScreen> {
  final StaffApi _api = StaffApi();
  Future<List<CalendarEvent>>? _future;

  @override
  void initState() {
    super.initState();
    _reload();
  }

  void _reload() {
    setState(() {
      _future = _api.fetchCalendarEvents(
        baseUrl: widget.session.baseUrl,
        token: widget.session.token,
      );
    });
  }

  Future<void> _openCreate() async {
    Haptics.light();
    final created = await Navigator.of(context).push<bool>(
      MaterialPageRoute(
        builder: (_) => CalendarEventEditor(session: widget.session),
      ),
    );
    if (created == true) _reload();
  }

  Future<void> _openEdit(CalendarEvent event) async {
    Haptics.light();
    final updated = await Navigator.of(context).push<bool>(
      MaterialPageRoute(
        builder: (_) =>
            CalendarEventEditor(session: widget.session, existing: event),
      ),
    );
    if (updated == true) _reload();
  }

  Future<void> _confirmDelete(CalendarEvent event) async {
    Haptics.warn();
    final confirmed = await showDialog<bool>(
      context: context,
      builder: (dialogContext) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
        title: Text(
          'Delete event?',
          style: TextStyle(
            fontWeight: FontWeight.w900,
            color: AppTheme.textPrimary,
          ),
        ),
        content: Text(
          '"${event.title}" will be permanently removed.',
          style: TextStyle(color: AppTheme.textSecondary),
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.of(dialogContext).pop(false),
            child: const Text('Cancel'),
          ),
          FilledButton(
            style: FilledButton.styleFrom(backgroundColor: AppTheme.danger),
            onPressed: () => Navigator.of(dialogContext).pop(true),
            child: const Text('Delete'),
          ),
        ],
      ),
    );
    if (confirmed != true) return;

    try {
      await _api.deleteCalendarEvent(
        baseUrl: widget.session.baseUrl,
        token: widget.session.token,
        id: event.id,
      );
      if (!mounted) return;
      AppToast.success(context, 'Event deleted.');
      _reload();
    } on ApiException catch (e) {
      if (!mounted) return;
      AppToast.error(context, e.message);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppTheme.background,
      floatingActionButton: FloatingActionButton.extended(
        backgroundColor: AppTheme.primaryDark,
        foregroundColor: Colors.white,
        onPressed: _openCreate,
        icon: const Icon(PhosphorIconsBold.plus, size: 18),
        label: const Text(
          'New event',
          style: TextStyle(fontWeight: FontWeight.w800),
        ),
      ),
      body: SafeArea(
        bottom: false,
        child: FutureBuilder<List<CalendarEvent>>(
          future: _future,
          builder: (context, snapshot) {
            final loading = snapshot.connectionState == ConnectionState.waiting;
            final error = snapshot.error;
            final events = snapshot.data ?? const [];

            return RefreshIndicator(
              color: AppTheme.primary,
              onRefresh: () async => _reload(),
              child: ListView(
                physics: const AlwaysScrollableScrollPhysics(),
                padding: EdgeInsets.fromLTRB(
                  context.gutter,
                  12,
                  context.gutter,
                  100,
                ),
                children: [
                  MobileHeader(
                    title: 'Calendar',
                    subtitle: 'Your notes & reminders',
                    leadingIcon: PhosphorIconsBold.caretLeft,
                    onLeadingTap: () {
                      Haptics.light();
                      Navigator.of(context).maybePop();
                    },
                  ),
                  const SizedBox(height: 14),
                  if (loading && snapshot.data == null)
                    Column(
                      children: List.generate(
                        4,
                        (_) => const Padding(
                          padding: EdgeInsets.only(bottom: 12),
                          child: SkeletonCard(child: SizedBox(height: 64)),
                        ),
                      ),
                    )
                  else if (error != null && snapshot.data == null)
                    _ErrorCard(
                      message: error is ApiException
                          ? error.message
                          : 'Unable to load events.',
                      onRetry: _reload,
                    )
                  else if (events.isEmpty)
                    const _EmptyState()
                  else
                    ..._buildGroupedEvents(events),
                ],
              ),
            );
          },
        ),
      ),
    );
  }

  List<Widget> _buildGroupedEvents(List<CalendarEvent> events) {
    final now = DateTime.now();
    final upcoming = events.where((e) => !e.end.isBefore(now)).toList();
    final past = events.where((e) => e.end.isBefore(now)).toList();

    final widgets = <Widget>[];
    if (upcoming.isNotEmpty) {
      widgets.add(
        _CalendarSectionHeader(
          icon: PhosphorIconsBold.calendarCheck,
          title: 'Upcoming & today',
          count: upcoming.length,
        ),
      );
      widgets.add(const SizedBox(height: 10));
      for (var i = 0; i < upcoming.length; i++) {
        widgets.add(
          Padding(
            padding: const EdgeInsets.only(bottom: 10),
            child: FadeSlide(
              delay: Duration(milliseconds: 60 + i * 40),
              child: _EventCard(
                event: upcoming[i],
                onTap: () => _openEdit(upcoming[i]),
                onDelete: upcoming[i].canDelete
                    ? () => _confirmDelete(upcoming[i])
                    : null,
              ),
            ),
          ),
        );
      }
      widgets.add(const SizedBox(height: 14));
    }

    if (past.isNotEmpty) {
      widgets.add(
        _CalendarSectionHeader(
          icon: PhosphorIconsBold.calendarX,
          title: 'Past events',
          count: past.length,
        ),
      );
      widgets.add(const SizedBox(height: 10));
      for (var i = 0; i < past.length; i++) {
        widgets.add(
          Padding(
            padding: const EdgeInsets.only(bottom: 10),
            child: _EventCard(
              event: past[i],
              onTap: () => _openEdit(past[i]),
              onDelete: past[i].canDelete
                  ? () => _confirmDelete(past[i])
                  : null,
              faded: true,
            ),
          ),
        );
      }
    }
    return widgets;
  }
}

class _CalendarSectionHeader extends StatelessWidget {
  const _CalendarSectionHeader({
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

class _EventCard extends StatelessWidget {
  const _EventCard({
    required this.event,
    required this.onTap,
    required this.onDelete,
    this.faded = false,
  });

  final CalendarEvent event;
  final VoidCallback onTap;
  final VoidCallback? onDelete;
  final bool faded;

  Color get _accent {
    try {
      final hex = event.color.replaceAll('#', '');
      if (hex.length == 6) {
        return Color(int.parse('FF$hex', radix: 16));
      }
    } catch (_) {}
    return AppTheme.primaryDark;
  }

  @override
  Widget build(BuildContext context) {
    final accent = _accent;
    final timeLabel = _formatRange(event);
    return Opacity(
      opacity: faded ? 0.7 : 1.0,
      child: MobileSurfaceCard(
        padding: const EdgeInsets.all(14),
        child: InkWell(
          onTap: onTap,
          borderRadius: BorderRadius.circular(18),
          child: Row(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Container(
                width: 44,
                height: 44,
                decoration: BoxDecoration(
                  color: accent.withValues(alpha: 0.10),
                  borderRadius: BorderRadius.circular(12),
                ),
                child: Center(
                  child: Column(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      Text(
                        event.start.day.toString(),
                        style: TextStyle(
                          fontSize: 15,
                          fontWeight: FontWeight.w900,
                          color: accent,
                          height: 1.0,
                        ),
                      ),
                      Text(
                        _monthShort(event.start.month),
                        style: TextStyle(
                          fontSize: 9,
                          fontWeight: FontWeight.w800,
                          color: accent.withValues(alpha: 0.75),
                          height: 1.1,
                        ),
                      ),
                    ],
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
                          child: Text(
                            event.title,
                            maxLines: 2,
                            overflow: TextOverflow.ellipsis,
                            style: TextStyle(
                              fontWeight: FontWeight.w900,
                              color: AppTheme.textPrimary,
                              fontSize: 14.5,
                            ),
                          ),
                        ),
                        if (event.isPublic)
                          Container(
                            margin: const EdgeInsets.only(left: 8),
                            padding: const EdgeInsets.symmetric(
                              horizontal: 7,
                              vertical: 3,
                            ),
                            decoration: BoxDecoration(
                              color: accent.withValues(alpha: 0.10),
                              borderRadius: BorderRadius.circular(999),
                            ),
                            child: Text(
                              'PUBLIC',
                              style: TextStyle(
                                color: accent,
                                fontSize: 9,
                                fontWeight: FontWeight.w900,
                                letterSpacing: 1.0,
                              ),
                            ),
                          ),
                      ],
                    ),
                    const SizedBox(height: 4),
                    Row(
                      children: [
                        Icon(
                          PhosphorIconsBold.clock,
                          size: 12,
                          color: AppTheme.textSecondary,
                        ),
                        const SizedBox(width: 5),
                        Expanded(
                          child: Text(
                            timeLabel,
                            style: TextStyle(
                              color: AppTheme.textSecondary,
                              fontSize: 12,
                              fontWeight: FontWeight.w700,
                            ),
                          ),
                        ),
                      ],
                    ),
                    if (event.location.isNotEmpty) ...[
                      const SizedBox(height: 3),
                      Row(
                        children: [
                          Icon(
                            PhosphorIconsBold.mapPin,
                            size: 12,
                            color: AppTheme.textMuted,
                          ),
                          const SizedBox(width: 5),
                          Expanded(
                            child: Text(
                              event.location,
                              maxLines: 1,
                              overflow: TextOverflow.ellipsis,
                              style: TextStyle(
                                color: AppTheme.textMuted,
                                fontSize: 11.5,
                                fontWeight: FontWeight.w600,
                              ),
                            ),
                          ),
                        ],
                      ),
                    ],
                  ],
                ),
              ),
              if (onDelete != null)
                IconButton(
                  tooltip: 'Delete',
                  visualDensity: VisualDensity.compact,
                  icon: const Icon(
                    PhosphorIconsBold.trash,
                    size: 16,
                    color: AppTheme.danger,
                  ),
                  onPressed: onDelete,
                ),
            ],
          ),
        ),
      ),
    );
  }

  String _monthShort(int m) {
    const months = [
      'JAN',
      'FEB',
      'MAR',
      'APR',
      'MAY',
      'JUN',
      'JUL',
      'AUG',
      'SEP',
      'OCT',
      'NOV',
      'DEC',
    ];
    return months[m - 1];
  }

  String _formatRange(CalendarEvent e) {
    final sameDay =
        e.start.year == e.end.year &&
        e.start.month == e.end.month &&
        e.start.day == e.end.day;
    if (e.allDay) {
      return sameDay
          ? '${_formatDate(e.start)} · All day'
          : '${_formatDate(e.start)} → ${_formatDate(e.end)} · All day';
    }
    if (sameDay) {
      return '${_formatDate(e.start)} · ${_formatTime(e.start)} – ${_formatTime(e.end)}';
    }
    return '${_formatDate(e.start)} ${_formatTime(e.start)} → ${_formatDate(e.end)} ${_formatTime(e.end)}';
  }

  String _formatDate(DateTime dt) {
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
    return '${months[dt.month - 1]} ${dt.day}, ${dt.year}';
  }

  String _formatTime(DateTime dt) {
    final h = dt.hour == 0 ? 12 : (dt.hour > 12 ? dt.hour - 12 : dt.hour);
    final m = dt.minute.toString().padLeft(2, '0');
    final ampm = dt.hour >= 12 ? 'PM' : 'AM';
    return '$h:$m $ampm';
  }
}

class _EmptyState extends StatelessWidget {
  const _EmptyState();

  @override
  Widget build(BuildContext context) {
    return MobileSurfaceCard(
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Container(
            width: 44,
            height: 44,
            decoration: BoxDecoration(
              color: AppTheme.primarySoft,
              borderRadius: BorderRadius.circular(14),
            ),
            child: const Icon(
              PhosphorIconsFill.calendarBlank,
              color: AppTheme.primaryDark,
              size: 22,
            ),
          ),
          const SizedBox(height: 12),
          Text(
            'No events yet',
            style: TextStyle(
              fontWeight: FontWeight.w900,
              color: AppTheme.textPrimary,
              fontSize: 15,
            ),
          ),
          const SizedBox(height: 4),
          Text(
            'Tap "New event" to add a personal note or reminder.',
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
            'Calendar unavailable',
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
