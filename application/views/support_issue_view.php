<?php
$issue = isset($issue) ? $issue : null;
$comments = isset($comments) && is_array($comments) ? $comments : array();
$assignableUsers = isset($assignableUsers) && is_array($assignableUsers) ? $assignableUsers : array();
$taggableUsers = isset($taggableUsers) && is_array($taggableUsers) ? $taggableUsers : array();
$attachments = isset($attachments) && is_array($attachments) ? $attachments : array();
$canViewChat = isset($canViewChat) ? (bool) $canViewChat : true;
$canReplyChat = isset($canReplyChat) ? (bool) $canReplyChat : true;
$currentUserId = isset($currentUserId) ? (int) $currentUserId : 0;
$isStaffUser = isset($isStaffUser) ? (bool) $isStaffUser : false;
$ticketViewUrl = !empty($issue->id) ? base_url('Page/supportIssueView?id=' . (int) $issue->id) : '#';

if (!function_exists('supportStaffDisplayName')) {
    function supportStaffDisplayName($user)
    {
        $first = trim((string) ($user->fName ?? ''));
        $last = trim((string) ($user->lName ?? ''));

        $staffName = trim($first . ' ' . $last);
        if ($staffName !== '') {
            return $staffName;
        }

        $username = trim((string) ($user->username ?? ''));
        if ($username !== '') {
            return $username;
        }

        return 'Staff #' . (int) ($user->user_id ?? 0);
    }
}

if (!function_exists('getAttachmentIcon')) {
    function getAttachmentIcon($fileName)
    {
        $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $iconMap = array(
            'pdf' => 'fa-file-pdf',
            'doc' => 'fa-file-word',
            'docx' => 'fa-file-word',
            'xls' => 'fa-file-excel',
            'xlsx' => 'fa-file-excel',
            'ppt' => 'fa-file-powerpoint',
            'pptx' => 'fa-file-powerpoint',
            'jpg' => 'fa-file-image',
            'jpeg' => 'fa-file-image',
            'png' => 'fa-file-image',
            'gif' => 'fa-file-image',
            'webp' => 'fa-file-image',
            'zip' => 'fa-file-archive',
            'rar' => 'fa-file-archive',
            'txt' => 'fa-file-alt',
            'mp4' => 'fa-file-video',
            'mp3' => 'fa-file-audio',
            'csv' => 'fa-file-csv',
        );
        return isset($iconMap[$extension]) ? $iconMap[$extension] : 'fa-file';
    }
}

if (!function_exists('formatFileSize')) {
    function formatFileSize($bytes)
    {
        if ($bytes === null || $bytes === '') {
            return 'Unknown size';
        }
        $bytes = (int) $bytes;
        if ($bytes < 1024) {
            return $bytes . ' B';
        } elseif ($bytes < 1048576) {
            return number_format($bytes / 1024, 1) . ' KB';
        } elseif ($bytes < 1073741824) {
            return number_format($bytes / 1048576, 1) . ' MB';
        } else {
            return number_format($bytes / 1073741824, 1) . ' GB';
        }
    }
}

if (!function_exists('supportMessageLinks')) {
    function supportMessageLinks($text)
    {
        $text = (string) $text;
        if ($text === '') {
            return array();
        }

        preg_match_all('/https?:\/\/[^\s<>"\']+/i', $text, $matches);
        $links = array();

        foreach (($matches[0] ?? array()) as $match) {
            $url = rtrim(trim((string) $match), ".,);");
            if ($url !== '' && !in_array($url, $links, true)) {
                $links[] = $url;
            }
        }

        return $links;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<?php include('includes/head.php'); ?>
<link rel="stylesheet" href="<?= base_url(); ?>assets/libs/select2/select2.min.css">

<body>
    <div id="wrapper">
        <?php include('includes/top-nav-bar.php'); ?>
        <?php include('includes/sidebar.php'); ?>

        <div class="content-page">
            <div class="content">
                <div class="container-fluid client-profile-page">
                    <style>
                        .client-profile-page {
                            --bg: #f5f7fb;
                            --surface: rgba(255, 255, 255, 0.92);
                            --surface-2: #ffffff;
                            --line: #e7ecf3;
                            --text: #122033;
                            --text-soft: #5e7188;
                            --text-faint: #8ea0b5;
                            --primary: #2563eb;
                            --primary-soft: #eaf2ff;
                            --success: #059669;
                            --success-soft: #ecfdf5;
                            --warning: #d97706;
                            --warning-soft: #fff7ed;
                            --danger: #e11d48;
                            --danger-soft: #fff1f2;
                            --shadow: 0 14px 40px rgba(15, 23, 42, 0.08);
                            --radius-xl: 22px;
                            font-family: 'Inter', 'Poppins', 'Segoe UI', Arial, sans-serif;
                            background:
                                radial-gradient(circle at top left, rgba(37, 99, 235, 0.08), transparent 28%),
                                radial-gradient(circle at top right, rgba(16, 185, 129, 0.08), transparent 24%),
                                linear-gradient(180deg, #f8fbff 0%, #f4f7fb 100%);
                            min-height: 100vh;
                            padding-bottom: 16px;
                        }

                        .client-profile-page .cp-header {
                            margin: 16px 0 12px;
                            display: flex;
                            justify-content: space-between;
                            align-items: flex-end;
                            gap: 16px;
                            flex-wrap: wrap;
                        }

                        .client-profile-page .cp-eyebrow {
                            display: inline-flex;
                            align-items: center;
                            padding: 7px 12px;
                            border-radius: 999px;
                            background: rgba(37, 99, 235, .08);
                            color: #1d4ed8;
                            font-size: .76rem;
                            font-weight: 700;
                            letter-spacing: .08em;
                            text-transform: uppercase;
                            margin-bottom: 10px;
                        }

                        .client-profile-page .cp-title {
                            margin: 0;
                            color: var(--text);
                            font-size: clamp(2rem, 3vw, 2.7rem);
                            line-height: 1.05;
                            font-weight: 800;
                        }

                        .client-profile-page .cp-subtitle {
                            margin: 12px 0 0;
                            color: var(--text-soft);
                            font-size: 1rem;
                            max-width: 780px;
                        }

                        .client-profile-page .header-actions {
                            display: flex;
                            gap: 10px;
                            flex-wrap: wrap;
                        }

                        .client-profile-page .btn-soft,
                        .client-profile-page .btn-solid,
                        .client-profile-page .btn-filter {
                            display: inline-flex;
                            align-items: center;
                            gap: 10px;
                            border-radius: 18px;
                            padding: 12px 20px;
                            font-weight: 700;
                            text-decoration: none;
                            box-shadow: 0 10px 26px rgba(15, 23, 42, .04);
                            border: 1px solid var(--line);
                            background: rgba(255, 255, 255, .9);
                            color: var(--text);
                        }

                        .client-profile-page .btn-link-action {
                            display: inline-flex;
                            align-items: center;
                            gap: 8px;
                            border-radius: 14px;
                            padding: 10px 16px;
                            border: 1px solid rgba(37, 99, 235, .18);
                            background: var(--primary-soft);
                            color: var(--primary);
                            font-weight: 700;
                            text-decoration: none;
                            line-height: 1.3;
                            word-break: break-word;
                        }

                        .client-profile-page .btn-link-action:hover {
                            text-decoration: none;
                            background: #dbeafe;
                            color: #1d4ed8;
                        }

                        .client-profile-page .btn-solid {
                            background: linear-gradient(135deg, var(--primary), #1d4ed8);
                            color: #fff;
                            border-color: transparent;
                        }

                        .client-profile-page .panel-card {
                            background: var(--surface);
                            border: 1px solid rgba(255, 255, 255, .75);
                            border-radius: var(--radius-xl);
                            box-shadow: var(--shadow);
                            overflow: hidden;
                            margin-bottom: 16px;
                        }

                        .client-profile-page .panel-header {
                            padding: 18px 24px 14px;
                            border-bottom: 1px solid var(--line);
                        }

                        .client-profile-page .panel-title {
                            margin: 0;
                            color: var(--text);
                            font-size: 1.45rem;
                            font-weight: 800;
                        }

                        .client-profile-page .panel-subtitle {
                            margin-top: 8px;
                            color: var(--text-soft);
                            font-size: .98rem;
                        }

                        .client-profile-page .panel-body {
                            padding: 16px 24px 20px;
                        }

                        .client-profile-page .status-pill {
                            display: inline-flex;
                            align-items: center;
                            padding: 5px 10px;
                            border-radius: 999px;
                            font-size: .74rem;
                            font-weight: 700;
                        }

                        .client-profile-page .status-open,
                        .client-profile-page .status-awaiting_reply,
                        .client-profile-page .status-reopened {
                            background: var(--primary-soft);
                            color: var(--primary);
                        }

                        .client-profile-page .status-closed,
                        .client-profile-page .status-resolved,
                        .client-profile-page .status-done,
                        .client-profile-page .status-completed {
                            background: var(--success-soft);
                            color: var(--success);
                        }

                        .client-profile-page .status-cancelled,
                        .client-profile-page .status-canceled {
                            background: var(--danger-soft);
                            color: var(--danger);
                        }

                        .client-profile-page .alert {
                            border: none;
                            border-radius: 18px;
                            box-shadow: 0 10px 26px rgba(15, 23, 42, .04);
                            margin-bottom: 20px;
                        }

                        .client-profile-page .form-control {
                            border-radius: 14px;
                            border: 1px solid var(--line);
                            padding: 12px 16px;
                            font-size: .95rem;
                        }

                        .client-profile-page .form-control:focus {
                            border-color: var(--primary);
                            box-shadow: 0 0 0 3px rgba(37, 99, 235, .1);
                        }

                        .client-profile-page label {
                            color: var(--text);
                            font-weight: 600;
                            font-size: .92rem;
                            margin-bottom: 8px;
                            display: block;
                        }

                        .client-profile-page .info-row {
                            display: flex;
                            margin-bottom: 12px;
                        }

                        .client-profile-page .info-label {
                            min-width: 120px;
                            color: var(--text-soft);
                            font-weight: 600;
                            font-size: .88rem;
                        }

                        .client-profile-page .info-value {
                            color: var(--text);
                            font-weight: 500;
                        }

                        .client-profile-page .info-value a {
                            color: var(--primary);
                            text-decoration: none;
                        }

                        .client-profile-page .info-value a:hover {
                            text-decoration: underline;
                        }

                        .support-chat-list {
                            display: flex;
                            flex-direction: column;
                            gap: 14px;
                            margin-bottom: 20px;
                        }

                        .support-chat-message {
                            border-radius: 18px;
                            padding: 14px 16px;
                            border: 1px solid var(--line);
                            box-shadow: 0 10px 24px rgba(15, 23, 42, .04);
                        }

                        .support-chat-message.client-message {
                            background: var(--primary-soft);
                            border-color: rgba(37, 99, 235, .2);
                        }

                        .support-chat-message.staff-message {
                            background: var(--success-soft);
                            border-color: rgba(5, 150, 105, .2);
                        }

                        .support-chat-message.internal-note {
                            background: var(--warning-soft);
                            border-color: rgba(217, 119, 6, .2);
                        }

                        .support-chat-message .chat-meta {
                            display: flex;
                            justify-content: space-between;
                            align-items: center;
                            gap: 12px;
                            flex-wrap: wrap;
                            margin-bottom: 8px;
                        }

                        .support-chat-message .chat-badge {
                            display: inline-flex;
                            align-items: center;
                            padding: 4px 10px;
                            border-radius: 999px;
                            font-size: .72rem;
                            font-weight: 700;
                            letter-spacing: .02em;
                        }

                        .support-chat-message.client-message .chat-badge {
                            background: #dbeafe;
                            color: #1d4ed8;
                        }

                        .support-chat-message.staff-message .chat-badge {
                            background: #d1fae5;
                            color: #047857;
                        }

                        .support-chat-message.internal-note .chat-badge {
                            background: #ffedd5;
                            color: #c2410c;
                        }

                        .support-chat-message .chat-body {
                            color: var(--text);
                            line-height: 1.6;
                        }

                        .support-chat-message .chat-links {
                            display: flex !important;
                            flex-wrap: wrap !important;
                            gap: 10px !important;
                            margin-top: 12px !important;
                            visibility: visible !important;
                            position: relative !important;
                            z-index: 5 !important;
                        }

                        .support-chat-message .chat-links a {
                            display: inline-flex !important;
                            visibility: visible !important;
                            position: relative !important;
                            z-index: 6 !important;
                        }

                        .support-chat-message .chat-actions {
                            display: flex;
                            gap: 8px;
                            margin-top: 10px;
                        }

                        .support-chat-message .chat-actions button,
                        .support-chat-message .chat-actions a {
                            font-size: .75rem;
                            padding: 6px 12px;
                            border-radius: 10px;
                            border: none;
                            cursor: pointer;
                            display: inline-flex;
                            align-items: center;
                            gap: 5px;
                            font-weight: 600;
                            text-decoration: none;
                        }

                        .support-chat-message .chat-actions .btn-edit {
                            background: var(--primary-soft);
                            color: var(--primary);
                            border: 1px solid rgba(37, 99, 235, .2);
                        }

                        .support-chat-message .chat-actions .btn-edit:hover {
                            background: #dbeafe;
                        }

                        .support-chat-message .chat-actions .btn-delete {
                            background: var(--danger-soft);
                            color: var(--danger);
                            border: 1px solid rgba(225, 29, 72, .2);
                        }

                        .support-chat-message .chat-actions .btn-delete:hover {
                            background: #ffe4e6;
                        }

                        .support-chat-message .edit-form {
                            display: none;
                            margin-top: 12px;
                        }

                        .support-chat-message .edit-form textarea {
                            width: 100%;
                            border-radius: 12px;
                            border: 1px solid var(--line);
                            padding: 10px 14px;
                            font-size: .95rem;
                            min-height: 80px;
                            resize: vertical;
                        }

                        .support-chat-message .edit-form .edit-actions {
                            display: flex;
                            gap: 8px;
                            margin-top: 10px;
                        }

                        .support-chat-message .edit-form .edit-actions button {
                            font-size: .75rem;
                            padding: 6px 14px;
                            border-radius: 10px;
                            border: none;
                            cursor: pointer;
                            font-weight: 600;
                        }

                        .support-chat-message .edit-form .edit-actions .btn-save {
                            background: var(--success);
                            color: #fff;
                        }

                        .support-chat-message .edit-form .edit-actions .btn-cancel {
                            background: var(--line);
                            color: var(--text);
                        }

                        .client-profile-page .btn-block {
                            width: 100%;
                            display: flex;
                            justify-content: center;
                        }

                        .client-profile-page .btn-primary {
                            background: linear-gradient(135deg, var(--primary), #1d4ed8);
                            color: #fff;
                            border: none;
                            border-radius: 18px;
                            padding: 12px 20px;
                            font-weight: 700;
                        }

                        .client-profile-page .btn-warning {
                            background: linear-gradient(135deg, var(--warning), #b45309);
                            color: #fff;
                            border: none;
                            border-radius: 18px;
                            padding: 12px 20px;
                            font-weight: 700;
                        }

                        .client-profile-page .btn-info {
                            background: linear-gradient(135deg, #0891b2, #0e7490);
                            color: #fff;
                            border: none;
                            border-radius: 18px;
                            padding: 12px 20px;
                            font-weight: 700;
                        }

                        .client-profile-page .btn-success {
                            background: linear-gradient(135deg, var(--success), #047857);
                            color: #fff;
                            border: none;
                            border-radius: 18px;
                            padding: 12px 20px;
                            font-weight: 700;
                        }

                        .client-profile-page .attachment-box {
                            border: 1px solid var(--line);
                            border-radius: 18px;
                            background: #fff;
                            padding: 18px;
                            margin-top: 16px;
                        }

                        .client-profile-page .attachment-grid {
                            display: grid;
                            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
                            gap: 14px;
                            margin-top: 12px;
                        }

                        .client-profile-page .attachment-card {
                            border: 1px solid var(--line);
                            border-radius: 14px;
                            background: var(--primary-soft);
                            padding: 16px;
                            display: flex;
                            flex-direction: column;
                            align-items: center;
                            text-align: center;
                            transition: all 0.2s ease;
                            text-decoration: none;
                            color: var(--text);
                        }

                        .client-profile-page .attachment-card:hover {
                            transform: translateY(-3px);
                            box-shadow: 0 8px 20px rgba(15, 23, 42, .08);
                            border-color: var(--primary);
                        }

                        .client-profile-page .attachment-icon {
                            font-size: 2.5rem;
                            margin-bottom: 10px;
                            color: var(--primary);
                        }

                        .client-profile-page .attachment-name {
                            font-size: .88rem;
                            font-weight: 600;
                            margin-bottom: 6px;
                            word-break: break-word;
                            line-height: 1.3;
                        }

                        .client-profile-page .attachment-size {
                            font-size: .76rem;
                            color: var(--text-soft);
                        }

                        /* Responsive optimizations for maximum screen utilization */
                        @media (min-width: 1400px) {
                            .client-profile-page .container-fluid {
                                max-width: 95%;
                            }
                        }

                        @media (min-width: 1600px) {
                            .client-profile-page .container-fluid {
                                max-width: 94%;
                            }
                        }

                        @media (min-width: 1920px) {
                            .client-profile-page .container-fluid {
                                max-width: 92%;
                            }
                        }

                        @media (max-width: 768px) {
                            .client-profile-page .cp-header {
                                flex-direction: column;
                                align-items: flex-start;
                                gap: 12px;
                            }

                            .client-profile-page .panel-header,
                            .client-profile-page .panel-body {
                                padding: 16px;
                            }

                            .support-chat-message {
                                padding: 12px 16px;
                            }

                            .client-profile-page .attachment-grid {
                                grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
                            }
                        }

                    </style>

                    <div class="cp-header">
                        <div>
                            <div class="cp-eyebrow">Support Portal</div>
                            <h1 class="cp-title">Ticket <?= htmlspecialchars((string) ($issue->ticket_number ?? ''), ENT_QUOTES, 'UTF-8'); ?></h1>
                            <p class="cp-subtitle"><?= htmlspecialchars((string) ($issue->title ?? ''), ENT_QUOTES, 'UTF-8'); ?></p>
                        </div>
                        <div class="header-actions">
                            <a class="btn-link-action" href="<?= htmlspecialchars($ticketViewUrl, ENT_QUOTES, 'UTF-8'); ?>">
                                <i class="fas fa-link"></i>
                                Ticket Link
                            </a>
                            <a class="btn-soft" href="<?= base_url('Page/supportIssues?scope=open'); ?>"><i class="fas fa-arrow-left"></i>Back to Issues</a>
                        </div>
                    </div>

                    <?php if ($this->session->flashdata('success')): ?>
                        <div class="alert alert-success"><?= $this->session->flashdata('success'); ?></div>
                    <?php endif; ?>
                    <?php if ($this->session->flashdata('danger')): ?>
                        <div class="alert alert-danger"><?= $this->session->flashdata('danger'); ?></div>
                    <?php endif; ?>
                    <?php if ($this->session->flashdata('warning')): ?>
                        <div class="alert alert-warning"><?= $this->session->flashdata('warning'); ?></div>
                    <?php endif; ?>

                    <div class="row">
                        <div class="col-lg-8">
                            <div class="panel-card">
                                <div class="panel-header">
                                    <h2 class="panel-title">Ticket Details</h2>
                                </div>
                                <div class="panel-body">
                                    <div class="info-row">
                                        <div class="info-label">Customer:</div>
                                        <div class="info-value"><?= htmlspecialchars((string) ($issue->customer_name ?? ''), ENT_QUOTES, 'UTF-8'); ?></div>
                                    </div>
                                    <div class="info-row">
                                        <div class="info-label">Email:</div>
                                        <div class="info-value"><?= htmlspecialchars((string) ($issue->customer_email ?? ''), ENT_QUOTES, 'UTF-8'); ?></div>
                                    </div>
                                    <div class="info-row">
                                        <div class="info-label">Project:</div>
                                        <div class="info-value"><?= htmlspecialchars((string) (!empty($issue->projectDescription) ? $issue->projectDescription : 'General'), ENT_QUOTES, 'UTF-8'); ?></div>
                                    </div>
                                    <div class="info-row">
                                        <div class="info-label">Category:</div>
                                        <div class="info-value"><?= htmlspecialchars(ucfirst((string) ($issue->category ?? 'general')), ENT_QUOTES, 'UTF-8'); ?></div>
                                    </div>
                                    <div class="info-row">
                                        <div class="info-label">Department:</div>
                                        <div class="info-value"><?= htmlspecialchars((string) ($issue->department_name ?? 'N/A'), ENT_QUOTES, 'UTF-8'); ?></div>
                                    </div>
                                    <?php $statusKey = strtolower(trim((string) ($issue->status ?? 'open'))); ?>
                                    <div class="info-row">
                                        <div class="info-label">Status:</div>
                                        <div class="info-value"><span class="status-pill status-<?= htmlspecialchars($statusKey, ENT_QUOTES, 'UTF-8'); ?>"><?= htmlspecialchars(str_replace('_', ' ', ucfirst($statusKey !== '' ? $statusKey : 'open')), ENT_QUOTES, 'UTF-8'); ?></span></div>
                                    </div>
                                    <div class="info-row">
                                        <div class="info-label">Assigned To:</div>
                                        <div class="info-value"><?= htmlspecialchars((string) ($issue->assigned_employee_name ?? 'Unassigned'), ENT_QUOTES, 'UTF-8'); ?></div>
                                    </div>
                                    <?php if (!empty($issue->reference_link)): ?>
                                        <div class="info-row">
                                            <div class="info-label">Reference:</div>
                                            <div class="info-value">
                                                <a class="btn-link-action" href="<?= htmlspecialchars((string) $issue->reference_link, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener noreferrer">
                                                    <i class="fas fa-up-right-from-square"></i>
                                                    Reference Link
                                                </a>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                    <?php if (!empty($attachments)): ?>
                                        <div class="attachment-box">
                                            <div class="info-label">Attachments</div>
                                            <div class="attachment-grid">
                                                <?php foreach ($attachments as $attachment): ?>
                                                    <a class="attachment-card" href="<?= base_url((string) ($attachment->file_path ?? '')); ?>" target="_blank" rel="noopener noreferrer">
                                                        <i class="fas <?= htmlspecialchars(getAttachmentIcon((string) ($attachment->file_name ?? '')), ENT_QUOTES, 'UTF-8'); ?> attachment-icon"></i>
                                                        <div class="attachment-name"><?= htmlspecialchars((string) ($attachment->file_name ?? ''), ENT_QUOTES, 'UTF-8'); ?></div>
                                                        <div class="attachment-size"><?= htmlspecialchars(formatFileSize((int) ($attachment->file_size ?? 0)), ENT_QUOTES, 'UTF-8'); ?></div>
                                                    </a>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                    <div style="margin-top:16px; padding-top:16px; border-top:1px solid var(--line);">
                                        <div class="info-label" style="margin-bottom:8px;">Description:</div>
                                        <div style="color:var(--text); line-height:1.6;"><?= nl2br(htmlspecialchars((string) ($issue->description ?? ''), ENT_QUOTES, 'UTF-8')); ?></div>
                                    </div>
                                </div>
                            </div>

                            <div class="panel-card">
                                <div class="panel-header">
                                    <h2 class="panel-title">Ticket Chat</h2>
                                </div>
                                <div class="panel-body">
                                    <?php if ($canViewChat): ?>
                                        <?php if (!empty($comments)): ?>
                                            <div class="support-chat-list">
                                                <?php $attachmentCounter = 1; ?>
                                                <?php foreach ($comments as $comment): ?>
                                                    <?php
                                                    $isClientComment = !empty($comment->customer_comment);
                                                    $isInternalNote = !empty($comment->internal_note);
                                                    $messageClass = $isInternalNote ? 'internal-note' : ($isClientComment ? 'client-message' : 'staff-message');
                                                    $messageLinks = supportMessageLinks((string) ($comment->comment ?? ''));
                                                    $senderLabel = $isInternalNote
                                                        ? 'Internal Note'
                                                        : ($isClientComment ? htmlspecialchars((string) ($comment->customer_name ?? 'Client'), ENT_QUOTES, 'UTF-8') : htmlspecialchars((string) ($comment->employee_name ?? 'Team'), ENT_QUOTES, 'UTF-8'));
                                                    $badgeLabel = $isInternalNote ? 'Internal' : ($isClientComment ? 'Customer' : htmlspecialchars((string) ($comment->employee_name ?? 'Staff'), ENT_QUOTES, 'UTF-8'));
                                                    $isOwnComment = !$isClientComment && (int) ($comment->employee_id ?? 0) === (int) ($currentUserId ?? 0);
                                                    $commentId = (int) ($comment->id ?? 0);
                                                    ?>
                                                    <div class="support-chat-message <?= $messageClass; ?>" data-comment-id="<?= $commentId; ?>">
                                                        <div class="chat-meta">
                                                            <div>
                                                                <strong><?= $senderLabel; ?></strong>
                                                                <span class="chat-badge ml-2"><?= htmlspecialchars($badgeLabel, ENT_QUOTES, 'UTF-8'); ?></span>
                                                            </div>
                                                            <span style="color:var(--text-soft); font-size:.85rem;"><?= !empty($comment->created_at) ? htmlspecialchars(date('M d, Y h:i A', strtotime((string) $comment->created_at)), ENT_QUOTES, 'UTF-8') : 'N/A'; ?></span>
                                                        </div>
                                                        <div class="chat-body" id="comment-body-<?= $commentId; ?>"><?= nl2br(htmlspecialchars((string) ($comment->comment ?? ''), ENT_QUOTES, 'UTF-8')); ?></div>
                                                        <?php
                                                        $attachmentPath = '';
                                                        $attachmentName = '';
                                                        if (!empty($comment->attachment_path)) {
                                                            $attachmentPath = $comment->attachment_path;
                                                            $attachmentName = !empty($comment->attachment_name) ? $comment->attachment_name : basename($attachmentPath);
                                                        } elseif (!empty($comment->attachment)) {
                                                            $attachmentPath = $comment->attachment;
                                                            $attachmentName = !empty($comment->attachment_name) ? $comment->attachment_name : basename($attachmentPath);
                                                        } elseif (!empty($comment->file_path)) {
                                                            $attachmentPath = $comment->file_path;
                                                            $attachmentName = !empty($comment->file_name) ? $comment->file_name : basename($attachmentPath);
                                                        } elseif (!empty($comment->file)) {
                                                            $attachmentPath = $comment->file;
                                                            $attachmentName = !empty($comment->filename) ? $comment->filename : basename($attachmentPath);
                                                        }
                                                        ?>
                                                        <?php if (!empty($attachmentPath)): ?>
                                                            <div class="chat-links">
                                                                <a class="btn-link-action" href="<?= base_url((string) $attachmentPath); ?>" target="_blank" rel="noopener noreferrer">
                                                                    <i class="fas fa-paperclip"></i>
                                                                    <?= !empty($attachmentName) ? htmlspecialchars($attachmentName, ENT_QUOTES, 'UTF-8') : 'Attachment ' . $attachmentCounter++; ?>
                                                                </a>
                                                            </div>
                                                        <?php endif; ?>
                                                        <?php if (!empty($messageLinks)): ?>
                                                            <div class="chat-links">
                                                                <?php foreach ($messageLinks as $messageLink): ?>
                                                                    <a class="btn-link-action" href="<?= htmlspecialchars($messageLink, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener noreferrer">
                                                                        <i class="fas fa-up-right-from-square"></i>
                                                                        Reference Link
                                                                    </a>
                                                                <?php endforeach; ?>
                                                            </div>
                                                        <?php endif; ?>
                                                        <?php if ($isOwnComment): ?>
                                                            <div class="chat-actions">
                                                                <button type="button" class="btn-edit" onclick="toggleEditForm(<?= $commentId; ?>)">
                                                                    <i class="fas fa-edit"></i> Edit
                                                                </button>
                                                                <a href="<?= base_url('Page/deleteSupportIssueComment?comment_id=' . $commentId . '&issue_id=' . (int) ($issue->id ?? 0)); ?>" class="btn-delete" onclick="return confirm('Are you sure you want to delete this comment?');">
                                                                    <i class="fas fa-trash"></i> Delete
                                                                </a>
                                                            </div>
                                                            <div class="edit-form" id="edit-form-<?= $commentId; ?>">
                                                                <form method="post" action="<?= base_url('Page/editSupportIssueComment'); ?>">
                                                                    <input type="hidden" name="comment_id" value="<?= $commentId; ?>">
                                                                    <input type="hidden" name="issue_id" value="<?= (int) ($issue->id ?? 0); ?>">
                                                                    <textarea name="comment" required><?= htmlspecialchars((string) ($comment->comment ?? ''), ENT_QUOTES, 'UTF-8'); ?></textarea>
                                                                    <div class="edit-actions">
                                                                        <button type="submit" class="btn-save">Save</button>
                                                                        <button type="button" class="btn-cancel" onclick="toggleEditForm(<?= $commentId; ?>)">Cancel</button>
                                                                    </div>
                                                                </form>
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php else: ?>
                                            <p style="color:var(--text-soft); text-align:center; padding:30px 0;">No comments yet.</p>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <div class="alert alert-warning mb-0">You are not allowed to view ticket chats.</div>
                                    <?php endif; ?>

                                    <?php if ($canViewChat && $canReplyChat): ?>
                                        <form method="post" action="<?= base_url('Page/addSupportIssueComment'); ?>" enctype="multipart/form-data" style="margin-top:20px;">
                                            <input type="hidden" name="issue_id" value="<?= (int) ($issue->id ?? 0); ?>">
                                            <div class="form-group">
                                                <label for="comment">Add Comment</label>
                                                <textarea class="form-control" id="comment" name="comment" rows="4" required></textarea>
                                            </div>
                                            <div class="form-group">
                                                <label for="comment_attachment">Attachment <small class="text-muted">(optional)</small></label>
                                                <input type="file" class="form-control" id="comment_attachment" name="comment_attachment" accept=".pdf,.png,.jpg,.jpeg,.doc,.docx">
                                                <small class="form-text text-muted">Max file size: 5MB. Allowed: PDF, PNG, JPG, Word (DOC/DOCX)</small>
                                            </div>
                                            <button type="submit" class="btn btn-primary">Post Comment</button>
                                        </form>
                                    <?php elseif ($canViewChat): ?>
                                        <div class="alert alert-info mb-0">You can view this ticket chat, but you are not allowed to reply.</div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-4">
                            <div class="panel-card">
                                <div class="panel-header">
                                    <h2 class="panel-title">Forward Issue</h2>
                                </div>
                                <div class="panel-body">
                                    <?php if (!empty($isStaffUser) && (int) ($issue->assigned_employee_id ?? 0) <= 0): ?>
                                        <form method="post" action="<?= base_url('Page/forwardSupportIssue'); ?>" style="margin-bottom:16px;">
                                            <input type="hidden" name="issue_id" value="<?= (int) ($issue->id ?? 0); ?>">
                                            <input type="hidden" name="forward_to" value="<?= (int) ($currentUserId ?? 0); ?>">
                                            <button type="submit" class="btn btn-primary btn-block">Assign to Me</button>
                                        </form>
                                    <?php endif; ?>
                                    <form method="post" action="<?= base_url('Page/forwardSupportIssue'); ?>">
                                        <input type="hidden" name="issue_id" value="<?= (int) ($issue->id ?? 0); ?>">
                                        <div class="form-group">
                                            <label for="forward_to">Forward To</label>
                                            <select class="form-control" id="forward_to" name="forward_to" required>
                                                <option value="">Select employee</option>
                                                <?php foreach ($assignableUsers as $user): ?>
                                                    <option value="<?= (int) ($user->user_id ?? 0); ?>">
                                                        <?= htmlspecialchars(supportStaffDisplayName($user), ENT_QUOTES, 'UTF-8'); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                                <?php if (empty($assignableUsers)): ?>
                                                    <option value="">No employees available</option>
                                                <?php endif; ?>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label for="forward_note">Note</label>
                                            <textarea class="form-control" id="forward_note" name="forward_note" rows="3"></textarea>
                                        </div>
                                        <button type="submit" class="btn btn-warning btn-block">Forward</button>
                                    </form>
                                </div>
                            </div>

                            <div class="panel-card">
                                <div class="panel-header">
                                    <h2 class="panel-title">Tag Personnel</h2>
                                </div>
                                <div class="panel-body">
                                    <form method="post" action="<?= base_url('Page/tagSupportIssueUser'); ?>">
                                        <input type="hidden" name="issue_id" value="<?= (int) ($issue->id ?? 0); ?>">
                                        <div class="form-group">
                                            <label for="tag_user_ids">Tag User</label>
                                            <select class="form-control" id="tag_user_ids" name="tag_user_ids[]" multiple>
                                                <?php foreach ($taggableUsers as $user): ?>
                                                    <?php $tagUserId = (int) ($user->user_id ?? 0); ?>
                                                    <option value="<?= $tagUserId; ?>">
                                                        <?= htmlspecialchars(supportStaffDisplayName($user), ENT_QUOTES, 'UTF-8'); ?><?= $tagUserId === $currentUserId ? ' (You)' : ''; ?>
                                                    </option>
                                                <?php endforeach; ?>
                                                <?php if (empty($taggableUsers)): ?>
                                                    <option value="">No staff available</option>
                                                <?php endif; ?>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label for="tag_note">Message</label>
                                            <textarea class="form-control" id="tag_note" name="tag_note" rows="3"></textarea>
                                        </div>
                                        <?php if ($currentUserId > 0): ?>
                                            <div class="form-group" style="margin-bottom:10px;">
                                                <button type="submit" class="btn btn-soft btn-block" name="tag_user_id" value="<?= $currentUserId; ?>">Tag Myself</button>
                                            </div>
                                        <?php endif; ?>
                                        <button type="submit" class="btn btn-info btn-block">Tag User</button>
                                    </form>
                                </div>
                            </div>

                            <div class="panel-card">
                                <div class="panel-header">
                                    <h2 class="panel-title">Close Ticket</h2>
                                </div>
                                <div class="panel-body">
                                    <form method="post" action="<?= base_url('Page/closeSupportIssue'); ?>">
                                        <input type="hidden" name="issue_id" value="<?= (int) ($issue->id ?? 0); ?>">
                                        <div class="form-group">
                                            <label for="close_message">Message to Client</label>
                                            <textarea class="form-control" id="close_message" name="close_message" rows="4" required placeholder="Tell the client the issue has been fixed."></textarea>
                                        </div>
                                        <button type="submit" class="btn btn-success btn-block">Close Ticket</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php include('includes/footer.php'); ?>
            </div>
        </div>
    </div>
    <script src="<?= base_url(); ?>assets/js/vendor.min.js"></script>
    <script src="<?= base_url(); ?>assets/libs/select2/select2.min.js"></script>
    <script src="<?= base_url(); ?>assets/js/app.min.js"></script>
    <script>
        if (window.jQuery && jQuery.fn && typeof jQuery.fn.select2 === 'function') {
            jQuery('#tag_user_ids').select2({
                width: '100%',
                placeholder: 'Select employees to tag'
            });
            jQuery('#forward_to').select2({
                width: '100%',
                placeholder: 'Select staff to assign'
            });
        }

        function toggleEditForm(commentId) {
            const editForm = document.getElementById('edit-form-' + commentId);
            const commentBody = document.getElementById('comment-body-' + commentId);
            const chatActions = editForm.previousElementSibling;

            if (editForm.style.display === 'none' || editForm.style.display === '') {
                editForm.style.display = 'block';
                if (commentBody) commentBody.style.display = 'none';
                if (chatActions) chatActions.style.display = 'none';
            } else {
                editForm.style.display = 'none';
                if (commentBody) commentBody.style.display = 'block';
                if (chatActions) chatActions.style.display = 'flex';
            }
        }

    </script>
</body>

</html>
