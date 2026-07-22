<?php
$attachment_name = trim((string) ($article->attachment_name ?? ''));
$current_user_id = $this->session->userdata('user_id') ?? $this->session->userdata('id');
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
            <div class="container-fluid knowledge-base-page">

                    <style>
                        .knowledge-base-page {
                            --bg: #f5f7fb;
                            --surface: rgba(255, 255, 255, 0.94);
                            --line: #e7ecf3;
                            --line-strong: #d7e0ec;
                            --text: #122033;
                            --text-soft: #5e7188;
                            --text-faint: #8ea0b5;
                            --primary: #2563eb;
                            --primary-2: #1d4ed8;
                            --primary-soft: #eaf2ff;
                            --success: #059669;
                            --success-soft: #ecfdf5;
                            --warning: #d97706;
                            --warning-soft: #fff7ed;
                            --danger: #e11d48;
                            --danger-soft: #fff1f2;
                            --slate-soft: #f8fafc;
                            --shadow: 0 14px 40px rgba(15, 23, 42, 0.08);
                            --shadow-soft: 0 8px 24px rgba(15, 23, 42, 0.05);
                            --radius-xl: 22px;
                            --radius-lg: 16px;
                            --radius-md: 12px;
                            --radius-sm: 10px;
                            --font-body: var(--font-primary);
                            --font-head: var(--font-primary);
                            font-family: var(--font-primary, Montserrat, Segoe UI, Arial, sans-serif);
                            background:
                                radial-gradient(circle at top left, rgba(37, 99, 235, 0.08), transparent 28%),
                                radial-gradient(circle at top right, rgba(16, 185, 129, 0.08), transparent 24%),
                                linear-gradient(180deg, #f8fbff 0%, #f4f7fb 100%);
                            min-height: 100vh;
                            padding-bottom: 24px;
                        }

                        .knowledge-base-page * {
                            box-sizing: border-box;
                        }

                        .knowledge-base-page .kb-header {
                            margin: 24px 0 18px;
                            display: flex;
                            justify-content: space-between;
                            align-items: flex-end;
                            gap: 16px;
                            flex-wrap: wrap;
                        }

                        .knowledge-base-page .kb-eyebrow {
                            display: inline-flex;
                            align-items: center;
                            gap: 8px;
                            padding: 7px 12px;
                            background: var(--primary-soft);
                            color: var(--primary);
                            border-radius: var(--radius-sm);
                            font-size: 0.85rem;
                            font-weight: 600;
                            letter-spacing: 0.3px;
                            text-transform: uppercase;
                        }

                        .knowledge-base-page .kb-title {
                            font-family: var(--font-primary, Montserrat, Segoe UI, Arial, sans-serif);
                            font-size: 1.75rem;
                            font-weight: 700;
                            color: var(--text);
                            margin: 0;
                            line-height: 1.2;
                        }

                        .knowledge-base-page .kb-card {
                            background: var(--surface);
                            border: 1px solid var(--line);
                            border-radius: var(--radius-lg);
                            box-shadow: var(--shadow-soft);
                            margin-bottom: 20px;
                            overflow: hidden;
                        }

                        .knowledge-base-page .kb-card-header {
                            padding: 20px 24px;
                            border-bottom: 1px solid var(--line);
                            display: flex;
                            justify-content: space-between;
                            align-items: center;
                            gap: 16px;
                            flex-wrap: wrap;
                        }

                        .knowledge-base-page .kb-card-title {
                            font-family: var(--font-primary, Montserrat, Segoe UI, Arial, sans-serif);
                            font-size: 1.15rem;
                            font-weight: 600;
                            color: var(--text);
                            margin: 0;
                        }

                        .knowledge-base-page .kb-card-body {
                            padding: 24px;
                        }

                        .knowledge-base-page .btn-primary {
                            background: var(--primary);
                            border-color: var(--primary);
                            color: white;
                            border-radius: var(--radius-sm);
                            padding: 10px 20px;
                            font-weight: 500;
                            transition: all 0.2s ease;
                        }

                        .knowledge-base-page .btn-primary:hover {
                            background: var(--primary-2);
                            border-color: var(--primary-2);
                        }

                        .knowledge-base-page .article-content {
                            line-height: 1.8;
                            color: var(--text-soft);
                        }

                        .knowledge-base-page .article-content p {
                            margin-bottom: 15px;
                        }
                    </style>

                    <style>
                        /* Hero Banner */
                        .knowledge-base-page .kb-hero {
                            display: flex;
                            align-items: center;
                            justify-content: space-between;
                            flex-wrap: wrap;
                            gap: 16px;
                            padding: 28px 24px;
                            margin: 0 0 22px;
                            border-radius: 16px;
                            background: #b45309;
                            box-shadow: 0 8px 32px rgba(180, 83, 9, 0.25);
                            position: relative;
                            overflow: hidden;
                        }

                        .knowledge-base-page .kb-hero::before {
                            content: '';
                            position: absolute;
                            top: -50%;
                            right: -10%;
                            width: 400px;
                            height: 400px;
                            border-radius: 50%;
                            background: rgba(255, 255, 255, 0.06);
                            pointer-events: none;
                        }

                        .knowledge-base-page .kb-hero::after {
                            content: '';
                            position: absolute;
                            bottom: -60%;
                            right: 15%;
                            width: 300px;
                            height: 300px;
                            border-radius: 50%;
                            background: rgba(255, 255, 255, 0.04);
                            pointer-events: none;
                        }

                        .knowledge-base-page .kb-hero__content {
                            position: relative;
                            z-index: 1;
                            max-width: 650px;
                        }

                        .knowledge-base-page .kb-hero__eyebrow {
                            display: inline-flex;
                            align-items: center;
                            gap: 6px;
                            margin-bottom: 8px;
                            color: rgba(255, 255, 255, 0.85);
                            font-size: 0.78rem;
                            font-weight: 600;
                            letter-spacing: 0.04em;
                        }

                        .knowledge-base-page .kb-hero__eyebrow i {
                            font-size: 1rem;
                        }

                        .knowledge-base-page .kb-hero__title {
                            margin: 0 0 4px 0;
                            color: #fff;
                            font-size: clamp(1.4rem, 2.5vw, 2rem);
                            font-weight: 800;
                            line-height: 1.2;
                            letter-spacing: -0.02em;
                            font-family: var(--font-primary, Montserrat, Segoe UI, Arial, sans-serif), "Apple Color Emoji", "Segoe UI Emoji", "Noto Color Emoji", sans-serif;
                        }

                        .knowledge-base-page .kb-hero__subtitle {
                            margin: 0;
                            color: rgba(255, 255, 255, 0.8);
                            font-size: 0.88rem;
                        }

                        .knowledge-base-page .kb-hero__actions {
                            display: flex;
                            align-items: center;
                            flex-wrap: wrap;
                            gap: 10px;
                            position: relative;
                            z-index: 1;
                        }

                        .knowledge-base-page .kb-hero-btn {
                            display: inline-flex;
                            align-items: center;
                            gap: 6px;
                            padding: 8px 16px;
                            border-radius: 10px;
                            border: 1px solid rgba(255, 255, 255, 0.3);
                            background: rgba(255, 255, 255, 0.15);
                            color: #fff;
                            font-size: 0.82rem;
                            font-weight: 600;
                            text-decoration: none;
                            cursor: pointer;
                            transition: all 0.18s ease;
                        }

                        .knowledge-base-page .kb-hero-btn:hover,
                        .knowledge-base-page .kb-hero-btn:focus {
                            background: rgba(255, 255, 255, 0.25);
                            border-color: rgba(255, 255, 255, 0.5);
                            color: #fff;
                            text-decoration: none;
                            transform: translateY(-1px);
                        }

                        .knowledge-base-page .kb-hero-btn--solid {
                            border-color: rgba(255, 255, 255, 0.6);
                            background: rgba(255, 255, 255, 0.95);
                            color: #b45309;
                            font-weight: 700;
                        }

                        .knowledge-base-page .kb-hero-btn--solid:hover,
                        .knowledge-base-page .kb-hero-btn--solid:focus {
                            background: #fff;
                            color: #92400e;
                        }

                        .knowledge-base-page .book-pulse {
                            display: inline-block;
                            animation: book-pulse 2s ease-in-out infinite;
                        }

                        @keyframes book-pulse {
                            0%, 70%, 100% { transform: scale(1); }
                            15% { transform: scale(1.15); }
                            30% { transform: scale(1); }
                            45% { transform: scale(1.08); }
                            60% { transform: scale(1); }
                        }

                        .knowledge-base-page .kb-card {
                            border-top: 3px solid #b45309;
                        }

                        @media (max-width: 767px) {
                            .knowledge-base-page .kb-hero,
                            .knowledge-base-page .kb-hero__actions {
                                flex-direction: column;
                                align-items: stretch;
                            }

                            .knowledge-base-page .kb-hero {
                                padding: 20px;
                            }

                            .knowledge-base-page .kb-hero-btn {
                                flex: 1 1 auto;
                                justify-content: center;
                            }
                        }
                    </style>

                    <div class="kb-hero">
                        <div class="kb-hero__content">
                            <div class="kb-hero__eyebrow">
                                <i class="mdi mdi-book-open-page-variant"></i>
                                Knowledge Base
                            </div>
                            <h1 class="kb-hero__title"><?= htmlspecialchars($article->title, ENT_QUOTES, 'UTF-8'); ?> <span class="book-pulse">📖</span></h1>
                            <p class="kb-hero__subtitle">Viewing article details and content.</p>
                        </div>
                        <div class="kb-hero__actions">
                            <a class="kb-hero-btn" href="<?= base_url(); ?>Page/knowledgeBase">
                                <i class="mdi mdi-arrow-left"></i>
                                <span>Back</span>
                            </a>
                            <?php if ($article->created_by == $current_user_id): ?>
                                <a class="kb-hero-btn kb-hero-btn--solid" href="<?= base_url(); ?>Page/knowledgeBaseEdit/<?= $article->id; ?>">
                                    <i class="mdi mdi-pencil-outline"></i>
                                    <span>Edit</span>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="kb-card">
                        <div class="kb-card-header">
                            <h3 class="kb-card-title">Article Details</h3>
                        </div>
                        <div class="kb-card-body">
              <div class="row mb-3">
                <div class="col-md-6">
                  <span class="badge badge-<?= $article->type === 'faq' ? 'info' : 'primary'; ?>">
                    <?= htmlspecialchars(ucfirst($article->type), ENT_QUOTES, 'UTF-8'); ?>
                  </span>
                  <?php if ($article->category): ?>
                    <span class="badge badge-secondary">
                      <?= htmlspecialchars($article->category, ENT_QUOTES, 'UTF-8'); ?>
                    </span>
                  <?php endif; ?>
                  <span class="badge badge-<?= $article->status === 'published' ? 'success' : 'warning'; ?>">
                    <?= htmlspecialchars(ucfirst($article->status), ENT_QUOTES, 'UTF-8'); ?>
                  </span>
                </div>
                <div class="col-md-6 text-right">
                  <small class="text-muted">
                    Created by <?= htmlspecialchars($article->created_by_name ?? 'Unknown', ENT_QUOTES, 'UTF-8'); ?> on <?= date('F d, Y g:i A', strtotime($article->created_at)); ?>
                  </small>
                  <?php if ($article->updated_at): ?>
                    <br>
                    <small class="text-muted">
                      Updated by <?= htmlspecialchars($article->updated_by_name ?? 'Unknown', ENT_QUOTES, 'UTF-8'); ?> on <?= date('F d, Y g:i A', strtotime($article->updated_at)); ?>
                    </small>
                  <?php endif; ?>
                  <br>
                  <small class="text-muted">
                    <i class="fas fa-eye"></i> <?= $article->view_count; ?> views
                  </small>
                </div>
              </div>

              <hr>

              <div class="article-content">
                <?= nl2br(htmlspecialchars($article->content, ENT_QUOTES, 'UTF-8')); ?>
              </div>

              <?php if (!empty($article->attachment_path)): ?>
                <div class="alert alert-light border mt-4 mb-0">
                  <strong><i class="fas fa-file-pdf text-danger"></i> Attachment:</strong>
                  <a href="<?= base_url('Page/knowledgeBaseAttachment/' . $article->id); ?>" target="_blank" rel="noopener noreferrer">
                    <?= htmlspecialchars($attachment_name !== '' ? $attachment_name : 'View attached PDF', ENT_QUOTES, 'UTF-8'); ?>
                  </a>
                </div>
              <?php endif; ?>
            </div>
          </div>
        </div>

            </div>
        </div>
    </div>

    <?php include('includes/footer.php'); ?>

    <script src="<?= base_url(); ?>assets/js/vendor.min.js"></script>
    <script src="<?= base_url(); ?>assets/js/app.min.js"></script>

</div>

</body>
</html>
