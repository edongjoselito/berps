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

  /// "Lastname, Firstname" — falls back gracefully when one side is missing.
  String get formalName {
    final f = firstName.trim();
    final l = lastName.trim();
    if (f.isEmpty && l.isEmpty) return fullName.isEmpty ? username : fullName;
    if (l.isEmpty) return f;
    if (f.isEmpty) return l;
    return '$l, $f';
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
      };
}
