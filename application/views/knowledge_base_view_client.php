<?php
$attachment_name = trim((string) ($article->attachment_name ?? ''));
?>
<!DOCTYPE html>
<html lang="en">

<?php include('includes/head.php'); ?>

<body>

<div id="wrapper">

    <?php include('includes/top-nav-bar.php'); ?>
    <?php include('includes/sidebar.php'); ?>

    <div class="content-page knowledge-base-shell">
        <div class="content">
            <div class="container-fluid knowledge-base-page">

                <style>

                    .content-page.knowledge-base-shell {
                        padding-top: 0 !important;
                    }

                    .content-page.knowledge-base-shell > .content {
                        margin-top: 0;
                        padding-top: 0;
                    }

                    .knowledge-base-page {
                        --surface: rgba(255, 255, 255, 0.96);
                        --surface-soft: #f8fbff;
                        --line: #e4ebf4;
                        --line-strong: #cfdbea;
                        --text: #142235;
                        --text-soft: #617489;
                        --text-faint: #8ea0b5;
                        --primary: #2563eb;
                        --primary-2: #1d4ed8;
                        --primary-soft: #eaf2ff;
                        --shadow: 0 16px 40px rgba(15, 23, 42, 0.06);
                        --shadow-soft: 0 8px 24px rgba(15, 23, 42, 0.04);
                        --radius-xl: 16px;
                        --radius-lg: 12px;
                        --radius-md: 10px;
                        --font-body: var(--font-primary);
                        --font-head: var(--font-primary);
                        background:
                            radial-gradient(circle at top left, rgba(37, 99, 235, 0.08), transparent 28%),
                            radial-gradient(circle at top right, rgba(16, 185, 129, 0.08), transparent 24%),
                            linear-gradient(180deg, #f8fbff 0%, #f4f7fb 100%);
                        display: flow-root;
                        min-height: 100vh;
                        padding-top: 0;
                        padding-bottom: 100px;
                        font-family: var(--font-primary, Montserrat, Segoe UI, Arial, sans-serif);
                    }

                    .knowledge-base-page * {
                        box-sizing: border-box;
                    }

                    .knowledge-base-page .kb-page-header {
                        display: flex;
                        justify-content: space-between;
                        align-items: flex-start;
                        gap: 16px;
                        margin: 8px 0 16px;
                        flex-wrap: wrap;
                    }

                    .knowledge-base-page .kb-page-eyebrow {
                        display: inline-flex;
                        align-items: center;
                        gap: 8px;
                        padding: 7px 12px;
                        border-radius: 999px;
                        background: rgba(37, 99, 235, 0.08);
                        color: var(--primary-2);
                        font-size: 0.74rem;
                        font-weight: 700;
                        letter-spacing: 0.08em;
                        text-transform: uppercase;
                        margin-bottom: 12px;
                    }

                    .knowledge-base-page .kb-page-eyebrow::before {
                        content: '';
                        width: 8px;
                        height: 8px;
                        border-radius: 50%;
                        background: linear-gradient(135deg, var(--primary), var(--primary-2));
                        box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.10);
                    }

                    .knowledge-base-page .kb-page-title {
                        margin: 0;
                        font-family: var(--font-primary, Montserrat, Segoe UI, Arial, sans-serif);
                        font-size: 1.7rem;
                        line-height: 1.2;
                        letter-spacing: -0.03em;
                        font-weight: 700;
                        color: var(--text);
                    }

                    .knowledge-base-page .kb-page-subtitle {
                        margin-top: 6px;
                        color: var(--text-soft);
                        font-size: 0.9rem;
                        max-width: 760px;
                    }

                    .knowledge-base-page .btn {
                        transition: all 0.16s ease;
                    }

                    .knowledge-base-page .btn-default {
                        background: #fff;
                        border: 1px solid var(--line);
                        color: var(--text-soft);
                        border-radius: 10px;
                    }

                    .knowledge-base-page .btn-primary {
                        background: linear-gradient(135deg, var(--primary), var(--primary-2));
                        border-color: transparent;
                        color: #fff;
                        border-radius: 10px;
                    }

                    .knowledge-base-page .reader-card {
                        background: var(--surface);
                        border: 1px solid rgba(255, 255, 255, 0.72);
                        border-radius: var(--radius-xl);
                        box-shadow: var(--shadow-soft);
                        overflow: hidden;
                    }

                    .knowledge-base-page .reader-head {
                        padding: 22px 24px 0;
                    }

                    .knowledge-base-page .reader-meta {
                        display: flex;
                        align-items: center;
                        gap: 10px;
                        flex-wrap: wrap;
                        margin-bottom: 14px;
                    }

                    .knowledge-base-page .reader-support {
                        display: flex;
                        gap: 16px;
                        flex-wrap: wrap;
                        color: var(--text-faint);
                        font-size: 0.82rem;
                        margin-bottom: 18px;
                    }

                    .knowledge-base-page .reader-support span {
                        display: inline-flex;
                        align-items: center;
                        gap: 6px;
                    }

                    .knowledge-base-page .reader-body {
                        padding: 0 24px 24px;
                    }

                    .knowledge-base-page .article-content {
                        color: var(--text-soft);
                        line-height: 1.8;
                        font-size: 0.96rem;
                        padding-top: 18px;
                        border-top: 1px solid #eef3f8;
                    }

                    .knowledge-base-page .article-content p {
                        margin-bottom: 16px;
                    }

                    .knowledge-base-page .attachment-card {
                        margin-top: 22px;
                        display: flex;
                        align-items: center;
                        justify-content: space-between;
                        gap: 12px;
                        padding: 16px 18px;
                        border-radius: var(--radius-lg);
                        border: 1px solid var(--line);
                        background: var(--surface-soft);
                        flex-wrap: wrap;
                    }

                    .knowledge-base-page .attachment-label {
                        color: var(--text);
                        font-weight: 700;
                    }

                    .knowledge-base-page .attachment-name {
                        color: var(--text-soft);
                        font-size: 0.88rem;
                        margin-top: 4px;
                    }

                    .knowledge-base-page .badge {
                        padding: 6px 12px;
                        border-radius: 999px;
                        font-weight: 700;
                        font-size: 0.76rem;
                    }

                    @media (max-width: 767.98px) {
                        .knowledge-base-page {
                            padding-bottom: 90px;
                        }

                        .knowledge-base-page .kb-page-title {
                            font-size: 1.4rem;
                        }

                        .knowledge-base-page .reader-head,
                        .knowledge-base-page .reader-body {
                            padding-left: 16px;
                            padding-right: 16px;
                        }
                    }
                </style>

                <div class="kb-page-header">
                    <div>
                        <div class="kb-page-eyebrow">Knowledge Base</div>
                        <h4 class="kb-page-title"><?= htmlspecialchars($article->title, ENT_QUOTES, 'UTF-8'); ?></h4>
                        <div class="kb-page-subtitle">Read-only article view for client access.</div>
                    </div>
                    <div>
                        <a href="<?= base_url('Page/knowledgeBase'); ?>" class="btn btn-default">
                            <i class="fas fa-arrow-left"></i> Back to Articles
                        </a>
                    </div>
                </div>

                <div class="reader-card">
                    <div class="reader-head">
                        <div class="reader-meta">
                            <span class="badge badge-<?= $article->type === 'faq' ? 'info' : 'primary'; ?>">
                                <?= htmlspecialchars(ucfirst($article->type), ENT_QUOTES, 'UTF-8'); ?>
                            </span>
                            <?php if (!empty($article->category)): ?>
                                <span class="badge badge-secondary">
                                    <?= htmlspecialchars($article->category, ENT_QUOTES, 'UTF-8'); ?>
                                </span>
                            <?php endif; ?>
                            <?php if (!empty($article->attachment_path)): ?>
                                <span class="badge badge-warning">PDF</span>
                            <?php endif; ?>
                        </div>

                        <div class="reader-support">
                            <span>
                                <i class="fas fa-user"></i>
                                <?= htmlspecialchars($article->created_by_name ?? 'Unknown', ENT_QUOTES, 'UTF-8'); ?>
                            </span>
                            <span>
                                <i class="fas fa-calendar"></i>
                                <?= !empty($article->created_at) ? date('F d, Y g:i A', strtotime($article->created_at)) : '-'; ?>
                            </span>
                            <span>
                                <i class="fas fa-eye"></i>
                                <?= htmlspecialchars($article->view_count ?? 0, ENT_QUOTES, 'UTF-8'); ?> views
                            </span>
                        </div>
                    </div>

                    <div class="reader-body">
                        <div class="article-content">
                            <?= nl2br(htmlspecialchars($article->content, ENT_QUOTES, 'UTF-8')); ?>
                        </div>

                        <?php if (!empty($article->attachment_path)): ?>
                            <div class="attachment-card">
                                <div>
                                    <div class="attachment-label">
                                        <i class="fas fa-file-pdf text-danger"></i> Attached PDF Guide
                                    </div>
                                    <div class="attachment-name">
                                        <?= htmlspecialchars($attachment_name !== '' ? $attachment_name : 'View attached PDF', ENT_QUOTES, 'UTF-8'); ?>
                                    </div>
                                </div>
                                <a href="<?= base_url('Page/knowledgeBaseAttachment/' . $article->id); ?>" target="_blank" rel="noopener noreferrer" class="btn btn-primary">
                                    <i class="fas fa-external-link-alt"></i> Open PDF
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

            </div>
        </div>
    </div>

</div>

<script src="<?= base_url(); ?>assets/js/vendor.min.js"></script>
<script src="<?= base_url(); ?>assets/js/app.min.js"></script>

</body>
</html>
