import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:lucide_icons_flutter/lucide_icons.dart';

import '../../../core/theme/app_theme.dart';
import '../../../core/widgets/desktop_kit.dart';

/// A runnable entry in the command palette (navigation target or action).
class DeskCommand {
  const DeskCommand({
    required this.label,
    required this.section,
    required this.icon,
    required this.onRun,
    this.shortcut,
    this.keywords = '',
  });

  final String label;
  final String section;
  final IconData icon;
  final VoidCallback onRun;
  final String? shortcut;
  final String keywords;

  bool matches(String query) {
    if (query.isEmpty) return true;
    final haystack = '$label $section $keywords'.toLowerCase();
    return query
        .toLowerCase()
        .split(RegExp(r'\s+'))
        .where((t) => t.isNotEmpty)
        .every(haystack.contains);
  }
}

/// Opens the global command palette (⌘K / Ctrl+K). The search field is the
/// app-wide entry point for jumping anywhere and running actions.
Future<void> showCommandPalette(
  BuildContext context,
  List<DeskCommand> commands,
) async {
  final command = await showDialog<DeskCommand>(
    context: context,
    barrierColor: const Color(0x660B1526),
    builder: (_) => _CommandPalette(commands: commands),
  );
  command?.onRun();
}

class _CommandPalette extends StatefulWidget {
  const _CommandPalette({required this.commands});

  final List<DeskCommand> commands;

  @override
  State<_CommandPalette> createState() => _CommandPaletteState();
}

class _CommandPaletteState extends State<_CommandPalette> {
  final _controller = TextEditingController();
  final _scroll = ScrollController();
  String _query = '';
  int _index = 0;

  static const _rowHeight = 42.0;

  List<DeskCommand> get _results =>
      widget.commands.where((c) => c.matches(_query)).toList();

  @override
  void dispose() {
    _controller.dispose();
    _scroll.dispose();
    super.dispose();
  }

  void _move(int delta) {
    final count = _results.length;
    if (count == 0) return;
    setState(() => _index = (_index + delta + count) % count);
    final offset = _index * _rowHeight;
    if (_scroll.hasClients) {
      final view = _scroll.position.viewportDimension;
      if (offset < _scroll.offset) {
        _scroll.jumpTo(offset);
      } else if (offset + _rowHeight > _scroll.offset + view) {
        _scroll.jumpTo(offset + _rowHeight - view);
      }
    }
  }

  void _run([DeskCommand? command]) {
    final results = _results;
    final target = command ?? (results.isEmpty ? null : results[_index]);
    if (target != null) Navigator.of(context).pop(target);
  }

  @override
  Widget build(BuildContext context) {
    final results = _results;
    return Align(
      alignment: const Alignment(0, -0.6),
      child: Material(
        color: Colors.white,
        borderRadius: BorderRadius.circular(14),
        clipBehavior: Clip.antiAlias,
        elevation: 0,
        child: Container(
          width: 580,
          decoration: BoxDecoration(
            borderRadius: BorderRadius.circular(14),
            border: Border.all(color: AppTheme.border),
            boxShadow: const [
              BoxShadow(
                color: Color(0x290B1526),
                blurRadius: 40,
                offset: Offset(0, 18),
              ),
            ],
          ),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              CallbackShortcuts(
                bindings: {
                  const SingleActivator(LogicalKeyboardKey.arrowDown): () =>
                      _move(1),
                  const SingleActivator(LogicalKeyboardKey.arrowUp): () =>
                      _move(-1),
                  const SingleActivator(LogicalKeyboardKey.enter): _run,
                  // The focused text field swallows Escape, so close here.
                  const SingleActivator(LogicalKeyboardKey.escape): () =>
                      Navigator.of(context).pop(),
                },
                child: Padding(
                  padding: const EdgeInsets.symmetric(horizontal: 18),
                  child: Row(
                    children: [
                      const Icon(
                        LucideIcons.search,
                        size: 18,
                        color: AppTheme.textMuted,
                      ),
                      const SizedBox(width: 12),
                      Expanded(
                        child: TextField(
                          controller: _controller,
                          autofocus: true,
                          onChanged: (value) => setState(() {
                            _query = value.trim();
                            _index = 0;
                          }),
                          style: const TextStyle(
                            fontSize: 15.5,
                            fontWeight: FontWeight.w600,
                            color: AppTheme.textPrimary,
                          ),
                          decoration: const InputDecoration(
                            hintText: 'Search or jump to…',
                            filled: false,
                            border: InputBorder.none,
                            enabledBorder: InputBorder.none,
                            focusedBorder: InputBorder.none,
                            contentPadding: EdgeInsets.symmetric(vertical: 18),
                          ),
                        ),
                      ),
                      const KeyHint('esc'),
                    ],
                  ),
                ),
              ),
              const Divider(height: 1, color: AppTheme.border),
              ConstrainedBox(
                constraints: const BoxConstraints(maxHeight: 392),
                child: results.isEmpty
                    ? const Padding(
                        padding: EdgeInsets.symmetric(vertical: 28),
                        child: Text(
                          'No matching commands',
                          textAlign: TextAlign.center,
                          style: TextStyle(
                            color: AppTheme.textSecondary,
                            fontSize: 13,
                            fontWeight: FontWeight.w600,
                          ),
                        ),
                      )
                    : ListView.builder(
                        controller: _scroll,
                        shrinkWrap: true,
                        padding: const EdgeInsets.symmetric(vertical: 6),
                        itemCount: results.length,
                        itemExtent: _rowHeight,
                        itemBuilder: (_, i) => _PaletteRow(
                          command: results[i],
                          selected: i == _index,
                          onHover: () => setState(() => _index = i),
                          onTap: () => _run(results[i]),
                        ),
                      ),
              ),
              const Divider(height: 1, color: AppTheme.border),
              Container(
                color: AppTheme.surfaceMuted.withValues(alpha: 0.6),
                padding: const EdgeInsets.symmetric(
                  horizontal: 18,
                  vertical: 10,
                ),
                child: const Row(
                  children: [
                    KeyHint('↑↓'),
                    SizedBox(width: 6),
                    Text('navigate', style: _footStyle),
                    SizedBox(width: 14),
                    KeyHint('↵'),
                    SizedBox(width: 6),
                    Text('open', style: _footStyle),
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

const _footStyle = TextStyle(
  fontSize: 11.5,
  fontWeight: FontWeight.w600,
  color: AppTheme.textSecondary,
);

class _PaletteRow extends StatelessWidget {
  const _PaletteRow({
    required this.command,
    required this.selected,
    required this.onHover,
    required this.onTap,
  });

  final DeskCommand command;
  final bool selected;
  final VoidCallback onHover;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    return MouseRegion(
      onEnter: (_) => onHover(),
      child: GestureDetector(
        onTap: onTap,
        child: Container(
          margin: const EdgeInsets.symmetric(horizontal: 8),
          padding: const EdgeInsets.symmetric(horizontal: 12),
          decoration: BoxDecoration(
            color: selected ? AppTheme.primarySoft : null,
            borderRadius: BorderRadius.circular(8),
          ),
          child: Row(
            children: [
              Icon(
                command.icon,
                size: 16,
                color: selected ? AppTheme.primaryDark : AppTheme.textSecondary,
              ),
              const SizedBox(width: 12),
              Expanded(
                child: Text(
                  command.label,
                  maxLines: 1,
                  overflow: TextOverflow.ellipsis,
                  style: TextStyle(
                    fontSize: 13.5,
                    fontWeight: FontWeight.w700,
                    color: selected
                        ? AppTheme.textPrimary
                        : AppTheme.textPrimary.withValues(alpha: 0.85),
                  ),
                ),
              ),
              Text(
                command.section,
                style: const TextStyle(
                  fontSize: 11.5,
                  fontWeight: FontWeight.w600,
                  color: AppTheme.textMuted,
                ),
              ),
              if (command.shortcut != null) ...[
                const SizedBox(width: 10),
                KeyHint(command.shortcut!),
              ],
            ],
          ),
        ),
      ),
    );
  }
}
