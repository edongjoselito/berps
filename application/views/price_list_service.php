<!DOCTYPE html>
<html lang="en">

<?php include('includes/head.php'); ?>

<body>

     <div id="wrapper">

          <?php include('includes/top-nav-bar.php'); ?>
          <?php include('includes/sidebar.php'); ?>

          <div class="content-page">
               <div class="content">
                    <div class="container-fluid service-price-list-page">
                         <style>

                              .service-price-list-page {
                                   --bg: #f5f7fb;
                                   --surface: rgba(255, 255, 255, 0.96);
                                   --surface-strong: #ffffff;
                                   --surface-soft: #f8fbff;
                                   --line: #e4ebf4;
                                   --line-strong: #cfdbea;
                                   --text: #142235;
                                   --text-soft: #617489;
                                   --text-faint: #8ea0b5;
                                   --primary: #2563eb;
                                   --primary-2: #1d4ed8;
                                   --primary-soft: #eaf2ff;
                                   --success: #059669;
                                   --success-soft: #ecfdf5;
                                   --warning: #d97706;
                                   --warning-soft: #fff7ed;
                                   --danger: #e11d48;
                                   --danger-soft: #fff1f2;
                                   --shadow: 0 16px 40px rgba(15, 23, 42, 0.06);
                                   --shadow-soft: 0 8px 24px rgba(15, 23, 42, 0.04);
                                   --radius-xl: 16px;
                                   --radius-lg: 12px;
                                   --radius-md: 10px;
                                   --radius-sm: 8px;
                                   --font-body: var(--font-primary);
                                   --font-head: var(--font-primary);
                                   font-family: var(--font-primary, Montserrat, Segoe UI, Arial, sans-serif);
                                   min-height: 100vh;
                                   padding-bottom: 100px;
                              }

                              .service-price-list-page .content {
                                   margin-bottom: 40px;
                              }

                              .service-price-list-page .page-header-card {
                                   border: none;
                                   border-radius: var(--radius-xl);
                                   box-shadow: var(--shadow-soft);
                                   margin-bottom: 28px;
                                   overflow: hidden;
                              }

                              .service-price-list-page .page-header-card .card-header {
                                   background: linear-gradient(135deg, var(--primary), var(--primary-2));
                                   color: #fff;
                                   border-bottom: none;
                                   padding: 28px 32px;
                              }

                              .service-price-list-page .page-header-card .card-header h3 {
                                   margin: 0;
                                   font-size: 1.6rem;
                                   font-weight: 700;
                              }

                              .service-price-list-page .page-header-card .card-header p {
                                   margin-bottom: 0;
                                   color: rgba(255, 255, 255, 0.85);
                              }

                              .service-price-list-page .page-header-card .card-body {
                                   padding: 24px 32px;
                                   background: #fff;
                              }

                              .service-price-list-page .page-header-card .breadcrumb {
                                   background: transparent;
                                   padding: 0;
                                   margin-bottom: 0;
                              }

                              .service-price-list-page .page-header-actions {
                                   display: flex;
                                   align-items: center;
                                   gap: 12px;
                              }

                              .service-price-list-page .btn-add-service {
                                   background: rgba(255, 255, 255, 0.92);
                                   color: var(--primary-2);
                                   font-weight: 700;
                                   border: none;
                                   border-radius: 999px;
                                   padding: 10px 22px;
                                   display: inline-flex;
                                   align-items: center;
                                   gap: 8px;
                                   transition: transform 0.15s ease, box-shadow 0.15s ease;
                              }

                              .service-price-list-page .btn-add-service:hover {
                                   transform: translateY(-2px);
                                   box-shadow: 0 10px 24px rgba(15, 23, 42, 0.16);
                              }

                              .service-price-list-page .service-table-card {
                                   border: none;
                                   border-radius: var(--radius-xl);
                                   box-shadow: var(--shadow-soft);
                              }

                              .service-price-list-page .service-table-card .card-header {
                                   border-bottom: none;
                                   padding: 24px 32px 12px;
                                   background: transparent;
                              }

                              .service-price-list-page .service-table-card .card-title {
                                   margin-bottom: 0;
                                   font-size: 1.2rem;
                                   font-weight: 700;
                              }

                              .service-price-list-page .service-table-card .card-subtitle {
                                   margin-bottom: 0;
                                   font-size: 0.875rem;
                              }

                              .service-price-list-page .service-table-card .card-body {
                                   padding: 0 32px 32px;
                              }

                              .service-price-list-page .service-table-card .table thead th {
                                   text-transform: uppercase;
                                   letter-spacing: 0.08em;
                                   font-size: 0.72rem;
                                   font-weight: 800;
                                   white-space: nowrap;
                                   color: var(--text-faint);
                                   border-top: none;
                                   border-bottom: 1px solid var(--line);
                              }

                              .service-price-list-page .service-table-card .table td {
                                   vertical-align: middle;
                                   border-color: var(--line);
                                   color: var(--text);
                              }

                              .service-price-list-page .action-column {
                                   text-align: center;
                              }

                              .service-price-list-page .action-actions {
                                   display: inline-flex;
                                   align-items: center;
                                   justify-content: center;
                                   gap: 8px;
                                   flex-wrap: wrap;
                              }

                              .service-price-list-page .action-column .action-link {
                                   font-weight: 700;
                                   text-decoration: none;
                                   transition: all 0.2s ease;
                                   padding: 6px 12px;
                                   border-radius: var(--radius-sm);
                              }

                              .service-price-list-page .action-column .action-link-edit {
                                   color: var(--primary);
                                   background: var(--primary-soft);
                              }

                              .service-price-list-page .action-column .action-link-edit:hover {
                                   background: #dbeafe;
                              }

                              .service-price-list-page .action-column .action-link-remove {
                                   color: var(--danger);
                                   background: var(--danger-soft);
                              }

                              .service-price-list-page .action-column .action-link-remove:hover {
                                   background: #fecdd3;
                              }

                              .service-price-list-page .price-value {
                                   font-weight: 700;
                                   color: var(--primary);
                              }

                              @media (max-width: 576px) {
                                   .service-price-list-page .page-header-card .card-header {
                                        padding: 24px;
                                   }

                                   .service-price-list-page .page-header-card .card-body {
                                        padding: 20px 24px;
                                   }

                                   .service-price-list-page .service-table-card .card-header {
                                        padding: 20px 24px 10px;
                                   }

                                   .service-price-list-page .service-table-card .card-body {
                                        padding: 0 24px 24px;
                                   }
                              }
                         </style>

                         <div class="card page-header-card">
                              <div class="card-header d-flex flex-column flex-md-row align-items-md-center justify-content-between">
                                   <div>
                                        <h3 class="mb-1" style="color: #fff;">Service Price List</h3>
                                        <p>Keep your service offerings and rates accessible to the team.</p>
                                   </div>
                                   <div class="page-header-actions mt-3 mt-md-0">
                                        <button type="button" class="btn btn-add-service" data-toggle="modal" data-target="#newServiceModal">
                                             <i class="mdi mdi-plus-circle-outline"></i>
                                             <span>Add Service</span>
                                        </button>
                                   </div>
                              </div>
                              <div class="card-body">
                                   <ol class="breadcrumb mb-0">
                                        <li class="breadcrumb-item">
                                             <a href="<?= base_url('Page/admin'); ?>">Home</a>
                                        </li>
                                        <li class="breadcrumb-item">Notes</li>
                                        <li class="breadcrumb-item active">Price List (Services)</li>
                                   </ol>
                              </div>
                         </div>

                         <div class="card service-table-card">
                              <div class="card-header">
                                   <h4 class="card-title">Available Services</h4>
                                   <p class="card-subtitle text-muted">Review, update, or remove services from the catalogue.</p>
                              </div>
                              <div class="card-body">
                                   <div class="table-responsive">
                                        <table id="service-table" class="table table-striped table-bordered w-100">
                                             <thead class="thead-light">
                                                  <tr>
                                                       <th>Type of Service</th>
                                                       <th>Sub Category / Plan</th>
                                                       <th>Details</th>
                                                       <th>Price</th>
                                                       <th class="action-column">Action</th>
                                                  </tr>
                                             </thead>
                                             <tbody>
                                                  <?php if (!empty($data)): ?>
                                                       <?php foreach ($data as $row): ?>
                                                            <?php
                                                            $serviceDescription = isset($row->FeesDescription) ? (string) $row->FeesDescription : '';
                                                            $serviceSubCategory = isset($row->subCategory) ? trim((string) $row->subCategory) : '';
                                                            $serviceDetails = isset($row->feeDetails) ? (string) $row->feeDetails : '';
                                                            $amountValue = isset($row->Amount) ? (string) $row->Amount : '';
                                                            $amountDisplay = is_numeric($amountValue) ? number_format((float) $amountValue, 2) : htmlspecialchars($amountValue, ENT_QUOTES, 'UTF-8');
                                                            $deleteUrl = base_url('Page/deleteFees?id=' . urlencode((string) $row->feesID));
                                                            $amountInputValue = is_numeric($amountValue) ? number_format((float) $amountValue, 2, '.', '') : '0.00';
                                                            ?>
                                                            <tr>
                                                                 <td><?= htmlspecialchars($serviceDescription, ENT_QUOTES, 'UTF-8'); ?></td>
                                                                 <td><?= $serviceSubCategory !== '' ? htmlspecialchars($serviceSubCategory, ENT_QUOTES, 'UTF-8') : '-'; ?></td>
                                                                 <td><?= $serviceDetails !== '' ? htmlspecialchars($serviceDetails, ENT_QUOTES, 'UTF-8') : '-'; ?></td>
                                                                 <td class="price-value"><?= $amountDisplay; ?></td>
                                                                 <td class="action-column">
                                                                      <div class="action-actions">
                                                                           <button
                                                                                type="button"
                                                                                class="btn btn-link p-0 action-link action-link-edit"
                                                                                data-toggle="modal"
                                                                                data-target="#editServiceModal"
                                                                                data-edit-service
                                                                                data-id="<?= (int) $row->feesID; ?>"
                                                                                data-description="<?= htmlspecialchars($serviceDescription, ENT_QUOTES, 'UTF-8'); ?>"
                                                                                data-sub-category="<?= htmlspecialchars($serviceSubCategory, ENT_QUOTES, 'UTF-8'); ?>"
                                                                                data-details="<?= htmlspecialchars($serviceDetails, ENT_QUOTES, 'UTF-8'); ?>"
                                                                                data-amount="<?= htmlspecialchars($amountInputValue, ENT_QUOTES, 'UTF-8'); ?>">
                                                                                Edit
                                                                           </button>
                                                                           <a class="action-link action-link-remove" href="<?= htmlspecialchars($deleteUrl, ENT_QUOTES, 'UTF-8'); ?>" onclick="return confirm('Remove this service from the price list?');">
                                                                                Remove
                                                                           </a>
                                                                      </div>
                                                                 </td>
                                                            </tr>
                                                       <?php endforeach; ?>
                                                  <?php else: ?>
                                                       <tr>
                                                            <td colspan="5" class="text-center text-muted py-4">No services have been recorded yet.</td>
                                                       </tr>
                                                  <?php endif; ?>
                                             </tbody>
                                        </table>
                                   </div>
                              </div>
                         </div>
                    </div>

                    <?php include('includes/footer.php'); ?>
               </div>
          </div>
     </div>

     <?php include('includes/themecustomizer.php'); ?>

     <div class="modal fade" id="newServiceModal" tabindex="-1" role="dialog" aria-hidden="true">
          <div class="modal-dialog modal-dialog-centered modal-lg">
               <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                         <h5 class="modal-title mb-0">New Service</h5>
                         <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                              <span aria-hidden="true">&times;</span>
                         </button>
                    </div>
                    <form class="needs-validation" method="post" novalidate>
                         <div class="modal-body">
                              <div class="form-group">
                                   <label for="serviceName">Service</label>
                                   <input type="text" class="form-control" name="FeesDescription" id="serviceName" required>
                              </div>
                              <div class="form-group">
                                   <label for="serviceSubCategory">Sub Category / Plan</label>
                                   <input type="text" class="form-control" name="subCategory" id="serviceSubCategory" placeholder="Optional plan or package name">
                              </div>
                              <div class="form-group">
                                   <label for="serviceDetails">Additional Details</label>
                                   <input type="text" class="form-control" name="feeDetails" id="serviceDetails" placeholder="Optional notes, inclusions, or coverage">
                              </div>
                              <div class="form-group">
                                   <label for="servicePrice">Price</label>
                                   <input type="text" class="form-control" name="Amount" id="servicePrice" required>
                              </div>
                         </div>
                         <div class="modal-footer">
                              <button type="submit" name="addservice" value="1" class="btn btn-primary">
                                   <i class="mdi mdi-content-save"></i> Save Service
                              </button>
                              <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                         </div>
                    </form>
               </div>
          </div>
     </div>

     <div class="modal fade" id="editServiceModal" tabindex="-1" role="dialog" aria-hidden="true">
          <div class="modal-dialog modal-dialog-centered modal-lg">
               <div class="modal-content">
                    <div class="modal-header bg-info text-white">
                         <h5 class="modal-title mb-0">Update Service</h5>
                         <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                              <span aria-hidden="true">&times;</span>
                         </button>
                    </div>
                    <form class="needs-validation" method="post" novalidate id="editServiceForm">
                         <input type="hidden" name="feesID" id="edit-service-id">
                         <div class="modal-body">
                              <div class="form-group">
                                   <label for="edit-service-name">Service</label>
                                   <input type="text" class="form-control" name="FeesDescription" id="edit-service-name" required>
                              </div>
                              <div class="form-group">
                                   <label for="edit-service-sub-category">Sub Category / Plan</label>
                                   <input type="text" class="form-control" name="subCategory" id="edit-service-sub-category" placeholder="Optional plan or package name">
                              </div>
                              <div class="form-group">
                                   <label for="edit-service-details">Additional Details</label>
                                   <input type="text" class="form-control" name="feeDetails" id="edit-service-details" placeholder="Optional notes, inclusions, or coverage">
                              </div>
                              <div class="form-group">
                                   <label for="edit-service-price">Price</label>
                                   <input type="text" class="form-control" name="Amount" id="edit-service-price" required>
                              </div>
                         </div>
                         <div class="modal-footer">
                              <button type="submit" name="updateservice" value="1" class="btn btn-info">
                                   <i class="mdi mdi-content-save-edit-outline"></i> Update Service
                              </button>
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
          (function($) {
               'use strict';

               $(function() {
                    var $serviceTable = $('#service-table');

                    if ($serviceTable.length) {
                         $serviceTable.DataTable({
                              responsive: true,
                              autoWidth: false,
                              order: [
                                   [0, 'asc']
                              ],
                              columnDefs: [{
                                   targets: -1,
                                   orderable: false,
                                   searchable: false
                              }]
                         });
                    }

                    $('#newServiceModal').on('hidden.bs.modal', function() {
                         var form = $(this).find('form')[0];
                         if (form) {
                              form.reset();
                              form.classList.remove('was-validated');
                         }
                    });

                    $(document).on('click', '[data-edit-service]', function() {
                         var $trigger = $(this);
                         $('#edit-service-id').val($trigger.data('id') || '');
                         $('#edit-service-name').val($trigger.data('description') || '');
                         $('#edit-service-sub-category').val($trigger.data('sub-category') || '');
                         $('#edit-service-details').val($trigger.data('details') || '');
                         $('#edit-service-price').val($trigger.data('amount') || '0.00');
                    });

                    $('#editServiceModal').on('hidden.bs.modal', function() {
                         var form = document.getElementById('editServiceForm');
                         if (form) {
                              form.reset();
                              form.classList.remove('was-validated');
                         }
                         $('#edit-service-id').val('');
                    });
               });
          })(jQuery);
     </script>

</body>

</html>