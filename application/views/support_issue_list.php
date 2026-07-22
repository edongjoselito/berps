<?php
$issues = isset($issues) && is_array($issues) ? $issues : array();
$unreadNotificationCount = isset($unreadNotificationCount) ? (int) $unreadNotificationCount : 0;
$scope = isset($scope) ? (string) $scope : 'open';
$scopeLabels = array(
    'unassigned' => 'Unassigned Tickets',
    'awaiting_reply' => 'Awaiting Reply',
    'open' => 'All Open Tickets',
    'closed' => 'All Closed Tickets',
    'all' => 'All Tickets',
);
$pageTitle = $scopeLabels[$scope] ?? 'Support Tickets';
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
            <div class="container-fluid client-profile-page">
                <style>
                    .client-profile-page {
                        --bg: #f5f7fb;
                        --surface: rgba(255, 255, 255, 0.92);
                        --surface-2: #ffffff;
                        --line: #e7ecf3;
                        --text: #122033;
                        --text-soft: #5e7188;
                        --text-faint: #8ea0b5;
                        --primary: #2563eb;
                        --primary-soft: #eaf2ff;
                        --success: #059669;
                        --success-soft: #ecfdf5;
                        --warning: #d97706;
                        --warning-soft: #fff7ed;
                        --danger: #e11d48;
                        --danger-soft: #fff1f2;
                        --shadow: 0 14px 40px rgba(15, 23, 42, 0.08);
                        --radius-xl: 22px;
                        font-family: var(--font-primary, Montserrat, Segoe UI, Arial, sans-serif);
                        background:
                            radial-gradient(circle at top left, rgba(37, 99, 235, 0.08), transparent 28%),
                            radial-gradient(circle at top right, rgba(16, 185, 129, 0.08), transparent 24%),
                            linear-gradient(180deg, #f8fbff 0%, #f4f7fb 100%);
                        min-height: 100vh;
                        padding-bottom: 24px;
                    }
                    .client-profile-page .cp-header { margin: 24px 0 22px; display:flex; justify-content:space-between; align-items:flex-end; gap:16px; flex-wrap:wrap; }
                    .client-profile-page .cp-eyebrow { display:inline-flex; align-items:center; padding:7px 12px; border-radius:999px; background:rgba(37,99,235,.08); color:#1d4ed8; font-size:.76rem; font-weight:700; letter-spacing:.08em; text-transform:uppercase; margin-bottom:10px; }
                    .client-profile-page .cp-title { margin:0; color:var(--text); font-size:clamp(2rem,3vw,2.7rem); line-height:1.05; font-weight:800; }
                    .client-profile-page .cp-subtitle { margin:12px 0 0; color:var(--text-soft); font-size:1rem; max-width:780px; }
                    .client-profile-page .btn-soft, .client-profile-page .btn-solid, .client-profile-page .btn-filter {
                        display:inline-flex; align-items:center; gap:10px; border-radius:18px; padding:12px 20px; font-weight:700; text-decoration:none;
                        box-shadow:0 10px 26px rgba(15,23,42,.04); border:1px solid var(--line); background:rgba(255,255,255,.9); color:var(--text);
                    }
                    .client-profile-page .btn-solid { background:linear-gradient(135deg,var(--primary),#1d4ed8); color:#fff; border-color:transparent; }
                    .client-profile-page .panel-card { background:var(--surface); border:1px solid rgba(255,255,255,.75); border-radius:var(--radius-xl); box-shadow:var(--shadow); overflow:hidden; }
                    .client-profile-page .panel-header { padding:24px 28px 18px; border-bottom:1px solid var(--line); }
                    .client-profile-page .panel-title { margin:0; color:var(--text); font-size:1.45rem; font-weight:800; }
                    .client-profile-page .panel-subtitle { margin-top:8px; color:var(--text-soft); font-size:.98rem; }
                    .client-profile-page .panel-body { padding:22px 28px 28px; }
                    .client-profile-page .filter-row { display:flex; gap:10px; flex-wrap:wrap; margin-bottom:18px; }
                    .client-profile-page .btn-filter { padding:10px 16px; border-radius:999px; font-size:.85rem; }
                    .client-profile-page .btn-filter.is-active { background:linear-gradient(135deg,var(--primary),#1d4ed8); color:#fff; border-color:transparent; }
                    .client-profile-page .table-responsive {
                        display: block;
                        width: 100%;
                        border: 1px solid var(--line);
                        border-radius: 22px;
                        overflow: hidden;
                        background: var(--surface-2);
                    }
                    .client-profile-page table {
                        margin-bottom: 0;
                        width: 100%;
                        min-width: 0;
                    }
                    .client-profile-page thead th { border-top:none; background:#f8fbff; color:var(--text-faint); font-size:.78rem; font-weight:800; letter-spacing:.08em; text-transform:uppercase; padding:16px 18px; }
                    .client-profile-page tbody td { vertical-align:top; padding:18px; color:var(--text); border-top:1px solid var(--line); }
                    .client-profile-page .status-pill, .client-profile-page .ticket-pill {
                        display:inline-flex; align-items:center; padding:5px 10px; border-radius:999px; font-size:.74rem; font-weight:700;
                    }
                    .client-profile-page .ticket-pill { background:#f8fbff; border:1px solid var(--line); color:var(--text-soft); }
                    .client-profile-page .status-open, .client-profile-page .status-awaiting_reply, .client-profile-page .status-reopened { background:var(--primary-soft); color:var(--primary); }
                    .client-profile-page .status-closed, .client-profile-page .status-resolved, .client-profile-page .status-done, .client-profile-page .status-completed { background:var(--success-soft); color:var(--success); }
                    .client-profile-page .status-cancelled, .client-profile-page .status-canceled { background:var(--danger-soft); color:var(--danger); }
                    .client-profile-page .action-stack { display:flex; gap:8px; justify-content:flex-end; flex-wrap:wrap; }
                    .client-profile-page .btn-sm-theme { display:inline-flex; align-items:center; justify-content:center; padding:8px 12px; border-radius:12px; text-decoration:none; font-size:.82rem; font-weight:700; border:1px solid var(--line); }
                    .client-profile-page .btn-open { background:#eef6ff; color:#2563eb; }
                    .client-profile-page .empty-state { padding:30px 24px; text-align:center; color:var(--text-soft); }
                    .client-profile-page .alert { border:none; border-radius:18px; box-shadow:0 10px 26px rgba(15,23,42,.04); }
                    @media (max-width: 1199.98px) {
                        .client-profile-page .panel-body {
                            padding: 18px 16px 20px;
                        }
                        .client-profile-page .table-responsive {
                            border: none;
                            background: transparent;
                            overflow: visible !important;
                        }
                        .client-profile-page table,
                        .client-profile-page thead,
                        .client-profile-page tbody,
                        .client-profile-page tr,
                        .client-profile-page th,
                        .client-profile-page td {
                            display: block;
                            width: 100%;
                        }
                        .client-profile-page table {
                            min-width: 0 !important;
                        }
                        .client-profile-page thead {
                            display: none;
                        }
                        .client-profile-page tbody tr {
                            margin-bottom: 16px;
                            border: 1px solid var(--line);
                            border-radius: 18px;
                            background: var(--surface-2);
                            box-shadow: 0 10px 26px rgba(15,23,42,.04);
                            overflow: hidden;
                        }
                        .client-profile-page tbody td {
                            border-top: 1px solid var(--line);
                            padding: 14px 16px;
                        }
                        .client-profile-page tbody td:first-child {
                            border-top: none;
                        }
                        .client-profile-page tbody td::before {
                            content: attr(data-label);
                            display: block;
                            margin-bottom: 6px;
                            color: var(--text-faint);
                            font-size: .72rem;
                            font-weight: 800;
                            letter-spacing: .08em;
                            text-transform: uppercase;
                        }
                        .client-profile-page .action-stack {
                            justify-content: flex-start;
                        }
                        .client-profile-page td.text-right {
                            text-align: left !important;
                        }
                        .client-profile-page .ticket-pill,
                        .client-profile-page .status-pill {
                            width: fit-content;
                        }
                    }

                    /* ─── Hero Banner ─────────────────────────────────────── */
                    .client-profile-page .support-hero {
                        display: flex;
                        align-items: center;
                        justify-content: space-between;
                        flex-wrap: wrap;
                        gap: 16px;
                        padding: 28px 24px;
                        margin: 24px 0 22px;
                        border-radius: 22px;
                        background: #0a7e8c;
                        box-shadow: 0 8px 32px rgba(10, 126, 140, 0.25);
                        position: relative;
                        overflow: hidden;
                    }

                    .client-profile-page .support-hero::before {
                        content: '';
                        position: absolute;
                        top: -50%;
                        right: -10%;
                        width: 400px;
                        height: 400px;
                        border-radius: 50%;
                        background: rgba(255, 255, 255, 0.06);
                        pointer-events: none;
                    }

                    .client-profile-page .support-hero::after {
                        content: '';
                        position: absolute;
                        bottom: -60%;
                        right: 15%;
                        width: 300px;
                        height: 300px;
                        border-radius: 50%;
                        background: rgba(255, 255, 255, 0.04);
                        pointer-events: none;
                    }

                    .client-profile-page .support-hero__content {
                        position: relative;
                        z-index: 1;
                    }

                    .client-profile-page .support-hero__eyebrow {
                        display: inline-flex;
                        align-items: center;
                        gap: 6px;
                        margin-bottom: 8px;
                        color: rgba(255, 255, 255, 0.85);
                        font-size: 0.78rem;
                        font-weight: 600;
                        letter-spacing: 0.04em;
                    }

                    .client-profile-page .support-hero__eyebrow i {
                        font-size: 1rem;
                    }

                    .client-profile-page .support-hero__title {
                        margin: 0 0 4px 0;
                        color: #fff;
                        font-size: clamp(1.6rem, 2.5vw, 2.2rem);
                        font-weight: 800;
                        line-height: 1.15;
                        letter-spacing: -0.02em;
                    }

                    .client-profile-page .support-hero__subtitle {
                        margin: 0;
                        color: rgba(255, 255, 255, 0.8);
                        font-size: 0.88rem;
                        max-width: 480px;
                    }

                    /* SOS wave animation */
                    .client-profile-page .support-wave {
                        display: inline-block;
                        animation: support-wave 2s ease-in-out infinite;
                        transform-origin: 70% 70%;
                    }

                    @keyframes support-wave {
                        0%, 70%, 100% { transform: rotate(0deg); }
                        10% { transform: rotate(-20deg); }
                        20% { transform: rotate(15deg); }
                        30% { transform: rotate(-10deg); }
                        40% { transform: rotate(8deg); }
                        50% { transform: rotate(0deg); }
                    }

                    /* Teal accent on panel card */
                    .client-profile-page .panel-card {
                        border-top: 3px solid #0a7e8c;
                    }

                    .client-profile-page .panel-header {
                        border-bottom: 2px solid #0a7e8c;
                    }

                    .client-profile-page .panel-title {
                        color: #0a7e8c;
                    }

                    .client-profile-page .btn-filter.is-active {
                        background: linear-gradient(135deg, #0a7e8c, #086a77);
                        color: #fff;
                        border-color: transparent;
                    }

                    .client-profile-page .btn-open {
                        background: #e0f5f7;
                        color: #0a7e8c;
                    }

                    /* Responsive hero */
                    @media (max-width: 767px) {
                        .client-profile-page .support-hero {
                            padding: 20px;
                        }

                        .client-profile-page .support-hero__title {
                            font-size: 1.3rem;
                        }
                    }
                </style>

                <div class="support-hero">
                    <div class="support-hero__content">
                        <div class="support-hero__eyebrow">
                            <i class="mdi mdi-lifebuoy"></i>
                            Support Portal
                        </div>
                        <h1 class="support-hero__title"><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8'); ?> <span class="support-wave">🆘</span></h1>
                        <p class="support-hero__subtitle">Manage customer support tickets, track response times, and resolve issues efficiently.</p>
                    </div>
                </div>

                <?php if ($this->session->flashdata('success')): ?>
                    <div class="alert alert-success"><?= $this->session->flashdata('success'); ?></div>
                <?php endif; ?>
                <?php if ($this->session->flashdata('danger')): ?>
                    <div class="alert alert-danger"><?= $this->session->flashdata('danger'); ?></div>
                <?php endif; ?>

                <div class="panel-card">
                    <div class="panel-header">
                        <h2 class="panel-title">Support Ticket List</h2>
                        <div class="panel-subtitle"><?= number_format(count($issues)); ?> ticket<?= count($issues) === 1 ? '' : 's'; ?> visible in the current filter.</div>
                    </div>
                    <div class="panel-body">
                        <div class="filter-row">
                            <a class="btn-filter <?= $scope === 'unassigned' ? 'is-active' : ''; ?>" href="<?= base_url('Page/supportIssues?scope=unassigned'); ?>">Unassigned</a>
                            <a class="btn-filter <?= $scope === 'awaiting_reply' ? 'is-active' : ''; ?>" href="<?= base_url('Page/supportIssues?scope=awaiting_reply'); ?>">Awaiting Reply</a>
                            <a class="btn-filter <?= $scope === 'open' ? 'is-active' : ''; ?>" href="<?= base_url('Page/supportIssues?scope=open'); ?>">Open</a>
                            <a class="btn-filter <?= $scope === 'closed' ? 'is-active' : ''; ?>" href="<?= base_url('Page/supportIssues?scope=closed'); ?>">Closed</a>
                            <a class="btn-filter <?= $scope === 'all' ? 'is-active' : ''; ?>" href="<?= base_url('Page/supportIssues?scope=all'); ?>">All</a>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>Ticket No.</th>
                                        <th>Customer</th>
                                        <th>Subject</th>
                                        <th>Category</th>
                                        <th>Status</th>
                                        <th>Assigned To</th>
                                        <th class="text-right">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($issues)): ?>
                                        <?php foreach ($issues as $issue): ?>
                                            <?php $statusKey = strtolower(trim((string) ($issue->status ?? 'open'))); ?>
                                            <tr>
                                                <td data-label="Ticket No."><span class="ticket-pill"><?= htmlspecialchars((string) ($issue->ticket_number ?? ''), ENT_QUOTES, 'UTF-8'); ?></span></td>
                                                <td data-label="Customer">
                                                    <strong><?= htmlspecialchars((string) ($issue->customer_name ?? ''), ENT_QUOTES, 'UTF-8'); ?></strong>
                                                    <div style="color: var(--text-soft); margin-top: 4px;"><?= htmlspecialchars((string) (!empty($issue->projectDescription) ? $issue->projectDescription : 'General'), ENT_QUOTES, 'UTF-8'); ?></div>
                                                </td>
                                                <td data-label="Subject"><?= htmlspecialchars((string) ($issue->title ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td data-label="Category"><?= htmlspecialchars(ucfirst((string) ($issue->category ?? 'general')), ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td data-label="Status"><span class="status-pill status-<?= htmlspecialchars($statusKey, ENT_QUOTES, 'UTF-8'); ?>"><?= htmlspecialchars(str_replace('_', ' ', ucfirst($statusKey !== '' ? $statusKey : 'open')), ENT_QUOTES, 'UTF-8'); ?></span></td>
                                                <td data-label="Assigned To"><?= htmlspecialchars((string) ($issue->assigned_employee_name ?? 'Unassigned'), ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td class="text-right" data-label="Actions">
                                                    <div class="action-stack">
                                                        <a class="btn-sm-theme btn-open" href="<?= base_url('Page/supportIssueView?id=' . (int) ($issue->id ?? 0)); ?>">View</a>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr><td colspan="7" class="empty-state">No support issues found.</td></tr>
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
<script src="<?= base_url(); ?>assets/js/vendor.min.js"></script>
<script src="<?= base_url(); ?>assets/js/app.min.js"></script>
</body>
</html>
