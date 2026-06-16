<?php
$payments = isset($data) && is_array($data) ? $data : array();
$clientData = isset($client) ? $client : null;
$primaryPayment = !empty($payments) ? $payments[0] : null;

$resolvedCustID = trim((string) ($custID ?? ''));
if ($resolvedCustID === '' && $clientData && !empty($clientData->CustID)) {
    $resolvedCustID = trim((string) $clientData->CustID);
}
if ($resolvedCustID === '' && $primaryPayment && !empty($primaryPayment->CustID)) {
    $resolvedCustID = trim((string) $primaryPayment->CustID);
}

$resolvedCustomerName = trim((string) (!empty($customerName)
    ? $customerName
    : ($clientData->Customer ?? ($primaryPayment->Customer ?? 'Customer'))));
$address = trim((string) ($clientData->Address ?? ''));
$contactPerson = trim((string) ($clientData->ContactPerson ?? ''));
$contactNos = trim((string) ($clientData->ContactNos ?? ''));
$companyEmail = trim((string) ($clientData->CompanyEmail ?? ''));
$clientEmail = trim((string) ($clientData->client_email ?? ''));
$clientStatus = trim((string) ($clientData->ClientStat ?? ''));

$paymentCount = count($payments);
$totalCollected = 0.0;
$distinctInvoices = array();
foreach ($payments as $paymentRow) {
    $totalCollected += (float) ($paymentRow->GrossAmountPaid ?? ((float) ($paymentRow->AmountPaid ?? 0) + (float) ($paymentRow->TaxAmount ?? 0)));
    $invoiceNoKey = trim((string) ($paymentRow->InvoiceNo ?? ''));
    if ($invoiceNoKey !== '') {
        $distinctInvoices[$invoiceNoKey] = true;
    }
}
$invoiceCount = count($distinctInvoices);

$latestPaymentRaw = !empty($payments) && !empty($payments[0]->PDate) ? trim((string) $payments[0]->PDate) : '';
$latestPaymentLabel = $latestPaymentRaw !== '' && $latestPaymentRaw !== '0000-00-00'
    ? date('M d, Y', strtotime($latestPaymentRaw))
    : 'No payments yet';

$filterMonthValue = isset($filterMonth) ? trim((string) $filterMonth) : '';
$filterYearValue = isset($filterYear) ? trim((string) $filterYear) : '';
$backToPaymentsUrl = base_url() . 'Page/paymentList';
if ($filterMonthValue !== '' && $filterYearValue !== '') {
    $backToPaymentsUrl .= '?' . http_build_query(array(
        'filter_month' => $filterMonthValue,
        'filter_year' => $filterYearValue
    ));
}

$clientProfileUrl = '';
if ($resolvedCustID !== '') {
    $clientProfileUrl = base_url() . 'Page/clientProfile?cust_id=' . rawurlencode($resolvedCustID);
} elseif ($resolvedCustomerName !== '') {
    $clientProfileUrl = base_url() . 'Page/clientProfile?customer=' . rawurlencode($resolvedCustomerName);
}

$backUrl = isset($backUrl) && trim((string) $backUrl) !== ''
    ? (string) $backUrl
    : $backToPaymentsUrl;
$backLabel = isset($backLabel) && trim((string) $backLabel) !== ''
    ? (string) $backLabel
    : 'Back to Payments';
$showClientProfileAction = array_key_exists('showClientProfileAction', get_defined_vars())
    ? (bool) $showClientProfileAction
    : true;
$clientProfileLabel = isset($clientProfileLabel) && trim((string) $clientProfileLabel) !== ''
    ? (string) $clientProfileLabel
    : 'View Company';
$clientPortalMode = strtolower(trim((string) $this->session->userdata('level'))) === 'client';

$statusClass = 'status-neutral';
if (strcasecmp($clientStatus, 'Active') === 0) {
    $statusClass = 'status-active';
} elseif (strcasecmp($clientStatus, 'Inactive') === 0) {
    $statusClass = 'status-inactive';
} elseif (strcasecmp($clientStatus, 'Prospect') === 0) {
    $statusClass = 'status-prospect';
} elseif (strcasecmp($clientStatus, 'Donation') === 0) {
    $statusClass = 'status-donation';
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
            <div class="container-fluid customer-history-page">

                <style>
                    .customer-history-page {
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

                    .customer-history-page * {
                        box-sizing: border-box;
                    }

                    .customer-history-page .ch-header {
                        margin: 24px 0 22px;
                        display: flex;
                        justify-content: space-between;
                        align-items: flex-end;
                        gap: 16px;
                        flex-wrap: wrap;
                    }

                    .customer-history-page .ch-eyebrow {
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

                    .customer-history-page .ch-eyebrow::before {
                        content: '';
                        width: 8px;
                        height: 8px;
                        border-radius: 50%;
                        background: linear-gradient(135deg, var(--primary), var(--primary-2));
                        box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.10);
                    }

                    .customer-history-page .ch-title {
                        margin: 0;
                        font-family: var(--font-head);
                        font-size: 2rem;
                        font-weight: 800;
                        letter-spacing: -0.04em;
                        color: var(--text);
                        line-height: 1.1;
                    }

                    .customer-history-page .ch-subtitle {
                        margin-top: 8px;
                        color: var(--text-soft);
                        font-size: 0.93rem;
                        font-weight: 500;
                    }

                    .customer-history-page .ch-actions {
                        display: flex;
                        gap: 10px;
                        flex-wrap: wrap;
                        justify-content: flex-end;
                    }

                    .customer-history-page .btn-soft,
                    .customer-history-page .btn-solid {
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

                    .customer-history-page .btn-soft {
                        background: rgba(255, 255, 255, 0.88);
                        color: var(--text);
                        border: 1px solid var(--line-strong);
                        box-shadow: var(--shadow-soft);
                    }

                    .customer-history-page .btn-solid {
                        background: linear-gradient(135deg, var(--primary), var(--primary-2));
                        color: #fff;
                        border: none;
                        box-shadow: 0 10px 24px rgba(37, 99, 235, 0.22);
                    }

                    .customer-history-page .btn-soft:hover,
                    .customer-history-page .btn-solid:hover {
                        transform: translateY(-1px);
                        filter: brightness(1.02);
                        text-decoration: none;
                    }

                    .customer-history-page .btn-solid:hover {
                        color: #fff;
                    }

                    .customer-history-page .stat-strip {
                        display: grid;
                        grid-template-columns: repeat(4, minmax(0, 1fr));
                        gap: 16px;
                        margin-bottom: 22px;
                    }

                    .customer-history-page .stat-card {
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

                    .customer-history-page .stat-card::before {
                        content: '';
                        position: absolute;
                        inset: 0 0 auto 0;
                        height: 4px;
                    }

                    .customer-history-page .stat-card.sc-count::before {
                        background: linear-gradient(90deg, #3b82f6, #60a5fa);
                    }

                    .customer-history-page .stat-card.sc-total::before {
                        background: linear-gradient(90deg, #10b981, #34d399);
                    }

                    .customer-history-page .stat-card.sc-latest::before {
                        background: linear-gradient(90deg, #f59e0b, #fbbf24);
                    }

                    .customer-history-page .stat-card.sc-invoices::before {
                        background: linear-gradient(90deg, #f43f5e, #fb7185);
                    }

                    .customer-history-page .stat-label {
                        color: var(--text-faint);
                        font-size: 0.74rem;
                        font-weight: 700;
                        text-transform: uppercase;
                        letter-spacing: 0.08em;
                        margin-bottom: 12px;
                    }

                    .customer-history-page .stat-value {
                        color: var(--text);
                        font-size: 2rem;
                        font-weight: 800;
                        line-height: 1;
                        letter-spacing: -0.04em;
                        margin-bottom: 6px;
                    }

                    .customer-history-page .stat-meta {
                        color: var(--text-soft);
                        font-size: 0.82rem;
                        font-weight: 500;
                    }

                    .customer-history-page .ch-card {
                        background: var(--surface);
                        backdrop-filter: blur(12px);
                        border: 1px solid rgba(255, 255, 255, 0.72);
                        border-radius: 24px;
                        box-shadow: var(--shadow);
                        overflow: hidden;
                    }

                    .customer-history-page .ch-card-header {
                        padding: 20px 24px;
                        border-bottom: 1px solid var(--line);
                        display: flex;
                        align-items: center;
                        justify-content: space-between;
                        gap: 14px;
                        flex-wrap: wrap;
                        background: linear-gradient(180deg, rgba(255, 255, 255, 0.94), rgba(249, 251, 255, 0.94));
                    }

                    .customer-history-page .ch-card-title {
                        margin: 0;
                        color: var(--text);
                        font-size: 1.05rem;
                        font-weight: 800;
                        letter-spacing: -0.02em;
                    }

                    .customer-history-page .ch-card-subtitle {
                        margin-top: 5px;
                        color: var(--text-soft);
                        font-size: 0.88rem;
                    }

                    .customer-history-page .chip-row {
                        display: flex;
                        gap: 8px;
                        flex-wrap: wrap;
                    }

                    .customer-history-page .chip {
                        display: inline-flex;
                        align-items: center;
                        gap: 6px;
                        padding: 7px 11px;
                        border-radius: 999px;
                        background: var(--primary-soft);
                        color: var(--primary-2);
                        font-size: 0.78rem;
                        font-weight: 700;
                    }

                    .customer-history-page .chip.status-neutral {
                        background: #f8fbff;
                        border: 1px solid var(--line);
                        color: var(--text-soft);
                    }

                    .customer-history-page .chip.status-active {
                        background: var(--success-soft);
                        color: var(--success);
                    }

                    .customer-history-page .chip.status-inactive {
                        background: var(--danger-soft);
                        color: var(--danger);
                    }

                    .customer-history-page .chip.status-prospect {
                        background: var(--warning-soft);
                        color: var(--warning);
                    }

                    .customer-history-page .chip.status-donation {
                        background: #efe8ff;
                        color: #6f42c1;
                    }

                    .customer-history-page .ch-card-body {
                        padding: 22px 24px 24px;
                    }

                    .customer-history-page .context-grid {
                        display: grid;
                        grid-template-columns: minmax(0, 1fr) minmax(300px, 0.9fr);
                        gap: 18px;
                        margin-bottom: 22px;
                    }

                    .customer-history-page .context-panel {
                        background: #fff;
                        border: 1px solid var(--line);
                        border-radius: 18px;
                        padding: 18px;
                        box-shadow: 0 10px 26px rgba(15, 23, 42, 0.04);
                    }

                    .customer-history-page .context-label {
                        color: var(--text-faint);
                        font-size: 0.72rem;
                        font-weight: 800;
                        letter-spacing: 0.08em;
                        text-transform: uppercase;
                        margin-bottom: 10px;
                    }

                    .customer-history-page .context-value {
                        color: var(--text);
                        font-size: 1.08rem;
                        font-weight: 700;
                        line-height: 1.45;
                        margin-bottom: 6px;
                    }

                    .customer-history-page .context-help {
                        color: var(--text-soft);
                        font-size: 0.86rem;
                        line-height: 1.6;
                    }

                    .customer-history-page .context-mono {
                        font-family: var(--font-mono);
                        font-size: 0.9rem;
                        color: var(--text-soft);
                    }

                    .customer-history-page .data-table-container {
                        position: relative;
                    }

                    .customer-history-page .data-table-container.loading::after {
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

                    .customer-history-page .data-table-container.loading::before {
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
                        animation: customer-history-spinner 0.7s linear infinite;
                        z-index: 2;
                    }

                    .customer-history-page .data-table-container.ready::after,
                    .customer-history-page .data-table-container.ready::before {
                        display: none;
                    }

                    .customer-history-page .table {
                        margin-bottom: 0;
                    }

                    .customer-history-page .table-init-hidden {
                        opacity: 0;
                    }

                    .customer-history-page .table-init-ready {
                        opacity: 1;
                        transition: opacity 0.2s ease;
                    }

                    .customer-history-page .table thead th {
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

                    .customer-history-page .table td {
                        vertical-align: middle;
                        border-top: 1px solid #eef3f8;
                        color: var(--text);
                    }

                    .customer-history-page .table tbody tr:hover {
                        background: rgba(37, 99, 235, 0.03);
                    }

                    .customer-history-page .num-cell {
                        font-family: var(--font-mono);
                        font-variant-numeric: tabular-nums;
                    }

                    .customer-history-page .invoice-link {
                        color: var(--primary-2);
                        font-weight: 700;
                        text-decoration: none;
                    }

                    .customer-history-page .invoice-link:hover {
                        text-decoration: underline;
                    }

                    .customer-history-page .source-pill {
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

                    .customer-history-page .reference-pill {
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

                    .customer-history-page .table-empty {
                        text-align: center;
                        color: var(--text-soft);
                        padding: 48px 16px;
                    }

                    .customer-history-page .table-empty strong {
                        display: block;
                        color: var(--text);
                        font-size: 1rem;
                        margin-bottom: 6px;
                    }

                    .customer-history-page .dataTables_wrapper .dataTables_length,
                    .customer-history-page .dataTables_wrapper .dataTables_filter {
                        margin-bottom: 16px;
                    }

                    .customer-history-page .dataTables_wrapper .dataTables_info,
                    .customer-history-page .dataTables_wrapper .dataTables_paginate {
                        margin-top: 16px;
                    }

                    @keyframes customer-history-spinner {
                        to {
                            transform: rotate(360deg);
                        }
                    }

                    @media (max-width: 1199.98px) {
                        .customer-history-page .stat-strip {
                            grid-template-columns: repeat(2, minmax(0, 1fr));
                        }

                        .customer-history-page .context-grid {
                            grid-template-columns: 1fr;
                        }
                    }

                    @media (max-width: 767.98px) {
                        .customer-history-page .ch-title {
                            font-size: 1.7rem;
                        }

                        .customer-history-page .stat-strip {
                            grid-template-columns: 1fr;
                        }

                        .customer-history-page .ch-card-header,
                        .customer-history-page .ch-card-body {
                            padding-left: 18px;
                            padding-right: 18px;
                        }

                        .customer-history-page .ch-actions {
                            width: 100%;
                            justify-content: stretch;
                        }

                        .customer-history-page .ch-actions a {
                            flex: 1 1 auto;
                            justify-content: center;
                        }
                    }
                </style>

                <div class="ch-header">
                    <div>
                        <div class="ch-eyebrow"><?= $clientPortalMode ? 'My Payment History' : 'Customer History'; ?></div>
                        <h1 class="ch-title"><?= htmlspecialchars($resolvedCustomerName !== '' ? $resolvedCustomerName : 'Customer', ENT_QUOTES, 'UTF-8'); ?></h1>
                        <p class="ch-subtitle">
                            <?= $resolvedCustID !== '' ? 'Customer ID ' . htmlspecialchars($resolvedCustID, ENT_QUOTES, 'UTF-8') . ' · ' : ''; ?>
                            <?= $clientPortalMode
                                ? 'Review every payment recorded for your company across invoices in your portal.'
                                : 'Review all recorded payments for this customer across invoices.'; ?>
                        </p>
                    </div>

                    <div class="ch-actions">
                        <a class="btn-soft" href="<?= htmlspecialchars($backUrl, ENT_QUOTES, 'UTF-8'); ?>">
                            <i class="fas fa-arrow-left"></i>
                            <?= htmlspecialchars($backLabel, ENT_QUOTES, 'UTF-8'); ?>
                        </a>
                        <?php if ($showClientProfileAction && $clientProfileUrl !== ''): ?>
                            <a class="btn-solid" href="<?= htmlspecialchars($clientProfileUrl, ENT_QUOTES, 'UTF-8'); ?>">
                                <i class="fas fa-building"></i>
                                <?= htmlspecialchars($clientProfileLabel, ENT_QUOTES, 'UTF-8'); ?>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="stat-strip">
                    <div class="stat-card sc-count">
                        <div class="stat-label">Payments Recorded</div>
                        <div class="stat-value"><?= number_format($paymentCount); ?></div>
                        <div class="stat-meta">Total payment entries for this customer</div>
                    </div>
                    <div class="stat-card sc-total">
                        <div class="stat-label">Total Collected</div>
                        <div class="stat-value"><?= number_format($totalCollected, 2); ?></div>
                        <div class="stat-meta">Combined amount collected across all entries</div>
                    </div>
                    <div class="stat-card sc-latest">
                        <div class="stat-label">Latest Payment</div>
                        <div class="stat-value" style="font-size:1.45rem;line-height:1.15;"><?= htmlspecialchars($latestPaymentLabel, ENT_QUOTES, 'UTF-8'); ?></div>
                        <div class="stat-meta"><?= $paymentCount > 0 ? 'Most recent recorded payment date' : 'No payments logged yet'; ?></div>
                    </div>
                    <div class="stat-card sc-invoices">
                        <div class="stat-label">Invoices Touched</div>
                        <div class="stat-value"><?= number_format($invoiceCount); ?></div>
                        <div class="stat-meta">Distinct invoices linked to this customer history</div>
                    </div>
                </div>

                <div class="ch-card">
                    <div class="ch-card-header">
                        <div>
                            <h2 class="ch-card-title">Payment Records</h2>
                            <p class="ch-card-subtitle">Browse payment entries, references, receipts, and related invoice numbers for this customer.</p>
                        </div>

                        <div class="chip-row">
                            <?php if ($resolvedCustID !== ''): ?>
                                <span class="chip">
                                    <i class="fas fa-hashtag"></i>
                                    <?= htmlspecialchars($resolvedCustID, ENT_QUOTES, 'UTF-8'); ?>
                                </span>
                            <?php endif; ?>
                            <span class="chip <?= htmlspecialchars($statusClass, ENT_QUOTES, 'UTF-8'); ?>">
                                <i class="fas fa-circle"></i>
                                <?= htmlspecialchars($clientStatus !== '' ? $clientStatus : 'No Status', ENT_QUOTES, 'UTF-8'); ?>
                            </span>
                        </div>
                    </div>

                    <div class="ch-card-body">
                        <div class="context-grid">
                            <div class="context-panel">
                                <div class="context-label">Customer</div>
                                <div class="context-value"><?= htmlspecialchars($resolvedCustomerName !== '' ? $resolvedCustomerName : 'Customer', ENT_QUOTES, 'UTF-8'); ?></div>
                                <div class="context-help">
                                    <?= $address !== ''
                                        ? nl2br(htmlspecialchars($address, ENT_QUOTES, 'UTF-8'))
                                        : 'No customer address is available for this record.'; ?>
                                </div>
                            </div>

                            <div class="context-panel">
                                <div class="context-label">Contact Snapshot</div>
                                <div class="context-help">
                                    <span class="context-mono">Contact Person:</span> <?= $contactPerson !== '' ? htmlspecialchars($contactPerson, ENT_QUOTES, 'UTF-8') : '—'; ?><br>
                                    <span class="context-mono">Contact No.:</span> <?= $contactNos !== '' ? htmlspecialchars($contactNos, ENT_QUOTES, 'UTF-8') : '—'; ?><br>
                                    <span class="context-mono">Company Email:</span> <?= $companyEmail !== '' ? htmlspecialchars($companyEmail, ENT_QUOTES, 'UTF-8') : '—'; ?><br>
                                    <span class="context-mono">Client Email:</span> <?= $clientEmail !== '' ? htmlspecialchars($clientEmail, ENT_QUOTES, 'UTF-8') : '—'; ?>
                                </div>
                            </div>
                        </div>

                        <div class="table-responsive data-table-container loading">
                            <table id="customer-history-table" class="table table-hover table-centered mb-0 table-init-hidden">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Invoice No.</th>
                                        <th>Source</th>
                                        <th class="text-right">Amount Paid</th>
                                        <th class="text-right">Tax 2307</th>
                                        <th class="text-right">Total Credit</th>
                                        <th class="text-center">O.R. No.</th>
                                        <th>Reference</th>
                                        <th>Description</th>
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
                                            $invoiceNo = trim((string) ($row->InvoiceNo ?? ''));
                                            $invoiceUrl = $invoiceNo !== '' ? base_url() . 'Page/invoice?invoice_no=' . rawurlencode($invoiceNo) : '';
                                            $paymentSourceLabel = trim((string) ($row->PaymentSource ?? ''));
                                            if ($paymentSourceLabel === 'Others') {
                                                $paymentSourceLabel = 'Invoice';
                                            }
                                            if ($paymentSourceLabel === '') {
                                                $paymentSourceLabel = 'Payment';
                                            }
                                            $orNumber = trim((string) ($row->ORNo ?? ''));
                                            $reference = trim((string) ($row->PaymentReference ?? ''));
                                            $description = trim((string) ($row->TransDescription ?? ''));
                                            $amountPaid = (float) ($row->AmountPaid ?? 0);
                                            $taxAmount = (float) ($row->TaxAmount ?? 0);
                                            $grossAmount = (float) ($row->GrossAmountPaid ?? ($amountPaid + $taxAmount));
                                            ?>
                                            <tr>
                                                <td class="num-cell" data-order="<?= htmlspecialchars($paymentDateRaw, ENT_QUOTES, 'UTF-8'); ?>"><?= htmlspecialchars($paymentDateDisplay, ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td data-order="<?= htmlspecialchars($invoiceNo, ENT_QUOTES, 'UTF-8'); ?>">
                                                    <?php if ($invoiceUrl !== ''): ?>
                                                        <a class="invoice-link" href="<?= htmlspecialchars($invoiceUrl, ENT_QUOTES, 'UTF-8'); ?>">
                                                            #<?= htmlspecialchars($invoiceNo, ENT_QUOTES, 'UTF-8'); ?>
                                                        </a>
                                                    <?php else: ?>
                                                        —
                                                    <?php endif; ?>
                                                </td>
                                                <td><span class="source-pill"><?= htmlspecialchars($paymentSourceLabel, ENT_QUOTES, 'UTF-8'); ?></span></td>
                                                <td class="text-right num-cell" data-order="<?= htmlspecialchars((string) $amountPaid, ENT_QUOTES, 'UTF-8'); ?>"><?= number_format($amountPaid, 2); ?></td>
                                                <td class="text-right num-cell" data-order="<?= htmlspecialchars((string) $taxAmount, ENT_QUOTES, 'UTF-8'); ?>"><?= number_format($taxAmount, 2); ?></td>
                                                <td class="text-right num-cell" data-order="<?= htmlspecialchars((string) $grossAmount, ENT_QUOTES, 'UTF-8'); ?>"><?= number_format($grossAmount, 2); ?></td>
                                                <td class="text-center"><?= htmlspecialchars($orNumber !== '' ? $orNumber : '-', ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td><span class="reference-pill"><?= htmlspecialchars($reference !== '' ? $reference : 'No reference', ENT_QUOTES, 'UTF-8'); ?></span></td>
                                                <td><?= htmlspecialchars($description !== '' ? $description : '-', ENT_QUOTES, 'UTF-8'); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="9" class="table-empty">
                                                <strong>No payment history found yet.</strong>
                                                This customer does not have any recorded payments at the moment.
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
<script src="<?= base_url(); ?>assets/js/app.min.js"></script>
<script src="<?= base_url(); ?>assets/libs/datatables/jquery.dataTables.min.js"></script>
<script src="<?= base_url(); ?>assets/libs/datatables/dataTables.bootstrap4.min.js"></script>
<script src="<?= base_url(); ?>assets/libs/datatables/dataTables.responsive.min.js"></script>
<script src="<?= base_url(); ?>assets/libs/datatables/responsive.bootstrap4.min.js"></script>

<script>
    (function($) {
        'use strict';

        $(function() {
            var $tableContainer = $('.customer-history-page .data-table-container');
            var $historyTable = $('#customer-history-table');

            if (!$historyTable.length) {
                return;
            }

            $historyTable.DataTable({
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
                    emptyTable: 'No payment history recorded.'
                },
                columnDefs: [{
                    targets: [3, 4, 5],
                    className: 'text-right'
                }],
                initComplete: function() {
                    $historyTable.removeClass('table-init-hidden').addClass('table-init-ready');
                    $tableContainer.removeClass('loading').addClass('ready');
                }
            });
        });
    })(jQuery);
</script>

</body>

</html>
