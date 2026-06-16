<?php
// Test Manila timezone configuration
echo "<h2>Timezone Test for BERPS System</h2>";

// Test 1: Check default timezone
echo "<h3>1. Default Timezone:</h3>";
echo "Current default timezone: " . date_default_timezone_get() . "<br>";

// Test 2: Show current time in Manila
echo "<h3>2. Current Time in Manila:</h3>";
$manila_time = new DateTime('now', new DateTimeZone('Asia/Manila'));
echo "Manila Time: " . $manila_time->format('Y-m-d H:i:s T') . "<br>";

// Test 3: Show UTC time for comparison
echo "<h3>3. Current UTC Time:</h3>";
$utc_time = new DateTime('now', new DateTimeZone('UTC'));
echo "UTC Time: " . $utc_time->format('Y-m-d H:i:s T') . "<br>";

// Test 4: Test CodeIgniter timezone setting
echo "<h3>4. CodeIgniter Time Reference:</h3>";
define('BASEPATH', true);
include_once 'application/config/config.php';
echo "CI Time Reference: " . $config['time_reference'] . "<br>";

// Test 5: Test PHP date functions
echo "<h3>5. PHP Date Functions:</h3>";
echo "date(): " . date('Y-m-d H:i:s') . "<br>";
echo "gmdate(): " . gmdate('Y-m-d H:i:s') . "<br>";

// Test 6: Test MySQL timezone (if connection available)
echo "<h3>6. MySQL Timezone (if available):</h3>";
try {
    $pdo = new PDO('mysql:host=localhost;dbname=berps', 'root', '');
    $stmt = $pdo->query("SELECT @@global.time_zone, @@session.time_zone");
    $result = $stmt->fetch(PDO::FETCH_NUM);
    echo "MySQL Global Timezone: " . $result[0] . "<br>";
    echo "MySQL Session Timezone: " . $result[1] . "<br>";
} catch (Exception $e) {
    echo "MySQL connection not available or error: " . $e->getMessage() . "<br>";
}

echo "<h3>Summary:</h3>";
echo "If Manila timezone is properly set, you should see:<br>";
echo "- Default timezone: Asia/Manila<br>";
echo "- CI Time Reference: Asia/Manila<br>";
echo "- Manila Time should be UTC+8<br>";
?>
