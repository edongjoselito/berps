import 'package:flutter/material.dart';
import 'package:phosphor_flutter/phosphor_flutter.dart';

import '../../../core/network/api_exception.dart';
import '../../../core/theme/app_theme.dart';
import '../../../core/utils/haptics.dart';
import '../../../core/utils/responsive.dart';
import '../../../core/widgets/animations.dart';
import '../../../core/widgets/app_toast.dart';
import '../../../core/widgets/mobile_header.dart';
import '../../auth/domain/staff_session.dart';
import '../../home/data/staff_api.dart';
import '../domain/calendar_event.dart';

class CalendarEventEditor extends StatefulWidget {
  const CalendarEventEditor({super.key, required this.session, this.existing});

  final StaffSession session;
  final CalendarEvent? existing;

  @override
  State<CalendarEventEditor> createState() => _CalendarEventEditorState();
}

class _CalendarEventEditorState extends State<CalendarEventEditor> {
  final StaffApi _api = StaffApi();
  final _formKey = GlobalKey<FormState>();
  late final TextEditingController _title;
  late final TextEditingController _description;
  late final TextEditingController _notes;
  late final TextEditingController _location;
  late final TextEditingController _reminderEmail;

  late DateTime _start;
  late DateTime _end;
  late bool _allDay;
  late bool _isPublic;
  late bool _reminderEnabled;
  late String _color;
  bool _saving = false;
  bool _deleting = false;

  static const _colorChoices = <String>[
    '#3788d8',
    '#2563EB',
    '#16A34A',
    '#D9A24B',
    '#B91C1C',
    '#7C3AED',
    '#0EA5E9',
    '#EC4899',
  ];

  @override
  void initState() {
    super.initState();
    final e = widget.existing;
    _title = TextEditingController(text: e?.title ?? '');
    _description = TextEditingController(text: e?.description ?? '');
    _notes = TextEditingController(text: e?.notes ?? '');
    _location = TextEditingController(text: e?.location ?? '');
    _reminderEmail = TextEditingController(text: e?.reminderEmail ?? '');
    _start = e?.start ?? DateTime.now().add(const Duration(hours: 1));
    _end = e?.end ?? _start.add(const Duration(hours: 1));
    _allDay = e?.allDay ?? false;
    _isPublic = e?.isPublic ?? false;
    _reminderEnabled = e?.reminderEmailEnabled ?? false;
    _color = e?.color ?? _colorChoices.first;
  }

  @override
  void dispose() {
    _title.dispose();
    _description.dispose();
    _notes.dispose();
    _location.dispose();
    _reminderEmail.dispose();
    super.dispose();
  }

  bool get _isEdit => widget.existing != null;
  bool get _canEdit => widget.existing == null || widget.existing!.canEdit;

  String _formatDateTime(DateTime dt) {
    final y = dt.year.toString().padLeft(4, '0');
    final m = dt.month.toString().padLeft(2, '0');
    final d = dt.day.toString().padLeft(2, '0');
    final h = dt.hour.toString().padLeft(2, '0');
    final mm = dt.minute.toString().padLeft(2, '0');
    return '$y-$m-$d $h:$mm:00';
  }

  Future<void> _pickDateTime({required bool isStart}) async {
    Haptics.light();
    final base = isStart ? _start : _end;
    final date = await showDatePicker(
      context: context,
      initialDate: base,
      firstDate: DateTime(2020),
      lastDate: DateTime(2100),
    );
    if (date == null || !mounted) return;
    TimeOfDay time = TimeOfDay.fromDateTime(base);
    if (!_allDay) {
      final picked = await showTimePicker(context: context, initialTime: time);
      if (picked == null || !mounted) return;
      time = picked;
    }
    final combined = DateTime(
      date.year,
      date.month,
      date.day,
      _allDay ? (isStart ? 0 : 23) : time.hour,
      _allDay ? (isStart ? 0 : 59) : time.minute,
    );
    setState(() {
      if (isStart) {
        _start = combined;
        if (_end.isBefore(_start)) {
          _end = _start.add(const Duration(hours: 1));
        }
      } else {
        _end = combined;
      }
    });
  }

  Future<void> _save() async {
    if (_saving) return;
    if (!(_formKey.currentState?.validate() ?? false)) {
      Haptics.warn();
      return;
    }
    if (_end.isBefore(_start)) {
      Haptics.warn();
      AppToast.error(context, 'End must be after start.');
      return;
    }

    setState(() => _saving = true);
    Haptics.medium();

    final body = <String, dynamic>{
      'title': _title.text.trim(),
      'description': _description.text.trim(),
      'notes': _notes.text.trim(),
      'start': _formatDateTime(_start),
      'end': _formatDateTime(_end),
      'all_day': _allDay,
      'event_type': 'default',
      'color': _color,
      'location': _location.text.trim(),
      'is_public': _isPublic,
      'reminder_email_enabled': _reminderEnabled,
      'reminder_email': _reminderEmail.text.trim(),
    };

    try {
      if (_isEdit) {
        await _api.updateCalendarEvent(
          baseUrl: widget.session.baseUrl,
          token: widget.session.token,
          id: widget.existing!.id,
          body: body,
        );
      } else {
        await _api.createCalendarEvent(
          baseUrl: widget.session.baseUrl,
          token: widget.session.token,
          body: body,
        );
      }
      if (!mounted) return;
      AppToast.success(context, _isEdit ? 'Event updated.' : 'Event created.');
      Navigator.of(context).pop(true);
    } on ApiException catch (e) {
      if (!mounted) return;
      AppToast.error(context, e.message);
    } finally {
      if (mounted) setState(() => _saving = false);
    }
  }

  Future<void> _delete() async {
    final e = widget.existing;
    if (e == null || _deleting) return;
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
          'This event will be permanently removed.',
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

    setState(() => _deleting = true);
    try {
      await _api.deleteCalendarEvent(
        baseUrl: widget.session.baseUrl,
        token: widget.session.token,
        id: e.id,
      );
      if (!mounted) return;
      AppToast.success(context, 'Event deleted.');
      Navigator.of(context).pop(true);
    } on ApiException catch (e) {
      if (!mounted) return;
      AppToast.error(context, e.message);
    } finally {
      if (mounted) setState(() => _deleting = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppTheme.background,
      body: SafeArea(
        bottom: false,
        child: Form(
          key: _formKey,
          child: ListView(
            padding: EdgeInsets.fromLTRB(
              context.gutter,
              12,
              context.gutter,
              32,
            ),
            children: [
              MobileHeader(
                title: _isEdit ? 'Edit Event' : 'New Event',
                leadingIcon: PhosphorIconsBold.caretLeft,
                onLeadingTap: () {
                  Haptics.light();
                  Navigator.of(context).maybePop();
                },
              ),
              const SizedBox(height: 16),
              _SectionHeader(icon: PhosphorIconsBold.textT, title: 'Details'),
              const SizedBox(height: 10),
              MobileSurfaceCard(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    TextFormField(
                      controller: _title,
                      enabled: _canEdit,
                      decoration: const InputDecoration(
                        labelText: 'Title *',
                        hintText: 'e.g. Project review',
                      ),
                      validator: (v) => (v == null || v.trim().isEmpty)
                          ? 'Title is required'
                          : null,
                    ),
                    const SizedBox(height: 12),
                    TextFormField(
                      controller: _description,
                      enabled: _canEdit,
                      decoration: const InputDecoration(
                        labelText: 'Description',
                      ),
                      minLines: 2,
                      maxLines: 4,
                    ),
                    const SizedBox(height: 12),
                    TextFormField(
                      controller: _location,
                      enabled: _canEdit,
                      decoration: const InputDecoration(labelText: 'Location'),
                    ),
                  ],
                ),
              ),
              const SizedBox(height: 18),
              _SectionHeader(icon: PhosphorIconsBold.clock, title: 'Schedule'),
              const SizedBox(height: 10),
              MobileSurfaceCard(
                child: Column(
                  children: [
                    SwitchListTile.adaptive(
                      contentPadding: EdgeInsets.zero,
                      title: Text(
                        'All day',
                        style: TextStyle(
                          fontWeight: FontWeight.w800,
                          color: AppTheme.textPrimary,
                        ),
                      ),
                      value: _allDay,
                      onChanged: _canEdit
                          ? (v) => setState(() => _allDay = v)
                          : null,
                    ),
                    const Divider(height: 8),
                    _DateTimeRow(
                      label: 'Start',
                      value: _start,
                      allDay: _allDay,
                      onTap: _canEdit
                          ? () => _pickDateTime(isStart: true)
                          : null,
                    ),
                    const Divider(height: 8),
                    _DateTimeRow(
                      label: 'End',
                      value: _end,
                      allDay: _allDay,
                      onTap: _canEdit
                          ? () => _pickDateTime(isStart: false)
                          : null,
                    ),
                  ],
                ),
              ),
              const SizedBox(height: 18),
              _SectionHeader(
                icon: PhosphorIconsBold.palette,
                title: 'Appearance',
              ),
              const SizedBox(height: 10),
              MobileSurfaceCard(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      'Color',
                      style: TextStyle(
                        fontWeight: FontWeight.w800,
                        color: AppTheme.textPrimary,
                        fontSize: 13,
                      ),
                    ),
                    const SizedBox(height: 10),
                    Wrap(
                      spacing: 10,
                      runSpacing: 10,
                      children: _colorChoices.map((c) {
                        final selected = c == _color;
                        final color = Color(
                          int.parse('FF${c.replaceAll('#', '')}', radix: 16),
                        );
                        return GestureDetector(
                          onTap: _canEdit
                              ? () {
                                  Haptics.light();
                                  setState(() => _color = c);
                                }
                              : null,
                          child: Container(
                            width: 36,
                            height: 36,
                            decoration: BoxDecoration(
                              color: color,
                              borderRadius: BorderRadius.circular(12),
                              border: Border.all(
                                color: selected
                                    ? AppTheme.textPrimary
                                    : Colors.transparent,
                                width: 2,
                              ),
                            ),
                            child: selected
                                ? const Icon(
                                    PhosphorIconsBold.check,
                                    color: Colors.white,
                                    size: 16,
                                  )
                                : null,
                          ),
                        );
                      }).toList(),
                    ),
                    const SizedBox(height: 16),
                    SwitchListTile.adaptive(
                      contentPadding: EdgeInsets.zero,
                      title: Text(
                        'Visible to teammates',
                        style: TextStyle(
                          fontWeight: FontWeight.w800,
                          color: AppTheme.textPrimary,
                        ),
                      ),
                      subtitle: Text(
                        'Share this event with everyone in your workspace.',
                        style: TextStyle(
                          color: AppTheme.textSecondary,
                          fontSize: 12.5,
                        ),
                      ),
                      value: _isPublic,
                      onChanged: _canEdit
                          ? (v) => setState(() => _isPublic = v)
                          : null,
                    ),
                  ],
                ),
              ),
              const SizedBox(height: 14),
              MobileSurfaceCard(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    SwitchListTile.adaptive(
                      contentPadding: EdgeInsets.zero,
                      title: Text(
                        'Email reminder',
                        style: TextStyle(
                          fontWeight: FontWeight.w800,
                          color: AppTheme.textPrimary,
                        ),
                      ),
                      subtitle: Text(
                        'Send a reminder 1 day before the event.',
                        style: TextStyle(
                          color: AppTheme.textSecondary,
                          fontSize: 12.5,
                        ),
                      ),
                      value: _reminderEnabled,
                      onChanged: _canEdit
                          ? (v) => setState(() => _reminderEnabled = v)
                          : null,
                    ),
                    if (_reminderEnabled) ...[
                      const SizedBox(height: 6),
                      TextFormField(
                        controller: _reminderEmail,
                        enabled: _canEdit,
                        keyboardType: TextInputType.emailAddress,
                        decoration: InputDecoration(
                          labelText: 'Email',
                          hintText: widget.session.email.isEmpty
                              ? 'recipient@example.com'
                              : widget.session.email,
                        ),
                      ),
                    ],
                  ],
                ),
              ),
              const SizedBox(height: 18),
              _SectionHeader(icon: PhosphorIconsBold.notebook, title: 'Notes'),
              const SizedBox(height: 10),
              MobileSurfaceCard(
                child: TextFormField(
                  controller: _notes,
                  enabled: _canEdit,
                  decoration: const InputDecoration(
                    labelText: 'Notes',
                    hintText: 'Anything else to remember',
                  ),
                  minLines: 3,
                  maxLines: 6,
                ),
              ),
              const SizedBox(height: 18),
              if (_canEdit)
                LoadingButton(
                  label: _isEdit ? 'Save changes' : 'Create event',
                  isLoading: _saving,
                  onPressed: _save,
                ),
              if (_isEdit && (widget.existing?.canDelete ?? false)) ...[
                const SizedBox(height: 10),
                OutlinedButton.icon(
                  onPressed: _deleting ? null : _delete,
                  style: OutlinedButton.styleFrom(
                    foregroundColor: AppTheme.danger,
                    side: const BorderSide(color: AppTheme.danger),
                  ),
                  icon: const Icon(PhosphorIconsBold.trash, size: 16),
                  label: Text(_deleting ? 'Deleting…' : 'Delete event'),
                ),
              ],
              if (!_canEdit) ...[
                const SizedBox(height: 14),
                Container(
                  padding: const EdgeInsets.all(12),
                  decoration: BoxDecoration(
                    color: AppTheme.surfaceMuted,
                    borderRadius: BorderRadius.circular(14),
                  ),
                  child: Text(
                    'This event was created by a teammate — you can view it but cannot make changes.',
                    style: TextStyle(
                      color: AppTheme.textSecondary,
                      fontSize: 12.5,
                      fontWeight: FontWeight.w600,
                    ),
                  ),
                ),
              ],
            ],
          ),
        ),
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
          style: TextStyle(
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

class _DateTimeRow extends StatelessWidget {
  const _DateTimeRow({
    required this.label,
    required this.value,
    required this.allDay,
    required this.onTap,
  });

  final String label;
  final DateTime value;
  final bool allDay;
  final VoidCallback? onTap;

  String _format(DateTime dt) {
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
    final date = '${months[dt.month - 1]} ${dt.day}, ${dt.year}';
    if (allDay) return date;
    final h = dt.hour == 0 ? 12 : (dt.hour > 12 ? dt.hour - 12 : dt.hour);
    final m = dt.minute.toString().padLeft(2, '0');
    final ampm = dt.hour >= 12 ? 'PM' : 'AM';
    return '$date · $h:$m $ampm';
  }

  @override
  Widget build(BuildContext context) {
    return InkWell(
      onTap: onTap,
      borderRadius: BorderRadius.circular(12),
      child: Padding(
        padding: const EdgeInsets.symmetric(vertical: 10),
        child: Row(
          children: [
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    label,
                    style: TextStyle(
                      color: AppTheme.textSecondary,
                      fontSize: 12,
                      fontWeight: FontWeight.w700,
                    ),
                  ),
                  const SizedBox(height: 3),
                  Text(
                    _format(value),
                    style: TextStyle(
                      color: AppTheme.textPrimary,
                      fontSize: 14,
                      fontWeight: FontWeight.w900,
                    ),
                  ),
                ],
              ),
            ),
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
