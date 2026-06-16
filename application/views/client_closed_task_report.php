<?php
$clientData = isset($client) ? $client : null;
$taskRows = isset($tasks) && is_array($tasks) ? array_values($tasks) : array();
$clientName = trim((string) ($clientData->Customer ?? 'Client Portal'));
$backUrl = site_url('Page/clientDashboard');
$latestCompletedAt = isset($latestCompletedAt) ? trim((string) $latestCompletedAt) : '';
$totalClosedTasks = isset($totalClosedTasks) ? (int) $totalClosedTasks : count($taskRows);
$highPriorityCount = isset($highPriorityCount) ? (int) $highPriorityCount : 0;
$mediumPriorityCount = isset($mediumPriorityCount) ? (int) $mediumPriorityCount : 0;
$lowPriorityCount = isset($lowPriorityCount) ? (int) $lowPriorityCount : 0;

$priorityLabels = array(
    '1' => 'High',
    '2' => 'Medium',
    '3' => 'Low',
);

$priorityClasses = array(
    '1' => 'priority-high',
    '2' => 'priority-medium',
    '3' => 'priority-low',
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
            <div class="container-fluid client-report-page">
                <style>
                    .client-report-page {
                        --bg: #f5f7fb;
                        --surface: rgba(255, 255, 255, 0.94);
                        --surface-2: #ffffff;
                        --line: #e7ecf3;
                        --text: #122033;
                        --text-soft: #5e7188;
                        --text-faint: #8ea0b5;
                        --primary: #2563eb;
                        --primary-soft: #eaf2ff;
                        --success: #059669;
                        --success-soft: #ecfdf5;
                        --warning: #d97706;
                        --warning-soft: #fff7ed;
                        --danger: #e11d48;
                        --danger-soft: #fff1f2;
                        --shadow: 0 14px 40px rgba(15, 23, 42, 0.08);
                        --radius-xl: 22px;
                        font-family: 'Inter', 'Poppins', 'Segoe UI', Arial, sans-serif;
                        background:
                            radial-gradient(circle at top left, rgba(37, 99, 235, 0.08), transparent 28%),
                            radial-gradient(circle at top right, rgba(16, 185, 129, 0.08), transparent 24%),
                            linear-gradient(180deg, #f8fbff 0%, #f4f7fb 100%);
                        min-height: 100vh;
                        padding-bottom: 24px;
                    }

                    .client-report-page .cr-header {
                        margin: 24px 0 22px;
                        display: flex;
                        justify-content: space-between;
                        align-items: flex-end;
                        gap: 16px;
                        flex-wrap: wrap;
                    }

                    .client-report-page .cr-eyebrow {
                        display: inline-flex;
                        align-items: center;
                        padding: 7px 12px;
                        border-radius: 999px;
                        background: rgba(37, 99, 235, 0.08);
                        color: #1d4ed8;
                        font-size: 0.76rem;
                        font-weight: 700;
                        letter-spacing: 0.08em;
                        text-transform: uppercase;
                        margin-bottom: 10px;
                    }

                    .client-report-page .cr-title {
                        margin: 0;
                        color: var(--text);
                        font-size: clamp(2rem, 3vw, 2.7rem);
                        line-height: 1.05;
                        font-weight: 800;
                    }

                    .client-report-page .cr-subtitle {
                        margin: 12px 0 0;
                        color: var(--text-soft);
                        font-size: 1rem;
                        max-width: 780px;
                    }

                    .client-report-page .header-actions {
                        display: flex;
                        gap: 10px;
                        flex-wrap: wrap;
                    }

                    .client-report-page .btn-soft,
                    .client-report-page .btn-solid {
                        display: inline-flex;
                        align-items: center;
                        gap: 10px;
                        border-radius: 18px;
                        padding: 12px 20px;
                        font-weight: 700;
                        text-decoration: none;
                        box-shadow: 0 10px 26px rgba(15, 23, 42, 0.04);
                        border: 1px solid var(--line);
                        background: rgba(255, 255, 255, 0.9);
                        color: var(--text);
                    }

                    .client-report-page .btn-solid {
                        background: linear-gradient(135deg, var(--primary), #1d4ed8);
                        color: #fff;
                        border-color: transparent;
                    }

                    .client-report-page .summary-grid {
                        display: grid;
                        grid-template-columns: repeat(4, minmax(0, 1fr));
                        gap: 16px;
                        margin-bottom: 22px;
                    }

                    .client-report-page .summary-card,
                    .client-report-page .panel-card {
                        background: var(--surface);
                        border: 1px solid rgba(255, 255, 255, 0.75);
                        border-radius: var(--radius-xl);
                        box-shadow: var(--shadow);
                    }

                    .client-report-page .summary-card {
                        padding: 22px 24px;
                    }

                    .client-report-page .summary-label {
                        color: var(--text-faint);
                        font-size: 0.78rem;
                        font-weight: 800;
                        text-transform: uppercase;
                        letter-spacing: 0.08em;
                        margin-bottom: 10px;
                    }

                    .client-report-page .summary-value {
                        color: var(--text);
                        font-size: 2rem;
                        font-weight: 800;
                        line-height: 1;
                    }

                    .client-report-page .summary-meta {
                        margin-top: 10px;
                        color: var(--text-soft);
                        font-size: 0.92rem;
                    }

                    .client-report-page .panel-header {
                        padding: 24px 28px 18px;
                        border-bottom: 1px solid var(--line);
                    }

                    .client-report-page .panel-title {
                        margin: 0;
                        color: var(--text);
                        font-size: 1.45rem;
                        font-weight: 800;
                    }

                    .client-report-page .panel-subtitle {
                        margin-top: 8px;
                        color: var(--text-soft);
                        font-size: 0.98rem;
                    }

                    .client-report-page .panel-body {
                        padding: 22px 28px 28px;
                    }

                    .client-report-page .table-responsive {
                        border: 1px solid var(--line);
                        border-radius: 22px;
                        overflow: hidden;
                        background: var(--surface-2);
                    }

                    .client-report-page table {
                        margin-bottom: 0;
                    }

                    .client-report-page thead th {
                        border-top: none;
                        background: #f8fbff;
                        color: var(--text-faint);
                        font-size: 0.78rem;
                        font-weight: 800;
                        letter-spacing: 0.08em;
                        text-transform: uppercase;
                        padding: 16px 18px;
                    }

                    .client-report-page tbody td {
                        vertical-align: top;
                        padding: 18px;
                        color: var(--text);
                        border-top: 1px solid var(--line);
                    }

                    .client-report-page .priority-pill,
                    .client-report-page .status-pill {
                        display: inline-flex;
                        align-items: center;
                        padding: 5px 10px;
                        border-radius: 999px;
                        font-size: 0.74rem;
                        font-weight: 700;
                    }

                    .client-report-page .priority-high {
                        background: var(--danger-soft);
                        color: var(--danger);
                    }

                    .client-report-page .priority-medium {
                        background: var(--warning-soft);
                        color: var(--warning);
                    }

                    .client-report-page .priority-low {
                        background: var(--success-soft);
                        color: var(--success);
                    }

                    .client-report-page .status-pill {
                        background: var(--success-soft);
                        color: var(--success);
                    }

                    .client-report-page .muted {
                        color: var(--text-soft);
                    }

                    .client-report-page .empty-state {
                        padding: 30px 24px;
                        text-align: center;
                        color: var(--text-soft);
                    }

                    @media print {
                        .left-side-menu,
                        .navbar-custom,
                        .footer,
                        .header-actions {
                            display: none !important;
                        }

                        .content-page {
                            margin-left: 0 !important;
                        }
                    }
                </style>

                <div class="cr-header">
                    <div>
                        <div class="cr-eyebrow">Client Report</div>
                        <h1 class="cr-title">Closed Task Report</h1>
                        <!-- <p class="cr-subtitle">
                            View a full report of completed tasks for <?= htmlspecialchars($clientName !== '' ? $clientName : 'your account', ENT_QUOTES, 'UTF-8'); ?>, including assignee, project, completion notes, and schedule details.
                        </p> -->
                    </div>

                    <div class="header-actions">
                        <a class="btn-soft" href="<?= htmlspecialchars($backUrl, ENT_QUOTES, 'UTF-8'); ?>">
                            <i class="fas fa-arrow-left"></i>
                            Back to Dashboard
                        </a>
                        <button type="button" class="btn-solid" onclick="window.print();">
                            <i class="fas fa-print"></i>
                            Print Report
                        </button>
                    </div>
                </div>

                <div class="summary-grid">
                    <div class="summary-card">
                        <div class="summary-label">Closed Tasks</div>
                        <div class="summary-value"><?= number_format($totalClosedTasks); ?></div>
                        <!-- <div class="summary-meta">Total completed work items on record.</div> -->
                    </div>
                    <div class="summary-card">
                        <div class="summary-label">High Priority</div>
                        <div class="summary-value"><?= number_format($highPriorityCount); ?></div>
                        <!-- <div class="summary-meta">Urgent or high-impact tasks completed.</div> -->
                    </div>
                    <div class="summary-card">
                        <div class="summary-label">Medium Priority</div>
                        <div class="summary-value"><?= number_format($mediumPriorityCount); ?></div>
                        <!-- <div class="summary-meta">Standard-priority tasks completed.</div> -->
                    </div>
                    <div class="summary-card">
                        <div class="summary-label">Latest Completion</div>
                        <div class="summary-value" style="font-size: 1.05rem; line-height: 1.35;">
                            <?= htmlspecialchars($latestCompletedAt !== '' ? date('M d, Y h:i A', strtotime($latestCompletedAt)) : 'No record', ENT_QUOTES, 'UTF-8'); ?>
                        </div>
                        <!-- <div class="summary-meta">Most recent completed task update.</div> -->
                    </div>
                </div>

                <div class="panel-card">
                    <div class="panel-header">
                        <h2 class="panel-title">Closed Task Details</h2>
                        <div class="panel-subtitle">Report-ready list of all completed tasks available to this client account.</div>
                    </div>
                    <div class="panel-body">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                <tr>
                                    <th>Task</th>
                                    <th>Completed</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php if (!empty($taskRows)): ?>
                                    <?php foreach ($taskRows as $task): ?>
                                        <?php
                                        $priorityKey = (string) ($task->priority ?? '2');
                                        $priorityLabel = isset($priorityLabels[$priorityKey]) ? $priorityLabels[$priorityKey] : 'Medium';
                                        $priorityClass = isset($priorityClasses[$priorityKey]) ? $priorityClasses[$priorityKey] : 'priority-medium';
                                        $completedDate = trim((string) ($task->completedDate ?? ''));
                                        $completionNote = trim((string) ($task->completionNote ?? ''));
                                        ?>
                                        <tr>
                                            <td>
                                                <?= htmlspecialchars((string) ($task->task ?? 'N/A'), ENT_QUOTES, 'UTF-8'); ?>
                                                <div class="muted">Task #<?= (int) ($task->taskID ?? 0); ?></div>
                                            </td>
                                            <td>
                                                <span class="status-pill">Closed</span>
                                                <div class="muted mt-1"><?= htmlspecialchars($completedDate !== '' ? date('M d, Y h:i A', strtotime($completedDate)) : 'N/A', ENT_QUOTES, 'UTF-8'); ?></div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="2" class="empty-state">No closed tasks found for this client account.</td>
                                    </tr>
                                <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <?php include('includes/footer.php'); ?>
        </div>
    </div>
</div>

<script src="<?= base_url(); ?>assets/js/vendor.min.js"></script>
<script src="<?= base_url(); ?>assets/js/app.min.js"></script>
</body>
</html>
