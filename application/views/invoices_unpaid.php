
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
              <li class="breadcrumb-item"><a href="#">Invoice</a></li>
              <li class="breadcrumb-item active" aria-current="page">Unpaid Invoices</li>
            </ol>
          </nav>
        </div>
        <div class="col-xl-12 col-md-12">
          <div class="ms-panel">
            <div class="ms-panel-header ms-panel-custome">
              <h6>Unpaid Invoices</h6>
              <!-- <a href="#joborder" class="text-blue" data-toggle="modal" data-backdrop="static" data-keyboard="false"><i class="flaticon-pencil"></i> Add New Job Order</a> -->
            </div>

            <div class="ms-panel-body">
            <div class="table-responsive">
            <table id="table" class="table table-striped table-bordered" style="width:100%">
                    <thead>
                        <tr>
                            <th>Invoice No.</th>
                            <th>Customer</th>
                            <th>Transaction Date</th>
                            <th>Transaction Description</th>
                            <th>Total Due</th>
                            <th>Amount Paid</th>
                            <th>Balance</th>
							<th style="text-align:center;">Action</th> 
											</tr>
                    </thead>
                    <tbody>  
                    <?php
										  $i=1;
										  foreach($data as $row)
										  {
										  echo "<tr>";
                      ?>
                      <td><a href="<?= base_url(); ?>Page/invoice?id=<?php echo $row->orderID; ?>"><?php echo $row->InvoiceNo; ?></a></td>
                      
                      <?php
                      echo "<td>".$row->Customer."</td>";
                      echo "<td>".$row->TransDate."</td>";
                      echo "<td>".$row->JobDescription."</td>";
                        
                      ?>
                      <td style="text-align:right;"><?php echo number_format($row->TotalDue,2); ?></td>
                      <td style="text-align:right;"><a href="<?= base_url(); ?>Page/paymentHistory?id=<?php echo $row->orderID; ?>"><?php echo number_format($row->AmountPaid,2); ?></a></td>
                      <td style="text-align:right;"><?php echo number_format($row->Balance,2); ?></td>
                                      
					<td style="text-align:center;">
                      <a href="<?= base_url(); ?>Page/invoice?id=<?php echo $row->orderID; ?>&print=1" target="_blank" rel="noopener">Print</a>
                      |
                      <a href="<?= base_url(); ?>Page/addPaymentJO?id=<?php echo $row->orderID; ?>&InvoiceNo=<?php echo $row->InvoiceNo; ?>">Add Payment</a>
                      <!-- <a href="<?= base_url(); ?>Page/updateJO?id=<?php echo $row->orderID; ?>">Edit</a> 
                        | <a href="<?= base_url(); ?>Page/deleteJO?id=<?php echo $row->orderID; ?>" onclick="return confirm('Are you sure you want to delete this record?');">Delete</a>
									 -->
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

<script type="text/javascript" src="<?= base_url(); ?>assets/js/calculate.js"></script>
    <script type="text/javascript">
        function balance() {
            var TotalDue = document.getElementById('TotalDue').value;
            var AmountPaid = document.getElementById('AmountPaid').value;
            var result = parseInt(TotalDue) - parseInt(AmountPaid);
            if (!isNaN(result)) {
                document.getElementById('Balance').value = result;
            }
        }
    </script>
    

    <!-- Add New Job Order -->
    <div class="modal fade" id="joborder" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog ms-modal-dialog-width">
      <div class="modal-content ms-modal-content-width">
        <div class="modal-header  ms-modal-header-radius-0">
          <h4 class="modal-title text-white">New Job Order</h4>
          <button type="button" class="close text-white" data-dismiss="modal" aria-hidden="true">x</button>

        </div>
        <div class="modal-body p-0 text-left">
          <div class="col-xl-12 col-md-12">
            <div class="ms-panel ms-panel-bshadow-none">
              <div class="ms-panel-header">
                <h6>Job Order Details</h6>
              </div>
              <div class="ms-panel-body">
              <form class="needs-validation" method="post" action="<?= base_url(); ?>Page/addJO" novalidate>
                <div class="form-row">
                    <div class="col-md-3 mb-3">
                        <label for="validationCustom050">Invoice No.</label>
                        <div class="input-group">
                          <input type="text" class="form-control" name="InvoiceNo" id="validationCustom050" value="<?php echo $data1[0]->InvoiceNo+1; ?>" required readonly>
                        </div>
                    </div>

                    <div class="col-md-9 mb-3">
                      <label for="validationCustom020">Customer</label>
                      <div class="input-group">
                        <input type="text" class="form-control" name="Customer" id="validationCustom020" required>
                        <!-- <div class="valid-feedback">
                          Looks good!
                        </div> -->
                      </div>
                    </div>

                  </div>

              <div class="form-row">
                     <div class="col-md-12 mb-3">
                      <label for="validationCustom020">Customer Address</label>
                      <div class="input-group">
                        <input type="text" class="form-control" name="CustAddress" id="validationCustom020">
                        <!-- <div class="valid-feedback">
                          Looks good!
                        </div> -->
                      </div>
                    </div>

                  </div>

                  <div class="form-row">
                     <div class="col-md-6 mb-2">
                        <label>Job Description</label>
                        <input type="text" class="form-control" name="JobDescription" id="validationCustom020" required>
                    </div>

                      <div class="col-md-6 mb-2">
                        <label>Notes</label>
                        <input type="text" class="form-control" name="Notes" id="validationCustom020">
                      </div>
                  </div>

                  <div class="form-row">
                      <div class="col-md-4 mb-2">
                        <label>Total Due</label>
                        <input type="text" class="form-control" name="TotalDue" id="TotalDue" onkeyup="balance()" required>
                      </div>

                      <div class="col-md-4 mb-2">
                        <label>Amount Paid</label>
                        <input type="text" class="form-control" name="AmountPaid" id="AmountPaid" onkeyup="balance()" required>
                      </div>

                       <div class="col-md-4 mb-2">
                        <label>Balance</label>
                        <input type="text" class="form-control" name="Balance" id="Balance" required readonly>
                      </div> 
                  </div>

              <input type="submit" name="submit" class="btn btn-primary mt-4 d-inline w-20" value="Save Job Order">  
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


      <div class="modal fade" id="addpayment" tabindex="-1" role="dialog" aria-hidden="true">
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
              <form class="needs-validation" method="post" action="<?= base_url(); ?>Page/addJO" novalidate>
              <input type="text" name="dataid" id="dataid" value=""/>
                  <div class="form-row">
                    <div class="col-md-3 mb-3">
                        <label for="validationCustom050">Invoice No.</label>
                        <div class="input-group">
                          <input type="text" class="form-control" name="InvoiceNo" id="validationCustom050" value="<?php echo $data1[0]->InvoiceNo+1; ?>" required readonly>
                        </div>
                    </div>

                    <div class="col-md-9 mb-3">
                      <label for="validationCustom020">Customer</label>
                      <div class="input-group">
                        <input type="text" class="form-control" name="Customer" id="validationCustom020" required>
                        <!-- <div class="valid-feedback">
                          Looks good!
                        </div> -->
                      </div>
                    </div>

                  </div>

              <div class="form-row">
                     <div class="col-md-12 mb-3">
                      <label for="validationCustom020">Customer Address</label>
                      <div class="input-group">
                        <input type="text" class="form-control" name="CustAddress" id="validationCustom020">
                        <!-- <div class="valid-feedback">
                          Looks good!
                        </div> -->
                      </div>
                    </div>

                  </div>

                  <div class="form-row">
                     <div class="col-md-6 mb-2">
                        <label>Job Description</label>
                        <input type="text" class="form-control" name="JobDescription" id="validationCustom020" required>
                    </div>

                      <div class="col-md-6 mb-2">
                        <label>Notes</label>
                        <input type="text" class="form-control" name="Notes" id="validationCustom020">
                      </div>
                  </div>

                  <div class="form-row">
                      <div class="col-md-4 mb-2">
                        <label>Total Due</label>
                        <input type="text" class="form-control" name="TotalDue" id="TotalDue" onkeyup="balance()" required>
                      </div>

                      <div class="col-md-4 mb-2">
                        <label>Amount Paid</label>
                        <input type="text" class="form-control" name="AmountPaid" id="AmountPaid" onkeyup="balance()" required>
                      </div>

                       <div class="col-md-4 mb-2">
                        <label>Balance</label>
                        <input type="text" class="form-control" name="Balance" id="Balance" required readonly>
                      </div> 
                  </div>

              <input type="submit" name="submit" class="btn btn-primary mt-4 d-inline w-20" value="Save Job Order">  
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
