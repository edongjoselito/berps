import 'package:flutter/services.dart';

/// Thin wrapper around [HapticFeedback] so we have a single place to tweak the
/// app's tactile vocabulary. All calls are fire-and-forget; failures on
/// devices without haptic support are silently swallowed by the platform.
class Haptics {
  Haptics._();

  /// Subtle "tap" used for nav switches, chips, and routine selection.
  static void light() {
    HapticFeedback.selectionClick();
  }

  /// A slightly heavier confirmation feel for primary buttons.
  static void medium() {
    HapticFeedback.mediumImpact();
  }

  /// Reserved for big moments — time-in / time-out, sign-in success.
  static void success() {
    HapticFeedback.heavyImpact();
  }

  /// Used for errors / destructive confirmations.
  static void warn() {
    HapticFeedback.vibrate();
  }
}
