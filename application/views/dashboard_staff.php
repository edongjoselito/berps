<?php
// Package 2 detection
$staffDashboardSettingsId = (int) $this->session->userdata('settingsID');
$staffDashboardEnabledFeatures = array();
$staffDashboardIsPackage2 = false;

if ($staffDashboardSettingsId > 0 && $this->db->table_exists('company_features')) {
    $staffDashboardFeatureRows = $this->db
        ->select('feature_key')
        ->from('company_features')
        ->where('settingsID', $staffDashboardSettingsId)
        ->where('is_enabled', 1)
        ->get()
        ->result();

    foreach ($staffDashboardFeatureRows as $staffDashboardFeatureRow) {
        $staffDashboardFeatureKey = trim((string) ($staffDashboardFeatureRow->feature_key ?? ''));
        if ($staffDashboardFeatureKey !== '') {
            $staffDashboardEnabledFeatures[] = $staffDashboardFeatureKey;
        }
    }

    $staffDashboardEnabledFeatures = array_values(array_unique($staffDashboardEnabledFeatures));
    
    // Check if company is on Package 2 (Task Management Suite)
    // Package 2 features: tasks, notes, calendar
    $staffDashboardPackage2Features = array('tasks', 'notes', 'calendar');
    $staffDashboardIsPackage2 = count($staffDashboardEnabledFeatures) === count($staffDashboardPackage2Features) && 
                               count(array_intersect($staffDashboardEnabledFeatures, $staffDashboardPackage2Features)) === count($staffDashboardPackage2Features);
}

$dueTodayRecords = isset($dueToday) ? $dueToday : array();
if ($dueTodayRecords instanceof Traversable) {
    $dueTodayRecords = iterator_to_array($dueTodayRecords, false);
}
$dueTodayRecords = is_array($dueTodayRecords) ? array_values($dueTodayRecords) : array();

$archivedTodayRecords = isset($archivedToday) ? $archivedToday : array();
if ($archivedTodayRecords instanceof Traversable) {
    $archivedTodayRecords = iterator_to_array($archivedTodayRecords, false);
}
$archivedTodayRecords = is_array($archivedTodayRecords) ? array_values($archivedTodayRecords) : array();

$accomplishedRows = isset($accomplishedSummary) ? $accomplishedSummary : array();
if ($accomplishedRows instanceof Traversable) {
    $accomplishedRows = iterator_to_array($accomplishedRows, false);
}
$accomplishedRows = is_array($accomplishedRows) ? array_values($accomplishedRows) : array();

$dueTodayCount      = count($dueTodayRecords);
$archivedTodayCount = count($archivedTodayRecords);
$leaderboardRows    = array_slice($accomplishedRows, 0, 10);
$topScoreValue      = !empty($leaderboardRows) && isset($leaderboardRows[0]->total)
    ? (int) $leaderboardRows[0]->total
    : 0;

$taskDueTodayValue   = isset($taskDueTodayCount) ? (int) $taskDueTodayCount : 0;
$taskDueSoonValue    = isset($taskDueSoonCount) ? (int) $taskDueSoonCount : 0;
$taskOverdueValue    = isset($taskOverdueCount) ? (int) $taskOverdueCount : 0;
$taskWithoutDueValue = isset($taskWithoutDueDateCount) ? (int) $taskWithoutDueDateCount : 0;
$taskDueWindowValue  = isset($taskDueWindowDays) ? (int) $taskDueWindowDays : 7;

$taskQueueRows = isset($taskDueQueue) ? $taskDueQueue : array();
if ($taskQueueRows instanceof Traversable) {
    $taskQueueRows = iterator_to_array($taskQueueRows, false);
}
$taskQueueRows = is_array($taskQueueRows) ? array_values($taskQueueRows) : array();
$forwardedTaskValue   = isset($forwardedTaskCount) ? (int) $forwardedTaskCount : 0;

$timeNotice           = trim((string) ($time_notice ?? ''));
$attendanceStatusMeta = $timeNotice !== '' ? $timeNotice : 'Your attendance record looks complete for today.';
$currentDateLabel     = date('l, F j, Y');
$currentMonthLabel    = date('F Y');
?>
<!DOCTYPE html>
<html lang="en">

<?php include('includes/head.php'); ?>
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css" rel="stylesheet">


<body>
    <div id="wrapper" class="staff-dashboard-admin">

        <?php include('includes/top-nav-bar.php'); ?>
        <?php include('includes/sidebar.php'); ?>

        <div class="content-page">
            <div class="content">
                <div class="container-fluid dashboard-wrap berps-page">

                    <?php if ($this->session->flashdata('success')): ?>
                        <div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
                            <?= htmlspecialchars((string) $this->session->flashdata('success'), ENT_QUOTES, 'UTF-8'); ?>
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        </div>
                    <?php endif; ?>

                    <?php if ($this->session->flashdata('danger')): ?>
                        <div class="alert alert-danger alert-dismissible fade show mt-3" role="alert">
                            <?= htmlspecialchars((string) $this->session->flashdata('danger'), ENT_QUOTES, 'UTF-8'); ?>
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        </div>
                    <?php endif; ?>

                    <div class="dash-header">
                        <div class="dash-header-left">
                            <div class="eyebrow">Staff Overview</div>
                            <h1>Staff Dashboard</h1>
                        </div>
                        <div class="header-actions">
                            <?php if (!$staffDashboardIsPackage2): ?>
                            <a href="<?= base_url(); ?>Page/myDTR" class="btn btn-clean">
                                <i class="mdi mdi-clock-time-four-outline"></i>
                                My DTR
                            </a>
                            <a href="<?= base_url(); ?>Page/supportIssues?scope=unassigned" class="btn btn-clean">
                                <i class="mdi mdi-lifebuoy"></i>
                                Unassigned
                                <span class="badge badge-pill badge-danger ml-2"><?= number_format((int) ($unassignedTicketCount ?? 0)); ?></span>
                            </a>
                            <a href="<?= base_url(); ?>Page/projectAddTask?status=open&amp;scope=forwarded" class="btn btn-clean">
                                <i class="mdi mdi-share-all-outline"></i>
                                Forwarded Tasks
                                <span class="badge badge-pill badge-danger ml-2"><?= number_format($forwardedTaskValue); ?></span>
                            </a>
                            <?php endif; ?>
                            <a href="<?= base_url(); ?>Page/projectAddTask" class="btn btn-solid">
                                <i class="mdi mdi-format-list-checks"></i>
                                Task List
                            </a>
                        </div>
                    </div>

                    <div class="kpi-grid">
                        <?php if (!$staffDashboardIsPackage2): ?>
                        <div class="kpi-card kpi-card--info">
                            <div class="kpi-top">
                                <div class="kpi-icon"><i class="mdi mdi-bell-ring-outline"></i></div>
                            </div>
                            <div class="kpi-label">Due Today</div>
                            <div class="kpi-num"><?= number_format($dueTodayCount); ?></div>
                            <!-- <div class="kpi-meta">Reminders scheduled for <?= htmlspecialchars($currentDateLabel, ENT_QUOTES, 'UTF-8'); ?>.</div> -->
                        </div>
                        <?php endif; ?>

                        <div class="kpi-card kpi-card--warning">
                            <div class="kpi-top">
                                <div class="kpi-icon"><i class="mdi mdi-calendar-clock"></i></div>
                            </div>
                            <div class="kpi-label">Tasks Due Today</div>
                            <div class="kpi-num"><?= number_format($taskDueTodayValue); ?></div>
                            <!-- <div class="kpi-meta">Open tasks to complete or update today.</div> -->
                        </div>

                        <div class="kpi-card kpi-card--danger">
                            <div class="kpi-top">
                                <div class="kpi-icon"><i class="mdi mdi-alert-circle-outline"></i></div>
                            </div>
                            <div class="kpi-label">Overdue Tasks</div>
                            <div class="kpi-num"><?= number_format($taskOverdueValue); ?></div>
                            <!-- <div class="kpi-meta">Tasks whose due date has already passed.</div> -->
                        </div>

                        <div class="kpi-card kpi-card--success">
                            <div class="kpi-top">
                                <div class="kpi-icon"><i class="mdi mdi-trophy-outline"></i></div>
                            </div>
                            <div class="kpi-label">Top Monthly Score</div>
                            <div class="kpi-num"><?= number_format($topScoreValue); ?></div>
                            <!-- <div class="kpi-meta">Highest accomplishment total for <?= htmlspecialchars($currentMonthLabel, ENT_QUOTES, 'UTF-8'); ?>.</div> -->
                        </div>
                    </div>

                    <div class="content-grid">
                        <div>
                            <?php if ($staffDashboardIsPackage2): ?>
                            <!-- Calendar for Package 2 -->
                            <div class="panel">
                                <div class="panel-header panel-header--blue">
                                    <div>
                                        <h5 class="panel-title">Calendar</h5>
                                        <div class="panel-subtitle">Manage your schedule and events</div>
                                    </div>
                                    <div class="d-flex gap-2">
                                        <a class="btn btn-outline-info btn-sm" href="<?= base_url('Calendar/completion_stats'); ?>">
                                            <i class="mdi mdi-chart-bar mr-1"></i> Completion Stats
                                        </a>
                                        <a class="btn btn-outline-secondary btn-sm" href="<?= base_url('Calendar/print_all'); ?>" target="_blank">
                                            <i class="mdi mdi-printer mr-1"></i> Print All
                                        </a>
                                    </div>
                                </div>
                                <div class="panel-body calendar-container">
                                    <div id="staffCalendar"></div>
                                </div>
                            </div>
                            <?php else: ?>
                            <!-- Accomplishment Summary for non-Package 2 -->
                            <div class="panel">
                                <div class="panel-header panel-header--blue">
                                    <div>
                                        <h5 class="panel-title">Accomplishment Summary</h5>
                                        <div class="panel-subtitle">Top completed task totals for <?= htmlspecialchars($currentMonthLabel, ENT_QUOTES, 'UTF-8'); ?>.</div>
                                    </div>
                                </div>
                                <div class="panel-body p-0">
                                    <?php if (!empty($leaderboardRows)): ?>
                                        <div class="table-responsive">
                                            <table class="leaderboard-table">
                                                <thead>
                                                    <tr>
                                                        <th>Rank</th>
                                                        <th>Assigned Person</th>
                                                        <th class="text-right">Total Points</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php
                                                    $rank = 1;
                                                    $previousTotal = null;
                                                    foreach ($leaderboardRows as $index => $row):
                                                        $totalDone = (int) ($row->total ?? 0);
                                                        if ($previousTotal !== null && $totalDone !== $previousTotal) {
                                                            $rank = $index + 1;
                                                        }
                                                        $previousTotal = $totalDone;
                                                        $suffix = 'th';
                                                        if (!in_array($rank % 100, array(11, 12, 13), true)) {
                                                            switch ($rank % 10) {
                                                                case 1:
                                                                    $suffix = 'st';
                                                                    break;
                                                                case 2:
                                                                    $suffix = 'nd';
                                                                    break;
                                                                case 3:
                                                                    $suffix = 'rd';
                                                                    break;
                                                            }
                                                        }
                                                        $fullName = trim((string) (($row->lName ?? '') . ', ' . ($row->fName ?? '')));
                                                        if ($fullName === ',' || $fullName === '') {
                                                            $fullName = trim((string) ($row->username ?? 'Unassigned'));
                                                        }
                                                        $rankClass = $rank <= 3 ? 'rank-' . $rank : '';
                                                    ?>
                                                        <tr>
                                                            <td>
                                                                <span class="rank-badge <?= htmlspecialchars($rankClass, ENT_QUOTES, 'UTF-8'); ?>">
                                                                    <?= htmlspecialchars($rank . $suffix, ENT_QUOTES, 'UTF-8'); ?>
                                                                </span>
                                                            </td>
                                                            <td class="leader-name"><?= htmlspecialchars($fullName, ENT_QUOTES, 'UTF-8'); ?></td>
                                                            <td class="leader-points<?= $index === 0 ? ' champion' : ''; ?>"><?= number_format($totalDone); ?></td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    <?php else: ?>
                                        <div class="panel-body">
                                            <div class="empty-state">No accomplished tasks found for this month yet.</div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <?php include('includes/footer.php'); ?>
        </div>
    </div>

    <!-- Sticky Notes Widget -->
    <div class="sticky-notes-widget">
        <div class="sticky-notes-panel" id="stickyNotesPanel">
            <div class="sticky-notes-header">
                <h6><i class="mdi mdi-notebook mr-2"></i>Quick Notes</h6>
                <button type="button" class="sticky-notes-close" onclick="toggleStickyNotes()" aria-label="Close quick notes">
                    <i class="mdi mdi-close" aria-hidden="true"></i>
                </button>
            </div>
            <div class="sticky-notes-body">
                <textarea class="sticky-notes-textarea" id="stickyNotesTextarea" placeholder="Type your important notes here..."></textarea>
            </div>
            <div class="sticky-notes-footer">
                <button type="button" class="sticky-notes-clear" onclick="clearStickyNotes()">Clear</button>
                <div>
                    <span class="sticky-notes-saved" id="stickyNotesSaved">Saved!</span>
                    <button type="button" class="sticky-notes-save" onclick="saveStickyNotes()">Save</button>
                </div>
            </div>
        </div>
        <button type="button" class="sticky-notes-toggle" onclick="toggleStickyNotes()" title="Quick Notes" aria-label="Open quick notes">
            <i class="mdi mdi-notebook" aria-hidden="true"></i>
        </button>
    </div>

    <?php include('includes/themecustomizer.php'); ?>

    <script src="<?= base_url(); ?>assets/js/vendor.min.js"></script>
    <script src="<?= base_url(); ?>assets/js/app.min.js"></script>
    <script src="<?= base_url(); ?>assets/libs/sweetalert2/sweetalert2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>

    <script>
        // Sticky Notes Widget Functionality
        function toggleStickyNotes() {
            const panel = document.getElementById('stickyNotesPanel');
            panel.classList.toggle('is-visible');
        }

        function saveStickyNotes() {
            const textarea = document.getElementById('stickyNotesTextarea');
            const notes = textarea.value;
            localStorage.setItem('staffStickyNotes', notes);
            
            const savedIndicator = document.getElementById('stickyNotesSaved');
            savedIndicator.style.display = 'inline';
            setTimeout(() => {
                savedIndicator.style.display = 'none';
            }, 2000);
        }

        function clearStickyNotes() {
            const textarea = document.getElementById('stickyNotesTextarea');
            textarea.value = '';
            localStorage.removeItem('staffStickyNotes');
        }

        // Load saved notes on page load
        document.addEventListener('DOMContentLoaded', function() {
            const savedNotes = localStorage.getItem('staffStickyNotes');
            if (savedNotes) {
                const textarea = document.getElementById('stickyNotesTextarea');
                textarea.value = savedNotes;
            }

            // Show task reminder alert
            const taskDueToday = <?= $taskDueTodayValue; ?>;
            const taskOverdue = <?= $taskOverdueValue; ?>;
            const overdueTasks = <?= json_encode(isset($overdueTasks) ? $overdueTasks : array()); ?>;
            const dueTodayTasks = <?= json_encode(isset($dueTodayTasks) ? $dueTodayTasks : array()); ?>;
            
            if (taskOverdue > 0 || taskDueToday > 0) {
                let message = '';
                let icon = '';
                let title = '';
                let taskDetails = '';
                
                function getRandomTask(tasks) {
                    if (!tasks || tasks.length === 0) return null;
                    const randomIndex = Math.floor(Math.random() * tasks.length);
                    return tasks[randomIndex];
                }
                
                function formatTaskDetails(task) {
                    if (!task) return '';
                    const taskName = task.task || task.taskID || 'No task name';
                    const dueDate = task.dueDate ? new Date(task.dueDate).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }) : 'No due date';
                    const priority = task.priority || 'Normal';
                    const reportedDate = task.reportedDate ? new Date(task.reportedDate).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }) : 'No date';
                    return `<div class="staff-dashboard-task-detail">
                        <div class="staff-dashboard-task-detail__title">${esc(taskName)}</div>
                        <div>Priority: ${esc(priority)}</div>
                        <div>Reported: ${esc(reportedDate)}</div>
                        <div>Due: ${esc(dueDate)}</div>
                    </div>`;
                }
                
                if (taskOverdue > 0 && taskDueToday > 0) {
                    title = 'Task Reminders';
                    const randomOverdue = getRandomTask(overdueTasks);
                    const randomDueToday = getRandomTask(dueTodayTasks);
                    message = `You have <strong>${taskOverdue} overdue task(s)</strong> and <strong>${taskDueToday} task(s) due today</strong>. Please prioritize these tasks.`;
                    if (randomOverdue) {
                        message += formatTaskDetails(randomOverdue);
                    }
                    icon = 'warning';
                } else if (taskOverdue > 0) {
                    title = 'Overdue Tasks';
                    const randomTask = getRandomTask(overdueTasks);
                    message = `You have <strong>${taskOverdue} overdue task(s)</strong>. Please address them as soon as possible.`;
                    if (randomTask) {
                        message += formatTaskDetails(randomTask);
                    }
                    icon = 'error';
                } else if (taskDueToday > 0) {
                    title = 'Tasks Due Today';
                    const randomTask = getRandomTask(dueTodayTasks);
                    message = `You have <strong>${taskDueToday} task(s) due today</strong>. Make sure to complete them on time.`;
                    if (randomTask) {
                        message += formatTaskDetails(randomTask);
                    }
                    icon = 'info';
                }
                
                Swal.fire({
                    title: title,
                    html: message,
                    icon: icon,
                    timer: 12000,
                    timerProgressBar: true,
                    showConfirmButton: true,
                    confirmButtonText: 'Got it',
                    confirmButtonColor: '#667eea',
                    backdrop: `rgba(0,0,0,0.4)`
                });
            }
        });

        // Auto-save notes every 30 seconds
        setInterval(function() {
            const textarea = document.getElementById('stickyNotesTextarea');
            if (textarea.value) {
                localStorage.setItem('staffStickyNotes', textarea.value);
            }
        }, 30000);
    </script>
    <?php if ($staffDashboardIsPackage2): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const calendarEl = document.getElementById('staffCalendar');
            if (!calendarEl) return;

            function pad(value) {
                return String(value).padStart(2, '0');
            }

            function formatDateTimeLocal(date) {
                return [
                    date.getFullYear(),
                    '-',
                    pad(date.getMonth() + 1),
                    '-',
                    pad(date.getDate()),
                    'T',
                    pad(date.getHours()),
                    ':',
                    pad(date.getMinutes())
                ].join('');
            }

            function esc(s) {
                return $('<div>').text(s || '').html();
            }

            const calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,timeGridDay'
                },
                selectable: true,
                selectMirror: true,
                editable: true,
                nowIndicator: true,
                slotMinTime: '06:00:00',
                slotMaxTime: '22:00:00',
                slotDuration: '00:30:00',
                allDaySlot: true,
                height: 'auto',
                events: '<?= site_url("calendar/get_events") ?>',

                eventDataTransform(raw) {
                    const color = raw.color || '#dc3545';
                    raw.extendedProps = raw.extendedProps || {};
                    raw.extendedProps.description = raw.description || '';
                    raw.extendedProps.notes = raw.notes || '';
                    raw.extendedProps.status = raw.status || 'private';
                    raw.extendedProps.color = color;
                    raw.extendedProps.location = raw.location || '';
                    raw.extendedProps.reminder_email_enabled = !!raw.reminder_email_enabled;
                    raw.extendedProps.reminder_email = raw.reminder_email || '';
                    raw.extendedProps.own = !!raw.own;
                    raw.extendedProps.is_completed = parseInt(raw.is_completed, 10) || 1;
                    const canEdit = raw.extendedProps.canEdit || false;
                    raw.extendedProps.editable = canEdit;
                    raw.editable = canEdit;
                    raw.startEditable = canEdit;
                    raw.durationEditable = canEdit;
                    raw.backgroundColor = color;
                    raw.borderColor = color;
                    raw.textColor = '#fff';
                    
                    if (raw.allDay && raw.end) {
                        const endDate = new Date(raw.end);
                        endDate.setDate(endDate.getDate() + 1);
                        raw.end = endDate.toISOString();
                    }
                    
                    if (raw.extendedProps.is_completed == 0) {
                        raw.backgroundColor = adjustColorOpacity(color, 0.4);
                        raw.borderColor = adjustColorOpacity(color, 0.4);
                    }
                    
                    return raw;
                },

                eventDidMount(info) {
                    const c = info.event.extendedProps.color || '#dc3545';
                    const isCompleted = info.event.extendedProps.is_completed;
                    
                    if (isCompleted == 0) {
                        info.el.style.backgroundColor = adjustColorOpacity(c, 0.4);
                        info.el.style.borderColor = adjustColorOpacity(c, 0.4);
                        info.el.style.opacity = '0.6';
                    } else {
                        info.el.style.backgroundColor = c;
                        info.el.style.borderColor = c;
                    }
                    info.el.style.color = '#fff';
                },

                eventContent(arg) {
                    const isCompleted = arg.event.extendedProps.is_completed;
                    const isOwn = arg.event.extendedProps.own;
                    const canComplete = arg.event.extendedProps.canComplete || false;
                    const checkIcon = (isCompleted == 0) ? '<i class="mdi mdi-check-circle" aria-hidden="true"></i>' : '';
                    const toggleBtn = (isOwn || canComplete) ? '<span class="evt-toggle-complete" data-event-id="' + arg.event.id + '" role="button" tabindex="0" aria-label="Toggle event completion"><i class="mdi mdi-check-circle-outline" aria-hidden="true"></i></span>' : '';
                    return {
                        html: '<div class="evt-title">' + checkIcon + esc(arg.event.title) + toggleBtn + '</div>' +
                            (arg.event.extendedProps.description ?
                                '<div class="evt-desc">' + esc(arg.event.extendedProps.description) + '</div>' :
                                '')
                    };
                },

                select(info) {
                    window.location.href = '<?= base_url('Calendar'); ?>';
                },

                dateClick(info) {
                    if (calendar.view.type === 'dayGridMonth') {
                        window.location.href = '<?= base_url('Calendar'); ?>';
                    }
                },

                eventClick(info) {
                    window.location.href = '<?= base_url('Calendar'); ?>';
                },

                eventDrop(info) {
                    if (!info.event.extendedProps.canEdit) {
                        info.revert();
                        return;
                    }

                    let endDate = info.event.end || info.event.start;
                    if (info.event.allDay && info.event.end) {
                        endDate = new Date(info.event.end.getTime() - 86400000);
                    }

                    const payload = {
                        event_id: info.event.id,
                        title: info.event.title,
                        description: info.event.extendedProps.description || '',
                        notes: info.event.extendedProps.notes || '',
                        start_date: formatDateTimeLocal(info.event.start),
                        end_date: formatDateTimeLocal(endDate),
                        all_day: info.event.allDay ? 1 : 0,
                        event_type: 'default',
                        color: info.event.extendedProps.color || '#dc3545',
                        location: info.event.extendedProps.location || '',
                        reminder_email_enabled: info.event.extendedProps.reminder_email_enabled ? 1 : 0,
                        reminder_email: info.event.extendedProps.reminder_email || '',
                        is_public: info.event.extendedProps.status === 'public' ? 1 : 0,
                        is_completed: parseInt(info.event.extendedProps.is_completed, 10) || 1
                    };

                    $.post('<?= site_url("calendar/update_event") ?>', payload, function(res) {
                        if (!res.success) {
                            alert(res.message || 'Unable to update event.');
                            info.revert();
                        }
                    }, 'json').fail(function() {
                        alert('Unable to update event.');
                        info.revert();
                    });
                },

                eventResize(info) {
                    if (!info.event.extendedProps.canEdit) {
                        info.revert();
                        return;
                    }

                    let endDate = info.event.end || info.event.start;
                    if (info.event.allDay && info.event.end) {
                        endDate = new Date(info.event.end.getTime() - 86400000);
                    }

                    const payload = {
                        event_id: info.event.id,
                        title: info.event.title,
                        description: info.event.extendedProps.description || '',
                        notes: info.event.extendedProps.notes || '',
                        start_date: formatDateTimeLocal(info.event.start),
                        end_date: formatDateTimeLocal(endDate),
                        all_day: info.event.allDay ? 1 : 0,
                        event_type: 'default',
                        color: info.event.extendedProps.color || '#dc3545',
                        location: info.event.extendedProps.location || '',
                        reminder_email_enabled: info.event.extendedProps.reminder_email_enabled ? 1 : 0,
                        reminder_email: info.event.extendedProps.reminder_email || '',
                        is_public: info.event.extendedProps.status === 'public' ? 1 : 0,
                        is_completed: parseInt(info.event.extendedProps.is_completed, 10) || 1
                    };

                    $.post('<?= site_url("calendar/update_event") ?>', payload, function(res) {
                        if (!res.success) {
                            alert(res.message || 'Unable to resize event.');
                            info.revert();
                        }
                    }, 'json').fail(function() {
                        alert('Unable to resize event.');
                        info.revert();
                    });
                }
            });

            function adjustColorOpacity(hex, opacity) {
                const r = parseInt(hex.slice(1, 3), 16);
                const g = parseInt(hex.slice(3, 5), 16);
                const b = parseInt(hex.slice(5, 7), 16);
                return `rgba(${r}, ${g}, ${b}, ${opacity})`;
            }

            calendar.render();

            // Quick toggle completion status
            $(document).on('keydown', '.evt-toggle-complete', function(e) {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    $(this).trigger('click');
                }
            });

            $(document).on('click', '.evt-toggle-complete', function(e) {
                e.stopPropagation();
                const eventId = $(this).data('event-id');
                const event = calendar.getEventById(eventId);
                if (!event) {
                    console.log('Toggle complete: Event not found', eventId);
                    return;
                }

                const canComplete = event.extendedProps.canComplete || false;
                const isOwn = event.extendedProps.own || false;
                
                if (!isOwn && !canComplete) {
                    console.log('Toggle complete: No permission');
                    return;
                }

                const newStatus = event.extendedProps.is_completed == 0 ? 1 : 0;

                $.post('<?= site_url("calendar/toggle_completion") ?>', {
                    event_id: eventId,
                    is_completed: newStatus
                }, function(response) {
                    if (response.success) {
                        calendar.refetchEvents();
                    } else {
                        alert(response.message || 'Error updating completion status');
                    }
                }, 'json').fail(function() {
                    alert('Error updating completion status');
                });
            });

            // Reminder notification functionality
            const reminders = <?php echo json_encode(isset($reminders) ? $reminders : array()); ?>;

            function checkReminders() {
                const today = new Date();
                today.setHours(0, 0, 0, 0);

                reminders.forEach(reminder => {
                    if (!reminder.is_active) return;

                    const reminderDate = new Date(reminder.next_reminder_date);
                    const daysBefore = parseInt(reminder.reminder_days_before) || 3;
                    const alertDate = new Date(reminderDate);
                    alertDate.setDate(alertDate.getDate() - daysBefore);

                    if (today >= alertDate && today <= reminderDate) {
                        const daysDiff = Math.ceil((reminderDate - today) / (1000 * 60 * 60 * 24));
                        let message = reminder.title;
                        if (daysDiff > 0) {
                            message += ` - ${daysDiff} day${daysDiff > 1 ? 's' : ''} remaining`;
                        } else {
                            message += ' - Due today!';
                        }

                        if (reminder.description) {
                            message += '\n\n' + reminder.description;
                        }

                        // Determine color based on urgency
                        let iconType = 'info';
                        let backgroundColor = '#3b82f6';
                        let iconColor = '#ffffff';

                        if (daysDiff === 0) {
                            iconType = 'warning';
                            backgroundColor = '#f59e0b';
                            iconColor = '#ffffff';
                        } else if (daysDiff <= 1) {
                            iconType = 'warning';
                            backgroundColor = '#f97316';
                            iconColor = '#ffffff';
                        } else if (daysDiff <= 3) {
                            iconType = 'info';
                            backgroundColor = '#8b5cf6';
                            iconColor = '#ffffff';
                        } else {
                            iconType = 'info';
                            backgroundColor = '#10b981';
                            iconColor = '#ffffff';
                        }

                        const Toast = Swal.mixin({
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 10000,
                            timerProgressBar: true,
                            background: backgroundColor,
                            color: iconColor,
                            customClass: {
                                popup: 'colored-toast'
                            },
                            didOpen: (toast) => {
                                toast.addEventListener('mouseenter', Swal.stopTimer)
                                toast.addEventListener('mouseleave', Swal.resumeTimer)
                            }
                        });

                        Toast.fire({
                            icon: iconType,
                            title: 'Reminder',
                            text: message
                        });
                    }
                });
            }

            $('#addReminderBtn').on('click', function() {
                $reminderForm[0].reset();
                $('#reminderId').val('');
                $('#reminderDaysBefore').val(3);
                $('#reminderModal').modal('show');
            });

            $('#saveReminderBtn').on('click', function() {
                let formData = $reminderForm.serialize();
                const startDate = $('#reminderStartDate').val();
                const frequency = $('#reminderFrequency').val();

                console.log('Save button clicked');
                console.log('Start date:', startDate);
                console.log('Frequency:', frequency);

                if (!startDate) {
                    alert('Please select a start date');
                    return;
                }

                const nextReminderDate = calculateNextReminderDate(startDate, frequency);
                formData += '&next_reminder_date=' + encodeURIComponent(nextReminderDate);

                console.log('Form data:', formData);
                console.log('Next reminder date:', nextReminderDate);

                $.post('<?= site_url("Page/saveReminder"); ?>', formData, function(response) {
                    console.log('Response:', response);
                    if (response.success) {
                        $reminderModal.modal('hide');
                        alert('Reminder saved successfully');
                        location.reload();
                    } else {
                        alert(response.message || 'Error saving reminder');
                    }
                }, 'json').fail(function(xhr, status, error) {
                    console.log('AJAX failed:', status, error);
                    console.log('Response text:', xhr.responseText);
                    alert('Error saving reminder: ' + error);
                });
            });

            // Check reminders on page load
            checkReminders();
        });
    </script>
    <?php endif; ?>
</body>

</html>
