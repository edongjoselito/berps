<!DOCTYPE html>
<html lang="en">

<?php include('includes/head.php'); ?>

<body>

     <div id="wrapper">

          <?php include('includes/top-nav-bar.php'); ?>
          <?php include('includes/sidebar.php'); ?>

          <div class="content-page">
               <div class="content">
                    <div class="container-fluid update-client-page berps-form-page berps-page">

                         <?php
                         $client = isset($data[0]) ? $data[0] : null;
                         ?>

                         <header class="berps-page-header">
                              <div class="berps-page-header__content">
                                   <span class="berps-page-header__eyebrow">Clients</span>
                                   <h1 class="berps-page-title">Update Client</h1>
                                   <p class="berps-page-subtitle">Maintain the company profile and primary contact information.</p>
                              </div>
                              <div class="berps-page-header__actions">
                                   <a href="<?= base_url(); ?>Page/clientList" class="btn btn-outline-secondary">
                                        <i class="mdi mdi-arrow-left mr-1" aria-hidden="true"></i>Back to Client List
                                   </a>
                              </div>
                         </header>

                         <div class="berps-form-card">
                              <div class="berps-form-card__header">
                                   <div>
                                        <h2 class="berps-form-card__title">Client details</h2>
                                        <p class="berps-form-card__copy">Review identifiers carefully; the client ID is read-only.</p>
                                   </div>
                              </div>
                              <div class="berps-form-card__body">
                                   <?php if ($client): ?>
                                        <form class="needs-validation" method="post" novalidate>
                                             <section class="berps-form-section">
                                                  <div class="berps-form-section__header">
                                                       <h3 class="berps-form-section__title">Company information</h3>
                                                  </div>
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
                                                            <label class="berps-required" for="client_name">Client</label>
                                                            <input type="text"
                                                                 class="form-control"
                                                                 id="client_name"
                                                                 name="Customer"
                                                                 value="<?= htmlspecialchars((string) $client->Customer, ENT_QUOTES, 'UTF-8'); ?>"
                                                                 required>
                                                       </div>
                                                  </div>

                                                  <div class="form-group">
                                                       <label class="berps-required" for="client_address">Address</label>
                                                       <input type="text"
                                                            class="form-control"
                                                            id="client_address"
                                                            name="Address"
                                                            value="<?= htmlspecialchars((string) $client->Address, ENT_QUOTES, 'UTF-8'); ?>"
                                                            required>
                                                  </div>
                                             </section>

                                             <section class="berps-form-section">
                                                  <div class="berps-form-section__header">
                                                       <h3 class="berps-form-section__title">Contact information</h3>
                                                  </div>
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
                                             </section>

                                             <div class="berps-form-actions">
                                                  <a href="<?= base_url(); ?>Page/clientList" class="btn btn-outline-secondary">Cancel</a>
                                                  <button type="reset" name="resettask" class="btn btn-outline-secondary">
                                                       <i class="mdi mdi-refresh mr-1" aria-hidden="true"></i>Reset
                                                  </button>
                                                  <button type="submit" name="updateclient" value="1" class="btn btn-primary">
                                                       <i class="mdi mdi-content-save mr-1" aria-hidden="true"></i>Update Client
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

</body>

</html>
