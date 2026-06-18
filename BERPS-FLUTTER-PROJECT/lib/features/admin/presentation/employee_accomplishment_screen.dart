import 'package:flutter/material.dart';
import 'package:phosphor_flutter/phosphor_flutter.dart';

import '../../../core/theme/app_theme.dart';
import '../../../core/utils/responsive.dart';
import '../../../core/widgets/skeleton.dart';
import '../../auth/domain/staff_session.dart';
import '../data/admin_api.dart';
import '../domain/admin_models.dart';
import 'admin_widgets.dart';

class EmployeeAccomplishmentScreen extends StatefulWidget {
  const EmployeeAccomplishmentScreen({super.key, required this.session});
  final StaffSession session;

  @override
  State<EmployeeAccomplishmentScreen> createState() =>
      _EmployeeAccomplishmentScreenState();
}

class _EmployeeAccomplishmentScreenState
    extends State<EmployeeAccomplishmentScreen> {
  final AdminApi _api = AdminApi();
  late Future<List<EmployeeOption>> _employeesFuture;

  EmployeeOption? _selected;
  int _month = DateTime.now().month;
  int _year = DateTime.now().year;
  bool _allMonths = false;

  Future<List<AdminAccomplishment>>? _dataFuture;

  @override
  void initState() {
    super.initState();
    _employeesFuture = _api.fetchEmployeeOptions(
      baseUrl: widget.session.baseUrl,
      token: widget.session.token,
    );
  }

  void _runReport() {
    if (_selected == null) return;
    setState(() {
      _dataFuture = _api.fetchEmployeeAccomplishmentData(
        baseUrl: widget.session.baseUrl,
        token: widget.session.token,
        userId: '${_selected!.userId}',
        reportPeriod: _allMonths
            ? null
            : '$_year-${_month.toString().padLeft(2, '0')}',
      );
    });
  }

  @override
  Widget build(BuildContext context) {
    final gutter = context.gutter;
    return Scaffold(
      backgroundColor: AppTheme.background,
      body: ListView(
        physics: const AlwaysScrollableScrollPhysics(),
        padding: EdgeInsets.fromLTRB(gutter, 12, gutter, 28),
        children: [
          AdminHeader(
            title: 'Employee report',
            subtitle: 'Accomplishments by employee',
            onBack: () => Navigator.pop(context),
          ),
          const SizedBox(height: 16),
          _employeePicker(),
          const SizedBox(height: 12),
          Row(
            children: [
              MonthYearButton(
                month: _month,
                year: _year,
                allowAll: true,
                isAll: _allMonths,
                onChanged: (m, y) {
                  setState(() {
                    if (m == null) {
                      _allMonths = true;
                    } else {
                      _allMonths = false;
                      _month = m;
                      _year = y ?? _year;
                    }
                  });
                  _runReport();
                },
              ),
              const Spacer(),
              FilledButton(
                onPressed: _selected == null ? null : _runReport,
                style: FilledButton.styleFrom(
                  minimumSize: const Size(0, 44),
                  padding: const EdgeInsets.symmetric(horizontal: 18),
                ),
                child: const Text('Run'),
              ),
            ],
          ),
          const SizedBox(height: 16),
          if (_dataFuture == null)
            const Padding(
              padding: EdgeInsets.only(top: 40),
              child: AdminEmptyView(
                icon: PhosphorIconsFill.userFocus,
                title: 'Pick an employee',
                message: 'Choose an employee and period to view their report.',
              ),
            )
          else
            FutureBuilder<List<AdminAccomplishment>>(
              future: _dataFuture,
              builder: (context, snapshot) {
                if (snapshot.connectionState == ConnectionState.waiting) {
                  return Column(
                    children: List.generate(
                      3,
                      (_) => const Padding(
                        padding: EdgeInsets.only(bottom: 10),
                        child: Skeleton(height: 88, radius: 16),
                      ),
                    ),
                  );
                }
                if (snapshot.hasError) {
                  return Padding(
                    padding: const EdgeInsets.only(top: 40),
                    child: AdminErrorView(
                      message: snapshot.error.toString(),
                      onRetry: _runReport,
                    ),
                  );
                }
                final data = snapshot.data!;
                if (data.isEmpty) {
                  return const Padding(
                    padding: EdgeInsets.only(top: 40),
                    child: AdminEmptyView(
                      icon: PhosphorIconsFill.trophy,
                      title: 'No accomplishments',
                      message: 'None recorded for this selection.',
                    ),
                  );
                }
                return Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      '${data.length} accomplishment(s)',
                      style: TextStyle(
                        fontWeight: FontWeight.w700,
                        fontSize: 12.5,
                        color: AppTheme.textSecondary,
                      ),
                    ),
                    const SizedBox(height: 10),
                    ...data.map((a) => AccomplishmentCard(item: a)),
                  ],
                );
              },
            ),
        ],
      ),
    );
  }

  Widget _employeePicker() {
    return FutureBuilder<List<EmployeeOption>>(
      future: _employeesFuture,
      builder: (context, snapshot) {
        if (snapshot.connectionState == ConnectionState.waiting) {
          return const Skeleton(height: 54, radius: 14);
        }
        if (snapshot.hasError) {
          return _pickerShell(
            child: const Text('Failed to load employees',
                style: TextStyle(color: AppTheme.danger)),
          );
        }
        final employees = snapshot.data ?? [];
        return _pickerShell(
          child: DropdownButtonHideUnderline(
            child: DropdownButton<EmployeeOption>(
              value: _selected,
              isExpanded: true,
              hint: Text('Select employee',
                  style: TextStyle(color: AppTheme.textMuted)),
              items: employees
                  .map((e) =>
                      DropdownMenuItem(value: e, child: Text(e.name)))
                  .toList(),
              onChanged: (v) {
                setState(() => _selected = v);
                _runReport();
              },
            ),
          ),
        );
      },
    );
  }

  Widget _pickerShell({required Widget child}) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 4),
      decoration: BoxDecoration(
        color: AppTheme.surface,
        borderRadius: BorderRadius.circular(14),
        border: Border.all(color: AppTheme.border),
        boxShadow: AppTheme.shadowSoft,
      ),
      child: Row(
        children: [
          const Icon(PhosphorIconsBold.userCircle,
              size: 18, color: AppTheme.primaryDark),
          const SizedBox(width: 10),
          Expanded(child: child),
        ],
      ),
    );
  }
}
