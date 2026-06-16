<!DOCTYPE html>
<html lang="en">

<?php include('includes/head.php'); ?>

<?php
$employees = isset($employees) && is_array($employees) ? $employees : array();
$skills = isset($skills) && is_array($skills) ? $skills : array();
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
                                                  <h3 class="text-white mb-1">Skills and Certifications</h3>
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
                                                            <a href="<?= base_url(); ?>Page/employeeEducation" class="btn btn-outline-primary">
                                                                 <i class="mdi mdi-school"></i> Education
                                                            </a>
                                                            <a href="<?= base_url(); ?>Page/employeeSkills" class="btn btn-primary">
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
                                             <button type="button" class="btn btn-primary mb-3" data-toggle="modal" data-target="#addSkillModal">
                                                  <i class="mdi mdi-plus"></i> Add Skill/Certification
                                             </button>
                                             <div class="table-responsive">
                                                  <table class="table table-striped table-bordered w-100">
                                                       <thead class="bg-light text-uppercase">
                                                            <tr>
                                                                 <th>Employee</th>
                                                                 <th>Skill/Certification</th>
                                                                 <th>Type</th>
                                                                 <th>Proficiency</th>
                                                                 <th>Issuing Organization</th>
                                                                 <th>Issue Date</th>
                                                                 <th>Expiry Date</th>
                                                                 <th>Actions</th>
                                                            </tr>
                                                       </thead>
                                                       <tbody>
                                                            <?php if (!empty($skills)): ?>
                                                                 <?php foreach ($skills as $skill): ?>
                                                                      <?php
                                                                      $employee = isset($employeeMap[(string) $skill->empID]) ? $employeeMap[(string) $skill->empID] : null;
                                                                      $employeeName = $employee ? htmlspecialchars(trim($employee->lName . ', ' . $employee->fName), ENT_QUOTES, 'UTF-8') : 'Unknown';
                                                                      $skillName = htmlspecialchars($skill->skill_name, ENT_QUOTES, 'UTF-8');
                                                                      $skillType = htmlspecialchars($skill->skill_type, ENT_QUOTES, 'UTF-8');
                                                                      $proficiency = htmlspecialchars($skill->proficiency_level ?? 'intermediate', ENT_QUOTES, 'UTF-8');
                                                                      $organization = htmlspecialchars($skill->issuing_organization ?? '', ENT_QUOTES, 'UTF-8');
                                                                      $issueDate = !empty($skill->issue_date) ? htmlspecialchars($skill->issue_date, ENT_QUOTES, 'UTF-8') : '-';
                                                                      $expiryDate = !empty($skill->expiry_date) ? htmlspecialchars($skill->expiry_date, ENT_QUOTES, 'UTF-8') : '-';
                                                                      
                                                                      $typeBadge = 'badge-primary';
                                                                      if ($skillType === 'certification') $typeBadge = 'badge-success';
                                                                      elseif ($skillType === 'license') $typeBadge = 'badge-warning';
                                                                      
                                                                      $profBadge = 'badge-secondary';
                                                                      if ($proficiency === 'beginner') $profBadge = 'badge-info';
                                                                      elseif ($proficiency === 'intermediate') $profBadge = 'badge-primary';
                                                                      elseif ($proficiency === 'advanced') $profBadge = 'badge-success';
                                                                      elseif ($proficiency === 'expert') $profBadge = 'badge-dark';
                                                                      ?>
                                                                      <tr>
                                                                           <td><?= $employeeName; ?></td>
                                                                           <td><?= $skillName; ?></td>
                                                                           <td><span class="badge <?= $typeBadge; ?>"><?= ucfirst($skillType); ?></span></td>
                                                                           <td><span class="badge <?= $profBadge; ?>"><?= ucfirst($proficiency); ?></span></td>
                                                                           <td><?= $organization; ?></td>
                                                                           <td><?= $issueDate; ?></td>
                                                                           <td><?= $expiryDate; ?></td>
                                                                           <td>
                                                                                <a href="#" class="btn btn-sm btn-info" onclick="editSkill(<?= $skill->id; ?>, '<?= htmlspecialchars($skill->empID, ENT_QUOTES, 'UTF-8'); ?>', '<?= htmlspecialchars($skill->skill_name, ENT_QUOTES, 'UTF-8'); ?>', '<?= htmlspecialchars($skill->skill_type, ENT_QUOTES, 'UTF-8'); ?>', '<?= htmlspecialchars($skill->proficiency_level, ENT_QUOTES, 'UTF-8'); ?>', '<?= htmlspecialchars($skill->issuing_organization ?? '', ENT_QUOTES, 'UTF-8'); ?>', '<?= htmlspecialchars($skill->issue_date ?? '', ENT_QUOTES, 'UTF-8'); ?>', '<?= htmlspecialchars($skill->expiry_date ?? '', ENT_QUOTES, 'UTF-8'); ?>', '<?= htmlspecialchars($skill->credential_number ?? '', ENT_QUOTES, 'UTF-8'); ?>', '<?= htmlspecialchars($skill->description ?? '', ENT_QUOTES, 'UTF-8'); ?>')"><i class="mdi mdi-pencil"></i></a>
                                                                                <a href="<?= base_url(); ?>Page/employeeSkills?delete_id=<?= $skill->id; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this record?');"><i class="mdi mdi-delete"></i></a>
                                                                           </td>
                                                                      </tr>
                                                                 <?php endforeach; ?>
                                                            <?php else: ?>
                                                                 <tr>
                                                                      <td colspan="8" class="text-center text-muted py-4">No skills or certifications found.</td>
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

     <!-- Add/Edit Skill Modal -->
     <div class="modal fade" id="addSkillModal" tabindex="-1" role="dialog" aria-hidden="true">
          <div class="modal-dialog modal-lg modal-dialog-centered">
               <div class="modal-content">
                    <div class="modal-header">
                         <h4 class="modal-title mb-0" id="skillModalTitle">Add Skill/Certification</h4>
                         <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                              <span aria-hidden="true">&times;</span>
                         </button>
                    </div>
                    <form class="needs-validation" method="post" action="<?= base_url(); ?>Page/employeeSkills" novalidate>
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
                                        <label for="skill_name">Skill/Certification Name</label>
                                        <input type="text" class="form-control" id="skill_name" name="skill_name" required>
                                   </div>
                              </div>
                              <div class="form-row">
                                   <div class="form-group col-md-4">
                                        <label for="skill_type">Type</label>
                                        <select class="form-control" id="skill_type" name="skill_type" required>
                                             <option value="skill">Skill</option>
                                             <option value="certification">Certification</option>
                                             <option value="license">License</option>
                                        </select>
                                   </div>
                                   <div class="form-group col-md-4">
                                        <label for="proficiency_level">Proficiency Level</label>
                                        <select class="form-control" id="proficiency_level" name="proficiency_level">
                                             <option value="beginner">Beginner</option>
                                             <option value="intermediate" selected>Intermediate</option>
                                             <option value="advanced">Advanced</option>
                                             <option value="expert">Expert</option>
                                        </select>
                                   </div>
                                   <div class="form-group col-md-4">
                                        <label for="credential_number">Credential Number</label>
                                        <input type="text" class="form-control" id="credential_number" name="credential_number">
                                   </div>
                              </div>
                              <div class="form-row">
                                   <div class="form-group col-md-6">
                                        <label for="issuing_organization">Issuing Organization</label>
                                        <input type="text" class="form-control" id="issuing_organization" name="issuing_organization">
                                   </div>
                                   <div class="form-group col-md-3">
                                        <label for="issue_date">Issue Date</label>
                                        <input type="date" class="form-control" id="issue_date" name="issue_date">
                                   </div>
                                   <div class="form-group col-md-3">
                                        <label for="expiry_date">Expiry Date</label>
                                        <input type="date" class="form-control" id="expiry_date" name="expiry_date">
                                   </div>
                              </div>
                              <div class="form-group">
                                   <label for="description">Description</label>
                                   <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                              </div>
                         </div>
                         <div class="modal-footer">
                              <button type="submit" id="skillSubmitBtn" name="add_skill" value="1" class="btn btn-primary">Add Record</button>
                              <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                         </div>
                    </form>
               </div>
          </div>
     </div>

     <script>
          function editSkill(id, empID, skillName, skillType, proficiencyLevel, issuingOrganization, issueDate, expiryDate, credentialNumber, description) {
               document.getElementById('edit_id').value = id;
               document.getElementById('employee_id').value = empID;
               document.getElementById('skill_name').value = skillName;
               document.getElementById('skill_type').value = skillType;
               document.getElementById('proficiency_level').value = proficiencyLevel;
               document.getElementById('issuing_organization').value = issuingOrganization;
               document.getElementById('issue_date').value = issueDate;
               document.getElementById('expiry_date').value = expiryDate;
               document.getElementById('credential_number').value = credentialNumber;
               document.getElementById('description').value = description;
               
               document.getElementById('skillModalTitle').textContent = 'Edit Skill/Certification';
               document.getElementById('skillSubmitBtn').name = 'edit_skill';
               document.getElementById('skillSubmitBtn').textContent = 'Update Record';
               
               $('#addSkillModal').modal('show');
          }
          
          $('#addSkillModal').on('hidden.bs.modal', function () {
               document.getElementById('edit_id').value = '';
               document.getElementById('employee_id').value = '';
               document.getElementById('skill_name').value = '';
               document.getElementById('skill_type').value = 'skill';
               document.getElementById('proficiency_level').value = 'intermediate';
               document.getElementById('issuing_organization').value = '';
               document.getElementById('issue_date').value = '';
               document.getElementById('expiry_date').value = '';
               document.getElementById('credential_number').value = '';
               document.getElementById('description').value = '';
               
               document.getElementById('skillModalTitle').textContent = 'Add Skill/Certification';
               document.getElementById('skillSubmitBtn').name = 'add_skill';
               document.getElementById('skillSubmitBtn').textContent = 'Add Record';
          });
     </script>

     <script src="<?= base_url(); ?>assets/js/vendor.min.js"></script>
     <script src="<?= base_url(); ?>assets/js/app.min.js"></script>
</body>

</html>
