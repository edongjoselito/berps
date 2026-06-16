import 'package:flutter/material.dart';
import '../utils/haptics.dart';

/// An [InkWell] that automatically triggers a light haptic on tap.
class HapticInkWell extends StatelessWidget {
  const HapticInkWell({
    super.key,
    required this.child,
    this.onTap,
    this.onDoubleTap,
    this.onLongPress,
    this.borderRadius,
    this.customBorder,
    this.splashColor,
    this.highlightColor,
    this.haptic = Haptics.light,
  });

  final Widget child;
  final VoidCallback? onTap;
  final VoidCallback? onDoubleTap;
  final VoidCallback? onLongPress;
  final BorderRadius? borderRadius;
  final ShapeBorder? customBorder;
  final Color? splashColor;
  final Color? highlightColor;
  final void Function() haptic;

  @override
  Widget build(BuildContext context) {
    return InkWell(
      onTap: onTap == null
          ? null
          : () {
              haptic();
              onTap!();
            },
      onDoubleTap: onDoubleTap == null
          ? null
          : () {
              haptic();
              onDoubleTap!();
            },
      onLongPress: onLongPress == null
          ? null
          : () {
              haptic();
              onLongPress!();
            },
      borderRadius: borderRadius,
      customBorder: customBorder,
      splashColor: splashColor,
      highlightColor: highlightColor,
      child: child,
    );
  }
}

/// A wrapper that adds haptic feedback to any button callback.
class HapticButton extends StatelessWidget {
  const HapticButton({
    super.key,
    required this.child,
    required this.onPressed,
    this.haptic = Haptics.medium,
  });

  final Widget child;
  final VoidCallback? onPressed;
  final void Function() haptic;

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: onPressed == null
          ? null
          : () {
              haptic();
              onPressed!();
            },
      behavior: HitTestBehavior.translucent,
      child: child,
    );
  }
}

/// A [ListTile] that triggers a light haptic before calling [onTap].
class HapticListTile extends StatelessWidget {
  const HapticListTile({
    super.key,
    this.leading,
    required this.title,
    this.subtitle,
    this.trailing,
    this.onTap,
    this.onLongPress,
    this.dense,
    this.contentPadding,
    this.shape,
    this.tileColor,
  });

  final Widget? leading;
  final Widget title;
  final Widget? subtitle;
  final Widget? trailing;
  final VoidCallback? onTap;
  final VoidCallback? onLongPress;
  final bool? dense;
  final EdgeInsetsGeometry? contentPadding;
  final ShapeBorder? shape;
  final Color? tileColor;

  @override
  Widget build(BuildContext context) {
    return ListTile(
      leading: leading,
      title: title,
      subtitle: subtitle,
      trailing: trailing,
      onTap: onTap == null
          ? null
          : () {
              Haptics.light();
              onTap!();
            },
      onLongPress: onLongPress == null
          ? null
          : () {
              Haptics.light();
              onLongPress!();
            },
      dense: dense,
      contentPadding: contentPadding,
      shape: shape,
      tileColor: tileColor,
    );
  }
}
