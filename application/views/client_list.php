<?php
$clientRecords = isset($data) ? $data : array();
if ($clientRecords instanceof Traversable) {
    $clientRecords = iterator_to_array($clientRecords, false);
}
$clientRecords = is_array($clientRecords) ? array_values($clientRecords) : array();

$isAdmin = strtolower(trim((string) $this->session->userdata('level'))) === 'admin';
$totalClients = count($clientRecords);
$activeCount = 0;
$inactiveCount = 0;
$prospectCount = 0;
$donationCount = 0;
$portalEnabledCount = 0;

foreach ($clientRecords as $clientSummaryRow) {
    $clientStat = trim((string) ($clientSummaryRow->ClientStat ?? ''));
    if (strcasecmp($clientStat, 'Active') === 0) {
        $activeCount++;
    } elseif (strcasecmp($clientStat, 'Inactive') === 0) {
        $inactiveCount++;
    } elseif (strcasecmp($clientStat, 'Prospect') === 0) {
        $prospectCount++;
    } elseif (strcasecmp($clientStat, 'Donation') === 0) {
        $donationCount++;
    }

    if (!empty($clientSummaryRow->portal_enabled)) {
        $portalEnabledCount++;
    }
}

?>
<!DOCTYPE html>
<html lang="en">

<?php include('includes/head.php'); ?>

<body>

    <div id="wrapper">

        <?php include('includes/top-nav-bar.php'); ?>
        <?php include('includes/sidebar.php'); ?>

        <div class="content-page">
            <div class="content">
                <div class="container-fluid client-list-page">
                    <style>
                        @import url('https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,100..1000;1,9..40,100..1000&display=swap');

                        .client-list-page {
                            --bg: #f5f7fb;
                            --surface: rgba(255, 255, 255, 0.96);
                            --surface-strong: #ffffff;
                            --surface-soft: #f8fbff;
                            --line: #e4ebf4;
                            --line-strong: #cfdbea;
                            --text: #142235;
                            --text-soft: #617489;
                            --text-faint: #8ea0b5;
                            --primary: #2563eb;
                            --primary-2: #1d4ed8;
                            --primary-soft: #eaf2ff;
                            --success: #059669;
                            --success-soft: #ecfdf5;
                            --warning: #d97706;
                            --warning-soft: #fff7ed;
                            --danger: #e11d48;
                            --danger-soft: #fff1f2;
                            --shadow: 0 16px 40px rgba(15, 23, 42, 0.06);
                            --shadow-soft: 0 8px 24px rgba(15, 23, 42, 0.04);
                            --radius-xl: 16px;
                            --radius-lg: 12px;
                            --radius-md: 10px;
                            --radius-sm: 8px;
                            --font-body: 'DM Sans', 'Segoe UI', Arial, sans-serif;
                            --font-head: 'DM Sans', 'Segoe UI', Arial, sans-serif;
                            --font-mono: 'DM Sans', 'SFMono-Regular', Consolas, monospace;
                            background:
                                radial-gradient(circle at top left, rgba(37, 99, 235, 0.08), transparent 28%),
                                radial-gradient(circle at top right, rgba(16, 185, 129, 0.08), transparent 24%),
                                linear-gradient(180deg, #f8fbff 0%, #f4f7fb 100%);
                            min-height: 100vh;
                            padding-bottom: 100px;
                            font-family: var(--font-body);
                        }

                        .client-list-page * {
                            box-sizing: border-box;
                        }

                        .client-list-page .content {
                            margin-bottom: 40px;
                        }

                        .client-list-page .alert {
                            border: none;
                            border-radius: 16px;
                            box-shadow: var(--shadow-soft);
                        }

                        .client-list-page .page-header {
                            display: flex;
                            justify-content: space-between;
                            align-items: flex-end;
                            gap: 16px;
                            margin: 16px 0 16px;
                            flex-wrap: wrap;
                        }

                        .client-list-page .page-eyebrow {
                            display: inline-flex;
                            align-items: center;
                            gap: 8px;
                            padding: 7px 12px;
                            border-radius: 999px;
                            background: rgba(37, 99, 235, 0.08);
                            color: var(--primary-2);
                            font-size: 0.74rem;
                            font-weight: 700;
                            letter-spacing: 0.08em;
                            text-transform: uppercase;
                            margin-bottom: 12px;
                        }

                        .client-list-page .page-eyebrow::before {
                            content: '';
                            width: 8px;
                            height: 8px;
                            border-radius: 50%;
                            background: linear-gradient(135deg, var(--primary), var(--primary-2));
                            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.10);
                        }

                        .client-list-page .page-title {
                            margin: 0;
                            font-family: var(--font-head);
                            font-size: 1.5rem;
                            line-height: 1.2;
                            letter-spacing: -0.02em;
                            font-weight: 700;
                            color: var(--text);
                        }

                        .client-list-page .page-subtitle {
                            margin-top: 6px;
                            color: var(--text-soft);
                            font-size: 0.9rem;
                            max-width: 760px;
                        }

                        .client-list-page .page-actions {
                            display: flex;
                            gap: 12px;
                            flex-wrap: wrap;
                        }

                        .client-list-page .btn-action,
                        .client-list-page .btn-submit {
                            display: inline-flex;
                            align-items: center;
                            justify-content: center;
                            gap: 8px;
                            border-radius: 12px;
                            font-size: 0.92rem;
                            font-weight: 700;
                            padding: 11px 18px;
                            transition: all 0.16s ease;
                            text-decoration: none;
                        }

                        .client-list-page .btn-action {
                            border: 1px solid var(--line-strong);
                            color: var(--text);
                            background: #fff;
                        }

                        .client-list-page .btn-action:hover {
                            color: var(--primary);
                            border-color: #bfd3ef;
                            background: #f9fbff;
                        }

                        .client-list-page .btn-submit {
                            border: none;
                            color: #fff;
                            background: linear-gradient(135deg, var(--primary), var(--primary-2));
                            box-shadow: 0 10px 24px rgba(37, 99, 235, 0.24);
                        }

                        .client-list-page .btn-submit:hover {
                            transform: translateY(-1px);
                            box-shadow: 0 14px 28px rgba(37, 99, 235, 0.28);
                            color: #fff;
                        }

                        .client-list-page .stats-grid {
                            display: grid;
                            grid-template-columns: repeat(4, minmax(0, 1fr));
                            gap: 12px;
                            margin-bottom: 16px;
                        }

                        .client-list-page .stat-card {
                            position: relative;
                            overflow: hidden;
                            background: var(--surface);
                            border: 1px solid rgba(255, 255, 255, 0.72);
                            border-radius: var(--radius-xl);
                            box-shadow: var(--shadow-soft);
                            padding: 14px 16px 14px;
                        }

                        .client-list-page .stat-card::before {
                            content: '';
                            position: absolute;
                            inset: 0 0 auto 0;
                            height: 4px;
                        }

                        .client-list-page .stat-total::before {
                            background: linear-gradient(90deg, #3b82f6, #60a5fa);
                        }

                        .client-list-page .stat-active::before {
                            background: linear-gradient(90deg, #10b981, #34d399);
                        }

                        .client-list-page .stat-prospect::before {
                            background: linear-gradient(90deg, #f59e0b, #fbbf24);
                        }

                        .client-list-page .stat-portal::before {
                            background: linear-gradient(90deg, #7c3aed, #a78bfa);
                        }

                        .client-list-page .stat-label {
                            color: var(--text-faint);
                            font-size: 0.65rem;
                            font-weight: 600;
                            text-transform: uppercase;
                            letter-spacing: 0.06em;
                            margin-bottom: 8px;
                        }

                        .client-list-page .stat-value {
                            color: var(--text);
                            font-size: 1.25rem;
                            font-weight: 700;
                            line-height: 1.2;
                            letter-spacing: -0.02em;
                            font-family: var(--font-head);
                        }

                        .client-list-page .stat-meta {
                            color: var(--text-soft);
                            font-size: 0.72rem;
                            margin-top: 4px;
                        }

                        .client-list-page .card-stack {
                            display: grid;
                            gap: 16px;
                        }

                        .client-list-page .theme-card {
                            background: var(--surface);
                            border: 1px solid rgba(255, 255, 255, 0.72);
                            border-radius: var(--radius-xl);
                            box-shadow: var(--shadow-soft);
                            overflow: hidden;
                        }

                        .client-list-page .theme-card-head {
                            padding: 14px 18px;
                            border-bottom: 1px solid var(--line);
                            background: linear-gradient(180deg, rgba(255, 255, 255, 0.98), rgba(249, 251, 255, 0.94));
                        }

                        .client-list-page .theme-card-title {
                            margin: 0;
                            color: var(--text);
                            font-size: 0.95rem;
                            font-weight: 700;
                            letter-spacing: -0.01em;
                        }

                        .client-list-page .theme-card-subtitle {
                            margin-top: 4px;
                            color: var(--text-soft);
                            font-size: 0.8rem;
                        }

                        .client-list-page .theme-card-body {
                            padding: 18px;
                        }

                        .client-list-page .filter-grid {
                            display: grid;
                            grid-template-columns: repeat(5, minmax(0, 1fr));
                            gap: 14px;
                        }

                        .client-list-page .filter-status-card {
                            position: relative;
                            width: 100%;
                            border: 1px solid var(--line);
                            border-radius: var(--radius-lg);
                            background: var(--surface-soft);
                            box-shadow: var(--shadow-soft);
                            padding: 16px 16px 15px;
                            text-align: left;
                            cursor: pointer;
                            transition: transform 0.18s ease, box-shadow 0.18s ease, border-color 0.18s ease;
                        }

                        .client-list-page .filter-status-card:hover {
                            transform: translateY(-2px);
                            box-shadow: 0 18px 28px rgba(15, 23, 42, 0.09);
                        }

                        .client-list-page .filter-status-card.is-active {
                            border-color: rgba(37, 99, 235, 0.32);
                            box-shadow: 0 16px 32px rgba(37, 99, 235, 0.12);
                            background: #fff;
                        }

                        .client-list-page .filter-status-card::before {
                            content: '';
                            position: absolute;
                            inset: 0 0 auto 0;
                            height: 4px;
                        }

                        .client-list-page .filter-all::before {
                            background: linear-gradient(90deg, #3b82f6, #60a5fa);
                        }

                        .client-list-page .filter-active::before {
                            background: linear-gradient(90deg, #10b981, #34d399);
                        }

                        .client-list-page .filter-prospect::before {
                            background: linear-gradient(90deg, #f59e0b, #fbbf24);
                        }

                        .client-list-page .filter-inactive::before {
                            background: linear-gradient(90deg, #ef4444, #fb7185);
                        }

                        .client-list-page .filter-donation::before {
                            background: linear-gradient(90deg, #8b5cf6, #c4b5fd);
                        }

                        .client-list-page .filter-card-label {
                            color: var(--text);
                            font-size: 0.9rem;
                            font-weight: 800;
                            margin-bottom: 10px;
                        }

                        .client-list-page .filter-card-count {
                            color: var(--text);
                            font-size: 1.85rem;
                            line-height: 1;
                            font-weight: 800;
                            letter-spacing: -0.04em;
                            font-family: var(--font-mono);
                        }

                        .client-list-page .filter-card-meta {
                            color: var(--text-soft);
                            font-size: 0.82rem;
                            margin-top: 8px;
                        }

                        .client-list-page .summary-row {
                            display: flex;
                            justify-content: space-between;
                            align-items: center;
                            gap: 12px;
                            flex-wrap: wrap;
                            margin-top: 16px;
                        }

                        .client-list-page .summary-chip {
                            display: inline-flex;
                            align-items: center;
                            gap: 8px;
                            padding: 8px 12px;
                            border-radius: 999px;
                            border: 1px solid #dbeafe;
                            background: var(--primary-soft);
                            color: var(--primary-2);
                            font-size: 0.8rem;
                            font-weight: 700;
                        }

                        .client-list-page .summary-note {
                            color: var(--text-soft);
                            font-size: 0.84rem;
                        }

                        .client-list-page label {
                            color: var(--text);
                            font-size: 0.82rem;
                            font-weight: 700;
                            letter-spacing: 0.02em;
                            margin-bottom: 8px;
                        }

                        .client-list-page .form-control,
                        .client-list-page .custom-select,
                        .client-modal .form-control,
                        .client-modal .custom-select {
                            border: 1px solid var(--line-strong);
                            border-radius: var(--radius-sm);
                            min-height: 46px;
                            color: var(--text);
                            box-shadow: none;
                        }

                        .client-list-page .form-control:focus,
                        .client-list-page .custom-select:focus,
                        .client-modal .form-control:focus,
                        .client-modal .custom-select:focus {
                            border-color: #9cc0f5;
                            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
                        }

                        .client-list-page .table-responsive {
                            overflow-x: auto;
                        }

                        .client-list-page .table {
                            margin-bottom: 0;
                        }

                        .client-list-page .table thead th {
                            border-top: none;
                            border-bottom: 1px solid var(--line);
                            color: var(--text-faint);
                            font-size: 0.72rem;
                            font-weight: 800;
                            letter-spacing: 0.08em;
                            text-transform: uppercase;
                            white-space: nowrap;
                        }

                        .client-list-page .table td {
                            vertical-align: middle;
                            border-color: var(--line);
                        }

                        .client-list-page .data-table-container {
                            position: relative;
                        }

                        .client-list-page .data-table-container.loading::after {
                            content: 'Loading clients...';
                            position: absolute;
                            inset: 0;
                            background: rgba(255, 255, 255, 0.85);
                            display: flex;
                            align-items: center;
                            justify-content: center;
                            font-size: 0.9rem;
                            color: var(--text-soft);
                            z-index: 1;
                        }

                        .client-list-page .data-table-container.loading::before {
                            content: '';
                            position: absolute;
                            top: 50%;
                            left: 50%;
                            width: 26px;
                            height: 26px;
                            margin: -40px 0 0 -13px;
                            border-radius: 50%;
                            border: 3px solid rgba(108, 117, 125, 0.3);
                            border-top-color: rgba(37, 99, 235, 0.8);
                            animation: client-spinner 0.7s linear infinite;
                            z-index: 2;
                        }

                        .client-list-page .data-table-container.ready::after,
                        .client-list-page .data-table-container.ready::before {
                            display: none;
                        }

                        .client-list-page .table-init-hidden {
                            opacity: 0;
                        }

                        .client-list-page .table-init-ready {
                            opacity: 1;
                            transition: opacity 0.2s ease;
                        }

                        .client-list-page .dataTables_wrapper .dataTables_filter input,
                        .client-list-page .dataTables_wrapper .dataTables_length select {
                            border: 1px solid var(--line-strong);
                            border-radius: 10px;
                            min-height: 38px;
                            padding: 6px 10px;
                            background: #fff;
                        }

                        .client-list-page .dataTables_wrapper .dataTables_paginate .paginate_button {
                            border-radius: 8px !important;
                        }

                        .client-list-page .client-name-link {
                            color: var(--primary-2);
                            font-weight: 700;
                            text-decoration: none;
                        }

                        .client-list-page .client-name-link:hover {
                            text-decoration: underline;
                        }

                        .client-list-page .external-link {
                            color: var(--info);
                            font-weight: 700;
                            text-decoration: none;
                        }

                        .client-list-page .external-link:hover {
                            text-decoration: underline;
                        }

                        .client-list-page .notes-preview {
                            max-width: 240px;
                            white-space: normal;
                            word-break: break-word;
                            color: var(--text-soft);
                        }

                        .client-list-page .client-actions {
                            display: inline-flex;
                            gap: 6px;
                            align-items: center;
                            justify-content: center;
                        }

                        .client-list-page .client-actions .action-btn {
                            position: relative;
                            display: inline-flex;
                            align-items: center;
                            justify-content: center;
                            width: 34px;
                            height: 34px;
                            border-radius: 50%;
                            transition: all 0.18s ease;
                            text-decoration: none;
                            font-size: 16px;
                            border: none;
                            cursor: pointer;
                        }

                        .client-list-page .client-actions .action-btn.view {
                            color: var(--info);
                            background: rgba(15, 118, 110, 0.12);
                        }

                        .client-list-page .client-actions .action-btn.view:hover {
                            background: rgba(15, 118, 110, 0.22);
                        }

                        .client-list-page .client-actions .action-btn.edit {
                            color: var(--primary-2);
                            background: rgba(37, 99, 235, 0.12);
                        }

                        .client-list-page .client-actions .action-btn.edit:hover {
                            background: rgba(37, 99, 235, 0.22);
                        }

                        .client-list-page .client-actions .action-btn.delete {
                            color: #dc2626;
                            background: rgba(220, 38, 38, 0.12);
                        }

                        .client-list-page .client-actions .action-btn.delete:hover {
                            background: rgba(220, 38, 38, 0.22);
                        }

                        .client-list-page .client-actions .action-btn::after {
                            content: attr(data-label);
                            position: absolute;
                            bottom: -32px;
                            left: 50%;
                            transform: translate(-50%, 6px);
                            background: rgba(15, 23, 42, 0.92);
                            color: #fff;
                            padding: 4px 8px;
                            border-radius: 4px;
                            font-size: 11px;
                            white-space: nowrap;
                            opacity: 0;
                            pointer-events: none;
                            transition: all 0.15s ease;
                        }

                        .client-list-page .client-actions .action-btn:hover::after {
                            opacity: 1;
                            transform: translate(-50%, 0);
                        }

                        .client-list-page .status-badge {
                            display: inline-flex;
                            align-items: center;
                            gap: 6px;
                            padding: 6px 12px;
                            border-radius: 999px;
                            font-size: 12px;
                            font-weight: 700;
                        }

                        .client-list-page .status-active {
                            background: var(--success-soft);
                            color: var(--success);
                        }

                        .client-list-page .status-inactive {
                            background: var(--danger-soft);
                            color: var(--danger);
                        }

                        .client-list-page .status-prospect {
                            background: var(--warning-soft);
                            color: var(--warning);
                        }

                        .client-list-page .status-donation {
                            background: var(--purple-soft);
                            color: var(--purple);
                        }

                        .client-list-page .inline-delete-form {
                            display: inline-block;
                            margin: 0;
                        }

                        .client-modal .modal-content {
                            border: none;
                            border-radius: 20px;
                            overflow: hidden;
                            box-shadow: 0 24px 60px rgba(15, 23, 42, 0.16);
                        }

                        .client-modal .modal-header {
                            background: linear-gradient(135deg, var(--primary), var(--primary-2));
                            color: #fff;
                            border-bottom: none;
                            padding: 18px 22px;
                        }

                        .client-modal .modal-header .close {
                            color: #fff;
                            opacity: 0.85;
                        }

                        .client-modal .modal-body {
                            padding: 22px;
                        }

                        .client-modal .modal-footer {
                            border-top: 1px solid var(--line);
                            padding: 16px 22px;
                        }

                        .client-modal .btn {
                            border-radius: 10px;
                            font-weight: 700;
                            padding: 10px 16px;
                        }

                        .client-modal .btn-primary,
                        .client-modal .btn-info {
                            border: none;
                            color: #fff;
                            background: linear-gradient(135deg, var(--primary), var(--primary-2));
                            box-shadow: 0 10px 24px rgba(37, 99, 235, 0.18);
                        }

                        .client-modal .btn-secondary {
                            border: 1px solid var(--line-strong);
                            background: #fff;
                            color: var(--text);
                        }

                        .client-modal .btn-warning {
                            border: none;
                            background: linear-gradient(135deg, #f59e0b, #f97316);
                            color: #fff;
                        }

                        @keyframes client-spinner {
                            to {
                                transform: rotate(360deg);
                            }
                        }

                        @media (max-width: 1199px) {
                            .client-list-page .stats-grid {
                                grid-template-columns: repeat(2, minmax(0, 1fr));
                            }

                            .client-list-page .filter-grid {
                                grid-template-columns: repeat(3, minmax(0, 1fr));
                            }
                        }

                        @media (max-width: 767px) {
                            .client-list-page .page-title {
                                font-size: 1.75rem;
                            }

                            .client-list-page .stats-grid,
                            .client-list-page .filter-grid {
                                grid-template-columns: 1fr;
                            }

                            .client-list-page .theme-card-head,
                            .client-list-page .theme-card-body,
                            .client-modal .modal-body,
                            .client-modal .modal-footer {
                                padding-left: 16px;
                                padding-right: 16px;
                            }
                        }
                    </style>

                    <?php if ($this->session->flashdata('success')): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <?= htmlspecialchars((string) $this->session->flashdata('success'), ENT_QUOTES, 'UTF-8'); ?>
                            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                        </div>
                    <?php endif; ?>

                    <?php if ($this->session->flashdata('danger')): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?= htmlspecialchars((string) $this->session->flashdata('danger'), ENT_QUOTES, 'UTF-8'); ?>
                            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                        </div>
                    <?php endif; ?>

                    <div class="page-header">
                        <div>
                            <div class="page-eyebrow">Clients</div>
                            <h4 class="page-title">Client Directory</h4>
                            <!-- <div class="page-subtitle">Review company profiles, portal access, lead sources, and customer notes from the same BERPS workspace style used across the newer billing pages.</div> -->
                        </div>
                        <div class="page-actions">
                            <a href="<?= base_url(); ?>Page/clientEntry" class="btn-submit">
                                <i class="mdi mdi-account-plus-outline"></i>
                                Add Client
                            </a>
                        </div>
                    </div>

                    <div class="stats-grid">
                        <div class="stat-card stat-total">
                            <div class="stat-label">Total Clients</div>
                            <div class="stat-value"><?= number_format($totalClients); ?></div>
                            <div class="stat-meta">All client profiles currently stored in BERPS.</div>
                        </div>
                        <div class="stat-card stat-active">
                            <div class="stat-label">Active Accounts</div>
                            <div class="stat-value"><?= number_format($activeCount); ?></div>
                            <div class="stat-meta">Companies with an active service relationship.</div>
                        </div>
                        <div class="stat-card stat-prospect">
                            <div class="stat-label">Prospects</div>
                            <div class="stat-value"><?= number_format($prospectCount); ?></div>
                            <div class="stat-meta">Leads that are still under follow-up.</div>
                        </div>
                        <div class="stat-card stat-portal">
                            <div class="stat-label">Portal Enabled</div>
                            <div class="stat-value"><?= number_format($portalEnabledCount); ?></div>
                            <div class="stat-meta">Client profiles with portal access switched on.</div>
                        </div>
                    </div>

                    <div class="card-stack">
                        <div class="theme-card">
                            <div class="theme-card-head">
                                <h5 class="theme-card-title">Quick Filters</h5>
                                <!-- <div class="theme-card-subtitle">Use the status cards below to narrow the directory without leaving the page.</div> -->
                            </div>
                            <div class="theme-card-body">
                                <div class="filter-grid">
                                    <button type="button" class="filter-status-card filter-all is-active" data-status="">
                                        <div class="filter-card-label">All Clients</div>
                                        <div class="filter-card-count"><?= number_format($totalClients); ?></div>
                                        <div class="filter-card-meta">Show every client record</div>
                                    </button>
                                    <button type="button" class="filter-status-card filter-active" data-status="active">
                                        <div class="filter-card-label">Active</div>
                                        <div class="filter-card-count"><?= number_format($activeCount); ?></div>
                                        <div class="filter-card-meta">Focus on active service accounts</div>
                                    </button>
                                    <button type="button" class="filter-status-card filter-prospect" data-status="prospect">
                                        <div class="filter-card-label">Prospect</div>
                                        <div class="filter-card-count"><?= number_format($prospectCount); ?></div>
                                        <div class="filter-card-meta">Show leads still in progress</div>
                                    </button>
                                    <button type="button" class="filter-status-card filter-inactive" data-status="inactive">
                                        <div class="filter-card-label">Inactive</div>
                                        <div class="filter-card-count"><?= number_format($inactiveCount); ?></div>
                                        <div class="filter-card-meta">View paused or inactive accounts</div>
                                    </button>
                                    <button type="button" class="filter-status-card filter-donation" data-status="donation">
                                        <div class="filter-card-label">Donation</div>
                                        <div class="filter-card-count"><?= number_format($donationCount); ?></div>
                                        <div class="filter-card-meta">Show donation-based client records</div>
                                    </button>
                                </div>

                                <div class="summary-row">
                                    <div id="summaryFilterStatus" class="summary-chip">
                                        <i class="mdi mdi-filter-variant"></i>
                                        Showing: All Clients
                                    </div>
                                    <div class="summary-note"><?= $isAdmin ? 'Admins can update and archive client records from the table below.' : 'You can review client records here and add new profiles using the action above.'; ?></div>
                                </div>
                            </div>
                        </div>

                        <div class="theme-card">
                            <div class="theme-card-head">
                                <h5 class="theme-card-title">Client Records</h5>
                                <!-- <div class="theme-card-subtitle">Review company contact details, source tracking, portal access, and notes in one table.</div> -->
                            </div>
                            <div class="theme-card-body">
                                <div class="table-responsive data-table-container loading" id="client-table-container">
                                    <table id="client-table" class="table table-init-hidden">
                                        <thead class="thead-light">
                                            <tr>
                                                <th>Client ID</th>
                                                <th>Client</th>
                                                <th>Address</th>
                                                <?php if ($isAdmin): ?>
                                                    <th>Contact Person</th>
                                                    <th>Contact No.</th>
                                                    <th>Company Email</th>
                                                    <th>Client Source</th>
                                                    <th>Sales Agent</th>
                                                    <th>Facebook Link</th>
                                                    <th>Client Email</th>
                                                    <th>Portal Access</th>
                                                    <th>Notes</th>
                                                    <th>Status</th>
                                                    <th class="text-center">Actions</th>
                                                <?php endif; ?>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (!empty($clientRecords)): ?>
                                                <?php foreach ($clientRecords as $row): ?>
                                                    <?php
                                                    $clientId      = isset($row->CustID) ? (string)$row->CustID : '';
                                                    $clientName    = isset($row->Customer) ? (string)$row->Customer : '';
                                                    $address       = isset($row->Address) ? (string)$row->Address : '';
                                                    $contactPerson = isset($row->ContactPerson) ? (string)$row->ContactPerson : '';
                                                    $contactNumber = isset($row->ContactNos) ? (string)$row->ContactNos : '';
                                                    $companyEmail  = isset($row->CompanyEmail) ? (string)$row->CompanyEmail : '';
                                                    $clientStat    = isset($row->ClientStat) ? (string)$row->ClientStat : '';

                                                    $clientSource  = isset($row->client_source) ? (string)$row->client_source : '';
                                                    $salesAgent    = isset($row->sales_agent) ? (string)$row->sales_agent : '';
                                                    $facebookLink  = isset($row->facebook_link) ? (string)$row->facebook_link : '';
                                                    $clientEmail2  = isset($row->client_email) ? (string)$row->client_email : '';
                                                    $portalEnabled = !empty($row->portal_enabled);
                                                    $notes         = isset($row->notes) ? (string)$row->notes : '';
                                                    $clientProfileParams = array();
                                                    if ($clientId !== '') {
                                                        $clientProfileParams['cust_id'] = $clientId;
                                                    } elseif ($clientName !== '') {
                                                        $clientProfileParams['customer'] = $clientName;
                                                    }
                                                    $clientProfileUrl = base_url() . 'Page/clientProfile';
                                                    if (!empty($clientProfileParams)) {
                                                        $clientProfileUrl .= '?' . http_build_query($clientProfileParams);
                                                    }

                                                    $statusClass = 'status-prospect';
                                                    if (strcasecmp($clientStat, 'Active') === 0) {
                                                        $statusClass = 'status-active';
                                                    } elseif (strcasecmp($clientStat, 'Inactive') === 0) {
                                                        $statusClass = 'status-inactive';
                                                    } elseif (strcasecmp($clientStat, 'Donation') === 0) {
                                                        $statusClass = 'status-donation';
                                                    }
                                                    ?>
                                                    <tr data-client-status="<?= htmlspecialchars(strtolower(trim($clientStat)), ENT_QUOTES, 'UTF-8'); ?>">
                                                        <td class="font-weight-semibold"><?= htmlspecialchars($clientId, ENT_QUOTES, 'UTF-8'); ?></td>
                                                        <td>
                                                            <?php if ($clientName !== '' && !empty($clientProfileParams) && $isAdmin): ?>
                                                                <a class="client-name-link" href="<?= htmlspecialchars($clientProfileUrl, ENT_QUOTES, 'UTF-8'); ?>">
                                                                    <?= htmlspecialchars($clientName, ENT_QUOTES, 'UTF-8'); ?>
                                                                </a>
                                                            <?php elseif ($clientName !== ''): ?>
                                                                <?= htmlspecialchars($clientName, ENT_QUOTES, 'UTF-8'); ?>
                                                            <?php else: ?>
                                                                -
                                                            <?php endif; ?>
                                                        </td>
                                                        <td><?= $address !== '' ? htmlspecialchars($address, ENT_QUOTES, 'UTF-8') : '-'; ?></td>
                                                        <?php if ($isAdmin): ?>
                                                            <td><?= $contactPerson !== '' ? htmlspecialchars($contactPerson, ENT_QUOTES, 'UTF-8') : '-'; ?></td>
                                                            <td><?= $contactNumber !== '' ? htmlspecialchars($contactNumber, ENT_QUOTES, 'UTF-8') : '-'; ?></td>
                                                            <td><?= $companyEmail !== '' ? htmlspecialchars($companyEmail, ENT_QUOTES, 'UTF-8') : '-'; ?></td>
                                                            <td><?= $clientSource !== '' ? htmlspecialchars($clientSource, ENT_QUOTES, 'UTF-8') : '-'; ?></td>
                                                            <td><?= $salesAgent !== '' ? htmlspecialchars($salesAgent, ENT_QUOTES, 'UTF-8') : '-'; ?></td>
                                                            <td>
                                                                <?php if ($facebookLink !== ''): ?>
                                                                    <a class="external-link" href="<?= htmlspecialchars($facebookLink, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener noreferrer">
                                                                        View Link
                                                                    </a>
                                                                <?php else: ?>
                                                                    -
                                                                <?php endif; ?>
                                                            </td>
                                                            <td><?= $clientEmail2 !== '' ? htmlspecialchars($clientEmail2, ENT_QUOTES, 'UTF-8') : '-'; ?></td>
                                                            <td data-order="<?= $portalEnabled ? 'Enabled' : 'Disabled'; ?>">
                                                                <span class="status-badge <?= $portalEnabled ? 'status-active' : 'status-inactive'; ?>">
                                                                    <?= $portalEnabled ? 'Enabled' : 'Disabled'; ?>
                                                                </span>
                                                            </td>
                                                            <td class="notes-preview"><?= $notes !== '' ? nl2br(htmlspecialchars($notes, ENT_QUOTES, 'UTF-8')) : '-'; ?></td>
                                                            <td data-order="<?= htmlspecialchars($clientStat, ENT_QUOTES, 'UTF-8'); ?>">
                                                                <?php if ($clientStat !== ''): ?>
                                                                    <span class="status-badge <?= $statusClass; ?>">
                                                                        <?= htmlspecialchars($clientStat, ENT_QUOTES, 'UTF-8'); ?>
                                                                    </span>
                                                                <?php else: ?>
                                                                    <span class="text-muted">-</span>
                                                                <?php endif; ?>
                                                            </td>
                                                            <td class="text-center">
                                                                <div class="client-actions">
                                                                    <?php if (!empty($clientProfileParams)): ?>
                                                                        <a href="<?= htmlspecialchars($clientProfileUrl, ENT_QUOTES, 'UTF-8'); ?>" class="action-btn view" data-label="View Company" title="View Company">
                                                                            <i class="mdi mdi-eye-outline"></i>
                                                                        </a>
                                                                    <?php endif; ?>

                                                                    <?php if ($isAdmin): ?>
                                                                        <button
                                                                            type="button"
                                                                            class="action-btn edit"
                                                                            data-label="Edit"
                                                                            title="Edit Client"
                                                                            data-toggle="modal"
                                                                            data-target="#editClientModal<?= htmlspecialchars($clientId, ENT_QUOTES, 'UTF-8'); ?>">
                                                                            <i class="mdi mdi-square-edit-outline"></i>
                                                                        </button>

                                                                        <form method="post" action="" class="inline-delete-form" onsubmit="return confirm('Are you sure you want to delete this client?');">
                                                                            <input type="hidden" name="CustID" value="<?= htmlspecialchars($clientId, ENT_QUOTES, 'UTF-8'); ?>">
                                                                            <button type="submit" name="deleteclient" value="1" class="action-btn delete" data-label="Delete" title="Delete Client">
                                                                                <i class="mdi mdi-trash-can-outline"></i>
                                                                            </button>
                                                                        </form>
                                                                    <?php endif; ?>
                                                                </div>
                                                            </td>
                                                        <?php endif; ?>
                                                    </tr>

                                                    <?php if ($isAdmin): ?>
                                                        <div class="modal fade client-modal" id="editClientModal<?= htmlspecialchars($clientId, ENT_QUOTES, 'UTF-8'); ?>" tabindex="-1" role="dialog" aria-hidden="true">
                                                            <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
                                                                <div class="modal-content">
                                                                    <div class="modal-header border-0 bg-info text-white">
                                                                        <h4 class="modal-title mb-0">Update Client</h4>
                                                                        <button type="button" class="close text-white" data-dismiss="modal">
                                                                            <span>&times;</span>
                                                                        </button>
                                                                    </div>

                                                                    <form method="post" action="">
                                                                        <div class="modal-body">
                                                                            <div class="form-row">
                                                                                <div class="form-group col-md-4">
                                                                                    <label>Client ID</label>
                                                                                    <input type="text" class="form-control" name="CustID" value="<?= htmlspecialchars($clientId, ENT_QUOTES, 'UTF-8'); ?>" readonly>
                                                                                </div>
                                                                                <div class="form-group col-md-8">
                                                                                    <label>Client</label>
                                                                                    <input type="text" class="form-control" name="Customer" value="<?= htmlspecialchars($clientName, ENT_QUOTES, 'UTF-8'); ?>" required>
                                                                                </div>
                                                                            </div>

                                                                            <div class="form-group">
                                                                                <label>Address</label>
                                                                                <input type="text" class="form-control" name="Address" value="<?= htmlspecialchars($address, ENT_QUOTES, 'UTF-8'); ?>" required>
                                                                            </div>

                                                                            <div class="form-row">
                                                                                <div class="form-group col-md-4">
                                                                                    <label>Contact Person</label>
                                                                                    <input type="text" class="form-control" name="ContactPerson" value="<?= htmlspecialchars($contactPerson, ENT_QUOTES, 'UTF-8'); ?>">
                                                                                </div>
                                                                                <div class="form-group col-md-4">
                                                                                    <label>Contact Nos.</label>
                                                                                    <input type="text" class="form-control" name="Contact" value="<?= htmlspecialchars($contactNumber, ENT_QUOTES, 'UTF-8'); ?>">
                                                                                </div>
                                                                                <div class="form-group col-md-4">
                                                                                    <label>Company E-mail</label>
                                                                                    <input type="email" class="form-control" name="CompanyEmail" value="<?= htmlspecialchars($companyEmail, ENT_QUOTES, 'UTF-8'); ?>">
                                                                                </div>
                                                                            </div>

                                                                            <div class="form-row">
                                                                                <div class="form-group col-md-6">
                                                                                    <label>Client Source</label>
                                                                                    <select class="form-control" name="client_source">
                                                                                        <option value="">Select Source</option>
                                                                                        <option value="Facebook Ads" <?= strcasecmp(trim((string)$clientSource), 'Facebook Ads') === 0 ? 'selected' : ''; ?>>Facebook Ads</option>
                                                                                        <option value="E-mail Marketing" <?= strcasecmp(trim((string)$clientSource), 'E-mail Marketing') === 0 ? 'selected' : ''; ?>>E-mail Marketing</option>
                                                                                        <option value="Referral" <?= strcasecmp(trim((string)$clientSource), 'Referral') === 0 ? 'selected' : ''; ?>>Referral</option>
                                                                                        <option value="Others" <?= strcasecmp(trim((string)$clientSource), 'Others') === 0 ? 'selected' : ''; ?>>Others</option>
                                                                                    </select>
                                                                                </div>
                                                                                <div class="form-group col-md-6">
                                                                                    <label>Sales Agent</label>
                                                                                    <input type="text" class="form-control" name="sales_agent" value="<?= htmlspecialchars($salesAgent, ENT_QUOTES, 'UTF-8'); ?>">
                                                                                </div>
                                                                            </div>

                                                                            <div class="form-row">
                                                                                <div class="form-group col-md-6">
                                                                                    <label>Facebook Link</label>
                                                                                    <input type="text" class="form-control" name="facebook_link" value="<?= htmlspecialchars($facebookLink, ENT_QUOTES, 'UTF-8'); ?>">
                                                                                </div>
                                                                                <div class="form-group col-md-6">
                                                                                    <label>Client Email</label>
                                                                                    <input type="email" class="form-control" name="client_email" value="<?= htmlspecialchars($clientEmail2, ENT_QUOTES, 'UTF-8'); ?>">
                                                                                </div>
                                                                            </div>

                                                                            <div class="form-row">
                                                                                <div class="form-group col-md-6">
                                                                                    <label>Status</label>
                                                                                    <select class="form-control" name="ClientStat" required>
                                                                                        <option value="Active" <?= strcasecmp($clientStat, 'Active') === 0 ? 'selected' : ''; ?>>Active</option>
                                                                                        <option value="Inactive" <?= strcasecmp($clientStat, 'Inactive') === 0 ? 'selected' : ''; ?>>Inactive</option>
                                                                                        <option value="Prospect" <?= strcasecmp($clientStat, 'Prospect') === 0 ? 'selected' : ''; ?>>Prospect</option>
                                                                                        <option value="Donation" <?= strcasecmp($clientStat, 'Donation') === 0 ? 'selected' : ''; ?>>Donation</option>
                                                                                    </select>
                                                                                </div>
                                                                                <div class="form-group col-md-6">
                                                                                    <label>Portal Access</label>
                                                                                    <select class="form-control" name="portal_enabled">
                                                                                        <option value="0" <?= !$portalEnabled ? 'selected' : ''; ?>>Disabled</option>
                                                                                        <option value="1" <?= $portalEnabled ? 'selected' : ''; ?>>Enabled</option>
                                                                                    </select>
                                                                                </div>
                                                                                <div class="form-group col-md-6">
                                                                                    <label>Invoice Access</label>
                                                                                    <select class="form-control" name="invoice_access_enabled">
                                                                                        <option value="0" <?= !($row->invoice_access_enabled ?? 0) ? 'selected' : ''; ?>>Disabled</option>
                                                                                        <option value="1" <?= ($row->invoice_access_enabled ?? 0) ? 'selected' : ''; ?>>Enabled</option>
                                                                                    </select>
                                                                                    <small class="form-text text-muted">Enable/disable invoice features for client portal access.</small>
                                                                                </div>
                                                                            </div>

                                                                            <div class="form-row">
                                                                                <div class="form-group col-md-6">
                                                                                    <label>Portal Password</label>
                                                                                    <input type="password" class="form-control" name="portal_password" placeholder="Leave blank to keep the current password">
                                                                                    <small class="form-text text-muted">Clients sign in using their Client Email and this portal password.</small>
                                                                                </div>
                                                                                <div class="form-group col-md-6">
                                                                                    <label>Date Added</label>
                                                                                    <input type="date" class="form-control" name="created_at" value="<?= isset($row->created_at) ? date('Y-m-d', strtotime($row->created_at)) : date('Y-m-d'); ?>">
                                                                                </div>
                                                                            </div>

                                                                            <div class="form-group">
                                                                                <label>Notes</label>
                                                                                <textarea class="form-control" name="notes" rows="4"><?= htmlspecialchars($notes, ENT_QUOTES, 'UTF-8'); ?></textarea>
                                                                            </div>
                                                                        </div>
                                                                        <div class="modal-footer">
                                                                            <button type="submit" name="updateclient" value="1" class="btn btn-info">Update Client</button>
                                                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                                                        </div>
                                                                    </form>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    <?php endif; ?>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <tr>
                                                    <td colspan="14" class="text-center text-muted py-4">No clients recorded yet.</td>
                                                </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                <?php include('includes/footer.php'); ?>
            </div>
        </div>

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
        <script src="<?= base_url(); ?>assets/libs/datatables/jquery.dataTables.min.js"></script>
        <script src="<?= base_url(); ?>assets/libs/datatables/dataTables.bootstrap4.min.js"></script>
        <script src="<?= base_url(); ?>assets/libs/datatables/dataTables.buttons.min.js"></script>
        <script src="<?= base_url(); ?>assets/libs/datatables/buttons.bootstrap4.min.js"></script>
        <script src="<?= base_url(); ?>assets/libs/jszip/jszip.min.js"></script>
        <script src="<?= base_url(); ?>assets/libs/pdfmake/pdfmake.min.js"></script>
        <script src="<?= base_url(); ?>assets/libs/pdfmake/vfs_fonts.js"></script>
        <script src="<?= base_url(); ?>assets/libs/datatables/buttons.html5.min.js"></script>
        <script src="<?= base_url(); ?>assets/libs/datatables/buttons.print.min.js"></script>
        <script src="<?= base_url(); ?>assets/libs/datatables/dataTables.responsive.min.js"></script>
        <script src="<?= base_url(); ?>assets/libs/datatables/responsive.bootstrap4.min.js"></script>
        <script src="<?= base_url(); ?>assets/libs/datatables/dataTables.keyTable.min.js"></script>
        <script src="<?= base_url(); ?>assets/libs/datatables/dataTables.select.min.js"></script>

        <script>
            (function($) {
                'use strict';

                $(function() {
                    var clientTable = null;
                    var clientTableElement = $('#client-table');
                    var clientTableContainer = $('#client-table-container');
                    var clientTableNode = clientTableElement.get(0) || null;
                    var selectedStatus = '';

                    if (clientTableElement.length) {
                        clientTable = clientTableElement.DataTable({
                            responsive: true,
                            autoWidth: false,
                            order: [
                                [1, 'asc']
                            ],
                            language: {
                                emptyTable: 'No clients recorded yet.'
                            },
                            columnDefs: [{
                                targets: -1,
                                orderable: false,
                                searchable: false
                            }],
                            initComplete: function() {
                                clientTableContainer.removeClass('loading').addClass('ready');
                                clientTableElement.removeClass('table-init-hidden').addClass('table-init-ready');
                            }
                        });
                    }

                    $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
                        if (!clientTableNode || settings.nTable !== clientTableNode) return true;
                        if (!clientTable) return true;
                        if (!selectedStatus) return true;

                        var rowNode = settings.aoData[dataIndex] ? settings.aoData[dataIndex].nTr : null;
                        if (!rowNode) return true;

                        var rowStatus = ($(rowNode).attr('data-client-status') || '').toLowerCase();
                        return rowStatus === selectedStatus.toLowerCase();
                    });

                    $('.filter-status-card').on('click', function() {
                        if (!clientTable) return;

                        var status = $(this).data('status') || '';
                        var label = status !== '' ? (status.charAt(0).toUpperCase() + status.slice(1)) : 'All Clients';

                        selectedStatus = status;

                        $('.filter-status-card').removeClass('is-active');
                        $(this).addClass('is-active');

                        clientTable.draw();
                        $('#summaryFilterStatus').html('<i class="mdi mdi-filter-variant"></i> Showing: ' + label);
                    });
                });
            })(jQuery);
        </script>

    </div>
</body>

</html>