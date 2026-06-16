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
                    <div class="container-fluid employee-list-page">
                         <style>
                              :root {
                                   --fixed-footer-h: 90px;
                                   /* adjust if your fixed footer is taller/shorter */
                              }

                              /* Since footer is fixed, always give the content extra bottom space */
                              .content-page .content {
                                   padding-bottom: calc(var(--fixed-footer-h) + 24px) !important;
                              }

                              .employee-list-page {
                                   padding-top: 14px;
                                   padding-bottom: calc(var(--fixed-footer-h) + 24px);
                              }

                              .employee-list-page .breadcrumb {
                                   background: transparent;
                                   padding: 0;
                                   margin-bottom: 1.5rem;
                              }

                              .employee-list-page .page-header-card {
                                   border: none;
                                   border-radius: 18px;
                                   box-shadow: 0 16px 36px rgba(15, 23, 42, 0.08);
                                   overflow: hidden;
                              }

                              .employee-list-page .page-header-card .card-header {
                                   background: linear-gradient(130deg, #4c6ef5, #15aabf);
                                   color: #fff;
                                   border-bottom: none;
                                   padding: 28px 32px;
                              }

                              .employee-list-page .page-header-card .card-header h3 {
                                   margin: 0;
                                   font-size: 1.6rem;
                                   font-weight: 600;
                              }

                              .employee-list-page .page-header-card .card-body {
                                   padding: 26px 32px;
                              }

                              .employee-list-page .page-header-card,
                              .employee-list-page .employee-table-card {
                                   margin-bottom: 24px;
                              }

                              .employee-list-page .page-header-card .card-actions {
                                   display: flex;
                                   align-items: center;
                                   gap: 12px;
                              }

                              .employee-list-page .page-header-card .btn-add-employee {
                                   background: rgba(255, 255, 255, 0.92);
                                   color: #0b7285;
                                   font-weight: 600;
                                   border: none;
                                   border-radius: 999px;
                                   padding: 10px 22px;
                                   display: inline-flex;
                                   align-items: center;
                                   gap: 8px;
                                   transition: transform 0.15s ease, box-shadow 0.15s ease;
                              }

                              .employee-list-page .page-header-card .btn-add-employee:hover {
                                   transform: translateY(-2px);
                                   box-shadow: 0 10px 24px rgba(15, 23, 42, 0.16);
                              }

                              .employee-list-page .employee-table-card {
                                   border: none;
                                   border-radius: 18px;
                                   box-shadow: 0 14px 32px rgba(15, 23, 42, 0.07);
                                   margin-bottom: 18px;
                                   /* extra gap above footer */
                              }

                              .employee-list-page .employee-table-card .card-body {
                                   padding: 28px 32px;
                              }

                              .employee-list-page .employee-table-card .table thead th {
                                   text-transform: uppercase;
                                   letter-spacing: 0.04em;
                                   font-size: 0.78rem;
                                   white-space: nowrap;
                              }

                              .employee-list-page .employee-table-card .table td {
                                   vertical-align: middle;
                              }

                              .employee-list-page .action-column {
                                   text-align: center;
                              }

                              .employee-list-page .action-icons {
                                   display: inline-flex;
                                   gap: 8px;
                              }

                              .employee-list-page .action-icon {
                                   position: relative;
                                   display: inline-flex;
                                   align-items: center;
                                   justify-content: center;
                                   width: 38px;
                                   height: 38px;
                                   border-radius: 50%;
                                   text-decoration: none;
                                   font-size: 1rem;
                                   transition: transform 0.15s ease, box-shadow 0.15s ease;
                              }

                              .employee-list-page .action-icon::after {
                                   content: attr(data-label);
                                   position: absolute;
                                   bottom: -32px;
                                   left: 50%;
                                   transform: translate(-50%, 6px);
                                   background: rgba(15, 23, 42, 0.9);
                                   color: #fff;
                                   padding: 4px 10px;
                                   border-radius: 6px;
                                   font-size: 11px;
                                   white-space: nowrap;
                                   opacity: 0;
                                   pointer-events: none;
                                   transition: opacity 0.15s ease, transform 0.15s ease;
                              }

                              .employee-list-page .action-icon:hover::after,
                              .employee-list-page .action-icon:focus::after {
                                   opacity: 1;
                                   transform: translate(-50%, 0);
                              }

                              .employee-list-page .action-icon:hover,
                              .employee-list-page .action-icon:focus {
                                   transform: translateY(-2px);
                                   box-shadow: 0 10px 20px rgba(15, 23, 42, 0.12);
                              }

                              .employee-list-page .action-icon i {
                                   color: inherit;
                              }

                              .employee-list-page .action-view {
                                   background: rgba(59, 130, 246, 0.14);
                                   color: #2563eb;
                              }

                              .employee-list-page .action-edit {
                                   background: rgba(14, 165, 233, 0.14);
                                   color: #0891b2;
                              }

                              .employee-list-page .action-delete {
                                   background: rgba(239, 68, 68, 0.14);
                                   color: #dc2626;
                              }

                              /* Avoid table hugging the fixed footer (DataTables renders bottom controls) */
                              .employee-list-page .dataTables_wrapper {
                                   margin-bottom: 16px;
                                   padding-bottom: 16px;
                              }

                              .employee-list-page .dataTables_wrapper .row:last-child {
                                   margin-bottom: 12px;
                              }

                              /* Keep responsive table usable */
                              .employee-list-page .table-responsive {
                                   overflow-x: auto;
                                   /* was hidden; allow scroll if needed */
                              }

                              .employee-list-page .modal .modal-header {
                                   background: linear-gradient(135deg, #4c6ef5, #15aabf);
                                   color: #fff;
                              }

                              .employee-list-page .modal .modal-title {
                                   font-weight: 600;
                              }

                              .employee-list-page .modal .form-group label {
                                   font-weight: 600;
                                   color: #495057;
                              }

                              @media (max-width: 767.98px) {
                                   :root {
                                        --fixed-footer-h: 100px;
                                        /* mobile footer can wrap */
                                   }

                                   .employee-list-page .page-header-card .card-header,
                                   .employee-list-page .page-header-card .card-body,
                                   .employee-list-page .employee-table-card .card-body {
                                        padding: 20px;
                                   }

                                   .employee-list-page .action-icon::after {
                                        bottom: -28px;
                                   }

                                   .employee-list-page .dataTables_wrapper {
                                        margin-bottom: 22px;
                                        padding-bottom: 22px;
                                   }
                              }
                         </style>


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


                         <div class="row">
                              <div class="col-12">
                                   <div class="card page-header-card mb-4">
                                        <div class="card-header d-flex flex-wrap justify-content-between align-items-center">
                                             <div>
                                                  <h3 class="text-white mb-1">Employee Directory</h3>
                                             </div>
                                             <div class="card-actions mt-3 mt-md-0">
                                                  <a href="<?= base_url(); ?>Page/payrollModule" class="btn btn-add-employee">
                                                       <i class="mdi mdi-cash-multiple"></i>
                                                       Payroll Module
                                                  </a>
                                                  <button type="button" class="btn btn-add-employee" data-toggle="modal" data-target="#newEmployeeModal">
                                                       <i class="mdi mdi-account-plus-outline"></i>
                                                       Add New Employee
                                                  </button>
                                             </div>
                                        </div>
                                        <div class="card-body">
                                             <div class="row mb-3">
                                                  <div class="col-12">
                                                       <h5 class="font-weight-semibold text-dark mb-3">Employee Information Management</h5>
                                                       <div class="btn-group flex-wrap" role="group">
                                                            <a href="<?= base_url(); ?>Page/employeeList?status=<?= isset($statusFilter) ? urlencode($statusFilter) : 'Active'; ?>" class="btn btn-outline-primary">
                                                                 <i class="mdi mdi-account-group"></i> Employee List
                                                            </a>
                                                            <a href="<?= base_url(); ?>Page/employmentHistory" class="btn btn-outline-primary">
                                                                 <i class="mdi mdi-briefcase"></i> Employment History
                                                            </a>
                                                            <a href="<?= base_url(); ?>Page/employeeEducation" class="btn btn-outline-primary">
                                                                 <i class="mdi mdi-school"></i> Education
                                                            </a>
                                                            <a href="<?= base_url(); ?>Page/employeeSkills" class="btn btn-outline-primary">
                                                                 <i class="mdi mdi-certificate"></i> Skills & Certifications
                                                            </a>
                                                            <a href="<?= base_url(); ?>Page/employeeEmergencyContacts" class="btn btn-outline-primary">
                                                                 <i class="mdi mdi-phone"></i> Emergency Contacts
                                                            </a>
                                                            <a href="<?= base_url(); ?>Page/employeeDocuments" class="btn btn-outline-primary">
                                                                 <i class="mdi mdi-file-document"></i> Documents
                                                            </a>
                                                       </div>
                                                  </div>
                                             </div>
                                             <div class="row">
                                                  <div class="col-lg-4 col-sm-6">
                                                       <div class="d-flex align-items-center mb-3">
                                                            <span class="avatar-sm rounded-circle bg-white text-primary d-inline-flex align-items-center justify-content-center mr-3 shadow-sm">
                                                                 <i class="mdi mdi-account-group-outline"></i>
                                                            </span>
                                                            <div>
                                                                 <?php $employeeCount = is_array($data) ? count($data) : 0; ?>
                                                                 <h5 class="mb-0 font-weight-semibold text-dark"><?= number_format($employeeCount); ?> Employees</h5>
                                                                 <small class="text-dark">Active records in the directory</small>
                                                            </div>
                                                       </div>
                                                  </div>
                                                  <div class="col-lg-4 col-sm-6">
                                                       <div class="d-flex align-items-center mb-3">
                                                            <span class="avatar-sm rounded-circle bg-white text-primary d-inline-flex align-items-center justify-content-center mr-3 shadow-sm">
                                                                 <i class="mdi mdi-cash-check"></i>
                                                            </span>
                                                            <div>
                                                                 <h5 class="mb-0 font-weight-semibold text-dark"><?= number_format($configuredPayrollCount); ?> Payroll Profiles</h5>
                                                                 <small class="text-dark">Employees with salary and deduction defaults</small>
                                                            </div>
                                                       </div>
                                                  </div>
                                                  <div class="col-lg-4 col-sm-6">
                                                       <div class="d-flex align-items-center mb-3">
                                                            <span class="avatar-sm rounded-circle bg-white text-primary d-inline-flex align-items-center justify-content-center mr-3 shadow-sm">
                                                                 <i class="mdi mdi-filter-outline"></i>
                                                            </span>
                                                            <div>
                                                                 <label for="status_filter" class="mb-1 small text-dark font-weight-semibold">Filter by Status</label>
                                                                 <select class="form-control form-control-sm" id="status_filter" name="status_filter" onchange="filterByStatus()">
                                                                      <?php $currentFilter = isset($statusFilter) ? $statusFilter : 'Active'; ?>
                                                                      <option value="all" <?= $currentFilter === 'all' ? 'selected' : ''; ?>>All Statuses</option>
                                                                      <option value="Active" <?= $currentFilter === 'Active' ? 'selected' : ''; ?>>Active</option>
                                                                      <option value="Terminated" <?= $currentFilter === 'Terminated' ? 'selected' : ''; ?>>Terminated</option>
                                                                      <option value="On Leave" <?= $currentFilter === 'On Leave' ? 'selected' : ''; ?>>On Leave</option>
                                                                      <option value="Resigned" <?= $currentFilter === 'Resigned' ? 'selected' : ''; ?>>Resigned</option>
                                                                      <option value="Suspended" <?= $currentFilter === 'Suspended' ? 'selected' : ''; ?>>Suspended</option>
                                                                 </select>
                                                            </div>
                                                       </div>
                                                  </div>
                                             </div>
                                        </div>
                                   </div>
                              </div>
                         </div>

                         <div class="row">
                              <div class="col-12">
                                   <div class="card employee-table-card">
                                        <div class="card-body">
                                             <div class="table-responsive">
                                                  <table id="employee-table" class="table table-striped table-bordered w-100">
                                                       <thead class="bg-light text-uppercase">
                                                            <tr>
                                                                 <th>Emp. ID</th>
                                                                 <th>Employee Name</th>
                                                                 <th>Date Hired</th>
                                                                 <th>Position</th>
                                                                 <th>Department</th>
                                                                 <th>Monthly Salary</th>
                                                                 <th>Birth Date</th>
                                                                 <th class="action-column">Action</th>
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
                                                                           <td class="action-column">
                                                                                <div class="action-icons">
                                                                                     <a href="<?= $profileUrl; ?>" class="action-icon action-view" data-label="View Profile" aria-label="View Profile">
                                                                                          <i class="fa-solid fa-id-card"></i>
                                                                                     </a>
                                                                                     <a href="<?= $editUrl; ?>" class="action-icon action-edit" data-label="Edit Employee" aria-label="Edit Employee">
                                                                                          <i class="mdi mdi-square-edit-outline"></i>
                                                                                     </a>
                                                                                     <a href="<?= $deleteUrl; ?>" class="action-icon action-delete" data-label="Delete Employee" aria-label="Delete Employee" onclick="return confirm('Are you sure you want to delete this employee?');">
                                                                                          <i class="mdi mdi-delete-outline"></i>
                                                                                     </a>
                                                                                </div>
                                                                           </td>
                                                                      </tr>
                                                                 <?php endforeach; ?>
                                                            <?php else: ?>
                                                                 <tr>
                                                                      <td colspan="8" class="text-center text-muted py-4">No employee records found.</td>
                                                                 </tr>
                                                            <?php endif; ?>
                                                       </tbody>
                                                  </table>
                                             </div>
                                        </div>
                                   </div>
                              </div>
                         </div>
                    </div>

                    <?php include('includes/footer.php'); ?>
               </div>
          </div>
     </div>

     <?php include('includes/themecustomizer.php'); ?>

     <!-- New Employee Modal -->
     <div class="modal fade" id="newEmployeeModal" tabindex="-1" role="dialog" aria-hidden="true">
          <div class="modal-dialog modal-lg modal-dialog-centered">
               <div class="modal-content">
                    <div class="modal-header">
                         <h4 class="modal-title mb-0">Add New Employee</h4>
                         <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
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
