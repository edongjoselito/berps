<!DOCTYPE html>
<html lang="en">

<?php include('includes/head.php'); ?>

<?php
$employees = isset($employees) && is_array($employees) ? $employees : array();
$emergencyContacts = isset($emergencyContacts) && is_array($emergencyContacts) ? $emergencyContacts : array();
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
                                                  <h3 class="text-white mb-1">Emergency Contacts</h3>
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
                                                            <a href="<?= base_url(); ?>Page/employeeSkills" class="btn btn-outline-primary">
                                                                 <i class="mdi mdi-certificate"></i> Skills & Certifications
                                                            </a>
                                                            <a href="<?= base_url(); ?>Page/employeeEmergencyContacts" class="btn btn-primary">
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
                                             <button type="button" class="btn btn-primary mb-3" data-toggle="modal" data-target="#addEmergencyContactModal">
                                                  <i class="mdi mdi-plus"></i> Add Emergency Contact
                                             </button>
                                             <div class="table-responsive">
                                                  <table class="table table-striped table-bordered w-100">
                                                       <thead class="bg-light text-uppercase">
                                                            <tr>
                                                                 <th>Employee</th>
                                                                 <th>Contact Name</th>
                                                                 <th>Relationship</th>
                                                                 <th>Phone Number</th>
                                                                 <th>Alternative Phone</th>
                                                                 <th>Email</th>
                                                                 <th>Primary</th>
                                                                 <th>Actions</th>
                                                            </tr>
                                                       </thead>
                                                       <tbody>
                                                            <?php if (!empty($emergencyContacts)): ?>
                                                                 <?php foreach ($emergencyContacts as $contact): ?>
                                                                      <?php
                                                                      $employee = isset($employeeMap[(string) $contact->empID]) ? $employeeMap[(string) $contact->empID] : null;
                                                                      $employeeName = $employee ? htmlspecialchars(trim($employee->lName . ', ' . $employee->fName), ENT_QUOTES, 'UTF-8') : 'Unknown';
                                                                      $contactName = htmlspecialchars($contact->contact_name, ENT_QUOTES, 'UTF-8');
                                                                      $relationship = htmlspecialchars($contact->relationship, ENT_QUOTES, 'UTF-8');
                                                                      $phone = htmlspecialchars($contact->phone_number, ENT_QUOTES, 'UTF-8');
                                                                      $altPhone = htmlspecialchars($contact->alternative_phone ?? '', ENT_QUOTES, 'UTF-8');
                                                                      $email = htmlspecialchars($contact->email ?? '', ENT_QUOTES, 'UTF-8');
                                                                      $isPrimary = (int) ($contact->is_primary ?? 0) === 1;
                                                                      ?>
                                                                      <tr>
                                                                           <td><?= $employeeName; ?></td>
                                                                           <td><?= $contactName; ?></td>
                                                                           <td><?= $relationship; ?></td>
                                                                           <td><?= $phone; ?></td>
                                                                           <td><?= $altPhone ?: '-'; ?></td>
                                                                           <td><?= $email ?: '-'; ?></td>
                                                                           <td>
                                                                                <?php if ($isPrimary): ?>
                                                                                     <span class="badge badge-success">Yes</span>
                                                                                <?php else: ?>
                                                                                     <span class="badge badge-secondary">No</span>
                                                                                <?php endif; ?>
                                                                           </td>
                                                                           <td>
                                                                                <a href="#" class="btn btn-sm btn-info" onclick="editEmergencyContact(<?= $contact->id; ?>, '<?= htmlspecialchars($contact->empID, ENT_QUOTES, 'UTF-8'); ?>', '<?= htmlspecialchars($contact->contact_name, ENT_QUOTES, 'UTF-8'); ?>', '<?= htmlspecialchars($contact->relationship, ENT_QUOTES, 'UTF-8'); ?>', '<?= htmlspecialchars($contact->phone_number, ENT_QUOTES, 'UTF-8'); ?>', '<?= htmlspecialchars($contact->alternative_phone ?? '', ENT_QUOTES, 'UTF-8'); ?>', '<?= htmlspecialchars($contact->email ?? '', ENT_QUOTES, 'UTF-8'); ?>', '<?= htmlspecialchars($contact->address ?? '', ENT_QUOTES, 'UTF-8'); ?>', <?= (int) $contact->is_primary; ?>)"><i class="mdi mdi-pencil"></i></a>
                                                                                <a href="<?= base_url(); ?>Page/employeeEmergencyContacts?delete_id=<?= $contact->id; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this record?');"><i class="mdi mdi-delete"></i></a>
                                                                           </td>
                                                                      </tr>
                                                                 <?php endforeach; ?>
                                                            <?php else: ?>
                                                                 <tr>
                                                                      <td colspan="8" class="text-center text-muted py-4">No emergency contacts found.</td>
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

     <!-- Add/Edit Emergency Contact Modal -->
     <div class="modal fade" id="addEmergencyContactModal" tabindex="-1" role="dialog" aria-hidden="true">
          <div class="modal-dialog modal-lg modal-dialog-centered">
               <div class="modal-content">
                    <div class="modal-header">
                         <h4 class="modal-title mb-0" id="emergencyContactModalTitle">Add Emergency Contact</h4>
                         <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                              <span aria-hidden="true">&times;</span>
                         </button>
                    </div>
                    <form class="needs-validation" method="post" action="<?= base_url(); ?>Page/employeeEmergencyContacts" novalidate>
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
                                        <label for="contact_name">Contact Name</label>
                                        <input type="text" class="form-control" id="contact_name" name="contact_name" required>
                                   </div>
                              </div>
                              <div class="form-row">
                                   <div class="form-group col-md-6">
                                        <label for="relationship">Relationship</label>
                                        <input type="text" class="form-control" id="relationship" name="relationship" required placeholder="e.g., Spouse, Parent, Sibling">
                                   </div>
                                   <div class="form-group col-md-6">
                                        <label for="phone_number">Phone Number</label>
                                        <input type="text" class="form-control" id="phone_number" name="phone_number" required>
                                   </div>
                              </div>
                              <div class="form-row">
                                   <div class="form-group col-md-6">
                                        <label for="alternative_phone">Alternative Phone</label>
                                        <input type="text" class="form-control" id="alternative_phone" name="alternative_phone">
                                   </div>
                                   <div class="form-group col-md-6">
                                        <label for="email">Email</label>
                                        <input type="email" class="form-control" id="email" name="email">
                                   </div>
                              </div>
                              <div class="form-group">
                                   <label for="address">Address</label>
                                   <textarea class="form-control" id="address" name="address" rows="2"></textarea>
                              </div>
                              <div class="form-group">
                                   <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" id="is_primary" name="is_primary" value="1">
                                        <label class="custom-control-label" for="is_primary">Mark as Primary Emergency Contact</label>
                                   </div>
                              </div>
                         </div>
                         <div class="modal-footer">
                              <button type="submit" id="emergencyContactSubmitBtn" name="add_emergency_contact" value="1" class="btn btn-primary">Add Contact</button>
                              <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                         </div>
                    </form>
               </div>
          </div>
     </div>

     <script>
          function editEmergencyContact(id, empID, contactName, relationship, phoneNumber, altPhone, email, address, isPrimary) {
               document.getElementById('edit_id').value = id;
               document.getElementById('employee_id').value = empID;
               document.getElementById('contact_name').value = contactName;
               document.getElementById('relationship').value = relationship;
               document.getElementById('phone_number').value = phoneNumber;
               document.getElementById('alternative_phone').value = altPhone;
               document.getElementById('email').value = email;
               document.getElementById('address').value = address;
               document.getElementById('is_primary').checked = isPrimary === 1;
               
               document.getElementById('emergencyContactModalTitle').textContent = 'Edit Emergency Contact';
               document.getElementById('emergencyContactSubmitBtn').name = 'edit_emergency_contact';
               document.getElementById('emergencyContactSubmitBtn').textContent = 'Update Contact';
               
               $('#addEmergencyContactModal').modal('show');
          }
          
          $('#addEmergencyContactModal').on('hidden.bs.modal', function () {
               document.getElementById('edit_id').value = '';
               document.getElementById('employee_id').value = '';
               document.getElementById('contact_name').value = '';
               document.getElementById('relationship').value = '';
               document.getElementById('phone_number').value = '';
               document.getElementById('alternative_phone').value = '';
               document.getElementById('email').value = '';
               document.getElementById('address').value = '';
               document.getElementById('is_primary').checked = false;
               
               document.getElementById('emergencyContactModalTitle').textContent = 'Add Emergency Contact';
               document.getElementById('emergencyContactSubmitBtn').name = 'add_emergency_contact';
               document.getElementById('emergencyContactSubmitBtn').textContent = 'Add Contact';
          });
     </script>

     <script src="<?= base_url(); ?>assets/js/vendor.min.js"></script>
     <script src="<?= base_url(); ?>assets/js/app.min.js"></script>
</body>

</html>
