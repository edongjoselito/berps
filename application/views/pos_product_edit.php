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
                    <div class="page-title-box d-flex justify-content-between align-items-center flex-wrap">
                        <div>
                            <h4 class="page-title mb-1">
                                <?= htmlspecialchars($page_title ?? 'Edit Product', ENT_QUOTES, 'UTF-8'); ?>
                            </h4>
                            <p class="text-muted mb-0">
                                <?= htmlspecialchars($page_subtitle ?? 'Update POS item details.', ENT_QUOTES, 'UTF-8'); ?>
                            </p>
                        </div>
                        <div class="mt-2 mt-md-0">
                            <a href="<?= base_url('Pos/posProductList'); ?>" class="btn btn-secondary">
                                <i class="mdi mdi-arrow-left"></i> Back to Products
                            </a>
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

                    <?php if (validation_errors()): ?>
                        <div class="alert alert-danger" role="alert">
                            <?= validation_errors(); ?>
                        </div>
                    <?php endif; ?>

                    <div class="card">
                        <div class="card-body">
                            <form method="post" action="<?= base_url('Pos/posUpdateProduct/' . (int)$product->id); ?>">
                                <input type="hidden" name="id" value="<?= (int)$product->id; ?>">

                                <div class="form-row">
                                    <div class="form-group col-md-4">
                                        <label>SKU</label>
                                        <input type="text" name="sku" class="form-control" value="<?= set_value('sku', $product->sku); ?>" readonly>
                                        <small class="form-text text-muted">SKU is kept for tracking.</small>
                                    </div>
                                    <div class="form-group col-md-8">
                                        <label>Name</label>
                                        <input type="text" name="name" class="form-control" required value="<?= set_value('name', $product->name); ?>">
                                    </div>
                                </div>

                                <div class="form-row">
                                    <div class="form-group col-md-4">
                                        <label>Category</label>
                                        <input type="text" name="category" class="form-control" value="<?= set_value('category', $product->category); ?>">
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label>Unit Cost</label>
                                        <input type="number" step="0.01" name="unit_cost" class="form-control" value="<?= set_value('unit_cost', $product->unit_cost); ?>">
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label>Unit Price</label>
                                        <input type="number" step="0.01" name="unit_price" class="form-control" required value="<?= set_value('unit_price', $product->unit_price); ?>">
                                    </div>
                                </div>

                                <div class="form-row">
                                    <div class="form-group col-md-4">
                                        <label>Stock Qty</label>
                                        <input type="number" name="stock_qty" class="form-control" value="<?= set_value('stock_qty', $product->stock_qty); ?>">
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label>Expiry Date</label>
                                        <input type="date" name="expiry_date" class="form-control" value="<?= set_value('expiry_date', ($product->expiry_date && $product->expiry_date !== '0000-00-00') ? $product->expiry_date : ''); ?>">
                                    </div>
                                </div>

                                <div class="d-flex justify-content-between mt-3">
                                    <a href="<?= base_url('Pos/posProductList'); ?>" class="btn btn-link text-muted">Cancel</a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="mdi mdi-content-save"></i> Save Changes
                                    </button>
                                </div>
                            </form>
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
