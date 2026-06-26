<?php
$payments = isset($payments) && is_array($payments) ? $payments : array();
$totalTax = 0;
$totalAmount = 0;
foreach ($payments as $payment) {
    $totalTax += (float) ($payment->TaxAmount ?? 0);
    $totalAmount += (float) ($payment->AmountPaid ?? 0);
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

                        .payments-tax-page .table td {
                            vertical-align: middle;
                            border-top: 1px solid #eef3f8;
                            color: var(--text);
                        }

                        .payments-tax-page .table tbody tr:hover {
                            background: rgba(37, 99, 235, 0.03);
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
                    </style>

                    <div class="isr-header">
                        <div>
                            <div class="isr-eyebrow">Tax Reporting</div>
                            <h1 class="isr-title">BIR Form 2307 Payments</h1>
                            <p class="isr-subtitle">
                                Review all payments with BIR Form 2307 tax credit attachments.
                            </p>
                        </div>

                        <div class="isr-actions">
                            <button type="button" class="btn-soft" data-toggle="modal" data-target="#filterModal">
                                <i class="fas fa-filter"></i>
                                Filter
                            </button>
                            <a class="btn-soft" href="<?= htmlspecialchars(base_url() . 'Page/paymentList', ENT_QUOTES, 'UTF-8'); ?>">
                                <i class="fas fa-arrow-left"></i>
                                Back to Payments
                            </a>
                            <button type="button" class="btn-soft" onclick="window.print()">
                                <i class="fas fa-print"></i>
                                Print Report
                            </button>
                            <a class="btn-solid" href="<?= htmlspecialchars(base_url() . 'Page/paymentsWithTax', ENT_QUOTES, 'UTF-8'); ?>">
                                <i class="fas fa-rotate-right"></i>
                                Reset
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
                                            <th>O.R. No</th>
                                            <th class="num-cell">Amount Paid</th>
                                            <th class="num-cell">Tax Credit</th>
                                            <th class="num-cell">Total</th>
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
                                                        <div class="line-main">#<?= htmlspecialchars((string) ($payment->paymentID ?? 'N/A'), ENT_QUOTES, 'UTF-8'); ?></div>
                                                    </td>
                                                    <td>
                                                        <?= !empty($payment->PDate) ? htmlspecialchars(date('M j, Y', strtotime((string) $payment->PDate)), ENT_QUOTES, 'UTF-8') : '<span class="text-muted">-</span>'; ?>
                                                    </td>
                                                    <td>
                                                        <a class="link-quiet" href="<?= htmlspecialchars($invoiceUrl, ENT_QUOTES, 'UTF-8'); ?>">
                                                            #<?= htmlspecialchars((string) ($payment->InvoiceNo ?? 'N/A'), ENT_QUOTES, 'UTF-8'); ?>
                                                        </a>
                                                    </td>
                                                    <td>
                                                        <div class="line-main"><?= htmlspecialchars((string) ($payment->Customer ?? 'N/A'), ENT_QUOTES, 'UTF-8'); ?></div>
                                                    </td>
                                                    <td><?= htmlspecialchars((string) ($payment->ORNo ?? 'N/A'), ENT_QUOTES, 'UTF-8'); ?></td>
                                                    <td class="num-cell"><?= number_format($amountPaid, 2); ?></td>
                                                    <td class="num-cell">
                                                        <span class="text-info font-weight-bold"><?= number_format($taxAmount, 2); ?></span>
                                                    </td>
                                                    <td class="num-cell font-weight-bold"><?= number_format($totalCredit, 2); ?></td>
                                                    <td><?= htmlspecialchars((string) ($payment->Cashier ?? 'N/A'), ENT_QUOTES, 'UTF-8'); ?></td>
                                                    <td class="text-center">
                                                        <?php if ($hasAttachment): ?>
                                                            <span class="chip is-success">
                                                                <i class="fa fa-check"></i> Available
                                                            </span>
                                                        <?php else: ?>
                                                            <span class="chip is-warning">
                                                                <i class="fa fa-exclamation"></i> Missing
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
                                                                    <i class="fa fa-eye"></i> View 2307
                                                                </a>
                                                            <?php else: ?>
                                                                <button class="table-btn" disabled title="No attachment available">
                                                                    <i class="fa fa-eye-slash"></i> No File
                                                                </button>
                                                            <?php endif; ?>

                                                            <a href="<?= htmlspecialchars($invoiceUrl, ENT_QUOTES, 'UTF-8'); ?>"
                                                               class="table-btn"
                                                               title="View Invoice">
                                                                <i class="fa fa-file-invoice"></i>
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
                    columnDefs: [{
                        targets: [5, 6, 7],
                        className: 'text-right'
                    }, {
                        targets: -1,
                        orderable: false,
                        searchable: false
                    }],
                    language: {
                        emptyTable: "No payments with BIR Form 2307 found.",
                        info: "Showing _START_ to _END_ of _TOTAL_ payments",
                        infoEmpty: "Showing 0 to 0 of 0 payments",
                        infoFiltered: "(filtered from _MAX_ total payments)"
                    }
                });
            } else {
                console.error('DataTables library is not loaded.');
            }
        });
    </script>

</body>
</html>