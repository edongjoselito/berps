<!DOCTYPE html>
<html lang="en">

<?php include('includes/head.php'); ?>

<?php
$employees = isset($employees) && is_array($employees) ? $employees : array();
$education = isset($education) && is_array($education) ? $education : array();
$employeeMap = array();
foreach ($employees as $employee) {
    $empKey = (string) ($employee->empID ?? '');
    if ($empKey !== '') {
        $employeeMap[$empKey] = $employee;
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
                              }
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
                              .employee-list-page .employee-table-card {
                                   border: none;
                                   border-radius: 18px;
                                   box-shadow: 0 14px 32px rgba(15, 23, 42, 0.07);
                                   margin-bottom: 18px;
                              }
                              .employee-list-page .employee-table-card .card-body {
                                   padding: 28px 32px;
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
                                                  <h3 class="text-white mb-1">Educational Background</h3>
                                             </div>
                                        </div>
                                        <div class="card-body">
                                             <div class="row mb-3">
                                                  <div class="col-12">
                                                       <h5 class="font-weight-semibold text-dark mb-3">Employee Information Management</h5>
                                                       <div class="btn-group flex-wrap" role="group">
                                                            <a href="<?= base_url(); ?>Page/employeeList" class="btn btn-outline-primary">
                                                                 <i class="mdi mdi-account-group"></i> Employee List
                                                            </a>
                                                            <a href="<?= base_url(); ?>Page/employmentHistory" class="btn btn-outline-primary">
                                                                 <i class="mdi mdi-briefcase"></i> Employment History
                                                            </a>
                                                            <a href="<?= base_url(); ?>Page/employeeEducation" class="btn btn-primary">
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
                                        </div>
                                   </div>
                              </div>
                         </div>

                         <div class="row">
                              <div class="col-12">
                                   <div class="card employee-table-card">
                                        <div class="card-body">
                                             <button type="button" class="btn btn-primary mb-3" data-toggle="modal" data-target="#addEducationModal">
                                                  <i class="mdi mdi-plus"></i> Add Education
                                             </button>
                                             <div class="table-responsive">
                                                  <table class="table table-striped table-bordered w-100">
                                                       <thead class="bg-light text-uppercase">
                                                            <tr>
                                                                 <th>Employee</th>
                                                                 <th>Institution</th>
                                                                 <th>Degree</th>
                                                                 <th>Field of Study</th>
                                                                 <th>Start Date</th>
                                                                 <th>End Date</th>
                                                                 <th>Status</th>
                                                                 <th>Actions</th>
                                                            </tr>
                                                       </thead>
                                                       <tbody>
                                                            <?php if (!empty($education)): ?>
                                                                 <?php foreach ($education as $edu): ?>
                                                                      <?php
                                                                      $employee = isset($employeeMap[(string) $edu->empID]) ? $employeeMap[(string) $edu->empID] : null;
                                                                      $employeeName = $employee ? htmlspecialchars(trim($employee->lName . ', ' . $employee->fName), ENT_QUOTES, 'UTF-8') : 'Unknown';
                                                                      $institution = htmlspecialchars($edu->institution_name, ENT_QUOTES, 'UTF-8');
                                                                      $degree = htmlspecialchars($edu->degree, ENT_QUOTES, 'UTF-8');
                                                                      $fieldOfStudy = htmlspecialchars($edu->field_of_study ?? '', ENT_QUOTES, 'UTF-8');
                                                                      $startDate = !empty($edu->start_date) ? htmlspecialchars($edu->start_date, ENT_QUOTES, 'UTF-8') : '-';
                                                                      $endDate = !empty($edu->end_date) ? htmlspecialchars($edu->end_date, ENT_QUOTES, 'UTF-8') : 'Ongoing';
                                                                      $isCurrent = (int) ($edu->is_current ?? 0) === 1;
                                                                      ?>
                                                                      <tr>
                                                                           <td><?= $employeeName; ?></td>
                                                                           <td><?= $institution; ?></td>
                                                                           <td><?= $degree; ?></td>
                                                                           <td><?= $fieldOfStudy; ?></td>
                                                                           <td><?= $startDate; ?></td>
                                                                           <td><?= $endDate; ?></td>
                                                                           <td>
                                                                                <?php if ($isCurrent): ?>
                                                                                     <span class="badge badge-success">Ongoing</span>
                                                                                <?php else: ?>
                                                                                     <span class="badge badge-secondary">Completed</span>
                                                                                <?php endif; ?>
                                                                           </td>
                                                                           <td>
                                                                                <a href="#" class="btn btn-sm btn-info" onclick="editEducation(<?= $edu->id; ?>, '<?= htmlspecialchars($edu->empID, ENT_QUOTES, 'UTF-8'); ?>', '<?= htmlspecialchars($edu->institution_name, ENT_QUOTES, 'UTF-8'); ?>', '<?= htmlspecialchars($edu->degree, ENT_QUOTES, 'UTF-8'); ?>', '<?= htmlspecialchars($edu->field_of_study ?? '', ENT_QUOTES, 'UTF-8'); ?>', '<?= htmlspecialchars($edu->start_date, ENT_QUOTES, 'UTF-8'); ?>', '<?= htmlspecialchars($edu->end_date ?? '', ENT_QUOTES, 'UTF-8'); ?>', <?= (int) $edu->is_current; ?>, '<?= htmlspecialchars($edu->gpa ?? '', ENT_QUOTES, 'UTF-8'); ?>', '<?= htmlspecialchars($edu->description ?? '', ENT_QUOTES, 'UTF-8'); ?>')"><i class="mdi mdi-pencil"></i></a>
                                                                                <a href="<?= base_url(); ?>Page/employeeEducation?delete_id=<?= $edu->id; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this record?');"><i class="mdi mdi-delete"></i></a>
                                                                           </td>
                                                                      </tr>
                                                                 <?php endforeach; ?>
                                                            <?php else: ?>
                                                                 <tr>
                                                                      <td colspan="8" class="text-center text-muted py-4">No education records found.</td>
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

     <!-- Add/Edit Education Modal -->
     <div class="modal fade" id="addEducationModal" tabindex="-1" role="dialog" aria-hidden="true">
          <div class="modal-dialog modal-lg modal-dialog-centered">
               <div class="modal-content">
                    <div class="modal-header">
                         <h4 class="modal-title mb-0" id="educationModalTitle">Add Education</h4>
                         <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                              <span aria-hidden="true">&times;</span>
                         </button>
                    </div>
                    <form class="needs-validation" method="post" action="<?= base_url(); ?>Page/employeeEducation" novalidate>
                         <input type="hidden" id="edit_id" name="edit_id" value="">
                         <div class="modal-body">
                              <div class="form-row">
                                   <div class="form-group col-md-6">
                                        <label for="employee_id">Employee</label>
                                        <select class="form-control" id="employee_id" name="empID" required>
                                             <option value="">Select Employee</option>
                                             <?php foreach ($employees as $employee): ?>
                                                  <option value="<?= htmlspecialchars($employee->empID, ENT_QUOTES, 'UTF-8'); ?>">
                                                       <?= htmlspecialchars(trim($employee->lName . ', ' . $employee->fName), ENT_QUOTES, 'UTF-8'); ?>
                                                  </option>
                                             <?php endforeach; ?>
                                        </select>
                                   </div>
                                   <div class="form-group col-md-6">
                                        <label for="institution_name">Institution Name</label>
                                        <input type="text" class="form-control" id="institution_name" name="institution_name" required>
                                   </div>
                              </div>
                              <div class="form-row">
                                   <div class="form-group col-md-6">
                                        <label for="degree">Degree</label>
                                        <input type="text" class="form-control" id="degree" name="degree" required>
                                   </div>
                                   <div class="form-group col-md-6">
                                        <label for="field_of_study">Field of Study</label>
                                        <input type="text" class="form-control" id="field_of_study" name="field_of_study">
                                   </div>
                              </div>
                              <div class="form-row">
                                   <div class="form-group col-md-3">
                                        <label for="start_date">Start Date</label>
                                        <input type="date" class="form-control" id="start_date" name="start_date" required>
                                   </div>
                                   <div class="form-group col-md-3">
                                        <label for="end_date">End Date</label>
                                        <input type="date" class="form-control" id="end_date" name="end_date">
                                   </div>
                                   <div class="form-group col-md-3">
                                        <label for="gpa">GPA</label>
                                        <input type="number" step="0.01" class="form-control" id="gpa" name="gpa" min="0" max="4.00">
                                   </div>
                              </div>
                              <div class="form-group">
                                   <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" id="is_current" name="is_current" value="1">
                                        <label class="custom-control-label" for="is_current">Currently Studying</label>
                                   </div>
                              </div>
                              <div class="form-group">
                                   <label for="description">Description</label>
                                   <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                              </div>
                         </div>
                         <div class="modal-footer">
                              <button type="submit" id="educationSubmitBtn" name="add_education" value="1" class="btn btn-primary">Add Record</button>
                              <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                         </div>
                    </form>
               </div>
          </div>
     </div>

     <script>
          function editEducation(id, empID, institutionName, degree, fieldOfStudy, startDate, endDate, isCurrent, gpa, description) {
               document.getElementById('edit_id').value = id;
               document.getElementById('employee_id').value = empID;
               document.getElementById('institution_name').value = institutionName;
               document.getElementById('degree').value = degree;
               document.getElementById('field_of_study').value = fieldOfStudy;
               document.getElementById('start_date').value = startDate;
               document.getElementById('end_date').value = endDate;
               document.getElementById('is_current').checked = isCurrent === 1;
               document.getElementById('gpa').value = gpa;
               document.getElementById('description').value = description;
               
               document.getElementById('educationModalTitle').textContent = 'Edit Education';
               document.getElementById('educationSubmitBtn').name = 'edit_education';
               document.getElementById('educationSubmitBtn').textContent = 'Update Record';
               
               $('#addEducationModal').modal('show');
          }
          
          $('#addEducationModal').on('hidden.bs.modal', function () {
               document.getElementById('edit_id').value = '';
               document.getElementById('employee_id').value = '';
               document.getElementById('institution_name').value = '';
               document.getElementById('degree').value = '';
               document.getElementById('field_of_study').value = '';
               document.getElementById('start_date').value = '';
               document.getElementById('end_date').value = '';
               document.getElementById('is_current').checked = false;
               document.getElementById('gpa').value = '';
               document.getElementById('description').value = '';
               
               document.getElementById('educationModalTitle').textContent = 'Add Education';
               document.getElementById('educationSubmitBtn').name = 'add_education';
               document.getElementById('educationSubmitBtn').textContent = 'Add Record';
          });
     </script>

     <script src="<?= base_url(); ?>assets/js/vendor.min.js"></script>
     <script src="<?= base_url(); ?>assets/js/app.min.js"></script>
</body>

</html>
