class AnnualGoal {
  const AnnualGoal({
    required this.goalId,
    required this.year,
    required this.targetClients,
    required this.targetIncome,
    required this.actualClients,
    required this.actualIncome,
    required this.clientsProgressPct,
    required this.incomeProgressPct,
    required this.notes,
    required this.createdBy,
  });

  final int goalId;
  final int year;
  final int targetClients;
  final double targetIncome;
  final int actualClients;
  final double actualIncome;
  final double clientsProgressPct;
  final double incomeProgressPct;
  final String notes;
  final String createdBy;

  factory AnnualGoal.fromJson(Map<String, dynamic> json) {
    double d(String k) =>
        double.tryParse((json[k] ?? '0').toString()) ?? 0;
    int i(String k) => int.tryParse((json[k] ?? '0').toString()) ?? 0;
    return AnnualGoal(
      goalId: i('goal_id'),
      year: i('year'),
      targetClients: i('target_clients'),
      targetIncome: d('target_income'),
      actualClients: i('actual_clients'),
      actualIncome: d('actual_income'),
      clientsProgressPct: d('clients_progress_pct'),
      incomeProgressPct: d('income_progress_pct'),
      notes: (json['notes'] ?? '').toString(),
      createdBy: (json['created_by'] ?? '').toString(),
    );
  }
}

class AnnualGoalsData {
  const AnnualGoalsData({
    required this.currentYear,
    required this.current,
    required this.goals,
  });

  final int currentYear;
  final AnnualGoal? current;
  final List<AnnualGoal> goals;

  factory AnnualGoalsData.fromJson(Map<String, dynamic> json) {
    final list = (json['goals'] as List?)
            ?.whereType<Map>()
            .map((g) => AnnualGoal.fromJson(Map<String, dynamic>.from(g)))
            .toList() ??
        const [];
    final currentRaw = json['current'];
    return AnnualGoalsData(
      currentYear:
          int.tryParse((json['current_year'] ?? '0').toString()) ?? 0,
      current: currentRaw is Map
          ? AnnualGoal.fromJson(Map<String, dynamic>.from(currentRaw))
          : null,
      goals: list,
    );
  }
}

class AnnualGoalMonth {
  const AnnualGoalMonth({
    required this.month,
    required this.label,
    required this.clients,
    required this.income,
  });
  final int month;
  final String label;
  final int clients;
  final double income;

  factory AnnualGoalMonth.fromJson(Map<String, dynamic> json) {
    return AnnualGoalMonth(
      month: int.tryParse((json['month'] ?? '0').toString()) ?? 0,
      label: (json['label'] ?? '').toString(),
      clients: int.tryParse((json['clients'] ?? '0').toString()) ?? 0,
      income: double.tryParse((json['income'] ?? '0').toString()) ?? 0,
    );
  }
}

class AnnualGoalDetail {
  const AnnualGoalDetail({
    required this.year,
    required this.goal,
    required this.monthly,
  });
  final int year;
  final AnnualGoal? goal;
  final List<AnnualGoalMonth> monthly;

  factory AnnualGoalDetail.fromJson(Map<String, dynamic> json) {
    final monthly = (json['monthly'] as List?)
            ?.whereType<Map>()
            .map((m) => AnnualGoalMonth.fromJson(Map<String, dynamic>.from(m)))
            .toList() ??
        const [];
    final raw = json['goal'];
    return AnnualGoalDetail(
      year: int.tryParse((json['year'] ?? '0').toString()) ?? 0,
      goal: raw is Map
          ? AnnualGoal.fromJson(Map<String, dynamic>.from(raw))
          : null,
      monthly: monthly,
    );
  }
}
