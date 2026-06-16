<!DOCTYPE html>
<html lang="en">
<?php include('includes/head.php'); ?>

<body>
    <div id="wrapper">
        <?php include('includes/top-nav-bar.php'); ?>
        <?php include('includes/sidebar.php'); ?>

        <div class="content-page">
            <div class="content">
                <div class="container-fluid employee-task-page">

                    <style>
                        .content-page {
                            display: flex;
                            flex-direction: column;
                            min-height: 100vh;
                        }

                        .content-page .content {
                            flex: 1 0 auto;
                            padding-bottom: 3rem;
                        }

                        .footer {
                            flex: 0 0 auto;
                            margin-top: 6rem !important;
                            padding-top: 2rem;
                        }

                        .employee-task-page .title-wrap {
                            padding: 1.25rem 1.25rem 0;
                        }

                        .employee-task-page .title-wrap h4 {
                            margin: 0;
                            line-height: 1.15;
                        }

                        .employee-task-page .title-sub {
                            display: inline-block;
                            margin-top: 6px;
                        }

                        .employee-task-page .title-divider {
                            border: 0;
                            height: 2px;
                            border-radius: 1px;
                            background: linear-gradient(to right, #4285F4 60%, #FBBC05 80%, #34A853 100%);
                            margin: 10px 1.25rem 18px;
                        }

                        .employee-task-page .summary-card {
                            background: #fff;
                            border: 1px solid rgba(15, 23, 42, .08);
                            border-radius: 14px;
                            box-shadow: 0 6px 18px rgba(15, 23, 42, .08);
                            margin-bottom: 18px;
                        }

                        .employee-task-page .stat-box {
                            background: #f8f9fc;
                            border: 1px solid #e9edf7;
                            border-radius: 12px;
                            padding: 16px;
                            text-align: center;
                            height: 100%;
                        }

                        .employee-task-page .stat-box h3 {
                            margin: 0;
                            font-size: 28px;
                            font-weight: 700;
                            color: #27326c;
                        }

                        .employee-task-page .stat-box p {
                            margin: 4px 0 0;
                            color: #6c757d;
                            font-size: 13px;
                        }

                        .employee-task-page .accordion-card {
                            background: #fff;
                            border: 1px solid rgba(15, 23, 42, .08);
                            border-radius: 14px;
                            box-shadow: 0 6px 18px rgba(15, 23, 42, .06);
                            margin-bottom: 14px;
                            overflow: hidden;
                        }

                        .employee-task-page .accordion-header-btn {
                            width: 100%;
                            text-align: left;
                            border: 0;
                            background: #fff;
                            padding: 16px 18px;
                            display: flex;
                            align-items: center;
                            justify-content: space-between;
                            cursor: pointer;
                            outline: none;
                        }

                        .employee-task-page .employee-meta {
                            display: flex;
                            align-items: center;
                            gap: 14px;
                            min-width: 0;
                        }

                        .employee-task-page .avatar-circle {
                            width: 46px;
                            height: 46px;
                            border-radius: 50%;
                            background: #eaf1ff;
                            color: #27326c;
                            font-weight: 700;
                            display: flex;
                            align-items: center;
                            justify-content: center;
                            font-size: 16px;
                            flex-shrink: 0;
                        }

                        .employee-task-page .employee-name {
                            font-size: 16px;
                            font-weight: 700;
                            color: #27326c;
                            margin: 0;
                        }

                        .employee-task-page .employee-sub {
                            font-size: 12px;
                            color: #6c757d;
                            margin-top: 2px;
                        }

                        .employee-task-page .right-meta {
                            display: flex;
                            align-items: center;
                            gap: 10px;
                            flex-wrap: wrap;
                            margin-left: 14px;
                        }

                        .employee-task-page .pending-badge {
                            display: inline-block;
                            padding: 7px 12px;
                            border-radius: 999px;
                            font-size: 12px;
                            font-weight: 700;
                        }

                        .employee-task-page .pending-badge.has-task {
                            background: #fff4e5;
                            color: #b26a00;
                        }

                        .employee-task-page .pending-badge.no-task {
                            background: #eaf7ee;
                            color: #1e7e34;
                        }

                        .employee-task-page .chevron {
                            transition: transform .25s ease;
                            font-size: 18px;
                            color: #27326c;
                        }

                        .employee-task-page .accordion-header-btn[aria-expanded="true"] .chevron {
                            transform: rotate(180deg);
                        }

                        .employee-task-page .task-body {
                            padding: 0 18px 18px;
                            background: #fcfdff;
                            border-top: 1px solid #eef1f6;
                        }

                        .employee-task-page .task-table {
                            margin-top: 14px;
                            margin-bottom: 0;
                        }

                        .employee-task-page .task-table thead th {
                            background: #f2f5fb;
                            color: #445;
                            font-size: 13px;
                            border-top: none;
                            white-space: nowrap;
                            vertical-align: middle;
                        }

                        .employee-task-page .task-table td {
                            vertical-align: middle;
                        }

                        .employee-task-page .task-cell {
                            white-space: normal;
                            min-width: 240px;
                        }

                        .employee-task-page .task-text {
                            font-weight: 600;
                            color: #27326c;
                            line-height: 1.4;
                        }

                        .employee-task-page .empty-state {
                            background: rgba(68, 86, 204, .08);
                            border-radius: 12px;
                            padding: 14px 18px;
                            color: #5c6b7a;
                            margin-top: 14px;
                        }

                        .employee-task-page .badge-priority-high {
                            background: #fdeaea;
                            color: #b42318;
                        }

                        .employee-task-page .badge-priority-medium {
                            background: #fff4e5;
                            color: #b26a00;
                        }

                        .employee-task-page .badge-priority-low {
                            background: #eaf7ee;
                            color: #1e7e34;
                        }

                        .employee-task-page .attachment-btn {
                            border-radius: 20px;
                            padding: 4px 10px;
                            font-size: 12px;
                        }

                        @media (max-width: 768px) {
                            .employee-task-page .accordion-header-btn {
                                flex-direction: column;
                                align-items: flex-start;
                                gap: 10px;
                            }

                            .employee-task-page .right-meta {
                                width: 100%;
                                justify-content: space-between;
                                margin-left: 0;
                            }
                        }

                        @media (max-width:576px) {
                            .employee-task-page .title-wrap {
                                padding: 1rem 1rem 0;
                            }

                            .employee-task-page .title-divider {
                                margin: 8px 1rem 14px;
                            }
                        }
                    </style>

                    <?php
                    $totalEmployees = !empty($employees) ? count($employees) : 0;
                    $totalPending   = 0;

                    if (!empty($employees)) {
                        foreach ($employees as $emp) {
                            $totalPending += isset($emp->pending_count) ? (int)$emp->pending_count : 0;
                        }
                    }
                    ?>

                    <div class="row">
                        <div class="col-12">
                            <div class="title-wrap">
                                <h4 class="page-title">
                                    Employee Pending Tasks<br>

                                </h4>
                            </div>
                            <hr class="title-divider">
                        </div>
                    </div>

                    <?php
                    $taskFilter = isset($taskFilter) ? (string) $taskFilter : 'all';
                    ?>
                    <div class="row mb-3">
                        <div class="col-12">
                            <div class="btn-group" role="group" aria-label="Task filter">
                                <a href="<?= base_url('Page/employeeTask?task_filter=all'); ?>" class="btn btn-sm <?= $taskFilter === 'all' ? 'btn-primary' : 'btn-outline-primary'; ?>">
                                    <i class="fas fa-users"></i> All Employees
                                </a>
                                <a href="<?= base_url('Page/employeeTask?task_filter=with_tasks'); ?>" class="btn btn-sm <?= $taskFilter === 'with_tasks' ? 'btn-warning' : 'btn-outline-warning'; ?>">
                                    <i class="fas fa-tasks"></i> With Pending Tasks
                                </a>
                                <a href="<?= base_url('Page/employeeTask?task_filter=without_tasks'); ?>" class="btn btn-sm <?= $taskFilter === 'without_tasks' ? 'btn-success' : 'btn-outline-success'; ?>">
                                    <i class="fas fa-check-circle"></i> Without Tasks
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-2">
                        <div class="col-md-6 col-lg-3 mb-3">
                            <div class="stat-box">
                                <h3><?= (int)$totalEmployees; ?></h3>
                                <p>Total Employees</p>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-3 mb-3">
                            <div class="stat-box">
                                <h3><?= (int)$totalPending; ?></h3>
                                <p>Total Pending Tasks</p>
                            </div>
                        </div>
                    </div>

                    <div id="employeeAccordion">
                        <?php if (!empty($employees)): ?>
                            <?php foreach ($employees as $index => $row): ?>
                                <?php
                                $collapseId = 'empCollapse' . (int)$index;
                                $headingId  = 'empHeading' . (int)$index;

                                $rowUserId = (int) ($row->user_id ?? 0);
                                $shouldOpen = !empty($open_user_id) && (int) $open_user_id === $rowUserId;
                                $collapsedAttr = $shouldOpen ? 'true' : 'false';
                                $collapseClass = $shouldOpen ? 'collapse show' : 'collapse';

                                $fullName = trim($row->lName . ', ' . $row->fName . ' ' . $row->mName);
                                $initials = strtoupper(
                                    substr((string)$row->fName, 0, 1) .
                                        substr((string)$row->lName, 0, 1)
                                );
                                ?>
                                <div class="accordion-card">
                                    <button
                                        class="accordion-header-btn"
                                        type="button"
                                        data-toggle="collapse"
                                        data-target="#<?= $collapseId; ?>"
                                        aria-expanded="<?= $collapsedAttr; ?>"
                                        aria-controls="<?= $collapseId; ?>"
                                        id="<?= $headingId; ?>">
                                        <div class="employee-meta">
                                            <div class="avatar-circle"><?= htmlspecialchars($initials, ENT_QUOTES, 'UTF-8'); ?></div>
                                            <div>
                                                <h5 class="employee-name"><?= htmlspecialchars($fullName, ENT_QUOTES, 'UTF-8'); ?></h5>
                                                <div class="employee-sub">
                                                    User ID:
                                                    <?= htmlspecialchars(isset($row->user_id) ? $row->user_id : '', ENT_QUOTES, 'UTF-8'); ?>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="right-meta">
                                            <span class="pending-badge <?= ((int)$row->pending_count > 0) ? 'has-task' : 'no-task'; ?>">
                                                <?= (int)$row->pending_count; ?> Pending Task<?= ((int)$row->pending_count !== 1) ? 's' : ''; ?>
                                            </span>
                                            <i class="mdi mdi-chevron-down chevron"></i>
                                        </div>
                                    </button>

                                    <div
                                        id="<?= $collapseId; ?>"
                                        class="<?= $collapseClass; ?>"
                                        aria-labelledby="<?= $headingId; ?>"
                                        data-parent="#employeeAccordion">
                                        <div class="task-body">
                                            <?php if (!empty($row->pending_tasks)): ?>
                                                <div class="table-responsive">
                                                    <table class="table table-bordered table-hover task-table">
                                                        <thead>
                                                            <tr>
                                                                <th style="width:60px;">#</th>
                                                                <th>Task</th>
                                                                <th style="width:140px;">Reported Date</th>
                                                                <!-- <th style="width:120px;">Priority</th> -->
                                                                <th style="width:140px;">Attachment</th>
                                                                <th style="width:100px;">Action</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <?php foreach ($row->pending_tasks as $k => $task): ?>
                                                                <?php
                                                                $priority = isset($task->priority) ? trim((string)$task->priority) : '';
                                                                $priorityClass = 'badge-secondary';

                                                                if (strcasecmp($priority, 'High') === 0) {
                                                                    $priorityClass = 'badge-priority-high';
                                                                } elseif (strcasecmp($priority, 'Medium') === 0) {
                                                                    $priorityClass = 'badge-priority-medium';
                                                                } elseif (strcasecmp($priority, 'Low') === 0) {
                                                                    $priorityClass = 'badge-priority-low';
                                                                }
                                                                ?>
                                                                <tr>
                                                                    <td><?= $k + 1; ?></td>
                                                                    <td class="task-cell">
                                                                        <div class="task-text">
                                                                            <?= htmlspecialchars(isset($task->task) ? $task->task : '', ENT_QUOTES, 'UTF-8'); ?>
                                                                        </div>
                                                                        <?php if (!empty($task->projectDescription)): ?>
                                                                            <div class="text-muted small mt-1">
                                                                                <i class="mdi mdi-folder-outline"></i> <?= htmlspecialchars($task->projectDescription, ENT_QUOTES, 'UTF-8'); ?>
                                                                            </div>
                                                                        <?php endif; ?>
                                                                    </td>
                                                                    <td>
                                                                        <?= !empty($task->reportedDate) ? date('M d, Y', strtotime($task->reportedDate)) : '-'; ?>
                                                                    </td>

                                                                    <!-- <td>
                                                                    <span class="badge <?= $priorityClass; ?>">
                                                                        <?= htmlspecialchars($priority !== '' ? $priority : '-', ENT_QUOTES, 'UTF-8'); ?>
                                                                    </span>
                                                                </td> -->

                                                                    <td>
                                                                        <?php if (!empty($task->attachment_link)): ?>
                                                                            <a href="<?= htmlspecialchars($task->attachment_link, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" class="btn btn-sm btn-outline-primary attachment-btn">
                                                                                <i class="mdi mdi-paperclip"></i> View File
                                                                            </a>
                                                                        <?php else: ?>
                                                                            <span class="text-muted">No File</span>
                                                                        <?php endif; ?>
                                                                    </td>
                                                                    <td>
                                                                        <?php
                                                                        $taskId = (int)($task->taskID ?? 0);
                                                                        $taskText = htmlspecialchars($task->task ?? '', ENT_QUOTES, 'UTF-8');
                                                                        $priority = htmlspecialchars($task->priority ?? '', ENT_QUOTES, 'UTF-8');
                                                                        $projectId = (int)($task->projectID ?? 0);
                                                                        $assignedId = htmlspecialchars($task->assignedPerson ?? '', ENT_QUOTES, 'UTF-8');
                                                                        $reportedDate = htmlspecialchars($task->reportedDate ?? '', ENT_QUOTES, 'UTF-8');
                                                                        $dueDate = htmlspecialchars($task->dueDate ?? '', ENT_QUOTES, 'UTF-8');
                                                                        $attachmentUrl = htmlspecialchars($task->attachment_link ?? '', ENT_QUOTES, 'UTF-8');
                                                                        $clientComment = htmlspecialchars($task->client_comment ?? '', ENT_QUOTES, 'UTF-8');
                                                                        $taskViewLink = base_url('Page/taskStat?id=') . $taskId;
                                                                        $isAdmin = $this->session->userdata('level') === 'Admin';
                                                                        ?>
                                                                        <div class="btn-group">
                                                                            <a href="<?= htmlspecialchars($taskViewLink, ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-sm btn-outline-info">
                                                                                <i class="mdi mdi-eye"></i>
                                                                            </a>
                                                                            <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle dropdown-toggle-split" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                                                <i class="mdi mdi-dots-vertical"></i>
                                                                            </button>
                                                                            <div class="dropdown-menu dropdown-menu-right">
                                                                                <a class="dropdown-item" href="#" data-toggle="modal" data-target="#updateTaskModal"
                                                                                   data-id="<?= $taskId; ?>"
                                                                                   data-task="<?= $taskText; ?>"
                                                                                   data-priority="<?= $priority; ?>"
                                                                                   data-project="<?= $projectId; ?>"
                                                                                   data-assigned="<?= $assignedId; ?>"
                                                                                   data-user-id="<?= (int) ($row->user_id ?? 0); ?>"
                                                                                   data-reported="<?= $reportedDate; ?>"
                                                                                   data-due="<?= $dueDate; ?>"
                                                                                   data-attachment="<?= $attachmentUrl; ?>"
                                                                                   data-client-comment="<?= $clientComment; ?>">
                                                                                    <i class="mdi mdi-pencil mr-1"></i> Edit
                                                                                </a>
                                                                                <a class="dropdown-item" href="#" data-toggle="modal" data-target="#addstatus" data-task-id="<?= $taskId; ?>">
                                                                                    <i class="mdi mdi-clipboard-check mr-1"></i> Add Status
                                                                                </a>
                                                                                <div class="dropdown-divider"></div>
                                                                                <a class="dropdown-item text-danger" href="<?= base_url('Page/deleteTask/' . $taskId . '?open_user_id=' . (int) ($row->user_id ?? 0)); ?>" onclick="return confirm('Are you sure you want to delete this task?');">
                                                                                    <i class="mdi mdi-delete mr-1"></i> Delete
                                                                                </a>
                                                                            </div>
                                                                        </div>
                                                                    </td>
                                                                </tr>
                                                            <?php endforeach; ?>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            <?php else: ?>
                                                <div class="empty-state">
                                                    <i class="mdi mdi-check-circle-outline mr-1"></i>
                                                    No pending tasks for this employee.
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="empty-state">
                                <i class="mdi mdi-information-outline mr-1"></i>
                                No employees found.
                            </div>
                        <?php endif; ?>
                    </div>

                </div>
            </div>

            <?php include('includes/footer.php'); ?>
        </div>
    </div>

    <!-- Add Status Modal -->
    <div class="modal fade" id="addstatus" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Task Status</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <form method="post" action="<?= base_url('Page/addTaskNote'); ?>">
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
                        <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Update Task Modal -->
    <div class="modal fade" id="updateTaskModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Update Task</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <form method="POST" action="<?= base_url('Page/updateTask'); ?>">
                    <input type="hidden" name="update_task" value="1">
                    <input type="hidden" name="open_user_id" id="update_open_user_id" value="">
                    <div class="modal-body">
                        <input type="hidden" name="taskID" id="update_taskID">
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
                                <small class="form-text text-muted">This note will appear on the client's pending task view.</small>
                            </div>
                        <?php endif; ?>
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
                                    <select class="custom-select" id="update_assignedPerson" name="assignedPerson" required>
                                        <option value="">-- Select User --</option>
                                        <option value="<?= (int) ($currentUserId ?? 0); ?>">
                                            Myself (<?= htmlspecialchars($currentUserName ?? '', ENT_QUOTES, 'UTF-8'); ?>)
                                        </option>
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
                        <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php include('includes/themecustomizer.php'); ?>

    <script src="<?= base_url(); ?>assets/js/vendor.min.js"></script>
    <script src="<?= base_url(); ?>assets/libs/moment/moment.min.js"></script>
    <script src="<?= base_url(); ?>assets/libs/jquery-scrollto/jquery.scrollTo.min.js"></script>
    <script src="<?= base_url(); ?>assets/libs/sweetalert2/sweetalert2.min.js"></script>
    <script src="<?= base_url(); ?>assets/libs/fullcalendar/fullcalendar.min.js"></script>
    <script src="<?= base_url(); ?>assets/js/pages/calendar.init.js"></script>
    <script src="<?= base_url(); ?>assets/js/pages/jquery.chat.js"></script>
    <script src="<?= base_url(); ?>assets/js/pages/jquery.todo.js"></script>
    <script src="<?= base_url(); ?>assets/libs/morris-js/morris.min.js"></script>
    <script src="<?= base_url(); ?>assets/libs/raphael/raphael.min.js"></script>
    <script src="<?= base_url(); ?>assets/libs/jquery-sparkline/jquery.sparkline.min.js"></script>
    <script src="<?= base_url(); ?>assets/js/pages/dashboard.init.js"></script>
    <script src="<?= base_url(); ?>assets/js/app.min.js"></script>
    <script src="<?= base_url(); ?>assets/libs/jquery-ui/jquery-ui.min.js"></script>

    <script>
    $(function() {
        // Add Status modal - populate task ID
        $('#addstatus').on('show.bs.modal', function(event) {
            var button = $(event.relatedTarget);
            var taskId = button.data('task-id');
            $(this).find('#status_task_id').val(taskId);
        });

        // Update Task modal - populate all fields
        $('#updateTaskModal').on('show.bs.modal', function(event) {
            var button = $(event.relatedTarget);
            var modal = $(this);

            modal.find('#update_taskID').val(button.data('id'));
            modal.find('#update_task').val(button.data('task'));
            modal.find('#update_project').val(button.data('project'));
            modal.find('#update_priority').val(button.data('priority'));
            modal.find('#update_attachment').val(button.data('attachment'));
            modal.find('#update_reportedDate').val(button.data('reported'));
            modal.find('#update_dueDate').val(button.data('due'));
            modal.find('#update_client_comment').val(button.data('client-comment') || '');

            modal.find('#update_open_user_id').val(button.data('user-id'));

            // Map priority text to value if needed
            var priorityVal = button.data('priority');
            if (priorityVal === 'High') priorityVal = '1';
            else if (priorityVal === 'Medium') priorityVal = '2';
            else if (priorityVal === 'Low') priorityVal = '3';
            modal.find('#update_priority').val(priorityVal);

            <?php if ($isAdmin): ?>
            modal.find('#update_assignedPerson').val(button.data('assigned'));
            <?php endif; ?>
        });
    });
    </script>

</body>

</html>
