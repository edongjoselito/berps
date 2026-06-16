<?php
$articles_list = isset($articles) && is_array($articles) ? $articles : array();
$categories_source = isset($categories) && is_array($categories) ? $categories : array();
$category_entries = array();
$current_type_filter = trim((string) ($this->input->get('type') ?? ''));
$current_category_filter = trim((string) ($this->input->get('category') ?? ''));
$visible_articles_count = count($articles_list);
$article_only_count = 0;
$faq_articles_count = 0;
$pdf_articles_count = 0;

foreach ($categories_source as $kb_category) {
    $category_name = trim((string) ($kb_category->name ?? $kb_category->category ?? ''));
    if ($category_name === '') {
        continue;
    }

    $category_entries[] = array(
        'name' => $category_name,
        'count' => (int) ($kb_category->count ?? 0),
    );
}

foreach ($articles_list as $kb_article) {
    if (($kb_article->type ?? '') === 'faq') {
        $faq_articles_count++;
    } else {
        $article_only_count++;
    }

    if (!empty($kb_article->attachment_path)) {
        $pdf_articles_count++;
    }
}

$category_total_count = count($category_entries);
$active_filter_parts = array();

if ($current_type_filter === 'article') {
    $active_filter_parts[] = 'Articles';
} elseif ($current_type_filter === 'faq') {
    $active_filter_parts[] = 'FAQs';
}

if ($current_category_filter !== '') {
    $active_filter_parts[] = $current_category_filter;
}

$active_filter_label = !empty($active_filter_parts) ? implode(' / ', $active_filter_parts) : 'All published content';
$empty_state_message = !empty($active_filter_parts)
    ? 'No published articles match the current filters.'
    : 'No published articles available yet.';

$type_filter_base_query = array();
if ($current_category_filter !== '') {
    $type_filter_base_query['category'] = $current_category_filter;
}

$all_content_url = base_url('Page/knowledgeBase' . (!empty($type_filter_base_query) ? '?' . http_build_query($type_filter_base_query) : ''));
$articles_only_url = base_url('Page/knowledgeBase?' . http_build_query(array_merge($type_filter_base_query, array('type' => 'article'))));
$faq_only_url = base_url('Page/knowledgeBase?' . http_build_query(array_merge($type_filter_base_query, array('type' => 'faq'))));

$all_categories_query = array();
if ($current_type_filter !== '') {
    $all_categories_query['type'] = $current_type_filter;
}
$all_categories_url = base_url('Page/knowledgeBase' . (!empty($all_categories_query) ? '?' . http_build_query($all_categories_query) : ''));
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
                    @import url('https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,100..1000;1,9..40,100..1000&display=swap');

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
                        --font-body: 'DM Sans', 'Segoe UI', Arial, sans-serif;
                        --font-head: 'DM Sans', 'Segoe UI', Arial, sans-serif;
                        background:
                            radial-gradient(circle at top left, rgba(37, 99, 235, 0.08), transparent 28%),
                            radial-gradient(circle at top right, rgba(16, 185, 129, 0.08), transparent 24%),
                            linear-gradient(180deg, #f8fbff 0%, #f4f7fb 100%);
                        display: flow-root;
                        min-height: 100vh;
                        padding-top: 0;
                        padding-bottom: 100px;
                        font-family: var(--font-body);
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
                        font-family: var(--font-head);
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

                    .knowledge-base-page .stat-visible::before {
                        background: linear-gradient(90deg, #3b82f6, #60a5fa);
                    }

                    .knowledge-base-page .stat-article::before {
                        background: linear-gradient(90deg, #10b981, #34d399);
                    }

                    .knowledge-base-page .stat-faq::before {
                        background: linear-gradient(90deg, #8b5cf6, #a78bfa);
                    }

                    .knowledge-base-page .stat-pdf::before {
                        background: linear-gradient(90deg, #f59e0b, #fbbf24);
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
                        font-family: var(--font-head);
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

                    .knowledge-base-page .filter-card-subtitle,
                    .knowledge-base-page .theme-card-meta {
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

                    .knowledge-base-page .filter-pills,
                    .knowledge-base-page .category-pills {
                        display: flex;
                        gap: 8px;
                        flex-wrap: wrap;
                    }

                    .knowledge-base-page .filter-pill,
                    .knowledge-base-page .category-pill {
                        display: inline-flex;
                        align-items: center;
                        justify-content: center;
                        gap: 8px;
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

                    .knowledge-base-page .filter-pill:hover,
                    .knowledge-base-page .category-pill:hover {
                        color: var(--primary-2);
                        border-color: rgba(37, 99, 235, 0.28);
                        text-decoration: none;
                    }

                    .knowledge-base-page .filter-pill.is-active,
                    .knowledge-base-page .category-pill.is-active {
                        background: var(--primary-soft);
                        border-color: rgba(37, 99, 235, 0.22);
                        color: var(--primary-2);
                    }

                    .knowledge-base-page .category-strip {
                        margin-top: 16px;
                        padding-top: 16px;
                        border-top: 1px solid #eef3f8;
                    }

                    .knowledge-base-page .category-strip-label {
                        color: var(--text-faint);
                        font-size: 0.7rem;
                        font-weight: 700;
                        letter-spacing: 0.08em;
                        text-transform: uppercase;
                        margin-bottom: 10px;
                    }

                    .knowledge-base-page .category-pill-count {
                        display: inline-flex;
                        align-items: center;
                        justify-content: center;
                        min-width: 22px;
                        height: 22px;
                        padding: 0 6px;
                        border-radius: 999px;
                        background: rgba(37, 99, 235, 0.08);
                        color: var(--primary-2);
                        font-size: 0.72rem;
                        font-weight: 700;
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

                    .knowledge-base-page .article-grid {
                        display: grid;
                        grid-template-columns: repeat(2, minmax(0, 1fr));
                        gap: 16px;
                    }

                    .knowledge-base-page .article-card {
                        display: flex;
                        flex-direction: column;
                        justify-content: space-between;
                        min-height: 100%;
                        border: 1px solid var(--line);
                        border-radius: var(--radius-lg);
                        background: linear-gradient(180deg, rgba(255, 255, 255, 0.98), #fdfefe);
                        box-shadow: 0 10px 24px rgba(15, 23, 42, 0.05);
                        overflow: hidden;
                    }

                    .knowledge-base-page .article-card-body {
                        padding: 18px 18px 14px;
                    }

                    .knowledge-base-page .article-card-top {
                        display: flex;
                        align-items: flex-start;
                        justify-content: space-between;
                        gap: 12px;
                        margin-bottom: 12px;
                    }

                    .knowledge-base-page .article-card-badges {
                        display: flex;
                        gap: 8px;
                        flex-wrap: wrap;
                    }

                    .knowledge-base-page .article-card-date {
                        color: var(--text-faint);
                        font-size: 0.74rem;
                        white-space: nowrap;
                    }

                    .knowledge-base-page .article-card-title {
                        margin: 0 0 10px;
                        font-size: 1.02rem;
                        line-height: 1.4;
                        font-weight: 700;
                    }

                    .knowledge-base-page .article-card-title a {
                        color: var(--text);
                        text-decoration: none;
                    }

                    .knowledge-base-page .article-card-title a:hover {
                        color: var(--primary-2);
                    }

                    .knowledge-base-page .article-card-excerpt {
                        margin: 0 0 14px;
                        color: var(--text-soft);
                        font-size: 0.88rem;
                        line-height: 1.65;
                    }

                    .knowledge-base-page .article-card-meta {
                        display: flex;
                        gap: 14px;
                        flex-wrap: wrap;
                        color: var(--text-faint);
                        font-size: 0.78rem;
                    }

                    .knowledge-base-page .article-card-meta span {
                        display: inline-flex;
                        align-items: center;
                        gap: 6px;
                    }

                    .knowledge-base-page .article-card-footer {
                        display: flex;
                        align-items: center;
                        justify-content: space-between;
                        gap: 12px;
                        padding: 14px 18px 18px;
                        border-top: 1px solid #eef3f8;
                        flex-wrap: wrap;
                    }

                    .knowledge-base-page .article-card-helper {
                        display: inline-flex;
                        align-items: center;
                        gap: 6px;
                        color: var(--text-soft);
                        font-size: 0.78rem;
                        font-weight: 600;
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

                    .knowledge-base-page .kb-empty {
                        padding: 28px 24px;
                        text-align: center;
                        color: var(--text-soft);
                        background: #fff;
                        border: 1px dashed var(--line-strong);
                        border-radius: var(--radius-lg);
                    }

                    @media (max-width: 991.98px) {
                        .knowledge-base-page .stats-grid,
                        .knowledge-base-page .article-grid {
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

                        .knowledge-base-page .stats-grid,
                        .knowledge-base-page .article-grid {
                            grid-template-columns: 1fr;
                        }

                        .knowledge-base-page .kb-toolbar {
                            flex-direction: column;
                            align-items: stretch;
                        }

                        .knowledge-base-page .filter-pills,
                        .knowledge-base-page .category-pills {
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

                        .knowledge-base-page .article-card-footer {
                            align-items: stretch;
                        }
                    }
                </style>

                <div class="kb-page-header">
                    <div>
                        <div class="kb-page-eyebrow">Knowledge Base</div>
                        <h4 class="kb-page-title">Client Knowledge Base</h4>
                        <div class="kb-page-subtitle">Browse published articles, FAQs, and attached PDF guides. This client area is read-only and designed for quick viewing.</div>
                    </div>
                </div>

                <div class="stats-grid">
                    <div class="stat-card stat-visible">
                        <div class="stat-label">Visible Now</div>
                        <div class="stat-value"><?= (int) $visible_articles_count; ?></div>
                        <div class="stat-meta">Published items in this view</div>
                    </div>
                    <div class="stat-card stat-article">
                        <div class="stat-label">Articles</div>
                        <div class="stat-value"><?= (int) $article_only_count; ?></div>
                        <div class="stat-meta">Guides and walkthroughs</div>
                    </div>
                    <div class="stat-card stat-faq">
                        <div class="stat-label">FAQs</div>
                        <div class="stat-value"><?= (int) $faq_articles_count; ?></div>
                        <div class="stat-meta">Common questions and answers</div>
                    </div>
                    <div class="stat-card stat-pdf">
                        <div class="stat-label">PDF Guides</div>
                        <div class="stat-value"><?= (int) $pdf_articles_count; ?></div>
                        <div class="stat-meta">Entries with downloadable PDFs</div>
                    </div>
                </div>

                <div class="filter-card">
                    <div class="filter-card-head">
                        <div>
                            <h5 class="filter-card-title">Find Articles</h5>
                            <div class="filter-card-subtitle">Search published content or browse by type and category.</div>
                        </div>
                        <div class="filter-active-chip">
                            <i class="fas fa-filter"></i>
                            <?= htmlspecialchars($active_filter_label, ENT_QUOTES, 'UTF-8'); ?>
                        </div>
                    </div>

                    <div class="kb-toolbar">
                        <div class="kb-search">
                            <form action="<?= base_url('Page/knowledgeBaseSearch'); ?>" method="GET">
                                <?php if ($current_type_filter !== ''): ?>
                                    <input type="hidden" name="type" value="<?= htmlspecialchars($current_type_filter, ENT_QUOTES, 'UTF-8'); ?>">
                                <?php endif; ?>
                                <div class="input-group">
                                    <input
                                        type="text"
                                        name="q"
                                        class="form-control"
                                        placeholder="Search published articles..."
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
                            <a href="<?= $all_content_url; ?>" class="filter-pill <?= $current_type_filter === '' ? 'is-active' : ''; ?>">
                                All
                            </a>
                            <a href="<?= $articles_only_url; ?>" class="filter-pill <?= $current_type_filter === 'article' ? 'is-active' : ''; ?>">
                                Articles
                            </a>
                            <a href="<?= $faq_only_url; ?>" class="filter-pill <?= $current_type_filter === 'faq' ? 'is-active' : ''; ?>">
                                FAQs
                            </a>
                        </div>
                    </div>

                    <?php if (!empty($category_entries)): ?>
                        <div class="category-strip">
                            <div class="category-strip-label">Browse Categories</div>
                            <div class="category-pills">
                                <a href="<?= $all_categories_url; ?>" class="category-pill <?= $current_category_filter === '' ? 'is-active' : ''; ?>">
                                    All Categories
                                    <span class="category-pill-count"><?= (int) $category_total_count; ?></span>
                                </a>

                                <?php foreach ($category_entries as $category_entry): ?>
                                    <?php
                                    $category_query = array();
                                    if ($current_type_filter !== '') {
                                        $category_query['type'] = $current_type_filter;
                                    }
                                    $category_query['category'] = $category_entry['name'];
                                    $category_url = base_url('Page/knowledgeBase?' . http_build_query($category_query));
                                    ?>
                                    <a href="<?= $category_url; ?>" class="category-pill <?= $current_category_filter === $category_entry['name'] ? 'is-active' : ''; ?>">
                                        <?= htmlspecialchars($category_entry['name'], ENT_QUOTES, 'UTF-8'); ?>
                                        <span class="category-pill-count"><?= (int) $category_entry['count']; ?></span>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="card-stack">
                    <div class="theme-card">
                        <div class="theme-card-head">
                            <h5 class="theme-card-title">Published Articles</h5>
                            <div class="theme-card-meta">
                                <?= (int) $visible_articles_count; ?> article<?= $visible_articles_count === 1 ? '' : 's'; ?> available to read
                            </div>
                        </div>

                        <div class="theme-card-body">
                            <?php if (empty($articles_list)): ?>
                                <div class="kb-empty">
                                    <?= htmlspecialchars($empty_state_message, ENT_QUOTES, 'UTF-8'); ?>
                                </div>
                            <?php else: ?>
                                <div class="article-grid">
                                    <?php foreach ($articles_list as $article): ?>
                                        <?php
                                        $article_excerpt = trim((string) preg_replace('/\s+/', ' ', strip_tags((string) ($article->content ?? ''))));
                                        if (strlen($article_excerpt) > 160) {
                                            $article_excerpt = substr($article_excerpt, 0, 157) . '...';
                                        }
                                        ?>
                                        <article class="article-card">
                                            <div class="article-card-body">
                                                <div class="article-card-top">
                                                    <div class="article-card-badges">
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
                                                    <div class="article-card-date">
                                                        <?= !empty($article->created_at) ? date('M d, Y', strtotime($article->created_at)) : '-'; ?>
                                                    </div>
                                                </div>

                                                <h6 class="article-card-title">
                                                    <a href="<?= base_url('Page/knowledgeBaseView/' . $article->id); ?>">
                                                        <?= htmlspecialchars($article->title, ENT_QUOTES, 'UTF-8'); ?>
                                                    </a>
                                                </h6>

                                                <?php if ($article_excerpt !== ''): ?>
                                                    <p class="article-card-excerpt">
                                                        <?= htmlspecialchars($article_excerpt, ENT_QUOTES, 'UTF-8'); ?>
                                                    </p>
                                                <?php endif; ?>

                                                <div class="article-card-meta">
                                                    <span>
                                                        <i class="fas fa-user"></i>
                                                        <?= htmlspecialchars($article->created_by_name ?? 'Unknown', ENT_QUOTES, 'UTF-8'); ?>
                                                    </span>
                                                    <span>
                                                        <i class="fas fa-eye"></i>
                                                        <?= htmlspecialchars($article->view_count ?? 0, ENT_QUOTES, 'UTF-8'); ?> views
                                                    </span>
                                                </div>
                                            </div>

                                            <div class="article-card-footer">
                                                <a href="<?= base_url('Page/knowledgeBaseView/' . $article->id); ?>" class="btn btn-sm btn-primary">
                                                    <i class="fas fa-book-open"></i> Read Article
                                                </a>

                                                <?php if (!empty($article->attachment_path)): ?>
                                                    <span class="article-card-helper">
                                                        <i class="fas fa-file-pdf text-danger"></i>
                                                        Includes PDF guide
                                                    </span>
                                                <?php else: ?>
                                                    <span class="article-card-helper">
                                                        <i class="fas fa-eye"></i>
                                                        Read-only access
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                        </article>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
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
