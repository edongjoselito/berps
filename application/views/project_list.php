<!DOCTYPE html>
<html lang="en">

<?php include('includes/head.php'); ?>

<body>

    <div id="wrapper">

        <?php include('includes/top-nav-bar.php'); ?>
        <?php include('includes/sidebar.php'); ?>

        <div class="content-page">
            <div class="content">
                <div class="container-fluid project-list-page">

                    <style>
                        @import url('https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,100..1000;1,9..40,100..1000&display=swap');

                        .project-list-page {
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

                        .project-list-page * {
                            box-sizing: border-box;
                        }

                        .project-list-page .content {
                            margin-bottom: 40px;
                        }

                        .project-list-page .alert {
                            border: none;
                            border-radius: var(--radius-lg);
                            font-size: 0.9rem;
                        }

                        .project-list-page .page-header {
                            display: flex;
                            justify-content: space-between;
                            align-items: flex-end;
                            gap: 16px;
                            margin: 16px 0 16px;
                            flex-wrap: wrap;
                        }

                        .project-list-page .page-eyebrow {
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

                        .project-list-page .page-eyebrow::before {
                            content: '';
                            width: 8px;
                            height: 8px;
                            border-radius: 50%;
                            background: linear-gradient(135deg, var(--primary), var(--primary-2));
                            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.10);
                        }

                        .project-list-page .page-title {
                            margin: 0;
                            font-family: var(--font-head);
                            font-size: 1.5rem;
                            line-height: 1.2;
                            letter-spacing: -0.02em;
                            font-weight: 700;
                            color: var(--text);
                        }

                        .project-list-page .page-subtitle {
                            margin-top: 6px;
                            color: var(--text-soft);
                            font-size: 0.9rem;
                            max-width: 760px;
                        }

                        .project-list-page .page-actions {
                            display: flex;
                            gap: 12px;
                            flex-wrap: wrap;
                        }

                        .project-list-page .btn-action,
                        .project-list-page .btn-submit {
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

                        .project-list-page .btn-action {
                            border: 1px solid var(--line-strong);
                            color: var(--text);
                            background: #fff;
                        }

                        .project-list-page .btn-action:hover {
                            color: var(--primary);
                            border-color: #bfd3ef;
                            background: #f9fbff;
                        }

                        .project-list-page .btn-submit {
                            border: none;
                            color: #fff;
                            background: linear-gradient(135deg, var(--primary), var(--primary-2));
                            box-shadow: 0 10px 24px rgba(37, 99, 235, 0.24);
                        }

                        .project-list-page .btn-submit:hover {
                            transform: translateY(-1px);
                            box-shadow: 0 14px 28px rgba(37, 99, 235, 0.28);
                        }

                        .project-list-page .card-stack {
                            display: grid;
                            gap: 16px;
                        }

                        .project-list-page .theme-card {
                            background: var(--surface);
                            border: 1px solid rgba(255, 255, 255, 0.72);
                            border-radius: var(--radius-xl);
                            box-shadow: var(--shadow-soft);
                            overflow: hidden;
                        }

                        .project-list-page .theme-card-head {
                            display: flex;
                            align-items: center;
                            justify-content: space-between;
                            gap: 12px;
                            padding: 14px 18px;
                            border-bottom: 1px solid var(--line);
                            background: linear-gradient(180deg, rgba(255, 255, 255, 0.98), rgba(249, 251, 255, 0.94));
                            flex-wrap: wrap;
                        }

                        .project-list-page .theme-card-title {
                            margin: 0;
                            color: var(--text);
                            font-size: 0.95rem;
                            font-weight: 700;
                            letter-spacing: -0.01em;
                        }

                        .project-list-page .theme-card-subtitle {
                            margin-top: 4px;
                            color: var(--text-soft);
                            font-size: 0.8rem;
                        }

                        .project-list-page .theme-card-body {
                            padding: 18px;
                        }

                        .project-list-page .count-pill {
                            display: inline-flex;
                            align-items: center;
                            gap: 6px;
                            padding: 6px 12px;
                            border-radius: 999px;
                            background: var(--primary-soft);
                            border: 1px solid #dbeafe;
                            color: var(--primary-2);
                            font-size: 0.78rem;
                            font-weight: 700;
                        }

                        /* ─── Table ─────────────────────────────────────────────── */
                        .project-list-page .project-table {
                            width: 100%;
                            border-collapse: collapse;
                        }

                        .project-list-page .project-table thead th {
                            border-top: none;
                            border-bottom: 1px solid var(--line);
                            color: var(--text-faint);
                            font-size: 0.72rem;
                            font-weight: 800;
                            letter-spacing: 0.08em;
                            text-transform: uppercase;
                            white-space: nowrap;
                            padding: 10px 14px;
                            background: transparent;
                        }

                        .project-list-page .project-table td {
                            padding: 11px 14px;
                            border-bottom: 1px solid var(--line);
                            vertical-align: middle;
                            font-size: 0.9rem;
                            color: var(--text);
                        }

                        .project-list-page .project-table tbody tr:last-child td {
                            border-bottom: none;
                        }

                        .project-list-page .project-table tbody tr:hover td {
                            background: var(--surface-soft);
                        }

                        .project-list-page .project-name {
                            font-weight: 700;
                            color: var(--text);
                            font-size: 0.9rem;
                        }

                        .project-list-page .cell-muted {
                            color: var(--text-soft);
                            font-size: 0.82rem;
                        }

                        .project-list-page .cost-value {
                            font-family: var(--font-mono);
                            font-weight: 700;
                            color: var(--primary-2);
                            font-size: 0.9rem;
                        }

                        /* ─── Category tag ──────────────────────────────────────── */
                        .project-list-page .category-tag {
                            display: inline-flex;
                            align-items: center;
                            padding: 4px 10px;
                            border-radius: 999px;
                            font-size: 0.75rem;
                            font-weight: 600;
                            background: var(--surface-soft);
                            border: 1px solid var(--line-strong);
                            color: var(--text-soft);
                            white-space: nowrap;
                        }

                        /* ─── Action icons ──────────────────────────────────────── */
                        .project-list-page .project-actions {
                            display: inline-flex;
                            align-items: center;
                            gap: 4px;
                        }

                        .project-list-page .action-icon {
                            width: 32px;
                            height: 32px;
                            border-radius: var(--radius-sm);
                            display: inline-flex;
                            align-items: center;
                            justify-content: center;
                            font-size: 16px;
                            text-decoration: none;
                            transition: all 0.16s ease;
                            color: var(--text-soft);
                            background: transparent;
                        }

                        .project-list-page .action-icon:hover {
                            transform: translateY(-1px);
                        }

                        .project-list-page .action-icon.tasks {
                            color: var(--success);
                        }

                        .project-list-page .action-icon.tasks:hover {
                            background: var(--success-soft);
                        }

                        .project-list-page .action-icon.deployment {
                            color: #0891b2;
                        }

                        .project-list-page .action-icon.deployment:hover {
                            background: #ecfeff;
                        }

                        .project-list-page .action-icon.edit {
                            color: var(--primary);
                        }

                        .project-list-page .action-icon.edit:hover {
                            background: var(--primary-soft);
                        }

                        .project-list-page .action-icon.delete {
                            color: var(--danger);
                        }

                        .project-list-page .action-icon.delete:hover {
                            background: var(--danger-soft);
                        }

                        /* ─── Year filter cards ─────────────────────────────────── */
                        .project-list-page .year-grid {
                            display: grid;
                            grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
                            gap: 12px;
                        }

                        .project-list-page .year-card {
                            background: var(--surface);
                            border: 1px solid var(--line);
                            border-radius: var(--radius-lg);
                            padding: 14px 16px;
                            cursor: pointer;
                            transition: all 0.16s ease;
                            display: flex;
                            align-items: center;
                            gap: 12px;
                        }

                        .project-list-page .year-card:hover {
                            border-color: #bfd3ef;
                            background: var(--primary-soft);
                        }

                        .project-list-page .year-card.is-active {
                            border-color: #bfd3ef;
                            background: var(--primary-soft);
                            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
                        }

                        .project-list-page .year-card-icon {
                            width: 32px;
                            height: 32px;
                            border-radius: var(--radius-sm);
                            background: var(--primary-soft);
                            border: 1px solid #dbeafe;
                            display: flex;
                            align-items: center;
                            justify-content: center;
                            font-size: 0.9rem;
                            color: var(--primary);
                            flex-shrink: 0;
                        }

                        .project-list-page .year-card.is-active .year-card-icon {
                            background: var(--primary);
                            color: #fff;
                            border-color: var(--primary);
                        }

                        .project-list-page .year-card-body {
                            min-width: 0;
                        }

                        .project-list-page .year-card-label {
                            font-size: 0.65rem;
                            font-weight: 700;
                            color: var(--text-faint);
                            text-transform: uppercase;
                            letter-spacing: 0.06em;
                        }

                        .project-list-page .year-card-count {
                            font-size: 1.25rem;
                            font-weight: 700;
                            color: var(--text);
                            line-height: 1.2;
                            font-family: var(--font-mono);
                            letter-spacing: -0.02em;
                        }

                        .project-list-page .year-card.is-active .year-card-count,
                        .project-list-page .year-card.is-active .year-card-label {
                            color: var(--primary-2);
                        }

                        .project-list-page .filter-status-bar {
                            display: flex;
                            align-items: center;
                            gap: 8px;
                            padding: 10px 0 14px;
                            font-size: 0.8rem;
                            color: var(--text-faint);
                        }

                        .project-list-page .filter-status-bar strong {
                            color: var(--text-soft);
                        }

                        /* ─── DataTables overrides ──────────────────────────────── */
                        .project-list-page .dataTables_wrapper {
                            padding: 0;
                        }

                        .project-list-page .dataTables_wrapper .dataTables_filter,
                        .project-list-page .dataTables_wrapper .dataTables_length {
                            padding: 12px 20px 0;
                        }

                        .project-list-page .dataTables_wrapper .dataTables_info,
                        .project-list-page .dataTables_wrapper .dataTables_paginate {
                            padding: 12px 20px;
                        }

                        .project-list-page .dataTables_wrapper .dataTables_filter input,
                        .project-list-page .dataTables_wrapper .dataTables_length select {
                            border: 1px solid var(--tw-line-strong);
                            border-radius: var(--tw-radius-md);
                            padding: 5px 10px;
                            min-height: 34px;
                            box-shadow: none;
                            font-size: 0.83rem;
                            color: var(--tw-text);
                        }

                        .project-list-page .dataTables_wrapper .dataTables_filter input:focus,
                        .project-list-page .dataTables_wrapper .dataTables_length select:focus {
                            outline: none;
                            border-color: var(--tw-blue);
                        }

                        .project-list-page .dataTables_wrapper .dataTables_info {
                            font-size: 0.8rem;
                            color: var(--tw-text-faint);
                        }

                        .project-list-page .dataTables_wrapper .dataTables_paginate .paginate_button {
                            border-radius: var(--tw-radius-md) !important;
                            padding: 4px 10px !important;
                            font-size: 0.82rem !important;
                            border: 1px solid transparent !important;
                            background: transparent !important;
                            color: var(--tw-text-soft) !important;
                        }

                        .project-list-page .dataTables_wrapper .dataTables_paginate .paginate_button.current,
                        .project-list-page .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover {
                            color: #fff !important;
                            border-color: var(--tw-blue) !important;
                            background: var(--tw-blue) !important;
                        }

                        .project-list-page .dataTables_wrapper .dataTables_paginate .paginate_button:hover:not(.current) {
                            background: var(--tw-blue-soft) !important;
                            border-color: var(--tw-blue-border) !important;
                            color: var(--tw-blue-2) !important;
                        }

                        /* loading overlay */
                        .project-list-page .data-table-container {
                            position: relative;
                        }

                        .project-list-page .table-init-hidden {
                            opacity: 0;
                        }

                        .project-list-page .table-init-ready {
                            opacity: 1;
                            transition: opacity .2s ease;
                        }

                        /* ─── Responsive ────────────────────────────────────────── */
                        @media (max-width: 767px) {
                            .project-list-page .page-title {
                                font-size: 1.45rem;
                            }

                            .project-list-page .page-header {
                                flex-direction: column;
                                align-items: flex-start;
                            }

                            .project-list-page .year-grid {
                                grid-template-columns: repeat(2, 1fr);
                            }
                        }

                        @keyframes project-spinner {
                            to {
                                transform: rotate(360deg);
                            }
                        }
                    </style>

                    <?php
                    $totalProjects = !empty($data) ? count($data) : 0;
                    $userLevel = strtolower(trim((string)$this->session->userdata('level')));
                    $isAdmin = ($userLevel === 'admin');
                    $isStaff = in_array($userLevel, ['staff', 'encoder'], true);

                    $yearSummary = [];
                    $allClientTracker = [];

                    if ($isAdmin && !empty($data)) {
                        foreach ($data as $srow) {
                            $yearKey = 'No Year';
                            if (!empty($srow->contractDate) && $srow->contractDate !== '0000-00-00') {
                                $ts = strtotime($srow->contractDate);
                                if ($ts) {
                                    $yearKey = date('Y', $ts);
                                }
                            }
                            $clientKey = isset($srow->Customer) ? trim((string)$srow->Customer) : '';
                            if ($clientKey === '') {
                                $clientKey = '[NO CLIENT]';
                            }
                            if (!isset($yearSummary[$yearKey])) {
                                $yearSummary[$yearKey] = [];
                            }
                            $yearSummary[$yearKey][$clientKey] = true;
                            $allClientTracker[$clientKey] = true;
                        }
                        if (!empty($yearSummary)) {
                            uksort($yearSummary, function ($a, $b) {
                                if ($a === 'No Year') return 1;
                                if ($b === 'No Year') return -1;
                                return strcmp($b, $a);
                            });
                        }
                    }
                    ?>

                    <?php if ($this->session->flashdata('success')): ?>
                        <div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
                            <?= html_escape($this->session->flashdata('success')); ?>
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    <?php endif; ?>

                    <?php if ($this->session->flashdata('danger')): ?>
                        <div class="alert alert-danger alert-dismissible fade show mt-3" role="alert">
                            <?= html_escape($this->session->flashdata('danger')); ?>
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    <?php endif; ?>

                    <!-- Page header -->
                    <div class="page-header">
                        <div>
                            <div class="page-eyebrow">Project Management</div>
                            <h4 class="page-title">Projects</h4>
                            <div class="page-subtitle">Manage active engagements, view tasks, and track deployment status.</div>
                        </div>
                        <?php if ($isAdmin): ?>
                            <div class="page-actions">
                                <a href="<?= base_url(); ?>Page/addProject" class="btn-submit">
                                    <i class="mdi mdi-briefcase-plus-outline"></i>
                                    Add Project
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Project table card -->
                    <div class="card-stack">
                        <div class="theme-card">
                            <div class="theme-card-head">
                                <div>
                                    <h5 class="theme-card-title">Project Register</h5>
                                    <div class="theme-card-subtitle">All active project engagements with linked tasks and deployment records.</div>
                                </div>
                                <span class="count-pill">
                                    <i class="mdi mdi-briefcase-outline"></i>
                                    <?= number_format($totalProjects); ?> project<?= $totalProjects !== 1 ? 's' : ''; ?>
                                </span>
                            </div>

                            <div class="theme-card-body" style="padding:0;">
                                <div class="data-table-container loading">
                                    <table id="project-table" class="table project-table mb-0 table-init-hidden">
                                        <thead>
                                            <tr>
                                                <th>Category</th>
                                                <th>Project</th>
                                                <th>Client</th>
                                                <th>Contract Date</th>
                                                <?php if ($isAdmin): ?>
                                                    <th style="text-align:right;">Cost</th>
                                                <?php endif; ?>
                                                <th style="text-align:center;">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (!empty($data)): ?>
                                                <?php foreach ($data as $row): ?>
                                                    <?php
                                                    $projectID          = isset($row->projectID)          ? $row->projectID          : '';
                                                    $projectCategory    = isset($row->projectCategory)    ? $row->projectCategory    : '';
                                                    $projectDescription = isset($row->projectDescription) ? $row->projectDescription : '';
                                                    $clientName         = isset($row->Customer)           ? $row->Customer           : '';
                                                    $contractDate       = '—';
                                                    $contractYear       = 'No Year';

                                                    if (!empty($row->contractDate) && $row->contractDate !== '0000-00-00') {
                                                        $timestamp = strtotime($row->contractDate);
                                                        if ($timestamp) {
                                                            $contractDate = date('M d, Y', $timestamp);
                                                            $contractYear = date('Y', $timestamp);
                                                        } else {
                                                            $contractDate = htmlspecialchars($row->contractDate, ENT_QUOTES, 'UTF-8');
                                                        }
                                                    }

                                                    $costValue    = isset($row->projectCost) ? $row->projectCost : '';
                                                    $projectCost  = is_numeric($costValue) ? number_format((float)$costValue, 2) : htmlspecialchars((string)$costValue, ENT_QUOTES, 'UTF-8');
                                                    $projectParam = urlencode((string)$projectID);

                                                    $editUrl       = base_url('Page/updateProject?id=' . $projectParam);
                                                    $deleteUrl     = base_url('Page/deleteProject?id=' . $projectParam);
                                                    $tasksUrl      = base_url('Page/taskPerProject?projectID=' . $projectParam);
                                                    $deploymentUrl = base_url('Page/projectDeploymentStatus?projectID=' . $projectParam);
                                                    ?>
                                                    <tr data-contract-year="<?= htmlspecialchars($contractYear, ENT_QUOTES, 'UTF-8'); ?>">
                                                        <td>
                                                            <?php if ($projectCategory !== ''): ?>
                                                                <span class="category-tag"><?= htmlspecialchars($projectCategory, ENT_QUOTES, 'UTF-8'); ?></span>
                                                            <?php else: ?>
                                                                <span class="cell-muted">—</span>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td class="project-name"><?= htmlspecialchars($projectDescription, ENT_QUOTES, 'UTF-8'); ?></td>
                                                        <td class="cell-muted"><?= htmlspecialchars($clientName, ENT_QUOTES, 'UTF-8'); ?></td>
                                                        <td class="cell-muted"><?= $contractDate; ?></td>

                                                        <?php if ($isAdmin): ?>
                                                            <td style="text-align:right;">
                                                                <?php if (is_numeric($costValue)): ?>
                                                                    <span class="cost-value"><?= $projectCost; ?></span>
                                                                <?php else: ?>
                                                                    <span class="cell-muted">—</span>
                                                                <?php endif; ?>
                                                            </td>
                                                        <?php endif; ?>

                                                        <td style="text-align:center;">
                                                            <div class="project-actions">
                                                                <?php if ($isAdmin): ?>
                                                                    <a class="action-icon tasks" href="<?= $tasksUrl; ?>" title="View Tasks">
                                                                        <i class="mdi mdi-format-list-checks"></i>
                                                                    </a>
                                                                <?php endif; ?>

                                                                <a class="action-icon deployment" href="<?= $deploymentUrl; ?>" title="Deployment Status">
                                                                    <i class="mdi mdi-clipboard-check-outline"></i>
                                                                </a>

                                                                <?php if ($isAdmin): ?>
                                                                    <a class="action-icon edit" href="<?= $editUrl; ?>" title="Edit Project">
                                                                        <i class="mdi mdi-square-edit-outline"></i>
                                                                    </a>

                                                                    <a class="action-icon delete" href="<?= $deleteUrl; ?>"
                                                                        title="Delete Project"
                                                                        onclick="return confirm('Are you sure you want to delete this project?');">
                                                                        <i class="mdi mdi-trash-can-outline"></i>
                                                                    </a>
                                                                <?php endif; ?>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Client summary by year (admin only) -->
                    <?php if ($isAdmin): ?>
                        <div class="card-stack">
                            <div class="theme-card">
                                <div class="theme-card-head">
                                    <div>
                                        <h5 class="theme-card-title">Client Summary by Year</h5>
                                        <div class="theme-card-subtitle">Click a year to filter the project table by contract year.</div>
                                    </div>
                                </div>
                                <div class="theme-card-body">
                                    <div class="filter-status-bar">
                                        <i class="mdi mdi-filter-outline"></i>
                                        Showing: <strong id="projectSummaryFilterStatus">All Years</strong>
                                    </div>
                                    <div class="year-grid">
                                        <!-- All years card -->
                                        <div class="year-card filter-year-card is-active" data-year="">
                                            <div class="year-card-icon"><i class="mdi mdi-calendar-range"></i></div>
                                            <div class="year-card-body">
                                                <div class="year-card-label">All Years</div>
                                                <div class="year-card-count"><?= count($allClientTracker); ?></div>
                                            </div>
                                        </div>

                                        <?php if (!empty($yearSummary)): ?>
                                            <?php foreach ($yearSummary as $yearKey => $clients): ?>
                                                <div class="year-card filter-year-card" data-year="<?= htmlspecialchars($yearKey, ENT_QUOTES, 'UTF-8'); ?>">
                                                    <div class="year-card-icon"><i class="mdi mdi-calendar-outline"></i></div>
                                                    <div class="year-card-body">
                                                        <div class="year-card-label"><?= htmlspecialchars($yearKey, ENT_QUOTES, 'UTF-8'); ?></div>
                                                        <div class="year-card-count"><?= count($clients); ?></div>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                </div>

                <?php include('includes/footer.php'); ?>
            </div>
        </div>

        <?php include('includes/themecustomizer.php'); ?>

        <script src="<?= base_url(); ?>assets/js/vendor.min.js"></script>
        <script src="<?= base_url(); ?>assets/libs/datatables/jquery.dataTables.min.js"></script>
        <script src="<?= base_url(); ?>assets/libs/datatables/dataTables.bootstrap4.min.js"></script>
        <script src="<?= base_url(); ?>assets/libs/datatables/dataTables.responsive.min.js"></script>
        <script src="<?= base_url(); ?>assets/libs/datatables/responsive.bootstrap4.min.js"></script>
        <script src="<?= base_url(); ?>assets/libs/sweetalert2/sweetalert2.min.js"></script>
        <script src="<?= base_url(); ?>assets/js/app.min.js"></script>

        <script>
            (function($) {
                'use strict';
                $(function() {
                    var $tableContainer = $('.project-list-page .data-table-container');
                    var $projectTable = $('#project-table');
                    var selectedYear = '';
                    var projectTable = null;

                    $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
                        if (!projectTable || !selectedYear) return true;
                        var rowNode = projectTable.row(dataIndex).node();
                        if (!rowNode) return true;
                        return ($(rowNode).attr('data-contract-year') || '') === selectedYear;
                    });

                    projectTable = $projectTable.DataTable({
                        responsive: true,
                        autoWidth: false,
                        order: [
                            [1, 'asc']
                        ],
                        language: {
                            emptyTable: 'No projects recorded yet.'
                        },
                        columnDefs: [{
                            targets: -1,
                            orderable: false,
                            searchable: false
                        }],
                        initComplete: function() {
                            $projectTable.removeClass('table-init-hidden').addClass('table-init-ready');
                            $tableContainer.removeClass('loading').addClass('ready');
                        }
                    });

                    $('.filter-year-card').on('click', function() {
                        if (!projectTable) return;
                        var year = ($(this).data('year') || '').toString();
                        var label = year !== '' ? year : 'All Years';
                        selectedYear = year;
                        $('.filter-year-card').removeClass('is-active');
                        $(this).addClass('is-active');
                        projectTable.draw();
                        $('#projectSummaryFilterStatus').text(label);
                    });
                });
            })(jQuery);
        </script>

        <script>
            (function() {
                var type = "<?= $this->session->flashdata('toast_type'); ?>";
                var text = "<?= $this->session->flashdata('toast_text'); ?>";
                if (type && text && typeof Swal !== 'undefined') {
                    Swal.mixin({
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 2200,
                        timerProgressBar: true
                    }).fire({
                        icon: type,
                        title: text
                    });
                }
            })();
        </script>

</body>

</html>
