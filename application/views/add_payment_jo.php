<?php
$paymentData = isset($data[0]) ? $data[0] : null;
$previousPayments = $paymentData && $paymentData->AmountPaid !== null
    ? (float) $paymentData->AmountPaid
    : ((!empty($data1) && isset($data1[0]->TotalPayments)) ? (float) $data1[0]->TotalPayments : 0);
$custID = trim((string) ($paymentData->CustID ?? ''));
$customer = trim((string) ($paymentData->Customer ?? ''));
$customerAddress = trim((string) ($paymentData->CustAddress ?? ''));
$invoiceNo = trim((string) ($paymentData->InvoiceNo ?? ''));
$orderId = trim((string) ($paymentData->orderID ?? ''));
$jobDescription = trim((string) ($paymentData->JobDescription ?? ''));
$paymentSource = isset($paymentSource) ? trim((string) $paymentSource) : trim((string) ($paymentData->invoiceSource ?? ''));
$paymentSourceLabel = $paymentSource === 'Others' ? 'Invoice' : ($paymentSource !== '' ? $paymentSource : 'Invoice');
$returnTo = isset($returnTo) ? trim((string) $returnTo) : ($paymentSource === 'Job Order' ? 'joList' : 'invList');
$totalDue = $paymentData ? (float) $paymentData->TotalDue : 0;
$currentBalance = max(0, $totalDue - $previousPayments);
$isFullyPaid = $currentBalance <= 0.00001;
$isPartiallyPaid = !$isFullyPaid && $previousPayments > 0;
$statusLabel = $isFullyPaid ? 'Fully Paid' : ($isPartiallyPaid ? 'Partially Paid' : 'Unpaid');
$statusChipClass = $isFullyPaid ? 'chip-success' : ($isPartiallyPaid ? 'chip-warning' : 'chip-primary');
$balanceAccentClass = $isFullyPaid ? 'accent-success' : ($isPartiallyPaid ? 'accent-warning' : 'accent-primary');
$paymentNotice = $this->session->flashdata('payment_notice');
$invoiceDateRaw = trim((string) ($paymentData->TransDate ?? ''));
$invoiceDateLabel = $invoiceDateRaw !== '' && $invoiceDateRaw !== '0000-00-00'
    ? date('F j, Y', strtotime($invoiceDateRaw))
    : 'Not specified';
$receiveDateRaw = trim((string) ($paymentData->ReceiveDate ?? ''));
$receiveDateLabel = $receiveDateRaw !== '' && $receiveDateRaw !== '0000-00-00'
    ? date('F j, Y', strtotime($receiveDateRaw))
    : 'Not specified';
$paymentHistoryUrl = $orderId !== ''
    ? base_url() . 'Page/paymentHistory?id=' . rawurlencode($orderId)
    : ($invoiceNo !== '' ? base_url() . 'Page/paymentHistory?invoice_no=' . rawurlencode($invoiceNo) : '');
$invoiceUrl = $orderId !== ''
    ? base_url() . 'Page/invoice?id=' . rawurlencode($orderId)
    : ($invoiceNo !== '' ? base_url() . 'Page/invoice?invoice_no=' . rawurlencode($invoiceNo) : '');
$backUrl = base_url() . 'Page/' . $returnTo;
$customerHistoryUrl = '';
if ($custID !== '') {
    $customerHistoryUrl = base_url() . 'Page/customerHistory?cust_id=' . rawurlencode($custID);
} elseif ($customer !== '') {
    $customerHistoryUrl = base_url() . 'Page/customerHistory?customer=' . rawurlencode($customer);
}
$progressPercentage = $totalDue > 0 ? max(0, min(100, ($previousPayments / $totalDue) * 100)) : 0;
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
                <div class="container-fluid add-payment-page">

                    <style>
                        .add-payment-page {
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

                        .add-payment-page * {
                            box-sizing: border-box;
                        }

                        .add-payment-page .ap-header {
                            margin: 24px 0 22px;
                            display: flex;
                            justify-content: space-between;
                            align-items: flex-end;
                            gap: 16px;
                            flex-wrap: wrap;
                        }

                        .add-payment-page .ap-eyebrow {
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

                        .add-payment-page .ap-eyebrow::before {
                            content: '';
                            width: 8px;
                            height: 8px;
                            border-radius: 50%;
                            background: linear-gradient(135deg, var(--primary), var(--primary-2));
                            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.10);
                        }

                        .add-payment-page .ap-title {
                            margin: 0;
                            font-family: var(--font-head);
                            font-size: 2rem;
                            font-weight: 800;
                            letter-spacing: -0.04em;
                            color: var(--text);
                            line-height: 1.1;
                        }

                        .add-payment-page .ap-subtitle {
                            margin-top: 8px;
                            color: var(--text-soft);
                            font-size: 0.93rem;
                            font-weight: 500;
                        }

                        .add-payment-page .ap-actions {
                            display: flex;
                            gap: 10px;
                            flex-wrap: wrap;
                            justify-content: flex-end;
                        }

                        .add-payment-page .btn-soft,
                        .add-payment-page .btn-solid {
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

                        .add-payment-page .btn-soft {
                            background: rgba(255, 255, 255, 0.88);
                            color: var(--text);
                            border: 1px solid var(--line-strong);
                            box-shadow: var(--shadow-soft);
                        }

                        .add-payment-page .btn-soft:hover,
                        .add-payment-page .btn-solid:hover {
                            transform: translateY(-1px);
                            filter: brightness(1.02);
                            text-decoration: none;
                        }

                        .add-payment-page .btn-solid {
                            background: linear-gradient(135deg, var(--primary), var(--primary-2));
                            color: #fff;
                            border: none;
                            box-shadow: 0 10px 24px rgba(37, 99, 235, 0.22);
                        }

                        .add-payment-page .btn-solid:hover {
                            color: #fff;
                        }

                        .add-payment-page .stat-strip {
                            display: grid;
                            grid-template-columns: repeat(4, minmax(0, 1fr));
                            gap: 16px;
                            margin-bottom: 22px;
                        }

                        .add-payment-page .stat-card {
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

                        .add-payment-page .stat-card:hover {
                            transform: translateY(-3px);
                            box-shadow: var(--shadow);
                        }

                        .add-payment-page .stat-card::before {
                            content: '';
                            position: absolute;
                            inset: 0 0 auto 0;
                            height: 4px;
                        }

                        .add-payment-page .stat-card.sc-total::before {
                            background: linear-gradient(90deg, #3b82f6, #60a5fa);
                        }

                        .add-payment-page .stat-card.sc-paid::before {
                            background: linear-gradient(90deg, #10b981, #34d399);
                        }

                        .add-payment-page .stat-card.sc-balance::before {
                            background: linear-gradient(90deg, #f59e0b, #fbbf24);
                        }

                        .add-payment-page .stat-card.sc-preview::before {
                            background: linear-gradient(90deg, #f43f5e, #fb7185);
                        }

                        .add-payment-page .stat-label {
                            color: var(--text-faint);
                            font-size: 0.74rem;
                            font-weight: 700;
                            text-transform: uppercase;
                            letter-spacing: 0.08em;
                            margin-bottom: 12px;
                        }

                        .add-payment-page .stat-value {
                            color: var(--text);
                            font-size: 2rem;
                            font-weight: 800;
                            line-height: 1;
                            letter-spacing: -0.04em;
                            margin-bottom: 6px;
                        }

                        .add-payment-page .stat-meta {
                            color: var(--text-soft);
                            font-size: 0.82rem;
                            font-weight: 500;
                        }

                        .add-payment-page .accent-primary {
                            color: var(--primary);
                        }

                        .add-payment-page .accent-warning {
                            color: var(--warning);
                        }

                        .add-payment-page .accent-success {
                            color: var(--success);
                        }

                        .add-payment-page .ap-grid {
                            display: grid;
                            grid-template-columns: minmax(300px, 0.92fr) minmax(420px, 1.08fr);
                            gap: 20px;
                        }

                        .add-payment-page .ap-card {
                            background: var(--surface);
                            backdrop-filter: blur(12px);
                            border: 1px solid rgba(255, 255, 255, 0.72);
                            border-radius: 24px;
                            box-shadow: var(--shadow);
                            overflow: hidden;
                        }

                        .add-payment-page .ap-card-header {
                            padding: 20px 24px;
                            border-bottom: 1px solid var(--line);
                            display: flex;
                            align-items: center;
                            justify-content: space-between;
                            gap: 14px;
                            flex-wrap: wrap;
                            background: linear-gradient(180deg, rgba(255, 255, 255, 0.94), rgba(249, 251, 255, 0.94));
                        }

                        .add-payment-page .ap-card-title {
                            margin: 0;
                            color: var(--text);
                            font-size: 1.05rem;
                            font-weight: 800;
                            letter-spacing: -0.02em;
                        }

                        .add-payment-page .ap-card-subtitle {
                            margin-top: 5px;
                            color: var(--text-soft);
                            font-size: 0.88rem;
                        }

                        .add-payment-page .ap-card-body {
                            padding: 22px 24px 24px;
                        }

                        .add-payment-page .chip-row {
                            display: flex;
                            flex-wrap: wrap;
                            gap: 8px;
                        }

                        .add-payment-page .chip {
                            display: inline-flex;
                            align-items: center;
                            gap: 6px;
                            padding: 7px 11px;
                            border-radius: 999px;
                            font-size: 0.78rem;
                            font-weight: 700;
                            letter-spacing: 0.01em;
                        }

                        .add-payment-page .chip-primary {
                            background: var(--primary-soft);
                            color: var(--primary-2);
                        }

                        .add-payment-page .chip-warning {
                            background: var(--warning-soft);
                            color: var(--warning);
                        }

                        .add-payment-page .chip-success {
                            background: var(--success-soft);
                            color: var(--success);
                        }

                        .add-payment-page .chip-neutral {
                            background: #f8fbff;
                            color: var(--text-soft);
                            border: 1px solid var(--line);
                        }

                        .add-payment-page .info-stack {
                            display: grid;
                            gap: 14px;
                        }

                        .add-payment-page .info-block {
                            background: #fff;
                            border: 1px solid var(--line);
                            border-radius: 18px;
                            padding: 16px 17px;
                            box-shadow: 0 10px 26px rgba(15, 23, 42, 0.04);
                        }

                        .add-payment-page .info-label {
                            color: var(--text-faint);
                            font-size: 0.72rem;
                            font-weight: 800;
                            letter-spacing: 0.08em;
                            text-transform: uppercase;
                            margin-bottom: 9px;
                        }

                        .add-payment-page .info-value {
                            color: var(--text);
                            font-size: 1.02rem;
                            font-weight: 700;
                            line-height: 1.45;
                        }

                        .add-payment-page .info-value a {
                            color: var(--primary-2);
                            text-decoration: none;
                        }

                        .add-payment-page .info-value a:hover {
                            text-decoration: underline;
                        }

                        .add-payment-page .info-help {
                            margin-top: 6px;
                            color: var(--text-soft);
                            font-size: 0.84rem;
                            line-height: 1.55;
                        }

                        .add-payment-page .progress-shell {
                            margin-top: 18px;
                            padding: 16px 17px 17px;
                            border-radius: 18px;
                            background: linear-gradient(180deg, #fbfdff 0%, #f7faff 100%);
                            border: 1px solid #e8eef7;
                        }

                        .add-payment-page .progress-meta {
                            display: flex;
                            justify-content: space-between;
                            gap: 10px;
                            flex-wrap: wrap;
                            margin-bottom: 10px;
                            color: var(--text-soft);
                            font-size: 0.84rem;
                            font-weight: 600;
                        }

                        .add-payment-page .progress {
                            height: 10px;
                            border-radius: 999px;
                            background: #eaf0f8;
                            overflow: hidden;
                        }

                        .add-payment-page .progress-bar {
                            background: linear-gradient(90deg, var(--primary), var(--primary-2));
                            border-radius: 999px;
                        }

                        .add-payment-page .alert-card {
                            border-radius: 16px;
                            border: 1px solid transparent;
                            padding: 14px 16px;
                            font-size: 0.9rem;
                            font-weight: 600;
                            margin-bottom: 16px;
                        }

                        .add-payment-page .alert-warning-card {
                            background: #fff8eb;
                            border-color: #fde6b2;
                            color: #8a5b00;
                        }

                        .add-payment-page .alert-success-card {
                            background: #effbf5;
                            border-color: #c9efd9;
                            color: #0c6b45;
                        }

                        .add-payment-page label {
                            color: var(--text);
                            font-size: 0.8rem;
                            font-weight: 700;
                            margin-bottom: 7px;
                            letter-spacing: 0.01em;
                        }

                        .add-payment-page .form-control {
                            min-height: 46px;
                            border-radius: 12px;
                            border: 1px solid var(--line-strong);
                            box-shadow: none;
                        }

                        .add-payment-page .form-control:focus {
                            border-color: rgba(37, 99, 235, 0.55);
                            box-shadow: 0 0 0 0.18rem rgba(37, 99, 235, 0.12);
                        }

                        .add-payment-page .readonly-box {
                            min-height: 46px;
                            display: flex;
                            align-items: center;
                            border-radius: 12px;
                            border: 1px solid var(--line);
                            background: #f8fbff;
                            padding: 0 14px;
                            color: var(--text);
                            font-size: 0.92rem;
                            font-weight: 600;
                        }

                        .add-payment-page .money-box {
                            font-family: var(--font-mono);
                            font-variant-numeric: tabular-nums;
                        }

                        .add-payment-page .form-note {
                            margin-top: 8px;
                            color: var(--text-soft);
                            font-size: 0.82rem;
                            line-height: 1.55;
                        }

                        .add-payment-page .quick-actions {
                            display: flex;
                            gap: 10px;
                            flex-wrap: wrap;
                            margin-top: 10px;
                        }

                        .add-payment-page .quick-btn {
                            border: 1px solid var(--line-strong);
                            background: #fff;
                            color: var(--text);
                            padding: 8px 12px;
                            border-radius: 10px;
                            font-size: 0.8rem;
                            font-weight: 700;
                            transition: all 0.15s ease;
                        }

                        .add-payment-page .quick-btn:hover {
                            background: #f6f9fd;
                            border-color: var(--text-faint);
                        }

                        .add-payment-page .preview-panel {
                            margin-top: 18px;
                            padding: 16px 17px;
                            border-radius: 18px;
                            background: linear-gradient(180deg, #fbfdff 0%, #f7faff 100%);
                            border: 1px solid #e8eef7;
                        }

                        .add-payment-page .preview-grid {
                            display: grid;
                            grid-template-columns: repeat(4, minmax(0, 1fr));
                            gap: 12px;
                        }

                        .add-payment-page .preview-box {
                            background: #fff;
                            border: 1px solid var(--line);
                            border-radius: 14px;
                            padding: 14px;
                        }

                        .add-payment-page .preview-label {
                            color: var(--text-faint);
                            font-size: 0.72rem;
                            font-weight: 800;
                            letter-spacing: 0.08em;
                            text-transform: uppercase;
                            margin-bottom: 7px;
                        }

                        .add-payment-page .preview-value {
                            color: var(--text);
                            font-size: 1.05rem;
                            font-weight: 800;
                            line-height: 1.3;
                            font-family: var(--font-mono);
                            font-variant-numeric: tabular-nums;
                        }

                        .add-payment-page .ap-form-actions {
                            display: flex;
                            justify-content: flex-end;
                            gap: 10px;
                            flex-wrap: wrap;
                            padding-top: 20px;
                        }

                        .add-payment-page .btn-submit {
                            display: inline-flex;
                            align-items: center;
                            justify-content: center;
                            gap: 8px;
                            min-width: 156px;
                            padding: 12px 18px;
                            border-radius: 12px;
                            border: none;
                            background: linear-gradient(135deg, var(--primary), var(--primary-2));
                            color: #fff;
                            font-size: 0.9rem;
                            font-weight: 800;
                            box-shadow: 0 12px 24px rgba(37, 99, 235, 0.22);
                        }

                        .add-payment-page .btn-submit:disabled {
                            opacity: 0.7;
                            cursor: not-allowed;
                            box-shadow: none;
                        }

                        .add-payment-page .btn-reset {
                            display: inline-flex;
                            align-items: center;
                            justify-content: center;
                            gap: 8px;
                            min-width: 120px;
                            padding: 12px 18px;
                            border-radius: 12px;
                            border: 1px solid var(--line-strong);
                            background: #fff;
                            color: var(--text);
                            font-size: 0.9rem;
                            font-weight: 700;
                        }

                        .add-payment-page .btn-reset:hover {
                            background: #f8fbff;
                        }

                        @media (max-width: 1199.98px) {
                            .add-payment-page .stat-strip {
                                grid-template-columns: repeat(2, minmax(0, 1fr));
                            }

                            .add-payment-page .ap-grid {
                                grid-template-columns: 1fr;
                            }
                        }

                        @media (max-width: 767.98px) {
                            .add-payment-page .ap-title {
                                font-size: 1.7rem;
                            }

                            .add-payment-page .stat-strip {
                                grid-template-columns: 1fr;
                            }

                            .add-payment-page .preview-grid {
                                grid-template-columns: 1fr;
                            }

                            .add-payment-page .ap-card-header,
                            .add-payment-page .ap-card-body {
                                padding-left: 18px;
                                padding-right: 18px;
                            }

                            .add-payment-page .ap-actions {
                                width: 100%;
                                justify-content: stretch;
                            }

                            .add-payment-page .ap-actions a {
                                flex: 1 1 auto;
                                justify-content: center;
                            }

                            .add-payment-page .ap-form-actions {
                                justify-content: stretch;
                            }

                            .add-payment-page .ap-form-actions button {
                                width: 100%;
                            }
                        }
                    </style>

                    <div class="ap-header">
                        <div>
                            <div class="ap-eyebrow">Record Payment</div>
                            <h1 class="ap-title"><?= htmlspecialchars($paymentSourceLabel, ENT_QUOTES, 'UTF-8'); ?> Payment</h1>
                            <p class="ap-subtitle">
                                Add a payment for invoice #<?= htmlspecialchars($invoiceNo !== '' ? $invoiceNo : 'N/A', ENT_QUOTES, 'UTF-8'); ?>
                                <?= $customer !== '' ? ' for ' . htmlspecialchars($customer, ENT_QUOTES, 'UTF-8') : ''; ?>.
                            </p>
                        </div>

                        <div class="ap-actions">
                            <a class="btn-soft" href="<?= htmlspecialchars($backUrl, ENT_QUOTES, 'UTF-8'); ?>">
                                <i class="fas fa-arrow-left"></i>
                                Back
                            </a>
                            <?php if ($invoiceUrl !== ''): ?>
                                <a class="btn-soft" href="<?= htmlspecialchars($invoiceUrl, ENT_QUOTES, 'UTF-8'); ?>">
                                    <i class="fas fa-file-invoice"></i>
                                    View Invoice
                                </a>
                            <?php endif; ?>
                            <?php if ($paymentHistoryUrl !== ''): ?>
                                <a class="btn-solid" href="<?= htmlspecialchars($paymentHistoryUrl, ENT_QUOTES, 'UTF-8'); ?>">
                                    <i class="fas fa-credit-card"></i>
                                    Payment History
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="stat-strip">
                        <div class="stat-card sc-total">
                            <div class="stat-label">Total Due</div>
                            <div class="stat-value"><?= number_format($totalDue, 2); ?></div>
                            <div class="stat-meta">Original billed amount for this <?= htmlspecialchars(strtolower($paymentSourceLabel), ENT_QUOTES, 'UTF-8'); ?></div>
                        </div>
                        <div class="stat-card sc-paid">
                            <div class="stat-label">Paid to Date</div>
                            <div class="stat-value"><?= number_format($previousPayments, 2); ?></div>
                            <div class="stat-meta">Payments already posted before this entry</div>
                        </div>
                        <div class="stat-card sc-balance">
                            <div class="stat-label">Current Balance</div>
                            <div class="stat-value <?= htmlspecialchars($balanceAccentClass, ENT_QUOTES, 'UTF-8'); ?>" id="summary-balance"><?= number_format($currentBalance, 2); ?></div>
                            <div class="stat-meta"><?= htmlspecialchars($statusLabel, ENT_QUOTES, 'UTF-8'); ?> status</div>
                        </div>
                        <div class="stat-card sc-preview">
                            <div class="stat-label">After This Payment</div>
                            <div class="stat-value" id="summary-after-payment"><?= number_format($currentBalance, 2); ?></div>
                            <div class="stat-meta" id="summary-after-note"><?= $isFullyPaid ? 'No additional payment needed' : 'Remaining amount after save'; ?></div>
                        </div>
                    </div>

                    <div class="ap-grid">
                        <div class="ap-card">
                            <div class="ap-card-header">
                                <div>
                                    <h2 class="ap-card-title">Invoice Context</h2>
                                    <p class="ap-card-subtitle">Review the linked job order or invoice details before posting the payment.</p>
                                </div>
                                <div class="chip-row">
                                    <span class="chip chip-primary">
                                        <i class="fas fa-hashtag"></i>
                                        <?= htmlspecialchars($invoiceNo !== '' ? $invoiceNo : 'N/A', ENT_QUOTES, 'UTF-8'); ?>
                                    </span>
                                    <span class="chip <?= htmlspecialchars($statusChipClass, ENT_QUOTES, 'UTF-8'); ?>">
                                        <i class="fas fa-circle"></i>
                                        <?= htmlspecialchars($statusLabel, ENT_QUOTES, 'UTF-8'); ?>
                                    </span>
                                </div>
                            </div>

                            <div class="ap-card-body">
                                <div class="info-stack">
                                    <div class="info-block">
                                        <div class="info-label">Customer</div>
                                        <div class="info-value">
                                            <?php if ($customerHistoryUrl !== ''): ?>
                                                <a href="<?= htmlspecialchars($customerHistoryUrl, ENT_QUOTES, 'UTF-8'); ?>"><?= htmlspecialchars($customer !== '' ? $customer : 'Walk-in Customer', ENT_QUOTES, 'UTF-8'); ?></a>
                                            <?php else: ?>
                                                <?= htmlspecialchars($customer !== '' ? $customer : 'Walk-in Customer', ENT_QUOTES, 'UTF-8'); ?>
                                            <?php endif; ?>
                                        </div>
                                        <div class="info-help">
                                            <?= $customerAddress !== ''
                                                ? nl2br(htmlspecialchars($customerAddress, ENT_QUOTES, 'UTF-8'))
                                                : 'No customer address is linked to this invoice.'; ?>
                                        </div>
                                    </div>

                                    <div class="info-block">
                                        <div class="info-label">Description</div>
                                        <div class="info-value"><?= htmlspecialchars($jobDescription !== '' ? $jobDescription : 'No job description provided', ENT_QUOTES, 'UTF-8'); ?></div>
                                        <div class="info-help">
                                            Source: <?= htmlspecialchars($paymentSourceLabel, ENT_QUOTES, 'UTF-8'); ?>
                                            <?php if ($orderId !== ''): ?>
                                                · Reference ID <?= htmlspecialchars($orderId, ENT_QUOTES, 'UTF-8'); ?>
                                            <?php endif; ?>
                                        </div>
                                    </div>

                                    <div class="info-block">
                                        <div class="info-label">Dates</div>
                                        <div class="info-value">Invoice Date: <?= htmlspecialchars($invoiceDateLabel, ENT_QUOTES, 'UTF-8'); ?></div>
                                        <div class="info-help">Received / Scheduled: <?= htmlspecialchars($receiveDateLabel, ENT_QUOTES, 'UTF-8'); ?></div>
                                    </div>
                                </div>

                                <div class="progress-shell">
                                    <div class="progress-meta">
                                        <span>Collection progress</span>
                                        <span id="progress-label"><?= number_format($progressPercentage, 1); ?>% paid</span>
                                    </div>
                                    <div class="progress">
                                        <div id="payment-progress-bar" class="progress-bar" role="progressbar" style="width: <?= number_format($progressPercentage, 2, '.', ''); ?>%;" aria-valuemin="0" aria-valuemax="100" aria-valuenow="<?= number_format($progressPercentage, 2, '.', ''); ?>"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="ap-card">
                            <div class="ap-card-header">
                                <div>
                                    <h2 class="ap-card-title">Payment Details</h2>
                                    <p class="ap-card-subtitle">Enter the cash payment and, if needed, the BIR Form 2307 tax credit for this invoice.</p>
                                </div>
                                <div class="chip-row">
                                    <span class="chip chip-neutral">
                                        <i class="fas fa-wallet"></i>
                                        Max Credit <?= number_format($currentBalance, 2); ?>
                                    </span>
                                </div>
                            </div>

                            <div class="ap-card-body">
                                <?php if (!empty($paymentNotice)): ?>
                                    <div class="alert-card alert-warning-card">
                                        <?= htmlspecialchars($paymentNotice, ENT_QUOTES, 'UTF-8'); ?>
                                    </div>
                                <?php endif; ?>

                                <?php if ($isFullyPaid): ?>
                                    <div class="alert-card alert-success-card">
                                        Invoice <?= htmlspecialchars($invoiceNo, ENT_QUOTES, 'UTF-8'); ?> is already fully paid. Additional payments are disabled.
                                    </div>
                                <?php endif; ?>

                                <form class="needs-validation" id="payment-form" method="post" enctype="multipart/form-data" novalidate>
                                    <input type="hidden" name="CustID" value="<?= htmlspecialchars($custID, ENT_QUOTES, 'UTF-8'); ?>">
                                    <input type="hidden" name="Customer" value="<?= htmlspecialchars($customer, ENT_QUOTES, 'UTF-8'); ?>">
                                    <input type="hidden" name="orderID" value="<?= htmlspecialchars($orderId, ENT_QUOTES, 'UTF-8'); ?>">
                                    <input type="hidden" name="InvoiceNo" value="<?= htmlspecialchars($invoiceNo, ENT_QUOTES, 'UTF-8'); ?>">
                                    <input type="hidden" name="TransDescription" value="<?= htmlspecialchars($jobDescription, ENT_QUOTES, 'UTF-8'); ?>">
                                    <input type="hidden" name="return_to" value="<?= htmlspecialchars($returnTo, ENT_QUOTES, 'UTF-8'); ?>">

                                    <div class="form-row">
                                        <div class="form-group col-md-6">
                                            <label>Payor</label>
                                            <div class="readonly-box"><?= htmlspecialchars($customer !== '' ? $customer : 'Walk-in Customer', ENT_QUOTES, 'UTF-8'); ?></div>
                                        </div>
                                        <div class="form-group col-md-6">
                                            <label>Linked Invoice</label>
                                            <div class="readonly-box money-box">#<?= htmlspecialchars($invoiceNo !== '' ? $invoiceNo : 'N/A', ENT_QUOTES, 'UTF-8'); ?></div>
                                        </div>
                                    </div>

                                    <div class="form-row">
                                        <div class="form-group col-md-4">
                                            <label for="payment-date">Payment Date</label>
                                            <input type="date" class="form-control" id="payment-date" name="PDate" value="<?= date('Y-m-d'); ?>" <?= $isFullyPaid ? 'disabled readonly' : 'required'; ?>>
                                        </div>
                                        <div class="form-group col-md-4">
                                            <label for="payment-orno">O.R. No.</label>
                                            <input type="text" class="form-control" id="payment-orno" name="ORNo" placeholder="Official receipt number" <?= $isFullyPaid ? 'disabled readonly' : ''; ?>>
                                        </div>
                                        <div class="form-group col-md-4">
                                            <label for="payment-reference">Payment Reference</label>
                                            <input type="text" class="form-control" id="payment-reference" name="PaymentReference" placeholder="Bank, check, transfer, or notes" <?= $isFullyPaid ? 'disabled readonly' : ''; ?>>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label for="payment-amount">Amount Paid</label>
                                        <input type="number"
                                               class="form-control"
                                               id="payment-amount"
                                               name="AmountPaid"
                                               min="0"
                                               max="<?= number_format($currentBalance, 2, '.', ''); ?>"
                                               step="0.01"
                                               placeholder="0.00"
                                               <?= $isFullyPaid ? 'disabled readonly' : 'required'; ?>>
                                        <div class="form-note" id="amount-help">
                                            Enter the actual cash received. For government payments with BIR Form 2307, add the tax below. Total credit = Amount Paid + Tax.
                                        </div>
                                        <?php if (!$isFullyPaid): ?>
                                            <div class="quick-actions">
                                                <button type="button" class="quick-btn" id="fill-full-balance">Use Full Balance</button>
                                                <button type="button" class="quick-btn" id="clear-payment-amount">Clear Amount</button>
                                            </div>
                                        <?php endif; ?>
                                    </div>

                                    <div class="form-group">
                                        <label for="payment-tax">Tax Credit (BIR Form 2307)</label>
                                        <input type="number"
                                               class="form-control"
                                               id="payment-tax"
                                               name="TaxAmount"
                                               min="0"
                                               step="0.01"
                                               placeholder="0.00"
                                               <?= $isFullyPaid ? 'disabled readonly' : ''; ?>>
                                        <div class="form-note" id="tax-help">
                                            Leave this at 0.00 unless the payment is from a government client with BIR Form 2307 withholding tax.
                                        </div>
                                    </div>

                                    <div class="form-group" id="attachment-group">
                                        <label for="bir-attachment">BIR Form 2307 Attachment <small class="text-muted">(optional)</small></label>
                                        <input type="file"
                                               class="form-control"
                                               id="bir-attachment"
                                               name="bir_attachment"
                                               accept=".pdf,.jpg,.jpeg,.png"
                                               <?= $isFullyPaid ? 'disabled' : ''; ?>>
                                        <div class="form-note" id="attachment-help">
                                            Optional: attach the BIR Form 2307 document (PDF, JPG, PNG, max 5MB).
                                        </div>
                                        <div class="invalid-feedback" id="attachment-error"></div>
                                    </div>

                                    <div class="form-group">
                                        <label for="payment-notes">Notes</label>
                                        <textarea class="form-control"
                                                  id="payment-notes"
                                                  name="Notes"
                                                  rows="3"
                                                  <?= $isFullyPaid ? 'disabled readonly' : ''; ?>></textarea>
                                        <div class="form-note">
                                            Internal remarks that will be saved alongside this payment record.
                                        </div>
                                    </div>

                                    <div class="preview-panel">
                                        <div class="preview-grid">
                                            <div class="preview-box">
                                                <div class="preview-label">Previous Payments</div>
                                                <div class="preview-value" id="preview-previous"><?= number_format($previousPayments, 2); ?></div>
                                            </div>
                                            <div class="preview-box">
                                                <div class="preview-label">This Entry Credit</div>
                                                <div class="preview-value" id="preview-credit">0.00</div>
                                            </div>
                                            <div class="preview-box">
                                                <div class="preview-label">Total After Save</div>
                                                <div class="preview-value" id="preview-total"><?= number_format($previousPayments, 2); ?></div>
                                            </div>
                                            <div class="preview-box">
                                                <div class="preview-label">Remaining Balance</div>
                                                <div class="preview-value" id="preview-balance"><?= number_format($currentBalance, 2); ?></div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="ap-form-actions">
                                        <button type="reset" class="btn-reset" <?= $isFullyPaid ? 'disabled' : ''; ?>>
                                            <i class="fas fa-rotate-left"></i>
                                            Reset
                                        </button>
                                        <button type="submit" name="submit" class="btn-submit" <?= $isFullyPaid ? 'disabled' : ''; ?>>
                                            <i class="fas fa-save"></i>
                                            <?= $isFullyPaid ? 'Fully Paid' : 'Save Payment'; ?>
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
    <script src="<?= base_url(); ?>assets/js/app.min.js"></script>

    <script>
        (function($) {
            'use strict';

            function formatMoney(value) {
                return Number(value).toLocaleString('en-US', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });
            }

            function toNumber(value) {
                var parsed = parseFloat(value);
                return isNaN(parsed) ? 0 : parsed;
            }

            $(function() {
                var previousPayments = <?= json_encode((float) $previousPayments); ?>;
                var totalDue = <?= json_encode((float) $totalDue); ?>;
                var maxBalance = <?= json_encode((float) $currentBalance); ?>;
                var $form = $('#payment-form');
                var $amount = $('#payment-amount');
                var $tax = $('#payment-tax');
                var $fullBalanceButton = $('#fill-full-balance');
                var $clearAmountButton = $('#clear-payment-amount');
                var $progressBar = $('#payment-progress-bar');
                var $progressLabel = $('#progress-label');
                var $summaryAfterPayment = $('#summary-after-payment');
                var $summaryAfterNote = $('#summary-after-note');
                var $summaryBalance = $('#summary-balance');
                var $previewPrevious = $('#preview-previous');
                var $previewCredit = $('#preview-credit');
                var $previewTotal = $('#preview-total');
                var $previewBalance = $('#preview-balance');
                var $amountHelp = $('#amount-help');
                var $taxHelp = $('#tax-help');
                var $attachmentGroup = $('#attachment-group');
                var $attachment = $('#bir-attachment');
                var $attachmentError = $('#attachment-error');

                function validateAttachment() {
                    // Upload is optional; only validate type/size when a file is chosen.
                    if (!$attachment.length || !$attachment[0].files || !$attachment[0].files[0]) {
                        $attachment.removeClass('is-invalid');
                        $attachmentError.hide();
                        return true;
                    }
                    var file = $attachment[0].files[0];
                    var allowedTypes = ['application/pdf', 'image/jpeg', 'image/jpg', 'image/png'];
                    if (!allowedTypes.includes(file.type)) {
                        $attachment.addClass('is-invalid');
                        $attachmentError.text('Please upload a valid file (PDF, JPG, or PNG).').show();
                        return false;
                    }
                    if (file.size > 5 * 1024 * 1024) {
                        $attachment.addClass('is-invalid');
                        $attachmentError.text('File size must be less than 5MB.').show();
                        return false;
                    }
                    $attachment.removeClass('is-invalid');
                    $attachmentError.hide();
                    return true;
                }

                $tax.on('input change', function() {
                    updatePaymentPreview();
                });

                $attachment.on('change', function() {
                    validateAttachment();
                });

                $form.on('submit', function(e) {
                    if (!validateAttachment()) {
                        e.preventDefault();
                        e.stopPropagation();
                        return false;
                    }
                });

                function updatePaymentPreview() {
                    if (!$amount.length) {
                        return;
                    }

                    var amount = toNumber($amount.val());
                    var tax = toNumber($tax.val());
                    if (amount < 0) {
                        amount = 0;
                    }
                    if (tax < 0) {
                        tax = 0;
                    }

                    var totalCredit = amount + tax;

                    if (totalCredit > maxBalance) {
                        $amount[0].setCustomValidity('Amount paid plus tax exceeds the remaining balance.');
                        if ($tax.length) {
                            $tax[0].setCustomValidity('Amount paid plus tax exceeds the remaining balance.');
                        }
                    } else {
                        $amount[0].setCustomValidity('');
                        if ($tax.length) {
                            $tax[0].setCustomValidity('');
                        }
                    }

                    var totalAfter = previousPayments + totalCredit;
                    var remainingAfter = Math.max(0, totalDue - totalAfter);
                    var percentPaid = totalDue > 0 ? Math.min(100, (totalAfter / totalDue) * 100) : 0;

                    $previewPrevious.text(formatMoney(previousPayments));
                    $previewCredit.text(formatMoney(totalCredit));
                    $previewTotal.text(formatMoney(totalAfter));
                    $previewBalance.text(formatMoney(remainingAfter));
                    $summaryAfterPayment.text(formatMoney(remainingAfter));
                    $summaryBalance.text(formatMoney(maxBalance));
                    $progressBar.css('width', percentPaid + '%').attr('aria-valuenow', percentPaid.toFixed(2));
                    $progressLabel.text(percentPaid.toFixed(1) + '% paid');

                    if (totalCredit <= 0) {
                        $summaryAfterNote.text('Remaining amount after save');
                        $amountHelp.text('Enter the cash payment and optional BIR Form 2307 tax. Total credit can be up to ' + formatMoney(maxBalance) + '.');
                        $taxHelp.text('Leave this at 0.00 unless the payment is from a government client with BIR Form 2307 withholding tax.');
                    } else if (remainingAfter <= 0.00001) {
                        $summaryAfterNote.text('This payment clears the invoice');
                        $amountHelp.text('This entry will fully settle invoice #' + <?= json_encode($invoiceNo); ?> + '.');
                        $taxHelp.text('Total credited to the invoice: ' + formatMoney(totalCredit) + ' = cash payment + tax.');
                    } else {
                        $summaryAfterNote.text('Balance left after this payment');
                        $amountHelp.text('After saving, ' + formatMoney(remainingAfter) + ' will remain unpaid.');
                        $taxHelp.text('Total credited to the invoice: ' + formatMoney(totalCredit) + ' = cash payment + tax.');
                    }
                }

                updatePaymentPreview();

                $amount.on('input', updatePaymentPreview);
                $tax.on('input', updatePaymentPreview);

                $form.on('reset', function() {
                    window.setTimeout(function() {
                        if ($amount.length) {
                            $amount[0].setCustomValidity('');
                        }
                        if ($tax.length) {
                            $tax[0].setCustomValidity('');
                        }
                        updatePaymentPreview();
                    }, 0);
                });

                $fullBalanceButton.on('click', function() {
                    if ($amount.length) {
                        var tax = toNumber($tax.val());
                        var remainingCash = Math.max(0, maxBalance - tax);
                        $amount.val(remainingCash.toFixed(2)).trigger('input').trigger('focus');
                    }
                });

                $clearAmountButton.on('click', function() {
                    if ($amount.length) {
                        $amount.val('').trigger('input').trigger('focus');
                    }
                });
            });
        })(jQuery);
    </script>

</body>

</html>
