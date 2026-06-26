<?php
$my_articles_list = isset($my_articles) && is_array($my_articles) ? $my_articles : array();
$published_articles_list = isset($published_articles) && is_array($published_articles) ? $published_articles : array();
$my_articles_count = count($my_articles_list);
$published_articles_count = count($published_articles_list);
$draft_articles_count = 0;
$faq_articles_count = 0;
$current_type_filter = trim((string) ($this->input->get('type') ?? ''));

foreach ($my_articles_list as $kb_article) {
    if (($kb_article->status ?? '') === 'draft') {
        $draft_articles_count++;
    }
}

foreach ($published_articles_list as $kb_article) {
    if (($kb_article->type ?? '') === 'faq') {
        $faq_articles_count++;
    }
}

$active_filter_label = 'All content';
if ($current_type_filter === 'article') {
    $active_filter_label = 'Articles only';
} elseif ($current_type_filter === 'faq') {
    $active_filter_label = 'FAQs only';
}
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

                <?php if ($this->session->flashdata('kb_success')): ?>
                    <div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
                        <?= htmlspecialchars($this->session->flashdata('kb_success'), ENT_QUOTES, 'UTF-8'); ?>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                <?php endif; ?>

                <?php if ($this->session->flashdata('kb_error')): ?>
                    <div class="alert alert-danger alert-dismissible fade show mt-3" role="alert">
                        <?= htmlspecialchars($this->session->flashdata('kb_error'), ENT_QUOTES, 'UTF-8'); ?>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                <?php endif; ?>

                <style>

                    .content-page.knowledge-base-shell {
                        padding-top: 0 !important;
                    }

                    .content-page.knowledge-base-shell > .content {
                        margin-top: 0;
                        padding-top: 0;
                    }

                    .knowledge-base-page {
                        --bg: #f5f7fb;
                        --surface: rgba(255, 255, 255, 0.96);
                        --surface-strong: #ffffff;
                        --surface-soft: #f8fbff;
                        --line: #e4ebf4;
                        --line-strong: #cfdbea;
                        --text: #142235;
                        --text-soft: #617489;
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
                        --shadow: 0 16px 40px rgba(15, 23, 42, 0.06);
                        --shadow-soft: 0 8px 24px rgba(15, 23, 42, 0.04);
                        --radius-xl: 16px;
                        --radius-lg: 12px;
                        --radius-md: 10px;
                        --radius-sm: 8px;
                        --font-body: var(--font-primary);
                        --font-head: var(--font-primary);
                        --font-mono: var(--font-primary);
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
                        align-items: flex-end;
                        gap: 16px;
                        margin: 8px 0 16px;
                        padding: 0;
                        border: 0;
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
                        font-size: 1.5rem;
                        line-height: 1.2;
                        letter-spacing: -0.02em;
                        font-weight: 700;
                        color: var(--text);
                    }

                    .knowledge-base-page .kb-page-subtitle {
                        margin-top: 6px;
                        color: var(--text-soft);
                        font-size: 0.9rem;
                        max-width: 760px;
                    }

                    .knowledge-base-page .kb-page-actions {
                        display: flex;
                        gap: 12px;
                        flex-wrap: wrap;
                    }

                    .knowledge-base-page .btn-submit {
                        display: inline-flex;
                        align-items: center;
                        gap: 8px;
                        border: none;
                        border-radius: 999px;
                        background: linear-gradient(135deg, var(--primary), var(--primary-2));
                        color: #fff;
                        font-weight: 700;
                        padding: 12px 18px;
                        box-shadow: 0 12px 22px rgba(37, 99, 235, 0.16);
                        transition: transform 0.16s ease, box-shadow 0.16s ease, opacity 0.16s ease;
                    }

                    .knowledge-base-page .btn-submit:hover {
                        color: #fff;
                        text-decoration: none;
                        transform: translateY(-1px);
                        box-shadow: 0 16px 28px rgba(37, 99, 235, 0.2);
                    }

                    .knowledge-base-page .stats-grid {
                        display: grid;
                        grid-template-columns: repeat(4, minmax(0, 1fr));
                        gap: 12px;
                        margin-bottom: 16px;
                    }

                    .knowledge-base-page .stat-card {
                        position: relative;
                        overflow: hidden;
                        background: var(--surface);
                        border: 1px solid rgba(255, 255, 255, 0.72);
                        border-radius: var(--radius-xl);
                        box-shadow: var(--shadow-soft);
                        padding: 14px 16px;
                    }

                    .knowledge-base-page .stat-card::before {
                        content: '';
                        position: absolute;
                        inset: 0 0 auto 0;
                        height: 4px;
                    }

                    .knowledge-base-page .stat-mine::before {
                        background: linear-gradient(90deg, #3b82f6, #60a5fa);
                    }

                    .knowledge-base-page .stat-published::before {
                        background: linear-gradient(90deg, #10b981, #34d399);
                    }

                    .knowledge-base-page .stat-draft::before {
                        background: linear-gradient(90deg, #f59e0b, #fbbf24);
                    }

                    .knowledge-base-page .stat-faq::before {
                        background: linear-gradient(90deg, #8b5cf6, #a78bfa);
                    }

                    .knowledge-base-page .stat-label {
                        color: var(--text-faint);
                        font-size: 0.65rem;
                        font-weight: 600;
                        text-transform: uppercase;
                        letter-spacing: 0.06em;
                        margin-bottom: 8px;
                    }

                    .knowledge-base-page .stat-value {
                        color: var(--text);
                        font-size: 1.25rem;
                        font-weight: 700;
                        line-height: 1.2;
                        letter-spacing: -0.02em;
                        font-family: var(--font-primary, Montserrat, Segoe UI, Arial, sans-serif);
                    }

                    .knowledge-base-page .stat-meta {
                        color: var(--text-soft);
                        font-size: 0.72rem;
                        margin-top: 4px;
                    }

                    .knowledge-base-page .filter-card,
                    .knowledge-base-page .theme-card {
                        background: var(--surface);
                        border: 1px solid rgba(255, 255, 255, 0.72);
                        border-radius: var(--radius-xl);
                        box-shadow: var(--shadow-soft);
                    }

                    .knowledge-base-page .filter-card {
                        margin-bottom: 16px;
                        padding: 16px;
                    }

                    .knowledge-base-page .filter-card-head {
                        display: flex;
                        align-items: center;
                        justify-content: space-between;
                        gap: 12px;
                        margin-bottom: 16px;
                        flex-wrap: wrap;
                    }

                    .knowledge-base-page .filter-card-title,
                    .knowledge-base-page .theme-card-title {
                        margin: 0;
                        color: var(--text);
                        font-weight: 700;
                        font-size: 1rem;
                    }

                    .knowledge-base-page .filter-card-subtitle {
                        color: var(--text-soft);
                        font-size: 0.82rem;
                        margin-top: 4px;
                    }

                    .knowledge-base-page .filter-active-chip {
                        display: inline-flex;
                        align-items: center;
                        gap: 8px;
                        padding: 8px 12px;
                        border-radius: 999px;
                        background: var(--surface-soft);
                        color: var(--text-soft);
                        border: 1px solid var(--line);
                        font-size: 0.78rem;
                        font-weight: 600;
                    }

                    .knowledge-base-page .kb-toolbar {
                        display: flex;
                        align-items: center;
                        justify-content: space-between;
                        gap: 12px;
                        flex-wrap: wrap;
                    }

                    .knowledge-base-page .kb-search {
                        flex: 1 1 320px;
                    }

                    .knowledge-base-page .form-control {
                        border: 1px solid var(--line);
                        border-radius: 12px;
                        min-height: 46px;
                        color: var(--text);
                        box-shadow: none;
                    }

                    .knowledge-base-page .form-control:focus {
                        border-color: rgba(37, 99, 235, 0.38);
                        box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.10);
                    }

                    .knowledge-base-page .input-group-append .btn {
                        border-radius: 0 12px 12px 0;
                        border-color: var(--line);
                        background: #fff;
                        color: var(--text-soft);
                    }

                    .knowledge-base-page .filter-pills {
                        display: flex;
                        gap: 8px;
                        flex-wrap: wrap;
                    }

                    .knowledge-base-page .filter-pill {
                        display: inline-flex;
                        align-items: center;
                        justify-content: center;
                        min-width: 86px;
                        padding: 10px 14px;
                        border-radius: 999px;
                        border: 1px solid var(--line);
                        background: #fff;
                        color: var(--text-soft);
                        font-size: 0.82rem;
                        font-weight: 700;
                        text-decoration: none;
                        transition: all 0.16s ease;
                    }

                    .knowledge-base-page .filter-pill:hover {
                        color: var(--primary-2);
                        border-color: rgba(37, 99, 235, 0.28);
                        text-decoration: none;
                    }

                    .knowledge-base-page .filter-pill.is-active {
                        background: var(--primary-soft);
                        border-color: rgba(37, 99, 235, 0.22);
                        color: var(--primary-2);
                    }

                    .knowledge-base-page .card-stack {
                        display: grid;
                        gap: 16px;
                    }

                    .knowledge-base-page .theme-card-head {
                        padding: 18px 20px 0;
                    }

                    .knowledge-base-page .theme-card-body {
                        padding: 18px 20px 20px;
                    }

                    .knowledge-base-page .nav-tabs {
                        border-bottom: 1px solid var(--line);
                        margin-top: 2px;
                    }

                    .knowledge-base-page .nav-tabs .nav-link {
                        color: var(--text-soft);
                        font-weight: 700;
                        border: none;
                        border-bottom: 2px solid transparent;
                        padding: 12px 14px;
                    }

                    .knowledge-base-page .nav-tabs .nav-link.active {
                        color: var(--primary-2);
                        background: transparent;
                        border-bottom-color: var(--primary);
                    }

                    .knowledge-base-page .table-responsive {
                        border: 1px solid var(--line);
                        border-radius: var(--radius-lg);
                        overflow: hidden;
                        background: #fff;
                    }

                    .knowledge-base-page .table {
                        margin-bottom: 0;
                        background: #fff;
                    }

                    .knowledge-base-page .table thead th {
                        background: #f8fbff;
                        color: var(--text);
                        font-weight: 700;
                        border-bottom: 1px solid var(--line);
                        padding: 14px 16px;
                        white-space: nowrap;
                        vertical-align: middle;
                    }

                    .knowledge-base-page .table tbody td {
                        padding: 14px 16px;
                        border-top: 1px solid #eef3f8;
                        color: var(--text-soft);
                        vertical-align: middle;
                    }

                    .knowledge-base-page .table tbody tr:hover {
                        background: rgba(37, 99, 235, 0.03);
                    }

                    .knowledge-base-page .table a {
                        color: var(--primary-2);
                        font-weight: 700;
                    }

                    .knowledge-base-page .table a:hover {
                        color: var(--primary);
                        text-decoration: none;
                    }

                    .knowledge-base-page .badge {
                        padding: 6px 12px;
                        border-radius: 999px;
                        font-weight: 700;
                        font-size: 0.76rem;
                    }

                    .knowledge-base-page .btn {
                        transition: all 0.16s ease;
                    }

                    .knowledge-base-page .btn-sm {
                        border-radius: 10px;
                        padding: 8px 12px;
                        font-size: 0.82rem;
                    }

                    .knowledge-base-page .btn-primary {
                        background: linear-gradient(135deg, var(--primary), var(--primary-2));
                        border-color: transparent;
                        color: #fff;
                    }

                    .knowledge-base-page .btn-default {
                        background: #fff;
                        border: 1px solid var(--line);
                        color: var(--text-soft);
                    }

                    .knowledge-base-page .btn-info,
                    .knowledge-base-page .btn-danger {
                        border-color: transparent;
                    }

                    .knowledge-base-page .kb-empty {
                        padding: 28px 24px;
                        text-align: center;
                        color: var(--text-soft);
                        background: #fff;
                        border: 1px dashed var(--line-strong);
                        border-radius: var(--radius-lg);
                    }

                    @media (max-width: 991.98px) {
                        .knowledge-base-page .stats-grid {
                            grid-template-columns: repeat(2, minmax(0, 1fr));
                        }
                    }

                    @media (max-width: 767.98px) {
                        .knowledge-base-page {
                            padding-bottom: 90px;
                        }

                        .knowledge-base-page .kb-page-header {
                            align-items: flex-start;
                        }

                        .knowledge-base-page .kb-page-title {
                            font-size: 1.35rem;
                        }

                        .knowledge-base-page .stats-grid {
                            grid-template-columns: 1fr;
                        }

                        .knowledge-base-page .kb-toolbar {
                            flex-direction: column;
                            align-items: stretch;
                        }

                        .knowledge-base-page .filter-pills {
                            width: 100%;
                        }

                        .knowledge-base-page .filter-pill {
                            flex: 1 1 0;
                        }

                        .knowledge-base-page .theme-card-head,
                        .knowledge-base-page .theme-card-body,
                        .knowledge-base-page .filter-card {
                            padding-left: 16px;
                            padding-right: 16px;
                        }

                        .knowledge-base-page .table thead th,
                        .knowledge-base-page .table tbody td {
                            padding: 12px;
                            font-size: 0.88rem;
                        }
                    }
                </style>

                <div class="kb-page-header">
                    <div>
                        <div class="kb-page-eyebrow">Knowledge Base</div>
                        <h4 class="kb-page-title">Knowledge Base & FAQ</h4>
                        <div class="kb-page-subtitle">Manage internal articles, FAQs, and published help content from one place.</div>
                    </div>
                    <div class="kb-page-actions">
                        <a href="<?= base_url('Page/knowledgeBaseCreate'); ?>" class="btn-submit">
                            <i class="mdi mdi-plus-circle-outline"></i>
                            Add New Article
                        </a>
                    </div>
                </div>

                <div class="stats-grid">
                    <div class="stat-card stat-mine">
                        <div class="stat-label">My Articles</div>
                        <div class="stat-value"><?= (int) $my_articles_count; ?></div>
                        <div class="stat-meta">Entries you created</div>
                    </div>
                    <div class="stat-card stat-published">
                        <div class="stat-label">Published</div>
                        <div class="stat-value"><?= (int) $published_articles_count; ?></div>
                        <div class="stat-meta">Visible knowledge base items</div>
                    </div>
                    <div class="stat-card stat-draft">
                        <div class="stat-label">Drafts</div>
                        <div class="stat-value"><?= (int) $draft_articles_count; ?></div>
                        <div class="stat-meta">Still in progress</div>
                    </div>
                    <div class="stat-card stat-faq">
                        <div class="stat-label">FAQs</div>
                        <div class="stat-value"><?= (int) $faq_articles_count; ?></div>
                        <div class="stat-meta">Published FAQ entries</div>
                    </div>
                </div>

                <div class="filter-card">
                    <div class="filter-card-head">
                        <div>
                            <h5 class="filter-card-title">Search and Filter</h5>
                            <div class="filter-card-subtitle">Look up articles quickly or narrow the list by content type.</div>
                        </div>
                        <div class="filter-active-chip">
                            <i class="fas fa-filter"></i>
                            <?= htmlspecialchars($active_filter_label, ENT_QUOTES, 'UTF-8'); ?>
                        </div>
                    </div>

                    <div class="kb-toolbar">
                        <div class="kb-search">
                            <form action="<?= base_url('Page/knowledgeBaseSearch'); ?>" method="GET">
                                <div class="input-group">
                                    <input
                                        type="text"
                                        name="q"
                                        class="form-control"
                                        placeholder="Search articles..."
                                        value="<?= htmlspecialchars($this->input->get('q') ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                    >

                                    <div class="input-group-append">
                                        <button type="submit" class="btn btn-default">
                                            <i class="fas fa-search"></i>
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>

                        <div class="filter-pills">
                            <a href="<?= base_url('Page/knowledgeBase'); ?>" class="filter-pill <?= $current_type_filter === '' ? 'is-active' : ''; ?>">
                                All
                            </a>
                            <a href="<?= base_url('Page/knowledgeBase?type=article'); ?>" class="filter-pill <?= $current_type_filter === 'article' ? 'is-active' : ''; ?>">
                                Articles
                            </a>
                            <a href="<?= base_url('Page/knowledgeBase?type=faq'); ?>" class="filter-pill <?= $current_type_filter === 'faq' ? 'is-active' : ''; ?>">
                                FAQs
                            </a>
                        </div>
                    </div>
                </div>

                <div class="card-stack">
                    <div class="theme-card">
                        <div class="theme-card-head">
                            <h5 class="theme-card-title">Knowledge Base Articles</h5>
                        </div>

                        <div class="theme-card-body">
                        <ul class="nav nav-tabs" id="kbTabs" role="tablist">
                            <li class="nav-item">
                                <a
                                    class="nav-link active"
                                    id="my-articles-tab"
                                    data-toggle="tab"
                                    href="#my-articles"
                                    role="tab"
                                    aria-controls="my-articles"
                                    aria-selected="true"
                                >
                                    My Articles
                                </a>
                            </li>

                            <li class="nav-item">
                                <a
                                    class="nav-link"
                                    id="published-tab"
                                    data-toggle="tab"
                                    href="#published"
                                    role="tab"
                                    aria-controls="published"
                                    aria-selected="false"
                                >
                                    Published Articles
                                </a>
                            </li>
                        </ul>

                        <div class="tab-content pt-3" id="kbTabsContent">

                            <!-- MY ARTICLES TAB -->
                            <div
                                class="tab-pane fade show active"
                                id="my-articles"
                                role="tabpanel"
                                aria-labelledby="my-articles-tab"
                            >
                                <?php if (empty($my_articles)): ?>

                                    <div class="kb-empty">
                                        You haven't created any articles yet.
                                    </div>

                                <?php else: ?>

                                    <div class="table-responsive">
                                        <table class="table table-striped">
                                            <thead>
                                                <tr>
                                                    <th>Title</th>
                                                    <th>Category</th>
                                                    <th>Type</th>
                                                    <th>Status</th>
                                                    <th>Created</th>
                                                    <th>Views</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>

                                            <tbody>
                                                <?php foreach ($my_articles as $article): ?>
                                                    <tr>
                                                        <td>
                                                            <a href="<?= base_url('Page/knowledgeBaseView/' . $article->id); ?>">
                                                                <?= htmlspecialchars($article->title, ENT_QUOTES, 'UTF-8'); ?>
                                                            </a>
                                                        </td>

                                                        <td>
                                                            <?= htmlspecialchars($article->category ?? 'Uncategorized', ENT_QUOTES, 'UTF-8'); ?>
                                                        </td>

                                                        <td>
                                                            <span class="badge badge-<?= $article->type === 'faq' ? 'info' : 'primary'; ?>">
                                                                <?= htmlspecialchars(ucfirst($article->type), ENT_QUOTES, 'UTF-8'); ?>
                                                            </span>
                                                        </td>

                                                        <td>
                                                            <span class="badge badge-<?= $article->status === 'published' ? 'success' : 'warning'; ?>">
                                                                <?= htmlspecialchars(ucfirst($article->status), ENT_QUOTES, 'UTF-8'); ?>
                                                            </span>
                                                        </td>

                                                        <td>
                                                            <?= !empty($article->created_at) ? date('M d, Y', strtotime($article->created_at)) : '-'; ?>
                                                        </td>

                                                        <td>
                                                            <?= htmlspecialchars($article->view_count ?? 0, ENT_QUOTES, 'UTF-8'); ?>
                                                        </td>

                                                        <td>
                                                            <a
                                                                href="<?= base_url('Page/knowledgeBaseEdit/' . $article->id); ?>"
                                                                class="btn btn-sm btn-info"
                                                                title="Edit"
                                                            >
                                                                <i class="fas fa-edit"></i>
                                                            </a>

                                                            <a
                                                                href="<?= base_url('Page/knowledgeBaseDelete/' . $article->id); ?>"
                                                                class="btn btn-sm btn-danger"
                                                                title="Delete"
                                                                onclick="return confirm('Are you sure you want to delete this article?');"
                                                            >
                                                                <i class="fas fa-trash"></i>
                                                            </a>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>

                                <?php endif; ?>
                            </div>

                            <!-- PUBLISHED ARTICLES TAB -->
                            <div
                                class="tab-pane fade"
                                id="published"
                                role="tabpanel"
                                aria-labelledby="published-tab"
                            >
                                <?php if (empty($published_articles)): ?>

                                    <div class="kb-empty">
                                        No published articles yet.
                                    </div>

                                <?php else: ?>

                                    <div class="table-responsive">
                                        <table class="table table-striped">
                                            <thead>
                                                <tr>
                                                    <th>Title</th>
                                                    <th>Category</th>
                                                    <th>Type</th>
                                                    <th>Author</th>
                                                    <th>Created</th>
                                                    <th>Views</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>

                                            <tbody>
                                                <?php foreach ($published_articles as $article): ?>
                                                    <tr>
                                                        <td>
                                                            <a href="<?= base_url('Page/knowledgeBaseView/' . $article->id); ?>">
                                                                <?= htmlspecialchars($article->title, ENT_QUOTES, 'UTF-8'); ?>
                                                            </a>
                                                        </td>

                                                        <td>
                                                            <?= htmlspecialchars($article->category ?? 'Uncategorized', ENT_QUOTES, 'UTF-8'); ?>
                                                        </td>

                                                        <td>
                                                            <span class="badge badge-<?= $article->type === 'faq' ? 'info' : 'primary'; ?>">
                                                                <?= htmlspecialchars(ucfirst($article->type), ENT_QUOTES, 'UTF-8'); ?>
                                                            </span>
                                                        </td>

                                                        <td>
                                                            <?= htmlspecialchars($article->created_by_name ?? 'Unknown', ENT_QUOTES, 'UTF-8'); ?>
                                                        </td>

                                                        <td>
                                                            <?= !empty($article->created_at) ? date('M d, Y', strtotime($article->created_at)) : '-'; ?>
                                                        </td>

                                                        <td>
                                                            <?= htmlspecialchars($article->view_count ?? 0, ENT_QUOTES, 'UTF-8'); ?>
                                                        </td>

                                                        <td>
                                                            <a
                                                                href="<?= base_url('Page/knowledgeBaseView/' . $article->id); ?>"
                                                                class="btn btn-sm btn-primary"
                                                            >
                                                                <i class="fas fa-eye"></i> View
                                                            </a>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>

                                <?php endif; ?>
                            </div>

                        </div>
                        </div>
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
