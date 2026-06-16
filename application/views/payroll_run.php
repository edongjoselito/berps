<?php
$businessData = isset($business) ? $business : null;
$run = isset($run) ? $run : null;
$entries = isset($entries) && is_array($entries) ? $entries : array();
$deductionSummary = isset($deductionSummary) && is_array($deductionSummary) ? $deductionSummary : array();
$notice = isset($notice) ? trim((string) $notice) : '';
$noticeType = isset($noticeType) ? trim((string) $noticeType) : 'success';

$businessName = trim((string) ($businessData->CompName ?? 'BERPS'));

$money = function ($value) {
    return number_format((float) $value, 2);
};

$dateLabel = function ($value, $fallback = '-') {
    $value = trim((string) $value);
    if ($value === '' || $value === '0000-00-00') {
        return $fallback;
    }

    return date('M j, Y', strtotime($value));
};
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
                <div class="container-fluid payroll-run-page">

                    <style>
                        @import url('https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,100..1000;1,9..40,100..1000&display=swap');

                        .payroll-run-page {
                            --surface: rgba(255, 255, 255, 0.96);
                            --line: #e4ebf4;
                            --line-strong: #cfdbea;
                            --text: #142235;
                            --text-soft: #617489;
                            --text-faint: #8ea0b5;
                            --primary: #2563eb;
                            --primary-2: #1d4ed8;
                            --success: #059669;
                            --warning: #d97706;
                            --shadow-soft: 0 8px 24px rgba(15, 23, 42, 0.05);
                            --radius-xl: 16px;
                            --font-body: 'DM Sans', 'Segoe UI', Arial, sans-serif;
                            --font-mono: 'DM Sans', 'SFMono-Regular', Consolas, monospace;
                            font-family: var(--font-body);
                            background:
                                radial-gradient(circle at top left, rgba(37, 99, 235, 0.08), transparent 28%),
                                radial-gradient(circle at top right, rgba(16, 185, 129, 0.08), transparent 24%),
                                linear-gradient(180deg, #f8fbff 0%, #f4f7fb 100%);
                            min-height: 100vh;
                            padding-bottom: 100px;
                        }

                        .payroll-run-page * {
                            box-sizing: border-box;
                        }

                        .payroll-run-page .pr-header {
                            margin: 24px 0 22px;
                            display: flex;
                            justify-content: space-between;
                            align-items: flex-end;
                            gap: 16px;
                            flex-wrap: wrap;
                        }

                        .payroll-run-page .pr-title {
                            margin: 0;
                            font-size: 2rem;
                            font-weight: 800;
                            letter-spacing: -0.04em;
                            color: var(--text);
                            line-height: 1.08;
                        }

                        .payroll-run-page .pr-subtitle {
                            margin-top: 8px;
                            color: var(--text-soft);
                            font-size: 0.93rem;
                            font-weight: 500;
                        }

                        .payroll-run-page .btn-soft,
                        .payroll-run-page .btn-solid {
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

                        .payroll-run-page .btn-soft {
                            background: rgba(255, 255, 255, 0.88);
                            color: var(--text);
                            border: 1px solid var(--line-strong);
                            box-shadow: var(--shadow-soft);
                        }

                        .payroll-run-page .btn-solid {
                            background: linear-gradient(135deg, var(--primary), var(--primary-2));
                            color: #fff;
                            box-shadow: 0 10px 24px rgba(37, 99, 235, 0.22);
                        }

                        .payroll-run-page .btn-soft:hover,
                        .payroll-run-page .btn-solid:hover {
                            transform: translateY(-1px);
                            filter: brightness(1.02);
                            text-decoration: none;
                        }

                        .payroll-run-page .stat-strip {
                            display: grid;
                            grid-template-columns: repeat(5, minmax(0, 1fr));
                            gap: 14px;
                            margin-bottom: 20px;
                        }

                        .payroll-run-page .stat-card,
                        .payroll-run-page .panel-card,
                        .payroll-run-page .notice-card {
                            background: var(--surface);
                            border: 1px solid rgba(255, 255, 255, 0.72);
                            border-radius: var(--radius-xl);
                            box-shadow: var(--shadow-soft);
                            overflow: hidden;
                            margin-bottom: 18px;
                        }

                        .payroll-run-page .stat-card {
                            padding: 18px 18px 20px;
                            min-height: 116px;
                            position: relative;
                        }

                        .payroll-run-page .stat-card::before {
                            content: '';
                            position: absolute;
                            top: 0;
                            left: 0;
                            width: 100%;
                            height: 4px;
                            background: linear-gradient(135deg, var(--primary), var(--primary-2));
                        }

                        .payroll-run-page .stat-card.success::before {
                            background: linear-gradient(135deg, var(--success), #10b981);
                        }

                        .payroll-run-page .stat-card.warning::before {
                            background: linear-gradient(135deg, var(--warning), #f59e0b);
                        }

                        .payroll-run-page .stat-label {
                            color: var(--text-soft);
                            font-size: 0.75rem;
                            font-weight: 700;
                            text-transform: uppercase;
                            letter-spacing: 0.08em;
                            margin-bottom: 10px;
                        }

                        .payroll-run-page .stat-value {
                            color: var(--text);
                            font-size: 1.35rem;
                            font-weight: 800;
                            line-height: 1.1;
                            font-family: var(--font-mono);
                        }

                        .payroll-run-page .stat-meta {
                            margin-top: 10px;
                            color: var(--text-faint);
                            font-size: 0.8rem;
                            font-weight: 600;
                        }

                        .payroll-run-page .panel-body,
                        .payroll-run-page .notice-body {
                            padding: 20px 22px;
                        }

                        .payroll-run-page .panel-header {
                            display: flex;
                            justify-content: space-between;
                            align-items: flex-start;
                            gap: 12px;
                            margin-bottom: 16px;
                            flex-wrap: wrap;
                        }

                        .payroll-run-page .panel-title {
                            margin: 0;
                            font-size: 1.08rem;
                            font-weight: 800;
                            color: var(--text);
                        }

                        .payroll-run-page .panel-subtitle {
                            margin: 4px 0 0;
                            color: var(--text-soft);
                            font-size: 0.86rem;
                        }

                        .payroll-run-page .badge-soft {
                            display: inline-flex;
                            align-items: center;
                            gap: 6px;
                            padding: 6px 10px;
                            border-radius: 999px;
                            background: rgba(37, 99, 235, 0.1);
                            color: var(--primary-2);
                            font-size: 0.76rem;
                            font-weight: 700;
                        }

                        .payroll-run-page .summary-grid {
                            display: grid;
                            grid-template-columns: repeat(5, minmax(0, 1fr));
                            gap: 14px;
                        }

                        .payroll-run-page .table-responsive {
                            overflow-x: auto;
                        }

                        .payroll-run-page .table {
                            width: 100%;
                            margin-bottom: 0;
                        }

                        .payroll-run-page .table thead th {
                            border-top: none;
                            color: var(--text-soft);
                            font-size: 0.74rem;
                            font-weight: 700;
                            text-transform: uppercase;
                            letter-spacing: 0.08em;
                            background: rgba(248, 251, 255, 0.9);
                            white-space: nowrap;
                        }

                        .payroll-run-page .table td,
                        .payroll-run-page .table th {
                            padding: 11px 12px;
                            border-top: 1px solid var(--line);
                            vertical-align: middle;
                        }

                        .payroll-run-page .table-cell-label {
                            color: var(--text);
                            font-weight: 700;
                        }

                        .payroll-run-page .num-cell {
                            font-family: var(--font-mono);
                            font-weight: 800;
                            color: var(--text);
                            white-space: nowrap;
                        }

                        .payroll-run-page .empty-state {
                            padding: 22px 18px;
                            border: 1px dashed var(--line-strong);
                            border-radius: 14px;
                            text-align: center;
                            color: var(--text-soft);
                            background: rgba(248, 251, 255, 0.8);
                            font-weight: 600;
                        }

                        .payroll-run-page .notice-card.notice-success {
                            border-color: rgba(5, 150, 105, 0.18);
                            background: linear-gradient(135deg, rgba(5, 150, 105, 0.08), rgba(16, 185, 129, 0.04));
                        }

                        .payroll-run-page .notice-card.notice-danger {
                            border-color: rgba(225, 29, 72, 0.18);
                            background: linear-gradient(135deg, rgba(225, 29, 72, 0.08), rgba(251, 113, 133, 0.04));
                        }

                        @media (max-width: 1199px) {
                            .payroll-run-page .stat-strip,
                            .payroll-run-page .summary-grid {
                                grid-template-columns: repeat(2, minmax(0, 1fr));
                            }
                        }

                        @media (max-width: 767px) {
                            .payroll-run-page .stat-strip,
                            .payroll-run-page .summary-grid {
                                grid-template-columns: 1fr;
                            }
                        }
                    </style>

                    <div class="pr-header">
                        <div>
                            <h1 class="pr-title">Payroll Run Details</h1>
                            <div class="pr-subtitle">
                                <?= htmlspecialchars($businessName); ?> · <?= htmlspecialchars($dateLabel($run->periodStart ?? '')); ?> to <?= htmlspecialchars($dateLabel($run->periodEnd ?? '')); ?> · Pay date <?= htmlspecialchars($dateLabel($run->payDate ?? '')); ?>
                            </div>
                        </div>
                        <div style="display:flex; gap:10px; flex-wrap:wrap;">
                            <a href="<?= base_url(); ?>Page/payrollModule" class="btn-soft">
                                <i class="mdi mdi-arrow-left"></i> Back to Payroll
                            </a>
                        </div>
                    </div>

                    <?php if ($notice !== ''): ?>
                        <div class="notice-card <?= $noticeType === 'danger' ? 'notice-danger' : 'notice-success'; ?>">
                            <div class="notice-body"><?= htmlspecialchars($notice); ?></div>
                        </div>
                    <?php endif; ?>

                    <div class="stat-strip">
                        <div class="stat-card">
                            <div class="stat-label">Employees</div>
                            <div class="stat-value"><?= number_format((int) ($run->employeeCount ?? 0)); ?></div>
                            <div class="stat-meta">Included in this run</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-label">Gross Payroll</div>
                            <div class="stat-value">PHP <?= $money($run->totalGross ?? 0); ?></div>
                            <div class="stat-meta">Before all deductions</div>
                        </div>
                        <div class="stat-card warning">
                            <div class="stat-label">Total Deductions</div>
                            <div class="stat-value">PHP <?= $money($run->totalDeductions ?? 0); ?></div>
                            <div class="stat-meta">Posted deductions this cycle</div>
                        </div>
                        <div class="stat-card success">
                            <div class="stat-label">Net Payroll</div>
                            <div class="stat-value">PHP <?= $money($run->totalNet ?? 0); ?></div>
                            <div class="stat-meta">Take-home pay</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-label">Run Status</div>
                            <div class="stat-value"><?= htmlspecialchars(ucfirst((string) ($run->status ?? 'posted'))); ?></div>
                            <div class="stat-meta">Created by <?= htmlspecialchars((string) ($run->createdBy ?? 'System')); ?></div>
                        </div>
                    </div>

                    <div class="panel-card">
                        <div class="panel-body">
                            <div class="panel-header">
                                <div>
                                    <h3 class="panel-title">Deduction Payables</h3>
                                    <div class="panel-subtitle">Amounts generated by this payroll run</div>
                                </div>
                            </div>
                            <div class="summary-grid">
                                <div class="stat-card">
                                    <div class="stat-label">PhilHealth</div>
                                    <div class="stat-value">PHP <?= $money($deductionSummary['philhealth'] ?? 0); ?></div>
                                </div>
                                <div class="stat-card">
                                    <div class="stat-label">SSS</div>
                                    <div class="stat-value">PHP <?= $money($deductionSummary['sss'] ?? 0); ?></div>
                                </div>
                                <div class="stat-card">
                                    <div class="stat-label">Pag-IBIG</div>
                                    <div class="stat-value">PHP <?= $money($deductionSummary['pagibig'] ?? 0); ?></div>
                                </div>
                                <div class="stat-card warning">
                                    <div class="stat-label">Loans</div>
                                    <div class="stat-value">PHP <?= $money($deductionSummary['loan'] ?? 0); ?></div>
                                </div>
                                <div class="stat-card warning">
                                    <div class="stat-label">Cash Advances</div>
                                    <div class="stat-value">PHP <?= $money($deductionSummary['cash_advance'] ?? 0); ?></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="panel-card">
                        <div class="panel-body">
                            <div class="panel-header">
                                <div>
                                    <h3 class="panel-title">Payroll Entries</h3>
                                    <div class="panel-subtitle">Employee-by-employee payroll breakdown for this run</div>
                                </div>
                                <span class="badge-soft">PHP <?= $money($run->totalNet ?? 0); ?> net payout</span>
                            </div>
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Employee</th>
                                            <th>Gross</th>
                                            <th>PhilHealth</th>
                                            <th>SSS</th>
                                            <th>Pag-IBIG</th>
                                            <th>Loans</th>
                                            <th>Advances</th>
                                            <th>Total Deductions</th>
                                            <th>Net Pay</th>
                                            <th>Payslip</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($entries)): ?>
                                            <tr>
                                                <td colspan="10">
                                                    <div class="empty-state">No payroll entries were generated for this run.</div>
                                                </td>
                                            </tr>
                                        <?php else: ?>
                                            <?php foreach ($entries as $entry): ?>
                                                <tr>
                                                    <td class="table-cell-label"><?= htmlspecialchars((string) ($entry->employeeName ?? $entry->empID ?? '-')); ?></td>
                                                    <td class="num-cell">PHP <?= $money($entry->grossPay ?? 0); ?></td>
                                                    <td class="num-cell">PHP <?= $money($entry->philhealthAmount ?? 0); ?></td>
                                                    <td class="num-cell">PHP <?= $money($entry->sssAmount ?? 0); ?></td>
                                                    <td class="num-cell">PHP <?= $money($entry->pagibigAmount ?? 0); ?></td>
                                                    <td class="num-cell">PHP <?= $money($entry->loanAmount ?? 0); ?></td>
                                                    <td class="num-cell">PHP <?= $money($entry->cashAdvanceAmount ?? 0); ?></td>
                                                    <td class="num-cell">PHP <?= $money($entry->totalDeductions ?? 0); ?></td>
                                                    <td class="num-cell">PHP <?= $money($entry->netPay ?? 0); ?></td>
                                                    <td>
                                                        <a href="<?= base_url(); ?>Page/payrollPayslip?id=<?= urlencode((string) ($entry->entryID ?? 0)); ?>" class="btn-soft">
                                                            <i class="mdi mdi-printer"></i> Payslip
                                                        </a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="panel-card">
                        <div class="panel-body">
                            <div class="panel-header">
                                <div>
                                    <h3 class="panel-title">Deduction Lines</h3>
                                    <div class="panel-subtitle">Detailed payable breakdown for this payroll run</div>
                                </div>
                            </div>
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Type</th>
                                            <th>Label</th>
                                            <th>Count</th>
                                            <th>Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($deductionSummary['lines'])): ?>
                                            <tr>
                                                <td colspan="4">
                                                    <div class="empty-state">No deduction detail lines were found for this run.</div>
                                                </td>
                                            </tr>
                                        <?php else: ?>
                                            <?php foreach ($deductionSummary['lines'] as $line): ?>
                                                <tr>
                                                    <td class="table-cell-label"><?= htmlspecialchars(ucwords(str_replace('_', ' ', (string) ($line['itemType'] ?? 'other')))); ?></td>
                                                    <td><?= htmlspecialchars((string) ($line['itemLabel'] ?? '-')); ?></td>
                                                    <td><?= number_format((int) ($line['itemCount'] ?? 0)); ?></td>
                                                    <td class="num-cell">PHP <?= $money($line['totalAmount'] ?? 0); ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
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

</body>

</html>
