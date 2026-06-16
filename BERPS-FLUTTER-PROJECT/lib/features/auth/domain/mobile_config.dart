import 'package:flutter/material.dart';

class MobileConfig {
  const MobileConfig({
    required this.appName,
    required this.tagline,
    required this.description,
    required this.baseUrl,
    required this.apiBaseUrl,
    required this.logoUrl,
    required this.wordmarkUrl,
    required this.allowedRoles,
    required this.themePrimary,
    required this.themePrimaryDark,
    required this.themeBackground,
  });

  final String appName;
  final String tagline;
  final String description;
  final String baseUrl;
  final String apiBaseUrl;
  final String logoUrl;
  final String wordmarkUrl;
  final List<String> allowedRoles;
  final Color themePrimary;
  final Color themePrimaryDark;
  final Color themeBackground;

  factory MobileConfig.fromJson(Map<String, dynamic> json) {
    final theme = (json['theme'] is Map)
        ? Map<String, dynamic>.from(json['theme'] as Map)
        : <String, dynamic>{};
    final roles = json['allowed_roles'];

    return MobileConfig(
      appName: (json['app_name'] ?? 'BERPS').toString(),
      tagline: (json['tagline'] ?? 'Tasks · Invoices · Job Orders').toString(),
      description: (json['description'] ?? '').toString(),
      baseUrl: (json['base_url'] ?? '').toString(),
      apiBaseUrl: (json['api_base_url'] ?? '').toString(),
      logoUrl: (json['logo_url'] ?? '').toString(),
      wordmarkUrl: (json['wordmark_url'] ?? '').toString(),
      allowedRoles: roles is List
          ? roles.map((e) => e.toString()).toList()
          : const <String>['Staff'],
      themePrimary: _parseColor(theme['primary'], const Color(0xFF1B5ED6)),
      themePrimaryDark:
          _parseColor(theme['primary_dark'], const Color(0xFF114CB3)),
      themeBackground:
          _parseColor(theme['background'], const Color(0xFFE8F1FB)),
    );
  }

  static Color _parseColor(dynamic value, Color fallback) {
    if (value is! String) return fallback;
    var hex = value.trim().replaceFirst('#', '');
    if (hex.length == 6) hex = 'FF$hex';
    final parsed = int.tryParse(hex, radix: 16);
    return parsed == null ? fallback : Color(parsed);
  }
}
