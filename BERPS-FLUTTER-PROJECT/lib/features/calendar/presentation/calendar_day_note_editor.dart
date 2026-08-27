import 'package:flutter/material.dart';
import 'package:lucide_icons_flutter/lucide_icons.dart';

import '../../../core/network/api_exception.dart';
import '../../../core/theme/app_theme.dart';
import '../../../core/utils/haptics.dart';
import '../../../core/utils/html_to_text.dart';
import '../../../core/widgets/app_toast.dart';
import '../../../core/widgets/mobile_header.dart';
import '../../auth/domain/staff_session.dart';
import '../../notes/data/notes_api.dart';
import '../../notes/domain/note.dart';

/// A simple, notes-app-style editor for a calendar day note.
/// The first non-empty line of the body becomes the title; everything else is
/// the description. There is no character limit.
class CalendarDayNoteEditor extends StatefulWidget {
  const CalendarDayNoteEditor({
    super.key,
    required this.session,
    required this.date,
    this.existing,
  });

  final StaffSession session;
  final DateTime date;
  final Note? existing;

  @override
  State<CalendarDayNoteEditor> createState() => _CalendarDayNoteEditorState();
}

class _CalendarDayNoteEditorState extends State<CalendarDayNoteEditor> {
  final NotesApi _api = NotesApi();
  late final TextEditingController _bodyController;
  bool _saving = false;
  bool _deleting = false;

  bool get _isEditing => widget.existing != null;

  static final List<String> _monthNames = [
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

  static final List<String> _weekdayNames = [
    'Monday',
    'Tuesday',
    'Wednesday',
    'Thursday',
    'Friday',
    'Saturday',
    'Sunday',
  ];

  @override
  void initState() {
    super.initState();
    final body = _buildBodyFromExisting();
    _bodyController = TextEditingController(text: body);
  }

  String _buildBodyFromExisting() {
    final note = widget.existing;
    if (note == null) return '';
    final title = note.title.trim();
    final description = htmlToPlainText(note.description);
    if (title.isEmpty && description.isEmpty) return '';
    if (title.isEmpty) return description;
    if (description.isEmpty) return title;
    return '$title\n$description';
  }

  @override
  void dispose() {
    _bodyController.dispose();
    super.dispose();
  }

  String _formatDate(DateTime d) {
    return '${_weekdayNames[d.weekday - 1]}, ${_monthNames[d.month - 1]} ${d.day}, ${d.year}';
  }

  String _dateApiValue(DateTime d) {
    final y = d.year.toString().padLeft(4, '0');
    final m = d.month.toString().padLeft(2, '0');
    final day = d.day.toString().padLeft(2, '0');
    return '$y-$m-$day';
  }

  (String title, String description) _parseBody() {
    final raw = _bodyController.text.trim();
    final lines = raw.split('\n');
    String title = '';
    int titleIndex = -1;
    for (var i = 0; i < lines.length; i++) {
      final line = lines[i].trim();
      if (line.isNotEmpty) {
        title = line;
        titleIndex = i;
        break;
      }
    }
    if (titleIndex == -1) return ('', '');
    final descriptionLines = lines.sublist(titleIndex + 1);
    // Preserve leading blank lines inside the description so formatting is
    // natural, but trim trailing whitespace.
    final description = descriptionLines.join('\n').trim();
    return (title, description);
  }

  Future<void> _save() async {
    final (title, description) = _parseBody();
    if (title.isEmpty && description.isEmpty) {
      Haptics.warn();
      AppToast.error(context, 'Write something before saving.');
      return;
    }

    setState(() => _saving = true);
    Haptics.medium();

    try {
      if (_isEditing) {
        await _api.updateNote(
          baseUrl: widget.session.baseUrl,
          token: widget.session.token,
          noteId: widget.existing!.id,
          title: title,
          description: description,
          tags: const <String>[],
          date: _dateApiValue(widget.date),
        );
      } else {
        await _api.createNote(
          baseUrl: widget.session.baseUrl,
          token: widget.session.token,
          title: title,
          description: description,
          tags: const <String>[],
          date: _dateApiValue(widget.date),
        );
      }
      if (!mounted) return;
      AppToast.success(context, _isEditing ? 'Note updated.' : 'Note saved.');
      Navigator.of(context).pop(true);
    } on ApiException catch (e) {
      if (!mounted) return;
      Haptics.warn();
      AppToast.error(context, e.message);
    } finally {
      if (mounted) setState(() => _saving = false);
    }
  }

  Future<void> _delete() async {
    final note = widget.existing;
    if (note == null || _deleting) return;
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
        content: const Text(
          'This note will be permanently removed.',
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
      await _api.deleteNote(
        baseUrl: widget.session.baseUrl,
        token: widget.session.token,
        noteId: note.id,
      );
      if (!mounted) return;
      AppToast.success(context, 'Note deleted.');
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
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            MobileHeader(
              title: _formatDate(widget.date),
              subtitle: _isEditing ? 'Edit note' : 'New note',
              leadingIcon: LucideIcons.chevronLeft,
              onLeadingTap: () {
                Haptics.light();
                Navigator.of(context).maybePop();
              },
              trailing: Row(
                mainAxisSize: MainAxisSize.min,
                children: [
                  if (_isEditing)
                    IconButton(
                      onPressed: _deleting ? null : _delete,
                      icon: Icon(
                        LucideIcons.trash2,
                        color: _deleting ? AppTheme.textMuted : AppTheme.danger,
                      ),
                    ),
                  TextButton(
                    onPressed: _saving ? null : _save,
                    child: _saving
                        ? const SizedBox(
                            width: 16,
                            height: 16,
                            child: CircularProgressIndicator(
                              strokeWidth: 2,
                              color: AppTheme.primary,
                            ),
                          )
                        : const Text(
                            'Save',
                            style: TextStyle(
                              color: AppTheme.primary,
                              fontWeight: FontWeight.w800,
                            ),
                          ),
                  ),
                ],
              ),
            ),
            Expanded(
              child: TextField(
                controller: _bodyController,
                textCapitalization: TextCapitalization.sentences,
                keyboardType: TextInputType.multiline,
                maxLines: null,
                expands: true,
                textAlignVertical: TextAlignVertical.top,
                style: const TextStyle(
                  fontSize: 16,
                  height: 1.55,
                  color: AppTheme.textPrimary,
                  fontWeight: FontWeight.w500,
                ),
                decoration: InputDecoration(
                  filled: true,
                  fillColor: AppTheme.background,
                  hintText: 'Start typing your note...',
                  hintStyle: TextStyle(
                    color: AppTheme.textMuted.withValues(alpha: 0.7),
                    fontSize: 16,
                    fontWeight: FontWeight.w500,
                  ),
                  contentPadding: const EdgeInsets.fromLTRB(16, 16, 16, 32),
                  border: InputBorder.none,
                  enabledBorder: InputBorder.none,
                  focusedBorder: InputBorder.none,
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }
}
