<?php
$payments = isset($payments) && is_array($payments) ? $payments : array();
$totalTax = 0;
$totalAmount = 0;
$attachmentCount = 0;
foreach ($payments as $payment) {
    $totalTax += (float) ($payment->TaxAmount ?? 0);
    $totalAmount += (float) ($payment->AmountPaid ?? 0);
    if (!empty($payment->attachment_path)) {
        $attachmentCount++;
    }
}
$totalGross = $totalAmount + $totalTax;
$businessData = isset($business) ? $business : null;
$businessName = trim((string) ($businessData->CompName ?? 'BERPS'));
$businessAddress = trim((string) ($businessData->CompAddress ?? ''));
$businessTin = trim((string) ($businessData->CompTin ?? ''));
$generatedAt = isset($generatedAt) && trim((string) $generatedAt) !== '' ? (string) $generatedAt : date('F j, Y h:i A');
$filterFrom = trim((string) ($from_date ?? ''));
$filterTo = trim((string) ($to_date ?? ''));

$rangeLabel = 'All recorded dates';
if ($filterFrom !== '' && $filterTo !== '') {
    $formattedFrom = strtotime($filterFrom) !== false ? date('F j, Y', strtotime($filterFrom)) : $filterFrom;
    $formattedTo = strtotime($filterTo) !== false ? date('F j, Y', strtotime($filterTo)) : $filterTo;
    $rangeLabel = $filterFrom === $filterTo ? $formattedFrom : $formattedFrom . ' to ' . $formattedTo;
} elseif ($filterFrom !== '') {
    $rangeLabel = 'From ' . (strtotime($filterFrom) !== false ? date('F j, Y', strtotime($filterFrom)) : $filterFrom);
} elseif ($filterTo !== '') {
    $rangeLabel = 'Through ' . (strtotime($filterTo) !== false ? date('F j, Y', strtotime($filterTo)) : $filterTo);
}
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
                <div class="container-fluid payments-tax-page">

                    <?php if ($this->session->flashdata('success')): ?>
                        <div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
                            <?= htmlspecialchars($this->session->flashdata('success'), ENT_QUOTES, 'UTF-8'); ?>
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    <?php endif; ?>

                    <?php if ($this->session->flashdata('error')): ?>
                        <div class="alert alert-danger alert-dismissible fade show mt-3" role="alert">
                            <?= htmlspecialchars($this->session->flashdata('error'), ENT_QUOTES, 'UTF-8'); ?>
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    <?php endif; ?>

                    <style>

                        .content-page {
                            margin-top: 0 !important;
                            overflow: visible !important;
                            height: auto !important;
                            min-height: auto !important;
                            padding: 10px 15px 65px 15px !important;
                        }

                        #wrapper {
                            overflow: visible !important;
                            height: auto !important;
                        }

                        body, html {
                            overflow: auto !important;
                        }

                        .payments-tax-page {
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

                        .payments-tax-page * {
                            box-sizing: border-box;
                        }

                        .payments-tax-page img {
                            max-width: 100%;
                            height: auto;
                            display: block;
                        }

                        .payments-tax-page .isr-header {
                            margin: 8px 0 22px;
                            display: flex;
                            justify-content: space-between;
                            align-items: flex-end;
                            gap: 16px;
                            flex-wrap: wrap;
                        }

                        .payments-tax-page .isr-eyebrow {
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

                        .payments-tax-page .isr-eyebrow::before {
                            content: '';
                            width: 8px;
                            height: 8px;
                            border-radius: 50%;
                            background: linear-gradient(135deg, var(--primary), var(--primary-2));
                            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.10);
                        }

                        .payments-tax-page .isr-title {
                            margin: 0;
                            font-family: var(--font-primary, Montserrat, Segoe UI, Arial, sans-serif);
                            font-size: 2rem;
                            font-weight: 800;
                            letter-spacing: -0.04em;
                            color: var(--text);
                            line-height: 1.1;
                        }

                        .payments-tax-page .isr-subtitle {
                            margin-top: 8px;
                            color: var(--text-soft);
                            font-size: 0.93rem;
                            font-weight: 500;
                        }

                        .payments-tax-page .isr-actions {
                            display: flex;
                            gap: 10px;
                            flex-wrap: wrap;
                            justify-content: flex-end;
                        }

                        .payments-tax-page .btn-soft,
                        .payments-tax-page .btn-solid {
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

                        .payments-tax-page .btn-soft {
                            background: rgba(255, 255, 255, 0.88);
                            color: var(--text);
                            border: 1px solid var(--line-strong);
                            box-shadow: var(--shadow-soft);
                        }

                        .payments-tax-page .btn-solid {
                            background: linear-gradient(135deg, var(--primary), var(--primary-2));
                            color: #fff;
                            border: none;
                            box-shadow: 0 10px 24px rgba(37, 99, 235, 0.22);
                        }

                        .payments-tax-page .btn-soft:hover,
                        .payments-tax-page .btn-solid:hover {
                            transform: translateY(-1px);
                            filter: brightness(1.02);
                            text-decoration: none;
                        }

                        .payments-tax-page .btn-solid:hover {
                            color: #fff;
                        }

                        .payments-tax-page .stat-strip {
                            display: grid;
                            grid-template-columns: repeat(3, minmax(0, 1fr));
                            gap: 16px;
                            margin-bottom: 20px;
                        }

                        .payments-tax-page .stat-card {
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

                        .payments-tax-page .stat-card::before {
                            content: '';
                            position: absolute;
                            inset: 0 0 auto 0;
                            height: 4px;
                        }

                        .payments-tax-page .stat-card.sc-count::before {
                            background: linear-gradient(90deg, #2563eb, #3b82f6);
                        }

                        .payments-tax-page .stat-card.sc-amount::before {
                            background: linear-gradient(90deg, #059669, #34d399);
                        }

                        .payments-tax-page .stat-card.sc-tax::before {
                            background: linear-gradient(90deg, #0ea5e9, #38bdf8);
                        }

                        .payments-tax-page .stat-label {
                            color: var(--text-faint);
                            font-size: 0.74rem;
                            font-weight: 700;
                            text-transform: uppercase;
                            letter-spacing: 0.08em;
                            margin-bottom: 12px;
                        }

                        .payments-tax-page .stat-value {
                            color: var(--text);
                            font-size: 2rem;
                            font-weight: 800;
                            line-height: 1;
                            letter-spacing: -0.04em;
                            margin-bottom: 8px;
                        }

                        .payments-tax-page .stat-meta {
                            color: var(--text-soft);
                            font-size: 0.82rem;
                            font-weight: 500;
                        }

                        .payments-tax-page .panel-card {
                            background: var(--surface);
                            border: 1px solid rgba(255, 255, 255, 0.72);
                            border-radius: var(--radius-xl);
                            box-shadow: var(--shadow-soft);
                            overflow: hidden;
                            margin-bottom: 20px;
                        }

                        .payments-tax-page .panel-header {
                            padding: 20px 24px;
                            border-bottom: 1px solid var(--line);
                            display: flex;
                            align-items: center;
                            justify-content: space-between;
                            gap: 14px;
                            flex-wrap: wrap;
                            background: linear-gradient(180deg, rgba(255, 255, 255, 0.94), rgba(249, 251, 255, 0.94));
                        }

                        .payments-tax-page .panel-body {
                            padding: 22px 24px;
                        }

                        .payments-tax-page .panel-title {
                            margin: 0;
                            color: var(--text);
                            font-size: 1.05rem;
                            font-weight: 800;
                            letter-spacing: -0.02em;
                        }

                        .payments-tax-page .panel-subtitle {
                            margin-top: 5px;
                            color: var(--text-soft);
                            font-size: 0.88rem;
                        }

                        .payments-tax-page .form-label {
                            color: var(--text);
                            font-size: 0.85rem;
                            font-weight: 700;
                            margin-bottom: 8px;
                        }

                        .payments-tax-page .form-control {
                            border-radius: 12px;
                            min-height: 46px;
                            border: 1px solid var(--line-strong);
                            box-shadow: none;
                        }

                        .payments-tax-page .form-control:focus {
                            border-color: rgba(37, 99, 235, 0.45);
                            box-shadow: 0 0 0 0.18rem rgba(37, 99, 235, 0.12);
                        }

                        .payments-tax-page .table {
                            color: var(--text);
                            margin-bottom: 0;
                        }

                        .payments-tax-page .table thead th {
                            border-top: 0;
                            border-bottom: 2px solid #0e7490;
                            color: #0e7490;
                            font-size: 0.72rem;
                            font-weight: 800;
                            text-transform: uppercase;
                            letter-spacing: 0.08em;
                            background: #f0fbfd;
                            padding: 14px 12px;
                            white-space: nowrap;
                        }

                        .payments-tax-page .table td {
                            vertical-align: middle;
                            border-top: 1px solid #eef3f8;
                            color: var(--text);
                            padding: 12px 12px;
                            font-size: 0.85rem;
                        }

                        .payments-tax-page .table tbody tr:hover {
                            background: rgba(14, 116, 144, 0.04);
                        }

                        .payments-tax-page .table tbody tr:nth-child(even) {
                            background: rgba(14, 116, 144, 0.015);
                        }

                        .payments-tax-page .table tbody tr:nth-child(even):hover {
                            background: rgba(14, 116, 144, 0.06);
                        }

                        .payments-tax-page .table-responsive {
                            border-radius: 18px;
                            overflow-x: auto;
                            border: 1px solid var(--line);
                            background: #fff;
                        }

                        .payments-tax-page .line-main {
                            font-weight: 700;
                            color: var(--text);
                            font-size: 0.86rem;
                        }

                        .payments-tax-page .line-sub {
                            font-size: 0.74rem;
                            color: var(--text-faint);
                            margin-top: 2px;
                        }

                        .payments-tax-page .pay-id {
                            display: inline-flex;
                            align-items: center;
                            gap: 4px;
                            padding: 3px 8px;
                            border-radius: 6px;
                            background: rgba(14, 116, 144, 0.08);
                            color: #0e7490;
                            font-size: 0.78rem;
                            font-weight: 700;
                            font-variant-numeric: tabular-nums;
                        }

                        .payments-tax-page .date-cell {
                            font-size: 0.83rem;
                            color: var(--text);
                            font-weight: 600;
                        }

                        .payments-tax-page .date-cell .date-sub {
                            display: block;
                            font-size: 0.72rem;
                            color: var(--text-faint);
                            font-weight: 400;
                        }

                        .payments-tax-page .amount-positive {
                            color: #0e7490;
                            font-weight: 700;
                        }

                        .payments-tax-page .amount-tax {
                            color: #0891b2;
                            font-weight: 700;
                        }

                        .payments-tax-page .amount-total {
                            color: var(--text);
                            font-weight: 800;
                            font-size: 0.88rem;
                        }

                        .payments-tax-page .source-badge {
                            display: inline-flex;
                            align-items: center;
                            gap: 4px;
                            padding: 2px 8px;
                            border-radius: 6px;
                            font-size: 0.72rem;
                            font-weight: 600;
                            background: rgba(14, 116, 144, 0.08);
                            color: #0e7490;
                            border: 1px solid rgba(14, 116, 144, 0.15);
                        }

                        .payments-tax-page .line-sub {
                            color: var(--text-soft);
                            font-size: 0.82rem;
                            margin-top: 3px;
                        }

                        .payments-tax-page .chip {
                            display: inline-flex;
                            align-items: center;
                            gap: 7px;
                            padding: 6px 10px;
                            border-radius: 999px;
                            font-size: 0.76rem;
                            font-weight: 800;
                            letter-spacing: 0.04em;
                            text-transform: uppercase;
                            border: 1px solid transparent;
                            white-space: nowrap;
                        }

                        .payments-tax-page .chip.is-success {
                            background: var(--success-soft);
                            color: var(--success);
                            border-color: #c7f0dd;
                        }

                        .payments-tax-page .chip.is-primary {
                            background: var(--primary-soft);
                            color: var(--primary-2);
                            border-color: #dbeafe;
                        }

                        .payments-tax-page .chip.is-danger {
                            background: var(--danger-soft);
                            color: var(--danger);
                            border-color: #fecdd3;
                        }

                        .payments-tax-page .chip.is-slate {
                            background: var(--slate-soft);
                            color: #475569;
                            border-color: #e2e8f0;
                        }

                        .payments-tax-page .chip.is-warning {
                            background: var(--warning-soft);
                            color: var(--warning);
                            border-color: #fed7aa;
                        }

                        .payments-tax-page .link-quiet {
                            color: var(--primary-2);
                            font-weight: 700;
                            text-decoration: none;
                        }

                        .payments-tax-page .link-quiet:hover {
                            color: var(--primary);
                            text-decoration: underline;
                        }

                        .payments-tax-page .num-cell {
                            text-align: right;
                            font-variant-numeric: tabular-nums;
                            white-space: nowrap;
                        }

                        .payments-tax-page .table-actions {
                            display: inline-flex;
                            gap: 8px;
                            flex-wrap: wrap;
                        }

                        .payments-tax-page .table-btn {
                            display: inline-flex;
                            align-items: center;
                            gap: 6px;
                            padding: 7px 10px;
                            border-radius: 10px;
                            border: 1px solid var(--line-strong);
                            background: #fff;
                            color: var(--text);
                            font-size: 0.8rem;
                            font-weight: 700;
                            text-decoration: none;
                        }

                        .payments-tax-page .table-btn:hover {
                            color: var(--primary);
                            border-color: #bfd3ef;
                            background: #f9fbff;
                            text-decoration: none;
                        }

                        .payments-tax-page .table-btn.btn-success {
                            background: var(--success-soft);
                            border-color: #c7f0dd;
                            color: var(--success);
                        }

                        .payments-tax-page .table-btn.btn-success:hover {
                            background: #d1fae5;
                            border-color: #a7f3d0;
                        }

                        .payments-tax-page .empty-state {
                            text-align: center;
                            color: var(--text-soft);
                            padding: 28px 0;
                        }

                        /* DataTables styling */
                        .payments-tax-page .dataTables_length select {
                            border: 1px solid var(--line-strong);
                            border-radius: 8px;
                            padding: 4px 8px;
                            font-size: 0.82rem;
                            margin: 0 4px;
                            color: var(--text);
                        }

                        .payments-tax-page .dataTables_filter input {
                            border: 1px solid var(--line-strong);
                            border-radius: 10px;
                            padding: 8px 14px;
                            font-size: 0.85rem;
                            min-width: 220px;
                            color: var(--text);
                            box-shadow: var(--shadow-soft);
                            transition: border-color 0.18s ease, box-shadow 0.18s ease;
                        }

                        .payments-tax-page .dataTables_filter input:focus {
                            border-color: #0e7490;
                            box-shadow: 0 0 0 0.18rem rgba(14, 116, 144, 0.12);
                            outline: none;
                        }

                        .payments-tax-page .dataTables_info {
                            color: var(--text-soft);
                            font-size: 0.82rem;
                            padding-top: 14px;
                        }

                        .payments-tax-page .dataTables_paginate .paginate_button {
                            border-radius: 8px !important;
                            border: 1px solid var(--line) !important;
                            background: #fff !important;
                            color: var(--text) !important;
                            margin: 0 3px;
                            padding: 6px 12px;
                            font-size: 0.82rem;
                            transition: all 0.16s ease;
                        }

                        .payments-tax-page .dataTables_paginate .paginate_button:hover {
                            background: rgba(14, 116, 144, 0.08) !important;
                            border-color: rgba(14, 116, 144, 0.3) !important;
                            color: #0e7490 !important;
                        }

                        .payments-tax-page .dataTables_paginate .paginate_button.current {
                            background: #0e7490 !important;
                            border-color: #0e7490 !important;
                            color: #fff !important;
                        }

                        .payments-tax-page .dataTables_paginate .paginate_button.current:hover {
                            background: #0b5d72 !important;
                            border-color: #0b5d72 !important;
                            color: #fff !important;
                        }

                        .payments-tax-page .dataTables_paginate .paginate_button.disabled {
                            opacity: 0.4;
                            cursor: not-allowed;
                        }

                        @media (max-width: 1199px) {
                            .payments-tax-page .stat-strip {
                                grid-template-columns: repeat(2, minmax(0, 1fr));
                            }
                        }

                        @media (max-width: 991px) {
                            .content-page {
                                padding-top: 65px;
                            }
                        }

                        @media (max-width: 767px) {
                            .content-page {
                                padding-top: 60px;
                            }

                            .payments-tax-page {
                                padding-top: 4px;
                            }

                            .payments-tax-page .stat-strip {
                                grid-template-columns: 1fr;
                            }

                            .payments-tax-page .panel-body,
                            .payments-tax-page .panel-header {
                                padding-left: 16px;
                                padding-right: 16px;
                            }

                            .payments-tax-page .isr-title {
                                font-size: 1.72rem;
                            }

                            .payments-tax-page .isr-actions {
                                width: 100%;
                            }

                            .payments-tax-page .isr-actions .btn-soft,
                            .payments-tax-page .isr-actions .btn-solid {
                                flex: 1 1 auto;
                                justify-content: center;
                            }

                            .payments-tax-page .table-responsive {
                                overflow-x: auto;
                                -webkit-overflow-scrolling: touch;
                            }

                            .payments-tax-page .table {
                                font-size: 0.85rem;
                            }

                            .payments-tax-page .table thead th,
                            .payments-tax-page .table td {
                                padding: 10px 8px;
                            }

                            .payments-tax-page .table-actions {
                                flex-direction: column;
                                gap: 4px;
                            }

                            .payments-tax-page .table-btn {
                                width: 100%;
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
                            .isr-header,
                            .isr-actions,
                            .pt-hero,
                            .pt-hero__actions,
                            .dataTables_length,
                            .dataTables_filter,
                            .dataTables_info,
                            .dataTables_paginate,
                            .dt-buttons,
                            .theme-settings,
                            .right-bar,
                            .button-menu-mobile,
                            #filterModal {
                                display: none !important;
                            }

                            .payments-tax-page {
                                background: #ffffff !important;
                                padding: 0 !important;
                                min-height: 0 !important;
                                color: #000000 !important;
                            }

                            .payments-tax-page .stat-strip {
                                gap: 10px;
                            }

                            .payments-tax-page .stat-card,
                            .payments-tax-page .panel-card {
                                background: #ffffff !important;
                                border: 1px solid #d1d5db !important;
                                box-shadow: none !important;
                                break-inside: avoid;
                                page-break-inside: avoid;
                            }

                            .payments-tax-page .panel-header {
                                background: #ffffff !important;
                                border-bottom: 1px solid #d1d5db !important;
                            }

                            .payments-tax-page .table-responsive {
                                border: 1px solid #d1d5db !important;
                                background: #ffffff !important;
                            }

                            .payments-tax-page .table thead th,
                            .payments-tax-page .table td,
                            .payments-tax-page .panel-title,
                            .payments-tax-page .panel-subtitle,
                            .payments-tax-page .stat-label,
                            .payments-tax-page .stat-value,
                            .payments-tax-page .stat-meta,
                            .payments-tax-page .line-main,
                            .payments-tax-page .link-quiet,
                            .payments-tax-page .chip,
                            .payments-tax-page .empty-state {
                                color: #000000 !important;
                            }

                            .payments-tax-page .table thead th {
                                background: #f3f4f6 !important;
                            }

                            .payments-tax-page .chip {
                                background: #ffffff !important;
                                border: 1px solid #d1d5db !important;
                            }

                            a[href]:after {
                                content: none !important;
                            }

                            #payments-tax-table {
                                page-break-inside: auto;
                            }

                            #payments-tax-table tr {
                                page-break-inside: avoid;
                                page-break-after: auto;
                            }
                        }

                        /* Hero Banner */
                        .payments-tax-page .pt-hero {
                            display: flex;
                            align-items: center;
                            justify-content: space-between;
                            flex-wrap: wrap;
                            gap: 16px;
                            padding: 28px 24px;
                            margin: 8px 0 22px;
                            border-radius: 16px;
                            background: #0e7490;
                            box-shadow: 0 8px 32px rgba(14, 116, 144, 0.25);
                            position: relative;
                            overflow: hidden;
                        }

                        .payments-tax-page .pt-hero::before {
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

                        .payments-tax-page .pt-hero::after {
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

                        .payments-tax-page .pt-hero__content {
                            position: relative;
                            z-index: 1;
                        }

                        .payments-tax-page .pt-hero__eyebrow {
                            display: inline-flex;
                            align-items: center;
                            gap: 6px;
                            margin-bottom: 8px;
                            color: rgba(255, 255, 255, 0.85);
                            font-size: 0.78rem;
                            font-weight: 600;
                            letter-spacing: 0.04em;
                        }

                        .payments-tax-page .pt-hero__eyebrow i {
                            font-size: 1rem;
                        }

                        .payments-tax-page .pt-hero__title {
                            margin: 0 0 4px 0;
                            color: #fff;
                            font-size: clamp(1.6rem, 2.5vw, 2.2rem);
                            font-weight: 800;
                            line-height: 1.15;
                            letter-spacing: -0.02em;
                        }

                        .payments-tax-page .pt-hero__subtitle {
                            margin: 0;
                            color: rgba(255, 255, 255, 0.8);
                            font-size: 0.88rem;
                            max-width: 480px;
                        }

                        .payments-tax-page .pt-hero__actions {
                            display: flex;
                            align-items: center;
                            flex-wrap: wrap;
                            gap: 10px;
                            position: relative;
                            z-index: 1;
                        }

                        .payments-tax-page .pt-hero-btn {
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

                        .payments-tax-page .pt-hero-btn:hover,
                        .payments-tax-page .pt-hero-btn:focus {
                            background: rgba(255, 255, 255, 0.25);
                            border-color: rgba(255, 255, 255, 0.5);
                            color: #fff;
                            text-decoration: none;
                            transform: translateY(-1px);
                        }

                        .payments-tax-page .pt-hero-btn--solid {
                            border-color: rgba(255, 255, 255, 0.6);
                            background: rgba(255, 255, 255, 0.95);
                            color: #0e7490;
                            font-weight: 700;
                        }

                        .payments-tax-page .pt-hero-btn--solid:hover,
                        .payments-tax-page .pt-hero-btn--solid:focus {
                            background: #fff;
                            color: #0b5d72;
                        }

                        /* Money bag bounce animation */
                        .payments-tax-page .money-bag {
                            display: inline-block;
                            animation: money-bag 2s ease-in-out infinite;
                        }

                        @keyframes money-bag {
                            0%, 70%, 100% { transform: translateY(0) scale(1); }
                            15% { transform: translateY(-10px) scale(1.15); }
                            30% { transform: translateY(0) scale(1); }
                            45% { transform: translateY(-4px) scale(1.08); }
                            60% { transform: translateY(0) scale(1); }
                        }

                        /* Teal-cyan accent on cards */
                        .payments-tax-page .panel-card {
                            border-top: 3px solid #0e7490;
                        }

                        .payments-tax-page .panel-header {
                            border-bottom: 2px solid #0e7490;
                        }

                        .payments-tax-page .panel-title {
                            color: #0e7490;
                        }

                        /* Responsive hero */
                        @media (max-width: 767px) {
                            .payments-tax-page .pt-hero,
                            .payments-tax-page .pt-hero__actions {
                                flex-direction: column;
                                align-items: stretch;
                            }

                            .payments-tax-page .pt-hero {
                                padding: 20px;
                            }

                            .payments-tax-page .pt-hero-btn {
                                flex: 1 1 auto;
                                justify-content: center;
                            }
                        }

                        /* Dedicated paged document. The dashboard remains unchanged on screen. */
                        .payments-tax-print-document {
                            display: none;
                        }

                        @media print {
                            @page {
                                size: A4 landscape;
                                margin: 14mm 13mm 16mm;
                            }

                            html,
                            body,
                            #wrapper,
                            .content-page,
                            .content,
                            .container-fluid {
                                margin: 0 !important;
                                padding: 0 !important;
                                background: #fff !important;
                            }

                            .navbar-custom,
                            .left-side-menu,
                            .footer,
                            .theme-settings,
                            .right-bar,
                            .button-menu-mobile {
                                display: none !important;
                            }

                            .payments-tax-page > *:not(.payments-tax-print-document) {
                                display: none !important;
                            }

                            .payments-tax-page .payments-tax-print-document {
                                display: block !important;
                                color: #111 !important;
                                font-family: Arial, Helvetica, sans-serif !important;
                                font-size: 9pt;
                                line-height: 1.35;
                            }

                            .payments-tax-print-document,
                            .payments-tax-print-document * {
                                box-sizing: border-box;
                                color: #111 !important;
                                text-shadow: none !important;
                                box-shadow: none !important;
                            }

                            .payments-tax-print-letterhead {
                                display: grid;
                                grid-template-columns: minmax(0, 1fr) auto;
                                gap: 18mm;
                                align-items: end;
                                padding-bottom: 4mm;
                                margin-bottom: 5mm;
                                border-bottom: 2px solid #111;
                            }

                            .payments-tax-print-company {
                                margin: 0 0 1mm;
                                font-size: 17pt;
                                font-weight: 700;
                                letter-spacing: 0.02em;
                                text-transform: uppercase;
                            }

                            .payments-tax-print-company-meta,
                            .payments-tax-print-doc-meta {
                                font-size: 8.5pt;
                                line-height: 1.45;
                            }

                            .payments-tax-print-doc-meta {
                                min-width: 58mm;
                            }

                            .payments-tax-print-doc-meta div {
                                display: flex;
                                justify-content: space-between;
                                gap: 8mm;
                                padding: 0.5mm 0;
                                border-bottom: 1px solid #bbb;
                            }

                            .payments-tax-print-doc-meta span:first-child {
                                font-weight: 700;
                                text-transform: uppercase;
                            }

                            .payments-tax-print-title {
                                margin: 0;
                                text-align: center;
                                font-size: 15pt;
                                font-weight: 700;
                                letter-spacing: 0.08em;
                                text-transform: uppercase;
                            }

                            .payments-tax-print-period {
                                margin: 1mm 0 5mm;
                                text-align: center;
                                font-size: 9pt;
                            }

                            .payments-tax-print-summary {
                                width: 100%;
                                margin: 0 0 5mm;
                                border-collapse: collapse;
                                table-layout: fixed;
                            }

                            .payments-tax-print-summary td {
                                width: 20%;
                                padding: 2.5mm 3mm;
                                border: 1px solid #777;
                                vertical-align: top;
                            }

                            .payments-tax-print-summary-label {
                                display: block;
                                margin-bottom: 1mm;
                                font-size: 7.4pt;
                                font-weight: 700;
                                letter-spacing: 0.04em;
                                text-transform: uppercase;
                            }

                            .payments-tax-print-summary-value {
                                display: block;
                                font-size: 11pt;
                                font-weight: 700;
                                font-variant-numeric: tabular-nums;
                                white-space: nowrap;
                            }

                            .payments-tax-print-section-title {
                                margin: 0 0 1mm;
                                padding-bottom: 1.5mm;
                                border-bottom: 1px solid #111;
                                font-size: 10.5pt;
                                font-weight: 700;
                                letter-spacing: 0.04em;
                                text-transform: uppercase;
                            }

                            .payments-tax-print-note {
                                margin: 0 0 2.5mm;
                                font-size: 8pt;
                                color: #444 !important;
                            }

                            .payments-tax-print-table {
                                width: 100%;
                                border-collapse: collapse;
                                table-layout: fixed;
                                font-size: 7.5pt;
                            }

                            .payments-tax-print-table thead {
                                display: table-header-group;
                            }

                            .payments-tax-print-table tfoot {
                                display: table-row-group;
                            }

                            .payments-tax-print-table tr {
                                break-inside: avoid;
                                page-break-inside: avoid;
                            }

                            .payments-tax-print-table th,
                            .payments-tax-print-table td {
                                padding: 1.45mm 1.7mm;
                                border: 1px solid #999;
                                vertical-align: top;
                                overflow-wrap: anywhere;
                            }

                            .payments-tax-print-table th {
                                background: #ececec !important;
                                font-size: 7pt;
                                font-weight: 700;
                                letter-spacing: 0.025em;
                                text-align: left;
                                text-transform: uppercase;
                            }

                            .payments-tax-print-table .payments-tax-print-num {
                                text-align: right;
                                white-space: nowrap;
                                font-variant-numeric: tabular-nums;
                            }

                            .payments-tax-print-table tfoot td {
                                border-top: 2px double #111;
                                border-bottom: 2px double #111;
                                background: #f5f5f5 !important;
                                font-weight: 700;
                            }

                            .payments-tax-print-empty {
                                padding: 5mm !important;
                                text-align: center;
                            }

                            .payments-tax-print-attestation {
                                display: grid;
                                grid-template-columns: 1.3fr 1fr 1fr;
                                gap: 12mm;
                                margin-top: 5mm;
                                break-inside: avoid;
                                page-break-inside: avoid;
                            }

                            .payments-tax-print-attestation-note {
                                font-size: 8pt;
                                color: #444 !important;
                            }

                            .payments-tax-print-signature {
                                padding-top: 6mm;
                                border-top: 1px solid #111;
                                text-align: center;
                                font-size: 8pt;
                            }

                            .payments-tax-print-footer-note {
                                margin-top: 3mm;
                                padding-top: 1mm;
                                border-top: 1px solid #aaa;
                                text-align: center;
                                font-size: 7.25pt;
                                color: #555 !important;
                            }
                        }
                    </style>

                    <article class="payments-tax-print-document" aria-label="Printable BIR Form 2307 payment register">
                        <header class="payments-tax-print-letterhead">
                            <div>
                                <h1 class="payments-tax-print-company"><?= htmlspecialchars($businessName !== '' ? $businessName : 'BERPS', ENT_QUOTES, 'UTF-8'); ?></h1>
                                <div class="payments-tax-print-company-meta">
                                    <?php if ($businessAddress !== ''): ?>
                                        <div><?= htmlspecialchars($businessAddress, ENT_QUOTES, 'UTF-8'); ?></div>
                                    <?php endif; ?>
                                    <?php if ($businessTin !== ''): ?>
                                        <div>Taxpayer Identification No.: <?= htmlspecialchars($businessTin, ENT_QUOTES, 'UTF-8'); ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="payments-tax-print-doc-meta">
                                <div><span>Document</span><strong>BIR-2307-REGISTER</strong></div>
                                <div><span>Prepared</span><strong><?= htmlspecialchars($generatedAt, ENT_QUOTES, 'UTF-8'); ?></strong></div>
                            </div>
                        </header>

                        <h2 class="payments-tax-print-title">BIR Form 2307 Payment Register</h2>
                        <p class="payments-tax-print-period">Reporting period: <strong><?= htmlspecialchars($rangeLabel, ENT_QUOTES, 'UTF-8'); ?></strong></p>

                        <table class="payments-tax-print-summary" aria-label="BIR Form 2307 payment totals">
                            <tbody>
                                <tr>
                                    <td><span class="payments-tax-print-summary-label">Payments</span><span class="payments-tax-print-summary-value"><?= number_format(count($payments)); ?></span></td>
                                    <td><span class="payments-tax-print-summary-label">Cash Received</span><span class="payments-tax-print-summary-value">PHP <?= number_format($totalAmount, 2); ?></span></td>
                                    <td><span class="payments-tax-print-summary-label">Tax Credits</span><span class="payments-tax-print-summary-value">PHP <?= number_format($totalTax, 2); ?></span></td>
                                    <td><span class="payments-tax-print-summary-label">Gross Settlement</span><span class="payments-tax-print-summary-value">PHP <?= number_format($totalGross, 2); ?></span></td>
                                    <td><span class="payments-tax-print-summary-label">Attachments on File</span><span class="payments-tax-print-summary-value"><?= number_format($attachmentCount); ?> / <?= number_format(count($payments)); ?></span></td>
                                </tr>
                            </tbody>
                        </table>

                        <section>
                            <h3 class="payments-tax-print-section-title">Tax-Bearing Payment Register</h3>
                            <p class="payments-tax-print-note">Payment records with BIR Form 2307 tax credits. Amounts are stated in Philippine pesos.</p>
                            <table class="payments-tax-print-table">
                                <thead>
                                    <tr>
                                        <th style="width: 7%;">Payment ID</th>
                                        <th style="width: 8%;">Date</th>
                                        <th style="width: 7%;">Invoice</th>
                                        <th style="width: 17%;">Client</th>
                                        <th style="width: 14%;">O.R. No. / Source</th>
                                        <th class="payments-tax-print-num" style="width: 9%;">Cash</th>
                                        <th class="payments-tax-print-num" style="width: 9%;">Tax Credit</th>
                                        <th class="payments-tax-print-num" style="width: 9%;">Gross</th>
                                        <th style="width: 15%;">Cashier</th>
                                        <th style="width: 5%;">2307</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($payments)): ?>
                                        <?php foreach ($payments as $payment): ?>
                                            <?php
                                            $printAmountPaid = (float) ($payment->AmountPaid ?? 0);
                                            $printTaxAmount = (float) ($payment->TaxAmount ?? 0);
                                            $printGross = $printAmountPaid + $printTaxAmount;
                                            $printOrNo = trim((string) ($payment->ORNo ?? ''));
                                            $printSource = trim((string) ($payment->PaymentSource ?? ''));
                                            $printReferenceParts = array_values(array_filter(array($printOrNo, $printSource), function ($value) { return $value !== ''; }));
                                            ?>
                                            <tr>
                                                <td>#<?= htmlspecialchars((string) ($payment->paymentID ?? 'N/A'), ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td><?= !empty($payment->PDate) ? htmlspecialchars(date('M j, Y', strtotime((string) $payment->PDate)), ENT_QUOTES, 'UTF-8') : '-'; ?></td>
                                                <td><?= htmlspecialchars((string) ($payment->InvoiceNo ?? 'N/A'), ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td><?= htmlspecialchars((string) ($payment->Customer ?? 'N/A'), ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td><?= htmlspecialchars(!empty($printReferenceParts) ? implode(' / ', $printReferenceParts) : '-', ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td class="payments-tax-print-num"><?= number_format($printAmountPaid, 2); ?></td>
                                                <td class="payments-tax-print-num"><?= number_format($printTaxAmount, 2); ?></td>
                                                <td class="payments-tax-print-num"><?= number_format($printGross, 2); ?></td>
                                                <td><?= htmlspecialchars((string) ($payment->Cashier ?? 'N/A'), ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td><?= !empty($payment->attachment_path) ? 'On file' : 'Missing'; ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr><td colspan="10" class="payments-tax-print-empty">No payments with BIR Form 2307 were found for this reporting period.</td></tr>
                                    <?php endif; ?>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="5">Grand Total</td>
                                        <td class="payments-tax-print-num"><?= number_format($totalAmount, 2); ?></td>
                                        <td class="payments-tax-print-num"><?= number_format($totalTax, 2); ?></td>
                                        <td class="payments-tax-print-num"><?= number_format($totalGross, 2); ?></td>
                                        <td colspan="2"></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </section>

                        <div class="payments-tax-print-attestation">
                            <div class="payments-tax-print-attestation-note">I certify that this register was generated from the valid payment records available in BERPS for the reporting period shown above.</div>
                            <div class="payments-tax-print-signature">Prepared by / Date</div>
                            <div class="payments-tax-print-signature">Reviewed by / Date</div>
                        </div>
                        <div class="payments-tax-print-footer-note">Computer-generated report &middot; <?= htmlspecialchars($businessName !== '' ? $businessName : 'BERPS', ENT_QUOTES, 'UTF-8'); ?> &middot; Confidential</div>
                    </article>

                    <div class="pt-hero">
                        <div class="pt-hero__content">
                            <div class="pt-hero__eyebrow">
                                <i class="mdi mdi-file-document-multiple-outline"></i>
                                Tax Reporting
                            </div>
                            <h1 class="pt-hero__title">BIR Form 2307 Payments <span class="money-bag">💰</span></h1>
                            <p class="pt-hero__subtitle">Review all payments with BIR Form 2307 tax credit attachments.</p>
                        </div>
                        <div class="pt-hero__actions">
                            <button type="button" class="pt-hero-btn" data-toggle="modal" data-target="#filterModal">
                                <i class="mdi mdi-filter-variant"></i>
                                <span>Filter</span>
                            </button>
                            <a class="pt-hero-btn" href="<?= htmlspecialchars(base_url() . 'Page/paymentList', ENT_QUOTES, 'UTF-8'); ?>">
                                <i class="mdi mdi-arrow-left"></i>
                                <span>Back to Payments</span>
                            </a>
                            <button type="button" class="pt-hero-btn" onclick="window.print()">
                                <i class="mdi mdi-printer"></i>
                                <span>Print</span>
                            </button>
                            <a class="pt-hero-btn pt-hero-btn--solid" href="<?= htmlspecialchars(base_url() . 'Page/paymentsWithTax', ENT_QUOTES, 'UTF-8'); ?>">
                                <i class="mdi mdi-refresh"></i>
                                <span>Reset</span>
                            </a>
                        </div>
                    </div>

                    <div class="stat-strip">
                        <div class="stat-card sc-count">
                            <div class="stat-label">Total Payments</div>
                            <div class="stat-value"><?= number_format((int) count($payments)); ?></div>
                            <div class="stat-meta">With BIR Form 2307</div>
                        </div>
                        <div class="stat-card sc-amount">
                            <div class="stat-label">Amount Paid</div>
                            <div class="stat-value"><?= number_format((float) $totalAmount, 2); ?></div>
                            <div class="stat-meta">Cash payments received</div>
                        </div>
                        <div class="stat-card sc-tax">
                            <div class="stat-label">Tax Credit</div>
                            <div class="stat-value"><?= number_format((float) $totalTax, 2); ?></div>
                            <div class="stat-meta">BIR 2307 credits</div>
                        </div>
                    </div>

                    <div class="panel-card">
                        <div class="panel-header">
                            <div>
                                <h2 class="panel-title">Payment Records with BIR Form 2307</h2>
                                <p class="panel-subtitle">Showing <?= count($payments); ?> payment(s) with tax credit</p>
                            </div>
                        </div>
                        <div class="panel-body">
                            <div class="table-responsive">
                                <table id="payments-tax-table" class="table table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th>Payment ID</th>
                                            <th>Date</th>
                                            <th>Invoice</th>
                                            <th>Client</th>
                                            <th>O.R. No / Source</th>
                                            <th class="num-cell">Amount Paid</th>
                                            <th class="num-cell">Tax Credit</th>
                                            <th class="num-cell">Gross Total</th>
                                            <th>Cashier</th>
                                            <th>Attachment</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($payments)): ?>
                                            <?php foreach ($payments as $payment): ?>
                                                <?php
                                                $amountPaid = (float) ($payment->AmountPaid ?? 0);
                                                $taxAmount = (float) ($payment->TaxAmount ?? 0);
                                                $totalCredit = $amountPaid + $taxAmount;
                                                $hasAttachment = !empty($payment->attachment_path);
                                                $invoiceUrl = base_url() . 'Page/invoice?invoice_no=' . rawurlencode((string) ($payment->InvoiceNo ?? ''));
                                                ?>
                                                <tr>
                                                    <td>
                                                        <span class="pay-id">#<?= htmlspecialchars((string) ($payment->paymentID ?? 'N/A'), ENT_QUOTES, 'UTF-8'); ?></span>
                                                    </td>
                                                    <td>
                                                        <?php if (!empty($payment->PDate)): ?>
                                                            <div class="date-cell"><?= htmlspecialchars(date('M j, Y', strtotime((string) $payment->PDate)), ENT_QUOTES, 'UTF-8'); ?>
                                                                <span class="date-sub"><?= htmlspecialchars(date('D', strtotime((string) $payment->PDate)), ENT_QUOTES, 'UTF-8'); ?></span>
                                                            </div>
                                                        <?php else: ?>
                                                            <span class="text-muted">-</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <a class="link-quiet" href="<?= htmlspecialchars($invoiceUrl, ENT_QUOTES, 'UTF-8'); ?>">
                                                            #<?= htmlspecialchars((string) ($payment->InvoiceNo ?? 'N/A'), ENT_QUOTES, 'UTF-8'); ?>
                                                        </a>
                                                    </td>
                                                    <td>
                                                        <div class="line-main"><?= htmlspecialchars((string) ($payment->Customer ?? 'N/A'), ENT_QUOTES, 'UTF-8'); ?></div>
                                                    </td>
                                                    <td>
                                                        <div class="line-main"><?= htmlspecialchars((string) ($payment->ORNo ?? 'N/A'), ENT_QUOTES, 'UTF-8'); ?></div>
                                                        <?php if (!empty($payment->PaymentSource)): ?>
                                                            <div class="line-sub"><?= htmlspecialchars((string) $payment->PaymentSource, ENT_QUOTES, 'UTF-8'); ?></div>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td class="num-cell"><span class="amount-positive"><?= number_format($amountPaid, 2); ?></span></td>
                                                    <td class="num-cell"><span class="amount-tax"><?= number_format($taxAmount, 2); ?></span></td>
                                                    <td class="num-cell"><span class="amount-total"><?= number_format($totalCredit, 2); ?></span></td>
                                                    <td>
                                                        <div class="line-main"><?= htmlspecialchars((string) ($payment->Cashier ?? 'N/A'), ENT_QUOTES, 'UTF-8'); ?></div>
                                                    </td>
                                                    <td class="text-center">
                                                        <?php if ($hasAttachment): ?>
                                                            <span class="chip is-success">
                                                                <i class="mdi mdi-paperclip-check"></i> Available
                                                            </span>
                                                        <?php else: ?>
                                                            <span class="chip is-warning">
                                                                <i class="mdi mdi-paperclip-off"></i> Missing
                                                            </span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <div class="table-actions">
                                                            <?php if ($hasAttachment): ?>
                                                                <a href="<?= base_url(); ?>Page/viewBIRAttachment/<?= htmlspecialchars((string) ($payment->paymentID ?? ''), ENT_QUOTES, 'UTF-8'); ?>"
                                                                   class="table-btn btn-success"
                                                                   target="_blank"
                                                                   title="View BIR Form 2307">
                                                                    <i class="mdi mdi-eye-outline"></i> View 2307
                                                                </a>
                                                            <?php else: ?>
                                                                <button class="table-btn" disabled title="No attachment available">
                                                                    <i class="mdi mdi-eye-off-outline"></i> No File
                                                                </button>
                                                            <?php endif; ?>

                                                            <a href="<?= htmlspecialchars($invoiceUrl, ENT_QUOTES, 'UTF-8'); ?>"
                                                               class="table-btn"
                                                               title="View Invoice">
                                                                <i class="mdi mdi-file-document-outline"></i>
                                                            </a>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="11" class="empty-state">
                                                    <i class="fa fa-inbox fa-3x mb-3"></i>
                                                    <p>No payments with BIR Form 2307 found.</p>
                                                    <a href="<?= base_url(); ?>Page/paymentList" class="btn btn-primary">View All Payments</a>
                                                </td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Filter Modal -->
                    <div class="modal fade" id="filterModal" tabindex="-1" role="dialog" aria-labelledby="filterModalLabel" aria-hidden="true">
                        <div class="modal-dialog" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="filterModalLabel">
                                        <i class="fas fa-filter mr-2"></i>Filter Payments
                                    </h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <form method="get" action="<?= base_url(); ?>Page/paymentsWithTax">
                                    <div class="modal-body">
                                        <div class="form-group">
                                            <label for="filter-from" class="form-label">From Date</label>
                                            <input type="date" class="form-control" id="filter-from" name="from_date" value="<?= htmlspecialchars((string) ($from_date ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
                                        </div>
                                        <div class="form-group">
                                            <label for="filter-to" class="form-label">To Date</label>
                                            <input type="date" class="form-control" id="filter-to" name="to_date" value="<?= htmlspecialchars((string) ($to_date ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <a class="btn-soft" href="<?= htmlspecialchars(base_url() . 'Page/paymentsWithTax', ENT_QUOTES, 'UTF-8'); ?>">
                                            Clear
                                        </a>
                                        <button type="submit" class="btn-solid">
                                            Apply Filter
                                        </button>
                                    </div>
                                </form>
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
        $(document).ready(function() {
            if (typeof $.fn.DataTable !== 'undefined') {
                $('#payments-tax-table').DataTable({
                    responsive: true,
                    autoWidth: false,
                    stateSave: true,
                    pageLength: 25,
                    order: [[1, 'desc']],
                    dom: '<"row mb-3"<"col-sm-6"l><"col-sm-6 text-right"f>>rtip',
                    columnDefs: [{
                        targets: [5, 6, 7],
                        className: 'text-right'
                    }, {
                        targets: -1,
                        orderable: false,
                        searchable: false
                    }, {
                        targets: 9,
                        orderable: false
                    }],
                    language: {
                        search: "",
                        searchPlaceholder: "Search payments...",
                        emptyTable: '<div style="padding:20px;"><i class=\"mdi mdi-inbox-outline mdi-36px\"></i><p style=\"margin:8px 0 4px;font-weight:600;\">No payments with BIR Form 2307 found.</p><p style=\"font-size:0.8rem;color:#8ea0b5;\">Try adjusting your filter or add tax values to payments.</p></div>',
                        info: "Showing _START_ to _END_ of _TOTAL_ payments",
                        infoEmpty: "No payments to display",
                        infoFiltered: "(filtered from _MAX_ total)",
                        lengthMenu: "Show _MENU_",
                        paginate: {
                            first: '<i class="mdi mdi-chevron-double-left"></i>',
                            last: '<i class="mdi mdi-chevron-double-right"></i>',
                            next: '<i class="mdi mdi-chevron-right"></i>',
                            previous: '<i class="mdi mdi-chevron-left"></i>'
                        }
                    }
                });
            } else {
                console.error('DataTables library is not loaded.');
            }
        });
    </script>

</body>
</html>
