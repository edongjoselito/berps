<?php
$clientData = isset($client) ? $client : null;
$tickets = isset($tickets) && is_array($tickets) ? array_values($tickets) : array();
$filter = isset($filter) ? strtolower((string) $filter) : 'open';
$clientName = trim((string) ($clientData->Customer ?? 'Client Portal'));
$backUrl = base_url() . 'Page/clientDashboard';
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
                    .client-profile-page .header-actions { display:flex; gap:10px; flex-wrap:wrap; }
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
                    .client-profile-page .table-responsive { border:1px solid var(--line); border-radius:22px; overflow:hidden; background:var(--surface-2); }
                    .client-profile-page table { margin-bottom:0; }
                    .client-profile-page thead th { border-top:none; background:#f8fbff; color:var(--text-faint); font-size:.78rem; font-weight:800; letter-spacing:.08em; text-transform:uppercase; padding:16px 18px; }
                    .client-profile-page tbody td { vertical-align:top; padding:18px; color:var(--text); border-top:1px solid var(--line); }
                    .client-profile-page .status-pill, .client-profile-page .ticket-pill {
                        display:inline-flex; align-items:center; padding:5px 10px; border-radius:999px; font-size:.74rem; font-weight:700;
                    }
                    .client-profile-page .ticket-pill { background:#f8fbff; border:1px solid var(--line); color:var(--text-soft); }
                    .client-profile-page .status-open, .client-profile-page .status-awaiting_reply, .client-profile-page .status-reopened { background:var(--primary-soft); color:var(--primary); }
                    .client-profile-page .status-assigned, .client-profile-page .status-in_progress { background:#ecfdf3; color:#047857; }
                    .client-profile-page .status-closed, .client-profile-page .status-resolved, .client-profile-page .status-done, .client-profile-page .status-completed { background:var(--success-soft); color:var(--success); }
                    .client-profile-page .status-cancelled, .client-profile-page .status-canceled { background:var(--danger-soft); color:var(--danger); }
                    .client-profile-page .action-stack { display:flex; gap:8px; justify-content:flex-end; flex-wrap:wrap; }
                    .client-profile-page .btn-sm-theme { display:inline-flex; align-items:center; justify-content:center; padding:8px 12px; border-radius:12px; text-decoration:none; font-size:.82rem; font-weight:700; border:1px solid var(--line); }
                    .client-profile-page .btn-open { background:#eef6ff; color:#2563eb; }
                    .client-profile-page .btn-cancel { background:var(--danger-soft); color:var(--danger); border-color:rgba(225,29,72,.16); }
                    .client-profile-page .empty-state { padding:30px 24px; text-align:center; color:var(--text-soft); }
                    .client-profile-page .alert { border:none; border-radius:18px; box-shadow:0 10px 26px rgba(15,23,42,.04); }
                </style>

                <div class="cp-header">
                    <div>
                        <div class="cp-eyebrow">Client Portal</div>
                        <h1 class="cp-title">My Tickets</h1>
                        <!-- <p class="cp-subtitle">Track support concerns submitted for <?= htmlspecialchars($clientName !== '' ? $clientName : 'your account', ENT_QUOTES, 'UTF-8'); ?> and quickly open or cancel untouched tickets.</p> -->
                    </div>
                    <div class="header-actions">
                        <a class="btn-soft" href="<?= htmlspecialchars($backUrl, ENT_QUOTES, 'UTF-8'); ?>"><i class="fas fa-arrow-left"></i>Back to Dashboard</a>
                        <a class="btn-solid" href="<?= base_url('Page/clientReportIssue'); ?>"><i class="fas fa-plus-circle"></i>Report an Issue</a>
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
                        <div class="panel-subtitle"><?= number_format(count($tickets)); ?> ticket<?= count($tickets) === 1 ? '' : 's'; ?> visible in the current filter.</div>
                    </div>
                    <div class="panel-body">
                        <div class="filter-row">
                            <a class="btn-filter <?= $filter === 'open' ? 'is-active' : ''; ?>" href="<?= base_url('Page/clientMyTickets?filter=open'); ?>">Open</a>
                            <a class="btn-filter <?= $filter === 'closed' ? 'is-active' : ''; ?>" href="<?= base_url('Page/clientMyTickets?filter=closed'); ?>">Closed</a>
                            <a class="btn-filter <?= $filter === 'all' ? 'is-active' : ''; ?>" href="<?= base_url('Page/clientMyTickets?filter=all'); ?>">All</a>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>Ticket No.</th>
                                        <th>Subject</th>
                                        <th>Status</th>
                                        <th>Assigned To</th>
                                        <th class="text-right">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($tickets)): ?>
                                        <?php foreach ($tickets as $ticket): ?>
                                            <?php $statusKey = strtolower(trim((string) ($ticket->status ?? 'open'))); ?>
                                            <tr>
                                                <td><span class="ticket-pill"><?= htmlspecialchars((string) ($ticket->ticket_number ?? ''), ENT_QUOTES, 'UTF-8'); ?></span></td>
                                                <td>
                                                    <strong><?= htmlspecialchars((string) ($ticket->title ?? ''), ENT_QUOTES, 'UTF-8'); ?></strong>
                                                    <div style="color: var(--text-soft); margin-top: 4px;"><?= htmlspecialchars((string) (!empty($ticket->projectDescription) ? $ticket->projectDescription : 'General'), ENT_QUOTES, 'UTF-8'); ?></div>
                                                </td>
                                                <td><span class="status-pill status-<?= htmlspecialchars($statusKey, ENT_QUOTES, 'UTF-8'); ?>"><?= htmlspecialchars(ucwords(str_replace('_', ' ', $statusKey !== '' ? $statusKey : 'open')), ENT_QUOTES, 'UTF-8'); ?></span></td>
                                                <td><?= htmlspecialchars((string) ($ticket->assigned_employee_name ?? 'Waiting for assignment'), ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td class="text-right">
                                                    <div class="action-stack">
                                                        <a class="btn-sm-theme btn-open" href="<?= base_url('Page/clientTicketView?id=' . (int) ($ticket->id ?? 0)); ?>">View</a>
                                                        <?php if (!empty($ticket->can_cancel)): ?>
                                                            <form method="post" action="<?= base_url('Page/cancelClientTicket'); ?>" onsubmit="return confirm('Cancel this untouched ticket?');" style="margin:0; display:inline-flex;">
                                                                <input type="hidden" name="issue_id" value="<?= (int) ($ticket->id ?? 0); ?>">
                                                                <button type="submit" name="cancel_ticket" value="1" class="btn-sm-theme btn-cancel">Cancel</button>
                                                            </form>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr><td colspan="5" class="empty-state">No tickets found for this filter.</td></tr>
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
