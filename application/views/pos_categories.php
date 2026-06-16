<!DOCTYPE html>
<html lang="en">
<?php include('includes/head.php'); ?>

<body>
    <div id="wrapper">
        <?php include('includes/top-nav-bar.php'); ?>
        <?php include('includes/sidebar.php'); ?>

        <div class="content-page">
            <div class="content">
                <div class="container-fluid pos-staff-dashboard pb-4">
                    <style>
                        .pos-staff-dashboard .page-title-box {
                            padding: 6px 0 8px;
                            margin: 0 0 12px;
                        }

                        .pos-staff-dashboard .page-title {
                            font-weight: 700;
                            letter-spacing: -0.2px;
                            margin-bottom: 4px;
                        }

                        .pos-staff-dashboard .table thead th {
                            white-space: nowrap;
                        }
                    </style>

                    <div class="page-title-box d-flex justify-content-between align-items-center flex-wrap">
                        <div class="mb-2 mb-md-0">
                            <h4 class="page-title mb-0"><?= htmlspecialchars($page_title ?? 'POS Categories', ENT_QUOTES, 'UTF-8'); ?></h4>
                            <?php if (!empty($page_subtitle)): ?>
                                <p class="text-muted page-subtitle mb-0"><?= htmlspecialchars($page_subtitle, ENT_QUOTES, 'UTF-8'); ?></p>
                            <?php endif; ?>
                        </div>
                        <div class="mt-2 mt-md-0">
                            <a href="<?= base_url(); ?>Pos/posProductList" class="btn btn-outline-secondary btn-sm">Back to Products</a>
                        </div>
                    </div>

                    <?php if (!empty($notice)): ?>
                        <div class="alert alert-<?= ($notice_type === 'error') ? 'danger' : 'success'; ?> alert-dismissible fade show" role="alert">
                            <?= htmlspecialchars($notice, ENT_QUOTES, 'UTF-8'); ?>
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    <?php endif; ?>

                    <div class="card">
                        <div class="card-body">
                            <form method="post" action="<?= base_url('Pos/posCategoryCreate'); ?>" class="mb-3">
                                <div class="form-row">
                                    <div class="form-group col-md-8 mb-2">
                                        <label for="newCategoryName" class="mb-1"><strong>New Category</strong></label>
                                        <input type="text" id="newCategoryName" name="name" class="form-control" required>
                                    </div>
                                    <div class="form-group col-md-4 mb-2 d-flex align-items-end">
                                        <button type="submit" class="btn btn-primary btn-block">Add Category</button>
                                    </div>
                                </div>
                            </form>

                            <div class="table-responsive">
                                <table class="table table-bordered table-striped mb-0">
                                    <thead>
                                        <tr>
                                            <th style="width:80px">#</th>
                                            <th>Name</th>
                                            <th style="width:220px">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($categories)): ?>
                                            <?php $row = 1; foreach ($categories as $cat): ?>
                                                <tr>
                                                    <td><?= $row++; ?></td>
                                                    <td>
                                                        <form method="post" action="<?= base_url('Pos/posCategoryUpdate'); ?>" class="d-flex align-items-center" style="gap:8px;">
                                                            <input type="hidden" name="id" value="<?= (int) ($cat->id ?? 0); ?>">
                                                            <input type="text" name="name" class="form-control form-control-sm" value="<?= htmlspecialchars((string) ($cat->name ?? ''), ENT_QUOTES, 'UTF-8'); ?>" required>
                                                            <button type="submit" class="btn btn-outline-primary btn-sm">Save</button>
                                                        </form>
                                                    </td>
                                                    <td>
                                                        <a href="<?= base_url('Pos/posCategoryDelete?id=' . (int) ($cat->id ?? 0)); ?>" class="btn btn-outline-danger btn-sm" onclick="return confirm('Delete this category?');">Delete</a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="3" class="text-center text-muted py-4">No categories yet.</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <?php include('includes/footer.php'); ?>
        </div>
    </div>

    <?php include('includes/themecustomizer.php'); ?>

    <script src="<?= base_url(); ?>assets/js/vendor.min.js"></script>
    <script src="<?= base_url(); ?>assets/js/app.min.js"></script>
</body>

</html>
