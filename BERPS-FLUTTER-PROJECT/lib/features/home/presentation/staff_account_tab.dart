import 'package:flutter/material.dart';
import 'package:phosphor_flutter/phosphor_flutter.dart';

import '../../../core/services/biometric_auth.dart';
import '../../../core/theme/app_theme.dart';
import '../../../core/utils/haptics.dart';
import '../../../core/utils/responsive.dart';
import '../../../core/widgets/animations.dart';
import '../../../core/widgets/mobile_header.dart';
import '../../../core/widgets/staff_avatar.dart';
import '../../auth/data/session_store.dart';
import '../../auth/domain/mobile_config.dart';
import '../../auth/domain/staff_session.dart';
import '../../notifications/presentation/notification_bell.dart';
import '../../privacy/presentation/data_deletion_screen.dart';
import '../../privacy/presentation/privacy_policy_screen.dart';

class StaffAccountTab extends StatefulWidget {
  const StaffAccountTab({
    super.key,
    required this.session,
    required this.config,
    required this.onMenu,
    required this.onSignOut,
    required this.onOpenMyProfile,
    required this.store,
  });

  final StaffSession session;
  final MobileConfig? config;
  final VoidCallback onMenu;
  final Future<void> Function() onSignOut;
  final VoidCallback onOpenMyProfile;
  final SessionStore store;

  @override
  State<StaffAccountTab> createState() => _StaffAccountTabState();
}

class _StaffAccountTabState extends State<StaffAccountTab> {
  bool _biometricAvailable = false;
  bool _biometricEnabled = false;
  String _biometricLabel = 'Biometric';

  @override
  void initState() {
    super.initState();
    _checkBiometric();
  }

  Future<void> _checkBiometric() async {
    final available = await BiometricAuth().isAvailable;
    final label = await BiometricAuth().label;
    if (mounted) {
      setState(() {
        _biometricAvailable = available;
        _biometricLabel = label;
        _biometricEnabled = widget.store.readBiometricEnabled();
      });
    }
  }

  Future<void> _toggleBiometric(bool value) async {
    Haptics.medium();
    if (value) {
      final authenticated = await BiometricAuth().authenticate(
        localizedReason: 'Confirm to enable biometric login',
      );
      if (!authenticated) return;

      // Ask for password so we can save credentials for auto-login.
      if (!mounted) return;
      final password = await _askPassword();
      if (password == null || password.isEmpty) return;

      await widget.store.saveBiometricEnabled(true);
      await widget.store.saveBiometricCredentials(
        '${widget.session.username}:::$password',
      );
    } else {
      await widget.store.saveBiometricEnabled(false);
      await widget.store.clearBiometricCredentials();
    }
    if (mounted) {
      setState(() => _biometricEnabled = value);
    }
  }

  Future<String?> _askPassword() async {
    final controller = TextEditingController();
    final password = await showDialog<String>(
      context: context,
      barrierDismissible: false,
      builder: (ctx) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
        title: const Text('Enter your password'),
        content: TextField(
          controller: controller,
          obscureText: true,
          decoration: const InputDecoration(hintText: 'Password'),
          onSubmitted: (_) => Navigator.of(ctx).pop(controller.text),
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.of(ctx).pop(),
            child: const Text('Cancel'),
          ),
          FilledButton(
            onPressed: () => Navigator.of(ctx).pop(controller.text),
            child: const Text('Save'),
          ),
        ],
      ),
    );
    controller.dispose();
    return password?.trim();
  }

  @override
  Widget build(BuildContext context) {
    return ListView(
      padding: EdgeInsets.fromLTRB(context.gutter, 12, context.gutter, 32),
      children: [
        SafeArea(
          bottom: false,
          child: MobileHeader(
            title: 'Account',
            leadingIcon: PhosphorIconsBold.list,
            onLeadingTap: () {
              Haptics.light();
              widget.onMenu();
            },
            trailing: NotificationBell(session: widget.session),
          ),
        ),
        const SizedBox(height: 16),
        FadeSlide(
          delay: const Duration(milliseconds: 60),
          child: _ProfileCard(session: widget.session),
        ),
        const SizedBox(height: 14),
        FadeSlide(
          delay: const Duration(milliseconds: 80),
          child: _WorkspaceCard(
            baseUrl: widget.session.baseUrl,
            appName: widget.config?.appName ?? 'Workspace',
          ),
        ),
        const SizedBox(height: 20),
        FadeSlide(
          delay: const Duration(milliseconds: 100),
          child: _SectionLabel(
            icon: PhosphorIconsBold.userCircle,
            text: 'Profile',
          ),
        ),
        const SizedBox(height: 10),
        FadeSlide(
          delay: const Duration(milliseconds: 140),
          child: _ActionCard(
            icon: PhosphorIconsBold.identificationBadge,
            title: 'My Profile',
            subtitle: 'View your personal and employment details.',
            actionLabel: 'Open',
            onTap: () async {
              Haptics.light();
              widget.onOpenMyProfile();
            },
          ),
        ),
        const SizedBox(height: 20),
        FadeSlide(
          delay: const Duration(milliseconds: 200),
          child: _SectionLabel(
            icon: PhosphorIconsBold.shieldCheck,
            text: 'Security',
          ),
        ),
        const SizedBox(height: 10),
        if (_biometricAvailable)
          FadeSlide(
            delay: const Duration(milliseconds: 220),
            child: MobileSurfaceCard(
              child: SwitchListTile(
                value: _biometricEnabled,
                onChanged: _toggleBiometric,
                secondary: Icon(
                  PhosphorIconsBold.fingerprint,
                  color: AppTheme.primaryDark,
                ),
                title: Text(
                  '$_biometricLabel Login',
                  style: const TextStyle(
                    fontWeight: FontWeight.w800,
                    fontSize: 14,
                    color: AppTheme.textPrimary,
                  ),
                ),
                subtitle: const Text(
                  'Sign in faster using your device biometrics.',
                  style: TextStyle(
                    fontSize: 12.5,
                    color: AppTheme.textSecondary,
                    height: 1.35,
                  ),
                ),
                activeTrackColor: AppTheme.primary,
                contentPadding: const EdgeInsets.symmetric(
                  horizontal: 16,
                  vertical: 4,
                ),
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(18),
                ),
              ),
            ),
          ),
        const SizedBox(height: 20),
        FadeSlide(
          delay: const Duration(milliseconds: 240),
          child: _SectionLabel(
            icon: PhosphorIconsBold.fileText,
            text: 'Privacy',
          ),
        ),
        const SizedBox(height: 10),
        FadeSlide(
          delay: const Duration(milliseconds: 260),
          child: _ActionCard(
            icon: PhosphorIconsBold.shield,
            title: 'Privacy Policy',
            subtitle: 'How we collect, use, and protect your data.',
            actionLabel: 'View',
            onTap: () async {
              Haptics.light();
              await Navigator.of(context).push(
                MaterialPageRoute(
                  builder: (_) =>
                      PrivacyPolicyScreen(workspaceUrl: widget.session.baseUrl),
                ),
              );
            },
          ),
        ),
        const SizedBox(height: 10),
        FadeSlide(
          delay: const Duration(milliseconds: 280),
          child: _ActionCard(
            icon: PhosphorIconsBold.trash,
            title: 'Delete My Data',
            subtitle: 'Request deletion of your personal data.',
            actionLabel: 'Request',
            danger: true,
            onTap: () async {
              Haptics.light();
              await Navigator.of(context).push(
                MaterialPageRoute(builder: (_) => const DataDeletionScreen()),
              );
            },
          ),
        ),
        const SizedBox(height: 20),
        FadeSlide(
          delay: const Duration(milliseconds: 320),
          child: _SectionLabel(icon: PhosphorIconsBold.power, text: 'Session'),
        ),
        const SizedBox(height: 10),
        FadeSlide(
          delay: const Duration(milliseconds: 300),
          child: _ActionCard(
            icon: PhosphorIconsBold.signOut,
            title: 'Sign out',
            subtitle: 'End the current session on this device.',
            actionLabel: 'Sign out',
            danger: true,
            onTap: () async {
              await widget.onSignOut();
            },
          ),
        ),
        const SizedBox(height: 20),
        FadeSlide(
          delay: const Duration(milliseconds: 340),
          child: const _AppFooter(),
        ),
      ],
    );
  }
}

class _SectionLabel extends StatelessWidget {
  const _SectionLabel({required this.icon, required this.text});

  final IconData icon;
  final String text;

  @override
  Widget build(BuildContext context) {
    return Row(
      children: [
        Container(
          width: 28,
          height: 28,
          decoration: BoxDecoration(
            color: AppTheme.primarySoft,
            borderRadius: BorderRadius.circular(8),
          ),
          child: Icon(icon, size: 14, color: AppTheme.primaryDark),
        ),
        const SizedBox(width: 10),
        Text(
          text.toUpperCase(),
          style: const TextStyle(
            fontSize: 11,
            fontWeight: FontWeight.w900,
            letterSpacing: 1.5,
            color: AppTheme.textMuted,
          ),
        ),
      ],
    );
  }
}

class _WorkspaceCard extends StatelessWidget {
  const _WorkspaceCard({required this.baseUrl, required this.appName});

  final String baseUrl;
  final String appName;

  String _extractDomain(String url) {
    try {
      final uri = Uri.parse(url);
      return uri.host.isNotEmpty ? uri.host : url;
    } catch (_) {
      return url;
    }
  }

  @override
  Widget build(BuildContext context) {
    final domain = _extractDomain(baseUrl);
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: AppTheme.border),
      ),
      child: Row(
        children: [
          Container(
            width: 40,
            height: 40,
            decoration: BoxDecoration(
              gradient: const LinearGradient(
                colors: [Color(0xFF2D5A8A), Color(0xFF1E3A5F)],
              ),
              borderRadius: BorderRadius.circular(12),
            ),
            child: const Icon(
              PhosphorIconsBold.globe,
              color: Colors.white,
              size: 18,
            ),
          ),
          const SizedBox(width: 14),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  appName,
                  style: const TextStyle(
                    fontWeight: FontWeight.w900,
                    fontSize: 14,
                    color: AppTheme.textPrimary,
                  ),
                ),
                const SizedBox(height: 2),
                Text(
                  domain,
                  maxLines: 1,
                  overflow: TextOverflow.ellipsis,
                  style: const TextStyle(
                    fontSize: 12,
                    fontWeight: FontWeight.w700,
                    color: AppTheme.textSecondary,
                  ),
                ),
              ],
            ),
          ),
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
            decoration: BoxDecoration(
              color: AppTheme.success.withValues(alpha: 0.10),
              borderRadius: BorderRadius.circular(10),
            ),
            child: Row(
              mainAxisSize: MainAxisSize.min,
              children: [
                Icon(
                  PhosphorIconsBold.checkCircle,
                  size: 10,
                  color: AppTheme.success,
                ),
                const SizedBox(width: 4),
                Text(
                  'Connected',
                  style: TextStyle(
                    fontSize: 10,
                    fontWeight: FontWeight.w900,
                    color: AppTheme.success,
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}

class _ProfileCard extends StatelessWidget {
  const _ProfileCard({required this.session});

  final StaffSession session;

  @override
  Widget build(BuildContext context) {
    return MobileSurfaceCard(
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              StaffAvatar(
                url: session.avatarUrl,
                size: 60,
                radius: 18,
                placeholderColor: AppTheme.primaryDark,
                placeholderSize: 34,
              ),
              const SizedBox(width: 14),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      session.fullName.isEmpty
                          ? session.username
                          : session.fullName,
                      style: const TextStyle(
                        color: AppTheme.textPrimary,
                        fontWeight: FontWeight.w900,
                        fontSize: 17,
                        letterSpacing: -0.2,
                      ),
                    ),
                    const SizedBox(height: 4),
                    MobileInlineBadge(
                      icon: PhosphorIconsBold.briefcase,
                      label: session.position.isEmpty
                          ? 'Staff'
                          : session.position,
                    ),
                  ],
                ),
              ),
            ],
          ),
          const SizedBox(height: 16),
          _ProfileInfoRow(
            icon: PhosphorIconsBold.envelopeSimple,
            label: session.email.isEmpty ? 'No email on file' : session.email,
          ),
          const SizedBox(height: 10),
          _ProfileInfoRow(
            icon: PhosphorIconsBold.identificationCard,
            label: 'Username: ${session.username}',
          ),
        ],
      ),
    );
  }
}

class _ProfileInfoRow extends StatelessWidget {
  const _ProfileInfoRow({required this.icon, required this.label});

  final IconData icon;
  final String label;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        color: AppTheme.surfaceMuted,
        borderRadius: BorderRadius.circular(14),
      ),
      child: Row(
        children: [
          Icon(icon, color: AppTheme.textSecondary, size: 16),
          const SizedBox(width: 10),
          Expanded(
            child: Text(
              label,
              overflow: TextOverflow.ellipsis,
              style: const TextStyle(
                color: AppTheme.textPrimary,
                fontWeight: FontWeight.w700,
                fontSize: 12.5,
              ),
            ),
          ),
        ],
      ),
    );
  }
}

class _ActionCard extends StatelessWidget {
  const _ActionCard({
    required this.icon,
    required this.title,
    required this.subtitle,
    required this.actionLabel,
    required this.onTap,
    this.danger = false,
  });

  final IconData icon;
  final String title;
  final String subtitle;
  final String actionLabel;
  final Future<void> Function() onTap;
  final bool danger;

  @override
  Widget build(BuildContext context) {
    final accent = danger ? AppTheme.danger : AppTheme.primaryDark;
    return MobileSurfaceCard(
      padding: const EdgeInsets.all(16),
      child: InkWell(
        onTap: onTap,
        borderRadius: BorderRadius.circular(18),
        child: Row(
          children: [
            Container(
              width: 42,
              height: 42,
              decoration: BoxDecoration(
                color: accent.withValues(alpha: 0.08),
                borderRadius: BorderRadius.circular(14),
              ),
              child: Icon(icon, color: accent, size: 19),
            ),
            const SizedBox(width: 14),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    title,
                    style: const TextStyle(
                      fontWeight: FontWeight.w900,
                      color: AppTheme.textPrimary,
                      fontSize: 14,
                    ),
                  ),
                  const SizedBox(height: 3),
                  Text(
                    subtitle,
                    maxLines: 2,
                    overflow: TextOverflow.ellipsis,
                    style: const TextStyle(
                      color: AppTheme.textSecondary,
                      fontSize: 12.5,
                      height: 1.35,
                    ),
                  ),
                ],
              ),
            ),
            const SizedBox(width: 10),
            Row(
              mainAxisSize: MainAxisSize.min,
              children: [
                Text(
                  actionLabel,
                  style: TextStyle(
                    color: accent,
                    fontWeight: FontWeight.w800,
                    fontSize: 12.5,
                  ),
                ),
                const SizedBox(width: 6),
                Icon(PhosphorIconsBold.caretRight, size: 14, color: accent),
              ],
            ),
          ],
        ),
      ),
    );
  }
}

class _AppFooter extends StatelessWidget {
  const _AppFooter();

  @override
  Widget build(BuildContext context) {
    return Center(
      child: Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          Row(
            mainAxisSize: MainAxisSize.min,
            children: [
              Icon(
                PhosphorIconsBold.code,
                size: 12,
                color: AppTheme.textMuted.withValues(alpha: 0.7),
              ),
              const SizedBox(width: 5),
              Text(
                'BERPS Mobile',
                style: TextStyle(
                  fontSize: 11.5,
                  fontWeight: FontWeight.w800,
                  letterSpacing: 0.4,
                  color: AppTheme.textMuted.withValues(alpha: 0.9),
                ),
              ),
            ],
          ),
          const SizedBox(height: 3),
          Text(
            'Version 1.0.0',
            style: TextStyle(
              fontSize: 10,
              fontWeight: FontWeight.w600,
              color: AppTheme.textMuted.withValues(alpha: 0.6),
            ),
          ),
        ],
      ),
    );
  }
}
