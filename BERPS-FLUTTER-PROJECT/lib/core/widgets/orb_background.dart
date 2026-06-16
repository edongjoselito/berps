import 'dart:math';

import 'package:flutter/material.dart';

import '../theme/app_theme.dart';

/// Professional auth backdrop. A soft vertical gradient with two slow,
/// far-away luminous blooms — replaces the heavier floating orbs with a
/// more refined enterprise feel.
class OrbBackground extends StatefulWidget {
  const OrbBackground({super.key, required this.child});
  final Widget child;

  @override
  State<OrbBackground> createState() => _OrbBackgroundState();
}

class _OrbBackgroundState extends State<OrbBackground>
    with SingleTickerProviderStateMixin {
  late final AnimationController _ctrl;

  @override
  void initState() {
    super.initState();
    _ctrl = AnimationController(
      vsync: this,
      duration: const Duration(seconds: 20),
    )..repeat();
  }

  @override
  void dispose() {
    _ctrl.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final media = MediaQuery.of(context).size;

    return DecoratedBox(
      decoration: const BoxDecoration(
        gradient: LinearGradient(
          begin: Alignment.topCenter,
          end: Alignment.bottomCenter,
          stops: [0.0, 0.55, 1.0],
          colors: [
            Color(0xFFFAFBFE),
            Color(0xFFF3F7FD),
            Color(0xFFEAF1FB),
          ],
        ),
      ),
      child: Stack(
        children: [
          AnimatedBuilder(
            animation: _ctrl,
            builder: (context, _) {
              final t = _ctrl.value * 2 * pi;
              return Stack(
                children: [
                  _bloom(
                    color: AppTheme.primary.withValues(alpha: 0.16),
                    size: 360,
                    dx: -120 + 12 * sin(t),
                    dy: -110 + 12 * cos(t),
                  ),
                  _bloom(
                    color: AppTheme.primaryDark.withValues(alpha: 0.12),
                    size: 320,
                    dx: media.width - 200 + 10 * cos(t * 0.6),
                    dy: media.height - 220 + 10 * sin(t * 0.6),
                  ),
                ],
              );
            },
          ),
          CustomPaint(
            size: media,
            painter: const _GridPainter(),
          ),
          widget.child,
        ],
      ),
    );
  }

  Widget _bloom({
    required Color color,
    required double size,
    required double dx,
    required double dy,
  }) {
    return Positioned(
      left: dx,
      top: dy,
      child: IgnorePointer(
        child: Container(
          width: size,
          height: size,
          decoration: BoxDecoration(
            shape: BoxShape.circle,
            gradient: RadialGradient(
              colors: [color, color.withValues(alpha: 0)],
            ),
          ),
        ),
      ),
    );
  }
}

class _GridPainter extends CustomPainter {
  const _GridPainter();

  @override
  void paint(Canvas canvas, Size size) {
    final paint = Paint()
      ..color = AppTheme.primary.withValues(alpha: 0.05)
      ..strokeWidth = 1;
    const step = 28.0;

    for (double y = 0; y < size.height; y += step) {
      canvas.drawLine(Offset(0, y), Offset(size.width, y), paint);
    }
    for (double x = 0; x < size.width; x += step) {
      canvas.drawLine(Offset(x, 0), Offset(x, size.height), paint);
    }
  }

  @override
  bool shouldRepaint(_GridPainter old) => false;
}
