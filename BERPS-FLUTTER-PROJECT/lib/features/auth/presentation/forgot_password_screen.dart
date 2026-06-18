import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:phosphor_flutter/phosphor_flutter.dart';

import '../../../core/network/api_exception.dart';
import '../../../core/theme/app_theme.dart';
import '../../../core/utils/haptics.dart';
import '../../../core/widgets/animations.dart';
import '../../../core/widgets/mobile_header.dart';
import '../../../core/widgets/orb_background.dart';
import '../data/auth_api.dart';

enum _Step { email, otp, password, done }

class ForgotPasswordScreen extends StatefulWidget {
  const ForgotPasswordScreen({
    super.key,
    required this.api,
    required this.baseUrl,
  });

  final AuthApi api;
  final String baseUrl;

  @override
  State<ForgotPasswordScreen> createState() => _ForgotPasswordScreenState();
}

class _ForgotPasswordScreenState extends State<ForgotPasswordScreen> {
  final _emailController = TextEditingController();
  final _otpController = TextEditingController();
  final _passwordController = TextEditingController();
  final _confirmController = TextEditingController();

  _Step _step = _Step.email;
  bool _submitting = false;
  bool _obscure = true;
  bool _obscureConfirm = true;
  String? _error;
  String? _info;
  String _resetToken = '';

  @override
  void dispose() {
    _emailController.dispose();
    _otpController.dispose();
    _passwordController.dispose();
    _confirmController.dispose();
    super.dispose();
  }

  String get _email => _emailController.text.trim();

  bool _isValidEmail(String value) {
    final regex = RegExp(r'^[\w\.\-\+]+@[\w\-]+(\.[\w\-]+)+$');
    return regex.hasMatch(value);
  }

  Future<void> _sendCode() async {
    if (!_isValidEmail(_email)) {
      Haptics.warn();
      setState(() => _error = 'Please enter a valid email address.');
      return;
    }

    Haptics.medium();
    setState(() {
      _submitting = true;
      _error = null;
      _info = null;
    });

    try {
      await widget.api.requestPasswordReset(
        baseUrl: widget.baseUrl,
        email: _email,
      );
      if (!mounted) return;
      Haptics.success();
      setState(() {
        _step = _Step.otp;
        _info = 'We sent a 6-digit code to $_email.';
      });
    } on ApiException catch (e) {
      if (!mounted) return;
      Haptics.warn();
      setState(() => _error = e.message);
    } finally {
      if (mounted) setState(() => _submitting = false);
    }
  }

  Future<void> _verifyCode() async {
    final otp = _otpController.text.trim();
    if (otp.length != 6) {
      Haptics.warn();
      setState(() => _error = 'Enter the 6-digit code from your email.');
      return;
    }

    Haptics.medium();
    setState(() {
      _submitting = true;
      _error = null;
      _info = null;
    });

    try {
      final token = await widget.api.verifyResetOtp(
        baseUrl: widget.baseUrl,
        email: _email,
        otp: otp,
      );
      if (!mounted) return;
      Haptics.success();
      setState(() {
        _resetToken = token;
        _step = _Step.password;
      });
    } on ApiException catch (e) {
      if (!mounted) return;
      Haptics.warn();
      setState(() => _error = e.message);
    } finally {
      if (mounted) setState(() => _submitting = false);
    }
  }

  Future<void> _setNewPassword() async {
    final password = _passwordController.text;
    final confirm = _confirmController.text;

    if (password.length < 8) {
      Haptics.warn();
      setState(() => _error = 'Password must be at least 8 characters.');
      return;
    }
    if (password != confirm) {
      Haptics.warn();
      setState(() => _error = 'Passwords don\'t match.');
      return;
    }

    Haptics.medium();
    setState(() {
      _submitting = true;
      _error = null;
    });

    try {
      await widget.api.resetPasswordWithToken(
        baseUrl: widget.baseUrl,
        email: _email,
        resetToken: _resetToken,
        newPassword: password,
      );
      if (!mounted) return;
      Haptics.success();
      setState(() => _step = _Step.done);
    } on ApiException catch (e) {
      if (!mounted) return;
      Haptics.warn();
      setState(() => _error = e.message);
    } finally {
      if (mounted) setState(() => _submitting = false);
    }
  }

  void _back() {
    Haptics.light();
    if (_step == _Step.otp) {
      setState(() {
        _step = _Step.email;
        _otpController.clear();
        _error = null;
        _info = null;
      });
      return;
    }
    if (_step == _Step.password) {
      setState(() {
        _step = _Step.otp;
        _passwordController.clear();
        _confirmController.clear();
        _error = null;
      });
      return;
    }
    Navigator.of(context).pop();
  }

  @override
  Widget build(BuildContext context) {
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
                        delay: const Duration(milliseconds: 40),
                        child: Row(
                          children: [
                            MobileHeaderButton(
                              icon: PhosphorIconsBold.arrowLeft,
                              onTap: _submitting ? () {} : _back,
                            ),
                            const Spacer(),
                            _StepPill(step: _step),
                          ],
                        ),
                      ),
                      const SizedBox(height: 18),
                      FadeSlide(
                        delay: const Duration(milliseconds: 100),
                        child: _ResetHero(step: _step, email: _email),
                      ),
                      const SizedBox(height: 18),
                      FadeSlide(
                        delay: const Duration(milliseconds: 160),
                        child: MobileSurfaceCard(child: _buildBody()),
                      ),
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

  Widget _buildBody() {
    switch (_step) {
      case _Step.email:
        return _buildEmailStep();
      case _Step.otp:
        return _buildOtpStep();
      case _Step.password:
        return _buildPasswordStep();
      case _Step.done:
        return _buildDoneStep();
    }
  }

  Widget _buildEmailStep() {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.stretch,
      children: [
        if (_error != null) ...[
          _StatusBanner(message: _error!, isError: true),
          const SizedBox(height: 16),
        ],
        const _FieldLabel(icon: PhosphorIconsBold.envelope, text: 'Email'),
        TextField(
          controller: _emailController,
          autocorrect: false,
          keyboardType: TextInputType.emailAddress,
          decoration: const InputDecoration(hintText: 'you@company.com'),
          onSubmitted: (_) => _submitting ? null : _sendCode(),
        ),
        const SizedBox(height: 18),
        LoadingButton(
          label: 'Send verification code',
          isLoading: _submitting,
          onPressed: _sendCode,
        ),
      ],
    );
  }

  Widget _buildOtpStep() {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.stretch,
      children: [
        if (_error != null) ...[
          _StatusBanner(message: _error!, isError: true),
          const SizedBox(height: 16),
        ] else if (_info != null) ...[
          _StatusBanner(message: _info!, isError: false),
          const SizedBox(height: 16),
        ],
        const _FieldLabel(icon: PhosphorIconsBold.numpad, text: '6-digit code'),
        TextField(
          controller: _otpController,
          autocorrect: false,
          keyboardType: TextInputType.number,
          maxLength: 6,
          textAlign: TextAlign.center,
          style: TextStyle(
            fontSize: 22,
            fontWeight: FontWeight.w800,
            letterSpacing: 8,
            color: AppTheme.textPrimary,
          ),
          decoration: const InputDecoration(
            hintText: '••••••',
            counterText: '',
          ),
          inputFormatters: [FilteringTextInputFormatter.digitsOnly],
          onSubmitted: (_) => _submitting ? null : _verifyCode(),
        ),
        const SizedBox(height: 18),
        LoadingButton(
          label: 'Verify code',
          isLoading: _submitting,
          onPressed: _verifyCode,
        ),
        const SizedBox(height: 12),
        TextButton(
          onPressed: _submitting
              ? null
              : () {
                  setState(() {
                    _otpController.clear();
                    _error = null;
                  });
                  _sendCode();
                },
          child: const Text('Resend code'),
        ),
      ],
    );
  }

  Widget _buildPasswordStep() {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.stretch,
      children: [
        if (_error != null) ...[
          _StatusBanner(message: _error!, isError: true),
          const SizedBox(height: 16),
        ],
        const _FieldLabel(
          icon: PhosphorIconsBold.lockKey,
          text: 'New password',
        ),
        TextField(
          controller: _passwordController,
          obscureText: _obscure,
          decoration: InputDecoration(
            hintText: 'At least 8 characters',
            suffixIcon: IconButton(
              icon: Icon(
                _obscure ? PhosphorIconsBold.eye : PhosphorIconsBold.eyeSlash,
                color: _obscure ? AppTheme.textMuted : AppTheme.primary,
                size: 18,
              ),
              onPressed: () => setState(() => _obscure = !_obscure),
            ),
          ),
        ),
        const SizedBox(height: 16),
        const _FieldLabel(
          icon: PhosphorIconsBold.lockKey,
          text: 'Confirm password',
        ),
        TextField(
          controller: _confirmController,
          obscureText: _obscureConfirm,
          decoration: InputDecoration(
            hintText: 'Re-enter your new password',
            suffixIcon: IconButton(
              icon: Icon(
                _obscureConfirm
                    ? PhosphorIconsBold.eye
                    : PhosphorIconsBold.eyeSlash,
                color: _obscureConfirm ? AppTheme.textMuted : AppTheme.primary,
                size: 18,
              ),
              onPressed: () =>
                  setState(() => _obscureConfirm = !_obscureConfirm),
            ),
          ),
          onSubmitted: (_) => _submitting ? null : _setNewPassword(),
        ),
        const SizedBox(height: 18),
        LoadingButton(
          label: 'Reset password',
          isLoading: _submitting,
          onPressed: _setNewPassword,
        ),
      ],
    );
  }

  Widget _buildDoneStep() {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.stretch,
      children: [
        Center(
          child: Container(
            width: 64,
            height: 64,
            decoration: BoxDecoration(
              color: AppTheme.success.withValues(alpha: 0.10),
              borderRadius: BorderRadius.circular(20),
            ),
            child: const Icon(
              PhosphorIconsFill.checkCircle,
              color: AppTheme.success,
              size: 36,
            ),
          ),
        ),
        const SizedBox(height: 18),
        Text(
          'Password updated',
          textAlign: TextAlign.center,
          style: TextStyle(
            fontSize: 18,
            fontWeight: FontWeight.w900,
            color: AppTheme.textPrimary,
          ),
        ),
        const SizedBox(height: 8),
        Text(
          'You can now sign in with your new password.',
          textAlign: TextAlign.center,
          style: TextStyle(
            fontSize: 13,
            color: AppTheme.textSecondary,
            height: 1.45,
          ),
        ),
        const SizedBox(height: 20),
        LoadingButton(
          label: 'Back to sign in',
          onPressed: () => Navigator.of(context).pop(),
        ),
      ],
    );
  }
}

class _StepPill extends StatelessWidget {
  const _StepPill({required this.step});
  final _Step step;

  @override
  Widget build(BuildContext context) {
    final stepNumber = switch (step) {
      _Step.email => 1,
      _Step.otp => 2,
      _Step.password => 3,
      _Step.done => 3,
    };
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
      decoration: BoxDecoration(
        color: AppTheme.surface,
        borderRadius: BorderRadius.circular(999),
        border: Border.all(color: AppTheme.border),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          const Icon(
            PhosphorIconsBold.shieldCheck,
            size: 14,
            color: AppTheme.primaryDark,
          ),
          const SizedBox(width: 8),
          Text(
            'Step $stepNumber of 3',
            style: TextStyle(
              fontSize: 11.5,
              fontWeight: FontWeight.w700,
              color: AppTheme.textPrimary,
            ),
          ),
        ],
      ),
    );
  }
}

class _ResetHero extends StatelessWidget {
  const _ResetHero({required this.step, required this.email});
  final _Step step;
  final String email;

  @override
  Widget build(BuildContext context) {
    final title = switch (step) {
      _Step.email => 'Forgot password?',
      _Step.otp => 'Check your email',
      _Step.password => 'Set a new password',
      _Step.done => 'All set',
    };
    final subtitle = switch (step) {
      _Step.email =>
        'Enter the email tied to your BERPS account and we\'ll send you a verification code.',
      _Step.otp =>
        'We sent a 6-digit code to ${email.isEmpty ? "your email" : email}. It expires in 15 minutes.',
      _Step.password => 'Choose a strong password you haven\'t used before.',
      _Step.done => 'Your password has been updated successfully.',
    };

    return MobileSurfaceCard(
      child: Column(
        children: [
          Container(
            width: 56,
            height: 56,
            decoration: BoxDecoration(
              color: AppTheme.primarySoft,
              borderRadius: BorderRadius.circular(18),
            ),
            child: const Icon(
              PhosphorIconsBold.lockKeyOpen,
              color: AppTheme.primaryDark,
              size: 26,
            ),
          ),
          const SizedBox(height: 12),
          Text(
            title,
            textAlign: TextAlign.center,
            style: TextStyle(
              fontSize: 22,
              fontWeight: FontWeight.w900,
              color: AppTheme.textPrimary,
              letterSpacing: -0.6,
            ),
          ),
          const SizedBox(height: 6),
          Text(
            subtitle,
            textAlign: TextAlign.center,
            style: TextStyle(
              fontSize: 13,
              color: AppTheme.textSecondary,
              height: 1.45,
            ),
          ),
        ],
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
            style: TextStyle(
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

class _StatusBanner extends StatelessWidget {
  const _StatusBanner({required this.message, required this.isError});
  final String message;
  final bool isError;

  @override
  Widget build(BuildContext context) {
    final color = isError ? AppTheme.danger : AppTheme.primaryDark;
    final icon = isError
        ? PhosphorIconsFill.warningCircle
        : PhosphorIconsFill.info;
    return Container(
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        color: color.withValues(alpha: 0.08),
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: color.withValues(alpha: 0.25)),
      ),
      child: Row(
        children: [
          Icon(icon, color: color, size: 18),
          const SizedBox(width: 10),
          Expanded(
            child: Text(
              message,
              style: TextStyle(
                color: color,
                fontSize: 12.5,
                fontWeight: FontWeight.w600,
              ),
            ),
          ),
        ],
      ),
    );
  }
}
