<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Add_Attachment_Path_To_Payments extends CI_Migration {

    public function up() {
        // Check if column exists
        $column_exists = $this->db->field_exists('attachment_path', 'payments');
        
        if (!$column_exists) {
            // Add attachment_path column to payments table
            $this->db->query("ALTER TABLE payments ADD COLUMN attachment_path VARCHAR(255) NULL AFTER settingsID");
            
            echo "Column 'attachment_path' added to 'payments' table successfully.\n";
        } else {
            echo "Column 'attachment_path' already exists in 'payments' table.\n";
        }
    }

    public function down() {
        // Remove attachment_path column from payments table
        $this->db->query("ALTER TABLE payments DROP COLUMN IF EXISTS attachment_path");
        
        echo "Column 'attachment_path' removed from 'payments' table.\n";
    }
}
