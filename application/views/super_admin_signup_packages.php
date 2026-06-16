<?php
$signupPackages = isset($signupPackages) && is_array($signupPackages) ? $signupPackages : array();
$packages = isset($featurePackages) && is_array($featurePackages) ? $featurePackages : array();
$featureLabelsMap = isset($featureLabels) && is_array($featureLabels) ? $featureLabels : array();

// Build a map of enabled packages
$enabledPackageMap = array();
foreach ($signupPackages as $pkg) {
    $enabledPackageMap[$pkg->package_id] = (int) $pkg->is_enabled === 1;
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

    .package-card.enabled {
        border-color: #4f46e5;
        background: linear-gradient(180deg, #eef2ff 0%, #ffffff 100%);
        box-shadow: 0 12px 30px rgba(79, 70, 229, .12);
    }

    .package-card.disabled {
        opacity: 0.6;
        border-color: #e5e7eb;
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

    .package-card.enabled .package-badge {
        background: #4338ca;
        color: #fff;
    }

    .package-card.disabled .package-badge {
        background: #e5e7eb;
        color: #6b7280;
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

    .package-card.disabled .feature-list li::before {
        color: #9ca3af;
    }

    .package-footer {
        margin-top: 18px;
        padding-top: 16px;
        border-top: 1px solid #f3f4f6;
        color: #6b7280;
        font-size: 12px;
        font-weight: 600;
    }

    .status-badge {
        display: inline-flex;
        align-items: center;
        padding: 4px 8px;
        border-radius: 999px;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .05em;
    }

    .status-badge.enabled {
        background: #dcfce7;
        color: #166534;
    }

    .status-badge.disabled {
        background: #f3f4f6;
        color: #6b7280;
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
                                        <h4 class="mb-1">Signup Package Management</h4>
                                        <p class="text-muted mb-0">Select which packages should be available for new user signups. Disabled packages will not appear in the signup form.</p>
                                    </div>
                                    <button class="btn btn-secondary" onclick="window.location.href='<?= site_url('Page/superAdminCompanies'); ?>'">
                                        <i class="mdi mdi-arrow-left mr-1"></i> Back to Companies
                                    </button>
                                </div>

                                <div class="package-grid" id="packagesList">
                                    <?php foreach ($packages as $packageId => $package): ?>
                                        <?php 
                                        $isEnabled = isset($enabledPackageMap[$packageId]) && $enabledPackageMap[$packageId];
                                        $displayId = $packageId === 'all' ? 'all' : (int) $packageId;
                                        ?>
                                        <div class="package-card <?= $isEnabled ? 'enabled' : 'disabled'; ?>" data-package="<?= htmlspecialchars($displayId, ENT_QUOTES, 'UTF-8'); ?>">
                                            <div class="package-badge">Package <?= htmlspecialchars($displayId, ENT_QUOTES, 'UTF-8'); ?></div>
                                            <div class="package-title"><?= htmlspecialchars((string) ($package['title'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></div>
                                            <div class="package-description"><?= htmlspecialchars((string) ($package['description'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></div>

                                            <ul class="feature-list">
                                                <?php foreach (($package['features'] ?? array()) as $featureKey): ?>
                                                    <li><?= htmlspecialchars((string) ($featureLabelsMap[$featureKey] ?? $featureKey), ENT_QUOTES, 'UTF-8'); ?></li>
                                                <?php endforeach; ?>
                                            </ul>

                                            <div class="package-footer">
                                                <span class="status-badge <?= $isEnabled ? 'enabled' : 'disabled'; ?>">
                                                    <?= $isEnabled ? 'Enabled' : 'Disabled'; ?>
                                                </span>
                                                <span style="margin-left: 8px;"><?= count($package['features'] ?? array()); ?> feature<?= count($package['features'] ?? array()) === 1 ? '' : 's'; ?></span>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>

                                <div class="action-bar mt-4">
                                    <p class="action-copy">Click on a package card to toggle its availability in the signup form. Save your changes to apply them.</p>
                                    <button class="btn btn-primary btn-lg" id="savePackageBtn">
                                        <i class="mdi mdi-content-save-outline mr-1"></i> Save Package Settings
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
            const $packageCards = $('.package-card');
            const $saveBtn = $('#savePackageBtn');
            const enabledPackages = new Set();

            // Initialize with currently enabled packages
            <?php foreach ($enabledPackageMap as $pkgId => $enabled): ?>
                <?php if ($enabled): ?>
                    enabledPackages.add('<?= htmlspecialchars($pkgId, ENT_QUOTES, 'UTF-8'); ?>');
                <?php endif; ?>
            <?php endforeach; ?>

            $packageCards.on('click', function() {
                const packageId = $(this).data('package');
                if (!packageId) {
                    return;
                }

                if (enabledPackages.has(packageId)) {
                    enabledPackages.delete(packageId);
                    $(this).removeClass('enabled').addClass('disabled');
                    $(this).find('.status-badge').removeClass('enabled').addClass('disabled').text('Disabled');
                } else {
                    enabledPackages.add(packageId);
                    $(this).removeClass('disabled').addClass('enabled');
                    $(this).find('.status-badge').removeClass('disabled').addClass('enabled').text('Enabled');
                }
            });

            $saveBtn.on('click', function() {
                const packageIds = Array.from(enabledPackages);

                if (packageIds.length === 0) {
                    alert('Please enable at least one package for signup');
                    return;
                }

                $.post('<?= site_url('Page/saveSignupPackages'); ?>', {
                    enabled_packages: packageIds
                }, function(response) {
                    if (response.success) {
                        alert('Signup packages updated successfully');
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
