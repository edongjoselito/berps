<!DOCTYPE html>
<html lang="en">

<?php include('includes/head.php'); ?>

<body>

    <div id="wrapper">

        <?php include('includes/top-nav-bar.php'); ?>
        <?php include('includes/sidebar.php'); ?>

        <div class="content-page">
            <div class="content">
                <div class="container-fluid annual-goals-page">

                    <?php if ($this->session->flashdata('success')): ?>
                        <div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
                            <?= htmlspecialchars($this->session->flashdata('success'), ENT_QUOTES, 'UTF-8'); ?>
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    <?php endif; ?>

                    <?php if ($this->session->flashdata('danger')): ?>
                        <div class="alert alert-danger alert-dismissible fade show mt-3" role="alert">
                            <?= htmlspecialchars($this->session->flashdata('danger'), ENT_QUOTES, 'UTF-8'); ?>
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    <?php endif; ?>

                    <style>
                        .annual-goals-page {
                            --bg: #f5f7fb;
                            --surface: rgba(255, 255, 255, 0.96);
                            --line: #e4ebf4;
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
                            --purple: #7c3aed;
                            --purple-soft: #f5f3ff;
                            --shadow: 0 20px 50px rgba(15, 23, 42, 0.08);
                            --shadow-soft: 0 12px 30px rgba(15, 23, 42, 0.05);
                            --radius-xl: 24px;
                            --radius-lg: 18px;
                            --radius-md: 14px;
                            --font-body: var(--font-primary);
                            --font-head: var(--font-primary);
                            font-family: var(--font-primary, Montserrat, Segoe UI, Arial, sans-serif);
                            background: radial-gradient(circle at top left, rgba(37, 99, 235, 0.08), transparent 28%),
                                radial-gradient(circle at top right, rgba(124, 58, 237, 0.08), transparent 24%),
                                linear-gradient(180deg, #f8fbff 0%, #f4f7fb 100%);
                            min-height: 100vh;
                            padding-bottom: 28px;
                        }

                        .annual-goals-page .ag-header {
                            margin: 24px 0 18px;
                            display: flex;
                            justify-content: space-between;
                            align-items: flex-end;
                            gap: 16px;
                            flex-wrap: wrap;
                        }

                        .annual-goals-page .ag-title-wrap {
                            flex: 1 1 auto;
                        }

                        .annual-goals-page .ag-eyebrow {
                            display: inline-flex;
                            align-items: center;
                            gap: 8px;
                            padding: 7px 12px;
                            border-radius: 999px;
                            background: rgba(124, 58, 237, 0.08);
                            color: #7c3aed;
                            font-size: 0.74rem;
                            font-weight: 700;
                            letter-spacing: 0.08em;
                            text-transform: uppercase;
                            margin-bottom: 12px;
                        }

                        .annual-goals-page .ag-title {
                            margin: 0;
                            font-family: var(--font-primary, Montserrat, Segoe UI, Arial, sans-serif);
                            font-size: 2rem;
                            font-weight: 800;
                            letter-spacing: -0.04em;
                            color: var(--text);
                            line-height: 1.1;
                        }

                        .annual-goals-page .ag-subtitle {
                            margin-top: 8px;
                            color: var(--text-soft);
                            font-size: 0.93rem;
                        }

                        .annual-goals-page .ag-btn {
                            display: inline-flex;
                            align-items: center;
                            gap: 8px;
                            padding: 12px 20px;
                            border-radius: 12px;
                            font-weight: 700;
                            font-size: 0.9rem;
                            border: none;
                            cursor: pointer;
                            transition: all 0.2s ease;
                        }

                        .annual-goals-page .ag-btn-primary {
                            background: linear-gradient(135deg, #7c3aed, #a855f7);
                            color: #fff;
                            box-shadow: 0 4px 14px rgba(124, 58, 237, 0.3);
                        }

                        .annual-goals-page .ag-btn-primary:hover {
                            transform: translateY(-2px);
                            box-shadow: 0 6px 20px rgba(124, 58, 237, 0.4);
                        }

                        .annual-goals-page .ag-card {
                            background: var(--surface);
                            backdrop-filter: blur(12px);
                            border: 1px solid rgba(255, 255, 255, 0.72);
                            border-radius: 24px;
                            box-shadow: var(--shadow);
                            overflow: hidden;
                            margin-bottom: 24px;
                        }

                        .annual-goals-page .ag-card-header {
                            padding: 20px 24px;
                            border-bottom: 1px solid var(--line);
                            background: linear-gradient(180deg, rgba(255, 255, 255, 0.96), rgba(249, 251, 255, 0.96));
                            display: flex;
                            justify-content: space-between;
                            align-items: center;
                        }

                        .annual-goals-page .ag-card-title {
                            margin: 0;
                            font-size: 1.1rem;
                            font-weight: 800;
                            color: var(--text);
                        }

                        .annual-goals-page .ag-card-body {
                            padding: 24px;
                        }

                        .annual-goals-page .current-stats {
                            display: grid;
                            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
                            gap: 20px;
                            margin-bottom: 30px;
                        }

                        .annual-goals-page .stat-box {
                            background: linear-gradient(135deg, #fff 0%, #f8fafc 100%);
                            border: 1px solid var(--line);
                            border-radius: 18px;
                            padding: 24px;
                            position: relative;
                            overflow: hidden;
                        }

                        .annual-goals-page .stat-box::before {
                            content: '';
                            position: absolute;
                            top: 0;
                            left: 0;
                            right: 0;
                            height: 4px;
                            background: linear-gradient(90deg, #7c3aed, #a855f7);
                        }

                        .annual-goals-page .stat-box.success::before {
                            background: linear-gradient(90deg, #059669, #10b981);
                        }

                        .annual-goals-page .stat-box.warning::before {
                            background: linear-gradient(90deg, #d97706, #f59e0b);
                        }

                        .annual-goals-page .stat-label {
                            font-size: 0.82rem;
                            font-weight: 700;
                            color: var(--text-faint);
                            text-transform: uppercase;
                            letter-spacing: 0.06em;
                            margin-bottom: 8px;
                        }

                        .annual-goals-page .stat-value {
                            font-size: 2.2rem;
                            font-weight: 800;
                            color: var(--text);
                            letter-spacing: -0.02em;
                            line-height: 1.2;
                        }

                        .annual-goals-page .stat-sub {
                            margin-top: 6px;
                            font-size: 0.85rem;
                            color: var(--text-soft);
                        }

                        .annual-goals-page .progress-section {
                            margin-top: 16px;
                        }

                        .annual-goals-page .progress-label {
                            display: flex;
                            justify-content: space-between;
                            font-size: 0.8rem;
                            margin-bottom: 6px;
                            color: var(--text-soft);
                        }

                        .annual-goals-page .progress {
                            height: 8px;
                            background: #e2e8f0;
                            border-radius: 4px;
                            overflow: hidden;
                        }

                        .annual-goals-page .progress-bar {
                            background: linear-gradient(90deg, #7c3aed, #a855f7);
                            border-radius: 4px;
                        }

                        .annual-goals-page .progress-bar.success {
                            background: linear-gradient(90deg, #059669, #10b981);
                        }

                        .annual-goals-page .progress-bar.warning {
                            background: linear-gradient(90deg, #d97706, #f59e0b);
                        }

                        .annual-goals-page .table-responsive {
                            border-radius: 18px;
                        }

                        .annual-goals-page #goals-table {
                            border-collapse: separate !important;
                            border-spacing: 0 10px !important;
                            margin-top: -10px !important;
                        }

                        .annual-goals-page #goals-table thead th {
                            background: transparent !important;
                            color: var(--text-faint) !important;
                            border: none !important;
                            font-size: 0.74rem;
                            font-weight: 700;
                            text-transform: uppercase;
                            letter-spacing: 0.08em;
                            padding: 6px 14px 10px !important;
                        }

                        .annual-goals-page #goals-table tbody tr {
                            box-shadow: var(--shadow-soft);
                        }

                        .annual-goals-page #goals-table tbody td {
                            background: #fff !important;
                            border-top: 1px solid var(--line) !important;
                            border-bottom: 1px solid var(--line) !important;
                            border-left: none !important;
                            border-right: none !important;
                            padding: 18px 14px !important;
                            vertical-align: middle;
                            color: var(--text);
                        }

                        .annual-goals-page #goals-table tbody td:first-child {
                            border-left: 1px solid var(--line) !important;
                            border-top-left-radius: 16px;
                            border-bottom-left-radius: 16px;
                        }

                        .annual-goals-page #goals-table tbody td:last-child {
                            border-right: 1px solid var(--line) !important;
                            border-top-right-radius: 16px;
                            border-bottom-right-radius: 16px;
                        }

                        .annual-goals-page .year-badge {
                            display: inline-flex;
                            align-items: center;
                            gap: 6px;
                            padding: 6px 14px;
                            border-radius: 20px;
                            background: var(--purple-soft);
                            color: var(--purple);
                            font-weight: 800;
                            font-size: 0.9rem;
                        }

                        .annual-goals-page .target-clients {
                            color: var(--primary-2);
                            font-weight: 700;
                        }

                        .annual-goals-page .target-income {
                            color: var(--success);
                            font-weight: 700;
                            font-family: var(--font-primary, Montserrat, Segoe UI, Arial, sans-serif);
                        }

                        .annual-goals-page .action-btns {
                            display: flex;
                            gap: 8px;
                        }

                        .annual-goals-page .action-btn {
                            width: 32px;
                            height: 32px;
                            border-radius: 8px;
                            display: inline-flex;
                            align-items: center;
                            justify-content: center;
                            border: none;
                            cursor: pointer;
                            transition: all 0.2s ease;
                        }

                        .annual-goals-page .action-btn.view {
                            background: var(--primary-soft);
                            color: var(--primary-2);
                        }

                        .annual-goals-page .action-btn.view:hover {
                            background: var(--primary-2);
                            color: #fff;
                        }

                        .annual-goals-page .action-btn.delete {
                            background: var(--danger-soft);
                            color: var(--danger);
                        }

                        .annual-goals-page .action-btn.delete:hover {
                            background: var(--danger);
                            color: #fff;
                        }

                        .annual-goals-page .empty-state {
                            text-align: center;
                            padding: 60px 20px;
                        }

                        .annual-goals-page .empty-state-icon {
                            width: 80px;
                            height: 80px;
                            border-radius: 24px;
                            background: var(--purple-soft);
                            color: var(--purple);
                            display: flex;
                            align-items: center;
                            justify-content: center;
                            margin: 0 auto 20px;
                            font-size: 2rem;
                        }

                        @media (max-width: 767px) {
                            .annual-goals-page .ag-card-header,
                            .annual-goals-page .ag-card-body {
                                padding: 16px;
                            }
                        }
                    </style>

                    <div class="ag-header">
                        <div class="ag-title-wrap">
                            <div class="ag-eyebrow">Performance Tracking</div>
                            <h4 class="ag-title">Annual Goals</h4>
                            <div class="ag-subtitle">Set targets and track progress for clients and revenue</div>
                        </div>
                        <?php if ($isAdmin): ?>
                            <button type="button" class="ag-btn ag-btn-primary" data-toggle="modal" data-target="#addGoalModal">
                                <i class="mdi mdi-plus"></i> Set New Goal
                            </button>
                        <?php endif; ?>
                    </div>

                    <!-- Current Year Progress -->
                    <div class="ag-card">
                        <div class="ag-card-header">
                            <h5 class="ag-card-title"><i class="mdi mdi-chart-line mr-2"></i><?= $currentYear; ?> Progress</h5>
                            <a href="<?= base_url(); ?>Page/annualGoalsReport?year=<?= $currentYear; ?>" class="btn btn-sm btn-outline-primary">
                                <i class="mdi mdi-file-chart mr-1"></i> View Report
                            </a>
                        </div>
                        <div class="ag-card-body">
                            <?php if ($currentGoal): ?>
                                <?php
                                $clientProgress = $currentGoal->targetClients > 0 ? round(($currentProgress['actualClients'] / $currentGoal->targetClients) * 100, 1) : 0;
                                $incomeProgress = $currentGoal->targetIncome > 0 ? round(($currentProgress['actualIncome'] / $currentGoal->targetIncome) * 100, 1) : 0;
                                ?>
                                <div class="current-stats">
                                    <div class="stat-box <?= $clientProgress >= 100 ? 'success' : ($clientProgress >= 50 ? 'warning' : ''); ?>">
                                        <div class="stat-label">Target Clients</div>
                                        <div class="stat-value"><?= number_format($currentGoal->targetClients); ?></div>
                                        <div class="progress-section">
                                            <div class="progress-label">
                                                <span><?= number_format($currentProgress['actualClients']); ?> acquired</span>
                                                <span><?= $clientProgress; ?>%</span>
                                            </div>
                                            <div class="progress">
                                                <div class="progress-bar <?= $clientProgress >= 100 ? 'success' : ($clientProgress >= 50 ? 'warning' : ''); ?>" style="width: <?= min($clientProgress, 100); ?>"></div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="stat-box <?= $incomeProgress >= 100 ? 'success' : ($incomeProgress >= 50 ? 'warning' : ''); ?>">
                                        <div class="stat-label">Target Income</div>
                                        <div class="stat-value">PHP <?= number_format($currentGoal->targetIncome, 0); ?></div>
                                        <div class="progress-section">
                                            <div class="progress-label">
                                                <span>PHP <?= number_format($currentProgress['actualIncome'], 2); ?> collected</span>
                                                <span><?= $incomeProgress; ?>%</span>
                                            </div>
                                            <div class="progress">
                                                <div class="progress-bar <?= $incomeProgress >= 100 ? 'success' : ($incomeProgress >= 50 ? 'warning' : ''); ?>" style="width: <?= min($incomeProgress, 100); ?>"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <?php if ($currentGoal->notes): ?>
                                    <div class="alert alert-info" style="border-radius: 12px; border: none; background: var(--primary-soft); color: var(--primary-2);">
                                        <i class="mdi mdi-information-outline mr-2"></i><?= htmlspecialchars($currentGoal->notes, ENT_QUOTES, 'UTF-8'); ?>
                                    </div>
                                <?php endif; ?>
                            <?php else: ?>
                                <div class="empty-state">
                                    <div class="empty-state-icon">
                                        <i class="mdi mdi-target"></i>
                                    </div>
                                    <h5>No Goal Set for <?= $currentYear; ?></h5>
                                    <p class="text-muted">Set annual targets to start tracking your progress.</p>
                                    <?php if ($isAdmin): ?>
                                        <button type="button" class="ag-btn ag-btn-primary mt-3" data-toggle="modal" data-target="#addGoalModal">
                                            <i class="mdi mdi-plus"></i> Set Goal Now
                                        </button>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- All Goals Table -->
                    <div class="ag-card">
                        <div class="ag-card-header">
                            <h5 class="ag-card-title"><i class="mdi mdi-history mr-2"></i>All Annual Goals</h5>
                        </div>
                        <div class="ag-card-body">
                            <div class="table-responsive">
                                <table id="goals-table" class="table table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th>Year</th>
                                            <th>Target Clients</th>
                                            <th>Target Income</th>
                                            <th>Actual Clients</th>
                                            <th>Actual Income</th>
                                            <th>Client Progress</th>
                                            <th>Income Progress</th>
                                            <th class="text-center">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($goals)): ?>
                                            <?php foreach ($goals as $goal):
                                                $progress = $progressData[$goal->goalYear] ?? ['actualClients' => 0, 'actualIncome' => 0];
                                                $clientPct = $goal->targetClients > 0 ? round(($progress['actualClients'] / $goal->targetClients) * 100, 1) : 0;
                                                $incomePct = $goal->targetIncome > 0 ? round(($progress['actualIncome'] / $goal->targetIncome) * 100, 1) : 0;
                                            ?>
                                                <tr>
                                                    <td>
                                                        <span class="year-badge"><?= $goal->goalYear; ?></span>
                                                    </td>
                                                    <td class="target-clients"><?= number_format($goal->targetClients); ?></td>
                                                    <td class="target-income">PHP <?= number_format($goal->targetIncome, 2); ?></td>
                                                    <td><?= number_format($progress['actualClients']); ?></td>
                                                    <td>PHP <?= number_format($progress['actualIncome'], 2); ?></td>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <div class="progress flex-grow-1 mr-2" style="height: 6px;">
                                                                <div class="progress-bar <?= $clientPct >= 100 ? 'success' : ($clientPct >= 50 ? 'warning' : ''); ?>" style="width: <?= min($clientPct, 100); ?>"></div>
                                                            </div>
                                                            <small class="text-muted"><?= $clientPct; ?>%</small>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <div class="progress flex-grow-1 mr-2" style="height: 6px;">
                                                                <div class="progress-bar <?= $incomePct >= 100 ? 'success' : ($incomePct >= 50 ? 'warning' : ''); ?>" style="width: <?= min($incomePct, 100); ?>"></div>
                                                            </div>
                                                            <small class="text-muted"><?= $incomePct; ?>%</small>
                                                        </div>
                                                    </td>
                                                    <td class="text-center">
                                                        <div class="action-btns justify-content-center">
                                                            <a href="<?= base_url(); ?>Page/annualGoalsReport?year=<?= $goal->goalYear; ?>" class="action-btn view" title="View Report">
                                                                <i class="mdi mdi-file-chart"></i>
                                                            </a>
                                                            <?php if ($isAdmin): ?>
                                                                <form method="post" action="" class="d-inline" onsubmit="return confirm('Delete goal for <?= $goal->goalYear; ?>?');">
                                                                    <input type="hidden" name="goalID" value="<?= $goal->goalID; ?>">
                                                                    <button type="submit" name="deleteGoal" value="1" class="action-btn delete" title="Delete">
                                                                        <i class="mdi mdi-trash-can"></i>
                                                                    </button>
                                                                </form>
                                                            <?php endif; ?>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="8" class="text-center py-4 text-muted">No goals set yet.</td>
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

    <?php if ($isAdmin): ?>
        <!-- Add Goal Modal -->
        <div class="modal fade" id="addGoalModal" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content" style="border-radius: 22px; overflow: hidden; box-shadow: 0 28px 60px rgba(15, 23, 42, 0.18);">
                    <div class="modal-header" style="background: linear-gradient(135deg, #7c3aed, #a855f7); border: none; padding: 22px 24px;">
                        <h5 class="modal-title" style="color: #fff; font-size: 1.08rem; font-weight: 800;">
                            <i class="mdi mdi-target mr-2"></i>Set Annual Goal
                        </h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color: #fff; opacity: 1; background: rgba(255,255,255,0.14); border: 1px solid rgba(255,255,255,0.22); width: 38px; height: 38px; border-radius: 50%;">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body" style="background: linear-gradient(180deg, #fbfdff 0%, #f6f9fc 100%); padding: 24px;">
                        <form method="post" action="">
                            <div class="form-group">
                                <label style="font-weight: 700; color: #334155; font-size: 0.85rem;">Goal Year</label>
                                <select name="goalYear" class="form-control" required style="border-radius: 12px; border: 1px solid #e2e8f0; padding: 12px;">
                                    <?php for ($y = date('Y') - 2; $y <= date('Y') + 3; $y++): ?>
                                        <option value="<?= $y; ?>" <?= $y == date('Y') ? 'selected' : ''; ?>><?= $y; ?></option>
                                    <?php endfor; ?>
                                </select>
                            </div>

                            <div class="form-group" style="margin-top: 16px;">
                                <label style="font-weight: 700; color: #334155; font-size: 0.85rem;">Target Number of Clients</label>
                                <input type="number" name="targetClients" class="form-control" required min="0" placeholder="e.g. 100" style="border-radius: 12px; border: 1px solid #e2e8f0; padding: 12px;">
                            </div>

                            <div class="form-group" style="margin-top: 16px;">
                                <label style="font-weight: 700; color: #334155; font-size: 0.85rem;">Target Annual Income (PHP)</label>
                                <input type="number" name="targetIncome" class="form-control" required min="0" step="0.01" placeholder="e.g. 1000000" style="border-radius: 12px; border: 1px solid #e2e8f0; padding: 12px;">
                            </div>

                            <div class="form-group" style="margin-top: 16px;">
                                <label style="font-weight: 700; color: #334155; font-size: 0.85rem;">Notes (Optional)</label>
                                <textarea name="notes" class="form-control" rows="2" placeholder="Any additional notes..." style="border-radius: 12px; border: 1px solid #e2e8f0; padding: 12px;"></textarea>
                            </div>

                            <div class="text-right" style="margin-top: 24px; padding-top: 20px; border-top: 1px solid #e2e8f0;">
                                <button type="button" class="btn" data-dismiss="modal" style="background: #fff; color: #334155; border: 1px solid #e2e8f0; border-radius: 10px; padding: 10px 20px; font-weight: 600; margin-right: 10px;">
                                    Cancel
                                </button>
                                <button type="submit" name="saveGoal" value="1" class="btn" style="background: linear-gradient(135deg, #7c3aed, #a855f7); color: #fff; border: none; border-radius: 10px; padding: 10px 24px; font-weight: 700; box-shadow: 0 4px 14px rgba(124, 58, 237, 0.3);">
                                    <i class="mdi mdi-content-save mr-1"></i>Save Goal
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <script src="<?= base_url(); ?>assets/js/vendor.min.js"></script>
    <script src="<?= base_url(); ?>assets/js/app.min.js"></script>
    <script src="<?= base_url(); ?>assets/libs/datatables/jquery.dataTables.min.js"></script>
    <script src="<?= base_url(); ?>assets/libs/datatables/dataTables.bootstrap4.min.js"></script>
    <script>
        (function($) {
            'use strict';
            $(function() {
                if ($.fn.DataTable) {
                    $('#goals-table').DataTable({
                        responsive: true,
                        autoWidth: false,
                        order: [[0, 'desc']],
                        language: {
                            emptyTable: 'No goals set yet.'
                        }
                    });
                }
            });
        })(jQuery);
    </script>

</body>

</html>
