<?php
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

$current_category = trim((string) ($article->category ?? ''));
$current_category_is_custom = $current_category !== '' && !in_array($current_category, $category_names, true);
$current_category_select = $current_category_is_custom ? 'new' : $current_category;
$current_new_category = $current_category_is_custom ? $current_category : '';
$current_attachment_name = trim((string) ($article->attachment_name ?? ''));
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

                        .knowledge-base-page .form-control {
                            border: 1px solid var(--line);
                            border-radius: var(--radius-sm);
                            padding: 12px 16px;
                        }

                        .knowledge-base-page .form-control:focus {
                            border-color: var(--primary);
                            box-shadow: 0 0 0 3px var(--primary-soft);
                        }

                        .knowledge-base-page .form-group label {
                            font-weight: 500;
                            color: var(--text);
                            margin-bottom: 8px;
                        }
                    </style>

                    <div class="kb-header">
                        <div>
                            <div class="kb-eyebrow">
                                <i class="fas fa-book-open"></i>
                                Knowledge Base
                            </div>
                            <h1 class="kb-title">Edit Knowledge Base Article</h1>
                        </div>
                        <div>
                            <a href="<?= base_url(); ?>Page/knowledgeBase" class="btn btn-default">
                                <i class="fas fa-arrow-left"></i> Back
                            </a>
                        </div>
                    </div>

                    <div class="kb-card">
                        <div class="kb-card-header">
                            <h3 class="kb-card-title">Edit Article</h3>
                        </div>
                        <div class="kb-card-body">
              <?php if ($kb_error_message !== ''): ?>
                <div class="alert alert-danger">
                  <?= htmlspecialchars($kb_error_message, ENT_QUOTES, 'UTF-8'); ?>
                </div>
              <?php endif; ?>

              <?= form_open_multipart('Page/knowledgeBaseEdit/' . $article->id); ?>
                <div class="form-group">
                  <label for="title">Title <span class="text-danger">*</span></label>
                  <input type="text" name="title" id="title" class="form-control" value="<?= htmlspecialchars($article->title, ENT_QUOTES, 'UTF-8'); ?>" required>
                </div>

                <div class="row">
                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="type">Type <span class="text-danger">*</span></label>
                      <select name="type" id="type" class="form-control" required>
                        <option value="article" <?= $article->type === 'article' ? 'selected' : ''; ?>>Article</option>
                        <option value="faq" <?= $article->type === 'faq' ? 'selected' : ''; ?>>FAQ</option>
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
                            <option value="<?= htmlspecialchars($category_name, ENT_QUOTES, 'UTF-8'); ?>" <?= $current_category_select === $category_name ? 'selected' : ''; ?>>
                              <?= htmlspecialchars($category_name, ENT_QUOTES, 'UTF-8'); ?>
                            </option>
                          <?php endforeach; ?>
                        <?php endif; ?>
                        <option value="new" <?= $current_category_select === 'new' ? 'selected' : ''; ?>>+ Add New Category</option>
                      </select>
                    </div>
                  </div>
                </div>

                <div class="form-group" id="newCategoryGroup" style="display: <?= $current_category_select === 'new' ? 'block' : 'none'; ?>;">
                  <label for="newCategory">New Category</label>
                  <input type="text" name="newCategory" id="newCategory" class="form-control" value="<?= htmlspecialchars($current_new_category, ENT_QUOTES, 'UTF-8'); ?>" <?= $current_category_select === 'new' ? 'required' : ''; ?>>
                </div>

                <div class="form-group">
                  <label for="content">Content <span class="text-danger">*</span></label>
                  <textarea name="content" id="content" class="form-control" rows="10" required><?= htmlspecialchars($article->content, ENT_QUOTES, 'UTF-8'); ?></textarea>
                </div>

                <div class="form-group">
                  <label for="attachment">PDF Attachment</label>
                  <input type="file" name="attachment" id="attachment" class="form-control-file" accept=".pdf,application/pdf">
                  <small class="form-text text-muted">Optional. Upload a new PDF to replace the current attachment.</small>

                  <?php if (!empty($article->attachment_path)): ?>
                    <div class="mt-2">
                      <a href="<?= base_url('Page/knowledgeBaseAttachment/' . $article->id); ?>" target="_blank" rel="noopener noreferrer">
                        <i class="fas fa-file-pdf text-danger"></i>
                        <?= htmlspecialchars($current_attachment_name !== '' ? $current_attachment_name : 'Current PDF attachment', ENT_QUOTES, 'UTF-8'); ?>
                      </a>
                    </div>

                    <div class="custom-control custom-checkbox mt-2">
                      <input type="checkbox" class="custom-control-input" id="remove_attachment" name="remove_attachment" value="1">
                      <label class="custom-control-label" for="remove_attachment">Remove current PDF attachment</label>
                    </div>
                  <?php endif; ?>
                </div>

                <div class="form-group">
                  <label for="status">Status</label>
                  <select name="status" id="status" class="form-control">
                    <option value="draft" <?= $article->status === 'draft' ? 'selected' : ''; ?>>Draft</option>
                    <option value="published" <?= $article->status === 'published' ? 'selected' : ''; ?>>Published</option>
                  </select>
                </div>

                <div class="form-group">
                  <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Update Article
                  </button>
                  <a href="<?= base_url(); ?>Page/knowledgeBase" class="btn btn-default">
                    Cancel
                  </a>
                </div>
              <?= form_close(); ?>
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

<script>
document.getElementById('category').addEventListener('change', function() {
  var newCategoryGroup = document.getElementById('newCategoryGroup');
  var newCategoryInput = document.getElementById('newCategory');
  if (this.value === 'new') {
    newCategoryGroup.style.display = 'block';
    newCategoryInput.required = true;
  } else {
    newCategoryGroup.style.display = 'none';
    newCategoryInput.required = false;
    newCategoryInput.value = '';
  }
});

document.querySelector('form').addEventListener('submit', function() {
  var categorySelect = document.getElementById('category');
  var newCategoryInput = document.getElementById('newCategory');
  if (categorySelect.value === 'new' && newCategoryInput.value) {
    categorySelect.value = newCategoryInput.value;
  }
});
</script>

</body>
</html>
