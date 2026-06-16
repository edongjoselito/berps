<!DOCTYPE html>
<html lang="en">
<?php include('includes/head.php'); ?>

<style>
    .pos-history-page {
        background:
            radial-gradient(circle at top left, rgba(14, 165, 233, 0.08), transparent 26%),
            radial-gradient(circle at top right, rgba(249, 115, 22, 0.08), transparent 24%),
            linear-gradient(180deg, #f7fafc 0%, #f2f6fa 100%);
        min-height: 100vh;
        padding: 24px 0 32px;
    }

    .pos-history-page .page-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-end;
        gap: 16px;
        flex-wrap: wrap;
        margin-bottom: 18px;
    }

    .pos-history-page .page-title {
        margin: 0;
        font-size: 2rem;
        font-weight: 800;
        color: #17324a;
        letter-spacing: -0.04em;
    }

    .pos-history-page .page-subtitle {
        margin-top: 8px;
        color: #62778d;
        max-width: 760px;
    }

    .pos-history-page .page-actions {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }

    .pos-history-page .btn-action {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 10px 16px;
        border-radius: 12px;
        font-weight: 700;
        text-decoration: none;
    }

    .pos-history-page .btn-ghost {
        border: 1px solid #d7e1eb;
        background: rgba(255, 255, 255, 0.88);
        color: #17324a;
    }

    .pos-history-page .btn-primary-soft {
        border: 1px solid rgba(2, 132, 199, 0.18);
        background: rgba(2, 132, 199, 0.10);
        color: #075985;
    }

    .pos-history-page .stat-grid {
        display: grid;
        grid-template-columns: repeat(5, minmax(0, 1fr));
        gap: 14px;
        margin-bottom: 18px;
    }

    .pos-history-page .stat-card,
    .pos-history-page .theme-card {
        background: #fff;
        border-radius: 20px;
        border: 1px solid rgba(255, 255, 255, 0.8);
        box-shadow: 0 18px 42px rgba(15, 23, 42, 0.08);
    }

    .pos-history-page .stat-card {
        padding: 18px 20px;
    }

    .pos-history-page .stat-label {
        color: #75879b;
        font-size: 0.74rem;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        font-weight: 700;
    }

    .pos-history-page .stat-value {
        margin-top: 8px;
        color: #17324a;
        font-size: 1.7rem;
        font-weight: 800;
        letter-spacing: -0.04em;
    }

    .pos-history-page .stat-meta {
        margin-top: 6px;
        color: #62778d;
        font-size: 0.85rem;
    }

    .pos-history-page .theme-card-head {
        padding: 18px 20px;
        border-bottom: 1px solid #e8eef4;
        background: linear-gradient(180deg, rgba(255, 255, 255, 0.98), rgba(247, 250, 252, 0.94));
    }

    .pos-history-page .theme-card-title {
        margin: 0;
        color: #17324a;
        font-weight: 800;
    }

    .pos-history-page .theme-card-subtitle {
        margin-top: 6px;
        color: #62778d;
        font-size: 0.9rem;
    }

    .pos-history-page .theme-card-body {
        padding: 20px;
    }

    .pos-history-page .table thead th {
        border-top: 0;
        color: #60758a;
        font-size: 0.76rem;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        white-space: nowrap;
    }

    @media (max-width: 991.98px) {
        .pos-history-page .stat-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 575.98px) {
        .pos-history-page .stat-grid {
            grid-template-columns: minmax(0, 1fr);
        }
    }

    .badge-soft {
        border: 1px solid transparent;
        padding: 0.28rem 0.5rem;
        font-weight: 600;
    }

    .badge-soft.success {
        color: #166534;
        background: #ecfdf3;
        border-color: #bbf7d0;
    }

    .badge-soft.warning {
        color: #d97706;
        background: #fff7ed;
        border-color: #fef3c7;
    }

    .badge-soft.danger {
        color: #b91c1c;
        background: #fef2f2;
        border-color: #fee2e2;
    }

    .badge-soft.secondary {
        color: #374151;
        background: #f3f4f6;
        border-color: #e5e7eb;
    }

    .stock-table td,
    .stock-table th {
                        font-size: 0.9rem;
    }

    .expiry-cell {
        min-width: 160px;
    }
</style>

<body>
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="<?= base_url(); ?>assets/libs/datatables/responsive.bootstrap4.min.css">
    <link rel="stylesheet" href="<?= base_url(); ?>assets/libs/datatables/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="<?= base_url(); ?>assets/libs/datatables/buttons.bootstrap4.min.css">

    <?php
    $mode = isset($mode) ? (string) $mode : 'stock';
    $categories = [];
    $summary_total = 0;
    $summary_in_stock = 0;
    $summary_low_stock = 0;
    $summary_out_of_stock = 0;
    $summary_expired = 0;

    if (!empty($products)) {
        foreach ($products as $p) {
            $summary_total++;
            $stockQty = (int)($p->stock_qty ?? 0);
            $reorderLvl = (int)($p->reorder_level ?? 5);
            if ($stockQty <= 0) {
                $summary_out_of_stock++;
            } elseif ($stockQty <= $reorderLvl) {
                $summary_low_stock++;
            } else {
                $summary_in_stock++;
            }

            $expiryRaw = trim((string)($p->expiry_date ?? ''));
            $expiryDate = ($expiryRaw !== '' && $expiryRaw !== '0000-00-00') ? $expiryRaw : '';
            if ($expiryDate !== '' && strtotime($expiryDate) < strtotime(date('Y-m-d'))) {
                $summary_expired++;
            }

            $c = trim((string)($p->category ?? ''));
            if ($c !== '') {
                $categories[$c] = true;
            }
        }
    }

    $categories = array_keys($categories);
    sort($categories);
    ?>

    <div id="wrapper">
        <?php include('includes/top-nav-bar.php'); ?>
        <?php include('includes/sidebar.php'); ?>

        <div class="content-page">
            <div class="content">
                <div class="container-fluid pos-history-page">

                    <div class="page-header">
                        <div>
                            <h4 class="page-title"><?= htmlspecialchars($page_title ?? 'Stock Levels', ENT_QUOTES, 'UTF-8'); ?></h4>
                            <p class="page-subtitle"><?= htmlspecialchars($page_subtitle ?? '', ENT_QUOTES, 'UTF-8'); ?></p>
                        </div>
                        <div class="page-actions">
                            <a class="btn-action btn-primary-soft" href="<?= base_url(); ?>Pos/posStockLevels">
                                <i class="mdi mdi-warehouse"></i> Stock Levels
                            </a>
                            <a class="btn-action btn-ghost" href="<?= base_url(); ?>Pos/posLowStockItems">
                                <i class="mdi mdi-alert"></i> Low Stock
                            </a>
                            <a class="btn-action btn-ghost" href="<?= base_url(); ?>Pos/posExpiringSoon">
                                <i class="mdi mdi-timer-sand"></i> Expiry
                            </a>
                            <a class="btn-action btn-ghost" href="<?= base_url(); ?>Pos/posProductList">
                                <i class="mdi mdi-package-variant"></i> Products
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

                    <div class="stat-grid">
                        <div class="stat-card">
                            <div class="stat-label">Total Items</div>
                            <div class="stat-value"><?= (int) $summary_total; ?></div>
                            <div class="stat-meta">Products in the current result</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-label">In Stock</div>
                            <div class="stat-value"><?= (int) $summary_in_stock; ?></div>
                            <div class="stat-meta">Above reorder threshold</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-label">Low Stock</div>
                            <div class="stat-value"><?= (int) $summary_low_stock; ?></div>
                            <div class="stat-meta">At or below reorder level</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-label">Out of Stock</div>
                            <div class="stat-value"><?= (int) $summary_out_of_stock; ?></div>
                            <div class="stat-meta">Requires restock</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-label">Expired</div>
                            <div class="stat-value"><?= (int) $summary_expired; ?></div>
                            <div class="stat-meta">Past expiry date</div>
                        </div>
                    </div>

                    <div class="theme-card no-print">
                        <div class="theme-card-head">
                            <div class="theme-card-title">Filter Inventory</div>
                            <div class="theme-card-subtitle">Use category, stock status, and expiry state to narrow the list before exporting or restocking.</div>
                        </div>
                        <div class="theme-card-body">
                            <div class="form-row">
                                <div class="form-group col-md-4">
                                    <label for="categoryFilter">Category</label>
                                    <select id="categoryFilter" class="form-control">
                                        <option value="">All</option>
                                        <?php foreach ($categories as $c): ?>
                                            <option value="<?= htmlspecialchars($c, ENT_QUOTES, 'UTF-8'); ?>"><?= htmlspecialchars($c, ENT_QUOTES, 'UTF-8'); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group col-md-4">
                                    <label for="statusFilter">Status</label>
                                    <select id="statusFilter" class="form-control">
                                        <option value="">All</option>
                                        <option value="in_stock">In Stock</option>
                                        <option value="low_stock">Low Stock</option>
                                        <option value="out_of_stock">Out of Stock</option>
                                        <option value="expired">Expired</option>
                                    </select>
                                </div>
                                <div class="form-group col-md-4">
                                    <label for="expiryFilter">Expiry</label>
                                    <select id="expiryFilter" class="form-control">
                                        <option value="">All</option>
                                        <option value="no_expiry">No Expiry Date</option>
                                        <option value="expired">Expired</option>
                                        <option value="soon">Expiring Soon (≤ 30 days)</option>
                                        <option value="valid">Valid</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="theme-card">
                        <div class="theme-card-head">
                            <div class="theme-card-title"><?= ($mode === 'low') ? 'Low Stock Items' : 'Stock Levels'; ?></div>
                            <div class="theme-card-subtitle"><?= ($mode === 'low') ? 'Items at or below reorder threshold. Use this list to plan replenishment.' : 'Live stock quantities, reorder levels, and expiry tracking for your products.'; ?></div>
                        </div>
                        <div class="theme-card-body">
                            <div class="table-responsive">
                                <table id="stockTable" class="table table-hover mb-0 dt-responsive nowrap stock-table" style="width:100%">
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
                                            <th>Status</th>
                                            <th>Expiry</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($products)): ?>
                                            <?php $row = 1; foreach ($products as $p): ?>
                                                <?php
                                                $stock = (int)($p->stock_qty ?? 0);
                                                $reorderLevel = (int)($p->reorder_level ?? 5);
                                                $statusLabel = 'In Stock';
                                                $badgeClass = 'success';
                                                $statusTag = 'in_stock';

                                                if ($stock <= 0) {
                                                    $statusLabel = 'Out of Stock';
                                                    $badgeClass = 'danger';
                                                    $statusTag = 'out_of_stock';
                                                } elseif ($stock <= $reorderLevel) {
                                                    $statusLabel = 'Low Stock';
                                                    $badgeClass = 'warning';
                                                    $statusTag = 'low_stock';
                                                }

                                                $expiryRaw = trim((string)($p->expiry_date ?? ''));
                                                $expiryDate = ($expiryRaw !== '' && $expiryRaw !== '0000-00-00') ? $expiryRaw : '';
                                                $expiryTs = $expiryDate ? strtotime($expiryDate) : 0;
                                                $todayTs  = strtotime(date('Y-m-d'));
                                                $daysLeft = ($expiryTs > 0) ? (int)floor(($expiryTs - $todayTs) / 86400) : null;

                                                $expiryTag = 'no_expiry';

                                                $expiryLabel = 'No Expiry';
                                                $expiryBadge = 'secondary';
                                                if ($expiryTs > 0) {
                                                    $expiryTag = 'valid';
                                                    if ($expiryTs < $todayTs) {
                                                        $expiryLabel = 'Expired';
                                                        $expiryBadge = 'danger';
                                                        $expiryTag = 'expired';
                                                    } elseif ($daysLeft !== null && $daysLeft <= 30) {
                                                        $expiryLabel = 'Expiring in ' . $daysLeft . ' day' . ($daysLeft === 1 ? '' : 's');
                                                        $expiryBadge = 'warning';
                                                        $expiryTag = 'soon';
                                                    } else {
                                                        $expiryLabel = 'Valid';
                                                        $expiryBadge = 'secondary';
                                                        $expiryTag = 'valid';
                                                    }
                                                }

                                                // Combine stock + expiry status when both matter
                                                if ($expiryBadge === 'danger') {
                                                    $statusLabel .= ' / Expired';
                                                    $badgeClass = 'danger';
                                                    $statusTag = 'expired';
                                                }
                                                ?>
                                                <tr data-category="<?= htmlspecialchars((string)($p->category ?? ''), ENT_QUOTES, 'UTF-8'); ?>" data-status-tag="<?= htmlspecialchars($statusTag, ENT_QUOTES, 'UTF-8'); ?>" data-expiry-tag="<?= htmlspecialchars($expiryTag, ENT_QUOTES, 'UTF-8'); ?>">
                                                    <td><?= $row++; ?></td>
                                                    <td><?= htmlspecialchars($p->sku, ENT_QUOTES, 'UTF-8'); ?></td>
                                                    <td><?= htmlspecialchars($p->name, ENT_QUOTES, 'UTF-8'); ?></td>
                                                    <td><?= htmlspecialchars($p->category ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                                                    <td><?= htmlspecialchars((string)($p->unit ?? 'pcs'), ENT_QUOTES, 'UTF-8'); ?></td>
                                                    <td class="text-right"><?= number_format((float)$p->unit_price, 2); ?></td>
                                                    <td class="text-right"><?= $stock; ?></td>
                                                    <td class="text-right"><?= $reorderLevel; ?></td>
                                                    <td><span class="badge-soft <?= $badgeClass; ?>" style="font-size: 0.85rem;"><?= htmlspecialchars($statusLabel, ENT_QUOTES, 'UTF-8'); ?></span></td>
                                                    <td class="expiry-cell">
                                                        <?php if ($expiryDate): ?>
                                                            <div class="d-flex flex-column align-items-start">
                                                                <span class="badge-soft <?= $expiryBadge; ?>" style="font-size: 0.85rem;"><?= htmlspecialchars($expiryLabel, ENT_QUOTES, 'UTF-8'); ?></span>
                                                                <small class="text-muted mt-1"><?= htmlspecialchars(date('M d, Y', strtotime($expiryDate)), ENT_QUOTES, 'UTF-8'); ?></small>
                                                            </div>
                                                        <?php else: ?>
                                                            <span class="text-muted">—</span>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="10" class="text-center text-muted py-4"><?= htmlspecialchars($empty_text ?? 'No products to display.', ENT_QUOTES, 'UTF-8'); ?></td>
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

    <!-- DataTables JS -->
    <script src="<?= base_url(); ?>assets/js/vendor.min.js"></script>
    <script src="<?= base_url(); ?>assets/libs/datatables/jquery.dataTables.min.js"></script>
    <script src="<?= base_url(); ?>assets/libs/datatables/dataTables.bootstrap4.min.js"></script>
    <script src="<?= base_url(); ?>assets/libs/datatables/dataTables.responsive.min.js"></script>
    <script src="<?= base_url(); ?>assets/libs/datatables/responsive.bootstrap4.min.js"></script>
    <script src="<?= base_url(); ?>assets/js/app.min.js"></script>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            var mode = <?= json_encode($mode); ?>;

            var table = $('#stockTable').DataTable({
                responsive: {
                    details: {
                        type: 'inline',
                        target: 'td:not(.no-details)'
                    }
                },
                order: mode === 'low' ? [
                    [6, 'asc']
                ] : [
                    [3, 'asc'],
                    [2, 'asc']
                ]
            });

            $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
                var rowNode = table.row(dataIndex).node();
                if (!rowNode) return true;

                var selectedCat = ($('#categoryFilter').val() || '').trim();
                var selectedStatus = ($('#statusFilter').val() || '').trim();
                var selectedExpiry = ($('#expiryFilter').val() || '').trim();

                var rowCat = (rowNode.getAttribute('data-category') || '').trim();
                var rowStatus = (rowNode.getAttribute('data-status-tag') || '').trim();
                var rowExpiry = (rowNode.getAttribute('data-expiry-tag') || '').trim();

                if (selectedCat && rowCat !== selectedCat) return false;
                if (selectedStatus && rowStatus !== selectedStatus) return false;
                if (selectedExpiry && rowExpiry !== selectedExpiry) return false;

                return true;
            });

            $('#categoryFilter, #statusFilter, #expiryFilter').on('change', function() {
                table.draw();
            });
        });
    </script>
</body>

</html>
