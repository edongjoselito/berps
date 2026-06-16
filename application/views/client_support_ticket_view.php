<?php
$issue = isset($issue) ? $issue : null;
$comments = isset($comments) && is_array($comments) ? $comments : array();
$attachments = isset($attachments) && is_array($attachments) ? $attachments : array();

$issueDescription = trim((string) ($issue->description ?? ''));
$issueReferenceLink = trim((string) ($issue->reference_link ?? ''));
$redundantInitialComment = $issueDescription;
if ($issueReferenceLink !== '') {
    $redundantInitialComment .= "\nReference Link: " . $issueReferenceLink;
}
$redundantInitialComment = trim($redundantInitialComment);
$statusKey = strtolower(trim((string) ($issue->status ?? 'open')));

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

$issueLinks = supportMessageLinks($issueDescription . "\n" . $issueReferenceLink);
?>
<!DOCTYPE html>
<html lang="en">
<?php include('includes/head.php'); ?>
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
                        --radius-lg: 18px;
                        font-family: 'Inter', 'Poppins', 'Segoe UI', Arial, sans-serif;
                        background:
                            radial-gradient(circle at top left, rgba(37, 99, 235, 0.08), transparent 28%),
                            radial-gradient(circle at top right, rgba(16, 185, 129, 0.08), transparent 24%),
                            linear-gradient(180deg, #f8fbff 0%, #f4f7fb 100%);
                        min-height: 100vh;
                        padding-bottom: 24px;
                    }
                    .client-profile-page .cp-header { margin: 24px 0 22px; display:flex; justify-content:space-between; align-items:flex-end; gap:16px; flex-wrap:wrap; }
                    .client-profile-page .cp-eyebrow { display:inline-flex; align-items:center; padding:7px 12px; border-radius:999px; background:rgba(37,99,235,.08); color:#1d4ed8; font-size:.76rem; font-weight:700; letter-spacing:.08em; text-transform:uppercase; margin-bottom:10px; }
                    .client-profile-page .cp-title { margin:0; color:var(--text); font-size:clamp(2rem,3vw,2.7rem); line-height:1.05; font-weight:800; }
                    .client-profile-page .cp-subtitle { margin:12px 0 0; color:var(--text-soft); font-size:1rem; max-width:780px; }
                    .client-profile-page .btn-soft, .client-profile-page .btn-warning {
                        display:inline-flex; align-items:center; gap:10px; border-radius:18px; padding:12px 20px; font-weight:700; text-decoration:none;
                        box-shadow:0 10px 26px rgba(15,23,42,.04); border:1px solid var(--line); background:rgba(255,255,255,.9); color:var(--text);
                    }
                    .client-profile-page .btn-link-action {
                        display:inline-flex;
                        align-items:center;
                        gap:8px;
                        border-radius:14px;
                        padding:10px 16px;
                        border:1px solid rgba(37,99,235,.18);
                        background:var(--primary-soft);
                        color:var(--primary);
                        font-weight:700;
                        text-decoration:none;
                        line-height:1.3;
                        word-break:break-word;
                    }
                    .client-profile-page .btn-link-action:hover {
                        text-decoration:none;
                        background:#dbeafe;
                        color:#1d4ed8;
                    }
                    .client-profile-page .btn-warning { background:linear-gradient(135deg,#f59e0b,#d97706); color:#fff; border-color:transparent; }
                    .client-profile-page .layout-grid { display:grid; grid-template-columns:minmax(0, 2fr) minmax(320px, 1fr); gap:22px; }
                    .client-profile-page .panel-card { background:var(--surface); border:1px solid rgba(255,255,255,.75); border-radius:var(--radius-xl); box-shadow:var(--shadow); overflow:hidden; }
                    .client-profile-page .panel-header { padding:24px 28px 18px; border-bottom:1px solid var(--line); }
                    .client-profile-page .panel-title { margin:0; color:var(--text); font-size:1.45rem; font-weight:800; }
                    .client-profile-page .panel-subtitle { margin-top:8px; color:var(--text-soft); font-size:.98rem; }
                    .client-profile-page .panel-body { padding:22px 28px 28px; }
                    .client-profile-page .meta-grid { display:grid; grid-template-columns:repeat(2, minmax(0, 1fr)); gap:16px; margin-bottom:18px; }
                    .client-profile-page .meta-card { border:1px solid var(--line); border-radius:18px; background:#fff; padding:16px 18px; }
                    .client-profile-page .meta-label { color:var(--text-faint); font-size:.76rem; font-weight:800; text-transform:uppercase; letter-spacing:.08em; margin-bottom:6px; }
                    .client-profile-page .meta-value { color:var(--text); font-weight:700; }
                    .client-profile-page .status-pill, .client-profile-page .ticket-pill {
                        display:inline-flex; align-items:center; padding:5px 10px; border-radius:999px; font-size:.74rem; font-weight:700;
                    }
                    .client-profile-page .ticket-pill { background:#f8fbff; border:1px solid var(--line); color:var(--text-soft); }
                    .client-profile-page .status-open, .client-profile-page .status-awaiting_reply, .client-profile-page .status-reopened { background:var(--primary-soft); color:var(--primary); }
                    .client-profile-page .status-assigned, .client-profile-page .status-in_progress { background:#ecfdf3; color:#047857; }
                    .client-profile-page .status-closed, .client-profile-page .status-resolved, .client-profile-page .status-done, .client-profile-page .status-completed { background:var(--success-soft); color:var(--success); }
                    .client-profile-page .status-cancelled, .client-profile-page .status-canceled { background:var(--danger-soft); color:var(--danger); }
                    .client-profile-page .description-box, .client-profile-page .attachment-box, .client-profile-page .timeline-card { border:1px solid var(--line); border-radius:18px; background:#fff; padding:18px; }
                    .client-profile-page .timeline-card + .timeline-card { margin-top:14px; }
                    .client-profile-page .timeline-top { display:flex; justify-content:space-between; gap:12px; margin-bottom:8px; flex-wrap:wrap; }
                    .client-profile-page .muted { color:var(--text-soft); }
                    .client-profile-page .attachment-list { list-style:none; padding:0; margin:0; }
                    .client-profile-page .attachment-list li + li { margin-top:10px; }
                    .client-profile-page .attachment-list a { color:#2563eb; font-weight:700; text-decoration:none; }
                    .client-profile-page .attachment-grid { display:grid; grid-template-columns:repeat(auto-fill, minmax(180px, 1fr)); gap:12px; margin-top:12px; }
                    .client-profile-page .attachment-card { border:1px solid var(--line); border-radius:14px; background:var(--primary-soft); padding:14px; display:flex; flex-direction:column; align-items:center; text-align:center; transition:all 0.2s ease; text-decoration:none; color:var(--text); }
                    .client-profile-page .attachment-card:hover { transform:translateY(-3px); box-shadow:0 8px 20px rgba(15,23,42,.08); border-color:var(--primary); }
                    .client-profile-page .attachment-icon { font-size:2.2rem; margin-bottom:8px; color:var(--primary); }
                    .client-profile-page .attachment-name { font-size:.85rem; font-weight:600; margin-bottom:5px; word-break:break-word; line-height:1.3; }
                    .client-profile-page .attachment-size { font-size:.74rem; color:var(--text-soft); }
                    .client-profile-page .form-control { width:100%; border:1px solid var(--line); border-radius:16px; padding:13px 15px; background:#fff; color:var(--text); min-height:120px; resize:vertical; }
                    .client-profile-page .alert { border:none; border-radius:18px; box-shadow:0 10px 26px rgba(15,23,42,.04); }
                    .client-profile-page .support-chat-message { border-radius:18px; padding:16px 20px; margin-bottom:14px; }
                    .client-profile-page .support-chat-message.client-message { background:#dbeafe; border-left:4px solid #2563eb; }
                    .client-profile-page .support-chat-message.staff-message { background:#d1fae5; border-left:4px solid #059669; }
                    .client-profile-page .support-chat-message .chat-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:8px; flex-wrap:wrap; gap:8px; }
                    .client-profile-page .support-chat-message .chat-author { font-weight:700; font-size:.92rem; }
                    .client-profile-page .support-chat-message .chat-time { color:var(--text-soft); font-size:.78rem; }
                    .client-profile-page .support-chat-message .chat-body { line-height:1.6; word-wrap:break-word; }
                    .client-profile-page .chat-links { display:flex; flex-wrap:wrap; gap:10px; margin-top:12px; }
                    .client-profile-page .btn-primary { background:linear-gradient(135deg,var(--primary),#1d4ed8); color:#fff; border:none; border-radius:18px; padding:12px 20px; font-weight:700; }
                    .client-profile-page .btn-block { width:100%; display:flex; justify-content:center; }
                    @media (max-width: 991px) {
                        .client-profile-page .layout-grid { grid-template-columns:1fr; }
                        .client-profile-page .meta-grid { grid-template-columns:1fr; }
                    }
                </style>

                <div class="cp-header">
                    <div>
                        <div class="cp-eyebrow">Client Portal</div>
                        <h1 class="cp-title">Ticket <?= htmlspecialchars((string) ($issue->ticket_number ?? ''), ENT_QUOTES, 'UTF-8'); ?></h1>
                        <p class="cp-subtitle"><?= htmlspecialchars((string) ($issue->title ?? ''), ENT_QUOTES, 'UTF-8'); ?></p>
                    </div>
                    <a class="btn-soft" href="<?= base_url('Page/clientMyTickets'); ?>"><i class="fas fa-arrow-left"></i>Back to My Tickets</a>
                </div>

                <?php if ($this->session->flashdata('success')): ?>
                    <div class="alert alert-success"><?= $this->session->flashdata('success'); ?></div>
                <?php endif; ?>
                <?php if ($this->session->flashdata('danger')): ?>
                    <div class="alert alert-danger"><?= $this->session->flashdata('danger'); ?></div>
                <?php endif; ?>

                <div class="layout-grid">
                    <div>
                        <div class="panel-card">
                            <div class="panel-header">
                                <h2 class="panel-title">Ticket Details</h2>
                                <div class="panel-subtitle">Current assignment, department, and submitted concern details.</div>
                            </div>
                            <div class="panel-body">
                                <div class="meta-grid">
                                    <div class="meta-card">
                                        <div class="meta-label">Status</div>
                                        <div class="meta-value"><span class="status-pill status-<?= htmlspecialchars($statusKey, ENT_QUOTES, 'UTF-8'); ?>"><?= htmlspecialchars(ucwords(str_replace('_', ' ', $statusKey !== '' ? $statusKey : 'open')), ENT_QUOTES, 'UTF-8'); ?></span></div>
                                    </div>
                                    <div class="meta-card">
                                        <div class="meta-label">Assigned To</div>
                                        <div class="meta-value"><?= htmlspecialchars((string) ($issue->assigned_employee_name ?? 'Waiting for assignment'), ENT_QUOTES, 'UTF-8'); ?></div>
                                    </div>
                                    <div class="meta-card">
                                        <div class="meta-label">Department</div>
                                        <div class="meta-value"><?= htmlspecialchars((string) ($issue->department_name ?? 'N/A'), ENT_QUOTES, 'UTF-8'); ?></div>
                                    </div>
                                    <div class="meta-card">
                                        <div class="meta-label">Project</div>
                                        <div class="meta-value"><?= htmlspecialchars((string) (!empty($issue->projectDescription) ? $issue->projectDescription : 'General'), ENT_QUOTES, 'UTF-8'); ?></div>
                                    </div>
                                </div>

                                <div class="description-box">
                                    <div class="meta-label">Submitted Details</div>
                                    <div><?= nl2br(htmlspecialchars((string) ($issue->description ?? ''), ENT_QUOTES, 'UTF-8')); ?></div>
                                    <?php if (!empty($issueLinks)): ?>
                                        <div class="chat-links" style="margin-top:12px;">
                                            <?php foreach ($issueLinks as $issueLink): ?>
                                                <a class="btn-link-action" href="<?= htmlspecialchars($issueLink, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener noreferrer">
                                                    <i class="fas fa-up-right-from-square"></i>
                                                    Reference Link
                                                </a>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <?php if (!empty($attachments)): ?>
                                    <div class="attachment-box" style="margin-top:18px;">
                                        <div class="meta-label">Attachments</div>
                                        <div class="attachment-grid">
                                            <?php foreach ($attachments as $attachment): ?>
                                                <a class="attachment-card" href="<?= base_url((string) ($attachment->file_path ?? '')); ?>" target="_blank" rel="noopener noreferrer">
                                                    <i class="fas <?= htmlspecialchars(getAttachmentIcon((string) ($attachment->file_name ?? '')), ENT_QUOTES, 'UTF-8'); ?> attachment-icon"></i>
                                                    <div class="attachment-size"><?= htmlspecialchars(formatFileSize((int) ($attachment->file_size ?? 0)), ENT_QUOTES, 'UTF-8'); ?></div>
                                                </a>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="panel-card" style="margin-top:22px;">
                            <div class="panel-header">
                                <h2 class="panel-title">Ticket Updates</h2>
                                <div class="panel-subtitle">A timeline of client and support responses for this ticket.</div>
                            </div>
                            <div class="panel-body">
                                <?php if (!empty($comments)): ?>
                                    <?php foreach ($comments as $comment): ?>
                                        <?php
                                        $commentBody = trim((string) ($comment->comment ?? ''));
                                        $isRedundantInitialComment = !empty($comment->customer_comment) && $redundantInitialComment !== '' && $commentBody === $redundantInitialComment;
                                        if ($isRedundantInitialComment) {
                                            continue;
                                        }
                                        $commentLinks = supportMessageLinks($commentBody);
                                        $messageClass = !empty($comment->customer_comment) ? 'client-message' : 'staff-message';
                                        $authorName = !empty($comment->customer_comment) ? htmlspecialchars((string) ($comment->customer_name ?? 'You'), ENT_QUOTES, 'UTF-8') : htmlspecialchars((string) ($comment->employee_name ?? 'Support Team'), ENT_QUOTES, 'UTF-8');
                                        ?>
                                        <div class="support-chat-message <?= $messageClass; ?>">
                                            <div class="chat-header">
                                                <span class="chat-author"><?= $authorName; ?></span>
                                                <span class="chat-time"><?= !empty($comment->created_at) ? htmlspecialchars(date('M d, Y h:i A', strtotime((string) $comment->created_at)), ENT_QUOTES, 'UTF-8') : ''; ?></span>
                                            </div>
                                            <div class="chat-body"><?= nl2br(htmlspecialchars($commentBody, ENT_QUOTES, 'UTF-8')); ?></div>
                                            <?php if (!empty($commentLinks)): ?>
                                                <div class="chat-links">
                                                    <?php foreach ($commentLinks as $commentLink): ?>
                                                        <a class="btn-link-action" href="<?= htmlspecialchars($commentLink, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener noreferrer">
                                                            <i class="fas fa-up-right-from-square"></i>
                                                            Reference Link
                                                        </a>
                                                    <?php endforeach; ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="timeline-card muted">No updates yet.</div>
                                <?php endif; ?>

                                <?php if (!in_array($statusKey, array('closed', 'resolved', 'done', 'completed'), true)): ?>
                                    <div style="margin-top:22px; padding-top:22px; border-top:1px solid var(--line);">
                                        <div class="meta-label" style="margin-bottom:12px;">Post a Reply</div>
                                        <form method="post" action="<?= base_url('Page/submitClientTicketReply'); ?>">
                                            <input type="hidden" name="issue_id" value="<?= (int) ($issue->id ?? 0); ?>">
                                            <textarea class="form-control" name="reply_message" rows="4" placeholder="Type your message here..." required></textarea>
                                            <button type="submit" class="btn-primary btn-block" style="margin-top:14px;"><i class="fas fa-paper-plane"></i> Send Reply</button>
                                        </form>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <div>
                        <div class="panel-card">
                            <div class="panel-header">
                                <h2 class="panel-title">Ticket Summary</h2>
                                <div class="panel-subtitle">Quick reference for this support concern.</div>
                            </div>
                            <div class="panel-body">
                                <div class="meta-card" style="margin-bottom:14px;">
                                    <div class="meta-label">Ticket Number</div>
                                    <div class="meta-value"><span class="ticket-pill"><?= htmlspecialchars((string) ($issue->ticket_number ?? ''), ENT_QUOTES, 'UTF-8'); ?></span></div>
                                </div>
                                <div class="meta-card">
                                    <div class="meta-label">Current Status</div>
                                    <div class="meta-value"><?= htmlspecialchars(ucwords(str_replace('_', ' ', $statusKey !== '' ? $statusKey : 'open')), ENT_QUOTES, 'UTF-8'); ?></div>
                                </div>
                            </div>
                        </div>

                        <?php if (in_array($statusKey, array('closed', 'resolved', 'done', 'completed'), true)): ?>
                            <div class="panel-card" style="margin-top:22px;">
                                <div class="panel-header">
                                    <h2 class="panel-title">Re-open Ticket</h2>
                                    <div class="panel-subtitle">If the issue still needs attention, let the support team know what remains unresolved.</div>
                                </div>
                                <div class="panel-body">
                                    <form method="post" action="<?= base_url('Page/reopenSupportIssue'); ?>">
                                        <input type="hidden" name="issue_id" value="<?= (int) ($issue->id ?? 0); ?>">
                                        <label for="reopen_message" class="meta-label" style="display:block;">Tell us what still needs attention</label>
                                        <textarea class="form-control" id="reopen_message" name="reopen_message" rows="4" required></textarea>
                                        <button type="submit" class="btn-warning" style="margin-top:14px; width:100%; justify-content:center;"><i class="fas fa-redo"></i>Re-open Ticket</button>
                                    </form>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php include('includes/footer.php'); ?>
        </div>
    </div>
</div>
<script src="<?= base_url(); ?>assets/js/vendor.min.js"></script>
<script src="<?= base_url(); ?>assets/js/app.min.js"></script>
</body>
</html>
