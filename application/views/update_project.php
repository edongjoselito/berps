<!DOCTYPE html>
<html lang="en">
<?php include('includes/head.php'); ?>

<body>
     <div id="wrapper">
          <?php include('includes/top-nav-bar.php'); ?>
          <?php include('includes/sidebar.php'); ?>

          <div class="content-page">
               <div class="content">
                    <div class="container-fluid update-project-page">
                         <style>
                              .content-page {
                                   display: flex;
                                   flex-direction: column;
                                   min-height: 100vh;
                              }

                              .content-page .content {
                                   flex: 1 0 auto;
                                   padding-bottom: 1.25rem;
                              }

                              .footer {
                                   flex: 0 0 auto;
                                   margin-top: 0 !important;
                              }

                              .update-project-page .title-wrap {
                                   padding: 1.25rem 1.25rem 0;
                              }

                              .update-project-page .title-wrap h4 {
                                   margin: 0;
                                   line-height: 1.1;
                              }

                              .update-project-page .title-sub {
                                   display: inline-block;
                                   margin-top: 4px;
                              }

                              .update-project-page .title-divider {
                                   border: 0;
                                   height: 2px;
                                   border-radius: 1px;
                                   background: linear-gradient(to right, #4285F4 60%, #FBBC05 80%, #34A853 100%);
                                   margin: 10px 1.25rem 16px;
                              }

                              .update-project-page .card {
                                   border: 1px solid rgba(15, 23, 42, .08);
                                   border-radius: 14px;
                              }

                              .update-project-page .card-header {
                                   border-bottom: 1px solid rgba(15, 23, 42, .08);
                                   background: rgba(248, 250, 252, .65);
                                   padding: .85rem 1.25rem;
                              }

                              .update-project-page .card-body {
                                   padding: 1.25rem;
                              }

                              .update-project-page .form-label {
                                   font-weight: 600;
                                   color: #5c6b7a;
                                   margin-bottom: .25rem;
                              }

                              .update-project-page .req::after {
                                   content: " *";
                                   color: #dc3545;
                              }

                              .update-project-page .form-row {
                                   margin-left: -8px;
                                   margin-right: -8px;
                              }

                              .update-project-page .form-row>[class^="col"] {
                                   padding-left: 8px;
                                   padding-right: 8px;
                              }

                              .form-text {
                                   font-size: .8rem;
                                   color: #6c757d;
                              }

                              .was-validated .form-control:invalid,
                              .form-control.is-invalid,
                              .was-validated .custom-select:invalid,
                              .custom-select.is-invalid {
                                   border-color: #dc3545;
                              }

                              .was-validated .form-control:valid,
                              .form-control.is-valid,
                              .was-validated .custom-select:valid,
                              .custom-select.is-valid {
                                   border-color: #198754;
                              }

                              .update-project-page .form-actions {
                                   display: flex;
                                   gap: .5rem;
                                   justify-content: flex-end;
                              }

                              #project-other-details {
                                   min-height: 110px;
                              }

                              .select2-container--default .select2-selection--single {
                                   height: calc(2.25rem + 2px);
                                   border: 1px solid #ced4da;
                                   border-radius: .25rem;
                                   padding: .375rem .75rem;
                              }

                              .select2-container--default .select2-selection--single .select2-selection__rendered {
                                   line-height: 1.4rem;
                                   padding-left: 0;
                                   color: #495057;
                              }

                              .select2-container--default .select2-selection--single .select2-selection__arrow {
                                   height: calc(2.25rem + 2px);
                                   right: 6px;
                              }

                              @media (max-width:576px) {
                                   .update-project-page .title-wrap {
                                        padding: 1rem 1rem 0;
                                   }

                                   .update-project-page .title-divider {
                                        margin: 8px 1rem 12px;
                                   }
                              }
                         </style>

                         <?php
                         $project     = isset($project) && is_object($project) ? $project : null;
                         $categories  = isset($categories) && is_array($categories) ? $categories : [];
                         $clients     = isset($clients) && is_array($clients) ? $clients : [];

                         $projectID          = $project->projectID          ?? '';
                         $projectDescription = $project->projectDescription ?? '';
                         $projectCategory    = $project->projectCategory    ?? '';
                         $projectClient      = $project->Customer           ?? '';
                         $contractDate       = $project->contractDate       ?? '';
                         $projectCost        = $project->projectCost        ?? 0;
                         $contactPerson      = $project->contactPerson      ?? '';
                         $otherDetails       = $project->otherDetails       ?? '';
                         $projectAddress     = $project->Address            ?? '';
                         $projectCustID      = $project->CustID             ?? '';

                         $categoryOptions = [];
                         foreach ($categories as $row) {
                              if (is_object($row) && isset($row->Category)) {
                                   $name = (string)$row->Category;
                                   if ($name !== '' && !array_key_exists($name, $categoryOptions)) {
                                        $categoryOptions[$name] = $name;
                                   }
                              }
                         }
                         if ($projectCategory !== '' && !array_key_exists($projectCategory, $categoryOptions)) {
                              $categoryOptions = [$projectCategory => $projectCategory] + $categoryOptions;
                         }
                         ?>

                         <div class="row">
                              <div class="col-12">
                                   <div class="title-wrap">
                                        <h4 class="page-title">
                                             Update Project<br>
                                             <span class="badge badge-purple title-sub">Adjust project metadata</span>
                                        </h4>
                                   </div>
                                   <hr class="title-divider">
                              </div>
                         </div>

                         <?php if ($project): ?>
                              <div class="row">
                                   <div class="col-lg-8">
                                        <div class="card shadow-sm">
                                             <div class="card-header d-flex align-items-center justify-content-between">
                                                  <h5 class="mb-0">Project Details</h5>
                                                  <a href="<?= base_url(); ?>Page/projectList" class="btn btn-outline-secondary btn-sm">
                                                       <i class="mdi mdi-chevron-left mr-1"></i> Back to Projects
                                                  </a>
                                             </div>
                                             <div class="card-body">
                                                  <form class="needs-validation" method="post" action="<?= base_url(); ?>Page/updateProject?id=<?= htmlspecialchars($projectID, ENT_QUOTES, 'UTF-8'); ?>" novalidate>
                                                       <input type="hidden" name="id" value="<?= htmlspecialchars($projectID, ENT_QUOTES, 'UTF-8'); ?>">
                                                       <input type="hidden" name="CustID" id="CustID" value="<?= htmlspecialchars($projectCustID, ENT_QUOTES, 'UTF-8'); ?>">
                                                       <input type="hidden" name="Customer" id="Customer" value="<?= htmlspecialchars($projectClient, ENT_QUOTES, 'UTF-8'); ?>">

                                                       <div class="form-group">
                                                            <label class="form-label req" for="project-description">Project</label>
                                                            <input type="text"
                                                                 class="form-control"
                                                                 id="project-description"
                                                                 name="projectDescription"
                                                                 value="<?= htmlspecialchars($projectDescription, ENT_QUOTES, 'UTF-8'); ?>"
                                                                 required>
                                                            <div class="invalid-feedback">Please enter the project name.</div>
                                                       </div>

                                                       <div class="form-row">
                                                            <div class="form-group col-md-6">
                                                                 <label class="form-label req" for="project-category">Category</label>
                                                                 <select class="form-control" id="project-category" name="projectCategory" required>
                                                                      <option value="">— Select a category —</option>
                                                                      <?php foreach ($categoryOptions as $value => $label): ?>
                                                                           <option value="<?= htmlspecialchars($value, ENT_QUOTES, 'UTF-8'); ?>" <?= $value === $projectCategory ? 'selected' : ''; ?>>
                                                                                <?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8'); ?>
                                                                           </option>
                                                                      <?php endforeach; ?>
                                                                 </select>
                                                                 <div class="invalid-feedback">Select a category.</div>
                                                            </div>

                                                            <div class="form-group col-md-6">
                                                                 <label class="form-label" for="clientSelect">Client</label>
                                                                 <select class="form-control" id="clientSelect">
                                                                      <option value="">— Search and select client —</option>
                                                                      <?php if (!empty($clients)): ?>
                                                                           <?php foreach ($clients as $client): ?>
                                                                                <option
                                                                                     value="<?= htmlspecialchars($client->CustID, ENT_QUOTES, 'UTF-8'); ?>"
                                                                                     data-customer="<?= htmlspecialchars($client->Customer, ENT_QUOTES, 'UTF-8'); ?>"
                                                                                     data-address="<?= htmlspecialchars($client->Address, ENT_QUOTES, 'UTF-8'); ?>"
                                                                                     data-contactperson="<?= htmlspecialchars($client->ContactPerson, ENT_QUOTES, 'UTF-8'); ?>"
                                                                                     <?= ((string)$projectCustID === (string)$client->CustID) ? 'selected' : ''; ?>>
                                                                                     <?= htmlspecialchars($client->Customer, ENT_QUOTES, 'UTF-8'); ?>
                                                                                     <?php if (!empty($client->Address)): ?>
                                                                                          — <?= htmlspecialchars($client->Address, ENT_QUOTES, 'UTF-8'); ?>
                                                                                     <?php endif; ?>
                                                                                </option>
                                                                           <?php endforeach; ?>
                                                                      <?php endif; ?>
                                                                 </select>
                                                            </div>
                                                       </div>

                                                       <div class="form-row">
                                                            <div class="form-group col-md-6">
                                                                 <label class="form-label req" for="contract-date">Contract Date</label>
                                                                 <input type="date"
                                                                      class="form-control"
                                                                      id="contract-date"
                                                                      name="contractDate"
                                                                      value="<?= !empty($contractDate) ? date('Y-m-d', strtotime($contractDate)) : ''; ?>"
                                                                      required>
                                                                 <div class="invalid-feedback">Provide a contract date.</div>
                                                            </div>

                                                            <div class="form-group col-md-6">
                                                                 <label class="form-label" for="project-cost">Project Cost</label>
                                                                 <div class="input-group">
                                                                      <div class="input-group-prepend"><span class="input-group-text">₱</span></div>
                                                                      <input type="text"
                                                                           class="form-control"
                                                                           id="project-cost"
                                                                           name="projectCost"
                                                                           inputmode="decimal"
                                                                           value="<?= htmlspecialchars(number_format((float)$projectCost, 2, '.', ''), ENT_QUOTES, 'UTF-8'); ?>"
                                                                           placeholder="0.00">
                                                                 </div>
                                                                 <small class="form-text">Auto-formats to currency on blur.</small>
                                                            </div>
                                                       </div>

                                                       <div class="form-group">
                                                            <label class="form-label" for="project-contact">Contact Person / Contact Nos.</label>
                                                            <input type="text"
                                                                 class="form-control"
                                                                 id="project-contact"
                                                                 name="contactPerson"
                                                                 value="<?= htmlspecialchars($contactPerson, ENT_QUOTES, 'UTF-8'); ?>">
                                                       </div>

                                                       <div class="form-group">
                                                            <label class="form-label" for="project-address">Address</label>
                                                            <input type="text"
                                                                 class="form-control"
                                                                 id="project-address"
                                                                 name="Address"
                                                                 value="<?= htmlspecialchars($projectAddress, ENT_QUOTES, 'UTF-8'); ?>">
                                                       </div>

                                                       <div class="form-group">
                                                            <label class="form-label" for="project-other-details">Other Details</label>
                                                            <textarea class="form-control"
                                                                 id="project-other-details"
                                                                 name="otherDetails"
                                                                 rows="3"
                                                                 placeholder="Key contract terms, deliverables, notes, etc."><?= htmlspecialchars($otherDetails, ENT_QUOTES, 'UTF-8'); ?></textarea>
                                                       </div>

                                                       <div class="form-actions">
                                                            <button type="reset" class="btn btn-warning" id="resetUpdateProjectForm">
                                                                 <i class="mdi mdi-refresh mr-1"></i> Reset
                                                            </button>
                                                            <button type="submit" name="submit" class="btn btn-primary">Save Changes</button>
                                                       </div>
                                                  </form>
                                             </div>
                                        </div>
                                   </div>

                                   <div class="col-lg-4">
                                        <div class="card shadow-sm">
                                             <div class="card-header">
                                                  <h5 class="mb-0">Summary</h5>
                                             </div>
                                             <div class="card-body">
                                                  <p class="mb-1 text-muted">Project</p>
                                                  <h5><?= htmlspecialchars($projectDescription, ENT_QUOTES, 'UTF-8'); ?></h5>

                                                  <p class="mb-1 text-muted">Client</p>
                                                  <h6 id="summary-client"><?= htmlspecialchars($projectClient, ENT_QUOTES, 'UTF-8'); ?></h6>

                                                  <p class="mb-1 text-muted">Category</p>
                                                  <h6><?= htmlspecialchars($projectCategory, ENT_QUOTES, 'UTF-8'); ?></h6>

                                                  <p class="mb-1 text-muted">Contract Date</p>
                                                  <h6><?= !empty($contractDate) ? date('F d, Y', strtotime($contractDate)) : '-'; ?></h6>

                                                  <p class="mb-1 text-muted">Cost</p>
                                                  <h5><?= number_format((float)$projectCost, 2); ?></h5>

                                                  <p class="mb-1 text-muted">Address</p>
                                                  <h6 id="summary-address"><?= htmlspecialchars($projectAddress, ENT_QUOTES, 'UTF-8'); ?></h6>
                                             </div>
                                        </div>
                                   </div>
                              </div>
                         <?php else: ?>
                              <div class="alert alert-warning" role="alert">
                                   Unable to find the requested project. It may have been removed.
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
     <script src="<?= base_url(); ?>assets/libs/select2/select2.min.js"></script>

     <script>
          (function() {
               'use strict';

               var form = document.querySelector('.needs-validation');
               if (form) {
                    form.addEventListener('submit', function(e) {
                         if (!form.checkValidity()) {
                              e.preventDefault();
                              e.stopPropagation();
                         }
                         form.classList.add('was-validated');
                    }, false);
               }

               var cost = document.getElementById('project-cost');
               if (cost) {
                    cost.addEventListener('blur', function() {
                         var v = (cost.value || '').toString().replace(/[^\d.]/g, '');
                         if (v) {
                              var num = parseFloat(v);
                              if (!isNaN(num)) {
                                   cost.value = num.toLocaleString('en-PH', {
                                        minimumFractionDigits: 2,
                                        maximumFractionDigits: 2
                                   });
                              }
                         }
                    });
                    cost.addEventListener('input', function() {
                         this.value = this.value.replace(/[^\d.]/g, '');
                    });
               }

               if (window.jQuery && jQuery.fn && typeof jQuery.fn.select2 === 'function') {
                    jQuery('#clientSelect').select2({
                         width: '100%',
                         placeholder: '— Search and select client —',
                         allowClear: true
                    });

                    jQuery('#clientSelect').on('change', function() {
                         var selected = jQuery(this).find(':selected');

                         var custId = selected.val() || '';
                         var customer = selected.data('customer') || '';
                         var address = selected.data('address') || '';
                         var contactPerson = selected.data('contactperson') || '';

                         jQuery('#CustID').val(custId);
                         jQuery('#Customer').val(customer);
                         jQuery('#project-address').val(address);
                         jQuery('#summary-client').text(customer);
                         jQuery('#summary-address').text(address);

                         if (contactPerson && !jQuery('#project-contact').val()) {
                              jQuery('#project-contact').val(contactPerson);
                         }
                    });

                    jQuery('#resetUpdateProjectForm').on('click', function() {
                         setTimeout(function() {
                              var selected = jQuery('#clientSelect').find(':selected');
                              if (selected.length) {
                                   jQuery('#clientSelect').trigger('change');
                              }
                         }, 0);
                    });
               }
          })();
     </script>
</body>

</html>