import 'package:flutter/material.dart';
import 'package:phosphor_flutter/phosphor_flutter.dart';

import '../services/connectivity_service.dart';

/// A thin animated banner that slides down when the user loses
/// internet connectivity and slides back up when restored.
class OfflineBanner extends StatefulWidget {
  const OfflineBanner({super.key});

  @override
  State<OfflineBanner> createState() => _OfflineBannerState();
}

class _OfflineBannerState extends State<OfflineBanner>
    with SingleTickerProviderStateMixin {
  late final AnimationController _ctrl;
  late final ConnectivityService _service;

  @override
  void initState() {
    super.initState();
    _ctrl = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 320),
    );
    _service = ConnectivityService();
    _service.startMonitoring();
    _service.addListener(_onStatusChanged);
    if (!_service.isOnline) _ctrl.value = 1.0;
  }

  void _onStatusChanged() {
    if (!mounted) return;
    if (_service.isOnline) {
      _ctrl.reverse();
    } else {
      _ctrl.forward();
    }
  }

  @override
  void dispose() {
    _service.removeListener(_onStatusChanged);
    _ctrl.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return AnimatedBuilder(
      animation: _ctrl,
      builder: (context, child) {
        final value = Curves.easeOutCubic.transform(_ctrl.value);
        return Transform.translate(
          offset: Offset(0, -40 * (1 - value)),
          child: Opacity(opacity: value, child: child),
        );
      },
      child: Container(
        width: double.infinity,
        padding: const EdgeInsets.symmetric(vertical: 8, horizontal: 16),
        decoration: const BoxDecoration(color: Color(0xFF1E293B)),
        child: const SafeArea(
          bottom: false,
          child: Row(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Icon(PhosphorIconsBold.wifiSlash, color: Colors.white, size: 16),
              SizedBox(width: 8),
              Text(
                'No internet connection',
                style: TextStyle(
                  color: Colors.white,
                  fontSize: 12.5,
                  fontWeight: FontWeight.w700,
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
