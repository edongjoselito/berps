<?php
$recaptchaSettings = isset($recaptchaSettings) && is_object($recaptchaSettings) ? $recaptchaSettings : (object) array(
    'site_key' => '',
    'secret_key' => '',
    'is_enabled' => 0,
    'recaptcha_version' => 'v2'
);
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
                                        <h4 class="mb-1">Google reCAPTCHA Settings</h4>
                                        <p class="text-muted mb-0">Configure Google reCAPTCHA to protect the signup form from bots and automated abuse.</p>
                                    </div>
                                    <button class="btn btn-secondary" onclick="window.location.href='<?= site_url('Page/superAdminCompanies'); ?>'">
                                        <i class="mdi mdi-arrow-left mr-1"></i> Back to Companies
                                    </button>
                                </div>

                                <div class="info-box">
                                    <h6><i class="mdi mdi-information-outline mr-1"></i> About Google reCAPTCHA</h6>
                                    <p>Google reCAPTCHA is a free service that protects your website from fraud and abuse. It uses advanced risk analysis techniques to tell humans and bots apart.</p>
                                    <ul>
                                        <li><strong>Site Key:</strong> Public key used in your website's HTML code</li>
                                        <li><strong>Secret Key:</strong> Private key used for server-side validation (never share this)</li>
                                        <li><strong>Get your keys:</strong> Visit <a href="https://www.google.com/recaptcha/admin" target="_blank">Google reCAPTCHA Admin Console</a></li>
                                    </ul>
                                </div>

                                <form id="recaptchaForm" method="POST" action="<?= site_url('Page/saveRecaptchaSettings'); ?>">
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
            <?php include('includes/footer.php'); ?>
        </div>
    </div>

    <?php include('includes/themecustomizer.php'); ?>
    <script src="<?= base_url(); ?>assets/js/vendor.min.js"></script>
    <script src="<?= base_url(); ?>assets/js/app.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const $form = $('#recaptchaForm');
            
            $form.on('submit', function(e) {
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
