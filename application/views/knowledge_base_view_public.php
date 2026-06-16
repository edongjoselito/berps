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
        .back-link {
            display: inline-block;
            margin-bottom: 20px;
            color: #007bff;
            text-decoration: none;
        }
        .back-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

<div class="public-container">
    <a href="javascript:history.back()" class="back-link">
        <i class="fas fa-arrow-left"></i> Back
    </a>

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
