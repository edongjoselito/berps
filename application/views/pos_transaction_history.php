<?php
$summary = is_array($list_summary ?? null) ? $list_summary : ['count' => 0, 'gross_total' => 0, 'paid_total' => 0, 'balance_total' => 0, 'installment_total' => 0];
$sales = is_array($sales ?? null) ? $sales : [];
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
                <div class="container-fluid pos-history-page">
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
                            grid-template-columns: repeat(4, minmax(0, 1fr));
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
                        }

                        .pos-history-page .status-pill {
                            display: inline-flex;
                            align-items: center;
                            justify-content: center;
                            padding: 6px 10px;
                            border-radius: 999px;
                            font-weight: 700;
                            font-size: 0.78rem;
                        }

                        .status-paid {
                            color: #166534;
                            background: #ecfdf3;
                        }

                        .status-partially {
                            color: #92400e;
                            background: #fff7ed;
                        }

                        .status-unpaid {
                            color: #b91c1c;
                            background: #fef2f2;
                        }

                        .status-voided {
                            color: #475569;
                            background: #e2e8f0;
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

                        /* Hero Banner */
                        .pos-history-page .ph-hero {
                            display: flex;
                            align-items: center;
                            justify-content: space-between;
                            flex-wrap: wrap;
                            gap: 16px;
                            padding: 28px 24px;
                            margin: 0 0 22px;
                            border-radius: 16px;
                            background: #0e7490;
                            box-shadow: 0 8px 32px rgba(14, 116, 144, 0.25);
                            position: relative;
                            overflow: hidden;
                        }

                        .pos-history-page .ph-hero::before {
                            content: '';
                            position: absolute;
                            top: -50%;
                            right: -10%;
                            width: 400px;
                            height: 400px;
                            border-radius: 50%;
                            background: rgba(255, 255, 255, 0.06);
                            pointer-events: none;
                        }

                        .pos-history-page .ph-hero::after {
                            content: '';
                            position: absolute;
                            bottom: -60%;
                            right: 15%;
                            width: 300px;
                            height: 300px;
                            border-radius: 50%;
                            background: rgba(255, 255, 255, 0.04);
                            pointer-events: none;
                        }

                        .pos-history-page .ph-hero__content {
                            position: relative;
                            z-index: 1;
                        }

                        .pos-history-page .ph-hero__eyebrow {
                            display: inline-flex;
                            align-items: center;
                            gap: 6px;
                            margin-bottom: 8px;
                            color: rgba(255, 255, 255, 0.85);
                            font-size: 0.78rem;
                            font-weight: 600;
                            letter-spacing: 0.04em;
                        }

                        .pos-history-page .ph-hero__eyebrow i {
                            font-size: 1rem;
                        }

                        .pos-history-page .ph-hero__title {
                            margin: 0 0 4px 0;
                            color: #fff;
                            font-size: clamp(1.6rem, 2.5vw, 2.2rem);
                            font-weight: 800;
                            line-height: 1.15;
                            letter-spacing: -0.02em;
                        }

                        .pos-history-page .ph-hero__subtitle {
                            margin: 0;
                            color: rgba(255, 255, 255, 0.8);
                            font-size: 0.88rem;
                            max-width: 520px;
                        }

                        .pos-history-page .ph-hero__actions {
                            display: flex;
                            align-items: center;
                            flex-wrap: wrap;
                            gap: 10px;
                            position: relative;
                            z-index: 1;
                        }

                        .pos-history-page .ph-hero-btn {
                            display: inline-flex;
                            align-items: center;
                            gap: 6px;
                            padding: 8px 16px;
                            border-radius: 10px;
                            border: 1px solid rgba(255, 255, 255, 0.3);
                            background: rgba(255, 255, 255, 0.15);
                            color: #fff;
                            font-size: 0.82rem;
                            font-weight: 600;
                            text-decoration: none;
                            cursor: pointer;
                            transition: all 0.18s ease;
                        }

                        .pos-history-page .ph-hero-btn:hover,
                        .pos-history-page .ph-hero-btn:focus {
                            background: rgba(255, 255, 255, 0.25);
                            border-color: rgba(255, 255, 255, 0.5);
                            color: #fff;
                            text-decoration: none;
                            transform: translateY(-1px);
                        }

                        .pos-history-page .ph-hero-btn--solid {
                            border-color: rgba(255, 255, 255, 0.6);
                            background: rgba(255, 255, 255, 0.95);
                            color: #0e7490;
                            font-weight: 700;
                        }

                        .pos-history-page .ph-hero-btn--solid:hover,
                        .pos-history-page .ph-hero-btn--solid:focus {
                            background: #fff;
                            color: #0b5d72;
                        }

                        /* Receipt shake animation */
                        .pos-history-page .receipt-shake {
                            display: inline-block;
                            animation: receipt-shake 2.5s ease-in-out infinite;
                        }

                        @keyframes receipt-shake {
                            0%, 70%, 100% { transform: rotate(0deg); }
                            15% { transform: rotate(-12deg); }
                            30% { transform: rotate(8deg); }
                            45% { transform: rotate(-5deg); }
                            60% { transform: rotate(0deg); }
                        }

                        /* Teal accent on cards */
                        .pos-history-page .stat-card,
                        .pos-history-page .theme-card {
                            border-top: 3px solid #0e7490;
                        }

                        .pos-history-page .theme-card-head {
                            border-bottom: 2px solid #0e7490;
                        }

                        .pos-history-page .theme-card-title {
                            color: #0e7490;
                        }

                        /* Responsive hero */
                        @media (max-width: 767px) {
                            .pos-history-page .ph-hero,
                            .pos-history-page .ph-hero__actions {
                                flex-direction: column;
                                align-items: stretch;
                            }

                            .pos-history-page .ph-hero {
                                padding: 20px;
                            }

                            .pos-history-page .ph-hero-btn {
                                flex: 1 1 auto;
                                justify-content: center;
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

                    <div class="ph-hero">
                        <div class="ph-hero__content">
                            <div class="ph-hero__eyebrow">
                                <i class="mdi mdi-history"></i>
                                Transaction History
                            </div>
                            <h1 class="ph-hero__title"><?= htmlspecialchars($page_title ?? 'POS History', ENT_QUOTES, 'UTF-8'); ?> <span class="receipt-shake">🧾</span></h1>
                            <p class="ph-hero__subtitle"><?= htmlspecialchars($page_subtitle ?? '', ENT_QUOTES, 'UTF-8'); ?></p>
                        </div>
                        <div class="ph-hero__actions">
                            <a class="ph-hero-btn" href="<?= base_url('Pos/posNewTransaction'); ?>">
                                <i class="mdi mdi-plus-box-outline"></i>
                                <span>New Sale</span>
                            </a>
                            <a class="ph-hero-btn ph-hero-btn--solid" href="<?= base_url('Pos/posReports'); ?>">
                                <i class="mdi mdi-chart-line"></i>
                                <span>Reports</span>
                            </a>
                        </div>
                    </div>

                    <div class="stat-grid">
                        <div class="stat-card">
                            <div class="stat-label">Transactions</div>
                            <div class="stat-value"><?= number_format((int) ($summary['count'] ?? 0)); ?></div>
                            <div class="stat-meta">Matching the selected filters</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-label">Gross Total</div>
                            <div class="stat-value"><?= number_format((float) ($summary['gross_total'] ?? 0), 2); ?></div>
                            <div class="stat-meta">Combined grand total of shown sales</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-label">Collections</div>
                            <div class="stat-value"><?= number_format((float) ($summary['paid_total'] ?? 0), 2); ?></div>
                            <div class="stat-meta">Amount already paid across shown sales</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-label"><?= !empty($void_mode) ? 'Voided Sales' : 'Outstanding Balance'; ?></div>
                            <div class="stat-value"><?= !empty($void_mode) ? number_format((int) ($summary['count'] ?? 0)) : number_format((float) ($summary['balance_total'] ?? 0), 2); ?></div>
                            <div class="stat-meta"><?= !empty($void_mode) ? 'Transactions already voided' : number_format((int) ($summary['installment_total'] ?? 0)) . ' installment sale(s) in this result'; ?></div>
                        </div>
                    </div>

                    <div class="theme-card">
                        <div class="theme-card-head">
                            <div class="theme-card-title">Filter Sales</div>
                            <div class="theme-card-subtitle">Narrow the result set by date, status, or keyword before reviewing individual transactions.</div>
                        </div>
                        <div class="theme-card-body">
                            <form method="get">
                                <div class="form-row">
                                    <div class="form-group col-md-3">
                                        <label for="filterDateFrom">From</label>
                                        <input type="date" class="form-control" id="filterDateFrom" name="date_from" value="<?= htmlspecialchars((string) ($filter_date_from ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
                                    </div>
                                    <div class="form-group col-md-3">
                                        <label for="filterDateTo">To</label>
                                        <input type="date" class="form-control" id="filterDateTo" name="date_to" value="<?= htmlspecialchars((string) ($filter_date_to ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
                                    </div>
                                    <?php if (empty($void_mode)): ?>
                                        <div class="form-group col-md-3">
                                            <label for="filterStatus">Status</label>
                                            <select class="form-control" id="filterStatus" name="status">
                                                <option value="">All Active Sales</option>
                                                <?php foreach (['Paid', 'Partially Paid', 'Unpaid'] as $statusOption): ?>
                                                    <option value="<?= htmlspecialchars($statusOption, ENT_QUOTES, 'UTF-8'); ?>" <?= ($filter_status ?? '') === $statusOption ? 'selected' : ''; ?>>
                                                        <?= htmlspecialchars($statusOption, ENT_QUOTES, 'UTF-8'); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    <?php else: ?>
                                        <input type="hidden" name="status" value="Voided">
                                    <?php endif; ?>
                                    <div class="form-group col-md-<?= empty($void_mode) ? '3' : '6'; ?>">
                                        <label for="filterSearch">Search</label>
                                        <input type="text" class="form-control" id="filterSearch" name="search" value="<?= htmlspecialchars((string) ($filter_search ?? ''), ENT_QUOTES, 'UTF-8'); ?>" placeholder="Sale no., customer, cashier">
                                    </div>
                                </div>
                                <div class="d-flex flex-wrap gap-2">
                                    <button type="submit" class="btn btn-primary mr-2">Apply Filters</button>
                                    <a class="btn btn-light" href="<?= htmlspecialchars(current_url(), ENT_QUOTES, 'UTF-8'); ?>">Reset</a>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="theme-card">
                        <div class="theme-card-head">
                            <div class="theme-card-title"><?= !empty($void_mode) ? 'Voided Transactions' : 'Sales Ledger'; ?></div>
                            <div class="theme-card-subtitle"><?= !empty($void_mode) ? 'Voided sales are excluded from active reports and inventory is restored automatically.' : 'Click a row action to view payment history, installment details, and printable sale information.'; ?></div>
                        </div>
                        <div class="theme-card-body">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0" id="salesTable">
                                    <thead>
                                        <tr>
                                            <th>Sale No.</th>
                                            <th>Date</th>
                                            <th>Customer</th>
                                            <th>Cashier</th>
                                            <th>Payment Term</th>
                                            <th>Status</th>
                                            <th class="text-right">Grand Total</th>
                                            <th class="text-right">Paid</th>
                                            <th class="text-right">Balance</th>
                                            <th class="text-center">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($sales)): ?>
                                            <?php foreach ($sales as $sale): ?>
                                                <?php
                                                $status = trim((string) ($sale->status ?? 'Unpaid'));
                                                $statusClass = 'status-unpaid';
                                                if ($status === 'Paid') {
                                                    $statusClass = 'status-paid';
                                                } elseif ($status === 'Partially Paid') {
                                                    $statusClass = 'status-partially';
                                                } elseif ($status === 'Voided') {
                                                    $statusClass = 'status-voided';
                                                }
                                                ?>
                                                <tr>
                                                    <td>
                                                        <strong><?= htmlspecialchars((string) ($sale->sale_no ?? ''), ENT_QUOTES, 'UTF-8'); ?></strong>
                                                        <?php if (!empty($sale->discount_label)): ?>
                                                            <div class="text-muted small"><?= htmlspecialchars((string) $sale->discount_label, ENT_QUOTES, 'UTF-8'); ?></div>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td><?= htmlspecialchars(date('M d, Y', strtotime((string) ($sale->transaction_date ?? date('Y-m-d')))), ENT_QUOTES, 'UTF-8'); ?></td>
                                                    <td><?= htmlspecialchars((string) ($sale->customer_name ?? 'Walk-in Customer'), ENT_QUOTES, 'UTF-8'); ?></td>
                                                    <td><?= htmlspecialchars((string) ($sale->cashier_name ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                                                    <td><?= htmlspecialchars(ucfirst((string) ($sale->payment_term ?? 'full')), ENT_QUOTES, 'UTF-8'); ?></td>
                                                    <td><span class="status-pill <?= $statusClass; ?>"><?= htmlspecialchars($status, ENT_QUOTES, 'UTF-8'); ?></span></td>
                                                    <td class="text-right"><?= number_format((float) ($sale->grand_total ?? 0), 2); ?></td>
                                                    <td class="text-right"><?= number_format((float) ($sale->amount_paid ?? 0), 2); ?></td>
                                                    <td class="text-right"><?= number_format((float) ($sale->balance_due ?? 0), 2); ?></td>
                                                    <td class="text-center">
                                                        <a href="<?= base_url('Pos/posTransactionDetail/' . (int) ($sale->id ?? 0)); ?>" class="btn btn-sm btn-outline-primary">
                                                            View
                                                        </a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="10" class="text-center text-muted py-4">
                                                    <?= !empty($void_mode) ? 'No voided transactions found for the selected filters.' : 'No POS sales found for the selected filters.'; ?>
                                                </td>
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
    <script src="<?= base_url(); ?>assets/libs/datatables/jquery.dataTables.min.js"></script>
    <script src="<?= base_url(); ?>assets/libs/datatables/dataTables.bootstrap4.min.js"></script>
    <script src="<?= base_url(); ?>assets/libs/datatables/dataTables.responsive.min.js"></script>
    <script src="<?= base_url(); ?>assets/libs/datatables/responsive.bootstrap4.min.js"></script>
    <script src="<?= base_url(); ?>assets/js/app.min.js"></script>
    <script>
        $(function() {
            $('#salesTable').DataTable({
                responsive: true,
                pageLength: 25,
                order: [
                    [1, 'desc']
                ]
            });
        });
    </script>
</body>

</html>
