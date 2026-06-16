-- Create calendar_events table for user-specific calendar events
-- Execute this in phpMyAdmin, MySQL Workbench, or command line

-- Drop table if it exists (for clean re-creation)
DROP TABLE IF EXISTS calendar_events;

-- Create calendar_events table
CREATE TABLE calendar_events (
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
    
    -- Indexes for better performance
    INDEX idx_user_id (user_id),
    INDEX idx_settingsID (settingsID),
    INDEX idx_start_date (start_date),
    INDEX idx_status (status),
    INDEX idx_user_settings (user_id, settingsID),
    INDEX idx_date_range (start_date, end_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Show table structure after creation
DESCRIBE calendar_events;

-- Success message
SELECT 'Calendar events table created successfully!' as message;
