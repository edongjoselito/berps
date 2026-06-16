<!DOCTYPE html>
<html lang="en">

<?php include('includes/head.php'); ?>

<body>

    <div id="wrapper">

        <?php include('includes/top-nav-bar.php'); ?>
        <?php include('includes/sidebar.php'); ?>

        <div class="content-page">
            <div class="content">
                <div class="container-fluid annual-goals-report-page">

                    <style>
                        .annual-goals-report-page {
                            --bg: #f5f7fb;
                            --surface: rgba(255, 255, 255, 0.96);
                            --line: #e4ebf4;
                            --text: #142235;
                            --text-soft: #617489;
                            --text-faint: #8ea0b5;
                            --primary: #2563eb;
                            --success: #059669;
                            --success-soft: #ecfdf5;
                            --warning: #d97706;
                            --danger: #e11d48;
                            --purple: #7c3aed;
                            --purple-soft: #f5f3ff;
                            --shadow: 0 20px 50px rgba(15, 23, 42, 0.08);
                            font-family: 'Inter', 'Poppins', 'Segoe UI', Arial, sans-serif;
                            background: radial-gradient(circle at top left, rgba(37, 99, 235, 0.08), transparent 28%),
                                radial-gradient(circle at top right, rgba(124, 58, 237, 0.08), transparent 24%),
                                linear-gradient(180deg, #f8fbff 0%, #f4f7fb 100%);
                            min-height: 100vh;
                            padding-bottom: 28px;
                        }

                        .agr-header {
                            margin: 24px 0 18px;
                            display: flex;
                            justify-content: space-between;
                            align-items: flex-end;
                            flex-wrap: wrap;
                            gap: 16px;
                        }

                        .agr-title {
                            margin: 0;
                            font-size: 2rem;
                            font-weight: 800;
                            letter-spacing: -0.04em;
                            color: var(--text);
                        }

                        .agr-subtitle {
                            color: var(--text-soft);
                            font-size: 0.95rem;
                            margin-top: 6px;
                        }

                        .agr-card {
                            background: var(--surface);
                            border: 1px solid rgba(255, 255, 255, 0.72);
                            border-radius: 24px;
                            box-shadow: var(--shadow);
                            margin-bottom: 24px;
                            overflow: hidden;
                        }

                        .agr-card-header {
                            padding: 20px 24px;
                            border-bottom: 1px solid var(--line);
                            background: linear-gradient(180deg, rgba(255,255,255,0.96), rgba(249,251,255,0.96));
                        }

                        .agr-card-title {
                            margin: 0;
                            font-size: 1.1rem;
                            font-weight: 800;
                        }

                        .agr-card-body {
                            padding: 24px;
                        }

                        .summary-grid {
                            display: grid;
                            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
                            gap: 20px;
                            margin-bottom: 30px;
                        }

                        .summary-box {
                            background: linear-gradient(135deg, #fff 0%, #f8fafc 100%);
                            border: 1px solid var(--line);
                            border-radius: 18px;
                            padding: 24px;
                            text-align: center;
                        }

                        .summary-box.target {
                            border-top: 4px solid var(--purple);
                        }

                        .summary-box.actual {
                            border-top: 4px solid var(--success);
                        }

                        .summary-box.remaining {
                            border-top: 4px solid var(--warning);
                        }

                        .summary-label {
                            font-size: 0.8rem;
                            font-weight: 700;
                            color: var(--text-faint);
                            text-transform: uppercase;
                            letter-spacing: 0.06em;
                            margin-bottom: 8px;
                        }

                        .summary-value {
                            font-size: 1.8rem;
                            font-weight: 800;
                            color: var(--text);
                        }

                        .progress-ring {
                            width: 120px;
                            height: 120px;
                            margin: 20px auto;
                            position: relative;
                        }

                        .progress-ring svg {
                            transform: rotate(-90deg);
                        }

                        .progress-ring-bg {
                            fill: none;
                            stroke: #e2e8f0;
                            stroke-width: 8;
                        }

                        .progress-ring-fill {
                            fill: none;
                            stroke: var(--purple);
                            stroke-width: 8;
                            stroke-linecap: round;
                            transition: stroke-dashoffset 0.5s ease;
                        }

                        .progress-ring-fill.success {
                            stroke: var(--success);
                        }

                        .progress-ring-fill.warning {
                            stroke: var(--warning);
                        }

                        .progress-text {
                            position: absolute;
                            top: 50%;
                            left: 50%;
                            transform: translate(-50%, -50%);
                            font-size: 1.5rem;
                            font-weight: 800;
                            color: var(--text);
                        }

                        .month-grid {
                            display: grid;
                            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
                            gap: 16px;
                        }

                        .month-card {
                            background: #fff;
                            border: 1px solid var(--line);
                            border-radius: 16px;
                            padding: 20px;
                        }

                        .month-name {
                            font-weight: 700;
                            font-size: 1rem;
                            color: var(--text);
                            margin-bottom: 16px;
                            padding-bottom: 12px;
                            border-bottom: 1px solid var(--line);
                        }

                        .month-stat {
                            display: flex;
                            justify-content: space-between;
                            align-items: center;
                            margin-bottom: 10px;
                            font-size: 0.9rem;
                        }

                        .month-stat-label {
                            color: var(--text-soft);
                        }

                        .month-stat-value {
                            font-weight: 700;
                            font-family: monospace;
                        }

                        .no-goal-alert {
                            background: var(--warning-soft);
                            border: none;
                            border-radius: 16px;
                            padding: 40px;
                            text-align: center;
                        }
                    </style>

                    <div class="agr-header">
                        <div>
                            <h4 class="agr-title"><i class="mdi mdi-file-chart mr-2 text-primary"></i>Annual Goals Report</h4>
                            <div class="agr-subtitle">Detailed breakdown for year <?= $year; ?></div>
                        </div>
                        <div>
                            <a href="<?= base_url(); ?>Page/annualGoals" class="btn btn-outline-secondary">
                                <i class="mdi mdi-arrow-left mr-1"></i>Back to Goals
                            </a>
                        </div>
                    </div>

                    <?php if ($goal): ?>
                        <?php
                        $clientPct = $goal->targetClients > 0 ? min(100, round(($progress['actualClients'] / $goal->targetClients) * 100, 1)) : 0;
                        $incomePct = $goal->targetIncome > 0 ? min(100, round(($progress['actualIncome'] / $goal->targetIncome) * 100, 1)) : 0;
                        $clientRemaining = max(0, $goal->targetClients - $progress['actualClients']);
                        $incomeRemaining = max(0, $goal->targetIncome - $progress['actualIncome']);
                        ?>

                        <!-- Summary Cards -->
                        <div class="agr-card">
                            <div class="agr-card-header">
                                <h5 class="agr-card-title"><?= $year; ?> Performance Summary</h5>
                            </div>
                            <div class="agr-card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <h6 class="text-center mb-3" style="color: var(--purple); font-weight: 700;">Clients Target</h6>
                                        <div class="summary-grid">
                                            <div class="summary-box target">
                                                <div class="summary-label">Target</div>
                                                <div class="summary-value"><?= number_format($goal->targetClients); ?></div>
                                            </div>
                                            <div class="summary-box actual">
                                                <div class="summary-label">Actual</div>
                                                <div class="summary-value" style="color: var(--success);"><?= number_format($progress['actualClients']); ?></div>
                                            </div>
                                            <div class="summary-box remaining">
                                                <div class="summary-label">Remaining</div>
                                                <div class="summary-value" style="color: var(--warning);"><?= number_format($clientRemaining); ?></div>
                                            </div>
                                        </div>
                                        <div class="text-center">
                                            <div class="progress-ring">
                                                <svg width="120" height="120">
                                                    <circle class="progress-ring-bg" cx="60" cy="60" r="52"></circle>
                                                    <circle class="progress-ring-fill <?= $clientPct >= 100 ? 'success' : ($clientPct >= 50 ? 'warning' : ''); ?>" cx="60" cy="60" r="52"
                                                        stroke-dasharray="327"
                                                        stroke-dashoffset="<?= 327 - (327 * $clientPct / 100); ?>"></circle>
                                                </svg>
                                                <div class="progress-text"><?= $clientPct; ?>%</div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <h6 class="text-center mb-3" style="color: var(--purple); font-weight: 700;">Income Target</h6>
                                        <div class="summary-grid">
                                            <div class="summary-box target">
                                                <div class="summary-label">Target</div>
                                                <div class="summary-value">PHP <?= number_format($goal->targetIncome, 0); ?></div>
                                            </div>
                                            <div class="summary-box actual">
                                                <div class="summary-label">Actual</div>
                                                <div class="summary-value" style="color: var(--success);">PHP <?= number_format($progress['actualIncome'], 0); ?></div>
                                            </div>
                                            <div class="summary-box remaining">
                                                <div class="summary-label">Remaining</div>
                                                <div class="summary-value" style="color: var(--warning);">PHP <?= number_format($incomeRemaining, 0); ?></div>
                                            </div>
                                        </div>
                                        <div class="text-center">
                                            <div class="progress-ring">
                                                <svg width="120" height="120">
                                                    <circle class="progress-ring-bg" cx="60" cy="60" r="52"></circle>
                                                    <circle class="progress-ring-fill <?= $incomePct >= 100 ? 'success' : ($incomePct >= 50 ? 'warning' : ''); ?>" cx="60" cy="60" r="52"
                                                        stroke-dasharray="327"
                                                        stroke-dashoffset="<?= 327 - (327 * $incomePct / 100); ?>"></circle>
                                                </svg>
                                                <div class="progress-text"><?= $incomePct; ?>%</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <?php if ($goal->notes): ?>
                                    <div class="alert mt-4" style="background: var(--purple-soft); color: var(--purple); border: none; border-radius: 12px;">
                                        <i class="mdi mdi-note-text mr-2"></i><strong>Notes:</strong> <?= htmlspecialchars($goal->notes, ENT_QUOTES, 'UTF-8'); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Monthly Breakdown -->
                        <div class="agr-card">
                            <div class="agr-card-header">
                                <h5 class="agr-card-title">Monthly Breakdown</h5>
                            </div>
                            <div class="agr-card-body">
                                <div class="month-grid">
                                    <?php
                                    $months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
                                    foreach ($months as $index => $monthName):
                                        $m = $index + 1;
                                        $data = $monthlyData[$m];
                                    ?>
                                        <div class="month-card">
                                            <div class="month-name"><?= $monthName; ?></div>
                                            <div class="month-stat">
                                                <span class="month-stat-label"><i class="mdi mdi-account-plus mr-1"></i>New Clients</span>
                                                <span class="month-stat-value"><?= number_format($data['clients']); ?></span>
                                            </div>
                                            <div class="month-stat">
                                                <span class="month-stat-label"><i class="mdi mdi-currency-php mr-1"></i>Income</span>
                                                <span class="month-stat-value" style="color: var(--success);">PHP <?= number_format($data['income'], 0); ?></span>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>

                    <?php else: ?>
                        <div class="no-goal-alert">
                            <i class="mdi mdi-alert-circle-outline" style="font-size: 3rem; color: var(--warning); margin-bottom: 16px;"></i>
                            <h4>No Goal Set for <?= $year; ?></h4>
                            <p class="text-muted">There is no target goal configured for this year.</p>
                            <?php if ($isAdmin): ?>
                                <a href="<?= base_url(); ?>Page/annualGoals" class="btn btn-primary mt-3">
                                    <i class="mdi mdi-plus mr-1"></i>Set Goal Now
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                </div>
            </div>

            <?php include('includes/footer.php'); ?>
        </div>
    </div>

    <?php include('includes/themecustomizer.php'); ?>

    <script src="<?= base_url(); ?>assets/js/vendor.min.js"></script>
    <script src="<?= base_url(); ?>assets/js/app.min.js"></script>

</body>

</html>
