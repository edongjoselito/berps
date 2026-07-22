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
                <div class="container-fluid super-admin-dashboard-page berps-page">
                    <header class="berps-page-header">
                        <div class="berps-page-header__content">
                            <span class="berps-page-header__eyebrow">System overview</span>
                            <h1 class="berps-page-title">Super Admin Dashboard</h1>
                            <p class="berps-page-subtitle">Manage companies, administrators, billing, and system-wide settings.</p>
                        </div>
                    </header>

                    <section aria-labelledby="super-admin-summary-heading">
                        <h2 id="super-admin-summary-heading" class="sr-only">System summary</h2>
                        <div class="berps-stat-grid">
                            <div class="berps-stat-card">
                                <div>
                                    <p class="berps-stat-card__value"><?= number_format($totalCompaniesValue); ?></p>
                                    <p class="berps-stat-card__label">Total companies</p>
                                </div>
                                <span class="berps-stat-card__icon" aria-hidden="true"><i class="mdi mdi-domain"></i></span>
                            </div>
                            <div class="berps-stat-card berps-tone-info">
                                <div>
                                    <p class="berps-stat-card__value"><?= number_format($totalUsersValue); ?></p>
                                    <p class="berps-stat-card__label">Total users</p>
                                </div>
                                <span class="berps-stat-card__icon" aria-hidden="true"><i class="mdi mdi-account-group"></i></span>
                            </div>
                            <div class="berps-stat-card berps-tone-success">
                                <div>
                                    <p class="berps-stat-card__value"><?= number_format($totalClientsValue); ?></p>
                                    <p class="berps-stat-card__label">Total clients</p>
                                </div>
                                <span class="berps-stat-card__icon" aria-hidden="true"><i class="mdi mdi-account-multiple"></i></span>
                            </div>
                        </div>
                    </section>

                    <section class="berps-section-card" aria-labelledby="super-admin-actions-heading">
                        <div class="berps-section-card__header">
                            <div>
                                <h2 id="super-admin-actions-heading" class="berps-section-title">Quick actions</h2>
                                <p class="berps-section-copy">Open the administration areas you use most often.</p>
                            </div>
                        </div>
                        <div class="berps-section-card__body">
                            <div class="berps-quick-action-grid">
                                <?php foreach ($quickActions as $action): ?>
                                    <a href="<?= htmlspecialchars($action['url'], ENT_QUOTES, 'UTF-8'); ?>" class="berps-quick-action">
                                        <span class="berps-quick-action__icon" aria-hidden="true">
                                            <i class="mdi <?= htmlspecialchars($action['icon'], ENT_QUOTES, 'UTF-8'); ?>"></i>
                                        </span>
                                        <span>
                                            <span class="berps-quick-action__label"><?= htmlspecialchars($action['label'], ENT_QUOTES, 'UTF-8'); ?></span>
                                            <span class="berps-quick-action__meta"><?= htmlspecialchars($action['meta'], ENT_QUOTES, 'UTF-8'); ?></span>
                                        </span>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </section>
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
