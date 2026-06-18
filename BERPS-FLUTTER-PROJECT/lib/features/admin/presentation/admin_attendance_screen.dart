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

class AdminAttendanceScreen extends StatefulWidget {
  const AdminAttendanceScreen({super.key, required this.session});
  final StaffSession session;

  @override
  State<AdminAttendanceScreen> createState() => _AdminAttendanceScreenState();
}

class _AdminAttendanceScreenState extends State<AdminAttendanceScreen> {
  final AdminApi _api = AdminApi();
  late Future<AdminAttendanceData> _future;
  DateTime _from = DateTime.now();
  DateTime _to = DateTime.now();

  @override
  void initState() {
    super.initState();
    _future = _load();
  }

  String _fmt(DateTime d) =>
      '${d.year}-${d.month.toString().padLeft(2, '0')}-${d.day.toString().padLeft(2, '0')}';

  Future<AdminAttendanceData> _load() => _api.fetchAttendance(
        baseUrl: widget.session.baseUrl,
        token: widget.session.token,
        from: _fmt(_from),
        to: _fmt(_to),
      );

  void _reload() => setState(() => _future = _load());

  Future<void> _pickRange() async {
    final picked = await showDateRangePicker(
      context: context,
      firstDate: DateTime(2020),
      lastDate: DateTime(2100),
      initialDateRange: DateTimeRange(start: _from, end: _to),
    );
    if (picked != null) {
      setState(() {
        _from = picked.start;
        _to = picked.end;
        _future = _load();
      });
    }
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
        child: FutureBuilder<AdminAttendanceData>(
          future: _future,
          builder: (context, snapshot) {
            return ListView(
              physics: const AlwaysScrollableScrollPhysics(),
              padding: EdgeInsets.fromLTRB(gutter, 12, gutter, 28),
              children: [
                AdminHeader(
                  title: 'Attendance',
                  subtitle: 'Daily time logs',
                  onBack: () => Navigator.pop(context),
                ),
                const SizedBox(height: 16),
                GestureDetector(
                  onTap: _pickRange,
                  child: Container(
                    padding: const EdgeInsets.symmetric(
                        horizontal: 14, vertical: 13),
                    decoration: BoxDecoration(
                      color: Colors.white,
                      borderRadius: BorderRadius.circular(14),
                      border: Border.all(color: AppTheme.border),
                      boxShadow: AppTheme.shadowSoft,
                    ),
                    child: Row(
                      children: [
                        const Icon(PhosphorIconsBold.calendarDots,
                            size: 18, color: AppTheme.primaryDark),
                        const SizedBox(width: 10),
                        Expanded(
                          child: Text(
                            formatRangeLabel(_fmt(_from), _fmt(_to)),
                            style: const TextStyle(
                              fontWeight: FontWeight.w800,
                              fontSize: 13.5,
                              color: AppTheme.textPrimary,
                            ),
                          ),
                        ),
                        const Icon(PhosphorIconsBold.caretRight,
                            size: 15, color: AppTheme.textMuted),
                      ],
                    ),
                  ),
                ),
                const SizedBox(height: 14),
                if (snapshot.hasData)
                  Container(
                    padding: const EdgeInsets.all(14),
                    decoration: BoxDecoration(
                      color: AppTheme.primaryDark,
                      borderRadius: BorderRadius.circular(16),
                    ),
                    child: Row(
                      children: [
                        const Icon(PhosphorIconsFill.clock,
                            color: Colors.white, size: 20),
                        const SizedBox(width: 10),
                        const Text(
                          'Total hours (all staff)',
                          style: TextStyle(
                            color: Colors.white,
                            fontWeight: FontWeight.w700,
                            fontSize: 13,
                          ),
                        ),
                        const Spacer(),
                        Text(
                          snapshot.data!.grandTotalAll,
                          style: const TextStyle(
                            color: Colors.white,
                            fontWeight: FontWeight.w900,
                            fontSize: 18,
                          ),
                        ),
                      ],
                    ),
                  ),
                const SizedBox(height: 14),
                if (snapshot.connectionState == ConnectionState.waiting)
                  ...List.generate(
                    5,
                    (_) => const Padding(
                      padding: EdgeInsets.only(bottom: 10),
                      child: Skeleton(height: 80, radius: 16),
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
                else if (snapshot.data!.rows.isEmpty)
                  const Padding(
                    padding: EdgeInsets.only(top: 40),
                    child: AdminEmptyView(
                      icon: PhosphorIconsFill.clock,
                      title: 'No attendance logs',
                      message: 'No time records for this range.',
                    ),
                  )
                else
                  ...snapshot.data!.rows.map(_attendanceCard),
              ],
            );
          },
        ),
      ),
    );
  }

  Widget _attendanceCard(AttendanceRow r) {
    return Container(
      margin: const EdgeInsets.only(bottom: 10),
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: Colors.white,
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
                  r.employeeName.isEmpty ? '(unknown)' : r.employeeName,
                  style: const TextStyle(
                    fontWeight: FontWeight.w800,
                    fontSize: 14.5,
                    color: AppTheme.textPrimary,
                  ),
                ),
              ),
              Container(
                padding:
                    const EdgeInsets.symmetric(horizontal: 9, vertical: 4),
                decoration: BoxDecoration(
                  color: AppTheme.primarySoft,
                  borderRadius: BorderRadius.circular(999),
                ),
                child: Text(
                  r.totalHoursLabel,
                  style: const TextStyle(
                    fontWeight: FontWeight.w800,
                    fontSize: 12,
                    color: AppTheme.primaryDark,
                  ),
                ),
              ),
            ],
          ),
          const SizedBox(height: 4),
          Row(
            children: [
              Icon(PhosphorIconsBold.calendarBlank,
                  size: 12, color: AppTheme.textMuted),
              const SizedBox(width: 4),
              Text(
                formatCompactDate(r.logDate),
                style:
                    const TextStyle(fontSize: 12, color: AppTheme.textSecondary),
              ),
              const SizedBox(width: 12),
              Icon(PhosphorIconsBold.checkSquareOffset,
                  size: 12, color: AppTheme.textMuted),
              const SizedBox(width: 4),
              Text(
                '${r.accomplishmentCount} done',
                style:
                    const TextStyle(fontSize: 12, color: AppTheme.textSecondary),
              ),
            ],
          ),
          if (r.intervals.isNotEmpty) ...[
            const SizedBox(height: 10),
            Wrap(
              spacing: 8,
              runSpacing: 6,
              children: r.intervals
                  .map(
                    (i) => Container(
                      padding: const EdgeInsets.symmetric(
                          horizontal: 9, vertical: 5),
                      decoration: BoxDecoration(
                        color: AppTheme.surfaceMuted,
                        borderRadius: BorderRadius.circular(8),
                      ),
                      child: Text(
                        i.label,
                        style: const TextStyle(
                          fontSize: 11,
                          fontWeight: FontWeight.w600,
                          color: AppTheme.textSecondary,
                        ),
                      ),
                    ),
                  )
                  .toList(),
            ),
          ],
        ],
      ),
    );
  }
}
