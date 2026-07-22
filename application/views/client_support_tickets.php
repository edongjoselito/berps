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

                <style>
                    /* Hero Banner */
                    .client-profile-page .ct-hero {
                        display: flex;
                        align-items: center;
                        justify-content: space-between;
                        flex-wrap: wrap;
                        gap: 16px;
                        padding: 28px 24px;
                        margin: 0 0 22px;
                        border-radius: 16px;
                        background: #be123c;
                        box-shadow: 0 8px 32px rgba(190, 18, 60, 0.25);
                        position: relative;
                        overflow: hidden;
                    }

                    .client-profile-page .ct-hero::before {
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

                    .client-profile-page .ct-hero::after {
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

                    .client-profile-page .ct-hero__content {
                        position: relative;
                        z-index: 1;
                    }

                    .client-profile-page .ct-hero__eyebrow {
                        display: inline-flex;
                        align-items: center;
                        gap: 6px;
                        margin-bottom: 8px;
                        color: rgba(255, 255, 255, 0.85);
                        font-size: 0.78rem;
                        font-weight: 600;
                        letter-spacing: 0.04em;
                    }

                    .client-profile-page .ct-hero__eyebrow i {
                        font-size: 1rem;
                    }

                    .client-profile-page .ct-hero__title {
                        margin: 0 0 4px 0;
                        color: #fff;
                        font-size: clamp(1.6rem, 2.5vw, 2.2rem);
                        font-weight: 800;
                        line-height: 1.15;
                        letter-spacing: -0.02em;
                        font-family: var(--font-primary, Montserrat, Segoe UI, Arial, sans-serif), "Apple Color Emoji", "Segoe UI Emoji", "Noto Color Emoji", sans-serif;
                    }

                    .client-profile-page .ct-hero__subtitle {
                        margin: 0;
                        color: rgba(255, 255, 255, 0.8);
                        font-size: 0.88rem;
                        max-width: 520px;
                    }

                    .client-profile-page .ct-hero__actions {
                        display: flex;
                        align-items: center;
                        flex-wrap: wrap;
                        gap: 10px;
                        position: relative;
                        z-index: 1;
                    }

                    .client-profile-page .ct-hero-btn {
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

                    .client-profile-page .ct-hero-btn:hover,
                    .client-profile-page .ct-hero-btn:focus {
                        background: rgba(255, 255, 255, 0.25);
                        border-color: rgba(255, 255, 255, 0.5);
                        color: #fff;
                        text-decoration: none;
                        transform: translateY(-1px);
                    }

                    .client-profile-page .ct-hero-btn--solid {
                        border-color: rgba(255, 255, 255, 0.6);
                        background: rgba(255, 255, 255, 0.95);
                        color: #be123c;
                        font-weight: 700;
                    }

                    .client-profile-page .ct-hero-btn--solid:hover,
                    .client-profile-page .ct-hero-btn--solid:focus {
                        background: #fff;
                        color: #9f1239;
                    }

                    .client-profile-page .ticket-shake {
                        display: inline-block;
                        animation: ticket-shake 2.5s ease-in-out infinite;
                    }

                    @keyframes ticket-shake {
                        0%, 70%, 100% { transform: rotate(0deg); }
                        15% { transform: rotate(-10deg); }
                        30% { transform: rotate(8deg); }
                        45% { transform: rotate(-5deg); }
                        60% { transform: rotate(0deg); }
                    }

                    .client-profile-page .panel-card {
                        border-top: 3px solid #be123c;
                    }

                    @media (max-width: 767px) {
                        .client-profile-page .ct-hero,
                        .client-profile-page .ct-hero__actions {
                            flex-direction: column;
                            align-items: stretch;
                        }

                        .client-profile-page .ct-hero {
                            padding: 20px;
                        }

                        .client-profile-page .ct-hero-btn {
                            flex: 1 1 auto;
                            justify-content: center;
                        }
                    }
                </style>

                <div class="ct-hero">
                    <div class="ct-hero__content">
                        <div class="ct-hero__eyebrow">
                            <i class="mdi mdi-ticket-outline"></i>
                            Client Portal
                        </div>
                        <h1 class="ct-hero__title">My Tickets <span class="ticket-shake">🎫</span></h1>
                        <p class="ct-hero__subtitle">Track support concerns submitted for <?= htmlspecialchars($clientName !== '' ? $clientName : 'your account', ENT_QUOTES, 'UTF-8'); ?> and quickly open or cancel untouched tickets.</p>
                    </div>
                    <div class="ct-hero__actions">
                        <a class="ct-hero-btn" href="<?= htmlspecialchars($backUrl, ENT_QUOTES, 'UTF-8'); ?>">
                            <i class="mdi mdi-arrow-left"></i>
                            <span>Back to Dashboard</span>
                        </a>
                        <a class="ct-hero-btn ct-hero-btn--solid" href="<?= base_url('Page/clientReportIssue'); ?>">
                            <i class="mdi mdi-plus-circle-outline"></i>
                            <span>Report an Issue</span>
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
        </div>
        <?php include('includes/footer.php'); ?>
    </div>
</div>
<script src="<?= base_url(); ?>assets/js/vendor.min.js"></script>
<script src="<?= base_url(); ?>assets/js/app.min.js"></script>
</body>
</html>
