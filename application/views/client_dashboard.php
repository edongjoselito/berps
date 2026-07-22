<?php
$clientData = isset($client) ? $client : null;
$invoiceRows = isset($invoices) && is_array($invoices) ? array_values($invoices) : array();
$paymentRows = isset($payments) && is_array($payments) ? array_values($payments) : array();

$clientName = trim((string) ($clientData->Customer ?? 'Client Portal'));
$custID = trim((string) ($clientData->CustID ?? ''));
$address = trim((string) ($clientData->Address ?? ''));
$contactPerson = trim((string) ($clientData->ContactPerson ?? ''));
$contactNos = trim((string) ($clientData->ContactNos ?? ''));
$companyEmail = trim((string) ($clientData->CompanyEmail ?? ''));
$clientEmail = trim((string) ($clientData->client_email ?? ''));
$portalLastLoginRaw = trim((string) ($clientData->portal_last_login ?? ''));
$portalLastLogin = $portalLastLoginRaw !== '' && $portalLastLoginRaw !== '0000-00-00 00:00:00'
    ? date('M d, Y h:i A', strtotime($portalLastLoginRaw))
    : 'First portal login';

$openInvoices = array();
$paidInvoices = array();
$totalInvoiced = 0.0;
$totalCollected = 0.0;
$totalOutstanding = 0.0;

foreach ($invoiceRows as $invoiceRow) {
    $balance = max(0, (float) ($invoiceRow->Balance ?? 0));
    $amountPaid = (float) ($invoiceRow->AmountPaid ?? 0);
    $totalDue = (float) ($invoiceRow->TotalDue ?? 0);

    $totalInvoiced += $totalDue;
    $totalCollected += $amountPaid;
    $totalOutstanding += $balance;

    if ($balance <= 0.00001) {
        $paidInvoices[] = $invoiceRow;
    } else {
        $openInvoices[] = $invoiceRow;
    }
}

usort($invoiceRows, function ($left, $right) {
    $leftDate = strtotime((string) ($left->TransDate ?? '1970-01-01'));
    $rightDate = strtotime((string) ($right->TransDate ?? '1970-01-01'));
    if ($leftDate === $rightDate) {
        return (int) ($right->orderID ?? 0) <=> (int) ($left->orderID ?? 0);
    }
    return $rightDate <=> $leftDate;
});

usort($paymentRows, function ($left, $right) {
    $leftDate = strtotime((string) ($left->PDate ?? '1970-01-01'));
    $rightDate = strtotime((string) ($right->PDate ?? '1970-01-01'));
    if ($leftDate === $rightDate) {
        return (int) ($right->paymentID ?? 0) <=> (int) ($left->paymentID ?? 0);
    }
    return $rightDate <=> $leftDate;
});

$recentInvoices = array_slice($invoiceRows, 0, 5);
$recentPayments = array_slice($paymentRows, 0, 5);
$latestPaymentLabel = !empty($recentPayments) && !empty($recentPayments[0]->PDate)
    ? date('M d, Y', strtotime((string) $recentPayments[0]->PDate))
    : 'No payments yet';

$myCompanyUrl = base_url() . 'Page/clientProfile';
$myInvoicesUrl = base_url() . 'Page/clientProfile?tab=invoices';
$paymentHistoryUrl = base_url() . 'Page/customerHistory';
$requestedTodayUrl = base_url() . 'Page/clientRequestedToday';
$closedTaskReportUrl = base_url() . 'Page/clientClosedTaskReport';
$myTicketsUrl = base_url() . 'Page/clientMyTickets';
$reportIssueUrl = base_url() . 'Page/clientReportIssue';
$changePasswordUrl = base_url() . 'Users/changepassword';
$supportIssues = isset($supportIssues) && is_array($supportIssues) ? array_values($supportIssues) : array();
$supportProjects = isset($supportProjects) && is_array($supportProjects) ? array_values($supportProjects) : array();
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
            <div class="container-fluid client-dashboard-page">

                <style>
                    .client-dashboard-page {
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
                        --font-body: var(--font-primary);
                        --font-head: var(--font-primary);
                        --font-mono: var(--font-primary);
                        font-family: var(--font-primary, Montserrat, Segoe UI, Arial, sans-serif);
                        background:
                            radial-gradient(circle at top left, rgba(37, 99, 235, 0.08), transparent 28%),
                            radial-gradient(circle at top right, rgba(16, 185, 129, 0.08), transparent 24%),
                            linear-gradient(180deg, #f8fbff 0%, #f4f7fb 100%);
                        min-height: 100vh;
                        padding-bottom: 24px;
                    }

                    .client-dashboard-page * {
                        box-sizing: border-box;
                    }

                    .client-dashboard-page .cd-header {
                        margin: 24px 0 22px;
                        display: flex;
                        justify-content: space-between;
                        align-items: flex-end;
                        gap: 16px;
                        flex-wrap: wrap;
                    }

                    .client-dashboard-page .cd-eyebrow {
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

                    .client-dashboard-page .cd-eyebrow::before {
                        content: '';
                        width: 8px;
                        height: 8px;
                        border-radius: 50%;
                        background: linear-gradient(135deg, var(--primary), var(--primary-2));
                        box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.10);
                    }

                    .client-dashboard-page .cd-title {
                        margin: 0;
                        font-family: var(--font-primary, Montserrat, Segoe UI, Arial, sans-serif);
                        font-size: 2rem;
                        font-weight: 800;
                        letter-spacing: -0.04em;
                        color: var(--text);
                        line-height: 1.1;
                    }

                    .client-dashboard-page .cd-subtitle {
                        margin-top: 8px;
                        color: var(--text-soft);
                        font-size: 0.93rem;
                        font-weight: 500;
                    }

                    .client-dashboard-page .cd-actions {
                        display: flex;
                        gap: 10px;
                        flex-wrap: wrap;
                        justify-content: flex-end;
                    }

                    .client-dashboard-page .btn-soft,
                    .client-dashboard-page .btn-solid {
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

                    .client-dashboard-page .btn-soft {
                        background: rgba(255, 255, 255, 0.88);
                        color: var(--text);
                        border: 1px solid var(--line-strong);
                        box-shadow: var(--shadow-soft);
                    }

                    .client-dashboard-page .btn-solid {
                        background: linear-gradient(135deg, var(--primary), var(--primary-2));
                        color: #fff;
                        border: none;
                        box-shadow: 0 10px 24px rgba(37, 99, 235, 0.22);
                    }

                    .client-dashboard-page .btn-soft:hover,
                    .client-dashboard-page .btn-solid:hover {
                        transform: translateY(-1px);
                        filter: brightness(1.02);
                        text-decoration: none;
                    }

                    .client-dashboard-page .btn-solid:hover {
                        color: #fff;
                    }

                    .client-dashboard-page .hero-card,
                    .client-dashboard-page .panel-card {
                        background: var(--surface);
                        backdrop-filter: blur(12px);
                        border: 1px solid rgba(255, 255, 255, 0.72);
                        border-radius: 24px;
                        box-shadow: var(--shadow);
                        overflow: hidden;
                    }

                    .client-dashboard-page .hero-card {
                        margin-bottom: 20px;
                        position: relative;
                    }

                    .client-dashboard-page .hero-card::before {
                        content: '';
                        position: absolute;
                        inset: 0;
                        background:
                            radial-gradient(circle at top right, rgba(37, 99, 235, 0.12), transparent 30%),
                            radial-gradient(circle at bottom left, rgba(16, 185, 129, 0.10), transparent 24%);
                        pointer-events: none;
                    }

                    .client-dashboard-page .hero-body {
                        position: relative;
                        padding: 24px;
                        display: grid;
                        grid-template-columns: minmax(0, 1.2fr) minmax(300px, 0.8fr);
                        gap: 18px;
                    }

                    .client-dashboard-page .hero-label {
                        color: var(--text-faint);
                        font-size: 0.74rem;
                        font-weight: 800;
                        text-transform: uppercase;
                        letter-spacing: 0.08em;
                        margin-bottom: 10px;
                    }

                    .client-dashboard-page .hero-name {
                        color: var(--text);
                        font-family: var(--font-primary, Montserrat, Segoe UI, Arial, sans-serif);
                        font-size: 1.65rem;
                        font-weight: 800;
                        line-height: 1.15;
                        margin-bottom: 8px;
                    }

                    .client-dashboard-page .hero-copy {
                        color: var(--text-soft);
                        font-size: 0.95rem;
                        line-height: 1.7;
                        max-width: 640px;
                    }

                    .client-dashboard-page .hero-meta {
                        display: grid;
                        grid-template-columns: repeat(2, minmax(0, 1fr));
                        gap: 12px 16px;
                        margin-top: 18px;
                    }

                    .client-dashboard-page .hero-meta-item {
                        min-width: 0;
                    }

                    .client-dashboard-page .hero-meta-label {
                        color: var(--text-faint);
                        font-size: 0.7rem;
                        font-weight: 800;
                        letter-spacing: 0.08em;
                        text-transform: uppercase;
                        margin-bottom: 5px;
                    }

                    .client-dashboard-page .hero-meta-value {
                        color: var(--text);
                        font-size: 0.92rem;
                        font-weight: 600;
                        line-height: 1.5;
                        word-break: break-word;
                    }

                    .client-dashboard-page .quick-grid {
                        display: grid;
                        gap: 12px;
                    }

                    .client-dashboard-page .quick-link {
                        display: flex;
                        align-items: center;
                        justify-content: space-between;
                        gap: 12px;
                        padding: 14px 16px;
                        border-radius: 16px;
                        background: #fff;
                        border: 1px solid var(--line);
                        color: var(--text);
                        text-decoration: none;
                        box-shadow: 0 10px 26px rgba(15, 23, 42, 0.04);
                        transition: transform 0.16s ease, box-shadow 0.16s ease, border-color 0.16s ease;
                    }

                    .client-dashboard-page .quick-link:hover {
                        transform: translateY(-1px);
                        border-color: rgba(37, 99, 235, 0.22);
                        box-shadow: 0 14px 28px rgba(37, 99, 235, 0.10);
                        text-decoration: none;
                    }

                    .client-dashboard-page .quick-main {
                        display: flex;
                        align-items: center;
                        gap: 12px;
                    }

                    .client-dashboard-page .quick-icon {
                        width: 42px;
                        height: 42px;
                        border-radius: 12px;
                        display: inline-flex;
                        align-items: center;
                        justify-content: center;
                        background: var(--primary-soft);
                        color: var(--primary-2);
                        font-size: 1rem;
                        flex: 0 0 auto;
                    }

                    .client-dashboard-page .quick-text {
                        min-width: 0;
                    }

                    .client-dashboard-page .quick-title {
                        color: var(--text);
                        font-size: 0.92rem;
                        font-weight: 800;
                        margin-bottom: 3px;
                    }

                    .client-dashboard-page .quick-desc {
                        color: var(--text-soft);
                        font-size: 0.82rem;
                        line-height: 1.45;
                    }

                    .client-dashboard-page .stat-strip {
                        display: grid;
                        grid-template-columns: repeat(4, minmax(0, 1fr));
                        gap: 16px;
                        margin-bottom: 22px;
                    }

                    .client-dashboard-page .stat-card {
                        position: relative;
                        overflow: hidden;
                        background: var(--surface);
                        backdrop-filter: blur(12px);
                        border: 1px solid rgba(255, 255, 255, 0.7);
                        border-radius: var(--radius-xl);
                        box-shadow: var(--shadow-soft);
                        padding: 18px 20px 20px;
                        min-height: 118px;
                        cursor: pointer;
                        transition: transform 0.16s ease, box-shadow 0.16s ease;
                    }

                    .client-dashboard-page .stat-card-link {
                        display: block;
                        text-decoration: none;
                        color: inherit;
                        cursor: pointer;
                    }

                    .client-dashboard-page .stat-card:hover {
                        transform: translateY(-2px);
                        box-shadow: var(--shadow);
                    }

                    .client-dashboard-page .stat-card::before {
                        content: '';
                        position: absolute;
                        inset: 0 0 auto 0;
                        height: 4px;
                    }

                    .client-dashboard-page .stat-card.sc-total::before {
                        background: linear-gradient(90deg, #3b82f6, #60a5fa);
                    }

                    .client-dashboard-page .stat-card.sc-open::before {
                        background: linear-gradient(90deg, #f59e0b, #fbbf24);
                    }

                    .client-dashboard-page .stat-card.sc-paid::before {
                        background: linear-gradient(90deg, #10b981, #34d399);
                    }

                    .client-dashboard-page .stat-card.sc-latest::before {
                        background: linear-gradient(90deg, #f43f5e, #fb7185);
                    }

                    .client-dashboard-page .stat-label {
                        color: var(--text-faint);
                        font-size: 0.74rem;
                        font-weight: 700;
                        text-transform: uppercase;
                        letter-spacing: 0.08em;
                        margin-bottom: 12px;
                    }

                    .client-dashboard-page .stat-value {
                        color: var(--text);
                        font-size: 2rem;
                        font-weight: 800;
                        line-height: 1;
                        letter-spacing: -0.04em;
                        margin-bottom: 6px;
                    }

                    .client-dashboard-page .stat-meta {
                        color: var(--text-soft);
                        font-size: 0.82rem;
                        font-weight: 500;
                    }

                    .client-dashboard-page .dashboard-grid {
                        display: grid;
                        grid-template-columns: minmax(0, 1.15fr) minmax(320px, 0.85fr);
                        gap: 20px;
                    }

                    .client-dashboard-page .panel-card {
                        margin-bottom: 20px;
                    }

                    .client-dashboard-page .panel-header {
                        padding: 20px 24px;
                        border-bottom: 1px solid var(--line);
                        display: flex;
                        align-items: center;
                        justify-content: space-between;
                        gap: 14px;
                        flex-wrap: wrap;
                        background: linear-gradient(180deg, rgba(255, 255, 255, 0.94), rgba(249, 251, 255, 0.94));
                    }

                    .client-dashboard-page .panel-title {
                        margin: 0;
                        color: var(--text);
                        font-size: 1.05rem;
                        font-weight: 800;
                        letter-spacing: -0.02em;
                    }

                    .client-dashboard-page .panel-subtitle {
                        margin-top: 5px;
                        color: var(--text-soft);
                        font-size: 0.88rem;
                    }

                    .client-dashboard-page .panel-body {
                        padding: 22px 24px 24px;
                    }

                    .client-dashboard-page .table-responsive {
                        border-radius: 18px;
                        overflow: hidden;
                        border: 1px solid var(--line);
                        background: #fff;
                    }

                    .client-dashboard-page .table {
                        margin-bottom: 0;
                    }

                    .client-dashboard-page .table thead th {
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

                    .client-dashboard-page .table td {
                        vertical-align: middle;
                        border-top: 1px solid #eef3f8;
                        color: var(--text);
                    }

                    .client-dashboard-page .invoice-link,
                    .client-dashboard-page .payment-link,
                    .client-dashboard-page .panel-link {
                        color: var(--primary-2);
                        font-weight: 700;
                        text-decoration: none;
                    }

                    .client-dashboard-page .invoice-link:hover,
                    .client-dashboard-page .payment-link:hover,
                    .client-dashboard-page .panel-link:hover {
                        text-decoration: underline;
                    }

                    .client-dashboard-page .invoice-status {
                        display: inline-flex;
                        align-items: center;
                        justify-content: center;
                        padding: 5px 10px;
                        border-radius: 999px;
                        font-size: 0.74rem;
                        font-weight: 700;
                        letter-spacing: 0.01em;
                    }

                    .client-dashboard-page .invoice-status.status-paid {
                        background: var(--success-soft);
                        color: var(--success);
                    }

                    .client-dashboard-page .invoice-status.status-partial {
                        background: var(--warning-soft);
                        color: var(--warning);
                    }

                    .client-dashboard-page .invoice-status.status-unpaid {
                        background: var(--danger-soft);
                        color: var(--danger);
                    }

                    .client-dashboard-page .source-pill,
                    .client-dashboard-page .reference-pill {
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

                    .client-dashboard-page .num-cell {
                        font-family: var(--font-primary, Montserrat, Segoe UI, Arial, sans-serif);
                        font-variant-numeric: tabular-nums;
                    }

                    .client-dashboard-page .stack-list {
                        display: grid;
                        gap: 14px;
                    }

                    .client-dashboard-page .mini-card {
                        background: #fff;
                        border: 1px solid var(--line);
                        border-radius: 18px;
                        padding: 16px 18px;
                        box-shadow: 0 10px 26px rgba(15, 23, 42, 0.04);
                    }

                    .client-dashboard-page .mini-label {
                        color: var(--text-faint);
                        font-size: 0.72rem;
                        font-weight: 800;
                        letter-spacing: 0.08em;
                        text-transform: uppercase;
                        margin-bottom: 8px;
                    }

                    .client-dashboard-page .mini-value {
                        color: var(--text);
                        font-size: 1rem;
                        font-weight: 700;
                        line-height: 1.5;
                        word-break: break-word;
                    }

                    .client-dashboard-page .empty-state {
                        padding: 24px;
                        text-align: center;
                        color: var(--text-soft);
                        font-size: 0.92rem;
                    }

                    .client-dashboard-page .empty-state strong {
                        display: block;
                        color: var(--text);
                        font-size: 0.98rem;
                        margin-bottom: 6px;
                    }

                    @media (max-width: 1199.98px) {
                        .client-dashboard-page .hero-body,
                        .client-dashboard-page .dashboard-grid {
                            grid-template-columns: 1fr;
                        }
                    }

                    @media (max-width: 767.98px) {
                        .client-dashboard-page .cd-title {
                            font-size: 1.7rem;
                        }

                        .client-dashboard-page .stat-strip {
                            grid-template-columns: 1fr;
                        }

                        .client-dashboard-page .hero-body,
                        .client-dashboard-page .panel-header,
                        .client-dashboard-page .panel-body {
                            padding-left: 18px;
                            padding-right: 18px;
                        }

                        .client-dashboard-page .hero-meta {
                            grid-template-columns: 1fr;
                        }

                        .client-dashboard-page .cd-actions {
                            width: 100%;
                            justify-content: stretch;
                        }

                        .client-dashboard-page .cd-actions a {
                            flex: 1 1 auto;
                            justify-content: center;
                        }
                    }
                </style>

                <style>
                    /* Hero Banner */
                    .client-dashboard-page .cd-hero {
                        display: flex;
                        align-items: center;
                        justify-content: space-between;
                        flex-wrap: wrap;
                        gap: 16px;
                        padding: 28px 24px;
                        margin: 0 0 22px;
                        border-radius: 16px;
                        background: #1e40af;
                        box-shadow: 0 8px 32px rgba(30, 64, 175, 0.25);
                        position: relative;
                        overflow: hidden;
                    }

                    .client-dashboard-page .cd-hero::before {
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

                    .client-dashboard-page .cd-hero::after {
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

                    .client-dashboard-page .cd-hero__content {
                        position: relative;
                        z-index: 1;
                    }

                    .client-dashboard-page .cd-hero__eyebrow {
                        display: inline-flex;
                        align-items: center;
                        gap: 6px;
                        margin-bottom: 8px;
                        color: rgba(255, 255, 255, 0.85);
                        font-size: 0.78rem;
                        font-weight: 600;
                        letter-spacing: 0.04em;
                    }

                    .client-dashboard-page .cd-hero__eyebrow i {
                        font-size: 1rem;
                    }

                    .client-dashboard-page .cd-hero__title {
                        margin: 0 0 4px 0;
                        color: #fff;
                        font-size: clamp(1.6rem, 2.5vw, 2.2rem);
                        font-weight: 800;
                        line-height: 1.15;
                        letter-spacing: -0.02em;
                        font-family: var(--font-primary, Montserrat, Segoe UI, Arial, sans-serif), "Apple Color Emoji", "Segoe UI Emoji", "Noto Color Emoji", sans-serif;
                    }

                    .client-dashboard-page .cd-hero__subtitle {
                        margin: 0;
                        color: rgba(255, 255, 255, 0.8);
                        font-size: 0.88rem;
                        max-width: 520px;
                    }

                    .client-dashboard-page .cd-hero__actions {
                        display: flex;
                        align-items: center;
                        flex-wrap: wrap;
                        gap: 10px;
                        position: relative;
                        z-index: 1;
                    }

                    .client-dashboard-page .cd-hero-btn {
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

                    .client-dashboard-page .cd-hero-btn:hover,
                    .client-dashboard-page .cd-hero-btn:focus {
                        background: rgba(255, 255, 255, 0.25);
                        border-color: rgba(255, 255, 255, 0.5);
                        color: #fff;
                        text-decoration: none;
                        transform: translateY(-1px);
                    }

                    .client-dashboard-page .cd-hero-btn--solid {
                        border-color: rgba(255, 255, 255, 0.6);
                        background: rgba(255, 255, 255, 0.95);
                        color: #1e40af;
                        font-weight: 700;
                    }

                    .client-dashboard-page .cd-hero-btn--solid:hover,
                    .client-dashboard-page .cd-hero-btn--solid:focus {
                        background: #fff;
                        color: #1e3a8a;
                    }

                    .client-dashboard-page .cd-hero-btn[disabled] {
                        opacity: 0.6;
                        cursor: not-allowed;
                        pointer-events: none;
                    }

                    .client-dashboard-page .office-bounce {
                        display: inline-block;
                        animation: office-bounce 2s ease-in-out infinite;
                    }

                    @keyframes office-bounce {
                        0%, 70%, 100% { transform: translateY(0); }
                        15% { transform: translateY(-10px); }
                        30% { transform: translateY(0); }
                        45% { transform: translateY(-5px); }
                        60% { transform: translateY(0); }
                    }

                    .client-dashboard-page .stat-card,
                    .client-dashboard-page .panel-card,
                    .client-dashboard-page .info-card {
                        border-top: 3px solid #1e40af;
                    }

                    @media (max-width: 767px) {
                        .client-dashboard-page .cd-hero,
                        .client-dashboard-page .cd-hero__actions {
                            flex-direction: column;
                            align-items: stretch;
                        }

                        .client-dashboard-page .cd-hero {
                            padding: 20px;
                        }

                        .client-dashboard-page .cd-hero-btn {
                            flex: 1 1 auto;
                            justify-content: center;
                        }
                    }
                </style>

                <div class="cd-hero">
                    <div class="cd-hero__content">
                        <div class="cd-hero__eyebrow">
                            <i class="mdi mdi-briefcase-outline"></i>
                            Client Portal
                        </div>
                        <h1 class="cd-hero__title"><?= htmlspecialchars($clientName !== '' ? $clientName : 'Client Portal', ENT_QUOTES, 'UTF-8'); ?> <span class="office-bounce">🏢</span></h1>
                        <p class="cd-hero__subtitle">Access your invoices, payment history, and company details in one place.</p>
                    </div>
                    <div class="cd-hero__actions">
                        <a class="cd-hero-btn" href="<?= htmlspecialchars($reportIssueUrl, ENT_QUOTES, 'UTF-8'); ?>">
                            <i class="mdi mdi-lifebuoy"></i>
                            <span>Report an Issue</span>
                        </a>
                        <?php if (isset($invoice_access_enabled) && $invoice_access_enabled): ?>
                        <a class="cd-hero-btn cd-hero-btn--solid" href="<?= htmlspecialchars($myInvoicesUrl, ENT_QUOTES, 'UTF-8'); ?>">
                            <i class="mdi mdi-file-document-outline"></i>
                            <span>Open My Invoices</span>
                        </a>
                        <?php else: ?>
                        <a class="cd-hero-btn cd-hero-btn--solid" disabled>
                            <i class="mdi mdi-file-document-outline"></i>
                            <span>Invoice Access Disabled</span>
                        </a>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="stat-strip">
                    <div class="stat-card sc-total" onclick="window.location.href='<?= htmlspecialchars($requestedTodayUrl, ENT_QUOTES, 'UTF-8'); ?>'">
                        <div class="stat-label">Requested Today</div>
                        <div class="stat-value"><?= isset($requestedTodayCount) ? number_format($requestedTodayCount) : 0; ?></div>
                        <div class="stat-meta">Open today&apos;s request details</div>
                    </div>
                    <div class="stat-card sc-paid" onclick="window.location.href='<?= htmlspecialchars($closedTaskReportUrl, ENT_QUOTES, 'UTF-8'); ?>'">
                        <div class="stat-label">Closed Task</div>
                        <div class="stat-value"><?= isset($accomplishedCount) ? number_format($accomplishedCount) : 0; ?></div>
                        <div class="stat-meta">View closed task report</div>
                    </div>
                    <div class="stat-card sc-latest" onclick="window.location.href='<?= htmlspecialchars($myTicketsUrl, ENT_QUOTES, 'UTF-8'); ?>'">
                        <div class="stat-label">My Tickets</div>
                        <div class="stat-value"><?= isset($myTicketsCount) ? number_format($myTicketsCount) : 0; ?></div>
                        <div class="stat-meta">Open your ticket list</div>
                    </div>
                    <?php if (isset($invoice_access_enabled) && $invoice_access_enabled): ?>
                    <div class="stat-card sc-open" onclick="window.location.href='<?= htmlspecialchars($myInvoicesUrl, ENT_QUOTES, 'UTF-8'); ?>'">
                        <div class="stat-label">Outstanding Balance</div>
                        <div class="stat-value"><?= number_format($totalOutstanding, 2); ?></div>
                        <div class="stat-meta"><?= number_format(count($openInvoices)); ?> unpaid or partially paid invoices</div>
                    </div>
                    <?php else: ?>
                    <div class="stat-card sc-open" style="opacity: 0.6; cursor: not-allowed;">
                        <div class="stat-label">Outstanding Balance</div>
                        <div class="stat-value">--</div>
                        <div class="stat-meta">Invoice access disabled</div>
                    </div>
                    <?php endif; ?>
                </div>

            </div>

        </div>
        <?php include('includes/footer.php'); ?>
    </div>

    <!-- Outstanding Balance Modal -->
    <div class="modal fade" id="outstandingBalanceModal" tabindex="-1" role="dialog" aria-labelledby="outstandingBalanceModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="outstandingBalanceModalLabel">Outstanding Balance</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Invoice No.</th>
                                    <th>Date</th>
                                    <th class="text-right">Total Due</th>
                                    <th class="text-right">Balance</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (isset($invoice_access_enabled) && $invoice_access_enabled): ?>
                                    <?php if (!empty($openInvoices)): ?>
                                        <?php foreach ($openInvoices as $invoice): ?>
                                            <?php
                                            $balance = max(0, (float) ($invoice->Balance ?? 0));
                                            $statusLabel = 'Unpaid';
                                            $statusClass = 'status-unpaid';
                                            if ($invoice->AmountPaid > 0) {
                                                $statusLabel = 'Partially Paid';
                                                $statusClass = 'status-partial';
                                            }
                                            ?>
                                            <tr>
                                                <td><?= htmlspecialchars($invoice->InvoiceNo ?? 'N/A', ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td><?= htmlspecialchars($invoice->TransDate ?? 'N/A', ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td class="text-right"><?= number_format((float) ($invoice->TotalDue ?? 0), 2); ?></td>
                                                <td class="text-right"><?= number_format($balance, 2); ?></td>
                                                <td><span class="invoice-status <?= $statusClass; ?>"><?= $statusLabel; ?></span></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="5" class="text-center">No outstanding invoices</td>
                                        </tr>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="text-center text-muted">Invoice access is disabled for your account</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
    function showModal(type) {
        var modalId = '';
        switch(type) {
            case 'outstandingBalance':
                modalId = 'outstandingBalanceModal';
                break;
        }
        if (modalId) {
            $('#' + modalId).modal('show');
        }
    }
    </script>

</div>

<?php include('includes/themecustomizer.php'); ?>

<script src="<?= base_url(); ?>assets/js/vendor.min.js"></script>
<script src="<?= base_url(); ?>assets/js/app.min.js"></script>

</body>

</html>
