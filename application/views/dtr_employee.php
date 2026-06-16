<!DOCTYPE html>
<html lang="en">

<?php include('includes/head.php'); ?>

<body>
    <div id="wrapper">

        <?php include('includes/top-nav-bar.php'); ?>
        <?php include('includes/sidebar.php'); ?>

        <div class="content-page">
            <div class="content">
                <div class="container-fluid dtr-page">

                    <style>
                        :root {
                            --fixed-footer-h: 90px;
                        }

                        .dtr-page {
                            padding-bottom: calc(var(--fixed-footer-h) + 32px);
                            padding-top: 18px;
                        }

                        .dtr-page .page-title-box {
                            margin-bottom: .75rem;
                            padding-bottom: .25rem;
                        }

                        .dtr-page .page-title-box hr {
                            margin: 10px 0 6px;
                        }

                        /* Card look */
                        .dtr-card {
                            border: 0;
                            border-radius: 12px;
                            box-shadow: 0 8px 20px rgba(0, 0, 0, .06);
                            overflow: hidden;
                        }

                        .dtr-card .card-header {
                            background: #f8f9fb;
                            display: flex;
                            align-items: center;
                            justify-content: space-between;
                            gap: 12px;
                        }

                        .dtr-title {
                            margin: 0;
                            font-weight: 700;
                        }

                        .filters-wrap {
                            background: #fff;
                            border: 1px solid rgba(0, 0, 0, .06);
                            border-radius: 12px;
                            padding: 14px;
                            margin-bottom: 14px;
                        }

                        /* Print rules */
                        @media print {
                            body * {
                                visibility: hidden !important;
                            }

                            #print-area,
                            #print-area * {
                                visibility: visible !important;
                            }

                            /* hide buttons/filter during print */
                            #noPrint {
                                display: none !important;
                            }

                            /* layout */
                            #print-area {
                                position: absolute;
                                left: 0;
                                top: 0;
                                width: 100%;
                            }

                            .content-page,
                            .content,
                            .container-fluid {
                                padding: 0 !important;
                                margin: 0 !important;
                            }
                        }

                        /* Table */
                        .table thead th {
                            vertical-align: middle;
                            white-space: nowrap;
                            text-align: center;
                            font-weight: 600;
                        }

                        .table tbody td {
                            vertical-align: middle;
                        }

                        .table td:nth-child(1) {
                            white-space: nowrap;
                        }

                        .breakdown-list div {
                            line-height: 1.35;
                        }

                        .dtr-absent {
                            background: #fff6f6;
                        }

                        .dtr-absent .badge {
                            opacity: .95;
                        }

                        .header-actions {
                            display: flex;
                            flex-direction: column;
                            align-items: flex-end;
                            gap: 8px;
                        }

                        .header-actions .header-row {
                            display: flex;
                            flex-wrap: wrap;
                            justify-content: flex-end;
                            gap: 8px;
                        }

                        .header-actions .btn,
                        .header-actions .stat-pill {
                            width: 190px;
                        }

                        .header-actions .stat-pill {
                            display: inline-flex;
                            flex-direction: column;
                            align-items: center;
                            justify-content: center;
                            padding: 6px 10px;
                            line-height: 1.15;
                            font-weight: 600;
                        }

                        .header-actions .stat-label {
                            font-size: 11px;
                            text-transform: uppercase;
                            letter-spacing: .04em;
                            color: #6c757d;
                        }

                        .header-actions .stat-value {
                            font-size: 13px;
                            color: #212529;
                        }

                        @media (max-width: 767.98px) {
                            .header-actions {
                                align-items: flex-start;
                            }
                        }
                    </style>

                    <?php
                    // Safe helper
                    if (!function_exists('h')) {
                        function h($s)
                        {
                            return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
                        }
                    }

                    if (!function_exists('format_duration')) {
                        function format_duration($seconds)
                        {
                            $seconds = (int) $seconds;
                            $hours = (int) floor($seconds / 3600);
                            $minutes = (int) floor(($seconds % 3600) / 60);
                            return $hours . ' Hrs and ' . str_pad((string) $minutes, 2, '0', STR_PAD_LEFT) . ' mins';
                        }
                    }

                    $employees = isset($employees) && is_array($employees) ? $employees : [];
                    $selectedEmployee = isset($selected_employee) ? (string) $selected_employee : '';
                    $selectedEmployeeName = isset($selected_employee_name) ? (string) $selected_employee_name : 'Employee DTR';
                    $selectedMonth = isset($selected_month) ? (int) $selected_month : (int) date('n');
                    $selectedYear = isset($selected_year) ? (int) $selected_year : (int) date('Y');
                    $taskCounts = isset($task_counts) && is_array($task_counts) ? $task_counts : [];
                    $filterApplied = !empty($filter_applied);
                    $monthTotalSeconds = isset($month_total_seconds) ? (int) $month_total_seconds : 0;
                    $presentDays = isset($present_days) ? (int) $present_days : 0;
                    $absentDays = isset($absent_days) ? (int) $absent_days : 0;
                    $pendingDays = isset($pending_days) ? (int) $pending_days : 0;
                    $months = [
                        1 => 'January',
                        2 => 'February',
                        3 => 'March',
                        4 => 'April',
                        5 => 'May',
                        6 => 'June',
                        7 => 'July',
                        8 => 'August',
                        9 => 'September',
                        10 => 'October',
                        11 => 'November',
                        12 => 'December',
                    ];
                    ?>

                    <!-- Title -->
                    <div class="row">
                        <div class="col-12">
                            <div class="page-title-box">
                                <h4 class="page-title mb-1">Employee DTR</h4>
                                <div class="clearfix"></div>
                                <hr style="border:0; height:2px; background:linear-gradient(to right, #4285F4 60%, #FBBC05 80%, #34A853 100%); border-radius:1px;" />
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12">

                            <div class="card dtr-card" id="print-area">
                                <div class="card-header">
                                    <div>
                                        <h5 class="dtr-title mb-0"><?= h($selectedEmployeeName); ?></h5>
                                        <small class="text-muted">
                                            Daily Time Record (<?= h($months[$selectedMonth] ?? ''); ?> <?= h($selectedYear); ?>)
                                        </small>
                                    </div>

                                    <div id="noPrint" class="header-actions">
                                        <?php if ($filterApplied): ?>
                                            <div class="header-row">
                                                <span class="stat-pill btn btn-sm btn-light border">
                                                    <span class="stat-label">Total Hours</span>
                                                    <span class="stat-value"><?= h(format_duration($monthTotalSeconds)); ?></span>
                                                </span>
                                                <span class="stat-pill btn btn-sm btn-light border">
                                                    <span class="stat-label">Days Present</span>
                                                    <span class="stat-value" style="color: blue"><?= h($presentDays); ?></span>
                                                </span>
                                                <span class="stat-pill btn btn-sm btn-light border">
                                                    <span class="stat-label">Days Absent</span>
                                                    <span class="stat-value" style="color: red;"><?= h($absentDays); ?></span>
                                                </span>
                                                <span class="stat-pill btn btn-sm btn-light border">
                                                    <span class="stat-label">No Time Out</span>
                                                    <span class="stat-value" style="color: #f0ad4e;"><?= h($pendingDays); ?></span>
                                                </span>
                                            </div>
                                        <?php endif; ?>
                                        <div class="header-row">
                                            <?php if ($filterApplied): ?>
                                                <button type="button" class="btn btn-sm btn-light border" onclick="history.back();">
                                                    <i class="mdi mdi-arrow-left mr-1"></i> Back
                                                </button>
                                            <?php endif; ?>
                                            <button type="button" class="btn btn-sm btn-primary" data-toggle="modal" data-target="#dtrFilterModal">
                                                <i class="mdi mdi-filter-variant mr-1"></i> Filter
                                            </button>
                                            <button id="printTable" class="btn btn-sm btn-secondary">
                                                <i class="mdi mdi-printer mr-1"></i> Print
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <div class="card-body">

                                    <div class="table-responsive">
                                        <table id="table" class="table table-striped table-bordered mb-0">
                                            <thead class="bg-light">
                                                <tr>
                                                    <th>Date</th>
                                                    <th>AM Time Breakdown</th>
                                                    <th>PM Time Breakdown</th>
                                                    <th class="text-center">Total Hours</th>
                                                    <th class="text-center">Status</th>
                                                    <th class="text-center">Accomplishment</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php if ($filterApplied): ?>
                                                    <?php if (!empty($data)): ?>
                                                        <?php foreach ($data as $row): ?>
                                                            <?php
                                                            $logDate = isset($row->logDate) ? (string) $row->logDate : '';
                                                            $ts = $logDate !== '' ? strtotime($logDate) : false;
                                                            $displayDate = $ts !== false ? date('M d, Y', $ts) : $logDate;
                                                            $amIntervals = isset($row->am_intervals) && is_array($row->am_intervals) ? $row->am_intervals : [];
                                                            $pmIntervals = isset($row->pm_intervals) && is_array($row->pm_intervals) ? $row->pm_intervals : [];
                                                            $isAbsent = isset($row->is_absent) ? (bool) $row->is_absent : (empty($amIntervals) && empty($pmIntervals));
                                                            $isPending = isset($row->is_pending) ? (bool) $row->is_pending : false;
                                                            $taskCount = $logDate !== '' && isset($taskCounts[$logDate]) ? (int) $taskCounts[$logDate] : 0;
                                                            $rowTotalSeconds = isset($row->total_seconds) ? (int) $row->total_seconds : 0;
                                                            ?>
                                                            <tr class="<?= $isAbsent ? 'dtr-absent' : ''; ?>">
                                                                <td><?= h($displayDate); ?></td>
                                                                <td class="breakdown-list">
                                                                    <?php if (!empty($amIntervals)): ?>
                                                                        <?php foreach ($amIntervals as $intv): ?>
                                                                            <div><?= h($intv['label'] ?? ''); ?></div>
                                                                        <?php endforeach; ?>
                                                                    <?php else: ?>
                                                                        <span class="text-muted">No AM punches</span>
                                                                    <?php endif; ?>
                                                                </td>
                                                                <td class="breakdown-list">
                                                                    <?php if (!empty($pmIntervals)): ?>
                                                                        <?php foreach ($pmIntervals as $intv): ?>
                                                                            <div><?= h($intv['label'] ?? ''); ?></div>
                                                                        <?php endforeach; ?>
                                                                    <?php else: ?>
                                                                        <span class="text-muted">No PM punches</span>
                                                                    <?php endif; ?>
                                                                </td>
                                                                <td class="text-center"><?= h(format_duration($rowTotalSeconds)); ?></td>
                                                                <td class="text-center">
                                                                    <?php if ($isAbsent): ?>
                                                                        <span class="badge badge-danger">Absent</span>
                                                                    <?php elseif ($isPending): ?>
                                                                        <span class="badge badge-warning text-dark">No Time Out Yet</span>
                                                                    <?php else: ?>
                                                                        <span class="badge badge-success">Present</span>
                                                                    <?php endif; ?>
                                                                </td>
                                                                <td class="text-center">
                                                                    <?php if ($selectedEmployee !== '' && $logDate !== ''): ?>
                                                                        <a class="text-primary" href="<?= base_url(); ?>Page/accomplishmentStaff?assignedPerson=<?= urlencode((string) $selectedEmployee); ?>&date=<?= urlencode((string) $logDate); ?>">
                                                                            <i class="mdi mdi-clipboard-text" style="font-size: 22px;"></i>
                                                                        </a>
                                                                        <span class="badge badge-light border ml-1"><?= $taskCount; ?></span>
                                                                    <?php else: ?>
                                                                        <span class="text-muted">—</span>
                                                                    <?php endif; ?>
                                                                </td>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                    <?php else: ?>
                                                        <tr>
                                                            <td colspan="6" class="text-center text-muted py-3">No DTR records found for the selected month.</td>
                                                        </tr>
                                                    <?php endif; ?>
                                                <?php else: ?>
                                                    <tr>
                                                        <td colspan="6" class="text-center text-muted py-3">Select an employee and month to view attendance.</td>
                                                    </tr>
                                                <?php endif; ?>
                                            </tbody>
                                        </table>
                                    </div>

                                </div><!-- card-body -->
                            </div><!-- card -->
                        </div>
                    </div>

                    <!-- Today's Personnel Section -->
                    <?php
                    $todayPersonnel = isset($today_personnel) && is_array($today_personnel) ? $today_personnel : [];
                    if (!empty($todayPersonnel)):
                    ?>
                        <div class="row" id="noPrint">
                            <div class="col-12">
                                <div class="card dtr-card mb-3">
                                    <div class="card-header bg-light">
                                        <div>
                                            <h5 class="dtr-title mb-0"><i class="mdi mdi-account-clock mr-2"></i>Today's Attendance (<?= date('M d, Y'); ?>)</h5>
                                            <small class="text-muted">Staff with time-in today</small>
                                        </div>
                                        <div>
                                            <span class="badge badge-info"><?= count($todayPersonnel); ?> Present</span>
                                        </div>
                                    </div>
                                    <div class="card-body py-2" style="max-height: 400px; overflow-y: auto;">
                                        <div class="table-responsive">
                                            <table class="table table-sm table-hover mb-0">
                                                <thead class="bg-light">
                                                    <tr>
                                                        <th>Employee</th>
                                                        <th>AM Time In</th>
                                                        <th>AM Time Out</th>
                                                        <th>PM Time In</th>
                                                        <th>PM Time Out</th>
                                                        <th class="text-center">Status</th>
                                                        <th class="text-center">Total Hours</th>
                                                        <th class="text-center"></th>Accomplishment</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($todayPersonnel as $person): ?>
                                                        <?php
                                                        $fullName = trim((string) ($person->fName ?? '') . ' ' . (string) ($person->lName ?? ''));
                                                        if ($fullName === '') {
                                                            $fullName = (string) ($person->username ?? 'Unknown');
                                                        }
                                                        $hasAmIn = !empty($person->amTimeIn);
                                                        $hasAmOut = !empty($person->amTimeOut);
                                                        $hasPmIn = !empty($person->pmTimeIn);
                                                        $hasPmOut = !empty($person->pmTimeOut);

                                                        // Determine status
                                                        if (($hasAmIn && $hasAmOut) || ($hasPmIn && $hasPmOut)) {
                                                            $status = 'Complete';
                                                            $statusBadge = 'badge-success';
                                                        } elseif ($hasAmIn || $hasPmIn) {
                                                            $status = 'No Time Out';
                                                            $statusBadge = 'badge-warning text-dark';
                                                        } else {
                                                            $status = 'Absent';
                                                            $statusBadge = 'badge-danger';
                                                        }

                                                        // Get today's accomplishment count
                                                        $todayDate = date('Y-m-d');
                                                        $accCount = 0;
                                                        if (!empty($person->username)) {
                                                            $dayTasks = $this->CashModel->accomplishmentsStaffbyDate($settingsID, $person->username, $todayDate);
                                                            $accCount = count($dayTasks);
                                                        }

                                                        // Calculate total hours for today
                                                        $todayTotalSeconds = 0;
                                                        if ($hasAmIn && $hasAmOut) {
                                                            $amStart = strtotime($todayDate . ' ' . $person->amTimeIn);
                                                            $amEnd = strtotime($todayDate . ' ' . $person->amTimeOut);
                                                            if ($amEnd > $amStart) {
                                                                $todayTotalSeconds += ($amEnd - $amStart);
                                                            }
                                                        }
                                                        if ($hasPmIn && $hasPmOut) {
                                                            $pmStart = strtotime($todayDate . ' ' . $person->pmTimeIn);
                                                            $pmEnd = strtotime($todayDate . ' ' . $person->pmTimeOut);
                                                            if ($pmEnd > $pmStart) {
                                                                $todayTotalSeconds += ($pmEnd - $pmStart);
                                                            }
                                                        }
                                                        $todayTotalHours = format_duration($todayTotalSeconds);
                                                        ?>
                                                        <tr>
                                                            <td>
                                                                <strong><?= h($fullName); ?></strong>
                                                                <br><small class="text-muted"><?= h($person->username ?? ''); ?></small>
                                                            </td>
                                                            <td><?= $hasAmIn ? h($person->amTimeIn) : '<span class="text-muted">—</span>'; ?></td>
                                                            <td><?= $hasAmOut ? h($person->amTimeOut) : '<span class="text-muted">—</span>'; ?></td>
                                                            <td><?= $hasPmIn ? h($person->pmTimeIn) : '<span class="text-muted">—</span>'; ?></td>
                                                            <td><?= $hasPmOut ? h($person->pmTimeOut) : '<span class="text-muted">—</span>'; ?></td>
                                                            <td class="text-center">
                                                                <span class="badge <?= $statusBadge; ?>"><?= $status; ?></span>
                                                            </td>
                                                            <td class="text-center"><?= h($todayTotalHours); ?></td>
                                                            <td class="text-center">
                                                                <?php if (!empty($person->username)): ?>
                                                                    <a class="text-primary" href="<?= base_url(); ?>Page/accomplishmentStaff?assignedPerson=<?= urlencode((string) $person->username); ?>&date=<?= urlencode($todayDate); ?>">
                                                                        <i class="mdi mdi-clipboard-text" style="font-size: 18px;"></i>
                                                                    </a>
                                                                    <span class="badge badge-light border ml-1"><?= $accCount; ?></span>
                                                                <?php else: ?>
                                                                    <span class="text-muted">—</span>
                                                                <?php endif; ?>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                </div><!-- container-fluid -->
            </div><!-- content -->

            <?php include('includes/footer.php'); ?>
        </div><!-- content-page -->
    </div><!-- wrapper -->

    <?php include('includes/themecustomizer.php'); ?>

    <!-- Vendor js -->
    <script src="<?= base_url(); ?>assets/js/vendor.min.js"></script>

    <!-- App js -->
    <script src="<?= base_url(); ?>assets/js/app.min.js"></script>

    <script>
        (function($) {
            'use strict';

            $(function() {

                $('#printTable').on('click', function() {
                    window.print();
                });

            });

        })(jQuery);
    </script>

    <div class="modal fade" id="dtrFilterModal" tabindex="-1" role="dialog" aria-labelledby="dtrFilterModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form method="get" action="<?= base_url(); ?>Page/empDTR">
                    <div class="modal-header">
                        <h5 class="modal-title mb-0" id="dtrFilterModalLabel">Select Employee and Month</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="employeeSelect">Employee</label>
                            <select id="employeeSelect" name="id" class="form-control" required>
                                <option value="">-- Select Employee --</option>
                                <?php foreach ($employees as $emp): ?>
                                    <?php
                                    $value = !empty($emp->username) ? (string) $emp->username : (string) ($emp->user_id ?? '');
                                    $fullName = trim((string) ($emp->fName ?? '') . ' ' . (string) ($emp->lName ?? ''));
                                    if ($fullName === '') {
                                        $fullName = $value !== '' ? $value : 'Employee';
                                    }
                                    $label = $fullName;
                                    ?>
                                    <option value="<?= h($value); ?>" <?= ($selectedEmployee !== '' && $selectedEmployee === (string) $value) ? 'selected' : ''; ?>>
                                        <?= h($label); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="monthSelect">Month</label>
                                <select id="monthSelect" name="month" class="form-control" required>
                                    <?php foreach ($months as $num => $label): ?>
                                        <option value="<?= $num; ?>" <?= ($selectedMonth === (int) $num) ? 'selected' : ''; ?>>
                                            <?= h($label); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group col-md-6">
                                <label for="yearSelect">Year</label>
                                <input id="yearSelect" type="number" name="year" class="form-control" min="2000" max="2100" value="<?= h($selectedYear); ?>">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <a href="<?= base_url(); ?>Page/empDTR" class="btn btn-light border">Clear</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="mdi mdi-magnify mr-1"></i> View
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</body>

</html>