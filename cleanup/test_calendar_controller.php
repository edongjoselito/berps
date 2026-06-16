<?php
// Test the Calendar controller directly
session_start();

echo "<h2>Calendar Controller Test</h2>";

// Simulate a POST request to Calendar/add_event
$_SERVER['REQUEST_METHOD'] = 'POST';
$_POST['title'] = 'Controller Test Event';
$_POST['start_date'] = date('Y-m-d H:i:s');
$_POST['end_date'] = date('Y-m-d H:i:s', strtotime('+1 hour'));
$_POST['description'] = 'Testing controller directly';
$_POST['all_day'] = '0';
$_POST['event_type'] = 'default';
$_POST['color'] = '#3788d8';

// Load CodeIgniter
define('ENVIRONMENT', 'development');
define('BASEPATH', 'system/');
define('APPPATH', 'application/');

// Load the database config
require_once 'application/config/database.php';

// Test database connection directly
try {
    $conn = new PDO("mysql:host=localhost;dbname=berps", "root", "");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "<p style='color: green;'>Database connection successful</p>";
    
    // Test inserting event directly
    $title = $_POST['title'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $description = $_POST['description'];
    $user_id = $_SESSION['id'] ?? 1;
    $settingsID = $_SESSION['settingsID'] ?? 1;
    
    echo "<h3>Testing Direct Event Insert</h3>";
    echo "<p>User ID: $user_id</p>";
    echo "<p>SettingsID: $settingsID</p>";
    echo "<p>Title: $title</p>";
    echo "<p>Start: $start_date</p>";
    echo "<p>End: $end_date</p>";
    
    // Check if table exists
    $stmt = $conn->query("SHOW TABLES LIKE 'calendar_events'");
    if ($stmt->rowCount() > 0) {
        echo "<p style='color: green;'>calendar_events table exists</p>";
        
        // Insert test event
        $sql = "INSERT INTO calendar_events (title, description, start_date, end_date, user_id, settingsID) 
                VALUES (:title, :description, :start_date, :end_date, :user_id, :settingsID)";
        
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':title', $title);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':start_date', $start_date);
        $stmt->bindParam(':end_date', $end_date);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':settingsID', $settingsID);
        
        if ($stmt->execute()) {
            $event_id = $conn->lastInsertId();
            echo "<p style='color: green;'>Event inserted successfully! ID: $event_id</p>";
            
            // Verify the event was inserted
            $stmt = $conn->prepare("SELECT * FROM calendar_events WHERE id = ?");
            $stmt->execute([$event_id]);
            $event = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($event) {
                echo "<h4>Inserted Event Details:</h4>";
                echo "<pre>";
                print_r($event);
                echo "</pre>";
            }
        } else {
            echo "<p style='color: red;'>Failed to insert event</p>";
        }
    } else {
        echo "<p style='color: red;'>calendar_events table does not exist</p>";
    }
    
} catch(PDOException $e) {
    echo "<p style='color: red;'>Database error: " . $e->getMessage() . "</p>";
}

// Test the actual Calendar controller if possible
echo "<h3>Testing Calendar Controller</h3>";
try {
    // Try to include and test the Calendar controller
    if (file_exists('application/controllers/Calendar.php')) {
        echo "<p style='color: green;'>Calendar.php controller file exists</p>";
        
        // Check if we can load it (this might not work due to CI dependencies)
        require_once 'application/controllers/Calendar.php';
        
        if (class_exists('Calendar')) {
            echo "<p style='color: green;'>Calendar class exists</p>";
        } else {
            echo "<p style='color: orange;'>Calendar class not found (expected due to CI dependencies)</p>";
        }
    } else {
        echo "<p style='color: red;'>Calendar.php controller file missing</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: orange;'>Controller test failed (expected): " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<h3>Next Steps</h3>";
echo "<ol>";
echo "<li>Run this test to verify database insertion works</li>";
echo "<li>If successful, the issue is likely in the AJAX request or controller</li>";
echo "<li>If unsuccessful, the issue is in the database or table structure</li>";
echo "</ol>";

echo "<p><a href='calendar_debug.php'>Run Full Debug</a> | <a href='Calendar'>Go to Calendar</a></p>";
?>
