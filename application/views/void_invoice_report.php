<!DOCTYPE html>
<html lang="en">

<?php include('includes/head.php'); ?>

<body>

     <div id="wrapper">

          <?php include('includes/top-nav-bar.php'); ?>
          <?php include('includes/sidebar.php'); ?>

          <div class="content-page">
               <div class="content">
                    <div class="container-fluid void-invoice-report-page">

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
                              .void-invoice-report-page {
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
                                   --font-body: var(--font-primary);
                                   --font-head: var(--font-primary);
                                   --font-mono: var(--font-primary);
                                   font-family: var(--font-primary, Montserrat, Segoe UI, Arial, sans-serif);
                                   background:
                                        radial-gradient(circle at top left, rgba(37, 99, 235, 0.08), transparent 28%),
                                        radial-gradient(circle at top right, rgba(220, 38, 38, 0.06), transparent 24%),
                                        linear-gradient(180deg, #f8fbff 0%, #f4f7fb 100%);
                                   min-height: 100vh;
                                   padding-bottom: 24px;
                              }

                              .void-invoice-report-page * {
                                   box-sizing: border-box;
                              }

                              .void-invoice-report-page .vir-header {
                                   margin: 24px 0 18px;
                                   display: flex;
                                   justify-content: space-between;
                                   align-items: flex-end;
                                   gap: 16px;
                                   flex-wrap: wrap;
                              }

                              .void-invoice-report-page .vir-eyebrow {
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

                              .void-invoice-report-page .vir-eyebrow::before {
                                   content: '';
                                   width: 8px;
                                   height: 8px;
                                   border-radius: 50%;
                                   background: linear-gradient(135deg, #dc2626, #ef4444);
                                   box-shadow: 0 0 0 4px rgba(220, 38, 38, 0.10);
                              }

                              .void-invoice-report-page .vir-title {
                                   margin: 0;
                                   font-family: var(--font-primary, Montserrat, Segoe UI, Arial, sans-serif);
                                   font-size: 2rem;
                                   font-weight: 800;
                                   letter-spacing: -0.04em;
                                   color: var(--text);
                                   line-height: 1.1;
                              }

                              .void-invoice-report-page .vir-subtitle {
                                   margin-top: 8px;
                                   color: var(--text-soft);
                                   font-size: 0.93rem;
                                   font-weight: 500;
                              }

                              .void-invoice-report-page .vir-card {
                                   background: var(--surface);
                                   backdrop-filter: blur(12px);
                                   border: 1px solid rgba(255, 255, 255, 0.72);
                                   border-radius: 24px;
                                   box-shadow: var(--shadow);
                                   overflow: hidden;
                              }

                              .void-invoice-report-page .vir-card-header {
                                   padding: 20px 24px;
                                   border-bottom: 1px solid var(--line);
                                   display: flex;
                                   align-items: center;
                                   justify-content: space-between;
                                   flex-wrap: wrap;
                                   gap: 14px;
                                   background: linear-gradient(180deg, rgba(255, 255, 255, 0.94), rgba(249, 251, 255, 0.94));
                              }

                              .void-invoice-report-page .vir-card-title {
                                   margin: 0;
                                   color: var(--text);
                                   font-size: 1.05rem;
                                   font-weight: 800;
                                   letter-spacing: -0.02em;
                              }

                              .void-invoice-report-page .vir-card-body {
                                   padding: 22px 24px 24px;
                              }

                              .void-invoice-report-page .table-responsive {
                                   border-radius: 18px;
                              }

                              .void-invoice-report-page #void-invoice-table {
                                   border-collapse: separate !important;
                                   border-spacing: 0 10px !important;
                                   margin-top: -10px !important;
                              }

                              .void-invoice-report-page #void-invoice-table thead th {
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

                              .void-invoice-report-page #void-invoice-table tbody tr {
                                   box-shadow: var(--shadow-soft);
                              }

                              .void-invoice-report-page #void-invoice-table tbody td {
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

                              .void-invoice-report-page #void-invoice-table tbody td:first-child {
                                   border-left: 1px solid var(--line) !important;
                                   border-top-left-radius: 16px;
                                   border-bottom-left-radius: 16px;
                              }

                              .void-invoice-report-page #void-invoice-table tbody td:last-child {
                                   border-right: 1px solid var(--line) !important;
                                   border-top-right-radius: 16px;
                                   border-bottom-right-radius: 16px;
                              }

                              .void-invoice-report-page #void-invoice-table tbody tr:hover td {
                                   background: #fef2f2 !important;
                              }

                              .void-invoice-report-page .inv-no {
                                   font-family: var(--font-primary, Montserrat, Segoe UI, Arial, sans-serif);
                                   font-weight: 700;
                                   color: var(--danger);
                              }

                              .void-invoice-report-page .void-badge {
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

                              .void-invoice-report-page .void-badge::before {
                                   content: '';
                                   width: 6px;
                                   height: 6px;
                                   border-radius: 50%;
                                   background: var(--danger);
                              }

                              .void-invoice-report-page .void-info {
                                   font-size: 0.8rem;
                                   color: var(--text-soft);
                              }

                              .void-invoice-report-page .void-reason {
                                   max-width: 200px;
                                   overflow: hidden;
                                   text-overflow: ellipsis;
                                   white-space: nowrap;
                              }

                              .void-invoice-report-page .num-cell {
                                   font-family: var(--font-primary, Montserrat, Segoe UI, Arial, sans-serif);
                                   font-weight: 600;
                              }

                              .void-invoice-report-page .summary-bar {
                                   display: flex;
                                   gap: 24px;
                                   margin-top: 20px;
                                   padding-top: 20px;
                                   border-top: 1px solid var(--line);
                              }

                              .void-invoice-report-page .summary-item {
                                   font-size: 0.9rem;
                              }

                              .void-invoice-report-page .summary-label {
                                   color: var(--text-faint);
                                   font-size: 0.75rem;
                                   font-weight: 600;
                                   text-transform: uppercase;
                                   letter-spacing: 0.05em;
                              }

                              .void-invoice-report-page .summary-value {
                                   color: var(--text);
                                   font-weight: 700;
                                   font-size: 1.1rem;
                              }

                              .void-invoice-report-page .empty-state {
                                   text-align: center;
                                   padding: 60px 20px;
                              }

                              .void-invoice-report-page .empty-state-icon {
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

                              .void-invoice-report-page .empty-state h4 {
                                   color: var(--text);
                                   font-weight: 700;
                                   margin-bottom: 8px;
                              }

                              .void-invoice-report-page .empty-state p {
                                   color: var(--text-soft);
                                   font-size: 0.9rem;
                              }

                              @media (max-width: 767px) {
                                   .void-invoice-report-page .vir-card-header,
                                   .void-invoice-report-page .vir-card-body {
                                        padding-left: 16px;
                                        padding-right: 16px;
                                   }

                                   .void-invoice-report-page #void-invoice-table tbody td {
                                        padding: 13px 12px !important;
                                   }

                                   .void-invoice-report-page .summary-bar {
                                        flex-direction: column;
                                        gap: 12px;
                                   }
                              }
                         </style>

                         <div class="vir-header">
                              <div class="vir-header-left">
                                   <div class="vir-eyebrow">Invoice Management</div>
                                   <h4 class="vir-title">Voided Invoices Report</h4>
                                   <div class="vir-subtitle">List of all cancelled and voided invoices</div>
                              </div>
                         </div>

                         <div class="vir-card">
                              <div class="vir-card-header">
                                   <h5 class="vir-card-title">Voided Invoice Records</h5>
                              </div>
                              <div class="vir-card-body">
                                   <div class="table-responsive">
                                        <table id="void-invoice-table" class="table table-hover mb-0">
                                             <thead>
                                                  <tr>
                                                       <th>Invoice No.</th>
                                                       <th>Customer</th>
                                                       <th>Date Voided</th>
                                                       <th>Voided By</th>
                                                       <th>Reason</th>
                                                       <th class="text-right">Original Amount</th>
                                                       <th>Status</th>
                                                  </tr>
                                             </thead>
                                             <tbody>
                                                  <?php 
                                                  $totalVoided = 0;
                                                  $count = 0;
                                                  if (!empty($data)): 
                                                       foreach ($data as $row): 
                                                            $totalVoided += (float) ($row->TotalDue ?? 0);
                                                            $count++;
                                                            $voidDate = !empty($row->voidDate) ? date('M d, Y h:i A', strtotime($row->voidDate)) : 'N/A';
                                                  ?>
                                                            <tr>
                                                                 <td class="inv-no">#<?= htmlspecialchars($row->InvoiceNo ?? 'N/A'); ?></td>
                                                                 <td><?= htmlspecialchars($row->Customer ?? 'N/A'); ?></td>
                                                                 <td><?= $voidDate; ?></td>
                                                                 <td><?= htmlspecialchars($row->voidBy ?? 'System'); ?></td>
                                                                 <td class="void-reason" title="<?= htmlspecialchars($row->voidReason ?? ''); ?>">
                                                                      <?= htmlspecialchars($row->voidReason ?? 'No reason'); ?>
                                                                 </td>
                                                                 <td class="text-right num-cell"><?= number_format($row->TotalDue ?? 0, 2); ?></td>
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
                                             <h4>No Voided Invoices</h4>
                                             <p>There are no voided invoices in the system. All invoices are active or paid.</p>
                                        </div>
                                   <?php else: ?>
                                        <div class="summary-bar">
                                             <div class="summary-item">
                                                  <div class="summary-label">Total Voided Invoices</div>
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
                         $('#void-invoice-table').DataTable({
                              responsive: true,
                              autoWidth: false,
                              order: [[2, 'desc']],
                              language: {
                                   emptyTable: 'No voided invoices found.'
                              },
                              columnDefs: [{
                                   targets: 5,
                                   className: 'text-right'
                              }]
                         });
                    }
               });
          })(jQuery);
     </script>

</body>

</html>
