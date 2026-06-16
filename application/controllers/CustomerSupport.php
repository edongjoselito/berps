<?php
defined('BASEPATH') OR exit('No direct script access required');

class CustomerSupport extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('CustomerSupport_model');
        $this->load->model('User_model');
        $this->load->helper(['url', 'form', 'date']);
        $this->load->library(['session', 'form_validation', 'email']);

        // Check if user is logged in (using the same check as Page controller)
        if ($this->session->userdata('logged_in') !== TRUE) {
            redirect(base_url('login'));
        }
    }

    // Main dashboard
    public function index() {
        try {
            $data['title'] = 'Customer Support Dashboard';
            $data['stats'] = $this->CustomerSupport_model->get_dashboard_stats();
            $data['recent_issues'] = $this->CustomerSupport_model->get_issues([], null, 10);
            $data['notifications'] = $this->CustomerSupport_model->get_notifications(null, false, null, 5);
            $data['unread_count'] = $this->CustomerSupport_model->get_unread_count();
            
            $this->load->view('includes/header', $data);
            $this->load->view('customer_support/dashboard', $data);
            $this->load->view('includes/footer');
        } catch (Exception $e) {
            // Handle database errors - likely missing tables
            $data['title'] = 'Customer Support Setup Required';
            $data['error_message'] = 'Customer Support database tables are not installed.';
            $data['solution'] = 'Please run the database schema script to install the required tables.';
            
            $this->load->view('includes/header', $data);
            $this->load->view('customer_support/setup_required', $data);
            $this->load->view('includes/footer');
        }
    }

    // Department Management
    public function departments() {
        $data['title'] = 'Department Management';
        $data['departments'] = $this->CustomerSupport_model->get_departments();
        
        $this->load->view('includes/header', $data);
        $this->load->view('customer_support/departments', $data);
        $this->load->view('includes/footer');
    }

    public function create_department() {
        if ($this->input->post()) {
            $this->form_validation->set_rules('department_name', 'Department Name', 'required|trim');
            $this->form_validation->set_rules('department_code', 'Department Code', 'required|trim|callback_check_department_code');
            $this->form_validation->set_rules('description', 'Description', 'trim');
            $this->form_validation->set_rules('email', 'Email', 'valid_email|trim');
            $this->form_validation->set_rules('phone', 'Phone', 'trim');
            
            if ($this->form_validation->run() == FALSE) {
                $this->session->set_flashdata('error', validation_errors());
                redirect('CustomerSupport/departments');
            } else {
                $data = [
                    'department_name' => $this->input->post('department_name'),
                    'department_code' => $this->input->post('department_code'),
                    'description' => $this->input->post('description'),
                    'email' => $this->input->post('email'),
                    'phone' => $this->input->post('phone'),
                    'manager_id' => $this->input->post('manager_id')
                ];
                
                if ($this->CustomerSupport_model->create_department($data)) {
                    $this->session->set_flashdata('success', 'Department created successfully');
                } else {
                    $this->session->set_flashdata('error', 'Failed to create department');
                }
                redirect('CustomerSupport/departments');
            }
        }
    }

    public function edit_department($id) {
        $data['title'] = 'Edit Department';
        $data['department'] = $this->CustomerSupport_model->get_department($id);
        $data['employees'] = $this->User_model->get_by_settings($this->session->userdata('settingsID'));
        
        if (!$data['department']) {
            $this->session->set_flashdata('error', 'Department not found');
            redirect('CustomerSupport/departments');
        }
        
        $this->load->view('includes/header', $data);
        $this->load->view('customer_support/edit_department', $data);
        $this->load->view('includes/footer');
    }

    public function update_department($id) {
        if ($this->input->post()) {
            $this->form_validation->set_rules('department_name', 'Department Name', 'required|trim');
            $this->form_validation->set_rules('department_code', 'Department Code', 'required|trim');
            $this->form_validation->set_rules('description', 'Description', 'trim');
            $this->form_validation->set_rules('email', 'Email', 'valid_email|trim');
            $this->form_validation->set_rules('phone', 'Phone', 'trim');
            
            if ($this->form_validation->run() == FALSE) {
                $this->session->set_flashdata('error', validation_errors());
                redirect('CustomerSupport/edit_department/' . $id);
            } else {
                $data = [
                    'department_name' => $this->input->post('department_name'),
                    'department_code' => $this->input->post('department_code'),
                    'description' => $this->input->post('description'),
                    'email' => $this->input->post('email'),
                    'phone' => $this->input->post('phone'),
                    'manager_id' => $this->input->post('manager_id')
                ];
                
                if ($this->CustomerSupport_model->update_department($id, $data)) {
                    $this->session->set_flashdata('success', 'Department updated successfully');
                } else {
                    $this->session->set_flashdata('error', 'Failed to update department');
                }
                redirect('CustomerSupport/departments');
            }
        }
    }

    public function delete_department($id) {
        if ($this->CustomerSupport_model->delete_department($id)) {
            $this->session->set_flashdata('success', 'Department deleted successfully');
        } else {
            $this->session->set_flashdata('error', 'Failed to delete department');
        }
        redirect('CustomerSupport/departments');
    }

    // Employee Department Assignment
    public function employee_assignments() {
        $data['title'] = 'Employee Department Assignments';
        $data['departments'] = $this->CustomerSupport_model->get_departments();
        $data['employees'] = $this->User_model->get_by_settings($this->session->userdata('settingsID'));
        
        $this->load->view('includes/header', $data);
        $this->load->view('customer_support/employee_assignments', $data);
        $this->load->view('includes/footer');
    }

    public function assign_employee() {
        if ($this->input->post()) {
            $employee_id = $this->input->post('employee_id');
            $department_id = $this->input->post('department_id');
            $role = $this->input->post('role');
            
            if ($this->CustomerSupport_model->assign_employee_to_department($employee_id, $department_id, $role)) {
                $this->session->set_flashdata('success', 'Employee assigned to department successfully');
            } else {
                $this->session->set_flashdata('error', 'Failed to assign employee to department');
            }
            redirect('CustomerSupport/employee_assignments');
        }
    }

    public function remove_employee_assignment($employee_id, $department_id) {
        if ($this->CustomerSupport_model->remove_employee_from_department($employee_id, $department_id)) {
            $this->session->set_flashdata('success', 'Employee removed from department successfully');
        } else {
            $this->session->set_flashdata('error', 'Failed to remove employee from department');
        }
        redirect('CustomerSupport/employee_assignments');
    }

    // Issue Management
    public function issues() {
        $filters = [
            'status' => $this->input->get('status'),
            'priority' => $this->input->get('priority'),
            'department_id' => $this->input->get('department_id'),
            'assigned_employee_id' => $this->input->get('assigned_employee_id'),
            'date_from' => $this->input->get('date_from'),
            'date_to' => $this->input->get('date_to')
        ];
        
        $data['title'] = 'Support Issues';
        $data['issues'] = $this->CustomerSupport_model->get_issues($filters);
        $data['departments'] = $this->CustomerSupport_model->get_departments();
        $data['employees'] = $this->User_model->get_by_settings($this->session->userdata('settingsID'));
        $data['filters'] = $filters;
        
        $this->load->view('includes/header', $data);
        $this->load->view('customer_support/issues', $data);
        $this->load->view('includes/footer');
    }

    public function create_issue() {
        if ($this->input->post()) {
            $this->form_validation->set_rules('customer_name', 'Customer Name', 'required|trim');
            $this->form_validation->set_rules('customer_email', 'Customer Email', 'required|valid_email|trim');
            $this->form_validation->set_rules('customer_phone', 'Customer Phone', 'trim');
            $this->form_validation->set_rules('department_id', 'Department', 'required|integer');
            $this->form_validation->set_rules('title', 'Issue Title', 'required|trim');
            $this->form_validation->set_rules('description', 'Description', 'required|trim');
            $this->form_validation->set_rules('category', 'Category', 'trim');
            $this->form_validation->set_rules('priority', 'Priority', 'required|in_list[low,medium,high,urgent]');
            
            if ($this->form_validation->run() == FALSE) {
                $this->session->set_flashdata('error', validation_errors());
                redirect('CustomerSupport/issues');
            } else {
                $data = [
                    'customer_id' => $this->input->post('customer_id'),
                    'customer_name' => $this->input->post('customer_name'),
                    'customer_email' => $this->input->post('customer_email'),
                    'customer_phone' => $this->input->post('customer_phone'),
                    'department_id' => $this->input->post('department_id'),
                    'title' => $this->input->post('title'),
                    'description' => $this->input->post('description'),
                    'category' => $this->input->post('category'),
                    'priority' => $this->input->post('priority'),
                    'due_date' => $this->input->post('due_date')
                ];
                
                if ($this->CustomerSupport_model->create_issue($data)) {
                    $this->session->set_flashdata('success', 'Issue created successfully');
                } else {
                    $this->session->set_flashdata('error', 'Failed to create issue');
                }
                redirect('CustomerSupport/issues');
            }
        }
    }

    public function view_issue($id) {
        $data['title'] = 'Issue Details';
        $data['issue'] = $this->CustomerSupport_model->get_issue($id);
        $data['comments'] = $this->CustomerSupport_model->get_issue_comments($id);
        $data['departments'] = $this->CustomerSupport_model->get_departments();
        $data['employees'] = $this->User_model->get_by_settings($this->session->userdata('settingsID'));
        
        if (!$data['issue']) {
            $this->session->set_flashdata('error', 'Issue not found');
            redirect('CustomerSupport/issues');
        }
        
        $this->load->view('includes/header', $data);
        $this->load->view('customer_support/view_issue', $data);
        $this->load->view('includes/footer');
    }

    public function update_issue($id) {
        if ($this->input->post()) {
            $this->form_validation->set_rules('status', 'Status', 'required|in_list[open,assigned,in_progress,pending_customer,resolved,closed]');
            $this->form_validation->set_rules('assigned_employee_id', 'Assigned Employee', 'integer');
            $this->form_validation->set_rules('priority', 'Priority', 'required|in_list[low,medium,high,urgent]');
            $this->form_validation->set_rules('resolution_details', 'Resolution Details', 'trim');
            
            if ($this->form_validation->run() == FALSE) {
                $this->session->set_flashdata('error', validation_errors());
                redirect('CustomerSupport/view_issue/' . $id);
            } else {
                $data = [
                    'status' => $this->input->post('status'),
                    'assigned_employee_id' => $this->input->post('assigned_employee_id'),
                    'priority' => $this->input->post('priority'),
                    'resolution_details' => $this->input->post('resolution_details'),
                    'resolved_by' => $this->session->userdata('id')
                ];
                
                if ($this->input->post('status') == 'resolved') {
                    $data['resolution_date'] = date('Y-m-d H:i:s');
                }
                
                if ($this->CustomerSupport_model->update_issue($id, $data)) {
                    $this->session->set_flashdata('success', 'Issue updated successfully');
                } else {
                    $this->session->set_flashdata('error', 'Failed to update issue');
                }
                redirect('CustomerSupport/view_issue/' . $id);
            }
        }
    }

    public function assign_issue($id) {
        $employee_id = $this->input->post('assigned_employee_id');
        
        if ($this->CustomerSupport_model->assign_issue($id, $employee_id)) {
            $this->session->set_flashdata('success', 'Issue assigned successfully');
        } else {
            $this->session->set_flashdata('error', 'Failed to assign issue');
        }
        redirect('CustomerSupport/view_issue/' . $id);
    }

    public function add_comment($id) {
        if ($this->input->post()) {
            $this->form_validation->set_rules('comment', 'Comment', 'required|trim');
            $this->form_validation->set_rules('internal_note', 'Internal Note', 'integer');
            
            if ($this->form_validation->run() == FALSE) {
                $this->session->set_flashdata('error', validation_errors());
                redirect('CustomerSupport/view_issue/' . $id);
            } else {
                $data = [
                    'issue_id' => $id,
                    'employee_id' => $this->session->userdata('id'),
                    'comment' => $this->input->post('comment'),
                    'internal_note' => $this->input->post('internal_note') ? 1 : 0
                ];
                
                if ($this->CustomerSupport_model->add_comment($data)) {
                    $this->session->set_flashdata('success', 'Comment added successfully');
                } else {
                    $this->session->set_flashdata('error', 'Failed to add comment');
                }
                redirect('CustomerSupport/view_issue/' . $id);
            }
        }
    }

    // Customer Portal - Public Issue Submission
    public function submit_issue() {
        $data['title'] = 'Submit Support Request';
        $data['departments'] = $this->CustomerSupport_model->get_departments();
        
        $this->load->view('customer_support/submit_issue', $data);
    }

    public function customer_submit_issue() {
        if ($this->input->post()) {
            $this->form_validation->set_rules('customer_name', 'Name', 'required|trim');
            $this->form_validation->set_rules('customer_email', 'Email', 'required|valid_email|trim');
            $this->form_validation->set_rules('customer_phone', 'Phone', 'trim');
            $this->form_validation->set_rules('department_id', 'Department', 'required|integer');
            $this->form_validation->set_rules('title', 'Issue Title', 'required|trim');
            $this->form_validation->set_rules('description', 'Description', 'required|trim');
            $this->form_validation->set_rules('category', 'Category', 'trim');
            $this->form_validation->set_rules('priority', 'Priority', 'required|in_list[low,medium,high,urgent]');
            
            if ($this->form_validation->run() == FALSE) {
                $data['title'] = 'Submit Support Request';
                $data['departments'] = $this->CustomerSupport_model->get_departments();
                $data['error'] = validation_errors();
                
                $this->load->view('customer_support/submit_issue', $data);
            } else {
                $data = [
                    'customer_name' => $this->input->post('customer_name'),
                    'customer_email' => $this->input->post('customer_email'),
                    'customer_phone' => $this->input->post('customer_phone'),
                    'department_id' => $this->input->post('department_id'),
                    'title' => $this->input->post('title'),
                    'description' => $this->input->post('description'),
                    'category' => $this->input->post('category'),
                    'priority' => $this->input->post('priority')
                ];
                
                if ($this->CustomerSupport_model->create_issue($data)) {
                    $this->session->set_flashdata('success', 'Support request submitted successfully. We will contact you shortly.');
                } else {
                    $this->session->set_flashdata('error', 'Failed to submit support request');
                }
                redirect('CustomerSupport/submit_issue');
            }
        }
    }

    // Notifications
    public function notifications() {
        $data['title'] = 'Notifications';
        $data['notifications'] = $this->CustomerSupport_model->get_notifications();
        $data['unread_count'] = $this->CustomerSupport_model->get_unread_count();
        
        $this->load->view('includes/header', $data);
        $this->load->view('customer_support/notifications', $data);
        $this->load->view('includes/footer');
    }

    public function mark_notification_read($id) {
        $this->CustomerSupport_model->mark_notification_read($id);
        redirect('CustomerSupport/notifications');
    }

    public function mark_all_notifications_read() {
        $this->CustomerSupport_model->mark_all_notifications_read();
        redirect('CustomerSupport/notifications');
    }

    // Reports
    public function reports() {
        $data['title'] = 'Support Reports';
        $data['stats'] = $this->CustomerSupport_model->get_dashboard_stats();
        $data['departments'] = $this->CustomerSupport_model->get_departments();
        
        $this->load->view('includes/header', $data);
        $this->load->view('customer_support/reports', $data);
        $this->load->view('includes/footer');
    }

    // AJAX Methods
    public function get_department_employees($department_id) {
        $employees = $this->CustomerSupport_model->get_department_employees($department_id);
        echo json_encode($employees);
    }

    public function get_unread_count() {
        $count = $this->CustomerSupport_model->get_unread_count();
        echo json_encode(['count' => $count]);
    }

    // Validation Callbacks
    public function check_department_code($code) {
        $existing = $this->db->where('department_code', $code)
                            ->where('settingsID', $this->session->userdata('settingsID'))
                            ->get('support_departments')
                            ->row();
        
        if ($existing) {
            $this->form_validation->set_message('check_department_code', 'Department code already exists');
            return FALSE;
        }
        return TRUE;
    }

    // Private helper methods
    private function _ensure_support_user() {
        $allowed_roles = ['Admin', 'Manager', 'Support Staff'];
        if (!in_array($this->session->userdata('level'), $allowed_roles)) {
            $this->session->set_flashdata('error', 'You do not have permission to access this page');
            redirect('dashboard');
        }
    }
}
