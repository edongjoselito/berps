class StaffNotification {
  const StaffNotification({
    required this.id,
    required this.source,
    required this.title,
    required this.message,
    required this.actorName,
    required this.isSeen,
    required this.createdLabel,
    required this.createdAt,
    required this.taskId,
    required this.issueId,
    required this.ticketNumber,
  });

  factory StaffNotification.fromJson(Map<String, dynamic> json) {
    return StaffNotification(
      id: (json['id'] ?? '').toString(),
      source: (json['source'] ?? 'task').toString(),
      title: (json['title'] ?? '').toString(),
      message: (json['message'] ?? '').toString(),
      actorName: (json['actor_name'] ?? '').toString(),
      isSeen: json['is_seen'] == true,
      createdLabel: (json['created_label'] ?? '').toString(),
      createdAt: (json['created_at'] ?? '').toString(),
      taskId: (json['task_id'] is num) ? (json['task_id'] as num).toInt() : 0,
      issueId: (json['issue_id'] is num) ? (json['issue_id'] as num).toInt() : 0,
      ticketNumber: (json['ticket_number'] ?? '').toString(),
    );
  }

  final String id;
  final String source; // 'task' or 'support'
  final String title;
  final String message;
  final String actorName;
  final bool isSeen;
  final String createdLabel;
  final String createdAt;
  final int taskId;
  final int issueId;
  final String ticketNumber;

  bool get isSupport => source == 'support';
}

class StaffNotificationsData {
  const StaffNotificationsData({
    required this.notifications,
    required this.unseenTotal,
  });

  factory StaffNotificationsData.fromJson(Map<String, dynamic> json) {
    final list = (json['notifications'] as List<dynamic>? ?? [])
        .whereType<Map<String, dynamic>>()
        .map(StaffNotification.fromJson)
        .toList();
    final total = json['unseen_total'] is num
        ? (json['unseen_total'] as num).toInt()
        : (json['count'] is num ? (json['count'] as num).toInt() : 0);
    return StaffNotificationsData(
      notifications: list,
      unseenTotal: total,
    );
  }

  final List<StaffNotification> notifications;
  final int unseenTotal;
}
