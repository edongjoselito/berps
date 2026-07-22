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

                         <style>
                             /* Hero Banner */
                             .employee-list-page .el-hero {
                                 display: flex;
                                 align-items: center;
                                 justify-content: space-between;
                                 flex-wrap: wrap;
                                 gap: 16px;
                                 padding: 28px 24px;
                                 margin: 0 0 22px;
                                 border-radius: 16px;
                                 background: #1e3a8a;
                                 box-shadow: 0 8px 32px rgba(30, 58, 138, 0.25);
                                 position: relative;
                                 overflow: hidden;
                             }

                             .employee-list-page .el-hero::before {
                                 content: '';
                                 position: absolute;
                                 top: -50%;
                                 right: -10%;
                                 width: 400px;
                                 height: 400px;
                                 border-radius: 50%;
                                 background: rgba(255, 255, 255, 0.06);
                                 pointer-events: none;
                             }

                             .employee-list-page .el-hero::after {
                                 content: '';
                                 position: absolute;
                                 bottom: -60%;
                                 right: 15%;
                                 width: 300px;
                                 height: 300px;
                                 border-radius: 50%;
                                 background: rgba(255, 255, 255, 0.04);
                                 pointer-events: none;
                             }

                             .employee-list-page .el-hero__content {
                                 position: relative;
                                 z-index: 1;
                             }

                             .employee-list-page .el-hero__eyebrow {
                                 display: inline-flex;
                                 align-items: center;
                                 gap: 6px;
                                 margin-bottom: 8px;
                                 color: rgba(255, 255, 255, 0.85);
                                 font-size: 0.78rem;
                                 font-weight: 600;
                                 letter-spacing: 0.04em;
                             }

                             .employee-list-page .el-hero__eyebrow i {
                                 font-size: 1rem;
                             }

                             .employee-list-page .el-hero__title {
                                 margin: 0 0 4px 0;
                                 color: #fff;
                                 font-size: clamp(1.6rem, 2.5vw, 2.2rem);
                                 font-weight: 800;
                                 line-height: 1.15;
                                 letter-spacing: -0.02em;
                                 font-family: var(--font-primary, Montserrat, Segoe UI, Arial, sans-serif), "Apple Color Emoji", "Segoe UI Emoji", "Noto Color Emoji", sans-serif;
                             }

                             .employee-list-page .el-hero__subtitle {
                                 margin: 0;
                                 color: rgba(255, 255, 255, 0.8);
                                 font-size: 0.88rem;
                                 max-width: 520px;
                             }

                             .employee-list-page .el-hero__actions {
                                 display: flex;
                                 align-items: center;
                                 flex-wrap: wrap;
                                 gap: 10px;
                                 position: relative;
                                 z-index: 1;
                             }

                             .employee-list-page .el-hero-btn {
                                 display: inline-flex;
                                 align-items: center;
                                 gap: 6px;
                                 padding: 8px 16px;
                                 border-radius: 10px;
                                 border: 1px solid rgba(255, 255, 255, 0.3);
                                 background: rgba(255, 255, 255, 0.15);
                                 color: #fff;
                                 font-size: 0.82rem;
                                 font-weight: 600;
                                 text-decoration: none;
                                 cursor: pointer;
                                 transition: all 0.18s ease;
                             }

                             .employee-list-page .el-hero-btn:hover,
                             .employee-list-page .el-hero-btn:focus {
                                 background: rgba(255, 255, 255, 0.25);
                                 border-color: rgba(255, 255, 255, 0.5);
                                 color: #fff;
                                 text-decoration: none;
                                 transform: translateY(-1px);
                             }

                             .employee-list-page .el-hero-btn--solid {
                                 border-color: rgba(255, 255, 255, 0.6);
                                 background: rgba(255, 255, 255, 0.95);
                                 color: #1e3a8a;
                                 font-weight: 700;
                             }

                             .employee-list-page .el-hero-btn--solid:hover,
                             .employee-list-page .el-hero-btn--solid:focus {
                                 background: #fff;
                                 color: #1e2a6a;
                             }

                             /* People pulse animation */
                             .employee-list-page .people-pulse {
                                 display: inline-block;
                                 animation: people-pulse 2s ease-in-out infinite;
                             }

                             @keyframes people-pulse {
                                 0%, 70%, 100% { transform: scale(1); }
                                 15% { transform: scale(1.15); }
                                 30% { transform: scale(1); }
                                 45% { transform: scale(1.08); }
                                 60% { transform: scale(1); }
                             }

                             /* Blue accent on cards */
                             .employee-list-page .berps-section-card,
                             .employee-list-page .berps-table-card {
                                 border-top: 3px solid #1e3a8a;
                             }

                             .employee-list-page .berps-section-title {
                                 color: #1e3a8a;
                             }

                             /* Responsive hero */
                             @media (max-width: 767px) {
                                 .employee-list-page .el-hero,
                                 .employee-list-page .el-hero__actions {
                                     flex-direction: column;
                                     align-items: stretch;
                                 }

                                 .employee-list-page .el-hero {
                                     padding: 20px;
                                 }

                                 .employee-list-page .el-hero-btn {
                                     flex: 1 1 auto;
                                     justify-content: center;
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


                         <?php
                         $employeeCount = is_array($data) ? count($data) : 0;
                         $currentFilter = isset($statusFilter) ? $statusFilter : 'Active';
                         ?>

                         <div class="el-hero">
                              <div class="el-hero__content">
                                   <div class="el-hero__eyebrow">
                                        <i class="mdi mdi-account-group-outline"></i>
                                        People Operations
                                   </div>
                                   <h1 class="el-hero__title">Employee Directory <span class="people-pulse">👥</span></h1>
                                   <p class="el-hero__subtitle">Manage employee profiles, employment records, documents, and payroll setup.</p>
                              </div>
                              <div class="el-hero__actions">
                                   <a class="el-hero-btn" href="<?= base_url(); ?>Page/payrollModule">
                                        <i class="mdi mdi-cash-multiple"></i>
                                        <span>Payroll Module</span>
                                   </a>
                                   <button type="button" class="el-hero-btn el-hero-btn--solid" data-toggle="modal" data-target="#newEmployeeModal">
                                        <i class="mdi mdi-account-plus-outline"></i>
                                        <span>Add Employee</span>
                                   </button>
                              </div>
                         </div>

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
                                                                                     <a href="<?= $deleteUrl; ?>" class="berps-icon-action berps-icon-action--danger" title="Delete employee" aria-label="Delete employee"
                                                                                          data-berps-confirm="This permanently removes the employee record. This action cannot be undone."
                                                                                          data-berps-confirm-title="Delete employee?"
                                                                                          data-berps-confirm-label="Delete employee">
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
     <div class="modal fade berps-form-modal" id="newEmployeeModal" tabindex="-1" role="dialog" aria-labelledby="newEmployeeModalTitle" aria-hidden="true">
          <div class="modal-dialog modal-lg modal-dialog-centered">
               <div class="modal-content">
                    <div class="modal-header">
                         <div>
                              <h2 class="modal-title mb-0" id="newEmployeeModalTitle">Add New Employee</h2>
                              <p class="berps-modal-subtitle">Create the employee profile, permissions, and payroll defaults.</p>
                         </div>
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
                              <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Cancel</button>
                              <button type="reset" name="resettask" class="btn btn-outline-secondary">Reset</button>
                              <button type="submit" name="addemployee" value="1" class="btn btn-primary">Add Employee</button>
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
