<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>BERPS</title>

  <!-- Fonts & Icons -->
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
  <link href="<?= base_url(); ?>vendors/iconic-fonts/font-awesome/css/all.min.css" rel="stylesheet">
  <link rel="stylesheet" href="<?= base_url(); ?>vendors/iconic-fonts/flat-icons/flaticon.css">

  <!-- Core CSS -->
  <link href="<?= base_url(); ?>assets/css/bootstrap.min.css" rel="stylesheet">
  <link href="<?= base_url(); ?>assets/css/jquery-ui.min.css" rel="stylesheet">
  <link href="<?= base_url(); ?>assets/css/style.css" rel="stylesheet">

  <!-- Favicon -->
  <link rel="icon" type="image/png" sizes="32x32" href="<?= base_url(); ?>favicon.ico">
    <link rel="stylesheet" href="<?= base_url('assets/css/fonts.css'); ?>">
</head>

<body class="ms-body ms-aside-left-open ms-primary-theme ms-has-quickbar">

  <!-- Includes -->
  <?php include('includes/settings.php'); ?>
  <?php include('includes/sidebar.php'); ?>
  <?php include('includes/sidebar-right.php'); ?>

  <!-- Main Content -->
  <main class="body-content">
    <?php include('includes/nav.php'); ?>

    <div class="ms-content-wrapper">
      <div class="row">
        <!-- Today's Income -->
        <div class="col-xl-4 col-md-6">
          <div class="ms-card card-gradient-success ms-widget ms-infographics-widget">
            <div class="ms-card-body media">
              <a href="<?= base_url(); ?>Page/todaysCollection">
                <div class="media-body">
                  <h6>Today's Income</h6>
                  <p class="ms-card-change"><?= $data[0]->Total !== null ? number_format($data[0]->Total, 2) : '0.00'; ?></p>
                </div>
              </a>
            </div>
            <i class="flaticon-stats"></i>
          </div>
        </div>

        <!-- Today's Expenses -->
        <div class="col-xl-4 col-md-6">
          <div class="ms-card card-gradient-secondary ms-widget ms-infographics-widget">
            <div class="ms-card-body media">
              <a href="<?= base_url(); ?>Page/todaysExpenses">
                <div class="media-body">
                  <h6>Today's Expenses</h6>
                  <p class="ms-card-change"><?= (!empty($data1) && $data1[0]->Total !== null) ? number_format($data1[0]->Total, 2) : '0.00'; ?></p>
                </div>
              </a>
            </div>
            <i class="flaticon-layers"></i>
          </div>
        </div>

        <!-- Receivables -->
        <div class="col-xl-4 col-md-6">
          <div class="ms-card card-gradient-warning ms-widget ms-infographics-widget">
            <div class="ms-card-body media">
              <a href="<?= base_url(); ?>Page/unpaidInvoices">
                <div class="media-body">
                  <h6>Receivables</h6>
                  <p class="ms-card-change">
                    <?= isset($data4[0]->Counts) && $data4[0]->Counts !== null ? number_format($data4[0]->Counts, 2) : '0.00'; ?>
                  </p>

                </div>
              </a>
            </div>
            <i class="flaticon-sticky-note"></i>
          </div>
        </div>
      </div>


      <div class="row mt-4">
        <div class="col-12">
          <div class="card">
            <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
              <h5 class="mb-0 text-white">Accomplishment Summary</h5>
            </div>
            <div class="card-body p-0">
              <div class="table-responsive">
                <table class="table table-hover text-center mb-0">
                  <thead class="bg-secondary text-white">
                    <tr>
                      <th>Rank</th>
                      <th>Assigned Person</th>
                      <th>Total Accomplished</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php if (!empty($accomplishedSummary)): ?>
                      <?php
                      $rank = 1;
                      $prevTotal = null;

                      foreach ($accomplishedSummary as $index => $row):
                        if ($prevTotal !== null && $row->total != $prevTotal) {
                          $rank = $index + 1;
                        }
                        $prevTotal = $row->total;
                      ?>
                        <tr>
                          <td>
                            <span class="badge badge-pill badge-primary" style="font-size: 16px; padding: 10px 15px;">
                              <?= $rank ?>
                            </span>
                          </td>
                          <td style="font-weight: 500;">
                            <?= $row->lName . ', ' . $row->fName ?>
                          </td>
                          <td>
                            <span class="text-success font-weight-bold" style="font-size: 18px;">
                              <?= $row->total ?>
                            </span>
                          </td>
                        </tr>
                      <?php endforeach; ?>
                    <?php else: ?>
                      <tr>
                        <td colspan="3">No accomplished tasks found.</td>
                      </tr>
                    <?php endif; ?>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>


    </div>
  </main>

  <!-- Scripts -->
  <script src="<?= base_url(); ?>assets/js/jquery-3.3.1.min.js"></script>
  <script src="<?= base_url(); ?>assets/js/popper.min.js"></script>
  <script src="<?= base_url(); ?>assets/js/bootstrap.min.js"></script>
  <script src="<?= base_url(); ?>assets/js/perfect-scrollbar.js"></script>
  <script src="<?= base_url(); ?>assets/js/jquery-ui.min.js"></script>
  <script src="<?= base_url(); ?>assets/js/framework.js"></script>
  <script src="<?= base_url(); ?>assets/js/settings.js"></script>
</body>

</html>