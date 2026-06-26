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

                <div class="cp-header">
                    <div>
                        <div class="cp-eyebrow"><?= $isClientView ? 'Client Portal' : 'Support Logs'; ?></div>
                        <h1 class="cp-title">Cancelled Ticket Logs</h1>
                        <?php if (!$isClientView): ?>
                            <p class="cp-subtitle">Review all support tickets cancelled by clients and open each one for full context.</p>
                        <?php endif; ?>
                    </div>
                    <a class="btn-soft" href="<?= htmlspecialchars($backUrl, ENT_QUOTES, 'UTF-8'); ?>"><i class="fas fa-arrow-left"></i>Back</a>
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
            <?php include('includes/footer.php'); ?>
        </div>
    </div>
</div>
<script src="<?= base_url(); ?>assets/js/vendor.min.js"></script>
<script src="<?= base_url(); ?>assets/js/app.min.js"></script>
</body>
</html>
