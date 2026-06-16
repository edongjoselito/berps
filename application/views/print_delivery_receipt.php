<?php
// Get delivery data
$deliveryData = isset($delivery) ? $delivery : null;
$companyData = isset($company) ? $company : null;

// Company information
$companyName = 'BERPS';
$companyAddress = '';
$companyTin = '';
$proprietor = '';
$companyType = '';

if ($companyData && !empty($companyData)) {
    // Handle pos_settings data structure
    $firstCompany = is_array($companyData) ? $companyData[0] : $companyData;
    
    // Try different field names that might exist in pos_settings
    $companyName = trim((string) (
        $firstCompany->company_name ?? 
        $firstCompany->business_name ?? 
        $firstCompany->store_name ?? 
        $firstCompany->shop_name ?? 
        $firstCompany->CompName ?? 
        $firstCompany->name ?? 
        'BERPS'
    ));
    
    $companyAddress = trim((string) (
        $firstCompany->company_address ?? 
        $firstCompany->business_address ?? 
        $firstCompany->store_address ?? 
        $firstCompany->shop_address ?? 
        $firstCompany->address ?? 
        $firstCompany->CompAddress ?? 
        ''
    ));
    
    $companyTin = trim((string) (
        $firstCompany->tin ?? 
        $firstCompany->tax_id ?? 
        $firstCompany->company_tin ?? 
        $firstCompany->CompTin ?? 
        ''
    ));
    
    $proprietor = trim((string) (
        $firstCompany->owner ?? 
        $firstCompany->proprietor ?? 
        $firstCompany->manager ?? 
        $firstCompany->director ?? 
        $firstCompany->contact_person ?? 
        $firstCompany->Proprietor ?? 
        ''
    ));
    
    $companyType = trim((string) (
        $firstCompany->company_type ?? 
        $firstCompany->business_type ?? 
        $firstCompany->store_type ?? 
        $firstCompany->shop_type ?? 
        $firstCompany->CompType ?? 
        ''
    ));
}

// Delivery information
$deliveryNo = trim((string) ($deliveryData->deliveryNo ?? ''));
$customerName = trim((string) ($deliveryData->customerName ?? ''));
$customerAddress = trim((string) ($deliveryData->customerAddress ?? ''));
$deliveryDate = trim((string) ($deliveryData->first_delivery_date ?? ''));
$deliveryStatus = trim((string) ($deliveryData->deliveryStatus ?? 'pending'));
$paymentStatus = trim((string) ($deliveryData->paymentStatus ?? 'unpaid'));
$totalAmount = (float) ($deliveryData->totalAmount ?? 0);
$amountPaid = (float) ($deliveryData->amountPaid ?? 0);
$balance = (float) ($deliveryData->balance ?? 0);
$notes = trim((string) ($deliveryData->notes ?? ''));

// Format dates
$deliveryDateFormatted = $deliveryDate !== '' && $deliveryDate !== '0000-00-00'
    ? date('F j, Y', strtotime($deliveryDate))
    : 'Not specified';

// Status styling
$statusLabel = 'Pending';
$statusClass = 'status-pending';
if ($deliveryStatus === 'delivered') {
    $statusLabel = 'Delivered';
    $statusClass = 'status-delivered';
} elseif ($deliveryStatus === 'cancelled') {
    $statusLabel = 'Cancelled';
    $statusClass = 'status-cancelled';
}

$paymentStatusLabel = 'Unpaid';
$paymentStatusClass = 'status-unpaid';
if ($balance <= 0) {
    $paymentStatusLabel = 'Paid';
    $paymentStatusClass = 'status-paid';
} elseif ($amountPaid > 0) {
    $paymentStatusLabel = 'Partially Paid';
    $paymentStatusClass = 'status-partial';
}

// Prepare line items from deliveries
$lineItems = [];
if (isset($deliveries) && !empty($deliveries)) {
    foreach ($deliveries as $delivery) {
        if (isset($delivery->items) && !empty($delivery->items)) {
            foreach ($delivery->items as $item) {
                $quantity = (float) ($item->itemQuantity ?? 1);
                $unitPrice = (float) ($item->itemUnitPrice ?? 0);
                $total = $quantity * $unitPrice;
                
                $lineItems[] = [
                    'itemDescription' => trim((string) ($item->itemDescription ?? 'Delivery Item')),
                    'itemQuantityDisplay' => $quantity,
                    'itemUnit' => trim((string) ($item->itemUnit ?? 'each')),
                    'itemUnitPrice' => $unitPrice,
                    'lineTotal' => $total,
                ];
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Delivery Receipt <?= htmlspecialchars($deliveryNo, ENT_QUOTES, 'UTF-8'); ?> | BERPS</title>
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
            --warning: #92400e;
            --warning-mid: #d97706;
            --warning-soft: #fef3c7;
            --danger: #991b1b;
            --danger-mid: #dc2626;
            --danger-soft: #fef2f2;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 0;
            background: var(--surface-soft);
            color: var(--ink);
            font-family: "Segoe UI", system-ui, -apple-system, sans-serif;
            line-height: 1.6;
        }

        .page-shell {
            max-width: 900px;
            margin: 40px auto;
            padding: 0 20px 40px;
        }

        .screen-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 32px;
            padding: 16px 20px;
            background: var(--surface);
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        }

        .summary {
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--ink);
        }

        .btn-row {
            display: flex;
            gap: 12px;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 16px;
            border-radius: 8px;
            font-size: 0.9rem;
            font-weight: 500;
            text-decoration: none;
            border: none;
            cursor: pointer;
            transition: all 0.16s ease;
        }

        .btn-primary {
            background: var(--accent);
            color: white;
        }

        .btn-primary:hover {
            background: var(--accent-mid);
        }

        .btn-secondary {
            background: var(--surface-mid);
            color: var(--ink);
            border: 1px solid var(--line);
        }

        .btn-secondary:hover {
            background: var(--line);
        }

        .invoice-card {
            background: var(--surface);
            border-radius: 16px;
            box-shadow: 0 4px 24px rgba(0, 0, 0, 0.07);
            overflow: hidden;
        }

        .invoice-stripe {
            height: 6px;
            background: var(--accent);
            border-radius: 16px 16px 0 0;
        }

        .invoice-inner {
            padding: 40px;
        }

        .hero {
            display: grid;
            grid-template-columns: 1fr 280px;
            align-items: start;
            gap: 32px;
            padding-bottom: 32px;
            margin-bottom: 32px;
            border-bottom: 1px solid var(--line);
        }

        .brand-name {
            font-size: 2.2rem;
            font-weight: 800;
            letter-spacing: -0.06em;
            color: var(--ink);
            line-height: 1.1;
            margin-bottom: 8px;
        }

        .brand-meta {
            font-size: 0.92rem;
            color: var(--muted);
            line-height: 1.6;
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

        .status-delivered {
            background: var(--success-soft);
            color: var(--success);
            border: 1px solid #a7f3d0;
        }

        .status-delivered::before {
            background: var(--success-mid);
        }

        .status-pending {
            background: var(--warning-soft);
            color: var(--warning);
            border: 1px solid #fde68a;
        }

        .status-pending::before {
            background: var(--warning-mid);
        }

        .status-cancelled {
            background: var(--danger-soft);
            color: var(--danger);
            border: 1px solid #fecaca;
        }

        .status-cancelled::before {
            background: var(--danger-mid);
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
            gap: 32px;
            margin-bottom: 32px;
        }

        .meta-block {
            background: var(--surface-soft);
            border: 1px solid var(--line);
            border-radius: 12px;
            padding: 24px;
        }

        .meta-label {
            display: block;
            font-size: 0.72rem;
            font-weight: 700;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            color: var(--muted-light);
            margin-bottom: 12px;
        }

        .customer-name {
            font-size: 1.1rem;
            font-weight: 700;
            margin-bottom: 6px;
            color: var(--ink);
        }

        .customer-address {
            font-size: 0.92rem;
            line-height: 1.6;
            color: var(--muted);
        }

        .details-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px 20px;
            align-items: start;
        }

        .detail-item {
            min-width: 0;
        }

        .detail-item .detail-label {
            font-size: 0.72rem;
            font-weight: 700;
            line-height: 1.2;
            margin-bottom: 4px;
            color: var(--muted-light);
        }

        .detail-item .detail-value {
            font-size: 0.92rem;
            font-weight: 600;
            line-height: 1.3;
            color: var(--ink-2);
            word-break: break-word;
        }

        .line-items {
            border: 1px solid var(--line);
            border-radius: 12px;
            overflow: hidden;
            margin-bottom: 32px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        .line-items thead {
            background: var(--surface-soft);
        }

        .line-items th {
            font-size: 0.78rem;
            font-weight: 700;
            padding: 16px 20px;
            color: var(--muted);
            border-bottom: 1px solid var(--line);
            text-align: left;
        }

        .line-items td {
            font-size: 0.92rem;
            padding: 16px 20px;
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

        .line-price {
            font-size: 0.95rem;
            font-weight: 600;
            color: var(--ink-2);
        }

        .summary-grid {
            display: grid;
            grid-template-columns: 1fr 300px;
            gap: 24px;
        }

        .notes-block,
        .totals-block {
            background: var(--surface-soft);
            border: 1px solid var(--line);
            border-radius: 12px;
            padding: 24px;
        }

        .section-title {
            font-size: 0.72rem;
            font-weight: 700;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            color: var(--muted-light);
            margin-bottom: 12px;
        }

        .notes-block p {
            font-size: 0.88rem;
            color: var(--muted);
            line-height: 1.65;
            margin: 0;
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
        }

        .signature-section {
            margin-top: 48px;
            padding-top: 24px;
            border-top: 1px solid var(--line);
        }

        .signature-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 48px;
            margin-bottom: 24px;
        }

        .signature-block {
            text-align: center;
        }

        .signature-label {
            font-size: 0.9rem;
            font-weight: 600;
            color: var(--ink);
            margin-bottom: 8px;
        }

        .signature-line {
            border-bottom: 1px solid var(--line-strong);
            padding-bottom: 4px;
            margin-bottom: 8px;
            min-height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .signature-name {
            font-size: 0.92rem;
            color: var(--ink-2);
            font-style: italic;
        }

        .signature-date {
            font-size: 0.78rem;
            color: var(--muted);
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .signature-note {
            text-align: center;
            margin-top: 16px;
        }

        .signature-note small {
            font-size: 0.75rem;
            color: var(--muted-light);
            font-style: italic;
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
                font-size: 20pt !important;
                font-weight: 800 !important;
                line-height: 1 !important;
                margin-bottom: 10pt !important;
            }

            .status-pill {
                font-size: 7pt !important;
                padding: 4px 8px !important;
            }

            .meta-grid {
                display: grid !important;
                grid-template-columns: 1fr 1fr !important;
                gap: 14pt !important;
                margin-bottom: 14pt !important;
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

            .summary-grid {
                display: grid !important;
                grid-template-columns: 1fr 300px !important;
                gap: 14pt !important;
                page-break-inside: avoid !important;
                break-inside: avoid !important;
            }

            .notes-block,
            .totals-block {
                background: #f8fafc !important;
                border: 1px solid #cbd5e1 !important;
                border-radius: 8px !important;
                padding: 14pt !important;
                page-break-inside: avoid !important;
                break-inside: avoid !important;
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

            .signature-section {
                margin-top: 36pt !important;
                padding-top: 18pt !important;
                border-top: 1px solid #cbd5e1 !important;
                page-break-inside: avoid !important;
                break-inside: avoid !important;
            }

            .signature-grid {
                display: grid !important;
                grid-template-columns: 1fr 1fr !important;
                gap: 36pt !important;
                margin-bottom: 18pt !important;
            }

            .signature-block {
                text-align: center !important;
            }

            .signature-label {
                font-size: 9pt !important;
                font-weight: 600 !important;
                color: #0f172a !important;
                margin-bottom: 6pt !important;
            }

            .signature-line {
                border-bottom: 1px solid #cbd5e1 !important;
                padding-bottom: 3pt !important;
                margin-bottom: 6pt !important;
                min-height: 24pt !important;
                display: flex !important;
                align-items: center !important;
                justify-content: center !important;
            }

            .signature-name {
                font-size: 8.5pt !important;
                color: #334155 !important;
                font-style: italic !important;
            }

            .signature-date {
                font-size: 7pt !important;
                color: #64748b !important;
                text-transform: uppercase !important;
                letter-spacing: 0.05em !important;
            }

            .signature-note {
                text-align: center !important;
                margin-top: 12pt !important;
            }

            .signature-note small {
                font-size: 6.5pt !important;
                color: #94a3b8 !important;
                font-style: italic !important;
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
    </style>
</head>

<body>
    <div class="page-shell">

        <div class="screen-actions">
            <div class="summary">
                Delivery Receipt <?= htmlspecialchars($deliveryNo, ENT_QUOTES, 'UTF-8'); ?>
                <?php if ($customerName !== ''): ?>
                    &mdash; <?= htmlspecialchars($customerName, ENT_QUOTES, 'UTF-8'); ?>
                <?php endif; ?>
            </div>
            <div class="btn-row">
                <a class="btn btn-secondary" href="<?= base_url(); ?>Page/customerDeliveryList">
                    &#8592; Back to Delivery List
                </a>
                <button type="button" class="btn btn-primary" onclick="window.print();">
                    &#128438; Print Receipt
                </button>
            </div>
        </div>

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
                        <div class="invoice-type-label"><?= htmlspecialchars($deliveryNo, ENT_QUOTES, 'UTF-8'); ?></div>
                        <div class="invoice-heading">Delivery Receipt</div>
                        <div class="status-pill <?= htmlspecialchars($statusClass, ENT_QUOTES, 'UTF-8'); ?>">
                            <?= htmlspecialchars($statusLabel, ENT_QUOTES, 'UTF-8'); ?>
                        </div>
                    </div>
                </div>

                <div class="meta-grid">
                    <div class="meta-block">
                        <span class="meta-label">Deliver To</span>
                        <div class="customer-name"><?= htmlspecialchars($customerName !== '' ? $customerName : 'Customer', ENT_QUOTES, 'UTF-8'); ?></div>
                        <div class="customer-address">
                            <?php if ($customerAddress !== ''): ?>
                                <?= nl2br(htmlspecialchars($customerAddress, ENT_QUOTES, 'UTF-8')); ?>
                            <?php else: ?>
                                No customer address on file.
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="meta-block">
                        <span class="meta-label">Delivery Details</span>
                        <div class="details-grid">
                            <div class="detail-item">
                                <div class="detail-label">Delivery No.</div>
                                <div class="detail-value"><?= htmlspecialchars($deliveryNo, ENT_QUOTES, 'UTF-8'); ?></div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">Delivery Date</div>
                                <div class="detail-value"><?= htmlspecialchars($deliveryDateFormatted, ENT_QUOTES, 'UTF-8'); ?></div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">Delivery Status</div>
                                <div class="detail-value"><?= htmlspecialchars(ucfirst($deliveryStatus), ENT_QUOTES, 'UTF-8'); ?></div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">Payment Status</div>
                                <div class="detail-value"><?= htmlspecialchars(ucfirst($paymentStatus), ENT_QUOTES, 'UTF-8'); ?></div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">Generated</div>
                                <div class="detail-value"><?= htmlspecialchars(date('M j, Y g:i A'), ENT_QUOTES, 'UTF-8'); ?></div>
                            </div>
                        </div>
                    </div>
                </div>

                <?php if (!empty($lineItems)): ?>
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
                                        <div class="desc-sub"><?= htmlspecialchars($lineItem['itemQuantityDisplay'], ENT_QUOTES, 'UTF-8'); ?> x PHP <?= number_format($lineItem['itemUnitPrice'], 2); ?> / <?= htmlspecialchars($lineItem['itemUnit'], ENT_QUOTES, 'UTF-8'); ?></div>
                                    </td>
                                    <td class="text-right line-price text-mono">PHP <?= number_format($lineItem['itemUnitPrice'], 2); ?></td>
                                    <td class="text-right line-price text-mono">PHP <?= number_format($lineItem['lineTotal'], 2); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>

                <div class="summary-grid">
                    <div class="notes-block">
                        <div class="section-title">Notes</div>
                        <p>
                            <?php if ($notes !== ''): ?>
                                <?= nl2br(htmlspecialchars($notes, ENT_QUOTES, 'UTF-8')); ?>
                            <?php else: ?>
                                Thank you for your business! This document confirms the delivery of the items listed above.
                            <?php endif; ?>
                        </p>
                    </div>

                    <div class="totals-block">
                        <div class="section-title">Summary</div>
                        <table class="totals-table">
                            <tr>
                                <td class="t-label">Total Amount</td>
                                <td class="t-val text-mono">PHP <?= number_format($totalAmount, 2); ?></td>
                            </tr>
                            <tr>
                                <td class="t-label">Amount Paid</td>
                                <td class="t-val text-mono">PHP <?= number_format($amountPaid, 2); ?></td>
                            </tr>
                            <tr class="balance-row">
                                <td class="t-label">Balance</td>
                                <td class="t-val text-mono">PHP <?= number_format($balance, 2); ?></td>
                            </tr>
                        </table>
                    </div>
                </div>

                <!-- Signature Sections -->
                <div class="signature-section">
                    <div class="signature-grid">
                        <div class="signature-block">
                            <div class="signature-label">Delivered By:</div>
                            <div class="signature-line">
                                <span class="signature-name"><?= htmlspecialchars($deliveredByName ?? '', ENT_QUOTES, 'UTF-8'); ?></span>
                            </div>
                            <div class="signature-date">Signature & Date</div>
                        </div>
                        <div class="signature-block">
                            <div class="signature-label">Received By:</div>
                            <div class="signature-line">
                                <span class="signature-name"></span>
                            </div>
                            <div class="signature-date">Signature & Date</div>
                        </div>
                    </div>
                    <div class="signature-note">
                        <small>This is a computer-generated delivery receipt. No signature required for electronic records.</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>
