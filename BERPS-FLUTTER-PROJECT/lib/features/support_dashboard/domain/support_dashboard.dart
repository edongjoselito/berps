class SupportDashboard {
  const SupportDashboard({
    required this.totals,
    required this.thisMonth,
    required this.avgResolutionHours,
    required this.byPriority,
    required this.byStatus,
    required this.byDepartment,
    required this.trend,
    required this.recent,
    required this.oldestOpen,
  });

  final SupportTotals totals;
  final SupportMonth thisMonth;
  final double avgResolutionHours;
  final List<SupportCount> byPriority;
  final List<SupportCount> byStatus;
  final List<SupportDepartment> byDepartment;
  final List<SupportTrendPoint> trend;
  final List<SupportTicketLite> recent;
  final List<SupportTicketLite> oldestOpen;

  factory SupportDashboard.fromJson(Map<String, dynamic> json) {
    Map<String, dynamic> m(String k) => json[k] is Map
        ? Map<String, dynamic>.from(json[k] as Map)
        : <String, dynamic>{};
    List<Map<String, dynamic>> l(String k) =>
        (json[k] as List?)
            ?.whereType<Map>()
            .map((e) => Map<String, dynamic>.from(e))
            .toList() ??
        const [];

    return SupportDashboard(
      totals: SupportTotals.fromJson(m('totals')),
      thisMonth: SupportMonth.fromJson(m('this_month')),
      avgResolutionHours:
          double.tryParse((json['avg_resolution_hours'] ?? '0').toString()) ??
              0,
      byPriority: l('by_priority').map(SupportCount.fromJson).toList(),
      byStatus: l('by_status').map(SupportCount.fromJson).toList(),
      byDepartment:
          l('by_department').map(SupportDepartment.fromJson).toList(),
      trend: l('trend').map(SupportTrendPoint.fromJson).toList(),
      recent: l('recent').map(SupportTicketLite.fromJson).toList(),
      oldestOpen:
          l('oldest_open').map(SupportTicketLite.fromJson).toList(),
    );
  }
}

class SupportTotals {
  const SupportTotals({
    required this.total,
    required this.open,
    required this.closed,
    required this.unassigned,
    required this.awaitingReply,
  });

  final int total;
  final int open;
  final int closed;
  final int unassigned;
  final int awaitingReply;

  factory SupportTotals.fromJson(Map<String, dynamic> json) {
    int i(String k) => int.tryParse((json[k] ?? '0').toString()) ?? 0;
    return SupportTotals(
      total: i('total'),
      open: i('open'),
      closed: i('closed'),
      unassigned: i('unassigned'),
      awaitingReply: i('awaiting_reply'),
    );
  }
}

class SupportMonth {
  const SupportMonth({required this.created, required this.closed});
  final int created;
  final int closed;

  factory SupportMonth.fromJson(Map<String, dynamic> json) {
    int i(String k) => int.tryParse((json[k] ?? '0').toString()) ?? 0;
    return SupportMonth(created: i('created'), closed: i('closed'));
  }
}

class SupportCount {
  const SupportCount({required this.key, required this.count});
  final String key;
  final int count;

  factory SupportCount.fromJson(Map<String, dynamic> json) {
    return SupportCount(
      key: (json['key'] ?? '').toString(),
      count: int.tryParse((json['count'] ?? '0').toString()) ?? 0,
    );
  }
}

class SupportDepartment {
  const SupportDepartment({
    required this.id,
    required this.name,
    required this.total,
    required this.open,
  });

  final int id;
  final String name;
  final int total;
  final int open;

  factory SupportDepartment.fromJson(Map<String, dynamic> json) {
    int i(String k) => int.tryParse((json[k] ?? '0').toString()) ?? 0;
    return SupportDepartment(
      id: i('id'),
      name: (json['name'] ?? '').toString(),
      total: i('total'),
      open: i('open'),
    );
  }
}

class SupportTrendPoint {
  const SupportTrendPoint({
    required this.date,
    required this.label,
    required this.created,
    required this.closed,
  });

  final String date;
  final String label;
  final int created;
  final int closed;

  factory SupportTrendPoint.fromJson(Map<String, dynamic> json) {
    int i(String k) => int.tryParse((json[k] ?? '0').toString()) ?? 0;
    return SupportTrendPoint(
      date: (json['date'] ?? '').toString(),
      label: (json['label'] ?? '').toString(),
      created: i('created'),
      closed: i('closed'),
    );
  }
}

class SupportTicketLite {
  const SupportTicketLite({
    required this.id,
    required this.ticketNumber,
    required this.title,
    required this.status,
    required this.priority,
    required this.createdLabel,
    required this.department,
    required this.assignee,
  });

  final int id;
  final String ticketNumber;
  final String title;
  final String status;
  final String priority;
  final String createdLabel;
  final String department;
  final String assignee;

  factory SupportTicketLite.fromJson(Map<String, dynamic> json) {
    return SupportTicketLite(
      id: int.tryParse((json['id'] ?? '0').toString()) ?? 0,
      ticketNumber: (json['ticket_number'] ?? '').toString(),
      title: (json['title'] ?? '').toString(),
      status: (json['status'] ?? '').toString(),
      priority: (json['priority'] ?? '').toString(),
      createdLabel: (json['created_label'] ?? '').toString(),
      department: (json['department'] ?? '').toString(),
      assignee: (json['assignee'] ?? '').toString(),
    );
  }
}
