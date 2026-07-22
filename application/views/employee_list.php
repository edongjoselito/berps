<!DOCTYPE html>
<html lang="en">

<?php include('includes/head.php'); ?>

<?php
$payrollEmployees = isset($payrollEmployees) && is_array($payrollEmployees) ? $payrollEmployees : array();
$payrollEmployeeMap = array();
$configuredPayrollCount = 0;

foreach ($payrollEmployees as $payrollEmployee) {
     $empKey = (string) ($payrollEmployee->empID ?? '');
     if ($empKey !== '') {
          $payrollEmployeeMap[$empKey] = $payrollEmployee;
     }

     if ((int) ($payrollEmployee->profileID ?? 0) > 0 && (float) ($payrollEmployee->monthlySalary ?? 0) > 0) {
          $configuredPayrollCount++;
     }
}
?>

<body>

     <div id="wrapper">

          <?php include('includes/top-nav-bar.php'); ?>
          <?php include('includes/sidebar.php'); ?>

          <div class="content-page">
               <div class="content">
                    <div class="container-fluid employee-list-page berps-page">


                         <?php if ($msg = $this->session->flashdata('msg')): ?>
                              <?= $msg; ?>
                         <?php endif; ?>
                         <?php if ($success = $this->session->flashdata('success')): ?>
                              <div class="alert alert-success alert-dismissible fade show" role="alert">
                                   <?= htmlspecialchars((string) $success, ENT_QUOTES, 'UTF-8'); ?>
                                   <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                              </div>
                         <?php endif; ?>
                         <?php if ($danger = $this->session->flashdata('danger')): ?>
                              <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                   <?= htmlspecialchars((string) $danger, ENT_QUOTES, 'UTF-8'); ?>
                                   <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                              </div>
                         <?php endif; ?>


                         <?php
                         $employeeCount = is_array($data) ? count($data) : 0;
                         $currentFilter = isset($statusFilter) ? $statusFilter : 'Active';
                         ?>

                         <header class="berps-page-header">
                              <div class="berps-page-header__content">
                                   <span class="berps-page-header__eyebrow">People operations</span>
                                   <h1 class="berps-page-title">Employee Directory</h1>
                                   <p class="berps-page-subtitle">Manage employee profiles, employment records, documents, and payroll setup.</p>
                              </div>
                              <div class="berps-page-header__actions">
                                   <a href="<?= base_url(); ?>Page/payrollModule" class="btn btn-outline-primary">
                                        <i class="mdi mdi-cash-multiple mr-1" aria-hidden="true"></i>Payroll module
                                   </a>
                                   <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#newEmployeeModal">
                                        <i class="mdi mdi-account-plus-outline mr-1" aria-hidden="true"></i>Add employee
                                   </button>
                              </div>
                         </header>

                         <nav class="berps-section-card mb-4" aria-label="Employee information sections">
                              <div class="berps-section-card__header">
                                   <div>
                                        <h2 class="berps-section-title">Employee information</h2>
                                        <p class="berps-section-copy">Open related records without leaving the people module.</p>
                                   </div>
                              </div>
                              <div class="berps-section-card__body">
                                   <div class="berps-section-nav">
                                        <a href="<?= base_url(); ?>Page/employeeList?status=<?= urlencode($currentFilter); ?>" class="btn btn-primary btn-sm" aria-current="page"><i class="mdi mdi-account-group mr-1" aria-hidden="true"></i>Employees</a>
                                        <a href="<?= base_url(); ?>Page/employmentHistory" class="btn btn-outline-primary btn-sm"><i class="mdi mdi-briefcase mr-1" aria-hidden="true"></i>Employment history</a>
                                        <a href="<?= base_url(); ?>Page/employeeEducation" class="btn btn-outline-primary btn-sm"><i class="mdi mdi-school mr-1" aria-hidden="true"></i>Education</a>
                                        <a href="<?= base_url(); ?>Page/employeeSkills" class="btn btn-outline-primary btn-sm"><i class="mdi mdi-certificate mr-1" aria-hidden="true"></i>Skills</a>
                                        <a href="<?= base_url(); ?>Page/employeeEmergencyContacts" class="btn btn-outline-primary btn-sm"><i class="mdi mdi-phone mr-1" aria-hidden="true"></i>Emergency contacts</a>
                                        <a href="<?= base_url(); ?>Page/employeeDocuments" class="btn btn-outline-primary btn-sm"><i class="mdi mdi-file-document mr-1" aria-hidden="true"></i>Documents</a>
                                   </div>
                              </div>
                         </nav>

                         <section aria-labelledby="employee-summary-heading">
                              <h2 id="employee-summary-heading" class="sr-only">Employee summary</h2>
                              <div class="berps-stat-grid">
                                   <div class="berps-stat-card">
                                        <div><p class="berps-stat-card__value"><?= number_format($employeeCount); ?></p><p class="berps-stat-card__label">Employees in this view</p></div>
                                        <span class="berps-stat-card__icon" aria-hidden="true"><i class="mdi mdi-account-group-outline"></i></span>
                                   </div>
                                   <div class="berps-stat-card berps-tone-success">
                                        <div><p class="berps-stat-card__value"><?= number_format($configuredPayrollCount); ?></p><p class="berps-stat-card__label">Configured payroll profiles</p></div>
                                        <span class="berps-stat-card__icon" aria-hidden="true"><i class="mdi mdi-cash-multiple"></i></span>
                                   </div>
                              </div>
                         </section>

                         <section class="berps-table-card" aria-labelledby="employee-table-heading">
                              <div class="berps-table-card__header">
                                   <div>
                                        <h2 id="employee-table-heading" class="berps-section-title">Employee records</h2>
                                        <p class="berps-section-copy">Search, review, and maintain the people included in this directory.</p>
                                   </div>
                                   <div class="berps-toolbar__field">
                                        <label for="status_filter" class="control-label mb-1">Employment status</label>
                                        <select class="form-control form-control-sm" id="status_filter" name="status_filter" onchange="filterByStatus()">
                                             <option value="all" <?= $currentFilter === 'all' ? 'selected' : ''; ?>>All statuses</option>
                                             <option value="Active" <?= $currentFilter === 'Active' ? 'selected' : ''; ?>>Active</option>
                                             <option value="Terminated" <?= $currentFilter === 'Terminated' ? 'selected' : ''; ?>>Terminated</option>
                                             <option value="On Leave" <?= $currentFilter === 'On Leave' ? 'selected' : ''; ?>>On leave</option>
                                             <option value="Resigned" <?= $currentFilter === 'Resigned' ? 'selected' : ''; ?>>Resigned</option>
                                             <option value="Suspended" <?= $currentFilter === 'Suspended' ? 'selected' : ''; ?>>Suspended</option>
                                        </select>
                                   </div>
                              </div>
                              <div class="table-responsive">
                                   <table id="employee-table" class="table table-hover w-100">
                                                       <thead>
                                                            <tr>
                                                                 <th>Emp. ID</th>
                                                                 <th>Employee Name</th>
                                                                 <th>Date Hired</th>
                                                                 <th>Position</th>
                                                                 <th>Department</th>
                                                                 <th>Monthly Salary</th>
                                                                 <th>Birth Date</th>
                                                                 <th class="text-right">Actions</th>
                                                            </tr>
                                                       </thead>
                                                       <tbody>
                                                            <?php if (!empty($data)): ?>
                                                                 <?php foreach ($data as $row): ?>
                                                                      <?php
                                                                      $empId = isset($row->empID) ? htmlspecialchars($row->empID, ENT_QUOTES, 'UTF-8') : '';
                                                                      $employeeName = htmlspecialchars(trim($row->lName . ', ' . $row->fName), ENT_QUOTES, 'UTF-8');
                                                                      $dateHired = !empty($row->dateHired) ? htmlspecialchars($row->dateHired, ENT_QUOTES, 'UTF-8') : '-';
                                                                      $position = isset($row->empPosition) ? htmlspecialchars($row->empPosition, ENT_QUOTES, 'UTF-8') : '-';
                                                                      $department = isset($row->department) ? htmlspecialchars($row->department, ENT_QUOTES, 'UTF-8') : '-';
                                                                      $payrollProfile = isset($payrollEmployeeMap[(string) $row->empID]) ? $payrollEmployeeMap[(string) $row->empID] : null;
                                                                      $monthlySalary = ($payrollProfile && (float) ($payrollProfile->monthlySalary ?? 0) > 0)
                                                                           ? 'PHP ' . number_format((float) ($payrollProfile->monthlySalary ?? 0), 2)
                                                                           : '<span class="text-muted">Not set</span>';
                                                                      $birthDate = !empty($row->bDate) ? htmlspecialchars($row->bDate, ENT_QUOTES, 'UTF-8') : '-';
                                                                      $profileUrl = base_url('Page/empProfile?id=' . urlencode((string) $row->empID));
                                                                      $currentFilter = isset($statusFilter) ? $statusFilter : 'Active';
                                                                      $editUrl = base_url('Page/updateEmployee?id=' . urlencode((string) $row->empID) . '&status=' . urlencode($currentFilter));
                                                                      $deleteUrl = base_url('Page/deleteEmployee?id=' . urlencode((string) $row->empID) . '&status=' . urlencode($currentFilter));
                                                                      ?>
                                                                      <tr>
                                                                           <td><?= $empId; ?></td>
                                                                           <td><?= $employeeName; ?></td>
                                                                           <td><?= $dateHired; ?></td>
                                                                           <td><?= $position; ?></td>
                                                                           <td><?= $department; ?></td>
                                                                           <td><?= $monthlySalary; ?></td>
                                                                           <td><?= $birthDate; ?></td>
                                                                           <td class="text-right">
                                                                                <div class="berps-row-actions">
                                                                                     <a href="<?= $profileUrl; ?>" class="berps-icon-action" title="View profile" aria-label="View profile">
                                                                                          <i class="fa-solid fa-id-card" aria-hidden="true"></i>
                                                                                     </a>
                                                                                     <a href="<?= $editUrl; ?>" class="berps-icon-action" title="Edit employee" aria-label="Edit employee">
                                                                                          <i class="mdi mdi-square-edit-outline" aria-hidden="true"></i>
                                                                                     </a>
                                                                                     <a href="<?= $deleteUrl; ?>" class="berps-icon-action berps-icon-action--danger" title="Delete employee" aria-label="Delete employee" onclick="return confirm('Are you sure you want to delete this employee?');">
                                                                                          <i class="mdi mdi-delete-outline" aria-hidden="true"></i>
                                                                                     </a>
                                                                                </div>
                                                                           </td>
                                                                      </tr>
                                                                 <?php endforeach; ?>
                                                            <?php else: ?>
                                                                 <tr>
                                                                      <td colspan="8">
                                                                           <div class="berps-empty-state">
                                                                                <i class="mdi mdi-account-search-outline berps-empty-state__icon" aria-hidden="true"></i>
                                                                                <span>No employee records found for this status.</span>
                                                                           </div>
                                                                      </td>
                                                                 </tr>
                                                            <?php endif; ?>
                                                       </tbody>
                                   </table>
                              </div>
                         </section>
                    </div>

               </div>
               <?php include('includes/footer.php'); ?>
          </div>
     </div>

     <?php include('includes/themecustomizer.php'); ?>

     <!-- New Employee Modal -->
     <div class="modal fade" id="newEmployeeModal" tabindex="-1" role="dialog" aria-hidden="true">
          <div class="modal-dialog modal-lg modal-dialog-centered">
               <div class="modal-content">
                    <div class="modal-header">
                         <h4 class="modal-title mb-0">Add New Employee</h4>
                         <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                              <span aria-hidden="true">&times;</span>
                         </button>
                    </div>
                    <form class="needs-validation" method="post" novalidate>
                         <div class="modal-body">
                              <div class="form-row">
                                   <div class="form-group col-md-3">
                                        <label for="employee_id">Employee ID</label>
                                        <input type="text"
                                             class="form-control"
                                             id="employee_id"
                                             name="empID"
                                             value="<?= isset($nextEmployeeId) ? htmlspecialchars($nextEmployeeId, ENT_QUOTES, 'UTF-8') : htmlspecialchars($this->session->userdata('settingsID') . date('Y') . '0001', ENT_QUOTES, 'UTF-8'); ?>"
                                             readonly
                                             required>
                                   </div>
                                   <div class="form-group col-md-3">
                                        <label for="first_name">First Name</label>
                                        <input type="text" class="form-control" id="first_name" name="fName" required>
                                   </div>
                                   <div class="form-group col-md-3">
                                        <label for="middle_name">Middle Name</label>
                                        <input type="text" class="form-control" id="middle_name" name="mName">
                                   </div>
                                   <div class="form-group col-md-3">
                                        <label for="last_name">Last Name</label>
                                        <input type="text" class="form-control" id="last_name" name="lName" required>
                                   </div>
                              </div>
                              <div class="form-row">
                                   <div class="form-group col-md-3">
                                        <label for="birth_date">Birth Date</label>
                                        <input type="date" class="form-control" id="birth_date" name="bDate">
                                   </div>
                                   <div class="form-group col-md-3">
                                        <label for="position">Position</label>
                                        <input type="text" class="form-control" id="position" name="empPosition" required>
                                   </div>
                                   <div class="form-group col-md-3">
                                        <label for="email">Email</label>
                                        <input type="email" class="form-control" id="email" name="email" required>
                                   </div>
                                   <div class="form-group col-md-3">
                                        <label for="date_hired">Date Hired</label>
                                        <input type="date" class="form-control" id="date_hired" name="dateHired">
                                   </div>
                              </div>
                              <div class="form-row">
                                   <div class="form-group col-md-3">
                                        <label for="department">Department</label>
                                        <select class="form-control" id="department" name="department" required>
                                             <option value="">Select department</option>
                                             <option value="General">General</option>
                                             <option value="Billing">Billing</option>
                                             <option value="Technical">Technical</option>
                                             <option value="Sales">Sales</option>
                                        </select>
                                   </div>
                              </div>
                              <hr>
                              <h5 class="mb-3">Support Chat Permissions</h5>
                              <div class="form-row">
                                   <div class="form-group col-md-3">
                                        <div class="custom-control custom-checkbox mt-4">
                                             <input type="checkbox" class="custom-control-input" id="support_chat_view" name="support_chat_view" value="1" checked>
                                             <label class="custom-control-label" for="support_chat_view">Can view chats</label>
                                        </div>
                                   </div>
                                   <div class="form-group col-md-3">
                                        <div class="custom-control custom-checkbox mt-4">
                                             <input type="checkbox" class="custom-control-input" id="support_chat_reply" name="support_chat_reply" value="1" checked>
                                             <label class="custom-control-label" for="support_chat_reply">Can reply to chats</label>
                                        </div>
                                   </div>
                              </div>
                              <hr>
                              <h5 class="mb-3">Payroll Defaults</h5>
                              <div class="form-row">
                                   <div class="form-group col-md-3">
                                        <label for="monthly_salary">Monthly Salary</label>
                                        <input type="number" step="0.01" min="0" class="form-control" id="monthly_salary" name="monthlySalary" placeholder="0.00">
                                   </div>
                                   <div class="form-group col-md-3">
                                        <label for="philhealth_amount">PhilHealth</label>
                                        <input type="number" step="0.01" min="0" class="form-control" id="philhealth_amount" name="philhealthAmount" placeholder="0.00">
                                   </div>
                                   <div class="form-group col-md-3">
                                        <label for="sss_amount">SSS</label>
                                        <input type="number" step="0.01" min="0" class="form-control" id="sss_amount" name="sssAmount" placeholder="0.00">
                                   </div>
                                   <div class="form-group col-md-3">
                                        <label for="pagibig_amount">Pag-IBIG</label>
                                        <input type="number" step="0.01" min="0" class="form-control" id="pagibig_amount" name="pagibigAmount" placeholder="0.00">
                                   </div>
                              </div>
                              <div class="form-row">
                                   <div class="form-group col-md-12">
                                        <label for="payroll_notes">Payroll Notes</label>
                                        <textarea class="form-control" id="payroll_notes" name="payrollNotes" rows="2" placeholder="Optional payroll remarks or setup notes"></textarea>
                                   </div>
                              </div>
                              <input type="hidden" name="payrollStatus" value="active">
                         </div>
                         <div class="modal-footer">
                              <button type="submit" name="addemployee" value="1" class="btn btn-primary">Add Employee</button>
                              <button type="reset" name="resettask" class="btn btn-warning text-white">Reset</button>
                              <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                         </div>
                    </form>
               </div>
          </div>
     </div>

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
          function filterByStatus() {
               var statusFilter = document.getElementById('status_filter').value;
               window.location.href = '<?= base_url(); ?>Page/employeeList?status=' + encodeURIComponent(statusFilter);
          }

          (function($) {
               'use strict';

               $(function() {
                    var $employeeTable = $('#employee-table');

                    if ($employeeTable.length) {
                         $employeeTable.DataTable({
                              responsive: true,
                              autoWidth: false,
                              order: [
                                   [1, 'asc']
                              ],
                              language: {
                                   emptyTable: 'No employee records found.'
                              },
                              columnDefs: [{
                                   targets: -1,
                                   orderable: false,
                                   searchable: false
                              }]
                         });
                    }

                    $('#newEmployeeModal').on('hidden.bs.modal', function() {
                         var form = $(this).find('form')[0];
                         if (form) {
                              form.reset();
                         }
                    });
               });
          })(jQuery);
     </script>

</body>

</html>
