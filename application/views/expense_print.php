<?php
$expenseData = isset($expense) ? $expense : null;
$businessData = isset($business) ? $business : null;

$companyName    = trim((string) ($businessData->CompName    ?? 'BERPS'));
$companyAddress = trim((string) ($businessData->CompAddress ?? ''));
$companyTin     = trim((string) ($businessData->CompTin     ?? ''));
$proprietor     = trim((string) ($businessData->Proprietor  ?? ''));

$expenseId      = trim((string) ($expenseData->expensesid  ?? ''));
$description    = trim((string) ($expenseData->Description ?? ''));
$amount         = (float) ($expenseData->Amount            ?? 0);
$responsible    = trim((string) ($expenseData->Responsible ?? ''));
$category       = trim((string) ($expenseData->Category    ?? ''));
$expenseDateRaw = trim((string) ($expenseData->ExpenseDate ?? ''));
$expenseDate    = $expenseDateRaw !== '' && $expenseDateRaw !== '0000-00-00'
    ? date('F j, Y', strtotime($expenseDateRaw))
    : 'Not specified';
$processedBy    = trim((string) ($expenseData->processedBy ?? ''));

$backUrl        = base_url() . 'Page/expensesList';
$paddedId       = str_pad($expenseId, 6, '0', STR_PAD_LEFT);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Expense #<?= htmlspecialchars($expenseId, ENT_QUOTES, 'UTF-8'); ?> | <?= htmlspecialchars($companyName, ENT_QUOTES, 'UTF-8'); ?></title>

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
            font-family: "Segoe UI", system-ui, -apple-system, sans-serif;
            font-size: 14px;
            line-height: 1.6;
            color: var(--ink);
            background: #e9eef6;
        }

        /* ── Page shell ──────────────────────────────────────────────────── */
        .page-shell {
            min-height: 100vh;
            padding: 36px 20px 48px;
        }

        /* ── Screen-only toolbar ─────────────────────────────────────────── */
        .screen-toolbar {
            max-width: 760px;
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

        /* ── Card ────────────────────────────────────────────────────────── */
        .voucher-card {
            max-width: 760px;
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
            background: var(--danger);
            /* keep color on print */
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .card-body { padding: 40px 44px 36px; }

        /* ── Hero ────────────────────────────────────────────────────────── */
        .hero {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 28px;
            padding-bottom: 28px;
            border-bottom: 1px solid var(--line);
            margin-bottom: 26px;
        }

        .brand-name {
            font-size: 1.75rem;
            font-weight: 700;
            letter-spacing: -0.04em;
            color: var(--ink);
            line-height: 1;
            margin-bottom: 8px;
        }

        .brand-meta {
            font-size: 0.8rem;
            color: var(--muted);
            line-height: 1.85;
        }
        .brand-meta div + div::before {
            content: '·';
            margin-right: 6px;
            color: var(--muted-light);
        }

        .expense-mark { text-align: right; flex-shrink: 0; }

        .ev-badge {
            display: inline-block;
            font-size: 0.68rem;
            font-weight: 700;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            padding: 3px 9px;
            border-radius: var(--radius-sm);
            background: var(--danger-soft);
            color: var(--danger-deep);
            border: 1px solid var(--danger-border);
            margin-bottom: 10px;
        }

        .ev-amount {
            font-size: 2.2rem;
            font-weight: 800;
            letter-spacing: -0.05em;
            color: var(--danger);
            font-variant-numeric: tabular-nums;
            line-height: 1.05;
            margin-bottom: 8px;
        }

        .ev-id {
            font-size: 0.85rem;
            color: var(--muted);
            font-family: "SF Mono", Consolas, "Courier New", monospace;
        }

        /* ── Detail grid ─────────────────────────────────────────────────── */
        .detail-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 20px 32px;
            padding-bottom: 26px;
            border-bottom: 1px solid var(--line);
        }

        .detail-item { display: flex; flex-direction: column; gap: 5px; }
        .detail-item.full { grid-column: 1 / -1; }

        .detail-label {
            font-size: 0.72rem;
            font-weight: 700;
            letter-spacing: 0.09em;
            text-transform: uppercase;
            color: var(--muted);
        }

        .detail-value {
            font-size: 0.975rem;
            color: var(--ink);
            font-weight: 600;
        }
        .detail-value.desc {
            font-size: 1.05rem;
            font-weight: 400;
            line-height: 1.55;
        }
        .detail-value.amount-val {
            font-size: 1.4rem;
            font-weight: 800;
            color: var(--danger);
            font-variant-numeric: tabular-nums;
        }

        /* ── Signature section ───────────────────────────────────────────── */
        .sig-section {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 24px;
            padding: 28px 0 22px;
            border-bottom: 1px solid var(--line);
        }

        .sig-box { display: flex; flex-direction: column; }

        .sig-space {
            height: 52px; /* room to physically sign */
        }

        .sig-line {
            height: 1px;
            background: var(--line-mid);
            margin-bottom: 8px;
        }

        .sig-name {
            font-size: 0.88rem;
            font-weight: 600;
            color: var(--ink-2);
            min-height: 1.2em; /* keep layout stable when name is empty */
        }

        .sig-role {
            font-size: 0.75rem;
            color: var(--muted);
            margin-top: 2px;
        }

        /* ── Footer ──────────────────────────────────────────────────────── */
        .card-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 20px;
            margin-top: 2px;
            gap: 12px;
            flex-wrap: wrap;
        }

        .recorded-by {
            font-size: 0.85rem;
            color: var(--muted);
        }
        .recorded-by strong {
            color: var(--ink-2);
            font-weight: 600;
        }

        .print-timestamp {
            font-size: 0.78rem;
            color: var(--muted-light);
            font-family: "SF Mono", Consolas, "Courier New", monospace;
        }

        /* ── Screen-only print hint ──────────────────────────────────────── */
        .print-hint {
            max-width: 760px;
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
        @media (max-width: 600px) {
            .hero { flex-direction: column; gap: 20px; }
            .expense-mark { text-align: left; }
            .detail-grid { grid-template-columns: 1fr; }
            .sig-section { grid-template-columns: 1fr; }
            .card-body { padding: 28px 24px; }
        }

        /* ── Print ───────────────────────────────────────────────────────── */
        @media print {
            /* Remove browser default header/footer (URL, date) */
            @page {
                size: A4 portrait;
                margin: 18mm 16mm 20mm;
            }

            /* Hide everything that should not print */
            .screen-toolbar,
            .print-hint {
                display: none !important;
            }

            body {
                background: #fff;
                font-size: 12px;
            }

            .page-shell {
                padding: 0;
                min-height: unset;
            }

            .voucher-card {
                box-shadow: none;
                border: 1px solid #cbd5e1;
                border-radius: 0;
                max-width: 100%;
            }

            /* Ensure stripe prints in color */
            .card-stripe {
                background: #dc2626 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            /* Prevent awkward page breaks */
            .hero,
            .detail-grid,
            .sig-section,
            .card-footer {
                break-inside: avoid;
            }

            /* Signature lines should be visible on paper */
            .sig-line {
                background: #000 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            /* Danger red should survive print */
            .ev-amount,
            .detail-value.amount-val {
                color: #dc2626 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            /* Badge */
            .ev-badge {
                background: #fef2f2 !important;
                color: #991b1b !important;
                border-color: #fca5a5 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
    </style>
</head>
<body>
<div class="page-shell">

    <!-- ── Screen toolbar (hidden on print) ─────────────────────────── -->
    <div class="screen-toolbar">
        <div class="toolbar-label">
            <strong>Expense Voucher</strong>
            #EXP-<?= htmlspecialchars($paddedId, ENT_QUOTES, 'UTF-8'); ?>
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
                Print
            </button>
        </div>
    </div>

    <!-- ── Voucher card ──────────────────────────────────────────────── -->
    <div class="voucher-card">
        <div class="card-stripe"></div>
        <div class="card-body">

            <!-- Hero -->
            <div class="hero">
                <div class="brand">
                    <div class="brand-name"><?= htmlspecialchars($companyName, ENT_QUOTES, 'UTF-8'); ?></div>
                    <div class="brand-meta">
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

                <div class="expense-mark">
                    <div class="ev-badge">Expense Voucher</div>
                    <div class="ev-amount">PHP <?= number_format($amount, 2); ?></div>
                    <div class="ev-id">#EXP-<?= htmlspecialchars($paddedId, ENT_QUOTES, 'UTF-8'); ?></div>
                </div>
            </div>

            <!-- Detail grid -->
            <div class="detail-grid">
                <div class="detail-item">
                    <div class="detail-label">Date Incurred</div>
                    <div class="detail-value"><?= htmlspecialchars($expenseDate, ENT_QUOTES, 'UTF-8'); ?></div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Category</div>
                    <div class="detail-value"><?= htmlspecialchars($category, ENT_QUOTES, 'UTF-8'); ?></div>
                </div>
                <div class="detail-item full">
                    <div class="detail-label">Description</div>
                    <div class="detail-value desc"><?= htmlspecialchars($description, ENT_QUOTES, 'UTF-8'); ?></div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Responsible Person</div>
                    <div class="detail-value"><?= htmlspecialchars($responsible, ENT_QUOTES, 'UTF-8'); ?></div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Amount</div>
                    <div class="detail-value amount-val">PHP <?= number_format($amount, 2); ?></div>
                </div>
            </div>

            <!-- Signature section -->
            <div class="sig-section">
                <div class="sig-box">
                    <div class="sig-space"></div>
                    <div class="sig-line"></div>
                    <div class="sig-name"><?= htmlspecialchars($responsible, ENT_QUOTES, 'UTF-8'); ?></div>
                    <div class="sig-role">Prepared by / Claimant</div>
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
            <div class="card-footer">
                <div class="recorded-by">
                    Recorded by: <strong><?= htmlspecialchars($processedBy, ENT_QUOTES, 'UTF-8'); ?></strong>
                </div>
                <div class="print-timestamp">
                    Printed on <?= date('F j, Y g:i A'); ?>
                </div>
            </div>

        </div><!-- /.card-body -->
    </div><!-- /.voucher-card -->

    <!-- ── Screen-only print hint ────────────────────────────────────── -->
    <div class="print-hint">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true" style="flex-shrink:0">
            <circle cx="12" cy="12" r="10"/>
            <path d="M12 8v4M12 16h.01"/>
        </svg>
        Signature lines print automatically. Use A4 or Letter paper, portrait orientation.
    </div>

</div><!-- /.page-shell -->

<?php if (!empty($autoPrint)): ?>
<script>
(function () {
    window.addEventListener('load', function () {
        setTimeout(function () { window.print(); }, 350);
    });
}());
</script>
<?php endif; ?>

</body>
</html>