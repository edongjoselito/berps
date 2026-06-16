<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Create_Calendar_Events_Table extends CI_Migration {

    public function up() {
        // Check if table exists
        $table_exists = $this->db->table_exists('calendar_events');
        
        if (!$table_exists) {
            // Create calendar_events table
            $this->db->query("
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
                    INDEX idx_user_id (user_id),
                    INDEX idx_settingsID (settingsID),
                    INDEX idx_start_date (start_date),
                    INDEX idx_status (status)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ");
            
            echo "Table 'calendar_events' created successfully.\n";
        } else {
            echo "Table 'calendar_events' already exists.\n";
        }
    }

    public function down() {
        // Drop calendar_events table
        $this->db->query("DROP TABLE IF EXISTS calendar_events");
        
        echo "Table 'calendar_events' dropped.\n";
    }
}
