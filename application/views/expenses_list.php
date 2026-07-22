<!DOCTYPE html>
<html lang="en">

<?php include('includes/head.php'); ?>

<?php
$expenseCategories = isset($expenseCategories) && is_array($expenseCategories) ? $expenseCategories : array();
?>

<body>

     <div id="wrapper">

          <?php include('includes/top-nav-bar.php'); ?>
          <?php include('includes/sidebar.php'); ?>

          <div class="content-page">
               <div class="content">
                    <div class="container-fluid expenses-list-page berps-page">

                         <?php if ($this->session->flashdata('success')): ?>
                              <div class="alert alert-success alert-dismissible fade show mt-3" role="alert" style="border: none; border-radius: 16px; box-shadow: var(--shadow-soft);">
                                   <?= htmlspecialchars((string) $this->session->flashdata('success'), ENT_QUOTES, 'UTF-8'); ?>
                                   <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                              </div>
                         <?php endif; ?>

                         <?php if ($this->session->flashdata('danger')): ?>
                              <div class="alert alert-danger alert-dismissible fade show mt-3" role="alert" style="border: none; border-radius: 16px; box-shadow: var(--shadow-soft);">
                                   <?= htmlspecialchars((string) $this->session->flashdata('danger'), ENT_QUOTES, 'UTF-8'); ?>
                                   <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                              </div>
                         <?php endif; ?>

                         <?php $totalExpenses = !empty($data1) && isset($data1[0]->Total) ? (float) $data1[0]->Total : 0; ?>

                         <!-- Page header -->
                         <header class="berps-page-header">
                              <div class="berps-page-header__content">
                                   <span class="berps-page-header__eyebrow">Finance Overview</span>
                                   <h1 class="berps-page-title">Expenses</h1>
                                   <p class="berps-page-subtitle">Monitor and manage outgoing costs efficiently.</p>
                              </div>
                              <div class="berps-page-header__actions">
                                   <a href="<?= base_url(); ?>Page/downloadExpenseTemplate" class="btn btn-outline-secondary">
                                        <i class="mdi mdi-download mr-1" aria-hidden="true"></i>Download Template
                                   </a>
                                   <button type="button" class="btn btn-outline-secondary" data-toggle="modal" data-target="#bulkUploadModal">
                                        <i class="mdi mdi-upload mr-1" aria-hidden="true"></i>Bulk Upload
                                   </button>
                                   <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#expenseModal">
                                        <i class="mdi mdi-cash-minus mr-1" aria-hidden="true"></i>Add New Expense
                                   </button>
                              </div>
                         </header>

                         <!-- Per Year Expense Statistics -->
                         <div class="row" style="margin-bottom: 24px;">
                              <div class="col-lg-3 col-md-6">
                                   <div class="theme-card dashboard-card" style="height: 100%; cursor: pointer; transition: all 0.3s ease;" data-type="current-year" data-year="<?= date('Y'); ?>">
                                        <div class="theme-card-body" style="text-align: center; padding: 20px;">
                                             <div style="width: 60px; height: 60px; margin: 0 auto 16px; background: linear-gradient(135deg, #e11d48, #be123c); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                                                  <i class="mdi mdi-calendar-today" style="color: #fff; font-size: 24px;"></i>
                                             </div>
                                             <h6 style="color: var(--text-soft); font-size: 0.85rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 8px;">Current Year</h6>
                                             <h3 style="color: var(--danger); font-weight: 800; font-size: 1.8rem; margin-bottom: 4px;">
                                                  <?= number_format($currentYearExpenses, 2); ?>
                                             </h3>
                                             <div style="font-size: 0.8rem; color: var(--text-faint);">
                                                  <?= date('Y'); ?>
                                             </div>
                                        </div>
                                   </div>
                              </div>
                              <div class="col-lg-3 col-md-6">
                                   <div class="theme-card dashboard-card" style="height: 100%; cursor: pointer; transition: all 0.3s ease;" data-type="previous-year" data-year="<?= date('Y') - 1; ?>">
                                        <div class="theme-card-body" style="text-align: center; padding: 20px;">
                                             <div style="width: 60px; height: 60px; margin: 0 auto 16px; background: linear-gradient(135deg, #059669, #047857); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                                                  <i class="mdi mdi-trending-up" style="color: #fff; font-size: 24px;"></i>
                                             </div>
                                             <h6 style="color: var(--text-soft); font-size: 0.85rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 8px;">Previous Year</h6>
                                             <h3 style="color: var(--success); font-weight: 800; font-size: 1.8rem; margin-bottom: 4px;">
                                                  <?= number_format($previousYearExpenses, 2); ?>
                                             </h3>
                                             <div style="font-size: 0.8rem; color: var(--text-faint);">
                                                  <?= date('Y') - 1; ?>
                                             </div>
                                        </div>
                                   </div>
                              </div>
                              <div class="col-lg-3 col-md-6">
                                   <div class="theme-card dashboard-card" style="height: 100%; cursor: pointer; transition: all 0.3s ease;" data-type="yearly-average" data-year="<?= date('Y'); ?>">
                                        <div class="theme-card-body" style="text-align: center; padding: 20px;">
                                             <div style="width: 60px; height: 60px; margin: 0 auto 16px; background: linear-gradient(135deg, #2563eb, #1d4ed8); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                                                  <i class="mdi mdi-chart-line" style="color: #fff; font-size: 24px;"></i>
                                             </div>
                                             <h6 style="color: var(--text-soft); font-size: 0.85rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 8px;">Year Average</h6>
                                             <h3 style="color: var(--primary); font-weight: 800; font-size: 1.8rem; margin-bottom: 4px;">
                                                  <?= number_format($yearlyAverage, 2); ?>
                                             </h3>
                                             <div style="font-size: 0.8rem; color: var(--text-faint);">
                                                  Per Month
                                             </div>
                                        </div>
                                   </div>
                              </div>
                              <div class="col-lg-3 col-md-6">
                                   <div class="theme-card dashboard-card" style="height: 100%; cursor: pointer; transition: all 0.3s ease;" data-type="yoy-change" data-year="<?= date('Y'); ?>">
                                        <div class="theme-card-body" style="text-align: center; padding: 20px;">
                                             <div style="width: 60px; height: 60px; margin: 0 auto 16px; background: linear-gradient(135deg, #d97706, #b45309); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                                                  <i class="mdi mdi-percent" style="color: #fff; font-size: 24px;"></i>
                                             </div>
                                             <h6 style="color: var(--text-soft); font-size: 0.85rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 8px;">YoY Change</h6>
                                             <h3 style="color: var(--warning); font-weight: 800; font-size: 1.8rem; margin-bottom: 4px;">
                                                  <?= $yearOverYearChange >= 0 ? '+' : ''; ?><?= number_format($yearOverYearChange, 1); ?>%
                                             </h3>
                                             <div style="font-size: 0.8rem; color: var(--text-faint);">
                                                  <?= $yearOverYearChange >= 0 ? 'Increase' : 'Decrease'; ?>
                                             </div>
                                        </div>
                                   </div>
                              </div>
                         </div>

                         <div class="theme-card">
                              <div class="theme-card-head">
                                   <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
                                        <div>
                                             <h5 class="theme-card-title">Expenses List</h5>
                                             <div class="theme-card-subtitle">Track all recorded expenses with details.</div>
                                        </div>
                                        <form method="get" action="<?= base_url(); ?>Page/expensesList" class="expense-filters" style="display: flex; gap: 10px; flex-wrap: wrap; align-items: center;">
                                             <div style="display: flex; gap: 8px; align-items: center;">
                                                  <input type="date" name="from" class="form-control" value="<?= htmlspecialchars($fromDate); ?>" style="width: 150px; padding: 8px 12px; font-size: 0.88rem; border: 1px solid var(--line-strong); border-radius: 10px;">
                                                  <span style="color: var(--text-soft); font-size: 0.9rem;">to</span>
                                                  <input type="date" name="to" class="form-control" value="<?= htmlspecialchars($toDate); ?>" style="width: 150px; padding: 8px 12px; font-size: 0.88rem; border: 1px solid var(--line-strong); border-radius: 10px;">
                                                  <button type="submit" class="btn btn-primary" style="padding: 8px 16px; font-size: 0.85rem; border-radius: 10px;">
                                                        <i class="mdi mdi-filter"></i> Filter
                                                  </button>
                                                  <?php if ($isFiltered): ?>
                                                       <a href="<?= base_url(); ?>Page/expensesList" class="btn btn-light" style="padding: 8px 16px; font-size: 0.85rem; border-radius: 10px; border: 1px solid var(--line-strong);">
                                                            <i class="mdi mdi-refresh"></i> Reset
                                                       </a>
                                                  <?php endif; ?>
                                                  <a href="<?= base_url(); ?>Page/expensesReport?from=<?= htmlspecialchars($fromDate); ?>&to=<?= htmlspecialchars($toDate); ?>" class="btn btn-success" style="padding: 8px 16px; font-size: 0.85rem; border-radius: 10px;" target="_blank">
                                                       <i class="mdi mdi-printer"></i> Print Report
                                                  </a>
                                             </div>
                                        </form>
                                   </div>
                              </div>
                              <div class="theme-card-body">

                                   <div class="table-responsive data-table-container loading">
                                        <table id="expenses-table" class="table table-hover table-centered mb-0 table-init-hidden">
                                             <thead>
                                                  <tr>
                                                       <th>Date</th>
                                                       <th class="text-right">Amount</th>
                                                       <th>Description</th>
                                                       <th>Category</th>
                                                       <th>Responsible</th>
                                                       <th>Attachment</th>
                                                       <th class="text-center">Action</th>
                                                  </tr>
                                             </thead>
                                             <tbody>
                                                  <?php if (!empty($data)): ?>
                                                       <?php foreach ($data as $row): ?>
                                                            <tr>
                                                                 <td><?= $row->ExpenseDate; ?></td>
                                                                 <td class="text-right"><?= number_format($row->Amount, 2); ?></td>
                                                                 <td><?= $row->Description; ?></td>
                                                                 <td><?= $row->Category; ?></td>
                                                                 <td><?= $row->Responsible; ?></td>
                                                                 <td>
                                                                      <?php if (!empty($row->attachment)): ?>
                                                                           <?php 
                                                                           $fileExtension = strtolower(pathinfo($row->attachment, PATHINFO_EXTENSION));
                                                                           $icon = 'mdi-file-document-outline';
                                                                           $color = '#617489';
                                                                           
                                                                           if (in_array($fileExtension, ['jpg', 'jpeg', 'png'])) {
                                                                               $icon = 'mdi-image-outline';
                                                                               $color = '#059669';
                                                                           } elseif ($fileExtension === 'pdf') {
                                                                               $icon = 'mdi-file-pdf-outline';
                                                                               $color = '#e11d48';
                                                                           }
                                                                           ?>
                                                                           <a href="<?= base_url(); ?><?= htmlspecialchars($row->attachment); ?>" 
                                                                              target="_blank" 
                                                                              class="attachment-link" 
                                                                              title="Download attachment"
                                                                              style="color: <?= $color; ?>; text-decoration: none; display: inline-flex; align-items: center; gap: 4px;">
                                                                              <i class="mdi <?= $icon; ?>"></i>
                                                                              <span style="font-size: 0.8rem;"><?= strtoupper($fileExtension); ?></span>
                                                                           </a>
                                                                      <?php else: ?>
                                                                           <span style="color: #8ea0b5; font-size: 0.8rem;">No attachment</span>
                                                                      <?php endif; ?>
                                                                 </td>
                                                                 <td class="text-center">
                                                                      <?php if ($this->session->userdata('level') === 'Admin'): ?>
                                                                           <div class="expense-actions">
                                                                                <a class="action-icon print" href="<?= base_url(); ?>Page/printExpense?id=<?= $row->expensesid; ?>" target="_blank" data-label="Print" title="Print">
                                                                                     <i class="mdi mdi-printer"></i>
                                                                                     <span class="sr-only">Print</span>
                                                                                </a>
                                                                                <a class="action-icon edit" href="<?= base_url(); ?>Page/updateExpenses?id=<?= $row->expensesid; ?>" data-label="Edit" title="Edit">
                                                                                     <i class="mdi mdi-square-edit-outline"></i>
                                                                                     <span class="sr-only">Edit</span>
                                                                                </a>
                                                                                <a class="action-icon delete" href="<?= base_url(); ?>Page/deleteExpense?id=<?= $row->expensesid; ?>" onclick="return confirm('Are you sure you want to delete this record?');" data-label="Delete" title="Delete">
                                                                                     <i class="mdi mdi-trash-can-outline"></i>
                                                                                     <span class="sr-only">Delete</span>
                                                                                </a>
                                                                           </div>
                                                                      <?php elseif (in_array($this->session->userdata('level'), ['Staff', 'Encoder'], true)): ?>
                                                                           <div class="expense-actions">
                                                                                <a class="action-icon print" href="<?= base_url(); ?>Page/printExpense?id=<?= $row->expensesid; ?>" target="_blank" data-label="Print" title="Print">
                                                                                     <i class="mdi mdi-printer"></i>
                                                                                     <span class="sr-only">Print</span>
                                                                                </a>
                                                                           </div>
                                                                      <?php else: ?>
                                                                           <span class="text-muted">–</span>
                                                                      <?php endif; ?>
                                                                 </td>
                                                            </tr>
                                                       <?php endforeach; ?>
                                                  <?php endif; ?>
                                             </tbody>
                                        </table>
                                   </div>

                                   <div class="mt-3" style="padding-top: 16px; border-top: 1px solid var(--line); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
                                        <div>
                                             <?php
                                             $displayFrom = date('M d, Y', strtotime($fromDate));
                                             $displayTo = date('M d, Y', strtotime($toDate));
                                             if ($fromDate === $toDate) {
                                                  $dateLabel = 'for ' . $displayFrom;
                                             } else {
                                                  $dateLabel = 'from ' . $displayFrom . ' to ' . $displayTo;
                                             }
                                             ?>
                                             <span style="font-size: 0.88rem; color: var(--text-soft);">Total <?=$dateLabel; ?>:</span>
                                             <span style="color: var(--danger); font-weight: 800; font-family: var(--font-primary, Montserrat, Segoe UI, Arial, sans-serif); font-size: 1.1rem; margin-left: 8px;"><?= number_format($totalExpenses, 2); ?></span>
                                        </div>
                                        <div style="font-size: 0.8rem; color: var(--text-faint);">
                                             <?= count($data); ?> record(s) found
                                        </div>
                                   </div>
                              </div>
                         </div>

                    </div>
               </div>

               <?php include('includes/footer.php'); ?>
          </div>
     </div>

     <?php include('includes/themecustomizer.php'); ?>

     <div class="modal fade berps-form-modal" id="expenseModal" tabindex="-1" role="dialog" aria-hidden="true">
          <div class="modal-dialog modal-lg" role="document">
               <div class="modal-content expense-modal">
                    <div class="modal-header">
                         <h5 class="modal-title">
                              <i class="mdi mdi-cash-minus"></i>
                              New Expense
                         </h5>
                         <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                              <span aria-hidden="true">&times;</span>
                         </button>
                    </div>
                    <div class="modal-body">
                         <form class="needs-validation" method="post" action="<?= base_url(); ?>Page/addExpenses" enctype="multipart/form-data" novalidate>
                              <div class="row">
                                   <div class="col-md-6">
                                        <div class="form-group">
                                             <label for="expense-date">Date <span class="text-danger">*</span></label>
                                             <input type="date" class="form-control" id="expense-date" name="ExpenseDate" value="<?= date('Y-m-d'); ?>" required>
                                        </div>
                                   </div>
                                   <div class="col-md-6">
                                        <div class="form-group">
                                             <label for="expense-amount">Amount <span class="text-danger">*</span></label>
                                             <input type="number" class="form-control" id="expense-amount" name="Amount" min="0" step="0.01" required>
                                        </div>
                                   </div>
                              </div>
                              <div class="form-group">
                                   <label for="expense-desc">Description <span class="text-danger">*</span></label>
                                   <input type="text" class="form-control" id="expense-desc" name="Description" required>
                              </div>
                              <div class="row">
                                   <div class="col-md-6">
                                        <div class="form-group">
                                             <label for="expense-category">Category <span class="text-danger">*</span></label>
                                             <select class="form-control" id="expense-category" name="Category" required>
                                                  <option value="">Select expense category</option>
                                                  <?php foreach ($expenseCategories as $categoryRow): ?>
                                                       <?php $categoryName = trim((string) ($categoryRow->Category ?? '')); ?>
                                                       <?php if ($categoryName !== ''): ?>
                                                            <option value="<?= htmlspecialchars($categoryName, ENT_QUOTES, 'UTF-8'); ?>">
                                                                 <?= htmlspecialchars($categoryName, ENT_QUOTES, 'UTF-8'); ?>
                                                            </option>
                                                       <?php endif; ?>
                                                  <?php endforeach; ?>
                                             </select>
                                        </div>
                                   </div>
                                   <div class="col-md-6">
                                        <div class="form-group">
                                             <label for="expense-responsible">Responsible <span class="text-danger">*</span></label>
                                             <input type="text" class="form-control" id="expense-responsible" name="Responsible" required>
                                        </div>
                                   </div>
                              </div>
                              <div class="form-group">
                                   <label for="expense-attachment">Attachment (Optional)</label>
                                   <div class="custom-file">
                                        <input type="file" class="custom-file-input" id="expense-attachment" name="attachment" accept=".jpg,.jpeg,.png,.pdf">
                                        <label class="custom-file-label" for="expense-attachment">Choose file...</label>
                                   </div>
                                   <small class="form-text text-muted">Accepted formats: JPG, JPEG, PNG, PDF. Maximum file size: 5MB.</small>
                              </div>
                              <div class="modal-footer">
                                   <button type="reset" class="btn btn-light">
                                        <i class="mdi mdi-refresh"></i> Reset
                                   </button>
                                   <button type="submit" name="submit" class="btn btn-primary">
                                        <i class="mdi mdi-check"></i> Save Expense
                                   </button>
                              </div>
                         </form>
                    </div>
               </div>
          </div>
     </div>

     <!-- Bulk Upload Modal -->
     <div class="modal fade berps-form-modal" id="bulkUploadModal" tabindex="-1" role="dialog" aria-hidden="true">
          <div class="modal-dialog modal-lg" role="document">
               <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                         <h5 class="modal-title mb-0">Bulk Upload Expenses</h5>
                         <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                              <span aria-hidden="true">&times;</span>
                         </button>
                    </div>
                    <div class="modal-body">
                         <div class="alert alert-info mb-4" style="border: none; border-radius: 12px;">
                              <strong><i class="mdi mdi-information-outline"></i> Instructions:</strong>
                              <ul class="mb-0 mt-2" style="padding-left: 20px;">
                                   <li>Download the <a href="<?= base_url(); ?>Page/downloadExpenseTemplate" class="font-weight-bold">Excel template</a> first.</li>
                                   <li>Fill in your expense data (Date, Amount, Description, Category, Responsible).</li>
                                   <li>Save as CSV or Excel (.xlsx/.xls) format.</li>
                                   <li>Upload the file below.</li>
                              </ul>
                         </div>

                         <form method="post" action="<?= base_url(); ?>Page/bulkUploadExpenses" enctype="multipart/form-data" id="bulkUploadForm">
                              <div class="form-group">
                                   <label for="expense-file">Upload Expense File <span class="text-danger">*</span></label>
                                   <div class="custom-file">
                                        <input type="file" class="custom-file-input" id="expense-file" name="expense_file" accept=".csv,.xlsx,.xls" required>
                                        <label class="custom-file-label" for="expense-file">Choose file...</label>
                                   </div>
                                   <small class="form-text text-muted">Accepted formats: CSV, Excel (.xlsx, .xls). Maximum file size: 5MB.</small>
                              </div>

                              <div class="form-group">
                                   <label for="default-category">Default Category (optional)</label>
                                   <select class="form-control" id="default-category" name="default_category">
                                        <option value="">-- Use category from file --</option>
                                        <?php foreach ($expenseCategories as $categoryRow): ?>
                                             <?php $categoryName = trim((string) ($categoryRow->Category ?? '')); ?>
                                             <?php if ($categoryName !== ''): ?>
                                                  <option value="<?= htmlspecialchars($categoryName, ENT_QUOTES, 'UTF-8'); ?>">
                                                       <?= htmlspecialchars($categoryName, ENT_QUOTES, 'UTF-8'); ?>
                                                  </option>
                                             <?php endif; ?>
                                        <?php endforeach; ?>
                                   </select>
                                   <small class="form-text text-muted">Select a default category if your file doesn't have a Category column.</small>
                              </div>

                              <div class="text-right">
                                   <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
                                   <button type="submit" class="btn btn-primary ml-2">
                                        <i class="mdi mdi-upload"></i>Upload & Process
                                   </button>
                              </div>
                         </form>

                         <!-- Preview section (hidden initially) -->
                         <div id="uploadPreview" class="mt-4" style="display: none;">
                              <hr>
                              <h6>Preview:</h6>
                              <div class="table-responsive">
                                   <table class="table table-sm table-bordered" id="previewTable">
                                        <thead class="thead-light">
                                             <tr>
                                                  <th>Date</th>
                                                  <th>Amount</th>
                                                  <th>Description</th>
                                                  <th>Category</th>
                                                  <th>Responsible</th>
                                                  <th>Status</th>
                                             </tr>
                                        </thead>
                                        <tbody></tbody>
                                   </table>
                              </div>
                         </div>
                    </div>
               </div>
          </div>
     </div>

     <!-- Yearly Expense Details Modal -->
     <div class="modal fade berps-form-modal" id="yearlyDetailsModal" tabindex="-1" role="dialog" aria-hidden="true">
          <div class="modal-dialog modal-xl" role="document">
               <div class="modal-content expense-modal">
                    <div class="modal-header">
                         <h5 class="modal-title">
                              <i class="mdi mdi-chart-line"></i>
                              Yearly Expense Details
                         </h5>
                         <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                              <span aria-hidden="true">&times;</span>
                         </button>
                    </div>
                    <div class="modal-body">
                         <div class="row mb-3">
                              <div class="col-md-4">
                                   <div class="card" style="background: linear-gradient(135deg, #f8f9ff, #eef2ff); border: none;">
                                        <div class="card-body text-center">
                                             <h6 class="card-title mb-2" style="color: var(--primary);">Total Expenses</h6>
                                             <h3 id="modalTotalExpenses" style="color: var(--danger); font-weight: 800;">-</h3>
                                             <small id="modalYearLabel" class="text-muted">-</small>
                                        </div>
                                   </div>
                              </div>
                              <div class="col-md-4">
                                   <div class="card" style="background: linear-gradient(135deg, #ecfdf5, #d1fae5); border: none;">
                                        <div class="card-body text-center">
                                             <h6 class="card-title mb-2" style="color: var(--success);">Monthly Average</h6>
                                             <h3 id="modalMonthlyAverage" style="color: var(--success); font-weight: 800;">-</h3>
                                             <small class="text-muted">Per Month</small>
                                        </div>
                                   </div>
                              </div>
                              <div class="col-md-4">
                                   <div class="card" style="background: linear-gradient(135deg, #fff7ed, #fed7aa); border: none;">
                                        <div class="card-body text-center">
                                             <h6 class="card-title mb-2" style="color: var(--warning);">Total Records</h6>
                                             <h3 id="modalTotalRecords" style="color: var(--warning); font-weight: 800;">-</h3>
                                             <small class="text-muted">Expenses</small>
                                        </div>
                                   </div>
                              </div>
                         </div>
                         
                         <div class="row">
                              <div class="col-md-6">
                                   <h6 class="mb-3">Monthly Breakdown</h6>
                                   <div class="table-responsive">
                                        <table class="table table-sm" id="monthlyTable">
                                             <thead>
                                                  <tr>
                                                       <th>Month</th>
                                                       <th class="text-right">Amount</th>
                                                       <th class="text-right">Count</th>
                                                  </tr>
                                             </thead>
                                             <tbody id="monthlyTableBody">
                                                  <tr>
                                                       <td colspan="3" class="text-center text-muted">Loading...</td>
                                                  </tr>
                                             </tbody>
                                        </table>
                                   </div>
                              </div>
                              <div class="col-md-6">
                                   <h6 class="mb-3">Category Breakdown</h6>
                                   <div class="table-responsive">
                                        <table class="table table-sm" id="categoryTable">
                                             <thead>
                                                  <tr>
                                                       <th>Category</th>
                                                       <th class="text-right">Amount</th>
                                                       <th class="text-right">%</th>
                                                  </tr>
                                             </thead>
                                             <tbody id="categoryTableBody">
                                                  <tr>
                                                       <td colspan="3" class="text-center text-muted">Loading...</td>
                                                  </tr>
                                             </tbody>
                                        </table>
                                   </div>
                              </div>
                         </div>
                    </div>
                    <div class="modal-footer">
                         <button type="button" class="btn btn-light" data-dismiss="modal">Close</button>
                         <button type="button" class="btn btn-primary" id="exportYearlyData">
                              <i class="mdi mdi-download"></i> Export Data
                         </button>
                    </div>
               </div>
          </div>
     </div>

     <script src="<?= base_url(); ?>assets/js/vendor.min.js"></script>
     <script src="<?= base_url(); ?>assets/libs/select2/select2.min.js"></script>
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
                    var $tableContainer = $('.expenses-list-page .data-table-container');
                    var $expensesTable = $('#expenses-table');

                    // Initialize DataTable
                    var table = $expensesTable.DataTable({
                         responsive: true,
                         autoWidth: false,
                         order: [
                              [0, 'desc']
                         ],
                         language: {
                              emptyTable: 'No expenses recorded yet.',
                              search: '',
                              searchPlaceholder: 'Search expenses...'
                         },
                         columnDefs: [{
                              targets: 1,
                              className: 'text-right'
                         }, {
                              targets: -1,
                              orderable: false,
                              searchable: false
                         }],
                         dom: '<"row"<"col-sm-12"tr>>' +
                              '<"row"<"col-sm-5"i><"col-sm-7"p>>',
                         initComplete: function() {
                              $expensesTable.removeClass('table-init-hidden').addClass('table-init-ready');
                              $tableContainer.removeClass('loading').addClass('ready');

                              // Category filter
                              $('#category-filter').on('change', function() {
                                   var category = $(this).val();
                                   if (category) {
                                        table.column(3).search('^' + category + '$', true, false).draw();
                                   } else {
                                        table.column(3).search('').draw();
                                   }
                              });

                              // Search input
                              $('#search-input').on('keyup', function() {
                                   table.search(this.value).draw();
                              });
                         }
                    });

                    // Clear filters button functionality
                    $(document).on('click', '.clear-filters', function(e) {
                         e.preventDefault();
                         $('#search-input').val('');
                         $('#category-filter').val('');
                         table.search('').columns().search('').draw();
                    });

                    if (window.jQuery && $.fn && typeof $.fn.select2 === 'function') {
                         $('#expenseModal').on('shown.bs.modal', function() {
                              var $categorySelect = $('#expense-category');
                              if ($categorySelect.hasClass('select2-hidden-accessible')) {
                                   return;
                              }

                              $categorySelect.select2({
                                   width: '100%',
                                   placeholder: 'Search expense category',
                                   dropdownParent: $('#expenseModal'),
                                   allowClear: true
                              });
                         });
                    }

                    // Custom file input handler
                    $('.custom-file-input').on('change', function() {
                         var fileName = $(this).val().split('\\').pop();
                         $(this).siblings('.custom-file-label').addClass('selected').html(fileName);
                    });

                    // Dashboard card click handlers
                    $('.dashboard-card').on('click', function() {
                         var type = $(this).data('type');
                         var year = $(this).data('year');
                         loadYearlyDetails(year, type);
                    });

                    // Load yearly details function
                    function loadYearlyDetails(year, type) {
                         // Show loading state
                         $('#monthlyTableBody').html('<tr><td colspan="3" class="text-center text-muted">Loading...</td></tr>');
                         $('#categoryTableBody').html('<tr><td colspan="3" class="text-center text-muted">Loading...</td></tr>');
                         
                         // Update modal title and year
                         $('#modalYearLabel').text(year);
                         
                         // Fetch yearly details via AJAX
                         $.ajax({
                              url: '<?= base_url(); ?>Page/getYearlyExpenseDetails',
                              method: 'POST',
                              data: {
                                   year: year,
                                   type: type
                              },
                              success: function(response) {
                                   if (response.success) {
                                        updateModalContent(response.data);
                                        $('#yearlyDetailsModal').modal('show');
                                   } else {
                                        Swal.fire('Error', response.message || 'Failed to load data', 'error');
                                   }
                              },
                              error: function() {
                                   Swal.fire('Error', 'Failed to load yearly details', 'error');
                              }
                         });
                    }

                    // Update modal content with fetched data
                    function updateModalContent(data) {
                         // Update summary cards
                         $('#modalTotalExpenses').text(data.totalExpensesFormatted);
                         $('#modalMonthlyAverage').text(data.monthlyAverageFormatted);
                         $('#modalTotalRecords').text(data.totalRecords);
                         
                         // Update monthly breakdown
                         var monthlyHtml = '';
                         if (data.monthlyBreakdown && data.monthlyBreakdown.length > 0) {
                              data.monthlyBreakdown.forEach(function(month) {
                                   monthlyHtml += '<tr>';
                                   monthlyHtml += '<td>' + month.month + '</td>';
                                   monthlyHtml += '<td class="text-right">' + month.amountFormatted + '</td>';
                                   monthlyHtml += '<td class="text-right">' + month.count + '</td>';
                                   monthlyHtml += '</tr>';
                              });
                         } else {
                              monthlyHtml = '<tr><td colspan="3" class="text-center text-muted">No data available</td></tr>';
                         }
                         $('#monthlyTableBody').html(monthlyHtml);
                         
                         // Update category breakdown
                         var categoryHtml = '';
                         if (data.categoryBreakdown && data.categoryBreakdown.length > 0) {
                              data.categoryBreakdown.forEach(function(category) {
                                   categoryHtml += '<tr>';
                                   categoryHtml += '<td>' + category.category + '</td>';
                                   categoryHtml += '<td class="text-right">' + category.amountFormatted + '</td>';
                                   categoryHtml += '<td class="text-right">' + category.percentage + '%</td>';
                                   categoryHtml += '</tr>';
                              });
                         } else {
                              categoryHtml = '<tr><td colspan="3" class="text-center text-muted">No data available</td></tr>';
                         }
                         $('#categoryTableBody').html(categoryHtml);
                    }

                    // Export yearly data
                    $('#exportYearlyData').on('click', function() {
                         var year = $('#modalYearLabel').text();
                         window.open('<?= base_url(); ?>Page/exportYearlyExpenses?year=' + year, '_blank');
                    });
               });
          })(jQuery);
     </script>

</body>

</html>
