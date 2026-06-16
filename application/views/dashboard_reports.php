
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

      <div class="col-xl-3 col-md-6">
                  <div class="ms-card card-gradient-success ms-widget ms-infographics-widget">
                     <div class="ms-card-body media">
                        <div class="media-body">
                           <h6>Accumulated Income</h6>
                           <p class="ms-card-change"><?php echo number_format($data[0]->Total,2); ?></p>
                           <!-- <p class="fs-12">48% From Last 24 Hours</p> -->
                        </div>
                     </div>
                     <i class="flaticon-layers"></i>
                  </div>
               </div>
               <div class="col-xl-3 col-md-6">
                  <div class="ms-card card-gradient-secondary ms-widget ms-infographics-widget">
                     <div class="ms-card-body media">
                        <div class="media-body">
                           <h6>Accumulated Expenses</h6>
                           <p class="ms-card-change"><?php echo number_format($data1[0]->Total,2); ?></p>
                           <!-- <p class="fs-12">2% Decreased from last budget</p> -->
                        </div>
                     </div>
                     <i class="flaticon-stats"></i>
                  </div>
               </div>
               <div class="col-xl-3 col-md-6">
                  <div class="ms-card card-gradient-warning ms-widget ms-infographics-widget">
                     <div class="ms-card-body media">
                        <div class="media-body">
                           <h6>Receivables</h6>
                           <p class="ms-card-change"> 0.00</p>
                           <!-- <p class="fs-12">48% From Last 24 Hours</p> -->
                        </div>
                     </div>
                     <i class="flaticon-sticky-note"></i>
                  </div>
               </div>
               <div class="col-xl-3 col-md-6">
                  <div class="ms-card card-gradient-info ms-widget ms-infographics-widget">
                     <div class="ms-card-body media">
                        <div class="media-body">
                           <h6>Clients</h6>
                           <p class="ms-card-change"> 0</p>
                           <!-- <p class="fs-12">2% Decreased from last budget</p> -->
                        </div>
                     </div>
                     <i class="flaticon-user"></i>
                  </div>
               </div>

        <!-- Projects Summary -->
        <div class="col-xl-12 col-md-12">
          <div class="ms-panel ms-widget ms-panel-fh">
            <div class="ms-panel-header">
              <h6>Projects Summary</h6>
            </div>
            <div class="ms-panel-body">
              <div class="table-responsive">
                <table class="table table-hover">
                <thead>
                        <tr>
                            <th>Project</th>
                            <th style="text-align: center">Task Counts</th>
                            <th style="text-align: center">Action</th>
                                            </tr>
                    </thead>
                    <tbody>  
                    <?php
                                          $i=1;
                                          foreach($data4 as $row)
                                          {
                                          echo "<tr>";
                                          echo "<td>".$row->project."</td>";
                      echo "<td style='text-align: center'>".$row->pCounts."</td>";                  
                      ?>
                      <td style="text-align:center"><a href="<?= base_url(); ?>Page/taskPerProject?project=<?php echo $row->project; ?>">View</a></td>
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

</body>

</html>
