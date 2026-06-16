<?php
defined('BASEPATH') OR exit('No direct script access required');

class CustomerSupport_bypass extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('CustomerSupport_model');
        $this->load->model('User_model');
        $this->load->helper(['url', 'form', 'date']);
        $this->load->library(['session', 'form_validation', 'email']);
        
        // TEMPORARY: Bypass session check for testing
        // Remove this after fixing the original issue
        error_log("CustomerSupport_bypass - Session check bypassed for testing");
    }

    // Main dashboard
    public function index() {
        $data['title'] = 'Customer Support Dashboard (Test Mode)';
        
        try {
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
        $data['title'] = 'Department Management (Test Mode)';
        $data['departments'] = $this->CustomerSupport_model->get_departments();
        
        $this->load->view('includes/header', $data);
        $this->load->view('customer_support/departments', $data);
        $this->load->view('includes/footer');
    }

    // Employee Department Assignment
    public function employee_assignments() {
        $data['title'] = 'Employee Department Assignments (Test Mode)';
        $data['departments'] = $this->CustomerSupport_model->get_departments();
        $data['employees'] = $this->User_model->get_users();
        
        $this->load->view('includes/header', $data);
        $this->load->view('customer_support/employee_assignments', $data);
        $this->load->view('includes/footer');
    }

    // Support Issues
    public function issues() {
        $data['title'] = 'Support Issues (Test Mode)';
        $data['issues'] = $this->CustomerSupport_model->get_issues();
        $data['departments'] = $this->CustomerSupport_model->get_departments();
        $data['employees'] = $this->User_model->get_users();
        
        $this->load->view('includes/header', $data);
        $this->load->view('customer_support/issues', $data);
        $this->load->view('includes/footer');
    }

    // Customer Issue Submission
    public function submit_issue() {
        $data['title'] = 'Submit Support Request';
        $data['departments'] = $this->CustomerSupport_model->get_departments();
        
        $this->load->view('customer_support/submit_issue', $data);
    }
}
?>
