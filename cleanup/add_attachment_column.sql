-- Add attachment_path column to payments table for BIR Form 2307 files
-- Run this in phpMyAdmin or MySQL command line

-- Check if column exists before adding
SET @column_exists = (
    SELECT COUNT(*) 
    FROM information_schema.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'payments' 
    AND COLUMN_NAME = 'attachment_path'
);

-- Add column if it doesn't exist
SET @sql = IF(@column_exists = 0, 
    'ALTER TABLE payments ADD COLUMN attachment_path VARCHAR(255) NULL AFTER settingsID',
    'SELECT "Column already exists" as message'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Show table structure after modification
DESCRIBE payments;
