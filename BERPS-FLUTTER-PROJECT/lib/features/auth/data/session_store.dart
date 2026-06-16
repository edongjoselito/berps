import 'dart:convert';

import 'package:shared_preferences/shared_preferences.dart';

import '../domain/staff_session.dart';

class SessionStore {
  SessionStore(this._preferences);

  final SharedPreferences _preferences;

  static const _sessionKey = 'berps_staff_session';
  static const _baseUrlKey = 'berps_base_url';
  static const _pairedKey = 'berps_is_paired';
  static const _biometricKey = 'berps_biometric_enabled';
  static const _biometricCredentialsKey = 'berps_biometric_credentials';
  static const _privacyConsentKey = 'berps_privacy_consent_v1';

  String readBaseUrl() => _preferences.getString(_baseUrlKey) ?? '';

  Future<void> saveBaseUrl(String baseUrl) =>
      _preferences.setString(_baseUrlKey, baseUrl);

  bool readIsPaired() => _preferences.getBool(_pairedKey) ?? false;

  Future<void> savePairing(String baseUrl) async {
    await saveBaseUrl(baseUrl);
    await _preferences.setBool(_pairedKey, true);
  }

  StaffSession? readSession() {
    final raw = _preferences.getString(_sessionKey);
    if (raw == null || raw.isEmpty) return null;
    try {
      final decoded = jsonDecode(raw);
      if (decoded is! Map) return null;
      return StaffSession.fromStorage(Map<String, dynamic>.from(decoded));
    } catch (_) {
      return null;
    }
  }

  Future<void> saveSession(StaffSession session) async {
    await savePairing(session.baseUrl);
    await _preferences.setString(_sessionKey, jsonEncode(session.toJson()));
  }

  Future<void> clearSession() => _preferences.remove(_sessionKey);

  Future<void> clearPairing() async {
    await clearSession();
    await _preferences.remove(_baseUrlKey);
    await _preferences.remove(_pairedKey);
    await _preferences.remove(_biometricKey);
    await _preferences.remove(_biometricCredentialsKey);
  }

  bool readBiometricEnabled() => _preferences.getBool(_biometricKey) ?? false;

  Future<void> saveBiometricEnabled(bool enabled) =>
      _preferences.setBool(_biometricKey, enabled);

  String? readBiometricCredentials() =>
      _preferences.getString(_biometricCredentialsKey);

  Future<void> saveBiometricCredentials(String credentials) =>
      _preferences.setString(_biometricCredentialsKey, credentials);

  Future<void> clearBiometricCredentials() =>
      _preferences.remove(_biometricCredentialsKey);

  bool readPrivacyConsent() =>
      _preferences.getBool(_privacyConsentKey) ?? false;

  Future<void> savePrivacyConsent(bool accepted) =>
      _preferences.setBool(_privacyConsentKey, accepted);

  Future<void> clearPrivacyConsent() => _preferences.remove(_privacyConsentKey);
}
