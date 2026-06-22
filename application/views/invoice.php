<?php
$invoiceData = isset($invoice) ? $invoice : null;
$businessData = isset($business) ? $business : null;
$footerData = isset($invoiceFooter) ? $invoiceFooter : null;
$isPdfRender = !empty($isPdfRender);

$companyName = trim((string) ($businessData->CompName ?? 'BERPS'));
$companyAddress = trim((string) ($businessData->CompAddress ?? ''));
$companyTin = trim((string) ($businessData->CompTin ?? ''));
$proprietor = trim((string) ($businessData->Proprietor ?? ''));
$companyType = trim((string) ($businessData->CompType ?? ''));

$invoiceNo = trim((string) ($invoiceData->InvoiceNo ?? ''));
$customer = trim((string) ($invoiceData->Customer ?? ''));
$customerAddress = trim((string) ($invoiceData->CustAddress ?? ''));
$transactionDateRaw = trim((string) ($invoiceData->TransDate ?? ''));
$transactionDate = $transactionDateRaw !== '' && $transactionDateRaw !== '0000-00-00'
    ? date('F j, Y', strtotime($transactionDateRaw))
    : 'Not specified';
$receiveDateRaw = trim((string) ($invoiceData->ReceiveDate ?? ''));
$description = trim((string) ($invoiceData->JobDescription ?? ''));
$notes = trim((string) ($invoiceData->Notes ?? ''));

// Build default notes with bank details if no notes exist
if ($notes === '' && !empty($footerData)) {
    $defaultNotes = [];

    // Add bank details
    $hasBank1 = !empty($footerData->bank_name_1) && !empty($footerData->bank_account_name_1) && !empty($footerData->bank_account_no_1);
    $hasBank2 = !empty($footerData->bank_name_2) && !empty($footerData->bank_account_name_2) && !empty($footerData->bank_account_no_2);

    if ($hasBank1 || $hasBank2) {
        $defaultNotes[] = 'Payment Information:';
        $defaultNotes[] = 'You may deposit your payment to:';

        if ($hasBank1) {
            $defaultNotes[] = '';
            $defaultNotes[] = 'Bank Name: ' . $footerData->bank_name_1;
            $defaultNotes[] = 'Account Name: ' . $footerData->bank_account_name_1;
            $defaultNotes[] = 'Account No.: ' . $footerData->bank_account_no_1;
        }

        if ($hasBank2) {
            $defaultNotes[] = '';
            $defaultNotes[] = 'Bank Name: ' . $footerData->bank_name_2;
            $defaultNotes[] = 'Account Name: ' . $footerData->bank_account_name_2;
            $defaultNotes[] = 'Account No.: ' . $footerData->bank_account_no_2;
        }
    }

    // Add contact info
    if (!empty($footerData->contact_email) || !empty($footerData->contact_phone)) {
        $defaultNotes[] = '';
        $defaultNotes[] = 'Thank you for doing business with us! If you have any questions, please feel free to contact us at';
        $contactParts = [];
        if (!empty($footerData->contact_email)) {
            $contactParts[] = 'Email: ' . $footerData->contact_email;
        }
        if (!empty($footerData->contact_phone)) {
            $contactParts[] = 'Call us at: ' . $footerData->contact_phone;
        }
        $defaultNotes[] = implode(' | ', $contactParts);
    }

    // Add disclaimer
    if (!empty($footerData->footer_disclaimer)) {
        $defaultNotes[] = '';
        $defaultNotes[] = $footerData->footer_disclaimer;
    }

    if (!empty($defaultNotes)) {
        $notes = implode("\n", $defaultNotes);
    }
}

$invoiceSource = trim((string) ($invoiceData->invoiceSource ?? 'Invoice'));
$preparedBy = trim((string) ($invoiceData->invoiceBy ?? ''));
$orderID = trim((string) ($invoiceData->orderID ?? ''));
$recurringFrequency = trim((string) ($invoiceData->recurringFrequency ?? 'none'));
$recurringScheduleRaw = trim((string) ($invoiceData->recurringScheduleDate ?? ''));
$recurringSchedule = $recurringScheduleRaw !== '' && $recurringScheduleRaw !== '0000-00-00'
    ? date('F j, Y', strtotime($recurringScheduleRaw))
    : '';

// Helper function to calculate covered period for recurring invoices
function getInvoiceCoveredMonths($invoiceData)
{
    $frequency = $invoiceData->recurringFrequency ?? 'none';
    $scheduleDate = $invoiceData->recurringScheduleDate ?? '';
    $coverageOption = $invoiceData->coverageOption ?? 'coming';

    if ($frequency === 'none' || empty($scheduleDate)) {
        return '';
    }

    $startDate = new DateTime($scheduleDate);
    $endDate = clone $startDate;

    // Calculate the covered period based on frequency
    switch ($frequency) {
        case 'daily':
            // Daily: just the single day (schedule date)
            $endDate = $startDate;
            break;

        case 'weekly':
            // Weekly: 7 days from schedule date
            if ($coverageOption === 'previous') {
                $startDate->modify('-6 days');
                $endDate = clone $startDate;
                $endDate->modify('+6 days');
            } else {
                $endDate->modify('+6 days');
            }
            break;

        case 'monthly':
            // Monthly: from schedule date to schedule date + 1 month - 1 day
            if ($coverageOption === 'previous') {
                $startDate->modify('-1 month');
                $endDate = clone $startDate;
                $endDate->modify('+1 month')->modify('-1 day');
            } else {
                $endDate->modify('+1 month')->modify('-1 day');
            }
            break;

        case 'quarterly':
            // Quarterly: use calendar quarters based on coverageOption
            $year = (int)$startDate->format('Y');
            $month = (int)$startDate->format('n');
            $quarter = ceil($month / 3);

            if ($coverageOption === 'previous') {
                // Previous quarter
                $quarter--;
                if ($quarter < 1) {
                    $quarter = 4;
                    $year--;
                }
            }

            // Calculate start and end of the quarter
            $startMonth = ($quarter - 1) * 3 + 1;
            $endMonth = $quarter * 3;

            $startDate = new DateTime("$year-$startMonth-01");
            $endDate = new DateTime("$year-$endMonth-01");
            $endDate->modify('+1 month')->modify('-1 day');
            break;

        case 'yearly':
            // Yearly: from schedule date to schedule date + 1 year - 1 day
            if ($coverageOption === 'previous') {
                $startDate->modify('-1 year');
                $endDate = clone $startDate;
                $endDate->modify('+1 year')->modify('-1 day');
            } else {
                $endDate->modify('+1 year')->modify('-1 day');
            }
            break;

        default:
            return '';
    }

    return 'From ' . date('M d, Y', $startDate->getTimestamp()) . ' To ' . date('M d, Y', $endDate->getTimestamp());
}

$coveredMonths = getInvoiceCoveredMonths($invoiceData);
$dueDateRaw = $recurringScheduleRaw !== '' && $recurringScheduleRaw !== '0000-00-00'
    ? $recurringScheduleRaw
    : ($receiveDateRaw !== '' && $receiveDateRaw !== '0000-00-00' ? $receiveDateRaw : $transactionDateRaw);
$dueDate = $dueDateRaw !== '' && $dueDateRaw !== '0000-00-00'
    ? date('F j, Y', strtotime($dueDateRaw))
    : 'Not specified';

$totalDue = (float) ($invoiceData->TotalDue ?? 0);
$amountPaid = (float) ($invoiceData->AmountPaid ?? 0);
$balance = (float) ($invoiceData->Balance ?? 0);
$lineItems = isset($invoiceItems) && is_array($invoiceItems) ? $invoiceItems : array();
if (empty($lineItems)) {
    $legacyQuantity = (isset($invoiceData->itemQuantity) && is_numeric($invoiceData->itemQuantity) && (float) $invoiceData->itemQuantity > 0)
        ? (float) $invoiceData->itemQuantity
        : 1;
    $legacyUnit = trim((string) ($invoiceData->itemDurationUnit ?? 'each'));
    $legacyUnitPrice = (isset($invoiceData->itemUnitPrice) && is_numeric($invoiceData->itemUnitPrice))
        ? (float) $invoiceData->itemUnitPrice
        : ($legacyQuantity > 0 ? ($totalDue / $legacyQuantity) : $totalDue);

    $lineItems[] = array(
        'itemDescription' => $description !== '' ? $description : 'Invoice item',
        'itemQuantity' => $legacyQuantity,
        'itemDurationUnit' => $legacyUnit !== '' ? $legacyUnit : 'each',
        'itemUnitPrice' => $legacyUnitPrice,
        'lineTotal' => $totalDue,
    );
}

foreach ($lineItems as $index => $lineItem) {
    if (is_object($lineItem)) {
        $lineItem = (array) $lineItem;
    }
    $itemQuantity = (isset($lineItem['itemQuantity']) && is_numeric($lineItem['itemQuantity']) && (float) $lineItem['itemQuantity'] > 0)
        ? (float) $lineItem['itemQuantity']
        : 1;
    $itemDurationUnit = trim((string) ($lineItem['itemDurationUnit'] ?? 'each'));
    $itemUnitPrice = (isset($lineItem['itemUnitPrice']) && is_numeric($lineItem['itemUnitPrice']))
        ? (float) $lineItem['itemUnitPrice']
        : 0;
    $lineTotal = (isset($lineItem['lineTotal']) && is_numeric($lineItem['lineTotal']))
        ? (float) $lineItem['lineTotal']
        : round($itemQuantity * $itemUnitPrice, 2);
    if ($itemUnitPrice <= 0 && $itemQuantity > 0 && $lineTotal > 0) {
        $itemUnitPrice = round($lineTotal / $itemQuantity, 2);
    }

    $itemQuantityDisplay = (abs($itemQuantity - round($itemQuantity)) < 0.00001)
        ? (string) ((int) round($itemQuantity))
        : number_format($itemQuantity, 2);
    $itemDurationLabel = '';
    if ($itemDurationUnit !== '' && $itemDurationUnit !== 'each') {
        $itemDurationLabel = ($itemQuantity == 1 || preg_match('/s$/i', $itemDurationUnit))
            ? $itemDurationUnit
            : $itemDurationUnit . 's';
    }
    $rateUnitLabel = $itemDurationUnit !== '' ? $itemDurationUnit : 'each';

    $lineItems[$index] = array(
        'itemDescription' => trim((string) ($lineItem['itemDescription'] ?? '')) !== '' ? (string) $lineItem['itemDescription'] : 'Invoice item',
        'itemQuantityDisplay' => $itemQuantityDisplay,
        'itemBreakdownText' => $itemDurationLabel !== ''
            ? ($itemQuantityDisplay . ' ' . $itemDurationLabel . ' x PHP ' . number_format($itemUnitPrice, 2) . ' / ' . $rateUnitLabel)
            : ($itemQuantityDisplay . ' x PHP ' . number_format($itemUnitPrice, 2) . ' / ' . $rateUnitLabel),
        'itemUnitPrice' => $itemUnitPrice,
        'lineTotal' => $lineTotal,
    );
}
$paymentHistoryUrl = '';

if ($orderID !== '') {
    $paymentHistoryUrl = base_url() . 'Page/paymentHistory?id=' . rawurlencode($orderID);
} elseif ($invoiceNo !== '') {
    $paymentHistoryUrl = base_url() . 'Page/paymentHistory?invoice_no=' . rawurlencode($invoiceNo);
}

$statusLabel = 'Unpaid';
$statusClass = 'status-unpaid';
if ($balance <= 0) {
    $statusLabel = 'Paid';
    $statusClass = 'status-paid';
} elseif ($amountPaid > 0) {
    $statusLabel = 'Partially Paid';
    $statusClass = 'status-partial';
}

$backUrl = isset($backUrl) && trim((string) $backUrl) !== ''
    ? (string) $backUrl
    : base_url() . 'Page/' . (($invoiceSource === 'Job Order') ? 'joList' : 'invList');
$backLabel = isset($backLabel) && trim((string) $backLabel) !== ''
    ? (string) $backLabel
    : 'Back to List';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Invoice <?= htmlspecialchars($invoiceNo, ENT_QUOTES, 'UTF-8'); ?> | BERPS</title>
    <style>
        :root {
            --ink: #0f172a;
            --ink-2: #334155;
            --muted: #64748b;
            --muted-light: #94a3b8;
            --line: #e2e8f0;
            --line-strong: #cbd5e1;
            --surface: #ffffff;
            --surface-soft: #f8fafc;
            --surface-mid: #f1f5f9;
            --accent: #1e40af;
            --accent-mid: #3b82f6;
            --accent-soft: #eff6ff;
            --accent-text: #1e3a8a;
            --success: #065f46;
            --success-mid: #059669;
            --success-soft: #ecfdf5;
            --warning: #78350f;
            --warning-mid: #d97706;
            --warning-soft: #fffbeb;
            --danger: #7f1d1d;
            --danger-mid: #dc2626;
            --danger-soft: #fef2f2;
            --sidebar-w: 200px;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: "Segoe UI", system-ui, -apple-system, sans-serif;
            font-size: 14px;
            line-height: 1.6;
            color: var(--ink);
            background: #e9eef6;
        }

        .page-shell {
            min-height: 100vh;
            padding: 36px 20px;
        }

        .screen-actions {
            max-width: 900px;
            margin: 0 auto 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
        }

        .screen-actions .summary {
            color: var(--muted);
            font-size: 0.88rem;
            letter-spacing: 0.01em;
        }

        .btn-row {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            border: 1px solid transparent;
            border-radius: 8px;
            padding: 9px 18px;
            font-size: 0.88rem;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            letter-spacing: 0.01em;
            transition: all 0.15s ease;
        }

        .btn:hover {
            transform: translateY(-1px);
        }

        .btn-primary {
            background: var(--accent);
            color: #fff;
            border-color: var(--accent);
            box-shadow: 0 2px 8px rgba(30, 64, 175, 0.25);
        }

        .btn-primary:hover {
            background: #1d3fa5;
            box-shadow: 0 4px 14px rgba(30, 64, 175, 0.35);
        }

        .btn-pay {
            background: #059669;
            color: #fff;
            border-color: #059669;
            box-shadow: 0 2px 8px rgba(5, 150, 105, 0.3);
        }

        .btn-pay:hover {
            background: #047857;
            box-shadow: 0 4px 14px rgba(5, 150, 105, 0.4);
        }

        .pm-modal-backdrop {
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.55);
            display: none;
            z-index: 1000;
            padding: 20px;
        }

        .pm-modal-backdrop.is-open {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .pm-modal {
            background: #fff;
            border-radius: 14px;
            width: 100%;
            max-width: 420px;
            padding: 28px 28px 24px;
            box-shadow: 0 20px 60px rgba(15, 23, 42, 0.25);
            text-align: center;
        }

        .pm-modal h3 {
            margin: 0 0 4px;
            font-size: 1.2rem;
            color: var(--ink);
        }

        .pm-modal .pm-amount {
            font-size: 1.55rem;
            font-weight: 700;
            margin: 10px 0 6px;
            color: var(--ink);
        }

        .pm-modal .pm-sub {
            color: var(--muted);
            font-size: 0.85rem;
            margin-bottom: 18px;
        }

        .pm-modal .pm-qr-frame {
            background: var(--surface-soft);
            border: 1px dashed var(--line-strong);
            border-radius: 12px;
            padding: 16px;
            min-height: 280px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 14px;
        }

        .pm-modal .pm-qr-frame img {
            max-width: 100%;
            display: block;
        }

        .pm-modal .pm-status {
            padding: 10px 14px;
            border-radius: 8px;
            font-size: 0.85rem;
            font-weight: 600;
            margin-bottom: 14px;
        }

        .pm-modal .pm-status.waiting {
            background: #fffbeb;
            color: #78350f;
            border: 1px solid #fde68a;
        }

        .pm-modal .pm-status.paid {
            background: #ecfdf5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }

        .pm-modal .pm-status.error {
            background: #fef2f2;
            color: #7f1d1d;
            border: 1px solid #fecaca;
        }

        .pm-modal .pm-actions {
            display: flex;
            justify-content: space-between;
            gap: 10px;
            margin-top: 4px;
        }

        .pm-modal .pm-actions .btn {
            flex: 1;
            justify-content: center;
        }

        .pm-modal .pm-link {
            display: inline-block;
            margin-top: 6px;
            font-size: 0.82rem;
            color: var(--accent);
            text-decoration: none;
            word-break: break-all;
        }

        .pm-modal .pm-link:hover {
            text-decoration: underline;
        }

        @media print {
            .pm-modal-backdrop {
                display: none !important;
            }
        }

        .btn-secondary {
            background: #fff;
            color: var(--ink-2);
            border-color: var(--line-strong);
        }

        .btn-secondary:hover {
            background: var(--surface-soft);
            border-color: var(--muted-light);
        }

        .payment-link {
            display: inline-flex;
            align-items: center;
            margin-top: 10px;
            font-size: 0.84rem;
            font-weight: 600;
            color: var(--accent);
            text-decoration: none;
        }

        .payment-link:hover {
            text-decoration: underline;
        }

        .invoice-card {
            max-width: 900px;
            margin: 0 auto;
            background: var(--surface);
            border-radius: 16px;
            box-shadow:
                0 1px 3px rgba(15, 23, 42, 0.06),
                0 8px 32px rgba(15, 23, 42, 0.10),
                0 0 0 1px rgba(15, 23, 42, 0.05);
            overflow: hidden;
        }

        .invoice-stripe {
            height: 5px;
            background: linear-gradient(90deg, #1e40af 0%, #3b82f6 50%, #60a5fa 100%);
        }

        .invoice-inner {
            padding: 44px 48px 40px;
        }

        .hero {
            display: grid;
            grid-template-columns: 1fr auto;
            gap: 32px;
            align-items: flex-start;
            padding-bottom: 32px;
            margin-bottom: 0;
            border-bottom: 1.5px solid var(--line);
        }

        .brand-name {
            font-size: 1.85rem;
            font-weight: 700;
            letter-spacing: -0.04em;
            color: var(--ink);
            line-height: 1;
            margin-bottom: 8px;
        }

        .brand-meta {
            font-size: 0.83rem;
            color: var(--muted);
            line-height: 1.7;
        }

        .brand-meta span {
            display: inline-block;
            margin-right: 14px;
        }

        .brand-meta span::before {
            content: '';
            display: inline-block;
            width: 3px;
            height: 3px;
            border-radius: 50%;
            background: var(--muted-light);
            vertical-align: middle;
            margin-right: 6px;
            margin-bottom: 2px;
        }

        .brand-meta span:first-child::before {
            display: none;
        }

        .invoice-mark {
            text-align: right;
        }

        .invoice-type-label {
            display: inline-block;
            font-size: 0.72rem;
            font-weight: 700;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: var(--accent-text);
            background: var(--accent-soft);
            border: 1px solid #bfdbfe;
            border-radius: 6px;
            padding: 4px 10px;
            margin-bottom: 10px;
        }

        .invoice-heading {
            font-size: 2.4rem;
            font-weight: 800;
            letter-spacing: -0.06em;
            color: var(--ink);
            line-height: 1;
            margin-bottom: 12px;
        }

        .status-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.78rem;
            font-weight: 700;
            letter-spacing: 0.04em;
            text-transform: uppercase;
        }

        .status-pill::before {
            content: '';
            width: 6px;
            height: 6px;
            border-radius: 50%;
            flex-shrink: 0;
        }

        .status-paid {
            background: var(--success-soft);
            color: var(--success);
            border: 1px solid #a7f3d0;
        }

        .status-paid::before {
            background: var(--success-mid);
        }

        .status-partial {
            background: var(--warning-soft);
            color: var(--warning);
            border: 1px solid #fde68a;
        }

        .status-partial::before {
            background: var(--warning-mid);
        }

        .status-unpaid {
            background: var(--danger-soft);
            color: var(--danger);
            border: 1px solid #fecaca;
        }

        .status-unpaid::before {
            background: var(--danger-mid);
        }

        .meta-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
            margin: 24px 0;
        }

        .meta-block {
            background: var(--surface-soft);
            border: 1px solid var(--line);
            border-radius: 12px;
            padding: 18px 20px;
        }

        .meta-label {
            display: block;
            font-size: 0.7rem;
            font-weight: 700;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            color: var(--muted-light);
            margin-bottom: 10px;
        }

        .meta-block .customer-name {
            font-size: 1.05rem;
            font-weight: 600;
            color: var(--ink);
            margin-bottom: 4px;
        }

        .meta-block .customer-address {
            font-size: 0.85rem;
            color: var(--muted);
            line-height: 1.55;
        }

        .details-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px 20px;
            align-items: start;
        }

        .detail-item {
            min-width: 0;
        }

        .detail-item .detail-label {
            font-size: 0.72rem;
            font-weight: 600;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            color: var(--muted-light);
            margin-bottom: 3px;
        }

        .detail-item .detail-value {
            font-size: 0.88rem;
            font-weight: 600;
            color: var(--ink-2);
            word-break: break-word;
        }

        .line-items {
            border: 1px solid var(--line);
            border-radius: 12px;
            overflow: hidden;
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        .line-items thead {
            background: var(--surface-mid);
        }

        .line-items th {
            padding: 12px 18px;
            font-size: 0.7rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: var(--muted);
            border-bottom: 1px solid var(--line);
            text-align: left;
        }

        .line-items td {
            padding: 16px 18px;
            border-bottom: 1px solid var(--line);
            vertical-align: top;
        }

        .line-items tbody tr:last-child td {
            border-bottom: none;
        }

        .text-right {
            text-align: right;
        }

        .text-mono {
            font-variant-numeric: tabular-nums;
        }

        .description-cell .desc-title {
            font-size: 0.95rem;
            font-weight: 600;
            color: var(--ink);
            margin-bottom: 3px;
        }

        .description-cell .desc-sub {
            font-size: 0.82rem;
            color: var(--muted);
        }

        .description-cell .desc-coverage {
            font-size: 0.78rem;
            color: var(--accent-text);
            font-weight: 600;
            margin-top: 6px;
            padding: 4px 10px;
            background: var(--accent-soft);
            border-radius: 6px;
            display: inline-block;
        }

        .line-price {
            font-size: 0.95rem;
            font-weight: 600;
            color: var(--ink-2);
        }

        .summary-grid {
            display: grid;
            grid-template-columns: 1fr 300px;
            gap: 16px;
        }

        .notes-block,
        .totals-block {
            background: var(--surface-soft);
            border: 1px solid var(--line);
            border-radius: 12px;
            padding: 20px 22px;
        }

        .section-title {
            font-size: 0.7rem;
            font-weight: 700;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            color: var(--muted-light);
            margin-bottom: 10px;
        }

        .notes-block p {
            font-size: 0.88rem;
            color: var(--muted);
            line-height: 1.65;
        }

        .totals-table {
            width: 100%;
        }

        .totals-table td {
            padding: 8px 0;
            border: none;
            font-size: 0.9rem;
        }

        .totals-table tr:not(:last-child) td {
            color: var(--ink-2);
        }

        .totals-table .t-label {
            color: var(--muted);
        }

        .totals-table .t-val {
            text-align: right;
            font-variant-numeric: tabular-nums;
            font-weight: 600;
        }

        .totals-table .balance-row td {
            font-size: 1.05rem;
            font-weight: 700;
            color: var(--ink);
            padding-top: 14px;
            border-top: 1.5px solid var(--line-strong);
        }

        .totals-table .balance-row .t-val {
            color: var(--accent);
            font-size: 1.15rem;
        }

        .invoice-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 28px;
            padding-top: 20px;
            border-top: 1px solid var(--line);
            gap: 16px;
            flex-wrap: wrap;
        }

        .footer-note {
            font-size: 0.78rem;
            color: var(--muted-light);
        }

        .footer-sig {
            font-size: 0.78rem;
            color: var(--muted-light);
            text-align: right;
        }

        @media (max-width: 767px) {
            .page-shell {
                padding: 16px 12px;
            }

            .invoice-inner {
                padding: 28px 22px;
            }

            .hero,
            .meta-grid,
            .summary-grid,
            .details-grid {
                grid-template-columns: 1fr;
            }

            .invoice-mark {
                text-align: left;
            }

            .invoice-heading {
                font-size: 1.8rem;
            }
        }

        @media print {
            @page {
                size: A4 portrait;
                margin: 10mm 12mm;
            }

            * {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
                box-sizing: border-box !important;
            }

            html,
            body {
                width: 210mm;
                background: #fff !important;
                color: #0f172a !important;
                font-size: 10.5pt !important;
                margin: 0 !important;
                padding: 0 !important;
            }

            body {
                font-family: "Segoe UI", system-ui, -apple-system, sans-serif !important;
                line-height: 1.45 !important;
            }

            .page-shell {
                padding: 0 !important;
                margin: 0 !important;
                background: #fff !important;
            }

            .screen-actions {
                display: none !important;
            }

            .invoice-card {
                width: 100% !important;
                max-width: 100% !important;
                margin: 0 !important;
                border: none !important;
                border-radius: 0 !important;
                box-shadow: none !important;
                background: #fff !important;
                overflow: visible !important;
            }

            .invoice-stripe {
                height: 5px !important;
                background: #1e40af !important;
                margin: 0 0 14pt 0 !important;
                border-radius: 0 !important;
            }

            .invoice-inner {
                padding: 0 !important;
            }

            .hero {
                display: grid !important;
                grid-template-columns: 1fr 220px !important;
                align-items: start !important;
                gap: 18pt !important;
                padding-bottom: 16pt !important;
                margin-bottom: 14pt !important;
                border-bottom: 1px solid #cbd5e1 !important;
                page-break-inside: avoid !important;
                break-inside: avoid !important;
            }

            .brand-name {
                font-size: 18pt !important;
                font-weight: 700 !important;
                line-height: 1.1 !important;
                margin-bottom: 6pt !important;
            }

            .brand-meta {
                font-size: 8.5pt !important;
                line-height: 1.5 !important;
                color: #64748b !important;
            }

            .invoice-mark {
                text-align: right !important;
            }

            .invoice-type-label {
                display: inline-block !important;
                font-size: 7pt !important;
                font-weight: 700 !important;
                letter-spacing: 0.1em !important;
                padding: 4px 8px !important;
                margin-bottom: 8pt !important;
                color: #1e3a8a !important;
                background: #eff6ff !important;
                border: 1px solid #bfdbfe !important;
                border-radius: 6px !important;
            }

            .invoice-heading {
                font-size: 21pt !important;
                font-weight: 800 !important;
                line-height: 1 !important;
                margin-bottom: 8pt !important;
            }

            .status-pill {
                display: inline-flex !important;
                align-items: center !important;
                font-size: 7.5pt !important;
                font-weight: 700 !important;
                padding: 5px 10px !important;
                border-radius: 18px !important;
            }

            .status-pill::before {
                display: none !important;
            }

            .status-paid {
                background: #ecfdf5 !important;
                color: #065f46 !important;
                border: 1px solid #a7f3d0 !important;
            }

            .status-partial {
                background: #fffbeb !important;
                color: #78350f !important;
                border: 1px solid #fde68a !important;
            }

            .status-unpaid {
                background: #fef2f2 !important;
                color: #7f1d1d !important;
                border: 1px solid #fecaca !important;
            }

            .meta-grid {
                display: grid !important;
                grid-template-columns: 1fr 1fr !important;
                gap: 12pt !important;
                margin: 0 0 14pt 0 !important;
                page-break-inside: avoid !important;
                break-inside: avoid !important;
            }

            .meta-block {
                background: #f8fafc !important;
                border: 1px solid #cbd5e1 !important;
                border-radius: 8px !important;
                padding: 14pt !important;
                min-height: 132pt !important;
                overflow: hidden !important;
                page-break-inside: avoid !important;
                break-inside: avoid !important;
            }

            .meta-label {
                display: block !important;
                font-size: 7pt !important;
                font-weight: 700 !important;
                letter-spacing: 0.1em !important;
                text-transform: uppercase !important;
                color: #94a3b8 !important;
                margin-bottom: 8pt !important;
            }

            .customer-name {
                font-size: 10.5pt !important;
                font-weight: 700 !important;
                margin-bottom: 4pt !important;
                color: #0f172a !important;
            }

            .customer-address {
                font-size: 8.5pt !important;
                line-height: 1.45 !important;
                color: #475569 !important;
            }

            .details-grid {
                display: grid !important;
                grid-template-columns: 1fr 1fr !important;
                gap: 10pt 18pt !important;
                align-items: start !important;
                page-break-inside: avoid !important;
                break-inside: avoid !important;
            }

            .detail-item {
                min-width: 0 !important;
                page-break-inside: avoid !important;
                break-inside: avoid !important;
            }

            .detail-item .detail-label {
                font-size: 7pt !important;
                font-weight: 700 !important;
                line-height: 1.2 !important;
                margin-bottom: 2pt !important;
                color: #94a3b8 !important;
            }

            .detail-item .detail-value {
                font-size: 9pt !important;
                font-weight: 600 !important;
                line-height: 1.3 !important;
                color: #1e293b !important;
                word-break: break-word !important;
            }

            .line-items {
                border: 1px solid #cbd5e1 !important;
                border-radius: 8px !important;
                overflow: hidden !important;
                margin-bottom: 14pt !important;
                page-break-inside: avoid !important;
                break-inside: avoid !important;
            }

            table {
                width: 100% !important;
                border-collapse: collapse !important;
            }

            .line-items thead {
                background: #f1f5f9 !important;
            }

            .line-items th {
                font-size: 7pt !important;
                padding: 9pt 12pt !important;
                color: #64748b !important;
                border-bottom: 1px solid #cbd5e1 !important;
                text-align: left !important;
            }

            .line-items td {
                font-size: 9pt !important;
                padding: 11pt 12pt !important;
                border-bottom: 1px solid #e2e8f0 !important;
                vertical-align: top !important;
            }

            .line-items tbody tr:last-child td {
                border-bottom: none !important;
            }

            .description-cell .desc-title {
                font-size: 9.2pt !important;
                font-weight: 700 !important;
                margin-bottom: 2pt !important;
                color: #0f172a !important;
            }

            .description-cell .desc-sub {
                font-size: 8pt !important;
                color: #64748b !important;
            }

            .description-cell .desc-coverage {
                font-size: 7.8pt !important;
                color: #1e3a8a !important;
                background: #eff6ff !important;
                padding: 2pt 6pt !important;
                border-radius: 4pt !important;
                margin-top: 4pt !important;
            }

            .line-price {
                font-size: 9pt !important;
                font-weight: 600 !important;
            }

            .text-right {
                text-align: right !important;
            }

            .summary-grid {
                display: grid !important;
                grid-template-columns: 1fr 250px !important;
                gap: 12pt !important;
                page-break-inside: avoid !important;
                break-inside: avoid !important;
            }

            .notes-block,
            .totals-block {
                background: #f8fafc !important;
                border: 1px solid #cbd5e1 !important;
                border-radius: 8px !important;
                padding: 14pt !important;
            }

            .section-title {
                font-size: 7pt !important;
                font-weight: 700 !important;
                letter-spacing: 0.1em !important;
                text-transform: uppercase !important;
                margin-bottom: 7pt !important;
                color: #94a3b8 !important;
            }

            .notes-block p {
                font-size: 8.5pt !important;
                line-height: 1.5 !important;
                color: #475569 !important;
            }

            .totals-table td {
                font-size: 9pt !important;
                padding: 5pt 0 !important;
                border: none !important;
            }

            .totals-table .t-label {
                color: #64748b !important;
            }

            .totals-table .t-val {
                text-align: right !important;
                font-weight: 600 !important;
            }

            .totals-table .balance-row td {
                font-size: 10pt !important;
                font-weight: 700 !important;
                color: #0f172a !important;
                padding-top: 8pt !important;
                border-top: 1px solid #cbd5e1 !important;
            }

            .totals-table .balance-row .t-val {
                color: #1e40af !important;
                font-size: 11pt !important;
            }

            .invoice-footer {
                display: flex !important;
                justify-content: space-between !important;
                align-items: center !important;
                margin-top: 14pt !important;
                padding-top: 10pt !important;
                border-top: 1px solid #cbd5e1 !important;
                gap: 10pt !important;
                page-break-inside: avoid !important;
                break-inside: avoid !important;
            }

            .footer-note,
            .footer-sig {
                font-size: 7.5pt !important;
                color: #94a3b8 !important;
            }

            a {
                color: inherit !important;
                text-decoration: none !important;
            }

            .invoice-card,
            .invoice-card * {
                color: #000 !important;
                text-shadow: none !important;
            }
        }

        /* ── Enhanced Modal Styles (QRPh Payment Details) ───────────────────────── */
        .pm-modal.pm-modal-enhanced {
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
            font-family: 'Courier New', monospace;
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
</head>

<body>
    <div class="page-shell">
        <?php if (!$isPdfRender): ?>
        <div class="screen-actions">
            <div class="summary">
                Invoice <?= htmlspecialchars($invoiceNo, ENT_QUOTES, 'UTF-8'); ?>
                <?php if ($customer !== ''): ?>
                    &mdash; <?= htmlspecialchars($customer, ENT_QUOTES, 'UTF-8'); ?>
                <?php endif; ?>
            </div>
            <div class="btn-row">
                <a class="btn btn-secondary" href="<?= htmlspecialchars($backUrl, ENT_QUOTES, 'UTF-8'); ?>">
                    &#8592; <?= htmlspecialchars($backLabel, ENT_QUOTES, 'UTF-8'); ?>
                </a>
                <?php if ($amountPaid > 0 && $paymentHistoryUrl !== ''): ?>
                    <a class="btn btn-secondary" href="<?= htmlspecialchars($paymentHistoryUrl, ENT_QUOTES, 'UTF-8'); ?>">
                        View Payment Details
                    </a>
                <?php endif; ?>
                <?php if ($balance > 0): ?>
                    <button type="button" class="btn btn-pay" id="payOnlineBtn"
                        data-invoice-id="<?= htmlspecialchars((string) ($invoiceData->orderID ?? ''), ENT_QUOTES, 'UTF-8'); ?>"
                        data-invoice-no="<?= htmlspecialchars($invoiceNo, ENT_QUOTES, 'UTF-8'); ?>"
                        data-balance="<?= number_format($balance, 2, '.', ''); ?>">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:18px;height:18px;margin-right:6px;vertical-align:middle;">
                            <rect x="3" y="3" width="7" height="7" />
                            <rect x="14" y="3" width="7" height="7" />
                            <rect x="3" y="14" width="7" height="7" />
                            <path d="M14 14h.01M14 17h.01M17 14h.01M17 17h.01M20 14h.01M20 17h.01M20 20h.01M17 20h.01M14 20h.01" />
                        </svg>
                        Scan to Pay
                    </button>
                <?php endif; ?>
                <button type="button" class="btn btn-primary" onclick="window.print();">
                    &#128438; Print Invoice
                </button>
            </div>
        </div>
        <?php endif; ?>

        <div class="invoice-card">
            <div class="invoice-stripe"></div>

            <div class="invoice-inner">
                <div class="hero">
                    <div class="brand">
                        <div class="brand-name"><?= htmlspecialchars($companyName, ENT_QUOTES, 'UTF-8'); ?></div>
                        <div class="brand-meta">
                            <?php if ($companyAddress !== ''): ?>
                                <span><?= nl2br(htmlspecialchars($companyAddress, ENT_QUOTES, 'UTF-8')); ?></span>
                            <?php endif; ?>
                            <?php if ($companyTin !== ''): ?>
                                <span>TIN: <?= htmlspecialchars($companyTin, ENT_QUOTES, 'UTF-8'); ?></span>
                            <?php endif; ?>
                            <?php if ($proprietor !== ''): ?>
                                <span>Proprietor: <?= htmlspecialchars($proprietor, ENT_QUOTES, 'UTF-8'); ?></span>
                            <?php endif; ?>
                            <?php if ($companyType !== ''): ?>
                                <span><?= htmlspecialchars($companyType, ENT_QUOTES, 'UTF-8'); ?></span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="invoice-mark">
                        <div class="invoice-type-label">Official Invoice</div>
                        <div class="invoice-heading">Invoice</div>
                        <div class="status-pill <?= htmlspecialchars($statusClass, ENT_QUOTES, 'UTF-8'); ?>">
                            <?= htmlspecialchars($statusLabel, ENT_QUOTES, 'UTF-8'); ?>
                        </div>
                    </div>
                </div>

                <div class="meta-grid">
                    <div class="meta-block">
                        <span class="meta-label">Bill To</span>
                        <div class="customer-name"><?= htmlspecialchars($customer !== '' ? $customer : 'Walk-in Customer', ENT_QUOTES, 'UTF-8'); ?></div>
                        <div class="customer-address">
                            <?php if ($customerAddress !== ''): ?>
                                <?= nl2br(htmlspecialchars($customerAddress, ENT_QUOTES, 'UTF-8')); ?>
                            <?php else: ?>
                                No customer address on file.
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="meta-block">
                        <span class="meta-label">Invoice Details</span>
                        <div class="details-grid">
                            <div class="detail-item">
                                <div class="detail-label">Invoice No.</div>
                                <div class="detail-value"><?= htmlspecialchars($invoiceNo, ENT_QUOTES, 'UTF-8'); ?></div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">Invoice Date</div>
                                <div class="detail-value"><?= htmlspecialchars($transactionDate, ENT_QUOTES, 'UTF-8'); ?></div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">Due Date</div>
                                <div class="detail-value"><?= htmlspecialchars($dueDate, ENT_QUOTES, 'UTF-8'); ?></div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">Reference ID</div>
                                <div class="detail-value"><?= htmlspecialchars($orderID !== '' ? $orderID : '—', ENT_QUOTES, 'UTF-8'); ?></div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">Generated</div>
                                <div class="detail-value"><?= htmlspecialchars(date('M j, Y g:i A'), ENT_QUOTES, 'UTF-8'); ?></div>
                            </div>
                            <?php if ($recurringFrequency !== '' && $recurringFrequency !== 'none'): ?>
                                <div class="detail-item">
                                    <div class="detail-label">Recurring</div>
                                    <div class="detail-value"><?= htmlspecialchars(ucfirst($recurringFrequency), ENT_QUOTES, 'UTF-8'); ?></div>
                                </div>
                                <div class="detail-item">
                                    <div class="detail-label">Schedule Date</div>
                                    <div class="detail-value"><?= htmlspecialchars($recurringSchedule !== '' ? $recurringSchedule : 'Not specified', ENT_QUOTES, 'UTF-8'); ?></div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="line-items">
                    <table>
                        <thead>
                            <tr>
                                <th style="width: 8%;">Qty</th>
                                <th>Description</th>
                                <th class="text-right" style="width: 20%;">Unit Cost</th>
                                <th class="text-right" style="width: 20%;">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($lineItems as $lineItem): ?>
                                <tr>
                                    <td style="color: var(--muted); font-size: 0.92rem;"><?= htmlspecialchars($lineItem['itemQuantityDisplay'], ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td class="description-cell">
                                        <div class="desc-title"><?= htmlspecialchars($lineItem['itemDescription'], ENT_QUOTES, 'UTF-8'); ?></div>
                                        <div class="desc-sub"><?= htmlspecialchars($lineItem['itemBreakdownText'], ENT_QUOTES, 'UTF-8'); ?></div>
                                        <?php if (!empty($coveredMonths)): ?>
                                            <div class="desc-coverage"><?= htmlspecialchars($coveredMonths, ENT_QUOTES, 'UTF-8'); ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-right text-mono line-price"><?= number_format((float) $lineItem['itemUnitPrice'], 2); ?></td>
                                    <td class="text-right text-mono line-price"><?= number_format((float) $lineItem['lineTotal'], 2); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div class="summary-grid">
                    <div class="notes-block">
                        <div class="section-title">Notes</div>
                        <p>
                            <?= $notes !== ''
                                ? nl2br(htmlspecialchars($notes, ENT_QUOTES, 'UTF-8'))
                                : 'Please contact us for payment instructions.'; ?>
                        </p>
                    </div>

                    <div class="totals-block">
                        <div class="section-title">Summary</div>
                        <table class="totals-table">
                            <tbody>
                                <tr>
                                    <td class="t-label">Total Due</td>
                                    <td class="t-val text-mono"><?= number_format($totalDue, 2); ?></td>
                                </tr>
                                <tr>
                                    <td class="t-label">Amount Paid</td>
                                    <td class="t-val text-mono"><?= number_format($amountPaid, 2); ?></td>
                                </tr>
                                <tr class="balance-row">
                                    <td class="t-label">Balance</td>
                                    <td class="t-val text-mono"><?= number_format($balance, 2); ?></td>
                                </tr>
                            </tbody>
                        </table>
                        <?php if ($amountPaid > 0 && $paymentHistoryUrl !== ''): ?>
                            <a class="payment-link" href="<?= htmlspecialchars($paymentHistoryUrl, ENT_QUOTES, 'UTF-8'); ?>">
                                View payment details
                            </a>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="invoice-footer">
                    <div class="footer-note">
                        GENERATED FROM BERPS.
                    </div>
                    <div class="footer-sig">
                        POWERED BY SOFTTECH SOLUTIONS AND SERVICES CO.
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php if (!$isPdfRender && $balance > 0): ?>
        <?php
        $onlineFeeRate = 0.02;
        $onlineFee = round($balance * $onlineFeeRate, 2);
        $qrTotal = $balance + $onlineFee;
        ?>
        <div class="pm-modal-backdrop" id="payModal" role="dialog" aria-modal="true" aria-labelledby="payModalTitle">
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
                        <h5 class="pm-modal-title" id="payModalTitle">QRPh Payment Details</h5>
                    </div>
                    <button type="button" class="pm-modal-close" id="pmCloseBtnX" aria-label="Close">
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
                            <div class="pm-qr-frame" id="pmQrFrame">
                                <div class="pm-qr-placeholder" id="pmQrPlaceholder">Generating payment QR&hellip;</div>
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
                                    <dd id="pmRef" class="pm-mono">—</dd>
                                </div>
                                <div class="pm-detail-row">
                                    <dt>Customer</dt>
                                    <dd id="pmCustomer"><?= htmlspecialchars($customer, ENT_QUOTES, 'UTF-8'); ?></dd>
                                </div>
                                <div class="pm-detail-row">
                                    <dt>Invoice</dt>
                                    <dd id="pmInvoice">#<?= htmlspecialchars($invoiceNo, ENT_QUOTES, 'UTF-8'); ?></dd>
                                </div>
                                <div class="pm-detail-row">
                                    <dt>Description</dt>
                                    <dd id="pmDescription"><?= htmlspecialchars($description ?: 'Invoice Payment', ENT_QUOTES, 'UTF-8'); ?></dd>
                                </div>
                                <div class="pm-detail-divider"></div>
                                <div class="pm-detail-row">
                                    <dt>Base Amount</dt>
                                    <dd id="pmBaseAmount">PHP <?= number_format($balance, 2); ?></dd>
                                </div>
                                <div class="pm-detail-row">
                                    <dt>2% Online Fee</dt>
                                    <dd id="pmOnlineFee">PHP <?= number_format($onlineFee, 2); ?></dd>
                                </div>
                                <div class="pm-detail-row pm-detail-total">
                                    <dt>QR Total</dt>
                                    <dd id="pmQrTotal">PHP <?= number_format($qrTotal, 2); ?></dd>
                                </div>
                                <div class="pm-detail-divider"></div>
                                <div class="pm-detail-row">
                                    <dt>Status</dt>
                                    <dd><span id="pmStatusBadge" class="pm-badge pm-badge-warning">PENDING</span></dd>
                                </div>
                                <div class="pm-detail-row">
                                    <dt>Provider Status</dt>
                                    <dd id="pmProviderStatus">—</dd>
                                </div>
                                <div class="pm-detail-row">
                                    <dt>Expires At</dt>
                                    <dd id="pmExpires">—</dd>
                                </div>
                                <div class="pm-detail-row">
                                    <dt>Accounting O.R.</dt>
                                    <dd id="pmORNumber">Not posted yet</dd>
                                </div>
                                <div class="pm-detail-row">
                                    <dt>Paid At</dt>
                                    <dd id="pmPaidAt">—</dd>
                                </div>
                            </dl>
                        </div>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="pm-modal-footer">
                    <button type="button" class="pm-btn pm-btn-ghost" id="pmOpenBtn" style="display:none;">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6" />
                            <polyline points="15 3 21 3 21 9" />
                            <line x1="10" y1="14" x2="21" y2="3" />
                        </svg>
                        Open QR
                    </button>
                    <button type="button" class="pm-btn pm-btn-success" id="pmDownloadBtn" style="display:none;">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                            <polyline points="7 10 12 15 17 10" />
                            <line x1="12" y1="15" x2="12" y2="3" />
                        </svg>
                        Download
                    </button>
                    <button type="button" class="pm-btn pm-btn-primary" id="pmRefreshBtn">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="23 4 23 10 17 10" />
                            <path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10" />
                        </svg>
                        Refresh
                    </button>
                    <button type="button" class="pm-btn pm-btn-close" id="pmCloseBtn">Close</button>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <?php if (!$isPdfRender): ?>
    <script>
        window.addEventListener('load', function() {
            var shouldAutoPrint = <?= !empty($autoPrint) ? 'true' : 'false'; ?>;
            if (shouldAutoPrint) {
                window.setTimeout(function() {
                    window.print();
                }, 250);
            }
        });

        <?php if ($balance > 0): ?>
                (function() {
                    var baseUrl = <?= json_encode(base_url()); ?>;
                    var btn = document.getElementById('payOnlineBtn');
                    var modal = document.getElementById('payModal');
                    var closeBtn = document.getElementById('pmCloseBtn');
                    var closeBtnX = document.getElementById('pmCloseBtnX');
                    var refreshBtn = document.getElementById('pmRefreshBtn');
                    var openBtn = document.getElementById('pmOpenBtn');
                    var downloadBtn = document.getElementById('pmDownloadBtn');
                    var qrFrame = document.getElementById('pmQrFrame');
                    var placeholder = document.getElementById('pmQrPlaceholder');

                    // Detail elements
                    var refEl = document.getElementById('pmRef');
                    var providerStatusEl = document.getElementById('pmProviderStatus');
                    var paidAtEl = document.getElementById('pmPaidAt');
                    var statusBadgeEl = document.getElementById('pmStatusBadge');
                    var orNumberEl = document.getElementById('pmORNumber');
                    var expiresEl = document.getElementById('pmExpires');

                    if (!btn || !modal) return;

                    var pollTimer = null;
                    var currentId = null;
                    var currentQrUrl = null;

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
                            if (data.paymongo_payment_id) {
                                orNumberEl.textContent = data.paymongo_payment_id;
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

                    btn.addEventListener('click', function() {
                        openModal();
                        placeholder.textContent = 'Generating payment QR\u2026';
                        qrFrame.innerHTML = '';
                        qrFrame.appendChild(placeholder);
                        setStatusBadge('pending', '—');
                        refEl.textContent = '—';
                        paidAtEl.textContent = '—';
                        orNumberEl.textContent = 'Not posted yet';

                        // Hide action buttons initially
                        openBtn.style.display = 'none';
                        downloadBtn.style.display = 'none';

                        var fd = new FormData();
                        fd.append('invoice_id', btn.getAttribute('data-invoice-id'));
                        fd.append('invoice_no', btn.getAttribute('data-invoice-no'));

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
                                openBtn.style.display = 'inline-flex';
                                downloadBtn.style.display = 'inline-flex';
                                openBtn.setAttribute('data-url', data.qr_code_url);
                                downloadBtn.setAttribute('data-url', data.qr_code_url);

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
        <?php endif; ?>
    </script>
    <?php endif; ?>
</body>

</html>
