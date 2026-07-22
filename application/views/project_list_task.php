<?php
$currentLevel = strtolower(trim((string) $this->session->userdata('level')));
$isAdmin = ($currentLevel === 'admin');
$currentUser = (string) $this->session->userdata('username');
$currentUserId = (int) ($this->session->userdata('user_id') ?? 0);

// Package 2 detection
$taskPageSettingsId = (int) $this->session->userdata('settingsID');
$taskPageEnabledFeatures = array();
$taskPageIsPackage2 = false;

if ($taskPageSettingsId > 0 && $this->db->table_exists('company_features')) {
    $taskPageFeatureRows = $this->db
        ->select('feature_key')
        ->from('company_features')
        ->where('settingsID', $taskPageSettingsId)
        ->where('is_enabled', 1)
        ->get()
        ->result();

    foreach ($taskPageFeatureRows as $taskPageFeatureRow) {
        $taskPageFeatureKey = trim((string) ($taskPageFeatureRow->feature_key ?? ''));
        if ($taskPageFeatureKey !== '') {
            $taskPageEnabledFeatures[] = $taskPageFeatureKey;
        }
    }

    $taskPageEnabledFeatures = array_values(array_unique($taskPageEnabledFeatures));
    
    // Check if company is on Package 2 (Task Management Suite)
    // Package 2 features: tasks, notes, calendar
    $taskPagePackage2Features = array('tasks', 'notes', 'calendar');
    $taskPageIsPackage2 = count($taskPageEnabledFeatures) === count($taskPagePackage2Features) && 
                       count(array_intersect($taskPageEnabledFeatures, $taskPagePackage2Features)) === count($taskPagePackage2Features);
}

$taskRecords = isset($data) ? $data : array();
if ($taskRecords instanceof Traversable) {
    $taskRecords = iterator_to_array($taskRecords, false);
}
$taskRecords = is_array($taskRecords) ? array_values($taskRecords) : array();

$staffOptions = isset($data2) ? $data2 : array();
if ($staffOptions instanceof Traversable) {
    $staffOptions = iterator_to_array($staffOptions, false);
}
$staffOptions = is_array($staffOptions) ? array_values($staffOptions) : array();

$projectOptions = isset($data1) ? $data1 : array();
if ($projectOptions instanceof Traversable) {
    $projectOptions = iterator_to_array($projectOptions, false);
}
$projectOptions = is_array($projectOptions) ? array_values($projectOptions) : array();

$openTaskCount      = isset($openTaskCount)      ? (int) $openTaskCount      : 0;
$closedTaskCount    = isset($closedTaskCount)    ? (int) $closedTaskCount    : 0;
$dueTodayTaskCount  = isset($dueTodayTaskCount)  ? (int) $dueTodayTaskCount  : 0;
$dueSoonTaskCount   = isset($dueSoonTaskCount)   ? (int) $dueSoonTaskCount   : 0;
$overdueTaskCount   = isset($overdueTaskCount)   ? (int) $overdueTaskCount   : 0;
$undatedTaskCount   = isset($undatedTaskCount)   ? (int) $undatedTaskCount   : 0;
$taskDueWindowValue = isset($taskDueWindowDays)  ? (int) $taskDueWindowDays  : 7;
$statusFilter     = isset($statusFilter)         ? (string) $statusFilter    : 'open';
$taskScope        = isset($taskScope)            ? (string) $taskScope       : '';

$todayRaw        = date('Y-m-d');
$todayTimestamp  = strtotime($todayRaw);
$currentDateLabel = date('l, F j, Y');

$priorityLabels = array('1' => 'High', '2' => 'Medium', '3' => 'Low');
$priorityClasses = array('1' => 'priority-high', '2' => 'priority-medium', '3' => 'priority-low');
?>
<!DOCTYPE html>
<html lang="en">

<?php include('includes/head.php'); ?>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

<body>
    <div id="wrapper">

        <?php include('includes/top-nav-bar.php'); ?>
        <?php include('includes/sidebar.php'); ?>

        <div class="content-page">
            <div class="content">
                <div class="container-fluid task-workspace-page berps-page">



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

                    <!-- Page header -->
                    <header class="berps-page-header">
                        <div class="berps-page-header__content">
                            <span class="berps-page-header__eyebrow">Task Operations</span>
                            <h1 class="berps-page-title">Task List</h1>
                            <p class="berps-page-subtitle">Assign, track, and close out team tasks.</p>
                        </div>
                        <div class="berps-page-header__actions">
                            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addTaskModal">
                                <i class="mdi mdi-plus-circle-outline mr-1" aria-hidden="true"></i>Add New Task
                            </button>
                            <button type="button" class="btn btn-outline-secondary" data-toggle="modal" data-target="#bulkTaskUploadModal">
                                <i class="mdi mdi-upload-outline mr-1" aria-hidden="true"></i>Bulk Upload
                            </button>
                            <a href="<?= base_url(); ?>Page/downloadTaskBulkTemplate" class="btn btn-outline-secondary">
                                <i class="mdi mdi-download-outline mr-1" aria-hidden="true"></i>Download Template
                            </a>
                            <a href="<?= base_url(); ?>Page/accomplishments" class="btn btn-outline-secondary">
                                <i class="mdi mdi-trophy-outline mr-1" aria-hidden="true"></i>Accomplishments
                            </a>
                        </div>
                    </header>

                    <!-- Stat cards -->
                    <div class="berps-stat-grid">
                        <div class="berps-stat-card">
                            <div>
                                <p class="berps-stat-card__value"><?= number_format($openTaskCount); ?></p>
                                <p class="berps-stat-card__label">Open Tasks</p>
                            </div>
                            <span class="berps-stat-card__icon" aria-hidden="true"><i class="mdi mdi-folder-open-outline"></i></span>
                        </div>
                        <div class="berps-stat-card berps-tone-warning">
                            <div>
                                <p class="berps-stat-card__value"><?= number_format($dueTodayTaskCount); ?></p>
                                <p class="berps-stat-card__label">Due Today</p>
                            </div>
                            <span class="berps-stat-card__icon" aria-hidden="true"><i class="mdi mdi-calendar-clock"></i></span>
                        </div>
                        <div class="berps-stat-card berps-tone-danger">
                            <div>
                                <p class="berps-stat-card__value"><?= number_format($overdueTaskCount); ?></p>
                                <p class="berps-stat-card__label">Overdue</p>
                            </div>
                            <span class="berps-stat-card__icon" aria-hidden="true"><i class="mdi mdi-alert-circle-outline"></i></span>
                        </div>
                        <div class="berps-stat-card berps-tone-success">
                            <div>
                                <p class="berps-stat-card__value"><?= number_format($closedTaskCount); ?></p>
                                <p class="berps-stat-card__label">Accomplished</p>
                            </div>
                            <span class="berps-stat-card__icon" aria-hidden="true"><i class="mdi mdi-check-circle-outline"></i></span>
                        </div>
                    </div>

                    <!-- Banner strip -->
                    <?php if ($undatedTaskCount > 0): ?>
                        <div class="theme-banner banner-warning">
                            <i class="mdi mdi-alert-outline"></i>
                            <span class="banner-title"><?= number_format($undatedTaskCount); ?> task(s) have no due date —</span>
                            <span class="banner-copy">add one during update so dashboards can track them.</span>
                            <span class="banner-date"><?= htmlspecialchars($currentDateLabel, ENT_QUOTES, 'UTF-8'); ?></span>
                        </div>
                    <?php else: ?>
                        <div class="theme-banner banner-info">
                            <i class="mdi mdi-check-circle-outline"></i>
                            <span class="banner-title">All tasks have due dates</span>
                            <span class="banner-copy">— deadline tracking is fully active.</span>
                            <span class="banner-date"><?= htmlspecialchars($currentDateLabel, ENT_QUOTES, 'UTF-8'); ?></span>
                        </div>
                    <?php endif; ?>

                    <!-- Task register card -->
                    <div class="theme-card">
                        <div class="theme-card-head" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
                            <div>
                                <h5 class="theme-card-title">
                                    <?=
                                        $taskScope === 'forwarded'
                                        ? 'Forwarded Task Queue'
                                        : ($statusFilter === 'closed' ? 'Closed Task Register' : ($statusFilter === 'all' ? 'All Task Register' : 'Open Task Register'));
                                    ?>
                                </h5>
                                <div class="theme-card-subtitle">
                                    <?= $taskScope === 'forwarded'
                                        ? 'Forwarded tasks assigned to you that still need your first action.'
                                        : 'Newest tasks appear first so fresh entries are immediately visible to the team.'; ?>
                                </div>
                            </div>
                            <div class="btn-group" role="group" aria-label="Task status filter">
                                <a href="<?= base_url('Page/projectAddTask?status=open' . ($taskScope !== '' ? '&scope=' . rawurlencode($taskScope) : '')); ?>" class="btn btn-sm <?= $statusFilter === 'open' ? 'btn-primary' : 'btn-outline-primary'; ?>">
                                    <i class="mdi mdi-folder-open"></i> Open
                                </a>
                                <a href="<?= base_url('Page/projectAddTask?status=closed' . ($taskScope !== '' ? '&scope=' . rawurlencode($taskScope) : '')); ?>" class="btn btn-sm <?= $statusFilter === 'closed' ? 'btn-success' : 'btn-outline-success'; ?>">
                                    <i class="mdi mdi-check-circle"></i> Closed
                                </a>
                                <a href="<?= base_url('Page/projectAddTask?status=all' . ($taskScope !== '' ? '&scope=' . rawurlencode($taskScope) : '')); ?>" class="btn btn-sm <?= $statusFilter === 'all' ? 'btn-secondary' : 'btn-outline-secondary'; ?>">
                                    <i class="mdi mdi-format-list-bulleted"></i> All
                                </a>
                            </div>
                        </div>

                        <div class="theme-card-body">
                            <div class="table-responsive">
                                <table id="task-table" class="table task-table">
                                    <thead>
                                        <tr>
                                            <th>Task</th>
                                            <th>Reported</th>
                                            <th>Due Date</th>
                                            <?php if ($isAdmin): ?>
                                                <th>Assigned To</th>
                                            <?php endif; ?>
                                            <th>Project</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($taskRecords)): ?>
                                            <?php foreach ($taskRecords as $row): ?>
                                                <?php
                                                $taskId           = (int) ($row->taskID ?? 0);
                                                $taskTextRaw      = trim((string) ($row->task ?? ''));
                                                $taskText         = $taskTextRaw !== '' ? $taskTextRaw : 'Untitled task';
                                                $taskTextDisplay  = function_exists('mb_strtoupper')
                                                    ? mb_strtoupper($taskText, 'UTF-8')
                                                    : strtoupper($taskText);
                                                $taskLink         = base_url('Page/taskStat?id=') . $taskId;
                                                $assignedPersonRaw = trim((string) ($row->assignedPersonName ?? ''));
                                                if ($assignedPersonRaw === '') {
                                                    $assignedPersonRaw = trim((string) ($row->assignedPerson ?? 'Unassigned'));
                                                }
                                                $projectDescRaw  = trim((string) ($row->projectDescription ?? ''));
                                                $projectDescRaw  = $projectDescRaw !== '' ? $projectDescRaw : 'No project';
                                                $reportedDateRaw = trim((string) ($row->reportedDate ?? ''));
                                                $reportedDisplay = ($reportedDateRaw !== '' && $reportedDateRaw !== '0000-00-00')
                                                    ? date('M j, Y', strtotime($reportedDateRaw))
                                                    : 'Not set';
                                                $dueDateRaw  = trim((string) ($row->dueDate ?? ''));
                                                $hasDueDate  = ($dueDateRaw !== '' && $dueDateRaw !== '0000-00-00');
                                                $dueOrder    = $hasDueDate ? $dueDateRaw : '9999-12-31';
                                                $dueDisplay  = $hasDueDate ? date('M j, Y', strtotime($dueDateRaw)) : 'Not set';
                                                $dueMetaClass  = 'is-upcoming';
                                                $dueMetaLabel  = 'No due date set yet';
                                                if ($hasDueDate) {
                                                    $dayDiff = (int) floor((strtotime($dueDateRaw) - $todayTimestamp) / 86400);
                                                    if ($dayDiff < 0) {
                                                        $dueMetaClass = 'is-overdue';
                                                        $dueMetaLabel = 'Overdue by ' . number_format(abs($dayDiff)) . ' day(s)';
                                                    } elseif ($dayDiff === 0) {
                                                        $dueMetaClass = 'is-due-today';
                                                        $dueMetaLabel = 'Due today';
                                                    } else {
                                                        $dueMetaClass = 'is-upcoming';
                                                        $dueMetaLabel = 'Due in ' . number_format($dayDiff) . ' day(s)';
                                                    }
                                                }
                                                $priorityRaw   = (string) ($row->priority ?? '2');
                                                $priorityLabel = isset($priorityLabels[$priorityRaw]) ? $priorityLabels[$priorityRaw] : 'Medium';
                                                $priorityClass = isset($priorityClasses[$priorityRaw]) ? $priorityClasses[$priorityRaw] : 'priority-medium';
                                                $attachmentUrlRaw  = trim((string) ($row->attachment_link ?? ''));
                                                $adminCommentRaw   = trim((string) ($row->latestAdminComment ?? ''));
                                                $adminCommentId    = isset($row->latestAdminCommentId) ? (int) $row->latestAdminCommentId : 0;
                                                $hasAdminComment   = ($adminCommentRaw !== '');
                                                $assignedId        = trim((string) ($row->assignedPerson ?? ''));
                                                $supportIssueId    = isset($row->supportIssueId) ? (int) $row->supportIssueId : 0;
                                                $supportTicketRaw  = trim((string) ($row->supportTicketNumber ?? ''));
                                                $assignedLink      = base_url('Page/employeeTaskData2?name=') . rawurlencode($assignedId);
                                                $linkedProjectId   = isset($row->linkedProjectId) ? (int) $row->linkedProjectId : (int) ($row->projectID ?? 0);
                                                $projectLink       = $linkedProjectId > 0 ? base_url('Page/taskPerProject?projectID=') . rawurlencode((string) $linkedProjectId) : '';
                                                $supportIssueLink  = $supportIssueId > 0 ? base_url('Page/supportIssueView?id=') . $supportIssueId : '';
                                                $supportTicketLabel = $supportTicketRaw !== '' ? $supportTicketRaw : ('#' . $supportIssueId);
                                                $taskAddedBy = trim((string) ($row->added_by ?? ''));
                                                /* Build initials from assignedPersonRaw */
                                                $nameParts = explode(' ', $assignedPersonRaw);
                                                $initials  = '';
                                                foreach (array_slice($nameParts, 0, 2) as $part) {
                                                    $initials .= strtoupper(substr(trim($part), 0, 1));
                                                }
                                                $initials = $initials !== '' ? $initials : 'NA';
                                                ?>
                                                <tr>
                                                    <td>
                                                        <a href="<?= htmlspecialchars($taskLink, ENT_QUOTES, 'UTF-8'); ?>" class="task-title-link">
                                                            <?= htmlspecialchars($taskTextDisplay, ENT_QUOTES, 'UTF-8'); ?>
                                                        </a>
                                                        <div class="task-meta-row">
                                                            <span class="priority-badge <?= htmlspecialchars($priorityClass, ENT_QUOTES, 'UTF-8'); ?>">
                                                                <?= htmlspecialchars($priorityLabel, ENT_QUOTES, 'UTF-8'); ?>
                                                            </span>
                                                            <?php if ($attachmentUrlRaw !== ''): ?>
                                                                <a href="<?= htmlspecialchars($attachmentUrlRaw, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener noreferrer" class="attachment-link">
                                                                    <i class="mdi mdi-paperclip"></i> View Attachment
                                                                </a>
                                                            <?php endif; ?>
                                                            <?php if ($supportIssueId > 0): ?>
                                                                <a href="<?= htmlspecialchars($supportIssueLink, ENT_QUOTES, 'UTF-8'); ?>" class="attachment-link">
                                                                    <i class="mdi mdi-lifebuoy"></i> View Ticket <?= htmlspecialchars($supportTicketLabel, ENT_QUOTES, 'UTF-8'); ?>
                                                                </a>
                                                            <?php endif; ?>
                                                        </div>
                                                    </td>
                                                    <td data-order="<?= htmlspecialchars($reportedDateRaw !== '' ? $reportedDateRaw : '9999-12-31', ENT_QUOTES, 'UTF-8'); ?>">
                                                        <div class="cell-muted"><?= htmlspecialchars($reportedDisplay, ENT_QUOTES, 'UTF-8'); ?></div>
                                                    </td>
                                                    <td data-order="<?= htmlspecialchars($dueOrder, ENT_QUOTES, 'UTF-8'); ?>">
                                                        <div class="due-date-value"><?= htmlspecialchars($dueDisplay, ENT_QUOTES, 'UTF-8'); ?></div>
                                                        <div class="due-date-meta <?= htmlspecialchars($dueMetaClass, ENT_QUOTES, 'UTF-8'); ?>"><?= htmlspecialchars($dueMetaLabel, ENT_QUOTES, 'UTF-8'); ?></div>
                                                    </td>
                                                    <?php if ($isAdmin): ?>
                                                        <td>
                                                            <div class="person-cell">
                                                                <div class="person-avatar"><?= htmlspecialchars($initials, ENT_QUOTES, 'UTF-8'); ?></div>
                                                                <a href="<?= htmlspecialchars($assignedLink, ENT_QUOTES, 'UTF-8'); ?>" class="task-title-link is-muted">
                                                                    <?= htmlspecialchars($assignedPersonRaw, ENT_QUOTES, 'UTF-8'); ?>
                                                                </a>
                                                            </div>
                                                        </td>
                                                    <?php endif; ?>
                                                    <td>
                                                        <?php if ($linkedProjectId > 0): ?>
                                                            <a href="<?= htmlspecialchars($projectLink, ENT_QUOTES, 'UTF-8'); ?>" class="project-tag">
                                                                <?= htmlspecialchars($projectDescRaw, ENT_QUOTES, 'UTF-8'); ?>
                                                            </a>
                                                        <?php else: ?>
                                                            <span class="project-tag">
                                                                <?= htmlspecialchars($projectDescRaw, ENT_QUOTES, 'UTF-8'); ?>
                                                            </span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <div class="task-actions">
                                                            <button type="button" class="action-menu-btn" data-task-id="<?= $taskId; ?>" aria-label="Actions">
                                                                <i class="fa-solid fa-ellipsis-vertical"></i>
                                                            </button>
                                                            <div class="action-dropdown" id="dropdown-<?= $taskId; ?>">
                                                                <a href="<?= htmlspecialchars($taskLink, ENT_QUOTES, 'UTF-8'); ?>" class="action-dropdown-item action-view">
                                                                    <i class="fa-regular fa-eye"></i> View
                                                                </a>
                                                                <?php if ($supportIssueId > 0): ?>
                                                                    <a href="<?= htmlspecialchars($supportIssueLink, ENT_QUOTES, 'UTF-8'); ?>" class="action-dropdown-item action-view">
                                                                        <i class="fa-solid fa-lifebuoy"></i> View Ticket
                                                                    </a>
                                                                <?php endif; ?>
                                                                <button type="button" class="action-dropdown-item action-update"
                                                                    data-toggle="modal" data-target="#updateTaskModal"
                                                                    data-id="<?= $taskId; ?>"
                                                                    data-task="<?= htmlspecialchars($taskTextDisplay, ENT_QUOTES, 'UTF-8'); ?>"
                                                                    data-priority="<?= htmlspecialchars($priorityRaw, ENT_QUOTES, 'UTF-8'); ?>"
                                                                    data-project="<?= htmlspecialchars((string) ($row->projectID ?? ''), ENT_QUOTES, 'UTF-8'); ?>"
                                                                    data-reported="<?= htmlspecialchars(($reportedDateRaw !== '0000-00-00' ? $reportedDateRaw : ''), ENT_QUOTES, 'UTF-8'); ?>"
                                                                    data-assigned="<?= htmlspecialchars($assignedId, ENT_QUOTES, 'UTF-8'); ?>"
                                                                    data-attachment="<?= htmlspecialchars($attachmentUrlRaw, ENT_QUOTES, 'UTF-8'); ?>"
                                                                    data-client-comment="<?= htmlspecialchars((string) ($row->client_comment ?? ''), ENT_QUOTES, 'UTF-8'); ?>"
                                                                    data-due="<?= htmlspecialchars($hasDueDate ? $dueDateRaw : '', ENT_QUOTES, 'UTF-8'); ?>">
                                                                    <i class="fa-solid fa-pen-to-square"></i> Update
                                                                </button>
                                                                <button type="button" class="action-dropdown-item action-status"
                                                                    data-toggle="modal" data-target="#addstatus" data-task-id="<?= $taskId; ?>">
                                                                    <i class="fa-solid fa-clipboard-check"></i> Add Status
                                                                </button>
                                                                <button type="button" class="action-dropdown-item action-forward"
                                                                    data-toggle="modal" data-target="#forwardTaskModal"
                                                                    data-task-id="<?= $taskId; ?>"
                                                                    data-task="<?= htmlspecialchars($taskTextDisplay, ENT_QUOTES, 'UTF-8'); ?>">
                                                                    <i class="fa-solid fa-share"></i> Forward
                                                                </button>
                                                                <?php if ($hasAdminComment || $isAdmin): ?>
                                                                    <button type="button" class="action-dropdown-item action-comment"
                                                                        data-toggle="modal" data-target="#adminCommentModal"
                                                                        data-task-id="<?= $taskId; ?>"
                                                                        data-pts-id="<?= $adminCommentId > 0 ? htmlspecialchars((string) $adminCommentId, ENT_QUOTES, 'UTF-8') : ''; ?>"
                                                                        data-task="<?= htmlspecialchars($taskTextDisplay, ENT_QUOTES, 'UTF-8'); ?>"
                                                                        data-comment="<?= htmlspecialchars($adminCommentRaw, ENT_QUOTES, 'UTF-8'); ?>">
                                                                        <i class="fa-regular fa-comment-dots"></i> <?= $hasAdminComment ? 'View Comment' : 'Add Comment'; ?>
                                                                    </button>
                                                                <?php endif; ?>
                                                                <?php if ($isAdmin || ($taskAddedBy !== '' && $taskAddedBy === $currentUser)): ?>
                                                                    <div class="action-dropdown-divider"></div>
                                                                    <a href="<?= htmlspecialchars(base_url('Page/deleteTask/') . $taskId, ENT_QUOTES, 'UTF-8'); ?>"
                                                                        class="action-dropdown-item action-delete"
                                                                        onclick="return confirm('Are you sure you want to delete this task?');">
                                                                        <i class="fa-solid fa-trash"></i> Delete
                                                                    </a>
                                                                <?php endif; ?>
                                                            </div>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                            <?php if (empty($taskRecords)): ?>
                                <div class="empty-state">
                                    <?php if ($statusFilter === 'closed'): ?>
                                        No closed tasks are recorded yet.
                                    <?php elseif ($taskScope === 'forwarded'): ?>
                                        No forwarded tasks are waiting for your action.
                                    <?php elseif ($statusFilter === 'all'): ?>
                                        No tasks are recorded yet.
                                    <?php else: ?>
                                        No open tasks are recorded yet.
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                </div>
            </div>

            <?php include('includes/footer.php'); ?>
        </div>
    </div>

    <?php include('includes/themecustomizer.php'); ?>

    <!-- ───────── MODALS ───────── -->

    <!-- Add Status -->
    <div class="modal fade berps-form-modal" id="addstatus" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title mb-0">Add Task Status</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <form class="needs-validation" id="addStatusForm" method="post" action="<?= base_url(); ?>Page/addTaskNote" novalidate>
                    <div class="modal-body">
                        <input type="hidden" name="dataid" id="status_task_id" value="">
                        <div class="form-group">
                            <label for="status_note">Notes</label>
                            <textarea class="form-control" id="status_note" name="note" rows="4"></textarea>
                        </div>
                        <div class="form-group mb-0">
                            <label for="status_taskStat">Current Status</label>
                            <select class="custom-select" id="status_taskStat" name="taskStat">
                                <option value="1">Open</option>
                                <option value="0">Closed</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" name="add_task_stat" class="btn btn-primary">Save Status</button>
                        <button type="reset" class="btn btn-light">Reset</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Add New Task -->
    <div class="modal fade berps-form-modal" id="addTaskModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title mb-0">Add New Task</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <form class="needs-validation" id="addNewTaskForm" method="post" novalidate>
                    <input type="hidden" name="add_task" value="1">
                    <div class="modal-body">
                        <?php if (!$taskPageIsPackage2): ?>
                        <div class="form-group">
                            <label for="new_task_project">Project</label>
                            <select class="custom-select" id="new_task_project" name="project" required>
                                <option value="">-- Select Project --</option>
                                <?php foreach ($projectOptions as $project): ?>
                                    <option value="<?= htmlspecialchars((string) ($project->projectID ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
                                        <?= htmlspecialchars((string) ($project->projectDescription ?? ''), ENT_QUOTES, 'UTF-8'); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <?php else: ?>
                        <input type="hidden" name="project" value="">
                        <?php endif; ?>
                        <div class="form-group">
                            <label for="new_task_name">Task</label>
                            <input type="text" class="form-control" id="new_task_name" name="task" required>
                        </div>
                        <div class="form-group">
                            <label for="new_task_attachment">Attachment Link, if any</label>
                            <input type="url" class="form-control" id="new_task_attachment" name="attachment_link" placeholder="https://example.com/attachment">
                        </div>
                        
                        <!-- Task Checklist Section -->
                        <div class="form-group">
                            <label>Task Checklist <small class="text-muted">(Optional - Add breakdown items for this task)</small></label>
                            <div id="taskChecklistContainer">
                                <div class="checklist-item">
                                    <div class="input-group mb-2">
                                        <input type="text" class="form-control checklist-item-input" placeholder="Enter checklist item..." name="checklist_items[]">
                                        <div class="input-group-append">
                                            <button type="button" class="btn btn-outline-success add-checklist-item" title="Add more items">
                                                <i class="fas fa-plus"></i>
                                            </button>
                                            <button type="button" class="btn btn-outline-danger remove-checklist-item" title="Remove this item" style="display: none;">
                                                <i class="fas fa-minus"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <small class="form-text text-muted">Each completed checklist item will be counted as an accomplishment when the task is closed.</small>
                        </div>
                        <?php if ($isAdmin): ?>
                            <div class="form-row">
                                <div class="form-group col-md-3">
                                    <label for="new_task_reportedDate">Reported Date</label>
                                    <input type="date" class="form-control" id="new_task_reportedDate" name="reportedDate" value="<?= htmlspecialchars($todayRaw, ENT_QUOTES, 'UTF-8'); ?>" required>
                                </div>
                                <div class="form-group col-md-3">
                                    <label for="new_task_dueDate">Due Date</label>
                                    <input type="date" class="form-control" id="new_task_dueDate" name="dueDate" value="<?= htmlspecialchars($todayRaw, ENT_QUOTES, 'UTF-8'); ?>" required>
                                </div>
                                <div class="form-group col-md-4">
                                    <label for="new_task_assignedPerson">Assigned Staff</label>
                                    <select class="select2-staff" id="new_task_assignedPerson" name="assignedPerson" required style="width: 100%;">
                                        <option value="">-- Select User --</option>
                                        <?php if ($currentUserId > 0): ?>
                                            <option value="<?= htmlspecialchars((string) $currentUserId, ENT_QUOTES, 'UTF-8'); ?>">Me (<?= htmlspecialchars($currentUser, ENT_QUOTES, 'UTF-8'); ?>)</option>
                                        <?php endif; ?>
                                        <?php foreach ($staffOptions as $staff): ?>
                                            <?php if ((int) ($staff->user_id ?? 0) !== (int) ($currentUserId ?? 0)): ?>
                                            <option value="<?= htmlspecialchars((string) ($staff->user_id ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
                                                <?= htmlspecialchars(trim((string) (($staff->lName ?? '') . ', ' . ($staff->fName ?? ''))), ENT_QUOTES, 'UTF-8'); ?>
                                            </option>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group col-md-2">
                                    <label for="new_task_priority">Priority</label>
                                    <select class="custom-select" id="new_task_priority" name="priority" required>
                                        <option value="1">High</option>
                                        <option value="2" selected>Medium</option>
                                        <option value="3">Low</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group col-md-4">
                                    <label for="new_task_points">Task Points</label>
                                    <input type="number" class="form-control" id="new_task_points" name="points" min="1" value="1" required>
                                    <small class="form-text text-muted">Points awarded when this task is completed.</small>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="form-row">
                                <div class="form-group col-md-4">
                                    <label for="new_task_reportedDate_staff">Reported Date</label>
                                    <input type="date" class="form-control" id="new_task_reportedDate_staff" name="reportedDate" value="<?= htmlspecialchars($todayRaw, ENT_QUOTES, 'UTF-8'); ?>" required>
                                </div>
                                <div class="form-group col-md-4">
                                    <label for="new_task_dueDate_staff">Due Date</label>
                                    <input type="date" class="form-control" id="new_task_dueDate_staff" name="dueDate" value="<?= htmlspecialchars($todayRaw, ENT_QUOTES, 'UTF-8'); ?>" required>
                                </div>
                                <div class="form-group col-md-4">
                                    <label for="new_task_priority_staff">Priority</label>
                                    <select class="custom-select" id="new_task_priority_staff" name="priority" required>
                                        <option value="1">High</option>
                                        <option value="2" selected>Medium</option>
                                        <option value="3">Low</option>
                                    </select>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Add Task</button>
                        <button type="reset" class="btn btn-light">Reset</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bulk Upload Tasks -->
    <div class="modal fade berps-form-modal" id="bulkTaskUploadModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title mb-0">Bulk Upload Tasks</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <form method="post" action="<?= base_url('Page/bulkUploadTasks'); ?>" enctype="multipart/form-data">
                    <div class="modal-body">
                        <div class="bulk-upload-note mb-3">
                            <strong>Template guide</strong>
                            <ul>
                                <li>Download the template first, then fill it out in Excel or any spreadsheet app.</li>
                                <li>Required columns: `Task` and either `Project ID` or `Project Name`.</li>
                                <li>Accepted upload formats: `CSV` and `Excel (.xlsx)`.</li>
                                <li>`Priority` accepts `High`, `Medium`, `Low`, or `1`, `2`, `3`.</li>
                                <?php if ($isAdmin): ?>
                                    <li>`Assigned User ID` is required for admin bulk uploads.</li>
                                <?php else: ?>
                                    <li>For staff uploads, tasks will automatically be assigned to your account.</li>
                                <?php endif; ?>
                            </ul>
                        </div>

                        <div class="form-group">
                            <label for="task_file">Task File</label>
                            <div class="custom-file">
                                <input type="file" class="custom-file-input" id="task_file" name="task_file" accept=".csv,.xlsx" required>
                                <label class="custom-file-label" for="task_file">Choose CSV or Excel file...</label>
                            </div>
                            <small class="form-text text-muted">Maximum file size: 5MB. Use the provided template for the correct columns.</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <a href="<?= base_url(); ?>Page/downloadTaskBulkTemplate" class="btn btn-light">Download Template</a>
                        <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="mdi mdi-upload-outline"></i>
                            Upload Tasks
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Update Task -->
    <div class="modal fade berps-form-modal" id="updateTaskModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title mb-0">Update Task</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <form class="needs-validation" id="updateTaskForm" method="POST" action="<?= base_url('Page/updateTask'); ?>" novalidate>
                    <input type="hidden" name="update_task" value="1">
                    <div class="modal-body">
                        <input type="hidden" name="taskID" id="update_taskID">
                        <?php if (!$taskPageIsPackage2): ?>
                        <div class="form-group">
                            <label for="update_project">Project</label>
                            <select class="custom-select" id="update_project" name="project" required>
                                <option value="">-- Select Project --</option>
                                <?php foreach ($projectOptions as $proj): ?>
                                    <option value="<?= htmlspecialchars((string) ($proj->projectID ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
                                        <?= htmlspecialchars((string) ($proj->projectDescription ?? ''), ENT_QUOTES, 'UTF-8'); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <?php else: ?>
                        <input type="hidden" name="project" id="update_project" value="">
                        <?php endif; ?>
                        <div class="form-group">
                            <label for="update_task">Task</label>
                            <input type="text" class="form-control" id="update_task" name="task" required>
                        </div>
                        <div class="form-group">
                            <label for="update_attachment">Attachment Link, if any</label>
                            <input type="url" class="form-control" id="update_attachment" name="attachment_link" placeholder="https://example.com/attachment">
                        </div>
                        <?php if ($isAdmin): ?>
                            <div class="form-group">
                                <label for="update_client_comment">Client Comment</label>
                                <textarea class="form-control" id="update_client_comment" name="client_comment" rows="3" placeholder="Add a comment the client can see while this task is pending."></textarea>
                                <small class="form-text text-muted">This comment is visible in the client pending task page.</small>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Task Checklist Section for Update -->
                        <div class="form-group">
                            <label>Task Checklist</label>
                            <div id="updateTaskChecklistContainer">
                                <div class="checklist-items-loading text-muted">
                                    <i class="fas fa-spinner fa-spin"></i> Loading checklist items...
                                </div>
                            </div>
                            <div class="mt-2">
                                <button type="button" class="btn btn-sm btn-outline-primary" id="addChecklistItemBtn">
                                    <i class="fas fa-plus"></i> Add Item
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-success" id="saveChecklistBtn" style="display: none;">
                                    <i class="fas fa-save"></i> Save Checklist
                                </button>
                            </div>
                            <small class="form-text text-muted">Check off completed items. Each completed item counts as an accomplishment when the task is closed.</small>
                        </div>
                        <div class="form-row">
                            <div class="form-group <?= $isAdmin ? 'col-md-3' : 'col-md-6'; ?>">
                                <label for="update_reportedDate">Reported Date</label>
                                <input type="date" class="form-control" id="update_reportedDate" name="reportedDate" required>
                            </div>
                            <div class="form-group <?= $isAdmin ? 'col-md-3' : 'col-md-6'; ?>">
                                <label for="update_dueDate">Due Date</label>
                                <input type="date" class="form-control" id="update_dueDate" name="dueDate" required>
                            </div>
                            <?php if ($isAdmin): ?>
                                <div class="form-group col-md-4">
                                    <label for="update_assignedPerson">Assigned Person</label>
                                    <select class="select2-staff" id="update_assignedPerson" name="assignedPerson" required style="width: 100%;">
                                        <option value="">-- Select User --</option>
                                        <?php if ($currentUserId > 0): ?>
                                            <option value="<?= htmlspecialchars((string) $currentUserId, ENT_QUOTES, 'UTF-8'); ?>">Me (<?= htmlspecialchars($currentUser, ENT_QUOTES, 'UTF-8'); ?>)</option>
                                        <?php endif; ?>
                                        <?php foreach ($staffOptions as $staff): ?>
                                            <?php if ((int) ($staff->user_id ?? 0) !== (int) ($currentUserId ?? 0)): ?>
                                            <option value="<?= htmlspecialchars((string) ($staff->user_id ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
                                                <?= htmlspecialchars(trim((string) (($staff->lName ?? '') . ', ' . ($staff->fName ?? ''))), ENT_QUOTES, 'UTF-8'); ?>
                                            </option>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            <?php endif; ?>
                            <div class="form-group <?= $isAdmin ? 'col-md-2' : 'col-md-6'; ?>">
                                <label for="update_priority">Priority</label>
                                <select class="custom-select" id="update_priority" name="priority" required>
                                    <option value="1">High</option>
                                    <option value="2">Medium</option>
                                    <option value="3">Low</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Update Task</button>
                        <button type="button" class="btn btn-light" data-dismiss="modal">Close</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Admin Comment -->
    <div class="modal fade berps-form-modal" id="adminCommentModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title mb-0">Admin Comment</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <form method="post" action="<?= base_url(); ?>Page/saveTaskComment">
                    <div class="modal-body">
                        <input type="hidden" name="task_id" id="admin-comment-task-id">
                        <input type="hidden" name="pts_id" id="admin-comment-pts-id">
                        <div class="form-group">
                            <label for="admin-comment-task">Task</label>
                            <input type="text" class="form-control" id="admin-comment-task" readonly>
                        </div>
                        <div class="form-group mb-0">
                            <label for="admin-comment-note">Comment</label>
                            <textarea class="form-control" id="admin-comment-note" name="note" rows="4" <?= $isAdmin ? '' : 'readonly'; ?>></textarea>
                            <small class="form-text text-muted">Saved comments are also shown to the client while the task remains pending.</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <?php if ($isAdmin): ?>
                            <button type="submit" class="btn btn-primary">Save Comment</button>
                        <?php endif; ?>
                        <button type="button" class="btn btn-light" data-dismiss="modal">Close</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Forward Task -->
    <div class="modal fade berps-form-modal" id="forwardTaskModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title mb-0">Forward Task</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <form method="POST" action="<?= base_url('Page/forwardTask'); ?>">
                    <div class="modal-body">
                        <input type="hidden" name="taskID" id="forward_task_id">
                        <div class="form-group">
                            <label for="forward_task_name">Task</label>
                            <input type="text" class="form-control" id="forward_task_name" readonly>
                        </div>
                        <div class="form-group">
                            <label for="forward_to">Forward To</label>
                            <select class="select2-staff" id="forward_to" name="forwardTo" required style="width: 100%;">
                                <option value="">-- Select Employee --</option>
                                <?php foreach ($staffOptions as $staff): ?>
                                    <option value="<?= htmlspecialchars((string) ($staff->user_id ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
                                        <?= htmlspecialchars(trim((string) (($staff->lName ?? '') . ', ' . ($staff->fName ?? ''))), ENT_QUOTES, 'UTF-8'); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group mb-0">
                            <label for="forward_note">Note</label>
                            <textarea class="form-control" id="forward_note" name="forwardNote" rows="4" placeholder="Add context before forwarding the task..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" name="forward_task" value="1" class="btn btn-primary">Forward Task</button>
                        <button type="button" class="btn btn-light" data-dismiss="modal">Close</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- ───────── SCRIPTS ───────── -->
    <script>
        var hasTimeInToday = <?= isset($hasTimeInToday) && $hasTimeInToday ? 'true' : 'false'; ?>;
        var isAdmin = <?= $isAdmin ? 'true' : 'false'; ?>;
        var isPackage2 = <?= $taskPageIsPackage2 ? 'true' : 'false'; ?>;
    </script>
    <script src="<?= base_url(); ?>assets/js/vendor.min.js"></script>
    <script src="<?= base_url(); ?>assets/libs/sweetalert2/sweetalert2.min.js"></script>
    <script src="<?= base_url(); ?>assets/libs/datatables/jquery.dataTables.min.js"></script>
    <script src="<?= base_url(); ?>assets/libs/datatables/dataTables.bootstrap4.min.js"></script>
    <script src="<?= base_url(); ?>assets/libs/datatables/dataTables.responsive.min.js"></script>
    <script src="<?= base_url(); ?>assets/libs/datatables/responsive.bootstrap4.min.js"></script>
    <script src="<?= base_url(); ?>assets/js/app.min.js"></script>
    <script>
        (function($) {
            'use strict';

            function guardAttendance(event, message) {
                if (!hasTimeInToday && !isAdmin && !isPackage2) {
                    event.preventDefault();
                    Swal.fire({
                        icon: 'warning',
                        title: 'Attendance Required',
                        text: message,
                        confirmButtonColor: '#2563eb',
                        confirmButtonText: 'OK'
                    });
                    return false;
                }
                return true;
            }

            $(function() {

                /* DataTable */
                var $taskTable = $('#task-table');
                if ($taskTable.length) {
                    $taskTable.DataTable({
                        responsive: true,
                        autoWidth: false,
                        pageLength: 10,
                        order: [
                            [1, 'desc']
                        ],
                        language: {
                            emptyTable: '<?= $taskScope === "forwarded"
                                ? "No forwarded tasks are waiting for your action."
                                : ($statusFilter === "closed"
                                    ? "No closed tasks are recorded yet."
                                    : ($statusFilter === "all" ? "No tasks are recorded yet." : "No open tasks are recorded yet.")); ?>'
                        },
                        columnDefs: [{
                            targets: -1,
                            orderable: false,
                            searchable: false
                        }]
                    });
                }

                /* Attendance guards */
                $('#addStatusForm').on('submit', function(e) {
                    return guardAttendance(e, "You cannot update task status because you don't have attendance today. Please time in first.");
                });
                $('#updateTaskForm').on('submit', function(e) {
                    return guardAttendance(e, "You cannot update this task because you don't have attendance today. Please time in first.");
                });
                $('#addNewTaskForm').on('submit', function(e) {
                    return guardAttendance(e, "You cannot add a task because you don't have attendance today. Please time in first.");
                });

                /* Sync due date with reported date in Add Task modal */
                var addReportedDate = document.getElementById('new_task_reportedDate') || document.getElementById('new_task_reportedDate_staff');
                var addDueDate = document.getElementById('new_task_dueDate') || document.getElementById('new_task_dueDate_staff');
                var lastReportedDateValue = addReportedDate ? addReportedDate.value : '';
                var updateReportedDate = document.getElementById('update_reportedDate');
                var updateDueDate = document.getElementById('update_dueDate');
                var lastUpdateReportedDateValue = updateReportedDate ? updateReportedDate.value : '';

                function syncAddDueDate() {
                    if (!addReportedDate || !addDueDate) {
                        return;
                    }
                    if (addDueDate.value === '' || addDueDate.value === lastReportedDateValue) {
                        addDueDate.value = addReportedDate.value;
                    }
                    lastReportedDateValue = addReportedDate.value;
                }

                function syncUpdateDueDate() {
                    if (!updateReportedDate || !updateDueDate) {
                        return;
                    }
                    if (updateDueDate.value === '' || updateDueDate.value === lastUpdateReportedDateValue) {
                        updateDueDate.value = updateReportedDate.value;
                    }
                    lastUpdateReportedDateValue = updateReportedDate.value;
                }

                if (addReportedDate && addDueDate) {
                    addReportedDate.addEventListener('change', syncAddDueDate);
                }

                if (updateReportedDate && updateDueDate) {
                    updateReportedDate.addEventListener('change', syncUpdateDueDate);
                }

                /* Add Status modal */
                $('#addstatus')
                    .on('show.bs.modal', function(event) {
                        $(this).find('#status_task_id').val($(event.relatedTarget).data('task-id') || '');
                    })
                    .on('hidden.bs.modal', function() {
                        var f = $(this).find('form')[0];
                        if (f) f.reset();
                    });

                /* Update Task modal */
                $('#updateTaskModal')
                    .on('show.bs.modal', function(event) {
                        var btn = $(event.relatedTarget);
                        var modal = $(this);
                        if (!btn.length) {
                            return;
                        }
                        modal.find('#update_taskID').val(btn.data('id') || '');
                        modal.find('#update_task').val(btn.data('task') || '');
                        modal.find('#update_priority').val(btn.data('priority') || '2');
                        modal.find('#update_project').val(btn.data('project') || '');
                        modal.find('#update_attachment').val(btn.data('attachment') || '');
                        modal.find('#update_reportedDate').val(btn.data('reported') || '');
                        modal.find('#update_dueDate').val(btn.data('due') || btn.data('reported') || '');
                        modal.find('#update_client_comment').val(btn.data('client-comment') || '');
                        lastUpdateReportedDateValue = btn.data('reported') || '';
                        var ap = modal.find('#update_assignedPerson');
                        if (ap.length) {
                            ap.val(btn.data('assigned') || '').trigger('change');
                        }
                    })
                    .on('hidden.bs.modal', function() {
                        var f = $(this).find('form')[0];
                        if (f) f.reset();
                    });

                /* Admin Comment modal */
                $('#adminCommentModal')
                    .on('show.bs.modal', function(event) {
                        var btn = $(event.relatedTarget);
                        var modal = $(this);
                        if (!btn.length) {
                            return;
                        }
                        modal.find('#admin-comment-task').val(btn.attr('data-task') || '');
                        modal.find('#admin-comment-note').val(btn.attr('data-comment') || '');
                        modal.find('#admin-comment-task-id').val(btn.attr('data-task-id') || '');
                        modal.find('#admin-comment-pts-id').val(btn.attr('data-pts-id') || '');
                    })
                    .on('hidden.bs.modal', function() {
                        $(this).find('#admin-comment-task, #admin-comment-note, #admin-comment-task-id, #admin-comment-pts-id').val('');
                    });

                /* Forward Task modal */
                $('#forwardTaskModal')
                    .on('show.bs.modal', function(event) {
                        var btn = $(event.relatedTarget);
                        var modal = $(this);
                        if (!btn.length) {
                            return;
                        }
                        modal.find('#forward_task_id').val(btn.data('task-id') || '');
                        modal.find('#forward_task_name').val(btn.data('task') || '');
                    })
                    .on('hidden.bs.modal', function() {
                        var f = $(this).find('form')[0];
                        if (f) f.reset();
                    });

                $('#task_file').on('change', function() {
                    var fileName = ($(this).val() || '').split('\\').pop();
                    if (fileName !== '') {
                        $(this).siblings('.custom-file-label').addClass('selected').text(fileName);
                    }
                });

                /* Action dropdown menu */
                $(document).on('click', '.action-menu-btn', function(e) {
                    e.stopPropagation();
                    var taskId = $(this).data('task-id');
                    var $dropdown = $('#dropdown-' + taskId);
                    $('.action-dropdown').not($dropdown).removeClass('show');
                    $dropdown.toggleClass('show');
                });

                $(document).on('click', function() {
                    $('.action-dropdown').removeClass('show');
                });

                $(document).on('click', '.action-dropdown', function(e) {
                    e.stopPropagation();
                });

                /* Checklist functionality */
                $(document).on('click', '.add-checklist-item', function() {
                    var container = $('#taskChecklistContainer');
                    var newItem = $('.checklist-item:first').clone();
                    newItem.find('input').val('');
                    container.append(newItem);
                    updateChecklistButtons();
                });

                $(document).on('click', '.remove-checklist-item', function() {
                    $(this).closest('.checklist-item').remove();
                    updateChecklistButtons();
                });

                function updateChecklistButtons() {
                    var items = $('.checklist-item');
                    items.each(function(index) {
                        var removeBtn = $(this).find('.remove-checklist-item');
                        if (items.length > 1) {
                            removeBtn.show();
                        } else {
                            removeBtn.hide();
                        }
                    });
                }

                /* Update Task Checklist Management */
                var currentTaskId = 0;
                var checklistItems = [];

                $('#updateTaskModal').on('show.bs.modal', function(event) {
                    var btn = $(event.relatedTarget);
                    currentTaskId = btn.data('id') || 0;
                    
                    if (currentTaskId > 0) {
                        loadTaskChecklist(currentTaskId);
                    }
                });

                function loadTaskChecklist(taskId) {
                    console.log('Loading checklist for task:', taskId);
                    $.ajax({
                        url: '<?= base_url('Page/getTaskChecklist'); ?>',
                        method: 'POST',
                        data: { task_id: taskId },
                        success: function(response) {
                            console.log('Load response:', response);
                            checklistItems = response.data || [];
                            renderChecklist();
                            $('#saveChecklistBtn').hide();
                        },
                        error: function(xhr, status, error) {
                            console.error('Load error:', xhr.responseText);
                            $('#updateTaskChecklistContainer').html('<div class="text-muted">No checklist items found.</div>');
                        }
                    });
                }

                function renderChecklist() {
                    var html = '';
                    checklistItems.forEach(function(item, index) {
                        html += '<div class="checklist-item mb-2">' +
                            '<div class="row">' +
                            '<div class="col-md-1">' +
                            '<div class="form-check">' +
                            '<input type="checkbox" class="form-check-input checklist-checkbox" data-index="' + index + '" ' + (item.isCompleted ? 'checked' : '') + '>' +
                            '</div>' +
                            '</div>' +
                            '<div class="col-md-5">' +
                            '<input type="text" class="form-control checklist-item-input" data-index="' + index + '" value="' + item.itemDescription + '" placeholder="Enter checklist item...">' +
                            '</div>' +
                            '<div class="col-md-3">' +
                            '<select class="form-control checklist-status" data-index="' + index + '">' +
                            '<option value="Pending" ' + (item.status === 'Pending' ? 'selected' : '') + '>Pending</option>' +
                            '<option value="In Progress" ' + (item.status === 'In Progress' ? 'selected' : '') + '>In Progress</option>' +
                            '<option value="Completed" ' + (item.status === 'Completed' ? 'selected' : '') + '>Completed</option>' +
                            '<option value="On Hold" ' + (item.status === 'On Hold' ? 'selected' : '') + '>On Hold</option>' +
                            '<option value="Blocked" ' + (item.status === 'Blocked' ? 'selected' : '') + '>Blocked</option>' +
                            '</select>' +
                            '</div>' +
                            '<div class="col-md-3">' +
                            '<div class="input-group">' +
                            '<button type="button" class="btn btn-outline-danger remove-checklist-item" data-index="' + index + '" title="Remove this item">' +
                            '<i class="fas fa-minus"></i>' +
                            '</button>' +
                            '</div>' +
                            '</div>' +
                            '</div>' +
                            '</div>';
                    });
                    
                    if (html === '') {
                        html = '<div class="text-muted">No checklist items yet. Click "Add Item" to create one.</div>';
                    }
                    
                    $('#updateTaskChecklistContainer').html(html);
                }

                $(document).on('click', '#addChecklistItemBtn', function() {
                    var newItem = {
                        checklistID: 0,
                        itemDescription: '',
                        isCompleted: 0
                    };
                    checklistItems.push(newItem);
                    renderChecklist();
                    $('#saveChecklistBtn').show();
                });

                $(document).on('click', '.remove-checklist-item', function() {
                    var index = $(this).data('index');
                    checklistItems.splice(index, 1);
                    renderChecklist();
                    $('#saveChecklistBtn').show();
                });

                $(document).on('change', '.checklist-checkbox', function() {
                    var index = $(this).data('index');
                    checklistItems[index].isCompleted = $(this).is(':1') ? 1 : 0;
                    $('#saveChecklistBtn').show();
                });

                $(document).on('input', '.checklist-item-input', function() {
                    $('#saveChecklistBtn').show();
                });

                $(document).on('change', '.checklist-status', function() {
                    $('#saveChecklistBtn').show();
                });

                $(document).on('click', '#saveChecklistBtn', function() {
                    var updatedItems = [];
                    $('.checklist-item').each(function() {
                        var checkbox = $(this).find('.checklist-checkbox');
                        var input = $(this).find('.checklist-item-input');
                        var statusSelect = $(this).find('.checklist-status');
                        var index = checkbox.data('index');
                        var description = input.val().trim();
                        var status = statusSelect.val();
                        
                        if (description !== '') {
                            updatedItems.push({
                                checklistID: checklistItems[index] ? (checklistItems[index].checklistID || 0) : 0,
                                itemDescription: description,
                                status: status,
                                isCompleted: checkbox.is(':checked') ? 1 : 0
                            });
                        }
                    });
                    
                    console.log('Saving checklist:', updatedItems);
                    console.log('Task ID:', currentTaskId);
                    
                    if (currentTaskId <= 0) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Invalid task ID. Please try again.'
                        });
                        return;
                    }
                    
                    $.ajax({
                        url: '<?= base_url('Page/saveTaskChecklist'); ?>',
                        method: 'POST',
                        data: { 
                            task_id: currentTaskId,
                            checklist_items: updatedItems
                        },
                        success: function(response) {
                            console.log('Save response:', response);
                            $('#saveChecklistBtn').hide();
                            checklistItems = updatedItems;
                            Swal.fire({
                                icon: 'success',
                                title: 'Checklist Updated',
                                text: 'Task checklist has been saved successfully.',
                                timer: 2000,
                                showConfirmButton: false
                            });
                        },
                        error: function(xhr, status, error) {
                            console.error('Save error:', xhr.responseText);
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'Failed to save checklist. Please try again.'
                            });
                        }
                    });
                });

                /* Close dropdown when modal opens */
                $(document).on('show.bs.modal', function() {
                    $('.action-dropdown').removeClass('show');
                });

            });
        })(jQuery);
    </script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(document).ready(function() {
    $('.select2-staff').select2({
        placeholder: 'Select an employee',
        allowClear: true
    });
});
</script>

</body>

</html>
