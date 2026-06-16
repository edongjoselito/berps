<?php
// Database migration script for calendar_events table
// Place this in application/controllers/ temporarily to run the migration

defined('BASEPATH') OR exit('No direct script access allowed');

class Migrate_calendar_task_sync extends CI_Controller {

    public function __construct() {
        parent::__construct();
        // Restrict to admin only
        if (!$this->session->userdata('user_id')) {
            show_error('Not authenticated');
            return;
        }
    }

    public function index() {
        // Check admin status
        $is_admin = $this->db->where('user_id', $this->session->userdata('user_id'))
                             ->where('level', 'Admin')
                             ->get('users')
                             ->num_rows() > 0;
        
        if (!$is_admin) {
            show_error('Admin access required');
            return;
        }

        try {
            // Check each column and add if missing
            $migrations = array(
                array(
                    'column' => 'task_id',
                    'sql' => 'ALTER TABLE calendar_events ADD COLUMN task_id INT DEFAULT 0 AFTER status'
                ),
                array(
                    'column' => 'is_completed',
                    'sql' => 'ALTER TABLE calendar_events ADD COLUMN is_completed TINYINT(1) DEFAULT 1 COMMENT "0 = completed, 1 = not completed" AFTER task_id'
                ),
                array(
                    'column' => 'reminder_email_enabled',
                    'sql' => 'ALTER TABLE calendar_events ADD COLUMN reminder_email_enabled TINYINT(1) DEFAULT 0 AFTER is_completed'
                ),
                array(
                    'column' => 'reminder_email',
                    'sql' => 'ALTER TABLE calendar_events ADD COLUMN reminder_email VARCHAR(255) DEFAULT NULL AFTER reminder_email_enabled'
                ),
                array(
                    'column' => 'notes',
                    'sql' => 'ALTER TABLE calendar_events ADD COLUMN notes TEXT DEFAULT NULL AFTER reminder_email'
                )
            );

            $completed = array();
            $errors = array();

            foreach ($migrations as $migration) {
                if (!$this->db->field_exists($migration['column'], 'calendar_events')) {
                    try {
                        $this->db->query($migration['sql']);
                        $completed[] = $migration['column'];
                        error_log("Migration: Added column {$migration['column']} to calendar_events");
                    } catch (Exception $e) {
                        $errors[] = $migration['column'] . ': ' . $e->getMessage();
                        error_log("Migration error for {$migration['column']}: " . $e->getMessage());
                    }
                } else {
                    $completed[] = $migration['column'] . ' (already exists)';
                }
            }

            // Add indexes if they don't exist
            try {
                $this->db->query('ALTER TABLE calendar_events ADD INDEX idx_task_id (task_id)');
                $completed[] = 'Index idx_task_id';
            } catch (Exception $e) {
                // Index might already exist
                error_log("Index migration note: " . $e->getMessage());
            }

            try {
                $this->db->query('ALTER TABLE calendar_events ADD INDEX idx_is_completed (is_completed)');
                $completed[] = 'Index idx_is_completed';
            } catch (Exception $e) {
                // Index might already exist
                error_log("Index migration note: " . $e->getMessage());
            }

            $response = array(
                'success' => true,
                'message' => 'Migration completed',
                'completed' => $completed,
                'errors' => $errors
            );

            echo json_encode($response);

        } catch (Exception $e) {
            echo json_encode(array(
                'success' => false,
                'message' => 'Migration failed: ' . $e->getMessage()
            ));
        }
    }
}
?>
