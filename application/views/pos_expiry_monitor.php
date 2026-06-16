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
        grid-template-columns: repeat(3, minmax(0, 1fr));
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

    .pos-history-page .status-pill {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 6px 10px;
        border-radius: 999px;
        font-weight: 700;
        font-size: 0.78rem;
        border: 1px solid transparent;
    }

    .pos-history-page .status-warning {
        color: #92400e;
        background: #fff7ed;
        border-color: #fed7aa;
    }

    .pos-history-page .status-danger {
        color: #b91c1c;
        background: #fef2f2;
        border-color: #fecaca;
    }

    .pos-history-page .status-secondary {
        color: #475569;
        background: #e2e8f0;
        border-color: #cbd5e1;
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
</style>

<body>
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="<?= base_url(); ?>assets/libs/datatables/responsive.bootstrap4.min.css">
    <link rel="stylesheet" href="<?= base_url(); ?>assets/libs/datatables/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="<?= base_url(); ?>assets/libs/datatables/buttons.bootstrap4.min.css">

    <div id="wrapper">
        <?php include('includes/top-nav-bar.php'); ?>
        <?php include('includes/sidebar.php'); ?>

        <div class="content-page">
            <div class="content">
                <div class="container-fluid pos-history-page">

                    <?php
                        $productsSafe = is_array($products ?? null) ? $products : [];
                        $productCount = count($productsSafe);
                        $totalStock = 0;
                        $totalValue = 0.0;
                        foreach ($productsSafe as $pp) {
                            $qty = (int) ($pp->stock_qty ?? 0);
                            $price = (float) ($pp->unit_price ?? 0);
                            $totalStock += $qty;
                            $totalValue += ($qty * $price);
                        }
                    ?>

                    <div class="page-header">
                        <div>
                            <h1 class="page-title"><?= htmlspecialchars($page_title ?? 'Expiry Monitoring', ENT_QUOTES, 'UTF-8'); ?></h1>
                            <?php if (!empty($page_subtitle)): ?>
                                <div class="page-subtitle"><?= htmlspecialchars($page_subtitle, ENT_QUOTES, 'UTF-8'); ?></div>
                            <?php endif; ?>
                        </div>
                        <div class="page-actions">
                            <a class="btn-action btn-ghost" href="<?= base_url('Pos/posStockLevels'); ?>">
                                <i class="mdi mdi-chart-bar"></i> Stock Levels
                            </a>
                            <a class="btn-action btn-primary-soft" href="<?= base_url('Pos/posProductList'); ?>">
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
                            <div class="stat-label">Products</div>
                            <div class="stat-value"><?= number_format($productCount); ?></div>
                            <div class="stat-meta">Listed under this view</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-label">Units in Stock</div>
                            <div class="stat-value"><?= number_format($totalStock); ?></div>
                            <div class="stat-meta">Current on-hand quantity</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-label">Stock Value (SRP)</div>
                            <div class="stat-value"><?= number_format($totalValue, 2); ?></div>
                            <div class="stat-meta">Unit price × stock quantity</div>
                        </div>
                    </div>

                    <div class="theme-card">
                        <div class="theme-card-head">
                            <h5 class="theme-card-title">Expiry List</h5>
                            <div class="theme-card-subtitle">Review affected items and take action (dispose, return, markdown, or re-order).</div>
                        </div>
                        <div class="theme-card-body">
                            <div class="table-responsive">
                                <table id="expiryTable" class="table table-bordered table-striped dt-responsive nowrap" style="width:100%">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Serial Number</th>
                                            <th>Name</th>
                                            <th>Category</th>
                                            <th class="text-right">Unit Price</th>
                                            <th class="text-right">Stock</th>
                                            <th>Expiry</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($products)): ?>
                                            <?php $row = 1; foreach ($products as $p): ?>
                                                <?php
                                                $expiryRaw = trim((string)($p->expiry_date ?? ''));
                                                $expiryDate = ($expiryRaw !== '' && $expiryRaw !== '0000-00-00') ? $expiryRaw : '';
                                                $expiryTs = $expiryDate ? strtotime($expiryDate) : 0;
                                                $todayTs  = strtotime(date('Y-m-d'));
                                                $daysLeft = ($expiryTs > 0) ? (int)floor(($expiryTs - $todayTs) / 86400) : null;

                                                $statusLabel = 'No Expiry';
                                                $badgeClass = 'secondary';
                                                $showCountdown = false;
                                                if ($expiryTs > 0) {
                                                    if ($expiryTs < $todayTs) {
                                                        $statusLabel = 'Expired';
                                                        $badgeClass = 'danger';
                                                    } elseif ($daysLeft !== null && $daysLeft <= 30) {
                                                        if ($daysLeft === 0) {
                                                            $statusLabel = 'Expires today';
                                                            $showCountdown = true;
                                                        } else {
                                                            $statusLabel = 'Expiring in ' . $daysLeft . ' day' . ($daysLeft === 1 ? '' : 's');
                                                        }
                                                        $badgeClass = 'warning';
                                                    } else {
                                                        $statusLabel = 'Valid';
                                                        $badgeClass = 'secondary';
                                                    }
                                                }
                                                ?>
                                                <tr>
                                                    <td><?= $row++; ?></td>
                                                    <td><?= htmlspecialchars($p->sku, ENT_QUOTES, 'UTF-8'); ?></td>
                                                    <td><?= htmlspecialchars($p->name, ENT_QUOTES, 'UTF-8'); ?></td>
                                                    <td><?= htmlspecialchars($p->category ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                                                    <td class="text-right"><?= number_format((float)$p->unit_price, 2); ?></td>
                                                    <td class="text-right"><?= (int)$p->stock_qty; ?></td>
                                                    <td>
                                                        <?php if ($expiryDate): ?>
                                                            <?= htmlspecialchars(date('M d, Y', strtotime($expiryDate)), ENT_QUOTES, 'UTF-8'); ?>
                                                        <?php else: ?>
                                                            —
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <div class="d-flex flex-column">
                                                            <?php
                                                                $pillClass = 'status-secondary';
                                                                if ($badgeClass === 'danger') {
                                                                    $pillClass = 'status-danger';
                                                                } elseif ($badgeClass === 'warning') {
                                                                    $pillClass = 'status-warning';
                                                                }
                                                            ?>
                                                            <span class="status-pill <?= $pillClass; ?>"><?= htmlspecialchars($statusLabel, ENT_QUOTES, 'UTF-8'); ?></span>
                                                            <?php if ($showCountdown && $expiryDate): ?>
                                                                <span class="text-muted small mt-1 expiry-countdown" data-expiry="<?= htmlspecialchars($expiryDate, ENT_QUOTES, 'UTF-8'); ?>"></span>
                                                            <?php endif; ?>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="8" class="text-center text-muted py-4"><?= htmlspecialchars($empty_text ?? 'No products to display.', ENT_QUOTES, 'UTF-8'); ?></td>
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
            $('#expiryTable').DataTable({
                responsive: {
                    details: {
                        type: 'inline',
                        target: 'td:not(.no-details)'
                    }
                }
            });

            function updateCountdowns() {
                var nodes = document.querySelectorAll('.expiry-countdown[data-expiry]');
                var now = new Date();

                nodes.forEach(function(node) {
                    var expiry = node.getAttribute('data-expiry');
                    if (!expiry) return;

                    var endOfDay = new Date(expiry + 'T23:59:59+08:00'); // Asia/Manila
                    var diff = endOfDay - now;
                    if (diff <= 0) {
                        node.textContent = 'Expired';
                        return;
                    }

                    var hours = Math.floor(diff / (1000 * 60 * 60));
                    var minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
                    var seconds = Math.floor((diff % (1000 * 60)) / 1000);

                    var targetTime = endOfDay.toLocaleTimeString('en-US', {
                        hour: 'numeric',
                        minute: '2-digit',
                        second: '2-digit',
                        hour12: true,
                        timeZone: 'Asia/Manila'
                    });

                    node.textContent = hours + 'h ' + minutes + 'm ' + seconds + 's left (expires at ' + targetTime + ')';
                });
            }

            updateCountdowns();
            setInterval(updateCountdowns, 1000);
        });
    </script>
</body>

</html>
