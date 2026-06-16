<!DOCTYPE html>
<html lang="en">

<?php include('includes/head.php'); ?>

<body>

     <div id="wrapper">

          <?php include('includes/top-nav-bar.php'); ?>
          <?php include('includes/sidebar.php'); ?>

          <div class="content-page">
               <div class="content">
                    <div class="container-fluid taskstat-page">
                         <style>
                              :root {
                                   --fixed-footer-h: 90px;
                              }

                              /* Keep content clear of navbar/footer */
                              .taskstat-page {
                                   padding-top: var(--page-content-top-gap, 22px);
                                   padding-bottom: calc(var(--fixed-footer-h) + 28px);
                              }

                              .taskstat-page .breadcrumb {
                                   background: transparent;
                                   padding: 0;
                                   margin-bottom: 1.5rem;
                              }

                              .taskstat-page .card {
                                   border: none;
                                   border-radius: 16px;
                                   box-shadow: 0 16px 32px rgba(15, 23, 42, 0.08);
                              }

                              .taskstat-page .card-header {
                                   border-bottom: 1px solid rgba(15, 23, 42, 0.08);
                                   background: linear-gradient(130deg, #4c6ef5, #15aabf);
                                   color: #fff;
                                   padding: 22px 28px;
                                   border-top-left-radius: 16px;
                                   border-top-right-radius: 16px;
                              }

                              .taskstat-page .card-header h4 {
                                   margin: 0;
                                   font-weight: 600;
                                   font-size: 1.25rem;
                              }

                              .taskstat-page .card-body {
                                   padding: 28px 32px;
                              }

                              .taskstat-page .empty-state {
                                   text-align: center;
                                   padding: 60px 20px;
                                   border-radius: 16px;
                                   background: #f8f9fa;
                                   box-shadow: inset 0 0 0 1px rgba(15, 23, 42, 0.05);
                              }

                              .taskstat-page .empty-state h3 {
                                   font-weight: 600;
                                   color: #343a40;
                                   margin-bottom: 1.5rem;
                              }
                         </style>

                         <?php if (empty($data)): ?>
                              <div class="row">
                                   <div class="col-12">
                                        <div class="empty-state">
                                             <h3>Oops! No posted update yet.</h3>
                                             <a href="<?= base_url(); ?>Page/projectAddTask" class="btn btn-white">
                                                  <i class="mdi mdi-arrow-left"></i> Back to Task List
                                             </a>
                                        </div>
                                   </div>
                              </div>
                         <?php else: ?>
                              <?php
                              $task = $data[0];
                              $projectTitle = isset($task->projectDescription) ? (string) $task->projectDescription : 'Project';
                              $taskTitle = isset($task->task) ? (string) $task->task : 'Task Details';
                              ?>
                              <div class="row">
                                   <div class="col-12">
                                        <nav aria-label="breadcrumb">
                                             <ol class="breadcrumb pl-0">
                                                  <li class="breadcrumb-item">
                                                       <a href="<?= base_url(); ?>Page/projectAddTask">
                                                            <i class="mdi mdi-view-list-outline"></i> Tasks
                                                       </a>
                                                  </li>
                                                  <li class="breadcrumb-item active" aria-current="page">
                                                       <?= htmlspecialchars($projectTitle, ENT_QUOTES, 'UTF-8'); ?>
                                                  </li>
                                             </ol>
                                        </nav>
                                   </div>
                              </div>

                              <div class="row">
                                   <div class="col-12">
                                        <div class="card">
                                             <div class="card-header">
                                                  <h4>Task: <?= htmlspecialchars($taskTitle, ENT_QUOTES, 'UTF-8'); ?></h4>
                                             </div>
                                             <div class="card-body">
                                                  <div class="table-responsive">
                                                       <table id="task-stat-table" class="table table-striped table-bordered w-100">
                                                            <thead class="thead-light">
                                                                 <tr>
                                                                      <th style="width: 20%;">Date</th>
                                                                      <th style="width: 60%;">Note</th>
                                                                      <th style="width: 20%;">Posted By</th>
                                                                 </tr>
                                                            </thead>
                                                            <tbody>
                                                                 <?php foreach ($data as $row): ?>
                                                                      <?php
                                                                      $datePosted = isset($row->datePosted) ? (string) $row->datePosted : '';
                                                                      $note = isset($row->note) ? (string) $row->note : '';
                                                                      $postedBy = '';
                                                                      if (!empty($row->assignedPersonName)) {
                                                                           $postedBy = (string) $row->assignedPersonName;
                                                                      } elseif (!empty($row->postedBy)) {
                                                                           $postedBy = (string) $row->postedBy;
                                                                      }
                                                                      ?>
                                                                      <tr>
                                                                           <td><?= htmlspecialchars($datePosted, ENT_QUOTES, 'UTF-8'); ?></td>
                                                                           <td><?= nl2br(htmlspecialchars($note, ENT_QUOTES, 'UTF-8')); ?></td>
                                                                           <td><?= htmlspecialchars($postedBy, ENT_QUOTES, 'UTF-8'); ?></td>
                                                                      </tr>
                                                                 <?php endforeach; ?>
                                                            </tbody>
                                                       </table>
                                                  </div>
                                             </div>
                                        </div>
                                   </div>
                              </div>
                         <?php endif; ?>
                    </div>

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
     <script src="<?= base_url(); ?>assets/libs/datatables/jquery.dataTables.min.js"></script>
     <script src="<?= base_url(); ?>assets/libs/datatables/dataTables.bootstrap4.min.js"></script>
     <script src="<?= base_url(); ?>assets/libs/datatables/dataTables.buttons.min.js"></script>
     <script src="<?= base_url(); ?>assets/libs/datatables/buttons.bootstrap4.min.js"></script>
     <script src="<?= base_url(); ?>assets/libs/datatables/buttons.html5.min.js"></script>
     <script src="<?= base_url(); ?>assets/libs/datatables/buttons.print.min.js"></script>
     <script src="<?= base_url(); ?>assets/libs/datatables/dataTables.responsive.min.js"></script>
     <script src="<?= base_url(); ?>assets/libs/datatables/responsive.bootstrap4.min.js"></script>
     <script>
          (function($) {
               'use strict';
               $(function() {
                    var $table = $('#task-stat-table');
                    if ($table.length) {
                         $table.DataTable({
                              responsive: true,
                              autoWidth: false,
                              pageLength: 10,
                              order: [[0, 'desc']]
                         });
                    }
               });
          })(jQuery);
     </script>

</body>

</html>
