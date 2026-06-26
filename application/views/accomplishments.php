<!DOCTYPE html>
<html lang="en">

<?php include('includes/head.php'); ?>

<body>

     <div id="wrapper">

          <?php include('includes/top-nav-bar.php'); ?>
          <?php include('includes/sidebar.php'); ?>

          <div class="content-page">
               <div class="content">
                    <div class="container-fluid accomplishments-page">
                         <style>

                              .accomplishments-page {
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
                                   --shadow: 0 16px 40px rgba(15, 23, 42, 0.06);
                                   --shadow-soft: 0 8px 24px rgba(15, 23, 42, 0.04);
                                   --radius-xl: 16px;
                                   --radius-lg: 12px;
                                   --radius-md: 10px;
                                   --radius-sm: 8px;
                                   --font-body: var(--font-primary);
                                   --font-head: var(--font-primary);
                                   --font-mono: var(--font-primary);
                                   background:
                                        radial-gradient(circle at top left, rgba(37, 99, 235, 0.08), transparent 28%),
                                        radial-gradient(circle at top right, rgba(16, 185, 129, 0.08), transparent 24%),
                                        linear-gradient(180deg, #f8fbff 0%, #f4f7fb 100%);
                                   min-height: 100vh;
                                   padding-bottom: 20px;
                                   font-family: var(--font-primary, Montserrat, Segoe UI, Arial, sans-serif);
                              }

                              .accomplishments-page * {
                                   box-sizing: border-box;
                              }

                              .accomplishments-page .page-header {
                                   display: flex;
                                   justify-content: space-between;
                                   align-items: flex-end;
                                   gap: 16px;
                                   margin: 16px 0 16px;
                                   flex-wrap: wrap;
                              }

                              .accomplishments-page .page-eyebrow {
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

                              .accomplishments-page .page-eyebrow::before {
                                   content: '';
                                   width: 8px;
                                   height: 8px;
                                   border-radius: 50%;
                                   background: linear-gradient(135deg, var(--primary), var(--primary-2));
                                   box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.10);
                              }

                              .accomplishments-page .page-title {
                                   margin: 0;
                                   font-family: var(--font-primary, Montserrat, Segoe UI, Arial, sans-serif);
                                   font-size: 1.5rem;
                                   line-height: 1.2;
                                   letter-spacing: -0.02em;
                                   font-weight: 700;
                                   color: var(--text);
                              }

                              .accomplishments-page .stats-grid {
                                   display: grid;
                                   grid-template-columns: repeat(4, minmax(0, 1fr));
                                   gap: 12px;
                                   margin-bottom: 16px;
                              }

                              .accomplishments-page .stat-card {
                                   position: relative;
                                   overflow: hidden;
                                   background: var(--surface);
                                   border: 1px solid rgba(255, 255, 255, 0.72);
                                   border-radius: var(--radius-xl);
                                   box-shadow: var(--shadow-soft);
                                   padding: 14px 16px 14px;
                              }

                              .accomplishments-page .stat-card::before {
                                   content: '';
                                   position: absolute;
                                   inset: 0 0 auto 0;
                                   height: 4px;
                              }

                              .accomplishments-page .stat-employees::before {
                                   background: linear-gradient(90deg, #3b82f6, #60a5fa);
                              }

                              .accomplishments-page .stat-tasks::before {
                                   background: linear-gradient(90deg, #10b981, #34d399);
                              }

                              .accomplishments-page .stat-label {
                                   color: var(--text-faint);
                                   font-size: 0.65rem;
                                   font-weight: 600;
                                   text-transform: uppercase;
                                   letter-spacing: 0.06em;
                                   margin-bottom: 8px;
                              }

                              .accomplishments-page .stat-value {
                                   color: var(--text);
                                   font-size: 1.25rem;
                                   font-weight: 700;
                                   line-height: 1.2;
                                   letter-spacing: -0.02em;
                                   font-family: var(--font-primary, Montserrat, Segoe UI, Arial, sans-serif);
                              }

                              .accomplishments-page .stat-meta {
                                   color: var(--text-soft);
                                   font-size: 0.72rem;
                                   margin-top: 4px;
                              }

                              .accomplishments-page .card-stack {
                                   display: grid;
                                   gap: 16px;
                              }

                              .accomplishments-page .theme-card {
                                   background: var(--surface);
                                   border: 1px solid rgba(255, 255, 255, 0.72);
                                   border-radius: var(--radius-xl);
                                   box-shadow: var(--shadow-soft);
                                   overflow: hidden;
                              }

                              .accomplishments-page .filter-bar {
                                   padding: 14px 18px;
                                   display: flex;
                                   justify-content: space-between;
                                   align-items: center;
                                   flex-wrap: wrap;
                                   gap: 10px;
                                   border-bottom: 1px solid var(--line);
                                   background: linear-gradient(180deg, rgba(255, 255, 255, 0.98), rgba(249, 251, 255, 0.94));
                              }

                              .accomplishments-page .accordion-card {
                                   background: var(--surface-strong);
                                   border: 1px solid var(--line);
                                   border-radius: var(--radius-lg);
                                   box-shadow: var(--shadow-soft);
                                   margin-bottom: 12px;
                                   overflow: hidden;
                              }

                              .accomplishments-page .accordion-header-btn {
                                   width: 100%;
                                   text-align: left;
                                   border: 0;
                                   background: var(--surface-strong);
                                   padding: 14px 16px;
                                   display: flex;
                                   align-items: center;
                                   justify-content: space-between;
                                   cursor: pointer;
                                   outline: none;
                              }

                              .accomplishments-page .employee-meta {
                                   display: flex;
                                   align-items: center;
                                   gap: 12px;
                                   min-width: 0;
                              }

                              .accomplishments-page .avatar-circle {
                                   width: 40px;
                                   height: 40px;
                                   border-radius: 50%;
                                   background: var(--primary-soft);
                                   color: var(--primary-2);
                                   font-weight: 700;
                                   display: flex;
                                   align-items: center;
                                   justify-content: center;
                                   font-size: 14px;
                                   flex-shrink: 0;
                              }

                              .accomplishments-page .employee-name {
                                   font-size: 0.95rem;
                                   font-weight: 700;
                                   color: var(--text);
                                   margin: 0;
                              }

                              .accomplishments-page .employee-sub {
                                   font-size: 0.75rem;
                                   color: var(--text-soft);
                                   margin-top: 2px;
                              }

                              .accomplishments-page .right-meta {
                                   display: flex;
                                   align-items: center;
                                   gap: 10px;
                                   flex-wrap: wrap;
                                   margin-left: 12px;
                              }

                              .accomplishments-page .count-badge {
                                   display: inline-block;
                                   padding: 6px 10px;
                                   border-radius: 999px;
                                   font-size: 0.75rem;
                                   font-weight: 700;
                                   background: var(--success-soft);
                                   color: var(--success);
                              }

                              .accomplishments-page .chevron {
                                   transition: transform .25s ease;
                                   font-size: 16px;
                                   color: var(--text);
                              }

                              .accomplishments-page .accordion-header-btn[aria-expanded="true"] .chevron {
                                   transform: rotate(180deg);
                              }

                              .accomplishments-page .task-body {
                                   padding: 0 16px 16px;
                                   background: var(--surface-soft);
                                   border-top: 1px solid var(--line);
                              }

                              .accomplishments-page .task-table {
                                   margin-top: 12px;
                                   margin-bottom: 0;
                              }

                              .accomplishments-page .task-table thead th {
                                   background: transparent;
                                   color: var(--text-faint);
                                   font-size: 0.72rem;
                                   font-weight: 800;
                                   text-transform: uppercase;
                                   letter-spacing: 0.08em;
                                   border-top: none;
                                   border-bottom: 1px solid var(--line);
                                   white-space: nowrap;
                                   vertical-align: middle;
                              }

                              .accomplishments-page .task-table td {
                                   vertical-align: middle;
                                   border-color: var(--line);
                              }

                              .accomplishments-page .task-cell {
                                   white-space: normal;
                                   min-width: 240px;
                              }

                              .accomplishments-page .task-text {
                                   font-weight: 600;
                                   color: var(--text);
                                   line-height: 1.4;
                              }

                              .accomplishments-page .empty-state {
                                   background: var(--primary-soft);
                                   border-radius: var(--radius-md);
                                   padding: 14px 18px;
                                   color: #5c6b7a;
                                   margin-top: 14px;
                              }

                              @media (max-width: 768px) {
                                   .accomplishments-page .accordion-header-btn {
                                        flex-direction: column;
                                        align-items: flex-start;
                                        gap: 10px;
                                   }

                                   .accomplishments-page .right-meta {
                                        width: 100%;
                                        justify-content: space-between;
                                        margin-left: 0;
                                   }

                                   .accomplishments-page .filter-bar {
                                        flex-direction: column;
                                        align-items: flex-start;
                                   }
                              }

                              @media (max-width:576px) {
                                   .accomplishments-page .title-wrap {
                                        padding: 1rem 1rem 0;
                                   }

                                   .accomplishments-page .title-divider {
                                        margin: 8px 1rem 14px;
                                   }
                              }
                         </style>

                         <?php
                         $selected_month = isset($selected_month) ? $selected_month : date('n');
                         $selected_year = isset($selected_year) ? $selected_year : date('Y');
                         $isAdmin = $this->session->userdata('level') === 'Admin';

                         // Group accomplishments by employee
                         $groupedData = [];
                         $totalAccomplishments = 0;
                         $pointsEnabled = isset($pointsEnabled) ? $pointsEnabled : false;
                         if (!empty($data)) {
                              foreach ($data as $row) {
                                   $empName = (!empty($row->assignedPersonName) && trim($row->assignedPersonName) !== '') ? trim((string)$row->assignedPersonName) : 'Unassigned';
                                   // For Staff, use a single key so all their tasks appear under their name
                                   $empKey = $isAdmin ? (isset($row->assignedPerson) ? $row->assignedPerson : 'unassigned') : 'current_user';
                                   if (!isset($groupedData[$empKey])) {
                                        $groupedData[$empKey] = [
                                             'name' => $empName,
                                             'tasks' => [],
                                             'points' => 0
                                        ];
                                   }
                                   $groupedData[$empKey]['tasks'][] = $row;
                                   $groupedData[$empKey]['points'] += (int)($row->points ?? 1);
                                   $totalAccomplishments++;
                              }
                         }
                         $totalEmployees = count($groupedData);
                         ?>

                         <div class="page-header">
                              <div>
                                   <div class="page-eyebrow">Performance Monitoring</div>
                                   <h4 class="page-title">Task Accomplishments</h4>
                              </div>
                         </div>

                         <div class="stats-grid">
                              <div class="stat-card stat-employees">
                                   <div class="stat-label">Total Employees</div>
                                   <div class="stat-value"><?= number_format($totalEmployees); ?></div>
                                   <div class="stat-meta">Active staff members</div>
                              </div>
                              <div class="stat-card stat-tasks">
                                   <div class="stat-label">Total Accomplishments</div>
                                   <div class="stat-value"><?= number_format($totalAccomplishments); ?></div>
                                   <div class="stat-meta">Completed tasks</div>
                              </div>
                         </div>

                         <div class="card-stack">
                              <div class="theme-card">

                                   <div class="filter-bar">
                                        <div class="text-muted">
                                             Showing accomplishments for <span class="font-weight-bold"><?= date('F', mktime(0, 0, 0, $selected_month, 1)); ?> <?= $selected_year; ?></span>
                                        </div>
                                        <button type="button" class="btn btn-outline-primary" data-toggle="modal" data-target="#filterAccomplishmentsModal">
                                             <i class="mdi mdi-calendar mr-1"></i> Filter Month &amp; Year
                                        </button>
                                   </div>

                                   <div id="employeeAccordion">
                                        <?php if (!empty($groupedData)): ?>
                                             <?php $index = 0; ?>
                                             <?php foreach ($groupedData as $empKey => $empData): ?>
                                                  <?php
                                                  $collapseId = 'empCollapse' . $index;
                                                  $headingId = 'empHeading' . $index;
                                                  $fullName = $empData['name'];

                                                  // Generate initials
                                                  $initials = '??';
                                                  if ($fullName !== 'Unassigned') {
                                                       $nameParts = explode(' ', $fullName);
                                                       if (count($nameParts) >= 2) {
                                                            $initials = strtoupper(substr($nameParts[0], 0, 1) . substr(end($nameParts), 0, 1));
                                                       } else {
                                                            $initials = strtoupper(substr($fullName, 0, 2));
                                                       }
                                                  }
                                                  $taskCount = count($empData['tasks']);
                                                  $empPoints = $empData['points'];
                                                  $badgeText = $taskCount . ' Task' . ($taskCount !== 1 ? 's' : '');
                                                  if ($pointsEnabled && $empPoints != $taskCount) {
                                                       $badgeText .= ' (' . $empPoints . ' points)';
                                                  }
                                                  ?>
                                                  <div class="accordion-card">
                                                       <button class="accordion-header-btn" type="button" data-toggle="collapse" data-target="#<?= $collapseId; ?>" aria-expanded="false" aria-controls="<?= $collapseId; ?>" id="<?= $headingId; ?>">
                                                            <div class="employee-meta">
                                                                 <div class="avatar-circle"><?= htmlspecialchars($initials, ENT_QUOTES, 'UTF-8'); ?></div>
                                                                 <div>
                                                                      <h5 class="employee-name"><?= htmlspecialchars($fullName, ENT_QUOTES, 'UTF-8'); ?></h5>
                                                                      <div class="employee-sub">
                                                                           <?= $taskCount; ?> accomplished task<?= $taskCount !== 1 ? 's' : ''; ?>
                                                                      </div>
                                                                 </div>
                                                            </div>

                                                            <div class="right-meta">
                                                                 <span class="count-badge">
                                                                      <?= $badgeText; ?>
                                                                 </span>
                                                                 <i class="mdi mdi-chevron-down chevron"></i>
                                                            </div>
                                                       </button>

                                                       <div id="<?= $collapseId; ?>" class="collapse" aria-labelledby="<?= $headingId; ?>" data-parent="#employeeAccordion">
                                                            <div class="task-body">
                                                                 <div class="table-responsive">
                                                                      <table class="table table-bordered table-hover task-table">
                                                                           <thead>
                                                                                <tr>
                                                                                     <th style="width:60px;">#</th>
                                                                                     <th>Task</th>
                                                                                     <th style="width:140px;">Date Accomplished</th>
                                                                                     <th style="width:180px;">Project</th>
                                                                                     <?php if ($this->session->userdata('level') === 'Admin'): ?>
                                                                                          <th style="width:100px;" class="text-center">Action</th>
                                                                                     <?php endif; ?>
                                                                                </tr>
                                                                           </thead>
                                                                           <tbody>
                                                                                <?php foreach ($empData['tasks'] as $k => $row): ?>
                                                                                     <?php
                                                                                     $isCalendar = isset($row->accomplishment_type) && $row->accomplishment_type === 'calendar';
                                                                                     
                                                                                     if ($isCalendar) {
                                                                                          $taskName = htmlspecialchars((string)($row->event_title ?? ''), ENT_QUOTES, 'UTF-8');
                                                                                          $projectDescription = 'Calendar Event';
                                                                                          $datePosted = $row->completed_at;
                                                                                          $taskId = '';
                                                                                          $linkUrl = '#';
                                                                                     } else {
                                                                                          $taskName = htmlspecialchars((string)($row->task ?? ''), ENT_QUOTES, 'UTF-8');
                                                                                          $projectDescription = htmlspecialchars((string)($row->projectDescription ?? ''), ENT_QUOTES, 'UTF-8');
                                                                                          $datePosted = $row->datePosted;
                                                                                          $taskId = $row->taskID;
                                                                                          $linkUrl = base_url() . 'Page/taskStat?id=' . $taskId;
                                                                                     }
                                                                                     ?>
                                                                                     <tr>
                                                                                          <td><?= $k + 1; ?></td>
                                                                                          <td class="task-cell">
                                                                                               <div class="task-text">
                                                                                                    <?php if ($isCalendar): ?>
                                                                                                         <span style="color: #27326c;"><?= $taskName; ?> <small class="text-muted">(Calendar)</small></span>
                                                                                                    <?php else: ?>
                                                                                                         <a href="<?= $linkUrl; ?>" style="color: #27326c;"><?= $taskName; ?></a>
                                                                                                    <?php endif; ?>
                                                                                               </div>
                                                                                          </td>
                                                                                          <td><?= date('M d, Y', strtotime($datePosted)); ?></td>
                                                                                          <td><?= $projectDescription; ?></td>
                                                                                          <?php if ($this->session->userdata('level') === 'Admin' && !$isCalendar): ?>
                                                                                               <td class="text-center">
                                                                                                    <a href="#" class="badge badge-primary" data-toggle="modal" data-target="#addStatusModal" data-task-id="<?= $taskId; ?>">
                                                                                                         Add Status
                                                                                                    </a>
                                                                                               </td>
                                                                                          <?php elseif ($this->session->userdata('level') === 'Admin' && $isCalendar): ?>
                                                                                               <td class="text-center">
                                                                                                    <span class="badge badge-secondary">N/A</span>
                                                                                               </td>
                                                                                          <?php endif; ?>
                                                                                     </tr>
                                                                                <?php endforeach; ?>
                                                                           </tbody>
                                                                      </table>
                                                                 </div>
                                                            </div>
                                                       </div>
                                                  </div>
                                                  <?php $index++; ?>
                                             <?php endforeach; ?>
                                        <?php else: ?>
                                             <div class="empty-state">
                                                  <i class="mdi mdi-information-outline mr-1"></i>
                                                  <?= (isset($filter_applied) && $filter_applied) ? 'No results found for the selected month and year.' : 'No accomplished tasks available yet.'; ?>
                                             </div>
                                        <?php endif; ?>
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
                    $('#addStatusModal').on('show.bs.modal', function(event) {
                         var button = $(event.relatedTarget);
                         var taskId = button.data('task-id');
                         $(this).find('#dataid').val(taskId);
                    });
               });
          })(jQuery);
     </script>

     <div class="modal fade" id="filterAccomplishmentsModal" tabindex="-1" role="dialog" aria-labelledby="filterAccomplishmentsModalLabel" aria-hidden="true">
          <div class="modal-dialog" role="document">
               <div class="modal-content">
                    <form method="post">
                         <div class="modal-header">
                              <h5 class="modal-title mb-0" id="filterAccomplishmentsModalLabel">Select Month and Year</h5>
                              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                   <span aria-hidden="true">&times;</span>
                              </button>
                         </div>
                         <div class="modal-body">
                              <div class="form-row">
                                   <div class="form-group col-md-6">
                                        <label for="filter-month">Month</label>
                                        <select name="month" id="filter-month" class="form-control" required>
                                             <option value="">-- Select Month --</option>
                                             <?php for ($m = 1; $m <= 12; $m++): ?>
                                                  <option value="<?= $m; ?>" <?= ($selected_month == $m) ? 'selected' : ''; ?>>
                                                       <?= date('F', mktime(0, 0, 0, $m, 1)); ?>
                                                  </option>
                                             <?php endfor; ?>
                                        </select>
                                   </div>
                                   <div class="form-group col-md-6">
                                        <label for="filter-year">Year</label>
                                        <select name="year" id="filter-year" class="form-control" required>
                                             <option value="">-- Select Year --</option>
                                             <?php for ($y = date('Y'); $y >= 2020; $y--): ?>
                                                  <option value="<?= $y; ?>" <?= ($selected_year == $y) ? 'selected' : ''; ?>>
                                                       <?= $y; ?>
                                                  </option>
                                             <?php endfor; ?>
                                        </select>
                                   </div>
                              </div>
                         </div>
                         <div class="modal-footer">
                              <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
                              <button type="submit" name="filter" value="1" class="btn btn-primary">Apply Filter</button>
                         </div>
                    </form>
               </div>
          </div>
     </div>

     <div class="modal fade" id="addStatusModal" tabindex="-1" role="dialog" aria-hidden="true">
          <div class="modal-dialog modal-lg" role="document">
               <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                         <h5 class="modal-title mb-0">Add Task Status</h5>
                         <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                              <span aria-hidden="true">&times;</span>
                         </button>
                    </div>
                    <form class="needs-validation" method="post" action="<?= base_url(); ?>Page/addTaskNote" novalidate>
                         <div class="modal-body">
                              <input type="hidden" name="dataid" id="dataid">
                              <div class="form-row">
                                   <div class="col-md-12 mb-3">
                                        <label for="status-note">Notes</label>
                                        <textarea class="form-control" id="status-note" name="note" rows="3"></textarea>
                                   </div>
                                   <div class="col-md-6 mb-3">
                                        <label for="status-taskStat">Current Status</label>
                                        <select class="form-control" id="status-taskStat" name="taskStat">
                                             <option value="1">Open</option>
                                             <option value="0">Closed</option>
                                        </select>
                                   </div>
                              </div>
                         </div>
                         <div class="modal-footer">
                              <button type="submit" name="add_task_stat" class="btn btn-primary">Submit</button>
                              <button type="submit" name="resettask" class="btn btn-warning">Reset</button>
                              <button type="button" class="btn btn-light" data-dismiss="modal">Close</button>
                         </div>
                    </form>
               </div>
          </div>
     </div>

</body>

</html>