import 'package:flutter/material.dart';
import 'package:phosphor_flutter/phosphor_flutter.dart';

import '../../../core/network/api_exception.dart';
import '../../../core/theme/app_theme.dart';
import '../../../core/utils/date_formatters.dart';
import '../../../core/utils/haptics.dart';
import '../../../core/utils/responsive.dart';
import '../../../core/widgets/animations.dart';
import '../../../core/widgets/app_toast.dart';
import '../../../core/widgets/mobile_header.dart';
import '../../../core/widgets/skeleton.dart';
import '../../auth/domain/staff_session.dart';
import '../../home/data/staff_api.dart';
import '../../notifications/presentation/notification_bell.dart';
import '../domain/staff_tasks.dart';
import 'staff_task_editor_screen.dart';

class StaffTasksTab extends StatefulWidget {
  const StaffTasksTab({
    super.key,
    required this.session,
    required this.onMenu,
    this.initialScope = '',
  });

  final StaffSession session;
  final VoidCallback onMenu;
  final String initialScope;

  @override
  State<StaffTasksTab> createState() => _StaffTasksTabState();
}

class _StaffTasksTabState extends State<StaffTasksTab> {
  final StaffApi _api = StaffApi();
  Future<StaffTasksData>? _future;
  String _status = 'open';
  String _scope = '';

  @override
  void initState() {
    super.initState();
    _scope = widget.initialScope;
    _reload();
  }

  void _reload() {
    setState(() {
      _future = _api.fetchTasks(
        baseUrl: widget.session.baseUrl,
        token: widget.session.token,
        status: _status,
        scope: _scope,
      );
    });
  }

  Future<void> _openEditor(StaffTasksData data, {int? taskId}) async {
    Haptics.light();
    final changed = await Navigator.of(context).push<bool>(
      MaterialPageRoute(
        builder: (_) => StaffTaskEditorScreen(
          session: widget.session,
          projects: data.projects,
          staffOptions: data.staffOptions,
          taskId: taskId,
        ),
      ),
    );

    if (changed == true && mounted) {
      _reload();
    }
  }

  Future<void> _openTaskActions(StaffTasksData data, StaffTask task) async {
    Haptics.light();
    final action = await showModalBottomSheet<_TaskAction>(
      context: context,
      backgroundColor: Colors.transparent,
      isScrollControlled: true,
      builder: (_) => _TaskActionSheet(task: task),
    );

    if (action == null || !mounted) return;

    switch (action) {
      case _TaskAction.view:
        await _openEditor(data, taskId: task.id);
        break;
      case _TaskAction.status:
        await _addStatus(task);
        break;
      case _TaskAction.forward:
        await _forwardTask(data, task);
        break;
      case _TaskAction.delete:
        await _confirmDelete(task);
        break;
    }
  }

  Future<void> _addStatus(StaffTask task) async {
    final noteController = TextEditingController();
    String selectedStatus = task.isClosed ? '0' : '1';

    final confirmed = await showModalBottomSheet<bool>(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (sheetContext) {
        return StatefulBuilder(
          builder: (context, setSheet) {
            return _SheetContainer(
              title: 'Add Task Status',
              subtitle: task.title,
              icon: PhosphorIconsBold.clipboardText,
              child: Padding(
                padding: EdgeInsets.fromLTRB(
                  16,
                  4,
                  16,
                  MediaQuery.of(context).viewInsets.bottom + 16,
                ),
                child: Column(
                  mainAxisSize: MainAxisSize.min,
                  crossAxisAlignment: CrossAxisAlignment.stretch,
                  children: [
                    const _SheetFieldLabel('Notes'),
                    TextField(
                      controller: noteController,
                      maxLines: 4,
                      decoration: const InputDecoration(
                        hintText: 'Share an update on this task…',
                      ),
                    ),
                    const SizedBox(height: 14),
                    const _SheetFieldLabel('Current Status'),
                    Row(
                      children: [
                        Expanded(
                          child: _StatusChoice(
                            label: 'Open',
                            icon: PhosphorIconsBold.clock,
                            selected: selectedStatus == '1',
                            onTap: () => setSheet(() => selectedStatus = '1'),
                          ),
                        ),
                        const SizedBox(width: 10),
                        Expanded(
                          child: _StatusChoice(
                            label: 'Closed',
                            icon: PhosphorIconsBold.checkCircle,
                            selected: selectedStatus == '0',
                            onTap: () => setSheet(() => selectedStatus = '0'),
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 18),
                    Row(
                      children: [
                        Expanded(
                          child: OutlinedButton(
                            onPressed: () =>
                                Navigator.of(sheetContext).pop(false),
                            child: const Text('Cancel'),
                          ),
                        ),
                        const SizedBox(width: 10),
                        Expanded(
                          child: FilledButton(
                            onPressed: () =>
                                Navigator.of(sheetContext).pop(true),
                            child: const Text('Save Status'),
                          ),
                        ),
                      ],
                    ),
                  ],
                ),
              ),
            );
          },
        );
      },
    );

    final note = noteController.text.trim();
    noteController.dispose();

    if (confirmed != true || !mounted) return;

    try {
      await _api.updateTaskStatus(
        baseUrl: widget.session.baseUrl,
        token: widget.session.token,
        taskId: task.id,
        taskStatus: selectedStatus,
        note: note,
      );
      if (!mounted) return;
      AppToast.success(context, 'Status saved.');
      _reload();
    } on ApiException catch (e) {
      if (!mounted) return;
      AppToast.error(context, e.message);
    }
  }

  Future<void> _forwardTask(StaffTasksData data, StaffTask task) async {
    final options = data.staffOptions
        .where((o) => o.userId != task.assignedPersonId)
        .toList();
    if (options.isEmpty) {
      AppToast.warning(context, 'No staff available to forward to.');
      return;
    }

    int? selectedUserId;
    final noteController = TextEditingController();

    final confirmed = await showModalBottomSheet<bool>(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (sheetContext) {
        return StatefulBuilder(
          builder: (context, setSheet) {
            return _SheetContainer(
              title: 'Forward Task',
              subtitle: task.title,
              icon: PhosphorIconsBold.arrowsLeftRight,
              child: Padding(
                padding: EdgeInsets.fromLTRB(
                  16,
                  4,
                  16,
                  MediaQuery.of(context).viewInsets.bottom + 16,
                ),
                child: Column(
                  mainAxisSize: MainAxisSize.min,
                  crossAxisAlignment: CrossAxisAlignment.stretch,
                  children: [
                    const _SheetFieldLabel('Forward To'),
                    DropdownButtonFormField<int>(
                      initialValue: selectedUserId,
                      items: options
                          .map(
                            (option) => DropdownMenuItem<int>(
                              value: option.userId,
                              child: Text(option.name),
                            ),
                          )
                          .toList(),
                      onChanged: (value) =>
                          setSheet(() => selectedUserId = value),
                      decoration: const InputDecoration(
                        hintText: 'Choose recipient',
                      ),
                    ),
                    const SizedBox(height: 14),
                    const _SheetFieldLabel('Note'),
                    TextField(
                      controller: noteController,
                      maxLines: 3,
                      decoration: const InputDecoration(
                        hintText: 'Why are you forwarding this task?',
                      ),
                    ),
                    const SizedBox(height: 18),
                    Row(
                      children: [
                        Expanded(
                          child: OutlinedButton(
                            onPressed: () =>
                                Navigator.of(sheetContext).pop(false),
                            child: const Text('Cancel'),
                          ),
                        ),
                        const SizedBox(width: 10),
                        Expanded(
                          child: FilledButton(
                            onPressed: selectedUserId == null
                                ? null
                                : () => Navigator.of(sheetContext).pop(true),
                            child: const Text('Forward'),
                          ),
                        ),
                      ],
                    ),
                  ],
                ),
              ),
            );
          },
        );
      },
    );

    final note = noteController.text.trim();
    noteController.dispose();

    if (confirmed != true || selectedUserId == null || !mounted) return;

    try {
      await _api.forwardTask(
        baseUrl: widget.session.baseUrl,
        token: widget.session.token,
        taskId: task.id,
        forwardTo: selectedUserId!,
        note: note,
      );
      if (!mounted) return;
      AppToast.success(context, 'Task forwarded.');
      _reload();
    } on ApiException catch (e) {
      if (!mounted) return;
      AppToast.error(context, e.message);
    }
  }

  Future<void> _confirmDelete(StaffTask task) async {
    Haptics.warn();
    final confirmed = await showDialog<bool>(
      context: context,
      builder: (context) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
        title: Text(
          'Delete this task?',
          style: TextStyle(
            fontWeight: FontWeight.w900,
            color: AppTheme.textPrimary,
          ),
        ),
        content: Text(
          '"${task.title}" will be permanently removed.',
          style: TextStyle(color: AppTheme.textSecondary),
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.of(context).pop(false),
            child: const Text('Cancel'),
          ),
          FilledButton(
            onPressed: () => Navigator.of(context).pop(true),
            style: FilledButton.styleFrom(
              backgroundColor: AppTheme.danger,
              minimumSize: const Size(96, 44),
            ),
            child: const Text('Delete'),
          ),
        ],
      ),
    );

    if (confirmed != true || !mounted) return;

    try {
      await _api.deleteTask(
        baseUrl: widget.session.baseUrl,
        token: widget.session.token,
        taskId: task.id,
      );
      if (!mounted) return;
      AppToast.success(context, 'Task deleted.');
      _reload();
    } on ApiException catch (e) {
      if (!mounted) return;
      AppToast.error(context, e.message);
    }
  }

  @override
  Widget build(BuildContext context) {
    return RefreshIndicator(
      color: AppTheme.primary,
      onRefresh: () async {
        Haptics.light();
        _reload();
        await _future;
      },
      child: FutureBuilder<StaffTasksData>(
        future: _future,
        builder: (context, snapshot) {
          return ListView(
            physics: const AlwaysScrollableScrollPhysics(),
            padding: EdgeInsets.fromLTRB(
              context.gutter,
              12,
              context.gutter,
              28,
            ),
            children: [
              SafeArea(
                bottom: false,
                child: MobileHeader(
                  title: 'Tasks',
                  leadingIcon: PhosphorIconsBold.list,
                  onLeadingTap: () {
                    Haptics.light();
                    widget.onMenu();
                  },
                  trailing: NotificationBell(session: widget.session),
                ),
              ),
              const SizedBox(height: 16),
              if (snapshot.connectionState == ConnectionState.waiting)
                const _TasksSkeleton()
              else if (snapshot.hasError)
                _TaskTabError(
                  message: snapshot.error is ApiException
                      ? (snapshot.error as ApiException).message
                      : snapshot.error.toString(),
                  onRetry: () {
                    Haptics.medium();
                    _reload();
                  },
                )
              else ...[
                FadeSlide(
                  delay: const Duration(milliseconds: 60),
                  child: _TaskStatsRow(stats: snapshot.data!.stats),
                ),
                const SizedBox(height: 18),
                FadeSlide(
                  delay: const Duration(milliseconds: 120),
                  child: _TaskFilters(
                    status: _status,
                    scope: _scope,
                    canAdd: snapshot.hasData,
                    onAdd: snapshot.hasData
                        ? () => _openEditor(snapshot.data!)
                        : null,
                    onStatusChanged: (value) {
                      Haptics.light();
                      setState(() => _status = value);
                      _reload();
                    },
                    onScopeChanged: (value) {
                      Haptics.light();
                      setState(() => _scope = value);
                      _reload();
                    },
                  ),
                ),
                const SizedBox(height: 18),
                if (!snapshot.data!.hasTimeInToday)
                  FadeSlide(
                    delay: const Duration(milliseconds: 180),
                    child: const _TimeInWarning(),
                  ),
                if (!snapshot.data!.hasTimeInToday) const SizedBox(height: 18),
                FadeSlide(
                  delay: const Duration(milliseconds: 190),
                  child: _TaskSectionHeader(
                    icon: PhosphorIconsBold.listChecks,
                    title: _status == 'open' ? 'My Tasks' : 'Task History',
                    count: snapshot.data!.tasks.length,
                  ),
                ),
                const SizedBox(height: 10),
                ...snapshot.data!.tasks.asMap().entries.map(
                  (entry) => Padding(
                    padding: const EdgeInsets.only(bottom: 12),
                    child: FadeSlide(
                      delay: Duration(milliseconds: 200 + 40 * entry.key),
                      child: _TaskCard(
                        task: entry.value,
                        onTap: () =>
                            _openTaskActions(snapshot.data!, entry.value),
                      ),
                    ),
                  ),
                ),
                if (snapshot.data!.tasks.isEmpty) const _TaskEmptyState(),
              ],
            ],
          );
        },
      ),
    );
  }
}

class _TaskStatsRow extends StatelessWidget {
  const _TaskStatsRow({required this.stats});

  final TaskStats stats;

  @override
  Widget build(BuildContext context) {
    final items = <_StatItem>[
      _StatItem(
        'Open',
        '${stats.open}',
        AppTheme.primary,
        PhosphorIconsBold.folderOpen,
        const [Color(0xFF2563EB), Color(0xFF1D4ED8)],
      ),
      _StatItem(
        'Due Today',
        '${stats.dueToday}',
        AppTheme.accent,
        PhosphorIconsBold.calendarCheck,
        const [Color(0xFF0891B2), Color(0xFF0E7490)],
      ),
      _StatItem(
        'Overdue',
        '${stats.overdue}',
        AppTheme.danger,
        PhosphorIconsBold.warningCircle,
        const [Color(0xFFDC2626), Color(0xFFB91C1C)],
      ),
      _StatItem(
        'Done',
        '${stats.closed}',
        AppTheme.success,
        PhosphorIconsBold.checkCircle,
        const [Color(0xFF16A34A), Color(0xFF15803D)],
      ),
    ];

    return LayoutBuilder(
      builder: (context, constraints) {
        final tileWidth = (constraints.maxWidth - 12) / 2;
        return Wrap(
          spacing: 12,
          runSpacing: 12,
          children: items.map((item) {
            return SizedBox(
              width: tileWidth,
              child: Container(
                padding: const EdgeInsets.all(14),
                decoration: BoxDecoration(
                  gradient: LinearGradient(
                    colors: item.gradient,
                    begin: Alignment.topLeft,
                    end: Alignment.bottomRight,
                  ),
                  borderRadius: BorderRadius.circular(16),
                  boxShadow: [
                    BoxShadow(
                      color: item.color.withValues(alpha: 0.25),
                      blurRadius: 14,
                      offset: const Offset(0, 6),
                    ),
                  ],
                ),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Container(
                      width: 30,
                      height: 30,
                      decoration: BoxDecoration(
                        color: AppTheme.surface.withValues(alpha: 0.15),
                        borderRadius: BorderRadius.circular(10),
                      ),
                      child: Icon(item.icon, size: 16, color: Colors.white),
                    ),
                    const SizedBox(height: 12),
                    TweenAnimationBuilder<int>(
                      tween: IntTween(
                        begin: 0,
                        end: int.tryParse(item.value) ?? 0,
                      ),
                      duration: const Duration(milliseconds: 900),
                      curve: Curves.easeOutCubic,
                      builder: (context, val, child) {
                        return Text(
                          '$val',
                          style: const TextStyle(
                            fontSize: 20,
                            fontWeight: FontWeight.w900,
                            color: Colors.white,
                            letterSpacing: -0.3,
                          ),
                        );
                      },
                    ),
                    const SizedBox(height: 2),
                    Text(
                      item.label,
                      style: TextStyle(
                        color: Colors.white.withValues(alpha: 0.85),
                        fontWeight: FontWeight.w700,
                        fontSize: 12,
                      ),
                    ),
                  ],
                ),
              ),
            );
          }).toList(),
        );
      },
    );
  }
}

class _StatItem {
  const _StatItem(this.label, this.value, this.color, this.icon, this.gradient);
  final String label;
  final String value;
  final Color color;
  final IconData icon;
  final List<Color> gradient;
}

class _AddTaskButton extends StatelessWidget {
  const _AddTaskButton({required this.canAdd, required this.onTap});

  final bool canAdd;
  final VoidCallback? onTap;

  @override
  Widget build(BuildContext context) {
    return PressScale(
      onTap: canAdd && onTap != null
          ? () {
              Haptics.medium();
              onTap!();
            }
          : null,
      child: AnimatedOpacity(
        duration: const Duration(milliseconds: 180),
        opacity: canAdd && onTap != null ? 1 : 0.55,
        child: Container(
          height: 50,
          decoration: BoxDecoration(
            color: AppTheme.primary,
            borderRadius: BorderRadius.circular(14),
            boxShadow: [
              BoxShadow(
                color: AppTheme.primary.withValues(alpha: 0.30),
                blurRadius: 14,
                offset: const Offset(0, 8),
              ),
            ],
          ),
          padding: const EdgeInsets.symmetric(horizontal: 16),
          child: Row(
            children: [
              Container(
                width: 28,
                height: 28,
                decoration: BoxDecoration(
                  color: AppTheme.surface.withValues(alpha: 0.18),
                  borderRadius: BorderRadius.circular(9),
                ),
                child: const Icon(
                  PhosphorIconsBold.plus,
                  size: 14,
                  color: Colors.white,
                ),
              ),
              const SizedBox(width: 12),
              const Expanded(
                child: Text(
                  'New task',
                  style: TextStyle(
                    color: Colors.white,
                    fontWeight: FontWeight.w800,
                    fontSize: 14,
                    letterSpacing: 0.1,
                  ),
                ),
              ),
              const Icon(
                PhosphorIconsBold.arrowRight,
                size: 14,
                color: Colors.white,
              ),
            ],
          ),
        ),
      ),
    );
  }
}

class _TaskFilters extends StatelessWidget {
  const _TaskFilters({
    required this.status,
    required this.scope,
    required this.onStatusChanged,
    required this.onScopeChanged,
    required this.canAdd,
    required this.onAdd,
  });

  final String status;
  final String scope;
  final ValueChanged<String> onStatusChanged;
  final ValueChanged<String> onScopeChanged;
  final bool canAdd;
  final VoidCallback? onAdd;

  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        _AddTaskButton(canAdd: canAdd, onTap: onAdd),
        const SizedBox(height: 14),
        Container(
          padding: const EdgeInsets.all(4),
          decoration: BoxDecoration(
            color: AppTheme.surface,
            borderRadius: BorderRadius.circular(14),
            border: Border.all(color: AppTheme.border),
          ),
          child: Row(
            children: [
              Expanded(
                child: _SegmentPill(
                  label: 'Open',
                  icon: PhosphorIconsBold.listChecks,
                  active: status == 'open',
                  onTap: () => onStatusChanged('open'),
                ),
              ),
              Expanded(
                child: _SegmentPill(
                  label: 'Closed',
                  icon: PhosphorIconsBold.checkCircle,
                  active: status == 'closed',
                  onTap: () => onStatusChanged('closed'),
                ),
              ),
              Expanded(
                child: _SegmentPill(
                  label: 'All',
                  icon: PhosphorIconsBold.rows,
                  active: status == 'all',
                  onTap: () => onStatusChanged('all'),
                ),
              ),
            ],
          ),
        ),
        const SizedBox(height: 10),
        _FilterChip(
          label: 'Forwarded Queue',
          icon: PhosphorIconsBold.arrowsLeftRight,
          selected: scope == 'forwarded',
          onSelected: (selected) => onScopeChanged(selected ? 'forwarded' : ''),
        ),
      ],
    );
  }
}

class _SegmentPill extends StatelessWidget {
  const _SegmentPill({
    required this.label,
    required this.icon,
    required this.active,
    required this.onTap,
  });

  final String label;
  final IconData icon;
  final bool active;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: onTap,
      behavior: HitTestBehavior.opaque,
      child: AnimatedContainer(
        duration: const Duration(milliseconds: 220),
        curve: Curves.easeOut,
        height: 38,
        decoration: BoxDecoration(
          color: active ? AppTheme.primary : Colors.transparent,
          borderRadius: BorderRadius.circular(10),
          boxShadow: active
              ? [
                  BoxShadow(
                    color: AppTheme.primaryDark.withValues(alpha: 0.25),
                    blurRadius: 12,
                    offset: const Offset(0, 6),
                  ),
                ]
              : null,
        ),
        child: Row(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(
              icon,
              size: 14,
              color: active ? Colors.white : AppTheme.textSecondary,
            ),
            const SizedBox(width: 6),
            Text(
              label,
              style: TextStyle(
                fontSize: 12.5,
                fontWeight: FontWeight.w800,
                color: active ? Colors.white : AppTheme.textSecondary,
              ),
            ),
          ],
        ),
      ),
    );
  }
}

class _FilterChip extends StatelessWidget {
  const _FilterChip({
    required this.label,
    required this.icon,
    required this.selected,
    required this.onSelected,
  });

  final String label;
  final IconData icon;
  final bool selected;
  final ValueChanged<bool> onSelected;

  @override
  Widget build(BuildContext context) {
    return PressScale(
      onTap: () => onSelected(!selected),
      child: AnimatedContainer(
        duration: const Duration(milliseconds: 200),
        padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
        decoration: BoxDecoration(
          color: selected
              ? AppTheme.primary.withValues(alpha: 0.10)
              : AppTheme.surface,
          borderRadius: BorderRadius.circular(12),
          border: Border.all(
            color: selected ? AppTheme.primary : AppTheme.border,
            width: selected ? 1.2 : 1,
          ),
        ),
        child: Row(
          mainAxisSize: MainAxisSize.min,
          children: [
            Icon(
              icon,
              size: 14,
              color: selected ? AppTheme.primaryDark : AppTheme.textSecondary,
            ),
            const SizedBox(width: 8),
            Text(
              label,
              style: TextStyle(
                fontSize: 12.5,
                fontWeight: FontWeight.w800,
                color: selected ? AppTheme.primaryDark : AppTheme.textSecondary,
              ),
            ),
          ],
        ),
      ),
    );
  }
}

class _TimeInWarning extends StatelessWidget {
  const _TimeInWarning();

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: AppTheme.warning.withValues(alpha: 0.10),
        borderRadius: BorderRadius.circular(14),
        border: Border.all(color: AppTheme.warning.withValues(alpha: 0.22)),
      ),
      child: Row(
        children: [
          Container(
            width: 32,
            height: 32,
            decoration: BoxDecoration(
              color: AppTheme.surface,
              borderRadius: BorderRadius.circular(10),
            ),
            child: const Icon(
              PhosphorIconsBold.warningCircle,
              color: AppTheme.warning,
              size: 16,
            ),
          ),
          const SizedBox(width: 12),
          Expanded(
            child: Text(
              'Task creation and updates require a time-in for today.',
              style: TextStyle(
                color: AppTheme.textPrimary,
                fontSize: 12.5,
                fontWeight: FontWeight.w600,
                height: 1.4,
              ),
            ),
          ),
        ],
      ),
    );
  }
}

class _TaskSectionHeader extends StatelessWidget {
  const _TaskSectionHeader({
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

class _TaskCard extends StatelessWidget {
  const _TaskCard({required this.task, required this.onTap});

  final StaffTask task;
  final VoidCallback onTap;

  Color get _priorityColor {
    final p = task.priorityLabel.toLowerCase();
    if (p.contains('high')) return AppTheme.danger;
    if (p.contains('medium')) return AppTheme.warning;
    if (p.contains('low')) return AppTheme.success;
    return AppTheme.textSecondary;
  }

  @override
  Widget build(BuildContext context) {
    final dueColor = switch (task.dueMetaType) {
      'overdue' => AppTheme.danger,
      'due_today' => AppTheme.warning,
      'undated' => AppTheme.textSecondary,
      _ => AppTheme.success,
    };

    return PressScale(
      onTap: onTap,
      borderRadius: BorderRadius.circular(20),
      child: Container(
        padding: const EdgeInsets.all(16),
        decoration: BoxDecoration(
          color: AppTheme.surface,
          borderRadius: BorderRadius.circular(20),
          border: Border.all(color: AppTheme.border),
          boxShadow: AppTheme.shadowSoft,
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Container(
                  width: 4,
                  height: 36,
                  decoration: BoxDecoration(
                    color: dueColor,
                    borderRadius: BorderRadius.circular(2),
                  ),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        task.title,
                        maxLines: 2,
                        overflow: TextOverflow.ellipsis,
                        style: TextStyle(
                          fontWeight: FontWeight.w900,
                          color: AppTheme.textPrimary,
                          fontSize: 14.5,
                          height: 1.3,
                        ),
                      ),
                      if (task.projectName.isNotEmpty) ...[
                        const SizedBox(height: 4),
                        Text(
                          task.projectName,
                          style: const TextStyle(
                            color: AppTheme.primaryDark,
                            fontWeight: FontWeight.w700,
                            fontSize: 12,
                          ),
                        ),
                      ],
                    ],
                  ),
                ),
              ],
            ),
            const SizedBox(height: 10),
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
              decoration: BoxDecoration(
                color: _priorityColor.withValues(alpha: 0.10),
                borderRadius: BorderRadius.circular(999),
              ),
              child: Row(
                mainAxisSize: MainAxisSize.min,
                children: [
                  Icon(PhosphorIconsBold.flag, size: 10, color: _priorityColor),
                  const SizedBox(width: 4),
                  Text(
                    task.priorityLabel,
                    style: TextStyle(
                      color: _priorityColor,
                      fontWeight: FontWeight.w900,
                      fontSize: 10.5,
                      letterSpacing: 0.4,
                    ),
                  ),
                ],
              ),
            ),
            const SizedBox(height: 12),
            Wrap(
              spacing: 6,
              runSpacing: 6,
              children: [
                _TaskMetaPill(
                  icon: PhosphorIconsBold.calendarBlank,
                  label: 'Reported ${formatCompactDate(task.reportedDate)}',
                ),
                _TaskMetaPill(
                  icon: PhosphorIconsBold.clock,
                  label: task.dueMetaLabel,
                  accent: dueColor,
                ),
                if (task.isForwardedPending)
                  _TaskMetaPill(
                    icon: PhosphorIconsBold.arrowsLeftRight,
                    label: 'Needs your first action',
                    accent: AppTheme.warning,
                  ),
              ],
            ),
            if (task.adminComment.isNotEmpty) ...[
              const SizedBox(height: 12),
              Container(
                padding: const EdgeInsets.all(10),
                decoration: BoxDecoration(
                  color: AppTheme.surfaceMuted,
                  borderRadius: BorderRadius.circular(10),
                ),
                child: Text(
                  task.adminComment,
                  style: TextStyle(
                    color: AppTheme.textSecondary,
                    fontSize: 12.5,
                    height: 1.4,
                  ),
                ),
              ),
            ],
            if (task.attachmentLink.isNotEmpty) ...[
              const SizedBox(height: 10),
              Row(
                children: [
                  Icon(
                    PhosphorIconsBold.paperclip,
                    size: 12,
                    color: AppTheme.textSecondary,
                  ),
                  SizedBox(width: 6),
                  Text(
                    'Attachment linked',
                    style: TextStyle(
                      color: AppTheme.textSecondary,
                      fontWeight: FontWeight.w700,
                      fontSize: 11.5,
                    ),
                  ),
                ],
              ),
            ],
          ],
        ),
      ),
    );
  }
}

class _TaskMetaPill extends StatelessWidget {
  const _TaskMetaPill({required this.icon, required this.label, this.accent});

  final IconData icon;
  final String label;
  final Color? accent;

  @override
  Widget build(BuildContext context) {
    final color = accent ?? AppTheme.textSecondary;
    return ConstrainedBox(
      constraints: BoxConstraints(
        maxWidth: MediaQuery.of(context).size.width - 96,
      ),
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 9, vertical: 5),
        decoration: BoxDecoration(
          color: AppTheme.surfaceMuted,
          borderRadius: BorderRadius.circular(999),
        ),
        child: Row(
          mainAxisSize: MainAxisSize.min,
          children: [
            Icon(icon, size: 11, color: color),
            const SizedBox(width: 5),
            Flexible(
              child: Text(
                label,
                overflow: TextOverflow.ellipsis,
                style: TextStyle(
                  color: AppTheme.textPrimary,
                  fontWeight: FontWeight.w700,
                  fontSize: 11,
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }
}

class _TasksSkeleton extends StatelessWidget {
  const _TasksSkeleton();

  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        LayoutBuilder(
          builder: (context, constraints) {
            final w = (constraints.maxWidth - 12) / 2;
            return Wrap(
              spacing: 12,
              runSpacing: 12,
              children: [
                for (var i = 0; i < 4; i++)
                  SizedBox(
                    width: w,
                    child: SkeletonCard(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: const [
                          Skeleton(width: 30, height: 30, radius: 10),
                          SizedBox(height: 12),
                          Skeleton(width: 60, height: 22),
                          SizedBox(height: 6),
                          Skeleton(width: 80, height: 10),
                        ],
                      ),
                    ),
                  ),
              ],
            );
          },
        ),
        const SizedBox(height: 18),
        const Skeleton(height: 46, radius: 14),
        const SizedBox(height: 12),
        const Skeleton(width: 160, height: 36, radius: 12),
        const SizedBox(height: 18),
        for (var i = 0; i < 3; i++)
          Padding(
            padding: const EdgeInsets.only(bottom: 12),
            child: SkeletonCard(
              radius: 20,
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: const [
                  Skeleton(width: 200, height: 14),
                  SizedBox(height: 6),
                  Skeleton(width: 120, height: 10),
                  SizedBox(height: 14),
                  Row(
                    children: [
                      Skeleton(width: 90, height: 24, radius: 999),
                      SizedBox(width: 8),
                      Skeleton(width: 70, height: 24, radius: 999),
                    ],
                  ),
                ],
              ),
            ),
          ),
      ],
    );
  }
}

class _TaskTabError extends StatelessWidget {
  const _TaskTabError({required this.message, required this.onRetry});

  final String message;
  final VoidCallback onRetry;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        color: AppTheme.surface,
        borderRadius: BorderRadius.circular(20),
        border: Border.all(color: AppTheme.border),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            'Unable to load tasks',
            style: TextStyle(
              fontWeight: FontWeight.w900,
              color: AppTheme.textPrimary,
            ),
          ),
          const SizedBox(height: 8),
          Text(
            message,
            style: TextStyle(color: AppTheme.textSecondary, fontSize: 13),
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

enum _TaskAction { view, status, forward, delete }

class _TaskActionSheet extends StatelessWidget {
  const _TaskActionSheet({required this.task});
  final StaffTask task;

  @override
  Widget build(BuildContext context) {
    final canDelete = task.addedBy.isNotEmpty;
    return _SheetContainer(
      title: 'Task Actions',
      subtitle: task.title,
      icon: PhosphorIconsBold.dotsThreeOutlineVertical,
      child: Padding(
        padding: EdgeInsets.fromLTRB(
          12,
          4,
          12,
          MediaQuery.of(context).viewInsets.bottom + 12,
        ),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            _ActionRow(
              icon: PhosphorIconsBold.pencilSimpleLine,
              label: 'View / Update Task',
              subtitle: 'Edit details, checklist, mark complete',
              accent: AppTheme.primaryDark,
              onTap: () => Navigator.of(context).pop(_TaskAction.view),
            ),
            _ActionRow(
              icon: PhosphorIconsBold.clipboardText,
              label: 'Add Status',
              subtitle: 'Post a note and update task status',
              accent: AppTheme.primary,
              onTap: () => Navigator.of(context).pop(_TaskAction.status),
            ),
            _ActionRow(
              icon: PhosphorIconsBold.arrowsLeftRight,
              label: 'Forward Task',
              subtitle: 'Hand it off to a teammate',
              accent: AppTheme.warning,
              onTap: () => Navigator.of(context).pop(_TaskAction.forward),
            ),
            if (canDelete)
              _ActionRow(
                icon: PhosphorIconsBold.trash,
                label: 'Delete Task',
                subtitle: 'Only the creator can delete',
                accent: AppTheme.danger,
                danger: true,
                onTap: () => Navigator.of(context).pop(_TaskAction.delete),
              ),
            const SizedBox(height: 4),
          ],
        ),
      ),
    );
  }
}

class _SheetContainer extends StatelessWidget {
  const _SheetContainer({
    required this.title,
    required this.subtitle,
    required this.icon,
    required this.child,
  });

  final String title;
  final String subtitle;
  final IconData icon;
  final Widget child;

  @override
  Widget build(BuildContext context) {
    return Container(
      decoration: BoxDecoration(
        color: AppTheme.surface,
        borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
      ),
      child: SafeArea(
        top: false,
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            const SizedBox(height: 10),
            Container(
              width: 36,
              height: 4,
              decoration: BoxDecoration(
                color: AppTheme.border,
                borderRadius: BorderRadius.circular(2),
              ),
            ),
            Padding(
              padding: const EdgeInsets.fromLTRB(20, 14, 20, 6),
              child: Row(
                children: [
                  Container(
                    width: 36,
                    height: 36,
                    decoration: BoxDecoration(
                      color: AppTheme.primarySoft,
                      borderRadius: BorderRadius.circular(11),
                    ),
                    child: Icon(icon, size: 18, color: AppTheme.primaryDark),
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          title,
                          style: TextStyle(
                            fontSize: 15.5,
                            fontWeight: FontWeight.w900,
                            color: AppTheme.textPrimary,
                          ),
                        ),
                        const SizedBox(height: 2),
                        Text(
                          subtitle,
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                          style: TextStyle(
                            fontSize: 12,
                            color: AppTheme.textSecondary,
                            fontWeight: FontWeight.w600,
                          ),
                        ),
                      ],
                    ),
                  ),
                ],
              ),
            ),
            child,
          ],
        ),
      ),
    );
  }
}

class _ActionRow extends StatelessWidget {
  const _ActionRow({
    required this.icon,
    required this.label,
    required this.subtitle,
    required this.accent,
    required this.onTap,
    this.danger = false,
  });

  final IconData icon;
  final String label;
  final String subtitle;
  final Color accent;
  final VoidCallback onTap;
  final bool danger;

  @override
  Widget build(BuildContext context) {
    return PressScale(
      onTap: () {
        Haptics.light();
        onTap();
      },
      child: Padding(
        padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 4),
        child: Container(
          padding: const EdgeInsets.all(12),
          decoration: BoxDecoration(
            color: danger
                ? AppTheme.danger.withValues(alpha: 0.04)
                : AppTheme.surfaceMuted,
            borderRadius: BorderRadius.circular(14),
            border: Border.all(
              color: danger
                  ? AppTheme.danger.withValues(alpha: 0.18)
                  : AppTheme.border,
            ),
          ),
          child: Row(
            children: [
              Container(
                width: 38,
                height: 38,
                decoration: BoxDecoration(
                  color: accent.withValues(alpha: 0.12),
                  borderRadius: BorderRadius.circular(11),
                ),
                child: Icon(icon, size: 18, color: accent),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      label,
                      style: TextStyle(
                        fontWeight: FontWeight.w800,
                        fontSize: 13.5,
                        color: danger ? AppTheme.danger : AppTheme.textPrimary,
                      ),
                    ),
                    const SizedBox(height: 2),
                    Text(
                      subtitle,
                      style: TextStyle(
                        color: AppTheme.textSecondary,
                        fontSize: 11.5,
                      ),
                    ),
                  ],
                ),
              ),
              Icon(
                PhosphorIconsBold.caretRight,
                size: 14,
                color: danger ? AppTheme.danger : AppTheme.textMuted,
              ),
            ],
          ),
        ),
      ),
    );
  }
}

class _SheetFieldLabel extends StatelessWidget {
  const _SheetFieldLabel(this.text);
  final String text;

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.only(left: 4, bottom: 6),
      child: Text(
        text.toUpperCase(),
        style: TextStyle(
          fontSize: 10.5,
          fontWeight: FontWeight.w900,
          letterSpacing: 1.2,
          color: AppTheme.textMuted,
        ),
      ),
    );
  }
}

class _StatusChoice extends StatelessWidget {
  const _StatusChoice({
    required this.label,
    required this.icon,
    required this.selected,
    required this.onTap,
  });

  final String label;
  final IconData icon;
  final bool selected;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    return PressScale(
      onTap: onTap,
      child: AnimatedContainer(
        duration: const Duration(milliseconds: 200),
        padding: const EdgeInsets.symmetric(vertical: 12, horizontal: 10),
        decoration: BoxDecoration(
          color: selected
              ? AppTheme.primary.withValues(alpha: 0.10)
              : AppTheme.surface,
          borderRadius: BorderRadius.circular(12),
          border: Border.all(
            color: selected ? AppTheme.primary : AppTheme.border,
            width: selected ? 1.4 : 1,
          ),
        ),
        child: Row(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(
              icon,
              size: 16,
              color: selected ? AppTheme.primaryDark : AppTheme.textSecondary,
            ),
            const SizedBox(width: 8),
            Text(
              label,
              style: TextStyle(
                fontWeight: FontWeight.w800,
                fontSize: 13,
                color: selected ? AppTheme.primaryDark : AppTheme.textSecondary,
              ),
            ),
          ],
        ),
      ),
    );
  }
}

class _TaskEmptyState extends StatelessWidget {
  const _TaskEmptyState();

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(24),
      decoration: BoxDecoration(
        color: AppTheme.surface,
        borderRadius: BorderRadius.circular(20),
        border: Border.all(color: AppTheme.border),
      ),
      child: Row(
        children: [
          Container(
            width: 40,
            height: 40,
            decoration: BoxDecoration(
              color: AppTheme.primarySoft,
              borderRadius: BorderRadius.circular(12),
            ),
            child: const Icon(
              PhosphorIconsBold.checkSquareOffset,
              color: AppTheme.primaryDark,
              size: 18,
            ),
          ),
          const SizedBox(width: 14),
          Expanded(
            child: Text(
              'No tasks match the current filter.',
              style: TextStyle(color: AppTheme.textSecondary, fontSize: 13),
            ),
          ),
        ],
      ),
    );
  }
}
