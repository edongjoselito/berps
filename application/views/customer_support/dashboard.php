<div class="content-page">
    <div class="content">
        <div class="container-fluid">
            <!-- Page Title -->
            <div class="page-title-box">
                <div class="row align-items-center">
                    <div class="col-sm-6">
                        <h4 class="page-title">Customer Support Dashboard</h4>
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="<?= base_url('dashboard'); ?>">Home</a></li>
                            <li class="breadcrumb-item active">Customer Support</li>
                        </ol>
                    </div>
                    <div class="col-sm-6">
                        <div class="float-right d-none d-md-block">
                            <a href="<?= base_url('CustomerSupport/submit_issue'); ?>" class="btn btn-primary">
                                <i class="mdi mdi-plus"></i> New Issue
                            </a>
                            <a href="<?= base_url('CustomerSupport/issues'); ?>" class="btn btn-info ml-2">
                                <i class="mdi mdi-format-list-bulleted"></i> All Issues
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="row">
                <div class="col-xl-3 col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <div class="avatar-sm bg-primary rounded">
                                        <i class="mdi mdi-ticket-account avatar-title font-size-24"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <p class="text-muted mb-1">Total Issues</p>
                                    <h4 class="mb-0"><?= $stats['total_issues'] ?? 0; ?></h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-xl-3 col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <div class="avatar-sm bg-warning rounded">
                                        <i class="mdi mdi-clock-outline avatar-title font-size-24"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <p class="text-muted mb-1">Open Issues</p>
                                    <h4 class="mb-0"><?= $stats['issues_by_status']['open'] ?? 0; ?></h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-xl-3 col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <div class="avatar-sm bg-success rounded">
                                        <i class="mdi mdi-check-circle avatar-title font-size-24"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <p class="text-muted mb-1">Resolved</p>
                                    <h4 class="mb-0"><?= $stats['issues_by_status']['resolved'] ?? 0; ?></h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-xl-3 col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <div class="avatar-sm bg-danger rounded">
                                        <i class="mdi mdi-bell avatar-title font-size-24"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <p class="text-muted mb-1">Unread Notifications</p>
                                    <h4 class="mb-0"><?= $unread_count ?? 0; ?></h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts and Tables Row -->
            <div class="row">
                <!-- Issues by Status Chart -->
                <div class="col-xl-6">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="card-title mb-4">Issues by Status</h4>
                            <div id="status-chart" style="height: 300px;"></div>
                        </div>
                    </div>
                </div>
                
                <!-- Issues by Priority Chart -->
                <div class="col-xl-6">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="card-title mb-4">Issues by Priority</h4>
                            <div id="priority-chart" style="height: 300px;"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Department Statistics -->
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="card-title mb-4">Department Statistics</h4>
                            <div class="table-responsive">
                                <table class="table table-centered table-nowrap mb-0">
                                    <thead>
                                        <tr>
                                            <th>Department</th>
                                            <th>Total Issues</th>
                                            <th>Open</th>
                                            <th>In Progress</th>
                                            <th>Resolved</th>
                                            <th>Urgent Issues</th>
                                            <th>Avg Resolution Time</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($stats['department_stats'])): ?>
                                            <?php foreach ($stats['department_stats'] as $dept): ?>
                                                <tr>
                                                    <td>
                                                        <h5 class="font-size-14 mb-1"><?= htmlspecialchars($dept->department_name, ENT_QUOTES, 'UTF-8'); ?></h5>
                                                        <p class="text-muted mb-0"><?= htmlspecialchars($dept->department_code, ENT_QUOTES, 'UTF-8'); ?></p>
                                                    </td>
                                                    <td><?= $dept->total_issues; ?></td>
                                                    <td><span class="badge badge-warning"><?= $dept->open_issues; ?></span></td>
                                                    <td><span class="badge badge-info"><?= $dept->in_progress_issues; ?></span></td>
                                                    <td><span class="badge badge-success"><?= $dept->resolved_issues; ?></span></td>
                                                    <td><span class="badge badge-danger"><?= $dept->urgent_issues; ?></span></td>
                                                    <td><?= number_format($dept->avg_resolution_time_hours ?? 0, 1); ?> hrs</td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="7" class="text-center">No department statistics available</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Issues -->
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <h4 class="card-title mb-0">Recent Issues</h4>
                                <a href="<?= base_url('CustomerSupport/issues'); ?>" class="btn btn-sm btn-primary">View All</a>
                            </div>
                            
                            <div class="table-responsive">
                                <table class="table table-centered table-nowrap mb-0">
                                    <thead>
                                        <tr>
                                            <th>Ticket #</th>
                                            <th>Title</th>
                                            <th>Customer</th>
                                            <th>Department</th>
                                            <th>Priority</th>
                                            <th>Status</th>
                                            <th>Assigned To</th>
                                            <th>Created</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($recent_issues)): ?>
                                            <?php foreach ($recent_issues as $issue): ?>
                                                <tr>
                                                    <td>
                                                        <span class="badge badge-primary"><?= htmlspecialchars($issue->ticket_number, ENT_QUOTES, 'UTF-8'); ?></span>
                                                    </td>
                                                    <td>
                                                        <h5 class="font-size-14 mb-1"><?= htmlspecialchars($issue->title, ENT_QUOTES, 'UTF-8'); ?></h5>
                                                        <p class="text-muted mb-0"><?= substr(htmlspecialchars($issue->description, ENT_QUOTES, 'UTF-8'), 0, 50) . '...'; ?></p>
                                                    </td>
                                                    <td>
                                                        <div>
                                                            <h5 class="font-size-14 mb-1"><?= htmlspecialchars($issue->customer_name, ENT_QUOTES, 'UTF-8'); ?></h5>
                                                            <p class="text-muted mb-0"><?= htmlspecialchars($issue->customer_email, ENT_QUOTES, 'UTF-8'); ?></p>
                                                        </div>
                                                    </td>
                                                    <td><?= htmlspecialchars($issue->department_name, ENT_QUOTES, 'UTF-8'); ?></td>
                                                    <td>
                                                        <?php
                                                        $priority_colors = [
                                                            'low' => 'secondary',
                                                            'medium' => 'info',
                                                            'high' => 'warning',
                                                            'urgent' => 'danger'
                                                        ];
                                                        $color = $priority_colors[$issue->priority] ?? 'secondary';
                                                        ?>
                                                        <span class="badge badge-<?= $color; ?>"><?= ucfirst($issue->priority); ?></span>
                                                    </td>
                                                    <td>
                                                        <?php
                                                        $status_colors = [
                                                            'open' => 'warning',
                                                            'assigned' => 'info',
                                                            'in_progress' => 'primary',
                                                            'pending_customer' => 'secondary',
                                                            'resolved' => 'success',
                                                            'closed' => 'dark'
                                                        ];
                                                        $color = $status_colors[$issue->status] ?? 'secondary';
                                                        ?>
                                                        <span class="badge badge-<?= $color; ?>"><?= ucfirst(str_replace('_', ' ', $issue->status)); ?></span>
                                                    </td>
                                                    <td>
                                                        <?= $issue->assigned_employee_name ? htmlspecialchars($issue->assigned_employee_name, ENT_QUOTES, 'UTF-8') : '<span class="text-muted">Unassigned</span>'; ?>
                                                    </td>
                                                    <td><?= date('M d, Y H:i', strtotime($issue->created_at)); ?></td>
                                                    <td>
                                                        <a href="<?= base_url('CustomerSupport/view_issue/' . $issue->id); ?>" class="btn btn-sm btn-primary">
                                                            <i class="mdi mdi-eye"></i> View
                                                        </a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="9" class="text-center">No recent issues found</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="card-title mb-4">Quick Actions</h4>
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="text-center">
                                        <a href="<?= base_url('CustomerSupport/departments'); ?>" class="btn btn-primary btn-lg">
                                            <i class="mdi mdi-office-building d-block font-size-24 mb-2"></i>
                                            Manage Departments
                                        </a>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="text-center">
                                        <a href="<?= base_url('CustomerSupport/employee_assignments'); ?>" class="btn btn-info btn-lg">
                                            <i class="mdi mdi-account-multiple d-block font-size-24 mb-2"></i>
                                            Employee Assignments
                                        </a>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="text-center">
                                        <a href="<?= base_url('CustomerSupport/notifications'); ?>" class="btn btn-warning btn-lg">
                                            <i class="mdi mdi-bell d-block font-size-24 mb-2"></i>
                                            Notifications
                                            <?php if ($unread_count > 0): ?>
                                                <span class="badge badge-danger ml-2"><?= $unread_count; ?></span>
                                            <?php endif; ?>
                                        </a>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="text-center">
                                        <a href="<?= base_url('CustomerSupport/reports'); ?>" class="btn btn-success btn-lg">
                                            <i class="mdi mdi-chart-line d-block font-size-24 mb-2"></i>
                                            Reports
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Charts -->
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

<script>
// Status Chart
var statusOptions = {
    series: [{
        name: 'Issues',
        data: [
            <?php if (isset($stats['issues_by_status'])): ?>
                <?= $stats['issues_by_status']['open'] ?? 0; ?>,
                <?= $stats['issues_by_status']['assigned'] ?? 0; ?>,
                <?= $stats['issues_by_status']['in_progress'] ?? 0; ?>,
                <?= $stats['issues_by_status']['pending_customer'] ?? 0; ?>,
                <?= $stats['issues_by_status']['resolved'] ?? 0; ?>,
                <?= $stats['issues_by_status']['closed'] ?? 0; ?>
            <?php else: ?>
                0,0,0,0,0,0
            <?php endif; ?>
        ]
    }],
    chart: {
        type: 'bar',
        height: 300,
        toolbar: {
            show: false
        }
    },
    plotOptions: {
        bar: {
            horizontal: false,
            columnWidth: '55%',
            endingShape: 'rounded'
        },
    },
    dataLabels: {
        enabled: false
    },
    stroke: {
        show: true,
        width: 2,
        colors: ['transparent']
    },
    xaxis: {
        categories: ['Open', 'Assigned', 'In Progress', 'Pending Customer', 'Resolved', 'Closed'],
    },
    yaxis: {
        title: {
            text: 'Number of Issues'
        }
    },
    fill: {
        opacity: 1
    },
    tooltip: {
        y: {
            formatter: function (val) {
                return val + " issues"
            }
        }
    }
};

var statusChart = new ApexCharts(document.querySelector("#status-chart"), statusOptions);
statusChart.render();

// Priority Chart
var priorityOptions = {
    series: [{
        name: 'Issues',
        data: [
            <?php if (isset($stats['issues_by_priority'])): ?>
                <?= $stats['issues_by_priority']['low'] ?? 0; ?>,
                <?= $stats['issues_by_priority']['medium'] ?? 0; ?>,
                <?= $stats['issues_by_priority']['high'] ?? 0; ?>,
                <?= $stats['issues_by_priority']['urgent'] ?? 0; ?>
            <?php else: ?>
                0,0,0,0
            <?php endif; ?>
        ]
    }],
    chart: {
        type: 'donut',
        height: 300
    },
    labels: ['Low', 'Medium', 'High', 'Urgent'],
    colors: ['#6c757d', '#17a2b8', '#ffc107', '#dc3545'],
    legend: {
        position: 'bottom'
    },
    responsive: [{
        breakpoint: 480,
        options: {
            chart: {
                width: 200
            },
            legend: {
                position: 'bottom'
            }
        }
    }]
};

var priorityChart = new ApexCharts(document.querySelector("#priority-chart"), priorityOptions);
priorityChart.render();

// Auto-refresh notifications
setInterval(function() {
    fetch('<?= base_url('CustomerSupport/get_unread_count'); ?>')
        .then(response => response.json())
        .then(data => {
            // Update notification badge if needed
            const badge = document.querySelector('.notification-badge');
            if (badge && data.count !== parseInt(badge.textContent)) {
                badge.textContent = data.count;
                badge.style.display = data.count > 0 ? 'inline-block' : 'none';
            }
        });
}, 30000); // Refresh every 30 seconds
</script>
