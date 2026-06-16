<?php
$client = isset($client) ? $client : null;
$tasks = isset($tasks) && is_array($tasks) ? $tasks : [];
$pageTitle = isset($pageTitle) ? $pageTitle : 'Tasks';
$pageSubtitle = isset($pageSubtitle) ? $pageSubtitle : '';
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
                <div class="container-fluid client-task-list-page">

                    <style>
                        .client-task-list-page {
                            max-width: 1200px;
                            margin: 0 auto;
                            padding: 30px 20px;
                        }

                        .client-task-list-page .entry-header {
                            margin-bottom: 28px;
                        }

                        .client-task-list-page .entry-title {
                            font-size: 1.75rem;
                            font-weight: 800;
                            color: var(--text);
                            margin-bottom: 8px;
                        }

                        .client-task-list-page .entry-breadcrumb {
                            display: flex;
                            align-items: center;
                            gap: 8px;
                            font-size: 0.85rem;
                            color: var(--text-soft);
                        }

                        .client-task-list-page .entry-breadcrumb a {
                            color: var(--primary);
                            text-decoration: none;
                            font-weight: 600;
                        }

                        .client-task-list-page .entry-breadcrumb a:hover {
                            text-decoration: underline;
                        }

                        .client-task-list-page .entry-breadcrumb .separator {
                            color: var(--text-faint);
                        }

                        .client-task-list-page .entry-card {
                            background: var(--surface);
                            border: 1px solid var(--line);
                            border-radius: var(--radius-xl);
                            box-shadow: var(--shadow-soft);
                            padding: 32px 36px;
                        }

                        .client-task-list-page .task-table {
                            width: 100%;
                            border-collapse: collapse;
                        }

                        .client-task-list-page .task-table th {
                            font-size: 0.82rem;
                            font-weight: 700;
                            text-transform: uppercase;
                            letter-spacing: 0.06em;
                            color: var(--text-faint);
                            padding: 14px 16px;
                            text-align: left;
                            border-bottom: 2px solid var(--line);
                        }

                        .client-task-list-page .task-table td {
                            padding: 16px;
                            border-bottom: 1px solid var(--line);
                            color: var(--text);
                            font-size: 0.95rem;
                        }

                        .client-task-list-page .task-table tr:last-child td {
                            border-bottom: none;
                        }

                        .client-task-list-page .task-table tr:hover td {
                            background: var(--surface-soft);
                        }

                        .client-task-list-page .priority-badge {
                            display: inline-block;
                            padding: 4px 12px;
                            border-radius: var(--radius-sm);
                            font-size: 0.75rem;
                            font-weight: 700;
                            text-transform: uppercase;
                        }

                        .client-task-list-page .priority-badge.high {
                            background: rgba(239, 68, 68, 0.1);
                            color: #ef4444;
                        }

                        .client-task-list-page .priority-badge.urgent {
                            background: rgba(220, 38, 38, 0.15);
                            color: #dc2626;
                        }

                        .client-task-list-page .priority-badge.normal {
                            background: rgba(59, 130, 246, 0.1);
                            color: #3b82f6;
                        }

                        .client-task-list-page .priority-badge.low {
                            background: rgba(156, 163, 175, 0.1);
                            color: #9ca3af;
                        }

                        .client-task-list-page .empty-state {
                            text-align: center;
                            padding: 60px 20px;
                            color: var(--text-soft);
                        }

                        .client-task-list-page .empty-state-icon {
                            font-size: 3rem;
                            margin-bottom: 16px;
                            color: var(--text-faint);
                        }

                        .client-task-list-page .empty-state-text {
                            font-size: 1.1rem;
                            font-weight: 600;
                        }

                        .client-task-list-page .btn-back {
                            display: inline-flex;
                            align-items: center;
                            gap: 8px;
            padding: 10px 20px;
            background: var(--surface-soft);
            border: 1px solid var(--line);
            border-radius: var(--radius-md);
            color: var(--text);
            font-weight: 600;
            text-decoration: none;
            transition: all 0.18s ease;
        }

        .client-task-list-page .btn-back:hover {
            background: var(--surface);
            border-color: var(--text-soft);
        }
                    </style>

                    <div class="entry-header">
                        <div class="entry-breadcrumb">
                            <a href="<?= base_url('Page/clientDashboard'); ?>">Dashboard</a>
                            <span class="separator">/</span>
                            <span><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8'); ?></span>
                        </div>
                        <h1 class="entry-title"><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8'); ?></h1>
                        <?php if ($pageSubtitle !== ''): ?>
                            <p style="color: var(--text-soft); margin-top: 8px;"><?= htmlspecialchars($pageSubtitle, ENT_QUOTES, 'UTF-8'); ?></p>
                        <?php endif; ?>
                    </div>

                    <div class="entry-card">
                        <?php if (!empty($tasks)): ?>
                            <table class="task-table">
                                <thead>
                                    <tr>
                                        <th>Task</th>
                                        <th>Project</th>
                                        <th>Assigned To</th>
                                        <th>Priority</th>
                                        <?php if ($pageTitle === 'Accomplished'): ?>
                                            <th>Completed Date</th>
                                        <?php elseif ($pageTitle === 'Requested Today'): ?>
                                            <th>Reported Date</th>
                                        <?php else: ?>
                                            <th>Due Date</th>
                                        <?php endif; ?>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($tasks as $task): ?>
                                        <tr>
                                            <td>
                                                <strong><?= htmlspecialchars($task->task ?? 'N/A', ENT_QUOTES, 'UTF-8'); ?></strong>
                                            </td>
                                            <td><?= htmlspecialchars($task->projectDescription ?? 'N/A', ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td><?= htmlspecialchars($task->assignedPersonName ?? 'N/A', ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td>
                                                <?php
                                                    $priority = strtolower(trim((string) ($task->priority ?? 'normal')));
                                                    $priorityClass = in_array($priority, ['high', 'urgent', 'low']) ? $priority : 'normal';
                                                ?>
                                                <span class="priority-badge <?= htmlspecialchars($priorityClass, ENT_QUOTES, 'UTF-8'); ?>">
                                                    <?= htmlspecialchars(ucfirst($priority), ENT_QUOTES, 'UTF-8'); ?>
                                                </span>
                                            </td>
                                            <?php if ($pageTitle === 'Accomplished'): ?>
                                                <td><?= htmlspecialchars($task->completedDate ?? 'N/A', ENT_QUOTES, 'UTF-8'); ?></td>
                                            <?php elseif ($pageTitle === 'Requested Today'): ?>
                                                <td><?= htmlspecialchars($task->reportedDate ?? 'N/A', ENT_QUOTES, 'UTF-8'); ?></td>
                                            <?php else: ?>
                                                <td><?= htmlspecialchars($task->dueDate ?? 'N/A', ENT_QUOTES, 'UTF-8'); ?></td>
                                            <?php endif; ?>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php else: ?>
                            <div class="empty-state">
                                <div class="empty-state-icon">
                                    <i class="fas fa-tasks"></i>
                                </div>
                                <div class="empty-state-text">
                                    No <?= htmlspecialchars(strtolower($pageTitle), ENT_QUOTES, 'UTF-8'); ?> tasks found
                                </div>
                                <a href="<?= base_url('Page/clientDashboard'); ?>" class="btn-back" style="margin-top: 20px;">
                                    <i class="fas fa-arrow-left"></i>
                                    Back to Dashboard
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>

                    <?php if (!empty($tasks)): ?>
                        <div style="margin-top: 24px;">
                            <a href="<?= base_url('Page/clientDashboard'); ?>" class="btn-back">
                                <i class="fas fa-arrow-left"></i>
                                Back to Dashboard
                            </a>
                        </div>
                    <?php endif; ?>
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
