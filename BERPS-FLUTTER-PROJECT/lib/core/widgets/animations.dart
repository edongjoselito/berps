import 'package:flutter/material.dart';

import '../theme/app_theme.dart';

/// Wraps a child with a quick press-in scale + light haptic-friendly feel.
/// Use on tappable surfaces (cards, chips, custom buttons).
class PressScale extends StatefulWidget {
  const PressScale({
    super.key,
    required this.child,
    this.onTap,
    this.scale = 0.97,
    this.duration = const Duration(milliseconds: 140),
    this.borderRadius,
  });

  final Widget child;
  final VoidCallback? onTap;
  final double scale;
  final Duration duration;
  final BorderRadius? borderRadius;

  @override
  State<PressScale> createState() => _PressScaleState();
}

class _PressScaleState extends State<PressScale> {
  bool _down = false;

  @override
  Widget build(BuildContext context) {
    final disabled = widget.onTap == null;
    return GestureDetector(
      behavior: HitTestBehavior.opaque,
      onTapDown: disabled ? null : (_) => setState(() => _down = true),
      onTapCancel: disabled ? null : () => setState(() => _down = false),
      onTapUp: disabled ? null : (_) => setState(() => _down = false),
      onTap: widget.onTap,
      child: AnimatedScale(
        duration: widget.duration,
        curve: Curves.easeOut,
        scale: _down ? widget.scale : 1.0,
        child: widget.child,
      ),
    );
  }
}

/// Fade + slide-from-bottom entrance animation. Compose with `delay` to
/// stagger child cards.
class FadeSlide extends StatefulWidget {
  const FadeSlide({
    super.key,
    required this.child,
    this.delay = Duration.zero,
    this.offset = const Offset(0, 18),
  });

  final Widget child;
  final Duration delay;
  final Offset offset;

  @override
  State<FadeSlide> createState() => _FadeSlideState();
}

class _FadeSlideState extends State<FadeSlide>
    with SingleTickerProviderStateMixin {
  late final AnimationController _ctrl = AnimationController(
    vsync: this,
    duration: const Duration(milliseconds: 480),
  );
  late final Animation<double> _opacity = CurvedAnimation(
    parent: _ctrl,
    curve: Curves.easeOut,
  );
  late final Animation<Offset> _slide = Tween<Offset>(
    begin: widget.offset,
    end: Offset.zero,
  ).animate(CurvedAnimation(parent: _ctrl, curve: Curves.easeOutCubic));

  @override
  void initState() {
    super.initState();
    Future.delayed(widget.delay, () {
      if (mounted) _ctrl.forward();
    });
  }

  @override
  void dispose() {
    _ctrl.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return AnimatedBuilder(
      animation: _ctrl,
      builder: (context, child) => Transform.translate(
        offset: _slide.value,
        child: Opacity(opacity: _opacity.value, child: child),
      ),
      child: widget.child,
    );
  }
}

/// A button that shows a loading spinner when [isLoading] is true.
/// Includes press-scale feedback and smooth cross-fade between label and spinner.
class LoadingButton extends StatelessWidget {
  const LoadingButton({
    super.key,
    required this.label,
    required this.onPressed,
    this.isLoading = false,
    this.isPrimary = true,
    this.height = 50,
    this.icon,
  });

  final String label;
  final VoidCallback? onPressed;
  final bool isLoading;
  final bool isPrimary;
  final double height;
  final IconData? icon;

  @override
  Widget build(BuildContext context) {
    final fg = isPrimary ? Colors.white : AppTheme.primaryDark;
    final bg = isPrimary ? AppTheme.primaryDark : Colors.white;

    return PressScale(
      onTap: isLoading ? null : onPressed,
      child: AnimatedContainer(
        duration: const Duration(milliseconds: 200),
        curve: Curves.easeOut,
        width: double.infinity,
        height: height,
        alignment: Alignment.center,
        decoration: BoxDecoration(
          gradient: isPrimary
              ? LinearGradient(
                  colors: [AppTheme.primary, AppTheme.primaryDark],
                  begin: Alignment.topLeft,
                  end: Alignment.bottomRight,
                )
              : null,
          color: isPrimary ? null : bg,
          borderRadius: BorderRadius.circular(14),
          border: isPrimary ? null : Border.all(color: AppTheme.borderStrong),
          boxShadow: isPrimary
              ? [
                  BoxShadow(
                    color: AppTheme.primaryDark.withValues(alpha: 0.28),
                    blurRadius: 18,
                    offset: const Offset(0, 10),
                  ),
                ]
              : null,
        ),
        child: Stack(
          alignment: Alignment.center,
          children: [
            AnimatedOpacity(
              opacity: isLoading ? 0 : 1,
              duration: const Duration(milliseconds: 150),
              child: Row(
                mainAxisSize: MainAxisSize.min,
                children: [
                  if (icon != null) ...[
                    Icon(icon, size: 16, color: fg),
                    const SizedBox(width: 8),
                  ],
                  Text(
                    label,
                    style: TextStyle(
                      color: fg,
                      fontWeight: FontWeight.w800,
                      fontSize: 14,
                      letterSpacing: 0.2,
                    ),
                  ),
                ],
              ),
            ),
            AnimatedOpacity(
              opacity: isLoading ? 1 : 0,
              duration: const Duration(milliseconds: 150),
              child: const SizedBox(
                width: 22,
                height: 22,
                child: CircularProgressIndicator(
                  strokeWidth: 2.4,
                  color: Colors.white,
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }
}

/// Counts an integer from 0 → `end` with an easeOutCubic curve.
class CountUp extends StatefulWidget {
  const CountUp({
    super.key,
    required this.end,
    required this.style,
    this.duration = const Duration(milliseconds: 900),
    this.textAlign = TextAlign.start,
  });

  final int end;
  final TextStyle style;
  final Duration duration;
  final TextAlign textAlign;

  @override
  State<CountUp> createState() => _CountUpState();
}

class _CountUpState extends State<CountUp> with SingleTickerProviderStateMixin {
  late final AnimationController _ctrl = AnimationController(
    vsync: this,
    duration: widget.duration,
  )..forward();

  late final Animation<double> _anim = CurvedAnimation(
    parent: _ctrl,
    curve: Curves.easeOutCubic,
  );

  @override
  void dispose() {
    _ctrl.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return AnimatedBuilder(
      animation: _anim,
      builder: (_, _) => Text(
        (_anim.value * widget.end).round().toString(),
        textAlign: widget.textAlign,
        style: widget.style,
      ),
    );
  }
}

/// A subtle white dot grid painted over a gradient surface, à la the SRMS hero.
class DotGridPainter extends CustomPainter {
  const DotGridPainter({
    required this.color,
    this.spacing = 20,
    this.radius = 1.4,
  });

  final Color color;
  final double spacing;
  final double radius;

  @override
  void paint(Canvas canvas, Size size) {
    final paint = Paint()..color = color;
    for (double x = spacing; x < size.width; x += spacing) {
      for (double y = spacing; y < size.height; y += spacing) {
        canvas.drawCircle(Offset(x, y), radius, paint);
      }
    }
  }

  @override
  bool shouldRepaint(DotGridPainter old) => old.color != color;
}

/// Animated waving emoji used as a delightful accent on the home greeting.
class WavingHand extends StatefulWidget {
  const WavingHand({super.key, this.size = 24});
  final double size;

  @override
  State<WavingHand> createState() => _WavingHandState();
}

class _WavingHandState extends State<WavingHand>
    with SingleTickerProviderStateMixin {
  late final AnimationController _ctrl = AnimationController(
    vsync: this,
    duration: const Duration(milliseconds: 1800),
  );

  late final Animation<double> _angle = TweenSequence<double>([
    TweenSequenceItem(tween: Tween(begin: 0.0, end: 0.4), weight: 15),
    TweenSequenceItem(tween: Tween(begin: 0.4, end: -0.25), weight: 20),
    TweenSequenceItem(tween: Tween(begin: -0.25, end: 0.35), weight: 20),
    TweenSequenceItem(tween: Tween(begin: 0.35, end: -0.15), weight: 20),
    TweenSequenceItem(tween: Tween(begin: -0.15, end: 0.0), weight: 25),
  ]).animate(CurvedAnimation(parent: _ctrl, curve: Curves.easeInOut));

  @override
  void initState() {
    super.initState();
    Future.delayed(const Duration(milliseconds: 300), () {
      if (mounted) _ctrl.forward();
    });
  }

  @override
  void dispose() {
    _ctrl.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return AnimatedBuilder(
      animation: _angle,
      builder: (_, _) => Transform.rotate(
        angle: _angle.value,
        alignment: Alignment.bottomCenter,
        child: Text('👋', style: TextStyle(fontSize: widget.size)),
      ),
    );
  }
}
