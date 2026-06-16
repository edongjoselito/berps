<!DOCTYPE html>
<html lang="en">

<?php include('includes/head.php'); ?>

<body>

     <div id="wrapper">

          <?php include('includes/top-nav-bar.php'); ?>
          <?php include('includes/sidebar.php'); ?>

          <div class="content-page">
               <div class="content">
                    <div class="container-fluid void-payment-report-page">

                         <?php if ($this->session->flashdata('success')): ?>
                              <div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
                                   <?= htmlspecialchars($this->session->flashdata('success'), ENT_QUOTES, 'UTF-8'); ?>
                                   <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                   </button>
                              </div>
                         <?php endif; ?>

                         <?php if ($this->session->flashdata('danger')): ?>
                              <div class="alert alert-danger alert-dismissible fade show mt-3" role="alert">
                                   <?= htmlspecialchars($this->session->flashdata('danger'), ENT_QUOTES, 'UTF-8'); ?>
                                   <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                   </button>
                              </div>
                         <?php endif; ?>

                         <style>
                              .void-payment-report-page {
                                   --bg: #f5f7fb;
                                   --surface: rgba(255, 255, 255, 0.94);
                                   --line: #e7ecf3;
                                   --line-strong: #d7e0ec;
                                   --text: #122033;
                                   --text-soft: #5e7188;
                                   --text-faint: #8ea0b5;
                                   --primary: #2563eb;
                                   --primary-2: #1d4ed8;
                                   --primary-soft: #eaf2ff;
                                   --success: #059669;
                                   --success-soft: #ecfdf5;
                                   --warning: #d97706;
                                   --warning-soft: #fff7ed;
                                   --danger: #dc2626;
                                   --danger-soft: #fef2f2;
                                   --shadow: 0 14px 40px rgba(15, 23, 42, 0.08);
                                   --shadow-soft: 0 8px 24px rgba(15, 23, 42, 0.05);
                                   --radius-xl: 22px;
                                   --radius-lg: 16px;
                                   --radius-md: 12px;
                                   --radius-sm: 10px;
                                   --font-body: 'Inter', 'Poppins', 'Segoe UI', Arial, sans-serif;
                                   --font-head: 'Inter', 'Montserrat', 'Segoe UI', Arial, sans-serif;
                                   --font-mono: 'SFMono-Regular', Consolas, 'Liberation Mono', monospace;
                                   font-family: var(--font-body);
                                   background:
                                        radial-gradient(circle at top left, rgba(37, 99, 235, 0.08), transparent 28%),
                                        radial-gradient(circle at top right, rgba(220, 38, 38, 0.06), transparent 24%),
                                        linear-gradient(180deg, #f8fbff 0%, #f4f7fb 100%);
                                   min-height: 100vh;
                                   padding-bottom: 24px;
                              }

                              .void-payment-report-page * {
                                   box-sizing: border-box;
                              }

                              .void-payment-report-page .vpr-header {
                                   margin: 24px 0 18px;
                                   display: flex;
                                   justify-content: space-between;
                                   align-items: flex-end;
                                   gap: 16px;
                                   flex-wrap: wrap;
                              }

                              .void-payment-report-page .vpr-eyebrow {
                                   display: inline-flex;
                                   align-items: center;
                                   gap: 8px;
                                   padding: 7px 12px;
                                   border-radius: 999px;
                                   background: rgba(220, 38, 38, 0.08);
                                   color: #991b1b;
                                   font-size: 0.74rem;
                                   font-weight: 700;
                                   letter-spacing: 0.08em;
                                   text-transform: uppercase;
                                   margin-bottom: 12px;
                              }

                              .void-payment-report-page .vpr-eyebrow::before {
                                   content: '';
                                   width: 8px;
                                   height: 8px;
                                   border-radius: 50%;
                                   background: linear-gradient(135deg, #dc2626, #ef4444);
                                   box-shadow: 0 0 0 4px rgba(220, 38, 38, 0.10);
                              }

                              .void-payment-report-page .vpr-title {
                                   margin: 0;
                                   font-family: var(--font-head);
                                   font-size: 2rem;
                                   font-weight: 800;
                                   letter-spacing: -0.04em;
                                   color: var(--text);
                                   line-height: 1.1;
                              }

                              .void-payment-report-page .vpr-subtitle {
                                   margin-top: 8px;
                                   color: var(--text-soft);
                                   font-size: 0.93rem;
                                   font-weight: 500;
                              }

                              .void-payment-report-page .vpr-card {
                                   background: var(--surface);
                                   backdrop-filter: blur(12px);
                                   border: 1px solid rgba(255, 255, 255, 0.72);
                                   border-radius: 24px;
                                   box-shadow: var(--shadow);
                                   overflow: hidden;
                              }

                              .void-payment-report-page .vpr-card-header {
                                   padding: 20px 24px;
                                   border-bottom: 1px solid var(--line);
                                   display: flex;
                                   align-items: center;
                                   justify-content: space-between;
                                   flex-wrap: wrap;
                                   gap: 14px;
                                   background: linear-gradient(180deg, rgba(255, 255, 255, 0.94), rgba(249, 251, 255, 0.94));
                              }

                              .void-payment-report-page .vpr-card-title {
                                   margin: 0;
                                   color: var(--text);
                                   font-size: 1.05rem;
                                   font-weight: 800;
                                   letter-spacing: -0.02em;
                              }

                              .void-payment-report-page .vpr-card-body {
                                   padding: 22px 24px 24px;
                              }

                              .void-payment-report-page .table-responsive {
                                   border-radius: 18px;
                              }

                              .void-payment-report-page #void-payment-table {
                                   border-collapse: separate !important;
                                   border-spacing: 0 10px !important;
                                   margin-top: -10px !important;
                              }

                              .void-payment-report-page #void-payment-table thead th {
                                   background: transparent !important;
                                   color: var(--text-faint) !important;
                                   border: none !important;
                                   font-size: 0.74rem;
                                   font-weight: 700;
                                   text-transform: uppercase;
                                   letter-spacing: 0.08em;
                                   padding: 6px 14px 10px !important;
                                   white-space: nowrap;
                              }

                              .void-payment-report-page #void-payment-table tbody tr {
                                   box-shadow: var(--shadow-soft);
                              }

                              .void-payment-report-page #void-payment-table tbody td {
                                   background: #fff !important;
                                   border-top: 1px solid var(--line) !important;
                                   border-bottom: 1px solid var(--line) !important;
                                   border-left: none !important;
                                   border-right: none !important;
                                   padding: 16px 14px !important;
                                   vertical-align: middle;
                                   color: var(--text);
                                   font-size: 0.9rem;
                              }

                              .void-payment-report-page #void-payment-table tbody td:first-child {
                                   border-left: 1px solid var(--line) !important;
                                   border-top-left-radius: 16px;
                                   border-bottom-left-radius: 16px;
                              }

                              .void-payment-report-page #void-payment-table tbody td:last-child {
                                   border-right: 1px solid var(--line) !important;
                                   border-top-right-radius: 16px;
                                   border-bottom-right-radius: 16px;
                              }

                              .void-payment-report-page #void-payment-table tbody tr:hover td {
                                   background: #fef2f2 !important;
                              }

                              .void-payment-report-page .pay-id {
                                   font-family: var(--font-mono);
                                   font-weight: 700;
                                   color: var(--danger);
                              }

                              .void-payment-report-page .void-badge {
                                   display: inline-flex;
                                   align-items: center;
                                   gap: 6px;
                                   padding: 4px 10px;
                                   border-radius: 20px;
                                   background: var(--danger-soft);
                                   color: var(--danger);
                                   font-size: 0.75rem;
                                   font-weight: 700;
                              }

                              .void-payment-report-page .void-badge::before {
                                   content: '';
                                   width: 6px;
                                   height: 6px;
                                   border-radius: 50%;
                                   background: var(--danger);
                              }

                              .void-payment-report-page .void-reason {
                                   max-width: 200px;
                                   overflow: hidden;
                                   text-overflow: ellipsis;
                                   white-space: nowrap;
                              }

                              .void-payment-report-page .num-cell {
                                   font-family: var(--font-mono);
                                   font-weight: 600;
                              }

                              .void-payment-report-page .summary-bar {
                                   display: flex;
                                   gap: 24px;
                                   margin-top: 20px;
                                   padding-top: 20px;
                                   border-top: 1px solid var(--line);
                              }

                              .void-payment-report-page .summary-item {
                                   font-size: 0.9rem;
                              }

                              .void-payment-report-page .summary-label {
                                   color: var(--text-faint);
                                   font-size: 0.75rem;
                                   font-weight: 600;
                                   text-transform: uppercase;
                                   letter-spacing: 0.05em;
                              }

                              .void-payment-report-page .summary-value {
                                   color: var(--text);
                                   font-weight: 700;
                                   font-size: 1.1rem;
                              }

                              .void-payment-report-page .empty-state {
                                   text-align: center;
                                   padding: 60px 20px;
                              }

                              .void-payment-report-page .empty-state-icon {
                                   width: 80px;
                                   height: 80px;
                                   border-radius: 24px;
                                   background: var(--danger-soft);
                                   color: var(--danger);
                                   display: flex;
                                   align-items: center;
                                   justify-content: center;
                                   margin: 0 auto 20px;
                                   font-size: 2rem;
                              }

                              .void-payment-report-page .empty-state h4 {
                                   color: var(--text);
                                   font-weight: 700;
                                   margin-bottom: 8px;
                              }

                              .void-payment-report-page .empty-state p {
                                   color: var(--text-soft);
                                   font-size: 0.9rem;
                              }

                              @media (max-width: 767px) {
                                   .void-payment-report-page .vpr-card-header,
                                   .void-payment-report-page .vpr-card-body {
                                        padding-left: 16px;
                                        padding-right: 16px;
                                   }

                                   .void-payment-report-page #void-payment-table tbody td {
                                        padding: 13px 12px !important;
                                   }

                                   .void-payment-report-page .summary-bar {
                                        flex-direction: column;
                                        gap: 12px;
                                   }
                              }
                         </style>

                         <div class="vpr-header">
                              <div class="vpr-header-left">
                                   <div class="vpr-eyebrow">Collections</div>
                                   <h4 class="vpr-title">Voided Payments Report</h4>
                                   <div class="vpr-subtitle">List of all cancelled and voided payment records</div>
                              </div>
                         </div>

                         <div class="vpr-card">
                              <div class="vpr-card-header">
                                   <h5 class="vpr-card-title">Voided Payment Records</h5>
                              </div>
                              <div class="vpr-card-body">
                                   <div class="table-responsive">
                                        <table id="void-payment-table" class="table table-hover mb-0">
                                             <thead>
                                                  <tr>
                                                       <th>OR No.</th>
                                                       <th>Customer</th>
                                                       <th>Invoice No.</th>
                                                       <th>Date Voided</th>
                                                       <th>Voided By</th>
                                                       <th>Reason</th>
                                                       <th class="text-right">Amount</th>
                                                       <th>Status</th>
                                                  </tr>
                                             </thead>
                                             <tbody>
                                                  <?php 
                                                  $totalVoided = 0;
                                                  $count = 0;
                                                  if (!empty($data)): 
                                                       foreach ($data as $row): 
                                                            $totalVoided += (float) ($row->AmountPaid ?? 0);
                                                            $count++;
                                                            $voidDate = !empty($row->voidDate) ? date('M d, Y h:i A', strtotime($row->voidDate)) : 'N/A';
                                                  ?>
                                                            <tr>
                                                                 <td class="pay-id"><?= htmlspecialchars($row->ORNo ?? 'N/A'); ?></td>
                                                                 <td><?= htmlspecialchars($row->Customer ?? 'N/A'); ?></td>
                                                                 <td><?= htmlspecialchars($row->InvoiceNo ?? 'N/A'); ?></td>
                                                                 <td><?= $voidDate; ?></td>
                                                                 <td><?= htmlspecialchars($row->voidBy ?? 'System'); ?></td>
                                                                 <td class="void-reason" title="<?= htmlspecialchars($row->voidReason ?? ''); ?>">
                                                                      <?= htmlspecialchars($row->voidReason ?? 'No reason'); ?>
                                                                 </td>
                                                                 <td class="text-right num-cell"><?= number_format($row->AmountPaid ?? 0, 2); ?></td>
                                                                 <td>
                                                                      <span class="void-badge">Voided</span>
                                                                 </td>
                                                            </tr>
                                                  <?php 
                                                       endforeach; 
                                                  endif; 
                                                  ?>
                                             </tbody>
                                        </table>
                                   </div>

                                   <?php if (empty($data)): ?>
                                        <div class="empty-state">
                                             <div class="empty-state-icon">
                                                  <i class="mdi mdi-check-circle"></i>
                                             </div>
                                             <h4>No Voided Payments</h4>
                                             <p>There are no voided payments in the system. All payment records are valid.</p>
                                        </div>
                                   <?php else: ?>
                                        <div class="summary-bar">
                                             <div class="summary-item">
                                                  <div class="summary-label">Total Voided Payments</div>
                                                  <div class="summary-value"><?= $count; ?></div>
                                             </div>
                                             <div class="summary-item">
                                                  <div class="summary-label">Total Voided Amount</div>
                                                  <div class="summary-value" style="color: var(--danger);">PHP <?= number_format($totalVoided, 2); ?></div>
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
     <script src="<?= base_url(); ?>assets/js/app.min.js"></script>
     <script src="<?= base_url(); ?>assets/libs/datatables/jquery.dataTables.min.js"></script>
     <script src="<?= base_url(); ?>assets/libs/datatables/dataTables.bootstrap4.min.js"></script>
     <script>
          (function($) {
               'use strict';
               $(function() {
                    if ($.fn.DataTable) {
                         $('#void-payment-table').DataTable({
                              responsive: true,
                              autoWidth: false,
                              order: [[3, 'desc']],
                              language: {
                                   emptyTable: 'No voided payments found.'
                              },
                              columnDefs: [{
                                   targets: 6,
                                   className: 'text-right'
                              }]
                         });
                    }
               });
          })(jQuery);
     </script>

</body>

</html>
