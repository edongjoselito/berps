<!DOCTYPE html>
<html lang="en">

<?php include('includes/head.php'); ?>

<body>

     <div id="wrapper">

          <?php include('includes/top-nav-bar.php'); ?>
          <?php include('includes/sidebar.php'); ?>

          <div class="content-page">
               <div class="content">
                    <div class="container-fluid birthday-page birthday-month">
                         <style>
                              .birthday-page .page-header-card {
                                   border: none;
                                   border-radius: 18px;
                                   box-shadow: 0 16px 32px rgba(15, 23, 42, 0.08);
                                   margin-bottom: 28px;
                              }

                              .birthday-page .page-header-card .card-header {
                                   border-bottom: none;
                                   background: #fff;
                              }

                              .birthday-page .page-header-card .card-body {
                                   padding: 24px 32px;
                                   background: #fff;
                              }

                              .birthday-page .summary-header {
                                   display: flex;
                                   align-items: center;
                                   justify-content: space-between;
                                   padding: 10px 16px;
                                   border-radius: 14px;
                                   background: #f8fafc;
                              }

                              .birthday-page .summary-header .summary-content h3 {
                                   font-weight: 600;
                                   margin-bottom: 4px;
                              }

                              .birthday-page .summary-header .summary-content p {
                                   margin-bottom: 0;
                                   color: #6b7280;
                              }

                              .birthday-page .summary-header .summary-icon {
                                   width: 60px;
                                   height: 60px;
                                   border-radius: 14px;
                                   background: rgba(96, 165, 250, 0.15);
                                   display: flex;
                                   align-items: center;
                                   justify-content: center;
                              }

                              .birthday-page .summary-header .summary-icon i {
                                   font-size: 30px;
                                   color: #3b82f6;
                              }

                              .birthday-page .page-header-card .breadcrumb {
                                   background: transparent;
                                   padding: 0;
                                   margin-bottom: 0;
                              }

                              .birthday-page .celebrants-card {
                                   border: none;
                                   border-radius: 18px;
                                   box-shadow: 0 14px 28px rgba(15, 23, 42, 0.07);
                              }

                              .birthday-page .empty-state {
                                   border-radius: 18px;
                                   padding: 60px 20px;
                                   background: #fff;
                                   box-shadow: inset 0 0 0 1px rgba(15, 23, 42, 0.06);
                                   text-align: center;
                              }

                              .birthday-page .empty-state i {
                                   font-size: 48px;
                                   color: #60a5fa;
                                   display: block;
                                   margin-bottom: 16px;
                              }
                         </style>

                         <?php
                         $celebrants = isset($celebrants) && is_array($celebrants) ? $celebrants : [];
                         ?>

                         <div class="card page-header-card">
                              <div class="card-body">
                                   <div class="summary-header mb-3">
                                        <div class="summary-content">
                                             <h3 class="mb-1">Birthday Celebrants — This Month</h3>
                                             <p class="mb-0">Celebrants for <?= date('F Y'); ?> · Total: <?= count($celebrants); ?></p>
                                        </div>
                                        <div class="summary-icon">
                                             <i class="mdi mdi-calendar-star"></i>
                                        </div>
                                   </div>
                                   <ol class="breadcrumb mb-0">
                                        <li class="breadcrumb-item">
                                             <a href="<?= base_url('Page/admin'); ?>">Home</a>
                                        </li>
                                        <li class="breadcrumb-item active">Monthly Celebrants</li>
                                   </ol>
                              </div>
                         </div>

                         <?php if (empty($celebrants)): ?>
                              <div class="empty-state">
                                   <i class="mdi mdi-cake-variant"></i>
                                   <h4 class="mb-2">No birthday celebrants this month</h4>
                                   <p class="text-muted mb-0">We'll keep this list updated as we approach new celebrations.</p>
                              </div>
                         <?php else: ?>
                              <div class="card celebrants-card">
                                   <div class="card-body">
                                        <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
                                             <div>
                                                  <h4 class="mb-1">Celebrants for <span class="text-primary"><?= date('F Y'); ?></span></h4>
                                                  <p class="text-muted mb-0">Plan ahead and send your greetings throughout the month.</p>
                                             </div>
                                             <div class="text-muted font-weight-semibold">
                                                  Total celebrants: <?= count($celebrants); ?>
                                             </div>
                                        </div>
                                        <div class="table-responsive">
                                             <table id="table-bday-month" class="table table-striped table-bordered w-100">
                                                  <thead class="thead-light">
                                                       <tr>
                                                            <th>#</th>
                                                            <th>Employee ID</th>
                                                            <th>Full Name</th>
                                                            <th>Department</th>
                                                            <th>Position</th>
                                                            <th>Birth Date</th>
                                                            <th>Day</th>
                                                            <th>Age</th>
                                                       </tr>
                                                  </thead>
                                                  <tbody>
                                                       <?php $i = 1; ?>
                                                       <?php foreach ($celebrants as $row): ?>
                                                            <?php
                                                            $fullName = trim(
                                                                 ($row->lName ?? '') . ', ' .
                                                                 ($row->fName ?? '') . ' ' .
                                                                 ($row->mName ?? '')
                                                            );
                                                            $birthDateObj = isset($row->bDate) ? strtotime($row->bDate) : false;
                                                            $birthDate = $birthDateObj ? date('F d', $birthDateObj) : '-';
                                                            $birthDay = isset($row->birth_day) ? str_pad((string) $row->birth_day, 2, '0', STR_PAD_LEFT) : ($birthDateObj ? date('d', $birthDateObj) : '-');
                                                            $age = isset($row->age) ? (int) $row->age : '-';
                                                            ?>
                                                            <tr>
                                                                 <td><?= $i++; ?></td>
                                                                 <td><?= htmlspecialchars($row->empID ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                                                                 <td><?= htmlspecialchars($fullName, ENT_QUOTES, 'UTF-8'); ?></td>
                                                                 <td><?= htmlspecialchars($row->department ?? '-', ENT_QUOTES, 'UTF-8'); ?></td>
                                                                 <td><?= htmlspecialchars($row->empPosition ?? '-', ENT_QUOTES, 'UTF-8'); ?></td>
                                                                 <td><?= htmlspecialchars($birthDate, ENT_QUOTES, 'UTF-8'); ?></td>
                                                                 <td><?= htmlspecialchars($birthDay, ENT_QUOTES, 'UTF-8'); ?></td>
                                                                 <td><?= htmlspecialchars($age, ENT_QUOTES, 'UTF-8'); ?></td>
                                                            </tr>
                                                       <?php endforeach; ?>
                                                  </tbody>
                                             </table>
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
     <script src="<?= base_url(); ?>assets/libs/jszip/jszip.min.js"></script>
     <script src="<?= base_url(); ?>assets/libs/pdfmake/pdfmake.min.js"></script>
     <script src="<?= base_url(); ?>assets/libs/pdfmake/vfs_fonts.js"></script>
     <script src="<?= base_url(); ?>assets/libs/datatables/buttons.html5.min.js"></script>
     <script src="<?= base_url(); ?>assets/libs/datatables/buttons.print.min.js"></script>
     <script src="<?= base_url(); ?>assets/libs/datatables/dataTables.responsive.min.js"></script>
     <script src="<?= base_url(); ?>assets/libs/datatables/responsive.bootstrap4.min.js"></script>
     <script>
          (function($) {
               'use strict';
               $(function() {
                    var $table = $('#table-bday-month');
                    if ($table.length) {
                         $table.DataTable({
                              pageLength: 25,
                              responsive: true,
                              order: [
                                   [6, 'asc'],
                                   [2, 'asc']
                              ],
                              dom: 'Bfrtip',
                              buttons: [
                                   {
                                        extend: 'excelHtml5',
                                        title: 'Birthday_Celebrants_This_Month'
                                   },
                                   {
                                        extend: 'print',
                                        title: 'Birthday Celebrants - This Month'
                                   }
                              ]
                         });
                    }
               });
          })(jQuery);
     </script>

</body>

</html>
