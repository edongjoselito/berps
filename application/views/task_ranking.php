<!DOCTYPE html>
<html lang="en">

<?php include('includes/head.php'); ?>

<body>
    <div id="wrapper">

        <?php include('includes/top-nav-bar.php'); ?>
        <?php include('includes/sidebar.php'); ?>

        <div class="content-page">
            <div class="content">
                <div class="container-fluid ranking-page berps-page">

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
                    <div class="ranking-hero">
                        <div class="ranking-hero__content">
                            <div class="ranking-hero__eyebrow">
                                <i class="mdi mdi-podium-outline"></i>
                                Task Performance
                            </div>
                            <h1 class="ranking-hero__title">Employee Ranking <span class="medal-spin">🥇</span></h1>
                            <p class="ranking-hero__subtitle">Top performers by task points — <?= htmlspecialchars($periodLabel, ENT_QUOTES, 'UTF-8'); ?></p>
                        </div>
                        <div class="ranking-hero__actions">
                            <button type="button" class="ranking-hero-btn" data-toggle="modal" data-target="#filterRankingModal">
                                <i class="mdi mdi-filter-variant"></i>
                                <span>Filter Period</span>
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
                        <div class="berps-stat-grid">
                            <div class="berps-stat-card">
                                <div>
                                    <p class="berps-stat-card__value"><?= number_format($totalEmployees); ?></p>
                                    <p class="berps-stat-card__label">Total Employees</p>
                                </div>
                                <span class="berps-stat-card__icon" aria-hidden="true"><i class="mdi mdi-account-group"></i></span>
                            </div>
                            <div class="berps-stat-card berps-tone-success">
                                <div>
                                    <p class="berps-stat-card__value"><?= number_format($totalPoints); ?></p>
                                    <p class="berps-stat-card__label">Total Points</p>
                                </div>
                                <span class="berps-stat-card__icon" aria-hidden="true"><i class="mdi mdi-trophy"></i></span>
                            </div>
                            <div class="berps-stat-card berps-tone-warning">
                                <div>
                                    <p class="berps-stat-card__value" style="font-size: 0.95rem;"><?= htmlspecialchars(trim($topPerformer->name ?? '—'), ENT_QUOTES, 'UTF-8'); ?></p>
                                    <p class="berps-stat-card__label">Top Performer</p>
                                </div>
                                <span class="berps-stat-card__icon" aria-hidden="true"><i class="mdi mdi-star"></i></span>
                            </div>
                            <div class="berps-stat-card berps-tone-info">
                                <div>
                                    <p class="berps-stat-card__value" style="font-size: 0.95rem;"><?= htmlspecialchars($periodLabel, ENT_QUOTES, 'UTF-8'); ?></p>
                                    <p class="berps-stat-card__label">Period</p>
                                </div>
                                <span class="berps-stat-card__icon" aria-hidden="true"><i class="mdi mdi-calendar"></i></span>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Full Ranking Table -->
                    <div class="theme-card">
                        <div class="theme-card-head">
                            <h5 class="theme-card-title">Complete Rankings</h5>
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

    <div class="modal fade berps-form-modal" id="filterRankingModal" tabindex="-1" role="dialog" aria-labelledby="filterRankingModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <form method="post">
                    <div class="modal-header">
                        <div>
                            <h2 class="modal-title mb-0" id="filterRankingModalLabel">Filter Period</h2>
                            <p class="berps-modal-subtitle">Choose the year and month to rank by.</p>
                        </div>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="filter-year" class="control-label">Year</label>
                                    <select name="year" id="filter-year" class="form-control">
                                        <option value="all" <?= ($selected_year === 'all') ? 'selected' : ''; ?>>All Years</option>
                                        <?php for ($y = date('Y'); $y >= 2020; $y--): ?>
                                            <option value="<?= $y; ?>" <?= ((string) $selected_year === (string) $y) ? 'selected' : ''; ?>>
                                                <?= $y; ?>
                                            </option>
                                        <?php endfor; ?>
                                    </select>
                                    <small class="help-block">Select "All Years" for complete history.</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="filter-month" class="control-label">Month</label>
                                    <select name="month" id="filter-month" class="form-control" <?= ($selected_year === 'all') ? 'disabled' : ''; ?>>
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
                                    <small class="help-block">Choose specific month or all.</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" name="filter" value="1" class="btn btn-primary">Apply Filter</button>
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
