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
                            --font-body: 'Inter', 'Poppins', 'Segoe UI', Arial, sans-serif;
                            --font-head: 'Inter', 'Montserrat', 'Segoe UI', Arial, sans-serif;
                            font-family: var(--font-body);
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
                            font-family: var(--font-head);
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
                            font-family: var(--font-head);
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
                    </style>

                    <div class="kb-header">
                        <div>
                            <div class="kb-eyebrow">
                                <i class="fas fa-book-open"></i>
                                Knowledge Base
                            </div>
                            <h1 class="kb-title">Search Results</h1>
                        </div>
                        <div>
                            <a href="<?= base_url(); ?>Page/knowledgeBase" class="btn btn-default">
                                <i class="fas fa-arrow-left"></i> Back
                            </a>
                        </div>
                    </div>

                    <div class="kb-card">
                        <div class="kb-card-header">
                            <h3 class="kb-card-title">Search Results for "<?= htmlspecialchars($query, ENT_QUOTES, 'UTF-8'); ?>"</h3>
                        </div>
                        <div class="kb-card-body">
              <?php if (empty($results)): ?>
                <div class="alert alert-info">
                  No results found for your search.
                </div>
              <?php else: ?>
                <p class="text-muted"><?= count($results); ?> result(s) found.</p>
                <div class="row">
                  <?php foreach ($results as $article): ?>
                    <div class="col-md-6 mb-4">
                      <div class="card">
                        <div class="card-body">
                          <h5 class="card-title">
                            <a href="<?= base_url(); ?>Page/knowledgeBaseView/<?= $article->id; ?>">
                              <?= htmlspecialchars($article->title, ENT_QUOTES, 'UTF-8'); ?>
                            </a>
                          </h5>
                          <div class="mb-2">
                            <span class="badge badge-<?= $article->type === 'faq' ? 'info' : 'primary'; ?>">
                              <?= htmlspecialchars(ucfirst($article->type), ENT_QUOTES, 'UTF-8'); ?>
                            </span>
                            <?php if ($article->category): ?>
                              <span class="badge badge-secondary">
                                <?= htmlspecialchars($article->category, ENT_QUOTES, 'UTF-8'); ?>
                              </span>
                            <?php endif; ?>
                          </div>
                          <p class="card-text text-muted">
                            <?= substr(htmlspecialchars($article->content, ENT_QUOTES, 'UTF-8'), 0, 150); ?>...
                          </p>
                          <small class="text-muted">
                            <i class="fas fa-user"></i> <?= htmlspecialchars($article->created_by_name ?? 'Unknown', ENT_QUOTES, 'UTF-8'); ?> |
                            <i class="fas fa-calendar"></i> <?= date('M d, Y', strtotime($article->created_at)); ?> |
                            <i class="fas fa-eye"></i> <?= $article->view_count; ?> views
                          </small>
                        </div>
                      </div>
                    </div>
                  <?php endforeach; ?>
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
