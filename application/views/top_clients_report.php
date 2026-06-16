<?php
$businessData = isset($business) && is_array($business) && !empty($business)
    ? $business[0]
    : (isset($business) ? $business : null);

$companyName    = trim((string) ($businessData->CompName    ?? 'BERPS'));
$companyAddress = trim((string) ($businessData->CompAddress ?? ''));
$companyTin     = trim((string) ($businessData->CompTin     ?? ''));
$proprietor     = trim((string) ($businessData->Proprietor  ?? ''));

$fromDate    = isset($fromDate) ? $fromDate : date('Y-01-01');
$toDate      = isset($toDate)   ? $toDate   : date('Y-m-d');
$displayFrom = date('M d, Y', strtotime($fromDate));
$displayTo   = date('M d, Y', strtotime($toDate));
$limit       = isset($limit)    ? (int) $limit : 20;

$topClients  = isset($topClients) ? $topClients : array();
$totals      = isset($totals)    ? $totals    : array(
    'totalPaid'     => 0,
    'totalTax'      => 0,
    'totalGross'    => 0,
    'totalPayments' => 0,
);

$totalPaid     = (float) ($totals['totalPaid'] ?? 0);
$totalTax      = (float) ($totals['totalTax'] ?? 0);
$totalGross    = (float) ($totals['totalGross'] ?? 0);
$totalPayments = (int) ($totals['totalPayments'] ?? 0);
$clientCount   = count($topClients);

$generatedAt   = isset($generatedAt) ? $generatedAt : date('F j, Y h:i A');
$backUrl       = base_url() . 'Page/reports';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Top Clients Report | <?= htmlspecialchars($companyName, ENT_QUOTES, 'UTF-8'); ?></title>

    <style>
        /* ── Reset ─────────────────────────────────────────────────────── */
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        /* ── Tokens ─────────────────────────────────────────────────────── */
        :root {
            --ink:          #0f172a;
            --ink-2:        #334155;
            --muted:        #64748b;
            --muted-light:  #94a3b8;
            --line:         #e2e8f0;
            --line-mid:     #cbd5e1;
            --surface:      #ffffff;
            --surface-soft: #f8fafc;
            --surface-mid:  #f1f5f9;
            --accent:       #1e40af;
            --accent-hover: #1d3fa5;
            --accent-soft:  #eff6ff;
            --accent-text:  #1e3a8a;
            --success:      #059669;
            --success-soft: #ecfdf5;
            --warning:      #d97706;
            --warning-soft: #fffbeb;
            --radius-sm:    6px;
            --radius-md:    10px;
            --radius-lg:    14px;
        }

        /* ── Base ────────────────────────────────────────────────────────── */
        body {
            font-family: "Segoe UI", system-ui, -apple-system, sans-serif;
            font-size: 13px;
            line-height: 1.6;
            color: var(--ink);
            background: #e9eef6;
        }

        /* ── Page shell ──────────────────────────────────────────────────── */
        .page-shell {
            min-height: 100vh;
            padding: 32px 20px 48px;
        }

        /* ── Screen-only toolbar ─────────────────────────────────────────── */
        .screen-toolbar {
            max-width: 1000px;
            margin: 0 auto 18px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
        }

        .toolbar-label {
            color: var(--muted);
            font-size: 0.85rem;
        }
        .toolbar-label strong {
            display: block;
            font-size: 0.95rem;
            font-weight: 600;
            color: var(--ink-2);
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
        .btn:hover { transform: translateY(-1px); }

        .btn-secondary {
            background: #fff;
            color: var(--ink-2);
            border-color: var(--line-mid);
        }
        .btn-secondary:hover {
            background: var(--surface-soft);
            border-color: var(--muted-light);
        }

        .btn-primary {
            background: var(--accent);
            color: #fff;
            border-color: var(--accent);
            box-shadow: 0 2px 8px rgba(30, 64, 175, 0.25);
        }
        .btn-primary:hover {
            background: var(--accent-hover);
            box-shadow: 0 4px 14px rgba(30, 64, 175, 0.35);
        }

        /* ── Filter bar ──────────────────────────────────────────────────── */
        .filter-bar {
            max-width: 1000px;
            margin: 0 auto 18px;
            background: #fff;
            border: 1px solid var(--line);
            border-radius: var(--radius-md);
            padding: 16px 20px;
        }

        .filter-form {
            display: flex;
            gap: 14px;
            flex-wrap: wrap;
            align-items: flex-end;
        }

        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .filter-label {
            font-size: 0.75rem;
            font-weight: 600;
            color: var(--muted);
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }

        .filter-input {
            padding: 8px 12px;
            border: 1px solid var(--line-mid);
            border-radius: var(--radius-sm);
            font-size: 0.9rem;
            color: var(--ink);
            background: #fff;
            min-width: 140px;
        }
        .filter-input:focus {
            outline: none;
            border-color: var(--accent);
            box-shadow: 0 0 0 3px var(--accent-soft);
        }

        /* ── Report card ─────────────────────────────────────────────────── */
        .report-card {
            max-width: 1000px;
            margin: 0 auto;
            background: var(--surface);
            border-radius: var(--radius-lg);
            box-shadow:
                0 1px 3px rgba(15, 23, 42, 0.06),
                0 8px 32px rgba(15, 23, 42, 0.10),
                0 0 0 1px rgba(15, 23, 42, 0.05);
            overflow: hidden;
        }

        .report-header {
            padding: 28px 32px;
            border-bottom: 1px solid var(--line);
        }

        .company-name {
            font-size: 1.35rem;
            font-weight: 700;
            color: var(--ink);
            margin-bottom: 6px;
        }

        .company-meta {
            font-size: 0.82rem;
            color: var(--muted);
            line-height: 1.5;
        }

        .report-title {
            margin-top: 18px;
            font-size: 1.15rem;
            font-weight: 700;
            color: var(--accent-text);
            letter-spacing: -0.01em;
        }

        .report-period {
            font-size: 0.85rem;
            color: var(--muted);
            margin-top: 4px;
        }

        .report-generated {
            font-size: 0.78rem;
            color: var(--muted-light);
            margin-top: 8px;
        }

        /* ── Summary cards ──────────────────────────────────────────────── */
        .summary-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 16px;
            padding: 24px 32px;
            background: var(--surface-soft);
            border-bottom: 1px solid var(--line);
        }

        .summary-card {
            background: #fff;
            border: 1px solid var(--line);
            border-radius: var(--radius-md);
            padding: 16px;
            text-align: center;
        }

        .summary-label {
            font-size: 0.72rem;
            font-weight: 600;
            color: var(--muted);
            text-transform: uppercase;
            letter-spacing: 0.06em;
            margin-bottom: 6px;
        }

        .summary-value {
            font-size: 1.35rem;
            font-weight: 700;
            color: var(--ink);
            font-variant-numeric: tabular-nums;
        }

        .summary-value.accent {
            color: var(--accent);
        }

        .summary-value.success {
            color: var(--success);
        }

        /* ── Table ───────────────────────────────────────────────────────── */
        .table-wrap {
            padding: 24px 32px 32px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead {
            background: var(--surface-mid);
        }

        th {
            padding: 12px 14px;
            font-size: 0.7rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: var(--muted);
            border-bottom: 1px solid var(--line);
            text-align: left;
        }
        th.text-right { text-align: right; }
        th.text-center { text-align: center; }

        td {
            padding: 14px;
            border-bottom: 1px solid var(--line);
            vertical-align: middle;
        }

        tbody tr:last-child td {
            border-bottom: none;
        }

        tbody tr:hover {
            background: var(--surface-soft);
        }

        .rank-cell {
            font-size: 0.9rem;
            font-weight: 700;
            color: var(--accent);
            text-align: center;
            width: 60px;
        }

        .client-name {
            font-weight: 600;
            color: var(--ink);
        }

        .client-id {
            font-size: 0.75rem;
            color: var(--muted);
            margin-top: 2px;
        }

        .amount {
            font-variant-numeric: tabular-nums;
            font-weight: 600;
            color: var(--ink-2);
        }

        .amount.paid {
            color: var(--success);
        }

        .count {
            font-variant-numeric: tabular-nums;
            color: var(--muted);
        }

        .date-range {
            font-size: 0.78rem;
            color: var(--muted);
        }

        /* ── Empty state ──────────────────────────────────────────────────── */
        .empty-state {
            text-align: center;
            padding: 48px 24px;
            color: var(--muted);
        }

        .empty-state-icon {
            font-size: 2.5rem;
            margin-bottom: 12px;
        }

        /* ── Print ───────────────────────────────────────────────────────── */
        @media print {
            @page {
                size: A4 landscape;
                margin: 12mm 14mm;
            }

            body {
                background: #fff;
                font-size: 10pt;
            }

            .page-shell {
                padding: 0;
            }

            .screen-toolbar,
            .filter-bar,
            .btn-row {
                display: none !important;
            }

            .report-card {
                box-shadow: none;
                border: none;
                max-width: 100%;
            }

            .summary-grid {
                grid-template-columns: repeat(4, 1fr);
                background: #fff;
                padding: 12pt 0;
            }

            .summary-card {
                border: 1px solid #cbd5e1;
            }

            .table-wrap {
                padding: 12pt 0;
            }

            th {
                background: #f1f5f9 !important;
                font-size: 8pt !important;
            }

            td {
                font-size: 9pt !important;
                padding: 8pt !important;
            }
        }

        /* ── Responsive ──────────────────────────────────────────────────── */
        @media (max-width: 768px) {
            .summary-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .filter-form {
                flex-direction: column;
                align-items: stretch;
            }

            .filter-group {
                width: 100%;
            }

            .filter-input {
                width: 100%;
            }

            th, td {
                padding: 10px 8px;
                font-size: 0.85rem;
            }
        }
    </style>
</head>
<body>
    <div class="page-shell">
        <!-- Screen toolbar -->
        <div class="screen-toolbar">
            <div class="toolbar-label">
                <strong>Top Clients Report</strong>
                Generated <?= htmlspecialchars($generatedAt, ENT_QUOTES, 'UTF-8'); ?>
            </div>
            <div class="btn-row">
                <a href="<?= htmlspecialchars($backUrl, ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-secondary">
                    &#8592; Back to Reports
                </a>
                <button type="button" class="btn btn-primary" onclick="window.print();">
                    &#128438; Print Report
                </button>
            </div>
        </div>

        <!-- Filter bar -->
        <div class="filter-bar">
            <form method="get" action="<?= base_url(); ?>Page/topClientsReport" class="filter-form">
                <div class="filter-group">
                    <label class="filter-label">From Date</label>
                    <input type="date" name="from" class="filter-input" value="<?= htmlspecialchars($fromDate, ENT_QUOTES, 'UTF-8'); ?>">
                </div>
                <div class="filter-group">
                    <label class="filter-label">To Date</label>
                    <input type="date" name="to" class="filter-input" value="<?= htmlspecialchars($toDate, ENT_QUOTES, 'UTF-8'); ?>">
                </div>
                <div class="filter-group">
                    <label class="filter-label">Top N Clients</label>
                    <select name="limit" class="filter-input">
                        <option value="10" <?= $limit === 10 ? 'selected' : ''; ?>>Top 10</option>
                        <option value="20" <?= $limit === 20 ? 'selected' : ''; ?>>Top 20</option>
                        <option value="50" <?= $limit === 50 ? 'selected' : ''; ?>>Top 50</option>
                        <option value="100" <?= $limit === 100 ? 'selected' : ''; ?>>Top 100</option>
                    </select>
                </div>
                <div class="filter-group">
                    <button type="submit" class="btn btn-primary">Apply Filters</button>
                </div>
            </form>
        </div>

        <!-- Report card -->
        <div class="report-card">
            <!-- Header -->
            <div class="report-header">
                <div class="company-name"><?= htmlspecialchars($companyName, ENT_QUOTES, 'UTF-8'); ?></div>
                <?php if ($companyAddress !== ''): ?>
                    <div class="company-meta"><?= nl2br(htmlspecialchars($companyAddress, ENT_QUOTES, 'UTF-8')); ?></div>
                <?php endif; ?>
                <?php if ($companyTin !== ''): ?>
                    <div class="company-meta">TIN: <?= htmlspecialchars($companyTin, ENT_QUOTES, 'UTF-8'); ?></div>
                <?php endif; ?>

                <div class="report-title">Top Clients by Payment Amount</div>
                <div class="report-period">Period: <?= htmlspecialchars($displayFrom, ENT_QUOTES, 'UTF-8'); ?> &ndash; <?= htmlspecialchars($displayTo, ENT_QUOTES, 'UTF-8'); ?></div>
                <div class="report-generated">Generated: <?= htmlspecialchars($generatedAt, ENT_QUOTES, 'UTF-8'); ?></div>
            </div>

            <!-- Summary cards -->
            <div class="summary-grid">
                <div class="summary-card">
                    <div class="summary-label">Total Clients</div>
                    <div class="summary-value"><?= number_format($clientCount); ?></div>
                </div>
                <div class="summary-card">
                    <div class="summary-label">Total Payments</div>
                    <div class="summary-value"><?= number_format($totalPayments); ?></div>
                </div>
                <div class="summary-card">
                    <div class="summary-label">Total Amount Paid</div>
                    <div class="summary-value success">PHP <?= number_format($totalPaid, 2); ?></div>
                </div>
                <div class="summary-card">
                    <div class="summary-label">Gross Amount</div>
                    <div class="summary-value accent">PHP <?= number_format($totalGross, 2); ?></div>
                </div>
            </div>

            <!-- Table -->
            <div class="table-wrap">
                <?php if (empty($topClients)): ?>
                    <div class="empty-state">
                        <div class="empty-state-icon">&#128196;</div>
                        <p>No payment data found for the selected period.</p>
                    </div>
                <?php else: ?>
                    <table>
                        <thead>
                            <tr>
                                <th class="text-center">Rank</th>
                                <th>Client</th>
                                <th class="text-right">Payments</th>
                                <th class="text-right">Amount Paid</th>
                                <th class="text-right">Tax Amount</th>
                                <th class="text-right">Gross Amount</th>
                                <th>First Payment</th>
                                <th>Last Payment</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $rank = 1; ?>
                            <?php foreach ($topClients as $client): ?>
                                <?php
                                $custID        = trim((string) ($client->CustID ?? ''));
                                $customerName  = trim((string) ($client->Customer ?? 'Unknown'));
                                $paymentCount  = (int) ($client->paymentCount ?? 0);
                                $amountPaid    = (float) ($client->totalAmountPaid ?? 0);
                                $taxAmount     = (float) ($client->totalTaxAmount ?? 0);
                                $grossAmount   = (float) ($client->totalGrossAmount ?? 0);
                                $firstPayment  = !empty($client->firstPaymentDate) && $client->firstPaymentDate !== '0000-00-00'
                                    ? date('M d, Y', strtotime($client->firstPaymentDate))
                                    : '—';
                                $lastPayment   = !empty($client->lastPaymentDate) && $client->lastPaymentDate !== '0000-00-00'
                                    ? date('M d, Y', strtotime($client->lastPaymentDate))
                                    : '—';
                                ?>
                                <tr>
                                    <td class="rank-cell">#<?= $rank++; ?></td>
                                    <td>
                                        <div class="client-name"><?= htmlspecialchars($customerName, ENT_QUOTES, 'UTF-8'); ?></div>
                                        <?php if ($custID !== ''): ?>
                                            <div class="client-id">ID: <?= htmlspecialchars($custID, ENT_QUOTES, 'UTF-8'); ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-right count"><?= number_format($paymentCount); ?></td>
                                    <td class="text-right amount paid">PHP <?= number_format($amountPaid, 2); ?></td>
                                    <td class="text-right amount">PHP <?= number_format($taxAmount, 2); ?></td>
                                    <td class="text-right amount">PHP <?= number_format($grossAmount, 2); ?></td>
                                    <td class="date-range"><?= htmlspecialchars($firstPayment, ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td class="date-range"><?= htmlspecialchars($lastPayment, ENT_QUOTES, 'UTF-8'); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
