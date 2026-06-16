<?php
$totalTickets         = (int) ($totalTickets ?? 0);
$openTickets          = (int) ($openTickets ?? 0);
$closedTickets        = (int) ($closedTickets ?? 0);
$unassignedTickets    = (int) ($unassignedTickets ?? 0);
$awaitingReplyTickets = (int) ($awaitingReplyTickets ?? 0);
$clientReplyRequired  = (int) ($clientReplyRequired ?? 0);
$thisMonthCreated     = (int) ($thisMonthCreated ?? 0);
$thisMonthClosed      = (int) ($thisMonthClosed ?? 0);
$avgResolutionHours   = (float) ($avgResolutionHours ?? 0);
$byDepartment         = isset($byDepartment) && is_array($byDepartment) ? $byDepartment : array();
$byPriority           = isset($byPriority) && is_array($byPriority) ? $byPriority : array();
$byStatus             = isset($byStatus) && is_array($byStatus) ? $byStatus : array();
$byCategory           = isset($byCategory) && is_array($byCategory) ? $byCategory : array();
$byEmployee           = isset($byEmployee) && is_array($byEmployee) ? $byEmployee : array();
$trendLabels          = isset($trendLabels) && is_array($trendLabels) ? $trendLabels : array();
$createdSeries        = isset($createdSeries) && is_array($createdSeries) ? $createdSeries : array();
$closedSeries         = isset($closedSeries) && is_array($closedSeries) ? $closedSeries : array();
$recentTickets        = isset($recentTickets) && is_array($recentTickets) ? $recentTickets : array();
$oldestOpenTickets    = isset($oldestOpenTickets) && is_array($oldestOpenTickets) ? $oldestOpenTickets : array();
$unreadNotificationCount = (int) ($unreadNotificationCount ?? 0);
$isStaffUser          = !empty($isStaffUser);

$resolutionLabel = '—';
if ($avgResolutionHours > 0) {
    if ($avgResolutionHours >= 24) {
        $resolutionLabel = number_format($avgResolutionHours / 24, 1) . ' days';
    } else {
        $resolutionLabel = number_format($avgResolutionHours, 1) . ' hrs';
    }
}

$priorityColors = array(
    'urgent'   => '#e11d48',
    'high'     => '#f97316',
    'medium'   => '#2563eb',
    'low'      => '#059669',
    'critical' => '#7c3aed',
);

$maxDeptCount = 0;
foreach ($byDepartment as $row) {
    $maxDeptCount = max($maxDeptCount, (int) ($row->ticket_count ?? 0));
}
$maxCatCount = 0;
foreach ($byCategory as $row) {
    $maxCatCount = max($maxCatCount, (int) ($row->ticket_count ?? 0));
}
$maxEmpCount = 0;
foreach ($byEmployee as $row) {
    $maxEmpCount = max($maxEmpCount, (int) ($row->total_assigned ?? 0));
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
            <div class="container-fluid support-dashboard-page">
                <style>
                    .support-dashboard-page {
                        --bg: #f5f7fb;
                        --surface: rgba(255, 255, 255, 0.94);
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
                        --purple: #7c3aed;
                        --purple-soft: #f3e8ff;
                        --shadow: 0 14px 40px rgba(15, 23, 42, 0.08);
                        --shadow-soft: 0 8px 22px rgba(15, 23, 42, 0.05);
                        --radius-xl: 22px;
                        --radius-lg: 16px;
                        font-family: 'Inter', 'Poppins', 'Segoe UI', Arial, sans-serif;
                        background:
                            radial-gradient(circle at top left, rgba(37, 99, 235, 0.08), transparent 28%),
                            radial-gradient(circle at top right, rgba(16, 185, 129, 0.08), transparent 24%),
                            linear-gradient(180deg, #f8fbff 0%, #f4f7fb 100%);
                        min-height: 100vh;
                        padding-bottom: 32px;
                    }
                    .support-dashboard-page * { box-sizing: border-box; }
                    .support-dashboard-page .sd-header { margin: 24px 0 20px; display:flex; justify-content:space-between; align-items:flex-end; gap:16px; flex-wrap:wrap; }
                    .support-dashboard-page .sd-eyebrow { display:inline-flex; align-items:center; padding:7px 12px; border-radius:999px; background:rgba(37,99,235,.08); color:#1d4ed8; font-size:.76rem; font-weight:700; letter-spacing:.08em; text-transform:uppercase; margin-bottom:10px; }
                    .support-dashboard-page .sd-title { margin:0; color:var(--text); font-size:clamp(1.85rem, 2.6vw, 2.4rem); line-height:1.05; font-weight:800; }
                    .support-dashboard-page .sd-subtitle { margin:10px 0 0; color:var(--text-soft); font-size:.98rem; max-width:780px; }
                    .support-dashboard-page .sd-action-row { display:flex; gap:10px; flex-wrap:wrap; }
                    .support-dashboard-page .btn-soft, .support-dashboard-page .btn-solid {
                        display:inline-flex; align-items:center; gap:8px; border-radius:14px; padding:10px 16px; font-weight:700; font-size:.85rem; text-decoration:none;
                        box-shadow:0 8px 20px rgba(15,23,42,.05); border:1px solid var(--line); background:rgba(255,255,255,.92); color:var(--text);
                        transition: transform .15s ease, box-shadow .15s ease;
                    }
                    .support-dashboard-page .btn-soft:hover { transform: translateY(-1px); box-shadow:0 12px 26px rgba(15,23,42,.08); }
                    .support-dashboard-page .btn-solid { background:linear-gradient(135deg,var(--primary),#1d4ed8); color:#fff; border-color:transparent; }
                    .support-dashboard-page .btn-solid:hover { color:#fff; }

                    .support-dashboard-page .stat-grid { display:grid; grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)); gap:16px; margin-bottom:22px; }
                    .support-dashboard-page .stat-card { background:var(--surface-2); border:1px solid var(--line); border-radius:var(--radius-lg); padding:20px; box-shadow:var(--shadow-soft); position:relative; overflow:hidden; }
                    .support-dashboard-page .stat-card::after { content:''; position:absolute; right:-22px; top:-22px; width:80px; height:80px; border-radius:50%; opacity:.1; }
                    .support-dashboard-page .stat-card .stat-label { color:var(--text-faint); font-size:.72rem; font-weight:800; letter-spacing:.08em; text-transform:uppercase; }
                    .support-dashboard-page .stat-card .stat-value { color:var(--text); font-size:2rem; font-weight:800; margin-top:6px; line-height:1.1; }
                    .support-dashboard-page .stat-card .stat-meta { color:var(--text-soft); font-size:.82rem; margin-top:6px; }
                    .support-dashboard-page .stat-card .stat-icon { position:absolute; top:18px; right:18px; width:42px; height:42px; border-radius:12px; display:flex; align-items:center; justify-content:center; font-size:1.3rem; }
                    .support-dashboard-page .sc-total .stat-icon { background:var(--primary-soft); color:var(--primary); }
                    .support-dashboard-page .sc-total::after { background:var(--primary); }
                    .support-dashboard-page .sc-open .stat-icon { background:var(--warning-soft); color:var(--warning); }
                    .support-dashboard-page .sc-open::after { background:var(--warning); }
                    .support-dashboard-page .sc-closed .stat-icon { background:var(--success-soft); color:var(--success); }
                    .support-dashboard-page .sc-closed::after { background:var(--success); }
                    .support-dashboard-page .sc-unassigned .stat-icon { background:var(--danger-soft); color:var(--danger); }
                    .support-dashboard-page .sc-unassigned::after { background:var(--danger); }
                    .support-dashboard-page .sc-await .stat-icon { background:var(--purple-soft); color:var(--purple); }
                    .support-dashboard-page .sc-await::after { background:var(--purple); }
                    .support-dashboard-page .sc-avg .stat-icon { background:#e0f2fe; color:#0369a1; }
                    .support-dashboard-page .sc-avg::after { background:#0369a1; }

                    .support-dashboard-page .panel-card { background:var(--surface); border:1px solid rgba(255,255,255,.75); border-radius:var(--radius-xl); box-shadow:var(--shadow); overflow:hidden; margin-bottom:22px; }
                    .support-dashboard-page .panel-header { padding:20px 24px 14px; border-bottom:1px solid var(--line); display:flex; justify-content:space-between; align-items:center; gap:12px; flex-wrap:wrap; }
                    .support-dashboard-page .panel-title { margin:0; color:var(--text); font-size:1.15rem; font-weight:800; }
                    .support-dashboard-page .panel-subtitle { color:var(--text-soft); font-size:.86rem; margin-top:4px; }
                    .support-dashboard-page .panel-body { padding:20px 24px 22px; }
                    .support-dashboard-page .grid-2 { display:grid; grid-template-columns: 1.4fr 1fr; gap:22px; }
                    .support-dashboard-page .grid-2-equal { display:grid; grid-template-columns: 1fr 1fr; gap:22px; }
                    @media (max-width: 991.98px) {
                        .support-dashboard-page .grid-2, .support-dashboard-page .grid-2-equal { grid-template-columns: 1fr; }
                    }

                    .support-dashboard-page .progress-list { display:flex; flex-direction:column; gap:14px; }
                    .support-dashboard-page .progress-row .progress-row-head { display:flex; justify-content:space-between; align-items:center; margin-bottom:6px; font-size:.88rem; color:var(--text); font-weight:600; }
                    .support-dashboard-page .progress-row .pr-count { color:var(--text-soft); font-size:.82rem; font-weight:700; }
                    .support-dashboard-page .progress-bar-wrap { background:#f1f5f9; height:10px; border-radius:999px; overflow:hidden; }
                    .support-dashboard-page .progress-bar-fill { height:100%; border-radius:999px; transition: width .4s ease; }

                    .support-dashboard-page .priority-grid { display:grid; grid-template-columns:repeat(auto-fit, minmax(140px,1fr)); gap:12px; }
                    .support-dashboard-page .priority-card { padding:14px 16px; border-radius:14px; border:1px solid var(--line); background:#fff; }
                    .support-dashboard-page .priority-card .pc-label { font-size:.72rem; font-weight:800; letter-spacing:.08em; text-transform:uppercase; color:var(--text-faint); }
                    .support-dashboard-page .priority-card .pc-value { font-size:1.6rem; font-weight:800; margin-top:4px; }
                    .support-dashboard-page .priority-card.urgent { background:#fff1f2; border-color:#fecdd3; }
                    .support-dashboard-page .priority-card.urgent .pc-value { color:#e11d48; }
                    .support-dashboard-page .priority-card.high { background:#fff7ed; border-color:#fed7aa; }
                    .support-dashboard-page .priority-card.high .pc-value { color:#f97316; }
                    .support-dashboard-page .priority-card.medium { background:#eaf2ff; border-color:#bfdbfe; }
                    .support-dashboard-page .priority-card.medium .pc-value { color:#2563eb; }
                    .support-dashboard-page .priority-card.low { background:#ecfdf5; border-color:#a7f3d0; }
                    .support-dashboard-page .priority-card.low .pc-value { color:#059669; }
                    .support-dashboard-page .priority-card.critical { background:#f3e8ff; border-color:#ddd6fe; }
                    .support-dashboard-page .priority-card.critical .pc-value { color:#7c3aed; }

                    .support-dashboard-page table { margin-bottom: 0; width:100%; }
                    .support-dashboard-page thead th { border-top:none; background:#f8fbff; color:var(--text-faint); font-size:.72rem; font-weight:800; letter-spacing:.08em; text-transform:uppercase; padding:12px 14px; }
                    .support-dashboard-page tbody td { vertical-align:middle; padding:12px 14px; color:var(--text); border-top:1px solid var(--line); font-size:.88rem; }
                    .support-dashboard-page .ticket-pill { display:inline-flex; align-items:center; padding:4px 9px; border-radius:999px; font-size:.72rem; font-weight:700; background:#f8fbff; border:1px solid var(--line); color:var(--text-soft); }
                    .support-dashboard-page .status-pill { display:inline-flex; align-items:center; padding:4px 9px; border-radius:999px; font-size:.72rem; font-weight:700; }
                    .support-dashboard-page .status-open, .support-dashboard-page .status-awaiting_reply, .support-dashboard-page .status-reopened { background:var(--primary-soft); color:var(--primary); }
                    .support-dashboard-page .status-closed, .support-dashboard-page .status-resolved, .support-dashboard-page .status-done, .support-dashboard-page .status-completed { background:var(--success-soft); color:var(--success); }
                    .support-dashboard-page .status-assigned { background:var(--purple-soft); color:var(--purple); }
                    .support-dashboard-page .status-cancelled, .support-dashboard-page .status-canceled { background:var(--danger-soft); color:var(--danger); }
                    .support-dashboard-page .empty-state { padding:24px; text-align:center; color:var(--text-soft); font-size:.9rem; }

                    .support-dashboard-page .chart-wrap { position:relative; height:280px; }
                    .support-dashboard-page .donut-wrap { position:relative; height:240px; }

                    .support-dashboard-page .age-pill { display:inline-flex; align-items:center; padding:3px 8px; border-radius:999px; font-size:.7rem; font-weight:700; background:#fff1f2; color:#e11d48; }
                    .support-dashboard-page .age-pill.warn { background:#fff7ed; color:#d97706; }
                    .support-dashboard-page .age-pill.ok { background:#ecfdf5; color:#059669; }
                </style>

                <div class="sd-header">
                    <div>
                        <div class="sd-eyebrow">Customer Support</div>
                        <h1 class="sd-title">Support Dashboard</h1>
                        <p class="sd-subtitle">Real-time overview of ticket activity, workload distribution, and resolution metrics. <?php if ($unreadNotificationCount > 0): ?><strong><?= number_format($unreadNotificationCount); ?></strong> unread notifications.<?php endif; ?></p>
                    </div>
                    <div class="sd-action-row">
                        <a class="btn-soft" href="<?= base_url('Page/supportIssues?scope=unassigned'); ?>"><i class="mdi mdi-account-question-outline"></i> Unassigned</a>
                        <a class="btn-soft" href="<?= base_url('Page/supportIssues?scope=awaiting_reply'); ?>"><i class="mdi mdi-message-reply-outline"></i> Awaiting Reply</a>
                        <a class="btn-solid" href="<?= base_url('Page/supportIssues?scope=open'); ?>"><i class="mdi mdi-ticket-outline"></i> View All Tickets</a>
                    </div>
                </div>

                <?php if ($this->session->flashdata('success')): ?>
                    <div class="alert alert-success" style="border:none;border-radius:16px;"><?= $this->session->flashdata('success'); ?></div>
                <?php endif; ?>

                <!-- Stat Cards -->
                <div class="stat-grid">
                    <div class="stat-card sc-total">
                        <div class="stat-icon"><i class="mdi mdi-ticket-outline"></i></div>
                        <div class="stat-label">Total Tickets</div>
                        <div class="stat-value"><?= number_format($totalTickets); ?></div>
                        <div class="stat-meta"><?= number_format($thisMonthCreated); ?> this month</div>
                    </div>
                    <div class="stat-card sc-open">
                        <div class="stat-icon"><i class="mdi mdi-clock-outline"></i></div>
                        <div class="stat-label">Open Tickets</div>
                        <div class="stat-value"><?= number_format($openTickets); ?></div>
                        <div class="stat-meta">Currently active</div>
                    </div>
                    <div class="stat-card sc-closed">
                        <div class="stat-icon"><i class="mdi mdi-check-decagram"></i></div>
                        <div class="stat-label">Closed Tickets</div>
                        <div class="stat-value"><?= number_format($closedTickets); ?></div>
                        <div class="stat-meta"><?= number_format($thisMonthClosed); ?> closed this month</div>
                    </div>
                    <div class="stat-card sc-unassigned">
                        <div class="stat-icon"><i class="mdi mdi-account-question-outline"></i></div>
                        <div class="stat-label">Unassigned</div>
                        <div class="stat-value"><?= number_format($unassignedTickets); ?></div>
                        <div class="stat-meta">Needs assignment</div>
                    </div>
                    <div class="stat-card sc-await">
                        <div class="stat-icon"><i class="mdi mdi-message-reply-outline"></i></div>
                        <div class="stat-label">Awaiting Reply</div>
                        <div class="stat-value"><?= number_format($awaitingReplyTickets); ?></div>
                        <div class="stat-meta"><?= number_format($clientReplyRequired); ?> need client reply</div>
                    </div>
                    <div class="stat-card sc-avg">
                        <div class="stat-icon"><i class="mdi mdi-timer-sand"></i></div>
                        <div class="stat-label">Avg. Resolution</div>
                        <div class="stat-value"><?= htmlspecialchars($resolutionLabel, ENT_QUOTES, 'UTF-8'); ?></div>
                        <div class="stat-meta">From open to closed</div>
                    </div>
                </div>

                <!-- Trend Chart + Status Donut -->
                <div class="grid-2">
                    <div class="panel-card">
                        <div class="panel-header">
                            <div>
                                <h2 class="panel-title">Ticket Activity (Last 14 days)</h2>
                                <div class="panel-subtitle">Newly created vs. closed tickets per day</div>
                            </div>
                        </div>
                        <div class="panel-body">
                            <div class="chart-wrap">
                                <canvas id="sdTrendChart"></canvas>
                            </div>
                        </div>
                    </div>

                    <div class="panel-card">
                        <div class="panel-header">
                            <div>
                                <h2 class="panel-title">Status Breakdown</h2>
                                <div class="panel-subtitle">Distribution of ticket statuses</div>
                            </div>
                        </div>
                        <div class="panel-body">
                            <?php if (!empty($byStatus)): ?>
                                <div class="donut-wrap">
                                    <canvas id="sdStatusChart"></canvas>
                                </div>
                            <?php else: ?>
                                <div class="empty-state">No status data available.</div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Priority Grid -->
                <div class="panel-card">
                    <div class="panel-header">
                        <div>
                            <h2 class="panel-title">By Priority</h2>
                            <div class="panel-subtitle">Distribution across priority levels</div>
                        </div>
                    </div>
                    <div class="panel-body">
                        <?php if (!empty($byPriority)): ?>
                            <div class="priority-grid">
                                <?php foreach ($byPriority as $row): ?>
                                    <?php
                                    $key = strtolower(trim((string) ($row->priority_key ?? 'medium')));
                                    $cssClass = in_array($key, array('urgent','high','medium','low','critical'), true) ? $key : 'medium';
                                    ?>
                                    <div class="priority-card <?= htmlspecialchars($cssClass, ENT_QUOTES, 'UTF-8'); ?>">
                                        <div class="pc-label"><?= htmlspecialchars(ucfirst($key), ENT_QUOTES, 'UTF-8'); ?></div>
                                        <div class="pc-value"><?= number_format((int) ($row->ticket_count ?? 0)); ?></div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="empty-state">No priority data available.</div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Department + Category -->
                <div class="grid-2-equal">
                    <div class="panel-card">
                        <div class="panel-header">
                            <div>
                                <h2 class="panel-title">By Department</h2>
                                <div class="panel-subtitle">Total tickets per department</div>
                            </div>
                        </div>
                        <div class="panel-body">
                            <?php if (!empty($byDepartment)): ?>
                                <div class="progress-list">
                                    <?php foreach ($byDepartment as $row): ?>
                                        <?php
                                        $count = (int) ($row->ticket_count ?? 0);
                                        $openCount = (int) ($row->open_count ?? 0);
                                        $width = $maxDeptCount > 0 ? round(($count / $maxDeptCount) * 100) : 0;
                                        ?>
                                        <div class="progress-row">
                                            <div class="progress-row-head">
                                                <span><?= htmlspecialchars((string) ($row->department_name ?? 'Unassigned'), ENT_QUOTES, 'UTF-8'); ?></span>
                                                <span class="pr-count"><?= number_format($count); ?> <small style="font-weight:600;color:var(--text-faint);">(<?= number_format($openCount); ?> open)</small></span>
                                            </div>
                                            <div class="progress-bar-wrap">
                                                <div class="progress-bar-fill" style="width: <?= $width; ?>%; background: linear-gradient(90deg, var(--primary), #1d4ed8);"></div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <div class="empty-state">No department data available.</div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="panel-card">
                        <div class="panel-header">
                            <div>
                                <h2 class="panel-title">By Category</h2>
                                <div class="panel-subtitle">Top issue categories</div>
                            </div>
                        </div>
                        <div class="panel-body">
                            <?php if (!empty($byCategory)): ?>
                                <div class="progress-list">
                                    <?php foreach ($byCategory as $row): ?>
                                        <?php
                                        $count = (int) ($row->ticket_count ?? 0);
                                        $width = $maxCatCount > 0 ? round(($count / $maxCatCount) * 100) : 0;
                                        ?>
                                        <div class="progress-row">
                                            <div class="progress-row-head">
                                                <span><?= htmlspecialchars(ucfirst((string) ($row->category ?? 'general')), ENT_QUOTES, 'UTF-8'); ?></span>
                                                <span class="pr-count"><?= number_format($count); ?></span>
                                            </div>
                                            <div class="progress-bar-wrap">
                                                <div class="progress-bar-fill" style="width: <?= $width; ?>%; background: linear-gradient(90deg, #7c3aed, #a855f7);"></div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <div class="empty-state">No category data available.</div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Workload by Employee -->
                <div class="panel-card">
                    <div class="panel-header">
                        <div>
                            <h2 class="panel-title">Workload by Employee (Top 10)</h2>
                            <div class="panel-subtitle">Tickets currently assigned to support staff</div>
                        </div>
                    </div>
                    <div class="panel-body">
                        <?php if (!empty($byEmployee)): ?>
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Employee</th>
                                            <th class="text-center">Total Assigned</th>
                                            <th class="text-center">Open</th>
                                            <th class="text-center">Closed</th>
                                            <th>Workload</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($byEmployee as $row): ?>
                                            <?php
                                            $total = (int) ($row->total_assigned ?? 0);
                                            $open = (int) ($row->open_assigned ?? 0);
                                            $closed = (int) ($row->closed_assigned ?? 0);
                                            $width = $maxEmpCount > 0 ? round(($total / $maxEmpCount) * 100) : 0;
                                            ?>
                                            <tr>
                                                <td><strong><?= htmlspecialchars(trim((string) ($row->employee_name ?? '')) !== '' ? trim((string) $row->employee_name) : 'Unknown', ENT_QUOTES, 'UTF-8'); ?></strong></td>
                                                <td class="text-center"><span class="ticket-pill"><?= number_format($total); ?></span></td>
                                                <td class="text-center"><span class="status-pill status-open"><?= number_format($open); ?></span></td>
                                                <td class="text-center"><span class="status-pill status-closed"><?= number_format($closed); ?></span></td>
                                                <td style="min-width:160px;">
                                                    <div class="progress-bar-wrap">
                                                        <div class="progress-bar-fill" style="width: <?= $width; ?>%; background: linear-gradient(90deg, #f97316, #fb923c);"></div>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="empty-state">No employees have assigned tickets.</div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Recent + Aging tickets -->
                <div class="grid-2-equal">
                    <div class="panel-card">
                        <div class="panel-header">
                            <div>
                                <h2 class="panel-title">Recent Tickets</h2>
                                <div class="panel-subtitle">Latest 10 submissions</div>
                            </div>
                            <a class="btn-soft" href="<?= base_url('Page/supportIssues?scope=all'); ?>" style="font-size:.78rem;padding:6px 12px;">View All</a>
                        </div>
                        <div class="panel-body" style="padding:0;">
                            <?php if (!empty($recentTickets)): ?>
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Ticket</th>
                                            <th>Customer</th>
                                            <th>Status</th>
                                            <th>Created</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($recentTickets as $issue): ?>
                                            <?php $statusKey = strtolower(trim((string) ($issue->status ?? 'open'))); ?>
                                            <tr>
                                                <td>
                                                    <a href="<?= base_url('Page/supportIssueView?id=' . (int) ($issue->id ?? 0)); ?>" style="text-decoration:none;color:var(--primary);font-weight:700;">
                                                        <?= htmlspecialchars((string) ($issue->ticket_number ?? '#' . (int) ($issue->id ?? 0)), ENT_QUOTES, 'UTF-8'); ?>
                                                    </a>
                                                    <div style="color:var(--text-soft);font-size:.78rem;margin-top:2px;"><?= htmlspecialchars(mb_strimwidth((string) ($issue->title ?? ''), 0, 40, '…'), ENT_QUOTES, 'UTF-8'); ?></div>
                                                </td>
                                                <td><?= htmlspecialchars((string) ($issue->customer_name ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td><span class="status-pill status-<?= htmlspecialchars($statusKey, ENT_QUOTES, 'UTF-8'); ?>"><?= htmlspecialchars(str_replace('_',' ', ucfirst($statusKey !== '' ? $statusKey : 'open')), ENT_QUOTES, 'UTF-8'); ?></span></td>
                                                <td style="color:var(--text-soft);font-size:.82rem;"><?= !empty($issue->created_at) ? date('M j, Y', strtotime((string) $issue->created_at)) : '—'; ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            <?php else: ?>
                                <div class="empty-state">No tickets yet.</div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="panel-card">
                        <div class="panel-header">
                            <div>
                                <h2 class="panel-title">Oldest Open Tickets</h2>
                                <div class="panel-subtitle">Tickets needing attention</div>
                            </div>
                        </div>
                        <div class="panel-body" style="padding:0;">
                            <?php if (!empty($oldestOpenTickets)): ?>
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Ticket</th>
                                            <th>Department</th>
                                            <th>Age</th>
                                            <th>Assigned</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($oldestOpenTickets as $issue): ?>
                                            <?php
                                            $createdTs = !empty($issue->created_at) ? strtotime((string) $issue->created_at) : 0;
                                            $ageDays = $createdTs > 0 ? floor((time() - $createdTs) / 86400) : 0;
                                            $ageClass = $ageDays >= 7 ? '' : ($ageDays >= 3 ? 'warn' : 'ok');
                                            ?>
                                            <tr>
                                                <td>
                                                    <a href="<?= base_url('Page/supportIssueView?id=' . (int) ($issue->id ?? 0)); ?>" style="text-decoration:none;color:var(--primary);font-weight:700;">
                                                        <?= htmlspecialchars((string) ($issue->ticket_number ?? '#' . (int) ($issue->id ?? 0)), ENT_QUOTES, 'UTF-8'); ?>
                                                    </a>
                                                    <div style="color:var(--text-soft);font-size:.78rem;margin-top:2px;"><?= htmlspecialchars(mb_strimwidth((string) ($issue->title ?? ''), 0, 36, '…'), ENT_QUOTES, 'UTF-8'); ?></div>
                                                </td>
                                                <td style="font-size:.82rem;"><?= htmlspecialchars((string) ($issue->department_name ?? '—'), ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td><span class="age-pill <?= $ageClass; ?>"><?= number_format($ageDays); ?>d</span></td>
                                                <td style="font-size:.82rem;color:var(--text-soft);"><?= htmlspecialchars(trim((string) ($issue->assigned_employee_name ?? '')) !== '' ? trim((string) $issue->assigned_employee_name) : 'Unassigned', ENT_QUOTES, 'UTF-8'); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            <?php else: ?>
                                <div class="empty-state">No open tickets.</div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

            </div>
            <?php include('includes/footer.php'); ?>
        </div>
    </div>
</div>
<script src="<?= base_url(); ?>assets/js/vendor.min.js"></script>
<script src="<?= base_url(); ?>assets/libs/chart-js/Chart.bundle.min.js"></script>
<script src="<?= base_url(); ?>assets/js/app.min.js"></script>
<script>
(function() {
    if (typeof Chart === 'undefined') { return; }

    var trendCtx = document.getElementById('sdTrendChart');
    if (trendCtx) {
        new Chart(trendCtx.getContext('2d'), {
            type: 'line',
            data: {
                labels: <?= json_encode($trendLabels); ?>,
                datasets: [
                    {
                        label: 'Created',
                        data: <?= json_encode($createdSeries); ?>,
                        borderColor: '#2563eb',
                        backgroundColor: 'rgba(37, 99, 235, 0.12)',
                        borderWidth: 2.5,
                        tension: 0.35,
                        pointRadius: 3,
                        pointBackgroundColor: '#2563eb',
                        fill: true
                    },
                    {
                        label: 'Closed',
                        data: <?= json_encode($closedSeries); ?>,
                        borderColor: '#059669',
                        backgroundColor: 'rgba(5, 150, 105, 0.10)',
                        borderWidth: 2.5,
                        tension: 0.35,
                        pointRadius: 3,
                        pointBackgroundColor: '#059669',
                        fill: true
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'top', labels: { boxWidth: 12, usePointStyle: true } } },
                legend: { position: 'top', labels: { boxWidth: 12, usePointStyle: true } },
                scales: {
                    yAxes: [{ ticks: { beginAtZero: true, precision: 0 }, gridLines: { color: 'rgba(0,0,0,0.04)' } }],
                    xAxes: [{ gridLines: { display: false } }]
                }
            }
        });
    }

    var statusCtx = document.getElementById('sdStatusChart');
    if (statusCtx) {
        var statusLabels = <?= json_encode(array_map(function($r){ return ucfirst(str_replace('_',' ', (string) ($r->status_key ?? ''))); }, $byStatus)); ?>;
        var statusValues = <?= json_encode(array_map(function($r){ return (int) ($r->ticket_count ?? 0); }, $byStatus)); ?>;
        var statusColors = ['#2563eb', '#059669', '#f97316', '#7c3aed', '#e11d48', '#0ea5e9', '#d97706', '#10b981'];
        new Chart(statusCtx.getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: statusLabels,
                datasets: [{
                    data: statusValues,
                    backgroundColor: statusColors.slice(0, statusValues.length),
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutoutPercentage: 65,
                legend: { position: 'bottom', labels: { boxWidth: 12, usePointStyle: true, padding: 14 } }
            }
        });
    }
})();
</script>
</body>
</html>
