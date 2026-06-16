<?php
$invoiceData = isset($invoice) ? $invoice : null;
$payments = isset($data) && is_array($data) ? $data : array();
$primaryPayment = !empty($payments) ? $payments[0] : null;

$invoiceNo = trim((string) ($invoiceData->InvoiceNo ?? ($primaryPayment->InvoiceNo ?? '')));
$customerName = trim((string) ($invoiceData->Customer ?? ($primaryPayment->Customer ?? '')));
$customerAddress = trim((string) ($invoiceData->CustAddress ?? ''));
$invoiceSource = trim((string) ($invoiceData->invoiceSource ?? ($primaryPayment->PaymentSource ?? 'Invoice')));
$orderID = trim((string) ($invoiceData->orderID ?? ''));

$totalDue = (float) ($invoiceData->TotalDue ?? 0);
$amountPaid = (float) ($invoiceData->AmountPaid ?? 0);
$balance = (float) ($invoiceData->Balance ?? 0);

$recordedPaymentTotal = 0.0;
foreach ($payments as $paymentRow) {
    $recordedPaymentTotal += (float) ($paymentRow->GrossAmountPaid ?? ((float) ($paymentRow->AmountPaid ?? 0) + (float) ($paymentRow->TaxAmount ?? 0)));
}

if ($amountPaid <= 0 && $recordedPaymentTotal > 0) {
    $amountPaid = $recordedPaymentTotal;
}

if ($balance <= 0 && $totalDue > 0 && $amountPaid > 0) {
    $balance = max(0, $totalDue - $amountPaid);
}

$paymentCount = count($payments);
$latestPaymentRaw = !empty($payments) && !empty($payments[0]->PDate) ? trim((string) $payments[0]->PDate) : '';
$latestPaymentLabel = $latestPaymentRaw !== '' && $latestPaymentRaw !== '0000-00-00'
    ? date('M d, Y', strtotime($latestPaymentRaw))
    : 'No payments yet';

$clientMode = !empty($clientMode);
$customBackUrl = isset($backUrl) ? trim((string) $backUrl) : '';
$backUrl = $customBackUrl !== ''
    ? $customBackUrl
    : base_url() . 'Page/paymentList';
if ($customBackUrl === '' && $invoiceData) {
    $backUrl = base_url() . 'Page/' . (($invoiceSource === 'Job Order') ? 'joList' : 'invList');
}
$backLabel = isset($backLabel) && trim((string) $backLabel) !== ''
    ? (string) $backLabel
    : 'Back';

$invoiceUrl = '';
if ($orderID !== '') {
    $invoiceUrl = base_url() . 'Page/invoice?id=' . rawurlencode($orderID);
} elseif ($invoiceNo !== '') {
    $invoiceUrl = base_url() . 'Page/invoice?invoice_no=' . rawurlencode($invoiceNo);
}

$addPaymentUrl = '';
if ($orderID !== '' && $invoiceNo !== '') {
    $paymentSource = $invoiceSource !== '' ? $invoiceSource : 'Others';
    $addPaymentUrl = base_url() . 'Page/addPaymentJO?id=' . rawurlencode($orderID)
        . '&InvoiceNo=' . rawurlencode($invoiceNo)
        . '&PaymentSource=' . rawurlencode($paymentSource);
}

$pageTitle = $invoiceNo !== '' ? 'Invoice #' . $invoiceNo . ' Payment History' : 'Payment History';
$pageSubtitleParts = array();
if ($customerName !== '') {
    $pageSubtitleParts[] = $customerName;
}
if ($paymentCount > 0) {
    $pageSubtitleParts[] = $paymentCount . ' payment' . ($paymentCount === 1 ? '' : 's') . ' recorded';
}
$pageSubtitle = !empty($pageSubtitleParts) ? implode(' · ', $pageSubtitleParts) : 'Track all payments recorded for this invoice.';

$showAddPayment = !$clientMode && $addPaymentUrl !== '' && ($totalDue <= 0 || $balance > 0.00001);
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
                <div class="container-fluid payment-history-page">

                    <style>
                        .payment-history-page {
                            --bg: #f5f7fb;
                            --surface: rgba(255, 255, 255, 0.92);
                            --surface-2: #ffffff;
                            --line: #e7ecf3;
                            --line-strong: #d7e0ec;
                            --text: #122033;
                            --text-soft: #5e7188;
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
                            --shadow: 0 14px 40px rgba(15, 23, 42, 0.08);
                            --shadow-soft: 0 8px 24px rgba(15, 23, 42, 0.05);
                            --radius-xl: 22px;
                            --radius-lg: 16px;
                            --radius-md: 12px;
                            --radius-sm: 10px;
                            --font-body: 'Inter', 'Poppins', 'Segoe UI', Arial, sans-serif;
                            --font-head: 'Inter', 'Montserrat', 'Segoe UI', Arial, sans-serif;
                            --font-mono: 'SFMono-Regular', Consolas, 'Liberation Mono', monospace;
                            font-family: var(--font-body);
                            background:
                                radial-gradient(circle at top left, rgba(37, 99, 235, 0.08), transparent 28%),
                                radial-gradient(circle at top right, rgba(16, 185, 129, 0.08), transparent 24%),
                                linear-gradient(180deg, #f8fbff 0%, #f4f7fb 100%);
                            min-height: 100vh;
                            padding-bottom: 24px;
                        }

                        .payment-history-page * {
                            box-sizing: border-box;
                        }

                        .payment-history-page .ph-header {
                            margin: 24px 0 22px;
                            display: flex;
                            justify-content: space-between;
                            align-items: flex-end;
                            gap: 16px;
                            flex-wrap: wrap;
                        }

                        .payment-history-page .ph-eyebrow {
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

                        .payment-history-page .ph-eyebrow::before {
                            content: '';
                            width: 8px;
                            height: 8px;
                            border-radius: 50%;
                            background: linear-gradient(135deg, var(--primary), var(--primary-2));
                            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.10);
                        }

                        .payment-history-page .ph-title {
                            margin: 0;
                            font-family: var(--font-head);
                            font-size: 2rem;
                            font-weight: 800;
                            letter-spacing: -0.04em;
                            color: var(--text);
                            line-height: 1.1;
                        }

                        .payment-history-page .ph-subtitle {
                            margin-top: 8px;
                            color: var(--text-soft);
                            font-size: 0.93rem;
                            font-weight: 500;
                        }

                        .payment-history-page .ph-actions {
                            display: flex;
                            gap: 10px;
                            flex-wrap: wrap;
                            justify-content: flex-end;
                        }

                        .payment-history-page .btn-soft,
                        .payment-history-page .btn-solid {
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

                        .payment-history-page .btn-soft {
                            background: rgba(255, 255, 255, 0.88);
                            color: var(--text);
                            border: 1px solid var(--line-strong);
                            box-shadow: var(--shadow-soft);
                        }

                        .payment-history-page .btn-solid {
                            background: linear-gradient(135deg, var(--primary), var(--primary-2));
                            color: #fff;
                            border: none;
                            box-shadow: 0 10px 24px rgba(37, 99, 235, 0.22);
                        }

                        .payment-history-page .btn-soft:hover,
                        .payment-history-page .btn-solid:hover {
                            transform: translateY(-1px);
                            filter: brightness(1.02);
                            color: inherit;
                            text-decoration: none;
                        }

                        .payment-history-page .btn-solid:hover {
                            color: #fff;
                        }

                        .payment-history-page .stat-strip {
                            display: grid;
                            grid-template-columns: repeat(4, minmax(0, 1fr));
                            gap: 16px;
                            margin-bottom: 22px;
                        }

                        .payment-history-page .stat-card {
                            position: relative;
                            overflow: hidden;
                            background: var(--surface);
                            backdrop-filter: blur(12px);
                            border: 1px solid rgba(255, 255, 255, 0.7);
                            border-radius: var(--radius-xl);
                            box-shadow: var(--shadow-soft);
                            padding: 18px 20px 20px;
                            min-height: 118px;
                            transition: transform 0.18s ease, box-shadow 0.18s ease;
                        }

                        .payment-history-page .stat-card:hover {
                            transform: translateY(-3px);
                            box-shadow: var(--shadow);
                        }

                        .payment-history-page .stat-card::before {
                            content: '';
                            position: absolute;
                            inset: 0 0 auto 0;
                            height: 4px;
                        }

                        .payment-history-page .stat-card.sc-total::before {
                            background: linear-gradient(90deg, #3b82f6, #60a5fa);
                        }

                        .payment-history-page .stat-card.sc-paid::before {
                            background: linear-gradient(90deg, #10b981, #34d399);
                        }

                        .payment-history-page .stat-card.sc-balance::before {
                            background: linear-gradient(90deg, #f59e0b, #fbbf24);
                        }

                        .payment-history-page .stat-card.sc-latest::before {
                            background: linear-gradient(90deg, #f43f5e, #fb7185);
                        }

                        .payment-history-page .stat-label {
                            color: var(--text-faint);
                            font-size: 0.74rem;
                            font-weight: 700;
                            text-transform: uppercase;
                            letter-spacing: 0.08em;
                            margin-bottom: 12px;
                        }

                        .payment-history-page .stat-value {
                            color: var(--text);
                            font-size: 2rem;
                            font-weight: 800;
                            line-height: 1;
                            letter-spacing: -0.04em;
                            margin-bottom: 6px;
                        }

                        .payment-history-page .stat-meta {
                            color: var(--text-soft);
                            font-size: 0.82rem;
                            font-weight: 500;
                        }

                        .payment-history-page .ph-card {
                            background: var(--surface);
                            backdrop-filter: blur(12px);
                            border: 1px solid rgba(255, 255, 255, 0.72);
                            border-radius: 24px;
                            box-shadow: var(--shadow);
                            overflow: hidden;
                        }

                        .payment-history-page .ph-card-header {
                            padding: 20px 24px;
                            border-bottom: 1px solid var(--line);
                            display: flex;
                            align-items: center;
                            justify-content: space-between;
                            gap: 14px;
                            flex-wrap: wrap;
                            background: linear-gradient(180deg, rgba(255, 255, 255, 0.94), rgba(249, 251, 255, 0.94));
                        }

                        .payment-history-page .ph-card-title {
                            margin: 0;
                            color: var(--text);
                            font-size: 1.05rem;
                            font-weight: 800;
                            letter-spacing: -0.02em;
                        }

                        .payment-history-page .ph-card-subtitle {
                            margin-top: 5px;
                            color: var(--text-soft);
                            font-size: 0.88rem;
                        }

                        .payment-history-page .header-badges {
                            display: flex;
                            flex-wrap: wrap;
                            gap: 8px;
                        }

                        .payment-history-page .header-badge {
                            display: inline-flex;
                            align-items: center;
                            gap: 6px;
                            padding: 7px 11px;
                            border-radius: 999px;
                            background: var(--primary-soft);
                            color: var(--primary-2);
                            font-size: 0.78rem;
                            font-weight: 700;
                            letter-spacing: 0.01em;
                        }

                        .payment-history-page .ph-card-body {
                            padding: 22px 24px 24px;
                        }

                        .payment-history-page .context-grid {
                            display: grid;
                            grid-template-columns: minmax(0, 1.15fr) minmax(280px, 0.85fr);
                            gap: 18px;
                            margin-bottom: 22px;
                        }

                        .payment-history-page .context-panel {
                            background: #fff;
                            border: 1px solid var(--line);
                            border-radius: 18px;
                            padding: 18px 18px 16px;
                            box-shadow: 0 10px 26px rgba(15, 23, 42, 0.04);
                        }

                        .payment-history-page .context-label {
                            color: var(--text-faint);
                            font-size: 0.72rem;
                            font-weight: 800;
                            letter-spacing: 0.08em;
                            text-transform: uppercase;
                            margin-bottom: 10px;
                        }

                        .payment-history-page .context-value {
                            color: var(--text);
                            font-size: 1.15rem;
                            font-weight: 700;
                            line-height: 1.35;
                            margin-bottom: 6px;
                        }

                        .payment-history-page .context-help {
                            color: var(--text-soft);
                            font-size: 0.88rem;
                            line-height: 1.55;
                        }

                        .payment-history-page .context-mono {
                            font-family: var(--font-mono);
                            font-size: 0.9rem;
                            color: var(--text-soft);
                        }

                        .payment-history-page .data-table-container {
                            position: relative;
                        }

                        .payment-history-page .data-table-container.loading::after {
                            content: 'Loading payment history...';
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

                        .payment-history-page .data-table-container.loading::before {
                            content: '';
                            position: absolute;
                            top: 50%;
                            left: 50%;
                            width: 26px;
                            height: 26px;
                            margin: -40px 0 0 -13px;
                            border-radius: 50%;
                            border: 3px solid rgba(108, 117, 125, 0.3);
                            border-top-color: rgba(108, 117, 125, 0.8);
                            animation: payment-history-spinner 0.7s linear infinite;
                            z-index: 2;
                        }

                        .payment-history-page .data-table-container.ready::after,
                        .payment-history-page .data-table-container.ready::before {
                            display: none;
                        }

                        .payment-history-page .table {
                            margin-bottom: 0;
                        }

                        .payment-history-page .table-init-hidden {
                            opacity: 0;
                        }

                        .payment-history-page .table-init-ready {
                            opacity: 1;
                            transition: opacity 0.2s ease;
                        }

                        .payment-history-page .table thead th {
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

                        .payment-history-page .table td {
                            vertical-align: middle;
                            border-top: 1px solid #eef3f8;
                            color: var(--text);
                        }

                        .payment-history-page .table tbody tr:hover {
                            background: rgba(37, 99, 235, 0.03);
                        }

                        .payment-history-page .num-cell {
                            font-family: var(--font-mono);
                            font-variant-numeric: tabular-nums;
                        }

                        .payment-history-page .payment-date {
                            white-space: nowrap;
                            color: var(--text-soft);
                            font-size: 0.82rem;
                        }

                        .payment-history-page .customer-link,
                        .payment-history-page .invoice-link {
                            color: var(--primary-2);
                            font-weight: 700;
                            text-decoration: none;
                        }

                        .payment-history-page .customer-link:hover,
                        .payment-history-page .invoice-link:hover {
                            text-decoration: underline;
                        }

                        .payment-history-page .reference-pill {
                            display: inline-flex;
                            align-items: center;
                            padding: 5px 9px;
                            border-radius: 999px;
                            background: #f8fbff;
                            border: 1px solid var(--line);
                            color: var(--text-soft);
                            font-size: 0.78rem;
                            font-weight: 700;
                        }

                        .payment-history-page .action-set {
                            display: inline-flex;
                            align-items: center;
                            justify-content: center;
                            gap: 6px;
                        }

                        .payment-history-page .action-icon {
                            position: relative;
                            display: inline-flex;
                            align-items: center;
                            justify-content: center;
                            width: 34px;
                            height: 34px;
                            border-radius: 50%;
                            text-decoration: none;
                            transition: all 0.2s ease;
                            font-size: 15px;
                        }

                        .payment-history-page .action-icon.edit {
                            color: #2563eb;
                            background: rgba(37, 99, 235, 0.12);
                        }

                        .payment-history-page .action-icon.edit:hover {
                            color: #1d4ed8;
                            background: rgba(37, 99, 235, 0.22);
                        }

                        .payment-history-page .action-icon.delete {
                            color: var(--danger);
                            background: rgba(225, 29, 72, 0.12);
                        }

                        .payment-history-page .action-icon.delete:hover {
                            color: #be123c;
                            background: rgba(225, 29, 72, 0.22);
                        }

                        .payment-history-page .action-icon::after {
                            content: attr(data-label);
                            position: absolute;
                            bottom: -32px;
                            left: 50%;
                            transform: translate(-50%, 6px);
                            background: rgba(18, 32, 51, 0.92);
                            color: #fff;
                            padding: 4px 8px;
                            border-radius: 4px;
                            font-size: 11px;
                            white-space: nowrap;
                            opacity: 0;
                            pointer-events: none;
                            transition: all 0.15s ease;
                        }

                        .payment-history-page .action-icon:hover::after {
                            opacity: 1;
                            transform: translate(-50%, 0);
                        }

                        .payment-history-page .table-empty {
                            text-align: center;
                            color: var(--text-soft);
                            padding: 48px 16px;
                        }

                        .payment-history-page .table-empty strong {
                            display: block;
                            color: var(--text);
                            font-size: 1rem;
                            margin-bottom: 6px;
                        }

                        .payment-history-page .dataTables_wrapper .dataTables_length,
                        .payment-history-page .dataTables_wrapper .dataTables_filter {
                            margin-bottom: 16px;
                        }

                        .payment-history-page .dataTables_wrapper .dataTables_info,
                        .payment-history-page .dataTables_wrapper .dataTables_paginate {
                            margin-top: 16px;
                        }

                        @keyframes payment-history-spinner {
                            to {
                                transform: rotate(360deg);
                            }
                        }

                        @media (max-width: 1199.98px) {
                            .payment-history-page .stat-strip {
                                grid-template-columns: repeat(2, minmax(0, 1fr));
                            }

                            .payment-history-page .context-grid {
                                grid-template-columns: 1fr;
                            }
                        }

                        @media (max-width: 767.98px) {
                            .payment-history-page .ph-title {
                                font-size: 1.65rem;
                            }

                            .payment-history-page .stat-strip {
                                grid-template-columns: 1fr;
                            }

                            .payment-history-page .ph-card-header,
                            .payment-history-page .ph-card-body {
                                padding-left: 18px;
                                padding-right: 18px;
                            }

                            .payment-history-page .ph-actions {
                                width: 100%;
                                justify-content: stretch;
                            }

                            .payment-history-page .ph-actions a {
                                flex: 1 1 auto;
                                justify-content: center;
                            }
                        }
                    </style>

                    <div class="ph-header">
                        <div class="ph-header-left">
                            <div class="ph-eyebrow">Payment History</div>
                            <h1 class="ph-title"><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8'); ?></h1>
                            <p class="ph-subtitle"><?= htmlspecialchars($pageSubtitle, ENT_QUOTES, 'UTF-8'); ?></p>
                        </div>

                        <div class="ph-actions">
                            <a class="btn-soft" href="<?= htmlspecialchars($backUrl, ENT_QUOTES, 'UTF-8'); ?>">
                                <i class="fas fa-arrow-left"></i>
                                <?= htmlspecialchars($backLabel, ENT_QUOTES, 'UTF-8'); ?>
                            </a>
                            <?php if ($invoiceUrl !== ''): ?>
                                <a class="btn-soft" href="<?= htmlspecialchars($invoiceUrl, ENT_QUOTES, 'UTF-8'); ?>">
                                    <i class="fas fa-file-invoice"></i>
                                    View Invoice
                                </a>
                            <?php endif; ?>
                            <?php if ($showAddPayment): ?>
                                <a class="btn-solid" href="<?= htmlspecialchars($addPaymentUrl, ENT_QUOTES, 'UTF-8'); ?>">
                                    <i class="fas fa-plus-circle"></i>
                                    Add Payment
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="stat-strip">
                        <div class="stat-card sc-total">
                            <div class="stat-label">Invoice Total Due</div>
                            <div class="stat-value"><?= number_format($totalDue, 2); ?></div>
                            <div class="stat-meta"><?= $invoiceNo !== '' ? 'Invoice #' . htmlspecialchars($invoiceNo, ENT_QUOTES, 'UTF-8') : 'No linked invoice record'; ?></div>
                        </div>

                        <div class="stat-card sc-paid">
                            <div class="stat-label">Payments Recorded</div>
                            <div class="stat-value"><?= number_format($recordedPaymentTotal, 2); ?></div>
                            <div class="stat-meta"><?= $paymentCount; ?> payment<?= $paymentCount === 1 ? '' : 's'; ?> in history</div>
                        </div>

                        <div class="stat-card sc-balance">
                            <div class="stat-label">Remaining Balance</div>
                            <div class="stat-value"><?= number_format(max($balance, 0), 2); ?></div>
                            <div class="stat-meta"><?= $balance <= 0.00001 && $paymentCount > 0 ? 'Invoice fully paid' : 'Open amount still due'; ?></div>
                        </div>

                        <div class="stat-card sc-latest">
                            <div class="stat-label">Latest Payment</div>
                            <div class="stat-value" style="font-size:1.45rem;line-height:1.15;"><?= htmlspecialchars($latestPaymentLabel, ENT_QUOTES, 'UTF-8'); ?></div>
                            <div class="stat-meta"><?= $paymentCount > 0 ? 'Most recent recorded payment date' : 'Waiting for first payment'; ?></div>
                        </div>
                    </div>

                    <div class="ph-card">
                        <div class="ph-card-header">
                            <div>
                                <h2 class="ph-card-title">Payment Records</h2>
                                <!-- <p class="ph-card-subtitle">Review payment references, receipts, descriptions, and customer links in one place.</p> -->
                            </div>

                            <div class="header-badges">
                                <?php if ($invoiceNo !== ''): ?>
                                    <span class="header-badge">
                                        <i class="fas fa-hashtag"></i>
                                        <?= htmlspecialchars($invoiceNo, ENT_QUOTES, 'UTF-8'); ?>
                                    </span>
                                <?php endif; ?>
                                <?php if ($invoiceSource !== ''): ?>
                                    <span class="header-badge">
                                        <i class="fas fa-layer-group"></i>
                                        <?= htmlspecialchars($invoiceSource === 'Others' ? 'Invoice' : $invoiceSource, ENT_QUOTES, 'UTF-8'); ?>
                                    </span>
                                <?php endif; ?>
                                <?php if ($orderID !== ''): ?>
                                    <span class="header-badge">
                                        <i class="fas fa-link"></i>
                                        Ref <?= htmlspecialchars($orderID, ENT_QUOTES, 'UTF-8'); ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="ph-card-body">
                            <div class="context-grid">
                                <div class="context-panel">
                                    <div class="context-label">Customer</div>
                                    <div class="context-value"><?= htmlspecialchars($customerName !== '' ? $customerName : 'Walk-in Customer', ENT_QUOTES, 'UTF-8'); ?></div>
                                    <div class="context-help">
                                        <?= $customerAddress !== ''
                                            ? nl2br(htmlspecialchars($customerAddress, ENT_QUOTES, 'UTF-8'))
                                            : 'No customer address is linked to this invoice yet.'; ?>
                                    </div>
                                </div>

                                <div class="context-panel">
                                    <div class="context-label">Invoice Snapshot</div>
                                    <div class="context-value">
                                        <?= $invoiceNo !== '' ? 'Invoice #' . htmlspecialchars($invoiceNo, ENT_QUOTES, 'UTF-8') : 'Payment records only'; ?>
                                    </div>
                                    <div class="context-help">
                                        <span class="context-mono">Credited:</span> <?= number_format($amountPaid, 2); ?><br>
                                        <span class="context-mono">Balance:</span> <?= number_format(max($balance, 0), 2); ?><br>
                                        <span class="context-mono">Source:</span> <?= htmlspecialchars($invoiceSource !== '' ? ($invoiceSource === 'Others' ? 'Invoice' : $invoiceSource) : 'Not specified', ENT_QUOTES, 'UTF-8'); ?>
                                    </div>
                                </div>
                            </div>

                            <div class="table-responsive data-table-container loading">
                                <table id="payment-history-table" class="table table-hover table-centered mb-0 table-init-hidden">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th class="text-right">Amount Paid</th>
                                            <th class="text-right">Tax 2307</th>
                                            <th class="text-right">Total Credit</th>
                                            <th>O.R. No.</th>
                                            <th>Reference</th>
                                            <th>Description</th>
                                            <th>Payor</th>
                                            <th class="text-center">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($payments)): ?>
                                            <?php foreach ($payments as $row): ?>
                                                <?php
                                                $paymentDateRaw = trim((string) ($row->PDate ?? ''));
                                                $paymentDateDisplay = $paymentDateRaw !== '' && $paymentDateRaw !== '0000-00-00'
                                                    ? date('M d, Y', strtotime($paymentDateRaw))
                                                    : 'Not specified';
                                                $customerHistoryParams = !empty($row->CustID)
                                                    ? 'cust_id=' . rawurlencode((string) $row->CustID)
                                                    : 'customer=' . rawurlencode((string) $row->Customer);
                                                $customerHistoryUrl = base_url() . 'Page/customerHistory?' . $customerHistoryParams;
                                                $paymentReference = trim((string) ($row->PaymentReference ?? ''));
                                                $orNumber = trim((string) ($row->ORNo ?? ''));
                                                $description = trim((string) ($row->TransDescription ?? ''));
                                                $payor = trim((string) ($row->Customer ?? ''));
                                                $cashPaid = (float) ($row->AmountPaid ?? 0);
                                                $taxAmount = (float) ($row->TaxAmount ?? 0);
                                                $grossAmount = (float) ($row->GrossAmountPaid ?? ($cashPaid + $taxAmount));
                                                ?>
                                                <tr>
                                                    <td class="payment-date" data-order="<?= htmlspecialchars($paymentDateRaw, ENT_QUOTES, 'UTF-8'); ?>"><?= htmlspecialchars($paymentDateDisplay, ENT_QUOTES, 'UTF-8'); ?></td>
                                                    <td class="text-right num-cell" data-order="<?= htmlspecialchars((string) $cashPaid, ENT_QUOTES, 'UTF-8'); ?>"><?= number_format($cashPaid, 2); ?></td>
                                                    <td class="text-right num-cell" data-order="<?= htmlspecialchars((string) $taxAmount, ENT_QUOTES, 'UTF-8'); ?>"><?= number_format($taxAmount, 2); ?></td>
                                                    <td class="text-right num-cell" data-order="<?= htmlspecialchars((string) $grossAmount, ENT_QUOTES, 'UTF-8'); ?>"><?= number_format($grossAmount, 2); ?></td>
                                                    <td><?= htmlspecialchars($orNumber !== '' ? $orNumber : '-', ENT_QUOTES, 'UTF-8'); ?></td>
                                                    <td>
                                                        <span class="reference-pill"><?= htmlspecialchars($paymentReference !== '' ? $paymentReference : 'No reference', ENT_QUOTES, 'UTF-8'); ?></span>
                                                    </td>
                                                    <td><?= htmlspecialchars($description !== '' ? $description : '-', ENT_QUOTES, 'UTF-8'); ?></td>
                                                    <td>
                                                        <a class="customer-link" href="<?= htmlspecialchars($customerHistoryUrl, ENT_QUOTES, 'UTF-8'); ?>">
                                                            <?= htmlspecialchars($payor !== '' ? $payor : 'Unknown payor', ENT_QUOTES, 'UTF-8'); ?>
                                                        </a>
                                                    </td>
                                                    <td class="text-center">
                                                        <?php if ($this->session->userdata('level') === 'Admin'): ?>
                                                            <div class="action-set">
                                                                <a class="action-icon edit" href="<?= base_url(); ?>Page/updatePayment?id=<?= (int) $row->paymentID; ?>" data-label="Edit" title="Edit">
                                                                    <i class="fa fa-edit"></i>
                                                                </a>
                                                                <a class="action-icon delete" href="<?= base_url(); ?>Page/deletePayment?id=<?= (int) $row->paymentID; ?>" onclick="return confirm('Are you sure you want to delete this record?');" data-label="Delete" title="Delete">
                                                                    <i class="fa fa-trash"></i>
                                                                </a>
                                                            </div>
                                                        <?php else: ?>
                                                            <span class="text-muted">View only</span>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="9" class="table-empty">
                                                    <strong>No payment history found yet.</strong>
                                                    This invoice does not have any recorded payments at the moment.
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
                var $tableContainer = $('.payment-history-page .data-table-container');
                var $paymentTable = $('#payment-history-table');

                if (!$paymentTable.length) {
                    return;
                }

                $paymentTable.DataTable({
                    responsive: true,
                    autoWidth: false,
                    deferRender: true,
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
                        emptyTable: 'No payment history recorded yet.'
                    },
                    columnDefs: [{
                        targets: [1, 2, 3],
                        className: 'text-right'
                    }, {
                        targets: -1,
                        orderable: false,
                        searchable: false
                    }],
                    initComplete: function() {
                        $paymentTable.removeClass('table-init-hidden').addClass('table-init-ready');
                        $tableContainer.removeClass('loading').addClass('ready');
                    }
                });
            });
        })(jQuery);
    </script>

</body>

</html>