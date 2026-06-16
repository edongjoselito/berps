<!DOCTYPE html>
<html lang="en">
<?php include('includes/head.php'); ?>

<body>
  <div id="wrapper">
    <?php include('includes/top-nav-bar.php'); ?>
    <?php include('includes/sidebar.php'); ?>

    <div class="content-page">
      <div class="content">
        <div class="container-fluid employee-accomplishment-page">

          <style>
            .employee-accomplishment-page {
              padding: 20px 0 90px;
            }

            .employee-accomplishment-page .page-shell {
              max-width: 1380px;
              margin: 0 auto;
            }

            .employee-accomplishment-page .page-header {
              margin-bottom: 20px;
            }

            .employee-accomplishment-page .page-header h4 {
              margin: 0;
              font-size: 28px;
              font-weight: 800;
              color: #24325f;
              letter-spacing: .2px;
            }

            .employee-accomplishment-page .page-header p {
              margin: 6px 0 0;
              font-size: 14px;
              color: #7a8699;
            }

            .employee-accomplishment-page .title-divider {
              border: 0;
              height: 3px;
              border-radius: 999px;
              background: linear-gradient(90deg, #4285F4 0%, #6ea8fe 45%, #34A853 100%);
              margin: 14px 0 0;
              width: 100%;
            }

            .employee-accomplishment-page .top-row {
              margin-bottom: 12px;
            }

            .employee-accomplishment-page .top-row>div {
              margin-bottom: 16px;
            }

            .employee-accomplishment-page .top-card,
            .employee-accomplishment-page .filter-card {
              position: relative;
              background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
              border: 1px solid rgba(66, 133, 244, .10);
              border-radius: 18px;
              box-shadow: 0 10px 26px rgba(31, 55, 90, .08);
              min-height: 205px;
              height: 100%;
              overflow: hidden;
            }

            .employee-accomplishment-page .top-card::before,
            .employee-accomplishment-page .filter-card::before {
              content: "";
              position: absolute;
              top: 0;
              left: 0;
              right: 0;
              height: 5px;
              background: linear-gradient(90deg, #4285F4, #34A853);
            }

            .employee-accomplishment-page .top-card .card-body-custom {
              padding: 22px 20px;
              height: 100%;
              display: flex;
              align-items: center;
            }

            .employee-accomplishment-page .metric-wrap {
              display: flex;
              align-items: center;
              gap: 14px;
              width: 100%;
            }

            .employee-accomplishment-page .metric-icon {
              width: 58px;
              height: 58px;
              min-width: 58px;
              border-radius: 16px;
              display: flex;
              align-items: center;
              justify-content: center;
              background: linear-gradient(135deg, rgba(66, 133, 244, .12), rgba(52, 168, 83, .08));
              color: #2f5fd0;
              font-size: 25px;
            }

            .employee-accomplishment-page .metric-content {
              min-width: 0;
            }

            .employee-accomplishment-page .metric-label {
              margin: 0 0 10px;
              font-size: 13px;
              font-weight: 800;
              text-transform: uppercase;
              letter-spacing: .8px;
              color: #7d8798;
            }

            .employee-accomplishment-page .metric-value {
              margin: 0;
              font-size: 34px;
              line-height: 1;
              font-weight: 800;
              color: #22315f;
            }

            .employee-accomplishment-page .metric-desc {
              margin: 10px 0 0;
              font-size: 13px;
              line-height: 1.55;
              color: #6f7d90;
            }

            .employee-accomplishment-page .filter-card .filter-inner {
              padding: 20px 20px 18px;
              height: 100%;
              display: flex;
              flex-direction: column;
              justify-content: center;
            }

            .employee-accomplishment-page .filter-header {
              display: flex;
              align-items: center;
              gap: 10px;
              margin-bottom: 16px;
            }

            .employee-accomplishment-page .filter-header .filter-icon {
              width: 42px;
              height: 42px;
              min-width: 42px;
              border-radius: 12px;
              display: flex;
              align-items: center;
              justify-content: center;
              background: rgba(66, 133, 244, .10);
              color: #2f5fd0;
              font-size: 20px;
            }

            .employee-accomplishment-page .filter-header h5 {
              margin: 0;
              font-size: 17px;
              font-weight: 800;
              color: #24325f;
              line-height: 1.2;
            }

            .employee-accomplishment-page .filter-grid {
              display: flex;
              flex-wrap: wrap;
              align-items: flex-end;
              margin-right: -10px;
              margin-left: -10px;
            }

            .employee-accomplishment-page .filter-grid>div {
              padding-left: 10px;
              padding-right: 10px;
              margin-bottom: 10px;
            }

            .employee-accomplishment-page .field-staff {
              width: 45%;
            }

            .employee-accomplishment-page .field-month {
              width: 23%;
            }

            .employee-accomplishment-page .field-button {
              width: 32%;
            }

            .employee-accomplishment-page .form-label {
              display: inline-block;
              margin-bottom: 8px;
              font-size: 13px;
              font-weight: 700;
              color: #526176;
              letter-spacing: .2px;
            }

            .employee-accomplishment-page .req::after {
              content: " *";
              color: #dc3545;
            }

            .employee-accomplishment-page .input-group {
              display: flex;
              flex-wrap: nowrap;
              align-items: stretch;
              width: 100%;
            }

            .employee-accomplishment-page .input-group-prepend {
              display: flex;
            }

            .employee-accomplishment-page .input-group-text {
              min-width: 48px;
              width: 48px;
              justify-content: center;
              align-items: center;
              display: flex;
              background: #edf3ff;
              border: 1px solid #dbe6ff;
              color: #2f5fd0;
              border-radius: 12px 0 0 12px;
              font-size: 18px;
              padding: 0;
            }

            .employee-accomplishment-page .form-control {
              height: 46px;
              border: 1px solid #dbe3ef;
              border-left: 0;
              border-radius: 0 12px 12px 0;
              font-size: 14px;
              color: #24325f;
              background: #fff;
              box-shadow: none;
              min-width: 0;
            }

            .employee-accomplishment-page .form-control:focus {
              border-color: #4285F4;
              box-shadow: 0 0 0 0.18rem rgba(66, 133, 244, .14);
            }

            .employee-accomplishment-page .filter-actions {
              display: flex;
              align-items: flex-end;
              height: 100%;
              width: 100%;
            }

            .employee-accomplishment-page .btn-generate {
              width: 100%;
              height: 46px;
              border: 0;
              border-radius: 12px;
              background: linear-gradient(135deg, #4285F4 0%, #2f6ef6 100%);
              color: #fff;
              font-size: 14px;
              font-weight: 700;
              letter-spacing: .2px;
              line-height: 1.2;
              box-shadow: 0 8px 20px rgba(66, 133, 244, .25);
              transition: all .2s ease;
              padding: 0 16px;
              white-space: nowrap;
            }

            .employee-accomplishment-page .btn-generate:hover {
              background: linear-gradient(135deg, #3367d6 0%, #2757c9 100%);
              box-shadow: 0 12px 24px rgba(51, 103, 214, .28);
              transform: translateY(-1px);
            }

            .employee-accomplishment-page .btn-generate i {
              margin-right: 8px;
            }

            .employee-accomplishment-page .filter-note {
              margin-top: 6px;
              font-size: 12px;
              line-height: 1.45;
              color: #7a8699;
            }

            .employee-accomplishment-page .invalid-feedback {
              font-size: 12px;
            }

            .employee-accomplishment-page .select2-container {
              width: 100% !important;
              flex: 1 1 auto;
              min-width: 0;
            }

            .employee-accomplishment-page .select2-container .selection {
              display: block;
              width: 100%;
            }

            .employee-accomplishment-page .select2-container--default .select2-selection--single {
              height: 46px;
              border: 1px solid #dbe3ef;
              border-left: 0;
              border-radius: 0 12px 12px 0;
              background: #fff;
              display: flex;
              align-items: center;
            }

            .employee-accomplishment-page .select2-container--default .select2-selection--single .select2-selection__rendered {
              line-height: 44px;
              padding-left: 14px;
              padding-right: 34px;
              color: #24325f;
              font-size: 14px;
              white-space: nowrap;
              overflow: hidden;
              text-overflow: ellipsis;
            }

            .employee-accomplishment-page .select2-container--default .select2-selection--single .select2-selection__arrow {
              height: 44px;
              right: 8px;
            }

            .employee-accomplishment-page .select2-container--default.select2-container--focus .select2-selection--single,
            .employee-accomplishment-page .select2-container--default.select2-container--open .select2-selection--single {
              border-color: #4285F4;
              box-shadow: 0 0 0 0.18rem rgba(66, 133, 244, .14);
            }

            .employee-accomplishment-page .select2-dropdown {
              border: 1px solid #dbe3ef;
              border-radius: 12px;
              overflow: hidden;
              box-shadow: 0 12px 24px rgba(15, 23, 42, .12);
            }

            .employee-accomplishment-page .select2-search--dropdown .select2-search__field {
              border: 1px solid #dbe3ef;
              border-radius: 8px;
              padding: 8px 10px;
            }

            @media (max-width: 1399.98px) {
              .employee-accomplishment-page .field-staff {
                width: 42%;
              }

              .employee-accomplishment-page .field-month {
                width: 24%;
              }

              .employee-accomplishment-page .field-button {
                width: 34%;
              }
            }

            @media (max-width: 1199.98px) {

              .employee-accomplishment-page .field-staff,
              .employee-accomplishment-page .field-month,
              .employee-accomplishment-page .field-button {
                width: 100%;
              }

              .employee-accomplishment-page .top-card,
              .employee-accomplishment-page .filter-card {
                min-height: auto;
              }

              .employee-accomplishment-page .filter-actions {
                margin-top: 2px;
              }
            }

            @media (max-width: 767.98px) {
              .employee-accomplishment-page {
                padding-top: 16px;
              }

              .employee-accomplishment-page .page-header h4 {
                font-size: 24px;
              }

              .employee-accomplishment-page .top-card .card-body-custom,
              .employee-accomplishment-page .filter-card .filter-inner {
                padding: 16px;
              }

              .employee-accomplishment-page .metric-value {
                font-size: 30px;
              }

              .employee-accomplishment-page .metric-wrap {
                align-items: flex-start;
              }

              .employee-accomplishment-page .form-control,
              .employee-accomplishment-page .btn-generate,
              .employee-accomplishment-page .select2-container--default .select2-selection--single {
                height: 44px;
              }

              .employee-accomplishment-page .select2-container--default .select2-selection--single .select2-selection__rendered {
                line-height: 42px;
              }

              .employee-accomplishment-page .select2-container--default .select2-selection--single .select2-selection__arrow {
                height: 42px;
              }
            }
          </style>

          <?php
          $monthsBack     = 24;
          $selectedYm     = isset($_GET['report_period']) ? (string) $_GET['report_period'] : '';
          $selectedUserId = isset($_GET['user_id']) ? (string) $_GET['user_id'] : '';
          $staffCount     = !empty($data2) ? count($data2) : 0;
          $monthCount     = $monthsBack + 1;
          ?>

          <div class="page-shell">

            <div class="page-header">
              <h4>Employee Accomplishment Report</h4>
              <p>Generate accomplishment reports by staff member and reporting period.</p>
              <hr class="title-divider">
            </div>

            <div class="row top-row">

              <div class="col-lg-3 col-md-12">
                <div class="top-card">
                  <div class="card-body-custom">
                    <div class="metric-wrap">
                      <div class="metric-icon">
                        <i class="mdi mdi-calendar-range"></i>
                      </div>
                      <div class="metric-content">
                        <p class="metric-label">Months Available</p>
                        <h3 class="metric-value"><?= $monthCount; ?></h3>
                        <p class="metric-desc">Reports can be generated from the current month up to 24 months back.</p>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <div class="col-lg-9 col-md-12">
                <div class="filter-card">
                  <div class="filter-inner">
                    <div class="filter-header">
                      <div class="filter-icon">
                        <i class="mdi mdi-filter-variant"></i>
                      </div>
                      <h5>Report Filter</h5>
                    </div>

                    <form method="get" action="<?= base_url(); ?>Page/employeeAccomplishmentData" class="needs-validation" novalidate>
                      <div class="filter-grid">

                        <div class="field-staff">
                          <label for="employee-select" class="form-label req">Staff Name</label>
                          <div class="input-group">
                            <div class="input-group-prepend">
                              <div class="input-group-text">
                                <i class="mdi mdi-account"></i>
                              </div>
                            </div>
                            <select class="form-control" id="employee-select" name="user_id" required>
                              <option value="" disabled <?= $selectedUserId === '' ? 'selected' : ''; ?>>— Select Assigned Staff —</option>
                              <?php foreach ($data2 as $row): ?>
                                <?php
                                $fullName = trim($row->lName . ', ' . $row->fName . ' ' . $row->mName);
                                $isSelected = ($selectedUserId !== '' && $selectedUserId == $row->user_id) ? 'selected' : '';
                                ?>
                                <option value="<?= htmlspecialchars($row->user_id, ENT_QUOTES, 'UTF-8'); ?>" <?= $isSelected; ?>>
                                  <?= htmlspecialchars($fullName, ENT_QUOTES, 'UTF-8'); ?>
                                </option>
                              <?php endforeach; ?>
                            </select>
                            <div class="invalid-feedback">Please select a staff member.</div>
                          </div>
                        </div>

                        <?php if ($this->session->userdata('level') === 'Admin'): ?>
                          <div class="field-month">
                            <label for="report-period" class="form-label">Report Month</label>
                            <div class="input-group">
                              <div class="input-group-prepend">
                                <div class="input-group-text">
                                  <i class="mdi mdi-calendar-month"></i>
                                </div>
                              </div>
                              <select class="form-control" id="report-period" name="report_period">
                                <option value="">— All Months —</option>
                                <?php for ($i = 0; $i <= $monthsBack; $i++): ?>
                                  <?php
                                  $ym = date('Y-m', strtotime("-{$i} month"));
                                  $label = date('F Y', strtotime($ym . '-01'));
                                  $isSelected = ($selectedYm === $ym) ? 'selected' : '';
                                  ?>
                                  <option value="<?= htmlspecialchars($ym, ENT_QUOTES, 'UTF-8'); ?>" <?= $isSelected; ?>>
                                    <?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8'); ?>
                                  </option>
                                <?php endfor; ?>
                              </select>
                            </div>
                          </div>

                          <div class="field-button">
                            <label class="form-label" style="visibility:hidden;">Generate</label>
                            <div class="filter-actions">
                              <button type="submit" name="submit" value="1" class="btn btn-generate">
                                <i class="mdi mdi-file-chart-outline"></i>Generate Report
                              </button>
                            </div>
                          </div>
                        <?php else: ?>
                          <div class="field-button" style="width:55%;">
                            <label class="form-label" style="visibility:hidden;">Generate</label>
                            <div class="filter-actions">
                              <button type="submit" name="submit" value="1" class="btn btn-generate">
                                <i class="mdi mdi-file-chart-outline"></i>Generate Report
                              </button>
                            </div>
                          </div>
                        <?php endif; ?>

                      </div>

                      <div class="filter-note">
                        Select the assigned staff and optionally choose a month before generating the report.
                      </div>
                    </form>
                  </div>
                </div>
              </div>
            </div>

          </div><!-- /.page-shell -->
        </div><!-- /.container-fluid -->
      </div><!-- /.content -->

      <?php include('includes/footer.php'); ?>
    </div><!-- /.content-page -->
  </div><!-- /#wrapper -->

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

  <script>
    (function() {
      'use strict';

      var form = document.querySelector('.needs-validation');
      if (form) {
        form.addEventListener('submit', function(e) {
          if (!form.checkValidity()) {
            e.preventDefault();
            e.stopPropagation();
          }
          form.classList.add('was-validated');
        }, false);
      }

      if (window.jQuery && jQuery.fn && typeof jQuery.fn.select2 === 'function') {
        jQuery('#employee-select').select2({
          width: '100%',
          placeholder: '— Select Assigned Staff —',
          allowClear: false
        });

        if (jQuery('#report-period').length) {
          jQuery('#report-period').select2({
            width: '100%',
            placeholder: '— All Months —',
            allowClear: true
          });
        }
      }
    })();
  </script>

</body>

</html>