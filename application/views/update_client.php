<!DOCTYPE html>
<html lang="en">

<?php include('includes/head.php'); ?>

<body>

     <div id="wrapper">

          <?php include('includes/top-nav-bar.php'); ?>
          <?php include('includes/sidebar.php'); ?>

          <div class="content-page">
               <div class="content">
                    <div class="container-fluid update-client-page">
                         <style>
                              .update-client-page .breadcrumb {
                                   background: transparent;
                                   padding: 0;
                                   margin-bottom: 1.5rem;
                              }

                              .update-client-page .card {
                                   border: none;
                                   border-radius: 16px;
                                   box-shadow: 0 16px 32px rgba(15, 23, 42, 0.08);
                              }

                              .update-client-page .card-header {
                                   border-bottom: 1px solid rgba(15, 23, 42, 0.08);
                                   background: linear-gradient(130deg, #4c6ef5, #15aabf);
                                   color: #fff;
                                   padding: 20px 28px;
                                   border-top-left-radius: 16px;
                                   border-top-right-radius: 16px;
                              }

                              .update-client-page .card-header h4 {
                                   margin: 0;
                                   font-weight: 600;
                                   font-size: 1.3rem;
                              }

                              .update-client-page .card-body {
                                   padding: 30px 32px;
                              }

                              .update-client-page .form-control {
                                   border-radius: 10px;
                              }

                              .update-client-page .form-section-title {
                                   font-weight: 600;
                                   margin-bottom: 1rem;
                                   color: #343a40;
                              }

                              .update-client-page .btn + .btn {
                                   margin-left: 10px;
                              }
                         </style>

                         <?php
                         $client = isset($data[0]) ? $data[0] : null;
                         ?>

                         <div class="row">
                              <div class="col-12">
                                   <nav aria-label="breadcrumb">
                                        <ol class="breadcrumb pl-0">
                                             <li class="breadcrumb-item">
                                                  <a href="<?= base_url(); ?>Page/admin">
                                                       <i class="mdi mdi-home-outline"></i> Home
                                                  </a>
                                             </li>
                                             <li class="breadcrumb-item">
                                                  <a href="<?= base_url(); ?>Page/clientList">Client Directory</a>
                                             </li>
                                             <li class="breadcrumb-item active" aria-current="page">Update Client</li>
                                        </ol>
                                   </nav>
                              </div>
                         </div>

                         <div class="row">
                              <div class="col-12">
                                   <div class="card">
                                        <div class="card-header d-flex align-items-center justify-content-between">
                                             <h4>Update Client</h4>
                                             <a href="<?= base_url(); ?>Page/clientList" class="btn btn-light btn-sm text-primary">
                                                  <i class="mdi mdi-arrow-left"></i> Back to Client List
                                             </a>
                                        </div>
                                        <div class="card-body">
                                             <?php if ($client): ?>
                                                  <form class="needs-validation" method="post" novalidate>
                                                       <h5 class="form-section-title">Client Details</h5>
                                                       <div class="form-row">
                                                            <div class="form-group col-md-4">
                                                                 <label for="client_id">Client ID</label>
                                                                 <input type="text"
                                                                      class="form-control"
                                                                      id="client_id"
                                                                      name="CustID"
                                                                      value="<?= htmlspecialchars((string) $client->CustID, ENT_QUOTES, 'UTF-8'); ?>"
                                                                      readonly
                                                                      required>
                                                            </div>
                                                            <div class="form-group col-md-8">
                                                                 <label for="client_name">Client</label>
                                                                 <input type="text"
                                                                      class="form-control"
                                                                      id="client_name"
                                                                      name="Customer"
                                                                      value="<?= htmlspecialchars((string) $client->Customer, ENT_QUOTES, 'UTF-8'); ?>"
                                                                      required>
                                                            </div>
                                                       </div>

                                                       <div class="form-group">
                                                            <label for="client_address">Address</label>
                                                            <input type="text"
                                                                 class="form-control"
                                                                 id="client_address"
                                                                 name="Address"
                                                                 value="<?= htmlspecialchars((string) $client->Address, ENT_QUOTES, 'UTF-8'); ?>"
                                                                 required>
                                                       </div>

                                                       <h5 class="form-section-title">Contact Information</h5>
                                                       <div class="form-row">
                                                            <div class="form-group col-md-4">
                                                                 <label for="contact_person">Contact Person</label>
                                                                 <input type="text"
                                                                      class="form-control"
                                                                      id="contact_person"
                                                                      name="ContactPerson"
                                                                      value="<?= htmlspecialchars((string) $client->ContactPerson, ENT_QUOTES, 'UTF-8'); ?>">
                                                            </div>
                                                            <div class="form-group col-md-4">
                                                                 <label for="contact_number">Contact Nos.</label>
                                                                 <input type="text"
                                                                      class="form-control"
                                                                      id="contact_number"
                                                                      name="Contact"
                                                                      value="<?= htmlspecialchars((string) $client->ContactNos, ENT_QUOTES, 'UTF-8'); ?>">
                                                            </div>
                                                            <div class="form-group col-md-4">
                                                                 <label for="company_email">E-mail</label>
                                                                 <input type="email"
                                                                      class="form-control"
                                                                      id="company_email"
                                                                      name="CompanyEmail"
                                                                      value="<?= htmlspecialchars((string) $client->CompanyEmail, ENT_QUOTES, 'UTF-8'); ?>">
                                                            </div>
                                                       </div>

                                                       <div class="d-flex justify-content-end">
                                                            <button type="submit" name="updateclient" value="1" class="btn btn-primary">
                                                                 <i class="mdi mdi-content-save"></i> Update Client
                                                            </button>
                                                            <button type="reset" name="resettask" class="btn btn-outline-secondary">
                                                                 <i class="mdi mdi-refresh"></i> Reset
                                                            </button>
                                                       </div>
                                                  </form>
                                             <?php else: ?>
                                                  <div class="alert alert-warning mb-0" role="alert">
                                                       The client information could not be loaded. Please go back to the <a href="<?= base_url(); ?>Page/clientList" class="alert-link">client list</a> and try again.
                                                  </div>
                                             <?php endif; ?>
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

</body>

</html>
