<?php
$clientData = isset($client) ? $client : null;
$taskRows = isset($tasks) && is_array($tasks) ? array_values($tasks) : array();
$clientName = trim((string) ($clientData->Customer ?? 'Client Portal'));
$backUrl = base_url() . 'Page/clientDashboard';

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
            <div class="container-fluid client-profile-page">
                <style>
                    .client-profile-page {
                        --bg: #f5f7fb;
                        --surface: rgba(255, 255, 255, 0.92);
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
                        --radius-lg: 16px;
                        --radius-md: 12px;
                        font-family: var(--font-primary, Montserrat, Segoe UI, Arial, sans-serif);
                        background:
                            radial-gradient(circle at top left, rgba(37, 99, 235, 0.08), transparent 28%),
                            radial-gradient(circle at top right, rgba(16, 185, 129, 0.08), transparent 24%),
                            linear-gradient(180deg, #f8fbff 0%, #f4f7fb 100%);
                        min-height: 100vh;
                        padding-bottom: 24px;
                    }

                    .client-profile-page .cp-header {
                        margin: 24px 0 22px;
                        display: flex;
                        justify-content: space-between;
                        align-items: flex-end;
                        gap: 16px;
                        flex-wrap: wrap;
                    }

                    .client-profile-page .cp-eyebrow {
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

                    .client-profile-page .cp-title {
                        margin: 0;
                        color: var(--text);
                        font-size: clamp(2rem, 3vw, 2.7rem);
                        line-height: 1.05;
                        font-weight: 800;
                    }

                    .client-profile-page .cp-subtitle {
                        margin: 12px 0 0;
                        color: var(--text-soft);
                        font-size: 1rem;
                        max-width: 760px;
                    }

                    .client-profile-page .btn-soft {
                        display: inline-flex;
                        align-items: center;
                        gap: 10px;
                        border-radius: 18px;
                        padding: 12px 20px;
                        border: 1px solid var(--line);
                        background: rgba(255, 255, 255, 0.9);
                        color: var(--text);
                        font-weight: 700;
                        text-decoration: none;
                        box-shadow: 0 10px 26px rgba(15, 23, 42, 0.04);
                    }

                    .client-profile-page .panel-card {
                        background: var(--surface);
                        border: 1px solid rgba(255, 255, 255, 0.75);
                        border-radius: var(--radius-xl);
                        box-shadow: var(--shadow);
                        overflow: hidden;
                    }

                    .client-profile-page .panel-header {
                        padding: 24px 28px 18px;
                        border-bottom: 1px solid var(--line);
                    }

                    .client-profile-page .panel-title {
                        margin: 0;
                        color: var(--text);
                        font-size: 1.45rem;
                        font-weight: 800;
                    }

                    .client-profile-page .panel-subtitle {
                        margin-top: 8px;
                        color: var(--text-soft);
                        font-size: 0.98rem;
                    }

                    .client-profile-page .panel-body {
                        padding: 22px 28px 28px;
                    }

                    .client-profile-page .table-responsive {
                        border: 1px solid var(--line);
                        border-radius: 22px;
                        overflow: hidden;
                        background: var(--surface-2);
                    }

                    .client-profile-page table {
                        margin-bottom: 0;
                        width: 100%;
                        table-layout: fixed;
                    }

                    .client-profile-page thead th {
                        border-top: none;
                        background: #f8fbff;
                        color: var(--text-faint);
                        font-size: 0.78rem;
                        font-weight: 800;
                        letter-spacing: 0.08em;
                        text-transform: uppercase;
                        padding: 16px 18px;
                    }

                    .client-profile-page tbody td {
                        vertical-align: top;
                        padding: 18px;
                        color: var(--text);
                        border-top: 1px solid var(--line);
                        word-break: break-word;
                        overflow-wrap: anywhere;
                    }

                    .client-profile-page tbody td:first-child {
                        width: 34%;
                    }

                    .client-profile-page .priority-pill,
                    .client-profile-page .status-pill {
                        display: inline-flex;
                        align-items: center;
                        padding: 5px 10px;
                        border-radius: 999px;
                        font-size: 0.74rem;
                        font-weight: 700;
                    }

                    .client-profile-page .priority-high {
                        background: var(--danger-soft);
                        color: var(--danger);
                    }

                    .client-profile-page .priority-medium {
                        background: var(--warning-soft);
                        color: var(--warning);
                    }

                    .client-profile-page .priority-low {
                        background: var(--success-soft);
                        color: var(--success);
                    }

                    .client-profile-page .status-pill {
                        background: var(--success-soft);
                        color: var(--success);
                    }

                    .client-profile-page .muted {
                        color: var(--text-soft);
                    }

                    .client-profile-page .empty-state {
                        padding: 30px 24px;
                        text-align: center;
                        color: var(--text-soft);
                    }

                    @media (max-width: 1399.98px) {
                        .client-profile-page .panel-body {
                            padding: 18px;
                        }

                        .client-profile-page .table-responsive {
                            border: none;
                            background: transparent;
                            overflow: visible;
                        }

                        .client-profile-page table,
                        .client-profile-page thead,
                        .client-profile-page tbody,
                        .client-profile-page th,
                        .client-profile-page td,
                        .client-profile-page tr {
                            display: block;
                            width: 100%;
                        }

                        .client-profile-page thead {
                            display: none;
                        }

                        .client-profile-page tbody tr {
                            margin-bottom: 16px;
                            border: 1px solid var(--line);
                            border-radius: 18px;
                            background: var(--surface-2);
                            box-shadow: 0 10px 24px rgba(15, 23, 42, 0.05);
                            overflow: hidden;
                        }

                        .client-profile-page tbody td {
                            border-top: 1px solid var(--line);
                            padding: 14px 16px;
                        }

                        .client-profile-page tbody td:first-child {
                            border-top: none;
                        }

                        .client-profile-page tbody td::before {
                            content: attr(data-label);
                            display: block;
                            margin-bottom: 6px;
                            color: var(--text-faint);
                            font-size: 0.76rem;
                            font-weight: 800;
                            letter-spacing: 0.08em;
                            text-transform: uppercase;
                        }

                        .client-profile-page .empty-state {
                            padding: 24px 18px;
                        }
                    }
                </style>

                <div class="cp-header">
                    <div>
                        <div class="cp-eyebrow">Client Portal</div>
                        <h1 class="cp-title">Accomplished Tasks</h1>
                        <!-- <p class="cp-subtitle">
                            Review completed work for <?= htmlspecialchars($clientName !== '' ? $clientName : 'your account', ENT_QUOTES, 'UTF-8'); ?>, including project, assignee, completion date, and completion notes.
                        </p> -->
                    </div>

                    <a class="btn-soft" href="<?= htmlspecialchars($backUrl, ENT_QUOTES, 'UTF-8'); ?>">
                        <i class="fas fa-arrow-left"></i>
                        Back to Dashboard
                    </a>
                </div>

                <div class="panel-card">
                    <div class="panel-header">
                        <h2 class="panel-title">Completed Task Details</h2>
                        <div class="panel-subtitle"><?= number_format((int) ($totalAccomplished ?? count($taskRows))); ?> completed task<?= ((int) ($totalAccomplished ?? count($taskRows))) === 1 ? '' : 's'; ?> available for review.</div>
                    </div>
                    <div class="panel-body">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                <tr>
                                    <th>Task</th>
                                    <th>Reported</th>
                                    <th>Due</th>
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
                                        $reportedDate = trim((string) ($task->reportedDate ?? ''));
                                        $dueDate = trim((string) ($task->dueDate ?? ''));
                                        $completedDate = trim((string) ($task->completedDate ?? ''));
                                        $completionNote = trim((string) ($task->completionNote ?? ''));
                                        ?>
                                        <tr>
                                            <td data-label="Task">
                                                <strong><?= htmlspecialchars((string) ($task->task ?? 'N/A'), ENT_QUOTES, 'UTF-8'); ?></strong>
                                                <div class="muted">Task #<?= (int) ($task->taskID ?? 0); ?></div>
                                                <div class="mt-2">
                                                    <span class="priority-pill <?= htmlspecialchars($priorityClass, ENT_QUOTES, 'UTF-8'); ?>"><?= htmlspecialchars($priorityLabel, ENT_QUOTES, 'UTF-8'); ?> Priority</span>
                                                </div>
                                            </td>
                                            <td data-label="Reported"><?= htmlspecialchars($reportedDate !== '' && $reportedDate !== '0000-00-00' ? date('M d, Y', strtotime($reportedDate)) : 'N/A', ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td data-label="Due"><?= htmlspecialchars($dueDate !== '' && $dueDate !== '0000-00-00' ? date('M d, Y', strtotime($dueDate)) : 'N/A', ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td data-label="Completed">
                                                <span class="status-pill">Completed</span>
                                                <div class="muted mt-1"><?= htmlspecialchars($completedDate !== '' ? date('M d, Y h:i A', strtotime($completedDate)) : 'N/A', ENT_QUOTES, 'UTF-8'); ?></div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="4" class="empty-state">No accomplished tasks found.</td>
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
