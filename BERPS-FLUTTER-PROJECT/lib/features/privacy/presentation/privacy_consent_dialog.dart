import 'package:flutter/material.dart';
import 'package:phosphor_flutter/phosphor_flutter.dart';

import '../../../core/theme/app_theme.dart';
import '../../../core/utils/haptics.dart';
import 'privacy_policy_screen.dart';

class PrivacyConsentDialog extends StatefulWidget {
  const PrivacyConsentDialog({super.key});

  @override
  State<PrivacyConsentDialog> createState() => _PrivacyConsentDialogState();
}

class _PrivacyConsentDialogState extends State<PrivacyConsentDialog> {
  bool _agreed = false;

  @override
  Widget build(BuildContext context) {
    return AlertDialog(
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(24)),
      backgroundColor: AppTheme.surface,
      contentPadding: const EdgeInsets.fromLTRB(20, 20, 20, 0),
      actionsPadding: const EdgeInsets.fromLTRB(20, 0, 20, 20),
      content: ConstrainedBox(
        constraints: const BoxConstraints(maxWidth: 340),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Container(
              width: 56,
              height: 56,
              decoration: BoxDecoration(
                color: AppTheme.primarySoft,
                borderRadius: BorderRadius.circular(18),
              ),
              child: const Icon(
                PhosphorIconsBold.shieldCheck,
                color: AppTheme.primaryDark,
                size: 26,
              ),
            ),
            const SizedBox(height: 14),
            const Text(
              'Data Privacy Consent',
              style: TextStyle(
                fontSize: 18,
                fontWeight: FontWeight.w900,
                color: AppTheme.textPrimary,
              ),
            ),
            const SizedBox(height: 10),
            const Text(
              'Before you sign in, please review how BERPS handles your personal data. '
              'By continuing, you agree that your workspace may collect and process '
              'the information described in the Privacy Policy.',
              textAlign: TextAlign.center,
              style: TextStyle(
                fontSize: 13,
                color: AppTheme.textSecondary,
                height: 1.45,
                fontWeight: FontWeight.w600,
              ),
            ),
            const SizedBox(height: 14),
            TextButton(
              onPressed: () {
                Haptics.light();
                Navigator.of(context).push(
                  MaterialPageRoute(
                    builder: (_) => const PrivacyPolicyScreen(),
                  ),
                );
              },
              child: const Text(
                'Read Privacy Policy',
                style: TextStyle(fontSize: 13, fontWeight: FontWeight.w800),
              ),
            ),
            const SizedBox(height: 8),
            InkWell(
              onTap: () {
                Haptics.light();
                setState(() => _agreed = !_agreed);
              },
              borderRadius: BorderRadius.circular(12),
              child: Padding(
                padding: const EdgeInsets.symmetric(vertical: 6),
                child: Row(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    AnimatedContainer(
                      duration: const Duration(milliseconds: 180),
                      width: 20,
                      height: 20,
                      decoration: BoxDecoration(
                        color: _agreed ? AppTheme.primary : Colors.transparent,
                        borderRadius: BorderRadius.circular(6),
                        border: Border.all(
                          color: _agreed
                              ? AppTheme.primary
                              : AppTheme.borderStrong,
                          width: 1.8,
                        ),
                      ),
                      child: _agreed
                          ? const Icon(
                              PhosphorIconsBold.check,
                              size: 12,
                              color: Colors.white,
                            )
                          : null,
                    ),
                    const SizedBox(width: 10),
                    const Flexible(
                      child: Text(
                        'I agree to the Privacy Policy and data collection practices.',
                        style: TextStyle(
                          fontSize: 12.5,
                          fontWeight: FontWeight.w700,
                          color: AppTheme.textPrimary,
                          height: 1.35,
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
      actions: [
        FilledButton(
          onPressed: _agreed
              ? () {
                  Haptics.success();
                  Navigator.of(context).pop(true);
                }
              : null,
          style: FilledButton.styleFrom(
            minimumSize: const Size.fromHeight(48),
            backgroundColor: AppTheme.primary,
            disabledBackgroundColor: AppTheme.borderStrong,
          ),
          child: const Text('Continue'),
        ),
      ],
    );
  }
}
