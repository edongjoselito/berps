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
                            <h1 class="kb-title">Knowledge Base Settings</h1>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="kb-card">
                                <div class="kb-card-header">
                                    <h3 class="kb-card-title">Manage Categories</h3>
                                </div>
                                <div class="kb-card-body">
              <form action="<?= base_url(); ?>Page/knowledgeBaseSettings" method="POST">
                <div class="form-group">
                  <label for="newCategory">Add New Category</label>
                  <input type="text" name="new_category" id="newCategory" class="form-control" required>
                </div>
                <button type="submit" name="add_category" class="btn btn-primary">
                  <i class="fas fa-plus"></i> Add Category
                </button>
              </form>

              <hr>

              <h5>Existing Categories</h5>
              <?php if (!empty($categories)): ?>
                <ul class="list-group mt-2">
                  <?php foreach ($categories as $cat): ?>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                      <div>
                        <strong><?= htmlspecialchars($cat->name, ENT_QUOTES, 'UTF-8'); ?></strong>
                        <small class="text-muted ml-2"><?= $cat->count; ?> articles</small>
                      </div>
                      <div>
                        <button type="button" class="btn btn-sm btn-info" onclick="editCategory('<?= htmlspecialchars($cat->name, ENT_QUOTES, 'UTF-8'); ?>')">
                          <i class="fas fa-edit"></i>
                        </button>
                        <a href="<?= base_url(); ?>Page/knowledgeBaseDeleteCategory/<?= urlencode($cat->name); ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this category? All articles in this category will be set to uncategorized.');">
                          <i class="fas fa-trash"></i>
                        </a>
                      </div>
                    </li>
                  <?php endforeach; ?>
                </ul>
              <?php else: ?>
                <p class="text-muted">No categories created yet.</p>
              <?php endif; ?>

              <!-- Edit Category Modal -->
              <div class="modal fade" id="editCategoryModal" tabindex="-1" role="dialog">
                <div class="modal-dialog" role="document">
                  <div class="modal-content">
                    <div class="modal-header">
                      <h5 class="modal-title">Edit Category</h5>
                      <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                      </button>
                    </div>
                    <div class="modal-body">
                      <form id="editCategoryForm" action="<?= base_url(); ?>Page/knowledgeBaseEditCategory" method="POST">
                        <input type="hidden" name="old_category" id="oldCategory">
                        <div class="form-group">
                          <label for="editCategory">Category Name</label>
                          <input type="text" name="new_category" id="editCategory" class="form-control" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Update Category</button>
                      </form>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="col-md-6">
          <div class="kb-card">
            <div class="kb-card-header">
              <h3 class="kb-card-title">Article Types</h3>
            </div>
            <div class="kb-card-body">
              <p class="text-muted">The following article types are available in the system:</p>
              <ul class="list-group">
                <li class="list-group-item">
                  <strong>Article</strong>
                  <p class="mb-0 text-muted">Standard knowledge base articles with detailed information.</p>
                </li>
                <li class="list-group-item">
                  <strong>FAQ</strong>
                  <p class="mb-0 text-muted">Frequently Asked Questions for quick reference.</p>
                </li>
              </ul>

              <hr>

              <h5>Article Statistics</h5>
              <div class="row mt-3">
                <div class="col-md-6">
                  <div class="card bg-light">
                    <div class="card-body text-center">
                      <h3><?= $total_articles ?? 0; ?></h3>
                      <p class="mb-0">Total Articles</p>
                    </div>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="card bg-light">
                    <div class="card-body text-center">
                      <h3><?= $published_articles ?? 0; ?></h3>
                      <p class="mb-0">Published</p>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

            </div>
        </div>
    </div>

    <?php include('includes/footer.php'); ?>

    <script src="<?= base_url(); ?>assets/js/vendor.min.js"></script>
    <script src="<?= base_url(); ?>assets/js/app.min.js"></script>

    <script>
    function editCategory(categoryName) {
        document.getElementById('oldCategory').value = categoryName;
        document.getElementById('editCategory').value = categoryName;
        $('#editCategoryModal').modal('show');
    }
    </script>

</div>

</body>
</html>
