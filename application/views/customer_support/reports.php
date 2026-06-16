<div class="content-page">
    <div class="content">
        <div class="container-fluid admin-dashboard-page">
            <!-- Page Title -->
            <div class="page-title-box">
                <div class="row align-items-center">
                    <div class="col-sm-6">
                        <h4 class="page-title">Support Reports</h4>
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="<?= base_url('CustomerSupport'); ?>">Customer Support</a></li>
                            <li class="breadcrumb-item active">Reports</li>
                        </ol>
                    </div>
                </div>
            </div>

            <style>
                .admin-dashboard-page {
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
                    --warning: #d97706;
                    --danger: #dc2626;
                }

                .admin-dashboard-page .card {
                    background: var(--surface);
                    border: 1px solid var(--line);
                    border-radius: 16px;
                    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
                }

                .admin-dashboard-page .card-body {
                    padding: 24px;
                }

                .admin-dashboard-page .card-title {
                    color: var(--text);
                    font-weight: 600;
                    font-size: 1.125rem;
                }

                .admin-dashboard-page .stat-card {
                    background: var(--surface);
                    border: 1px solid var(--line);
                    border-radius: 16px;
                    padding: 24px;
                    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
                }

                .admin-dashboard-page .stat-number {
                    color: var(--text);
                    font-weight: 700;
                    font-size: 2rem;
                }

                .admin-dashboard-page .stat-label {
                    color: var(--text-soft);
                    font-size: 0.875rem;
                    font-weight: 500;
                }

                .admin-dashboard-page .badge-primary {
                    background: var(--primary-soft);
                    color: var(--primary);
                    padding: 4px 10px;
                    border-radius: 6px;
                    font-weight: 500;
                    font-size: 0.75rem;
                }

                .admin-dashboard-page .badge-success {
                    background: #d1fae5;
                    color: var(--success);
                    padding: 4px 10px;
                    border-radius: 6px;
                    font-weight: 500;
                    font-size: 0.75rem;
                }

                .admin-dashboard-page .badge-warning {
                    background: #fef3c7;
                    color: #d97706;
                    padding: 4px 10px;
                    border-radius: 6px;
                    font-weight: 500;
                    font-size: 0.75rem;
                }

                .admin-dashboard-page .badge-danger {
                    background: #fee2e2;
                    color: var(--danger);
                    padding: 4px 10px;
                    border-radius: 6px;
                    font-weight: 500;
                    font-size: 0.75rem;
                }

                .admin-dashboard-page .badge-secondary {
                    background: #f1f5f9;
                    color: var(--text-soft);
                    padding: 4px 10px;
                    border-radius: 6px;
                    font-weight: 500;
                    font-size: 0.75rem;
                }

                .admin-dashboard-page .table {
                    color: var(--text);
                }

                .admin-dashboard-page .table thead th {
                    background: var(--surface-soft);
                    color: var(--text);
                    font-weight: 600;
                    font-size: 0.875rem;
                    padding: 12px 16px;
                    border-bottom: 2px solid var(--line);
                }

                .admin-dashboard-page .table tbody td {
                    padding: 12px 16px;
                    border-bottom: 1px solid var(--line);
                    vertical-align: middle;
                }

                .admin-dashboard-page .avatar-sm {
                    width: 40px;
                    height: 40px;
                    display: inline-flex;
                    align-items: center;
                    justify-content: center;
                }

                .admin-dashboard-page .bg-primary {
                    background: var(--primary);
                }

                .admin-dashboard-page .bg-success {
                    background: var(--success);
                }

                .admin-dashboard-page .bg-warning {
                    background: var(--warning);
                }

                .admin-dashboard-page .bg-info {
                    background: #0ea5e9;
                }

                .admin-dashboard-page .avatar-title {
                    color: white;
                    font-size: 20px;
                }

                .admin-dashboard-page .text-muted {
                    color: var(--text-soft);
                }

                .admin-dashboard-page .text-end {
                    text-align: right;
                }
            </style>

            <!-- Overview Statistics -->
            <div class="row">
                <div class="col-xl-3 col-md-6">
                    <div class="stat-card">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <div class="avatar-sm bg-primary rounded">
                                    <i class="mdi mdi-ticket-account avatar-title font-size-20"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <p class="stat-label mb-1">Total Issues</p>
                                <h4 class="stat-number mb-0"><?= $stats['total_issues'] ?? 0; ?></h4>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6">
                    <div class="stat-card">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <div class="avatar-sm bg-success rounded">
                                    <i class="mdi mdi-check-circle avatar-title font-size-20"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <p class="stat-label mb-1">Resolved</p>
                                <h4 class="stat-number mb-0"><?= $stats['issues_by_status']['resolved'] ?? 0; ?></h4>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6">
                    <div class="stat-card">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <div class="avatar-sm bg-warning rounded">
                                    <i class="mdi mdi-clock avatar-title font-size-20"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <p class="stat-label mb-1">Open Issues</p>
                                <h4 class="stat-number mb-0"><?= $stats['issues_by_status']['open'] ?? 0; ?></h4>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6">
                    <div class="stat-card">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <div class="avatar-sm bg-info rounded">
                                    <i class="mdi mdi-bell avatar-title font-size-20"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <p class="stat-label mb-1">Unread Notifications</p>
                                <h4 class="stat-number mb-0"><?= $stats['unread_notifications'] ?? 0; ?></h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Issues by Status -->
            <div class="row mt-4">
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="card-title mb-4">Issues by Status</h4>
                            <div class="table-responsive">
                                <table class="table table-centered table-nowrap mb-0">
                                    <tbody>
                                        <?php if (!empty($stats['issues_by_status'])): ?>
                                            <?php foreach ($stats['issues_by_status'] as $status => $count): ?>
                                                <tr>
                                                    <td>
                                                        <span class="badge badge-<?= $status === 'resolved' ? 'success' : ($status === 'open' ? 'warning' : 'primary'); ?>">
                                                            <?= ucfirst($status); ?>
                                                        </span>
                                                    </td>
                                                    <td class="text-end">
                                                        <strong><?= $count; ?></strong>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="2" class="text-center text-muted">No data available</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Issues by Priority -->
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="card-title mb-4">Issues by Priority</h4>
                            <div class="table-responsive">
                                <table class="table table-centered table-nowrap mb-0">
                                    <tbody>
                                        <?php if (!empty($stats['issues_by_priority'])): ?>
                                            <?php foreach ($stats['issues_by_priority'] as $priority => $count): ?>
                                                <tr>
                                                    <td>
                                                        <span class="badge badge-<?= $priority === 'urgent' ? 'danger' : ($priority === 'high' ? 'warning' : 'primary'); ?>">
                                                            <?= ucfirst($priority); ?>
                                                        </span>
                                                    </td>
                                                    <td class="text-end">
                                                        <strong><?= $count; ?></strong>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="2" class="text-center text-muted">No data available</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Department Overview -->
            <div class="row mt-4">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="card-title mb-4">Department Overview</h4>
                            <div class="table-responsive">
                                <table class="table table-centered table-nowrap mb-0">
                                    <thead>
                                        <tr>
                                            <th>Department</th>
                                            <th>Code</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($departments)): ?>
                                            <?php foreach ($departments as $dept): ?>
                                                <tr>
                                                    <td>
                                                        <h5 class="font-size-14 mb-1"><?= htmlspecialchars($dept->department_name, ENT_QUOTES, 'UTF-8'); ?></h5>
                                                    </td>
                                                    <td>
                                                        <span class="badge badge-primary"><?= htmlspecialchars($dept->department_code, ENT_QUOTES, 'UTF-8'); ?></span>
                                                    </td>
                                                    <td>
                                                        <?php if ($dept->is_active): ?>
                                                            <span class="badge badge-success">Active</span>
                                                        <?php else: ?>
                                                            <span class="badge badge-secondary">Inactive</span>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="3" class="text-center text-muted">No departments found</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
