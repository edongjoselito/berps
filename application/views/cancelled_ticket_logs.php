<?php
$clientData = isset($client) ? $client : null;
$logs = isset($logs) && is_array($logs) ? array_values($logs) : array();
$isClientView = !empty($isClientView);
$clientName = trim((string) ($clientData->Customer ?? 'Client Portal'));
$backUrl = $isClientView ? base_url('Page/clientMyTickets') : base_url('Page/supportIssues?scope=closed');
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
                    .client-profile-page .btn-soft { display:inline-flex; align-items:center; gap:10px; border-radius:18px; padding:12px 20px; border:1px solid var(--line); background:rgba(255,255,255,.9); color:var(--text); font-weight:700; text-decoration:none; box-shadow:0 10px 26px rgba(15,23,42,.04); }
                    .client-profile-page .panel-card { background:var(--surface); border:1px solid rgba(255,255,255,.75); border-radius:var(--radius-xl); box-shadow:var(--shadow); overflow:hidden; }
                    .client-profile-page .panel-header { padding:24px 28px 18px; border-bottom:1px solid var(--line); }
                    .client-profile-page .panel-title { margin:0; color:var(--text); font-size:1.45rem; font-weight:800; }
                    .client-profile-page .panel-subtitle { margin-top:8px; color:var(--text-soft); font-size:.98rem; }
                    .client-profile-page .panel-body { padding:22px 28px 28px; }
                    .client-profile-page .table-responsive { border:1px solid var(--line); border-radius:22px; overflow:hidden; background:var(--surface-2); }
                    .client-profile-page table { margin-bottom:0; }
                    .client-profile-page thead th { border-top:none; background:#f8fbff; color:var(--text-faint); font-size:.78rem; font-weight:800; letter-spacing:.08em; text-transform:uppercase; padding:16px 18px; }
                    .client-profile-page tbody td { vertical-align:top; padding:18px; color:var(--text); border-top:1px solid var(--line); }
                    .client-profile-page .ticket-pill, .client-profile-page .status-pill { display:inline-flex; align-items:center; padding:5px 10px; border-radius:999px; font-size:.74rem; font-weight:700; }
                    .client-profile-page .ticket-pill { background:#f8fbff; border:1px solid var(--line); color:var(--text-soft); }
                    .client-profile-page .status-pill { background:var(--danger-soft); color:var(--danger); }
                    .client-profile-page .muted { color:var(--text-soft); }
                    .client-profile-page .btn-open { display:inline-flex; align-items:center; justify-content:center; padding:8px 12px; border-radius:12px; text-decoration:none; font-size:.82rem; font-weight:700; background:#eef6ff; color:#2563eb; border:1px solid var(--line); }
                    .client-profile-page .empty-state { padding:30px 24px; text-align:center; color:var(--text-soft); }
                    .client-profile-page .alert { border:none; border-radius:18px; box-shadow:0 10px 26px rgba(15,23,42,.04); }
                </style>

                <style>
                    /* Hero Banner */
                    .client-profile-page .cl-hero {
                        display: flex;
                        align-items: center;
                        justify-content: space-between;
                        flex-wrap: wrap;
                        gap: 16px;
                        padding: 28px 24px;
                        margin: 0 0 22px;
                        border-radius: 16px;
                        background: #7f1d1d;
                        box-shadow: 0 8px 32px rgba(127, 29, 29, 0.25);
                        position: relative;
                        overflow: hidden;
                    }

                    .client-profile-page .cl-hero::before {
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

                    .client-profile-page .cl-hero::after {
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

                    .client-profile-page .cl-hero__content {
                        position: relative;
                        z-index: 1;
                    }

                    .client-profile-page .cl-hero__eyebrow {
                        display: inline-flex;
                        align-items: center;
                        gap: 6px;
                        margin-bottom: 8px;
                        color: rgba(255, 255, 255, 0.85);
                        font-size: 0.78rem;
                        font-weight: 600;
                        letter-spacing: 0.04em;
                    }

                    .client-profile-page .cl-hero__eyebrow i {
                        font-size: 1rem;
                    }

                    .client-profile-page .cl-hero__title {
                        margin: 0 0 4px 0;
                        color: #fff;
                        font-size: clamp(1.6rem, 2.5vw, 2.2rem);
                        font-weight: 800;
                        line-height: 1.15;
                        letter-spacing: -0.02em;
                        font-family: var(--font-primary, Montserrat, Segoe UI, Arial, sans-serif), "Apple Color Emoji", "Segoe UI Emoji", "Noto Color Emoji", sans-serif;
                    }

                    .client-profile-page .cl-hero__subtitle {
                        margin: 0;
                        color: rgba(255, 255, 255, 0.8);
                        font-size: 0.88rem;
                        max-width: 520px;
                    }

                    .client-profile-page .cl-hero__actions {
                        display: flex;
                        align-items: center;
                        flex-wrap: wrap;
                        gap: 10px;
                        position: relative;
                        z-index: 1;
                    }

                    .client-profile-page .cl-hero-btn {
                        display: inline-flex;
                        align-items: center;
                        gap: 6px;
                        padding: 8px 16px;
                        border-radius: 10px;
                        border: 1px solid rgba(255, 255, 255, 0.3);
                        background: rgba(255, 255, 255, 0.15);
                        color: #fff;
                        font-size: 0.82rem;
                        font-weight: 600;
                        text-decoration: none;
                        cursor: pointer;
                        transition: all 0.18s ease;
                    }

                    .client-profile-page .cl-hero-btn:hover,
                    .client-profile-page .cl-hero-btn:focus {
                        background: rgba(255, 255, 255, 0.25);
                        border-color: rgba(255, 255, 255, 0.5);
                        color: #fff;
                        text-decoration: none;
                        transform: translateY(-1px);
                    }

                    .client-profile-page .trash-swing {
                        display: inline-block;
                        animation: trash-swing 2.5s ease-in-out infinite;
                        transform-origin: 50% 80%;
                    }

                    @keyframes trash-swing {
                        0%, 70%, 100% { transform: rotate(0deg); }
                        15% { transform: rotate(-12deg); }
                        30% { transform: rotate(10deg); }
                        45% { transform: rotate(-6deg); }
                        60% { transform: rotate(0deg); }
                    }

                    .client-profile-page .panel-card {
                        border-top: 3px solid #7f1d1d;
                    }

                    @media (max-width: 767px) {
                        .client-profile-page .cl-hero,
                        .client-profile-page .cl-hero__actions {
                            flex-direction: column;
                            align-items: stretch;
                        }

                        .client-profile-page .cl-hero {
                            padding: 20px;
                        }

                        .client-profile-page .cl-hero-btn {
                            flex: 1 1 auto;
                            justify-content: center;
                        }
                    }
                </style>

                <div class="cl-hero">
                    <div class="cl-hero__content">
                        <div class="cl-hero__eyebrow">
                            <i class="mdi mdi-trash-can-outline"></i>
                            <?= $isClientView ? 'Client Portal' : 'Support Logs'; ?>
                        </div>
                        <h1 class="cl-hero__title">Cancelled Ticket Logs <span class="trash-swing">🗑️</span></h1>
                        <?php if (!$isClientView): ?>
                            <p class="cl-hero__subtitle">Review all support tickets cancelled by clients and open each one for full context.</p>
                        <?php endif; ?>
                    </div>
                    <div class="cl-hero__actions">
                        <a class="cl-hero-btn" href="<?= htmlspecialchars($backUrl, ENT_QUOTES, 'UTF-8'); ?>">
                            <i class="mdi mdi-arrow-left"></i>
                            <span>Back</span>
                        </a>
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
                        <h2 class="panel-title">Cancelled Ticket History</h2>
                        <div class="panel-subtitle"><?= number_format(count($logs)); ?> cancelled ticket<?= count($logs) === 1 ? '' : 's'; ?> found.</div>
                    </div>
                    <div class="panel-body">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>Ticket No.</th>
                                        <?php if (!$isClientView): ?>
                                            <th>Client</th>
                                        <?php endif; ?>
                                        <th>Subject</th>
                                        <th>Department</th>
                                        <th>Project</th>
                                        <th>Date Cancelled</th>
                                        <th>Status</th>
                                        <th class="text-right">View</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($logs)): ?>
                                        <?php foreach ($logs as $log): ?>
                                            <tr>
                                                <td><span class="ticket-pill"><?= htmlspecialchars((string) ($log->ticket_number ?? ''), ENT_QUOTES, 'UTF-8'); ?></span></td>
                                                <?php if (!$isClientView): ?>
                                                    <td><?= htmlspecialchars((string) ($log->customer_name ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                                                <?php endif; ?>
                                                <td>
                                                    <strong><?= htmlspecialchars((string) ($log->title ?? ''), ENT_QUOTES, 'UTF-8'); ?></strong>
                                                    <?php if (trim((string) ($log->cancellation_note ?? '')) !== ''): ?>
                                                        <div class="muted" style="margin-top:4px;"><?= htmlspecialchars((string) $log->cancellation_note, ENT_QUOTES, 'UTF-8'); ?></div>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?= htmlspecialchars((string) ($log->department_name ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td><?= htmlspecialchars((string) ($log->projectDescription ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td><?= htmlspecialchars((string) ($log->cancelled_at ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td><span class="status-pill"><?= htmlspecialchars(ucwords(str_replace('_', ' ', (string) ($log->status ?? 'cancelled'))), ENT_QUOTES, 'UTF-8'); ?></span></td>
                                                <td class="text-right">
                                                    <?php if ($isClientView): ?>
                                                        <a class="btn-open" href="<?= base_url('Page/clientTicketView?id=' . (int) ($log->id ?? 0)); ?>">Open</a>
                                                    <?php else: ?>
                                                        <a class="btn-open" href="<?= base_url('Page/supportIssueView?id=' . (int) ($log->id ?? 0)); ?>">Open</a>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr><td colspan="<?= $isClientView ? '8' : '9'; ?>" class="empty-state">No cancelled ticket logs found.</td></tr>
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
