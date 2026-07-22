<?php
$attachment_name = trim((string) ($article->attachment_name ?? ''));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($article->title, ENT_QUOTES, 'UTF-8'); ?> - Knowledge Base</title>

    <link rel="stylesheet" href="<?= base_url('assets/css/bootstrap.min.css'); ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/icons.min.css'); ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/app.min.css'); ?>">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .public-container {
            max-width: 900px;
            margin: 40px auto;
            padding: 30px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        /* Hero Banner */
        .kb-hero {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 16px;
            padding: 28px 24px;
            margin: 0 0 24px;
            border-radius: 16px;
            background: #b45309;
            box-shadow: 0 8px 32px rgba(180, 83, 9, 0.25);
            position: relative;
            overflow: hidden;
        }
        .kb-hero::before {
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
        .kb-hero::after {
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
        .kb-hero__content {
            position: relative;
            z-index: 1;
            max-width: 650px;
        }
        .kb-hero__eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            margin-bottom: 8px;
            color: rgba(255, 255, 255, 0.85);
            font-size: 0.78rem;
            font-weight: 600;
            letter-spacing: 0.04em;
        }
        .kb-hero__title {
            margin: 0 0 4px 0;
            color: #fff;
            font-size: clamp(1.4rem, 2.5vw, 2rem);
            font-weight: 800;
            line-height: 1.2;
            letter-spacing: -0.02em;
            font-family: "Apple Color Emoji", "Segoe UI Emoji", "Noto Color Emoji", sans-serif;
        }
        .kb-hero__subtitle {
            margin: 0;
            color: rgba(255, 255, 255, 0.8);
            font-size: 0.88rem;
        }
        .kb-hero__actions {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
            position: relative;
            z-index: 1;
        }
        .kb-hero-btn {
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
        .kb-hero-btn:hover,
        .kb-hero-btn:focus {
            background: rgba(255, 255, 255, 0.25);
            border-color: rgba(255, 255, 255, 0.5);
            color: #fff;
            text-decoration: none;
            transform: translateY(-1px);
        }
        .book-pulse {
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
        .article-header {
            border-bottom: 1px solid #e9ecef;
            padding-bottom: 20px;
            margin-bottom: 20px;
        }
        .article-content {
            line-height: 1.8;
            color: #495057;
        }
        .article-content p {
            margin-bottom: 15px;
        }
        @media (max-width: 767px) {
            .kb-hero {
                flex-direction: column;
                align-items: stretch;
                padding: 20px;
            }
            .kb-hero-btn {
                flex: 1 1 auto;
                justify-content: center;
            }
        }
    </style>
    <link rel="stylesheet" href="<?= base_url('assets/css/fonts.css'); ?>">
</head>
<body>

<div class="public-container">
    <div class="kb-hero">
        <div class="kb-hero__content">
            <div class="kb-hero__eyebrow">
                <i class="mdi mdi-book-open-page-variant"></i>
                Knowledge Base
            </div>
            <h1 class="kb-hero__title"><?= htmlspecialchars($article->title, ENT_QUOTES, 'UTF-8'); ?> <span class="book-pulse">📖</span></h1>
            <p class="kb-hero__subtitle">Public article view.</p>
        </div>
        <div class="kb-hero__actions">
            <a href="javascript:history.back()" class="kb-hero-btn">
                <i class="mdi mdi-arrow-left"></i>
                <span>Back</span>
            </a>
        </div>
    </div>

    <div class="article-header">
        <h1><?= htmlspecialchars($article->title, ENT_QUOTES, 'UTF-8'); ?></h1>
        <div class="mt-3">
            <span class="badge badge-<?= $article->type === 'faq' ? 'info' : 'primary'; ?>">
                <?= htmlspecialchars(ucfirst($article->type), ENT_QUOTES, 'UTF-8'); ?>
            </span>
            <?php if ($article->category): ?>
                <span class="badge badge-secondary">
                    <?= htmlspecialchars($article->category, ENT_QUOTES, 'UTF-8'); ?>
                </span>
            <?php endif; ?>
        </div>
        <small class="text-muted mt-2 d-block">
            Created by <?= htmlspecialchars($article->created_by_name ?? 'Unknown', ENT_QUOTES, 'UTF-8'); ?> on <?= date('F d, Y g:i A', strtotime($article->created_at)); ?>
            <?php if ($article->updated_at): ?>
                <br>
                Updated on <?= date('F d, Y g:i A', strtotime($article->updated_at)); ?>
            <?php endif; ?>
            <br>
            <i class="fas fa-eye"></i> <?= $article->view_count; ?> views
        </small>
    </div>

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

    <hr class="mt-4">

    <div class="text-center">
        <p class="text-muted mb-0">
            <small>Powered by BERPS - Business Resource Planning System</small>
        </p>
        <?php if (!$this->session->userdata('logged_in')): ?>
            <p class="mt-2">
                <a href="<?= base_url(); ?>login" class="btn btn-primary btn-sm">Login to access more features</a>
            </p>
        <?php endif; ?>
    </div>
</div>

<script src="<?= base_url('assets/js/vendor.min.js'); ?>"></script>
<script src="<?= base_url('assets/js/app.min.js'); ?>"></script>

</body>
</html>
