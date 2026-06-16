class MyDtrData {
  const MyDtrData({
    required this.month,
    required this.year,
    required this.rows,
    required this.monthTotalSeconds,
    required this.monthTotalLabel,
    required this.presentDays,
    required this.absentDays,
    required this.pendingDays,
  });

  final int month;
  final int year;
  final List<MyDtrRow> rows;
  final int monthTotalSeconds;
  final String monthTotalLabel;
  final int presentDays;
  final int absentDays;
  final int pendingDays;

  factory MyDtrData.fromJson(Map<String, dynamic> json) {
    final rowList = (json['rows'] as List?) ?? const [];
    return MyDtrData(
      month: (json['month'] ?? 0) as int,
      year: (json['year'] ?? 0) as int,
      rows: rowList
          .whereType<Map>()
          .map((e) => MyDtrRow.fromJson(Map<String, dynamic>.from(e)))
          .toList(),
      monthTotalSeconds: (json['month_total_seconds'] ?? 0) as int,
      monthTotalLabel: (json['month_total_label'] ?? '').toString(),
      presentDays: (json['present_days'] ?? 0) as int,
      absentDays: (json['absent_days'] ?? 0) as int,
      pendingDays: (json['pending_days'] ?? 0) as int,
    );
  }
}

class MyDtrRow {
  const MyDtrRow({
    required this.date,
    required this.amBreakdown,
    required this.pmBreakdown,
    required this.totalHours,
    required this.totalSeconds,
    required this.status,
  });

  final String date;
  final List<String> amBreakdown;
  final List<String> pmBreakdown;
  final String totalHours;
  final int totalSeconds;
  final String status;

  factory MyDtrRow.fromJson(Map<String, dynamic> json) {
    final am = (json['am_breakdown'] as List?) ?? const [];
    final pm = (json['pm_breakdown'] as List?) ?? const [];
    return MyDtrRow(
      date: (json['date'] ?? '').toString(),
      amBreakdown: am.whereType<String>().toList(),
      pmBreakdown: pm.whereType<String>().toList(),
      totalHours: (json['total_hours'] ?? '').toString(),
      totalSeconds: (json['total_seconds'] ?? 0) as int,
      status: (json['status'] ?? '').toString(),
    );
  }
}
