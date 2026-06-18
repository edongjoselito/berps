import 'package:flutter/material.dart';

import '../theme/app_theme.dart';

/// Network-loaded BERPS logo with a graceful fallback icon. Sized to the
/// `size` parameter so it composes well inside the framed brand mark.
class BrandLogo extends StatelessWidget {
  const BrandLogo({
    super.key,
    required this.url,
    this.size = 72,
    this.borderRadius = 18,
    this.framed = true,
  });

  final String url;
  final double size;
  final double borderRadius;
  final bool framed;

  @override
  Widget build(BuildContext context) {
    final image = url.trim().isEmpty
        ? _fallback()
        : Image.network(
            url,
            width: size,
            height: size,
            fit: BoxFit.contain,
            errorBuilder: (_, _, _) => _fallback(),
            loadingBuilder: (context, child, progress) {
              if (progress == null) return child;
              return SizedBox(
                width: size,
                height: size,
                child: const Center(
                  child: SizedBox(
                    width: 20,
                    height: 20,
                    child: CircularProgressIndicator(strokeWidth: 2.2),
                  ),
                ),
              );
            },
          );

    if (!framed) return image;

    final framePadding = size * 0.12;
    return Container(
      width: size + framePadding * 2,
      height: size + framePadding * 2,
      padding: EdgeInsets.all(framePadding),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(borderRadius),
        border: Border.all(color: AppTheme.border),
        boxShadow: [
          BoxShadow(
            color: AppTheme.primary.withValues(alpha: 0.18),
            blurRadius: 28,
            offset: const Offset(0, 14),
          ),
        ],
      ),
      child: image,
    );
  }

  Widget _fallback() => Image.asset(
        'assets/logo.png',
        width: size,
        height: size,
        fit: BoxFit.contain,
      );
}
