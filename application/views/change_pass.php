<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<!DOCTYPE html>
<html lang="en">

<?php include('includes/head.php'); ?>

<body>
    <div id="wrapper">
        <?php include('includes/top-nav-bar.php'); ?>
        <?php include('includes/sidebar.php'); ?>

        <div class="content-page">
            <div class="content">
                <div class="container-fluid change-password-page">
                    <style>
                        .change-password-page {
                            --ink: #0f172a;
                            --muted: #6b7280;
                            --accent: #2563eb;
                            --accent-2: #7c3aed;
                            --border: #e5e7eb;
                            --shadow: 0 22px 48px rgba(15, 23, 42, 0.12);
                            padding-top: 12px;
                        }

                        .change-password-page .breadcrumb {
                            background: transparent;
                            padding: 0;
                            margin-bottom: 10px;
                        }

                        .change-password-page .page-hero {
                            border-radius: 18px;
                            background: radial-gradient(circle at 18% 20%, rgba(37, 99, 235, 0.14), transparent 32%),
                                radial-gradient(circle at 82% 0%, rgba(124, 58, 237, 0.18), transparent 30%),
                                linear-gradient(120deg, #1f2937, #111827);
                            color: #e5e7eb;
                            padding: 20px 24px;
                            position: relative;
                            overflow: hidden;
                            box-shadow: var(--shadow);
                        }

                        .change-password-page .page-hero::after {
                            content: '';
                            position: absolute;
                            inset: 14px;
                            border: 1px solid rgba(255, 255, 255, 0.08);
                            border-radius: 14px;
                            pointer-events: none;
                        }

                        .change-password-page .page-hero h4 {
                            color: #fff;
                            margin-bottom: 4px;
                            letter-spacing: -0.02em;
                        }

                        .change-password-page .eyebrow {
                            text-transform: uppercase;
                            font-weight: 700;
                            letter-spacing: 0.08em;
                            color: rgba(255, 255, 255, 0.75);
                            font-size: 11px;
                        }

                        .change-password-page .hero-pill {
                            display: inline-flex;
                            align-items: center;
                            gap: 8px;
                            background: rgba(255, 255, 255, 0.08);
                            color: #e5e7eb;
                            padding: 9px 14px;
                            border-radius: 999px;
                            border: 1px solid rgba(255, 255, 255, 0.08);
                            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.12);
                        }

                        .change-password-page .card {
                            border: none;
                            border-radius: 18px;
                            box-shadow: var(--shadow);
                        }

                        .change-password-page .card-header {
                            background: #fff;
                            border-bottom: 1px solid rgba(15, 23, 42, 0.06);
                            padding: 16px 20px;
                        }

                        .change-password-page .card-body {
                            padding: 22px;
                        }

                        .change-password-page .section-title {
                            font-weight: 700;
                            color: var(--ink);
                            margin: 0;
                            letter-spacing: -0.01em;
                        }

                        .change-password-page .input-group-text {
                            background: #f4f6fb;
                            border: 1px solid var(--border);
                            border-right: 0;
                            color: #4b5563;
                        }

                        .change-password-page .form-control {
                            border: 1px solid var(--border);
                            border-radius: 12px;
                            height: 48px;
                            background: #fbfdff;
                            box-shadow: none;
                            transition: border-color 0.2s ease, box-shadow 0.2s ease, background 0.2s ease;
                        }

                        .change-password-page .form-control:focus {
                            border-color: rgba(37, 99, 235, 0.75);
                            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.18);
                            background: #fff;
                        }

                        .change-password-page .toggle-password {
                            border: 1px solid var(--border);
                            border-left: 0;
                            background: #f8fafc;
                            color: #4b5563;
                            padding: 0 12px;
                        }

                        .change-password-page .helper {
                            color: var(--muted);
                            font-size: 13px;
                        }

                        .change-password-page .alert {
                            border-radius: 12px;
                            border: 1px solid rgba(220, 38, 38, 0.12);
                        }

                        .change-password-page .alert > .d-flex {
                            gap: 12px;
                        }

                        .change-password-page .checklist {
                            list-style: none;
                            padding: 0;
                            margin: 0;
                        }

                        .change-password-page .checklist li {
                            display: flex;
                            align-items: center;
                            gap: 8px;
                            padding: 8px 0;
                            color: var(--muted);
                        }

                        .change-password-page .side-card {
                            background: linear-gradient(130deg, rgba(37, 99, 235, 0.12), rgba(124, 58, 237, 0.1));
                        }

                        .change-password-page .icon-pill {
                            width: 44px;
                            height: 44px;
                            border-radius: 14px;
                            display: inline-flex;
                            align-items: center;
                            justify-content: center;
                            background: rgba(37, 99, 235, 0.1);
                            color: var(--accent);
                            font-size: 20px;
                        }

                        .change-password-page .action-row {
                            display: flex;
                            flex-wrap: wrap;
                            gap: 10px;
                            justify-content: space-between;
                            align-items: center;
                            margin-top: 8px;
                        }

                        @media (max-width: 767px) {
                            .change-password-page .card-header,
                            .change-password-page .card-body {
                                padding: 16px;
                            }

                            .change-password-page .page-hero {
                                padding: 18px;
                            }
                        }
                    </style>

                    <?php
                    $level = strtolower((string) $this->session->userdata('level'));
                    $homeRoute = 'Page/admin';

                    if ($level === 'staff' || $level === 'encoder') {
                        $homeRoute = 'Page/staff';
                    } elseif ($level === 'pos admin' || $level === 'manager') {
                        $homeRoute = 'Pos/posAdmin';
                    } elseif ($level === 'pos staff' || $level === 'cashier') {
                        $homeRoute = 'Pos/posStaff';
                    } elseif ($level === 'super admin' || $level === 'system administrator') {
                        $homeRoute = 'Page/superAdmin';
                    }

                    $displayName = trim((string) $this->session->userdata('fname') . ' ' . $this->session->userdata('lname'));
                    $displayName = $displayName !== '' ? $displayName : (string) $this->session->userdata('username');
                    $safeDisplayName = htmlspecialchars($displayName, ENT_QUOTES, 'UTF-8');
                    $safeUsername = htmlspecialchars((string) $this->session->userdata('username'), ENT_QUOTES, 'UTF-8');
                    $validationList = validation_errors('<li class="mb-1">', '</li>');
                    $flashMessage = $this->session->flashdata('msg');
                    ?>

                    <div class="row">
                        <div class="col-12">
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb pl-0">
                                    <li class="breadcrumb-item">
                                        <a href="<?= base_url(); ?><?= $homeRoute; ?>"><i class="mdi mdi-home-outline"></i> Dashboard</a>
                                    </li>
                                    <li class="breadcrumb-item active" aria-current="page">Change Password</li>
                                </ol>
                            </nav>
                        </div>
                    </div>

                    <div class="page-hero mb-3">
                        <div class="d-flex flex-wrap align-items-center justify-content-between">
                            <div>
                                <div class="eyebrow mb-1">Account security</div>
                                <h4 class="mb-1">Keep your credentials fresh</h4>
                                <p class="mb-0">Signed in as <strong><?= $safeDisplayName; ?></strong></p>
                            </div>
                            <div class="hero-pill mt-2 mt-sm-0">
                                <i class="mdi mdi-shield-check-outline"></i>
                                <span>Two-minute update</span>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-xl-7">
                            <div class="card mb-3">
                                <div class="card-header d-flex align-items-center justify-content-between">
                                    <div>
                                        <p class="text-muted mb-0 small">Step 1</p>
                                        <h5 class="section-title">Update your password</h5>
                                    </div>
                                    <span class="badge badge-primary p-2"><i class="mdi mdi-lock-reset mr-1"></i> Secure</span>
                                </div>
                                <div class="card-body">
                                    <?php if (!empty($flashMessage)): ?>
                                        <div class="mb-3">
                                            <?= $flashMessage; ?>
                                        </div>
                                    <?php endif; ?>

                                    <?php if (!empty($validationList)): ?>
                                        <div class="alert alert-danger d-flex align-items-start">
                                            <span class="icon-pill mr-2"><i class="mdi mdi-alert-decagram"></i></span>
                                            <div>
                                                <div class="font-weight-bold mb-1">Please double-check the form:</div>
                                                <ul class="mb-0 pl-3">
                                                    <?= $validationList; ?>
                                                </ul>
                                            </div>
                                        </div>
                                    <?php endif; ?>

                                    <form method="POST" action="<?= base_url(); ?>Users/update_password" novalidate>
                                        <input type="hidden" name="txt_hidden" value="<?= $safeUsername; ?>">

                                        <div class="form-group">
                                            <label for="currentpassword">Current password</label>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text"><i class="mdi mdi-lock-outline"></i></span>
                                                </div>
                                                <input type="password" class="form-control" id="currentpassword" name="currentpassword" placeholder="Enter current password" autocomplete="current-password" required>
                                                <div class="input-group-append">
                                                    <button type="button" class="btn toggle-password" data-target="#currentpassword" aria-label="Toggle current password visibility">
                                                        <i class="mdi mdi-eye-off"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label for="newpassword">New password</label>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text"><i class="mdi mdi-shield-key-outline"></i></span>
                                                </div>
                                                <input type="password" class="form-control" id="newpassword" name="newpassword" placeholder="Create a strong password" autocomplete="new-password" required minlength="8">
                                                <div class="input-group-append">
                                                    <button type="button" class="btn toggle-password" data-target="#newpassword" aria-label="Toggle new password visibility">
                                                        <i class="mdi mdi-eye-off"></i>
                                                    </button>
                                                </div>
                                            </div>
                                            <p class="helper mb-0 mt-1">Use at least 8 characters with letters, numbers, and ! @ # $ % ^ &amp; * only.</p>
                                        </div>

                                        <div class="form-group mb-2">
                                            <label for="cnewpassword">Confirm new password</label>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text"><i class="mdi mdi-checkbox-marked-circle-outline"></i></span>
                                                </div>
                                                <input type="password" class="form-control" id="cnewpassword" name="cnewpassword" placeholder="Re-enter new password" autocomplete="new-password" required>
                                                <div class="input-group-append">
                                                    <button type="button" class="btn toggle-password" data-target="#cnewpassword" aria-label="Toggle confirm password visibility">
                                                        <i class="mdi mdi-eye-off"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="action-row">
                                            <div class="d-flex align-items-center text-muted helper">
                                                <i class="mdi mdi-information-outline mr-1 text-primary"></i>
                                                You will stay signed in here. Sign out of other devices after updating.
                                            </div>
                                            <div class="d-flex align-items-center" style="gap: 8px;">
                                                <button type="reset" class="btn btn-outline-secondary">Reset</button>
                                                <button type="submit" name="updatePwd" class="btn btn-primary">
                                                    <i class="mdi mdi-lock-reset mr-1"></i> Update Password
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-5">
                            <div class="card side-card h-100">
                                <div class="card-body">
                                    <div class="d-flex align-items-center mb-3">
                                        <span class="icon-pill"><i class="mdi mdi-shield-account-outline"></i></span>
                                        <div class="ml-3">
                                            <h5 class="mb-0">Password checklist</h5>
                                            <p class="text-muted mb-0">Aim for a mix of memorable and secure.</p>
                                        </div>
                                    </div>

                                    <ul class="checklist mb-3">
                                        <li><i class="mdi mdi-check-circle text-success"></i> Minimum of 8 characters</li>
                                        <li><i class="mdi mdi-check-circle text-success"></i> Combine letters, numbers, and allowed symbols (! @ # $ % ^ &amp; *)</li>
                                        <li><i class="mdi mdi-check-circle text-success"></i> Avoid reusing old or shared passwords</li>
                                        <li><i class="mdi mdi-check-circle text-success"></i> Consider a short phrase you can easily remember</li>
                                    </ul>

                                    <div class="d-flex align-items-center mt-3">
                                        <span class="icon-pill" style="background: rgba(16, 185, 129, 0.12); color: #0f766e;"><i class="mdi mdi-lifebuoy"></i></span>
                                        <div class="ml-3">
                                            <div class="font-weight-bold mb-1">Need help?</div>
                                            <p class="text-muted mb-0">If you get locked out, reach out to your administrator or drop us an email.</p>
                                            <a class="btn btn-sm btn-outline-primary mt-2" href="mailto:admin@softtechservices.net">
                                                <i class="mdi mdi-email-outline mr-1"></i> Contact support
                                            </a>
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
    </div>

    <?php include('includes/themecustomizer.php'); ?>

    <script src="<?= base_url(); ?>assets/js/vendor.min.js"></script>
    <script src="<?= base_url(); ?>assets/libs/moment/moment.min.js"></script>
    <script src="<?= base_url(); ?>assets/libs/jquery-scrollto/jquery.scrollTo.min.js"></script>
    <script src="<?= base_url(); ?>assets/libs/sweetalert2/sweetalert2.min.js"></script>
    <script src="<?= base_url(); ?>assets/libs/fullcalendar/fullcalendar.min.js"></script>
    <script src="<?= base_url(); ?>assets/js/pages/calendar.init.js"></script>
    <script src="<?= base_url(); ?>assets/js/pages/jquery.chat.js"></script>
    <script src="<?= base_url(); ?>assets/js/pages/jquery.todo.js"></script>
    <script src="<?= base_url(); ?>assets/libs/morris-js/morris.min.js"></script>
    <script src="<?= base_url(); ?>assets/libs/raphael/raphael.min.js"></script>
    <script src="<?= base_url(); ?>assets/libs/jquery-sparkline/jquery.sparkline.min.js"></script>
    <script src="<?= base_url(); ?>assets/js/pages/dashboard.init.js"></script>
    <script src="<?= base_url(); ?>assets/js/app.min.js"></script>
    <script src="<?= base_url(); ?>assets/libs/jquery-ui/jquery-ui.min.js"></script>

    <script>
        (function() {
            var buttons = document.querySelectorAll('.toggle-password');

            buttons.forEach(function(btn) {
                btn.addEventListener('click', function() {
                    var selector = btn.getAttribute('data-target');
                    var input = document.querySelector(selector);

                    if (!input) {
                        return;
                    }

                    var isPassword = input.getAttribute('type') === 'password';
                    input.setAttribute('type', isPassword ? 'text' : 'password');
                    var icon = btn.querySelector('i');

                    if (icon) {
                        icon.classList.toggle('mdi-eye');
                        icon.classList.toggle('mdi-eye-off');
                    }
                });
            });
        })();
    </script>
</body>

</html>
