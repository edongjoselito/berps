<?php
class RemindersModel extends CI_Model
{
    /**
     * Storage table for archived reminders.
     *
     * @var string
     */
    protected $historyTable = 'reminder_history';

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Make sure the reminder history table exists.
     *
     * The structure mirrors the live reminders table and keeps a timestamp
     * indicating when the reminder was archived.
     *
     * @return void
     */
    protected function ensureHistoryTable()
    {
        if ($this->db->table_exists($this->historyTable)) {
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
            'reminder_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => true,
            ],
            'settingsID' => [
                'type'       => 'VARCHAR',
                'constraint' => 30,
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
            'description' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'recurrence' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
            ],
            'remind_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'archived_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'notification_id' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'null'       => true,
            ],
        ];

        $this->dbforge->add_field($fields);
        $this->dbforge->add_key('id', true);
        $this->dbforge->add_key('reminder_id');
        $this->dbforge->create_table($this->historyTable, true);
    }

    public function getAllReminders($settingsID, $user_id)
    {
        $this->db->where('settingsID', $settingsID);
        $this->db->where('user_id', $user_id);
        $query = $this->db->get('reminders');
        return $query->result();
    }

    public function getReminderById($id)
    {
        return $this->db->get_where('reminders', ['id' => $id])->row();
    }

    public function addReminder($data)
    {
        return $this->db->insert('reminders', $data);
    }

    public function updateReminder($id, $data)
    {
        $this->db->where('id', $id);
        return $this->db->update('reminders', $data);
    }

    public function deleteReminder($id)
    {
        $this->db->where('id', $id);
        return $this->db->delete('reminders');
    }

    /**
     * Archive reminders that are due today or earlier.
     *
     * Returns the reminders that were archived so the caller can trigger
     * notifications or other side effects.
     *
     * @param string $settingsID
     * @param int    $user_id
     * @return array
     */
    public function archiveDueReminders($settingsID, $user_id)
    {
        date_default_timezone_set('Asia/Manila');

        $today = date('Y-m-d');

        $dueQuery = $this->db
            ->where('settingsID', $settingsID)
            ->where('user_id', $user_id)
            ->where('DATE(remind_at) <=', $today)
            ->get('reminders');

        $dueReminders = $dueQuery->result();

        if (empty($dueReminders)) {
            return [];
        }

        $this->ensureHistoryTable();

        foreach ($dueReminders as $reminder) {
            $historyPayload = [
                'reminder_id' => $reminder->id,
                'settingsID'  => $reminder->settingsID,
                'user_id'     => $reminder->user_id,
                'title'       => $reminder->title,
                'description' => $reminder->description,
                'recurrence'  => $reminder->recurrence,
                'remind_at'   => $reminder->remind_at,
                'archived_at' => date('Y-m-d H:i:s'),
            ];

            $this->db->insert($this->historyTable, $historyPayload);

            $reminder->history_id = (int) $this->db->insert_id();
            $reminder->was_rescheduled = false;
            $reminder->next_remind_at = null;

            if ($reminder->recurrence === 'monthly') {
                $nextDue = date('Y-m-d H:i:s', strtotime('+1 month', strtotime($reminder->remind_at)));
                $this->db
                    ->where('id', $reminder->id)
                    ->update('reminders', ['remind_at' => $nextDue]);
                $reminder->was_rescheduled = true;
                $reminder->next_remind_at = $nextDue;
            } elseif ($reminder->recurrence === 'yearly') {
                $nextDue = date('Y-m-d H:i:s', strtotime('+1 year', strtotime($reminder->remind_at)));
                $this->db
                    ->where('id', $reminder->id)
                    ->update('reminders', ['remind_at' => $nextDue]);
                $reminder->was_rescheduled = true;
                $reminder->next_remind_at = $nextDue;
            } else {
                $this->db->where('id', $reminder->id)->delete('reminders');
            }
        }

        return $dueReminders;
    }

    /**
     * Retrieve reminder history entries for the current account.
     *
     * @param string $settingsID
     * @param int    $user_id
     * @param int    $limit
     * @return array
     */
    public function getReminderHistory($settingsID, $user_id, $limit = 50)
    {
        $this->ensureHistoryTable();

        $this->db->where('settingsID', $settingsID);
        $this->db->where('user_id', $user_id);
        $this->db->order_by('archived_at', 'DESC');
        if ($limit > 0) {
            $this->db->limit($limit);
        }

        return $this->db->get($this->historyTable)->result();
    }

    /**
     * Fetch a history record by its primary key.
     *
     * @param int $id
     * @return object|null
     */
    public function getHistoryById($id)
    {
        $this->ensureHistoryTable();

        return $this->db->get_where($this->historyTable, ['id' => $id])->row();
    }

    /**
     * Attach a notification reference to a history record.
     *
     * @param int $historyId
     * @param int $notificationId
     * @return bool
     */
    public function attachNotificationToHistory($historyId, $notificationId)
    {
        if (!$historyId || !$notificationId) {
            return false;
        }

        $this->ensureHistoryTable();

        $this->db->where('id', $historyId);
        return (bool) $this->db->update($this->historyTable, [
            'notification_id' => $notificationId,
        ]);
    }

    public function getRemindersDueToday($settingsID, $user_id)
    {
        $today = date('Y-m-d');       // e.g., '2025-07-14'
        $day = date('d');             // e.g., '14'
        $monthDay = date('m-d');      // e.g., '07-14'

        $this->db->where('settingsID', $settingsID);
        $this->db->where('user_id', $user_id);

        $this->db->group_start();

        // One-time reminder (exact date)
        $this->db->group_start();
        $this->db->where('recurrence', 'once');
        $this->db->where('DATE(remind_at)', $today); // <-- Strip time
        $this->db->group_end();

        // Monthly recurring (day of month match)
        $this->db->or_group_start();
        $this->db->where('recurrence', 'monthly');
        $this->db->where('DAY(remind_at)', $day);
        $this->db->group_end();

        // Yearly recurring (month and day match)
        $this->db->or_group_start();
        $this->db->where('recurrence', 'yearly');
        $this->db->where("DATE_FORMAT(remind_at, '%m-%d') =", $monthDay);
        $this->db->group_end();

        $this->db->group_end();

        return $this->db->get('reminders')->result();
    }



    public function getDueToday($settingsID, $user_id)
    {
        date_default_timezone_set('Asia/Manila'); // Set Manila timezone

        $now = date('Y-m-d H:i:s');

        $this->db->where('settingsID', $settingsID);
        $this->db->where('user_id', $user_id);
        $this->db->where('DATE(remind_at)', date('Y-m-d'));
        // Exclude reminders already past their time today
        $this->db->where('remind_at >=', $now);
        $query = $this->db->get('reminders');
        return $query->result();
    }

    /**
     * Reminders that should alert now (respecting notify_before_minutes).
     *
     * A reminder is due now if its scheduled time is in the past, or if the
     * current time is within the notify_before window.
     */
    public function getDueNow($settingsID, $user_id)
    {
        date_default_timezone_set('Asia/Manila');
        $now = date('Y-m-d H:i:s');

        $this->db->where('settingsID', $settingsID);
        $this->db->where('user_id', $user_id);
        // If the notify_before_minutes column exists, include its window; otherwise just use remind_at.
        if ($this->db->field_exists('notify_before_minutes', 'reminders')) {
            $this->db->group_start();
            $this->db->where('remind_at <=', $now);
            $this->db->or_group_start();
            $this->db->where('notify_before_minutes IS NOT NULL');
            $this->db->where('DATE_SUB(remind_at, INTERVAL notify_before_minutes MINUTE) <=', $now);
            $this->db->group_end();
            $this->db->group_end();
        } else {
            $this->db->where('remind_at <=', $now);
        }

        return $this->db->get('reminders')->result();
    }
}
