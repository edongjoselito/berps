<?php
$businessData = isset($business) ? $business : null;
$clients = isset($clients) && is_array($clients) ? $clients : array();
$paymentSources = isset($paymentSources) && is_array($paymentSources) ? $paymentSources : array();
$paymentCashiers = isset($paymentCashiers) && is_array($paymentCashiers) ? $paymentCashiers : array();

$recurringTemplates = isset($recurringTemplates) && is_array($recurringTemplates) ? $recurringTemplates : array();
$recurringFrequencySummaries = isset($recurringFrequencySummaries) && is_array($recurringFrequencySummaries) ? $recurringFrequencySummaries : array();
$projectedIncomeByMonth = isset($projectedIncomeByMonth) && is_array($projectedIncomeByMonth) ? $projectedIncomeByMonth : array();
$projectedRecurringTotals = isset($projectedRecurringTotals) && is_array($projectedRecurringTotals) ? $projectedRecurringTotals : array();

$acceptedPayments = isset($acceptedPayments) && is_array($acceptedPayments) ? $acceptedPayments : array();
$collectionTotals = isset($collectionTotals) && is_array($collectionTotals) ? $collectionTotals : array();
$acceptedPaymentMonthlySummaries = isset($acceptedPaymentMonthlySummaries) && is_array($acceptedPaymentMonthlySummaries) ? $acceptedPaymentMonthlySummaries : array();
$acceptedPaymentYearlySummaries = isset($acceptedPaymentYearlySummaries) && is_array($acceptedPaymentYearlySummaries) ? $acceptedPaymentYearlySummaries : array();
$acceptedPaymentSourceSummaries = isset($acceptedPaymentSourceSummaries) && is_array($acceptedPaymentSourceSummaries) ? $acceptedPaymentSourceSummaries : array();
$acceptedPaymentCashierSummaries = isset($acceptedPaymentCashierSummaries) && is_array($acceptedPaymentCashierSummaries) ? $acceptedPaymentCashierSummaries : array();
$monthlyComparisonSummaries = isset($monthlyComparisonSummaries) && is_array($monthlyComparisonSummaries) ? $monthlyComparisonSummaries : array();

$filterDateFrom = isset($filterDateFrom) ? trim((string) $filterDateFrom) : date('Y-01-01');
$filterDateTo = isset($filterDateTo) ? trim((string) $filterDateTo) : date('Y-12-31');
$rangeLabel = isset($rangeLabel) ? (string) $rangeLabel : 'Current range';
$generatedAt = isset($generatedAt) ? (string) $generatedAt : date('F j, Y h:i A');
$recurringFrequencyFilter = isset($recurringFrequencyFilter) ? (string) $recurringFrequencyFilter : 'all';
$selectedCustID = isset($selectedCustID) ? (string) $selectedCustID : '';
$selectedPaymentSource = isset($selectedPaymentSource) ? (string) $selectedPaymentSource : '';
$selectedCashier = isset($selectedCashier) ? (string) $selectedCashier : '';

$businessName = trim((string) ($businessData->CompName ?? 'BERPS'));
$businessAddress = trim((string) ($businessData->CompAddress ?? ''));

$projectedTemplateCount = (int) ($projectedRecurringTotals['templateCount'] ?? 0);
$projectedOccurrenceCount = (int) ($projectedRecurringTotals['projectedOccurrenceCount'] ?? 0);
$projectedAmount = (float) ($projectedRecurringTotals['projectedAmount'] ?? 0);
$acceptedEntryCount = (int) ($collectionTotals['entryCount'] ?? 0);
$acceptedInvoiceCount = (int) ($collectionTotals['invoiceCount'] ?? 0);
$acceptedClientCount = (int) ($collectionTotals['clientCount'] ?? 0);
$acceptedCashAmount = (float) ($collectionTotals['cashAmount'] ?? 0);
$acceptedTaxAmount = (float) ($collectionTotals['taxAmount'] ?? 0);
$acceptedGrossAmount = (float) ($collectionTotals['grossAmount'] ?? 0);

$currentYearStartUrl = base_url() . 'Page/revenueReports?date_from=' . date('Y-01-01') . '&date_to=' . date('Y-12-31');
$currentMonthStartUrl = base_url() . 'Page/revenueReports?date_from=' . date('Y-m-01') . '&date_to=' . date('Y-m-t');
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
                <div class="container-fluid revenue-reports-page">

                    <style>
                        @import url('https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,100..1000;1,9..40,100..1000&display=swap');

                        .revenue-reports-page {
                            --bg: #f5f7fb;
                            --surface: rgba(255, 255, 255, 0.96);
                            --surface-strong: #ffffff;
                            --surface-soft: #f8fbff;
                            --line: #e4ebf4;
                            --line-strong: #cfdbea;
                            --text: #142235;
                            --text-soft: #617489;
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
                            --shadow: 0 16px 40px rgba(15, 23, 42, 0.06);
                            --shadow-soft: 0 8px 24px rgba(15, 23, 42, 0.04);
                            --radius-xl: 16px;
                            --radius-lg: 12px;
                            --radius-md: 10px;
                            --radius-sm: 8px;
                            --font-body: 'DM Sans', 'Segoe UI', Arial, sans-serif;
                            --font-head: 'DM Sans', 'Segoe UI', Arial, sans-serif;
                            --font-mono: 'DM Sans', 'SFMono-Regular', Consolas, monospace;
                            font-family: var(--font-body);
                            background:
                                radial-gradient(circle at top left, rgba(37, 99, 235, 0.08), transparent 28%),
                                radial-gradient(circle at top right, rgba(16, 185, 129, 0.08), transparent 24%),
                                linear-gradient(180deg, #f8fbff 0%, #f4f7fb 100%);
                            min-height: 100vh;
                            padding-bottom: 100px;
                        }

                        .revenue-reports-page * {
                            box-sizing: border-box;
                        }

                        .revenue-reports-page .content {
                            margin-bottom: 40px;
                        }

                        .revenue-reports-page .rr-header {
                            margin: 24px 0 22px;
                            display: flex;
                            justify-content: space-between;
                            align-items: flex-end;
                            gap: 16px;
                            flex-wrap: wrap;
                        }

                        .revenue-reports-page .rr-eyebrow {
                            display: inline-flex;
                            align-items: center;
                            gap: 8px;
                            padding: 7px 12px;
                            border-radius: 999px;
                            background: rgba(37, 99, 235, 0.08);
                            color: var(--primary-2);
                            font-size: 0.74rem;
                            font-weight: 700;
                            letter-spacing: 0.08em;
                            text-transform: uppercase;
                            margin-bottom: 12px;
                        }

                        .revenue-reports-page .rr-eyebrow::before {
                            content: '';
                            width: 8px;
                            height: 8px;
                            border-radius: 50%;
                            background: linear-gradient(135deg, var(--primary), var(--primary-2));
                            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.10);
                        }

                        .revenue-reports-page .rr-title {
                            margin: 0;
                            font-family: var(--font-head);
                            font-size: 2rem;
                            font-weight: 800;
                            letter-spacing: -0.04em;
                            color: var(--text);
                            line-height: 1.1;
                        }

                        .revenue-reports-page .rr-subtitle {
                            margin-top: 8px;
                            color: var(--text-soft);
                            font-size: 0.93rem;
                            font-weight: 500;
                        }

                        .revenue-reports-page .rr-actions {
                            display: flex;
                            gap: 10px;
                            flex-wrap: wrap;
                            justify-content: flex-end;
                        }

                        .revenue-reports-page .btn-soft,
                        .revenue-reports-page .btn-solid {
                            display: inline-flex;
                            align-items: center;
                            gap: 8px;
                            padding: 10px 16px;
                            border-radius: 12px;
                            font-size: 0.88rem;
                            font-weight: 700;
                            text-decoration: none;
                            transition: transform 0.16s ease, box-shadow 0.16s ease, filter 0.16s ease;
                        }

                        .revenue-reports-page .btn-soft {
                            background: rgba(255, 255, 255, 0.88);
                            color: var(--text);
                            border: 1px solid var(--line-strong);
                            box-shadow: var(--shadow-soft);
                        }

                        .revenue-reports-page .btn-solid {
                            background: linear-gradient(135deg, var(--primary), var(--primary-2));
                            color: #fff;
                            border: none;
                            box-shadow: 0 10px 24px rgba(37, 99, 235, 0.22);
                        }

                        .revenue-reports-page .btn-soft:hover,
                        .revenue-reports-page .btn-solid:hover {
                            transform: translateY(-1px);
                            filter: brightness(1.02);
                            text-decoration: none;
                        }

                        .revenue-reports-page .btn-solid:hover {
                            color: #fff;
                        }

                        .revenue-reports-page .stat-strip {
                            display: grid;
                            grid-template-columns: repeat(6, minmax(0, 1fr));
                            gap: 14px;
                            margin-bottom: 20px;
                        }

                        .revenue-reports-page .stat-card {
                            position: relative;
                            overflow: hidden;
                            background: var(--surface);
                            backdrop-filter: blur(12px);
                            border: 1px solid rgba(255, 255, 255, 0.7);
                            border-radius: var(--radius-xl);
                            box-shadow: var(--shadow-soft);
                            padding: 18px 18px 20px;
                            min-height: 118px;
                        }

                        .revenue-reports-page .stat-card::before {
                            content: '';
                            position: absolute;
                            inset: 0 0 auto 0;
                            height: 4px;
                            background: linear-gradient(90deg, rgba(37, 99, 235, 0.95), rgba(37, 99, 235, 0.28));
                        }

                        .revenue-reports-page .stat-card.is-success::before {
                            background: linear-gradient(90deg, rgba(5, 150, 105, 0.95), rgba(5, 150, 105, 0.28));
                        }

                        .revenue-reports-page .stat-card.is-warning::before {
                            background: linear-gradient(90deg, rgba(217, 119, 6, 0.95), rgba(217, 119, 6, 0.28));
                        }

                        .revenue-reports-page .stat-card.is-danger::before {
                            background: linear-gradient(90deg, rgba(225, 29, 72, 0.95), rgba(225, 29, 72, 0.28));
                        }

                        .revenue-reports-page .stat-label {
                            color: var(--text-faint);
                            font-size: 0.74rem;
                            font-weight: 800;
                            letter-spacing: 0.08em;
                            text-transform: uppercase;
                        }

                        .revenue-reports-page .stat-value {
                            margin-top: 10px;
                            color: var(--text);
                            font-size: 1.5rem;
                            line-height: 1.1;
                            font-weight: 800;
                            font-family: var(--font-mono);
                        }

                        .revenue-reports-page .stat-meta {
                            margin-top: 8px;
                            color: var(--text-soft);
                            font-size: 0.8rem;
                        }

                        .revenue-reports-page .filter-card,
                        .revenue-reports-page .panel-card {
                            background: var(--surface);
                            backdrop-filter: blur(12px);
                            border-radius: var(--radius-xl);
                            border: 1px solid rgba(255, 255, 255, 0.72);
                            box-shadow: var(--shadow);
                        }

                        .revenue-reports-page .filter-card {
                            margin-bottom: 20px;
                        }

                        .revenue-reports-page .filter-body,
                        .revenue-reports-page .panel-body {
                            padding: 20px 22px;
                        }

                        .revenue-reports-page .panel-header {
                            padding: 18px 22px;
                            border-bottom: 1px solid var(--line);
                            display: flex;
                            justify-content: space-between;
                            align-items: center;
                            gap: 16px;
                            flex-wrap: wrap;
                        }

                        .revenue-reports-page .panel-title {
                            margin: 0;
                            font-family: var(--font-head);
                            font-size: 1rem;
                            font-weight: 800;
                            color: var(--text);
                            letter-spacing: -0.02em;
                        }

                        .revenue-reports-page .panel-subtitle {
                            margin: 6px 0 0;
                            color: var(--text-soft);
                            font-size: 0.84rem;
                        }

                        .revenue-reports-page .filter-grid,
                        .revenue-reports-page .summary-grid {
                            display: grid;
                            grid-template-columns: repeat(4, minmax(0, 1fr));
                            gap: 14px;
                        }

                        .revenue-reports-page .summary-grid {
                            grid-template-columns: repeat(2, minmax(0, 1fr));
                            margin-bottom: 20px;
                        }

                        .revenue-reports-page .form-label {
                            display: block;
                            margin-bottom: 7px;
                            color: var(--text);
                            font-size: 0.8rem;
                            font-weight: 700;
                            letter-spacing: 0.02em;
                        }

                        .revenue-reports-page .form-control,
                        .revenue-reports-page .custom-select {
                            border-radius: var(--radius-sm);
                            min-height: 44px;
                            border: 1px solid var(--line-strong);
                            box-shadow: none;
                        }

                        .revenue-reports-page .form-control:focus,
                        .revenue-reports-page .custom-select:focus {
                            border-color: #9cc0f5;
                            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
                        }

                        .revenue-reports-page .filter-actions {
                            display: flex;
                            gap: 10px;
                            flex-wrap: wrap;
                            align-items: center;
                        }

                        .revenue-reports-page .range-pill {
                            display: inline-flex;
                            align-items: center;
                            gap: 8px;
                            padding: 9px 14px;
                            border-radius: 999px;
                            background: var(--primary-soft);
                            color: var(--primary-2);
                            border: 1px solid #d8e6ff;
                            font-size: 0.82rem;
                            font-weight: 700;
                        }

                        .revenue-reports-page .table-responsive {
                            border-radius: var(--radius-lg);
                            border: 1px solid var(--line);
                            overflow: hidden;
                            background: var(--surface-strong);
                        }

                        .revenue-reports-page .table {
                            margin: 0;
                        }

                        .revenue-reports-page .table thead th {
                            background: #f8fbff;
                            border-bottom: 1px solid var(--line);
                            color: var(--text-faint);
                            font-size: 0.74rem;
                            font-weight: 800;
                            letter-spacing: 0.08em;
                            text-transform: uppercase;
                            padding: 13px 14px;
                            white-space: nowrap;
                        }

                        .revenue-reports-page .table td {
                            padding: 13px 14px;
                            border-top: 1px solid var(--line);
                            color: var(--text);
                            vertical-align: middle;
                        }

                        .revenue-reports-page .table tbody tr:hover {
                            background: rgba(37, 99, 235, 0.03);
                        }

                        .revenue-reports-page .num-cell {
                            text-align: right;
                            font-variant-numeric: tabular-nums;
                            font-family: var(--font-mono);
                        }

                        .revenue-reports-page .muted-note {
                            color: var(--text-soft);
                            font-size: 0.82rem;
                        }

                        .revenue-reports-page .chip {
                            display: inline-flex;
                            align-items: center;
                            padding: 6px 10px;
                            border-radius: 999px;
                            font-size: 0.74rem;
                            font-weight: 700;
                            border: 1px solid transparent;
                        }

                        .revenue-reports-page .chip.is-primary {
                            background: var(--primary-soft);
                            color: var(--primary-2);
                            border-color: #d8e6ff;
                        }

                        .revenue-reports-page .chip.is-success {
                            background: var(--success-soft);
                            color: var(--success);
                            border-color: #cceedd;
                        }

                        .revenue-reports-page .chip.is-warning {
                            background: var(--warning-soft);
                            color: var(--warning);
                            border-color: #fde6c7;
                        }

                        .revenue-reports-page .variance-positive {
                            color: var(--success);
                        }

                        .revenue-reports-page .variance-negative {
                            color: var(--danger);
                        }

                        .revenue-reports-page .link-quiet {
                            color: var(--primary-2);
                            font-weight: 700;
                            text-decoration: none;
                        }

                        .revenue-reports-page .link-quiet:hover {
                            text-decoration: underline;
                        }

                        .revenue-reports-page .empty-state {
                            padding: 28px 20px;
                            text-align: center;
                            color: var(--text-soft);
                        }

                        @media (max-width: 1399px) {
                            .revenue-reports-page .stat-strip {
                                grid-template-columns: repeat(3, minmax(0, 1fr));
                            }
                        }

                        @media (max-width: 991px) {

                            .revenue-reports-page .filter-grid,
                            .revenue-reports-page .summary-grid {
                                grid-template-columns: 1fr 1fr;
                            }
                        }

                        @media (max-width: 767px) {

                            .revenue-reports-page .stat-strip,
                            .revenue-reports-page .filter-grid,
                            .revenue-reports-page .summary-grid {
                                grid-template-columns: 1fr;
                            }

                            .revenue-reports-page .rr-title {
                                font-size: 1.7rem;
                            }

                            .revenue-reports-page .filter-body,
                            .revenue-reports-page .panel-header,
                            .revenue-reports-page .panel-body {
                                padding-left: 16px;
                                padding-right: 16px;
                            }
                        }
                    </style>

                    <div class="rr-header">
                        <div>
                            <div class="rr-eyebrow">Revenue Intelligence</div>
                            <h1 class="rr-title">Revenue Reports</h1>
                            <p class="rr-subtitle">
                                Track recurring invoices, expected monthly income, and accepted-payment collections in one place.
                                <?php if ($businessName !== ''): ?>
                                    <span class="d-block mt-1"><?= htmlspecialchars($businessName, ENT_QUOTES, 'UTF-8'); ?><?= $businessAddress !== '' ? ' · ' . htmlspecialchars($businessAddress, ENT_QUOTES, 'UTF-8') : ''; ?></span>
                                <?php endif; ?>
                            </p>
                        </div>

                        <div class="rr-actions">
                            <button type="button" class="btn-soft" data-toggle="modal" data-target="#filterModal">
                                <i class="fas fa-filter"></i>
                                Filter
                            </button>
                            <a class="btn-soft" href="<?= htmlspecialchars(base_url() . 'Page/invList', ENT_QUOTES, 'UTF-8'); ?>">
                                <i class="fas fa-file-invoice"></i>
                                Invoice List
                            </a>
                            <a class="btn-soft" href="<?= htmlspecialchars(base_url() . 'Page/paymentList', ENT_QUOTES, 'UTF-8'); ?>">
                                <i class="fas fa-money-check-dollar"></i>
                                Payment List
                            </a>
                            <a class="btn-solid" href="<?= htmlspecialchars(base_url() . 'Page/revenueReports', ENT_QUOTES, 'UTF-8'); ?>">
                                <i class="fas fa-rotate-right"></i>
                                Reset
                            </a>
                        </div>
                    </div>

                    <div class="stat-strip">
                        <div class="stat-card">
                            <div class="stat-label">Recurring Templates</div>
                            <div class="stat-value"><?= number_format($projectedTemplateCount); ?></div>
                            <div class="stat-meta">Active parent invoices with a recurring schedule</div>
                        </div>
                        <div class="stat-card is-warning">
                            <div class="stat-label">Projected Billings</div>
                            <div class="stat-value"><?= number_format($projectedOccurrenceCount); ?></div>
                            <div class="stat-meta"><?= htmlspecialchars($rangeLabel, ENT_QUOTES, 'UTF-8'); ?></div>
                        </div>
                        <div class="stat-card is-success">
                            <div class="stat-label">Projected Income</div>
                            <div class="stat-value"><?= number_format($projectedAmount, 2); ?></div>
                            <div class="stat-meta">Expected recurring value inside the selected range</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-label">Accepted Cash</div>
                            <div class="stat-value"><?= number_format($acceptedCashAmount, 2); ?></div>
                            <div class="stat-meta">Only payments with `ORStat = Valid`</div>
                        </div>
                        <div class="stat-card is-success">
                            <div class="stat-label">Accepted Total Credit</div>
                            <div class="stat-value"><?= number_format($acceptedGrossAmount, 2); ?></div>
                            <div class="stat-meta"><?= number_format($acceptedTaxAmount, 2); ?> of this came from 2307 tax credit</div>
                        </div>
                        <div class="stat-card is-danger">
                            <div class="stat-label">Paying Clients</div>
                            <div class="stat-value"><?= number_format($acceptedClientCount); ?></div>
                            <div class="stat-meta"><?= number_format($acceptedInvoiceCount); ?> invoices across <?= number_format($acceptedEntryCount); ?> valid payments</div>
                        </div>
                    </div>

                    <!-- Filter Modal -->
                    <div class="modal fade" id="filterModal" tabindex="-1" role="dialog" aria-labelledby="filterModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-lg" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="filterModalLabel">
                                        <i class="fas fa-filter mr-2"></i>Filter Revenue Reports
                                    </h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <form method="get" action="<?= base_url(); ?>Page/revenueReports">
                                    <div class="modal-body">
                                        <div class="filter-grid">
                                            <div>
                                                <label class="form-label" for="rr-filter-from">From</label>
                                                <input type="date" class="form-control" id="rr-filter-from" name="date_from" value="<?= htmlspecialchars($filterDateFrom, ENT_QUOTES, 'UTF-8'); ?>">
                                            </div>
                                            <div>
                                                <label class="form-label" for="rr-filter-to">To</label>
                                                <input type="date" class="form-control" id="rr-filter-to" name="date_to" value="<?= htmlspecialchars($filterDateTo, ENT_QUOTES, 'UTF-8'); ?>">
                                            </div>
                                            <div>
                                                <label class="form-label" for="rr-filter-frequency">Recurring Frequency</label>
                                                <select class="custom-select" id="rr-filter-frequency" name="recurring_frequency">
                                                    <option value="all" <?= $recurringFrequencyFilter === 'all' ? 'selected' : ''; ?>>All Frequencies</option>
                                                    <option value="daily" <?= $recurringFrequencyFilter === 'daily' ? 'selected' : ''; ?>>Daily</option>
                                                    <option value="weekly" <?= $recurringFrequencyFilter === 'weekly' ? 'selected' : ''; ?>>Weekly</option>
                                                    <option value="monthly" <?= $recurringFrequencyFilter === 'monthly' ? 'selected' : ''; ?>>Monthly</option>
                                                    <option value="quarterly" <?= $recurringFrequencyFilter === 'quarterly' ? 'selected' : ''; ?>>Quarterly</option>
                                                    <option value="yearly" <?= $recurringFrequencyFilter === 'yearly' ? 'selected' : ''; ?>>Yearly</option>
                                                </select>
                                            </div>
                                            <div>
                                                <label class="form-label" for="rr-filter-client">Client</label>
                                                <select class="custom-select" id="rr-filter-client" name="cust_id">
                                                    <option value="">All Clients</option>
                                                    <?php foreach ($clients as $client): ?>
                                                        <option value="<?= htmlspecialchars((string) ($client->CustID ?? ''), ENT_QUOTES, 'UTF-8'); ?>" <?= $selectedCustID === (string) ($client->CustID ?? '') ? 'selected' : ''; ?>>
                                                            <?= htmlspecialchars((string) ($client->Customer ?? 'Unknown Client'), ENT_QUOTES, 'UTF-8'); ?><?php if (!empty($client->CustID)): ?> · <?= htmlspecialchars((string) $client->CustID, ENT_QUOTES, 'UTF-8'); ?><?php endif; ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div>
                                                <label class="form-label" for="rr-filter-source">Payment Source</label>
                                                <select class="custom-select" id="rr-filter-source" name="payment_source">
                                                    <option value="">All Sources</option>
                                                    <?php foreach ($paymentSources as $source): ?>
                                                        <?php $sourceValue = trim((string) ($source->PaymentSource ?? '')); ?>
                                                        <?php if ($sourceValue === '') continue; ?>
                                                        <option value="<?= htmlspecialchars($sourceValue, ENT_QUOTES, 'UTF-8'); ?>" <?= $selectedPaymentSource === $sourceValue ? 'selected' : ''; ?>>
                                                            <?= htmlspecialchars($sourceValue, ENT_QUOTES, 'UTF-8'); ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div>
                                                <label class="form-label" for="rr-filter-cashier">Cashier</label>
                                                <select class="custom-select" id="rr-filter-cashier" name="cashier">
                                                    <option value="">All Cashiers</option>
                                                    <?php foreach ($paymentCashiers as $cashier): ?>
                                                        <?php $cashierValue = trim((string) ($cashier->Cashier ?? '')); ?>
                                                        <?php if ($cashierValue === '') continue; ?>
                                                        <option value="<?= htmlspecialchars($cashierValue, ENT_QUOTES, 'UTF-8'); ?>" <?= $selectedCashier === $cashierValue ? 'selected' : ''; ?>>
                                                            <?= htmlspecialchars($cashierValue, ENT_QUOTES, 'UTF-8'); ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="mt-3">
                                            <span class="range-pill">
                                                <i class="fas fa-chart-line"></i>
                                                <?= htmlspecialchars($rangeLabel, ENT_QUOTES, 'UTF-8'); ?>
                                            </span>
                                            <span class="range-pill">
                                                <i class="fas fa-shield-check"></i>
                                                Accepted payments only
                                            </span>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <a class="btn-soft" href="<?= htmlspecialchars($currentMonthStartUrl, ENT_QUOTES, 'UTF-8'); ?>">
                                            <i class="fas fa-calendar-days"></i>
                                            This Month
                                        </a>
                                        <a class="btn-soft" href="<?= htmlspecialchars($currentYearStartUrl, ENT_QUOTES, 'UTF-8'); ?>">
                                            <i class="fas fa-calendar"></i>
                                            This Year
                                        </a>
                                        <button type="button" class="btn-soft" data-dismiss="modal">Cancel</button>
                                        <button type="submit" class="btn-solid">
                                            <i class="fas fa-filter"></i>
                                            Apply Filter
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="panel-card mb-4">
                        <div class="panel-header">
                            <div>
                                <h2 class="panel-title">Projected vs Actual by Month</h2>
                                <p class="panel-subtitle">Expected recurring income compared with accepted-payment totals for each month in the selected range.</p>
                            </div>
                            <div class="muted-note">Generated <?= htmlspecialchars($generatedAt, ENT_QUOTES, 'UTF-8'); ?></div>
                        </div>
                        <div class="panel-body">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th>Month</th>
                                            <th class="text-right">Projected Billings</th>
                                            <th class="text-right">Projected Income</th>
                                            <th class="text-right">Accepted Payments</th>
                                            <th class="text-right">Accepted Total Credit</th>
                                            <th class="text-right">Variance</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($monthlyComparisonSummaries)): ?>
                                            <?php foreach ($monthlyComparisonSummaries as $summary): ?>
                                                <?php $variance = (float) ($summary['varianceAmount'] ?? 0); ?>
                                                <tr>
                                                    <td><?= htmlspecialchars((string) ($summary['periodLabel'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                                                    <td class="num-cell"><?= number_format((int) ($summary['projectedOccurrenceCount'] ?? 0)); ?></td>
                                                    <td class="num-cell"><?= number_format((float) ($summary['projectedAmount'] ?? 0), 2); ?></td>
                                                    <td class="num-cell"><?= number_format((int) ($summary['actualEntryCount'] ?? 0)); ?></td>
                                                    <td class="num-cell"><?= number_format((float) ($summary['actualGrossAmount'] ?? 0), 2); ?></td>
                                                    <td class="num-cell <?= $variance >= 0 ? 'variance-positive' : 'variance-negative'; ?>">
                                                        <?= number_format($variance, 2); ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="6" class="empty-state">No monthly data is available for the selected range yet.</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="summary-grid">
                        <div class="panel-card">
                            <div class="panel-header">
                                <div>
                                    <h2 class="panel-title">Recurring Frequency Breakdown</h2>
                                    <p class="panel-subtitle">How many active recurring templates are billing daily, weekly, monthly, quarterly, or yearly.</p>
                                </div>
                            </div>
                            <div class="panel-body">
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead>
                                            <tr>
                                                <th>Frequency</th>
                                                <th class="text-right">Templates</th>
                                                <th class="text-right">Projected Billings</th>
                                                <th class="text-right">Projected Income</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (!empty($recurringFrequencySummaries)): ?>
                                                <?php foreach ($recurringFrequencySummaries as $summary): ?>
                                                    <tr>
                                                        <td><span class="chip is-primary"><?= htmlspecialchars((string) ($summary['label'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></span></td>
                                                        <td class="num-cell"><?= number_format((int) ($summary['templateCount'] ?? 0)); ?></td>
                                                        <td class="num-cell"><?= number_format((int) ($summary['projectedOccurrenceCount'] ?? 0)); ?></td>
                                                        <td class="num-cell"><?= number_format((float) ($summary['projectedAmount'] ?? 0), 2); ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <tr>
                                                    <td colspan="4" class="empty-state">No active recurring invoices matched the current filters.</td>
                                                </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="panel-card">
                            <div class="panel-header">
                                <div>
                                    <h2 class="panel-title">Accepted Sales Summary by Year</h2>
                                    <p class="panel-subtitle">Year-level totals based strictly on payments whose `ORStat` is `Valid`.</p>
                                </div>
                            </div>
                            <div class="panel-body">
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead>
                                            <tr>
                                                <th>Year</th>
                                                <th class="text-right">Payments</th>
                                                <th class="text-right">Invoices</th>
                                                <th class="text-right">Cash</th>
                                                <th class="text-right">Tax</th>
                                                <th class="text-right">Gross</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (!empty($acceptedPaymentYearlySummaries)): ?>
                                                <?php foreach ($acceptedPaymentYearlySummaries as $summary): ?>
                                                    <tr>
                                                        <td><?= htmlspecialchars((string) ($summary['periodLabel'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                                                        <td class="num-cell"><?= number_format((int) ($summary['entryCount'] ?? 0)); ?></td>
                                                        <td class="num-cell"><?= number_format((int) ($summary['invoiceCount'] ?? 0)); ?></td>
                                                        <td class="num-cell"><?= number_format((float) ($summary['cashAmount'] ?? 0), 2); ?></td>
                                                        <td class="num-cell"><?= number_format((float) ($summary['taxAmount'] ?? 0), 2); ?></td>
                                                        <td class="num-cell"><?= number_format((float) ($summary['grossAmount'] ?? 0), 2); ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <tr>
                                                    <td colspan="6" class="empty-state">No accepted-payment totals were found for the selected range.</td>
                                                </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="summary-grid">
                        <div class="panel-card">
                            <div class="panel-header">
                                <div>
                                    <h2 class="panel-title">Accepted Sales Summary by Month</h2>
                                    <p class="panel-subtitle">Monthly actuals from accepted payments, including cash and BIR Form 2307 tax credit.</p>
                                </div>
                            </div>
                            <div class="panel-body">
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead>
                                            <tr>
                                                <th>Month</th>
                                                <th class="text-right">Payments</th>
                                                <th class="text-right">Invoices</th>
                                                <th class="text-right">Cash</th>
                                                <th class="text-right">Tax</th>
                                                <th class="text-right">Gross</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (!empty($acceptedPaymentMonthlySummaries)): ?>
                                                <?php foreach ($acceptedPaymentMonthlySummaries as $summary): ?>
                                                    <tr>
                                                        <td><?= htmlspecialchars((string) ($summary['periodLabel'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                                                        <td class="num-cell"><?= number_format((int) ($summary['entryCount'] ?? 0)); ?></td>
                                                        <td class="num-cell"><?= number_format((int) ($summary['invoiceCount'] ?? 0)); ?></td>
                                                        <td class="num-cell"><?= number_format((float) ($summary['cashAmount'] ?? 0), 2); ?></td>
                                                        <td class="num-cell"><?= number_format((float) ($summary['taxAmount'] ?? 0), 2); ?></td>
                                                        <td class="num-cell"><?= number_format((float) ($summary['grossAmount'] ?? 0), 2); ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <tr>
                                                    <td colspan="6" class="empty-state">No monthly sales summary is available yet.</td>
                                                </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="panel-card">
                            <div class="panel-header">
                                <div>
                                    <h2 class="panel-title">Accepted Collections by Source</h2>
                                    <p class="panel-subtitle">Useful for separating Job Orders, Invoices, and other payment sources.</p>
                                </div>
                            </div>
                            <div class="panel-body">
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead>
                                            <tr>
                                                <th>Source</th>
                                                <th class="text-right">Payments</th>
                                                <th class="text-right">Cash</th>
                                                <th class="text-right">Tax</th>
                                                <th class="text-right">Gross</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (!empty($acceptedPaymentSourceSummaries)): ?>
                                                <?php foreach ($acceptedPaymentSourceSummaries as $summary): ?>
                                                    <tr>
                                                        <td><span class="chip is-success"><?= htmlspecialchars((string) ($summary['label'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></span></td>
                                                        <td class="num-cell"><?= number_format((int) ($summary['entryCount'] ?? 0)); ?></td>
                                                        <td class="num-cell"><?= number_format((float) ($summary['cashAmount'] ?? 0), 2); ?></td>
                                                        <td class="num-cell"><?= number_format((float) ($summary['taxAmount'] ?? 0), 2); ?></td>
                                                        <td class="num-cell"><?= number_format((float) ($summary['grossAmount'] ?? 0), 2); ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <tr>
                                                    <td colspan="5" class="empty-state">No accepted-payment source totals are available yet.</td>
                                                </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="summary-grid">
                        <div class="panel-card">
                            <div class="panel-header">
                                <div>
                                    <h2 class="panel-title">Accepted Collections by Cashier</h2>
                                    <p class="panel-subtitle">Breaks down accepted collections by the cashier who posted the payment.</p>
                                </div>
                            </div>
                            <div class="panel-body">
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead>
                                            <tr>
                                                <th>Cashier</th>
                                                <th class="text-right">Payments</th>
                                                <th class="text-right">Cash</th>
                                                <th class="text-right">Tax</th>
                                                <th class="text-right">Gross</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (!empty($acceptedPaymentCashierSummaries)): ?>
                                                <?php foreach ($acceptedPaymentCashierSummaries as $summary): ?>
                                                    <tr>
                                                        <td><?= htmlspecialchars((string) ($summary['label'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                                                        <td class="num-cell"><?= number_format((int) ($summary['entryCount'] ?? 0)); ?></td>
                                                        <td class="num-cell"><?= number_format((float) ($summary['cashAmount'] ?? 0), 2); ?></td>
                                                        <td class="num-cell"><?= number_format((float) ($summary['taxAmount'] ?? 0), 2); ?></td>
                                                        <td class="num-cell"><?= number_format((float) ($summary['grossAmount'] ?? 0), 2); ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <tr>
                                                    <td colspan="5" class="empty-state">No cashier collection totals are available yet.</td>
                                                </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="panel-card">
                            <div class="panel-header">
                                <div>
                                    <h2 class="panel-title">Projected Recurring Income by Month</h2>
                                    <p class="panel-subtitle">The monthly income forecast generated from active recurring invoice templates.</p>
                                </div>
                            </div>
                            <div class="panel-body">
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead>
                                            <tr>
                                                <th>Month</th>
                                                <th class="text-right">Projected Billings</th>
                                                <th class="text-right">Projected Income</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (!empty($projectedIncomeByMonth)): ?>
                                                <?php foreach ($projectedIncomeByMonth as $summary): ?>
                                                    <tr>
                                                        <td><?= htmlspecialchars((string) ($summary['periodLabel'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                                                        <td class="num-cell"><?= number_format((int) ($summary['occurrenceCount'] ?? 0)); ?></td>
                                                        <td class="num-cell"><?= number_format((float) ($summary['amount'] ?? 0), 2); ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <tr>
                                                    <td colspan="3" class="empty-state">No recurring income projection is available for the selected range.</td>
                                                </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="panel-card mb-4">
                        <div class="panel-header">
                            <div>
                                <h2 class="panel-title">Recurring Invoice Templates</h2>
                                <p class="panel-subtitle">Only active parent recurring invoices are listed here, so the projection does not double-count generated child invoices.</p>
                            </div>
                        </div>
                        <div class="panel-body">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th>Invoice</th>
                                            <th>Client</th>
                                            <th>Description</th>
                                            <th>Frequency</th>
                                            <th>Base Schedule</th>
                                            <th>Next Billing</th>
                                            <th class="text-right">Total Due</th>
                                            <th class="text-right">Projected Billings</th>
                                            <th class="text-right">Projected Income</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($recurringTemplates)): ?>
                                            <?php foreach ($recurringTemplates as $row): ?>
                                                <?php
                                                $invoiceUrl = !empty($row['orderID']) ? base_url() . 'Page/invoice?id=' . rawurlencode((string) $row['orderID']) : '';
                                                $customerHistoryUrl = !empty($row['CustID'])
                                                    ? base_url() . 'Page/customerHistory?cust_id=' . rawurlencode((string) $row['CustID'])
                                                    : '';
                                                ?>
                                                <tr>
                                                    <td>
                                                        <?php if ($invoiceUrl !== ''): ?>
                                                            <a class="link-quiet" href="<?= htmlspecialchars($invoiceUrl, ENT_QUOTES, 'UTF-8'); ?>">
                                                                #<?= htmlspecialchars((string) ($row['InvoiceNo'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>
                                                            </a>
                                                        <?php else: ?>
                                                            #<?= htmlspecialchars((string) ($row['InvoiceNo'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <?php if ($customerHistoryUrl !== ''): ?>
                                                            <a class="link-quiet" href="<?= htmlspecialchars($customerHistoryUrl, ENT_QUOTES, 'UTF-8'); ?>">
                                                                <?= htmlspecialchars((string) ($row['Customer'] ?? 'Unknown Client'), ENT_QUOTES, 'UTF-8'); ?>
                                                            </a>
                                                        <?php else: ?>
                                                            <?= htmlspecialchars((string) ($row['Customer'] ?? 'Unknown Client'), ENT_QUOTES, 'UTF-8'); ?>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td><?= htmlspecialchars((string) ($row['JobDescription'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                                                    <td><span class="chip is-primary"><?= htmlspecialchars((string) ($row['frequencyLabel'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></span></td>
                                                    <td><?= !empty($row['scheduleDate']) ? htmlspecialchars(date('M j, Y', strtotime((string) $row['scheduleDate'])), ENT_QUOTES, 'UTF-8') : '<span class="text-muted">-</span>'; ?></td>
                                                    <td><?= !empty($row['nextBillingDate']) ? htmlspecialchars(date('M j, Y', strtotime((string) $row['nextBillingDate'])), ENT_QUOTES, 'UTF-8') : '<span class="text-muted">-</span>'; ?></td>
                                                    <td class="num-cell"><?= number_format((float) ($row['totalDue'] ?? 0), 2); ?></td>
                                                    <td class="num-cell"><?= number_format((int) ($row['projectedOccurrenceCount'] ?? 0)); ?></td>
                                                    <td class="num-cell"><?= number_format((float) ($row['projectedAmount'] ?? 0), 2); ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="9" class="empty-state">No recurring templates matched the current filters.</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="panel-card">
                        <div class="panel-header">
                            <div>
                                <h2 class="panel-title">Accepted Payment Details</h2>
                                <p class="panel-subtitle">Collection report based on accepted payments only. Deleted or rejected payments are excluded.</p>
                            </div>
                        </div>
                        <div class="panel-body">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Invoice</th>
                                            <th>Client</th>
                                            <th>Source</th>
                                            <th>Cashier</th>
                                            <th class="text-right">Cash</th>
                                            <th class="text-right">Tax</th>
                                            <th class="text-right">Gross</th>
                                            <th>Reference</th>
                                            <th>O.R. No.</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($acceptedPayments)): ?>
                                            <?php foreach ($acceptedPayments as $row): ?>
                                                <?php
                                                $invoiceNo = trim((string) ($row->InvoiceNo ?? ''));
                                                $invoiceUrl = $invoiceNo !== '' ? base_url() . 'Page/invoice?invoice_no=' . rawurlencode($invoiceNo) : '';
                                                $customerHistoryUrl = !empty($row->CustID)
                                                    ? base_url() . 'Page/customerHistory?cust_id=' . rawurlencode((string) $row->CustID)
                                                    : '';
                                                $sourceLabel = trim((string) ($row->PaymentSource ?? ''));
                                                $cashierLabel = trim((string) ($row->Cashier ?? ''));
                                                ?>
                                                <tr>
                                                    <td><?= htmlspecialchars((string) ($row->PDate ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                                                    <td>
                                                        <?php if ($invoiceUrl !== ''): ?>
                                                            <a class="link-quiet" href="<?= htmlspecialchars($invoiceUrl, ENT_QUOTES, 'UTF-8'); ?>">
                                                                #<?= htmlspecialchars($invoiceNo, ENT_QUOTES, 'UTF-8'); ?>
                                                            </a>
                                                        <?php else: ?>
                                                            <span class="text-muted">-</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <?php if ($customerHistoryUrl !== ''): ?>
                                                            <a class="link-quiet" href="<?= htmlspecialchars($customerHistoryUrl, ENT_QUOTES, 'UTF-8'); ?>">
                                                                <?= htmlspecialchars((string) ($row->Customer ?? 'Unknown Client'), ENT_QUOTES, 'UTF-8'); ?>
                                                            </a>
                                                        <?php else: ?>
                                                            <?= htmlspecialchars((string) ($row->Customer ?? 'Unknown Client'), ENT_QUOTES, 'UTF-8'); ?>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td><?= htmlspecialchars($sourceLabel !== '' ? $sourceLabel : 'Unspecified', ENT_QUOTES, 'UTF-8'); ?></td>
                                                    <td><?= htmlspecialchars($cashierLabel !== '' ? $cashierLabel : 'Unassigned', ENT_QUOTES, 'UTF-8'); ?></td>
                                                    <td class="num-cell"><?= number_format((float) ($row->AmountPaid ?? 0), 2); ?></td>
                                                    <td class="num-cell"><?= number_format((float) ($row->TaxAmount ?? 0), 2); ?></td>
                                                    <td class="num-cell"><?= number_format((float) ($row->GrossAmountPaid ?? ((float) ($row->AmountPaid ?? 0) + (float) ($row->TaxAmount ?? 0))), 2); ?></td>
                                                    <td><?= !empty($row->PaymentReference) ? htmlspecialchars((string) $row->PaymentReference, ENT_QUOTES, 'UTF-8') : '<span class="text-muted">-</span>'; ?></td>
                                                    <td><?= !empty($row->ORNo) ? htmlspecialchars((string) $row->ORNo, ENT_QUOTES, 'UTF-8') : '<span class="text-muted">-</span>'; ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="10" class="empty-state">No accepted-payment entries were found for the selected filters.</td>
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