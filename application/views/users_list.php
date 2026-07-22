<?php
$users = isset($users) && is_array($users) ? $users : [];

$flashSuccess = isset($users_notice_type) && $users_notice_type === 'success'
    ? $users_notice
    : $this->session->flashdata('users_success');
$flashError = isset($users_notice_type) && $users_notice_type === 'error'
    ? $users_notice
    : $this->session->flashdata('users_error');

// Get current user's level for role-based restrictions
$currentLevel = strtolower(trim((string) ($this->session->userdata('level') ?? '')));
$isPosAdmin = ($currentLevel === 'pos admin');
$canManagePosRoles = isset($canManagePosRoles) ? (bool) $canManagePosRoles : true;

$buildPositionOptions = static function ($isPosAdminView, $canManagePosRolesView, $currentPosition = '') {
    $currentPosition = trim((string) $currentPosition);
    $currentPositionKey = strtolower($currentPosition);
    $options = [];

    if ($isPosAdminView) {
        if ($canManagePosRolesView) {
            $options['Manager'] = 'Manager';
            $options['Cashier'] = 'Cashier';
        }

        if (in_array($currentPositionKey, ['pos admin', 'pos staff'], true)) {
            $options[$currentPosition] = $currentPosition;
        }

        return $options;
    }

    if ($currentPosition === 'Admin') {
        $options['Admin'] = 'Admin';
    }

    if ($canManagePosRolesView || $currentPositionKey === 'manager') {
        $options['Manager'] = 'Manager';
    }

    $options['Encoder'] = 'Encoder';
    $options['Staff'] = 'Staff';

    if ($canManagePosRolesView || $currentPositionKey === 'cashier') {
        $options['Cashier'] = 'Cashier';
    }

    if (in_array($currentPositionKey, ['pos admin', 'pos staff'], true)) {
        $options[$currentPosition] = $currentPosition;
    }

    return $options;
};

$newUserPositionOptions = $buildPositionOptions($isPosAdmin, $canManagePosRoles);

$totalUsers = count($users);
$adminCount = 0;
$staffCount = 0;
$posCount = 0;

foreach ($users as $user) {
    $positionValue = strtolower(trim((string) ($user->position ?? '')));
    if ($positionValue === 'admin') {
        $adminCount++;
    } elseif ($positionValue === 'staff' || $positionValue === 'encoder') {
        $staffCount++;
    } elseif (in_array($positionValue, ['manager', 'cashier', 'pos admin', 'pos staff'], true)) {
        $posCount++;
    }
}
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
                <div class="container-fluid users-management-page berps-page">

                    <?php if (!empty($flashSuccess)): ?>
                        <div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
                            <?= htmlspecialchars($flashSuccess, ENT_QUOTES, 'UTF-8'); ?>
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($flashError)): ?>
                        <div class="alert alert-danger alert-dismissible fade show mt-3" role="alert">
                            <?= htmlspecialchars($flashError, ENT_QUOTES, 'UTF-8'); ?>
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    <?php endif; ?>

                    <?php if (!$canManagePosRoles): ?>
                        <div class="alert alert-info alert-dismissible fade show mt-3" role="alert">
                            POS Manager and Cashier role creation is disabled for this company until <strong>Package 4: POS</strong> is enabled.
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    <?php endif; ?>

                    <?php
                    $resetDisplay = isset($reset_password_display) ? $reset_password_display : null;
                    if (!empty($resetDisplay) && is_array($resetDisplay)):
                        $resetUserName = htmlspecialchars((string) ($resetDisplay['user_name'] ?? ''), ENT_QUOTES, 'UTF-8');
                        $resetUsername = htmlspecialchars((string) ($resetDisplay['username'] ?? ''), ENT_QUOTES, 'UTF-8');
                        $resetTempPassword = htmlspecialchars((string) ($resetDisplay['temp_password'] ?? ''), ENT_QUOTES, 'UTF-8');
                        $resetEmail = htmlspecialchars((string) ($resetDisplay['email'] ?? ''), ENT_QUOTES, 'UTF-8');
                    ?>
                        <div class="alert alert-warning alert-dismissible fade show mt-3 berps-credential-notice" role="alert">
                            <i class="mdi mdi-lock-reset berps-credential-notice__icon" aria-hidden="true"></i>
                            <div class="berps-credential-notice__body">
                                    <strong class="berps-credential-notice__title">Password reset successful</strong>
                                    <div class="berps-credential-notice__details">
                                        <div><strong>User:</strong> <?= $resetUserName; ?></div>
                                        <div><strong>Username:</strong> <?= $resetUsername; ?></div>
                                        <div class="berps-credential-notice__password">
                                            <label for="resetTempPasswordField">Temporary password</label>
                                            <div class="berps-credential-notice__row">
                                                <input
                                                    type="text"
                                                    class="form-control"
                                                    id="resetTempPasswordField"
                                                    value="<?= $resetTempPassword; ?>"
                                                    readonly
                                                    onclick="this.select();"
                                                >
                                                <button
                                                    type="button"
                                                    class="btn btn-sm btn-outline-warning"
                                                    id="copyResetPasswordBtn"
                                                    data-password="<?= $resetTempPassword; ?>"
                                                >
                                                    Copy
                                                </button>
                                                <span id="copyResetPasswordStatus" class="berps-credential-notice__status" aria-live="polite"></span>
                                            </div>
                                        </div>
                                        <div class="berps-credential-notice__email">
                                            <i class="mdi mdi-email-check" aria-hidden="true"></i> Also emailed to: <?= $resetEmail; ?>
                                        </div>
                                    </div>
                            </div>
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    <?php endif; ?>


                    <div class="page-header">
                        <div>
                            <div class="page-eyebrow">Account Management</div>
                            <h1 class="page-title">User Accounts</h1>
                            <div class="page-subtitle">Create and manage staff access, account roles, and password resets from one place while keeping invoice and payment ownership properly separated.</div>
                        </div>
                        <div class="page-actions">
                            <button type="button" class="btn-submit" data-toggle="modal" data-target="#addUserModal">
                                <i class="mdi mdi-account-plus-outline"></i>
                                Add User
                            </button>
                        </div>
                    </div>

                    <div class="stats-grid">
                        <div class="stat-card stat-total">
                            <div class="stat-label">Total Accounts</div>
                            <div class="stat-value"><?= number_format($totalUsers); ?></div>
                            <div class="stat-meta">All users under the current company settings.</div>
                        </div>
                        <div class="stat-card stat-admin">
                            <div class="stat-label">Admin Accounts</div>
                            <div class="stat-value"><?= number_format($adminCount); ?></div>
                            <div class="stat-meta">Users with full management access.</div>
                        </div>
                        <div class="stat-card stat-staff">
                            <div class="stat-label">Operations and POS Roles</div>
                            <div class="stat-value"><?= number_format($staffCount + $posCount); ?></div>
                            <div class="stat-meta">Operational accounts for encoding and daily work.</div>
                        </div>
                    </div>

                    <div class="content-grid">
                        <div class="theme-card">
                            <div class="theme-card-head">
                                <h5 class="theme-card-title">Registered Accounts</h5>
                                <div class="theme-card-subtitle">Manage usernames, email addresses, positions, password resets, and account cleanup.</div>
                            </div>
                            <div class="theme-card-body">
                                <div class="table-responsive">
                                    <table id="users-table" class="table w-100">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>User</th>
                                                <th>Email</th>
                                                <th>Role</th>
                                                <th class="text-center">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (!empty($users)): ?>
                                                <?php foreach ($users as $user): ?>
                                                    <?php
                                                    $userId = isset($user->user_id) ? (string) $user->user_id : '';
                                                    $safeId = preg_replace('/[^A-Za-z0-9_-]/', '', $userId);
                                                    $modalId = 'editUserModal' . $safeId;
                                                    $username = isset($user->username) ? (string) $user->username : '';
                                                    $position = isset($user->position) ? (string) $user->position : '';
                                                    $email = isset($user->email) ? (string) $user->email : '';
                                                    $firstName = isset($user->fName) ? (string) $user->fName : '';
                                                    $middleName = isset($user->mName) ? (string) $user->mName : '';
                                                    $lastName = isset($user->lName) ? (string) $user->lName : '';
                                                    $fullName = trim($firstName . ' ' . $lastName);
                                                    $deleteUrl = site_url('users/delete/' . rawurlencode($userId));
                                                    $editAction = site_url('users/edit/' . rawurlencode($userId));
                                                    $positionKey = strtolower(trim($position));
                                                    $positionBadgeClass = 'role-default';
                                                    if ($positionKey === 'admin') {
                                                        $positionBadgeClass = 'role-admin';
                                                    } elseif ($positionKey === 'staff' || $positionKey === 'encoder') {
                                                        $positionBadgeClass = 'role-staff';
                                                    } elseif (in_array($positionKey, ['manager', 'cashier', 'pos admin', 'pos staff'], true)) {
                                                        $positionBadgeClass = 'role-pos';
                                                    }
                                                    ?>
                                                    <tr>
                                                        <td><?= htmlspecialchars($userId, ENT_QUOTES, 'UTF-8'); ?></td>
                                                        <td>
                                                            <div class="user-name"><?= htmlspecialchars($fullName !== '' ? $fullName : $username, ENT_QUOTES, 'UTF-8'); ?></div>
                                                            <div class="user-sub">@<?= htmlspecialchars($username, ENT_QUOTES, 'UTF-8'); ?></div>
                                                        </td>
                                                        <td><?= $email !== '' ? htmlspecialchars($email, ENT_QUOTES, 'UTF-8') : '<span class="text-muted">Not set</span>'; ?></td>
                                                        <td>
                                                            <span class="role-badge <?= htmlspecialchars($positionBadgeClass, ENT_QUOTES, 'UTF-8'); ?>">
                                                                <?= htmlspecialchars($position !== '' ? $position : 'Unassigned', ENT_QUOTES, 'UTF-8'); ?>
                                                            </span>
                                                        </td>
                                                        <td class="text-center">
                                                            <div class="action-stack">
                                                                <button type="button"
                                                                    class="action-btn action-edit"
                                                                    data-toggle="modal"
                                                                    data-target="#<?= htmlspecialchars($modalId, ENT_QUOTES, 'UTF-8'); ?>">
                                                                    <i class="mdi mdi-square-edit-outline"></i>
                                                                    Edit
                                                                </button>
                                                                <a class="action-btn action-reset"
                                                                    href="<?= site_url('users/reset_password/' . rawurlencode($userId)); ?>"
                                                                    data-berps-confirm="A new temporary password will be generated and emailed to this user."
                                                                    data-berps-confirm-title="Reset this password?"
                                                                    data-berps-confirm-label="Send password"
                                                                    data-berps-confirm-tone="primary">
                                                                    <i class="mdi mdi-lock-reset"></i>
                                                                    Reset Password
                                                                </a>
                                                                <a class="action-btn action-delete"
                                                                    href="<?= htmlspecialchars($deleteUrl, ENT_QUOTES, 'UTF-8'); ?>"
                                                                    data-berps-confirm="This permanently removes the user account. This action cannot be undone."
                                                                    data-berps-confirm-title="Delete user?"
                                                                    data-berps-confirm-label="Delete user">
                                                                    <i class="mdi mdi-trash-can-outline"></i>
                                                                    Delete
                                                                </a>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <tr>
                                                    <td colspan="5"><div class="berps-empty-state"><i class="mdi mdi-account-search-outline berps-empty-state__icon" aria-hidden="true"></i><span>No users recorded yet.</span></div></td>
                                                </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="side-stack">
                            <div class="theme-card">
                                <div class="theme-card-head">
                                    <h5 class="theme-card-title">Access Policy</h5>
                                    <div class="theme-card-subtitle">Current operational rules for invoice, job order, and payment encoding.</div>
                                </div>
                                <div class="theme-card-body">
                                    <div class="summary-chip">
                                        <i class="mdi mdi-shield-account-outline"></i>
                                        Staff restrictions active
                                    </div>
                                    <div class="policy-list">
                                        <div class="policy-item">
                                            <i class="fas fa-check-circle"></i>
                                            <span>Staff users can encode new Invoices, Job Orders, and Payments.</span>
                                        </div>
                                        <div class="policy-item">
                                            <i class="fas fa-user-check"></i>
                                            <span>Staff users only see invoice, job order, and payment records that they personally encoded.</span>
                                        </div>
                                        <div class="policy-item">
                                            <i class="fas fa-lock"></i>
                                            <span>Edit and delete actions for invoices, job orders, and payments remain restricted to admins only.</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="theme-card">
                                <div class="theme-card-head">
                                    <h5 class="theme-card-title">Role Snapshot</h5>
                                    <div class="theme-card-subtitle">A quick reference for the role distribution in this account list.</div>
                                </div>
                                <div class="theme-card-body">
                                    <div class="policy-list">
                                        <div class="policy-item">
                                            <i class="fas fa-user-shield"></i>
                                            <span><strong><?= number_format($adminCount); ?></strong>&nbsp;Admin account<?= $adminCount === 1 ? '' : 's'; ?> with full setup and maintenance access.</span>
                                        </div>
                                        <div class="policy-item">
                                            <i class="fas fa-user-tie"></i>
                                            <span><strong><?= number_format($staffCount); ?></strong>&nbsp;Encoder/Staff account<?= $staffCount === 1 ? '' : 's'; ?> focused on daily encoding and operations.</span>
                                        </div>
                                        <div class="policy-item">
                                            <i class="fas fa-cash-register"></i>
                                            <span><strong><?= number_format($posCount); ?></strong>&nbsp;Manager/Cashier POS role<?= $posCount === 1 ? '' : 's'; ?> reserved for branch and point-of-sale workflows.</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
            <?php include('includes/footer.php'); ?>
        </div>

        <?php include('includes/themecustomizer.php'); ?>

        <div class="modal fade users-management-page berps-form-modal" id="addUserModal" tabindex="-1" role="dialog" aria-labelledby="addUserModalTitle" aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <form method="post" action="<?= site_url('users/create'); ?>">
                    <div class="modal-content">
                        <div class="modal-header">
                            <div>
                                <h2 class="modal-title mb-0" id="addUserModalTitle">Add New User</h2>
                                <p class="berps-modal-subtitle">Set login credentials, role, and account details.</p>
                            </div>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="form-row">
                                <div class="form-group col-md-4">
                                    <label for="newUserEmail">Email / Username</label>
                                    <input type="email" name="email" id="newUserEmail" class="form-control" required>
                                </div>
                                <div class="form-group col-md-4">
                                    <label for="newUserPassword">Password</label>
                                    <input type="password" name="password" id="newUserPassword" class="form-control" required>
                                </div>
                                <div class="form-group col-md-4">
                                    <label for="newUserPosition">Position</label>
                                    <select name="position" id="newUserPosition" class="custom-select" required>
                                        <option value="">-- Select Position --</option>
                                        <?php foreach ($newUserPositionOptions as $positionValue => $positionLabel): ?>
                                            <option value="<?= htmlspecialchars((string) $positionValue, ENT_QUOTES, 'UTF-8'); ?>">
                                                <?= htmlspecialchars((string) $positionLabel, ENT_QUOTES, 'UTF-8'); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group col-md-4">
                                    <label for="newUserFirstName">First Name</label>
                                    <input type="text" name="fName" id="newUserFirstName" class="form-control">
                                </div>
                                <div class="form-group col-md-4">
                                    <label for="newUserMiddleName">Middle Name</label>
                                    <input type="text" name="mName" id="newUserMiddleName" class="form-control">
                                </div>
                                <div class="form-group col-md-4">
                                    <label for="newUserLastName">Last Name</label>
                                    <input type="text" name="lName" id="newUserLastName" class="form-control">
                                </div>
                            </div>
                            <input type="hidden" name="acctStat" value="active">
                            <input type="hidden" name="avatar" value="avatar.png">
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">
                                <i class="mdi mdi-content-save mr-1" aria-hidden="true"></i>Create User
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <?php if (!empty($users)): ?>
            <?php foreach ($users as $user): ?>
                <?php
                $userId = isset($user->user_id) ? (string) $user->user_id : '';
                $safeId = preg_replace('/[^A-Za-z0-9_-]/', '', $userId);
                $modalId = 'editUserModal' . $safeId;
                $username = isset($user->username) ? (string) $user->username : '';
                $position = isset($user->position) ? (string) $user->position : '';
                $email = isset($user->email) ? (string) $user->email : '';
                $firstName = isset($user->fName) ? (string) $user->fName : '';
                $middleName = isset($user->mName) ? (string) $user->mName : '';
                $lastName = isset($user->lName) ? (string) $user->lName : '';
                $editAction = site_url('users/edit/' . rawurlencode($userId));
                $editPositionOptions = $buildPositionOptions($isPosAdmin, $canManagePosRoles, $position);
                ?>
                <div class="modal fade users-management-page berps-form-modal" id="<?= htmlspecialchars($modalId, ENT_QUOTES, 'UTF-8'); ?>" tabindex="-1" role="dialog" aria-labelledby="<?= htmlspecialchars($modalId, ENT_QUOTES, 'UTF-8'); ?>Title" aria-hidden="true">
                    <div class="modal-dialog modal-lg" role="document">
                        <form method="post" action="<?= htmlspecialchars($editAction, ENT_QUOTES, 'UTF-8'); ?>">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <div>
                                        <h2 class="modal-title mb-0" id="<?= htmlspecialchars($modalId, ENT_QUOTES, 'UTF-8'); ?>Title">Edit User: <?= htmlspecialchars($username, ENT_QUOTES, 'UTF-8'); ?></h2>
                                        <p class="berps-modal-subtitle">Update role, login, and profile information.</p>
                                    </div>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <div class="form-row">
                                        <div class="form-group col-md-4">
                                            <label for="username<?= htmlspecialchars($safeId, ENT_QUOTES, 'UTF-8'); ?>">Username</label>
                                            <input type="text"
                                                name="username"
                                                id="username<?= htmlspecialchars($safeId, ENT_QUOTES, 'UTF-8'); ?>"
                                                class="form-control"
                                                value="<?= htmlspecialchars($username, ENT_QUOTES, 'UTF-8'); ?>"
                                                required>
                                        </div>
                                        <div class="form-group col-md-4">
                                            <label for="position<?= htmlspecialchars($safeId, ENT_QUOTES, 'UTF-8'); ?>">Position</label>
                                            <select name="position"
                                                id="position<?= htmlspecialchars($safeId, ENT_QUOTES, 'UTF-8'); ?>"
                                                class="custom-select"
                                                required>
                                                <option value="">-- Select Position --</option>
                                                <?php foreach ($editPositionOptions as $positionValue => $positionLabel): ?>
                                                    <option value="<?= htmlspecialchars((string) $positionValue, ENT_QUOTES, 'UTF-8'); ?>" <?= $position === $positionValue ? 'selected' : ''; ?>>
                                                        <?= htmlspecialchars((string) $positionLabel, ENT_QUOTES, 'UTF-8'); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="form-group col-md-4">
                                            <label for="email<?= htmlspecialchars($safeId, ENT_QUOTES, 'UTF-8'); ?>">Email</label>
                                            <input type="email"
                                                name="email"
                                                id="email<?= htmlspecialchars($safeId, ENT_QUOTES, 'UTF-8'); ?>"
                                                class="form-control"
                                                value="<?= htmlspecialchars($email, ENT_QUOTES, 'UTF-8'); ?>">
                                        </div>
                                    </div>
                                    <div class="form-row">
                                        <div class="form-group col-md-4">
                                            <label for="firstName<?= htmlspecialchars($safeId, ENT_QUOTES, 'UTF-8'); ?>">First Name</label>
                                            <input type="text"
                                                name="fName"
                                                id="firstName<?= htmlspecialchars($safeId, ENT_QUOTES, 'UTF-8'); ?>"
                                                class="form-control"
                                                value="<?= htmlspecialchars($firstName, ENT_QUOTES, 'UTF-8'); ?>">
                                        </div>
                                        <div class="form-group col-md-4">
                                            <label for="middleName<?= htmlspecialchars($safeId, ENT_QUOTES, 'UTF-8'); ?>">Middle Name</label>
                                            <input type="text"
                                                name="mName"
                                                id="middleName<?= htmlspecialchars($safeId, ENT_QUOTES, 'UTF-8'); ?>"
                                                class="form-control"
                                                value="<?= htmlspecialchars($middleName, ENT_QUOTES, 'UTF-8'); ?>">
                                        </div>
                                        <div class="form-group col-md-4">
                                            <label for="lastName<?= htmlspecialchars($safeId, ENT_QUOTES, 'UTF-8'); ?>">Last Name</label>
                                            <input type="text"
                                                name="lName"
                                                id="lastName<?= htmlspecialchars($safeId, ENT_QUOTES, 'UTF-8'); ?>"
                                                class="form-control"
                                                value="<?= htmlspecialchars($lastName, ENT_QUOTES, 'UTF-8'); ?>">
                                        </div>
                                    </div>
                                    <input type="hidden" name="acctStat" value="active">
                                    <input type="hidden" name="avatar" value="avatar.png">
                                    <input type="hidden" name="settingsID" value="1">
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Cancel</button>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="mdi mdi-content-save mr-1" aria-hidden="true"></i>Update User
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>

        <script src="<?= base_url(); ?>assets/js/vendor.min.js"></script>
        <script src="<?= base_url(); ?>assets/libs/moment/moment.min.js"></script>
        <script src="<?= base_url(); ?>assets/libs/jquery-scrollto/jquery.scrollTo.min.js"></script>
        <script src="<?= base_url(); ?>assets/libs/fullcalendar/fullcalendar.min.js"></script>
        <script src="<?= base_url(); ?>assets/js/pages/calendar.init.js"></script>
        <script src="<?= base_url(); ?>assets/js/app.min.js"></script>
        <script src="<?= base_url(); ?>assets/libs/jquery-ui/jquery-ui.min.js"></script>
        <script src="<?= base_url(); ?>assets/libs/datatables/jquery.dataTables.min.js"></script>
        <script src="<?= base_url(); ?>assets/libs/datatables/dataTables.bootstrap4.min.js"></script>
        <script src="<?= base_url(); ?>assets/libs/datatables/dataTables.buttons.min.js"></script>
        <script src="<?= base_url(); ?>assets/libs/datatables/buttons.bootstrap4.min.js"></script>
        <script src="<?= base_url(); ?>assets/libs/jszip/jszip.min.js"></script>
        <script src="<?= base_url(); ?>assets/libs/pdfmake/pdfmake.min.js"></script>
        <script src="<?= base_url(); ?>assets/libs/pdfmake/vfs_fonts.js"></script>
        <script src="<?= base_url(); ?>assets/libs/datatables/buttons.html5.min.js"></script>
        <script src="<?= base_url(); ?>assets/libs/datatables/buttons.print.min.js"></script>
        <script src="<?= base_url(); ?>assets/libs/datatables/dataTables.responsive.min.js"></script>
        <script src="<?= base_url(); ?>assets/libs/datatables/responsive.bootstrap4.min.js"></script>
        <script src="<?= base_url(); ?>assets/libs/datatables/dataTables.keyTable.min.js"></script>
        <script src="<?= base_url(); ?>assets/libs/datatables/dataTables.select.min.js"></script>
        <script>
            (function($) {
                'use strict';

                $(function() {
                    var $usersTable = $('#users-table');
                    var $copyResetPasswordBtn = $('#copyResetPasswordBtn');

                    if ($usersTable.length) {
                        $usersTable.DataTable({
                            responsive: true,
                            autoWidth: false,
                            order: [
                                [0, 'asc']
                            ],
                            columnDefs: [{
                                targets: -1,
                                orderable: false,
                                searchable: false
                            }]
                        });
                    }

                    $('#addUserModal').on('hidden.bs.modal', function() {
                        var form = this.querySelector('form');
                        if (form) {
                            form.reset();
                            form.classList.remove('was-validated');
                        }
                    });

                    $copyResetPasswordBtn.on('click', function() {
                        var passwordField = document.getElementById('resetTempPasswordField');
                        var copyStatus = document.getElementById('copyResetPasswordStatus');
                        var passwordValue = $(this).data('password') || (passwordField ? passwordField.value : '');

                        if (!passwordField || passwordValue === '') {
                            return;
                        }

                        passwordField.focus();
                        passwordField.select();
                        passwordField.setSelectionRange(0, passwordField.value.length);

                        var markCopied = function() {
                            if (copyStatus) {
                                copyStatus.textContent = 'Copied';
                            }
                        };

                        if (navigator.clipboard && window.isSecureContext) {
                            navigator.clipboard.writeText(passwordValue).then(markCopied).catch(function() {
                                document.execCommand('copy');
                                markCopied();
                            });
                            return;
                        }

                        document.execCommand('copy');
                        markCopied();
                    });
                });
            })(jQuery);
        </script>

</body>

</html>
