import 'package:flutter/material.dart';
import 'package:phosphor_flutter/phosphor_flutter.dart';
import 'package:url_launcher/url_launcher.dart';

import '../../../core/theme/app_theme.dart';
import '../../../core/utils/haptics.dart';
import '../../../core/widgets/animations.dart';
import '../../../core/widgets/mobile_header.dart';

class PrivacyPolicyScreen extends StatelessWidget {
  const PrivacyPolicyScreen({super.key, this.workspaceUrl = ''});

  final String workspaceUrl;

  static const _lastUpdated = 'June 13, 2026';

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
    final domain = _extractDomain(workspaceUrl);

    return Scaffold(
      backgroundColor: AppTheme.background,
      body: SafeArea(
        child: CustomScrollView(
          slivers: [
            SliverToBoxAdapter(
              child: Padding(
                padding: const EdgeInsets.fromLTRB(16, 12, 16, 0),
                child: MobileHeader(
                  title: 'Privacy Policy',
                  leadingIcon: PhosphorIconsBold.arrowLeft,
                  onLeadingTap: () {
                    Haptics.light();
                    Navigator.of(context).pop();
                  },
                ),
              ),
            ),
            SliverPadding(
              padding: const EdgeInsets.all(16),
              sliver: SliverList(
                delegate: SliverChildListDelegate([
                  FadeSlide(
                    delay: const Duration(milliseconds: 60),
                    child: _SectionCard(
                      icon: PhosphorIconsBold.shield,
                      title: 'Your Data in BERPS',
                      children: [
                        const _BodyText(
                          'This Privacy Policy explains how the BERPS system '
                          'collects, uses, and protects your personal data within '
                          'your organization.',
                        ),
                        const SizedBox(height: 8),
                        _MetaRow(
                          icon: PhosphorIconsBold.calendar,
                          label: 'Last updated: $_lastUpdated',
                        ),
                        if (domain.isNotEmpty) ...[
                          const SizedBox(height: 4),
                          _MetaRow(
                            icon: PhosphorIconsBold.globe,
                            label: 'Workspace: $domain',
                          ),
                        ],
                      ],
                    ),
                  ),
                  const SizedBox(height: 16),
                  FadeSlide(
                    delay: const Duration(milliseconds: 120),
                    child: _SectionCard(
                      icon: PhosphorIconsBold.database,
                      title: 'What Data BERPS Stores',
                      children: const [
                        _BulletItem(
                          title: 'Account Profile',
                          description:
                              'Your user ID, username, password (encrypted), position/role, first name, middle name, last name, email address, and profile photo. This is managed by your workspace administrator in the BERPS users table.',
                        ),
                        _BulletItem(
                          title: 'Authentication Session',
                          description:
                              'When you sign in, the server issues a secure bearer token (JWT) that expires after 30 days. The token contains your user ID, username, role, and workspace ID for authorization purposes.',
                        ),
                        _BulletItem(
                          title: 'Work Records',
                          description:
                              'Tasks and assignments, attendance time-in/time-out entries, DTR (Daily Time Record) data, support tickets, calendar events, and reminders created or assigned to you within the workspace.',
                        ),
                        _BulletItem(
                          title: 'Device-Only Preferences',
                          description:
                              'Your optional biometric login preference and cached session are stored locally on this device only. These are never sent to the server.',
                        ),
                      ],
                    ),
                  ),
                  const SizedBox(height: 16),
                  FadeSlide(
                    delay: const Duration(milliseconds: 180),
                    child: _SectionCard(
                      icon: PhosphorIconsBold.lockKey,
                      title: 'How Your Data Is Used',
                      children: const [
                        _BulletItem(
                          title: 'Authentication & Access',
                          description:
                              'Your username and encrypted password verify your identity. Your role/position determines which features you can access in the mobile app.',
                        ),
                        _BulletItem(
                          title: 'Work Data Sync',
                          description:
                              'Tasks, attendance, DTR, support tickets, and calendar events are fetched from the workspace server so you can view and manage them in the app.',
                        ),
                        _BulletItem(
                          title: 'Password Recovery',
                          description:
                              'If you request a password reset, a one-time code is sent to your registered email address. Reset tokens expire automatically after a short time.',
                        ),
                        _BulletItem(
                          title: 'Profile Photo',
                          description:
                              'Your uploaded avatar is stored on the workspace server and displayed in the app for identification by your team.',
                        ),
                      ],
                    ),
                  ),
                  const SizedBox(height: 16),
                  FadeSlide(
                    delay: const Duration(milliseconds: 240),
                    child: _SectionCard(
                      icon: PhosphorIconsBold.users,
                      title: 'Who Can See Your Data',
                      children: const [
                        _BodyText(
                          'BERPS is a self-hosted system. Your data stays within your organization.',
                        ),
                        SizedBox(height: 8),
                        _BulletItem(
                          title: 'Within Your Workspace',
                          description:
                              'Your profile, tasks, attendance, and support tickets are visible to workspace administrators and team members based on the permissions set in your BERPS system.',
                        ),
                        _BulletItem(
                          title: 'No External Sharing',
                          description:
                              'BERPS does not sell, share, or transmit your personal data to external companies, advertisers, or analytics services.',
                        ),
                        _BulletItem(
                          title: 'Server Location',
                          description:
                              'All data is stored in the MySQL database on the BERPS server managed by your organization.',
                        ),
                      ],
                    ),
                  ),
                  const SizedBox(height: 16),
                  FadeSlide(
                    delay: const Duration(milliseconds: 300),
                    child: _SectionCard(
                      icon: PhosphorIconsBold.userGear,
                      title: 'Your Rights',
                      children: const [
                        _BulletItem(
                          title: 'Access',
                          description:
                              'You can view your profile, tasks, attendance, and support tickets at any time through the app.',
                        ),
                        _BulletItem(
                          title: 'Correction',
                          description:
                              'Contact your workspace administrator to update inaccurate profile information (name, email, position, or avatar).',
                        ),
                        _BulletItem(
                          title: 'Deletion',
                          description:
                              'Request your workspace administrator to deactivate or delete your account. This removes your profile and work records from the server database.',
                        ),
                        _BulletItem(
                          title: 'Withdraw Consent',
                          description:
                              'Sign out and clear the app data from your device settings to remove all locally stored sessions and preferences.',
                        ),
                      ],
                    ),
                  ),
                  const SizedBox(height: 16),
                  FadeSlide(
                    delay: const Duration(milliseconds: 360),
                    child: _SectionCard(
                      icon: PhosphorIconsBold.trash,
                      title: 'Data Retention',
                      children: const [
                        _BodyText(
                          'Your account and work data remain in the BERPS database as long as your workspace administrator keeps your account active. '
                          'When an admin deactivates or deletes your account, your records are removed from the system. '
                          'Session tokens expire automatically after 30 days. Password reset codes expire after 15 minutes. '
                          'All data cached on this device is removed when you sign out or uninstall the app.',
                        ),
                      ],
                    ),
                  ),
                  const SizedBox(height: 16),
                  FadeSlide(
                    delay: const Duration(milliseconds: 420),
                    child: _SectionCard(
                      icon: PhosphorIconsBold.envelopeSimple,
                      title: 'Contact',
                      children: [
                        const _BodyText(
                          'For questions about your data, or to request access, correction, or deletion, contact your workspace administrator. '
                          'They manage the BERPS server and your account settings.',
                        ),
                        if (domain.isNotEmpty) ...[
                          const SizedBox(height: 12),
                          _ContactRow(
                            icon: PhosphorIconsBold.globe,
                            label: domain,
                            onTap: () {},
                          ),
                        ],
                      ],
                    ),
                  ),
                  const SizedBox(height: 16),
                  FadeSlide(
                    delay: const Duration(milliseconds: 480),
                    child: _SectionCard(
                      icon: PhosphorIconsBold.arrowSquareOut,
                      title: 'External Policy Link',
                      children: [
                        const _BodyText(
                          'You can also view this privacy policy in your web browser.',
                        ),
                        const SizedBox(height: 12),
                        if (workspaceUrl.isNotEmpty)
                          _ContactRow(
                            icon: PhosphorIconsBold.link,
                            label: 'Open in Browser',
                            onTap: () async {
                              Haptics.light();
                              final uri = Uri.parse(
                                '${workspaceUrl.replaceAll(RegExp(r'/$'), '')}/mobile/privacy',
                              );
                              if (await canLaunchUrl(uri)) {
                                await launchUrl(
                                  uri,
                                  mode: LaunchMode.externalApplication,
                                );
                              }
                            },
                          )
                        else
                          const _BodyText(
                            'Available after you are signed in to a workspace.',
                          ),
                      ],
                    ),
                  ),
                  const SizedBox(height: 32),
                ]),
              ),
            ),
          ],
        ),
      ),
    );
  }
}

class _SectionCard extends StatelessWidget {
  const _SectionCard({
    required this.icon,
    required this.title,
    required this.children,
  });

  final IconData icon;
  final String title;
  final List<Widget> children;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(18),
        border: Border.all(color: AppTheme.border),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Container(
                width: 32,
                height: 32,
                decoration: BoxDecoration(
                  color: AppTheme.primarySoft,
                  borderRadius: BorderRadius.circular(9),
                ),
                child: Icon(icon, size: 16, color: AppTheme.primaryDark),
              ),
              const SizedBox(width: 10),
              Text(
                title,
                style: const TextStyle(
                  fontSize: 15,
                  fontWeight: FontWeight.w900,
                  color: AppTheme.textPrimary,
                ),
              ),
            ],
          ),
          const SizedBox(height: 12),
          ...children,
        ],
      ),
    );
  }
}

class _BodyText extends StatelessWidget {
  const _BodyText(this.text);
  final String text;

  @override
  Widget build(BuildContext context) {
    return Text(
      text,
      style: const TextStyle(
        fontSize: 13,
        color: AppTheme.textSecondary,
        height: 1.5,
        fontWeight: FontWeight.w600,
      ),
    );
  }
}

class _BulletItem extends StatelessWidget {
  const _BulletItem({required this.title, required this.description});

  final String title;
  final String description;

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 10),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Container(
            width: 6,
            height: 6,
            margin: const EdgeInsets.only(top: 6),
            decoration: const BoxDecoration(
              color: AppTheme.primary,
              shape: BoxShape.circle,
            ),
          ),
          const SizedBox(width: 10),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  title,
                  style: const TextStyle(
                    fontSize: 13,
                    fontWeight: FontWeight.w800,
                    color: AppTheme.textPrimary,
                  ),
                ),
                const SizedBox(height: 2),
                Text(
                  description,
                  style: const TextStyle(
                    fontSize: 12.5,
                    color: AppTheme.textSecondary,
                    height: 1.4,
                    fontWeight: FontWeight.w600,
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

class _MetaRow extends StatelessWidget {
  const _MetaRow({required this.icon, required this.label});

  final IconData icon;
  final String label;

  @override
  Widget build(BuildContext context) {
    return Row(
      children: [
        Icon(icon, size: 12, color: AppTheme.textMuted),
        const SizedBox(width: 6),
        Text(
          label,
          style: const TextStyle(
            fontSize: 11.5,
            fontWeight: FontWeight.w700,
            color: AppTheme.textMuted,
          ),
        ),
      ],
    );
  }
}

class _ContactRow extends StatelessWidget {
  const _ContactRow({
    required this.icon,
    required this.label,
    required this.onTap,
  });

  final IconData icon;
  final String label;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    return InkWell(
      onTap: onTap,
      borderRadius: BorderRadius.circular(12),
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
        decoration: BoxDecoration(
          color: AppTheme.surfaceMuted,
          borderRadius: BorderRadius.circular(12),
        ),
        child: Row(
          children: [
            Icon(icon, size: 16, color: AppTheme.primaryDark),
            const SizedBox(width: 10),
            Text(
              label,
              style: const TextStyle(
                fontSize: 13,
                fontWeight: FontWeight.w800,
                color: AppTheme.primaryDark,
              ),
            ),
          ],
        ),
      ),
    );
  }
}
