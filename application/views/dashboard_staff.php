<?php
// Package 2 detection
$staffDashboardSettingsId = (int) $this->session->userdata('settingsID');
$staffDashboardEnabledFeatures = array();
$staffDashboardIsPackage2 = false;

if ($staffDashboardSettingsId > 0 && $this->db->table_exists('company_features')) {
    $staffDashboardFeatureRows = $this->db
        ->select('feature_key')
        ->from('company_features')
        ->where('settingsID', $staffDashboardSettingsId)
        ->where('is_enabled', 1)
        ->get()
        ->result();

    foreach ($staffDashboardFeatureRows as $staffDashboardFeatureRow) {
        $staffDashboardFeatureKey = trim((string) ($staffDashboardFeatureRow->feature_key ?? ''));
        if ($staffDashboardFeatureKey !== '') {
            $staffDashboardEnabledFeatures[] = $staffDashboardFeatureKey;
        }
    }

    $staffDashboardEnabledFeatures = array_values(array_unique($staffDashboardEnabledFeatures));
    
    // Check if company is on Package 2 (Task Management Suite)
    // Package 2 features: tasks, notes, calendar
    $staffDashboardPackage2Features = array('tasks', 'notes', 'calendar');
    $staffDashboardIsPackage2 = count($staffDashboardEnabledFeatures) === count($staffDashboardPackage2Features) && 
                               count(array_intersect($staffDashboardEnabledFeatures, $staffDashboardPackage2Features)) === count($staffDashboardPackage2Features);
}

$dueTodayRecords = isset($dueToday) ? $dueToday : array();
if ($dueTodayRecords instanceof Traversable) {
    $dueTodayRecords = iterator_to_array($dueTodayRecords, false);
}
$dueTodayRecords = is_array($dueTodayRecords) ? array_values($dueTodayRecords) : array();

$archivedTodayRecords = isset($archivedToday) ? $archivedToday : array();
if ($archivedTodayRecords instanceof Traversable) {
    $archivedTodayRecords = iterator_to_array($archivedTodayRecords, false);
}
$archivedTodayRecords = is_array($archivedTodayRecords) ? array_values($archivedTodayRecords) : array();

$accomplishedRows = isset($accomplishedSummary) ? $accomplishedSummary : array();
if ($accomplishedRows instanceof Traversable) {
    $accomplishedRows = iterator_to_array($accomplishedRows, false);
}
$accomplishedRows = is_array($accomplishedRows) ? array_values($accomplishedRows) : array();

$dueTodayCount      = count($dueTodayRecords);
$archivedTodayCount = count($archivedTodayRecords);
$leaderboardRows    = array_slice($accomplishedRows, 0, 10);
$topScoreValue      = !empty($leaderboardRows) && isset($leaderboardRows[0]->total)
    ? (int) $leaderboardRows[0]->total
    : 0;

$taskDueTodayValue   = isset($taskDueTodayCount) ? (int) $taskDueTodayCount : 0;
$taskDueSoonValue    = isset($taskDueSoonCount) ? (int) $taskDueSoonCount : 0;
$taskOverdueValue    = isset($taskOverdueCount) ? (int) $taskOverdueCount : 0;
$taskWithoutDueValue = isset($taskWithoutDueDateCount) ? (int) $taskWithoutDueDateCount : 0;
$taskDueWindowValue  = isset($taskDueWindowDays) ? (int) $taskDueWindowDays : 7;

$taskQueueRows = isset($taskDueQueue) ? $taskDueQueue : array();
if ($taskQueueRows instanceof Traversable) {
    $taskQueueRows = iterator_to_array($taskQueueRows, false);
}
$taskQueueRows = is_array($taskQueueRows) ? array_values($taskQueueRows) : array();
$forwardedTaskValue   = isset($forwardedTaskCount) ? (int) $forwardedTaskCount : 0;

$timeNotice           = trim((string) ($time_notice ?? ''));
$attendanceStatusMeta = $timeNotice !== '' ? $timeNotice : 'Your attendance record looks complete for today.';
$currentDateLabel     = date('l, F j, Y');
$currentMonthLabel    = date('F Y');
?>
<!DOCTYPE html>
<html lang="en">

<?php include('includes/head.php'); ?>
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css" rel="stylesheet">

<style>
    @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');

    .staff-dashboard-admin,
    .staff-dashboard-admin .content-page {
        font-family: 'Plus Jakarta Sans', sans-serif !important;
    }

    .staff-dashboard-admin {
        --primary: #2563eb;
        --primary-dark: #1d4ed8;
        --primary-light: #eff6ff;
        --teal: #0d9488;
        --teal-light: #f0fdfa;
        --amber: #d97706;
        --amber-light: #fffbeb;
        --red: #dc2626;
        --red-light: #fef2f2;
        --green: #16a34a;
        --green-light: #f0fdf4;
        --purple: #7c3aed;
        --purple-light: #f5f3ff;
        --surface: #ffffff;
        --surface-2: #f8fafc;
        --surface-3: #f1f5f9;
        --border: #e2e8f0;
        --border-strong: #cbd5e1;
        --text-1: #0f172a;
        --text-2: #475569;
        --text-3: #94a3b8;
        --shadow-sm: 0 1px 3px rgba(15, 23, 42, .06), 0 1px 2px rgba(15, 23, 42, .05);
        --shadow-md: 0 8px 24px rgba(15, 23, 42, .08), 0 2px 8px rgba(15, 23, 42, .05);
        --shadow-lg: 0 16px 36px rgba(15, 23, 42, .10), 0 8px 16px rgba(15, 23, 42, .06);
        --radius: 14px;
        --radius-lg: 18px;
        background: #f8fafc;
        min-height: 100vh;
        padding-bottom: 32px;
    }

    .staff-dashboard-admin .dashboard-wrap {
        padding: 0 0 2rem;
    }

    .staff-dashboard-admin .dash-header {
        display: flex;
        align-items: flex-end;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 14px;
        margin-bottom: 1.4rem;
        padding-bottom: 1.2rem;
        border-bottom: 1px solid var(--border);
    }

    .staff-dashboard-admin .dash-header-left .eyebrow {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        font-size: 0.72rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .12em;
        color: var(--text-3);
        margin-bottom: 7px;
    }

    .staff-dashboard-admin .dash-header-left .eyebrow::before {
        content: '';
        width: 7px;
        height: 7px;
        border-radius: 50%;
        background: var(--primary);
        display: inline-block;
    }

    .staff-dashboard-admin .dash-header-left h4 {
        margin: 0;
        font-size: 1.5rem;
        line-height: 1.2;
        font-weight: 800;
        letter-spacing: -.03em;
        color: var(--text-1);
    }

    .staff-dashboard-admin .dash-header-left p {
        margin: 4px 0 0;
        font-size: 0.84rem;
        color: var(--text-3);
    }

    .staff-dashboard-admin .header-actions {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }

    .staff-dashboard-admin .btn-clean,
    .staff-dashboard-admin .btn-solid {
        border-radius: 10px !important;
        font-size: 0.82rem !important;
        font-weight: 700 !important;
        padding: 10px 16px !important;
        display: inline-flex !important;
        align-items: center !important;
        gap: 7px !important;
        transition: all .18s ease !important;
        text-decoration: none !important;
        box-shadow: none;
    }

    .staff-dashboard-admin .btn-clean {
        background: #fff !important;
        color: var(--text-2) !important;
        border: 1px solid var(--border) !important;
    }

    .staff-dashboard-admin .btn-clean:hover {
        color: var(--text-1) !important;
        border-color: var(--border-strong) !important;
        transform: translateY(-1px);
        box-shadow: var(--shadow-sm);
    }

    .staff-dashboard-admin .btn-solid {
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%) !important;
        color: #fff !important;
        border: 1px solid transparent !important;
        box-shadow: 0 10px 24px rgba(37, 99, 235, .18) !important;
    }

    .staff-dashboard-admin .btn-solid:hover {
        transform: translateY(-1px);
        box-shadow: 0 14px 26px rgba(37, 99, 235, .24) !important;
    }

    .staff-dashboard-admin .kpi-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 12px;
        margin-bottom: 1.25rem;
    }

    .staff-dashboard-admin .kpi-card {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--radius);
        padding: 0.8rem 0.9rem;
        box-shadow: var(--shadow-sm);
        position: relative;
        overflow: hidden;
        transition: transform .18s ease, box-shadow .18s ease, border-color .18s ease;
        min-height: 0;
    }

    .staff-dashboard-admin .kpi-card:hover {
        transform: translateY(-3px);
        box-shadow: var(--shadow-md);
        border-color: transparent;
    }

    .staff-dashboard-admin .kpi-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 4px;
        height: 100%;
    }

    .staff-dashboard-admin .kpi-card--info::before {
        background: var(--primary);
    }

    .staff-dashboard-admin .kpi-card--warning::before {
        background: var(--amber);
    }

    .staff-dashboard-admin .kpi-card--danger::before {
        background: var(--red);
    }

    .staff-dashboard-admin .kpi-card--success::before {
        background: var(--green);
    }

    .staff-dashboard-admin .kpi-top {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 8px;
    }

    .staff-dashboard-admin .kpi-icon {
        width: 32px;
        height: 32px;
        border-radius: 9px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1rem;
    }

    .staff-dashboard-admin .kpi-card--info .kpi-icon {
        background: var(--primary-light);
        color: var(--primary);
    }

    .staff-dashboard-admin .kpi-card--warning .kpi-icon {
        background: var(--amber-light);
        color: var(--amber);
    }

    .staff-dashboard-admin .kpi-card--danger .kpi-icon {
        background: var(--red-light);
        color: var(--red);
    }

    .staff-dashboard-admin .kpi-card--success .kpi-icon {
        background: var(--green-light);
        color: var(--green);
    }

    .staff-dashboard-admin .kpi-label {
        font-size: 0.68rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .07em;
        color: var(--text-3);
        margin-bottom: 4px;
    }

    .staff-dashboard-admin .kpi-num {
        font-size: 1.4rem;
        line-height: 1;
        font-weight: 800;
        letter-spacing: -.03em;
        color: var(--text-1);
        margin-bottom: 4px;
    }

    .staff-dashboard-admin .kpi-meta {
        color: var(--text-2);
        font-size: 0.72rem;
        line-height: 1.4;
    }

    .staff-dashboard-admin .content-grid {
        display: grid;
        grid-template-columns: minmax(0, 1.45fr) minmax(320px, .95fr);
        gap: 18px;
        align-items: start;
    }

    .staff-dashboard-admin .panel {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow-sm);
        overflow: hidden;
        margin-bottom: 18px;
    }

    .staff-dashboard-admin .panel-header {
        padding: 1rem 1.25rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
    }

    .staff-dashboard-admin .panel-header--purple {
        background: linear-gradient(135deg, var(--purple) 0%, #5b21b6 100%);
        color: #fff;
    }

    .staff-dashboard-admin .panel-header--teal {
        background: linear-gradient(135deg, var(--teal) 0%, #0f766e 100%);
        color: #fff;
    }

    .staff-dashboard-admin .panel-header--blue {
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
        color: #fff;
    }

    .staff-dashboard-admin .panel-header--blue .panel-title {
        color: #fff;
    }

    .staff-dashboard-admin .panel-header--blue .btn-outline-info,
    .staff-dashboard-admin .panel-header--blue .btn-outline-secondary {
        color: #ffffff !important;
        border-color: rgba(255, 255, 255, 0.42) !important;
        background: rgba(255, 255, 255, 0.14) !important;
        box-shadow: none !important;
        text-shadow: 0 1px 6px rgba(15, 23, 42, 0.2);
    }

    .staff-dashboard-admin .panel-header--blue .btn-outline-info:hover,
    .staff-dashboard-admin .panel-header--blue .btn-outline-info:focus,
    .staff-dashboard-admin .panel-header--blue .btn-outline-secondary:hover,
    .staff-dashboard-admin .panel-header--blue .btn-outline-secondary:focus {
        color: #ffffff !important;
        border-color: rgba(255, 255, 255, 0.7) !important;
        background: rgba(255, 255, 255, 0.22) !important;
    }

    .staff-dashboard-admin .panel-title {
        margin: 0;
        font-size: 0.95rem;
        font-weight: 800;
        letter-spacing: .02em;
    }

    .staff-dashboard-admin .panel-subtitle {
        margin-top: 2px;
        font-size: 0.8rem;
        opacity: .88;
    }

    .staff-dashboard-admin .panel-body {
        padding: 1.2rem;
    }

    .staff-dashboard-admin .panel-toggle {
        width: 34px;
        height: 34px;
        border-radius: 50%;
        border: 1px solid rgba(255, 255, 255, .25);
        background: rgba(255, 255, 255, .12);
        color: #fff;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0;
        transition: all .2s ease;
    }

    .staff-dashboard-admin .panel-toggle:hover {
        background: rgba(255, 255, 255, .18);
    }

    .staff-dashboard-admin .panel-toggle.is-collapsed i {
        transform: rotate(-90deg);
    }

    .staff-dashboard-admin .panel-toggle i {
        transition: transform .2s ease;
    }

    .staff-dashboard-admin .panel-collapse-bar {
        display: none;
        flex-wrap: wrap;
        gap: 8px;
        padding: 1rem 1.2rem;
        background: #fafbff;
        border-top: 1px solid var(--border);
    }

    .staff-dashboard-admin .panel-collapse-bar.is-visible {
        display: flex;
    }

    .staff-dashboard-admin .pill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        border-radius: 999px;
        padding: 6px 12px;
        font-size: 0.78rem;
        font-weight: 700;
        border: 1px solid transparent;
    }

    .staff-dashboard-admin .pill-info {
        background: var(--primary-light);
        color: var(--primary);
        border-color: #bfdbfe;
    }

    .staff-dashboard-admin .pill-warn {
        background: var(--amber-light);
        color: var(--amber);
        border-color: #fde68a;
    }

    .staff-dashboard-admin .pill-success {
        background: var(--green-light);
        color: var(--green);
        border-color: #bbf7d0;
    }

    .staff-dashboard-admin .pill-purple {
        background: var(--purple-light);
        color: var(--purple);
        border-color: #ddd6fe;
    }

    .staff-dashboard-admin .panel-body.is-collapsed {
        display: none;
    }

    .staff-dashboard-admin .notice-list {
        display: grid;
        gap: 12px;
    }

    .staff-dashboard-admin .notice-card {
        border: 1px solid var(--border);
        border-radius: 14px;
        background: #fff;
        overflow: hidden;
        transition: transform .16s ease, box-shadow .16s ease, border-color .16s ease;
    }

    .staff-dashboard-admin .notice-card:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow-md);
    }

    .staff-dashboard-admin .notice-head {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 14px 16px;
        cursor: pointer;
    }

    .staff-dashboard-admin .notice-icon {
        width: 42px;
        height: 42px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        font-size: 1.1rem;
    }

    .staff-dashboard-admin .notice-info .notice-icon {
        background: var(--primary-light);
        color: var(--primary);
    }

    .staff-dashboard-admin .notice-purple .notice-icon {
        background: var(--purple-light);
        color: var(--purple);
    }

    .staff-dashboard-admin .notice-teal .notice-icon {
        background: var(--teal-light);
        color: var(--teal);
    }

    .staff-dashboard-admin .notice-success .notice-icon {
        background: var(--green-light);
        color: var(--green);
    }

    .staff-dashboard-admin .notice-head-text {
        min-width: 0;
        flex: 1;
    }

    .staff-dashboard-admin .notice-title {
        font-size: 0.95rem;
        font-weight: 800;
        color: var(--text-1);
        margin-bottom: 2px;
    }

    .staff-dashboard-admin .notice-summary {
        color: var(--text-3);
        font-size: 0.79rem;
    }

    .staff-dashboard-admin .notice-chevron {
        color: var(--text-3);
        font-size: 1.15rem;
    }

    .staff-dashboard-admin .notice-card.is-open .notice-chevron i {
        transform: rotate(180deg);
    }

    .staff-dashboard-admin .notice-chevron i {
        transition: transform .2s ease;
    }

    .staff-dashboard-admin .notice-body {
        border-top: 1px solid var(--border);
    }

    .staff-dashboard-admin .notice-body.is-hidden {
        display: none;
    }

    .staff-dashboard-admin .notice-body-inner {
        padding: 14px 16px 16px;
    }

    .staff-dashboard-admin .notice-copy {
        font-size: 0.84rem;
        line-height: 1.65;
        color: var(--text-2);
    }

    .staff-dashboard-admin .notice-copy.mt-2 {
        margin-top: 8px;
    }

    .staff-dashboard-admin .notice-items {
        margin: 10px 0 0;
        padding-left: 18px;
        color: var(--text-2);
        font-size: 0.83rem;
    }

    .staff-dashboard-admin .notice-items li+li {
        margin-top: 6px;
    }

    .staff-dashboard-admin .inline-link {
        color: var(--primary);
        font-weight: 700;
        text-decoration: none;
    }

    .staff-dashboard-admin .inline-link:hover {
        text-decoration: underline;
    }

    .staff-dashboard-admin .leaderboard-table {
        width: 100%;
        border-collapse: collapse;
    }

    .staff-dashboard-admin .leaderboard-table th {
        padding: .8rem 1rem;
        text-align: left;
        font-size: .75rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .07em;
        color: var(--text-3);
        border-bottom: 1px solid var(--border);
        background: var(--surface-2);
    }

    .staff-dashboard-admin .leaderboard-table td {
        padding: .9rem 1rem;
        font-size: .88rem;
        color: var(--text-2);
        border-bottom: 1px solid var(--border);
        vertical-align: middle;
    }

    .staff-dashboard-admin .leaderboard-table tr:last-child td {
        border-bottom: none;
    }

    .staff-dashboard-admin .leaderboard-table tbody tr:hover td {
        background: #fbfdff;
    }

    .staff-dashboard-admin .rank-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 50px;
        padding: 6px 10px;
        border-radius: 999px;
        font-size: .74rem;
        font-weight: 800;
        border: 1px solid var(--border);
        background: var(--surface-2);
        color: var(--text-2);
    }

    .staff-dashboard-admin .rank-1 {
        background: var(--green-light);
        color: var(--green);
        border-color: #bbf7d0;
    }

    .staff-dashboard-admin .rank-2 {
        background: var(--primary-light);
        color: var(--primary);
        border-color: #bfdbfe;
    }

    .staff-dashboard-admin .rank-3 {
        background: var(--amber-light);
        color: var(--amber);
        border-color: #fde68a;
    }

    .staff-dashboard-admin .leader-name {
        font-weight: 700;
        color: var(--text-1);
    }

    .staff-dashboard-admin .leader-points {
        text-align: right;
        font-weight: 800;
        color: var(--text-2);
    }

    .staff-dashboard-admin .leader-points.champion {
        color: var(--green);
    }

    .staff-dashboard-admin .summary-box {
        border: 1px solid var(--border);
        background: var(--surface-2);
        border-radius: 14px;
        padding: 14px 16px;
        margin-bottom: 12px;
    }

    .staff-dashboard-admin .summary-box:last-child {
        margin-bottom: 0;
    }

    .staff-dashboard-admin .summary-box-label {
        font-size: .76rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .08em;
        color: var(--text-3);
        margin-bottom: 7px;
    }

    .staff-dashboard-admin .summary-box-value {
        font-size: 1.6rem;
        line-height: 1;
        font-weight: 800;
        color: var(--text-1);
        margin-bottom: 7px;
    }

    .staff-dashboard-admin .summary-box-text {
        color: var(--text-2);
        font-size: .82rem;
        line-height: 1.55;
    }

    .staff-dashboard-admin .empty-state {
        border: 1px dashed var(--border-strong);
        background: var(--surface-2);
        color: var(--text-3);
        border-radius: 14px;
        padding: 1rem;
        text-align: center;
        font-size: .84rem;
    }

    .staff-dashboard-admin .alert {
        border: none;
        border-radius: 12px;
        box-shadow: var(--shadow-sm);
    }

    /* Calendar styles for Package 2 */
    .staff-dashboard-admin .calendar-container {
        padding: 1.2rem;
    }

    .staff-dashboard-admin #staffCalendar {
        min-height: 500px;
    }

    .staff-dashboard-admin .fc .fc-toolbar-title {
        font-weight: 800;
        letter-spacing: .02em;
        color: var(--text-1);
    }

    .staff-dashboard-admin .fc .fc-button {
        border-radius: 10px;
        font-weight: 700;
        font-size: 0.82rem;
    }

    .staff-dashboard-admin .fc .fc-daygrid-day-number,
    .staff-dashboard-admin .fc .fc-timegrid-slot-label-cushion,
    .staff-dashboard-admin .fc .fc-col-header-cell-cushion {
        font-weight: 700;
        color: var(--text-2);
    }

    .staff-dashboard-admin .fc .fc-daygrid-event,
    .staff-dashboard-admin .fc .fc-timegrid-event {
        border-radius: 8px;
        cursor: pointer;
        border-width: 0;
        box-shadow: var(--shadow-sm);
        white-space: normal !important;
        align-items: flex-start;
    }

    .staff-dashboard-admin .fc .fc-daygrid-event .fc-event-main,
    .staff-dashboard-admin .fc .fc-timegrid-event .fc-event-main,
    .staff-dashboard-admin .fc .fc-daygrid-event .fc-event-main-frame,
    .staff-dashboard-admin .fc .fc-daygrid-event .fc-event-title-container,
    .staff-dashboard-admin .fc .fc-daygrid-event .fc-event-title,
    .staff-dashboard-admin .fc .fc-timegrid-event .fc-event-title {
        white-space: normal !important;
        overflow: visible !important;
        text-overflow: clip !important;
        word-break: break-word;
        overflow-wrap: anywhere;
    }

    .staff-dashboard-admin .evt-title {
        font-weight: 800;
        font-size: 0.75rem;
        line-height: 1.2;
        white-space: normal;
        word-break: break-word;
        overflow-wrap: anywhere;
    }

    .staff-dashboard-admin .evt-desc {
        font-size: 0.7rem;
        opacity: .9;
        line-height: 1.2;
        margin-top: 2px;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    @media (max-width: 1199px) {
        .staff-dashboard-admin .content-grid {
            grid-template-columns: 1fr;
        }

        .staff-dashboard-admin .kpi-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 767px) {
        .staff-dashboard-admin .dashboard-wrap {
            padding-top: 24px;
        }

        .staff-dashboard-admin .dash-header {
            align-items: flex-start;
        }

        .staff-dashboard-admin .kpi-grid {
            grid-template-columns: 1fr;
        }

        .staff-dashboard-admin .panel-header,
        .staff-dashboard-admin .panel-body {
            padding-left: 1rem;
            padding-right: 1rem;
        }
    }

    /* Sticky Notes Widget */
    .sticky-notes-widget {
        position: fixed;
        bottom: 20px;
        right: 20px;
        z-index: 9999;
        font-family: 'Plus Jakarta Sans', sans-serif;
    }

    .sticky-notes-toggle {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border: none;
        box-shadow: 0 4px 20px rgba(102, 126, 234, 0.4);
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s ease;
    }

    .sticky-notes-toggle:hover {
        transform: scale(1.1);
        box-shadow: 0 6px 25px rgba(102, 126, 234, 0.5);
    }

    .sticky-notes-toggle i {
        font-size: 28px;
        color: white;
    }

    .sticky-notes-panel {
        position: absolute;
        bottom: 80px;
        right: 0;
        width: 320px;
        background: white;
        border-radius: 16px;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
        overflow: hidden;
        display: none;
        animation: slideUp 0.3s ease;
    }

    .sticky-notes-panel.is-visible {
        display: block;
    }

    @keyframes slideUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .sticky-notes-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        padding: 16px;
        color: #ffffff;
        display: flex;
        align-items: center;
        justify-content: space-between;
        text-shadow: 0 1px 2px rgba(0, 0, 0, 0.2);
    }

    .sticky-notes-header h6 {
        margin: 0;
        font-size: 0.95rem;
        font-weight: 700;
        color: #ffffff;
    }

    .sticky-notes-close {
        background: rgba(255, 255, 255, 0.2);
        border: none;
        color: white;
        width: 28px;
        height: 28px;
        border-radius: 50%;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: background 0.2s ease;
    }

    .sticky-notes-close:hover {
        background: rgba(255, 255, 255, 0.3);
    }

    .sticky-notes-body {
        padding: 16px;
    }

    .sticky-notes-textarea {
        width: 100%;
        min-height: 150px;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 12px;
        font-size: 0.9rem;
        font-family: 'Plus Jakarta Sans', sans-serif;
        resize: vertical;
        outline: none;
        transition: border-color 0.2s ease;
    }

    .sticky-notes-textarea:focus {
        border-color: #667eea;
    }

    .sticky-notes-footer {
        padding: 12px 16px;
        border-top: 1px solid #e2e8f0;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .sticky-notes-save {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border: none;
        padding: 8px 16px;
        border-radius: 8px;
        font-size: 0.85rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .sticky-notes-save:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
    }

    .sticky-notes-clear {
        background: transparent;
        color: #64748b;
        border: none;
        padding: 8px 12px;
        border-radius: 8px;
        font-size: 0.85rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .sticky-notes-clear:hover {
        background: #f1f5f9;
        color: #475569;
    }

    .sticky-notes-saved {
        font-size: 0.75rem;
        color: #16a34a;
        font-weight: 600;
    }
</style>

<body>
    <div id="wrapper" class="staff-dashboard-admin">

        <?php include('includes/top-nav-bar.php'); ?>
        <?php include('includes/sidebar.php'); ?>

        <div class="content-page">
            <div class="content">
                <div class="container-fluid dashboard-wrap">

                    <?php if ($this->session->flashdata('success')): ?>
                        <div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
                            <?= htmlspecialchars((string) $this->session->flashdata('success'), ENT_QUOTES, 'UTF-8'); ?>
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        </div>
                    <?php endif; ?>

                    <?php if ($this->session->flashdata('danger')): ?>
                        <div class="alert alert-danger alert-dismissible fade show mt-3" role="alert">
                            <?= htmlspecialchars((string) $this->session->flashdata('danger'), ENT_QUOTES, 'UTF-8'); ?>
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        </div>
                    <?php endif; ?>

                    <div class="dash-header">
                        <div class="dash-header-left">
                            <div class="eyebrow">Staff Overview</div>
                            <h4>Staff Dashboard</h4>
                        </div>
                        <div class="header-actions">
                            <?php if (!$staffDashboardIsPackage2): ?>
                            <a href="<?= base_url(); ?>Page/myDTR" class="btn btn-clean">
                                <i class="mdi mdi-clock-time-four-outline"></i>
                                My DTR
                            </a>
                            <a href="<?= base_url(); ?>Page/supportIssues?scope=unassigned" class="btn btn-clean">
                                <i class="mdi mdi-lifebuoy"></i>
                                Unassigned
                                <span class="badge badge-pill badge-danger ml-2"><?= number_format((int) ($unassignedTicketCount ?? 0)); ?></span>
                            </a>
                            <a href="<?= base_url(); ?>Page/projectAddTask?status=open&amp;scope=forwarded" class="btn btn-clean">
                                <i class="mdi mdi-share-all-outline"></i>
                                Forwarded Tasks
                                <span class="badge badge-pill badge-danger ml-2"><?= number_format($forwardedTaskValue); ?></span>
                            </a>
                            <?php endif; ?>
                            <a href="<?= base_url(); ?>Page/projectAddTask" class="btn btn-solid">
                                <i class="mdi mdi-format-list-checks"></i>
                                Task List
                            </a>
                        </div>
                    </div>

                    <div class="kpi-grid">
                        <?php if (!$staffDashboardIsPackage2): ?>
                        <div class="kpi-card kpi-card--info">
                            <div class="kpi-top">
                                <div class="kpi-icon"><i class="mdi mdi-bell-ring-outline"></i></div>
                            </div>
                            <div class="kpi-label">Due Today</div>
                            <div class="kpi-num"><?= number_format($dueTodayCount); ?></div>
                            <!-- <div class="kpi-meta">Reminders scheduled for <?= htmlspecialchars($currentDateLabel, ENT_QUOTES, 'UTF-8'); ?>.</div> -->
                        </div>
                        <?php endif; ?>

                        <div class="kpi-card kpi-card--warning">
                            <div class="kpi-top">
                                <div class="kpi-icon"><i class="mdi mdi-calendar-clock"></i></div>
                            </div>
                            <div class="kpi-label">Tasks Due Today</div>
                            <div class="kpi-num"><?= number_format($taskDueTodayValue); ?></div>
                            <!-- <div class="kpi-meta">Open tasks to complete or update today.</div> -->
                        </div>

                        <div class="kpi-card kpi-card--danger">
                            <div class="kpi-top">
                                <div class="kpi-icon"><i class="mdi mdi-alert-circle-outline"></i></div>
                            </div>
                            <div class="kpi-label">Overdue Tasks</div>
                            <div class="kpi-num"><?= number_format($taskOverdueValue); ?></div>
                            <!-- <div class="kpi-meta">Tasks whose due date has already passed.</div> -->
                        </div>

                        <div class="kpi-card kpi-card--success">
                            <div class="kpi-top">
                                <div class="kpi-icon"><i class="mdi mdi-trophy-outline"></i></div>
                            </div>
                            <div class="kpi-label">Top Monthly Score</div>
                            <div class="kpi-num"><?= number_format($topScoreValue); ?></div>
                            <!-- <div class="kpi-meta">Highest accomplishment total for <?= htmlspecialchars($currentMonthLabel, ENT_QUOTES, 'UTF-8'); ?>.</div> -->
                        </div>
                    </div>

                    <div class="content-grid" style="grid-template-columns: 1fr;">
                        <div>
                            <?php if ($staffDashboardIsPackage2): ?>
                            <!-- Calendar for Package 2 -->
                            <div class="panel">
                                <div class="panel-header panel-header--blue">
                                    <div>
                                        <h5 class="panel-title">Calendar</h5>
                                        <div class="panel-subtitle">Manage your schedule and events</div>
                                    </div>
                                    <div class="d-flex gap-2">
                                        <a class="btn btn-outline-info btn-sm" href="<?= base_url('Calendar/completion_stats'); ?>">
                                            <i class="mdi mdi-chart-bar mr-1"></i> Completion Stats
                                        </a>
                                        <a class="btn btn-outline-secondary btn-sm" href="<?= base_url('Calendar/print_all'); ?>" target="_blank">
                                            <i class="mdi mdi-printer mr-1"></i> Print All
                                        </a>
                                    </div>
                                </div>
                                <div class="panel-body calendar-container">
                                    <div id="staffCalendar"></div>
                                </div>
                            </div>
                            <?php else: ?>
                            <!-- Accomplishment Summary for non-Package 2 -->
                            <div class="panel">
                                <div class="panel-header panel-header--blue">
                                    <div>
                                        <h5 class="panel-title">Accomplishment Summary</h5>
                                        <div class="panel-subtitle">Top completed task totals for <?= htmlspecialchars($currentMonthLabel, ENT_QUOTES, 'UTF-8'); ?>.</div>
                                    </div>
                                </div>
                                <div class="panel-body p-0">
                                    <?php if (!empty($leaderboardRows)): ?>
                                        <div class="table-responsive">
                                            <table class="leaderboard-table">
                                                <thead>
                                                    <tr>
                                                        <th style="width:140px;padding-left:20px;">Rank</th>
                                                        <th style="padding-left:40px;">Assigned Person</th>
                                                        <th style="text-align:right;padding-right:20px;">Total Points</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php
                                                    $rank = 1;
                                                    $previousTotal = null;
                                                    foreach ($leaderboardRows as $index => $row):
                                                        $totalDone = (int) ($row->total ?? 0);
                                                        if ($previousTotal !== null && $totalDone !== $previousTotal) {
                                                            $rank = $index + 1;
                                                        }
                                                        $previousTotal = $totalDone;
                                                        $suffix = 'th';
                                                        if (!in_array($rank % 100, array(11, 12, 13), true)) {
                                                            switch ($rank % 10) {
                                                                case 1:
                                                                    $suffix = 'st';
                                                                    break;
                                                                case 2:
                                                                    $suffix = 'nd';
                                                                    break;
                                                                case 3:
                                                                    $suffix = 'rd';
                                                                    break;
                                                            }
                                                        }
                                                        $fullName = trim((string) (($row->lName ?? '') . ', ' . ($row->fName ?? '')));
                                                        if ($fullName === ',' || $fullName === '') {
                                                            $fullName = trim((string) ($row->username ?? 'Unassigned'));
                                                        }
                                                        $rankClass = $rank <= 3 ? 'rank-' . $rank : '';
                                                    ?>
                                                        <tr>
                                                            <td style="padding-left:20px;">
                                                                <span class="rank-badge <?= htmlspecialchars($rankClass, ENT_QUOTES, 'UTF-8'); ?>">
                                                                    <?= htmlspecialchars($rank . $suffix, ENT_QUOTES, 'UTF-8'); ?>
                                                                </span>
                                                            </td>
                                                            <td class="leader-name" style="padding-left:40px;"><?= htmlspecialchars($fullName, ENT_QUOTES, 'UTF-8'); ?></td>
                                                            <td class="leader-points<?= $index === 0 ? ' champion' : ''; ?>" style="padding-right:20px;"><?= number_format($totalDone); ?></td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    <?php else: ?>
                                        <div class="panel-body">
                                            <div class="empty-state">No accomplished tasks found for this month yet.</div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <?php include('includes/footer.php'); ?>
        </div>
    </div>

    <!-- Sticky Notes Widget -->
    <div class="sticky-notes-widget">
        <div class="sticky-notes-panel" id="stickyNotesPanel">
            <div class="sticky-notes-header">
                <h6><i class="mdi mdi-notebook mr-2"></i>Quick Notes</h6>
                <button class="sticky-notes-close" onclick="toggleStickyNotes()">
                    <i class="mdi mdi-close"></i>
                </button>
            </div>
            <div class="sticky-notes-body">
                <textarea class="sticky-notes-textarea" id="stickyNotesTextarea" placeholder="Type your important notes here..."></textarea>
            </div>
            <div class="sticky-notes-footer">
                <button class="sticky-notes-clear" onclick="clearStickyNotes()">Clear</button>
                <div>
                    <span class="sticky-notes-saved" id="stickyNotesSaved" style="display: none;">Saved!</span>
                    <button class="sticky-notes-save" onclick="saveStickyNotes()">Save</button>
                </div>
            </div>
        </div>
        <button class="sticky-notes-toggle" onclick="toggleStickyNotes()" title="Quick Notes">
            <i class="mdi mdi-notebook"></i>
        </button>
    </div>

    <?php include('includes/themecustomizer.php'); ?>

    <script src="<?= base_url(); ?>assets/js/vendor.min.js"></script>
    <script src="<?= base_url(); ?>assets/js/app.min.js"></script>
    <script src="<?= base_url(); ?>assets/libs/sweetalert2/sweetalert2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>

    <script>
        // Sticky Notes Widget Functionality
        function toggleStickyNotes() {
            const panel = document.getElementById('stickyNotesPanel');
            panel.classList.toggle('is-visible');
        }

        function saveStickyNotes() {
            const textarea = document.getElementById('stickyNotesTextarea');
            const notes = textarea.value;
            localStorage.setItem('staffStickyNotes', notes);
            
            const savedIndicator = document.getElementById('stickyNotesSaved');
            savedIndicator.style.display = 'inline';
            setTimeout(() => {
                savedIndicator.style.display = 'none';
            }, 2000);
        }

        function clearStickyNotes() {
            const textarea = document.getElementById('stickyNotesTextarea');
            textarea.value = '';
            localStorage.removeItem('staffStickyNotes');
        }

        // Load saved notes on page load
        document.addEventListener('DOMContentLoaded', function() {
            const savedNotes = localStorage.getItem('staffStickyNotes');
            if (savedNotes) {
                const textarea = document.getElementById('stickyNotesTextarea');
                textarea.value = savedNotes;
            }

            // Show task reminder alert
            const taskDueToday = <?= $taskDueTodayValue; ?>;
            const taskOverdue = <?= $taskOverdueValue; ?>;
            const overdueTasks = <?= json_encode(isset($overdueTasks) ? $overdueTasks : array()); ?>;
            const dueTodayTasks = <?= json_encode(isset($dueTodayTasks) ? $dueTodayTasks : array()); ?>;
            
            if (taskOverdue > 0 || taskDueToday > 0) {
                let message = '';
                let icon = '';
                let title = '';
                let taskDetails = '';
                
                function getRandomTask(tasks) {
                    if (!tasks || tasks.length === 0) return null;
                    const randomIndex = Math.floor(Math.random() * tasks.length);
                    return tasks[randomIndex];
                }
                
                function formatTaskDetails(task) {
                    if (!task) return '';
                    const taskName = task.task || task.taskID || 'No task name';
                    const dueDate = task.dueDate ? new Date(task.dueDate).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }) : 'No due date';
                    const priority = task.priority || 'Normal';
                    const reportedDate = task.reportedDate ? new Date(task.reportedDate).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }) : 'No date';
                    return `<div style="background: #f8f9fa; padding: 12px; border-radius: 8px; margin-top: 10px; border-left: 4px solid #667eea;">
                        <div style="font-weight: 700; color: #333; margin-bottom: 4px; font-size: 0.95rem;">${taskName}</div>
                        <div style="font-size: 0.85rem; color: #666;">Priority: ${priority}</div>
                        <div style="font-size: 0.85rem; color: #666;">Reported: ${reportedDate}</div>
                        <div style="font-size: 0.85rem; color: #666;">Due: ${dueDate}</div>
                    </div>`;
                }
                
                if (taskOverdue > 0 && taskDueToday > 0) {
                    title = 'Task Reminders';
                    const randomOverdue = getRandomTask(overdueTasks);
                    const randomDueToday = getRandomTask(dueTodayTasks);
                    message = `You have <strong>${taskOverdue} overdue task(s)</strong> and <strong>${taskDueToday} task(s) due today</strong>. Please prioritize these tasks.`;
                    if (randomOverdue) {
                        message += formatTaskDetails(randomOverdue);
                    }
                    icon = 'warning';
                } else if (taskOverdue > 0) {
                    title = 'Overdue Tasks';
                    const randomTask = getRandomTask(overdueTasks);
                    message = `You have <strong>${taskOverdue} overdue task(s)</strong>. Please address them as soon as possible.`;
                    if (randomTask) {
                        message += formatTaskDetails(randomTask);
                    }
                    icon = 'error';
                } else if (taskDueToday > 0) {
                    title = 'Tasks Due Today';
                    const randomTask = getRandomTask(dueTodayTasks);
                    message = `You have <strong>${taskDueToday} task(s) due today</strong>. Make sure to complete them on time.`;
                    if (randomTask) {
                        message += formatTaskDetails(randomTask);
                    }
                    icon = 'info';
                }
                
                Swal.fire({
                    title: title,
                    html: message,
                    icon: icon,
                    timer: 12000,
                    timerProgressBar: true,
                    showConfirmButton: true,
                    confirmButtonText: 'Got it',
                    confirmButtonColor: '#667eea',
                    backdrop: `rgba(0,0,0,0.4)`
                });
            }
        });

        // Auto-save notes every 30 seconds
        setInterval(function() {
            const textarea = document.getElementById('stickyNotesTextarea');
            if (textarea.value) {
                localStorage.setItem('staffStickyNotes', textarea.value);
            }
        }, 30000);
    </script>
    <?php if ($staffDashboardIsPackage2): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const calendarEl = document.getElementById('staffCalendar');
            if (!calendarEl) return;

            function pad(value) {
                return String(value).padStart(2, '0');
            }

            function formatDateTimeLocal(date) {
                return [
                    date.getFullYear(),
                    '-',
                    pad(date.getMonth() + 1),
                    '-',
                    pad(date.getDate()),
                    'T',
                    pad(date.getHours()),
                    ':',
                    pad(date.getMinutes())
                ].join('');
            }

            function esc(s) {
                return $('<div>').text(s || '').html();
            }

            const calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,timeGridDay'
                },
                selectable: true,
                selectMirror: true,
                editable: true,
                nowIndicator: true,
                slotMinTime: '06:00:00',
                slotMaxTime: '22:00:00',
                slotDuration: '00:30:00',
                allDaySlot: true,
                height: 'auto',
                events: '<?= site_url("calendar/get_events") ?>',

                eventDataTransform(raw) {
                    const color = raw.color || '#dc3545';
                    raw.extendedProps = raw.extendedProps || {};
                    raw.extendedProps.description = raw.description || '';
                    raw.extendedProps.notes = raw.notes || '';
                    raw.extendedProps.status = raw.status || 'private';
                    raw.extendedProps.color = color;
                    raw.extendedProps.location = raw.location || '';
                    raw.extendedProps.reminder_email_enabled = !!raw.reminder_email_enabled;
                    raw.extendedProps.reminder_email = raw.reminder_email || '';
                    raw.extendedProps.own = !!raw.own;
                    raw.extendedProps.is_completed = parseInt(raw.is_completed, 10) || 1;
                    const canEdit = raw.extendedProps.canEdit || false;
                    raw.extendedProps.editable = canEdit;
                    raw.editable = canEdit;
                    raw.startEditable = canEdit;
                    raw.durationEditable = canEdit;
                    raw.backgroundColor = color;
                    raw.borderColor = color;
                    raw.textColor = '#fff';
                    
                    if (raw.allDay && raw.end) {
                        const endDate = new Date(raw.end);
                        endDate.setDate(endDate.getDate() + 1);
                        raw.end = endDate.toISOString();
                    }
                    
                    if (raw.extendedProps.is_completed == 0) {
                        raw.backgroundColor = adjustColorOpacity(color, 0.4);
                        raw.borderColor = adjustColorOpacity(color, 0.4);
                    }
                    
                    return raw;
                },

                eventDidMount(info) {
                    const c = info.event.extendedProps.color || '#dc3545';
                    const isCompleted = info.event.extendedProps.is_completed;
                    
                    if (isCompleted == 0) {
                        info.el.style.backgroundColor = adjustColorOpacity(c, 0.4);
                        info.el.style.borderColor = adjustColorOpacity(c, 0.4);
                        info.el.style.opacity = '0.6';
                    } else {
                        info.el.style.backgroundColor = c;
                        info.el.style.borderColor = c;
                    }
                    info.el.style.color = '#fff';
                },

                eventContent(arg) {
                    const isCompleted = arg.event.extendedProps.is_completed;
                    const isOwn = arg.event.extendedProps.own;
                    const canComplete = arg.event.extendedProps.canComplete || false;
                    const checkIcon = (isCompleted == 0) ? '<i class="mdi mdi-check-circle" style="margin-right:4px;"></i>' : '';
                    const toggleBtn = (isOwn || canComplete) ? '<span class="evt-toggle-complete" data-event-id="' + arg.event.id + '" style="cursor:pointer; margin-left:4px; opacity:0.7;"><i class="mdi mdi-check-circle-outline"></i></span>' : '';
                    return {
                        html: '<div class="evt-title">' + checkIcon + esc(arg.event.title) + toggleBtn + '</div>' +
                            (arg.event.extendedProps.description ?
                                '<div class="evt-desc">' + esc(arg.event.extendedProps.description) + '</div>' :
                                '')
                    };
                },

                select(info) {
                    window.location.href = '<?= base_url('Calendar'); ?>';
                },

                dateClick(info) {
                    if (calendar.view.type === 'dayGridMonth') {
                        window.location.href = '<?= base_url('Calendar'); ?>';
                    }
                },

                eventClick(info) {
                    window.location.href = '<?= base_url('Calendar'); ?>';
                },

                eventDrop(info) {
                    if (!info.event.extendedProps.canEdit) {
                        info.revert();
                        return;
                    }

                    let endDate = info.event.end || info.event.start;
                    if (info.event.allDay && info.event.end) {
                        endDate = new Date(info.event.end.getTime() - 86400000);
                    }

                    const payload = {
                        event_id: info.event.id,
                        title: info.event.title,
                        description: info.event.extendedProps.description || '',
                        notes: info.event.extendedProps.notes || '',
                        start_date: formatDateTimeLocal(info.event.start),
                        end_date: formatDateTimeLocal(endDate),
                        all_day: info.event.allDay ? 1 : 0,
                        event_type: 'default',
                        color: info.event.extendedProps.color || '#dc3545',
                        location: info.event.extendedProps.location || '',
                        reminder_email_enabled: info.event.extendedProps.reminder_email_enabled ? 1 : 0,
                        reminder_email: info.event.extendedProps.reminder_email || '',
                        is_public: info.event.extendedProps.status === 'public' ? 1 : 0,
                        is_completed: parseInt(info.event.extendedProps.is_completed, 10) || 1
                    };

                    $.post('<?= site_url("calendar/update_event") ?>', payload, function(res) {
                        if (!res.success) {
                            alert(res.message || 'Unable to update event.');
                            info.revert();
                        }
                    }, 'json').fail(function() {
                        alert('Unable to update event.');
                        info.revert();
                    });
                },

                eventResize(info) {
                    if (!info.event.extendedProps.canEdit) {
                        info.revert();
                        return;
                    }

                    let endDate = info.event.end || info.event.start;
                    if (info.event.allDay && info.event.end) {
                        endDate = new Date(info.event.end.getTime() - 86400000);
                    }

                    const payload = {
                        event_id: info.event.id,
                        title: info.event.title,
                        description: info.event.extendedProps.description || '',
                        notes: info.event.extendedProps.notes || '',
                        start_date: formatDateTimeLocal(info.event.start),
                        end_date: formatDateTimeLocal(endDate),
                        all_day: info.event.allDay ? 1 : 0,
                        event_type: 'default',
                        color: info.event.extendedProps.color || '#dc3545',
                        location: info.event.extendedProps.location || '',
                        reminder_email_enabled: info.event.extendedProps.reminder_email_enabled ? 1 : 0,
                        reminder_email: info.event.extendedProps.reminder_email || '',
                        is_public: info.event.extendedProps.status === 'public' ? 1 : 0,
                        is_completed: parseInt(info.event.extendedProps.is_completed, 10) || 1
                    };

                    $.post('<?= site_url("calendar/update_event") ?>', payload, function(res) {
                        if (!res.success) {
                            alert(res.message || 'Unable to resize event.');
                            info.revert();
                        }
                    }, 'json').fail(function() {
                        alert('Unable to resize event.');
                        info.revert();
                    });
                }
            });

            function adjustColorOpacity(hex, opacity) {
                const r = parseInt(hex.slice(1, 3), 16);
                const g = parseInt(hex.slice(3, 5), 16);
                const b = parseInt(hex.slice(5, 7), 16);
                return `rgba(${r}, ${g}, ${b}, ${opacity})`;
            }

            calendar.render();

            // Quick toggle completion status
            $(document).on('click', '.evt-toggle-complete', function(e) {
                e.stopPropagation();
                const eventId = $(this).data('event-id');
                const event = calendar.getEventById(eventId);
                if (!event) {
                    console.log('Toggle complete: Event not found', eventId);
                    return;
                }

                const canComplete = event.extendedProps.canComplete || false;
                const isOwn = event.extendedProps.own || false;
                
                if (!isOwn && !canComplete) {
                    console.log('Toggle complete: No permission');
                    return;
                }

                const newStatus = event.extendedProps.is_completed == 0 ? 1 : 0;

                $.post('<?= site_url("calendar/toggle_completion") ?>', {
                    event_id: eventId,
                    is_completed: newStatus
                }, function(response) {
                    if (response.success) {
                        calendar.refetchEvents();
                    } else {
                        alert(response.message || 'Error updating completion status');
                    }
                }, 'json').fail(function() {
                    alert('Error updating completion status');
                });
            });
        });
    </script>
    <?php endif; ?>
</body>

</html>
