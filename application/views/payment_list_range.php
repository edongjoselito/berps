<!DOCTYPE html>
<html lang="en">

<?php include('includes/head.php'); ?>

<body>

     <div id="wrapper">

          <?php include('includes/top-nav-bar.php'); ?>
          <?php include('includes/sidebar.php'); ?>

          <div class="content-page">
               <div class="content">
                    <div class="container-fluid payment-range-page">
                         <style>
                              .payment-range-page .page-title-box {
                                   padding: 12px 0;
                                   margin-bottom: 18px;
                              }

                              .payment-range-page .date-range-card {
                                   border-radius: 16px;
                              }

                              .payment-range-page .date-range-card .input-group-text {
                                   min-width: 58px;
                                   justify-content: center;
                              }

                              .payment-range-page .btn-primary {
                                   border-radius: 8px;
                              }
                         </style>

                         <div class="row">
                              <div class="col-12">
                                   <div class="page-title-box">
                                        <h4 class="page-title">Payments By Date Range<br />
                                             <span class="badge badge-purple mb-1">Filter records by custom dates</span>
                                        </h4>
                                        <div class="clearfix"></div>
                                        <hr style="border:0; height:2px; background:linear-gradient(to right, #4285F4 60%, #FBBC05 80%, #34A853 100%); border-radius:1px; margin:20px 0;" />
                                   </div>
                              </div>
                         </div>

                         <div class="row justify-content-center">
                              <div class="col-lg-6 col-md-8">
                                   <div class="card date-range-card">
                                        <div class="card-header">
                                             <h5 class="mb-0">Select Date Range</h5>
                                        </div>
                                        <div class="card-body">
                                             <form method="post" action="<?= base_url(); ?>Page/paymentRangeData" class="needs-validation" novalidate>
                                                  <div class="form-group">
                                                       <label for="range-from">From</label>
                                                       <div class="input-group">
                                                            <div class="input-group-prepend">
                                                                 <span class="input-group-text">From</span>
                                                            </div>
                                                            <input type="date" class="form-control" id="range-from" name="from" required>
                                                       </div>
                                                  </div>

                                                  <div class="form-group">
                                                       <label for="range-to">To</label>
                                                       <div class="input-group">
                                                            <div class="input-group-prepend">
                                                                 <span class="input-group-text">To</span>
                                                            </div>
                                                            <input type="date" class="form-control" id="range-to" name="to" required>
                                                       </div>
                                                  </div>

                                                  <div class="text-right">
                                                       <button name="submit" type="submit" class="btn btn-primary">Submit</button>
                                                       <a href="<?= base_url(); ?>Page/paymentList" class="btn btn-light ml-2">Back</a>
                                                  </div>
                                             </form>
                                        </div>
                                   </div>
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
