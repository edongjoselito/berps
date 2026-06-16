<!DOCTYPE html>
<html lang="en">

<?php include('includes/head.php'); ?>

<?php
$employees = isset($employees) && is_array($employees) ? $employees : array();
$documents = isset($documents) && is_array($documents) ? $documents : array();
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
                                                  <h3 class="text-white mb-1">Employee Documents</h3>
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
                                                            <a href="<?= base_url(); ?>Page/employeeEmergencyContacts" class="btn btn-outline-primary">
                                                                 <i class="mdi mdi-phone"></i> Emergency Contacts
                                                            </a>
                                                            <a href="<?= base_url(); ?>Page/employeeDocuments" class="btn btn-primary">
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
                                             <button type="button" class="btn btn-primary mb-3" data-toggle="modal" data-target="#addDocumentModal">
                                                  <i class="mdi mdi-plus"></i> Upload Document
                                             </button>
                                             <div class="table-responsive">
                                                  <table class="table table-striped table-bordered w-100">
                                                       <thead class="bg-light text-uppercase">
                                                            <tr>
                                                                 <th>Employee</th>
                                                                 <th>Document Type</th>
                                                                 <th>Document Name</th>
                                                                 <th>File Size</th>
                                                                 <th>Issue Date</th>
                                                                 <th>Expiry Date</th>
                                                                 <th>Confidential</th>
                                                                 <th>Actions</th>
                                                            </tr>
                                                       </thead>
                                                       <tbody>
                                                            <?php if (!empty($documents)): ?>
                                                                 <?php foreach ($documents as $doc): ?>
                                                                      <?php
                                                                      $employee = isset($employeeMap[(string) $doc->empID]) ? $employeeMap[(string) $doc->empID] : null;
                                                                      $employeeName = $employee ? htmlspecialchars(trim($employee->lName . ', ' . $employee->fName), ENT_QUOTES, 'UTF-8') : 'Unknown';
                                                                      $docType = htmlspecialchars($doc->document_type, ENT_QUOTES, 'UTF-8');
                                                                      $docName = htmlspecialchars($doc->document_name, ENT_QUOTES, 'UTF-8');
                                                                      $fileSize = $doc->file_size ? number_format($doc->file_size / 1024, 2) . ' KB' : '-';
                                                                      $issueDate = !empty($doc->issue_date) ? htmlspecialchars($doc->issue_date, ENT_QUOTES, 'UTF-8') : '-';
                                                                      $expiryDate = !empty($doc->expiry_date) ? htmlspecialchars($doc->expiry_date, ENT_QUOTES, 'UTF-8') : '-';
                                                                      $isConfidential = (int) ($doc->is_confidential ?? 0) === 1;
                                                                      ?>
                                                                      <tr>
                                                                           <td><?= $employeeName; ?></td>
                                                                           <td><?= $docType; ?></td>
                                                                           <td><?= $docName; ?></td>
                                                                           <td><?= $fileSize; ?></td>
                                                                           <td><?= $issueDate; ?></td>
                                                                           <td><?= $expiryDate; ?></td>
                                                                           <td>
                                                                                <?php if ($isConfidential): ?>
                                                                                     <span class="badge badge-danger">Yes</span>
                                                                                <?php else: ?>
                                                                                     <span class="badge badge-secondary">No</span>
                                                                                <?php endif; ?>
                                                                           </td>
                                                                           <td>
                                                                                <a href="<?= base_url(); ?>uploads/employee_documents/<?= htmlspecialchars($doc->file_path, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" class="btn btn-sm btn-success"><i class="mdi mdi-download"></i></a>
                                                                                <a href="#" class="btn btn-sm btn-info" onclick="editDocument(<?= $doc->id; ?>, '<?= htmlspecialchars($doc->empID, ENT_QUOTES, 'UTF-8'); ?>', '<?= htmlspecialchars($doc->document_type, ENT_QUOTES, 'UTF-8'); ?>', '<?= htmlspecialchars($doc->document_name, ENT_QUOTES, 'UTF-8'); ?>', '<?= htmlspecialchars($doc->description ?? '', ENT_QUOTES, 'UTF-8'); ?>', '<?= htmlspecialchars($doc->issue_date ?? '', ENT_QUOTES, 'UTF-8'); ?>', '<?= htmlspecialchars($doc->expiry_date ?? '', ENT_QUOTES, 'UTF-8'); ?>', <?= (int) $doc->is_confidential; ?>)"><i class="mdi mdi-pencil"></i></a>
                                                                                <a href="<?= base_url(); ?>Page/employeeDocuments?delete_id=<?= $doc->id; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this document? The file will also be deleted.');"><i class="mdi mdi-delete"></i></a>
                                                                           </td>
                                                                      </tr>
                                                                 <?php endforeach; ?>
                                                            <?php else: ?>
                                                                 <tr>
                                                                      <td colspan="8" class="text-center text-muted py-4">No documents found.</td>
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

     <!-- Add/Edit Document Modal -->
     <div class="modal fade" id="addDocumentModal" tabindex="-1" role="dialog" aria-hidden="true">
          <div class="modal-dialog modal-lg modal-dialog-centered">
               <div class="modal-content">
                    <div class="modal-header">
                         <h4 class="modal-title mb-0" id="documentModalTitle">Upload Document</h4>
                         <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                              <span aria-hidden="true">&times;</span>
                         </button>
                    </div>
                    <form class="needs-validation" method="post" action="<?= base_url(); ?>Page/employeeDocuments" enctype="multipart/form-data" novalidate>
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
                                        <label for="document_type">Document Type</label>
                                        <select class="form-control" id="document_type" name="document_type" required>
                                             <option value="">Select Type</option>
                                             <option value="Resume">Resume</option>
                                             <option value="Contract">Contract</option>
                                             <option value="ID">ID</option>
                                             <option value="Certificate">Certificate</option>
                                             <option value="License">License</option>
                                             <option value="Diploma">Diploma</option>
                                             <option value="NBI Clearance">NBI Clearance</option>
                                             <option value="Tax Form">Tax Form</option>
                                             <option value="Medical Certificate">Medical Certificate</option>
                                             <option value="Other">Other</option>
                                        </select>
                                   </div>
                              </div>
                              <div class="form-group">
                                   <label for="document_name">Document Name</label>
                                   <input type="text" class="form-control" id="document_name" name="document_name" required>
                              </div>
                              <div class="form-group">
                                   <label for="document_file">File</label>
                                   <input type="file" class="form-control" id="document_file" name="document_file">
                                   <small class="text-muted" id="file_help_text">Allowed file types: PDF, DOC, DOCX, JPG, PNG</small>
                              </div>
                              <div class="form-row">
                                   <div class="form-group col-md-4">
                                        <label for="issue_date">Issue Date</label>
                                        <input type="date" class="form-control" id="issue_date" name="issue_date">
                                   </div>
                                   <div class="form-group col-md-4">
                                        <label for="expiry_date">Expiry Date</label>
                                        <input type="date" class="form-control" id="expiry_date" name="expiry_date">
                                   </div>
                              </div>
                              <div class="form-group">
                                   <label for="description">Description</label>
                                   <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                              </div>
                              <div class="form-group">
                                   <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" id="is_confidential" name="is_confidential" value="1">
                                        <label class="custom-control-label" for="is_confidential">Mark as Confidential</label>
                                   </div>
                              </div>
                         </div>
                         <div class="modal-footer">
                              <button type="submit" id="documentSubmitBtn" name="upload_document" value="1" class="btn btn-primary">Upload Document</button>
                              <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                         </div>
                    </form>
               </div>
          </div>
     </div>

     <script>
          function editDocument(id, empID, documentType, documentName, description, issueDate, expiryDate, isConfidential) {
               document.getElementById('edit_id').value = id;
               document.getElementById('employee_id').value = empID;
               document.getElementById('document_type').value = documentType;
               document.getElementById('document_name').value = documentName;
               document.getElementById('description').value = description;
               document.getElementById('issue_date').value = issueDate;
               document.getElementById('expiry_date').value = expiryDate;
               document.getElementById('is_confidential').checked = isConfidential === 1;
               
               document.getElementById('document_file').removeAttribute('required');
               document.getElementById('file_help_text').textContent = 'Leave empty to keep existing file';
               
               document.getElementById('documentModalTitle').textContent = 'Edit Document';
               document.getElementById('documentSubmitBtn').name = 'edit_document';
               document.getElementById('documentSubmitBtn').textContent = 'Update Document';
               
               $('#addDocumentModal').modal('show');
          }
          
          $('#addDocumentModal').on('hidden.bs.modal', function () {
               document.getElementById('edit_id').value = '';
               document.getElementById('employee_id').value = '';
               document.getElementById('document_type').value = '';
               document.getElementById('document_name').value = '';
               document.getElementById('document_file').value = '';
               document.getElementById('document_file').setAttribute('required', 'required');
               document.getElementById('file_help_text').textContent = 'Allowed file types: PDF, DOC, DOCX, JPG, PNG';
               document.getElementById('issue_date').value = '';
               document.getElementById('expiry_date').value = '';
               document.getElementById('description').value = '';
               document.getElementById('is_confidential').checked = false;
               
               document.getElementById('documentModalTitle').textContent = 'Upload Document';
               document.getElementById('documentSubmitBtn').name = 'upload_document';
               document.getElementById('documentSubmitBtn').textContent = 'Upload Document';
          });
     </script>

     <script src="<?= base_url(); ?>assets/js/vendor.min.js"></script>
     <script src="<?= base_url(); ?>assets/js/app.min.js"></script>
</body>

</html>
