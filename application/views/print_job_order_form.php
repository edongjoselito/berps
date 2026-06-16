<?php
$jobOrderData = isset($jobOrder) ? $jobOrder : null;
$businessData = isset($business) ? $business : null;

$companyName = trim((string) ($businessData->CompName ?? 'BERPS'));
$companyAddress = trim((string) ($businessData->CompAddress ?? ''));
$companyTin = trim((string) ($businessData->CompTin ?? ''));
$proprietor = trim((string) ($businessData->Proprietor ?? ''));
$companyType = trim((string) ($businessData->CompType ?? ''));

$invoiceNo = trim((string) ($jobOrderData->InvoiceNo ?? ''));
$customer = trim((string) ($jobOrderData->Customer ?? ''));
$customerAddress = trim((string) ($jobOrderData->CustAddress ?? ''));
$transactionDateRaw = trim((string) ($jobOrderData->TransDate ?? ''));
$transactionDate = $transactionDateRaw !== '' && $transactionDateRaw !== '0000-00-00'
    ? date('F j, Y', strtotime($transactionDateRaw))
    : 'Not specified';
$description = trim((string) ($jobOrderData->JobDescription ?? ''));
$notes = trim((string) ($jobOrderData->Notes ?? ''));
$preparedBy = trim((string) ($jobOrderData->invoiceBy ?? ''));
$orderID = trim((string) ($jobOrderData->orderID ?? ''));

$totalDue = (float) ($jobOrderData->TotalDue ?? 0);
$amountPaid = (float) ($jobOrderData->AmountPaid ?? 0);
$balance = (float) ($jobOrderData->Balance ?? 0);

$statusLabel = 'Unpaid';
$statusClass = 'status-unpaid';
if ($balance <= 0) {
    $statusLabel = 'Paid';
    $statusClass = 'status-paid';
} elseif ($amountPaid > 0) {
    $statusLabel = 'Partially Paid';
    $statusClass = 'status-partial';
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Job Order Form <?= htmlspecialchars($invoiceNo, ENT_QUOTES, 'UTF-8'); ?> | BERPS</title>
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

        .btn-secondary {
            background: #fff;
            color: var(--ink-2);
            border-color: var(--line-strong);
        }

        .btn-secondary:hover {
            background: var(--surface-soft);
            border-color: var(--muted-light);
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
                font-weight: 600 !important;
                color: #0f172a !important;
                margin-bottom: 2pt !important;
            }

            .description-cell .desc-sub {
                font-size: 8pt !important;
                color: #64748b !important;
            }

            .line-price {
                font-size: 9pt !important;
                font-weight: 600 !important;
                color: #1e293b !important;
            }

            .summary-grid {
                display: grid !important;
                grid-template-columns: 1fr 280px !important;
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
                page-break-inside: avoid !important;
                break-inside: avoid !important;
            }

            .section-title {
                font-size: 7pt !important;
                font-weight: 700 !important;
                letter-spacing: 0.1em !important;
                text-transform: uppercase !important;
                color: #94a3b8 !important;
                margin-bottom: 8pt !important;
            }

            .notes-block p {
                font-size: 8.5pt !important;
                color: #64748b !important;
                line-height: 1.5 !important;
            }

            .totals-table td {
                padding: 6pt 0 !important;
                font-size: 9pt !important;
            }

            .totals-table .balance-row td {
                font-size: 10.5pt !important;
                font-weight: 700 !important;
                padding-top: 10pt !important;
                border-top: 1.5px solid #cbd5e1 !important;
            }

            .totals-table .balance-row .t-val {
                color: #1e40af !important;
                font-size: 11.5pt !important;
            }

            .invoice-footer {
                margin-top: 18pt !important;
                padding-top: 12pt !important;
                border-top: 1px solid #cbd5e1 !important;
            }

            .footer-note,
            .footer-sig {
                font-size: 7pt !important;
                color: #94a3b8 !important;
            }
        }
    </style>
</head>

<body>
    <div class="page-shell">
        <div class="screen-actions">
            <div class="summary">
                Job Order Form #<?= htmlspecialchars($invoiceNo, ENT_QUOTES, 'UTF-8'); ?>
            </div>
            <div class="btn-row">
                <a href="<?= base_url(); ?>Page/joList" class="btn btn-secondary">
                    ← Back to List
                </a>
                <button onclick="window.print()" class="btn btn-primary">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="6 9 6 2 18 2 18 9"></polyline>
                        <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path>
                        <rect x="6" y="14" width="12" height="8"></rect>
                    </svg>
                    Print
                </button>
            </div>
        </div>

        <div class="invoice-card">
            <div class="invoice-stripe"></div>
            <div class="invoice-inner">
                <div class="hero">
                    <div>
                        <div class="brand-name"><?= htmlspecialchars($companyName, ENT_QUOTES, 'UTF-8'); ?></div>
                        <div class="brand-meta">
                            <?php if ($companyAddress): ?><span><?= htmlspecialchars($companyAddress, ENT_QUOTES, 'UTF-8'); ?></span><?php endif; ?>
                            <?php if ($companyTin): ?><span>TIN: <?= htmlspecialchars($companyTin, ENT_QUOTES, 'UTF-8'); ?></span><?php endif; ?>
                        </div>
                    </div>
                    <div class="invoice-mark">
                        <div class="invoice-type-label">Job Order Form</div>
                        <div class="invoice-heading">#<?= htmlspecialchars($invoiceNo, ENT_QUOTES, 'UTF-8'); ?></div>
                        <div class="status-pill <?= $statusClass; ?>"><?= $statusLabel; ?></div>
                    </div>
                </div>

                <div class="meta-grid">
                    <div class="meta-block">
                        <span class="meta-label">Bill To</span>
                        <div class="customer-name"><?= htmlspecialchars($customer, ENT_QUOTES, 'UTF-8'); ?></div>
                        <div class="customer-address"><?= htmlspecialchars($customerAddress, ENT_QUOTES, 'UTF-8'); ?></div>
                    </div>
                    <div class="meta-block">
                        <span class="meta-label">Job Order Details</span>
                        <div class="details-grid">
                            <div class="detail-item">
                                <div class="detail-label">Date</div>
                                <div class="detail-value"><?= htmlspecialchars($transactionDate, ENT_QUOTES, 'UTF-8'); ?></div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">Prepared By</div>
                                <div class="detail-value"><?= htmlspecialchars($preparedBy ?: '—', ENT_QUOTES, 'UTF-8'); ?></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="line-items">
                    <table>
                        <thead>
                            <tr>
                                <th style="width: 60%;">Description</th>
                                <th class="text-right text-mono" style="width: 20%;">Total Due</th>
                                <th class="text-right text-mono" style="width: 20%;">Balance</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="description-cell">
                                    <div class="desc-title"><?= htmlspecialchars($description ?: 'Job Order', ENT_QUOTES, 'UTF-8'); ?></div>
                                    <?php if ($notes): ?>
                                        <div class="desc-sub"><?= htmlspecialchars($notes, ENT_QUOTES, 'UTF-8'); ?></div>
                                    <?php endif; ?>
                                </td>
                                <td class="text-right text-mono line-price">PHP <?= number_format($totalDue, 2); ?></td>
                                <td class="text-right text-mono line-price">PHP <?= number_format($balance, 2); ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="summary-grid">
                    <?php if ($notes): ?>
                        <div class="notes-block">
                            <div class="section-title">Notes</div>
                            <p><?= nl2br(htmlspecialchars($notes, ENT_QUOTES, 'UTF-8')); ?></p>
                        </div>
                    <?php else: ?>
                        <div></div>
                    <?php endif; ?>
                    <div class="totals-block">
                        <table class="totals-table">
                            <tr>
                                <td class="t-label">Total Due</td>
                                <td class="t-val">PHP <?= number_format($totalDue, 2); ?></td>
                            </tr>
                            <tr>
                                <td class="t-label">Amount Paid</td>
                                <td class="t-val">PHP <?= number_format($amountPaid, 2); ?></td>
                            </tr>
                            <tr class="balance-row">
                                <td class="t-label">Balance</td>
                                <td class="t-val">PHP <?= number_format($balance, 2); ?></td>
                            </tr>
                        </table>
                    </div>
                </div>

                <div class="invoice-footer">
                    <div class="footer-note">
                        Printed on <?= htmlspecialchars($printDate ?? '', ENT_QUOTES, 'UTF-8'); ?> · This is a computer-generated document.
                    </div>
                    <div class="footer-sig">
                        Thank you for your business!
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        window.onload = function() {
            if (<?= isset($autoPrint) && $autoPrint ? 'true' : 'false'; ?>) {
                window.print();
            }
        };
    </script>
</body>

</html>
