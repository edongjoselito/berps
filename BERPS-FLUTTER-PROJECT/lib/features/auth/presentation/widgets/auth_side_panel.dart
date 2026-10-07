import 'package:flutter/material.dart';
import 'package:lucide_icons_flutter/lucide_icons.dart';

import '../../../../core/theme/app_theme.dart';
import '../../../../core/widgets/brand_logo.dart';

/// Desktop-only brand panel used on the auth flow's split layout — a deep
/// navy gradient rail with the product mark, headline, and feature hints.
/// Not rendered on mobile.
class AuthSidePanel extends StatelessWidget {
  const AuthSidePanel({super.key, required this.logoUrl});

  final String logoUrl;

  @override
  Widget build(BuildContext context) {
    return Container(
      decoration: const BoxDecoration(
        gradient: LinearGradient(
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
          colors: [Color(0xFF0C1B2E), Color(0xFF16336B), Color(0xFF1D4ED8)],
          stops: [0.0, 0.55, 1.0],
        ),
      ),
      child: Stack(
        children: [
          Positioned(right: -90, top: -90, child: _glow(260, 0.07)),
          Positioned(left: -70, bottom: 90, child: _glow(190, 0.05)),
          Padding(
            padding: const EdgeInsets.fromLTRB(44, 40, 44, 34),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  children: [
                    BrandLogo(url: logoUrl, size: 38, borderRadius: 12),
                    const SizedBox(width: 14),
                    const Text(
                      'BERPS',
                      style: TextStyle(
                        color: Colors.white,
                        fontWeight: FontWeight.w900,
                        fontSize: 21,
                        letterSpacing: 1.6,
                      ),
                    ),
                  ],
                ),
                const Spacer(),
                const Text(
                  'Your staff\nworkspace.',
                  style: TextStyle(
                    color: Colors.white,
                    fontWeight: FontWeight.w900,
                    fontSize: 40,
                    height: 1.12,
                    letterSpacing: -1.2,
                  ),
                ),
                const SizedBox(height: 18),
                Text(
                  'Tasks, attendance, tickets and goals —\n'
                  'your whole workday in one place.',
                  style: TextStyle(
                    color: Colors.white.withValues(alpha: 0.72),
                    fontSize: 15,
                    height: 1.6,
                  ),
                ),
                const SizedBox(height: 44),
                const _Feature(
                  icon: LucideIcons.listChecks,
                  label: 'Tasks & ticket queues',
                ),
                const SizedBox(height: 16),
                const _Feature(
                  icon: LucideIcons.clock,
                  label: 'Attendance & daily time records',
                ),
                const SizedBox(height: 16),
                const _Feature(
                  icon: LucideIcons.trendingUp,
                  label: 'Support & performance goals',
                ),
                const Spacer(),
                Row(
                  children: [
                    Icon(
                      LucideIcons.shieldCheck,
                      size: 13,
                      color: Colors.white.withValues(alpha: 0.55),
                    ),
                    const SizedBox(width: 8),
                    Text(
                      AppTheme.productLabel,
                      style: TextStyle(
                        color: Colors.white.withValues(alpha: 0.55),
                        fontSize: 12,
                        fontWeight: FontWeight.w700,
                        letterSpacing: 0.3,
                      ),
                    ),
                  ],
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _glow(double size, double opacity) {
    return Container(
      width: size,
      height: size,
      decoration: BoxDecoration(
        shape: BoxShape.circle,
        gradient: RadialGradient(
          colors: [
            Colors.white.withValues(alpha: opacity),
            Colors.white.withValues(alpha: 0),
          ],
        ),
      ),
    );
  }
}

class _Feature extends StatelessWidget {
  const _Feature({required this.icon, required this.label});

  final IconData icon;
  final String label;

  @override
  Widget build(BuildContext context) {
    return Row(
      children: [
        Container(
          width: 34,
          height: 34,
          decoration: BoxDecoration(
            color: Colors.white.withValues(alpha: 0.10),
            borderRadius: BorderRadius.circular(10),
            border: Border.all(color: Colors.white.withValues(alpha: 0.12)),
          ),
          child: Icon(icon, size: 16, color: Colors.white),
        ),
        const SizedBox(width: 14),
        Text(
          label,
          style: TextStyle(
            color: Colors.white.withValues(alpha: 0.85),
            fontSize: 14,
            fontWeight: FontWeight.w600,
            letterSpacing: 0.1,
          ),
        ),
      ],
    );
  }
}
