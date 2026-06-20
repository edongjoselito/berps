import 'package:flutter/material.dart';
import 'package:phosphor_flutter/phosphor_flutter.dart';

import '../../../core/network/api_exception.dart';
import '../../../core/theme/app_theme.dart';
import '../../../core/utils/haptics.dart';
import '../../../core/widgets/app_toast.dart';
import '../../../core/widgets/mobile_header.dart';
import '../../auth/domain/staff_session.dart';
import '../../home/data/staff_api.dart';
import '../domain/calendar_event.dart';
import 'calendar_event_editor.dart';

/// Apple Calendar signature red — used for the year, the current month and
/// the "today" marker, exactly like the iOS Calendar year view.
const Color kAppleRed = Color(0xFFFF3B30);

const List<String> _monthNamesFull = [
  'January',
  'February',
  'March',
  'April',
  'May',
  'June',
  'July',
  'August',
  'September',
  'October',
  'November',
  'December',
];

const List<String> _monthNamesShort = [
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

const List<String> _weekdayLetters = ['S', 'M', 'T', 'W', 'T', 'F', 'S'];

/// Days laid out Sunday-first (matching iOS). 0 = blank padding cell.
List<List<int>> _monthWeeks(int year, int month) {
  final first = DateTime(year, month, 1);
  final daysInMonth = DateTime(year, month + 1, 0).day;
  final lead = first.weekday % 7; // Dart: Mon=1..Sun=7 → Sun=0
  final cells = <int>[];
  for (var i = 0; i < lead; i++) {
    cells.add(0);
  }
  for (var d = 1; d <= daysInMonth; d++) {
    cells.add(d);
  }
  while (cells.length % 7 != 0) {
    cells.add(0);
  }
  return [for (var i = 0; i < cells.length; i += 7) cells.sublist(i, i + 7)];
}

Color _eventColor(CalendarEvent e) {
  try {
    final hex = e.color.replaceAll('#', '');
    if (hex.length == 6) return Color(int.parse('FF$hex', radix: 16));
  } catch (_) {}
  return AppTheme.primaryDark;
}

bool _sameDay(DateTime a, DateTime b) =>
    a.year == b.year && a.month == b.month && a.day == b.day;

/// Whether [event] covers the calendar day [day].
bool _eventCoversDay(CalendarEvent event, DateTime day) {
  final dayStart = DateTime(day.year, day.month, day.day);
  final start = DateTime(event.start.year, event.start.month, event.start.day);
  final end = DateTime(event.end.year, event.end.month, event.end.day);
  return !dayStart.isBefore(start) && !dayStart.isAfter(end);
}

/// Calendar — iOS-style year view.
class CalendarScreen extends StatefulWidget {
  const CalendarScreen({super.key, required this.session});
  final StaffSession session;

  @override
  State<CalendarScreen> createState() => _CalendarScreenState();
}

class _CalendarScreenState extends State<CalendarScreen> {
  final ScrollController _scrollController = ScrollController();

  @override
  void dispose() {
    _scrollController.dispose();
    super.dispose();
  }

  Future<void> _openCreate() async {
    Haptics.light();
    await Navigator.of(context).push<bool>(
      MaterialPageRoute(
        builder: (_) => CalendarEventEditor(session: widget.session),
      ),
    );
  }

  Future<void> _openMonth(int year, int month) async {
    Haptics.light();
    await Navigator.of(context).push<void>(
      MaterialPageRoute(
        builder: (_) => _MonthDetailScreen(
          session: widget.session,
          year: year,
          month: month,
        ),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final now = DateTime.now();
    // Current year at the top, scrolling forward — mirrors the iOS year view.
    final years = [for (var y = now.year; y <= now.year + 4; y++) y];

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
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            _TopBar(
              onBack: () {
                Haptics.light();
                Navigator.of(context).maybePop();
              },
            ),
            Expanded(
              child: ListView.builder(
                controller: _scrollController,
                physics: const AlwaysScrollableScrollPhysics(),
                padding: const EdgeInsets.fromLTRB(16, 4, 16, 110),
                itemCount: years.length,
                itemBuilder: (context, index) {
                  final year = years[index];
                  return _YearBlock(
                    year: year,
                    today: now,
                    onMonthTap: (month) => _openMonth(year, month),
                  );
                },
              ),
            ),
          ],
        ),
      ),
    );
  }
}

/// A full-month calendar built to be embedded as a home tab. Used as the staff
/// dashboard for workspaces configured with `dashboardMode == 'calendar'`.
///
/// Unlike [CalendarScreen] (which opens as its own route with a back button),
/// this shows a hamburger header so it can live inside the bottom-nav shell,
/// and it highlights every day that has an event so they stand out at a glance.
class CalendarDashboardTab extends StatefulWidget {
  const CalendarDashboardTab({
    super.key,
    required this.session,
    required this.onMenu,
  });

  final StaffSession session;
  final VoidCallback onMenu;

  @override
  State<CalendarDashboardTab> createState() => _CalendarDashboardTabState();
}

class _CalendarDashboardTabState extends State<CalendarDashboardTab> {
  final StaffApi _api = StaffApi();
  late int _year = DateTime.now().year;
  late int _month = DateTime.now().month;
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

  void _shiftMonth(int delta) {
    Haptics.light();
    final next = DateTime(_year, _month + delta, 1);
    setState(() {
      _year = next.year;
      _month = next.month;
    });
  }

  void _goToday() {
    Haptics.light();
    final now = DateTime.now();
    setState(() {
      _year = now.year;
      _month = now.month;
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
        title: const Text(
          'Delete event?',
          style: TextStyle(
            fontWeight: FontWeight.w900,
            color: AppTheme.textPrimary,
          ),
        ),
        content: Text(
          '"${event.title}" will be permanently removed.',
          style: const TextStyle(color: AppTheme.textSecondary),
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
      Navigator.of(context).maybePop(); // close the day sheet
      _reload();
    } on ApiException catch (e) {
      if (!mounted) return;
      AppToast.error(context, e.message);
    }
  }

  void _openDay(DateTime day, List<CalendarEvent> events) {
    Haptics.light();
    final dayEvents = events.where((e) => _eventCoversDay(e, day)).toList()
      ..sort((a, b) => a.start.compareTo(b.start));
    showModalBottomSheet<void>(
      context: context,
      backgroundColor: AppTheme.surface,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
      ),
      builder: (sheetContext) => _DaySheet(
        day: day,
        events: dayEvents,
        onAdd: () {
          Navigator.of(sheetContext).pop();
          _openCreate();
        },
        onEdit: (e) {
          Navigator.of(sheetContext).pop();
          _openEdit(e);
        },
        onDelete: (e) => _confirmDelete(e),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final now = DateTime.now();
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
            final events = snapshot.data ?? const <CalendarEvent>[];
            return Column(
              crossAxisAlignment: CrossAxisAlignment.stretch,
              children: [
                Padding(
                  padding: const EdgeInsets.fromLTRB(16, 12, 16, 4),
                  child: MobileHeader(
                    title: 'Calendar',
                    subtitle: '${_monthNamesFull[_month - 1]} $_year',
                    leadingIcon: PhosphorIconsBold.list,
                    onLeadingTap: () {
                      Haptics.light();
                      widget.onMenu();
                    },
                    trailingIcon: PhosphorIconsBold.arrowClockwise,
                    onTrailingTap: () {
                      Haptics.light();
                      _reload();
                    },
                  ),
                ),
                _DashboardMonthBar(
                  title: '${_monthNamesFull[_month - 1]} $_year',
                  onPrev: () => _shiftMonth(-1),
                  onNext: () => _shiftMonth(1),
                  onToday: _goToday,
                ),
                const _WeekdayHeader(),
                Expanded(
                  child: _MonthGrid(
                    year: _year,
                    month: _month,
                    today: now,
                    events: events,
                    onDayTap: (day) => _openDay(day, events),
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

/// Month navigation row for [CalendarDashboardTab]: previous / next arrows, the
/// current month title, and a quick "Today" jump.
class _DashboardMonthBar extends StatelessWidget {
  const _DashboardMonthBar({
    required this.title,
    required this.onPrev,
    required this.onNext,
    required this.onToday,
  });

  final String title;
  final VoidCallback onPrev;
  final VoidCallback onNext;
  final VoidCallback onToday;

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.fromLTRB(12, 4, 8, 6),
      child: Row(
        children: [
          IconButton(
            onPressed: onPrev,
            icon: const Icon(
              PhosphorIconsBold.caretLeft,
              color: AppTheme.textSecondary,
              size: 18,
            ),
          ),
          Expanded(
            child: Text(
              title,
              textAlign: TextAlign.center,
              style: const TextStyle(
                fontWeight: FontWeight.w900,
                fontSize: 17,
                letterSpacing: -0.3,
                color: AppTheme.textPrimary,
              ),
            ),
          ),
          IconButton(
            onPressed: onNext,
            icon: const Icon(
              PhosphorIconsBold.caretRight,
              color: AppTheme.textSecondary,
              size: 18,
            ),
          ),
          TextButton(
            onPressed: onToday,
            style: TextButton.styleFrom(
              foregroundColor: kAppleRed,
              padding: const EdgeInsets.symmetric(horizontal: 10),
              minimumSize: const Size(0, 36),
            ),
            child: const Text(
              'Today',
              style: TextStyle(fontWeight: FontWeight.w800, fontSize: 13.5),
            ),
          ),
        ],
      ),
    );
  }
}

class _TopBar extends StatelessWidget {
  const _TopBar({required this.onBack});
  final VoidCallback onBack;

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.fromLTRB(8, 8, 16, 4),
      child: Row(
        children: [
          IconButton(
            onPressed: onBack,
            icon: const Icon(
              PhosphorIconsBold.caretLeft,
              color: AppTheme.textPrimary,
              size: 20,
            ),
          ),
          const Text(
            'Calendar',
            style: TextStyle(
              fontWeight: FontWeight.w900,
              fontSize: 20,
              letterSpacing: -0.3,
              color: AppTheme.textPrimary,
            ),
          ),
        ],
      ),
    );
  }
}

class _YearBlock extends StatelessWidget {
  const _YearBlock({
    required this.year,
    required this.today,
    required this.onMonthTap,
  });

  final int year;
  final DateTime today;
  final ValueChanged<int> onMonthTap;

  @override
  Widget build(BuildContext context) {
    final isCurrentYear = year == today.year;
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Padding(
          padding: const EdgeInsets.only(top: 18, bottom: 6),
          child: Text(
            '$year',
            style: TextStyle(
              fontSize: 30,
              fontWeight: FontWeight.w800,
              letterSpacing: -0.5,
              color: isCurrentYear ? kAppleRed : AppTheme.textPrimary,
            ),
          ),
        ),
        const Divider(height: 1, color: AppTheme.border),
        const SizedBox(height: 6),
        for (var row = 0; row < 4; row++)
          Padding(
            padding: const EdgeInsets.only(bottom: 14),
            child: Row(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                for (var col = 0; col < 3; col++)
                  Expanded(
                    child: Padding(
                      padding: EdgeInsets.only(right: col < 2 ? 10 : 0),
                      child: _MiniMonth(
                        year: year,
                        month: row * 3 + col + 1,
                        today: today,
                        onTap: onMonthTap,
                      ),
                    ),
                  ),
              ],
            ),
          ),
      ],
    );
  }
}

class _MiniMonth extends StatelessWidget {
  const _MiniMonth({
    required this.year,
    required this.month,
    required this.today,
    required this.onTap,
  });

  final int year;
  final int month;
  final DateTime today;
  final ValueChanged<int> onTap;

  @override
  Widget build(BuildContext context) {
    final weeks = _monthWeeks(year, month);
    final isCurrentMonth = today.year == year && today.month == month;

    return GestureDetector(
      behavior: HitTestBehavior.opaque,
      onTap: () => onTap(month),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Padding(
            padding: const EdgeInsets.only(bottom: 4, left: 2),
            child: Text(
              _monthNamesShort[month - 1],
              style: TextStyle(
                fontSize: 15,
                fontWeight: FontWeight.w800,
                color: isCurrentMonth ? kAppleRed : AppTheme.textPrimary,
              ),
            ),
          ),
          for (final week in weeks)
            SizedBox(
              height: 15,
              child: Row(
                children: [
                  for (final day in week)
                    Expanded(
                      child: _MiniDayCell(
                        day: day,
                        isToday:
                            day != 0 &&
                            today.year == year &&
                            today.month == month &&
                            today.day == day,
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

class _MiniDayCell extends StatelessWidget {
  const _MiniDayCell({required this.day, required this.isToday});
  final int day;
  final bool isToday;

  @override
  Widget build(BuildContext context) {
    if (day == 0) return const SizedBox.shrink();
    final text = Text(
      '$day',
      textAlign: TextAlign.center,
      style: TextStyle(
        fontSize: 9.5,
        height: 1.0,
        fontWeight: isToday ? FontWeight.w800 : FontWeight.w500,
        color: isToday ? Colors.white : AppTheme.textPrimary,
      ),
    );
    if (!isToday) return Center(child: text);
    return Center(
      child: Container(
        width: 14,
        height: 14,
        alignment: Alignment.center,
        decoration: BoxDecoration(
          color: kAppleRed,
          borderRadius: BorderRadius.circular(4),
        ),
        child: text,
      ),
    );
  }
}

/// Full month view opened from the year grid — shows event dots and lets you
/// tap a day to see / manage that day's events.
class _MonthDetailScreen extends StatefulWidget {
  const _MonthDetailScreen({
    required this.session,
    required this.year,
    required this.month,
  });

  final StaffSession session;
  final int year;
  final int month;

  @override
  State<_MonthDetailScreen> createState() => _MonthDetailScreenState();
}

class _MonthDetailScreenState extends State<_MonthDetailScreen> {
  final StaffApi _api = StaffApi();
  late int _year = widget.year;
  late int _month = widget.month;
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

  void _shiftMonth(int delta) {
    Haptics.light();
    final next = DateTime(_year, _month + delta, 1);
    setState(() {
      _year = next.year;
      _month = next.month;
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
        title: const Text(
          'Delete event?',
          style: TextStyle(
            fontWeight: FontWeight.w900,
            color: AppTheme.textPrimary,
          ),
        ),
        content: Text(
          '"${event.title}" will be permanently removed.',
          style: const TextStyle(color: AppTheme.textSecondary),
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
      Navigator.of(context).maybePop(); // close the day sheet
      _reload();
    } on ApiException catch (e) {
      if (!mounted) return;
      AppToast.error(context, e.message);
    }
  }

  void _openDay(DateTime day, List<CalendarEvent> events) {
    Haptics.light();
    final dayEvents = events.where((e) => _eventCoversDay(e, day)).toList()
      ..sort((a, b) => a.start.compareTo(b.start));
    showModalBottomSheet<void>(
      context: context,
      backgroundColor: AppTheme.surface,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
      ),
      builder: (sheetContext) => _DaySheet(
        day: day,
        events: dayEvents,
        onAdd: () {
          Navigator.of(sheetContext).pop();
          _openCreate();
        },
        onEdit: (e) {
          Navigator.of(sheetContext).pop();
          _openEdit(e);
        },
        onDelete: (e) => _confirmDelete(e),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final now = DateTime.now();
    return Scaffold(
      backgroundColor: AppTheme.background,
      body: SafeArea(
        bottom: false,
        child: FutureBuilder<List<CalendarEvent>>(
          future: _future,
          builder: (context, snapshot) {
            final events = snapshot.data ?? const <CalendarEvent>[];
            return Column(
              crossAxisAlignment: CrossAxisAlignment.stretch,
              children: [
                _MonthHeader(
                  title: '${_monthNamesFull[_month - 1]} $_year',
                  onBack: () {
                    Haptics.light();
                    Navigator.of(context).maybePop();
                  },
                  onPrev: () => _shiftMonth(-1),
                  onNext: () => _shiftMonth(1),
                ),
                const _WeekdayHeader(),
                Expanded(
                  child: _MonthGrid(
                    year: _year,
                    month: _month,
                    today: now,
                    events: events,
                    onDayTap: (day) => _openDay(day, events),
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

class _MonthHeader extends StatelessWidget {
  const _MonthHeader({
    required this.title,
    required this.onBack,
    required this.onPrev,
    required this.onNext,
  });

  final String title;
  final VoidCallback onBack;
  final VoidCallback onPrev;
  final VoidCallback onNext;

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.fromLTRB(8, 8, 8, 6),
      child: Row(
        children: [
          IconButton(
            onPressed: onBack,
            icon: const Icon(
              PhosphorIconsBold.caretLeft,
              color: kAppleRed,
              size: 20,
            ),
          ),
          Expanded(
            child: Text(
              title,
              style: const TextStyle(
                fontWeight: FontWeight.w900,
                fontSize: 19,
                letterSpacing: -0.3,
                color: AppTheme.textPrimary,
              ),
            ),
          ),
          IconButton(
            onPressed: onPrev,
            icon: const Icon(
              PhosphorIconsBold.caretLeft,
              color: AppTheme.textSecondary,
              size: 18,
            ),
          ),
          IconButton(
            onPressed: onNext,
            icon: const Icon(
              PhosphorIconsBold.caretRight,
              color: AppTheme.textSecondary,
              size: 18,
            ),
          ),
        ],
      ),
    );
  }
}

class _WeekdayHeader extends StatelessWidget {
  const _WeekdayHeader();

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 12),
      child: Row(
        children: [
          for (var i = 0; i < 7; i++)
            Expanded(
              child: Padding(
                padding: const EdgeInsets.only(bottom: 6),
                child: Text(
                  _weekdayLetters[i],
                  textAlign: TextAlign.center,
                  style: TextStyle(
                    fontSize: 12,
                    fontWeight: FontWeight.w800,
                    color: (i == 0 || i == 6)
                        ? AppTheme.textMuted
                        : AppTheme.textSecondary,
                  ),
                ),
              ),
            ),
        ],
      ),
    );
  }
}

class _MonthGrid extends StatelessWidget {
  const _MonthGrid({
    required this.year,
    required this.month,
    required this.today,
    required this.events,
    required this.onDayTap,
  });

  final int year;
  final int month;
  final DateTime today;
  final List<CalendarEvent> events;
  final ValueChanged<DateTime> onDayTap;

  @override
  Widget build(BuildContext context) {
    final weeks = _monthWeeks(year, month);
    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 12),
      child: Column(
        children: [
          for (final week in weeks)
            Expanded(
              child: Row(
                children: [
                  for (final day in week)
                    Expanded(
                      child: day == 0
                          ? const SizedBox.shrink()
                          : _MonthDayCell(
                              date: DateTime(year, month, day),
                              isToday:
                                  today.year == year &&
                                  today.month == month &&
                                  today.day == day,
                              dotColors: _dotsFor(DateTime(year, month, day)),
                              hasEvents: _hasEvents(DateTime(year, month, day)),
                              onTap: onDayTap,
                            ),
                    ),
                ],
              ),
            ),
        ],
      ),
    );
  }

  List<Color> _dotsFor(DateTime day) {
    final matches = events.where((e) => _eventCoversDay(e, day)).toList();
    return [for (final e in matches.take(3)) _eventColor(e)];
  }

  bool _hasEvents(DateTime day) => events.any((e) => _eventCoversDay(e, day));
}

class _MonthDayCell extends StatelessWidget {
  const _MonthDayCell({
    required this.date,
    required this.isToday,
    required this.dotColors,
    required this.hasEvents,
    required this.onTap,
  });

  final DateTime date;
  final bool isToday;
  final List<Color> dotColors;
  final bool hasEvents;
  final ValueChanged<DateTime> onTap;

  @override
  Widget build(BuildContext context) {
    // Days with events get a soft red highlight so they're easy to spot at a
    // glance; "today" keeps its solid red marker and takes precedence.
    final Color background = isToday
        ? kAppleRed
        : (hasEvents ? kAppleRed.withValues(alpha: 0.14) : Colors.transparent);
    final Color textColor = isToday
        ? Colors.white
        : (hasEvents ? kAppleRed : AppTheme.textPrimary);

    return InkWell(
      borderRadius: BorderRadius.circular(12),
      onTap: () => onTap(date),
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Container(
            width: 34,
            height: 34,
            alignment: Alignment.center,
            decoration: BoxDecoration(
              color: background,
              borderRadius: BorderRadius.circular(10),
            ),
            child: Text(
              '${date.day}',
              style: TextStyle(
                fontSize: 15,
                fontWeight: (isToday || hasEvents)
                    ? FontWeight.w800
                    : FontWeight.w600,
                color: textColor,
              ),
            ),
          ),
          const SizedBox(height: 3),
          SizedBox(
            height: 6,
            child: Row(
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                for (final color in dotColors)
                  Container(
                    width: 5,
                    height: 5,
                    margin: const EdgeInsets.symmetric(horizontal: 1),
                    decoration: BoxDecoration(
                      color: color,
                      shape: BoxShape.circle,
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

class _DaySheet extends StatelessWidget {
  const _DaySheet({
    required this.day,
    required this.events,
    required this.onAdd,
    required this.onEdit,
    required this.onDelete,
  });

  final DateTime day;
  final List<CalendarEvent> events;
  final VoidCallback onAdd;
  final ValueChanged<CalendarEvent> onEdit;
  final ValueChanged<CalendarEvent> onDelete;

  static const List<String> _weekdaysFull = [
    'Monday',
    'Tuesday',
    'Wednesday',
    'Thursday',
    'Friday',
    'Saturday',
    'Sunday',
  ];

  @override
  Widget build(BuildContext context) {
    final weekday = _weekdaysFull[day.weekday - 1];
    return SafeArea(
      top: false,
      child: Padding(
        padding: const EdgeInsets.fromLTRB(20, 14, 20, 18),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Center(
              child: Container(
                width: 40,
                height: 4,
                decoration: BoxDecoration(
                  color: AppTheme.border,
                  borderRadius: BorderRadius.circular(99),
                ),
              ),
            ),
            const SizedBox(height: 16),
            Text(
              weekday.toUpperCase(),
              style: const TextStyle(
                fontSize: 11,
                fontWeight: FontWeight.w900,
                letterSpacing: 1.4,
                color: kAppleRed,
              ),
            ),
            const SizedBox(height: 2),
            Text(
              '${_monthNamesFull[day.month - 1]} ${day.day}, ${day.year}',
              style: const TextStyle(
                fontSize: 22,
                fontWeight: FontWeight.w900,
                letterSpacing: -0.4,
                color: AppTheme.textPrimary,
              ),
            ),
            const SizedBox(height: 14),
            if (events.isEmpty)
              Padding(
                padding: const EdgeInsets.symmetric(vertical: 18),
                child: Row(
                  children: [
                    const Icon(
                      PhosphorIconsBold.calendarBlank,
                      size: 18,
                      color: AppTheme.textMuted,
                    ),
                    const SizedBox(width: 10),
                    Text(
                      'No events on this day',
                      style: TextStyle(
                        color: AppTheme.textSecondary,
                        fontSize: 14,
                        fontWeight: FontWeight.w600,
                      ),
                    ),
                  ],
                ),
              )
            else
              ConstrainedBox(
                constraints: BoxConstraints(
                  maxHeight: MediaQuery.of(context).size.height * 0.45,
                ),
                child: ListView.separated(
                  shrinkWrap: true,
                  itemCount: events.length,
                  separatorBuilder: (_, _) => const SizedBox(height: 8),
                  itemBuilder: (_, i) => _DayEventRow(
                    event: events[i],
                    onTap: () => onEdit(events[i]),
                    onDelete: events[i].canDelete
                        ? () => onDelete(events[i])
                        : null,
                  ),
                ),
              ),
            const SizedBox(height: 14),
            SizedBox(
              width: double.infinity,
              child: FilledButton.icon(
                style: FilledButton.styleFrom(
                  backgroundColor: AppTheme.primaryDark,
                ),
                onPressed: onAdd,
                icon: const Icon(PhosphorIconsBold.plus, size: 16),
                label: const Text('Add event'),
              ),
            ),
          ],
        ),
      ),
    );
  }
}

class _DayEventRow extends StatelessWidget {
  const _DayEventRow({
    required this.event,
    required this.onTap,
    required this.onDelete,
  });

  final CalendarEvent event;
  final VoidCallback onTap;
  final VoidCallback? onDelete;

  @override
  Widget build(BuildContext context) {
    final color = _eventColor(event);
    return Material(
      color: AppTheme.surfaceMuted,
      borderRadius: BorderRadius.circular(14),
      child: InkWell(
        borderRadius: BorderRadius.circular(14),
        onTap: onTap,
        child: Padding(
          padding: const EdgeInsets.all(12),
          child: Row(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Container(
                width: 10,
                height: 10,
                margin: const EdgeInsets.only(top: 4),
                decoration: BoxDecoration(color: color, shape: BoxShape.circle),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      event.title,
                      style: const TextStyle(
                        fontWeight: FontWeight.w800,
                        fontSize: 14.5,
                        color: AppTheme.textPrimary,
                      ),
                    ),
                    const SizedBox(height: 3),
                    Text(
                      _timeLabel(event),
                      style: const TextStyle(
                        color: AppTheme.textSecondary,
                        fontSize: 12.5,
                        fontWeight: FontWeight.w600,
                      ),
                    ),
                    if (event.location.isNotEmpty) ...[
                      const SizedBox(height: 2),
                      Row(
                        children: [
                          const Icon(
                            PhosphorIconsBold.mapPin,
                            size: 12,
                            color: AppTheme.textMuted,
                          ),
                          const SizedBox(width: 4),
                          Expanded(
                            child: Text(
                              event.location,
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
                  ],
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
                    color: color.withValues(alpha: 0.12),
                    borderRadius: BorderRadius.circular(999),
                  ),
                  child: Text(
                    'PUBLIC',
                    style: TextStyle(
                      color: color,
                      fontSize: 9,
                      fontWeight: FontWeight.w900,
                      letterSpacing: 1.0,
                    ),
                  ),
                ),
              if (onDelete != null)
                IconButton(
                  visualDensity: VisualDensity.compact,
                  padding: EdgeInsets.zero,
                  constraints: const BoxConstraints(),
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

  String _timeLabel(CalendarEvent e) {
    if (e.allDay) return 'All day';
    final sameDay = _sameDay(e.start, e.end);
    if (sameDay) {
      return '${_formatTime(e.start)} – ${_formatTime(e.end)}';
    }
    return '${_monthNamesShort[e.start.month - 1]} ${e.start.day}, '
        '${_formatTime(e.start)} → ${_monthNamesShort[e.end.month - 1]} '
        '${e.end.day}, ${_formatTime(e.end)}';
  }

  String _formatTime(DateTime dt) {
    final h = dt.hour == 0 ? 12 : (dt.hour > 12 ? dt.hour - 12 : dt.hour);
    final m = dt.minute.toString().padLeft(2, '0');
    final ampm = dt.hour >= 12 ? 'PM' : 'AM';
    return '$h:$m $ampm';
  }
}
