<?php
// Direct database migration - Run this from terminal
// php database/migrate_calendar_columns.php

$config = array(
    'host' => 'localhost',
    'user' => 'root',
    'password' => '',
    'database' => 'itwebpor_cms'
);

$mysqli = new mysqli($config['host'], $config['user'], $config['password'], $config['database']);

if ($mysqli->connect_error) {
    die(json_encode(array(
        'success' => false,
        'message' => 'Database connection failed: ' . $mysqli->connect_error
    )));
}

$mysqli->set_charset('utf8mb4');

$columns_to_add = array(
    'task_id' => 'INT DEFAULT 0',
    'is_completed' => 'TINYINT(1) DEFAULT 1 COMMENT "0 = completed, 1 = not completed"',
    'reminder_email_enabled' => 'TINYINT(1) DEFAULT 0',
    'reminder_email' => 'VARCHAR(255) DEFAULT NULL',
    'notes' => 'TEXT DEFAULT NULL'
);

$completed = array();
$errors = array();

// Check and add columns
foreach ($columns_to_add as $column => $definition) {
    $check_column = $mysqli->query("SHOW COLUMNS FROM calendar_events WHERE Field = '$column'");
    
    if ($check_column && $check_column->num_rows == 0) {
        $position = $column === 'task_id' ? 'AFTER status' : 'AFTER ' . array_keys($columns_to_add)[array_key_first(array_filter(array_keys($columns_to_add), fn($k) => $k !== $column && array_search($k, array_keys($columns_to_add)) < array_search($column, array_keys($columns_to_add))))];
        
        if ($column === 'task_id') {
            $position = 'AFTER status';
        } elseif ($column === 'is_completed') {
            $position = 'AFTER task_id';
        } elseif ($column === 'reminder_email_enabled') {
            $position = 'AFTER is_completed';
        } elseif ($column === 'reminder_email') {
            $position = 'AFTER reminder_email_enabled';
        } elseif ($column === 'notes') {
            $position = 'AFTER reminder_email';
        }
        
        $sql = "ALTER TABLE calendar_events ADD COLUMN $column $definition $position";
        
        if ($mysqli->query($sql)) {
            $completed[] = "✓ Added column: $column";
            echo "✓ Added column: $column\n";
        } else {
            $errors[] = "✗ Failed to add column $column: " . $mysqli->error;
            echo "✗ Failed to add column $column: " . $mysqli->error . "\n";
        }
    } else {
        $completed[] = "✓ Column $column already exists";
        echo "✓ Column $column already exists\n";
    }
}

// Add indexes
$indexes = array(
    'idx_task_id' => 'ALTER TABLE calendar_events ADD INDEX idx_task_id (task_id)',
    'idx_is_completed' => 'ALTER TABLE calendar_events ADD INDEX idx_is_completed (is_completed)'
);

foreach ($indexes as $index_name => $index_sql) {
    $check_index = $mysqli->query("SHOW INDEX FROM calendar_events WHERE Key_name = '$index_name'");
    
    if ($check_index && $check_index->num_rows == 0) {
        if ($mysqli->query($index_sql)) {
            $completed[] = "✓ Added index: $index_name";
            echo "✓ Added index: $index_name\n";
        } else {
            $errors[] = "✗ Failed to add index $index_name: " . $mysqli->error;
            echo "✗ Failed to add index $index_name: " . $mysqli->error . "\n";
        }
    } else {
        $completed[] = "✓ Index $index_name already exists";
        echo "✓ Index $index_name already exists\n";
    }
}

// Verify the migration
$result = $mysqli->query("DESCRIBE calendar_events");
$columns = array();
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $columns[] = $row['Field'];
    }
}

$migration_result = array(
    'success' => count($errors) === 0,
    'message' => count($errors) === 0 ? 'Migration completed successfully' : 'Migration completed with errors',
    'completed' => $completed,
    'errors' => $errors,
    'calendar_columns' => $columns
);

echo "\n" . json_encode($migration_result, JSON_PRETTY_PRINT) . "\n";

$mysqli->close();
?>
