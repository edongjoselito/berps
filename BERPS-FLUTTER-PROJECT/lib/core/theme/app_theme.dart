import 'package:flutter/material.dart';
import 'package:flutter/services.dart';

/// BERPS mobile palette — light sky-blue primary.
/// A friendlier modern blue (Tailwind-style) with cool neutrals
/// and a warm amber accent reserved for status emphasis.
///
/// The neutral tokens (backgrounds, surfaces, borders, text) are *adaptive*:
/// they resolve to a light or dark value depending on the active system
/// brightness. The active brightness is published once per frame by the root
/// [MaterialApp.builder] via [AppTheme.brightness]; every widget that reads a
/// token below therefore follows the phone's light/dark setting without having
/// to thread a [BuildContext] through hundreds of call sites.
class AppTheme {
  // --- Brand & semantic colors (identical in light and dark) -------------
  static const Color primary = Color(0xFF3B82F6);
  static const Color primaryDark = Color(0xFF2563EB);
  static const Color primaryDeeper = Color(0xFF1D4ED8);
  static const Color accent = Color(0xFFD9A24B);
  static const Color success = Color(0xFF16A34A);
  static const Color warning = Color(0xFFB97A0E);
  static const Color danger = Color(0xFFB91C1C);

  static const String fontFamily = 'Rubik';

  // --- Active brightness (set by the root MaterialApp.builder) -----------
  static Brightness _brightness = Brightness.light;

  /// Publishes the brightness of the resolved theme. Called once per frame
  /// from [MaterialApp.builder] so the adaptive getters below stay in sync
  /// with whichever [ThemeData] (light/dark) Material selected.
  static set brightness(Brightness value) => _brightness = value;

  static bool get isDark => _brightness == Brightness.dark;

  static _Palette get _p => isDark ? _dark : _light;

  // --- Adaptive neutral tokens (resolve against the active palette) ------
  static Color get background => _p.background;
  static Color get backgroundAlt => _p.backgroundAlt;
  static Color get surface => _p.surface;
  static Color get surfaceMuted => _p.surfaceMuted;
  static Color get border => _p.border;
  static Color get borderStrong => _p.borderStrong;
  static Color get primarySoft => _p.primarySoft;
  static Color get accentSoft => _p.accentSoft;
  static Color get textPrimary => _p.textPrimary;
  static Color get textSecondary => _p.textSecondary;
  static Color get textMuted => _p.textMuted;

  /// Reusable shadow tokens so cards share an exact elevation language.
  static List<BoxShadow> get shadowSoft => _p.shadowSoft;
  static List<BoxShadow> get shadowMedium => _p.shadowMedium;

  // --- Light / dark palettes ---------------------------------------------
  static const _Palette _light = _Palette(
    background: Color(0xFFF6F8FC),
    backgroundAlt: Color(0xFFEEF3FA),
    surface: Colors.white,
    surfaceMuted: Color(0xFFF1F5FB),
    border: Color(0xFFE2E8F2),
    borderStrong: Color(0xFFC9D3E3),
    primarySoft: Color(0xFFEFF6FF),
    accentSoft: Color(0xFFFBF3E2),
    textPrimary: Color(0xFF0F1B2D),
    textSecondary: Color(0xFF64748B),
    textMuted: Color(0xFF94A3B8),
    shadowSoft: [
      BoxShadow(color: Color(0x0A0F1E3A), blurRadius: 18, offset: Offset(0, 8)),
    ],
    shadowMedium: [
      BoxShadow(color: Color(0x140F1E3A), blurRadius: 24, offset: Offset(0, 14)),
    ],
  );

  static const _Palette _dark = _Palette(
    background: Color(0xFF0B1220),
    backgroundAlt: Color(0xFF0E1626),
    surface: Color(0xFF141E30),
    surfaceMuted: Color(0xFF1B2638),
    border: Color(0xFF263247),
    borderStrong: Color(0xFF35455F),
    primarySoft: Color(0xFF18283F),
    accentSoft: Color(0xFF2C2616),
    textPrimary: Color(0xFFE7ECF5),
    textSecondary: Color(0xFFA0AEC4),
    textMuted: Color(0xFF6E7E96),
    shadowSoft: [
      BoxShadow(color: Color(0x40000000), blurRadius: 18, offset: Offset(0, 8)),
    ],
    shadowMedium: [
      BoxShadow(color: Color(0x59000000), blurRadius: 24, offset: Offset(0, 14)),
    ],
  );

  static ThemeData light() => _build(_light, Brightness.light);
  static ThemeData dark() => _build(_dark, Brightness.dark);

  static ThemeData _build(_Palette p, Brightness brightness) {
    final isDark = brightness == Brightness.dark;
    final base = ThemeData(
      useMaterial3: true,
      brightness: brightness,
      colorScheme: ColorScheme.fromSeed(
        seedColor: primary,
        primary: primary,
        brightness: brightness,
        surface: p.surface,
        onSurface: p.textPrimary,
      ),
      scaffoldBackgroundColor: p.background,
      fontFamily: fontFamily,
      splashFactory: InkSparkle.splashFactory,
    );

    // Status bar / nav bar icons follow the active brightness.
    final overlayStyle = SystemUiOverlayStyle(
      statusBarColor: Colors.transparent,
      statusBarIconBrightness: isDark ? Brightness.light : Brightness.dark,
      statusBarBrightness: isDark ? Brightness.dark : Brightness.light,
      systemNavigationBarColor: p.surface,
      systemNavigationBarIconBrightness:
          isDark ? Brightness.light : Brightness.dark,
    );

    return base.copyWith(
      appBarTheme: AppBarTheme(
        backgroundColor: p.surface,
        foregroundColor: p.textPrimary,
        elevation: 0,
        scrolledUnderElevation: 0,
        centerTitle: false,
        systemOverlayStyle: overlayStyle,
        titleTextStyle: TextStyle(
          fontFamily: fontFamily,
          color: p.textPrimary,
          fontWeight: FontWeight.w800,
          fontSize: 18,
          letterSpacing: -0.2,
        ),
      ),
      inputDecorationTheme: InputDecorationTheme(
        filled: true,
        fillColor: p.surfaceMuted,
        contentPadding: const EdgeInsets.symmetric(
          horizontal: 16,
          vertical: 16,
        ),
        hintStyle: TextStyle(
          fontFamily: fontFamily,
          color: p.textMuted,
          fontWeight: FontWeight.w500,
        ),
        labelStyle: TextStyle(
          fontFamily: fontFamily,
          color: p.textSecondary,
        ),
        border: OutlineInputBorder(
          borderRadius: BorderRadius.circular(14),
          borderSide: BorderSide(color: p.border),
        ),
        enabledBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(16),
          borderSide: BorderSide(color: p.border),
        ),
        focusedBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(16),
          borderSide: const BorderSide(color: primary, width: 1.8),
        ),
      ),
      outlinedButtonTheme: OutlinedButtonThemeData(
        style: OutlinedButton.styleFrom(
          minimumSize: const Size.fromHeight(50),
          side: BorderSide(color: p.borderStrong, width: 1.2),
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(16),
          ),
          textStyle: const TextStyle(
            fontFamily: fontFamily,
            fontSize: 15,
            fontWeight: FontWeight.w700,
            letterSpacing: 0.1,
          ),
        ),
      ),
      elevatedButtonTheme: ElevatedButtonThemeData(
        style: ElevatedButton.styleFrom(
          textStyle: const TextStyle(
            fontFamily: fontFamily,
            fontSize: 15,
            fontWeight: FontWeight.w700,
            letterSpacing: 0.1,
          ),
        ),
      ),
      textButtonTheme: TextButtonThemeData(
        style: TextButton.styleFrom(
          textStyle: const TextStyle(
            fontFamily: fontFamily,
            fontSize: 14,
            fontWeight: FontWeight.w700,
          ),
        ),
      ),
      navigationBarTheme: NavigationBarThemeData(
        height: 68,
        backgroundColor: p.surface,
        surfaceTintColor: p.surface,
        indicatorColor: p.primarySoft,
        indicatorShape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(14),
        ),
        iconTheme: WidgetStateProperty.resolveWith(
          (states) => IconThemeData(
            color: states.contains(WidgetState.selected)
                ? (isDark ? primary : primaryDark)
                : p.textSecondary,
            size: states.contains(WidgetState.selected) ? 23 : 21,
          ),
        ),
        labelTextStyle: WidgetStateProperty.resolveWith(
          (states) => TextStyle(
            fontFamily: fontFamily,
            fontSize: 12,
            fontWeight: states.contains(WidgetState.selected)
                ? FontWeight.w800
                : FontWeight.w600,
            color: states.contains(WidgetState.selected)
                ? p.textPrimary
                : p.textSecondary,
          ),
        ),
      ),
      textTheme: base.textTheme.apply(
        fontFamily: fontFamily,
        bodyColor: p.textPrimary,
        displayColor: p.textPrimary,
      ),
      cardTheme: CardThemeData(
        color: p.surface,
        elevation: 0,
        margin: EdgeInsets.zero,
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(22),
          side: BorderSide(color: p.border),
        ),
      ),
      filledButtonTheme: FilledButtonThemeData(
        style: FilledButton.styleFrom(
          backgroundColor: primary,
          foregroundColor: Colors.white,
          minimumSize: const Size.fromHeight(52),
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(16),
          ),
          textStyle: const TextStyle(
            fontFamily: fontFamily,
            fontSize: 15.5,
            fontWeight: FontWeight.w800,
            letterSpacing: 0.2,
          ),
          elevation: 0,
        ),
      ),
      snackBarTheme: SnackBarThemeData(
        behavior: SnackBarBehavior.floating,
        backgroundColor: isDark ? p.surfaceMuted : p.textPrimary,
        contentTextStyle: TextStyle(
          color: isDark ? p.textPrimary : Colors.white,
          fontWeight: FontWeight.w600,
          fontFamily: fontFamily,
        ),
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
      ),
      dividerTheme: DividerThemeData(
        color: p.border,
        thickness: 1,
        space: 1,
      ),
    );
  }
}

/// Immutable bundle of the neutral tokens for one brightness.
class _Palette {
  const _Palette({
    required this.background,
    required this.backgroundAlt,
    required this.surface,
    required this.surfaceMuted,
    required this.border,
    required this.borderStrong,
    required this.primarySoft,
    required this.accentSoft,
    required this.textPrimary,
    required this.textSecondary,
    required this.textMuted,
    required this.shadowSoft,
    required this.shadowMedium,
  });

  final Color background;
  final Color backgroundAlt;
  final Color surface;
  final Color surfaceMuted;
  final Color border;
  final Color borderStrong;
  final Color primarySoft;
  final Color accentSoft;
  final Color textPrimary;
  final Color textSecondary;
  final Color textMuted;
  final List<BoxShadow> shadowSoft;
  final List<BoxShadow> shadowMedium;
}
