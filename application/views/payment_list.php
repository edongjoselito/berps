<?php
$filterDateFromValue = isset($filterDateFrom) ? (string) $filterDateFrom : date('Y-m-d');
$filterDateToValue = isset($filterDateTo) ? (string) $filterDateTo : $filterDateFromValue;
$todayDateValue = isset($currentDayDate) ? (string) $currentDayDate : date('Y-m-d');
$showingToday = !empty($showingTodayOnly);
$isAdmin = strtolower(trim((string) $this->session->userdata('level'))) === 'admin';
$clients = isset($data2) && is_array($data2) ? $data2 : array();

$paymentRecords = isset($data) ? $data : array();
if ($paymentRecords instanceof Traversable) {
    $paymentRecords = iterator_to_array($paymentRecords, false);
}
$paymentRecords = is_array($paymentRecords) ? array_values($paymentRecords) : array();

if (!empty($paymentRecords)) {
    usort($paymentRecords, function ($a, $b) {
        $aDate = (isset($a->PDate) && $a->PDate !== '') ? strtotime((string) $a->PDate) : 0;
        $bDate = (isset($b->PDate) && $b->PDate !== '') ? strtotime((string) $b->PDate) : 0;

        if ($aDate === $bDate) {
            $aId = isset($a->paymentID) ? (int) $a->paymentID : 0;
            $bId = isset($b->paymentID) ? (int) $b->paymentID : 0;
            if ($aId === $bId) {
                return 0;
            }

            return ($aId < $bId) ? 1 : -1;
        }

        return ($aDate < $bDate) ? 1 : -1;
    });
}

$displayLimit = 300;
$totalPayments = count($paymentRecords);
$limitedView = ($displayLimit > 0) && ($totalPayments > $displayLimit);
$displayPayments = $limitedView ? array_slice($paymentRecords, 0, $displayLimit) : $paymentRecords;

$todayTotalValue = (!empty($todayTotal) && isset($todayTotal[0]->Total))
    ? (float) $todayTotal[0]->Total
    : 0.0;
$filteredTotalValue = 0.0;
foreach ($paymentRecords as $paymentRow) {
    $filteredTotalValue += (float) ($paymentRow->GrossAmountPaid ?? ((float) ($paymentRow->AmountPaid ?? 0) + (float) ($paymentRow->TaxAmount ?? 0)));
}
if ($showingToday) {
    $todayTotalValue = $filteredTotalValue;
}

$averageCreditValue = $totalPayments > 0 ? ($filteredTotalValue / $totalPayments) : 0.0;

$rangeSummaryLabel = $filterDateFromValue;
if ($filterDateFromValue !== '' && $filterDateToValue !== '') {
    $formattedFrom = date('F j, Y', strtotime($filterDateFromValue));
    $formattedTo = date('F j, Y', strtotime($filterDateToValue));
    $rangeSummaryLabel = ($filterDateFromValue === $filterDateToValue)
        ? $formattedFrom
        : $formattedFrom . ' to ' . $formattedTo;
}

$filterQueryString = http_build_query(array(
    'date_from' => $filterDateFromValue,
    'date_to' => $filterDateToValue,
));
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
                <div class="container-fluid payment-list-page">

                    <style>

                        .payment-list-page {
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
                            background:
                                radial-gradient(circle at top left, rgba(37, 99, 235, 0.08), transparent 28%),
                                radial-gradient(circle at top right, rgba(16, 185, 129, 0.08), transparent 24%),
                                linear-gradient(180deg, #f8fbff 0%, #f4f7fb 100%);
                            min-height: 100vh;
                            padding-bottom: 20px;
                            font-family: var(--font-primary, Montserrat, Segoe UI, Arial, sans-serif);
                        }

                        .payment-list-page * {
                            box-sizing: border-box;
                        }

                        .payment-list-page .page-header {
                            display: flex;
                            justify-content: space-between;
                            align-items: flex-end;
                            gap: 16px;
                            margin: 16px 0 16px;
                            flex-wrap: wrap;
                        }

                        .payment-list-page .page-eyebrow {
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

                        .payment-list-page .page-eyebrow::before {
                            content: '';
                            width: 8px;
                            height: 8px;
                            border-radius: 50%;
                            background: linear-gradient(135deg, var(--primary), var(--primary-2));
                            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.10);
                        }

                        .payment-list-page .page-title {
                            margin: 0;
                            font-family: var(--font-primary, Montserrat, Segoe UI, Arial, sans-serif);
                            font-size: 1.5rem;
                            line-height: 1.2;
                            letter-spacing: -0.02em;
                            font-weight: 700;
                            color: var(--text);
                        }

                        .payment-list-page .page-subtitle {
                            margin-top: 6px;
                            color: var(--text-soft);
                            font-size: 0.9rem;
                            max-width: 760px;
                        }

                        .payment-list-page .page-actions {
                            display: flex;
                            gap: 12px;
                            flex-wrap: wrap;
                        }

                        .payment-list-page .btn-action,
                        .payment-list-page .btn-submit {
                            display: inline-flex;
                            align-items: center;
                            justify-content: center;
                            gap: 8px;
                            border-radius: 12px;
                            font-size: 0.92rem;
                            font-weight: 700;
                            padding: 11px 18px;
                            transition: all 0.16s ease;
                            text-decoration: none;
                        }

                        .payment-list-page .btn-action {
                            border: 1px solid var(--line-strong);
                            color: var(--text);
                            background: #fff;
                        }

                        .payment-list-page .btn-action:hover {
                            color: var(--primary);
                            border-color: #bfd3ef;
                            background: #f9fbff;
                        }

                        .payment-list-page .btn-submit {
                            border: none;
                            color: #fff;
                            background: linear-gradient(135deg, var(--primary), var(--primary-2));
                            box-shadow: 0 10px 24px rgba(37, 99, 235, 0.24);
                        }

                        .payment-list-page .btn-submit:hover {
                            transform: translateY(-1px);
                            box-shadow: 0 14px 28px rgba(37, 99, 235, 0.28);
                        }

                        .payment-list-page .stats-grid {
                            display: grid;
                            grid-template-columns: repeat(4, minmax(0, 1fr));
                            gap: 12px;
                            margin-bottom: 16px;
                        }

                        .payment-list-page .stat-card {
                            position: relative;
                            overflow: hidden;
                            background: var(--surface);
                            border: 1px solid rgba(255, 255, 255, 0.72);
                            border-radius: var(--radius-xl);
                            box-shadow: var(--shadow-soft);
                            padding: 14px 16px 14px;
                        }

                        .payment-list-page .stat-card::before {
                            content: '';
                            position: absolute;
                            inset: 0 0 auto 0;
                            height: 4px;
                        }

                        .payment-list-page .stat-today::before {
                            background: linear-gradient(90deg, #3b82f6, #60a5fa);
                        }

                        .payment-list-page .stat-filtered::before {
                            background: linear-gradient(90deg, #10b981, #34d399);
                        }

                        .payment-list-page .stat-count::before {
                            background: linear-gradient(90deg, #f59e0b, #fbbf24);
                        }

                        .payment-list-page .stat-average::before {
                            background: linear-gradient(90deg, #ef4444, #fb7185);
                        }

                        .payment-list-page .stat-label {
                            color: var(--text-faint);
                            font-size: 0.65rem;
                            font-weight: 600;
                            text-transform: uppercase;
                            letter-spacing: 0.06em;
                            margin-bottom: 8px;
                        }

                        .payment-list-page .stat-value {
                            color: var(--text);
                            font-size: 1.25rem;
                            font-weight: 700;
                            line-height: 1.2;
                            letter-spacing: -0.02em;
                            font-family: var(--font-primary, Montserrat, Segoe UI, Arial, sans-serif);
                        }

                        .payment-list-page .stat-meta {
                            color: var(--text-soft);
                            font-size: 0.72rem;
                            margin-top: 4px;
                        }

                        .payment-list-page .card-stack {
                            display: grid;
                            gap: 16px;
                        }

                        .payment-list-page .theme-card {
                            background: var(--surface);
                            border: 1px solid rgba(255, 255, 255, 0.72);
                            border-radius: var(--radius-xl);
                            box-shadow: var(--shadow-soft);
                            overflow: hidden;
                        }

                        .payment-list-page .theme-card-head {
                            padding: 14px 18px;
                            border-bottom: 1px solid var(--line);
                            background: linear-gradient(180deg, rgba(255, 255, 255, 0.98), rgba(249, 251, 255, 0.94));
                        }

                        .payment-list-page .theme-card-title {
                            margin: 0;
                            color: var(--text);
                            font-size: 0.95rem;
                            font-weight: 700;
                            letter-spacing: -0.01em;
                        }

                        .payment-list-page .theme-card-subtitle {
                            margin-top: 4px;
                            color: var(--text-soft);
                            font-size: 0.8rem;
                        }

                        .payment-list-page .theme-card-body {
                            padding: 18px;
                        }

                        .payment-list-page .filter-form .form-group {
                            margin-bottom: 0;
                        }

                        .payment-list-page label {
                            color: var(--text);
                            font-size: 0.82rem;
                            font-weight: 700;
                            letter-spacing: 0.02em;
                            margin-bottom: 8px;
                        }

                        .payment-list-page .form-control,
                        .payment-list-page .custom-select {
                            border: 1px solid var(--line-strong);
                            border-radius: var(--radius-sm);
                            min-height: 46px;
                            color: var(--text);
                            box-shadow: none;
                        }

                        .payment-list-page .form-control:focus,
                        .payment-list-page .custom-select:focus {
                            border-color: #9cc0f5;
                            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
                        }

                        .payment-list-page .summary-row {
                            display: flex;
                            justify-content: space-between;
                            align-items: center;
                            gap: 12px;
                            flex-wrap: wrap;
                            margin-top: 16px;
                        }

                        .payment-list-page .summary-chip {
                            display: inline-flex;
                            align-items: center;
                            gap: 8px;
                            padding: 8px 12px;
                            border-radius: 999px;
                            border: 1px solid #dbeafe;
                            background: var(--primary-soft);
                            color: var(--primary-2);
                            font-size: 0.8rem;
                            font-weight: 700;
                        }

                        .payment-list-page .summary-note {
                            color: var(--text-soft);
                            font-size: 0.84rem;
                        }

                        .payment-list-page .table-responsive {
                            overflow-x: auto;
                        }

                        .payment-list-page .table {
                            margin-bottom: 0;
                        }

                        .payment-list-page .table thead th {
                            border-top: none;
                            border-bottom: 1px solid var(--line);
                            color: var(--text-faint);
                            font-size: 0.72rem;
                            font-weight: 800;
                            letter-spacing: 0.08em;
                            text-transform: uppercase;
                            white-space: nowrap;
                        }

                        .payment-list-page .table td {
                            vertical-align: middle;
                            border-color: var(--line);
                        }

                        .payment-list-page .invoice-link {
                            color: var(--primary-2);
                            font-weight: 600;
                            text-decoration: none;
                        }

                        .payment-list-page .invoice-link:hover {
                            color: var(--primary);
                            text-decoration: underline;
                        }

                        .payment-list-page .payment-date {
                            white-space: nowrap;
                        }

                        .payment-list-page .payor-link {
                            color: var(--primary-2);
                            font-weight: 700;
                            text-decoration: none;
                        }

                        .payment-list-page .payor-link:hover {
                            text-decoration: underline;
                        }

                        .payment-list-page .payor-sub,
                        .payment-list-page .description-sub {
                            color: var(--text-soft);
                            font-size: 0.82rem;
                            margin-top: 3px;
                        }

                        .payment-list-page .payment-actions {
                            display: inline-flex;
                            align-items: center;
                            justify-content: center;
                            gap: 8px;
                            flex-wrap: wrap;
                        }

                        .payment-list-page .payment-actions-desktop {
                            display: inline-flex;
                            gap: 6px;
                            flex-wrap: wrap;
                            justify-content: center;
                        }

                        .payment-list-page .payment-actions-mobile {
                            display: none;
                        }

                        .payment-list-page .action-overflow-toggle {
                            display: inline-flex;
                            align-items: center;
                            justify-content: center;
                            width: 36px;
                            height: 36px;
                            border: 1px solid var(--line-strong);
                            border-radius: 999px;
                            background: #fff;
                            color: var(--text);
                            box-shadow: var(--shadow-soft);
                            transition: all 0.16s ease;
                        }

                        .payment-list-page .action-overflow-toggle:hover,
                        .payment-list-page .action-overflow-toggle:focus {
                            color: var(--primary);
                            border-color: #bfd3ef;
                            background: #f9fbff;
                            outline: none;
                            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
                        }

                        .payment-list-page .action-overflow-toggle::after {
                            display: none;
                        }

                        .payment-list-page .payment-actions-menu {
                            min-width: 176px;
                            padding: 8px;
                            border: 1px solid var(--line);
                            border-radius: 14px;
                            box-shadow: 0 18px 38px rgba(15, 23, 42, 0.14);
                        }

                        .payment-list-page .payment-actions-menu .dropdown-item {
                            display: flex;
                            align-items: center;
                            gap: 10px;
                            border-radius: 10px;
                            padding: 10px 12px;
                            color: var(--text);
                            font-size: 0.88rem;
                            font-weight: 600;
                        }

                        .payment-list-page .payment-actions-menu .dropdown-item:hover,
                        .payment-list-page .payment-actions-menu .dropdown-item:focus {
                            background: #f8fbff;
                            color: var(--primary-2);
                        }

                        .payment-list-page .payment-actions-menu .dropdown-item.text-danger {
                            color: #dc2626 !important;
                        }

                        .payment-list-page .payment-actions-menu .dropdown-item.text-danger:hover,
                        .payment-list-page .payment-actions-menu .dropdown-item.text-danger:focus {
                            background: rgba(220, 38, 38, 0.08);
                            color: #dc2626 !important;
                        }

                        .payment-list-page .payment-actions-menu .dropdown-item i {
                            font-size: 1rem;
                        }

                        .payment-list-page .action-icon {
                            position: relative;
                            display: inline-flex;
                            align-items: center;
                            justify-content: center;
                            width: 34px;
                            height: 34px;
                            border-radius: 50%;
                            text-decoration: none;
                            font-size: 16px;
                            transition: all 0.18s ease;
                        }

                        .payment-list-page .action-icon.edit {
                            color: var(--primary-2);
                            background: rgba(37, 99, 235, 0.12);
                        }

                        .payment-list-page .action-icon.edit:hover {
                            background: rgba(37, 99, 235, 0.22);
                            color: var(--primary-2);
                        }

                        .payment-list-page .action-icon.delete {
                            color: #dc2626;
                            background: rgba(220, 38, 38, 0.12);
                        }

                        .payment-list-page .action-icon.delete:hover {
                            background: rgba(220, 38, 38, 0.22);
                            color: #dc2626;
                        }

                        .payment-list-page .action-icon.void {
                            color: #d97706;
                            background: rgba(217, 119, 6, 0.12);
                        }

                        .payment-list-page .action-icon.void:hover {
                            background: rgba(217, 119, 6, 0.22);
                            color: #d97706;
                        }

                        .payment-list-page .action-icon::after {
                            content: attr(data-label);
                            position: absolute;
                            bottom: -32px;
                            left: 50%;
                            transform: translate(-50%, 6px);
                            background: rgba(15, 23, 42, 0.92);
                            color: #fff;
                            padding: 4px 8px;
                            border-radius: 4px;
                            font-size: 11px;
                            white-space: nowrap;
                            opacity: 0;
                            pointer-events: none;
                            transition: all 0.15s ease;
                        }

                        .payment-list-page .action-icon:hover::after {
                            opacity: 1;
                            transform: translate(-50%, 0);
                        }

                        .payment-list-page .empty-action {
                            color: var(--text-faint);
                            font-size: 0.82rem;
                            font-weight: 600;
                        }

                        .payment-list-page .table-init-hidden {
                            opacity: 0;
                        }

                        .payment-list-page .table-init-ready {
                            opacity: 1;
                            transition: opacity 0.2s ease;
                        }

                        .payment-list-page .data-table-container {
                            position: relative;
                        }

                        .payment-list-page .data-table-container.loading::after {
                            content: 'Loading payments...';
                            position: absolute;
                            inset: 0;
                            background: rgba(255, 255, 255, 0.85);
                            display: flex;
                            align-items: center;
                            justify-content: center;
                            font-size: 0.9rem;
                            color: var(--text-soft);
                            z-index: 1;
                        }

                        .payment-list-page .data-table-container.loading::before {
                            content: '';
                            position: absolute;
                            top: 50%;
                            left: 50%;
                            width: 26px;
                            height: 26px;
                            margin: -40px 0 0 -13px;
                            border-radius: 50%;
                            border: 3px solid rgba(108, 117, 125, 0.3);
                            border-top-color: rgba(37, 99, 235, 0.8);
                            animation: payment-spinner 0.7s linear infinite;
                            z-index: 2;
                        }

                        .payment-list-page .data-table-container.ready::after,
                        .payment-list-page .data-table-container.ready::before {
                            display: none;
                        }

                        .payment-list-page .dataTables_wrapper .dataTables_filter input,
                        .payment-list-page .dataTables_wrapper .dataTables_length select {
                            border: 1px solid var(--line-strong);
                            border-radius: 10px;
                            min-height: 38px;
                            padding: 6px 10px;
                            background: #fff;
                        }

                        .payment-list-page .dataTables_wrapper .dataTables_paginate .paginate_button {
                            border-radius: 8px !important;
                        }

                        .payment-list-page .modal-content {
                            border: none;
                            border-radius: 20px;
                            overflow: hidden;
                            box-shadow: 0 24px 60px rgba(15, 23, 42, 0.16);
                        }

                        .payment-list-page .modal-header {
                            background: linear-gradient(135deg, var(--primary), var(--primary-2));
                            color: #fff;
                            border-bottom: none;
                            padding: 18px 22px;
                        }

                        .payment-list-page .modal-header .close {
                            color: #fff;
                            opacity: 0.85;
                        }

                        .payment-list-page .modal-body {
                            padding: 22px;
                        }

                        .payment-list-page .modal-footer {
                            border-top: 1px solid var(--line);
                            padding: 16px 22px;
                        }

                        @keyframes payment-spinner {
                            to {
                                transform: rotate(360deg);
                            }
                        }

                        @media (max-width: 1199px) {
                            .payment-list-page .stats-grid {
                                grid-template-columns: repeat(2, minmax(0, 1fr));
                            }
                        }

                        @media (max-width: 767px) {
                            .payment-list-page .page-title {
                                font-size: 1.75rem;
                            }

                            .payment-list-page .stats-grid {
                                grid-template-columns: 1fr;
                            }

                            .payment-list-page .theme-card-head,
                            .payment-list-page .theme-card-body,
                            .payment-list-page .modal-body,
                            .payment-list-page .modal-footer {
                                padding-left: 16px;
                                padding-right: 16px;
                            }
                        }

                        @media (max-width: 991px) {
                            .payment-list-page .payment-actions-desktop {
                                display: none;
                            }

                            .payment-list-page .payment-actions-mobile {
                                display: inline-flex;
                            }
                        }
                    </style>

                    <div class="page-header">
                        <div>
                            <div class="page-eyebrow">Collections</div>
                            <h4 class="page-title">Payment Collections</h4>
                            <!-- <div class="page-subtitle">Review accepted payments, filter collection activity by date range, and quickly add new receipts using the same BERPS workspace style as the rest of the billing screens.</div> -->
                        </div>
                        <div class="page-actions">
                            <button type="button" class="btn-action" data-toggle="modal" data-target="#searchPaymentModal">
                                <i class="mdi mdi-magnify"></i>
                                Find Payment
                            </button>
                            <button type="button" class="btn-action" data-toggle="modal" data-target="#filterModal">
                                <i class="mdi mdi-filter-outline"></i>
                                Filter
                            </button>
                            <a href="<?= base_url(); ?>Page/unifiedPayment" class="btn-submit">
                                <i class="mdi mdi-credit-card-plus-outline"></i>
                                Add New Payment
                            </a>
                        </div>
                    </div>

                    <div class="stats-grid">
                        <div class="stat-card stat-today">
                            <div class="stat-label">Current Day Collections</div>
                            <div class="stat-value"><?= number_format($todayTotalValue, 2); ?></div>
                            <div class="stat-meta"><?= date('F j, Y', strtotime($todayDateValue)); ?></div>
                        </div>
                        <div class="stat-card stat-filtered">
                            <div class="stat-label">Filtered Collections</div>
                            <div class="stat-value"><?= number_format($filteredTotalValue, 2); ?></div>
                            <div class="stat-meta"><?= htmlspecialchars($rangeSummaryLabel, ENT_QUOTES, 'UTF-8'); ?></div>
                        </div>
                        <div class="stat-card stat-count">
                            <div class="stat-label">Payments Shown</div>
                            <div class="stat-value"><?= number_format($totalPayments); ?></div>
                            <div class="stat-meta"><?= $showingToday ? 'Showing today only' : 'Filtered date range'; ?></div>
                        </div>
                        <div class="stat-card stat-average">
                            <div class="stat-label">Average Credit</div>
                            <div class="stat-value"><?= number_format($averageCreditValue, 2); ?></div>
                            <div class="stat-meta">Average total credit per payment entry.</div>
                        </div>
                    </div>

                    <div class="card-stack">
                        <div class="theme-card">
                            <div class="theme-card-head">
                                <h5 class="theme-card-title">Payment Records</h5>
                                <!-- <div class="theme-card-subtitle">Browse posted collections, BIR Form 2307 credits, references, and linked payors in one table.</div> -->
                            </div>
                            <div class="theme-card-body">
                                <div class="table-responsive">
                                    <table id="payment-table" class="table">
                                        <thead>
                                            <tr>
                                                <th>Payment ID</th>
                                                <th>Invoice No</th>
                                                <th>Date</th>
                                                <th class="text-right">Amount Paid</th>
                                                <th class="text-right">Tax 2307</th>
                                                <th class="text-right">Total Credit</th>
                                                <th>O.R. No.</th>
                                                <th>Reference</th>
                                                <th>Payor</th>
                                                <th class="text-center">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (!empty($displayPayments)): ?>
                                                <?php foreach ($displayPayments as $row): ?>
                                                    <?php
                                                    $cashPaid = (float) ($row->AmountPaid ?? 0);
                                                    $taxAmount = (float) ($row->TaxAmount ?? 0);
                                                    $grossAmount = (float) ($row->GrossAmountPaid ?? ($cashPaid + $taxAmount));

                                                    $customerHistoryParams = array();
                                                    if (!empty($row->CustID)) {
                                                        $customerHistoryParams['cust_id'] = (string) $row->CustID;
                                                    } else {
                                                        $customerHistoryParams['customer'] = (string) $row->Customer;
                                                    }
                                                    if (!empty($filterQueryString)) {
                                                        parse_str($filterQueryString, $filterQueryArray);
                                                        $customerHistoryParams = array_merge($customerHistoryParams, $filterQueryArray);
                                                    }
                                                    $customerHistoryUrl = base_url() . 'Page/customerHistory?' . http_build_query($customerHistoryParams);
                                                    ?>
                                                    <tr>
                                                        <td><?= !empty($row->paymentID) ? '#' . htmlspecialchars((string) $row->paymentID, ENT_QUOTES, 'UTF-8') : '<span class="text-muted">-</span>'; ?></td>
                                                        <td>
                                                            <?php if (!empty($row->InvoiceNo)): ?>
                                                                <a href="<?= base_url(); ?>Page/invoice?id=<?= htmlspecialchars((string) ($row->orderID ?? ''), ENT_QUOTES, 'UTF-8'); ?>" class="invoice-link">
                                                                    #<?= htmlspecialchars((string) $row->InvoiceNo, ENT_QUOTES, 'UTF-8'); ?>
                                                                </a>
                                                            <?php else: ?>
                                                                <span class="text-muted">-</span>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td class="payment-date"><?= htmlspecialchars((string) $row->PDate, ENT_QUOTES, 'UTF-8'); ?></td>
                                                        <td class="text-right"><?= number_format($cashPaid, 2); ?></td>
                                                        <td class="text-right"><?= number_format($taxAmount, 2); ?></td>
                                                        <td class="text-right"><?= number_format($grossAmount, 2); ?></td>
                                                        <td><?= !empty($row->ORNo) ? htmlspecialchars((string) $row->ORNo, ENT_QUOTES, 'UTF-8') : '<span class="text-muted">-</span>'; ?></td>
                                                        <td><?= !empty($row->PaymentReference) ? htmlspecialchars((string) $row->PaymentReference, ENT_QUOTES, 'UTF-8') : '<span class="text-muted">-</span>'; ?></td>
                                                        <td>
                                                            <a class="payor-link" href="<?= htmlspecialchars($customerHistoryUrl, ENT_QUOTES, 'UTF-8'); ?>">
                                                                <?= htmlspecialchars((string) $row->Customer, ENT_QUOTES, 'UTF-8'); ?>
                                                            </a>
                                                            <?php if (!empty($row->CustID)): ?>
                                                                <div class="payor-sub">Client ID <?= htmlspecialchars((string) $row->CustID, ENT_QUOTES, 'UTF-8'); ?></div>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td class="text-center">
                                                            <?php if ($isAdmin): ?>
                                                                <div class="payment-actions">
                                                                    <div class="payment-actions-desktop">
                                                                        <a class="action-icon edit" href="<?= base_url(); ?>Page/updatePayment?id=<?= (int) $row->paymentID; ?>" data-label="Edit" title="Edit">
                                                                            <i class="mdi mdi-square-edit-outline"></i>
                                                                            <span class="sr-only">Edit</span>
                                                                        </a>
                                                                        <a class="action-icon void" href="javascript:void(0);" data-toggle="modal" data-target="#voidPaymentModal" data-paymentid="<?= (int) $row->paymentID; ?>" data-orno="<?= htmlspecialchars($row->ORNo ?? ''); ?>" onclick="prepareVoidPaymentModal(this)" data-label="Void" title="Void">
                                                                            <i class="mdi mdi-cancel"></i>
                                                                            <span class="sr-only">Void</span>
                                                                        </a>
                                                                        <a class="action-icon delete" href="<?= base_url(); ?>Page/deletePayment?id=<?= (int) $row->paymentID; ?>" onclick="return confirm('Are you sure you want to delete this record?');" data-label="Delete" title="Delete">
                                                                            <i class="mdi mdi-trash-can-outline"></i>
                                                                            <span class="sr-only">Delete</span>
                                                                        </a>
                                                                    </div>
                                                                    <div class="dropdown payment-actions-mobile">
                                                                        <button
                                                                            class="action-overflow-toggle dropdown-toggle"
                                                                            type="button"
                                                                            id="paymentActionsMenu<?= (int) $row->paymentID; ?>"
                                                                            data-toggle="dropdown"
                                                                            aria-haspopup="true"
                                                                            aria-expanded="false"
                                                                            title="More actions">
                                                                            <i class="mdi mdi-dots-horizontal"></i>
                                                                            <span class="sr-only">Open payment actions</span>
                                                                        </button>
                                                                        <div class="dropdown-menu dropdown-menu-right payment-actions-menu" aria-labelledby="paymentActionsMenu<?= (int) $row->paymentID; ?>">
                                                                            <a class="dropdown-item" href="<?= base_url(); ?>Page/updatePayment?id=<?= (int) $row->paymentID; ?>">
                                                                                <i class="mdi mdi-square-edit-outline"></i>
                                                                                <span>Edit Payment</span>
                                                                            </a>
                                                                            <a class="dropdown-item" href="javascript:void(0);" data-toggle="modal" data-target="#voidPaymentModal" data-paymentid="<?= (int) $row->paymentID; ?>" data-orno="<?= htmlspecialchars($row->ORNo ?? ''); ?>" onclick="prepareVoidPaymentModal(this)">
                                                                                <i class="mdi mdi-cancel"></i>
                                                                                <span>Void Payment</span>
                                                                            </a>
                                                                            <a class="dropdown-item text-danger" href="<?= base_url(); ?>Page/deletePayment?id=<?= (int) $row->paymentID; ?>" onclick="return confirm('Are you sure you want to delete this record?');">
                                                                                <i class="mdi mdi-trash-can-outline"></i>
                                                                                <span>Delete Payment</span>
                                                                            </a>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            <?php else: ?>
                                                                <span class="empty-action">View only</span>
                                                            <?php endif; ?>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <tr>
                                                    <td colspan="10" class="text-center text-muted py-4">No payments found for the selected date range.</td>
                                                </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Filter Modal -->
                    <div class="modal fade payment-list-page" id="filterModal" tabindex="-1" role="dialog" aria-hidden="true">
                        <div class="modal-dialog" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title mb-0">
                                        <i class="mdi mdi-filter-outline mr-2"></i>Filter Collections
                                    </h5>
                                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <form method="get" action="<?= base_url(); ?>Page/paymentList" id="filterForm">
                                        <div class="form-row">
                                            <div class="form-group col-md-6">
                                                <label for="payment-filter-from">From Date</label>
                                                <input type="date" class="form-control" id="payment-filter-from" name="date_from" value="<?= htmlspecialchars($filterDateFromValue, ENT_QUOTES, 'UTF-8'); ?>" required>
                                            </div>
                                            <div class="form-group col-md-6">
                                                <label for="payment-filter-to">To Date</label>
                                                <input type="date" class="form-control" id="payment-filter-to" name="date_to" value="<?= htmlspecialchars($filterDateToValue, ENT_QUOTES, 'UTF-8'); ?>" required>
                                            </div>
                                        </div>
                                    </form>
                                    <div class="alert alert-info mt-3 mb-0" style="border-radius: var(--radius-md); background: var(--primary-soft); border: none; color: var(--primary-2);">
                                        <i class="mdi mdi-information-outline mr-2"></i>
                                        Showing payments for <strong><?= htmlspecialchars($rangeSummaryLabel, ENT_QUOTES, 'UTF-8'); ?></strong>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <a href="<?= base_url(); ?>Page/paymentList" class="btn-action">
                                        <i class="mdi mdi-calendar-today"></i>
                                        Today
                                    </a>
                                    <button type="submit" form="filterForm" class="btn-submit">
                                        <i class="mdi mdi-filter-outline"></i>
                                        Apply Filter
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Void Payment Modal -->
                    <div class="modal fade" id="voidPaymentModal" tabindex="-1" role="dialog" aria-hidden="true">
                        <div class="modal-dialog" role="document">
                            <div class="modal-content" style="border-radius: 22px; overflow: hidden; box-shadow: 0 28px 60px rgba(15, 23, 42, 0.18);">
                                <div class="modal-header" style="background: linear-gradient(135deg, #d97706, #f59e0b); border: none; padding: 22px 24px;">
                                    <h5 class="modal-title" style="color: #fff; font-size: 1.08rem; font-weight: 800; letter-spacing: -0.02em;">
                                        <i class="mdi mdi-cancel mr-2"></i>Void Payment
                                    </h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color: #fff; opacity: 1; text-shadow: none; background: rgba(255,255,255,0.14); border: 1px solid rgba(255,255,255,0.22); width: 38px; height: 38px; border-radius: 50%; padding: 0; margin: 0; line-height: 1;">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body" style="background: linear-gradient(180deg, #fbfdff 0%, #f6f9fc 100%); padding: 24px;">
                                    <form id="voidPaymentForm" method="post" action="<?= base_url(); ?>Page/voidPayment">
                                        <input type="hidden" name="paymentID" id="voidPaymentID" value="">

                                        <div class="alert" style="border: none; border-radius: 14px; background: #fffbeb; color: #92400e; font-size: 0.9rem;">
                                            <i class="mdi mdi-alert mr-2"></i>
                                            <strong>Warning:</strong> Voiding a payment will cancel this record and reverse its effect on the invoice balance. This action cannot be undone.
                                        </div>

                                        <div class="form-group" style="margin-top: 20px;">
                                            <label style="display: block; margin-bottom: 8px; color: #334155; font-size: 0.85rem; font-weight: 700;">
                                                OR Number
                                            </label>
                                            <input type="text" id="voidORNo" class="form-control" readonly style="background: #f8fafc; color: #64748b; border: 1px solid #e2e8f0; border-radius: 12px; padding: 12px 14px; font-family: var(--font-primary, Montserrat, Segoe UI, Arial, sans-serif);">
                                        </div>

                                        <div class="form-group" style="margin-top: 16px;">
                                            <label style="display: block; margin-bottom: 8px; color: #334155; font-size: 0.85rem; font-weight: 700;">
                                                Reason for Voiding <span style="color: #d97706;">*</span>
                                            </label>
                                            <textarea name="voidReason" id="voidPaymentReason" class="form-control" rows="3" required placeholder="Enter reason for voiding this payment..." style="border: 1px solid #e2e8f0; border-radius: 12px; padding: 12px 14px; resize: vertical;"></textarea>
                                        </div>

                                        <div class="text-right" style="margin-top: 24px; padding-top: 20px; border-top: 1px solid #e2e8f0;">
                                            <button type="button" class="btn" data-dismiss="modal" style="background: #fff; color: #334155; border: 1px solid #e2e8f0; border-radius: 10px; padding: 10px 20px; font-weight: 600; margin-right: 10px;">
                                                Cancel
                                            </button>
                                            <button type="submit" class="btn" style="background: linear-gradient(135deg, #d97706, #f59e0b); color: #fff; border: none; border-radius: 10px; padding: 10px 24px; font-weight: 700; box-shadow: 0 4px 14px rgba(217, 119, 6, 0.3);">
                                                <i class="mdi mdi-cancel mr-1"></i>Void Payment
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
        </div>
    </div>

    <?php include('includes/themecustomizer.php'); ?>

    <div class="modal fade payment-list-page" id="paymentModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title mb-0">New Payment</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form class="needs-validation" method="post" action="<?= base_url(); ?>Page/addPayment" novalidate id="paymentModalForm">
                        <div class="form-row">
                            <div class="form-group col-md-3">
                                <label for="payment-date">Date</label>
                                <input type="date" class="form-control" id="payment-date" name="PDate" value="<?= date('Y-m-d'); ?>" required>
                            </div>
                            <div class="form-group col-md-3">
                                <label for="payment-amount">Amount Paid</label>
                                <input type="number" class="form-control" id="payment-amount" name="AmountPaid" min="0" step="0.01" required>
                            </div>
                            <div class="form-group col-md-3">
                                <label for="payment-orno">O.R. No.</label>
                                <input type="text" class="form-control" id="payment-orno" name="ORNo">
                            </div>
                            <div class="form-group col-md-3">
                                <label for="payment-reference">Reference</label>
                                <input type="text" class="form-control" id="payment-reference" name="PaymentReference" placeholder="Optional">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-4">
                                <label for="payment-invoice">Invoice No.</label>
                                <input type="text" class="form-control" id="payment-invoice" name="InvoiceNo" placeholder="Optional">
                            </div>
                            <div class="form-group col-md-4">
                                <label for="payment-customer">Payor</label>
                                <select class="custom-select" id="payment-customer" name="CustID" required>
                                    <option value="">Select payor</option>
                                    <?php if (!empty($clients)): ?>
                                        <?php foreach ($clients as $customer): ?>
                                            <option value="<?= htmlspecialchars((string) $customer->CustID, ENT_QUOTES, 'UTF-8'); ?>">
                                                <?= htmlspecialchars((string) $customer->Customer, ENT_QUOTES, 'UTF-8'); ?> · <?= htmlspecialchars((string) $customer->CustID, ENT_QUOTES, 'UTF-8'); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                            <div class="form-group col-md-4">
                                <label for="payment-description">Transaction / Description</label>
                                <input type="text" class="form-control" id="payment-description" name="TransDescription" required>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="submit" name="submit" form="paymentModalForm" class="btn-submit">
                        <i class="mdi mdi-check-circle-outline"></i>
                        Accept Payment
                    </button>
                    <button type="reset" form="paymentModalForm" class="btn-action">Reset</button>
                </div>
            </div>
        </div>
    </div>

    <script src="<?= base_url(); ?>assets/js/vendor.min.js"></script>
    <script src="<?= base_url(); ?>assets/js/app.min.js"></script>
    <script src="<?= base_url(); ?>assets/libs/datatables/jquery.dataTables.min.js"></script>
    <script src="<?= base_url(); ?>assets/libs/datatables/dataTables.bootstrap4.min.js"></script>

    <script>
        (function($) {
            'use strict';

            $(document).ready(function() {
                var $paymentTable = $('#payment-table');

                if ($paymentTable.length && $paymentTable.find('tbody tr').not(':has(td[colspan])').length > 0) {
                    try {
                        var table = $paymentTable.DataTable({
                            pageLength: 25
                        });
                    } catch (e) {
                        console.error('DataTables initialization error:', e);
                    }
                }

                $('#paymentModal').on('hidden.bs.modal', function() {
                    var form = document.getElementById('paymentModalForm');
                    if (form) {
                        form.reset();
                        form.classList.remove('was-validated');
                    }
                });

                // Void Payment Modal Handler
                window.prepareVoidPaymentModal = function(element) {
                    var paymentID = $(element).data('paymentid');
                    var orNo = $(element).data('orno');
                    $('#voidPaymentID').val(paymentID);
                    $('#voidORNo').val(orNo);
                    $('#voidPaymentReason').val('');
                };

                // Search Payment Modal Handler
                var searchPaymentModal = $('#searchPaymentModal');
                var searchInput = $('#searchPaymentInput');
                var searchBtn = $('#searchPaymentBtn');
                var searchResults = $('#searchResults');
                var searchResultsContent = $('#searchResultsContent');
                var searchLoading = $('#searchLoading');
                var searchNoResults = $('#searchNoResults');

                // Search on Enter key
                searchInput.on('keypress', function(e) {
                    if (e.which === 13) {
                        searchPayment();
                    }
                });

                // Search on button click
                searchBtn.on('click', searchPayment);

                function searchPayment() {
                    var searchTerm = searchInput.val().trim();

                    if (!searchTerm) {
                        alert('Please enter a Payment ID or Invoice Number');
                        return;
                    }

                    // Show loading
                    searchResults.hide();
                    searchNoResults.hide();
                    searchLoading.show();

                    // Make AJAX call to search for payment
                    $.ajax({
                        url: '<?= base_url(); ?>Page/searchPayment',
                        method: 'POST',
                        data: {
                            search_term: searchTerm
                        },
                        success: function(response) {
                            searchLoading.hide();

                            if (response.success && response.data) {
                                displaySearchResults(response.data);
                            } else {
                                searchNoResults.show();
                            }
                        },
                        error: function() {
                            searchLoading.hide();
                            searchNoResults.show();
                            alert('Error searching for payment. Please try again.');
                        }
                    });
                }

                function displaySearchResults(data) {
                    var html = '';

                    if (Array.isArray(data)) {
                        data.forEach(function(payment) {
                            html += createPaymentResultHTML(payment);
                        });
                    } else {
                        html += createPaymentResultHTML(data);
                    }

                    searchResultsContent.html(html);
                    searchResults.show();
                }

                function createPaymentResultHTML(payment) {
                    var amountPaid = parseFloat(payment.AmountPaid || 0);
                    var taxAmount = parseFloat(payment.TaxAmount || 0);
                    var totalCredit = amountPaid + taxAmount;

                    return `
                    <div class="search-result-item">
                        <div class="search-result-header">
                            <h6 class="search-result-title">
                                Payment ID: #${payment.paymentID || 'N/A'}
                            </h6>
                            <span class="badge badge-primary">${payment.PDate || 'No Date'}</span>
                        </div>
                        <div class="search-result-details">
                            <div class="search-result-detail">
                                <strong>Invoice:</strong> ${payment.InvoiceNo || 'N/A'}
                            </div>
                            <div class="search-result-detail">
                                <strong>Customer:</strong> ${payment.Customer || 'N/A'}
                            </div>
                            <div class="search-result-detail">
                                <strong>O.R. No:</strong> ${payment.ORNo || 'N/A'}
                            </div>
                            <div class="search-result-detail">
                                <strong>Total Credit:</strong> ${totalCredit.toFixed(2)}
                            </div>
                        </div>
                        <div class="search-result-actions">
                            <a href="<?= base_url(); ?>Page/updatePayment?id=${payment.paymentID}"
                               class="btn-edit-payment"
                               target="_blank">
                                <i class="mdi mdi-pencil"></i> Edit Payment
                            </a>
                        </div>
                    </div>
                `;
                }

                // Reset modal when hidden
                searchPaymentModal.on('hidden.bs.modal', function() {
                    searchInput.val('');
                    searchResults.hide();
                    searchNoResults.hide();
                    searchLoading.hide();
                    searchResultsContent.html('');
                });
            });
        })(jQuery);
    </script>

    <!-- Special Search Payment Modal -->
    <div class="modal fade" id="searchPaymentModal" tabindex="-1" role="dialog" aria-labelledby="searchPaymentModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="searchPaymentModalLabel">
                        <i class="mdi mdi-magnify"></i>
                        Find & Edit Payment
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="search-form">
                        <div class="form-group">
                            <label for="searchPaymentInput">Search by Payment ID or Invoice Number</label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="searchPaymentInput"
                                    placeholder="Enter Payment ID (e.g., 123) or Invoice No (e.g., 456)"
                                    autocomplete="off">
                                <div class="input-group-append">
                                    <button type="button" class="btn btn-primary" id="searchPaymentBtn">
                                        <i class="mdi mdi-magnify"></i> Search
                                    </button>
                                </div>
                            </div>
                            <small class="form-text text-muted">
                                <i class="mdi mdi-information"></i>
                                Enter just the number (e.g., "123" for Payment ID or "456" for Invoice No)
                            </small>
                        </div>
                    </div>

                    <div id="searchResults" style="display: none;">
                        <hr>
                        <h6><i class="mdi mdi-clipboard-check"></i> Search Results</h6>
                        <div id="searchResultsContent"></div>
                    </div>

                    <div id="searchLoading" style="display: none; text-align: center; padding: 20px;">
                        <i class="mdi mdi-loading mdi-spin"></i> Searching...
                    </div>

                    <div id="searchNoResults" style="display: none; text-align: center; padding: 20px;">
                        <i class="mdi mdi-clipboard-off"></i>
                        <p>No payment found matching your search.</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="mdi mdi-close"></i> Close
                    </button>
                </div>
            </div>
        </div>
    </div>

    <style>
        .search-form {
            margin-bottom: 20px;
        }

        .search-result-item {
            border: 1px solid var(--line);
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 10px;
            background: var(--surface);
        }

        .search-result-item:hover {
            border-color: var(--primary);
            box-shadow: 0 2px 8px rgba(37, 99, 235, 0.1);
        }

        .search-result-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }

        .search-result-title {
            font-weight: 600;
            color: var(--text);
            margin: 0;
        }

        .search-result-details {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 8px;
            margin-bottom: 10px;
        }

        .search-result-detail {
            font-size: 0.9rem;
            color: var(--text-soft);
        }

        .search-result-actions {
            text-align: right;
        }

        .btn-edit-payment {
            background: var(--primary-soft);
            color: var(--primary-2);
            border: 1px solid var(--primary-soft);
            padding: 6px 12px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 0.85rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .btn-edit-payment:hover {
            background: var(--primary);
            color: white;
            text-decoration: none;
        }
    </style>

</body>

</html>