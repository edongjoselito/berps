<?php
$summary = is_array($summary ?? null) ? $summary : [];
$daily = is_array($daily ?? null) ? $daily : [];
$cashier = is_array($cashier ?? null) ? $cashier : [];
$paymentModes = is_array($payment_modes ?? null) ? $payment_modes : [];
$topProducts = is_array($top_products ?? null) ? $top_products : [];
$installmentsDue = is_array($installments_due ?? null) ? $installments_due : [];
$lowStock = is_array($low_stock ?? null) ? $low_stock : [];
$inventoryMovements = is_array($inventory_movements ?? null) ? $inventory_movements : [];
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
                <div class="container-fluid pos-reports-page">
                    <style>
                        .pos-reports-page {
                            background:
                                radial-gradient(circle at top left, rgba(30, 64, 175, 0.08), transparent 26%),
                                radial-gradient(circle at top right, rgba(5, 150, 105, 0.08), transparent 24%),
                                linear-gradient(180deg, #f8fbff 0%, #f2f6fa 100%);
                            min-height: 100vh;
                            padding: 24px 0 32px;
                        }

                        .pos-reports-page .page-header {
                            display: flex;
                            justify-content: space-between;
                            align-items: flex-end;
                            gap: 16px;
                            flex-wrap: wrap;
                            margin-bottom: 18px;
                        }

                        .pos-reports-page .page-title {
                            margin: 0;
                            font-size: 2rem;
                            font-weight: 800;
                            color: #17324a;
                            letter-spacing: -0.04em;
                        }

                        .pos-reports-page .page-subtitle {
                            margin-top: 8px;
                            color: #62778d;
                            max-width: 760px;
                        }

                        .pos-reports-page .page-actions {
                            display: flex;
                            gap: 10px;
                            flex-wrap: wrap;
                        }

                        .pos-reports-page .btn-action {
                            display: inline-flex;
                            align-items: center;
                            justify-content: center;
                            gap: 8px;
                            padding: 10px 16px;
                            border-radius: 12px;
                            font-weight: 700;
                            text-decoration: none;
                        }

                        .pos-reports-page .btn-ghost {
                            border: 1px solid #d7e1eb;
                            background: rgba(255, 255, 255, 0.9);
                            color: #17324a;
                        }

                        .pos-reports-page .btn-primary-soft {
                            border: 1px solid rgba(30, 64, 175, 0.18);
                            background: rgba(30, 64, 175, 0.10);
                            color: #1d4ed8;
                        }

                        .pos-reports-page .stat-grid {
                            display: grid;
                            grid-template-columns: repeat(5, minmax(0, 1fr));
                            gap: 14px;
                            margin-bottom: 18px;
                        }

                        .pos-reports-page .stat-card,
                        .pos-reports-page .theme-card {
                            background: #fff;
                            border-radius: 20px;
                            border: 1px solid rgba(255, 255, 255, 0.8);
                            box-shadow: 0 18px 42px rgba(15, 23, 42, 0.08);
                        }

                        .pos-reports-page .stat-card {
                            padding: 18px 20px;
                        }

                        .pos-reports-page .stat-label {
                            color: #75879b;
                            font-size: 0.74rem;
                            text-transform: uppercase;
                            letter-spacing: 0.08em;
                            font-weight: 700;
                        }

                        .pos-reports-page .stat-value {
                            margin-top: 8px;
                            color: #17324a;
                            font-size: 1.55rem;
                            font-weight: 800;
                            letter-spacing: -0.04em;
                        }

                        .pos-reports-page .stat-meta {
                            margin-top: 6px;
                            color: #62778d;
                            font-size: 0.85rem;
                        }

                        .pos-reports-page .theme-card {
                            margin-bottom: 18px;
                        }

                        .pos-reports-page .theme-card-head {
                            padding: 18px 20px;
                            border-bottom: 1px solid #e8eef4;
                            background: linear-gradient(180deg, rgba(255, 255, 255, 0.98), rgba(247, 250, 252, 0.94));
                        }

                        .pos-reports-page .theme-card-title {
                            margin: 0;
                            color: #17324a;
                            font-weight: 800;
                        }

                        .pos-reports-page .theme-card-subtitle {
                            margin-top: 6px;
                            color: #62778d;
                            font-size: 0.9rem;
                        }

                        .pos-reports-page .theme-card-body {
                            padding: 20px;
                        }

                        .pos-reports-page .table thead th {
                            border-top: 0;
                            color: #60758a;
                            font-size: 0.76rem;
                            letter-spacing: 0.08em;
                            text-transform: uppercase;
                        }

                        @media (max-width: 1199.98px) {
                            .pos-reports-page .stat-grid {
                                grid-template-columns: repeat(3, minmax(0, 1fr));
                            }
                        }

                        @media (max-width: 767.98px) {
                            .pos-reports-page .stat-grid {
                                grid-template-columns: repeat(2, minmax(0, 1fr));
                            }
                        }

                        @media (max-width: 575.98px) {
                            .pos-reports-page .stat-grid {
                                grid-template-columns: minmax(0, 1fr);
                            }
                        }
                    </style>

                    <?php if (!empty($notice)): ?>
                        <div class="alert alert-<?= ($notice_type === 'error') ? 'danger' : 'success'; ?> alert-dismissible fade show" role="alert">
                            <?= htmlspecialchars($notice, ENT_QUOTES, 'UTF-8'); ?>
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    <?php endif; ?>

                    <div class="page-header">
                        <div>
                            <h4 class="page-title"><?= htmlspecialchars($page_title ?? 'POS Reports', ENT_QUOTES, 'UTF-8'); ?></h4>
                            <p class="page-subtitle"><?= htmlspecialchars($page_subtitle ?? '', ENT_QUOTES, 'UTF-8'); ?></p>
                        </div>
                        <div class="page-actions">
                            <a class="btn-action btn-ghost" href="<?= base_url('Pos/posNewTransaction'); ?>">
                                <i class="mdi mdi-plus-box-outline"></i> New Sale
                            </a>
                            <a class="btn-action btn-primary-soft" href="<?= base_url('Pos/posTransactionHistory'); ?>">
                                <i class="mdi mdi-history"></i> Sales History
                            </a>
                        </div>
                    </div>

                    <div class="theme-card">
                        <div class="theme-card-head">
                            <div class="theme-card-title">Report Filters</div>
                            <div class="theme-card-subtitle">Choose a period to refresh the sales, collection, installment, and inventory summaries.</div>
                        </div>
                        <div class="theme-card-body">
                            <form method="get">
                                <div class="form-row">
                                    <div class="form-group col-md-4">
                                        <label for="reportDateFrom">From</label>
                                        <input type="date" class="form-control" id="reportDateFrom" name="date_from" value="<?= htmlspecialchars((string) ($date_from ?? date('Y-m-01')), ENT_QUOTES, 'UTF-8'); ?>">
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label for="reportDateTo">To</label>
                                        <input type="date" class="form-control" id="reportDateTo" name="date_to" value="<?= htmlspecialchars((string) ($date_to ?? date('Y-m-d')), ENT_QUOTES, 'UTF-8'); ?>">
                                    </div>
                                    <div class="form-group col-md-4 d-flex align-items-end">
                                        <div>
                                            <button type="submit" class="btn btn-primary mr-2">Run Report</button>
                                            <a class="btn btn-light" href="<?= base_url('Pos/posReports'); ?>">Reset</a>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="stat-grid">
                        <div class="stat-card">
                            <div class="stat-label">Gross Sales</div>
                            <div class="stat-value"><?= number_format((float) ($summary['gross_sales'] ?? 0), 2); ?></div>
                            <div class="stat-meta"><?= number_format((int) ($summary['sales_count'] ?? 0)); ?> transaction(s) in range</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-label">Collections</div>
                            <div class="stat-value"><?= number_format((float) ($summary['collections'] ?? 0), 2); ?></div>
                            <div class="stat-meta">Payments actually received within the period</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-label">Discounts</div>
                            <div class="stat-value"><?= number_format((float) ($summary['discount_amount'] ?? 0), 2); ?></div>
                            <div class="stat-meta">Senior, PWD, and regular discounts granted</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-label">VAT</div>
                            <div class="stat-value"><?= number_format((float) ($summary['vat_amount'] ?? 0), 2); ?></div>
                            <div class="stat-meta">VAT portion of report-period sales</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-label">Outstanding</div>
                            <div class="stat-value"><?= number_format((float) ($summary['outstanding_balance'] ?? 0), 2); ?></div>
                            <div class="stat-meta"><?= number_format((int) ($summary['installment_count'] ?? 0)); ?> installment sale(s) still open</div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-lg-7">
                            <div class="theme-card">
                                <div class="theme-card-head">
                                    <div class="theme-card-title">Daily Sales vs Collections</div>
                                    <div class="theme-card-subtitle">Compare what was sold to what was actually collected each day in the selected period.</div>
                                </div>
                                <div class="theme-card-body">
                                    <div class="table-responsive">
                                        <table class="table table-hover mb-0">
                                            <thead>
                                                <tr>
                                                    <th>Date</th>
                                                    <th class="text-right">Transactions</th>
                                                    <th class="text-right">Sales</th>
                                                    <th class="text-right">Collections</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($daily as $row): ?>
                                                    <tr>
                                                        <td><?= htmlspecialchars(date('M d, Y', strtotime((string) ($row['date'] ?? date('Y-m-d')))), ENT_QUOTES, 'UTF-8'); ?></td>
                                                        <td class="text-right"><?= number_format((int) ($row['transactions'] ?? 0)); ?></td>
                                                        <td class="text-right"><?= number_format((float) ($row['sales'] ?? 0), 2); ?></td>
                                                        <td class="text-right"><?= number_format((float) ($row['collections'] ?? 0), 2); ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-5">
                            <div class="theme-card">
                                <div class="theme-card-head">
                                    <div class="theme-card-title">Payment Modes</div>
                                    <div class="theme-card-subtitle">Shows where collections are coming from across cash and digital channels.</div>
                                </div>
                                <div class="theme-card-body">
                                    <div class="table-responsive">
                                        <table class="table table-hover mb-0">
                                            <thead>
                                                <tr>
                                                    <th>Payment Mode</th>
                                                    <th class="text-right">Count</th>
                                                    <th class="text-right">Amount</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php if (!empty($paymentModes)): ?>
                                                    <?php foreach ($paymentModes as $row): ?>
                                                        <tr>
                                                            <td><?= htmlspecialchars((string) ($row['payment_mode'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                                                            <td class="text-right"><?= number_format((int) ($row['count'] ?? 0)); ?></td>
                                                            <td class="text-right"><?= number_format((float) ($row['amount'] ?? 0), 2); ?></td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                <?php else: ?>
                                                    <tr>
                                                        <td colspan="3" class="text-center text-muted py-4">No collection entries found in the selected range.</td>
                                                    </tr>
                                                <?php endif; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-lg-6">
                            <div class="theme-card">
                                <div class="theme-card-head">
                                    <div class="theme-card-title">Cashier Summary</div>
                                    <div class="theme-card-subtitle">Which cashier encoded the most sales and where balances are still open.</div>
                                </div>
                                <div class="theme-card-body">
                                    <div class="table-responsive">
                                        <table class="table table-hover mb-0">
                                            <thead>
                                                <tr>
                                                    <th>Cashier</th>
                                                    <th class="text-right">Transactions</th>
                                                    <th class="text-right">Gross Sales</th>
                                                    <th class="text-right">Outstanding</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php if (!empty($cashier)): ?>
                                                    <?php foreach ($cashier as $row): ?>
                                                        <tr>
                                                            <td><?= htmlspecialchars((string) ($row['cashier_name'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                                                            <td class="text-right"><?= number_format((int) ($row['transactions'] ?? 0)); ?></td>
                                                            <td class="text-right"><?= number_format((float) ($row['gross_sales'] ?? 0), 2); ?></td>
                                                            <td class="text-right"><?= number_format((float) ($row['outstanding_balance'] ?? 0), 2); ?></td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                <?php else: ?>
                                                    <tr>
                                                        <td colspan="4" class="text-center text-muted py-4">No sales activity found.</td>
                                                    </tr>
                                                <?php endif; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="theme-card">
                                <div class="theme-card-head">
                                    <div class="theme-card-title">Top Products</div>
                                    <div class="theme-card-subtitle">Best-selling items ranked by generated revenue in the report period.</div>
                                </div>
                                <div class="theme-card-body">
                                    <div class="table-responsive">
                                        <table class="table table-hover mb-0">
                                            <thead>
                                                <tr>
                                                    <th>Product</th>
                                                    <th class="text-right">Qty</th>
                                                    <th class="text-right">Revenue</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php if (!empty($topProducts)): ?>
                                                    <?php foreach ($topProducts as $row): ?>
                                                        <tr>
                                                            <td>
                                                                <strong><?= htmlspecialchars((string) ($row['product_name'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></strong>
                                                                <?php if (!empty($row['sku'])): ?>
                                                                    <div class="text-muted small"><?= htmlspecialchars((string) $row['sku'], ENT_QUOTES, 'UTF-8'); ?></div>
                                                                <?php endif; ?>
                                                            </td>
                                                            <td class="text-right"><?= number_format((int) ($row['quantity'] ?? 0)); ?></td>
                                                            <td class="text-right"><?= number_format((float) ($row['revenue'] ?? 0), 2); ?></td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                <?php else: ?>
                                                    <tr>
                                                        <td colspan="3" class="text-center text-muted py-4">No product movement found in the selected range.</td>
                                                    </tr>
                                                <?php endif; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-lg-6">
                            <div class="theme-card">
                                <div class="theme-card-head">
                                    <div class="theme-card-title">Due and Overdue Installments</div>
                                    <div class="theme-card-subtitle">Installment lines due on or before the selected report end date.</div>
                                </div>
                                <div class="theme-card-body">
                                    <div class="table-responsive">
                                        <table class="table table-hover mb-0">
                                            <thead>
                                                <tr>
                                                    <th>Sale</th>
                                                    <th>Customer</th>
                                                    <th>Due Date</th>
                                                    <th class="text-right">Due</th>
                                                    <th class="text-right">Paid</th>
                                                    <th>Status</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php if (!empty($installmentsDue)): ?>
                                                    <?php foreach ($installmentsDue as $row): ?>
                                                        <tr>
                                                            <td><?= htmlspecialchars((string) ($row->sale_no ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                                                            <td><?= htmlspecialchars((string) ($row->customer_name ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                                                            <td><?= htmlspecialchars(date('M d, Y', strtotime((string) ($row->due_date ?? date('Y-m-d')))), ENT_QUOTES, 'UTF-8'); ?></td>
                                                            <td class="text-right"><?= number_format((float) ($row->amount_due ?? 0), 2); ?></td>
                                                            <td class="text-right"><?= number_format((float) ($row->amount_paid ?? 0), 2); ?></td>
                                                            <td><?= htmlspecialchars((string) ($row->status ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                <?php else: ?>
                                                    <tr>
                                                        <td colspan="6" class="text-center text-muted py-4">No due installment entries in the selected range.</td>
                                                    </tr>
                                                <?php endif; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="theme-card">
                                <div class="theme-card-head">
                                    <div class="theme-card-title">Low Stock Watchlist</div>
                                    <div class="theme-card-subtitle">Products already at or below their reorder level.</div>
                                </div>
                                <div class="theme-card-body">
                                    <div class="table-responsive">
                                        <table class="table table-hover mb-0">
                                            <thead>
                                                <tr>
                                                    <th>SKU</th>
                                                    <th>Product</th>
                                                    <th class="text-right">Stock</th>
                                                    <th class="text-right">Reorder</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php if (!empty($lowStock)): ?>
                                                    <?php foreach ($lowStock as $row): ?>
                                                        <tr>
                                                            <td><?= htmlspecialchars((string) ($row->sku ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                                                            <td><?= htmlspecialchars((string) ($row->name ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                                                            <td class="text-right"><?= number_format((int) ($row->stock_qty ?? 0)); ?></td>
                                                            <td class="text-right"><?= number_format((int) ($row->reorder_level ?? 0)); ?></td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                <?php else: ?>
                                                    <tr>
                                                        <td colspan="4" class="text-center text-muted py-4">No low stock items right now.</td>
                                                    </tr>
                                                <?php endif; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="theme-card">
                        <div class="theme-card-head">
                            <div class="theme-card-title">Recent Inventory Movements</div>
                            <div class="theme-card-subtitle">Shows opening balances, sales deductions, void restores, and stock adjustments captured in the selected period.</div>
                        </div>
                        <div class="theme-card-body">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th>Date / Time</th>
                                            <th>Product</th>
                                            <th>Movement</th>
                                            <th class="text-right">Change</th>
                                            <th class="text-right">Before</th>
                                            <th class="text-right">After</th>
                                            <th>Remarks</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($inventoryMovements)): ?>
                                            <?php foreach ($inventoryMovements as $row): ?>
                                                <tr>
                                                    <td><?= htmlspecialchars(date('M d, Y h:i A', strtotime((string) ($row->created_at ?? date('Y-m-d H:i:s')))), ENT_QUOTES, 'UTF-8'); ?></td>
                                                    <td>
                                                        <strong><?= htmlspecialchars((string) ($row->name ?? ''), ENT_QUOTES, 'UTF-8'); ?></strong>
                                                        <div class="text-muted small"><?= htmlspecialchars((string) ($row->sku ?? ''), ENT_QUOTES, 'UTF-8'); ?></div>
                                                    </td>
                                                    <td><?= htmlspecialchars(ucwords(str_replace('_', ' ', (string) ($row->movement_type ?? ''))), ENT_QUOTES, 'UTF-8'); ?></td>
                                                    <td class="text-right"><?= number_format((int) ($row->quantity_change ?? 0)); ?></td>
                                                    <td class="text-right"><?= number_format((int) ($row->qty_before ?? 0)); ?></td>
                                                    <td class="text-right"><?= number_format((int) ($row->qty_after ?? 0)); ?></td>
                                                    <td><?= htmlspecialchars((string) ($row->remarks ?? '—'), ENT_QUOTES, 'UTF-8'); ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="7" class="text-center text-muted py-4">No inventory movement records found in the selected range.</td>
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
