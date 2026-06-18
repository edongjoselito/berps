import 'package:flutter/material.dart';
import 'package:phosphor_flutter/phosphor_flutter.dart';

import '../../../core/network/api_exception.dart';
import '../../../core/theme/app_theme.dart';
import '../../../core/utils/date_formatters.dart';
import '../../../core/utils/haptics.dart';
import '../../../core/utils/responsive.dart';
import '../../../core/widgets/app_toast.dart';
import '../../../core/widgets/skeleton.dart';
import '../../auth/domain/staff_session.dart';
import '../data/admin_api.dart';
import '../domain/admin_models.dart';
import 'admin_widgets.dart';

class AdminTasksTab extends StatefulWidget {
  const AdminTasksTab({super.key, required this.session, required this.onMenu});
  final StaffSession session;
  final VoidCallback onMenu;

  @override
  State<AdminTasksTab> createState() => _AdminTasksTabState();
}

class _AdminTasksTabState extends State<AdminTasksTab> {
  final AdminApi _api = AdminApi();
  late Future<AdminTasksData> _future;
  String _status = 'open';

  @override
  void initState() {
    super.initState();
    _future = _load();
  }

  Future<AdminTasksData> _load() => _api.fetchTasks(
        baseUrl: widget.session.baseUrl,
        token: widget.session.token,
        status: _status,
      );

  void _reload() => setState(() => _future = _load());

  void _setStatus(String s) {
    Haptics.light();
    setState(() {
      _status = s;
      _future = _load();
    });
  }

  Future<void> _openCreate(AdminTasksData data) async {
    final created = await showModalBottomSheet<bool>(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (_) => _CreateTaskSheet(
        session: widget.session,
        api: _api,
        projects: data.projects,
        staff: data.staff,
      ),
    );
    if (created == true && mounted) _reload();
  }

  @override
  Widget build(BuildContext context) {
    final gutter = context.gutter;
    return Scaffold(
      backgroundColor: AppTheme.background,
      floatingActionButton: FutureBuilder<AdminTasksData>(
        future: _future,
        builder: (context, snapshot) {
          if (!snapshot.hasData) return const SizedBox.shrink();
          return FloatingActionButton.extended(
            onPressed: () => _openCreate(snapshot.data!),
            backgroundColor: AppTheme.primaryDark,
            icon: const Icon(PhosphorIconsBold.plus, color: Colors.white),
            label: const Text('New task',
                style: TextStyle(color: Colors.white, fontWeight: FontWeight.w800)),
          );
        },
      ),
      body: RefreshIndicator(
        color: AppTheme.primary,
        onRefresh: () async {
          _reload();
          await _future;
        },
        child: FutureBuilder<AdminTasksData>(
          future: _future,
          builder: (context, snapshot) {
            return ListView(
              physics: const AlwaysScrollableScrollPhysics(),
              padding: EdgeInsets.fromLTRB(gutter, 12, gutter, 96),
              children: [
                AdminHeader(
                  title: 'Tasks',
                  subtitle: 'Projects & task board',
                  onMenu: widget.onMenu,
                ),
                const SizedBox(height: 16),
                AdminFilterBar(
                  options: const {
                    'open': 'Open',
                    'closed': 'Closed',
                    'all': 'All',
                  },
                  selected: _status,
                  onSelected: _setStatus,
                ),
                const SizedBox(height: 16),
                if (snapshot.connectionState == ConnectionState.waiting)
                  ...List.generate(
                    4,
                    (_) => const Padding(
                      padding: EdgeInsets.only(bottom: 12),
                      child: Skeleton(height: 96, radius: 18),
                    ),
                  )
                else if (snapshot.hasError)
                  Padding(
                    padding: const EdgeInsets.only(top: 50),
                    child: AdminErrorView(
                      message: snapshot.error.toString(),
                      onRetry: _reload,
                    ),
                  )
                else if (snapshot.data!.tasks.isEmpty)
                  const Padding(
                    padding: EdgeInsets.only(top: 50),
                    child: AdminEmptyView(
                      icon: PhosphorIconsFill.checkCircle,
                      title: 'No tasks here',
                      message: 'Tap “New task” to assign one.',
                    ),
                  )
                else
                  ...snapshot.data!.tasks.map(_taskCard),
              ],
            );
          },
        ),
      ),
    );
  }

  Widget _taskCard(AdminTask t) {
    final pColor = priorityColor(t.priorityValue);
    return Container(
      margin: const EdgeInsets.only(bottom: 12),
      padding: const EdgeInsets.all(15),
      decoration: BoxDecoration(
        color: AppTheme.surface,
        borderRadius: BorderRadius.circular(18),
        border: Border.all(color: AppTheme.border),
        boxShadow: AppTheme.shadowSoft,
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Expanded(
                child: Text(
                  t.title,
                  style: TextStyle(
                    fontWeight: FontWeight.w800,
                    fontSize: 15,
                    color: AppTheme.textPrimary,
                  ),
                ),
              ),
              const SizedBox(width: 8),
              Container(
                padding:
                    const EdgeInsets.symmetric(horizontal: 9, vertical: 4),
                decoration: BoxDecoration(
                  color: pColor.withValues(alpha: 0.10),
                  borderRadius: BorderRadius.circular(999),
                ),
                child: Text(
                  t.priorityLabel,
                  style: TextStyle(
                    fontSize: 10.5,
                    fontWeight: FontWeight.w800,
                    color: pColor,
                  ),
                ),
              ),
            ],
          ),
          const SizedBox(height: 6),
          Row(
            children: [
              Icon(PhosphorIconsBold.user, size: 13, color: AppTheme.textMuted),
              const SizedBox(width: 5),
              Expanded(
                child: Text(
                  t.assignedName,
                  maxLines: 1,
                  overflow: TextOverflow.ellipsis,
                  style: TextStyle(
                    fontSize: 12.5,
                    fontWeight: FontWeight.w600,
                    color: AppTheme.textSecondary,
                  ),
                ),
              ),
            ],
          ),
          const SizedBox(height: 3),
          Row(
            children: [
              Icon(PhosphorIconsBold.folderSimple,
                  size: 13, color: AppTheme.textMuted),
              const SizedBox(width: 5),
              Expanded(
                child: Text(
                  t.projectName,
                  maxLines: 1,
                  overflow: TextOverflow.ellipsis,
                  style: TextStyle(
                    fontSize: 12.5,
                    color: AppTheme.textSecondary,
                  ),
                ),
              ),
            ],
          ),
          const SizedBox(height: 10),
          Row(
            children: [
              Container(
                padding:
                    const EdgeInsets.symmetric(horizontal: 9, vertical: 5),
                decoration: BoxDecoration(
                  color: dueMetaColor(t.dueMetaType).withValues(alpha: 0.10),
                  borderRadius: BorderRadius.circular(8),
                ),
                child: Text(
                  t.dueMetaLabel,
                  style: TextStyle(
                    fontSize: 11,
                    fontWeight: FontWeight.w700,
                    color: dueMetaColor(t.dueMetaType),
                  ),
                ),
              ),
              const Spacer(),
              Container(
                padding:
                    const EdgeInsets.symmetric(horizontal: 9, vertical: 5),
                decoration: BoxDecoration(
                  color: t.isOpen
                      ? AppTheme.primarySoft
                      : AppTheme.success.withValues(alpha: 0.10),
                  borderRadius: BorderRadius.circular(8),
                ),
                child: Text(
                  t.isOpen ? 'Open' : 'Closed',
                  style: TextStyle(
                    fontSize: 11,
                    fontWeight: FontWeight.w800,
                    color: t.isOpen ? AppTheme.primaryDark : AppTheme.success,
                  ),
                ),
              ),
            ],
          ),
          if (t.adminComment.trim().isNotEmpty) ...[
            const SizedBox(height: 10),
            Container(
              padding: const EdgeInsets.all(10),
              decoration: BoxDecoration(
                color: AppTheme.surfaceMuted,
                borderRadius: BorderRadius.circular(10),
              ),
              child: Text(
                t.adminComment,
                style: TextStyle(
                  fontSize: 12,
                  color: AppTheme.textSecondary,
                  height: 1.4,
                ),
              ),
            ),
          ],
        ],
      ),
    );
  }
}

class _CreateTaskSheet extends StatefulWidget {
  const _CreateTaskSheet({
    required this.session,
    required this.api,
    required this.projects,
    required this.staff,
  });

  final StaffSession session;
  final AdminApi api;
  final List<ProjectOption> projects;
  final List<StaffOption> staff;

  @override
  State<_CreateTaskSheet> createState() => _CreateTaskSheetState();
}

class _CreateTaskSheetState extends State<_CreateTaskSheet> {
  final _taskCtrl = TextEditingController();
  final _attachmentCtrl = TextEditingController();
  int? _projectId;
  int? _assignedId;
  String _priority = '2';
  DateTime _reported = DateTime.now();
  DateTime _due = DateTime.now();
  bool _saving = false;

  @override
  void dispose() {
    _taskCtrl.dispose();
    _attachmentCtrl.dispose();
    super.dispose();
  }

  String _fmt(DateTime d) =>
      '${d.year}-${d.month.toString().padLeft(2, '0')}-${d.day.toString().padLeft(2, '0')}';

  Future<void> _pickDate(bool reported) async {
    final initial = reported ? _reported : _due;
    final picked = await showDatePicker(
      context: context,
      initialDate: initial,
      firstDate: DateTime(2020),
      lastDate: DateTime(2100),
    );
    if (picked != null) {
      setState(() {
        if (reported) {
          _reported = picked;
        } else {
          _due = picked;
        }
      });
    }
  }

  Future<void> _submit() async {
    if (_taskCtrl.text.trim().isEmpty) {
      AppToast.warning(context, 'Task name is required.');
      return;
    }
    if (_projectId == null) {
      AppToast.warning(context, 'Please select a project.');
      return;
    }
    if (_assignedId == null) {
      AppToast.warning(context, 'Please assign the task to someone.');
      return;
    }
    setState(() => _saving = true);
    try {
      await widget.api.createTask(
        baseUrl: widget.session.baseUrl,
        token: widget.session.token,
        task: _taskCtrl.text.trim(),
        projectId: _projectId!,
        assignedPersonId: _assignedId!,
        priority: _priority,
        reportedDate: _fmt(_reported),
        dueDate: _fmt(_due),
        attachmentLink: _attachmentCtrl.text.trim(),
      );
      if (!mounted) return;
      AppToast.success(context, 'Task added successfully.');
      Navigator.of(context).pop(true);
    } on ApiException catch (e) {
      if (!mounted) return;
      setState(() => _saving = false);
      AppToast.error(context, e.message);
    }
  }

  @override
  Widget build(BuildContext context) {
    final bottom = MediaQuery.of(context).viewInsets.bottom;
    return Padding(
      padding: EdgeInsets.only(bottom: bottom),
      child: Container(
        decoration: BoxDecoration(
          color: AppTheme.surface,
          borderRadius: BorderRadius.vertical(top: Radius.circular(26)),
        ),
        padding: const EdgeInsets.fromLTRB(20, 14, 20, 24),
        child: SingleChildScrollView(
          child: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Center(
                child: Container(
                  width: 42,
                  height: 4,
                  decoration: BoxDecoration(
                    color: AppTheme.border,
                    borderRadius: BorderRadius.circular(99),
                  ),
                ),
              ),
              const SizedBox(height: 16),
              Text(
                'New task',
                style: TextStyle(
                  fontWeight: FontWeight.w900,
                  fontSize: 20,
                  color: AppTheme.textPrimary,
                ),
              ),
              const SizedBox(height: 16),
              _label('Task'),
              TextField(
                controller: _taskCtrl,
                textCapitalization: TextCapitalization.sentences,
                decoration: const InputDecoration(hintText: 'What needs doing?'),
              ),
              const SizedBox(height: 14),
              _label('Project'),
              _dropdown<int>(
                value: _projectId,
                hint: 'Select project',
                items: widget.projects
                    .map((p) => DropdownMenuItem(value: p.id, child: Text(p.name)))
                    .toList(),
                onChanged: (v) => setState(() => _projectId = v),
              ),
              const SizedBox(height: 14),
              _label('Assign to'),
              _dropdown<int>(
                value: _assignedId,
                hint: 'Select employee',
                items: widget.staff
                    .map((s) =>
                        DropdownMenuItem(value: s.userId, child: Text(s.name)))
                    .toList(),
                onChanged: (v) => setState(() => _assignedId = v),
              ),
              const SizedBox(height: 14),
              _label('Priority'),
              Row(
                children: [
                  _priorityChip('1', 'High', AppTheme.danger),
                  const SizedBox(width: 8),
                  _priorityChip('2', 'Medium', AppTheme.warning),
                  const SizedBox(width: 8),
                  _priorityChip('3', 'Low', AppTheme.success),
                ],
              ),
              const SizedBox(height: 14),
              Row(
                children: [
                  Expanded(child: _dateField('Reported', _reported, () => _pickDate(true))),
                  const SizedBox(width: 12),
                  Expanded(child: _dateField('Due', _due, () => _pickDate(false))),
                ],
              ),
              const SizedBox(height: 14),
              _label('Attachment link (optional)'),
              TextField(
                controller: _attachmentCtrl,
                keyboardType: TextInputType.url,
                decoration: const InputDecoration(hintText: 'https://…'),
              ),
              const SizedBox(height: 22),
              FilledButton(
                onPressed: _saving ? null : _submit,
                child: _saving
                    ? const SizedBox(
                        width: 22,
                        height: 22,
                        child: CircularProgressIndicator(
                          strokeWidth: 2.4,
                          color: Colors.white,
                        ),
                      )
                    : const Text('Create task'),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _label(String text) => Padding(
        padding: const EdgeInsets.only(bottom: 6),
        child: Text(
          text,
          style: TextStyle(
            fontWeight: FontWeight.w700,
            fontSize: 13,
            color: AppTheme.textSecondary,
          ),
        ),
      );

  Widget _dropdown<T>({
    required T? value,
    required String hint,
    required List<DropdownMenuItem<T>> items,
    required ValueChanged<T?> onChanged,
  }) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 14),
      decoration: BoxDecoration(
        color: AppTheme.surfaceMuted,
        borderRadius: BorderRadius.circular(14),
        border: Border.all(color: AppTheme.border),
      ),
      child: DropdownButtonHideUnderline(
        child: DropdownButton<T>(
          value: value,
          isExpanded: true,
          hint: Text(hint,
              style: TextStyle(color: AppTheme.textMuted, fontSize: 14)),
          items: items,
          onChanged: onChanged,
        ),
      ),
    );
  }

  Widget _priorityChip(String value, String label, Color color) {
    final active = _priority == value;
    return Expanded(
      child: GestureDetector(
        onTap: () => setState(() => _priority = value),
        child: Container(
          padding: const EdgeInsets.symmetric(vertical: 11),
          alignment: Alignment.center,
          decoration: BoxDecoration(
            color: active ? color.withValues(alpha: 0.12) : AppTheme.surfaceMuted,
            borderRadius: BorderRadius.circular(12),
            border: Border.all(
              color: active ? color : AppTheme.border,
              width: active ? 1.4 : 1,
            ),
          ),
          child: Text(
            label,
            style: TextStyle(
              fontWeight: FontWeight.w800,
              fontSize: 13,
              color: active ? color : AppTheme.textSecondary,
            ),
          ),
        ),
      ),
    );
  }

  Widget _dateField(String label, DateTime value, VoidCallback onTap) {
    return GestureDetector(
      onTap: onTap,
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          _label(label),
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 14),
            decoration: BoxDecoration(
              color: AppTheme.surfaceMuted,
              borderRadius: BorderRadius.circular(14),
              border: Border.all(color: AppTheme.border),
            ),
            child: Row(
              children: [
                Icon(PhosphorIconsBold.calendarBlank,
                    size: 16, color: AppTheme.textSecondary),
                const SizedBox(width: 8),
                Expanded(
                  child: Text(
                    formatCompactDate(_fmt(value)),
                    style: TextStyle(
                      fontSize: 13,
                      fontWeight: FontWeight.w600,
                      color: AppTheme.textPrimary,
                    ),
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
