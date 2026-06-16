<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * BERPS Mobile authentication endpoints.
 *
 * Issues an HMAC-signed bearer token so the mobile client can authenticate
 * subsequent requests without relying on PHP sessions or cookies. Token logic
 * lives in mobile_auth_helper so other API controllers can verify without
 * re-instantiating this one (which would re-trigger the autoload).
 */
class MobileAuth extends CI_Controller
{
    private const ALLOWED_LEVELS = ['Staff', 'Admin'];

    public function __construct()
    {
        parent::__construct();
        $this->load->helper('mobile_auth');
        mobile_register_error_handlers();
        $this->load->model('Login_model');
        mobile_send_cors();
    }

    public function config()
    {
        if ($this->_method() === 'OPTIONS') {
            return mobile_json(['ok' => true]);
        }
        if ($this->_method() !== 'GET') {
            return mobile_json(['ok' => false, 'message' => 'Method not allowed.'], 405);
        }

        $baseUrl = rtrim(base_url(), '/');
        return mobile_json([
            'ok'            => true,
            'app_name'      => 'BERPS',
            'tagline'       => 'Tasks · Invoices · Job Orders',
            'description'   => 'Business Resource Planning System built for task management, '
                             . 'invoicing, and job order processing so every team stays '
                             . 'aligned from request to delivery.',
            'allowed_roles' => self::ALLOWED_LEVELS,
            'base_url'      => $baseUrl,
            'api_base_url'  => $baseUrl . '/api/mobile',
            'logo_url'      => $baseUrl . '/assets/images/logo-sm1.png',
            'wordmark_url'  => $baseUrl . '/assets/images/logo-dark.png',
            'theme' => [
                'primary'      => '#1B5ED6',
                'primary_dark' => '#114CB3',
                'background'   => '#E8F1FB',
                'text'         => '#0B1D3D',
            ],
        ]);
    }

    public function login()
    {
        if ($this->_method() === 'OPTIONS') {
            return mobile_json(['ok' => true]);
        }
        if ($this->_method() !== 'POST') {
            return mobile_json(['ok' => false, 'message' => 'Method not allowed.'], 405);
        }

        $payload  = $this->_read_payload();
        $username = trim((string)($payload['username'] ?? ''));
        $password = (string)($payload['password'] ?? '');

        if ($username === '' || $password === '') {
            return mobile_json([
                'ok'      => false,
                'message' => 'Username and password are required.',
            ], 422);
        }

        $user = $this->Login_model->get_user_by_username($username);
        if (!$user || !password_verify($password, $user->password)) {
            return mobile_json([
                'ok'      => false,
                'message' => 'The username or password is incorrect.',
            ], 401);
        }

        $level = trim((string)($user->position ?? ''));
        if (!in_array($level, self::ALLOWED_LEVELS, true)) {
            return mobile_json([
                'ok'      => false,
                'message' => 'This account is not allowed to use the mobile app yet.',
            ], 403);
        }

        if (strtolower(trim((string)($user->acctStat ?? 'active'))) !== 'active') {
            return mobile_json([
                'ok'      => false,
                'message' => 'Your account is not active. Please contact an administrator.',
            ], 403);
        }

        $token = mobile_build_token([
            'user_id'    => (int) $user->user_id,
            'username'   => (string) $user->username,
            'level'      => $level,
            'settingsID' => (int) ($user->settingsID ?? 0),
        ]);

        return mobile_json([
            'ok'      => true,
            'message' => 'Login successful.',
            'token'   => $token,
            'user'    => $this->_user_payload($user),
        ]);
    }

    public function me()
    {
        if ($this->_method() === 'OPTIONS') {
            return mobile_json(['ok' => true]);
        }
        if ($this->_method() !== 'GET') {
            return mobile_json(['ok' => false, 'message' => 'Method not allowed.'], 405);
        }

        $claims = mobile_require_claims();
        if ($claims === null) return;

        $user = $this->Login_model->get_user_by_username((string)($claims['username'] ?? ''));
        if (!$user) {
            return mobile_json(['ok' => false, 'message' => 'User not found.'], 404);
        }

        return mobile_json([
            'ok'   => true,
            'user' => $this->_user_payload($user),
        ]);
    }

    public function logout()
    {
        // Stateless tokens — the client just discards.
        return mobile_json(['ok' => true, 'message' => 'Logged out.']);
    }

    // ── Password reset (OTP flow) ───────────────────────────────────────────

    public function forgotPassword()
    {
        if ($this->_method() === 'OPTIONS') {
            return mobile_json(['ok' => true]);
        }
        if ($this->_method() !== 'POST') {
            return mobile_json(['ok' => false, 'message' => 'Method not allowed.'], 405);
        }

        $payload = $this->_read_payload();
        $email   = strtolower(trim((string)($payload['email'] ?? '')));

        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return mobile_json([
                'ok'      => false,
                'message' => 'Please enter a valid email address.',
            ], 422);
        }

        // Don't leak whether the email exists — always respond with the same
        // generic message after attempting the send.
        $user = $this->Login_model->find_staff_user_by_email($email);
        if ($user && in_array(trim((string)($user->position ?? '')), self::ALLOWED_LEVELS, true)) {
            $otp = $this->_generate_otp(6);
            $this->Login_model->create_mobile_otp_reset($user, $otp, 900, $email);
            $this->Login_model->send_otp_reset_email($user, $otp, 900, $email);
        }

        return mobile_json([
            'ok'      => true,
            'message' => 'If an account matches that email, we\'ve sent a 6-digit code.',
            'ttl'     => 900,
        ]);
    }

    public function verifyOtp()
    {
        if ($this->_method() === 'OPTIONS') {
            return mobile_json(['ok' => true]);
        }
        if ($this->_method() !== 'POST') {
            return mobile_json(['ok' => false, 'message' => 'Method not allowed.'], 405);
        }

        $payload = $this->_read_payload();
        $email   = strtolower(trim((string)($payload['email'] ?? '')));
        $otp     = preg_replace('/\D/', '', (string)($payload['otp'] ?? ''));

        if ($email === '' || strlen($otp) < 4) {
            return mobile_json([
                'ok'      => false,
                'message' => 'Email and the verification code are required.',
            ], 422);
        }

        $user = $this->Login_model->find_staff_user_by_email($email);
        if (!$user) {
            return mobile_json([
                'ok'      => false,
                'message' => 'That code is invalid or has expired.',
            ], 400);
        }

        $row = $this->Login_model->find_active_reset_for_user($user->user_id);
        if (!$row || !password_verify($otp, $row->hashed_token)) {
            return mobile_json([
                'ok'      => false,
                'message' => 'That code is invalid or has expired.',
            ], 400);
        }

        $resetToken = bin2hex(random_bytes(32));
        $this->Login_model->upgrade_reset_to_token($row->id, $resetToken, 600);

        return mobile_json([
            'ok'          => true,
            'message'     => 'Code verified.',
            'reset_token' => $resetToken,
            'ttl'         => 600,
        ]);
    }

    public function resetPassword()
    {
        if ($this->_method() === 'OPTIONS') {
            return mobile_json(['ok' => true]);
        }
        if ($this->_method() !== 'POST') {
            return mobile_json(['ok' => false, 'message' => 'Method not allowed.'], 405);
        }

        $payload     = $this->_read_payload();
        $email       = strtolower(trim((string)($payload['email'] ?? '')));
        $resetToken  = trim((string)($payload['reset_token'] ?? ''));
        $newPassword = (string)($payload['new_password'] ?? '');

        if ($email === '' || $resetToken === '') {
            return mobile_json([
                'ok'      => false,
                'message' => 'Missing reset details. Please start over.',
            ], 422);
        }
        if (strlen($newPassword) < 8) {
            return mobile_json([
                'ok'      => false,
                'message' => 'Password must be at least 8 characters.',
            ], 422);
        }

        $user = $this->Login_model->find_staff_user_by_email($email);
        if (!$user) {
            return mobile_json([
                'ok'      => false,
                'message' => 'Reset session is invalid. Please start over.',
            ], 400);
        }

        $row = $this->Login_model->find_active_reset_for_user($user->user_id);
        if (!$row || !password_verify($resetToken, $row->hashed_token)) {
            return mobile_json([
                'ok'      => false,
                'message' => 'Reset session is invalid or has expired. Please start over.',
            ], 400);
        }

        $this->Login_model->update_user_password($user->user_id, $newPassword);
        $this->Login_model->delete_resets_for_user($user->user_id);

        return mobile_json([
            'ok'      => true,
            'message' => 'Password updated. You can now sign in.',
        ]);
    }

    // ── Internals ────────────────────────────────────────────────────────────

    private function _generate_otp($length = 6)
    {
        $max = (int) str_repeat('9', $length);
        $min = (int) (10 ** ($length - 1));
        return (string) random_int($min, $max);
    }


    private function _user_payload($user)
    {
        $first = trim((string)($user->fName ?? ''));
        $last  = trim((string)($user->lName ?? ''));
        $mid   = trim((string)($user->mName ?? ''));
        $full  = trim($first . ' ' . ($mid !== '' ? $mid[0] . '. ' : '') . $last);

        return [
            'user_id'     => (int) $user->user_id,
            'username'    => (string) $user->username,
            'full_name'   => $full !== '' ? $full : (string) $user->username,
            'first_name'  => $first,
            'middle_name' => $mid,
            'last_name'   => $last,
            'email'       => (string) ($user->email ?? ''),
            'avatar'      => (string) ($user->avatar ?? ''),
            'avatar_url'  => $this->_avatar_url((string) ($user->avatar ?? '')),
            'position'    => (string) ($user->position ?? ''),
            'acct_stat'   => (string) ($user->acctStat ?? ''),
            'settings_id' => (int) ($user->settingsID ?? 0),
        ];
    }

    private function _avatar_url($avatar)
    {
        return mobile_avatar_url($avatar);
    }

    private function _read_payload()
    {
        $raw = file_get_contents('php://input');
        if (is_string($raw) && $raw !== '') {
            $decoded = json_decode($raw, true);
            if (is_array($decoded)) return $decoded;
        }
        return $this->input->post(null, true) ?: [];
    }

    private function _method()
    {
        return strtoupper((string) $this->input->method(true));
    }
}
