-- Create calendar_events table if it doesn't exist
CREATE TABLE IF NOT EXISTS calendar_events (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
