<!DOCTYPE html>
<html lang="en">

<?php include('includes/head.php'); ?>

<body>
  <div id="wrapper">

    <!-- Topbar -->
    <?php include('includes/top-nav-bar.php'); ?>
    <!-- Sidebar -->
    <?php include('includes/sidebar.php'); ?>

    <div class="content-page">
      <div class="content">
        <div class="container-fluid accomplishments-today-page">
          <style>
            .accomplishments-today-page {
              padding-bottom: 100px;
            }

            .accomplishments-today-page .title-wrap {
              padding: 1.25rem 1.25rem 0;
            }

            .accomplishments-today-page .title-wrap h4 {
              margin: 0;
              line-height: 1.15;
            }

            .accomplishments-today-page .title-sub {
              display: inline-block;
              margin-top: 6px;
            }

            .accomplishments-today-page .title-divider {
              border: 0;
              height: 2px;
              border-radius: 1px;
              background: linear-gradient(to right, #4285F4 60%, #FBBC05 80%, #34A853 100%);
              margin: 10px 1.25rem 18px;
            }

            .accomplishments-today-page .stat-box {
              background: #f8f9fc;
              border: 1px solid #e9edf7;
              border-radius: 12px;
              padding: 16px;
              text-align: center;
              height: 100%;
            }

            .accomplishments-today-page .stat-box h3 {
              margin: 0;
              font-size: 28px;
              font-weight: 700;
              color: #27326c;
            }

            .accomplishments-today-page .stat-box p {
              margin: 4px 0 0;
              color: #6c757d;
              font-size: 13px;
            }

            .accomplishments-today-page .accordion-card {
              background: #fff;
              border: 1px solid rgba(15, 23, 42, .08);
              border-radius: 14px;
              box-shadow: 0 6px 18px rgba(15, 23, 42, .06);
              margin-bottom: 14px;
              overflow: hidden;
            }

            .accomplishments-today-page .accordion-header-btn {
              width: 100%;
              text-align: left;
              border: 0;
              background: #fff;
              padding: 16px 18px;
              display: flex;
              align-items: center;
              justify-content: space-between;
              cursor: pointer;
              outline: none;
            }

            .accomplishments-today-page .employee-meta {
              display: flex;
              align-items: center;
              gap: 14px;
              min-width: 0;
            }

            .accomplishments-today-page .avatar-circle {
              width: 46px;
              height: 46px;
              border-radius: 50%;
              background: #eaf1ff;
              color: #27326c;
              font-weight: 700;
              display: flex;
              align-items: center;
              justify-content: center;
              font-size: 16px;
              flex-shrink: 0;
            }

            .accomplishments-today-page .employee-name {
              font-size: 16px;
              font-weight: 700;
              color: #27326c;
              margin: 0;
            }

            .accomplishments-today-page .employee-sub {
              font-size: 12px;
              color: #6c757d;
              margin-top: 2px;
            }

            .accomplishments-today-page .right-meta {
              display: flex;
              align-items: center;
              gap: 10px;
              flex-wrap: wrap;
              margin-left: 14px;
            }

            .accomplishments-today-page .count-badge {
              display: inline-block;
              padding: 7px 12px;
              border-radius: 999px;
              font-size: 12px;
              font-weight: 700;
              background: #eaf7ee;
              color: #1e7e34;
            }

            .accomplishments-today-page .chevron {
              transition: transform .25s ease;
              font-size: 18px;
              color: #27326c;
            }

            .accomplishments-today-page .accordion-header-btn[aria-expanded="true"] .chevron {
              transform: rotate(180deg);
            }

            .accomplishments-today-page .task-body {
              padding: 0 18px 18px;
              background: #fcfdff;
              border-top: 1px solid #eef1f6;
            }

            .accomplishments-today-page .task-table {
              margin-top: 14px;
              margin-bottom: 0;
            }

            .accomplishments-today-page .task-table thead th {
              background: #f2f5fb;
              color: #445;
              font-size: 13px;
              border-top: none;
              white-space: nowrap;
              vertical-align: middle;
            }

            .accomplishments-today-page .task-table td {
              vertical-align: middle;
            }

            .accomplishments-today-page .task-cell {
              white-space: normal;
              min-width: 200px;
            }

            .accomplishments-today-page .task-text {
              font-weight: 600;
              color: #27326c;
              line-height: 1.4;
            }

            .accomplishments-today-page .empty-state {
              background: rgba(68, 86, 204, .08);
              border-radius: 12px;
              padding: 14px 18px;
              color: #5c6b7a;
              margin-top: 14px;
            }

            .accomplishments-today-page .accomp-status-badge {
              font-size: 0.75rem;
              padding: 6px 10px;
              border-radius: 10px;
              display: inline-flex;
              align-items: center;
              gap: 6px;
            }

            .accomplishments-today-page .back-btn {
              border-radius: 24px;
              padding: 8px 20px;
              font-weight: 600;
              font-size: 13px;
            }

            @media (max-width: 768px) {
              .accomplishments-today-page .accordion-header-btn {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
              }

              .accomplishments-today-page .right-meta {
                width: 100%;
                justify-content: space-between;
                margin-left: 0;
              }
            }

            @media (max-width:576px) {
              .accomplishments-today-page .title-wrap {
                padding: 1rem 1rem 0;
              }

              .accomplishments-today-page .title-divider {
                margin: 8px 1rem 14px;
              }
            }
          </style>

          <?php
          $selected_date = isset($selectedDate) && $selectedDate ? $selectedDate : ($this->input->get('date') ?: date('Y-m-d'));
          $labelDate = date('F j, Y', strtotime($selected_date));
          $title = ($selected_date === date('Y-m-d')) ? "Today's Accomplishments" : $labelDate . " Accomplishments";

          // Group accomplishments by employee
          $groupedData = [];
          $totalAccomplishments = 0;
          if (!empty($accomplishments)) {
            foreach ($accomplishments as $row) {
              $empKey = isset($row->user_id) ? $row->user_id : (isset($row->assignedPerson) ? $row->assignedPerson : 'unknown');
              $empName = isset($row->fName) ? trim((string)$row->fName) : (isset($row->assignedPersonName) ? $row->assignedPersonName : 'Unknown');
              if (!isset($groupedData[$empKey])) {
                $groupedData[$empKey] = [
                  'name' => $empName,
                  'tasks' => []
                ];
              }
              $groupedData[$empKey]['tasks'][] = $row;
              $totalAccomplishments++;
            }
          }
          $totalEmployees = count($groupedData);
          ?>

          <div class="row">
            <div class="col-12">
              <div class="title-wrap d-flex justify-content-between align-items-center">
                <h4 class="page-title"><?= $title; ?></h4>
                <a href="<?= base_url(); ?>Page/attendanceList" class="btn btn-secondary btn-sm back-btn">Back to Attendance</a>
              </div>
              <hr class="title-divider">
            </div>
          </div>

          <!-- Stats -->
          <div class="row mb-2">
            <div class="col-md-6 col-lg-3 mb-3">
              <div class="stat-box">
                <h3><?= (int)$totalEmployees; ?></h3>
                <p>Total Employees</p>
              </div>
            </div>
            <div class="col-md-6 col-lg-3 mb-3">
              <div class="stat-box">
                <h3><?= (int)$totalAccomplishments; ?></h3>
                <p>Total Accomplishments</p>
              </div>
            </div>
          </div>

          <div id="employeeAccordion">
            <?php if (!empty($groupedData)): ?>
              <?php $index = 0; ?>
              <?php foreach ($groupedData as $empKey => $empData): ?>
                <?php
                $collapseId = 'empCollapse' . $index;
                $headingId = 'empHeading' . $index;
                $fullName = $empData['name'];
                $taskCount = count($empData['tasks']);

                // Generate initials
                $initials = '??';
                if ($fullName !== 'Unknown') {
                  $nameParts = explode(' ', $fullName);
                  if (count($nameParts) >= 2) {
                    $initials = strtoupper(substr($nameParts[0], 0, 1) . substr(end($nameParts), 0, 1));
                  } else {
                    $initials = strtoupper(substr($fullName, 0, 2));
                  }
                }
                ?>
                <div class="accordion-card">
                  <button class="accordion-header-btn" type="button" data-toggle="collapse" data-target="#<?= $collapseId; ?>" aria-expanded="<?= $index === 0 ? 'true' : 'false'; ?>" aria-controls="<?= $collapseId; ?>" id="<?= $headingId; ?>">
                    <div class="employee-meta">
                      <div class="avatar-circle"><?= htmlspecialchars($initials, ENT_QUOTES, 'UTF-8'); ?></div>
                      <div>
                        <h5 class="employee-name"><?= htmlspecialchars($fullName, ENT_QUOTES, 'UTF-8'); ?></h5>
                        <div class="employee-sub"><?= $taskCount; ?> accomplishment<?= $taskCount !== 1 ? 's' : ''; ?></div>
                      </div>
                    </div>

                    <div class="right-meta">
                      <span class="count-badge">
                        <?= $taskCount; ?> Task<?= $taskCount !== 1 ? 's' : ''; ?>
                      </span>
                      <i class="mdi mdi-chevron-down chevron"></i>
                    </div>
                  </button>

                  <div id="<?= $collapseId; ?>" class="collapse <?= $index === 0 ? 'show' : ''; ?>" aria-labelledby="<?= $headingId; ?>" data-parent="#employeeAccordion">
                    <div class="task-body">
                      <div class="table-responsive">
                        <table class="table table-bordered table-hover task-table">
                          <thead>
                            <tr>
                              <th style="width:60px;">#</th>
                              <th>Task</th>
                              <th style="width:180px;">Project</th>
                              <th style="width:140px;">Date Posted</th>
                              <th style="width:120px;" class="text-center">Status</th>
                            </tr>
                          </thead>
                          <tbody>
                            <?php foreach ($empData['tasks'] as $k => $row): ?>
                              <tr>
                                <td><?= $k + 1; ?></td>
                                <td class="task-cell">
                                  <div class="task-text"><?= $row->task ?? 'Task'; ?></div>
                                </td>
                                <td><?= $row->projectDescription ?? '—'; ?></td>
                                <td><?= !empty($row->datePosted) ? date('M d, Y H:i', strtotime($row->datePosted)) : '—'; ?></td>
                                <td class="text-center">
                                  <span class="badge badge-success accomp-status-badge text-white">
                                    <i class="mdi mdi-check-circle"></i> Has accomplishment
                                  </span>
                                </td>
                              </tr>
                            <?php endforeach; ?>
                          </tbody>
                        </table>
                      </div>
                    </div>
                  </div>
                </div>
                <?php $index++; ?>
              <?php endforeach; ?>
            <?php else: ?>
              <div class="empty-state">
                <i class="mdi mdi-information-outline mr-1"></i>
                No accomplishments recorded for this date.
              </div>
            <?php endif; ?>
          </div>

        </div>
      </div>

      <?php include('includes/footer.php'); ?>
    </div>
  </div>

  <?php include('includes/themecustomizer.php'); ?>

  <script src="<?= base_url(); ?>assets/js/vendor.min.js"></script>
  <script src="<?= base_url(); ?>assets/js/app.min.js"></script>
</body>

</html>