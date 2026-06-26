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
                    <div class="container-fluid add-expense-page">
                         <style>
                              /* ─── Reset & base ─────────────────────────────────────── */
                              .add-expense-page * {
                                   box-sizing: border-box;
                              }

                              /* ─── Page shell ────────────────────────────────────────── */
                              .add-expense-page {
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
                              .add-expense-page .page-header {
                                   display: flex;
                                   justify-content: space-between;
                                   align-items: flex-end;
                                   gap: 18px;
                                   margin: 24px 0 22px;
                                   flex-wrap: wrap;
                              }

                              .add-expense-page .page-eyebrow {
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

                              .add-expense-page .page-eyebrow::before {
                                   content: '';
                                   width: 8px;
                                   height: 8px;
                                   border-radius: 50%;
                                   background: linear-gradient(135deg, var(--primary), var(--primary-2));
                                   box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.10);
                              }

                              .add-expense-page .page-title {
                                   margin: 0;
                                   font-family: var(--font-primary, Montserrat, Segoe UI, Arial, sans-serif);
                                   font-size: 2.15rem;
                                   line-height: 1.05;
                                   letter-spacing: -0.05em;
                                   font-weight: 800;
                                   color: var(--text);
                              }

                              .add-expense-page .page-subtitle {
                                   margin-top: 8px;
                                   color: var(--text-soft);
                                   font-size: 0.96rem;
                                   max-width: 760px;
                              }

                              .add-expense-page .page-actions {
                                   display: flex;
                                   gap: 12px;
                                   flex-wrap: wrap;
                              }

                              /* ─── Buttons ───────────────────────────────────────────── */
                              .add-expense-page .btn-action,
                              .add-expense-page .btn-submit {
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

                              .add-expense-page .btn-action {
                                   border: 1px solid var(--line-strong);
                                   color: var(--text);
                                   background: #fff;
                              }

                              .add-expense-page .btn-action:hover {
                                   color: var(--primary);
                                   border-color: #bfd3ef;
                                   background: #f9fbff;
                                   text-decoration: none;
                              }

                              .add-expense-page .btn-submit {
                                   color: #fff;
                                   background: linear-gradient(135deg, var(--primary), var(--primary-2));
                                   box-shadow: 0 10px 24px rgba(37, 99, 235, 0.24);
                              }

                              .add-expense-page .btn-submit:hover {
                                   transform: translateY(-1px);
                                   box-shadow: 0 14px 28px rgba(37, 99, 235, 0.28);
                                   color: #fff;
                                   text-decoration: none;
                              }

                              /* ─── Cards ─────────────────────────────────────────────── */
                              .add-expense-page .theme-card {
                                   background: var(--surface);
                                   border: 1px solid rgba(255, 255, 255, 0.72);
                                   border-radius: var(--radius-xl);
                                   box-shadow: var(--shadow);
                                   overflow: hidden;
                              }

                              .add-expense-page .theme-card-head {
                                   padding: 18px 22px;
                                   border-bottom: 1px solid var(--line);
                                   background: linear-gradient(180deg, rgba(255, 255, 255, 0.98), rgba(249, 251, 255, 0.94));
                              }

                              .add-expense-page .theme-card-title {
                                   margin: 0;
                                   color: var(--text);
                                   font-size: 1.02rem;
                                   font-weight: 800;
                                   letter-spacing: -0.02em;
                              }

                              .add-expense-page .theme-card-subtitle {
                                   margin-top: 6px;
                                   color: var(--text-soft);
                                   font-size: 0.86rem;
                              }

                              .add-expense-page .theme-card-body {
                                   padding: 22px;
                              }

                              /* ─── Form styles ───────────────────────────────────────── */
                              .add-expense-page .form-control {
                                   border: 1px solid var(--line-strong);
                                   border-radius: var(--radius-sm);
                                   padding: 12px 16px;
                                   font-size: 0.95rem;
                                   color: var(--text);
                                   background: var(--surface-strong);
                                   transition: all 0.16s ease;
                              }

                              .add-expense-page .form-control:focus {
                                   border-color: var(--primary);
                                   box-shadow: 0 0 0 4px var(--primary-soft);
                                   outline: none;
                              }

                              .add-expense-page .form-group label {
                                   font-size: 0.84rem;
                                   font-weight: 700;
                                   color: var(--text);
                                   margin-bottom: 8px;
                                   text-transform: uppercase;
                                   letter-spacing: 0.05em;
                              }

                              .add-expense-page .form-row {
                                   margin-bottom: 16px;
                              }

                              .add-expense-page select.form-control {
                                   appearance: auto;
                                   padding-right: 30px;
                              }

                              .add-expense-page .select2-container {
                                   width: 100% !important;
                              }

                              .add-expense-page .select2-container--default .select2-selection--single {
                                   min-height: 46px;
                                   border: 1px solid var(--line-strong);
                                   border-radius: var(--radius-sm);
                                   display: flex;
                                   align-items: center;
                              }

                              .add-expense-page .select2-container--default .select2-selection--single .select2-selection__rendered {
                                   line-height: 44px;
                                   padding-left: 14px;
                                   padding-right: 38px;
                                   color: var(--text);
                              }

                              .add-expense-page .select2-container--default .select2-selection--single .select2-selection__arrow {
                                   height: 44px;
                                   right: 10px;
                              }

                              .add-expense-page .select2-container--default.select2-container--focus .select2-selection--single,
                              .add-expense-page .select2-container--default.select2-container--open .select2-selection--single {
                                   border-color: var(--primary);
                                   box-shadow: 0 0 0 4px var(--primary-soft);
                              }

                              .add-expense-page .select2-dropdown {
                                   border-color: var(--line-strong);
                                   border-radius: 14px;
                                   overflow: hidden;
                                   box-shadow: 0 18px 38px rgba(15, 23, 42, 0.14);
                              }

                              .add-expense-page .select2-search--dropdown {
                                   padding: 12px;
                              }

                              .add-expense-page .select2-search__field {
                                   border: 1px solid var(--line-strong);
                                   border-radius: 10px;
                                   padding: 9px 12px;
                              }

                              /* ─── Responsive ────────────────────────────────────────── */
                              @media (max-width: 767px) {
                                   .add-expense-page .page-title {
                                        font-size: 1.75rem;
                                   }

                                   .add-expense-page .page-header {
                                        flex-direction: column;
                                        align-items: flex-start;
                                   }

                                   .add-expense-page .theme-card-head,
                                   .add-expense-page .theme-card-body {
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
                                   <h4 class="page-title">Add New Expense</h4>
                                   <div class="page-subtitle">Record a new expense entry with complete details.</div>
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
                                             <div class="theme-card-subtitle">Fill in all required fields to record the expense.</div>
                                        </div>
                                        <div class="theme-card-body">
                                             <form class="needs-validation" method="post" action="<?= base_url(); ?>Page/addExpenses" novalidate>
                                                  <div class="form-row">
                                                       <div class="form-group col-md-6">
                                                            <label for="expense-date">Date <span class="text-danger">*</span></label>
                                                            <input type="date" class="form-control" id="expense-date" name="ExpenseDate" required>
                                                       </div>
                                                       <div class="form-group col-md-6">
                                                            <label for="expense-amount">Amount <span class="text-danger">*</span></label>
                                                            <input type="number" class="form-control" id="expense-amount" name="Amount" min="0" step="0.01" placeholder="0.00" required>
                                                       </div>
                                                  </div>

                                                  <div class="form-group">
                                                       <label for="expense-description">Description <span class="text-danger">*</span></label>
                                                       <input type="text" class="form-control" id="expense-description" name="Description" placeholder="Enter expense description" required>
                                                  </div>

                                                  <div class="form-row">
                                                       <div class="form-group col-md-6">
                                                            <label for="expense-category">Category <span class="text-danger">*</span></label>
                                                            <select class="form-control" id="expense-category" name="Category" required>
                                                                 <option value="">Select a category</option>
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
                                                       <div class="form-group col-md-6">
                                                            <label for="expense-responsible">Responsible Person <span class="text-danger">*</span></label>
                                                            <input type="text" class="form-control" id="expense-responsible" name="Responsible" placeholder="Enter responsible person" required>
                                                       </div>
                                                  </div>

                                                  <div class="text-right" style="margin-top: 24px; padding-top: 20px; border-top: 1px solid var(--line);">
                                                       <a href="<?= base_url(); ?>Page/expensesList" class="btn-action" style="margin-right: 8px;">Cancel</a>
                                                       <button type="submit" name="submit" class="btn-submit">
                                                            <i class="mdi mdi-check"></i>Save Expense
                                                       </button>
                                                  </div>
                                             </form>
                                        </div>
                                   </div>
                              </div>

                              <div class="col-lg-4">
                                   <div class="theme-card">
                                        <div class="theme-card-head">
                                             <h5 class="theme-card-title">Quick Tips</h5>
                                        </div>
                                        <div class="theme-card-body">
                                             <div style="display: grid; gap: 14px;">
                                                  <div style="display: flex; align-items: flex-start; gap: 12px;">
                                                       <div style="width: 32px; height: 32px; border-radius: 8px; background: var(--primary-soft); color: var(--primary); display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                                            <i class="mdi mdi-calendar" style="font-size: 16px;"></i>
                                                       </div>
                                                       <div>
                                                            <div style="font-weight: 700; font-size: 0.9rem; color: var(--text);">Date</div>
                                                            <div style="font-size: 0.84rem; color: var(--text-soft);">Select the actual date when the expense was incurred.</div>
                                                       </div>
                                                  </div>
                                                  <div style="display: flex; align-items: flex-start; gap: 12px;">
                                                       <div style="width: 32px; height: 32px; border-radius: 8px; background: var(--success-soft); color: var(--success); display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                                            <i class="mdi mdi-cash" style="font-size: 16px;"></i>
                                                       </div>
                                                       <div>
                                                            <div style="font-weight: 700; font-size: 0.9rem; color: var(--text);">Amount</div>
                                                            <div style="font-size: 0.84rem; color: var(--text-soft);">Enter the exact amount spent. Use positive numbers only.</div>
                                                       </div>
                                                  </div>
                                                  <div style="display: flex; align-items: flex-start; gap: 12px;">
                                                       <div style="width: 32px; height: 32px; border-radius: 8px; background: var(--warning-soft); color: var(--warning); display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                                            <i class="mdi mdi-tag" style="font-size: 16px;"></i>
                                                       </div>
                                                       <div>
                                                            <div style="font-weight: 700; font-size: 0.9rem; color: var(--text);">Category</div>
                                                            <div style="font-size: 0.84rem; color: var(--text-soft);">Choose the most appropriate category for accurate reporting.</div>
                                                       </div>
                                                  </div>
                                             </div>
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
     <script src="<?= base_url(); ?>assets/js/app.min.js"></script>

     <script>
          // Set today's date as default
          document.addEventListener('DOMContentLoaded', function() {
               const dateInput = document.getElementById('expense-date');
               if (dateInput && !dateInput.value) {
                    const today = new Date().toISOString().split('T')[0];
                    dateInput.value = today;
               }

               if (window.jQuery && window.jQuery.fn && typeof window.jQuery.fn.select2 === 'function') {
                    window.jQuery('#expense-category').select2({
                         width: '100%',
                         placeholder: 'Search expense category',
                         allowClear: true
                    });
               }
          });
     </script>

</body>

</html>
