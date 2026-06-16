<!DOCTYPE html>
<html lang="en">

<?php include('includes/head.php'); ?>

<body>

     <div id="wrapper">

          <?php include('includes/top-nav-bar.php'); ?>
          <?php include('includes/sidebar.php'); ?>

          <div class="content-page">
               <div class="content">
                    <div class="container-fluid product-delivery-page">
                         <style>
                              .product-delivery-page .page-title-box {
                                   padding: 12px 0;
                                   margin-bottom: 18px;
                              }

                              .product-delivery-page .form-control {
                                   border-radius: 8px;
                              }

                              .product-delivery-page .card {
                                   border-radius: 16px;
                              }

                              .product-delivery-page .card-header {
                                   border-bottom: 1px solid rgba(0, 0, 0, 0.05);
                              }

                              .product-delivery-page .btn-light {
                                   background: #f5f7fb;
                                   border-color: #f5f7fb;
                              }

                              .product-delivery-page .btn-light:hover {
                                   background: #e9eefb;
                                   border-color: #e9eefb;
                              }
                         </style>

                         <div class="row">
                              <div class="col-12">
                                   <div class="page-title-box">
                                        <h4 class="page-title">Record Product Delivery<br />
                                             <span class="badge badge-purple mb-1">Capture incoming stock details</span>
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
                                             <h5 class="mb-0">Delivery Details</h5>
                                             <a href="<?= base_url(); ?>Page/productList" class="btn btn-sm btn-outline-primary">
                                                  <i class="mdi mdi-chevron-left mr-1"></i> Back to Products
                                             </a>
                                        </div>
                                        <div class="card-body">
                                             <form class="needs-validation" method="post" action="<?= base_url(); ?>Page/productDelivery?id=<?= $data[0]->itemID; ?>" novalidate>
                                                  <input type="hidden" name="id" value="<?= $data[0]->itemID; ?>">
                                                  <div class="form-row">
                                                       <div class="form-group col-md-4">
                                                            <label for="delivery-item-id">Item ID</label>
                                                            <input type="text" class="form-control" id="delivery-item-id" name="InvoiceNo" value="<?= $data[0]->itemID; ?>" readonly required>
                                                       </div>
                                                       <div class="form-group col-md-4">
                                                            <label for="delivery-product-name">Product Name</label>
                                                            <input type="text" class="form-control" id="delivery-product-name" value="<?= $data[0]->productName; ?>" readonly required>
                                                       </div>
                                                       <div class="form-group col-md-4">
                                                            <label for="delivery-product-line">Product Line</label>
                                                            <input type="text" class="form-control" id="delivery-product-line" value="<?= $data[0]->productLine; ?>" readonly required>
                                                       </div>
                                                  </div>

                                                  <div class="form-row">
                                                       <div class="form-group col-md-4">
                                                            <label for="delivery-barcode">Barcode</label>
                                                            <input type="text" class="form-control" id="delivery-barcode" name="productCode" required>
                                                       </div>
                                                       <div class="form-group col-md-4">
                                                            <label for="delivery-supplier">Supplier</label>
                                                            <input type="text" class="form-control" id="delivery-supplier" name="Supplier">
                                                       </div>
                                                       <div class="form-group col-md-4">
                                                            <label for="delivery-serial">Serial No.</label>
                                                            <input type="text" class="form-control" id="delivery-serial" name="serialNo">
                                                       </div>
                                                  </div>

                                                  <div class="form-row">
                                                       <div class="form-group col-md-4">
                                                            <label for="delivery-model">Model</label>
                                                            <input type="text" class="form-control" id="delivery-model" name="model">
                                                       </div>
                                                       <div class="form-group col-md-4">
                                                            <label for="delivery-brand">Brand</label>
                                                            <input type="text" class="form-control" id="delivery-brand" name="brand">
                                                       </div>
                                                       <div class="form-group col-md-4">
                                                            <label for="delivery-qty">Qty Delivered</label>
                                                            <input type="number" class="form-control" id="delivery-qty" name="QtyDelivered" min="0" step="1" required>
                                                       </div>
                                                  </div>

                                                  <div class="form-row">
                                                       <div class="form-group col-md-4">
                                                            <label for="delivery-unit">Unit</label>
                                                            <input type="text" class="form-control" id="delivery-unit" name="prodUnit" required>
                                                       </div>
                                                       <div class="form-group col-md-4">
                                                            <label for="delivery-purchase">Purchase Price</label>
                                                            <input type="number" class="form-control" id="delivery-purchase" name="purchasePrice" min="0" step="0.01" required>
                                                       </div>
                                                       <div class="form-group col-md-4">
                                                            <label for="delivery-selling">Selling Price</label>
                                                            <input type="number" class="form-control" id="delivery-selling" name="sellingPrice" min="0" step="0.01" required>
                                                       </div>
                                                  </div>

                                                  <div class="form-group">
                                                       <label for="delivery-notes">Additional Notes</label>
                                                       <input type="text" class="form-control" id="delivery-notes" name="Notes">
                                                  </div>

                                                  <div class="text-right">
                                                       <button type="submit" name="submit" class="btn btn-primary">Save Delivery</button>
                                                       <button type="reset" class="btn btn-light ml-2">Reset</button>
                                                  </div>
                                             </form>
                                        </div>
                                   </div>
                              </div>

                              <div class="col-lg-4">
                                   <div class="card">
                                        <div class="card-header">
                                             <h5 class="mb-0">Product Summary</h5>
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
