<?php
// Debug script to check session and routing issues
session_start();

echo "<h2>Customer Support Debug Information</h2>";

echo "<h3>Current Session Data:</h3>";
echo "<pre>";
echo "Session ID: " . session_id() . "\n";
echo "Session Status: " . session_status() . "\n";
echo "Session Data:\n";
print_r($_SESSION);
echo "</pre>";

echo "<h3>CodeIgniter Session Check:</h3>";
if (file_exists('application/libraries/Session.php')) {
    echo "Session library exists<br>";
} else {
    echo "Session library NOT found<br>";
}

echo "<h3>Current URL Analysis:</h3>";
echo "Current URL: " . $_SERVER['REQUEST_URI'] . "<br>";
echo "Script Name: " . $_SERVER['SCRIPT_NAME'] . "<br>";
echo "Host: " . $_SERVER['HTTP_HOST'] . "<br>";

echo "<h3>Base URL Check:</h3>";
$base_url = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') ? "https" : "http");
$base_url .= "://".$_SERVER['HTTP_HOST'];
$base_url .= str_replace(basename($_SERVER['SCRIPT_NAME']), "", $_SERVER['SCRIPT_NAME']);
echo "Calculated Base URL: " . $base_url . "<br>";

echo "<h3>CustomerSupport Controller Check:</h3>";
if (file_exists('application/controllers/CustomerSupport.php')) {
    echo "CustomerSupport controller exists<br>";
    
    // Check for syntax errors
    $content = file_get_contents('application/controllers/CustomerSupport.php');
    if (strpos($content, 'class CustomerSupport') !== false) {
        echo "CustomerSupport class found<br>";
    } else {
        echo "CustomerSupport class NOT found<br>";
    }
    
    // Check constructor
    if (strpos($content, 'public function __construct') !== false) {
        echo "Constructor found<br>";
    } else {
        echo "Constructor NOT found<br>";
    }
    
    // Check index method
    if (strpos($content, 'public function index') !== false) {
        echo "Index method found<br>";
    } else {
        echo "Index method NOT found<br>";
    }
} else {
    echo "CustomerSupport controller NOT found<br>";
}

echo "<h3>.htaccess Check:</h3>";
if (file_exists('.htaccess')) {
    echo ".htaccess exists<br>";
    echo "Content:<br>";
    echo "<pre>" . htmlspecialchars(file_get_contents('.htaccess')) . "</pre>";
} else {
    echo ".htaccess NOT found<br>";
}

echo "<h3>Database Connection Test:</h3>";
include 'application/config/database.php';
try {
    $conn = new mysqli($db['default']['hostname'], $db['default']['username'], $db['default']['password'], $db['default']['database']);
    if ($conn->connect_error) {
        echo "Database connection failed: " . $conn->connect_error . "<br>";
    } else {
        echo "Database connection successful<br>";
        
        // Check if support tables exist
        $result = $conn->query("SHOW TABLES LIKE 'support_departments'");
        if ($result->num_rows > 0) {
            echo "Support tables appear to exist<br>";
        } else {
            echo "Support tables NOT found<br>";
        }
    }
    $conn->close();
} catch (Exception $e) {
    echo "Database error: " . $e->getMessage() . "<br>";
}

echo "<h3>Test Direct Access:</h3>";
echo "<a href='" . $base_url . "CustomerSupport'>Try CustomerSupport</a><br>";
echo "<a href='" . $base_url . "index.php/CustomerSupport'>Try index.php/CustomerSupport</a><br>";

echo "<h3>Current User Level Check:</h3>";
if (isset($_SESSION['level'])) {
    echo "User Level: " . $_SESSION['level'] . "<br>";
    if ($_SESSION['level'] === 'Admin') {
        echo "User is Admin - should have access<br>";
    } else {
        echo "User is NOT Admin - may have limited access<br>";
    }
} else {
    echo "User Level not set in session<br>";
}

echo "<hr>";
echo "<h3>Next Steps:</h3>";
echo "<ol>";
echo "<li>Check if you're logged in (session should have user data)</li>";
echo "<li>Verify your user level is 'Admin'</li>";
echo "<li>Try accessing with index.php in the URL</li>";
echo "<li>Check .htaccess for routing issues</li>";
echo "</ol>";

echo "<br>";
echo "<a href='" . $base_url . "'>Back to Home</a> | ";
echo "<a href='" . $base_url . "Page/admin'>Admin Dashboard</a>";
?>
