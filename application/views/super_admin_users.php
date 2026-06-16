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

    .user-card {
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 16px;
        background: #fff;
        transition: all 0.2s;
    }

    .user-card:hover {
        box-shadow: 0 4px 12px rgba(0, 0, 0, .08);
    }

    .user-name {
        font-weight: 700;
        font-size: 16px;
        color: #111827;
        margin-bottom: 8px;
    }

    .user-meta {
        font-size: 13px;
        color: #6b7280;
        margin-bottom: 4px;
    }

    .status-badge {
        display: inline-block;
        padding: 4px 10px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 600;
    }

    .status-active {
        background: #d1fae5;
        color: #065f46;
    }

    .status-pending {
        background: #fef3c7;
        color: #92400e;
    }

    .status-inactive {
        background: #fee2e2;
        color: #991b1b;
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
                                    <h4 class="mb-1">Company Users</h4>
                                    <p class="text-muted mb-0">Manage all users for each company</p>
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
                                            <a href="<?= site_url('Page/superAdminUsers'); ?>" class="text-white ml-2" style="text-decoration: none;">
                                                <i class="mdi mdi-close"></i> Clear
                                            </a>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="d-flex" style="gap: 10px;">
                                    <select class="form-control" id="companyFilter" style="width: 250px;">
                                        <option value="">All Companies</option>
                                        <?php foreach ($companies as $company): ?>
                                        <option value="<?= (int) ($company->settingsID ?? 0); ?>" <?= (isset($filterSettingsID) && $filterSettingsID == $company->settingsID) ? 'selected' : ''; ?>>
                                            <?= htmlspecialchars((string) ($company->CompName ?? $company->BusinessName ?? 'Unknown'), ENT_QUOTES, 'UTF-8'); ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <button class="btn btn-primary" id="addUserBtn">
                                        <i class="mdi mdi-plus mr-1"></i> Add User
                                    </button>
                                </div>
                            </div>

                            <div id="usersList">
                                <?php if (!empty($users)): ?>
                                    <?php foreach ($users as $user): ?>
                                    <?php
                                    $statusClass = 'status-inactive';
                                    $statusText = 'Inactive';
                                    if ($user->acctStat === 'Active') {
                                        $statusClass = 'status-active';
                                        $statusText = 'Active';
                                    } elseif ($user->acctStat === 'Pending') {
                                        $statusClass = 'status-pending';
                                        $statusText = 'Pending';
                                    }
                                    ?>
                                    <div class="user-card" data-user-id="<?= (int) ($user->user_id ?? 0); ?>">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div>
                                                <div class="user-name">
                                                    <?= htmlspecialchars((string) ($user->fName ?? '') . ' ' . ($user->lName ?? ''), ENT_QUOTES, 'UTF-8'); ?>
                                                </div>
                                                <div class="user-meta">
                                                    <strong>Email:</strong> <?= htmlspecialchars((string) ($user->email ?? ''), ENT_QUOTES, 'UTF-8'); ?>
                                                </div>
                                                <div class="user-meta">
                                                    <strong>Position:</strong> <?= htmlspecialchars((string) ($user->position ?? ''), ENT_QUOTES, 'UTF-8'); ?>
                                                </div>
                                                <div class="user-meta">
                                                    <strong>Company:</strong> 
                                                    <?php 
                                                    $companyName = 'Unknown';
                                                    foreach ($companies as $company) {
                                                        if ($company->settingsID == $user->settingsID) {
                                                            $companyName = $company->CompName ?? $company->BusinessName ?? 'Unknown';
                                                            break;
                                                        }
                                                    }
                                                    echo htmlspecialchars($companyName, ENT_QUOTES, 'UTF-8');
                                                    ?>
                                                </div>
                                                <div class="user-meta">
                                                    <strong>Settings ID:</strong> <?= (int) ($user->settingsID ?? 0); ?>
                                                </div>
                                                <div class="user-meta">
                                                    <strong>Status:</strong> 
                                                    <span class="status-badge <?= $statusClass; ?>"><?= $statusText; ?></span>
                                                </div>
                                            </div>
                                            <div class="btn-group">
                                                <button class="btn btn-sm btn-outline-info edit-user" data-user-id="<?= (int) ($user->user_id ?? 0); ?>" title="Edit User">
                                                    <i class="mdi mdi-pencil"></i>
                                                </button>
                                                <button class="btn btn-sm btn-outline-danger delete-user" data-user-id="<?= (int) ($user->user_id ?? 0); ?>" title="Delete User">
                                                    <i class="mdi mdi-delete"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="text-center py-5 text-muted">
                                        <i class="mdi mdi-account-group" style="font-size: 48px; opacity: 0.3;"></i>
                                        <p class="mt-3">No users found.</p>
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

    <div class="modal fade" id="userModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <h5 class="modal-title mb-0" id="modalTitle">Edit User</h5>
                        <div class="small text-muted">Update user details</div>
                    </div>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>

                <div class="modal-body">
                    <form id="userForm">
                        <div class="form-group mb-3">
                            <label class="font-weight-bold">Company *</label>
                            <select class="form-control" name="settingsID" id="settingsID" required>
                                <option value="">Select Company</option>
                                <?php foreach ($companies as $company): ?>
                                <option value="<?= (int) ($company->settingsID ?? 0); ?>" <?= (isset($filterSettingsID) && $filterSettingsID == $company->settingsID) ? 'selected' : ''; ?>>
                                    <?= htmlspecialchars((string) ($company->CompName ?? $company->BusinessName ?? 'Unknown'), ENT_QUOTES, 'UTF-8'); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group mb-3">
                            <label class="font-weight-bold">Email (will be used as username) *</label>
                            <input type="email" class="form-control" name="email" id="email" required>
                        </div>
                        <div class="form-group mb-3">
                            <label class="font-weight-bold">Password <span id="passwordRequired">*</span></label>
                            <input type="password" class="form-control" name="password" id="password">
                        </div>
                        <div class="form-group mb-3">
                            <label class="font-weight-bold">First Name</label>
                            <input type="text" class="form-control" name="fName" id="fName">
                        </div>
                        <div class="form-group mb-3">
                            <label class="font-weight-bold">Last Name</label>
                            <input type="text" class="form-control" name="lName" id="lName">
                        </div>
                        <div class="form-group mb-3">
                            <label class="font-weight-bold">Position</label>
                            <select class="form-control" name="position" id="position">
                                <option value="Admin">Admin</option>
                                <option value="Staff" selected>Staff</option>
                                <option value="Encoder">Encoder</option>
                                <option value="POS Admin">POS Admin</option>
                                <option value="Manager">Manager</option>
                                <option value="POS Staff">POS Staff</option>
                                <option value="Cashier">Cashier</option>
                                <option value="Client">Client</option>
                            </select>
                        </div>
                        <div class="form-group mb-0">
                            <label class="font-weight-bold">Account Status</label>
                            <select class="form-control" name="acctStat" id="acctStat">
                                <option value="Active">Active</option>
                                <option value="Pending">Pending</option>
                                <option value="Inactive">Inactive</option>
                            </select>
                        </div>
                        <input type="hidden" name="user_id" id="userId">
                    </form>
                </div>

                <div class="modal-footer d-flex justify-content-between">
                    <button class="btn btn-light" data-dismiss="modal">Cancel</button>
                    <button id="saveUserBtn" class="btn btn-primary">
                        <i class="mdi mdi-content-save-outline mr-1"></i> Save User
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
            const $usersList = $('#usersList');
            const $userForm = $('#userForm');
            const $modal = $('#userModal');
            const $modalTitle = $('#modalTitle');
            const $companyFilter = $('#companyFilter');

            const users = <?php echo json_encode($users); ?>;

            function resetForm() {
                $userForm[0].reset();
                $('#userId').val('');
                $('#password').val('');
                $('#password').prop('required', true);
                $('#passwordRequired').text('*');
                <?php if (isset($filterSettingsID) && $filterSettingsID): ?>
                $('#settingsID').val(<?= $filterSettingsID; ?>);
                <?php endif; ?>
                $modalTitle.text('Add User');
            }

            function loadUser(userId) {
                const user = users.find(u => u.user_id == userId);
                
                if (user) {
                    $('#userId').val(user.user_id);
                    $('#settingsID').val(user.settingsID || '');
                    $('#email').val(user.email || '');
                    $('#password').val('');
                    $('#password').prop('required', false);
                    $('#passwordRequired').text('(leave blank to keep current)');
                    $('#fName').val(user.fName || '');
                    $('#lName').val(user.lName || '');
                    $('#position').val(user.position || 'Staff');
                    $('#acctStat').val(user.acctStat || 'Active');
                    $modalTitle.text('Edit User');
                    $modal.modal('show');
                }
            }

            $('#addUserBtn').on('click', function() {
                resetForm();
                $modal.modal('show');
            });

            $(document).on('click', '.edit-user', function() {
                const userId = $(this).data('user-id');
                loadUser(userId);
            });

            $(document).on('click', '.delete-user', function() {
                if (!confirm('Delete this user? This cannot be undone.')) return;
                const userId = $(this).data('user-id');
                
                $.post('<?= site_url("Page/deleteSuperAdminUser"); ?>', { user_id: userId }, function(response) {
                    if (response.success) {
                        location.reload();
                    } else {
                        alert(response.message || 'Error deleting user');
                    }
                }, 'json').fail(function() {
                    alert('Error deleting user');
                });
            });

            $('#saveUserBtn').on('click', function() {
                const formData = $userForm.serialize();
                
                $.post('<?= site_url("Page/saveSuperAdminUser"); ?>', formData, function(response) {
                    if (response.success) {
                        $modal.modal('hide');
                        location.reload();
                    } else {
                        alert(response.message || 'Error saving user');
                    }
                }, 'json').fail(function() {
                    alert('Error saving user');
                });
            });

            $companyFilter.on('change', function() {
                const settingsID = $(this).val();
                if (settingsID) {
                    window.location.href = '<?= site_url("Page/superAdminUsers"); ?>?settingsID=' + encodeURIComponent(settingsID);
                } else {
                    window.location.href = '<?= site_url("Page/superAdminUsers"); ?>';
                }
            });

            $modal.on('hidden.bs.modal', resetForm);
        });
    </script>
</body>
</html>
