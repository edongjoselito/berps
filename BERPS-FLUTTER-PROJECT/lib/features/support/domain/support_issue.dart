class SupportIssuesData {
  const SupportIssuesData({
    required this.scope,
    required this.counts,
    required this.issues,
  });

  final String scope;
  final SupportIssueCounts counts;
  final List<SupportIssue> issues;

  factory SupportIssuesData.fromJson(Map<String, dynamic> json) {
    final countsMap = json['counts'] is Map
        ? Map<String, dynamic>.from(json['counts'] as Map)
        : <String, dynamic>{};
    final issuesRaw = json['issues'];

    return SupportIssuesData(
      scope: (json['scope'] ?? 'open').toString(),
      counts: SupportIssueCounts.fromJson(countsMap),
      issues: issuesRaw is List
          ? issuesRaw
                .whereType<Map>()
                .map((e) => SupportIssue.fromJson(Map<String, dynamic>.from(e)))
                .toList()
          : const <SupportIssue>[],
    );
  }
}

class SupportIssueCounts {
  const SupportIssueCounts({
    required this.open,
    required this.unassigned,
    required this.closed,
    required this.all,
  });

  final int open;
  final int unassigned;
  final int closed;
  final int all;

  factory SupportIssueCounts.fromJson(Map<String, dynamic> json) {
    int parseInt(dynamic v) => int.tryParse((v ?? '0').toString()) ?? 0;
    return SupportIssueCounts(
      open: parseInt(json['open']),
      unassigned: parseInt(json['unassigned']),
      closed: parseInt(json['closed']),
      all: parseInt(json['all']),
    );
  }
}

class SupportIssue {
  const SupportIssue({
    required this.id,
    required this.ticketNumber,
    required this.title,
    required this.customerName,
    required this.customerEmail,
    required this.departmentName,
    required this.projectName,
    required this.category,
    required this.priority,
    required this.status,
    required this.isClosed,
    required this.isUnassigned,
    required this.assignedToMe,
    required this.assignedName,
    required this.createdLabel,
    required this.updatedLabel,
    this.description = '',
    this.referenceLink = '',
    this.customerPhone = '',
    this.taskId = 0,
  });

  final int id;
  final String ticketNumber;
  final String title;
  final String customerName;
  final String customerEmail;
  final String departmentName;
  final String projectName;
  final String category;
  final String priority;
  final String status;
  final bool isClosed;
  final bool isUnassigned;
  final bool assignedToMe;
  final String assignedName;
  final String createdLabel;
  final String updatedLabel;

  final String description;
  final String referenceLink;
  final String customerPhone;
  final int taskId;

  factory SupportIssue.fromJson(Map<String, dynamic> json) {
    int parseInt(dynamic v) => int.tryParse((v ?? '0').toString()) ?? 0;
    bool parseBool(dynamic v) {
      if (v is bool) return v;
      final s = (v ?? '').toString().toLowerCase();
      return s == '1' || s == 'true';
    }

    return SupportIssue(
      id: parseInt(json['id']),
      ticketNumber: (json['ticket_number'] ?? '').toString(),
      title: (json['title'] ?? '').toString(),
      customerName: (json['customer_name'] ?? '').toString(),
      customerEmail: (json['customer_email'] ?? '').toString(),
      departmentName: (json['department_name'] ?? '').toString(),
      projectName: (json['project_name'] ?? '').toString(),
      category: (json['category'] ?? '').toString(),
      priority: (json['priority'] ?? 'medium').toString(),
      status: (json['status'] ?? 'open').toString(),
      isClosed: parseBool(json['is_closed']),
      isUnassigned: parseBool(json['is_unassigned']),
      assignedToMe: parseBool(json['assigned_to_me']),
      assignedName: (json['assigned_name'] ?? '').toString(),
      createdLabel: (json['created_label'] ?? '').toString(),
      updatedLabel: (json['updated_label'] ?? '').toString(),
      description: (json['description'] ?? '').toString(),
      referenceLink: (json['reference_link'] ?? '').toString(),
      customerPhone: (json['customer_phone'] ?? '').toString(),
      taskId: parseInt(json['task_id']),
    );
  }
}

class SupportIssueDetail {
  const SupportIssueDetail({
    required this.issue,
    required this.comments,
    required this.canViewChat,
    required this.canReplyChat,
    required this.assignableUsers,
    required this.taggableUsers,
    required this.currentUserId,
  });

  final SupportIssue issue;
  final List<SupportComment> comments;
  final bool canViewChat;
  final bool canReplyChat;
  final List<SupportUserOption> assignableUsers;
  final List<SupportUserOption> taggableUsers;
  final int currentUserId;

  factory SupportIssueDetail.fromJson(Map<String, dynamic> json) {
    final issueMap = json['issue'] is Map
        ? Map<String, dynamic>.from(json['issue'] as Map)
        : <String, dynamic>{};
    final commentsRaw = json['comments'];
    bool parseBool(dynamic v) {
      if (v is bool) return v;
      final s = (v ?? '').toString().toLowerCase();
      return s == '1' || s == 'true';
    }

    int parseInt(dynamic v) => int.tryParse((v ?? '0').toString()) ?? 0;

    return SupportIssueDetail(
      issue: SupportIssue.fromJson(issueMap),
      comments: commentsRaw is List
          ? commentsRaw
                .whereType<Map>()
                .map(
                  (e) => SupportComment.fromJson(Map<String, dynamic>.from(e)),
                )
                .toList()
          : const <SupportComment>[],
      canViewChat: parseBool(json['can_view_chat']),
      canReplyChat: parseBool(json['can_reply_chat']),
      assignableUsers: _mapSupportUserList(json['assignable_users']),
      taggableUsers: _mapSupportUserList(json['taggable_users']),
      currentUserId: parseInt(json['current_user_id']),
    );
  }
}

class SupportUserOption {
  const SupportUserOption({required this.userId, required this.name});

  final int userId;
  final String name;

  factory SupportUserOption.fromJson(Map<String, dynamic> json) {
    return SupportUserOption(
      userId: int.tryParse((json['user_id'] ?? '0').toString()) ?? 0,
      name: (json['name'] ?? '').toString(),
    );
  }
}

class SupportComment {
  const SupportComment({
    required this.id,
    required this.authorName,
    required this.isCustomer,
    required this.isMine,
    required this.isInternal,
    required this.comment,
    required this.attachmentPath,
    required this.createdLabel,
  });

  final int id;
  final String authorName;
  final bool isCustomer;
  final bool isMine;
  final bool isInternal;
  final String comment;
  final String attachmentPath;
  final String createdLabel;

  factory SupportComment.fromJson(Map<String, dynamic> json) {
    int parseInt(dynamic v) => int.tryParse((v ?? '0').toString()) ?? 0;
    bool parseBool(dynamic v) {
      if (v is bool) return v;
      final s = (v ?? '').toString().toLowerCase();
      return s == '1' || s == 'true';
    }

    return SupportComment(
      id: parseInt(json['id']),
      authorName: (json['author_name'] ?? '').toString(),
      isCustomer: parseBool(json['is_customer']),
      isMine: parseBool(json['is_mine']),
      isInternal: parseBool(json['is_internal']),
      comment: (json['comment'] ?? '').toString(),
      attachmentPath: (json['attachment_path'] ?? '').toString(),
      createdLabel: (json['created_label'] ?? '').toString(),
    );
  }
}

List<SupportUserOption> _mapSupportUserList(dynamic raw) {
  if (raw is! List) return const <SupportUserOption>[];
  return raw
      .whereType<Map>()
      .map((e) => SupportUserOption.fromJson(Map<String, dynamic>.from(e)))
      .toList();
}
