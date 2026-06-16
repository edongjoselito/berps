<!DOCTYPE html>
<html lang="en">
<?php include('includes/head.php'); ?>
<style>
  .event-card {
    border: 1px solid #e5e7eb;
    border-radius: 10px;
    padding: 14px 16px;
    margin-bottom: 12px;
    background: #fff;
    box-shadow: 0 4px 12px rgba(15, 23, 42, 0.06);
  }

  .event-title {
    font-weight: 700;
    font-size: 1rem;
    margin-bottom: 4px;
  }

  .event-dates {
    color: #6c757d;
    font-weight: 600;
    margin: 0;
  }

  .dot {
    display: inline-block;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    margin-right: 8px;
    border: 1px solid #d1d5db;
  }
</style>

<body>
  <div id="wrapper">
    <?php include('includes/top-nav-bar.php'); ?>
    <?php include('includes/sidebar.php'); ?>

    <div class="content-page">
      <div class="content">
        <div class="container-fluid">
          <div class="card">
            <div class="card-body">
              <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                  <h4 class="mb-1">Calendar Events</h4>
                  <div class="text-muted small">Public events plus your private reminders.</div>
                </div>
                <div class="d-flex" style="gap:8px;">
                  <a class="btn btn-outline-secondary btn-sm" href="<?= base_url('Calendar'); ?>">
                    <i class="mdi mdi-calendar-blank mr-1"></i> Manage Calendar
                  </a>
                  <a class="btn btn-info btn-sm" href="<?= base_url('Calendar/print_all'); ?>" target="_blank">
                    <i class="mdi mdi-printer mr-1"></i> Print All
                  </a>
                </div>
              </div>

              <?php if (empty($events)): ?>
                <div class="alert alert-light border" role="alert">
                  No calendar events to show.
                </div>
              <?php else: ?>
                <?php foreach ($events as $ev): ?>
                  <?php
                  $title = $ev->title ?? 'Calendar Event';
                  $color = $ev->color ?? '#10b981';
                  $startTs = isset($ev->start) ? strtotime($ev->start) : null;
                  $endTs   = isset($ev->end) && $ev->end !== '' ? strtotime($ev->end) : null;
                  if ($endTs) {
                    $timePart = date('H:i:s', $endTs);
                    if ($timePart === '00:00:00' && $endTs > $startTs) $endTs = strtotime('-1 day', $endTs);
                  }
                  $start = $startTs ? date('M d, Y', $startTs) : '';
                  $end   = $endTs ? date('M d, Y', $endTs) : '';
                  $range = $start;
                  if ($end && $end !== $start) $range .= ' — ' . $end;
                  ?>
                  <div class="event-card">
                    <div class="d-flex align-items-center" style="gap:10px;">
                      <span class="dot" style="background: <?= htmlspecialchars($color, ENT_QUOTES, 'UTF-8'); ?>;"></span>
                      <div>
                        <div class="event-title"><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8'); ?></div>
                        <p class="event-dates mb-0"><?= htmlspecialchars($range, ENT_QUOTES, 'UTF-8'); ?></p>
                      </div>
                    </div>
                  </div>
                <?php endforeach; ?>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>
      <?php include('includes/footer.php'); ?>
    </div>
  </div>

  <script src="<?= base_url(); ?>assets/js/vendor.min.js"></script>
  <script src="<?= base_url(); ?>assets/js/app.min.js"></script>
</body>

</html>