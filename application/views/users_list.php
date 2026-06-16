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
                <div class="container-fluid users-management-page">

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
                        <div class="alert alert-warning alert-dismissible fade show mt-3" role="alert" style="border-left: 4px solid #f59e0b;">
                            <div style="display: flex; align-items: flex-start; gap: 12px;">
                                <i class="mdi mdi-lock-reset" style="font-size: 24px; color: #f59e0b;"></i>
                                <div style="flex: 1;">
                                    <strong style="color: #92400e;">Password Reset Successful</strong>
                                    <div style="margin-top: 8px; padding: 12px; background: #fffbeb; border-radius: 6px; font-family: monospace; font-size: 14px;">
                                        <div><strong>User:</strong> <?= $resetUserName; ?></div>
                                        <div><strong>Username:</strong> <?= $resetUsername; ?></div>
                                        <div style="margin-top: 8px; padding: 10px; background: #fff; border: 1px dashed #f59e0b; border-radius: 4px;">
                                            <strong style="display: block; margin-bottom: 8px;">Temporary Password:</strong>
                                            <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                                                <input
                                                    type="text"
                                                    class="form-control"
                                                    id="resetTempPasswordField"
                                                    value="<?= $resetTempPassword; ?>"
                                                    readonly
                                                    onclick="this.select();"
                                                    style="max-width: 320px; font-size: 16px; color: #b45309; letter-spacing: 1px; background: #fffdf7; border: 1px solid #fcd34d;"
                                                >
                                                <button
                                                    type="button"
                                                    class="btn btn-sm btn-outline-warning"
                                                    id="copyResetPasswordBtn"
                                                    data-password="<?= $resetTempPassword; ?>"
                                                >
                                                    Copy
                                                </button>
                                                <span id="copyResetPasswordStatus" style="font-size: 12px; color: #92400e;"></span>
                                            </div>
                                        </div>
                                        <div style="margin-top: 8px; font-size: 12px; color: #92400e;">
                                            <i class="mdi mdi-email-check"></i> Also emailed to: <?= $resetEmail; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close" style="position: absolute; right: 12px; top: 12px;">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    <?php endif; ?>

                    <style>
                        @import url('https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,100..1000;1,9..40,100..1000&display=swap');

                        .users-management-page {
                            --bg: #f5f7fb;
                            --surface: rgba(255, 255, 255, 0.96);
                            --surface-strong: #ffffff;
                            --surface-soft: #f8fbff;
                            --line: #e4ebf4;
                            --line-strong: #cfdbea;
                            --text: #142235;
                            --text-soft: #617489;
                            --text-faint: #8ea0b5;
                            --primary: #2563eb;
                            --primary-2: #1d4ed8;
                            --primary-soft: #eaf2ff;
                            --success: #059669;
                            --success-soft: #ecfdf5;
                            --warning: #d97706;
                            --warning-soft: #fff7ed;
                            --danger: #e11d48;
                            --danger-soft: #fff1f2;
                            --shadow: 0 16px 40px rgba(15, 23, 42, 0.06);
                            --shadow-soft: 0 8px 24px rgba(15, 23, 42, 0.04);
                            --radius-xl: 16px;
                            --radius-lg: 12px;
                            --radius-md: 10px;
                            --radius-sm: 8px;
                            --font-body: 'DM Sans', 'Segoe UI', Arial, sans-serif;
                            --font-head: 'DM Sans', 'Segoe UI', Arial, sans-serif;
                            background:
                                radial-gradient(circle at top left, rgba(37, 99, 235, 0.08), transparent 28%),
                                radial-gradient(circle at top right, rgba(16, 185, 129, 0.08), transparent 24%),
                                linear-gradient(180deg, #f8fbff 0%, #f4f7fb 100%);
                            min-height: 100vh;
                            padding-bottom: 100px;
                            font-family: var(--font-body);
                        }

                        .users-management-page .content {
                            margin-bottom: 40px;
                        }

                        .users-management-page * {
                            box-sizing: border-box;
                        }

                        .users-management-page .page-header {
                            display: flex;
                            justify-content: space-between;
                            align-items: flex-end;
                            gap: 18px;
                            margin: 24px 0 22px;
                            flex-wrap: wrap;
                        }

                        .users-management-page .page-eyebrow {
                            display: inline-flex;
                            align-items: center;
                            gap: 8px;
                            padding: 7px 12px;
                            border-radius: 999px;
                            background: rgba(37, 99, 235, 0.08);
                            color: var(--primary-2);
                            font-size: 0.74rem;
                            font-weight: 700;
                            letter-spacing: 0.08em;
                            text-transform: uppercase;
                            margin-bottom: 12px;
                        }

                        .users-management-page .page-eyebrow::before {
                            content: '';
                            width: 8px;
                            height: 8px;
                            border-radius: 50%;
                            background: linear-gradient(135deg, var(--primary), var(--primary-2));
                            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.10);
                        }

                        .users-management-page .page-title {
                            margin: 0;
                            font-family: var(--font-head);
                            font-size: 2.1rem;
                            line-height: 1.05;
                            letter-spacing: -0.05em;
                            font-weight: 800;
                            color: var(--text);
                        }

                        .users-management-page .page-subtitle {
                            margin-top: 8px;
                            color: var(--text-soft);
                            font-size: 0.96rem;
                            max-width: 780px;
                        }

                        .users-management-page .page-actions {
                            display: flex;
                            gap: 12px;
                            flex-wrap: wrap;
                        }

                        .users-management-page .btn-action,
                        .users-management-page .btn-submit {
                            display: inline-flex;
                            align-items: center;
                            justify-content: center;
                            gap: 8px;
                            border-radius: 12px;
                            font-size: 0.92rem;
                            font-weight: 700;
                            padding: 11px 18px;
                            transition: all 0.16s ease;
                            text-decoration: none;
                        }

                        .users-management-page .btn-action {
                            border: 1px solid var(--line-strong);
                            color: var(--text);
                            background: #fff;
                        }

                        .users-management-page .btn-action:hover {
                            color: var(--primary);
                            border-color: #bfd3ef;
                            background: #f9fbff;
                        }

                        .users-management-page .btn-submit {
                            border: none;
                            color: #fff;
                            background: linear-gradient(135deg, var(--primary), var(--primary-2));
                            box-shadow: 0 10px 24px rgba(37, 99, 235, 0.24);
                        }

                        .users-management-page .btn-submit:hover {
                            transform: translateY(-1px);
                            box-shadow: 0 14px 28px rgba(37, 99, 235, 0.28);
                        }

                        .users-management-page .stats-grid {
                            display: grid;
                            grid-template-columns: repeat(3, minmax(0, 1fr));
                            gap: 16px;
                            margin-bottom: 20px;
                        }

                        .users-management-page .stat-card {
                            position: relative;
                            overflow: hidden;
                            background: var(--surface);
                            border: 1px solid rgba(255, 255, 255, 0.72);
                            border-radius: var(--radius-xl);
                            box-shadow: var(--shadow-soft);
                            padding: 18px 20px 20px;
                        }

                        .users-management-page .stat-card::before {
                            content: '';
                            position: absolute;
                            inset: 0 0 auto 0;
                            height: 4px;
                        }

                        .users-management-page .stat-total::before {
                            background: linear-gradient(90deg, #3b82f6, #60a5fa);
                        }

                        .users-management-page .stat-admin::before {
                            background: linear-gradient(90deg, #10b981, #34d399);
                        }

                        .users-management-page .stat-staff::before {
                            background: linear-gradient(90deg, #f59e0b, #fbbf24);
                        }

                        .users-management-page .stat-label {
                            color: var(--text-faint);
                            font-size: 0.74rem;
                            font-weight: 700;
                            text-transform: uppercase;
                            letter-spacing: 0.08em;
                            margin-bottom: 12px;
                        }

                        .users-management-page .stat-value {
                            color: var(--text);
                            font-size: 1.9rem;
                            font-weight: 800;
                            line-height: 1;
                            letter-spacing: -0.04em;
                        }

                        .users-management-page .stat-meta {
                            color: var(--text-soft);
                            font-size: 0.84rem;
                            margin-top: 7px;
                        }

                        .users-management-page .content-grid {
                            display: grid;
                            grid-template-columns: minmax(0, 1.65fr) minmax(320px, 0.95fr);
                            gap: 20px;
                            align-items: start;
                        }

                        .users-management-page .theme-card {
                            background: var(--surface);
                            border: 1px solid rgba(255, 255, 255, 0.72);
                            border-radius: var(--radius-xl);
                            box-shadow: var(--shadow);
                            overflow: hidden;
                        }

                        .users-management-page .theme-card-head {
                            padding: 18px 22px;
                            border-bottom: 1px solid var(--line);
                            background: linear-gradient(180deg, rgba(255, 255, 255, 0.98), rgba(249, 251, 255, 0.94));
                        }

                        .users-management-page .theme-card-title {
                            margin: 0;
                            color: var(--text);
                            font-size: 1.02rem;
                            font-weight: 800;
                            letter-spacing: -0.02em;
                        }

                        .users-management-page .theme-card-subtitle {
                            margin-top: 6px;
                            color: var(--text-soft);
                            font-size: 0.86rem;
                        }

                        .users-management-page .theme-card-body {
                            padding: 22px;
                        }

                        .users-management-page .side-stack {
                            display: grid;
                            gap: 20px;
                        }

                        .users-management-page .side-stack .theme-card {
                            position: sticky;
                            top: 88px;
                        }

                        .users-management-page .role-badge {
                            display: inline-flex;
                            align-items: center;
                            gap: 6px;
                            padding: 6px 10px;
                            border-radius: 999px;
                            font-size: 0.78rem;
                            font-weight: 700;
                            line-height: 1;
                        }

                        .users-management-page .role-admin {
                            background: var(--success-soft);
                            color: var(--success);
                        }

                        .users-management-page .role-staff {
                            background: var(--warning-soft);
                            color: var(--warning);
                        }

                        .users-management-page .role-pos {
                            background: var(--primary-soft);
                            color: var(--primary-2);
                        }

                        .users-management-page .role-default {
                            background: #f1f5f9;
                            color: var(--text-soft);
                        }

                        .users-management-page .user-name {
                            color: var(--text);
                            font-weight: 700;
                        }

                        .users-management-page .user-sub {
                            color: var(--text-soft);
                            font-size: 0.84rem;
                            margin-top: 3px;
                        }

                        .users-management-page .table {
                            margin-bottom: 0;
                        }

                        .users-management-page .table thead th {
                            border-top: none;
                            border-bottom: 1px solid var(--line);
                            color: var(--text-faint);
                            font-size: 0.72rem;
                            font-weight: 800;
                            letter-spacing: 0.08em;
                            text-transform: uppercase;
                            white-space: nowrap;
                        }

                        .users-management-page .table td {
                            vertical-align: middle;
                            border-color: var(--line);
                        }

                        .users-management-page .table-responsive {
                            overflow-x: auto;
                        }

                        .users-management-page .action-stack {
                            display: flex;
                            gap: 8px;
                            flex-wrap: wrap;
                            justify-content: center;
                        }

                        .users-management-page .action-btn {
                            display: inline-flex;
                            align-items: center;
                            gap: 6px;
                            padding: 8px 12px;
                            border-radius: 10px;
                            font-size: 0.78rem;
                            font-weight: 700;
                            text-decoration: none;
                            border: 1px solid transparent;
                            transition: all 0.15s ease;
                        }

                        .users-management-page .action-btn:hover {
                            transform: translateY(-1px);
                            text-decoration: none;
                        }

                        .users-management-page .action-edit {
                            background: #eff6ff;
                            color: var(--primary-2);
                            border-color: #bfdbfe;
                        }

                        .users-management-page .action-reset {
                            background: #fffbeb;
                            color: #92400e;
                            border-color: #fde68a;
                        }

                        .users-management-page .action-delete {
                            background: #fef2f2;
                            color: #b91c1c;
                            border-color: #fecaca;
                        }

                        .users-management-page .policy-list {
                            display: grid;
                            gap: 12px;
                        }

                        .users-management-page .policy-item {
                            display: flex;
                            gap: 10px;
                            align-items: flex-start;
                            color: var(--text-soft);
                            font-size: 0.88rem;
                            line-height: 1.55;
                        }

                        .users-management-page .policy-item i {
                            color: var(--primary);
                            margin-top: 3px;
                        }

                        .users-management-page .summary-chip {
                            display: inline-flex;
                            align-items: center;
                            gap: 8px;
                            padding: 8px 12px;
                            border-radius: 999px;
                            border: 1px solid #dbeafe;
                            background: var(--primary-soft);
                            color: var(--primary-2);
                            font-size: 0.8rem;
                            font-weight: 700;
                            margin-bottom: 12px;
                        }

                        .users-management-page .modal-content {
                            border: none;
                            border-radius: 20px;
                            overflow: hidden;
                            box-shadow: 0 24px 60px rgba(15, 23, 42, 0.16);
                        }

                        .users-management-page .modal-header {
                            background: linear-gradient(135deg, var(--primary), var(--primary-2));
                            color: #fff;
                            border-bottom: none;
                            padding: 18px 22px;
                        }

                        .users-management-page .modal-header .close {
                            color: #fff;
                            opacity: 0.85;
                        }

                        .users-management-page .modal-body {
                            padding: 22px;
                        }

                        .users-management-page .modal-footer {
                            border-top: 1px solid var(--line);
                            padding: 16px 22px;
                        }

                        .users-management-page label {
                            color: var(--text);
                            font-size: 0.82rem;
                            font-weight: 700;
                            letter-spacing: 0.02em;
                            margin-bottom: 8px;
                        }

                        .users-management-page .form-control,
                        .users-management-page .custom-select {
                            border: 1px solid var(--line-strong);
                            border-radius: var(--radius-sm);
                            min-height: 46px;
                            color: var(--text);
                            box-shadow: none;
                        }

                        .users-management-page .form-control:focus,
                        .users-management-page .custom-select:focus {
                            border-color: #9cc0f5;
                            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
                        }

                        .users-management-page .dataTables_wrapper .dataTables_filter input,
                        .users-management-page .dataTables_wrapper .dataTables_length select {
                            border: 1px solid var(--line-strong);
                            border-radius: 10px;
                            min-height: 38px;
                            padding: 6px 10px;
                            background: #fff;
                        }

                        .users-management-page .dataTables_wrapper .dataTables_paginate .paginate_button {
                            border-radius: 8px !important;
                        }

                        @media (max-width: 1199px) {

                            .users-management-page .stats-grid,
                            .users-management-page .content-grid {
                                grid-template-columns: 1fr;
                            }

                            .users-management-page .side-stack .theme-card {
                                position: static;
                            }
                        }

                        @media (max-width: 767px) {
                            .users-management-page .page-title {
                                font-size: 1.75rem;
                            }

                            .users-management-page .theme-card-head,
                            .users-management-page .theme-card-body,
                            .users-management-page .modal-body,
                            .users-management-page .modal-footer {
                                padding-left: 16px;
                                padding-right: 16px;
                            }

                            .users-management-page .action-stack {
                                justify-content: flex-start;
                            }
                        }
                    </style>

                    <div class="page-header">
                        <div>
                            <div class="page-eyebrow">Account Management</div>
                            <h4 class="page-title">User Accounts</h4>
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
                                                                    onclick="return confirm('Email a temporary password to this user?');">
                                                                    <i class="mdi mdi-lock-reset"></i>
                                                                    Reset Password
                                                                </a>
                                                                <a class="action-btn action-delete"
                                                                    href="<?= htmlspecialchars($deleteUrl, ENT_QUOTES, 'UTF-8'); ?>"
                                                                    onclick="return confirm('Delete this user?');">
                                                                    <i class="mdi mdi-trash-can-outline"></i>
                                                                    Delete
                                                                </a>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <tr>
                                                    <td colspan="5" class="text-center text-muted py-4">No users recorded yet.</td>
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

                    <?php include('includes/footer.php'); ?>
                </div>
            </div>
        </div>

        <?php include('includes/themecustomizer.php'); ?>

        <div class="modal fade users-management-page" id="addUserModal" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <form method="post" action="<?= site_url('users/create'); ?>">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title mb-0">Add New User</h5>
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
                            <button type="submit" class="btn-submit">
                                <i class="mdi mdi-content-save"></i>
                                Create User
                            </button>
                            <button type="button" class="btn-action" data-dismiss="modal">Cancel</button>
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
                <div class="modal fade users-management-page" id="<?= htmlspecialchars($modalId, ENT_QUOTES, 'UTF-8'); ?>" tabindex="-1" role="dialog" aria-hidden="true">
                    <div class="modal-dialog modal-lg" role="document">
                        <form method="post" action="<?= htmlspecialchars($editAction, ENT_QUOTES, 'UTF-8'); ?>">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title mb-0">Edit User: <?= htmlspecialchars($username, ENT_QUOTES, 'UTF-8'); ?></h5>
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
                                    <button type="submit" class="btn-submit">
                                        <i class="mdi mdi-content-save"></i>
                                        Update User
                                    </button>
                                    <button type="button" class="btn-action" data-dismiss="modal">Cancel</button>
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
