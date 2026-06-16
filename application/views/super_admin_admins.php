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

    .admin-card {
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 16px;
        background: #fff;
        transition: all 0.2s;
    }

    .admin-card:hover {
        box-shadow: 0 4px 12px rgba(0, 0, 0, .08);
    }

    .admin-name {
        font-weight: 700;
        font-size: 16px;
        color: #111827;
        margin-bottom: 8px;
    }

    .admin-meta {
        font-size: 13px;
        color: #6b7280;
        margin-bottom: 4px;
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
                                    <h4 class="mb-1">Company Admins</h4>
                                    <p class="text-muted mb-0">Assign and manage admin users for each company</p>
                                    <?php if (isset($filterSettingsID) && $filterSettingsID): ?>
                                        <?php 
                                        $filteredCompanyName = 'Unknown';
                                        foreach ($companies as $company) {
                                            if ($company->settingsID == $filterSettingsID) {
                                                $filteredCompanyName = $company->CompName ?? $company->BusinessName ?? 'Unknown';
                                                break;
                                            }
                                        }
                                        ?>
                                        <div class="badge badge-info mt-2">
                                            <i class="mdi mdi-filter"></i> Filtering by: <?= htmlspecialchars($filteredCompanyName, ENT_QUOTES, 'UTF-8'); ?> (ID: <?= $filterSettingsID; ?>)
                                            <a href="<?= site_url('Page/superAdminAdmins'); ?>" class="text-white ml-2" style="text-decoration: none;">
                                                <i class="mdi mdi-close"></i> Clear
                                            </a>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <button class="btn btn-primary" id="addAdminBtn">
                                    <i class="mdi mdi-plus mr-1"></i> Assign Admin
                                </button>
                            </div>

                            <div id="adminsList">
                                <?php if (!empty($admins)): ?>
                                    <?php foreach ($admins as $admin): ?>
                                    <div class="admin-card" data-user-id="<?= (int) ($admin->user_id ?? 0); ?>">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div>
                                                <div class="admin-name">
                                                    <?= htmlspecialchars((string) ($admin->fName ?? '') . ' ' . ($admin->lName ?? ''), ENT_QUOTES, 'UTF-8'); ?>
                                                </div>
                                                <div class="admin-meta">
                                                    <strong>Username:</strong> <?= htmlspecialchars((string) ($admin->username ?? ''), ENT_QUOTES, 'UTF-8'); ?>
                                                </div>
                                                <div class="admin-meta">
                                                    <strong>Email:</strong> <?= htmlspecialchars((string) ($admin->email ?? ''), ENT_QUOTES, 'UTF-8'); ?>
                                                </div>
                                                <div class="admin-meta">
                                                    <strong>Company:</strong> 
                                                    <?php 
                                                    $companyName = 'Unknown';
                                                    foreach ($companies as $company) {
                                                        if ($company->settingsID == $admin->settingsID) {
                                                            $companyName = $company->CompName ?? $company->BusinessName ?? 'Unknown';
                                                            break;
                                                        }
                                                    }
                                                    echo htmlspecialchars($companyName, ENT_QUOTES, 'UTF-8');
                                                    ?>
                                                </div>
                                                <div class="admin-meta">
                                                    <strong>Settings ID:</strong> <?= (int) ($admin->settingsID ?? 0); ?>
                                                </div>
                                            </div>
                                            <div class="btn-group">
                                                <button class="btn btn-sm btn-outline-info edit-admin" data-user-id="<?= (int) ($admin->user_id ?? 0); ?>">
                                                    <i class="mdi mdi-pencil"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="text-center py-5 text-muted">
                                        <i class="mdi mdi-account-key" style="font-size: 48px; opacity: 0.3;"></i>
                                        <p class="mt-3">No company admins found. Click "Assign Admin" to create one.</p>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <hr class="my-4">

                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <div>
                                    <h4 class="mb-1">Company Features</h4>
                                    <p class="text-muted mb-0">Enable or disable features for each company</p>
                                </div>
                            </div>

                            <div id="featuresList">
                                <?php if (!empty($companies)): ?>
                                    <?php foreach ($companies as $company): ?>
                                    <div class="admin-card" data-settings-id="<?= (int) ($company->settingsID ?? 0); ?>">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div>
                                                <div class="admin-name">
                                                    <?= htmlspecialchars((string) ($company->CompName ?? $company->BusinessName ?? 'Unknown'), ENT_QUOTES, 'UTF-8'); ?>
                                                </div>
                                                <div class="admin-meta">
                                                    <strong>Settings ID:</strong> <?= (int) ($company->settingsID ?? 0); ?>
                                                </div>
                                            </div>
                                            <button class="btn btn-sm btn-primary configure-features" data-settings-id="<?= (int) ($company->settingsID ?? 0); ?>">
                                                <i class="mdi mdi-cog"></i> Configure Features
                                            </button>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="text-center py-5 text-muted">
                                        <i class="mdi mdi-domain" style="font-size: 48px; opacity: 0.3;"></i>
                                        <p class="mt-3">No companies found.</p>
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

    <div class="modal fade" id="adminModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <h5 class="modal-title mb-0" id="modalTitle">Assign Admin</h5>
                        <div class="small text-muted">Configure admin details and company assignment</div>
                    </div>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>

                <div class="modal-body">
                    <form id="adminForm">
                        <div class="form-group mb-3">
                            <label class="font-weight-bold">Company *</label>
                            <select class="form-control" name="settingsID" id="settingsID" required>
                                <option value="">Select Company</option>
                                <?php foreach ($companies as $company): ?>
                                <option value="<?= (int) ($company->settingsID ?? 0); ?>" <?= (isset($filterSettingsID) && $filterSettingsID == $company->settingsID) ? 'selected' : ''; ?>>
                                    <?= htmlspecialchars((string) ($company->CompName ?? $company->BusinessName ?? 'Unknown'), ENT_QUOTES, 'UTF-8'); ?>
                                    (ID: <?= (int) ($company->settingsID ?? 0); ?>)
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group mb-3">
                            <label class="font-weight-bold">Username *</label>
                            <input type="text" class="form-control" name="username" id="username" required>
                        </div>
                        <div class="form-group mb-3">
                            <label class="font-weight-bold">Email *</label>
                            <input type="email" class="form-control" name="email" id="email" required>
                        </div>
                        <div class="form-group mb-3">
                            <label class="font-weight-bold">Password <?php if (isset($admin)): ?>(leave blank to keep current)<?php endif; ?></label>
                            <input type="password" class="form-control" name="password" id="password" <?php if (!isset($admin)): ?>required<?php endif; ?>>
                        </div>
                        <div class="form-group mb-3">
                            <label class="font-weight-bold">First Name</label>
                            <input type="text" class="form-control" name="fName" id="fName">
                        </div>
                        <div class="form-group mb-0">
                            <label class="font-weight-bold">Last Name</label>
                            <input type="text" class="form-control" name="lName" id="lName">
                        </div>
                        <input type="hidden" name="admin_user_id" id="adminUserId">
                    </form>
                </div>

                <div class="modal-footer d-flex justify-content-between">
                    <button class="btn btn-light" data-dismiss="modal">Cancel</button>
                    <button id="saveAdminBtn" class="btn btn-primary">
                        <i class="mdi mdi-content-save-outline mr-1"></i> Save Admin
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="featuresModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <h5 class="modal-title mb-0" id="featuresModalTitle">Configure Features</h5>
                        <div class="small text-muted" id="featuresModalSubtitle">Enable or disable features for this company</div>
                    </div>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>

                <div class="modal-body">
                    <form id="featuresForm">
                        <input type="hidden" name="settingsID" id="featuresSettingsID">
                        <div id="featuresCheckboxes">
                            <!-- Features will be loaded dynamically -->
                        </div>
                    </form>
                </div>

                <div class="modal-footer d-flex justify-content-between">
                    <button class="btn btn-light" data-dismiss="modal">Cancel</button>
                    <button id="saveFeaturesBtn" class="btn btn-primary">
                        <i class="mdi mdi-content-save-outline mr-1"></i> Save Features
                    </button>
                </div>
            </div>
        </div>
    </div>

    <?php include('includes/themecustomizer.php'); ?>
    <script src="<?= base_url(); ?>assets/js/vendor.min.js"></script>
    <script src="<?= base_url(); ?>assets/js/app.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const $adminsList = $('#adminsList');
            const $adminForm = $('#adminForm');
            const $modal = $('#adminModal');
            const $modalTitle = $('#modalTitle');
            const $featuresModal = $('#featuresModal');
            const $featuresForm = $('#featuresForm');
            const $featuresCheckboxes = $('#featuresCheckboxes');

            const admins = <?php echo json_encode($admins); ?>;
            const companies = <?php echo json_encode($companies); ?>;

            // Define available features
            const availableFeatures = [
                { key: 'calendar', name: 'Calendar Module' },
                { key: 'projects', name: 'Project Management' },
                { key: 'tasks', name: 'Task Management' },
                { key: 'support', name: 'Support Tickets' },
                { key: 'knowledge_base', name: 'Knowledge Base' },
                { key: 'reports', name: 'Reports & Analytics' },
                { key: 'time_tracking', name: 'Time Tracking' },
                { key: 'inventory', name: 'Inventory Management' },
                { key: 'hr', name: 'HR Module' },
                { key: 'finance', name: 'Finance Module' }
            ];

            function resetForm() {
                $adminForm[0].reset();
                $('#adminUserId').val('');
                $('#password').prop('required', true);
                $modalTitle.text('Assign Admin');
            }

            function loadAdmin(userId) {
                const admin = admins.find(a => a.user_id == userId);
                
                if (admin) {
                    $('#adminUserId').val(admin.user_id);
                    $('#settingsID').val(admin.settingsID || '');
                    $('#username').val(admin.username || '');
                    $('#email').val(admin.email || '');
                    $('#password').val('');
                    $('#password').prop('required', false);
                    $('#fName').val(admin.fName || '');
                    $('#lName').val(admin.lName || '');
                    $modalTitle.text('Edit Admin');
                    $modal.modal('show');
                }
            }

            function loadFeatures(settingsID) {
                console.log('loadFeatures called with settingsID:', settingsID);
                const company = companies.find(c => c.settingsID == settingsID);
                
                console.log('Company found:', company);
                
                if (company) {
                    $('#featuresSettingsID').val(settingsID);
                    $('#featuresModalTitle').text('Configure Features');
                    $('#featuresModalSubtitle').text(company.CompName || company.BusinessName || 'Unknown');
                    
                    console.log('Loading features from server...');
                    
                    // Load current feature settings
                    $.get('<?= site_url("Page/getCompanyFeatures"); ?>', { settingsID: settingsID }, function(response) {
                        console.log('Server response:', response);
                        const enabledFeatures = response.data || [];
                        
                        $featuresCheckboxes.empty();
                        availableFeatures.forEach(feature => {
                            const isEnabled = enabledFeatures.includes(feature.key);
                            const checkboxHtml = `
                                <div class="custom-control custom-switch mb-3">
                                    <input type="checkbox" class="custom-control-input" 
                                           id="feature_${feature.key}" 
                                           name="features[]" 
                                           value="${feature.key}"
                                           ${isEnabled ? 'checked' : ''}>
                                    <label class="custom-control-label" for="feature_${feature.key}">
                                        ${feature.name}
                                    </label>
                                </div>
                            `;
                            $featuresCheckboxes.append(checkboxHtml);
                        });
                        
                        console.log('Showing modal...');
                        $featuresModal.modal('show');
                    }, 'json').fail(function(xhr, status, error) {
                        console.error('Error loading features:', xhr, status, error);
                        const errorMessage = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : error;
                        alert('Error loading features: ' + errorMessage);
                    });
                } else {
                    console.error('Company not found for settingsID:', settingsID);
                }
            }

            $('#addAdminBtn').on('click', function() {
                resetForm();
                $modal.modal('show');
            });

            $(document).on('click', '.edit-admin', function() {
                const userId = $(this).data('user-id');
                loadAdmin(userId);
            });

            $(document).on('click', '.configure-features', function() {
                const settingsID = $(this).data('settings-id');
                loadFeatures(settingsID);
            });

            $('#saveAdminBtn').on('click', function() {
                const formData = $adminForm.serialize();
                
                $.post('<?= site_url("Page/assignCompanyAdmin"); ?>', formData, function(response) {
                    if (response.success) {
                        $modal.modal('hide');
                        location.reload();
                    } else {
                        alert(response.message || 'Error saving admin');
                    }
                }, 'json').fail(function() {
                    alert('Error saving admin');
                });
            });

            $('#saveFeaturesBtn').on('click', function() {
                const formData = $featuresForm.serialize();
                
                $.post('<?= site_url("Page/saveCompanyFeatures"); ?>', formData, function(response) {
                    if (response.success) {
                        $featuresModal.modal('hide');
                        alert('Features saved successfully');
                    } else {
                        alert(response.message || 'Error saving features');
                    }
                }, 'json').fail(function() {
                    alert('Error saving features');
                });
            });

            $modal.on('hidden.bs.modal', resetForm);
        });
    </script>
</body>
</html>
