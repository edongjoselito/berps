class StaffAttendanceData {
  const StaffAttendanceData({
    required this.today,
    required this.range,
    required this.summary,
    required this.status,
    required this.records,
  });

  final String today;
  final AttendanceRange range;
  final AttendanceSummary summary;
  final AttendanceStatus status;
  final List<AttendanceRecord> records;

  factory StaffAttendanceData.fromJson(Map<String, dynamic> json) {
    final range = json['range'] is Map
        ? Map<String, dynamic>.from(json['range'] as Map)
        : <String, dynamic>{};
    final summary = json['summary'] is Map
        ? Map<String, dynamic>.from(json['summary'] as Map)
        : <String, dynamic>{};
    final status = json['attendance'] is Map
        ? Map<String, dynamic>.from(json['attendance'] as Map)
        : <String, dynamic>{};
    final recordsRaw = json['records'];

    return StaffAttendanceData(
      today: (json['today'] ?? '').toString(),
      range: AttendanceRange.fromJson(range),
      summary: AttendanceSummary.fromJson(summary),
      status: AttendanceStatus.fromJson(status),
      records: recordsRaw is List
          ? recordsRaw
              .whereType<Map>()
              .map((e) => AttendanceRecord.fromJson(Map<String, dynamic>.from(e)))
              .toList()
          : const <AttendanceRecord>[],
    );
  }
}

class AttendanceRange {
  const AttendanceRange({required this.from, required this.to});

  final String from;
  final String to;

  factory AttendanceRange.fromJson(Map<String, dynamic> json) {
    return AttendanceRange(
      from: (json['from'] ?? '').toString(),
      to: (json['to'] ?? '').toString(),
    );
  }
}

class AttendanceSummary {
  const AttendanceSummary({
    required this.presentDays,
    required this.pendingDays,
    required this.absentDays,
    required this.accomplishmentCount,
    required this.totalSeconds,
    required this.totalHoursLabel,
  });

  final int presentDays;
  final int pendingDays;
  final int absentDays;
  final int accomplishmentCount;
  final int totalSeconds;
  final String totalHoursLabel;

  factory AttendanceSummary.fromJson(Map<String, dynamic> json) {
    return AttendanceSummary(
      presentDays: _toInt(json['present_days']),
      pendingDays: _toInt(json['pending_days']),
      absentDays: _toInt(json['absent_days']),
      accomplishmentCount: _toInt(json['accomplishment_count']),
      totalSeconds: _toInt(json['total_seconds']),
      totalHoursLabel: (json['total_hours_label'] ?? '00:00').toString(),
    );
  }
}

class AttendanceStatus {
  const AttendanceStatus({
    required this.notice,
    required this.statusLabel,
    required this.canTimeIn,
    required this.canTimeOut,
    required this.latestTimeInLabel,
    required this.latestTimeOutLabel,
    required this.openSlotLabel,
    required this.hasRecordToday,
  });

  final String notice;
  final String statusLabel;
  final bool canTimeIn;
  final bool canTimeOut;
  final String latestTimeInLabel;
  final String latestTimeOutLabel;
  final String openSlotLabel;
  final bool hasRecordToday;

  factory AttendanceStatus.fromJson(Map<String, dynamic> json) {
    return AttendanceStatus(
      notice: (json['notice'] ?? '').toString(),
      statusLabel: (json['status_label'] ?? '').toString(),
      canTimeIn: _toBool(json['can_time_in']),
      canTimeOut: _toBool(json['can_time_out']),
      latestTimeInLabel: (json['latest_time_in_label'] ?? '').toString(),
      latestTimeOutLabel: (json['latest_time_out_label'] ?? '').toString(),
      openSlotLabel: (json['open_slot_label'] ?? '').toString(),
      hasRecordToday: _toBool(json['has_record_today']),
    );
  }
}

class AttendanceRecord {
  const AttendanceRecord({
    required this.date,
    required this.dateLabel,
    required this.status,
    required this.intervals,
    required this.totalSeconds,
    required this.totalHoursLabel,
    required this.accomplishmentCount,
  });

  final String date;
  final String dateLabel;
  final String status;
  final List<AttendanceInterval> intervals;
  final int totalSeconds;
  final String totalHoursLabel;
  final int accomplishmentCount;

  factory AttendanceRecord.fromJson(Map<String, dynamic> json) {
    final intervalsRaw = json['intervals'];

    return AttendanceRecord(
      date: (json['date'] ?? '').toString(),
      dateLabel: (json['date_label'] ?? '').toString(),
      status: (json['status'] ?? 'present').toString(),
      intervals: intervalsRaw is List
          ? intervalsRaw
              .whereType<Map>()
              .map((e) => AttendanceInterval.fromJson(Map<String, dynamic>.from(e)))
              .toList()
          : const <AttendanceInterval>[],
      totalSeconds: _toInt(json['total_seconds']),
      totalHoursLabel: (json['total_hours_label'] ?? '00:00').toString(),
      accomplishmentCount: _toInt(json['accomplishment_count']),
    );
  }
}

class AttendanceInterval {
  const AttendanceInterval({
    required this.label,
    required this.timeInLabel,
    required this.timeOutLabel,
    required this.seconds,
    required this.isOpen,
  });

  final String label;
  final String timeInLabel;
  final String timeOutLabel;
  final int seconds;
  final bool isOpen;

  factory AttendanceInterval.fromJson(Map<String, dynamic> json) {
    return AttendanceInterval(
      label: (json['label'] ?? '').toString(),
      timeInLabel: (json['time_in_label'] ?? '').toString(),
      timeOutLabel: (json['time_out_label'] ?? '').toString(),
      seconds: _toInt(json['seconds']),
      isOpen: _toBool(json['open']),
    );
  }
}

int _toInt(dynamic value) => int.tryParse((value ?? '0').toString()) ?? 0;

bool _toBool(dynamic value) {
  if (value is bool) return value;
  final normalized = (value ?? '').toString().toLowerCase();
  return normalized == '1' || normalized == 'true';
}
