<!DOCTYPE html>
<html lang="en">
<?php include('includes/head.php'); ?>

<style>
    html,
    body {
        height: 100%;
    }

    #wrapper {
        min-height: 100vh;
    }

    .content-page {
        min-height: 100vh;
        display: flex;
        flex-direction: column;
    }

    .content-page>.content {
        flex: 1 0 auto;
    }

    .content-page>.footer {
        flex: 0 0 auto;
    }

    .pos-staff-dashboard {
        padding-bottom: 24px !important;
    }

    .btn {
        transition: all 0.25s ease-in-out;
    }

    .btn:hover {
        transform: scale(1.05);
        opacity: 0.95;
    }

    .btn-primary:hover {
        box-shadow: 0 0 8px rgba(0, 123, 255, 0.5);
        border-color: rgba(0, 123, 255, 0.4);
    }

    .btn-secondary:hover {
        box-shadow: 0 0 8px rgba(108, 117, 125, 0.5);
        border-color: rgba(108, 117, 125, 0.4);
    }

    .btn-info:hover {
        box-shadow: 0 0 8px rgba(23, 162, 184, 0.5);
        border-color: rgba(23, 162, 184, 0.4);
    }

    .btn-danger:hover {
        box-shadow: 0 0 8px rgba(220, 53, 69, 0.5);
        border-color: rgba(220, 53, 69, 0.4);
    }

    /* Keep content off the footer when the table gets long */
    .pos-staff-dashboard {
        padding-bottom: 96px;
    }

    /* PRINT (copied + adapted from your reference) */
    @media print {

        .no-print,
        .page-title-box,
        .navbar-custom,
        .left-side-menu,
        .footer,
        .dataTables_length,
        .dataTables_filter,
        .dataTables_info,
        .dataTables_paginate,
        .dt-buttons,
        #categoryFilter,
        #expiryFilter {
            display: none !important;
        }

        .content-page,
        .content,
        .container-fluid,
        .card,
        .card-body,
        .table-responsive {
            padding: 0 !important;
            margin: 0 !important;
            box-shadow: none !important;
            border: 0 !important;
        }

        table {
            width: 100% !important;
            border-collapse: collapse !important;
            font-size: 11px;
        }

        thead {
            display: table-header-group;
        }

        tr,
        td,
        th {
            page-break-inside: avoid !important;
        }

        /* hide action + responsive control */
        #datatable td.no-details,
        #datatable th:last-child {
            display: none !important;
        }

        #datatable td.dtr-control,
        #datatable th.dtr-control {
            display: none !important;
        }
    }
</style>

<body>
    <?php
    // normalize optional edit data so view stays safe
    $edit_product = isset($edit_product) ? $edit_product : null;
    $open_edit_modal = !empty($open_edit_modal) && $edit_product;
    ?>
    <!-- DataTables CSS (same libs as your reference) -->
    <link rel="stylesheet" href="<?= base_url(); ?>assets/libs/datatables/responsive.bootstrap4.min.css">
    <link rel="stylesheet" href="<?= base_url(); ?>assets/libs/datatables/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="<?= base_url(); ?>assets/libs/datatables/buttons.bootstrap4.min.css">

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
                            <h4 class="page-title mb-0"><?= htmlspecialchars($page_title ?? 'Product List', ENT_QUOTES, 'UTF-8'); ?></h4>
                        </div>
                        <div class="mt-2 mt-md-0 no-print">
                            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addProductModal">
                                <i class="mdi mdi-plus"></i> Add Product
                            </button>
                            <button type="button" class="btn btn-info ml-1" onclick="printAllProducts()">
                                <i class="fas fa-print"></i> Print
                            </button>
                        </div>
                    </div>

                    <?php if (!empty($notice)): ?>
                        <div class="alert alert-<?= ($notice_type === 'error') ? 'danger' : 'success'; ?> alert-dismissible fade show alert-pos-notice" role="alert">
                            <?= htmlspecialchars($notice, ENT_QUOTES, 'UTF-8'); ?>
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    <?php endif; ?>

                    <?php if ($open_edit_modal && validation_errors()): ?>
                        <div class="alert alert-danger" role="alert">
                            <?= validation_errors(); ?>
                        </div>
                    <?php endif; ?>

                    <div class="card">
                        <div class="card-body">

                            <?php
                            // Build category list for filter (safe)
                            $categories = [];
                            if (!empty($products)) {
                                foreach ($products as $p) {
                                    $c = trim((string)($p->category ?? ''));
                                    if ($c !== '') $categories[$c] = true;
                                }
                            }
                            $categories = array_keys($categories);
                            sort($categories);
                            ?>

                            <div class="d-flex align-items-center mb-3 flex-wrap no-print">
                                <div class="mr-3 mb-2">
                                    <label for="categoryFilter" class="mb-0 mr-2"><strong>Category:</strong></label>
                                    <select id="categoryFilter" class="form-control form-control-sm" style="min-width:200px; max-width:260px;">
                                        <option value="">All</option>
                                        <?php foreach ($categories as $c): ?>
                                            <option value="<?= htmlspecialchars($c, ENT_QUOTES, 'UTF-8'); ?>"><?= htmlspecialchars($c, ENT_QUOTES, 'UTF-8'); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="mr-3 mb-2">
                                    <label for="expiryFilter" class="mb-0 mr-2"><strong>Expiry:</strong></label>
                                    <select id="expiryFilter" class="form-control form-control-sm" style="min-width:220px; max-width:260px;">
                                        <option value="">All</option>
                                        <option value="has_expiry">With Expiry Date</option>
                                        <option value="no_expiry">No Expiry Date</option>
                                        <option value="expired">Expired</option>
                                        <option value="soon">Expiring Soon (≤ 30 days)</option>
                                    </select>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table id="datatable" class="table table-bordered table-striped dt-responsive nowrap" style="width:100%">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Serial Number</th>
                                            <th>Name</th>
                                            <th>Category</th>
                                            <th>Unit</th>
                                            <th class="text-right">Unit Price</th>
                                            <th class="text-right">Stock</th>
                                            <th class="text-right">Reorder</th>
                                            <th>Tax</th>
                                            <th>Expiry</th>
                                            <th class="no-details print-hide">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($products)): ?>
                                            <?php $row = 1;
                                            foreach ($products as $p): ?>
                                                <?php
                                                $expiryRaw = trim((string)($p->expiry_date ?? ''));
                                                $expiryDate = ($expiryRaw !== '' && $expiryRaw !== '0000-00-00') ? $expiryRaw : '';
                                                $expiryTs = $expiryDate ? strtotime($expiryDate) : 0;
                                                $todayTs  = strtotime(date('Y-m-d'));
                                                $daysLeft = ($expiryTs > 0) ? (int)floor(($expiryTs - $todayTs) / 86400) : null;

                                                $expiryTag = 'no_expiry';
                                                if ($expiryTs > 0) {
                                                    if ($expiryTs < $todayTs) $expiryTag = 'expired';
                                                    else if ($daysLeft !== null && $daysLeft <= 30) $expiryTag = 'soon';
                                                    else $expiryTag = 'has_expiry';
                                                }

                                                $cat = trim((string)($p->category ?? ''));
                                                ?>
                                                <?php
                                                $skuSort = 0;
                                                if (isset($p->sku) && preg_match('/(\\d+)/', (string)$p->sku, $mSku)) {
                                                    $skuSort = (int)$mSku[1];
                                                }
                                                ?>
                                                <tr
                                                    data-category="<?= htmlspecialchars($cat, ENT_QUOTES, 'UTF-8'); ?>"
                                                    data-expiry-tag="<?= htmlspecialchars($expiryTag, ENT_QUOTES, 'UTF-8'); ?>">
                                                    <td><?= $row++; ?></td>
                                                    <td data-order="<?= $skuSort; ?>"><?= htmlspecialchars($p->sku, ENT_QUOTES, 'UTF-8'); ?></td>
                                                    <td><?= htmlspecialchars($p->name, ENT_QUOTES, 'UTF-8'); ?></td>
                                                    <td><?= htmlspecialchars($cat, ENT_QUOTES, 'UTF-8'); ?></td>
                                                    <td><?= htmlspecialchars((string)($p->unit ?? 'pcs'), ENT_QUOTES, 'UTF-8'); ?></td>
                                                    <td class="text-right"><?= number_format((float)$p->unit_price, 2); ?></td>
                                                    <td class="text-right"><?= (int)$p->stock_qty; ?></td>
                                                    <td class="text-right"><?= (int)($p->reorder_level ?? 5); ?></td>
                                                    <td><?= htmlspecialchars(ucwords(str_replace('_', ' ', (string)($p->tax_type ?? 'vatable'))), ENT_QUOTES, 'UTF-8'); ?></td>
                                                    <td>
                                                        <?php if ($expiryDate): ?>
                                                            <?= htmlspecialchars(date('M d, Y', strtotime($expiryDate)), ENT_QUOTES, 'UTF-8'); ?>
                                                            <?php if ($expiryTag === 'expired'): ?>
                                                                <span class="badge badge-danger ml-1">Expired</span>
                                                            <?php elseif ($expiryTag === 'soon'): ?>
                                                                <span class="badge badge-warning ml-1">Soon</span>
                                                            <?php endif; ?>
                                                        <?php else: ?>
                                                            —
                                                        <?php endif; ?>
                                                    </td>

                                                    <td class="no-details" style="white-space:nowrap">
                                                        <a href="<?= base_url('Pos/posEditProduct/' . $p->id) ?>"
                                                            class="btn btn-outline-info btn-sm" title="Edit" data-bs-toggle="tooltip">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <a href="<?= base_url('Pos/posDeleteProduct/' . $p->id) ?>"
                                                            class="btn btn-outline-danger btn-sm" title="Delete" data-bs-toggle="tooltip"
                                                            onclick="return confirm('Delete this product?')">
                                                            <i class="fas fa-trash-alt"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="11" class="text-center text-muted py-4">No products yet.</td>
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

    <!-- ADD PRODUCT MODAL (keep yours as-is; pasted here unchanged) -->
    <div class="modal fade" id="addProductModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <form method="post" action="<?= base_url('Pos/posCreateProduct'); ?>">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title mb-0">Add Product</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>

                    <div class="modal-body">
                        <div class="form-row">
                            <div class="form-group col-md-4">
                                <label>Serial Number</label>
                                <input type="text" name="sku" class="form-control"
                                    value="<?= isset($next_sku) ? htmlspecialchars($next_sku, ENT_QUOTES, 'UTF-8') : ''; ?>"
                                    placeholder="Auto-generated serial" readonly>
                            </div>
                            <div class="form-group col-md-8">
                                <label>Name</label>
                                <input type="text" name="name" class="form-control" placeholder="Product name" required>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-4">
                                <label>Category</label>
                                <input type="text" name="category" class="form-control" placeholder="e.g., Beverages">
                            </div>
                            <div class="form-group col-md-4">
                                <label>Barcode</label>
                                <input type="text" name="barcode" class="form-control" placeholder="Optional barcode">
                            </div>
                            <div class="form-group col-md-4">
                                <label>Unit</label>
                                <input type="text" name="unit" class="form-control" value="pcs" placeholder="pcs, box, bottle">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-4">
                                <label>Unit Cost</label>
                                <input type="number" step="0.01" name="unit_cost" class="form-control" value="0" placeholder="0.00">
                            </div>
                            <div class="form-group col-md-4">
                                <label>Unit Price</label>
                                <input type="number" step="0.01" name="unit_price" class="form-control" value="0" placeholder="0.00" required>
                            </div>
                            <div class="form-group col-md-4">
                                <label>Tax Type</label>
                                <select name="tax_type" class="form-control">
                                    <option value="vatable">Vatable</option>
                                    <option value="vat_exempt">VAT Exempt</option>
                                    <option value="zero_rated">Zero Rated</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-4">
                                <label>Stock Qty</label>
                                <input type="number" name="stock_qty" class="form-control" value="0" placeholder="On-hand qty">
                            </div>
                            <div class="form-group col-md-4">
                                <label>Reorder Level</label>
                                <input type="number" name="reorder_level" class="form-control" value="5" placeholder="Low stock alert">
                            </div>
                            <div class="form-group col-md-4">
                                <label>Discount Eligible</label>
                                <select name="discount_eligible" class="form-control">
                                    <option value="1">Yes</option>
                                    <option value="0">No</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-4">
                                <label>Expiry Date</label>
                                <input type="date" name="expiry_date" class="form-control">
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Save Product</button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <?php if ($edit_product): ?>
        <?php
        $edit_expiry = ($edit_product->expiry_date && $edit_product->expiry_date !== '0000-00-00')
            ? $edit_product->expiry_date
            : '';
        ?>
        <div class="modal fade" id="editProductModal" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <form method="post" action="<?= base_url('Pos/posUpdateProduct/' . (int)$edit_product->id); ?>">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title mb-0">Edit Product</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>

                        <div class="modal-body">
                            <input type="hidden" name="id" value="<?= (int)$edit_product->id; ?>">

                            <div class="form-row">
                                <div class="form-group col-md-4">
                                    <label>Serial Number</label>
                                    <input type="text" name="sku" class="form-control" value="<?= set_value('sku', $edit_product->sku); ?>" readonly>
                                </div>
                                <div class="form-group col-md-8">
                                    <label>Name</label>
                                    <input type="text" name="name" class="form-control" value="<?= set_value('name', $edit_product->name); ?>" required>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group col-md-4">
                                    <label>Category</label>
                                    <input type="text" name="category" class="form-control" value="<?= set_value('category', $edit_product->category); ?>">
                                </div>
                                <div class="form-group col-md-4">
                                    <label>Barcode</label>
                                    <input type="text" name="barcode" class="form-control" value="<?= set_value('barcode', $edit_product->barcode ?? ''); ?>">
                                </div>
                                <div class="form-group col-md-4">
                                    <label>Unit</label>
                                    <input type="text" name="unit" class="form-control" value="<?= set_value('unit', $edit_product->unit ?? 'pcs'); ?>">
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group col-md-4">
                                    <label>Unit Cost</label>
                                    <input type="number" step="0.01" name="unit_cost" class="form-control" value="<?= set_value('unit_cost', $edit_product->unit_cost); ?>">
                                </div>
                                <div class="form-group col-md-4">
                                    <label>Unit Price</label>
                                    <input type="number" step="0.01" name="unit_price" class="form-control" value="<?= set_value('unit_price', $edit_product->unit_price); ?>" required>
                                </div>
                                <div class="form-group col-md-4">
                                    <label>Tax Type</label>
                                    <?php $editTaxType = set_value('tax_type', $edit_product->tax_type ?? 'vatable'); ?>
                                    <select name="tax_type" class="form-control">
                                        <option value="vatable" <?= $editTaxType === 'vatable' ? 'selected' : ''; ?>>Vatable</option>
                                        <option value="vat_exempt" <?= $editTaxType === 'vat_exempt' ? 'selected' : ''; ?>>VAT Exempt</option>
                                        <option value="zero_rated" <?= $editTaxType === 'zero_rated' ? 'selected' : ''; ?>>Zero Rated</option>
                                    </select>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group col-md-4">
                                    <label>Stock Qty</label>
                                    <input type="number" name="stock_qty" class="form-control" value="<?= set_value('stock_qty', $edit_product->stock_qty); ?>">
                                </div>
                                <div class="form-group col-md-4">
                                    <label>Reorder Level</label>
                                    <input type="number" name="reorder_level" class="form-control" value="<?= set_value('reorder_level', $edit_product->reorder_level ?? 5); ?>">
                                </div>
                                <div class="form-group col-md-4">
                                    <label>Discount Eligible</label>
                                    <?php $editDiscountEligible = (string) set_value('discount_eligible', (string) ($edit_product->discount_eligible ?? 1)); ?>
                                    <select name="discount_eligible" class="form-control">
                                        <option value="1" <?= $editDiscountEligible === '1' ? 'selected' : ''; ?>>Yes</option>
                                        <option value="0" <?= $editDiscountEligible === '0' ? 'selected' : ''; ?>>No</option>
                                    </select>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group col-md-4">
                                    <label>Expiry Date</label>
                                    <input type="date" name="expiry_date" class="form-control" value="<?= set_value('expiry_date', $edit_expiry); ?>">
                                </div>
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="submit" class="btn btn-primary">Save Changes</button>
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    <?php endif; ?>

    <?php include('includes/themecustomizer.php'); ?>

    <!-- DataTables JS (same pattern as your reference) -->
    <script src="<?= base_url(); ?>assets/js/vendor.min.js"></script>
    <script src="<?= base_url(); ?>assets/libs/datatables/jquery.dataTables.min.js"></script>
    <script src="<?= base_url(); ?>assets/libs/datatables/dataTables.bootstrap4.min.js"></script>
    <script src="<?= base_url(); ?>assets/libs/datatables/dataTables.responsive.min.js"></script>
    <script src="<?= base_url(); ?>assets/libs/datatables/responsive.bootstrap4.min.js"></script>
    <script src="<?= base_url(); ?>assets/js/app.min.js"></script>

    <script>
        var table;

        function ensureDataTable() {
            if ($.fn.dataTable.isDataTable('#datatable')) {
                table = $('#datatable').DataTable();
            } else {
                table = $('#datatable').DataTable({
                    order: [
                        [1, 'asc']
                    ], // default sort by Serial Number
                    responsive: {
                        details: {
                            type: 'inline',
                            target: 'td:not(.no-details)'
                        }
                    },
                    columnDefs: [{
                            orderable: false,
                            searchable: false,
                            targets: 'no-details'
                        },
                        {
                            targets: 0, // #
                            orderable: false,
                            searchable: false,
                            render: function(data, type, row, meta) {
                                return meta.row + 1;
                            }
                        }
                    ]
                });
            }
            return table;
        }

        function initTooltips() {
            if (!window.bootstrap || !bootstrap.Tooltip) return;
            [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
                .map(function(el) {
                    return new bootstrap.Tooltip(el);
                });
        }

        function attachFiltersOnce() {
            if (window._posFiltersAttached) return;

            $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
                var dt = table;
                if (!dt) return true;

                var rowNode = dt.row(dataIndex).node();
                if (!rowNode) return true;

                var selectedCat = ($('#categoryFilter').val() || '').trim();
                var selectedExp = ($('#expiryFilter').val() || '').trim();

                var rowCat = (rowNode.getAttribute('data-category') || '').trim();
                var rowExp = (rowNode.getAttribute('data-expiry-tag') || '').trim();

                if (selectedCat && rowCat !== selectedCat) return false;
                if (selectedExp && rowExp !== selectedExp) return false;

                return true;
            });

            $('#categoryFilter, #expiryFilter').on('change', function() {
                table.draw();
            });

            window._posFiltersAttached = true;
        }

        document.addEventListener("DOMContentLoaded", function() {
            initTooltips();
            ensureDataTable();
            attachFiltersOnce();

            var shouldOpenEdit = <?= $open_edit_modal ? 'true' : 'false'; ?>;
            if (shouldOpenEdit && typeof $ === 'function') {
                $('#editProductModal').modal('show');
            }
        });

        function printAllProducts() {
            ensureDataTable();
            if (!table) return;

            var info = table.page.info();
            var oldStart = info.start;
            var oldLength = table.page.len();

            // close responsive children
            table.rows().every(function() {
                if (this.child && this.child.isShown()) this.child.hide();
            });

            table.page.len(-1).draw(false);

            var restore = function() {
                table.page.len(oldLength).draw(false);
                if (oldLength > 0 && oldLength !== -1) {
                    var oldPage = Math.floor(oldStart / oldLength);
                    table.page(oldPage).draw(false);
                }
                window.removeEventListener('afterprint', restore);
            };

            if ('onafterprint' in window) {
                window.addEventListener('afterprint', restore, {
                    once: true
                });
            } else if (window.matchMedia) {
                var mql = window.matchMedia('print');
                var listener = function(q) {
                    if (!q.matches) {
                        restore();
                        mql.removeListener(listener);
                    }
                };
                mql.addListener(listener);
            }

            setTimeout(function() {
                window.print();
            }, 150);
        }

        // auto hide notice (same behavior you had)
        (function() {
            var notice = document.querySelector('.alert-pos-notice');
            if (notice) {
                setTimeout(function() {
                    if (notice && notice.classList) {
                        notice.classList.remove('show');
                        notice.classList.add('fade');
                        setTimeout(function() {
                            if (notice.parentNode) notice.parentNode.removeChild(notice);
                        }, 300);
                    }
                }, 4000);
            }
        })();
    </script>
</body>

</html>
