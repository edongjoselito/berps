<?php
if (!function_exists('h')) {
    function h($str)
    {
        return htmlspecialchars((string) $str, ENT_QUOTES, 'UTF-8');
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

$selectedEmployee = isset($selected_employee) ? (string) $selected_employee : '';
$selectedEmployeeName = isset($selected_employee_name) ? (string) $selected_employee_name : 'My DTR';
$selectedMonth = isset($selected_month) ? (int) $selected_month : (int) date('n');
$selectedYear = isset($selected_year) ? (int) $selected_year : (int) date('Y');
$taskCounts = isset($task_counts) && is_array($task_counts) ? $task_counts : array();
$monthTotalSeconds = isset($month_total_seconds) ? (int) $month_total_seconds : 0;
$presentDays = isset($present_days) ? (int) $present_days : 0;
$absentDays = isset($absent_days) ? (int) $absent_days : 0;
$pendingDays = isset($pending_days) ? (int) $pending_days : 0;
$dtrRows = isset($data) ? $data : array();
if ($dtrRows instanceof Traversable) {
    $dtrRows = iterator_to_array($dtrRows, false);
}
$dtrRows = is_array($dtrRows) ? array_values($dtrRows) : array();

$monthTaskTotal = 0;
foreach ($taskCounts as $taskCountValue) {
    $monthTaskTotal += (int) $taskCountValue;
}

$months = array(
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
);

$selectedMonthLabel = isset($months[$selectedMonth]) ? $months[$selectedMonth] : date('F');
$selectedPeriodLabel = $selectedMonthLabel . ' ' . $selectedYear;
$currentDateLabel = date('l, F j, Y');
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
                <div class="container-fluid my-dtr-page">

                    <style>
                        @import url('https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,100..1000;1,9..40,100..1000&display=swap');

                        .my-dtr-page {
                            --bg: #f4f7fb;
                            --surface: rgba(255, 255, 255, 0.98);
                            --surface-soft: #f8fbff;
                            --surface-soft-2: #f1f6fd;
                            --line: #e5edf6;
                            --line-strong: #cfdaea;
                            --text: #152537;
                            --text-soft: #63778c;
                            --text-faint: #90a2b8;
                            --primary: #2563eb;
                            --primary-2: #1d4ed8;
                            --primary-soft: #eaf2ff;
                            --success: #059669;
                            --success-soft: #ecfdf5;
                            --warning: #d97706;
                            --warning-soft: #fff7ed;
                            --danger: #e11d48;
                            --danger-soft: #fff1f2;
                            --info: #0891b2;
                            --info-soft: #ecfeff;
                            --violet: #7c3aed;
                            --violet-soft: #f3e8ff;
                            --shadow: 0 16px 40px rgba(15, 23, 42, 0.06);
                            --shadow-soft: 0 8px 24px rgba(15, 23, 42, 0.04);
                            --radius-xl: 16px;
                            --radius-lg: 12px;
                            --radius-md: 10px;
                            --font-body: 'DM Sans', 'Segoe UI', Arial, sans-serif;
                            --font-head: 'DM Sans', 'Segoe UI', Arial, sans-serif;
                            --font-mono: 'DM Sans', 'SFMono-Regular', Consolas, monospace;
                            background:
                                radial-gradient(circle at top left, rgba(37, 99, 235, 0.08), transparent 26%),
                                radial-gradient(circle at top right, rgba(16, 185, 129, 0.07), transparent 22%),
                                linear-gradient(180deg, #f9fbff 0%, #f4f7fb 100%);
                            min-height: 100vh;
                            padding: 16px 0 48px;
                            font-family: var(--font-body);
                        }

                        .my-dtr-page * {
                            box-sizing: border-box;
                        }

                        .my-dtr-page .alert {
                            border: none;
                            border-radius: 16px;
                            box-shadow: var(--shadow-soft);
                        }

                        .my-dtr-page .page-header {
                            display: flex;
                            justify-content: space-between;
                            align-items: flex-end;
                            gap: 18px;
                            margin: 6px 0 22px;
                            flex-wrap: wrap;
                        }

                        .my-dtr-page .page-eyebrow {
                            display: inline-flex;
                            align-items: center;
                            gap: 8px;
                            padding: 7px 12px;
                            border-radius: 999px;
                            background: rgba(37, 99, 235, 0.09);
                            color: var(--primary-2);
                            font-size: 0.74rem;
                            font-weight: 800;
                            letter-spacing: 0.09em;
                            text-transform: uppercase;
                            margin-bottom: 12px;
                        }

                        .my-dtr-page .page-eyebrow::before {
                            content: '';
                            width: 8px;
                            height: 8px;
                            border-radius: 50%;
                            background: linear-gradient(135deg, var(--primary), var(--primary-2));
                            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.11);
                        }

                        .my-dtr-page .page-title {
                            margin: 0;
                            font-family: var(--font-head);
                            font-size: 1.5rem;
                            line-height: 1.2;
                            letter-spacing: -0.02em;
                            font-weight: 700;
                            color: var(--text);
                        }

                        .my-dtr-page .page-subtitle {
                            margin-top: 8px;
                            color: var(--text-soft);
                            font-size: 0.9rem;
                            max-width: 780px;
                            line-height: 1.5;
                        }

                        .my-dtr-page .page-actions {
                            display: flex;
                            gap: 12px;
                            flex-wrap: wrap;
                        }

                        .my-dtr-page .btn-action,
                        .my-dtr-page .btn-submit {
                            display: inline-flex;
                            align-items: center;
                            justify-content: center;
                            gap: 8px;
                            border-radius: 14px;
                            font-size: 0.92rem;
                            font-weight: 700;
                            padding: 11px 18px;
                            transition: all 0.18s ease;
                            text-decoration: none;
                            min-height: 46px;
                        }

                        .my-dtr-page .btn-action {
                            border: 1px solid var(--line-strong);
                            color: var(--text);
                            background: #fff;
                            box-shadow: 0 8px 22px rgba(15, 23, 42, 0.04);
                        }

                        .my-dtr-page .btn-action:hover {
                            color: var(--primary);
                            border-color: #bfd3ef;
                            background: #f9fbff;
                            transform: translateY(-1px);
                        }

                        .my-dtr-page .btn-submit {
                            border: none;
                            color: #fff;
                            background: linear-gradient(135deg, var(--primary), var(--primary-2));
                            box-shadow: 0 12px 28px rgba(37, 99, 235, 0.24);
                        }

                        .my-dtr-page .btn-submit:hover {
                            transform: translateY(-1px);
                            box-shadow: 0 16px 32px rgba(37, 99, 235, 0.28);
                            color: #fff;
                        }

                        .my-dtr-page .stats-grid {
                            display: grid;
                            grid-template-columns: repeat(5, minmax(0, 1fr));
                            gap: 12px;
                            margin-bottom: 16px;
                        }

                        .my-dtr-page .stat-card {
                            position: relative;
                            overflow: hidden;
                            background: var(--surface);
                            border: 1px solid rgba(255, 255, 255, 0.76);
                            border-radius: var(--radius-xl);
                            box-shadow: var(--shadow-soft);
                            padding: 14px 16px 14px;
                            transition: transform 0.16s ease, box-shadow 0.16s ease;
                        }

                        .my-dtr-page .stat-card:hover {
                            transform: translateY(-2px);
                            box-shadow: 0 18px 40px rgba(15, 23, 42, 0.08);
                        }

                        .my-dtr-page .stat-card::before {
                            content: '';
                            position: absolute;
                            inset: 0 0 auto 0;
                            height: 4px;
                        }

                        .my-dtr-page .stat-hours::before {
                            background: linear-gradient(90deg, #3b82f6, #60a5fa);
                        }

                        .my-dtr-page .stat-present::before {
                            background: linear-gradient(90deg, #10b981, #34d399);
                        }

                        .my-dtr-page .stat-absent::before {
                            background: linear-gradient(90deg, #ef4444, #fb7185);
                        }

                        .my-dtr-page .stat-pending::before {
                            background: linear-gradient(90deg, #f59e0b, #fbbf24);
                        }

                        .my-dtr-page .stat-tasks::before {
                            background: linear-gradient(90deg, #8b5cf6, #c4b5fd);
                        }

                        .my-dtr-page .stat-icon {
                            display: flex;
                            align-items: center;
                            justify-content: center;
                            width: 28px;
                            height: 28px;
                            border-radius: 8px;
                            margin-bottom: 10px;
                            font-size: 0.9rem;
                        }

                        .stat-hours .stat-icon {
                            background: rgba(59, 130, 246, 0.1);
                            color: #3b82f6;
                        }

                        .stat-present .stat-icon {
                            background: rgba(16, 185, 129, 0.1);
                            color: #10b981;
                        }

                        .stat-absent .stat-icon {
                            background: rgba(239, 68, 68, 0.1);
                            color: #ef4444;
                        }

                        .stat-pending .stat-icon {
                            background: rgba(245, 158, 11, 0.1);
                            color: #f59e0b;
                        }

                        .stat-tasks .stat-icon {
                            background: rgba(139, 92, 246, 0.1);
                            color: #8b5cf6;
                        }

                        .my-dtr-page .stat-label {
                            color: var(--text-faint);
                            font-size: 0.65rem;
                            font-weight: 600;
                            text-transform: uppercase;
                            letter-spacing: 0.06em;
                            margin-bottom: 4px;
                        }

                        .my-dtr-page .stat-value {
                            color: var(--text);
                            font-size: 1.25rem;
                            font-weight: 700;
                            line-height: 1.2;
                            letter-spacing: -0.02em;
                            font-family: var(--font-head);
                        }

                        .my-dtr-page .stat-meta {
                            color: var(--text-soft);
                            font-size: 0.72rem;
                            margin-top: 4px;
                            line-height: 1.4;
                        }

                        .my-dtr-page .theme-banner {
                            display: flex;
                            justify-content: space-between;
                            align-items: flex-start;
                            gap: 14px;
                            padding: 14px 18px;
                            border-radius: var(--radius-lg);
                            background: linear-gradient(135deg, #eff6ff, #f7fbff);
                            border: 1px solid rgba(37, 99, 235, 0.11);
                            box-shadow: var(--shadow-soft);
                            margin-bottom: 14px;
                        }

                        .my-dtr-page .banner-title {
                            color: var(--text);
                            font-size: 0.9rem;
                            font-weight: 700;
                            margin-bottom: 4px;
                        }

                        .my-dtr-page .banner-copy {
                            color: var(--text-soft);
                            font-size: 0.82rem;
                            line-height: 1.5;
                        }

                        .my-dtr-page .banner-id {
                            padding: 8px 12px;
                            border-radius: 999px;
                            background: rgba(255, 255, 255, 0.8);
                            border: 1px solid rgba(37, 99, 235, 0.10);
                            color: var(--primary-2);
                            font-size: 0.84rem;
                            font-weight: 700;
                            white-space: nowrap;
                        }

                        .my-dtr-page .theme-card {
                            background: var(--surface);
                            border: 1px solid rgba(255, 255, 255, 0.78);
                            border-radius: var(--radius-xl);
                            box-shadow: var(--shadow-soft);
                            overflow: hidden;
                        }

                        .my-dtr-page .theme-card-head {
                            display: flex;
                            justify-content: space-between;
                            align-items: flex-start;
                            gap: 14px;
                            padding: 16px 18px;
                            border-bottom: 1px solid var(--line);
                            background: linear-gradient(180deg, rgba(255, 255, 255, 0.99), rgba(249, 251, 255, 0.95));
                            flex-wrap: wrap;
                        }

                        .my-dtr-page .theme-card-title {
                            margin: 0;
                            color: var(--text);
                            font-size: 0.95rem;
                            font-weight: 700;
                            letter-spacing: -0.01em;
                        }

                        .my-dtr-page .theme-card-subtitle {
                            margin-top: 6px;
                            color: var(--text-soft);
                            font-size: 0.87rem;
                            line-height: 1.55;
                        }

                        .my-dtr-page .theme-card-body {
                            padding: 18px;
                        }

                        .my-dtr-page .mini-stats {
                            display: flex;
                            gap: 10px;
                            flex-wrap: wrap;
                        }

                        .my-dtr-page .mini-stat-pill {
                            display: inline-flex;
                            align-items: center;
                            gap: 6px;
                            padding: 7px 12px;
                            border-radius: 999px;
                            background: var(--surface-soft);
                            border: 1px solid var(--line);
                            font-size: 0.8rem;
                            color: var(--text-soft);
                        }

                        .my-dtr-page .mini-stat-pill strong {
                            color: var(--text);
                            font-family: var(--font-mono);
                        }

                        .my-dtr-page .table-responsive {
                            border-radius: 18px;
                        }

                        .my-dtr-page .dtr-table {
                            width: 100%;
                            border-collapse: separate;
                            border-spacing: 0;
                        }

                        .my-dtr-page .dtr-table thead th {
                            border-top: none;
                            border-bottom: 1px solid var(--line);
                            color: var(--text-faint);
                            font-size: 0.72rem;
                            font-weight: 800;
                            letter-spacing: 0.08em;
                            text-transform: uppercase;
                            white-space: nowrap;
                            padding: 0 14px 12px;
                            background: transparent;
                        }

                        .my-dtr-page .dtr-table td {
                            padding: 16px 14px;
                            border-bottom: 1px solid var(--line);
                            vertical-align: top;
                            color: var(--text);
                            background: transparent;
                            transition: background 0.15s ease;
                        }

                        .my-dtr-page .dtr-table tbody tr:hover td {
                            background: #fafcff;
                        }

                        .my-dtr-page .dtr-table tbody tr:last-child td {
                            border-bottom: none;
                        }

                        .my-dtr-page .row-absent td {
                            background: rgba(225, 29, 72, 0.03);
                        }

                        .my-dtr-page .date-label {
                            font-weight: 800;
                            color: var(--text);
                            line-height: 1.45;
                            font-size: 0.95rem;
                        }

                        .my-dtr-page .breakdown-list {
                            display: grid;
                            gap: 7px;
                        }

                        .my-dtr-page .breakdown-line {
                            display: inline-flex;
                            align-items: center;
                            gap: 7px;
                            color: var(--text-soft);
                            font-size: 0.83rem;
                            line-height: 1.4;
                            padding: 5px 9px;
                            border-radius: 8px;
                            background: #f4f8ff;
                            border: 1px solid #e5eef9;
                        }

                        .my-dtr-page .date-day {
                            display: block;
                            font-size: 0.72rem;
                            font-weight: 700;
                            color: var(--text-faint);
                            text-transform: uppercase;
                            letter-spacing: 0.06em;
                            margin-bottom: 2px;
                        }

                        .my-dtr-page .muted-copy {
                            color: var(--text-soft);
                            font-size: 0.87rem;
                            line-height: 1.45;
                        }

                        .my-dtr-page .empty-time {
                            display: inline-flex;
                            align-items: center;
                            padding: 7px 10px;
                            border-radius: 10px;
                            background: #fafafa;
                            border: 1px dashed #d9e3ef;
                            color: var(--text-faint);
                            font-size: 0.84rem;
                        }

                        .my-dtr-page .total-label {
                            font-weight: 800;
                            color: var(--text);
                            font-family: var(--font-mono);
                            font-size: 0.95rem;
                            padding-top: 4px;
                        }

                        .my-dtr-page .status-pill {
                            display: inline-flex;
                            align-items: center;
                            justify-content: center;
                            padding: 7px 12px;
                            border-radius: 999px;
                            font-size: 0.73rem;
                            font-weight: 800;
                            letter-spacing: 0.05em;
                            text-transform: uppercase;
                            white-space: nowrap;
                        }

                        .my-dtr-page .status-present {
                            background: var(--success-soft);
                            color: var(--success);
                        }

                        .my-dtr-page .status-absent {
                            background: var(--danger-soft);
                            color: var(--danger);
                        }

                        .my-dtr-page .status-pending {
                            background: var(--warning-soft);
                            color: var(--warning);
                        }

                        .my-dtr-page .task-cell {
                            min-width: 150px;
                        }

                        .my-dtr-page .task-stack {
                            display: flex;
                            align-items: center;
                            gap: 10px;
                            flex-wrap: wrap;
                        }

                        .my-dtr-page .task-link {
                            display: inline-flex;
                            align-items: center;
                            gap: 8px;
                            color: var(--primary-2);
                            font-weight: 700;
                            text-decoration: none;
                            padding: 8px 12px;
                            border-radius: 12px;
                            background: var(--primary-soft);
                            transition: all 0.16s ease;
                        }

                        .my-dtr-page .task-link:hover {
                            text-decoration: none;
                            transform: translateY(-1px);
                            background: #dfeaff;
                        }

                        .my-dtr-page .task-count-pill {
                            display: inline-flex;
                            align-items: center;
                            justify-content: center;
                            min-width: 34px;
                            height: 34px;
                            padding: 0 10px;
                            border-radius: 999px;
                            background: var(--violet-soft);
                            color: var(--violet);
                            font-size: 0.8rem;
                            font-weight: 800;
                            border: 1px solid rgba(124, 58, 237, 0.08);
                        }

                        .my-dtr-page .empty-state {
                            padding: 18px 0 10px;
                            color: var(--text-soft);
                            font-size: 0.94rem;
                            text-align: center;
                        }

                        .my-dtr-page .theme-modal .modal-content {
                            border: none;
                            border-radius: 22px;
                            overflow: hidden;
                            box-shadow: 0 24px 60px rgba(15, 23, 42, 0.18);
                        }

                        .my-dtr-page .theme-modal .modal-header {
                            padding: 18px 22px;
                            border-bottom: 1px solid var(--line);
                            background: linear-gradient(180deg, rgba(255, 255, 255, 0.98), rgba(249, 251, 255, 0.94));
                        }

                        .my-dtr-page .theme-modal .modal-title {
                            color: var(--text);
                            font-size: 1.02rem;
                            font-weight: 800;
                            letter-spacing: -0.02em;
                        }

                        .my-dtr-page .theme-modal .close {
                            color: var(--text-soft);
                            opacity: 1;
                            text-shadow: none;
                        }

                        .my-dtr-page .theme-modal .modal-body {
                            padding: 22px;
                            background: #fff;
                        }

                        .my-dtr-page .theme-modal .modal-footer {
                            padding: 0 22px 22px;
                            border-top: none;
                            gap: 10px;
                            background: #fff;
                        }

                        .my-dtr-page .theme-modal label {
                            color: var(--text);
                            font-size: 0.85rem;
                            font-weight: 700;
                            margin-bottom: 6px;
                        }

                        .my-dtr-page .theme-modal .form-control,
                        .my-dtr-page .theme-modal .custom-select {
                            border: 1px solid var(--line-strong);
                            border-radius: 12px;
                            min-height: 46px;
                            box-shadow: none;
                            background: #fff;
                        }

                        .my-dtr-page .theme-modal .form-control:focus,
                        .my-dtr-page .theme-modal .custom-select:focus {
                            border-color: rgba(37, 99, 235, 0.45);
                            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.12);
                        }

                        .my-dtr-page .theme-modal .btn {
                            border-radius: 12px;
                            font-weight: 700;
                            padding: 10px 16px;
                        }

                        .my-dtr-page .theme-modal .btn-primary {
                            border: none;
                            color: #fff;
                            background: linear-gradient(135deg, var(--primary), var(--primary-2));
                            box-shadow: 0 10px 24px rgba(37, 99, 235, 0.22);
                        }

                        .my-dtr-page .theme-modal .btn-light {
                            border: 1px solid var(--line-strong);
                            color: var(--text);
                            background: #fff;
                        }

                        @media print {

                            #noPrint,
                            .left-side-menu,
                            .navbar-custom,
                            .footer,
                            .theme-customizer,
                            .modal,
                            .modal-backdrop {
                                display: none !important;
                            }

                            body,
                            html {
                                background: #fff !important;
                            }

                            .content-page {
                                margin-left: 0 !important;
                                padding: 0 !important;
                            }

                            .content,
                            .container-fluid {
                                padding: 0 !important;
                                margin: 0 !important;
                            }

                            .my-dtr-page {
                                background: #fff !important;
                                padding: 0 !important;
                            }

                            .my-dtr-page .theme-card,
                            .my-dtr-page .stat-card,
                            .my-dtr-page .theme-banner {
                                box-shadow: none !important;
                                border: 1px solid #dbe3ee !important;
                            }

                            .my-dtr-page .stats-grid {
                                grid-template-columns: repeat(5, 1fr) !important;
                                gap: 10px !important;
                            }

                            .my-dtr-page .dtr-table thead th,
                            .my-dtr-page .dtr-table td {
                                padding: 10px 8px !important;
                                font-size: 12px !important;
                            }

                            .my-dtr-page .task-link {
                                background: transparent !important;
                                padding: 0 !important;
                            }
                        }

                        @media (max-width: 1399px) {
                            .my-dtr-page .stats-grid {
                                grid-template-columns: repeat(3, minmax(0, 1fr));
                            }
                        }

                        @media (max-width: 991px) {
                            .my-dtr-page .stats-grid {
                                grid-template-columns: repeat(2, minmax(0, 1fr));
                            }
                        }

                        @media (max-width: 767px) {
                            .my-dtr-page .page-title {
                                font-size: 1.45rem;
                            }

                            .my-dtr-page .stats-grid {
                                grid-template-columns: 1fr;
                            }

                            .my-dtr-page .theme-banner,
                            .my-dtr-page .theme-card-head {
                                flex-direction: column;
                                align-items: flex-start;
                            }

                            .my-dtr-page .banner-id {
                                white-space: normal;
                            }

                            .my-dtr-page .theme-card-head,
                            .my-dtr-page .theme-card-body,
                            .my-dtr-page .theme-modal .modal-header,
                            .my-dtr-page .theme-modal .modal-body,
                            .my-dtr-page .theme-modal .modal-footer {
                                padding-left: 16px;
                                padding-right: 16px;
                            }

                            .my-dtr-page .dtr-table thead {
                                display: none;
                            }

                            .my-dtr-page .dtr-table,
                            .my-dtr-page .dtr-table tbody,
                            .my-dtr-page .dtr-table tr,
                            .my-dtr-page .dtr-table td {
                                display: block;
                                width: 100%;
                            }

                            .my-dtr-page .dtr-table tr {
                                margin-bottom: 14px;
                                border: 1px solid var(--line);
                                border-radius: 16px;
                                overflow: hidden;
                                background: #fff;
                            }

                            .my-dtr-page .dtr-table td {
                                border-bottom: 1px solid var(--line);
                                padding: 12px 14px;
                            }

                            .my-dtr-page .dtr-table td:last-child {
                                border-bottom: none;
                            }
                        }
                    </style>

                    <?php if ($this->session->flashdata('success')): ?>
                        <div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
                            <?= h($this->session->flashdata('success')); ?>
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    <?php endif; ?>

                    <?php if ($this->session->flashdata('danger')): ?>
                        <div class="alert alert-danger alert-dismissible fade show mt-3" role="alert">
                            <?= h($this->session->flashdata('danger')); ?>
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    <?php endif; ?>

                    <div class="page-header" id="noPrint">
                        <div>
                            <div class="page-eyebrow">Attendance Overview</div>
                            <h4 class="page-title">My Daily Time Record</h4>
                            <div class="page-subtitle">
                                Review your attendance, total worked hours, daily punch logs, and linked accomplishments for <?= h($selectedPeriodLabel); ?>.
                            </div>
                        </div>
                        <div class="page-actions">
                            <button type="button" class="btn-action" data-toggle="modal" data-target="#dtrFilterModal">
                                <i class="mdi mdi-calendar-month-outline"></i>
                                Change Month
                            </button>
                            <button id="printTable" type="button" class="btn-submit">
                                <i class="mdi mdi-printer"></i>
                                Print
                            </button>
                        </div>
                    </div>

                    <div class="stats-grid">
                        <div class="stat-card stat-hours">
                            <div class="stat-icon"><i class="mdi mdi-clock-outline"></i></div>
                            <div class="stat-label">Total Hours</div>
                            <div class="stat-value"><?= h(format_duration($monthTotalSeconds)); ?></div>
                            <div class="stat-meta">Accumulated worked time for <?= h($selectedPeriodLabel); ?>.</div>
                        </div>
                        <div class="stat-card stat-present">
                            <div class="stat-icon"><i class="mdi mdi-check-circle-outline"></i></div>
                            <div class="stat-label">Days Present</div>
                            <div class="stat-value"><?= number_format($presentDays); ?></div>
                            <div class="stat-meta">Complete days with valid time in and time out.</div>
                        </div>
                        <div class="stat-card stat-absent">
                            <div class="stat-icon"><i class="mdi mdi-close-circle-outline"></i></div>
                            <div class="stat-label">Days Absent</div>
                            <div class="stat-value"><?= number_format($absentDays); ?></div>
                            <div class="stat-meta">Days without recorded AM or PM punches.</div>
                        </div>
                        <div class="stat-card stat-pending">
                            <div class="stat-icon"><i class="mdi mdi-timer-sand"></i></div>
                            <div class="stat-label">No Time Out</div>
                            <div class="stat-value"><?= number_format($pendingDays); ?></div>
                            <div class="stat-meta">Days with incomplete attendance logs.</div>
                        </div>
                        <div class="stat-card stat-tasks">
                            <div class="stat-icon"><i class="mdi mdi-clipboard-check-outline"></i></div>
                            <div class="stat-label">Accomplishments</div>
                            <div class="stat-value"><?= number_format($monthTaskTotal); ?></div>
                            <div class="stat-meta">Total linked accomplishment entries this month.</div>
                        </div>
                    </div>

                    <div class="theme-banner" id="print-area">
                        <div>
                            <div class="banner-title"><?= h($selectedEmployeeName); ?></div>
                            <div class="banner-copy">
                                Personal Daily Time Record for <?= h($selectedPeriodLabel); ?>. Current date: <?= h($currentDateLabel); ?>.
                            </div>
                        </div>
                        <div class="banner-id">
                            Employee ID: <?= h($selectedEmployee !== '' ? $selectedEmployee : 'Not set'); ?>
                        </div>
                    </div>

                    <div class="theme-card">
                        <div class="theme-card-head">
                            <div>
                                <h5 class="theme-card-title">Attendance Ledger</h5>
                                <div class="theme-card-subtitle">
                                    View your AM and PM punch intervals, total worked hours, attendance status, and accomplishment links per day.
                                </div>
                            </div>
                            <div class="mini-stats">
                                <span class="mini-stat-pill">Month <strong><?= h($selectedMonthLabel); ?></strong></span>
                                <span class="mini-stat-pill">Year <strong><?= h($selectedYear); ?></strong></span>
                                <span class="mini-stat-pill">Rows <strong><?= number_format(count($dtrRows)); ?></strong></span>
                            </div>
                        </div>

                        <div class="theme-card-body">
                            <div class="table-responsive">
                                <table class="table dtr-table mb-0">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>AM Time Breakdown</th>
                                            <th>PM Time Breakdown</th>
                                            <th>Total Hours</th>
                                            <th>Status</th>
                                            <th>Accomplishment</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($dtrRows)): ?>
                                            <?php foreach ($dtrRows as $row): ?>
                                                <?php
                                                $logDate = isset($row->logDate) ? (string) $row->logDate : '';
                                                $ts = $logDate !== '' ? strtotime($logDate) : false;
                                                $displayDate = $ts !== false ? date('M d, Y', $ts) : $logDate;
                                                $displayDay  = $ts !== false ? date('D', $ts) : '';
                                                $amIntervals = isset($row->am_intervals) && is_array($row->am_intervals) ? $row->am_intervals : array();
                                                $pmIntervals = isset($row->pm_intervals) && is_array($row->pm_intervals) ? $row->pm_intervals : array();
                                                $isAbsent = isset($row->is_absent) ? (bool) $row->is_absent : (empty($amIntervals) && empty($pmIntervals));
                                                $isPending = isset($row->is_pending) ? (bool) $row->is_pending : false;
                                                $taskCount = $logDate !== '' && isset($taskCounts[$logDate]) ? (int) $taskCounts[$logDate] : 0;
                                                $rowTotalSeconds = isset($row->total_seconds) ? (int) $row->total_seconds : 0;
                                                ?>
                                                <tr class="<?= $isAbsent ? 'row-absent' : ''; ?>">
                                                    <td>
                                                        <?php if ($displayDay !== ''): ?>
                                                            <span class="date-day"><?= h($displayDay); ?></span>
                                                        <?php endif; ?>
                                                        <div class="date-label"><?= h($displayDate); ?></div>
                                                    </td>

                                                    <td>
                                                        <div class="breakdown-list">
                                                            <?php if (!empty($amIntervals)): ?>
                                                                <?php foreach ($amIntervals as $intv): ?>
                                                                    <div class="breakdown-line">
                                                                        <i class="mdi mdi-weather-sunset-up text-primary"></i>
                                                                        <?= h($intv['label'] ?? ''); ?>
                                                                    </div>
                                                                <?php endforeach; ?>
                                                            <?php else: ?>
                                                                <div class="empty-time">No AM punches</div>
                                                            <?php endif; ?>
                                                        </div>
                                                    </td>

                                                    <td>
                                                        <div class="breakdown-list">
                                                            <?php if (!empty($pmIntervals)): ?>
                                                                <?php foreach ($pmIntervals as $intv): ?>
                                                                    <div class="breakdown-line">
                                                                        <i class="mdi mdi-weather-night text-info"></i>
                                                                        <?= h($intv['label'] ?? ''); ?>
                                                                    </div>
                                                                <?php endforeach; ?>
                                                            <?php else: ?>
                                                                <div class="empty-time">No PM punches</div>
                                                            <?php endif; ?>
                                                        </div>
                                                    </td>

                                                    <td>
                                                        <div class="total-label"><?= h(format_duration($rowTotalSeconds)); ?></div>
                                                    </td>

                                                    <td>
                                                        <?php if ($isAbsent): ?>
                                                            <span class="status-pill status-absent">Absent</span>
                                                        <?php elseif ($isPending): ?>
                                                            <span class="status-pill status-pending">No Time Out Yet</span>
                                                        <?php else: ?>
                                                            <span class="status-pill status-present">Present</span>
                                                        <?php endif; ?>
                                                    </td>

                                                    <td class="task-cell">
                                                        <?php if ($selectedEmployee !== '' && $logDate !== ''): ?>
                                                            <div class="task-stack">
                                                                <a class="task-link" href="<?= base_url(); ?>Page/accomplishmentStaff?assignedPerson=<?= urlencode((string) $selectedEmployee); ?>&date=<?= urlencode((string) $logDate); ?>">
                                                                    <i class="mdi mdi-clipboard-text-outline"></i>
                                                                    View
                                                                </a>
                                                                <span class="task-count-pill"><?= number_format($taskCount); ?></span>
                                                            </div>
                                                        <?php else: ?>
                                                            <span class="muted-copy">No linked accomplishment</span>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="6" class="empty-state">No DTR records found for the selected month.</td>
                                            </tr>
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

    <div class="modal fade theme-modal" id="dtrFilterModal" tabindex="-1" role="dialog" aria-labelledby="dtrFilterModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form method="get" action="<?= base_url(); ?>Page/myDTR">
                    <div class="modal-header">
                        <h5 class="modal-title mb-0" id="dtrFilterModalLabel">Select Month</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>

                    <div class="modal-body">
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="monthSelect">Month</label>
                                <select id="monthSelect" name="month" class="custom-select" required>
                                    <?php foreach ($months as $num => $label): ?>
                                        <option value="<?= h($num); ?>" <?= ($selectedMonth === (int) $num) ? 'selected' : ''; ?>>
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
                        <a href="<?= base_url(); ?>Page/myDTR" class="btn btn-light">Reset to Current Month</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="mdi mdi-magnify mr-1"></i>
                            View
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</body>

</html>