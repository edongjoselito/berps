<!DOCTYPE html>
<html lang="en">

<?php include('includes/head.php'); ?>

<body>
  <div id="wrapper">

    <?php include('includes/top-nav-bar.php'); ?>
    <?php include('includes/sidebar.php'); ?>

    <div class="content-page">
      <div class="content">
        <div class="container-fluid attendance-page">

          <style>
            /* ─── Google Font Import ─────────────────────────────────── */

            /* ─── Reset ─────────────────────────────────────────────── */
            .attendance-page * {
              box-sizing: border-box;
              margin: 0;
              padding: 0;
            }

            /* ─── Design Tokens ─────────────────────────────────────── */
            .attendance-page {
              --c-bg: #f7f7f5;
              --c-surface: #ffffff;
              --c-surface-2: #fafaf8;
              --c-border: #e9e9e4;
              --c-border-2: #d9d9d2;
              --c-ink: #1a1a18;
              --c-ink-2: #5a5a52;
              --c-ink-3: #9a9a90;
              --c-accent: #1a1a18;
              --c-accent-warm: #c8622a;
              --c-accent-blue: #2a6de8;
              --c-accent-green: #1a7a4a;
              --c-accent-amber: #c07a1a;
              --c-accent-red: #d0352a;
              --c-accent-teal: #1a7a8a;

              --c-pill-green-bg: #eef7f2;
              --c-pill-green-fg: #1a7a4a;
              --c-pill-muted-bg: #f2f2ee;
              --c-pill-muted-fg: #8a8a7e;
              --c-punch-in-bg: #4e8ef7;
              --c-punch-out-bg: #6c757d;

              --r-xl: 16px;
              --r-lg: 12px;
              --r-md: 8px;
              --r-sm: 5px;

              --font-body: var(--font-primary);
              --font-mono: var(--font-primary);

              --shadow-sm: 0 1px 3px rgba(26, 26, 24, 0.06), 0 1px 2px rgba(26, 26, 24, 0.04);
              --shadow-md: 0 4px 12px rgba(26, 26, 24, 0.08), 0 2px 6px rgba(26, 26, 24, 0.04);
              --shadow-lg: 0 16px 40px rgba(26, 26, 24, 0.12), 0 4px 12px rgba(26, 26, 24, 0.06);

              font-family: var(--font-primary, Montserrat, Segoe UI, Arial, sans-serif);
              color: var(--c-ink);
              background: var(--c-bg);
              min-height: 100vh;
              padding-bottom: 80px;
              font-size: 14px;
              line-height: 1.5;
            }

            /* ─── Flash ─────────────────────────────────────────────── */
            .attendance-page .alert {
              border: none;
              border-radius: var(--r-lg);
              font-size: 0.85rem;
              font-weight: 500;
              background: var(--c-accent-green);
              color: #fff;
              padding: 10px 16px;
              margin-top: 20px;
            }

            .attendance-page .alert .close {
              color: #fff;
              opacity: 0.7;
              text-shadow: none;
            }

            /* ─── Page Header ───────────────────────────────────────── */
            .attendance-page .page-header {
              display: flex;
              align-items: flex-end;
              justify-content: space-between;
              gap: 16px;
              padding: 28px 0 20px;
              flex-wrap: wrap;
              border-bottom: 1px solid var(--c-border);
              margin-bottom: 22px;
            }

            .attendance-page .page-eyebrow {
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

            .attendance-page .page-eyebrow span {
              width: 4px;
              height: 4px;
              border-radius: 50%;
              background: var(--c-accent-warm);
              display: inline-block;
            }

            .attendance-page .page-title {
              font-size: 1.55rem;
              font-weight: 500;
              letter-spacing: -0.025em;
              color: var(--c-ink);
              line-height: 1.2;
            }

            .attendance-page .page-subtitle {
              margin-top: 3px;
              color: var(--c-ink-3);
              font-size: 0.8rem;
              font-weight: 400;
            }

            .attendance-page .page-actions {
              display: flex;
              gap: 8px;
              align-items: center;
              flex-wrap: wrap;
            }

            /* ─── Buttons ───────────────────────────────────────────── */
            .attendance-page .btn-action,
            .attendance-page .btn-submit,
            .attendance-page .btn-ghost {
              display: inline-flex;
              align-items: center;
              gap: 5px;
              border-radius: var(--r-md);
              font-size: 0.78rem;
              font-weight: 600;
              font-family: var(--font-primary, Montserrat, Segoe UI, Arial, sans-serif);
              padding: 7px 13px;
              transition: all 0.15s ease;
              text-decoration: none;
              cursor: pointer;
              line-height: 1;
              white-space: nowrap;
              letter-spacing: 0.01em;
              border: none;
            }

            .attendance-page .btn-submit {
              background: var(--c-ink);
              color: #fff;
            }

            .attendance-page .btn-submit:hover {
              background: #333330;
              color: #fff;
              text-decoration: none;
              transform: translateY(-1px);
              box-shadow: var(--shadow-md);
            }

            .attendance-page .btn-action {
              background: var(--c-surface);
              color: var(--c-ink-2);
              border: 1px solid var(--c-border);
            }

            .attendance-page .btn-action:hover {
              background: var(--c-surface-2);
              border-color: var(--c-border-2);
              color: var(--c-ink);
              text-decoration: none;
            }

            .attendance-page .btn-ghost {
              background: transparent;
              color: var(--c-ink-3);
              border: 1px solid var(--c-border);
            }

            .attendance-page .btn-ghost:hover {
              background: var(--c-surface);
              color: var(--c-ink);
              text-decoration: none;
            }

            /* ─── Punch Cards ───────────────────────────────────────── */
            .attendance-page .punch-grid {
              display: grid;
              grid-template-columns: 1fr 1fr;
              gap: 10px;
              margin-bottom: 22px;
            }

            .attendance-page .punch-card {
              border-radius: var(--r-xl);
              padding: 16px 18px;
              display: flex;
              align-items: center;
              gap: 14px;
              transition: transform 0.15s ease, box-shadow 0.15s ease;
            }

            .attendance-page .punch-card:hover {
              transform: translateY(-2px);
              box-shadow: var(--shadow-md);
            }

            .attendance-page .punch-card.punch-in {
              background: linear-gradient(135deg, #4e8ef7, #6dc5ff);
            }

            .attendance-page .punch-card.punch-out {
              background: linear-gradient(135deg, #6c757d, #9ea3a7);
            }

            .attendance-page .punch-icon {
              width: 38px;
              height: 38px;
              border-radius: var(--r-md);
              display: flex;
              align-items: center;
              justify-content: center;
              font-size: 1.1rem;
              flex-shrink: 0;
            }

            .punch-in .punch-icon {
              background: rgba(255, 255, 255, 0.1);
              color: #fff;
            }

            .punch-out .punch-icon {
              background: rgba(255, 255, 255, 0.15);
              color: #fff;
            }

            .attendance-page .punch-info {
              flex: 1;
              min-width: 0;
            }

            .attendance-page .punch-label {
              font-size: 0.84rem;
              font-weight: 600;
              margin-bottom: 1px;
            }

            .punch-in .punch-label {
              color: #fff;
            }

            .punch-out .punch-label {
              color: #fff;
            }

            .attendance-page .punch-sublabel {
              font-size: 0.7rem;
            }

            .punch-in .punch-sublabel {
              color: rgba(255, 255, 255, 0.55);
            }

            .punch-out .punch-sublabel {
              color: rgba(255, 255, 255, 0.65);
            }

            .attendance-page .punch-btn {
              display: inline-flex;
              align-items: center;
              padding: 6px 13px;
              border-radius: var(--r-md);
              font-size: 0.75rem;
              font-weight: 600;
              text-decoration: none;
              white-space: nowrap;
              flex-shrink: 0;
              transition: all 0.13s;
              font-family: var(--font-primary, Montserrat, Segoe UI, Arial, sans-serif);
              letter-spacing: 0.01em;
            }

            .punch-in .punch-btn {
              background: rgba(255, 255, 255, 0.14);
              border: 1px solid rgba(255, 255, 255, 0.22);
              color: #fff;
            }

            .punch-in .punch-btn:hover {
              background: rgba(255, 255, 255, 0.24);
              color: #fff;
              text-decoration: none;
            }

            .punch-out .punch-btn {
              background: rgba(255, 255, 255, 0.18);
              border: 1px solid rgba(255, 255, 255, 0.28);
              color: #fff;
            }

            .punch-out .punch-btn:hover {
              background: rgba(255, 255, 255, 0.28);
              color: #fff;
              text-decoration: none;
            }

            /* ─── Main Card ─────────────────────────────────────────── */
            .attendance-page .theme-card {
              background: var(--c-surface);
              border: 1px solid var(--c-border);
              border-radius: var(--r-xl);
              overflow: hidden;
              margin-bottom: 16px;
              box-shadow: var(--shadow-sm);
            }

            .attendance-page .theme-card-head {
              display: flex;
              align-items: center;
              justify-content: space-between;
              gap: 12px;
              padding: 14px 20px;
              border-bottom: 1px solid var(--c-border);
              flex-wrap: wrap;
            }

            .attendance-page .theme-card-title {
              font-size: 0.88rem;
              font-weight: 600;
              color: var(--c-ink);
              letter-spacing: -0.01em;
            }

            .attendance-page .theme-card-subtitle {
              margin-top: 1px;
              color: var(--c-ink-3);
              font-size: 0.73rem;
            }

            /* ─── Toolbar ───────────────────────────────────────────── */
            .attendance-page .card-toolbar {
              display: flex;
              align-items: center;
              gap: 6px;
              padding: 10px 16px;
              border-bottom: 1px solid var(--c-border);
              flex-wrap: wrap;
              background: var(--c-surface-2);
            }

            .attendance-page .card-toolbar .ml-auto {
              margin-left: auto;
            }

            .attendance-page .card-toolbar .mdi {
              font-size: 0.9rem;
            }

            /* ─── Table ─────────────────────────────────────────────── */
            .attendance-page .att-table {
              width: 100% !important;
              border-collapse: collapse;
            }

            .attendance-page .att-table thead th {
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

            .attendance-page .att-table td {
              padding: 9px 16px;
              border-bottom: 1px solid var(--c-border);
              vertical-align: middle;
              font-size: 0.82rem;
              color: var(--c-ink);
            }

            .attendance-page .att-table tbody tr:last-child td {
              border-bottom: none;
            }

            .attendance-page .att-table tbody tr:hover td {
              background: var(--c-surface-2);
              transition: background 0.1s;
            }

            .attendance-page .cell-muted {
              color: var(--c-ink-3);
              font-size: 0.78rem;
              font-family: var(--font-primary, Montserrat, Segoe UI, Arial, sans-serif);
              font-weight: 400;
              letter-spacing: -0.01em;
            }

            /* ─── Hours display ─────────────────────────────────────── */
            .attendance-page .hours-badge {
              font-family: var(--font-primary, Montserrat, Segoe UI, Arial, sans-serif);
              font-size: 0.78rem;
              font-weight: 500;
              color: var(--c-ink);
              background: var(--c-surface-2);
              border: 1px solid var(--c-border);
              border-radius: var(--r-sm);
              padding: 2px 8px;
              display: inline-block;
            }

            /* ─── Status pills ──────────────────────────────────────── */
            .attendance-page .status-pill {
              display: inline-flex;
              align-items: center;
              gap: 4px;
              padding: 3px 8px;
              border-radius: 999px;
              font-size: 0.65rem;
              font-weight: 600;
              letter-spacing: 0.06em;
              text-transform: uppercase;
            }

            .attendance-page .pill-success {
              background: var(--c-pill-green-bg);
              color: var(--c-pill-green-fg);
            }

            .attendance-page .pill-muted {
              background: var(--c-pill-muted-bg);
              color: var(--c-pill-muted-fg);
            }

            /* ─── Breakdown ─────────────────────────────────────────── */
            .attendance-page .breakdown-line {
              display: inline-flex;
              align-items: center;
              gap: 5px;
              font-size: 0.75rem;
              color: var(--c-ink-2);
              font-family: var(--font-primary, Montserrat, Segoe UI, Arial, sans-serif);
              padding: 1px 0;
              line-height: 1.5;
            }

            .attendance-page .breakdown-line+.breakdown-line {
              margin-top: 1px;
            }

            .attendance-page .breakdown-line .mdi {
              color: var(--c-ink-3);
              font-size: 0.7rem;
            }

            .attendance-page .see-more {
              display: inline-block;
              margin-top: 3px;
              font-size: 0.7rem;
              color: var(--c-accent-blue);
              font-weight: 500;
              text-decoration: none;
              font-family: var(--font-primary, Montserrat, Segoe UI, Arial, sans-serif);
            }

            .attendance-page .see-more:hover {
              text-decoration: underline;
            }

            /* ─── Action Icons ──────────────────────────────────────── */
            .attendance-page .action-icon {
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

            .attendance-page .action-icon:hover {
              background: var(--c-surface-2);
              border-color: var(--c-border);
              color: var(--c-ink);
              text-decoration: none;
            }

            .attendance-page .action-icon.act-blue:hover {
              color: var(--c-accent-blue);
            }

            .attendance-page .action-icon.act-sky:hover {
              color: var(--c-accent-teal);
            }

            /* ─── Total Bar ─────────────────────────────────────────── */
            .attendance-page .total-bar {
              display: flex;
              align-items: center;
              justify-content: flex-end;
              gap: 16px;
              padding: 10px 18px;
              border-top: 1px solid var(--c-border);
              font-size: 0.75rem;
              color: var(--c-ink-3);
              background: var(--c-surface-2);
              flex-wrap: wrap;
            }

            .attendance-page .total-bar strong {
              color: var(--c-ink);
              font-family: var(--font-primary, Montserrat, Segoe UI, Arial, sans-serif);
              font-size: 0.82rem;
              font-weight: 500;
            }

            /* ─── DataTables Overrides ──────────────────────────────── */
            .attendance-page .dataTables_wrapper {
              padding: 0;
            }

            .attendance-page .dataTables_wrapper .dataTables_filter,
            .attendance-page .dataTables_wrapper .dataTables_length {
              padding: 10px 16px 0;
            }

            .attendance-page .dataTables_wrapper .dataTables_info,
            .attendance-page .dataTables_wrapper .dataTables_paginate {
              padding: 8px 16px;
            }

            .attendance-page .dataTables_wrapper .dataTables_info {
              font-size: 0.73rem;
              color: var(--c-ink-3);
            }

            .attendance-page .dataTables_wrapper .dataTables_filter label,
            .attendance-page .dataTables_wrapper .dataTables_length label {
              font-size: 0.76rem;
              color: var(--c-ink-2);
              font-weight: 400;
              font-family: var(--font-primary, Montserrat, Segoe UI, Arial, sans-serif);
            }

            .attendance-page .dataTables_wrapper .dataTables_filter input,
            .attendance-page .dataTables_wrapper .dataTables_length select {
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

            .attendance-page .dataTables_wrapper .dataTables_filter input:focus,
            .attendance-page .dataTables_wrapper .dataTables_length select:focus {
              outline: none;
              border-color: var(--c-ink);
              box-shadow: 0 0 0 2px rgba(26, 26, 24, 0.07);
            }

            .attendance-page .dataTables_wrapper .dataTables_paginate .paginate_button {
              border-radius: var(--r-md) !important;
              padding: 3px 9px !important;
              font-size: 0.76rem !important;
              border: 1px solid transparent !important;
              background: transparent !important;
              color: var(--c-ink-2) !important;
              font-family: var(--font-primary, Montserrat, Segoe UI, Arial, sans-serif);
            }

            .attendance-page .dataTables_wrapper .dataTables_paginate .paginate_button.current,
            .attendance-page .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover {
              background: var(--c-ink) !important;
              border-color: var(--c-ink) !important;
              color: #fff !important;
              box-shadow: none !important;
            }

            .attendance-page .dataTables_wrapper .dataTables_paginate .paginate_button:hover:not(.current) {
              background: var(--c-surface-2) !important;
              border-color: var(--c-border) !important;
              color: var(--c-ink) !important;
            }

            /* ─── Modals ────────────────────────────────────────────── */
            .attendance-page .theme-modal .modal-content {
              border: none;
              border-radius: var(--r-xl);
              overflow: hidden;
              box-shadow: var(--shadow-lg);
            }

            .attendance-page .theme-modal .modal-header {
              padding: 14px 18px;
              border-bottom: 1px solid var(--c-border);
              background: var(--c-surface-2);
            }

            .attendance-page .theme-modal .modal-title {
              color: var(--c-ink);
              font-size: 0.88rem;
              font-weight: 600;
              letter-spacing: -0.01em;
            }

            .attendance-page .theme-modal .close {
              color: var(--c-ink-3);
              opacity: 1;
              text-shadow: none;
              font-size: 1.1rem;
            }

            .attendance-page .theme-modal .close:hover {
              color: var(--c-ink);
            }

            .attendance-page .theme-modal .modal-body {
              padding: 18px;
            }

            .attendance-page .theme-modal .modal-footer {
              padding: 0 18px 18px;
              border-top: none;
              gap: 8px;
            }

            .attendance-page .theme-modal label {
              color: var(--c-ink-2);
              font-size: 0.74rem;
              font-weight: 600;
              letter-spacing: 0.04em;
              text-transform: uppercase;
              margin-bottom: 5px;
              display: block;
            }

            .attendance-page .theme-modal .form-control {
              border: 1px solid var(--c-border);
              border-radius: var(--r-md);
              min-height: 36px;
              box-shadow: none;
              font-size: 0.82rem;
              color: var(--c-ink);
              font-family: var(--font-primary, Montserrat, Segoe UI, Arial, sans-serif);
              background: var(--c-surface);
            }

            .attendance-page .theme-modal .form-control:focus {
              border-color: var(--c-ink);
              box-shadow: 0 0 0 2px rgba(26, 26, 24, 0.07);
              outline: none;
            }

            .attendance-page .theme-modal .btn {
              border-radius: var(--r-md);
              font-weight: 600;
              font-size: 0.8rem;
              padding: 7px 15px;
              font-family: var(--font-primary, Montserrat, Segoe UI, Arial, sans-serif);
              border: none;
              cursor: pointer;
              transition: all 0.13s;
            }

            .attendance-page .theme-modal .btn-primary {
              background: var(--c-ink);
              color: #fff;
            }

            .attendance-page .theme-modal .btn-primary:hover {
              background: #333;
            }

            .attendance-page .theme-modal .btn-light {
              background: var(--c-surface);
              color: var(--c-ink-2);
              border: 1px solid var(--c-border);
            }

            .attendance-page .theme-modal .btn-light:hover {
              background: var(--c-surface-2);
              color: var(--c-ink);
            }

            .attendance-page .theme-modal p {
              font-size: 0.76rem;
              color: var(--c-ink-3);
              margin-top: 8px;
            }

            /* ─── Responsive ────────────────────────────────────────── */
            @media (max-width: 767px) {
              .attendance-page .page-title {
                font-size: 1.3rem;
              }

              .attendance-page .page-header {
                flex-direction: column;
                align-items: flex-start;
              }

              .attendance-page .punch-grid {
                grid-template-columns: 1fr;
              }
            }

            /* ─── Subtle entry animation ────────────────────────────── */
            .attendance-page .theme-card {
              animation: fadeUp 0.28s ease both;
            }

            .attendance-page .punch-grid {
              animation: fadeUp 0.22s ease both;
            }

            @keyframes fadeUp {
              from {
                opacity: 0;
                transform: translateY(8px);
              }

              to {
                opacity: 1;
                transform: translateY(0);
              }
            }
          </style>

          <?php
          $date = (string)$this->input->get('date');
          $from = (string)$this->input->get('from');
          $to   = (string)$this->input->get('to');
          $view = (string)$this->input->get('view');

          $hasDateParam  = ($date !== '');
          $hasRangeParam = ($from !== '' || $to !== '');
          $level = $this->session->userdata('level');

          $viewMode     = ($level === 'Admin' && $view === '1');
          $presenceMode = ($level === 'Admin' && $hasDateParam && !$viewMode);

          if (!$hasDateParam && !$hasRangeParam) {
            $from = $to = date('Y-m-d');
          } else {
            if ($hasDateParam) {
              $from = $to = $date;
            } else {
              if ($from === '') $from = $to;
              if ($to === '')   $to   = $from;
            }
          }

          if ($from && $to && strtotime($from) > strtotime($to)) {
            $tmp  = $from;
            $from = $to;
            $to = $tmp;
          }

          $isToday = ($from === date('Y-m-d') && $to === date('Y-m-d'));
          $selectedLabel = ($from === $to)
            ? date('F j, Y', strtotime($from))
            : (date('F j, Y', strtotime($from)) . ' – ' . date('F j, Y', strtotime($to)));

          $pageTitle     = $isToday ? 'Attendance' : ('Attendance — ' . $selectedLabel);
          $accompBtnText = $isToday ? "Today's Accomplishments" : ("Accomplishments: " . $selectedLabel);

          if (!$hasDateParam && !$hasRangeParam && !empty($data)) {
            $data = array_values(array_filter($data, function ($r) use ($from) {
              return !empty($r->logDate) && $r->logDate === $from;
            }));
          }

          $selected_date = ($from !== '' ? $from : date('Y-m-d'));
          $attendanceOnly = $viewMode;
          $presenceOnly   = $presenceMode;
          ?>

          <?php
          if (!function_exists('human_hours_label')) {
            function human_hours_label($label)
            {
              $parts = explode(':', (string)$label);
              $h = isset($parts[0]) ? (int)$parts[0] : 0;
              $m = isset($parts[1]) ? (int)$parts[1] : 0;
              $out = [$h . 'h'];
              if ($m > 0) $out[] = str_pad($m, 2, '0', STR_PAD_LEFT) . 'm';
              return implode(' ', $out);
            }
          }
          ?>

          <?php $flash = $this->session->flashdata('msg'); ?>
          <?php if (!empty($flash)): ?>
            <div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
              <?= $flash; ?>
              <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
            </div>
          <?php endif; ?>

          <!-- Page header -->
          <div class="page-header">
            <div>
              <div class="page-eyebrow"><span></span>Attendance</div>
              <h4 class="page-title"><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8'); ?></h4>
              <div class="page-subtitle">
                <?= $level === 'Admin' ? 'All employee punch logs' : 'Your daily punch logs'; ?>
                <?php if (!$isToday): ?>
                  &mdash; <?= htmlspecialchars($selectedLabel, ENT_QUOTES, 'UTF-8'); ?>
                <?php endif; ?>
              </div>
            </div>
            <?php if ($level === 'Admin' && !$attendanceOnly && !$presenceOnly): ?>
              <div class="page-actions">
                <a href="<?= base_url(); ?>Page/dayAccomplishments?from=<?= htmlspecialchars($from, ENT_QUOTES, 'UTF-8'); ?>&to=<?= htmlspecialchars($to, ENT_QUOTES, 'UTF-8'); ?>"
                  class="btn-submit">
                  <i class="mdi mdi-trophy-outline"></i>
                  <?= htmlspecialchars($accompBtnText, ENT_QUOTES, 'UTF-8'); ?>
                </a>
              </div>
            <?php endif; ?>
          </div>

          <!-- Staff Punch Cards -->
          <?php if ($level !== 'Admin'): ?>
            <div class="punch-grid">
              <div class="punch-card punch-in">
                <div class="punch-icon"><i class="mdi mdi-clock-plus"></i></div>
                <div class="punch-info">
                  <div class="punch-label">Time-In</div>
                  <div class="punch-sublabel">Auto-detects AM or PM slot</div>
                </div>
                <a class="punch-btn" href="<?= base_url(); ?>Page/amTimeIn">Time-In</a>
              </div>
              <div class="punch-card punch-out">
                <div class="punch-icon"><i class="mdi mdi-clock-outline"></i></div>
                <div class="punch-info">
                  <div class="punch-label">Time-Out</div>
                  <div class="punch-sublabel">Closes the latest open punch</div>
                </div>
                <a class="punch-btn" href="<?= base_url(); ?>Page/amTimeOut">Time-Out</a>
              </div>
            </div>
          <?php endif; ?>

          <!-- Attendance card -->
          <div class="theme-card">
            <div class="theme-card-head">
              <div>
                <h5 class="theme-card-title">Attendance Log</h5>
                <div class="theme-card-subtitle">
                  <?= $level === 'Admin' ? 'Viewing all employees' : 'Viewing your logs'; ?>
                </div>
              </div>
            </div>

            <!-- Toolbar -->
            <div class="card-toolbar">
              <?php
              $fromVal = (string)$this->input->get('from');
              $toVal   = (string)$this->input->get('to');
              if ($fromVal === '' && $toVal === '') {
                $fromVal = $toVal = date('Y-m-d');
              } else {
                if ($fromVal === '') $fromVal = $toVal;
                if ($toVal === '')   $toVal   = $fromVal;
              }
              $rangeFilterHelp = ($level === 'Admin')
                ? 'Reloads the page with the selected date range.'
                : 'Shows only your attendance within the selected range.';
              $yesterday = date('Y-m-d', strtotime('-1 day'));
              ?>

              <button type="button" class="btn-action" data-toggle="modal" data-target="#monthFilterModal">
                <i class="mdi mdi-calendar-month-outline"></i>
                Filter Range
              </button>

              <button type="button" class="btn-submit" data-toggle="modal" data-target="#dayAccompModal">
                <i class="mdi mdi-calendar-search"></i>
                View Attendance
              </button>

              <?php if ($level !== 'Admin'): ?>
                <a href="<?= base_url(); ?>Page/attendanceList?date=<?= htmlspecialchars($yesterday, ENT_QUOTES, 'UTF-8'); ?>" class="btn-action">
                  <i class="mdi mdi-calendar-arrow-left"></i>
                  Yesterday
                </a>
              <?php endif; ?>

              <?php if ($level === 'Admin' && !$attendanceOnly && !$presenceOnly): ?>
                <button type="button" id="toggle-grandtotal-col" class="btn-action">
                  <i class="mdi mdi-eye"></i>
                  Grand Total
                </button>
              <?php endif; ?>

              <?php if (!empty($this->input->get('date')) || !empty($this->input->get('from')) || !empty($this->input->get('to')) || !empty($this->input->get('view'))): ?>
                <a href="<?= base_url(); ?>Page/attendanceList" class="btn-ghost ml-auto">
                  <i class="mdi mdi-close"></i>
                  Clear Filter
                </a>
              <?php endif; ?>
            </div>

            <!-- Table -->
            <div class="table-responsive">
              <table id="attendance-table" class="table att-table" style="width:100%">
                <thead>
                  <?php if ($level === 'Admin' && $attendanceOnly): ?>
                    <tr>
                      <th>Employee</th>
                      <th style="text-align:center;">Total Hours</th>
                      <th style="text-align:center;">Actions</th>
                    </tr>
                  <?php elseif ($level === 'Admin' && $presenceOnly): ?>
                    <tr>
                      <th>Employee</th>
                      <th style="text-align:center;">Attendance</th>
                    </tr>
                  <?php elseif ($level === 'Admin'): ?>
                    <tr>
                      <th>Date</th>
                      <th>Employee</th>
                      <th>Status</th>
                      <th>Time Breakdown</th>
                      <th style="text-align:center;">Per-Day Hours</th>
                      <th style="text-align:center;">Grand Total</th>
                      <th style="text-align:center;">Accomplishment</th>
                    </tr>
                  <?php else: ?>
                    <tr>
                      <th>Date</th>
                      <th>Time Breakdown</th>
                      <th style="text-align:center;">Per-Day Hours</th>
                      <th style="text-align:center;">Accomplishment</th>
                    </tr>
                  <?php endif; ?>
                </thead>

                <tbody>
                  <?php if (!empty($data)): ?>

                    <?php if ($level === 'Admin' && $attendanceOnly): ?>
                      <?php
                      $seen = [];
                      foreach ($data as $row) {
                        $empId = !empty($row->IDNumber) ? $row->IDNumber : (!empty($row->user_id) ? $row->user_id : (!empty($row->username) ? $row->username : ''));
                        if ($empId === '') continue;
                        $firstName = isset($row->fName) ? trim((string)$row->fName) : '';
                        $displayName = $firstName !== '' ? $firstName : (!empty($row->username) ? $row->username : 'Name not set');
                        if (!isset($seen[$empId])) {
                          $seen[$empId] = ['id' => $empId, 'name' => $displayName, 'total_seconds' => 0];
                        }
                        $sec = isset($row->total_seconds) ? (int)$row->total_seconds : 0;
                        $seen[$empId]['total_seconds'] += $sec;
                      }
                      $fmt_hhmm = function ($totalSeconds) {
                        $h = (int)floor($totalSeconds / 3600);
                        $m = (int)floor(($totalSeconds % 3600) / 60);
                        return sprintf('%02d:%02d', $h, $m);
                      };
                      $endDate = ($to !== '' ? $to : ($from !== '' ? $from : date('Y-m-d')));
                      $reportPeriod = date('Y-m', strtotime($endDate));
                      $clipboardDate = ($from === $to) ? $from : $endDate;
                      uasort($seen, function ($a, $b) {
                        return ($b['total_seconds'] <=> $a['total_seconds']);
                      });
                      ?>
                      <?php foreach ($seen as $emp): ?>
                        <?php
                        $totalLabel = $fmt_hhmm($emp['total_seconds']);
                        $prettyEnd  = date('M. d, Y', strtotime($endDate));
                        $tipDay     = ($from === $to)
                          ? ("Accomplishments for " . date('F j, Y', strtotime($from)))
                          : ("Accomplishments up to " . $prettyEnd);
                        ?>
                        <tr data-total-seconds="<?= (int)$emp['total_seconds']; ?>">
                          <td class="cell-muted"><?= htmlspecialchars($emp['name'], ENT_QUOTES, 'UTF-8'); ?></td>
                          <td style="text-align:center;"><span class="hours-badge"><?= human_hours_label($totalLabel); ?></span></td>
                          <td style="text-align:center;">
                            <div style="display:inline-flex;align-items:center;gap:4px;">
                              <a class="action-icon act-blue"
                                href="<?= base_url(); ?>Page/accomplishmentsPerEmployee?name=<?= urlencode($emp['id']); ?>&date=<?= htmlspecialchars($clipboardDate, ENT_QUOTES, 'UTF-8'); ?>"
                                title="<?= htmlspecialchars($tipDay, ENT_QUOTES, 'UTF-8'); ?>">
                                <i class="mdi mdi-clipboard-text-outline"></i>
                              </a>
                              <a class="action-icon act-sky"
                                href="<?= base_url(); ?>Page/employeeAccomplishmentData?user_id=<?= urlencode($emp['id']); ?>&report_period=<?= urlencode($reportPeriod); ?>&end_date=<?= urlencode($endDate); ?>"
                                title="Monthly accomplishments up to <?= htmlspecialchars($prettyEnd, ENT_QUOTES, 'UTF-8'); ?>">
                                <i class="mdi mdi-calendar-month-outline"></i>
                              </a>
                            </div>
                          </td>
                        </tr>
                      <?php endforeach; ?>

                    <?php elseif ($level === 'Admin' && $presenceOnly): ?>
                      <?php
                      $seen = [];
                      foreach ($data as $row) {
                        $empId = !empty($row->IDNumber) ? $row->IDNumber : (!empty($row->user_id) ? $row->user_id : (!empty($row->username) ? $row->username : ''));
                        if ($empId === '' || isset($seen[$empId])) continue;
                        $firstName   = isset($row->fName) ? trim((string)$row->fName) : '';
                        $displayName = $firstName !== '' ? $firstName : (!empty($row->username) ? $row->username : 'Name not set');
                        $seen[$empId] = ['name' => $displayName, 'id' => $empId];
                      }
                      ?>
                      <?php foreach ($seen as $emp): ?>
                        <tr>
                          <td><?= htmlspecialchars($emp['name'], ENT_QUOTES, 'UTF-8'); ?></td>
                          <td style="text-align:center;">
                            <span class="status-pill pill-success"><i class="mdi mdi-check"></i> Present</span>
                          </td>
                        </tr>
                      <?php endforeach; ?>

                    <?php else: ?>

                      <?php foreach ($data as $row): ?>
                        <?php
                        $empId           = !empty($row->IDNumber) ? $row->IDNumber : (!empty($row->user_id) ? $row->user_id : (!empty($row->username) ? $row->username : ''));
                        $firstName       = isset($row->fName) ? trim((string)$row->fName) : '';
                        $displayName     = $firstName !== '' ? $firstName : (!empty($row->username) ? $row->username : 'Name not set');
                        $accompKey       = $empId;
                        $rowTotalSeconds = isset($row->total_seconds) ? (int)$row->total_seconds : 0;
                        $rowTotalLabel   = isset($row->total_hours_label) ? $row->total_hours_label : '00:00';
                        $orderDate       = isset($row->logDate) ? (string)$row->logDate : '';
                        $rowMonth        = $orderDate !== '' ? date('Y-m', strtotime($orderDate)) : '';
                        $hasAcc          = !empty($row->accomplishment_count) && !empty($row->has_time_in);
                        $tipDay          = $isToday ? "Today's accomplishments" : ("Accomplishments for " . $selectedLabel);
                        $prettyDate      = date('M. d, Y', strtotime($row->logDate));
                        ?>
                        <tr data-emp-key="<?= htmlspecialchars($empId, ENT_QUOTES, 'UTF-8'); ?>" data-total-seconds="<?= (int)$rowTotalSeconds; ?>">
                          <td data-order="<?= htmlspecialchars($orderDate, ENT_QUOTES, 'UTF-8'); ?>" class="cell-muted"><?= $prettyDate; ?></td>

                          <?php if ($level === 'Admin'): ?>
                            <td><?= htmlspecialchars($displayName, ENT_QUOTES, 'UTF-8'); ?></td>

                            <td data-order="<?= $hasAcc ? 1 : 0; ?>">
                              <?php if ($hasAcc): ?>
                                <span class="status-pill pill-success"><i class="mdi mdi-check"></i> Has accomplishment</span>
                              <?php else: ?>
                                <span class="status-pill pill-muted">None</span>
                              <?php endif; ?>
                            </td>
                          <?php endif; ?>

                          <td>
                            <?php
                            $intervals  = !empty($row->intervals) ? $row->intervals : [];
                            $intCount   = count($intervals);
                            $maxVisible = 3;
                            if ($intCount > 0):
                              if ($intCount <= $maxVisible):
                                foreach ($intervals as $intv): ?>
                                  <div class="breakdown-line"><i class="mdi mdi-clock-outline"></i><?= $intv['label']; ?></div>
                                <?php endforeach;
                              else:
                                $firstTwo = array_slice($intervals, 0, 2);
                                $lastOne  = $intervals[$intCount - 1];
                                $hidden   = array_slice($intervals, 2, $intCount - 3);
                                foreach ($firstTwo as $intv): ?>
                                  <div class="breakdown-line"><i class="mdi mdi-clock-outline"></i><?= $intv['label']; ?></div>
                                <?php endforeach; ?>
                                <div class="more-intervals d-none">
                                  <?php foreach ($hidden as $intv): ?>
                                    <div class="breakdown-line"><i class="mdi mdi-clock-outline"></i><?= $intv['label']; ?></div>
                                  <?php endforeach; ?>
                                </div>
                                <div class="breakdown-line"><i class="mdi mdi-clock-outline"></i><?= $lastOne['label']; ?></div>
                                <a href="#" class="see-more" data-target="more-intervals">+<?= $intCount - $maxVisible; ?> more</a>
                              <?php endif; ?>
                            <?php else: ?>
                              <span style="color:var(--c-ink-3);font-size:0.75rem;">No punches</span>
                            <?php endif; ?>
                          </td>

                          <td style="text-align:center;"><span class="hours-badge"><?= human_hours_label($rowTotalLabel); ?></span></td>

                          <?php if ($level === 'Admin'): ?>
                            <td style="text-align:center;">
                              <?php
                              $gt = !empty($row->grand_total_label) ? $row->grand_total_label : '00:00';
                              echo '<span class="hours-badge">' . human_hours_label($gt) . '</span>';
                              ?>
                            </td>
                            <td style="text-align:center;">
                              <div style="display:inline-flex;align-items:center;gap:4px;">
                                <a class="action-icon act-blue"
                                  href="<?= base_url(); ?>Page/accomplishmentsPerEmployee?name=<?= urlencode($accompKey); ?>&date=<?= htmlspecialchars($orderDate, ENT_QUOTES, 'UTF-8'); ?>"
                                  title="<?= htmlspecialchars($tipDay, ENT_QUOTES, 'UTF-8'); ?>">
                                  <i class="mdi mdi-clipboard-text-outline"></i>
                                </a>
                                <a class="action-icon act-sky"
                                  href="<?= base_url(); ?>Page/employeeAccomplishmentData?user_id=<?= urlencode($accompKey); ?>&report_period=<?= urlencode($rowMonth); ?>&end_date=<?= urlencode($orderDate); ?>"
                                  title="Monthly accomplishments up to <?= htmlspecialchars($prettyDate, ENT_QUOTES, 'UTF-8'); ?>">
                                  <i class="mdi mdi-calendar-month-outline"></i>
                                </a>
                              </div>
                            </td>
                          <?php else: ?>
                            <td style="text-align:center;">
                              <a class="action-icon act-blue"
                                href="<?= base_url(); ?>Page/accomplishmentStaff?assignedPerson=<?= urlencode($accompKey); ?>&date=<?= $row->logDate; ?>"
                                title="View accomplishment">
                                <i class="mdi mdi-clipboard-text-outline"></i>
                              </a>
                            </td>
                          <?php endif; ?>
                        </tr>
                      <?php endforeach; ?>

                    <?php endif; ?>

                  <?php else: ?>
                    <tr>
                      <td colspan="<?= ($level === 'Admin' && $attendanceOnly) ? 3 : (($level === 'Admin' && $presenceOnly) ? 2 : ($level === 'Admin' ? 7 : 4)); ?>"
                        style="text-align:center;padding:2.5rem;color:var(--c-ink-3);font-size:0.82rem;">
                        No attendance records found.
                      </td>
                    </tr>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>

            <!-- Grand total bar -->
            <?php if ($level === 'Admin' && $attendanceOnly): ?>
              <div class="total-bar" id="grand-total">
                Total hours (all employees): <strong id="grand-total-filtered">00:00</strong>
              </div>
            <?php elseif ($level === 'Admin' && !$presenceOnly): ?>
              <div class="total-bar" id="grand-total">
                <span>Overall grand total: <strong><?= isset($grand_total_all) ? $grand_total_all : '00:00'; ?></strong></span>
                <span>Filtered total: <strong id="grand-total-filtered">00:00</strong></span>
              </div>
              <div id="employee-total-summary"></div>
            <?php elseif ($level !== 'Admin'): ?>
              <div class="total-bar" id="grand-total">
                Grand total: <strong id="grand-total-filtered">00:00</strong>
              </div>
            <?php endif; ?>
          </div>

          <!-- Filter Range Modal -->
          <div class="modal fade theme-modal" id="monthFilterModal" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
              <div class="modal-content">
                <div class="modal-header">
                  <h5 class="modal-title">Filter Attendance Range</h5>
                  <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <form method="get" action="<?= base_url(); ?>Page/attendanceList">
                  <div class="modal-body">
                    <div class="form-row">
                      <div class="form-group col-6">
                        <label for="range-from">From</label>
                        <input type="date" id="range-from" name="from" class="form-control"
                          value="<?= htmlspecialchars($fromVal, ENT_QUOTES, 'UTF-8'); ?>">
                      </div>
                      <div class="form-group col-6">
                        <label for="range-to">To</label>
                        <input type="date" id="range-to" name="to" class="form-control"
                          value="<?= htmlspecialchars($toVal, ENT_QUOTES, 'UTF-8'); ?>">
                      </div>
                    </div>
                    <p><?= htmlspecialchars($rangeFilterHelp, ENT_QUOTES, 'UTF-8'); ?></p>
                  </div>
                  <div class="modal-footer">
                    <a href="<?= base_url(); ?>Page/attendanceList" class="btn btn-light">Clear</a>
                    <button type="submit" class="btn btn-primary">Apply</button>
                  </div>
                </form>
              </div>
            </div>
          </div>

          <!-- View Attendance Modal -->
          <div class="modal fade theme-modal" id="dayAccompModal" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
              <div class="modal-content">
                <div class="modal-header">
                  <h5 class="modal-title">View Attendance (From / To)</h5>
                  <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <form method="get" action="<?= base_url(); ?>Page/attendanceList">
                  <div class="modal-body">
                    <div class="form-row">
                      <div class="form-group col-6">
                        <label for="view-from">From</label>
                        <input type="date" id="view-from" name="from" class="form-control"
                          value="<?= htmlspecialchars($fromVal, ENT_QUOTES, 'UTF-8'); ?>">
                      </div>
                      <div class="form-group col-6">
                        <label for="view-to">To</label>
                        <input type="date" id="view-to" name="to" class="form-control"
                          value="<?= htmlspecialchars($toVal, ENT_QUOTES, 'UTF-8'); ?>">
                      </div>
                    </div>
                    <input type="hidden" name="view" value="1">
                    <p>Shows employee total hours and accomplishment links for the selected period.</p>
                  </div>
                  <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Apply</button>
                  </div>
                </form>
              </div>
            </div>
          </div>

        </div><!-- /.attendance-page -->
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
      var isAdmin = <?= ($level === 'Admin') ? 'true' : 'false'; ?>;
      var attendanceOnly = <?= ($attendanceOnly)    ? 'true' : 'false'; ?>;
      var presenceOnly = <?= ($presenceOnly)      ? 'true' : 'false'; ?>;

      var initialOrder;
      if (attendanceOnly) {
        initialOrder = [
          [1, 'desc']
        ];
      } else if (presenceOnly) {
        initialOrder = [
          [0, 'asc']
        ];
      } else {
        initialOrder = isAdmin ? [
          [0, 'desc'],
          [2, 'desc']
        ] : [
          [0, 'desc']
        ];
      }

      var dtColumnDefs = [];
      if (attendanceOnly) {
        dtColumnDefs.push({
          targets: 2,
          orderable: false,
          searchable: false
        });
      }
      if (!attendanceOnly && !presenceOnly) {
        dtColumnDefs.push({
          targets: -1,
          orderable: false,
          searchable: false
        });
      }

      var table = $('#attendance-table').DataTable({
        order: initialOrder,
        pageLength: 25,
        responsive: true,
        autoWidth: false,
        lengthChange: true,
        columnDefs: dtColumnDefs
      });

      if (isAdmin && !attendanceOnly && !presenceOnly) {
        var grandTotalColIndex = 5;
        table.column(grandTotalColIndex).visible(false);

        $('#toggle-grandtotal-col').on('click', function() {
          var visible = table.column(grandTotalColIndex).visible();
          table.column(grandTotalColIndex).visible(!visible);
          $(this).html(!visible ?
            '<i class="mdi mdi-eye-off"></i> Hide Grand Total' :
            '<i class="mdi mdi-eye"></i> Grand Total');
        });
      }


      function updateGrandTotal() {
        var totalSeconds = 0;
        table.rows({
          search: 'applied'
        }).every(function() {
          totalSeconds += $(this.node()).data('total-seconds') || 0;
        });
        var h = Math.floor(totalSeconds / 3600);
        var m = Math.floor((totalSeconds % 3600) / 60);
        $('#grand-total-filtered').text((h < 10 ? '0' : '') + h + ':' + (m < 10 ? '0' : '') + m);
      }

      $('[data-toggle="tooltip"]').tooltip();

      if (!presenceOnly && $('#grand-total').length) {
        table.on('draw', updateGrandTotal);
        updateGrandTotal();
      }

      $(document).on('click', '.see-more', function(e) {
        e.preventDefault();
        var $link = $(this);
        var targetClass = $link.data('target') ? '.' + $link.data('target') : '.more-intervals';
        var $container = $link.closest('td').find(targetClass);
        $container.toggleClass('d-none');
        $link.text($container.hasClass('d-none') ? '+' + $container.find('div').length + ' more' : 'Hide');
      });
    })();
  </script>

</body>

</html>