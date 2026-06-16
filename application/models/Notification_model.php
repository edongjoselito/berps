<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Notification_model extends CI_Model
{
    /**
     * Storage table for admin notifications.
     */
    protected $table = 'admin_notifications';

    public function __construct()
    {
        parent::__construct();
        $this->ensureTable();
    }

    /**
     * Make sure the notifications table exists.
     */
    protected function ensureTable()
    {
        if ($this->db->table_exists($this->table)) {
            return;
        }

        $this->load->dbforge();

        $fields = [
            'id' => [
                'type'           => 'BIGINT',
                'constraint'     => 20,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'settings_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 30,
                'null'       => true,
            ],
            'task_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => true,
            ],
            'user_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => true,
            ],
            'title' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'message' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'link' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'status' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'default'    => 'pending',
            ],
            'is_seen' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
            ],
            'created_at' => [
                'type' => 'DATETIME',
            ],
            'seen_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ];

        $this->dbforge->add_field($fields);
        $this->dbforge->add_key('id', true);
        $this->dbforge->create_table($this->table, true);
    }

    /**
     * Create a notification when a task is accomplished.
     */
    public function create_accomplishment_notification(array $payload)
    {
        $taskId = isset($payload['task_id']) ? (int) $payload['task_id'] : 0;
        if ($taskId > 0) {
            $exists = $this->db
                ->select('id')
                ->from($this->table)
                ->where('task_id', $taskId)
                ->where('status', 'pending')
                ->limit(1)
                ->get()
                ->num_rows() > 0;

            if ($exists) {
                return null;
            }
        }

        $data = [
            'settings_id' => isset($payload['settings_id']) ? $payload['settings_id'] : null,
            'task_id'     => $taskId ?: null,
            'user_id'     => isset($payload['user_id']) ? $payload['user_id'] : null,
            'title'       => isset($payload['title']) ? $payload['title'] : null,
            'message'     => isset($payload['message']) ? $payload['message'] : null,
            'link'        => isset($payload['link']) ? $payload['link'] : null,
            'status'      => 'pending',
            'is_seen'     => 0,
            'created_at'  => isset($payload['created_at']) ? $payload['created_at'] : date('Y-m-d H:i:s'),
        ];

        $this->db->insert($this->table, $data);
        return (int) $this->db->insert_id();
    }

    /**
     * Create a notification for reminder events.
     *
     * @param array $payload
     * @return int
     */
    public function create_reminder_notification(array $payload)
    {
        $data = [
            'settings_id' => isset($payload['settings_id']) ? $payload['settings_id'] : null,
            'task_id'     => null,
            'user_id'     => isset($payload['user_id']) ? $payload['user_id'] : null,
            'title'       => isset($payload['title']) ? $payload['title'] : 'Reminder Due',
            'message'     => isset($payload['message']) ? $payload['message'] : null,
            'link'        => isset($payload['link']) ? $payload['link'] : null,
            'status'      => 'pending',
            'is_seen'     => 0,
            'created_at'  => isset($payload['created_at']) ? $payload['created_at'] : date('Y-m-d H:i:s'),
        ];

        $this->db->insert($this->table, $data);
        return (int) $this->db->insert_id();
    }

    /**
     * Resolve/close notifications related to a task.
     */
    public function resolve_task_notifications($taskId)
    {
        $this->db
            ->where('task_id', $taskId)
            ->where('status', 'pending')
            ->update($this->table, [
                'status'  => 'resolved',
                'is_seen' => 1,
                'seen_at' => date('Y-m-d H:i:s'),
            ]);
    }

    /**
     * Count outstanding, unseen notifications for a settings account.
     */
    public function count_pending($settingsId, $userId = null)
    {
        $this->db
            ->where('settings_id', $settingsId)
            ->where('status', 'pending')
            ->where('is_seen', 0);

        if ($userId !== null) {
            $this->db->where('user_id', $userId);
        }

        return (int) $this->db->count_all_results($this->table);
    }

    /**
     * Fetch the latest pending notifications.
     */
    public function get_pending($settingsId, $limit = 8, $userId = null)
    {
        $this->db->select('n.*, u.fName, u.lName');
        $this->db->from($this->table . ' n');
        $this->db->join('users u', 'u.user_id = n.user_id', 'left');
        $this->db->where('n.settings_id', $settingsId);
        $this->db->where('n.status', 'pending');
        if ($userId !== null) {
            $this->db->where('n.user_id', $userId);
        }
        $this->db->order_by('n.is_seen', 'ASC');
        $this->db->order_by('n.created_at', 'DESC');
        if ($limit > 0) {
            $this->db->limit($limit);
        }

        return $this->db->get()->result();
    }

    /**
     * Mark notifications as seen.
     */
    public function mark_seen($settingsId, $userId = null)
    {
        $this->db
            ->where('settings_id', $settingsId)
            ->where('status', 'pending')
            ->where('is_seen', 0);

        if ($userId !== null) {
            $this->db->where('user_id', $userId);
        }

        $this->db->update($this->table, [
            'is_seen' => 1,
            'seen_at' => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * List all notifications to present in the admin page.
     */
    public function list_all($settingsId, $limit = 100, $userId = null)
    {
        $this->db->select('n.*, u.fName, u.lName');
        $this->db->from($this->table . ' n');
        $this->db->join('users u', 'u.user_id = n.user_id', 'left');
        $this->db->where('n.settings_id', $settingsId);
        if ($userId !== null) {
            $this->db->where('n.user_id', $userId);
        }
        $this->db->order_by('n.created_at', 'DESC');
        if ($limit > 0) {
            $this->db->limit($limit);
        }

        return $this->db->get()->result();
    }
}
