<?php
class Reminders extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->model('RemindersModel');
        $this->load->model('Notification_model', 'notifications');
        $this->load->helper(['url', 'form']);
        $this->load->library('session');
        $this->load->database();

        if (!$this->session->userdata('logged_in')) {
            redirect('Login');
        }

        if (!$this->_is_feature_enabled('calendar')) {
            show_error('Scheduling is not included in your company package. Please contact your super admin.', 403, 'Feature Not Available');
        }
    }

    protected function _is_feature_enabled($featureKey)
    {
        if ($this->session->userdata('level') === 'Super Admin') {
            return true;
        }

        $settingsID = (int) $this->session->userdata('settingsID');
        if ($settingsID <= 0) {
            return false;
        }

        if (!$this->db->table_exists('company_features')) {
            return true;
        }

        $activeFeatureCount = (int) $this->db
            ->where('settingsID', $settingsID)
            ->where('is_enabled', 1)
            ->count_all_results('company_features');

        if ($activeFeatureCount === 0) {
            return true;
        }

        $featureCount = (int) $this->db
            ->where('settingsID', $settingsID)
            ->where('feature_key', $featureKey)
            ->where('is_enabled', 1)
            ->count_all_results('company_features');

        return $featureCount > 0;
    }

    public function index()
    {
        $settingsID = $this->session->userdata('settingsID');
        $user_id = $this->session->userdata('user_id');

        // Capture reminders due today; no auto-archiving on load.
        $data['dueToday'] = $this->RemindersModel->getDueToday($settingsID, $user_id);
        $data['dueNow'] = $this->RemindersModel->getDueNow($settingsID, $user_id);
        $data['autoArchived'] = [];

        // Fetch reminders filtered by settingsID and user_id
        $data['reminders'] = $this->RemindersModel->getAllReminders($settingsID, $user_id);

        $this->load->view('reminders_list', $data);
    }

    public function edit($id = null)
    {
        if (empty($id)) {
            redirect('Reminders');
            return;
        }

        $settingsID = $this->session->userdata('settingsID');
        $user_id = $this->session->userdata('user_id');

        $reminder = $this->RemindersModel->getReminderById($id);

        if (!$reminder || $reminder->settingsID !== $settingsID || (int) $reminder->user_id !== (int) $user_id) {
            show_error('Reminder not found or access denied.', 404);
            return;
        }

        $data['reminder'] = $reminder;
        $data['dueToday'] = $this->RemindersModel->getDueToday($settingsID, $user_id);

        $this->load->view('reminders_edit', $data);
    }

    public function history()
    {
        $settingsID = $this->session->userdata('settingsID');
        $user_id = $this->session->userdata('user_id');

        $data['history'] = $this->RemindersModel->getReminderHistory($settingsID, $user_id, 0);
        $data['dueToday'] = $this->RemindersModel->getDueToday($settingsID, $user_id);

        $this->load->view('reminders_history', $data);
    }

    public function add()
    {
        date_default_timezone_set('Asia/Manila');

        if ($this->input->post('save')) {
            $remind_date = $this->input->post('remind_at'); // e.g., 2025-07-15
            $remind_time = $this->input->post('remind_time');
            $timePart = $remind_time ? date('H:i:s', strtotime($remind_time)) : date('H:i:s');
            $remind_at = $remind_date . ' ' . $timePart; // e.g., 2025-07-15 14:30:00

            $notifyBefore = (int) $this->input->post('notify_before');
            if ($notifyBefore < 0) {
                $notifyBefore = 0;
            }

            $data = [
                'title' => $this->input->post('title'),
                'description' => $this->input->post('description'),
                'remind_at' => $remind_at,
                'recurrence' => $this->input->post('recurrence'),
                'settingsID' => $this->session->userdata('settingsID'),
                'user_id' => $this->session->userdata('user_id')
            ];

            $this->applyNotifyBeforeField($data, $notifyBefore);

            $this->RemindersModel->addReminder($data);
            $this->session->set_flashdata('success', 'Reminder added successfully.');
            redirect('Reminders');
        } else {
            redirect('Reminders');
        }
    }


    public function update($id)
    {
        date_default_timezone_set('Asia/Manila');

        if ($this->input->server('REQUEST_METHOD') === 'POST') {
            $remind_date = $this->input->post('remind_at'); // e.g., 2025-07-20
            $remind_time = $this->input->post('remind_time');
            $timePart = $remind_time ? date('H:i:s', strtotime($remind_time)) : date('H:i:s');
            $remind_at = $remind_date . ' ' . $timePart;

            $notifyBefore = (int) $this->input->post('notify_before');
            if ($notifyBefore < 0) {
                $notifyBefore = 0;
            }

            $updateData = [
                'title' => $this->input->post('title'),
                'description' => $this->input->post('description'),
                'recurrence' => $this->input->post('recurrence'),
                'remind_at' => $remind_at
            ];

            $this->applyNotifyBeforeField($updateData, $notifyBefore);

            $this->RemindersModel->updateReminder($id, $updateData);

            $this->session->set_flashdata('success', 'Reminder updated successfully.');
        }

        redirect('Reminders');
    }

    public function delete($id)
    {
        $this->RemindersModel->deleteReminder($id);

        $this->session->set_flashdata('success', 'Reminder deleted successfully.');
        redirect('Reminders');
    }

    /**
     * Lightweight JSON feed for due reminders (used for global alerts).
     */
    public function dueNowFeed()
    {
        if ($this->session->userdata('logged_in') !== TRUE) {
            show_error('Forbidden', 403);
            return;
        }

        $settingsID = $this->session->userdata('settingsID');
        $user_id    = $this->session->userdata('user_id');

        $due = $this->RemindersModel->getDueNow($settingsID, $user_id);

        $out = array_map(function ($item) {
            return [
                'id'         => (int) $item->id,
                'title'      => (string) $item->title,
                'description'=> (string) $item->description,
                'remind_at'  => (string) $item->remind_at,
            ];
        }, $due);

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'count' => count($out),
                'items' => $out,
            ]));
    }

    /**
     * Optionally attach notify_before_minutes to payload if the column exists.
     *
     * @param array $payload
     * @param int   $minutes
     * @return void
     */
    private function applyNotifyBeforeField(array &$payload, $minutes)
    {
        if ($this->db->field_exists('notify_before_minutes', 'reminders')) {
            $payload['notify_before_minutes'] = $minutes;
        }
    }
}
