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
import '../data/notes_api.dart';
import '../domain/note.dart';

class NotesScreen extends StatefulWidget {
  const NotesScreen({super.key, required this.session});
  final StaffSession session;

  @override
  State<NotesScreen> createState() => _NotesScreenState();
}

class _NotesScreenState extends State<NotesScreen> {
  final NotesApi _api = NotesApi();
  Future<List<Note>>? _future;

  @override
  void initState() {
    super.initState();
    _reload();
  }

  void _reload() {
    setState(() {
      _future = _api.fetchNotes(
        baseUrl: widget.session.baseUrl,
        token: widget.session.token,
      );
    });
  }

  Future<void> _openEditor({Note? existing}) async {
    Haptics.light();
    final saved = await showModalBottomSheet<bool>(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (_) => _NoteEditorSheet(session: widget.session, existing: existing),
    );
    if (saved == true) _reload();
  }

  Future<void> _toggleFavorite(Note note) async {
    Haptics.light();
    try {
      await _api.toggleFavorite(
        baseUrl: widget.session.baseUrl,
        token: widget.session.token,
        noteId: note.id,
        isFavorite: !note.isFavorite,
      );
      _reload();
    } on ApiException catch (e) {
      if (!mounted) return;
      AppToast.error(context, e.message);
    }
  }

  Future<void> _confirmDelete(Note note) async {
    Haptics.warn();
    final confirmed = await showDialog<bool>(
      context: context,
      builder: (dialogContext) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
        title: Text(
          'Delete note?',
          style: TextStyle(
            fontWeight: FontWeight.w900,
            color: AppTheme.textPrimary,
          ),
        ),
        content: Text(
          '"${note.displayTitle}" will be removed.',
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
      await _api.deleteNote(
        baseUrl: widget.session.baseUrl,
        token: widget.session.token,
        noteId: note.id,
      );
      if (!mounted) return;
      AppToast.success(context, 'Note deleted.');
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
        onPressed: () => _openEditor(),
        icon: const Icon(PhosphorIconsBold.plus, size: 18),
        label: const Text(
          'New note',
          style: TextStyle(fontWeight: FontWeight.w800),
        ),
      ),
      body: SafeArea(
        bottom: false,
        child: FutureBuilder<List<Note>>(
          future: _future,
          builder: (context, snapshot) {
            final loading = snapshot.connectionState == ConnectionState.waiting;
            final error = snapshot.error;
            final notes = snapshot.data ?? const <Note>[];
            final favorites = notes.where((n) => n.isFavorite).toList();
            final others = notes.where((n) => !n.isFavorite).toList();

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
                    title: 'Notes',
                    subtitle: 'Your saved notes',
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
                          child: SkeletonCard(child: SizedBox(height: 72)),
                        ),
                      ),
                    )
                  else if (error != null && snapshot.data == null)
                    _ErrorCard(
                      message: error is ApiException
                          ? error.message
                          : 'Unable to load notes.',
                      onRetry: _reload,
                    )
                  else if (notes.isEmpty)
                    const _EmptyState()
                  else ...[
                    if (favorites.isNotEmpty) ...[
                      const _SectionHeader(
                        icon: PhosphorIconsFill.star,
                        title: 'Favorites',
                      ),
                      const SizedBox(height: 10),
                      for (var i = 0; i < favorites.length; i++)
                        Padding(
                          padding: const EdgeInsets.only(bottom: 10),
                          child: _NoteCard(
                            note: favorites[i],
                            onTap: () => _openEditor(existing: favorites[i]),
                            onFavorite: () => _toggleFavorite(favorites[i]),
                            onDelete: () => _confirmDelete(favorites[i]),
                          ),
                        ),
                      const SizedBox(height: 14),
                    ],
                    if (others.isNotEmpty) ...[
                      if (favorites.isNotEmpty)
                        const _SectionHeader(
                          icon: PhosphorIconsBold.notebook,
                          title: 'All notes',
                        ),
                      if (favorites.isNotEmpty) const SizedBox(height: 10),
                      for (var i = 0; i < others.length; i++)
                        Padding(
                          padding: const EdgeInsets.only(bottom: 10),
                          child: FadeSlide(
                            delay: Duration(milliseconds: 40 * i),
                            child: _NoteCard(
                              note: others[i],
                              onTap: () => _openEditor(existing: others[i]),
                              onFavorite: () => _toggleFavorite(others[i]),
                              onDelete: () => _confirmDelete(others[i]),
                            ),
                          ),
                        ),
                    ],
                  ],
                ],
              ),
            );
          },
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
        Icon(icon, size: 15, color: AppTheme.primaryDark),
        const SizedBox(width: 8),
        Text(
          title,
          style: TextStyle(
            fontSize: 13.5,
            fontWeight: FontWeight.w900,
            color: AppTheme.textPrimary,
          ),
        ),
      ],
    );
  }
}

class _NoteCard extends StatelessWidget {
  const _NoteCard({
    required this.note,
    required this.onTap,
    required this.onFavorite,
    required this.onDelete,
  });

  final Note note;
  final VoidCallback onTap;
  final VoidCallback onFavorite;
  final VoidCallback onDelete;

  @override
  Widget build(BuildContext context) {
    return PressScale(
      onTap: onTap,
      child: Container(
        padding: const EdgeInsets.all(14),
        decoration: BoxDecoration(
          color: AppTheme.surface,
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
                    note.displayTitle,
                    maxLines: 1,
                    overflow: TextOverflow.ellipsis,
                    style: TextStyle(
                      fontWeight: FontWeight.w800,
                      color: AppTheme.textPrimary,
                      fontSize: 14,
                    ),
                  ),
                ),
                GestureDetector(
                  behavior: HitTestBehavior.opaque,
                  onTap: onFavorite,
                  child: Padding(
                    padding: const EdgeInsets.only(left: 6),
                    child: Icon(
                      note.isFavorite
                          ? PhosphorIconsFill.star
                          : PhosphorIconsBold.star,
                      size: 18,
                      color: note.isFavorite
                          ? const Color(0xFFF59E0B)
                          : AppTheme.textMuted,
                    ),
                  ),
                ),
                GestureDetector(
                  behavior: HitTestBehavior.opaque,
                  onTap: onDelete,
                  child: const Padding(
                    padding: EdgeInsets.only(left: 12),
                    child: Icon(
                      PhosphorIconsBold.trash,
                      size: 17,
                      color: AppTheme.danger,
                    ),
                  ),
                ),
              ],
            ),
            if (note.description.isNotEmpty) ...[
              const SizedBox(height: 6),
              Text(
                note.description,
                maxLines: 3,
                overflow: TextOverflow.ellipsis,
                style: TextStyle(
                  color: AppTheme.textSecondary,
                  fontSize: 12.5,
                  height: 1.4,
                ),
              ),
            ],
            if (note.tags.isNotEmpty) ...[
              const SizedBox(height: 10),
              Wrap(
                spacing: 6,
                runSpacing: 6,
                children: [
                  for (final tag in note.tags) _TagChip(tag: tag),
                ],
              ),
            ],
            const SizedBox(height: 10),
            Row(
              children: [
                Icon(
                  PhosphorIconsBold.calendarBlank,
                  size: 12,
                  color: AppTheme.textMuted,
                ),
                const SizedBox(width: 5),
                Text(
                  note.dateLabel.isEmpty ? note.date : note.dateLabel,
                  style: TextStyle(
                    color: AppTheme.textMuted,
                    fontSize: 11,
                    fontWeight: FontWeight.w600,
                  ),
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }
}

class _TagChip extends StatelessWidget {
  const _TagChip({required this.tag});
  final String tag;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
      decoration: BoxDecoration(
        color: AppTheme.primarySoft,
        borderRadius: BorderRadius.circular(999),
      ),
      child: Text(
        '#$tag',
        style: const TextStyle(
          color: AppTheme.primaryDark,
          fontSize: 10.5,
          fontWeight: FontWeight.w700,
        ),
      ),
    );
  }
}

class _NoteEditorSheet extends StatefulWidget {
  const _NoteEditorSheet({required this.session, this.existing});
  final StaffSession session;
  final Note? existing;

  @override
  State<_NoteEditorSheet> createState() => _NoteEditorSheetState();
}

class _NoteEditorSheetState extends State<_NoteEditorSheet> {
  final NotesApi _api = NotesApi();
  late final TextEditingController _titleController;
  late final TextEditingController _descriptionController;
  late final TextEditingController _tagsController;
  bool _submitting = false;
  String? _error;

  bool get _isEditing => widget.existing != null;

  @override
  void initState() {
    super.initState();
    final existing = widget.existing;
    _titleController = TextEditingController(text: existing?.title ?? '');
    _descriptionController =
        TextEditingController(text: existing?.description ?? '');
    _tagsController =
        TextEditingController(text: existing?.tags.join(', ') ?? '');
  }

  @override
  void dispose() {
    _titleController.dispose();
    _descriptionController.dispose();
    _tagsController.dispose();
    super.dispose();
  }

  List<String> _parseTags() {
    return _tagsController.text
        .split(',')
        .map((e) => e.trim())
        .where((e) => e.isNotEmpty)
        .toList(growable: false);
  }

  Future<void> _save() async {
    final title = _titleController.text.trim();
    final description = _descriptionController.text.trim();
    if (title.isEmpty && description.isEmpty) {
      setState(() => _error = 'Enter a title or description.');
      Haptics.warn();
      return;
    }

    Haptics.medium();
    setState(() {
      _submitting = true;
      _error = null;
    });

    try {
      if (_isEditing) {
        await _api.updateNote(
          baseUrl: widget.session.baseUrl,
          token: widget.session.token,
          noteId: widget.existing!.id,
          title: title,
          description: description,
          tags: _parseTags(),
        );
      } else {
        await _api.createNote(
          baseUrl: widget.session.baseUrl,
          token: widget.session.token,
          title: title,
          description: description,
          tags: _parseTags(),
        );
      }
      if (!mounted) return;
      Haptics.success();
      Navigator.of(context).pop(true);
    } on ApiException catch (e) {
      if (!mounted) return;
      Haptics.warn();
      setState(() => _error = e.message);
    } finally {
      if (mounted) setState(() => _submitting = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final bottomInset = MediaQuery.of(context).viewInsets.bottom;
    return Padding(
      padding: EdgeInsets.only(bottom: bottomInset),
      child: Container(
        decoration: BoxDecoration(
          color: AppTheme.surface,
          borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
        ),
        padding: const EdgeInsets.fromLTRB(20, 12, 20, 20),
        child: SingleChildScrollView(
          child: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              Center(
                child: Container(
                  width: 40,
                  height: 4,
                  decoration: BoxDecoration(
                    color: AppTheme.border,
                    borderRadius: BorderRadius.circular(999),
                  ),
                ),
              ),
              const SizedBox(height: 16),
              Text(
                _isEditing ? 'Edit note' : 'New note',
                style: TextStyle(
                  fontSize: 18,
                  fontWeight: FontWeight.w900,
                  color: AppTheme.textPrimary,
                ),
              ),
              const SizedBox(height: 16),
              if (_error != null) ...[
                Container(
                  padding: const EdgeInsets.all(12),
                  decoration: BoxDecoration(
                    color: AppTheme.danger.withValues(alpha: 0.08),
                    borderRadius: BorderRadius.circular(12),
                  ),
                  child: Text(
                    _error!,
                    style: const TextStyle(
                      color: AppTheme.danger,
                      fontSize: 12.5,
                      fontWeight: FontWeight.w600,
                    ),
                  ),
                ),
                const SizedBox(height: 14),
              ],
              const _FieldLabel('Title'),
              TextField(
                controller: _titleController,
                textCapitalization: TextCapitalization.sentences,
                decoration: const InputDecoration(
                  contentPadding:
                      EdgeInsets.symmetric(horizontal: 14, vertical: 14),
                ),
              ),
              const SizedBox(height: 14),
              const _FieldLabel('Description'),
              TextField(
                controller: _descriptionController,
                textCapitalization: TextCapitalization.sentences,
                minLines: 3,
                maxLines: 8,
                decoration: const InputDecoration(
                  contentPadding:
                      EdgeInsets.symmetric(horizontal: 14, vertical: 14),
                ),
              ),
              const SizedBox(height: 14),
              const _FieldLabel('Tags (comma separated)'),
              TextField(
                controller: _tagsController,
                decoration: const InputDecoration(
                  contentPadding:
                      EdgeInsets.symmetric(horizontal: 14, vertical: 14),
                ),
              ),
              const SizedBox(height: 20),
              LoadingButton(
                label: _isEditing ? 'Save changes' : 'Create note',
                isLoading: _submitting,
                onPressed: _submitting ? null : _save,
              ),
            ],
          ),
        ),
      ),
    );
  }
}

class _FieldLabel extends StatelessWidget {
  const _FieldLabel(this.text);
  final String text;

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.only(left: 4, bottom: 8),
      child: Text(
        text,
        style: TextStyle(
          fontSize: 12.5,
          fontWeight: FontWeight.w700,
          color: AppTheme.textSecondary,
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
      margin: const EdgeInsets.only(top: 40),
      padding: const EdgeInsets.all(28),
      decoration: BoxDecoration(
        color: AppTheme.surface,
        borderRadius: BorderRadius.circular(20),
        border: Border.all(color: AppTheme.border),
      ),
      child: Column(
        children: [
          Container(
            width: 60,
            height: 60,
            decoration: BoxDecoration(
              color: AppTheme.primarySoft,
              borderRadius: BorderRadius.circular(18),
            ),
            child: const Icon(
              PhosphorIconsBold.notebook,
              color: AppTheme.primary,
              size: 28,
            ),
          ),
          const SizedBox(height: 16),
          Text(
            'No notes yet',
            style: TextStyle(
              fontSize: 15,
              fontWeight: FontWeight.w900,
              color: AppTheme.textPrimary,
            ),
          ),
          const SizedBox(height: 6),
          Text(
            'Tap "New note" to jot down your first note.',
            textAlign: TextAlign.center,
            style: TextStyle(
              color: AppTheme.textSecondary,
              fontSize: 12.5,
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
              Expanded(
                child: Text(
                  'Unable to load notes',
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
            style: TextStyle(
              color: AppTheme.textSecondary,
              fontSize: 12.5,
            ),
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
