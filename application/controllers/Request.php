<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Request extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();

        $this->load->model('Notification_model', 'notifications');
        $this->load->helper(['url', 'date']);
    }

    /**
     * Render the notification center page for administrators.
     */
    public function index()
    {
        $this->guard();

        $settingsId = (string) $this->session->userdata('settingsID');
        $userId = (int) $this->session->userdata('user_id');
        $level = strtolower((string) $this->session->userdata('level'));

        if ($level === 'client') {
            redirect('Page/clientMyTickets');
            return;
        }

        $scopedUserId = ($level === 'admin') ? null : $userId;

        $data['notifications'] = $this->notifications->list_all($settingsId, 200, $scopedUserId);
        $data['notifications'] = $this->mergeSupportNotifications($data['notifications'], $settingsId, $userId, 200, $level === 'admin');

        $this->load->view('request_list', $data);
    }

    /**
     * Return the current count of unseen notifications for the bell widget.
     */
    public function ajax_pending_count()
    {
        if (!$this->isAuthorizedUser()) {
            return $this->respondJson(['count' => 0]);
        }

        $settingsId = (string) $this->session->userdata('settingsID');
        $userId = (int) $this->session->userdata('user_id');
        $level = strtolower((string) $this->session->userdata('level'));
        $scopedUserId = ($level === 'admin') ? null : $userId;

        $count = 0;
        if ($level !== 'client') {
            $count = $this->notifications->count_pending($settingsId, $scopedUserId);
        }
        $count += $this->countSupportPending($settingsId, $userId, $level === 'admin');

        return $this->respondJson(['count' => $count]);
    }

    /**
     * Provide a list of recent notifications for the bell widget.
     */
    public function ajax_pending_list()
    {
        if (!$this->isAuthorizedUser()) {
            return $this->respondJson(['data' => []]);
        }

        $settingsId = (string) $this->session->userdata('settingsID');
        $limit = (int) $this->input->get('limit', true);
        if ($limit <= 0 || $limit > 50) {
            $limit = 8;
        }

        $userId = (int) $this->session->userdata('user_id');
        $level = strtolower((string) $this->session->userdata('level'));
        $scopedUserId = ($level === 'admin') ? null : $userId;

        $rows = [];
        if ($level !== 'client') {
            $rows = $this->notifications->get_pending($settingsId, $limit, $scopedUserId);
        }
        $rows = $this->mergeSupportNotifications($rows, $settingsId, $userId, $limit, $level === 'admin', $level === 'client');
        $data = array_map(function ($row) {
            $firstName = isset($row->fName) ? $row->fName : '';
            $lastName  = isset($row->lName) ? $row->lName : '';
            $nameParts = array_filter([$firstName, $lastName]);
            $fullName = trim(implode(' ', $nameParts));

            return [
                'document_type' => isset($row->title) ? $row->title : 'Task update',
                'student'       => $fullName !== '' ? $fullName : 'Unknown',
                'request_date'  => isset($row->created_at) ? $row->created_at : '',
                'link'          => isset($row->link) ? $row->link : '',
                'message'       => isset($row->message) ? $row->message : '',
                'is_seen'       => isset($row->is_seen) ? (int) $row->is_seen : 0,
            ];
        }, $rows);

        return $this->respondJson(['data' => $data]);
    }

    /**
     * Mark notifications as seen when the dropdown is opened.
     */
    public function ajax_mark_seen()
    {
        if (!$this->isAuthorizedUser()) {
            return $this->respondJson(['status' => 'skipped']);
        }

        $settingsId = (string) $this->session->userdata('settingsID');
        $userId = (int) $this->session->userdata('user_id');
        $level = strtolower((string) $this->session->userdata('level'));
        $scopedUserId = ($level === 'admin') ? null : $userId;

        $this->notifications->mark_seen($settingsId, $scopedUserId);
        $this->markSupportSeen($settingsId, $userId, $level === 'admin');

        return $this->respondJson(['status' => 'ok']);
    }

    private function countSupportPending($settingsId, $userId, $isAdmin)
    {
        if (!$this->db->table_exists('support_notifications')) {
            return 0;
        }

        $this->db->from('support_notifications');
        $this->db->where('settingsID', (int) $settingsId);
        $this->db->where('is_read', 0);

        if (!$isAdmin) {
            $this->db->where('user_id', (int) $userId);
        }

        return (int) $this->db->count_all_results();
    }

    private function fetchSupportNotifications($settingsId, $userId, $limit, $isAdmin, $isClient = false)
    {
        if (!$this->db->table_exists('support_notifications')) {
            return [];
        }

        $this->db->select('n.*, u.fName, u.lName, si.ticket_number, actor.fName AS actor_fName, actor.lName AS actor_lName');
        $this->db->from('support_notifications n');
        $this->db->join('users u', 'u.user_id = n.user_id', 'left');
        $this->db->join('support_issues si', 'si.id = n.issue_id', 'left');
        $this->db->join('users actor', 'actor.user_id = n.actor_id', 'left');
        $this->db->where('n.settingsID', (int) $settingsId);

        if (!$isAdmin) {
            $this->db->where('n.user_id', (int) $userId);
        }

        $this->db->order_by('n.is_read', 'ASC');
        $this->db->order_by('n.created_at', 'DESC');
        if ($limit > 0) {
            $this->db->limit($limit);
        }

        $rows = $this->db->get()->result();
        return array_map(function ($row) use ($isClient) {
            $link = '';
            if (!empty($row->issue_id)) {
                $link = $isClient
                    ? site_url('Page/clientTicketView?id=' . (int) $row->issue_id)
                    : site_url('Page/supportIssueView?id=' . (int) $row->issue_id);
            }
            return (object) [
                'id' => 'support_' . (int) $row->id,
                'fName' => !empty($row->actor_fName) ? $row->actor_fName : ($row->fName ?? ''),
                'lName' => !empty($row->actor_lName) ? $row->actor_lName : ($row->lName ?? ''),
                'title' => $row->title ?? 'Support notification',
                'message' => $row->message ?? '',
                'link' => $link,
                'status' => ((int) ($row->is_read ?? 0) === 1) ? 'resolved' : 'pending',
                'is_seen' => (int) ($row->is_read ?? 0),
                'created_at' => $row->created_at ?? '',
                'source_type' => 'support',
                'issue_id' => $row->issue_id ?? null,
                'ticket_number' => $row->ticket_number ?? '',
                'user_id' => $row->user_id ?? null,
            ];
        }, $rows);
    }

    private function mergeSupportNotifications(array $notifications, $settingsId, $userId, $limit, $isAdmin, $isClient = false)
    {
        $supportRows = $this->fetchSupportNotifications($settingsId, $userId, $limit, $isAdmin, $isClient);
        $merged = array_merge($notifications, $supportRows);

        usort($merged, function ($left, $right) {
            $leftSeen = isset($left->is_seen) ? (int) $left->is_seen : 0;
            $rightSeen = isset($right->is_seen) ? (int) $right->is_seen : 0;
            if ($leftSeen !== $rightSeen) {
                return $leftSeen <=> $rightSeen;
            }

            $leftDate = strtotime((string) ($left->created_at ?? '1970-01-01 00:00:00'));
            $rightDate = strtotime((string) ($right->created_at ?? '1970-01-01 00:00:00'));
            return $rightDate <=> $leftDate;
        });

        if ($limit > 0) {
            return array_slice($merged, 0, $limit);
        }

        return $merged;
    }

    private function markSupportSeen($settingsId, $userId, $isAdmin)
    {
        if (!$this->db->table_exists('support_notifications')) {
            return;
        }

        $this->db->where('settingsID', (int) $settingsId);
        $this->db->where('is_read', 0);

        if (!$isAdmin) {
            $this->db->where('user_id', (int) $userId);
        }

        $this->db->update('support_notifications', [
            'is_read' => 1,
            'read_at' => date('Y-m-d H:i:s'),
        ]);
    }

    private function guard()
    {
        if (!$this->isAuthorizedUser()) {
            show_error('Access denied', 403);
            exit;
        }
    }

    private function isAuthorizedUser()
    {
        $userId = $this->session->userdata('user_id');
        $level = strtolower((string) $this->session->userdata('level'));
        return !empty($userId) && in_array($level, ['admin', 'staff', 'client'], true);
    }

    private function respondJson(array $payload)
    {
        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($payload));
    }
}
