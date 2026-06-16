class StaffRanking {
  const StaffRanking({
    required this.periodLabel,
    required this.year,
    required this.month,
    required this.totalEmployees,
    required this.totalPoints,
    required this.currentRank,
    required this.currentPoints,
    required this.top,
    required this.entries,
  });

  final String periodLabel;
  final int year;
  final int month;
  final int totalEmployees;
  final int totalPoints;
  final int? currentRank;
  final int currentPoints;
  final List<RankingEntry> top;
  final List<RankingEntry> entries;

  factory StaffRanking.fromJson(Map<String, dynamic> json) {
    final period = json['period'] is Map
        ? Map<String, dynamic>.from(json['period'] as Map)
        : <String, dynamic>{};
    final current = json['current'] is Map
        ? Map<String, dynamic>.from(json['current'] as Map)
        : <String, dynamic>{};

    int parseInt(dynamic v) => int.tryParse((v ?? '0').toString()) ?? 0;
    int? parseIntNullable(dynamic v) {
      if (v == null) return null;
      final s = v.toString();
      if (s.isEmpty || s == 'null') return null;
      return int.tryParse(s);
    }

    List<RankingEntry> mapList(dynamic raw) {
      if (raw is! List) return const <RankingEntry>[];
      return raw
          .whereType<Map>()
          .map((e) => RankingEntry.fromJson(Map<String, dynamic>.from(e)))
          .toList();
    }

    return StaffRanking(
      periodLabel: (period['label'] ?? '').toString(),
      year: parseInt(period['year']),
      month: parseInt(period['month']),
      totalEmployees: parseInt(json['total_employees']),
      totalPoints: parseInt(json['total_points']),
      currentRank: parseIntNullable(current['rank']),
      currentPoints: parseInt(current['points']),
      top: mapList(json['top']),
      entries: mapList(json['entries']),
    );
  }
}

class RankingEntry {
  const RankingEntry({
    required this.rank,
    required this.userId,
    required this.name,
    required this.role,
    required this.points,
    required this.lastDoneLabel,
    required this.isCurrent,
  });

  final int rank;
  final int userId;
  final String name;
  final String role;
  final int points;
  final String lastDoneLabel;
  final bool isCurrent;

  String get initials {
    final trimmed = name.trim();
    if (trimmed.isEmpty) return '?';
    final parts = trimmed.split(RegExp(r'\s+'));
    final first = parts.first.isNotEmpty ? parts.first[0] : '';
    final last = parts.length > 1 && parts.last.isNotEmpty ? parts.last[0] : '';
    final initials = (first + last).toUpperCase();
    return initials.isEmpty ? trimmed[0].toUpperCase() : initials;
  }

  factory RankingEntry.fromJson(Map<String, dynamic> json) {
    int parseInt(dynamic v) => int.tryParse((v ?? '0').toString()) ?? 0;
    bool parseBool(dynamic v) {
      if (v is bool) return v;
      final s = (v ?? '').toString().toLowerCase();
      return s == '1' || s == 'true';
    }

    return RankingEntry(
      rank: parseInt(json['rank']),
      userId: parseInt(json['user_id']),
      name: (json['name'] ?? '').toString(),
      role: (json['role'] ?? '').toString(),
      points: parseInt(json['points']),
      lastDoneLabel: (json['last_done_label'] ?? '').toString(),
      isCurrent: parseBool(json['is_current']),
    );
  }
}
