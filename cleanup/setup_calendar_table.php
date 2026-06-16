<?php
// Quick script to setup calendar events table
session_start();

// Load CodeIgniter
require_once 'system/core/CodeIgniter.php';

$CI =& get_instance();
$CI->load->database();

echo "<h2>Calendar Events Table Setup</h2>";

// Check if table exists
$table_exists = $CI->db->table_exists('calendar_events');

if ($table_exists) {
    echo "<p style='color: green;'>Calendar events table already exists.</p>";
    
    // Show table info
    $query = $CI->db->get('calendar_events');
    $count = $query->num_rows();
    echo "<p>Total events in table: $count</p>";
    
    if ($count > 0) {
        echo "<h4>Sample Events:</h4>";
        $query = $CI->db->get('calendar_events', 5);
        echo "<table border='1'>";
        echo "<tr><th>ID</th><th>Title</th><th>Start</th><th>User ID</th></tr>";
        foreach ($query->result() as $row) {
            echo "<tr><td>{$row->id}</td><td>{$row->title}</td><td>{$row->start_date}</td><td>{$row->user_id}</td></tr>";
        }
        echo "</table>";
    }
} else {
    echo "<p style='color: orange;'>Creating calendar events table...</p>";
    
    // Create the table
    $sql = "CREATE TABLE calendar_events (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        description TEXT,
        start_date DATETIME NOT NULL,
        end_date DATETIME NOT NULL,
        all_day BOOLEAN DEFAULT FALSE,
        event_type VARCHAR(50) DEFAULT 'default',
        color VARCHAR(7) DEFAULT '#3788d8',
        user_id INT NOT NULL,
        settingsID INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        status ENUM('active', 'cancelled') DEFAULT 'active',
        reminder_time INT DEFAULT 15,
        location VARCHAR(255),
        is_public BOOLEAN DEFAULT FALSE,
        INDEX idx_user_id (user_id),
        INDEX idx_settingsID (settingsID),
        INDEX idx_start_date (start_date),
        INDEX idx_status (status)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    try {
        $CI->db->query($sql);
        echo "<p style='color: green;'>Calendar events table created successfully!</p>";
        
        // Add a test event
        if (isset($_SESSION['id'])) {
            $test_event = array(
                'title' => 'Test Calendar Event',
                'description' => 'This is a test event created during setup',
                'start_date' => date('Y-m-d H:i:s'),
                'end_date' => date('Y-m-d H:i:s', strtotime('+1 hour')),
                'user_id' => $_SESSION['id'],
                'settingsID' => $_SESSION['settingsID'] ?? 1,
                'event_type' => 'default',
                'color' => '#3788d8'
            );
            
            $CI->db->insert('calendar_events', $test_event);
            echo "<p style='color: green;'>Test event added for user ID: " . $_SESSION['id'] . "</p>";
        }
        
    } catch (Exception $e) {
        echo "<p style='color: red;'>Error creating table: " . $e->getMessage() . "</p>";
    }
}

echo "<hr>";
echo "<h3>Session Information:</h3>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

echo "<hr>";
echo "<p><a href='Calendar'>Go to Calendar</a> | <a href='Page/admin'>Go to Admin Dashboard</a></p>";
?>
