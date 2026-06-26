<!DOCTYPE html>
<html lang="en">

<?php include('includes/head.php'); ?>

<body>
  <div id="wrapper">

    <?php include('includes/top-nav-bar.php'); ?>
    <?php include('includes/sidebar.php'); ?>

    <div class="content-page">
      <div class="content">
        <div class="container-fluid dtr-page">

          <style>
            /* ─── Design Tokens ─────────────────────────────────────── */
            .dtr-page {
              --c-bg: #f7f7f5;
              --c-surface: #ffffff;
              --c-surface-2: #fafaf8;
              --c-border: #e9e9e4;
              --c-border-2: #d9d9d2;
              --c-ink: #1a1a18;
              --c-ink-2: #5a5a52;
              --c-ink-3: #9a9a90;
              --c-accent-blue: #2a6de8;

              --r-xl: 16px;
              --r-lg: 12px;
              --r-md: 8px;
              --r-sm: 5px;

              --font-body: var(--font-primary);
              --font-mono: var(--font-primary);

              --shadow-sm: 0 1px 3px rgba(26,26,24,.06), 0 1px 2px rgba(26,26,24,.04);
              --shadow-md: 0 4px 12px rgba(26,26,24,.08), 0 2px 6px rgba(26,26,24,.04);
              --shadow-lg: 0 16px 40px rgba(26,26,24,.12), 0 4px 12px rgba(26,26,24,.06);

              font-family: var(--font-primary, Montserrat, Segoe UI, Arial, sans-serif);
              color: var(--c-ink);
              background: var(--c-bg);
              min-height: 100vh;
              padding-bottom: 80px;
              font-size: 14px;
              line-height: 1.5;
            }

            /* ─── Page Header ───────────────────────────────────────── */
            .dtr-page .page-header {
              display: flex;
              align-items: flex-end;
              justify-content: space-between;
              gap: 16px;
              padding: 28px 0 20px;
              flex-wrap: wrap;
              border-bottom: 1px solid var(--c-border);
              margin-bottom: 22px;
            }

            .dtr-page .page-eyebrow {
              display: inline-flex;
              align-items: center;
              gap: 6px;
              font-size: 0.68rem;
              font-weight: 600;
              letter-spacing: 0.1em;
              text-transform: uppercase;
              color: var(--c-ink-3);
              margin-bottom: 5px;
            }

            .dtr-page .page-eyebrow span {
              width: 4px;
              height: 4px;
              border-radius: 50%;
              background: #4e8ef7;
              display: inline-block;
            }

            .dtr-page .page-title {
              font-size: 1.55rem;
              font-weight: 500;
              letter-spacing: -0.025em;
              color: var(--c-ink);
              line-height: 1.2;
            }

            .dtr-page .page-subtitle {
              margin-top: 3px;
              color: var(--c-ink-3);
              font-size: 0.8rem;
            }

            /* ─── Punch Cards ───────────────────────────────────────── */
            .dtr-page .punch-grid {
              display: grid;
              grid-template-columns: 1fr 1fr;
              gap: 10px;
              margin-bottom: 22px;
            }

            .dtr-page .punch-card {
              border-radius: var(--r-xl);
              padding: 16px 18px;
              display: flex;
              align-items: center;
              gap: 14px;
              transition: transform 0.15s ease, box-shadow 0.15s ease;
            }

            .dtr-page .punch-card:hover {
              transform: translateY(-2px);
              box-shadow: var(--shadow-md);
            }

            .dtr-page .punch-card.punch-in {
              background: linear-gradient(135deg, #4e8ef7, #6dc5ff);
            }

            .dtr-page .punch-card.punch-out {
              background: linear-gradient(135deg, #6c757d, #9ea3a7);
            }

            .dtr-page .punch-icon {
              width: 38px;
              height: 38px;
              border-radius: var(--r-md);
              display: flex;
              align-items: center;
              justify-content: center;
              font-size: 1.1rem;
              flex-shrink: 0;
              background: rgba(255,255,255,0.15);
              color: #fff;
            }

            .dtr-page .punch-info { flex: 1; min-width: 0; }

            .dtr-page .punch-label {
              font-size: 0.84rem;
              font-weight: 600;
              color: #fff;
              margin-bottom: 1px;
            }

            .dtr-page .punch-sublabel {
              font-size: 0.7rem;
              color: rgba(255,255,255,0.65);
            }

            .dtr-page .punch-btn {
              display: inline-flex;
              align-items: center;
              padding: 6px 13px;
              border-radius: var(--r-md);
              font-size: 0.75rem;
              font-weight: 600;
              text-decoration: none;
              white-space: nowrap;
              flex-shrink: 0;
              background: rgba(255,255,255,0.18);
              border: 1px solid rgba(255,255,255,0.28);
              color: #fff;
              transition: all 0.13s;
              font-family: var(--font-primary, Montserrat, Segoe UI, Arial, sans-serif);
              letter-spacing: 0.01em;
            }

            .dtr-page .punch-btn:hover {
              background: rgba(255,255,255,0.28);
              color: #fff;
              text-decoration: none;
            }

            /* ─── Theme Card ────────────────────────────────────────── */
            .dtr-page .theme-card {
              background: var(--c-surface);
              border: 1px solid var(--c-border);
              border-radius: var(--r-xl);
              overflow: hidden;
              margin-bottom: 16px;
              box-shadow: var(--shadow-sm);
              animation: fadeUp 0.28s ease both;
            }

            .dtr-page .theme-card-head {
              display: flex;
              align-items: center;
              justify-content: space-between;
              gap: 12px;
              padding: 14px 20px;
              border-bottom: 1px solid var(--c-border);
            }

            .dtr-page .theme-card-title {
              font-size: 0.88rem;
              font-weight: 600;
              color: var(--c-ink);
              letter-spacing: -0.01em;
            }

            .dtr-page .theme-card-subtitle {
              margin-top: 1px;
              color: var(--c-ink-3);
              font-size: 0.73rem;
            }

            /* ─── Table ─────────────────────────────────────────────── */
            .dtr-page .dtr-table {
              width: 100% !important;
              border-collapse: collapse;
            }

            .dtr-page .dtr-table thead th {
              border-top: none;
              border-bottom: 1px solid var(--c-border);
              color: var(--c-ink-3);
              font-size: 0.65rem;
              font-weight: 600;
              letter-spacing: 0.09em;
              text-transform: uppercase;
              white-space: nowrap;
              padding: 9px 16px;
              background: var(--c-surface-2);
            }

            .dtr-page .dtr-table td {
              padding: 9px 16px;
              border-bottom: 1px solid var(--c-border);
              vertical-align: middle;
              font-size: 0.82rem;
              color: var(--c-ink);
            }

            .dtr-page .dtr-table tbody tr:last-child td {
              border-bottom: none;
            }

            .dtr-page .dtr-table tbody tr:hover td {
              background: var(--c-surface-2);
              transition: background 0.1s;
            }

            .dtr-page .breakdown-line {
              display: inline-flex;
              align-items: center;
              gap: 5px;
              font-size: 0.75rem;
              color: var(--c-ink-2);
              font-family: var(--font-primary, Montserrat, Segoe UI, Arial, sans-serif);
              padding: 1px 0;
              line-height: 1.5;
            }

            .dtr-page .breakdown-line .mdi {
              color: var(--c-ink-3);
              font-size: 0.7rem;
            }

            .dtr-page .breakdown-line + .breakdown-line {
              margin-top: 1px;
            }

            .dtr-page .cell-muted {
              color: var(--c-ink-3);
              font-size: 0.78rem;
              font-family: var(--font-primary, Montserrat, Segoe UI, Arial, sans-serif);
              letter-spacing: -0.01em;
            }

            .dtr-page .action-icon {
              display: inline-flex;
              align-items: center;
              justify-content: center;
              width: 26px;
              height: 26px;
              border-radius: var(--r-sm);
              font-size: 0.88rem;
              text-decoration: none;
              transition: all 0.12s ease;
              color: var(--c-ink-3);
              border: 1px solid transparent;
            }

            .dtr-page .action-icon:hover {
              background: var(--c-surface-2);
              border-color: var(--c-border);
              color: var(--c-accent-blue);
              text-decoration: none;
            }

            .dtr-page .see-more {
              display: inline-block;
              margin-top: 3px;
              font-size: 0.7rem;
              color: var(--c-accent-blue);
              font-weight: 500;
              text-decoration: none;
              font-family: var(--font-primary, Montserrat, Segoe UI, Arial, sans-serif);
            }

            .dtr-page .see-more:hover { text-decoration: underline; }

            /* ─── DataTables Overrides ──────────────────────────────── */
            .dtr-page .dataTables_wrapper { padding: 0; }

            .dtr-page .dataTables_wrapper .dataTables_filter,
            .dtr-page .dataTables_wrapper .dataTables_length {
              padding: 10px 16px 0;
            }

            .dtr-page .dataTables_wrapper .dataTables_info,
            .dtr-page .dataTables_wrapper .dataTables_paginate {
              padding: 8px 16px;
            }

            .dtr-page .dataTables_wrapper .dataTables_info {
              font-size: 0.73rem;
              color: var(--c-ink-3);
            }

            .dtr-page .dataTables_wrapper .dataTables_filter label,
            .dtr-page .dataTables_wrapper .dataTables_length label {
              font-size: 0.76rem;
              color: var(--c-ink-2);
              font-weight: 400;
              font-family: var(--font-primary, Montserrat, Segoe UI, Arial, sans-serif);
            }

            .dtr-page .dataTables_wrapper .dataTables_filter input,
            .dtr-page .dataTables_wrapper .dataTables_length select {
              border: 1px solid var(--c-border);
              border-radius: var(--r-md);
              padding: 5px 10px;
              min-height: 32px;
              box-shadow: none;
              font-size: 0.78rem;
              color: var(--c-ink);
              background: var(--c-surface);
              font-family: var(--font-primary, Montserrat, Segoe UI, Arial, sans-serif);
            }

            .dtr-page .dataTables_wrapper .dataTables_filter input:focus,
            .dtr-page .dataTables_wrapper .dataTables_length select:focus {
              outline: none;
              border-color: var(--c-ink);
              box-shadow: 0 0 0 2px rgba(26,26,24,.07);
            }

            .dtr-page .dataTables_wrapper .dataTables_paginate .paginate_button {
              border-radius: var(--r-md) !important;
              padding: 3px 9px !important;
              font-size: 0.76rem !important;
              border: 1px solid transparent !important;
              background: transparent !important;
              color: var(--c-ink-2) !important;
              font-family: var(--font-primary, Montserrat, Segoe UI, Arial, sans-serif);
            }

            .dtr-page .dataTables_wrapper .dataTables_paginate .paginate_button.current,
            .dtr-page .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover {
              background: var(--c-ink) !important;
              border-color: var(--c-ink) !important;
              color: #fff !important;
              box-shadow: none !important;
            }

            .dtr-page .dataTables_wrapper .dataTables_paginate .paginate_button:hover:not(.current) {
              background: var(--c-surface-2) !important;
              border-color: var(--c-border) !important;
              color: var(--c-ink) !important;
            }

            /* ─── Flash ─────────────────────────────────────────────── */
            .dtr-page .alert {
              border: none;
              border-radius: var(--r-lg);
              font-size: 0.85rem;
              font-weight: 500;
              padding: 10px 16px;
              margin-bottom: 16px;
            }

            /* ─── Responsive ────────────────────────────────────────── */
            @media (max-width: 767px) {
              .dtr-page .page-title { font-size: 1.3rem; }
              .dtr-page .punch-grid { grid-template-columns: 1fr; }
            }

            @keyframes fadeUp {
              from { opacity: 0; transform: translateY(8px); }
              to   { opacity: 1; transform: translateY(0); }
            }
          </style>

          <?php $flash = $this->session->flashdata('msg'); ?>
          <?php if (!empty($flash)): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
              <?= $flash; ?>
              <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
            </div>
          <?php endif; ?>

          <!-- Page Header -->
          <div class="page-header">
            <div>
              <div class="page-eyebrow"><span></span>Records</div>
              <h4 class="page-title">Daily Time Record</h4>
              <div class="page-subtitle">Your punch log &amp; attendance history</div>
            </div>
          </div>

          <!-- Punch Cards -->
          <div class="punch-grid">
            <div class="punch-card punch-in">
              <div class="punch-icon"><i class="mdi mdi-clock-plus"></i></div>
              <div class="punch-info">
                <div class="punch-label">Time-In (Auto AM/PM)</div>
                <div class="punch-sublabel">Detects your slot from the current time</div>
              </div>
              <a class="punch-btn" href="<?= base_url(); ?>Page/amTimeIn">Time-In</a>
            </div>
            <div class="punch-card punch-out">
              <div class="punch-icon"><i class="mdi mdi-clock-outline"></i></div>
              <div class="punch-info">
                <div class="punch-label">Time-Out (Auto AM/PM)</div>
                <div class="punch-sublabel">Closes the latest open punch</div>
              </div>
              <a class="punch-btn" href="<?= base_url(); ?>Page/amTimeOut">Time-Out</a>
            </div>
          </div>

          <!-- DTR Table Card -->
          <div class="theme-card">
            <div class="theme-card-head">
              <div>
                <div class="theme-card-title">My Daily Time Record</div>
                <div class="theme-card-subtitle">Latest entries</div>
              </div>
            </div>

            <div class="table-responsive">
              <table id="dtr-table" class="table dtr-table dt-responsive nowrap" style="width:100%">
                <thead>
                  <tr>
                    <th>Date</th>
                    <th>AM Time Breakdown</th>
                    <th>PM Time Breakdown</th>
                    <th style="text-align:center;">Accomplishment</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if (!empty($data)): ?>
                    <?php foreach ($data as $row): ?>
                      <tr>
                        <td class="cell-muted"><?= htmlspecialchars($row->logDate, ENT_QUOTES, 'UTF-8'); ?></td>

                        <td>
                          <?php if (!empty($row->am_intervals)): ?>
                            <?php
                            $maxVisible = 2;
                            $amCount = count($row->am_intervals);
                            if ($amCount <= $maxVisible):
                              foreach (array_slice($row->am_intervals, 0, $maxVisible) as $intv): ?>
                                <div class="breakdown-line"><i class="mdi mdi-clock-outline"></i><?= $intv['label']; ?></div>
                              <?php endforeach;
                            else:
                              $firstOne = $row->am_intervals[0];
                              $lastOne  = $row->am_intervals[$amCount - 1];
                              $hidden   = array_slice($row->am_intervals, 1, $amCount - 2); ?>
                              <div class="breakdown-line"><i class="mdi mdi-clock-outline"></i><?= $firstOne['label']; ?></div>
                              <div class="more-am d-none">
                                <?php foreach ($hidden as $intv): ?>
                                  <div class="breakdown-line"><i class="mdi mdi-clock-outline"></i><?= $intv['label']; ?></div>
                                <?php endforeach; ?>
                              </div>
                              <div class="breakdown-line"><i class="mdi mdi-clock-outline"></i><?= $lastOne['label']; ?></div>
                              <a href="#" class="see-more" data-target="am" data-date="<?= $row->logDate; ?>">+<?= $amCount - $maxVisible; ?> more</a>
                            <?php endif; ?>
                          <?php else: ?>
                            <span style="color:var(--c-ink-3);font-size:0.75rem;">No AM punches</span>
                          <?php endif; ?>
                        </td>

                        <td>
                          <?php if (!empty($row->pm_intervals)): ?>
                            <?php
                            $maxVisible = 2;
                            $pmCount = count($row->pm_intervals);
                            if ($pmCount <= $maxVisible):
                              foreach (array_slice($row->pm_intervals, 0, $maxVisible) as $intv): ?>
                                <div class="breakdown-line"><i class="mdi mdi-clock-outline"></i><?= $intv['label']; ?></div>
                              <?php endforeach;
                            else:
                              $firstOne = $row->pm_intervals[0];
                              $lastOne  = $row->pm_intervals[$pmCount - 1];
                              $hidden   = array_slice($row->pm_intervals, 1, $pmCount - 2); ?>
                              <div class="breakdown-line"><i class="mdi mdi-clock-outline"></i><?= $firstOne['label']; ?></div>
                              <div class="more-pm d-none">
                                <?php foreach ($hidden as $intv): ?>
                                  <div class="breakdown-line"><i class="mdi mdi-clock-outline"></i><?= $intv['label']; ?></div>
                                <?php endforeach; ?>
                              </div>
                              <div class="breakdown-line"><i class="mdi mdi-clock-outline"></i><?= $lastOne['label']; ?></div>
                              <a href="#" class="see-more" data-target="pm" data-date="<?= $row->logDate; ?>">+<?= $pmCount - $maxVisible; ?> more</a>
                            <?php endif; ?>
                          <?php else: ?>
                            <span style="color:var(--c-ink-3);font-size:0.75rem;">No PM punches</span>
                          <?php endif; ?>
                        </td>

                        <td style="text-align:center;">
                          <a class="action-icon"
                            href="<?= base_url(); ?>Page/accomplishmentStaff?assignedPerson=<?= urlencode((string)$this->session->userdata('user_id')); ?>&date=<?= $row->logDate; ?>"
                            title="View accomplishment">
                            <i class="mdi mdi-clipboard-text-outline"></i>
                          </a>
                        </td>
                      </tr>
                    <?php endforeach; ?>
                  <?php else: ?>
                    <tr>
                      <td colspan="4" style="text-align:center;padding:2.5rem;color:var(--c-ink-3);font-size:0.82rem;">
                        No DTR records found.
                      </td>
                    </tr>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>
          </div>

        </div><!-- /.dtr-page -->
      </div><!-- /.content -->

      <?php include('includes/footer.php'); ?>
    </div><!-- /.content-page -->
  </div><!-- /#wrapper -->

  <?php include('includes/themecustomizer.php'); ?>

  <script src="<?= base_url(); ?>assets/js/vendor.min.js"></script>
  <script src="<?= base_url(); ?>assets/libs/datatables/jquery.dataTables.min.js"></script>
  <script src="<?= base_url(); ?>assets/libs/datatables/dataTables.bootstrap4.min.js"></script>
  <script src="<?= base_url(); ?>assets/libs/datatables/dataTables.responsive.min.js"></script>
  <script src="<?= base_url(); ?>assets/libs/datatables/responsive.bootstrap4.min.js"></script>
  <link href="<?= base_url(); ?>assets/libs/datatables/dataTables.bootstrap4.min.css" rel="stylesheet" />
  <link href="<?= base_url(); ?>assets/libs/datatables/responsive.bootstrap4.min.css" rel="stylesheet" />
  <script src="<?= base_url(); ?>assets/js/app.min.js"></script>

  <script>
    (function() {
      $('#dtr-table').DataTable({
        order: [[0, 'desc']],
        pageLength: 25,
        responsive: true,
        autoWidth: false,
        columnDefs: [{targets: -1, orderable: false, searchable: false}]
      });

      $(document).on('click', '.see-more', function(e) {
        e.preventDefault();
        var $link = $(this);
        var targetClass = $link.data('target') === 'pm' ? '.more-pm' : '.more-am';
        var $container = $link.closest('td').find(targetClass);
        $container.toggleClass('d-none');
        if ($container.hasClass('d-none')) {
          $link.text('+' + $container.find('div').length + ' more');
        } else {
          $link.text('Hide');
        }
      });
    })();
  </script>

</body>
</html>
