<?php
$form_values = isset($form_values) && is_array($form_values) ? $form_values : array();
$kb_error_message = isset($kb_error) ? (string) $kb_error : (string) ($this->session->flashdata('kb_error') ?? '');
$category_names = array();

if (!empty($categories)) {
    foreach ($categories as $cat) {
        $category_name = trim((string) ($cat->name ?? $cat->category ?? ''));
        if ($category_name !== '') {
            $category_names[] = $category_name;
        }
    }
}

$selected_title = (string) ($form_values['title'] ?? '');
$selected_content = (string) ($form_values['content'] ?? '');
$selected_type = (string) ($form_values['type'] ?? 'article');
$selected_status = (string) ($form_values['status'] ?? 'draft');
$selected_category_raw = trim((string) ($form_values['category'] ?? ''));
$selected_new_category = trim((string) ($form_values['newCategory'] ?? ''));
$selected_category_is_custom = $selected_category_raw !== '' && $selected_category_raw !== 'new' && !in_array($selected_category_raw, $category_names, true);
$selected_category = $selected_category_is_custom ? 'new' : $selected_category_raw;
if ($selected_new_category === '' && $selected_category_is_custom) {
    $selected_new_category = $selected_category_raw;
}
?>
<!DOCTYPE html>
<html lang="en">

<?php include('includes/head.php'); ?>

<!-- Summernote CSS -->
<link href="<?= base_url(); ?>assets/libs/summernote/summernote-bs4.css" rel="stylesheet" type="text/css" />

<body>

<div id="wrapper">

    <?php include('includes/top-nav-bar.php'); ?>
    <?php include('includes/sidebar.php'); ?>

    <div class="content-page">
        <div class="content">
            <div class="container-fluid knowledge-base-page">

                <style>
                    /*
                    IMPORTANT:
                    Do NOT override #wrapper, .content-page, or .content here.
                    Those classes control your main template header/sidebar layout.
                    */

                    .knowledge-base-page {
                        --bg: #f5f7fb;
                        --surface: rgba(255, 255, 255, 0.96);
                        --line: #e7ecf3;
                        --line-strong: #d7e0ec;

                        --text: #122033;
                        --text-soft: #5e7188;
                        --text-faint: #8ea0b5;

                        --primary: #36c2c8;
                        --primary-2: #21aeb5;
                        --primary-soft: #e8fbfc;

                        --success: #059669;
                        --warning: #d97706;
                        --danger: #e11d48;
                        --slate-soft: #f8fafc;

                        --shadow-soft: 0 8px 24px rgba(15, 23, 42, 0.05);

                        --radius-lg: 16px;
                        --radius-md: 12px;
                        --radius-sm: 10px;

                        --font-body: 'Inter', 'Poppins', 'Segoe UI', Arial, sans-serif;
                        --font-head: 'Inter', 'Montserrat', 'Segoe UI', Arial, sans-serif;

                        font-family: var(--font-body);
                        background:
                            radial-gradient(circle at top left, rgba(54, 194, 200, 0.08), transparent 28%),
                            radial-gradient(circle at top right, rgba(37, 99, 235, 0.08), transparent 24%),
                            linear-gradient(180deg, #f8fbff 0%, var(--bg) 100%);
                        min-height: 100vh;
                        padding: 16px 24px 100px;
                    }

                    .knowledge-base-page * {
                        box-sizing: border-box;
                    }

                    /* HEADER */
                    .knowledge-base-page .kb-header {
                        margin: 0 0 16px;
                        display: flex;
                        justify-content: space-between;
                        align-items: center;
                        gap: 16px;
                        flex-wrap: wrap;
                    }

                    .knowledge-base-page .kb-header-left {
                        display: flex;
                        flex-direction: column;
                        gap: 10px;
                    }

                    .knowledge-base-page .kb-eyebrow {
                        width: fit-content;
                        display: inline-flex;
                        align-items: center;
                        gap: 8px;
                        padding: 7px 12px;
                        background: var(--primary-soft);
                        color: var(--primary-2);
                        border-radius: var(--radius-sm);
                        font-size: 0.85rem;
                        font-weight: 700;
                        letter-spacing: 0.3px;
                        text-transform: uppercase;
                    }

                    .knowledge-base-page .kb-title {
                        font-family: var(--font-head);
                        font-size: 1.75rem;
                        font-weight: 800;
                        color: #707070;
                        margin: 0;
                        line-height: 1.2;
                    }

                    .knowledge-base-page .kb-subtitle {
                        margin: 4px 0 0;
                        color: var(--text-soft);
                        font-size: 0.95rem;
                    }

                    /* CARD */
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
                        font-weight: 700;
                        color: #707070;
                        margin: 0;
                    }

                    .knowledge-base-page .kb-card-note {
                        color: var(--text-soft);
                        font-size: 0.9rem;
                        margin: 4px 0 0;
                    }

                    .knowledge-base-page .kb-card-body {
                        padding: 24px;
                    }

                    /* FORM */
                    .knowledge-base-page .form-group {
                        margin-bottom: 18px;
                    }

                    .knowledge-base-page .form-group label {
                        font-weight: 700;
                        color: var(--text);
                        margin-bottom: 8px;
                    }

                    .knowledge-base-page .form-control {
                        border: 1px solid var(--line);
                        border-radius: var(--radius-sm);
                        padding: 11px 14px;
                        min-height: 44px;
                        color: var(--text);
                        background-color: #fff;
                        transition: all 0.2s ease;
                    }

                    .knowledge-base-page textarea.form-control {
                        min-height: 260px;
                        resize: vertical;
                        line-height: 1.6;
                    }

                    .knowledge-base-page .form-control:focus {
                        border-color: var(--primary);
                        box-shadow: 0 0 0 3px rgba(54, 194, 200, 0.15);
                    }

                    .knowledge-base-page .form-text {
                        color: var(--text-faint);
                        font-size: 0.85rem;
                    }

                    /* BUTTONS */
                    .knowledge-base-page .btn {
                        border-radius: 0;
                        padding: 10px 18px;
                        font-weight: 600;
                        transition: all 0.2s ease;
                    }

                    .knowledge-base-page .btn-primary {
                        background: var(--primary);
                        border-color: var(--primary);
                        color: #fff;
                    }

                    .knowledge-base-page .btn-primary:hover {
                        background: var(--primary-2);
                        border-color: var(--primary-2);
                        color: #fff;
                    }

                    .knowledge-base-page .btn-default {
                        background: #fff;
                        border: 1px solid var(--line-strong);
                        color: var(--text-soft);
                    }

                    .knowledge-base-page .btn-default:hover {
                        background: #f8fafc;
                        border-color: var(--primary);
                        color: var(--primary-2);
                    }

                    .knowledge-base-page .kb-actions {
                        display: flex;
                        justify-content: flex-end;
                        align-items: center;
                        gap: 10px;
                        flex-wrap: wrap;
                        padding-top: 18px;
                        margin-top: 10px;
                        border-top: 1px solid var(--line);
                    }

                    /* RESPONSIVE */
                    @media (max-width: 767.98px) {
                        .knowledge-base-page {
                            padding: 16px 16px 90px;
                        }

                        .knowledge-base-page .kb-header {
                            align-items: flex-start;
                        }

                        .knowledge-base-page .kb-title {
                            font-size: 1.45rem;
                        }

                        .knowledge-base-page .kb-card-header,
                        .knowledge-base-page .kb-card-body {
                            padding: 18px;
                        }

                        .knowledge-base-page .kb-actions {
                            justify-content: stretch;
                        }

                        .knowledge-base-page .kb-actions .btn {
                            width: 100%;
                        }
                    }
                </style>

                <!-- PAGE HEADER -->
                <div class="kb-header">
                    <div class="kb-header-left">
                        <div class="kb-eyebrow">
                            <i class="fas fa-book-open"></i>
                            Knowledge Base
                        </div>

                        <div>
                            <h1 class="kb-title">Create Knowledge Base Article</h1>
                            <p class="kb-subtitle">
                                Add a new article or FAQ entry for your knowledge base.
                            </p>
                        </div>
                    </div>

                    <div class="kb-header-right">
                        <a href="<?= base_url('Page/knowledgeBase'); ?>" class="btn btn-default">
                            <i class="fas fa-arrow-left"></i> Back
                        </a>
                    </div>
                </div>

                <!-- FORM CARD -->
                <div class="kb-card">
                    <div class="kb-card-header">
                        <div>
                            <h3 class="kb-card-title">New Article</h3>
                            <p class="kb-card-note">
                                Fields marked with <span class="text-danger">*</span> are required.
                            </p>
                        </div>
                    </div>

                    <div class="kb-card-body">

                        <?php if ($kb_error_message !== ''): ?>
                            <div class="alert alert-danger">
                                <?= htmlspecialchars($kb_error_message, ENT_QUOTES, 'UTF-8'); ?>
                            </div>
                        <?php endif; ?>

                        <?= form_open_multipart('Page/knowledgeBaseCreate', ['id' => 'knowledgeBaseForm']); ?>

                            <!-- TITLE -->
                            <div class="form-group">
                                <label for="title">
                                    Title <span class="text-danger">*</span>
                                </label>
                                <input
                                    type="text"
                                    name="title"
                                    id="title"
                                    class="form-control"
                                    placeholder="Enter article title"
                                    value="<?= htmlspecialchars($selected_title, ENT_QUOTES, 'UTF-8'); ?>"
                                    required
                                >
                            </div>

                            <!-- TYPE AND CATEGORY -->
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="type">
                                            Type <span class="text-danger">*</span>
                                        </label>
                                        <select name="type" id="type" class="form-control" required>
                                            <option value="article" <?= $selected_type === 'article' ? 'selected' : ''; ?>>Article</option>
                                            <option value="faq" <?= $selected_type === 'faq' ? 'selected' : ''; ?>>FAQ</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="category">Category</label>
                                        <select name="category" id="category" class="form-control">
                                            <option value="">-- Select Category --</option>

                                            <?php if (!empty($categories)): ?>
                                                <?php foreach ($categories as $cat): ?>
                                                    <?php $category_name = trim((string) ($cat->name ?? $cat->category ?? '')); ?>
                                                    <option value="<?= htmlspecialchars($category_name, ENT_QUOTES, 'UTF-8'); ?>" <?= $selected_category === $category_name ? 'selected' : ''; ?>>
                                                        <?= htmlspecialchars($category_name, ENT_QUOTES, 'UTF-8'); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            <?php endif; ?>

                                            <option value="new" <?= $selected_category === 'new' ? 'selected' : ''; ?>>+ Add New Category</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- NEW CATEGORY -->
                            <div class="form-group" id="newCategoryGroup" style="display: <?= $selected_category === 'new' ? 'block' : 'none'; ?>;">
                                <label for="newCategory">New Category</label>
                                <input
                                    type="text"
                                    name="newCategory"
                                    id="newCategory"
                                    class="form-control"
                                    placeholder="Enter new category name"
                                    value="<?= htmlspecialchars($selected_new_category, ENT_QUOTES, 'UTF-8'); ?>"
                                    <?= $selected_category === 'new' ? 'required' : ''; ?>
                                >
                                <small class="form-text">
                                    This will be used as the category for this article.
                                </small>
                            </div>

                            <!-- CONTENT -->
                            <div class="form-group">
                                <label for="content">
                                    Content <span class="text-danger">*</span>
                                </label>
                                <textarea
                                    name="content"
                                    id="content"
                                    class="form-control"
                                    placeholder="Write the article content here..."
                                    required
                                ><?= htmlspecialchars($selected_content, ENT_QUOTES, 'UTF-8'); ?></textarea>
                            </div>

                            <div class="form-group">
                                <label for="attachment">PDF Attachment</label>
                                <input
                                    type="file"
                                    name="attachment"
                                    id="attachment"
                                    class="form-control-file"
                                    accept=".pdf,application/pdf"
                                >
                                <small class="form-text">
                                    Optional. Upload one PDF file up to 10MB.
                                </small>
                            </div>

                            <!-- STATUS -->
                            <div class="form-group">
                                <label for="status">Status</label>
                                <select name="status" id="status" class="form-control">
                                    <option value="draft" <?= $selected_status === 'draft' ? 'selected' : ''; ?>>Draft</option>
                                    <option value="published" <?= $selected_status === 'published' ? 'selected' : ''; ?>>Published</option>
                                </select>
                            </div>

                            <!-- ACTIONS -->
                            <div class="kb-actions">
                                <a href="<?= base_url('Page/knowledgeBase'); ?>" class="btn btn-default">
                                    Cancel
                                </a>

                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Save Article
                                </button>
                            </div>

                        <?= form_close(); ?>

                    </div>
                </div>

            </div>
        </div>
    </div>

</div>

<!-- Summernote JS -->
<script src="<?= base_url(); ?>assets/libs/summernote/summernote-bs4.min.js"></script>

<script src="<?= base_url(); ?>assets/js/vendor.min.js"></script>
<script src="<?= base_url(); ?>assets/js/app.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Summernote WYSIWYG editor
    $('#content').summernote({
        placeholder: 'Write the article content here...',
        height: 300,
        minHeight: 200,
        maxHeight: 600,
        toolbar: [
            ['style', ['style']],
            ['font', ['bold', 'italic', 'underline', 'clear']],
            ['fontname', ['fontname']],
            ['fontsize', ['fontsize']],
            ['color', ['color']],
            ['para', ['ul', 'ol', 'paragraph']],
            ['insert', ['link', 'picture', 'video']],
            ['view', ['fullscreen', 'codeview', 'help']]
        ],
        callbacks: {
            onPaste: function(e) {
                var bufferText = ((e.originalEvent || e).clipboardData || window.clipboardData).getData('Text');
                e.preventDefault();
                document.execCommand('insertText', false, bufferText);
            }
        }
    });
});
</script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('knowledgeBaseForm');
    const categorySelect = document.getElementById('category');
    const newCategoryGroup = document.getElementById('newCategoryGroup');
    const newCategoryInput = document.getElementById('newCategory');

    if (!form || !categorySelect || !newCategoryGroup || !newCategoryInput) {
        return;
    }

    categorySelect.addEventListener('change', function () {
        const isNewCategory = this.value === 'new';

        newCategoryGroup.style.display = isNewCategory ? 'block' : 'none';
        newCategoryInput.required = isNewCategory;

        if (!isNewCategory) {
            newCategoryInput.value = '';
        }
    });

    form.addEventListener('submit', function () {
        if (categorySelect.value === 'new' && newCategoryInput.value.trim() !== '') {
            const newOption = document.createElement('option');

            newOption.value = newCategoryInput.value.trim();
            newOption.textContent = newCategoryInput.value.trim();
            newOption.selected = true;

            categorySelect.appendChild(newOption);
        }
    });
});
</script>

</body>
</html>
