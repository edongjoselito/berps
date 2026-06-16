<?php
// Comprehensive calendar debugging script
session_start();

echo "<h2>Calendar Debug Report</h2>";

// 1. Check session
echo "<h3>1. Session Status</h3>";
if (session_status() === PHP_SESSION_ACTIVE) {
    echo "<p style='color: green;'>Session is active</p>";
} else {
    echo "<p style='color: red;'>Session is not active</p>";
}

echo "<h4>Session Data:</h4>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

// 2. Database connection
echo "<h3>2. Database Connection</h3>";
try {
    $conn = new PDO("mysql:host=localhost;dbname=berps", "root", "");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "<p style='color: green;'>Database connection successful</p>";
    
    // 3. Check calendar_events table
    echo "<h3>3. Calendar Events Table</h3>";
    $stmt = $conn->query("SHOW TABLES LIKE 'calendar_events'");
    $table_exists = $stmt->rowCount() > 0;
    
    if ($table_exists) {
        echo "<p style='color: green;'>calendar_events table exists</p>";
        
        // Show table structure
        $stmt = $conn->query("DESCRIBE calendar_events");
        echo "<h4>Table Structure:</h4>";
        echo "<table border='1'>";
        echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "<tr><td>{$row['Field']}</td><td>{$row['Type']}</td><td>{$row['Null']}</td><td>{$row['Key']}</td><td>{$row['Default']}</td></tr>";
        }
        echo "</table>";
        
        // Count events
        $stmt = $conn->query("SELECT COUNT(*) as count FROM calendar_events");
        $count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        echo "<h4>Total Events: $count</h4>";
        
        // Show recent events
        if ($count > 0) {
            $stmt = $conn->query("SELECT * FROM calendar_events ORDER BY created_at DESC LIMIT 5");
            echo "<h4>Recent Events:</h4>";
            echo "<table border='1'>";
            echo "<tr><th>ID</th><th>Title</th><th>User ID</th><th>SettingsID</th><th>Start Date</th><th>Status</th></tr>";
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                echo "<tr><td>{$row['id']}</td><td>{$row['title']}</td><td>{$row['user_id']}</td><td>{$row['settingsID']}</td><td>{$row['start_date']}</td><td>{$row['status']}</td></tr>";
            }
            echo "</table>";
        }
    } else {
        echo "<p style='color: red;'>calendar_events table does not exist</p>";
        echo "<p>Run the SQL file: create_calendar_events_table.sql</p>";
    }
    
} catch(PDOException $e) {
    echo "<p style='color: red;'>Database connection failed: " . $e->getMessage() . "</p>";
}

// 4. Test POST request simulation
echo "<h3>4. POST Request Test</h3>";
if ($_POST) {
    echo "<h4>POST Data Received:</h4>";
    echo "<pre>";
    print_r($_POST);
    echo "</pre>";
    
    // Test inserting an event
    if (isset($_POST['test_create_event'])) {
        try {
            $title = $_POST['title'] ?? 'Test Event';
            $start_date = $_POST['start_date'] ?? date('Y-m-d H:i:s');
            $end_date = $_POST['end_date'] ?? date('Y-m-d H:i:s', strtotime('+1 hour'));
            $user_id = $_SESSION['id'] ?? 1;
            $settingsID = $_SESSION['settingsID'] ?? 1;
            
            $sql = "INSERT INTO calendar_events (title, start_date, end_date, user_id, settingsID) 
                    VALUES (:title, :start_date, :end_date, :user_id, :settingsID)";
            
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':title', $title);
            $stmt->bindParam(':start_date', $start_date);
            $stmt->bindParam(':end_date', $end_date);
            $stmt->bindParam(':user_id', $user_id);
            $stmt->bindParam(':settingsID', $settingsID);
            
            if ($stmt->execute()) {
                echo "<p style='color: green;'>Test event created successfully! ID: " . $conn->lastInsertId() . "</p>";
            } else {
                echo "<p style='color: red;'>Failed to create test event</p>";
            }
        } catch(PDOException $e) {
            echo "<p style='color: red;'>Error creating test event: " . $e->getMessage() . "</p>";
        }
    }
} else {
    echo "<p>No POST data received</p>";
}

// 5. Test AJAX endpoint
echo "<h3>5. AJAX Endpoint Test</h3>";
echo "<p>Testing Calendar/add_event endpoint...</p>";

// Simulate what the JavaScript sends
$test_data = array(
    'title' => 'AJAX Test Event',
    'description' => 'Testing AJAX endpoint',
    'start_date' => date('Y-m-d H:i:s'),
    'end_date' => date('Y-m-d H:i:s', strtotime('+1 hour')),
    'all_day' => 0,
    'event_type' => 'default',
    'color' => '#3788d8',
    'location' => '',
    'reminder_time' => 15,
    'is_public' => 0
);

echo "<h4>Test Data:</h4>";
echo "<pre>";
print_r($test_data);
echo "</pre>";

// 6. Check file permissions and paths
echo "<h3>6. File System Check</h3>";
$files_to_check = array(
    'application/controllers/Calendar.php',
    'application/views/calendar_view.php',
    'create_calendar_events_table.sql'
);

foreach ($files_to_check as $file) {
    if (file_exists($file)) {
        echo "<p style='color: green;'>$file exists</p>";
    } else {
        echo "<p style='color: red;'>$file missing</p>";
    }
}

// 7. PHP Error Reporting
echo "<h3>7. PHP Error Reporting</h3>";
echo "<p>Current error reporting level: " . error_reporting() . "</p>";
echo "<p>Display errors: " . (ini_get('display_errors') ? 'On' : 'Off') . "</p>";
echo "<p>Log errors: " . (ini_get('log_errors') ? 'On' : 'Off') . "</p>";
echo "<p>Error log file: " . ini_get('error_log') . "</p>";
?>

<hr>

<h3>Test Event Creation Form</h3>
<form method="post" action="">
    <input type="hidden" name="test_create_event" value="1">
    <table>
        <tr>
            <td><label>Title:</label></td>
            <td><input type="text" name="title" value="Debug Test Event" required></td>
        </tr>
        <tr>
            <td><label>Start Date:</label></td>
            <td><input type="datetime-local" name="start_date" value="<?php echo date('Y-m-d\TH:i'); ?>" required></td>
        </tr>
        <tr>
            <td><label>End Date:</label></td>
            <td><input type="datetime-local" name="end_date" value="<?php echo date('Y-m-d\TH:i', strtotime('+1 hour')); ?>" required></td>
        </tr>
        <tr>
            <td><label>Description:</label></td>
            <td><input type="text" name="description" value="Debug test event"></td>
        </tr>
        <tr>
            <td colspan="2"><button type="submit">Create Test Event</button></td>
        </tr>
    </table>
</form>

<hr>

<h3>Quick Actions</h3>
<p>
    <a href="Calendar">Go to Calendar</a> | 
    <a href="Page/admin">Admin Dashboard</a> | 
    <a href="setup_calendar_table.php">Setup Calendar Table</a>
</p>

<style>
table {
    border-collapse: collapse;
    margin: 10px 0;
}
table td, table th {
    border: 1px solid #ddd;
    padding: 8px;
    text-align: left;
}
table th {
    background-color: #f2f2f2;
}
pre {
    background: #f5f5f5;
    padding: 10px;
    border-radius: 4px;
    overflow-x: auto;
}
</style>
