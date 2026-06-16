<?php
// Debug script for calendar functionality
session_start();

// Database connection
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'berps';

try {
    $conn = new PDO("mysql:host=$host;dbname=$database", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<h2>Database Connection: SUCCESS</h2>";
    
    // Check if calendar_events table exists
    $stmt = $conn->query("SHOW TABLES LIKE 'calendar_events'");
    $table_exists = $stmt->rowCount() > 0;
    
    echo "<h3>Calendar Events Table: " . ($table_exists ? "EXISTS" : "NOT FOUND") . "</h3>";
    
    if ($table_exists) {
        // Show table structure
        $stmt = $conn->query("DESCRIBE calendar_events");
        echo "<h4>Table Structure:</h4>";
        echo "<table border='1'>";
        echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th></tr>";
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "<tr><td>{$row['Field']}</td><td>{$row['Type']}</td><td>{$row['Null']}</td><td>{$row['Key']}</td></tr>";
        }
        echo "</table>";
        
        // Show sample data
        $stmt = $conn->query("SELECT COUNT(*) as count FROM calendar_events");
        $count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        echo "<h4>Total Events: $count</h4>";
        
        if ($count > 0) {
            $stmt = $conn->query("SELECT * FROM calendar_events LIMIT 5");
            echo "<h4>Sample Events:</h4>";
            echo "<table border='1'>";
            echo "<tr><th>ID</th><th>Title</th><th>Start Date</th><th>User ID</th></tr>";
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                echo "<tr><td>{$row['id']}</td><td>{$row['title']}</td><td>{$row['start_date']}</td><td>{$row['user_id']}</td></tr>";
            }
            echo "</table>";
        }
    } else {
        echo "<h4>Creating calendar_events table...</h4>";
        
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
        
        $conn->exec($sql);
        echo "<h4>Table created successfully!</h4>";
    }
    
    // Check session data
    echo "<h3>Session Data:</h3>";
    echo "<pre>";
    print_r($_SESSION);
    echo "</pre>";
    
    // Test POST data handling
    echo "<h3>POST Test:</h3>";
    if ($_POST) {
        echo "<pre>";
        print_r($_POST);
        echo "</pre>";
    } else {
        echo "No POST data received";
    }
    
} catch(PDOException $e) {
    echo "<h2>Database Connection: FAILED</h2>";
    echo "<p>Error: " . $e->getMessage() . "</p>";
}
?>

<hr>
<h3>Test Event Creation Form</h3>
<form method="post" action="">
    <input type="hidden" name="test_event" value="1">
    <label>Title: <input type="text" name="title" value="Test Event" required></label><br><br>
    <label>Start: <input type="datetime-local" name="start_date" value="2024-04-16T10:00" required></label><br><br>
    <label>End: <input type="datetime-local" name="end_date" value="2024-04-16T11:00" required></label><br><br>
    <label>Description: <input type="text" name="description" value="Test description"></label><br><br>
    <button type="submit">Create Test Event</button>
</form>

<?php
if (isset($_POST['test_event'])) {
    try {
        $title = $_POST['title'];
        $description = $_POST['description'];
        $start_date = $_POST['start_date'];
        $end_date = $_POST['end_date'];
        $user_id = isset($_SESSION['id']) ? $_SESSION['id'] : 1;
        $settingsID = isset($_SESSION['settingsID']) ? $_SESSION['settingsID'] : 1;
        
        $sql = "INSERT INTO calendar_events (title, description, start_date, end_date, user_id, settingsID) 
                VALUES (:title, :description, :start_date, :end_date, :user_id, :settingsID)";
        
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':title', $title);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':start_date', $start_date);
        $stmt->bindParam(':end_date', $end_date);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':settingsID', $settingsID);
        
        $stmt->execute();
        
        echo "<h4>Test event created successfully! ID: " . $conn->lastInsertId() . "</h4>";
        
    } catch(PDOException $e) {
        echo "<h4>Error creating test event: " . $e->getMessage() . "</h4>";
    }
}
?>
