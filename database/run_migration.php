<?php
// Database migration runner for expense attachments
// Run this script to add the attachment column to the expenses table

$host = '127.0.0.1';
$username = 'root';
$password = 'moth34board';
$database = 'itwebpor_cms';

try {
    // Connect to database
    $conn = new PDO("mysql:host=$host;dbname=$database", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Connected to database successfully.\n";
    
    // Check if column already exists
    $stmt = $conn->prepare("SHOW COLUMNS FROM expenses LIKE 'attachment'");
    $stmt->execute();
    $columnExists = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($columnExists) {
        echo "Column 'attachment' already exists in expenses table.\n";
    } else {
        // Add the attachment column
        $sql = "ALTER TABLE `expenses` ADD COLUMN `attachment` VARCHAR(255) NULL DEFAULT NULL AFTER `processedBy`";
        $conn->exec($sql);
        echo "Column 'attachment' added successfully to expenses table.\n";
    }
    
    echo "Migration completed successfully!\n";
    
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

$conn = null;
?>
