<?php
$level = strtolower((string) $this->session->userdata('level'));
?>
<?php
$dynamicBell = in_array($level, ['admin', 'staff', 'client'], true);
$indexUrl = $level === 'client' ? site_url('Page/clientMyTickets') : site_url('request');
?>
<?php if ($dynamicBell): ?>
  <li class="dropdown notification-list req-bell"
    data-count-url="<?= site_url('request/ajax_pending_count'); ?>"
    data-list-url="<?= site_url('request/ajax_pending_list'); ?>"
    data-markseen-url="<?= site_url('request/ajax_mark_seen'); ?>"
    data-index-url="<?= htmlspecialchars($indexUrl, ENT_QUOTES, 'UTF-8'); ?>">

    <a class="nav-link dropdown-toggle waves-effect" data-toggle="dropdown" href="#" role="button" aria-haspopup="false" aria-expanded="false">
      <i class="mdi mdi-bell-outline noti-icon"></i>
      <span class="badge badge-danger rounded-circle noti-icon-badge req-badge" style="display:none;">0</span>
    </a>

    <div class="dropdown-menu dropdown-menu-right dropdown-lg">
      <div class="dropdown-item noti-title d-flex justify-content-between align-items-center">
        <h5 class="font-16 m-0"><span class="req-title" data-default="Notifications">Notifications</span></h5>
        <a href="<?= htmlspecialchars($indexUrl, ENT_QUOTES, 'UTF-8'); ?>" class="text-muted small req-view-all">View all</a>
      </div>

      <div class="slimscroll noti-scroll" style="max-height:320px; overflow:auto;">
        <div class="text-center text-muted p-3 req-empty">No pending requests.</div>
        <div class="req-list"></div>
      </div>
    </div>
  </li>
<?php elseif ($level !== 'student'): ?>
  <li class="dropdown notification-list req-bell req-bell-static">
    <a class="nav-link dropdown-toggle waves-effect" data-toggle="dropdown" href="#" role="button" aria-haspopup="false" aria-expanded="false">
      <i class="mdi mdi-bell-outline noti-icon"></i>
      <span class="badge badge-secondary rounded-circle noti-icon-badge" style="display:none;">0</span>
    </a>

    <div class="dropdown-menu dropdown-menu-right dropdown-lg">
      <div class="dropdown-item noti-title d-flex justify-content-between align-items-center">
        <h5 class="font-16 m-0">Notifications</h5>
      </div>

      <div class="slimscroll noti-scroll" style="max-height:320px; overflow:auto;">
        <div class="text-center text-muted p-3">No notifications available.</div>
      </div>
    </div>
  </li>
<?php endif; ?>
