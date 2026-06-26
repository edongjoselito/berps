<?php
$entries = isset($entries) && is_array($entries) ? array_values($entries) : array();
$totals = isset($totals) && is_array($totals) ? $totals : array();
$customerSummaries = isset($customerSummaries) && is_array($customerSummaries) ? array_values($customerSummaries) : array();
$monthSummaries = isset($monthSummaries) && is_array($monthSummaries) ? array_values($monthSummaries) : array();

$filterDateFrom = isset($filterDateFrom) ? trim((string) $filterDateFrom) : '';
$filterDateTo = isset($filterDateTo) ? trim((string) $filterDateTo) : '';
$hasFilter = $filterDateFrom !== '' || $filterDateTo !== '';
$businessData = isset($business) ? $business : null;
$printMode = !empty($autoPrint);
$generatedAt = isset($generatedAt) && trim((string) $generatedAt) !== ''
    ? (string) $generatedAt
    : date('F j, Y h:i A');
$businessName = trim((string) ($businessData->CompName ?? 'BERPS'));
$businessAddress = trim((string) ($businessData->CompAddress ?? ''));
$businessTin = trim((string) ($businessData->CompTin ?? ''));

$rangeLabel = 'All recorded tax entries';
if ($filterDateFrom !== '' && $filterDateTo !== '') {
    $formattedFrom = date('F j, Y', strtotime($filterDateFrom));
    $formattedTo = date('F j, Y', strtotime($filterDateTo));
    $rangeLabel = $filterDateFrom === $filterDateTo
        ? $formattedFrom
        : $formattedFrom . ' to ' . $formattedTo;
} elseif ($filterDateFrom !== '') {
    $rangeLabel = date('F j, Y', strtotime($filterDateFrom));
}

$totalCash = (float) ($totals['cashAmount'] ?? 0);
$totalTax = (float) ($totals['taxAmount'] ?? 0);
$totalGross = (float) ($totals['grossAmount'] ?? 0);
$entryCount = (int) ($totals['entryCount'] ?? 0);
$uniqueClients = (int) ($totals['uniqueClients'] ?? 0);

$printQuery = array();
if ($filterDateFrom !== '') {
    $printQuery['date_from'] = $filterDateFrom;
}
if ($filterDateTo !== '') {
    $printQuery['date_to'] = $filterDateTo;
}
$printQuery['print'] = 1;
$printUrl = base_url() . 'Page/taxSummaryReport?' . http_build_query($printQuery);
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
                <div class="container-fluid tax-summary-page">

                    <style>

                        .tax-summary-page {
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
                            --font-body: var(--font-primary);
                            --font-head: var(--font-primary);
                            --font-mono: var(--font-primary);
                            font-family: var(--font-primary, Montserrat, Segoe UI, Arial, sans-serif);
                            background:
                                radial-gradient(circle at top left, rgba(37, 99, 235, 0.08), transparent 28%),
                                radial-gradient(circle at top right, rgba(16, 185, 129, 0.08), transparent 24%),
                                linear-gradient(180deg, #f8fbff 0%, #f4f7fb 100%);
                            min-height: 100vh;
                            padding-bottom: 100px;
                        }

                        .tax-summary-page * {
                            box-sizing: border-box;
                        }

                        .tax-summary-page .ts-header {
                            margin: 24px 0 22px;
                            display: flex;
                            justify-content: space-between;
                            align-items: flex-end;
                            gap: 16px;
                            flex-wrap: wrap;
                        }

                        .tax-summary-page .ts-eyebrow {
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

                        .tax-summary-page .ts-eyebrow::before {
                            content: '';
                            width: 8px;
                            height: 8px;
                            border-radius: 50%;
                            background: linear-gradient(135deg, var(--primary), var(--primary-2));
                            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.10);
                        }

                        .tax-summary-page .ts-title {
                            margin: 0;
                            font-family: var(--font-primary, Montserrat, Segoe UI, Arial, sans-serif);
                            font-size: 2rem;
                            font-weight: 800;
                            letter-spacing: -0.04em;
                            color: var(--text);
                            line-height: 1.1;
                        }

                        .tax-summary-page .ts-subtitle {
                            margin-top: 8px;
                            color: var(--text-soft);
                            font-size: 0.93rem;
                            font-weight: 500;
                        }

                        .tax-summary-page .ts-actions {
                            display: flex;
                            gap: 10px;
                            flex-wrap: wrap;
                            justify-content: flex-end;
                        }

                        .tax-summary-page .btn-soft,
                        .tax-summary-page .btn-solid {
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

                        .tax-summary-page .btn-soft {
                            background: rgba(255, 255, 255, 0.88);
                            color: var(--text);
                            border: 1px solid var(--line-strong);
                            box-shadow: var(--shadow-soft);
                        }

                        .tax-summary-page .btn-solid {
                            background: linear-gradient(135deg, var(--primary), var(--primary-2));
                            color: #fff;
                            border: none;
                            box-shadow: 0 10px 24px rgba(37, 99, 235, 0.22);
                        }

                        .tax-summary-page .btn-soft:hover,
                        .tax-summary-page .btn-solid:hover {
                            transform: translateY(-1px);
                            filter: brightness(1.02);
                            text-decoration: none;
                        }

                        .tax-summary-page .btn-solid:hover {
                            color: #fff;
                        }

                        .tax-summary-page .stat-strip {
                            display: grid;
                            grid-template-columns: repeat(4, minmax(0, 1fr));
                            gap: 16px;
                            margin-bottom: 20px;
                        }

                        .tax-summary-page .stat-card {
                            position: relative;
                            overflow: hidden;
                            background: var(--surface);
                            backdrop-filter: blur(12px);
                            border: 1px solid rgba(255, 255, 255, 0.7);
                            border-radius: var(--radius-xl);
                            box-shadow: var(--shadow-soft);
                            padding: 18px 20px 20px;
                            min-height: 118px;
                        }

                        .tax-summary-page .stat-card::before {
                            content: '';
                            position: absolute;
                            inset: 0 0 auto 0;
                            height: 4px;
                        }

                        .tax-summary-page .stat-card.sc-tax::before {
                            background: linear-gradient(90deg, #3b82f6, #60a5fa);
                        }

                        .tax-summary-page .stat-card.sc-gross::before {
                            background: linear-gradient(90deg, #10b981, #34d399);
                        }

                        .tax-summary-page .stat-card.sc-count::before {
                            background: linear-gradient(90deg, #f59e0b, #fbbf24);
                        }

                        .tax-summary-page .stat-card.sc-clients::before {
                            background: linear-gradient(90deg, #f43f5e, #fb7185);
                        }

                        .tax-summary-page .stat-label {
                            color: var(--text-faint);
                            font-size: 0.74rem;
                            font-weight: 700;
                            text-transform: uppercase;
                            letter-spacing: 0.08em;
                            margin-bottom: 12px;
                        }

                        .tax-summary-page .stat-value {
                            color: var(--text);
                            font-size: 2rem;
                            font-weight: 800;
                            line-height: 1;
                            letter-spacing: -0.04em;
                            margin-bottom: 6px;
                        }

                        .tax-summary-page .stat-meta {
                            color: var(--text-soft);
                            font-size: 0.82rem;
                            font-weight: 500;
                        }

                        .tax-summary-page .content {
                            margin-bottom: 40px;
                        }

                        .tax-summary-page .filter-card,
                        .tax-summary-page .panel-card {
                            background: var(--surface);
                            border: 1px solid rgba(255, 255, 255, 0.72);
                            border-radius: var(--radius-xl);
                            box-shadow: var(--shadow-soft);
                            overflow: hidden;
                        }

                        .tax-summary-page .filter-card {
                            margin-bottom: 20px;
                        }

                        .tax-summary-page .filter-body,
                        .tax-summary-page .panel-body {
                            padding: 22px 24px 24px;
                        }

                        .tax-summary-page .panel-header {
                            padding: 20px 24px;
                            border-bottom: 1px solid var(--line);
                            display: flex;
                            align-items: center;
                            justify-content: space-between;
                            gap: 14px;
                            flex-wrap: wrap;
                            background: linear-gradient(180deg, rgba(255, 255, 255, 0.94), rgba(249, 251, 255, 0.94));
                        }

                        .tax-summary-page .panel-title {
                            margin: 0;
                            color: var(--text);
                            font-size: 1.05rem;
                            font-weight: 800;
                            letter-spacing: -0.02em;
                        }

                        .tax-summary-page .panel-subtitle {
                            margin-top: 5px;
                            color: var(--text-soft);
                            font-size: 0.88rem;
                        }

                        .tax-summary-page .filter-grid,
                        .tax-summary-page .summary-grid {
                            display: grid;
                            grid-template-columns: repeat(2, minmax(0, 1fr));
                            gap: 20px;
                        }

                        .tax-summary-page .form-label {
                            color: var(--text);
                            font-size: 0.85rem;
                            font-weight: 700;
                            margin-bottom: 8px;
                        }

                        .tax-summary-page .form-control {
                            border-radius: 12px;
                            min-height: 46px;
                            border: 1px solid var(--line-strong);
                            box-shadow: none;
                        }

                        .tax-summary-page .form-control:focus {
                            border-color: rgba(37, 99, 235, 0.45);
                            box-shadow: 0 0 0 0.18rem rgba(37, 99, 235, 0.12);
                        }

                        .tax-summary-page .filter-actions {
                            display: flex;
                            gap: 10px;
                            flex-wrap: wrap;
                            align-items: center;
                        }

                        .tax-summary-page .range-pill {
                            display: inline-flex;
                            align-items: center;
                            gap: 8px;
                            padding: 8px 12px;
                            border-radius: 999px;
                            background: var(--primary-soft);
                            color: var(--primary-2);
                            font-size: 0.8rem;
                            font-weight: 700;
                        }

                        .tax-summary-page .table-responsive {
                            border-radius: 18px;
                            overflow: hidden;
                            border: 1px solid var(--line);
                            background: #fff;
                        }

                        .tax-summary-page .table {
                            margin-bottom: 0;
                        }

                        .tax-summary-page .table thead th {
                            border-top: 0;
                            border-bottom: 1px solid var(--line);
                            color: var(--text-faint);
                            font-size: 0.74rem;
                            font-weight: 800;
                            text-transform: uppercase;
                            letter-spacing: 0.08em;
                            background: #f9fbfe;
                            padding-top: 14px;
                            padding-bottom: 14px;
                            white-space: nowrap;
                        }

                        .tax-summary-page .table td {
                            vertical-align: middle;
                            border-top: 1px solid #eef3f8;
                            color: var(--text);
                        }

                        .tax-summary-page .table tbody tr:hover {
                            background: rgba(37, 99, 235, 0.03);
                        }

                        .tax-summary-page .num-cell {
                            font-family: var(--font-primary, Montserrat, Segoe UI, Arial, sans-serif);
                            font-variant-numeric: tabular-nums;
                        }

                        .tax-summary-page .invoice-link,
                        .tax-summary-page .customer-link {
                            color: var(--primary-2);
                            font-weight: 700;
                            text-decoration: none;
                        }

                        .tax-summary-page .invoice-link:hover,
                        .tax-summary-page .customer-link:hover {
                            text-decoration: underline;
                        }

                        .tax-summary-page .source-pill {
                            display: inline-flex;
                            align-items: center;
                            padding: 5px 10px;
                            border-radius: 999px;
                            background: #f8fbff;
                            border: 1px solid var(--line);
                            color: var(--text-soft);
                            font-size: 0.76rem;
                            font-weight: 700;
                        }

                        .tax-summary-page .ts-print-header {
                            display: none;
                            margin-bottom: 18px;
                            padding-bottom: 16px;
                            border-bottom: 2px solid #dbe5f1;
                        }

                        .tax-summary-page .ts-print-title {
                            color: var(--text);
                            font-family: var(--font-primary, Montserrat, Segoe UI, Arial, sans-serif);
                            font-size: 1.55rem;
                            font-weight: 800;
                            letter-spacing: -0.03em;
                            margin-bottom: 6px;
                        }

                        .tax-summary-page .ts-print-meta {
                            color: var(--text-soft);
                            font-size: 0.88rem;
                            line-height: 1.6;
                        }

                        .tax-summary-page .empty-state {
                            padding: 24px;
                            text-align: center;
                            color: var(--text-soft);
                            font-size: 0.92rem;
                        }

                        .tax-summary-page .empty-state strong {
                            display: block;
                            color: var(--text);
                            font-size: 0.98rem;
                            margin-bottom: 6px;
                        }

                        @media (max-width: 1199.98px) {
                            .tax-summary-page .summary-grid {
                                grid-template-columns: 1fr;
                            }
                        }

                        @media (max-width: 767.98px) {
                            .tax-summary-page .ts-title {
                                font-size: 1.7rem;
                            }

                            .tax-summary-page .stat-strip,
                            .tax-summary-page .filter-grid {
                                grid-template-columns: 1fr;
                            }

                            .tax-summary-page .filter-body,
                            .tax-summary-page .panel-header,
                            .tax-summary-page .panel-body {
                                padding-left: 18px;
                                padding-right: 18px;
                            }

                            .tax-summary-page .ts-actions {
                                width: 100%;
                                justify-content: stretch;
                            }

                            .tax-summary-page .ts-actions a,
                            .tax-summary-page .filter-actions a,
                            .tax-summary-page .filter-actions button {
                                flex: 1 1 auto;
                                justify-content: center;
                            }
                        }

                        @media print {

                            html,
                            body {
                                background: #ffffff !important;
                                color: #000000 !important;
                            }

                            body {
                                margin: 0;
                                padding: 0;
                            }

                            #wrapper,
                            .content-page,
                            .content,
                            .container-fluid {
                                margin: 0 !important;
                                padding: 0 !important;
                            }

                            .left-side-menu,
                            .navbar-custom,
                            .footer,
                            .ts-header,
                            .filter-card,
                            .ts-actions,
                            .dataTables_length,
                            .dataTables_filter,
                            .dataTables_info,
                            .dataTables_paginate,
                            .dt-buttons,
                            .theme-settings,
                            .right-bar,
                            .button-menu-mobile {
                                display: none !important;
                            }

                            .tax-summary-page {
                                background: #ffffff !important;
                                padding: 0 !important;
                                min-height: 0 !important;
                                color: #000000 !important;
                            }

                            .tax-summary-page .ts-print-header {
                                display: block;
                            }

                            .tax-summary-page .summary-grid,
                            .tax-summary-page .stat-strip {
                                gap: 10px;
                            }

                            .tax-summary-page .stat-card,
                            .tax-summary-page .panel-card {
                                background: #ffffff !important;
                                border: 1px solid #d1d5db !important;
                                box-shadow: none !important;
                                break-inside: avoid;
                                page-break-inside: avoid;
                            }

                            .tax-summary-page .panel-header {
                                background: #ffffff !important;
                                border-bottom: 1px solid #d1d5db !important;
                            }

                            .tax-summary-page .table-responsive {
                                border: 1px solid #d1d5db !important;
                                background: #ffffff !important;
                            }

                            .tax-summary-page .table thead th,
                            .tax-summary-page .table td,
                            .tax-summary-page .panel-title,
                            .tax-summary-page .panel-subtitle,
                            .tax-summary-page .stat-label,
                            .tax-summary-page .stat-value,
                            .tax-summary-page .stat-meta,
                            .tax-summary-page .ts-print-title,
                            .tax-summary-page .ts-print-meta,
                            .tax-summary-page .invoice-link,
                            .tax-summary-page .customer-link,
                            .tax-summary-page .source-pill,
                            .tax-summary-page .empty-state,
                            .tax-summary-page .empty-state strong {
                                color: #000000 !important;
                            }

                            .tax-summary-page .table thead th {
                                background: #f3f4f6 !important;
                            }

                            .tax-summary-page .source-pill {
                                background: #ffffff !important;
                                border: 1px solid #d1d5db !important;
                            }

                            a[href]:after {
                                content: none !important;
                            }

                            #tax-summary-table {
                                page-break-inside: auto;
                            }

                            #tax-summary-table tr {
                                page-break-inside: avoid;
                                page-break-after: auto;
                            }
                        }
                    </style>

                    <div class="ts-print-header">
                        <div class="ts-print-title"><?= htmlspecialchars($businessName !== '' ? $businessName : 'BERPS', ENT_QUOTES, 'UTF-8'); ?></div>
                        <div class="ts-print-meta">
                            Tax Summary Report<br>
                            Coverage: <?= htmlspecialchars($rangeLabel, ENT_QUOTES, 'UTF-8'); ?><br>
                            Generated: <?= htmlspecialchars($generatedAt, ENT_QUOTES, 'UTF-8'); ?>
                            <?php if ($businessTin !== ''): ?>
                                <br>TIN: <?= htmlspecialchars($businessTin, ENT_QUOTES, 'UTF-8'); ?>
                            <?php endif; ?>
                            <?php if ($businessAddress !== ''): ?>
                                <br>Address: <?= htmlspecialchars($businessAddress, ENT_QUOTES, 'UTF-8'); ?>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="ts-header">
                        <div>
                            <div class="ts-eyebrow">Tax Reporting</div>
                            <h1 class="ts-title">Tax Summary Report</h1>
                            <p class="ts-subtitle">
                                Review all payments with BIR Form 2307 tax, grouped by client and by month.
                            </p>
                        </div>

                        <div class="ts-actions">
                            <button type="button" class="btn-soft" data-toggle="modal" data-target="#filterModal">
                                <i class="fas fa-filter"></i>
                                Filter
                            </button>
                            <a class="btn-soft" href="<?= htmlspecialchars(base_url() . 'Page/paymentList', ENT_QUOTES, 'UTF-8'); ?>">
                                <i class="fas fa-arrow-left"></i>
                                Back to Payments
                            </a>
                            <a class="btn-soft" href="<?= htmlspecialchars($printUrl, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener noreferrer">
                                <i class="fas fa-print"></i>
                                Print Report
                            </a>
                            <a class="btn-solid" href="<?= htmlspecialchars(base_url() . 'Page/taxSummaryReport', ENT_QUOTES, 'UTF-8'); ?>">
                                <i class="fas fa-rotate-right"></i>
                                Reset
                            </a>
                        </div>
                    </div>

                    <div class="stat-strip">
                        <div class="stat-card sc-tax">
                            <div class="stat-label">Total Tax Recorded</div>
                            <div class="stat-value"><?= number_format($totalTax, 2); ?></div>
                            <div class="stat-meta"><?= htmlspecialchars($rangeLabel, ENT_QUOTES, 'UTF-8'); ?></div>
                        </div>
                        <div class="stat-card sc-gross">
                            <div class="stat-label">Total Credited</div>
                            <div class="stat-value"><?= number_format($totalGross, 2); ?></div>
                            <div class="stat-meta">Cash plus BIR 2307 tax across all shown entries</div>
                        </div>
                        <div class="stat-card sc-count">
                            <div class="stat-label">Taxed Payments</div>
                            <div class="stat-value"><?= number_format($entryCount); ?></div>
                            <div class="stat-meta"><?= number_format($totalCash, 2); ?> cash portion before tax</div>
                        </div>
                        <div class="stat-card sc-clients">
                            <div class="stat-label">Clients Affected</div>
                            <div class="stat-value"><?= number_format($uniqueClients); ?></div>
                            <div class="stat-meta">Companies with recorded BIR 2307 tax entries</div>
                        </div>
                    </div>

                    <!-- Filter Modal -->
                    <div class="modal fade" id="filterModal" tabindex="-1" role="dialog" aria-labelledby="filterModalLabel" aria-hidden="true">
                        <div class="modal-dialog" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="filterModalLabel">
                                        <i class="fas fa-filter mr-2"></i>Filter Tax Report
                                    </h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <form method="get" action="<?= base_url(); ?>Page/taxSummaryReport">
                                    <div class="modal-body">
                                        <div class="filter-grid">
                                            <div>
                                                <label class="form-label" for="tax-filter-from">From</label>
                                                <input type="date" class="form-control" id="tax-filter-from" name="date_from" value="<?= htmlspecialchars($filterDateFrom, ENT_QUOTES, 'UTF-8'); ?>">
                                            </div>
                                            <div>
                                                <label class="form-label" for="tax-filter-to">To</label>
                                                <input type="date" class="form-control" id="tax-filter-to" name="date_to" value="<?= htmlspecialchars($filterDateTo, ENT_QUOTES, 'UTF-8'); ?>">
                                            </div>
                                        </div>
                                        <div class="mt-3">
                                            <?php if ($hasFilter): ?>
                                                <span class="range-pill">
                                                    <i class="fas fa-clock"></i>
                                                    <?= htmlspecialchars($rangeLabel, ENT_QUOTES, 'UTF-8'); ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="range-pill">
                                                    <i class="fas fa-globe"></i>
                                                    All Time
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <a class="btn-soft" href="<?= htmlspecialchars(base_url() . 'Page/taxSummaryReport?date_from=' . date('Y-m-01') . '&date_to=' . date('Y-m-t'), ENT_QUOTES, 'UTF-8'); ?>">
                                            <i class="fas fa-calendar-alt"></i>
                                            This Month
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

                    <div class="summary-grid">
                        <div class="panel-card">
                            <div class="panel-header">
                                <div>
                                    <h2 class="panel-title">Client Breakdown</h2>
                                    <p class="panel-subtitle">Who accounted for the highest total tax amount in the selected range.</p>
                                </div>
                            </div>
                            <div class="panel-body">
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead>
                                            <tr>
                                                <th>Client</th>
                                                <th class="text-right">Entries</th>
                                                <th class="text-right">Cash</th>
                                                <th class="text-right">Tax</th>
                                                <th class="text-right">Total Credit</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (!empty($customerSummaries)): ?>
                                                <?php foreach ($customerSummaries as $summary): ?>
                                                    <?php
                                                    $customerHistoryUrl = !empty($summary['custID'])
                                                        ? base_url() . 'Page/customerHistory?cust_id=' . rawurlencode((string) $summary['custID'])
                                                        : '';
                                                    ?>
                                                    <tr>
                                                        <td>
                                                            <?php if ($customerHistoryUrl !== ''): ?>
                                                                <a class="customer-link" href="<?= htmlspecialchars($customerHistoryUrl, ENT_QUOTES, 'UTF-8'); ?>">
                                                                    <?= htmlspecialchars((string) $summary['label'], ENT_QUOTES, 'UTF-8'); ?>
                                                                </a>
                                                            <?php else: ?>
                                                                <?= htmlspecialchars((string) $summary['label'], ENT_QUOTES, 'UTF-8'); ?>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td class="text-right num-cell"><?= number_format((int) $summary['entryCount']); ?></td>
                                                        <td class="text-right num-cell"><?= number_format((float) $summary['cashAmount'], 2); ?></td>
                                                        <td class="text-right num-cell"><?= number_format((float) $summary['taxAmount'], 2); ?></td>
                                                        <td class="text-right num-cell"><?= number_format((float) $summary['grossAmount'], 2); ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <tr>
                                                    <td colspan="5" class="empty-state">
                                                        <strong>No tax data found.</strong>
                                                        Tax-bearing payments will appear here once BIR 2307 amounts are recorded.
                                                    </td>
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
                                    <h2 class="panel-title">Monthly Breakdown</h2>
                                    <p class="panel-subtitle">Track how much tax was recorded each month within the selected range.</p>
                                </div>
                            </div>
                            <div class="panel-body">
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead>
                                            <tr>
                                                <th>Period</th>
                                                <th class="text-right">Entries</th>
                                                <th class="text-right">Cash</th>
                                                <th class="text-right">Tax</th>
                                                <th class="text-right">Total Credit</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (!empty($monthSummaries)): ?>
                                                <?php foreach ($monthSummaries as $summary): ?>
                                                    <tr>
                                                        <td><?= htmlspecialchars((string) $summary['periodLabel'], ENT_QUOTES, 'UTF-8'); ?></td>
                                                        <td class="text-right num-cell"><?= number_format((int) $summary['entryCount']); ?></td>
                                                        <td class="text-right num-cell"><?= number_format((float) $summary['cashAmount'], 2); ?></td>
                                                        <td class="text-right num-cell"><?= number_format((float) $summary['taxAmount'], 2); ?></td>
                                                        <td class="text-right num-cell"><?= number_format((float) $summary['grossAmount'], 2); ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <tr>
                                                    <td colspan="5" class="empty-state">
                                                        <strong>No monthly breakdown yet.</strong>
                                                        Add a tax-bearing payment first and the grouped monthly summary will appear here.
                                                    </td>
                                                </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="panel-card mt-4">
                        <div class="panel-header">
                            <div>
                                <h2 class="panel-title">Tax Payment Entries</h2>
                                <p class="panel-subtitle">Detailed list of every valid payment where a BIR Form 2307 tax amount was recorded.</p>
                            </div>
                        </div>
                        <div class="panel-body">
                            <div class="table-responsive">
                                <table id="tax-summary-table" class="table table-hover table-centered mb-0">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Invoice No.</th>
                                            <th>Client</th>
                                            <th>Source</th>
                                            <th class="text-right">Amount Paid</th>
                                            <th class="text-right">Tax 2307</th>
                                            <th class="text-right">Total Credit</th>
                                            <th>Reference</th>
                                            <th>O.R. No.</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($entries)): ?>
                                            <?php foreach ($entries as $row): ?>
                                                <?php
                                                $invoiceNo = trim((string) ($row->InvoiceNo ?? ''));
                                                $invoiceUrl = $invoiceNo !== '' ? base_url() . 'Page/invoice?invoice_no=' . rawurlencode($invoiceNo) : '';
                                                $customerName = trim((string) ($row->Customer ?? ''));
                                                $customerUrl = !empty($row->CustID)
                                                    ? base_url() . 'Page/customerHistory?cust_id=' . rawurlencode((string) $row->CustID)
                                                    : ($customerName !== '' ? base_url() . 'Page/customerHistory?customer=' . rawurlencode($customerName) : '');
                                                $sourceLabel = trim((string) ($row->PaymentSource ?? ''));
                                                if ($sourceLabel === 'Others') {
                                                    $sourceLabel = 'Invoice';
                                                }
                                                if ($sourceLabel === '') {
                                                    $sourceLabel = 'Payment';
                                                }
                                                $paymentReference = trim((string) ($row->PaymentReference ?? ''));
                                                $orNo = trim((string) ($row->ORNo ?? ''));
                                                $cashPaid = (float) ($row->AmountPaid ?? 0);
                                                $taxAmount = (float) ($row->TaxAmount ?? 0);
                                                $grossAmount = (float) ($row->GrossAmountPaid ?? ($cashPaid + $taxAmount));
                                                ?>
                                                <tr>
                                                    <td class="num-cell" data-order="<?= htmlspecialchars((string) ($row->PDate ?? ''), ENT_QUOTES, 'UTF-8'); ?>"><?= htmlspecialchars((string) ($row->PDate ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                                                    <td>
                                                        <?php if ($invoiceUrl !== ''): ?>
                                                            <a class="invoice-link" href="<?= htmlspecialchars($invoiceUrl, ENT_QUOTES, 'UTF-8'); ?>">
                                                                #<?= htmlspecialchars($invoiceNo, ENT_QUOTES, 'UTF-8'); ?>
                                                            </a>
                                                        <?php else: ?>
                                                            -
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <?php if ($customerUrl !== ''): ?>
                                                            <a class="customer-link" href="<?= htmlspecialchars($customerUrl, ENT_QUOTES, 'UTF-8'); ?>">
                                                                <?= htmlspecialchars($customerName !== '' ? $customerName : 'Unknown Customer', ENT_QUOTES, 'UTF-8'); ?>
                                                            </a>
                                                        <?php else: ?>
                                                            <?= htmlspecialchars($customerName !== '' ? $customerName : 'Unknown Customer', ENT_QUOTES, 'UTF-8'); ?>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td><span class="source-pill"><?= htmlspecialchars($sourceLabel, ENT_QUOTES, 'UTF-8'); ?></span></td>
                                                    <td class="text-right num-cell"><?= number_format($cashPaid, 2); ?></td>
                                                    <td class="text-right num-cell"><?= number_format($taxAmount, 2); ?></td>
                                                    <td class="text-right num-cell"><?= number_format($grossAmount, 2); ?></td>
                                                    <td><?= htmlspecialchars($paymentReference !== '' ? $paymentReference : '-', ENT_QUOTES, 'UTF-8'); ?></td>
                                                    <td><?= htmlspecialchars($orNo !== '' ? $orNo : '-', ENT_QUOTES, 'UTF-8'); ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="9" class="empty-state">
                                                    <strong>No tax payment entries found.</strong>
                                                    Try a different date range or add tax values to government payments with BIR Form 2307.
                                                </td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <?php include('includes/footer.php'); ?>
            </div>
        </div>

    </div>

    <?php include('includes/themecustomizer.php'); ?>

    <script src="<?= base_url(); ?>assets/js/vendor.min.js"></script>
    <script src="<?= base_url(); ?>assets/js/app.min.js"></script>
    <script src="<?= base_url(); ?>assets/libs/datatables/jquery.dataTables.min.js"></script>
    <script src="<?= base_url(); ?>assets/libs/datatables/dataTables.bootstrap4.min.js"></script>
    <script src="<?= base_url(); ?>assets/libs/datatables/dataTables.responsive.min.js"></script>
    <script src="<?= base_url(); ?>assets/libs/datatables/responsive.bootstrap4.min.js"></script>

    <script>
        (function($) {
            'use strict';

            $(function() {
                var $table = $('#tax-summary-table');
                if (!$table.length) {
                    return;
                }

                var printMode = <?= $printMode ? 'true' : 'false'; ?>;

                if (!printMode) {
                    $table.DataTable({
                        responsive: true,
                        autoWidth: false,
                        stateSave: true,
                        pageLength: 25,
                        lengthMenu: [
                            [10, 25, 50, 100, -1],
                            [10, 25, 50, 100, 'All']
                        ],
                        order: [
                            [0, 'desc']
                        ],
                        language: {
                            emptyTable: 'No tax-bearing payments recorded.'
                        },
                        columnDefs: [{
                            targets: [4, 5, 6],
                            className: 'text-right'
                        }]
                    });
                } else {
                    window.setTimeout(function() {
                        window.print();
                    }, 300);
                }
            });
        })(jQuery);
    </script>

</body>

</html>