<?php
$companies = isset($companies) && is_array($companies) ? $companies : array();
$billingSummaries = isset($billingSummaries) && is_array($billingSummaries) ? $billingSummaries : array();
$overviewTotals = isset($overviewTotals) && is_array($overviewTotals) ? $overviewTotals : array();
?>
<!DOCTYPE html>
<html lang="en">
<?php include('includes/head.php'); ?>

<style>
    body {
        background: #f6f7fb;
        font-size: 14px;
        color: #111827;
    }

    .page-shell {
        display: grid;
        gap: 20px;
    }

    .card {
        border: 1px solid rgba(0, 0, 0, .06);
        border-radius: 14px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, .05);
        background: #fff;
    }

    .hero-grid {
        display: grid;
        grid-template-columns: minmax(0, 1.6fr) minmax(260px, 1fr);
        gap: 16px;
        align-items: start;
    }

    .hero-title {
        font-size: 24px;
        font-weight: 700;
        color: #111827;
        margin-bottom: 8px;
    }

    .hero-copy {
        color: #6b7280;
        margin: 0;
    }

    .stat-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 16px;
    }

    .stat-card {
        border: 1px solid #e5e7eb;
        border-radius: 14px;
        padding: 18px;
        background: #fff;
    }

    .stat-label {
        font-size: 11px;
        font-weight: 700;
        color: #6b7280;
        letter-spacing: .08em;
        text-transform: uppercase;
        margin-bottom: 8px;
    }

    .stat-value {
        font-size: 24px;
        font-weight: 700;
        color: #111827;
    }

    .stat-meta {
        font-size: 12px;
        color: #6b7280;
        margin-top: 6px;
    }

    .search-box {
        position: relative;
        min-width: 280px;
    }

    .search-box i {
        position: absolute;
        left: 12px;
        top: 50%;
        transform: translateY(-50%);
        color: #94a3b8;
    }

    .search-box .form-control {
        padding-left: 36px;
        border-radius: 10px;
    }

    .table-wrap {
        overflow-x: auto;
        border: 1px solid #e5e7eb;
        border-radius: 14px;
    }

    .table {
        margin-bottom: 0;
    }

    .table thead th {
        background: #f8fafc;
        border-bottom: 1px solid #e5e7eb;
        color: #111827;
        font-weight: 700;
        white-space: nowrap;
    }

    .table tbody td {
        vertical-align: middle;
    }

    .company-name {
        font-weight: 700;
        color: #111827;
    }

    .company-meta {
        margin-top: 4px;
        font-size: 12px;
        color: #6b7280;
    }

    .pill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 10px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 700;
    }

    .pill.mode {
        background: #eef2ff;
        color: #4338ca;
    }

    .status-badge {
        display: inline-flex;
        align-items: center;
        padding: 6px 10px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 700;
        text-transform: capitalize;
    }

    .status-unpaid {
        background: #fef3c7;
        color: #92400e;
    }

    .status-partial {
        background: #dbeafe;
        color: #1d4ed8;
    }

    .status-paid {
        background: #dcfce7;
        color: #166534;
    }

    .status-free {
        background: #ede9fe;
        color: #6d28d9;
    }

    @media (max-width: 1199.98px) {
        .stat-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 991.98px) {
        .hero-grid,
        .stat-grid {
            grid-template-columns: 1fr;
        }

        .search-box {
            min-width: 100%;
        }
    }
</style>

<body>
    <div id="wrapper">
        <?php include('includes/top-nav-bar.php'); ?>
        <?php include('includes/sidebar.php'); ?>

        <div class="content-page">
            <div class="content">
                <div class="container-fluid">
                    <div class="page-shell">
                        <div class="card">
                            <div class="card-body p-4">
                                <div class="hero-grid">
                                    <div>
                                        <div class="hero-title">Billing & Payments</div>
                                        <p class="hero-copy">Review how every company is billed, see the current monthly billing estimate, and jump into each company’s payment ledger from one place.</p>
                                    </div>
                                    <div class="d-flex justify-content-lg-end flex-wrap" style="gap: 10px;">
                                        <a href="<?= site_url('Page/superAdminCompanies'); ?>" class="btn btn-outline-secondary">
                                            <i class="mdi mdi-domain mr-1"></i> Manage Companies
                                        </a>
                                        <a href="<?= site_url('Page/superAdmin'); ?>" class="btn btn-primary">
                                            <i class="mdi mdi-view-dashboard-outline mr-1"></i> Dashboard
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="stat-grid">
                            <div class="stat-card">
                                <div class="stat-label">Configured Companies</div>
                                <div class="stat-value"><?= number_format((int) ($overviewTotals['company_count'] ?? 0)); ?></div>
                                <div class="stat-meta">Companies visible to the super admin billing workspace.</div>
                            </div>
                            <div class="stat-card">
                                <div class="stat-label">Expected Monthly Billing</div>
                                <div class="stat-value">PHP <?= number_format((float) ($overviewTotals['expected_monthly_charge'] ?? 0), 2); ?></div>
                                <div class="stat-meta">Estimate based on each company’s current billing mode and rate.</div>
                            </div>
                            <div class="stat-card">
                                <div class="stat-label">Collected Payments</div>
                                <div class="stat-value">PHP <?= number_format((float) ($overviewTotals['total_paid'] ?? 0), 2); ?></div>
                                <div class="stat-meta">Total payments recorded across all company billing entries.</div>
                            </div>
                            <div class="stat-card">
                                <div class="stat-label">Outstanding Balance</div>
                                <div class="stat-value">PHP <?= number_format((float) ($overviewTotals['outstanding_balance'] ?? 0), 2); ?></div>
                                <div class="stat-meta">Remaining unpaid balances from the billing ledger.</div>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-body p-4">
                                <div class="d-flex justify-content-between align-items-center flex-wrap mb-3" style="gap: 12px;">
                                    <div>
                                        <h4 class="mb-1">Company Billing Overview</h4>
                                        <p class="text-muted mb-0">Each row shows the company billing setup, latest billing status, and current unpaid balance.</p>
                                    </div>
                                    <div class="search-box">
                                        <i class="mdi mdi-magnify"></i>
                                        <input type="text" class="form-control" id="companyBillingSearch" placeholder="Search company or settings ID">
                                    </div>
                                </div>

                                <?php if (!empty($companies)): ?>
                                <div class="table-wrap">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Company</th>
                                                <th>Billing Setup</th>
                                                <th>Billable Users</th>
                                                <th>Current Estimate</th>
                                                <th>Outstanding</th>
                                                <th>Last Billing</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody id="billingCompaniesTable">
                                            <?php foreach ($companies as $company): ?>
                                            <?php
                                            $settingsID = (int) ($company->settingsID ?? 0);
                                            $summary = isset($billingSummaries[$settingsID]) ? $billingSummaries[$settingsID] : array();
                                            $billingMode = (string) ($summary['billing_mode'] ?? 'company');
                                            $latestRecord = isset($summary['last_record']) ? $summary['last_record'] : null;
                                            $lastStatus = strtolower(trim((string) ($latestRecord->status ?? ($billingMode === 'free' ? 'free' : 'unpaid'))));
                                            $rateSuffix = $billingMode === 'individual' ? '/ active user' : '/ company';
                                            $companyName = trim((string) ($company->CompName ?? $company->BusinessName ?? 'Unknown Company'));
                                            ?>
                                            <tr class="billing-company-row" data-search="<?= htmlspecialchars(strtolower($companyName . ' ' . $settingsID), ENT_QUOTES, 'UTF-8'); ?>">
                                                <td>
                                                    <div class="company-name"><?= htmlspecialchars($companyName, ENT_QUOTES, 'UTF-8'); ?></div>
                                                    <div class="company-meta">Settings ID: <?= $settingsID; ?></div>
                                                    <?php if (!empty($company->CompEmail)): ?>
                                                    <div class="company-meta"><?= htmlspecialchars((string) $company->CompEmail, ENT_QUOTES, 'UTF-8'); ?></div>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <div class="pill mode">
                                                        <i class="mdi mdi-credit-card-outline"></i>
                                                        <?= htmlspecialchars((string) ($summary['billing_mode_label'] ?? 'Paid by Company'), ENT_QUOTES, 'UTF-8'); ?>
                                                    </div>
                                                    <div class="company-meta mt-2">
                                                        <?php if ($billingMode === 'free'): ?>
                                                            No recurring rate configured
                                                        <?php else: ?>
                                                            PHP <?= number_format((float) ($summary['monthly_rate'] ?? 0), 2); ?> <?= htmlspecialchars($rateSuffix, ENT_QUOTES, 'UTF-8'); ?>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="font-weight-bold"><?= number_format((int) ($summary['billable_users'] ?? 0)); ?></div>
                                                    <div class="company-meta">Active internal users</div>
                                                </td>
                                                <td>
                                                    <div class="font-weight-bold">PHP <?= number_format((float) ($summary['expected_monthly_charge'] ?? 0), 2); ?></div>
                                                    <div class="company-meta">
                                                        <?= $billingMode === 'individual' ? 'Rate x active users' : ($billingMode === 'free' ? 'Free access' : 'Fixed company rate'); ?>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="font-weight-bold">PHP <?= number_format((float) ($summary['outstanding_balance'] ?? 0), 2); ?></div>
                                                    <div class="company-meta"><?= number_format((int) ($summary['record_count'] ?? 0)); ?> billing entr<?= ((int) ($summary['record_count'] ?? 0)) === 1 ? 'y' : 'ies'; ?></div>
                                                </td>
                                                <td>
                                                    <?php if ($latestRecord): ?>
                                                        <div class="status-badge status-<?= htmlspecialchars($lastStatus, ENT_QUOTES, 'UTF-8'); ?>">
                                                            <?= htmlspecialchars(ucfirst($lastStatus), ENT_QUOTES, 'UTF-8'); ?>
                                                        </div>
                                                        <div class="company-meta mt-2">
                                                            <?= date('F Y', strtotime((string) $latestRecord->billing_month)); ?>
                                                        </div>
                                                    <?php else: ?>
                                                        <div class="company-meta">No billing entry yet</div>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <a href="<?= site_url('Page/superAdminCompanyBilling?settingsID=' . $settingsID); ?>" class="btn btn-sm btn-primary">
                                                        <i class="mdi mdi-file-document-edit-outline mr-1"></i> Manage
                                                    </a>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                                <?php else: ?>
                                <div class="alert alert-info mb-0">
                                    <i class="mdi mdi-information-outline mr-1"></i>
                                    No companies found yet. Create a company first before configuring billing.
                                </div>
                                <?php endif; ?>
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

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const $search = $('#companyBillingSearch');
            const $rows = $('.billing-company-row');

            $search.on('input', function() {
                const query = ($(this).val() || '').toLowerCase().trim();

                $rows.each(function() {
                    const haystack = ($(this).data('search') || '').toString();
                    $(this).toggle(query === '' || haystack.indexOf(query) !== -1);
                });
            });
        });
    </script>
</body>
</html>
