<!DOCTYPE html>
<html lang="en">

<?php include('includes/head.php'); ?>

<body>

     <div id="wrapper">

          <?php include('includes/top-nav-bar.php'); ?>
          <?php include('includes/sidebar.php'); ?>

          <div class="content-page">
               <div class="content">
                    <div class="container-fluid update-employee-page berps-form-page berps-page">

                         <?php
                         $employee = isset($data[0]) ? $data[0] : null;
                         $payrollProfile = isset($payrollProfile) ? $payrollProfile : null;
                         $supportChatView = isset($supportChatView) ? (int) $supportChatView : 1;
                         $supportChatReply = isset($supportChatReply) ? (int) $supportChatReply : 1;
                         $statusHistory = isset($statusHistory) ? $statusHistory : array();
                         ?>

                         <header class="berps-page-header">
                              <div class="berps-page-header__content">
                                   <span class="berps-page-header__eyebrow">People operations</span>
                                   <h1 class="berps-page-title">Update Employee</h1>
                                   <p class="berps-page-subtitle">Maintain employee, access, status, and payroll information in one workspace.</p>
                              </div>
                              <div class="berps-page-header__actions">
                                   <a href="<?= base_url(); ?>Page/employeeList" class="btn btn-outline-secondary">
                                        <i class="mdi mdi-arrow-left mr-1" aria-hidden="true"></i>Back to Employee List
                                   </a>
                              </div>
                         </header>

                         <div class="row">
                              <div class="col-12">
                                   <div class="berps-form-card">
                                        <div class="berps-form-card__header">
                                             <div>
                                                  <h2 class="berps-form-card__title">Employee details</h2>
                                                  <p class="berps-form-card__copy">Required employment and account fields must remain complete.</p>
                                             </div>
                                        </div>
                                        <div class="berps-form-card__body">
                                             <?php if ($this->session->flashdata('success')): ?>
                                                  <div class="alert alert-success"><?= htmlspecialchars((string) $this->session->flashdata('success'), ENT_QUOTES, 'UTF-8'); ?></div>
                                             <?php endif; ?>
                                             <?php if ($this->session->flashdata('danger')): ?>
                                                  <div class="alert alert-danger"><?= htmlspecialchars((string) $this->session->flashdata('danger'), ENT_QUOTES, 'UTF-8'); ?></div>
                                             <?php endif; ?>
                                             <?php if ($employee): ?>
                                                  <form class="needs-validation" method="post" novalidate>
                                                       <h5 class="form-section-title">Basic Information</h5>
                                                       <div class="form-row">
                                                            <div class="form-group col-md-3">
                                                                 <label for="emp_id">Employee ID</label>
                                                                 <input type="text"
                                                                      class="form-control"
                                                                      id="emp_id"
                                                                      name="empID"
                                                                      value="<?= htmlspecialchars((string) $employee->empID, ENT_QUOTES, 'UTF-8'); ?>"
                                                                      readonly
                                                                      required>
                                                            </div>
                                                            <div class="form-group col-md-3">
                                                                 <label for="first_name">First Name</label>
                                                                 <input type="text"
                                                                      class="form-control"
                                                                      id="first_name"
                                                                      name="fName"
                                                                      value="<?= htmlspecialchars((string) $employee->fName, ENT_QUOTES, 'UTF-8'); ?>"
                                                                      required>
                                                            </div>
                                                            <div class="form-group col-md-3">
                                                                 <label for="middle_name">Middle Name</label>
                                                                 <input type="text"
                                                                      class="form-control"
                                                                      id="middle_name"
                                                                      name="mName"
                                                                      value="<?= htmlspecialchars((string) $employee->mName, ENT_QUOTES, 'UTF-8'); ?>">
                                                            </div>
                                                            <div class="form-group col-md-3">
                                                                 <label for="last_name">Last Name</label>
                                                                 <input type="text"
                                                                      class="form-control"
                                                                      id="last_name"
                                                                      name="lName"
                                                                      value="<?= htmlspecialchars((string) $employee->lName, ENT_QUOTES, 'UTF-8'); ?>"
                                                                      required>
                                                            </div>
                                                       </div>

                                                       <h5 class="form-section-title">Employment Details</h5>
                                                       <div class="form-row">
                                                            <div class="form-group col-md-3">
                                                                 <label for="birth_date">Birth Date</label>
                                                                 <input type="date"
                                                                      class="form-control"
                                                                      id="birth_date"
                                                                      name="bDate"
                                                                      value="<?= htmlspecialchars((string) $employee->bDate, ENT_QUOTES, 'UTF-8'); ?>">
                                                            </div>
                                                            <div class="form-group col-md-3">
                                                                 <label for="position">Position</label>
                                                                 <input type="text"
                                                                      class="form-control"
                                                                      id="position"
                                                                      name="empPosition"
                                                                      value="<?= htmlspecialchars((string) $employee->empPosition, ENT_QUOTES, 'UTF-8'); ?>"
                                                                      required>
                                                            </div>
                                                            <div class="form-group col-md-3">
                                                                 <label for="email">Email</label>
                                                                 <input type="email"
                                                                      class="form-control"
                                                                      id="email"
                                                                      name="email"
                                                                      value="<?= htmlspecialchars((string) ($employee->email ?? ''), ENT_QUOTES, 'UTF-8'); ?>"
                                                                      required>
                                                            </div>
                                                            <div class="form-group col-md-3">
                                                                 <label for="date_hired">Date Hired</label>
                                                                 <input type="date"
                                                                      class="form-control"
                                                                      id="date_hired"
                                                                      name="dateHired"
                                                                      value="<?= htmlspecialchars((string) $employee->dateHired, ENT_QUOTES, 'UTF-8'); ?>">
                                                            </div>
                                                       </div>
                                                       <div class="form-row">
                                                            <div class="form-group col-md-3">
                                                                 <label for="department">Department</label>
                                                                 <select
                                                                      class="form-control"
                                                                      id="department"
                                                                      name="department"
                                                                      required>
                                                                      <?php $currentDepartment = trim((string) $employee->department); ?>
                                                                      <option value="">Select department</option>
                                                                      <option value="General" <?= strcasecmp($currentDepartment, 'General') === 0 ? 'selected' : ''; ?>>General</option>
                                                                      <option value="Billing" <?= strcasecmp($currentDepartment, 'Billing') === 0 ? 'selected' : ''; ?>>Billing</option>
                                                                      <option value="Technical" <?= strcasecmp($currentDepartment, 'Technical') === 0 ? 'selected' : ''; ?>>Technical</option>
                                                                      <option value="Sales" <?= strcasecmp($currentDepartment, 'Sales') === 0 ? 'selected' : ''; ?>>Sales</option>
                                                                 </select>
                                                            </div>
                                                            <div class="form-group col-md-3">
                                                                 <label for="employee_status">Employee Status</label>
                                                                 <select
                                                                      class="form-control"
                                                                      id="employee_status"
                                                                      name="empStat"
                                                                      required>
                                                                      <?php $currentStatus = trim((string) ($employee->empStat ?? 'Active')); ?>
                                                                      <option value="Active" <?= strcasecmp($currentStatus, 'Active') === 0 ? 'selected' : ''; ?>>Active</option>
                                                                      <option value="Terminated" <?= strcasecmp($currentStatus, 'Terminated') === 0 ? 'selected' : ''; ?>>Terminated</option>
                                                                      <option value="On Leave" <?= strcasecmp($currentStatus, 'On Leave') === 0 ? 'selected' : ''; ?>>On Leave</option>
                                                                      <option value="Resigned" <?= strcasecmp($currentStatus, 'Resigned') === 0 ? 'selected' : ''; ?>>Resigned</option>
                                                                      <option value="Suspended" <?= strcasecmp($currentStatus, 'Suspended') === 0 ? 'selected' : ''; ?>>Suspended</option>
                                                                 </select>
                                                            </div>
                                                       </div>
                                                       <div class="form-row">
                                                            <div class="form-group col-md-6">
                                                                 <label for="status_change_reason">Status Change Reason (if changing status)</label>
                                                                 <textarea
                                                                      class="form-control"
                                                                      id="status_change_reason"
                                                                      name="statusChangeReason"
                                                                      rows="2"
                                                                      placeholder="Provide reason for status change..."></textarea>
                                                            </div>
                                                       </div>

                                                       <h5 class="form-section-title">Support Chat Permissions</h5>
                                                       <div class="form-row">
                                                            <div class="form-group col-md-3">
                                                                 <div class="custom-control custom-checkbox mt-4">
                                                                      <input type="checkbox" class="custom-control-input" id="support_chat_view" name="support_chat_view" value="1" <?= $supportChatView === 1 ? 'checked' : ''; ?>>
                                                                      <label class="custom-control-label" for="support_chat_view">Can view chats</label>
                                                                 </div>
                                                            </div>
                                                            <div class="form-group col-md-3">
                                                                 <div class="custom-control custom-checkbox mt-4">
                                                                      <input type="checkbox" class="custom-control-input" id="support_chat_reply" name="support_chat_reply" value="1" <?= $supportChatReply === 1 ? 'checked' : ''; ?>>
                                                                      <label class="custom-control-label" for="support_chat_reply">Can reply to chats</label>
                                                                 </div>
                                                            </div>
                                                       </div>

                                                       <h5 class="form-section-title">Payroll Defaults</h5>
                                                       <div class="form-row">
                                                            <div class="form-group col-md-3">
                                                                 <label for="monthly_salary">Monthly Salary</label>
                                                                 <input type="number"
                                                                      class="form-control"
                                                                      id="monthly_salary"
                                                                      name="monthlySalary"
                                                                      min="0"
                                                                      step="0.01"
                                                                      value="<?= htmlspecialchars(number_format((float) ($payrollProfile->monthlySalary ?? 0), 2, '.', ''), ENT_QUOTES, 'UTF-8'); ?>">
                                                            </div>
                                                            <div class="form-group col-md-3">
                                                                 <label for="philhealth_amount">PhilHealth</label>
                                                                 <input type="number"
                                                                      class="form-control"
                                                                      id="philhealth_amount"
                                                                      name="philhealthAmount"
                                                                      min="0"
                                                                      step="0.01"
                                                                      value="<?= htmlspecialchars(number_format((float) ($payrollProfile->philhealthAmount ?? 0), 2, '.', ''), ENT_QUOTES, 'UTF-8'); ?>">
                                                            </div>
                                                            <div class="form-group col-md-3">
                                                                 <label for="sss_amount">SSS</label>
                                                                 <input type="number"
                                                                      class="form-control"
                                                                      id="sss_amount"
                                                                      name="sssAmount"
                                                                      min="0"
                                                                      step="0.01"
                                                                      value="<?= htmlspecialchars(number_format((float) ($payrollProfile->sssAmount ?? 0), 2, '.', ''), ENT_QUOTES, 'UTF-8'); ?>">
                                                            </div>
                                                            <div class="form-group col-md-3">
                                                                 <label for="pagibig_amount">Pag-IBIG</label>
                                                                 <input type="number"
                                                                      class="form-control"
                                                                      id="pagibig_amount"
                                                                      name="pagibigAmount"
                                                                      min="0"
                                                                      step="0.01"
                                                                      value="<?= htmlspecialchars(number_format((float) ($payrollProfile->pagibigAmount ?? 0), 2, '.', ''), ENT_QUOTES, 'UTF-8'); ?>">
                                                            </div>
                                                       </div>

                                                       <div class="form-row">
                                                            <div class="form-group col-md-12">
                                                                 <label for="payroll_notes">Payroll Notes</label>
                                                                 <textarea
                                                                      class="form-control"
                                                                      id="payroll_notes"
                                                                      name="payrollNotes"
                                                                      rows="2"
                                                                      placeholder="Optional payroll setup notes"><?= htmlspecialchars((string) ($payrollProfile->notes ?? ''), ENT_QUOTES, 'UTF-8'); ?></textarea>
                                                            </div>
                                                       </div>
                                                       <input type="hidden" name="payrollStatus" value="<?= htmlspecialchars((string) ($payrollProfile->payrollStatus ?? 'active'), ENT_QUOTES, 'UTF-8'); ?>">

                                                       <div class="berps-form-actions">
                                                            <a href="<?= base_url(); ?>Page/employeeList" class="btn btn-outline-secondary">Cancel</a>
                                                            <button type="reset" name="resettask" class="btn btn-outline-secondary">
                                                                 <i class="mdi mdi-refresh mr-1" aria-hidden="true"></i>Reset
                                                            </button>
                                                            <button type="submit" name="updateemployee" value="1" class="btn btn-primary">
                                                                 <i class="mdi mdi-content-save-outline mr-1" aria-hidden="true"></i>Update Employee
                                                            </button>
                                                       </div>
                                                  </form>
                                             <?php else: ?>
                                                  <div class="alert alert-warning mb-0" role="alert">
                                                       The employee information could not be loaded. Please go back to the <a href="<?= base_url(); ?>Page/employeeList" class="alert-link">employee list</a> and try again.
                                                  </div>
                                             <?php endif; ?>
                                        </div>
                                   </div>
                              </div>
                         </div>

                         <?php if (!empty($statusHistory)): ?>
                         <div class="row mt-4">
                              <div class="col-12">
                                   <div class="berps-section-card">
                                        <div class="berps-section-card__header">
                                             <div>
                                                  <h2 class="berps-section-title">Employee status history</h2>
                                                  <p class="berps-section-copy">A record of status changes and their reasons.</p>
                                             </div>
                                        </div>
                                        <div class="berps-section-card__body">
                                             <div class="table-responsive">
                                                  <table class="table table-bordered table-hover">
                                                       <thead>
                                                            <tr>
                                                                 <th>Date</th>
                                                                 <th>Old Status</th>
                                                                 <th>New Status</th>
                                                                 <th>Changed By</th>
                                                                 <th>Reason</th>
                                                            </tr>
                                                       </thead>
                                                       <tbody>
                                                            <?php foreach ($statusHistory as $history): ?>
                                                            <tr>
                                                                 <td><?= htmlspecialchars(date('M d, Y g:i A', strtotime($history->change_date)), ENT_QUOTES, 'UTF-8'); ?></td>
                                                                 <td>
                                                                      <?php if ($history->old_status): ?>
                                                                           <span class="badge badge-secondary"><?= htmlspecialchars($history->old_status, ENT_QUOTES, 'UTF-8'); ?></span>
                                                                      <?php else: ?>
                                                                           <em class="text-muted">N/A</em>
                                                                      <?php endif; ?>
                                                                 </td>
                                                                 <td>
                                                                      <?php
                                                                      $badgeClass = 'badge-primary';
                                                                      if (strcasecmp($history->new_status, 'Active') === 0) $badgeClass = 'badge-success';
                                                                      elseif (strcasecmp($history->new_status, 'Terminated') === 0) $badgeClass = 'badge-danger';
                                                                      elseif (strcasecmp($history->new_status, 'On Leave') === 0) $badgeClass = 'badge-warning';
                                                                      elseif (strcasecmp($history->new_status, 'Resigned') === 0) $badgeClass = 'badge-secondary';
                                                                      elseif (strcasecmp($history->new_status, 'Suspended') === 0) $badgeClass = 'badge-dark';
                                                                      ?>
                                                                      <span class="badge <?= $badgeClass; ?>"><?= htmlspecialchars($history->new_status, ENT_QUOTES, 'UTF-8'); ?></span>
                                                                 </td>
                                                                 <td><?= htmlspecialchars($history->changed_by_username, ENT_QUOTES, 'UTF-8'); ?></td>
                                                                 <td><?= $history->change_reason ? htmlspecialchars($history->change_reason, ENT_QUOTES, 'UTF-8') : '<em class="text-muted">No reason provided</em>'; ?></td>
                                                            </tr>
                                                            <?php endforeach; ?>
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

</body>

</html>
