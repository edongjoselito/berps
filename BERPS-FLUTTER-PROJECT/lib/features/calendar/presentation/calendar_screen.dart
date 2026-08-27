import 'package:flutter/material.dart';
import 'package:flutter_html/flutter_html.dart';
import 'package:lucide_icons_flutter/lucide_icons.dart';
import 'package:url_launcher/url_launcher.dart';

import '../../../core/network/api_exception.dart';
import '../../../core/theme/app_theme.dart';
import '../../../core/utils/haptics.dart';
import '../../../core/widgets/animations.dart';
import '../../../core/widgets/app_toast.dart';
import '../../../core/widgets/mobile_header.dart';
import '../../auth/domain/staff_session.dart';
import '../../home/data/staff_api.dart';
import '../../notes/data/notes_api.dart';
import '../../notes/domain/note.dart';
import '../../reminders/data/reminders_api.dart';
import '../../reminders/domain/reminder.dart';
import '../domain/calendar_event.dart';
import 'calendar_day_note_editor.dart';
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

bool _sameDay(DateTime a, DateTime b) =>
    a.year == b.year && a.month == b.month && a.day == b.day;

/// A unified calendar entry — an event, a note, or a reminder — used by the
/// calendar month/week/day views so the calendar can show all three entity
/// types on the timeline without involving tasks.
class CalendarDayItem {
  const CalendarDayItem({
    required this.date,
    required this.title,
    required this.color,
    required this.type,
    this.note,
    this.reminder,
    this.event,
    this.endDate,
  });

  final DateTime date;
  final String title;
  final Color color;
  final CalendarDayItemType type;
  final Note? note;
  final Reminder? reminder;
  final CalendarEvent? event;
  final DateTime? endDate;

  bool get isNote => type == CalendarDayItemType.note;
  bool get isReminder => type == CalendarDayItemType.reminder;
  bool get isEvent => type == CalendarDayItemType.event;
}

enum CalendarDayItemType { note, reminder, event }

/// Parses a "yyyy-MM-dd" or "yyyy-MM-dd HH:mm:ss" string into a DateTime.
DateTime _parseDate(String s) {
  final cleaned = s.trim().replaceFirst(' ', 'T');
  return DateTime.tryParse(cleaned) ?? DateTime(1970);
}

/// Builds calendar entries from events, notes and reminders for the grid.
List<CalendarDayItem> _buildDayItems({
  required List<CalendarEvent> events,
  required List<Note> notes,
  required List<Reminder> reminders,
}) {
  final items = <CalendarDayItem>[];

  for (final event in events) {
    final start = event.start;
    final end = event.end;
    items.add(CalendarDayItem(
      date: start,
      title: event.title,
      color: _parseHexColor(event.color),
      type: CalendarDayItemType.event,
      event: event,
      endDate: end,
    ));
  }

  for (final note in notes) {
    final date = _parseDate(note.date);
    if (date.year != 1970) {
      items.add(CalendarDayItem(
        date: date,
        title: note.displayTitle,
        color: const Color(0xFF007AFF), // blue for notes
        type: CalendarDayItemType.note,
        note: note,
      ));
    }
  }

  for (final reminder in reminders) {
    final date = _parseDate(reminder.remindAt);
    if (date.year != 1970) {
      items.add(CalendarDayItem(
        date: date,
        title: reminder.title,
        color: const Color(0xFFFF9500), // orange for reminders
        type: CalendarDayItemType.reminder,
        reminder: reminder,
      ));
    }
  }

  return items;
}

Color _parseHexColor(String hex) {
  var h = hex.trim();
  if (h.startsWith('#')) h = h.substring(1);
  if (h.length == 6) h = 'FF$h';
  return Color(int.tryParse(h, radix: 16) ?? 0xFF3788D8);
}

bool _itemCoversDay(CalendarDayItem item, DateTime day) {
  if (item.isEvent && item.endDate != null) {
    // Multi-day events: check if day is between start and end (inclusive)
    final start = DateTime(item.date.year, item.date.month, item.date.day);
    final end = DateTime(item.endDate!.year, item.endDate!.month, item.endDate!.day);
    final d = DateTime(day.year, day.month, day.day);
    return !d.isBefore(start) && !d.isAfter(end);
  }
  return _sameDay(item.date, day);
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
        builder: (_) => CalendarDayNoteEditor(
          session: widget.session,
          date: DateTime.now(),
        ),
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
        icon: const Icon(LucideIcons.plus, size: 18),
        label: const Text(
          'New note',
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
  final NotesApi _notesApi = NotesApi();
  final RemindersApi _remindersApi = RemindersApi();
  final StaffApi _staffApi = StaffApi();
  late int _year = DateTime.now().year;
  late int _month = DateTime.now().month;
  _CalendarView _view = _CalendarView.month;
  DateTime _selectedDay = DateTime.now();
  Future<List<CalendarDayItem>>? _future;

  @override
  void initState() {
    super.initState();
    _reload();
  }

  String _monthStart() {
    final d = DateTime(_year, _month, 1);
    return '${d.year.toString().padLeft(4, '0')}-${d.month.toString().padLeft(2, '0')}-${d.day.toString().padLeft(2, '0')}';
  }

  String _monthEnd() {
    final d = DateTime(_year, _month + 1, 0); // last day of month
    return '${d.year.toString().padLeft(4, '0')}-${d.month.toString().padLeft(2, '0')}-${d.day.toString().padLeft(2, '0')}';
  }

  void _reload() {
    setState(() {
      _future = _fetchItems();
    });
  }

  Future<List<CalendarDayItem>> _fetchItems() async {
    final from = _monthStart();
    final to = _monthEnd();
    final results = await Future.wait([
      _staffApi.fetchCalendarEvents(
        baseUrl: widget.session.baseUrl,
        token: widget.session.token,
        from: from,
        to: to,
      ),
      _notesApi.fetchNotesByDateRange(
        baseUrl: widget.session.baseUrl,
        token: widget.session.token,
        from: from,
        to: to,
      ),
      _remindersApi.fetchRemindersByDateRange(
        baseUrl: widget.session.baseUrl,
        token: widget.session.token,
        from: from,
        to: to,
      ),
    ]);
    return _buildDayItems(
      events: results[0] as List<CalendarEvent>,
      notes: results[1] as List<Note>,
      reminders: results[2] as List<Reminder>,
    );
  }

  void _shiftPeriod(int delta) {
    Haptics.light();
    if (_view == _CalendarView.month) {
      final next = DateTime(_year, _month + delta, 1);
      setState(() {
        _year = next.year;
        _month = next.month;
      });
    } else {
      // Week or Day view — shift by weeks or days
      final shiftDays = _view == _CalendarView.week ? 7 * delta : delta;
      final next = DateTime(_year, _month, _selectedDay.day + shiftDays);
      setState(() {
        _year = next.year;
        _month = next.month;
        _selectedDay = next;
      });
    }
    _reload();
  }

  void _goToday() {
    Haptics.light();
    final now = DateTime.now();
    setState(() {
      _year = now.year;
      _month = now.month;
      _selectedDay = now;
    });
    _reload();
  }

  void _selectDay(DateTime day) {
    Haptics.light();
    setState(() {
      _selectedDay = day;
      _year = day.year;
      _month = day.month;
    });
  }

  void _setView(_CalendarView view) {
    Haptics.light();
    setState(() => _view = view);
  }

  Future<void> _openCreateNote() async {
    Haptics.light();
    final saved = await Navigator.of(context).push<bool>(
      MaterialPageRoute(
        builder: (_) => CalendarDayNoteEditor(
          session: widget.session,
          date: _view == _CalendarView.day ? _selectedDay : DateTime(_year, _month, DateTime.now().day),
        ),
      ),
    );
    if (saved == true) _reload();
  }

  Future<void> _openCreateEvent() async {
    Haptics.light();
    final saved = await Navigator.of(context).push<bool>(
      MaterialPageRoute(
        builder: (_) => CalendarEventEditor(
          session: widget.session,
          initialDate: _view == _CalendarView.day ? _selectedDay : DateTime(_year, _month, DateTime.now().day),
        ),
      ),
    );
    if (saved == true) _reload();
  }

  Future<void> _openCreateReminder() async {
    Haptics.light();
    // Reuse the reminders screen editor via a bottom sheet
    final saved = await Navigator.of(context).push<bool>(
      MaterialPageRoute(
        builder: (_) => _ReminderQuickEditor(
          session: widget.session,
          date: _view == _CalendarView.day ? _selectedDay : DateTime(_year, _month, DateTime.now().day),
        ),
      ),
    );
    if (saved == true) _reload();
  }

  Future<void> _openEditNote(Note note) async {
    Haptics.light();
    final saved = await Navigator.of(context).push<bool>(
      MaterialPageRoute(
        builder: (_) =>
            CalendarDayNoteEditor(
              session: widget.session,
              date: _parseDate(note.date),
              existing: note,
            ),
      ),
    );
    if (saved == true) _reload();
  }

  Future<void> _openEditEvent(CalendarEvent event) async {
    Haptics.light();
    final saved = await Navigator.of(context).push<bool>(
      MaterialPageRoute(
        builder: (_) => CalendarEventEditor(
          session: widget.session,
          existing: event,
        ),
      ),
    );
    if (saved == true) _reload();
  }

  Future<void> _openEditReminder(Reminder reminder) async {
    Haptics.light();
    final saved = await Navigator.of(context).push<bool>(
      MaterialPageRoute(
        builder: (_) => _ReminderQuickEditor(
          session: widget.session,
          date: _parseDate(reminder.remindAt),
          existing: reminder,
        ),
      ),
    );
    if (saved == true) _reload();
  }

  Future<void> _confirmDeleteNote(Note note) async {
    Haptics.warn();
    final confirmed = await showDialog<bool>(
      context: context,
      builder: (dialogContext) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
        title: const Text(
          'Delete note?',
          style: TextStyle(
            fontWeight: FontWeight.w900,
            color: AppTheme.textPrimary,
          ),
        ),
        content: Text(
          '"${note.displayTitle}" will be permanently removed.',
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
      await _notesApi.deleteNote(
        baseUrl: widget.session.baseUrl,
        token: widget.session.token,
        noteId: note.id,
      );
      if (!mounted) return;
      AppToast.success(context, 'Note deleted.');
      Navigator.of(context).maybePop(); // close the day sheet
      _reload();
    } on ApiException catch (e) {
      if (!mounted) return;
      AppToast.error(context, e.message);
    }
  }

  Future<void> _confirmDeleteEvent(CalendarEvent event) async {
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
      await _staffApi.deleteCalendarEvent(
        baseUrl: widget.session.baseUrl,
        token: widget.session.token,
        id: event.id,
      );
      if (!mounted) return;
      AppToast.success(context, 'Event deleted.');
      Navigator.of(context).maybePop();
      _reload();
    } on ApiException catch (e) {
      if (!mounted) return;
      AppToast.error(context, e.message);
    }
  }

  Future<void> _confirmDeleteReminder(Reminder reminder) async {
    Haptics.warn();
    final confirmed = await showDialog<bool>(
      context: context,
      builder: (dialogContext) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
        title: const Text(
          'Delete reminder?',
          style: TextStyle(
            fontWeight: FontWeight.w900,
            color: AppTheme.textPrimary,
          ),
        ),
        content: Text(
          '"${reminder.title}" will be permanently removed.',
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
      await _remindersApi.deleteReminder(
        baseUrl: widget.session.baseUrl,
        token: widget.session.token,
        reminderId: reminder.id,
      );
      if (!mounted) return;
      AppToast.success(context, 'Reminder deleted.');
      Navigator.of(context).maybePop();
      _reload();
    } on ApiException catch (e) {
      if (!mounted) return;
      AppToast.error(context, e.message);
    }
  }

  void _openDay(DateTime day, List<CalendarDayItem> items) {
    Haptics.light();
    final dayItems = items.where((e) => _itemCoversDay(e, day)).toList()
      ..sort((a, b) => a.date.compareTo(b.date));
    showModalBottomSheet<void>(
      context: context,
      backgroundColor: AppTheme.surface,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
      ),
      builder: (sheetContext) => _DaySheet(
        session: widget.session,
        day: day,
        items: dayItems,
        onAddNote: () {
          Navigator.of(sheetContext).pop();
          _openCreateNote();
        },
        onAddEvent: () {
          Navigator.of(sheetContext).pop();
          _openCreateEvent();
        },
        onAddReminder: () {
          Navigator.of(sheetContext).pop();
          _openCreateReminder();
        },
        onEditNote: (note) {
          Navigator.of(sheetContext).pop();
          _openEditNote(note);
        },
        onEditEvent: (event) {
          Navigator.of(sheetContext).pop();
          _openEditEvent(event);
        },
        onEditReminder: (reminder) {
          Navigator.of(sheetContext).pop();
          _openEditReminder(reminder);
        },
        onDeleteNote: (note) => _confirmDeleteNote(note),
        onDeleteEvent: (event) => _confirmDeleteEvent(event),
        onDeleteReminder: (reminder) => _confirmDeleteReminder(reminder),
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
        onPressed: _openCreateNote,
        icon: const Icon(LucideIcons.plus, size: 18),
        label: const Text(
          'New note',
          style: TextStyle(fontWeight: FontWeight.w800),
        ),
      ),
      body: SafeArea(
        bottom: false,
        child: FutureBuilder<List<CalendarDayItem>>(
          future: _future,
          builder: (context, snapshot) {
            final items = snapshot.data ?? const <CalendarDayItem>[];
            final title = _view == _CalendarView.month
                ? '${_monthNamesFull[_month - 1]} $_year'
                : _view == _CalendarView.week
                    ? 'Week of ${_monthNamesShort[_weekStart(_selectedDay).month - 1]} ${_weekStart(_selectedDay).day}'
                    : '${_weekdayFull(_selectedDay.weekday)}, ${_monthNamesShort[_selectedDay.month - 1]} ${_selectedDay.day}';
            return Column(
              crossAxisAlignment: CrossAxisAlignment.stretch,
              children: [
                Padding(
                  padding: const EdgeInsets.fromLTRB(16, 12, 16, 4),
                  child: MobileHeader(
                    title: 'Calendar',
                    subtitle: title,
                    leadingIcon: LucideIcons.list,
                    onLeadingTap: () {
                      Haptics.light();
                      widget.onMenu();
                    },
                    trailingIcon: LucideIcons.rotateCw,
                    onTrailingTap: () {
                      Haptics.light();
                      _reload();
                    },
                  ),
                ),
                _ViewSwitcher(
                  current: _view,
                  onChanged: _setView,
                ),
                _DashboardPeriodBar(
                  title: title,
                  onPrev: () => _shiftPeriod(-1),
                  onNext: () => _shiftPeriod(1),
                  onToday: _goToday,
                ),
                if (_view == _CalendarView.month) ...[
                  const _WeekdayHeader(),
                  Expanded(
                    child: _MonthGrid(
                      year: _year,
                      month: _month,
                      today: now,
                      items: items,
                      onDayTap: (day) {
                        _selectDay(day);
                        _openDay(day, items);
                      },
                    ),
                  ),
                ],
                if (_view == _CalendarView.week)
                  Expanded(
                    child: _WeekView(
                      weekStart: _weekStart(_selectedDay),
                      today: now,
                      items: items,
                      onDayTap: (day) {
                        _selectDay(day);
                        _openDay(day, items);
                      },
                    ),
                  ),
                if (_view == _CalendarView.day)
                  Expanded(
                    child: _DayView(
                      day: _selectedDay,
                      today: now,
                      items: items.where((e) => _itemCoversDay(e, _selectedDay)).toList(),
                      onAddEvent: _openCreateEvent,
                      onAddNote: _openCreateNote,
                      onAddReminder: _openCreateReminder,
                      onEditEvent: _openEditEvent,
                      onEditNote: _openEditNote,
                      onEditReminder: _openEditReminder,
                      onDeleteEvent: _confirmDeleteEvent,
                      onDeleteNote: _confirmDeleteNote,
                      onDeleteReminder: _confirmDeleteReminder,
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

enum _CalendarView { month, week, day }

DateTime _weekStart(DateTime day) {
  final d = DateTime(day.year, day.month, day.day);
  return d.subtract(Duration(days: d.weekday % 7)); // Sunday-first
}

String _weekdayFull(int weekday) {
  const names = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
  return names[weekday - 1];
}

String _formatTime(DateTime dt) {
  final h = dt.hour;
  final m = dt.minute;
  final ampm = h >= 12 ? 'PM' : 'AM';
  final h12 = h == 0 ? 12 : (h > 12 ? h - 12 : h);
  return '$h12:${m.toString().padLeft(2, '0')} $ampm';
}

/// Segmented control for switching between Month / Week / Day views.
class _ViewSwitcher extends StatelessWidget {
  const _ViewSwitcher({required this.current, required this.onChanged});
  final _CalendarView current;
  final ValueChanged<_CalendarView> onChanged;

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.fromLTRB(16, 4, 16, 6),
      child: Container(
        padding: const EdgeInsets.all(3),
        decoration: BoxDecoration(
          color: AppTheme.surfaceMuted,
          borderRadius: BorderRadius.circular(12),
        ),
        child: Row(
          children: [
            for (final v in _CalendarView.values)
              Expanded(
                child: GestureDetector(
                  onTap: () => onChanged(v),
                  child: AnimatedContainer(
                    duration: const Duration(milliseconds: 200),
                    padding: const EdgeInsets.symmetric(vertical: 8),
                    decoration: BoxDecoration(
                      color: current == v ? AppTheme.primaryDark : Colors.transparent,
                      borderRadius: BorderRadius.circular(9),
                    ),
                    child: Text(
                      v == _CalendarView.month ? 'Month' : v == _CalendarView.week ? 'Week' : 'Day',
                      textAlign: TextAlign.center,
                      style: TextStyle(
                        fontSize: 13,
                        fontWeight: FontWeight.w800,
                        color: current == v ? Colors.white : AppTheme.textSecondary,
                      ),
                    ),
                  ),
                ),
              ),
          ],
        ),
      ),
    );
  }
}

/// Period navigation row (replaces _DashboardMonthBar — works for all views).
class _DashboardPeriodBar extends StatelessWidget {
  const _DashboardPeriodBar({
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
              LucideIcons.chevronLeft,
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
              LucideIcons.chevronRight,
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
              LucideIcons.chevronLeft,
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
  final NotesApi _notesApi = NotesApi();
  final RemindersApi _remindersApi = RemindersApi();
  final StaffApi _staffApi = StaffApi();
  late int _year = widget.year;
  late int _month = widget.month;
  Future<List<CalendarDayItem>>? _future;

  @override
  void initState() {
    super.initState();
    _reload();
  }

  String _monthStart() {
    final d = DateTime(_year, _month, 1);
    return '${d.year.toString().padLeft(4, '0')}-${d.month.toString().padLeft(2, '0')}-${d.day.toString().padLeft(2, '0')}';
  }

  String _monthEnd() {
    final d = DateTime(_year, _month + 1, 0);
    return '${d.year.toString().padLeft(4, '0')}-${d.month.toString().padLeft(2, '0')}-${d.day.toString().padLeft(2, '0')}';
  }

  void _reload() {
    setState(() {
      _future = _fetchItems();
    });
  }

  Future<List<CalendarDayItem>> _fetchItems() async {
    final from = _monthStart();
    final to = _monthEnd();
    final results = await Future.wait([
      _staffApi.fetchCalendarEvents(
        baseUrl: widget.session.baseUrl,
        token: widget.session.token,
        from: from,
        to: to,
      ),
      _notesApi.fetchNotesByDateRange(
        baseUrl: widget.session.baseUrl,
        token: widget.session.token,
        from: from,
        to: to,
      ),
      _remindersApi.fetchRemindersByDateRange(
        baseUrl: widget.session.baseUrl,
        token: widget.session.token,
        from: from,
        to: to,
      ),
    ]);
    return _buildDayItems(
      events: results[0] as List<CalendarEvent>,
      notes: results[1] as List<Note>,
      reminders: results[2] as List<Reminder>,
    );
  }

  void _shiftMonth(int delta) {
    Haptics.light();
    final next = DateTime(_year, _month + delta, 1);
    setState(() {
      _year = next.year;
      _month = next.month;
    });
    _reload();
  }

  Future<void> _openCreateNote() async {
    Haptics.light();
    final saved = await Navigator.of(context).push<bool>(
      MaterialPageRoute(
        builder: (_) => CalendarDayNoteEditor(
          session: widget.session,
          date: DateTime(_year, _month, DateTime.now().day),
        ),
      ),
    );
    if (saved == true) _reload();
  }

  Future<void> _openCreateEvent() async {
    Haptics.light();
    final saved = await Navigator.of(context).push<bool>(
      MaterialPageRoute(
        builder: (_) => CalendarEventEditor(
          session: widget.session,
          initialDate: DateTime(_year, _month, DateTime.now().day),
        ),
      ),
    );
    if (saved == true) _reload();
  }

  Future<void> _openCreateReminder() async {
    Haptics.light();
    final saved = await Navigator.of(context).push<bool>(
      MaterialPageRoute(
        builder: (_) => _ReminderQuickEditor(
          session: widget.session,
          date: DateTime(_year, _month, DateTime.now().day),
        ),
      ),
    );
    if (saved == true) _reload();
  }

  Future<void> _openEditNote(Note note) async {
    Haptics.light();
    final saved = await Navigator.of(context).push<bool>(
      MaterialPageRoute(
        builder: (_) =>
            CalendarDayNoteEditor(
              session: widget.session,
              date: _parseDate(note.date),
              existing: note,
            ),
      ),
    );
    if (saved == true) _reload();
  }

  Future<void> _openEditEvent(CalendarEvent event) async {
    Haptics.light();
    final saved = await Navigator.of(context).push<bool>(
      MaterialPageRoute(
        builder: (_) => CalendarEventEditor(
          session: widget.session,
          existing: event,
        ),
      ),
    );
    if (saved == true) _reload();
  }

  Future<void> _openEditReminder(Reminder reminder) async {
    Haptics.light();
    final saved = await Navigator.of(context).push<bool>(
      MaterialPageRoute(
        builder: (_) => _ReminderQuickEditor(
          session: widget.session,
          date: _parseDate(reminder.remindAt),
          existing: reminder,
        ),
      ),
    );
    if (saved == true) _reload();
  }

  Future<void> _confirmDeleteNote(Note note) async {
    Haptics.warn();
    final confirmed = await showDialog<bool>(
      context: context,
      builder: (dialogContext) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
        title: const Text(
          'Delete note?',
          style: TextStyle(
            fontWeight: FontWeight.w900,
            color: AppTheme.textPrimary,
          ),
        ),
        content: Text(
          '"${note.displayTitle}" will be permanently removed.',
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
      await _notesApi.deleteNote(
        baseUrl: widget.session.baseUrl,
        token: widget.session.token,
        noteId: note.id,
      );
      if (!mounted) return;
      AppToast.success(context, 'Note deleted.');
      Navigator.of(context).maybePop();
      _reload();
    } on ApiException catch (e) {
      if (!mounted) return;
      AppToast.error(context, e.message);
    }
  }

  Future<void> _confirmDeleteEvent(CalendarEvent event) async {
    Haptics.warn();
    final confirmed = await showDialog<bool>(
      context: context,
      builder: (dialogContext) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
        title: const Text('Delete event?',
            style: TextStyle(fontWeight: FontWeight.w900, color: AppTheme.textPrimary)),
        content: Text('"${event.title}" will be permanently removed.',
            style: const TextStyle(color: AppTheme.textSecondary)),
        actions: [
          TextButton(onPressed: () => Navigator.of(dialogContext).pop(false), child: const Text('Cancel')),
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
      await _staffApi.deleteCalendarEvent(
        baseUrl: widget.session.baseUrl,
        token: widget.session.token,
        id: event.id,
      );
      if (!mounted) return;
      AppToast.success(context, 'Event deleted.');
      Navigator.of(context).maybePop();
      _reload();
    } on ApiException catch (e) {
      if (!mounted) return;
      AppToast.error(context, e.message);
    }
  }

  Future<void> _confirmDeleteReminder(Reminder reminder) async {
    Haptics.warn();
    final confirmed = await showDialog<bool>(
      context: context,
      builder: (dialogContext) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
        title: const Text('Delete reminder?',
            style: TextStyle(fontWeight: FontWeight.w900, color: AppTheme.textPrimary)),
        content: Text('"${reminder.title}" will be permanently removed.',
            style: const TextStyle(color: AppTheme.textSecondary)),
        actions: [
          TextButton(onPressed: () => Navigator.of(dialogContext).pop(false), child: const Text('Cancel')),
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
      await _remindersApi.deleteReminder(
        baseUrl: widget.session.baseUrl,
        token: widget.session.token,
        reminderId: reminder.id,
      );
      if (!mounted) return;
      AppToast.success(context, 'Reminder deleted.');
      Navigator.of(context).maybePop();
      _reload();
    } on ApiException catch (e) {
      if (!mounted) return;
      AppToast.error(context, e.message);
    }
  }

  void _openDay(DateTime day, List<CalendarDayItem> items) {
    Haptics.light();
    final dayItems = items.where((e) => _itemCoversDay(e, day)).toList()
      ..sort((a, b) => a.date.compareTo(b.date));
    showModalBottomSheet<void>(
      context: context,
      backgroundColor: AppTheme.surface,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
      ),
      builder: (sheetContext) => _DaySheet(
        session: widget.session,
        day: day,
        items: dayItems,
        onAddNote: () { Navigator.of(sheetContext).pop(); _openCreateNote(); },
        onAddEvent: () { Navigator.of(sheetContext).pop(); _openCreateEvent(); },
        onAddReminder: () { Navigator.of(sheetContext).pop(); _openCreateReminder(); },
        onEditNote: (note) { Navigator.of(sheetContext).pop(); _openEditNote(note); },
        onEditEvent: (event) { Navigator.of(sheetContext).pop(); _openEditEvent(event); },
        onEditReminder: (reminder) { Navigator.of(sheetContext).pop(); _openEditReminder(reminder); },
        onDeleteNote: (note) => _confirmDeleteNote(note),
        onDeleteEvent: (event) => _confirmDeleteEvent(event),
        onDeleteReminder: (reminder) => _confirmDeleteReminder(reminder),
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
        child: FutureBuilder<List<CalendarDayItem>>(
          future: _future,
          builder: (context, snapshot) {
            final items = snapshot.data ?? const <CalendarDayItem>[];
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
                    items: items,
                    onDayTap: (day) => _openDay(day, items),
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
              LucideIcons.chevronLeft,
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
              LucideIcons.chevronLeft,
              color: AppTheme.textSecondary,
              size: 18,
            ),
          ),
          IconButton(
            onPressed: onNext,
            icon: const Icon(
              LucideIcons.chevronRight,
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
    required this.items,
    required this.onDayTap,
  });

  final int year;
  final int month;
  final DateTime today;
  final List<CalendarDayItem> items;
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
                              hasEvents: _hasItems(DateTime(year, month, day)),
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
    final matches = items.where((e) => _itemCoversDay(e, day)).toList();
    return [for (final e in matches.take(3)) e.color];
  }

  bool _hasItems(DateTime day) => items.any((e) => _itemCoversDay(e, day));
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

/// Builds the notes sliver for the day sheet from a list of notes.
Widget _dayNotes(List<Note> notes, _DaySheetState sheet) {
  if (notes.isEmpty) {
    return const SliverToBoxAdapter(
      child: Padding(
        padding: EdgeInsets.symmetric(vertical: 8),
        child: Row(
          children: [
            Icon(
              LucideIcons.notebookText,
              size: 16,
              color: AppTheme.textMuted,
            ),
            SizedBox(width: 10),
            Text(
              'No notes on this day',
              style: TextStyle(
                color: AppTheme.textSecondary,
                fontSize: 13,
                fontWeight: FontWeight.w600,
              ),
            ),
          ],
        ),
      ),
    );
  }
  return SliverList(
    delegate: SliverChildBuilderDelegate(
      (context, i) => Padding(
        padding: const EdgeInsets.only(bottom: 8),
        child: _DayNoteRow(
          note: notes[i],
          onTap: () => sheet.widget.onEditNote(notes[i]),
          onDelete: () => sheet.widget.onDeleteNote(notes[i]),
        ),
      ),
      childCount: notes.length,
    ),
  );
}

/// Reminders section for the day sheet — shows reminders from the items
/// passed in (already filtered to this day).
/// Events section for the day sheet — shows calendar events with times.
class _DayEventsSection extends StatelessWidget {
  const _DayEventsSection({
    required this.events,
    required this.onAdd,
    required this.onEdit,
    required this.onDelete,
  });

  final List<CalendarEvent> events;
  final VoidCallback onAdd;
  final ValueChanged<CalendarEvent> onEdit;
  final ValueChanged<CalendarEvent> onDelete;

  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Row(
          children: [
            const Icon(LucideIcons.calendar, size: 15, color: AppTheme.primaryDark),
            const SizedBox(width: 8),
            const Text('Events',
                style: TextStyle(fontSize: 13.5, fontWeight: FontWeight.w900, color: AppTheme.textPrimary)),
            const SizedBox(width: 8),
            if (events.isNotEmpty)
              Text('${events.length}',
                  style: const TextStyle(fontSize: 11, fontWeight: FontWeight.w800, color: AppTheme.textMuted)),
            const Spacer(),
            TextButton.icon(
              onPressed: onAdd,
              icon: const Icon(LucideIcons.plus, size: 14),
              label: const Text('Add event'),
              style: TextButton.styleFrom(
                foregroundColor: AppTheme.primaryDark,
                padding: EdgeInsets.zero,
                textStyle: const TextStyle(fontWeight: FontWeight.w800, fontSize: 13),
              ),
            ),
          ],
        ),
        const SizedBox(height: 10),
        if (events.isEmpty)
          const Padding(
            padding: EdgeInsets.symmetric(vertical: 8),
            child: Row(
              children: [
                Icon(LucideIcons.calendarOff, size: 16, color: AppTheme.textMuted),
                SizedBox(width: 10),
                Text('No events on this day',
                    style: TextStyle(color: AppTheme.textSecondary, fontSize: 13, fontWeight: FontWeight.w600)),
              ],
            ),
          )
        else
          ListView.separated(
            shrinkWrap: true,
            physics: const NeverScrollableScrollPhysics(),
            itemCount: events.length,
            separatorBuilder: (_, _) => const SizedBox(height: 8),
            itemBuilder: (_, i) => _DayEventRow(
              event: events[i],
              onTap: () => onEdit(events[i]),
              onDelete: () => onDelete(events[i]),
            ),
          ),
      ],
    );
  }
}

class _DayEventRow extends StatelessWidget {
  const _DayEventRow({required this.event, required this.onTap, required this.onDelete});

  final CalendarEvent event;
  final VoidCallback onTap;
  final VoidCallback onDelete;

  @override
  Widget build(BuildContext context) {
    final color = _parseHexColor(event.color);
    final timeLabel = event.allDay
        ? 'All day'
        : '${_formatTime(event.start)} – ${_formatTime(event.end)}';
    return Material(
      color: color.withValues(alpha: 0.08),
      borderRadius: BorderRadius.circular(12),
      child: InkWell(
        borderRadius: BorderRadius.circular(12),
        onTap: onTap,
        child: Container(
          padding: const EdgeInsets.all(12),
          decoration: BoxDecoration(
            borderRadius: BorderRadius.circular(12),
            border: Border.all(color: color.withValues(alpha: 0.3)),
          ),
          child: Row(
            children: [
              Container(
                width: 4,
                height: 36,
                decoration: BoxDecoration(color: color, borderRadius: BorderRadius.circular(2)),
              ),
              const SizedBox(width: 10),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      event.title.isEmpty ? '(Untitled event)' : event.title,
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                      style: TextStyle(
                        fontWeight: FontWeight.w800,
                        fontSize: 13,
                        color: color,
                      ),
                    ),
                    const SizedBox(height: 2),
                    Row(
                      children: [
                        Icon(LucideIcons.clock, size: 11, color: AppTheme.textMuted),
                        const SizedBox(width: 4),
                        Text(timeLabel,
                            style: const TextStyle(fontSize: 11, fontWeight: FontWeight.w600, color: AppTheme.textMuted)),
                        if (event.location.isNotEmpty) ...[
                          const SizedBox(width: 8),
                          Icon(LucideIcons.mapPin, size: 11, color: AppTheme.textMuted),
                          const SizedBox(width: 4),
                          Expanded(
                            child: Text(event.location,
                                maxLines: 1,
                                overflow: TextOverflow.ellipsis,
                                style: const TextStyle(fontSize: 11, fontWeight: FontWeight.w600, color: AppTheme.textMuted)),
                          ),
                        ],
                      ],
                    ),
                  ],
                ),
              ),
              IconButton(
                visualDensity: VisualDensity.compact,
                padding: EdgeInsets.zero,
                constraints: const BoxConstraints(),
                icon: const Icon(LucideIcons.trash2, size: 16, color: AppTheme.danger),
                onPressed: onDelete,
              ),
            ],
          ),
        ),
      ),
    );
  }
}

class _DayRemindersSection extends StatelessWidget {
  const _DayRemindersSection({
    required this.reminders,
    this.onAdd,
    this.onEdit,
    this.onDelete,
  });

  final List<Reminder> reminders;
  final VoidCallback? onAdd;
  final ValueChanged<Reminder>? onEdit;
  final ValueChanged<Reminder>? onDelete;

  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Row(
          children: [
            const Icon(
              LucideIcons.bellRing,
              size: 15,
              color: AppTheme.primaryDark,
            ),
            const SizedBox(width: 8),
            const Text(
              'Reminders',
              style: TextStyle(
                fontSize: 13.5,
                fontWeight: FontWeight.w900,
                color: AppTheme.textPrimary,
              ),
            ),
            const SizedBox(width: 8),
            if (reminders.isNotEmpty)
              Text(
                '${reminders.length}',
                style: const TextStyle(
                  fontSize: 11,
                  fontWeight: FontWeight.w800,
                  color: AppTheme.textMuted,
                ),
              ),
            const Spacer(),
            if (onAdd != null)
              TextButton.icon(
                onPressed: onAdd,
                icon: const Icon(LucideIcons.plus, size: 14),
                label: const Text('Add reminder'),
                style: TextButton.styleFrom(
                  foregroundColor: AppTheme.primaryDark,
                  padding: EdgeInsets.zero,
                  textStyle: const TextStyle(fontWeight: FontWeight.w800, fontSize: 13),
                ),
              ),
          ],
        ),
        const SizedBox(height: 10),
        if (reminders.isEmpty)
          const Padding(
            padding: EdgeInsets.symmetric(vertical: 8),
            child: Row(
              children: [
                Icon(
                  LucideIcons.bellOff,
                  size: 16,
                  color: AppTheme.textMuted,
                ),
                SizedBox(width: 10),
                Text(
                  'No reminders on this day',
                  style: TextStyle(
                    color: AppTheme.textSecondary,
                    fontSize: 13,
                    fontWeight: FontWeight.w600,
                  ),
                ),
              ],
            ),
          )
        else
          ListView.separated(
            shrinkWrap: true,
            physics: const NeverScrollableScrollPhysics(),
            itemCount: reminders.length,
            separatorBuilder: (_, _) => const SizedBox(height: 8),
            itemBuilder: (_, i) => _DayReminderRow(
              reminder: reminders[i],
              onTap: onEdit != null ? () => onEdit!(reminders[i]) : null,
              onDelete: onDelete != null ? () => onDelete!(reminders[i]) : null,
            ),
          ),
      ],
    );
  }
}

class _DayReminderRow extends StatelessWidget {
  const _DayReminderRow({required this.reminder, this.onTap, this.onDelete});

  final Reminder reminder;
  final VoidCallback? onTap;
  final VoidCallback? onDelete;

  @override
  Widget build(BuildContext context) {
    final card = Container(
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        color: const Color(0xFFFFF7ED),
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: const Color(0xFFFED7AA)),
      ),
      child: Row(
        children: [
          const Icon(
            LucideIcons.bellRing,
            size: 16,
            color: Color(0xFFEA580C),
          ),
          const SizedBox(width: 10),
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
                    fontSize: 13,
                    color: AppTheme.textPrimary,
                  ),
                ),
                if (reminder.remindAtLabel.isNotEmpty) ...[
                  const SizedBox(height: 2),
                  Text(
                    reminder.remindAtLabel,
                    style: const TextStyle(
                      fontSize: 11,
                      fontWeight: FontWeight.w600,
                      color: AppTheme.textMuted,
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
          if (onDelete != null) ...[
            const SizedBox(width: 8),
            IconButton(
              visualDensity: VisualDensity.compact,
              padding: EdgeInsets.zero,
              constraints: const BoxConstraints(),
              icon: const Icon(LucideIcons.trash2, size: 16, color: AppTheme.danger),
              onPressed: onDelete,
            ),
          ],
        ],
      ),
    );
    if (onTap == null) return card;
    return InkWell(
      borderRadius: BorderRadius.circular(12),
      onTap: onTap,
      child: card,
    );
  }
}

class _DaySheet extends StatefulWidget {
  const _DaySheet({
    required this.session,
    required this.day,
    required this.items,
    required this.onAddNote,
    required this.onAddEvent,
    required this.onAddReminder,
    required this.onEditNote,
    required this.onEditEvent,
    required this.onEditReminder,
    required this.onDeleteNote,
    required this.onDeleteEvent,
    required this.onDeleteReminder,
  });

  final StaffSession session;
  final DateTime day;
  final List<CalendarDayItem> items;
  final VoidCallback onAddNote;
  final VoidCallback onAddEvent;
  final VoidCallback onAddReminder;
  final ValueChanged<Note> onEditNote;
  final ValueChanged<CalendarEvent> onEditEvent;
  final ValueChanged<Reminder> onEditReminder;
  final ValueChanged<Note> onDeleteNote;
  final ValueChanged<CalendarEvent> onDeleteEvent;
  final ValueChanged<Reminder> onDeleteReminder;

  @override
  State<_DaySheet> createState() => _DaySheetState();
}

class _DaySheetState extends State<_DaySheet> {
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
    final weekday = _weekdaysFull[widget.day.weekday - 1];
    final maxSheetHeight = MediaQuery.of(context).size.height * 0.82;

    return SafeArea(
      top: false,
      child: ConstrainedBox(
        constraints: BoxConstraints(maxHeight: maxSheetHeight),
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
                '${_monthNamesFull[widget.day.month - 1]} ${widget.day.day}, ${widget.day.year}',
                style: const TextStyle(
                  fontSize: 22,
                  fontWeight: FontWeight.w900,
                  letterSpacing: -0.4,
                  color: AppTheme.textPrimary,
                ),
              ),
              const SizedBox(height: 14),
              Expanded(
                child: CustomScrollView(
                  slivers: [
                    // Events section
                    SliverToBoxAdapter(
                      child: _DayEventsSection(
                        events: widget.items
                            .where((e) => e.isEvent)
                            .map((e) => e.event!)
                            .toList(),
                        onAdd: widget.onAddEvent,
                        onEdit: widget.onEditEvent,
                        onDelete: widget.onDeleteEvent,
                      ),
                    ),
                    const SliverToBoxAdapter(child: SizedBox(height: 20)),
                    // Reminders section
                    SliverToBoxAdapter(
                      child: _DayRemindersSection(
                        reminders: widget.items
                            .where((e) => e.isReminder)
                            .map((e) => e.reminder!)
                            .toList(),
                        onAdd: widget.onAddReminder,
                        onEdit: widget.onEditReminder,
                        onDelete: widget.onDeleteReminder,
                      ),
                    ),
                    const SliverToBoxAdapter(child: SizedBox(height: 20)),
                    // Notes section header
                    SliverToBoxAdapter(
                      child: Row(
                        children: [
                          const Icon(
                            LucideIcons.notebookText,
                            size: 15,
                            color: AppTheme.primaryDark,
                          ),
                          const SizedBox(width: 8),
                          const Expanded(
                            child: Text(
                              'Notes',
                              style: TextStyle(
                                fontSize: 13.5,
                                fontWeight: FontWeight.w900,
                                color: AppTheme.textPrimary,
                              ),
                            ),
                          ),
                          TextButton.icon(
                            onPressed: widget.onAddNote,
                            icon: const Icon(
                              LucideIcons.plus,
                              size: 14,
                            ),
                            label: const Text('Add note'),
                            style: TextButton.styleFrom(
                              foregroundColor: AppTheme.primaryDark,
                              padding: EdgeInsets.zero,
                              textStyle: const TextStyle(
                                fontWeight: FontWeight.w800,
                                fontSize: 13,
                              ),
                            ),
                          ),
                        ],
                      ),
                    ),
                    const SliverToBoxAdapter(child: SizedBox(height: 10)),
                    // Notes list (from items passed in)
                    _dayNotes(
                      widget.items
                          .where((e) => e.isNote)
                          .map((e) => e.note!)
                          .toList(),
                      this,
                    ),
                    const SliverToBoxAdapter(child: SizedBox(height: 14)),
                    // Add note button
                    SliverToBoxAdapter(
                      child: SizedBox(
                        width: double.infinity,
                        child: FilledButton.icon(
                          style: FilledButton.styleFrom(
                            backgroundColor: AppTheme.primaryDark,
                          ),
                          onPressed: widget.onAddNote,
                          icon: const Icon(LucideIcons.plus, size: 16),
                          label: const Text('Add note'),
                        ),
                      ),
                    ),
                  ],
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}

class _DayNoteRow extends StatelessWidget {
  const _DayNoteRow({
    required this.note,
    required this.onTap,
    required this.onDelete,
  });

  final Note note;
  final VoidCallback onTap;
  final VoidCallback onDelete;

  @override
  Widget build(BuildContext context) {
    final title = note.title.trim();
    final description = note.description.trim();
    final hasBody = title.isNotEmpty || description.isNotEmpty;

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
                          fontSize: 14.5,
                          color: AppTheme.textPrimary,
                        ),
                      ),
                    if (description.isNotEmpty) ...[
                      if (title.isNotEmpty) const SizedBox(height: 3),
                      Html(
                        data: description,
                        shrinkWrap: true,
                        style: {
                          'p': Style(
                            margin: Margins.zero,
                            color: AppTheme.textSecondary,
                            fontSize: FontSize(12.5),
                            fontWeight: FontWeight.w600,
                            lineHeight: const LineHeight(1.35),
                            maxLines: 2,
                            textOverflow: TextOverflow.ellipsis,
                          ),
                          'a': Style(
                            color: AppTheme.primaryDark,
                            fontSize: FontSize(12.5),
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
                    if (!hasBody)
                      const Text(
                        'Empty note',
                        style: TextStyle(
                          color: AppTheme.textMuted,
                          fontSize: 12.5,
                          fontStyle: FontStyle.italic,
                        ),
                      ),
                  ],
                ),
              ),
              IconButton(
                visualDensity: VisualDensity.compact,
                padding: EdgeInsets.zero,
                constraints: const BoxConstraints(),
                icon: const Icon(
                  LucideIcons.trash2,
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
}

// ─── Week View ──────────────────────────────────────────────────────────────

class _WeekView extends StatelessWidget {
  const _WeekView({
    required this.weekStart,
    required this.today,
    required this.items,
    required this.onDayTap,
  });

  final DateTime weekStart;
  final DateTime today;
  final List<CalendarDayItem> items;
  final ValueChanged<DateTime> onDayTap;

  @override
  Widget build(BuildContext context) {
    final days = [for (var i = 0; i < 7; i++) weekStart.add(Duration(days: i))];
    return ListView.builder(
      padding: const EdgeInsets.fromLTRB(12, 4, 12, 100),
      itemCount: days.length,
      itemBuilder: (context, i) {
        final day = days[i];
        final dayItems = items.where((e) => _itemCoversDay(e, day)).toList()
          ..sort((a, b) => a.date.compareTo(b.date));
        final isToday = _sameDay(day, today);
        return _WeekDayRow(
          day: day,
          isToday: isToday,
          items: dayItems,
          onTap: () => onDayTap(day),
        );
      },
    );
  }
}

class _WeekDayRow extends StatelessWidget {
  const _WeekDayRow({
    required this.day,
    required this.isToday,
    required this.items,
    required this.onTap,
  });

  final DateTime day;
  final bool isToday;
  final List<CalendarDayItem> items;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 10),
      child: InkWell(
        borderRadius: BorderRadius.circular(14),
        onTap: onTap,
        child: Container(
          padding: const EdgeInsets.all(12),
          decoration: BoxDecoration(
            color: isToday ? kAppleRed.withValues(alpha: 0.06) : AppTheme.surface,
            borderRadius: BorderRadius.circular(14),
            border: Border.all(
              color: isToday ? kAppleRed.withValues(alpha: 0.3) : AppTheme.border,
            ),
          ),
          child: Row(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              SizedBox(
                width: 44,
                child: Column(
                  children: [
                    Text(
                      _weekdayLetters[day.weekday % 7],
                      style: TextStyle(
                        fontSize: 11,
                        fontWeight: FontWeight.w800,
                        color: isToday ? kAppleRed : AppTheme.textMuted,
                      ),
                    ),
                    const SizedBox(height: 2),
                    Container(
                      width: 28,
                      height: 28,
                      alignment: Alignment.center,
                      decoration: BoxDecoration(
                        color: isToday ? kAppleRed : Colors.transparent,
                        borderRadius: BorderRadius.circular(8),
                      ),
                      child: Text(
                        '${day.day}',
                        style: TextStyle(
                          fontSize: 14,
                          fontWeight: FontWeight.w900,
                          color: isToday ? Colors.white : AppTheme.textPrimary,
                        ),
                      ),
                    ),
                  ],
                ),
              ),
              const SizedBox(width: 10),
              Expanded(
                child: items.isEmpty
                    ? const Padding(
                        padding: EdgeInsets.symmetric(vertical: 6),
                        child: Text(
                          'No items',
                          style: TextStyle(
                            color: AppTheme.textMuted,
                            fontSize: 12,
                            fontWeight: FontWeight.w600,
                            fontStyle: FontStyle.italic,
                          ),
                        ),
                      )
                    : Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          for (final item in items.take(4))
                            Padding(
                              padding: const EdgeInsets.only(bottom: 4),
                              child: Row(
                                children: [
                                  Container(
                                    width: 8,
                                    height: 8,
                                    decoration: BoxDecoration(
                                      color: item.color,
                                      shape: BoxShape.circle,
                                    ),
                                  ),
                                  const SizedBox(width: 8),
                                  if (item.isEvent && item.event != null && !item.event!.allDay)
                                    Text(
                                      '${_formatTime(item.event!.start)} ',
                                      style: const TextStyle(
                                        fontSize: 10.5,
                                        fontWeight: FontWeight.w700,
                                        color: AppTheme.textMuted,
                                      ),
                                    ),
                                  Expanded(
                                    child: Text(
                                      item.title.isEmpty
                                          ? (item.isNote ? 'Untitled note' : item.isEvent ? 'Untitled event' : 'Untitled reminder')
                                          : item.title,
                                      maxLines: 1,
                                      overflow: TextOverflow.ellipsis,
                                      style: TextStyle(
                                        fontSize: 12.5,
                                        fontWeight: FontWeight.w700,
                                        color: item.color,
                                      ),
                                    ),
                                  ),
                                ],
                              ),
                            ),
                          if (items.length > 4)
                            Padding(
                              padding: const EdgeInsets.only(top: 2),
                              child: Text(
                                '+${items.length - 4} more',
                                style: const TextStyle(
                                  fontSize: 11,
                                  fontWeight: FontWeight.w700,
                                  color: AppTheme.textMuted,
                                ),
                              ),
                            ),
                        ],
                      ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}

// ─── Day View ───────────────────────────────────────────────────────────────

class _DayView extends StatelessWidget {
  const _DayView({
    required this.day,
    required this.today,
    required this.items,
    required this.onAddEvent,
    required this.onAddNote,
    required this.onAddReminder,
    required this.onEditEvent,
    required this.onEditNote,
    required this.onEditReminder,
    required this.onDeleteEvent,
    required this.onDeleteNote,
    required this.onDeleteReminder,
  });

  final DateTime day;
  final DateTime today;
  final List<CalendarDayItem> items;
  final VoidCallback onAddEvent;
  final VoidCallback onAddNote;
  final VoidCallback onAddReminder;
  final ValueChanged<CalendarEvent> onEditEvent;
  final ValueChanged<Note> onEditNote;
  final ValueChanged<Reminder> onEditReminder;
  final ValueChanged<CalendarEvent> onDeleteEvent;
  final ValueChanged<Note> onDeleteNote;
  final ValueChanged<Reminder> onDeleteReminder;

  @override
  Widget build(BuildContext context) {
    final events = items.where((e) => e.isEvent).map((e) => e.event!).toList()
      ..sort((a, b) => a.start.compareTo(b.start));
    final notes = items.where((e) => e.isNote).map((e) => e.note!).toList();
    final reminders = items.where((e) => e.isReminder).map((e) => e.reminder!).toList();
    final isToday = _sameDay(day, today);

    return ListView(
      padding: const EdgeInsets.fromLTRB(16, 4, 16, 100),
      children: [
        // Day header
        Container(
          padding: const EdgeInsets.all(16),
          decoration: BoxDecoration(
            color: isToday ? kAppleRed.withValues(alpha: 0.06) : AppTheme.surface,
            borderRadius: BorderRadius.circular(16),
            border: Border.all(color: isToday ? kAppleRed.withValues(alpha: 0.2) : AppTheme.border),
          ),
          child: Row(
            children: [
              Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    _weekdayFull(day.weekday).toUpperCase(),
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
                      fontSize: 20,
                      fontWeight: FontWeight.w900,
                      letterSpacing: -0.3,
                      color: AppTheme.textPrimary,
                    ),
                  ),
                ],
              ),
              const Spacer(),
              // Quick add buttons
              Row(
                children: [
                  _QuickAddButton(icon: LucideIcons.calendarPlus, label: 'Event', onTap: onAddEvent),
                  const SizedBox(width: 8),
                  _QuickAddButton(icon: LucideIcons.bellPlus, label: 'Reminder', onTap: onAddReminder),
                  const SizedBox(width: 8),
                  _QuickAddButton(icon: LucideIcons.stickyNote, label: 'Note', onTap: onAddNote),
                ],
              ),
            ],
          ),
        ),
        const SizedBox(height: 20),
        // Events timeline
        if (events.isNotEmpty) ...[
          const _SectionLabel(icon: LucideIcons.calendar, label: 'Events'),
          const SizedBox(height: 10),
          for (final event in events)
            Padding(
              padding: const EdgeInsets.only(bottom: 10),
              child: _DayEventRow(
                event: event,
                onTap: () => onEditEvent(event),
                onDelete: () => onDeleteEvent(event),
              ),
            ),
          const SizedBox(height: 20),
        ],
        // Reminders
        const _SectionLabel(icon: LucideIcons.bellRing, label: 'Reminders'),
        const SizedBox(height: 10),
        if (reminders.isEmpty)
          const _EmptyHint(icon: LucideIcons.bellOff, text: 'No reminders on this day')
        else
          for (final reminder in reminders)
            Padding(
              padding: const EdgeInsets.only(bottom: 8),
              child: _DayReminderRow(
                reminder: reminder,
                onTap: () => onEditReminder(reminder),
                onDelete: () => onDeleteReminder(reminder),
              ),
            ),
        const SizedBox(height: 20),
        // Notes
        const _SectionLabel(icon: LucideIcons.notebookText, label: 'Notes'),
        const SizedBox(height: 10),
        if (notes.isEmpty)
          const _EmptyHint(icon: LucideIcons.notebookPen, text: 'No notes on this day')
        else
          for (final note in notes)
            Padding(
              padding: const EdgeInsets.only(bottom: 8),
              child: _DayNoteRow(
                note: note,
                onTap: () => onEditNote(note),
                onDelete: () => onDeleteNote(note),
              ),
            ),
      ],
    );
  }
}

class _QuickAddButton extends StatelessWidget {
  const _QuickAddButton({required this.icon, required this.label, required this.onTap});
  final IconData icon;
  final String label;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    return PressScale(
      onTap: () { Haptics.light(); onTap(); },
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 8),
        decoration: BoxDecoration(
          color: AppTheme.primaryDark,
          borderRadius: BorderRadius.circular(10),
        ),
        child: Row(
          mainAxisSize: MainAxisSize.min,
          children: [
            Icon(icon, size: 13, color: Colors.white),
            const SizedBox(width: 4),
            Text(label, style: const TextStyle(fontSize: 11, fontWeight: FontWeight.w800, color: Colors.white)),
          ],
        ),
      ),
    );
  }
}

class _SectionLabel extends StatelessWidget {
  const _SectionLabel({required this.icon, required this.label});
  final IconData icon;
  final String label;

  @override
  Widget build(BuildContext context) {
    return Row(
      children: [
        Icon(icon, size: 15, color: AppTheme.primaryDark),
        const SizedBox(width: 8),
        Text(label,
            style: const TextStyle(fontSize: 13.5, fontWeight: FontWeight.w900, color: AppTheme.textPrimary)),
      ],
    );
  }
}

class _EmptyHint extends StatelessWidget {
  const _EmptyHint({required this.icon, required this.text});
  final IconData icon;
  final String text;

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 8),
      child: Row(
        children: [
          Icon(icon, size: 16, color: AppTheme.textMuted),
          const SizedBox(width: 10),
          Text(text,
              style: const TextStyle(color: AppTheme.textSecondary, fontSize: 13, fontWeight: FontWeight.w600)),
        ],
      ),
    );
  }
}

// ─── Reminder Quick Editor ───────────────────────────────────────────────────

/// A lightweight screen for creating/editing a reminder from the calendar.
/// Reuses the RemindersApi directly.
class _ReminderQuickEditor extends StatefulWidget {
  const _ReminderQuickEditor({required this.session, required this.date, this.existing});

  final StaffSession session;
  final DateTime date;
  final Reminder? existing;

  @override
  State<_ReminderQuickEditor> createState() => _ReminderQuickEditorState();
}

class _ReminderQuickEditorState extends State<_ReminderQuickEditor> {
  final RemindersApi _api = RemindersApi();
  final _titleController = TextEditingController();
  final _descController = TextEditingController();
  late DateTime _selectedDateTime;
  String _recurrence = 'once';
  bool _saving = false;

  @override
  void initState() {
    super.initState();
    final e = widget.existing;
    _titleController.text = e?.title ?? '';
    _descController.text = e?.description ?? '';
    _selectedDateTime = e?.remindAtDate ?? widget.date;
    _recurrence = e?.recurrence ?? 'once';
  }

  @override
  void dispose() {
    _titleController.dispose();
    _descController.dispose();
    super.dispose();
  }

  String _formatDateTime(DateTime dt) {
    return '${dt.year.toString().padLeft(4, '0')}-${dt.month.toString().padLeft(2, '0')}-${dt.day.toString().padLeft(2, '0')} '
        '${dt.hour.toString().padLeft(2, '0')}:${dt.minute.toString().padLeft(2, '0')}:00';
  }

  Future<void> _pickDate() async {
    final d = await showDatePicker(
      context: context,
      initialDate: _selectedDateTime,
      firstDate: DateTime(2000),
      lastDate: DateTime(2100),
    );
    if (d == null) return;
    setState(() {
      _selectedDateTime = DateTime(d.year, d.month, d.day, _selectedDateTime.hour, _selectedDateTime.minute);
    });
  }

  Future<void> _pickTime() async {
    final t = await showTimePicker(
      context: context,
      initialTime: TimeOfDay.fromDateTime(_selectedDateTime),
    );
    if (t == null) return;
    setState(() {
      _selectedDateTime = DateTime(_selectedDateTime.year, _selectedDateTime.month, _selectedDateTime.day, t.hour, t.minute);
    });
  }

  Future<void> _save() async {
    if (_titleController.text.trim().isEmpty) {
      AppToast.error(context, 'Please enter a title.');
      return;
    }
    setState(() => _saving = true);
    try {
      if (widget.existing != null) {
        await _api.updateReminder(
          baseUrl: widget.session.baseUrl,
          token: widget.session.token,
          reminderId: widget.existing!.id,
          title: _titleController.text.trim(),
          description: _descController.text.trim(),
          remindAt: _formatDateTime(_selectedDateTime),
          recurrence: _recurrence,
        );
      } else {
        await _api.createReminder(
          baseUrl: widget.session.baseUrl,
          token: widget.session.token,
          title: _titleController.text.trim(),
          description: _descController.text.trim(),
          remindAt: _formatDateTime(_selectedDateTime),
          recurrence: _recurrence,
        );
      }
      if (!mounted) return;
      AppToast.success(context, widget.existing != null ? 'Reminder updated.' : 'Reminder created.');
      Navigator.of(context).pop(true);
    } on ApiException catch (e) {
      if (!mounted) return;
      AppToast.error(context, e.message);
    } finally {
      if (mounted) setState(() => _saving = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final isEdit = widget.existing != null;
    return Scaffold(
      backgroundColor: AppTheme.background,
      appBar: AppBar(
        backgroundColor: AppTheme.surface,
        elevation: 0,
        leading: IconButton(
          icon: const Icon(LucideIcons.chevronLeft, color: AppTheme.textPrimary),
          onPressed: () => Navigator.of(context).pop(),
        ),
        title: Text(isEdit ? 'Edit Reminder' : 'New Reminder',
            style: const TextStyle(fontWeight: FontWeight.w900, fontSize: 18, color: AppTheme.textPrimary)),
      ),
      body: SafeArea(
        child: SingleChildScrollView(
          padding: const EdgeInsets.all(20),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              TextField(
                controller: _titleController,
                decoration: const InputDecoration(labelText: 'Title', hintText: 'Reminder title'),
                textCapitalization: TextCapitalization.sentences,
              ),
              const SizedBox(height: 16),
              TextField(
                controller: _descController,
                maxLines: 3,
                decoration: const InputDecoration(labelText: 'Description', hintText: 'Optional details'),
                textCapitalization: TextCapitalization.sentences,
              ),
              const SizedBox(height: 16),
              Row(
                children: [
                  Expanded(
                    child: _PickerField(
                      icon: LucideIcons.calendar,
                      label: 'Date',
                      value: '${_selectedDateTime.month}/${_selectedDateTime.day}/${_selectedDateTime.year}',
                      onTap: _pickDate,
                    ),
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    child: _PickerField(
                      icon: LucideIcons.clock,
                      label: 'Time',
                      value: _formatTime(_selectedDateTime),
                      onTap: _pickTime,
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 16),
              const Text('Repeat',
                  style: TextStyle(fontSize: 13, fontWeight: FontWeight.w800, color: AppTheme.textSecondary)),
              const SizedBox(height: 8),
              Wrap(
                spacing: 8,
                children: [
                  for (final r in ['once', 'monthly', 'yearly'])
                    _RecurrenceChip(
                      label: r == 'once' ? 'One-time' : r == 'monthly' ? 'Monthly' : 'Yearly',
                      selected: _recurrence == r,
                      onTap: () => setState(() => _recurrence = r),
                    ),
                ],
              ),
              const SizedBox(height: 28),
              FilledButton(
                onPressed: _saving ? null : _save,
                style: FilledButton.styleFrom(
                  backgroundColor: AppTheme.primaryDark,
                  minimumSize: const Size(0, 50),
                ),
                child: _saving
                    ? const SizedBox(width: 18, height: 18, child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white))
                    : Text(isEdit ? 'Update Reminder' : 'Create Reminder',
                        style: const TextStyle(fontWeight: FontWeight.w800, fontSize: 15)),
              ),
            ],
          ),
        ),
      ),
    );
  }
}

class _PickerField extends StatelessWidget {
  const _PickerField({required this.icon, required this.label, required this.value, required this.onTap});
  final IconData icon;
  final String label;
  final String value;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    return InkWell(
      borderRadius: BorderRadius.circular(12),
      onTap: onTap,
      child: InputDecorator(
        decoration: InputDecoration(
          labelText: label,
          prefixIcon: Icon(icon, size: 18),
          border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
        ),
        child: Text(value, style: const TextStyle(fontWeight: FontWeight.w700, fontSize: 14)),
      ),
    );
  }
}

class _RecurrenceChip extends StatelessWidget {
  const _RecurrenceChip({required this.label, required this.selected, required this.onTap});
  final String label;
  final bool selected;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: onTap,
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 8),
        decoration: BoxDecoration(
          color: selected ? AppTheme.primaryDark : AppTheme.surfaceMuted,
          borderRadius: BorderRadius.circular(99),
          border: Border.all(color: selected ? AppTheme.primaryDark : AppTheme.border),
        ),
        child: Text(label,
            style: TextStyle(
              fontSize: 12.5,
              fontWeight: FontWeight.w800,
              color: selected ? Colors.white : AppTheme.textSecondary,
            )),
      ),
    );
  }
}

