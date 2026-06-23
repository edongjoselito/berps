<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * BERPS Mobile Admin endpoints.
 *
 * Mirrors the admin-only web pages (dashboard, project/task management,
 * employee tasks & accomplishments, attendance, employee DTR and the client
 * list) over the same HMAC bearer-token auth used by the staff app. Only a
 * token whose `level` claim is "Admin" may call these endpoints.
 *
 * The data layer is reused verbatim from CashModel (the same methods the web
 * Page controller calls); this controller only translates the rows into JSON
 * and re-implements the small presentation helpers (attendance/DTR
 * aggregation, task payloads) so it stays independent of the Page/MobileStaff
 * controllers.
 */
class MobileAdmin extends CI_Controller
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
        mobile_send_cors();
    }

    // ── Dashboard (Page/admin) ───────────────────────────────────────────────

    public function dashboard()
    {
        if ($this->_method() === 'OPTIONS') {
            return mobile_json(['ok' => true]);
        }
        if ($this->_method() !== 'GET') {
            return mobile_json(['ok' => false, 'message' => 'Method not allowed.'], 405);
        }

        $claims = $this->_require_admin_claims();
        if ($claims === null) {
            return;
        }

        date_default_timezone_set('Asia/Manila');

        $settingsID = (int) ($claims['settingsID'] ?? 0);
        $today      = date('Y-m-d');
        $month      = (int) date('n');
        $year       = (int) date('Y');
        $windowDays = 7;

        $payments    = $this->CashModel->todaysPayments($settingsID, $today);
        $expenses    = $this->CashModel->todayExpenses($settingsID, $today);
        $clients     = $this->CashModel->totalClients($settingsID);
        $receivables = $this->CashModel->receivableCounts($settingsID);
        $prospects   = $this->CashModel->getClientsByStatus($settingsID, 'Prospect');
        $summaryRows = $this->CashModel->getAccomplishedTaskSummary($settingsID, $month, $year);
        $queueRows   = $this->CashModel->openTaskDueQueue($settingsID, null, 6, $windowDays, $today);

        $accomplished = [];
        foreach ((array) $summaryRows as $row) {
            $name = trim((string) (($row->fName ?? '') . ' ' . ($row->lName ?? '')));
            $accomplished[] = [
                'name'  => $name !== '' ? $name : 'Unknown',
                'total' => (int) ($row->total ?? 0),
            ];
        }

        return mobile_json([
            'ok'      => true,
            'date'    => $today,
            'finance' => [
                'todays_payments' => (float) ($payments[0]->Total ?? 0),
                'todays_expenses' => (float) ($expenses[0]->Total ?? 0),
                'total_clients'   => (int) ($clients[0]->Total ?? 0),
                'open_receivable' => (float) ($receivables[0]->Counts ?? 0),
                'prospect_clients' => is_array($prospects) ? count($prospects) : 0,
            ],
            'tasks' => [
                'due_today'        => (int) $this->CashModel->countOpenTasksDueToday($settingsID, null, $today),
                'due_soon'         => (int) $this->CashModel->countOpenTasksDueSoon($settingsID, $windowDays, null, $today),
                'overdue'          => (int) $this->CashModel->countOpenTasksOverdue($settingsID, null, $today),
                'without_due_date' => (int) $this->CashModel->countOpenTasksWithoutDueDate($settingsID),
                'due_window_days'  => $windowDays,
            ],
            'task_queue'           => $this->_task_queue_payload($queueRows, $today),
            'accomplished_summary' => $accomplished,
        ]);
    }

    // ── Tasks list / create (Page/projectAddTask) ────────────────────────────

    public function tasks()
    {
        if ($this->_method() === 'OPTIONS') {
            return mobile_json(['ok' => true]);
        }
        if ($this->_method() !== 'GET') {
            return mobile_json(['ok' => false, 'message' => 'Method not allowed.'], 405);
        }

        $claims = $this->_require_admin_claims();
        if ($claims === null) {
            return;
        }

        date_default_timezone_set('Asia/Manila');

        $settingsID   = (int) ($claims['settingsID'] ?? 0);
        $today        = date('Y-m-d');
        $windowDays   = 7;
        $statusFilter = $this->_normalize_status_filter($this->input->get('status'));

        $taskData = $this->CashModel->taskList($settingsID);
        $filtered = array_values(array_filter((array) $taskData, function ($task) use ($statusFilter) {
            $stat = (string) ($task->taskStat ?? '1');
            if ($statusFilter === 'open') {
                return $stat === '1';
            }
            if ($statusFilter === 'closed') {
                return $stat === '0';
            }
            return true;
        }));

        $tasks = array_map(function ($row) use ($today) {
            return $this->_task_payload_from_row($row, $today);
        }, $filtered);

        return mobile_json([
            'ok'            => true,
            'status_filter' => $statusFilter,
            'counts' => [
                'open'             => (int) $this->CashModel->countOpenTasks($settingsID),
                'closed'           => (int) $this->CashModel->countClosedTasks($settingsID),
                'due_today'        => (int) $this->CashModel->countOpenTasksDueToday($settingsID, null, $today),
                'due_soon'         => (int) $this->CashModel->countOpenTasksDueSoon($settingsID, $windowDays, null, $today),
                'overdue'          => (int) $this->CashModel->countOpenTasksOverdue($settingsID, null, $today),
                'without_due_date' => (int) $this->CashModel->countOpenTasksWithoutDueDate($settingsID),
                'due_window_days'  => $windowDays,
            ],
            'task_queue' => $this->_task_queue_payload(
                $this->CashModel->openTaskDueQueue($settingsID, null, 6, $windowDays, $today),
                $today
            ),
            'tasks'    => $tasks,
            'projects' => $this->_project_options_payload($this->CashModel->getProjectName($settingsID)),
            'staff'    => $this->_staff_options_payload($this->CashModel->employeeList($settingsID)),
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

        $claims = $this->_require_admin_claims();
        if ($claims === null) {
            return;
        }

        date_default_timezone_set('Asia/Manila');

        $payload      = $this->_read_payload();
        $settingsID   = (int) ($claims['settingsID'] ?? 0);
        $creatorId    = (int) ($claims['user_id'] ?? 0);
        $username     = trim((string) ($claims['username'] ?? ''));
        $today        = date('Y-m-d');

        $task         = $this->_normalize_task_label($payload['task'] ?? '');
        $reportedDate = $this->_normalize_date_input($payload['reported_date'] ?? $payload['reportedDate'] ?? '') ?: $today;
        $dueDate      = $this->_normalize_date_input($payload['due_date'] ?? $payload['dueDate'] ?? '') ?: $reportedDate;
        $projectId    = (int) ($payload['project_id'] ?? $payload['project'] ?? 0);
        $priority     = $this->_normalize_priority($payload['priority'] ?? '2');
        $assignedTo   = (int) ($payload['assigned_person_id'] ?? $payload['assignedPerson'] ?? 0);
        if ($assignedTo <= 0) {
            $assignedTo = $creatorId;
        }
        $attachment   = trim((string) ($payload['attachment_link'] ?? ''));
        $attachment   = $attachment !== '' ? $attachment : null;
        $checklist    = $payload['checklist_items'] ?? [];
        $points       = max(1, (int) ($payload['points'] ?? 1));

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
            'taskID'          => 0,
            'task'            => $task,
            'reportedDate'    => $reportedDate,
            'dueDate'         => $dueDate,
            'projectID'       => $projectId,
            'taskStat'        => '1',
            'priority'        => $priority,
            'settingsID'      => $settingsID,
            'assignedPerson'  => $assignedTo,
            'attachment_link' => $attachment,
            'added_by'        => $username,
        ];
        if ($this->db->field_exists('points', 'projects_task')) {
            $taskInsert['points'] = $points;
        }

        $this->db->insert('projects_task', $taskInsert);
        $taskId = (int) $this->db->insert_id();
        if ($taskId <= 0) {
            return mobile_json(['ok' => false, 'message' => 'Unable to create task.'], 500);
        }

        $this->_create_task_calendar_events($taskId, $task, $reportedDate, $dueDate, $priority, $assignedTo, $settingsID);
        $this->_save_task_checklist($taskId, $settingsID, $checklist);

        return mobile_json([
            'ok'      => true,
            'message' => 'New task has been successfully added.',
            'task_id' => $taskId,
        ]);
    }

    // ── Employee tasks (Page/employeeTask) ───────────────────────────────────

    public function employeeTasks()
    {
        if ($this->_method() === 'OPTIONS') {
            return mobile_json(['ok' => true]);
        }
        if ($this->_method() !== 'GET') {
            return mobile_json(['ok' => false, 'message' => 'Method not allowed.'], 405);
        }

        $claims = $this->_require_admin_claims();
        if ($claims === null) {
            return;
        }

        date_default_timezone_set('Asia/Manila');

        $settingsID = (int) ($claims['settingsID'] ?? 0);
        $today      = date('Y-m-d');
        $taskFilter = strtolower(trim((string) $this->input->get('task_filter')));
        if (!in_array($taskFilter, ['all', 'with_tasks', 'without_tasks'], true)) {
            $taskFilter = 'all';
        }

        $employees = (array) $this->CashModel->employeeList($settingsID);
        $payload   = [];

        foreach ($employees as $emp) {
            $userId = (int) ($emp->user_id ?? 0);
            $pending = $userId > 0
                ? (array) $this->CashModel->getPendingTasksByEmployee($userId, $settingsID)
                : [];
            $count = count($pending);

            if ($taskFilter === 'with_tasks' && $count === 0) {
                continue;
            }
            if ($taskFilter === 'without_tasks' && $count > 0) {
                continue;
            }

            $payload[] = [
                'user_id'       => $userId,
                'name'          => trim((string) (($emp->lName ?? '') . ', ' . ($emp->fName ?? ''))),
                'position'      => (string) ($emp->position ?? ''),
                'email'         => (string) ($emp->email ?? ''),
                'pending_count' => $count,
                'pending_tasks' => array_map(function ($row) use ($today) {
                    return $this->_task_payload_from_row($row, $today);
                }, $pending),
            ];
        }

        return mobile_json([
            'ok'          => true,
            'task_filter' => $taskFilter,
            'projects'    => $this->_project_options_payload($this->CashModel->getProjectName($settingsID)),
            'employees'   => $payload,
        ]);
    }

    // ── Accomplishments (Page/accomplishments) ───────────────────────────────

    public function accomplishments()
    {
        if ($this->_method() === 'OPTIONS') {
            return mobile_json(['ok' => true]);
        }
        if ($this->_method() !== 'GET') {
            return mobile_json(['ok' => false, 'message' => 'Method not allowed.'], 405);
        }

        $claims = $this->_require_admin_claims();
        if ($claims === null) {
            return;
        }

        date_default_timezone_set('Asia/Manila');

        $settingsID   = (int) ($claims['settingsID'] ?? 0);
        $month        = (int) ($this->input->get('month') ?: date('n'));
        $year         = (int) ($this->input->get('year') ?: date('Y'));
        if ($month < 1 || $month > 12) {
            $month = (int) date('n');
        }
        if ($year < 2000) {
            $year = (int) date('Y');
        }

        $pointsEnabled = $this->db->field_exists('points', 'projects_task_stat');
        $rows = (array) $this->CashModel->accomplishmentsAdminFiltered($settingsID, $month, $year);

        return mobile_json([
            'ok'             => true,
            'selected_month' => $month,
            'selected_year'  => $year,
            'points_enabled' => $pointsEnabled,
            'data'           => array_map(function ($row) use ($pointsEnabled) {
                return $this->_accomplishment_payload($row, $pointsEnabled);
            }, $rows),
        ]);
    }

    // ── Employee accomplishment selector + data (Page/employeeAccomplishment) ─

    public function employeeAccomplishments()
    {
        if ($this->_method() === 'OPTIONS') {
            return mobile_json(['ok' => true]);
        }
        if ($this->_method() !== 'GET') {
            return mobile_json(['ok' => false, 'message' => 'Method not allowed.'], 405);
        }

        $claims = $this->_require_admin_claims();
        if ($claims === null) {
            return;
        }

        $settingsID = (int) ($claims['settingsID'] ?? 0);
        $employees  = (array) $this->CashModel->employeeList($settingsID);

        $payload = [];
        foreach ($employees as $emp) {
            $userId = (int) ($emp->user_id ?? 0);
            if ($userId <= 0) {
                continue;
            }
            $payload[] = [
                'user_id'  => $userId,
                'name'     => trim((string) (($emp->lName ?? '') . ', ' . ($emp->fName ?? ''))),
                'position' => (string) ($emp->position ?? ''),
            ];
        }

        return mobile_json(['ok' => true, 'employees' => $payload]);
    }

    public function employeeAccomplishmentData()
    {
        if ($this->_method() === 'OPTIONS') {
            return mobile_json(['ok' => true]);
        }
        if ($this->_method() !== 'GET') {
            return mobile_json(['ok' => false, 'message' => 'Method not allowed.'], 405);
        }

        $claims = $this->_require_admin_claims();
        if ($claims === null) {
            return;
        }

        $settingsID   = (int) ($claims['settingsID'] ?? 0);
        $userIdent    = trim((string) $this->input->get('user_id'));
        $reportPeriod = trim((string) $this->input->get('report_period'));
        $endDateRaw   = trim((string) $this->input->get('end_date'));

        if ($userIdent === '') {
            return mobile_json(['ok' => false, 'message' => 'An employee is required.'], 422);
        }

        $selectedMonth   = null;
        $selectedYear    = null;
        $selectedEndDate = null;

        if ($reportPeriod !== '') {
            $parts = explode('-', $reportPeriod);
            if (count($parts) === 2) {
                $selectedYear  = is_numeric($parts[0]) ? (int) $parts[0] : null;
                $selectedMonth = is_numeric($parts[1]) ? (int) $parts[1] : null;
                if ($selectedMonth !== null && ($selectedMonth < 1 || $selectedMonth > 12)) {
                    $selectedMonth = null;
                }
                if ($selectedYear !== null && $selectedYear < 2000) {
                    $selectedYear = null;
                }
            }
        }
        if ($endDateRaw !== '' && $this->_normalize_date_input($endDateRaw) !== null) {
            $selectedEndDate = $endDateRaw;
        }

        if ($selectedMonth && $selectedYear) {
            $rows = $this->CashModel->accomplishmentsStaffFiltered($settingsID, $userIdent, $selectedMonth, $selectedYear, $selectedEndDate);
        } else {
            $rows = $this->CashModel->accomplishmentsStaffFiltered($settingsID, $userIdent, null, null, $selectedEndDate);
        }

        $pointsEnabled = $this->db->field_exists('points', 'projects_task_stat');
        $employee      = $this->CashModel->getUserFlexible($settingsID, $userIdent);

        return mobile_json([
            'ok'             => true,
            'selected_user'  => $userIdent,
            'selected_month' => $selectedMonth,
            'selected_year'  => $selectedYear,
            'selected_end_date' => $selectedEndDate,
            'points_enabled' => $pointsEnabled,
            'employee'       => $employee ? [
                'user_id'  => (int) ($employee->user_id ?? 0),
                'name'     => trim((string) (($employee->fName ?? '') . ' ' . ($employee->lName ?? ''))),
                'position' => (string) ($employee->position ?? ''),
            ] : null,
            'data' => array_map(function ($row) use ($pointsEnabled) {
                return $this->_accomplishment_payload($row, $pointsEnabled);
            }, (array) $rows),
        ]);
    }

    // ── Attendance (Page/attendanceList, admin = all employees) ───────────────

    public function attendance()
    {
        if ($this->_method() === 'OPTIONS') {
            return mobile_json(['ok' => true]);
        }
        if ($this->_method() !== 'GET') {
            return mobile_json(['ok' => false, 'message' => 'Method not allowed.'], 405);
        }

        $claims = $this->_require_admin_claims();
        if ($claims === null) {
            return;
        }

        date_default_timezone_set('Asia/Manila');

        $settingsID = (int) ($claims['settingsID'] ?? 0);
        $date = trim((string) $this->input->get('date'));
        $from = trim((string) $this->input->get('from'));
        $to   = trim((string) $this->input->get('to'));

        if ($date !== '') {
            $from = $to = $date;
        }
        if ($from === '' && $to === '') {
            $from = $to = date('Y-m-d');
        } else {
            if ($from === '') $from = $to;
            if ($to === '') $to = $from;
        }
        if (strtotime($from) > strtotime($to)) {
            [$from, $to] = [$to, $from];
        }

        $raw    = $this->CashModel->attendanceListByRange($settingsID, $from, $to);
        $result = $this->_aggregateAttendance($raw, $settingsID);

        return mobile_json([
            'ok'              => true,
            'range_from'      => $from,
            'range_to'        => $to,
            'grand_total_all' => $result['grand_total_all'],
            'data'            => $result['data'],
        ]);
    }

    // ── Employee DTR (Page/empDTR) ───────────────────────────────────────────

    public function empDTR()
    {
        if ($this->_method() === 'OPTIONS') {
            return mobile_json(['ok' => true]);
        }
        if ($this->_method() !== 'GET') {
            return mobile_json(['ok' => false, 'message' => 'Method not allowed.'], 405);
        }

        $claims = $this->_require_admin_claims();
        if ($claims === null) {
            return;
        }

        date_default_timezone_set('Asia/Manila');

        $settingsID  = (int) ($claims['settingsID'] ?? 0);
        $selectedId  = trim((string) $this->input->get('id'));
        $selectedNm  = trim((string) $this->input->get('name'));
        $monthInput  = (int) $this->input->get('month');
        $yearInput   = (int) $this->input->get('year');

        $month = ($monthInput >= 1 && $monthInput <= 12) ? $monthInput : (int) date('n');
        $year  = ($yearInput >= 2000 && $yearInput <= 2100) ? $yearInput : (int) date('Y');

        $employees  = (array) $this->CashModel->getStaff($settingsID);
        $identifier = $selectedId !== '' ? $selectedId : $selectedNm;

        $employee = $identifier !== '' ? $this->CashModel->getUserFlexible($settingsID, $identifier) : null;

        $employeeName = 'Employee DTR';
        if ($employee) {
            $employeeName = trim((string) ($employee->fName ?? '') . ' ' . (string) ($employee->lName ?? ''));
        } elseif ($selectedNm !== '') {
            $employeeName = $selectedNm;
        }

        $dtrIdentifier = ($employee && !empty($employee->username)) ? (string) $employee->username : $identifier;

        $dataRows         = [];
        $taskCounts       = [];
        $monthTotalSecs   = 0;
        $presentDays      = 0;
        $absentDays       = 0;
        $pendingDays      = 0;

        if ($dtrIdentifier !== '') {
            $startDate = sprintf('%04d-%02d-01', $year, $month);
            $endDate   = date('Y-m-t', strtotime($startDate));

            $raw = $this->CashModel->attendanceListByEmployeeRange($settingsID, $dtrIdentifier, $startDate, $endDate);
            $aggregated = !empty($raw) ? $this->_aggregateDtrForUser($raw) : [];

            $byDate = [];
            foreach ($aggregated as $item) {
                $ts = strtotime((string) $item->logDate);
                if ($ts !== false) {
                    $item->logDate = date('Y-m-d', $ts);
                }
                $byDate[$item->logDate] = $item;
            }

            $tasks = $this->CashModel->accomplishmentsStaffFiltered($settingsID, $dtrIdentifier, $month, $year);
            foreach ((array) $tasks as $task) {
                if (!empty($task->datePosted)) {
                    $taskDate = date('Y-m-d', strtotime((string) $task->datePosted));
                    $taskCounts[$taskDate] = ($taskCounts[$taskDate] ?? 0) + 1;
                }
            }

            $daysInMonth = (int) date('t', strtotime($startDate));
            $today       = date('Y-m-d');
            $todayYear   = (int) date('Y', strtotime($today));
            $todayMonth  = (int) date('n', strtotime($today));
            $todayDay    = (int) date('j', strtotime($today));
            $lastDay     = $daysInMonth;
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
                $hasOpen  = false;
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
                    'log_date'      => $entry->logDate,
                    'intervals'     => $this->_dtr_interval_labels($entry->intervals ?? []),
                    'am_intervals'  => $this->_dtr_interval_labels($entry->am_intervals ?? []),
                    'pm_intervals'  => $this->_dtr_interval_labels($entry->pm_intervals ?? []),
                    'total_seconds' => (int) ($entry->total_seconds ?? 0),
                    'total_label'   => (string) ($entry->total_label ?? $this->_formatSeconds($entry->total_seconds ?? 0)),
                    'task_count'    => (int) ($taskCounts[$dateKey] ?? 0),
                    'is_absent'     => $isAbsent,
                    'is_pending'    => $isPending,
                ];

                if (!empty($entry->total_seconds)) {
                    $monthTotalSecs += (int) $entry->total_seconds;
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

        $todayPersonnel = array_map(function ($row) {
            return [
                'name'     => trim((string) (($row->fName ?? '') . ' ' . ($row->lName ?? ''))),
                'username' => (string) ($row->username ?? ''),
                'time_in'  => (string) ($row->amTimeIn ?? $row->time_in ?? ''),
            ];
        }, (array) $this->CashModel->getTodayPersonnelWithTimeIn($settingsID, date('Y-m-d')));

        return mobile_json([
            'ok'                     => true,
            'selected_employee'      => $dtrIdentifier,
            'selected_employee_name' => $employeeName,
            'selected_month'         => $month,
            'selected_year'          => $year,
            'filter_applied'         => $dtrIdentifier !== '',
            'month_total_label'      => $this->_formatSeconds($monthTotalSecs),
            'month_total_seconds'    => $monthTotalSecs,
            'present_days'           => $presentDays,
            'absent_days'            => $absentDays,
            'pending_days'           => $pendingDays,
            'employees'              => $this->_staff_select_payload($employees),
            'today_personnel'        => $todayPersonnel,
            'data'                   => $dataRows,
        ]);
    }

    // ── Clients (Page/clientList) ────────────────────────────────────────────

    public function clients()
    {
        if ($this->_method() === 'OPTIONS') {
            return mobile_json(['ok' => true]);
        }
        if ($this->_method() !== 'GET') {
            return mobile_json(['ok' => false, 'message' => 'Method not allowed.'], 405);
        }

        $claims = $this->_require_admin_claims();
        if ($claims === null) {
            return;
        }

        $settingsID = (int) ($claims['settingsID'] ?? 0);
        $rows = (array) $this->CashModel->clientList($settingsID);

        return mobile_json([
            'ok'           => true,
            'next_cust_id' => $this->_next_client_id($settingsID),
            'clients'      => array_map([$this, '_client_payload'], $rows),
        ]);
    }

    public function createClient()
    {
        if ($this->_method() === 'OPTIONS') {
            return mobile_json(['ok' => true]);
        }
        if ($this->_method() !== 'POST') {
            return mobile_json(['ok' => false, 'message' => 'Method not allowed.'], 405);
        }

        $claims = $this->_require_admin_claims();
        if ($claims === null) {
            return;
        }

        $settingsID = (int) ($claims['settingsID'] ?? 0);
        $payload    = $this->_read_payload();

        $customer = trim((string) ($payload['Customer'] ?? $payload['customer'] ?? ''));
        if ($customer === '') {
            return mobile_json(['ok' => false, 'message' => 'Client/company name is required.'], 422);
        }

        $duplicate = $this->db
            ->where('settingsID', $settingsID)
            ->where('LOWER(Customer)', strtolower($customer))
            ->get('customers')
            ->row();
        if ($duplicate) {
            return mobile_json(['ok' => false, 'message' => 'Client/company name already exists.'], 409);
        }

        $custId = trim((string) ($payload['CustID'] ?? ''));
        if ($custId === '') {
            $custId = $this->_next_client_id($settingsID);
        }

        $data = $this->_client_writable_fields($payload);
        $data['CustID']     = $custId;
        $data['settingsID'] = $settingsID;

        if (!$this->db->insert('customers', $data)) {
            return mobile_json(['ok' => false, 'message' => 'Failed to add client.'], 500);
        }

        return mobile_json([
            'ok'      => true,
            'message' => 'Client added successfully.',
            'cust_id' => $custId,
        ]);
    }

    public function updateClient()
    {
        if ($this->_method() === 'OPTIONS') {
            return mobile_json(['ok' => true]);
        }
        if ($this->_method() !== 'POST') {
            return mobile_json(['ok' => false, 'message' => 'Method not allowed.'], 405);
        }

        $claims = $this->_require_admin_claims();
        if ($claims === null) {
            return;
        }

        $settingsID = (int) ($claims['settingsID'] ?? 0);
        $payload    = $this->_read_payload();
        $custId     = trim((string) ($payload['CustID'] ?? ''));

        if ($custId === '') {
            return mobile_json(['ok' => false, 'message' => 'Client identifier is required.'], 422);
        }

        $existing = $this->db
            ->where('CustID', $custId)
            ->where('settingsID', $settingsID)
            ->get('customers')
            ->row();
        if (!$existing) {
            return mobile_json(['ok' => false, 'message' => 'Client record not found.'], 404);
        }

        $data = $this->_client_writable_fields($payload);

        $this->db->where('CustID', $custId);
        $this->db->where('settingsID', $settingsID);
        if (!$this->db->update('customers', $data)) {
            return mobile_json(['ok' => false, 'message' => 'Failed to update client.'], 500);
        }

        return mobile_json(['ok' => true, 'message' => 'Client updated successfully.']);
    }

    public function deleteClient()
    {
        if ($this->_method() === 'OPTIONS') {
            return mobile_json(['ok' => true]);
        }
        if ($this->_method() !== 'POST') {
            return mobile_json(['ok' => false, 'message' => 'Method not allowed.'], 405);
        }

        $claims = $this->_require_admin_claims();
        if ($claims === null) {
            return;
        }

        $settingsID = (int) ($claims['settingsID'] ?? 0);
        $payload    = $this->_read_payload();
        $custId     = trim((string) ($payload['CustID'] ?? ''));

        if ($custId === '') {
            return mobile_json(['ok' => false, 'message' => 'Client identifier is required.'], 422);
        }

        $this->db->where('CustID', $custId);
        $this->db->where('settingsID', $settingsID);
        if (!$this->db->delete('customers')) {
            return mobile_json(['ok' => false, 'message' => 'Failed to delete client.'], 500);
        }

        return mobile_json(['ok' => true, 'message' => 'Client deleted successfully.']);
    }

    // ── Internals ────────────────────────────────────────────────────────────

    private function _require_admin_claims()
    {
        $claims = mobile_require_claims();
        if ($claims === null) {
            return null;
        }
        if (trim((string) ($claims['level'] ?? '')) !== 'Admin') {
            mobile_json(['ok' => false, 'message' => 'Admin access required.'], 403);
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

    private function _normalize_task_label($value)
    {
        $value = preg_replace('/\s+/', ' ', trim((string) $value));
        if ($value === '') {
            return '';
        }
        return function_exists('mb_strtoupper') ? mb_strtoupper($value, 'UTF-8') : strtoupper($value);
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

    private function _task_queue_payload($rows, $today)
    {
        $queue = [];
        foreach ((array) $rows as $row) {
            $reported = (string) ($row->reportedDate ?? '');
            $due      = (string) ($row->dueDate ?? '');
            $queue[] = [
                'id'            => (int) ($row->taskID ?? 0),
                'title'         => (string) ($row->task ?? 'Untitled task'),
                'subtitle'      => (string) ($row->projectDescription ?? ''),
                'assigned_name' => trim((string) ($row->assignedPersonName ?? '')),
                'reported_date' => $reported,
                'due_date'      => $due,
                'priority'      => (string) ($row->priority ?? ''),
                'progress'      => $this->_progressBetween($reported, $due, $today),
            ];
        }
        return $queue;
    }

    private function _task_payload_from_row($row, $today)
    {
        $taskId    = (int) ($row->taskID ?? 0);
        $taskTitle = trim((string) ($row->task ?? '')) ?: 'Untitled task';
        $reported  = trim((string) ($row->reportedDate ?? ''));
        $due       = trim((string) ($row->dueDate ?? ''));
        $priority  = $this->_normalize_priority((string) ($row->priority ?? '2'));
        $stat      = (string) ($row->taskStat ?? '1');
        $dueMeta   = $this->_task_due_meta($due, $today);

        return [
            'id'                   => $taskId,
            'title'                => $taskTitle,
            'reported_date'        => $reported,
            'due_date'             => $due,
            'status_value'         => $stat,
            'status'               => $stat === '0' ? 'closed' : 'open',
            'priority_value'       => $priority,
            'priority_label'       => self::PRIORITY_LABELS[$priority],
            'project_id'           => (int) ($row->projectID ?? 0),
            'project_name'         => trim((string) ($row->projectDescription ?? '')) ?: 'No project',
            'assigned_person_id'   => (int) ($row->assignedPerson ?? 0),
            'assigned_person_name' => trim((string) ($row->assignedPersonName ?? '')) ?: 'Unassigned',
            'attachment_link'      => (string) ($row->attachment_link ?? ''),
            'admin_comment'        => (string) ($row->latestAdminComment ?? ''),
            'added_by'             => (string) ($row->added_by ?? ''),
            'due_meta_label'       => $dueMeta['label'],
            'due_meta_type'        => $dueMeta['type'],
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
                'id'   => (int) ($row->projectID ?? 0),
                'name' => (string) ($row->projectDescription ?? ''),
            ];
        }, (array) $rows);
    }

    private function _staff_options_payload($rows)
    {
        $payload = [];
        foreach ((array) $rows as $row) {
            $userId = (int) ($row->user_id ?? 0);
            if ($userId <= 0) {
                continue;
            }
            $payload[] = [
                'user_id' => $userId,
                'name'    => trim((string) (($row->lName ?? '') . ', ' . ($row->fName ?? ''))),
            ];
        }
        return $payload;
    }

    private function _staff_select_payload($rows)
    {
        $payload = [];
        foreach ((array) $rows as $row) {
            $name = trim((string) (($row->fName ?? '') . ' ' . ($row->lName ?? '')));
            $payload[] = [
                'user_id'  => (int) ($row->user_id ?? 0),
                'username' => (string) ($row->username ?? ''),
                'name'     => $name !== '' ? $name : (string) ($row->username ?? ''),
            ];
        }
        return $payload;
    }

    private function _accomplishment_payload($row, $pointsEnabled)
    {
        $type = (string) ($row->accomplishment_type ?? 'task');
        if ($type === 'calendar') {
            $title = trim((string) ($row->title ?? $row->task ?? ''));
            $note  = trim((string) ($row->description ?? ''));
            $date  = (string) ($row->completed_at ?? $row->datePosted ?? '');
        } else {
            $title = trim((string) ($row->task ?? ''));
            $note  = trim((string) ($row->note ?? ''));
            $date  = (string) ($row->datePosted ?? '');
        }

        $priorityRaw = trim((string) ($row->priority ?? ''));
        return [
            'type'                 => $type,
            'task_id'              => (int) ($row->taskID ?? 0),
            'title'                => $title !== '' ? $title : 'Accomplishment',
            'note'                 => $note,
            'project_name'         => trim((string) ($row->projectDescription ?? '')),
            'assigned_person_name' => trim((string) ($row->assignedPersonName ?? '')),
            'date_posted'          => $date,
            'priority_label'       => array_key_exists($priorityRaw, self::PRIORITY_LABELS)
                ? self::PRIORITY_LABELS[$priorityRaw]
                : '',
            'points'               => $pointsEnabled ? (int) ($row->points ?? 0) : null,
        ];
    }

    private function _client_payload($row)
    {
        return [
            'cust_id'        => (string) ($row->CustID ?? ''),
            'customer'       => (string) ($row->Customer ?? ''),
            'address'        => (string) ($row->Address ?? ''),
            'contact'        => (string) ($row->ContactNos ?? ''),
            'contact_person' => (string) ($row->ContactPerson ?? ''),
            'company_email'  => (string) ($row->CompanyEmail ?? ''),
            'client_email'   => (string) ($row->client_email ?? ''),
            'client_stat'    => (string) ($row->ClientStat ?? ''),
            'client_source'  => (string) ($row->client_source ?? ''),
            'facebook_link'  => (string) ($row->facebook_link ?? ''),
            'sales_agent'    => (string) ($row->sales_agent ?? ''),
            'notes'          => (string) ($row->notes ?? ''),
            'created_at'     => (string) ($row->created_at ?? ''),
        ];
    }

    /**
     * Maps the JSON payload to the customers columns. Portal account fields are
     * intentionally left out — toggling client-portal credentials stays a
     * web-only admin action for now.
     */
    private function _client_writable_fields($payload)
    {
        return [
            'Customer'      => trim((string) ($payload['Customer'] ?? $payload['customer'] ?? '')),
            'Address'       => trim((string) ($payload['Address'] ?? $payload['address'] ?? '')),
            'ContactNos'    => trim((string) ($payload['Contact'] ?? $payload['contact'] ?? '')),
            'ContactPerson' => trim((string) ($payload['ContactPerson'] ?? $payload['contact_person'] ?? '')),
            'CompanyEmail'  => trim((string) ($payload['CompanyEmail'] ?? $payload['company_email'] ?? '')),
            'ClientStat'    => trim((string) ($payload['ClientStat'] ?? $payload['client_stat'] ?? 'Active')) ?: 'Active',
            'client_source' => trim((string) ($payload['client_source'] ?? '')),
            'facebook_link' => trim((string) ($payload['facebook_link'] ?? '')),
            'client_email'  => trim((string) ($payload['client_email'] ?? '')),
            'sales_agent'   => trim((string) ($payload['sales_agent'] ?? '')),
            'notes'         => trim((string) ($payload['notes'] ?? '')),
        ];
    }

    private function _next_client_id($settingsID)
    {
        $next = (string) $settingsID . '10001';
        $rows = $this->CashModel->getCustID();
        if (!empty($rows) && isset($rows[0]->CustID) && is_numeric($rows[0]->CustID)) {
            $next = (string) (((int) $rows[0]->CustID) + 1);
        }
        return $next;
    }

    private function _create_task_calendar_events($taskId, $task, $reportedDate, $dueDate, $priority, $assignedTo, $settingsID)
    {
        if ($taskId <= 0 || !$this->db->table_exists('calendar_events')) {
            return;
        }

        $assignedUsers = strpos((string) $assignedTo, ',') !== false
            ? array_map('trim', explode(',', (string) $assignedTo))
            : [(string) $assignedTo];

        $userIds = [];
        foreach ($assignedUsers as $assignedUser) {
            if (is_numeric($assignedUser)) {
                $userIds[] = (int) $assignedUser;
            } else {
                $user = $this->db->where('username', $assignedUser)
                    ->where('settingsID', $settingsID)
                    ->get('users')
                    ->row();
                if ($user && isset($user->user_id)) {
                    $userIds[] = (int) $user->user_id;
                }
            }
        }
        $color = $priority == '1' ? '#dc3545' : ($priority == '2' ? '#ffc107' : '#28a745');
        foreach ($userIds as $userId) {
            if ($userId <= 0) {
                continue;
            }
            $this->db->insert('calendar_events', [
                'title'        => $task,
                'description'  => 'Task: ' . $task,
                'start_date'   => $reportedDate,
                'end_date'     => $dueDate,
                'all_day'      => 1,
                'color'        => $color,
                'event_type'   => 'task',
                'user_id'      => $userId,
                'settingsID'   => $settingsID,
                'status'       => 'active',
                'task_id'      => $taskId,
                'is_public'    => 1,
                'is_completed' => 1,
                'created_at'   => date('Y-m-d H:i:s'),
                'updated_at'   => date('Y-m-d H:i:s'),
            ]);
        }
    }

    private function _save_task_checklist($taskId, $settingsID, $items)
    {
        if (empty($items) || !is_array($items) || $taskId <= 0) {
            return;
        }
        foreach ($items as $item) {
            $item = trim((string) $item);
            if ($item === '') {
                continue;
            }
            $this->db->insert('task_checklist', [
                'taskID'          => $taskId,
                'itemDescription' => $item,
                'isCompleted'     => 0,
                'settingsID'      => $settingsID,
            ]);
        }
    }

    private function _dtr_interval_labels($intervals)
    {
        return array_map(function ($intv) {
            return [
                'label'   => (string) ($intv['label'] ?? ''),
                'seconds' => (int) ($intv['seconds'] ?? 0),
                'open'    => (bool) ($intv['open'] ?? false),
            ];
        }, (array) $intervals);
    }

    /**
     * Groups raw attendance rows by employee+date for the admin attendance
     * view. Mirrors Page::_aggregateAttendance, taking settingsID explicitly
     * instead of reading it from the session.
     */
    private function _aggregateAttendance($rows, $settingsID)
    {
        $grouped = [];
        $grandTotals = [];
        $overallSeconds = 0;

        foreach ((array) $rows as $row) {
            $empKey = !empty($row->IDNumber)
                ? $row->IDNumber
                : (!empty($row->user_id) ? $row->user_id : (!empty($row->username) ? $row->username : ''));
            $dateKey = $row->logDate;
            $fullKey = $empKey . '|' . $dateKey;

            $intervals = [];
            foreach (
                [
                    ['time_in' => $row->amTimeIn ?? '', 'time_out' => $row->amTimeOut ?? ''],
                    ['time_in' => $row->pmTimeIn ?? '', 'time_out' => $row->pmTimeOut ?? ''],
                ] as $pair
            ) {
                $timeIn  = trim((string) $pair['time_in']);
                $timeOut = trim((string) $pair['time_out']);
                if ($timeIn === '') {
                    continue;
                }
                $start = $this->_parse_time($dateKey, $timeIn);
                if (!$start) {
                    continue;
                }
                if ($timeOut !== '') {
                    $end = $this->_parse_time($dateKey, $timeOut);
                    if ($end && $end > $start) {
                        $intervals[] = [
                            'label'   => date('g:i A', $start) . ' - ' . date('g:i A', $end),
                            'seconds' => $end - $start,
                            'start'   => $start,
                        ];
                        continue;
                    }
                }
                $intervals[] = [
                    'label'   => date('g:i A', $start) . ' - Time out pending',
                    'seconds' => 0,
                    'start'   => $start,
                ];
            }

            if (!isset($grouped[$fullKey])) {
                $grouped[$fullKey] = [
                    'logDate'              => $dateKey,
                    'IDNumber'             => $row->IDNumber ?? '',
                    'user_id'              => $row->user_id ?? null,
                    'username'             => $row->username ?? null,
                    'fName'                => $row->fName ?? '',
                    'mName'                => $row->mName ?? '',
                    'lName'                => $row->lName ?? '',
                    'accomplishment_count' => 0,
                    'has_time_in'          => false,
                    'intervals'            => [],
                    'total_seconds'        => 0,
                ];
            }

            if (!empty($row->amTimeIn) || !empty($row->pmTimeIn)) {
                $grouped[$fullKey]['has_time_in'] = true;
            }

            foreach ($intervals as $intv) {
                $secs = (int) ($intv['seconds'] ?? 0);
                $grouped[$fullKey]['intervals'][] = $intv;
                if ($secs <= 0) {
                    continue;
                }
                $grouped[$fullKey]['total_seconds'] += $secs;
                if ($empKey !== '') {
                    $grandTotals[$empKey] = ($grandTotals[$empKey] ?? 0) + $secs;
                }
                $overallSeconds += $secs;
            }

            if ($empKey !== '' && !empty($row->logDate)) {
                $grouped[$fullKey]['accomplishment_count'] =
                    $this->CashModel->accomplishmentCountForDate($settingsID, $empKey, $row->logDate);
            }
        }

        $data = [];
        foreach ($grouped as $g) {
            if (!empty($g['intervals'])) {
                usort($g['intervals'], function ($a, $b) {
                    return ($a['start'] ?? 0) <=> ($b['start'] ?? 0);
                });
            }
            $empKey = !empty($g['IDNumber'])
                ? $g['IDNumber']
                : (!empty($g['user_id']) ? $g['user_id'] : (!empty($g['username']) ? $g['username'] : ''));

            $name = trim((string) ($g['fName'] . ' ' . $g['lName']));
            $data[] = [
                'log_date'             => $g['logDate'],
                'employee_name'        => $name !== '' ? $name : (string) ($g['username'] ?? ''),
                'id_number'            => (string) $g['IDNumber'],
                'user_id'              => $g['user_id'],
                'username'             => $g['username'],
                'accomplishment_count' => (int) $g['accomplishment_count'],
                'has_time_in'          => (bool) $g['has_time_in'],
                'total_seconds'        => (int) $g['total_seconds'],
                'total_hours_label'    => $this->_formatSeconds($g['total_seconds']),
                'grand_total_label'    => $empKey !== ''
                    ? $this->_formatSeconds($grandTotals[$empKey] ?? 0)
                    : $this->_formatSeconds($g['total_seconds']),
                'intervals'            => array_map(function ($intv) {
                    return [
                        'label'   => (string) ($intv['label'] ?? ''),
                        'seconds' => (int) ($intv['seconds'] ?? 0),
                    ];
                }, $g['intervals']),
            ];
        }

        return [
            'data'            => $data,
            'grand_total_all' => $this->_formatSeconds($overallSeconds),
        ];
    }

    private function _aggregateDtrForUser($rows)
    {
        $grouped = [];
        foreach ((array) $rows as $row) {
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
                $end   = $this->_parse_time($dateKey, $row->amTimeOut);
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
                $end   = $this->_parse_time($dateKey, $row->pmTimeOut);
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
                    $entry = ['label' => $intv['label'], 'seconds' => 0, 'open' => true, 'start' => $intv['start'] ?? 0];
                    $grouped[$dateKey]['intervals'][] = $entry;
                    $bucket = 'am_intervals';
                    if (isset($intv['start']) && (int) date('G', $intv['start']) >= 12) {
                        $bucket = 'pm_intervals';
                    } elseif (stripos($intv['label'], 'PM') !== false) {
                        $bucket = 'pm_intervals';
                    }
                    $grouped[$dateKey][$bucket][] = $entry;
                } else {
                    $secs  = $intv[1] - $intv[0];
                    $label = date('g:i A', $intv[0]) . ' - ' . date('g:i A', $intv[1]);
                    $entry = ['label' => $label, 'seconds' => $secs, 'open' => false, 'start' => $intv[0]];
                    $grouped[$dateKey]['intervals'][] = $entry;
                    $bucket = 'am_intervals';
                    if ((int) date('G', $intv[1]) >= 12 || (int) date('G', $intv[0]) >= 12) {
                        $bucket = 'pm_intervals';
                    }
                    $grouped[$dateKey][$bucket][] = $entry;
                    $grouped[$dateKey]['total_seconds'] += $secs;
                }
            }
        }

        foreach ($grouped as &$g) {
            foreach (['intervals', 'am_intervals', 'pm_intervals'] as $bucket) {
                if (!empty($g[$bucket])) {
                    usort($g[$bucket], function ($a, $b) {
                        return ($a['start'] ?? 0) <=> ($b['start'] ?? 0);
                    });
                }
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

        $formats = ['Y-m-d g:i:s A', 'Y-m-d g:i A', 'Y-m-d H:i:s', 'Y-m-d H:i', 'Y-m-d H:i:s A', 'Y-m-d H:i A'];
        foreach ($formats as $format) {
            $dt = DateTime::createFromFormat($format, $dateKey . ' ' . $timeValue);
            if ($dt instanceof DateTime) {
                $errors = DateTime::getLastErrors();
                if ((!is_array($errors) || empty($errors['warning_count'])) && (!is_array($errors) || empty($errors['error_count']))) {
                    return $dt->getTimestamp();
                }
            }
        }

        $stripped = preg_replace('/\s*(AM|PM)$/i', '', $timeValue);
        if ($stripped !== null && $stripped !== $timeValue) {
            foreach (['Y-m-d H:i:s', 'Y-m-d H:i'] as $format) {
                $dt = DateTime::createFromFormat($format, $dateKey . ' ' . $stripped);
                if ($dt instanceof DateTime) {
                    $errors = DateTime::getLastErrors();
                    if ((!is_array($errors) || empty($errors['warning_count'])) && (!is_array($errors) || empty($errors['error_count']))) {
                        return $dt->getTimestamp();
                    }
                }
            }
        }

        $ts = strtotime($dateKey . ' ' . $timeValue);
        return $ts === false ? null : $ts;
    }

    private function _formatSeconds($seconds)
    {
        $seconds = max(0, (int) $seconds);
        $hours   = (int) floor($seconds / 3600);
        $minutes = (int) floor(($seconds % 3600) / 60);
        return sprintf('%02d:%02d', $hours, $minutes);
    }
}
