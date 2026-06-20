class StaffSession {
  const StaffSession({
    required this.baseUrl,
    required this.token,
    required this.userId,
    required this.username,
    required this.fullName,
    required this.firstName,
    required this.lastName,
    required this.email,
    required this.position,
    required this.avatarUrl,
    required this.settingsId,
    this.features = const <String>[],
    this.dashboardMode = 'default',
  });

  final String baseUrl;
  final String token;
  final int userId;
  final String username;
  final String fullName;
  final String firstName;
  final String lastName;
  final String email;
  final String position;
  final String avatarUrl;
  final int settingsId;

  /// Enabled company feature keys for this workspace. Mirrors the web sidebar
  /// gating (`company_features`). An empty list means "no restrictions" — the
  /// account has full access to every module.
  final List<String> features;

  /// Mobile home dashboard mode for this workspace. `'calendar'` replaces the
  /// snapshot dashboard with a full-month calendar; anything else = default.
  final String dashboardMode;

  /// Whether the home dashboard should render as a full-month calendar.
  bool get dashboardIsCalendar => dashboardMode == 'calendar';

  // ── Feature gating ─────────────────────────────────────────────────────────

  static const _payrollFeatures = {
    'payroll',
    'employee_payroll',
    'salary_computation',
    'payroll_reports',
  };

  /// True when the workspace restricts modules to an explicit feature set.
  bool get hasFeatureRestrictions => features.isNotEmpty;

  /// Whether [key] is available. Unrestricted workspaces allow everything.
  bool hasFeature(String key) =>
      !hasFeatureRestrictions || features.contains(key);

  bool get hasTasks => hasFeature('tasks');
  bool get hasCalendar => hasFeature('calendar');
  bool get hasNotes => hasFeature('notes');
  bool get hasSupport => hasFeature('support');

  /// Reminders are date/time based and ride along with the calendar feature.
  bool get hasReminders => hasCalendar;

  /// Attendance/DTR is only surfaced when there are no restrictions or the
  /// workspace has a payroll-family feature (matches the web sidebar).
  bool get hasAttendance =>
      !hasFeatureRestrictions || features.any(_payrollFeatures.contains);

  /// "Package 2" = the Task Management Suite (tasks + notes + calendar only).
  bool get isPackage2 =>
      hasFeatureRestrictions &&
      features.length == 3 &&
      features.toSet().containsAll(const {'tasks', 'notes', 'calendar'});

  bool get hasMyDtr => hasAttendance && !isPackage2;
  bool get hasRanking => hasTasks && !isPackage2;
  bool get hasForwardedTasks => hasTasks && !isPackage2;

  /// "Lastname, Firstname" — falls back gracefully when one side is missing.
  String get formalName {
    final f = firstName.trim();
    final l = lastName.trim();
    if (f.isEmpty && l.isEmpty) return fullName.isEmpty ? username : fullName;
    if (l.isEmpty) return f;
    if (f.isEmpty) return l;
    return '$l, $f';
  }

  static List<String> _parseFeatures(dynamic value) {
    if (value is List) {
      return value
          .map((e) => e.toString().trim())
          .where((e) => e.isNotEmpty)
          .toList(growable: false);
    }
    return const <String>[];
  }

  factory StaffSession.fromApi(
    Map<String, dynamic> json, {
    required String baseUrl,
    String? fallbackToken,
  }) {
    final user = (json['user'] is Map)
        ? Map<String, dynamic>.from(json['user'] as Map)
        : <String, dynamic>{};

    return StaffSession(
      baseUrl: baseUrl,
      token: (json['token'] ?? fallbackToken ?? '').toString(),
      userId: int.tryParse((user['user_id'] ?? '0').toString()) ?? 0,
      username: (user['username'] ?? '').toString(),
      fullName: (user['full_name'] ?? user['username'] ?? '').toString(),
      firstName: (user['first_name'] ?? '').toString(),
      lastName: (user['last_name'] ?? '').toString(),
      email: (user['email'] ?? '').toString(),
      position: (user['position'] ?? '').toString(),
      avatarUrl: (user['avatar_url'] ?? '').toString(),
      settingsId: int.tryParse((user['settings_id'] ?? '0').toString()) ?? 0,
      features: _parseFeatures(user['features']),
      dashboardMode: (user['dashboard_mode'] ?? 'default').toString(),
    );
  }

  factory StaffSession.fromStorage(Map<String, dynamic> json) {
    return StaffSession(
      baseUrl: (json['baseUrl'] ?? '').toString(),
      token: (json['token'] ?? '').toString(),
      userId: int.tryParse((json['userId'] ?? '0').toString()) ?? 0,
      username: (json['username'] ?? '').toString(),
      fullName: (json['fullName'] ?? '').toString(),
      firstName: (json['firstName'] ?? '').toString(),
      lastName: (json['lastName'] ?? '').toString(),
      email: (json['email'] ?? '').toString(),
      position: (json['position'] ?? '').toString(),
      avatarUrl: (json['avatarUrl'] ?? '').toString(),
      settingsId: int.tryParse((json['settingsId'] ?? '0').toString()) ?? 0,
      features: _parseFeatures(json['features']),
      dashboardMode: (json['dashboardMode'] ?? 'default').toString(),
    );
  }

  StaffSession copyWith({
    String? avatarUrl,
    String? fullName,
    String? firstName,
    String? lastName,
    String? email,
    String? position,
  }) {
    return StaffSession(
      baseUrl: baseUrl,
      token: token,
      userId: userId,
      username: username,
      fullName: fullName ?? this.fullName,
      firstName: firstName ?? this.firstName,
      lastName: lastName ?? this.lastName,
      email: email ?? this.email,
      position: position ?? this.position,
      avatarUrl: avatarUrl ?? this.avatarUrl,
      settingsId: settingsId,
      features: features,
      dashboardMode: dashboardMode,
    );
  }

  Map<String, dynamic> toJson() => {
    'baseUrl': baseUrl,
    'token': token,
    'userId': userId,
    'username': username,
    'fullName': fullName,
    'firstName': firstName,
    'lastName': lastName,
    'email': email,
    'position': position,
    'avatarUrl': avatarUrl,
    'settingsId': settingsId,
    'features': features,
    'dashboardMode': dashboardMode,
  };
}
