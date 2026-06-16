<?php
$billingModes = isset($billingModeOptions) && is_array($billingModeOptions) ? $billingModeOptions : array();
if (!isset($billingModes['company'])) {
    $billingModes = array(
        'company' => array('label' => 'Paid by Company', 'rate_label' => 'Monthly rate per company'),
        'individual' => array('label' => 'Individually', 'rate_label' => 'Monthly rate per active user'),
        'free' => array('label' => 'Free', 'rate_label' => 'Monthly rate'),
    );
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

    .card {
        border: 1px solid rgba(0, 0, 0, .06);
        border-radius: 14px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, .05);
        background: #fff;
    }

    .company-card {
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 16px;
        background: #fff;
        transition: all 0.2s;
    }

    .company-card:hover {
        box-shadow: 0 4px 12px rgba(0, 0, 0, .08);
    }

    .company-name {
        font-weight: 700;
        font-size: 16px;
        color: #111827;
        margin-bottom: 8px;
    }

    .company-card-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 16px;
        flex-wrap: wrap;
    }

    .company-meta {
        font-size: 13px;
        color: #6b7280;
        margin-bottom: 4px;
    }

    .company-actions {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
        justify-content: flex-end;
    }

    .billing-pill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 10px;
        border-radius: 999px;
        background: #eef2ff;
        color: #4338ca;
        font-size: 12px;
        font-weight: 700;
        margin-top: 8px;
        margin-bottom: 8px;
    }

    .billing-rate-note {
        font-size: 12px;
        color: #6b7280;
        margin-top: 6px;
    }

    .modal-content {
        border-radius: 14px;
        border: 1px solid rgba(0, 0, 0, .06);
        box-shadow: 0 18px 50px rgba(18, 38, 63, .18);
    }

    .modal-header {
        background: #f8fafc;
        border-bottom: 1px solid rgba(0, 0, 0, .06);
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
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <div>
                                    <h4 class="mb-1">Manage Companies</h4>
                                    <p class="text-muted mb-0">Add and manage client companies with unique settingsID</p>
                                </div>
                                <div class="d-flex flex-wrap" style="gap: 10px;">
                                    <a href="<?= site_url('Page/superAdminBilling'); ?>" class="btn btn-outline-primary">
                                        <i class="mdi mdi-credit-card-outline mr-1"></i> Billing & Payments
                                    </a>
                                    <button class="btn btn-primary" id="addCompanyBtn">
                                        <i class="mdi mdi-plus mr-1"></i> Add Company
                                    </button>
                                </div>
                            </div>

                            <div id="companiesList">
                                <?php if (!empty($companies)): ?>
                                    <?php foreach ($companies as $company): ?>
                                    <?php
                                    $companyBillingMode = trim((string) ($company->billing_mode ?? 'company'));
                                    if (!isset($billingModes[$companyBillingMode])) {
                                        $companyBillingMode = 'company';
                                    }
                                    $companyMonthlyRate = round((float) ($company->monthly_rate ?? 0), 2);
                                    $companyBillingLabel = (string) ($billingModes[$companyBillingMode]['label'] ?? 'Paid by Company');
                                    $companyRateSuffix = $companyBillingMode === 'individual' ? '/ active user / month' : '/ month';
                                    $companyCategory = trim((string) ($company->CompType ?? ''));
                                    ?>
                                    <div class="company-card" data-settings-id="<?= (int) ($company->settingsID ?? 0); ?>">
                                        <div class="company-card-header">
                                            <div>
                                                <div class="company-name">
                                                    <?= htmlspecialchars((string) ($company->CompName ?? $company->BusinessName ?? 'Unknown'), ENT_QUOTES, 'UTF-8'); ?>
                                                </div>
                                                <div class="company-meta">
                                                    <strong>Settings ID:</strong> <?= (int) ($company->settingsID ?? 0); ?>
                                                </div>
                                                <?php if ($companyCategory !== ''): ?>
                                                <div class="company-meta">
                                                    <strong>Business Category:</strong> <?= htmlspecialchars($companyCategory, ENT_QUOTES, 'UTF-8'); ?>
                                                </div>
                                                <?php endif; ?>
                                                <div class="billing-pill">
                                                    <i class="mdi mdi-credit-card-outline"></i>
                                                    <?= htmlspecialchars($companyBillingLabel, ENT_QUOTES, 'UTF-8'); ?>
                                                </div>
                                                <div class="company-meta">
                                                    <strong>Rate:</strong>
                                                    <?php if ($companyBillingMode === 'free'): ?>
                                                        Free
                                                    <?php else: ?>
                                                        PHP <?= number_format($companyMonthlyRate, 2); ?> <?= htmlspecialchars($companyRateSuffix, ENT_QUOTES, 'UTF-8'); ?>
                                                    <?php endif; ?>
                                                </div>
                                                <?php if (!empty($company->CompEmail)): ?>
                                                <div class="company-meta">
                                                    <strong>Email:</strong> <?= htmlspecialchars((string) $company->CompEmail, ENT_QUOTES, 'UTF-8'); ?>
                                                </div>
                                                <?php endif; ?>
                                                <?php if (!empty($company->CompPhone)): ?>
                                                <div class="company-meta">
                                                    <strong>Phone:</strong> <?= htmlspecialchars((string) $company->CompPhone, ENT_QUOTES, 'UTF-8'); ?>
                                                </div>
                                                <?php endif; ?>
                                                <?php if (!empty($company->CompAddress)): ?>
                                                <div class="company-meta">
                                                    <strong>Address:</strong> <?= htmlspecialchars((string) $company->CompAddress, ENT_QUOTES, 'UTF-8'); ?>
                                                </div>
                                                <?php endif; ?>
                                                <div class="billing-rate-note">
                                                    <?= $companyBillingMode === 'individual' ? 'Individual mode multiplies the rate by the company’s active internal users.' : ($companyBillingMode === 'free' ? 'Free companies stay accessible without monthly billing charges.' : 'Company mode charges one fixed monthly amount for this company.'); ?>
                                                </div>
                                            </div>
                                            <div class="company-actions">
                                                <div class="btn-group">
                                                    <button
                                                        class="btn btn-sm btn-outline-info edit-company"
                                                        data-settings-id="<?= (int) ($company->settingsID ?? 0); ?>"
                                                        data-comp-name="<?= htmlspecialchars((string) ($company->CompName ?? ''), ENT_QUOTES, 'UTF-8'); ?>"
                                                        data-business-name="<?= htmlspecialchars((string) ($company->BusinessName ?? ''), ENT_QUOTES, 'UTF-8'); ?>"
                                                        data-comp-email="<?= htmlspecialchars((string) ($company->CompEmail ?? ''), ENT_QUOTES, 'UTF-8'); ?>"
                                                        data-comp-phone="<?= htmlspecialchars((string) ($company->CompPhone ?? ''), ENT_QUOTES, 'UTF-8'); ?>"
                                                        data-comp-address="<?= htmlspecialchars((string) ($company->CompAddress ?? ''), ENT_QUOTES, 'UTF-8'); ?>"
                                                        data-comp-type="<?= htmlspecialchars($companyCategory, ENT_QUOTES, 'UTF-8'); ?>"
                                                        data-billing-mode="<?= htmlspecialchars($companyBillingMode, ENT_QUOTES, 'UTF-8'); ?>"
                                                        data-monthly-rate="<?= number_format($companyMonthlyRate, 2, '.', ''); ?>"
                                                        title="Edit Company">
                                                        <i class="mdi mdi-pencil"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-outline-danger delete-company" data-settings-id="<?= (int) ($company->settingsID ?? 0); ?>" title="Delete Company">
                                                        <i class="mdi mdi-delete"></i>
                                                    </button>
                                                </div>
                                                <div class="btn-group">
                                                    <button class="btn btn-sm btn-outline-primary manage-admins" data-settings-id="<?= (int) ($company->settingsID ?? 0); ?>" title="Manage Admins">
                                                        <i class="mdi mdi-account-key"></i> Admins
                                                    </button>
                                                    <button class="btn btn-sm btn-outline-success configure-features" data-settings-id="<?= (int) ($company->settingsID ?? 0); ?>" title="Configure Features">
                                                        <i class="mdi mdi-cog"></i> Features
                                                    </button>
                                                    <button class="btn btn-sm btn-outline-dark view-billing" data-settings-id="<?= (int) ($company->settingsID ?? 0); ?>" title="Billing & Payments">
                                                        <i class="mdi mdi-credit-card-outline"></i> Billing
                                                    </button>
                                                    <button class="btn btn-sm btn-outline-warning generate-activation-key" data-settings-id="<?= (int) ($company->settingsID ?? 0); ?>" title="Generate Branch Activation Key">
                                                        <i class="mdi mdi-key-variant"></i> Branch Key
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="text-center py-5 text-muted">
                                        <i class="mdi mdi-domain" style="font-size: 48px; opacity: 0.3;"></i>
                                        <p class="mt-3">No companies found. Click "Add Company" to create your first company.</p>
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

    <div class="modal fade" id="companyModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <h5 class="modal-title mb-0" id="modalTitle">Add Company</h5>
                        <div class="small text-muted">Configure company details</div>
                    </div>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>

                <div class="modal-body">
                    <form id="companyForm">
                        <div class="form-group mb-3">
                            <label class="font-weight-bold">Company Name *</label>
                            <input type="text" class="form-control" name="CompName" id="compName" required>
                        </div>
                        <div class="form-group mb-3">
                            <label class="font-weight-bold">Business Name</label>
                            <input type="text" class="form-control" name="BusinessName" id="businessName">
                        </div>
                        <div class="form-group mb-3">
                            <label class="font-weight-bold">Business Category</label>
                            <input type="text" class="form-control" name="CompType" id="compType" placeholder="Retail, Restaurant, Pharmacy, Grocery">
                        </div>
                        <div class="form-group mb-3">
                            <label class="font-weight-bold">Email</label>
                            <input type="email" class="form-control" name="CompEmail" id="compEmail">
                        </div>
                        <div class="form-group mb-3">
                            <label class="font-weight-bold">Phone</label>
                            <input type="text" class="form-control" name="CompPhone" id="compPhone">
                        </div>
                        <div class="form-group mb-0">
                            <label class="font-weight-bold">Address</label>
                            <textarea class="form-control" name="CompAddress" id="compAddress" rows="3"></textarea>
                        </div>
                        <div class="row mt-3">
                            <div class="col-md-6">
                                <div class="form-group mb-3 mb-md-0">
                                    <label class="font-weight-bold">Billing Setup</label>
                                    <select class="form-control" name="billing_mode" id="billingMode">
                                        <?php foreach ($billingModes as $billingModeKey => $billingMode): ?>
                                        <option value="<?= htmlspecialchars((string) $billingModeKey, ENT_QUOTES, 'UTF-8'); ?>">
                                            <?= htmlspecialchars((string) ($billingMode['label'] ?? $billingModeKey), ENT_QUOTES, 'UTF-8'); ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-0">
                                    <label class="font-weight-bold">Rate Per Month</label>
                                    <input type="number" class="form-control" name="monthly_rate" id="monthlyRate" min="0" step="0.01" placeholder="0.00">
                                    <small class="form-text text-muted" id="monthlyRateHelp">Set the company’s monthly billing rate.</small>
                                </div>
                            </div>
                        </div>
                        <input type="hidden" name="settingsID" id="settingsID">
                    </form>
                </div>

                <div class="modal-footer d-flex justify-content-between">
                    <button class="btn btn-light" data-dismiss="modal">Cancel</button>
                    <div>
                        <button id="deleteCompanyBtn" class="btn btn-outline-danger d-none mr-2">
                            <i class="mdi mdi-delete-outline"></i> Delete
                        </button>
                        <button id="saveCompanyBtn" class="btn btn-primary">
                            <i class="mdi mdi-content-save-outline mr-1"></i> Save Company
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include('includes/themecustomizer.php'); ?>
    <script src="<?= base_url(); ?>assets/js/vendor.min.js"></script>
    <script src="<?= base_url(); ?>assets/js/app.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const $companiesList = $('#companiesList');
            const $companyForm = $('#companyForm');
            const $modal = $('#companyModal');
            const $modalTitle = $('#modalTitle');
            const $deleteBtn = $('#deleteCompanyBtn');

            const deleteCompanyUrl = '<?= site_url("Page/deleteCompany"); ?>';
            const saveCompanyUrl = '<?= site_url("Page/saveCompany"); ?>';
            const generateActivationKeyUrl = '<?= site_url("Page/generateBranchActivationKey"); ?>';
            const superAdminAdminsUrl = '<?= site_url("Page/superAdminAdmins"); ?>';
            const superAdminCompanyFeaturesUrl = '<?= site_url("Page/superAdminCompanyFeatures"); ?>';
            const superAdminCompanyBillingUrl = '<?= site_url("Page/superAdminCompanyBilling"); ?>';
            const $billingMode = $('#billingMode');
            const $monthlyRate = $('#monthlyRate');
            const $monthlyRateHelp = $('#monthlyRateHelp');

            function updateBillingRateHelp() {
                const billingMode = $billingMode.val() || 'company';

                if (billingMode === 'individual') {
                    $monthlyRateHelp.text('This amount will be used as the per-user monthly charge for the company’s active internal users.');
                } else if (billingMode === 'free') {
                    $monthlyRateHelp.text('Free companies can keep this at 0.00 because no recurring charge will be generated.');
                } else {
                    $monthlyRateHelp.text('This amount will be charged once per month for the company.');
                }
            }

            function resetForm() {
                $companyForm[0].reset();
                $('#settingsID').val('');
                $billingMode.val('company');
                $monthlyRate.val('0.00');
                $modalTitle.text('Add Company');
                $deleteBtn.addClass('d-none');
                updateBillingRateHelp();
            }

            function loadCompany($button) {
                $('#settingsID').val($button.data('settings-id') || '');
                $('#compName').val($button.data('comp-name') || '');
                $('#businessName').val($button.data('business-name') || '');
                $('#compType').val($button.data('comp-type') || '');
                $('#compEmail').val($button.data('comp-email') || '');
                $('#compPhone').val($button.data('comp-phone') || '');
                $('#compAddress').val($button.data('comp-address') || '');
                $billingMode.val($button.data('billing-mode') || 'company');
                $monthlyRate.val($button.data('monthly-rate') || '0.00');
                $modalTitle.text('Edit Company');
                $deleteBtn.removeClass('d-none');
                updateBillingRateHelp();
                $modal.modal('show');
            }

            $('#addCompanyBtn').on('click', function() {
                resetForm();
                $modal.modal('show');
            });

            $(document).on('click', '.edit-company', function() {
                loadCompany($(this));
            });

            $(document).on('click', '.manage-admins', function() {
                const settingsID = $(this).data('settings-id');
                window.location.href = superAdminAdminsUrl + '?settingsID=' + encodeURIComponent(settingsID);
            });

            $(document).on('click', '.configure-features', function() {
                const settingsID = $(this).data('settings-id');
                window.location.href = superAdminCompanyFeaturesUrl + '?settingsID=' + encodeURIComponent(settingsID);
            });

            $(document).on('click', '.view-billing', function() {
                const settingsID = $(this).data('settings-id');
                window.location.href = superAdminCompanyBillingUrl + '?settingsID=' + encodeURIComponent(settingsID);
            });

            $(document).on('click', '.generate-activation-key', function() {
                const settingsID = $(this).data('settings-id');
                const notes = prompt('Optional note for this branch activation key:', '') || '';
                const expiresInDays = prompt('Days before expiration (blank means no expiration):', '') || '';

                $.post(generateActivationKeyUrl, {
                    settingsID: settingsID,
                    notes: notes,
                    expires_in_days: expiresInDays
                }, function(response) {
                    if (response.success) {
                        const expiryText = response.expires_at ? '\nExpires: ' + response.expires_at : '\nNo expiration date';
                        alert('Branch activation key:\n\n' + response.activation_key + expiryText + '\n\nShare this key with the business admin. It will be shown only once.');
                    } else {
                        alert(response.message || 'Error generating activation key');
                    }
                }, 'json').fail(function() {
                    alert('Error generating activation key');
                });
            });

            $(document).on('click', '.delete-company', function() {
                if (!confirm('Delete this company? This cannot be undone.')) return;
                const settingsID = $(this).data('settings-id');
                
                $.post(deleteCompanyUrl, { settingsID: settingsID }, function(response) {
                    if (response.success) {
                        location.reload();
                    } else {
                        alert(response.message || 'Error deleting company');
                    }
                }, 'json').fail(function() {
                    alert('Error deleting company');
                });
            });

            $('#saveCompanyBtn').on('click', function() {
                const formData = $companyForm.serialize();
                
                $.post(saveCompanyUrl, formData, function(response) {
                    if (response.success) {
                        $modal.modal('hide');
                        location.reload();
                    } else {
                        alert(response.message || 'Error saving company');
                    }
                }, 'json').fail(function() {
                    alert('Error saving company');
                });
            });

            $deleteBtn.on('click', function() {
                if (!confirm('Delete this company? This cannot be undone.')) return;
                const settingsID = $('#settingsID').val();
                
                $.post(deleteCompanyUrl, { settingsID: settingsID }, function(response) {
                    if (response.success) {
                        $modal.modal('hide');
                        location.reload();
                    } else {
                        alert(response.message || 'Error deleting company');
                    }
                }, 'json').fail(function() {
                    alert('Error deleting company');
                });
            });

            $billingMode.on('change', updateBillingRateHelp);
            $modal.on('hidden.bs.modal', resetForm);
            updateBillingRateHelp();
        });
    </script>
</body>
</html>
