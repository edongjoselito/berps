<?php
$clientData = isset($client) ? $client : null;
$clientInvoices = isset($invoices) && is_array($invoices) ? $invoices : array();
$clientPayments = isset($payments) && is_array($payments) ? $payments : array();

$clientName = trim((string) ($clientData->Customer ?? 'Company'));
$custID = trim((string) ($clientData->CustID ?? ''));
$address = trim((string) ($clientData->Address ?? ''));
$contactPerson = trim((string) ($clientData->ContactPerson ?? ''));
$contactNos = trim((string) ($clientData->ContactNos ?? ''));
$companyEmail = trim((string) ($clientData->CompanyEmail ?? ''));
$clientEmail = trim((string) ($clientData->client_email ?? ''));
$clientSource = trim((string) ($clientData->client_source ?? ''));
$salesAgent = trim((string) ($clientData->sales_agent ?? ''));
$facebookLink = trim((string) ($clientData->facebook_link ?? ''));
$notes = trim((string) ($clientData->notes ?? ''));
$clientStatus = trim((string) ($clientData->ClientStat ?? ''));

if (!function_exists('invoiceCoverageLabel')) {
    function invoiceCoverageLabel($invoiceRow)
    {
        $startRaw = trim((string) ($invoiceRow->TransDate ?? ''));
        $endRaw = trim((string) ($invoiceRow->invoiceExpirationDate ?? ''));

        if ($startRaw === '' || $startRaw === '0000-00-00') {
            return '';
        }

        $startTs = strtotime($startRaw);
        if ($startTs === false) {
            return '';
        }

        if ($endRaw !== '' && $endRaw !== '0000-00-00') {
            $endTs = strtotime($endRaw);
            if ($endTs !== false && date('Y-m-d', $endTs) !== date('Y-m-d', $startTs)) {
                return 'From ' . date('M d, Y', $startTs) . ' To ' . date('M d, Y', $endTs);
            }
        }

        return 'Coverage: ' . date('M d, Y', $startTs);
    }
}

$statusClass = 'status-prospect';
if (strcasecmp($clientStatus, 'Active') === 0) {
    $statusClass = 'status-active';
} elseif (strcasecmp($clientStatus, 'Inactive') === 0) {
    $statusClass = 'status-inactive';
} elseif (strcasecmp($clientStatus, 'Donation') === 0) {
    $statusClass = 'status-donation';
}

$openInvoices = array();
$paidInvoices = array();
$invoiceCount = count($clientInvoices);
$totalInvoiced = 0.0;
$totalCollected = 0.0;
$totalOutstanding = 0.0;

foreach ($clientInvoices as $invoiceRow) {
    $balance = (float) ($invoiceRow->Balance ?? 0);
    $totalInvoiced += (float) ($invoiceRow->TotalDue ?? 0);
    $totalCollected += (float) ($invoiceRow->AmountPaid ?? 0);
    $totalOutstanding += max($balance, 0);
    $invoiceRow->coverageLabel = invoiceCoverageLabel($invoiceRow);

    if ($balance <= 0.00001) {
        $paidInvoices[] = $invoiceRow;
    } else {
        $openInvoices[] = $invoiceRow;
    }
}

// Calculate payments totals
$paymentCount = count($clientPayments);
$totalPaymentsAmount = 0.0;
foreach ($clientPayments as $payment) {
    $totalPaymentsAmount += (float) ($payment->AmountPaid ?? 0);
}

$backUrl = isset($backUrl) && trim((string) $backUrl) !== ''
    ? (string) $backUrl
    : base_url() . 'Page/clientList';
$backLabel = isset($backLabel) && trim((string) $backLabel) !== ''
    ? (string) $backLabel
    : 'Back to Client List';
$activeTab = isset($activeTab) && in_array((string) $activeTab, ['invoices', 'payments', 'tickets']) ? (string) $activeTab : 'info';
$clientPortalMode = strtolower(trim((string) $this->session->userdata('level'))) === 'client';
$customerHistoryUrl = $custID !== ''
    ? base_url() . 'Page/customerHistory?cust_id=' . rawurlencode($custID)
    : ($clientName !== '' ? base_url() . 'Page/customerHistory?customer=' . rawurlencode($clientName) : '');
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
                <div class="container-fluid client-profile-page">

                    <style>

                        .client-profile-page {
                            --bg: #f5f7fb;
                            --surface: rgba(255, 255, 255, 0.96);
                            --surface-2: #ffffff;
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
                            --danger: #dc2626;
                            --danger-soft: #fef2f2;
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
                            padding-bottom: 80px;
                        }

                        .client-profile-page * {
                            box-sizing: border-box;
                        }

                        .client-profile-page .cp-header {
                            margin: 32px 0 28px;
                            display: flex;
                            justify-content: space-between;
                            align-items: flex-end;
                            gap: 24px;
                            flex-wrap: wrap;
                        }

                        .client-profile-page .cp-eyebrow {
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

                        .client-profile-page .cp-eyebrow::before {
                            content: '';
                            width: 8px;
                            height: 8px;
                            border-radius: 50%;
                            background: linear-gradient(135deg, var(--primary), var(--primary-2));
                            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.10);
                        }

                        .client-profile-page .cp-title {
                            margin: 0;
                            font-family: var(--font-primary, Montserrat, Segoe UI, Arial, sans-serif);
                            font-size: 2.15rem;
                            font-weight: 800;
                            color: var(--text);
                            line-height: 1.05;
                            letter-spacing: -0.05em;
                        }

                        .client-profile-page .cp-subtitle {
                            margin-top: 8px;
                            color: var(--text-soft);
                            font-size: 0.96rem;
                            max-width: 760px;
                        }

                        .client-profile-page .cp-actions {
                            display: flex;
                            gap: 12px;
                            flex-wrap: wrap;
                            justify-content: flex-end;
                        }

                        .client-profile-page .btn-soft,
                        .client-profile-page .btn-solid {
                            display: inline-flex;
                            align-items: center;
                            gap: 8px;
                            justify-content: center;
                            padding: 11px 18px;
                            border-radius: 12px;
                            font-size: 0.92rem;
                            font-weight: 700;
                            text-decoration: none;
                            transition: all 0.16s ease;
                        }

                        .client-profile-page .btn-soft {
                            background: #fff;
                            color: var(--text);
                            border: 1px solid var(--line-strong);
                        }

                        .client-profile-page .btn-soft:hover {
                            color: var(--primary);
                            border-color: #bfd3ef;
                            background: #f9fbff;
                            text-decoration: none;
                        }

                        .client-profile-page .btn-solid {
                            background: linear-gradient(135deg, var(--primary), var(--primary-2));
                            color: #fff;
                            border: none;
                            box-shadow: 0 10px 24px rgba(37, 99, 235, 0.24);
                        }

                        .client-profile-page .btn-solid:hover {
                            transform: translateY(-1px);
                            box-shadow: 0 14px 28px rgba(37, 99, 235, 0.28);
                            color: #fff;
                            text-decoration: none;
                        }

                        .client-profile-page .status-badge {
                            display: inline-flex;
                            align-items: center;
                            gap: 6px;
                            padding: 5px 12px;
                            border-radius: 999px;
                            font-size: 0.75rem;
                            font-weight: 500;
                        }

                        .client-profile-page .status-active {
                            background: var(--success-soft);
                            color: var(--success);
                        }

                        .client-profile-page .status-inactive {
                            background: var(--danger-soft);
                            color: var(--danger);
                        }

                        .client-profile-page .status-prospect {
                            background: var(--warning-soft);
                            color: var(--warning);
                        }

                        .client-profile-page .status-donation {
                            background: #f5f3ff;
                            color: #7c3aed;
                        }

                        .client-profile-page .stat-strip {
                            display: grid;
                            grid-template-columns: repeat(4, minmax(0, 1fr));
                            gap: 16px;
                            margin-bottom: 20px;
                        }

                        .client-profile-page .stat-card {
                            position: relative;
                            overflow: hidden;
                            background: var(--surface);
                            border: 1px solid rgba(255, 255, 255, 0.72);
                            border-radius: var(--radius-xl);
                            box-shadow: var(--shadow-soft);
                            padding: 18px 20px 20px;
                            cursor: pointer;
                            transition: all 0.2s ease;
                        }

                        .client-profile-page .stat-card:hover {
                            transform: translateY(-2px);
                            box-shadow: var(--shadow);
                            text-decoration: none;
                        }

                        .client-profile-page .stat-card::before {
                            content: '';
                            position: absolute;
                            inset: 0 0 auto 0;
                            height: 4px;
                        }

                        .client-profile-page .stat-card.sc-total::before {
                            background: linear-gradient(90deg, #3b82f6, #60a5fa);
                        }

                        .client-profile-page .stat-card.sc-open::before {
                            background: linear-gradient(90deg, #f59e0b, #fbbf24);
                        }

                        .client-profile-page .stat-card.sc-paid::before {
                            background: linear-gradient(90deg, #10b981, #34d399);
                        }

                        .client-profile-page .stat-card.sc-balance::before {
                            background: linear-gradient(90deg, #ef4444, #fb7185);
                        }

                        .client-profile-page .stat-card.sc-tickets::before {
                            background: linear-gradient(90deg, #8b5cf6, #a78bfa);
                        }

                        .client-profile-page .stat-card.sc-tickets-open::before {
                            background: linear-gradient(90deg, #f59e0b, #fbbf24);
                        }

                        .client-profile-page .stat-card.sc-tickets-closed::before {
                            background: linear-gradient(90deg, #10b981, #34d399);
                        }

                        .client-profile-page .stat-label {
                            color: var(--text-faint);
                            font-size: 0.74rem;
                            font-weight: 700;
                            text-transform: uppercase;
                            letter-spacing: 0.08em;
                            margin-bottom: 12px;
                        }

                        .client-profile-page .stat-value {
                            color: var(--text);
                            font-size: 1.85rem;
                            font-weight: 800;
                            line-height: 1;
                            letter-spacing: -0.04em;
                            font-family: var(--font-primary, Montserrat, Segoe UI, Arial, sans-serif);
                            margin-bottom: 0;
                        }

                        .client-profile-page .stat-meta {
                            color: var(--text-soft);
                            font-size: 0.84rem;
                            margin-top: 7px;
                        }

                        .client-profile-page .cp-card {
                            background: var(--surface);
                            border: 1px solid rgba(255, 255, 255, 0.72);
                            border-radius: var(--radius-xl);
                            box-shadow: var(--shadow);
                            overflow: hidden;
                        }

                        .client-profile-page .tabs-shell {
                            padding: 22px;
                        }

                        .client-profile-page .nav-tabs {
                            border-bottom: 1px solid var(--line);
                            gap: 8px;
                            margin-bottom: 20px;
                        }

                        .client-profile-page .nav-tabs .nav-link {
                            border: 0;
                            border-radius: 12px 12px 0 0;
                            color: var(--text-soft);
                            font-weight: 700;
                            padding: 11px 16px;
                            font-size: 0.92rem;
                            background: transparent;
                        }

                        .client-profile-page .nav-tabs .nav-link.active,
                        .client-profile-page .nav-tabs .nav-link:hover,
                        .client-profile-page .nav-tabs .nav-link:focus {
                            color: var(--primary);
                            background: var(--primary-soft);
                        }

                        .client-profile-page .tab-count {
                            display: inline-flex;
                            align-items: center;
                            justify-content: center;
                            min-width: 22px;
                            padding: 2px 7px;
                            margin-left: 6px;
                            border-radius: 999px;
                            background: rgba(37, 99, 235, 0.12);
                            color: var(--primary);
                            font-size: 0.72rem;
                            font-weight: 800;
                        }

                        .client-profile-page .info-panel {
                            background: var(--surface-2);
                            border: 1px solid var(--line);
                            border-radius: var(--radius-lg);
                            padding: 18px;
                            box-shadow: var(--shadow-soft);
                        }

                        .client-profile-page .info-title {
                            color: var(--text);
                            font-size: 1rem;
                            font-weight: 800;
                            margin-bottom: 14px;
                            letter-spacing: -0.02em;
                        }

                        .client-profile-page .detail-label {
                            color: var(--text-faint);
                            font-size: 0.74rem;
                            font-weight: 700;
                            margin-bottom: 6px;
                            text-transform: uppercase;
                            letter-spacing: 0.08em;
                        }

                        .client-profile-page .detail-value {
                            color: var(--text);
                            font-size: 0.9rem;
                            font-weight: 500;
                            line-height: 1.5;
                            word-break: break-word;
                        }

                        .client-profile-page .section-title {
                            margin: 0;
                            color: var(--text);
                            font-size: 1rem;
                            font-weight: 800;
                        }

                        .client-profile-page .section-subtitle {
                            color: var(--text-soft);
                            font-size: 0.84rem;
                            margin-top: 6px;
                        }

                        .client-profile-page .table-responsive {
                            border-radius: var(--radius-lg);
                            overflow: hidden;
                            border: 1px solid var(--line);
                            background: var(--surface-2);
                            box-shadow: var(--shadow-soft);
                        }

                        .client-profile-page .table thead th {
                            border-top: 0;
                            border-bottom: 1px solid var(--line);
                            color: var(--text-faint);
                            font-size: 0.72rem;
                            font-weight: 800;
                            background: linear-gradient(180deg, rgba(255, 255, 255, 0.98), rgba(249, 251, 255, 0.94));
                            padding: 14px 16px;
                            white-space: nowrap;
                            text-align: left;
                            text-transform: uppercase;
                            letter-spacing: 0.08em;
                        }

                        .client-profile-page .table td {
                            vertical-align: middle;
                            border-top: 1px solid var(--line);
                            color: var(--text);
                            padding: 13px 16px;
                            font-size: 0.88rem;
                        }

                        .client-profile-page .invoice-source {
                            display: inline-flex;
                            align-items: center;
                            padding: 4px 10px;
                            border-radius: 999px;
                            background: var(--primary-soft);
                            border: 1px solid var(--line);
                            color: var(--primary-2);
                            font-size: 0.75rem;
                            font-weight: 700;
                        }

                        .client-profile-page .invoice-status {
                            display: inline-flex;
                            align-items: center;
                            justify-content: center;
                            padding: 4px 10px;
                            border-radius: 999px;
                            font-size: 0.72rem;
                            font-weight: 700;
                        }

                        .client-profile-page .empty-state {
                            text-align: center;
                            color: var(--text-soft);
                            padding: 30px 18px;
                            font-size: 0.9rem;
                        }

                        .client-profile-page .empty-state strong {
                            display: block;
                            color: var(--text);
                            font-size: 0.95rem;
                            margin-bottom: 6px;
                            font-weight: 700;
                        }

                        .client-profile-page .tab-content {
                            padding-top: 4px;
                        }

                        .client-profile-page .info-grid {
                            display: grid;
                            grid-template-columns: repeat(2, minmax(0, 1fr));
                            gap: 20px;
                        }

                        .client-profile-page .info-panel.full {
                            grid-column: 1 / -1;
                        }

                        .client-profile-page .detail-list {
                            display: grid;
                            grid-template-columns: repeat(2, minmax(0, 1fr));
                            gap: 14px 20px;
                        }

                        .client-profile-page .detail-item {
                            min-width: 0;
                        }

                        .client-profile-page .detail-value a {
                            color: var(--primary-2);
                            text-decoration: none;
                        }

                        .client-profile-page .detail-value a:hover {
                            text-decoration: underline;
                        }

                        .client-profile-page .invoice-section+.invoice-section {
                            margin-top: 22px;
                        }

                        .client-profile-page .section-header {
                            display: flex;
                            justify-content: space-between;
                            align-items: flex-end;
                            gap: 12px;
                            flex-wrap: wrap;
                            margin-bottom: 14px;
                        }

                        .client-profile-page .table {
                            margin-bottom: 0;
                        }

                        .client-profile-page .invoice-link,
                        .client-profile-page .payment-link {
                            color: var(--primary-2);
                            font-weight: 700;
                            text-decoration: none;
                        }

                        .client-profile-page .invoice-link:hover,
                        .client-profile-page .payment-link:hover {
                            text-decoration: underline;
                        }

                        .client-profile-page .num-cell {
                            font-family: var(--font-primary, Montserrat, Segoe UI, Arial, sans-serif);
                            font-variant-numeric: tabular-nums;
                        }

                        .client-profile-page .invoice-status.status-paid {
                            background: var(--success-soft);
                            color: var(--success);
                        }

                        .client-profile-page .invoice-status.status-partial {
                            background: var(--warning-soft);
                            color: var(--warning);
                        }

                        .client-profile-page .invoice-status.status-unpaid {
                            background: var(--danger-soft);
                            color: var(--danger);
                        }

                        .client-profile-page .action-links {
                            display: inline-flex;
                            gap: 10px;
                            flex-wrap: wrap;
                        }

                        @media (max-width: 1199.98px) {
                            .client-profile-page .stat-strip {
                                grid-template-columns: repeat(2, minmax(0, 1fr));
                            }

                            .client-profile-page .info-grid {
                                grid-template-columns: 1fr;
                            }
                        }

                        @media (max-width: 767.98px) {
                            .client-profile-page .cp-title {
                                font-size: 1.75rem;
                            }

                            .client-profile-page .cp-header {
                                margin: 24px 0 20px;
                            }

                            .client-profile-page .stat-strip {
                                grid-template-columns: 1fr;
                            }

                            .client-profile-page .detail-list {
                                grid-template-columns: 1fr;
                            }

                            .client-profile-page .cp-actions {
                                width: 100%;
                                justify-content: stretch;
                            }

                            .client-profile-page .cp-actions a {
                                flex: 1 1 auto;
                                justify-content: center;
                            }
                        }
                    </style>

                    <div class="cp-header">
                        <div>
                            <div class="cp-eyebrow">Client's Profile</div>
                            <h1 class="cp-title"><?= htmlspecialchars($clientName !== '' ? $clientName : 'Company', ENT_QUOTES, 'UTF-8'); ?></h1>
                            <p class="cp-subtitle">
                                <?= $custID !== '' ? 'Client ID ' . htmlspecialchars($custID, ENT_QUOTES, 'UTF-8') . ' · ' : ''; ?>

                            </p>
                        </div>

                        <div class="cp-actions">
                            <a class="btn-soft" href="<?= htmlspecialchars($backUrl, ENT_QUOTES, 'UTF-8'); ?>">
                                <i class="fas fa-arrow-left"></i>
                                <?= htmlspecialchars($backLabel, ENT_QUOTES, 'UTF-8'); ?>
                            </a>
                            <?php if ($customerHistoryUrl !== ''): ?>
                                <a class="btn-solid" href="<?= htmlspecialchars($customerHistoryUrl, ENT_QUOTES, 'UTF-8'); ?>">
                                    <i class="fas fa-credit-card"></i>
                                    <?= $clientPortalMode ? 'My Payment History' : 'Payment History'; ?>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>

                    <?php if (isset($invoice_access_enabled) && $invoice_access_enabled): ?>
                    <div class="stat-strip">
                        <div class="stat-card sc-total">
                            <div class="stat-label">Invoices Generated</div>
                            <div class="stat-value"><?= number_format($invoiceCount); ?></div>
                            <div class="stat-meta">Total: PHP <?= number_format($totalInvoiced, 2); ?></div>
                        </div>
                        <div class="stat-card sc-open">
                            <div class="stat-label">Open Invoices</div>
                            <div class="stat-value"><?= number_format(count($openInvoices)); ?></div>
                            <div class="stat-meta">Total: PHP <?= number_format($totalOutstanding, 2); ?></div>
                        </div>
                        <div class="stat-card sc-paid">
                            <div class="stat-label">Payments Collected</div>
                            <div class="stat-value"><?= number_format($paymentCount); ?></div>
                            <div class="stat-meta">Total: PHP <?= number_format($totalPaymentsAmount, 2); ?></div>
                        </div>
                        <div class="stat-card sc-balance">
                            <div class="stat-label">Total Collected</div>
                            <div class="stat-value">PHP <?= number_format($totalCollected, 2); ?></div>
                            <div class="stat-meta">Amount paid across all invoices</div>
                        </div>
                    </div>
                    <?php else: ?>
                    <div class="stat-strip">
                        <div class="stat-card sc-total" style="opacity: 0.6;">
                            <div class="stat-label">Invoice Access</div>
                            <div class="stat-value">Disabled</div>
                            <div class="stat-meta">Contact administrator for access</div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <div class="cp-card">
                        <div class="tabs-shell">
                            <ul class="nav nav-tabs" role="tablist">
                                <li class="nav-item">
                                    <a class="nav-link <?= $activeTab === 'info' ? 'active' : ''; ?>" data-toggle="tab" href="#company-info" role="tab">
                                        Company Information
                                    </a>
                                </li>
                                <?php if (isset($invoice_access_enabled) && $invoice_access_enabled): ?>
                                <li class="nav-item">
                                    <a class="nav-link <?= $activeTab === 'invoices' ? 'active' : ''; ?>" data-toggle="tab" href="#company-invoices" role="tab">
                                        Invoices
                                        <span class="tab-count"><?= number_format($invoiceCount); ?></span>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link <?= $activeTab === 'payments' ? 'active' : ''; ?>" data-toggle="tab" href="#company-payments" role="tab">
                                        Payments
                                        <span class="tab-count"><?= number_format($paymentCount); ?></span>
                                    </a>
                                </li>
                                <?php endif; ?>
                                <li class="nav-item">
                                    <a class="nav-link <?= $activeTab === 'tickets' ? 'active' : ''; ?>" data-toggle="tab" href="#company-tickets" role="tab">
                                        Tickets
                                        <span class="tab-count"><?= number_format($ticketCounts['total']); ?></span>
                                    </a>
                                </li>
                            </ul>

                            <div class="tab-content">
                                <div class="tab-pane fade <?= $activeTab === 'info' ? 'show active' : ''; ?>" id="company-info" role="tabpanel">
                                    <div class="info-grid">
                                        <div class="info-panel">
                                            <div class="info-title">Company Details</div>
                                            <div class="detail-list">
                                                <div class="detail-item">
                                                    <div class="detail-label">Client ID</div>
                                                    <div class="detail-value"><?= $custID !== '' ? htmlspecialchars($custID, ENT_QUOTES, 'UTF-8') : '-'; ?></div>
                                                </div>
                                                <div class="detail-item">
                                                    <div class="detail-label">Status</div>
                                                    <div class="detail-value">
                                                        <?php if ($clientStatus !== ''): ?>
                                                            <span class="status-badge <?= htmlspecialchars($statusClass, ENT_QUOTES, 'UTF-8'); ?>"><?= htmlspecialchars($clientStatus, ENT_QUOTES, 'UTF-8'); ?></span>
                                                        <?php else: ?>
                                                            -
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                                <div class="detail-item">
                                                    <div class="detail-label">Client Source</div>
                                                    <div class="detail-value"><?= $clientSource !== '' ? htmlspecialchars($clientSource, ENT_QUOTES, 'UTF-8') : '-'; ?></div>
                                                </div>
                                                <div class="detail-item">
                                                    <div class="detail-label">Sales Agent</div>
                                                    <div class="detail-value"><?= $salesAgent !== '' ? htmlspecialchars($salesAgent, ENT_QUOTES, 'UTF-8') : '-'; ?></div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="info-panel">
                                            <div class="info-title">Contact Information</div>
                                            <div class="detail-list">
                                                <div class="detail-item">
                                                    <div class="detail-label">Contact Person</div>
                                                    <div class="detail-value"><?= $contactPerson !== '' ? htmlspecialchars($contactPerson, ENT_QUOTES, 'UTF-8') : '-'; ?></div>
                                                </div>
                                                <div class="detail-item">
                                                    <div class="detail-label">Contact Numbers</div>
                                                    <div class="detail-value"><?= $contactNos !== '' ? htmlspecialchars($contactNos, ENT_QUOTES, 'UTF-8') : '-'; ?></div>
                                                </div>
                                                <div class="detail-item">
                                                    <div class="detail-label">Company Email</div>
                                                    <div class="detail-value"><?= $companyEmail !== '' ? htmlspecialchars($companyEmail, ENT_QUOTES, 'UTF-8') : '-'; ?></div>
                                                </div>
                                                <div class="detail-item">
                                                    <div class="detail-label">Client Email</div>
                                                    <div class="detail-value"><?= $clientEmail !== '' ? htmlspecialchars($clientEmail, ENT_QUOTES, 'UTF-8') : '-'; ?></div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="info-panel full">
                                            <div class="info-title">Address</div>
                                            <div class="detail-value"><?= $address !== '' ? nl2br(htmlspecialchars($address, ENT_QUOTES, 'UTF-8')) : '-'; ?></div>
                                        </div>

                                        <div class="info-panel">
                                            <div class="info-title">Facebook Link</div>
                                            <div class="detail-value">
                                                <?php if ($facebookLink !== ''): ?>
                                                    <a href="<?= htmlspecialchars($facebookLink, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener noreferrer">Open company link</a>
                                                <?php else: ?>
                                                    -
                                                <?php endif; ?>
                                            </div>
                                        </div>

                                        <?php if (isset($invoice_access_enabled) && $invoice_access_enabled): ?>
                                        <div class="info-panel">
                                            <div class="info-title">Invoice Totals</div>
                                            <div class="detail-list">
                                                <div class="detail-item">
                                                    <div class="detail-label">Total Invoiced</div>
                                                    <div class="detail-value"><?= number_format($totalInvoiced, 2); ?></div>
                                                </div>
                                                <div class="detail-item">
                                                    <div class="detail-label">Collected</div>
                                                    <div class="detail-value"><?= number_format($totalCollected, 2); ?></div>
                                                </div>
                                            </div>
                                        </div>
                                        <?php endif; ?>

                                        <div class="info-panel full">
                                            <div class="info-title">Notes</div>
                                            <div class="detail-value"><?= $notes !== '' ? nl2br(htmlspecialchars($notes, ENT_QUOTES, 'UTF-8')) : 'No notes added for this company.'; ?></div>
                                        </div>
                                    </div>
                                </div>

                                <div class="tab-pane fade <?= $activeTab === 'invoices' ? 'show active' : ''; ?>" id="company-invoices" role="tabpanel">
                                    <div class="invoice-section">
                                        <div class="section-header">
                                            <div>
                                                <h3 class="section-title">Open Invoices</h3>
                                                <div class="section-subtitle">Includes unpaid and partially paid invoices for this company.</div>
                                            </div>
                                            <span class="status-badge status-prospect"><?= number_format(count($openInvoices)); ?> open</span>
                                        </div>

                                        <div class="table-responsive">
                                            <table class="table table-hover mb-0">
                                                <thead>
                                                    <tr>
                                                        <th>Invoice No.</th>
                                                        <th>Source</th>
                                                        <th>Date</th>
                                                        <th>Description</th>
                                                        <th class="text-right">Total Due</th>
                                                        <th class="text-right">Amount Paid</th>
                                                        <th class="text-right">Balance</th>
                                                        <th>Status</th>
                                                        <th>Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php if (!empty($openInvoices)): ?>
                                                        <?php foreach ($openInvoices as $invoiceRow): ?>
                                                            <?php
                                                            $amountPaid = (float) ($invoiceRow->AmountPaid ?? 0);
                                                            $balance = max(0, (float) ($invoiceRow->Balance ?? 0));
                                                            $statusLabel = $amountPaid > 0 ? 'Partially Paid' : 'Unpaid';
                                                            $statusClass = $amountPaid > 0 ? 'status-partial' : 'status-unpaid';
                                                            $invoiceUrl = base_url() . 'Page/invoice?id=' . rawurlencode((string) $invoiceRow->orderID);
                                                            $paymentUrl = base_url() . 'Page/paymentHistory?id=' . rawurlencode((string) $invoiceRow->orderID);
                                                            ?>
                                                            <tr>
                                                                <td>
                                                                    <a class="invoice-link" href="<?= htmlspecialchars($invoiceUrl, ENT_QUOTES, 'UTF-8'); ?>">
                                                                        #<?= htmlspecialchars((string) $invoiceRow->InvoiceNo, ENT_QUOTES, 'UTF-8'); ?>
                                                                    </a>
                                                                </td>
                                                                <td><span class="invoice-source"><?= htmlspecialchars((string) (($invoiceRow->invoiceSource ?? '') === 'Others' ? 'Invoice' : ($invoiceRow->invoiceSource ?? 'Invoice')), ENT_QUOTES, 'UTF-8'); ?></span></td>
                                                                <td class="num-cell"><?= htmlspecialchars((string) $invoiceRow->TransDate, ENT_QUOTES, 'UTF-8'); ?></td>
                                                                <td>
                                                                    <?= nl2br(htmlspecialchars((string) ($invoiceRow->invoiceSummary ?? $invoiceRow->JobDescription ?? ''), ENT_QUOTES, 'UTF-8')); ?>
                                                                    <?php if (!empty($invoiceRow->coverageLabel)): ?>
                                                                        <div class="mt-1" style="color: var(--text-soft); font-size: 0.8rem; font-weight: 600;">
                                                                            <?= htmlspecialchars((string) $invoiceRow->coverageLabel, ENT_QUOTES, 'UTF-8'); ?>
                                                                        </div>
                                                                    <?php endif; ?>
                                                                </td>
                                                                <td class="text-right num-cell"><?= number_format((float) $invoiceRow->TotalDue, 2); ?></td>
                                                                <td class="text-right num-cell">
                                                                    <?php if ($amountPaid > 0): ?>
                                                                        <a class="payment-link" href="<?= htmlspecialchars($paymentUrl, ENT_QUOTES, 'UTF-8'); ?>"><?= number_format($amountPaid, 2); ?></a>
                                                                    <?php else: ?>
                                                                        <?= number_format($amountPaid, 2); ?>
                                                                    <?php endif; ?>
                                                                </td>
                                                                <td class="text-right num-cell"><?= number_format($balance, 2); ?></td>
                                                                <td><span class="invoice-status <?= $statusClass; ?>"><?= $statusLabel; ?></span></td>
                                                                <td>
                                                                    <div class="action-links">
                                                                        <a class="invoice-link" href="<?= htmlspecialchars($invoiceUrl, ENT_QUOTES, 'UTF-8'); ?>">View</a>
                                                                        <?php if ($balance > 0): ?>
                                                                            <a class="payment-link pay-online-link" href="#"
                                                                                data-invoice-id="<?= htmlspecialchars((string) $invoiceRow->orderID, ENT_QUOTES, 'UTF-8'); ?>"
                                                                                data-invoice-no="<?= htmlspecialchars((string) $invoiceRow->InvoiceNo, ENT_QUOTES, 'UTF-8'); ?>"
                                                                                data-balance="<?= number_format($balance, 2, '.', ''); ?>">
                                                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:14px;height:14px;margin-right:4px;vertical-align:middle;">
                                                                                    <rect x="3" y="3" width="7" height="7" />
                                                                                    <rect x="14" y="3" width="7" height="7" />
                                                                                    <rect x="3" y="14" width="7" height="7" />
                                                                                    <path d="M14 14h.01M14 17h.01M17 14h.01M17 17h.01M20 14h.01M20 17h.01M20 20h.01M17 20h.01M14 20h.01" />
                                                                                </svg>
                                                                                Scan to Pay
                                                                            </a>
                                                                        <?php endif; ?>
                                                                        <?php if ($amountPaid > 0): ?>
                                                                            <a class="payment-link" href="<?= htmlspecialchars($paymentUrl, ENT_QUOTES, 'UTF-8'); ?>">Payments</a>
                                                                        <?php endif; ?>
                                                                    </div>
                                                                </td>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                    <?php else: ?>
                                                        <tr>
                                                            <td colspan="9" class="empty-state">
                                                                <strong>No open invoices.</strong>
                                                                This company does not have any unpaid or partially paid invoices right now.
                                                            </td>
                                                        </tr>
                                                    <?php endif; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>

                                    <div class="invoice-section">
                                        <div class="section-header">
                                            <div>
                                                <h3 class="section-title">Paid Invoices</h3>
                                                <div class="section-subtitle">Invoices that have already been fully settled.</div>
                                            </div>
                                            <span class="status-badge status-active"><?= number_format(count($paidInvoices)); ?> paid</span>
                                        </div>

                                        <div class="table-responsive">
                                            <table class="table table-hover mb-0">
                                                <thead>
                                                    <tr>
                                                        <th>Invoice No.</th>
                                                        <th>Source</th>
                                                        <th>Date</th>
                                                        <th>Description</th>
                                                        <th class="text-right">Total Due</th>
                                                        <th class="text-right">Amount Paid</th>
                                                        <th>Status</th>
                                                        <th>Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php if (!empty($paidInvoices)): ?>
                                                        <?php foreach ($paidInvoices as $invoiceRow): ?>
                                                            <?php
                                                            $amountPaid = (float) ($invoiceRow->AmountPaid ?? 0);
                                                            $invoiceUrl = base_url() . 'Page/invoice?id=' . rawurlencode((string) $invoiceRow->orderID);
                                                            $paymentUrl = base_url() . 'Page/paymentHistory?id=' . rawurlencode((string) $invoiceRow->orderID);
                                                            ?>
                                                            <tr>
                                                                <td>
                                                                    <a class="invoice-link" href="<?= htmlspecialchars($invoiceUrl, ENT_QUOTES, 'UTF-8'); ?>">
                                                                        #<?= htmlspecialchars((string) $invoiceRow->InvoiceNo, ENT_QUOTES, 'UTF-8'); ?>
                                                                    </a>
                                                                </td>
                                                                <td><span class="invoice-source"><?= htmlspecialchars((string) (($invoiceRow->invoiceSource ?? '') === 'Others' ? 'Invoice' : ($invoiceRow->invoiceSource ?? 'Invoice')), ENT_QUOTES, 'UTF-8'); ?></span></td>
                                                                <td class="num-cell"><?= htmlspecialchars((string) $invoiceRow->TransDate, ENT_QUOTES, 'UTF-8'); ?></td>
                                                                <td>
                                                                    <?= nl2br(htmlspecialchars((string) ($invoiceRow->invoiceSummary ?? $invoiceRow->JobDescription ?? ''), ENT_QUOTES, 'UTF-8')); ?>
                                                                    <?php if (!empty($invoiceRow->coverageLabel)): ?>
                                                                        <div class="mt-1" style="color: var(--text-soft); font-size: 0.8rem; font-weight: 600;">
                                                                            <?= htmlspecialchars((string) $invoiceRow->coverageLabel, ENT_QUOTES, 'UTF-8'); ?>
                                                                        </div>
                                                                    <?php endif; ?>
                                                                </td>
                                                                <td class="text-right num-cell"><?= number_format((float) $invoiceRow->TotalDue, 2); ?></td>
                                                                <td class="text-right num-cell">
                                                                    <?php if ($amountPaid > 0): ?>
                                                                        <a class="payment-link" href="<?= htmlspecialchars($paymentUrl, ENT_QUOTES, 'UTF-8'); ?>"><?= number_format($amountPaid, 2); ?></a>
                                                                    <?php else: ?>
                                                                        <?= number_format($amountPaid, 2); ?>
                                                                    <?php endif; ?>
                                                                </td>
                                                                <td><span class="invoice-status status-paid">Paid</span></td>
                                                                <td>
                                                                    <div class="action-links">
                                                                        <a class="invoice-link" href="<?= htmlspecialchars($invoiceUrl, ENT_QUOTES, 'UTF-8'); ?>">View</a>
                                                                        <?php if ($amountPaid > 0): ?>
                                                                            <a class="payment-link" href="<?= htmlspecialchars($paymentUrl, ENT_QUOTES, 'UTF-8'); ?>">Payments</a>
                                                                        <?php endif; ?>
                                                                    </div>
                                                                </td>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                    <?php else: ?>
                                                        <tr>
                                                            <td colspan="8" class="empty-state">
                                                                <strong>No paid invoices yet.</strong>
                                                                Fully settled invoices for this company will appear here.
                                                            </td>
                                                        </tr>
                                                    <?php endif; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>

                                <div class="tab-pane fade <?= $activeTab === 'payments' ? 'show active' : ''; ?>" id="company-payments" role="tabpanel">
                                    <div class="invoice-section">
                                        <div class="section-header">
                                            <div>
                                                <h3 class="section-title">Payment History</h3>
                                                <div class="section-subtitle">All payments collected from this client.</div>
                                            </div>
                                            <span class="status-badge status-active"><?= number_format($paymentCount); ?> payments</span>
                                        </div>

                                        <div class="table-responsive">
                                            <table class="table table-hover mb-0">
                                                <thead>
                                                    <tr>
                                                        <th>OR No.</th>
                                                        <th>Date</th>
                                                        <th>Invoice No.</th>
                                                        <th>Description</th>
                                                        <th class="text-right">Amount</th>
                                                        <th>Cashier</th>
                                                        <th>Source</th>
                                                        <th>Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php if (!empty($clientPayments)): ?>
                                                        <?php foreach ($clientPayments as $payment): ?>
                                                            <?php
                                                            $paymentUrl = base_url() . 'Page/paymentHistory?invoice_no=' . rawurlencode((string) $payment->InvoiceNo);
                                                            $invoiceUrl = base_url() . 'Page/invoice?id=' . rawurlencode((string) $payment->InvoiceNo);
                                                            ?>
                                                            <tr>
                                                                <td class="num-cell"><?= htmlspecialchars((string) ($payment->ORNo ?? '-'), ENT_QUOTES, 'UTF-8'); ?></td>
                                                                <td class="num-cell"><?= htmlspecialchars((string) ($payment->PDate ?? '-'), ENT_QUOTES, 'UTF-8'); ?></td>
                                                                <td>
                                                                    <a class="invoice-link" href="<?= htmlspecialchars($invoiceUrl, ENT_QUOTES, 'UTF-8'); ?>">
                                                                        #<?= htmlspecialchars((string) ($payment->InvoiceNo ?? '-'), ENT_QUOTES, 'UTF-8'); ?>
                                                                    </a>
                                                                </td>
                                                                <td><?= htmlspecialchars((string) ($payment->TransDescription ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                                                                <td class="text-right num-cell"><?= number_format((float) ($payment->AmountPaid ?? 0), 2); ?></td>
                                                                <td><?= htmlspecialchars((string) ($payment->Cashier ?? '-'), ENT_QUOTES, 'UTF-8'); ?></td>
                                                                <td><?= htmlspecialchars((string) ($payment->PaymentSource ?? '-'), ENT_QUOTES, 'UTF-8'); ?></td>
                                                                <td>
                                                                    <a class="payment-link" href="<?= htmlspecialchars($paymentUrl, ENT_QUOTES, 'UTF-8'); ?>">View</a>
                                                                </td>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                    <?php else: ?>
                                                        <tr>
                                                            <td colspan="8" class="empty-state">
                                                                <strong>No payments yet.</strong>
                                                                Payments collected from this client will appear here.
                                                            </td>
                                                        </tr>
                                                    <?php endif; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                <div class="tab-pane fade <?= $activeTab === 'tickets' ? 'show active' : ''; ?>" id="company-tickets" role="tabpanel">
                                    <div class="invoice-section">
                                        <div class="section-header">
                                            <div>
                                                <h4 class="section-title">Open Tickets</h4>
                                                <p class="section-subtitle"><?= number_format($ticketCounts['open']); ?> ticket(s) currently open</p>
                                            </div>
                                        </div>
                                        <div class="table-responsive">
                                            <table class="table">
                                                <thead>
                                                    <tr>
                                                        <th>Ticket ID</th>
                                                        <th>Subject</th>
                                                        <th>Concern</th>
                                                        <th>Priority</th>
                                                        <th>Created Date</th>
                                                        <th>Status</th>
                                                        <th>Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php if (!empty($openTickets)): ?>
                                                        <?php foreach ($openTickets as $ticket): ?>
                                                            <tr>
                                                                <td>#<?= htmlspecialchars((string) ($ticket->id ?? 'N/A'), ENT_QUOTES, 'UTF-8'); ?></td>
                                                                <td><?= htmlspecialchars((string) ($ticket->subject ?? 'N/A'), ENT_QUOTES, 'UTF-8'); ?></td>
                                                                <td><?= htmlspecialchars(substr((string) ($ticket->description ?? 'N/A'), 0, 100), ENT_QUOTES, 'UTF-8'); ?><?= strlen((string) ($ticket->description ?? '')) > 100 ? '...' : ''; ?></td>
                                                                <td>
                                                                    <span class="invoice-status <?= strtolower((string) ($ticket->priority ?? '')) === 'high' ? 'status-unpaid' : (strtolower((string) ($ticket->priority ?? '')) === 'medium' ? 'status-partial' : 'status-paid'); ?>">
                                                                        <?= htmlspecialchars((string) ($ticket->priority ?? 'N/A'), ENT_QUOTES, 'UTF-8'); ?>
                                                                    </span>
                                                                </td>
                                                                <td><?= !empty($ticket->created_at) ? htmlspecialchars(date('M d, Y', strtotime((string) $ticket->created_at)), ENT_QUOTES, 'UTF-8') : '-'; ?></td>
                                                                <td>
                                                                    <span class="invoice-status status-unpaid">
                                                                        <?= htmlspecialchars((string) ($ticket->status ?? 'N/A'), ENT_QUOTES, 'UTF-8'); ?>
                                                                    </span>
                                                                </td>
                                                                <td>
                                                                    <a href="<?= base_url('Page/supportIssueView?id=' . (int) ($ticket->id ?? 0)); ?>" class="invoice-link">View</a>
                                                                </td>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                    <?php else: ?>
                                                        <tr>
                                                            <td colspan="7" class="empty-state">
                                                                <strong>No open tickets.</strong>
                                                                All tickets have been resolved.
                                                            </td>
                                                        </tr>
                                                    <?php endif; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>

                                    <div class="invoice-section">
                                        <div class="section-header">
                                            <div>
                                                <h4 class="section-title">Closed Tickets</h4>
                                                <p class="section-subtitle"><?= number_format($ticketCounts['closed']); ?> resolved ticket(s)</p>
                                            </div>
                                        </div>
                                        <div class="table-responsive">
                                            <table class="table">
                                                <thead>
                                                    <tr>
                                                        <th>Ticket ID</th>
                                                        <th>Subject</th>
                                                        <th>Concern</th>
                                                        <th>Priority</th>
                                                        <th>Created Date</th>
                                                        <th>Status</th>
                                                        <th>Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php if (!empty($closedTickets)): ?>
                                                        <?php foreach ($closedTickets as $ticket): ?>
                                                            <tr>
                                                                <td>#<?= htmlspecialchars((string) ($ticket->id ?? 'N/A'), ENT_QUOTES, 'UTF-8'); ?></td>
                                                                <td><?= htmlspecialchars((string) ($ticket->subject ?? 'N/A'), ENT_QUOTES, 'UTF-8'); ?></td>
                                                                <td><?= htmlspecialchars(substr((string) ($ticket->description ?? 'N/A'), 0, 100), ENT_QUOTES, 'UTF-8'); ?><?= strlen((string) ($ticket->description ?? '')) > 100 ? '...' : ''; ?></td>
                                                                <td>
                                                                    <span class="invoice-status <?= strtolower((string) ($ticket->priority ?? '')) === 'high' ? 'status-unpaid' : (strtolower((string) ($ticket->priority ?? '')) === 'medium' ? 'status-partial' : 'status-paid'); ?>">
                                                                        <?= htmlspecialchars((string) ($ticket->priority ?? 'N/A'), ENT_QUOTES, 'UTF-8'); ?>
                                                                    </span>
                                                                </td>
                                                                <td><?= !empty($ticket->created_at) ? htmlspecialchars(date('M d, Y', strtotime((string) $ticket->created_at)), ENT_QUOTES, 'UTF-8') : '-'; ?></td>
                                                                <td>
                                                                    <span class="invoice-status status-paid">
                                                                        <?= htmlspecialchars((string) ($ticket->status ?? 'N/A'), ENT_QUOTES, 'UTF-8'); ?>
                                                                    </span>
                                                                </td>
                                                                <td>
                                                                    <a href="<?= base_url('Page/supportIssueView?id=' . (int) ($ticket->id ?? 0)); ?>" class="invoice-link">View</a>
                                                                </td>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                    <?php else: ?>
                                                        <tr>
                                                            <td colspan="7" class="empty-state">
                                                                <strong>No closed tickets.</strong>
                                                                No tickets have been resolved yet.
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
                    </div>
                </div>

                <?php include('includes/footer.php'); ?>
            </div>
        </div>

    </div>

    <?php include('includes/themecustomizer.php'); ?>

    <!-- Invoice Details Modal -->
    <div class="modal fade" id="invoiceDetailsModal" tabindex="-1" role="dialog" aria-labelledby="invoiceDetailsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="invoiceDetailsModalLabel">Invoice Details</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div id="modalInvoiceList">
                        <!-- Invoice list will be populated here -->
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <script src="<?= base_url(); ?>assets/js/vendor.min.js"></script>
    <script src="<?= base_url(); ?>assets/js/app.min.js"></script>

    <script>
        (function($) {
            'use strict';

            $(function() {
                // Invoice data from PHP
                var allInvoices = <?= json_encode($clientInvoices); ?>;
                var openInvoices = <?= json_encode($openInvoices); ?>;
                var paidInvoices = <?= json_encode($paidInvoices); ?>;
                var custID = <?= json_encode($custID); ?>;
                var customer = <?= json_encode($clientName); ?>;

                // Click handlers for stat cards
                $('.stat-card.sc-total').click(function() {
                    // Switch to Invoices tab (All)
                    $('a[href="#company-invoices"]').tab('show');
                });

                $('.stat-card.sc-open').click(function() {
                    // Switch to Invoices tab (Open)
                    $('a[href="#company-invoices"]').tab('show');
                });

                $('.stat-card.sc-paid').click(function() {
                    // Switch to Payments tab
                    $('a[href="#company-payments"]').tab('show');
                });

                $('.stat-card.sc-balance').click(function() {
                    // Switch to Invoices tab (Outstanding)
                    $('a[href="#company-invoices"]').tab('show');
                });

                function showInvoiceDetails(title, invoices) {
                    $('#invoiceDetailsModalLabel').text(title);

                    var html = '';
                    if (invoices.length === 0) {
                        html = '<div class="text-center text-muted py-4">No invoices to display.</div>';
                    } else {
                        html = '<div class="table-responsive">' +
                            '<table class="table table-striped table-hover">' +
                            '<thead class="thead-light">' +
                            '<tr>' +
                            '<th>Invoice #</th>' +
                            '<th>Date</th>' +
                            '<th>Description</th>' +
                            '<th class="text-right">Total Due</th>' +
                            '<th class="text-right">Amount Paid</th>' +
                            '<th class="text-right">Balance</th>' +
                            '<th>Status</th>' +
                            '<th>Actions</th>' +
                            '</tr>' +
                            '</thead>' +
                            '<tbody>';

                        invoices.forEach(function(invoice) {
                            var balance = parseFloat(invoice.Balance || 0);
                            var totalDue = parseFloat(invoice.TotalDue || 0);
                            var amountPaid = parseFloat(invoice.AmountPaid || 0);
                            var status = balance <= 0.00001 ? 'Paid' : 'Open';
                            var statusClass = balance <= 0.00001 ? 'success' : 'warning';
                            var invoiceUrl = '<?= base_url(); ?>Page/invoice?id=' + encodeURIComponent(invoice.orderID || invoice.OrderID || '');

                            html += '<tr>' +
                                '<td><a href="' + invoiceUrl + '" target="_blank">' + (invoice.InvoiceNo || invoice.invoiceNo || 'N/A') + '</a></td>' +
                                '<td>' + (invoice.TransDate || '') + '</td>' +
                                '<td>' + ((invoice.invoiceSummary || invoice.JobDescription || '').replace(/\n/g, '<br>')) +
                                ((invoice.coverageLabel || '') ? '<div style="margin-top:4px;color:#617489;font-size:0.8rem;font-weight:600;">' + invoice.coverageLabel + '</div>' : '') +
                                '</td>' +
                                '<td class="text-right">PHP ' + totalDue.toLocaleString('en-US', {
                                    minimumFractionDigits: 2
                                }) + '</td>' +
                                '<td class="text-right">PHP ' + amountPaid.toLocaleString('en-US', {
                                    minimumFractionDigits: 2
                                }) + '</td>' +
                                '<td class="text-right">PHP ' + balance.toLocaleString('en-US', {
                                    minimumFractionDigits: 2
                                }) + '</td>' +
                                '<td><span class="badge badge-' + statusClass + '">' + status + '</span></td>' +
                                '<td>' +
                                '<a href="' + invoiceUrl + '" class="btn btn-sm btn-outline-primary" target="_blank">View</a>' +
                                '</td>' +
                                '</tr>';
                        });

                        html += '</tbody></table></div>';

                        // Add summary
                        var totalAmount = invoices.reduce(function(sum, inv) {
                            return sum + parseFloat(inv.TotalDue || 0);
                        }, 0);
                        var totalPaid = invoices.reduce(function(sum, inv) {
                            return sum + parseFloat(inv.AmountPaid || 0);
                        }, 0);
                        var totalBalance = invoices.reduce(function(sum, inv) {
                            return sum + parseFloat(inv.Balance || 0);
                        }, 0);

                        html += '<div class="row mt-3">' +
                            '<div class="col-md-4">' +
                            '<div class="card border-left-primary">' +
                            '<div class="card-body">' +
                            '<h6 class="card-title">Total Amount</h6>' +
                            '<h4 class="text-primary">PHP ' + totalAmount.toLocaleString('en-US', {
                                minimumFractionDigits: 2
                            }) + '</h4>' +
                            '</div></div></div>' +
                            '<div class="col-md-4">' +
                            '<div class="card border-left-success">' +
                            '<div class="card-body">' +
                            '<h6 class="card-title">Total Paid</h6>' +
                            '<h4 class="text-success">PHP ' + totalPaid.toLocaleString('en-US', {
                                minimumFractionDigits: 2
                            }) + '</h4>' +
                            '</div></div></div>' +
                            '<div class="col-md-4">' +
                            '<div class="card border-left-warning">' +
                            '<div class="card-body">' +
                            '<h6 class="card-title">Total Balance</h6>' +
                            '<h4 class="text-warning">PHP ' + totalBalance.toLocaleString('en-US', {
                                minimumFractionDigits: 2
                            }) + '</h4>' +
                            '</div></div></div>' +
                            '</div>';
                    }

                    $('#modalInvoiceList').html(html);
                    $('#invoiceDetailsModal').modal('show');
                }
            });
        })(jQuery);
    </script>

    <!-- Enhanced QRPh Payment Modal -->
    <div class="pm-modal-backdrop" id="cpPayModal" role="dialog" aria-modal="true" aria-labelledby="cpPayModalTitle">
        <div class="pm-modal pm-modal-enhanced">
            <!-- Modal Header -->
            <div class="pm-modal-header">
                <div class="pm-modal-title-wrap">
                    <div class="pm-modal-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="3" width="7" height="7" />
                            <rect x="14" y="3" width="7" height="7" />
                            <rect x="3" y="14" width="7" height="7" />
                            <path d="M14 14h.01M14 17h.01M17 14h.01M17 17h.01M20 14h.01M20 17h.01M20 20h.01M17 20h.01M14 20h.01" />
                        </svg>
                    </div>
                    <h5 class="pm-modal-title" id="cpPayModalTitle">QRPh Payment Details</h5>
                </div>
                <button type="button" class="pm-modal-close" id="cpPmCloseBtnX" aria-label="Close">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="18" y1="6" x2="6" y2="18" />
                        <line x1="6" y1="6" x2="18" y2="18" />
                    </svg>
                </button>
            </div>

            <!-- Modal Body -->
            <div class="pm-modal-body">
                <div class="pm-modal-grid">
                    <!-- QR Column -->
                    <div class="pm-modal-qr-col">
                        <div class="pm-qr-frame" id="cpPmQrFrame">
                            <div class="pm-qr-placeholder" id="cpPmQrPlaceholder">Generating payment QR&hellip;</div>
                        </div>
                        <div class="pm-qr-hint">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="5" y="2" width="14" height="20" rx="2" ry="2" />
                                <line x1="12" y1="18" x2="12.01" y2="18" />
                            </svg>
                            <span>Using the same phone? Download the QR, then open your banking or e-wallet app and scan or import from your gallery.</span>
                        </div>
                    </div>

                    <!-- Details Column -->
                    <div class="pm-modal-details-col">
                        <dl class="pm-detail-list">
                            <div class="pm-detail-row">
                                <dt>Reference</dt>
                                <dd id="cpPmRef" class="pm-mono">—</dd>
                            </div>
                            <div class="pm-detail-row">
                                <dt>Invoice</dt>
                                <dd id="cpPmInvoice">—</dd>
                            </div>
                            <div class="pm-detail-row">
                                <dt>Description</dt>
                                <dd id="cpPmDescription">Invoice Payment</dd>
                            </div>
                            <div class="pm-detail-divider"></div>
                            <div class="pm-detail-row">
                                <dt>Base Amount</dt>
                                <dd id="cpPmBaseAmount">PHP 0.00</dd>
                            </div>
                            <div class="pm-detail-row">
                                <dt>2% Online Fee</dt>
                                <dd id="cpPmOnlineFee">PHP 0.00</dd>
                            </div>
                            <div class="pm-detail-row pm-detail-total">
                                <dt>QR Total</dt>
                                <dd id="cpPmQrTotal">PHP 0.00</dd>
                            </div>
                            <div class="pm-detail-divider"></div>
                            <div class="pm-detail-row">
                                <dt>Status</dt>
                                <dd><span id="cpPmStatusBadge" class="pm-badge pm-badge-warning">PENDING</span></dd>
                            </div>
                            <div class="pm-detail-row">
                                <dt>Provider Status</dt>
                                <dd id="cpPmProviderStatus">—</dd>
                            </div>
                            <div class="pm-detail-row">
                                <dt>Paid At</dt>
                                <dd id="cpPmPaidAt">—</dd>
                            </div>
                        </dl>
                    </div>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="pm-modal-footer">
                <button type="button" class="pm-btn pm-btn-ghost" id="cpPmOpenBtn" style="display:none;">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6" />
                        <polyline points="15 3 21 3 21 9" />
                        <line x1="10" y1="14" x2="21" y2="3" />
                    </svg>
                    Open QR
                </button>
                <button type="button" class="pm-btn pm-btn-success" id="cpPmDownloadBtn" style="display:none;">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                        <polyline points="7 10 12 15 17 10" />
                        <line x1="12" y1="15" x2="12" y2="3" />
                    </svg>
                    Download
                </button>
                <button type="button" class="pm-btn pm-btn-primary" id="cpPmRefreshBtn">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="23 4 23 10 17 10" />
                        <path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10" />
                    </svg>
                    Refresh
                </button>
                <button type="button" class="pm-btn pm-btn-close" id="cpPmCloseBtn">Close</button>
            </div>
        </div>
    </div>

    <style>
        /* ── Enhanced Modal Styles (QRPh Payment Details) ───────────────────────── */
        .pm-modal-backdrop {
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, .55);
            display: none;
        }

        .pm-modal-backdrop.is-open {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .pm-modal.pm-modal-enhanced {
            background: #fff;
            max-width: 800px;
            width: 95%;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
        }

        .pm-modal-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid #e2e8f0;
            background: #f8fafc;
        }

        .pm-modal-title-wrap {
            display: flex;
            align-items: center;
            gap: 0.6rem;
        }

        .pm-modal-icon {
            width: 34px;
            height: 34px;
            background: #e6f7f8;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #1a8d96;
            flex-shrink: 0;
        }

        .pm-modal-icon svg {
            width: 17px;
            height: 17px;
        }

        .pm-modal-title {
            font-size: 1rem;
            font-weight: 700;
            color: #0f172a;
            margin: 0;
        }

        .pm-modal-close {
            width: 32px;
            height: 32px;
            border: none;
            background: transparent;
            color: #64748b;
            cursor: pointer;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0;
            transition: background 0.15s;
            flex-shrink: 0;
        }

        .pm-modal-close svg {
            width: 18px;
            height: 18px;
        }

        .pm-modal-close:hover {
            background: #e2e8f0;
        }

        .pm-modal-body {
            padding: 1.5rem;
        }

        .pm-modal-grid {
            display: grid;
            grid-template-columns: 240px 1fr;
            gap: 1.5rem;
            align-items: start;
        }

        .pm-modal-qr-col {
            display: flex;
            flex-direction: column;
            gap: 0.85rem;
        }

        .pm-qr-frame {
            background: #fff;
            border: 1.5px solid #e2e8f0;
            border-radius: 12px;
            padding: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            aspect-ratio: 1;
            min-height: 240px;
        }

        .pm-qr-frame img {
            width: 100%;
            height: auto;
            display: block;
            border-radius: 4px;
        }

        .pm-qr-placeholder {
            color: #64748b;
            font-size: 0.9rem;
        }

        .pm-qr-hint {
            display: flex;
            align-items: flex-start;
            gap: 0.45rem;
            font-size: 0.8rem;
            color: #64748b;
            line-height: 1.4;
        }

        .pm-qr-hint svg {
            width: 15px;
            height: 15px;
            flex-shrink: 0;
            margin-top: 0.1rem;
        }

        .pm-modal-details-col {
            overflow: hidden;
        }

        .pm-detail-list {
            margin: 0;
        }

        .pm-detail-row {
            display: grid;
            grid-template-columns: 130px 1fr;
            gap: 0.5rem;
            align-items: baseline;
            padding: 0.5rem 0;
            border-bottom: 1px solid #e2e8f0;
        }

        .pm-detail-row:last-child {
            border-bottom: none;
        }

        .pm-detail-row dt {
            font-size: 0.78rem;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.03em;
        }

        .pm-detail-row dd {
            font-size: 0.9rem;
            color: #0f172a;
            margin: 0;
            word-break: break-word;
        }

        .pm-detail-divider {
            height: 1px;
            background: #e2e8f0;
            margin: 0.4rem 0;
        }

        .pm-detail-total dt,
        .pm-detail-total dd {
            font-size: 1rem;
            font-weight: 700;
            color: #136970;
        }

        .pm-mono {
            font-family: var(--font-primary, Montserrat, Segoe UI, Arial, sans-serif);
            font-size: 0.85rem;
        }

        .pm-modal-footer {
            padding: 1rem 1.5rem;
            border-top: 1px solid #e2e8f0;
            background: #f8fafc;
            display: flex;
            align-items: center;
            gap: 0.6rem;
            flex-wrap: wrap;
        }

        .pm-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.5rem 0.95rem;
            border-radius: 8px;
            font-size: 0.85rem;
            font-weight: 600;
            cursor: pointer;
            border: none;
            transition: opacity 0.15s, transform 0.1s;
            line-height: 1;
        }

        .pm-btn svg {
            width: 15px;
            height: 15px;
            flex-shrink: 0;
        }

        .pm-btn:hover {
            opacity: 0.88;
        }

        .pm-btn:active {
            transform: scale(0.97);
        }

        .pm-btn-ghost {
            background: #f1f5f9;
            color: #334155;
        }

        .pm-btn-success {
            background: #dcfce7;
            color: #15803d;
        }

        .pm-btn-primary {
            background: #059669;
            color: #fff;
        }

        .pm-btn-primary:hover {
            background: #047857;
        }

        .pm-btn-close {
            background: #f1f5f9;
            color: #334155;
            margin-left: auto;
        }

        /* Badges */
        .pm-badge {
            display: inline-block;
            padding: 0.28rem 0.65rem;
            border-radius: 6px;
            font-size: 0.75rem;
            font-weight: 700;
            letter-spacing: 0.03em;
            line-height: 1;
        }

        .pm-badge-success {
            background: #dcfce7;
            color: #15803d;
        }

        .pm-badge-danger {
            background: #fee2e2;
            color: #b91c1c;
        }

        .pm-badge-warning {
            background: #fef3c7;
            color: #b45309;
        }

        .pm-badge-info {
            background: #e0f2fe;
            color: #075985;
        }

        /* Responsive */
        @media (max-width: 767.98px) {
            .pm-modal-grid {
                grid-template-columns: 1fr;
            }

            .pm-qr-frame {
                max-width: 240px;
                margin: 0 auto;
                aspect-ratio: 1;
            }

            .pm-detail-row {
                grid-template-columns: 110px 1fr;
            }

            .pm-modal-footer {
                gap: 0.45rem;
            }

            .pm-btn {
                flex: 1 1 calc(50% - 0.3rem);
                justify-content: center;
            }

            .pm-btn-close {
                flex: 1 1 100%;
                margin-left: 0;
            }

            .pm-modal-body,
            .pm-modal-header,
            .pm-modal-footer {
                padding: 1rem;
            }
        }
    </style>

    <script>
        (function() {
            var baseUrl = <?= json_encode(base_url()); ?>;
            var modal = document.getElementById('cpPayModal');
            if (!modal) return;

            // Elements
            var qrFrame = document.getElementById('cpPmQrFrame');
            var placeholder = document.getElementById('cpPmQrPlaceholder');
            var refEl = document.getElementById('cpPmRef');
            var invoiceEl = document.getElementById('cpPmInvoice');
            var baseAmountEl = document.getElementById('cpPmBaseAmount');
            var onlineFeeEl = document.getElementById('cpPmOnlineFee');
            var qrTotalEl = document.getElementById('cpPmQrTotal');
            var statusBadgeEl = document.getElementById('cpPmStatusBadge');
            var providerStatusEl = document.getElementById('cpPmProviderStatus');
            var paidAtEl = document.getElementById('cpPmPaidAt');
            var openBtn = document.getElementById('cpPmOpenBtn');
            var downloadBtn = document.getElementById('cpPmDownloadBtn');
            var refreshBtn = document.getElementById('cpPmRefreshBtn');
            var closeBtn = document.getElementById('cpPmCloseBtn');
            var closeBtnX = document.getElementById('cpPmCloseBtnX');

            var pollTimer = null;
            var currentId = null;
            var currentQrUrl = null;
            var currentBalance = 0;

            function setStatusBadge(status, providerStatus) {
                var badgeClass = 'pm-badge-warning';
                var text = 'PENDING';
                if (status === 'paid') {
                    badgeClass = 'pm-badge-success';
                    text = 'PAID';
                } else if (status === 'failed') {
                    badgeClass = 'pm-badge-danger';
                    text = 'FAILED';
                }
                statusBadgeEl.className = 'pm-badge ' + badgeClass;
                statusBadgeEl.textContent = text;
                providerStatusEl.textContent = providerStatus || (status === 'paid' ? 'PAID' : '—');
            }

            function updateDetails(data) {
                if (data && data.online_payment_id) {
                    refEl.textContent = 'OP-' + data.online_payment_id;
                    setStatusBadge(data.status, data.provider_status);
                    if (data.paid_at) {
                        paidAtEl.textContent = new Date(data.paid_at).toLocaleString();
                    } else {
                        paidAtEl.textContent = '—';
                    }
                }
            }

            function openModal() {
                modal.classList.add('is-open');
            }

            function closeModal() {
                modal.classList.remove('is-open');
                if (pollTimer) {
                    clearInterval(pollTimer);
                    pollTimer = null;
                }
            }

            function refreshStatus() {
                if (!currentId) return;
                fetch(baseUrl + 'PayMongo/status/' + currentId, {
                        credentials: 'same-origin'
                    })
                    .then(function(r) {
                        return r.json();
                    })
                    .then(function(data) {
                        if (!data.ok) return;
                        updateDetails(data);
                        if (data.status === 'paid') {
                            setStatusBadge('paid', data.provider_status);
                            clearInterval(pollTimer);
                            pollTimer = null;
                            setTimeout(function() {
                                location.reload();
                            }, 2000);
                        } else if (data.status === 'failed') {
                            setStatusBadge('failed', data.provider_status);
                            clearInterval(pollTimer);
                            pollTimer = null;
                        }
                    })
                    .catch(function() {});
            }

            document.addEventListener('click', function(e) {
                var link = e.target.closest && e.target.closest('.pay-online-link');
                if (!link) return;
                e.preventDefault();

                var invoiceId = link.getAttribute('data-invoice-id');
                var invoiceNo = link.getAttribute('data-invoice-no');
                var balance = parseFloat(link.getAttribute('data-balance') || '0');
                currentBalance = balance;

                // Calculate amounts
                var onlineFeeRate = 0.02;
                var onlineFee = Math.round(balance * onlineFeeRate * 100) / 100;
                var qrTotal = balance + onlineFee;

                // Update UI
                invoiceEl.textContent = '#' + invoiceNo;
                baseAmountEl.textContent = 'PHP ' + balance.toLocaleString('en-US', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });
                onlineFeeEl.textContent = 'PHP ' + onlineFee.toLocaleString('en-US', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });
                qrTotalEl.textContent = 'PHP ' + qrTotal.toLocaleString('en-US', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });

                // Reset state
                qrFrame.innerHTML = '';
                qrFrame.appendChild(placeholder);
                placeholder.textContent = 'Generating payment QR\u2026';
                setStatusBadge('pending', '—');
                refEl.textContent = '—';
                paidAtEl.textContent = '—';

                // Hide action buttons initially
                if (openBtn) openBtn.style.display = 'none';
                if (downloadBtn) downloadBtn.style.display = 'none';

                openModal();

                var fd = new FormData();
                fd.append('invoice_id', invoiceId);
                fd.append('invoice_no', invoiceNo);

                fetch(baseUrl + 'PayMongo/createCheckout', {
                        method: 'POST',
                        body: fd,
                        credentials: 'same-origin'
                    })
                    .then(function(r) {
                        return r.json();
                    })
                    .then(function(data) {
                        if (!data.ok) {
                            setStatusBadge('failed', data.error || 'Error');
                            placeholder.textContent = 'Error generating QR';
                            return;
                        }
                        currentId = data.online_payment_id;
                        currentQrUrl = data.qr_code_url;

                        var img = document.createElement('img');
                        img.alt = 'Scan to Pay';
                        img.src = data.qr_code_url;
                        qrFrame.innerHTML = '';
                        qrFrame.appendChild(img);

                        // Show action buttons
                        if (openBtn) {
                            openBtn.style.display = 'inline-flex';
                            openBtn.setAttribute('data-url', data.qr_code_url);
                        }
                        if (downloadBtn) {
                            downloadBtn.style.display = 'inline-flex';
                            downloadBtn.setAttribute('data-url', data.qr_code_url);
                        }

                        updateDetails(data);
                        startPolling();
                    })
                    .catch(function() {
                        setStatusBadge('failed', 'Network error');
                        placeholder.textContent = 'Network error';
                    });
            });

            function startPolling() {
                if (pollTimer) clearInterval(pollTimer);
                pollTimer = setInterval(refreshStatus, 4000);
            }

            // Button event listeners
            closeBtn.addEventListener('click', closeModal);
            if (closeBtnX) closeBtnX.addEventListener('click', closeModal);
            if (refreshBtn) refreshBtn.addEventListener('click', refreshStatus);

            if (openBtn) {
                openBtn.addEventListener('click', function() {
                    var url = this.getAttribute('data-url');
                    if (url) window.open(url, '_blank');
                });
            }

            if (downloadBtn) {
                downloadBtn.addEventListener('click', function() {
                    var url = this.getAttribute('data-url');
                    if (!url) return;
                    var a = document.createElement('a');
                    a.href = url;
                    a.download = 'qr-payment-' + (currentId || 'invoice') + '.png';
                    document.body.appendChild(a);
                    a.click();
                    document.body.removeChild(a);
                });
            }

            modal.addEventListener('click', function(e) {
                if (e.target === modal) closeModal();
            });
        })();
    </script>

</body>

</html>