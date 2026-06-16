<!DOCTYPE html>
<html lang="en">

<?php include('includes/head.php'); ?>

<body>

     <div id="wrapper">

          <?php include('includes/top-nav-bar.php'); ?>
          <?php include('includes/sidebar.php'); ?>

          <?php
          $projectName = 'Project Tasks';
          if (!empty($data) && isset($data[0]->projectDescription)) {
               $projectName = htmlspecialchars((string) $data[0]->projectDescription, ENT_QUOTES, 'UTF-8');
          }
          $taskTotal  = (is_array($data) || $data instanceof \Countable) ? count($data) : 0;
          $tasksExist = $taskTotal > 0;
          // Current view: 1 = Open, 0 = Closed
          $currentStatus = isset($status) ? (int) $status : 1;
          $isClosedView  = ($currentStatus === 0);
          $pointsEnabled = isset($pointsEnabled) ? (bool) $pointsEnabled : false;

          // URLs for status filter buttons
          $baseUrl = base_url('Page/taskPerProject');

          $queryOpen = http_build_query([
               'projectID' => $projectID,
               // no "status" param -> defaults to open in controller
          ]);

          $queryClosed = http_build_query([
               'projectID' => $projectID,
               'status'    => 'closed',
          ]);

          $openUrl   = $baseUrl . '?' . $queryOpen;
          $closedUrl = $baseUrl . '?' . $queryClosed;

          // If you have a project in-charge field in your query, use it here
          $projectIncharge = '';
          if (!empty($data) && isset($data[0]->projectInchargeName)) {
               $projectIncharge = htmlspecialchars((string) $data[0]->projectInchargeName, ENT_QUOTES, 'UTF-8');
          }
          ?>

          <div class="content-page">
               <div class="content">
                    <div class="container-fluid project-tasks-page">

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

                         <style>
                              .project-tasks-page .page-title-box {
                                   padding: 12px 1.25rem;
                                   /* align with cards */
                                   margin-bottom: 12px;
                              }

                              .project-tasks-page .page-title-box h4 {
                                   font-size: 1.5rem;
                                   font-weight: 600;
                              }

                              .project-tasks-page .summary-card {
                                   border: none;
                                   border-radius: 16px;
                                   box-shadow: 0 12px 30px rgba(15, 23, 42, 0.08);
                              }

                              .project-tasks-page .summary-card .card-body {
                                   padding: 22px 28px;
                              }

                              .project-tasks-page .summary-card .project-title {
                                   font-size: 1.25rem;
                                   font-weight: 600;
                                   margin-bottom: 6px;
                              }

                              .project-tasks-page .summary-card .project-meta {
                                   color: #64748b;
                                   margin-bottom: 0;
                              }

                              .project-tasks-page .task-table-card {
                                   border: none;
                                   border-radius: 16px;
                                   box-shadow: 0 12px 32px rgba(15, 23, 42, 0.1);
                              }

                              .project-tasks-page .task-table-card .card-body {
                                   padding: 28px 32px;
                              }

                              .project-tasks-page .task-table-card .table thead th {
                                   white-space: nowrap;
                                   font-size: 0.78rem;
                                   text-transform: uppercase;
                                   letter-spacing: 0.05em;
                              }

                              .project-tasks-page .task-table-card .table tbody td {
                                   vertical-align: middle;
                              }

                              .project-tasks-page .task-actions {
                                   display: inline-flex;
                                   gap: 8px;
                                   align-items: center;
                              }

                              .project-tasks-page .task-actions .badge {
                                   font-size: 0.75rem;
                              }

                              .project-tasks-page .no-tasks-card {
                                   border-radius: 16px;
                              }

                              .project-tasks-page {
                                   padding-bottom: 2.5rem;
                                   /* add breathing room above footer */
                              }

                              @media (max-width: 575.98px) {
                                   .project-tasks-page .task-table-card .card-body {
                                        padding: 20px;
                                   }
                              }

                              .project-tasks-page .modal .modal-header {
                                   border-top-left-radius: 0.9rem;
                                   border-top-right-radius: 0.9rem;
                              }

                              .project-tasks-page .modal .modal-content {
                                   border-radius: 1rem;
                              }

                              .project-tasks-page .task-actions {
                                   display: inline-flex;
                                   gap: 8px;
                                   align-items: center;
                                   justify-content: center;
                              }

                              /* Round icon buttons */
                              .project-tasks-page .task-actions .action-icon {
                                   position: relative;
                                   display: inline-flex;
                                   align-items: center;
                                   justify-content: center;
                                   width: 32px;
                                   height: 32px;
                                   border-radius: 50%;
                                   text-decoration: none;
                                   font-size: 0.9rem;
                                   transition: transform 0.15s ease, box-shadow 0.15s ease, background-color 0.15s ease;
                              }

                              /* Colors */
                              .project-tasks-page .task-actions .action-edit {
                                   color: #4a90e2;
                                   background: rgba(74, 144, 226, 0.12);
                              }

                              .project-tasks-page .task-actions .action-delete {
                                   color: #dc3545;
                                   background: rgba(220, 53, 69, 0.12);
                              }

                              .project-tasks-page .task-actions .action-icon:hover {
                                   transform: translateY(-1px);
                                   box-shadow: 0 4px 10px rgba(15, 23, 42, 0.15);
                              }

                              /* Tooltip label on hover */
                              .project-tasks-page .task-actions .action-icon::after {
                                   content: attr(data-label);
                                   position: absolute;
                                   bottom: -32px;
                                   left: 50%;
                                   transform: translate(-50%, 6px);
                                   background: rgba(33, 37, 41, 0.9);
                                   color: #fff;
                                   padding: 4px 8px;
                                   border-radius: 4px;
                                   font-size: 11px;
                                   white-space: nowrap;
                                   opacity: 0;
                                   pointer-events: none;
                                   transition: all 0.15s ease;
                                   z-index: 10;
                              }

                              .project-tasks-page .task-actions .action-icon:hover::after {
                                   opacity: 1;
                                   transform: translate(-50%, 0);
                              }

                              /* Extra breathing room so it doesn’t hug the footer */
                              .project-tasks-page {
                                   padding-bottom: 2.5rem;
                              }
                         </style>

                         <!-- Page header -->
                         <div class="row">
                              <div class="col-12">
                                   <div class="page-title-box d-flex align-items-center justify-content-between flex-wrap">
                                        <div>

                                        </div>
                                        <div class="mt-2 mt-sm-0">

                                        </div>
                                   </div>
                              </div>
                         </div>


                         <!-- Summary card -->
                         <div class="row">
                              <div class="col-12">
                                   <div class="card summary-card mb-4">
                                        <div class="card-body d-flex flex-wrap justify-content-between align-items-center">
                                             <div>
                                                  <p class="project-title text-primary mb-1"><?= $projectName; ?></p>
                                                  <p class="project-meta mb-0">
                                                       <?= $tasksExist
                                                            ? ($isClosedView
                                                                 ? 'Below are all CLOSED tasks linked to this project.'
                                                                 : 'Below are all OPEN tasks linked to this project.')
                                                            : ($isClosedView
                                                                 ? 'There are currently no CLOSED tasks linked to this project.'
                                                                 : 'There are currently no OPEN tasks linked to this project.'); ?><br>

                                                       <?php if ($projectIncharge !== ''): ?>
                                                            <span>Project In-Charge:
                                                                 <strong><?= $projectIncharge; ?></strong>
                                                            </span>
                                                       <?php endif; ?>
                                                  </p>
                                             </div>
                                             <div class="text-right mt-3 mt-sm-0">
                                                  <span class="badge badge-pill badge-primary px-3 py-2">
                                                       <?= number_format((int) $taskTotal); ?> Task<?= $taskTotal === 1 ? '' : 's'; ?>
                                                  </span>
                                             </div>
                                        </div>
                                   </div>
                              </div>
                         </div>

                         <!-- Task table -->
                         <div class="row">
                              <div class="col-12">
                                   <?php if ($tasksExist): ?>
                                        <div class="card task-table-card">

                                             <div class="card-body">

                                                  <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap">

                                                       <div>

                                                            <h5 class="card-title mb-1">Task List</h5>

                                                            <p class="text-muted mb-0">
                                                                 Monitor the tasks under this project and update their status.
                                                            </p>
                                                            <div class="btn-group" role="group" aria-label="Task status filter">
                                                                 <a href="<?= $openUrl; ?>"
                                                                      class="btn btn-sm <?= $isClosedView ? 'btn-outline-primary' : 'btn-primary'; ?>">
                                                                      Open Tasks
                                                                 </a>
                                                                 <a href="<?= $closedUrl; ?>"
                                                                      class="btn btn-sm <?= $isClosedView ? 'btn-primary' : 'btn-outline-primary'; ?>">
                                                                      Closed Tasks
                                                                 </a>
                                                            </div>
                                                       </div>
                                                       <?php if (!$isClosedView): ?>
                                                            <form method="post" action="<?= base_url(); ?>Page/bulkCloseProjectTasks" id="bulkCloseForm" class="mb-0">
                                                                 <input type="hidden" name="projectID" value="<?= htmlspecialchars((string) $projectID, ENT_QUOTES, 'UTF-8'); ?>">
                                                                 <button type="button" class="btn btn-sm btn-danger" data-toggle="modal" data-target="#bulkCloseModal">
                                                                      <i class="mdi mdi-checkbox-multiple-marked-outline"></i>
                                                                      Bulk Close All Tasks
                                                                 </button>
                                                            </form>
                                                       <?php endif; ?>
                                                  </div>

                                                  <div class="table-responsive">
                                                       <table id="project-task-table" class="table table-striped table-hover w-100">
                                                            <thead class="bg-secondary text-white">
                                                                 <tr>
                                                                      <th>Task</th>
                                                                      <th>Reported Date</th>
                                                                      <th>In-Charge</th>
                                                                      <th>Status</th>
                                                                      <th>Priority</th>
                                                                      <th class="text-center">Action</th>
                                                                 </tr>
                                                            </thead>
                                                            <tbody>
                                                                 <?php foreach ($data as $row): ?>
                                                                 <?php
                                                                      $taskNameRaw = trim((string) $row->task);
                                                                      $taskNameDisplay = function_exists('mb_strtoupper')
                                                                           ? mb_strtoupper($taskNameRaw, 'UTF-8')
                                                                           : strtoupper($taskNameRaw);
                                                                      $taskName = htmlspecialchars($taskNameDisplay, ENT_QUOTES, 'UTF-8');

                                                                      $reportedDate = '-';
                                                                      if (!empty($row->reportedDate) && $row->reportedDate !== '0000-00-00') {
                                                                           $timestamp = strtotime($row->reportedDate);
                                                                           $reportedDate = $timestamp
                                                                                ? date('F d, Y', $timestamp)
                                                                                : htmlspecialchars((string) $row->reportedDate, ENT_QUOTES, 'UTF-8');
                                                                      }

                                                                      // If your query already returns assignedPersonName (like in the main list)
                                                                      $assignedName = isset($row->assignedPersonName)
                                                                           ? htmlspecialchars((string) $row->assignedPersonName, ENT_QUOTES, 'UTF-8')
                                                                           : '';

                                                                      $statusLabel = ((int) $row->taskStat === 1) ? 'Open' : 'Closed';
                                                                      $statusClass = ((int) $row->taskStat === 1) ? 'badge-success' : 'badge-secondary';

                                                                      $priorityLabel = 'Low';
                                                                      $priorityClass = 'badge-info';
                                                                      if ((int) $row->priority === 1) {
                                                                           $priorityLabel = 'High';
                                                                           $priorityClass = 'badge-danger';
                                                                      } elseif ((int) $row->priority === 2) {
                                                                           $priorityLabel = 'Medium';
                                                                           $priorityClass = 'badge-warning';
                                                                      }

                                                                      $adminComment = isset($row->latestAdminComment) ? trim((string) $row->latestAdminComment) : '';
                                                                      $adminComment = str_replace(["\r", "\n"], ' ', $adminComment);
                                                                      $adminCommentEsc = htmlspecialchars($adminComment, ENT_QUOTES, 'UTF-8');
                                                                      $hasAdminComment = ($adminComment !== '');
                                                                      ?>
                                                                      <tr>
                                                                           <td><?= $taskName; ?></td>
                                                                           <td><?= $reportedDate; ?></td>
                                                                           <td><?= $assignedName !== '' ? $assignedName : '—'; ?></td>
                                                                           <td>
                                                                                <span class="badge badge-pill <?= $statusClass; ?>"><?= $statusLabel; ?></span>
                                                                           </td>
                                                                           <td>
                                                                                <span class="badge badge-pill <?= $priorityClass; ?>"><?= $priorityLabel; ?></span>
                                                                           </td>
                                                                           <td class="text-center">
                                                                                <div class="task-actions">
                                                                                     <a href="#addStatusModal"
                                                                                          class="badge badge-primary"
                                                                                          data-toggle="modal"
                                                                                          data-target="#addStatusModal"
                                                                                          data-task-id="<?= (int) $row->taskID; ?>">
                                                                                          Modify Status
                                                                                     </a>
                                                                                     <?php if ($hasAdminComment): ?>
                                                                                          <a href="javascript:void(0);"
                                                                                               class="badge badge-info ml-2"
                                                                                               data-toggle="modal"
                                                                                               data-target="#adminCommentModal"
                                                                                               data-task="<?= $taskName; ?>"
                                                                                               data-comment="<?= $adminCommentEsc; ?>">
                                                                                               Admin Comment
                                                                                          </a>
                                                                                     <?php endif; ?>
                                                                                </div>
                                                                           </td>
                                                                      </tr>
                                                                 <?php endforeach; ?>
                                                            </tbody>
                                                       </table>
                                                  </div>
                                             </div>
                                        </div>
                                   <?php else: ?>
                                        <div class="card no-tasks-card">
                                             <div class="card-body text-center py-5">
                                                  <h5 class="mb-2">No tasks found for this project yet.</h5>
                                                  <p class="text-muted mb-0">
                                                       Add tasks from the main task management page to see them listed here.
                                                  </p>
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

     <script src="<?= base_url(); ?>assets/libs/datatables/jquery.dataTables.min.js"></script>
     <script src="<?= base_url(); ?>assets/libs/datatables/dataTables.bootstrap4.min.js"></script>
     <script src="<?= base_url(); ?>assets/libs/datatables/dataTables.responsive.min.js"></script>
     <script src="<?= base_url(); ?>assets/libs/datatables/responsive.bootstrap4.min.js"></script>

     <script>
          (function($) {
               'use strict';

               $(function() {
                    var $table = $('#project-task-table');
                    if ($table.length) {
                         $table.DataTable({
                              responsive: true,
                              autoWidth: false,
                              order: [
                                   [1, 'desc']
                              ],
                              language: {
                                   emptyTable: 'No tasks recorded for this project.'
                              }
                         });
                    }

                    $('#addStatusModal').on('show.bs.modal', function(event) {
                         var button = $(event.relatedTarget);
                         var taskId = button.data('task-id');
                         $(this).find('#status-task-id').val(taskId);
                    });

                    $('#adminCommentModal').on('show.bs.modal', function(event) {
                         var button = $(event.relatedTarget);
                         if (!button || !button.length) return;

                         var $m = $(this);
                         $m.find('#admin-comment-task').val(button.attr('data-task') || '');
                         $m.find('#admin-comment-note').val(button.attr('data-comment') || '');
                    }).on('hidden.bs.modal', function() {
                         $(this).find('#admin-comment-task').val('');
                         $(this).find('#admin-comment-note').val('');
                    });

                    $('#bulkCloseConfirm').on('click', function() {
                         var $btn = $(this);
                         $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm mr-1" role="status" aria-hidden="true"></span>Closing...');
                         $('#bulkCloseForm').trigger('submit');
                    });
               });
          })(jQuery);
     </script>

     <div class="modal fade" id="bulkCloseModal" tabindex="-1" role="dialog" aria-labelledby="bulkCloseModalLabel" aria-hidden="true">
          <div class="modal-dialog modal-dialog-centered" role="document">
               <div class="modal-content" style="border:none;border-radius:14px;overflow:hidden;box-shadow:0 20px 50px rgba(15,23,42,.18);">
                    <div class="modal-header">
                         <h5 class="modal-title mb-0" id="bulkCloseModalLabel">
                              <i class="mdi mdi-alert-octagon-outline mr-1"></i>
                              Bulk Close All Open Tasks
                         </h5>
                         <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                              <span aria-hidden="true">&times;</span>
                         </button>
                    </div>
                    <div class="modal-body text-center py-4">
                         <div style="width:64px;height:64px;border-radius:50%;background:#fef2f2;color:#dc3545;display:flex;align-items:center;justify-content:center;font-size:2rem;margin:0 auto 14px;">
                              <i class="mdi mdi-checkbox-multiple-marked-outline"></i>
                         </div>
                         <h5 class="mb-2" style="font-weight:700;">Close every open task in this project?</h5>
                         <p class="text-muted mb-3" style="font-size:.9rem;">
                              All open tasks will be marked <span class="badge badge-secondary">Closed</span>
                              and a "Closed via bulk action." note will be recorded for each. This cannot be undone from here.
                         </p>
                         <?php if ($pointsEnabled): ?>
                              <div class="text-left mx-auto" style="max-width: 420px;">
                                   <label class="font-weight-bold d-block mb-2">Points handling</label>
                                   <div class="custom-control custom-radio mb-2">
                                        <input class="custom-control-input" type="radio" name="bulkCloseAwardPoints" id="bulkCloseAwardPointsYes" value="1" checked form="bulkCloseForm">
                                        <label class="custom-control-label" for="bulkCloseAwardPointsYes">
                                             Credit 1 point to each assigned personnel
                                        </label>
                                   </div>
                                   <div class="custom-control custom-radio">
                                        <input class="custom-control-input" type="radio" name="bulkCloseAwardPoints" id="bulkCloseAwardPointsNo" value="0" form="bulkCloseForm">
                                        <label class="custom-control-label" for="bulkCloseAwardPointsNo">
                                             Close tasks without adding points
                                        </label>
                                   </div>
                                   <small class="form-text text-muted mt-2">
                                        This only affects leaderboard points. The tasks will still be closed and recorded in the task history.
                                   </small>
                              </div>
                         <?php else: ?>
                              <div class="alert alert-light border text-left mb-0 mx-auto" style="max-width: 420px;">
                                   Points tracking is not enabled in this database, so bulk close will only update task status and history.
                              </div>
                         <?php endif; ?>
                    </div>
                    <div class="modal-footer" style="border-top:1px solid #eef2f7;">
                         <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
                         <button type="button" class="btn btn-danger" id="bulkCloseConfirm">
                              <i class="mdi mdi-check mr-1"></i>
                              Yes, close all
                         </button>
                    </div>
               </div>
          </div>
     </div>

     <div class="modal fade" id="addStatusModal" tabindex="-1" role="dialog" aria-hidden="true">
          <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
               <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                         <h5 class="modal-title mb-0">Modify Task Status</h5>
                         <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                              <span aria-hidden="true">&times;</span>
                         </button>
                    </div>
                    <form class="needs-validation" method="post" action="<?= base_url(); ?>Page/addTaskNote" novalidate>
                         <div class="modal-body">
                              <input type="hidden" name="dataid" id="status-task-id">
                              <div class="form-group">
                                   <label for="status-note">Notes</label>
                                   <textarea class="form-control" id="status-note" name="note" rows="3"></textarea>
                              </div>
                              <div class="form-group">
                                   <label for="status-taskStat">Current Status</label>
                                   <select class="form-control" id="status-taskStat" name="taskStat">
                                        <option value="1">Open</option>
                                        <option value="0">Closed</option>
                                   </select>
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

     <div class="modal fade" id="adminCommentModal" tabindex="-1" role="dialog" aria-hidden="true">
          <div class="modal-dialog modal-dialog-centered" role="document">
               <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                         <h5 class="modal-title mb-0">Admin Comment</h5>
                         <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                              <span aria-hidden="true">&times;</span>
                         </button>
                    </div>
                    <div class="modal-body">
                         <div class="form-group">
                              <label for="admin-comment-task">Task</label>
                              <input type="text" class="form-control" id="admin-comment-task" readonly>
                         </div>
                         <div class="form-group mb-0">
                              <label for="admin-comment-note">Comment</label>
                              <textarea class="form-control" id="admin-comment-note" rows="4" readonly></textarea>
                         </div>
                    </div>
                    <div class="modal-footer">
                         <button type="button" class="btn btn-light" data-dismiss="modal">Close</button>
                    </div>
               </div>
          </div>
     </div>

</body>

</html>
