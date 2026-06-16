<?php
$companyName = trim((string) ($company->CompName ?? $company->BusinessName ?? 'Unknown Company'));
$companyBusinessName = trim((string) ($company->BusinessName ?? ''));
$companySettingsId = (int) ($company->settingsID ?? 0);
$selectedPackageIds = isset($currentPackageIds) && is_array($currentPackageIds) ? array_values(array_map('intval', $currentPackageIds)) : array();
$packages = isset($featurePackages) && is_array($featurePackages) ? $featurePackages : array();
$featureLabelsMap = isset($featureLabels) && is_array($featureLabels) ? $featureLabels : array();
$selectedPackageLabels = array();

foreach ($selectedPackageIds as $selectedPackageId) {
    if (isset($packages[$selectedPackageId])) {
        $selectedPackageLabels[] = 'Package ' . $selectedPackageId;
    }
}
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

    .company-summary {
        display: grid;
        grid-template-columns: minmax(0, 1.5fr) minmax(280px, 1fr);
        gap: 16px;
        align-items: start;
    }

    .summary-name {
        font-size: 22px;
        font-weight: 700;
        color: #111827;
        margin-bottom: 8px;
    }

    .summary-copy {
        color: #6b7280;
        margin-bottom: 0;
    }

    .summary-meta-grid {
        display: grid;
        gap: 10px;
    }

    .summary-meta-card {
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        padding: 16px;
        background: #f8fafc;
    }

    .summary-meta-label {
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .08em;
        color: #6b7280;
        margin-bottom: 6px;
    }

    .summary-meta-value {
        font-size: 18px;
        font-weight: 700;
        color: #111827;
    }

    .summary-meta-subvalue {
        margin-top: 4px;
        color: #6b7280;
        font-size: 13px;
    }

    .package-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 18px;
    }

    .package-card {
        border: 1px solid #e5e7eb;
        border-radius: 14px;
        padding: 24px;
        background: #fff;
        transition: all 0.22s ease;
        cursor: pointer;
        height: 100%;
        display: flex;
        flex-direction: column;
    }

    .package-card:hover {
        box-shadow: 0 8px 24px rgba(0, 0, 0, .10);
        transform: translateY(-2px);
        border-color: #c7d2fe;
    }

    .package-card.selected {
        border-color: #4f46e5;
        background: linear-gradient(180deg, #eef2ff 0%, #ffffff 100%);
        box-shadow: 0 12px 30px rgba(79, 70, 229, .12);
    }

    .package-badge {
        display: inline-flex;
        align-items: center;
        padding: 6px 10px;
        border-radius: 999px;
        background: #eef2ff;
        color: #4338ca;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: .05em;
        text-transform: uppercase;
        margin-bottom: 12px;
    }

    .package-card.selected .package-badge {
        background: #4338ca;
        color: #fff;
    }

    .package-title {
        font-weight: 700;
        font-size: 18px;
        color: #111827;
        margin-bottom: 8px;
    }

    .package-description {
        font-size: 13px;
        color: #6b7280;
        margin-bottom: 16px;
    }

    .feature-list {
        list-style: none;
        padding: 0;
        margin: 0;
        display: grid;
        gap: 10px;
        flex: 1 1 auto;
    }

    .feature-list li {
        position: relative;
        padding-left: 22px;
        font-size: 13px;
        color: #374151;
        line-height: 1.45;
    }

    .feature-list li::before {
        content: "✓";
        position: absolute;
        left: 0;
        top: 0;
        color: #10b981;
        font-weight: 700;
    }

    .package-footer {
        margin-top: 18px;
        padding-top: 16px;
        border-top: 1px solid #f3f4f6;
        color: #6b7280;
        font-size: 12px;
        font-weight: 600;
    }

    .action-bar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 16px;
        flex-wrap: wrap;
    }

    .action-copy {
        color: #6b7280;
        margin: 0;
    }

    @media (max-width: 1199.98px) {
        .package-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 991.98px) {
        .company-summary {
            grid-template-columns: 1fr;
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
                                <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap" style="gap: 16px;">
                                    <div>
                                        <h4 class="mb-1">Company Feature Packages</h4>
                                        <p class="text-muted mb-0">Select one or more packages and save them for the chosen company. The selected packages will combine their feature access for that client account.</p>
                                    </div>
                                    <button class="btn btn-secondary" onclick="window.location.href='<?= site_url('Page/superAdminCompanies'); ?>'">
                                        <i class="mdi mdi-arrow-left mr-1"></i> Back to Companies
                                    </button>
                                </div>

                                <div class="company-summary">
                                    <div class="summary-meta-card">
                                        <div class="summary-name"><?= htmlspecialchars($companyName, ENT_QUOTES, 'UTF-8'); ?></div>
                                        <p class="summary-copy">Use this page to assign one or more predefined feature packages for this company account.</p>
                                    </div>

                                    <div class="summary-meta-grid">
                                        <div class="summary-meta-card">
                                            <div class="summary-meta-label">Settings ID</div>
                                            <div class="summary-meta-value"><?= $companySettingsId; ?></div>
                                            <div class="summary-meta-subvalue">This identifier was carried over from `superAdminCompanies`.</div>
                                        </div>

                                        <div class="summary-meta-card">
                                            <div class="summary-meta-label">Current Packages</div>
                                            <div class="summary-meta-value"><?= !empty($selectedPackageLabels) ? htmlspecialchars(implode(', ', $selectedPackageLabels), ENT_QUOTES, 'UTF-8') : 'Not Set'; ?></div>
                                            <div class="summary-meta-subvalue">
                                                <?= htmlspecialchars($companyBusinessName !== '' ? $companyBusinessName : $companyName, ENT_QUOTES, 'UTF-8'); ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-body p-4">
                                <div class="package-grid" id="packagesList">
                                    <?php foreach ($packages as $packageId => $package): ?>
                                        <div class="package-card <?= in_array((int) $packageId, $selectedPackageIds, true) ? 'selected' : ''; ?>" data-package="<?= (int) $packageId; ?>">
                                            <div class="package-badge">Package <?= (int) $packageId; ?></div>
                                            <div class="package-title"><?= htmlspecialchars((string) ($package['title'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></div>
                                            <div class="package-description"><?= htmlspecialchars((string) ($package['description'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></div>

                                            <ul class="feature-list">
                                                <?php foreach (($package['features'] ?? array()) as $featureKey): ?>
                                                    <li><?= htmlspecialchars((string) ($featureLabelsMap[$featureKey] ?? $featureKey), ENT_QUOTES, 'UTF-8'); ?></li>
                                                <?php endforeach; ?>
                                            </ul>

                                            <div class="package-footer">
                                                <?= count($package['features'] ?? array()); ?> feature<?= count($package['features'] ?? array()) === 1 ? '' : 's'; ?> included
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>

                                <div class="action-bar mt-4">
                                    <p class="action-copy">Saving packages updates the company record and replaces the company’s enabled features with the combined feature set from all selected packages.</p>
                                    <button class="btn btn-primary btn-lg" id="savePackageBtn" <?= !empty($selectedPackageIds) ? '' : 'disabled'; ?>>
                                        <i class="mdi mdi-content-save-outline mr-1"></i> Save Package Selection
                                    </button>
                                </div>
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
            const settingsID = <?= $companySettingsId; ?>;
            const $packageCards = $('.package-card');
            const $saveBtn = $('#savePackageBtn');
            const selectedPackages = new Set(<?= json_encode($selectedPackageIds); ?>);

            $packageCards.on('click', function() {
                const packageId = Number($(this).data('package')) || 0;
                if (!packageId) {
                    return;
                }

                if (selectedPackages.has(packageId)) {
                    selectedPackages.delete(packageId);
                    $(this).removeClass('selected');
                } else {
                    selectedPackages.add(packageId);
                    $(this).addClass('selected');
                }

                $saveBtn.prop('disabled', selectedPackages.size === 0);
            });

            $saveBtn.on('click', function() {
                const packageIds = Array.from(selectedPackages).sort(function(a, b) {
                    return a - b;
                });

                if (packageIds.length === 0) {
                    alert('Please select at least one package');
                    return;
                }

                $.post('<?= site_url('Page/saveCompanyPackage'); ?>', {
                    settingsID: settingsID,
                    package_ids: packageIds
                }, function(response) {
                    if (response.success) {
                        alert('Packages saved successfully');
                        window.location.href = '<?= site_url('Page/superAdminCompanies'); ?>';
                    } else {
                        alert(response.message || 'Error saving packages');
                    }
                }, 'json').fail(function(xhr) {
                    const response = xhr.responseJSON || {};
                    alert(response.message || 'Error saving packages');
                });
            });
        });
    </script>
</body>
</html>
