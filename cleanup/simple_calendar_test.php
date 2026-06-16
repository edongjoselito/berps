<?php
// Simple standalone test for calendar functionality
session_start();

echo "<h2>Simple Calendar Test</h2>";

// Test 1: Check MySQL server status
echo "<h3>1. MySQL Server Status</h3>";
try {
    // Try different connection methods
    $connections = array(
        array('host' => 'localhost', 'port' => '3306'),
        array('host' => '127.0.0.1', 'port' => '3306'),
        array('host' => 'localhost', 'port' => '3307'),
        array('host' => '127.0.0.1', 'port' => '3307')
    );
    
    $connected = false;
    foreach ($connections as $config) {
        try {
            $conn = new PDO("mysql:host={$config['host']};port={$config['port']}", "root", "");
            echo "<p style='color: green;'>MySQL connected on {$config['host']}:{$config['port']}</p>";
            $connected = true;
            break;
        } catch (PDOException $e) {
            echo "<p style='color: orange;'>Failed to connect to {$config['host']}:{$config['port']} - {$e->getMessage()}</p>";
        }
    }
    
    if (!$connected) {
        echo "<p style='color: red;'>Cannot connect to MySQL server. Please check:</p>";
        echo "<ul>";
        echo "<li>XAMPP MySQL service is running</li>";
        echo "<li>MySQL is on port 3306 or 3307</li>";
        echo "<li>No firewall blocking MySQL</li>";
        echo "</ul>";
        exit;
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>MySQL connection test failed: " . $e->getMessage() . "</p>";
    exit;
}

// Test 2: Check database
echo "<h3>2. Database Check</h3>";
try {
    $conn = new PDO("mysql:host=localhost;dbname=berps", "root", "");
    echo "<p style='color: green;'>Database 'berps' accessible</p>";
} catch (PDOException $e) {
    echo "<p style='color: red;'>Cannot access 'berps' database: " . $e->getMessage() . "</p>";
    echo "<p>Trying to create database...</p>";
    try {
        $conn = new PDO("mysql:host=localhost", "root", "");
        $conn->exec("CREATE DATABASE IF NOT EXISTS berps");
        echo "<p style='color: green;'>Database 'berps' created/verified</p>";
        $conn = new PDO("mysql:host=localhost;dbname=berps", "root", "");
    } catch (PDOException $e2) {
        echo "<p style='color: red;'>Failed to create database: " . $e2->getMessage() . "</p>";
        exit;
    }
}

// Test 3: Check calendar_events table
echo "<h3>3. Calendar Events Table</h3>";
try {
    $stmt = $conn->query("SHOW TABLES LIKE 'calendar_events'");
    if ($stmt->rowCount() > 0) {
        echo "<p style='color: green;'>calendar_events table exists</p>";
        
        // Show table info
        $stmt = $conn->query("SELECT COUNT(*) as count FROM calendar_events");
        $count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        echo "<p>Total events: $count</p>";
        
    } else {
        echo "<p style='color: orange;'>calendar_events table missing. Creating...</p>";
        
        // Create table
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
        
        $conn->exec($sql);
        echo "<p style='color: green;'>calendar_events table created successfully</p>";
    }
} catch (PDOException $e) {
    echo "<p style='color: red;'>Table error: " . $e->getMessage() . "</p>";
}

// Test 4: Session check
echo "<h3>4. Session Check</h3>";
echo "<p>Session ID: " . session_id() . "</p>";
echo "<p>User ID: " . ($_SESSION['id'] ?? 'Not set') . "</p>";
echo "<p>Username: " . ($_SESSION['username'] ?? 'Not set') . "</p>";
echo "<p>SettingsID: " . ($_SESSION['settingsID'] ?? 'Not set') . "</p>";

// Test 5: Create test event
echo "<h3>5. Test Event Creation</h3>";
if ($_POST['create_test']) {
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
            $event_id = $conn->lastInsertId();
            echo "<p style='color: green;'>Test event created! ID: $event_id</p>";
            
            // Verify event
            $stmt = $conn->prepare("SELECT * FROM calendar_events WHERE id = ?");
            $stmt->execute([$event_id]);
            $event = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($event) {
                echo "<h4>Created Event:</h4>";
                echo "<pre>";
                print_r($event);
                echo "</pre>";
            }
        } else {
            echo "<p style='color: red;'>Failed to create test event</p>";
        }
    } catch (PDOException $e) {
        echo "<p style='color: red;'>Error creating event: " . $e->getMessage() . "</p>";
    }
}

// Test 6: Test Calendar controller URL
echo "<h3>6. Calendar Controller Test</h3>";
$calendar_url = 'http://localhost/berps/Calendar/add_event';
echo "<p>Testing URL: $calendar_url</p>";

// Use cURL to test the endpoint
if (function_exists('curl_init')) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $calendar_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(array(
        'title' => 'CURL Test Event',
        'start_date' => date('Y-m-d H:i:s'),
        'end_date' => date('Y-m-d H:i:s', strtotime('+1 hour'))
    )));
    curl_setopt($ch, CURLOPT_COOKIE, session_name() . '=' . session_id());
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    echo "<p>HTTP Status: $http_code</p>";
    echo "<p>Response:</p>";
    echo "<pre>" . htmlspecialchars($response) . "</pre>";
} else {
    echo "<p>cURL not available for testing</p>";
}
?>

<hr>

<h3>Create Test Event</h3>
<form method="post">
    <input type="hidden" name="create_test" value="1">
    <table>
        <tr>
            <td>Title:</td>
            <td><input type="text" name="title" value="Simple Test Event" required></td>
        </tr>
        <tr>
            <td>Start:</td>
            <td><input type="datetime-local" name="start_date" value="<?php echo date('Y-m-d\TH:i'); ?>" required></td>
        </tr>
        <tr>
            <td>End:</td>
            <td><input type="datetime-local" name="end_date" value="<?php echo date('Y-m-d\TH:i', strtotime('+1 hour')); ?>" required></td>
        </tr>
        <tr>
            <td colspan="2"><button type="submit">Create Test Event</button></td>
        </tr>
    </table>
</form>

<hr>
<p>
    <a href="Calendar">Go to Calendar</a> | 
    <a href="Page/admin">Admin Dashboard</a>
</p>

<style>
table { border-collapse: collapse; margin: 10px 0; }
table td { border: 1px solid #ddd; padding: 8px; }
pre { background: #f5f5f5; padding: 10px; border-radius: 4px; }
</style>
