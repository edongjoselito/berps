<?php
$businessData = isset($business) ? $business : null;
$payrollRuns = isset($payrollRuns) && is_array($payrollRuns) ? $payrollRuns : array();
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
                <div class="container-fluid payroll-runs-page">

                    <?php if ($notice !== ''): ?>
                        <div class="panel-card notice-card <?= $noticeType === 'danger' ? 'notice-danger' : 'notice-success'; ?>" style="margin-bottom: 18px;">
                            <div class="panel-body">
                                <div class="notice-title"><?= $noticeType === 'danger' ? 'Error' : 'Success'; ?></div>
                                <div><?= htmlspecialchars($notice); ?></div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <style>
                        @import url('https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,100..1000;1,9..40,100..1000&display=swap');

                        .payroll-runs-page {
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

                        .payroll-runs-page * { box-sizing: border-box; }

                        .pr-header {
                            margin: 24px 0 22px;
                            display: flex;
                            justify-content: space-between;
                            align-items: flex-end;
                            gap: 16px;
                            flex-wrap: wrap;
                        }

                        .pr-eyebrow {
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

                        .pr-eyebrow::before {
                            content: '';
                            width: 8px;
                            height: 8px;
                            border-radius: 50%;
                            background: linear-gradient(135deg, var(--primary), var(--primary-2));
                            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.10);
                        }

                        .pr-title {
                            margin: 0;
                            font-size: 2rem;
                            font-weight: 800;
                            letter-spacing: -0.04em;
                            color: var(--text);
                            line-height: 1.08;
                        }

                        .pr-subtitle {
                            margin-top: 8px;
                            color: var(--text-soft);
                            font-size: 0.93rem;
                            font-weight: 500;
                            max-width: 760px;
                        }

                        .pr-actions {
                            display: flex;
                            gap: 10px;
                            flex-wrap: wrap;
                            justify-content: flex-end;
                        }

                        .btn-soft, .btn-solid {
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

                        .btn-soft:hover, .btn-solid:hover {
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
                            font-family: var(--font-mono);
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

                        .month-badge {
                            display: inline-flex;
                            align-items: center;
                            gap: 6px;
                            padding: 6px 14px;
                            border-radius: 20px;
                            background: var(--primary-soft);
                            color: var(--primary-2);
                            font-weight: 800;
                            font-size: 0.9rem;
                        }

                        .status-posted {
                            background: rgba(5, 150, 105, 0.1);
                            color: var(--success);
                            padding: 4px 8px;
                            border-radius: 6px;
                            font-size: 0.75rem;
                            font-weight: 700;
                            text-transform: uppercase;
                            letter-spacing: 0.06em;
                        }

                        .status-draft {
                            background: rgba(217, 119, 6, 0.1);
                            color: var(--warning);
                            padding: 4px 8px;
                            border-radius: 6px;
                            font-size: 0.75rem;
                            font-weight: 700;
                            text-transform: uppercase;
                            letter-spacing: 0.06em;
                        }

                        @media (max-width: 991px) {
                            .pr-header { align-items: flex-start; }
                        }

                        /* DataTables styling */
                        .payroll-runs-page .dataTables_wrapper {
                            margin-bottom: 16px;
                            padding-bottom: 16px;
                        }

                        .payroll-runs-page .dataTables_wrapper .row:last-child {
                            margin-bottom: 12px;
                        }

                        .payroll-runs-page .dataTables_filter input {
                            border-radius: 10px;
                            border: 1px solid var(--line-strong);
                            padding: 6px 12px;
                            font-size: 0.85rem;
                        }

                        .payroll-runs-page .dataTables_length select {
                            border-radius: 10px;
                            border: 1px solid var(--line-strong);
                            padding: 6px 10px;
                            font-size: 0.85rem;
                        }

                        .payroll-runs-page .page-item.active .page-link {
                            background: var(--primary);
                            border-color: var(--primary);
                        }
                    </style>

                    <div class="pr-header">
                        <div>
                            <div class="pr-eyebrow">Payroll Management</div>
                            <h1 class="pr-title">Payroll Runs</h1>
                            <div class="pr-subtitle">
                                View all generated payroll runs and their details for <?= htmlspecialchars($businessName); ?>.
                            </div>
                        </div>
                        <div class="pr-actions">
                            <a href="<?= base_url(); ?>Page/payrollModule" class="btn-soft">
                                <i class="mdi mdi-arrow-left"></i> Back to Payroll
                            </a>
                        </div>
                    </div>

                    <div class="panel-card">
                        <div class="panel-body">
                            <div class="panel-header">
                                <div>
                                    <h3 class="panel-title">Payroll Run History</h3>
                                    <div class="panel-subtitle">All generated payroll runs with employee counts and totals</div>
                                </div>
                            </div>
                            <div class="table-responsive data-table-container">
                                <table id="payroll-runs-table" class="table table-hover table-centered mb-0">
                                    <thead>
                                        <tr>
                                            <th>Payroll Period</th>
                                            <th>Pay Date</th>
                                            <th>Employees</th>
                                            <th>Gross Pay</th>
                                            <th>Net Pay</th>
                                            <th>Deductions</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($payrollRuns)): ?>
                                            <tr>
                                                <td colspan="8">
                                                    <div class="empty-state">No payroll runs have been generated yet.</div>
                                                </td>
                                            </tr>
                                        <?php else: ?>
                                            <?php foreach ($payrollRuns as $run): ?>
                                                <tr>
                                                    <td>
                                                        <div class="table-cell-label"><?= $dateLabel($run->periodStart); ?></div>
                                                        <div class="table-cell-sub">to <?= $dateLabel($run->periodEnd); ?></div>
                                                    </td>
                                                    <td><?= $dateLabel($run->payDate); ?></td>
                                                    <td class="num-cell"><?= number_format((int) ($run->employeeCount ?? 0)); ?></td>
                                                    <td class="num-cell">PHP <?= $money($run->totalGross ?? 0); ?></td>
                                                    <td class="num-cell">PHP <?= $money($run->totalNet ?? 0); ?></td>
                                                    <td class="num-cell">PHP <?= $money($run->totalDeductions ?? 0); ?></td>
                                                    <td>
                                                        <span class="<?= ($run->status ?? 'draft') === 'posted' ? 'status-posted' : 'status-draft'; ?>">
                                                            <?= htmlspecialchars(ucfirst((string) ($run->status ?? 'draft'))); ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <div class="action-row">
                                                            <a href="<?= base_url(); ?>Page/payrollRun?id=<?= htmlspecialchars((string) ($run->payrollID ?? '')); ?>" 
                                                               class="btn-soft" title="View Details">
                                                                <i class="mdi mdi-eye"></i> View
                                                            </a>
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

    <script src="<?= base_url(); ?>assets/js/vendor.min.js"></script>
    <script src="<?= base_url(); ?>assets/js/app.min.js"></script>
    <script src="<?= base_url(); ?>assets/libs/datatables/jquery.dataTables.min.js"></script>
    <script src="<?= base_url(); ?>assets/libs/datatables/dataTables.bootstrap4.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize DataTable
            var $table = $('#payroll-runs-table');
            if ($table.length) {
                $table.DataTable({
                    responsive: true,
                    autoWidth: false,
                    order: [[0, 'desc']],
                    pageLength: 25,
                    language: {
                        search: 'Search payroll runs:',
                        lengthMenu: 'Show _MENU_ per page',
                        info: 'Showing _START_ to _END_ of _TOTAL_ payroll runs',
                        infoEmpty: 'No payroll runs found',
                        zeroRecords: 'No matching payroll runs found'
                    },
                    dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>rtip',
                    columnDefs: [
                        { orderable: false, targets: [7] },
                        { responsivePriority: 1, targets: [0, 7] },
                        { responsivePriority: 2, targets: [2, 3] }
                    ]
                });
            }
        });
    </script>

</body>

</html>
