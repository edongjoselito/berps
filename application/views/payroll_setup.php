<?php
$businessData = isset($business) ? $business : null;
$employees = isset($employees) && is_array($employees) ? $employees : array();
$activeLoans = isset($activeLoans) && is_array($activeLoans) ? $activeLoans : array();
$activeCashAdvances = isset($activeCashAdvances) && is_array($activeCashAdvances) ? $activeCashAdvances : array();
$notice = isset($notice) ? trim((string) $notice) : '';
$noticeType = isset($noticeType) ? trim((string) $noticeType) : 'success';

$businessName = trim((string) ($businessData->CompName ?? 'BERPS'));

$money = function ($value) {
    return number_format((float) $value, 2);
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
                <div class="container-fluid payroll-setup-page">

                    <style>

                        .payroll-setup-page {
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
                            --font-body: var(--font-primary);
                            --font-mono: var(--font-primary);
                            font-family: var(--font-primary, Montserrat, Segoe UI, Arial, sans-serif);
                            background:
                                radial-gradient(circle at top left, rgba(37, 99, 235, 0.08), transparent 28%),
                                radial-gradient(circle at top right, rgba(16, 185, 129, 0.08), transparent 24%),
                                linear-gradient(180deg, #f8fbff 0%, #f4f7fb 100%);
                            min-height: 100vh;
                            padding-bottom: 100px;
                        }

                        .payroll-setup-page * { box-sizing: border-box; }

                        .ps-header {
                            margin: 24px 0 22px;
                            display: flex;
                            justify-content: space-between;
                            align-items: flex-end;
                            gap: 16px;
                            flex-wrap: wrap;
                        }

                        .ps-eyebrow {
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

                        .ps-eyebrow::before {
                            content: '';
                            width: 8px;
                            height: 8px;
                            border-radius: 50%;
                            background: linear-gradient(135deg, var(--primary), var(--primary-2));
                            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.10);
                        }

                        .ps-title {
                            margin: 0;
                            font-size: 2rem;
                            font-weight: 800;
                            letter-spacing: -0.04em;
                            color: var(--text);
                            line-height: 1.08;
                        }

                        .ps-subtitle {
                            margin-top: 8px;
                            color: var(--text-soft);
                            font-size: 0.93rem;
                            font-weight: 500;
                            max-width: 760px;
                        }

                        .ps-actions {
                            display: flex;
                            gap: 10px;
                            flex-wrap: wrap;
                            justify-content: flex-end;
                        }

                        .btn-soft, .btn-solid, .btn-success-soft, .btn-warning-soft {
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

                        .btn-soft {
                            background: rgba(255, 255, 255, 0.88);
                            color: var(--text);
                            border-color: var(--line-strong);
                            box-shadow: var(--shadow-soft);
                        }

                        .btn-solid {
                            background: linear-gradient(135deg, var(--primary), var(--primary-2));
                            color: #fff;
                            box-shadow: 0 10px 24px rgba(37, 99, 235, 0.22);
                        }

                        .btn-success-soft {
                            background: rgba(5, 150, 105, 0.1);
                            color: var(--success);
                        }

                        .btn-warning-soft {
                            background: rgba(217, 119, 6, 0.1);
                            color: var(--warning);
                        }

                        .btn-soft:hover, .btn-solid:hover, .btn-success-soft:hover, .btn-warning-soft:hover {
                            transform: translateY(-1px);
                            filter: brightness(1.02);
                            text-decoration: none;
                        }

                        .panel-card {
                            background: var(--surface);
                            border: 1px solid rgba(255, 255, 255, 0.72);
                            border-radius: var(--radius-xl);
                            box-shadow: var(--shadow-soft);
                            overflow: hidden;
                            margin-bottom: 18px;
                        }

                        .panel-body { padding: 20px 22px; }

                        .panel-header {
                            display: flex;
                            justify-content: space-between;
                            align-items: flex-start;
                            gap: 12px;
                            margin-bottom: 16px;
                            flex-wrap: wrap;
                        }

                        .panel-title {
                            margin: 0;
                            font-size: 1.08rem;
                            font-weight: 800;
                            color: var(--text);
                            letter-spacing: -0.02em;
                        }

                        .panel-subtitle {
                            margin: 4px 0 0;
                            color: var(--text-soft);
                            font-size: 0.86rem;
                        }

                        .table-responsive { overflow-x: auto; }

                        .table {
                            width: 100%;
                            margin-bottom: 0;
                        }

                        .table thead th {
                            border-top: none;
                            color: var(--text-soft);
                            font-size: 0.74rem;
                            font-weight: 700;
                            text-transform: uppercase;
                            letter-spacing: 0.08em;
                            background: rgba(248, 251, 255, 0.9);
                            white-space: nowrap;
                        }

                        .table td, .table th {
                            padding: 11px 12px;
                            border-top: 1px solid var(--line);
                            vertical-align: middle;
                        }

                        .table-cell-label {
                            color: var(--text);
                            font-weight: 700;
                        }

                        .table-cell-sub {
                            color: var(--text-soft);
                            font-size: 0.82rem;
                            font-weight: 600;
                        }

                        .num-cell {
                            font-family: var(--font-primary, Montserrat, Segoe UI, Arial, sans-serif);
                            font-weight: 800;
                            color: var(--text);
                            white-space: nowrap;
                        }

                        .empty-state {
                            padding: 22px 18px;
                            border: 1px dashed var(--line-strong);
                            border-radius: 14px;
                            text-align: center;
                            color: var(--text-soft);
                            background: rgba(248, 251, 255, 0.8);
                            font-weight: 600;
                        }

                        .action-row {
                            display: flex;
                            gap: 8px;
                            flex-wrap: wrap;
                        }

                        .notice-card.notice-success {
                            border-color: rgba(5, 150, 105, 0.18);
                            background: linear-gradient(135deg, rgba(5, 150, 105, 0.08), rgba(16, 185, 129, 0.04));
                        }

                        .notice-card.notice-danger {
                            border-color: rgba(225, 29, 72, 0.18);
                            background: linear-gradient(135deg, rgba(225, 29, 72, 0.08), rgba(251, 113, 133, 0.04));
                        }

                        .notice-title {
                            font-size: 0.92rem;
                            font-weight: 800;
                            color: var(--text);
                            margin-bottom: 8px;
                        }

                        .theme-modal .modal-content {
                            border: 0;
                            border-radius: 18px;
                            overflow: hidden;
                        }

                        .theme-modal .modal-header {
                            background: linear-gradient(135deg, var(--primary), var(--primary-2));
                            color: #fff;
                            border-bottom: 0;
                        }

                        .theme-modal .modal-title { font-weight: 800; }

                        .theme-modal .close {
                            color: #fff;
                            text-shadow: none;
                            opacity: 0.9;
                        }

                        .form-label {
                            display: block;
                            margin-bottom: 8px;
                            color: var(--text-soft);
                            font-size: 0.78rem;
                            font-weight: 700;
                            letter-spacing: 0.06em;
                            text-transform: uppercase;
                        }

                        .form-control {
                            border-radius: 12px;
                            border: 1px solid var(--line-strong);
                            box-shadow: none;
                            min-height: 44px;
                            color: var(--text);
                            font-weight: 600;
                        }

                        .form-control:focus {
                            border-color: rgba(37, 99, 235, 0.55);
                            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.12);
                        }

                        @media (max-width: 991px) {
                            .ps-header { align-items: flex-start; }
                        }

                        @media (max-width: 768px) {
                            .action-row {
                                flex-direction: column !important;
                                gap: 6px;
                                width: 100% !important;
                                display: flex !important;
                                flex-wrap: nowrap !important;
                            }
                            
                            .action-row button,
                            .table .action-row button,
                            .dataTables_wrapper .action-row button {
                                width: 100% !important;
                                justify-content: center;
                                font-size: 0.85rem;
                                padding: 8px 12px;
                                min-width: 100% !important;
                                flex: 1 1 auto;
                                display: inline-flex !important;
                            }
                            
                            .table td {
                                vertical-align: top !important;
                            }
                        }

                        @media (max-width: 576px) {
                            .action-row button {
                                font-size: 0.8rem;
                                padding: 7px 10px;
                            }
                        }

                        /* DataTables styling */
                        .payroll-setup-page .dataTables_wrapper {
                            margin-bottom: 16px;
                            padding-bottom: 16px;
                        }

                        .payroll-setup-page .dataTables_wrapper .row:last-child {
                            margin-bottom: 12px;
                        }

                        .payroll-setup-page .dataTables_filter input {
                            border-radius: 10px;
                            border: 1px solid var(--line-strong);
                            padding: 6px 12px;
                            font-size: 0.85rem;
                        }

                        .payroll-setup-page .dataTables_length select {
                            border-radius: 10px;
                            border: 1px solid var(--line-strong);
                            padding: 6px 10px;
                            font-size: 0.85rem;
                        }

                        .payroll-setup-page .page-item.active .page-link {
                            background: var(--primary);
                            border-color: var(--primary);
                        }
                    </style>

                    <div class="ps-header">
                        <div>
                            <div class="ps-eyebrow">Payroll Configuration</div>
                            <h1 class="ps-title">Employee Payroll Setup</h1>
                           
                        </div>
                        <div class="ps-actions">
                            <a href="<?= base_url(); ?>Page/payrollModule" class="btn-soft">
                                <i class="mdi mdi-arrow-left"></i> Back to Payroll
                            </a>
                            <button type="button" class="btn-success-soft" data-toggle="modal" data-target="#loanModal">
                                <i class="mdi mdi-bank-plus"></i> Add Loan
                            </button>
                            <button type="button" class="btn-warning-soft" data-toggle="modal" data-target="#cashAdvanceModal">
                                <i class="mdi mdi-cash-fast"></i> Cash Advance
                            </button>
                        </div>
                    </div>

                    <?php if ($notice !== ''): ?>
                        <div class="panel-card notice-card <?= $noticeType === 'danger' ? 'notice-danger' : 'notice-success'; ?>" style="margin-bottom: 18px;">
                            <div class="panel-body">
                                <div class="notice-title"><?= $noticeType === 'danger' ? 'Update Failed' : 'Update Saved'; ?></div>
                                <div><?= htmlspecialchars($notice); ?></div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="panel-card">
                        <div class="panel-body">
                            <div class="panel-header">
                                <div>
                                    <h3 class="panel-title">Employee Payroll Setup</h3>
                                    <div class="panel-subtitle">Fixed monthly salary and standard deduction defaults per employee</div>
                                </div>
                            </div>
                            <div class="table-responsive data-table-container">
                                <table id="payroll-setup-table" class="table table-hover table-centered mb-0">
                                    <thead>
                                        <tr>
                                            <th>Employee</th>
                                            <th>Department</th>
                                            <th>Salary</th>
                                            <th>PhilHealth</th>
                                            <th>SSS</th>
                                            <th>Pag-IBIG</th>
                                            <th>Loan Balance</th>
                                            <th>Advance Balance</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($employees)): ?>
                                            <tr>
                                                <td colspan="9">
                                                    <div class="empty-state">No active employees were found yet.</div>
                                                </td>
                                            </tr>
                                        <?php else: ?>
                                            <?php foreach ($employees as $employee): ?>
                                                <?php
                                                $employeeName = trim((string) ($employee->employeeName ?? ''));
                                                $salaryRaw = number_format((float) ($employee->monthlySalary ?? 0), 2, '.', '');
                                                $philhealthRaw = number_format((float) ($employee->philhealthAmount ?? 0), 2, '.', '');
                                                $sssRaw = number_format((float) ($employee->sssAmount ?? 0), 2, '.', '');
                                                $pagibigRaw = number_format((float) ($employee->pagibigAmount ?? 0), 2, '.', '');
                                                ?>
                                                <tr>
                                                    <td>
                                                        <div class="table-cell-label"><?= htmlspecialchars($employeeName !== '' ? $employeeName : (string) ($employee->empID ?? '-')); ?></div>
                                                        <div class="table-cell-sub"><?= htmlspecialchars((string) ($employee->empID ?? '-')); ?> · <?= htmlspecialchars((string) ($employee->empPosition ?? 'Staff')); ?></div>
                                                    </td>
                                                    <td><?= htmlspecialchars((string) ($employee->department ?? '-')); ?></td>
                                                    <td class="num-cell">PHP <?= $money($employee->monthlySalary ?? 0); ?></td>
                                                    <td class="num-cell">PHP <?= $money($employee->philhealthAmount ?? 0); ?></td>
                                                    <td class="num-cell">PHP <?= $money($employee->sssAmount ?? 0); ?></td>
                                                    <td class="num-cell">PHP <?= $money($employee->pagibigAmount ?? 0); ?></td>
                                                    <td class="num-cell">PHP <?= $money($employee->loanBalance ?? 0); ?></td>
                                                    <td class="num-cell">PHP <?= $money($employee->advanceBalance ?? 0); ?></td>
                                                    <td>
                                                        <div class="action-row">
                                                            <button
                                                                type="button"
                                                                class="btn-soft btn-payroll-profile"
                                                                data-toggle="modal"
                                                                data-target="#profileModal"
                                                                data-empid="<?= htmlspecialchars((string) ($employee->empID ?? ''), ENT_QUOTES, 'UTF-8'); ?>"
                                                                data-employee="<?= htmlspecialchars($employeeName, ENT_QUOTES, 'UTF-8'); ?>"
                                                                data-salary="<?= htmlspecialchars($salaryRaw, ENT_QUOTES, 'UTF-8'); ?>"
                                                                data-philhealth="<?= htmlspecialchars($philhealthRaw, ENT_QUOTES, 'UTF-8'); ?>"
                                                                data-sss="<?= htmlspecialchars($sssRaw, ENT_QUOTES, 'UTF-8'); ?>"
                                                                data-pagibig="<?= htmlspecialchars($pagibigRaw, ENT_QUOTES, 'UTF-8'); ?>"
                                                                data-notes="<?= htmlspecialchars((string) ($employee->payrollNotes ?? ''), ENT_QUOTES, 'UTF-8'); ?>"
                                                                data-status="<?= htmlspecialchars((string) (($employee->payrollStatus ?? '') !== '' ? $employee->payrollStatus : 'active'), ENT_QUOTES, 'UTF-8'); ?>">
                                                                <i class="mdi mdi-cog-outline"></i> Setup
                                                            </button>
                                                            <button
                                                                type="button"
                                                                class="btn-success-soft btn-payroll-loan"
                                                                data-toggle="modal"
                                                                data-target="#loanModal"
                                                                data-empid="<?= htmlspecialchars((string) ($employee->empID ?? ''), ENT_QUOTES, 'UTF-8'); ?>"
                                                                data-employee="<?= htmlspecialchars($employeeName, ENT_QUOTES, 'UTF-8'); ?>">
                                                                <i class="mdi mdi-bank-transfer"></i> Loan
                                                            </button>
                                                            <button
                                                                type="button"
                                                                class="btn-warning-soft btn-payroll-advance"
                                                                data-toggle="modal"
                                                                data-target="#cashAdvanceModal"
                                                                data-empid="<?= htmlspecialchars((string) ($employee->empID ?? ''), ENT_QUOTES, 'UTF-8'); ?>"
                                                                data-employee="<?= htmlspecialchars($employeeName, ENT_QUOTES, 'UTF-8'); ?>">
                                                                <i class="mdi mdi-cash-fast"></i> Advance
                                                            </button>
                                                        </div>
                                                    </td>
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

    <div class="modal fade theme-modal" id="profileModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Payroll Profile</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form method="post" action="<?= base_url(); ?>Page/savePayrollProfile">
                    <input type="hidden" name="return_to" value="Page/payrollSetup">
                    <div class="modal-body">
                        <div class="form-row">
                            <div class="form-group col-md-4">
                                <label class="form-label">Employee</label>
                                <div class="form-control" style="background: #f8f9fa; color: #617489; font-weight: 600;" id="profile-employee-display">
                                    <span id="profile-employee-name">-</span>
                                </div>
                                <input type="hidden" name="empID" id="profile-empid">
                            </div>
                            <div class="form-group col-md-4">
                                <label class="form-label" for="profile-salary">Monthly Salary</label>
                                <input type="number" step="0.01" min="0" class="form-control" name="monthlySalary" id="profile-salary" required>
                            </div>
                            <div class="form-group col-md-4">
                                <label class="form-label" for="profile-status">Profile Status</label>
                                <select class="form-control" name="payrollStatus" id="profile-status">
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-4">
                                <label class="form-label" for="profile-philhealth">PhilHealth</label>
                                <input type="number" step="0.01" min="0" class="form-control" name="philhealthAmount" id="profile-philhealth">
                            </div>
                            <div class="form-group col-md-4">
                                <label class="form-label" for="profile-sss">SSS</label>
                                <input type="number" step="0.01" min="0" class="form-control" name="sssAmount" id="profile-sss">
                            </div>
                            <div class="form-group col-md-4">
                                <label class="form-label" for="profile-pagibig">Pag-IBIG</label>
                                <input type="number" step="0.01" min="0" class="form-control" name="pagibigAmount" id="profile-pagibig">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="profile-notes">Notes</label>
                            <textarea class="form-control" name="payrollNotes" id="profile-notes" rows="2" ></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-solid" style="background: linear-gradient(135deg, #2563eb, #1d4ed8); color: #fff; border: 0; padding: 10px 20px; border-radius: 10px; font-weight: 600;">
                            <i class="mdi mdi-content-save-outline"></i> Save Payroll Profile
                        </button>
                        <button type="button" class="btn btn-soft" data-dismiss="modal" style="background: rgba(255,255,255,0.88); border: 1px solid #cfdbea; padding: 10px 20px; border-radius: 10px; font-weight: 600;">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

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
                    <input type="hidden" name="return_to" value="Page/payrollSetup">
                    <div class="modal-body">
                        <div class="form-row">
                            <div class="form-group col-md-4">
                                <label class="form-label">Employee</label>
                                <div class="form-control" style="background: #f8f9fa; color: #617489; font-weight: 600;" id="loan-employee-display">
                                    <span id="loan-employee-name">-</span>
                                </div>
                                <input type="hidden" name="empID" id="loan-empid">
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
                            <textarea class="form-control" name="notes" id="loan-notes" rows="2" ></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-solid" style="background: linear-gradient(135deg, #2563eb, #1d4ed8); color: #fff; border: 0; padding: 10px 20px; border-radius: 10px; font-weight: 600;">
                            <i class="mdi mdi-bank-plus"></i> Save Loan
                        </button>
                        <button type="button" class="btn btn-soft" data-dismiss="modal" style="background: rgba(255,255,255,0.88); border: 1px solid #cfdbea; padding: 10px 20px; border-radius: 10px; font-weight: 600;">Cancel</button>
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
                    <input type="hidden" name="return_to" value="Page/payrollSetup">
                    <div class="modal-body">
                        <div class="form-row">
                            <div class="form-group col-md-4">
                                <label class="form-label">Employee</label>
                                <div class="form-control" style="background: #f8f9fa; color: #617489; font-weight: 600;" id="advance-employee-display">
                                    <span id="advance-employee-name">-</span>
                                </div>
                                <input type="hidden" name="empID" id="advance-empid">
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
                                <input type="text" class="form-control" name="reason" id="advance-reason" >
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-solid" style="background: linear-gradient(135deg, #2563eb, #1d4ed8); color: #fff; border: 0; padding: 10px 20px; border-radius: 10px; font-weight: 600;">
                            <i class="mdi mdi-cash-fast"></i> Save Cash Advance
                        </button>
                        <button type="button" class="btn btn-soft" data-dismiss="modal" style="background: rgba(255,255,255,0.88); border: 1px solid #cfdbea; padding: 10px 20px; border-radius: 10px; font-weight: 600;">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="<?= base_url(); ?>assets/js/vendor.min.js"></script>
    <script src="<?= base_url(); ?>assets/libs/select2/select2.min.js"></script>
    <script src="<?= base_url(); ?>assets/js/app.min.js"></script>
    <script src="<?= base_url(); ?>assets/libs/jquery-ui/jquery-ui.min.js"></script>
    <script src="<?= base_url(); ?>assets/libs/datatables/jquery.dataTables.min.js"></script>
    <script src="<?= base_url(); ?>assets/libs/datatables/dataTables.bootstrap4.min.js"></script>
    <script src="<?= base_url(); ?>assets/libs/datatables/dataTables.buttons.min.js"></script>
    <script src="<?= base_url(); ?>assets/libs/datatables/buttons.bootstrap4.min.js"></script>
    <script src="<?= base_url(); ?>assets/libs/datatables/dataTables.responsive.min.js"></script>
    <script src="<?= base_url(); ?>assets/libs/datatables/responsive.bootstrap4.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize DataTable
            var $table = $('#payroll-setup-table');
            if ($table.length) {
                $table.DataTable({
                    responsive: true,
                    autoWidth: false,
                    order: [[0, 'asc']],
                    pageLength: 25,
                    language: {
                        search: 'Search employees:',
                        lengthMenu: 'Show _MENU_ per page',
                        info: 'Showing _START_ to _END_ of _TOTAL_ employees',
                        infoEmpty: 'No employees found',
                        zeroRecords: 'No matching employees found'
                    },
                    dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>rtip',
                    columnDefs: [
                        { orderable: false, targets: [8] },
                        { responsivePriority: 1, targets: [0, 8] },
                        { responsivePriority: 2, targets: [2] }
                    ]
                });
            }

            // Profile modal population
            document.querySelectorAll('.btn-payroll-profile').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    document.getElementById('profile-empid').value = this.dataset.empid || '';
                    document.getElementById('profile-employee-name').textContent = this.dataset.employee || '';
                    document.getElementById('profile-salary').value = this.dataset.salary || '';
                    document.getElementById('profile-philhealth').value = this.dataset.philhealth || '';
                    document.getElementById('profile-sss').value = this.dataset.sss || '';
                    document.getElementById('profile-pagibig').value = this.dataset.pagibig || '';
                    document.getElementById('profile-notes').value = this.dataset.notes || '';
                    document.getElementById('profile-status').value = this.dataset.status || 'active';
                });
            });

            // Loan modal population
            document.querySelectorAll('.btn-payroll-loan').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    document.getElementById('loan-empid').value = this.dataset.empid || '';
                    document.getElementById('loan-employee-name').textContent = this.dataset.employee || '';
                });
            });

            // Cash advance modal population
            document.querySelectorAll('.btn-payroll-advance').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    document.getElementById('advance-empid').value = this.dataset.empid || '';
                    document.getElementById('advance-employee-name').textContent = this.dataset.employee || '';
                });
            });
        });
    </script>

</body>

</html>
