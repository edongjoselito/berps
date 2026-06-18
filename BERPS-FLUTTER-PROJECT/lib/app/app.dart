import 'package:flutter/material.dart';
import 'package:phosphor_flutter/phosphor_flutter.dart';
import 'package:shared_preferences/shared_preferences.dart';

import '../core/theme/app_theme.dart';
import '../core/widgets/offline_banner.dart';
import '../features/auth/data/auth_api.dart';
import '../features/auth/data/session_store.dart';
import '../features/auth/presentation/auth_controller.dart';
import '../features/auth/presentation/login_screen.dart';
import '../features/auth/presentation/welcome_screen.dart';
import '../features/admin/shell/admin_home_screen.dart';
import '../features/home/presentation/staff_home_screen.dart';

class BerpsMobileApp extends StatefulWidget {
  const BerpsMobileApp({super.key});

  @override
  State<BerpsMobileApp> createState() => _BerpsMobileAppState();
}

class _BerpsMobileAppState extends State<BerpsMobileApp> {
  late final Future<AuthController> _controllerFuture = _createController();

  Future<AuthController> _createController() async {
    final preferences = await SharedPreferences.getInstance();
    final controller = AuthController(
      api: AuthApi(),
      store: SessionStore(preferences),
    );
    await controller.bootstrap();
    return controller;
  }

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      debugShowCheckedModeBanner: false,
      title: 'BERPS',
      // App title shown in task switcher.
      theme: AppTheme.build(),
      home: FutureBuilder<AuthController>(
        future: _controllerFuture,
        builder: (context, snapshot) {
          if (snapshot.hasError) {
            return _StartupError(message: snapshot.error.toString());
          }
          if (!snapshot.hasData) {
            return const _SplashScreen();
          }
          return _AuthFlow(controller: snapshot.data!);
        },
      ),
    );
  }
}

class _AuthFlow extends StatelessWidget {
  const _AuthFlow({required this.controller});
  final AuthController controller;

  @override
  Widget build(BuildContext context) {
    return AnimatedBuilder(
      animation: controller,
      builder: (context, _) {
        final Widget screen;
        switch (controller.status) {
          case AuthStatus.loading:
            screen = const _SplashScreen();
            break;
          case AuthStatus.unpaired:
            screen = controller.usesConfiguredBaseUrl
                ? LoginScreen(controller: controller)
                : WelcomeScreen(controller: controller);
            break;
          case AuthStatus.signedOut:
          case AuthStatus.error:
            screen = LoginScreen(controller: controller);
            break;
          case AuthStatus.signedIn:
            final session = controller.session!;
            final isAdmin = session.position.trim().toLowerCase() == 'admin';
            screen = isAdmin
                ? AdminHomeScreen(
                    session: session,
                    config: controller.config,
                    onSignOut: controller.signOut,
                    onAvatarUpdated: controller.updateSessionAvatar,
                    store: controller.store,
                  )
                : StaffHomeScreen(
                    session: session,
                    config: controller.config,
                    onSignOut: controller.signOut,
                    onAvatarUpdated: controller.updateSessionAvatar,
                    store: controller.store,
                  );
            break;
        }
        return Stack(
          children: [
            screen,
            const Positioned(top: 0, left: 0, right: 0, child: OfflineBanner()),
          ],
        );
      },
    );
  }
}

class _SplashScreen extends StatefulWidget {
  const _SplashScreen();

  @override
  State<_SplashScreen> createState() => _SplashScreenState();
}

class _SplashScreenState extends State<_SplashScreen>
    with SingleTickerProviderStateMixin {
  late final AnimationController _ctrl = AnimationController(
    vsync: this,
    duration: const Duration(milliseconds: 1400),
  )..repeat();

  @override
  void dispose() {
    _ctrl.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.white,
      body: Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Container(
              width: 76,
              height: 76,
              padding: const EdgeInsets.all(14),
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.circular(20),
                border: Border.all(color: AppTheme.border),
                boxShadow: [
                  BoxShadow(
                    color: AppTheme.primary.withValues(alpha: 0.18),
                    blurRadius: 30,
                    offset: const Offset(0, 14),
                  ),
                ],
              ),
              child: Image.asset('assets/logo.png', fit: BoxFit.contain),
            ),
            const SizedBox(height: 22),
            const Text(
              'BERPS',
              style: TextStyle(
                fontWeight: FontWeight.w900,
                fontSize: 18,
                letterSpacing: -0.4,
                color: AppTheme.textPrimary,
              ),
            ),
            const SizedBox(height: 4),
            const Text(
              'Loading workspace…',
              style: TextStyle(
                color: AppTheme.textSecondary,
                fontWeight: FontWeight.w600,
                fontSize: 12.5,
              ),
            ),
            const SizedBox(height: 22),
            SizedBox(
              width: 140,
              child: ClipRRect(
                borderRadius: BorderRadius.circular(999),
                child: AnimatedBuilder(
                  animation: _ctrl,
                  builder: (context, _) => LinearProgressIndicator(
                    minHeight: 4,
                    value: null,
                    backgroundColor: AppTheme.primary.withValues(alpha: 0.10),
                    valueColor: const AlwaysStoppedAnimation(AppTheme.primary),
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

class _StartupError extends StatelessWidget {
  const _StartupError({required this.message});
  final String message;

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.white,
      body: Center(
        child: Padding(
          padding: const EdgeInsets.all(24),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              Container(
                width: 60,
                height: 60,
                decoration: BoxDecoration(
                  color: AppTheme.danger.withValues(alpha: 0.10),
                  borderRadius: BorderRadius.circular(18),
                ),
                child: const Icon(
                  PhosphorIconsFill.warningCircle,
                  color: AppTheme.danger,
                  size: 28,
                ),
              ),
              const SizedBox(height: 14),
              const Text(
                'Startup failed',
                style: TextStyle(
                  fontWeight: FontWeight.w900,
                  fontSize: 18,
                  color: AppTheme.textPrimary,
                ),
              ),
              const SizedBox(height: 6),
              Text(
                message,
                textAlign: TextAlign.center,
                style: const TextStyle(
                  color: AppTheme.textSecondary,
                  fontSize: 13,
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
