<!DOCTYPE html>
<html lang="en">

<?php include('includes/head.php'); ?>

<body>
    <div id="wrapper">

        <?php include('includes/top-nav-bar.php'); ?>
        <?php include('includes/sidebar.php'); ?>

        <div class="content-page">
            <div class="content">
                <div class="container-fluid ranking-page">
                    <style>
                        @import url('https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,100..1000;1,9..40,100..1000&display=swap');

                        .ranking-page {
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
                            --info: #0891b2;
                            --info-soft: #ecfeff;
                            --purple: #7c3aed;
                            --purple-soft: #f5f3ff;
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
                            padding-bottom: 80px;
                            font-family: var(--font-body);
                        }

                        .ranking-page * {
                            box-sizing: border-box;
                        }

                        /* Page Header */
                        .ranking-page .page-header {
                            display: flex;
                            justify-content: space-between;
                            align-items: flex-end;
                            gap: 18px;
                            margin: 24px 0 22px;
                            flex-wrap: wrap;
                        }

                        .ranking-page .page-eyebrow {
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

                        .ranking-page .page-eyebrow::before {
                            content: '';
                            width: 8px;
                            height: 8px;
                            border-radius: 50%;
                            background: linear-gradient(135deg, var(--primary), var(--primary-2));
                            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.10);
                        }

                        .ranking-page .page-title {
                            margin: 0;
                            font-family: var(--font-head);
                            font-size: 2.15rem;
                            line-height: 1.05;
                            letter-spacing: -0.05em;
                            font-weight: 800;
                            color: var(--text);
                        }

                        .ranking-page .page-subtitle {
                            margin-top: 8px;
                            color: var(--text-soft);
                            font-size: 0.96rem;
                        }

                        .ranking-page .page-actions {
                            display: flex;
                            gap: 12px;
                            flex-wrap: wrap;
                        }

                        .ranking-page .btn-action,
                        .ranking-page .btn-submit {
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
                            border: none;
                            cursor: pointer;
                        }

                        .ranking-page .btn-action {
                            border: 1px solid var(--line-strong);
                            color: var(--text);
                            background: #fff;
                        }

                        .ranking-page .btn-action:hover {
                            color: var(--primary);
                            border-color: #bfd3ef;
                            background: #f9fbff;
                        }

                        .ranking-page .btn-submit {
                            color: #fff;
                            background: linear-gradient(135deg, var(--primary), var(--primary-2));
                            box-shadow: 0 10px 24px rgba(37, 99, 235, 0.24);
                        }

                        .ranking-page .btn-submit:hover {
                            transform: translateY(-1px);
                            box-shadow: 0 14px 28px rgba(37, 99, 235, 0.28);
                            color: #fff;
                        }

                        /* Top 3 Leaders Podium */
                        .ranking-page .podium-section {
                            margin-bottom: 28px;
                        }

                        .ranking-page .podium-grid {
                            display: grid;
                            grid-template-columns: repeat(3, 1fr);
                            gap: 16px;
                            align-items: end;
                        }

                        .ranking-page .podium-card {
                            position: relative;
                            background: var(--surface);
                            border: 1px solid rgba(255, 255, 255, 0.72);
                            border-radius: var(--radius-xl);
                            box-shadow: var(--shadow);
                            padding: 20px;
                            text-align: center;
                            transition: transform 0.2s ease, box-shadow 0.2s ease;
                        }

                        .ranking-page .podium-card:hover {
                            transform: translateY(-4px);
                            box-shadow: 0 20px 48px rgba(15, 23, 42, 0.10);
                        }

                        .ranking-page .podium-card.first {
                            background: linear-gradient(135deg, rgba(245, 158, 11, 0.08), rgba(245, 158, 11, 0.02));
                            border-color: rgba(245, 158, 11, 0.2);
                            padding: 28px 20px;
                        }

                        .ranking-page .podium-card.second {
                            background: linear-gradient(135deg, rgba(148, 163, 184, 0.08), rgba(148, 163, 184, 0.02));
                            border-color: rgba(148, 163, 184, 0.2);
                        }

                        .ranking-page .podium-card.third {
                            background: linear-gradient(135deg, rgba(180, 83, 9, 0.08), rgba(180, 83, 9, 0.02));
                            border-color: rgba(180, 83, 9, 0.2);
                        }

                        .ranking-page .podium-rank {
                            position: absolute;
                            top: -12px;
                            left: 50%;
                            transform: translateX(-50%);
                            width: 40px;
                            height: 40px;
                            border-radius: 50%;
                            display: flex;
                            align-items: center;
                            justify-content: center;
                            font-weight: 800;
                            font-size: 1.1rem;
                            color: #fff;
                            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
                        }

                        .ranking-page .podium-card.first .podium-rank {
                            background: linear-gradient(135deg, #f59e0b, #fbbf24);
                        }

                        .ranking-page .podium-card.second .podium-rank {
                            background: linear-gradient(135deg, #94a3b8, #cbd5e1);
                        }

                        .ranking-page .podium-card.third .podium-rank {
                            background: linear-gradient(135deg, #b45309, #d97706);
                        }

                        .ranking-page .podium-avatar {
                            width: 64px;
                            height: 64px;
                            border-radius: 50%;
                            background: linear-gradient(135deg, var(--primary), var(--primary-2));
                            color: #fff;
                            display: inline-flex;
                            align-items: center;
                            justify-content: center;
                            font-weight: 700;
                            font-size: 1.4rem;
                            margin: 16px 0 12px;
                            box-shadow: 0 8px 20px rgba(37, 99, 235, 0.25);
                        }

                        .ranking-page .podium-card.first .podium-avatar {
                            width: 72px;
                            height: 72px;
                            font-size: 1.6rem;
                            box-shadow: 0 10px 28px rgba(245, 158, 11, 0.35);
                        }

                        .ranking-page .podium-name {
                            font-weight: 700;
                            color: var(--text);
                            font-size: 1.05rem;
                            margin-bottom: 4px;
                            white-space: nowrap;
                            overflow: hidden;
                            text-overflow: ellipsis;
                        }

                        .ranking-page .podium-points {
                            font-size: 1.4rem;
                            font-weight: 800;
                            color: var(--success);
                            font-family: var(--font-mono);
                        }

                        .ranking-page .podium-card.first .podium-points {
                            font-size: 1.6rem;
                        }

                        .ranking-page .podium-meta {
                            font-size: 0.8rem;
                            color: var(--text-faint);
                            margin-top: 8px;
                        }

                        /* Stats Bar */
                        .ranking-page .stats-bar {
                            display: grid;
                            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
                            gap: 12px;
                            margin-bottom: 20px;
                        }

                        .ranking-page .stat-pill {
                            display: flex;
                            align-items: center;
                            gap: 10px;
                            padding: 12px 16px;
                            background: var(--surface);
                            border: 1px solid rgba(255, 255, 255, 0.72);
                            border-radius: var(--radius-lg);
                            box-shadow: var(--shadow-soft);
                        }

                        .ranking-page .stat-pill-icon {
                            width: 36px;
                            height: 36px;
                            border-radius: 10px;
                            background: var(--primary-soft);
                            color: var(--primary-2);
                            display: inline-flex;
                            align-items: center;
                            justify-content: center;
                            font-size: 1.1rem;
                        }

                        .ranking-page .stat-pill-content {
                            flex: 1;
                        }

                        .ranking-page .stat-pill-label {
                            font-size: 0.7rem;
                            font-weight: 700;
                            text-transform: uppercase;
                            letter-spacing: 0.08em;
                            color: var(--text-faint);
                        }

                        .ranking-page .stat-pill-value {
                            font-size: 1.1rem;
                            font-weight: 800;
                            color: var(--text);
                            font-family: var(--font-mono);
                        }

                        /* Theme Card & Table */
                        .ranking-page .theme-card {
                            background: var(--surface);
                            border: 1px solid rgba(255, 255, 255, 0.72);
                            border-radius: var(--radius-xl);
                            box-shadow: var(--shadow);
                            overflow: hidden;
                        }

                        .ranking-page .theme-card-head {
                            padding: 18px 22px;
                            border-bottom: 1px solid var(--line);
                            background: linear-gradient(180deg, rgba(255, 255, 255, 0.98), rgba(249, 251, 255, 0.94));
                            display: flex;
                            justify-content: space-between;
                            align-items: center;
                            flex-wrap: wrap;
                            gap: 12px;
                        }

                        .ranking-page .theme-card-title {
                            margin: 0;
                            color: var(--text);
                            font-size: 1.02rem;
                            font-weight: 800;
                            letter-spacing: -0.02em;
                        }

                        .ranking-page .theme-card-body {
                            padding: 0;
                        }

                        .ranking-page .ranking-table {
                            width: 100%;
                            border-collapse: collapse;
                        }

                        .ranking-page .ranking-table thead th {
                            border-top: none;
                            border-bottom: 1px solid var(--line);
                            color: var(--text-faint);
                            font-size: 0.72rem;
                            font-weight: 800;
                            letter-spacing: 0.08em;
                            text-transform: uppercase;
                            white-space: nowrap;
                            padding: 14px 18px;
                            background: var(--surface-soft);
                        }

                        .ranking-page .ranking-table td {
                            padding: 14px 18px;
                            border-bottom: 1px solid var(--line);
                            vertical-align: middle;
                            color: var(--text);
                        }

                        .ranking-page .ranking-table tbody tr:last-child td {
                            border-bottom: none;
                        }

                        .ranking-page .ranking-table tbody tr:hover {
                            background: var(--surface-soft);
                        }

                        .ranking-page .rank-cell {
                            display: inline-flex;
                            align-items: center;
                            justify-content: center;
                            min-width: 36px;
                            height: 36px;
                            border-radius: 999px;
                            background: var(--primary-soft);
                            color: var(--primary-2);
                            font-weight: 800;
                            font-size: 0.9rem;
                        }

                        .ranking-page .rank-cell.top-3 {
                            background: linear-gradient(135deg, #f59e0b, #fbbf24);
                            color: #fff;
                        }

                        .ranking-page .employee-cell {
                            display: flex;
                            align-items: center;
                            gap: 12px;
                        }

                        .ranking-page .employee-avatar {
                            width: 40px;
                            height: 40px;
                            border-radius: 10px;
                            background: linear-gradient(135deg, var(--primary), var(--primary-2));
                            color: #fff;
                            display: inline-flex;
                            align-items: center;
                            justify-content: center;
                            font-weight: 700;
                            font-size: 0.9rem;
                            flex-shrink: 0;
                        }

                        .ranking-page .employee-info {
                            flex: 1;
                            min-width: 0;
                        }

                        .ranking-page .employee-name {
                            font-weight: 700;
                            color: var(--text);
                            font-size: 0.95rem;
                            white-space: nowrap;
                            overflow: hidden;
                            text-overflow: ellipsis;
                        }

                        .ranking-page .employee-role {
                            font-size: 0.78rem;
                            color: var(--text-soft);
                            margin-top: 2px;
                        }

                        .ranking-page .points-cell {
                            font-size: 1.15rem;
                            font-weight: 800;
                            color: var(--success);
                            font-family: var(--font-mono);
                        }

                        .ranking-page .date-cell {
                            font-size: 0.86rem;
                            color: var(--text-soft);
                        }

                        .ranking-page .empty-state {
                            padding: 60px 22px;
                            text-align: center;
                        }

                        .ranking-page .empty-state-icon {
                            font-size: 3.5rem;
                            color: var(--line-strong);
                            margin-bottom: 16px;
                        }

                        .ranking-page .empty-state-title {
                            color: var(--text);
                            font-size: 1.1rem;
                            font-weight: 700;
                            margin-bottom: 6px;
                        }

                        .ranking-page .empty-state-text {
                            color: var(--text-soft);
                            font-size: 0.9rem;
                        }

                        /* Modal */
                        .ranking-page .modal-content {
                            border: none;
                            border-radius: var(--radius-xl);
                            box-shadow: var(--shadow);
                        }

                        .ranking-page .modal-header {
                            border-bottom: 1px solid var(--line);
                            background: linear-gradient(180deg, rgba(255,255,255,0.98), rgba(249,251,255,0.94));
                            border-radius: var(--radius-xl) var(--radius-xl) 0 0;
                            padding: 18px 22px;
                        }

                        .ranking-page .modal-title {
                            font-weight: 700;
                            color: var(--text);
                        }

                        .ranking-page .modal-body {
                            padding: 20px 22px;
                        }

                        .ranking-page .modal-footer {
                            border-top: 1px solid var(--line);
                            padding: 16px 22px;
                        }

                        .ranking-page .form-control {
                            border: 1px solid var(--line);
                            border-radius: var(--radius-md);
                            padding: 10px 14px;
                            font-size: 0.92rem;
                            color: var(--text);
                            background: #fff;
                        }

                        .ranking-page .form-control:focus {
                            border-color: var(--primary);
                            box-shadow: 0 0 0 3px var(--primary-soft);
                            outline: none;
                        }

                        .ranking-page .form-label {
                            font-size: 0.74rem;
                            font-weight: 700;
                            text-transform: uppercase;
                            letter-spacing: 0.08em;
                            color: var(--text-faint);
                            margin-bottom: 8px;
                        }

                        /* Responsive */
                        @media (max-width: 767px) {
                            .ranking-page .page-title {
                                font-size: 1.75rem;
                            }

                            .ranking-page .podium-grid {
                                grid-template-columns: 1fr;
                                gap: 20px;
                            }

                            .ranking-page .podium-card {
                                padding: 16px;
                            }

                            .ranking-page .podium-card.first {
                                order: -1;
                                padding: 20px 16px;
                            }

                            .ranking-page .stats-bar {
                                grid-template-columns: repeat(2, 1fr);
                            }

                            .ranking-page .ranking-table thead th,
                            .ranking-page .ranking-table td {
                                padding: 12px 14px;
                            }

                            .ranking-page .employee-avatar {
                                width: 36px;
                                height: 36px;
                                font-size: 0.8rem;
                            }
                        }
                    </style>

                    <?php
                    $yearValue = (!empty($selected_year) && $selected_year !== 'all') ? (int) $selected_year : null;
                    $monthValue = !empty($selected_month) ? (int) $selected_month : null;

                    if (!$yearValue) {
                        $periodLabel = 'All time';
                    } elseif ($monthValue) {
                        $monthNames = [
                            1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
                            5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
                            9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December'
                        ];
                        $periodLabel = $monthNames[$monthValue] . ' ' . $yearValue;
                    } else {
                        $periodLabel = (string) $yearValue;
                    }

                    // Calculate stats
                    $totalEmployees = count($ranking);
                    $totalPoints = 0;
                    $topPerformer = null;
                    if (!empty($ranking)) {
                        foreach ($ranking as $row) {
                            $totalPoints += (int) ($row->accomplished_count ?? 0);
                        }
                        $topPerformer = $ranking[0];
                    }
                    ?>

                    <!-- Page Header -->
                    <div class="page-header">
                        <div>
                            <div class="page-eyebrow">Task Performance</div>
                            <h4 class="page-title">Employee Ranking</h4>
                            <div class="page-subtitle">Top performers by task points — <?= htmlspecialchars($periodLabel, ENT_QUOTES, 'UTF-8'); ?></div>
                        </div>
                        <div class="page-actions">
                            <button type="button" class="btn-action" data-toggle="modal" data-target="#filterRankingModal">
                                <i class="mdi mdi-filter-variant"></i>
                                Filter Period
                            </button>
                        </div>
                    </div>

                    <?php if (!empty($ranking)): ?>
                        <!-- Top 3 Podium -->
                        <div class="podium-section">
                            <div class="podium-grid">
                                <?php
                                $positions = ['second', 'first', 'third'];
                                $displayOrder = [1, 0, 2]; // 2nd, 1st, 3rd visually
                                foreach ($displayOrder as $displayIndex):
                                    if (!isset($ranking[$displayIndex])) continue;
                                    $row = $ranking[$displayIndex];
                                    $position = $positions[array_search($displayIndex, $displayOrder)];
                                    $actualRank = $displayIndex + 1;
                                    $name = trim($row->name ?? '');
                                    $initials = strtoupper(substr($name, 0, 1) . (strpos($name, ' ') !== false ? substr($name, strpos($name, ' ') + 1, 1) : ''));
                                    if (strlen($initials) < 2) $initials = strtoupper(substr($name, 0, min(2, strlen($name))));
                                    $points = (int) ($row->accomplished_count ?? 0);
                                    $lastDone = !empty($row->last_accomplished) ? date('M d, Y', strtotime($row->last_accomplished)) : '—';
                                ?>
                                    <div class="podium-card <?= $position; ?>">
                                        <div class="podium-rank"><?= $actualRank; ?></div>
                                        <div class="podium-avatar"><?= htmlspecialchars($initials, ENT_QUOTES, 'UTF-8'); ?></div>
                                        <div class="podium-name"><?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8'); ?></div>
                                        <div class="podium-points"><?= number_format($points); ?> pts</div>
                                        <div class="podium-meta">Last: <?= htmlspecialchars($lastDone, ENT_QUOTES, 'UTF-8'); ?></div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <!-- Stats Bar -->
                        <div class="stats-bar">
                            <div class="stat-pill">
                                <div class="stat-pill-icon">
                                    <i class="mdi mdi-account-group"></i>
                                </div>
                                <div class="stat-pill-content">
                                    <div class="stat-pill-label">Total Employees</div>
                                    <div class="stat-pill-value"><?= number_format($totalEmployees); ?></div>
                                </div>
                            </div>
                            <div class="stat-pill">
                                <div class="stat-pill-icon" style="background: var(--success-soft); color: var(--success);">
                                    <i class="mdi mdi-trophy"></i>
                                </div>
                                <div class="stat-pill-content">
                                    <div class="stat-pill-label">Total Points</div>
                                    <div class="stat-pill-value"><?= number_format($totalPoints); ?></div>
                                </div>
                            </div>
                            <div class="stat-pill">
                                <div class="stat-pill-icon" style="background: var(--warning-soft); color: var(--warning);">
                                    <i class="mdi mdi-star"></i>
                                </div>
                                <div class="stat-pill-content">
                                    <div class="stat-pill-label">Top Performer</div>
                                    <div class="stat-pill-value" style="font-size: 0.9rem;"><?= htmlspecialchars(trim($topPerformer->name ?? '—'), ENT_QUOTES, 'UTF-8'); ?></div>
                                </div>
                            </div>
                            <div class="stat-pill">
                                <div class="stat-pill-icon" style="background: var(--info-soft); color: var(--info);">
                                    <i class="mdi mdi-calendar"></i>
                                </div>
                                <div class="stat-pill-content">
                                    <div class="stat-pill-label">Period</div>
                                    <div class="stat-pill-value" style="font-size: 0.9rem;"><?= htmlspecialchars($periodLabel, ENT_QUOTES, 'UTF-8'); ?></div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Full Ranking Table -->
                    <div class="theme-card">
                        <div class="theme-card-head">
                            <h5 class="theme-card-title">Complete Rankings</h5>
                            <button type="button" class="btn-action" data-toggle="modal" data-target="#filterRankingModal" style="padding: 8px 14px; font-size: 0.85rem;">
                                <i class="mdi mdi-filter-variant"></i>
                                Adjust Filter
                            </button>
                        </div>
                        <div class="theme-card-body">
                            <?php if (!empty($ranking)): ?>
                                <table class="ranking-table">
                                    <thead>
                                        <tr>
                                            <th style="width: 80px;">Rank</th>
                                            <th>Employee</th>
                                            <th style="width: 140px; text-align: center;">Points</th>
                                            <th style="width: 160px;">Last Completed</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php $rank = 1; ?>
                                        <?php foreach ($ranking as $row): ?>
                                            <?php
                                            $name = trim($row->name ?? '');
                                            $role = trim($row->role ?? '');
                                            $initials = strtoupper(substr($name, 0, 1) . (strpos($name, ' ') !== false ? substr($name, strpos($name, ' ') + 1, 1) : ''));
                                            if (strlen($initials) < 2) $initials = strtoupper(substr($name, 0, min(2, strlen($name))));
                                            $points = (int) ($row->accomplished_count ?? 0);
                                            $lastDoneRaw = !empty($row->last_accomplished) ? $row->last_accomplished : null;
                                            $lastDone = $lastDoneRaw ? date('M d, Y', strtotime($lastDoneRaw)) : '—';
                                            $isTop3 = $rank <= 3;
                                            ?>
                                            <tr>
                                                <td>
                                                    <span class="rank-cell <?= $isTop3 ? 'top-3' : ''; ?>"><?= $rank; ?></span>
                                                </td>
                                                <td>
                                                    <div class="employee-cell">
                                                        <div class="employee-avatar"><?= htmlspecialchars($initials, ENT_QUOTES, 'UTF-8'); ?></div>
                                                        <div class="employee-info">
                                                            <div class="employee-name"><?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8'); ?></div>
                                                            <?php if ($role): ?>
                                                                <div class="employee-role"><?= htmlspecialchars($role, ENT_QUOTES, 'UTF-8'); ?></div>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="points-cell" style="text-align: center;"><?= number_format($points); ?></td>
                                                <td class="date-cell"><?= htmlspecialchars($lastDone, ENT_QUOTES, 'UTF-8'); ?></td>
                                            </tr>
                                            <?php $rank++; ?>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            <?php else: ?>
                                <div class="empty-state">
                                    <div class="empty-state-icon"><i class="mdi mdi-trophy-outline"></i></div>
                                    <div class="empty-state-title">No Rankings Available</div>
                                    <div class="empty-state-text">No task points have been recorded for <?= htmlspecialchars($periodLabel, ENT_QUOTES, 'UTF-8'); ?>.<br>Adjust the filter to see data for a different period.</div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                </div>
            </div>

            <?php include('includes/footer.php'); ?>
        </div>

    </div>

    <?php include('includes/themecustomizer.php'); ?>

    <script src="<?= base_url(); ?>assets/js/vendor.min.js"></script>
    <script src="<?= base_url(); ?>assets/js/app.min.js"></script>

    <div class="modal fade" id="filterRankingModal" tabindex="-1" role="dialog" aria-labelledby="filterRankingModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content" style="border: none; border-radius: 16px; box-shadow: 0 16px 40px rgba(15, 23, 42, 0.12);">
                <form method="post">
                    <div class="modal-header" style="border-bottom: 1px solid #e4ebf4; background: linear-gradient(180deg, rgba(255,255,255,0.98), rgba(249,251,255,0.94)); border-radius: 16px 16px 0 0; padding: 18px 22px;">
                        <h5 class="modal-title" style="font-weight: 700; color: #142235; margin: 0;">Filter Period</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color: #8ea0b5;">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body" style="padding: 22px;">
                        <div class="row">
                            <div class="col-md-6">
                                <label style="font-size: 0.74rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.08em; color: #8ea0b5; margin-bottom: 8px; display: block;">Year</label>
                                <select name="year" id="filter-year" class="form-control" style="border: 1px solid #e4ebf4; border-radius: 10px; padding: 10px 14px; font-size: 0.92rem; color: #142235; background: #fff; width: 100%;">
                                    <option value="all" <?= ($selected_year === 'all') ? 'selected' : ''; ?>>All Years</option>
                                    <?php for ($y = date('Y'); $y >= 2020; $y--): ?>
                                        <option value="<?= $y; ?>" <?= ((string) $selected_year === (string) $y) ? 'selected' : ''; ?>>
                                            <?= $y; ?>
                                        </option>
                                    <?php endfor; ?>
                                </select>
                                <small style="color: #617489; font-size: 0.8rem; display: block; margin-top: 6px;">Select "All Years" for complete history.</small>
                            </div>
                            <div class="col-md-6">
                                <label style="font-size: 0.74rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.08em; color: #8ea0b5; margin-bottom: 8px; display: block;">Month</label>
                                <select name="month" id="filter-month" class="form-control" <?= ($selected_year === 'all') ? 'disabled' : ''; ?> style="border: 1px solid #e4ebf4; border-radius: 10px; padding: 10px 14px; font-size: 0.92rem; color: #142235; background: #fff; width: 100%;">
                                    <option value="all" <?= (empty($selected_month)) ? 'selected' : ''; ?>>All Months</option>
                                    <?php
                                    $monthNames = [
                                        1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
                                        5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
                                        9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December'
                                    ];
                                    foreach ($monthNames as $num => $name): ?>
                                        <option value="<?= $num; ?>" <?= ((int) ($selected_month ?? 0) === $num) ? 'selected' : ''; ?>>
                                            <?= $name; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <small style="color: #617489; font-size: 0.8rem; display: block; margin-top: 6px;">Choose specific month or all.</small>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer" style="border-top: 1px solid #e4ebf4; padding: 16px 22px; display: flex; gap: 10px; justify-content: flex-end;">
                        <button type="button" class="btn" data-dismiss="modal" style="border: 1px solid #cfdbea; color: #142235; background: #fff; border-radius: 10px; padding: 10px 18px; font-weight: 600;">Cancel</button>
                        <button type="submit" name="filter" value="1" style="border: none; background: linear-gradient(135deg, #2563eb, #1d4ed8); color: #fff; border-radius: 10px; padding: 10px 22px; font-weight: 700; cursor: pointer;">Apply Filter</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
    // Enable/disable month dropdown based on year selection
    document.getElementById('filter-year').addEventListener('change', function() {
        var monthSelect = document.getElementById('filter-month');
        if (this.value === 'all') {
            monthSelect.disabled = true;
            monthSelect.value = 'all';
        } else {
            monthSelect.disabled = false;
        }
    });
    </script>

</body>

</html>
