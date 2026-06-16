import 'package:flutter/widgets.dart';

/// Phone breakpoints — values are inclusive at the lower bound.
///
/// - tiny  : up to 359 (iPhone SE 1st gen / very small Androids)
/// - small : 360–389  (iPhone SE 2/3, mini, most compact phones)
/// - medium: 390–429  (iPhone 13/14/15, Pixel)
/// - large : 430+     (iPhone Pro Max, large foldables in portrait)
class Breakpoints {
  Breakpoints._();

  static const double tiny = 360;
  static const double small = 390;
  static const double medium = 430;
}

/// Convenience extensions for reading screen size and breakpoint info from a
/// [BuildContext]. Use these in build methods to keep layouts responsive
/// without sprinkling MediaQuery everywhere.
extension ResponsiveX on BuildContext {
  Size get screen => MediaQuery.of(this).size;
  double get sw => screen.width;
  double get sh => screen.height;

  bool get isTinyPhone => sw < Breakpoints.tiny;
  bool get isSmallPhone => sw < Breakpoints.small;
  bool get isLargePhone => sw >= Breakpoints.medium;

  /// Linear scale from 0.92 → 1.05 based on phone width so typography stays
  /// in a comfortable range on every device. Clamped so very large screens
  /// don't blow up text.
  double get textScale {
    final w = sw.clamp(320.0, 480.0);
    return ((w - 320) / 160) * 0.13 + 0.92;
  }

  /// Outer horizontal padding for screen-level containers.
  double get gutter {
    if (isTinyPhone) return 14;
    if (isSmallPhone) return 16;
    return 20;
  }
}
