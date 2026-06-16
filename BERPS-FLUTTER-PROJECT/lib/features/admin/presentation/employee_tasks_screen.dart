import 'package:flutter/material.dart';
import 'package:phosphor_flutter/phosphor_flutter.dart';

import '../../../core/theme/app_theme.dart';
import '../../../core/utils/date_formatters.dart';
import '../../../core/utils/haptics.dart';
import '../../../core/utils/responsive.dart';
import '../../../core/widgets/skeleton.dart';
import '../../auth/domain/staff_session.dart';
import '../data/admin_api.dart';
import '../domain/admin_models.dart';
import 'admin_widgets.dart';

class EmployeeTasksScreen extends StatefulWidget {
  const EmployeeTasksScreen({super.key, required this.session});
  final StaffSession session;

  @override
  State<EmployeeTasksScreen> createState() => _EmployeeTasksScreenState();
}

class _EmployeeTasksScreenState extends State<EmployeeTasksScreen> {
  final AdminApi _api = AdminApi();
  late Future<List<EmployeeTaskGroup>> _future;
  String _filter = 'all';

  @override
  void initState() {
    super.initState();
    _future = _load();
  }

  Future<List<EmployeeTaskGroup>> _load() => _api.fetchEmployeeTasks(
        baseUrl: widget.session.baseUrl,
        token: widget.session.token,
        taskFilter: _filter,
      );

  void _reload() => setState(() => _future = _load());

  void _setFilter(String f) {
    Haptics.light();
    setState(() {
      _filter = f;
      _future = _load();
    });
  }

  @override
  Widget build(BuildContext context) {
    final gutter = context.gutter;
    return Scaffold(
      backgroundColor: AppTheme.background,
      body: RefreshIndicator(
        color: AppTheme.primary,
        onRefresh: () async {
          _reload();
          await _future;
        },
        child: FutureBuilder<List<EmployeeTaskGroup>>(
          future: _future,
          builder: (context, snapshot) {
            return ListView(
              physics: const AlwaysScrollableScrollPhysics(),
              padding: EdgeInsets.fromLTRB(gutter, 12, gutter, 28),
              children: [
                AdminHeader(
                  title: 'Employee tasks',
                  subtitle: 'Pending tasks per employee',
                  onBack: () => Navigator.pop(context),
                ),
                const SizedBox(height: 16),
                AdminFilterBar(
                  options: const {
                    'all': 'All',
                    'with_tasks': 'With tasks',
                    'without_tasks': 'No tasks',
                  },
                  selected: _filter,
                  onSelected: _setFilter,
                ),
                const SizedBox(height: 16),
                if (snapshot.connectionState == ConnectionState.waiting)
                  ...List.generate(
                    5,
                    (_) => const Padding(
                      padding: EdgeInsets.only(bottom: 10),
                      child: Skeleton(height: 64, radius: 16),
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
                else if (snapshot.data!.isEmpty)
                  const Padding(
                    padding: EdgeInsets.only(top: 50),
                    child: AdminEmptyView(
                      icon: PhosphorIconsFill.usersThree,
                      title: 'No employees found',
                    ),
                  )
                else
                  ...snapshot.data!.map(_employeeTile),
              ],
            );
          },
        ),
      ),
    );
  }

  Widget _employeeTile(EmployeeTaskGroup e) {
    return Container(
      margin: const EdgeInsets.only(bottom: 10),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: AppTheme.border),
        boxShadow: AppTheme.shadowSoft,
      ),
      child: Theme(
        data: Theme.of(context).copyWith(dividerColor: Colors.transparent),
        child: ExpansionTile(
          tilePadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 4),
          childrenPadding: const EdgeInsets.fromLTRB(14, 0, 14, 12),
          shape: const Border(),
          leading: Container(
            width: 42,
            height: 42,
            alignment: Alignment.center,
            decoration: BoxDecoration(
              color: AppTheme.primarySoft,
              borderRadius: BorderRadius.circular(12),
            ),
            child: Text(
              e.name.isNotEmpty ? e.name[0].toUpperCase() : '?',
              style: const TextStyle(
                fontWeight: FontWeight.w900,
                color: AppTheme.primaryDark,
              ),
            ),
          ),
          title: Text(
            e.name.isEmpty ? '(no name)' : e.name,
            style: const TextStyle(
              fontWeight: FontWeight.w800,
              fontSize: 14.5,
              color: AppTheme.textPrimary,
            ),
          ),
          subtitle: Text(
            e.position.isEmpty ? 'Staff' : e.position,
            style: const TextStyle(fontSize: 12, color: AppTheme.textSecondary),
          ),
          trailing: Container(
            padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
            decoration: BoxDecoration(
              color: e.pendingCount > 0
                  ? AppTheme.warning.withValues(alpha: 0.12)
                  : AppTheme.success.withValues(alpha: 0.12),
              borderRadius: BorderRadius.circular(999),
            ),
            child: Text(
              '${e.pendingCount} pending',
              style: TextStyle(
                fontSize: 11.5,
                fontWeight: FontWeight.w800,
                color: e.pendingCount > 0 ? AppTheme.warning : AppTheme.success,
              ),
            ),
          ),
          children: e.pendingTasks.isEmpty
              ? [
                  const Padding(
                    padding: EdgeInsets.symmetric(vertical: 8),
                    child: Text(
                      'No pending tasks.',
                      style: TextStyle(
                          color: AppTheme.textSecondary, fontSize: 12.5),
                    ),
                  ),
                ]
              : e.pendingTasks.map(_taskRow).toList(),
        ),
      ),
    );
  }

  Widget _taskRow(AdminTask t) {
    final pColor = priorityColor(t.priorityValue);
    return Container(
      margin: const EdgeInsets.only(top: 8),
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        color: AppTheme.surfaceMuted,
        borderRadius: BorderRadius.circular(12),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Expanded(
                child: Text(
                  t.title,
                  style: const TextStyle(
                    fontWeight: FontWeight.w700,
                    fontSize: 13.5,
                    color: AppTheme.textPrimary,
                  ),
                ),
              ),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                decoration: BoxDecoration(
                  color: pColor.withValues(alpha: 0.12),
                  borderRadius: BorderRadius.circular(999),
                ),
                child: Text(
                  t.priorityLabel,
                  style: TextStyle(
                    fontSize: 10,
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
              Icon(PhosphorIconsBold.calendarBlank,
                  size: 12, color: AppTheme.textMuted),
              const SizedBox(width: 4),
              Text(
                t.dueDate.isEmpty ? 'No due date' : formatCompactDate(t.dueDate),
                style:
                    const TextStyle(fontSize: 11.5, color: AppTheme.textSecondary),
              ),
              const Spacer(),
              Text(
                t.projectName,
                style:
                    const TextStyle(fontSize: 11.5, color: AppTheme.textMuted),
              ),
            ],
          ),
        ],
      ),
    );
  }
}
