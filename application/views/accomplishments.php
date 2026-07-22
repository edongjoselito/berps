<!DOCTYPE html>
<html lang="en">

<?php include('includes/head.php'); ?>

<body>

     <div id="wrapper">

          <?php include('includes/top-nav-bar.php'); ?>
          <?php include('includes/sidebar.php'); ?>

          <div class="content-page">
               <div class="content">
                    <div class="container-fluid accomplishments-page berps-page">

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

                         <header class="berps-page-header">
                              <div class="berps-page-header__content">
                                   <span class="berps-page-header__eyebrow">Performance Monitoring</span>
                                   <h1 class="berps-page-title">Task Accomplishments</h1>
                                   <p class="berps-page-subtitle">Completed tasks grouped per employee for the selected month.</p>
                              </div>
                         </header>

                         <div class="berps-stat-grid">
                              <div class="berps-stat-card">
                                   <div>
                                        <p class="berps-stat-card__value"><?= number_format($totalEmployees); ?></p>
                                        <p class="berps-stat-card__label">Total Employees</p>
                                        <p class="berps-stat-card__meta">Active staff members</p>
                                   </div>
                                   <span class="berps-stat-card__icon" aria-hidden="true"><i class="mdi mdi-account-group-outline"></i></span>
                              </div>
                              <div class="berps-stat-card berps-tone-success">
                                   <div>
                                        <p class="berps-stat-card__value"><?= number_format($totalAccomplishments); ?></p>
                                        <p class="berps-stat-card__label">Total Accomplishments</p>
                                        <p class="berps-stat-card__meta">Completed tasks</p>
                                   </div>
                                   <span class="berps-stat-card__icon" aria-hidden="true"><i class="mdi mdi-check-circle-outline"></i></span>
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

     <div class="modal fade berps-form-modal" id="filterAccomplishmentsModal" tabindex="-1" role="dialog" aria-labelledby="filterAccomplishmentsModalLabel" aria-hidden="true">
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

     <div class="modal fade berps-form-modal" id="addStatusModal" tabindex="-1" role="dialog" aria-hidden="true">
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