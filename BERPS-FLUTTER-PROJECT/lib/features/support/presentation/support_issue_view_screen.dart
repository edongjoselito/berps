import 'package:file_picker/file_picker.dart';
import 'package:flutter/foundation.dart';
import 'package:flutter/gestures.dart';
import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:image_picker/image_picker.dart';
import 'package:phosphor_flutter/phosphor_flutter.dart';
import 'package:url_launcher/url_launcher.dart';

import '../../../core/network/api_exception.dart';
import '../../../core/theme/app_theme.dart';
import '../../../core/utils/haptics.dart';
import '../../../core/utils/responsive.dart';
import '../../../core/widgets/animations.dart';
import '../../../core/widgets/app_toast.dart';
import '../../../core/widgets/mobile_header.dart';
import '../../../core/widgets/skeleton.dart';
import '../../auth/domain/staff_session.dart';
import '../../home/data/staff_api.dart';
import '../domain/support_issue.dart';

class SupportIssueViewScreen extends StatefulWidget {
  const SupportIssueViewScreen({
    super.key,
    required this.session,
    required this.issueId,
  });

  final StaffSession session;
  final int issueId;

  @override
  State<SupportIssueViewScreen> createState() => _SupportIssueViewScreenState();
}

class _SupportIssueViewScreenState extends State<SupportIssueViewScreen> {
  final StaffApi _api = StaffApi();
  final ImagePicker _imagePicker = ImagePicker();
  final TextEditingController _replyController = TextEditingController();
  final ScrollController _scrollController = ScrollController();
  Future<SupportIssueDetail>? _future;
  bool _sending = false;
  bool _closing = false;
  _SelectedSupportAttachment? _selectedAttachment;

  @override
  void initState() {
    super.initState();
    _reload();
  }

  @override
  void dispose() {
    _replyController.dispose();
    _scrollController.dispose();
    super.dispose();
  }

  void _reload() {
    setState(() {
      _future = _api.fetchSupportIssue(
        baseUrl: widget.session.baseUrl,
        token: widget.session.token,
        issueId: widget.issueId,
      );
    });
  }

  Future<void> _sendReply() async {
    final text = _replyController.text.trim();
    if ((text.isEmpty && _selectedAttachment == null) || _sending) return;

    setState(() => _sending = true);
    Haptics.medium();
    try {
      await _api.postSupportComment(
        baseUrl: widget.session.baseUrl,
        token: widget.session.token,
        issueId: widget.issueId,
        comment: text,
        attachmentName: _selectedAttachment?.name,
        attachmentBytes: _selectedAttachment?.bytes,
      );
      if (!mounted) return;
      _replyController.clear();
      setState(() => _selectedAttachment = null);
      AppToast.success(context, 'Reply posted.');
      _reload();
      Future.delayed(const Duration(milliseconds: 350), () {
        if (_scrollController.hasClients) {
          _scrollController.animateTo(
            _scrollController.position.maxScrollExtent,
            duration: const Duration(milliseconds: 250),
            curve: Curves.easeOut,
          );
        }
      });
    } on ApiException catch (e) {
      if (!mounted) return;
      AppToast.error(context, e.message);
    } finally {
      if (mounted) setState(() => _sending = false);
    }
  }

  Future<void> _pickFileAttachment() async {
    Haptics.light();
    try {
      final result = await FilePicker.platform.pickFiles(
        withData: true,
        allowMultiple: false,
        type: FileType.custom,
        allowedExtensions: const [
          'pdf',
          'jpg',
          'jpeg',
          'png',
          'doc',
          'docx',
          'heic',
          'heif',
          'mp4',
          'mov',
          'avi',
          'webm',
          'm4v',
        ],
      );
      if (!mounted || result == null || result.files.isEmpty) return;
      final file = result.files.single;
      final bytes = file.bytes;
      if (bytes == null || bytes.isEmpty) {
        AppToast.error(context, 'The selected file could not be read.');
        return;
      }
      setState(() {
        _selectedAttachment = _SelectedSupportAttachment(
          name: file.name,
          bytes: bytes,
        );
      });
    } catch (_) {
      if (!mounted) return;
      AppToast.error(
        context,
        'File upload is not available in this browser session.',
      );
    }
  }

  Future<void> _pickGalleryImage() async {
    await _pickImage(source: ImageSource.gallery);
  }

  Future<void> _capturePhoto() async {
    await _pickImage(source: ImageSource.camera);
  }

  Future<void> _captureVideo() async {
    Haptics.light();
    try {
      final file = await _imagePicker.pickVideo(
        source: ImageSource.camera,
        preferredCameraDevice: CameraDevice.rear,
        maxDuration: const Duration(minutes: 5),
      );
      await _attachXFile(file);
    } on PlatformException catch (error) {
      if (!mounted) return;
      AppToast.error(
        context,
        error.message ??
            'Video capture is unavailable. Check camera and microphone access.',
      );
    } catch (_) {
      if (!mounted) return;
      AppToast.error(context, 'Video capture is unavailable right now.');
    }
  }

  Future<void> _pickImage({required ImageSource source}) async {
    Haptics.light();
    try {
      final file = await _imagePicker.pickImage(
        source: source,
        preferredCameraDevice: CameraDevice.rear,
        imageQuality: 92,
      );
      await _attachXFile(file);
    } on PlatformException catch (error) {
      if (!mounted) return;
      AppToast.error(
        context,
        error.message ??
            'Camera or photo library access is unavailable. Check device permissions.',
      );
    } catch (_) {
      if (!mounted) return;
      AppToast.error(context, 'Camera or photo library access is unavailable.');
    }
  }

  Future<void> _attachXFile(XFile? file) async {
    if (!mounted || file == null) return;
    final bytes = await file.readAsBytes();
    if (!mounted) return;
    setState(() {
      _selectedAttachment = _SelectedSupportAttachment(
        name: file.name.isNotEmpty ? file.name : 'attachment',
        bytes: bytes,
      );
    });
  }

  Future<void> _closeTicket(SupportIssue issue) async {
    if (_closing || issue.isClosed) return;
    final controller = TextEditingController();
    final confirmed = await showModalBottomSheet<bool>(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (sheetContext) {
        return _CloseTicketSheet(controller: controller);
      },
    );
    final message = controller.text.trim();
    controller.dispose();

    if (confirmed != true || message.isEmpty || !mounted) return;

    setState(() => _closing = true);
    Haptics.medium();
    try {
      await _api.closeSupportIssue(
        baseUrl: widget.session.baseUrl,
        token: widget.session.token,
        issueId: widget.issueId,
        closeMessage: message,
      );
      if (!mounted) return;
      AppToast.success(context, 'Ticket closed.');
      _reload();
    } on ApiException catch (e) {
      if (!mounted) return;
      AppToast.error(context, e.message);
    } finally {
      if (mounted) {
        setState(() => _closing = false);
      }
    }
  }

  Future<void> _assignToMe(SupportIssueDetail detail) async {
    if (_closing || detail.currentUserId <= 0) return;
    Haptics.medium();
    try {
      await _api.forwardSupportIssue(
        baseUrl: widget.session.baseUrl,
        token: widget.session.token,
        issueId: widget.issueId,
        forwardTo: detail.currentUserId,
      );
      if (!mounted) return;
      AppToast.success(context, 'Ticket assigned to you.');
      _reload();
    } on ApiException catch (e) {
      if (!mounted) return;
      AppToast.error(context, e.message);
    }
  }

  Future<void> _forwardIssue(SupportIssueDetail detail) async {
    if (detail.assignableUsers.isEmpty) {
      AppToast.warning(
        context,
        'No staff available to forward this ticket to.',
      );
      return;
    }

    int? selectedUserId;
    final noteController = TextEditingController();
    final confirmed = await showModalBottomSheet<bool>(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (_) => _ForwardIssueSheet(
        users: detail.assignableUsers,
        currentUserId: detail.currentUserId,
        noteController: noteController,
        onChanged: (value) => selectedUserId = value,
      ),
    );
    final note = noteController.text.trim();
    noteController.dispose();

    if (confirmed != true || selectedUserId == null || !mounted) return;

    Haptics.medium();
    try {
      await _api.forwardSupportIssue(
        baseUrl: widget.session.baseUrl,
        token: widget.session.token,
        issueId: widget.issueId,
        forwardTo: selectedUserId!,
        note: note,
      );
      if (!mounted) return;
      AppToast.success(context, 'Ticket forwarded.');
      _reload();
    } on ApiException catch (e) {
      if (!mounted) return;
      AppToast.error(context, e.message);
    }
  }

  Future<void> _tagIssue(SupportIssueDetail detail) async {
    if (detail.taggableUsers.isEmpty) {
      AppToast.warning(context, 'No staff available to tag.');
      return;
    }

    final selectedIds = <int>{};
    final noteController = TextEditingController();
    final confirmed = await showModalBottomSheet<bool>(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (_) => _TagIssueSheet(
        users: detail.taggableUsers,
        currentUserId: detail.currentUserId,
        noteController: noteController,
        selectedIds: selectedIds,
      ),
    );
    final note = noteController.text.trim();
    noteController.dispose();

    if (confirmed != true || selectedIds.isEmpty || !mounted) return;

    Haptics.medium();
    try {
      await _api.tagSupportIssue(
        baseUrl: widget.session.baseUrl,
        token: widget.session.token,
        issueId: widget.issueId,
        tagUserIds: selectedIds.toList(),
        note: note,
      );
      if (!mounted) return;
      AppToast.success(context, 'Tagged successfully.');
      _reload();
    } on ApiException catch (e) {
      if (!mounted) return;
      AppToast.error(context, e.message);
    }
  }

  Future<void> _openAttachment(String rawPath) async {
    final uri = _resolveUri(rawPath);
    if (uri == null) {
      AppToast.error(context, 'Attachment link is invalid.');
      return;
    }
    final launched = await launchUrl(uri, webOnlyWindowName: '_blank');
    if (!launched && mounted) {
      AppToast.error(context, 'Could not open the attachment.');
    }
  }

  Uri? _resolveUri(String rawPath) {
    final trimmed = rawPath.trim();
    if (trimmed.isEmpty) return null;
    final parsed = Uri.tryParse(trimmed);
    if (parsed != null && parsed.hasScheme) {
      return parsed;
    }

    final base = widget.session.baseUrl.replaceFirst(RegExp(r'/+$'), '');
    final normalizedPath = trimmed.startsWith('/')
        ? trimmed.substring(1)
        : trimmed;
    return Uri.tryParse('$base/$normalizedPath');
  }

  Future<void> _openAttachmentMenu() async {
    Haptics.light();
    final action = await showModalBottomSheet<_AttachmentAction>(
      context: context,
      backgroundColor: Colors.transparent,
      builder: (_) => const _AttachmentActionSheet(),
    );

    if (!mounted || action == null) return;

    switch (action) {
      case _AttachmentAction.file:
        await _pickFileAttachment();
        break;
      case _AttachmentAction.photo:
        await _pickGalleryImage();
        break;
      case _AttachmentAction.camera:
        await _capturePhoto();
        break;
      case _AttachmentAction.video:
        await _captureVideo();
        break;
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppTheme.background,
      body: FutureBuilder<SupportIssueDetail>(
        future: _future,
        builder: (context, snapshot) {
          return Column(
            children: [
              SafeArea(
                bottom: false,
                child: Padding(
                  padding: EdgeInsets.fromLTRB(
                    context.gutter,
                    12,
                    context.gutter,
                    8,
                  ),
                  child: MobileHeader(
                    title: snapshot.data?.issue.title.isNotEmpty == true
                        ? snapshot.data!.issue.title
                        : 'Support ticket',
                    subtitle:
                        snapshot.data?.issue.ticketNumber.isNotEmpty == true
                        ? snapshot.data!.issue.ticketNumber
                        : 'Ticket details',
                    leadingIcon: PhosphorIconsBold.arrowLeft,
                    onLeadingTap: () => Navigator.of(context).maybePop(),
                  ),
                ),
              ),
              Expanded(
                child: () {
                  if (snapshot.connectionState == ConnectionState.waiting) {
                    return const _LoadingState();
                  }
                  if (snapshot.hasError) {
                    return _ErrorState(
                      message: snapshot.error is ApiException
                          ? (snapshot.error as ApiException).message
                          : snapshot.error.toString(),
                      onRetry: _reload,
                    );
                  }
                  if (!snapshot.hasData) {
                    return _ErrorState(
                      message: 'Ticket unavailable.',
                      onRetry: _reload,
                    );
                  }
                  return _ChatBody(
                    detail: snapshot.data!,
                    scrollController: _scrollController,
                    onAssignToMe: () => _assignToMe(snapshot.data!),
                    onForward: () => _forwardIssue(snapshot.data!),
                    onTag: () => _tagIssue(snapshot.data!),
                    onCloseTicket: () => _closeTicket(snapshot.data!.issue),
                    onOpenAttachment: _openAttachment,
                    closing: _closing,
                  );
                }(),
              ),
              if (snapshot.data?.canReplyChat == true)
                _ReplyComposer(
                  controller: _replyController,
                  sending: _sending,
                  onSend: _sendReply,
                  attachment: _selectedAttachment,
                  onRemoveAttachment: () =>
                      setState(() => _selectedAttachment = null),
                  onOpenAttachmentMenu: _openAttachmentMenu,
                ),
              if (snapshot.data?.canReplyChat == false && snapshot.data != null)
                Container(
                  padding: EdgeInsets.fromLTRB(
                    context.gutter,
                    10,
                    context.gutter,
                    MediaQuery.of(context).padding.bottom + 10,
                  ),
                  decoration: BoxDecoration(
                    color: AppTheme.surface,
                    border: Border(top: BorderSide(color: AppTheme.border)),
                  ),
                  child: Row(
                    children: [
                      Icon(
                        PhosphorIconsBold.lock,
                        size: 13,
                        color: AppTheme.textMuted,
                      ),
                      SizedBox(width: 8),
                      Expanded(
                        child: Text(
                          'You can view this chat but cannot reply.',
                          style: TextStyle(
                            color: AppTheme.textSecondary,
                            fontSize: 12,
                            fontWeight: FontWeight.w600,
                          ),
                        ),
                      ),
                    ],
                  ),
                ),
            ],
          );
        },
      ),
    );
  }
}

// ── Chat body ──────────────────────────────────────────────────────────────

class _ChatBody extends StatelessWidget {
  const _ChatBody({
    required this.detail,
    required this.scrollController,
    required this.onAssignToMe,
    required this.onForward,
    required this.onTag,
    required this.onCloseTicket,
    required this.onOpenAttachment,
    required this.closing,
  });

  final SupportIssueDetail detail;
  final ScrollController scrollController;
  final VoidCallback onAssignToMe;
  final VoidCallback onForward;
  final VoidCallback onTag;
  final VoidCallback onCloseTicket;
  final ValueChanged<String> onOpenAttachment;
  final bool closing;

  @override
  Widget build(BuildContext context) {
    return ListView(
      controller: scrollController,
      padding: EdgeInsets.fromLTRB(context.gutter, 8, context.gutter, 18),
      children: [
        FadeSlide(
          delay: const Duration(milliseconds: 60),
          child: _IssueSummaryCard(issue: detail.issue),
        ),
        const SizedBox(height: 12),
        FadeSlide(
          delay: const Duration(milliseconds: 100),
          child: _IssueActionsCard(
            detail: detail,
            closing: closing,
            onAssignToMe: onAssignToMe,
            onForward: onForward,
            onTag: onTag,
            onCloseTicket: onCloseTicket,
          ),
        ),
        const SizedBox(height: 14),
        _SectionHeader(
          icon: PhosphorIconsBold.chatTeardropText,
          title: 'Conversation',
          count: detail.canViewChat ? detail.comments.length : 0,
        ),
        const SizedBox(height: 10),
        if (!detail.canViewChat)
          const _ChatLocked()
        else if (detail.comments.isEmpty)
          const _ChatEmpty()
        else
          _ConversationCard(
            comments: detail.comments,
            onOpenAttachment: onOpenAttachment,
          ),
      ],
    );
  }
}

class _ConversationCard extends StatelessWidget {
  const _ConversationCard({
    required this.comments,
    required this.onOpenAttachment,
  });

  final List<SupportComment> comments;
  final ValueChanged<String> onOpenAttachment;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: AppTheme.surface,
        borderRadius: BorderRadius.circular(18),
        border: Border.all(color: AppTheme.border),
        boxShadow: AppTheme.shadowSoft,
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Icon(
                PhosphorIconsBold.chatsCircle,
                size: 16,
                color: AppTheme.primaryDark,
              ),
              SizedBox(width: 8),
              Text(
                'Conversation',
                style: TextStyle(
                  fontSize: 15,
                  fontWeight: FontWeight.w900,
                  color: AppTheme.textPrimary,
                ),
              ),
            ],
          ),
          const SizedBox(height: 4),
          Text(
            '${comments.length} message${comments.length == 1 ? '' : 's'}',
            style: TextStyle(
              color: AppTheme.textSecondary,
              fontSize: 12,
              fontWeight: FontWeight.w600,
            ),
          ),
          const SizedBox(height: 14),
          for (var i = 0; i < comments.length; i++) ...[
            _CommentBubble(
              comment: comments[i],
              onOpenAttachment: onOpenAttachment,
            ),
            if (i < comments.length - 1) const SizedBox(height: 14),
          ],
        ],
      ),
    );
  }
}

class _IssueSummaryCard extends StatefulWidget {
  const _IssueSummaryCard({required this.issue});
  final SupportIssue issue;

  @override
  State<_IssueSummaryCard> createState() => _IssueSummaryCardState();
}

class _IssueSummaryCardState extends State<_IssueSummaryCard> {
  bool _expanded = false;

  Color get _statusColor {
    if (widget.issue.isClosed) return AppTheme.success;
    if (widget.issue.isUnassigned) return AppTheme.warning;
    return AppTheme.primaryDark;
  }

  String get _statusLabel {
    if (widget.issue.isClosed) return 'CLOSED';
    if (widget.issue.isUnassigned) return 'UNASSIGNED';
    return widget.issue.status.toUpperCase();
  }

  Color get _priorityColor {
    switch (widget.issue.priority.toLowerCase()) {
      case 'urgent':
      case 'high':
        return AppTheme.danger;
      case 'low':
        return AppTheme.success;
      case 'medium':
      default:
        return AppTheme.warning;
    }
  }

  void _toggle() {
    Haptics.light();
    setState(() => _expanded = !_expanded);
  }

  @override
  Widget build(BuildContext context) {
    final issue = widget.issue;
    return Container(
      decoration: BoxDecoration(
        color: AppTheme.surface,
        borderRadius: BorderRadius.circular(18),
        border: Border.all(color: AppTheme.border),
        boxShadow: AppTheme.shadowSoft,
      ),
      clipBehavior: Clip.antiAlias,
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          InkWell(
            onTap: _toggle,
            child: Padding(
              padding: const EdgeInsets.all(14),
              child: Row(
                children: [
                  Container(
                    width: 36,
                    height: 36,
                    decoration: BoxDecoration(
                      color: _priorityColor.withValues(alpha: 0.10),
                      borderRadius: BorderRadius.circular(12),
                    ),
                    child: Icon(
                      PhosphorIconsFill.lifebuoy,
                      color: _priorityColor,
                      size: 18,
                    ),
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Row(
                          children: [
                            Expanded(
                              child: Text(
                                issue.customerName.isEmpty
                                    ? '—'
                                    : issue.customerName,
                                maxLines: 1,
                                overflow: TextOverflow.ellipsis,
                                style: TextStyle(
                                  fontWeight: FontWeight.w900,
                                  color: AppTheme.textPrimary,
                                  fontSize: 13.5,
                                ),
                              ),
                            ),
                            const SizedBox(width: 6),
                            Container(
                              padding: const EdgeInsets.symmetric(
                                horizontal: 8,
                                vertical: 4,
                              ),
                              decoration: BoxDecoration(
                                color: _statusColor.withValues(alpha: 0.10),
                                borderRadius: BorderRadius.circular(999),
                              ),
                              child: Text(
                                _statusLabel,
                                style: TextStyle(
                                  color: _statusColor,
                                  fontWeight: FontWeight.w900,
                                  fontSize: 9.5,
                                  letterSpacing: 0.8,
                                ),
                              ),
                            ),
                          ],
                        ),
                        const SizedBox(height: 2),
                        Text(
                          _expanded
                              ? 'Hide ticket details'
                              : 'Show ticket details',
                          style: TextStyle(
                            color: AppTheme.textSecondary,
                            fontSize: 11.5,
                            fontWeight: FontWeight.w700,
                          ),
                        ),
                      ],
                    ),
                  ),
                  const SizedBox(width: 8),
                  AnimatedRotation(
                    duration: const Duration(milliseconds: 220),
                    turns: _expanded ? 0.5 : 0,
                    child: Icon(
                      PhosphorIconsBold.caretDown,
                      size: 14,
                      color: AppTheme.textSecondary,
                    ),
                  ),
                ],
              ),
            ),
          ),
          AnimatedSize(
            duration: const Duration(milliseconds: 220),
            curve: Curves.easeOut,
            alignment: Alignment.topCenter,
            child: _expanded
                ? Padding(
                    padding: const EdgeInsets.fromLTRB(14, 0, 14, 14),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Divider(height: 1, color: AppTheme.border),
                        const SizedBox(height: 12),
                        if (issue.customerEmail.isNotEmpty)
                          Padding(
                            padding: const EdgeInsets.only(bottom: 10),
                            child: Row(
                              children: [
                                Icon(
                                  PhosphorIconsBold.envelopeSimple,
                                  size: 13,
                                  color: AppTheme.textSecondary,
                                ),
                                const SizedBox(width: 6),
                                Expanded(
                                  child: Text(
                                    issue.customerEmail,
                                    maxLines: 1,
                                    overflow: TextOverflow.ellipsis,
                                    style: TextStyle(
                                      color: AppTheme.textSecondary,
                                      fontSize: 12,
                                      fontWeight: FontWeight.w600,
                                    ),
                                  ),
                                ),
                              ],
                            ),
                          ),
                        if (issue.description.isNotEmpty) ...[
                          Container(
                            padding: const EdgeInsets.all(12),
                            decoration: BoxDecoration(
                              color: AppTheme.surfaceMuted,
                              borderRadius: BorderRadius.circular(12),
                            ),
                            child: Text(
                              issue.description,
                              style: TextStyle(
                                color: AppTheme.textPrimary,
                                fontSize: 12.5,
                                height: 1.45,
                              ),
                            ),
                          ),
                          const SizedBox(height: 12),
                        ],
                        Wrap(
                          spacing: 6,
                          runSpacing: 6,
                          children: [
                            if (issue.departmentName.isNotEmpty)
                              _SummaryPill(
                                icon: PhosphorIconsBold.buildingOffice,
                                label: issue.departmentName,
                              ),
                            if (issue.projectName.isNotEmpty)
                              _SummaryPill(
                                icon: PhosphorIconsBold.folder,
                                label: issue.projectName,
                              ),
                            _SummaryPill(
                              icon: PhosphorIconsBold.flag,
                              label: issue.priority.toUpperCase(),
                              accent: _priorityColor,
                            ),
                            if (issue.assignedName.isNotEmpty)
                              _SummaryPill(
                                icon: PhosphorIconsBold.user,
                                label: issue.assignedToMe
                                    ? 'Assigned to you'
                                    : issue.assignedName,
                                accent: issue.assignedToMe
                                    ? AppTheme.success
                                    : null,
                              ),
                            if (issue.createdLabel.isNotEmpty)
                              _SummaryPill(
                                icon: PhosphorIconsBold.clock,
                                label: issue.createdLabel,
                              ),
                          ],
                        ),
                      ],
                    ),
                  )
                : const SizedBox(width: double.infinity),
          ),
        ],
      ),
    );
  }
}

class _IssueActionsCard extends StatelessWidget {
  const _IssueActionsCard({
    required this.detail,
    required this.closing,
    required this.onAssignToMe,
    required this.onForward,
    required this.onTag,
    required this.onCloseTicket,
  });

  final SupportIssueDetail detail;
  final bool closing;
  final VoidCallback onAssignToMe;
  final VoidCallback onForward;
  final VoidCallback onTag;
  final VoidCallback onCloseTicket;

  @override
  Widget build(BuildContext context) {
    final issue = detail.issue;
    final actions = <Widget>[];

    if (issue.isUnassigned && !issue.isClosed) {
      actions.add(
        _ActionChip(
          icon: PhosphorIconsBold.userPlus,
          label: 'Assign to me',
          accent: AppTheme.success,
          onTap: onAssignToMe,
        ),
      );
    }
    if (!issue.isClosed) {
      actions.add(
        _ActionChip(
          icon: PhosphorIconsBold.arrowBendUpRight,
          label: 'Forward',
          accent: AppTheme.warning,
          onTap: onForward,
        ),
      );
      actions.add(
        _ActionChip(
          icon: PhosphorIconsBold.usersThree,
          label: 'Tag',
          accent: AppTheme.primaryDark,
          onTap: onTag,
        ),
      );
      actions.add(
        _ActionChip(
          icon: closing
              ? PhosphorIconsBold.arrowsClockwise
              : PhosphorIconsBold.checkCircle,
          label: closing ? 'Closing…' : 'Close',
          accent: AppTheme.danger,
          onTap: closing ? null : onCloseTicket,
        ),
      );
    }

    if (actions.isEmpty) {
      return const SizedBox.shrink();
    }

    return Container(
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        color: AppTheme.surface,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: AppTheme.border),
        boxShadow: AppTheme.shadowSoft,
      ),
      child: Wrap(spacing: 8, runSpacing: 8, children: actions),
    );
  }
}

class _SummaryPill extends StatelessWidget {
  const _SummaryPill({required this.icon, required this.label, this.accent});
  final IconData icon;
  final String label;
  final Color? accent;

  @override
  Widget build(BuildContext context) {
    final color = accent ?? AppTheme.textSecondary;
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
      decoration: BoxDecoration(
        color: AppTheme.surfaceMuted,
        borderRadius: BorderRadius.circular(999),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Icon(icon, size: 11, color: color),
          const SizedBox(width: 5),
          Text(
            label,
            style: TextStyle(
              fontSize: 11,
              fontWeight: FontWeight.w700,
              color: AppTheme.textPrimary,
            ),
          ),
        ],
      ),
    );
  }
}

class _ActionChip extends StatelessWidget {
  const _ActionChip({
    required this.icon,
    required this.label,
    required this.accent,
    required this.onTap,
  });

  final IconData icon;
  final String label;
  final Color accent;
  final VoidCallback? onTap;

  @override
  Widget build(BuildContext context) {
    return PressScale(
      onTap: onTap,
      child: AnimatedOpacity(
        duration: const Duration(milliseconds: 180),
        opacity: onTap == null ? 0.55 : 1,
        child: Container(
          padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 9),
          decoration: BoxDecoration(
            color: accent.withValues(alpha: 0.10),
            borderRadius: BorderRadius.circular(12),
            border: Border.all(color: accent.withValues(alpha: 0.20)),
          ),
          child: Row(
            mainAxisSize: MainAxisSize.min,
            children: [
              Icon(icon, size: 13, color: accent),
              const SizedBox(width: 7),
              Text(
                label,
                style: TextStyle(
                  color: accent,
                  fontWeight: FontWeight.w800,
                  fontSize: 11.5,
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}

class _CommentBubble extends StatelessWidget {
  const _CommentBubble({required this.comment, required this.onOpenAttachment});
  final SupportComment comment;
  final ValueChanged<String> onOpenAttachment;

  String get _cleanedBody {
    final lines = comment.comment.split('\n');
    final filtered = lines.where((line) {
      final trimmed = line.trimLeft();
      if (trimmed.startsWith('Project:')) return false;
      if (trimmed.startsWith('Reference Link:')) return false;
      return true;
    });
    return filtered.join('\n').trim();
  }

  @override
  Widget build(BuildContext context) {
    final isMine = comment.isMine;
    final isCustomer = comment.isCustomer;
    final body = _cleanedBody;

    final align = isMine ? CrossAxisAlignment.end : CrossAxisAlignment.start;

    final bubbleColor = isMine
        ? AppTheme.primaryDark
        : (isCustomer ? AppTheme.accentSoft : AppTheme.surfaceMuted);
    final textColor = isMine ? Colors.white : AppTheme.textPrimary;
    final borderColor = isMine
        ? AppTheme.primaryDark
        : (isCustomer
              ? AppTheme.accent.withValues(alpha: 0.35)
              : AppTheme.border);

    return Column(
      crossAxisAlignment: align,
      children: [
        Row(
          mainAxisAlignment: isMine
              ? MainAxisAlignment.end
              : MainAxisAlignment.start,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            if (!isMine) ...[
              _Avatar(name: comment.authorName, isCustomer: isCustomer),
              const SizedBox(width: 8),
            ],
            Flexible(
              child: Container(
                constraints: BoxConstraints(
                  maxWidth: MediaQuery.of(context).size.width * 0.78,
                ),
                padding: const EdgeInsets.symmetric(
                  horizontal: 14,
                  vertical: 12,
                ),
                decoration: BoxDecoration(
                  color: bubbleColor,
                  borderRadius: BorderRadius.only(
                    topLeft: const Radius.circular(18),
                    topRight: const Radius.circular(18),
                    bottomLeft: Radius.circular(isMine ? 18 : 6),
                    bottomRight: Radius.circular(isMine ? 6 : 18),
                  ),
                  border: Border.all(color: borderColor),
                  boxShadow: isMine
                      ? [
                          BoxShadow(
                            color: AppTheme.primaryDark.withValues(alpha: 0.18),
                            blurRadius: 12,
                            offset: const Offset(0, 6),
                          ),
                        ]
                      : null,
                ),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Row(
                      children: [
                        Expanded(
                          child: Text(
                            isMine ? 'You' : comment.authorName,
                            maxLines: 1,
                            overflow: TextOverflow.ellipsis,
                            style: TextStyle(
                              fontSize: 11.5,
                              fontWeight: FontWeight.w900,
                              color: isMine
                                  ? Colors.white.withValues(alpha: 0.95)
                                  : AppTheme.textPrimary,
                            ),
                          ),
                        ),
                        if (comment.createdLabel.isNotEmpty) ...[
                          const SizedBox(width: 8),
                          Text(
                            comment.createdLabel,
                            style: TextStyle(
                              fontSize: 10.5,
                              color: isMine
                                  ? Colors.white.withValues(alpha: 0.75)
                                  : AppTheme.textMuted,
                              fontWeight: FontWeight.w700,
                            ),
                          ),
                        ],
                      ],
                    ),
                    if (isCustomer || comment.isInternal) ...[
                      const SizedBox(height: 6),
                      Wrap(
                        spacing: 6,
                        runSpacing: 6,
                        children: [
                          if (isCustomer)
                            _CommentTag(
                              label: 'Customer',
                              color: AppTheme.warning,
                              dark: isMine,
                            ),
                          if (comment.isInternal)
                            _CommentTag(
                              label: 'Internal',
                              color: AppTheme.danger,
                              dark: isMine,
                            ),
                        ],
                      ),
                    ],
                    if (body.isNotEmpty) ...[
                      const SizedBox(height: 8),
                      _LinkifiedText(
                        text: body,
                        color: textColor,
                        linkColor: isMine ? Colors.white : AppTheme.primary,
                      ),
                    ],
                    if (comment.attachmentPath.isNotEmpty) ...[
                      if (body.isNotEmpty) const SizedBox(height: 10),
                      _AttachmentChip(
                        label: _fileNameFromPath(comment.attachmentPath),
                        onTap: () => onOpenAttachment(comment.attachmentPath),
                        dark: isMine,
                      ),
                    ],
                  ],
                ),
              ),
            ),
          ],
        ),
      ],
    );
  }
}

class _CommentTag extends StatelessWidget {
  const _CommentTag({
    required this.label,
    required this.color,
    required this.dark,
  });

  final String label;
  final Color color;
  final bool dark;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 2),
      decoration: BoxDecoration(
        color: dark
            ? Colors.white.withValues(alpha: 0.12)
            : color.withValues(alpha: 0.12),
        borderRadius: BorderRadius.circular(999),
      ),
      child: Text(
        label.toUpperCase(),
        style: TextStyle(
          color: dark ? Colors.white : color,
          fontWeight: FontWeight.w900,
          fontSize: 8.5,
          letterSpacing: 0.7,
        ),
      ),
    );
  }
}

class _LinkifiedText extends StatelessWidget {
  const _LinkifiedText({
    required this.text,
    required this.color,
    required this.linkColor,
  });

  final String text;
  final Color color;
  final Color linkColor;

  static final RegExp _urlRegex = RegExp(
    r'((?:https?:\/\/|www\.)[^\s<>"]+|[\w.\-+]+@[\w\-]+(?:\.[\w\-]+)+)',
    caseSensitive: false,
  );

  Future<void> _open(String raw) async {
    var target = raw;
    if (target.contains('@') && !target.startsWith('mailto:')) {
      target = 'mailto:$target';
    } else if (target.startsWith('www.')) {
      target = 'https://$target';
    }
    final uri = Uri.tryParse(target);
    if (uri == null) return;
    await launchUrl(uri, webOnlyWindowName: '_blank');
  }

  @override
  Widget build(BuildContext context) {
    final baseStyle = TextStyle(color: color, fontSize: 13, height: 1.4);
    final linkStyle = baseStyle.copyWith(
      color: linkColor,
      decoration: TextDecoration.underline,
      decorationColor: linkColor,
      fontWeight: FontWeight.w700,
    );

    final spans = <InlineSpan>[];
    var lastEnd = 0;
    for (final match in _urlRegex.allMatches(text)) {
      if (match.start > lastEnd) {
        spans.add(TextSpan(text: text.substring(lastEnd, match.start)));
      }
      final raw = match.group(0)!;
      // Strip trailing punctuation that's typically not part of the URL.
      final trailing = RegExp(r'[\.,;:!\?\)\]\}>]+$');
      final trimMatch = trailing.firstMatch(raw);
      final urlPart = trimMatch != null
          ? raw.substring(0, trimMatch.start)
          : raw;
      final tail = trimMatch != null ? raw.substring(trimMatch.start) : '';

      spans.add(
        TextSpan(
          text: urlPart,
          style: linkStyle,
          recognizer: TapGestureRecognizer()..onTap = () => _open(urlPart),
        ),
      );
      if (tail.isNotEmpty) {
        spans.add(TextSpan(text: tail));
      }
      lastEnd = match.end;
    }
    if (lastEnd < text.length) {
      spans.add(TextSpan(text: text.substring(lastEnd)));
    }
    if (spans.isEmpty) {
      spans.add(TextSpan(text: text));
    }

    return SelectableText.rich(TextSpan(style: baseStyle, children: spans));
  }
}

String _fileNameFromPath(String value) {
  final normalized = value.replaceAll('\\', '/');
  final index = normalized.lastIndexOf('/');
  if (index < 0 || index == normalized.length - 1) {
    return normalized;
  }
  return normalized.substring(index + 1);
}

class _Avatar extends StatelessWidget {
  const _Avatar({required this.name, required this.isCustomer});
  final String name;
  final bool isCustomer;

  String get _initials {
    final trimmed = name.trim();
    if (trimmed.isEmpty) return '?';
    final parts = trimmed.split(RegExp(r'\s+'));
    final first = parts.first.isNotEmpty ? parts.first[0] : '';
    final last = parts.length > 1 && parts.last.isNotEmpty ? parts.last[0] : '';
    final out = (first + last).toUpperCase();
    return out.isEmpty ? trimmed[0].toUpperCase() : out;
  }

  @override
  Widget build(BuildContext context) {
    return Container(
      width: 18,
      height: 18,
      decoration: BoxDecoration(
        gradient: isCustomer
            ? const LinearGradient(
                colors: [Color(0xFFFFB347), Color(0xFFD97706)],
                begin: Alignment.topLeft,
                end: Alignment.bottomRight,
              )
            : const LinearGradient(
                colors: [AppTheme.primary, AppTheme.primaryDark],
                begin: Alignment.topLeft,
                end: Alignment.bottomRight,
              ),
        borderRadius: BorderRadius.circular(6),
      ),
      alignment: Alignment.center,
      child: Text(
        _initials,
        style: const TextStyle(
          color: Colors.white,
          fontSize: 8,
          fontWeight: FontWeight.w900,
          letterSpacing: 0.2,
        ),
      ),
    );
  }
}

class _SectionHeader extends StatelessWidget {
  const _SectionHeader({
    required this.icon,
    required this.title,
    required this.count,
  });

  final IconData icon;
  final String title;
  final int count;

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
        const SizedBox(width: 8),
        Container(
          padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
          decoration: BoxDecoration(
            color: AppTheme.primarySoft,
            borderRadius: BorderRadius.circular(10),
          ),
          child: Text(
            count.toString(),
            style: const TextStyle(
              fontSize: 11,
              fontWeight: FontWeight.w900,
              color: AppTheme.primaryDark,
            ),
          ),
        ),
      ],
    );
  }
}

class _ChatLocked extends StatelessWidget {
  const _ChatLocked();

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(24),
      decoration: BoxDecoration(
        color: AppTheme.surface,
        borderRadius: BorderRadius.circular(18),
        border: Border.all(color: AppTheme.border),
      ),
      child: Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          Container(
            width: 56,
            height: 56,
            decoration: BoxDecoration(
              color: AppTheme.warning.withValues(alpha: 0.12),
              shape: BoxShape.circle,
            ),
            child: const Icon(
              PhosphorIconsBold.lock,
              size: 26,
              color: AppTheme.warning,
            ),
          ),
          const SizedBox(height: 14),
          Text(
            'Access restricted',
            style: TextStyle(
              fontWeight: FontWeight.w900,
              color: AppTheme.textPrimary,
              fontSize: 15,
            ),
          ),
          const SizedBox(height: 6),
          Text(
            'You do not have permission to view this conversation.',
            textAlign: TextAlign.center,
            style: TextStyle(
              color: AppTheme.textSecondary,
              fontSize: 13,
              height: 1.4,
            ),
          ),
        ],
      ),
    );
  }
}

class _ChatEmpty extends StatelessWidget {
  const _ChatEmpty();

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(24),
      decoration: BoxDecoration(
        color: AppTheme.surface,
        borderRadius: BorderRadius.circular(18),
        border: Border.all(color: AppTheme.border),
      ),
      child: Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          Container(
            width: 56,
            height: 56,
            decoration: BoxDecoration(
              color: AppTheme.primarySoft,
              shape: BoxShape.circle,
            ),
            child: const Icon(
              PhosphorIconsBold.chatTeardropText,
              size: 26,
              color: AppTheme.primaryDark,
            ),
          ),
          const SizedBox(height: 14),
          Text(
            'No messages yet',
            style: TextStyle(
              fontWeight: FontWeight.w900,
              color: AppTheme.textPrimary,
              fontSize: 15,
            ),
          ),
          const SizedBox(height: 6),
          Text(
            'Start the conversation with the customer.',
            textAlign: TextAlign.center,
            style: TextStyle(
              color: AppTheme.textSecondary,
              fontSize: 13,
              height: 1.4,
            ),
          ),
        ],
      ),
    );
  }
}

class _LoadingState extends StatelessWidget {
  const _LoadingState();

  @override
  Widget build(BuildContext context) {
    return ListView(
      padding: EdgeInsets.fromLTRB(context.gutter, 8, context.gutter, 18),
      children: [
        SkeletonCard(
          radius: 18,
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: const [
              Skeleton(height: 16, width: 200),
              SizedBox(height: 8),
              Skeleton(height: 12, width: 140),
              SizedBox(height: 12),
              Skeleton(height: 60, radius: 12),
            ],
          ),
        ),
        const SizedBox(height: 12),
        const Skeleton(height: 56, radius: 16),
        const SizedBox(height: 8),
        const Skeleton(height: 80, radius: 16),
        const SizedBox(height: 8),
        const Skeleton(height: 56, radius: 16),
      ],
    );
  }
}

class _ErrorState extends StatelessWidget {
  const _ErrorState({required this.message, required this.onRetry});
  final String message;
  final VoidCallback onRetry;

  @override
  Widget build(BuildContext context) {
    return Center(
      child: Padding(
        padding: EdgeInsets.all(context.gutter),
        child: Container(
          padding: const EdgeInsets.all(18),
          decoration: BoxDecoration(
            color: AppTheme.surface,
            borderRadius: BorderRadius.circular(18),
            border: Border.all(color: AppTheme.border),
          ),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(
                'Unable to load ticket',
                style: TextStyle(
                  fontWeight: FontWeight.w900,
                  color: AppTheme.textPrimary,
                ),
              ),
              const SizedBox(height: 6),
              Text(
                message,
                style: TextStyle(
                  color: AppTheme.textSecondary,
                  fontSize: 12.5,
                ),
              ),
              const SizedBox(height: 12),
              FilledButton.icon(
                onPressed: onRetry,
                icon: const Icon(PhosphorIconsBold.arrowsClockwise, size: 16),
                label: const Text('Try again'),
              ),
            ],
          ),
        ),
      ),
    );
  }
}

class _ReplyComposer extends StatelessWidget {
  const _ReplyComposer({
    required this.controller,
    required this.sending,
    required this.onSend,
    required this.attachment,
    required this.onRemoveAttachment,
    required this.onOpenAttachmentMenu,
  });

  final TextEditingController controller;
  final bool sending;
  final VoidCallback onSend;
  final _SelectedSupportAttachment? attachment;
  final VoidCallback onRemoveAttachment;
  final VoidCallback onOpenAttachmentMenu;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: EdgeInsets.fromLTRB(
        context.gutter,
        10,
        context.gutter,
        MediaQuery.of(context).padding.bottom + 10,
      ),
      decoration: BoxDecoration(
        color: AppTheme.background,
        border: Border(top: BorderSide(color: AppTheme.border)),
      ),
      child: Column(
        mainAxisSize: MainAxisSize.min,
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          if (attachment != null) ...[
            _SelectedAttachmentBanner(
              attachment: attachment!,
              onRemove: onRemoveAttachment,
            ),
            const SizedBox(height: 10),
          ],
          Container(
            padding: const EdgeInsets.all(10),
            decoration: BoxDecoration(
              color: AppTheme.surface,
              borderRadius: BorderRadius.circular(18),
              border: Border.all(color: AppTheme.border),
              boxShadow: AppTheme.shadowSoft,
            ),
            child: Row(
              crossAxisAlignment: CrossAxisAlignment.end,
              children: [
                PressScale(
                  onTap: onOpenAttachmentMenu,
                  child: Container(
                    width: 42,
                    height: 42,
                    decoration: BoxDecoration(
                      color: AppTheme.surfaceMuted,
                      borderRadius: BorderRadius.circular(12),
                      border: Border.all(color: AppTheme.border),
                    ),
                    child: const Icon(
                      PhosphorIconsBold.plus,
                      color: AppTheme.primaryDark,
                      size: 17,
                    ),
                  ),
                ),
                const SizedBox(width: 8),
                Expanded(
                  child: ConstrainedBox(
                    constraints: const BoxConstraints(maxHeight: 120),
                    child: TextField(
                      controller: controller,
                      minLines: 1,
                      maxLines: 5,
                      textCapitalization: TextCapitalization.sentences,
                      decoration: InputDecoration(
                        hintText: 'Write a reply…',
                        border: InputBorder.none,
                        enabledBorder: InputBorder.none,
                        focusedBorder: InputBorder.none,
                        filled: true,
                        fillColor: AppTheme.surfaceMuted,
                        contentPadding: EdgeInsets.symmetric(
                          horizontal: 14,
                          vertical: 12,
                        ),
                      ),
                    ),
                  ),
                ),
                const SizedBox(width: 8),
                PressScale(
                  onTap: sending ? null : onSend,
                  child: Container(
                    width: 42,
                    height: 42,
                    decoration: BoxDecoration(
                      gradient: sending
                          ? null
                          : const LinearGradient(
                              colors: [AppTheme.primary, AppTheme.primaryDark],
                              begin: Alignment.topLeft,
                              end: Alignment.bottomRight,
                            ),
                      color: sending ? AppTheme.surfaceMuted : null,
                      borderRadius: BorderRadius.circular(12),
                    ),
                    child: sending
                        ? const Padding(
                            padding: EdgeInsets.all(11),
                            child: CircularProgressIndicator(
                              strokeWidth: 2.2,
                              color: AppTheme.primaryDark,
                            ),
                          )
                        : const Icon(
                            PhosphorIconsBold.paperPlaneTilt,
                            color: Colors.white,
                            size: 17,
                          ),
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

enum _AttachmentAction { file, photo, camera, video }

class _AttachmentActionSheet extends StatelessWidget {
  const _AttachmentActionSheet();

  @override
  Widget build(BuildContext context) {
    final items = <({IconData icon, String label, _AttachmentAction value})>[
      (
        icon: PhosphorIconsBold.paperclip,
        label: 'Choose File',
        value: _AttachmentAction.file,
      ),
      if (!kIsWeb) ...[
        (
          icon: PhosphorIconsBold.image,
          label: 'Photo Library',
          value: _AttachmentAction.photo,
        ),
        (
          icon: PhosphorIconsBold.camera,
          label: 'Take Photo',
          value: _AttachmentAction.camera,
        ),
        (
          icon: PhosphorIconsBold.videoCamera,
          label: 'Record Video',
          value: _AttachmentAction.video,
        ),
      ],
    ];

    return Container(
      decoration: BoxDecoration(
        color: AppTheme.surface,
        borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
      ),
      padding: EdgeInsets.fromLTRB(
        16,
        16,
        16,
        MediaQuery.of(context).padding.bottom + 16,
      ),
      child: Column(
        mainAxisSize: MainAxisSize.min,
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          Text(
            'Add Attachment',
            style: TextStyle(
              fontWeight: FontWeight.w900,
              color: AppTheme.textPrimary,
              fontSize: 16,
            ),
          ),
          const SizedBox(height: 12),
          ...items.map(
            (item) => Padding(
              padding: const EdgeInsets.only(bottom: 8),
              child: PressScale(
                onTap: () => Navigator.of(context).pop(item.value),
                child: Container(
                  padding: const EdgeInsets.symmetric(
                    horizontal: 14,
                    vertical: 12,
                  ),
                  decoration: BoxDecoration(
                    color: AppTheme.surfaceMuted,
                    borderRadius: BorderRadius.circular(14),
                    border: Border.all(color: AppTheme.border),
                  ),
                  child: Row(
                    children: [
                      Icon(item.icon, size: 16, color: AppTheme.primaryDark),
                      const SizedBox(width: 10),
                      Text(
                        item.label,
                        style: TextStyle(
                          color: AppTheme.textPrimary,
                          fontWeight: FontWeight.w800,
                          fontSize: 12.5,
                        ),
                      ),
                    ],
                  ),
                ),
              ),
            ),
          ),
        ],
      ),
    );
  }
}

class _SelectedAttachmentBanner extends StatelessWidget {
  const _SelectedAttachmentBanner({
    required this.attachment,
    required this.onRemove,
  });

  final _SelectedSupportAttachment attachment;
  final VoidCallback onRemove;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
      decoration: BoxDecoration(
        color: AppTheme.primarySoft,
        borderRadius: BorderRadius.circular(14),
        border: Border.all(color: AppTheme.border),
      ),
      child: Row(
        children: [
          const Icon(
            PhosphorIconsBold.paperclip,
            size: 14,
            color: AppTheme.primaryDark,
          ),
          const SizedBox(width: 8),
          Expanded(
            child: Text(
              attachment.name,
              maxLines: 1,
              overflow: TextOverflow.ellipsis,
              style: TextStyle(
                color: AppTheme.textPrimary,
                fontWeight: FontWeight.w700,
                fontSize: 12,
              ),
            ),
          ),
          const SizedBox(width: 8),
          PressScale(
            onTap: onRemove,
            child: Container(
              width: 24,
              height: 24,
              decoration: BoxDecoration(
                color: AppTheme.surface,
                borderRadius: BorderRadius.circular(8),
              ),
              child: Icon(
                PhosphorIconsBold.x,
                size: 12,
                color: AppTheme.textSecondary,
              ),
            ),
          ),
        ],
      ),
    );
  }
}

class _ForwardIssueSheet extends StatefulWidget {
  const _ForwardIssueSheet({
    required this.users,
    required this.currentUserId,
    required this.noteController,
    required this.onChanged,
  });

  final List<SupportUserOption> users;
  final int currentUserId;
  final TextEditingController noteController;
  final ValueChanged<int?> onChanged;

  @override
  State<_ForwardIssueSheet> createState() => _ForwardIssueSheetState();
}

class _ForwardIssueSheetState extends State<_ForwardIssueSheet> {
  int? _selectedUserId;

  @override
  Widget build(BuildContext context) {
    return Container(
      decoration: BoxDecoration(
        color: AppTheme.surface,
        borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
      ),
      padding: EdgeInsets.fromLTRB(
        16,
        16,
        16,
        MediaQuery.of(context).viewInsets.bottom + 16,
      ),
      child: Column(
        mainAxisSize: MainAxisSize.min,
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          Text(
            'Forward Ticket',
            style: TextStyle(
              fontWeight: FontWeight.w900,
              color: AppTheme.textPrimary,
              fontSize: 16,
            ),
          ),
          const SizedBox(height: 12),
          DropdownButtonFormField<int>(
            initialValue: _selectedUserId,
            items: widget.users
                .map(
                  (user) => DropdownMenuItem<int>(
                    value: user.userId,
                    child: Text(
                      user.userId == widget.currentUserId
                          ? '${user.name} (You)'
                          : user.name,
                    ),
                  ),
                )
                .toList(),
            onChanged: (value) {
              setState(() => _selectedUserId = value);
              widget.onChanged(value);
            },
            decoration: const InputDecoration(labelText: 'Forward to'),
          ),
          const SizedBox(height: 12),
          TextField(
            controller: widget.noteController,
            maxLines: 3,
            textCapitalization: TextCapitalization.sentences,
            decoration: const InputDecoration(
              labelText: 'Note',
              hintText: 'Optional note',
            ),
          ),
          const SizedBox(height: 16),
          Row(
            children: [
              Expanded(
                child: OutlinedButton(
                  onPressed: () => Navigator.of(context).pop(false),
                  child: const Text('Cancel'),
                ),
              ),
              const SizedBox(width: 10),
              Expanded(
                child: FilledButton(
                  onPressed: _selectedUserId == null
                      ? null
                      : () => Navigator.of(context).pop(true),
                  child: const Text('Forward'),
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }
}

class _TagIssueSheet extends StatefulWidget {
  const _TagIssueSheet({
    required this.users,
    required this.currentUserId,
    required this.noteController,
    required this.selectedIds,
  });

  final List<SupportUserOption> users;
  final int currentUserId;
  final TextEditingController noteController;
  final Set<int> selectedIds;

  @override
  State<_TagIssueSheet> createState() => _TagIssueSheetState();
}

class _TagIssueSheetState extends State<_TagIssueSheet> {
  @override
  Widget build(BuildContext context) {
    return Container(
      decoration: BoxDecoration(
        color: AppTheme.surface,
        borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
      ),
      padding: EdgeInsets.fromLTRB(
        16,
        16,
        16,
        MediaQuery.of(context).viewInsets.bottom + 16,
      ),
      child: Column(
        mainAxisSize: MainAxisSize.min,
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          Text(
            'Tag Personnel',
            style: TextStyle(
              fontWeight: FontWeight.w900,
              color: AppTheme.textPrimary,
              fontSize: 16,
            ),
          ),
          const SizedBox(height: 12),
          Wrap(
            spacing: 8,
            runSpacing: 8,
            children: widget.users.map((user) {
              final selected = widget.selectedIds.contains(user.userId);
              final label = user.userId == widget.currentUserId
                  ? '${user.name} (You)'
                  : user.name;
              return FilterChip(
                selected: selected,
                label: Text(label),
                onSelected: (value) {
                  setState(() {
                    if (value) {
                      widget.selectedIds.add(user.userId);
                    } else {
                      widget.selectedIds.remove(user.userId);
                    }
                  });
                },
              );
            }).toList(),
          ),
          const SizedBox(height: 12),
          TextField(
            controller: widget.noteController,
            maxLines: 3,
            textCapitalization: TextCapitalization.sentences,
            decoration: const InputDecoration(
              labelText: 'Message',
              hintText: 'Optional note',
            ),
          ),
          const SizedBox(height: 16),
          Row(
            children: [
              Expanded(
                child: OutlinedButton(
                  onPressed: () => Navigator.of(context).pop(false),
                  child: const Text('Cancel'),
                ),
              ),
              const SizedBox(width: 10),
              Expanded(
                child: FilledButton(
                  onPressed: widget.selectedIds.isEmpty
                      ? null
                      : () => Navigator.of(context).pop(true),
                  child: const Text('Tag'),
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }
}

class _AttachmentChip extends StatelessWidget {
  const _AttachmentChip({
    required this.label,
    required this.onTap,
    this.dark = false,
  });

  final String label;
  final VoidCallback onTap;
  final bool dark;

  @override
  Widget build(BuildContext context) {
    return PressScale(
      onTap: onTap,
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 8),
        decoration: BoxDecoration(
          color: dark
              ? Colors.white.withValues(alpha: 0.14)
              : AppTheme.surfaceMuted,
          borderRadius: BorderRadius.circular(12),
          border: Border.all(
            color: dark
                ? Colors.white.withValues(alpha: 0.24)
                : AppTheme.border,
          ),
        ),
        child: Row(
          mainAxisSize: MainAxisSize.min,
          children: [
            Icon(
              PhosphorIconsBold.paperclip,
              size: 12,
              color: dark ? Colors.white : AppTheme.primaryDark,
            ),
            const SizedBox(width: 6),
            Flexible(
              child: Text(
                label,
                maxLines: 1,
                overflow: TextOverflow.ellipsis,
                style: TextStyle(
                  color: dark ? Colors.white : AppTheme.textPrimary,
                  fontWeight: FontWeight.w700,
                  fontSize: 11.5,
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }
}

class _CloseTicketSheet extends StatelessWidget {
  const _CloseTicketSheet({required this.controller});

  final TextEditingController controller;

  @override
  Widget build(BuildContext context) {
    return Container(
      decoration: BoxDecoration(
        color: AppTheme.surface,
        borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
      ),
      padding: EdgeInsets.fromLTRB(
        16,
        16,
        16,
        MediaQuery.of(context).viewInsets.bottom + 16,
      ),
      child: Column(
        mainAxisSize: MainAxisSize.min,
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          Text(
            'Close Ticket',
            style: TextStyle(
              fontWeight: FontWeight.w900,
              color: AppTheme.textPrimary,
              fontSize: 16,
            ),
          ),
          const SizedBox(height: 6),
          Text(
            'Send the completion message that the client will see.',
            style: TextStyle(color: AppTheme.textSecondary, fontSize: 12.5),
          ),
          const SizedBox(height: 14),
          TextField(
            controller: controller,
            maxLines: 5,
            textCapitalization: TextCapitalization.sentences,
            decoration: const InputDecoration(
              hintText: 'Tell the client the issue has been fixed.',
            ),
          ),
          const SizedBox(height: 16),
          Row(
            children: [
              Expanded(
                child: OutlinedButton(
                  onPressed: () => Navigator.of(context).pop(false),
                  child: const Text('Cancel'),
                ),
              ),
              const SizedBox(width: 10),
              Expanded(
                child: FilledButton(
                  onPressed: () => Navigator.of(context).pop(true),
                  child: const Text('Close Ticket'),
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }
}

class _SelectedSupportAttachment {
  const _SelectedSupportAttachment({required this.name, required this.bytes});

  final String name;
  final Uint8List bytes;
}
