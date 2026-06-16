<?php
// Simple script to check if Customer Support tables exist
include 'application/config/database.php';

try {
    $conn = new mysqli($db['default']['hostname'], $db['default']['username'], $db['default']['password'], $db['default']['database']);
    
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
    echo "<h2>Customer Support Database Tables Check</h2>";
    
    $tables_to_check = [
        'support_departments',
        'employee_departments', 
        'support_issues',
        'support_issue_comments',
        'support_notifications',
        'support_issue_history',
        'support_knowledge_base',
        'support_satisfaction_ratings',
        'support_sla_tracking',
        'support_issue_attachments',
        'support_settings',
        'support_templates',
        'support_escalation_rules',
        'support_time_tracking',
        'support_customer_feedback',
        'support_reports',
        'support_audit_log'
    ];
    
    $missing_tables = [];
    $existing_tables = [];
    
    foreach ($tables_to_check as $table) {
        $result = $conn->query("SHOW TABLES LIKE '$table'");
        if ($result->num_rows > 0) {
            $existing_tables[] = $table;
            echo "<p style='color: green;'>&#10004; Table '$table' exists</p>";
        } else {
            $missing_tables[] = $table;
            echo "<p style='color: red;'>&#10008; Table '$table' is missing</p>";
        }
    }
    
    echo "<hr>";
    
    if (!empty($missing_tables)) {
        echo "<h3 style='color: red;'>Missing Tables Found!</h3>";
        echo "<p>The following tables are missing: " . implode(', ', $missing_tables) . "</p>";
        echo "<h3>Solution:</h3>";
        echo "<p>You need to run the database schema script. Here are the steps:</p>";
        echo "<ol>";
        echo "<li>Open your MySQL client (phpMyAdmin, MySQL Workbench, or command line)</li>";
        echo "<li>Run the SQL script: <strong>database/customer_support_schema.sql</strong></li>";
        echo "<li>Or run this command in your MySQL client:</li>";
        echo "</ol>";
        echo "<pre>mysql -u root -p berps < database/customer_support_schema.sql</pre>";
        echo "<p>After creating the tables, the Customer Support system should work properly.</p>";
    } else {
        echo "<h3 style='color: green;'>All Tables Exist!</h3>";
        echo "<p>The Customer Support database tables are properly installed.</p>";
        echo "<p>If you're still experiencing redirect issues, the problem might be:</p>";
        echo "<ul>";
        echo "<li>Session not properly initialized</li>";
        echo "<li>CodeIgniter routing issue</li>";
        echo "<li>Permission problems</li>";
        echo "</ul>";
    }
    
    echo "<hr>";
    echo "<h3>Current Session Data:</h3>";
    session_start();
    echo "<pre>";
    echo "Session ID: " . session_id() . "\n";
    echo "Session Data: ";
    print_r($_SESSION);
    echo "</pre>";
    
    $conn->close();
    
} catch (Exception $e) {
    echo "<h3>Error:</h3>";
    echo "<p>" . $e->getMessage() . "</p>";
}
?>

<br>
<a href="<?= base_url(); ?>">Back to Main Site</a> | 
<a href="<?= base_url('Page/admin'); ?>">Admin Dashboard</a>
