<?php
defined('BASEPATH') OR exit('No direct script access required');

class CustomerSupport_test extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->helper(['url']);
        
        // Temporarily bypass session check for testing
        error_log("CustomerSupport_test accessed - bypassing session check");
    }

    public function index() {
        echo "<h2>Customer Support Test Controller</h2>";
        echo "<p>This is a test to verify the controller works without session issues.</p>";
        
        echo "<h3>Session Data:</h3>";
        echo "<pre>";
        print_r($this->session->all_userdata());
        echo "</pre>";
        
        echo "<h3>Base URL:</h3>";
        echo base_url() . "<br>";
        
        echo "<h3>Current User:</h3>";
        $user_id = $this->session->userdata('id');
        $user_level = $this->session->userdata('level');
        echo "User ID: " . ($user_id ?: 'NULL') . "<br>";
        echo "User Level: " . ($user_level ?: 'NULL') . "<br>";
        
        echo "<h3>Test Links:</h3>";
        echo "<a href='" . base_url('CustomerSupport') . "'>Try Original CustomerSupport</a><br>";
        echo "<a href='" . base_url('index.php/CustomerSupport') . "'>Try with index.php</a><br>";
        echo "<a href='" . base_url('debug_support.php') . "'>Run Debug Script</a><br>";
        
        if ($user_id && $user_level === 'Admin') {
            echo "<h3>Access Granted!</h3>";
            echo "<p>You are logged in as Admin. The original controller should work.</p>";
            echo "<a href='" . base_url('CustomerSupport') . "' class='btn btn-primary'>Go to Customer Support</a>";
        } else {
            echo "<h3>Access Issues Detected</h3>";
            echo "<p>Session problems detected. Please check:</p>";
            echo "<ul>";
            echo "<li>Are you logged in to the system?</li>";
            echo "<li>Is your user level set to 'Admin'?</li>";
            echo "<li>Try logging out and logging back in</li>";
            echo "</ul>";
            echo "<a href='" . base_url('login') . "' class='btn btn-warning'>Login Again</a>";
        }
    }
}
?>
