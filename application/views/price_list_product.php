
<!DOCTYPE html>
<html lang="en">

<head>

  <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>BERPS</title>
  <!-- Iconic Fonts -->
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
  <link href="<?= base_url(); ?>vendors/iconic-fonts/font-awesome/css/all.min.css" rel="stylesheet">
  <link rel="stylesheet" href="<?= base_url(); ?>vendors/iconic-fonts/flat-icons/flaticon.css">
  <link rel="stylesheet" href="<?= base_url(); ?>vendors/iconic-fonts/cryptocoins/cryptocoins.css">
  <link rel="stylesheet" href="<?= base_url(); ?>vendors/iconic-fonts/cryptocoins/cryptocoins-colors.css">
  <!-- Bootstrap core CSS -->
  <link href="<?= base_url(); ?>assets/css/bootstrap.min.css" rel="stylesheet">
  <!-- jQuery UI -->
  <link href="<?= base_url(); ?>assets/css/jquery-ui.min.css" rel="stylesheet">
  <!-- Medjestic styles -->
  <link href="<?= base_url(); ?>assets/css/style.css" rel="stylesheet">
 <!-- Page Specific Css (Datatables.css) -->
 <link href="<?= base_url(); ?>assets/css/datatables.min.css" rel="stylesheet">
  <!-- Favicon -->
  <link rel="icon" type="image/png" sizes="32x32" href="<?= base_url(); ?>favicon.ico">

    <link rel="stylesheet" href="<?= base_url('assets/css/fonts.css'); ?>">
</head>

<body class="ms-body ms-aside-left-open ms-primary-theme ms-has-quickbar">

  <!-- Setting Panel -->

  <!-- Sidebar Navigation Left -->
    <?php include('includes/sidebar.php'); ?>

  <!-- Sidebar Right -->

  <!-- Main Content -->
  <main class="body-content">

    <!-- Navigation Bar -->
    <?php include('includes/nav.php'); ?>


    <!-- Body Content Wrapper -->
    <div class="ms-content-wrapper">
      <div class="row">
        <div class="col-md-12">
          <nav aria-label="breadcrumb">
            <ol class="breadcrumb pl-0">
              <li class="breadcrumb-item"><a href="<?= base_url(); ?>Page/admin"><i class="material-icons">home</i> Home</a></li>
              <li class="breadcrumb-item"><a href="#">Notes</a></li>
              <li class="breadcrumb-item active" aria-current="page">Price List (Services)</li>
            </ol>
          </nav>
        </div>
        <div class="col-xl-12 col-md-12">
          <div class="ms-panel">
            <div class="ms-panel-header ms-panel-custome">
              <h6>Price List - Products</h6>
              <a href="#newservice" class="text-blue" data-toggle="modal"><i class="flaticon-list mr-2"></i> Add New Service</a>
            </div>

            <div class="ms-panel-body">
            <div class="table-responsive">
            <table id="table" class="table table-striped table-bordered" style="width:100%">
                    <thead>
                        <tr>
                            <th>Product Code</th>
                            <th>Product Name</th>
                            <th>Description</th>
                            <th>Counts</th>
                            <th>Price</th>
											</tr>
                    </thead>
                    <tbody>  
                    <?php
										  $i=1;
										  foreach($data as $row)
										  {
										  echo "<tr>";
										  echo "<td>".$row->productCode."</td>";
                      echo "<td>".$row->productName."</td>";
                      echo "<td>".$row->productDescription."</td>";
                      echo "<td style='text-align: center'>".$row->QtyDelivered."</td>";
                        ?>
										   <td style="text-align:right;">
                        <?php echo number_format($row->sellingPrice,2); ?>
										  </td> 
										  <?php
                                 
										  echo "</tr>";
											}

										   ?>
                      
                    </tbody>  
                  </table>

            </div>
            </div>

          </div>
        </div>



      </div>
    </div>

  </main>

  <!-- SCRIPTS -->
  <!-- Global Required Scripts Start -->
  <script src="<?= base_url(); ?>assets/js/jquery-3.3.1.min.js"></script>
  <script src="<?= base_url(); ?>assets/js/popper.min.js"></script>
  <script src="<?= base_url(); ?>assets/js/bootstrap.min.js"></script>
  <script src="<?= base_url(); ?>assets/js/perfect-scrollbar.js"> </script>
  <script src="<?= base_url(); ?>assets/js/jquery-ui.min.js"> </script>
  <!-- Global Required Scripts End -->

  <!-- Page Specific Scripts Start -->
  <script src="<?= base_url(); ?>assets/js/Chart.bundle.min.js"> </script>
  <script src="<?= base_url(); ?>assets/js/client-managemenet.js"> </script>
  <!-- Page Specific Scripts Finish -->

  <!-- Medjestic core JavaScript -->
  <script src="<?= base_url(); ?>assets/js/framework.js"></script>

  <!-- Settings -->
  <script src="<?= base_url(); ?>assets/js/settings.js"></script>

  <!-- Page Specific Scripts Start -->
  <script src="<?= base_url(); ?>assets/js/datatables.min.js"> </script>
  <script src="<?= base_url(); ?>assets/js/data-tables.js"> </script>
  <!-- Page Specific Scripts End -->
  <script>
$(document).ready(function() {
    $('#table').DataTable();
} );


  </script>

  <!-- Edit Task Modal -->
  <div class="modal fade" id="newservice" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog ms-modal-dialog-width">
      <div class="modal-content ms-modal-content-width">
        <div class="modal-header  ms-modal-header-radius-0">
          <h4 class="modal-title text-white">New Service</h4>
          <button type="button" class="close text-white" data-dismiss="modal" aria-hidden="true">x</button>

        </div>
        <div class="modal-body p-0 text-left">
          <div class="col-xl-12 col-md-12">
            <div class="ms-panel ms-panel-bshadow-none">
              <div class="ms-panel-header">
                <h6>Service Details</h6>
              </div>
              <div class="ms-panel-body">
                <form class="needs-validation" method="post" novalidate>
                  <div class="form-row">
                  <div class="col-md-12 mb-12">
                      <div class="col-md-12 mb-12">
                      <label for="validationCustom02">Service</label>
                      <input type="text" class="form-control" name="FeesDescription" id="validationCustom01"  required>
                      </div>
                  </div>
                  <div class="col-md-12 mb-12">
                      <div class="col-md-12 mb-12">
                      <label for="validationCustom02">Additional Details</label>
                      <input type="text" class="form-control" name="feeDetails" id="validationCustom01"  required>
                      </div>
                  </div>
                  <div class="col-md-12 mb-12">
                      <div class="col-md-12 mb-12">
                      <label for="validationCustom02">Price</label>
                      <input type="text" class="form-control" name="Amount" id="validationCustom01"  required>
                      </div>
                  </div>

                  </div>
                  <input type="submit" name="addservice" class="btn btn-primary mt-4 d-inline w-20" value="Save">        
                  <!-- <button class="btn btn-warning mt-4 d-inline w-20" type="submit" name="resettask">Reset</button> -->
                  <!-- <button class="btn btn-primary mt-4 d-inline w-20" type="submit" name="add_task">Add Task</button> -->
                </form>
              </div>

            </div>
          </div>
        </div>

      </div>
    </div>
  </div>


</body>

</html>
