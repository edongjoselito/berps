<!DOCTYPE html>
<html lang="en">
<?php include('includes/head.php'); ?>

<style>
    body {
        background: #f6f7fb;
        font-size: 14px;
        color: #111827;
    }

    .card {
        border: 1px solid rgba(0, 0, 0, .06);
        border-radius: 14px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, .05);
        background: #fff;
    }

    .stats-container {
        padding: 24px;
    }

    .chart-wrapper {
        position: relative;
        height: 400px;
        margin: 20px 0;
    }

    .stats-summary {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin-bottom: 24px;
    }

    .stat-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 12px;
        padding: 20px;
        color: #fff;
    }

    .stat-card.completed {
        background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
    }

    .stat-card.pending {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    }

    .stat-value {
        font-size: 32px;
        font-weight: 700;
        margin-bottom: 4px;
    }

    .stat-label {
        font-size: 13px;
        opacity: 0.9;
    }

    .table-container {
        margin-top: 24px;
        overflow-x: auto;
    }

    .stats-table {
        width: 100%;
        border-collapse: collapse;
    }

    .stats-table th,
    .stats-table td {
        padding: 12px 16px;
        text-align: left;
        border-bottom: 1px solid #e5e7eb;
    }

    .stats-table th {
        background: #f9fafb;
        font-weight: 600;
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: #6b7280;
    }

    .stats-table tr:hover {
        background: #f9fafb;
    }

    .progress-bar {
        height: 8px;
        background: #e5e7eb;
        border-radius: 4px;
        overflow: hidden;
    }

    .progress-fill {
        height: 100%;
        border-radius: 4px;
        transition: width 0.3s ease;
    }

    .progress-fill.completed {
        background: #10b981;
    }

    .progress-fill.pending {
        background: #f59e0b;
    }
</style>

<body>
    <div id="wrapper">
        <?php include('includes/top-nav-bar.php'); ?>
        <?php include('includes/sidebar.php'); ?>

        <div class="content-page">
            <div class="content">
                <div class="container-fluid">
                    <div class="card">
                        <div class="stats-container">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <div>
                                    <h5 class="mb-1">Completion Statistics</h5>
                                    <div class="text-muted small">Monthly breakdown of completed and pending calendar events</div>
                                </div>
                                <div class="d-flex gap-2">
                                    <select id="monthFilter" class="form-control form-control-sm" style="width: auto;">
                                        <option value="">All Months</option>
                                    </select>
                                    <a class="btn btn-outline-info btn-sm" href="<?= base_url('Calendar') ?>">
                                        <i class="mdi mdi-arrow-left mr-1"></i> Back to Calendar
                                    </a>
                                </div>
                            </div>

                            <div class="stats-summary">
                                <div class="stat-card">
                                    <div class="stat-value" id="totalEvents">0</div>
                                    <div class="stat-label">Total Events</div>
                                </div>
                                <div class="stat-card completed">
                                    <div class="stat-value" id="totalCompleted">0</div>
                                    <div class="stat-label">Completed</div>
                                </div>
                                <div class="stat-card pending">
                                    <div class="stat-value" id="totalPending">0</div>
                                    <div class="stat-label">Pending</div>
                                </div>
                            </div>

                            <div class="chart-wrapper">
                                <canvas id="completionChart"></canvas>
                            </div>

                            <div class="table-container">
                                <h6 class="mb-3">Monthly Details</h6>
                                <table class="stats-table">
                                    <thead>
                                        <tr>
                                            <th>Month</th>
                                            <th>Total</th>
                                            <th>Completed</th>
                                            <th>Completed %</th>
                                            <th>Pending</th>
                                            <th>Pending %</th>
                                        </tr>
                                    </thead>
                                    <tbody id="statsTableBody">
                                        <tr>
                                            <td colspan="6" class="text-center text-muted">Loading data...</td>
                                        </tr>
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
    <script src="<?= base_url(); ?>assets/js/vendor.min.js"></script>
    <script src="<?= base_url(); ?>assets/js/app.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.2.0/dist/chartjs-plugin-datalabels.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            Chart.register(ChartDataLabels);
            let completionChart = null;
            let allData = [];

            function loadStats() {
                $.get('<?= site_url("calendar/get_completion_stats") ?>', function(response) {
                    if (response.success && response.data.length > 0) {
                        allData = response.data;
                        renderChart(response.data);
                        renderTable(response.data);
                        updateSummary(response.data);
                        populateMonthFilter(response.data);
                    } else {
                        document.getElementById('statsTableBody').innerHTML = 
                            '<tr><td colspan="6" class="text-center text-muted">No events found</td></tr>';
                    }
                }, 'json').fail(function() {
                    document.getElementById('statsTableBody').innerHTML = 
                        '<tr><td colspan="6" class="text-center text-danger">Error loading data</td></tr>';
                });
            }

            function populateMonthFilter(data) {
                const select = document.getElementById('monthFilter');
                select.innerHTML = '<option value="">All Months</option>';
                
                data.forEach(item => {
                    const option = document.createElement('option');
                    option.value = item.month;
                    option.textContent = item.month;
                    select.appendChild(option);
                });

                // Set current month as default filter
                const currentMonth = new Date().toLocaleString('en-US', { month: 'long', year: 'numeric' });
                const currentMonthOption = Array.from(select.options).find(opt => opt.value === currentMonth);
                if (currentMonthOption) {
                    select.value = currentMonth;
                    applyMonthFilter();
                }
            }

            function renderChart(data) {
                const ctx = document.getElementById('completionChart').getContext('2d');
                
                const labels = data.map(d => d.month);
                const completedData = data.map(d => d.completed_percent);
                const pendingData = data.map(d => d.pending_percent);

                if (completionChart) {
                    completionChart.destroy();
                }

                completionChart = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: labels,
                        datasets: [
                            {
                                label: 'Completed %',
                                data: completedData,
                                backgroundColor: 'rgba(16, 185, 129, 0.8)',
                                borderColor: 'rgba(16, 185, 129, 1)',
                                borderWidth: 1,
                                datalabels: {
                                    anchor: 'end',
                                    align: 'end',
                                    color: '#fff',
                                    font: {
                                        weight: 'bold',
                                        size: 11
                                    },
                                    formatter: function(value) {
                                        return value + '%';
                                    }
                                }
                            },
                            {
                                label: 'Pending %',
                                data: pendingData,
                                backgroundColor: 'rgba(245, 158, 11, 0.8)',
                                borderColor: 'rgba(245, 158, 11, 1)',
                                borderWidth: 1,
                                datalabels: {
                                    anchor: 'end',
                                    align: 'end',
                                    color: '#fff',
                                    font: {
                                        weight: 'bold',
                                        size: 11
                                    },
                                    formatter: function(value) {
                                        return value + '%';
                                    }
                                }
                            }
                        ]
                    },
                    options: {
                        indexAxis: 'y',
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'top',
                            },
                            datalabels: {
                                display: true
                            },
                            tooltip: {
                                callbacks: {
                                    afterLabel: function(context) {
                                        const dataIndex = context.dataIndex;
                                        const datasetIndex = context.datasetIndex;
                                        const item = data[dataIndex];
                                        if (datasetIndex === 0) {
                                            return ` (${item.completed} events)`;
                                        } else {
                                            return ` (${item.pending} events)`;
                                        }
                                    }
                                }
                            }
                        },
                        scales: {
                            x: {
                                beginAtZero: true,
                                max: 100,
                                ticks: {
                                    callback: function(value) {
                                        return value + '%';
                                    }
                                }
                            },
                            y: {
                                grid: {
                                    display: false
                                }
                            }
                        },
                        barPercentage: 0.4,
                        categoryPercentage: 0.7
                    }
                });
            }

            function renderTable(data) {
                const tbody = document.getElementById('statsTableBody');
                let html = '';

                data.forEach(item => {
                    html += `
                        <tr>
                            <td><strong>${item.month}</strong></td>
                            <td>${item.total}</td>
                            <td>${item.completed}</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="progress-bar" style="width: 100px; margin-right: 8px;">
                                        <div class="progress-fill completed" style="width: ${item.completed_percent}%"></div>
                                    </div>
                                    <span>${item.completed_percent}%</span>
                                </div>
                            </td>
                            <td>${item.pending}</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="progress-bar" style="width: 100px; margin-right: 8px;">
                                        <div class="progress-fill pending" style="width: ${item.pending_percent}%"></div>
                                    </div>
                                    <span>${item.pending_percent}%</span>
                                </div>
                            </td>
                        </tr>
                    `;
                });

                tbody.innerHTML = html;
            }

            function updateSummary(data) {
                const total = data.reduce((sum, item) => sum + item.total, 0);
                const completed = data.reduce((sum, item) => sum + item.completed, 0);
                const pending = data.reduce((sum, item) => sum + item.pending, 0);

                document.getElementById('totalEvents').textContent = total;
                document.getElementById('totalCompleted').textContent = completed;
                document.getElementById('totalPending').textContent = pending;
            }

            function applyMonthFilter() {
                const selectedMonth = document.getElementById('monthFilter').value;
                
                if (selectedMonth === '') {
                    renderChart(allData);
                    renderTable(allData);
                    updateSummary(allData);
                } else {
                    const filteredData = allData.filter(item => item.month === selectedMonth);
                    renderChart(filteredData);
                    renderTable(filteredData);
                    updateSummary(filteredData);
                }
            }

            document.getElementById('monthFilter').addEventListener('change', applyMonthFilter);

            loadStats();
        });
    </script>
</body>
</html>
