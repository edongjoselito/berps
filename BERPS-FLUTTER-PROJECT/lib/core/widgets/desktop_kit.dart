import 'package:flutter/foundation.dart';
import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:lucide_icons_flutter/lucide_icons.dart';

import '../theme/app_theme.dart';

/// Drop-in replacement for [showModalBottomSheet]. On mobile it is a bottom
/// sheet; on desktop the same content opens as a centered dialog, which is
/// the native pattern for forms and pickers on a large screen.
Future<T?> showAppSheet<T>({
  required BuildContext context,
  required WidgetBuilder builder,
  bool isScrollControlled = false,
  Color? backgroundColor,
  ShapeBorder? shape,
  double maxWidth = 560,
}) {
  if (!AppTheme.isDesktop) {
    return showModalBottomSheet<T>(
      context: context,
      builder: builder,
      isScrollControlled: isScrollControlled,
      backgroundColor: backgroundColor,
      shape: shape,
    );
  }
  return showDialog<T>(
    context: context,
    barrierColor: const Color(0x590B1526),
    builder: (dialogContext) {
      final size = MediaQuery.sizeOf(dialogContext);
      return Dialog(
        backgroundColor: Colors.transparent,
        elevation: 0,
        insetPadding: const EdgeInsets.all(32),
        child: ConstrainedBox(
          constraints: BoxConstraints(
            maxWidth: maxWidth,
            maxHeight: size.height * 0.86,
          ),
          child: Container(
            decoration: BoxDecoration(
              borderRadius: BorderRadius.circular(18),
              // macOS sheet shadow — wide, soft, no tint.
              boxShadow: const [
                BoxShadow(
                  color: Color(0x330B1526),
                  blurRadius: 48,
                  offset: Offset(0, 18),
                ),
              ],
            ),
            child: ClipRRect(
              borderRadius: BorderRadius.circular(18),
              child: Material(
                color: Colors.white,
                // Focused text fields swallow Escape on macOS; close
                // explicitly.
                child: CallbackShortcuts(
                  bindings: {
                    const SingleActivator(LogicalKeyboardKey.escape): () =>
                        Navigator.of(dialogContext).maybePop(),
                  },
                  child: MediaQuery.removeViewInsets(
                    context: dialogContext,
                    removeBottom: true,
                    child: builder(dialogContext),
                  ),
                ),
              ),
            ),
          ),
        ),
      );
    },
  );
}

/// Title row for desktop modal content — bold title + optional actions and a
/// squircle close button, separated from the body by a hairline.
class DeskModalHeader extends StatelessWidget {
  const DeskModalHeader({
    super.key,
    required this.title,
    this.subtitle,
    this.actions,
  });

  final String title;
  final String? subtitle;
  final List<Widget>? actions;

  @override
  Widget build(BuildContext context) {
    return Column(
      mainAxisSize: MainAxisSize.min,
      crossAxisAlignment: CrossAxisAlignment.stretch,
      children: [
        Padding(
          padding: const EdgeInsets.fromLTRB(22, 16, 14, 14),
          child: Row(
            children: [
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      title,
                      style: const TextStyle(
                        fontSize: 15,
                        fontWeight: FontWeight.w800,
                        color: AppTheme.textPrimary,
                        letterSpacing: -0.2,
                      ),
                    ),
                    if (subtitle != null)
                      Text(
                        subtitle!,
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis,
                        style: const TextStyle(
                          fontSize: 12,
                          color: AppTheme.textSecondary,
                        ),
                      ),
                  ],
                ),
              ),
              if (actions != null) ...[...actions!, const SizedBox(width: 6)],
              DeskIconButton(
                icon: LucideIcons.x,
                tooltip: 'Close',
                onTap: () => Navigator.of(context).maybePop(),
              ),
            ],
          ),
        ),
        const Divider(height: 1, color: AppTheme.border),
      ],
    );
  }
}

/// Bottom action bar for desktop modal content — right-aligned buttons on a
/// hairline-separated strip.
class DeskModalFooter extends StatelessWidget {
  const DeskModalFooter({super.key, required this.children});

  final List<Widget> children;

  @override
  Widget build(BuildContext context) {
    return Column(
      mainAxisSize: MainAxisSize.min,
      crossAxisAlignment: CrossAxisAlignment.stretch,
      children: [
        const Divider(height: 1, color: AppTheme.border),
        Padding(
          padding: const EdgeInsets.fromLTRB(18, 12, 18, 14),
          child: Row(
            mainAxisAlignment: MainAxisAlignment.end,
            children: children,
          ),
        ),
      ],
    );
  }
}

/// Marks a page as a top-level sidebar destination. Headers inside it drop
/// their back button on desktop, since the sidebar is the navigation.
class DeskRootScope extends InheritedWidget {
  const DeskRootScope({super.key, required super.child});

  static bool isRoot(BuildContext context) =>
      context.dependOnInheritedWidgetOfExactType<DeskRootScope>() != null;

  @override
  bool updateShouldNotify(DeskRootScope oldWidget) => false;
}

/// Desktop confirmation dialog: left-aligned title and message, actions on
/// the right. Falls back to the same layout on mobile.
Future<bool> showDeskConfirm({
  required BuildContext context,
  required String title,
  required String message,
  required String confirmLabel,
  bool danger = false,
}) async {
  final result = await showDialog<bool>(
    context: context,
    barrierColor: const Color(0x660B1526),
    builder: (dialogContext) => Dialog(
      insetPadding: const EdgeInsets.all(32),
      child: ConstrainedBox(
        constraints: const BoxConstraints(maxWidth: 420),
        child: Padding(
          padding: const EdgeInsets.fromLTRB(24, 22, 20, 18),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(
                title,
                style: const TextStyle(
                  fontSize: 17,
                  fontWeight: FontWeight.w800,
                  color: AppTheme.textPrimary,
                  letterSpacing: -0.3,
                ),
              ),
              const SizedBox(height: 8),
              Text(
                message,
                style: const TextStyle(
                  fontSize: 13.5,
                  height: 1.5,
                  color: AppTheme.textSecondary,
                ),
              ),
              const SizedBox(height: 22),
              Row(
                mainAxisAlignment: MainAxisAlignment.end,
                children: [
                  DeskButton(
                    label: 'Cancel',
                    primary: false,
                    onTap: () => Navigator.of(dialogContext).pop(false),
                  ),
                  const SizedBox(width: 8),
                  DeskButton(
                    label: confirmLabel,
                    danger: danger,
                    onTap: () => Navigator.of(dialogContext).pop(true),
                  ),
                ],
              ),
            ],
          ),
        ),
      ),
    ),
  );
  return result == true;
}

/// Small keyboard-shortcut hint ("⌘K").
class KeyHint extends StatelessWidget {
  const KeyHint(this.label, {super.key, this.dark = false});

  final String label;
  final bool dark;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
      decoration: BoxDecoration(
        color: dark ? const Color(0x14FFFFFF) : AppTheme.surfaceMuted,
        borderRadius: BorderRadius.circular(5),
        border: Border.all(
          color: dark ? const Color(0x1FFFFFFF) : AppTheme.border,
        ),
      ),
      child: Text(
        label,
        style: TextStyle(
          fontSize: 10.5,
          fontWeight: FontWeight.w700,
          color: dark ? const Color(0x99FFFFFF) : AppTheme.textSecondary,
        ),
      ),
    );
  }
}

/// Platform modifier label for shortcuts: ⌘ on macOS, Ctrl elsewhere.
String get modKeyLabel =>
    defaultTargetPlatform == TargetPlatform.macOS ? '⌘' : 'Ctrl+';

/// Opens [child] as a right-hand slide-over panel (desktop) — used for
/// notifications and other secondary, glanceable surfaces.
Future<T?> showSidePanel<T>({
  required BuildContext context,
  required Widget child,
  double width = 440,
}) {
  return showGeneralDialog<T>(
    context: context,
    barrierDismissible: true,
    barrierLabel: 'Close',
    barrierColor: const Color(0x400B1526),
    transitionDuration: const Duration(milliseconds: 240),
    pageBuilder: (_, _, _) => Align(
      alignment: Alignment.centerRight,
      child: Material(
        color: Colors.white,
        elevation: 0,
        child: Container(
          width: width,
          height: double.infinity,
          padding: EdgeInsets.only(top: AppTheme.titleBarInset),
          decoration: const BoxDecoration(
            color: AppTheme.background,
            border: Border(left: BorderSide(color: AppTheme.border)),
          ),
          child: child,
        ),
      ),
    ),
    transitionBuilder: (_, animation, _, panel) => SlideTransition(
      position: Tween<Offset>(
        begin: const Offset(1, 0),
        end: Offset.zero,
      ).animate(CurvedAnimation(parent: animation, curve: Curves.easeOutCubic)),
      child: panel,
    ),
  );
}

/// Titled content panel — header row (title, optional count + action), a
/// hairline divider, then the body. The primary building block of desktop
/// layouts.
class DeskPanel extends StatelessWidget {
  const DeskPanel({
    super.key,
    required this.title,
    required this.child,
    this.count,
    this.action,
    this.padding = const EdgeInsets.fromLTRB(18, 4, 18, 16),
  });

  final String title;
  final int? count;
  final Widget? action;
  final Widget child;
  final EdgeInsetsGeometry padding;

  @override
  Widget build(BuildContext context) {
    return Container(
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(14),
        border: Border.all(color: AppTheme.border),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        mainAxisSize: MainAxisSize.min,
        children: [
          Padding(
            padding: const EdgeInsets.fromLTRB(18, 14, 12, 12),
            child: Row(
              children: [
                Text(
                  title,
                  style: const TextStyle(
                    fontSize: 14.5,
                    fontWeight: FontWeight.w800,
                    color: AppTheme.textPrimary,
                    letterSpacing: -0.2,
                  ),
                ),
                if (count != null) ...[
                  const SizedBox(width: 8),
                  Container(
                    padding: const EdgeInsets.symmetric(
                      horizontal: 7,
                      vertical: 2,
                    ),
                    decoration: BoxDecoration(
                      color: AppTheme.surfaceMuted,
                      borderRadius: BorderRadius.circular(999),
                    ),
                    child: Text(
                      '$count',
                      style: const TextStyle(
                        fontSize: 11,
                        fontWeight: FontWeight.w800,
                        color: AppTheme.textSecondary,
                      ),
                    ),
                  ),
                ],
                const Spacer(),
                ?action,
              ],
            ),
          ),
          const Divider(height: 1, color: AppTheme.border),
          Padding(padding: padding, child: child),
        ],
      ),
    );
  }
}

/// Small text action used in panel headers ("View all", "Open").
class DeskLink extends StatelessWidget {
  const DeskLink({super.key, required this.label, required this.onTap});

  final String label;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    return TextButton(
      onPressed: onTap,
      style: TextButton.styleFrom(
        foregroundColor: AppTheme.primaryDark,
        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
        minimumSize: Size.zero,
        tapTargetSize: MaterialTapTargetSize.shrinkWrap,
        textStyle: TextStyle(
          fontFamily: AppTheme.effectiveFontFamily,
          fontSize: 12.5,
          fontWeight: FontWeight.w700,
        ),
      ),
      child: Text(label),
    );
  }
}

/// macOS-style push button — 30px, 8px radius. Primary is a flat accent fill;
/// the neutral variant is a light-gray fill (not an outline, which reads as a
/// stroked pill). Destructive actions use red fill or red text on gray.
class DeskButton extends StatelessWidget {
  const DeskButton({
    super.key,
    required this.label,
    this.icon,
    required this.onTap,
    this.primary = true,
    this.danger = false,
  });

  final String label;
  final IconData? icon;
  final VoidCallback? onTap;
  final bool primary;

  /// Destructive styling — red fill when [primary], red text on the gray
  /// neutral otherwise.
  final bool danger;

  @override
  Widget build(BuildContext context) {
    final enabled = onTap != null;
    final style = TextStyle(
      fontFamily: AppTheme.effectiveFontFamily,
      fontSize: 13,
      fontWeight: FontWeight.w600,
      letterSpacing: -0.1,
    );
    const padding = EdgeInsets.symmetric(horizontal: 12, vertical: 7);
    const radius = 8.0;

    final Color fg;
    final Color bg;
    final Color hover;
    final List<BoxShadow>? shadow;
    if (primary) {
      fg = Colors.white;
      bg = danger ? AppTheme.danger : AppTheme.primary;
      hover = danger ? const Color(0xFFDC2626) : AppTheme.primaryDark;
      shadow = [
        BoxShadow(
          color: (danger ? AppTheme.danger : AppTheme.primary).withValues(
            alpha: 0.22,
          ),
          blurRadius: 6,
          offset: const Offset(0, 1),
        ),
      ];
    } else {
      fg = danger
          ? AppTheme.danger
          : enabled
          ? AppTheme.textPrimary
          : AppTheme.textMuted;
      bg = AppTheme.surfaceMuted;
      hover = const Color(0xFFE3E5EB);
      shadow = null;
    }

    return Material(
      color: Colors.transparent,
      borderRadius: BorderRadius.circular(radius),
      child: InkWell(
        onTap: onTap,
        borderRadius: BorderRadius.circular(radius),
        hoverColor: primary ? Colors.white.withValues(alpha: 0.12) : hover,
        splashFactory: primary ? NoSplash.splashFactory : null,
        child: Ink(
          decoration: BoxDecoration(
            color: bg,
            borderRadius: BorderRadius.circular(radius),
            boxShadow: enabled ? shadow : null,
          ),
          child: Container(
            padding: padding,
            child: Row(
              mainAxisSize: MainAxisSize.min,
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                if (icon != null) ...[
                  Icon(icon, size: 13.5, color: fg),
                  const SizedBox(width: 6),
                ],
                Text(label, style: style.copyWith(color: fg)),
              ],
            ),
          ),
        ),
      ),
    );
  }
}

/// Segmented control for toolbars — compact, inline, hairline bordered.
class DeskSegmented extends StatelessWidget {
  const DeskSegmented({
    super.key,
    required this.options,
    required this.value,
    required this.onChanged,
    this.expanded = false,
  });

  /// value → label
  final Map<String, String> options;
  final String value;
  final ValueChanged<String> onChanged;

  /// When true the control fills its parent's width and every segment gets
  /// an equal share — the right choice inside narrow panes where intrinsic
  /// label widths would otherwise overflow.
  final bool expanded;

  @override
  Widget build(BuildContext context) {
    Widget segment(MapEntry<String, String> entry) {
      final selected = entry.key == value;
      return InkWell(
        onTap: selected ? null : () => onChanged(entry.key),
        borderRadius: BorderRadius.circular(6),
        child: AnimatedContainer(
          duration: const Duration(milliseconds: 140),
          padding: EdgeInsets.symmetric(
            horizontal: expanded ? 4 : 13,
            vertical: 5,
          ),
          decoration: BoxDecoration(
            color: selected ? Colors.white : null,
            borderRadius: BorderRadius.circular(6),
            boxShadow: selected
                ? const [
                    BoxShadow(
                      color: Color(0x1A0B1526),
                      blurRadius: 3,
                      offset: Offset(0, 1),
                    ),
                  ]
                : null,
          ),
          child: Text(
            entry.value,
            textAlign: TextAlign.center,
            maxLines: 1,
            overflow: TextOverflow.ellipsis,
            style: TextStyle(
              fontSize: expanded ? 11.5 : 12,
              fontWeight: FontWeight.w600,
              color: selected ? AppTheme.textPrimary : AppTheme.textSecondary,
            ),
          ),
        ),
      );
    }

    return Container(
      padding: const EdgeInsets.all(2),
      decoration: BoxDecoration(
        color: const Color(0xFFE8EAF0),
        borderRadius: BorderRadius.circular(8),
      ),
      child: Row(
        mainAxisSize: expanded ? MainAxisSize.max : MainAxisSize.min,
        children: [
          for (final entry in options.entries)
            if (expanded) Expanded(child: segment(entry)) else segment(entry),
        ],
      ),
    );
  }
}

/// Desktop-styled stat card — flat white surface, hairline border, tinted
/// icon tile. Replaces the mobile gradient tiles at wide widths.
class DeskStatCard extends StatelessWidget {
  const DeskStatCard({
    super.key,
    required this.icon,
    required this.label,
    required this.value,
    required this.accent,
    this.selected = false,
    this.onTap,
  });

  final IconData icon;
  final String label;
  final String value;
  final Color accent;
  final bool selected;
  final VoidCallback? onTap;

  @override
  Widget build(BuildContext context) {
    return Material(
      color: selected ? accent.withValues(alpha: 0.06) : Colors.white,
      borderRadius: BorderRadius.circular(14),
      child: InkWell(
        onTap: onTap,
        borderRadius: BorderRadius.circular(14),
        hoverColor: accent.withValues(alpha: 0.05),
        child: AnimatedContainer(
          duration: const Duration(milliseconds: 180),
          padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 15),
          decoration: BoxDecoration(
            borderRadius: BorderRadius.circular(14),
            border: Border.all(
              color: selected ? accent.withValues(alpha: 0.5) : AppTheme.border,
              width: selected ? 1.6 : 1,
            ),
          ),
          child: Row(
            children: [
              Container(
                width: 38,
                height: 38,
                decoration: BoxDecoration(
                  color: accent.withValues(alpha: 0.10),
                  borderRadius: BorderRadius.circular(11),
                ),
                child: Icon(icon, size: 18, color: accent),
              ),
              const SizedBox(width: 13),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      value,
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                      style: const TextStyle(
                        fontSize: 20,
                        fontWeight: FontWeight.w900,
                        color: AppTheme.textPrimary,
                        letterSpacing: -0.4,
                        height: 1.1,
                      ),
                    ),
                    const SizedBox(height: 3),
                    Text(
                      label,
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                      style: const TextStyle(
                        fontSize: 12.5,
                        fontWeight: FontWeight.w700,
                        color: AppTheme.textSecondary,
                      ),
                    ),
                  ],
                ),
              ),
              if (selected)
                Icon(LucideIcons.circleCheck, size: 17, color: accent),
            ],
          ),
        ),
      ),
    );
  }
}

/// Squircle toolbar button — 34px rounded square. The default variant is a
/// neutral surface that softens on hover; `filled` is the flat accent used
/// for the leading "New …" action in toolbars.
class DeskIconButton extends StatelessWidget {
  const DeskIconButton({
    super.key,
    required this.icon,
    required this.onTap,
    this.tooltip,
    this.filled = false,
  });

  final IconData icon;
  final VoidCallback? onTap;
  final String? tooltip;
  final bool filled;

  @override
  Widget build(BuildContext context) {
    final enabled = onTap != null;
    final fg = filled
        ? Colors.white
        : enabled
        ? AppTheme.textPrimary
        : AppTheme.textMuted;
    Widget button = InkWell(
      onTap: onTap,
      borderRadius: BorderRadius.circular(8),
      hoverColor: filled
          ? Colors.white.withValues(alpha: 0.12)
          : const Color(0xFFE3E5EB),
      splashFactory: filled ? NoSplash.splashFactory : null,
      child: Ink(
        width: 34,
        height: 34,
        decoration: BoxDecoration(
          borderRadius: BorderRadius.circular(8),
          color: filled ? AppTheme.primary : AppTheme.surfaceMuted,
        ),
        child: Icon(icon, size: 16, color: fg),
      ),
    );
    if (tooltip != null) {
      button = Tooltip(message: tooltip!, child: button);
    }
    return Material(
      color: Colors.transparent,
      borderRadius: BorderRadius.circular(8),
      child: button,
    );
  }
}

/// Shared empty state — a centered icon tile, title and optional message
/// inside the platform-appropriate surface (flat 14px card on desktop,
/// rounded card on mobile).
class AppEmptyState extends StatelessWidget {
  const AppEmptyState({
    super.key,
    required this.icon,
    required this.title,
    this.message,
    this.accent = AppTheme.primaryDark,
  });

  final IconData icon;
  final String title;
  final String? message;
  final Color accent;

  @override
  Widget build(BuildContext context) {
    final desktop = AppTheme.isDesktop;
    final card = Container(
      width: double.infinity,
      padding: EdgeInsets.symmetric(
        horizontal: 24,
        vertical: desktop ? 36 : 30,
      ),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(desktop ? 14 : 20),
        border: Border.all(color: AppTheme.border),
        boxShadow: desktop ? null : AppTheme.shadowSoft,
      ),
      child: Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          Container(
            width: desktop ? 48 : 56,
            height: desktop ? 48 : 56,
            decoration: BoxDecoration(
              color: accent.withValues(alpha: 0.08),
              borderRadius: BorderRadius.circular(desktop ? 14 : 18),
            ),
            child: Icon(icon, color: accent, size: desktop ? 22 : 26),
          ),
          const SizedBox(height: 14),
          Text(
            title,
            textAlign: TextAlign.center,
            style: const TextStyle(
              fontWeight: FontWeight.w800,
              fontSize: 15,
              color: AppTheme.textPrimary,
            ),
          ),
          if ((message ?? '').isNotEmpty) ...[
            const SizedBox(height: 6),
            Text(
              message!,
              textAlign: TextAlign.center,
              style: const TextStyle(
                color: AppTheme.textSecondary,
                fontSize: 12.5,
                height: 1.45,
              ),
            ),
          ],
        ],
      ),
    );
    // On desktop a full-bleed empty card reads as a stretched mobile widget —
    // cap it and let the surrounding canvas breathe.
    if (!desktop) return card;
    return Center(
      child: ConstrainedBox(
        constraints: const BoxConstraints(maxWidth: 520),
        child: card,
      ),
    );
  }
}

/// Shared error state — matches [AppEmptyState] visually with a danger icon
/// and a retry action.
class AppErrorCard extends StatelessWidget {
  const AppErrorCard({
    super.key,
    required this.message,
    required this.onRetry,
    this.title = 'Something went wrong',
  });

  final String message;
  final VoidCallback onRetry;
  final String title;

  @override
  Widget build(BuildContext context) {
    final desktop = AppTheme.isDesktop;
    final card = Container(
      width: double.infinity,
      padding: EdgeInsets.all(desktop ? 24 : 22),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(desktop ? 14 : 20),
        border: Border.all(color: AppTheme.border),
        boxShadow: desktop ? null : AppTheme.shadowSoft,
      ),
      child: Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          Container(
            width: desktop ? 48 : 52,
            height: desktop ? 48 : 52,
            decoration: BoxDecoration(
              color: AppTheme.danger.withValues(alpha: 0.10),
              borderRadius: BorderRadius.circular(desktop ? 14 : 16),
            ),
            child: const Icon(
              LucideIcons.circleAlert,
              color: AppTheme.danger,
              size: 24,
            ),
          ),
          const SizedBox(height: 14),
          Text(
            title,
            textAlign: TextAlign.center,
            style: const TextStyle(
              fontWeight: FontWeight.w800,
              fontSize: 15.5,
              color: AppTheme.textPrimary,
            ),
          ),
          const SizedBox(height: 6),
          Text(
            message,
            textAlign: TextAlign.center,
            style: const TextStyle(color: AppTheme.textSecondary, fontSize: 13),
          ),
          const SizedBox(height: 16),
          OutlinedButton.icon(
            onPressed: onRetry,
            icon: const Icon(LucideIcons.rotateCw, size: 16),
            label: const Text('Try again'),
            style: OutlinedButton.styleFrom(
              minimumSize: Size(0, desktop ? 40 : 46),
            ),
          ),
        ],
      ),
    );
    if (!desktop) return card;
    return Center(
      child: ConstrainedBox(
        constraints: const BoxConstraints(maxWidth: 520),
        child: card,
      ),
    );
  }
}

/// Shared chrome for content hosted inside [showAppSheet]. On mobile the
/// sheet gets a top grab handle; on desktop it gets a plain title row with a
/// close button (the dialog frame already supplies the rounded corners).
class AppSheetScaffold extends StatelessWidget {
  const AppSheetScaffold({
    super.key,
    required this.title,
    required this.child,
    this.icon,
    this.onClose,
    this.titleColor = AppTheme.textPrimary,
    this.padding = const EdgeInsets.fromLTRB(20, 14, 20, 18),
  });

  final String title;
  final IconData? icon;
  final Widget child;
  final VoidCallback? onClose;
  final Color titleColor;
  final EdgeInsetsGeometry padding;

  @override
  Widget build(BuildContext context) {
    final desktop = AppTheme.isDesktop;
    return SafeArea(
      top: false,
      child: Padding(
        padding: padding,
        child: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            if (!desktop)
              Center(
                child: Container(
                  width: 40,
                  height: 4,
                  decoration: BoxDecoration(
                    color: AppTheme.border,
                    borderRadius: BorderRadius.circular(99),
                  ),
                ),
              ),
            SizedBox(height: desktop ? 6 : 14),
            Row(
              children: [
                if (icon != null) ...[
                  Icon(icon, size: 17, color: AppTheme.primaryDark),
                  const SizedBox(width: 10),
                ],
                Expanded(
                  child: Text(
                    title,
                    maxLines: 1,
                    overflow: TextOverflow.ellipsis,
                    style: TextStyle(
                      fontSize: desktop ? 15.5 : 16.5,
                      fontWeight: FontWeight.w900,
                      color: titleColor,
                      letterSpacing: -0.2,
                    ),
                  ),
                ),
                if (desktop)
                  IconButton(
                    visualDensity: VisualDensity.compact,
                    onPressed:
                        onClose ?? () => Navigator.of(context).maybePop(),
                    icon: const Icon(
                      LucideIcons.x,
                      size: 16,
                      color: AppTheme.textMuted,
                    ),
                  ),
              ],
            ),
            const SizedBox(height: 14),
            Flexible(child: child),
          ],
        ),
      ),
    );
  }
}

/// Thin status strip pinned to the bottom of the desktop shell — workspace
/// domain, connection state and product version.
class DeskStatusBar extends StatelessWidget {
  const DeskStatusBar({
    super.key,
    required this.domain,
    this.syncLabel,
    this.actions = const [],
  });

  /// Workspace host (e.g. `berps.online`).
  final String domain;

  /// Optional "last sync" text shown after the connection state.
  final String? syncLabel;

  /// Trailing widgets rendered at the right edge.
  final List<Widget> actions;

  @override
  Widget build(BuildContext context) {
    return Container(
      height: AppTheme.statusBarHeight,
      padding: const EdgeInsets.symmetric(horizontal: 14),
      decoration: const BoxDecoration(
        color: Colors.white,
        border: Border(top: BorderSide(color: AppTheme.border)),
      ),
      child: Row(
        children: [
          Container(
            width: 7,
            height: 7,
            decoration: const BoxDecoration(
              color: AppTheme.success,
              shape: BoxShape.circle,
            ),
          ),
          const SizedBox(width: 7),
          Text(
            domain.isEmpty ? 'Connected' : 'Connected · $domain',
            style: const TextStyle(
              fontSize: 11.5,
              fontWeight: FontWeight.w700,
              color: AppTheme.textSecondary,
            ),
          ),
          if (syncLabel != null) ...[
            const SizedBox(width: 14),
            Text(
              syncLabel!,
              style: const TextStyle(
                fontSize: 11.5,
                fontWeight: FontWeight.w600,
                color: AppTheme.textMuted,
              ),
            ),
          ],
          const Spacer(),
          for (final action in actions) action,
          const SizedBox(width: 6),
          Text(
            AppTheme.productLabel,
            style: const TextStyle(
              fontSize: 11.5,
              fontWeight: FontWeight.w600,
              color: AppTheme.textMuted,
            ),
          ),
        ],
      ),
    );
  }
}
