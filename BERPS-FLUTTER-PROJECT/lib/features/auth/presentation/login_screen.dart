import 'package:flutter/material.dart';
import 'package:phosphor_flutter/phosphor_flutter.dart';

import '../../../core/network/api_exception.dart';
import '../../../core/services/biometric_auth.dart';
import '../../../core/theme/app_theme.dart';
import '../../../core/utils/haptics.dart';
import '../../../core/widgets/animations.dart';
import '../../../core/widgets/brand_logo.dart';
import '../../../core/widgets/mobile_header.dart';
import '../../../core/widgets/orb_background.dart';
import '../../privacy/presentation/privacy_consent_dialog.dart';
import 'auth_controller.dart';
import 'forgot_password_screen.dart';

class LoginScreen extends StatefulWidget {
  const LoginScreen({super.key, required this.controller});

  final AuthController controller;

  @override
  State<LoginScreen> createState() => _LoginScreenState();
}

class _LoginScreenState extends State<LoginScreen> {
  final _formKey = GlobalKey<FormState>();
  final _usernameController = TextEditingController();
  final _passwordController = TextEditingController();

  bool _obscure = true;
  bool _remember = true;
  bool _submitting = false;
  String? _error;
  String _biometricLabel = 'Biometric';
  bool _biometricEnabled = false;

  @override
  void initState() {
    super.initState();
    _checkBiometric();
  }

  Future<void> _checkBiometric() async {
    final store = widget.controller.store;
    final enabled = store.readBiometricEnabled();
    final creds = store.readBiometricCredentials();
    final label = await BiometricAuth().label;
    if (mounted) {
      setState(() {
        _biometricLabel = label;
        _biometricEnabled = enabled && creds != null && creds.isNotEmpty;
      });
    }
  }

  Future<void> _tryBiometric() async {
    Haptics.medium();
    final store = widget.controller.store;
    final creds = store.readBiometricCredentials();
    if (creds == null || creds.isEmpty) return;

    if (!store.readPrivacyConsent()) {
      final accepted = await showDialog<bool>(
        context: context,
        barrierDismissible: false,
        builder: (_) => const PrivacyConsentDialog(),
      );
      if (accepted == true) {
        await store.savePrivacyConsent(true);
      } else {
        return;
      }
    }

    final parts = creds.split(':::');
    if (parts.length != 2) return;
    final username = parts[0];
    final password = parts[1];

    final authenticated = await BiometricAuth().authenticate(
      localizedReason: 'Sign in to BERPS',
    );
    if (!authenticated) {
      if (mounted) {
        setState(
          () => _error =
              'Biometric authentication failed. Please sign in with your password.',
        );
      }
      return;
    }
    if (!mounted) return;

    setState(() {
      _submitting = true;
      _error = null;
    });

    try {
      await widget.controller.ensureWorkspaceReady();
      await widget.controller.signIn(username: username, password: password);
      if (mounted) Haptics.success();
    } on ApiException catch (e) {
      if (!mounted) return;
      Haptics.warn();
      setState(() => _error = e.message);
    } finally {
      if (mounted) setState(() => _submitting = false);
    }
  }

  @override
  void dispose() {
    _usernameController.dispose();
    _passwordController.dispose();
    super.dispose();
  }

  Future<void> _signIn() async {
    if (!(_formKey.currentState?.validate() ?? false)) {
      Haptics.warn();
      return;
    }

    final store = widget.controller.store;
    if (!store.readPrivacyConsent()) {
      final accepted = await showDialog<bool>(
        context: context,
        barrierDismissible: false,
        builder: (_) => const PrivacyConsentDialog(),
      );
      if (accepted == true) {
        await store.savePrivacyConsent(true);
      } else {
        return;
      }
    }

    Haptics.medium();
    setState(() {
      _submitting = true;
      _error = null;
    });

    try {
      await widget.controller.ensureWorkspaceReady();
      await widget.controller.signIn(
        username: _usernameController.text.trim(),
        password: _passwordController.text,
      );
      if (mounted) {
        Haptics.success();
        // If biometric is enabled in settings but credentials were never saved
        // (e.g. toggled on from Account tab), save them now so the biometric
        // button appears on the next sign-out.
        if (store.readBiometricEnabled() &&
            (store.readBiometricCredentials()?.isEmpty ?? true)) {
          await store.saveBiometricCredentials(
            '${_usernameController.text.trim()}:::${_passwordController.text}',
          );
        }
      }
    } on ApiException catch (e) {
      if (!mounted) return;
      Haptics.warn();
      setState(() => _error = e.message);
    } finally {
      if (mounted) setState(() => _submitting = false);
    }
  }

  Future<void> _openForgotPassword() async {
    Haptics.light();
    setState(() {
      _submitting = true;
      _error = null;
    });

    try {
      await widget.controller.ensureWorkspaceReady();
      if (!mounted) return;
      await Navigator.of(context).push(
        MaterialPageRoute(
          builder: (_) => ForgotPasswordScreen(
            api: widget.controller.api,
            baseUrl: widget.controller.baseUrl,
          ),
        ),
      );
    } on ApiException catch (e) {
      if (!mounted) return;
      Haptics.warn();
      setState(() => _error = e.message);
    } finally {
      if (mounted) setState(() => _submitting = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final config = widget.controller.config;
    final title = config?.appName.isNotEmpty == true
        ? config!.appName
        : 'BERPS';
    final baseUrl = widget.controller.baseUrl;
    final domain = _extractDomain(baseUrl);
    final canSwitchWorkspace = !widget.controller.usesConfiguredBaseUrl;

    return Scaffold(
      body: OrbBackground(
        child: SafeArea(
          child: LayoutBuilder(
            builder: (context, constraints) {
              return SingleChildScrollView(
                padding: const EdgeInsets.symmetric(
                  horizontal: 20,
                  vertical: 16,
                ),
                child: ConstrainedBox(
                  constraints: BoxConstraints(
                    minHeight: constraints.maxHeight - 32,
                  ),
                  child: Column(
                    mainAxisAlignment: MainAxisAlignment.center,
                    crossAxisAlignment: CrossAxisAlignment.stretch,
                    children: [
                      FadeSlide(
                        delay: const Duration(milliseconds: 60),
                        child: _LoginHero(
                          title: title,
                          logoUrl: config?.logoUrl ?? '',
                          domain: domain,
                        ),
                      ),
                      const SizedBox(height: 14),
                      FadeSlide(
                        delay: const Duration(milliseconds: 140),
                        child: _LoginCard(
                          formKey: _formKey,
                          usernameController: _usernameController,
                          passwordController: _passwordController,
                          obscure: _obscure,
                          remember: _remember,
                          submitting: _submitting,
                          error: _error,
                          onToggleObscure: () {
                            Haptics.light();
                            setState(() => _obscure = !_obscure);
                          },
                          onToggleRemember: (v) {
                            Haptics.light();
                            setState(() => _remember = v);
                          },
                          onForgot: _submitting ? null : _openForgotPassword,
                          onSubmit: _submitting ? null : _signIn,
                          biometricEnabled: _biometricEnabled,
                          biometricLabel: _biometricLabel,
                          onBiometric: _submitting ? null : _tryBiometric,
                        ),
                      ),
                      if (canSwitchWorkspace) ...[
                        const SizedBox(height: 14),
                        FadeSlide(
                          delay: const Duration(milliseconds: 200),
                          child: Center(
                            child: TextButton.icon(
                              onPressed: _submitting
                                  ? null
                                  : () {
                                      Haptics.light();
                                      widget.controller.resetPairing();
                                    },
                              icon: const Icon(
                                PhosphorIconsBold.arrowsLeftRight,
                                size: 16,
                                color: AppTheme.textMuted,
                              ),
                              label: Text(
                                'Not your workspace? Switch',
                                style: TextStyle(
                                  fontSize: 13,
                                  fontWeight: FontWeight.w700,
                                  color: AppTheme.textMuted.withValues(
                                    alpha: 0.8,
                                  ),
                                ),
                              ),
                            ),
                          ),
                        ),
                      ],
                    ],
                  ),
                ),
              );
            },
          ),
        ),
      ),
    );
  }

  String _extractDomain(String url) {
    try {
      final uri = Uri.parse(url);
      return uri.host;
    } catch (_) {
      return url;
    }
  }
}

class _LoginHero extends StatelessWidget {
  const _LoginHero({
    required this.title,
    required this.logoUrl,
    required this.domain,
  });

  final String title;
  final String logoUrl;
  final String domain;

  @override
  Widget build(BuildContext context) {
    return Column(
      children: [
        BrandLogo(url: logoUrl, size: 60),
        const SizedBox(height: 12),
        Text(
          title,
          textAlign: TextAlign.center,
          style: const TextStyle(
            fontSize: 26,
            fontWeight: FontWeight.w900,
            color: AppTheme.textPrimary,
            letterSpacing: -0.7,
            height: 1.1,
          ),
        ),
        const SizedBox(height: 6),
        if (domain.isNotEmpty)
          Row(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              const Icon(
                PhosphorIconsBold.globe,
                size: 12,
                color: AppTheme.textMuted,
              ),
              const SizedBox(width: 5),
              Text(
                domain,
                textAlign: TextAlign.center,
                style: const TextStyle(
                  fontSize: 12.5,
                  fontWeight: FontWeight.w800,
                  color: AppTheme.textMuted,
                  height: 1.4,
                ),
              ),
            ],
          )
        else
          const Text(
            'Sign in to continue.',
            textAlign: TextAlign.center,
            style: TextStyle(
              fontSize: 13.5,
              color: AppTheme.textSecondary,
              height: 1.4,
            ),
          ),
      ],
    );
  }
}

class _LoginCard extends StatelessWidget {
  const _LoginCard({
    required this.formKey,
    required this.usernameController,
    required this.passwordController,
    required this.obscure,
    required this.remember,
    required this.submitting,
    required this.error,
    required this.onToggleObscure,
    required this.onToggleRemember,
    required this.onForgot,
    required this.onSubmit,
    this.biometricEnabled = false,
    this.biometricLabel = 'Biometric',
    this.onBiometric,
  });

  final GlobalKey<FormState> formKey;
  final TextEditingController usernameController;
  final TextEditingController passwordController;
  final bool obscure;
  final bool remember;
  final bool submitting;
  final String? error;
  final VoidCallback onToggleObscure;
  final ValueChanged<bool> onToggleRemember;
  final VoidCallback? onForgot;
  final VoidCallback? onSubmit;
  final bool biometricEnabled;
  final String biometricLabel;
  final VoidCallback? onBiometric;

  @override
  Widget build(BuildContext context) {
    return MobileSurfaceCard(
      padding: const EdgeInsets.all(16),
      child: Form(
        key: formKey,
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            if (error != null) ...[
              _StatusBanner(message: error!),
              const SizedBox(height: 12),
            ],
            const _FieldLabel(icon: PhosphorIconsBold.user, text: 'Email'),
            TextFormField(
              controller: usernameController,
              autocorrect: false,
              keyboardType: TextInputType.emailAddress,
              decoration: const InputDecoration(
                hintText: 'you@company.com',
                contentPadding: EdgeInsets.symmetric(
                  horizontal: 14,
                  vertical: 14,
                ),
              ),
              validator: (value) {
                if ((value ?? '').trim().isEmpty) {
                  return 'Email is required.';
                }
                return null;
              },
            ),
            const SizedBox(height: 12),
            const _FieldLabel(
              icon: PhosphorIconsBold.lockKey,
              text: 'Password',
            ),
            TextFormField(
              controller: passwordController,
              obscureText: obscure,
              decoration: InputDecoration(
                hintText: 'Enter your password',
                contentPadding: const EdgeInsets.symmetric(
                  horizontal: 14,
                  vertical: 14,
                ),
                suffixIcon: IconButton(
                  icon: Icon(
                    obscure
                        ? PhosphorIconsBold.eye
                        : PhosphorIconsBold.eyeSlash,
                    color: obscure ? AppTheme.textMuted : AppTheme.primary,
                    size: 18,
                  ),
                  onPressed: onToggleObscure,
                ),
              ),
              validator: (value) {
                if ((value ?? '').isEmpty) {
                  return 'Password is required.';
                }
                return null;
              },
              onFieldSubmitted: (_) => onSubmit?.call(),
            ),
            const SizedBox(height: 12),
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                _RememberToggle(value: remember, onChanged: onToggleRemember),
                _UnderlineLink(label: 'Forgot password?', onTap: onForgot),
              ],
            ),
            const SizedBox(height: 14),
            LoadingButton(
              label: 'Sign in',
              isLoading: submitting,
              onPressed: onSubmit,
            ),
            if (biometricEnabled && onBiometric != null) ...[
              const SizedBox(height: 10),
              OutlinedButton.icon(
                onPressed: onBiometric,
                icon: Icon(
                  PhosphorIconsBold.fingerprint,
                  size: 18,
                  color: AppTheme.primary,
                ),
                label: Text('Sign in with $biometricLabel'),
                style: OutlinedButton.styleFrom(
                  foregroundColor: AppTheme.primary,
                  side: const BorderSide(color: AppTheme.borderStrong),
                ),
              ),
            ],
          ],
        ),
      ),
    );
  }
}

class _FieldLabel extends StatelessWidget {
  const _FieldLabel({required this.icon, required this.text});
  final IconData icon;
  final String text;

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.only(left: 4, bottom: 8),
      child: Row(
        children: [
          Icon(icon, size: 13, color: AppTheme.textSecondary),
          const SizedBox(width: 6),
          Text(
            text,
            style: const TextStyle(
              fontSize: 12.5,
              fontWeight: FontWeight.w700,
              color: AppTheme.textSecondary,
            ),
          ),
        ],
      ),
    );
  }
}

class _RememberToggle extends StatelessWidget {
  const _RememberToggle({required this.value, required this.onChanged});
  final bool value;
  final ValueChanged<bool> onChanged;

  @override
  Widget build(BuildContext context) {
    return PressScale(
      onTap: () => onChanged(!value),
      child: Padding(
        padding: const EdgeInsets.all(4),
        child: Row(
          mainAxisSize: MainAxisSize.min,
          children: [
            AnimatedContainer(
              duration: const Duration(milliseconds: 180),
              width: 18,
              height: 18,
              decoration: BoxDecoration(
                color: value ? AppTheme.primary : Colors.white,
                borderRadius: BorderRadius.circular(5),
                border: Border.all(
                  color: value ? AppTheme.primary : AppTheme.borderStrong,
                  width: 1.4,
                ),
              ),
              child: value
                  ? const Icon(
                      PhosphorIconsBold.check,
                      size: 12,
                      color: Colors.white,
                    )
                  : null,
            ),
            const SizedBox(width: 8),
            const Text(
              'Remember me',
              style: TextStyle(
                fontSize: 12.5,
                fontWeight: FontWeight.w600,
                color: AppTheme.textSecondary,
              ),
            ),
          ],
        ),
      ),
    );
  }
}

class _UnderlineLink extends StatefulWidget {
  const _UnderlineLink({required this.label, required this.onTap});
  final String label;
  final VoidCallback? onTap;

  @override
  State<_UnderlineLink> createState() => _UnderlineLinkState();
}

class _UnderlineLinkState extends State<_UnderlineLink> {
  bool _pressed = false;

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      behavior: HitTestBehavior.opaque,
      onTapDown: (_) => setState(() => _pressed = true),
      onTapUp: (_) => setState(() => _pressed = false),
      onTapCancel: () => setState(() => _pressed = false),
      onTap: widget.onTap,
      child: AnimatedDefaultTextStyle(
        duration: const Duration(milliseconds: 180),
        style: TextStyle(
          fontSize: 12.5,
          fontWeight: FontWeight.w700,
          color: widget.onTap == null
              ? AppTheme.textMuted
              : _pressed
              ? AppTheme.primary
              : AppTheme.primaryDark,
          decoration: _pressed ? TextDecoration.underline : TextDecoration.none,
        ),
        child: Text(widget.label),
      ),
    );
  }
}

class _StatusBanner extends StatelessWidget {
  const _StatusBanner({required this.message});
  final String message;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.fromLTRB(14, 14, 14, 14),
      decoration: BoxDecoration(
        color: AppTheme.danger.withValues(alpha: 0.06),
        borderRadius: BorderRadius.circular(14),
      ),
      child: Row(
        children: [
          Container(
            width: 36,
            height: 36,
            decoration: BoxDecoration(
              color: AppTheme.danger.withValues(alpha: 0.1),
              borderRadius: BorderRadius.circular(10),
            ),
            child: const Icon(
              PhosphorIconsFill.warningCircle,
              color: AppTheme.danger,
              size: 18,
            ),
          ),
          const SizedBox(width: 12),
          Expanded(
            child: Text(
              message,
              style: const TextStyle(
                color: AppTheme.danger,
                fontSize: 13,
                fontWeight: FontWeight.w600,
                height: 1.4,
              ),
            ),
          ),
        ],
      ),
    );
  }
}
