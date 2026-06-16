class StaffDashboard {
  const StaffDashboard({
    required this.today,
    required this.tasksDueToday,
    required this.tasksDueSoon,
    required this.tasksOverdue,
    required this.tasksWithoutDueDate,
    required this.tasksDueWindowDays,
    required this.forwardedTaskCount,
    required this.remindersDueTodayCount,
    required this.accomplishmentsToday,
    required this.todayHoursLabel,
    required this.attendanceNotice,
    required this.attendanceStatusLabel,
    required this.unassignedSupportCount,
    required this.ongoingTasks,
  });

  final String today;
  final int tasksDueToday;
  final int tasksDueSoon;
  final int tasksOverdue;
  final int tasksWithoutDueDate;
  final int tasksDueWindowDays;
  final int forwardedTaskCount;
  final int remindersDueTodayCount;
  final int accomplishmentsToday;
  final String todayHoursLabel;
  final String attendanceNotice;
  final String attendanceStatusLabel;
  final int unassignedSupportCount;
  final List<OngoingTask> ongoingTasks;

  factory StaffDashboard.fromJson(Map<String, dynamic> json) {
    final tasks = (json['tasks'] is Map)
        ? Map<String, dynamic>.from(json['tasks'] as Map)
        : <String, dynamic>{};
    final reminders = (json['reminders'] is Map)
        ? Map<String, dynamic>.from(json['reminders'] as Map)
        : <String, dynamic>{};
    final accomplishments = (json['accomplishments'] is Map)
        ? Map<String, dynamic>.from(json['accomplishments'] as Map)
        : <String, dynamic>{};
    final attendance = (json['attendance'] is Map)
        ? Map<String, dynamic>.from(json['attendance'] as Map)
        : <String, dynamic>{};
    final support = (json['support'] is Map)
        ? Map<String, dynamic>.from(json['support'] as Map)
        : <String, dynamic>{};
    final ongoingRaw = json['ongoing_tasks'];
    final ongoing = ongoingRaw is List
        ? ongoingRaw
              .whereType<Map>()
              .map((e) => OngoingTask.fromJson(Map<String, dynamic>.from(e)))
              .toList()
        : <OngoingTask>[];

    int parseInt(dynamic v) => int.tryParse((v ?? '0').toString()) ?? 0;

    return StaffDashboard(
      today: (json['today'] ?? '').toString(),
      tasksDueToday: parseInt(tasks['due_today']),
      tasksDueSoon: parseInt(tasks['due_soon']),
      tasksOverdue: parseInt(tasks['overdue']),
      tasksWithoutDueDate: parseInt(tasks['without_due_date']),
      tasksDueWindowDays: parseInt(tasks['due_window_days']),
      forwardedTaskCount: parseInt(tasks['forwarded_count']),
      remindersDueTodayCount: parseInt(reminders['due_today_count']),
      accomplishmentsToday: parseInt(accomplishments['today_count']),
      todayHoursLabel: (accomplishments['today_hours_label'] ?? '00:00')
          .toString(),
      attendanceNotice: (attendance['notice'] ?? '').toString(),
      attendanceStatusLabel: (attendance['status_label'] ?? '').toString(),
      unassignedSupportCount: parseInt(support['unassigned_count']),
      ongoingTasks: ongoing,
    );
  }
}

class OngoingTask {
  const OngoingTask({
    required this.id,
    required this.title,
    required this.subtitle,
    required this.reportedDate,
    required this.dueDate,
    required this.priority,
    required this.progress,
  });

  final int id;
  final String title;
  final String subtitle;
  final String reportedDate;
  final String dueDate;
  final String priority;
  final int progress;

  factory OngoingTask.fromJson(Map<String, dynamic> json) {
    int parseInt(dynamic v) => int.tryParse((v ?? '0').toString()) ?? 0;
    return OngoingTask(
      id: parseInt(json['id']),
      title: (json['title'] ?? 'Untitled task').toString(),
      subtitle: (json['subtitle'] ?? '').toString(),
      reportedDate: (json['reported_date'] ?? '').toString(),
      dueDate: (json['due_date'] ?? '').toString(),
      priority: (json['priority'] ?? '').toString(),
      progress: parseInt(json['progress']).clamp(0, 100),
    );
  }
}
