import 'dart:async';

import 'package:flutter/material.dart';
import 'package:lucide_icons_flutter/lucide_icons.dart';

import '../../../core/theme/app_theme.dart';
import '../../../core/utils/haptics.dart';
import '../../../core/widgets/animations.dart';
import '../../../core/widgets/desktop_kit.dart';
import '../../auth/domain/staff_session.dart';
import '../../home/data/staff_api.dart';
import 'notifications_screen.dart';

class NotificationBell extends StatefulWidget {
  const NotificationBell({super.key, required this.session});

  final StaffSession session;

  @override
  State<NotificationBell> createState() => _NotificationBellState();
}

class _NotificationBellState extends State<NotificationBell>
    with WidgetsBindingObserver {
  static final StaffApi _api = StaffApi();
  int _count = 0;
  Timer? _poll;

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addObserver(this);
    _refresh();
    _poll = Timer.periodic(const Duration(seconds: 45), (_) => _refresh());
  }

  @override
  void dispose() {
    WidgetsBinding.instance.removeObserver(this);
    _poll?.cancel();
    super.dispose();
  }

  @override
  void didChangeAppLifecycleState(AppLifecycleState state) {
    if (state == AppLifecycleState.resumed) _refresh();
  }

  Future<void> _refresh() async {
    try {
      final data = await _api.fetchNotifications(
        baseUrl: widget.session.baseUrl,
        token: widget.session.token,
        limit: 1,
      );
      if (!mounted) return;
      if (data.unseenTotal != _count) {
        setState(() => _count = data.unseenTotal);
      }
    } catch (_) {
      // Silent — bell stays at last known value.
    }
  }

  Future<void> _open() async {
    Haptics.light();
    if (AppTheme.isDesktop) {
      await showSidePanel<void>(
        context: context,
        child: NotificationsScreen(session: widget.session),
      );
    } else {
      await Navigator.of(context).push(
        MaterialPageRoute(
          builder: (_) => NotificationsScreen(session: widget.session),
        ),
      );
    }
    if (mounted) _refresh();
  }

  @override
  Widget build(BuildContext context) {
    if (AppTheme.isDesktop) {
      // Squircle matching the other toolbar buttons; the count rides as a
      // red dot overlay instead of an inline pill.
      return Tooltip(
        message: 'Notifications',
        child: Material(
          color: Colors.transparent,
          borderRadius: BorderRadius.circular(8),
          child: InkWell(
            onTap: _open,
            borderRadius: BorderRadius.circular(8),
            hoverColor: const Color(0xFFE3E5EB),
            child: Ink(
              height: 34,
              padding: const EdgeInsets.symmetric(horizontal: 10),
              decoration: BoxDecoration(
                color: AppTheme.surfaceMuted,
                borderRadius: BorderRadius.circular(8),
              ),
              child: Row(
                mainAxisSize: MainAxisSize.min,
                children: [
                  const Icon(
                    LucideIcons.bell,
                    size: 16,
                    color: AppTheme.textPrimary,
                  ),
                  if (_count > 0) ...[
                    const SizedBox(width: 7),
                    Container(
                      padding: const EdgeInsets.symmetric(
                        horizontal: 5,
                        vertical: 1,
                      ),
                      decoration: BoxDecoration(
                        color: AppTheme.danger,
                        borderRadius: BorderRadius.circular(999),
                      ),
                      child: Text(
                        _count > 99 ? '99+' : '$_count',
                        style: const TextStyle(
                          color: Colors.white,
                          fontWeight: FontWeight.w800,
                          fontSize: 10,
                        ),
                      ),
                    ),
                  ],
                ],
              ),
            ),
          ),
        ),
      );
    }
    return PressScale(
      onTap: _open,
      child: SizedBox(
        width: 44,
        height: 44,
        child: Stack(
          clipBehavior: Clip.none,
          children: [
            Container(
              width: 44,
              height: 44,
              decoration: BoxDecoration(
                color: AppTheme.primaryDark,
                borderRadius: BorderRadius.circular(14),
                border: Border.all(color: AppTheme.primaryDark),
                boxShadow: AppTheme.isDesktop ? null : AppTheme.shadowSoft,
              ),
              child: const Icon(
                LucideIcons.bell,
                size: 18,
                color: Colors.white,
              ),
            ),
            if (_count > 0)
              Positioned(
                right: -4,
                top: -4,
                child: Container(
                  constraints: const BoxConstraints(
                    minWidth: 18,
                    minHeight: 18,
                  ),
                  padding: const EdgeInsets.symmetric(horizontal: 5),
                  decoration: BoxDecoration(
                    color: AppTheme.accent,
                    borderRadius: BorderRadius.circular(999),
                    border: Border.all(color: Colors.white, width: 2),
                  ),
                  alignment: Alignment.center,
                  child: Text(
                    _count > 99 ? '99+' : '$_count',
                    style: const TextStyle(
                      color: Colors.white,
                      fontWeight: FontWeight.w900,
                      fontSize: 9.5,
                      height: 1.1,
                    ),
                  ),
                ),
              ),
          ],
        ),
      ),
    );
  }
}
