import 'dart:async';

import 'package:flutter/material.dart';
import 'package:phosphor_flutter/phosphor_flutter.dart';

import '../theme/app_theme.dart';
import '../utils/haptics.dart';

/// Toast variants — each has a colour + icon vocabulary.
enum AppToastVariant { success, error, warning, info }

/// A universal in-app toast that slides in from the top of the screen,
/// floats over everything (including the app bar / nav bar), and dismisses
/// itself on tap or after a short timeout.
///
/// Usage:
///   AppToast.success(context, 'Time-in recorded.');
///   AppToast.error(context, e.message);
class AppToast {
  AppToast._();

  static OverlayEntry? _entry;
  static Timer? _timer;

  static void success(BuildContext context, String message) =>
      _show(context, AppToastVariant.success, message);

  static void error(BuildContext context, String message) =>
      _show(context, AppToastVariant.error, message);

  static void warning(BuildContext context, String message) =>
      _show(context, AppToastVariant.warning, message);

  static void info(BuildContext context, String message) =>
      _show(context, AppToastVariant.info, message);

  /// Show with an explicit variant; honoured by the public helpers above.
  static void show(
    BuildContext context, {
    required AppToastVariant variant,
    required String message,
    Duration duration = const Duration(milliseconds: 2800),
  }) {
    _show(context, variant, message, duration: duration);
  }

  static void dismiss() {
    _timer?.cancel();
    _timer = null;
    _entry?.remove();
    _entry = null;
  }

  static void _show(
    BuildContext context,
    AppToastVariant variant,
    String message, {
    Duration duration = const Duration(milliseconds: 2800),
  }) {
    final overlay = Overlay.maybeOf(context, rootOverlay: true);
    if (overlay == null) return;

    dismiss();

    switch (variant) {
      case AppToastVariant.success:
        Haptics.success();
        break;
      case AppToastVariant.error:
      case AppToastVariant.warning:
        Haptics.warn();
        break;
      case AppToastVariant.info:
        Haptics.light();
        break;
    }

    final entry = OverlayEntry(
      builder: (context) => _ToastWidget(
        message: message,
        variant: variant,
        duration: duration,
        onDismissed: dismiss,
      ),
    );
    _entry = entry;
    overlay.insert(entry);
  }
}

class _ToastWidget extends StatefulWidget {
  const _ToastWidget({
    required this.message,
    required this.variant,
    required this.duration,
    required this.onDismissed,
  });

  final String message;
  final AppToastVariant variant;
  final Duration duration;
  final VoidCallback onDismissed;

  @override
  State<_ToastWidget> createState() => _ToastWidgetState();
}

class _ToastWidgetState extends State<_ToastWidget>
    with SingleTickerProviderStateMixin {
  late final AnimationController _ctrl = AnimationController(
    vsync: this,
    duration: const Duration(milliseconds: 280),
  );

  Timer? _autoHide;

  @override
  void initState() {
    super.initState();
    _ctrl.forward();
    _autoHide = Timer(widget.duration, _dismiss);
  }

  Future<void> _dismiss() async {
    _autoHide?.cancel();
    if (!mounted) return;
    await _ctrl.reverse();
    if (!mounted) return;
    widget.onDismissed();
  }

  @override
  void dispose() {
    _autoHide?.cancel();
    _ctrl.dispose();
    super.dispose();
  }

  _ToastStyle get _style {
    switch (widget.variant) {
      case AppToastVariant.success:
        return const _ToastStyle(
          accent: AppTheme.success,
          icon: PhosphorIconsFill.checkCircle,
          fallbackTitle: 'Success',
        );
      case AppToastVariant.error:
        return const _ToastStyle(
          accent: AppTheme.danger,
          icon: PhosphorIconsFill.xCircle,
          fallbackTitle: 'Error',
        );
      case AppToastVariant.warning:
        return const _ToastStyle(
          accent: AppTheme.warning,
          icon: PhosphorIconsFill.warningCircle,
          fallbackTitle: 'Heads up',
        );
      case AppToastVariant.info:
        return _ToastStyle(
          accent: AppTheme.primaryDark,
          icon: PhosphorIconsFill.info,
          fallbackTitle: 'Info',
        );
    }
  }

  @override
  Widget build(BuildContext context) {
    final media = MediaQuery.of(context);
    final style = _style;

    return Positioned(
      top: media.padding.top + 10,
      left: 16,
      right: 16,
      child: SafeArea(
        bottom: false,
        child: AnimatedBuilder(
          animation: _ctrl,
          builder: (context, child) {
            final value = Curves.easeOutCubic.transform(_ctrl.value);
            return Opacity(
              opacity: value,
              child: Transform.translate(
                offset: Offset(0, -24 * (1 - value)),
                child: child,
              ),
            );
          },
          child: Material(
            color: Colors.transparent,
            child: GestureDetector(
              behavior: HitTestBehavior.opaque,
              onTap: _dismiss,
              child: Container(
                padding: const EdgeInsets.fromLTRB(12, 12, 12, 12),
                decoration: BoxDecoration(
                  color: AppTheme.surface,
                  borderRadius: BorderRadius.circular(16),
                  border: Border.all(
                    color: style.accent.withValues(alpha: 0.18),
                  ),
                  boxShadow: [
                    BoxShadow(
                      color: style.accent.withValues(alpha: 0.18),
                      blurRadius: 28,
                      offset: const Offset(0, 14),
                    ),
                    const BoxShadow(
                      color: Color(0x140F1E3A),
                      blurRadius: 30,
                      offset: Offset(0, 16),
                    ),
                  ],
                ),
                child: Row(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Container(
                      width: 38,
                      height: 38,
                      decoration: BoxDecoration(
                        color: style.accent.withValues(alpha: 0.12),
                        borderRadius: BorderRadius.circular(12),
                      ),
                      child: Icon(style.icon, color: style.accent, size: 20),
                    ),
                    const SizedBox(width: 12),
                    Expanded(
                      child: Padding(
                        padding: const EdgeInsets.only(top: 2),
                        child: Text(
                          widget.message,
                          maxLines: 4,
                          overflow: TextOverflow.ellipsis,
                          style: TextStyle(
                            color: AppTheme.textPrimary,
                            fontWeight: FontWeight.w700,
                            fontSize: 13.5,
                            height: 1.35,
                          ),
                        ),
                      ),
                    ),
                    const SizedBox(width: 8),
                    InkWell(
                      onTap: _dismiss,
                      borderRadius: BorderRadius.circular(8),
                      child: Padding(
                        padding: EdgeInsets.all(4),
                        child: Icon(
                          PhosphorIconsBold.x,
                          size: 14,
                          color: AppTheme.textMuted,
                        ),
                      ),
                    ),
                  ],
                ),
              ),
            ),
          ),
        ),
      ),
    );
  }
}

class _ToastStyle {
  const _ToastStyle({
    required this.accent,
    required this.icon,
    required this.fallbackTitle,
  });

  final Color accent;
  final IconData icon;
  final String fallbackTitle;
}
