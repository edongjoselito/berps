<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * BERPS Mobile Staff endpoints.
 */
class MobileStaff extends CI_Controller
{
    private const PRIORITY_LABELS = [
        '1' => 'High',
        '2' => 'Medium',
        '3' => 'Low',
    ];

    public function __construct()
    {
        parent::__construct();
        $this->load->helper('mobile_auth');
        mobile_register_error_handlers();
        $this->load->model('CashModel');
        $this->load->model('RemindersModel');
        mobile_send_cors();
    }

    public function dashboard()
    {
        if ($this->_method() === 'OPTIONS') {
            return mobile_json(['ok' => true]);
        }
        if ($this->_method() !== 'GET') {
            return mobile_json(['ok' => false, 'message' => 'Method not allowed.'], 405);
        }

        $claims = $this->_require_staff_claims();
        if ($claims === null) {
            return;
        }

        date_default_timezone_set('Asia/Manila');

        $userId     = (int) ($claims['user_id'] ?? 0);
        $settingsID = (int) ($claims['settingsID'] ?? 0);
        $username   = (string) ($claims['username'] ?? '');
        $today      = date('Y-m-d');

        $timeNotice = '';
        $dueTodayCount = 0;
        $todayAttendance = [
            'accomplishment_count' => 0,
            'total_seconds' => 0,
        ];
        $ongoing = [];
        $taskStats = [
            'due_today' => 0,
            'due_soon' => 0,
            'overdue' => 0,
            'without_due_date' => 0,
            'due_window_days' => 7,
            'forwarded_count' => 0,
        ];
        $supportUnassignedCount = 0;

        try {
            $dueToday = $this->RemindersModel->getDueToday($settingsID, $userId);
            $dueTodayCount = is_array($dueToday) ? count($dueToday) : 0;
        } catch (Throwable $e) {
            log_message('error', '[mobile_dashboard] reminders failed: ' . $e->getMessage());
        }

        try {
            $timeNotice = $this->_attendance_notice($username, $today);
        } catch (Throwable $e) {
            log_message('error', '[mobile_dashboard] attendance notice failed: ' . $e->getMessage());
        }

        try {
            $todayAttendance = $this->_aggregateAttendanceRows(
                $this->CashModel->attendanceListByEmployeeRange($settingsID, $username, $today, $today),
                $settingsID
            );
        } catch (Throwable $e) {
            log_message('error', '[mobile_dashboard] attendance aggregate failed: ' . $e->getMessage());
        }

        try {
            $queue = $this->CashModel->openTaskDueQueue($settingsID, $userId, 4, 7, $today);
        } catch (Throwable $e) {
            $queue = [];
            log_message('error', '[mobile_dashboard] task queue failed: ' . $e->getMessage());
        }

        foreach ((array) $queue as $row) {
            $reported = (string) ($row->reportedDate ?? '');
            $due      = (string) ($row->dueDate ?? '');
            $ongoing[] = [
                'id'           => (int) ($row->taskID ?? 0),
                'title'        => (string) ($row->task ?? 'Untitled task'),
                'subtitle'     => (string) ($row->projectDescription ?? ''),
                'reported_date' => $reported,
                'due_date'      => $due,
                'priority'      => (string) ($row->priority ?? ''),
                'progress'      => $this->_progressBetween($reported, $due, $today),
            ];
        }

        try {
            $taskStats = [
                'due_today'        => $this->CashModel->countOpenTasksDueToday($settingsID, $userId, $today),
                'due_soon'         => $this->CashModel->countOpenTasksDueSoon($settingsID, 7, $userId, $today),
                'overdue'          => $this->CashModel->countOpenTasksOverdue($settingsID, $userId, $today),
                'without_due_date' => $this->CashModel->countOpenTasksWithoutDueDate($settingsID, $userId),
                'due_window_days'  => 7,
                'forwarded_count'  => count($this->_staff_pending_forwarded_task_ids($settingsID, $userId, $username)),
            ];
        } catch (Throwable $e) {
            log_message('error', '[mobile_dashboard] task stats failed: ' . $e->getMessage());
        }

        try {
            $supportUnassignedCount = $this->_count_support_issues(
                $settingsID,
                $userId,
                'unassigned',
                $this->_mobile_accessible_issue_ids($settingsID, $userId)
            );
        } catch (Throwable $e) {
            log_message('error', '[mobile_dashboard] support summary failed: ' . $e->getMessage());
        }

        return mobile_json([
            'ok'    => true,
            'today' => $today,
            'tasks' => $taskStats,
            'ongoing_tasks' => $ongoing,
            'reminders' => [
                'due_today_count' => $dueTodayCount,
            ],
            'accomplishments' => [
                'today_count' => $todayAttendance['accomplishment_count'],
                'today_hours_label' => $this->_formatSeconds($todayAttendance['total_seconds']),
            ],
            'attendance' => [
                'notice'       => $timeNotice,
                'status_label' => $timeNotice !== ''
                    ? $timeNotice
                    : 'Your attendance record looks complete for today.',
                'can_time_in'  => !$this->_has_open_time_in($username, $today),
                'can_time_out' => $this->_has_open_time_in($username, $today),
            ],
            'support' => [
                'unassigned_count' => $supportUnassignedCount,
            ],
        ]);
    }

    public function attendance()
    {
        if ($this->_method() === 'OPTIONS') {
            return mobile_json(['ok' => true]);
        }
        if ($this->_method() !== 'GET') {
            return mobile_json(['ok' => false, 'message' => 'Method not allowed.'], 405);
        }

        $claims = $this->_require_staff_claims();
        if ($claims === null) {
            return;
        }

        date_default_timezone_set('Asia/Manila');

        $settingsID = (int) ($claims['settingsID'] ?? 0);
        $username   = (string) ($claims['username'] ?? '');
        $today      = date('Y-m-d');

        $date = trim((string) $this->input->get('date'));
        $from = trim((string) $this->input->get('from'));
        $to = trim((string) $this->input->get('to'));

        if ($date !== '') {
            $from = $date;
            $to = $date;
        }

        if ($from === '' && $to === '') {
            $from = $today;
            $to = $today;
        } else {
            if ($from === '') {
                $from = $to;
            }
            if ($to === '') {
                $to = $from;
            }
        }

        if (!$this->_is_valid_date($from) || !$this->_is_valid_date($to)) {
            return mobile_json(['ok' => false, 'message' => 'Invalid attendance date range.'], 422);
        }

        if (strtotime($from) > strtotime($to)) {
            $swap = $from;
            $from = $to;
            $to = $swap;
        }

        $rows = $this->CashModel->attendanceListByEmployeeRange($settingsID, $username, $from, $to);
        $attendance = $this->_aggregateAttendanceRows($rows, $settingsID);
        $todaySnapshot = $this->_today_attendance_snapshot($username, $today);

        return mobile_json([
            'ok' => true,
            'today' => $today,
            'range' => [
                'from' => $from,
                'to' => $to,
            ],
            'summary' => [
                'present_days' => $attendance['present_days'],
                'pending_days' => $attendance['pending_days'],
                'absent_days' => $attendance['absent_days'],
                'accomplishment_count' => $attendance['accomplishment_count'],
                'total_seconds' => $attendance['total_seconds'],
                'total_hours_label' => $this->_formatSeconds($attendance['total_seconds']),
            ],
            'attendance' => [
                'notice' => $this->_attendance_notice($username, $today),
                'status_label' => $this->_attendance_status_label($username, $today),
                'can_time_in' => !$this->_has_open_time_in($username, $today),
                'can_time_out' => $this->_has_open_time_in($username, $today),
                'latest_time_in_label' => $todaySnapshot['latest_time_in_label'],
                'latest_time_out_label' => $todaySnapshot['latest_time_out_label'],
                'open_slot_label' => $todaySnapshot['open_slot_label'],
                'has_record_today' => $todaySnapshot['has_record_today'],
            ],
            'records' => $attendance['records'],
        ]);
    }

    public function attendanceTimeIn()
    {
        if ($this->_method() === 'OPTIONS') {
            return mobile_json(['ok' => true]);
        }
        if ($this->_method() !== 'POST') {
            return mobile_json(['ok' => false, 'message' => 'Method not allowed.'], 405);
        }

        $claims = $this->_require_staff_claims();
        if ($claims === null) {
            return;
        }

        date_default_timezone_set('Asia/Manila');

        $settingsID = (int) ($claims['settingsID'] ?? 0);
        $username = (string) ($claims['username'] ?? '');
        $logDate = date('Y-m-d');
        $now = date('H:i:s A');
        $slot = $this->_detect_time_slot();

        if ($this->_has_open_time_in($username, $logDate)) {
            return mobile_json([
                'ok' => false,
                'message' => 'Please time out first.',
            ], 409);
        }

        $latest = $this->db
            ->query(
                "select * from dtr where logDate=? and IDNumber=? order by dtrID desc limit 1",
                [$logDate, $username]
            )
            ->row();

        if ($slot === 'pm') {
            if ($latest && empty($latest->pmTimeIn)) {
                $this->db->query(
                    "update dtr set pmTimeIn=?, pmTimeInStat='Closed', pmTimeOutStat='Open' where dtrID=?",
                    [$now, $latest->dtrID]
                );
            } else {
                $this->db->query(
                    "insert into dtr values('0', ?, '', '', ?, '', ?, 'Open', 'Open', 'Closed', 'Open', ?)",
                    [$logDate, $now, $username, $settingsID]
                );
            }
            $slotLabel = 'PM';
        } else {
            if ($latest && empty($latest->amTimeIn)) {
                $this->db->query(
                    "update dtr set amTimeIn=?, amTimeInStat='Closed', amTimeOutStat='Open' where dtrID=?",
                    [$now, $latest->dtrID]
                );
            } else {
                $this->db->query(
                    "insert into dtr values('0', ?, ?, '', '', '', ?, 'Closed', 'Open', 'Open', 'Open', ?)",
                    [$logDate, $now, $username, $settingsID]
                );
            }
            $slotLabel = 'AM';
        }

        return mobile_json([
            'ok' => true,
            'message' => 'Time-in recorded (auto: ' . $slotLabel . ').',
            'slot' => strtolower($slotLabel),
        ]);
    }

    public function attendanceTimeOut()
    {
        if ($this->_method() === 'OPTIONS') {
            return mobile_json(['ok' => true]);
        }
        if ($this->_method() !== 'POST') {
            return mobile_json(['ok' => false, 'message' => 'Method not allowed.'], 405);
        }

        $claims = $this->_require_staff_claims();
        if ($claims === null) {
            return;
        }

        date_default_timezone_set('Asia/Manila');

        $username = (string) ($claims['username'] ?? '');
        $logDate = date('Y-m-d');
        $now = date('H:i:s A');
        $slot = $this->_detect_time_slot();

        $openAm = $this->db->query(
            "select * from dtr where logDate=? and IDNumber=? and amTimeIn!='' and (amTimeOut='' or amTimeOut is null) order by dtrID desc limit 1",
            [$logDate, $username]
        )->row();
        $openPm = $this->db->query(
            "select * from dtr where logDate=? and IDNumber=? and pmTimeIn!='' and (pmTimeOut='' or pmTimeOut is null) order by dtrID desc limit 1",
            [$logDate, $username]
        )->row();

        $targetSlot = '';
        $targetRow = null;

        if ($slot === 'pm') {
            if ($openPm) {
                $targetSlot = 'pm';
                $targetRow = $openPm;
            } elseif ($openAm) {
                $targetSlot = 'am';
                $targetRow = $openAm;
            }
        } else {
            if ($openAm) {
                $targetSlot = 'am';
                $targetRow = $openAm;
            } elseif ($openPm) {
                $targetSlot = 'pm';
                $targetRow = $openPm;
            }
        }

        if ($targetSlot === 'am') {
            $this->db->query(
                "update dtr set amTimeOut=?, amTimeOutStat='Closed' where dtrID=?",
                [$now, $targetRow->dtrID]
            );
            return mobile_json([
                'ok' => true,
                'message' => 'Time-out recorded for AM slot.',
                'slot' => 'am',
            ]);
        }

        if ($targetSlot === 'pm') {
            $this->db->query(
                "update dtr set pmTimeOut=?, pmTimeOutStat='Closed' where dtrID=?",
                [$now, $targetRow->dtrID]
            );
            return mobile_json([
                'ok' => true,
                'message' => 'Time-out recorded for PM slot.',
                'slot' => 'pm',
            ]);
        }

        return mobile_json([
            'ok' => false,
            'message' => 'No open time-in found.',
        ], 409);
    }

    public function tasks()
    {
        if ($this->_method() === 'OPTIONS') {
            return mobile_json(['ok' => true]);
        }
        if ($this->_method() !== 'GET') {
            return mobile_json(['ok' => false, 'message' => 'Method not allowed.'], 405);
        }

        $claims = $this->_require_staff_claims();
        if ($claims === null) {
            return;
        }

        date_default_timezone_set('Asia/Manila');

        $settingsID = (int) ($claims['settingsID'] ?? 0);
        $userId = (int) ($claims['user_id'] ?? 0);
        $username = trim((string) ($claims['username'] ?? ''));
        $today = date('Y-m-d');
        $statusFilter = $this->_normalize_status_filter((string) $this->input->get('status'));
        $taskScope = $this->_normalize_task_scope((string) $this->input->get('scope'));

        $taskData = $this->CashModel->taskListStaff($settingsID, $userId);
        $pendingForwardedIds = $this->_staff_pending_forwarded_task_ids($settingsID, $userId, $username);

        if ($taskScope === 'forwarded') {
            $taskData = array_values(array_filter((array) $taskData, function ($task) use ($pendingForwardedIds) {
                return in_array((int) ($task->taskID ?? 0), $pendingForwardedIds, true);
            }));
        }

        $filtered = array_values(array_filter((array) $taskData, function ($task) use ($statusFilter) {
            $taskStat = (string) ($task->taskStat ?? '1');
            if ($statusFilter === 'open') {
                return $taskStat === '1';
            }
            if ($statusFilter === 'closed') {
                return $taskStat === '0';
            }
            return true;
        }));

        $tasks = [];
        foreach ($filtered as $row) {
            $tasks[] = $this->_task_payload_from_row($row, $today, $pendingForwardedIds);
        }

        return mobile_json([
            'ok' => true,
            'today' => $today,
            'status_filter' => $statusFilter,
            'task_scope' => $taskScope,
            'has_time_in_today' => $this->CashModel->hasTimeInToday($settingsID, $username, $today),
            'stats' => [
                'open' => $this->CashModel->countOpenTasksStaff($settingsID, $userId),
                'closed' => $this->CashModel->countClosedTasksStaff($settingsID, $userId),
                'due_today' => $this->CashModel->countOpenTasksDueToday($settingsID, $userId, $today),
                'due_soon' => $this->CashModel->countOpenTasksDueSoon($settingsID, 7, $userId, $today),
                'overdue' => $this->CashModel->countOpenTasksOverdue($settingsID, $userId, $today),
                'undated' => $this->CashModel->countOpenTasksWithoutDueDate($settingsID, $userId),
                'forwarded' => count($pendingForwardedIds),
            ],
            'projects' => $this->_project_options_payload($this->CashModel->getProjectName($settingsID)),
            'staff_options' => $this->_staff_options_payload($this->CashModel->employeeList($settingsID), $userId),
            'priority_options' => [
                ['value' => '1', 'label' => 'High'],
                ['value' => '2', 'label' => 'Medium'],
                ['value' => '3', 'label' => 'Low'],
            ],
            'tasks' => $tasks,
        ]);
    }

    public function taskDetail($taskId)
    {
        if ($this->_method() === 'OPTIONS') {
            return mobile_json(['ok' => true]);
        }
        if ($this->_method() !== 'GET') {
            return mobile_json(['ok' => false, 'message' => 'Method not allowed.'], 405);
        }

        $claims = $this->_require_staff_claims();
        if ($claims === null) {
            return;
        }

        date_default_timezone_set('Asia/Manila');

        $settingsID = (int) ($claims['settingsID'] ?? 0);
        $userId = (int) ($claims['user_id'] ?? 0);
        $username = trim((string) ($claims['username'] ?? ''));
        $today = date('Y-m-d');

        $task = $this->_fetch_task_row($settingsID, (int) $taskId, $userId);
        if (!$task) {
            return mobile_json(['ok' => false, 'message' => 'Task not found.'], 404);
        }

        $historyRows = $this->db
            ->select('ptsID, note, datePosted, postedBy, taskStat, points')
            ->from('projects_task_stat')
            ->where('taskID', (int) $taskId)
            ->order_by('datePosted', 'DESC')
            ->order_by('ptsID', 'DESC')
            ->get()
            ->result();

        $checklistRows = $this->db
            ->select('checklistID, itemDescription, status, isCompleted')
            ->from('task_checklist')
            ->where('taskID', (int) $taskId)
            ->where('settingsID', $settingsID)
            ->order_by('checklistID', 'ASC')
            ->get()
            ->result();

        return mobile_json([
            'ok' => true,
            'task' => $this->_task_payload_from_row($task, $today, $this->_staff_pending_forwarded_task_ids($settingsID, $userId, $username)),
            'checklist' => array_map(function ($row) {
                return [
                    'id' => (int) ($row->checklistID ?? 0),
                    'item_description' => (string) ($row->itemDescription ?? ''),
                    'status' => (string) (($row->status ?? 'Pending') ?: 'Pending'),
                    'is_completed' => (int) ($row->isCompleted ?? 0) === 1,
                ];
            }, (array) $checklistRows),
            'history' => array_map(function ($row) {
                return [
                    'id' => (int) ($row->ptsID ?? 0),
                    'note' => (string) ($row->note ?? ''),
                    'posted_at' => (string) ($row->datePosted ?? ''),
                    'posted_by' => (string) ($row->postedBy ?? ''),
                    'task_status' => (string) ($row->taskStat ?? ''),
                    'points' => isset($row->points) ? (int) $row->points : 0,
                ];
            }, (array) $historyRows),
        ]);
    }

    public function createTask()
    {
        if ($this->_method() === 'OPTIONS') {
            return mobile_json(['ok' => true]);
        }
        if ($this->_method() !== 'POST') {
            return mobile_json(['ok' => false, 'message' => 'Method not allowed.'], 405);
        }

        $claims = $this->_require_staff_claims();
        if ($claims === null) {
            return;
        }

        date_default_timezone_set('Asia/Manila');

        $payload = $this->_read_payload();
        $settingsID = (int) ($claims['settingsID'] ?? 0);
        $userId = (int) ($claims['user_id'] ?? 0);
        $username = trim((string) ($claims['username'] ?? ''));
        $today = date('Y-m-d');

        if (!$this->CashModel->hasTimeInToday($settingsID, $username, $today)) {
            return mobile_json(['ok' => false, 'message' => 'You need to attend first before you can add a task.'], 409);
        }

        $task = $this->_normalize_task_label($payload['task'] ?? '');
        $reportedDate = $this->_normalize_date_input($payload['reported_date'] ?? $payload['reportedDate'] ?? '') ?: $today;
        $dueDate = $this->_normalize_date_input($payload['due_date'] ?? $payload['dueDate'] ?? '') ?: $reportedDate;
        $projectId = (int) ($payload['project_id'] ?? $payload['project'] ?? 0);
        $priority = $this->_normalize_priority($payload['priority'] ?? '2');
        $attachmentLink = trim((string) ($payload['attachment_link'] ?? ''));
        $attachmentLink = $attachmentLink !== '' ? $attachmentLink : null;
        $checklistItems = $payload['checklist_items'] ?? [];
        $points = max(1, (int) ($payload['points'] ?? 1));

        if ($task === '') {
            return mobile_json(['ok' => false, 'message' => 'Task name is required.'], 422);
        }
        if ($projectId <= 0) {
            return mobile_json(['ok' => false, 'message' => 'Project is required.'], 422);
        }

        $project = $this->db
            ->select('projectID')
            ->from('projects')
            ->where('settingsID', $settingsID)
            ->where('projectID', $projectId)
            ->limit(1)
            ->get()
            ->row();
        if (!$project) {
            return mobile_json(['ok' => false, 'message' => 'Selected project was not found.'], 404);
        }

        $taskInsert = [
            'taskID' => 0,
            'task' => $task,
            'reportedDate' => $reportedDate,
            'dueDate' => $dueDate,
            'projectID' => $projectId,
            'taskStat' => '1',
            'priority' => $priority,
            'settingsID' => $settingsID,
            'assignedPerson' => $userId,
            'attachment_link' => $attachmentLink,
            'added_by' => $username,
        ];
        if ($this->db->field_exists('points', 'projects_task')) {
            $taskInsert['points'] = $points;
        }

        $this->db->insert('projects_task', $taskInsert);
        $taskId = (int) $this->db->insert_id();
        if ($taskId <= 0) {
            return mobile_json(['ok' => false, 'message' => 'Unable to create task.'], 500);
        }

        $this->_replace_task_checklist($taskId, $settingsID, $checklistItems, $username);

        return mobile_json([
            'ok' => true,
            'message' => 'New task has been successfully added.',
            'task_id' => $taskId,
        ]);
    }

    public function updateTask($taskId)
    {
        if ($this->_method() === 'OPTIONS') {
            return mobile_json(['ok' => true]);
        }
        if ($this->_method() !== 'POST') {
            return mobile_json(['ok' => false, 'message' => 'Method not allowed.'], 405);
        }

        $claims = $this->_require_staff_claims();
        if ($claims === null) {
            return;
        }

        date_default_timezone_set('Asia/Manila');

        $payload = $this->_read_payload();
        $settingsID = (int) ($claims['settingsID'] ?? 0);
        $userId = (int) ($claims['user_id'] ?? 0);
        $username = trim((string) ($claims['username'] ?? ''));
        $today = date('Y-m-d');

        if (!$this->CashModel->hasTimeInToday($settingsID, $username, $today)) {
            return mobile_json(['ok' => false, 'message' => 'You need to attend first before you can update task.'], 409);
        }

        $taskRow = $this->_fetch_task_row($settingsID, (int) $taskId, $userId);
        if (!$taskRow) {
            return mobile_json(['ok' => false, 'message' => 'Task not found.'], 404);
        }

        $task = $this->_normalize_task_label($payload['task'] ?? '');
        $projectId = (int) ($payload['project_id'] ?? $payload['project'] ?? 0);
        $reportedDate = $this->_normalize_date_input($payload['reported_date'] ?? $payload['reportedDate'] ?? '') ?: date('Y-m-d');
        $dueDate = $this->_normalize_date_input($payload['due_date'] ?? $payload['dueDate'] ?? '') ?: $reportedDate;
        $priority = $this->_normalize_priority($payload['priority'] ?? '2');
        $attachmentLink = trim((string) ($payload['attachment_link'] ?? ''));
        $attachmentLink = $attachmentLink !== '' ? $attachmentLink : null;

        if ($task === '' || $projectId <= 0) {
            return mobile_json(['ok' => false, 'message' => 'Task and project are required.'], 422);
        }

        $this->db
            ->where('taskID', (int) $taskId)
            ->where('settingsID', $settingsID)
            ->update('projects_task', [
                'task' => $task,
                'projectID' => $projectId,
                'priority' => $priority,
                'reportedDate' => $reportedDate,
                'dueDate' => $dueDate,
                'attachment_link' => $attachmentLink,
            ]);

        $this->_mark_forwarded_task_action((int) $taskId, $settingsID, $userId, $username, 'Forwarded task updated.');

        return mobile_json([
            'ok' => true,
            'message' => 'Task updated successfully.',
        ]);
    }

    public function saveTaskChecklist($taskId)
    {
        if ($this->_method() === 'OPTIONS') {
            return mobile_json(['ok' => true]);
        }
        if ($this->_method() !== 'POST') {
            return mobile_json(['ok' => false, 'message' => 'Method not allowed.'], 405);
        }

        $claims = $this->_require_staff_claims();
        if ($claims === null) {
            return;
        }

        $payload = $this->_read_payload();
        $settingsID = (int) ($claims['settingsID'] ?? 0);
        $userId = (int) ($claims['user_id'] ?? 0);
        $username = trim((string) ($claims['username'] ?? ''));
        $taskRow = $this->_fetch_task_row($settingsID, (int) $taskId, $userId);

        if (!$taskRow) {
            return mobile_json(['ok' => false, 'message' => 'Task not found.'], 404);
        }

        $items = $payload['checklist_items'] ?? [];
        if (!is_array($items)) {
            return mobile_json(['ok' => false, 'message' => 'Checklist items must be an array.'], 422);
        }

        $this->_replace_task_checklist((int) $taskId, $settingsID, $items, $username);

        return mobile_json([
            'ok' => true,
            'message' => 'Checklist saved successfully.',
        ]);
    }

    public function updateTaskStatus($taskId)
    {
        if ($this->_method() === 'OPTIONS') {
            return mobile_json(['ok' => true]);
        }
        if ($this->_method() !== 'POST') {
            return mobile_json(['ok' => false, 'message' => 'Method not allowed.'], 405);
        }

        $claims = $this->_require_staff_claims();
        if ($claims === null) {
            return;
        }

        date_default_timezone_set('Asia/Manila');

        $payload = $this->_read_payload();
        $settingsID = (int) ($claims['settingsID'] ?? 0);
        $userId = (int) ($claims['user_id'] ?? 0);
        $username = trim((string) ($claims['username'] ?? ''));
        $today = date('Y-m-d');
        $taskStat = ((string) ($payload['task_status'] ?? $payload['taskStat'] ?? '0')) === '0' ? '0' : '1';
        $note = trim((string) ($payload['note'] ?? ''));

        if (!$this->CashModel->hasTimeInToday($settingsID, $username, $today)) {
            return mobile_json(['ok' => false, 'message' => 'You need to time in first before you can update task status.'], 409);
        }

        $taskRow = $this->_fetch_task_row($settingsID, (int) $taskId, $userId);
        if (!$taskRow) {
            return mobile_json(['ok' => false, 'message' => 'Task not found.'], 404);
        }

        $date = date('Y-m-d H:i:s');

        $closeStatRow = [
            'taskID' => (int) $taskId,
            'note' => $note,
            'datePosted' => $date,
            'postedBy' => $username,
            'taskStat' => $taskStat,
        ];

        if (
            $taskStat === '0'
            && $this->db->field_exists('points', 'projects_task_stat')
            && $this->db->field_exists('points', 'projects_task')
        ) {
            $taskPointsRow = $this->db
                ->select('points')
                ->from('projects_task')
                ->where('taskID', (int) $taskId)
                ->limit(1)
                ->get()
                ->row();
            if ($taskPointsRow && isset($taskPointsRow->points) && is_numeric($taskPointsRow->points)) {
                $closeStatRow['points'] = (int) $taskPointsRow->points;
            }
        }

        $this->db->insert('projects_task_stat', $closeStatRow);

        $this->db
            ->where('taskID', (int) $taskId)
            ->where('settingsID', $settingsID)
            ->update('projects_task', [
                'taskStat' => $taskStat,
                'completed_by' => $taskStat === '0' ? $userId : null,
            ]);

        if ($taskStat === '0') {
            $this->_apply_forwarded_task_completion_logic((int) $taskId, $settingsID, $userId, $username, $date);
            $this->_apply_checklist_completion_points((int) $taskId, $username, $date);
        }

        return mobile_json([
            'ok' => true,
            'message' => 'Task updated successfully.',
        ]);
    }

    public function forwardTask($taskId)
    {
        if ($this->_method() === 'OPTIONS') {
            return mobile_json(['ok' => true]);
        }
        if ($this->_method() !== 'POST') {
            return mobile_json(['ok' => false, 'message' => 'Method not allowed.'], 405);
        }

        $claims = $this->_require_staff_claims();
        if ($claims === null) {
            return;
        }

        date_default_timezone_set('Asia/Manila');

        $payload = $this->_read_payload();
        $settingsID = (int) ($claims['settingsID'] ?? 0);
        $userId = (int) ($claims['user_id'] ?? 0);
        $username = trim((string) ($claims['username'] ?? ''));
        $today = date('Y-m-d');

        if (!$this->CashModel->hasTimeInToday($settingsID, $username, $today)) {
            return mobile_json(['ok' => false, 'message' => 'You need to attend first before you can forward a task.'], 409);
        }

        $taskRow = $this->_fetch_task_row($settingsID, (int) $taskId, $userId);
        if (!$taskRow) {
            return mobile_json(['ok' => false, 'message' => 'Task not found.'], 404);
        }

        $forwardTo = (int) ($payload['forward_to'] ?? $payload['forwardTo'] ?? 0);
        $forwardNote = trim((string) ($payload['forward_note'] ?? $payload['forwardNote'] ?? ''));

        if ($forwardTo <= 0) {
            return mobile_json(['ok' => false, 'message' => 'Please select an employee to forward the task to.'], 422);
        }

        $requiredColumns = ['forwarded_from', 'forwarded_to', 'forwarded_by', 'forwarded_note', 'forwarded_date', 'completed_by_forward'];
        $existingColumns = $this->db->list_fields('projects_task');
        $missingColumns = array_diff($requiredColumns, $existingColumns);
        if (!empty($missingColumns)) {
            return mobile_json([
                'ok' => false,
                'message' => 'Database error: Missing columns - ' . implode(', ', $missingColumns) . '. Please run the migration SQL file.',
            ], 500);
        }

        $originalAssignee = $this->db
            ->select('fName, lName, user_id')
            ->from('users')
            ->where('user_id', (int) ($taskRow->assignedPerson ?? 0))
            ->limit(1)
            ->get()
            ->row();
        $newAssignee = $this->db
            ->select('fName, lName, user_id, username')
            ->from('users')
            ->where('settingsID', $settingsID)
            ->where('user_id', $forwardTo)
            ->limit(1)
            ->get()
            ->row();

        if (!$newAssignee) {
            return mobile_json(['ok' => false, 'message' => 'Selected employee not found.'], 404);
        }

        $date = date('Y-m-d H:i:s');
        $originalAssigneeName = $originalAssignee
            ? trim((string) $originalAssignee->fName . ' ' . (string) $originalAssignee->lName)
            : 'Unknown';
        $newAssigneeName = trim((string) $newAssignee->fName . ' ' . (string) $newAssignee->lName);

        $this->db->insert('projects_task', [
            'taskID' => 0,
            'task' => $this->_normalize_task_label(((string) ($taskRow->task ?? '')) . ' [Forwarded from: ' . $originalAssigneeName . ']'),
            'reportedDate' => (string) ($taskRow->reportedDate ?? ''),
            'dueDate' => trim((string) ($taskRow->dueDate ?? '')) !== '' ? (string) $taskRow->dueDate : null,
            'projectID' => (int) ($taskRow->projectID ?? 0),
            'taskStat' => '1',
            'priority' => (string) ($taskRow->priority ?? '2'),
            'settingsID' => $settingsID,
            'assignedPerson' => $forwardTo,
            'attachment_link' => trim((string) ($taskRow->attachment_link ?? '')) !== '' ? (string) $taskRow->attachment_link : null,
            'added_by' => $username,
            'forwarded_from' => (int) $taskId,
            'forwarded_to' => $forwardTo,
            'forwarded_by' => $userId,
            'forwarded_note' => $forwardNote,
            'forwarded_date' => $date,
        ]);

        $forwardedTaskId = (int) $this->db->insert_id();
        if ($forwardedTaskId <= 0) {
            return mobile_json(['ok' => false, 'message' => 'Failed to create forwarded task.'], 500);
        }

        $this->db->insert('projects_task_stat', [
            'taskID' => $forwardedTaskId,
            'note' => 'Task forwarded from ' . $originalAssigneeName . '. Note: ' . $forwardNote,
            'datePosted' => $date,
            'postedBy' => $username,
            'taskStat' => '1',
        ]);

        $this->db->insert('projects_task_stat', [
            'taskID' => (int) $taskId,
            'note' => 'Task forwarded to ' . $newAssigneeName . '. Both can work on it - whoever completes first gets the points. Note: ' . $forwardNote,
            'datePosted' => $date,
            'postedBy' => $username,
            'taskStat' => '1',
        ]);

        return mobile_json([
            'ok' => true,
            'message' => 'Task forwarded successfully to ' . $newAssigneeName . '.',
            'task_id' => $forwardedTaskId,
        ]);
    }

    public function ranking()
    {
        if ($this->_method() === 'OPTIONS') {
            return mobile_json(['ok' => true]);
        }
        if ($this->_method() !== 'GET') {
            return mobile_json(['ok' => false, 'message' => 'Method not allowed.'], 405);
        }

        $claims = $this->_require_staff_claims();
        if ($claims === null) {
            return;
        }

        date_default_timezone_set('Asia/Manila');

        $settingsID = (int) ($claims['settingsID'] ?? 0);
        $username   = (string) ($claims['username'] ?? '');

        $yearInput  = trim((string) $this->input->get('year'));
        $monthInput = trim((string) $this->input->get('month'));

        $year = null;
        $month = null;
        if ($yearInput !== '' && strtolower($yearInput) !== 'all') {
            $year = (int) $yearInput;
        } else {
            $year = (int) date('Y');
        }
        if ($monthInput !== '' && strtolower($monthInput) !== 'all') {
            $month = (int) $monthInput;
        } else {
            $month = (int) date('n');
        }

        $rows = $this->CashModel->getTaskRanking($settingsID, $year, $month);

        $entries = [];
        $totalPoints = 0;
        $rank = 1;
        $currentRank = null;
        $currentPoints = null;

        foreach ((array) $rows as $row) {
            $points = (int) ($row->accomplished_count ?? 0);
            $totalPoints += $points;

            $userId = (int) ($row->user_id ?? 0);
            $name = trim((string) ($row->name ?? ''));
            $role = trim((string) ($row->role ?? ''));
            $last = (string) ($row->last_accomplished ?? '');

            $isCurrent = false;
            // Match by username via users table — we already have user_id, compare with logged in
            if ($userId > 0 && (int) ($claims['user_id'] ?? 0) === $userId) {
                $isCurrent = true;
                $currentRank = $rank;
                $currentPoints = $points;
            }

            $entries[] = [
                'rank'          => $rank,
                'user_id'       => $userId,
                'name'          => $name,
                'role'          => $role,
                'points'        => $points,
                'last_done'     => $last,
                'last_done_label' => $last !== '' ? date('M d, Y', strtotime($last)) : '',
                'is_current'    => $isCurrent,
            ];

            $rank++;
        }

        $top = array_slice($entries, 0, 3);

        $monthNames = [
            1 => 'January',
            2 => 'February',
            3 => 'March',
            4 => 'April',
            5 => 'May',
            6 => 'June',
            7 => 'July',
            8 => 'August',
            9 => 'September',
            10 => 'October',
            11 => 'November',
            12 => 'December',
        ];

        return mobile_json([
            'ok'             => true,
            'period'         => [
                'year'  => $year,
                'month' => $month,
                'label' => isset($monthNames[$month]) ? ($monthNames[$month] . ' ' . $year) : (string) $year,
            ],
            'total_employees' => count($entries),
            'total_points'   => $totalPoints,
            'current'        => [
                'rank'   => $currentRank,
                'points' => $currentPoints ?? 0,
            ],
            'top'            => $top,
            'entries'        => $entries,
        ]);
    }

    public function deleteTask($taskId)
    {
        if ($this->_method() === 'OPTIONS') {
            return mobile_json(['ok' => true]);
        }
        if ($this->_method() !== 'POST' && $this->_method() !== 'DELETE') {
            return mobile_json(['ok' => false, 'message' => 'Method not allowed.'], 405);
        }

        $claims = $this->_require_staff_claims();
        if ($claims === null) {
            return;
        }

        $settingsID = (int) ($claims['settingsID'] ?? 0);
        $username   = trim((string) ($claims['username'] ?? ''));

        $task = $this->db
            ->where('taskID', (int) $taskId)
            ->where('settingsID', $settingsID)
            ->get('projects_task')
            ->row();

        if (!$task) {
            return mobile_json(['ok' => false, 'message' => 'Task not found.'], 404);
        }

        $taskCreator = trim((string) ($task->added_by ?? ''));
        if ($taskCreator === '' || $taskCreator !== $username) {
            return mobile_json([
                'ok'      => false,
                'message' => 'You can only delete tasks that you created.',
            ], 403);
        }

        $this->db->where('taskID', (int) $taskId);
        $this->db->where('settingsID', $settingsID);
        $this->db->delete('projects_task');

        return mobile_json([
            'ok'      => true,
            'message' => 'Task deleted successfully.',
        ]);
    }

    // ── Support tickets ────────────────────────────────────────────────────

    public function supportIssues()
    {
        if ($this->_method() === 'OPTIONS') {
            return mobile_json(['ok' => true]);
        }
        if ($this->_method() !== 'GET') {
            return mobile_json(['ok' => false, 'message' => 'Method not allowed.'], 405);
        }

        $claims = $this->_require_staff_claims();
        if ($claims === null) {
            return;
        }

        $settingsID = (int) ($claims['settingsID'] ?? 0);
        $userId     = (int) ($claims['user_id'] ?? 0);

        $scope = strtolower(trim((string) $this->input->get('scope')));
        if (!in_array($scope, ['unassigned', 'open', 'closed', 'all'], true)) {
            $scope = 'open';
        }

        $accessible = $this->_mobile_accessible_issue_ids($settingsID, $userId);

        $counts = [
            'open'       => $this->_count_support_issues($settingsID, $userId, 'open', $accessible),
            'unassigned' => $this->_count_support_issues($settingsID, $userId, 'unassigned', $accessible),
            'closed'     => $this->_count_support_issues($settingsID, $userId, 'closed', $accessible),
            'all'        => $this->_count_support_issues($settingsID, $userId, 'all', $accessible),
        ];

        $issues = $this->_query_support_issues($settingsID, $userId, $scope, $accessible);
        $payload = [];
        foreach ($issues as $row) {
            $payload[] = $this->_support_issue_payload($row, $userId);
        }

        return mobile_json([
            'ok'     => true,
            'scope'  => $scope,
            'counts' => $counts,
            'issues' => $payload,
        ]);
    }

    public function supportIssueView($issueId)
    {
        if ($this->_method() === 'OPTIONS') {
            return mobile_json(['ok' => true]);
        }
        if ($this->_method() !== 'GET') {
            return mobile_json(['ok' => false, 'message' => 'Method not allowed.'], 405);
        }

        $claims = $this->_require_staff_claims();
        if ($claims === null) {
            return;
        }

        $settingsID = (int) ($claims['settingsID'] ?? 0);
        $userId     = (int) ($claims['user_id'] ?? 0);

        $issue = $this->_mobile_support_issue_for_user((int) $issueId, $settingsID, $userId);
        if (!$issue) {
            return mobile_json(['ok' => false, 'message' => 'Support ticket not found.'], 404);
        }

        $perms = $this->_mobile_support_chat_permissions($settingsID, $userId);

        // Mark notifications for this issue as read
        if ($this->db->table_exists('support_notifications')) {
            date_default_timezone_set('Asia/Manila');
            $now = date('Y-m-d H:i:s');
            $this->db
                ->where('settingsID', $settingsID)
                ->where('user_id', $userId)
                ->where('issue_id', (int) $issueId)
                ->update('support_notifications', [
                    'is_read' => 1,
                    'read_at' => $now,
                ]);
        }

        $comments = [];
        if ($perms['view']) {
            $rows = $this->db
                ->select('c.*, CONCAT(COALESCE(u.fName, ""), " ", COALESCE(u.lName, "")) AS employee_name')
                ->from('support_issue_comments c')
                ->join('users u', 'u.user_id = c.employee_id', 'left')
                ->where('c.settingsID', $settingsID)
                ->where('c.issue_id', (int) $issueId)
                ->order_by('c.created_at', 'ASC')
                ->get()
                ->result();

            foreach ($rows as $row) {
                $employeeId = (int) ($row->employee_id ?? 0);
                $customerComment = (int) ($row->customer_comment ?? 0) === 1;
                $authorName = $customerComment
                    ? trim((string) ($issue->customer_name ?? 'Customer'))
                    : trim((string) ($row->employee_name ?? ''));
                if ($authorName === '') {
                    $authorName = $customerComment ? 'Customer' : 'Support Team';
                }

                $comments[] = [
                    'id'              => (int) ($row->id ?? 0),
                    'employee_id'     => $employeeId,
                    'author_name'     => $authorName,
                    'is_customer'     => $customerComment,
                    'is_mine'         => !$customerComment && $employeeId === $userId,
                    'is_internal'     => (int) ($row->internal_note ?? 0) === 1,
                    'comment'         => (string) ($row->comment ?? ''),
                    'attachment_path' => (string) ($row->attachment_path ?? ''),
                    'created_at'      => (string) ($row->created_at ?? ''),
                    'created_label'   => $this->_format_support_timestamp($row->created_at ?? ''),
                ];
            }
        }

        return mobile_json([
            'ok'              => true,
            'issue'           => $this->_support_issue_payload($issue, $userId, true),
            'comments'        => $comments,
            'can_view_chat'   => (bool) $perms['view'],
            'can_reply_chat'  => (bool) $perms['reply'],
            'assignable_users' => $this->_support_user_option_payload(
                $this->_mobile_support_assignable_users($settingsID, (int) ($issue->department_id ?? 0))
            ),
            'taggable_users' => $this->_support_user_option_payload(
                $this->_mobile_support_taggable_users($settingsID)
            ),
            'current_user_id' => $userId,
        ]);
    }

    public function supportIssueComment($issueId)
    {
        if ($this->_method() === 'OPTIONS') {
            return mobile_json(['ok' => true]);
        }
        if ($this->_method() !== 'POST') {
            return mobile_json(['ok' => false, 'message' => 'Method not allowed.'], 405);
        }

        $claims = $this->_require_staff_claims();
        if ($claims === null) {
            return;
        }

        $settingsID = (int) ($claims['settingsID'] ?? 0);
        $userId     = (int) ($claims['user_id'] ?? 0);

        $issue = $this->_mobile_support_issue_for_user((int) $issueId, $settingsID, $userId);
        if (!$issue) {
            return mobile_json(['ok' => false, 'message' => 'Support ticket not found.'], 404);
        }

        $perms = $this->_mobile_support_chat_permissions($settingsID, $userId);
        if (!$perms['reply']) {
            return mobile_json([
                'ok'      => false,
                'message' => 'You are not allowed to reply in support ticket chats.',
            ], 403);
        }

        $payload = $this->_read_payload();
        $comment = trim((string) ($payload['comment'] ?? ''));
        $attachment = $this->_handle_mobile_support_comment_attachment('attachment');

        if ($comment === '' && $attachment['path'] === null) {
            return mobile_json([
                'ok'      => false,
                'message' => 'Comment or attachment is required.',
            ], 422);
        }

        date_default_timezone_set('Asia/Manila');
        $now = date('Y-m-d H:i:s');

        $this->db->insert('support_issue_comments', [
            'issue_id'        => (int) $issueId,
            'employee_id'     => $userId,
            'customer_comment' => 0,
            'comment'         => $comment,
            'internal_note'   => 0,
            'attachment_path' => $attachment['path'],
            'created_at'      => $now,
            'settingsID'      => $settingsID,
        ]);

        $this->db
            ->where('id', (int) $issueId)
            ->where('settingsID', $settingsID)
            ->update('support_issues', [
                'status'                => 'open',
                'client_reply_required' => 1,
                'updated_at'            => $now,
            ]);

        return mobile_json([
            'ok'      => true,
            'message' => $attachment['warning'] !== ''
                ? 'Reply posted, but the attachment was not saved: ' . $attachment['warning']
                : 'Reply posted.',
        ]);
    }

    public function supportIssueClose($issueId)
    {
        if ($this->_method() === 'OPTIONS') {
            return mobile_json(['ok' => true]);
        }
        if ($this->_method() !== 'POST') {
            return mobile_json(['ok' => false, 'message' => 'Method not allowed.'], 405);
        }

        $claims = $this->_require_staff_claims();
        if ($claims === null) {
            return;
        }

        $settingsID = (int) ($claims['settingsID'] ?? 0);
        $userId     = (int) ($claims['user_id'] ?? 0);
        $username   = trim((string) ($claims['username'] ?? 'system'));

        $issue = $this->_mobile_support_issue_for_user((int) $issueId, $settingsID, $userId);
        if (!$issue) {
            return mobile_json(['ok' => false, 'message' => 'Support ticket not found.'], 404);
        }

        $payload = $this->_read_payload();
        $closeMessage = trim((string) ($payload['close_message'] ?? $payload['message'] ?? ''));
        if ($closeMessage === '') {
            return mobile_json([
                'ok' => false,
                'message' => 'Please enter the message for the client before closing the ticket.',
            ], 422);
        }

        date_default_timezone_set('Asia/Manila');
        $closedAt = date('Y-m-d H:i:s');

        $this->db
            ->where('id', (int) $issueId)
            ->where('settingsID', $settingsID)
            ->update('support_issues', [
                'status' => 'closed',
                'resolution_details' => $closeMessage,
                'resolution_date' => $closedAt,
                'resolved_by' => $userId,
                'client_reply_required' => 0,
                'updated_at' => $closedAt,
            ]);

        if ((int) ($issue->task_id ?? 0) > 0) {
            $this->db
                ->where('taskID', (int) $issue->task_id)
                ->where('settingsID', $settingsID)
                ->update('projects_task', ['taskStat' => '0']);

            $this->db->insert('projects_task_stat', [
                'taskID' => (int) $issue->task_id,
                'note' => 'Support ticket closed. Message to client: ' . $closeMessage,
                'datePosted' => $closedAt,
                'postedBy' => $username !== '' ? $username : 'system',
                'taskStat' => '0',
            ]);
        }

        $this->db->insert('support_issue_comments', [
            'issue_id' => (int) $issueId,
            'employee_id' => $userId,
            'customer_comment' => 0,
            'comment' => $closeMessage,
            'internal_note' => 0,
            'attachment_path' => null,
            'created_at' => $closedAt,
            'settingsID' => $settingsID,
        ]);

        return mobile_json([
            'ok' => true,
            'message' => 'Ticket closed successfully.',
        ]);
    }

    public function supportIssueForward($issueId)
    {
        if ($this->_method() === 'OPTIONS') {
            return mobile_json(['ok' => true]);
        }
        if ($this->_method() !== 'POST') {
            return mobile_json(['ok' => false, 'message' => 'Method not allowed.'], 405);
        }

        $claims = $this->_require_staff_claims();
        if ($claims === null) {
            return;
        }

        $settingsID = (int) ($claims['settingsID'] ?? 0);
        $userId = (int) ($claims['user_id'] ?? 0);
        $username = trim((string) ($claims['username'] ?? 'system'));

        $issue = $this->_mobile_support_issue_for_user((int) $issueId, $settingsID, $userId);
        if (!$issue) {
            return mobile_json(['ok' => false, 'message' => 'Support ticket not found.'], 404);
        }

        $payload = $this->_read_payload();
        $forwardTo = (int) ($payload['forward_to'] ?? 0);
        $forwardNote = trim((string) ($payload['forward_note'] ?? ''));

        if ($forwardTo <= 0) {
            return mobile_json(['ok' => false, 'message' => 'Please select who to forward this ticket to.'], 422);
        }

        $allowedIds = array_map(function ($row) {
            return (int) ($row->user_id ?? 0);
        }, $this->_mobile_support_assignable_users($settingsID, (int) ($issue->department_id ?? 0)));

        if (!in_array($forwardTo, $allowedIds, true)) {
            return mobile_json(['ok' => false, 'message' => 'Selected employee was not found.'], 404);
        }

        $employee = $this->_mobile_support_employee_row($settingsID, $forwardTo);
        if (!$employee) {
            return mobile_json(['ok' => false, 'message' => 'Selected employee was not found.'], 404);
        }

        $employeeName = $this->_mobile_support_employee_name($employee);
        $forwardMessage = 'Forwarded to ' . $employeeName;
        if ($forwardNote !== '') {
            $forwardMessage .= '. Note: ' . $forwardNote;
        }

        $taskAssignmentNote = 'Support issue assigned to ' . $employeeName . '.';
        if ($forwardNote !== '') {
            $taskAssignmentNote .= ' Note: ' . $forwardNote;
        }

        $forwardedTaskId = $this->_support_forward_issue_task($issue, $forwardTo, $forwardNote, $userId, $username);
        if ($forwardedTaskId <= 0) {
            return mobile_json(['ok' => false, 'message' => 'Issue could not be forwarded.'], 500);
        }

        $this->db->insert('support_issue_comments', [
            'issue_id' => (int) $issueId,
            'employee_id' => $userId,
            'customer_comment' => 0,
            'comment' => $forwardMessage,
            'internal_note' => 1,
            'created_at' => date('Y-m-d H:i:s'),
            'settingsID' => $settingsID,
        ]);

        return mobile_json([
            'ok' => true,
            'message' => 'Issue forwarded successfully to ' . $employeeName . '.',
            'task_id' => $forwardedTaskId,
            'task_note' => $taskAssignmentNote,
        ]);
    }

    public function supportIssueTag($issueId)
    {
        if ($this->_method() === 'OPTIONS') {
            return mobile_json(['ok' => true]);
        }
        if ($this->_method() !== 'POST') {
            return mobile_json(['ok' => false, 'message' => 'Method not allowed.'], 405);
        }

        $claims = $this->_require_staff_claims();
        if ($claims === null) {
            return;
        }

        $settingsID = (int) ($claims['settingsID'] ?? 0);
        $userId = (int) ($claims['user_id'] ?? 0);
        $issue = $this->_mobile_support_issue_for_user((int) $issueId, $settingsID, $userId);
        if (!$issue) {
            return mobile_json(['ok' => false, 'message' => 'Support ticket not found.'], 404);
        }

        $payload = $this->_read_payload();
        $tagNote = trim((string) ($payload['tag_note'] ?? ''));
        $tagUserIds = $payload['tag_user_ids'] ?? [];
        if (!is_array($tagUserIds)) {
            $singleUserId = (int) ($payload['tag_user_id'] ?? 0);
            $tagUserIds = $singleUserId > 0 ? [$singleUserId] : [];
        }
        $tagUserIds = array_values(array_unique(array_filter(array_map('intval', $tagUserIds))));

        if (empty($tagUserIds)) {
            return mobile_json(['ok' => false, 'message' => 'Please select at least one user to tag.'], 422);
        }

        $allowedIds = array_map(function ($row) {
            return (int) ($row->user_id ?? 0);
        }, $this->_mobile_support_taggable_users($settingsID));

        $taggedNames = [];
        foreach ($tagUserIds as $tagUserId) {
            if (!in_array($tagUserId, $allowedIds, true)) {
                continue;
            }
            $employee = $this->_mobile_support_employee_row($settingsID, $tagUserId);
            if (!$employee) {
                continue;
            }

            $employeeName = $this->_mobile_support_employee_name($employee);
            $taskNote = 'Assigned from support ticket ' . trim((string) ($issue->ticket_number ?? ('#' . $issueId))) . ' to ' . $employeeName . ' via tagging.';
            if ($tagNote !== '') {
                $taskNote .= ' Note: ' . $tagNote;
            }

            $taskId = $this->_support_create_tagged_task_copy($issue, $tagUserId, $tagNote, $userId, trim((string) ($claims['username'] ?? 'system')));
            if ($taskId <= 0) {
                continue;
            }
            $taggedNames[] = $employeeName;
        }

        if (empty($taggedNames)) {
            return mobile_json(['ok' => false, 'message' => 'Selected employees could not be tagged.'], 500);
        }

        $tagMessage = 'Tagged ' . implode(', ', $taggedNames);
        if ($tagNote !== '') {
            $tagMessage .= '. Note: ' . $tagNote;
        }

        $this->db->insert('support_issue_comments', [
            'issue_id' => (int) $issueId,
            'employee_id' => $userId,
            'customer_comment' => 0,
            'comment' => $tagMessage,
            'internal_note' => 1,
            'created_at' => date('Y-m-d H:i:s'),
            'settingsID' => $settingsID,
        ]);

        return mobile_json([
            'ok' => true,
            'message' => 'User tagged successfully.',
            'tagged_names' => $taggedNames,
        ]);
    }

    // ── Support helpers (self-contained, mirror Page.php logic) ────────────

    private function _mobile_accessible_issue_ids($settingsID, $userId)
    {
        if (!$this->db->table_exists('support_issues')) {
            return [];
        }

        $accessibleIssueIds = [];

        $directRows = $this->db
            ->select('id')
            ->from('support_issues')
            ->where('settingsID', $settingsID)
            ->where('assigned_employee_id', $userId)
            ->get()
            ->result();
        foreach ($directRows as $row) {
            $accessibleIssueIds[] = (int) ($row->id ?? 0);
        }

        $departmentIds = $this->_mobile_staff_support_department_ids($settingsID, $userId);
        if (!empty($departmentIds)) {
            $deptRows = $this->db
                ->select('id')
                ->from('support_issues')
                ->where('settingsID', $settingsID)
                ->where_in('department_id', $departmentIds)
                ->get()
                ->result();
            foreach ($deptRows as $row) {
                $accessibleIssueIds[] = (int) ($row->id ?? 0);
            }
        }

        if ($this->db->table_exists('support_notifications')) {
            $notifRows = $this->db
                ->select('issue_id')
                ->from('support_notifications')
                ->where('settingsID', $settingsID)
                ->where('user_id', $userId)
                ->where('issue_id IS NOT NULL', null, false)
                ->get()
                ->result();
            foreach ($notifRows as $row) {
                $accessibleIssueIds[] = (int) ($row->issue_id ?? 0);
            }
        }

        if ($this->db->table_exists('projects_task') && $this->db->field_exists('forwarded_from', 'projects_task')) {
            $taskRows = $this->db
                ->select('taskID, forwarded_from')
                ->from('projects_task')
                ->where('settingsID', $settingsID)
                ->where('assignedPerson', $userId)
                ->group_start()
                ->where('forwarded_from IS NOT NULL', null, false)
                ->where('forwarded_from >', 0)
                ->group_end()
                ->get()
                ->result();

            $taskIds = [];
            foreach ($taskRows as $row) {
                $taskIds[] = (int) ($row->taskID ?? 0);
                $taskIds[] = (int) ($row->forwarded_from ?? 0);
            }
            $taskIds = array_values(array_unique(array_filter($taskIds)));

            if (!empty($taskIds) && $this->db->field_exists('task_id', 'support_issues')) {
                $issueRows = $this->db
                    ->select('id')
                    ->from('support_issues')
                    ->where('settingsID', $settingsID)
                    ->where_in('task_id', $taskIds)
                    ->get()
                    ->result();

                foreach ($issueRows as $row) {
                    $accessibleIssueIds[] = (int) ($row->id ?? 0);
                }
            }
        }

        return array_values(array_unique(array_filter($accessibleIssueIds)));
    }

    private function _mobile_staff_support_department_ids($settingsID, $userId)
    {
        $departmentIds = [];
        if ($this->db->table_exists('employee_departments')) {
            $rows = $this->db
                ->select('department_id')
                ->from('employee_departments')
                ->where('settingsID', $settingsID)
                ->where('employee_id', $userId)
                ->where('is_active', 1)
                ->get()
                ->result();
            foreach ($rows as $row) {
                $departmentIds[] = (int) ($row->department_id ?? 0);
            }
        }

        $departmentIds = array_values(array_unique(array_filter($departmentIds)));

        $employeeDepartment = '';
        $employee = null;
        if ($this->db->table_exists('employee')) {
            $employee = $this->db
                ->select('COALESCE(NULLIF(TRIM(e.department), \'\'), \'\') AS department', false)
                ->from('users u')
                ->join('employee e', 'e.empID = u.user_id AND e.settingsID = u.settingsID', 'left')
                ->where('u.settingsID', $settingsID)
                ->where('u.user_id', $userId)
                ->limit(1)
                ->get()
                ->row();
        }
        if ($employee) {
            $employeeDepartment = strtolower(trim((string) ($employee->department ?? '')));
        }

        $departmentKey = '';
        if ($employeeDepartment === 'technical' || $employeeDepartment === 'support') {
            $departmentKey = 'technical';
        } elseif ($employeeDepartment === 'billing') {
            $departmentKey = 'billing';
        } elseif ($employeeDepartment === 'general') {
            $departmentKey = 'general';
        }

        if (empty($departmentIds) && $departmentKey === '') {
            return [];
        }

        if ($this->db->table_exists('support_departments')) {
            $all = $this->db
                ->select('id')
                ->from('support_departments')
                ->where('settingsID', $settingsID)
                ->get()
                ->result();
            foreach ($all as $row) {
                $departmentIds[] = (int) ($row->id ?? 0);
            }
        }

        return array_values(array_unique(array_filter($departmentIds)));
    }

    private function _handle_mobile_support_comment_attachment($fieldName)
    {
        $result = [
            'path' => null,
            'warning' => '',
        ];

        if (!isset($_FILES[$fieldName]) || (int) ($_FILES[$fieldName]['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            return $result;
        }

        $uploadErr = (int) ($_FILES[$fieldName]['error'] ?? UPLOAD_ERR_NO_FILE);
        if ($uploadErr !== UPLOAD_ERR_OK) {
            $result['warning'] = $uploadErr === UPLOAD_ERR_INI_SIZE || $uploadErr === UPLOAD_ERR_FORM_SIZE
                ? 'Attachment exceeds the maximum allowed size.'
                : 'Attachment upload failed (error code ' . $uploadErr . ').';
            return $result;
        }

        $allowedExts = ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx', 'heic', 'heif', 'mp4', 'mov', 'avi', 'webm', 'm4v'];
        $fileSize = (int) ($_FILES[$fieldName]['size'] ?? 0);
        $maxSize = 20 * 1024 * 1024;
        $originalName = (string) ($_FILES[$fieldName]['name'] ?? '');
        $fileExt = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

        if (!in_array($fileExt, $allowedExts, true)) {
            $result['warning'] = 'Attachment file type not allowed. Allowed: PDF, PNG, JPG, DOC, DOCX, HEIC, HEIF, MP4, MOV, AVI, WEBM.';
            return $result;
        }
        if ($fileSize > $maxSize) {
            $result['warning'] = 'Attachment exceeds the 20MB size limit.';
            return $result;
        }

        $uploadDir = FCPATH . 'uploads/comment_attachments/';
        if (!is_dir($uploadDir) && !mkdir($uploadDir, 0755, true) && !is_dir($uploadDir)) {
            $result['warning'] = 'Unable to create upload directory for attachments.';
            return $result;
        }

        $fileName = 'comment_' . time() . '_' . uniqid('', true) . '.' . $fileExt;
        $filePath = $uploadDir . $fileName;
        $tmpName = (string) ($_FILES[$fieldName]['tmp_name'] ?? '');

        if ($tmpName === '' || !move_uploaded_file($tmpName, $filePath)) {
            $result['warning'] = 'Unable to save the attachment file.';
            return $result;
        }

        $result['path'] = 'uploads/comment_attachments/' . $fileName;
        return $result;
    }

    private function _query_support_issues($settingsID, $userId, $scope, array $accessible)
    {
        if (!$this->db->table_exists('support_issues')) {
            return [];
        }

        $select = 'si.*, p.projectDescription, CONCAT(COALESCE(u.fName, ""), " ", COALESCE(u.lName, "")) AS assigned_employee_name';
        if ($this->db->table_exists('support_departments')) {
            $select .= ', d.department_name';
        } else {
            $select .= ", '' AS department_name";
        }

        $this->db
            ->select($select, false)
            ->from('support_issues si')
            ->join('projects p', 'p.projectID = si.project_id', 'left')
            ->join('users u', 'u.user_id = si.assigned_employee_id', 'left')
            ->where('si.settingsID', $settingsID);
        if ($this->db->table_exists('support_departments')) {
            $this->db->join('support_departments d', 'd.id = si.department_id', 'left');
        }

        if (empty($accessible)) {
            $this->db->where('si.id', 0);
        } else {
            $this->db->where_in('si.id', $accessible);
        }

        $this->_apply_support_scope($scope);

        return $this->db
            ->order_by('si.created_at', 'DESC')
            ->get()
            ->result();
    }

    private function _count_support_issues($settingsID, $userId, $scope, array $accessible)
    {
        if (!$this->db->table_exists('support_issues')) {
            return 0;
        }

        $this->db
            ->from('support_issues si')
            ->where('si.settingsID', $settingsID);

        if (empty($accessible)) {
            $this->db->where('si.id', 0);
        } else {
            $this->db->where_in('si.id', $accessible);
        }

        $this->_apply_support_scope($scope);

        return (int) $this->db->count_all_results();
    }

    private function _apply_support_scope($scope)
    {
        if ($scope === 'unassigned') {
            $this->db->group_start()
                ->where('si.assigned_employee_id IS NULL', null, false)
                ->or_where('si.assigned_employee_id', 0)
                ->group_end()
                ->where("LOWER(TRIM(COALESCE(si.status, ''))) NOT IN ('closed','resolved','done','completed')", null, false);
        } elseif ($scope === 'closed') {
            $this->db->where("LOWER(TRIM(COALESCE(si.status, ''))) IN ('closed','resolved','done','completed')", null, false);
        } elseif ($scope === 'open') {
            $this->db->where("LOWER(TRIM(COALESCE(si.status, ''))) NOT IN ('closed','resolved','done','completed')", null, false);
        }
    }

    private function _mobile_support_issue_for_user($issueId, $settingsID, $userId)
    {
        if ($issueId <= 0 || $settingsID <= 0 || !$this->db->table_exists('support_issues')) {
            return null;
        }

        $select = 'si.*, p.projectDescription, CONCAT(COALESCE(u.fName, ""), " ", COALESCE(u.lName, "")) AS assigned_employee_name';
        if ($this->db->table_exists('support_departments')) {
            $select .= ', d.department_name';
        } else {
            $select .= ", '' AS department_name";
        }

        $this->db
            ->select($select, false)
            ->from('support_issues si')
            ->join('projects p', 'p.projectID = si.project_id', 'left')
            ->join('users u', 'u.user_id = si.assigned_employee_id', 'left')
            ->where('si.id', $issueId)
            ->where('si.settingsID', $settingsID)
            ->limit(1);
        if ($this->db->table_exists('support_departments')) {
            $this->db->join('support_departments d', 'd.id = si.department_id', 'left');
        }
        $row = $this->db->get()->row();

        if (!$row) {
            return null;
        }

        // Allow if assigned to me, or in my department, or referenced in my notifications
        $departmentIds = $this->_mobile_staff_support_department_ids($settingsID, $userId);
        if ((int) ($row->assigned_employee_id ?? 0) === $userId) return $row;
        if (in_array((int) ($row->department_id ?? 0), $departmentIds, true)) return $row;

        if ($this->db->table_exists('support_notifications')) {
            $hasNotif = $this->db
                ->from('support_notifications')
                ->where('settingsID', $settingsID)
                ->where('user_id', $userId)
                ->where('issue_id', $issueId)
                ->count_all_results() > 0;
            if ($hasNotif) return $row;
        }

        return null;
    }

    private function _mobile_support_chat_permissions($settingsID, $userId)
    {
        $row = $this->db
            ->select('support_chat_view, support_chat_reply')
            ->from('users')
            ->where('settingsID', $settingsID)
            ->where('user_id', $userId)
            ->limit(1)
            ->get()
            ->row();

        return [
            'view'  => $row ? ((int) ($row->support_chat_view ?? 1) === 1) : true,
            'reply' => $row ? ((int) ($row->support_chat_reply ?? 1) === 1) : true,
        ];
    }

    private function _support_user_option_payload($rows)
    {
        $payload = [];
        foreach ((array) $rows as $row) {
            $userId = (int) ($row->user_id ?? 0);
            if ($userId <= 0) {
                continue;
            }
            $payload[] = [
                'user_id' => $userId,
                'name' => $this->_mobile_support_employee_name($row),
            ];
        }
        return $payload;
    }

    private function _mobile_support_assignable_users($settingsID, $departmentId = 0)
    {
        $settingsID = (int) $settingsID;
        $departmentId = (int) $departmentId;
        $departmentFilter = '';

        if ($departmentId > 0) {
            $department = $this->db
                ->select('department_name')
                ->from('support_departments')
                ->where('support_departments.settingsID', $settingsID)
                ->where('support_departments.id', $departmentId)
                ->limit(1)
                ->get()
                ->row();

            $departmentName = strtolower(trim((string) ($department->department_name ?? '')));
            if ($departmentName !== '') {
                $departmentFilter = "AND LOWER(TRIM(COALESCE(e.department, ''))) = " . $this->db->escape($departmentName);
            }
        }

        $sql = "
            SELECT e.empID, e.fName, e.mName, e.lName, e.email, e.department, u.user_id, u.username, u.position
            FROM employee e
            INNER JOIN users u ON u.user_id = e.empID AND u.settingsID = e.settingsID
            WHERE e.settingsID = ?
              AND u.acctStat = 'active'
              AND u.position IN ('Admin', 'Manager', 'Encoder', 'Staff', 'Cashier', 'POS Admin', 'POS Staff')
              $departmentFilter
            GROUP BY e.empID
            ORDER BY e.fName ASC, e.lName ASC
        ";

        $users = $this->db->query($sql, [$settingsID])->result();

        if ($departmentId > 0 && empty($users)) {
            $fallbackSql = "
                SELECT e.empID, e.fName, e.mName, e.lName, e.email, e.department, u.user_id, u.username, u.position
                FROM employee e
                INNER JOIN users u ON u.user_id = e.empID AND u.settingsID = e.settingsID
                WHERE e.settingsID = ?
                  AND u.acctStat = 'active'
                  AND u.position IN ('Admin', 'Manager', 'Encoder', 'Staff', 'Cashier', 'POS Admin', 'POS Staff')
                GROUP BY e.empID
                ORDER BY e.fName ASC, e.lName ASC
            ";

            $users = $this->db->query($fallbackSql, [$settingsID])->result();
        }

        return $users;
    }

    private function _mobile_support_taggable_users($settingsID)
    {
        $settingsID = (int) $settingsID;

        $sql = "
            SELECT
              COALESCE(e.empID, u.user_id) AS empID,
              COALESCE(NULLIF(TRIM(e.fName), ''), NULLIF(TRIM(u.fName), ''), '') AS fName,
              COALESCE(NULLIF(TRIM(e.mName), ''), NULLIF(TRIM(u.mName), ''), '') AS mName,
              COALESCE(NULLIF(TRIM(e.lName), ''), NULLIF(TRIM(u.lName), ''), '') AS lName,
              COALESCE(NULLIF(TRIM(e.email), ''), NULLIF(TRIM(u.email), ''), NULLIF(TRIM(u.username), '')) AS email,
              COALESCE(NULLIF(TRIM(e.department), ''), '') AS department,
              u.user_id,
              u.username,
              u.position
            FROM users u
            LEFT JOIN employee e ON e.empID = u.user_id AND e.settingsID = u.settingsID
            WHERE u.settingsID = ?
              AND u.acctStat = 'active'
              AND u.position IN ('Admin', 'Manager', 'Encoder', 'Staff', 'Cashier', 'POS Admin', 'POS Staff')
            GROUP BY u.user_id
            ORDER BY
              COALESCE(NULLIF(TRIM(e.fName), ''), NULLIF(TRIM(u.fName), ''), u.username) ASC,
              COALESCE(NULLIF(TRIM(e.lName), ''), NULLIF(TRIM(u.lName), ''), '') ASC
        ";

        return $this->db->query($sql, [$settingsID])->result();
    }

    private function _mobile_support_employee_row($settingsID, $userId)
    {
        $settingsID = (int) $settingsID;
        $userId = (int) $userId;

        if ($settingsID <= 0 || $userId <= 0) {
            return null;
        }

        $sql = "
            SELECT
              COALESCE(e.empID, u.user_id) AS empID,
              COALESCE(NULLIF(TRIM(e.fName), ''), NULLIF(TRIM(u.fName), ''), '') AS fName,
              COALESCE(NULLIF(TRIM(e.mName), ''), NULLIF(TRIM(u.mName), ''), '') AS mName,
              COALESCE(NULLIF(TRIM(e.lName), ''), NULLIF(TRIM(u.lName), ''), '') AS lName,
              COALESCE(NULLIF(TRIM(e.email), ''), NULLIF(TRIM(u.email), ''), NULLIF(TRIM(u.username), '')) AS email,
              COALESCE(NULLIF(TRIM(e.department), ''), '') AS department,
              u.user_id,
              u.username,
              u.position
            FROM users u
            LEFT JOIN employee e ON e.empID = u.user_id AND e.settingsID = u.settingsID
            WHERE u.settingsID = ?
              AND u.user_id = ?
            LIMIT 1
        ";

        return $this->db->query($sql, [$settingsID, $userId])->row();
    }

    private function _mobile_support_employee_name($employee)
    {
        if (!$employee) {
            return '';
        }

        $name = trim((string) (($employee->fName ?? '') . ' ' . ($employee->mName ?? '') . ' ' . ($employee->lName ?? '')));
        if ($name !== '') {
            return $name;
        }

        $username = trim((string) ($employee->username ?? ''));
        if ($username !== '') {
            return $username;
        }

        return 'Staff #' . (int) ($employee->user_id ?? 0);
    }

    private function _support_issue_task_priority($priority)
    {
        $priority = strtolower(trim((string) $priority));
        if ($priority === 'high' || $priority === 'urgent') {
            return '1';
        }
        if ($priority === 'low') {
            return '3';
        }
        return '2';
    }

    private function _support_issue_task_row($taskId, $settingsID)
    {
        $taskId = (int) $taskId;
        $settingsID = (int) $settingsID;

        if ($taskId <= 0 || $settingsID <= 0) {
            return null;
        }

        return $this->db
            ->select('taskID, projectID, assignedPerson, taskStat')
            ->from('projects_task')
            ->where('taskID', $taskId)
            ->where('settingsID', $settingsID)
            ->limit(1)
            ->get()
            ->row();
    }

    private function _ensure_support_issue_project_task($issue, $assignedUserId = 0, $postedBy = 'system')
    {
        if (!$issue) {
            return 0;
        }

        $settingsID = (int) ($issue->settingsID ?? 0);
        $issueId = (int) ($issue->id ?? 0);
        $projectId = max(0, (int) ($issue->project_id ?? 0));
        $assignedUserId = (int) $assignedUserId;

        if ($settingsID <= 0 || $issueId <= 0) {
            return 0;
        }

        $existingTask = $this->_support_issue_task_row((int) ($issue->task_id ?? 0), $settingsID);
        if ($existingTask) {
            $taskUpdate = [];

            if ($projectId > 0 && (int) ($existingTask->projectID ?? 0) <= 0) {
                $taskUpdate['projectID'] = $projectId;
            }

            if ($assignedUserId > 0 && (int) ($existingTask->assignedPerson ?? 0) <= 0) {
                $taskUpdate['assignedPerson'] = $assignedUserId;
            }

            if (!empty($taskUpdate)) {
                $this->db
                    ->where('taskID', (int) $existingTask->taskID)
                    ->where('settingsID', $settingsID)
                    ->update('projects_task', $taskUpdate);
            }

            return (int) $existingTask->taskID;
        }

        $reportedDate = date('Y-m-d');
        $createdAt = trim((string) ($issue->created_at ?? ''));
        if ($createdAt !== '') {
            $createdTs = strtotime($createdAt);
            if ($createdTs !== false) {
                $reportedDate = date('Y-m-d', $createdTs);
            }
        }

        $taskLabel = trim((string) ($issue->title ?? ''));
        if ($taskLabel === '') {
            $taskLabel = 'Support Issue ' . trim((string) ($issue->ticket_number ?? $issueId));
        }
        $taskLabel = $this->_normalize_task_label($taskLabel);

        $taskData = [
            'taskID' => 0,
            'task' => $taskLabel,
            'reportedDate' => $reportedDate,
            'projectID' => $projectId,
            'taskStat' => '1',
            'priority' => $this->_support_issue_task_priority($issue->priority ?? 'medium'),
            'settingsID' => $settingsID,
            'assignedPerson' => $assignedUserId > 0 ? $assignedUserId : (int) ($issue->assigned_employee_id ?? 0),
            'added_by' => 'support_' . trim((string) ($issue->ticket_number ?? $issueId)),
        ];

        if ($this->db->field_exists('dueDate', 'projects_task')) {
            $dueDate = null;
            $issueDueDate = trim((string) ($issue->due_date ?? ''));
            if ($issueDueDate !== '') {
                $dueDateTs = strtotime($issueDueDate);
                if ($dueDateTs !== false) {
                    $dueDate = date('Y-m-d', $dueDateTs);
                }
            }
            $taskData['dueDate'] = $dueDate ?: $reportedDate;
        }

        if ($this->db->field_exists('attachment_link', 'projects_task')) {
            $taskData['attachment_link'] = null;
        }

        $this->db->insert('projects_task', $taskData);
        $taskId = (int) $this->db->insert_id();

        if ($taskId <= 0) {
            return 0;
        }

        $this->db
            ->where('id', $issueId)
            ->where('settingsID', $settingsID)
            ->update('support_issues', [
                'task_id' => $taskId,
            ]);

        $note = 'Task created from support ticket ' . trim((string) ($issue->ticket_number ?? ('#' . $issueId))) . '.';
        $description = trim((string) ($issue->description ?? ''));
        if ($description !== '') {
            $note .= ' Details: ' . $description;
        }

        $statData = [
            'taskID' => $taskId,
            'note' => $note,
            'datePosted' => date('Y-m-d H:i:s'),
            'postedBy' => $postedBy !== '' ? $postedBy : 'system',
            'taskStat' => '1',
        ];

        if ($this->db->field_exists('points', 'projects_task_stat')) {
            $statData['points'] = 1;
        }

        $this->db->insert('projects_task_stat', $statData);

        return $taskId;
    }

    private function _assign_support_issue_task($issue, $assignedUserId, $note, $postedBy = 'system', $updateIssue = true)
    {
        if (!$issue) {
            return 0;
        }

        $settingsID = (int) ($issue->settingsID ?? 0);
        $issueId = (int) ($issue->id ?? 0);
        $assignedUserId = (int) $assignedUserId;

        if ($settingsID <= 0 || $issueId <= 0 || $assignedUserId <= 0) {
            return 0;
        }

        $taskId = $this->_ensure_support_issue_project_task($issue, $assignedUserId, $postedBy);
        if ($taskId <= 0) {
            return 0;
        }

        $this->db
            ->where('taskID', $taskId)
            ->where('settingsID', $settingsID)
            ->update('projects_task', [
                'assignedPerson' => $assignedUserId,
            ]);

        if ($updateIssue) {
            $this->db
                ->where('id', $issueId)
                ->where('settingsID', $settingsID)
                ->update('support_issues', [
                    'task_id' => $taskId,
                    'assigned_employee_id' => $assignedUserId,
                    'status' => 'assigned',
                ]);
        }

        $statData = [
            'taskID' => $taskId,
            'note' => $note,
            'datePosted' => date('Y-m-d H:i:s'),
            'postedBy' => $postedBy !== '' ? $postedBy : 'system',
            'taskStat' => '1',
        ];

        if ($this->db->field_exists('points', 'projects_task_stat')) {
            $statData['points'] = 1;
        }

        $this->db->insert('projects_task_stat', $statData);
        return $taskId;
    }

    private function _support_create_tagged_task_copy($issue, $tagUserId, $tagNote = '', $actorUserId = 0, $postedBy = 'system')
    {
        if (!$issue) {
            return 0;
        }

        $settingsID = (int) ($issue->settingsID ?? 0);
        $issueId = (int) ($issue->id ?? 0);
        $tagUserId = (int) $tagUserId;

        if ($settingsID <= 0 || $issueId <= 0 || $tagUserId <= 0) {
            return 0;
        }

        $currentTaskId = $this->_ensure_support_issue_project_task($issue, (int) ($issue->assigned_employee_id ?? 0), $postedBy);
        if ($currentTaskId <= 0) {
            return 0;
        }

        $originalTask = $this->db
            ->select('*')
            ->from('projects_task')
            ->where('taskID', $currentTaskId)
            ->where('settingsID', $settingsID)
            ->limit(1)
            ->get()
            ->row();

        if (!$originalTask) {
            return 0;
        }

        $employee = $this->_mobile_support_employee_row($settingsID, $tagUserId);
        if (!$employee) {
            return 0;
        }

        $employeeName = $this->_mobile_support_employee_name($employee);
        $date = date('Y-m-d H:i:s');
        $taskData = [
            'taskID' => 0,
            'task' => $this->_normalize_task_label(((string) ($originalTask->task ?? '')) . ' [Tagged: ' . $employeeName . ']'),
            'reportedDate' => $originalTask->reportedDate,
            'projectID' => $originalTask->projectID,
            'taskStat' => '1',
            'priority' => $originalTask->priority,
            'settingsID' => $settingsID,
            'assignedPerson' => $tagUserId,
            'added_by' => (string) ($originalTask->added_by ?? $postedBy),
        ];

        if ($this->db->field_exists('forwarded_from', 'projects_task')) {
            $taskData['forwarded_from'] = (int) $originalTask->taskID;
        }
        if ($this->db->field_exists('forwarded_to', 'projects_task')) {
            $taskData['forwarded_to'] = $tagUserId;
        }
        if ($this->db->field_exists('forwarded_by', 'projects_task')) {
            $taskData['forwarded_by'] = $actorUserId > 0 ? $actorUserId : null;
        }
        if ($this->db->field_exists('forwarded_note', 'projects_task')) {
            $taskData['forwarded_note'] = $tagNote;
        }
        if ($this->db->field_exists('forwarded_date', 'projects_task')) {
            $taskData['forwarded_date'] = $date;
        }
        if ($this->db->field_exists('dueDate', 'projects_task')) {
            $taskData['dueDate'] = $originalTask->dueDate;
        }
        if ($this->db->field_exists('attachment_link', 'projects_task')) {
            $taskData['attachment_link'] = $originalTask->attachment_link;
        }

        $this->db->insert('projects_task', $taskData);
        $taskId = (int) $this->db->insert_id();
        if ($taskId <= 0) {
            return 0;
        }

        $this->db->insert('projects_task_stat', [
            'taskID' => $taskId,
            'note' => 'Tagged from support ticket ' . trim((string) ($issue->ticket_number ?? ('#' . $issueId))) . ' to ' . $employeeName . ($tagNote !== '' ? '. Note: ' . $tagNote : '.'),
            'datePosted' => $date,
            'postedBy' => $postedBy !== '' ? $postedBy : 'system',
            'taskStat' => '1',
        ]);

        return $taskId;
    }

    private function _support_forward_issue_task($issue, $forwardToUserId, $forwardNote = '', $actorUserId = 0, $postedBy = 'system')
    {
        if (!$issue) {
            return 0;
        }

        $settingsID = (int) ($issue->settingsID ?? 0);
        $issueId = (int) ($issue->id ?? 0);
        $forwardToUserId = (int) $forwardToUserId;

        if ($settingsID <= 0 || $issueId <= 0 || $forwardToUserId <= 0) {
            return 0;
        }

        $currentTaskId = $this->_ensure_support_issue_project_task($issue, (int) ($issue->assigned_employee_id ?? 0), $postedBy);
        if ($currentTaskId <= 0) {
            return 0;
        }

        $requiredColumns = ['forwarded_from', 'forwarded_to', 'forwarded_by', 'forwarded_note', 'forwarded_date'];
        foreach ($requiredColumns as $column) {
            if (!$this->db->field_exists($column, 'projects_task')) {
                return $this->_assign_support_issue_task($issue, $forwardToUserId, 'Support issue assigned without forwarded copy because task forwarding columns are missing.', $postedBy, true);
            }
        }

        $originalTask = $this->db
            ->select('*')
            ->from('projects_task')
            ->where('taskID', $currentTaskId)
            ->where('settingsID', $settingsID)
            ->limit(1)
            ->get()
            ->row();

        if (!$originalTask) {
            return 0;
        }

        $forwardToEmployee = $this->_mobile_support_employee_row($settingsID, $forwardToUserId);
        if (!$forwardToEmployee) {
            return 0;
        }

        $originalAssignee = $this->_mobile_support_employee_row($settingsID, (int) ($originalTask->assignedPerson ?? 0));
        $originalAssigneeName = $this->_mobile_support_employee_name($originalAssignee);
        if ($originalAssigneeName === '') {
            $originalAssigneeName = 'Current assignee';
        }

        $forwardedToName = $this->_mobile_support_employee_name($forwardToEmployee);
        $date = date('Y-m-d H:i:s');

        $forwardedTaskData = [
            'taskID' => 0,
            'task' => $this->_normalize_task_label(((string) ($originalTask->task ?? '')) . ' [Forwarded from: ' . $originalAssigneeName . ']'),
            'reportedDate' => $originalTask->reportedDate,
            'projectID' => $originalTask->projectID,
            'taskStat' => '1',
            'priority' => $originalTask->priority,
            'settingsID' => $settingsID,
            'assignedPerson' => $forwardToUserId,
            'added_by' => (string) ($originalTask->added_by ?? $postedBy),
            'forwarded_from' => (int) $originalTask->taskID,
            'forwarded_to' => $forwardToUserId,
            'forwarded_by' => $actorUserId > 0 ? $actorUserId : null,
            'forwarded_note' => $forwardNote,
            'forwarded_date' => $date,
        ];

        if ($this->db->field_exists('dueDate', 'projects_task')) {
            $forwardedTaskData['dueDate'] = $originalTask->dueDate;
        }
        if ($this->db->field_exists('attachment_link', 'projects_task')) {
            $forwardedTaskData['attachment_link'] = $originalTask->attachment_link;
        }

        $this->db->insert('projects_task', $forwardedTaskData);
        $forwardedTaskId = (int) $this->db->insert_id();
        if ($forwardedTaskId <= 0) {
            return 0;
        }

        $forwardedTaskNote = 'Task forwarded from ' . $originalAssigneeName . ' to ' . $forwardedToName . ' from support ticket ' . trim((string) ($issue->ticket_number ?? ('#' . $issueId))) . '.';
        if (trim((string) $forwardNote) !== '') {
            $forwardedTaskNote .= ' Note: ' . trim((string) $forwardNote);
        }

        $this->db->insert('projects_task_stat', [
            'taskID' => $forwardedTaskId,
            'note' => $forwardedTaskNote,
            'datePosted' => $date,
            'postedBy' => $postedBy !== '' ? $postedBy : 'system',
            'taskStat' => '1',
        ]);

        $this->db->insert('projects_task_stat', [
            'taskID' => (int) $originalTask->taskID,
            'note' => 'Task forwarded to ' . $forwardedToName . ' from support ticket ' . trim((string) ($issue->ticket_number ?? ('#' . $issueId))) . '.',
            'datePosted' => $date,
            'postedBy' => $postedBy !== '' ? $postedBy : 'system',
            'taskStat' => '1',
        ]);

        $this->db
            ->where('id', $issueId)
            ->where('settingsID', $settingsID)
            ->update('support_issues', [
                'task_id' => $forwardedTaskId,
                'assigned_employee_id' => $forwardToUserId,
                'status' => 'assigned',
            ]);

        return $forwardedTaskId;
    }

    private function _support_issue_payload($row, $currentUserId, $full = false)
    {
        $assignedId = (int) ($row->assigned_employee_id ?? 0);
        $statusRaw  = strtolower(trim((string) ($row->status ?? '')));
        $isClosed   = in_array($statusRaw, ['closed', 'resolved', 'done', 'completed'], true);

        $payload = [
            'id'              => (int) ($row->id ?? 0),
            'ticket_number'   => (string) ($row->ticket_number ?? ''),
            'title'           => (string) ($row->title ?? ''),
            'customer_name'   => (string) ($row->customer_name ?? ''),
            'customer_email'  => (string) ($row->customer_email ?? ''),
            'department_name' => (string) ($row->department_name ?? ''),
            'project_name'    => (string) ($row->projectDescription ?? ''),
            'category'        => (string) ($row->category ?? ''),
            'priority'        => (string) ($row->priority ?? 'medium'),
            'status'          => (string) ($row->status ?? 'open'),
            'is_closed'       => $isClosed,
            'is_unassigned'   => $assignedId <= 0 && !$isClosed,
            'assigned_to_me'  => $assignedId === (int) $currentUserId,
            'assigned_name'   => (string) ($row->assigned_employee_name ?? ''),
            'created_at'      => (string) ($row->created_at ?? ''),
            'created_label'   => $this->_format_support_timestamp($row->created_at ?? ''),
            'updated_label'   => $this->_format_support_timestamp($row->updated_at ?? ''),
        ];

        if ($full) {
            $payload['description']    = (string) ($row->description ?? '');
            $payload['reference_link'] = (string) ($row->reference_link ?? '');
            $payload['customer_phone'] = (string) ($row->customer_phone ?? '');
            $payload['task_id']        = (int) ($row->task_id ?? 0);
        }

        return $payload;
    }

    private function _format_support_timestamp($value)
    {
        $value = trim((string) $value);
        if ($value === '' || $value === '0000-00-00 00:00:00') {
            return '';
        }
        $ts = strtotime($value);
        if (!$ts) return $value;
        return date('M j, Y · g:i A', $ts);
    }

    // ── Notifications (mirrors Request controller bell + index) ───────────

    public function notifications()
    {
        if ($this->_method() === 'OPTIONS') {
            return mobile_json(['ok' => true]);
        }
        if ($this->_method() !== 'GET') {
            return mobile_json(['ok' => false, 'message' => 'Method not allowed.'], 405);
        }

        $claims = $this->_require_staff_claims();
        if ($claims === null) {
            return;
        }

        $settingsID = (string) ($claims['settingsID'] ?? '');
        $userId     = (int) ($claims['user_id'] ?? 0);
        $limitRaw   = (int) $this->input->get('limit');
        $limit      = ($limitRaw > 0 && $limitRaw <= 100) ? $limitRaw : 30;

        $this->load->model('Notification_model', 'notifications');

        $taskRows    = $this->notifications->get_pending($settingsID, $limit, $userId);
        $supportRows = $this->_mobile_fetch_support_notifications($settingsID, $userId, $limit);

        $merged = array_merge((array) $taskRows, (array) $supportRows);
        usort($merged, function ($a, $b) {
            $aSeen = isset($a->is_seen) ? (int) $a->is_seen : 0;
            $bSeen = isset($b->is_seen) ? (int) $b->is_seen : 0;
            if ($aSeen !== $bSeen) return $aSeen <=> $bSeen;
            $aTs = strtotime((string) ($a->created_at ?? '1970-01-01'));
            $bTs = strtotime((string) ($b->created_at ?? '1970-01-01'));
            return $bTs <=> $aTs;
        });
        $merged = array_slice($merged, 0, $limit);

        $items = array_map(function ($row) {
            return $this->_mobile_notification_payload($row);
        }, $merged);

        $taskUnseen    = (int) $this->notifications->count_pending($settingsID, $userId);
        $supportUnseen = $this->_mobile_count_support_notifications($settingsID, $userId);

        return mobile_json([
            'ok'           => true,
            'count'        => $taskUnseen + $supportUnseen,
            'unseen_total' => $taskUnseen + $supportUnseen,
            'notifications' => $items,
        ]);
    }

    public function notificationsMarkSeen()
    {
        if ($this->_method() === 'OPTIONS') {
            return mobile_json(['ok' => true]);
        }
        if ($this->_method() !== 'POST') {
            return mobile_json(['ok' => false, 'message' => 'Method not allowed.'], 405);
        }

        $claims = $this->_require_staff_claims();
        if ($claims === null) {
            return;
        }

        $settingsID = (string) ($claims['settingsID'] ?? '');
        $userId     = (int) ($claims['user_id'] ?? 0);

        $this->load->model('Notification_model', 'notifications');
        $this->notifications->mark_seen($settingsID, $userId);

        if ($this->db->table_exists('support_notifications')) {
            $this->db
                ->where('settingsID', (int) $settingsID)
                ->where('user_id', $userId)
                ->where('is_read', 0)
                ->update('support_notifications', [
                    'is_read' => 1,
                    'read_at' => date('Y-m-d H:i:s'),
                ]);
        }

        return mobile_json(['ok' => true]);
    }

    // ── Profile ─────────────────────────────────────────────────────────────

    public function profile()
    {
        if ($this->_method() === 'OPTIONS') {
            return mobile_json(['ok' => true]);
        }
        if ($this->_method() !== 'GET') {
            return mobile_json(['ok' => false, 'message' => 'Method not allowed.'], 405);
        }

        $claims = $this->_require_staff_claims();
        if ($claims === null) {
            return;
        }

        $username = trim((string) ($claims['username'] ?? ''));
        if ($username === '') {
            return mobile_json(['ok' => false, 'message' => 'Username missing from session.'], 400);
        }

        $user = $this->db->where('username', $username)->get('users')->row();
        if (!$user) {
            return mobile_json(['ok' => false, 'message' => 'Profile not found.'], 404);
        }

        return mobile_json([
            'ok'      => true,
            'profile' => $this->_profile_payload($user),
        ]);
    }

    public function uploadAvatar()
    {
        if ($this->_method() === 'OPTIONS') {
            return mobile_json(['ok' => true]);
        }
        if ($this->_method() !== 'POST') {
            return mobile_json(['ok' => false, 'message' => 'Method not allowed.'], 405);
        }

        $claims = $this->_require_staff_claims();
        if ($claims === null) {
            return;
        }

        $username = trim((string) ($claims['username'] ?? ''));
        if ($username === '') {
            return mobile_json(['ok' => false, 'message' => 'Username missing from session.'], 400);
        }

        if (!isset($_FILES['avatar']) || !is_uploaded_file($_FILES['avatar']['tmp_name'] ?? '')) {
            return mobile_json(['ok' => false, 'message' => 'No file uploaded. Use field name "avatar".'], 422);
        }

        $config = [
            'upload_path'      => FCPATH . 'upload/profile/',
            'allowed_types'    => 'jpg|jpeg|png|gif',
            'max_size'         => 4096,
            'file_ext_tolower' => TRUE,
            'encrypt_name'     => TRUE,
            'remove_spaces'    => TRUE,
        ];

        if (!is_dir($config['upload_path'])) {
            if (!@mkdir($config['upload_path'], 0755, TRUE) && !is_dir($config['upload_path'])) {
                return mobile_json(['ok' => false, 'message' => 'Unable to prepare upload directory.'], 500);
            }
        }

        $this->load->library('upload', $config);
        $this->upload->initialize($config);

        if (!$this->upload->do_upload('avatar')) {
            return mobile_json([
                'ok'      => false,
                'message' => strip_tags($this->upload->display_errors('', '')),
            ], 422);
        }

        $uploaded = $this->upload->data();
        $filename = (string) ($uploaded['file_name'] ?? '');

        $existing = $this->db->select('avatar')->from('users')->where('username', $username)->get()->row();
        if ($existing && !empty($existing->avatar) && strtolower((string) $existing->avatar) !== 'avatar.png') {
            $old = $config['upload_path'] . $existing->avatar;
            if (is_file($old)) {
                @unlink($old);
            }
        }

        $this->db->where('username', $username)->update('users', ['avatar' => $filename]);
        if ($this->db->table_exists('o_users')) {
            $this->db->where('username', $username)->update('o_users', ['avatar' => $filename]);
        }

        $user = $this->db->where('username', $username)->get('users')->row();
        $avatarUrl = mobile_avatar_url($filename);

        return mobile_json([
            'ok'         => true,
            'message'    => 'Profile picture updated successfully.',
            'avatar'     => $filename,
            'avatar_url' => $avatarUrl,
            'profile'    => $user ? $this->_profile_payload($user) : null,
        ]);
    }

    public function updateProfile()
    {
        if ($this->_method() === 'OPTIONS') {
            return mobile_json(['ok' => true]);
        }
        if ($this->_method() !== 'POST') {
            return mobile_json(['ok' => false, 'message' => 'Method not allowed.'], 405);
        }

        $claims = $this->_require_staff_claims();
        if ($claims === null) return;

        $username   = (string) ($claims['username'] ?? '');
        $settingsID = (int) ($claims['settingsID'] ?? 0);

        $payload = $this->_read_payload();

        $update = [];
        $allowed = [
            'empMobile',
            'empEmail',
            'BirthDate',
            'BirthPlace',
            'bloodType',
            'MaritalStatus',
            'height',
            'weight',
            'resStreet',
            'resVillage',
            'resBarangay',
            'resCity',
            'resProvince',
        ];
        foreach ($allowed as $field) {
            if (array_key_exists($field, $payload)) {
                $update[$field] = trim((string) $payload[$field]);
            }
        }

        if (empty($update)) {
            return mobile_json(['ok' => false, 'message' => 'No fields to update.'], 422);
        }

        $this->db->where('username', $username);
        $this->db->where('settingsID', $settingsID);
        $this->db->update('users', $update);

        $user = $this->db->where('username', $username)->get('users')->row();
        return mobile_json([
            'ok'      => true,
            'message' => 'Profile updated.',
            'profile' => $user ? $this->_profile_payload($user) : null,
        ]);
    }

    // ── My DTR ──────────────────────────────────────────────────────────────

    public function myDTR()
    {
        if ($this->_method() === 'OPTIONS') {
            return mobile_json(['ok' => true]);
        }
        if ($this->_method() !== 'GET') {
            return mobile_json(['ok' => false, 'message' => 'Method not allowed.'], 405);
        }

        $claims = $this->_require_staff_claims();
        if ($claims === null) return;

        $username   = (string) ($claims['username'] ?? '');
        $settingsID = (int) ($claims['settingsID'] ?? 0);

        $monthInput = (int) $this->input->get('month');
        $yearInput  = (int) $this->input->get('year');
        $month = ($monthInput >= 1 && $monthInput <= 12) ? $monthInput : (int) date('n');
        $year  = ($yearInput >= 2000 && $yearInput <= 2100) ? $yearInput : (int) date('Y');

        $dataRows = [];
        $monthTotalSeconds = 0;
        $presentDays = 0;
        $absentDays = 0;
        $pendingDays = 0;

        if ($username !== '') {
            $startDate = sprintf('%04d-%02d-01', $year, $month);
            $endDate = date('Y-m-t', strtotime($startDate));

            $raw = $this->CashModel->attendanceListByEmployeeRange($settingsID, $username, $startDate, $endDate);
            $aggregated = !empty($raw) ? $this->_aggregateDtrForUser($raw) : [];

            $byDate = [];
            foreach ($aggregated as $item) {
                $ts = strtotime((string) $item->logDate);
                if ($ts !== false) {
                    $item->logDate = date('Y-m-d', $ts);
                }
                $byDate[$item->logDate] = $item;
            }

            $daysInMonth = (int) date('t', strtotime($startDate));
            $today = date('Y-m-d');
            $todayYear = (int) date('Y', strtotime($today));
            $todayMonth = (int) date('n', strtotime($today));
            $todayDay = (int) date('j', strtotime($today));
            $lastDay = $daysInMonth;
            if ($year === $todayYear && $month === $todayMonth) {
                $lastDay = min($daysInMonth, $todayDay);
            }

            for ($day = 1; $day <= $lastDay; $day++) {
                $dateKey = sprintf('%04d-%02d-%02d', $year, $month, $day);
                if (isset($byDate[$dateKey])) {
                    $entry = $byDate[$dateKey];
                } else {
                    $entry = (object) [
                        'logDate'       => $dateKey,
                        'intervals'     => [],
                        'am_intervals'  => [],
                        'pm_intervals'  => [],
                        'total_seconds' => 0,
                        'total_label'   => $this->_formatSeconds(0),
                    ];
                }
                $isAbsent = empty($entry->am_intervals) && empty($entry->pm_intervals);
                $hasOpen = false;
                foreach (['am_intervals', 'pm_intervals'] as $bucket) {
                    if (!empty($entry->{$bucket}) && is_array($entry->{$bucket})) {
                        foreach ($entry->{$bucket} as $intv) {
                            if (!empty($intv['open'])) {
                                $hasOpen = true;
                                break 2;
                            }
                        }
                    }
                }
                $isPending = !$isAbsent && $hasOpen;

                $dataRows[] = [
                    'date'          => $dateKey,
                    'am_breakdown'  => $this->_dtr_interval_labels($entry->am_intervals ?? []),
                    'pm_breakdown'  => $this->_dtr_interval_labels($entry->pm_intervals ?? []),
                    'total_hours'   => $entry->total_label ?? $this->_formatSeconds(0),
                    'total_seconds' => (int) ($entry->total_seconds ?? 0),
                    'status'        => $isAbsent ? 'Absent' : ($isPending ? 'Pending' : 'Present'),
                ];

                if (!empty($entry->total_seconds)) {
                    $monthTotalSeconds += (int) $entry->total_seconds;
                }
                if ($isAbsent) {
                    $absentDays++;
                } elseif ($isPending) {
                    $pendingDays++;
                } else {
                    $presentDays++;
                }
            }
        }

        return mobile_json([
            'ok'                  => true,
            'month'               => $month,
            'year'                => $year,
            'rows'                => $dataRows,
            'month_total_seconds' => $monthTotalSeconds,
            'month_total_label'   => $this->_formatSeconds($monthTotalSeconds),
            'present_days'        => $presentDays,
            'absent_days'         => $absentDays,
            'pending_days'        => $pendingDays,
        ]);
    }

    private function _dtr_interval_labels($intervals)
    {
        $out = [];
        if (empty($intervals) || !is_array($intervals)) {
            return $out;
        }
        foreach ($intervals as $intv) {
            $out[] = $intv['label'] ?? '';
        }
        return $out;
    }

    // ── Calendar ────────────────────────────────────────────────────────────

    public function calendarEvents()
    {
        if ($this->_method() === 'OPTIONS') {
            return mobile_json(['ok' => true]);
        }
        if ($this->_method() !== 'GET') {
            return mobile_json(['ok' => false, 'message' => 'Method not allowed.'], 405);
        }

        $claims = $this->_require_member_claims();
        if ($claims === null) return;

        $this->_ensure_calendar_schema();

        $userId     = (int) ($claims['user_id'] ?? 0);
        $settingsID = (int) ($claims['settingsID'] ?? 0);
        $from = trim((string) $this->input->get('from'));
        $to   = trim((string) $this->input->get('to'));

        $this->db->from('calendar_events')
            ->where('settingsID', $settingsID)
            ->where('status', 'active')
            ->group_start()
            ->where('user_id', $userId)
            ->or_where('is_public', 1)
            ->group_end();

        if ($from !== '') {
            $this->db->where('end_date >=', $from . ' 00:00:00');
        }
        if ($to !== '') {
            $this->db->where('start_date <=', $to . ' 23:59:59');
        }

        $rows = $this->db->order_by('start_date', 'ASC')->get()->result();

        $events = [];
        foreach ($rows as $row) {
            $events[] = $this->_calendar_event_payload($row, $userId);
        }

        return mobile_json([
            'ok' => true,
            'events' => $events,
        ]);
    }

    public function calendarEventCreate()
    {
        if ($this->_method() === 'OPTIONS') {
            return mobile_json(['ok' => true]);
        }
        if ($this->_method() !== 'POST') {
            return mobile_json(['ok' => false, 'message' => 'Method not allowed.'], 405);
        }

        $claims = $this->_require_member_claims();
        if ($claims === null) return;

        $this->_ensure_calendar_schema();

        $userId     = (int) ($claims['user_id'] ?? 0);
        $settingsID = (int) ($claims['settingsID'] ?? 0);

        $payload = $this->_calendar_build_payload();
        if (!$payload['ok']) {
            return mobile_json(['ok' => false, 'message' => $payload['message']], 422);
        }

        $data = array_merge($payload['data'], [
            'user_id'    => $userId,
            'settingsID' => $settingsID,
            'status'     => 'active',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        $this->db->insert('calendar_events', $data);
        $eventId = (int) $this->db->insert_id();

        if ($eventId <= 0) {
            $error = $this->db->error();
            return mobile_json([
                'ok' => false,
                'message' => !empty($error['message']) ? $error['message'] : 'Failed to create event.',
            ], 500);
        }

        $row = $this->db->where('id', $eventId)->get('calendar_events')->row();
        return mobile_json([
            'ok' => true,
            'message' => 'Event created.',
            'event' => $row ? $this->_calendar_event_payload($row, $userId) : null,
        ]);
    }

    public function calendarEventDetail($id)
    {
        if ($this->_method() === 'OPTIONS') {
            return mobile_json(['ok' => true]);
        }
        if ($this->_method() !== 'GET') {
            return mobile_json(['ok' => false, 'message' => 'Method not allowed.'], 405);
        }

        $claims = $this->_require_member_claims();
        if ($claims === null) return;

        $this->_ensure_calendar_schema();

        $userId     = (int) ($claims['user_id'] ?? 0);
        $settingsID = (int) ($claims['settingsID'] ?? 0);
        $id         = (int) $id;

        $row = $this->db->from('calendar_events')
            ->where('id', $id)
            ->where('settingsID', $settingsID)
            ->where('status', 'active')
            ->group_start()
            ->where('user_id', $userId)
            ->or_where('is_public', 1)
            ->group_end()
            ->get()->row();

        if (!$row) {
            return mobile_json(['ok' => false, 'message' => 'Event not found.'], 404);
        }

        return mobile_json([
            'ok' => true,
            'event' => $this->_calendar_event_payload($row, $userId),
        ]);
    }

    public function calendarEventUpdate($id)
    {
        if ($this->_method() === 'OPTIONS') {
            return mobile_json(['ok' => true]);
        }
        if ($this->_method() !== 'POST') {
            return mobile_json(['ok' => false, 'message' => 'Method not allowed.'], 405);
        }

        $claims = $this->_require_member_claims();
        if ($claims === null) return;

        $this->_ensure_calendar_schema();

        $userId     = (int) ($claims['user_id'] ?? 0);
        $settingsID = (int) ($claims['settingsID'] ?? 0);
        $id         = (int) $id;

        $existing = $this->db->from('calendar_events')
            ->where('id', $id)
            ->where('user_id', $userId)
            ->where('settingsID', $settingsID)
            ->get()->row();

        if (!$existing) {
            return mobile_json(['ok' => false, 'message' => 'Event not found or you cannot edit it.'], 404);
        }

        $payload = $this->_calendar_build_payload();
        if (!$payload['ok']) {
            return mobile_json(['ok' => false, 'message' => $payload['message']], 422);
        }

        $update = array_merge($payload['data'], ['updated_at' => date('Y-m-d H:i:s')]);
        $this->db->where('id', $id)
            ->where('user_id', $userId)
            ->where('settingsID', $settingsID)
            ->update('calendar_events', $update);

        $row = $this->db->where('id', $id)->get('calendar_events')->row();
        return mobile_json([
            'ok' => true,
            'message' => 'Event updated.',
            'event' => $row ? $this->_calendar_event_payload($row, $userId) : null,
        ]);
    }

    public function calendarEventDelete($id)
    {
        if ($this->_method() === 'OPTIONS') {
            return mobile_json(['ok' => true]);
        }
        if ($this->_method() !== 'POST') {
            return mobile_json(['ok' => false, 'message' => 'Method not allowed.'], 405);
        }

        $claims = $this->_require_member_claims();
        if ($claims === null) return;

        $this->_ensure_calendar_schema();

        $userId     = (int) ($claims['user_id'] ?? 0);
        $settingsID = (int) ($claims['settingsID'] ?? 0);
        $id         = (int) $id;

        $existing = $this->db->from('calendar_events')
            ->where('id', $id)
            ->where('user_id', $userId)
            ->where('settingsID', $settingsID)
            ->get()->row();

        if (!$existing) {
            return mobile_json(['ok' => false, 'message' => 'Event not found or you cannot delete it.'], 404);
        }

        $this->db->where('id', $id)
            ->where('user_id', $userId)
            ->where('settingsID', $settingsID)
            ->delete('calendar_events');

        return mobile_json(['ok' => true, 'message' => 'Event deleted.']);
    }

    // ── Annual Goals (read-only for staff) ─────────────────────────────────

    public function annualGoals()
    {
        if ($this->_method() === 'OPTIONS') {
            return mobile_json(['ok' => true]);
        }
        if ($this->_method() !== 'GET') {
            return mobile_json(['ok' => false, 'message' => 'Method not allowed.'], 405);
        }

        $claims = $this->_require_staff_claims();
        if ($claims === null) return;

        $settingsID = (int) ($claims['settingsID'] ?? 0);

        $goals = $this->CashModel->getAnnualGoals($settingsID);
        $payload = [];
        foreach ((array) $goals as $goal) {
            $year = (int) ($goal->goalYear ?? 0);
            $progress = $this->CashModel->getYearlyProgress($settingsID, $year);
            $payload[] = $this->_annual_goal_payload($goal, $progress);
        }

        $currentYear = (int) date('Y');
        $currentGoal = $this->CashModel->getAnnualGoalByYear($settingsID, $currentYear);
        $currentProgress = $this->CashModel->getYearlyProgress($settingsID, $currentYear);

        return mobile_json([
            'ok' => true,
            'current_year' => $currentYear,
            'current' => $currentGoal
                ? $this->_annual_goal_payload($currentGoal, $currentProgress)
                : null,
            'goals' => $payload,
        ]);
    }

    public function annualGoalDetail($year)
    {
        if ($this->_method() === 'OPTIONS') {
            return mobile_json(['ok' => true]);
        }
        if ($this->_method() !== 'GET') {
            return mobile_json(['ok' => false, 'message' => 'Method not allowed.'], 405);
        }

        $claims = $this->_require_staff_claims();
        if ($claims === null) return;

        $settingsID = (int) ($claims['settingsID'] ?? 0);
        $year = (int) $year;
        if ($year <= 0) $year = (int) date('Y');

        $goal = $this->CashModel->getAnnualGoalByYear($settingsID, $year);
        $progress = $this->CashModel->getYearlyProgress($settingsID, $year);

        $monthly = [];
        for ($m = 1; $m <= 12; $m++) {
            $monthly[] = [
                'month' => $m,
                'label' => date('M', mktime(0, 0, 0, $m, 1)),
                'clients' => (int) $this->db
                    ->where('settingsID', $settingsID)
                    ->where('MONTH(created_at)', $m)
                    ->where('YEAR(created_at)', $year)
                    ->where("COALESCE(ClientStat, '') !=", 'Deleted')
                    ->count_all_results('customers'),
                'income' => (float) (($this->db
                    ->select('SUM(AmountPaid) as total')
                    ->where('settingsID', $settingsID)
                    ->where('MONTH(PDate)', $m)
                    ->where('YEAR(PDate)', $year)
                    ->where('ORStat', 'valid')
                    ->get('payments')
                    ->row()->total) ?? 0),
            ];
        }

        return mobile_json([
            'ok' => true,
            'year' => $year,
            'goal' => $goal ? $this->_annual_goal_payload($goal, $progress) : null,
            'monthly' => $monthly,
        ]);
    }

    // ── Support Dashboard ──────────────────────────────────────────────────

    public function supportDashboard()
    {
        if ($this->_method() === 'OPTIONS') {
            return mobile_json(['ok' => true]);
        }
        if ($this->_method() !== 'GET') {
            return mobile_json(['ok' => false, 'message' => 'Method not allowed.'], 405);
        }

        $claims = $this->_require_staff_claims();
        if ($claims === null) return;

        $settingsID = (int) ($claims['settingsID'] ?? 0);
        $userId     = (int) ($claims['user_id'] ?? 0);

        if (!$this->db->table_exists('support_issues')) {
            return mobile_json($this->_empty_support_dashboard_payload());
        }

        $accessibleIssueIds = $this->_mobile_accessible_issue_ids($settingsID, $userId);
        if (empty($accessibleIssueIds)) {
            $accessibleIssueIds = [0];
        }

        $hasResolutionDate = $this->db->field_exists('resolution_date', 'support_issues');
        $hasSupportDepartments = $this->db->table_exists('support_departments');

        $closedStatusSql = "LOWER(TRIM(COALESCE(status, ''))) IN ('closed','resolved','done','completed')";
        $openStatusSql   = "LOWER(TRIM(COALESCE(status, ''))) NOT IN ('closed','resolved','done','completed')";

        $countTotal = (int) $this->db->from('support_issues')
            ->where('settingsID', $settingsID)
            ->where_in('id', $accessibleIssueIds)
            ->count_all_results();

        $countOpen = (int) $this->db->from('support_issues')
            ->where('settingsID', $settingsID)
            ->where_in('id', $accessibleIssueIds)
            ->where($openStatusSql, null, false)
            ->count_all_results();

        $countClosed = (int) $this->db->from('support_issues')
            ->where('settingsID', $settingsID)
            ->where_in('id', $accessibleIssueIds)
            ->where($closedStatusSql, null, false)
            ->count_all_results();

        $countUnassigned = (int) $this->db->from('support_issues')
            ->where('settingsID', $settingsID)
            ->where_in('id', $accessibleIssueIds)
            ->group_start()
            ->where('assigned_employee_id IS NULL', null, false)
            ->or_where('assigned_employee_id', 0)
            ->group_end()
            ->where($openStatusSql, null, false)
            ->count_all_results();

        $countAwaitingReply = (int) $this->db->from('support_issues')
            ->where('settingsID', $settingsID)
            ->where_in('id', $accessibleIssueIds)
            ->where("LOWER(TRIM(COALESCE(status, ''))) = 'awaiting_reply'", null, false)
            ->count_all_results();

        $monthStart = date('Y-m-01 00:00:00');
        $monthCreated = (int) $this->db->from('support_issues')
            ->where('settingsID', $settingsID)
            ->where_in('id', $accessibleIssueIds)
            ->where('created_at >=', $monthStart)
            ->count_all_results();

        $monthClosed = 0;
        $avgResolutionHours = 0.0;
        if ($hasResolutionDate) {
            $monthClosed = (int) $this->db->from('support_issues')
                ->where('settingsID', $settingsID)
                ->where_in('id', $accessibleIssueIds)
                ->where('resolution_date >=', $monthStart)
                ->where($closedStatusSql, null, false)
                ->count_all_results();

            $avgRow = $this->db->select("AVG(TIMESTAMPDIFF(HOUR, created_at, resolution_date)) AS avg_hours", false)
                ->from('support_issues')
                ->where('settingsID', $settingsID)
                ->where_in('id', $accessibleIssueIds)
                ->where($closedStatusSql, null, false)
                ->where('resolution_date IS NOT NULL', null, false)
                ->get()->row();
            $avgResolutionHours = $avgRow && isset($avgRow->avg_hours) ? (float) $avgRow->avg_hours : 0.0;
        }

        $byPriority = $this->db->select("LOWER(TRIM(COALESCE(priority, 'medium'))) AS priority_key, COUNT(*) AS ticket_count", false)
            ->from('support_issues')
            ->where('settingsID', $settingsID)
            ->where_in('id', $accessibleIssueIds)
            ->group_by('priority_key')
            ->order_by('ticket_count', 'DESC')
            ->get()->result();

        $byStatus = $this->db->select("LOWER(TRIM(COALESCE(status, 'open'))) AS status_key, COUNT(*) AS ticket_count", false)
            ->from('support_issues')
            ->where('settingsID', $settingsID)
            ->where_in('id', $accessibleIssueIds)
            ->group_by('status_key')
            ->order_by('ticket_count', 'DESC')
            ->get()->result();

        $byDepartment = [];
        if ($hasSupportDepartments) {
            $byDepartment = $this->db->select("d.id, d.department_name, COUNT(si.id) AS ticket_count, SUM(CASE WHEN {$openStatusSql} THEN 1 ELSE 0 END) AS open_count", false)
                ->from('support_issues si')
                ->join('support_departments d', 'd.id = si.department_id', 'left')
                ->where('si.settingsID', $settingsID)
                ->where_in('si.id', $accessibleIssueIds)
                ->group_by('d.id, d.department_name')
                ->order_by('ticket_count', 'DESC')
                ->limit(8)
                ->get()->result();
        }

        $trendDays = 14;
        $trend = [];
        for ($i = $trendDays - 1; $i >= 0; $i--) {
            $day = date('Y-m-d', strtotime('-' . $i . ' days'));
            $createdCount = (int) $this->db->from('support_issues')
                ->where('settingsID', $settingsID)
                ->where_in('id', $accessibleIssueIds)
                ->where('DATE(created_at)', $day)
                ->count_all_results();
            $closedCount = 0;
            if ($hasResolutionDate) {
                $closedCount = (int) $this->db->from('support_issues')
                    ->where('settingsID', $settingsID)
                    ->where_in('id', $accessibleIssueIds)
                    ->where('DATE(resolution_date)', $day)
                    ->where($closedStatusSql, null, false)
                    ->count_all_results();
            }
            $trend[] = [
                'date' => $day,
                'label' => date('M j', strtotime($day)),
                'created' => $createdCount,
                'closed' => $closedCount,
            ];
        }

        $this->db->select('si.id, si.ticket_number, si.title, si.status, si.priority, si.created_at, CONCAT(u.fName, " ", u.lName) AS assigned_employee_name');
        if ($hasSupportDepartments) {
            $this->db->select('d.department_name');
        }
        $this->db->from('support_issues si');
        if ($hasSupportDepartments) {
            $this->db->join('support_departments d', 'd.id = si.department_id', 'left');
        }
        $this->db->join('users u', 'u.user_id = si.assigned_employee_id', 'left');
        $recentRows = $this->db
            ->where('si.settingsID', $settingsID)
            ->where_in('si.id', $accessibleIssueIds)
            ->order_by('si.created_at', 'DESC')
            ->limit(10)
            ->get()->result();

        $this->db->select('si.id, si.ticket_number, si.title, si.status, si.priority, si.created_at, CONCAT(u.fName, " ", u.lName) AS assigned_employee_name');
        if ($hasSupportDepartments) {
            $this->db->select('d.department_name');
        }
        $this->db->from('support_issues si');
        if ($hasSupportDepartments) {
            $this->db->join('support_departments d', 'd.id = si.department_id', 'left');
        }
        $this->db->join('users u', 'u.user_id = si.assigned_employee_id', 'left');
        $oldestRows = $this->db
            ->where('si.settingsID', $settingsID)
            ->where_in('si.id', $accessibleIssueIds)
            ->where($openStatusSql, null, false)
            ->order_by('si.created_at', 'ASC')
            ->limit(8)
            ->get()->result();

        return mobile_json([
            'ok' => true,
            'totals' => [
                'total' => $countTotal,
                'open' => $countOpen,
                'closed' => $countClosed,
                'unassigned' => $countUnassigned,
                'awaiting_reply' => $countAwaitingReply,
            ],
            'this_month' => [
                'created' => $monthCreated,
                'closed' => $monthClosed,
            ],
            'avg_resolution_hours' => round($avgResolutionHours, 1),
            'by_priority' => array_map(function ($r) {
                return [
                    'key' => (string) ($r->priority_key ?? ''),
                    'count' => (int) ($r->ticket_count ?? 0),
                ];
            }, $byPriority),
            'by_status' => array_map(function ($r) {
                return [
                    'key' => (string) ($r->status_key ?? ''),
                    'count' => (int) ($r->ticket_count ?? 0),
                ];
            }, $byStatus),
            'by_department' => array_map(function ($r) {
                return [
                    'id' => (int) ($r->id ?? 0),
                    'name' => trim((string) ($r->department_name ?? 'Unassigned')) ?: 'Unassigned',
                    'total' => (int) ($r->ticket_count ?? 0),
                    'open' => (int) ($r->open_count ?? 0),
                ];
            }, $byDepartment),
            'trend' => $trend,
            'recent' => array_map([$this, '_support_dashboard_ticket_payload'], $recentRows),
            'oldest_open' => array_map([$this, '_support_dashboard_ticket_payload'], $oldestRows),
        ]);
    }

    // ── Helpers for new endpoints ──────────────────────────────────────────

    private function _calendar_event_payload($row, $currentUserId)
    {
        $isOwn = (int) ($row->user_id ?? 0) === (int) $currentUserId;
        return [
            'id'           => (int) ($row->id ?? 0),
            'title'        => (string) ($row->title ?? ''),
            'description'  => (string) ($row->description ?? ''),
            'notes'        => (string) ($row->notes ?? ''),
            'start'        => (string) ($row->start_date ?? ''),
            'end'          => (string) ($row->end_date ?? ''),
            'all_day'      => (int) ($row->all_day ?? 0) === 1,
            'event_type'   => (string) ($row->event_type ?? 'default'),
            'color'        => (string) ($row->color ?? '#3788d8'),
            'location'     => (string) ($row->location ?? ''),
            'is_public'    => (int) ($row->is_public ?? 0) === 1,
            'reminder_email_enabled' => (int) ($row->reminder_email_enabled ?? 0) === 1,
            'reminder_email' => (string) ($row->reminder_email ?? ''),
            'can_edit'     => $isOwn,
            'can_delete'   => $isOwn,
            'own'          => $isOwn,
        ];
    }

    private function _calendar_build_payload()
    {
        $payload = $this->_read_payload();
        $title       = trim((string) ($payload['title'] ?? ''));
        $description = trim((string) ($payload['description'] ?? ''));
        $notes       = trim((string) ($payload['notes'] ?? ''));
        $allDay      = !empty($payload['all_day']) ? 1 : 0;
        $eventType   = trim((string) ($payload['event_type'] ?? ''));
        $color       = trim((string) ($payload['color'] ?? ''));
        $location    = trim((string) ($payload['location'] ?? ''));
        $isPublic    = !empty($payload['is_public']) ? 1 : 0;
        $reminderEnabled = !empty($payload['reminder_email_enabled']) ? 1 : 0;
        $reminderEmail = trim((string) ($payload['reminder_email'] ?? ''));

        $start = $this->_calendar_normalize_datetime((string) ($payload['start'] ?? $payload['start_date'] ?? ''), $allDay ? '00:00:00' : '09:00:00');
        $end   = $this->_calendar_normalize_datetime((string) ($payload['end'] ?? $payload['end_date'] ?? ''), $allDay ? '23:59:59' : '10:00:00');

        if ($title === '' || $start === null || $end === null) {
            return ['ok' => false, 'message' => 'Title, start, and end are required.'];
        }
        if (strtotime($end) < strtotime($start)) {
            return ['ok' => false, 'message' => 'End must be after start.'];
        }

        return [
            'ok' => true,
            'data' => [
                'title' => $title,
                'description' => $description,
                'notes' => $notes,
                'start_date' => $start,
                'end_date' => $end,
                'all_day' => $allDay,
                'event_type' => $eventType !== '' ? $eventType : 'default',
                'color' => $color !== '' ? $color : '#3788d8',
                'location' => $location,
                'reminder_time' => 1440,
                'reminder_email_enabled' => $reminderEnabled,
                'reminder_email' => $reminderEmail,
                'is_public' => $isPublic,
            ],
        ];
    }

    private function _calendar_normalize_datetime($value, $defaultTime)
    {
        $value = trim((string) $value);
        if ($value === '') return null;
        $value = str_replace('T', ' ', $value);
        $ts = strtotime($value);
        if ($ts === false) return null;
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            return date('Y-m-d', $ts) . ' ' . $defaultTime;
        }
        return date('Y-m-d H:i:s', $ts);
    }

    private function _ensure_calendar_schema()
    {
        if (!$this->db->table_exists('calendar_events')) {
            $this->db->query("
                CREATE TABLE `calendar_events` (
                    `id` int unsigned NOT NULL AUTO_INCREMENT,
                    `title` varchar(255) NOT NULL,
                    `description` text DEFAULT NULL,
                    `notes` text DEFAULT NULL,
                    `start_date` datetime NOT NULL,
                    `end_date` datetime NOT NULL,
                    `all_day` tinyint(1) NOT NULL DEFAULT 0,
                    `event_type` varchar(100) NOT NULL DEFAULT 'default',
                    `color` varchar(20) NOT NULL DEFAULT '#3788d8',
                    `user_id` int NOT NULL,
                    `settingsID` int NOT NULL DEFAULT 0,
                    `location` varchar(255) DEFAULT NULL,
                    `reminder_time` int NOT NULL DEFAULT 1440,
                    `reminder_email_enabled` tinyint(1) NOT NULL DEFAULT 1,
                    `reminder_email` varchar(191) DEFAULT NULL,
                    `reminder_sent_at` datetime DEFAULT NULL,
                    `is_public` tinyint(1) NOT NULL DEFAULT 0,
                    `status` varchar(20) NOT NULL DEFAULT 'active',
                    `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    PRIMARY KEY (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
            ");
        }
    }

    private function _annual_goal_payload($goal, $progress)
    {
        $targetClients = (int) ($goal->targetClients ?? 0);
        $targetIncome  = (float) ($goal->targetIncome ?? 0);
        $actualClients = (int) ($progress['actualClients'] ?? 0);
        $actualIncome  = (float) ($progress['actualIncome'] ?? 0);

        $clientsPct = $targetClients > 0 ? min(100, ($actualClients / $targetClients) * 100) : 0;
        $incomePct  = $targetIncome > 0 ? min(100, ($actualIncome / $targetIncome) * 100) : 0;

        return [
            'goal_id' => (int) ($goal->goalID ?? 0),
            'year' => (int) ($goal->goalYear ?? 0),
            'target_clients' => $targetClients,
            'target_income' => $targetIncome,
            'actual_clients' => $actualClients,
            'actual_income' => $actualIncome,
            'clients_progress_pct' => round($clientsPct, 1),
            'income_progress_pct' => round($incomePct, 1),
            'notes' => (string) ($goal->notes ?? ''),
            'created_by' => (string) ($goal->createdBy ?? ''),
        ];
    }

    private function _support_dashboard_ticket_payload($row)
    {
        $createdAt = (string) ($row->created_at ?? '');
        $createdLabel = '';
        if ($createdAt !== '') {
            $ts = strtotime($createdAt);
            $createdLabel = $ts ? date('M j, Y · g:i A', $ts) : $createdAt;
        }
        return [
            'id' => (int) ($row->id ?? 0),
            'ticket_number' => (string) ($row->ticket_number ?? ''),
            'title' => (string) ($row->title ?? ''),
            'status' => (string) ($row->status ?? ''),
            'priority' => (string) ($row->priority ?? ''),
            'created_at' => $createdAt,
            'created_label' => $createdLabel,
            'department' => trim((string) ($row->department_name ?? '')),
            'assignee' => trim((string) ($row->assigned_employee_name ?? '')),
        ];
    }

    private function _empty_support_dashboard_payload()
    {
        return [
            'ok' => true,
            'totals' => [
                'total' => 0,
                'open' => 0,
                'closed' => 0,
                'unassigned' => 0,
                'awaiting_reply' => 0,
            ],
            'this_month' => [
                'created' => 0,
                'closed' => 0,
            ],
            'avg_resolution_hours' => 0.0,
            'by_priority' => [],
            'by_status' => [],
            'by_department' => [],
            'trend' => [],
            'recent' => [],
            'oldest_open' => [],
        ];
    }

    private function _profile_payload($user)
    {
        $first = trim((string) ($user->fName ?? ''));
        $mid   = trim((string) ($user->mName ?? ''));
        $last  = trim((string) ($user->lName ?? ''));
        $full  = trim($first . ' ' . ($mid !== '' ? $mid[0] . '. ' : '') . $last);

        $addressParts = array_filter(array_map('trim', [
            (string) ($user->resStreet ?? ''),
            (string) ($user->resVillage ?? ''),
            (string) ($user->resBarangay ?? ''),
        ]));
        $regionParts = array_filter(array_map('trim', [
            (string) ($user->resCity ?? ''),
            (string) ($user->resProvince ?? ''),
        ]));
        $formattedAddress = '';
        if (!empty($addressParts)) {
            $formattedAddress .= implode(' ', $addressParts);
        }
        if (!empty($regionParts)) {
            if ($formattedAddress !== '') {
                $formattedAddress .= ', ';
            }
            $formattedAddress .= implode(', ', $regionParts);
        }

        return [
            'username'        => (string) ($user->username ?? ''),
            'full_name'       => $full !== '' ? $full : (string) ($user->username ?? ''),
            'first_name'      => $first,
            'middle_name'     => $mid,
            'last_name'       => $last,
            'email'           => (string) ($user->email ?? ''),
            'avatar'          => (string) ($user->avatar ?? ''),
            'avatar_url'      => mobile_avatar_url((string) ($user->avatar ?? '')),

            'employee_no'     => (string) ($user->IDNumber ?? ''),
            'position'        => (string) ($user->position ?? ''),
            'department'      => (string) ($user->Department ?? ''),
            'date_hired'      => (string) ($user->dateHired ?? ''),
            'tin_no'          => (string) ($user->tinNo ?? ''),
            'gsis_no'         => (string) ($user->gsis ?? ''),
            'pagibig_no'      => (string) ($user->pagibig ?? ''),
            'sss_no'          => (string) ($user->sssNo ?? ''),
            'philhealth_no'   => (string) ($user->philHealth ?? ''),

            'gender'          => (string) ($user->Sex ?? ''),
            'birth_date'      => (string) ($user->BirthDate ?? ''),
            'birth_place'     => (string) ($user->BirthPlace ?? ''),
            'blood_type'      => (string) ($user->bloodType ?? ''),
            'marital_status'  => (string) ($user->MaritalStatus ?? ''),
            'height'          => (string) ($user->height ?? ''),
            'weight'          => (string) ($user->weight ?? ''),

            'contact_no'      => (string) ($user->empMobile ?? ''),
            'official_email'  => (string) ($user->empEmail ?? ''),
            'address'         => $formattedAddress,
        ];
    }

    private function _mobile_fetch_support_notifications($settingsID, $userId, $limit)
    {
        if (!$this->db->table_exists('support_notifications')) {
            return [];
        }

        $this->db->select('n.*, actor.fName AS actor_fName, actor.lName AS actor_lName, si.ticket_number');
        $this->db->from('support_notifications n');
        $this->db->join('users actor', 'actor.user_id = n.actor_id', 'left');
        $this->db->join('support_issues si', 'si.id = n.issue_id', 'left');
        $this->db->where('n.settingsID', (int) $settingsID);
        $this->db->where('n.user_id', (int) $userId);
        $this->db->order_by('n.is_read', 'ASC');
        $this->db->order_by('n.created_at', 'DESC');
        if ($limit > 0) {
            $this->db->limit($limit);
        }
        $rows = $this->db->get()->result();

        return array_map(function ($row) {
            return (object) [
                'id'         => 'support_' . (int) ($row->id ?? 0),
                'title'      => $row->title ?? 'Support notification',
                'message'    => $row->message ?? '',
                'created_at' => $row->created_at ?? '',
                'is_seen'    => (int) ($row->is_read ?? 0),
                'fName'      => $row->actor_fName ?? '',
                'lName'      => $row->actor_lName ?? '',
                'source'     => 'support',
                'issue_id'   => (int) ($row->issue_id ?? 0),
                'ticket'     => $row->ticket_number ?? '',
                'task_id'    => 0,
            ];
        }, $rows);
    }

    private function _mobile_count_support_notifications($settingsID, $userId)
    {
        if (!$this->db->table_exists('support_notifications')) {
            return 0;
        }
        return (int) $this->db
            ->from('support_notifications')
            ->where('settingsID', (int) $settingsID)
            ->where('user_id', (int) $userId)
            ->where('is_read', 0)
            ->count_all_results();
    }

    private function _mobile_notification_payload($row)
    {
        $source   = isset($row->source) ? (string) $row->source : 'task';
        $firstName = trim((string) ($row->fName ?? ''));
        $lastName  = trim((string) ($row->lName ?? ''));
        $actorName = trim($firstName . ' ' . $lastName);

        $createdAt = (string) ($row->created_at ?? '');
        $createdLabel = '';
        if ($createdAt !== '' && $createdAt !== '0000-00-00 00:00:00') {
            $ts = strtotime($createdAt);
            $createdLabel = $ts ? date('M j, Y · g:i A', $ts) : $createdAt;
        }

        return [
            'id'             => (string) ($row->id ?? ''),
            'source'         => $source,
            'title'          => (string) ($row->title ?? ''),
            'message'        => (string) ($row->message ?? ''),
            'actor_name'     => $actorName !== '' ? $actorName : 'System',
            'is_seen'        => (int) ($row->is_seen ?? 0) === 1,
            'created_at'     => $createdAt,
            'created_label'  => $createdLabel,
            'task_id'        => (int) ($row->task_id ?? 0),
            'issue_id'       => (int) ($row->issue_id ?? 0),
            'ticket_number'  => (string) ($row->ticket ?? ''),
        ];
    }

    // ── Notes ───────────────────────────────────────────────────────────────

    public function notes()
    {
        if ($this->_method() === 'OPTIONS') {
            return mobile_json(['ok' => true]);
        }
        if ($this->_method() !== 'GET') {
            return mobile_json(['ok' => false, 'message' => 'Method not allowed.'], 405);
        }

        $claims = $this->_require_staff_claims();
        if ($claims === null) {
            return;
        }

        $settingsID = (int) ($claims['settingsID'] ?? 0);
        $username   = trim((string) ($claims['username'] ?? ''));

        $rows = $this->CashModel->noteList($username, $settingsID);
        $notes = [];
        foreach ((array) $rows as $row) {
            $notes[] = $this->_note_payload($row);
        }

        return mobile_json([
            'ok'    => true,
            'notes' => $notes,
        ]);
    }

    public function createNote()
    {
        if ($this->_method() === 'OPTIONS') {
            return mobile_json(['ok' => true]);
        }
        if ($this->_method() !== 'POST') {
            return mobile_json(['ok' => false, 'message' => 'Method not allowed.'], 405);
        }

        $claims = $this->_require_staff_claims();
        if ($claims === null) {
            return;
        }

        date_default_timezone_set('Asia/Manila');

        $payload    = $this->_read_payload();
        $settingsID = (int) ($claims['settingsID'] ?? 0);
        $username   = trim((string) ($claims['username'] ?? ''));

        $title       = trim((string) ($payload['title'] ?? ''));
        $description = trim((string) ($payload['description'] ?? $payload['noteDescription'] ?? ''));
        $tags        = $this->_normalize_tags($payload['tags'] ?? '');

        if ($title === '' && $description === '') {
            return mobile_json(['ok' => false, 'message' => 'Please enter a note title or description.'], 422);
        }

        if (!$this->db->field_exists('tags', 'notes')) {
            $this->db->query("ALTER TABLE notes ADD COLUMN tags VARCHAR(255) DEFAULT NULL");
        }

        $this->db->insert('notes', [
            'noteDate'        => date('Y-m-d'),
            'title'           => $title,
            'noteDescription' => $description,
            'tags'            => $tags,
            'notedBy'         => $username,
            'settingsID'      => $settingsID,
            'noteStat'        => 'Active',
        ]);

        $noteId = (int) $this->db->insert_id();
        if ($noteId <= 0) {
            return mobile_json(['ok' => false, 'message' => 'Unable to save the note.'], 500);
        }

        return mobile_json([
            'ok'      => true,
            'message' => 'Note saved successfully.',
            'note_id' => $noteId,
        ]);
    }

    public function updateNote($noteId)
    {
        if ($this->_method() === 'OPTIONS') {
            return mobile_json(['ok' => true]);
        }
        if ($this->_method() !== 'POST') {
            return mobile_json(['ok' => false, 'message' => 'Method not allowed.'], 405);
        }

        $claims = $this->_require_staff_claims();
        if ($claims === null) {
            return;
        }

        $payload    = $this->_read_payload();
        $settingsID = (int) ($claims['settingsID'] ?? 0);
        $username   = trim((string) ($claims['username'] ?? ''));

        $note = $this->_fetch_note_row((int) $noteId, $settingsID, $username);
        if (!$note) {
            return mobile_json(['ok' => false, 'message' => 'Note not found.'], 404);
        }

        $title       = trim((string) ($payload['title'] ?? ''));
        $description = trim((string) ($payload['description'] ?? $payload['noteDescription'] ?? ''));
        $tags        = $this->_normalize_tags($payload['tags'] ?? '');

        if ($title === '' && $description === '') {
            return mobile_json(['ok' => false, 'message' => 'Please enter a note title or description.'], 422);
        }

        if (!$this->db->field_exists('tags', 'notes')) {
            $this->db->query("ALTER TABLE notes ADD COLUMN tags VARCHAR(255) DEFAULT NULL");
        }

        $this->db
            ->where('noteID', (int) $noteId)
            ->where('settingsID', $settingsID)
            ->update('notes', [
                'title'           => $title,
                'noteDescription' => $description,
                'tags'            => $tags,
            ]);

        return mobile_json(['ok' => true, 'message' => 'Note updated successfully.']);
    }

    public function deleteNote($noteId)
    {
        if ($this->_method() === 'OPTIONS') {
            return mobile_json(['ok' => true]);
        }
        if ($this->_method() !== 'POST' && $this->_method() !== 'DELETE') {
            return mobile_json(['ok' => false, 'message' => 'Method not allowed.'], 405);
        }

        $claims = $this->_require_staff_claims();
        if ($claims === null) {
            return;
        }

        $settingsID = (int) ($claims['settingsID'] ?? 0);
        $username   = trim((string) ($claims['username'] ?? ''));

        $note = $this->_fetch_note_row((int) $noteId, $settingsID, $username);
        if (!$note) {
            return mobile_json(['ok' => false, 'message' => 'Note not found.'], 404);
        }

        $this->db
            ->where('noteID', (int) $noteId)
            ->where('settingsID', $settingsID)
            ->update('notes', ['noteStat' => 'Removed']);

        return mobile_json(['ok' => true, 'message' => 'Note deleted successfully.']);
    }

    public function toggleNoteFavorite($noteId)
    {
        if ($this->_method() === 'OPTIONS') {
            return mobile_json(['ok' => true]);
        }
        if ($this->_method() !== 'POST') {
            return mobile_json(['ok' => false, 'message' => 'Method not allowed.'], 405);
        }

        $claims = $this->_require_staff_claims();
        if ($claims === null) {
            return;
        }

        $payload    = $this->_read_payload();
        $settingsID = (int) ($claims['settingsID'] ?? 0);
        $username   = trim((string) ($claims['username'] ?? ''));

        $note = $this->_fetch_note_row((int) $noteId, $settingsID, $username);
        if (!$note) {
            return mobile_json(['ok' => false, 'message' => 'Note not found.'], 404);
        }

        if (!$this->db->field_exists('is_favorite', 'notes')) {
            $this->db->query("ALTER TABLE notes ADD COLUMN is_favorite INT DEFAULT 0");
        }

        $isFavorite = (int) ($payload['is_favorite'] ?? (((int) ($note->is_favorite ?? 0)) === 1 ? 0 : 1));
        $isFavorite = $isFavorite === 1 ? 1 : 0;

        $this->db
            ->where('noteID', (int) $noteId)
            ->where('settingsID', $settingsID)
            ->update('notes', ['is_favorite' => $isFavorite]);

        return mobile_json(['ok' => true, 'is_favorite' => $isFavorite === 1]);
    }

    // ── Reminders ───────────────────────────────────────────────────────────

    public function reminders()
    {
        if ($this->_method() === 'OPTIONS') {
            return mobile_json(['ok' => true]);
        }
        if ($this->_method() !== 'GET') {
            return mobile_json(['ok' => false, 'message' => 'Method not allowed.'], 405);
        }

        $claims = $this->_require_staff_claims();
        if ($claims === null) {
            return;
        }

        date_default_timezone_set('Asia/Manila');

        $settingsID = (int) ($claims['settingsID'] ?? 0);
        $userId     = (int) ($claims['user_id'] ?? 0);

        $rows = $this->RemindersModel->getAllReminders($settingsID, $userId);
        $reminders = [];
        foreach ((array) $rows as $row) {
            $reminders[] = $this->_reminder_payload($row);
        }

        $dueToday = $this->RemindersModel->getDueToday($settingsID, $userId);

        return mobile_json([
            'ok'              => true,
            'reminders'       => $reminders,
            'due_today_count' => is_array($dueToday) ? count($dueToday) : 0,
        ]);
    }

    public function createReminder()
    {
        if ($this->_method() === 'OPTIONS') {
            return mobile_json(['ok' => true]);
        }
        if ($this->_method() !== 'POST') {
            return mobile_json(['ok' => false, 'message' => 'Method not allowed.'], 405);
        }

        $claims = $this->_require_staff_claims();
        if ($claims === null) {
            return;
        }

        date_default_timezone_set('Asia/Manila');

        $payload    = $this->_read_payload();
        $settingsID = (int) ($claims['settingsID'] ?? 0);
        $userId     = (int) ($claims['user_id'] ?? 0);

        $title      = trim((string) ($payload['title'] ?? ''));
        $remindAt   = $this->_normalize_datetime_input($payload);
        $recurrence = $this->_normalize_recurrence($payload['recurrence'] ?? 'once');

        if ($title === '') {
            return mobile_json(['ok' => false, 'message' => 'Reminder title is required.'], 422);
        }
        if ($remindAt === null) {
            return mobile_json(['ok' => false, 'message' => 'Please provide a valid reminder date and time.'], 422);
        }

        $this->RemindersModel->addReminder([
            'title'       => $title,
            'description' => trim((string) ($payload['description'] ?? '')),
            'remind_at'   => $remindAt,
            'recurrence'  => $recurrence,
            'settingsID'  => $settingsID,
            'user_id'     => $userId,
        ]);

        $reminderId = (int) $this->db->insert_id();

        return mobile_json([
            'ok'          => true,
            'message'     => 'Reminder created successfully.',
            'reminder_id' => $reminderId,
        ]);
    }

    public function updateReminder($reminderId)
    {
        if ($this->_method() === 'OPTIONS') {
            return mobile_json(['ok' => true]);
        }
        if ($this->_method() !== 'POST') {
            return mobile_json(['ok' => false, 'message' => 'Method not allowed.'], 405);
        }

        $claims = $this->_require_staff_claims();
        if ($claims === null) {
            return;
        }

        date_default_timezone_set('Asia/Manila');

        $payload    = $this->_read_payload();
        $settingsID = (int) ($claims['settingsID'] ?? 0);
        $userId     = (int) ($claims['user_id'] ?? 0);

        $reminder = $this->_fetch_reminder_row((int) $reminderId, $settingsID, $userId);
        if (!$reminder) {
            return mobile_json(['ok' => false, 'message' => 'Reminder not found.'], 404);
        }

        $title      = trim((string) ($payload['title'] ?? ''));
        $remindAt   = $this->_normalize_datetime_input($payload);
        $recurrence = $this->_normalize_recurrence($payload['recurrence'] ?? ($reminder->recurrence ?? 'once'));

        if ($title === '') {
            return mobile_json(['ok' => false, 'message' => 'Reminder title is required.'], 422);
        }
        if ($remindAt === null) {
            return mobile_json(['ok' => false, 'message' => 'Please provide a valid reminder date and time.'], 422);
        }

        $this->RemindersModel->updateReminder((int) $reminderId, [
            'title'       => $title,
            'description' => trim((string) ($payload['description'] ?? '')),
            'remind_at'   => $remindAt,
            'recurrence'  => $recurrence,
        ]);

        return mobile_json(['ok' => true, 'message' => 'Reminder updated successfully.']);
    }

    public function deleteReminder($reminderId)
    {
        if ($this->_method() === 'OPTIONS') {
            return mobile_json(['ok' => true]);
        }
        if ($this->_method() !== 'POST' && $this->_method() !== 'DELETE') {
            return mobile_json(['ok' => false, 'message' => 'Method not allowed.'], 405);
        }

        $claims = $this->_require_staff_claims();
        if ($claims === null) {
            return;
        }

        $settingsID = (int) ($claims['settingsID'] ?? 0);
        $userId     = (int) ($claims['user_id'] ?? 0);

        $reminder = $this->_fetch_reminder_row((int) $reminderId, $settingsID, $userId);
        if (!$reminder) {
            return mobile_json(['ok' => false, 'message' => 'Reminder not found.'], 404);
        }

        $this->RemindersModel->deleteReminder((int) $reminderId);

        return mobile_json(['ok' => true, 'message' => 'Reminder deleted successfully.']);
    }

    // ── Notes / Reminders helpers ───────────────────────────────────────────

    private function _note_payload($row)
    {
        $rawTags = trim((string) ($row->tags ?? ''));
        $tags = $rawTags === ''
            ? []
            : array_values(array_filter(array_map('trim', explode(',', $rawTags)), function ($t) {
                return $t !== '';
            }));

        $date = (string) ($row->noteDate ?? '');

        return [
            'id'          => (int) ($row->noteID ?? 0),
            'title'       => (string) ($row->title ?? ''),
            'description' => (string) ($row->noteDescription ?? ''),
            'tags'        => $tags,
            'is_favorite' => (int) ($row->is_favorite ?? 0) === 1,
            'date'        => $date,
            'date_label'  => $date !== '' ? date('M d, Y', strtotime($date)) : '',
        ];
    }

    private function _fetch_note_row($noteId, $settingsID, $username)
    {
        return $this->db
            ->from('notes')
            ->where('noteID', (int) $noteId)
            ->where('settingsID', $settingsID)
            ->where('notedBy', $username)
            ->where('noteStat', 'Active')
            ->limit(1)
            ->get()
            ->row();
    }

    private function _normalize_tags($value)
    {
        if (is_array($value)) {
            $value = implode(',', array_map('strval', $value));
        }
        $parts = array_filter(array_map('trim', explode(',', (string) $value)), function ($t) {
            return $t !== '';
        });
        return implode(', ', $parts);
    }

    private function _reminder_payload($row)
    {
        $remindAt = (string) ($row->remind_at ?? '');
        return [
            'id'              => (int) ($row->id ?? 0),
            'title'           => (string) ($row->title ?? ''),
            'description'     => (string) ($row->description ?? ''),
            'remind_at'       => $remindAt,
            'remind_at_label' => $remindAt !== '' ? date('M d, Y · g:i A', strtotime($remindAt)) : '',
            'recurrence'      => (string) (($row->recurrence ?? 'once') ?: 'once'),
        ];
    }

    private function _fetch_reminder_row($reminderId, $settingsID, $userId)
    {
        $row = $this->RemindersModel->getReminderById((int) $reminderId);
        if (!$row) {
            return null;
        }
        if ((int) ($row->settingsID ?? 0) !== (int) $settingsID) {
            return null;
        }
        if ((int) ($row->user_id ?? 0) !== (int) $userId) {
            return null;
        }
        return $row;
    }

    private function _normalize_recurrence($value)
    {
        $value = strtolower(trim((string) $value));
        return in_array($value, ['once', 'monthly', 'yearly'], true) ? $value : 'once';
    }

    /**
     * Accepts either a combined `remind_at` ("Y-m-d H:i[:s]") or separate
     * `remind_date` + `remind_time` fields and returns a normalized
     * "Y-m-d H:i:s" string, or null when the input is invalid.
     */
    private function _normalize_datetime_input($payload)
    {
        $combined = trim((string) ($payload['remind_at'] ?? ''));
        $date = trim((string) ($payload['remind_date'] ?? ''));
        $time = trim((string) ($payload['remind_time'] ?? ''));

        $candidate = '';
        if ($combined !== '') {
            $candidate = $combined;
        } elseif ($date !== '') {
            $candidate = $date . ' ' . ($time !== '' ? $time : '00:00');
        }

        if ($candidate === '') {
            return null;
        }

        $ts = strtotime($candidate);
        if ($ts === false) {
            return null;
        }
        return date('Y-m-d H:i:s', $ts);
    }

    private function _require_staff_claims()
    {
        $claims = mobile_require_claims();
        if ($claims === null) {
            return null;
        }
        if (trim((string) ($claims['level'] ?? '')) !== 'Staff') {
            mobile_json(['ok' => false, 'message' => 'Staff access required.'], 403);
            return null;
        }
        return $claims;
    }

    /**
     * Claims guard for endpoints shared by Staff and Admin members (e.g. the
     * personal calendar, which is always scoped by user_id + settingsID).
     */
    private function _require_member_claims()
    {
        $claims = mobile_require_claims();
        if ($claims === null) {
            return null;
        }
        $level = trim((string) ($claims['level'] ?? ''));
        if ($level !== 'Staff' && $level !== 'Admin') {
            mobile_json(['ok' => false, 'message' => 'Member access required.'], 403);
            return null;
        }
        return $claims;
    }

    private function _method()
    {
        return strtoupper((string) $this->input->method(true));
    }

    private function _read_payload()
    {
        $raw = file_get_contents('php://input');
        if (is_string($raw) && $raw !== '') {
            $decoded = json_decode($raw, true);
            if (is_array($decoded)) {
                return $decoded;
            }
        }
        $post = $this->input->post(null, true);
        return is_array($post) ? $post : [];
    }

    private function _normalize_status_filter($value)
    {
        $value = strtolower(trim((string) $value));
        return in_array($value, ['open', 'closed', 'all'], true) ? $value : 'open';
    }

    private function _normalize_task_scope($value)
    {
        $value = strtolower(trim((string) $value));
        return in_array($value, ['forwarded'], true) ? $value : '';
    }

    private function _normalize_date_input($value)
    {
        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }
        $date = DateTime::createFromFormat('Y-m-d', $value);
        if ($date && $date->format('Y-m-d') === $value) {
            return $value;
        }
        return null;
    }

    private function _is_valid_date($value)
    {
        return $this->_normalize_date_input($value) !== null;
    }

    private function _normalize_task_label($value)
    {
        $value = preg_replace('/\s+/', ' ', trim((string) $value));
        if ($value === '') {
            return '';
        }
        if (function_exists('mb_strtoupper')) {
            return mb_strtoupper($value, 'UTF-8');
        }
        return strtoupper($value);
    }

    private function _normalize_priority($value)
    {
        $value = trim((string) $value);
        return array_key_exists($value, self::PRIORITY_LABELS) ? $value : '2';
    }

    private function _progressBetween($start, $end, $today)
    {
        $startTs = strtotime((string) $start);
        $endTs   = strtotime((string) $end);
        $todayTs = strtotime((string) $today);
        if (!$startTs || !$endTs || $endTs <= $startTs) {
            return 0;
        }
        if ($todayTs >= $endTs) {
            return 100;
        }
        if ($todayTs <= $startTs) {
            return 0;
        }
        return (int) round(($todayTs - $startTs) / ($endTs - $startTs) * 100);
    }

    private function _attendance_notice($username, $today)
    {
        if ($username === '') {
            return '';
        }
        if ($this->_has_open_time_in($username, $today)) {
            return "You have an open time-in. Please time out to complete today's attendance.";
        }

        $hasAny = $this->db->query(
            "select dtrID from dtr where logDate=? and IDNumber=? limit 1",
            [$today, $username]
        )->row();

        if (!$hasAny) {
            return 'Please remember to time in and time out today to record your attendance.';
        }

        return '';
    }

    private function _attendance_status_label($username, $today)
    {
        $notice = $this->_attendance_notice($username, $today);
        return $notice !== '' ? $notice : 'Your attendance record looks complete for today.';
    }

    private function _today_attendance_snapshot($username, $today)
    {
        $snapshot = [
            'latest_time_in_label' => '',
            'latest_time_out_label' => '',
            'open_slot_label' => '',
            'has_record_today' => false,
        ];

        if ($username === '') {
            return $snapshot;
        }

        $rows = $this->db
            ->select('amTimeIn, amTimeOut, pmTimeIn, pmTimeOut')
            ->from('dtr')
            ->where('logDate', $today)
            ->where('IDNumber', $username)
            ->order_by('dtrID', 'ASC')
            ->get()
            ->result();

        $latestTimeInTs = null;
        $latestTimeOutTs = null;

        foreach ((array) $rows as $row) {
            foreach (
                [
                    ['slot' => 'AM', 'time_in' => $row->amTimeIn ?? '', 'time_out' => $row->amTimeOut ?? ''],
                    ['slot' => 'PM', 'time_in' => $row->pmTimeIn ?? '', 'time_out' => $row->pmTimeOut ?? ''],
                ] as $pair
            ) {
                $timeIn = trim((string) $pair['time_in']);
                $timeOut = trim((string) $pair['time_out']);

                if ($timeIn !== '') {
                    $snapshot['has_record_today'] = true;
                    $timeInTs = $this->_parse_time($today, $timeIn);
                    if ($timeInTs !== null && ($latestTimeInTs === null || $timeInTs > $latestTimeInTs)) {
                        $latestTimeInTs = $timeInTs;
                        $snapshot['latest_time_in_label'] = date('g:i A', $timeInTs);
                    }
                }

                if ($timeOut !== '') {
                    $snapshot['has_record_today'] = true;
                    $timeOutTs = $this->_parse_time($today, $timeOut);
                    if ($timeOutTs !== null && ($latestTimeOutTs === null || $timeOutTs > $latestTimeOutTs)) {
                        $latestTimeOutTs = $timeOutTs;
                        $snapshot['latest_time_out_label'] = date('g:i A', $timeOutTs);
                    }
                } elseif ($timeIn !== '') {
                    $snapshot['open_slot_label'] = (string) $pair['slot'];
                }
            }
        }

        return $snapshot;
    }

    private function _has_open_time_in($username, $today)
    {
        if ($username === '') {
            return false;
        }

        $open = $this->db->query(
            "select dtrID from dtr where logDate=? and IDNumber=? and ((amTimeIn!='' and (amTimeOut='' or amTimeOut is null)) or (pmTimeIn!='' and (pmTimeOut='' or pmTimeOut is null))) order by dtrID desc limit 1",
            [$today, $username]
        )->row();

        return $open !== null;
    }

    private function _detect_time_slot($timestamp = null)
    {
        $timestamp = $timestamp ?? time();
        $hour = (int) date('G', $timestamp);
        return $hour < 12 ? 'am' : 'pm';
    }

    private function _aggregateAttendanceRows($rows, $settingsID)
    {
        $grouped = [];
        $totalSeconds = 0;
        $accomplishmentCount = 0;

        foreach ((array) $rows as $row) {
            $dateKey = (string) ($row->logDate ?? '');
            if ($dateKey === '') {
                continue;
            }

            $intervals = [];
            foreach (
                [
                    ['time_in' => $row->amTimeIn ?? '', 'time_out' => $row->amTimeOut ?? ''],
                    ['time_in' => $row->pmTimeIn ?? '', 'time_out' => $row->pmTimeOut ?? ''],
                ] as $pair
            ) {
                $timeIn = trim((string) $pair['time_in']);
                $timeOut = trim((string) $pair['time_out']);

                if ($timeIn === '') {
                    continue;
                }

                $start = $this->_parse_time($dateKey, $timeIn);
                if ($start === null) {
                    continue;
                }

                if ($timeOut !== '') {
                    $end = $this->_parse_time($dateKey, $timeOut);
                    if ($end !== null && $end > $start) {
                        $intervals[] = [
                            'label' => date('g:i A', $start) . ' - ' . date('g:i A', $end),
                            'time_in_label' => date('g:i A', $start),
                            'time_out_label' => date('g:i A', $end),
                            'seconds' => $end - $start,
                            'start' => $start,
                            'open' => false,
                        ];
                        continue;
                    }
                }

                $intervals[] = [
                    'label' => date('g:i A', $start) . ' - Time out pending',
                    'time_in_label' => date('g:i A', $start),
                    'time_out_label' => '',
                    'seconds' => 0,
                    'start' => $start,
                    'open' => true,
                ];
            }

            usort($intervals, function ($left, $right) {
                return (int) ($left['start'] ?? 0) <=> (int) ($right['start'] ?? 0);
            });

            $daySeconds = 0;
            $hasOpen = false;
            foreach ($intervals as $interval) {
                $daySeconds += (int) ($interval['seconds'] ?? 0);
                if (!empty($interval['open'])) {
                    $hasOpen = true;
                }
            }

            $accomplishments = $this->CashModel->accomplishmentCountForDate(
                $settingsID,
                (string) ($row->IDNumber ?? $row->username ?? ''),
                $dateKey
            );

            $status = 'present';
            if (empty($intervals)) {
                $status = 'absent';
            } elseif ($hasOpen) {
                $status = 'pending';
            }

            $grouped[] = [
                'date' => $dateKey,
                'date_label' => date('F j, Y', strtotime($dateKey)),
                'status' => $status,
                'intervals' => array_map(function ($interval) {
                    return [
                        'label' => (string) ($interval['label'] ?? ''),
                        'time_in_label' => (string) ($interval['time_in_label'] ?? ''),
                        'time_out_label' => (string) ($interval['time_out_label'] ?? ''),
                        'seconds' => (int) ($interval['seconds'] ?? 0),
                        'open' => !empty($interval['open']),
                    ];
                }, $intervals),
                'total_seconds' => $daySeconds,
                'total_hours_label' => $this->_formatSeconds($daySeconds),
                'accomplishment_count' => $accomplishments,
            ];

            $totalSeconds += $daySeconds;
            $accomplishmentCount += $accomplishments;
        }

        usort($grouped, function ($left, $right) {
            return strcmp((string) ($right['date'] ?? ''), (string) ($left['date'] ?? ''));
        });

        $presentDays = 0;
        $pendingDays = 0;
        $absentDays = 0;
        foreach ($grouped as $entry) {
            if ($entry['status'] === 'pending') {
                $pendingDays++;
            } elseif ($entry['status'] === 'absent') {
                $absentDays++;
            } else {
                $presentDays++;
            }
        }

        return [
            'records' => $grouped,
            'present_days' => $presentDays,
            'pending_days' => $pendingDays,
            'absent_days' => $absentDays,
            'accomplishment_count' => $accomplishmentCount,
            'total_seconds' => $totalSeconds,
        ];
    }

    private function _aggregateDtrForUser($rows)
    {
        $grouped = [];
        foreach ($rows as $row) {
            $dateKey = $row->logDate;
            if (!isset($grouped[$dateKey])) {
                $grouped[$dateKey] = [
                    'logDate'       => $dateKey,
                    'intervals'     => [],
                    'am_intervals'  => [],
                    'pm_intervals'  => [],
                    'total_seconds' => 0,
                ];
            }
            $intervals = [];
            if (!empty($row->amTimeIn) && !empty($row->amTimeOut)) {
                $start = $this->_parse_time($dateKey, $row->amTimeIn);
                $end = $this->_parse_time($dateKey, $row->amTimeOut);
                if ($start && $end && $end > $start) {
                    $intervals[] = [$start, $end];
                }
            } elseif (!empty($row->amTimeIn) && empty($row->amTimeOut)) {
                $start = $this->_parse_time($dateKey, $row->amTimeIn);
                if ($start) {
                    $intervals[] = ['open' => true, 'label' => date('g:i A', $start) . ' - pending', 'start' => $start];
                }
            }
            if (!empty($row->pmTimeIn) && !empty($row->pmTimeOut)) {
                $start = $this->_parse_time($dateKey, $row->pmTimeIn);
                $end = $this->_parse_time($dateKey, $row->pmTimeOut);
                if ($start && $end && $end > $start) {
                    $intervals[] = [$start, $end];
                }
            } elseif (!empty($row->pmTimeIn) && empty($row->pmTimeOut)) {
                $start = $this->_parse_time($dateKey, $row->pmTimeIn);
                if ($start) {
                    $intervals[] = ['open' => true, 'label' => date('g:i A', $start) . ' - pending', 'start' => $start];
                }
            }
            foreach ($intervals as $intv) {
                if (isset($intv['open'])) {
                    $grouped[$dateKey]['intervals'][] = [
                        'label'   => $intv['label'],
                        'seconds' => 0,
                        'open'    => true,
                        'start'   => $intv['start'] ?? 0,
                    ];
                    $bucket = 'am_intervals';
                    if (isset($intv['start']) && (int) date('G', $intv['start']) >= 12) {
                        $bucket = 'pm_intervals';
                    } elseif (stripos($intv['label'], 'PM') !== false) {
                        $bucket = 'pm_intervals';
                    }
                    $grouped[$dateKey][$bucket][] = [
                        'label'   => $intv['label'],
                        'seconds' => 0,
                        'open'    => true,
                        'start'   => $intv['start'] ?? 0,
                    ];
                } else {
                    $secs = $intv[1] - $intv[0];
                    $label = date('g:i A', $intv[0]) . ' - ' . date('g:i A', $intv[1]);
                    $grouped[$dateKey]['intervals'][] = [
                        'label'   => $label,
                        'seconds' => $secs,
                        'open'    => false,
                        'start'   => $intv[0],
                    ];
                    $bucket = 'am_intervals';
                    $endHour = (int) date('G', $intv[1]);
                    $startHour = (int) date('G', $intv[0]);
                    if ($endHour >= 12 || $startHour >= 12) {
                        $bucket = 'pm_intervals';
                    }
                    $grouped[$dateKey][$bucket][] = [
                        'label'   => $label,
                        'seconds' => $secs,
                        'open'    => false,
                        'start'   => $intv[0],
                    ];
                    $grouped[$dateKey]['total_seconds'] += $secs;
                }
            }
        }
        foreach ($grouped as &$g) {
            if (!empty($g['intervals'])) {
                usort($g['intervals'], function ($a, $b) {
                    return ($a['start'] ?? 0) <=> ($b['start'] ?? 0);
                });
            }
            if (!empty($g['am_intervals'])) {
                usort($g['am_intervals'], function ($a, $b) {
                    return ($a['start'] ?? 0) <=> ($b['start'] ?? 0);
                });
            }
            if (!empty($g['pm_intervals'])) {
                usort($g['pm_intervals'], function ($a, $b) {
                    return ($a['start'] ?? 0) <=> ($b['start'] ?? 0);
                });
            }
            $g['total_label'] = $this->_formatSeconds($g['total_seconds']);
        }
        unset($g);
        return array_map(function ($item) {
            return (object) $item;
        }, array_values($grouped));
    }

    private function _parse_time($dateKey, $timeValue)
    {
        $timeValue = trim((string) $timeValue);
        if ($timeValue === '') {
            return null;
        }

        $formats = [
            'Y-m-d g:i:s A',
            'Y-m-d g:i A',
            'Y-m-d H:i:s',
            'Y-m-d H:i',
            'Y-m-d H:i:s A',
            'Y-m-d H:i A',
        ];

        foreach ($formats as $format) {
            $dateTime = DateTime::createFromFormat($format, $dateKey . ' ' . $timeValue);
            if ($dateTime instanceof DateTime) {
                $errors = DateTime::getLastErrors();
                if (
                    (!is_array($errors) || empty($errors['warning_count']))
                    && (!is_array($errors) || empty($errors['error_count']))
                ) {
                    return $dateTime->getTimestamp();
                }
            }
        }

        $stripped = preg_replace('/\\s*(AM|PM)$/i', '', $timeValue);
        if ($stripped !== null && $stripped !== $timeValue) {
            foreach (['Y-m-d H:i:s', 'Y-m-d H:i'] as $format) {
                $dateTime = DateTime::createFromFormat($format, $dateKey . ' ' . $stripped);
                if ($dateTime instanceof DateTime) {
                    $errors = DateTime::getLastErrors();
                    if (
                        (!is_array($errors) || empty($errors['warning_count']))
                        && (!is_array($errors) || empty($errors['error_count']))
                    ) {
                        return $dateTime->getTimestamp();
                    }
                }
            }
        }

        $timestamp = strtotime($dateKey . ' ' . $timeValue);
        return $timestamp === false ? null : $timestamp;
    }

    private function _formatSeconds($seconds)
    {
        $seconds = max(0, (int) $seconds);
        $hours = (int) floor($seconds / 3600);
        $minutes = (int) floor(($seconds % 3600) / 60);
        return sprintf('%02d:%02d', $hours, $minutes);
    }

    private function _task_payload_from_row($row, $today, $pendingForwardedIds)
    {
        $taskId = (int) ($row->taskID ?? 0);
        $taskTitle = trim((string) ($row->task ?? ''));
        if ($taskTitle === '') {
            $taskTitle = 'Untitled task';
        }
        $reportedDate = trim((string) ($row->reportedDate ?? ''));
        $dueDate = trim((string) ($row->dueDate ?? ''));
        $priority = $this->_normalize_priority((string) ($row->priority ?? '2'));
        $statusValue = (string) ($row->taskStat ?? '1');
        $dueMeta = $this->_task_due_meta($dueDate, $today);

        return [
            'id' => $taskId,
            'title' => $taskTitle,
            'reported_date' => $reportedDate,
            'due_date' => $dueDate,
            'status_value' => $statusValue,
            'status' => $statusValue === '0' ? 'closed' : 'open',
            'priority_value' => $priority,
            'priority_label' => self::PRIORITY_LABELS[$priority],
            'project_id' => (int) ($row->projectID ?? 0),
            'project_name' => trim((string) ($row->projectDescription ?? '')) !== ''
                ? trim((string) $row->projectDescription)
                : 'No project',
            'assigned_person_id' => (int) ($row->assignedPerson ?? 0),
            'assigned_person_name' => trim((string) ($row->assignedPersonName ?? '')) !== ''
                ? trim((string) $row->assignedPersonName)
                : 'Unassigned',
            'attachment_link' => (string) ($row->attachment_link ?? ''),
            'admin_comment' => (string) ($row->latestAdminComment ?? ''),
            'latest_comment_date' => (string) ($row->latestAdminCommentDate ?? ''),
            'latest_comment_id' => (int) ($row->latestAdminCommentId ?? 0),
            'support_issue_id' => (int) ($row->supportIssueId ?? 0),
            'support_ticket_number' => (string) ($row->supportTicketNumber ?? ''),
            'added_by' => (string) ($row->added_by ?? ''),
            'forwarded_from' => (int) ($row->forwarded_from ?? 0),
            'forwarded_note' => (string) ($row->forwarded_note ?? ''),
            'is_forwarded_task' => (int) ($row->forwarded_from ?? 0) > 0,
            'is_forwarded_pending' => in_array($taskId, $pendingForwardedIds, true),
            'due_meta_label' => $dueMeta['label'],
            'due_meta_type' => $dueMeta['type'],
        ];
    }

    private function _task_due_meta($dueDate, $today)
    {
        $dueDate = trim((string) $dueDate);
        if ($dueDate === '' || $dueDate === '0000-00-00') {
            return ['type' => 'undated', 'label' => 'No due date set yet'];
        }

        $dayDiff = (int) floor((strtotime($dueDate) - strtotime($today)) / 86400);
        if ($dayDiff < 0) {
            return ['type' => 'overdue', 'label' => 'Overdue by ' . number_format(abs($dayDiff)) . ' day(s)'];
        }
        if ($dayDiff === 0) {
            return ['type' => 'due_today', 'label' => 'Due today'];
        }
        return ['type' => 'upcoming', 'label' => 'Due in ' . number_format($dayDiff) . ' day(s)'];
    }

    private function _project_options_payload($rows)
    {
        return array_map(function ($row) {
            return [
                'id' => (int) ($row->projectID ?? 0),
                'name' => (string) ($row->projectDescription ?? ''),
            ];
        }, (array) $rows);
    }

    private function _staff_options_payload($rows, $excludeUserId)
    {
        $payload = [];
        foreach ((array) $rows as $row) {
            $userId = (int) ($row->user_id ?? 0);
            if ($userId <= 0 || $userId === (int) $excludeUserId) {
                continue;
            }
            $payload[] = [
                'user_id' => $userId,
                'name' => trim((string) (($row->lName ?? '') . ', ' . ($row->fName ?? ''))),
            ];
        }
        return $payload;
    }

    private function _fetch_task_row($settingsID, $taskId, $userId)
    {
        $latestCommentSubquery = "(SELECT taskID, MAX(ptsID) AS ptsID FROM projects_task_stat GROUP BY taskID) latest_comment";

        return $this->db
            ->select("
                projects_task.*,
                COALESCE(NULLIF(TRIM(projects.projectDescription), ''), support_project.projectDescription) AS projectDescription,
                CONCAT(users.fName, ' ', users.lName) AS assignedPersonName,
                si.id AS supportIssueId,
                si.ticket_number AS supportTicketNumber,
                COALESCE(NULLIF(projects_task.projectID, 0), si.project_id) AS linkedProjectId,
                pts.note AS latestAdminComment,
                pts.datePosted AS latestAdminCommentDate,
                pts.ptsID AS latestAdminCommentId
            ")
            ->from('projects_task')
            ->join('projects', 'projects.projectID = projects_task.projectID', 'left')
            ->join('users', 'users.user_id = projects_task.assignedPerson', 'left')
            ->join('support_issues si', 'si.task_id = projects_task.taskID AND si.settingsID = projects_task.settingsID', 'left')
            ->join('projects support_project', 'support_project.projectID = si.project_id', 'left')
            ->join($latestCommentSubquery, 'latest_comment.taskID = projects_task.taskID', 'left', false)
            ->join('projects_task_stat pts', 'pts.ptsID = latest_comment.ptsID', 'left')
            ->where('projects_task.settingsID', (int) $settingsID)
            ->where('projects_task.taskID', (int) $taskId)
            ->where('projects_task.assignedPerson', (int) $userId)
            ->limit(1)
            ->get()
            ->row();
    }

    private function _replace_task_checklist($taskId, $settingsID, $items, $username)
    {
        $this->db
            ->where('taskID', (int) $taskId)
            ->where('settingsID', (int) $settingsID)
            ->delete('task_checklist');

        foreach ((array) $items as $item) {
            $description = '';
            $status = 'Pending';
            $isCompleted = 0;

            if (is_array($item)) {
                $description = trim((string) ($item['item_description'] ?? $item['itemDescription'] ?? ''));
                $status = trim((string) ($item['status'] ?? 'Pending'));
                $isCompleted = !empty($item['is_completed']) || !empty($item['isCompleted']) ? 1 : 0;
            } else {
                $description = trim((string) $item);
            }

            if ($description === '') {
                continue;
            }

            $data = [
                'taskID' => (int) $taskId,
                'itemDescription' => $description,
                'status' => $status !== '' ? $status : 'Pending',
                'isCompleted' => $isCompleted,
                'settingsID' => (int) $settingsID,
            ];
            if ($isCompleted) {
                if ($this->db->field_exists('completedAt', 'task_checklist')) {
                    $data['completedAt'] = date('Y-m-d H:i:s');
                }
                if ($this->db->field_exists('completedBy', 'task_checklist')) {
                    $data['completedBy'] = $username;
                }
            }

            $this->db->insert('task_checklist', $data);
        }
    }

    private function _apply_checklist_completion_points($taskId, $username, $date)
    {
        $completedChecklistCount = (int) $this->db
            ->where('taskID', (int) $taskId)
            ->where('isCompleted', 1)
            ->count_all_results('task_checklist');

        if ($completedChecklistCount <= 0) {
            return;
        }

        for ($index = 0; $index < $completedChecklistCount; $index++) {
            $data = [
                'taskID' => (int) $taskId,
                'note' => 'Checklist item completed #' . ($index + 1),
                'datePosted' => $date,
                'postedBy' => $username,
                'taskStat' => '0',
            ];
            if ($this->db->field_exists('points', 'projects_task_stat')) {
                $data['points'] = 1;
            }
            $this->db->insert('projects_task_stat', $data);
        }
    }

    private function _apply_forwarded_task_completion_logic($taskId, $settingsID, $userId, $username, $date)
    {
        $forwardedTask = $this->db
            ->select('forwarded_from, assignedPerson')
            ->from('projects_task')
            ->where('taskID', (int) $taskId)
            ->limit(1)
            ->get()
            ->row();

        if ($forwardedTask && !empty($forwardedTask->forwarded_from)) {
            $originalTask = $this->db
                ->select('taskID, taskStat, assignedPerson')
                ->from('projects_task')
                ->where('taskID', (int) $forwardedTask->forwarded_from)
                ->limit(1)
                ->get()
                ->row();

            if ($originalTask && (string) $originalTask->taskStat === '1') {
                $this->db->where('taskID', (int) $originalTask->taskID);
                $this->db->update('projects_task', [
                    'taskStat' => '0',
                    'completed_by' => $userId,
                    'completed_by_forward' => '1',
                ]);

                $this->db->insert('projects_task_stat', [
                    'taskID' => (int) $originalTask->taskID,
                    'note' => 'Task completed by forwarded assignee (race condition). Points go to forwarded person.',
                    'datePosted' => $date,
                    'postedBy' => $username,
                    'taskStat' => '0',
                ]);
            }
        }

        $forwardedCopies = $this->db
            ->select('taskID, taskStat, assignedPerson')
            ->from('projects_task')
            ->where('forwarded_from', (int) $taskId)
            ->where('taskStat', '1')
            ->get()
            ->result();

        foreach ((array) $forwardedCopies as $copy) {
            $this->db->where('taskID', (int) $copy->taskID);
            $this->db->update('projects_task', [
                'taskStat' => '0',
                'completed_by' => $userId,
                'completed_by_forward' => '1',
            ]);

            $this->db->insert('projects_task_stat', [
                'taskID' => (int) $copy->taskID,
                'note' => 'Task closed because original assignee completed it first. Points go to original assignee.',
                'datePosted' => $date,
                'postedBy' => $username,
                'taskStat' => '0',
            ]);
        }
    }

    private function _staff_pending_forwarded_task_ids($settingsID, $userId, $username)
    {
        $settingsID = (int) $settingsID;
        $userId = (int) $userId;
        $username = trim((string) $username);

        if ($settingsID <= 0 || $userId <= 0 || $username === '') {
            return [];
        }

        $usernameSql = $this->db->escape($username);
        $hasForwardedFrom = $this->db->field_exists('forwarded_from', 'projects_task');
        $systemNotesSql = implode(' OR ', array_map(function ($pattern) {
            return "COALESCE(s.note, '') LIKE " . $this->db->escape($pattern);
        }, [
            'Task created from support ticket %',
            'Tagged from support ticket %',
            'Task forwarded from %',
            'Support issue assigned%',
        ]));

        $this->db
            ->select('t.taskID')
            ->from('projects_task t')
            ->where('t.settingsID', $settingsID)
            ->where('t.assignedPerson', $userId)
            ->where('t.taskStat', '1')
            ->group_start();

        if ($hasForwardedFrom) {
            $this->db->where('t.forwarded_from >', 0);
            $this->db->or_like('t.added_by', 'support_', 'after');
        } else {
            $this->db->like('t.added_by', 'support_', 'after');
        }

        $rows = $this->db
            ->group_end()
            ->where(
                "NOT EXISTS (
                    SELECT 1
                    FROM projects_task_stat s
                    WHERE s.taskID = t.taskID
                        AND TRIM(COALESCE(s.postedBy, '')) = {$usernameSql}
                        AND NOT ({$systemNotesSql})
                )",
                null,
                false
            )
            ->get()
            ->result();

        return array_values(array_unique(array_filter(array_map(function ($row) {
            return (int) ($row->taskID ?? 0);
        }, $rows))));
    }

    private function _mark_forwarded_task_action($taskID, $settingsID, $userId, $username, $note = 'Forwarded task acknowledged.')
    {
        $taskID = (int) $taskID;
        $settingsID = (int) $settingsID;
        $userId = (int) $userId;
        $username = trim((string) $username);
        $note = trim((string) $note);

        if ($taskID <= 0 || $settingsID <= 0 || $userId <= 0 || $username === '' || $note === '') {
            return;
        }

        $taskRow = $this->db
            ->select('taskID, taskStat, forwarded_from, added_by')
            ->from('projects_task')
            ->where('taskID', $taskID)
            ->where('settingsID', $settingsID)
            ->where('assignedPerson', $userId)
            ->limit(1)
            ->get()
            ->row();

        $isForwardedTask = (int) ($taskRow->forwarded_from ?? 0) > 0;
        $isSupportAssignedTask = stripos((string) ($taskRow->added_by ?? ''), 'support_') === 0;

        if (!$taskRow || (!$isForwardedTask && !$isSupportAssignedTask)) {
            return;
        }

        $existing = $this->db
            ->select('ptsID')
            ->from('projects_task_stat')
            ->where('taskID', $taskID)
            ->where('postedBy', $username)
            ->where('note', $note)
            ->limit(1)
            ->get()
            ->row();

        if ($existing) {
            return;
        }

        $this->db->insert('projects_task_stat', [
            'taskID' => $taskID,
            'note' => $note,
            'datePosted' => date('Y-m-d H:i:s'),
            'postedBy' => $username,
            'taskStat' => (string) ($taskRow->taskStat ?? '1'),
        ]);
    }
}
