<!DOCTYPE html>
<html lang="en">

<?php include('includes/head.php'); ?>

<body>

     <div id="wrapper">

          <?php include('includes/top-nav-bar.php'); ?>
          <?php include('includes/sidebar.php'); ?>

          <div class="content-page">
               <div class="content">
                    <div class="container-fluid update-product-page">
                         <style>
                              .update-product-page .page-title-box {
                                   padding: 12px 0;
                                   margin-bottom: 18px;
                              }

                              .update-product-page .form-control {
                                   border-radius: 8px;
                              }

                              .update-product-page .card {
                                   border-radius: 16px;
                              }

                              .update-product-page .card-header {
                                   border-bottom: 1px solid rgba(0, 0, 0, 0.05);
                              }

                              .update-product-page .btn-light {
                                   background: #f5f7fb;
                                   border-color: #f5f7fb;
                              }

                              .update-product-page .btn-light:hover {
                                   background: #e9eefb;
                                   border-color: #e9eefb;
                              }
                         </style>

                         <div class="row">
                              <div class="col-12">
                                   <div class="page-title-box">
                                        <h4 class="page-title">Update Product<br />
                                             <span class="badge badge-purple mb-1">Maintain product catalog details</span>
                                        </h4>
                                        <div class="clearfix"></div>
                                        <hr style="border:0; height:2px; background:linear-gradient(to right, #4285F4 60%, #FBBC05 80%, #34A853 100%); border-radius:1px; margin:20px 0;" />
                                   </div>
                              </div>
                         </div>

                         <div class="row">
                              <div class="col-lg-8">
                                   <div class="card">
                                        <div class="card-header d-flex justify-content-between align-items-center">
                                             <h5 class="mb-0">Product Details</h5>
                                             <a href="<?= base_url(); ?>Page/productList" class="btn btn-sm btn-outline-primary">
                                                  <i class="mdi mdi-chevron-left mr-1"></i> Back to Products
                                             </a>
                                        </div>
                                        <div class="card-body">
                                             <form class="needs-validation" method="post" action="<?= base_url(); ?>Page/updateProduct?id=<?= $data[0]->itemID; ?>" novalidate>
                                                  <input type="hidden" name="id" value="<?= $data[0]->itemID; ?>">

                                                  <div class="form-row">
                                                       <div class="form-group col-md-4">
                                                            <label for="product-id">Item ID</label>
                                                            <input type="text" class="form-control" id="product-id" value="<?= $data[0]->itemID; ?>" readonly>
                                                       </div>
                                                       <div class="form-group col-md-4">
                                                            <label for="product-name">Product Name</label>
                                                            <input type="text" class="form-control" id="product-name" name="productName" value="<?= $data[0]->productName; ?>" required>
                                                       </div>
                                                       <div class="form-group col-md-4">
                                                            <label for="product-line">Product Line</label>
                                                            <input type="text" class="form-control" id="product-line" name="productLine" value="<?= $data[0]->productLine; ?>" required>
                                                       </div>
                                                  </div>

                                                  <div class="text-right">
                                                       <button type="submit" name="submit" class="btn btn-primary">Update Product</button>
                                                       <button type="reset" class="btn btn-light ml-2">Reset</button>
                                                  </div>
                                             </form>
                                        </div>
                                   </div>
                              </div>

                              <div class="col-lg-4">
                                   <div class="card">
                                        <div class="card-header">
                                             <h5 class="mb-0">Summary</h5>
                                        </div>
                                        <div class="card-body">
                                             <p class="mb-1 text-muted">Item ID</p>
                                             <h5><?= $data[0]->itemID; ?></h5>
                                             <p class="mb-1 text-muted">Product Name</p>
                                             <h6><?= $data[0]->productName; ?></h6>
                                             <p class="mb-1 text-muted">Product Line</p>
                                             <h6><?= $data[0]->productLine; ?></h6>
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
