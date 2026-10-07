import 'dart:io' show Platform;

import 'package:flutter/foundation.dart' show kIsWeb;
import 'package:flutter/material.dart';

/// BERPS mobile palette — light sky-blue primary.
/// A friendlier modern blue (Tailwind-style) with cool neutrals
/// and a warm amber accent reserved for status emphasis.
class AppTheme {
  static const Color primary = Color(0xFF3B82F6);
  static const Color primaryDark = Color(0xFF2563EB);
  static const Color primaryDeeper = Color(0xFF1D4ED8);
  static const Color primarySoft = Color(0xFFEFF6FF);
  static const Color background = Color(0xFFF6F8FC);
  static const Color backgroundAlt = Color(0xFFEEF3FA);
  static const Color surface = Colors.white;
  static const Color surfaceMuted = Color(0xFFF1F5FB);
  static const Color border = Color(0xFFE2E8F2);
  static const Color borderStrong = Color(0xFFC9D3E3);
  static const Color textPrimary = Color(0xFF0F1B2D);
  static const Color textSecondary = Color(0xFF64748B);
  static const Color textMuted = Color(0xFF94A3B8);
  static const Color accent = Color(0xFFD9A24B);
  static const Color accentSoft = Color(0xFFFBF3E2);
  static const Color success = Color(0xFF16A34A);
  static const Color warning = Color(0xFFB97A0E);
  static const Color danger = Color(0xFFB91C1C);

  /// Semantic sets — keep feature colors in one place instead of hardcoded
  /// hex values scattered through the screens.
  static const Color reminderAccent = Color(0xFFEA580C);
  static const Color reminderSoft = Color(0xFFFFF7ED);
  static const Color reminderBorder = Color(0xFFFED7AA);
  static const Color reminderDeep = Color(0xFF9A3412);

  static const Color navy = Color(0xFF1E3A5F);
  static const Color navyLight = Color(0xFF2D5A8A);

  static const Color rankGold = Color(0xFFD97706);
  static const Color rankBronze = Color(0xFFB45309);

  static const String fontFamily = 'InstrumentSans';

  /// Product/release branding — single source of truth so footers, the auth
  /// side panel and the status bar never drift apart.
  static const String appVersion = '1.0.2';
  static String get productName => isDesktop ? 'BERPS Desktop' : 'BERPS Mobile';
  static String get productLabel => '$productName · v$appVersion';

  /// Width of the right-hand detail rail used on desktop screens (dashboard,
  /// attendance, etc.) — shared so every rail lines up.
  static const double railWidth = 340;

  /// Desktop sidebar widths — expanded and collapsed-to-icons.
  static const double sidebarWidth = 248;
  static const double sidebarCollapsedWidth = 68;

  /// Height of the status bar strip pinned to the bottom of the desktop shell.
  static const double statusBarHeight = 28;

  /// True on macOS/Windows/Linux builds — used for desktop-only styling
  /// (Sora typeface, sidebar layout, wider density).
  static bool get isDesktop =>
      !kIsWeb && (Platform.isMacOS || Platform.isWindows || Platform.isLinux);

  /// Height reserved for the transparent macOS title bar (traffic lights sit
  /// over app content because the window uses a full-size content view).
  static double get titleBarInset => !kIsWeb && Platform.isMacOS ? 28 : 0;

  /// Font family actually applied by [build] — Sora on desktop.
  static String get effectiveFontFamily => isDesktop ? 'Sora' : fontFamily;

  /// Card chrome: flat 14px corners on desktop, the given rounder radius on
  /// mobile. Use for outer card surfaces (panels, stat cards, list cards) —
  /// inner chips/avatars keep their own radii.
  static double cardRadius([double mobile = 16]) => isDesktop ? 14 : mobile;

  /// Compact-density toggle — desktop tables, rows and the sidebar render
  /// tighter when on. Persisted via SessionStore; widgets that care listen
  /// through ValueListenableBuilder.
  static final ValueNotifier<bool> compactDensity = ValueNotifier(false);

  /// Reusable shadow tokens so cards share an exact elevation language.
  static const List<BoxShadow> shadowSoft = [
    BoxShadow(color: Color(0x0A0F1E3A), blurRadius: 18, offset: Offset(0, 8)),
  ];

  static const List<BoxShadow> shadowMedium = [
    BoxShadow(color: Color(0x140F1E3A), blurRadius: 24, offset: Offset(0, 14)),
  ];

  static ThemeData build() {
    final base = ThemeData(
      useMaterial3: true,
      colorScheme: ColorScheme.fromSeed(
        seedColor: primary,
        primary: primary,
        brightness: Brightness.light,
      ),
      scaffoldBackgroundColor: background,
      fontFamily: effectiveFontFamily,
      splashFactory: InkSparkle.splashFactory,
    );

    return base.copyWith(
      appBarTheme: AppBarTheme(
        backgroundColor: Colors.white,
        foregroundColor: textPrimary,
        elevation: 0,
        scrolledUnderElevation: 0,
        centerTitle: false,
        titleTextStyle: TextStyle(
          fontFamily: effectiveFontFamily,
          color: textPrimary,
          fontWeight: FontWeight.w800,
          fontSize: 18,
          letterSpacing: -0.2,
        ),
      ),
      inputDecorationTheme: InputDecorationTheme(
        filled: true,
        // macOS-style fields read as white wells on the gray canvas; mobile
        // keeps the softer filled-gray look.
        fillColor: isDesktop ? Colors.white : surfaceMuted,
        contentPadding: EdgeInsets.symmetric(
          horizontal: isDesktop ? 12 : 16,
          vertical: isDesktop ? 10 : 16,
        ),
        hintStyle: TextStyle(
          fontFamily: effectiveFontFamily,
          color: textMuted,
          fontWeight: FontWeight.w500,
          fontSize: isDesktop ? 13 : null,
        ),
        labelStyle: TextStyle(
          fontFamily: effectiveFontFamily,
          color: textSecondary,
          fontSize: isDesktop ? 13 : null,
        ),
        border: OutlineInputBorder(
          borderRadius: BorderRadius.circular(isDesktop ? 10 : 14),
          borderSide: const BorderSide(color: border),
        ),
        enabledBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(isDesktop ? 10 : 16),
          borderSide: const BorderSide(color: border),
        ),
        focusedBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(isDesktop ? 10 : 16),
          borderSide: const BorderSide(color: primary, width: 1.6),
        ),
      ),
      outlinedButtonTheme: OutlinedButtonThemeData(
        style: OutlinedButton.styleFrom(
          minimumSize: Size.fromHeight(isDesktop ? 40 : 50),
          side: const BorderSide(color: borderStrong, width: 1.2),
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(isDesktop ? 10 : 16),
          ),
          textStyle: TextStyle(
            fontFamily: effectiveFontFamily,
            fontSize: 15,
            fontWeight: FontWeight.w700,
            letterSpacing: 0.1,
          ),
        ),
      ),
      elevatedButtonTheme: ElevatedButtonThemeData(
        style: ElevatedButton.styleFrom(
          textStyle: TextStyle(
            fontFamily: effectiveFontFamily,
            fontSize: 15,
            fontWeight: FontWeight.w700,
            letterSpacing: 0.1,
          ),
        ),
      ),
      textButtonTheme: TextButtonThemeData(
        style: TextButton.styleFrom(
          textStyle: TextStyle(
            fontFamily: effectiveFontFamily,
            fontSize: 14,
            fontWeight: FontWeight.w700,
          ),
        ),
      ),
      navigationBarTheme: NavigationBarThemeData(
        height: 68,
        backgroundColor: Colors.white,
        surfaceTintColor: Colors.white,
        indicatorColor: primarySoft,
        indicatorShape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(14),
        ),
        iconTheme: WidgetStateProperty.resolveWith(
          (states) => IconThemeData(
            color: states.contains(WidgetState.selected)
                ? primaryDark
                : textSecondary,
            size: states.contains(WidgetState.selected) ? 23 : 21,
          ),
        ),
        labelTextStyle: WidgetStateProperty.resolveWith(
          (states) => TextStyle(
            fontFamily: effectiveFontFamily,
            fontSize: 12,
            fontWeight: states.contains(WidgetState.selected)
                ? FontWeight.w800
                : FontWeight.w600,
            color: states.contains(WidgetState.selected)
                ? textPrimary
                : textSecondary,
          ),
        ),
      ),
      textTheme: base.textTheme.apply(
        fontFamily: effectiveFontFamily,
        bodyColor: textPrimary,
        displayColor: textPrimary,
      ),
      cardTheme: CardThemeData(
        color: surface,
        elevation: 0,
        margin: EdgeInsets.zero,
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(isDesktop ? 14 : 22),
          side: const BorderSide(color: border),
        ),
      ),
      filledButtonTheme: FilledButtonThemeData(
        style: FilledButton.styleFrom(
          backgroundColor: primary,
          minimumSize: Size.fromHeight(isDesktop ? 44 : 52),
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(isDesktop ? 10 : 16),
          ),
          textStyle: TextStyle(
            fontFamily: effectiveFontFamily,
            fontSize: 15.5,
            fontWeight: FontWeight.w800,
            letterSpacing: 0.2,
          ),
          elevation: 0,
        ),
      ),
      snackBarTheme: SnackBarThemeData(
        behavior: SnackBarBehavior.floating,
        backgroundColor: textPrimary,
        contentTextStyle: TextStyle(
          color: Colors.white,
          fontWeight: FontWeight.w600,
          fontFamily: effectiveFontFamily,
        ),
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
      ),
      dialogTheme: DialogThemeData(
        backgroundColor: surface,
        surfaceTintColor: Colors.transparent,
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(isDesktop ? 16 : 24),
        ),
      ),
      tooltipTheme: TooltipThemeData(
        decoration: BoxDecoration(
          color: textPrimary,
          borderRadius: BorderRadius.circular(6),
        ),
        textStyle: TextStyle(
          fontFamily: effectiveFontFamily,
          color: Colors.white,
          fontSize: 12,
          fontWeight: FontWeight.w600,
        ),
        waitDuration: const Duration(milliseconds: 400),
      ),
      dividerTheme: const DividerThemeData(
        color: border,
        thickness: 1,
        space: 1,
      ),
      // Desktop scrollables get a thin, always-visible scrollbar; mobile keeps
      // the default overlay behavior.
      scrollbarTheme: ScrollbarThemeData(
        thumbVisibility: WidgetStateProperty.all(isDesktop),
        thickness: WidgetStateProperty.all(isDesktop ? 8 : 4),
        radius: const Radius.circular(8),
        interactive: isDesktop,
        thumbColor: WidgetStateProperty.resolveWith(
          (states) =>
              states.contains(WidgetState.hovered) ? textMuted : borderStrong,
        ),
      ),
    );
  }
}
