import 'package:flutter/material.dart';
import 'package:phosphor_flutter/phosphor_flutter.dart';

import '../../../core/theme/app_theme.dart';
import '../../../core/utils/date_formatters.dart';
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
            return ListView(
              physics: const AlwaysScrollableScrollPhysics(),
              padding: EdgeInsets.fromLTRB(gutter, 12, gutter, 28),
              children: [
                AdminHeader(
                  title: 'Employee DTR',
                  subtitle: 'Daily time record',
                  onBack: () => Navigator.pop(context),
                ),
                const SizedBox(height: 16),
                if (data != null) _employeePicker(data.employees),
                const SizedBox(height: 12),
                Row(
                  children: [
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
                const SizedBox(height: 16),
                if (snapshot.connectionState == ConnectionState.waiting)
                  ...List.generate(
                    5,
                    (_) => const Padding(
                      padding: EdgeInsets.only(bottom: 10),
                      child: Skeleton(height: 64, radius: 14),
                    ),
                  )
                else if (snapshot.hasError)
                  Padding(
                    padding: const EdgeInsets.only(top: 40),
                    child: AdminErrorView(
                      message: snapshot.error.toString(),
                      onRetry: _reload,
                    ),
                  )
                else if (!data!.filterApplied)
                  const Padding(
                    padding: EdgeInsets.only(top: 40),
                    child: AdminEmptyView(
                      icon: PhosphorIconsFill.userFocus,
                      title: 'Select an employee',
                      message: 'Choose an employee to view their DTR.',
                    ),
                  )
                else ...[
                  _summary(data),
                  const SizedBox(height: 14),
                  if (data.days.isEmpty)
                    const AdminEmptyView(
                      icon: PhosphorIconsFill.calendarX,
                      title: 'No records',
                      message: 'No time logs for this month.',
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

  Widget _employeePicker(List<DtrStaff> employees) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 4),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(14),
        border: Border.all(color: AppTheme.border),
        boxShadow: AppTheme.shadowSoft,
      ),
      child: Row(
        children: [
          const Icon(PhosphorIconsBold.userCircle,
              size: 18, color: AppTheme.primaryDark),
          const SizedBox(width: 10),
          Expanded(
            child: DropdownButtonHideUnderline(
              child: DropdownButton<String>(
                value: _employeeId,
                isExpanded: true,
                hint: const Text('Select employee',
                    style: TextStyle(color: AppTheme.textMuted)),
                items: employees
                    .where((e) => e.userId > 0)
                    .map((e) => DropdownMenuItem(
                          value: '${e.userId}',
                          child: Text(e.name),
                        ))
                    .toList(),
                onChanged: (v) {
                  setState(() {
                    _employeeId = v;
                    _future = _load();
                  });
                },
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _summary(AdminDtrData d) {
    Widget stat(String label, String value, Color color) => Expanded(
          child: Container(
            padding: const EdgeInsets.symmetric(vertical: 12),
            decoration: BoxDecoration(
              color: color.withValues(alpha: 0.08),
              borderRadius: BorderRadius.circular(14),
              border: Border.all(color: color.withValues(alpha: 0.18)),
            ),
            child: Column(
              children: [
                Text(
                  value,
                  style: TextStyle(
                    fontWeight: FontWeight.w900,
                    fontSize: 17,
                    color: color,
                  ),
                ),
                Text(
                  label,
                  style: const TextStyle(
                    fontSize: 11,
                    fontWeight: FontWeight.w600,
                    color: AppTheme.textSecondary,
                  ),
                ),
              ],
            ),
          ),
        );

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          d.selectedEmployeeName,
          style: const TextStyle(
            fontWeight: FontWeight.w900,
            fontSize: 16,
            color: AppTheme.textPrimary,
          ),
        ),
        const SizedBox(height: 4),
        Text(
          'Total this month: ${d.monthTotalLabel}',
          style: const TextStyle(fontSize: 12.5, color: AppTheme.textSecondary),
        ),
        const SizedBox(height: 12),
        Row(
          children: [
            stat('Present', '${d.presentDays}', AppTheme.success),
            const SizedBox(width: 10),
            stat('Pending', '${d.pendingDays}', AppTheme.warning),
            const SizedBox(width: 10),
            stat('Absent', '${d.absentDays}', AppTheme.danger),
          ],
        ),
      ],
    );
  }

  Widget _dayRow(DtrDay day) {
    final Color statusColor = day.isAbsent
        ? AppTheme.danger
        : (day.isPending ? AppTheme.warning : AppTheme.success);
    final String statusLabel =
        day.isAbsent ? 'Absent' : (day.isPending ? 'Pending' : 'Present');

    return Container(
      margin: const EdgeInsets.only(bottom: 8),
      padding: const EdgeInsets.all(13),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(14),
        border: Border.all(color: AppTheme.border),
        boxShadow: AppTheme.shadowSoft,
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Container(
                width: 8,
                height: 8,
                decoration:
                    BoxDecoration(color: statusColor, shape: BoxShape.circle),
              ),
              const SizedBox(width: 8),
              Text(
                formatCompactDate(day.logDate),
                style: const TextStyle(
                  fontWeight: FontWeight.w800,
                  fontSize: 13.5,
                  color: AppTheme.textPrimary,
                ),
              ),
              const Spacer(),
              Text(
                statusLabel,
                style: TextStyle(
                  fontWeight: FontWeight.w800,
                  fontSize: 11.5,
                  color: statusColor,
                ),
              ),
              if (!day.isAbsent) ...[
                const SizedBox(width: 8),
                Text(
                  day.totalLabel,
                  style: const TextStyle(
                    fontWeight: FontWeight.w800,
                    fontSize: 12.5,
                    color: AppTheme.primaryDark,
                  ),
                ),
              ],
            ],
          ),
          if (day.intervals.isNotEmpty) ...[
            const SizedBox(height: 8),
            Wrap(
              spacing: 6,
              runSpacing: 6,
              children: day.intervals
                  .map(
                    (i) => Container(
                      padding: const EdgeInsets.symmetric(
                          horizontal: 8, vertical: 4),
                      decoration: BoxDecoration(
                        color: i.open
                            ? AppTheme.warning.withValues(alpha: 0.10)
                            : AppTheme.surfaceMuted,
                        borderRadius: BorderRadius.circular(7),
                      ),
                      child: Text(
                        i.label,
                        style: TextStyle(
                          fontSize: 10.5,
                          fontWeight: FontWeight.w600,
                          color:
                              i.open ? AppTheme.warning : AppTheme.textSecondary,
                        ),
                      ),
                    ),
                  )
                  .toList(),
            ),
          ],
          if (day.taskCount > 0) ...[
            const SizedBox(height: 8),
            Row(
              children: [
                Icon(PhosphorIconsBold.checkSquareOffset,
                    size: 12, color: AppTheme.textMuted),
                const SizedBox(width: 4),
                Text(
                  '${day.taskCount} accomplishment(s)',
                  style: const TextStyle(
                    fontSize: 11.5,
                    color: AppTheme.textSecondary,
                  ),
                ),
              ],
            ),
          ],
        ],
      ),
    );
  }
}
