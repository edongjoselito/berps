import 'package:flutter/material.dart';
import 'package:phosphor_flutter/phosphor_flutter.dart';

import '../../../core/theme/app_theme.dart';
import '../../../core/utils/responsive.dart';
import '../../../core/widgets/skeleton.dart';
import '../../auth/domain/staff_session.dart';
import '../data/admin_api.dart';
import '../domain/admin_models.dart';
import 'admin_widgets.dart';

class EmpDtrScreen extends StatefulWidget {
  const EmpDtrScreen({super.key, required this.session});
  final StaffSession session;

  @override
  State<EmpDtrScreen> createState() => _EmpDtrScreenState();
}

class _EmpDtrScreenState extends State<EmpDtrScreen> {
  final AdminApi _api = AdminApi();
  late Future<AdminDtrData> _future;
  String? _employeeId;
  int _month = DateTime.now().month;
  int _year = DateTime.now().year;

  @override
  void initState() {
    super.initState();
    _future = _load();
  }

  Future<AdminDtrData> _load() => _api.fetchDtr(
    baseUrl: widget.session.baseUrl,
    token: widget.session.token,
    employeeId: _employeeId,
    month: _month,
    year: _year,
  );

  void _reload() => setState(() => _future = _load());

  Future<void> _openEmployeePicker(
    List<DtrStaff> employees,
    String currentValue,
  ) async {
    if (employees.isEmpty) return;

    final selected = await showModalBottomSheet<DtrStaff>(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (_) => _EmployeePickerSheet(
        employees: employees,
        currentValue: currentValue,
      ),
    );

    if (selected == null || selected.selectValue.isEmpty) return;
    setState(() {
      _employeeId = selected.selectValue;
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
        child: FutureBuilder<AdminDtrData>(
          future: _future,
          builder: (context, snapshot) {
            final data = snapshot.data;
            final isLoading =
                snapshot.connectionState == ConnectionState.waiting;

            return ListView(
              physics: const AlwaysScrollableScrollPhysics(),
              padding: EdgeInsets.fromLTRB(gutter, 10, gutter, 24),
              children: [
                AdminHeader(
                  title: 'Employee DTR',
                  subtitle: data?.filterApplied == true
                      ? '${data!.selectedEmployeeName} - ${_periodLabel()}'
                      : 'Monthly attendance audit',
                  onBack: () => Navigator.pop(context),
                  trailingIcon: PhosphorIconsBold.arrowClockwise,
                  onTrailingTap: _reload,
                ),
                const SizedBox(height: 12),
                if (data == null)
                  const Skeleton(height: 64, radius: 16)
                else
                  _filterBar(data),
                const SizedBox(height: 12),
                if (isLoading && data == null)
                  ...List.generate(
                    6,
                    (_) => const Padding(
                      padding: EdgeInsets.only(bottom: 8),
                      child: Skeleton(height: 72, radius: 14),
                    ),
                  )
                else if (snapshot.hasError)
                  Padding(
                    padding: const EdgeInsets.only(top: 32),
                    child: AdminErrorView(
                      message: snapshot.error.toString(),
                      onRetry: _reload,
                    ),
                  )
                else if (data != null && !data.filterApplied)
                  const Padding(
                    padding: EdgeInsets.only(top: 32),
                    child: AdminEmptyView(
                      icon: PhosphorIconsFill.userFocus,
                      title: 'Select an employee',
                      message: 'Choose an employee to view their monthly DTR.',
                    ),
                  )
                else if (data != null) ...[
                  _summary(data),
                  const SizedBox(height: 12),
                  _recordsHeader(data),
                  const SizedBox(height: 8),
                  if (data.days.isEmpty)
                    const AdminEmptyView(
                      icon: PhosphorIconsFill.calendarX,
                      title: 'No records',
                      message: 'No DTR days were found for this period.',
                    )
                  else
                    ...data.days.map(_dayRow),
                ],
              ],
            );
          },
        ),
      ),
    );
  }

  Widget _filterBar(AdminDtrData data) {
    final currentValue = _employeeId ?? data.selectedEmployee;
    final selected = _selectedEmployee(data.employees, currentValue);
    final employeeName =
        selected?.name ??
        (data.selectedEmployeeName == 'Employee DTR'
            ? 'Select employee'
            : data.selectedEmployeeName);
    final meta =
        selected?.identifierLabel ??
        (data.filterApplied ? data.selectedEmployee : 'Tap to choose staff');

    return Container(
      padding: const EdgeInsets.all(9),
      decoration: BoxDecoration(
        color: AppTheme.surface,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: AppTheme.border),
        boxShadow: AppTheme.shadowSoft,
      ),
      child: Row(
        children: [
          Expanded(
            child: _ControlButton(
              icon: PhosphorIconsBold.userCircle,
              label: employeeName,
              meta: meta,
              onTap: () => _openEmployeePicker(data.employees, currentValue),
            ),
          ),
          const SizedBox(width: 8),
          MonthYearButton(
            month: _month,
            year: _year,
            onChanged: (m, y) {
              setState(() {
                _month = m ?? _month;
                _year = y ?? _year;
                _future = _load();
              });
            },
          ),
        ],
      ),
    );
  }

  Widget _summary(AdminDtrData d) {
    return Container(
      padding: const EdgeInsets.all(12),
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
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      d.selectedEmployeeName,
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                      style: TextStyle(
                        color: AppTheme.textPrimary,
                        fontWeight: FontWeight.w900,
                        fontSize: 15.5,
                      ),
                    ),
                    const SizedBox(height: 2),
                    Text(
                      _periodLabel(),
                      style: TextStyle(
                        color: AppTheme.textSecondary,
                        fontSize: 11.5,
                        fontWeight: FontWeight.w700,
                      ),
                    ),
                  ],
                ),
              ),
              _StatusPill(
                label: d.monthTotalLabel,
                color: AppTheme.primaryDark,
                icon: PhosphorIconsBold.clock,
              ),
            ],
          ),
          const SizedBox(height: 12),
          LayoutBuilder(
            builder: (context, constraints) {
              final width = (constraints.maxWidth - 18) / 3;
              return Wrap(
                spacing: 9,
                runSpacing: 9,
                children: [
                  _MiniStat(
                    width: width,
                    label: 'Present',
                    value: '${d.presentDays}',
                    color: AppTheme.success,
                  ),
                  _MiniStat(
                    width: width,
                    label: 'Pending',
                    value: '${d.pendingDays}',
                    color: AppTheme.warning,
                  ),
                  _MiniStat(
                    width: width,
                    label: 'Absent',
                    value: '${d.absentDays}',
                    color: AppTheme.danger,
                  ),
                ],
              );
            },
          ),
        ],
      ),
    );
  }

  Widget _recordsHeader(AdminDtrData data) {
    return Row(
      children: [
        Expanded(
          child: Text(
            'Daily records',
            style: TextStyle(
              color: AppTheme.textPrimary,
              fontSize: 14.5,
              fontWeight: FontWeight.w900,
            ),
          ),
        ),
        Text(
          '${data.days.length} day(s)',
          style: TextStyle(
            color: AppTheme.textMuted,
            fontSize: 11.5,
            fontWeight: FontWeight.w700,
          ),
        ),
      ],
    );
  }

  Widget _dayRow(DtrDay day) {
    final statusColor = day.isAbsent
        ? AppTheme.danger
        : (day.isPending ? AppTheme.warning : AppTheme.success);
    final statusLabel = day.isAbsent
        ? 'Absent'
        : (day.isPending ? 'Pending' : 'Present');
    final am = day.amIntervals.isNotEmpty ? day.amIntervals : <TimeInterval>[];
    final pm = day.pmIntervals.isNotEmpty ? day.pmIntervals : <TimeInterval>[];
    final fallback = am.isEmpty && pm.isEmpty
        ? day.intervals
        : <TimeInterval>[];

    return Container(
      margin: const EdgeInsets.only(bottom: 8),
      padding: const EdgeInsets.all(10),
      decoration: BoxDecoration(
        color: AppTheme.surface,
        borderRadius: BorderRadius.circular(14),
        border: Border.all(color: AppTheme.border),
      ),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          _DateBadge(date: day.logDate),
          const SizedBox(width: 10),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Wrap(
                  spacing: 6,
                  runSpacing: 6,
                  children: [
                    _StatusPill(label: statusLabel, color: statusColor),
                    if (!day.isAbsent)
                      _StatusPill(
                        label: day.totalLabel,
                        color: AppTheme.primaryDark,
                        icon: PhosphorIconsBold.timer,
                      ),
                    if (day.taskCount > 0)
                      _StatusPill(
                        label: '${day.taskCount} task',
                        color: AppTheme.textSecondary,
                        icon: PhosphorIconsBold.checkSquareOffset,
                      ),
                  ],
                ),
                const SizedBox(height: 8),
                if (day.isAbsent)
                  Text(
                    'No punch logs',
                    style: TextStyle(
                      color: AppTheme.textMuted,
                      fontSize: 12,
                      fontWeight: FontWeight.w700,
                    ),
                  )
                else ...[
                  if (am.isNotEmpty) _intervalLine('AM', am),
                  if (pm.isNotEmpty) _intervalLine('PM', pm),
                  if (fallback.isNotEmpty) _intervalLine('Logs', fallback),
                ],
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _intervalLine(String label, List<TimeInterval> intervals) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 5),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          SizedBox(
            width: 34,
            child: Text(
              label,
              style: TextStyle(
                color: AppTheme.textMuted,
                fontSize: 10.5,
                fontWeight: FontWeight.w900,
              ),
            ),
          ),
          Expanded(
            child: Wrap(
              spacing: 5,
              runSpacing: 5,
              children: intervals.map(_intervalChip).toList(),
            ),
          ),
        ],
      ),
    );
  }

  Widget _intervalChip(TimeInterval interval) {
    final color = interval.open ? AppTheme.warning : AppTheme.textSecondary;
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 4),
      decoration: BoxDecoration(
        color: interval.open
            ? AppTheme.warning.withValues(alpha: 0.10)
            : AppTheme.surfaceMuted,
        borderRadius: BorderRadius.circular(8),
      ),
      child: Text(
        interval.label,
        style: TextStyle(
          color: color,
          fontSize: 10.5,
          fontWeight: FontWeight.w800,
        ),
      ),
    );
  }

  DtrStaff? _selectedEmployee(List<DtrStaff> employees, String currentValue) {
    if (currentValue.isEmpty) return null;
    for (final e in employees) {
      if (e.selectValue == currentValue ||
          e.dtrKey == currentValue ||
          e.username == currentValue ||
          '${e.userId}' == currentValue) {
        return e;
      }
    }
    return null;
  }

  String _periodLabel() => '${kMonthNames[_month - 1]} $_year';
}

class _ControlButton extends StatelessWidget {
  const _ControlButton({
    required this.icon,
    required this.label,
    required this.meta,
    required this.onTap,
  });

  final IconData icon;
  final String label;
  final String meta;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    return InkWell(
      onTap: onTap,
      borderRadius: BorderRadius.circular(12),
      child: Container(
        height: 52,
        padding: const EdgeInsets.symmetric(horizontal: 11),
        decoration: BoxDecoration(
          color: AppTheme.surfaceMuted,
          borderRadius: BorderRadius.circular(12),
          border: Border.all(color: AppTheme.border),
        ),
        child: Row(
          children: [
            Icon(icon, size: 18, color: AppTheme.primaryDark),
            const SizedBox(width: 9),
            Expanded(
              child: Column(
                mainAxisAlignment: MainAxisAlignment.center,
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    label,
                    maxLines: 1,
                    overflow: TextOverflow.ellipsis,
                    style: TextStyle(
                      color: AppTheme.textPrimary,
                      fontSize: 12.5,
                      fontWeight: FontWeight.w900,
                    ),
                  ),
                  const SizedBox(height: 1),
                  Text(
                    meta,
                    maxLines: 1,
                    overflow: TextOverflow.ellipsis,
                    style: TextStyle(
                      color: AppTheme.textMuted,
                      fontSize: 10.5,
                      fontWeight: FontWeight.w700,
                    ),
                  ),
                ],
              ),
            ),
            Icon(
              PhosphorIconsBold.caretDown,
              size: 13,
              color: AppTheme.textMuted,
            ),
          ],
        ),
      ),
    );
  }
}

class _MiniStat extends StatelessWidget {
  const _MiniStat({
    required this.width,
    required this.label,
    required this.value,
    required this.color,
  });

  final double width;
  final String label;
  final String value;
  final Color color;

  @override
  Widget build(BuildContext context) {
    return SizedBox(
      width: width,
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 9),
        decoration: BoxDecoration(
          color: color.withValues(alpha: 0.08),
          borderRadius: BorderRadius.circular(12),
          border: Border.all(color: color.withValues(alpha: 0.16)),
        ),
        child: Column(
          children: [
            Text(
              value,
              maxLines: 1,
              overflow: TextOverflow.ellipsis,
              style: TextStyle(
                color: color,
                fontSize: 17,
                fontWeight: FontWeight.w900,
              ),
            ),
            const SizedBox(height: 1),
            Text(
              label,
              maxLines: 1,
              overflow: TextOverflow.ellipsis,
              style: TextStyle(
                color: AppTheme.textSecondary,
                fontSize: 10.5,
                fontWeight: FontWeight.w800,
              ),
            ),
          ],
        ),
      ),
    );
  }
}

class _StatusPill extends StatelessWidget {
  const _StatusPill({required this.label, required this.color, this.icon});

  final String label;
  final Color color;
  final IconData? icon;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 4),
      decoration: BoxDecoration(
        color: color.withValues(alpha: 0.10),
        borderRadius: BorderRadius.circular(999),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          if (icon != null) ...[
            Icon(icon, size: 11, color: color),
            const SizedBox(width: 4),
          ],
          Text(
            label,
            maxLines: 1,
            overflow: TextOverflow.ellipsis,
            style: TextStyle(
              color: color,
              fontSize: 10.5,
              fontWeight: FontWeight.w900,
            ),
          ),
        ],
      ),
    );
  }
}

class _DateBadge extends StatelessWidget {
  const _DateBadge({required this.date});

  final String date;

  @override
  Widget build(BuildContext context) {
    final parsed = DateTime.tryParse(date);
    final day = parsed == null ? '--' : '${parsed.day}';
    final month = parsed == null
        ? ''
        : kMonthNames[parsed.month - 1].substring(0, 3);
    final weekday = parsed == null ? '' : _weekdays[parsed.weekday - 1];

    return Container(
      width: 46,
      padding: const EdgeInsets.symmetric(vertical: 7),
      decoration: BoxDecoration(
        color: AppTheme.primarySoft,
        borderRadius: BorderRadius.circular(12),
      ),
      child: Column(
        children: [
          Text(
            day,
            style: const TextStyle(
              color: AppTheme.primaryDeeper,
              fontSize: 17,
              fontWeight: FontWeight.w900,
              height: 1,
            ),
          ),
          const SizedBox(height: 3),
          Text(
            month,
            style: const TextStyle(
              color: AppTheme.primaryDark,
              fontSize: 9.5,
              fontWeight: FontWeight.w900,
            ),
          ),
          if (weekday.isNotEmpty) ...[
            const SizedBox(height: 1),
            Text(
              weekday,
              style: TextStyle(
                color: AppTheme.textMuted,
                fontSize: 9,
                fontWeight: FontWeight.w800,
              ),
            ),
          ],
        ],
      ),
    );
  }
}

class _EmployeePickerSheet extends StatefulWidget {
  const _EmployeePickerSheet({
    required this.employees,
    required this.currentValue,
  });

  final List<DtrStaff> employees;
  final String currentValue;

  @override
  State<_EmployeePickerSheet> createState() => _EmployeePickerSheetState();
}

class _EmployeePickerSheetState extends State<_EmployeePickerSheet> {
  final _search = TextEditingController();
  String _query = '';

  @override
  void dispose() {
    _search.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final bottom = MediaQuery.viewInsetsOf(context).bottom;
    final employees = _filteredEmployees();

    return Padding(
      padding: EdgeInsets.only(bottom: bottom),
      child: Container(
        constraints: BoxConstraints(
          maxHeight: MediaQuery.sizeOf(context).height * 0.82,
        ),
        decoration: BoxDecoration(
          color: AppTheme.surface,
          borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
        ),
        padding: const EdgeInsets.fromLTRB(16, 10, 16, 18),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Container(
              width: 42,
              height: 4,
              decoration: BoxDecoration(
                color: AppTheme.borderStrong,
                borderRadius: BorderRadius.circular(999),
              ),
            ),
            const SizedBox(height: 14),
            Row(
              children: [
                Expanded(
                  child: Text(
                    'Choose employee',
                    style: TextStyle(
                      color: AppTheme.textPrimary,
                      fontSize: 18,
                      fontWeight: FontWeight.w900,
                    ),
                  ),
                ),
                IconButton(
                  onPressed: () => Navigator.pop(context),
                  icon: const Icon(PhosphorIconsBold.x),
                  tooltip: 'Close',
                ),
              ],
            ),
            const SizedBox(height: 8),
            TextField(
              controller: _search,
              onChanged: (value) => setState(() => _query = value.trim()),
              textInputAction: TextInputAction.search,
              decoration: const InputDecoration(
                hintText: 'Search name or ID',
                prefixIcon: Icon(PhosphorIconsBold.magnifyingGlass),
                contentPadding: EdgeInsets.symmetric(horizontal: 12),
              ),
            ),
            const SizedBox(height: 12),
            Flexible(
              child: ListView.separated(
                shrinkWrap: true,
                itemCount: employees.length,
                separatorBuilder: (_, _) => const SizedBox(height: 6),
                itemBuilder: (context, index) {
                  final employee = employees[index];
                  final selected =
                      employee.selectValue == widget.currentValue ||
                      employee.username == widget.currentValue ||
                      '${employee.userId}' == widget.currentValue;
                  return _EmployeeTile(
                    employee: employee,
                    selected: selected,
                    onTap: () => Navigator.pop(context, employee),
                  );
                },
              ),
            ),
          ],
        ),
      ),
    );
  }

  List<DtrStaff> _filteredEmployees() {
    final q = _query.toLowerCase();
    final employees = widget.employees.where((employee) {
      if (q.isEmpty) return true;
      return employee.name.toLowerCase().contains(q) ||
          employee.username.toLowerCase().contains(q) ||
          '${employee.userId}'.contains(q);
    }).toList();

    employees.sort((a, b) {
      final history = b.dtrRecordCount.compareTo(a.dtrRecordCount);
      if (history != 0) return history;
      return a.name.toLowerCase().compareTo(b.name.toLowerCase());
    });
    return employees;
  }
}

class _EmployeeTile extends StatelessWidget {
  const _EmployeeTile({
    required this.employee,
    required this.selected,
    required this.onTap,
  });

  final DtrStaff employee;
  final bool selected;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    return InkWell(
      onTap: onTap,
      borderRadius: BorderRadius.circular(14),
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
        decoration: BoxDecoration(
          color: selected ? AppTheme.primarySoft : AppTheme.surfaceMuted,
          borderRadius: BorderRadius.circular(14),
          border: Border.all(
            color: selected
                ? AppTheme.primary.withValues(alpha: 0.45)
                : AppTheme.border,
          ),
        ),
        child: Row(
          children: [
            Container(
              width: 36,
              height: 36,
              alignment: Alignment.center,
              decoration: BoxDecoration(
                color: selected ? AppTheme.primary : AppTheme.surface,
                borderRadius: BorderRadius.circular(12),
              ),
              child: Icon(
                selected
                    ? PhosphorIconsFill.checkCircle
                    : PhosphorIconsBold.user,
                size: 18,
                color: selected ? Colors.white : AppTheme.primaryDark,
              ),
            ),
            const SizedBox(width: 10),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    employee.name,
                    maxLines: 1,
                    overflow: TextOverflow.ellipsis,
                    style: TextStyle(
                      color: AppTheme.textPrimary,
                      fontSize: 13.5,
                      fontWeight: FontWeight.w900,
                    ),
                  ),
                  const SizedBox(height: 2),
                  Text(
                    employee.identifierLabel,
                    maxLines: 1,
                    overflow: TextOverflow.ellipsis,
                    style: TextStyle(
                      color: AppTheme.textMuted,
                      fontSize: 11,
                      fontWeight: FontWeight.w700,
                    ),
                  ),
                ],
              ),
            ),
            const SizedBox(width: 8),
            _StatusPill(
              label: employee.hasDtrHistory
                  ? '${employee.dtrRecordCount} logs'
                  : 'No DTR',
              color: employee.hasDtrHistory
                  ? AppTheme.success
                  : AppTheme.textMuted,
            ),
          ],
        ),
      ),
    );
  }
}

const _weekdays = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
