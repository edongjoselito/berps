import 'package:flutter/material.dart';
import 'package:lucide_icons_flutter/lucide_icons.dart';

import '../theme/app_theme.dart';

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
              color: selected
                  ? accent.withValues(alpha: 0.5)
                  : AppTheme.border,
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
                Icon(
                  LucideIcons.circleCheck,
                  size: 17,
                  color: accent,
                )
              else if (onTap != null)
                const Icon(
                  LucideIcons.chevronRight,
                  size: 14,
                  color: AppTheme.borderStrong,
                ),
            ],
          ),
        ),
      ),
    );
  }
}
