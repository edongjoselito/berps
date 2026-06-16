<!DOCTYPE html>
<html lang="en">
<?php include('includes/head.php'); ?>

<body>
  <div id="wrapper">
    <?php include('includes/top-nav-bar.php'); ?>
    <?php include('includes/sidebar.php'); ?>

    <div class="content-page">
      <div class="content">
        <div class="container-fluid employee-task-select-page">
          <style>
            /* ===== Page structure: sticky footer, aligned title ===== */
            .content-page {
              display: flex;
              flex-direction: column;
              min-height: 100vh;
            }

            .content-page .content {
              flex: 1 0 auto;
              padding-bottom: 1.25rem;
            }

            .footer {
              flex: 0 0 auto;
              margin-top: 0 !important;
            }

            /* Title aligned to card padding (1.25rem) */
            .employee-task-select-page .title-wrap {
              padding: 1.25rem 1.25rem 0;
            }

            .employee-task-select-page .title-wrap h4 {
              margin: 0;
              line-height: 1.1;
            }

            .employee-task-select-page .title-sub {
              display: inline-block;
              margin-top: 4px;
            }

            .employee-task-select-page .title-divider {
              border: 0;
              height: 2px;
              border-radius: 1px;
              background: linear-gradient(to right, #4285F4 60%, #FBBC05 80%, #34A853 100%);
              margin: 10px 1.25rem 16px;
            }

            /* Card polish (consistent with other pages) */
            .employee-task-select-page .selection-card {
              border: 1px solid rgba(15, 23, 42, .08);
              border-radius: 14px;
              box-shadow: 0 6px 18px rgba(15, 23, 42, .08);
              width: 100%;
            }

            .employee-task-select-page .card-body {
              padding: 1.25rem;
            }

            /* Inputs & labels */
            .employee-task-select-page .form-label {
              font-weight: 600;
              color: #5c6b7a;
              margin-bottom: .25rem;
            }

            .employee-task-select-page .req::after {
              content: " *";
              color: #dc3545;
            }

            .employee-task-select-page .form-control {
              border-radius: 10px;
            }

            /* Input group tag */
            .employee-task-select-page .input-group-text {
              background: #eef1ff;
              border-color: #eef1ff;
              color: #27326c;
              font-weight: 600;
            }

            /* Button */
            .employee-task-select-page .btn-primary {
              padding: 10px 24px;
              border-radius: 24px;
            }

            /* Empty state */
            .employee-task-select-page .empty-state {
              background: rgba(68, 86, 204, .08);
              border-radius: 12px;
              padding: 14px 18px;
              margin-top: 14px;
            }

            @media (max-width:576px) {
              .employee-task-select-page .title-wrap {
                padding: 1rem 1rem 0;
              }

              .employee-task-select-page .title-divider {
                margin: 8px 1rem 12px;
              }
            }
          </style>

          <?php $totalAssignments = !empty($data2) ? count($data2) : 0; ?>

          <!-- Title (aligned to card/table padding) -->
          <div class="row">
            <div class="col-12">
              <div class="title-wrap">
                <h4 class="page-title">
                  Task Assignments<br>
                  <span class="badge badge-purple title-sub">View tasks per employee</span>
                </h4>
              </div>
              <hr class="title-divider">
            </div>
          </div>

          <!-- Selector -->
          <div class="row justify-content-center">
            <div class="col-xl-6 col-lg-7 col-md-9">
              <div class="card selection-card mx-auto">
                <div class="card-body">
                  <h5 class="mb-2">Select Employee</h5>
                  <p class="text-muted mb-4">Choose a team member to review their assigned tasks.</p>

                  <form method="get" action="<?= base_url(); ?>Page/employeeTaskData" class="needs-validation" novalidate>
                    <div class="form-group">
                      <label class="form-label req" for="emp-select">Employee Name</label>
                      <div class="input-group">
                        <div class="input-group-prepend">
                          <div class="input-group-text"><i class="mdi mdi-account-outline mr-1"></i> Employee</div>
                        </div>
                        <select class="form-control" id="emp-select" name="user_id" required>
                          <option value="" disabled selected>— Select Assigned Person —</option>
                          <?php foreach ($data2 as $row): ?>
                            <option value="<?= htmlspecialchars($row->user_id, ENT_QUOTES, 'UTF-8'); ?>">
                              <?= htmlspecialchars($row->lName . ', ' . $row->fName . ' ' . $row->mName, ENT_QUOTES, 'UTF-8'); ?>
                            </option>
                          <?php endforeach; ?>
                        </select>
                        <div class="invalid-feedback">Please select an employee.</div>
                      </div>
                    </div>

                    <div class="text-right">
                      <button type="submit" name="submit" class="btn btn-primary">
                        <i class="mdi mdi-format-list-checks mr-1"></i> View Tasks
                      </button>
                    </div>
                  </form>

                  <?php if ($totalAssignments === 0): ?>
                    <div class="empty-state text-muted">
                      <i class="mdi mdi-information-outline mr-1"></i>No active employees found with task assignments yet.
                    </div>
                  <?php endif; ?>
                </div>
              </div>
            </div>
          </div>

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
      // Client-side validation (soft)
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

      // Optional: upgrade select to Select2 if available (keeps graceful fallback)
      if (window.jQuery && jQuery.fn && typeof jQuery.fn.select2 === 'function') {
        jQuery('#emp-select').select2({
          width: '100%',
          placeholder: '— Select Assigned Person —',
          allowClear: true
        });
      }
    })();
  </script>
</body>

</html>