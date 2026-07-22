<?php
$levelLower = strtolower(trim((string) $this->session->userdata('level')));
$isClientPortalUser = $levelLower === 'client';
$sessionAvatar = trim((string) $this->session->userdata('avatar'));
$avatarPath = $sessionAvatar !== '' ? FCPATH . 'upload/profile/' . $sessionAvatar : '';
$avatarUrl = ($sessionAvatar !== '' && is_file($avatarPath))
    ? base_url() . 'upload/profile/' . $sessionAvatar
    : base_url() . 'assets/images/users/avatar.png';
$displayName = trim((string) $this->session->userdata('fname') . ' ' . $this->session->userdata('lname'));
$displayName = $displayName !== '' ? $displayName : (string) $this->session->userdata('username');
$profileUrl = $isClientPortalUser
    ? base_url() . 'Page/clientProfile'
    : base_url() . 'Page/staffprofile?id=' . $this->session->userdata('IDNumber');
$homeUrl = base_url();

switch ($levelLower) {
    case 'system administrator':
    case 'super admin':
        $homeUrl = base_url('Page/superAdmin');
        break;
    case 'admin':
        $homeUrl = base_url('Page/admin');
        break;
    case 'manager':
    case 'pos admin':
        $homeUrl = base_url('Pos/posAdmin');
        break;
    case 'encoder':
    case 'staff':
    case 'account':
        $homeUrl = base_url('Page/staff');
        break;
    case 'cashier':
    case 'pos staff':
        $homeUrl = base_url('Pos/posStaff');
        break;
    case 'client':
        $homeUrl = base_url('Page/clientDashboard');
        break;
    case 'student':
        $homeUrl = base_url('Page/studentsprofile');
        break;
}

$topNavEnabledCompanyFeatures = array();
$topNavHasCompanyFeatureRestrictions = false;
$topNavIsPackage2 = false;

if ((int) $this->session->userdata('settingsID') > 0 && in_array($levelLower, array('admin', 'staff', 'account', 'manager', 'cashier', 'encoder'), true) && $this->db->table_exists('company_features')) {
    $topNavFeatureRows = $this->db
        ->select('feature_key')
        ->from('company_features')
        ->where('settingsID', (int) $this->session->userdata('settingsID'))
        ->where('is_enabled', 1)
        ->get()
        ->result();

    foreach ($topNavFeatureRows as $topNavFeatureRow) {
        $topNavFeatureKey = trim((string) ($topNavFeatureRow->feature_key ?? ''));
        if ($topNavFeatureKey !== '') {
            $topNavEnabledCompanyFeatures[] = $topNavFeatureKey;
        }
    }

    $topNavEnabledCompanyFeatures = array_values(array_unique($topNavEnabledCompanyFeatures));
    $topNavHasCompanyFeatureRestrictions = !empty($topNavEnabledCompanyFeatures);
    
    // Check if company is on Package 2 (Task Management Suite)
    // Package 2 features: tasks, notes, calendar
    $topNavPackage2Features = array('tasks', 'notes', 'calendar');
    $topNavIsPackage2 = count($topNavEnabledCompanyFeatures) === count($topNavPackage2Features) && 
                       count(array_intersect($topNavEnabledCompanyFeatures, $topNavPackage2Features)) === count($topNavPackage2Features);
}

$topNavHasFeature = function ($featureKeys) use ($topNavHasCompanyFeatureRestrictions, $topNavEnabledCompanyFeatures) {
    if (!$topNavHasCompanyFeatureRestrictions) {
        return true;
    }

    foreach ((array) $featureKeys as $topNavFeatureKey) {
        $topNavFeatureKey = trim((string) $topNavFeatureKey);
        if ($topNavFeatureKey !== '' && in_array($topNavFeatureKey, $topNavEnabledCompanyFeatures, true)) {
            return true;
        }
    }

    return false;
};

$showTopNavSupport = $topNavHasFeature(array('support'));
$showTopNavCalendar = $topNavHasFeature(array('calendar'));
$showTopNavAnnualGoals = $topNavHasFeature(array('tasks'));
?>
<div class="navbar-custom">
    <div class="berps-topbar-left">
        <button class="berps-menu-toggle" type="button" aria-label="Toggle menu" data-berps-sidebar-toggle>
            <i class="ph ph-list"></i>
        </button>
        <div class="berps-topbar-title" id="berps-topbar-title"><?= isset($page_title) ? htmlspecialchars($page_title, ENT_QUOTES, 'UTF-8') : ''; ?></div>
    </div>

    <ul class="list-unstyled topnav-menu mb-0">
        <?php if ($this->session->userdata('level') !== 'Student'): ?>
            <?php if (!$isClientPortalUser): ?>
                <?php $__level = (string) $this->session->userdata('level'); ?>
                <li class="dropdown notification-list">
                    <a class="nav-link dropdown-toggle" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false" title="Apps & Tools">
                        <i class="ph ph-squares-four"></i>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right berps-apps-menu">
                        <?php if (!$topNavIsPackage2): ?>
                            <div class="dropdown-item noti-title">
                                <h6 class="font-14 m-0">Mail</h6>
                            </div>
                            <a href="<?= base_url(); ?>ZohoMail/inbox" class="dropdown-item notify-item">
                                <i class="ph ph-envelope-simple"></i> Inbox
                            </a>
                            <a href="<?= base_url(); ?>ZohoMail/compose" class="dropdown-item notify-item">
                                <i class="ph ph-pencil-simple"></i> Compose
                            </a>
                            <a href="<?= base_url(); ?>ZohoMail/settings" class="dropdown-item notify-item">
                                <i class="ph ph-gear"></i> Zoho Settings
                            </a>
                            <div class="dropdown-divider"></div>
                            <div class="dropdown-item noti-title">
                                <h6 class="font-14 m-0">Knowledge Base</h6>
                            </div>
                            <a href="<?= base_url(); ?>Page/knowledgeBase" class="dropdown-item notify-item">
                                <i class="ph ph-book-open"></i> View All
                            </a>
                            <a href="<?= base_url(); ?>Page/knowledgeBaseSettings" class="dropdown-item notify-item">
                                <i class="ph ph-gear"></i> Settings
                            </a>
                        <?php endif; ?>
                        <?php if ($showTopNavSupport): ?>
                            <?php if (!$topNavIsPackage2): ?><div class="dropdown-divider"></div><?php endif; ?>
                            <div class="dropdown-item noti-title">
                                <h6 class="font-14 m-0">Customer Support</h6>
                            </div>
                            <a href="<?= base_url(); ?>Page/supportDashboard" class="dropdown-item notify-item">
                                <i class="ph ph-chart-line"></i> Dashboard
                            </a>
                            <a href="<?= base_url(); ?>Page/supportIssues?scope=unassigned" class="dropdown-item notify-item">
                                <i class="ph ph-question"></i> Unassigned Tickets
                            </a>
                            <a href="<?= base_url(); ?>Page/supportIssues?scope=awaiting_reply" class="dropdown-item notify-item">
                                <i class="ph ph-chat-dots"></i> Awaiting Reply
                            </a>
                            <a href="<?= base_url(); ?>Page/supportIssues?scope=open" class="dropdown-item notify-item">
                                <i class="ph ph-list-bullets"></i> <?= $__level === 'Admin' ? 'All Open Tickets' : 'Open Tickets'; ?>
                            </a>
                            <a href="<?= base_url(); ?>Page/supportIssues?scope=closed" class="dropdown-item notify-item">
                                <i class="ph ph-check-circle"></i> <?= $__level === 'Admin' ? 'All Closed Tickets' : 'Closed Tickets'; ?>
                            </a>
                            <?php if ($__level === 'Admin'): ?>
                                <a href="<?= base_url(); ?>Page/cancelledTicketLogs" class="dropdown-item notify-item">
                                    <i class="ph ph-x"></i> Cancelled Ticket Logs
                                </a>
                            <?php else: ?>
                                <a href="<?= base_url(); ?>Page/supportIssues?scope=all" class="dropdown-item notify-item">
                                    <i class="ph ph-list-bullets"></i> All Tickets
                                </a>
                            <?php endif; ?>
                        <?php endif; ?>
                        <?php if ($showTopNavCalendar || ($showTopNavAnnualGoals && !$topNavIsPackage2) || !$topNavIsPackage2): ?>
                            <?php if (!$topNavIsPackage2 || $showTopNavSupport): ?><div class="dropdown-divider"></div><?php endif; ?>
                            <div class="dropdown-item noti-title">
                                <h6 class="font-14 m-0">Quick Links</h6>
                            </div>
                            <?php if ($showTopNavCalendar): ?>
                                <a href="<?= base_url(); ?>Calendar" class="dropdown-item notify-item">
                                    <i class="ph ph-calendar-blank"></i> Calendar
                                </a>
                            <?php endif; ?>
                            <?php if ($showTopNavAnnualGoals && !$topNavIsPackage2): ?>
                                <a href="<?= base_url(); ?>Page/annualGoals" class="dropdown-item notify-item">
                                    <i class="ph ph-chart-line-up"></i> Annual Goals
                                </a>
                            <?php endif; ?>
                            <?php if (!$topNavIsPackage2): ?>
                                <a href="<?= base_url('Page/bday_today'); ?>" class="dropdown-item notify-item">
                                    <i class="ph ph-gift"></i> Today's Birthdays
                                </a>
                                <a href="<?= base_url('Page/bday_month'); ?>" class="dropdown-item notify-item">
                                    <i class="ph ph-gift"></i> This Month's Birthdays
                                </a>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </li>
            <?php endif; ?>
            <?php if (!$topNavIsPackage2): ?>
            <?php include(APPPATH . 'views/includes/req_bell.php'); ?>
            <?php endif; ?>
        <?php endif; ?>
        <li class="dropdown notification-list">
            <a class="nav-link dropdown-toggle nav-user mr-0 waves-effect" data-toggle="dropdown" href="#" role="button" aria-haspopup="false" aria-expanded="false">
                <img src="<?= htmlspecialchars($avatarUrl, ENT_QUOTES, 'UTF-8'); ?>" alt="user-image" class="rounded-circle">
                <span class="pro-user-name ml-1">
                    <?= htmlspecialchars($displayName, ENT_QUOTES, 'UTF-8'); ?> <i class="ph ph-caret-down"></i>
                </span>
            </a>
            <div class="dropdown-menu dropdown-menu-right profile-dropdown ">
                <!-- item-->
                <!-- item-->
                <?php if (!$isClientPortalUser): ?>
                    <a href="<?= base_url(); ?>Page/changeDP?id=<?php echo $this->session->userdata('username'); ?>" class="dropdown-item notify-item" data-account-modal-open="profile-photo">
                        <i class="ph ph-gear"></i>
                        <span>Change Profile Photo</span>
                    </a>
                <?php endif; ?>
                <a href="<?= base_url(); ?>Users/changepassword" class="dropdown-item notify-item" data-account-modal-open="password">
                    <i class="ph ph-lock-key"></i>
                    <span>Change Password</span>
                </a>

                <?php if ($this->session->userdata('level') === 'Student'): ?>
                    <a href="<?= base_url(); ?>Page/studentsprofile" class="dropdown-item notify-item">
                        <i class="ph ph-user"></i>
                        <span>My Profile</span>
                    </a>
                <?php else: ?>
                    <a href="<?= htmlspecialchars($profileUrl, ENT_QUOTES, 'UTF-8'); ?>" class="dropdown-item notify-item">
                        <i class="ph ph-user"></i>
                        <span>My Profile</span>
                    </a>

                <?php endif; ?>

                <!-- item-->
                <?php if (!$isClientPortalUser): ?>
                    <a href="<?= base_url(); ?>Page/lockScreen?id=<?php echo $this->session->userdata('username'); ?>" class="dropdown-item notify-item">
                        <i class="ph ph-lock"></i>
                        <span>Lock Screen</span>
                    </a>
                <?php endif; ?>

                <div class="dropdown-divider"></div>

                <!-- item-->
                <a href="<?php echo site_url('login/logout'); ?>" class="dropdown-item notify-item">
                    <i class="ph ph-sign-out"></i>
                    <span>Logout</span>
                </a>

            </div>
        </li>

        <?php if (!$isClientPortalUser && $this->session->userdata('level') === 'Admin'): ?>
            <li class="dropdown notification-list">
                <a href="javascript:void(0);" class="nav-link right-bar-toggle waves-effect">
                    <i class="ph ph-gear"></i>
                </a>
            </li>
        <?php endif; ?>


    </ul>
</div>

<?php if (!$isClientPortalUser): ?>
    <div class="berps-account-modal" data-account-modal="profile-photo" aria-hidden="true" hidden>
        <div class="berps-account-modal__backdrop" data-account-modal-close></div>
        <section class="berps-account-modal__dialog" role="dialog" aria-modal="true" aria-labelledby="profile-photo-modal-title">
            <header class="berps-account-modal__header">
                <div class="berps-account-modal__header-icon" aria-hidden="true">
                    <i class="ph ph-user-circle"></i>
                </div>
                <div>
                    <span class="berps-account-modal__eyebrow">Personalize your account</span>
                    <h2 id="profile-photo-modal-title">Change profile photo</h2>
                    <p>Choose a clear photo so your teammates can recognize you.</p>
                </div>
                <button type="button" class="berps-account-modal__close" data-account-modal-close aria-label="Close change profile photo dialog">
                    <i class="ph ph-x" aria-hidden="true"></i>
                </button>
            </header>

            <form class="berps-account-modal__form" action="<?= site_url('Page/uploadProfPic'); ?>" method="post" enctype="multipart/form-data" data-account-form="profile-photo" novalidate>
                <div class="berps-account-modal__body">
                    <div class="berps-profile-photo-editor">
                        <div class="berps-profile-photo-editor__avatar-wrap">
                            <img class="berps-profile-photo-editor__avatar" src="<?= htmlspecialchars($avatarUrl, ENT_QUOTES, 'UTF-8'); ?>" alt="Current profile photo" data-profile-photo-preview data-saved-src="<?= htmlspecialchars($avatarUrl, ENT_QUOTES, 'UTF-8'); ?>">
                            <span class="berps-profile-photo-editor__badge" aria-hidden="true"><i class="ph ph-camera"></i></span>
                        </div>
                        <div class="berps-profile-photo-editor__copy">
                            <strong><?= htmlspecialchars($displayName, ENT_QUOTES, 'UTF-8'); ?></strong>
                            <span>Your current profile photo</span>
                        </div>
                    </div>

                    <label class="berps-photo-dropzone" for="account-profile-photo-input" data-profile-photo-dropzone role="button" tabindex="0">
                        <span class="berps-photo-dropzone__icon" aria-hidden="true"><i class="ph ph-image-square"></i></span>
                        <span class="berps-photo-dropzone__text">
                            <strong data-profile-photo-label>Choose a new photo</strong>
                            <small>JPG, PNG, or GIF &middot; Maximum 2 MB</small>
                        </span>
                        <span class="berps-photo-dropzone__action">Browse</span>
                    </label>
                    <input class="berps-account-modal__file" id="account-profile-photo-input" type="file" name="nonoy" accept="image/jpeg,image/png,image/gif" required>

                    <div class="berps-account-feedback" data-account-feedback role="status" aria-live="polite" hidden></div>
                </div>

                <footer class="berps-account-modal__footer">
                    <button type="button" class="btn berps-account-btn berps-account-btn--secondary" data-account-modal-close>Cancel</button>
                    <button type="submit" class="btn berps-account-btn berps-account-btn--primary" data-account-submit>
                        <i class="ph ph-upload-simple" aria-hidden="true"></i>
                        <span>Save photo</span>
                    </button>
                </footer>
            </form>
        </section>
    </div>
<?php endif; ?>

<div class="berps-account-modal" data-account-modal="password" aria-hidden="true" hidden>
    <div class="berps-account-modal__backdrop" data-account-modal-close></div>
    <section class="berps-account-modal__dialog berps-account-modal__dialog--password" role="dialog" aria-modal="true" aria-labelledby="password-modal-title">
        <header class="berps-account-modal__header">
            <div class="berps-account-modal__header-icon" aria-hidden="true">
                <i class="ph ph-shield-check"></i>
            </div>
            <div>
                <span class="berps-account-modal__eyebrow">Account security</span>
                <h2 id="password-modal-title">Change your password</h2>
                <p>Use a strong password that you do not use anywhere else.</p>
            </div>
            <button type="button" class="berps-account-modal__close" data-account-modal-close aria-label="Close change password dialog">
                <i class="ph ph-x" aria-hidden="true"></i>
            </button>
        </header>

        <form class="berps-account-modal__form" action="<?= site_url('Users/update_password'); ?>" method="post" data-account-form="password" novalidate>
            <div class="berps-account-modal__body">
                <div class="berps-account-field">
                    <label for="account-current-password">Current password</label>
                    <div class="berps-account-input">
                        <i class="ph ph-lock-key" aria-hidden="true"></i>
                        <input id="account-current-password" type="password" name="currentpassword" autocomplete="current-password" placeholder="Enter your current password" required>
                        <button type="button" data-password-toggle aria-label="Show current password" aria-pressed="false"><i class="ph ph-eye" aria-hidden="true"></i></button>
                    </div>
                </div>

                <div class="berps-account-field">
                    <label for="account-new-password">New password</label>
                    <div class="berps-account-input">
                        <i class="ph ph-shield-check" aria-hidden="true"></i>
                        <input id="account-new-password" type="password" name="newpassword" autocomplete="new-password" placeholder="Create a new password" minlength="8" required data-new-password>
                        <button type="button" data-password-toggle aria-label="Show new password" aria-pressed="false"><i class="ph ph-eye" aria-hidden="true"></i></button>
                    </div>
                    <div class="berps-password-strength" aria-live="polite">
                        <div class="berps-password-strength__bars" aria-hidden="true">
                            <span></span><span></span><span></span><span></span>
                        </div>
                        <small data-password-strength-label>Use 8 or more characters</small>
                    </div>
                </div>

                <div class="berps-account-field">
                    <label for="account-confirm-password">Confirm new password</label>
                    <div class="berps-account-input">
                        <i class="ph ph-check-circle" aria-hidden="true"></i>
                        <input id="account-confirm-password" type="password" name="cnewpassword" autocomplete="new-password" placeholder="Re-enter your new password" minlength="8" required data-confirm-password>
                        <button type="button" data-password-toggle aria-label="Show confirmed password" aria-pressed="false"><i class="ph ph-eye" aria-hidden="true"></i></button>
                    </div>
                </div>

                <div class="berps-password-note">
                    <i class="ph ph-info" aria-hidden="true"></i>
                    <span>Allowed symbols: <strong>! @ # $ % ^ &amp; *</strong>. You will remain signed in after the update.</span>
                </div>

                <div class="berps-account-feedback" data-account-feedback role="status" aria-live="polite" hidden></div>
            </div>

            <footer class="berps-account-modal__footer">
                <button type="button" class="btn berps-account-btn berps-account-btn--secondary" data-account-modal-close>Cancel</button>
                <button type="submit" class="btn berps-account-btn berps-account-btn--primary" data-account-submit>
                    <i class="ph ph-lock-key" aria-hidden="true"></i>
                    <span>Update password</span>
                </button>
            </footer>
        </form>
    </section>
</div>

<!-- <audio id="global-reminder-audio" src="<?= base_url('upload/daj_mi_dziuba_rmx.mp3'); ?>" preload="auto"></audio> -->
<script>
                    (function() {
                        var pollUrl = '<?= base_url('Reminders/dueNowFeed'); ?>';
                        var storageKey = 'reminder_notified_keys';
                        var audioEl = document.getElementById('global-reminder-audio');
                        var isPlaying = false;
                        var playAttempts = 0;

                        function loadNotified() {
                            try {
                                var val = localStorage.getItem(storageKey);
                                return val ? JSON.parse(val) : {};
                            } catch (e) {
                                return {};
                            }
                        }

                        function saveNotified(map) {
                            try {
                                localStorage.setItem(storageKey, JSON.stringify(map));
                            } catch (e) {
                                // ignore storage errors
                            }
                        }

                        var notified = loadNotified();

                        function playAlert() {
                            if (!audioEl || !audioEl.paused) return;
                            audioEl.loop = true;
                            audioEl.volume = 0.8;
                            audioEl.currentTime = 0;
                            playAttempts = 0;
                            var tryPlay = function() {
                                audioEl.play().catch(function() {
                                    playAttempts++;
                                    if (playAttempts < 5) {
                                        setTimeout(tryPlay, 1000);
                                    }
                                });
                            };
                            tryPlay();
                            isPlaying = true;
                            setTimeout(stopAlert, 10000); // shorten to ~10s
                        }

                        function stopAlert() {
                            if (!audioEl) return;
                            if (!audioEl.paused) {
                                audioEl.pause();
                                audioEl.currentTime = 0;
                            }
                            isPlaying = false;
                        }

                        function hasNew(items) {
                            for (var i = 0; i < items.length; i++) {
                                var key = String(items[i].id) + '_' + String(items[i].remind_at);
                                if (!notified[key]) {
                                    notified[key] = true;
                                    saveNotified(notified);
                                    return true;
                                }
                            }
                            return false;
                        }

                        function poll() {
                            if (typeof $ === 'undefined') return;
                            $.getJSON(pollUrl, function(res) {
                                var items = res && res.items ? res.items : [];
                                if (items.length > 0 && hasNew(items)) {
                                    playAlert();
                                }
                                if (items.length === 0 && isPlaying) {
                                    stopAlert();
                                }
                            });
                        }

                        window.addEventListener('load', function() {
                            poll();
                            setInterval(poll, 15000);
                        });
                    })();
                </script>
