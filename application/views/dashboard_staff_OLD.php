<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
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

  <style>
    .summary-header {
      background: #343a40;
      color: #fff;
      padding: 15px 20px;
      border-radius: 5px 5px 0 0;
    }

    .summary-table th {
      background-color: #6c757d;
      color: white;
      text-transform: uppercase;
    }

    .summary-table td {
      vertical-align: middle;
    }

    .badge-rank {
      font-size: 1rem;
      background-color: #007bff;
    }

    .card {
      box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
      border: none;
    }

    .no-data {
      color: #999;
      font-style: italic;
    }
  </style>

  <link rel="icon" type="image/png" sizes="32x32" href="<?= base_url(); ?>favicon.ico">
</head>

<body class="ms-body ms-aside-left-open ms-primary-theme ms-has-quickbar">

  <!-- Sidebar Navigation Left -->
  <?php include('includes/sidebar.php'); ?>

  <!-- Main Content -->
  <main class="body-content">

    <!-- Navigation Bar -->
    <?php include('includes/nav.php'); ?>

    <div class="ms-content-wrapper">
      <div class="card shadow">


        <?php if (!empty($dueToday)): ?>
          <div class="alert alert-info">
            <strong>Reminder:</strong> You have <?= count($dueToday) ?> task(s) due today!
            <ul class="mb-0">
              <?php foreach ($dueToday as $rem): ?>
                <li><?= $rem->title ?> - <?= $rem->description ?></li>
              <?php endforeach; ?>
            </ul>
          </div>
        <?php endif; ?>

        <div class="card-header bg-dark text-white d-flex align-items-center">
          <i class="material-icons mr-2 text-white">assignment_turned_in</i>
          <h4 id="task-summary" class="card-title mb-0 text-white">Accomplished Task Summary</h4>

<script>
    const monthNames = [
        "January", "February", "March", "April", "May", "June",
        "July", "August", "September", "October", "November", "December"
    ];
    const now = new Date();
    const currentMonth = monthNames[now.getMonth()];
    const currentYear = now.getFullYear();

    document.getElementById("task-summary").textContent = 
        `Accomplished Task Summary for the Month of ${currentMonth} ${currentYear}`;
</script>

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
                  $sameRank = 1;

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

  </main>

  <!-- SCRIPTS -->
  <script src="<?= base_url(); ?>assets/js/jquery-3.3.1.min.js"></script>
  <script src="<?= base_url(); ?>assets/js/popper.min.js"></script>
  <script src="<?= base_url(); ?>assets/js/bootstrap.min.js"></script>
  <script src="<?= base_url(); ?>assets/js/perfect-scrollbar.js"></script>
  <script src="<?= base_url(); ?>assets/js/jquery-ui.min.js"></script>
  <script src="<?= base_url(); ?>assets/js/Chart.bundle.min.js"></script>
  <script src="<?= base_url(); ?>assets/js/client-managemenet.js"></script>
  <script src="<?= base_url(); ?>assets/js/framework.js"></script>
  <script src="<?= base_url(); ?>assets/js/settings.js"></script>
</body>

</html>