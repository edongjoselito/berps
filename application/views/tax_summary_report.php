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

                        /* Official print document styles */
                        .tax-summary-page .ts-print-header {
                            text-align: center;
                        }

                        .tax-summary-page .ts-print-title {
                            font-size: 1.6rem;
                            font-weight: 800;
                            text-transform: uppercase;
                            letter-spacing: 0.02em;
                        }

                        .tax-summary-page .ts-print-subtitle {
                            font-size: 1.05rem;
                            font-weight: 700;
                            margin: 4px 0 8px;
                        }

                        .tax-summary-page .ts-print-meta {
                            font-size: 0.82rem;
                            line-height: 1.7;
                        }

                        .tax-summary-page .ts-print-divider {
                            border: none;
                            border-top: 2px solid #000;
                            margin: 12px 0 18px;
                        }

                        .tax-summary-page .ts-print-footer {
                            display: none;
                            margin-top: 40px;
                            padding-top: 16px;
                            border-top: 1px solid #999;
                            text-align: center;
                            font-size: 0.78rem;
                            color: #555;
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
                            .ts-hero,
                            .filter-card,
                            .ts-actions,
                            .ts-hero__actions,
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
                                padding: 0 0 16px 0;
                                margin-bottom: 20px;
                                border-bottom: 2px solid #000;
                            }

                            .tax-summary-page .ts-print-footer {
                                display: block;
                            }

                            .tax-summary-page .ts-print-divider {
                                display: block;
                            }

                            .tax-summary-page .stat-strip {
                                grid-template-columns: repeat(4, 1fr) !important;
                                gap: 8px !important;
                                margin-bottom: 16px !important;
                            }

                            .tax-summary-page .stat-card {
                                padding: 10px 12px !important;
                                min-height: 0 !important;
                                border: 1px solid #333 !important;
                                border-radius: 4px !important;
                            }

                            .tax-summary-page .stat-card::before {
                                display: none !important;
                            }

                            .tax-summary-page .stat-label {
                                font-size: 0.7rem !important;
                                text-transform: uppercase;
                                letter-spacing: 0.03em;
                            }

                            .tax-summary-page .stat-value {
                                font-size: 1rem !important;
                            }

                            .tax-summary-page .stat-meta {
                                font-size: 0.7rem !important;
                            }

                            .tax-summary-page .panel-card {
                                border: 1px solid #333 !important;
                                border-top: 1px solid #333 !important;
                                border-radius: 4px !important;
                                box-shadow: none !important;
                                margin-bottom: 14px !important;
                            }

                            .tax-summary-page .panel-header {
                                border-bottom: 1px solid #333 !important;
                                padding: 10px 14px !important;
                            }

                            .tax-summary-page .panel-title {
                                font-size: 0.95rem !important;
                                text-transform: uppercase;
                                letter-spacing: 0.02em;
                            }

                            .tax-summary-page .panel-subtitle {
                                font-size: 0.78rem !important;
                            }

                            .tax-summary-page .panel-body {
                                padding: 12px 14px !important;
                            }

                            .tax-summary-page .table {
                                font-size: 0.8rem !important;
                            }

                            .tax-summary-page .table thead th {
                                background: #eee !important;
                                border: 1px solid #333 !important;
                                font-weight: 700 !important;
                                text-transform: uppercase;
                                font-size: 0.72rem !important;
                            }

                            .tax-summary-page .table td {
                                border: 1px solid #ccc !important;
                            }

                            .tax-summary-page .summary-grid {
                                grid-template-columns: 1fr 1fr !important;
                                gap: 12px !important;
                            }

                            @page {
                                margin: 1.5cm 2cm;
                                size: A4 portrait;
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

                        /* Hero Banner */
                        .tax-summary-page .ts-hero {
                            display: flex;
                            align-items: center;
                            justify-content: space-between;
                            flex-wrap: wrap;
                            gap: 16px;
                            padding: 28px 24px;
                            margin: 24px 0 22px;
                            border-radius: 16px;
                            background: #6b21a8;
                            box-shadow: 0 8px 32px rgba(107, 33, 168, 0.25);
                            position: relative;
                            overflow: hidden;
                        }

                        .tax-summary-page .ts-hero::before {
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

                        .tax-summary-page .ts-hero::after {
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

                        .tax-summary-page .ts-hero__content {
                            position: relative;
                            z-index: 1;
                        }

                        .tax-summary-page .ts-hero__eyebrow {
                            display: inline-flex;
                            align-items: center;
                            gap: 6px;
                            margin-bottom: 8px;
                            color: rgba(255, 255, 255, 0.85);
                            font-size: 0.78rem;
                            font-weight: 600;
                            letter-spacing: 0.04em;
                        }

                        .tax-summary-page .ts-hero__eyebrow i {
                            font-size: 1rem;
                        }

                        .tax-summary-page .ts-hero__title {
                            margin: 0 0 4px 0;
                            color: #fff;
                            font-size: clamp(1.6rem, 2.5vw, 2.2rem);
                            font-weight: 800;
                            line-height: 1.15;
                            letter-spacing: -0.02em;
                        }

                        .tax-summary-page .ts-hero__subtitle {
                            margin: 0;
                            color: rgba(255, 255, 255, 0.8);
                            font-size: 0.88rem;
                            max-width: 480px;
                        }

                        .tax-summary-page .ts-hero__actions {
                            display: flex;
                            align-items: center;
                            flex-wrap: wrap;
                            gap: 10px;
                            position: relative;
                            z-index: 1;
                        }

                        .tax-summary-page .ts-hero-btn {
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

                        .tax-summary-page .ts-hero-btn:hover,
                        .tax-summary-page .ts-hero-btn:focus {
                            background: rgba(255, 255, 255, 0.25);
                            border-color: rgba(255, 255, 255, 0.5);
                            color: #fff;
                            text-decoration: none;
                            transform: translateY(-1px);
                        }

                        .tax-summary-page .ts-hero-btn--solid {
                            border-color: rgba(255, 255, 255, 0.6);
                            background: rgba(255, 255, 255, 0.95);
                            color: #6b21a8;
                            font-weight: 700;
                        }

                        .tax-summary-page .ts-hero-btn--solid:hover,
                        .tax-summary-page .ts-hero-btn--solid:focus {
                            background: #fff;
                            color: #581c87;
                        }

                        /* Receipt shake animation */
                        .tax-summary-page .receipt-shake {
                            display: inline-block;
                            animation: receipt-shake 2.5s ease-in-out infinite;
                            transform-origin: center;
                        }

                        @keyframes receipt-shake {
                            0%, 75%, 100% { transform: rotate(0deg) translateY(0); }
                            10% { transform: rotate(-6deg) translateY(-3px); }
                            20% { transform: rotate(4deg) translateY(0); }
                            30% { transform: rotate(-3deg) translateY(-2px); }
                            40% { transform: rotate(0deg) translateY(0); }
                        }

                        /* Deep purple accent on cards */
                        .tax-summary-page .panel-card {
                            border-top: 3px solid #6b21a8;
                        }

                        .tax-summary-page .panel-header {
                            border-bottom: 2px solid #6b21a8;
                        }

                        .tax-summary-page .panel-title {
                            color: #6b21a8;
                        }

                        /* Responsive hero */
                        @media (max-width: 767px) {
                            .tax-summary-page .ts-hero,
                            .tax-summary-page .ts-hero__actions {
                                flex-direction: column;
                                align-items: stretch;
                            }

                            .tax-summary-page .ts-hero {
                                padding: 20px;
                            }

                            .tax-summary-page .ts-hero-btn {
                                flex: 1 1 auto;
                                justify-content: center;
                            }
                        }

                        /* Dedicated paged document. The dashboard remains unchanged on screen. */
                        .tax-print-document {
                            display: none;
                        }

                        @media print {
                            @page {
                                size: A4 landscape;
                                margin: 14mm 13mm 16mm;
                            }

                            .tax-summary-page > *:not(.tax-print-document) {
                                display: none !important;
                            }

                            .tax-summary-page .tax-print-document {
                                display: block !important;
                                color: #111 !important;
                                font-family: Arial, Helvetica, sans-serif !important;
                                font-size: 9pt;
                                line-height: 1.35;
                            }

                            .tax-print-document,
                            .tax-print-document * {
                                box-sizing: border-box;
                                color: #111 !important;
                                text-shadow: none !important;
                                box-shadow: none !important;
                            }

                            .tax-print-letterhead {
                                display: grid;
                                grid-template-columns: minmax(0, 1fr) auto;
                                gap: 18mm;
                                align-items: end;
                                padding-bottom: 4mm;
                                margin-bottom: 5mm;
                                border-bottom: 2px solid #111;
                            }

                            .tax-print-company {
                                margin: 0 0 1mm;
                                font-size: 17pt;
                                font-weight: 700;
                                letter-spacing: 0.02em;
                                text-transform: uppercase;
                            }

                            .tax-print-company-meta,
                            .tax-print-doc-meta {
                                font-size: 8.5pt;
                                line-height: 1.45;
                            }

                            .tax-print-doc-meta {
                                min-width: 58mm;
                            }

                            .tax-print-doc-meta div {
                                display: flex;
                                justify-content: space-between;
                                gap: 8mm;
                                padding: 0.5mm 0;
                                border-bottom: 1px solid #bbb;
                            }

                            .tax-print-doc-meta span:first-child {
                                font-weight: 700;
                                text-transform: uppercase;
                            }

                            .tax-print-report-title {
                                margin: 0;
                                text-align: center;
                                font-size: 15pt;
                                font-weight: 700;
                                letter-spacing: 0.08em;
                                text-transform: uppercase;
                            }

                            .tax-print-report-period {
                                margin: 1mm 0 5mm;
                                text-align: center;
                                font-size: 9pt;
                            }

                            .tax-print-summary {
                                width: 100%;
                                margin: 0 0 5mm;
                                border-collapse: collapse;
                                table-layout: fixed;
                            }

                            .tax-print-summary td {
                                width: 20%;
                                padding: 2.5mm 3mm;
                                border: 1px solid #777;
                                vertical-align: top;
                            }

                            .tax-print-summary-label {
                                display: block;
                                margin-bottom: 1mm;
                                font-size: 7.5pt;
                                font-weight: 700;
                                letter-spacing: 0.04em;
                                text-transform: uppercase;
                            }

                            .tax-print-summary-value {
                                display: block;
                                font-size: 11.5pt;
                                font-weight: 700;
                                font-variant-numeric: tabular-nums;
                            }

                            .tax-print-section {
                                margin-top: 5mm;
                            }

                            .tax-print-section--page {
                                break-before: page;
                                page-break-before: always;
                            }

                            .tax-print-section-title {
                                margin: 0 0 2mm;
                                padding-bottom: 1.5mm;
                                border-bottom: 1px solid #111;
                                font-size: 10.5pt;
                                font-weight: 700;
                                letter-spacing: 0.04em;
                                text-transform: uppercase;
                            }

                            .tax-print-note {
                                margin: -1mm 0 2.5mm;
                                font-size: 8pt;
                                color: #444 !important;
                            }

                            .tax-print-grid {
                                display: grid;
                                grid-template-columns: 1fr 1fr;
                                gap: 7mm;
                                align-items: start;
                            }

                            .tax-print-table {
                                width: 100%;
                                border-collapse: collapse;
                                table-layout: fixed;
                                font-size: 8pt;
                            }

                            .tax-print-table thead {
                                display: table-header-group;
                            }

                            .tax-print-table tfoot {
                                display: table-row-group;
                            }

                            .tax-print-table tr {
                                break-inside: avoid;
                                page-break-inside: avoid;
                            }

                            .tax-print-table th,
                            .tax-print-table td {
                                padding: 1.7mm 2mm;
                                border: 1px solid #999;
                                vertical-align: top;
                                overflow-wrap: anywhere;
                            }

                            .tax-print-table th {
                                background: #ececec !important;
                                font-size: 7.25pt;
                                font-weight: 700;
                                letter-spacing: 0.03em;
                                text-align: left;
                                text-transform: uppercase;
                            }

                            .tax-print-table .tax-print-num {
                                text-align: right;
                                white-space: nowrap;
                                font-variant-numeric: tabular-nums;
                            }

                            .tax-print-table tfoot td {
                                border-top: 1.5px solid #111;
                                background: #f5f5f5 !important;
                                font-weight: 700;
                            }

                            .tax-print-empty {
                                padding: 5mm !important;
                                text-align: center;
                            }

                            .tax-print-attestation {
                                display: grid;
                                grid-template-columns: 1.3fr 1fr 1fr;
                                gap: 12mm;
                                margin-top: 4mm;
                                break-inside: avoid;
                                page-break-inside: avoid;
                            }

                            .tax-print-attestation-note {
                                font-size: 8pt;
                                color: #444 !important;
                            }

                            .tax-print-signature {
                                padding-top: 5mm;
                                border-top: 1px solid #111;
                                text-align: center;
                                font-size: 8pt;
                            }

                            .tax-print-footer-note {
                                margin-top: 3mm;
                                padding-top: 1mm;
                                border-top: 1px solid #aaa;
                                text-align: center;
                                font-size: 7.25pt;
                                color: #555 !important;
                            }
                        }
                    </style>

                    <section class="tax-print-document" aria-label="Printable tax summary report">
                        <header class="tax-print-letterhead">
                            <div>
                                <h1 class="tax-print-company"><?= htmlspecialchars($businessName !== '' ? $businessName : 'BERPS', ENT_QUOTES, 'UTF-8'); ?></h1>
                                <div class="tax-print-company-meta">
                                    <?php if ($businessAddress !== ''): ?>
                                        <div><?= htmlspecialchars($businessAddress, ENT_QUOTES, 'UTF-8'); ?></div>
                                    <?php endif; ?>
                                    <?php if ($businessTin !== ''): ?>
                                        <div>Taxpayer Identification No.: <?= htmlspecialchars($businessTin, ENT_QUOTES, 'UTF-8'); ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="tax-print-doc-meta">
                                <div><span>Document</span><strong>TAX-SUMMARY</strong></div>
                                <div><span>Prepared</span><strong><?= htmlspecialchars($generatedAt, ENT_QUOTES, 'UTF-8'); ?></strong></div>
                            </div>
                        </header>

                        <h2 class="tax-print-report-title">Tax Summary Report</h2>
                        <p class="tax-print-report-period">Reporting period: <strong><?= htmlspecialchars($rangeLabel, ENT_QUOTES, 'UTF-8'); ?></strong></p>

                        <table class="tax-print-summary" aria-label="Tax report totals">
                            <tbody>
                                <tr>
                                    <td><span class="tax-print-summary-label">Cash Received</span><span class="tax-print-summary-value">PHP <?= number_format($totalCash, 2); ?></span></td>
                                    <td><span class="tax-print-summary-label">BIR 2307 Tax Credit</span><span class="tax-print-summary-value">PHP <?= number_format($totalTax, 2); ?></span></td>
                                    <td><span class="tax-print-summary-label">Gross Settlement</span><span class="tax-print-summary-value">PHP <?= number_format($totalGross, 2); ?></span></td>
                                    <td><span class="tax-print-summary-label">Taxed Payments</span><span class="tax-print-summary-value"><?= number_format($entryCount); ?></span></td>
                                    <td><span class="tax-print-summary-label">Clients</span><span class="tax-print-summary-value"><?= number_format($uniqueClients); ?></span></td>
                                </tr>
                            </tbody>
                        </table>

                        <div class="tax-print-grid">
                            <section class="tax-print-section">
                                <h3 class="tax-print-section-title">Schedule A — Summary by Client</h3>
                                <table class="tax-print-table">
                                    <thead>
                                        <tr>
                                            <th style="width: 36%;">Client</th>
                                            <th class="tax-print-num" style="width: 12%;">Entries</th>
                                            <th class="tax-print-num">Cash</th>
                                            <th class="tax-print-num">Tax Credit</th>
                                            <th class="tax-print-num">Gross</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($customerSummaries)): ?>
                                            <?php foreach ($customerSummaries as $summary): ?>
                                                <tr>
                                                    <td><?= htmlspecialchars((string) ($summary['label'] ?? 'Unknown Customer'), ENT_QUOTES, 'UTF-8'); ?></td>
                                                    <td class="tax-print-num"><?= number_format((int) ($summary['entryCount'] ?? 0)); ?></td>
                                                    <td class="tax-print-num"><?= number_format((float) ($summary['cashAmount'] ?? 0), 2); ?></td>
                                                    <td class="tax-print-num"><?= number_format((float) ($summary['taxAmount'] ?? 0), 2); ?></td>
                                                    <td class="tax-print-num"><?= number_format((float) ($summary['grossAmount'] ?? 0), 2); ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr><td colspan="5" class="tax-print-empty">No tax-bearing payments were recorded for this period.</td></tr>
                                        <?php endif; ?>
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td>Report Total</td>
                                            <td class="tax-print-num"><?= number_format($entryCount); ?></td>
                                            <td class="tax-print-num"><?= number_format($totalCash, 2); ?></td>
                                            <td class="tax-print-num"><?= number_format($totalTax, 2); ?></td>
                                            <td class="tax-print-num"><?= number_format($totalGross, 2); ?></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </section>

                            <section class="tax-print-section">
                                <h3 class="tax-print-section-title">Schedule B — Summary by Month</h3>
                                <table class="tax-print-table">
                                    <thead>
                                        <tr>
                                            <th style="width: 36%;">Month</th>
                                            <th class="tax-print-num" style="width: 12%;">Entries</th>
                                            <th class="tax-print-num">Cash</th>
                                            <th class="tax-print-num">Tax Credit</th>
                                            <th class="tax-print-num">Gross</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($monthSummaries)): ?>
                                            <?php foreach ($monthSummaries as $summary): ?>
                                                <tr>
                                                    <td><?= htmlspecialchars((string) ($summary['periodLabel'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></td>
                                                    <td class="tax-print-num"><?= number_format((int) ($summary['entryCount'] ?? 0)); ?></td>
                                                    <td class="tax-print-num"><?= number_format((float) ($summary['cashAmount'] ?? 0), 2); ?></td>
                                                    <td class="tax-print-num"><?= number_format((float) ($summary['taxAmount'] ?? 0), 2); ?></td>
                                                    <td class="tax-print-num"><?= number_format((float) ($summary['grossAmount'] ?? 0), 2); ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr><td colspan="5" class="tax-print-empty">No monthly tax summary is available for this period.</td></tr>
                                        <?php endif; ?>
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td>Report Total</td>
                                            <td class="tax-print-num"><?= number_format($entryCount); ?></td>
                                            <td class="tax-print-num"><?= number_format($totalCash, 2); ?></td>
                                            <td class="tax-print-num"><?= number_format($totalTax, 2); ?></td>
                                            <td class="tax-print-num"><?= number_format($totalGross, 2); ?></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </section>
                        </div>

                        <div class="tax-print-attestation">
                            <div class="tax-print-attestation-note">I certify that this report was generated from the payment records available in BERPS for the reporting period shown above.</div>
                            <div class="tax-print-signature">Prepared by / Date</div>
                            <div class="tax-print-signature">Reviewed by / Date</div>
                        </div>

                        <section class="tax-print-section tax-print-section--page">
                            <h3 class="tax-print-section-title">Schedule C — Tax-Bearing Payment Register</h3>
                            <p class="tax-print-note">Detailed schedule supporting the report totals. Amounts are stated in Philippine pesos.</p>
                            <table class="tax-print-table">
                                <thead>
                                    <tr>
                                        <th style="width: 8%;">Date</th>
                                        <th style="width: 8%;">Invoice</th>
                                        <th style="width: 20%;">Client</th>
                                        <th style="width: 10%;">Source</th>
                                        <th style="width: 18%;">Reference / OR No.</th>
                                        <th class="tax-print-num" style="width: 12%;">Cash</th>
                                        <th class="tax-print-num" style="width: 12%;">Tax Credit</th>
                                        <th class="tax-print-num" style="width: 12%;">Gross</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($entries)): ?>
                                        <?php foreach ($entries as $row): ?>
                                            <?php
                                            $detailCash = (float) ($row->AmountPaid ?? 0);
                                            $detailTax = (float) ($row->TaxAmount ?? 0);
                                            $detailGross = (float) ($row->GrossAmountPaid ?? ($detailCash + $detailTax));
                                            $detailReference = trim((string) ($row->PaymentReference ?? $row->paymentReference ?? ''));
                                            $detailOrNo = trim((string) ($row->ORNo ?? $row->orNo ?? ''));
                                            $detailRefs = array_values(array_filter(array($detailReference, $detailOrNo), function ($value) { return $value !== ''; }));
                                            ?>
                                            <tr>
                                                <td><?= htmlspecialchars((string) ($row->PDate ?? '-'), ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td><?= htmlspecialchars((string) ($row->InvoiceNo ?? '-'), ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td><?= htmlspecialchars(trim((string) ($row->Customer ?? '')) !== '' ? (string) $row->Customer : 'Unknown Customer', ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td><?= htmlspecialchars(trim((string) ($row->PaymentSource ?? '')) !== '' ? (string) $row->PaymentSource : '-', ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td><?= htmlspecialchars(!empty($detailRefs) ? implode(' / ', $detailRefs) : '-', ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td class="tax-print-num"><?= number_format($detailCash, 2); ?></td>
                                                <td class="tax-print-num"><?= number_format($detailTax, 2); ?></td>
                                                <td class="tax-print-num"><?= number_format($detailGross, 2); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr><td colspan="8" class="tax-print-empty">No tax-bearing payments were recorded for this period.</td></tr>
                                    <?php endif; ?>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="5">Grand Total</td>
                                        <td class="tax-print-num"><?= number_format($totalCash, 2); ?></td>
                                        <td class="tax-print-num"><?= number_format($totalTax, 2); ?></td>
                                        <td class="tax-print-num"><?= number_format($totalGross, 2); ?></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </section>

                    </section>

                    <div class="ts-print-header">
                        <div class="ts-print-title"><?= htmlspecialchars($businessName !== '' ? $businessName : 'BERPS', ENT_QUOTES, 'UTF-8'); ?></div>
                        <?php if ($businessAddress !== ''): ?>
                            <div class="ts-print-meta"><?= htmlspecialchars($businessAddress, ENT_QUOTES, 'UTF-8'); ?></div>
                        <?php endif; ?>
                        <?php if ($businessTin !== ''): ?>
                            <div class="ts-print-meta">TIN: <?= htmlspecialchars($businessTin, ENT_QUOTES, 'UTF-8'); ?></div>
                        <?php endif; ?>
                        <hr class="ts-print-divider">
                        <div class="ts-print-subtitle">Tax Summary Report</div>
                        <div class="ts-print-meta">
                            Coverage: <?= htmlspecialchars($rangeLabel, ENT_QUOTES, 'UTF-8'); ?><br>
                            Generated: <?= htmlspecialchars($generatedAt, ENT_QUOTES, 'UTF-8'); ?>
                        </div>
                    </div>

                    <div class="ts-hero">
                        <div class="ts-hero__content">
                            <div class="ts-hero__eyebrow">
                                <i class="mdi mdi-receipt-text-outline"></i>
                                Tax Reporting
                            </div>
                            <h1 class="ts-hero__title">Tax Summary Report <span class="receipt-shake">🧾</span></h1>
                            <p class="ts-hero__subtitle">Review all payments with BIR Form 2307 tax, grouped by client and by month.</p>
                        </div>
                        <div class="ts-hero__actions">
                            <button type="button" class="ts-hero-btn" data-toggle="modal" data-target="#filterModal">
                                <i class="mdi mdi-filter-variant"></i>
                                <span>Filter</span>
                            </button>
                            <a class="ts-hero-btn" href="<?= htmlspecialchars(base_url() . 'Page/paymentList', ENT_QUOTES, 'UTF-8'); ?>">
                                <i class="mdi mdi-arrow-left"></i>
                                <span>Back to Payments</span>
                            </a>
                            <a class="ts-hero-btn" href="<?= htmlspecialchars($printUrl, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener noreferrer">
                                <i class="mdi mdi-printer"></i>
                                <span>Print</span>
                            </a>
                            <a class="ts-hero-btn ts-hero-btn--solid" href="<?= htmlspecialchars(base_url() . 'Page/taxSummaryReport', ENT_QUOTES, 'UTF-8'); ?>">
                                <i class="mdi mdi-refresh"></i>
                                <span>Reset</span>
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

                    <div class="ts-print-footer">
                        This document is computer-generated from BERPS. <?= htmlspecialchars($businessName, ENT_QUOTES, 'UTF-8'); ?> &middot; <?= htmlspecialchars($generatedAt, ENT_QUOTES, 'UTF-8'); ?><br>
                        Confidential &mdash; For internal use only.
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
