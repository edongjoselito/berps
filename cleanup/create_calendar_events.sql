-- Create calendar_events table for user-specific calendar events
-- Run this in phpMyAdmin or MySQL command line

-- Check if table exists before creating
SET @table_exists = (
    SELECT COUNT(*) 
    FROM information_schema.TABLES 
    WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'calendar_events'
);

-- Create table if it doesn't exist
SET @sql = IF(@table_exists = 0, 
    'CREATE TABLE calendar_events (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        description TEXT,
        start_date DATETIME NOT NULL,
        end_date DATETIME NOT NULL,
        all_day BOOLEAN DEFAULT FALSE,
        event_type VARCHAR(50) DEFAULT ''default'',
        color VARCHAR(7) DEFAULT ''#3788d8'',
        user_id INT NOT NULL,
        settingsID INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        status ENUM(''active'', ''cancelled'') DEFAULT ''active'',
        reminder_time INT DEFAULT 15,
        location VARCHAR(255),
        is_public BOOLEAN DEFAULT FALSE,
        INDEX idx_user_id (user_id),
        INDEX idx_settingsID (settingsID),
        INDEX idx_start_date (start_date),
        INDEX idx_status (status)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;',
    'SELECT "Table already exists" as message'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Show table structure after creation
DESCRIBE calendar_events;
