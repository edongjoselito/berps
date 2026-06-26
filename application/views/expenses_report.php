<?php
$businessData = isset($business) && is_array($business) && !empty($business)
    ? $business[0]
    : (isset($business) ? $business : null);
$expensesData = isset($data)  ? $data  : array();
$totalsData   = isset($data1) ? $data1 : array();

$companyName    = trim((string) ($businessData->CompName    ?? 'BERPS'));
$companyAddress = trim((string) ($businessData->CompAddress ?? ''));
$companyTin     = trim((string) ($businessData->CompTin     ?? ''));
$proprietor     = trim((string) ($businessData->Proprietor  ?? ''));

$fromDate    = isset($fromDate) ? $fromDate : date('Y-m-01');
$toDate      = isset($toDate)   ? $toDate   : date('Y-m-t');
$displayFrom = date('M d, Y', strtotime($fromDate));
$displayTo   = date('M d, Y', strtotime($toDate));

$totalAmount = 0;
if (!empty($totalsData) && isset($totalsData[0]->Total)) {
    $totalAmount = (float) $totalsData[0]->Total;
}

$expenseCount = count($expensesData);
$average      = $expenseCount > 0 ? $totalAmount / $expenseCount : 0;

$backUrl = base_url() . 'Page/expensesList';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Expenses Report | <?= htmlspecialchars($companyName, ENT_QUOTES, 'UTF-8'); ?></title>

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
            --danger:       #dc2626;
            --danger-deep:  #991b1b;
            --danger-soft:  #fef2f2;
            --danger-border:#fca5a5;
            --radius-sm:    6px;
            --radius-md:    10px;
            --radius-lg:    14px;
        }

        /* ── Base ────────────────────────────────────────────────────────── */
        body {
            font-family: var(--font-primary, Montserrat, Segoe UI, Arial, sans-serif);
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
            max-width: 900px;
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

        .btn-row { display: flex; gap: 8px; flex-wrap: wrap; }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            border: 1px solid transparent;
            border-radius: var(--radius-md);
            padding: 8px 16px;
            font-size: 0.85rem;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            transition: background 0.15s, box-shadow 0.15s, transform 0.1s;
        }
        .btn:hover { transform: translateY(-1px); }

        .btn-primary {
            background: var(--accent);
            color: #fff;
            border-color: var(--accent);
            box-shadow: 0 2px 8px rgba(30,64,175,.22);
        }
        .btn-primary:hover {
            background: var(--accent-hover);
            box-shadow: 0 4px 14px rgba(30,64,175,.32);
        }

        .btn-secondary {
            background: #fff;
            color: var(--ink-2);
            border-color: var(--line-mid);
        }
        .btn-secondary:hover {
            background: var(--surface-soft);
            border-color: var(--muted-light);
        }

        /* ── Report card ─────────────────────────────────────────────────── */
        .report-card {
            max-width: 900px;
            margin: 0 auto;
            background: var(--surface);
            border-radius: var(--radius-lg);
            box-shadow:
                0 1px 3px rgba(15,23,42,.06),
                0 8px 32px rgba(15,23,42,.10),
                0 0 0 1px rgba(15,23,42,.05);
            overflow: hidden;
        }

        .card-stripe {
            height: 4px;
            background: var(--accent);
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        /* ── Report header ───────────────────────────────────────────────── */
        .report-header {
            padding: 32px 40px 28px;
            border-bottom: 1px solid var(--line);
            background: var(--surface-soft);
        }

        .header-top {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 24px;
            margin-bottom: 24px;
        }

        .company-name {
            font-size: 1.6rem;
            font-weight: 700;
            letter-spacing: -0.04em;
            color: var(--ink);
            line-height: 1;
            margin-bottom: 8px;
        }

        .company-meta {
            font-size: 0.8rem;
            color: var(--muted);
            line-height: 1.85;
        }
        .company-meta div + div::before {
            content: '·';
            margin-right: 6px;
            color: var(--muted-light);
        }

        .report-title-block { text-align: right; flex-shrink: 0; }

        .report-type-label {
            display: inline-block;
            font-size: 0.68rem;
            font-weight: 700;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            padding: 3px 9px;
            border-radius: var(--radius-sm);
            background: var(--accent-soft);
            color: var(--accent-text);
            border: 1px solid #bfdbfe;
            margin-bottom: 8px;
        }

        .report-period-text {
            font-size: 1rem;
            font-weight: 700;
            color: var(--ink);
            margin-bottom: 4px;
        }

        .report-period-sub {
            font-size: 0.8rem;
            color: var(--muted);
        }

        /* ── Summary cards ───────────────────────────────────────────────── */
        .summary-cards {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 14px;
        }

        .summary-card {
            background: var(--surface);
            border: 1px solid var(--line);
            border-radius: var(--radius-md);
            padding: 14px 18px;
        }

        .summary-card .s-label {
            font-size: 0.7rem;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: var(--muted);
            margin-bottom: 5px;
        }

        .summary-card .s-value {
            font-size: 1.3rem;
            font-weight: 700;
            color: var(--ink);
            font-variant-numeric: tabular-nums;
        }

        .summary-card.total .s-value {
            color: var(--danger);
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        /* ── Table ───────────────────────────────────────────────────────── */
        .report-body { padding: 0; }

        .expenses-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .expenses-table thead {
            background: var(--surface-mid);
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .expenses-table th {
            padding: 12px 14px;
            text-align: left;
            font-size: 0.68rem;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: var(--muted);
            border-bottom: 1px solid var(--line-mid);
        }

        .expenses-table th.r { text-align: right; }
        .expenses-table th.c { text-align: center; }

        .expenses-table td {
            padding: 11px 14px;
            border-bottom: 1px solid var(--line);
            font-size: 0.83rem;
            vertical-align: top;
            color: var(--ink);
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .expenses-table tbody tr:nth-child(even) {
            background: var(--surface-soft);
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .expenses-table tbody tr:last-child td { border-bottom: none; }

        .expenses-table td.r { text-align: right; }
        .expenses-table td.c { text-align: center; }

        .col-no   { width: 44px; }
        .col-date { width: 100px; }
        .col-desc { /* flex */ }
        .col-cat  { width: 130px; }
        .col-resp { width: 130px; }
        .col-amt  { width: 110px; }

        .amount-cell {
            font-family: var(--font-primary, Montserrat, Segoe UI, Arial, sans-serif);
            font-weight: 600;
            color: var(--danger);
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .date-cell {
            white-space: nowrap;
            font-size: 0.78rem;
            color: var(--ink-2);
        }

        .category-badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 0.72rem;
            font-weight: 600;
            background: var(--accent-soft);
            color: var(--accent-text);
            border: 1px solid #bfdbfe;
            white-space: nowrap;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        /* ── Signature section ───────────────────────────────────────────── */
        .sig-section {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 28px;
            padding: 28px 40px 24px;
            border-top: 1px solid var(--line);
            border-bottom: 1px solid var(--line);
        }

        .sig-box { display: flex; flex-direction: column; }

        .sig-space { height: 48px; }

        .sig-line {
            height: 1px;
            background: var(--line-mid);
            margin-bottom: 7px;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .sig-name {
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--ink-2);
            min-height: 1.2em;
        }

        .sig-role {
            font-size: 0.72rem;
            color: var(--muted);
            margin-top: 2px;
        }

        /* ── Report footer ───────────────────────────────────────────────── */
        .report-footer {
            padding: 20px 40px;
            background: var(--surface-soft);
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            flex-wrap: wrap;
        }

        .footer-total {
            display: flex;
            align-items: baseline;
            gap: 12px;
        }

        .footer-total .ft-label {
            font-size: 0.85rem;
            color: var(--muted);
            font-weight: 600;
        }

        .footer-total .ft-value {
            font-size: 1.5rem;
            font-weight: 800;
            color: var(--danger);
            font-family: var(--font-primary, Montserrat, Segoe UI, Arial, sans-serif);
            font-variant-numeric: tabular-nums;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .print-timestamp {
            font-size: 0.75rem;
            color: var(--muted-light);
            font-family: var(--font-primary, Montserrat, Segoe UI, Arial, sans-serif);
        }

        /* ── Empty state ─────────────────────────────────────────────────── */
        .empty-state {
            padding: 60px 40px;
            text-align: center;
            color: var(--muted);
        }

        .empty-icon {
            width: 48px;
            height: 48px;
            margin: 0 auto 14px;
            opacity: 0.35;
        }

        /* ── Screen-only print hint ──────────────────────────────────────── */
        .print-hint {
            max-width: 900px;
            margin: 14px auto 0;
            background: var(--surface-mid);
            border-radius: var(--radius-md);
            padding: 9px 14px;
            font-size: 0.8rem;
            color: var(--muted);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        /* ── Responsive ──────────────────────────────────────────────────── */
        @media (max-width: 768px) {
            .header-top { flex-direction: column; gap: 16px; }
            .report-title-block { text-align: left; }
            .summary-cards { grid-template-columns: 1fr; }
            .sig-section { grid-template-columns: 1fr; }
            .report-header,
            .sig-section,
            .report-footer { padding-left: 20px; padding-right: 20px; }
            .col-cat, .col-resp { display: none; }
        }

        /* ── Print ───────────────────────────────────────────────────────── */
        @media print {
            @page {
                size: A4 portrait;
                margin: 14mm 14mm 18mm;
            }

            .screen-toolbar,
            .print-hint {
                display: none !important;
            }

            body {
                background: #fff;
                font-size: 11px;
            }

            .page-shell {
                padding: 0;
                min-height: unset;
            }

            .report-card {
                box-shadow: none;
                border: 1px solid #cbd5e1;
                border-radius: 0;
                max-width: 100%;
            }

            .report-header,
            .report-footer {
                background: #fff !important;
            }

            .expenses-table thead {
                background: #f1f5f9 !important;
            }

            .expenses-table tbody tr:nth-child(even) {
                background: #f8fafc !important;
            }

            /* Keep colors on print */
            .card-stripe        { background: #1e40af !important; }
            .amount-cell        { color: #dc2626 !important; }
            .ft-value           { color: #dc2626 !important; }
            .summary-card.total .s-value { color: #dc2626 !important; }
            .category-badge     { background: #eff6ff !important; color: #1e3a8a !important; }
            .sig-line           { background: #000 !important; }

            /* Avoid breaks inside key blocks */
            .report-header,
            .summary-cards,
            .sig-section,
            .report-footer      { break-inside: avoid; }

            /* Keep all columns visible on print */
            .col-cat, .col-resp { display: table-cell !important; }
        }
    </style>
    <link rel="stylesheet" href="<?= base_url('assets/css/fonts.css'); ?>">
</head>
<body>
<div class="page-shell">

    <!-- ── Screen toolbar ───────────────────────────────────────────── -->
    <div class="screen-toolbar">
        <div class="toolbar-label">
            <strong>Expenses Report</strong>
            <?= htmlspecialchars($displayFrom, ENT_QUOTES, 'UTF-8'); ?> &ndash; <?= htmlspecialchars($displayTo, ENT_QUOTES, 'UTF-8'); ?>
        </div>
        <div class="btn-row">
            <a href="<?= htmlspecialchars($backUrl, ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-secondary">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path d="M19 12H5M12 19l-7-7 7-7"/>
                </svg>
                Back to List
            </a>
            <button type="button" onclick="window.print()" class="btn btn-primary">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path d="M6 9V2h12v7M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2"/>
                    <path d="M6 14h12v8H6z"/>
                </svg>
                Print Report
            </button>
        </div>
    </div>

    <!-- ── Report card ──────────────────────────────────────────────── -->
    <div class="report-card">
        <div class="card-stripe"></div>

        <!-- Header -->
        <div class="report-header">
            <div class="header-top">
                <div>
                    <div class="company-name"><?= htmlspecialchars($companyName, ENT_QUOTES, 'UTF-8'); ?></div>
                    <div class="company-meta">
                        <?php if ($companyAddress): ?>
                            <div><?= htmlspecialchars($companyAddress, ENT_QUOTES, 'UTF-8'); ?></div>
                        <?php endif; ?>
                        <?php if ($companyTin): ?>
                            <div>TIN: <?= htmlspecialchars($companyTin, ENT_QUOTES, 'UTF-8'); ?></div>
                        <?php endif; ?>
                        <?php if ($proprietor): ?>
                            <div>Proprietor: <?= htmlspecialchars($proprietor, ENT_QUOTES, 'UTF-8'); ?></div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="report-title-block">
                    <div class="report-type-label">Expenses Report</div>
                    <div class="report-period-text">
                        <?php if ($fromDate === $toDate): ?>
                            <?= htmlspecialchars($displayFrom, ENT_QUOTES, 'UTF-8'); ?>
                        <?php else: ?>
                            <?= htmlspecialchars($displayFrom, ENT_QUOTES, 'UTF-8'); ?> &ndash; <?= htmlspecialchars($displayTo, ENT_QUOTES, 'UTF-8'); ?>
                        <?php endif; ?>
                    </div>
                    <div class="report-period-sub">Period covered</div>
                </div>
            </div>

            <!-- Summary cards -->
            <div class="summary-cards">
                <div class="summary-card">
                    <div class="s-label">No. of Expenses</div>
                    <div class="s-value"><?= number_format($expenseCount); ?></div>
                </div>
                <div class="summary-card total">
                    <div class="s-label">Total Amount</div>
                    <div class="s-value">PHP <?= number_format($totalAmount, 2); ?></div>
                </div>
                <div class="summary-card">
                    <div class="s-label">Average per Entry</div>
                    <div class="s-value">PHP <?= number_format($average, 2); ?></div>
                </div>
            </div>
        </div>

        <!-- Table body -->
        <div class="report-body">
            <?php if (!empty($expensesData)): ?>
                <table class="expenses-table">
                    <thead>
                        <tr>
                            <th class="col-no c">#</th>
                            <th class="col-date">Date</th>
                            <th class="col-desc">Description</th>
                            <th class="col-cat">Category</th>
                            <th class="col-resp">Responsible</th>
                            <th class="col-amt r">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($expensesData as $index => $row):
                            $rowDate = !empty($row->ExpenseDate) && $row->ExpenseDate !== '0000-00-00'
                                ? date('M d, Y', strtotime($row->ExpenseDate))
                                : '-';
                            $rowAmount      = (float)  ($row->Amount      ?? 0);
                            $rowDescription = trim((string) ($row->Description ?? ''));
                            $rowCategory    = trim((string) ($row->Category    ?? ''));
                            $rowResponsible = trim((string) ($row->Responsible ?? ''));
                        ?>
                        <tr>
                            <td class="c" style="color:var(--muted-light);font-size:.78rem;"><?= $index + 1; ?></td>
                            <td class="date-cell"><?= htmlspecialchars($rowDate, ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?= htmlspecialchars($rowDescription, ENT_QUOTES, 'UTF-8'); ?></td>
                            <td>
                                <?php if ($rowCategory): ?>
                                    <span class="category-badge"><?= htmlspecialchars($rowCategory, ENT_QUOTES, 'UTF-8'); ?></span>
                                <?php else: ?>
                                    <span style="color:var(--muted-light);">&mdash;</span>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($rowResponsible, ENT_QUOTES, 'UTF-8'); ?></td>
                            <td class="r amount-cell"><?= number_format($rowAmount, 2); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="empty-state">
                    <svg class="empty-icon" viewBox="0 0 48 48" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                        <rect x="8" y="6" width="32" height="36" rx="3"/>
                        <path d="M16 16h16M16 22h16M16 28h10"/>
                    </svg>
                    <p>No expenses found for the selected period.</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Signature section -->
        <div class="sig-section">
            <div class="sig-box">
                <div class="sig-space"></div>
                <div class="sig-line"></div>
                <div class="sig-name"><?= htmlspecialchars($processedBy ?? '', ENT_QUOTES, 'UTF-8'); ?></div>
                <div class="sig-role">Prepared by</div>
            </div>
            <div class="sig-box">
                <div class="sig-space"></div>
                <div class="sig-line"></div>
                <div class="sig-name">&nbsp;</div>
                <div class="sig-role">Checked by</div>
            </div>
            <div class="sig-box">
                <div class="sig-space"></div>
                <div class="sig-line"></div>
                <div class="sig-name"><?= htmlspecialchars($proprietor, ENT_QUOTES, 'UTF-8'); ?></div>
                <div class="sig-role">Approved by / Proprietor</div>
            </div>
        </div>

        <!-- Footer -->
        <div class="report-footer">
            <div class="footer-total">
                <span class="ft-label">Grand Total:</span>
                <span class="ft-value">PHP <?= number_format($totalAmount, 2); ?></span>
            </div>
            <div class="print-timestamp">
                Printed on <?= date('F j, Y g:i A'); ?>
            </div>
        </div>

    </div><!-- /.report-card -->

    <!-- ── Screen-only print hint ────────────────────────────────────── -->
    <div class="print-hint">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true" style="flex-shrink:0">
            <circle cx="12" cy="12" r="10"/>
            <path d="M12 8v4M12 16h.01"/>
        </svg>
        Signature lines and all columns print automatically. Use A4 or Letter paper, portrait orientation.
    </div>

</div><!-- /.page-shell -->

<script>
(function () {
    var params = new URLSearchParams(window.location.search);
    if (params.get('print') === '1') {
        window.addEventListener('load', function () {
            setTimeout(function () { window.print(); }, 400);
        });
    }
}());
</script>

</body>
</html>