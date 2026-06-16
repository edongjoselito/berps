<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class CustomerSupport_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    // DEPARTMENT MANAGEMENT METHODS
    
    public function get_departments($settingsID = null) {
        $settingsID = $settingsID ?: $this->session->userdata('settingsID');
        $this->db->where('settingsID', $settingsID);
        $this->db->where('is_active', 1);
        $this->db->order_by('department_name', 'ASC');
        return $this->db->get('support_departments')->result();
    }

    public function get_department($id, $settingsID = null) {
        $settingsID = $settingsID ?: $this->session->userdata('settingsID');
        $this->db->where('id', $id);
        $this->db->where('settingsID', $settingsID);
        return $this->db->get('support_departments')->row();
    }

    public function create_department($data) {
        $data['settingsID'] = $this->session->userdata('settingsID');
        $this->db->insert('support_departments', $data);
        return $this->db->insert_id();
    }

    public function update_department($id, $data) {
        $this->db->where('id', $id);
        $this->db->where('settingsID', $this->session->userdata('settingsID'));
        return $this->db->update('support_departments', $data);
    }

    public function delete_department($id) {
        $this->db->where('id', $id);
        $this->db->where('settingsID', $this->session->userdata('settingsID'));
        return $this->db->update('support_departments', ['is_active' => 0]);
    }

    // EMPLOYEE DEPARTMENT ASSIGNMENT METHODS
    
    public function get_employee_departments($employee_id = null, $settingsID = null) {
        $settingsID = $settingsID ?: $this->session->userdata('settingsID');
        $employee_id = $employee_id ?: $this->session->userdata('id');
        
        $this->db->select('ed.*, d.department_name, d.department_code');
        $this->db->from('employee_departments ed');
        $this->db->join('support_departments d', 'ed.department_id = d.id');
        $this->db->where('ed.employee_id', $employee_id);
        $this->db->where('ed.is_active', 1);
        $this->db->where('ed.settingsID', $settingsID);
        $this->db->where('d.is_active', 1);
        $this->db->order_by('d.department_name', 'ASC');
        return $this->db->get()->result();
    }

    public function get_department_employees($department_id, $settingsID = null) {
        $settingsID = $settingsID ?: $this->session->userdata('settingsID');

        $this->db->select('ed.*, CONCAT(u.fName, " ", u.lName) as employee_name, u.email as employee_email');
        $this->db->from('employee_departments ed');
        $this->db->join('users u', 'ed.employee_id = u.user_id');
        $this->db->where('ed.department_id', $department_id);
        $this->db->where('ed.is_active', 1);
        $this->db->where('ed.settingsID', $settingsID);
        $this->db->order_by('ed.role', 'ASC');
        $this->db->order_by('u.lName', 'ASC');
        return $this->db->get()->result();
    }

    public function assign_employee_to_department($employee_id, $department_id, $role = 'member') {
        $data = [
            'employee_id' => $employee_id,
            'department_id' => $department_id,
            'role' => $role,
            'assigned_by' => $this->session->userdata('id'),
            'settingsID' => $this->session->userdata('settingsID')
        ];
        
        // Check if assignment already exists
        $this->db->where('employee_id', $employee_id);
        $this->db->where('department_id', $department_id);
        $this->db->where('settingsID', $this->session->userdata('settingsID'));
        $existing = $this->db->get('employee_departments')->row();
        
        if ($existing) {
            // Reactivate if inactive
            $this->db->where('id', $existing->id);
            return $this->db->update('employee_departments', ['is_active' => 1, 'role' => $role]);
        } else {
            // Create new assignment
            return $this->db->insert('employee_departments', $data);
        }
    }

    public function remove_employee_from_department($employee_id, $department_id) {
        $this->db->where('employee_id', $employee_id);
        $this->db->where('department_id', $department_id);
        $this->db->where('settingsID', $this->session->userdata('settingsID'));
        return $this->db->update('employee_departments', ['is_active' => 0]);
    }

    // SUPPORT ISSUES METHODS
    
    public function get_issues($filters = [], $settingsID = null) {
        $settingsID = $settingsID ?: $this->session->userdata('settingsID');

        $this->db->select('si.*, d.department_name, d.department_code, CONCAT(u.fName, " ", u.lName) as assigned_employee_name');
        $this->db->from('support_issues si');
        $this->db->join('support_departments d', 'si.department_id = d.id', 'left');
        $this->db->join('users u', 'si.assigned_employee_id = u.user_id', 'left');
        $this->db->where('si.settingsID', $settingsID);
        
        // Apply filters
        if (!empty($filters['status'])) {
            $this->db->where('si.status', $filters['status']);
        }
        if (!empty($filters['priority'])) {
            $this->db->where('si.priority', $filters['priority']);
        }
        if (!empty($filters['department_id'])) {
            $this->db->where('si.department_id', $filters['department_id']);
        }
        if (!empty($filters['assigned_employee_id'])) {
            $this->db->where('si.assigned_employee_id', $filters['assigned_employee_id']);
        }
        if (!empty($filters['date_from'])) {
            $this->db->where('si.created_at >=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $this->db->where('si.created_at <=', $filters['date_to'] . ' 23:59:59');
        }
        
        $this->db->order_by('si.created_at', 'DESC');
        return $this->db->get()->result();
    }

    public function get_issue($id, $settingsID = null) {
        $settingsID = $settingsID ?: $this->session->userdata('settingsID');

        $this->db->select('si.*, d.department_name, d.department_code, CONCAT(u.fName, " ", u.lName) as assigned_employee_name, u.email as assigned_employee_email');
        $this->db->from('support_issues si');
        $this->db->join('support_departments d', 'si.department_id = d.id', 'left');
        $this->db->join('users u', 'si.assigned_employee_id = u.user_id', 'left');
        $this->db->where('si.id', $id);
        $this->db->where('si.settingsID', $settingsID);
        return $this->db->get()->row();
    }

    public function create_issue($data) {
        $data['settingsID'] = $this->session->userdata('settingsID');
        $data['created_at'] = date('Y-m-d H:i:s');
        
        $this->db->insert('support_issues', $data);
        $issue_id = $this->db->insert_id();
        
        // Create SLA tracking
        $this->create_sla_tracking($issue_id, $data['priority']);
        
        // Send notifications to department
        $this->send_department_notifications($issue_id, 'new_issue');
        
        return $issue_id;
    }

    public function update_issue($id, $data) {
        $this->db->where('id', $id);
        $this->db->where('settingsID', $this->session->userdata('settingsID'));
        
        // Track status change
        if (isset($data['status'])) {
            $current_issue = $this->get_issue($id);
            if ($current_issue && $current_issue->status != $data['status']) {
                $this->add_status_history($id, $current_issue->status, $data['status']);
                
                // Send notifications for status changes
                $this->send_department_notifications($id, 'status_update');
            }
        }
        
        $data['updated_at'] = date('Y-m-d H:i:s');
        return $this->db->update('support_issues', $data);
    }

    public function assign_issue($issue_id, $employee_id) {
        $data = [
            'assigned_employee_id' => $employee_id,
            'status' => 'assigned',
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        $result = $this->update_issue($issue_id, $data);
        
        // Send notification to assigned employee
        $this->send_employee_notification($employee_id, $issue_id, 'assigned');
        
        return $result;
    }

    // ISSUE COMMENTS METHODS
    
    public function get_issue_comments($issue_id, $settingsID = null) {
        $settingsID = $settingsID ?: $this->session->userdata('settingsID');

        $this->db->select('sic.*, CONCAT(u.fName, " ", u.lName) as employee_name');
        $this->db->from('support_issue_comments sic');
        $this->db->join('users u', 'sic.employee_id = u.user_id', 'left');
        $this->db->where('sic.issue_id', $issue_id);
        $this->db->where('sic.settingsID', $settingsID);
        $this->db->order_by('sic.created_at', 'ASC');
        return $this->db->get()->result();
    }

    public function add_comment($data) {
        $data['settingsID'] = $this->session->userdata('settingsID');
        $data['created_at'] = date('Y-m-d H:i:s');
        
        $result = $this->db->insert('support_issue_comments', $data);
        
        // Send notifications for new comments
        if ($result) {
            $this->send_department_notifications($data['issue_id'], 'comment');
        }
        
        return $result;
    }

    // NOTIFICATION METHODS
    
    public function get_notifications($user_id = null, $unread_only = false, $settingsID = null) {
        $settingsID = $settingsID ?: $this->session->userdata('settingsID');
        $user_id = $user_id ?: $this->session->userdata('id');
        
        $this->db->where('user_id', $user_id);
        $this->db->where('settingsID', $settingsID);
        
        if ($unread_only) {
            $this->db->where('is_read', 0);
        }
        
        $this->db->order_by('created_at', 'DESC');
        $this->db->limit(50);
        return $this->db->get('support_notifications')->result();
    }

    public function mark_notification_read($notification_id) {
        $this->db->where('id', $notification_id);
        $this->db->where('user_id', $this->session->userdata('id'));
        return $this->db->update('support_notifications', [
            'is_read' => 1,
            'read_at' => date('Y-m-d H:i:s')
        ]);
    }

    public function mark_all_notifications_read($user_id = null) {
        $user_id = $user_id ?: $this->session->userdata('id');
        
        $this->db->where('user_id', $user_id);
        $this->db->where('is_read', 0);
        return $this->db->update('support_notifications', [
            'is_read' => 1,
            'read_at' => date('Y-m-d H:i:s')
        ]);
    }

    public function create_notification($user_id, $issue_id, $type, $title, $message, $action_required = 0) {
        $data = [
            'user_id' => $user_id,
            'issue_id' => $issue_id,
            'notification_type' => $type,
            'title' => $title,
            'message' => $message,
            'action_required' => $action_required,
            'created_at' => date('Y-m-d H:i:s'),
            'expires_at' => date('Y-m-d H:i:s', strtotime('+7 days')),
            'settingsID' => $this->session->userdata('settingsID')
        ];
        
        return $this->db->insert('support_notifications', $data);
    }

    // DASHBOARD STATISTICS METHODS
    
    public function get_dashboard_stats($settingsID = null) {
        $settingsID = $settingsID ?: $this->session->userdata('settingsID');
        
        $stats = [];
        
        // Total issues
        $stats['total_issues'] = $this->db->where('settingsID', $settingsID)
                                       ->count_all_results('support_issues');
        
        // Issues by status
        $this->db->select('status, COUNT(*) as count');
        $this->db->where('settingsID', $settingsID);
        $this->db->group_by('status');
        $status_counts = $this->db->get('support_issues')->result();
        $stats['issues_by_status'] = [];
        foreach ($status_counts as $row) {
            $stats['issues_by_status'][$row->status] = $row->count;
        }
        
        // Issues by priority
        $this->db->select('priority, COUNT(*) as count');
        $this->db->where('settingsID', $settingsID);
        $this->db->group_by('priority');
        $priority_counts = $this->db->get('support_issues')->result();
        $stats['issues_by_priority'] = [];
        foreach ($priority_counts as $row) {
            $stats['issues_by_priority'][$row->priority] = $row->count;
        }
        
        // Unread notifications count
        $stats['unread_notifications'] = $this->db->where('user_id', $this->session->userdata('id'))
                                                   ->where('is_read', 0)
                                                   ->where('settingsID', $settingsID)
                                                   ->count_all_results('support_notifications');

        return $stats;
    }

    // HELPER METHODS
    
    private function create_sla_tracking($issue_id, $priority) {
        $sla_hours = [
            'urgent' => 2,
            'high' => 8,
            'medium' => 24,
            'low' => 72
        ];
        
        $hours = $sla_hours[$priority] ?? 24;
        $target_datetime = date('Y-m-d H:i:s', strtotime('+' . $hours . ' hours'));
        
        $this->db->insert('support_sla_tracking', [
            'issue_id' => $issue_id,
            'sla_type' => 'response',
            'sla_hours' => $hours,
            'target_datetime' => $target_datetime,
            'settingsID' => $this->session->userdata('settingsID')
        ]);
    }

    private function add_status_history($issue_id, $old_status, $new_status) {
        $this->db->insert('support_issue_history', [
            'issue_id' => $issue_id,
            'old_status' => $old_status,
            'new_status' => $new_status,
            'changed_by' => $this->session->userdata('id'),
            'settingsID' => $this->session->userdata('settingsID')
        ]);
    }

    private function send_department_notifications($issue_id, $type) {
        $issue = $this->get_issue($issue_id);
        if (!$issue) return;
        
        // Get all employees in the department
        $employees = $this->get_department_employees($issue->department_id);
        
        foreach ($employees as $employee) {
            $title = $this->get_notification_title($type, $issue);
            $message = $this->get_notification_message($type, $issue);
            $this->create_notification($employee->employee_id, $issue_id, $type, $title, $message, 1);
        }
    }

    private function send_employee_notification($employee_id, $issue_id, $type) {
        $issue = $this->get_issue($issue_id);
        if (!$issue) return;
        
        $title = $this->get_notification_title($type, $issue);
        $message = $this->get_notification_message($type, $issue);
        $this->create_notification($employee_id, $issue_id, $type, $title, $message, 1);
    }

    private function get_notification_title($type, $issue) {
        $titles = [
            'new_issue' => 'New Issue Assigned',
            'assigned' => 'Issue Assigned to You',
            'status_update' => 'Issue Status Updated',
            'comment' => 'New Comment on Issue',
            'urgent' => 'Urgent Issue Alert',
            'overdue' => 'Issue Overdue'
        ];
        
        return $titles[$type] ?? 'Support System Notification';
    }

    private function get_notification_message($type, $issue) {
        $messages = [
            'new_issue' => "New issue #{$issue->ticket_number}: {$issue->title} has been created and assigned to your department.",
            'assigned' => "Issue #{$issue->ticket_number}: {$issue->title} has been assigned to you.",
            'status_update' => "Issue #{$issue->ticket_number}: {$issue->title} status has been updated to {$issue->status}.",
            'comment' => "A new comment has been added to issue #{$issue->ticket_number}: {$issue->title}.",
            'urgent' => "URGENT: Issue #{$issue->ticket_number}: {$issue->title} requires immediate attention.",
            'overdue' => "Issue #{$issue->ticket_number}: {$issue->title} is overdue and requires attention."
        ];
        
        return $messages[$type] ?? 'Support system notification.';
    }

    public function get_unread_count($user_id = null) {
        $user_id = $user_id ?: $this->session->userdata('id');
        return $this->db->where('user_id', $user_id)
                        ->where('is_read', 0)
                        ->where('settingsID', $this->session->userdata('settingsID'))
                        ->count_all_results('support_notifications');
    }
}
