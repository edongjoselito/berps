import 'package:flutter/material.dart';
import 'package:flutter/foundation.dart';
import 'package:phosphor_flutter/phosphor_flutter.dart';

import '../../../core/network/api_exception.dart';
import '../../../core/theme/app_theme.dart';
import '../../../core/utils/haptics.dart';
import '../../../core/widgets/animations.dart';
import '../../../core/widgets/brand_logo.dart';
import '../../../core/widgets/mobile_header.dart';
import '../../../core/widgets/orb_background.dart';
import '../domain/mobile_config.dart';
import 'auth_controller.dart';

class WelcomeScreen extends StatefulWidget {
  const WelcomeScreen({super.key, required this.controller});

  final AuthController controller;

  @override
  State<WelcomeScreen> createState() => _WelcomeScreenState();
}

class _WelcomeScreenState extends State<WelcomeScreen> {
  final _urlController = TextEditingController(text: _defaultWorkspaceUrl());
  final _formKey = GlobalKey<FormState>();

  bool _connecting = false;
  String? _error;

  static String _defaultWorkspaceUrl() {
    final runtimeBase = Uri.base;
    final host = runtimeBase.host.toLowerCase();
    final isWebLocalhost =
        kIsWeb &&
        (host == 'localhost' || host == '127.0.0.1') &&
        (runtimeBase.scheme == 'http' || runtimeBase.scheme == 'https');

    if (isWebLocalhost) {
      return '${runtimeBase.scheme}://$host/berps';
    }

    final bakedDefault = const String.fromEnvironment('BERPS_DEFAULT_BASE_URL');
    if (bakedDefault.isNotEmpty) {
      return bakedDefault;
    }

    return '';
  }

  @override
  void dispose() {
    _urlController.dispose();
    super.dispose();
  }

  Future<void> _continue() async {
    if (!(_formKey.currentState?.validate() ?? false)) {
      Haptics.warn();
      return;
    }

    Haptics.medium();
    setState(() {
      _connecting = true;
      _error = null;
    });

    try {
      await widget.controller.savePairing(_urlController.text);
    } on ApiException catch (e) {
      if (!mounted) return;
      Haptics.warn();
      setState(() => _error = e.message);
    } finally {
      if (mounted) setState(() => _connecting = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final config = widget.controller.config;

    return Scaffold(
      body: OrbBackground(
        child: SafeArea(
          child: LayoutBuilder(
            builder: (context, constraints) {
              return SingleChildScrollView(
                padding: const EdgeInsets.symmetric(
                  horizontal: 20,
                  vertical: 20,
                ),
                child: ConstrainedBox(
                  constraints: BoxConstraints(
                    minHeight: constraints.maxHeight - 40,
                  ),
                  child: Form(
                    key: _formKey,
                    child: Column(
                      mainAxisAlignment: MainAxisAlignment.center,
                      crossAxisAlignment: CrossAxisAlignment.stretch,
                      children: [
                        FadeSlide(
                          delay: const Duration(milliseconds: 40),
                          child: _WelcomeHero(config: config),
                        ),
                        const SizedBox(height: 28),
                        FadeSlide(
                          delay: const Duration(milliseconds: 120),
                          child: _ConnectCard(
                            urlController: _urlController,
                            connecting: _connecting,
                            error: _error,
                            onSubmit: _continue,
                          ),
                        ),
                      ],
                    ),
                  ),
                ),
              );
            },
          ),
        ),
      ),
    );
  }
}

class _WelcomeHero extends StatelessWidget {
  const _WelcomeHero({required this.config});

  final MobileConfig? config;

  @override
  Widget build(BuildContext context) {
    return Column(
      children: [
        BrandLogo(url: config?.logoUrl ?? '', size: 72),
        const SizedBox(height: 20),
        Text(
          'BERPS Mobile',
          textAlign: TextAlign.center,
          style: TextStyle(
            fontSize: 28,
            fontWeight: FontWeight.w900,
            color: AppTheme.textPrimary,
            letterSpacing: -0.8,
          ),
        ),
        const SizedBox(height: 8),
        Text(
          'Enter your workspace URL to get started.',
          textAlign: TextAlign.center,
          style: TextStyle(
            fontSize: 15,
            color: AppTheme.textSecondary,
            height: 1.45,
          ),
        ),
      ],
    );
  }
}

class _ConnectCard extends StatelessWidget {
  const _ConnectCard({
    required this.urlController,
    required this.connecting,
    required this.error,
    required this.onSubmit,
  });

  final TextEditingController urlController;
  final bool connecting;
  final String? error;
  final VoidCallback onSubmit;

  @override
  Widget build(BuildContext context) {
    return MobileSurfaceCard(
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          TextFormField(
            controller: urlController,
            keyboardType: TextInputType.url,
            autocorrect: false,
            textInputAction: TextInputAction.go,
            decoration: const InputDecoration(
              labelText: 'Workspace URL',
              hintText: 'https://your-workspace.com/berps',
              prefixIcon: Icon(PhosphorIconsRegular.globeHemisphereWest),
            ),
            validator: (value) {
              final v = (value ?? '').trim();
              if (v.isEmpty) return 'Workspace URL is required.';
              return null;
            },
            onFieldSubmitted: (_) => onSubmit(),
          ),
          if (error != null) ...[
            const SizedBox(height: 12),
            _ErrorBanner(message: error!),
          ],
          const SizedBox(height: 16),
          LoadingButton(
            label: 'Connect workspace',
            isLoading: connecting,
            onPressed: onSubmit,
          ),
        ],
      ),
    );
  }
}

class _ErrorBanner extends StatelessWidget {
  const _ErrorBanner({required this.message});
  final String message;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
      decoration: BoxDecoration(
        color: AppTheme.danger.withValues(alpha: 0.08),
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: AppTheme.danger.withValues(alpha: 0.25)),
      ),
      child: Row(
        children: [
          const Icon(
            PhosphorIconsFill.warningCircle,
            color: AppTheme.danger,
            size: 18,
          ),
          const SizedBox(width: 10),
          Expanded(
            child: Text(
              message,
              style: const TextStyle(color: AppTheme.danger, fontSize: 13),
            ),
          ),
        ],
      ),
    );
  }
}
