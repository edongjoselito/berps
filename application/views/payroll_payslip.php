<?php
$businessData = isset($business) ? $business : null;
$entry = isset($entry) ? $entry : null;
$items = isset($items) && is_array($items) ? $items : array();
$autoPrint = !empty($autoPrint);

$businessName = trim((string) ($businessData->CompName ?? 'BERPS'));
$businessAddress = trim((string) ($businessData->CompAddress ?? ''));
$businessTin = trim((string) ($businessData->CompTin ?? ''));

$money = function ($value) {
    return number_format((float) $value, 2);
};

$dateLabel = function ($value, $fallback = '-') {
    $value = trim((string) $value);
    if ($value === '' || $value === '0000-00-00') {
        return $fallback;
    }

    return date('F j, Y', strtotime($value));
};
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Payroll Payslip</title>
    <style>

        :root {
            --text: #111827;
            --text-soft: #4b5563;
            --line: #dbe3ef;
            --surface: #ffffff;
            --accent: #2563eb;
            --shadow: 0 20px 45px rgba(15, 23, 42, 0.08);
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            background: #eef4fb;
            color: var(--text);
            font-family: var(--font-primary, Montserrat, Segoe UI, Arial, sans-serif);
            padding: 28px;
        }

        .page-actions {
            max-width: 920px;
            margin: 0 auto 16px;
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            flex-wrap: wrap;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 16px;
            border-radius: 12px;
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 700;
            border: 1px solid transparent;
        }

        .btn-soft {
            background: #fff;
            color: var(--text);
            border-color: #cfdbea;
        }

        .btn-solid {
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            color: #fff;
        }

        .sheet {
            max-width: 920px;
            margin: 0 auto;
            background: var(--surface);
            border-radius: 22px;
            box-shadow: var(--shadow);
            overflow: hidden;
        }

        .sheet-head {
            padding: 28px 32px 22px;
            background: linear-gradient(135deg, rgba(37, 99, 235, 0.08), rgba(16, 185, 129, 0.05));
            border-bottom: 1px solid var(--line);
        }

        .eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 7px 12px;
            border-radius: 999px;
            background: rgba(37, 99, 235, 0.10);
            color: #1d4ed8;
            font-size: 0.74rem;
            font-weight: 800;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            margin-bottom: 12px;
        }

        .title-row {
            display: flex;
            justify-content: space-between;
            gap: 24px;
            flex-wrap: wrap;
            align-items: flex-start;
        }

        .sheet-title {
            margin: 0;
            font-size: 2rem;
            font-weight: 800;
            letter-spacing: -0.04em;
        }

        .sheet-subtitle {
            margin-top: 8px;
            color: var(--text-soft);
            font-size: 0.94rem;
        }

        .company-card {
            text-align: right;
            max-width: 320px;
        }

        .company-name {
            font-size: 1.18rem;
            font-weight: 800;
        }

        .company-meta {
            margin-top: 6px;
            color: var(--text-soft);
            font-size: 0.88rem;
            line-height: 1.5;
        }

        .sheet-body {
            padding: 26px 32px 32px;
        }

        .info-grid,
        .summary-grid {
            display: grid;
            gap: 14px;
        }

        .info-grid {
            grid-template-columns: repeat(4, minmax(0, 1fr));
            margin-bottom: 18px;
        }

        .summary-grid {
            grid-template-columns: repeat(3, minmax(0, 1fr));
            margin-top: 18px;
        }

        .info-card,
        .summary-card {
            border: 1px solid var(--line);
            border-radius: 16px;
            padding: 14px 16px;
            background: #fbfdff;
        }

        .info-label,
        .summary-label {
            display: block;
            color: var(--text-soft);
            font-size: 0.74rem;
            font-weight: 800;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            margin-bottom: 8px;
        }

        .info-value {
            font-size: 1rem;
            font-weight: 700;
        }

        .summary-value {
            font-size: 1.3rem;
            font-weight: 800;
        }

        .summary-card.net .summary-value {
            color: #059669;
        }

        .section {
            margin-top: 18px;
            border: 1px solid var(--line);
            border-radius: 18px;
            overflow: hidden;
        }

        .section-header {
            padding: 14px 18px;
            background: #f8fbff;
            border-bottom: 1px solid var(--line);
        }

        .section-title {
            margin: 0;
            font-size: 1rem;
            font-weight: 800;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            padding: 12px 16px;
            border-top: 1px solid var(--line);
            vertical-align: middle;
        }

        thead th {
            border-top: none;
            background: #fcfdff;
            color: var(--text-soft);
            font-size: 0.74rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            text-align: left;
        }

        .amount {
            text-align: right;
            font-weight: 800;
            white-space: nowrap;
        }

        .totals-row td {
            font-weight: 800;
            border-top: 2px solid var(--line);
        }

        .footnote {
            margin-top: 18px;
            color: var(--text-soft);
            font-size: 0.84rem;
            line-height: 1.6;
        }

        @media (max-width: 900px) {
            body {
                padding: 16px;
            }

            .info-grid,
            .summary-grid {
                grid-template-columns: 1fr;
            }

            .company-card {
                text-align: left;
            }
        }

        @media print {
            body {
                background: #fff;
                padding: 0;
            }

            .page-actions {
                display: none !important;
            }

            .sheet {
                max-width: none;
                box-shadow: none;
                border-radius: 0;
            }

            * {
                color: #000 !important;
                box-shadow: none !important;
                text-shadow: none !important;
            }
        }
    </style>
    <link rel="stylesheet" href="<?= base_url('assets/css/fonts.css'); ?>">
</head>
<body>

    <div class="page-actions">
        <a href="<?= base_url(); ?>Page/payrollRun?id=<?= urlencode((string) ($entry->payrollID ?? 0)); ?>" class="btn btn-soft">Back to Payroll Run</a>
        <a href="<?= base_url(); ?>Page/payrollPayslip?id=<?= urlencode((string) ($entry->entryID ?? 0)); ?>&print=1" class="btn btn-solid">Print Payslip</a>
    </div>

    <div class="sheet">
        <div class="sheet-head">
            <div class="eyebrow">Payroll Payslip</div>
            <div class="title-row">
                <div>
                    <h1 class="sheet-title">Staff Payslip</h1>
                    <div class="sheet-subtitle">
                        Payroll period <?= htmlspecialchars($dateLabel($entry->periodStart ?? '')); ?> to <?= htmlspecialchars($dateLabel($entry->periodEnd ?? '')); ?> · Pay date <?= htmlspecialchars($dateLabel($entry->payDate ?? '')); ?>
                    </div>
                </div>
                <div class="company-card">
                    <div class="company-name"><?= htmlspecialchars($businessName); ?></div>
                    <div class="company-meta">
                        <?php if ($businessAddress !== ''): ?>
                            <div><?= htmlspecialchars($businessAddress); ?></div>
                        <?php endif; ?>
                        <?php if ($businessTin !== ''): ?>
                            <div>TIN: <?= htmlspecialchars($businessTin); ?></div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="sheet-body">
            <div class="info-grid">
                <div class="info-card">
                    <span class="info-label">Employee</span>
                    <div class="info-value"><?= htmlspecialchars((string) ($entry->employeeName ?? '-')); ?></div>
                </div>
                <div class="info-card">
                    <span class="info-label">Employee ID</span>
                    <div class="info-value"><?= htmlspecialchars((string) ($entry->empID ?? '-')); ?></div>
                </div>
                <div class="info-card">
                    <span class="info-label">Pay Frequency</span>
                    <div class="info-value"><?= htmlspecialchars(ucfirst((string) ($entry->payFrequency ?? 'monthly'))); ?></div>
                </div>
                <div class="info-card">
                    <span class="info-label">Generated By</span>
                    <div class="info-value"><?= htmlspecialchars((string) ($entry->payrollCreatedBy ?? 'System')); ?></div>
                </div>
            </div>

            <div class="section">
                <div class="section-header">
                    <h2 class="section-title">Earnings</h2>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>Description</th>
                            <th class="amount">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Fixed Monthly Salary</td>
                            <td class="amount">PHP <?= $money($entry->grossPay ?? 0); ?></td>
                        </tr>
                        <tr class="totals-row">
                            <td>Gross Pay</td>
                            <td class="amount">PHP <?= $money($entry->grossPay ?? 0); ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="section">
                <div class="section-header">
                    <h2 class="section-title">Deductions</h2>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>Description</th>
                            <th class="amount">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($items)): ?>
                            <tr>
                                <td colspan="2">No deductions were applied to this payslip.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($items as $item): ?>
                                <tr>
                                    <td><?= htmlspecialchars((string) ($item->itemLabel ?? 'Deduction')); ?></td>
                                    <td class="amount">PHP <?= $money($item->amount ?? 0); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        <tr class="totals-row">
                            <td>Total Deductions</td>
                            <td class="amount">PHP <?= $money($entry->totalDeductions ?? 0); ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="summary-grid">
                <div class="summary-card">
                    <span class="summary-label">Gross Pay</span>
                    <div class="summary-value">PHP <?= $money($entry->grossPay ?? 0); ?></div>
                </div>
                <div class="summary-card">
                    <span class="summary-label">Total Deductions</span>
                    <div class="summary-value">PHP <?= $money($entry->totalDeductions ?? 0); ?></div>
                </div>
                <div class="summary-card net">
                    <span class="summary-label">Net Pay</span>
                    <div class="summary-value">PHP <?= $money($entry->netPay ?? 0); ?></div>
                </div>
            </div>

            <?php if (!empty($entry->remarks) || !empty($entry->payrollNotes)): ?>
                <div class="footnote">
                    <?php if (!empty($entry->remarks)): ?>
                        <div><strong>Payroll Note:</strong> <?= htmlspecialchars((string) $entry->remarks); ?></div>
                    <?php endif; ?>
                    <?php if (!empty($entry->payrollNotes)): ?>
                        <div><strong>Run Note:</strong> <?= htmlspecialchars((string) $entry->payrollNotes); ?></div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        <?php if ($autoPrint): ?>
            window.addEventListener('load', function() {
                window.print();
            });
        <?php endif; ?>
    </script>
</body>
</html>
