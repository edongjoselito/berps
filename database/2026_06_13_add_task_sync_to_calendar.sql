-- Add missing columns to calendar_events table for task synchronization

-- Add task_id column if it doesn't exist
ALTER TABLE calendar_events ADD COLUMN task_id INT DEFAULT 0 AFTER status;

-- Add is_completed column if it doesn't exist
ALTER TABLE calendar_events ADD COLUMN is_completed TINYINT(1) DEFAULT 1 COMMENT '0 = completed, 1 = not completed' AFTER task_id;

-- Add reminder_email_enabled column if it doesn't exist
ALTER TABLE calendar_events ADD COLUMN reminder_email_enabled TINYINT(1) DEFAULT 0 AFTER is_completed;

-- Add reminder_email column if it doesn't exist
ALTER TABLE calendar_events ADD COLUMN reminder_email VARCHAR(255) DEFAULT NULL AFTER reminder_email_enabled;

-- Add notes column if it doesn't exist
ALTER TABLE calendar_events ADD COLUMN notes TEXT DEFAULT NULL AFTER reminder_email;

-- Add index on task_id for faster lookups
ALTER TABLE calendar_events ADD INDEX idx_task_id (task_id);

-- Add index on is_completed for filtering completed/pending events
ALTER TABLE calendar_events ADD INDEX idx_is_completed (is_completed);

-- Add check constraint to ensure valid values
ALTER TABLE calendar_events ADD CONSTRAINT chk_is_completed CHECK (is_completed IN (0, 1));
