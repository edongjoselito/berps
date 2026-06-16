<!DOCTYPE html>
<html lang="en">

<?php include('includes/head.php'); ?>

<body>

     <div id="wrapper">

          <?php include('includes/top-nav-bar.php'); ?>
          <?php include('includes/sidebar.php'); ?>

          <div class="content-page">
               <div class="content">
                    <div class="container-fluid reminders-page">
                         <style>
                              .reminders-page .page-title-wrapper {
                                   position: relative;
                                   background: #ffffff;
                                   border-radius: 16px;
                                   padding: 18px 22px 20px;
                                   margin-bottom: 24px;
                                   box-shadow: 0 18px 38px rgba(15, 23, 42, 0.08);
                                   border: 1px solid rgba(148, 163, 184, 0.18);
                              }

                              .reminders-page .page-title-wrapper::after {
                                   content: "";
                                   position: absolute;
                                   left: 22px;
                                   right: 22px;
                                   bottom: 10px;
                                   height: 2px;
                                   background: linear-gradient(to right, #4285F4 55%, #5dade2 80%, #34A853 100%);
                                   border-radius: 1px;
                              }

                              .reminders-page .page-title-box {
                                   display: flex;
                                   align-items: center;
                                   justify-content: space-between;
                                   flex-wrap: wrap;
                                   gap: 18px;
                                   margin: 0;
                              }

                              .reminders-page .page-title-stack {
                                   display: flex;
                                   align-items: flex-start;
                                   flex-direction: column;
                                   gap: 6px;
                              }

                              .reminders-page .page-title {
                                   margin: 0;
                                   font-weight: 600;
                                   color: #0f172a;
                              }

                              .reminders-page .subtitle-badge {
                                   display: inline-flex;
                                   align-items: center;
                                   padding: 0.3rem 0.75rem;
                                   border-radius: 999px;
                                   font-size: 0.74rem;
                                   background: rgba(94, 154, 255, 0.12);
                                   color: #1d4ed8;
                              }

                              .reminders-page .page-actions .btn {
                                   border-radius: 999px;
                                   padding: 0.35rem 0.95rem;
                                   display: inline-flex;
                                   align-items: center;
                                   gap: 6px;
                                   box-shadow: none;
                              }

                              .reminders-page .card-header {
                                   background: #5dade2;
                                   border-bottom: none;
                                   border-radius: 0.75rem 0.75rem 0 0;
                              }

                              .reminders-page .card-header h4 {
                                   color: #fff;
                                   margin-bottom: 0;
                              }

                              .reminders-page .card {
                                   border: none;
                                   border-radius: 0.75rem;
                                   box-shadow: 0 16px 32px rgba(15, 23, 42, 0.08);
                              }

                              .reminders-page .table .date-column {
                                   min-width: 160px;
                                   text-align: center;
                                   white-space: nowrap;
                              }

                              .reminders-page .table .frequency-column {
                                   min-width: 110px;
                                   text-align: center;
                              }

                              .reminders-page .date-stack {
                                   display: inline-flex;
                                   flex-direction: column;
                                   gap: 3px;
                                   align-items: center;
                                   white-space: nowrap;
                              }

                              .reminders-page .date-stack .date-primary {
                                   font-weight: 600;
                                   color: #0f172a;
                              }

                              .reminders-page .date-stack .date-secondary {
                                   font-size: 0.75rem;
                                   color: #64748b;
                                   letter-spacing: 0.02em;
                              }

                              .reminders-page table td,
                              .reminders-page table th {
                                   vertical-align: middle;
                              }

                              .reminders-page table td {
                                   word-break: break-word;
                              }
                         </style>

                         <div class="row">
                              <div class="col-12">
                                   <div class="page-title-wrapper">
                                        <div class="page-title-box">
                                             <div class="page-title-stack">
                                                  <h4 class="page-title mb-0">Reminder History</h4>
                                                  <span class="badge subtitle-badge">Archived reminders for reference</span>
                                             </div>
                                             <div class="page-actions">
                                                  <a href="<?= base_url('Reminders'); ?>" class="btn btn-primary btn-sm">
                                                       <i class="material-icons align-middle mr-1" style="font-size:18px;">arrow_back</i>Back to active reminders
                                                  </a>
                                             </div>
                                        </div>
                                   </div>
                              </div>
                         </div>

                         <?php if (!empty($dueToday)): ?>
                              <div class="alert alert-info shadow-sm">
                                   <strong>Reminder:</strong> You have <?= count($dueToday); ?> task(s) due today. Check the active list to stay updated.
                              </div>
                         <?php endif; ?>

                         <div class="card">
                              <div class="card-header">
                                   <h4 class="mb-0">Archived Reminders</h4>
                              </div>
                              <div class="card-body p-0">
                                   <div class="table-responsive">
                                        <table class="table table-striped table-centered mb-0">
                                             <thead class="bg-secondary text-white">
                                                  <tr class="text-center">
                                                       <th>Title</th>
                                                       <th class="d-none d-lg-table-cell">Description</th>
                                                       <th class="date-column">Original Due</th>
                                                       <th class="date-column">Archived On</th>
                                                       <th class="frequency-column">Frequency</th>
                                                  </tr>
                                             </thead>
                                             <tbody>
                                                  <?php if (!empty($history)): ?>
                                                       <?php foreach ($history as $item): ?>
                                                            <?php
                                                            $originalTs = strtotime($item->remind_at);
                                                            $archivedTs = strtotime($item->archived_at);
                                                            ?>
                                                            <tr>
                                                                 <td><?= $item->title; ?></td>
                                                                 <td class="d-none d-lg-table-cell"><?= $item->description; ?></td>
                                                                 <td class="date-column">
                                                                      <span class="date-stack">
                                                                           <span class="date-primary"><?= date('M d, Y', $originalTs); ?></span>
                                                                           <span class="date-secondary"><?= date('h:i A', $originalTs); ?></span>
                                                                      </span>
                                                                 </td>
                                                                 <td class="date-column">
                                                                      <span class="date-stack">
                                                                           <span class="date-primary"><?= date('M d, Y', $archivedTs); ?></span>
                                                                           <span class="date-secondary"><?= date('h:i A', $archivedTs); ?></span>
                                                                      </span>
                                                                 </td>
                                                                 <td class="frequency-column"><?= ucfirst($item->recurrence); ?></td>
                                                            </tr>
                                                       <?php endforeach; ?>
                                                  <?php else: ?>
                                                       <tr>
                                                            <td colspan="5" class="text-center text-muted py-4">No reminders have been archived yet.</td>
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
</body>

</html>
