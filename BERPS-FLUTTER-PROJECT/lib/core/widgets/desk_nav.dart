import 'package:flutter/material.dart';
import 'package:lucide_icons_flutter/lucide_icons.dart';

import '../theme/app_theme.dart';
import '../utils/haptics.dart';

/// Colors of the dark navy desktop sidebar, shared by the staff and admin
/// shells so both read as the same product.
class DeskNavColors {
  static const background = Color(0xFF0E1A2D);
  static const divider = Color(0x14FFFFFF);
  static const textDim = Color(0x8CFFFFFF);
  static const textFaint = Color(0x59FFFFFF);
  static const activeFill = Color(0x1AFFFFFF);
  static const hoverFill = Color(0x0DFFFFFF);
  static const badge = Color(0xFF4C8DFF);
}

/// One destination in the sidebar.
class DeskNavItemSpec {
  const DeskNavItemSpec({
    required this.id,
    required this.icon,
    required this.label,
    this.badge = 0,
  });

  final String id;
  final IconData icon;
  final String label;

  /// Unread/pending count — shows a pill when > 0.
  final int badge;
}

/// A labelled group of sidebar destinations.
class DeskNavSectionSpec {
  const DeskNavSectionSpec({required this.label, required this.items});

  final String label;
  final List<DeskNavItemSpec> items;
}

/// The docked desktop navigation rail: brand header, optional search field,
/// sectioned items with badges, a collapse-to-icons toggle and a footer slot
/// (usually the signed-in user card).
class DeskSideNav extends StatelessWidget {
  const DeskSideNav({
    super.key,
    required this.logo,
    required this.title,
    required this.sections,
    required this.activeId,
    required this.onSelect,
    this.collapsed = false,
    this.onToggleCollapse,
    this.search,
    this.bottomContent,
    this.footerBuilder,
  });

  /// Brand mark shown at the top (kept visible when collapsed).
  final Widget logo;
  final String title;

  final List<DeskNavSectionSpec> sections;
  final String activeId;
  final ValueChanged<String> onSelect;

  /// Icon-only rail (~68px) when true.
  final bool collapsed;
  final VoidCallback? onToggleCollapse;

  /// Search slot — hidden when collapsed.
  final Widget? search;

  /// Optional block pinned between the nav items and the footer (e.g. a
  /// mini calendar). Hidden when collapsed.
  final Widget? bottomContent;

  /// Footer slot (user card, sign-out…). Receives [collapsed] so callers can
  /// shrink it to an icon row.
  final Widget Function(bool collapsed)? footerBuilder;

  @override
  Widget build(BuildContext context) {
    return ValueListenableBuilder<bool>(
      valueListenable: AppTheme.compactDensity,
      builder: (context, compact, _) => _build(compact),
    );
  }

  Widget _build(bool compact) {
    final width = collapsed
        ? AppTheme.sidebarCollapsedWidth
        : AppTheme.sidebarWidth;
    // Midpoint between the two widths — the compact layout is used while the
    // container is still animating through anything narrower, so expanded
    // rows never paint into a too-thin rail.
    final switchPoint =
        (AppTheme.sidebarWidth + AppTheme.sidebarCollapsedWidth) / 2;

    return AnimatedContainer(
      duration: const Duration(milliseconds: 200),
      curve: Curves.easeOutCubic,
      width: width,
      clipBehavior: Clip.hardEdge,
      color: DeskNavColors.background,
      child: SafeArea(
        child: LayoutBuilder(
          builder: (context, box) {
            final rail = collapsed || box.maxWidth < switchPoint;
            final horizontal = rail ? 12.0 : 20.0;
            return Column(
              crossAxisAlignment: CrossAxisAlignment.stretch,
              children: [
                Padding(
                  padding: EdgeInsets.fromLTRB(
                    rail ? 0 : horizontal,
                    AppTheme.titleBarInset > 0
                        ? AppTheme.titleBarInset + 12
                        : 22,
                    rail ? 0 : (onToggleCollapse != null ? 8 : 20),
                    14,
                  ),
                  child: rail
                      ? Center(child: logo)
                      : Row(
                          children: [
                            logo,
                            const SizedBox(width: 11),
                            Expanded(
                              child: Text(
                                title,
                                maxLines: 1,
                                overflow: TextOverflow.ellipsis,
                                style: const TextStyle(
                                  color: Colors.white,
                                  fontSize: 16,
                                  fontWeight: FontWeight.w800,
                                  letterSpacing: 1.4,
                                ),
                              ),
                            ),
                            if (onToggleCollapse != null)
                              _NavGhostButton(
                                icon: LucideIcons.chevronsLeft,
                                tooltip: 'Collapse sidebar',
                                onTap: onToggleCollapse!,
                              ),
                          ],
                        ),
                ),
                if (rail && onToggleCollapse != null)
                  Center(
                    child: _NavGhostButton(
                      icon: LucideIcons.chevronsRight,
                      tooltip: 'Expand sidebar',
                      onTap: onToggleCollapse!,
                    ),
                  ),
                if (!rail && search != null)
                  Padding(
                    padding: const EdgeInsets.fromLTRB(12, 0, 12, 4),
                    child: search,
                  ),
                Expanded(
                  child: ListView(
                    padding: EdgeInsets.fromLTRB(
                      rail ? 10 : 12,
                      0,
                      rail ? 10 : 12,
                      12,
                    ),
                    children: [
                      for (var s = 0; s < sections.length; s++) ...[
                        if (rail && s > 0)
                          const Padding(
                            padding: EdgeInsets.symmetric(vertical: 8),
                            child: Divider(
                              height: 1,
                              color: DeskNavColors.divider,
                            ),
                          ),
                        if (!rail)
                          Padding(
                            padding: const EdgeInsets.fromLTRB(10, 16, 10, 6),
                            child: Text(
                              sections[s].label.toUpperCase(),
                              style: const TextStyle(
                                color: DeskNavColors.textFaint,
                                fontSize: 10.5,
                                fontWeight: FontWeight.w700,
                                letterSpacing: 1.4,
                              ),
                            ),
                          )
                        else
                          const SizedBox(height: 8),
                        for (final item in sections[s].items)
                          _DeskNavItem(
                            spec: item,
                            active: activeId == item.id,
                            collapsed: rail,
                            compact: compact,
                            onSelect: () => onSelect(item.id),
                          ),
                      ],
                    ],
                  ),
                ),
                if (!rail && bottomContent != null) bottomContent!,
                const Divider(height: 1, color: DeskNavColors.divider),
                ?footerBuilder?.call(rail),
              ],
            );
          },
        ),
      ),
    );
  }
}

class _NavGhostButton extends StatelessWidget {
  const _NavGhostButton({
    required this.icon,
    required this.onTap,
    this.tooltip,
  });

  final IconData icon;
  final VoidCallback onTap;
  final String? tooltip;

  @override
  Widget build(BuildContext context) {
    return IconButton(
      tooltip: tooltip,
      onPressed: onTap,
      hoverColor: DeskNavColors.hoverFill,
      visualDensity: VisualDensity.compact,
      icon: Icon(icon, size: 16, color: DeskNavColors.textDim),
    );
  }
}

class _DeskNavItem extends StatelessWidget {
  const _DeskNavItem({
    required this.spec,
    required this.active,
    required this.collapsed,
    required this.compact,
    required this.onSelect,
  });

  final DeskNavItemSpec spec;
  final bool active;
  final bool collapsed;
  final bool compact;
  final VoidCallback onSelect;

  @override
  Widget build(BuildContext context) {
    if (collapsed) {
      final tile = Material(
        color: active ? DeskNavColors.activeFill : Colors.transparent,
        borderRadius: BorderRadius.circular(10),
        child: InkWell(
          onTap: active
              ? null
              : () {
                  Haptics.light();
                  onSelect();
                },
          borderRadius: BorderRadius.circular(10),
          hoverColor: DeskNavColors.hoverFill,
          child: SizedBox(
            width: 44,
            height: 44,
            child: Stack(
              alignment: Alignment.center,
              children: [
                Icon(
                  spec.icon,
                  size: 18,
                  color: active ? Colors.white : DeskNavColors.textDim,
                ),
                if (spec.badge > 0)
                  Positioned(
                    top: 6,
                    right: 6,
                    child: Container(
                      width: 8,
                      height: 8,
                      decoration: const BoxDecoration(
                        color: DeskNavColors.badge,
                        shape: BoxShape.circle,
                      ),
                    ),
                  ),
              ],
            ),
          ),
        ),
      );
      return Padding(
        padding: const EdgeInsets.symmetric(vertical: 1),
        child: Tooltip(
          message: spec.badge > 0
              ? '${spec.label} (${spec.badge})'
              : spec.label,
          preferBelow: false,
          child: tile,
        ),
      );
    }

    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 1),
      child: Material(
        color: active ? DeskNavColors.activeFill : Colors.transparent,
        borderRadius: BorderRadius.circular(9),
        child: InkWell(
          onTap: active
              ? null
              : () {
                  Haptics.light();
                  onSelect();
                },
          borderRadius: BorderRadius.circular(9),
          hoverColor: DeskNavColors.hoverFill,
          child: SizedBox(
            height: compact ? 34 : 38,
            child: Row(
              children: [
                AnimatedContainer(
                  duration: const Duration(milliseconds: 180),
                  width: 3,
                  height: active ? 18 : 0,
                  decoration: BoxDecoration(
                    color: AppTheme.primary,
                    borderRadius: BorderRadius.circular(2),
                  ),
                ),
                const SizedBox(width: 9),
                Icon(
                  spec.icon,
                  size: 17,
                  color: active ? Colors.white : DeskNavColors.textDim,
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: Text(
                    spec.label,
                    maxLines: 1,
                    overflow: TextOverflow.ellipsis,
                    style: TextStyle(
                      color: active ? Colors.white : DeskNavColors.textDim,
                      fontSize: 13.5,
                      fontWeight: active ? FontWeight.w700 : FontWeight.w600,
                    ),
                  ),
                ),
                if (spec.badge > 0)
                  Padding(
                    padding: const EdgeInsets.only(right: 10),
                    child: Container(
                      padding: const EdgeInsets.symmetric(
                        horizontal: 7,
                        vertical: 2,
                      ),
                      decoration: BoxDecoration(
                        color: DeskNavColors.badge.withValues(alpha: 0.22),
                        borderRadius: BorderRadius.circular(999),
                      ),
                      child: Text(
                        spec.badge > 99 ? '99+' : '${spec.badge}',
                        style: const TextStyle(
                          color: Colors.white,
                          fontSize: 10.5,
                          fontWeight: FontWeight.w800,
                        ),
                      ),
                    ),
                  ),
              ],
            ),
          ),
        ),
      ),
    );
  }
}
