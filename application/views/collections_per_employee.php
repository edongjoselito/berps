
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
              <li class="breadcrumb-item"><a href="#">Payments</a></li>
              <li class="breadcrumb-item active" aria-current="page">Payment List</li>
            </ol>
          </nav>
        </div>
        <div class="col-xl-12 col-md-12">
          <div class="ms-panel">
            <div class="ms-panel-header ms-panel-custome">
              <h6>Collections</h6>
              <a href="#payment" class="text-blue" data-toggle="modal"><i class="flaticon-pencil"></i> Add New Payment</a>
            </div>
            
           
            <div class="ms-panel-body">
            <div class="table-responsive">

            <table id="table" class="table table-striped table-bordered" style="width:100%">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Amount Paid</th>
                            <th>O.R. No.</th>
                            <th>Description</th>
                            <th>Payor</th>
												    <th style="text-align:center;">Action</th>
											</tr>
                    </thead>
                    <tbody>  
                    <?php
										  $i=1;
										  foreach($data as $row)
										  {
										  echo "<tr>";
										  echo "<td>".$row->PDate."</td>";
                        ?>
                        <td style="text-align: right"><?php echo number_format($row->AmountPaid,2); ?></td>
                        <?php
                      echo "<td style='text-align: center'>".$row->ORNo."</td>";
                      echo "<td>".$row->TransDescription."</td>";
                      ?>
                      <td><a href="<?= base_url(); ?>Page/customerHistory?customer=<?php echo $row->Customer; ?>"><?php echo $row->Customer; ?></a></td>
                       
                      
										  <td style="text-align:center;">
                      <?php if($this->session->userdata('level')==='Admin'){ ?>
                      <a href="<?= base_url(); ?>Page/updatePayment?id=<?php echo $row->paymentID; ?>">Edit</a> | <a href="<?= base_url(); ?>Page/deletePayment?id=<?php echo $row->paymentID; ?>">Delete</a>
										
										  </td>
										  <?php
                                 
										  echo "</tr>";
											} else {}
                    }
										   ?>
                      
                    </tbody>  
                  </table>

                  <h4>Total Collections: <span style="font-weight: bold; "><?php echo number_format($data1[0]->Total,2); ?></span></h4>
            </div>
            </div>

          </div>
        </div>

        <!-- Payment Summary -->
        <div class="col-xl-12 col-md-12">
          <div class="ms-panel">
            <div class="ms-panel-header ms-panel-custome">
            <h6>Summary by Payment Description</h6>
            </div>
            
           
            <div class="ms-panel-body">
            <div class="table-responsive">

            <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Description</th>
                            <th style="text-align: right;">Total</th>
											</tr>
                    </thead>
                    <tbody>  
                    <?php
										  $i=1;
										  foreach($data2 as $row)
										  {
										  echo "<tr>";
										  echo "<td>".$row->TransDescription."</td>";
                      echo "<td style='text-align: right';>".number_format($row->Total,2)."</td>";
                                 
										  echo "</tr>";
                       } 
										   ?>
                      
                    </tbody>  
                  </table>


                  <!-- <h4>Total Collections: <span style="font-weight: bold; "><?php echo number_format($data1[0]->Total,2); ?></span></h4> -->
            </div>
            </div>

          </div>
        </div>

      </div>

      </div>
    </div>

  </main>

  <script type="text/javascript" src="<?= base_url(); ?>assets/js/calculate.js"></script>
    <script type="text/javascript">
        function getNetIncome() {
            var TotalCollections = document.getElementById('TotalCollections').value;
            var TotalExpenses = document.getElementById('TotalExpenses').value;
            var result = parseInt(TotalCollections) - parseInt(TotalExpenses);
            if (!isNaN(result)) {
                document.getElementById('NetIncome').value = result;
            }
        }
    </script>


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

      <!-- Add New Expense Modal -->
      <div class="modal fade" id="payment" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog ms-modal-dialog-width">
      <div class="modal-content ms-modal-content-width">
        <div class="modal-header  ms-modal-header-radius-0">
          <h4 class="modal-title text-white">New Payment</h4>
          <button type="button" class="close text-white" data-dismiss="modal" aria-hidden="true">x</button>

        </div>
        <div class="modal-body p-0 text-left">
          <div class="col-xl-12 col-md-12">
            <div class="ms-panel ms-panel-bshadow-none">
              <div class="ms-panel-header">
                <h6>Payment Details</h6>
              </div>
              <div class="ms-panel-body">
              <form class="needs-validation" method="post" action="<?= base_url(); ?>Page/addPayment" novalidate>
                  <div class="form-row">
                    <div class="col-md-12 mb-3">
                      <label for="validationCustom050">Payor</label>
                      <div class="input-group">
                        <input type="text" class="form-control" name="Customer" id="validationCustom050" placeholder="Name of the Customer/Company"  required>

                      </div>
                    </div>
                  </div>

                  <div class="form-row">
                    <div class="col-md-12 mb-3">
                      <label for="validationCustom050">Description</label>
                      <div class="input-group">
                      <input type="text" class="form-control" name="TransDescription" id="validationCustom010" required>

                      </div>
                    </div>
                  </div>

              <div class="form-row">
                    <div class="col-md-4 mb-3">
                      <label for="validationCustom020">Amount Paid</label>
                      <div class="input-group">
                        <input type="text" class="form-control" name="AmountPaid" id="validationCustom020" required>
                        <!-- <div class="valid-feedback">
                          Looks good!
                        </div> -->
                      </div>
                    </div>

                    <div class="col-md-4 mb-3">
                      <label for="validationCustom020">O.R. No.</label>
                      <div class="input-group">
                        <input type="text" class="form-control" name="ORNo" id="validationCustom020">
                        <!-- <div class="valid-feedback">
                          Looks good!
                        </div> -->
                      </div>
                    </div>

                    <div class="col-md-4 mb-3">
                    <label for="validationCustom010">Invoice No.</label>
                    <div class="input-group">
                      <input type="text" class="form-control" name="InvoiceNo" id="validationCustom010" placeholder="optional">
                    </div>
                  </div>
                  </div>

              <input type="submit" name="submit" class="btn btn-primary mt-4 d-inline w-20" value="Accept Payment">  
                <button class="btn btn-warning mt-4 d-inline w-20" type="reset
                ">Reset</button>
                <!-- <button name="submit" class="btn btn-primary mt-4 d-inline w-20" type="submit">Accept Payment</button>  -->
              </form>
              
              </div>

            </div>
          </div>
        </div>

      </div>
    </div>
  </div>
      <!-- Add New Expense Modal -->
      <div class="modal fade" id="payment" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog ms-modal-dialog-width">
      <div class="modal-content ms-modal-content-width">
        <div class="modal-header  ms-modal-header-radius-0">
          <h4 class="modal-title text-white">New Payment</h4>
          <button type="button" class="close text-white" data-dismiss="modal" aria-hidden="true">x</button>

        </div>
        <div class="modal-body p-0 text-left">
          <div class="col-xl-12 col-md-12">
            <div class="ms-panel ms-panel-bshadow-none">
              <div class="ms-panel-header">
                <h6>Payment Details</h6>
              </div>
              <div class="ms-panel-body">
              <form class="needs-validation" method="post" action="<?= base_url(); ?>Page/addPayment" novalidate>
                <div class="form-row">
                    <div class="col-md-12 mb-3">
                      <label for="validationCustom050">Payor</label>
                      <div class="input-group">
                        <input type="text" class="form-control" name="Customer" id="validationCustom050" placeholder="Name of the Customer/Company"  required>

                      </div>
                    </div>
                  </div>

              <div class="form-row">
                    <div class="col-md-3 mb-3">
                      <label for="validationCustom020">Payment Date</label>
                      <div class="input-group">
                        <input type="date" class="form-control" name="PDate" id="validationCustom020" required>
                        <!-- <div class="valid-feedback">
                          Looks good!
                        </div> -->
                      </div>
                    </div>

                    <div class="col-md-3 mb-3">
                      <label for="validationCustom020">Amount Paid</label>
                      <div class="input-group">
                        <input type="text" class="form-control" name="AmountPaid" id="validationCustom020" required>
                        <!-- <div class="valid-feedback">
                          Looks good!
                        </div> -->
                      </div>
                    </div>

                    <div class="col-md-3 mb-3">
                      <label for="validationCustom020">O.R. No.</label>
                      <div class="input-group">
                        <input type="text" class="form-control" name="ORNo" id="validationCustom020">
                        <!-- <div class="valid-feedback">
                          Looks good!
                        </div> -->
                      </div>
                    </div>

                    <div class="col-md-3 mb-3">
                    <label for="validationCustom010">Invoice No.</label>
                    <div class="input-group">
                      <input type="text" class="form-control" name="InvoiceNo" id="validationCustom010" placeholder="optional">
                    </div>
                  </div>
                  </div>

                  <div class="form-row">
                  <div class="col-md-12 mb-2">
                    <label>Transaction/Description</label>
                    <input type="text" class="form-control" name="TransDescription" id="validationCustom010" required>
                  </div>
                </div>
                
               

                

              <input type="submit" name="submit" class="btn btn-primary mt-4 d-inline w-20" value="Accept Payment">  
                <button class="btn btn-warning mt-4 d-inline w-20" type="reset
                ">Reset</button>
                <!-- <button name="submit" class="btn btn-primary mt-4 d-inline w-20" type="submit">Accept Payment</button>  -->
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
