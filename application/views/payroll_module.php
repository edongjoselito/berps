<?php
$businessData = isset($business) ? $business : null;
$employees = isset($employees) && is_array($employees) ? $employees : array();
$payrollRuns = isset($payrollRuns) && is_array($payrollRuns) ? $payrollRuns : array();
$activeLoans = isset($activeLoans) && is_array($activeLoans) ? $activeLoans : array();
$activeCashAdvances = isset($activeCashAdvances) && is_array($activeCashAdvances) ? $activeCashAdvances : array();
$deductionSummary = isset($deductionSummary) && is_array($deductionSummary) ? $deductionSummary : array();
$summaryCards = isset($summaryCards) && is_array($summaryCards) ? $summaryCards : array();

$filterDateFrom = isset($filterDateFrom) ? trim((string) $filterDateFrom) : '';
$filterDateTo = isset($filterDateTo) ? trim((string) $filterDateTo) : '';
$rangeLabel = isset($rangeLabel) ? (string) $rangeLabel : 'Current range';
$generatedAt = isset($generatedAt) ? (string) $generatedAt : date('F j, Y h:i A');
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
                <div class="container-fluid payroll-module-page">

                    <style>
                        @import url('https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,100..1000;1,9..40,100..1000&display=swap');

                        .payroll-module-page {
                            --bg: #f5f7fb;
                            --surface: rgba(255, 255, 255, 0.96);
                            --surface-soft: #f7fbff;
                            --line: #e4ebf4;
                            --line-strong: #cfdbea;
                            --text: #142235;
                            --text-soft: #617489;
                            --text-faint: #8ea0b5;
                            --primary: #2563eb;
                            --primary-2: #1d4ed8;
                            --success: #059669;
                            --warning: #d97706;
                            --danger: #e11d48;
                            --shadow-soft: 0 8px 24px rgba(15, 23, 42, 0.05);
                            --radius-xl: 16px;
                            --radius-lg: 12px;
                            --radius-md: 10px;
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

                        .payroll-module-page * {
                            box-sizing: border-box;
                        }

                        .payroll-module-page .pm-header {
                            margin: 24px 0 22px;
                            display: flex;
                            justify-content: space-between;
                            align-items: flex-end;
                            gap: 16px;
                            flex-wrap: wrap;
                        }

                        .payroll-module-page .pm-eyebrow {
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

                        .payroll-module-page .pm-eyebrow::before {
                            content: '';
                            width: 8px;
                            height: 8px;
                            border-radius: 50%;
                            background: linear-gradient(135deg, var(--primary), var(--primary-2));
                            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.10);
                        }

                        .payroll-module-page .pm-title {
                            margin: 0;
                            font-size: 2rem;
                            font-weight: 800;
                            letter-spacing: -0.04em;
                            color: var(--text);
                            line-height: 1.08;
                        }

                        .payroll-module-page .pm-subtitle {
                            margin-top: 8px;
                            color: var(--text-soft);
                            font-size: 0.93rem;
                            font-weight: 500;
                            max-width: 760px;
                        }

                        .payroll-module-page .pm-actions {
                            display: flex;
                            gap: 10px;
                            flex-wrap: wrap;
                            justify-content: flex-end;
                        }

                        .payroll-module-page .btn-soft,
                        .payroll-module-page .btn-solid,
                        .payroll-module-page .btn-success-soft,
                        .payroll-module-page .btn-warning-soft {
                            display: inline-flex;
                            align-items: center;
                            gap: 8px;
                            padding: 10px 16px;
                            border-radius: 12px;
                            font-size: 0.88rem;
                            font-weight: 700;
                            text-decoration: none;
                            border: 1px solid transparent;
                            transition: transform 0.16s ease, box-shadow 0.16s ease, filter 0.16s ease;
                        }

                        .payroll-module-page .btn-soft {
                            background: rgba(255, 255, 255, 0.88);
                            color: var(--text);
                            border-color: var(--line-strong);
                            box-shadow: var(--shadow-soft);
                        }

                        .payroll-module-page .btn-solid {
                            background: linear-gradient(135deg, var(--primary), var(--primary-2));
                            color: #fff;
                            box-shadow: 0 10px 24px rgba(37, 99, 235, 0.22);
                        }

                        .payroll-module-page .btn-success-soft {
                            background: rgba(5, 150, 105, 0.1);
                            color: var(--success);
                        }

                        .payroll-module-page .btn-warning-soft {
                            background: rgba(217, 119, 6, 0.1);
                            color: var(--warning);
                        }

                        .payroll-module-page .btn-soft:hover,
                        .payroll-module-page .btn-solid:hover,
                        .payroll-module-page .btn-success-soft:hover,
                        .payroll-module-page .btn-warning-soft:hover {
                            transform: translateY(-1px);
                            filter: brightness(1.02);
                            text-decoration: none;
                        }

                        .payroll-module-page .panel-card,
                        .payroll-module-page .stat-card,
                        .payroll-module-page .filter-card,
                        .payroll-module-page .notice-card {
                            background: var(--surface);
                            border: 1px solid rgba(255, 255, 255, 0.72);
                            border-radius: var(--radius-xl);
                            box-shadow: var(--shadow-soft);
                            overflow: hidden;
                            margin-bottom: 18px;
                        }

                        .payroll-module-page .panel-body,
                        .payroll-module-page .filter-body,
                        .payroll-module-page .notice-body {
                            padding: 20px 22px;
                        }

                        .payroll-module-page .stat-strip {
                            display: grid;
                            grid-template-columns: repeat(6, minmax(0, 1fr));
                            gap: 14px;
                            margin-bottom: 20px;
                        }

                        .payroll-module-page .stat-card {
                            padding: 18px 18px 20px;
                            min-height: 118px;
                            position: relative;
                        }

                        .payroll-module-page .stat-card::before {
                            content: '';
                            position: absolute;
                            top: 0;
                            left: 0;
                            width: 100%;
                            height: 4px;
                            background: linear-gradient(135deg, var(--primary), var(--primary-2));
                        }

                        .payroll-module-page .stat-card.success::before {
                            background: linear-gradient(135deg, var(--success), #10b981);
                        }

                        .payroll-module-page .stat-card.warning::before {
                            background: linear-gradient(135deg, var(--warning), #f59e0b);
                        }

                        .payroll-module-page .stat-card.danger::before {
                            background: linear-gradient(135deg, var(--danger), #fb7185);
                        }

                        .payroll-module-page .stat-label {
                            color: var(--text-soft);
                            font-size: 0.75rem;
                            font-weight: 700;
                            text-transform: uppercase;
                            letter-spacing: 0.08em;
                            margin-bottom: 10px;
                        }

                        .payroll-module-page .stat-value {
                            color: var(--text);
                            font-size: 1.45rem;
                            font-weight: 800;
                            letter-spacing: -0.03em;
                            line-height: 1.1;
                            word-break: break-word;
                        }

                        .payroll-module-page .stat-meta {
                            margin-top: 10px;
                            color: var(--text-faint);
                            font-size: 0.8rem;
                            font-weight: 600;
                        }

                        .payroll-module-page .panel-header {
                            display: flex;
                            justify-content: space-between;
                            align-items: flex-start;
                            gap: 12px;
                            margin-bottom: 16px;
                            flex-wrap: wrap;
                        }

                        .payroll-module-page .panel-title {
                            margin: 0;
                            font-size: 1.08rem;
                            font-weight: 800;
                            color: var(--text);
                            letter-spacing: -0.02em;
                        }

                        .payroll-module-page .panel-subtitle {
                            margin: 4px 0 0;
                            color: var(--text-soft);
                            font-size: 0.86rem;
                        }

                        .payroll-module-page .badge-soft {
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

                        .payroll-module-page .filter-grid,
                        .payroll-module-page .summary-grid {
                            display: grid;
                            gap: 14px;
                        }

                        .payroll-module-page .filter-grid {
                            grid-template-columns: repeat(4, minmax(0, 1fr));
                            align-items: end;
                        }

                        .payroll-module-page .summary-grid {
                            grid-template-columns: repeat(5, minmax(0, 1fr));
                        }

                        .payroll-module-page .form-label {
                            display: block;
                            margin-bottom: 8px;
                            color: var(--text-soft);
                            font-size: 0.78rem;
                            font-weight: 700;
                            letter-spacing: 0.06em;
                            text-transform: uppercase;
                        }

                        .payroll-module-page .form-control {
                            border-radius: 12px;
                            border: 1px solid var(--line-strong);
                            box-shadow: none;
                            min-height: 44px;
                            color: var(--text);
                            font-weight: 600;
                        }

                        .payroll-module-page .form-control:focus {
                            border-color: rgba(37, 99, 235, 0.55);
                            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.12);
                        }

                        .payroll-module-page .table-responsive {
                            overflow-x: auto;
                        }

                        .payroll-module-page .table {
                            width: 100%;
                            margin-bottom: 0;
                        }

                        .payroll-module-page .table thead th {
                            border-top: none;
                            color: var(--text-soft);
                            font-size: 0.74rem;
                            font-weight: 700;
                            text-transform: uppercase;
                            letter-spacing: 0.08em;
                            background: rgba(248, 251, 255, 0.9);
                            white-space: nowrap;
                        }

                        .payroll-module-page .table td,
                        .payroll-module-page .table th {
                            padding: 11px 12px;
                            border-top: 1px solid var(--line);
                            vertical-align: middle;
                        }

                        .payroll-module-page .table-cell-label {
                            color: var(--text);
                            font-weight: 700;
                        }

                        .payroll-module-page .table-cell-sub {
                            color: var(--text-soft);
                            font-size: 0.82rem;
                            font-weight: 600;
                        }

                        .payroll-module-page .num-cell {
                            font-family: var(--font-mono);
                            font-weight: 800;
                            color: var(--text);
                            white-space: nowrap;
                        }

                        .payroll-module-page .empty-state {
                            padding: 22px 18px;
                            border: 1px dashed var(--line-strong);
                            border-radius: 14px;
                            text-align: center;
                            color: var(--text-soft);
                            background: rgba(248, 251, 255, 0.8);
                            font-weight: 600;
                        }

                        .payroll-module-page .action-row {
                            display: flex;
                            gap: 8px;
                            flex-wrap: wrap;
                        }

                        .payroll-module-page .notice-card.notice-success {
                            border-color: rgba(5, 150, 105, 0.18);
                            background: linear-gradient(135deg, rgba(5, 150, 105, 0.08), rgba(16, 185, 129, 0.04));
                        }

                        .payroll-module-page .notice-card.notice-danger {
                            border-color: rgba(225, 29, 72, 0.18);
                            background: linear-gradient(135deg, rgba(225, 29, 72, 0.08), rgba(251, 113, 133, 0.04));
                        }

                        .payroll-module-page .notice-title {
                            font-size: 0.92rem;
                            font-weight: 800;
                            color: var(--text);
                            margin-bottom: 8px;
                        }

                        .payroll-module-page .theme-modal .modal-content {
                            border: 0;
                            border-radius: 18px;
                            overflow: hidden;
                        }

                        .payroll-module-page .theme-modal .modal-header {
                            background: linear-gradient(135deg, var(--primary), var(--primary-2));
                            color: #fff;
                            border-bottom: 0;
                        }

                        .payroll-module-page .theme-modal .modal-title {
                            font-weight: 800;
                        }

                        .payroll-module-page .theme-modal .close {
                            color: #fff;
                            text-shadow: none;
                            opacity: 0.9;
                        }

                        @media (max-width: 1399px) {
                            .payroll-module-page .stat-strip {
                                grid-template-columns: repeat(3, minmax(0, 1fr));
                            }

                            .payroll-module-page .summary-grid {
                                grid-template-columns: repeat(3, minmax(0, 1fr));
                            }
                        }

                        @media (max-width: 991px) {
                            .payroll-module-page .stat-strip,
                            .payroll-module-page .filter-grid,
                            .payroll-module-page .summary-grid {
                                grid-template-columns: 1fr;
                            }

                            .payroll-module-page .pm-header {
                                align-items: flex-start;
                            }
                        }
                    </style>

                    <div class="pm-header">
                        <div>
                            <div class="pm-eyebrow">Human Resource</div>
                            <h1 class="pm-title">Payroll Module</h1>
                            <div class="pm-subtitle">
                                Manage fixed monthly salaries, PhilHealth, SSS, Pag-IBIG deductions, staff loans, cash advances, payroll posting, deduction payables, and individual payslips for <?= htmlspecialchars($businessName); ?>.
                            </div>
                        </div>
                        <div class="pm-actions">
                            <a href="<?= base_url(); ?>Page/employeeList" class="btn-soft">
                                <i class="mdi mdi-account-group-outline"></i> Employee List
                            </a>
                            <button type="button" class="btn-success-soft" data-toggle="modal" data-target="#loanModal">
                                <i class="mdi mdi-bank-plus"></i> Add Loan
                            </button>
                            <button type="button" class="btn-warning-soft" data-toggle="modal" data-target="#cashAdvanceModal">
                                <i class="mdi mdi-cash-fast"></i> Cash Advance
                            </button>
                            <button type="button" class="btn-solid" data-toggle="modal" data-target="#generatePayrollModal">
                                <i class="mdi mdi-file-document-edit-outline"></i> Generate Payroll
                            </button>
                        </div>
                    </div>

                    <?php if ($notice !== ''): ?>
                        <div class="notice-card <?= $noticeType === 'danger' ? 'notice-danger' : 'notice-success'; ?>">
                            <div class="notice-body">
                                <div class="notice-title"><?= $noticeType === 'danger' ? 'Payroll Update Needs Attention' : 'Payroll Update Saved'; ?></div>
                                <div><?= htmlspecialchars($notice); ?></div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="stat-strip">
                        <div class="stat-card">
                            <div class="stat-label">Configured Employees</div>
                            <div class="stat-value"><?= number_format((int) ($summaryCards['configuredEmployeeCount'] ?? 0)); ?></div>
                            <div class="stat-meta">Employees with payroll defaults</div>
                        </div>
                        <div class="stat-card success">
                            <div class="stat-label">Monthly Salary Base</div>
                            <div class="stat-value">PHP <?= $money($summaryCards['totalMonthlySalary'] ?? 0); ?></div>
                            <div class="stat-meta">Current total fixed salaries</div>
                        </div>
                        <div class="stat-card warning">
                            <div class="stat-label">Loan Balance</div>
                            <div class="stat-value">PHP <?= $money($summaryCards['activeLoanBalance'] ?? 0); ?></div>
                            <div class="stat-meta">Outstanding staff loans</div>
                        </div>
                        <div class="stat-card warning">
                            <div class="stat-label">Cash Advances</div>
                            <div class="stat-value">PHP <?= $money($summaryCards['activeAdvanceBalance'] ?? 0); ?></div>
                            <div class="stat-meta">Unrecovered cash advances</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-label">Payroll Runs</div>
                            <div class="stat-value"><?= number_format((int) ($summaryCards['payrollRunCount'] ?? 0)); ?></div>
                            <div class="stat-meta">Posted payroll cycles</div>
                        </div>
                        <div class="stat-card success">
                            <div class="stat-label">Latest Net Payroll</div>
                            <div class="stat-value">PHP <?= $money($summaryCards['latestRunNet'] ?? 0); ?></div>
                            <div class="stat-meta">From the most recent run</div>
                        </div>
                    </div>

                    <div class="filter-card">
                        <div class="filter-body">
                            <form method="get" action="<?= base_url(); ?>Page/payrollModule">
                                <div class="filter-grid">
                                    <div>
                                        <label class="form-label" for="payroll-date-from">Summary From</label>
                                        <input type="date" class="form-control" id="payroll-date-from" name="date_from" value="<?= htmlspecialchars($filterDateFrom); ?>">
                                    </div>
                                    <div>
                                        <label class="form-label" for="payroll-date-to">Summary To</label>
                                        <input type="date" class="form-control" id="payroll-date-to" name="date_to" value="<?= htmlspecialchars($filterDateTo); ?>">
                                    </div>
                                    <div>
                                        <label class="form-label">Current Summary Range</label>
                                        <div class="badge-soft"><?= htmlspecialchars($rangeLabel); ?></div>
                                        <div class="mt-2" style="color: var(--text-faint); font-size: 0.82rem; font-weight: 600;">Generated <?= htmlspecialchars($generatedAt); ?></div>
                                    </div>
                                    <div>
                                        <label class="form-label">Actions</label>
                                        <div class="pm-actions">
                                            <button type="submit" class="btn-solid">
                                                <i class="mdi mdi-filter-outline"></i> Apply
                                            </button>
                                            <a href="<?= base_url(); ?>Page/payrollModule" class="btn-soft">
                                                <i class="mdi mdi-refresh"></i> Reset
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="panel-card">
                        <div class="panel-body">
                            <div class="panel-header">
                                <div>
                                    <h3 class="panel-title">Deduction Payables Summary</h3>
                                    <div class="panel-subtitle">Amounts accumulated from posted payroll entries in <?= htmlspecialchars($rangeLabel); ?></div>
                                </div>
                                <span class="badge-soft">PHP <?= $money($deductionSummary['totalDeductions'] ?? 0); ?> total deductions</span>
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
                                    <h3 class="panel-title">Payroll Runs</h3>
                                    <div class="panel-subtitle">Posted payroll cycles and their totals</div>
                                </div>
                            </div>
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Pay Date</th>
                                            <th>Coverage</th>
                                            <th>Employees</th>
                                            <th>Gross</th>
                                            <th>Deductions</th>
                                            <th>Net</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($payrollRuns)): ?>
                                            <tr>
                                                <td colspan="7">
                                                    <div class="empty-state">No payroll runs have been generated yet.</div>
                                                </td>
                                            </tr>
                                        <?php else: ?>
                                            <?php foreach ($payrollRuns as $run): ?>
                                                <tr>
                                                    <td class="table-cell-label"><?= htmlspecialchars($dateLabel($run->payDate ?? '')); ?></td>
                                                    <td><?= htmlspecialchars($dateLabel($run->periodStart ?? '')); ?> to <?= htmlspecialchars($dateLabel($run->periodEnd ?? '')); ?></td>
                                                    <td><?= number_format((int) ($run->employeeCount ?? 0)); ?></td>
                                                    <td class="num-cell">PHP <?= $money($run->totalGross ?? 0); ?></td>
                                                    <td class="num-cell">PHP <?= $money($run->totalDeductions ?? 0); ?></td>
                                                    <td class="num-cell">PHP <?= $money($run->totalNet ?? 0); ?></td>
                                                    <td>
                                                        <a href="<?= base_url(); ?>Page/payrollRun?id=<?= urlencode((string) ($run->payrollID ?? 0)); ?>" class="btn-soft">
                                                            <i class="mdi mdi-file-document-multiple-outline"></i> View Run
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
                                    <div class="panel-subtitle">Grouped breakdown for the filtered payroll range</div>
                                </div>
                            </div>
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Type</th>
                                            <th>Label</th>
                                            <th>Entries</th>
                                            <th>Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($deductionSummary['lines'])): ?>
                                            <tr>
                                                <td colspan="4">
                                                    <div class="empty-state">No deduction lines were posted in the selected range yet.</div>
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

                    <div class="panel-card">
                        <div class="panel-body">
                            <div class="panel-header">
                                <div>
                                    <h3 class="panel-title">Active Loans</h3>
                                    <div class="panel-subtitle">Outstanding employee loans that still deduct from payroll</div>
                                </div>
                            </div>
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Employee</th>
                                            <th>Loan Type</th>
                                            <th>Start Date</th>
                                            <th>Principal</th>
                                            <th>Balance</th>
                                            <th>Monthly Deduction</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($activeLoans)): ?>
                                            <tr>
                                                <td colspan="6">
                                                    <div class="empty-state">No active payroll loans are on record.</div>
                                                </td>
                                            </tr>
                                        <?php else: ?>
                                            <?php foreach ($activeLoans as $loan): ?>
                                                <tr>
                                                    <td>
                                                        <div class="table-cell-label"><?= htmlspecialchars((string) ($loan->employeeName ?? $loan->empID ?? '-')); ?></div>
                                                        <div class="table-cell-sub"><?= htmlspecialchars((string) ($loan->empID ?? '-')); ?></div>
                                                    </td>
                                                    <td><?= htmlspecialchars((string) ($loan->loanType ?? 'Loan')); ?></td>
                                                    <td><?= htmlspecialchars($dateLabel($loan->startDate ?? '')); ?></td>
                                                    <td class="num-cell">PHP <?= $money($loan->principalAmount ?? 0); ?></td>
                                                    <td class="num-cell">PHP <?= $money($loan->balanceAmount ?? 0); ?></td>
                                                    <td class="num-cell">PHP <?= $money($loan->monthlyDeduction ?? 0); ?></td>
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
                                    <h3 class="panel-title">Active Cash Advances</h3>
                                    <div class="panel-subtitle">Cash advances still scheduled for payroll deduction</div>
                                </div>
                            </div>
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Employee</th>
                                            <th>Date</th>
                                            <th>Reason</th>
                                            <th>Amount</th>
                                            <th>Balance</th>
                                            <th>Per Payroll</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($activeCashAdvances)): ?>
                                            <tr>
                                                <td colspan="6">
                                                    <div class="empty-state">No active cash advances are on record.</div>
                                                </td>
                                            </tr>
                                        <?php else: ?>
                                            <?php foreach ($activeCashAdvances as $advance): ?>
                                                <tr>
                                                    <td>
                                                        <div class="table-cell-label"><?= htmlspecialchars((string) ($advance->employeeName ?? $advance->empID ?? '-')); ?></div>
                                                        <div class="table-cell-sub"><?= htmlspecialchars((string) ($advance->empID ?? '-')); ?></div>
                                                    </td>
                                                    <td><?= htmlspecialchars($dateLabel($advance->advanceDate ?? '')); ?></td>
                                                    <td><?= htmlspecialchars((string) ($advance->reason ?? '-')); ?></td>
                                                    <td class="num-cell">PHP <?= $money($advance->amount ?? 0); ?></td>
                                                    <td class="num-cell">PHP <?= $money($advance->balanceAmount ?? 0); ?></td>
                                                    <td class="num-cell">PHP <?= $money($advance->deductionPerPayroll ?? 0); ?></td>
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

    <div class="modal fade theme-modal" id="loanModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Record Payroll Loan</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form method="post" action="<?= base_url(); ?>Page/addPayrollLoan">
                    <div class="modal-body">
                        <input type="hidden" name="return_to" value="Page/payrollModule">
                        <div class="form-row">
                            <div class="form-group col-md-4">
                                <label class="form-label" for="loan-empid">Employee</label>
                                <select class="form-control" name="empID" id="loan-empid" required>
                                    <option value="">Select employee</option>
                                    <?php foreach ($employees as $employee): ?>
                                        <option value="<?= htmlspecialchars((string) ($employee->empID ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
                                            <?= htmlspecialchars(trim((string) ($employee->employeeName ?? '') . ' [' . (string) ($employee->empID ?? '') . ']')); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group col-md-4">
                                <label class="form-label" for="loan-type">Loan Type</label>
                                <select class="form-control" name="loanType" id="loan-type" required>
                                    <option value="SSS Loan">SSS Loan</option>
                                    <option value="Pag-IBIG Loan">Pag-IBIG Loan</option>
                                    <option value="Other Loan">Other Loan</option>
                                </select>
                            </div>
                            <div class="form-group col-md-4">
                                <label class="form-label" for="loan-start-date">Start Date</label>
                                <input type="date" class="form-control" name="startDate" id="loan-start-date" value="<?= htmlspecialchars(date('Y-m-d')); ?>">
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-4">
                                <label class="form-label" for="loan-principal">Principal Amount</label>
                                <input type="number" step="0.01" min="0" class="form-control" name="principalAmount" id="loan-principal" required>
                            </div>
                            <div class="form-group col-md-4">
                                <label class="form-label" for="loan-monthly">Monthly Deduction</label>
                                <input type="number" step="0.01" min="0" class="form-control" name="monthlyDeduction" id="loan-monthly" required>
                            </div>
                            <div class="form-group col-md-4">
                                <label class="form-label" for="loan-end-date">Optional End Date</label>
                                <input type="date" class="form-control" name="endDate" id="loan-end-date">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="loan-notes">Notes</label>
                            <textarea class="form-control" name="notes" id="loan-notes" rows="2" placeholder="Loan remarks or memo"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn-solid">
                            <i class="mdi mdi-bank-plus"></i> Save Loan
                        </button>
                        <button type="button" class="btn-soft" data-dismiss="modal">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade theme-modal" id="cashAdvanceModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Record Cash Advance</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form method="post" action="<?= base_url(); ?>Page/addPayrollCashAdvance">
                    <div class="modal-body">
                        <input type="hidden" name="return_to" value="Page/payrollModule">
                        <div class="form-row">
                            <div class="form-group col-md-4">
                                <label class="form-label" for="advance-empid">Employee</label>
                                <select class="form-control" name="empID" id="advance-empid" required>
                                    <option value="">Select employee</option>
                                    <?php foreach ($employees as $employee): ?>
                                        <option value="<?= htmlspecialchars((string) ($employee->empID ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
                                            <?= htmlspecialchars(trim((string) ($employee->employeeName ?? '') . ' [' . (string) ($employee->empID ?? '') . ']')); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group col-md-4">
                                <label class="form-label" for="advance-date">Advance Date</label>
                                <input type="date" class="form-control" name="advanceDate" id="advance-date" value="<?= htmlspecialchars(date('Y-m-d')); ?>" required>
                            </div>
                            <div class="form-group col-md-4">
                                <label class="form-label" for="advance-amount">Amount</label>
                                <input type="number" step="0.01" min="0" class="form-control" name="amount" id="advance-amount" required>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-4">
                                <label class="form-label" for="advance-deduction">Deduction Per Payroll</label>
                                <input type="number" step="0.01" min="0" class="form-control" name="deductionPerPayroll" id="advance-deduction" required>
                            </div>
                            <div class="form-group col-md-8">
                                <label class="form-label" for="advance-reason">Reason</label>
                                <input type="text" class="form-control" name="reason" id="advance-reason" placeholder="Emergency cash advance, salary advance, etc.">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn-solid">
                            <i class="mdi mdi-cash-fast"></i> Save Cash Advance
                        </button>
                        <button type="button" class="btn-soft" data-dismiss="modal">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade theme-modal" id="generatePayrollModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Generate Payroll Run</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form method="post" action="<?= base_url(); ?>Page/generatePayroll">
                    <div class="modal-body">
                        <div class="form-row">
                            <div class="form-group col-md-4">
                                <label class="form-label" for="period-start">Period Start</label>
                                <input type="date" class="form-control" name="periodStart" id="period-start" value="<?= htmlspecialchars(date('Y-m-01')); ?>" required>
                            </div>
                            <div class="form-group col-md-4">
                                <label class="form-label" for="period-end">Period End</label>
                                <input type="date" class="form-control" name="periodEnd" id="period-end" value="<?= htmlspecialchars(date('Y-m-t')); ?>" required>
                            </div>
                            <div class="form-group col-md-4">
                                <label class="form-label" for="pay-date">Pay Date</label>
                                <input type="date" class="form-control" name="payDate" id="pay-date" value="<?= htmlspecialchars(date('Y-m-d')); ?>" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="payroll-notes">Notes</label>
                            <textarea class="form-control" name="notes" id="payroll-notes" rows="2" placeholder="Optional remarks for this payroll cycle"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn-solid">
                            <i class="mdi mdi-file-document-edit-outline"></i> Generate Payroll
                        </button>
                        <button type="button" class="btn-soft" data-dismiss="modal">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="<?= base_url(); ?>assets/js/vendor.min.js"></script>
    <script src="<?= base_url(); ?>assets/js/app.min.js"></script>
    <script>
        (function($) {
            'use strict';

            $(function() {
                $('#loanModal').on('show.bs.modal', function(event) {
                    var button = $(event.relatedTarget);
                    if (!button.length) {
                        return;
                    }

                    $('#loan-empid').val(button.data('empid') || '');
                });

                $('#cashAdvanceModal').on('show.bs.modal', function(event) {
                    var button = $(event.relatedTarget);
                    if (!button.length) {
                        return;
                    }

                    $('#advance-empid').val(button.data('empid') || '');
                });
            });
        })(jQuery);
    </script>

</body>

</html>
