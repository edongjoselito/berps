<?php
$todayIncomeValue = (!empty($data) && isset($data[0]->Total) && $data[0]->Total !== null)
    ? (float) $data[0]->Total
    : 0.0;
$todayExpensesValue = (!empty($data1) && isset($data1[0]->Total) && $data1[0]->Total !== null)
    ? (float) $data1[0]->Total
    : 0.0;
$receivablesValue = (!empty($data4) && isset($data4[0]->Counts) && $data4[0]->Counts !== null)
    ? (float) $data4[0]->Counts
    : 0.0;
$totalClientsValue = (!empty($data3) && isset($data3[0]->Total) && $data3[0]->Total !== null)
    ? (int) $data3[0]->Total
    : 0;
$netTodayValue = $todayIncomeValue - $todayExpensesValue;
$currentDateLabel = date('l, F j, Y');
$currentMonthLabel = date('F Y');
$taskChampions = !empty($accomplishedSummary) && is_array($accomplishedSummary) ? $accomplishedSummary : array();
$leaderboardRows = array_slice($taskChampions, 0, 10);
$taskDueTodayValue = isset($taskDueTodayCount) ? (int) $taskDueTodayCount : 0;
$taskDueSoonValue = isset($taskDueSoonCount) ? (int) $taskDueSoonCount : 0;
$taskOverdueValue = isset($taskOverdueCount) ? (int) $taskOverdueCount : 0;
$taskWithoutDueValue = isset($taskWithoutDueDateCount) ? (int) $taskWithoutDueDateCount : 0;
$taskDueWindowValue = isset($taskDueWindowDays) ? (int) $taskDueWindowDays : 7;
$taskQueueRows = isset($taskDueQueue) ? $taskDueQueue : array();
if ($taskQueueRows instanceof Traversable) {
    $taskQueueRows = iterator_to_array($taskQueueRows, false);
}
$taskQueueRows = is_array($taskQueueRows) ? array_values($taskQueueRows) : array();
$unassignedTicketValue = isset($unassignedTicketCount) ? (int) $unassignedTicketCount : 0;
$quickActions = array(
    array(
        'label' => 'Invoice List',
        'meta' => 'Review invoices and encode new billings.',
        'icon' => 'mdi-file-document-edit-outline',
        'url' => base_url() . 'Page/invList',
    ),
    array(
        'label' => 'Job Orders',
        'meta' => 'Track active work orders and receivables.',
        'icon' => 'mdi-clipboard-text-outline',
        'url' => base_url() . 'Page/joList',
    ),
    array(
        'label' => 'Payments',
        'meta' => 'Monitor collections and posted credits.',
        'icon' => 'mdi-credit-card-outline',
        'url' => base_url() . 'Page/paymentList',
    ),
    array(
        'label' => 'Recurring Invoices',
        'meta' => 'Check templates and generator readiness.',
        'icon' => 'mdi-autorenew',
        'url' => base_url() . 'Page/recurringInvoices',
    ),
    array(
        'label' => 'Clients',
        'meta' => 'Manage company profiles and portal access.',
        'icon' => 'mdi-account-group-outline',
        'url' => base_url() . 'Page/clientList',
    ),
    array(
        'label' => 'Knowledge Base',
        'meta' => 'Manage articles and FAQs for staff and clients.',
        'icon' => 'mdi-book-open-page-variant',
        'url' => base_url() . 'Page/knowledgeBase',
    ),
    array(
        'label' => 'Status Report',
        'meta' => 'View paid, unpaid, overdue, and draft invoices.',
        'icon' => 'mdi-chart-bar',
        'url' => base_url() . 'Page/invoiceStatusReport',
    ),
);
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
                <div class="container-fluid admin-dashboard-page">

                    <style>
                        @import url('https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,100..1000;1,9..40,100..1000&display=swap');

                        .admin-dashboard-page {
                            --bg: #f5f7fb;
                            --surface: rgba(255, 255, 255, 0.96);
                            --surface-strong: #ffffff;
                            --surface-soft: #f8fbff;
                            --line: #e4ebf4;
                            --line-strong: #cfdbea;
                            --text: #142235;
                            --text-soft: #617489;
                            --text-faint: #8ea0b5;
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
                            --purple: #7c3aed;
                            --purple-soft: #f5f3ff;
                            --shadow: 0 16px 40px rgba(15, 23, 42, 0.06);
                            --shadow-soft: 0 8px 24px rgba(15, 23, 42, 0.04);
                            --radius-xl: 16px;
                            --radius-lg: 12px;
                            --radius-md: 10px;
                            --radius-sm: 8px;
                            --font-body: 'DM Sans', 'Segoe UI', Arial, sans-serif;
                            --font-head: 'DM Sans', 'Segoe UI', Arial, sans-serif;
                            --font-mono: 'DM Sans', 'SFMono-Regular', Consolas, monospace;
                            background:
                                radial-gradient(circle at top left, rgba(37, 99, 235, 0.08), transparent 28%),
                                radial-gradient(circle at top right, rgba(16, 185, 129, 0.08), transparent 24%),
                                linear-gradient(180deg, #f8fbff 0%, #f4f7fb 100%);
                            min-height: 100vh;
                            padding-bottom: 80px;
                            font-family: var(--font-body);
                        }

                        .admin-dashboard-page * {
                            box-sizing: border-box;
                        }

                        .admin-dashboard-page .alert {
                            border: none;
                            border-radius: 16px;
                            box-shadow: var(--shadow-soft);
                        }

                        .admin-dashboard-page .page-header {
                            display: flex;
                            justify-content: space-between;
                            align-items: flex-end;
                            gap: 18px;
                            margin: 24px 0 22px;
                            flex-wrap: wrap;
                        }

                        .admin-dashboard-page .page-eyebrow {
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

                        .admin-dashboard-page .page-eyebrow::before {
                            content: '';
                            width: 8px;
                            height: 8px;
                            border-radius: 50%;
                            background: linear-gradient(135deg, var(--primary), var(--primary-2));
                            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.10);
                        }

                        .admin-dashboard-page .page-title {
                            margin: 0;
                            font-family: var(--font-head);
                            font-size: 2.15rem;
                            line-height: 1.05;
                            letter-spacing: -0.05em;
                            font-weight: 800;
                            color: var(--text);
                        }

                        .admin-dashboard-page .page-subtitle {
                            margin-top: 8px;
                            color: var(--text-soft);
                            font-size: 0.96rem;
                            max-width: 760px;
                        }

                        .admin-dashboard-page .page-actions {
                            display: flex;
                            gap: 12px;
                            flex-wrap: wrap;
                        }

                        .admin-dashboard-page .btn-action,
                        .admin-dashboard-page .btn-submit {
                            display: inline-flex;
                            align-items: center;
                            justify-content: center;
                            gap: 8px;
                            border-radius: 12px;
                            font-size: 0.92rem;
                            font-weight: 700;
                            padding: 11px 18px;
                            transition: all 0.16s ease;
                            text-decoration: none;
                        }

                        .admin-dashboard-page .btn-action {
                            border: 1px solid var(--line-strong);
                            color: var(--text);
                            background: #fff;
                        }

                        .admin-dashboard-page .btn-action:hover {
                            color: var(--primary);
                            border-color: #bfd3ef;
                            background: #f9fbff;
                        }

                        .admin-dashboard-page .btn-submit {
                            border: none;
                            color: #fff;
                            background: linear-gradient(135deg, var(--primary), var(--primary-2));
                            box-shadow: 0 10px 24px rgba(37, 99, 235, 0.24);
                        }

                        .admin-dashboard-page .btn-submit:hover {
                            transform: translateY(-1px);
                            box-shadow: 0 14px 28px rgba(37, 99, 235, 0.28);
                            color: #fff;
                        }

                        .admin-dashboard-page .stats-grid {
                            display: grid;
                            grid-template-columns: repeat(4, minmax(0, 1fr));
                            gap: 16px;
                            margin-bottom: 20px;
                        }

                        .admin-dashboard-page .stat-card {
                            position: relative;
                            overflow: hidden;
                            background: var(--surface);
                            border: 1px solid rgba(255, 255, 255, 0.72);
                            border-radius: var(--radius-xl);
                            box-shadow: var(--shadow-soft);
                            padding: 18px 20px 20px;
                        }

                        .admin-dashboard-page .stat-card::before {
                            content: '';
                            position: absolute;
                            inset: 0 0 auto 0;
                            height: 4px;
                        }

                        .admin-dashboard-page .stat-income::before {
                            background: linear-gradient(90deg, #3b82f6, #60a5fa);
                        }

                        .admin-dashboard-page .stat-expenses::before {
                            background: linear-gradient(90deg, #ef4444, #fb7185);
                        }

                        .admin-dashboard-page .stat-receivables::before {
                            background: linear-gradient(90deg, #f59e0b, #fbbf24);
                        }

                        .admin-dashboard-page .stat-clients::before {
                            background: linear-gradient(90deg, #10b981, #34d399);
                        }

                        .admin-dashboard-page .stat-label {
                            color: var(--text-faint);
                            font-size: 0.74rem;
                            font-weight: 700;
                            text-transform: uppercase;
                            letter-spacing: 0.08em;
                            margin-bottom: 12px;
                        }

                        .admin-dashboard-page .stat-value {
                            color: var(--text);
                            font-size: 1.85rem;
                            font-weight: 800;
                            line-height: 1;
                            letter-spacing: -0.04em;
                            font-family: var(--font-mono);
                        }

                        .admin-dashboard-page .stat-meta {
                            color: var(--text-soft);
                            font-size: 0.84rem;
                            margin-top: 7px;
                        }

                        .admin-dashboard-page a.stat-card,
                        .admin-dashboard-page a.stat-card:hover,
                        .admin-dashboard-page a.stat-card:focus {
                            display: block;
                            color: inherit;
                            text-decoration: none;
                        }

                        .admin-dashboard-page a.stat-card {
                            cursor: pointer;
                            transition: transform 0.15s ease, box-shadow 0.15s ease;
                        }

                        .admin-dashboard-page a.stat-card:hover {
                            transform: translateY(-2px);
                            box-shadow: var(--shadow);
                        }

                        .admin-dashboard-page .stat-link-hint {
                            display: inline-flex;
                            align-items: center;
                            gap: 4px;
                            margin-top: 8px;
                            color: #b45309;
                            font-size: 0.74rem;
                            font-weight: 700;
                            letter-spacing: 0.04em;
                            text-transform: uppercase;
                        }

                        .admin-dashboard-page .dashboard-layout {
                            display: grid;
                            grid-template-columns: minmax(0, 1.7fr) minmax(300px, 0.95fr);
                            gap: 20px;
                            align-items: start;
                        }

                        .admin-dashboard-page .dashboard-main,
                        .admin-dashboard-page .dashboard-side {
                            display: grid;
                            gap: 20px;
                        }

                        .admin-dashboard-page .theme-card {
                            background: var(--surface);
                            border: 1px solid rgba(255, 255, 255, 0.72);
                            border-radius: var(--radius-xl);
                            box-shadow: var(--shadow);
                            overflow: hidden;
                        }

                        .admin-dashboard-page .theme-card-head {
                            padding: 18px 22px;
                            border-bottom: 1px solid var(--line);
                            background: linear-gradient(180deg, rgba(255, 255, 255, 0.98), rgba(249, 251, 255, 0.94));
                        }

                        .admin-dashboard-page .theme-card-title {
                            margin: 0;
                            color: var(--text);
                            font-size: 1.02rem;
                            font-weight: 800;
                            letter-spacing: -0.02em;
                        }

                        .admin-dashboard-page .theme-card-subtitle {
                            margin-top: 6px;
                            color: var(--text-soft);
                            font-size: 0.86rem;
                        }

                        .admin-dashboard-page .theme-card-body {
                            padding: 22px;
                        }

                        .admin-dashboard-page .snapshot-card {
                            position: relative;
                            overflow: hidden;
                        }

                        .admin-dashboard-page .snapshot-card::before {
                            content: '';
                            position: absolute;
                            inset: 0;
                            background:
                                radial-gradient(circle at top right, rgba(255, 255, 255, 0.25), transparent 40%),
                                linear-gradient(135deg, rgba(37, 99, 235, 0.98), rgba(29, 78, 216, 0.92));
                            z-index: 0;
                        }

                        .admin-dashboard-page .snapshot-card .theme-card-body {
                            position: relative;
                            z-index: 1;
                            color: #fff;
                        }

                        .admin-dashboard-page .snapshot-label {
                            display: inline-flex;
                            align-items: center;
                            gap: 8px;
                            padding: 7px 12px;
                            border-radius: 999px;
                            background: rgba(255, 255, 255, 0.16);
                            font-size: 0.74rem;
                            font-weight: 800;
                            letter-spacing: 0.08em;
                            text-transform: uppercase;
                        }

                        .admin-dashboard-page .snapshot-value {
                            font-size: 2.35rem;
                            line-height: 1;
                            font-weight: 800;
                            letter-spacing: -0.05em;
                            margin: 16px 0 8px;
                            font-family: var(--font-mono);
                        }

                        .admin-dashboard-page .snapshot-copy {
                            color: rgba(255, 255, 255, 0.86);
                            font-size: 0.92rem;
                            max-width: 360px;
                        }

                        .admin-dashboard-page .snapshot-grid {
                            display: grid;
                            grid-template-columns: repeat(2, minmax(0, 1fr));
                            gap: 12px;
                            margin-top: 18px;
                        }

                        .admin-dashboard-page .snapshot-pill {
                            border-radius: 14px;
                            background: rgba(255, 255, 255, 0.14);
                            border: 1px solid rgba(255, 255, 255, 0.18);
                            padding: 12px 14px;
                        }

                        .admin-dashboard-page .snapshot-pill-label {
                            font-size: 0.75rem;
                            text-transform: uppercase;
                            letter-spacing: 0.08em;
                            color: rgba(255, 255, 255, 0.75);
                            margin-bottom: 6px;
                        }

                        .admin-dashboard-page .snapshot-pill-value {
                            font-size: 1.08rem;
                            font-weight: 800;
                        }

                        .admin-dashboard-page .quick-actions-grid {
                            display: grid;
                            grid-template-columns: repeat(2, minmax(0, 1fr));
                            gap: 14px;
                        }

                        .admin-dashboard-page .quick-action {
                            display: flex;
                            align-items: flex-start;
                            gap: 14px;
                            padding: 16px;
                            border-radius: var(--radius-lg);
                            border: 1px solid var(--line);
                            background: var(--surface-soft);
                            text-decoration: none;
                            transition: transform 0.18s ease, box-shadow 0.18s ease, border-color 0.18s ease;
                        }

                        .admin-dashboard-page .quick-action:hover {
                            transform: translateY(-2px);
                            box-shadow: var(--shadow-soft);
                            border-color: rgba(37, 99, 235, 0.2);
                            text-decoration: none;
                        }

                        .admin-dashboard-page .quick-action-icon {
                            display: inline-flex;
                            align-items: center;
                            justify-content: center;
                            width: 44px;
                            height: 44px;
                            border-radius: 14px;
                            background: var(--primary-soft);
                            color: var(--primary-2);
                            font-size: 1.25rem;
                            flex-shrink: 0;
                        }

                        .admin-dashboard-page .quick-action-title {
                            color: var(--text);
                            font-size: 0.96rem;
                            font-weight: 800;
                            margin-bottom: 4px;
                        }

                        .admin-dashboard-page .quick-action-meta {
                            color: var(--text-soft);
                            font-size: 0.84rem;
                            line-height: 1.4;
                        }

                        .admin-dashboard-page .leaderboard-table {
                            width: 100%;
                            border-collapse: collapse;
                        }

                        .admin-dashboard-page .leaderboard-table thead th {
                            border-top: none;
                            border-bottom: 1px solid var(--line);
                            color: var(--text-faint);
                            font-size: 0.72rem;
                            font-weight: 800;
                            letter-spacing: 0.08em;
                            text-transform: uppercase;
                            white-space: nowrap;
                            padding: 0 0 12px;
                        }

                        .admin-dashboard-page .leaderboard-table td {
                            padding: 14px 0;
                            border-bottom: 1px solid var(--line);
                            vertical-align: middle;
                            color: var(--text);
                        }

                        .admin-dashboard-page .leaderboard-table tbody tr:last-child td {
                            border-bottom: none;
                        }

                        .admin-dashboard-page .rank-badge {
                            display: inline-flex;
                            align-items: center;
                            justify-content: center;
                            min-width: 54px;
                            padding: 8px 12px;
                            border-radius: 999px;
                            background: var(--primary-soft);
                            color: var(--primary-2);
                            font-weight: 800;
                            font-size: 0.82rem;
                        }

                        .admin-dashboard-page .leader-name {
                            font-weight: 700;
                            color: var(--text);
                        }

                        .admin-dashboard-page .leader-points {
                            text-align: right;
                            font-weight: 800;
                            color: var(--text);
                            font-family: var(--font-mono);
                        }

                        .admin-dashboard-page .leader-points.champion {
                            color: var(--success);
                        }

                        .admin-dashboard-page .empty-state {
                            padding: 12px 0 4px;
                            color: var(--text-soft);
                            font-size: 0.92rem;
                        }

                        .admin-dashboard-page .insight-list {
                            display: grid;
                            gap: 12px;
                        }

                        .admin-dashboard-page .insight-item {
                            border: 1px solid var(--line);
                            border-radius: var(--radius-lg);
                            background: var(--surface-soft);
                            padding: 14px 15px;
                        }

                        .admin-dashboard-page .insight-label {
                            color: var(--text-faint);
                            font-size: 0.74rem;
                            font-weight: 700;
                            text-transform: uppercase;
                            letter-spacing: 0.08em;
                            margin-bottom: 6px;
                        }

                        .admin-dashboard-page .insight-value {
                            color: var(--text);
                            font-size: 1.15rem;
                            font-weight: 800;
                            font-family: var(--font-mono);
                            line-height: 1.1;
                        }

                        .admin-dashboard-page .insight-meta {
                            color: var(--text-soft);
                            font-size: 0.84rem;
                            margin-top: 6px;
                        }

                        .admin-dashboard-page .task-queue-list {
                            display: grid;
                            gap: 12px;
                        }

                        .admin-dashboard-page .task-queue-item {
                            border: 1px solid var(--line);
                            border-radius: var(--radius-lg);
                            background: var(--surface-soft);
                            padding: 14px 15px;
                        }

                        .admin-dashboard-page .task-queue-top {
                            display: flex;
                            justify-content: space-between;
                            gap: 12px;
                            align-items: flex-start;
                            margin-bottom: 8px;
                        }

                        .admin-dashboard-page .task-queue-title {
                            color: var(--text);
                            font-size: 0.95rem;
                            font-weight: 800;
                            line-height: 1.35;
                        }

                        .admin-dashboard-page .task-queue-date {
                            display: inline-flex;
                            align-items: center;
                            justify-content: center;
                            padding: 6px 10px;
                            border-radius: 999px;
                            font-size: 0.74rem;
                            font-weight: 800;
                            white-space: nowrap;
                        }

                        .admin-dashboard-page .task-queue-date.status-overdue {
                            background: var(--danger-soft);
                            color: var(--danger);
                        }

                        .admin-dashboard-page .task-queue-date.status-due-today {
                            background: var(--warning-soft);
                            color: var(--warning);
                        }

                        .admin-dashboard-page .task-queue-date.status-upcoming {
                            background: var(--primary-soft);
                            color: var(--primary-2);
                        }

                        .admin-dashboard-page .task-queue-meta {
                            color: var(--text-soft);
                            font-size: 0.84rem;
                            line-height: 1.45;
                        }

                        /* Prospect Clients - Prevent horizontal overflow */
                        .admin-dashboard-page .prospect-list {
                            overflow-x: hidden;
                        }

                        .admin-dashboard-page .prospect-item-wrapper {
                            min-width: 0;
                        }

                        .admin-dashboard-page .prospect-item:hover {
                            background: var(--surface-soft);
                        }

                        .admin-dashboard-page .prospect-actions {
                            display: flex;
                            gap: 8px;
                        }

                        .admin-dashboard-page .prospect-notes-btn,
                        .admin-dashboard-page .prospect-view-btn {
                            transition: all 0.15s ease;
                        }

                        .admin-dashboard-page .prospect-notes-btn:hover {
                            color: var(--primary);
                            background: var(--primary-soft);
                            border-color: var(--primary);
                        }

                        .admin-dashboard-page .prospect-view-btn:hover {
                            color: var(--primary);
                            background: var(--primary-soft);
                            border-color: var(--primary);
                        }

                        @media (max-width: 1199px) {
                            .admin-dashboard-page .stats-grid {
                                grid-template-columns: repeat(2, minmax(0, 1fr));
                            }

                            .admin-dashboard-page .dashboard-layout {
                                grid-template-columns: 1fr;
                            }
                        }

                        @media (max-width: 767px) {
                            .admin-dashboard-page .page-title {
                                font-size: 1.75rem;
                            }

                            .admin-dashboard-page .stats-grid,
                            .admin-dashboard-page .quick-actions-grid,
                            .admin-dashboard-page .snapshot-grid {
                                grid-template-columns: 1fr;
                            }

                            .admin-dashboard-page .theme-card-head,
                            .admin-dashboard-page .theme-card-body {
                                padding-left: 16px;
                                padding-right: 16px;
                            }

                            /* Prospect Clients Responsive */
                            .admin-dashboard-page .prospect-list {
                                max-height: none;
                            }

                            .admin-dashboard-page .prospect-item {
                                padding: 12px 14px 6px;
                                gap: 10px;
                            }

                            .admin-dashboard-page .prospect-avatar {
                                width: 36px;
                                height: 36px;
                                font-size: 0.8rem;
                            }

                            .admin-dashboard-page .prospect-name {
                                font-size: 0.9rem;
                                white-space: normal;
                                word-break: break-word;
                            }

                            .admin-dashboard-page .prospect-contact {
                                font-size: 0.78rem;
                            }

                            .admin-dashboard-page .prospect-contact>div {
                                white-space: normal;
                                word-break: break-word;
                            }

                            .admin-dashboard-page .prospect-actions {
                                padding: 0 14px 10px 60px;
                                gap: 8px;
                            }

                            .admin-dashboard-page .prospect-notes-btn,
                            .admin-dashboard-page .prospect-view-btn {
                                flex: 1;
                                justify-content: center;
                                padding: 8px 12px;
                                font-size: 0.78rem;
                            }
                        }

                        @media (max-width: 480px) {
                            .admin-dashboard-page .prospect-item {
                                padding: 10px 12px 4px;
                                gap: 8px;
                            }

                            .admin-dashboard-page .prospect-avatar {
                                width: 32px;
                                height: 32px;
                                font-size: 0.75rem;
                            }

                            .admin-dashboard-page .prospect-name {
                                font-size: 0.85rem;
                            }

                            .admin-dashboard-page .prospect-contact {
                                font-size: 0.75rem;
                            }

                            .admin-dashboard-page .notes-indicator {
                                width: 16px;
                                height: 16px;
                                font-size: 0.6rem;
                            }

                            .admin-dashboard-page .prospect-actions {
                                padding: 0 12px 8px 52px;
                                gap: 6px;
                            }

                            .admin-dashboard-page .prospect-notes-btn,
                            .admin-dashboard-page .prospect-view-btn {
                                padding: 6px 10px;
                                font-size: 0.75rem;
                            }

                            .admin-dashboard-page .prospect-notes-btn span,
                            .admin-dashboard-page .prospect-view-btn span {
                                display: none;
                            }
                        }
                    </style>

                    <?php if ($this->session->flashdata('success')): ?>
                        <div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
                            <?= htmlspecialchars((string) $this->session->flashdata('success'), ENT_QUOTES, 'UTF-8'); ?>
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    <?php endif; ?>

                    <?php if ($this->session->flashdata('danger')): ?>
                        <div class="alert alert-danger alert-dismissible fade show mt-3" role="alert">
                            <?= htmlspecialchars((string) $this->session->flashdata('danger'), ENT_QUOTES, 'UTF-8'); ?>
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    <?php endif; ?>

                    <div class="page-header">
                        <div>
                            <div class="page-eyebrow">Admin Overview</div>
                            <h4 class="page-title">Admin Dashboard</h4>
                            <!-- <div class="page-subtitle">Monitor today’s financial activity, open receivables, client growth, and team accomplishments from one cleaner BERPS control center.</div> -->
                        </div>
                        <div class="page-actions">
                            <a href="<?= base_url(); ?>Page/invList" class="btn-action">
                                <i class="mdi mdi-file-document-outline"></i>
                                Invoice List
                            </a>
                            <a href="<?= base_url(); ?>Page/supportIssues?scope=unassigned" class="btn-action">
                                <i class="mdi mdi-ticket-outline"></i>
                                Unassigned Tickets
                                <span class="badge badge-pill badge-danger ml-2"><?= number_format($unassignedTicketValue); ?></span>
                            </a>
                            <a href="<?= base_url(); ?>Page/recurringInvoices" class="btn-submit">
                                <i class="mdi mdi-refresh-circle"></i>
                                Recurring Invoices
                            </a>
                        </div>
                    </div>

                    <div class="stats-grid">
                        <div class="stat-card stat-income">
                            <div class="stat-label">Today&apos;s Income</div>
                            <div class="stat-value"><?= number_format($todayIncomeValue, 2); ?></div>
                            <div class="stat-meta"><?= htmlspecialchars($currentDateLabel, ENT_QUOTES, 'UTF-8'); ?></div>
                        </div>
                        <div class="stat-card stat-expenses">
                            <div class="stat-label">Today&apos;s Expenses</div>
                            <div class="stat-value"><?= number_format($todayExpensesValue, 2); ?></div>
                            <!-- <div class="stat-meta">Validated expense entries posted today.</div> -->
                        </div>
                        <?php
                        $receivablesUrl = base_url() . 'Page/accountingReports?'
                            . http_build_query(array(
                                'date_from' => date('Y') . '-01-01',
                                'date_to' => date('Y-m-d'),
                            ))
                            . '#receivables-tab';
                        ?>
                        <a class="stat-card stat-receivables" href="<?= htmlspecialchars($receivablesUrl, ENT_QUOTES, 'UTF-8'); ?>" title="View open receivables breakdown">
                            <div class="stat-label">Open Receivables</div>
                            <div class="stat-value"><?= number_format($receivablesValue, 2); ?></div>
                            <div class="stat-link-hint">View list <i class="mdi mdi-arrow-right"></i></div>
                        </a>
                        <div class="stat-card stat-clients">
                            <div class="stat-label">Client Profiles</div>
                            <div class="stat-value"><?= number_format($totalClientsValue); ?></div>
                            <!-- <div class="stat-meta">Total company profiles currently stored in BERPS.</div> -->
                        </div>
                    </div>

                    <div class="dashboard-layout">
                        <div class="dashboard-main">
                            <div class="theme-card">
                                <div class="theme-card-head">
                                    <h5 class="theme-card-title">Billing Shortcuts</h5>
                                    <!-- <div class="theme-card-subtitle">Jump straight into the areas admins use most for invoicing, collections, and client management.</div> -->
                                </div>
                                <div class="theme-card-body">
                                    <div class="quick-actions-grid">
                                        <?php foreach ($quickActions as $action): ?>
                                            <a class="quick-action" href="<?= htmlspecialchars((string) $action['url'], ENT_QUOTES, 'UTF-8'); ?>">
                                                <span class="quick-action-icon">
                                                    <i class="mdi <?= htmlspecialchars((string) $action['icon'], ENT_QUOTES, 'UTF-8'); ?>"></i>
                                                </span>
                                                <span>
                                                    <span class="quick-action-title"><?= htmlspecialchars((string) $action['label'], ENT_QUOTES, 'UTF-8'); ?></span>
                                                    <span class="quick-action-meta"><?= htmlspecialchars((string) $action['meta'], ENT_QUOTES, 'UTF-8'); ?></span>
                                                </span>
                                            </a>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>

                            <div class="theme-card">
                                <div class="theme-card-head">
                                    <h5 class="theme-card-title">Upcoming Task Due Dates</h5>
                                    <div class="theme-card-subtitle">Open tasks that are overdue or due within the next <?= number_format($taskDueWindowValue); ?> day(s).</div>
                                </div>
                                <div class="theme-card-body">
                                    <?php if (!empty($taskQueueRows)): ?>
                                        <div class="task-queue-list">
                                            <?php foreach ($taskQueueRows as $taskRow): ?>
                                                <?php
                                                $dueDateRaw = trim((string) ($taskRow->dueDate ?? ''));
                                                $dueTimestamp = $dueDateRaw !== '' ? strtotime($dueDateRaw) : false;
                                                $todayTimestamp = strtotime(date('Y-m-d'));
                                                $dayDiff = $dueTimestamp !== false ? (int) floor(($dueTimestamp - $todayTimestamp) / 86400) : null;
                                                $dueClass = 'status-upcoming';
                                                $dueLabel = 'Due soon';
                                                if ($dayDiff !== null) {
                                                    if ($dayDiff < 0) {
                                                        $dueClass = 'status-overdue';
                                                        $dueLabel = 'Overdue by ' . number_format(abs($dayDiff)) . ' day(s)';
                                                    } elseif ($dayDiff === 0) {
                                                        $dueClass = 'status-due-today';
                                                        $dueLabel = 'Due today';
                                                    } else {
                                                        $dueLabel = 'Due in ' . number_format($dayDiff) . ' day(s)';
                                                    }
                                                }
                                                $taskTitle = trim((string) ($taskRow->task ?? 'Untitled task'));
                                                $projectLabel = trim((string) ($taskRow->projectDescription ?? 'No project'));
                                                $assigneeLabel = trim((string) ($taskRow->assignedPersonName ?? 'Unassigned'));
                                                ?>
                                                <div class="task-queue-item">
                                                    <div class="task-queue-top">
                                                        <div class="task-queue-title"><?= htmlspecialchars($taskTitle, ENT_QUOTES, 'UTF-8'); ?></div>
                                                        <span class="task-queue-date <?= htmlspecialchars($dueClass, ENT_QUOTES, 'UTF-8'); ?>">
                                                            <?= htmlspecialchars($dueLabel, ENT_QUOTES, 'UTF-8'); ?>
                                                        </span>
                                                    </div>
                                                    <div class="task-queue-meta">
                                                        Due <?= htmlspecialchars(date('M j, Y', strtotime($dueDateRaw)), ENT_QUOTES, 'UTF-8'); ?>
                                                        · Project: <?= htmlspecialchars($projectLabel, ENT_QUOTES, 'UTF-8'); ?>
                                                        · Assigned to <?= htmlspecialchars($assigneeLabel, ENT_QUOTES, 'UTF-8'); ?>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php else: ?>
                                        <div class="empty-state">No open tasks are overdue or due within the next <?= number_format($taskDueWindowValue); ?> days.</div>
                                    <?php endif; ?>
                                </div>
                            </div>

                        </div>

                        <div class="dashboard-side">
                            <div class="theme-card snapshot-card">
                                <div class="theme-card-body">
                                    <div class="snapshot-label">
                                        <i class="mdi mdi-chart-areaspline"></i>
                                        Today&apos;s Snapshot
                                    </div>
                                    <div class="snapshot-value"><?= number_format($netTodayValue, 2); ?></div>
                                    <div class="snapshot-copy">Net cash movement for <?= htmlspecialchars($currentDateLabel, ENT_QUOTES, 'UTF-8'); ?> after subtracting today&apos;s expenses from today&apos;s recorded income.</div>

                                    <div class="snapshot-grid">
                                        <div class="snapshot-pill">
                                            <div class="snapshot-pill-label">Income</div>
                                            <div class="snapshot-pill-value"><?= number_format($todayIncomeValue, 2); ?></div>
                                        </div>
                                        <div class="snapshot-pill">
                                            <div class="snapshot-pill-label">Expenses</div>
                                            <div class="snapshot-pill-value"><?= number_format($todayExpensesValue, 2); ?></div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <?php
                            $prospectClients = isset($prospectClients) && is_array($prospectClients) ? $prospectClients : array();
                            $prospectCount = count($prospectClients);
                            ?>
                            <div class="theme-card">
                                <div class="theme-card-head">
                                    <h5 class="theme-card-title">Prospect Clients</h5>
                                    <div class="theme-card-subtitle"><?= number_format($prospectCount); ?> client(s) for follow-up. Click to view details or add notes.</div>
                                </div>
                                <div class="theme-card-body" style="padding: 0;">
                                    <?php if (!empty($prospectClients)): ?>
                                        <div class="prospect-list" style="max-height: 420px; overflow-y: auto;">
                                            <?php foreach ($prospectClients as $prospect): ?>
                                                <?php
                                                $custID = (int) ($prospect->CustID ?? 0);
                                                $clientName = trim((string) ($prospect->Customer ?? 'Unnamed Client'));
                                                $contactPerson = trim((string) ($prospect->ContactPerson ?? ''));
                                                $contactNo = trim((string) ($prospect->ContactNo ?? ''));
                                                $email = trim((string) ($prospect->Email ?? ''));
                                                $address = trim((string) ($prospect->Address ?? ''));
                                                $notes = trim((string) ($prospect->notes ?? ''));
                                                $hasNotes = !empty($notes);
                                                $initials = strtoupper(substr($clientName, 0, 1) . (strpos($clientName, ' ') !== false ? substr($clientName, strpos($clientName, ' ') + 1, 1) : ''));
                                                if (strlen($initials) < 2) {
                                                    $initials = strtoupper(substr($clientName, 0, 2));
                                                }
                                                ?>
                                                <div class="prospect-item-wrapper" style="display: flex; flex-direction: column; border-bottom: 1px solid var(--line);">
                                                    <a href="<?= base_url('Page/clientProfile?cust_id=' . $custID); ?>" class="prospect-item" style="display: flex; align-items: flex-start; gap: 12px; padding: 14px 18px 8px; text-decoration: none; color: inherit; transition: background 0.15s ease;">
                                                        <div class="prospect-avatar" style="width: 40px; height: 40px; border-radius: 10px; background: linear-gradient(135deg, var(--warning), #f59e0b); color: #fff; display: inline-flex; align-items: center; justify-content: center; font-weight: 700; font-size: 0.9rem; flex-shrink: 0;">
                                                            <?= htmlspecialchars($initials, ENT_QUOTES, 'UTF-8'); ?>
                                                        </div>
                                                        <div class="prospect-info" style="flex: 1; min-width: 0;">
                                                            <div class="prospect-name" style="font-weight: 700; color: var(--text); font-size: 0.95rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                                                <?= htmlspecialchars($clientName, ENT_QUOTES, 'UTF-8'); ?>
                                                                <?php if ($hasNotes): ?>
                                                                    <span class="notes-indicator" style="display: inline-flex; align-items: center; justify-content: center; width: 18px; height: 18px; border-radius: 50%; background: var(--info); color: #fff; font-size: 0.65rem; margin-left: 6px; vertical-align: middle;" title="Has notes"><i class="mdi mdi-text-short"></i></span>
                                                                <?php endif; ?>
                                                            </div>
                                                            <div class="prospect-contact" style="font-size: 0.82rem; color: var(--text-soft); margin-top: 4px; line-height: 1.4;">
                                                                <?php if ($contactPerson): ?>
                                                                    <div><i class="mdi mdi-account" style="font-size: 0.9em; margin-right: 4px;"></i><?= htmlspecialchars($contactPerson, ENT_QUOTES, 'UTF-8'); ?></div>
                                                                <?php endif; ?>
                                                                <?php if ($contactNo): ?>
                                                                    <div><i class="mdi mdi-phone" style="font-size: 0.9em; margin-right: 4px;"></i><?= htmlspecialchars($contactNo, ENT_QUOTES, 'UTF-8'); ?></div>
                                                                <?php endif; ?>
                                                                <?php if ($email): ?>
                                                                    <div style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"><i class="mdi mdi-email" style="font-size: 0.9em; margin-right: 4px;"></i><?= htmlspecialchars($email, ENT_QUOTES, 'UTF-8'); ?></div>
                                                                <?php endif; ?>
                                                            </div>
                                                        </div>
                                                    </a>
                                                    <div class="prospect-actions" style="display: flex; padding: 0 18px 12px 70px; gap: 8px;">
                                                        <button type="button" class="prospect-notes-btn" data-toggle="modal" data-target="#prospectNotesModal" data-cust-id="<?= $custID; ?>" data-client-name="<?= htmlspecialchars($clientName, ENT_QUOTES, 'UTF-8'); ?>" data-notes="<?= htmlspecialchars($notes, ENT_QUOTES, 'UTF-8'); ?>" style="display: inline-flex; align-items: center; gap: 6px; padding: 6px 14px; border: 1px solid var(--line); border-radius: var(--radius-md); background: var(--surface-soft); color: var(--text-soft); font-size: 0.8rem; font-weight: 600; cursor: pointer; transition: all 0.15s ease;" title="<?= $hasNotes ? 'View/Edit Notes' : 'Add Notes'; ?>">
                                                            <i class="mdi <?= $hasNotes ? 'mdi-note-text' : 'mdi-note-plus-outline'; ?>"></i>
                                                            <span><?= $hasNotes ? 'View Notes' : 'Add Notes'; ?></span>
                                                        </button>
                                                        <a href="<?= base_url('Page/clientProfile?cust_id=' . $custID); ?>" class="prospect-view-btn" style="display: inline-flex; align-items: center; gap: 6px; padding: 6px 14px; border: 1px solid var(--line); border-radius: var(--radius-md); background: var(--surface-soft); color: var(--text-soft); font-size: 0.8rem; font-weight: 600; text-decoration: none; transition: all 0.15s ease;" title="View Client Profile">
                                                            <i class="mdi mdi-open-in-new"></i>
                                                            <span>View</span>
                                                        </a>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php else: ?>
                                        <div class="empty-state" style="padding: 30px 22px; text-align: center;">
                                            <div style="font-size: 2.5rem; color: var(--line-strong); margin-bottom: 10px;"><i class="mdi mdi-account-search"></i></div>
                                            <div style="color: var(--text-soft); font-size: 0.95rem;">No Prospect clients found.</div>
                                            <div style="color: var(--text-faint); font-size: 0.82rem; margin-top: 6px;">Add clients with Prospect status for follow-up tracking.</div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Prospect Notes Modal -->
                            <div class="modal fade" id="prospectNotesModal" tabindex="-1" role="dialog" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered" role="document">
                                    <div class="modal-content" style="border: none; border-radius: var(--radius-xl); box-shadow: var(--shadow);">
                                        <div class="modal-header" style="border-bottom: 1px solid var(--line); background: linear-gradient(180deg, rgba(255,255,255,0.98), rgba(249,251,255,0.94)); border-radius: var(--radius-xl) var(--radius-xl) 0 0;">
                                            <h5 class="modal-title" style="font-weight: 700; color: var(--text);">Client Notes</h5>
                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color: var(--text-faint);">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                        <form id="prospectNotesForm" method="POST" action="<?= base_url('Page/saveProspectNotes'); ?>">
                                            <input type="hidden" name="cust_id" id="notes_cust_id">
                                            <div class="modal-body" style="padding: 20px;">
                                                <div style="margin-bottom: 16px;">
                                                    <label style="font-size: 0.74rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.08em; color: var(--text-faint); margin-bottom: 6px;">Client</label>
                                                    <div id="notes_client_name" style="font-weight: 700; color: var(--text); font-size: 1rem;"></div>
                                                </div>
                                                <div class="form-group mb-0">
                                                    <label for="notes_text" style="font-size: 0.74rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.08em; color: var(--text-faint); margin-bottom: 8px;">Notes</label>
                                                    <textarea class="form-control" id="notes_text" name="notes" rows="6" style="border: 1px solid var(--line); border-radius: var(--radius-md); resize: vertical;"></textarea>
                                                </div>
                                            </div>
                                            <div class="modal-footer" style="border-top: 1px solid var(--line); padding: 16px 20px;">
                                                <button type="button" class="btn btn-light" data-dismiss="modal" style="border: 1px solid var(--line); color: var(--text); background: #fff; border-radius: var(--radius-md); padding: 8px 16px; font-weight: 600;">Cancel</button>
                                                <button type="submit" class="btn btn-primary" style="border: none; background: linear-gradient(135deg, var(--primary), var(--primary-2)); color: #fff; border-radius: var(--radius-md); padding: 8px 20px; font-weight: 700;">
                                                    <i class="mdi mdi-content-save mr-1"></i> Save Notes
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
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
        $(document).ready(function() {
            // Prospect Notes Modal - Populate data when opened
            $('#prospectNotesModal').on('show.bs.modal', function(event) {
                var button = $(event.relatedTarget);
                var custId = button.data('cust-id');
                var clientName = button.data('client-name');
                var notes = button.data('notes');

                var modal = $(this);
                modal.find('#notes_cust_id').val(custId);
                modal.find('#notes_client_name').text(clientName);
                modal.find('#notes_text').val(notes);
            });

            // Handle notes form submission with AJAX
            $('#prospectNotesForm').on('submit', function(e) {
                e.preventDefault();

                var form = $(this);
                var submitBtn = form.find('button[type="submit"]');
                var originalText = submitBtn.html();

                // Show loading state
                submitBtn.prop('disabled', true).html('<i class="mdi mdi-loading mdi-spin mr-1"></i> Saving...');

                $.ajax({
                    url: form.attr('action'),
                    type: 'POST',
                    data: form.serialize(),
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            // Close modal
                            $('#prospectNotesModal').modal('hide');

                            // Show success message
                            if (typeof $.notify !== 'undefined') {
                                $.notify({
                                    message: response.message
                                }, {
                                    type: 'success'
                                });
                            }

                            // Refresh page to show updated notes indicator
                            setTimeout(function() {
                                location.reload();
                            }, 500);
                        } else {
                            alert(response.message || 'Failed to save notes.');
                        }
                    },
                    error: function() {
                        alert('An error occurred while saving notes. Please try again.');
                    },
                    complete: function() {
                        submitBtn.prop('disabled', false).html(originalText);
                    }
                });
            });
        });
    </script>

</body>

</html>
