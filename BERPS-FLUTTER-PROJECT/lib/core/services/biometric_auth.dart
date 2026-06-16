import 'package:flutter/foundation.dart';
import 'package:flutter/services.dart';
import 'package:local_auth/local_auth.dart';

class BiometricAuth {
  BiometricAuth._();
  static final BiometricAuth _instance = BiometricAuth._();
  factory BiometricAuth() => _instance;

  final LocalAuthentication _localAuth = LocalAuthentication();

  /// Checks whether the device supports biometric authentication.
  Future<bool> get isAvailable async {
    if (kIsWeb) return false;

    try {
      final canCheck = await _localAuth.canCheckBiometrics;
      final isDeviceSupported = await _localAuth.isDeviceSupported();
      return canCheck && isDeviceSupported;
    } on MissingPluginException {
      return false;
    } catch (_) {
      return false;
    }
  }

  /// Returns the list of enrolled biometric types (fingerprint, face, etc.)
  Future<List<BiometricType>> get enrolledTypes async {
    if (kIsWeb) return const [];

    try {
      return _localAuth.getAvailableBiometrics();
    } on MissingPluginException {
      return const [];
    } catch (_) {
      return const [];
    }
  }

  /// Prompts the user for biometric authentication.
  /// Returns `true` if authenticated, `false` if cancelled or failed.
  Future<bool> authenticate({
    String localizedReason = 'Verify your identity',
  }) async {
    if (kIsWeb) return false;

    try {
      return await _localAuth.authenticate(
        localizedReason: localizedReason,
        biometricOnly: false,
      );
    } catch (e) {
      return false;
    }
  }

  /// Human-readable label for the enrolled biometric type.
  Future<String> get label async {
    final types = await enrolledTypes;
    if (types.contains(BiometricType.face)) return 'Face ID';
    if (types.contains(BiometricType.iris)) return 'Iris';
    if (types.contains(BiometricType.fingerprint)) return 'Fingerprint';
    return 'Biometric';
  }
}
