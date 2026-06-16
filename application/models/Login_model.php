<?php
class Login_model extends CI_Model
{
    private $reset_table = 'password_resets';

    public function get_user_by_username($username)
    {
        return $this->db->get_where('users', ['username' => $username])->row();
    }

    public function get_client_by_identifier($identifier, $settingsID = null)
    {
        $identifier = trim((string) $identifier);
        if ($identifier === '') {
            return null;
        }

        $this->db->from('customers');
        $this->db->where('portal_enabled', 1);
        $this->db->where('ClientStat', 'Active');
        if ($settingsID !== null) {
            $this->db->where('settingsID', (int) $settingsID);
        }

        return $this->db
            ->group_start()
                ->where('client_email', $identifier)
                ->or_where('CustID', $identifier)
            ->group_end()
            ->limit(1)
            ->get()
            ->row();
    }

    public function update_client_last_login($custID, $settingsID = null)
    {
        if (!$custID) {
            return false;
        }

        $this->db->where('CustID', $custID);
        if ($settingsID !== null) {
            $this->db->where('settingsID', $settingsID);
        }

        return $this->db->update('customers', [
            'portal_last_login' => date('Y-m-d H:i:s'),
        ]);
    }

    public function get_client_portal_password($custID, $settingsID = null)
    {
        if (!$custID) {
            return null;
        }

        $this->db->select('portal_password');
        $this->db->from('customers');
        $this->db->where('CustID', $custID);
        if ($settingsID !== null) {
            $this->db->where('settingsID', $settingsID);
        }

        $row = $this->db->get()->row();
        return $row ? $row->portal_password : null;
    }

    public function update_client_password($custID, $newPasswordHash, $settingsID = null)
    {
        if (!$custID || !$newPasswordHash) {
            return false;
        }

        $this->db->where('CustID', $custID);
        if ($settingsID !== null) {
            $this->db->where('settingsID', $settingsID);
        }

        $updated = $this->db->update('customers', [
            'portal_password' => $newPasswordHash,
        ]);

        if ($updated) {
            $customer = $this->get_client_by_identifier($custID, $settingsID);
            if ($customer) {
                $this->sync_client_user_password_by_identifier($customer->client_email ?? $custID, $customer->settingsID ?? $settingsID, $newPasswordHash, $custID);
            }
        }

        return $updated;
    }

    public function find_user_for_reset($username)
    {
        $this->db->select('user_id, username, email, fName, lName');
        $this->db->from('users');
        $this->db->where('username', $username);
        return $this->db->get()->row();
    }

    public function find_staff_user_by_email($email)
    {
        $email = trim((string) $email);
        if ($email === '') {
            return null;
        }
        $this->db->select('user_id, username, email, fName, lName, position, acctStat, settingsID');
        $this->db->from('users');
        $this->db->where('LOWER(email)', strtolower($email));
        return $this->db->get()->row();
    }

    public function create_mobile_otp_reset($user, $otp, $ttlSeconds = 900, $targetEmail = null)
    {
        $this->ensure_reset_table();
        $this->purge_expired_tokens();
        $this->delete_resets_for_user($user->user_id);

        $selector  = bin2hex($this->random_bytes(8));
        $expiresAt = date('Y-m-d H:i:s', time() + $ttlSeconds);

        $this->db->insert($this->reset_table, [
            'user_id'      => $user->user_id,
            'email'        => $targetEmail ?: $user->email,
            'selector'     => $selector,
            'hashed_token' => password_hash($otp, PASSWORD_BCRYPT),
            'expires_at'   => $expiresAt,
            'created_at'   => date('Y-m-d H:i:s'),
        ]);

        return $expiresAt;
    }

    public function find_active_reset_for_user($user_id)
    {
        $this->ensure_reset_table();
        $this->purge_expired_tokens();

        return $this->db
            ->where('user_id', $user_id)
            ->where('expires_at >=', date('Y-m-d H:i:s'))
            ->order_by('id', 'DESC')
            ->limit(1)
            ->get($this->reset_table)
            ->row();
    }

    public function upgrade_reset_to_token($rowId, $resetToken, $ttlSeconds = 600)
    {
        $this->ensure_reset_table();
        return $this->db
            ->where('id', $rowId)
            ->update($this->reset_table, [
                'hashed_token' => password_hash($resetToken, PASSWORD_BCRYPT),
                'expires_at'   => date('Y-m-d H:i:s', time() + $ttlSeconds),
            ]);
    }

    public function send_otp_reset_email($user, $otp, $ttlSeconds, $targetEmail = null)
    {
        $this->load->helper('url');
        $this->load->config('email');
        $this->load->library('email');

        $body = $this->load->view('email/reset_password_otp', [
            'user'        => $user,
            'otp'         => $otp,
            'ttl_minutes' => max(1, (int) round($ttlSeconds / 60)),
        ], true);

        $fromAddress = $this->getFromAddress();

        $this->email->from($fromAddress, 'BERPS');
        $this->email->to($targetEmail ?: $user->email);
        $this->email->subject('Your BERPS password reset code');
        $this->email->message($body);

        return $this->email->send();
    }

    public function create_password_reset($user, $ttlSeconds = 3600, $targetEmail = null)
    {
        $this->ensure_reset_table();
        $this->purge_expired_tokens();

        $tokenPair = $this->generate_token_pair();
        $expiresAt = date('Y-m-d H:i:s', time() + $ttlSeconds);

        $this->delete_resets_for_user($user->user_id);

        $this->db->insert($this->reset_table, [
            'user_id'      => $user->user_id,
            'email'        => $targetEmail ?: $user->email,
            'selector'     => $tokenPair['selector'],
            'hashed_token' => password_hash($tokenPair['validator'], PASSWORD_BCRYPT),
            'expires_at'   => $expiresAt,
            'created_at'   => date('Y-m-d H:i:s'),
        ]);

        return [
            'selector'   => $tokenPair['selector'],
            'validator'  => $tokenPair['validator'],
            'expires_at' => $expiresAt,
        ];
    }

    public function find_reset_by_selector($selector)
    {
        if (!$selector) {
            return null;
        }
        $this->purge_expired_tokens();

        return $this->db
            ->where('selector', $selector)
            ->limit(1)
            ->get($this->reset_table)
            ->row();
    }

    public function is_valid_reset($tokenRow, $validator)
    {
        if (empty($tokenRow) || empty($validator)) {
            return false;
        }

        if (strtotime($tokenRow->expires_at) < time()) {
            $this->delete_resets_for_user($tokenRow->user_id);
            return false;
        }

        return password_verify($validator, $tokenRow->hashed_token);
    }

    public function delete_resets_for_user($user_id)
    {
        $this->ensure_reset_table();
        return $this->db->where('user_id', $user_id)->delete($this->reset_table);
    }

    public function update_user_password($user_id, $newPassword)
    {
        $hash = password_hash($newPassword, PASSWORD_BCRYPT);
        $user = $this->db->get_where('users', ['user_id' => $user_id])->row();
        $updated = $this->db->where('user_id', $user_id)->update('users', ['password' => $hash]);

        if ($updated && $user && strtolower((string) ($user->position ?? '')) === 'client') {
            $this->sync_client_password_by_identifier($user->username ?? '', $user->settingsID ?? null, $hash);
        }

        return $updated;
    }

    public function sync_client_password_by_identifier($identifier, $settingsID, $newPasswordHash)
    {
        $identifier = trim((string) $identifier);
        $settingsID = (int) $settingsID;

        if ($identifier === '' || $settingsID <= 0 || !$newPasswordHash) {
            return false;
        }

        return $this->db
            ->where('settingsID', $settingsID)
            ->group_start()
                ->where('client_email', $identifier)
                ->or_where('CustID', $identifier)
            ->group_end()
            ->update('customers', [
                'portal_password' => $newPasswordHash,
            ]);
    }

    public function sync_client_user_password_by_identifier($identifier, $settingsID, $newPasswordHash, $fallbackCustID = '')
    {
        $identifier = trim((string) $identifier);
        $fallbackCustID = trim((string) $fallbackCustID);
        $settingsID = (int) $settingsID;

        if ($settingsID <= 0 || !$newPasswordHash) {
            return false;
        }

        $identifiers = array_values(array_unique(array_filter(array($identifier, $fallbackCustID), function ($value) {
            return trim((string) $value) !== '';
        })));

        if (empty($identifiers)) {
            return false;
        }

        return $this->db
            ->where('settingsID', $settingsID)
            ->where('position', 'Client')
            ->group_start()
                ->where_in('username', $identifiers)
                ->or_where_in('email', $identifiers)
            ->group_end()
            ->update('users', [
                'password' => $newPasswordHash,
                'acctStat' => 'active',
            ]);
    }

    public function send_reset_email($user, $tokenData, $targetEmail = null)
    {
        $this->load->helper('url');
        $this->load->config('email');
        $this->load->library('email');

        $resetLink = site_url('login/reset/' . $tokenData['selector'] . '/' . $tokenData['validator']);
        $body = $this->load->view('email/reset_password', [
            'user'       => $user,
            'reset_link' => $resetLink,
            'expires_at' => $tokenData['expires_at'],
        ], true);

        $fromAddress = $this->getFromAddress();

        $this->email->from($fromAddress, 'BERPS');
        $this->email->to($targetEmail ?: $user->email);
        $this->email->subject('Reset your BERPS password');
        $this->email->message($body);

        return $this->email->send();
    }

    private function ensure_reset_table()
    {
        if ($this->db->table_exists($this->reset_table)) {
            return;
        }

        $this->db->query("
            CREATE TABLE IF NOT EXISTS `{$this->reset_table}` (
                `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
                `user_id` INT NOT NULL,
                `email` VARCHAR(191) NOT NULL,
                `selector` VARCHAR(32) NOT NULL,
                `hashed_token` VARCHAR(255) NOT NULL,
                `expires_at` DATETIME NOT NULL,
                `created_at` DATETIME NOT NULL,
                PRIMARY KEY (`id`),
                UNIQUE KEY `selector` (`selector`),
                KEY `expires_at` (`expires_at`),
                KEY `user_id` (`user_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ");
    }

    private function purge_expired_tokens()
    {
        $this->ensure_reset_table();
        $this->db->where('expires_at <', date('Y-m-d H:i:s'))->delete($this->reset_table);
    }

    private function generate_token_pair()
    {
        $selector = bin2hex($this->random_bytes(8));
        $validator = bin2hex($this->random_bytes(32));

        return ['selector' => $selector, 'validator' => $validator];
    }

    private function random_bytes($length)
    {
        if (function_exists('random_bytes')) {
            return random_bytes($length);
        }

        return openssl_random_pseudo_bytes($length);
    }

    private function getFromAddress()
    {
        $smtpUser = $this->config->item('smtp_user');
        if (!empty($smtpUser)) {
            return $smtpUser;
        }
        return 'no-reply@' . parse_url(base_url(), PHP_URL_HOST);
    }

    public function forgotPassword($username)
    {
        $user = $this->find_user_for_reset($username);
        return $user ? ['email' => $user->email] : null;
    }

    public function sur_d1($d1_answer)
    {
        $query = "insert into sur_d1 values('0',$d1_answer)";
        $this->db->query($query);
    }
}
