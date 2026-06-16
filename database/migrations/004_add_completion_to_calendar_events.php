<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Add_Completion_To_Calendar_Events extends CI_Migration {

    public function up() {
        // Check if column exists
        $column_exists = FALSE;
        $query = $this->db->query("SHOW COLUMNS FROM calendar_events LIKE 'is_completed'");
        if ($query->num_rows() > 0) {
            $column_exists = TRUE;
        }
        
        if (!$column_exists) {
            // Add is_completed column
            $this->db->query("ALTER TABLE calendar_events ADD COLUMN is_completed BOOLEAN DEFAULT FALSE AFTER is_public");
            
            echo "Column 'is_completed' added to 'calendar_events' table successfully.\n";
        } else {
            echo "Column 'is_completed' already exists in 'calendar_events' table.\n";
        }
    }

    public function down() {
        // Remove is_completed column
        $this->db->query("ALTER TABLE calendar_events DROP COLUMN IF EXISTS is_completed");
        
        echo "Column 'is_completed' removed from 'calendar_events' table.\n";
    }
}
