<!DOCTYPE html>
<html lang="en">
<?php include('includes/head.php'); ?>

<body>
     <div id="wrapper">
          <?php include('includes/top-nav-bar.php'); ?>
          <?php include('includes/sidebar.php'); ?>

          <div class="content-page">
               <div class="content">
                    <div class="container-fluid update-project-page berps-form-page berps-page">

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

                         <header class="berps-page-header">
                              <div class="berps-page-header__content">
                                   <span class="berps-page-header__eyebrow">Projects</span>
                                   <h1 class="berps-page-title">Update Project</h1>
                                   <p class="berps-page-subtitle">Adjust the client, contract, cost, and supporting project information.</p>
                              </div>
                              <div class="berps-page-header__actions">
                                   <a href="<?= base_url(); ?>Page/projectList" class="btn btn-outline-secondary">
                                        <i class="mdi mdi-arrow-left mr-1" aria-hidden="true"></i>Back to Project List
                                   </a>
                              </div>
                         </header>

                         <?php if ($project): ?>
                              <div class="row">
                                   <div class="col-lg-8">
                                        <div class="berps-form-card">
                                             <div class="berps-form-card__header">
                                                  <div>
                                                       <h2 class="berps-form-card__title">Project details</h2>
                                                       <p class="berps-form-card__copy">Fields marked with an asterisk are required.</p>
                                                  </div>
                                             </div>
                                             <div class="berps-form-card__body">
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

                                                       <div class="berps-form-actions">
                                                            <a href="<?= base_url(); ?>Page/projectList" class="btn btn-outline-secondary">Cancel</a>
                                                            <button type="reset" class="btn btn-outline-secondary" id="resetUpdateProjectForm">
                                                                 <i class="mdi mdi-refresh mr-1" aria-hidden="true"></i>Reset
                                                            </button>
                                                            <button type="submit" name="submit" class="btn btn-primary">
                                                                 <i class="mdi mdi-content-save-outline mr-1" aria-hidden="true"></i>Save Changes
                                                            </button>
                                                       </div>
                                                  </form>
                                             </div>
                                        </div>
                                   </div>

                                   <div class="col-lg-4">
                                        <aside class="berps-form-card">
                                             <div class="berps-form-card__header">
                                                  <div>
                                                       <h2 class="berps-form-card__title">Project summary</h2>
                                                       <p class="berps-form-card__copy">Current saved information.</p>
                                                  </div>
                                             </div>
                                             <div class="berps-form-card__body">
                                                  <dl class="berps-detail-list">
                                                       <div class="berps-detail-list__item"><dt>Project</dt><dd><?= htmlspecialchars($projectDescription, ENT_QUOTES, 'UTF-8'); ?></dd></div>
                                                       <div class="berps-detail-list__item"><dt>Client</dt><dd id="summary-client"><?= htmlspecialchars($projectClient, ENT_QUOTES, 'UTF-8'); ?></dd></div>
                                                       <div class="berps-detail-list__item"><dt>Category</dt><dd><?= htmlspecialchars($projectCategory, ENT_QUOTES, 'UTF-8'); ?></dd></div>
                                                       <div class="berps-detail-list__item"><dt>Contract date</dt><dd><?= !empty($contractDate) ? date('F d, Y', strtotime($contractDate)) : '-'; ?></dd></div>
                                                       <div class="berps-detail-list__item"><dt>Cost</dt><dd>PHP <?= number_format((float)$projectCost, 2); ?></dd></div>
                                                       <div class="berps-detail-list__item"><dt>Address</dt><dd id="summary-address"><?= htmlspecialchars($projectAddress, ENT_QUOTES, 'UTF-8'); ?></dd></div>
                                                  </dl>
                                             </div>
                                        </aside>
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
