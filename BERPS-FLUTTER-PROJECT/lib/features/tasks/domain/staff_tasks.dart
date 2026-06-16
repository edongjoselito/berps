class StaffTasksData {
  const StaffTasksData({
    required this.today,
    required this.statusFilter,
    required this.taskScope,
    required this.hasTimeInToday,
    required this.stats,
    required this.projects,
    required this.staffOptions,
    required this.priorityOptions,
    required this.tasks,
  });

  final String today;
  final String statusFilter;
  final String taskScope;
  final bool hasTimeInToday;
  final TaskStats stats;
  final List<ProjectOption> projects;
  final List<StaffOption> staffOptions;
  final List<PriorityOption> priorityOptions;
  final List<StaffTask> tasks;

  factory StaffTasksData.fromJson(Map<String, dynamic> json) {
    final stats = json['stats'] is Map
        ? Map<String, dynamic>.from(json['stats'] as Map)
        : <String, dynamic>{};

    return StaffTasksData(
      today: (json['today'] ?? '').toString(),
      statusFilter: (json['status_filter'] ?? 'open').toString(),
      taskScope: (json['task_scope'] ?? '').toString(),
      hasTimeInToday: _toBool(json['has_time_in_today']),
      stats: TaskStats.fromJson(stats),
      projects: _mapList(json['projects'], ProjectOption.fromJson),
      staffOptions: _mapList(json['staff_options'], StaffOption.fromJson),
      priorityOptions:
          _mapList(json['priority_options'], PriorityOption.fromJson),
      tasks: _mapList(json['tasks'], StaffTask.fromJson),
    );
  }
}

class TaskStats {
  const TaskStats({
    required this.open,
    required this.closed,
    required this.dueToday,
    required this.dueSoon,
    required this.overdue,
    required this.undated,
    required this.forwarded,
  });

  final int open;
  final int closed;
  final int dueToday;
  final int dueSoon;
  final int overdue;
  final int undated;
  final int forwarded;

  factory TaskStats.fromJson(Map<String, dynamic> json) {
    return TaskStats(
      open: _toInt(json['open']),
      closed: _toInt(json['closed']),
      dueToday: _toInt(json['due_today']),
      dueSoon: _toInt(json['due_soon']),
      overdue: _toInt(json['overdue']),
      undated: _toInt(json['undated']),
      forwarded: _toInt(json['forwarded']),
    );
  }
}

class ProjectOption {
  const ProjectOption({required this.id, required this.name});

  final int id;
  final String name;

  factory ProjectOption.fromJson(Map<String, dynamic> json) {
    return ProjectOption(
      id: _toInt(json['id']),
      name: (json['name'] ?? '').toString(),
    );
  }
}

class StaffOption {
  const StaffOption({required this.userId, required this.name});

  final int userId;
  final String name;

  factory StaffOption.fromJson(Map<String, dynamic> json) {
    return StaffOption(
      userId: _toInt(json['user_id']),
      name: (json['name'] ?? '').toString(),
    );
  }
}

class PriorityOption {
  const PriorityOption({required this.value, required this.label});

  final String value;
  final String label;

  factory PriorityOption.fromJson(Map<String, dynamic> json) {
    return PriorityOption(
      value: (json['value'] ?? '').toString(),
      label: (json['label'] ?? '').toString(),
    );
  }
}

class StaffTask {
  const StaffTask({
    required this.id,
    required this.title,
    required this.reportedDate,
    required this.dueDate,
    required this.statusValue,
    required this.status,
    required this.priorityValue,
    required this.priorityLabel,
    required this.projectId,
    required this.projectName,
    required this.assignedPersonId,
    required this.assignedPersonName,
    required this.attachmentLink,
    required this.adminComment,
    required this.latestCommentDate,
    required this.latestCommentId,
    required this.supportIssueId,
    required this.supportTicketNumber,
    required this.addedBy,
    required this.forwardedFrom,
    required this.forwardedNote,
    required this.isForwardedTask,
    required this.isForwardedPending,
    required this.dueMetaLabel,
    required this.dueMetaType,
  });

  final int id;
  final String title;
  final String reportedDate;
  final String dueDate;
  final String statusValue;
  final String status;
  final String priorityValue;
  final String priorityLabel;
  final int projectId;
  final String projectName;
  final int assignedPersonId;
  final String assignedPersonName;
  final String attachmentLink;
  final String adminComment;
  final String latestCommentDate;
  final int latestCommentId;
  final int supportIssueId;
  final String supportTicketNumber;
  final String addedBy;
  final int forwardedFrom;
  final String forwardedNote;
  final bool isForwardedTask;
  final bool isForwardedPending;
  final String dueMetaLabel;
  final String dueMetaType;

  bool get isClosed => statusValue == '0';

  factory StaffTask.fromJson(Map<String, dynamic> json) {
    return StaffTask(
      id: _toInt(json['id']),
      title: (json['title'] ?? '').toString(),
      reportedDate: (json['reported_date'] ?? '').toString(),
      dueDate: (json['due_date'] ?? '').toString(),
      statusValue: (json['status_value'] ?? '1').toString(),
      status: (json['status'] ?? 'open').toString(),
      priorityValue: (json['priority_value'] ?? '2').toString(),
      priorityLabel: (json['priority_label'] ?? 'Medium').toString(),
      projectId: _toInt(json['project_id']),
      projectName: (json['project_name'] ?? '').toString(),
      assignedPersonId: _toInt(json['assigned_person_id']),
      assignedPersonName: (json['assigned_person_name'] ?? '').toString(),
      attachmentLink: (json['attachment_link'] ?? '').toString(),
      adminComment: (json['admin_comment'] ?? '').toString(),
      latestCommentDate: (json['latest_comment_date'] ?? '').toString(),
      latestCommentId: _toInt(json['latest_comment_id']),
      supportIssueId: _toInt(json['support_issue_id']),
      supportTicketNumber: (json['support_ticket_number'] ?? '').toString(),
      addedBy: (json['added_by'] ?? '').toString(),
      forwardedFrom: _toInt(json['forwarded_from']),
      forwardedNote: (json['forwarded_note'] ?? '').toString(),
      isForwardedTask: _toBool(json['is_forwarded_task']),
      isForwardedPending: _toBool(json['is_forwarded_pending']),
      dueMetaLabel: (json['due_meta_label'] ?? '').toString(),
      dueMetaType: (json['due_meta_type'] ?? '').toString(),
    );
  }
}

class StaffTaskDetail {
  const StaffTaskDetail({
    required this.task,
    required this.checklist,
    required this.history,
  });

  final StaffTask task;
  final List<TaskChecklistItem> checklist;
  final List<TaskHistoryEntry> history;

  factory StaffTaskDetail.fromJson(Map<String, dynamic> json) {
    final taskMap = json['task'] is Map
        ? Map<String, dynamic>.from(json['task'] as Map)
        : <String, dynamic>{};

    return StaffTaskDetail(
      task: StaffTask.fromJson(taskMap),
      checklist: _mapList(json['checklist'], TaskChecklistItem.fromJson),
      history: _mapList(json['history'], TaskHistoryEntry.fromJson),
    );
  }
}

class TaskChecklistItem {
  const TaskChecklistItem({
    required this.id,
    required this.itemDescription,
    required this.status,
    required this.isCompleted,
  });

  final int id;
  final String itemDescription;
  final String status;
  final bool isCompleted;

  Map<String, dynamic> toJson() => {
        'id': id,
        'item_description': itemDescription,
        'status': status,
        'is_completed': isCompleted,
      };

  factory TaskChecklistItem.fromJson(Map<String, dynamic> json) {
    return TaskChecklistItem(
      id: _toInt(json['id']),
      itemDescription: (json['item_description'] ?? '').toString(),
      status: (json['status'] ?? 'Pending').toString(),
      isCompleted: _toBool(json['is_completed']),
    );
  }
}

class TaskHistoryEntry {
  const TaskHistoryEntry({
    required this.id,
    required this.note,
    required this.postedAt,
    required this.postedBy,
    required this.taskStatus,
    required this.points,
  });

  final int id;
  final String note;
  final String postedAt;
  final String postedBy;
  final String taskStatus;
  final int points;

  factory TaskHistoryEntry.fromJson(Map<String, dynamic> json) {
    return TaskHistoryEntry(
      id: _toInt(json['id']),
      note: (json['note'] ?? '').toString(),
      postedAt: (json['posted_at'] ?? '').toString(),
      postedBy: (json['posted_by'] ?? '').toString(),
      taskStatus: (json['task_status'] ?? '').toString(),
      points: _toInt(json['points']),
    );
  }
}

List<T> _mapList<T>(
  dynamic raw,
  T Function(Map<String, dynamic> json) mapper,
) {
  if (raw is! List) return <T>[];
  return raw
      .whereType<Map>()
      .map((e) => mapper(Map<String, dynamic>.from(e)))
      .toList();
}

int _toInt(dynamic value) => int.tryParse((value ?? '0').toString()) ?? 0;

bool _toBool(dynamic value) {
  if (value is bool) return value;
  final normalized = (value ?? '').toString().toLowerCase();
  return normalized == '1' || normalized == 'true';
}
