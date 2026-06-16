<!DOCTYPE html>
<html lang="en">

<?php include('includes/head.php'); ?>

<?php
$dueDateValue = '';
if (!empty($reminder->remind_at)) {
     $dueDateValue = date('Y-m-d', strtotime($reminder->remind_at));
}
$dueDateDisplay = !empty($reminder->remind_at) ? date('M d, Y', strtotime($reminder->remind_at)) : 'Not set';
$dueTimeDisplay = !empty($reminder->remind_at) ? date('h:i A', strtotime($reminder->remind_at)) : '--:--';
$recurrenceLabel = !empty($reminder->recurrence) ? ucfirst($reminder->recurrence) : 'Once';
$notifyBeforeValue = isset($reminder->notify_before_minutes) ? (int)$reminder->notify_before_minutes : 0;
$dueTimeValue = !empty($reminder->remind_at) ? date('H:i', strtotime($reminder->remind_at)) : date('H:i');
?>

<body>

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

                              .reminders-page .page-subtitle {
                                   display: inline-block;
                                   margin: 12px 0 8px;
                                   letter-spacing: 0.02em;
                              }

                              .reminders-page .page-title-box.d-flex {
                                   flex-wrap: wrap;
                              }

                              .reminders-page .card-header {
                                   background: #5dade2;
                                   border-bottom: none;
                              }

                              .reminders-page .card-header h4 {
                                   color: #fff;
                              }
                         </style>

                         <div class="row">
                              <div class="col-12">
                                   <div class="page-title-box d-flex justify-content-between align-items-start">
                                        <div>
                                             <h4 class="page-title mb-0">Edit Reminder</h4>
                                             <span class="badge badge-purple page-subtitle">Update the reminder details below</span>
                                        </div>
                                        <a href="<?= base_url('Reminders'); ?>" class="btn btn-primary btn-sm">
                                             <i class="material-icons align-middle mr-1" style="font-size:18px;">arrow_back</i>Back to list
                                        </a>
                                   </div>
                                   <hr style="border:0; height:2px; background:linear-gradient(to right, #4285F4 60%, #FBBC05 80%, #34A853 100%); border-radius:1px; margin:10px 0 20px;" />
                              </div>
                         </div>

                         <?php if (!empty($dueToday)): ?>
                              <div class="alert alert-info shadow-sm">
                                   <strong>Reminder:</strong> You still have <?= count($dueToday); ?> task(s) due today.
                              </div>
                         <?php endif; ?>

                         <div class="card">
                              <div class="card-header">
                                   <h4 class="mb-0">Reminder Details</h4>
                              </div>
                              <div class="card-body">
                                   <form method="post" action="<?= base_url('Reminders/update/' . $reminder->id); ?>" class="needs-validation" novalidate>
                                        <div class="form-row">
                                             <div class="col-md-12 mb-3">
                                                  <label for="title">Title</label>
                                                  <input type="text" name="title" id="title" class="form-control" value="<?= html_escape($reminder->title); ?>" required>
                                             </div>
                                        </div>
                                        <div class="form-row">
                                             <div class="col-md-12 mb-3">
                                                  <label for="description">Description</label>
                                                  <textarea name="description" id="description" class="form-control" rows="3" required><?= html_escape($reminder->description); ?></textarea>
                                             </div>
                                        </div>
                                        <div class="form-row">
                                             <div class="col-md-6 mb-3">
                                                  <label for="remind_at">Due Date</label>
                                                  <input type="date" name="remind_at" id="remind_at" class="form-control" value="<?= $dueDateValue; ?>" required>
                                             </div>
                                             <div class="col-md-6 mb-3">
                                                  <label for="remind_time">Due Time</label>
                                                  <input type="time" name="remind_time" id="remind_time" class="form-control" value="<?= $dueTimeValue; ?>" required>
                                             </div>
                                             <div class="col-md-6 mb-3">
                                                  <label for="recurrence">Frequency</label>
                                             <select name="recurrence" id="recurrence" class="form-control" required>
                                                  <option value="once" <?= $reminder->recurrence === 'once' ? 'selected' : ''; ?>>Once</option>
                                                  <option value="monthly" <?= $reminder->recurrence === 'monthly' ? 'selected' : ''; ?>>Monthly</option>
                                                  <option value="yearly" <?= $reminder->recurrence === 'yearly' ? 'selected' : ''; ?>>Yearly</option>
                                             </select>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                             <label for="notify_before">Notify me</label>
                                             <select name="notify_before" id="notify_before" class="form-control">
                                                  <?php
                                                  $options = [
                                                       0      => 'At due time',
                                                       30     => '30 minutes before',
                                                       60     => '1 hour before',
                                                       1440   => '1 day before',
                                                       14400  => '10 days before',
                                                       43200  => '1 month before',
                                                  ];
                                                  foreach ($options as $val => $label):
                                                  ?>
                                                       <option value="<?= $val; ?>" <?= ($notifyBeforeValue === (int) $val) ? 'selected' : ''; ?>><?= $label; ?></option>
                                                  <?php endforeach; ?>
                                             </select>
                                        </div>
                                  </div>
                                        <div class="text-right">
                                             <a href="<?= base_url('Reminders'); ?>" class="btn btn-light">Cancel</a>
                                             <button type="submit" class="btn btn-primary" name="save" value="1">Save Changes</button>
                                        </div>
                                   </form>
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
</body>

</html>
