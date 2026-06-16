<?php
$clientData = isset($client) ? $client : null;
$itemRows = isset($items) && is_array($items) ? array_values($items) : array();
$clientName = trim((string) ($clientData->Customer ?? 'Client Portal'));
$backUrl = base_url() . 'Page/clientDashboard';

$priorityLabels = array(
    '1' => 'High',
    '2' => 'Medium',
    '3' => 'Low',
);

$priorityClasses = array(
    '1' => 'priority-high',
    '2' => 'priority-medium',
    '3' => 'priority-low',
);
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
                        --primary-soft: #eaf2ff;
                        --success: #059669;
                        --success-soft: #ecfdf5;
                        --warning: #d97706;
                        --warning-soft: #fff7ed;
                        --danger: #e11d48;
                        --danger-soft: #fff1f2;
                        --shadow: 0 14px 40px rgba(15, 23, 42, 0.08);
                        --radius-xl: 22px;
                        font-family: 'Inter', 'Poppins', 'Segoe UI', Arial, sans-serif;
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
                    .client-profile-page .reference-pill, .client-profile-page .priority-pill, .client-profile-page .type-pill { display:inline-flex; align-items:center; padding:5px 10px; border-radius:999px; font-size:.74rem; font-weight:700; }
                    .client-profile-page .reference-pill, .client-profile-page .type-pill { background:#f8fbff; border:1px solid var(--line); color:var(--text-soft); }
                    .client-profile-page .priority-high { background:var(--danger-soft); color:var(--danger); }
                    .client-profile-page .priority-medium { background:var(--warning-soft); color:var(--warning); }
                    .client-profile-page .priority-low { background:var(--success-soft); color:var(--success); }
                    .client-profile-page .muted { color:var(--text-soft); }
                    .client-profile-page .empty-state { padding:30px 24px; text-align:center; color:var(--text-soft); }
                </style>

                <div class="cp-header">
                    <div>
                        <div class="cp-eyebrow">Client Portal</div>
                        <h1 class="cp-title">Requested Today</h1>
                        <!-- <p class="cp-subtitle">Review everything submitted today for <?= htmlspecialchars($clientName !== '' ? $clientName : 'your account', ENT_QUOTES, 'UTF-8'); ?>, including task requests and support issues.</p> -->
                    </div>
                    <a class="btn-soft" href="<?= htmlspecialchars($backUrl, ENT_QUOTES, 'UTF-8'); ?>"><i class="fas fa-arrow-left"></i>Back to Dashboard</a>
                </div>

                <div class="panel-card">
                    <div class="panel-header">
                        <h2 class="panel-title">Today&apos;s Submitted Items</h2>
                        <div class="panel-subtitle"><?= number_format(count($itemRows)); ?> item<?= count($itemRows) === 1 ? '' : 's'; ?> submitted today.</div>
                    </div>
                    <div class="panel-body">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                <tr>
                                    <th>Type</th>
                                    <th>Title</th>
                                    <th>Project</th>
                                    <th>Assigned To</th>
                                    <th>Priority</th>
                                    <th>Reported</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php if (!empty($itemRows)): ?>
                                    <?php foreach ($itemRows as $item): ?>
                                        <?php
                                        $itemType = trim((string) ($item->itemType ?? 'task'));
                                        $title = trim((string) ($item->title ?? ''));
                                        $ticketNumber = trim((string) ($item->ticketNumber ?? ''));
                                        if ($ticketNumber !== '') {
                                            $title .= ' (' . $ticketNumber . ')';
                                        }
                                        $priorityKey = (string) ($item->priority ?? '2');
                                        $priorityLabel = isset($priorityLabels[$priorityKey]) ? $priorityLabels[$priorityKey] : ($itemType === 'support' ? ucfirst((string) ($item->status ?? 'Open')) : 'Medium');
                                        $priorityClass = isset($priorityClasses[$priorityKey]) ? $priorityClasses[$priorityKey] : 'priority-medium';
                                        $reportedDate = trim((string) ($item->reportedDate ?? ''));
                                        ?>
                                        <tr>
                                            <td><span class="type-pill"><?= htmlspecialchars($itemType === 'support' ? 'Support' : 'Task', ENT_QUOTES, 'UTF-8'); ?></span></td>
                                            <td>
                                                <strong><?= htmlspecialchars($title !== '' ? $title : 'N/A', ENT_QUOTES, 'UTF-8'); ?></strong>
                                                <?php if (!empty($item->taskID)): ?>
                                                    <div class="muted">Task #<?= (int) $item->taskID; ?></div>
                                                <?php endif; ?>
                                            </td>
                                            <td><?= htmlspecialchars((string) ($item->projectDescription ?? 'N/A'), ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td><?= htmlspecialchars((string) ($item->assignedPersonName ?? 'N/A'), ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td><span class="priority-pill <?= htmlspecialchars($priorityClass, ENT_QUOTES, 'UTF-8'); ?>"><?= htmlspecialchars($priorityLabel, ENT_QUOTES, 'UTF-8'); ?></span></td>
                                            <td><?= htmlspecialchars($reportedDate !== '' ? date('M d, Y h:i A', strtotime($reportedDate)) : 'N/A', ENT_QUOTES, 'UTF-8'); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="6" class="empty-state">No requests submitted today.</td></tr>
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
