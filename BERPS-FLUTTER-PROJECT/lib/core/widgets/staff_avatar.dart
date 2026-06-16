import 'package:flutter/material.dart';
import 'package:phosphor_flutter/phosphor_flutter.dart';

import '../theme/app_theme.dart';

class StaffAvatar extends StatelessWidget {
  const StaffAvatar({
    super.key,
    required this.url,
    this.size = 46,
    this.radius = 14,
    this.background,
    this.placeholderColor = AppTheme.primary,
    this.placeholderSize,
  });

  final String url;
  final double size;
  final double radius;
  final Color? background;
  final Color placeholderColor;
  final double? placeholderSize;

  @override
  Widget build(BuildContext context) {
    final fallback = Icon(
      PhosphorIconsFill.userCircle,
      color: placeholderColor,
      size: placeholderSize ?? (size * 0.6),
    );

    return Container(
      width: size,
      height: size,
      decoration: BoxDecoration(
        color: background ?? AppTheme.primarySoft,
        borderRadius: BorderRadius.circular(radius),
      ),
      clipBehavior: Clip.antiAlias,
      child: url.isEmpty
          ? Center(child: fallback)
          : Image.network(
              url,
              fit: BoxFit.cover,
              gaplessPlayback: true,
              loadingBuilder: (context, child, progress) {
                if (progress == null) return child;
                return Center(
                  child: SizedBox(
                    width: size * 0.4,
                    height: size * 0.4,
                    child: CircularProgressIndicator(
                      strokeWidth: 2,
                      color: placeholderColor.withValues(alpha: 0.6),
                    ),
                  ),
                );
              },
              errorBuilder: (_, _, _) => Center(child: fallback),
            ),
    );
  }
}
