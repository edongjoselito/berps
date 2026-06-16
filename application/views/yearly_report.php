<?php
$selectedYear = isset($selectedYear) ? $selectedYear : date('Y');
$availableYears = isset($availableYears) && is_array($availableYears) ? $availableYears : array();
$monthlyData = isset($monthlyData) && is_array($monthlyData) ? $monthlyData : array();
$yearlyTotals = isset($yearlyTotals) && is_array($yearlyTotals) ? $yearlyTotals : array();
$comparisonData = isset($comparisonData) && is_array($comparisonData) ? $comparisonData : array();
$business = isset($business) ? $business : null;
$generatedAt = isset($generatedAt) ? $generatedAt : date('F j, Y h:i A');
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
                <div class="container-fluid yearly-report-page">

                    <style>
                        .yearly-report-page {
                            --bg: #f5f7fb;
                            --surface: rgba(255, 255, 255, 0.94);
                            --surface-2: #ffffff;
                            --line: #e7ecf3;
                            --line-strong: #d7e0ec;
                            --text: #122033;
                            --text-soft: #5e7188;
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
                            --slate-soft: #f8fafc;
                            --shadow: 0 14px 40px rgba(15, 23, 42, 0.08);
                            --shadow-soft: 0 8px 24px rgba(15, 23, 42, 0.05);
                            --radius-xl: 22px;
                            --radius-lg: 16px;
                            --radius-md: 12px;
                            --radius-sm: 10px;
                            --font-body: 'Inter', 'Poppins', 'Segoe UI', Arial, sans-serif;
                            --font-head: 'Inter', 'Montserrat', 'Segoe UI', Arial, sans-serif;
                            font-family: var(--font-body);
                            background:
                                radial-gradient(circle at top left, rgba(37, 99, 235, 0.08), transparent 28%),
                                radial-gradient(circle at top right, rgba(16, 185, 129, 0.08), transparent 24%),
                                linear-gradient(180deg, #f8fbff 0%, #f4f7fb 100%);
                            min-height: 100vh;
                            padding-bottom: 24px;
                        }

                        .yearly-report-page * {
                            box-sizing: border-box;
                        }

                        .yearly-report-page .page-header {
                            margin: 24px 0 18px;
                            display: flex;
                            justify-content: space-between;
                            align-items: flex-end;
                            gap: 16px;
                            flex-wrap: wrap;
                        }

                        .yearly-report-page .page-title {
                            font-size: 2.2rem;
                            font-weight: 800;
                            letter-spacing: -0.06em;
                            color: var(--text);
                            margin: 0;
                        }

                        .yearly-report-page .page-subtitle {
                            color: var(--text-soft);
                            font-size: 0.92rem;
                            font-weight: 500;
                            margin: 4px 0 0;
                        }

                        .yearly-report-page .year-selector {
                            display: flex;
                            align-items: center;
                            gap: 12px;
                        }

                        .yearly-report-page .year-selector select {
                            padding: 8px 12px;
                            border: 1px solid var(--line);
                            border-radius: var(--radius-sm);
                            font-size: 0.92rem;
                            font-weight: 500;
                            background: var(--surface);
                            color: var(--text);
                        }

                        .yearly-report-page .year-selector button {
                            padding: 8px 16px;
                            background: var(--primary);
                            color: white;
                            border: none;
                            border-radius: var(--radius-sm);
                            font-size: 0.92rem;
                            font-weight: 600;
                            cursor: pointer;
                            transition: all 0.2s ease;
                        }

                        .yearly-report-page .year-selector button:hover {
                            background: var(--primary-2);
                        }

                        .yearly-report-page .stats-grid {
                            display: grid;
                            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
                            gap: 20px;
                            margin-bottom: 24px;
                        }

                        .yearly-report-page .stat-card {
                            background: var(--surface);
                            border: 1px solid var(--line);
                            border-radius: var(--radius-lg);
                            padding: 20px;
                            box-shadow: var(--shadow-soft);
                        }

                        .yearly-report-page .stat-label {
                            color: var(--text-soft);
                            font-size: 0.82rem;
                            font-weight: 600;
                            text-transform: uppercase;
                            letter-spacing: 0.08em;
                            margin-bottom: 8px;
                        }

                        .yearly-report-page .stat-value {
                            font-size: 2rem;
                            font-weight: 800;
                            color: var(--text);
                            margin-bottom: 8px;
                        }

                        .yearly-report-page .stat-change {
                            font-size: 0.82rem;
                            font-weight: 600;
                            display: flex;
                            align-items: center;
                            gap: 4px;
                        }

                        .yearly-report-page .stat-change.positive {
                            color: var(--success);
                        }

                        .yearly-report-page .stat-change.negative {
                            color: var(--danger);
                        }

                        .yearly-report-page .chart-container {
                            background: var(--surface);
                            border: 1px solid var(--line);
                            border-radius: var(--radius-lg);
                            padding: 24px;
                            box-shadow: var(--shadow-soft);
                            margin-bottom: 24px;
                        }

                        .yearly-report-page .chart-title {
                            font-size: 1.2rem;
                            font-weight: 700;
                            color: var(--text);
                            margin: 0 0 20px 0;
                        }

                        .yearly-report-page .monthly-table {
                            width: 100%;
                            border-collapse: collapse;
                        }

                        .yearly-report-page .monthly-table th {
                            background: var(--slate-soft);
                            color: var(--text-faint);
                            font-size: 0.74rem;
                            font-weight: 800;
                            letter-spacing: 0.08em;
                            text-transform: uppercase;
                            padding: 12px 8px;
                            text-align: left;
                            border-bottom: 1px solid var(--line);
                        }

                        .yearly-report-page .monthly-table td {
                            padding: 12px 8px;
                            border-bottom: 1px solid var(--line);
                            font-size: 0.92rem;
                        }

                        .yearly-report-page .monthly-table .text-right {
                            text-align: right;
                        }

                        .yearly-report-page .monthly-table .text-mono {
                            font-family: 'Monaco', 'Menlo', 'Ubuntu Mono', monospace;
                        }

                        .yearly-report-page .company-header {
                            text-align: center;
                            margin-bottom: 32px;
                        }

                        .yearly-report-page .company-name {
                            font-size: 1.8rem;
                            font-weight: 800;
                            color: var(--text);
                            margin: 0;
                        }

                        .yearly-report-page .report-title {
                            font-size: 1.2rem;
                            font-weight: 600;
                            color: var(--text-soft);
                            margin: 8px 0 0;
                        }

                        .yearly-report-page .report-meta {
                            color: var(--text-faint);
                            font-size: 0.82rem;
                            margin: 4px 0 0;
                        }

                        @media print {
                            .yearly-report-page .year-selector,
                            .top-nav-bar,
                            .sidebar {
                                display: none !important;
                            }

                            .yearly-report-page .content-page {
                                margin-left: 0 !important;
                            }

                            .yearly-report-page .chart-container {
                                break-inside: avoid;
                                page-break-inside: avoid;
                            }

                            .yearly-report-page .stats-grid {
                                grid-template-columns: repeat(2, 1fr);
                                gap: 16px;
                            }
                        }
                    </style>

                    <div class="company-header">
                        <?php if ($business): ?>
                            <h1 class="company-name"><?= htmlspecialchars($business->CompName, ENT_QUOTES, 'UTF-8'); ?></h1>
                        <?php else: ?>
                            <h1 class="company-name">Business Report</h1>
                        <?php endif; ?>
                        <div class="report-title">Yearly Income Report - <?= htmlspecialchars($selectedYear, ENT_QUOTES, 'UTF-8'); ?></div>
                        <div class="report-meta">Generated on <?= htmlspecialchars($generatedAt, ENT_QUOTES, 'UTF-8'); ?></div>
                    </div>

                    <div class="page-header">
                        <div>
                            <h1 class="page-title">Yearly Income Report</h1>
                            <p class="page-subtitle">Comprehensive income analysis and comparison for <?= htmlspecialchars($selectedYear, ENT_QUOTES, 'UTF-8'); ?></p>
                        </div>
                        <div class="year-selector">
                            <form method="GET" action="<?= base_url(); ?>Page/yearlyReport" style="display: flex; align-items: center; gap: 12px;">
                                <select name="year" id="yearSelect">
                                    <?php foreach ($availableYears as $year): ?>
                                        <option value="<?= $year; ?>" <?= $year == $selectedYear ? 'selected' : ''; ?>>
                                            <?= $year; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="submit">View Report</button>
                            </form>
                        </div>
                    </div>

                    <div class="stats-grid">
                        <div class="stat-card">
                            <div class="stat-label">Total Invoices</div>
                            <div class="stat-value"><?= number_format($yearlyTotals['totalInvoices'] ?? 0); ?></div>
                            <div class="stat-change <?= ($comparisonData['invoiceGrowth'] ?? 0) >= 0 ? 'positive' : 'negative'; ?>">
                                <i class="fa fa-arrow-<?= ($comparisonData['invoiceGrowth'] ?? 0) >= 0 ? 'up' : 'down'; ?>"></i>
                                <?= number_format(abs($comparisonData['invoiceGrowth'] ?? 0), 1); ?>% from previous year
                            </div>
                        </div>

                        <div class="stat-card">
                            <div class="stat-label">Total Invoice Amount</div>
                            <div class="stat-value">PHP <?= number_format($yearlyTotals['totalInvoiceAmount'] ?? 0, 2); ?></div>
                            <div class="stat-change <?= ($comparisonData['invoiceGrowth'] ?? 0) >= 0 ? 'positive' : 'negative'; ?>">
                                <i class="fa fa-arrow-<?= ($comparisonData['invoiceGrowth'] ?? 0) >= 0 ? 'up' : 'down'; ?>"></i>
                                <?= number_format(abs($comparisonData['invoiceGrowth'] ?? 0), 1); ?>% from previous year
                            </div>
                        </div>

                        <div class="stat-card">
                            <div class="stat-label">Total Payments</div>
                            <div class="stat-value">PHP <?= number_format($yearlyTotals['totalPaymentAmount'] ?? 0, 2); ?></div>
                            <div class="stat-change <?= ($comparisonData['paymentGrowth'] ?? 0) >= 0 ? 'positive' : 'negative'; ?>">
                                <i class="fa fa-arrow-<?= ($comparisonData['paymentGrowth'] ?? 0) >= 0 ? 'up' : 'down'; ?>"></i>
                                <?= number_format(abs($comparisonData['paymentGrowth'] ?? 0), 1); ?>% from previous year
                            </div>
                        </div>

                        <div class="stat-card">
                            <div class="stat-label">Net Income</div>
                            <div class="stat-value">PHP <?= number_format($yearlyTotals['netIncome'] ?? 0, 2); ?></div>
                            <div class="stat-change <?= ($comparisonData['netIncomeGrowth'] ?? 0) >= 0 ? 'positive' : 'negative'; ?>">
                                <i class="fa fa-arrow-<?= ($comparisonData['netIncomeGrowth'] ?? 0) >= 0 ? 'up' : 'down'; ?>"></i>
                                <?= number_format(abs($comparisonData['netIncomeGrowth'] ?? 0), 1); ?>% from previous year
                            </div>
                        </div>
                    </div>

                    
                    <div class="chart-container">
                        <h2 class="chart-title">Monthly Breakdown</h2>
                        <div class="table-responsive">
                            <table class="monthly-table">
                                <thead>
                                    <tr>
                                        <th>Month</th>
                                        <th class="text-right">Invoices</th>
                                        <th class="text-right">Invoice Amount</th>
                                        <th class="text-right">Payments</th>
                                        <th class="text-right">Payment Amount</th>
                                        <th class="text-right">Net Income</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($monthlyData as $month): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($month['month'], ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td class="text-right"><?= number_format($month['invoiceCount']); ?></td>
                                            <td class="text-right text-mono">PHP <?= number_format($month['invoiceTotal'], 2); ?></td>
                                            <td class="text-right"><?= number_format($month['paymentCount']); ?></td>
                                            <td class="text-right text-mono">PHP <?= number_format($month['paymentTotal'], 2); ?></td>
                                            <td class="text-right text-mono <?= ($month['netIncome'] >= 0) ? '' : 'text-danger'; ?>">
                                                PHP <?= number_format($month['netIncome'], 2); ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <tfoot>
                                    <tr style="font-weight: 700; background: var(--slate-soft);">
                                        <td>Total</td>
                                        <td class="text-right"><?= number_format($yearlyTotals['totalInvoices'] ?? 0); ?></td>
                                        <td class="text-right text-mono">PHP <?= number_format($yearlyTotals['totalInvoiceAmount'] ?? 0, 2); ?></td>
                                        <td class="text-right"><?= number_format($yearlyTotals['totalPayments'] ?? 0); ?></td>
                                        <td class="text-right text-mono">PHP <?= number_format($yearlyTotals['totalPaymentAmount'] ?? 0, 2); ?></td>
                                        <td class="text-right text-mono">PHP <?= number_format($yearlyTotals['netIncome'] ?? 0, 2); ?></td>
                                    </tr>
                                </tfoot>
                            </table>
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

</body>

</html>
