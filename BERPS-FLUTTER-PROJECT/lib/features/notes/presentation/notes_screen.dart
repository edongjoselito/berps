import 'package:flutter/material.dart';
import 'package:flutter_html/flutter_html.dart';
import 'package:lucide_icons_flutter/lucide_icons.dart';
import 'package:url_launcher/url_launcher.dart';

import '../../../core/network/api_exception.dart';
import '../../../core/theme/app_theme.dart';
import '../../../core/widgets/desktop_kit.dart';
import '../../../core/utils/haptics.dart';
import '../../../core/utils/html_to_text.dart';
import '../../../core/utils/responsive.dart';
import '../../../core/widgets/animations.dart';
import '../../../core/widgets/app_toast.dart';
import '../../../core/widgets/mobile_header.dart';
import '../../../core/widgets/skeleton.dart';
import '../../auth/domain/staff_session.dart';
import '../data/notes_api.dart';
import '../domain/note.dart';

class NotesScreen extends StatefulWidget {
  const NotesScreen({
    super.key,
    required this.session,
    this.openEditorOnMount = false,
  });
  final StaffSession session;

  /// Opens the note editor immediately — used by the desktop "New note"
  /// command-palette action so the sheet appears as soon as the page lands.
  final bool openEditorOnMount;

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
    if (widget.openEditorOnMount) {
      WidgetsBinding.instance.addPostFrameCallback((_) {
        if (mounted) _openEditor();
      });
    }
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
    final saved = await showAppSheet<bool>(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (_) =>
          _NoteEditorSheet(session: widget.session, existing: existing),
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
    final bool? confirmed;
    if (AppTheme.isDesktop) {
      confirmed = await showDeskConfirm(
        context: context,
        title: 'Delete note?',
        message: '"${note.displayTitle}" will be removed.',
        confirmLabel: 'Delete',
        danger: true,
      );
    } else {
      confirmed = await showDialog<bool>(
        context: context,
        builder: (dialogContext) => AlertDialog(
          title: const Text(
            'Delete note?',
            style: TextStyle(
              fontWeight: FontWeight.w900,
              color: AppTheme.textPrimary,
            ),
          ),
          content: Text(
            '"${note.displayTitle}" will be removed.',
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
    }
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
      floatingActionButton: AppTheme.isDesktop
          ? null
          : FloatingActionButton.extended(
              backgroundColor: AppTheme.primaryDark,
              foregroundColor: Colors.white,
              onPressed: () => _openEditor(),
              icon: const Icon(LucideIcons.plus, size: 18),
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
                    leadingIcon: LucideIcons.chevronLeft,
                    onLeadingTap: () {
                      Haptics.light();
                      Navigator.of(context).maybePop();
                    },
                    trailing: AppTheme.isDesktop
                        ? Row(
                            mainAxisSize: MainAxisSize.min,
                            children: [
                              DeskIconButton(
                                icon: LucideIcons.rotateCw,
                                tooltip: 'Reload',
                                onTap: _reload,
                              ),
                              const SizedBox(width: 8),
                              DeskIconButton(
                                icon: LucideIcons.plus,
                                filled: true,
                                tooltip: 'New note',
                                onTap: () => _openEditor(),
                              ),
                            ],
                          )
                        : null,
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
                    AppErrorCard(
                      title: 'Unable to load notes',
                      message: error is ApiException
                          ? error.message
                          : 'Please try again in a moment.',
                      onRetry: _reload,
                    )
                  else if (notes.isEmpty)
                    const AppEmptyState(
                      icon: LucideIcons.notebookText,
                      title: 'No notes yet',
                      message: 'Tap "New note" to jot down your first note.',
                    )
                  else ...[
                    if (favorites.isNotEmpty) ...[
                      const _SectionHeader(
                        icon: LucideIcons.star,
                        title: 'Favorites',
                      ),
                      const SizedBox(height: 10),
                      _noteCollection(favorites),
                      const SizedBox(height: 14),
                    ],
                    if (others.isNotEmpty) ...[
                      if (favorites.isNotEmpty)
                        const _SectionHeader(
                          icon: LucideIcons.notebookText,
                          title: 'All notes',
                        ),
                      if (favorites.isNotEmpty) const SizedBox(height: 10),
                      _noteCollection(others),
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

  /// Mobile: stacked cards. Desktop: a fixed-width card grid — notes are
  /// content blocks, not table rows.
  Widget _noteCollection(List<Note> notes) {
    Widget cardFor(Note note, int i) => _NoteCard(
      note: note,
      onTap: () => _openEditor(existing: note),
      onFavorite: () => _toggleFavorite(note),
      onDelete: () => _confirmDelete(note),
    );

    if (!AppTheme.isDesktop) {
      return Column(
        children: [
          for (var i = 0; i < notes.length; i++)
            Padding(
              padding: const EdgeInsets.only(bottom: 10),
              child: FadeSlide(
                delay: Duration(milliseconds: 40 * i),
                child: cardFor(notes[i], i),
              ),
            ),
        ],
      );
    }

    return LayoutBuilder(
      builder: (context, constraints) {
        final cols = (constraints.maxWidth ~/ 330).clamp(1, 4);
        final cardWidth = (constraints.maxWidth - (cols - 1) * 12) / cols;
        return Wrap(
          spacing: 12,
          runSpacing: 12,
          children: [
            for (var i = 0; i < notes.length; i++)
              SizedBox(width: cardWidth, child: cardFor(notes[i], i)),
          ],
        );
      },
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
          style: const TextStyle(
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
          color: Colors.white,
          borderRadius: BorderRadius.circular(AppTheme.isDesktop ? 14 : 16),
          border: Border.all(color: AppTheme.border),
          boxShadow: AppTheme.isDesktop ? null : AppTheme.shadowSoft,
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
                    style: const TextStyle(
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
                      note.isFavorite ? LucideIcons.star : LucideIcons.star,
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
                      LucideIcons.trash2,
                      size: 17,
                      color: AppTheme.danger,
                    ),
                  ),
                ),
              ],
            ),
            if (note.description.isNotEmpty) ...[
              const SizedBox(height: 6),
              Html(
                data: note.description,
                shrinkWrap: true,
                style: {
                  'p': Style(
                    margin: Margins.zero,
                    color: AppTheme.textSecondary,
                    fontSize: FontSize(12.5),
                    lineHeight: const LineHeight(1.4),
                    fontWeight: FontWeight.w600,
                  ),
                  'a': Style(
                    color: AppTheme.primaryDark,
                    fontSize: FontSize(12.5),
                    fontWeight: FontWeight.w700,
                    textDecoration: TextDecoration.underline,
                  ),
                  'br': Style(height: Height(4)),
                },
                onLinkTap: (url, attributes, element) async {
                  if (url == null) return;
                  final uri = Uri.tryParse(url);
                  if (uri == null) return;
                  Haptics.light();
                  await launchUrl(uri, mode: LaunchMode.externalApplication);
                },
              ),
            ],
            if (note.tags.isNotEmpty) ...[
              const SizedBox(height: 10),
              Wrap(
                spacing: 6,
                runSpacing: 6,
                children: [for (final tag in note.tags) _TagChip(tag: tag)],
              ),
            ],
            const SizedBox(height: 10),
            Row(
              children: [
                const Icon(
                  LucideIcons.calendarDays,
                  size: 12,
                  color: AppTheme.textMuted,
                ),
                const SizedBox(width: 5),
                Text(
                  note.dateLabel.isEmpty ? note.date : note.dateLabel,
                  style: const TextStyle(
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
    _descriptionController = TextEditingController(
      text: htmlToPlainText(existing?.description ?? ''),
    );
    _tagsController = TextEditingController(
      text: existing?.tags.join(', ') ?? '',
    );
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
          borderRadius: AppTheme.isDesktop
              ? null
              : const BorderRadius.vertical(top: Radius.circular(24)),
        ),
        child: AppSheetScaffold(
          title: _isEditing ? 'Edit note' : 'New note',
          padding: const EdgeInsets.fromLTRB(20, 12, 20, 20),
          child: SingleChildScrollView(
            child: Column(
              mainAxisSize: MainAxisSize.min,
              crossAxisAlignment: CrossAxisAlignment.stretch,
              children: [
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
                    contentPadding: EdgeInsets.symmetric(
                      horizontal: 14,
                      vertical: 14,
                    ),
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
                    contentPadding: EdgeInsets.symmetric(
                      horizontal: 14,
                      vertical: 14,
                    ),
                  ),
                ),
                const SizedBox(height: 14),
                const _FieldLabel('Tags (comma separated)'),
                TextField(
                  controller: _tagsController,
                  decoration: const InputDecoration(
                    contentPadding: EdgeInsets.symmetric(
                      horizontal: 14,
                      vertical: 14,
                    ),
                  ),
                ),
                const SizedBox(height: 20),
                if (AppTheme.isDesktop)
                  Row(
                    mainAxisAlignment: MainAxisAlignment.end,
                    children: [
                      DeskButton(
                        label: 'Cancel',
                        primary: false,
                        onTap: () => Navigator.of(context).maybePop(),
                      ),
                      const SizedBox(width: 8),
                      DeskButton(
                        label: _submitting
                            ? 'Saving…'
                            : (_isEditing ? 'Save changes' : 'Create note'),
                        onTap: _submitting ? null : _save,
                      ),
                    ],
                  )
                else
                  LoadingButton(
                    label: _isEditing ? 'Save changes' : 'Create note',
                    isLoading: _submitting,
                    onPressed: _submitting ? null : _save,
                  ),
              ],
            ),
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
        style: const TextStyle(
          fontSize: 12.5,
          fontWeight: FontWeight.w700,
          color: AppTheme.textSecondary,
        ),
      ),
    );
  }
}
