// Domain models for the admin mobile experience. Each maps 1:1 to the JSON
// emitted by application/controllers/api/MobileAdmin.php.

int _asInt(dynamic v) => v is int ? v : int.tryParse('${v ?? ''}') ?? 0;
double _asDouble(dynamic v) =>
    v is num ? v.toDouble() : double.tryParse('${v ?? ''}') ?? 0;
bool _asBool(dynamic v) => v == true || v == 1 || v == '1' || v == 'true';
String _asStr(dynamic v) => (v ?? '').toString();

// ── Dashboard ────────────────────────────────────────────────────────────────

class AdminDashboard {
  AdminDashboard({
    required this.date,
    required this.todaysPayments,
    required this.todaysExpenses,
    required this.totalClients,
    required this.openReceivable,
    required this.prospectClients,
    required this.taskCounts,
    required this.taskQueue,
    required this.accomplished,
  });

  final String date;
  final double todaysPayments;
  final double todaysExpenses;
  final int totalClients;
  final double openReceivable;
  final int prospectClients;
  final TaskCounts taskCounts;
  final List<TaskQueueItem> taskQueue;
  final List<AccomplishedSummary> accomplished;

  factory AdminDashboard.fromJson(Map<String, dynamic> json) {
    final finance = (json['finance'] as Map?)?.cast<String, dynamic>() ?? {};
    return AdminDashboard(
      date: _asStr(json['date']),
      todaysPayments: _asDouble(finance['todays_payments']),
      todaysExpenses: _asDouble(finance['todays_expenses']),
      totalClients: _asInt(finance['total_clients']),
      openReceivable: _asDouble(finance['open_receivable']),
      prospectClients: _asInt(finance['prospect_clients']),
      taskCounts: TaskCounts.fromJson(
        (json['tasks'] as Map?)?.cast<String, dynamic>() ?? {},
      ),
      taskQueue: ((json['task_queue'] as List?) ?? [])
          .map((e) => TaskQueueItem.fromJson(e as Map<String, dynamic>))
          .toList(),
      accomplished: ((json['accomplished_summary'] as List?) ?? [])
          .map((e) => AccomplishedSummary.fromJson(e as Map<String, dynamic>))
          .toList(),
    );
  }
}

class AccomplishedSummary {
  AccomplishedSummary({required this.name, required this.total});
  final String name;
  final int total;
  factory AccomplishedSummary.fromJson(Map<String, dynamic> j) =>
      AccomplishedSummary(name: _asStr(j['name']), total: _asInt(j['total']));
}

class TaskCounts {
  TaskCounts({
    required this.open,
    required this.closed,
    required this.dueToday,
    required this.dueSoon,
    required this.overdue,
    required this.withoutDueDate,
    required this.dueWindowDays,
  });

  final int open;
  final int closed;
  final int dueToday;
  final int dueSoon;
  final int overdue;
  final int withoutDueDate;
  final int dueWindowDays;

  factory TaskCounts.fromJson(Map<String, dynamic> j) => TaskCounts(
        open: _asInt(j['open']),
        closed: _asInt(j['closed']),
        dueToday: _asInt(j['due_today']),
        dueSoon: _asInt(j['due_soon']),
        overdue: _asInt(j['overdue']),
        withoutDueDate: _asInt(j['without_due_date']),
        dueWindowDays: _asInt(j['due_window_days']),
      );
}

class TaskQueueItem {
  TaskQueueItem({
    required this.id,
    required this.title,
    required this.subtitle,
    required this.assignedName,
    required this.reportedDate,
    required this.dueDate,
    required this.priority,
    required this.progress,
  });

  final int id;
  final String title;
  final String subtitle;
  final String assignedName;
  final String reportedDate;
  final String dueDate;
  final String priority;
  final int progress;

  factory TaskQueueItem.fromJson(Map<String, dynamic> j) => TaskQueueItem(
        id: _asInt(j['id']),
        title: _asStr(j['title']),
        subtitle: _asStr(j['subtitle']),
        assignedName: _asStr(j['assigned_name']),
        reportedDate: _asStr(j['reported_date']),
        dueDate: _asStr(j['due_date']),
        priority: _asStr(j['priority']),
        progress: _asInt(j['progress']),
      );
}

// ── Tasks (projectAddTask) ───────────────────────────────────────────────────

class AdminTasksData {
  AdminTasksData({
    required this.statusFilter,
    required this.counts,
    required this.taskQueue,
    required this.tasks,
    required this.projects,
    required this.staff,
  });

  final String statusFilter;
  final TaskCounts counts;
  final List<TaskQueueItem> taskQueue;
  final List<AdminTask> tasks;
  final List<ProjectOption> projects;
  final List<StaffOption> staff;

  factory AdminTasksData.fromJson(Map<String, dynamic> json) => AdminTasksData(
        statusFilter: _asStr(json['status_filter']),
        counts: TaskCounts.fromJson(
          (json['counts'] as Map?)?.cast<String, dynamic>() ?? {},
        ),
        taskQueue: ((json['task_queue'] as List?) ?? [])
            .map((e) => TaskQueueItem.fromJson(e as Map<String, dynamic>))
            .toList(),
        tasks: ((json['tasks'] as List?) ?? [])
            .map((e) => AdminTask.fromJson(e as Map<String, dynamic>))
            .toList(),
        projects: ((json['projects'] as List?) ?? [])
            .map((e) => ProjectOption.fromJson(e as Map<String, dynamic>))
            .toList(),
        staff: ((json['staff'] as List?) ?? [])
            .map((e) => StaffOption.fromJson(e as Map<String, dynamic>))
            .toList(),
      );
}

class AdminTask {
  AdminTask({
    required this.id,
    required this.title,
    required this.reportedDate,
    required this.dueDate,
    required this.status,
    required this.priorityValue,
    required this.priorityLabel,
    required this.projectName,
    required this.assignedName,
    required this.attachmentLink,
    required this.adminComment,
    required this.dueMetaLabel,
    required this.dueMetaType,
  });

  final int id;
  final String title;
  final String reportedDate;
  final String dueDate;
  final String status;
  final String priorityValue;
  final String priorityLabel;
  final String projectName;
  final String assignedName;
  final String attachmentLink;
  final String adminComment;
  final String dueMetaLabel;
  final String dueMetaType;

  bool get isOpen => status == 'open';

  factory AdminTask.fromJson(Map<String, dynamic> j) => AdminTask(
        id: _asInt(j['id']),
        title: _asStr(j['title']),
        reportedDate: _asStr(j['reported_date']),
        dueDate: _asStr(j['due_date']),
        status: _asStr(j['status']),
        priorityValue: _asStr(j['priority_value']),
        priorityLabel: _asStr(j['priority_label']),
        projectName: _asStr(j['project_name']),
        assignedName: _asStr(j['assigned_person_name']),
        attachmentLink: _asStr(j['attachment_link']),
        adminComment: _asStr(j['admin_comment']),
        dueMetaLabel: _asStr(j['due_meta_label']),
        dueMetaType: _asStr(j['due_meta_type']),
      );
}

class ProjectOption {
  ProjectOption({required this.id, required this.name});
  final int id;
  final String name;
  factory ProjectOption.fromJson(Map<String, dynamic> j) =>
      ProjectOption(id: _asInt(j['id']), name: _asStr(j['name']));
}

class StaffOption {
  StaffOption({required this.userId, required this.name});
  final int userId;
  final String name;
  factory StaffOption.fromJson(Map<String, dynamic> j) =>
      StaffOption(userId: _asInt(j['user_id']), name: _asStr(j['name']));
}

// ── Employee tasks ───────────────────────────────────────────────────────────

class EmployeeTaskGroup {
  EmployeeTaskGroup({
    required this.userId,
    required this.name,
    required this.position,
    required this.email,
    required this.pendingCount,
    required this.pendingTasks,
  });

  final int userId;
  final String name;
  final String position;
  final String email;
  final int pendingCount;
  final List<AdminTask> pendingTasks;

  factory EmployeeTaskGroup.fromJson(Map<String, dynamic> j) =>
      EmployeeTaskGroup(
        userId: _asInt(j['user_id']),
        name: _asStr(j['name']),
        position: _asStr(j['position']),
        email: _asStr(j['email']),
        pendingCount: _asInt(j['pending_count']),
        pendingTasks: ((j['pending_tasks'] as List?) ?? [])
            .map((e) => AdminTask.fromJson(e as Map<String, dynamic>))
            .toList(),
      );
}

// ── Accomplishments ──────────────────────────────────────────────────────────

class AdminAccomplishment {
  AdminAccomplishment({
    required this.type,
    required this.title,
    required this.note,
    required this.projectName,
    required this.assignedName,
    required this.datePosted,
    required this.priorityLabel,
    required this.points,
  });

  final String type;
  final String title;
  final String note;
  final String projectName;
  final String assignedName;
  final String datePosted;
  final String priorityLabel;
  final int? points;

  factory AdminAccomplishment.fromJson(Map<String, dynamic> j) =>
      AdminAccomplishment(
        type: _asStr(j['type']),
        title: _asStr(j['title']),
        note: _asStr(j['note']),
        projectName: _asStr(j['project_name']),
        assignedName: _asStr(j['assigned_person_name']),
        datePosted: _asStr(j['date_posted']),
        priorityLabel: _asStr(j['priority_label']),
        points: j['points'] == null ? null : _asInt(j['points']),
      );
}

class EmployeeOption {
  EmployeeOption({
    required this.userId,
    required this.name,
    required this.position,
  });
  final int userId;
  final String name;
  final String position;
  factory EmployeeOption.fromJson(Map<String, dynamic> j) => EmployeeOption(
        userId: _asInt(j['user_id']),
        name: _asStr(j['name']),
        position: _asStr(j['position']),
      );
}

// ── Attendance ───────────────────────────────────────────────────────────────

class AdminAttendanceData {
  AdminAttendanceData({
    required this.rangeFrom,
    required this.rangeTo,
    required this.grandTotalAll,
    required this.rows,
  });

  final String rangeFrom;
  final String rangeTo;
  final String grandTotalAll;
  final List<AttendanceRow> rows;

  factory AdminAttendanceData.fromJson(Map<String, dynamic> json) =>
      AdminAttendanceData(
        rangeFrom: _asStr(json['range_from']),
        rangeTo: _asStr(json['range_to']),
        grandTotalAll: _asStr(json['grand_total_all']),
        rows: ((json['data'] as List?) ?? [])
            .map((e) => AttendanceRow.fromJson(e as Map<String, dynamic>))
            .toList(),
      );
}

class AttendanceRow {
  AttendanceRow({
    required this.logDate,
    required this.employeeName,
    required this.accomplishmentCount,
    required this.hasTimeIn,
    required this.totalHoursLabel,
    required this.intervals,
  });

  final String logDate;
  final String employeeName;
  final int accomplishmentCount;
  final bool hasTimeIn;
  final String totalHoursLabel;
  final List<TimeInterval> intervals;

  factory AttendanceRow.fromJson(Map<String, dynamic> j) => AttendanceRow(
        logDate: _asStr(j['log_date']),
        employeeName: _asStr(j['employee_name']),
        accomplishmentCount: _asInt(j['accomplishment_count']),
        hasTimeIn: _asBool(j['has_time_in']),
        totalHoursLabel: _asStr(j['total_hours_label']),
        intervals: ((j['intervals'] as List?) ?? [])
            .map((e) => TimeInterval.fromJson(e as Map<String, dynamic>))
            .toList(),
      );
}

class TimeInterval {
  TimeInterval({required this.label, required this.seconds, required this.open});
  final String label;
  final int seconds;
  final bool open;
  factory TimeInterval.fromJson(Map<String, dynamic> j) => TimeInterval(
        label: _asStr(j['label']),
        seconds: _asInt(j['seconds']),
        open: _asBool(j['open']),
      );
}

// ── Employee DTR ─────────────────────────────────────────────────────────────

class AdminDtrData {
  AdminDtrData({
    required this.selectedEmployee,
    required this.selectedEmployeeName,
    required this.month,
    required this.year,
    required this.filterApplied,
    required this.monthTotalLabel,
    required this.presentDays,
    required this.absentDays,
    required this.pendingDays,
    required this.employees,
    required this.days,
  });

  final String selectedEmployee;
  final String selectedEmployeeName;
  final int month;
  final int year;
  final bool filterApplied;
  final String monthTotalLabel;
  final int presentDays;
  final int absentDays;
  final int pendingDays;
  final List<DtrStaff> employees;
  final List<DtrDay> days;

  factory AdminDtrData.fromJson(Map<String, dynamic> json) => AdminDtrData(
        selectedEmployee: _asStr(json['selected_employee']),
        selectedEmployeeName: _asStr(json['selected_employee_name']),
        month: _asInt(json['selected_month']),
        year: _asInt(json['selected_year']),
        filterApplied: _asBool(json['filter_applied']),
        monthTotalLabel: _asStr(json['month_total_label']),
        presentDays: _asInt(json['present_days']),
        absentDays: _asInt(json['absent_days']),
        pendingDays: _asInt(json['pending_days']),
        employees: ((json['employees'] as List?) ?? [])
            .map((e) => DtrStaff.fromJson(e as Map<String, dynamic>))
            .toList(),
        days: ((json['data'] as List?) ?? [])
            .map((e) => DtrDay.fromJson(e as Map<String, dynamic>))
            .toList(),
      );
}

class DtrStaff {
  DtrStaff({
    required this.userId,
    required this.username,
    required this.name,
    required this.dtrKey,
    required this.dtrRecordCount,
  });

  final int userId;
  final String username;
  final String name;
  final String dtrKey;
  final int dtrRecordCount;

  String get selectValue {
    if (dtrKey.isNotEmpty) return dtrKey;
    if (username.isNotEmpty) return username;
    return userId > 0 ? '$userId' : '';
  }

  String get identifierLabel {
    final parts = <String>[];
    if (username.isNotEmpty) parts.add(username);
    if (userId > 0 && username != '$userId') parts.add('ID $userId');
    return parts.join(' - ');
  }

  bool get hasDtrHistory => dtrRecordCount > 0;

  factory DtrStaff.fromJson(Map<String, dynamic> j) {
    final username = _asStr(j['username']);
    final userId = _asInt(j['user_id']);
    return DtrStaff(
      userId: userId,
      username: username,
      name: _asStr(j['name']),
      dtrKey: _asStr(j['dtr_key']),
      dtrRecordCount: _asInt(j['dtr_record_count']),
    );
  }
}

class DtrDay {
  DtrDay({
    required this.logDate,
    required this.intervals,
    required this.amIntervals,
    required this.pmIntervals,
    required this.totalLabel,
    required this.taskCount,
    required this.isAbsent,
    required this.isPending,
  });

  final String logDate;
  final List<TimeInterval> intervals;
  final List<TimeInterval> amIntervals;
  final List<TimeInterval> pmIntervals;
  final String totalLabel;
  final int taskCount;
  final bool isAbsent;
  final bool isPending;

  factory DtrDay.fromJson(Map<String, dynamic> j) => DtrDay(
        logDate: _asStr(j['log_date']),
        intervals: ((j['intervals'] as List?) ?? [])
            .map((e) => TimeInterval.fromJson(e as Map<String, dynamic>))
            .toList(),
        amIntervals: ((j['am_intervals'] as List?) ?? [])
            .map((e) => TimeInterval.fromJson(e as Map<String, dynamic>))
            .toList(),
        pmIntervals: ((j['pm_intervals'] as List?) ?? [])
            .map((e) => TimeInterval.fromJson(e as Map<String, dynamic>))
            .toList(),
        totalLabel: _asStr(j['total_label']),
        taskCount: _asInt(j['task_count']),
        isAbsent: _asBool(j['is_absent']),
        isPending: _asBool(j['is_pending']),
      );
}

// ── Clients ──────────────────────────────────────────────────────────────────

class AdminClientsData {
  AdminClientsData({required this.nextCustId, required this.clients});
  final String nextCustId;
  final List<AdminClient> clients;
  factory AdminClientsData.fromJson(Map<String, dynamic> json) =>
      AdminClientsData(
        nextCustId: _asStr(json['next_cust_id']),
        clients: ((json['clients'] as List?) ?? [])
            .map((e) => AdminClient.fromJson(e as Map<String, dynamic>))
            .toList(),
      );
}

class AdminClient {
  AdminClient({
    required this.custId,
    required this.customer,
    required this.address,
    required this.contact,
    required this.contactPerson,
    required this.companyEmail,
    required this.clientEmail,
    required this.clientStat,
    required this.clientSource,
    required this.facebookLink,
    required this.salesAgent,
    required this.notes,
  });

  final String custId;
  final String customer;
  final String address;
  final String contact;
  final String contactPerson;
  final String companyEmail;
  final String clientEmail;
  final String clientStat;
  final String clientSource;
  final String facebookLink;
  final String salesAgent;
  final String notes;

  factory AdminClient.fromJson(Map<String, dynamic> j) => AdminClient(
        custId: _asStr(j['cust_id']),
        customer: _asStr(j['customer']),
        address: _asStr(j['address']),
        contact: _asStr(j['contact']),
        contactPerson: _asStr(j['contact_person']),
        companyEmail: _asStr(j['company_email']),
        clientEmail: _asStr(j['client_email']),
        clientStat: _asStr(j['client_stat']),
        clientSource: _asStr(j['client_source']),
        facebookLink: _asStr(j['facebook_link']),
        salesAgent: _asStr(j['sales_agent']),
        notes: _asStr(j['notes']),
      );

  Map<String, dynamic> toPayload() => {
        'CustID': custId,
        'Customer': customer,
        'Address': address,
        'Contact': contact,
        'ContactPerson': contactPerson,
        'CompanyEmail': companyEmail,
        'client_email': clientEmail,
        'ClientStat': clientStat,
        'client_source': clientSource,
        'facebook_link': facebookLink,
        'sales_agent': salesAgent,
        'notes': notes,
      };
}
