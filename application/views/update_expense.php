<!DOCTYPE html>
<html lang="en">

<?php include('includes/head.php'); ?>

<?php
$expenseCategories = isset($expenseCategories) && is_array($expenseCategories) ? $expenseCategories : array();
$currentExpenseCategory = isset($data[0]->Category) ? trim((string) $data[0]->Category) : '';
$expenseCategoryOptions = array();
foreach ($expenseCategories as $categoryRow) {
     $categoryName = trim((string) ($categoryRow->Category ?? ''));
     if ($categoryName !== '') {
          $expenseCategoryOptions[$categoryName] = $categoryName;
     }
}
if ($currentExpenseCategory !== '' && !isset($expenseCategoryOptions[$currentExpenseCategory])) {
     $expenseCategoryOptions[$currentExpenseCategory] = $currentExpenseCategory;
}
ksort($expenseCategoryOptions, SORT_NATURAL | SORT_FLAG_CASE);
?>

<body>

     <div id="wrapper">

          <?php include('includes/top-nav-bar.php'); ?>
          <?php include('includes/sidebar.php'); ?>

          <div class="content-page">
               <div class="content">
                    <div class="container-fluid update-expense-page">
                         <style>
                              /* ─── Reset & base ─────────────────────────────────────── */
                              .update-expense-page * {
                                   box-sizing: border-box;
                              }

                              /* ─── Page shell ────────────────────────────────────────── */
                              .update-expense-page {
                                   --bg: #f5f7fb;
                                   --surface: rgba(255, 255, 255, 0.96);
                                   --surface-strong: #ffffff;
                                   --surface-soft: #f8fbff;
                                   --line: #e4ebf4;
                                   --line-strong: #cfdbea;
                                   --text: #142235;
                                   --text-soft: #617489;
                                   --text-faint: #8ea0b5;
                                   --primary: #2563eb;
                                   --primary-2: #1d4ed8;
                                   --primary-soft: #eaf2ff;
                                   --success: #059669;
                                   --success-soft: #ecfdf5;
                                   --warning: #d97706;
                                   --warning-soft: #fff7ed;
                                   --danger: #e11d48;
                                   --danger-soft: #fff1f2;
                                   --shadow: 0 20px 50px rgba(15, 23, 42, 0.08);
                                   --shadow-soft: 0 12px 30px rgba(15, 23, 42, 0.05);
                                   --radius-xl: 24px;
                                   --radius-lg: 18px;
                                   --radius-md: 14px;
                                   --radius-sm: 10px;
                                   --font-body: var(--font-primary);
                                   --font-head: var(--font-primary);
                                   --font-mono: var(--font-primary);
                                   background:
                                       radial-gradient(circle at top left, rgba(37, 99, 235, 0.08), transparent 28%),
                                       radial-gradient(circle at top right, rgba(16, 185, 129, 0.08), transparent 24%),
                                       linear-gradient(180deg, #f8fbff 0%, #f4f7fb 100%);
                                   min-height: 100vh;
                                   padding-bottom: 28px;
                                   font-family: var(--font-primary, Montserrat, Segoe UI, Arial, sans-serif);
                                   color: var(--text);
                              }

                              /* ─── Page header ───────────────────────────────────────── */
                              .update-expense-page .page-header {
                                   display: flex;
                                   justify-content: space-between;
                                   align-items: flex-end;
                                   gap: 18px;
                                   margin: 24px 0 22px;
                                   flex-wrap: wrap;
                              }

                              .update-expense-page .page-eyebrow {
                                   display: inline-flex;
                                   align-items: center;
                                   gap: 8px;
                                   padding: 7px 12px;
                                   border-radius: 999px;
                                   background: rgba(37, 99, 235, 0.08);
                                   color: var(--primary-2);
                                   font-size: 0.74rem;
                                   font-weight: 700;
                                   letter-spacing: 0.08em;
                                   text-transform: uppercase;
                                   margin-bottom: 12px;
                              }

                              .update-expense-page .page-eyebrow::before {
                                   content: '';
                                   width: 8px;
                                   height: 8px;
                                   border-radius: 50%;
                                   background: linear-gradient(135deg, var(--primary), var(--primary-2));
                                   box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.10);
                              }

                              .update-expense-page .page-title {
                                   margin: 0;
                                   font-family: var(--font-primary, Montserrat, Segoe UI, Arial, sans-serif);
                                   font-size: 2.15rem;
                                   line-height: 1.05;
                                   letter-spacing: -0.05em;
                                   font-weight: 800;
                                   color: var(--text);
                              }

                              .update-expense-page .page-subtitle {
                                   margin-top: 8px;
                                   color: var(--text-soft);
                                   font-size: 0.96rem;
                                   max-width: 760px;
                              }

                              .update-expense-page .page-actions {
                                   display: flex;
                                   gap: 12px;
                                   flex-wrap: wrap;
                              }

                              /* ─── Buttons ───────────────────────────────────────────── */
                              .update-expense-page .btn-action,
                              .update-expense-page .btn-submit,
                              .update-expense-page .btn-danger-soft {
                                   display: inline-flex;
                                   align-items: center;
                                   justify-content: center;
                                   gap: 8px;
                                   border-radius: 12px;
                                   font-size: 0.92rem;
                                   font-weight: 700;
                                   padding: 11px 18px;
                                   transition: all 0.16s ease;
                                   text-decoration: none;
                                   cursor: pointer;
                                   line-height: 1;
                                   border: none;
                              }

                              .update-expense-page .btn-action {
                                   border: 1px solid var(--line-strong);
                                   color: var(--text);
                                   background: #fff;
                              }

                              .update-expense-page .btn-action:hover {
                                   color: var(--primary);
                                   border-color: #bfd3ef;
                                   background: #f9fbff;
                                   text-decoration: none;
                              }

                              .update-expense-page .btn-submit {
                                   color: #fff;
                                   background: linear-gradient(135deg, var(--primary), var(--primary-2));
                                   box-shadow: 0 10px 24px rgba(37, 99, 235, 0.24);
                              }

                              .update-expense-page .btn-submit:hover {
                                   transform: translateY(-1px);
                                   box-shadow: 0 14px 28px rgba(37, 99, 235, 0.28);
                                   color: #fff;
                                   text-decoration: none;
                              }

                              .update-expense-page .btn-danger-soft {
                                   border: 1px solid var(--line-strong);
                                   color: var(--danger);
                                   background: var(--danger-soft);
                              }

                              .update-expense-page .btn-danger-soft:hover {
                                   background: #fecdd3;
                                   text-decoration: none;
                              }

                              /* ─── Cards ─────────────────────────────────────────────── */
                              .update-expense-page .theme-card {
                                   background: var(--surface);
                                   border: 1px solid rgba(255, 255, 255, 0.72);
                                   border-radius: var(--radius-xl);
                                   box-shadow: var(--shadow);
                                   overflow: hidden;
                              }

                              .update-expense-page .theme-card-head {
                                   padding: 18px 22px;
                                   border-bottom: 1px solid var(--line);
                                   background: linear-gradient(180deg, rgba(255, 255, 255, 0.98), rgba(249, 251, 255, 0.94));
                              }

                              .update-expense-page .theme-card-title {
                                   margin: 0;
                                   color: var(--text);
                                   font-size: 1.02rem;
                                   font-weight: 800;
                                   letter-spacing: -0.02em;
                              }

                              .update-expense-page .theme-card-subtitle {
                                   margin-top: 6px;
                                   color: var(--text-soft);
                                   font-size: 0.86rem;
                              }

                              .update-expense-page .theme-card-body {
                                   padding: 22px;
                              }

                              /* ─── Form styles ───────────────────────────────────────── */
                              .update-expense-page .form-control {
                                   border: 1px solid var(--line-strong);
                                   border-radius: var(--radius-sm);
                                   padding: 12px 16px;
                                   font-size: 0.95rem;
                                   color: var(--text);
                                   background: var(--surface-strong);
                                   transition: all 0.16s ease;
                              }

                              .update-expense-page .form-control:focus {
                                   border-color: var(--primary);
                                   box-shadow: 0 0 0 4px var(--primary-soft);
                                   outline: none;
                              }

                              .update-expense-page .form-group label {
                                   font-size: 0.84rem;
                                   font-weight: 700;
                                   color: var(--text);
                                   margin-bottom: 8px;
                                   text-transform: uppercase;
                                   letter-spacing: 0.05em;
                              }

                              .update-expense-page .form-row {
                                   margin-bottom: 16px;
                              }

                              .update-expense-page select.form-control {
                                   appearance: auto;
                                   padding-right: 30px;
                              }

                              /* ─── Summary card ──────────────────────────────────────── */
                              .update-expense-page .summary-item {
                                   display: flex;
                                   justify-content: space-between;
                                   align-items: center;
                                   padding: 12px 0;
                                   border-bottom: 1px solid var(--line);
                              }

                              .update-expense-page .summary-item:last-child {
                                   border-bottom: none;
                              }

                              .update-expense-page .summary-label {
                                   font-size: 0.82rem;
                                   color: var(--text-soft);
                                   font-weight: 600;
                              }

                              .update-expense-page .summary-value {
                                   font-size: 0.95rem;
                                   color: var(--text);
                                   font-weight: 700;
                              }

                              .update-expense-page .summary-value.amount {
                                   color: var(--danger);
                                   font-family: var(--font-primary, Montserrat, Segoe UI, Arial, sans-serif);
                                   font-size: 1.1rem;
                              }

                              .update-expense-page .select2-container {
                                   width: 100% !important;
                              }

                              .update-expense-page .select2-container--default .select2-selection--single {
                                   min-height: 46px;
                                   border: 1px solid var(--line-strong);
                                   border-radius: var(--radius-sm);
                                   display: flex;
                                   align-items: center;
                              }

                              .update-expense-page .select2-container--default .select2-selection--single .select2-selection__rendered {
                                   line-height: 44px;
                                   padding-left: 14px;
                                   padding-right: 38px;
                                   color: var(--text);
                              }

                              .update-expense-page .select2-container--default .select2-selection--single .select2-selection__arrow {
                                   height: 44px;
                                   right: 10px;
                              }

                              .update-expense-page .select2-container--default.select2-container--focus .select2-selection--single,
                              .update-expense-page .select2-container--default.select2-container--open .select2-selection--single {
                                   border-color: var(--primary);
                                   box-shadow: 0 0 0 4px var(--primary-soft);
                              }

                              .update-expense-page .select2-dropdown {
                                   border-color: var(--line-strong);
                                   border-radius: 14px;
                                   overflow: hidden;
                                   box-shadow: 0 18px 38px rgba(15, 23, 42, 0.14);
                              }

                              .update-expense-page .select2-search--dropdown {
                                   padding: 12px;
                              }

                              .update-expense-page .select2-search__field {
                                   border: 1px solid var(--line-strong);
                                   border-radius: 10px;
                                   padding: 9px 12px;
                              }

                              /* ─── Responsive ────────────────────────────────────────── */
                              @media (max-width: 767px) {
                                   .update-expense-page .page-title {
                                        font-size: 1.75rem;
                                   }

                                   .update-expense-page .page-header {
                                        flex-direction: column;
                                        align-items: flex-start;
                                   }

                                   .update-expense-page .theme-card-head,
                                   .update-expense-page .theme-card-body {
                                        padding-left: 16px;
                                        padding-right: 16px;
                                   }
                              }
                         </style>

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

                         <!-- Page header -->
                         <div class="page-header">
                              <div>
                                   <div class="page-eyebrow">Finance Management</div>
                                   <h4 class="page-title">Update Expense</h4>
                                   <div class="page-subtitle">Modify the recorded expense details.</div>
                              </div>
                              <div class="page-actions">
                                   <a href="<?= base_url(); ?>Page/expensesList" class="btn-action">
                                        <i class="mdi mdi-chevron-left"></i>Back to List
                                   </a>
                              </div>
                         </div>

                         <div class="row">
                              <div class="col-lg-8">
                                   <div class="theme-card">
                                        <div class="theme-card-head">
                                             <h5 class="theme-card-title">Expense Details</h5>
                                             <div class="theme-card-subtitle">Update the fields below to modify this expense record.</div>
                                        </div>
                                        <div class="theme-card-body">
                                             <form class="needs-validation" method="post" action="<?= base_url(); ?>Page/updateExpenses?id=<?= $data[0]->expensesid; ?>" enctype="multipart/form-data" novalidate>
                                                  <input type="hidden" name="id" value="<?= $data[0]->expensesid; ?>">

                                                  <div class="form-row">
                                                       <div class="form-group col-md-6">
                                                            <label for="expense-date">Date <span class="text-danger">*</span></label>
                                                            <input type="date" class="form-control" id="expense-date" name="ExpenseDate" value="<?= $data[0]->ExpenseDate; ?>" required>
                                                       </div>
                                                       <div class="form-group col-md-6">
                                                            <label for="expense-amount">Amount <span class="text-danger">*</span></label>
                                                            <input type="number" class="form-control" id="expense-amount" name="Amount" min="0" step="0.01" value="<?= $data[0]->Amount; ?>" required>
                                                       </div>
                                                  </div>

                                                  <div class="form-group">
                                                       <label for="expense-description">Description <span class="text-danger">*</span></label>
                                                       <input type="text" class="form-control" id="expense-description" name="Description" value="<?= htmlspecialchars($data[0]->Description, ENT_QUOTES, 'UTF-8'); ?>" required>
                                                  </div>

                                                  <div class="form-row">
                                                       <div class="form-group col-md-6">
                                                            <label for="expense-category">Category <span class="text-danger">*</span></label>
                                                            <select class="form-control" id="expense-category" name="Category" required>
                                                                 <option value="">Select a category</option>
                                                                 <?php foreach ($expenseCategoryOptions as $categoryName): ?>
                                                                      <option value="<?= htmlspecialchars($categoryName, ENT_QUOTES, 'UTF-8'); ?>" <?= $categoryName === $currentExpenseCategory ? 'selected' : ''; ?>>
                                                                           <?= htmlspecialchars($categoryName, ENT_QUOTES, 'UTF-8'); ?>
                                                                      </option>
                                                                 <?php endforeach; ?>
                                                            </select>
                                                       </div>
                                                       <div class="form-group col-md-6">
                                                            <label for="expense-responsible">Responsible Person <span class="text-danger">*</span></label>
                                                            <input type="text" class="form-control" id="expense-responsible" name="Responsible" value="<?= htmlspecialchars($data[0]->Responsible, ENT_QUOTES, 'UTF-8'); ?>" required>
                                                       </div>
                                                  </div>

                                                  <div class="form-group">
                                                       <label for="expense-attachment">Attachment (Optional)</label>
                                                       <?php if (!empty($data[0]->attachment)): ?>
                                                            <div style="margin-bottom: 10px; padding: 10px; background: #f8f9fa; border-radius: 8px; border: 1px solid #e4ebf4;">
                                                                 <div style="font-size: 0.85rem; color: #617489; margin-bottom: 8px;">Current attachment:</div>
                                                                 <?php 
                                                                 $fileExtension = strtolower(pathinfo($data[0]->attachment, PATHINFO_EXTENSION));
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
                                                                 <div style="display: flex; align-items: center; gap: 8px;">
                                                                      <a href="<?= base_url(); ?><?= htmlspecialchars($data[0]->attachment); ?>" 
                                                                         target="_blank" 
                                                                         style="color: <?= $color; ?>; text-decoration: none; display: inline-flex; align-items: center; gap: 4px;">
                                                                         <i class="mdi <?= $icon; ?>"></i>
                                                                         <span><?= basename($data[0]->attachment); ?></span>
                                                                      </a>
                                                                      <span style="color: #8ea0b5; font-size: 0.8rem;">(<?= strtoupper($fileExtension); ?>)</span>
                                                                 </div>
                                                                 <div style="margin-top: 8px;">
                                                                      <label style="font-size: 0.8rem; color: #617489; display: flex; align-items: center; gap: 6px;">
                                                                           <input type="checkbox" name="remove_attachment" value="1" style="margin: 0;">
                                                                           Remove current attachment
                                                                      </label>
                                                                 </div>
                                                            </div>
                                                       <?php endif; ?>
                                                       <div class="custom-file">
                                                            <input type="file" class="custom-file-input" id="expense-attachment" name="attachment" accept=".jpg,.jpeg,.png,.pdf">
                                                            <label class="custom-file-label" for="expense-attachment">Choose file...</label>
                                                       </div>
                                                       <small class="form-text text-muted">Accepted formats: JPG, JPEG, PNG, PDF. Maximum file size: 5MB. <?= !empty($data[0]->attachment) ? 'Leave empty to keep current attachment.' : ''; ?></small>
                                                  </div>

                                                  <div class="text-right" style="margin-top: 24px; padding-top: 20px; border-top: 1px solid var(--line);">
                                                       <a href="<?= base_url(); ?>Page/expensesList" class="btn-action" style="margin-right: 8px;">Cancel</a>
                                                       <button type="submit" name="submit" class="btn-submit">
                                                            <i class="mdi mdi-check"></i>Save Changes
                                                       </button>
                                                  </div>
                                             </form>
                                        </div>
                                   </div>
                              </div>

                              <div class="col-lg-4">
                                   <div class="theme-card">
                                        <div class="theme-card-head">
                                             <h5 class="theme-card-title">Current Values</h5>
                                        </div>
                                        <div class="theme-card-body">
                                             <div class="summary-item">
                                                  <span class="summary-label">Amount</span>
                                                  <span class="summary-value amount"><?= number_format($data[0]->Amount, 2); ?></span>
                                             </div>
                                             <div class="summary-item">
                                                  <span class="summary-label">Date</span>
                                                  <span class="summary-value"><?= date('F d, Y', strtotime($data[0]->ExpenseDate)); ?></span>
                                             </div>
                                             <div class="summary-item">
                                                  <span class="summary-label">Category</span>
                                                  <span class="summary-value"><?= $data[0]->Category; ?></span>
                                             </div>
                                             <div class="summary-item">
                                                  <span class="summary-label">Responsible</span>
                                                  <span class="summary-value"><?= $data[0]->Responsible; ?></span>
                                             </div>
                                             <div class="summary-item">
                                                  <span class="summary-label">Expense ID</span>
                                                  <span class="summary-value" style="font-family: var(--font-primary, Montserrat, Segoe UI, Arial, sans-serif); font-size: 0.85rem; color: var(--text-faint);">#<?= $data[0]->expensesid; ?></span>
                                             </div>
                                        </div>
                                   </div>

                                   <div class="theme-card" style="margin-top: 20px;">
                                        <div class="theme-card-head" style="background: linear-gradient(180deg, rgba(225, 29, 72, 0.08), rgba(225, 29, 72, 0.02));">
                                             <h5 class="theme-card-title" style="color: var(--danger);">Danger Zone</h5>
                                        </div>
                                        <div class="theme-card-body">
                                             <p style="font-size: 0.86rem; color: var(--text-soft); margin-bottom: 16px;">Deleting this expense will permanently remove it from the system. This action cannot be undone.</p>
                                             <a href="<?= base_url(); ?>Page/deleteExpense?id=<?= $data[0]->expensesid; ?>" class="btn-danger-soft" onclick="return confirm('Are you sure you want to delete this expense? This action cannot be undone.');" style="width: 100%;">
                                                  <i class="mdi mdi-trash-can-outline"></i>Delete Expense
                                             </a>
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
     <script>
          (function($) {
               'use strict';

               $(function() {
                    if (window.jQuery && $.fn && typeof $.fn.select2 === 'function') {
                         $('#expense-category').select2({
                              width: '100%',
                              placeholder: 'Search expense category',
                              allowClear: true
                         });
                    }
               });
          })(jQuery);
     </script>

</body>

</html>
