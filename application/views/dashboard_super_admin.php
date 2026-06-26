<?php
$totalCompaniesValue = isset($totalCompanies) ? (int) $totalCompanies : 0;
$totalUsersValue = isset($totalUsers) ? (int) $totalUsers : 0;
$totalClientsValue = isset($totalClients) ? (int) $totalClients : 0;
$companies = isset($companies) ? $companies : array();

// Calculate per-company statistics
$companyStats = array();
foreach ($companies as $company) {
    $settingsID = (int) ($company->settingsID ?? 0);
    $userCount = $this->db->where('settingsID', $settingsID)->count_all_results('users');
    $clientCount = $this->db->where('settingsID', $settingsID)->count_all_results('customers');
    $companyStats[$settingsID] = array(
        'users' => $userCount,
        'clients' => $clientCount
    );
}

$quickActions = array(
    array(
        'label' => 'Manage Companies',
        'meta' => 'Add, edit, and manage client companies with unique settingsID.',
        'icon' => 'mdi-domain',
        'url' => base_url() . 'Page/superAdminCompanies',
    ),
    array(
        'label' => 'Company Admins',
        'meta' => 'Assign and manage admin users for each company.',
        'icon' => 'mdi-account-key',
        'url' => base_url() . 'Page/superAdminAdmins',
    ),
    array(
        'label' => 'Billing & Payments',
        'meta' => 'Handle billing setup, create monthly charges, and record payments per company.',
        'icon' => 'mdi-credit-card-outline',
        'url' => base_url() . 'Page/superAdminBilling',
    ),
    array(
        'label' => 'System Settings',
        'meta' => 'Configure global system settings, signup packages, and reCAPTCHA.',
        'icon' => 'mdi-cog',
        'url' => base_url() . 'Page/superAdminSettings',
    ),
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
                <div class="container-fluid super-admin-dashboard-page">

                    <style>

                        .super-admin-dashboard-page {
                            --bg: #f5f7fb;
                            --surface: rgba(255, 255, 255, 0.96);
                            --surface-strong: #ffffff;
                            --surface-soft: #f8fbff;
                            --line: #e4ebf4;
                            --line-strong: #cfdbea;
                            --text: #142235;
                            --text-soft: #617489;
                            --text-faint: #8ea0b5;
                            --primary: #7c3aed;
                            --primary-2: #6d28d9;
                            --primary-soft: #ede9fe;
                            --success: #10b981;
                            --warning: #f59e0b;
                            --danger: #ef4444;
                            --info: #3b82f6;
                        }

                        .super-admin-dashboard-page body {
                            background: var(--bg);
                            font-family: var(--font-primary, Montserrat, Segoe UI, Arial, sans-serif);
                            color: var(--text);
                        }

                        .stat-card {
                            background: var(--surface);
                            border: 1px solid var(--line);
                            border-radius: 16px;
                            padding: 24px;
                            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.04);
                            transition: all 0.3s ease;
                        }

                        .stat-card:hover {
                            transform: translateY(-4px);
                            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.08);
                        }

                        .stat-value {
                            font-size: 36px;
                            font-weight: 700;
                            color: var(--text);
                            margin-bottom: 4px;
                        }

                        .stat-label {
                            font-size: 14px;
                            color: var(--text-soft);
                            font-weight: 500;
                        }

                        .stat-icon {
                            width: 48px;
                            height: 48px;
                            border-radius: 12px;
                            display: flex;
                            align-items: center;
                            justify-content: center;
                            font-size: 24px;
                        }

                        .quick-action-card {
                            background: var(--surface);
                            border: 1px solid var(--line);
                            border-radius: 12px;
                            padding: 20px;
                            transition: all 0.3s ease;
                            cursor: pointer;
                            text-decoration: none;
                            display: block;
                        }

                        .quick-action-card:hover {
                            border-color: var(--primary);
                            background: var(--primary-soft);
                            transform: translateY(-2px);
                        }

                        .quick-action-icon {
                            width: 40px;
                            height: 40px;
                            border-radius: 10px;
                            background: var(--primary-soft);
                            color: var(--primary);
                            display: flex;
                            align-items: center;
                            justify-content: center;
                            font-size: 20px;
                            margin-bottom: 12px;
                        }

                        .quick-action-label {
                            font-weight: 600;
                            color: var(--text);
                            margin-bottom: 4px;
                        }

                        .quick-action-meta {
                            font-size: 12px;
                            color: var(--text-soft);
                        }

                        .company-card {
                            background: var(--surface);
                            border: 1px solid var(--line);
                            border-radius: 12px;
                            padding: 20px;
                            transition: all 0.3s ease;
                        }

                        .company-card:hover {
                            border-color: var(--primary);
                            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.06);
                        }

                        .company-name {
                            font-weight: 700;
                            color: var(--text);
                            margin-bottom: 4px;
                        }

                        .company-meta {
                            font-size: 13px;
                            color: var(--text-soft);
                        }

                        .company-stats {
                            display: flex;
                            gap: 16px;
                            margin-top: 12px;
                            padding-top: 12px;
                            border-top: 1px solid var(--line);
                        }

                        .company-stat {
                            display: flex;
                            align-items: center;
                            gap: 6px;
                            font-size: 12px;
                            color: var(--text-soft);
                        }

                        .company-stat i {
                            font-size: 16px;
                        }

                        .company-stat.users i {
                            color: #0284c7;
                        }

                        .company-stat.clients i {
                            color: #16a34a;
                        }

                        .table-responsive {
                            background: var(--surface);
                            border-radius: 12px;
                            border: 1px solid var(--line);
                            overflow: hidden;
                        }

                        .table thead th {
                            background: var(--surface-soft);
                            border-bottom: 2px solid var(--line);
                            font-weight: 600;
                            color: var(--text);
                            padding: 16px;
                        }

                        .table tbody td {
                            padding: 16px;
                            border-bottom: 1px solid var(--line);
                            vertical-align: middle;
                        }

                        .table tbody tr:hover {
                            background: var(--surface-soft);
                        }

                        .badge-status {
                            padding: 6px 12px;
                            border-radius: 20px;
                            font-size: 12px;
                            font-weight: 600;
                        }

                        .badge-active {
                            background: #dcfce7;
                            color: #16a34a;
                        }

                        .search-box {
                            position: relative;
                        }

                        .search-box input {
                            padding-left: 40px;
                            border-radius: 10px;
                            border: 1px solid var(--line);
                        }

                        .search-box i {
                            position: absolute;
                            left: 14px;
                            top: 50%;
                            transform: translateY(-50%);
                            color: var(--text-soft);
                        }
                    </style>

                    <div class="row mb-4">
                        <div class="col-12">
                            <h3 class="mb-1">Super Admin Dashboard</h3>
                            <p class="text-muted">Manage all companies and system-wide settings</p>
                        </div>
                    </div>

                    <!-- Stats Row -->
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <div class="stat-card">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <div class="stat-value"><?= number_format($totalCompaniesValue); ?></div>
                                        <div class="stat-label">Total Companies</div>
                                    </div>
                                    <div class="stat-icon" style="background: var(--primary-soft); color: var(--primary);">
                                        <i class="mdi mdi-domain"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="stat-card">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <div class="stat-value"><?= number_format($totalUsersValue); ?></div>
                                        <div class="stat-label">Total Users</div>
                                    </div>
                                    <div class="stat-icon" style="background: #e0f2fe; color: #0284c7;">
                                        <i class="mdi mdi-account-group"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="stat-card">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <div class="stat-value"><?= number_format($totalClientsValue); ?></div>
                                        <div class="stat-label">Total Clients</div>
                                    </div>
                                    <div class="stat-icon" style="background: #dcfce7; color: #16a34a;">
                                        <i class="mdi mdi-account-multiple"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h5 class="mb-3">Quick Actions</h5>
                        </div>
                        <?php foreach ($quickActions as $action): ?>
                        <div class="col-md-4 mb-3">
                            <a href="<?= $action['url']; ?>" class="quick-action-card">
                                <div class="quick-action-icon">
                                    <i class="mdi <?= $action['icon']; ?>"></i>
                                </div>
                                <div class="quick-action-label"><?= $action['label']; ?></div>
                                <div class="quick-action-meta"><?= $action['meta']; ?></div>
                            </a>
                        </div>
                        <?php endforeach; ?>
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
