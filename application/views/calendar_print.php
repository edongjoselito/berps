<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Calendar Events — <?= htmlspecialchars($company_name, ENT_QUOTES, 'UTF-8') ?></title>
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    body {
      font-family: "Segoe UI", Arial, sans-serif;
      font-size: 13px;
      color: #1a1a1a;
      background: #f4f5f7;
      padding: 32px 24px;
    }

    .page {
      max-width: 780px;
      margin: 0 auto;
      background: #fff;
      border-radius: 10px;
      box-shadow: 0 4px 24px rgba(0,0,0,.10);
      overflow: hidden;
    }

    /* ── Header ── */
    .doc-header {
      padding: 28px 32px 20px;
      border-bottom: 2px solid #2563eb;
      display: flex;
      justify-content: space-between;
      align-items: flex-end;
      gap: 16px;
      flex-wrap: wrap;
    }

    .doc-company {
      font-size: 11px;
      font-weight: 600;
      letter-spacing: .08em;
      text-transform: uppercase;
      color: #2563eb;
      margin-bottom: 4px;
    }

    .doc-title {
      font-size: 22px;
      font-weight: 700;
      color: #111827;
      line-height: 1.2;
    }

    .doc-meta {
      text-align: right;
      font-size: 11px;
      color: #6b7280;
      line-height: 1.7;
    }

    .doc-meta strong {
      color: #374151;
    }

    /* ── Toolbar (no-print) ── */
    .toolbar {
      padding: 14px 32px;
      background: #f9fafb;
      border-bottom: 1px solid #e5e7eb;
      display: flex;
      gap: 10px;
      align-items: center;
    }

    .btn-print {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      padding: 7px 18px;
      background: #2563eb;
      color: #fff;
      border: none;
      border-radius: 6px;
      font-size: 13px;
      font-weight: 600;
      cursor: pointer;
    }

    .btn-print:hover { background: #1d4ed8; }

    .btn-back {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      padding: 7px 14px;
      background: #fff;
      color: #374151;
      border: 1px solid #d1d5db;
      border-radius: 6px;
      font-size: 13px;
      text-decoration: none;
      font-weight: 500;
    }

    .btn-back:hover { background: #f3f4f6; }

    .filter-group {
      display: flex;
      gap: 8px;
      align-items: center;
    }

    .filter-select {
      padding: 6px 10px;
      border: 1px solid #d1d5db;
      border-radius: 6px;
      font-size: 13px;
      background: #fff;
    }

    .btn-filter {
      padding: 7px 14px;
      background: #2563eb;
      color: #fff;
      border: none;
      border-radius: 6px;
      font-size: 13px;
      font-weight: 600;
      cursor: pointer;
    }

    .btn-filter:hover { background: #1d4ed8; }

    /* ── Stats bar ── */
    .stats-bar {
      display: flex;
      gap: 24px;
      padding: 16px 32px;
      background: #f8faff;
      border-bottom: 1px solid #e5e7eb;
      flex-wrap: wrap;
    }

    .stat {
      display: flex;
      flex-direction: column;
      gap: 2px;
    }

    .stat-value {
      font-size: 20px;
      font-weight: 700;
      color: #111827;
      line-height: 1;
    }

    .stat-label {
      font-size: 11px;
      color: #6b7280;
      text-transform: uppercase;
      letter-spacing: .06em;
    }

    /* ── Events ── */
    .events-body {
      padding: 24px 32px 32px;
    }

    .month-group + .month-group {
      margin-top: 28px;
    }

    .month-heading {
      font-size: 11px;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: .1em;
      color: #2563eb;
      padding-bottom: 8px;
      border-bottom: 1px solid #e5e7eb;
      margin-bottom: 12px;
    }

    .event-row {
      display: flex;
      gap: 14px;
      align-items: flex-start;
      padding: 10px 0;
      border-bottom: 1px solid #f3f4f6;
    }

    .event-row:last-child { border-bottom: none; }

    .event-dot {
      width: 10px;
      height: 10px;
      border-radius: 50%;
      flex-shrink: 0;
      margin-top: 4px;
    }

    .event-info { flex: 1; min-width: 0; }

    .event-title {
      font-weight: 600;
      font-size: 13.5px;
      color: #111827;
      margin-bottom: 2px;
    }

    .event-dates {
      font-size: 11.5px;
      color: #6b7280;
    }

    .event-notes {
      margin-top: 6px;
      font-size: 11.5px;
      color: #4b5563;
      line-height: 1.5;
      white-space: pre-line;
    }

    .event-badge {
      flex-shrink: 0;
      font-size: 10px;
      font-weight: 600;
      letter-spacing: .05em;
      text-transform: uppercase;
      padding: 2px 8px;
      border-radius: 999px;
      align-self: center;
    }

    .badge-public  { background: #dbeafe; color: #1d4ed8; }
    .badge-private { background: #f3f4f6; color: #6b7280; }
    .badge-completed { background: #dcfce7; color: #166534; }
    .badge-pending { background: #fef3c7; color: #92400e; }

    .empty-state {
      text-align: center;
      padding: 48px 0;
      color: #9ca3af;
      font-size: 13px;
    }

    /* ── Footer ── */
    .doc-footer {
      padding: 20px 32px;
      border-top: 1px solid #e5e7eb;
      display: flex;
      justify-content: space-between;
      align-items: flex-end;
      gap: 24px;
      flex-wrap: wrap;
    }

    .sig-line {
      width: 180px;
      border-top: 1.5px solid #374151;
      padding-top: 6px;
      font-size: 11px;
      color: #6b7280;
    }

    .footer-note {
      font-size: 10.5px;
      color: #9ca3af;
      text-align: right;
    }

    /* ── Print overrides ── */
    @media print {
      body { background: #fff; padding: 0; }
      .page { box-shadow: none; border-radius: 0; }
      .no-print { display: none !important; }
      .event-row { page-break-inside: avoid; }
    }
  </style>
</head>
<body>

<?php
  $totalPublic  = 0;
  $totalPrivate = 0;
  $totalCompleted = 0;
  $totalPending = 0;
  $grouped = array();

  foreach ($events as $ev) {
    $ts    = isset($ev->start) ? strtotime($ev->start) : time();
    $month = date('Y-m', $ts);
    $grouped[$month][] = $ev;
    if (($ev->status ?? 'private') === 'public') $totalPublic++;
    else $totalPrivate++;
    
    $is_completed = isset($ev->is_completed) ? (int) $ev->is_completed : 1;
    if ($is_completed == 0) $totalCompleted++;
    else $totalPending++;
  }
  ksort($grouped);
?>

<div class="page">

  <!-- Header -->
  <div class="doc-header">
    <div>
      <div class="doc-company"><?= htmlspecialchars($company_name, ENT_QUOTES, 'UTF-8') ?></div>
      <div class="doc-title">Calendar Events</div>
    </div>
    <div class="doc-meta">
      <div><strong>Printed by:</strong> <?= htmlspecialchars($printed_by, ENT_QUOTES, 'UTF-8') ?></div>
      <div><strong>Date:</strong> <?= htmlspecialchars($printed_at, ENT_QUOTES, 'UTF-8') ?></div>
      <div><strong>Scope:</strong> <?= $is_admin_staff ? 'Public &amp; Private' : 'Public + My Events' ?></div>
    </div>
  </div>

  <!-- Toolbar -->
  <div class="toolbar no-print">
    <div class="filter-group">
      <select id="monthFilter" class="filter-select">
        <option value="">All Months</option>
        <?php
          $months = array();
          foreach ($events as $ev) {
            $ts = isset($ev->start) ? strtotime($ev->start) : time();
            $month_key = date('Y-m', $ts);
            $month_label = date('F Y', $ts);
            $months[$month_key] = $month_label;
          }
          ksort($months);
          foreach ($months as $key => $label):
        ?>
          <option value="<?= htmlspecialchars($key, ENT_QUOTES, 'UTF-8') ?>" <?= $month_filter === $key ? 'selected' : '' ?>>
            <?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?>
          </option>
        <?php endforeach; ?>
      </select>
      
      <select id="statusFilter" class="filter-select">
        <option value="all" <?= $status_filter === 'all' || !$status_filter ? 'selected' : '' ?>>All Status</option>
        <option value="completed" <?= $status_filter === 'completed' ? 'selected' : '' ?>>Completed</option>
        <option value="pending" <?= $status_filter === 'pending' ? 'selected' : '' ?>>Pending</option>
      </select>
      
      <button class="btn-filter" onclick="applyFilters()">Filter</button>
      <button class="btn-back" onclick="clearFilters()">Clear</button>
    </div>
    
    <div style="flex:1"></div>
    
    <button class="btn-print" onclick="window.print()">&#128438; Print</button>
    <a class="btn-back" href="javascript:history.back()">&#8592; Back</a>
  </div>

  <!-- Stats -->
  <div class="stats-bar">
    <div class="stat">
      <span class="stat-value"><?= count($events) ?></span>
      <span class="stat-label">Total Events</span>
    </div>
    <div class="stat">
      <span class="stat-value"><?= $totalPublic ?></span>
      <span class="stat-label">Public</span>
    </div>
    <div class="stat">
      <span class="stat-value"><?= $totalPrivate ?></span>
      <span class="stat-label">Private</span>
    </div>
    <div class="stat">
      <span class="stat-value"><?= $totalCompleted ?></span>
      <span class="stat-label">Completed</span>
    </div>
    <div class="stat">
      <span class="stat-value"><?= $totalPending ?></span>
      <span class="stat-label">Pending</span>
    </div>
    <div class="stat">
      <span class="stat-value"><?= count($grouped) ?></span>
      <span class="stat-label">Months</span>
    </div>
  </div>

  <!-- Events -->
  <div class="events-body">
    <?php if (empty($events)): ?>
      <div class="empty-state">No events found.</div>
    <?php else: ?>
      <?php foreach ($grouped as $month => $monthEvents): ?>
        <div class="month-group">
          <div class="month-heading"><?= date('F Y', strtotime($month . '-01')) ?></div>

          <?php foreach ($monthEvents as $ev): ?>
            <?php
              $start  = isset($ev->start) ? strtotime($ev->start) : null;
              $end    = isset($ev->end) && $ev->end !== '' ? strtotime($ev->end) : null;
              $status = $ev->status ?? 'private';
              $color  = $ev->color ?? '#2563eb';
              $description = trim((string) ($ev->description ?? ''));
              $notes = trim((string) ($ev->notes ?? ''));
              $is_completed = isset($ev->is_completed) ? (int) $ev->is_completed : 1;

              $dateStr = $start ? date('M j, Y g:i A', $start) : '';
              if ($end && $end !== $start) {
                $endTs = $end;
                if (date('H:i:s', $endTs) === '00:00:00' && $endTs > $start) {
                  $endTs = strtotime('-1 day', $endTs);
                }
                $endLabel = date('M j, Y g:i A', $endTs);
                if ($endLabel !== $dateStr) {
                  $dateStr .= ' — ' . $endLabel;
                }
              }
            ?>
            <div class="event-row">
              <span class="event-dot" style="background:<?= htmlspecialchars($color, ENT_QUOTES, 'UTF-8') ?>;"></span>
              <div class="event-info">
                <div class="event-title"><?= htmlspecialchars($ev->title ?? 'Untitled', ENT_QUOTES, 'UTF-8') ?></div>
                <div class="event-dates"><?= htmlspecialchars($dateStr, ENT_QUOTES, 'UTF-8') ?></div>
                <?php if ($description !== '' || $notes !== ''): ?>
                  <div class="event-notes"><?= htmlspecialchars(trim($description . ($description !== '' && $notes !== '' ? "\n" : '') . $notes), ENT_QUOTES, 'UTF-8') ?></div>
                <?php endif; ?>
              </div>
              <div style="display:flex; gap:6px; align-items:center;">
                <span class="event-badge <?= $is_completed == 0 ? 'badge-completed' : 'badge-pending' ?>">
                  <?= $is_completed == 0 ? 'Completed' : 'Pending' ?>
                </span>
                <span class="event-badge <?= $status === 'public' ? 'badge-public' : 'badge-private' ?>">
                  <?= $status === 'public' ? 'Public' : 'Private' ?>
                </span>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>

  <!-- Footer -->
  <div class="doc-footer">
    <div class="sig-line">Authorized Signatory</div>
    <div class="footer-note">
      <?= htmlspecialchars($company_name, ENT_QUOTES, 'UTF-8') ?> &mdash; Generated <?= htmlspecialchars($printed_at, ENT_QUOTES, 'UTF-8') ?>
    </div>
  </div>

</div>

<script>
  function applyFilters() {
    const month = document.getElementById('monthFilter').value;
    const status = document.getElementById('statusFilter').value;
    
    const url = new URL(window.location.href);
    if (month) url.searchParams.set('month', month);
    else url.searchParams.delete('month');
    
    if (status && status !== 'all') url.searchParams.set('status', status);
    else url.searchParams.delete('status');
    
    window.location.href = url.toString();
  }
  
  function clearFilters() {
    const url = new URL(window.location.href);
    url.searchParams.delete('month');
    url.searchParams.delete('status');
    window.location.href = url.toString();
  }
</script>
</body>
</html>
