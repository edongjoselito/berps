<!DOCTYPE html>
<html lang="en">

<?php include('includes/head.php'); ?>

<body>

     <?php
     if (!function_exists('reminder_offset_label')) {
          function reminder_offset_label($minutes)
          {
               $minutes = (int) $minutes;
               if ($minutes <= 0) return 'At due time';
               if ($minutes % 1440 === 0) {
                    $days = $minutes / 1440;
                    if ($days >= 30) return '1 month before';
                    return $days . ' day' . ($days === 1 ? '' : 's') . ' before';
               }
               if ($minutes >= 60) {
                    $hours = $minutes / 60;
                    return $hours . ' hour' . ($hours === 1 ? '' : 's') . ' before';
               }
               return $minutes . ' minutes before';
          }
     }
     ?>

     <div id="wrapper">

          <?php include('includes/top-nav-bar.php'); ?>
          <?php include('includes/sidebar.php'); ?>

          <div class="content-page">
               <div class="content">
                    <div class="container-fluid reminders-page">
                         <style>
                              .reminders-page .page-title-box {
                                   margin-bottom: 18px;
                                   padding: 12px 0;
                              }

                              .reminders-page .card-header {
                                   background: #5dade2;
                                   border-bottom: none;
                              }

                              .reminders-page .card-header h4 {
                                   color: #fff;
                              }

                              .reminders-page .card-header .btn {
                                   background: rgba(255, 255, 255, 0.2);
                                   border: none;
                                   color: #fff;
                              }

                              .reminders-page .card-header .btn:hover {
                                   background: rgba(255, 255, 255, 0.35);
                              }

                              .reminders-page .table-action-btns {
                                   display: inline-flex;
                                   justify-content: center;
                                   align-items: center;
                                   gap: 8px;
                              }

                              .reminders-page .action-icon {
                                   width: 36px;
                                   height: 36px;
                                   border-radius: 999px;
                                   display: inline-flex;
                                   align-items: center;
                                   justify-content: center;
                                   transition: all 0.2s ease;
                                   text-decoration: none;
                                   color: #1f2937;
                                   background: rgba(15, 23, 42, 0.05);
                                   box-shadow: 0 4px 10px rgba(15, 23, 42, 0.08);
                              }

                              .reminders-page .action-icon i {
                                   font-size: 18px;
                              }

                              .reminders-page .action-icon-edit {
                                   color: #2563eb;
                                   background: rgba(37, 99, 235, 0.12);
                              }

                              .reminders-page .action-icon-delete {
                                   color: #d14343;
                                   background: rgba(209, 67, 67, 0.12);
                              }

                              .reminders-page .action-icon:hover {
                                   transform: translateY(-1px);
                                   box-shadow: 0 8px 16px rgba(15, 23, 42, 0.15);
                              }
                         </style>

                         <div class="row">
                              <div class="col-12">
                                   <div class="page-title-box">
                                        <h4 class="page-title">Reminder List<br />
                                             <span class="badge badge-purple mb-1">Stay on top of important tasks</span>
                                        </h4>
                                        <div class="clearfix"></div>
                                        <hr style="border:0; height:2px; background:linear-gradient(to right, #4285F4 60%, #FBBC05 80%, #34A853 100%); border-radius:1px; margin:10px 0 0;" />
                                   </div>
                              </div>
                         </div>

                         <?php if (!empty($dueToday)): ?>
                              <div class="alert alert-info shadow-sm">
                                   <strong>Reminder:</strong> You have <?= count($dueToday); ?> task(s) due today!
                                   <ul class="mb-0 mt-2 pl-3">
                                        <?php foreach ($dueToday as $rem): ?>
                                             <li><?= $rem->title; ?> - <?= $rem->description; ?></li>
                                        <?php endforeach; ?>
                                   </ul>
                              </div>
                         <?php endif; ?>

                         <?php if ($this->session->flashdata('success')): ?>
                              <div class="alert alert-success alert-dismissible fade show" role="alert">
                                   <?= $this->session->flashdata('success'); ?>
                                   <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                   </button>
                              </div>
                         <?php endif; ?>

                         <div class="card">
                              <div class="card-header d-flex justify-content-between align-items-center">
                                   <h4 class="mb-0">Reminders</h4>
                                   <div class="d-flex align-items-center">
                                        <a href="<?= base_url('Reminders/history'); ?>" class="btn btn-sm mr-2">
                                             <i class="material-icons align-middle mr-1" style="font-size:18px;">history</i>History
                                        </a>
                                        <button class="btn btn-sm" data-toggle="modal" data-target="#addReminderModal">
                                             <i class="material-icons align-middle mr-1" style="font-size:18px;">add_alert</i>Add Reminder
                                        </button>
                                   </div>
                              </div>
                              <div class="card-body">
                                   <div class="table-responsive">
                                        <table id="reminders-table" class="table table-hover table-striped table-centered align-middle mb-0">
                                             <thead class="bg-secondary text-white">
                                                 <tr class="text-center">
                                                      <th>Title</th>
                                                      <th>Description</th>
                                                      <th>Due Date / Time</th>
                                                      <th>Notify</th>
                                                      <th>Frequency</th>
                                                      <th>Actions</th>
                                                 </tr>
                                             </thead>
                                             <tbody>
                                                  <?php if (!empty($reminders)): ?>
                                                       <?php foreach ($reminders as $rem): ?>
                                                            <tr>
                                                                 <td><?= $rem->title; ?></td>
                                                                 <td><?= $rem->description; ?></td>
                                                                 <td class="text-center"><?= date('M d, Y h:i A', strtotime($rem->remind_at)); ?></td>
                                                                 <td class="text-center">
                                                                      <?= reminder_offset_label($rem->notify_before_minutes ?? 0); ?>
                                                                 </td>
                                                                 <td class="text-center"><?= ucfirst($rem->recurrence); ?></td>
                                                                 <td class="text-center">
                                                                      <div class="table-action-btns">
                                                                           <a href="<?= base_url('Reminders/edit/' . $rem->id); ?>" class="action-icon action-icon-edit" data-toggle="tooltip" title="Edit reminder">
                                                                                <i class="material-icons">edit</i>
                                                                           </a>
                                                                           <a href="<?= base_url('Reminders/delete/' . $rem->id); ?>" class="action-icon action-icon-delete" data-toggle="tooltip" title="Delete reminder" onclick="return confirm('Are you sure you want to delete this reminder?');">
                                                                                <i class="material-icons">delete</i>
                                                                           </a>
                                                                      </div>
                                                                 </td>
                                                            </tr>
                                                       <?php endforeach; ?>
                                                  <?php else: ?>
                                                       <tr>
                                                            <td colspan="5" class="text-center text-muted">No reminders found.</td>
                                                       </tr>
                                                  <?php endif; ?>
                                             </tbody>
                                        </table>
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
     <script src="<?= base_url(); ?>assets/libs/datatables/jquery.dataTables.min.js"></script>
     <script src="<?= base_url(); ?>assets/libs/datatables/dataTables.bootstrap4.min.js"></script>
     <script src="<?= base_url(); ?>assets/libs/datatables/dataTables.buttons.min.js"></script>
     <script src="<?= base_url(); ?>assets/libs/datatables/buttons.bootstrap4.min.js"></script>
     <script src="<?= base_url(); ?>assets/libs/jszip/jszip.min.js"></script>
     <script src="<?= base_url(); ?>assets/libs/pdfmake/pdfmake.min.js"></script>
     <script src="<?= base_url(); ?>assets/libs/pdfmake/vfs_fonts.js"></script>
     <script src="<?= base_url(); ?>assets/libs/datatables/buttons.html5.min.js"></script>
     <script src="<?= base_url(); ?>assets/libs/datatables/buttons.print.min.js"></script>
     <script src="<?= base_url(); ?>assets/libs/datatables/dataTables.responsive.min.js"></script>
     <script src="<?= base_url(); ?>assets/libs/datatables/responsive.bootstrap4.min.js"></script>
     <script src="<?= base_url(); ?>assets/libs/datatables/dataTables.keyTable.min.js"></script>
     <script src="<?= base_url(); ?>assets/libs/datatables/dataTables.select.min.js"></script>
     <script src="<?= base_url(); ?>assets/js/pages/datatables.init.js"></script>

     <script>
          (function($) {
               'use strict';

               $(function() {
                    $('#reminders-table').DataTable({
                         responsive: true,
                         autoWidth: false,
                         order: [],
                         language: {
                              emptyTable: 'No reminders found.'
                         }
                    });
               });
          })(jQuery);
     </script>

     <div class="modal fade" id="addReminderModal" tabindex="-1" role="dialog" aria-hidden="true">
          <div class="modal-dialog modal-lg" role="document">
               <div class="modal-content">
                    <div class="modal-header bg-success text-white">
                         <h5 class="modal-title mb-0">Add Reminder</h5>
                         <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                              <span aria-hidden="true">&times;</span>
                         </button>
                    </div>
                    <form method="post" action="<?= base_url('Reminders/add'); ?>" class="needs-validation" novalidate>
                         <div class="modal-body">
                              <div class="form-row">
                                   <div class="col-md-12 mb-3">
                                        <label for="title">Title</label>
                                        <input type="text" name="title" id="title" class="form-control" required>
                                   </div>
                              </div>
                              <div class="form-row">
                                   <div class="col-md-12 mb-3">
                                        <label for="description">Description</label>
                                        <textarea name="description" id="description" class="form-control" rows="3" required></textarea>
                                   </div>
                              </div>
                              <div class="form-row">
                                  <div class="col-md-6 mb-3">
                                        <label for="remind_at">Due Date</label>
                                        <input type="date" name="remind_at" id="remind_at" class="form-control" required>
                                   </div>
                                   <div class="col-md-6 mb-3">
                                        <label for="remind_time">Due Time</label>
                                        <input type="time" name="remind_time" id="remind_time" class="form-control" value="<?= date('H:i'); ?>" required>
                                   </div>
                              </div>
                              <div class="form-row">
                                   <div class="col-md-6 mb-3">
                                        <label for="recurrence">Frequency</label>
                                        <select name="recurrence" id="recurrence" class="form-control" required>
                                             <option value="once">Once</option>
                                             <option value="monthly">Monthly</option>
                                             <option value="yearly">Yearly</option>
                                        </select>
                                   </div>
                                   <div class="col-md-6 mb-3">
                                        <label for="notify_before">Notify me</label>
                                        <select name="notify_before" id="notify_before" class="form-control">
                                             <option value="0">At due time</option>
                                             <option value="30">30 minutes before</option>
                                             <option value="60">1 hour before</option>
                                             <option value="1440">1 day before</option>
                                             <option value="14400">10 days before</option>
                                             <option value="43200">1 month before</option>
                                        </select>
                                   </div>
                              </div>
                              <input type="hidden" name="save" value="1">
                         </div>
                         <div class="modal-footer">
                              <button type="submit" class="btn btn-success">Save</button>
                              <button type="reset" class="btn btn-warning">Reset</button>
                              <button type="button" class="btn btn-light" data-dismiss="modal">Close</button>
                         </div>
                    </form>
               </div>
          </div>
     </div>

</body>

</html>
