import 'package:flutter/material.dart';
import 'package:lucide_icons_flutter/lucide_icons.dart';

import '../../../core/network/api_exception.dart';
import '../../../core/theme/app_theme.dart';
import '../../../core/utils/haptics.dart';
import '../../../core/widgets/desktop_kit.dart';
import '../../../core/utils/date_formatters.dart';
import '../../../core/widgets/animations.dart';
import '../../../core/widgets/app_toast.dart';
import '../../../core/widgets/mobile_header.dart';
import '../../auth/domain/staff_session.dart';
import '../../home/data/staff_api.dart';
import '../domain/staff_tasks.dart';

class StaffTaskEditorScreen extends StatefulWidget {
  const StaffTaskEditorScreen({
    super.key,
    required this.session,
    required this.projects,
    required this.staffOptions,
    this.taskId,
    this.modal = false,
  });

  final StaffSession session;
  final List<ProjectOption> projects;
  final List<StaffOption> staffOptions;
  final int? taskId;

  /// Desktop only — renders as centered-dialog content (DeskModalHeader +
  /// footer action bar) instead of a full page. Ignored on mobile.
  final bool modal;

  bool get isEditing => taskId != null;

  @override
  State<StaffTaskEditorScreen> createState() => _StaffTaskEditorScreenState();
}

class _StaffTaskEditorScreenState extends State<StaffTaskEditorScreen> {
  final StaffApi _api = StaffApi();
  final _formKey = GlobalKey<FormState>();
  final _taskController = TextEditingController();
  final _attachmentController = TextEditingController();
  final List<_ChecklistDraft> _checklist = <_ChecklistDraft>[];

  Future<StaffTaskDetail>? _detailFuture;
  String _reportedDate = _today();
  String _dueDate = _today();
  String _priority = '2';
  int? _projectId;
  bool _saving = false;

  @override
  void initState() {
    super.initState();
    _projectId = widget.projects.isNotEmpty ? widget.projects.first.id : null;
    if (widget.isEditing) {
      _detailFuture = _api.fetchTaskDetail(
        baseUrl: widget.session.baseUrl,
        token: widget.session.token,
        taskId: widget.taskId!,
      );
    } else {
      _checklist.add(_ChecklistDraft());
    }
  }

  @override
  void dispose() {
    _taskController.dispose();
    _attachmentController.dispose();
    for (final item in _checklist) {
      item.dispose();
    }
    super.dispose();
  }

  void _applyDetail(StaffTaskDetail detail) {
    if (_taskController.text.isNotEmpty) return;
    final task = detail.task;
    _taskController.text = task.title;
    _attachmentController.text = task.attachmentLink;
    _reportedDate = task.reportedDate.isNotEmpty ? task.reportedDate : _today();
    _dueDate = task.dueDate.isNotEmpty ? task.dueDate : _reportedDate;
    _priority = task.priorityValue;
    _projectId = task.projectId > 0 ? task.projectId : _projectId;
    _checklist
      ..clear()
      ..addAll(
        detail.checklist.map(
          (item) => _ChecklistDraft(
            text: item.itemDescription,
            status: item.status,
            isCompleted: item.isCompleted,
          ),
        ),
      );
    if (_checklist.isEmpty) {
      _checklist.add(_ChecklistDraft());
    }
  }

  Future<void> _pickDate({
    required String currentValue,
    required ValueChanged<String> onSelected,
  }) async {
    final picked = await showDatePicker(
      context: context,
      firstDate: DateTime(2020),
      lastDate: DateTime.now().add(const Duration(days: 730)),
      initialDate: DateTime.tryParse(currentValue) ?? DateTime.now(),
    );
    if (!mounted || picked == null) return;
    onSelected(_isoDate(picked));
  }

  Future<void> _save() async {
    if (_saving || !_formKey.currentState!.validate()) return;

    setState(() => _saving = true);

    final payload = <String, dynamic>{
      'task': _taskController.text.trim(),
      'project_id': _projectId,
      'reported_date': _reportedDate,
      'due_date': _dueDate,
      'priority': _priority,
      'attachment_link': _attachmentController.text.trim(),
      'checklist_items': _serializedChecklist(),
    };

    try {
      if (widget.isEditing) {
        await _api.updateTask(
          baseUrl: widget.session.baseUrl,
          token: widget.session.token,
          taskId: widget.taskId!,
          payload: payload,
        );
        await _api.saveTaskChecklist(
          baseUrl: widget.session.baseUrl,
          token: widget.session.token,
          taskId: widget.taskId!,
          checklistItems: _serializedChecklist(),
        );
      } else {
        await _api.createTask(
          baseUrl: widget.session.baseUrl,
          token: widget.session.token,
          payload: payload,
        );
      }

      if (!mounted) return;
      Navigator.of(context).pop(true);
    } on ApiException catch (error) {
      if (!mounted) return;
      AppToast.error(context, error.message);
    } finally {
      if (mounted) {
        setState(() => _saving = false);
      }
    }
  }

  Future<void> _submitStatus(String taskStatus) async {
    final noteController = TextEditingController();
    final confirmed = await showDialog<bool>(
      context: context,
      builder: (context) => AlertDialog(
        title: Text(taskStatus == '0' ? 'Close task?' : 'Reopen task?'),
        content: TextField(
          controller: noteController,
          decoration: const InputDecoration(
            labelText: 'Note',
            hintText: 'Optional note',
          ),
          maxLines: 3,
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.of(context).pop(false),
            child: const Text('Cancel'),
          ),
          FilledButton(
            onPressed: () => Navigator.of(context).pop(true),
            child: const Text('Continue'),
          ),
        ],
      ),
    );
    final note = noteController.text.trim();
    noteController.dispose();
    if (confirmed != true || !mounted || widget.taskId == null) return;

    try {
      await _api.updateTaskStatus(
        baseUrl: widget.session.baseUrl,
        token: widget.session.token,
        taskId: widget.taskId!,
        taskStatus: taskStatus,
        note: note,
      );
      if (!mounted) return;
      Navigator.of(context).pop(true);
    } on ApiException catch (error) {
      if (!mounted) return;
      AppToast.error(context, error.message);
    }
  }

  Future<void> _forwardTask() async {
    if (widget.taskId == null || widget.staffOptions.isEmpty) return;

    int? selectedUserId;
    final noteController = TextEditingController();

    final confirmed = await showAppSheet<bool>(
      context: context,
      isScrollControlled: true,
      builder: (context) {
        return Padding(
          padding: EdgeInsets.fromLTRB(
            20,
            20,
            20,
            MediaQuery.of(context).viewInsets.bottom + 20,
          ),
          child: StatefulBuilder(
            builder: (context, setModalState) {
              return Column(
                mainAxisSize: MainAxisSize.min,
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  const Text(
                    'Forward Task',
                    style: TextStyle(fontSize: 18, fontWeight: FontWeight.w800),
                  ),
                  const SizedBox(height: 16),
                  DropdownButtonFormField<int>(
                    initialValue: selectedUserId,
                    items: widget.staffOptions
                        .map(
                          (option) => DropdownMenuItem<int>(
                            value: option.userId,
                            child: Text(option.name),
                          ),
                        )
                        .toList(),
                    onChanged: (value) => setModalState(() {
                      selectedUserId = value;
                    }),
                    decoration: const InputDecoration(labelText: 'Forward To'),
                  ),
                  const SizedBox(height: 12),
                  TextField(
                    controller: noteController,
                    maxLines: 3,
                    decoration: const InputDecoration(
                      labelText: 'Note',
                      hintText: 'Why are you forwarding this task?',
                    ),
                  ),
                  const SizedBox(height: 16),
                  Row(
                    children: [
                      Expanded(
                        child: OutlinedButton(
                          onPressed: () => Navigator.of(context).pop(false),
                          child: const Text('Cancel'),
                        ),
                      ),
                      const SizedBox(width: 12),
                      Expanded(
                        child: FilledButton(
                          onPressed: selectedUserId == null
                              ? null
                              : () => Navigator.of(context).pop(true),
                          child: const Text('Forward'),
                        ),
                      ),
                    ],
                  ),
                ],
              );
            },
          ),
        );
      },
    );

    if (confirmed != true || selectedUserId == null || !mounted) {
      noteController.dispose();
      return;
    }

    try {
      await _api.forwardTask(
        baseUrl: widget.session.baseUrl,
        token: widget.session.token,
        taskId: widget.taskId!,
        forwardTo: selectedUserId!,
        note: noteController.text.trim(),
      );
      noteController.dispose();
      if (!mounted) return;
      Navigator.of(context).pop(true);
    } on ApiException catch (error) {
      noteController.dispose();
      if (!mounted) return;
      AppToast.error(context, error.message);
    }
  }

  List<Map<String, dynamic>> _serializedChecklist() {
    return _checklist
        .where((item) => item.controller.text.trim().isNotEmpty)
        .map(
          (item) => {
            'item_description': item.controller.text.trim(),
            'status': item.status,
            'is_completed': item.isCompleted,
          },
        )
        .toList();
  }

  @override
  Widget build(BuildContext context) {
    final body = widget.isEditing
        ? FutureBuilder<StaffTaskDetail>(
            future: _detailFuture,
            builder: (context, snapshot) {
              if (snapshot.connectionState == ConnectionState.waiting) {
                return const Center(child: CircularProgressIndicator());
              }
              if (snapshot.hasError) {
                final message = snapshot.error is ApiException
                    ? (snapshot.error as ApiException).message
                    : snapshot.error.toString();
                return _TaskEditorError(message: message);
              }
              final detail = snapshot.data!;
              _applyDetail(detail);
              return _EditorBody(
                modal: widget.modal,
                formKey: _formKey,
                taskController: _taskController,
                attachmentController: _attachmentController,
                checklist: _checklist,
                projects: widget.projects,
                projectId: _projectId,
                onProjectChanged: (value) => setState(() => _projectId = value),
                priority: _priority,
                onPriorityChanged: (value) => setState(() => _priority = value),
                reportedDate: _reportedDate,
                dueDate: _dueDate,
                onPickReported: () => _pickDate(
                  currentValue: _reportedDate,
                  onSelected: (value) => setState(() => _reportedDate = value),
                ),
                onPickDue: () => _pickDate(
                  currentValue: _dueDate,
                  onSelected: (value) => setState(() => _dueDate = value),
                ),
                onAddChecklist: () =>
                    setState(() => _checklist.add(_ChecklistDraft())),
                onRemoveChecklist: (item) => setState(() {
                  item.dispose();
                  _checklist.remove(item);
                  if (_checklist.isEmpty) _checklist.add(_ChecklistDraft());
                }),
                onToggleChecklist: (item, value) =>
                    setState(() => item.isCompleted = value),
                saving: _saving,
                onSave: _save,
                detail: detail,
                onCloseTask: () => _submitStatus('0'),
                onReopenTask: () => _submitStatus('1'),
              );
            },
          )
        : _EditorBody(
            modal: widget.modal,
            formKey: _formKey,
            taskController: _taskController,
            attachmentController: _attachmentController,
            checklist: _checklist,
            projects: widget.projects,
            projectId: _projectId,
            onProjectChanged: (value) => setState(() => _projectId = value),
            priority: _priority,
            onPriorityChanged: (value) => setState(() => _priority = value),
            reportedDate: _reportedDate,
            dueDate: _dueDate,
            onPickReported: () => _pickDate(
              currentValue: _reportedDate,
              onSelected: (value) => setState(() => _reportedDate = value),
            ),
            onPickDue: () => _pickDate(
              currentValue: _dueDate,
              onSelected: (value) => setState(() => _dueDate = value),
            ),
            onAddChecklist: () =>
                setState(() => _checklist.add(_ChecklistDraft())),
            onRemoveChecklist: (item) => setState(() {
              item.dispose();
              _checklist.remove(item);
              if (_checklist.isEmpty) _checklist.add(_ChecklistDraft());
            }),
            onToggleChecklist: (item, value) =>
                setState(() => item.isCompleted = value),
            saving: _saving,
            onSave: _save,
          );

    if (AppTheme.isDesktop) {
      if (widget.modal) {
        return Material(
          color: Colors.white,
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              DeskModalHeader(
                title: widget.isEditing ? 'Update task' : 'New task',
                subtitle: widget.isEditing
                    ? 'Edit details, checklist and schedule'
                    : null,
                actions: [
                  if (widget.isEditing)
                    DeskButton(
                      label: 'Forward',
                      icon: LucideIcons.arrowLeftRight,
                      primary: false,
                      onTap: _forwardTask,
                    ),
                ],
              ),
              Flexible(child: body),
            ],
          ),
        );
      }
      return Scaffold(
        backgroundColor: AppTheme.background,
        body: SafeArea(
          child: Column(
            children: [
              MobileHeader(
                title: widget.isEditing ? 'Update Task' : 'Add Task',
                subtitle: widget.isEditing
                    ? 'Edit details, checklist and schedule'
                    : 'Create a new task',
                leadingIcon: LucideIcons.chevronLeft,
                onLeadingTap: () {
                  Haptics.light();
                  Navigator.of(context).maybePop();
                },
                trailing: widget.isEditing
                    ? DeskButton(
                        label: 'Forward',
                        icon: LucideIcons.arrowLeftRight,
                        primary: false,
                        onTap: _forwardTask,
                      )
                    : null,
              ),
              Expanded(child: body),
            ],
          ),
        ),
      );
    }

    return Scaffold(
      appBar: AppBar(
        title: Text(widget.isEditing ? 'Update Task' : 'Add Task'),
        actions: [
          if (widget.isEditing)
            IconButton(
              onPressed: _forwardTask,
              icon: const Icon(LucideIcons.arrowLeftRight),
            ),
        ],
      ),
      body: body,
    );
  }
}

class _EditorBody extends StatelessWidget {
  const _EditorBody({
    required this.modal,
    required this.formKey,
    required this.taskController,
    required this.attachmentController,
    required this.checklist,
    required this.projects,
    required this.projectId,
    required this.onProjectChanged,
    required this.priority,
    required this.onPriorityChanged,
    required this.reportedDate,
    required this.dueDate,
    required this.onPickReported,
    required this.onPickDue,
    required this.onAddChecklist,
    required this.onRemoveChecklist,
    required this.onToggleChecklist,
    required this.saving,
    required this.onSave,
    this.detail,
    this.onCloseTask,
    this.onReopenTask,
  });

  final bool modal;
  final GlobalKey<FormState> formKey;
  final TextEditingController taskController;
  final TextEditingController attachmentController;
  final List<_ChecklistDraft> checklist;
  final List<ProjectOption> projects;
  final int? projectId;
  final ValueChanged<int?> onProjectChanged;
  final String priority;
  final ValueChanged<String> onPriorityChanged;
  final String reportedDate;
  final String dueDate;
  final VoidCallback onPickReported;
  final VoidCallback onPickDue;
  final VoidCallback onAddChecklist;
  final ValueChanged<_ChecklistDraft> onRemoveChecklist;
  final void Function(_ChecklistDraft item, bool value) onToggleChecklist;
  final bool saving;
  final VoidCallback onSave;
  final StaffTaskDetail? detail;
  final VoidCallback? onCloseTask;
  final VoidCallback? onReopenTask;

  Widget _projectField() {
    return DropdownButtonFormField<int>(
      isExpanded: true,
      menuMaxHeight: 320,
      initialValue: projectId,
      items: projects
          .map(
            (project) => DropdownMenuItem<int>(
              value: project.id,
              child: Text(
                project.name,
                maxLines: 1,
                overflow: TextOverflow.ellipsis,
              ),
            ),
          )
          .toList(),
      selectedItemBuilder: (context) => projects
          .map(
            (project) => Align(
              alignment: Alignment.centerLeft,
              child: Text(
                project.name,
                maxLines: 1,
                overflow: TextOverflow.ellipsis,
              ),
            ),
          )
          .toList(),
      onChanged: onProjectChanged,
      validator: (value) =>
          value == null || value <= 0 ? 'Project is required.' : null,
      decoration: const InputDecoration(labelText: 'Project'),
    );
  }

  Widget _detailsCard() {
    return MobileSurfaceCard(
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          _projectField(),
          const SizedBox(height: 14),
          TextFormField(
            controller: taskController,
            textCapitalization: TextCapitalization.sentences,
            validator: (value) =>
                (value ?? '').trim().isEmpty ? 'Task name is required.' : null,
            decoration: const InputDecoration(labelText: 'Task'),
          ),
          const SizedBox(height: 14),
          TextFormField(
            controller: attachmentController,
            decoration: const InputDecoration(
              labelText: 'Attachment Link',
              hintText: 'https://example.com/file',
            ),
          ),
          if (!AppTheme.isDesktop) ...[
            const SizedBox(height: 14),
            Row(
              children: [
                Expanded(
                  child: _DateButton(
                    label: 'Reported Date',
                    value: formatCompactDate(reportedDate),
                    onTap: onPickReported,
                  ),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: _DateButton(
                    label: 'Due Date',
                    value: formatCompactDate(dueDate),
                    onTap: onPickDue,
                  ),
                ),
              ],
            ),
            const SizedBox(height: 14),
            _SectionLabel(icon: LucideIcons.flag, text: 'Priority'),
            const SizedBox(height: 8),
            _priorityRow(),
          ],
        ],
      ),
    );
  }

  Widget _priorityRow() {
    return Row(
      children: [
        _PriorityChip(
          label: 'High',
          color: AppTheme.danger,
          selected: priority == '1',
          onTap: () => onPriorityChanged('1'),
        ),
        const SizedBox(width: 8),
        _PriorityChip(
          label: 'Medium',
          color: AppTheme.warning,
          selected: priority == '2',
          onTap: () => onPriorityChanged('2'),
        ),
        const SizedBox(width: 8),
        _PriorityChip(
          label: 'Low',
          color: AppTheme.success,
          selected: priority == '3',
          onTap: () => onPriorityChanged('3'),
        ),
      ],
    );
  }

  Widget _checklistCard() {
    return MobileSurfaceCard(
      child: Column(
        children: [
          Row(
            children: [
              const Expanded(
                child: Text(
                  'Checklist',
                  style: TextStyle(
                    fontWeight: FontWeight.w800,
                    color: AppTheme.textPrimary,
                    fontSize: 16,
                  ),
                ),
              ),
              TextButton.icon(
                onPressed: onAddChecklist,
                icon: const Icon(LucideIcons.plus),
                label: const Text('Add Item'),
              ),
            ],
          ),
          const SizedBox(height: 8),
          ...checklist.map(
            (item) => Padding(
              padding: const EdgeInsets.only(bottom: 10),
              child: Row(
                crossAxisAlignment: CrossAxisAlignment.center,
                children: [
                  Checkbox(
                    value: item.isCompleted,
                    onChanged: (value) =>
                        onToggleChecklist(item, value ?? false),
                  ),
                  Expanded(
                    child: TextFormField(
                      controller: item.controller,
                      decoration: const InputDecoration(
                        hintText: 'Checklist item',
                      ),
                    ),
                  ),
                  IconButton(
                    onPressed: checklist.length == 1
                        ? null
                        : () => onRemoveChecklist(item),
                    icon: const Icon(LucideIcons.circleMinus),
                  ),
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _historySection() {
    if (detail == null) return const SizedBox.shrink();
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        const Text(
          'History',
          style: TextStyle(
            fontWeight: FontWeight.w800,
            color: AppTheme.textPrimary,
          ),
        ),
        const SizedBox(height: 10),
        if (detail!.history.isEmpty)
          const Text(
            'No task history yet.',
            style: TextStyle(color: AppTheme.textSecondary),
          )
        else
          ...detail!.history.map(
            (entry) => Container(
              margin: const EdgeInsets.only(bottom: 10),
              padding: const EdgeInsets.all(14),
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.circular(
                  AppTheme.isDesktop ? 14 : 16,
                ),
                border: Border.all(color: AppTheme.border),
              ),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    entry.note.isEmpty ? 'No note' : entry.note,
                    style: const TextStyle(
                      color: AppTheme.textPrimary,
                      fontWeight: FontWeight.w700,
                    ),
                  ),
                  const SizedBox(height: 6),
                  Text(
                    '${entry.postedBy} • ${formatCompactDate(entry.postedAt.isEmpty ? _today() : entry.postedAt)}',
                    style: const TextStyle(color: AppTheme.textSecondary),
                  ),
                ],
              ),
            ),
          ),
      ],
    );
  }

  /// Desktop layout: form panels in the main column; status, schedule and
  /// priority stacked in the right rail. In modal mode the actions move to a
  /// bottom bar instead of the rail.
  Widget _buildDesktopForm() {
    final task = detail?.task;
    return ListView(
      shrinkWrap: modal,
      padding: EdgeInsets.fromLTRB(
        modal ? 20 : 24,
        modal ? 16 : 12,
        modal ? 20 : 24,
        20,
      ),
      children: [
        Row(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.stretch,
                children: [
                  _detailsCard(),
                  const SizedBox(height: 16),
                  _checklistCard(),
                  const SizedBox(height: 16),
                  _historySection(),
                ],
              ),
            ),
            const SizedBox(width: 20),
            SizedBox(
              width: AppTheme.railWidth,
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.stretch,
                children: [
                  if (task != null) ...[
                    _StatusBanner(task: task),
                    const SizedBox(height: 16),
                  ],
                  MobileSurfaceCard(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        const _SectionLabel(
                          icon: LucideIcons.calendarDays,
                          text: 'Schedule',
                        ),
                        const SizedBox(height: 10),
                        _DateButton(
                          label: 'Reported Date',
                          value: formatCompactDate(reportedDate),
                          onTap: onPickReported,
                        ),
                        const SizedBox(height: 8),
                        _DateButton(
                          label: 'Due Date',
                          value: formatCompactDate(dueDate),
                          onTap: onPickDue,
                        ),
                        const SizedBox(height: 14),
                        const _SectionLabel(
                          icon: LucideIcons.flag,
                          text: 'Priority',
                        ),
                        const SizedBox(height: 8),
                        _priorityRow(),
                      ],
                    ),
                  ),
                  if (!modal) ...[
                    const SizedBox(height: 16),
                    DeskButton(
                      label: saving ? 'Saving…' : 'Save Task',
                      icon: LucideIcons.check,
                      onTap: saving ? null : onSave,
                    ),
                    if (task != null) ...[
                      const SizedBox(height: 8),
                      DeskButton(
                        label: task.isClosed ? 'Reopen Task' : 'Mark Complete',
                        icon: task.isClosed
                            ? LucideIcons.rotateCcw
                            : LucideIcons.circleCheck,
                        primary: false,
                        onTap: task.isClosed ? onReopenTask : onCloseTask,
                      ),
                    ],
                  ],
                ],
              ),
            ),
          ],
        ),
      ],
    );
  }

  @override
  Widget build(BuildContext context) {
    if (AppTheme.isDesktop) {
      if (!modal) {
        return Form(key: formKey, child: _buildDesktopForm());
      }
      final task = detail?.task;
      return Form(
        key: formKey,
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Flexible(child: _buildDesktopForm()),
            DeskModalFooter(
              children: [
                if (task != null) ...[
                  DeskButton(
                    label: task.isClosed ? 'Reopen Task' : 'Mark Complete',
                    primary: false,
                    onTap: task.isClosed ? onReopenTask : onCloseTask,
                  ),
                ],
                DeskButton(
                  label: 'Cancel',
                  primary: false,
                  onTap: () => Navigator.of(context).maybePop(),
                ),
                const SizedBox(width: 8),
                DeskButton(
                  label: saving ? 'Saving…' : 'Save Task',
                  onTap: saving ? null : onSave,
                ),
              ],
            ),
          ],
        ),
      );
    }
    return Form(
      key: formKey,
      child: ListView(
        padding: const EdgeInsets.fromLTRB(20, 16, 20, 32),
        children: [
          if (detail != null) ...[
            _StatusBanner(task: detail!.task),
            const SizedBox(height: 16),
          ],
          _detailsCard(),
          const SizedBox(height: 20),
          _checklistCard(),
          const SizedBox(height: 20),
          LoadingButton(
            label: 'Save Task',
            isLoading: saving,
            onPressed: onSave,
          ),
          if (detail != null) ...[
            const SizedBox(height: 12),
            Row(
              children: [
                Expanded(
                  child: OutlinedButton(
                    onPressed: detail!.task.isClosed
                        ? onReopenTask
                        : onCloseTask,
                    child: Text(
                      detail!.task.isClosed ? 'Reopen Task' : 'Mark Complete',
                    ),
                  ),
                ),
              ],
            ),
            const SizedBox(height: 20),
            _historySection(),
          ],
        ],
      ),
    );
  }
}

class _DateButton extends StatelessWidget {
  const _DateButton({
    required this.label,
    required this.value,
    required this.onTap,
  });

  final String label;
  final String value;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    return InkWell(
      onTap: onTap,
      borderRadius: BorderRadius.circular(12),
      child: Ink(
        padding: const EdgeInsets.all(14),
        decoration: BoxDecoration(
          color: const Color(0xFFF7F9FC),
          borderRadius: BorderRadius.circular(12),
          border: Border.all(color: AppTheme.border),
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              label,
              style: const TextStyle(
                color: AppTheme.textSecondary,
                fontWeight: FontWeight.w600,
              ),
            ),
            const SizedBox(height: 6),
            Text(
              value,
              style: const TextStyle(
                color: AppTheme.textPrimary,
                fontWeight: FontWeight.w700,
              ),
            ),
          ],
        ),
      ),
    );
  }
}

class _StatusBanner extends StatelessWidget {
  const _StatusBanner({required this.task});

  final StaffTask task;

  @override
  Widget build(BuildContext context) {
    final color = task.isClosed ? AppTheme.success : AppTheme.primaryDark;
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: color.withValues(alpha: 0.10),
        borderRadius: BorderRadius.circular(AppTheme.isDesktop ? 10 : 18),
      ),
      child: Row(
        children: [
          Icon(
            task.isClosed ? LucideIcons.circleCheck : LucideIcons.timer,
            color: color,
          ),
          const SizedBox(width: 10),
          Expanded(
            child: Text(
              '${task.status.toUpperCase()} • ${task.dueMetaLabel}',
              style: TextStyle(color: color, fontWeight: FontWeight.w800),
            ),
          ),
        ],
      ),
    );
  }
}

class _SectionLabel extends StatelessWidget {
  const _SectionLabel({required this.icon, required this.text});

  final IconData icon;
  final String text;

  @override
  Widget build(BuildContext context) {
    return Row(
      children: [
        Icon(icon, size: 12, color: AppTheme.textMuted),
        const SizedBox(width: 6),
        Text(
          text.toUpperCase(),
          style: const TextStyle(
            fontSize: 10.5,
            fontWeight: FontWeight.w900,
            letterSpacing: 1.2,
            color: AppTheme.textMuted,
          ),
        ),
      ],
    );
  }
}

class _PriorityChip extends StatelessWidget {
  const _PriorityChip({
    required this.label,
    required this.color,
    required this.selected,
    required this.onTap,
  });

  final String label;
  final Color color;
  final bool selected;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    final desktop = AppTheme.isDesktop;
    return Expanded(
      child: PressScale(
        onTap: onTap,
        child: AnimatedContainer(
          duration: const Duration(milliseconds: 200),
          height: desktop ? 34 : 40,
          alignment: Alignment.center,
          decoration: BoxDecoration(
            gradient: !desktop && selected
                ? LinearGradient(
                    colors: [color, color.withValues(alpha: 0.85)],
                    begin: Alignment.topLeft,
                    end: Alignment.bottomRight,
                  )
                : null,
            color: selected
                ? (desktop ? color.withValues(alpha: 0.14) : null)
                : AppTheme.surfaceMuted,
            borderRadius: BorderRadius.circular(desktop ? 8 : 12),
            border: Border.all(
              color: selected
                  ? (desktop ? color.withValues(alpha: 0.45) : color)
                  : (desktop ? Colors.transparent : AppTheme.border),
              width: 1,
            ),
            boxShadow: !desktop && selected
                ? [
                    BoxShadow(
                      color: color.withValues(alpha: 0.25),
                      blurRadius: 10,
                      offset: const Offset(0, 4),
                    ),
                  ]
                : null,
          ),
          child: Text(
            label,
            style: TextStyle(
              fontWeight: desktop ? FontWeight.w700 : FontWeight.w900,
              fontSize: 12.5,
              color: selected
                  ? (desktop ? color : Colors.white)
                  : AppTheme.textSecondary,
            ),
          ),
        ),
      ),
    );
  }
}

class _TaskEditorError extends StatelessWidget {
  const _TaskEditorError({required this.message});

  final String message;

  @override
  Widget build(BuildContext context) {
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(24),
        child: Text(
          message,
          style: const TextStyle(color: AppTheme.textSecondary),
          textAlign: TextAlign.center,
        ),
      ),
    );
  }
}

class _ChecklistDraft {
  _ChecklistDraft({
    String text = '',
    this.status = 'Pending',
    this.isCompleted = false,
  }) : controller = TextEditingController(text: text);

  final TextEditingController controller;
  String status;
  bool isCompleted;

  void dispose() => controller.dispose();
}

String _today() => _isoDate(DateTime.now());

String _isoDate(DateTime value) =>
    '${value.year.toString().padLeft(4, '0')}-${value.month.toString().padLeft(2, '0')}-${value.day.toString().padLeft(2, '0')}';
