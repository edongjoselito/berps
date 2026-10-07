import 'package:flutter/material.dart';
import '../theme/app_theme.dart';
import 'animations.dart';
import 'desktop_kit.dart';

class MobileHeader extends StatelessWidget {
  const MobileHeader({
    super.key,
    required this.title,
    this.leadingIcon,
    this.onLeadingTap,
    this.subtitle,
    this.trailingIcon,
    this.onTrailingTap,
    this.trailing,
  });

  final String title;
  final String? subtitle;
  final IconData? leadingIcon;
  final VoidCallback? onLeadingTap;
  final IconData? trailingIcon;
  final VoidCallback? onTrailingTap;
  final Widget? trailing;

  @override
  Widget build(BuildContext context) {
    final trailingWidgets = <Widget>[];
    if (trailing != null) {
      trailingWidgets.add(trailing!);
    }
    if (trailing == null && trailingIcon != null && onTrailingTap != null) {
      trailingWidgets.add(
        MobileHeaderButton(
          icon: trailingIcon!,
          onTap: onTrailingTap!,
          filled: true,
        ),
      );
    }

    // Sidebar destinations on desktop don't need a back button.
    final showLeading =
        leadingIcon != null &&
        onLeadingTap != null &&
        !(AppTheme.isDesktop && DeskRootScope.isRoot(context));
    final row = Row(
      children: [
        if (showLeading) ...[
          MobileHeaderButton(icon: leadingIcon!, onTap: onLeadingTap!),
          const SizedBox(width: 12),
        ],
        Expanded(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(
                title,
                maxLines: 1,
                overflow: TextOverflow.ellipsis,
                style: TextStyle(
                  fontSize: 22,
                  fontWeight: AppTheme.isDesktop
                      ? FontWeight.w800
                      : FontWeight.w900,
                  color: AppTheme.textPrimary,
                  letterSpacing: AppTheme.isDesktop ? -0.7 : -0.5,
                ),
              ),
              if (subtitle?.trim().isNotEmpty == true) ...[
                SizedBox(height: AppTheme.isDesktop ? 3 : 2),
                Text(
                  subtitle!,
                  maxLines: 1,
                  overflow: TextOverflow.ellipsis,
                  style: TextStyle(
                    fontSize: AppTheme.isDesktop ? 13 : 12.5,
                    fontWeight: FontWeight.w600,
                    color: AppTheme.textSecondary,
                  ),
                ),
              ],
            ],
          ),
        ),
        for (var i = 0; i < trailingWidgets.length; i++) ...[
          if (i > 0) const SizedBox(width: 8),
          trailingWidgets[i],
        ],
      ],
    );
    if (!AppTheme.isDesktop) return row;
    // Desktop toolbar: a little extra vertical room so the page title reads
    // as a window toolbar rather than a mobile app bar.
    return Padding(
      padding: const EdgeInsets.only(top: 10, bottom: 6),
      child: row,
    );
  }
}

class MobileHeaderButton extends StatelessWidget {
  const MobileHeaderButton({
    super.key,
    required this.icon,
    required this.onTap,
    this.filled = false,
  });

  final IconData icon;
  final VoidCallback onTap;
  final bool filled;

  @override
  Widget build(BuildContext context) {
    if (AppTheme.isDesktop) {
      return Material(
        color: Colors.transparent,
        borderRadius: BorderRadius.circular(8),
        child: InkWell(
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
              color: filled ? AppTheme.primary : AppTheme.surfaceMuted,
              borderRadius: BorderRadius.circular(8),
            ),
            child: Icon(
              icon,
              size: 16,
              color: filled ? Colors.white : AppTheme.textPrimary,
            ),
          ),
        ),
      );
    }
    return PressScale(
      onTap: onTap,
      child: Container(
        width: 44,
        height: 44,
        decoration: BoxDecoration(
          color: filled ? AppTheme.primaryDark : Colors.white,
          borderRadius: BorderRadius.circular(14),
          border: Border.all(
            color: filled ? AppTheme.primaryDark : AppTheme.border,
          ),
          boxShadow: AppTheme.isDesktop ? null : AppTheme.shadowSoft,
        ),
        child: Icon(
          icon,
          size: 20,
          color: filled ? Colors.white : AppTheme.textPrimary,
        ),
      ),
    );
  }
}

class MobileInlineBadge extends StatelessWidget {
  const MobileInlineBadge({
    super.key,
    required this.icon,
    required this.label,
    this.accent = AppTheme.primaryDark,
  });

  final IconData icon;
  final String label;
  final Color accent;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
      decoration: BoxDecoration(
        color: accent.withValues(alpha: 0.08),
        borderRadius: BorderRadius.circular(999),
        border: Border.all(color: accent.withValues(alpha: 0.16)),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Icon(icon, size: 12, color: accent),
          const SizedBox(width: 6),
          Text(
            label,
            style: TextStyle(
              fontSize: 11.5,
              fontWeight: FontWeight.w800,
              color: accent,
            ),
          ),
        ],
      ),
    );
  }
}

class MobileSurfaceCard extends StatelessWidget {
  const MobileSurfaceCard({
    super.key,
    required this.child,
    this.padding = const EdgeInsets.all(18),
  });

  final Widget child;
  final EdgeInsetsGeometry padding;

  @override
  Widget build(BuildContext context) {
    if (AppTheme.isDesktop) {
      // Flat desktop surface — hairline border, tight radius, no drop shadow.
      return Container(
        padding: padding,
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(14),
          border: Border.all(color: AppTheme.border),
        ),
        child: child,
      );
    }
    return Container(
      padding: padding,
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(22),
        border: Border.all(color: AppTheme.border.withValues(alpha: 0.7)),
        boxShadow: [
          BoxShadow(
            color: AppTheme.primary.withValues(alpha: 0.04),
            blurRadius: 20,
            offset: const Offset(0, 6),
          ),
          BoxShadow(
            color: AppTheme.textPrimary.withValues(alpha: 0.03),
            blurRadius: 40,
            offset: const Offset(0, 16),
          ),
        ],
      ),
      child: child,
    );
  }
}

class MobileSectionLabel extends StatelessWidget {
  const MobileSectionLabel(this.text, {super.key});
  final String text;

  @override
  Widget build(BuildContext context) {
    return Text(
      text.toUpperCase(),
      style: const TextStyle(
        fontSize: 11,
        fontWeight: FontWeight.w900,
        letterSpacing: 1.5,
        color: AppTheme.textMuted,
      ),
    );
  }
}
