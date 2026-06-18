import 'package:flutter/material.dart';
import 'package:image_picker/image_picker.dart';
import 'package:phosphor_flutter/phosphor_flutter.dart';

import '../../../core/network/api_exception.dart';
import '../../../core/theme/app_theme.dart';
import '../../../core/utils/haptics.dart';
import '../../../core/utils/responsive.dart';
import '../../../core/widgets/animations.dart';
import '../../../core/widgets/app_toast.dart';
import '../../../core/widgets/mobile_header.dart';
import '../../../core/widgets/skeleton.dart';
import '../../../core/widgets/staff_avatar.dart';
import '../../auth/domain/staff_session.dart';
import '../data/staff_api.dart';
import '../domain/staff_profile.dart';

class StaffProfileScreen extends StatefulWidget {
  const StaffProfileScreen({
    super.key,
    required this.session,
    required this.onAvatarUpdated,
    this.openPhotoPicker = false,
  });

  final StaffSession session;
  final Future<void> Function(String avatarUrl) onAvatarUpdated;
  final bool openPhotoPicker;

  @override
  State<StaffProfileScreen> createState() => _StaffProfileScreenState();
}

class _StaffProfileScreenState extends State<StaffProfileScreen> {
  final StaffApi _api = StaffApi();
  final ImagePicker _picker = ImagePicker();

  Future<StaffProfile>? _future;
  String _avatarOverrideUrl = '';
  bool _uploading = false;
  bool _editing = false;
  bool _saving = false;

  // Controllers for editable fields
  final _contactController = TextEditingController();
  final _officialEmailController = TextEditingController();
  final _birthDateController = TextEditingController();
  final _birthPlaceController = TextEditingController();
  final _bloodTypeController = TextEditingController();
  final _maritalStatusController = TextEditingController();
  final _heightController = TextEditingController();
  final _weightController = TextEditingController();
  final _addressController = TextEditingController();

  @override
  void initState() {
    super.initState();
    _reload();
    if (widget.openPhotoPicker) {
      WidgetsBinding.instance.addPostFrameCallback((_) {
        if (mounted) _showPhotoSourceSheet();
      });
    }
  }

  @override
  void dispose() {
    _contactController.dispose();
    _officialEmailController.dispose();
    _birthDateController.dispose();
    _birthPlaceController.dispose();
    _bloodTypeController.dispose();
    _maritalStatusController.dispose();
    _heightController.dispose();
    _weightController.dispose();
    _addressController.dispose();
    super.dispose();
  }

  void _initControllers(StaffProfile p) {
    _contactController.text = p.contactNo;
    _officialEmailController.text = p.officialEmail;
    _birthDateController.text = p.birthDate;
    _birthPlaceController.text = p.birthPlace;
    _bloodTypeController.text = p.bloodType;
    _maritalStatusController.text = p.maritalStatus;
    _heightController.text = p.height;
    _weightController.text = p.weight;
    _addressController.text = p.address;
  }

  void _startEditing(StaffProfile p) {
    Haptics.light();
    _initControllers(p);
    setState(() => _editing = true);
  }

  void _cancelEditing() {
    Haptics.light();
    setState(() => _editing = false);
  }

  Future<void> _saveProfile() async {
    Haptics.medium();
    setState(() => _saving = true);
    try {
      final updated = await _api.updateProfile(
        baseUrl: widget.session.baseUrl,
        token: widget.session.token,
        body: {
          'empMobile': _contactController.text.trim(),
          'empEmail': _officialEmailController.text.trim(),
          'BirthDate': _birthDateController.text.trim(),
          'BirthPlace': _birthPlaceController.text.trim(),
          'bloodType': _bloodTypeController.text.trim(),
          'MaritalStatus': _maritalStatusController.text.trim(),
          'height': _heightController.text.trim(),
          'weight': _weightController.text.trim(),
          'resStreet': _addressController.text.trim(),
        },
      );
      if (!mounted) return;
      setState(() {
        _editing = false;
        _saving = false;
        _future = Future.value(updated);
      });
      AppToast.success(context, 'Profile updated.');
    } on ApiException catch (e) {
      if (!mounted) return;
      setState(() => _saving = false);
      AppToast.error(context, e.message);
    } catch (e) {
      if (!mounted) return;
      setState(() => _saving = false);
      AppToast.error(context, 'Unable to update profile.');
    }
  }

  void _reload() {
    setState(() {
      _future = _api.fetchProfile(
        baseUrl: widget.session.baseUrl,
        token: widget.session.token,
      );
    });
  }

  Future<void> _pickAndUpload(ImageSource source) async {
    Haptics.light();
    try {
      final picked = await _picker.pickImage(
        source: source,
        maxWidth: 1600,
        maxHeight: 1600,
        imageQuality: 88,
      );
      if (picked == null) return;

      final bytes = await picked.readAsBytes();
      final filename = picked.name.isEmpty
          ? 'avatar_${DateTime.now().millisecondsSinceEpoch}.jpg'
          : picked.name;

      setState(() => _uploading = true);

      final updated = await _api.uploadAvatar(
        baseUrl: widget.session.baseUrl,
        token: widget.session.token,
        filename: filename,
        bytes: bytes,
      );

      if (!mounted) return;
      // Cache-bust the avatar URL so Image.network re-fetches the new file.
      final bust = DateTime.now().millisecondsSinceEpoch;
      final freshUrl = updated.avatarUrl.contains('?')
          ? '${updated.avatarUrl}&t=$bust'
          : '${updated.avatarUrl}?t=$bust';
      setState(() {
        _avatarOverrideUrl = freshUrl;
        _future = Future.value(updated);
      });
      await widget.onAvatarUpdated(freshUrl);

      if (!mounted) return;
      AppToast.success(context, 'Profile picture updated.');
    } on ApiException catch (e) {
      if (!mounted) return;
      AppToast.error(context, e.message);
    } catch (e) {
      if (!mounted) return;
      AppToast.error(context, 'Unable to update photo.');
    } finally {
      if (mounted) {
        setState(() => _uploading = false);
      }
    }
  }

  void _showPhotoSourceSheet() {
    Haptics.light();
    showModalBottomSheet<void>(
      context: context,
      backgroundColor: AppTheme.surface,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
      ),
      builder: (sheetContext) {
        return SafeArea(
          top: false,
          child: Padding(
            padding: const EdgeInsets.fromLTRB(16, 12, 16, 18),
            child: Column(
              mainAxisSize: MainAxisSize.min,
              children: [
                Container(
                  width: 44,
                  height: 4,
                  margin: const EdgeInsets.only(bottom: 14),
                  decoration: BoxDecoration(
                    color: AppTheme.border,
                    borderRadius: BorderRadius.circular(999),
                  ),
                ),
                Align(
                  alignment: Alignment.centerLeft,
                  child: Text(
                    'Update profile picture',
                    style: TextStyle(
                      fontWeight: FontWeight.w900,
                      fontSize: 16,
                      color: AppTheme.textPrimary,
                    ),
                  ),
                ),
                const SizedBox(height: 12),
                _PhotoSourceTile(
                  icon: PhosphorIconsBold.camera,
                  title: 'Take a photo',
                  subtitle: 'Use the device camera.',
                  onTap: () {
                    Navigator.of(sheetContext).pop();
                    _pickAndUpload(ImageSource.camera);
                  },
                ),
                const SizedBox(height: 10),
                _PhotoSourceTile(
                  icon: PhosphorIconsBold.image,
                  title: 'Choose from gallery',
                  subtitle: 'Pick an existing image.',
                  onTap: () {
                    Navigator.of(sheetContext).pop();
                    _pickAndUpload(ImageSource.gallery);
                  },
                ),
              ],
            ),
          ),
        );
      },
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppTheme.background,
      body: SafeArea(
        bottom: false,
        child: FutureBuilder<StaffProfile>(
          future: _future,
          builder: (context, snapshot) {
            final loading = snapshot.connectionState == ConnectionState.waiting;
            final error = snapshot.error;
            final profile = snapshot.data;

            return RefreshIndicator(
              color: AppTheme.primary,
              onRefresh: () async => _reload(),
              child: ListView(
                physics: const AlwaysScrollableScrollPhysics(),
                padding: EdgeInsets.fromLTRB(
                  context.gutter,
                  12,
                  context.gutter,
                  32,
                ),
                children: [
                  MobileHeader(
                    title: 'My Profile',
                    leadingIcon: PhosphorIconsBold.caretLeft,
                    onLeadingTap: () {
                      Haptics.light();
                      if (_editing) {
                        _cancelEditing();
                      } else {
                        Navigator.of(context).maybePop();
                      }
                    },
                    trailing: (profile != null && !_editing)
                        ? IconButton(
                            onPressed: () => _startEditing(profile),
                            icon: const Icon(
                              PhosphorIconsBold.pencilSimple,
                              size: 20,
                              color: AppTheme.primaryDark,
                            ),
                          )
                        : null,
                  ),
                  const SizedBox(height: 16),
                  if (loading && profile == null)
                    const _ProfileSkeleton()
                  else if (error != null && profile == null)
                    _ErrorCard(
                      message: error is ApiException
                          ? error.message
                          : 'Unable to load profile.',
                      onRetry: _reload,
                    )
                  else if (profile != null) ...[
                    FadeSlide(
                      delay: const Duration(milliseconds: 60),
                      child: _ProfileHeaderCard(
                        profile: profile,
                        avatarOverrideUrl: _avatarOverrideUrl,
                        uploading: _uploading,
                        onTapAvatar: _showPhotoSourceSheet,
                      ),
                    ),
                    const SizedBox(height: 18),
                    FadeSlide(
                      delay: const Duration(milliseconds: 120),
                      child: _SectionHeader(
                        icon: PhosphorIconsBold.briefcase,
                        title: 'Official Information',
                      ),
                    ),
                    const SizedBox(height: 10),
                    FadeSlide(
                      delay: const Duration(milliseconds: 160),
                      child: _InfoCard(
                        rows: [
                          _InfoRow(
                            'Employee No.',
                            profile.employeeNo,
                            PhosphorIconsBold.identificationBadge,
                          ),
                          _InfoRow(
                            'Position',
                            profile.position,
                            PhosphorIconsBold.crown,
                          ),
                          _InfoRow(
                            'Department',
                            profile.department,
                            PhosphorIconsBold.buildings,
                          ),
                          _InfoRow(
                            'Date Hired',
                            profile.dateHired,
                            PhosphorIconsBold.calendarCheck,
                          ),
                          _InfoRow(
                            'TIN',
                            profile.tinNo,
                            PhosphorIconsBold.fileText,
                          ),
                          _InfoRow(
                            'GSIS BP No.',
                            profile.gsisNo,
                            PhosphorIconsBold.fileText,
                          ),
                          _InfoRow(
                            'PAG-IBIG No.',
                            profile.pagibigNo,
                            PhosphorIconsBold.fileText,
                          ),
                          _InfoRow(
                            'SSS',
                            profile.sssNo,
                            PhosphorIconsBold.fileText,
                          ),
                          _InfoRow(
                            'PhilHealth No.',
                            profile.philHealthNo,
                            PhosphorIconsBold.fileText,
                          ),
                        ],
                      ),
                    ),
                    const SizedBox(height: 18),
                    FadeSlide(
                      delay: const Duration(milliseconds: 200),
                      child: _SectionHeader(
                        icon: PhosphorIconsBold.user,
                        title: 'Personal Information',
                      ),
                    ),
                    const SizedBox(height: 10),
                    FadeSlide(
                      delay: const Duration(milliseconds: 240),
                      child: _editing
                          ? Column(
                              crossAxisAlignment: CrossAxisAlignment.stretch,
                              children: [
                                MobileSurfaceCard(
                                  child: _InfoRowTile(
                                    row: _InfoRow(
                                      'Gender',
                                      profile.gender,
                                      PhosphorIconsBold.users,
                                    ),
                                  ),
                                ),
                                const SizedBox(height: 10),
                                _EditCard(
                                  fields: [
                                    _EditField(
                                      label: 'Birth Date',
                                      controller: _birthDateController,
                                    ),
                                    _EditField(
                                      label: 'Birth Place',
                                      controller: _birthPlaceController,
                                    ),
                                    _EditField(
                                      label: 'Blood Type',
                                      controller: _bloodTypeController,
                                    ),
                                    _EditField(
                                      label: 'Marital Status',
                                      controller: _maritalStatusController,
                                    ),
                                    _EditField(
                                      label: 'Height',
                                      controller: _heightController,
                                    ),
                                    _EditField(
                                      label: 'Weight',
                                      controller: _weightController,
                                    ),
                                  ],
                                ),
                              ],
                            )
                          : _InfoCard(
                              rows: [
                                _InfoRow(
                                  'Gender',
                                  profile.gender,
                                  PhosphorIconsBold.users,
                                ),
                                _InfoRow(
                                  'Birth Date',
                                  profile.birthDate,
                                  PhosphorIconsBold.cake,
                                ),
                                _InfoRow(
                                  'Birth Place',
                                  profile.birthPlace,
                                  PhosphorIconsBold.mapPin,
                                ),
                                _InfoRow(
                                  'Blood Type',
                                  profile.bloodType,
                                  PhosphorIconsBold.heart,
                                ),
                                _InfoRow(
                                  'Marital Status',
                                  profile.maritalStatus,
                                  PhosphorIconsBold.heart,
                                ),
                                _InfoRow(
                                  'Height',
                                  profile.height,
                                  PhosphorIconsBold.ruler,
                                ),
                                _InfoRow(
                                  'Weight',
                                  profile.weight,
                                  PhosphorIconsBold.scales,
                                ),
                              ],
                            ),
                    ),
                    const SizedBox(height: 18),
                    FadeSlide(
                      delay: const Duration(milliseconds: 280),
                      child: _SectionHeader(
                        icon: PhosphorIconsBold.phone,
                        title: 'Contact Information',
                      ),
                    ),
                    const SizedBox(height: 10),
                    FadeSlide(
                      delay: const Duration(milliseconds: 320),
                      child: _editing
                          ? Column(
                              crossAxisAlignment: CrossAxisAlignment.stretch,
                              children: [
                                MobileSurfaceCard(
                                  child: _InfoRowTile(
                                    row: _InfoRow(
                                      'Account Email',
                                      profile.email,
                                      PhosphorIconsBold.envelope,
                                    ),
                                  ),
                                ),
                                const SizedBox(height: 10),
                                _EditCard(
                                  fields: [
                                    _EditField(
                                      label: 'Contact No.',
                                      controller: _contactController,
                                      keyboardType: TextInputType.phone,
                                    ),
                                    _EditField(
                                      label: 'Official Email',
                                      controller: _officialEmailController,
                                      keyboardType: TextInputType.emailAddress,
                                    ),
                                    _EditField(
                                      label: 'Address',
                                      controller: _addressController,
                                    ),
                                  ],
                                ),
                              ],
                            )
                          : _InfoCard(
                              rows: [
                                _InfoRow(
                                  'Contact No.',
                                  profile.contactNo,
                                  PhosphorIconsBold.deviceMobile,
                                ),
                                _InfoRow(
                                  'Account Email',
                                  profile.email,
                                  PhosphorIconsBold.envelope,
                                ),
                                _InfoRow(
                                  'Official Email',
                                  profile.officialEmail,
                                  PhosphorIconsBold.envelopeSimple,
                                ),
                                _InfoRow(
                                  'Address',
                                  profile.address,
                                  PhosphorIconsBold.house,
                                ),
                              ],
                            ),
                    ),
                    if (_editing) ...[
                      const SizedBox(height: 18),
                      FadeSlide(
                        delay: const Duration(milliseconds: 360),
                        child: Row(
                          children: [
                            Expanded(
                              child: OutlinedButton(
                                onPressed: _saving ? null : _cancelEditing,
                                child: const Text('Cancel'),
                              ),
                            ),
                            const SizedBox(width: 12),
                            Expanded(
                              child: LoadingButton(
                                label: 'Save',
                                isLoading: _saving,
                                onPressed: _saveProfile,
                              ),
                            ),
                          ],
                        ),
                      ),
                    ],
                  ],
                ],
              ),
            );
          },
        ),
      ),
    );
  }
}

class _ProfileHeaderCard extends StatelessWidget {
  const _ProfileHeaderCard({
    required this.profile,
    required this.avatarOverrideUrl,
    required this.uploading,
    required this.onTapAvatar,
  });

  final StaffProfile profile;
  final String avatarOverrideUrl;
  final bool uploading;
  final VoidCallback onTapAvatar;

  @override
  Widget build(BuildContext context) {
    final url = avatarOverrideUrl.isNotEmpty
        ? avatarOverrideUrl
        : profile.avatarUrl;

    return Container(
      padding: const EdgeInsets.fromLTRB(0, 24, 0, 22),
      decoration: BoxDecoration(
        gradient: const LinearGradient(
          colors: [Color(0xFF1E3A5F), Color(0xFF2D5A8A)],
        ),
        borderRadius: BorderRadius.circular(20),
        boxShadow: [
          BoxShadow(
            color: const Color(0xFF1E3A5F).withValues(alpha: 0.22),
            blurRadius: 18,
            offset: const Offset(0, 6),
          ),
        ],
      ),
      child: Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          GestureDetector(
            behavior: HitTestBehavior.opaque,
            onTap: uploading ? null : onTapAvatar,
            child: Stack(
              alignment: Alignment.center,
              children: [
                Container(
                  width: 96,
                  height: 96,
                  decoration: BoxDecoration(
                    color: AppTheme.surface.withValues(alpha: 0.15),
                    shape: BoxShape.circle,
                    border: Border.all(
                      color: Colors.white.withValues(alpha: 0.30),
                      width: 3,
                    ),
                  ),
                  child: ClipOval(
                    child: StaffAvatar(
                      url: url,
                      size: 96,
                      radius: 0,
                      background: Colors.transparent,
                      placeholderColor: AppTheme.surfaceMuted,
                      placeholderSize: 40,
                    ),
                  ),
                ),
                if (uploading)
                  Container(
                    width: 96,
                    height: 96,
                    decoration: BoxDecoration(
                      color: Colors.black.withValues(alpha: 0.35),
                      shape: BoxShape.circle,
                    ),
                    child: const Center(
                      child: SizedBox(
                        width: 24,
                        height: 24,
                        child: CircularProgressIndicator(
                          strokeWidth: 2.5,
                          valueColor: AlwaysStoppedAnimation(Colors.white),
                        ),
                      ),
                    ),
                  )
                else
                  Positioned(
                    right: 2,
                    bottom: 2,
                    child: Container(
                      width: 30,
                      height: 30,
                      decoration: BoxDecoration(
                        color: AppTheme.surface,
                        shape: BoxShape.circle,
                        border: Border.all(
                          color: const Color(0xFF1E3A5F),
                          width: 2,
                        ),
                        boxShadow: [
                          BoxShadow(
                            color: Colors.black.withValues(alpha: 0.15),
                            blurRadius: 6,
                            offset: const Offset(0, 2),
                          ),
                        ],
                      ),
                      child: Icon(
                        PhosphorIconsBold.camera,
                        color: AppTheme.primaryDark,
                        size: 14,
                      ),
                    ),
                  ),
              ],
            ),
          ),
          const SizedBox(height: 14),
          Padding(
            padding: const EdgeInsets.symmetric(horizontal: 20),
            child: Text(
              profile.fullName.isEmpty ? profile.username : profile.fullName,
              textAlign: TextAlign.center,
              style: const TextStyle(
                color: Colors.white,
                fontWeight: FontWeight.w900,
                fontSize: 18,
                letterSpacing: -0.2,
              ),
            ),
          ),
          const SizedBox(height: 8),
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
            decoration: BoxDecoration(
              color: AppTheme.surface.withValues(alpha: 0.12),
              borderRadius: BorderRadius.circular(20),
            ),
            child: Row(
              mainAxisSize: MainAxisSize.min,
              children: [
                Icon(
                  PhosphorIconsBold.briefcase,
                  size: 12,
                  color: Colors.white.withValues(alpha: 0.85),
                ),
                const SizedBox(width: 5),
                Text(
                  profile.position.isEmpty ? 'Staff' : profile.position,
                  style: TextStyle(
                    color: Colors.white.withValues(alpha: 0.85),
                    fontWeight: FontWeight.w800,
                    fontSize: 11.5,
                  ),
                ),
              ],
            ),
          ),
          const SizedBox(height: 8),
          if (profile.department.isNotEmpty)
            Text(
              profile.department,
              style: TextStyle(
                color: Colors.white.withValues(alpha: 0.60),
                fontSize: 12,
                fontWeight: FontWeight.w600,
              ),
            ),
          const SizedBox(height: 4),
          Text(
            profile.email.isEmpty ? 'No email on file' : profile.email,
            textAlign: TextAlign.center,
            style: TextStyle(
              color: Colors.white.withValues(alpha: 0.50),
              fontSize: 12,
              fontWeight: FontWeight.w600,
            ),
          ),
        ],
      ),
    );
  }
}

class _InfoCard extends StatelessWidget {
  const _InfoCard({required this.rows});
  final List<_InfoRow> rows;

  @override
  Widget build(BuildContext context) {
    return MobileSurfaceCard(
      padding: const EdgeInsets.symmetric(horizontal: 18, vertical: 6),
      child: Column(
        children: [
          for (var i = 0; i < rows.length; i++) ...[
            _InfoRowTile(row: rows[i]),
            if (i != rows.length - 1)
              Divider(height: 1, thickness: 1, color: AppTheme.border),
          ],
        ],
      ),
    );
  }
}

class _InfoRow {
  const _InfoRow(this.label, this.value, [this.icon]);
  final String label;
  final String value;
  final IconData? icon;
}

class _InfoRowTile extends StatelessWidget {
  const _InfoRowTile({required this.row});
  final _InfoRow row;

  @override
  Widget build(BuildContext context) {
    final value = row.value.trim().isEmpty ? '—' : row.value;
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 12),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          if (row.icon != null) ...[
            Container(
              width: 28,
              height: 28,
              decoration: BoxDecoration(
                color: AppTheme.primarySoft,
                borderRadius: BorderRadius.circular(8),
              ),
              child: Icon(row.icon, size: 13, color: AppTheme.primaryDark),
            ),
            const SizedBox(width: 10),
          ] else
            const SizedBox(width: 38),
          SizedBox(
            width: 100,
            child: Text(
              row.label,
              style: TextStyle(
                color: AppTheme.textSecondary,
                fontSize: 12.5,
                fontWeight: FontWeight.w700,
              ),
            ),
          ),
          const SizedBox(width: 12),
          Expanded(
            child: Text(
              value,
              style: TextStyle(
                color: AppTheme.textPrimary,
                fontSize: 13.5,
                fontWeight: FontWeight.w700,
              ),
            ),
          ),
        ],
      ),
    );
  }
}

class _SectionHeader extends StatelessWidget {
  const _SectionHeader({required this.icon, required this.title});

  final IconData icon;
  final String title;

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
          title.toUpperCase(),
          style: TextStyle(
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

class _PhotoSourceTile extends StatelessWidget {
  const _PhotoSourceTile({
    required this.icon,
    required this.title,
    required this.subtitle,
    required this.onTap,
  });

  final IconData icon;
  final String title;
  final String subtitle;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    return InkWell(
      onTap: onTap,
      borderRadius: BorderRadius.circular(16),
      child: Container(
        padding: const EdgeInsets.all(14),
        decoration: BoxDecoration(
          color: AppTheme.surfaceMuted,
          borderRadius: BorderRadius.circular(16),
        ),
        child: Row(
          children: [
            Container(
              width: 40,
              height: 40,
              decoration: BoxDecoration(
                color: AppTheme.primaryDark.withValues(alpha: 0.10),
                borderRadius: BorderRadius.circular(12),
              ),
              child: Icon(icon, color: AppTheme.primaryDark, size: 18),
            ),
            const SizedBox(width: 12),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    title,
                    style: TextStyle(
                      fontWeight: FontWeight.w900,
                      color: AppTheme.textPrimary,
                      fontSize: 14,
                    ),
                  ),
                  const SizedBox(height: 2),
                  Text(
                    subtitle,
                    style: TextStyle(
                      color: AppTheme.textSecondary,
                      fontSize: 12,
                    ),
                  ),
                ],
              ),
            ),
            Icon(
              PhosphorIconsBold.caretRight,
              size: 14,
              color: AppTheme.textMuted,
            ),
          ],
        ),
      ),
    );
  }
}

class _ProfileSkeleton extends StatelessWidget {
  const _ProfileSkeleton();

  @override
  Widget build(BuildContext context) {
    return Column(
      children: [
        SkeletonCard(
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              const SizedBox(height: 24),
              const Skeleton(width: 96, height: 96, radius: 48),
              const SizedBox(height: 14),
              const Skeleton(width: 180, height: 18, radius: 6),
              const SizedBox(height: 10),
              const Skeleton(width: 100, height: 14, radius: 6),
              const SizedBox(height: 8),
              const Skeleton(width: 200, height: 12, radius: 6),
              const SizedBox(height: 22),
            ],
          ),
        ),
        const SizedBox(height: 18),
        const Row(
          children: [
            Skeleton(width: 28, height: 28, radius: 8),
            SizedBox(width: 10),
            Skeleton(width: 120, height: 11, radius: 6),
          ],
        ),
        const SizedBox(height: 10),
        SkeletonCard(
          child: Column(
            children: List.generate(
              4,
              (_) => const Padding(
                padding: EdgeInsets.symmetric(vertical: 10),
                child: Row(
                  children: [
                    Skeleton(width: 28, height: 28, radius: 8),
                    SizedBox(width: 10),
                    Skeleton(width: 90, height: 12, radius: 6),
                    SizedBox(width: 12),
                    Expanded(child: Skeleton(height: 12, radius: 6)),
                  ],
                ),
              ),
            ),
          ),
        ),
      ],
    );
  }
}

class _EditField {
  const _EditField({
    required this.label,
    required this.controller,
    this.keyboardType = TextInputType.text,
  });
  final String label;
  final TextEditingController controller;
  final TextInputType keyboardType;
}

class _EditCard extends StatelessWidget {
  const _EditCard({required this.fields});

  final List<_EditField> fields;

  @override
  Widget build(BuildContext context) {
    return MobileSurfaceCard(
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: fields.map((field) {
          return Padding(
            padding: const EdgeInsets.only(bottom: 14),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  field.label,
                  style: TextStyle(
                    fontSize: 12,
                    fontWeight: FontWeight.w700,
                    color: AppTheme.textSecondary,
                  ),
                ),
                const SizedBox(height: 6),
                TextField(
                  controller: field.controller,
                  keyboardType: field.keyboardType,
                  style: TextStyle(
                    fontSize: 14,
                    fontWeight: FontWeight.w700,
                    color: AppTheme.textPrimary,
                  ),
                  decoration: const InputDecoration(
                    isDense: true,
                    contentPadding: EdgeInsets.symmetric(
                      horizontal: 12,
                      vertical: 12,
                    ),
                  ),
                ),
              ],
            ),
          );
        }).toList(),
      ),
    );
  }
}

class _ErrorCard extends StatelessWidget {
  const _ErrorCard({required this.message, required this.onRetry});
  final String message;
  final VoidCallback onRetry;

  @override
  Widget build(BuildContext context) {
    return MobileSurfaceCard(
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Container(
                width: 38,
                height: 38,
                decoration: BoxDecoration(
                  color: AppTheme.danger.withValues(alpha: 0.10),
                  borderRadius: BorderRadius.circular(12),
                ),
                child: const Icon(
                  PhosphorIconsBold.warningCircle,
                  color: AppTheme.danger,
                  size: 20,
                ),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: Text(
                  'Profile unavailable',
                  style: TextStyle(
                    fontWeight: FontWeight.w900,
                    color: AppTheme.textPrimary,
                    fontSize: 15,
                  ),
                ),
              ),
            ],
          ),
          const SizedBox(height: 10),
          Text(
            message,
            style: TextStyle(
              color: AppTheme.textSecondary,
              fontSize: 13,
              height: 1.4,
            ),
          ),
          const SizedBox(height: 14),
          Align(
            alignment: Alignment.centerRight,
            child: FilledButton(
              onPressed: onRetry,
              style: FilledButton.styleFrom(
                backgroundColor: AppTheme.primaryDark,
              ),
              child: const Text('Retry'),
            ),
          ),
        ],
      ),
    );
  }
}
