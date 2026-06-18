<?php
$calendarLevel = strtolower(trim((string) $this->session->userdata('level')));
$calendarSettingsID = (int) $this->session->userdata('settingsID');
$calendarIsPackage2 = false;

if ($calendarSettingsID > 0 && in_array($calendarLevel, array('admin', 'staff', 'account'), true) && $this->db->table_exists('company_features')) {
    $calendarFeatureRows = $this->db
        ->select('feature_key')
        ->from('company_features')
        ->where('settingsID', $calendarSettingsID)
        ->where('is_enabled', 1)
        ->get()
        ->result();

    $calendarEnabledFeatures = array();
    foreach ($calendarFeatureRows as $row) {
        $featureKey = trim((string) ($row->feature_key ?? ''));
        if ($featureKey !== '') {
            $calendarEnabledFeatures[] = $featureKey;
        }
    }

    $calendarPackage2Features = array('tasks', 'notes', 'calendar');
    $calendarIsPackage2 = count($calendarEnabledFeatures) === count($calendarPackage2Features) &&
        count(array_intersect($calendarEnabledFeatures, $calendarPackage2Features)) === count($calendarPackage2Features);
}
?>
<!DOCTYPE html>
<html lang="en">
<?php include('includes/head.php'); ?>

<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css" rel="stylesheet">

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

    .calendar-wrap {
        padding: 14px;
    }

    .calendar-toolbar-note {
        max-width: 520px;
        color: #6b7280;
        font-size: 12.5px;
        line-height: 1.5;
    }

    .fc .fc-highlight {
        background: rgba(220, 53, 69, .18) !important;
    }

    .fc .fc-toolbar-title {
        font-weight: 900;
        letter-spacing: .2px;
    }

    .fc .fc-button {
        border-radius: 10px;
        font-weight: 800;
    }

    .fc .fc-daygrid-day-number,
    .fc .fc-timegrid-slot-label-cushion,
    .fc .fc-col-header-cell-cushion {
        font-weight: 700;
    }

    .fc .fc-daygrid-event,
    .fc .fc-timegrid-event {
        border-radius: 10px;
        cursor: pointer;
        border-width: 0;
        box-shadow: 0 8px 20px rgba(15, 23, 42, .12);
        white-space: normal !important;
        align-items: flex-start;
    }

    .fc .fc-daygrid-event .fc-event-main,
    .fc .fc-timegrid-event .fc-event-main,
    .fc .fc-daygrid-event .fc-event-main-frame,
    .fc .fc-daygrid-event .fc-event-title-container,
    .fc .fc-daygrid-event .fc-event-title,
    .fc .fc-timegrid-event .fc-event-title {
        white-space: normal !important;
        overflow: visible !important;
        text-overflow: clip !important;
        word-break: break-word;
        overflow-wrap: anywhere;
    }

    .fc .fc-daygrid-day-events {
        min-height: auto;
    }

    .fc .fc-daygrid-event-harness {
        white-space: normal;
    }

    .evt-title {
        font-weight: 900;
        font-size: 12.5px;
        line-height: 1.2;
        white-space: normal;
        word-break: break-word;
        overflow-wrap: anywhere;
    }

    .evt-desc {
        font-size: 11px;
        opacity: .9;
        line-height: 1.2;
        margin-top: 2px;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
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

    .form-help {
        color: #6b7280;
        font-size: 12px;
        margin-top: 6px;
    }

    .event-fieldset {
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        padding: 14px;
        margin-bottom: 14px;
        background: #fbfdff;
    }

    .event-fieldset legend {
        width: auto;
        padding: 0 8px;
        margin-bottom: 0;
        font-size: 12px;
        font-weight: 800;
        color: #2563eb;
    }

    /* Responsive Styles */
    @media (max-width: 768px) {
        .calendar-wrap {
            padding: 10px;
        }

        .calendar-toolbar-note {
            max-width: 100%;
            font-size: 11px;
        }

        .fc .fc-toolbar {
            flex-direction: column;
            gap: 10px;
        }

        .fc .fc-toolbar-chunk {
            display: flex;
            justify-content: center;
            width: 100%;
        }

        .fc .fc-toolbar-title {
            font-size: 1.2rem;
        }

        .fc .fc-button {
            font-size: 0.85rem;
            padding: 6px 12px;
        }

        .fc .fc-daygrid-day-number,
        .fc .fc-col-header-cell-cushion {
            font-size: 0.85rem;
            padding: 4px 2px;
        }

        .evt-title {
            font-size: 11px;
        }

        .evt-desc {
            font-size: 10px;
            -webkit-line-clamp: 1;
        }

        .modal-dialog {
            margin: 10px;
            max-width: calc(100% - 20px);
        }

        .modal-content {
            border-radius: 10px;
        }

        .d-flex.gap-2 {
            flex-direction: column;
            width: 100%;
        }

        .d-flex.gap-2 .btn {
            width: 100%;
            text-align: center;
        }

        .d-flex.flex-wrap {
            flex-direction: column;
            align-items: stretch;
        }

        .d-flex.flex-wrap .d-flex {
            width: 100%;
            justify-content: center;
        }
    }

    @media (max-width: 576px) {
        .fc .fc-toolbar-title {
            font-size: 1rem;
        }

        .fc .fc-button {
            font-size: 0.75rem;
            padding: 5px 10px;
        }

        .fc .fc-daygrid-day-number,
        .fc .fc-col-header-cell-cushion {
            font-size: 0.75rem;
            padding: 3px 1px;
        }

        .evt-title {
            font-size: 10px;
        }

        .evt-desc {
            font-size: 9px;
        }

        .calendar-wrap {
            padding: 8px;
        }

        .event-fieldset {
            padding: 10px;
            margin-bottom: 10px;
        }

        .event-fieldset legend {
            font-size: 11px;
        }

        .form-help {
            font-size: 11px;
        }
    }

    @media (max-width: 480px) {
        .fc .fc-daygrid {
            font-size: 0.7rem;
        }

        .fc .fc-timegrid-slot-label {
            font-size: 0.7rem;
        }

        .fc .fc-timegrid-event {
            font-size: 0.7rem;
        }

        .fc .fc-daygrid-event-harness {
            margin-bottom: 2px;
        }

        .evt-title-mobile {
            cursor: pointer;
        }

        .evt-title-mobile:hover {
            opacity: 0.8;
        }
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
                        <div class="calendar-wrap">
                            <div class="d-flex flex-wrap justify-content-between align-items-start mb-3">
                                <div>
                                    <h5 class="mb-1">Calendar</h5>
                                </div>
                                <div class="d-flex gap-2 mt-2 mt-sm-0 align-items-center">
                                    <div class="mr-2">
                                        <select class="form-control form-control-sm" id="eventTypeFilter" style="width: auto;">
                                            <option value="all">All Events</option>
                                            <option value="work">Work-Related</option>
                                            <option value="personal">Personal</option>
                                        </select>
                                    </div>
                                    <a class="btn btn-outline-info btn-sm" href="<?= base_url('Calendar/event_types'); ?>">
                                        <i class="mdi mdi-calendar-clock mr-1"></i> Event Types
                                    </a>
                                    <a class="btn btn-outline-info btn-sm" href="<?= base_url('Calendar/availability'); ?>">
                                        <i class="mdi mdi-clock-outline mr-1"></i> Availability
                                    </a>
                                    <a class="btn btn-outline-success btn-sm" href="<?= base_url('Calendar/completion_stats'); ?>">
                                        <i class="mdi mdi-chart-bar mr-1"></i> Completion Stats
                                    </a>
                                    <a class="btn btn-outline-secondary btn-sm" href="<?= base_url('Calendar/print_all'); ?>" target="_blank">
                                        <i class="mdi mdi-printer mr-1"></i> Print All
                                    </a>
                                </div>
                            </div>
                            <div id="calendar"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="eventModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <h5 class="modal-title mb-0">Event Details</h5>
                        <div class="small text-muted">Set the schedule, notes, and reminder details before saving.</div>
                    </div>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>

                <div class="modal-body">
                    <form id="eventForm" method="POST" action="<?= site_url('calendar/add_event') ?>">
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label class="font-weight-bold">Event Title *</label>
                                <input type="text" class="form-control" name="title" id="eventTitle" required>
                            </div>

                            <div class="col-md-12">
                                <fieldset class="event-fieldset">
                                    <legend>Schedule</legend>
                                    <div class="row">
                                        <div class="col-md-12 mb-3">
                                            <div class="custom-control custom-switch">
                                                <input type="checkbox" class="custom-control-input" id="eventAllDay" name="all_day">
                                                <label class="custom-control-label" for="eventAllDay">All day event</label>
                                            </div>
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <label class="font-weight-bold">Start *</label>
                                            <input type="datetime-local" class="form-control" name="start_date" id="eventStartDate" required>
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <label class="font-weight-bold">End *</label>
                                            <input type="datetime-local" class="form-control" name="end_date" id="eventEndDate" required>
                                        </div>
                                    </div>
                                </fieldset>
                            </div>

                            <div class="col-md-12">
                                <fieldset class="event-fieldset">
                                    <legend>Details</legend>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="font-weight-bold">Location</label>
                                            <input type="text" class="form-control" name="location" id="eventLocation">
                                        </div>

                                        <?php
                                        $userLevel = strtolower(trim((string) $this->session->userdata('level')));
                                        $isStudent = ($userLevel === 'student');
                                        ?>
                                        <div class="col-md-3 mb-3">
                                            <label class="font-weight-bold">Visibility</label>
                                            <?php if ($isStudent): ?>
                                                <input type="hidden" name="is_public" id="eventStatusHidden" value="0">
                                                <input type="text" class="form-control" value="Private" disabled>
                                            <?php else: ?>
                                                <select class="form-control" name="is_public" id="eventStatus">
                                                    <option value="0">Private</option>
                                                    <option value="1">Public</option>
                                                </select>
                                            <?php endif; ?>
                                        </div>

                                        <div class="col-md-3 mb-3">
                                            <label class="font-weight-bold">Event Color</label>
                                            <input type="color" class="form-control" name="color" id="eventColor" value="#dc3545">
                                        </div>

                                        <div class="col-md-12 mb-3">
                                            <div class="custom-control custom-switch">
                                                <input type="checkbox" class="custom-control-input" id="eventCompleted" name="is_completed">
                                                <label class="custom-control-label" for="eventCompleted">Mark as completed</label>
                                            </div>
                                        </div>

                                        <?php if (!$calendarIsPackage2): ?>
                                        <div class="col-md-12 mb-3">
                                            <div class="custom-control custom-switch">
                                                <input type="checkbox" class="custom-control-input" id="createAsTask" name="create_as_task">
                                                <label class="custom-control-label" for="createAsTask">Create as Project Task</label>
                                            </div>
                                        </div>

                                        <div class="col-md-12 mb-3" id="taskFields" style="display:none;">
                                            <div class="row">
                                                <div class="col-md-6 mb-2">
                                                    <label class="font-weight-bold">Project</label>
                                                    <select class="form-control" name="project_id" id="eventProject">
                                                        <option value="">Select Project</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-6 mb-2">
                                                    <label class="font-weight-bold">Priority</label>
                                                    <select class="form-control" name="priority" id="eventPriority">
                                                        <option value="3">Low</option>
                                                        <option value="2">Medium</option>
                                                        <option value="1">High</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <?php endif; ?>

                                        <div class="col-md-12 mb-3">
                                            <label class="font-weight-bold">Description</label>
                                            <textarea class="form-control" name="description" id="eventDescription" rows="2"></textarea>
                                        </div>

                                        <div class="col-md-12 mb-0">
                                            <label class="font-weight-bold">Notes</label>
                                            <textarea class="form-control" name="notes" id="eventNotes" rows="3"></textarea>
                                        </div>

                                        <div class="col-md-12 mb-0" id="moveHistoryContainer" style="display:none;">
                                            <label class="font-weight-bold">Move History</label>
                                            <div id="moveHistoryList" class="mt-2"></div>
                                        </div>
                                    </div>
                                </fieldset>
                            </div>

                            <div class="col-md-12">
                                <fieldset class="event-fieldset mb-0">
                                    <legend>Reminder</legend>
                                    <div class="row">
                                        <div class="col-md-12 mb-3">
                                            <div class="custom-control custom-switch">
                                                <input type="checkbox" class="custom-control-input" id="eventReminderEnabled" name="reminder_email_enabled" checked>
                                                <label class="custom-control-label" for="eventReminderEnabled">Send email reminder 1 day before the activity</label>
                                            </div>
                                        </div>

                                        <div class="col-md-12">
                                            <label class="font-weight-bold">Reminder Email</label>
                                            <input type="email" class="form-control" name="reminder_email" id="eventReminderEmail" value="<?= htmlspecialchars((string) $this->session->userdata('email'), ENT_QUOTES, 'UTF-8'); ?>">
                                        </div>
                                    </div>
                                </fieldset>
                            </div>

                            <div class="col-md-12 mb-3">
                                <label class="font-weight-bold">Event Type *</label>
                                <select class="form-control" name="event_type" id="eventType" required>
                                    <option value="work">Work-Related</option>
                                    <option value="personal">Personal</option>
                                </select>
                            </div>
                            <input type="hidden" name="event_id" id="eventId">
                        </div>
                    </form>
                </div>

                <div class="modal-footer d-flex justify-content-between">
                    <button class="btn btn-light" data-dismiss="modal">Cancel</button>

                    <div>
                        <button id="deleteEventBtn" class="btn btn-outline-danger d-none mr-2">
                            <i class="mdi mdi-delete-outline"></i>
                        </button>
                        <button id="saveEventBtn" class="btn btn-info">
                            <i class="mdi mdi-content-save-outline mr-1"></i> Save Event
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include('includes/themecustomizer.php'); ?>
    <script src="<?= base_url(); ?>assets/js/vendor.min.js"></script>
    <script src="<?= base_url(); ?>assets/js/app.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const calendarEl = document.getElementById('calendar');
            const isStudent = '<?= $isStudent ? 'true' : 'false'; ?>' === 'true';
            const deleteBtn = $('#deleteEventBtn');
            const $eventForm = $('#eventForm');
            const $allDay = $('#eventAllDay');
            const $start = $('#eventStartDate');
            const $end = $('#eventEndDate');
            const $reminderEnabled = $('#eventReminderEnabled');
            const $reminderEmail = $('#eventReminderEmail');
            const $completed = $('#eventCompleted');
            const $createAsTask = $('#createAsTask');
            const $taskFields = $('#taskFields');

            // Toggle task fields visibility
            $createAsTask.on('change', function() {
                if ($(this).is(':checked')) {
                    $taskFields.show();
                    loadProjects();
                } else {
                    $taskFields.hide();
                }
            });

            // Event type filter
            $('#eventTypeFilter').on('change', function() {
                const filter = $(this).val();
                calendar.refetchEvents();
            });

            function loadProjects() {
                $.get('<?= site_url("calendar/get_projects") ?>', function(response) {
                    const select = $('#eventProject');
                    select.html('<option value="">Select Project</option>');
                    if (response.success && response.data) {
                        response.data.forEach(project => {
                            select.append(`<option value="${project.projectID}">${project.projectDescription}</option>`);
                        });
                    }
                }, 'json');
            }

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

            function toDate(value) {
                if (!value) return null;
                const date = value instanceof Date ? value : new Date(value);
                return Number.isNaN(date.getTime()) ? null : date;
            }

            function setDateRange(startDate, endDate, allDay) {
                const safeStart = toDate(startDate) || new Date();
                let safeEnd = toDate(endDate);

                if (!safeEnd || safeEnd < safeStart) {
                    safeEnd = new Date(safeStart.getTime() + (allDay ? 86400000 : 3600000));
                }

                if (allDay) {
                    safeStart.setHours(0, 0, 0, 0);
                    safeEnd.setHours(23, 59, 0, 0);
                }

                $start.val(formatDateTimeLocal(safeStart));
                $end.val(formatDateTimeLocal(safeEnd));
                $allDay.prop('checked', !!allDay);
                updateDateFieldMode();
            }

            function updateDateFieldMode() {
                const allDay = $allDay.is(':checked');
                const startValue = $start.val();
                const endValue = $end.val();

                // Always use datetime-local to allow time editing
                $start.attr('type', 'datetime-local');
                $end.attr('type', 'datetime-local');

                if (startValue) {
                    if (allDay && startValue.length === 10) {
                        $start.val(startValue + 'T00:00');
                    } else if (startValue.length === 10) {
                        $start.val(startValue + 'T09:00');
                    } else {
                        $start.val(startValue.slice(0, 16));
                    }
                }
                if (endValue) {
                    if (allDay && endValue.length === 10) {
                        $end.val(endValue + 'T23:59');
                    } else if (endValue.length === 10) {
                        $end.val(endValue + 'T10:00');
                    } else {
                        $end.val(endValue.slice(0, 16));
                    }
                }
            }

            function toggleReminderEmailState() {
                $reminderEmail.prop('disabled', !$reminderEnabled.is(':checked'));
            }

            function resetForm() {
                $eventForm[0].reset();
                $('#eventId').val('');
                $('#eventColor').val('#dc3545');
                $('#eventType').val('work');
                $('#eventDescription').val('');
                $('#eventNotes').val('');
                $('#eventLocation').val('');
                if ($('#eventStatus').length) {
                    $('#eventStatus').val('0');
                }
                $reminderEnabled.prop('checked', true);
                $completed.prop('checked', false);
                $createAsTask.prop('checked', false);
                $taskFields.hide();
                deleteBtn.addClass('d-none');
                $eventForm.find('input, textarea, select').prop('disabled', false);
                $('#saveEventBtn').prop('disabled', false);
                setDateRange(new Date(), new Date(new Date().getTime() + 3600000), false);
                toggleReminderEmailState();
            }

            function esc(s) {
                return $('<div>').text(s || '').html();
            }

            function adjustColorOpacity(hex, opacity) {
                const r = parseInt(hex.slice(1, 3), 16);
                const g = parseInt(hex.slice(3, 5), 16);
                const b = parseInt(hex.slice(5, 7), 16);
                return `rgba(${r}, ${g}, ${b}, ${opacity})`;
            }

            function buildPayload() {
                const allDay = $allDay.is(':checked');
                let endDate = $end.val();
                
                return {
                    event_id: $('#eventId').val(),
                    title: $('#eventTitle').val(),
                    description: $('#eventDescription').val() || '',
                    notes: $('#eventNotes').val() || '',
                    start_date: $start.val(),
                    end_date: endDate,
                    all_day: allDay ? 1 : 0,
                    event_type: $('#eventType').val() || 'default',
                    color: $('#eventColor').val(),
                    location: $('#eventLocation').val() || '',
                    reminder_email_enabled: $reminderEnabled.is(':checked') ? 1 : 0,
                    reminder_email: $reminderEmail.val() || '',
                    is_public: $('#eventStatus').length ? $('#eventStatus').val() : 0,
                    is_completed: $completed.is(':checked') ? 0 : 1,
                    create_as_task: $createAsTask.is(':checked') ? 1 : 0,
                    project_id: $('#eventProject').val() || '',
                    priority: $('#eventPriority').val() || '3'
                };
            }

            function loadEventIntoForm(event) {
                const canEdit = event.extendedProps.canEdit || false;
                const isCompleted = event.extendedProps.is_completed || false;

                $('#eventId').val(event.id);
                $('#eventTitle').val(event.title);
                $('#eventDescription').val(event.extendedProps.description || '');
                $('#eventNotes').val(event.extendedProps.notes || '');
                $('#eventLocation').val(event.extendedProps.location || '');
                $('#eventColor').val(event.extendedProps.color || '#dc3545');
                if ($('#eventStatus').length) {
                    $('#eventStatus').val(event.extendedProps.status === 'public' ? '1' : '0');
                }
                $reminderEnabled.prop('checked', !!event.extendedProps.reminder_email_enabled);
                $reminderEmail.val(event.extendedProps.reminder_email || '<?= htmlspecialchars((string) $this->session->userdata('email'), ENT_QUOTES, 'UTF-8'); ?>');
                $completed.prop('checked', isCompleted == 0);
                
                console.log('loadEventIntoForm - Event ID:', event.id, 'is_completed:', isCompleted);
                
                // For all-day events, subtract one day from end date when loading into form
                let eventEnd = event.end || event.start;
                if (event.allDay && event.end) {
                    eventEnd = new Date(event.end.getTime() - 86400000);
                }
                setDateRange(event.start, eventEnd, event.allDay);
                toggleReminderEmailState();

                // Load move history
                loadMoveHistory(event.id);

                if (!canEdit) {
                    $eventForm.find('input, textarea, select').prop('disabled', true);
                    $('#saveEventBtn').prop('disabled', true);
                    deleteBtn.addClass('d-none');
                } else {
                    $eventForm.find('input, textarea, select').prop('disabled', false);
                    $('#saveEventBtn').prop('disabled', false);
                    deleteBtn.removeClass('d-none');
                    toggleReminderEmailState();
                }
            }

            function loadMoveHistory(eventId) {
                $.get('<?= site_url("calendar/get_event_move_history") ?>', { event_id: eventId }, function(response) {
                    const container = document.getElementById('moveHistoryContainer');
                    const list = document.getElementById('moveHistoryList');
                    
                    if (response.success && response.data.length > 0) {
                        container.style.display = 'block';
                        let html = '<div class="list-group">';
                        
                        response.data.forEach(move => {
                            const fromDate = new Date(move.from_date).toLocaleString();
                            const toDate = new Date(move.to_date).toLocaleString();
                            const movedAt = new Date(move.moved_at).toLocaleString();
                            
                            html += `
                                <div class="list-group-item list-group-item-action">
                                    <div class="d-flex w-100 justify-content-between">
                                        <small class="text-muted">Moved on: ${movedAt}</small>
                                    </div>
                                    <p class="mb-1 small">
                                        <strong>From:</strong> ${fromDate}<br>
                                        <strong>To:</strong> ${toDate}
                                    </p>
                                </div>
                            `;
                        });
                        
                        html += '</div>';
                        list.innerHTML = html;
                    } else {
                        container.style.display = 'none';
                        list.innerHTML = '';
                    }
                }, 'json');
            }

            const calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: window.innerWidth < 768 ? 'dayGridMonth' : 'dayGridMonth',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: window.innerWidth < 768 ? 'dayGridMonth' : 'dayGridMonth,timeGridWeek,timeGridDay'
                },
                selectable: true,
                selectMirror: true,
                editable: true,
                nowIndicator: true,
                slotMinTime: '06:00:00',
                slotMaxTime: '22:00:00',
                slotDuration: '00:30:00',
                allDaySlot: true,
                height: window.innerWidth < 768 ? 'auto' : 'auto',
                aspectRatio: window.innerWidth < 768 ? 1.35 : 1.35,
                events: function(info, successCallback, failureCallback) {
                    const filter = $('#eventTypeFilter').val();
                    const url = '<?= site_url("calendar/get_events") ?>';
                    const params = new URLSearchParams({
                        start: info.startStr,
                        end: info.endStr,
                        event_type: filter
                    });

                    fetch(url + '?' + params.toString())
                        .then(response => response.json())
                        .then(data => {
                            if (data.error) {
                                failureCallback(data.error);
                            } else {
                                successCallback(data);
                            }
                        })
                        .catch(error => {
                            failureCallback(error);
                        });
                },

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
                    raw.extendedProps.is_completed = parseInt(raw.is_completed, 10) || 1; // Keep as numeric: 0=completed, 1=not completed
                    const canEdit = raw.extendedProps.canEdit || false;
                    raw.extendedProps.editable = canEdit;
                    raw.editable = canEdit;
                    raw.startEditable = canEdit;
                    raw.durationEditable = canEdit;
                    raw.backgroundColor = color;
                    raw.borderColor = color;
                    raw.textColor = '#fff';
                    
                    // For all-day events, add one day to end date to make it inclusive
                    if (raw.allDay && raw.end) {
                        const endDate = new Date(raw.end);
                        endDate.setDate(endDate.getDate() + 1);
                        raw.end = endDate.toISOString();
                    }
                    
                    // Dim completed events
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
                        // Completed events use #3498db with reduced opacity
                        info.el.style.backgroundColor = '#3498db';
                        info.el.style.borderColor = '#3498db';
                        info.el.style.opacity = '0.6';
                    } else {
                        info.el.style.backgroundColor = c;
                        info.el.style.borderColor = c;
                    }
                    info.el.style.color = '#fff';
                },

                eventContent(arg) {
                    const isMobile = window.innerWidth < 768;
                    const isCompleted = arg.event.extendedProps.is_completed;
                    const isOwn = arg.event.extendedProps.own;
                    const canComplete = arg.event.extendedProps.canComplete || false;
                    const checkIcon = (isCompleted == 0) ? '<i class="mdi mdi-check-circle" style="margin-right:4px;"></i>' : '';
                    const toggleBtn = (isOwn || canComplete) ? '<span class="evt-toggle-complete" data-event-id="' + arg.event.id + '" style="cursor:pointer; margin-left:4px; opacity:0.7;"><i class="mdi mdi-check-circle-outline"></i></span>' : '';
                    const moveBtn = isOwn && (isCompleted != 0) ? '<span class="evt-move-event" data-event-id="' + arg.event.id + '" style="cursor:pointer; margin-left:4px; opacity:0.7;" title="Move to another date"><i class="mdi mdi-arrow-right-bold"></i></span>' : '';

                    if (isMobile) {
                        // On mobile, show just an icon
                        return {
                            html: '<div class="evt-title-mobile" style="display:flex; align-items:center; justify-content:center; width:100%; height:100%; min-height:30px;" onclick="showMobileEventDetail(' + arg.event.id + ')">' +
                                '<i class="mdi mdi-calendar" style="font-size:16px;"></i>' +
                                '</div>'
                        };
                    }

                    return {
                        html: '<div class="evt-title">' + checkIcon + esc(arg.event.title) + toggleBtn + moveBtn + '</div>' +
                            (arg.event.extendedProps.description ?
                                '<div class="evt-desc">' + esc(arg.event.extendedProps.description) + '</div>' :
                                '')
                    };
                },

                select(info) {
                    resetForm();
                    const selectedAllDay = info.allDay || calendar.view.type === 'dayGridMonth';
                    let selectedEnd = info.end || info.start;
                    if (selectedAllDay && info.end) {
                        selectedEnd = new Date(info.end.getTime() - 60000);
                    }
                    setDateRange(info.start, selectedEnd, selectedAllDay);
                    $('#eventModal').modal('show');
                },

                dateClick(info) {
                    if (calendar.view.type === 'dayGridMonth') {
                        resetForm();
                        setDateRange(info.date, new Date(info.date.getTime() + 3600000), false);
                        $('#eventModal').modal('show');
                    }
                },

                eventClick(info) {
                    resetForm();
                    loadEventIntoForm(info.event);
                    $('#eventModal').modal('show');
                },

                eventDrop(info) {
                    if (!info.event.extendedProps.canEdit) {
                        info.revert();
                        return;
                    }

                    let endDate = info.event.end || info.event.start;
                    // For all-day events, subtract one day from end date before sending to server
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
                    // For all-day events, subtract one day from end date before sending to server
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

            calendar.render();
            resetForm();

            // Handle window resize for responsive calendar
            window.addEventListener('resize', function() {
                const isMobile = window.innerWidth < 768;
                if (isMobile) {
                    calendar.changeView('dayGridMonth');
                } else {
                    calendar.changeView('dayGridMonth');
                }
            });

            // Function to show mobile event detail modal
            window.showMobileEventDetail = function(eventId) {
                const event = calendar.getEventById(eventId);
                if (!event) return;

                const isCompleted = event.extendedProps.is_completed;
                const statusText = (isCompleted == 0) ? 'Completed' : 'Pending';
                const statusClass = (isCompleted == 0) ? 'text-success' : 'text-warning';

                const modalHtml = `
                    <div class="modal fade" id="mobileEventModal" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">${esc(event.title)}</h5>
                                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                                </div>
                                <div class="modal-body">
                                    <div class="mb-3">
                                        <strong>Status:</strong> <span class="${statusClass}">${statusText}</span>
                                    </div>
                                    <div class="mb-3">
                                        <strong>Start:</strong> ${event.start ? event.start.toLocaleString() : 'N/A'}
                                    </div>
                                    <div class="mb-3">
                                        <strong>End:</strong> ${event.end ? event.end.toLocaleString() : 'N/A'}
                                    </div>
                                    ${event.extendedProps.location ? `<div class="mb-3"><strong>Location:</strong> ${esc(event.extendedProps.location)}</div>` : ''}
                                    ${event.extendedProps.description ? `<div class="mb-3"><strong>Description:</strong><br>${esc(event.extendedProps.description)}</div>` : ''}
                                    ${event.extendedProps.notes ? `<div class="mb-3"><strong>Notes:</strong><br>${esc(event.extendedProps.notes)}</div>` : ''}
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                </div>
                            </div>
                        </div>
                    </div>
                `;

                // Remove existing modal if any
                const existingModal = document.getElementById('mobileEventModal');
                if (existingModal) {
                    existingModal.remove();
                }

                // Add new modal
                document.body.insertAdjacentHTML('beforeend', modalHtml);

                // Show modal
                $('#mobileEventModal').modal('show');

                // Remove modal after it's hidden
                $('#mobileEventModal').on('hidden.bs.modal', function() {
                    $(this).remove();
                });
            };

            $allDay.on('change', updateDateFieldMode);
            $reminderEnabled.on('change', toggleReminderEmailState);

            // Quick toggle completion status
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
                
                console.log('Toggle complete - Event ID:', eventId, 'canComplete:', canComplete, 'isOwn:', isOwn, 'is_completed:', event.extendedProps.is_completed);
                
                if (!isOwn && !canComplete) {
                    console.log('Toggle complete: No permission');
                    return;
                }

                const newStatus = event.extendedProps.is_completed == 0 ? 1 : 0;
                console.log('Toggle complete: Setting status to', newStatus);

                $.post('<?= site_url("calendar/toggle_completion") ?>', {
                    event_id: eventId,
                    is_completed: newStatus
                }, function(response) {
                    console.log('Toggle complete response:', response);
                    if (response.success) {
                        calendar.refetchEvents();
                    } else {
                        alert(response.message || 'Error updating completion status');
                    }
                }, 'json').fail(function() {
                    alert('Error updating completion status');
                });
            });

            // Move event to another date
            $(document).on('click', '.evt-move-event', function(e) {
                e.stopPropagation();
                const eventId = $(this).data('event-id');
                const event = calendar.getEventById(eventId);
                if (!event || !event.extendedProps.own) return;

                const newStartDate = prompt('Enter new start date (YYYY-MM-DD HH:MM:SS):', formatDateTimeLocal(event.start));
                if (!newStartDate) return;

                const newEndDate = prompt('Enter new end date (YYYY-MM-DD HH:MM:SS):', formatDateTimeLocal(event.end || event.start));
                if (!newEndDate) return;

                $.post('<?= site_url("calendar/move_event") ?>', {
                    event_id: eventId,
                    new_start_date: newStartDate,
                    new_end_date: newEndDate
                }, function(response) {
                    if (response.success) {
                        calendar.refetchEvents();
                        alert('Event moved successfully');
                    } else {
                        alert(response.message || 'Error moving event');
                    }
                }, 'json').fail(function() {
                    alert('Error moving event');
                });
            });

            $('#saveEventBtn').on('click', function() {
                const formData = buildPayload();
                const eventId = formData.event_id;
                const url = eventId ? '<?= site_url("calendar/update_event") ?>' : '<?= site_url("calendar/add_event") ?>';

                $.post(url, formData, function(response) {
                    if (response.success) {
                        $('#eventModal').modal('hide');
                        calendar.refetchEvents();
                    } else {
                        alert(response.message || 'Error saving event');
                    }
                }, 'json').fail(function() {
                    alert('Error saving event');
                });
            });

            deleteBtn.on('click', function() {
                if (!confirm('Delete this event?')) return;

                $.post('<?= site_url("calendar/delete_event") ?>', {
                    event_id: $('#eventId').val()
                }, function(response) {
                    if (response && response.success === false) {
                        alert(response.message || 'Error deleting event');
                        return;
                    }
                    $('#eventModal').modal('hide');
                    calendar.refetchEvents();
                }, 'json').fail(function() {
                    alert('Error deleting event');
                });
            });

            $('#eventModal').on('hidden.bs.modal', resetForm);
        });
    </script>
</body>
</html>
