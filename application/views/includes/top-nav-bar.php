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
<!-- <script type="text/javascript"> 
        window.history.forward(); 
        function noBack() { 
            window.history.forward(); 
        } 
    </script> -->


<div class="navbar-custom">
    <ul class="list-unstyled topnav-menu float-right mb-0">
        <?php if ($this->session->userdata('level') !== 'Student'): ?>
            <?php if (!$isClientPortalUser): ?>
                <?php $__level = (string) $this->session->userdata('level'); ?>
                <?php if (!$topNavIsPackage2): ?>
                <li class="dropdown notification-list">
                    <a class="nav-link dropdown-toggle waves-effect" data-toggle="dropdown" href="#" role="button" aria-haspopup="false" aria-expanded="false" title="Mail">
                        <i class="ph ph-envelope"></i>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right">
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
                    </div>
                </li>
                <li class="dropdown notification-list">
                    <a class="nav-link dropdown-toggle waves-effect" data-toggle="dropdown" href="#" role="button" aria-haspopup="false" aria-expanded="false" title="Knowledge Base">
                        <i class="ph ph-book-open"></i>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right">
                        <div class="dropdown-item noti-title">
                            <h6 class="font-14 m-0">Knowledge Base</h6>
                        </div>
                        <a href="<?= base_url(); ?>Page/knowledgeBase" class="dropdown-item notify-item">
                            <i class="ph ph-list-bullets"></i> View All
                        </a>
                        <?php if (!$isClientPortalUser): ?>
                            <a href="<?= base_url(); ?>Page/knowledgeBaseSettings" class="dropdown-item notify-item">
                                <i class="ph ph-gear"></i> Settings
                            </a>
                        <?php endif; ?>
                    </div>
                </li>
                <?php endif; ?>
                <?php if ($showTopNavSupport): ?>
                <li class="dropdown notification-list">
                    <a class="nav-link dropdown-toggle waves-effect" data-toggle="dropdown" href="#" role="button" aria-haspopup="false" aria-expanded="false" title="Customer Support">
                        <i class="ph ph-headset"></i>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right">
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
                    </div>
                </li>
                <?php endif; ?>
                <?php if ($showTopNavCalendar): ?>
                <li class="dropdown notification-list">
                    <a class="nav-link waves-effect" href="<?= base_url(); ?>Calendar" title="Calendar">
                        <i class="ph ph-calendar-blank"></i>
                    </a>
                </li>
                <?php endif; ?>
                <?php if ($showTopNavAnnualGoals && !$topNavIsPackage2): ?>
                <li class="dropdown notification-list">
                    <a class="nav-link waves-effect" href="<?= base_url(); ?>Page/annualGoals" title="Annual Goals">
                        <i class="ph ph-chart-line-up"></i>
                    </a>
                </li>
                <?php endif; ?>
                <?php if (!$topNavIsPackage2): ?>
                <li class="dropdown notification-list">
                    <a class="nav-link dropdown-toggle  waves-effect" data-toggle="dropdown" href="#" role="button" aria-haspopup="false" aria-expanded="false">
                        <i class="ph ph-gift"></i>
                    </a>

                    <div class="dropdown-menu dropdown-menu-right dropdown-lg">
                        <div class="dropdown-item noti-title">
                            <h5 class="font-16 m-0">
                                Birthday Celebrants
                            </h5>
                        </div>

                        <div class="slimscroll noti-scroll">
                            <div class="inbox-widget">
                                <a href="<?= base_url('Page/bday_today'); ?>">
                                    <div class="inbox-item">
                                        <div class="inbox-item-img"><img src="<?= base_url(); ?>assets/images/cake.png" class="rounded-circle" alt=""></div>
                                        <p class="inbox-item-author">Today's</p>
                                        <p class="inbox-item-text text-truncate">Birthday Celebrants</p>
                                    </div>
                                </a>
                                <a href="<?= base_url('Page/bday_month'); ?>">
                                    <div class="inbox-item">
                                        <div class="inbox-item-img"><img src="<?= base_url(); ?>assets/images/cake.png" class="rounded-circle" alt=""></div>
                                        <p class="inbox-item-author">This Month's</p>
                                        <p class="inbox-item-text text-truncate">Birthday Celebrants</p>
                                    </div>
                                </a>
                            </div>
                        </div>
                    </div>
                </li>
                <?php endif; ?>
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
                    <a href="<?= base_url(); ?>Page/changeDP?id=<?php echo $this->session->userdata('username'); ?>" class="dropdown-item notify-item">
                        <i class="ph ph-gear"></i>
                        <span>Change Profile Pic</span>
                    </a>
                    <a href="<?= base_url(); ?>Users/changepassword" class="dropdown-item notify-item">
                        <i class="ph ph-lock-key"></i>
                        <span>Change Password</span>
                    </a>
                <?php endif; ?>

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

    <!-- LOGO -->
    <div class="logo-box">
        <a href="<?= htmlspecialchars($homeUrl, ENT_QUOTES, 'UTF-8'); ?>" class="logo text-center logo-dark" aria-label="BERPS Home">
            <span class="logo-lg">
                <img src="<?= base_url(); ?>assets/images/srms-logo.png" alt="" height="18">
                <!-- <span class="logo-lg-text-dark">Velonic</span> -->
            </span>
            <span class="logo-sm">
                <!-- <span class="logo-lg-text-dark">V</span> -->
                <img src="<?= base_url(); ?>assets/images/logo-sm1.png" alt="" height="22">
            </span>
        </a>

        <a href="<?= htmlspecialchars($homeUrl, ENT_QUOTES, 'UTF-8'); ?>" class="logo text-center logo-light" aria-label="BERPS Home">
            <span class="logo-lg">
                <img src="<?= base_url(); ?>assets/images/srms-logo.png" alt="" height="45">
                <!-- <span class="logo-lg-text-dark">Velonic</span> -->
            </span>
            <span class="logo-sm">
                <!-- <span class="logo-lg-text-dark">V</span> -->
                <img src="<?= base_url(); ?>assets/images/logo-sm1.png" alt="" height="22">
            </span>
        </a>
    </div>

    <!-- LOGO -->


    <ul class="list-unstyled topnav-menu topnav-menu-left m-0">
        <li>
            <button class="button-menu-mobile waves-effect">
                <i class="ph ph-list"></i>
            </button>
        </li>

        <li class="d-none d-lg-block">
            <form class="app-search">
                <div class="app-search-box">
                    <div class="input-group">
                        <input type="text" class="form-control" placeholder="Search...">
                        <div class="input-group-append">
                            <button class="btn" type="submit">
                                <i class="ph ph-magnifying-glass"></i>
                            </button>
                        </div>
                    </div>
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
            </form>
        </li>
    </ul>
</div>
