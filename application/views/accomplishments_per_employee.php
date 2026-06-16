<!DOCTYPE html>
<html lang="en">

<?php include('includes/head.php'); ?>

<body>

     <div id="wrapper">

          <?php include('includes/top-nav-bar.php'); ?>
          <?php include('includes/sidebar.php'); ?>

          <div class="content-page">
               <div class="content">
                    <div class="container-fluid accomplishments-employee-page">
                         <style>
                              .accomplishments-employee-page {
                                   padding-bottom: 100px;
                              }

                              .accomplishments-employee-page .title-wrap {
                                   padding: 1.25rem 1.25rem 0;
                              }

                              .accomplishments-employee-page .title-wrap h4 {
                                   margin: 0;
                                   line-height: 1.15;
                              }

                              .accomplishments-employee-page .title-sub {
                                   display: inline-block;
                                   margin-top: 6px;
                              }

                              .accomplishments-employee-page .title-divider {
                                   border: 0;
                                   height: 2px;
                                   border-radius: 1px;
                                   background: linear-gradient(to right, #4285F4 60%, #FBBC05 80%, #34A853 100%);
                                   margin: 10px 1.25rem 18px;
                              }

                              .accomplishments-employee-page .stat-box {
                                   background: #f8f9fc;
                                   border: 1px solid #e9edf7;
                                   border-radius: 12px;
                                   padding: 16px;
                                   text-align: center;
                                   height: 100%;
                              }

                              .accomplishments-employee-page .stat-box h3 {
                                   margin: 0;
                                   font-size: 28px;
                                   font-weight: 700;
                                   color: #27326c;
                              }

                              .accomplishments-employee-page .stat-box p {
                                   margin: 4px 0 0;
                                   color: #6c757d;
                                   font-size: 13px;
                              }

                              /* Accordion card for employee */
                              .accomplishments-employee-page .accordion-card {
                                   background: #fff;
                                   border: 1px solid rgba(15, 23, 42, .08);
                                   border-radius: 14px;
                                   box-shadow: 0 6px 18px rgba(15, 23, 42, .06);
                                   margin-bottom: 14px;
                                   overflow: hidden;
                              }

                              .accomplishments-employee-page .accordion-header-btn {
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

                              .accomplishments-employee-page .employee-meta {
                                   display: flex;
                                   align-items: center;
                                   gap: 14px;
                                   min-width: 0;
                              }

                              .accomplishments-employee-page .avatar-circle {
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

                              .accomplishments-employee-page .employee-name {
                                   font-size: 16px;
                                   font-weight: 700;
                                   color: #27326c;
                                   margin: 0;
                              }

                              .accomplishments-employee-page .employee-sub {
                                   font-size: 12px;
                                   color: #6c757d;
                                   margin-top: 2px;
                              }

                              .accomplishments-employee-page .right-meta {
                                   display: flex;
                                   align-items: center;
                                   gap: 10px;
                                   flex-wrap: wrap;
                                   margin-left: 14px;
                              }

                              .accomplishments-employee-page .count-badge {
                                   display: inline-block;
                                   padding: 7px 12px;
                                   border-radius: 999px;
                                   font-size: 12px;
                                   font-weight: 700;
                                   background: #eaf7ee;
                                   color: #1e7e34;
                              }

                              .accomplishments-employee-page .chevron {
                                   transition: transform .25s ease;
                                   font-size: 18px;
                                   color: #27326c;
                              }

                              .accomplishments-employee-page .accordion-header-btn[aria-expanded="true"] .chevron {
                                   transform: rotate(180deg);
                              }

                              .accomplishments-employee-page .task-body {
                                   padding: 0 18px 18px;
                                   background: #fcfdff;
                                   border-top: 1px solid #eef1f6;
                              }

                              .accomplishments-employee-page .task-table {
                                   margin-top: 14px;
                                   margin-bottom: 0;
                              }

                              .accomplishments-employee-page .task-table thead th {
                                   background: #f2f5fb;
                                   color: #445;
                                   font-size: 13px;
                                   border-top: none;
                                   white-space: nowrap;
                                   vertical-align: middle;
                              }

                              .accomplishments-employee-page .task-table td {
                                   vertical-align: middle;
                              }

                              .accomplishments-employee-page .task-cell {
                                   white-space: normal;
                                   min-width: 240px;
                              }

                              .accomplishments-employee-page .task-text {
                                   font-weight: 600;
                                   color: #27326c;
                                   line-height: 1.4;
                              }

                              .accomplishments-employee-page .empty-state {
                                   background: rgba(68, 86, 204, .08);
                                   border-radius: 12px;
                                   padding: 14px 18px;
                                   color: #5c6b7a;
                                   margin-top: 14px;
                              }

                              /* Action icons */
                              .accomplishments-employee-page .action-icons {
                                   display: inline-flex;
                                   align-items: center;
                                   justify-content: center;
                                   gap: 6px;
                                   flex-wrap: nowrap;
                                   white-space: nowrap;
                              }

                              .accomplishments-employee-page .action-icon {
                                   width: 28px;
                                   height: 28px;
                                   border-radius: 50%;
                                   display: inline-flex;
                                   align-items: center;
                                   justify-content: center;
                                   font-size: 0.85rem;
                                   border: 1px solid transparent;
                                   background: transparent;
                                   color: inherit;
                                   position: relative;
                                   transition: transform 0.15s ease, box-shadow 0.15s ease;
                                   text-decoration: none;
                              }

                              .accomplishments-employee-page .action-icon:hover {
                                   transform: translateY(-2px);
                                   box-shadow: 0 4px 8px rgba(15, 23, 42, 0.15);
                              }

                              .accomplishments-employee-page .action-view {
                                   background: rgba(100, 116, 139, 0.16);
                                   color: #475569;
                              }

                              .accomplishments-employee-page .action-comment {
                                   background: rgba(14, 165, 233, 0.16);
                                   color: #0ea5e9;
                              }

                              .accomplishments-employee-page .action-points {
                                   background: rgba(234, 179, 8, 0.18);
                                   color: #ca8a04;
                              }

                              .accomplishments-employee-page .action-edit {
                                   background: rgba(37, 99, 235, 0.14);
                                   color: #2563eb;
                              }

                              .accomplishments-employee-page .action-delete {
                                   background: rgba(239, 68, 68, 0.16);
                                   color: #dc2626;
                              }

                              .accomplishments-employee-page .action-icon[disabled] {
                                   opacity: 0.45;
                                   cursor: not-allowed;
                              }

                              /* Add button */
                              .accomplishments-employee-page .add-btn {
                                   background: #4285F4;
                                   border-color: #4285F4;
                                   padding: 8px 20px;
                                   border-radius: 24px;
                                   font-weight: 600;
                                   font-size: 13px;
                              }

                              .accomplishments-employee-page .add-btn:hover {
                                   background: #3367d6;
                                   border-color: #3367d6;
                              }

                              @media (max-width: 768px) {
                                   .accomplishments-employee-page .accordion-header-btn {
                                        flex-direction: column;
                                        align-items: flex-start;
                                        gap: 10px;
                                   }

                                   .accomplishments-employee-page .right-meta {
                                        width: 100%;
                                        justify-content: space-between;
                                        margin-left: 0;
                                   }
                              }

                              @media (max-width:576px) {
                                   .accomplishments-employee-page .title-wrap {
                                        padding: 1rem 1rem 0;
                                   }

                                   .accomplishments-employee-page .title-divider {
                                        margin: 8px 1rem 14px;
                                   }
                              }
                         </style>

                         <?php
                         $employeeName = 'Employee not selected';
                         $employeePosition = '';
                         if (isset($employee) && !empty($employee)) {
                              $nameParts = array_filter([
                                   isset($employee->fName) ? trim((string) $employee->fName) : '',
                                   isset($employee->mName) ? trim((string) $employee->mName) : '',
                                   isset($employee->lName) ? trim((string) $employee->lName) : '',
                              ]);

                              if (!empty($nameParts)) {
                                   $employeeName = implode(' ', $nameParts);
                              } elseif (!empty($employee->username)) {
                                   $employeeName = (string) $employee->username;
                              }

                              if (!empty($employee->position)) {
                                   $employeePosition = (string) $employee->position;
                              }
                         }

                         $accomplishedCount = (is_array($data) || $data instanceof \Countable) ? count($data) : 0;
                         $latestTimestamp = null;
                         if (!empty($data) && (is_array($data) || $data instanceof \Traversable)) {
                              foreach ($data as $taskRow) {
                                   $rawDate = null;
                                   if (!empty($taskRow->accomplishedDate)) {
                                        $rawDate = $taskRow->accomplishedDate;
                                   } elseif (!empty($taskRow->datePosted)) {
                                        $rawDate = $taskRow->datePosted;
                                   }
                                   $candidate = $rawDate ? strtotime($rawDate) : false;
                                   if ($candidate && $candidate > 0 && ($latestTimestamp === null || $candidate > $latestTimestamp)) {
                                        $latestTimestamp = $candidate;
                                   }
                              }
                         }
                         $latestAccomplishedLabel = $latestTimestamp ? date('F d, Y', $latestTimestamp) : 'No records yet';

                         $selectedDateLabel = null;
                         if (isset($selectedDate) && $selectedDate !== '') {
                              $selectedTs = strtotime((string) $selectedDate);
                              $selectedDateLabel = $selectedTs ? date('F d, Y', $selectedTs) : (string) $selectedDate;
                         }

                         $hasMonthFilter = isset($selectedMonth) && isset($selectedYear) && $selectedMonth !== null && $selectedYear !== null;
                         $selectedPeriodLabel = null;
                         if ($hasMonthFilter) {
                              $monthInt = (int) $selectedMonth;
                              $yearInt = (int) $selectedYear;
                              if ($monthInt >= 1 && $monthInt <= 12 && $yearInt > 0) {
                                   $selectedPeriodLabel = date('F Y', mktime(0, 0, 0, $monthInt, 1, $yearInt));
                              } else {
                                   $hasMonthFilter = false;
                              }
                         }

                         $selectedEndDateLabel = null;
                         if (isset($selectedEndDate) && $selectedEndDate !== '') {
                              $endTs = strtotime((string) $selectedEndDate);
                              $selectedEndDateLabel = $endTs ? date('F d, Y', $endTs) : (string) $selectedEndDate;
                         }

                         $isAdmin = $this->session->userdata('level') === 'Admin';
                         $selectedDateValue = null;
                         if (isset($selectedDate) && $selectedDate !== '') {
                              $selectedTs = strtotime((string) $selectedDate);
                              $selectedDateValue = $selectedTs ? date('Y-m-d', $selectedTs) : (string) $selectedDate;
                         }
                         if ($selectedDateValue === null) {
                              $selectedDateValue = date('Y-m-d');
                         }
                         $selectedUserIdSafe = isset($selectedUserId) ? (string) $selectedUserId : '';
                         $projects = isset($projects) ? $projects : [];
                         $pointsEnabled = isset($pointsEnabled) ? (bool) $pointsEnabled : false;

                         $returnUserId = '';
                         $returnReportPeriod = '';
                         $returnEndDate = '';
                         $inputUserId = $this->input->get('user_id');
                         if ($inputUserId !== null) {
                              $returnUserId = (string) $inputUserId;
                         }
                         $inputReportPeriod = $this->input->get('report_period');
                         if ($inputReportPeriod !== null) {
                              $returnReportPeriod = (string) $inputReportPeriod;
                         }
                         $inputEndDate = $this->input->get('end_date');
                         if ($inputEndDate !== null) {
                              $returnEndDate = (string) $inputEndDate;
                         }
                         ?>



                         <div class="row">
                              <div class="col-12">
                                   <div class="title-wrap">
                                        <h4 class="page-title">Employee Accomplishments</h4>
                                   </div>
                                   <hr class="title-divider">
                              </div>
                         </div>

                         <!-- Stats -->
                         <div class="row mb-2">
                              <div class="col-md-6 col-lg-3 mb-3">
                                   <div class="stat-box">
                                        <h3><?= (int)$accomplishedCount; ?></h3>
                                        <p>Total Accomplishments</p>
                                   </div>
                              </div>
                              <div class="col-md-6 col-lg-3 mb-3">
                                   <div class="stat-box">
                                        <h3><?= $latestTimestamp ? date('M d', $latestTimestamp) : '-'; ?></h3>
                                        <p>Latest Activity</p>
                                   </div>
                              </div>
                         </div>

                         <?php if ($this->session->flashdata('success')): ?>
                              <div class="alert alert-success">
                                   <?= htmlspecialchars($this->session->flashdata('success'), ENT_QUOTES, 'UTF-8'); ?>
                              </div>
                         <?php endif; ?>
                         <?php if ($this->session->flashdata('danger')): ?>
                              <div class="alert alert-danger">
                                   <?= htmlspecialchars($this->session->flashdata('danger'), ENT_QUOTES, 'UTF-8'); ?>
                              </div>
                         <?php endif; ?>

                         <!-- Employee Accordion Card -->
                         <div class="accordion-card">
                              <button class="accordion-header-btn" type="button" data-toggle="collapse" data-target="#employeeTasks" aria-expanded="true" aria-controls="employeeTasks">
                                   <div class="employee-meta">
                                        <?php
                                        $initials = '??';
                                        if ($employeeName !== 'Employee not selected') {
                                             $nameParts = explode(' ', $employeeName);
                                             if (count($nameParts) >= 2) {
                                                  $initials = strtoupper(substr($nameParts[0], 0, 1) . substr(end($nameParts), 0, 1));
                                             } else {
                                                  $initials = strtoupper(substr($employeeName, 0, 2));
                                             }
                                        }
                                        ?>
                                        <div class="avatar-circle"><?= htmlspecialchars($initials, ENT_QUOTES, 'UTF-8'); ?></div>
                                        <div>
                                             <h5 class="employee-name"><?= htmlspecialchars($employeeName, ENT_QUOTES, 'UTF-8'); ?></h5>
                                             <div class="employee-sub">
                                                  <?php if ($hasMonthFilter && $selectedPeriodLabel): ?>
                                                       Showing for <?= htmlspecialchars($selectedPeriodLabel, ENT_QUOTES, 'UTF-8'); ?>
                                                  <?php else: ?>
                                                       All recorded accomplishments
                                                  <?php endif; ?>
                                             </div>
                                        </div>
                                   </div>

                                   <div class="right-meta">
                                        <span class="count-badge">
                                             <?= (int)$accomplishedCount; ?> Task<?= $accomplishedCount !== 1 ? 's' : ''; ?>
                                        </span>
                                        <i class="mdi mdi-chevron-down chevron"></i>
                                   </div>
                              </button>

                              <div id="employeeTasks" class="collapse show" aria-labelledby="employeeHeading">
                                   <div class="task-body">
                                        <?php if ($isAdmin): ?>
                                             <div class="mb-3">
                                                  <button type="button" class="btn btn-primary add-btn" data-toggle="modal" data-target="#addAccomplishmentModal">
                                                       <i class="mdi mdi-plus-circle mr-1"></i> Add Accomplishment
                                                  </button>
                                             </div>
                                        <?php endif; ?>

                                        <?php if (!empty($data)): ?>
                                             <div class="table-responsive">
                                                  <table class="table table-bordered table-hover task-table">
                                                       <thead>
                                                            <tr>
                                                                 <th style="width:60px;">#</th>
                                                                 <th>Task</th>
                                                                 <th style="width:140px;">Date Accomplished</th>
                                                                 <th style="width:100px;" class="text-center">Points</th>
                                                                 <?php if ($isAdmin): ?>
                                                                      <th style="width:180px;" class="text-center">Action</th>
                                                                 <?php endif; ?>
                                                            </tr>
                                                       </thead>
                                                       <tbody>
                                                            <?php $k = 0;
                                                            foreach ($data as $row): ?>
                                                                 <?php
                                                                 $taskName = isset($row->task) ? (string) $row->task : '';
                                                                 $taskEsc = htmlspecialchars($taskName, ENT_QUOTES, 'UTF-8');
                                                                 $rawDate = null;
                                                                 if (!empty($row->accomplishedDate)) {
                                                                      $rawDate = $row->accomplishedDate;
                                                                 } elseif (!empty($row->datePosted)) {
                                                                      $rawDate = $row->datePosted;
                                                                 }
                                                                 $timestamp = $rawDate ? strtotime($rawDate) : false;
                                                                 $displayDate = ($timestamp && $timestamp > 0) ? date('M d, Y', $timestamp) : 'Not recorded';
                                                                 $inputDateValue = ($timestamp && $timestamp > 0) ? date('Y-m-d', $timestamp) : $selectedDateValue;
                                                                 $taskId = isset($row->taskID) ? (int) $row->taskID : 0;
                                                                 $ptsId = isset($row->ptsID) ? (int) $row->ptsID : 0;
                                                                 $projectId = isset($row->projectID) ? (int) $row->projectID : 0;
                                                                 $priority = isset($row->priority) ? (int) $row->priority : 2;
                                                                 $pointsValue = (isset($row->points) && is_numeric($row->points)) ? (int) $row->points : 1;
                                                                 if ($pointsValue < 0) {
                                                                      $pointsValue = 0;
                                                                 }
                                                                 $noteValue = isset($row->note) ? (string) $row->note : '';
                                                                 $noteValue = str_replace(["\r", "\n"], ' ', $noteValue);
                                                                 $noteEsc = htmlspecialchars($noteValue, ENT_QUOTES, 'UTF-8');
                                                                 ?>
                                                                 <tr>
                                                                      <td><?= ++$k; ?></td>
                                                                      <td class="task-cell">
                                                                           <div class="task-text"><?= $taskEsc; ?></div>
                                                                      </td>
                                                                      <td><?= htmlspecialchars($displayDate, ENT_QUOTES, 'UTF-8'); ?></td>
                                                                      <td class="text-center"><?= (int) $pointsValue; ?></td>
                                                                      <?php if ($isAdmin): ?>
                                                                           <td class="text-center">
                                                                                <div class="action-icons">
                                                                                     <a class="action-icon action-view"
                                                                                          href="<?= base_url(); ?>Page/taskStat?id=<?= $taskId; ?>"
                                                                                          title="View">
                                                                                          <i class="mdi mdi-eye-outline"></i>
                                                                                     </a>
                                                                                     <button type="button"
                                                                                          class="action-icon action-comment"
                                                                                          data-toggle="modal"
                                                                                          data-target="#commentAccomplishmentModal"
                                                                                          data-task="<?= $taskEsc; ?>"
                                                                                          data-pts-id="<?= $ptsId; ?>"
                                                                                          data-note="<?= $noteEsc; ?>"
                                                                                          <?= $ptsId > 0 ? '' : 'disabled'; ?>>
                                                                                          <i class="mdi mdi-comment-text-outline"></i>
                                                                                     </button>
                                                                                     <button type="button"
                                                                                          class="action-icon action-points"
                                                                                          data-toggle="modal"
                                                                                          data-target="#pointsAccomplishmentModal"
                                                                                          data-task="<?= $taskEsc; ?>"
                                                                                          data-pts-id="<?= $ptsId; ?>"
                                                                                          data-points="<?= (int) $pointsValue; ?>"
                                                                                          <?= $ptsId > 0 ? '' : 'disabled'; ?>>
                                                                                          <i class="fa-solid fa-star"></i>
                                                                                     </button>
                                                                                     <button type="button"
                                                                                          class="action-icon action-edit"
                                                                                          data-toggle="modal"
                                                                                          data-target="#editAccomplishmentModal"
                                                                                          data-task-id="<?= $taskId; ?>"
                                                                                          data-pts-id="<?= $ptsId; ?>"
                                                                                          data-task="<?= $taskEsc; ?>"
                                                                                          data-project="<?= $projectId; ?>"
                                                                                          data-priority="<?= $priority; ?>"
                                                                                          data-date="<?= htmlspecialchars($inputDateValue, ENT_QUOTES, 'UTF-8'); ?>"
                                                                                          data-note="<?= $noteEsc; ?>"
                                                                                          data-taskstat="<?= isset($row->taskStat) ? (int) $row->taskStat : 0; ?>">
                                                                                          <i class="mdi mdi-pencil-outline"></i>
                                                                                     </button>
                                                                                     <a class="action-icon action-delete"
                                                                                          href="<?= base_url(); ?>Page/deleteAccomplishment?ptsId=<?= $ptsId; ?>&taskId=<?= $taskId; ?>&name=<?= urlencode($selectedUserIdSafe); ?>&date=<?= urlencode($selectedDateValue); ?><?= $returnUserId !== '' ? ('&return_user_id=' . urlencode($returnUserId) . '&return_report_period=' . urlencode($returnReportPeriod) . '&return_end_date=' . urlencode($returnEndDate)) : ''; ?>"
                                                                                          onclick="return confirm('Are you sure you want to delete this accomplishment?');"
                                                                                          title="Delete">
                                                                                          <i class="mdi mdi-trash-can-outline"></i>
                                                                                     </a>
                                                                                </div>
                                                                           </td>
                                                                      <?php endif; ?>
                                                                 </tr>
                                                            <?php endforeach; ?>
                                                       </tbody>
                                                  </table>
                                             </div>
                                        <?php else: ?>
                                             <div class="empty-state">
                                                  <i class="mdi mdi-information-outline mr-1"></i>
                                                  No accomplished tasks recorded yet.
                                             </div>
                                        <?php endif; ?>
                                   </div>
                              </div>
                         </div>
                    </div>

                    <?php if ($isAdmin): ?>
                         <div class="modal fade" id="addAccomplishmentModal" tabindex="-1" role="dialog" aria-hidden="true">
                              <div class="modal-dialog modal-lg" role="document">
                                   <div class="modal-content">
                                        <div class="modal-header bg-primary text-white">
                                             <h5 class="modal-title mb-0">Add Accomplishment</h5>
                                             <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                                                  <span aria-hidden="true">&times;</span>
                                             </button>
                                        </div>
                                        <form class="needs-validation" method="post" action="<?= base_url(); ?>Page/addAccomplishment" novalidate>
                                             <div class="modal-body">
                                                  <input type="hidden" name="assignedPerson" value="<?= htmlspecialchars($selectedUserIdSafe, ENT_QUOTES, 'UTF-8'); ?>">
                                                  <input type="hidden" name="return_name" value="<?= htmlspecialchars($selectedUserIdSafe, ENT_QUOTES, 'UTF-8'); ?>">
                                                  <input type="hidden" name="return_date" value="<?= htmlspecialchars($selectedDateValue, ENT_QUOTES, 'UTF-8'); ?>">
                                                  <?php if ($returnUserId !== ''): ?>
                                                       <input type="hidden" name="return_user_id" value="<?= htmlspecialchars($returnUserId, ENT_QUOTES, 'UTF-8'); ?>">
                                                       <input type="hidden" name="return_report_period" value="<?= htmlspecialchars($returnReportPeriod, ENT_QUOTES, 'UTF-8'); ?>">
                                                       <input type="hidden" name="return_end_date" value="<?= htmlspecialchars($returnEndDate, ENT_QUOTES, 'UTF-8'); ?>">
                                                  <?php endif; ?>

                                                  <div class="form-group">
                                                       <label for="accomplishment-task">Task</label>
                                                       <input type="text" class="form-control" id="accomplishment-task" name="task" required>
                                                  </div>

                                                  <div class="form-row">
                                                       <div class="form-group col-md-6">
                                                            <label for="accomplishment-project">Project</label>
                                                            <select class="form-control" id="accomplishment-project" name="project" required>
                                                                 <option value="">-- Select Project --</option>
                                                                 <?php foreach ($projects as $project): ?>
                                                                      <option value="<?= $project->projectID; ?>"><?= htmlspecialchars($project->projectDescription, ENT_QUOTES, 'UTF-8'); ?></option>
                                                                 <?php endforeach; ?>
                                                            </select>
                                                       </div>
                                                       <div class="form-group col-md-3">
                                                            <label for="accomplishment-priority">Priority</label>
                                                            <select class="form-control" id="accomplishment-priority" name="priority" required>
                                                                 <option value="1">High</option>
                                                                 <option value="2" selected>Medium</option>
                                                                 <option value="3">Low</option>
                                                            </select>
                                                       </div>
                                                       <div class="form-group col-md-3">
                                                            <label for="accomplishment-date">Date Accomplished</label>
                                                            <input type="date" class="form-control" id="accomplishment-date" name="accomplishedDate" value="<?= htmlspecialchars($selectedDateValue, ENT_QUOTES, 'UTF-8'); ?>" required>
                                                       </div>
                                                  </div>

                                                  <div class="form-group">
                                                       <label for="accomplishment-points">Task Points</label>
                                                       <input type="number" class="form-control" id="accomplishment-points" name="points" min="1" value="1" required <?= $pointsEnabled ? '' : 'disabled'; ?>>
                                                  </div>

                                                  <div class="form-group mb-0">
                                                       <label for="accomplishment-note">Notes (optional)</label>
                                                       <textarea class="form-control" id="accomplishment-note" name="note" rows="3"></textarea>
                                                  </div>
                                             </div>
                                             <div class="modal-footer">
                                                  <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
                                                  <button type="submit" name="add_accomplishment" class="btn btn-primary">Save</button>
                                             </div>
                                        </form>
                                   </div>
                              </div>
                         </div>

                         <div class="modal fade" id="editAccomplishmentModal" tabindex="-1" role="dialog" aria-hidden="true">
                              <div class="modal-dialog modal-lg" role="document">
                                   <div class="modal-content">
                                        <div class="modal-header bg-primary text-white">
                                             <h5 class="modal-title mb-0">Update Accomplishment</h5>
                                             <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                                                  <span aria-hidden="true">&times;</span>
                                             </button>
                                        </div>
                                        <form class="needs-validation" method="post" action="<?= base_url(); ?>Page/updateAccomplishment" novalidate>
                                             <div class="modal-body">
                                                  <input type="hidden" name="task_id" id="edit-accomplishment-task-id">
                                                  <input type="hidden" name="pts_id" id="edit-accomplishment-pts-id">
                                                  <input type="hidden" name="return_name" value="<?= htmlspecialchars($selectedUserIdSafe, ENT_QUOTES, 'UTF-8'); ?>">
                                                  <input type="hidden" name="return_date" value="<?= htmlspecialchars($selectedDateValue, ENT_QUOTES, 'UTF-8'); ?>">
                                                  <?php if ($returnUserId !== ''): ?>
                                                       <input type="hidden" name="return_user_id" value="<?= htmlspecialchars($returnUserId, ENT_QUOTES, 'UTF-8'); ?>">
                                                       <input type="hidden" name="return_report_period" value="<?= htmlspecialchars($returnReportPeriod, ENT_QUOTES, 'UTF-8'); ?>">
                                                       <input type="hidden" name="return_end_date" value="<?= htmlspecialchars($returnEndDate, ENT_QUOTES, 'UTF-8'); ?>">
                                                  <?php endif; ?>

                                                  <div class="form-group">
                                                       <label for="edit-accomplishment-task">Task</label>
                                                       <input type="text" class="form-control" id="edit-accomplishment-task" name="task" required>
                                                  </div>

                                                  <div class="form-row">
                                                       <div class="form-group col-md-6">
                                                            <label for="edit-accomplishment-project">Project</label>
                                                            <select class="form-control" id="edit-accomplishment-project" name="project" required>
                                                                 <option value="">-- Select Project --</option>
                                                                 <?php foreach ($projects as $project): ?>
                                                                      <option value="<?= $project->projectID; ?>"><?= htmlspecialchars($project->projectDescription, ENT_QUOTES, 'UTF-8'); ?></option>
                                                                 <?php endforeach; ?>
                                                            </select>
                                                       </div>
                                                       <div class="form-group col-md-3">
                                                            <label for="edit-accomplishment-priority">Priority</label>
                                                            <select class="form-control" id="edit-accomplishment-priority" name="priority" required>
                                                                 <option value="1">High</option>
                                                                 <option value="2">Medium</option>
                                                                 <option value="3">Low</option>
                                                            </select>
                                                       </div>
                                                       <div class="form-group col-md-3">
                                                            <label for="edit-accomplishment-date">Date Accomplished</label>
                                                            <input type="date" class="form-control" id="edit-accomplishment-date" name="accomplishedDate" required>
                                                       </div>
                                                  </div>

                                                  <div class="form-group mb-0">
                                                       <label for="edit-accomplishment-note">Notes (optional)</label>
                                                       <textarea class="form-control" id="edit-accomplishment-note" name="note" rows="3"></textarea>
                                                  </div>
                                                  <div class="form-group mt-3 mb-0">
                                                       <label for="edit-accomplishment-status">Status</label>
                                                       <select class="form-control" id="edit-accomplishment-status" name="task_status" required>
                                                            <option value="0" selected>Closed (Accomplished)</option>
                                                            <option value="1">Open (Reopen)</option>
                                                       </select>
                                                  </div>
                                             </div>
                                             <div class="modal-footer">
                                                  <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
                                                  <button type="submit" name="update_accomplishment" class="btn btn-primary">Update</button>
                                             </div>
                                        </form>
                                   </div>
                              </div>
                         </div>

                         <div class="modal fade" id="commentAccomplishmentModal" tabindex="-1" role="dialog" aria-hidden="true">
                              <div class="modal-dialog modal-lg" role="document">
                                   <div class="modal-content">
                                        <div class="modal-header bg-primary text-white">
                                             <h5 class="modal-title mb-0">Comment</h5>
                                             <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                                                  <span aria-hidden="true">&times;</span>
                                             </button>
                                        </div>
                                        <form class="needs-validation" method="post" action="<?= base_url(); ?>Page/updateAccomplishmentComment" novalidate>
                                             <div class="modal-body">
                                                  <input type="hidden" name="pts_id" id="comment-pts-id">
                                                  <input type="hidden" name="return_name" value="<?= htmlspecialchars($selectedUserIdSafe, ENT_QUOTES, 'UTF-8'); ?>">
                                                  <input type="hidden" name="return_date" value="<?= htmlspecialchars($selectedDateValue, ENT_QUOTES, 'UTF-8'); ?>">
                                                  <?php if ($returnUserId !== ''): ?>
                                                       <input type="hidden" name="return_user_id" value="<?= htmlspecialchars($returnUserId, ENT_QUOTES, 'UTF-8'); ?>">
                                                       <input type="hidden" name="return_report_period" value="<?= htmlspecialchars($returnReportPeriod, ENT_QUOTES, 'UTF-8'); ?>">
                                                       <input type="hidden" name="return_end_date" value="<?= htmlspecialchars($returnEndDate, ENT_QUOTES, 'UTF-8'); ?>">
                                                  <?php endif; ?>

                                                  <div class="form-group">
                                                       <label for="comment-task">Task</label>
                                                       <input type="text" class="form-control" id="comment-task" readonly>
                                                  </div>

                                                  <div class="form-group mb-0">
                                                       <label for="comment-note">Comment</label>
                                                       <textarea class="form-control" id="comment-note" name="note" rows="3"></textarea>
                                                  </div>
                                             </div>
                                             <div class="modal-footer">
                                                  <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
                                                  <button type="submit" name="update_comment" class="btn btn-primary">Save</button>
                                             </div>
                                        </form>
                                   </div>
                              </div>
                         </div>

                         <div class="modal fade" id="pointsAccomplishmentModal" tabindex="-1" role="dialog" aria-hidden="true">
                              <div class="modal-dialog modal-lg" role="document">
                                   <div class="modal-content">
                                        <div class="modal-header bg-primary text-white">
                                             <h5 class="modal-title mb-0">Task Points</h5>
                                             <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                                                  <span aria-hidden="true">&times;</span>
                                             </button>
                                        </div>
                                        <form class="needs-validation" method="post" action="<?= base_url(); ?>Page/updateAccomplishmentPoints" novalidate>
                                             <div class="modal-body">
                                                  <input type="hidden" name="pts_id" id="points-pts-id">
                                                  <input type="hidden" name="return_name" value="<?= htmlspecialchars($selectedUserIdSafe, ENT_QUOTES, 'UTF-8'); ?>">
                                                  <input type="hidden" name="return_date" value="<?= htmlspecialchars($selectedDateValue, ENT_QUOTES, 'UTF-8'); ?>">
                                                  <?php if ($returnUserId !== ''): ?>
                                                       <input type="hidden" name="return_user_id" value="<?= htmlspecialchars($returnUserId, ENT_QUOTES, 'UTF-8'); ?>">
                                                       <input type="hidden" name="return_report_period" value="<?= htmlspecialchars($returnReportPeriod, ENT_QUOTES, 'UTF-8'); ?>">
                                                       <input type="hidden" name="return_end_date" value="<?= htmlspecialchars($returnEndDate, ENT_QUOTES, 'UTF-8'); ?>">
                                                  <?php endif; ?>

                                                  <?php if (!$pointsEnabled): ?>
                                                       <div class="alert alert-warning">
                                                            Points are not enabled in the database. Run:
                                                            <code>ALTER TABLE projects_task_stat ADD COLUMN points INT UNSIGNED NOT NULL DEFAULT 1 AFTER note;</code>
                                                       </div>
                                                  <?php endif; ?>

                                                  <div class="form-group">
                                                       <label for="points-task">Task</label>
                                                       <input type="text" class="form-control" id="points-task" readonly>
                                                  </div>

                                                  <div class="form-group">
                                                       <label for="points-value">Task Points</label>
                                                       <input type="number" class="form-control" id="points-value" name="points" min="0" required <?= $pointsEnabled ? '' : 'disabled'; ?>>
                                                  </div>
                                             </div>
                                             <div class="modal-footer">
                                                  <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
                                                  <button type="submit" name="update_points" class="btn btn-primary" <?= $pointsEnabled ? '' : 'disabled'; ?>>Save</button>
                                             </div>
                                        </form>
                                   </div>
                              </div>
                         </div>
                    <?php endif; ?>

                    <?php include('includes/footer.php'); ?>
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
          (function($) {
               'use strict';

               $(function() {
                    var $editModal = $('#editAccomplishmentModal');
                    if ($editModal.length) {
                         $editModal.on('show.bs.modal', function(event) {
                              var button = $(event.relatedTarget);

                              $(this).find('#edit-accomplishment-task-id').val(button.data('task-id') || '');
                              $(this).find('#edit-accomplishment-pts-id').val(button.data('pts-id') || '');
                              $(this).find('#edit-accomplishment-task').val(button.data('task') || '');
                              $(this).find('#edit-accomplishment-project').val(button.data('project') || '');
                              $(this).find('#edit-accomplishment-priority').val(button.data('priority') || '2');
                              $(this).find('#edit-accomplishment-date').val(button.data('date') || '');
                              $(this).find('#edit-accomplishment-note').val(button.data('note') || '');
                              $(this).find('#edit-accomplishment-status').val(button.attr('data-taskstat') || '0');
                         });
                    }

                    var $commentModal = $('#commentAccomplishmentModal');
                    if ($commentModal.length) {
                         $commentModal.on('show.bs.modal', function(event) {
                              var button = $(event.relatedTarget);

                              $(this).find('#comment-pts-id').val(button.attr('data-pts-id') || '');
                              $(this).find('#comment-task').val(button.attr('data-task') || '');
                              $(this).find('#comment-note').val(button.attr('data-note') || '');
                         });
                    }

                    var $pointsModal = $('#pointsAccomplishmentModal');
                    if ($pointsModal.length) {
                         $pointsModal.on('show.bs.modal', function(event) {
                              var button = $(event.relatedTarget);

                              $(this).find('#points-pts-id').val(button.attr('data-pts-id') || '');
                              $(this).find('#points-task').val(button.attr('data-task') || '');
                              $(this).find('#points-value').val(button.attr('data-points') || 1);
                         });
                    }
               });
          })(jQuery);
     </script>

</body>

</html>
