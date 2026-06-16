import 'package:flutter/material.dart';
import 'package:phosphor_flutter/phosphor_flutter.dart';

import '../../../core/theme/app_theme.dart';
import '../../../core/utils/haptics.dart';
import '../../../core/widgets/animations.dart';
import '../../../core/widgets/mobile_header.dart';

class DataDeletionScreen extends StatelessWidget {
  const DataDeletionScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppTheme.background,
      body: SafeArea(
        child: CustomScrollView(
          slivers: [
            SliverToBoxAdapter(
              child: Padding(
                padding: const EdgeInsets.fromLTRB(16, 12, 16, 0),
                child: MobileHeader(
                  title: 'Delete My Data',
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
              sliver: SliverToBoxAdapter(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.stretch,
                  children: [
                    FadeSlide(
                      delay: const Duration(milliseconds: 60),
                      child: Container(
                        padding: const EdgeInsets.all(16),
                        decoration: BoxDecoration(
                          color: AppTheme.danger.withValues(alpha: 0.06),
                          borderRadius: BorderRadius.circular(18),
                          border: Border.all(
                            color: AppTheme.danger.withValues(alpha: 0.2),
                          ),
                        ),
                        child: Row(
                          children: [
                            Container(
                              width: 40,
                              height: 40,
                              decoration: BoxDecoration(
                                color: AppTheme.danger.withValues(alpha: 0.1),
                                borderRadius: BorderRadius.circular(12),
                              ),
                              child: const Icon(
                                PhosphorIconsBold.warning,
                                color: AppTheme.danger,
                                size: 20,
                              ),
                            ),
                            const SizedBox(width: 12),
                            const Expanded(
                              child: Text(
                                'Deleting your data requires action both on the server and on this device.',
                                style: TextStyle(
                                  fontSize: 13,
                                  fontWeight: FontWeight.w700,
                                  color: AppTheme.danger,
                                  height: 1.4,
                                ),
                              ),
                            ),
                          ],
                        ),
                      ),
                    ),
                    const SizedBox(height: 20),
                    FadeSlide(
                      delay: const Duration(milliseconds: 120),
                      child: Container(
                        padding: const EdgeInsets.all(16),
                        decoration: BoxDecoration(
                          color: Colors.white,
                          borderRadius: BorderRadius.circular(18),
                          border: Border.all(color: AppTheme.border),
                        ),
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            const Text(
                              'Server Data',
                              style: TextStyle(
                                fontSize: 14,
                                fontWeight: FontWeight.w900,
                                color: AppTheme.textPrimary,
                              ),
                            ),
                            const SizedBox(height: 8),
                            const _BodyText(
                              'Your profile, work records, tasks, attendance, DTR, and support tickets are stored in the BERPS database on your organization\'s server. '
                              'To delete this data permanently, contact your workspace administrator and request account deactivation or removal.',
                            ),
                            const SizedBox(height: 12),
                            _DeletionItem(
                              icon: PhosphorIconsBold.user,
                              label: 'Profile & account data',
                            ),
                            _DeletionItem(
                              icon: PhosphorIconsBold.calendarCheck,
                              label: 'Attendance & DTR records',
                            ),
                            _DeletionItem(
                              icon: PhosphorIconsBold.listChecks,
                              label: 'Tasks & assignments',
                            ),
                            _DeletionItem(
                              icon: PhosphorIconsBold.ticket,
                              label: 'Support tickets & comments',
                            ),
                            _DeletionItem(
                              icon: PhosphorIconsBold.image,
                              label: 'Uploaded profile photo',
                            ),
                          ],
                        ),
                      ),
                    ),
                    const SizedBox(height: 16),
                    FadeSlide(
                      delay: const Duration(milliseconds: 180),
                      child: Container(
                        padding: const EdgeInsets.all(16),
                        decoration: BoxDecoration(
                          color: Colors.white,
                          borderRadius: BorderRadius.circular(18),
                          border: Border.all(color: AppTheme.border),
                        ),
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            const Text(
                              'Device Data',
                              style: TextStyle(
                                fontSize: 14,
                                fontWeight: FontWeight.w900,
                                color: AppTheme.textPrimary,
                              ),
                            ),
                            const SizedBox(height: 8),
                            const _BodyText(
                              'The following data is stored only on this device and is never sent to the server. '
                              'Signing out will remove it immediately.',
                            ),
                            const SizedBox(height: 12),
                            _DeletionItem(
                              icon: PhosphorIconsBold.key,
                              label: 'Cached session token',
                            ),
                            _DeletionItem(
                              icon: PhosphorIconsBold.fingerprint,
                              label: 'Biometric login preference',
                            ),
                            _DeletionItem(
                              icon: PhosphorIconsBold.hardDrives,
                              label: 'Locally cached work data',
                            ),
                          ],
                        ),
                      ),
                    ),
                    const SizedBox(height: 24),
                    FadeSlide(
                      delay: const Duration(milliseconds: 240),
                      child: FilledButton.icon(
                        onPressed: () {
                          Haptics.medium();
                          Navigator.of(context).pop();
                        },
                        icon: const Icon(PhosphorIconsBold.signOut, size: 18),
                        label: const Text('Sign Out & Clear Device Data'),
                        style: FilledButton.styleFrom(
                          backgroundColor: AppTheme.danger,
                          minimumSize: const Size.fromHeight(52),
                        ),
                      ),
                    ),
                    const SizedBox(height: 12),
                    FadeSlide(
                      delay: const Duration(milliseconds: 280),
                      child: Center(
                        child: TextButton(
                          onPressed: () => Navigator.of(context).pop(),
                          child: const Text('Go Back'),
                        ),
                      ),
                    ),
                  ],
                ),
              ),
            ),
          ],
        ),
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

class _DeletionItem extends StatelessWidget {
  const _DeletionItem({required this.icon, required this.label});

  final IconData icon;
  final String label;

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 10),
      child: Row(
        children: [
          Icon(icon, size: 16, color: AppTheme.textSecondary),
          const SizedBox(width: 10),
          Text(
            label,
            style: const TextStyle(
              fontSize: 13,
              fontWeight: FontWeight.w700,
              color: AppTheme.textPrimary,
            ),
          ),
        ],
      ),
    );
  }
}
