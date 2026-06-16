<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Calendar extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->helper('url');
        $this->load->helper('form');
        $this->load->library('session');
        $this->load->database();
        
        // Check if user is logged in
        if (!$this->session->userdata('logged_in')) {
            redirect('Login');
        }

        // Check if calendar feature is enabled for this company
        if (!$this->_is_feature_enabled('calendar')) {
            show_error('Calendar module is not enabled for your company. Please contact your administrator.', 403, 'Feature Not Available');
        }

        $this->_ensure_calendar_schema();
    }

    protected function _is_feature_enabled($featureKey) {
        // Super Admin has access to all features
        if ($this->session->userdata('level') === 'Super Admin') {
            return true;
        }

        $settingsID = $this->session->userdata('settingsID');
        
        if (!$settingsID) {
            return false;
        }

        if (!$this->db->table_exists('company_features')) {
            return true;
        }

        $activeFeatureCount = (int) $this->db
            ->where('settingsID', $settingsID)
            ->where('is_enabled', 1)
            ->count_all_results('company_features');

        if ($activeFeatureCount === 0) {
            return true;
        }

        // Check if feature is enabled for this company
        $this->db->where('settingsID', $settingsID);
        $this->db->where('feature_key', $featureKey);
        $this->db->where('is_enabled', 1);
        $query = $this->db->get('company_features');

        return $query->num_rows() > 0;
    }

    public function index() {
        $data['title'] = 'Calendar';
        $data['username'] = $this->session->userdata('username');
        $data['user_id'] = $this->session->userdata('user_id');
        $data['settingsID'] = $this->session->userdata('settingsID');
        $data['user_level'] = $this->session->userdata('level');
        
        $this->load->view('calendar', $data);
    }

    public function get_events() {
        header('Content-Type: application/json');
        
        $user_id = $this->session->userdata('user_id');
        $settingsID = $this->session->userdata('settingsID');
        $start = $this->input->get('start');
        $end = $this->input->get('end');

        // Check if user is logged in
        if (!$user_id) {
            echo json_encode(array('error' => 'User not logged in'));
            return;
        }

        // Check if table exists
        if (!$this->db->table_exists('calendar_events')) {
            echo json_encode(array('error' => 'Calendar events table does not exist'));
            return;
        }

        try {
            // Build: only own events (public+private)
            $date_filter_start = $start ? "AND start_date >= '$start'" : '';
            $date_filter_end   = $end   ? "AND end_date <= '$end'"     : '';

            $sql = "
                SELECT *, 1 AS own FROM calendar_events
                WHERE user_id = ? AND settingsID = ? AND status = 'active'
                $date_filter_start $date_filter_end

                ORDER BY start_date ASC
            ";

            $query  = $this->db->query($sql, array($user_id, $settingsID));
            $events = $query->result();

            $calendar_events = array();
            foreach ($events as $event) {
                $is_own    = (bool) $event->own;
                $is_public = (bool) $event->is_public;
                $calendar_events[] = array(
                    'id'           => $event->id,
                    'title'        => $event->title,
                    'description'  => $event->description,
                    'notes'        => isset($event->notes) ? $event->notes : '',
                    'start'        => $event->start_date,
                    'end'          => $event->end_date,
                    'allDay'       => (bool) $event->all_day,
                    'color'        => $event->color,
                    'type'         => $event->event_type,
                    'location'     => $event->location,
                    'reminder_time'=> $event->reminder_time,
                    'reminder_email_enabled' => !empty($event->reminder_email_enabled),
                    'reminder_email' => isset($event->reminder_email) ? $event->reminder_email : '',
                    'is_public'    => $is_public,
                    'is_completed' => (bool) (isset($event->is_completed) ? $event->is_completed : 1),
                    'task_id'      => isset($event->task_id) ? $event->task_id : null,
                    'status'       => $is_public ? 'public' : 'private',
                    'own'          => $is_own,
                    'canEdit'      => $is_own,
                    'canDelete'    => $is_own
                );
            }

            // Tasks are now converted to calendar events in projectAddTask/updateTask
            // No need to fetch tasks separately here to avoid duplicates

            echo json_encode($calendar_events);
        } catch (Exception $e) {
            error_log("Calendar Get Events - Error: " . $e->getMessage());
            echo json_encode(array('error' => 'Database error: ' . $e->getMessage()));
        }
    }

    public function save_event() { $this->add_event(); }

    public function add_event() {
        header('Content-Type: application/json');
        
        $user_id = $this->session->userdata('user_id');
        $settingsID = $this->session->userdata('settingsID');

        // Check if user is logged in
        if (!$user_id) {
            echo json_encode(array('success' => false, 'message' => 'User not logged in'));
            return;
        }

        $payload = $this->_build_event_payload();
        if (!$payload['success']) {
            echo json_encode(array('success' => false, 'message' => $payload['message']));
            return;
        }

        // Only create task if explicitly requested (for events from projectAddTask)
        $create_as_task = isset($payload['data']['create_as_task']) ? (int) $payload['data']['create_as_task'] : 0;
        $project_id = isset($payload['data']['project_id']) ? (int) $payload['data']['project_id'] : 0;
        $priority = isset($payload['data']['priority']) ? $payload['data']['priority'] : '3';

        // Create task only when explicitly flagged (for events from projectAddTask)
        $task_id = 0;
        if ($create_as_task === 1 && $this->db->table_exists('projects_task') && $user_id > 0) {
            $task_data = array(
                'task' => $payload['data']['title'],
                'reportedDate' => date('Y-m-d', strtotime($payload['data']['start_date'])),
                'dueDate' => date('Y-m-d', strtotime($payload['data']['end_date'])),
                'projectID' => $project_id > 0 ? $project_id : 0,
                'taskStat' => '1', // Open
                'priority' => $priority,
                'settingsID' => $settingsID,
                'assignedPerson' => $user_id,
                'added_by' => $this->session->userdata('username')
            );

            try {
                $this->db->insert('projects_task', $task_data);
                $task_id = (int) $this->db->insert_id();

                if ($task_id > 0) {
                    $payload['data']['task_id'] = $task_id;
                    $payload['data']['event_type'] = 'task';
                    error_log("Calendar Add Event - Task created: ID $task_id for user $user_id");
                } else {
                    $dbError = $this->db->error();
                    error_log("Calendar Add Event - Task creation failed, no insert ID returned. DB Error: " . print_r($dbError, true));
                }
            } catch (Exception $e) {
                error_log("Calendar Add Event - Task creation error: " . $e->getMessage());
                // Continue with event creation even if task fails
            }
        }

        // Debug: Log received data
        error_log("Calendar Add Event - User ID: $user_id, SettingsID: $settingsID");
        error_log("Calendar Add Event - Title: {$payload['data']['title']}, Start: {$payload['data']['start_date']}, End: {$payload['data']['end_date']}");

        // Prepare event data - default to not completed (is_completed = 1)
        $event_data = array_merge($payload['data'], array(
            'user_id' => $user_id,
            'settingsID' => $settingsID,
            'status' => 'active',
            'is_completed' => 1, // Default to not completed
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ));

        // Insert event
        try {
            $result = $this->db->insert('calendar_events', $event_data);
            $dbError = $this->db->error();
            $event_id = (int) $this->db->insert_id();

            if ($result && $event_id > 0) {
                echo json_encode(array('success' => true, 'event_id' => $event_id, 'message' => 'Event created successfully'));
                return;
            }

            $errorMessage = !empty($dbError['message']) ? $dbError['message'] : 'Failed to create event';
            error_log('Calendar Add Event DB Error: ' . $errorMessage);
            echo json_encode(array('success' => false, 'message' => $errorMessage));
        } catch (Exception $e) {
            error_log('Calendar Add Event Exception: ' . $e->getMessage());
            echo json_encode(array('success' => false, 'message' => 'Error: ' . $e->getMessage()));
        }
    }

    public function update_event() {
        header('Content-Type: application/json');
        $user_id = $this->session->userdata('user_id');
        $username = $this->session->userdata('username');
        $settingsID = $this->session->userdata('settingsID');
        $event_id = $this->input->post('event_id');

        // Check if it's a task event by looking at event_type or task_id
        $event_id_int = (int) $event_id;
        $this->db->where('id', $event_id_int);
        $this->db->where('settingsID', $settingsID);
        $query = $this->db->get('calendar_events');
        
        if ($query->num_rows() > 0) {
            $event = $query->row();
            if (isset($event->event_type) && $event->event_type === 'task') {
                echo json_encode(array('success' => false, 'message' => 'Task events cannot be modified from the calendar. Please use the Task Management page to edit tasks.'));
                return;
            }
            if (isset($event->task_id) && !empty($event->task_id)) {
                echo json_encode(array('success' => false, 'message' => 'Task events cannot be modified from the calendar. Please use the Task Management page to edit tasks.'));
                return;
            }
        }

        // Regular calendar event update
        $event_id = (int) $event_id;

        // Check if event exists and belongs to user
        $this->db->where('id', $event_id);
        $this->db->where('user_id', $user_id);
        $this->db->where('settingsID', $settingsID);
        $query = $this->db->get('calendar_events');

        if ($query->num_rows() == 0) {
            echo json_encode(array('success' => false, 'message' => 'Event not found or you don\'t have permission to edit it'));
            return;
        }

        $payload = $this->_build_event_payload();
        if (!$payload['success']) {
            echo json_encode(array('success' => false, 'message' => $payload['message']));
            return;
        }

        // Get current event to preserve is_completed if not being updated
        $current_event = $query->row();
        $is_completed = $this->input->post('is_completed');
        
        // Prepare update data
        $update_data = array_merge($payload['data'], array(
            'updated_at' => date('Y-m-d H:i:s'),
        ));
        
        // If is_completed is being sent, use it; otherwise preserve current value
        if ($is_completed !== null) {
            $update_data['is_completed'] = (int) $is_completed;
        } else {
            $update_data['is_completed'] = isset($current_event->is_completed) ? (int) $current_event->is_completed : 1;
        }

        // Update event
        $this->db->where('id', $event_id);
        $this->db->where('user_id', $user_id);
        $this->db->where('settingsID', $settingsID);
        $result = $this->db->update('calendar_events', $update_data);
        $dbError = $this->db->error();

        if ($result) {
            echo json_encode(array('success' => true, 'message' => 'Event updated successfully'));
        } else {
            $errorMessage = !empty($dbError['message']) ? $dbError['message'] : 'Failed to update event';
            error_log('Calendar Update Event DB Error: ' . $errorMessage);
            echo json_encode(array('success' => false, 'message' => $errorMessage));
        }
    }

    public function delete_event() {
        header('Content-Type: application/json');
        $user_id = $this->session->userdata('user_id');
        $username = $this->session->userdata('username');
        $settingsID = $this->session->userdata('settingsID');
        $user_level = $this->session->userdata('level');
        $event_id_param = $this->input->post('event_id') ?: $this->input->post('id');

        error_log("Delete Event - Event ID param: $event_id_param, User ID: $user_id, Username: $username, SettingsID: $settingsID, Level: $user_level");

        // Check if it's a task (starts with 'task_')
        if (strpos($event_id_param, 'task_') === 0) {
            echo json_encode(array('success' => false, 'message' => 'Task events cannot be deleted from the calendar. Please use the Task Management page to delete tasks.'));
            return;
        }

        $event_id = (int) $event_id_param;
        
        // Check if event exists
        $this->db->where('id', $event_id);
        $query = $this->db->get('calendar_events');

        if ($query->num_rows() == 0) {
            error_log("Delete Event - Event not found with ID: $event_id");
            echo json_encode(array('success' => false, 'message' => 'Event not found'));
            return;
        }

        $event = $query->row();

        // Check if it's a task event by looking at event_type or task_id
        if (isset($event->event_type) && $event->event_type === 'task') {
            echo json_encode(array('success' => false, 'message' => 'Task events cannot be deleted from the calendar. Please use the Task Management page to delete tasks.'));
            return;
        }
        if (isset($event->task_id) && !empty($event->task_id)) {
            echo json_encode(array('success' => false, 'message' => 'Task events cannot be deleted from the calendar. Please use the Task Management page to delete tasks.'));
            return;
        }

        error_log("Delete Event - Event found. Owner: {$event->user_id}, Settings: {$event->settingsID}, Task ID: " . (isset($event->task_id) ? $event->task_id : 'none'));

        // Check if user is admin or the event owner
        $is_admin = ($user_level === 'Admin' || $user_level === 'Super Admin');
        $is_owner = ($event->user_id == $user_id && $event->settingsID == $settingsID);
        
        // Check if user is assigned to the task linked to this event or is the task creator
        $is_assigned = false;
        $is_task_creator = false;
        if (isset($event->task_id) && $event->task_id > 0) {
            $this->db->where('taskID', $event->task_id);
            $task_query = $this->db->get('projects_task');
            
            if ($task_query->num_rows() > 0) {
                $task = $task_query->row();
                $assignedPerson = $task->assignedPerson;
                $addedBy = $task->added_by;
                
                error_log("Delete Event - Task found. Task ID: {$event->task_id}, assignedPerson: $assignedPerson, added_by: $addedBy");
                
                // Check if user is the task creator
                if ($addedBy == $username) {
                    $is_task_creator = true;
                    error_log("Delete Event - User is task creator");
                }
                
                // Check if user is assigned (by ID or username)
                if ($assignedPerson == $user_id || $assignedPerson == $username) {
                    $is_assigned = true;
                    error_log("Delete Event - User assigned via direct match");
                }
                
                // Check if assignedPerson contains multiple users (comma-separated)
                if (!$is_assigned) {
                    $assigned_users = explode(',', $assignedPerson);
                    foreach ($assigned_users as $assigned_user) {
                        $assigned_user = trim($assigned_user);
                        if ($assigned_user == $user_id || $assigned_user == $username) {
                            $is_assigned = true;
                            error_log("Delete Event - User assigned via comma-separated list");
                            break;
                        }
                    }
                }
                
                // Also check if user_id matches assignedPerson as integer
                if (!$is_assigned && is_numeric($assignedPerson) && (int)$assignedPerson == $user_id) {
                    $is_assigned = true;
                    error_log("Delete Event - User assigned via numeric match");
                }
            } else {
                error_log("Delete Event - Task not found with ID: {$event->task_id}");
            }
        } else {
            error_log("Delete Event - Event has no task_id or task_id is 0");
        }

        error_log("Delete Event - Permission check: IsAdmin=$is_admin, IsOwner=$is_owner, IsAssigned=$is_assigned, IsTaskCreator=$is_task_creator");

        if (!$is_admin && !$is_owner && !$is_assigned && !$is_task_creator) {
            error_log("Delete Event - Permission denied. User: $user_id, Owner: {$event->user_id}");
            echo json_encode(array('success' => false, 'message' => 'You don\'t have permission to delete this event'));
            return;
        }

        // Delete the event
        $this->db->where('id', $event_id);
        $result = $this->db->delete('calendar_events');

        if ($this->db->affected_rows() > 0) {
            error_log("Delete Event - Event deleted successfully: $event_id");
            echo json_encode(array('success' => true, 'message' => 'Event deleted successfully'));
        } else {
            error_log("Delete Event - Failed to delete event: $event_id");
            echo json_encode(array('success' => false, 'message' => 'Failed to delete event'));
        }
    }

    public function toggle_completion() {
        header('Content-Type: application/json');
        $user_id = $this->session->userdata('user_id');
        $username = $this->session->userdata('username');
        $settingsID = $this->session->userdata('settingsID');
        $event_id = (int) $this->input->post('event_id');
        $is_completed = (int) $this->input->post('is_completed');

        // Check if it's a task event by looking at event_type or task_id
        $this->db->where('id', $event_id);
        $this->db->where('settingsID', $settingsID);
        $query = $this->db->get('calendar_events');
        
        if ($query->num_rows() > 0) {
            $event = $query->row();
            if (isset($event->event_type) && $event->event_type === 'task') {
                echo json_encode(array('success' => false, 'message' => 'Task completion cannot be toggled from the calendar. Please use the Task Management page to mark tasks as complete.'));
                return;
            }
            if (isset($event->task_id) && !empty($event->task_id)) {
                echo json_encode(array('success' => false, 'message' => 'Task completion cannot be toggled from the calendar. Please use the Task Management page to mark tasks as complete.'));
                return;
            }
        }

        // Check if it's a task (starts with 'task_')
        if (strpos($event_id, 'task_') === 0) {
            echo json_encode(array('success' => false, 'message' => 'Task completion cannot be toggled from the calendar. Please use the Task Management page to mark tasks as complete.'));
            return;
        }

        // Check if event exists and belongs to user
        $this->db->where('id', $event_id);
        $this->db->where('user_id', $user_id);
        $this->db->where('settingsID', $settingsID);
        $query = $this->db->get('calendar_events');

        if ($query->num_rows() == 0) {
            error_log("Toggle Completion - Event not found. Event ID: $event_id, User ID: $user_id, SettingsID: $settingsID");
            echo json_encode(array('success' => false, 'message' => 'Event not found or you don\'t have permission to edit it'));
            return;
        }

        $event = $query->row();
        error_log("Toggle Completion - Event found. Event ID: $event_id, Task ID: " . (isset($event->task_id) ? $event->task_id : 'none') . ", is_completed: $is_completed");

        // Update event completion
        $this->db->where('id', $event_id);
        $this->db->where('user_id', $user_id);
        $this->db->where('settingsID', $settingsID);
        $result = $this->db->update('calendar_events', array('is_completed' => $is_completed));
        
        error_log("Toggle Completion - Event update result: " . ($result ? 'success' : 'failed'));

        // If event is linked to a task, sync task status
        if ($result && isset($event->task_id) && $event->task_id > 0) {
            $task_stat = ($is_completed == 0) ? '0' : '1';
            $update_data = array('taskStat' => $task_stat);
            
            error_log("Toggle Completion - Syncing task {$event->task_id} to status $task_stat");

            // Set completed_by field when marking as complete
            if ($is_completed == 0 && $this->db->field_exists('completed_by', 'projects_task')) {
                $update_data['completed_by'] = $user_id;
                error_log("Toggle Completion - Setting completed_by to $user_id");
            }
            
            $this->db->where('taskID', $event->task_id);
            $task_result = $this->db->update('projects_task', $update_data);
            
            error_log("Toggle Completion - Task update result: " . ($task_result ? 'success' : 'failed') . ", Affected rows: " . $this->db->affected_rows());
            
            // Also add entry to projects_task_stat for history
            if ($task_result && $this->db->table_exists('projects_task_stat')) {
                $stat_note = ($is_completed == 0) ? 'Marked as completed from calendar' : 'Reopened from calendar';
                $stat_data = array(
                    'taskID' => $event->task_id,
                    'note' => $stat_note,
                    'datePosted' => date('Y-m-d H:i:s'),
                    'postedBy' => $username,
                    'taskStat' => $task_stat
                );
                
                // Add points if field exists and marking as complete
                if ($is_completed == 0 && $this->db->field_exists('points', 'projects_task_stat')) {
                    $stat_data['points'] = 1;
                }
                
                $this->db->insert('projects_task_stat', $stat_data);
                error_log("Toggle Completion - Task stat entry added");
            }
        } else {
            error_log("Toggle Completion - Event not linked to task or update failed. task_id: " . (isset($event->task_id) ? $event->task_id : 'none') . ", result: " . ($result ? 'success' : 'failed'));
        }

        // Track ALL calendar event completions in calendar_event_completions table
        if ($result && $is_completed == 0) {
            // Ensure table exists
            if (!$this->db->table_exists('calendar_event_completions')) {
                $this->db->query("
                    CREATE TABLE `calendar_event_completions` (
                        `id` int unsigned NOT NULL AUTO_INCREMENT,
                        `event_id` int NOT NULL,
                        `user_id` int NOT NULL,
                        `username` varchar(100) NOT NULL,
                        `settingsID` int NOT NULL DEFAULT 0,
                        `event_title` varchar(255) NOT NULL,
                        `event_type` varchar(100) NOT NULL DEFAULT 'default',
                        `completed_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                        `points` int NOT NULL DEFAULT 1,
                        PRIMARY KEY (`id`),
                        KEY `idx_event_completions_event` (`event_id`),
                        KEY `idx_event_completions_user_settings` (`user_id`, `settingsID`),
                        KEY `idx_event_completions_completed_at` (`completed_at`)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
                ");
            }
            
            // Check if already tracked
            $this->db->where('event_id', $event_id);
            $existing = $this->db->get('calendar_event_completions');
            
            if ($existing->num_rows() == 0) {
                $completion_data = array(
                    'event_id' => $event_id,
                    'user_id' => $user_id,
                    'username' => $username,
                    'settingsID' => $settingsID,
                    'event_title' => $event->title,
                    'event_type' => $event->event_type,
                    'completed_at' => date('Y-m-d H:i:s'),
                    'points' => 1
                );
                $this->db->insert('calendar_event_completions', $completion_data);
                error_log("Calendar completion tracked: Event ID $event_id, User $username, Title: {$event->title}");
            } else {
                error_log("Calendar completion already tracked: Event ID $event_id");
            }
        }

        if ($result) {
            echo json_encode(array('success' => true, 'message' => 'Event completion status updated successfully'));
        } else {
            echo json_encode(array('success' => false, 'message' => 'Failed to update event completion status'));
        }
    }

    public function move_event() {
        header('Content-Type: application/json');
        $user_id = $this->session->userdata('user_id');
        $settingsID = $this->session->userdata('settingsID');
        $event_id = (int) $this->input->post('event_id');
        $new_start_date = $this->input->post('new_start_date');
        $new_end_date = $this->input->post('new_end_date');

        // Check if event exists and belongs to user
        $this->db->where('id', $event_id);
        $this->db->where('user_id', $user_id);
        $this->db->where('settingsID', $settingsID);
        $query = $this->db->get('calendar_events');

        if ($query->num_rows() == 0) {
            echo json_encode(array('success' => false, 'message' => 'Event not found or you don\'t have permission to move it'));
            return;
        }

        $event = $query->row();

        // Check if event is already completed
        $is_completed = isset($event->is_completed) ? (int) $event->is_completed : 1;
        if ($is_completed == 0) {
            echo json_encode(array('success' => false, 'message' => 'Cannot move completed events'));
            return;
        }

        // Record the move history
        $move_data = array(
            'event_id' => $event_id,
            'user_id' => $user_id,
            'settingsID' => $settingsID,
            'from_date' => $event->start_date,
            'to_date' => $new_start_date,
            'moved_at' => date('Y-m-d H:i:s')
        );
        $this->db->insert('calendar_event_moves', $move_data);

        // Update the event dates
        $update_data = array(
            'start_date' => $new_start_date,
            'end_date' => $new_end_date,
            'updated_at' => date('Y-m-d H:i:s')
        );

        $this->db->where('id', $event_id);
        $this->db->where('user_id', $user_id);
        $this->db->where('settingsID', $settingsID);
        $result = $this->db->update('calendar_events', $update_data);

        if ($result) {
            echo json_encode(array('success' => true, 'message' => 'Event moved successfully'));
        } else {
            echo json_encode(array('success' => false, 'message' => 'Failed to move event'));
        }
    }

    public function get_event_move_history() {
        header('Content-Type: application/json');
        $user_id = $this->session->userdata('user_id');
        $settingsID = $this->session->userdata('settingsID');
        $event_id_param = $this->input->get('event_id');
        
        // Check if it's a task (starts with 'task_')
        if (strpos($event_id_param, 'task_') === 0) {
            // Tasks don't have move history, return empty
            echo json_encode(array('success' => true, 'data' => array()));
            return;
        }
        
        $event_id = (int) $event_id_param;

        $this->db->where('event_id', $event_id);
        $this->db->where('user_id', $user_id);
        $this->db->where('settingsID', $settingsID);
        $this->db->order_by('moved_at', 'DESC');
        $query = $this->db->get('calendar_event_moves');

        $moves = array();
        foreach ($query->result() as $move) {
            $moves[] = array(
                'from_date' => $move->from_date,
                'to_date' => $move->to_date,
                'moved_at' => $move->moved_at
            );
        }

        echo json_encode(array('success' => true, 'data' => $moves));
    }

    public function get_projects() {
        header('Content-Type: application/json');
        $settingsID = $this->session->userdata('settingsID');

        $this->db->select('projectID, projectDescription');
        $this->db->where('settingsID', $settingsID);
        $this->db->order_by('projectDescription', 'ASC');
        $query = $this->db->get('projects');

        $projects = array();
        foreach ($query->result() as $project) {
            $projects[] = array(
                'projectID' => $project->projectID,
                'projectDescription' => $project->projectDescription
            );
        }

        echo json_encode(array('success' => true, 'data' => $projects));
    }

    public function completion_stats() {
        $data['title'] = 'Completion Statistics';
        $data['username'] = $this->session->userdata('username');
        $data['user_id'] = $this->session->userdata('user_id');
        $data['settingsID'] = $this->session->userdata('settingsID');
        $data['user_level'] = $this->session->userdata('level');
        
        $this->load->view('calendar_completion_stats', $data);
    }

    public function get_completion_stats() {
        header('Content-Type: application/json');
        $user_id = $this->session->userdata('user_id');
        $settingsID = $this->session->userdata('settingsID');

        $sql = "
            SELECT *, 1 AS own FROM calendar_events
            WHERE user_id = ? AND settingsID = ? AND status = 'active'

            UNION ALL

            SELECT *, 0 AS own FROM calendar_events
            WHERE user_id != ? AND settingsID = ? AND is_public = 1 AND status = 'active'

            ORDER BY start_date ASC
        ";

        $query = $this->db->query($sql, array($user_id, $settingsID, $user_id, $settingsID));
        $events = $query->result();

        // Group by month and calculate completion percentages
        $monthly_stats = array();
        
        foreach ($events as $event) {
            $month_key = date('Y-m', strtotime($event->start_date));
            $month_label = date('F Y', strtotime($event->start_date));
            
            if (!isset($monthly_stats[$month_key])) {
                $monthly_stats[$month_key] = array(
                    'month' => $month_label,
                    'total' => 0,
                    'completed' => 0,
                    'pending' => 0
                );
            }
            
            $monthly_stats[$month_key]['total']++;
            $is_completed = isset($event->is_completed) ? (int) $event->is_completed : 1;
            if ($is_completed == 0) {
                $monthly_stats[$month_key]['completed']++;
            } else {
                $monthly_stats[$month_key]['pending']++;
            }
        }

        // Calculate percentages
        $result = array();
        foreach ($monthly_stats as $key => $stats) {
            $result[] = array(
                'month' => $stats['month'],
                'total' => $stats['total'],
                'completed' => $stats['completed'],
                'pending' => $stats['pending'],
                'completed_percent' => $stats['total'] > 0 ? round(($stats['completed'] / $stats['total']) * 100, 1) : 0,
                'pending_percent' => $stats['total'] > 0 ? round(($stats['pending'] / $stats['total']) * 100, 1) : 0
            );
        }

        echo json_encode(array('success' => true, 'data' => $result));
    }

    public function print_all() {
        $user_id    = $this->session->userdata('user_id');
        $settingsID = $this->session->userdata('settingsID');
        $level      = strtolower((string) $this->session->userdata('level'));
        $is_admin   = in_array($level, array('admin', 'staff', 'account'));
        
        // Get filter parameters
        $month_filter = $this->input->get('month'); // Format: Y-m (e.g., 2026-06)
        $status_filter = $this->input->get('status'); // 'all', 'completed', 'pending'

        $sql = "
            SELECT *, 1 AS own FROM calendar_events
            WHERE user_id = ? AND settingsID = ? AND status = 'active'

            UNION ALL

            SELECT *, 0 AS own FROM calendar_events
            WHERE user_id != ? AND settingsID = ? AND is_public = 1 AND status = 'active'

            ORDER BY start_date ASC
        ";

        $query  = $this->db->query($sql, array($user_id, $settingsID, $user_id, $settingsID));
        $rows   = $query->result();

        $events = array();
        foreach ($rows as $row) {
            $obj          = new stdClass();
            $obj->title   = $row->title;
            $obj->start   = $row->start_date;
            $obj->end     = $row->end_date;
            $obj->status  = $row->is_public ? 'public' : 'private';
            $obj->color   = $row->color;
            $obj->description = $row->description;
            $obj->notes   = isset($row->notes) ? $row->notes : '';
            $obj->is_completed = isset($row->is_completed) ? (int) $row->is_completed : 0;
            $events[]     = $obj;
        }

        // Apply filters
        if ($month_filter) {
            $events = array_filter($events, function($ev) use ($month_filter) {
                $event_month = date('Y-m', strtotime($ev->start));
                return $event_month === $month_filter;
            });
        }

        if ($status_filter && $status_filter !== 'all') {
            $events = array_filter($events, function($ev) use ($status_filter) {
                if ($status_filter === 'completed') {
                    return $ev->is_completed == 0;
                } elseif ($status_filter === 'pending') {
                    return $ev->is_completed == 1;
                }
                return true;
            });
        }

        // Re-index array after filtering
        $events = array_values($events);

        $this->load->model('SettingsModel');
        $settings = $this->SettingsModel->getPosSettings($settingsID);
        $company_name = trim((string) ($settings->CompName ?? $settings->BusinessName ?? 'Calendar'));

        $data['events']         = $events;
        $data['is_admin_staff'] = $is_admin;
        $data['company_name']   = $company_name;
        $data['printed_by']     = $this->session->userdata('username');
        $data['printed_at']     = date('F j, Y \a\t g:i A');
        $data['month_filter']   = $month_filter;
        $data['status_filter']  = $status_filter;
        $this->load->view('calendar_print', $data);
    }

    public function get_event_details() {
        $user_id = $this->session->userdata('user_id');
        $settingsID = $this->session->userdata('settingsID');
        $event_id_param = $this->input->get('event_id');
        
        // Check if it's a task (starts with 'task_')
        if (strpos($event_id_param, 'task_') === 0) {
            $task_id = (int) str_replace('task_', '', $event_id_param);
            
            // Get task details instead
            $this->db->where('taskID', $task_id);
            $this->db->where('settingsID', $settingsID);
            $query = $this->db->get('projects_task');
            
            if ($query->num_rows() > 0) {
                $task = $query->row();
                // Return task data in event format
                $event = array(
                    'id' => $event_id_param,
                    'title' => $task->task,
                    'description' => 'Task: ' . $task->task,
                    'start_date' => $task->reportedDate,
                    'end_date' => $task->dueDate,
                    'all_day' => 1,
                    'color' => $task->priority == '1' ? '#dc3545' : ($task->priority == '2' ? '#ffc107' : '#28a745'),
                    'event_type' => 'task',
                    'task_id' => $task_id,
                    'is_completed' => ($task->taskStat == '0') ? 0 : 1
                );
                echo json_encode(array('success' => true, 'data' => (object)$event));
            } else {
                echo json_encode(array('success' => false, 'message' => 'Task not found'));
            }
            return;
        }
        
        $event_id = (int) $event_id_param;

        // Get event details
        $this->db->where('id', $event_id);
        $this->db->where('user_id', $user_id);
        $this->db->where('settingsID', $settingsID);
        $this->db->where('status', 'active');
        $query = $this->db->get('calendar_events');

        if ($query->num_rows() > 0) {
            $event = $query->row();
            echo json_encode(array('success' => true, 'data' => $event));
        } else {
            echo json_encode(array('success' => false, 'message' => 'Event not found'));
        }
    }

    // Event Types Management
    public function event_types() {
        $data['title'] = 'Event Types';
        $data['username'] = $this->session->userdata('username');
        $data['user_id'] = $this->session->userdata('user_id');
        $data['settingsID'] = $this->session->userdata('settingsID');
        $data['user_level'] = $this->session->userdata('level');
        
        $this->load->view('calendar_event_types', $data);
    }

    public function get_event_types() {
        header('Content-Type: application/json');
        $user_id = $this->session->userdata('user_id');
        $settingsID = $this->session->userdata('settingsID');

        $this->db->where('user_id', $user_id);
        $this->db->where('settingsID', $settingsID);
        $this->db->where('is_active', 1);
        $this->db->order_by('created_at', 'DESC');
        $query = $this->db->get('calendar_event_types');
        $event_types = $query->result();

        echo json_encode(array('success' => true, 'data' => $event_types));
    }

    public function save_event_type() {
        header('Content-Type: application/json');
        $user_id = $this->session->userdata('user_id');
        $settingsID = $this->session->userdata('settingsID');
        $event_type_id = (int) $this->input->post('event_type_id');

        $name = trim((string) $this->input->post('name'));
        $description = trim((string) $this->input->post('description'));
        $duration = (int) $this->input->post('duration');
        $duration_unit = trim((string) $this->input->post('duration_unit'));
        $color = trim((string) $this->input->post('color'));
        $location_type = trim((string) $this->input->post('location_type'));
        $location_details = trim((string) $this->input->post('location_details'));
        $buffer_before = (int) $this->input->post('buffer_before');
        $buffer_after = (int) $this->input->post('buffer_after');
        $min_booking_notice = (int) $this->input->post('min_booking_notice');
        $max_booking_notice = (int) $this->input->post('max_booking_notice');
        $booking_link_slug = trim((string) $this->input->post('booking_link_slug'));

        if ($name === '' || $duration <= 0) {
            echo json_encode(array('success' => false, 'message' => 'Name and duration are required'));
            return;
        }

        $data = array(
            'name' => $name,
            'description' => $description,
            'duration' => $duration,
            'duration_unit' => $duration_unit,
            'color' => $color !== '' ? $color : '#3788d8',
            'location_type' => $location_type,
            'location_details' => $location_details,
            'buffer_before' => $buffer_before,
            'buffer_after' => $buffer_after,
            'min_booking_notice' => $min_booking_notice,
            'max_booking_notice' => $max_booking_notice,
            'updated_at' => date('Y-m-d H:i:s')
        );

        if ($booking_link_slug !== '') {
            // Check if slug is unique
            $this->db->where('booking_link_slug', $booking_link_slug);
            $this->db->where('settingsID', $settingsID);
            if ($event_type_id > 0) {
                $this->db->where('id !=', $event_type_id);
            }
            $check = $this->db->get('calendar_event_types');
            if ($check->num_rows() > 0) {
                echo json_encode(array('success' => false, 'message' => 'Booking link slug already exists'));
                return;
            }
            $data['booking_link_slug'] = $booking_link_slug;
        }

        if ($event_type_id > 0) {
            // Update existing
            $this->db->where('id', $event_type_id);
            $this->db->where('user_id', $user_id);
            $this->db->where('settingsID', $settingsID);
            $result = $this->db->update('calendar_event_types', $data);
        } else {
            // Create new
            $data['user_id'] = $user_id;
            $data['settingsID'] = $settingsID;
            $data['created_at'] = date('Y-m-d H:i:s');
            $result = $this->db->insert('calendar_event_types', $data);
        }

        if ($result) {
            echo json_encode(array('success' => true, 'message' => 'Event type saved successfully'));
        } else {
            echo json_encode(array('success' => false, 'message' => 'Failed to save event type'));
        }
    }

    public function delete_event_type() {
        header('Content-Type: application/json');
        $user_id = $this->session->userdata('user_id');
        $settingsID = $this->session->userdata('settingsID');
        $event_type_id = (int) $this->input->post('event_type_id');

        $this->db->where('id', $event_type_id);
        $this->db->where('user_id', $user_id);
        $this->db->where('settingsID', $settingsID);
        $result = $this->db->delete('calendar_event_types');

        if ($this->db->affected_rows() > 0) {
            echo json_encode(array('success' => true, 'message' => 'Event type deleted successfully'));
        } else {
            echo json_encode(array('success' => false, 'message' => 'Failed to delete event type'));
        }
    }

    // Availability Management
    public function availability() {
        $data['title'] = 'Availability';
        $data['username'] = $this->session->userdata('username');
        $data['user_id'] = $this->session->userdata('user_id');
        $data['settingsID'] = $this->session->userdata('settingsID');
        $data['user_level'] = $this->session->userdata('level');
        
        $this->load->view('calendar_availability', $data);
    }

    public function get_availability() {
        header('Content-Type: application/json');
        $user_id = $this->session->userdata('user_id');
        $settingsID = $this->session->userdata('settingsID');

        $this->db->where('user_id', $user_id);
        $this->db->where('settingsID', $settingsID);
        $query = $this->db->get('calendar_availability');
        $availability = $query->result();

        // Ensure all 7 days are present
        $daysOfWeek = array(0, 1, 2, 3, 4, 5, 6);
        $availabilityMap = array();
        foreach ($availability as $avail) {
            $availabilityMap[$avail->day_of_week] = $avail;
        }

        $result = array();
        foreach ($daysOfWeek as $day) {
            if (isset($availabilityMap[$day])) {
                $result[] = $availabilityMap[$day];
            } else {
                // Create default availability
                $default = new stdClass();
                $default->id = null;
                $default->day_of_week = $day;
                $default->is_available = ($day >= 1 && $day <= 5) ? 1 : 0; // Weekdays available by default
                $default->start_time = '09:00:00';
                $default->end_time = '17:00:00';
                $result[] = $default;
            }
        }

        echo json_encode(array('success' => true, 'data' => $result));
    }

    public function save_availability() {
        header('Content-Type: application/json');
        $user_id = $this->session->userdata('user_id');
        $settingsID = $this->session->userdata('settingsID');

        $availabilityData = $this->input->post('availability');

        if (empty($availabilityData) || !is_array($availabilityData)) {
            echo json_encode(array('success' => false, 'message' => 'Invalid availability data'));
            return;
        }

        foreach ($availabilityData as $dayData) {
            $dayOfWeek = (int) $dayData['day_of_week'];
            $isAvailable = isset($dayData['is_available']) ? (int) $dayData['is_available'] : 0;
            $startTime = trim((string) ($dayData['start_time'] ?? '09:00:00'));
            $endTime = trim((string) ($dayData['end_time'] ?? '17:00:00'));

            // Check if availability exists for this day
            $this->db->where('user_id', $user_id);
            $this->db->where('settingsID', $settingsID);
            $this->db->where('day_of_week', $dayOfWeek);
            $query = $this->db->get('calendar_availability');

            $data = array(
                'is_available' => $isAvailable,
                'start_time' => $startTime,
                'end_time' => $endTime,
                'updated_at' => date('Y-m-d H:i:s')
            );

            if ($query->num_rows() > 0) {
                // Update existing
                $this->db->where('user_id', $user_id);
                $this->db->where('settingsID', $settingsID);
                $this->db->where('day_of_week', $dayOfWeek);
                $this->db->update('calendar_availability', $data);
            } else {
                // Create new
                $data['user_id'] = $user_id;
                $data['settingsID'] = $settingsID;
                $data['day_of_week'] = $dayOfWeek;
                $data['created_at'] = date('Y-m-d H:i:s');
                $this->db->insert('calendar_availability', $data);
            }
        }

        echo json_encode(array('success' => true, 'message' => 'Availability saved successfully'));
    }

    // Public Booking Methods
    public function booking($slug = '') {
        if ($slug === '') {
            show_404();
            return;
        }

        $settingsID = $this->session->userdata('settingsID');
        
        // Get event type by slug
        $this->db->where('booking_link_slug', $slug);
        $this->db->where('settingsID', $settingsID);
        $this->db->where('is_active', 1);
        $event_type = $this->db->get('calendar_event_types')->row();

        if (!$event_type) {
            show_404();
            return;
        }

        // Get user info
        $this->db->where('user_id', $event_type->user_id);
        $user = $this->db->get('users')->row();

        $data['title'] = 'Book a Meeting';
        $data['event_type'] = $event_type;
        $data['user'] = $user;
        $data['settingsID'] = $settingsID;
        
        $this->load->view('calendar_booking', $data);
    }

    public function get_available_slots() {
        header('Content-Type: application/json');
        $event_type_id = (int) $this->input->get('event_type_id');
        $date = trim((string) $this->input->get('date'));
        $settingsID = (int) $this->input->get('settingsID');

        if ($event_type_id <= 0 || $date === '') {
            echo json_encode(array('success' => false, 'message' => 'Invalid parameters'));
            return;
        }

        // Get event type
        $this->db->where('id', $event_type_id);
        $event_type = $this->db->get('calendar_event_types')->row();

        if (!$event_type) {
            echo json_encode(array('success' => false, 'message' => 'Event type not found'));
            return;
        }

        // Get availability for the day
        $dayOfWeek = (int) date('w', strtotime($date));
        $this->db->where('user_id', $event_type->user_id);
        $this->db->where('settingsID', $settingsID);
        $this->db->where('day_of_week', $dayOfWeek);
        $availability = $this->db->get('calendar_availability')->row();

        if (!$availability || $availability->is_available != 1) {
            echo json_encode(array('success' => true, 'slots' => array()));
            return;
        }

        // Get existing bookings for the day
        $this->db->where('event_type_id', $event_type_id);
        $this->db->where('user_id', $event_type->user_id);
        $this->db->where('settingsID', $settingsID);
        $this->db->where('status', 'confirmed');
        $this->db->where('DATE(start_date)', $date);
        $bookings = $this->db->get('calendar_bookings')->result();

        // Generate time slots
        $slots = array();
        $startTime = strtotime($availability->start_time);
        $endTime = strtotime($availability->end_time);
        $duration = $event_type->duration_unit === 'hour' ? $event_type->duration * 3600 : $event_type->duration * 60;
        $bufferBefore = $event_type->buffer_before * 60;
        $bufferAfter = $event_type->buffer_after * 60;

        $currentTime = $startTime;
        while ($currentTime + $duration <= $endTime) {
            $slotStart = $currentTime + $bufferBefore;
            $slotEnd = $currentTime + $duration + bufferAfter;

            // Check if slot conflicts with existing bookings
            $isAvailable = true;
            foreach ($bookings as $booking) {
                $bookingStart = strtotime($booking->start_date);
                $bookingEnd = strtotime($booking->end_date);

                if (($slotStart < $bookingEnd) && ($slotEnd > $bookingStart)) {
                    $isAvailable = false;
                    break;
                }
            }

            if ($isAvailable) {
                $slots[] = array(
                    'start' => date('H:i', $slotStart),
                    'end' => date('H:i', $slotEnd),
                    'timestamp' => $slotStart
                );
            }

            $currentTime += $duration + $bufferBefore + $bufferAfter;
        }

        echo json_encode(array('success' => true, 'slots' => $slots));
    }

    public function create_booking() {
        header('Content-Type: application/json');
        
        $event_type_id = (int) $this->input->post('event_type_id');
        $settingsID = (int) $this->input->post('settingsID');
        $invitee_name = trim((string) $this->input->post('invitee_name'));
        $invitee_email = trim((string) $this->input->post('invitee_email'));
        $invitee_phone = trim((string) $this->input->post('invitee_phone'));
        $invitee_notes = trim((string) $this->input->post('invitee_notes'));
        $date = trim((string) $this->input->post('date'));
        $time = trim((string) $this->input->post('time'));

        if ($event_type_id <= 0 || $invitee_name === '' || $invitee_email === '' || $date === '' || $time === '') {
            echo json_encode(array('success' => false, 'message' => 'Please fill in all required fields'));
            return;
        }

        if (!filter_var($invitee_email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(array('success' => false, 'message' => 'Invalid email address'));
            return;
        }

        // Get event type
        $this->db->where('id', $event_type_id);
        $event_type = $this->db->get('calendar_event_types')->row();

        if (!$event_type) {
            echo json_encode(array('success' => false, 'message' => 'Event type not found'));
            return;
        }

        // Calculate start and end times
        $startDateTime = $date . ' ' . $time . ':00';
        $duration = $event_type->duration_unit === 'hour' ? $event_type->duration * 3600 : $event_type->duration * 60;
        $endDateTime = date('Y-m-d H:i:s', strtotime($startDateTime) + $duration);

        // Generate confirmation code
        $confirmationCode = strtoupper(substr(md5(uniqid()), 0, 8));

        // Create booking
        $bookingData = array(
            'event_type_id' => $event_type_id,
            'user_id' => $event_type->user_id,
            'settingsID' => $settingsID,
            'invitee_name' => $invitee_name,
            'invitee_email' => $invitee_email,
            'invitee_phone' => $invitee_phone,
            'invitee_notes' => $invitee_notes,
            'start_date' => $startDateTime,
            'end_date' => $endDateTime,
            'status' => 'confirmed',
            'confirmation_code' => $confirmationCode,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        );

        $result = $this->db->insert('calendar_bookings', $bookingData);

        if ($result) {
            // Also add to calendar_events
            $eventData = array(
                'title' => $event_type->name . ' with ' . $invitee_name,
                'description' => 'Booking via event type: ' . $event_type->name,
                'notes' => $invitee_notes,
                'start_date' => $startDateTime,
                'end_date' => $endDateTime,
                'all_day' => 0,
                'event_type' => 'booking',
                'color' => $event_type->color,
                'user_id' => $event_type->user_id,
                'settingsID' => $settingsID,
                'location' => $event_type->location_details,
                'reminder_time' => 1440,
                'reminder_email_enabled' => 1,
                'reminder_email' => $invitee_email,
                'is_public' => 0,
                'status' => 'active',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            );

            $this->db->insert('calendar_events', $eventData);

            echo json_encode(array(
                'success' => true, 
                'message' => 'Booking confirmed successfully',
                'confirmation_code' => $confirmationCode
            ));
        } else {
            echo json_encode(array('success' => false, 'message' => 'Failed to create booking'));
        }
    }

    public function get_bookings() {
        header('Content-Type: application/json');
        $user_id = $this->session->userdata('user_id');
        $settingsID = $this->session->userdata('settingsID');

        $this->db->select('cb.*, cet.name as event_type_name, cet.duration, cet.duration_unit');
        $this->db->from('calendar_bookings cb');
        $this->db->join('calendar_event_types cet', 'cet.id = cb.event_type_id');
        $this->db->where('cb.user_id', $user_id);
        $this->db->where('cb.settingsID', $settingsID);
        $this->db->order_by('cb.start_date', 'DESC');
        $query = $this->db->get();
        $bookings = $query->result();

        echo json_encode(array('success' => true, 'data' => $bookings));
    }

    private function _build_event_payload() {
        $title = trim((string) $this->input->post('title'));
        $description = trim((string) $this->input->post('description'));
        $notes = trim((string) $this->input->post('notes'));
        $all_day = $this->input->post('all_day') ? 1 : 0;
        $event_type = trim((string) $this->input->post('event_type'));
        $color = trim((string) $this->input->post('color'));
        $location = trim((string) $this->input->post('location'));
        $is_public = $this->input->post('is_public') ? 1 : 0;
        $is_completed = $this->input->post('is_completed') ? 0 : 1;
        $reminderEmailEnabled = $this->input->post('reminder_email_enabled') ? 1 : 0;
        $reminderEmail = trim((string) $this->input->post('reminder_email'));

        $start_date = $this->_normalize_datetime_input($this->input->post('start_date'), $all_day ? '00:00:00' : '09:00:00');
        $end_date = $this->_normalize_datetime_input($this->input->post('end_date'), $all_day ? '23:59:59' : '10:00:00');

        if ($title === '' || $start_date === null || $end_date === null) {
            return array(
                'success' => false,
                'message' => 'Title, start date, and end date are required',
            );
        }

        if (strtotime($end_date) < strtotime($start_date)) {
            return array(
                'success' => false,
                'message' => 'End date/time must be later than or equal to the start date/time',
            );
        }

        if ($reminderEmailEnabled && $reminderEmail === '') {
            $reminderEmail = $this->_get_current_user_email();
        }

        return array(
            'success' => true,
            'data' => array(
                'title' => $title,
                'description' => $description,
                'notes' => $notes,
                'start_date' => $start_date,
                'end_date' => $end_date,
                'all_day' => $all_day,
                'event_type' => $event_type !== '' ? $event_type : 'default',
                'color' => $color !== '' ? $color : '#3788d8',
                'location' => $location,
                'reminder_time' => 1440,
                'reminder_email_enabled' => $reminderEmailEnabled,
                'reminder_email' => $reminderEmail,
                'reminder_sent_at' => null,
                'is_public' => $is_public,
                'is_completed' => $is_completed,
            ),
        );
    }

    private function _normalize_datetime_input($value, $defaultTime = '09:00:00') {
        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }

        $value = str_replace('T', ' ', $value);
        $timestamp = strtotime($value);

        if ($timestamp === false) {
            return null;
        }

        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            return date('Y-m-d', $timestamp) . ' ' . $defaultTime;
        }

        return date('Y-m-d H:i:s', $timestamp);
    }

    private function _get_current_user_email() {
        $sessionEmail = trim((string) $this->session->userdata('email'));
        if ($sessionEmail !== '') {
            return $sessionEmail;
        }

        $userId = (int) $this->session->userdata('user_id');
        if ($userId > 0 && $this->db->table_exists('users') && $this->db->field_exists('email', 'users')) {
            $row = $this->db
                ->select('email')
                ->where('user_id', $userId)
                ->limit(1)
                ->get('users')
                ->row();

            if ($row && !empty($row->email)) {
                return trim((string) $row->email);
            }
        }

        return '';
    }

    private function _ensure_calendar_schema() {
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
                    PRIMARY KEY (`id`),
                    KEY `idx_calendar_events_user_settings` (`user_id`, `settingsID`),
                    KEY `idx_calendar_events_reminder` (`reminder_email_enabled`, `reminder_sent_at`, `start_date`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
            ");
        }

        if (!$this->db->field_exists('notes', 'calendar_events')) {
            $this->db->query("ALTER TABLE `calendar_events` ADD COLUMN `notes` text DEFAULT NULL AFTER `description`");
        }

        if (!$this->db->field_exists('reminder_email_enabled', 'calendar_events')) {
            $this->db->query("ALTER TABLE `calendar_events` ADD COLUMN `reminder_email_enabled` tinyint(1) NOT NULL DEFAULT 1 AFTER `reminder_time`");
        }

        if (!$this->db->field_exists('reminder_email', 'calendar_events')) {
            $this->db->query("ALTER TABLE `calendar_events` ADD COLUMN `reminder_email` varchar(191) DEFAULT NULL AFTER `reminder_email_enabled`");
        }

        if (!$this->db->field_exists('reminder_sent_at', 'calendar_events')) {
            $this->db->query("ALTER TABLE `calendar_events` ADD COLUMN `reminder_sent_at` datetime DEFAULT NULL AFTER `reminder_email`");
        }

        if (!$this->db->field_exists('is_completed', 'calendar_events')) {
            $this->db->query("ALTER TABLE `calendar_events` ADD COLUMN `is_completed` tinyint(1) NOT NULL DEFAULT 0 AFTER `is_public`");
        }

        if (!$this->db->field_exists('task_id', 'calendar_events')) {
            $this->db->query("ALTER TABLE `calendar_events` ADD COLUMN `task_id` int DEFAULT 0 AFTER `status`");
        }

        // Add indexes for performance
        try {
            $check_task_id_index = $this->db->query("SELECT 1 FROM INFORMATION_SCHEMA.STATISTICS WHERE TABLE_NAME='calendar_events' AND INDEX_NAME='idx_task_id' AND TABLE_SCHEMA=DATABASE()");
            if ($check_task_id_index === false || $check_task_id_index->num_rows() == 0) {
                $this->db->query("ALTER TABLE `calendar_events` ADD INDEX `idx_task_id` (`task_id`)");
            }
        } catch (Exception $e) {
            error_log("Calendar schema: Could not add idx_task_id index: " . $e->getMessage());
        }

        try {
            $check_completed_index = $this->db->query("SELECT 1 FROM INFORMATION_SCHEMA.STATISTICS WHERE TABLE_NAME='calendar_events' AND INDEX_NAME='idx_is_completed' AND TABLE_SCHEMA=DATABASE()");
            if ($check_completed_index === false || $check_completed_index->num_rows() == 0) {
                $this->db->query("ALTER TABLE `calendar_events` ADD INDEX `idx_is_completed` (`is_completed`)");
            }
        } catch (Exception $e) {
            error_log("Calendar schema: Could not add idx_is_completed index: " . $e->getMessage());
        }

        // Event move history table
        if (!$this->db->table_exists('calendar_event_moves')) {
            $this->db->query("
                CREATE TABLE `calendar_event_moves` (
                    `id` int unsigned NOT NULL AUTO_INCREMENT,
                    `event_id` int NOT NULL,
                    `user_id` int NOT NULL,
                    `settingsID` int NOT NULL DEFAULT 0,
                    `from_date` datetime NOT NULL,
                    `to_date` datetime NOT NULL,
                    `moved_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    PRIMARY KEY (`id`),
                    KEY `idx_event_moves_event` (`event_id`),
                    KEY `idx_event_moves_user_settings` (`user_id`, `settingsID`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
            ");
        }

        // Add task_id field to calendar_events for task integration
        if (!$this->db->field_exists('task_id', 'calendar_events')) {
            $this->db->query("ALTER TABLE `calendar_events` ADD COLUMN `task_id` int DEFAULT NULL AFTER `is_completed`");
        }

        // Create calendar_event_completions table for tracking non-task event completions as accomplishments
        if (!$this->db->table_exists('calendar_event_completions')) {
            $this->db->query("
                CREATE TABLE `calendar_event_completions` (
                    `id` int unsigned NOT NULL AUTO_INCREMENT,
                    `event_id` int NOT NULL,
                    `user_id` int NOT NULL,
                    `username` varchar(100) NOT NULL,
                    `settingsID` int NOT NULL DEFAULT 0,
                    `event_title` varchar(255) NOT NULL,
                    `event_type` varchar(100) NOT NULL DEFAULT 'default',
                    `completed_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    `points` int NOT NULL DEFAULT 1,
                    PRIMARY KEY (`id`),
                    KEY `idx_event_completions_event` (`event_id`),
                    KEY `idx_event_completions_user_settings` (`user_id`, `settingsID`),
                    KEY `idx_event_completions_completed_at` (`completed_at`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
            ");
        }

        // Create company_features table for feature toggles per company
        if (!$this->db->table_exists('company_features')) {
            $this->db->query("
                CREATE TABLE `company_features` (
                    `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
                    `settingsID` int UNSIGNED NOT NULL,
                    `feature_key` varchar(100) NOT NULL,
                    `feature_name` varchar(255) NOT NULL,
                    `is_enabled` tinyint(1) NOT NULL DEFAULT 0,
                    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    PRIMARY KEY (`id`),
                    UNIQUE KEY `unique_company_feature` (`settingsID`, `feature_key`),
                    KEY `idx_settingsID` (`settingsID`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");
        }

        // Add package_id field to pos_settings
        if (!$this->db->field_exists('package_id', 'pos_settings')) {
            $this->db->query("ALTER TABLE `pos_settings` ADD COLUMN `package_id` int DEFAULT NULL AFTER `CompType`");
        }

        if ($this->db->field_exists('status', 'calendar_events')) {
            $this->db->query("UPDATE `calendar_events` SET `status` = 'active' WHERE `status` IS NULL OR `status` = ''");
        }

        if ($this->db->field_exists('created_at', 'calendar_events')) {
            $this->db->query("UPDATE `calendar_events` SET `created_at` = NOW() WHERE `created_at` IS NULL");
        }

        if ($this->db->field_exists('updated_at', 'calendar_events')) {
            $this->db->query("UPDATE `calendar_events` SET `updated_at` = NOW() WHERE `updated_at` IS NULL");
        }

        // Event types table for Calendly-like scheduling
        if (!$this->db->table_exists('calendar_event_types')) {
            $this->db->query("
                CREATE TABLE `calendar_event_types` (
                    `id` int unsigned NOT NULL AUTO_INCREMENT,
                    `name` varchar(255) NOT NULL,
                    `description` text DEFAULT NULL,
                    `duration` int NOT NULL DEFAULT 30,
                    `duration_unit` varchar(20) NOT NULL DEFAULT 'minute',
                    `color` varchar(20) NOT NULL DEFAULT '#3788d8',
                    `location_type` varchar(50) NOT NULL DEFAULT 'in_person',
                    `location_details` varchar(255) DEFAULT NULL,
                    `buffer_before` int NOT NULL DEFAULT 0,
                    `buffer_after` int NOT NULL DEFAULT 0,
                    `min_booking_notice` int NOT NULL DEFAULT 0,
                    `max_booking_notice` int NOT NULL DEFAULT 90,
                    `user_id` int NOT NULL,
                    `settingsID` int NOT NULL DEFAULT 0,
                    `is_active` tinyint(1) NOT NULL DEFAULT 1,
                    `booking_link_slug` varchar(100) DEFAULT NULL,
                    `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    PRIMARY KEY (`id`),
                    UNIQUE KEY `idx_event_types_slug` (`booking_link_slug`, `settingsID`),
                    KEY `idx_event_types_user_settings` (`user_id`, `settingsID`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
            ");
        }

        // Availability settings table
        if (!$this->db->table_exists('calendar_availability')) {
            $this->db->query("
                CREATE TABLE `calendar_availability` (
                    `id` int unsigned NOT NULL AUTO_INCREMENT,
                    `user_id` int NOT NULL,
                    `settingsID` int NOT NULL DEFAULT 0,
                    `day_of_week` tinyint NOT NULL,
                    `is_available` tinyint(1) NOT NULL DEFAULT 1,
                    `start_time` time NOT NULL DEFAULT '09:00:00',
                    `end_time` time NOT NULL DEFAULT '17:00:00',
                    `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    PRIMARY KEY (`id`),
                    UNIQUE KEY `idx_availability_user_day` (`user_id`, `settingsID`, `day_of_week`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
            ");
        }

        // Bookings table for scheduled meetings
        if (!$this->db->table_exists('calendar_bookings')) {
            $this->db->query("
                CREATE TABLE `calendar_bookings` (
                    `id` int unsigned NOT NULL AUTO_INCREMENT,
                    `event_type_id` int unsigned NOT NULL,
                    `user_id` int NOT NULL,
                    `settingsID` int NOT NULL DEFAULT 0,
                    `invitee_name` varchar(255) NOT NULL,
                    `invitee_email` varchar(191) NOT NULL,
                    `invitee_phone` varchar(50) DEFAULT NULL,
                    `invitee_notes` text DEFAULT NULL,
                    `start_date` datetime NOT NULL,
                    `end_date` datetime NOT NULL,
                    `status` varchar(20) NOT NULL DEFAULT 'confirmed',
                    `confirmation_code` varchar(20) DEFAULT NULL,
                    `reminder_sent` tinyint(1) NOT NULL DEFAULT 0,
                    `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    PRIMARY KEY (`id`),
                    KEY `idx_bookings_event_type` (`event_type_id`),
                    KEY `idx_bookings_user_settings` (`user_id`, `settingsID`),
                    KEY `idx_bookings_invitee_email` (`invitee_email`),
                    KEY `idx_bookings_status` (`status`),
                    KEY `idx_bookings_start_date` (`start_date`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
            ");
        }
    }

    private function _process_pending_email_reminders() {
        if (!$this->db->table_exists('calendar_events')) {
            return;
        }

        $now = date('Y-m-d H:i:s');
        $windowEnd = date('Y-m-d H:i:s', strtotime('+1 day'));

        $events = $this->db
            ->where('status', 'active')
            ->where('reminder_email_enabled', 1)
            ->where('reminder_sent_at IS NULL', null, false)
            ->where('start_date >', $now)
            ->where('start_date <=', $windowEnd)
            ->where("TRIM(COALESCE(reminder_email, '')) <> ''", null, false)
            ->limit(10)
            ->get('calendar_events')
            ->result();

        foreach ($events as $event) {
            if ($this->_send_calendar_reminder_email($event)) {
                $this->db
                    ->where('id', $event->id)
                    ->update('calendar_events', array(
                        'reminder_sent_at' => date('Y-m-d H:i:s'),
                    ));
            }
        }
    }

    private function _send_calendar_reminder_email($event) {
        $targetEmail = trim((string) ($event->reminder_email ?? ''));
        if ($targetEmail === '') {
            return false;
        }

        $this->load->config('email');
        $this->load->library('email');

        $emailConfig = array(
            'protocol'     => $this->config->item('protocol') ?: 'smtp',
            'smtp_host'    => $this->config->item('smtp_host'),
            'smtp_user'    => $this->config->item('smtp_user'),
            'smtp_pass'    => $this->config->item('smtp_pass'),
            'smtp_port'    => $this->config->item('smtp_port') ?: 465,
            'smtp_crypto'  => $this->config->item('smtp_crypto') ?: 'ssl',
            'smtp_timeout' => $this->config->item('smtp_timeout') ?: 10,
            'mailtype'     => $this->config->item('mailtype') ?: 'html',
            'charset'      => $this->config->item('charset') ?: 'utf-8',
            'newline'      => $this->config->item('newline') ?: "\r\n",
            'crlf'         => $this->config->item('crlf') ?: "\r\n",
            'wordwrap'     => true,
        );

        $this->email->clear(true);
        $this->email->initialize($emailConfig);
        $fromAddress = $this->config->item('smtp_user');
        if (!$fromAddress) {
            $fromAddress = 'no-reply@' . parse_url(base_url(), PHP_URL_HOST);
        }

        $this->email->from($fromAddress, 'BERPS Calendar');
        $this->email->to($targetEmail);
        $this->email->subject('Reminder: ' . (string) $event->title);

        $startAt = !empty($event->start_date) ? date('F j, Y g:i A', strtotime($event->start_date)) : 'Not set';
        $endAt = !empty($event->end_date) ? date('F j, Y g:i A', strtotime($event->end_date)) : 'Not set';
        $description = nl2br(htmlspecialchars((string) ($event->description ?? ''), ENT_QUOTES, 'UTF-8'));
        $notes = nl2br(htmlspecialchars((string) ($event->notes ?? ''), ENT_QUOTES, 'UTF-8'));
        $location = htmlspecialchars((string) ($event->location ?? ''), ENT_QUOTES, 'UTF-8');

        $message = '
            <p>This is your BERPS calendar reminder for an upcoming activity.</p>
            <p><strong>Title:</strong> ' . htmlspecialchars((string) $event->title, ENT_QUOTES, 'UTF-8') . '</p>
            <p><strong>Start:</strong> ' . htmlspecialchars($startAt, ENT_QUOTES, 'UTF-8') . '</p>
            <p><strong>End:</strong> ' . htmlspecialchars($endAt, ENT_QUOTES, 'UTF-8') . '</p>' .
            ($location !== '' ? '<p><strong>Location:</strong> ' . $location . '</p>' : '') .
            ($description !== '' ? '<p><strong>Description:</strong><br>' . $description . '</p>' : '') .
            ($notes !== '' ? '<p><strong>Notes:</strong><br>' . $notes . '</p>' : '') . '
            <p>Please review the activity details in BERPS.</p>
        ';

        $this->email->message($message);

        return (bool) $this->email->send();
    }
}
