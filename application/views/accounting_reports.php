<?php
$businessData = isset($business) ? $business : null;
$headlineCards = isset($headlineCards) && is_array($headlineCards) ? $headlineCards : array();
$incomeStatement = isset($incomeStatement) && is_array($incomeStatement) ? $incomeStatement : array();
$balanceSheet = isset($balanceSheet) && is_array($balanceSheet) ? $balanceSheet : array();
$cashFlow = isset($cashFlow) && is_array($cashFlow) ? $cashFlow : array();
$receivables = isset($receivables) && is_array($receivables) ? $receivables : array();
$expenseSummary = isset($expenseSummary) && is_array($expenseSummary) ? $expenseSummary : array();

$filterDateFrom = isset($filterDateFrom) ? trim((string) $filterDateFrom) : '';
$filterDateTo = isset($filterDateTo) ? trim((string) $filterDateTo) : '';
$asOfDate = isset($asOfDate) ? trim((string) $asOfDate) : $filterDateTo;
$rangeLabel = isset($rangeLabel) ? (string) $rangeLabel : 'Current range';
$generatedAt = isset($generatedAt) && trim((string) $generatedAt) !== '' ? (string) $generatedAt : date('F j, Y h:i A');
$printMode = !empty($autoPrint);

$businessName = trim((string) ($businessData->CompName ?? 'BERPS'));
$businessAddress = trim((string) ($businessData->CompAddress ?? ''));
$businessTin = trim((string) ($businessData->CompTin ?? ''));

$revenueSources = isset($incomeStatement['revenueSources']) && is_array($incomeStatement['revenueSources']) ? $incomeStatement['revenueSources'] : array();
$balanceAssets = isset($balanceSheet['assets']) && is_array($balanceSheet['assets']) ? $balanceSheet['assets'] : array();
$balanceLiabilitiesAndEquity = isset($balanceSheet['liabilitiesAndEquity']) && is_array($balanceSheet['liabilitiesAndEquity']) ? $balanceSheet['liabilitiesAndEquity'] : array();
$cashMonthly = isset($cashFlow['monthly']) && is_array($cashFlow['monthly']) ? $cashFlow['monthly'] : array();
$receivableBuckets = isset($receivables['agingBuckets']) && is_array($receivables['agingBuckets']) ? $receivables['agingBuckets'] : array();
$receivableRows = isset($receivables['rows']) && is_array($receivables['rows']) ? $receivables['rows'] : array();
$expenseCategories = isset($expenseSummary['categories']) && is_array($expenseSummary['categories']) ? $expenseSummary['categories'] : array();
$expenseRows = isset($expenseSummary['rows']) && is_array($expenseSummary['rows']) ? $expenseSummary['rows'] : array();
$recentExpenseRows = array_slice($expenseRows, 0, 25);

$money = function ($value) {
    return number_format((float) $value, 2);
};

$formatDate = function ($value, $fallback = '-') {
    $value = trim((string) $value);
    if ($value === '' || $value === '0000-00-00') {
        return $fallback;
    }

    return date('M j, Y', strtotime($value));
};

$printQuery = array();
if ($filterDateFrom !== '') {
    $printQuery['date_from'] = $filterDateFrom;
}
if ($filterDateTo !== '') {
    $printQuery['date_to'] = $filterDateTo;
}
$printQuery['print'] = 1;
$printUrl = base_url() . 'Page/accountingReports?' . http_build_query($printQuery);
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
                <div class="container-fluid accounting-reports-page">

                    <style>
                        @import url('https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,100..1000;1,9..40,100..1000&display=swap');

                        .accounting-reports-page {
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
                            --info: #0891b2;
                            --info-soft: #ecfeff;
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
                            padding-bottom: 80px;
                        }

                        .accounting-reports-page * {
                            box-sizing: border-box;
                        }

                        .accounting-reports-page .ar-header {
                            margin: 32px 0 28px;
                            display: flex;
                            justify-content: space-between;
                            align-items: flex-end;
                            gap: 24px;
                            flex-wrap: wrap;
                        }

                        .accounting-reports-page .ar-eyebrow {
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

                        .accounting-reports-page .ar-eyebrow::before {
                            content: '';
                            width: 8px;
                            height: 8px;
                            border-radius: 50%;
                            background: linear-gradient(135deg, var(--primary), var(--primary-2));
                            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.10);
                        }

                        .accounting-reports-page .ar-title {
                            margin: 0;
                            font-family: var(--font-head);
                            font-size: 2.15rem;
                            font-weight: 800;
                            color: var(--text);
                            line-height: 1.05;
                            letter-spacing: -0.05em;
                        }

                        .accounting-reports-page .ar-subtitle {
                            margin-top: 8px;
                            color: var(--text-soft);
                            font-size: 0.96rem;
                            font-weight: 400;
                            max-width: 820px;
                        }

                        .accounting-reports-page .ar-actions {
                            display: flex;
                            gap: 12px;
                            flex-wrap: wrap;
                            justify-content: flex-end;
                        }

                        .accounting-reports-page .btn-soft,
                        .accounting-reports-page .btn-solid {
                            display: inline-flex;
                            align-items: center;
                            justify-content: center;
                            gap: 8px;
                            padding: 11px 18px;
                            border-radius: 12px;
                            font-size: 0.92rem;
                            font-weight: 700;
                            text-decoration: none;
                            transition: all 0.16s ease;
                        }

                        .accounting-reports-page .btn-soft {
                            background: #fff;
                            color: var(--text);
                            border: 1px solid var(--line-strong);
                        }

                        .accounting-reports-page .btn-soft:hover {
                            color: var(--primary);
                            border-color: #bfd3ef;
                            background: #f9fbff;
                            text-decoration: none;
                        }

                        .accounting-reports-page .btn-solid {
                            background: linear-gradient(135deg, var(--primary), var(--primary-2));
                            color: #fff;
                            border: none;
                            box-shadow: 0 10px 24px rgba(37, 99, 235, 0.24);
                        }

                        .accounting-reports-page .btn-solid:hover {
                            transform: translateY(-1px);
                            box-shadow: 0 14px 28px rgba(37, 99, 235, 0.28);
                            color: #fff;
                            text-decoration: none;
                        }

                        .accounting-reports-page .stat-strip {
                            display: grid;
                            grid-template-columns: repeat(6, minmax(0, 1fr));
                            gap: 16px;
                            margin-bottom: 22px;
                        }

                        .accounting-reports-page .stat-card,
                        .accounting-reports-page .panel-card,
                        .accounting-reports-page .filter-card,
                        .accounting-reports-page .alert-card {
                            position: relative;
                            overflow: hidden;
                            min-width: 0;
                            background: var(--surface);
                            border: 1px solid rgba(255, 255, 255, 0.72);
                            border-radius: var(--radius-xl);
                            box-shadow: var(--shadow-soft);
                        }

                        .accounting-reports-page .stat-card {
                            padding: 18px 20px 20px;
                        }

                        .accounting-reports-page .stat-card::before {
                            content: '';
                            position: absolute;
                            inset: 0 0 auto 0;
                            height: 4px;
                            background: linear-gradient(90deg, var(--primary), #60a5fa);
                        }

                        .accounting-reports-page .stat-card.success::before {
                            background: linear-gradient(90deg, #10b981, #34d399);
                        }

                        .accounting-reports-page .stat-card.warning::before {
                            background: linear-gradient(90deg, #f59e0b, #fbbf24);
                        }

                        .accounting-reports-page .stat-card.danger::before {
                            background: linear-gradient(90deg, #ef4444, #fb7185);
                        }

                        .accounting-reports-page .stat-label {
                            color: var(--text-faint);
                            font-size: 0.74rem;
                            font-weight: 700;
                            text-transform: uppercase;
                            letter-spacing: 0.08em;
                            margin-bottom: 12px;
                        }

                        .accounting-reports-page .stat-value {
                            color: var(--text);
                            font-size: clamp(1.05rem, 1.4vw + 0.4rem, 1.6rem);
                            font-weight: 800;
                            line-height: 1.1;
                            letter-spacing: -0.03em;
                            font-family: var(--font-mono);
                            min-width: 0;
                            word-break: break-word;
                            overflow-wrap: anywhere;
                        }

                        .accounting-reports-page .stat-meta {
                            margin-top: 7px;
                            color: var(--text-soft);
                            font-size: 0.84rem;
                            font-weight: 400;
                        }

                        .accounting-reports-page .filter-card,
                        .accounting-reports-page .panel-card,
                        .accounting-reports-page .alert-card {
                            margin-bottom: 18px;
                        }

                        .accounting-reports-page .filter-body,
                        .accounting-reports-page .panel-body,
                        .accounting-reports-page .alert-body {
                            padding: 20px 22px;
                        }

                        .accounting-reports-page .panel-header {
                            display: flex;
                            justify-content: space-between;
                            align-items: flex-start;
                            gap: 12px;
                            margin-bottom: 16px;
                            flex-wrap: wrap;
                        }

                        .accounting-reports-page .panel-title {
                            margin: 0;
                            font-size: 1.08rem;
                            font-weight: 800;
                            color: var(--text);
                            letter-spacing: -0.02em;
                        }

                        .accounting-reports-page .panel-subtitle {
                            margin: 4px 0 0;
                            color: var(--text-soft);
                            font-size: 0.86rem;
                        }

                        .accounting-reports-page .filter-grid,
                        .accounting-reports-page .mini-grid,
                        .accounting-reports-page .dual-grid {
                            display: grid;
                            gap: 14px;
                        }

                        .accounting-reports-page .filter-grid {
                            grid-template-columns: repeat(4, minmax(0, 1fr));
                            align-items: end;
                        }

                        .accounting-reports-page .dual-grid {
                            grid-template-columns: repeat(2, minmax(0, 1fr));
                        }

                        .accounting-reports-page .mini-grid {
                            grid-template-columns: repeat(4, minmax(0, 1fr));
                        }

                        .accounting-reports-page .form-label {
                            display: block;
                            margin-bottom: 8px;
                            color: var(--text-soft);
                            font-size: 0.78rem;
                            font-weight: 700;
                            letter-spacing: 0.06em;
                            text-transform: uppercase;
                        }

                        .accounting-reports-page .form-control {
                            border-radius: 12px;
                            border: 1px solid var(--line-strong);
                            box-shadow: none;
                            min-height: 44px;
                            color: var(--text);
                            font-weight: 600;
                        }

                        .accounting-reports-page .form-control:focus {
                            border-color: rgba(37, 99, 235, 0.55);
                            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.12);
                        }

                        .accounting-reports-page .filter-actions {
                            display: flex;
                            gap: 10px;
                            flex-wrap: wrap;
                        }

                        .accounting-reports-page .alert-card {
                            border: 1px solid rgba(37, 99, 235, 0.16);
                            background: linear-gradient(135deg, rgba(37, 99, 235, 0.06), rgba(16, 185, 129, 0.05));
                        }

                        .accounting-reports-page .alert-title {
                            font-size: 0.92rem;
                            font-weight: 800;
                            color: var(--text);
                            margin-bottom: 8px;
                        }

                        .accounting-reports-page .alert-body p:last-child {
                            margin-bottom: 0;
                        }

                        .accounting-reports-page .tab-nav {
                            display: flex;
                            gap: 10px;
                            flex-wrap: wrap;
                            margin-bottom: 18px;
                        }

                        .accounting-reports-page .tab-nav .nav-link {
                            border: 1px solid var(--line-strong);
                            border-radius: 999px;
                            background: rgba(255, 255, 255, 0.82);
                            color: var(--text-soft);
                            font-weight: 700;
                            padding: 10px 16px;
                            transition: all 0.16s ease;
                        }

                        .accounting-reports-page .tab-nav .nav-link.active,
                        .accounting-reports-page .tab-nav .nav-link:hover {
                            background: linear-gradient(135deg, var(--primary), var(--primary-2));
                            color: #fff;
                            border-color: transparent;
                            box-shadow: 0 10px 24px rgba(37, 99, 235, 0.18);
                        }

                        .accounting-reports-page .statement-table,
                        .accounting-reports-page .table {
                            width: 100%;
                            margin-bottom: 0;
                        }

                        .accounting-reports-page .statement-table td,
                        .accounting-reports-page .statement-table th,
                        .accounting-reports-page .table td,
                        .accounting-reports-page .table th {
                            padding: 11px 12px;
                            border-top: 1px solid var(--line);
                            vertical-align: middle;
                        }

                        .accounting-reports-page .statement-table tbody tr:first-child td,
                        .accounting-reports-page .table thead th {
                            border-top: none;
                        }

                        .accounting-reports-page .table thead th {
                            color: var(--text-soft);
                            font-size: 0.74rem;
                            font-weight: 700;
                            text-transform: uppercase;
                            letter-spacing: 0.08em;
                            background: rgba(248, 251, 255, 0.9);
                        }

                        .accounting-reports-page .statement-label,
                        .accounting-reports-page .table-cell-label {
                            color: var(--text);
                            font-weight: 700;
                        }

                        .accounting-reports-page .statement-subline {
                            color: var(--text-soft);
                            font-size: 0.82rem;
                            font-weight: 600;
                        }

                        .accounting-reports-page .statement-amount,
                        .accounting-reports-page .metric-amount {
                            font-family: var(--font-mono);
                            font-weight: 800;
                            color: var(--text);
                            text-align: right;
                            min-width: 0;
                            word-break: break-word;
                            overflow-wrap: anywhere;
                        }

                        .accounting-reports-page .statement-total td {
                            border-top: 2px solid var(--line-strong);
                            font-size: 1rem;
                            font-weight: 800;
                        }

                        .accounting-reports-page .statement-total.positive td {
                            color: var(--success);
                        }

                        .accounting-reports-page .statement-total.negative td {
                            color: var(--danger);
                        }

                        .accounting-reports-page .metric-card {
                            border: 1px solid var(--line);
                            border-radius: 14px;
                            padding: 14px 16px;
                            background: var(--surface-soft);
                            min-width: 0;
                            overflow: hidden;
                        }

                        .accounting-reports-page .metric-label {
                            display: block;
                            color: var(--text-soft);
                            font-size: 0.75rem;
                            font-weight: 700;
                            text-transform: uppercase;
                            letter-spacing: 0.08em;
                            margin-bottom: 8px;
                        }

                        .accounting-reports-page .metric-amount {
                            font-size: clamp(0.95rem, 1.1vw + 0.4rem, 1.2rem);
                            text-align: left;
                            line-height: 1.2;
                        }

                        .accounting-reports-page .metric-meta {
                            margin-top: 8px;
                            color: var(--text-faint);
                            font-size: 0.8rem;
                            font-weight: 600;
                        }

                        .accounting-reports-page .badge-soft {
                            display: inline-flex;
                            align-items: center;
                            gap: 6px;
                            padding: 6px 10px;
                            border-radius: 999px;
                            background: var(--primary-soft);
                            color: var(--primary-2);
                            font-size: 0.76rem;
                            font-weight: 700;
                        }

                        .accounting-reports-page .text-success-strong {
                            color: var(--success);
                        }

                        .accounting-reports-page .text-danger-strong {
                            color: var(--danger);
                        }

                        .accounting-reports-page .muted-note {
                            color: var(--text-faint);
                            font-size: 0.8rem;
                            font-weight: 600;
                        }

                        .accounting-reports-page .table-responsive {
                            overflow-x: auto;
                        }

                        .accounting-reports-page .link-inline {
                            color: var(--primary-2);
                            font-weight: 700;
                            text-decoration: none;
                        }

                        .accounting-reports-page .link-inline:hover {
                            text-decoration: underline;
                        }

                        .accounting-reports-page .empty-state {
                            padding: 22px 18px;
                            border: 1px dashed var(--line-strong);
                            border-radius: 14px;
                            text-align: center;
                            color: var(--text-soft);
                            background: rgba(248, 251, 255, 0.8);
                            font-weight: 600;
                        }

                        @media (max-width: 1399px) {
                            .accounting-reports-page .stat-strip {
                                grid-template-columns: repeat(3, minmax(0, 1fr));
                            }

                            .accounting-reports-page .mini-grid {
                                grid-template-columns: repeat(2, minmax(0, 1fr));
                            }
                        }

                        .accounting-reports-page .ar-range-line {
                            margin-top: 8px;
                            color: var(--text-soft);
                            font-size: 0.85rem;
                            font-weight: 500;
                        }

                        .ar-modal .modal-content.ar-modal {
                            border: none;
                            border-radius: var(--radius-xl);
                            box-shadow: 0 18px 40px rgba(15, 23, 42, 0.18);
                        }

                        .ar-modal .modal-header {
                            border-bottom: 1px solid var(--line);
                            padding: 16px 22px;
                        }

                        .ar-modal .modal-title {
                            font-size: 1rem;
                            font-weight: 700;
                            color: var(--text);
                        }

                        .ar-modal .modal-body {
                            padding: 22px;
                        }

                        .ar-modal .modal-footer {
                            border-top: 1px solid var(--line);
                            padding: 14px 22px;
                            gap: 10px;
                        }

                        .ar-modal-grid {
                            display: grid;
                            grid-template-columns: repeat(2, minmax(0, 1fr));
                            gap: 14px;
                        }

                        .ar-modal-meta {
                            margin-top: 14px;
                            padding: 10px 12px;
                            background: var(--surface-soft);
                            border-radius: var(--radius-md);
                            color: var(--text-soft);
                            font-size: 0.82rem;
                        }

                        .ar-legend-list {
                            list-style: none;
                            padding: 0;
                            margin: 0;
                            display: flex;
                            flex-direction: column;
                            gap: 10px;
                            color: var(--text-soft);
                            font-size: 0.88rem;
                            line-height: 1.5;
                        }

                        .ar-legend-list strong {
                            color: var(--text);
                            font-weight: 700;
                        }

                        .ar-legend-list .ar-legend-note {
                            margin-top: 6px;
                            padding-top: 10px;
                            border-top: 1px dashed var(--line-strong);
                            color: var(--text-faint);
                            font-size: 0.82rem;
                        }

                        .ar-key-metrics {
                            display: grid;
                            grid-template-columns: repeat(3, minmax(0, 1fr));
                            gap: 1px;
                            background: var(--line);
                            border: 1px solid var(--line);
                            border-radius: var(--radius-md);
                            overflow: hidden;
                        }

                        .ar-key-metric {
                            background: var(--surface-strong);
                            padding: 14px 16px;
                            display: flex;
                            flex-direction: column;
                            gap: 6px;
                            min-width: 0;
                        }

                        .ar-key-label {
                            color: var(--text-soft);
                            font-size: 0.72rem;
                            font-weight: 700;
                            letter-spacing: 0.06em;
                            text-transform: uppercase;
                        }

                        .ar-key-value {
                            color: var(--text);
                            font-size: clamp(0.92rem, 0.95vw + 0.4rem, 1.08rem);
                            font-weight: 800;
                            font-family: var(--font-mono);
                            min-width: 0;
                            word-break: break-word;
                            overflow-wrap: anywhere;
                        }

                        .accounting-reports-page .table-responsive {
                            overflow-x: auto;
                        }

                        .accounting-reports-page .dataTables_wrapper {
                            padding-top: 4px;
                        }

                        .accounting-reports-page .dataTables_wrapper .dataTables_filter input,
                        .accounting-reports-page .dataTables_wrapper .dataTables_length select {
                            border: 1px solid var(--line-strong);
                            border-radius: 10px;
                            padding: 6px 12px;
                            min-height: 36px;
                            font-weight: 600;
                        }

                        .accounting-reports-page .dataTables_wrapper .dataTables_filter input:focus,
                        .accounting-reports-page .dataTables_wrapper .dataTables_length select:focus {
                            border-color: rgba(37, 99, 235, 0.55);
                            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.10);
                            outline: none;
                        }

                        .accounting-reports-page .dataTables_wrapper .dataTables_paginate .paginate_button.current,
                        .accounting-reports-page .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover {
                            background: linear-gradient(135deg, var(--primary), var(--primary-2)) !important;
                            border: none !important;
                            color: #fff !important;
                            border-radius: 8px !important;
                        }

                        .accounting-reports-page .dataTables_wrapper .dataTables_paginate .paginate_button {
                            border-radius: 8px !important;
                            border: 1px solid transparent !important;
                            color: var(--text-soft) !important;
                            font-weight: 700;
                        }

                        .accounting-reports-page .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
                            border-color: var(--line-strong) !important;
                            background: #fff !important;
                            color: var(--primary) !important;
                        }

                        .accounting-reports-page .table.dataTable thead th {
                            border-bottom: 1px solid var(--line) !important;
                            font-weight: 700;
                            color: var(--text-soft);
                            font-size: 0.78rem;
                            text-transform: uppercase;
                            letter-spacing: 0.06em;
                        }

                        .accounting-reports-page .table.dataTable tbody td {
                            vertical-align: middle;
                        }

                        .accounting-reports-page .table.dataTable tbody td.statement-amount,
                        .accounting-reports-page .table tbody td.amount-cell {
                            font-family: var(--font-mono);
                            font-weight: 700;
                            white-space: nowrap;
                        }

                        @media (max-width: 991px) {

                            .accounting-reports-page .filter-grid,
                            .accounting-reports-page .dual-grid,
                            .accounting-reports-page .stat-strip,
                            .accounting-reports-page .mini-grid {
                                grid-template-columns: 1fr;
                            }

                            .accounting-reports-page .ar-header {
                                align-items: flex-start;
                            }

                            .ar-modal-grid {
                                grid-template-columns: 1fr;
                            }

                            .ar-key-metrics {
                                grid-template-columns: repeat(2, minmax(0, 1fr));
                            }
                        }

                        @media print {
                            body {
                                background: #fff !important;
                            }

                            .topbar,
                            .left-side-menu,
                            .footer,
                            .ar-actions,
                            .filter-card,
                            .tab-nav {
                                display: none !important;
                            }

                            .content-page {
                                margin-left: 0 !important;
                            }

                            .accounting-reports-page,
                            .accounting-reports-page * {
                                color: #000 !important;
                                box-shadow: none !important;
                                text-shadow: none !important;
                            }

                            .accounting-reports-page {
                                background: #fff !important;
                                padding-bottom: 0 !important;
                            }

                            .accounting-reports-page .panel-card,
                            .accounting-reports-page .stat-card,
                            .accounting-reports-page .alert-card {
                                border: 1px solid #d1d5db !important;
                                background: #fff !important;
                                break-inside: avoid;
                                page-break-inside: avoid;
                            }

                            .accounting-reports-page .tab-pane {
                                display: block !important;
                                opacity: 1 !important;
                            }
                        }
                    </style>

                    <div class="ar-header">
                        <div>
                            <div class="ar-eyebrow">Accounting</div>
                            <h1 class="ar-title">Accounting Reports</h1>
                            <div class="ar-subtitle ar-range-line"><?= htmlspecialchars($rangeLabel); ?> &middot; as of <?= htmlspecialchars($formatDate($asOfDate)); ?></div>
                        </div>
                        <div class="ar-actions">
                            <button type="button" class="btn-soft" data-toggle="modal" data-target="#arFilterModal" title="Filter reports">
                                <i class="mdi mdi-filter-variant"></i> Filter
                            </button>
                            <button type="button" class="btn-soft" data-toggle="modal" data-target="#arLegendModal" title="How to read these reports">
                                <i class="mdi mdi-information-outline"></i> Legend
                            </button>
                            <a href="<?= base_url(); ?>Page/revenueReports" class="btn-soft" title="Revenue Reports">
                                <i class="mdi mdi-chart-line"></i>
                            </a>
                            <a href="<?= base_url(); ?>Page/taxSummaryReport" class="btn-soft" title="Tax Summary">
                                <i class="mdi mdi-receipt-text-outline"></i>
                            </a>
                            <a href="<?= $printUrl; ?>" class="btn-solid" target="_blank" rel="noopener">
                                <i class="mdi mdi-printer"></i> Print
                            </a>
                        </div>
                    </div>

                    <div class="stat-strip">
                        <div class="stat-card">
                            <div class="stat-label">Total Revenue</div>
                            <div class="stat-value">PHP <?= $money($headlineCards['totalRevenue'] ?? 0); ?></div>
                        </div>
                        <div class="stat-card <?= ((float) ($headlineCards['netIncome'] ?? 0) >= 0) ? 'success' : 'danger'; ?>">
                            <div class="stat-label">Net Income</div>
                            <div class="stat-value">PHP <?= $money($headlineCards['netIncome'] ?? 0); ?></div>
                        </div>
                        <div class="stat-card success">
                            <div class="stat-label">Cash Inflow</div>
                            <div class="stat-value">PHP <?= $money($headlineCards['cashInflow'] ?? 0); ?></div>
                        </div>
                        <div class="stat-card warning">
                            <div class="stat-label">Operating Expenses</div>
                            <div class="stat-value">PHP <?= $money($headlineCards['totalExpenses'] ?? 0); ?></div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-label">Receivables</div>
                            <div class="stat-value">PHP <?= $money($headlineCards['accountsReceivable'] ?? 0); ?></div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-label">Tax Credits</div>
                            <div class="stat-value">PHP <?= $money($headlineCards['taxCredits'] ?? 0); ?></div>
                        </div>
                    </div>

                    <div class="modal fade" id="arFilterModal" tabindex="-1" role="dialog" aria-labelledby="arFilterModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered" role="document">
                            <div class="modal-content ar-modal">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="arFilterModalLabel">Filter Reports</h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <form method="get" action="<?= base_url(); ?>Page/accountingReports">
                                    <div class="modal-body">
                                        <div class="ar-modal-grid">
                                            <div>
                                                <label class="form-label" for="accounting-date-from">Date From</label>
                                                <input type="date" id="accounting-date-from" name="date_from" class="form-control" value="<?= htmlspecialchars($filterDateFrom); ?>">
                                            </div>
                                            <div>
                                                <label class="form-label" for="accounting-date-to">Date To</label>
                                                <input type="date" id="accounting-date-to" name="date_to" class="form-control" value="<?= htmlspecialchars($filterDateTo); ?>">
                                            </div>
                                        </div>
                                        <div class="ar-modal-meta">Balance sheet uses an as-of snapshot on <strong><?= htmlspecialchars($formatDate($asOfDate)); ?></strong>. Receivables are limited to documents within <strong><?= htmlspecialchars($rangeLabel); ?></strong>.</div>
                                    </div>
                                    <div class="modal-footer">
                                        <a href="<?= base_url(); ?>Page/accountingReports" class="btn-soft">
                                            <i class="mdi mdi-refresh"></i> Reset
                                        </a>
                                        <button type="submit" class="btn-solid">
                                            <i class="mdi mdi-check"></i> Apply
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="modal fade" id="arLegendModal" tabindex="-1" role="dialog" aria-labelledby="arLegendModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered" role="document">
                            <div class="modal-content ar-modal">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="arLegendModalLabel">How to read these reports</h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <ul class="ar-legend-list">
                                        <li><strong>Income Statement</strong> &mdash; invoices issued, POS sales, manual expenses, and posted payroll within the selected period.</li>
                                        <li><strong>Cash Flow</strong> &mdash; actual collections, payroll net payouts, and cash advances released in the same period.</li>
                                        <li><strong>Balance Sheet</strong> &mdash; operational snapshot as of <strong><?= htmlspecialchars($formatDate($asOfDate)); ?></strong>; reflects payroll deduction payables and employee cash advances where recorded.</li>
                                        <li><strong>Receivables</strong> &mdash; open invoice and POS balances aged from due date to the snapshot date.</li>
                                        <li class="ar-legend-note">BERPS does not operate as a full general ledger (no remittance, capital, depreciation, or inventory subledgers).</li>
                                    </ul>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn-soft" data-dismiss="modal">Close</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <ul class="nav nav-pills tab-nav" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" data-toggle="tab" href="#income-statement-tab" role="tab">Income Statement</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-toggle="tab" href="#balance-sheet-tab" role="tab">Balance Sheet</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-toggle="tab" href="#cash-flow-tab" role="tab">Cash Flow</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-toggle="tab" href="#receivables-tab" role="tab">Receivables</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-toggle="tab" href="#expenses-tab" role="tab">Expenses</a>
                        </li>
                    </ul>

                    <div class="tab-content">
                        <div class="tab-pane fade show active" id="income-statement-tab" role="tabpanel">
                            <div class="dual-grid">
                                <div class="panel-card">
                                    <div class="panel-body">
                                        <div class="panel-header">
                                            <div>
                                                <h3 class="panel-title">Income Statement</h3>
                                                <div class="panel-subtitle">Performance for <?= htmlspecialchars($rangeLabel); ?></div>
                                            </div>
                                            <span class="badge-soft"><?= htmlspecialchars($generatedAt); ?></span>
                                        </div>
                                        <table class="statement-table">
                                            <tbody>
                                                <?php foreach ($revenueSources as $source): ?>
                                                    <tr>
                                                        <td>
                                                            <div class="statement-label"><?= htmlspecialchars((string) ($source['label'] ?? 'Revenue')); ?></div>
                                                            <div class="statement-subline"><?= (int) ($source['count'] ?? 0); ?> document(s)</div>
                                                        </td>
                                                        <td class="statement-amount">PHP <?= $money($source['amount'] ?? 0); ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                                <tr class="statement-total">
                                                    <td class="statement-label">Total Revenue</td>
                                                    <td class="statement-amount">PHP <?= $money($incomeStatement['totalRevenue'] ?? 0); ?></td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        <div class="statement-label">Operating Expenses (Non-Payroll)</div>
                                                        <div class="statement-subline">Manual expenses booked in the selected range</div>
                                                    </td>
                                                    <td class="statement-amount">PHP <?= $money($incomeStatement['manualExpenses'] ?? 0); ?></td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        <div class="statement-label">Salaries and Wages</div>
                                                        <div class="statement-subline"><?= (int) ($incomeStatement['payrollRunCount'] ?? 0); ?> payroll run(s), <?= (int) ($incomeStatement['payrollEmployeeCount'] ?? 0); ?> employee coverages</div>
                                                    </td>
                                                    <td class="statement-amount">PHP <?= $money($incomeStatement['payrollExpense'] ?? 0); ?></td>
                                                </tr>
                                                <tr class="statement-total <?= ((float) ($incomeStatement['netIncome'] ?? 0) >= 0) ? 'positive' : 'negative'; ?>">
                                                    <td class="statement-label">Net Income</td>
                                                    <td class="statement-amount">PHP <?= $money($incomeStatement['netIncome'] ?? 0); ?></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <div class="panel-card">
                                    <div class="panel-body">
                                        <div class="panel-header">
                                            <div>
                                                <h3 class="panel-title">Supporting Metrics</h3>
                                                <div class="panel-subtitle">Activity and collections for <?= htmlspecialchars($rangeLabel); ?></div>
                                            </div>
                                        </div>
                                        <div class="ar-key-metrics">
                                            <div class="ar-key-metric">
                                                <span class="ar-key-label">Cash Collected</span>
                                                <span class="ar-key-value">PHP <?= $money($incomeStatement['totalCashCollections'] ?? 0); ?></span>
                                            </div>
                                            <div class="ar-key-metric">
                                                <span class="ar-key-label">Tax Credits</span>
                                                <span class="ar-key-value">PHP <?= $money($incomeStatement['invoiceTaxCredits'] ?? 0); ?></span>
                                            </div>
                                            <div class="ar-key-metric">
                                                <span class="ar-key-label">Invoices</span>
                                                <span class="ar-key-value"><?= (int) ($incomeStatement['invoiceCount'] ?? 0); ?></span>
                                            </div>
                                            <div class="ar-key-metric">
                                                <span class="ar-key-label">POS Sales</span>
                                                <span class="ar-key-value"><?= (int) ($incomeStatement['posSalesCount'] ?? 0); ?></span>
                                            </div>
                                            <div class="ar-key-metric">
                                                <span class="ar-key-label">Payroll Runs</span>
                                                <span class="ar-key-value"><?= (int) ($incomeStatement['payrollRunCount'] ?? 0); ?></span>
                                            </div>
                                            <div class="ar-key-metric">
                                                <span class="ar-key-label">Payroll Net Paid</span>
                                                <span class="ar-key-value">PHP <?= $money($incomeStatement['payrollNetPaid'] ?? 0); ?></span>
                                            </div>
                                            <div class="ar-key-metric">
                                                <span class="ar-key-label">Invoice Cash</span>
                                                <span class="ar-key-value">PHP <?= $money($incomeStatement['invoiceCashCollections'] ?? 0); ?></span>
                                            </div>
                                            <div class="ar-key-metric">
                                                <span class="ar-key-label">POS Cash</span>
                                                <span class="ar-key-value">PHP <?= $money($incomeStatement['posCashCollections'] ?? 0); ?></span>
                                            </div>
                                            <div class="ar-key-metric">
                                                <span class="ar-key-label">Payroll Deductions</span>
                                                <span class="ar-key-value">PHP <?= $money($incomeStatement['payrollDeductions'] ?? 0); ?></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="balance-sheet-tab" role="tabpanel">
                            <div class="dual-grid">
                                <div class="panel-card">
                                    <div class="panel-body">
                                        <div class="panel-header">
                                            <div>
                                                <h3 class="panel-title">Assets</h3>
                                                <div class="panel-subtitle">Tracked operational assets as of <?= htmlspecialchars($formatDate($asOfDate)); ?></div>
                                            </div>
                                        </div>
                                        <table class="statement-table">
                                            <tbody>
                                                <?php foreach ($balanceAssets as $line): ?>
                                                    <tr>
                                                        <td class="statement-label"><?= htmlspecialchars((string) ($line['label'] ?? 'Asset')); ?></td>
                                                        <td class="statement-amount">PHP <?= $money($line['amount'] ?? 0); ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                                <tr class="statement-total">
                                                    <td class="statement-label">Total Assets</td>
                                                    <td class="statement-amount">PHP <?= $money($balanceSheet['totalAssets'] ?? 0); ?></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <div class="panel-card">
                                    <div class="panel-body">
                                        <div class="panel-header">
                                            <div>
                                                <h3 class="panel-title">Liabilities and Equity</h3>
                                                <div class="panel-subtitle">Based on balances currently recorded in BERPS</div>
                                            </div>
                                        </div>
                                        <table class="statement-table">
                                            <tbody>
                                                <?php foreach ($balanceLiabilitiesAndEquity as $line): ?>
                                                    <tr>
                                                        <td class="statement-label"><?= htmlspecialchars((string) ($line['label'] ?? 'Line Item')); ?></td>
                                                        <td class="statement-amount">PHP <?= $money($line['amount'] ?? 0); ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                                <tr class="statement-total">
                                                    <td class="statement-label">Total Liabilities and Equity</td>
                                                    <td class="statement-amount">PHP <?= $money($balanceSheet['totalLiabilitiesAndEquity'] ?? 0); ?></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                        <div class="muted-note mt-3">
                                            Payroll deduction payables and employee cash advances are now included where BERPS records them. The operational equity line remains the balancing figure from tracked revenue, receivables, tax credits, collections, payroll, and expenses.
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="panel-card">
                                <div class="panel-body">
                                    <div class="panel-header">
                                        <div>
                                            <h3 class="panel-title">Supporting Totals</h3>
                                            <div class="panel-subtitle">Cumulative activity up to <?= htmlspecialchars($formatDate($asOfDate)); ?></div>
                                        </div>
                                    </div>
                                    <div class="mini-grid">
                                        <div class="metric-card">
                                            <span class="metric-label">Service Revenue to Date</span>
                                            <div class="metric-amount">PHP <?= $money($balanceSheet['serviceRevenueToDate'] ?? 0); ?></div>
                                        </div>
                                        <div class="metric-card">
                                            <span class="metric-label">POS Revenue to Date</span>
                                            <div class="metric-amount">PHP <?= $money($balanceSheet['posRevenueToDate'] ?? 0); ?></div>
                                        </div>
                                        <div class="metric-card">
                                            <span class="metric-label">Expenses to Date</span>
                                            <div class="metric-amount">PHP <?= $money($balanceSheet['expensesToDate'] ?? 0); ?></div>
                                        </div>
                                        <div class="metric-card">
                                            <span class="metric-label">Manual Expenses to Date</span>
                                            <div class="metric-amount">PHP <?= $money($balanceSheet['manualExpensesToDate'] ?? 0); ?></div>
                                        </div>
                                        <div class="metric-card">
                                            <span class="metric-label">Payroll Expense to Date</span>
                                            <div class="metric-amount">PHP <?= $money($balanceSheet['payrollExpenseToDate'] ?? 0); ?></div>
                                        </div>
                                        <div class="metric-card">
                                            <span class="metric-label">Cumulative Net Income</span>
                                            <div class="metric-amount">PHP <?= $money($balanceSheet['cumulativeNetIncome'] ?? 0); ?></div>
                                        </div>
                                        <div class="metric-card">
                                            <span class="metric-label">Invoice Cash to Date</span>
                                            <div class="metric-amount">PHP <?= $money($balanceSheet['invoiceCashToDate'] ?? 0); ?></div>
                                        </div>
                                        <div class="metric-card">
                                            <span class="metric-label">POS Cash to Date</span>
                                            <div class="metric-amount">PHP <?= $money($balanceSheet['posCashToDate'] ?? 0); ?></div>
                                        </div>
                                        <div class="metric-card">
                                            <span class="metric-label">Payroll Net Paid to Date</span>
                                            <div class="metric-amount">PHP <?= $money($balanceSheet['payrollNetPaidToDate'] ?? 0); ?></div>
                                        </div>
                                        <div class="metric-card">
                                            <span class="metric-label">Payroll Payables to Date</span>
                                            <div class="metric-amount">PHP <?= $money($balanceSheet['trackedLiabilities'] ?? 0); ?></div>
                                        </div>
                                        <div class="metric-card">
                                            <span class="metric-label">Service Receivables</span>
                                            <div class="metric-amount">PHP <?= $money($balanceSheet['serviceReceivables'] ?? 0); ?></div>
                                        </div>
                                        <div class="metric-card">
                                            <span class="metric-label">Tax Credits Receivable</span>
                                            <div class="metric-amount">PHP <?= $money($balanceSheet['taxCreditsToDate'] ?? 0); ?></div>
                                        </div>
                                        <div class="metric-card">
                                            <span class="metric-label">Cash Advances Outstanding</span>
                                            <div class="metric-amount">PHP <?= $money($balanceSheet['cashAdvancesOutstanding'] ?? 0); ?></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="cash-flow-tab" role="tabpanel">
                            <div class="dual-grid">
                                <div class="panel-card">
                                    <div class="panel-body">
                                        <div class="panel-header">
                                            <div>
                                                <h3 class="panel-title">Cash Flow Summary</h3>
                                                <div class="panel-subtitle">Cash movement for <?= htmlspecialchars($rangeLabel); ?></div>
                                            </div>
                                        </div>
                                        <table class="statement-table">
                                            <tbody>
                                                <tr>
                                                    <td class="statement-label">Invoice Cash Collections</td>
                                                    <td class="statement-amount">PHP <?= $money($cashFlow['invoiceCashCollections'] ?? 0); ?></td>
                                                </tr>
                                                <tr>
                                                    <td class="statement-label">POS Cash Collections</td>
                                                    <td class="statement-amount">PHP <?= $money($cashFlow['posCashCollections'] ?? 0); ?></td>
                                                </tr>
                                                <tr class="statement-total">
                                                    <td class="statement-label">Total Cash In</td>
                                                    <td class="statement-amount">PHP <?= $money($cashFlow['totalCashIn'] ?? 0); ?></td>
                                                </tr>
                                                <tr>
                                                    <td class="statement-label">Operating Expenses Paid</td>
                                                    <td class="statement-amount">PHP <?= $money($cashFlow['manualExpensesPaid'] ?? 0); ?></td>
                                                </tr>
                                                <tr>
                                                    <td class="statement-label">Payroll Net Paid</td>
                                                    <td class="statement-amount">PHP <?= $money($cashFlow['payrollNetPaid'] ?? 0); ?></td>
                                                </tr>
                                                <tr>
                                                    <td class="statement-label">Cash Advances Released</td>
                                                    <td class="statement-amount">PHP <?= $money($cashFlow['cashAdvancesIssued'] ?? 0); ?></td>
                                                </tr>
                                                <tr class="statement-total">
                                                    <td class="statement-label">Total Cash Out</td>
                                                    <td class="statement-amount">PHP <?= $money($cashFlow['totalCashOut'] ?? 0); ?></td>
                                                </tr>
                                                <tr class="statement-total <?= ((float) ($cashFlow['netCashMovement'] ?? 0) >= 0) ? 'positive' : 'negative'; ?>">
                                                    <td class="statement-label">Net Cash Movement</td>
                                                    <td class="statement-amount">PHP <?= $money($cashFlow['netCashMovement'] ?? 0); ?></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                        <div class="muted-note mt-3">
                                            BIR Form 2307 tax credits are tracked below as non-cash invoice credits and do not increase cash inflow.
                                        </div>
                                    </div>
                                </div>

                                <div class="panel-card">
                                    <div class="panel-body">
                                        <div class="panel-header">
                                            <div>
                                                <h3 class="panel-title">Payroll and Credit Support</h3>
                                                <div class="panel-subtitle">Supplemental non-cash and payroll metrics</div>
                                            </div>
                                        </div>
                                        <div class="mini-grid">
                                            <div class="metric-card">
                                                <span class="metric-label">Invoice Tax Credits</span>
                                                <div class="metric-amount">PHP <?= $money($cashFlow['invoiceTaxCredits'] ?? 0); ?></div>
                                            </div>
                                            <div class="metric-card">
                                                <span class="metric-label">Invoice Total Credit</span>
                                                <div class="metric-amount">PHP <?= $money($cashFlow['invoiceGrossCredits'] ?? 0); ?></div>
                                            </div>
                                            <div class="metric-card">
                                                <span class="metric-label">Payroll Gross Expense</span>
                                                <div class="metric-amount">PHP <?= $money($cashFlow['payrollExpense'] ?? 0); ?></div>
                                            </div>
                                            <div class="metric-card">
                                                <span class="metric-label">Payroll Deductions</span>
                                                <div class="metric-amount">PHP <?= $money($cashFlow['payrollDeductions'] ?? 0); ?></div>
                                            </div>
                                            <div class="metric-card">
                                                <span class="metric-label">Cash Advances Released</span>
                                                <div class="metric-amount">PHP <?= $money($cashFlow['cashAdvancesIssued'] ?? 0); ?></div>
                                            </div>
                                            <div class="metric-card">
                                                <span class="metric-label">Cash Outflow</span>
                                                <div class="metric-amount">PHP <?= $money($cashFlow['totalCashOut'] ?? 0); ?></div>
                                            </div>
                                            <div class="metric-card">
                                                <span class="metric-label">Payroll Net Paid</span>
                                                <div class="metric-amount">PHP <?= $money($cashFlow['payrollNetPaid'] ?? 0); ?></div>
                                            </div>
                                            <div class="metric-card">
                                                <span class="metric-label">Net Movement</span>
                                                <div class="metric-amount <?= ((float) ($cashFlow['netCashMovement'] ?? 0) >= 0) ? 'text-success-strong' : 'text-danger-strong'; ?>">
                                                    PHP <?= $money($cashFlow['netCashMovement'] ?? 0); ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="panel-card">
                                <div class="panel-body">
                                    <div class="panel-header">
                                        <div>
                                            <h3 class="panel-title">Monthly Activity</h3>
                                            <div class="panel-subtitle">Revenue, collections, payroll, and expenses by month within the selected range</div>
                                        </div>
                                    </div>
                                    <div class="table-responsive">
                                        <table id="ar-monthly-table" class="table">
                                            <thead>
                                                <tr>
                                                    <th>Period</th>
                                                    <th>Invoice Revenue</th>
                                                    <th>POS Revenue</th>
                                                    <th>Invoice Cash</th>
                                                    <th>POS Cash</th>
                                                    <th>Tax Credit</th>
                                                    <th>Manual Expenses</th>
                                                    <th>Payroll Expense</th>
                                                    <th>Payroll Net</th>
                                                    <th>Cash Advances</th>
                                                    <th>Net Income</th>
                                                    <th>Net Cash</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php if (empty($cashMonthly)): ?>
                                                    <tr>
                                                        <td colspan="12">
                                                            <div class="empty-state">No monthly activity was recorded for this range yet.</div>
                                                        </td>
                                                    </tr>
                                                <?php else: ?>
                                                    <?php foreach ($cashMonthly as $row): ?>
                                                        <tr>
                                                            <td class="table-cell-label"><?= htmlspecialchars((string) ($row['periodLabel'] ?? '-')); ?></td>
                                                            <td>PHP <?= $money($row['invoiceRevenue'] ?? 0); ?></td>
                                                            <td>PHP <?= $money($row['posRevenue'] ?? 0); ?></td>
                                                            <td>PHP <?= $money($row['invoiceCash'] ?? 0); ?></td>
                                                            <td>PHP <?= $money($row['posCash'] ?? 0); ?></td>
                                                            <td>PHP <?= $money($row['invoiceTax'] ?? 0); ?></td>
                                                            <td>PHP <?= $money($row['expenses'] ?? 0); ?></td>
                                                            <td>PHP <?= $money($row['payrollExpense'] ?? 0); ?></td>
                                                            <td>PHP <?= $money($row['payrollNet'] ?? 0); ?></td>
                                                            <td>PHP <?= $money($row['cashAdvancesIssued'] ?? 0); ?></td>
                                                            <td class="<?= ((float) ($row['netIncome'] ?? 0) >= 0) ? 'text-success-strong' : 'text-danger-strong'; ?>">PHP <?= $money($row['netIncome'] ?? 0); ?></td>
                                                            <td class="<?= ((float) ($row['netCash'] ?? 0) >= 0) ? 'text-success-strong' : 'text-danger-strong'; ?>">PHP <?= $money($row['netCash'] ?? 0); ?></td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                <?php endif; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="receivables-tab" role="tabpanel">
                            <div class="stat-strip" style="grid-template-columns: repeat(5, minmax(0, 1fr));">
                                <?php foreach ($receivableBuckets as $bucket): ?>
                                    <div class="stat-card">
                                        <div class="stat-label"><?= htmlspecialchars((string) ($bucket['label'] ?? 'Bucket')); ?></div>
                                        <div class="stat-value">PHP <?= $money($bucket['amount'] ?? 0); ?></div>
                                        <div class="stat-meta"><?= (int) ($bucket['count'] ?? 0); ?> open item(s)</div>
                                    </div>
                                <?php endforeach; ?>
                            </div>

                            <div class="panel-card">
                                <div class="panel-body">
                                    <div class="panel-header">
                                        <div>
                                            <h3 class="panel-title">Open Receivables</h3>
                                            <div class="panel-subtitle">Open balances for documents dated <?= htmlspecialchars($rangeLabel); ?>, net of credits posted through <?= htmlspecialchars($formatDate($asOfDate)); ?></div>
                                        </div>
                                        <span class="badge-soft">PHP <?= $money($receivables['totalReceivable'] ?? 0); ?> total</span>
                                    </div>
                                    <div class="table-responsive">
                                        <table id="ar-receivables-table" class="table">
                                            <thead>
                                                <tr>
                                                    <th>Source</th>
                                                    <th>Reference</th>
                                                    <th>Customer</th>
                                                    <th>Document Date</th>
                                                    <th>Due Date</th>
                                                    <th>Total</th>
                                                    <th>Credit Applied</th>
                                                    <th>Balance</th>
                                                    <th>Aging</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php if (empty($receivableRows)): ?>
                                                    <tr>
                                                        <td colspan="9">
                                                            <div class="empty-state">No open receivables were found for the selected snapshot date.</div>
                                                        </td>
                                                    </tr>
                                                <?php else: ?>
                                                    <?php foreach ($receivableRows as $row): ?>
                                                        <tr>
                                                            <td><?= htmlspecialchars((string) ($row['sourceLabel'] ?? '-')); ?></td>
                                                            <td>
                                                                <?php if (!empty($row['viewUrl'])): ?>
                                                                    <a href="<?= htmlspecialchars((string) $row['viewUrl']); ?>" class="link-inline">
                                                                        <?= htmlspecialchars((string) ($row['referenceNo'] ?? '-')); ?>
                                                                    </a>
                                                                <?php else: ?>
                                                                    <span class="table-cell-label"><?= htmlspecialchars((string) ($row['referenceNo'] ?? '-')); ?></span>
                                                                <?php endif; ?>
                                                            </td>
                                                            <td>
                                                                <div class="table-cell-label"><?= htmlspecialchars((string) ($row['customer'] ?? '-')); ?></div>
                                                                <?php if (!empty($row['description'])): ?>
                                                                    <div class="statement-subline"><?= htmlspecialchars((string) $row['description']); ?></div>
                                                                <?php endif; ?>
                                                            </td>
                                                            <td><?= htmlspecialchars($formatDate($row['documentDate'] ?? '')); ?></td>
                                                            <td><?= htmlspecialchars($formatDate($row['dueDate'] ?? '')); ?></td>
                                                            <td>PHP <?= $money($row['totalAmount'] ?? 0); ?></td>
                                                            <td>PHP <?= $money($row['creditApplied'] ?? 0); ?></td>
                                                            <td class="table-cell-label">PHP <?= $money($row['balance'] ?? 0); ?></td>
                                                            <td><?= (int) ($row['daysPastDue'] ?? 0); ?> day(s)</td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                <?php endif; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="expenses-tab" role="tabpanel">
                            <div class="dual-grid">
                                <div class="panel-card">
                                    <div class="panel-body">
                                        <div class="panel-header">
                                            <div>
                                                <h3 class="panel-title">Expense Categories</h3>
                                                <div class="panel-subtitle">Breakdown for <?= htmlspecialchars($rangeLabel); ?> including posted payroll</div>
                                            </div>
                                        </div>
                                        <div class="table-responsive">
                                            <table id="ar-expense-categories-table" class="table">
                                                <thead>
                                                    <tr>
                                                        <th>Category</th>
                                                        <th>Entries</th>
                                                        <th>Share</th>
                                                        <th>Total</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php if (empty($expenseCategories)): ?>
                                                        <tr>
                                                            <td colspan="4">
                                                                <div class="empty-state">No expenses were recorded in this range yet.</div>
                                                            </td>
                                                        </tr>
                                                    <?php else: ?>
                                                        <?php foreach ($expenseCategories as $category): ?>
                                                            <tr>
                                                                <td class="table-cell-label"><?= htmlspecialchars((string) ($category['label'] ?? 'Uncategorized')); ?></td>
                                                                <td><?= (int) ($category['count'] ?? 0); ?></td>
                                                                <td><?= number_format((float) ($category['share'] ?? 0), 2); ?>%</td>
                                                                <td>PHP <?= $money($category['amount'] ?? 0); ?></td>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                    <?php endif; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>

                                <div class="panel-card">
                                    <div class="panel-body">
                                        <div class="panel-header">
                                            <div>
                                                <h3 class="panel-title">Recent Expense Entries</h3>
                                                <div class="panel-subtitle">Latest 25 manual expense and payroll entries inside the current range</div>
                                            </div>
                                            <span class="badge-soft">PHP <?= $money($expenseSummary['total'] ?? 0); ?> total</span>
                                        </div>
                                        <div class="table-responsive">
                                            <table id="ar-recent-expenses-table" class="table">
                                                <thead>
                                                    <tr>
                                                        <th>Date</th>
                                                        <th>Category</th>
                                                        <th>Description</th>
                                                        <th>Processed By</th>
                                                        <th>Amount</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php if (empty($recentExpenseRows)): ?>
                                                        <tr>
                                                            <td colspan="5">
                                                                <div class="empty-state">No recent expense entries are available for this range.</div>
                                                            </td>
                                                        </tr>
                                                    <?php else: ?>
                                                        <?php foreach ($recentExpenseRows as $expense): ?>
                                                            <tr>
                                                                <td><?= htmlspecialchars($formatDate($expense->ExpenseDate ?? '')); ?></td>
                                                                <td class="table-cell-label"><?= htmlspecialchars(trim((string) ($expense->Category ?? '')) !== '' ? (string) $expense->Category : 'Uncategorized'); ?></td>
                                                                <td><?= htmlspecialchars((string) ($expense->Description ?? '-')); ?></td>
                                                                <td><?= htmlspecialchars((string) ($expense->processedBy ?? $expense->Cashier ?? '-')); ?></td>
                                                                <td>PHP <?= $money($expense->Amount ?? 0); ?></td>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                    <?php endif; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
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
    <script src="<?= base_url(); ?>assets/libs/datatables/jquery.dataTables.min.js"></script>
    <script src="<?= base_url(); ?>assets/libs/datatables/dataTables.bootstrap4.min.js"></script>
    <script src="<?= base_url(); ?>assets/libs/datatables/dataTables.responsive.min.js"></script>
    <script src="<?= base_url(); ?>assets/libs/datatables/responsive.bootstrap4.min.js"></script>

    <script>
        (function initAccountingDataTables() {
            if (typeof window.jQuery === 'undefined' || typeof window.jQuery.fn.DataTable === 'undefined') return;
            var $ = window.jQuery;

            $(function () {
                var common = {
                    responsive: true,
                    autoWidth: false,
                    pageLength: 10,
                    lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, 'All']],
                    language: { search: '', searchPlaceholder: 'Search…' }
                };

                function initIfPresent(selector, opts) {
                    var $table = $(selector);
                    if (!$table.length || $.fn.DataTable.isDataTable(selector)) return;
                    $table.DataTable($.extend({}, common, opts || {}));
                }

                initIfPresent('#ar-receivables-table', { order: [[4, 'asc']] });
                initIfPresent('#ar-recent-expenses-table', { order: [[0, 'desc']] });
                initIfPresent('#ar-monthly-table', { order: [[0, 'asc']], paging: false, searching: false, info: false });
                initIfPresent('#ar-expense-categories-table', { order: [[3, 'desc']], paging: false, searching: false, info: false });
            });
        })();

        (function activateTabFromHash() {
            function activate() {
                var hash = window.location.hash;
                if (!hash) return;
                var trigger = document.querySelector('a.nav-link[data-toggle="tab"][href="' + hash + '"]');
                if (!trigger) return;
                if (window.jQuery && typeof window.jQuery.fn.tab === 'function') {
                    window.jQuery(trigger).tab('show');
                } else {
                    trigger.click();
                }
            }
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', activate);
            } else {
                activate();
            }
            window.addEventListener('hashchange', activate);
        })();

        <?php if ($printMode): ?>
            window.addEventListener('load', function() {
                window.print();
            });
        <?php endif; ?>
    </script>
</body>

</html>
