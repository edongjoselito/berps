<!DOCTYPE html>
<html lang="en">

<?php include('includes/head.php'); ?>

<body>

     <div id="wrapper">

          <?php include('includes/top-nav-bar.php'); ?>
          <?php include('includes/sidebar.php'); ?>

          <div class="content-page">
               <div class="content">
                    <div class="container-fluid payment-range-data-page">
                         <style>
                              .payment-range-data-page .page-title-box {
                                   padding: 12px 0;
                                   margin-bottom: 18px;
                              }

                              .payment-range-data-page .table thead th {
                                   white-space: nowrap;
                                   font-size: 0.875rem;
                              }

                              .payment-range-data-page .table td,
                              .payment-range-data-page .table th {
                                   vertical-align: middle;
                              }

                              .payment-range-data-page .payment-date {
                                   white-space: nowrap;
                              }

                              .payment-range-data-page #range-table th:first-child,
                              .payment-range-data-page #range-table td.payment-date {
                                   min-width: 110px;
                              }

                              .payment-range-data-page .payment-actions {
                                   display: inline-flex;
                                   gap: 6px;
                              }

                              .payment-range-data-page .payment-actions .action-icon {
                                   position: relative;
                                   display: inline-flex;
                                   align-items: center;
                                   justify-content: center;
                                   width: 34px;
                                   height: 34px;
                                   border-radius: 50%;
                                   transition: all 0.2s ease;
                                   text-decoration: none;
                                   font-size: 16px;
                              }

                              .payment-range-data-page .payment-actions .action-icon.edit {
                                   color: #4a90e2;
                                   background: rgba(74, 144, 226, 0.12);
                              }

                              .payment-range-data-page .payment-actions .action-icon.edit:hover {
                                   background: rgba(74, 144, 226, 0.24);
                              }

                              .payment-range-data-page .payment-actions .action-icon.delete {
                                   color: #dc3545;
                                   background: rgba(220, 53, 69, 0.12);
                              }

                              .payment-range-data-page .payment-actions .action-icon.delete:hover {
                                   background: rgba(220, 53, 69, 0.24);
                              }

                              .payment-range-data-page .payment-actions .action-icon::after {
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
                              }

                              .payment-range-data-page .payment-actions .action-icon:hover::after {
                                   opacity: 1;
                                   transform: translate(-50%, 0);
                              }

                              .payment-range-data-page .data-table-container {
                                   position: relative;
                              }

                              .payment-range-data-page .data-table-container.loading::after {
                                   content: 'Loading payments…';
                                   position: absolute;
                                   inset: 0;
                                   background: rgba(255, 255, 255, 0.85);
                                   display: flex;
                                   align-items: center;
                                   justify-content: center;
                                   font-size: 0.9rem;
                                   color: #6c757d;
                                   z-index: 1;
                              }

                              .payment-range-data-page .data-table-container.loading::before {
                                   content: '';
                                   position: absolute;
                                   top: 50%;
                                   left: 50%;
                                   width: 26px;
                                   height: 26px;
                                   margin: -40px 0 0 -13px;
                                   border-radius: 50%;
                                   border: 3px solid rgba(108, 117, 125, 0.3);
                                   border-top-color: rgba(108, 117, 125, 0.8);
                                   animation: payment-range-spinner 0.7s linear infinite;
                                   z-index: 2;
                              }

                              .payment-range-data-page .data-table-container.ready::after,
                              .payment-range-data-page .data-table-container.ready::before {
                                   display: none;
                              }

                              .payment-range-data-page .table-init-hidden {
                                   opacity: 0;
                              }

                              .payment-range-data-page .table-init-ready {
                                   opacity: 1;
                                   transition: opacity 0.2s ease;
                              }

                              @keyframes payment-range-spinner {
                                   to {
                                        transform: rotate(360deg);
                                   }
                              }

                              .payment-range-data-page .summary-card {
                                   border-radius: 16px;
                              }
                              .payment-range-data-page .summary-card h5 {
                                   font-size: 1.1rem;
                              }
                         </style>

                         <?php
                         $fromDate = $this->input->post('from');
                         $toDate = $this->input->post('to');
                         $totalCollections = isset($data1[0]->Total) ? (float) $data1[0]->Total : 0;
                         $totalExpenses = isset($data3[0]->Total) ? (float) $data3[0]->Total : 0;
                         $netIncome = $totalCollections - $totalExpenses;
                         ?>

                         <div class="row">
                              <div class="col-12">
                                   <div class="page-title-box">
                                        <h4 class="page-title">Payment Collections<br />
                                             <span class="badge badge-purple mb-1">
                                                  <?php if (!empty($fromDate) || !empty($toDate)): ?>
                                                       Range: <?= $fromDate ?: '—'; ?> to <?= $toDate ?: '—'; ?>
                                                  <?php else: ?>
                                                       Filtered results
                                                  <?php endif; ?>
                                             </span>
                                        </h4>
                                        <div class="clearfix"></div>
                                        <hr style="border:0; height:2px; background:linear-gradient(to right, #4285F4 60%, #FBBC05 80%, #34A853 100%); border-radius:1px; margin:20px 0;" />
                                   </div>
                              </div>
                         </div>

                         <div class="card summary-card mb-4">
                              <div class="card-body d-flex flex-wrap justify-content-between align-items-center">
                                   <div>
                                        <h5 class="mb-1">Total Collections</h5>
                                        <span class="h4 mb-0 text-primary"><?= number_format($totalCollections, 2); ?></span>
                                   </div>
                                   <div>
                                        <h5 class="mb-1 text-muted">Total Expenses</h5>
                                        <span class="h5 mb-0"><?= number_format($totalExpenses, 2); ?></span>
                                   </div>
                                   <div>
                                        <h5 class="mb-1 text-muted">Net Income</h5>
                                        <span class="h4 mb-0 text-success"><?= number_format($netIncome, 2); ?></span>
                                   </div>
                              </div>
                         </div>

                         <div class="card mb-4">
                              <div class="card-body">
                                   <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
                                        <h5 class="mb-0">Payments</h5>
                                        <?php if ($this->session->userdata('level') === 'Admin'): ?>
                                             <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#paymentModal">
                                                  <i class="mdi mdi-credit-card-plus-outline mr-1"></i>Add Payment
                                             </button>
                                        <?php endif; ?>
                                   </div>

                                   <div class="table-responsive data-table-container loading">
                                        <table id="range-table" class="table table-hover table-striped table-centered mb-0 table-init-hidden">
                                             <thead class="bg-secondary text-white">
                                                  <tr>
                                                       <th>Date</th>
                                                       <th class="text-right">Amount Paid</th>
                                                       <th>O.R. No.</th>
                                                       <th>Description</th>
                                                       <th>Payor</th>
                                                       <th class="text-center">Action</th>
                                                  </tr>
                                             </thead>
                                             <tbody>
                                                  <?php if (!empty($data)): ?>
                                                       <?php foreach ($data as $row): ?>
                                                            <tr>
                                                                 <td class="payment-date"><?= $row->PDate; ?></td>
                                                                 <td class="text-right"><?= number_format($row->AmountPaid, 2); ?></td>
                                                                 <td class="text-center"><?= $row->ORNo; ?></td>
                                                                 <td><?= $row->TransDescription; ?></td>
                                                                 <td>
                                                                      <a href="<?= base_url(); ?>Page/customerHistory?customer=<?= $row->Customer; ?>">
                                                                           <?= $row->Customer; ?>
                                                                      </a>
                                                                 </td>
                                                                 <td class="text-center">
                                                                      <?php if ($this->session->userdata('level') === 'Admin'): ?>
                                                                           <div class="payment-actions">
                                                                                <a class="action-icon edit" href="<?= base_url(); ?>Page/updatePayment?id=<?= $row->paymentID; ?>" data-label="Edit" title="Edit">
                                                                                     <i class="mdi mdi-square-edit-outline"></i>
                                                                                     <span class="sr-only">Edit</span>
                                                                                </a>
                                                                                <a class="action-icon delete" href="<?= base_url(); ?>Page/deletePayment?id=<?= $row->paymentID; ?>" onclick="return confirm('Are you sure you want to delete this record?');" data-label="Delete" title="Delete">
                                                                                     <i class="mdi mdi-trash-can-outline"></i>
                                                                                     <span class="sr-only">Delete</span>
                                                                                </a>
                                                                           </div>
                                                                      <?php else: ?>
                                                                           <span class="text-muted">—</span>
                                                                      <?php endif; ?>
                                                                 </td>
                                                            </tr>
                                                       <?php endforeach; ?>
                                                  <?php endif; ?>
                                             </tbody>
                                        </table>
                                   </div>
                              </div>
                         </div>

                         <?php if ($this->session->userdata('level') === 'Admin'): ?>
                         <div class="row">
                              <div class="col-xl-6 mb-4">
                                   <div class="card h-100">
                                        <div class="card-header">
                                             <h5 class="mb-0">Summary by Description</h5>
                                        </div>
                                        <div class="card-body p-0">
                                             <div class="table-responsive">
                                                  <table class="table mb-0">
                                                       <thead>
                                                            <tr>
                                                                 <th>Description</th>
                                                                 <th class="text-right">Total</th>
                                                            </tr>
                                                       </thead>
                                                       <tbody>
                                                            <?php if (!empty($data2)): ?>
                                                                 <?php foreach ($data2 as $row): ?>
                                                                      <tr>
                                                                           <td><?= $row->TransDescription; ?></td>
                                                                           <td class="text-right"><?= number_format($row->Total, 2); ?></td>
                                                                      </tr>
                                                                 <?php endforeach; ?>
                                                            <?php else: ?>
                                                                 <tr>
                                                                      <td colspan="2" class="text-center text-muted">No summary data available.</td>
                                                                 </tr>
                                                            <?php endif; ?>
                                                       </tbody>
                                                  </table>
                                             </div>
                                        </div>
                                   </div>
                              </div>

                              <div class="col-xl-6 mb-4">
                                   <div class="card h-100">
                                        <div class="card-header">
                                             <h5 class="mb-0">Collections per Cashier</h5>
                                        </div>
                                        <div class="card-body p-0">
                                             <div class="table-responsive">
                                                  <table class="table mb-0">
                                                       <thead>
                                                            <tr>
                                                                 <th>Cashier</th>
                                                                 <th class="text-right">Total</th>
                                                            </tr>
                                                       </thead>
                                                       <tbody>
                                                            <?php if (!empty($data4)): ?>
                                                                 <?php foreach ($data4 as $row): ?>
                                                                      <tr>
                                                                           <td>
                                                                                <a href="<?= base_url(); ?>Page/collectionsEmployee?name=<?= $row->Cashier; ?>">
                                                                                     <?= $row->Cashier; ?>
                                                                                </a>
                                                                           </td>
                                                                           <td class="text-right"><?= number_format($row->Total, 2); ?></td>
                                                                      </tr>
                                                                 <?php endforeach; ?>
                                                            <?php else: ?>
                                                                 <tr>
                                                                      <td colspan="2" class="text-center text-muted">No cashier data available.</td>
                                                                 </tr>
                                                            <?php endif; ?>
                                                       </tbody>
                                                  </table>
                                             </div>
                                        </div>
                                   </div>
                              </div>
                         </div>
                         <?php endif; ?>
                    </div>
               </div>

               <?php include('includes/footer.php'); ?>
          </div>
     </div>

     <?php include('includes/themecustomizer.php'); ?>

     <?php if ($this->session->userdata('level') === 'Admin'): ?>
     <div class="modal fade" id="paymentModal" tabindex="-1" role="dialog" aria-hidden="true">
          <div class="modal-dialog modal-lg" role="document">
               <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                         <h5 class="modal-title mb-0">New Payment</h5>
                         <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                              <span aria-hidden="true">&times;</span>
                         </button>
                    </div>
                    <div class="modal-body">
                         <form class="needs-validation" method="post" action="<?= base_url(); ?>Page/addPayment" novalidate>
                              <div class="form-group">
                                   <label for="modal-customer">Payor</label>
                                   <input type="text" class="form-control" id="modal-customer" name="Customer" placeholder="Name of the customer or company" required>
                              </div>
                              <div class="form-group">
                                   <label for="modal-description">Transaction / Description</label>
                                   <input type="text" class="form-control" id="modal-description" name="TransDescription" required>
                              </div>
                              <div class="form-row">
                                   <div class="form-group col-md-4">
                                        <label for="modal-amount">Amount Paid</label>
                                        <input type="number" class="form-control" id="modal-amount" name="AmountPaid" min="0" step="0.01" required>
                                   </div>
                                   <div class="form-group col-md-4">
                                        <label for="modal-orno">O.R. No.</label>
                                        <input type="text" class="form-control" id="modal-orno" name="ORNo">
                                   </div>
                                   <div class="form-group col-md-4">
                                        <label for="modal-invoice">Invoice No.</label>
                                        <input type="text" class="form-control" id="modal-invoice" name="InvoiceNo" placeholder="Optional">
                                   </div>
                              </div>
                              <div class="text-right">
                                   <button type="submit" name="submit" class="btn btn-primary">Accept Payment</button>
                                   <button type="reset" class="btn btn-light ml-2">Reset</button>
                              </div>
                         </form>
                    </div>
               </div>
          </div>
     </div>
     <?php endif; ?>

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

     <script>
          (function($) {
               'use strict';

               $(function() {
                    var $tableContainer = $('.payment-range-data-page .data-table-container');
                    var $rangeTable = $('#range-table');

                    $rangeTable.DataTable({
                         responsive: true,
                         autoWidth: false,
                         order: [[0, 'desc']],
                         language: {
                              emptyTable: 'No payments found for the selected range.'
                         },
                         columnDefs: [{
                              targets: 0,
                              className: 'payment-date'
                         }, {
                              targets: 1,
                              className: 'text-right'
                         }, {
                              targets: -1,
                              orderable: false,
                              searchable: false
                         }],
                         initComplete: function() {
                              $rangeTable.removeClass('table-init-hidden').addClass('table-init-ready');
                              $tableContainer.removeClass('loading').addClass('ready');
                         }
                    });
               });
          })(jQuery);
     </script>

</body>

</html>
