<?php
$signupPackages = isset($signupPackages) && is_array($signupPackages) ? $signupPackages : array();
$packages = isset($featurePackages) && is_array($featurePackages) ? $featurePackages : array();
$featureLabelsMap = isset($featureLabels) && is_array($featureLabels) ? $featureLabels : array();
$recaptchaSettings = isset($recaptchaSettings) && is_object($recaptchaSettings) ? $recaptchaSettings : (object) array(
    'site_key' => '',
    'secret_key' => '',
    'is_enabled' => 0,
    'recaptcha_version' => 'v2'
);

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

    .package-desc {
        font-size: 13px;
        color: #6b7280;
        line-height: 1.5;
        margin-bottom: 16px;
        flex-grow: 1;
    }

    .package-features {
        margin-top: auto;
    }

    .package-features h6 {
        font-size: 12px;
        font-weight: 700;
        color: #374151;
        margin-bottom: 8px;
        text-transform: uppercase;
        letter-spacing: .05em;
    }

    .package-features ul {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .package-features li {
        font-size: 12px;
        color: #6b7280;
        padding: 4px 0;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .package-features li:before {
        content: "✓";
        color: #10b981;
        font-weight: 700;
    }

    .form-section {
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        padding: 24px;
        margin-bottom: 20px;
        background: #fbfdff;
    }

    .form-section-title {
        font-size: 16px;
        font-weight: 700;
        color: #111827;
        margin-bottom: 20px;
        padding-bottom: 12px;
        border-bottom: 2px solid #e5e7eb;
    }

    .form-help {
        color: #6b7280;
        font-size: 12px;
        margin-top: 6px;
    }

    .status-badge {
        display: inline-flex;
        align-items: center;
        padding: 6px 12px;
        border-radius: 999px;
        font-size: 12px;
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

    .info-box {
        background: #eff6ff;
        border: 1px solid #bfdbfe;
        border-radius: 10px;
        padding: 16px;
        margin-bottom: 20px;
    }

    .info-box h6 {
        color: #1e40af;
        font-weight: 700;
        margin-bottom: 8px;
    }

    .info-box p {
        color: #1e3a8a;
        font-size: 13px;
        margin: 0;
    }

    .info-box ul {
        color: #1e3a8a;
        font-size: 13px;
        margin: 8px 0 0 0;
        padding-left: 20px;
    }

    .info-box li {
        margin-bottom: 4px;
    }

    .nav-tabs {
        border-bottom: 2px solid #e5e7eb;
        margin-bottom: 24px;
    }

    .nav-tabs .nav-link {
        border: none;
        color: #6b7280;
        font-weight: 600;
        padding: 12px 24px;
        border-radius: 8px 8px 0 0;
        margin-right: 4px;
    }

    .nav-tabs .nav-link:hover {
        color: #4f46e5;
        background: #f9fafb;
    }

    .nav-tabs .nav-link.active {
        color: #4f46e5;
        background: #eef2ff;
        border-bottom: 2px solid #4f46e5;
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
                                        <h4 class="mb-1">System Settings</h4>
                                        <p class="text-muted mb-0">Configure global system settings for signup packages and security.</p>
                                    </div>
                                    <button class="btn btn-secondary" onclick="window.location.href='<?= site_url('Page/superAdmin'); ?>'">
                                        <i class="mdi mdi-arrow-left mr-1"></i> Back to Dashboard
                                    </button>
                                </div>

                                <ul class="nav nav-tabs" id="settingsTabs" role="tablist">
                                    <li class="nav-item">
                                        <a class="nav-link active" id="signup-packages-tab" data-toggle="tab" href="#signup-packages" role="tab">Signup Packages</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" id="recaptcha-tab" data-toggle="tab" href="#recaptcha" role="tab">reCAPTCHA Settings</a>
                                    </li>
                                </ul>

                                <div class="tab-content">
                                    <!-- Signup Packages Tab -->
                                    <div class="tab-pane fade show active" id="signup-packages" role="tabpanel">
                                        <div class="info-box">
                                            <h6><i class="mdi mdi-information-outline mr-1"></i> About Signup Packages</h6>
                                            <p>Configure which subscription packages are available for new company signups. Users can only select from enabled packages during registration.</p>
                                        </div>

                                        <div class="package-grid">
                                            <?php if (isset($packages['all'])): ?>
                                            <div class="package-card <?= isset($enabledPackageMap['all']) && $enabledPackageMap['all'] ? 'enabled' : 'disabled'; ?>" 
                                                 data-package-id="all" onclick="togglePackage('all')">
                                                <span class="package-badge">All Packages</span>
                                                <div class="package-title">Complete Access</div>
                                                <div class="package-desc">Full access to all features across all packages. Best for comprehensive business needs.</div>
                                                <div class="package-features">
                                                    <h6>Includes All Features</h6>
                                                    <ul>
                                                        <li>Invoice & Deliveries</li>
                                                        <li>Expenses & Job Orders</li>
                                                        <li>Projects & Tasks</li>
                                                        <li>Payroll Management</li>
                                                        <li>POS System</li>
                                                        <li>Calendar & Notes</li>
                                                    </ul>
                                                </div>
                                            </div>
                                            <?php endif; ?>

                                            <?php foreach ($packages as $pkgId => $pkgData): ?>
                                                <?php if ($pkgId === 'all') continue; ?>
                                                <div class="package-card <?= isset($enabledPackageMap[$pkgId]) && $enabledPackageMap[$pkgId] ? 'enabled' : 'disabled'; ?>" 
                                                     data-package-id="<?= $pkgId; ?>" onclick="togglePackage('<?= $pkgId; ?>')">
                                                    <span class="package-badge">Package <?= $pkgId; ?></span>
                                                    <div class="package-title"><?= htmlspecialchars((string) $pkgData['title'], ENT_QUOTES, 'UTF-8'); ?></div>
                                                    <div class="package-desc"><?= htmlspecialchars((string) $pkgData['description'], ENT_QUOTES, 'UTF-8'); ?></div>
                                                    <div class="package-features">
                                                        <h6>Features</h6>
                                                        <ul>
                                                            <?php foreach ($pkgData['features'] as $featureKey): ?>
                                                                <li><?= isset($featureLabelsMap[$featureKey]) ? htmlspecialchars((string) $featureLabelsMap[$featureKey], ENT_QUOTES, 'UTF-8') : $featureKey; ?></li>
                                                            <?php endforeach; ?>
                                                        </ul>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>

                                        <div class="d-flex justify-content-between align-items-center mt-4">
                                            <p class="text-muted mb-0">Click on packages to enable/disable them for signup.</p>
                                            <button class="btn btn-primary" onclick="saveSignupPackages()">
                                                <i class="mdi mdi-content-save-outline mr-1"></i> Save Changes
                                            </button>
                                        </div>
                                    </div>

                                    <!-- reCAPTCHA Tab -->
                                    <div class="tab-pane fade" id="recaptcha" role="tabpanel">
                                        <div class="info-box">
                                            <h6><i class="mdi mdi-information-outline mr-1"></i> About Google reCAPTCHA</h6>
                                            <p>Google reCAPTCHA is a free service that protects your website from fraud and abuse. It uses advanced risk analysis techniques to tell humans and bots apart.</p>
                                            <ul>
                                                <li><strong>Site Key:</strong> Public key used in your website's HTML code</li>
                                                <li><strong>Secret Key:</strong> Private key used for server-side validation (never share this)</li>
                                                <li><strong>Get your keys:</strong> Visit <a href="https://www.google.com/recaptcha/admin" target="_blank">Google reCAPTCHA Admin Console</a></li>
                                            </ul>
                                        </div>

                                        <form id="recaptchaForm">
                                            <div class="form-section">
                                                <div class="form-section-title">General Settings</div>
                                                
                                                <div class="row">
                                                    <div class="col-md-6 mb-3">
                                                        <label class="font-weight-bold">Enable reCAPTCHA</label>
                                                        <div class="custom-control custom-switch mt-2">
                                                            <input type="checkbox" class="custom-control-input" id="isEnabled" name="is_enabled" <?= $recaptchaSettings->is_enabled ? 'checked' : ''; ?>>
                                                            <label class="custom-control-label" for="isEnabled">Enable reCAPTCHA on signup form</label>
                                                        </div>
                                                        <div class="form-help">When enabled, users must complete the reCAPTCHA challenge before signing up.</div>
                                                    </div>

                                                    <div class="col-md-6 mb-3">
                                                        <label class="font-weight-bold">reCAPTCHA Version</label>
                                                        <select class="form-control" name="recaptcha_version" id="recaptchaVersion">
                                                            <option value="v2" <?= $recaptchaSettings->recaptcha_version === 'v2' ? 'selected' : ''; ?>>reCAPTCHA v2</option>
                                                            <option value="v3" <?= $recaptchaSettings->recaptcha_version === 'v3' ? 'selected' : ''; ?>>reCAPTCHA v3</option>
                                                        </select>
                                                        <div class="form-help">v2 shows a checkbox challenge, v3 runs invisibly in the background.</div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="form-section">
                                                <div class="form-section-title">API Keys</div>
                                                
                                                <div class="row">
                                                    <div class="col-md-12 mb-3">
                                                        <label class="font-weight-bold">Site Key *</label>
                                                        <input type="text" class="form-control" name="site_key" id="siteKey" value="<?= htmlspecialchars((string) $recaptchaSettings->site_key, ENT_QUOTES, 'UTF-8'); ?>" required>
                                                        <div class="form-help">The public site key from your Google reCAPTCHA project.</div>
                                                    </div>

                                                    <div class="col-md-12 mb-3">
                                                        <label class="font-weight-bold">Secret Key *</label>
                                                        <input type="password" class="form-control" name="secret_key" id="secretKey" value="<?= htmlspecialchars((string) $recaptchaSettings->secret_key, ENT_QUOTES, 'UTF-8'); ?>" required>
                                                        <div class="form-help">The private secret key from your Google reCAPTCHA project. Keep this secure.</div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="form-section">
                                                <div class="form-section-title">Current Status</div>
                                                
                                                <div class="row">
                                                    <div class="col-md-12">
                                                        <span class="status-badge <?= $recaptchaSettings->is_enabled ? 'enabled' : 'disabled'; ?>">
                                                            <?= $recaptchaSettings->is_enabled ? 'Enabled' : 'Disabled'; ?>
                                                        </span>
                                                        <span style="margin-left: 12px; color: #6b7280;">
                                                            Version: <?= htmlspecialchars((string) $recaptchaSettings->recaptcha_version, ENT_QUOTES, 'UTF-8'); ?>
                                                        </span>
                                                        <?php if (!empty($recaptchaSettings->site_key)): ?>
                                                            <span style="margin-left: 12px; color: #6b7280;">
                                                                Site Key configured
                                                            </span>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="d-flex justify-content-between align-items-center mt-4">
                                                <p class="text-muted mb-0">Save your changes to apply the reCAPTCHA settings to the signup form.</p>
                                                <button type="submit" class="btn btn-primary btn-lg">
                                                    <i class="mdi mdi-content-save-outline mr-1"></i> Save Settings
                                                </button>
                                            </div>
                                        </form>
                                    </div>
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
            // Signup Packages functionality
            window.togglePackage = function(packageId) {
                const card = document.querySelector('[data-package-id="' + packageId + '"]');
                if (card) {
                    card.classList.toggle('enabled');
                    card.classList.toggle('disabled');
                }
            };

            window.saveSignupPackages = function() {
                const enabledPackages = [];
                document.querySelectorAll('.package-card.enabled').forEach(function(card) {
                    enabledPackages.push(card.getAttribute('data-package-id'));
                });

                $.post('<?= site_url('Page/saveSignupPackages'); ?>', { enabled_packages: enabledPackages }, function(response) {
                    if (response.success) {
                        alert(response.message || 'Signup packages updated successfully');
                    } else {
                        alert(response.message || 'Error updating signup packages');
                    }
                }, 'json').fail(function(xhr) {
                    const response = xhr.responseJSON || {};
                    alert(response.message || 'Error updating signup packages');
                });
            };

            // reCAPTCHA form functionality
            const $recaptchaForm = $('#recaptchaForm');
            
            $recaptchaForm.on('submit', function(e) {
                e.preventDefault();
                
                const formData = {
                    site_key: $('#siteKey').val(),
                    secret_key: $('#secretKey').val(),
                    is_enabled: $('#isEnabled').is(':checked') ? 1 : 0,
                    recaptcha_version: $('#recaptchaVersion').val()
                };
                
                $.post('<?= site_url('Page/saveRecaptchaSettings'); ?>', formData, function(response) {
                    if (response.success) {
                        alert(response.message || 'Settings saved successfully');
                        window.location.reload();
                    } else {
                        alert(response.message || 'Error saving settings');
                    }
                }, 'json').fail(function(xhr) {
                    const response = xhr.responseJSON || {};
                    alert(response.message || 'Error saving settings');
                });
            });
        });
    </script>
</body>
</html>
