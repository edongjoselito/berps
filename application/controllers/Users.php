<?php
class Users extends CI_Controller
{
    private $companyFeatureAccessLoaded = false;
    private $companyFeatureRestrictionsActive = false;
    private $enabledCompanyFeatures = [];

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->model('User_model');
        $this->load->model('Login_model');
        $this->load->helper(['form', 'url']);
        $this->load->library('session');
        $this->load->library('form_validation');
        $this->load->library('user_agent');

        if ($this->session->userdata('logged_in') !== TRUE) {
            redirect('login');
        }

        $method = strtolower((string) $this->router->fetch_method());
        $passwordMethods = ['changepassword', 'update_password'];

        if (!in_array($method, $passwordMethods, true) && !$this->_can_manage_users()) {
            $this->_deny_users_access();
        }
    }

    public function index()
    {
        $settingsID = $this->session->userdata('settingsID');
        $level = strtolower((string)$this->session->userdata('level'));

        if ($level === 'pos admin') {
            // Legacy POS Admin sees only POS operations roles.
            $data['users'] = $this->User_model->get_by_settings_and_positions($settingsID, ['Manager', 'Cashier', 'POS Admin', 'POS Staff']);
        } else {
            // Admin and others see all
            $data['users'] = $this->User_model->get_by_settings($settingsID);
        }

        $data['canManagePosRoles'] = $this->_can_manage_pos_roles();
        $data['users_notice'] = $this->session->userdata('users_notice');
        $data['users_notice_type'] = $this->session->userdata('users_notice_type');
        $data['reset_password_display'] = $this->session->userdata('reset_password_display');
        // clear after reading
        $this->session->unset_userdata(['users_notice', 'users_notice_type', 'reset_password_display']);
        $this->load->view('users_list', $data);
    }

    public function create()
    {
        if ($this->input->post()) {
            $position = $this->input->post('position');
            $currentLevel = strtolower((string) $this->session->userdata('level'));

            // Legacy POS Admin can create POS operations users only.
            if ($currentLevel === 'pos admin') {
                $allowedPositions = ['Manager', 'Cashier'];
                if (!in_array($position, $allowedPositions, true)) {
                    $this->session->set_flashdata('users_error', 'POS Admin can only create Manager and Cashier POS accounts.');
                    redirect('users');
                    return;
                }
            } elseif ($currentLevel === 'admin') {
                $allowedPositions = ['Manager', 'Encoder', 'Staff', 'Cashier'];
                if (!in_array($position, $allowedPositions, true)) {
                    $this->session->set_flashdata('users_error', 'Admin can create Manager, Encoder, Staff, and Cashier accounts.');
                    redirect('users');
                    return;
                }
            }

            if ($this->_is_pos_role($position) && !$this->_can_manage_pos_roles()) {
                $this->session->set_flashdata('users_error', 'POS roles are available only when Package 4: POS is enabled for this company.');
                redirect('users');
                return;
            }

            $data = [
                'username' => $this->input->post('email'),
                'password'   => password_hash($this->input->post('password'), PASSWORD_DEFAULT),
                'position'   => $position,
                'fName'      => $this->input->post('fName'),
                'mName'      => $this->input->post('mName'),
                'lName'      => $this->input->post('lName'),
                'email'      => $this->input->post('email'),
                'avatar'     => $this->input->post('avatar'),
                'acctStat'   => $this->input->post('acctStat'),
                'settingsID' => $this->session->userdata('settingsID')
            ];
            $this->User_model->insert($data);
            redirect('users');
        }
        $this->load->view('users/create');
    }

    public function edit($id)
    {
        $data['user'] = $this->User_model->get_by_id($id);
        if ($this->input->post()) {
            $position = $this->input->post('position');
            $currentLevel = strtolower((string) $this->session->userdata('level'));
            $currentUser = $data['user'];
            $existingPosition = isset($currentUser->position) ? (string) $currentUser->position : '';

            // Legacy POS Admin can assign POS operations roles only. Existing legacy roles can be preserved.
            if ($currentLevel === 'pos admin') {
                $allowedPositions = ['Manager', 'Cashier'];
                if (in_array($existingPosition, ['POS Admin', 'POS Staff'], true)) {
                    $allowedPositions[] = $existingPosition;
                }
                if (!in_array($position, $allowedPositions, true)) {
                    $this->session->set_flashdata('users_error', 'POS Admin can only assign Manager and Cashier POS positions.');
                    redirect('users');
                    return;
                }
            } elseif ($currentLevel === 'admin') {
                $allowedPositions = ['Manager', 'Encoder', 'Staff', 'Cashier'];
                if ($existingPosition === 'Admin') {
                    $allowedPositions[] = 'Admin';
                }

                if (!in_array($position, $allowedPositions, true)) {
                    $this->session->set_flashdata('users_error', 'Admin can assign Manager, Encoder, Staff, and Cashier positions.');
                    redirect('users');
                    return;
                }
            }

            if (
                $this->_is_pos_role($position)
                && !$this->_can_manage_pos_roles()
                && !$this->_is_pos_role($existingPosition)
            ) {
                $this->session->set_flashdata('users_error', 'POS roles are available only when Package 4: POS is enabled for this company.');
                redirect('users');
                return;
            }

            $updateData = [
                'username'   => $this->input->post('username'),
                'position'   => $position,
                'fName'      => $this->input->post('fName'),
                'mName'      => $this->input->post('mName'),
                'lName'      => $this->input->post('lName'),
                'email'      => $this->input->post('email')
            ];
            $this->User_model->update($id, $updateData);
            redirect('users');
        }
        $this->load->view('users/edit', $data);
    }

    public function delete($id)
    {
        $this->User_model->delete($id);
        redirect('users');
    }

    function changepassword()
    {
        $this->load->view('change_pass');
    }

    public function update_password()
    {
        $this->form_validation->set_rules('currentpassword', 'Current Password', 'required|trim|callback__validate_currentpassword');
        $this->form_validation->set_rules('newpassword', 'New Password', 'required|trim|min_length[8]|regex_match[/^[a-zA-Z0-9!@#\$%\^&\*]+$/]');
        $this->form_validation->set_rules('cnewpassword', 'Confirm New Password', 'required|trim|matches[newpassword]');

        if ($this->form_validation->run()) {
            $newpass = password_hash($this->input->post('newpassword'), PASSWORD_DEFAULT);
            $level = strtolower((string) $this->session->userdata('level'));

            if ($level === 'client') {
                $custID = trim((string) $this->session->userdata('client_cust_id'));
                $settingsID = (int) $this->session->userdata('settingsID');
                $updated = $this->Login_model->update_client_password($custID, $newpass, $settingsID);
            } else {
                $username = $this->session->userdata('username');
                $updated = $this->User_model->reset_userpassword($username, $newpass);
            }

            if ($updated) {
                $this->session->set_flashdata('msg', '<div class="alert alert-success text-center">Password changed successfully.</div>');
                redirect($this->agent->referrer()); // ✅ redirect back to the previous page
            } else {
                show_error('Error updating password.');
            }
        } else {
            $this->changepassword(); // reload form with errors
        }
    }

    public function reset_password($id)
    {
        $user = $this->User_model->get_by_id($id);

        if (!$user) {
            $this->session->set_flashdata('users_error', 'User not found.');
            return redirect('users');
        }

        $username = isset($user->username) ? (string) $user->username : '';
        $displayName = trim((isset($user->fName) ? (string) $user->fName : '') . ' ' . (isset($user->lName) ? (string) $user->lName : ''));
        $email = isset($user->email) ? trim((string) $user->email) : '';

        if ($email === '') {
            $this->_set_notice('No email on file for this user.', 'error');
            return redirect('users');
        }

        // Load SMTP config and validate required pieces before touching the password
        $this->config->load('email');
        $host = trim((string) $this->config->item('smtp_host'));
        $smtpUser = trim((string) $this->config->item('smtp_user'));
        $smtpPass = (string) $this->config->item('smtp_pass');

        if ($host === '' || $smtpUser === '' || $smtpPass === '') {
            $this->_set_notice('SMTP is not configured. Please set smtp_host, smtp_user, and smtp_pass.', 'error');
            return redirect('users');
        }

        $temporaryPassword = $this->generate_temporary_password();
        $newPasswordHash = password_hash($temporaryPassword, PASSWORD_DEFAULT);
        $oldHash = isset($user->password) ? (string) $user->password : null;

        if (!$this->User_model->update($id, ['password' => $newPasswordHash])) {
            $this->_set_notice('Could not update password.', 'error');
            return redirect('users');
        }

        if (strtolower((string) ($user->position ?? '')) === 'client') {
            $this->Login_model->sync_client_password_by_identifier($username, $user->settingsID ?? null, $newPasswordHash);
        }

        $this->load->library('email');
        $fromEmail = $smtpUser;

        // Ensure config is applied even if defaults are not autoloaded
        $emailConfig = [
            'protocol'     => $this->config->item('protocol') ?: 'smtp',
            'smtp_host'    => $host,
            'smtp_user'    => $smtpUser,
            'smtp_pass'    => $smtpPass,
            'smtp_port'    => $this->config->item('smtp_port') ?: 587,
            'smtp_crypto'  => $this->config->item('smtp_crypto') ?: 'tls',
            'smtp_timeout' => $this->config->item('smtp_timeout') ?: 10,
            'mailtype'     => 'html',
            'charset'      => 'utf-8',
            'newline'      => "\r\n",
            'crlf'         => "\r\n",
            'wordwrap'     => true,
        ];
        $this->email->initialize($emailConfig);

        $this->email->from($fromEmail, 'BERPS');
        $this->email->to($email);
        $this->email->subject('Your BERPS password has been reset');
        $this->email->set_mailtype('html');
        $this->email->set_newline("\r\n");

        $safeName = htmlspecialchars($displayName !== '' ? $displayName : $username, ENT_QUOTES, 'UTF-8');
        $safeUsername = htmlspecialchars($username, ENT_QUOTES, 'UTF-8');
        $safeTempPass = htmlspecialchars($temporaryPassword, ENT_QUOTES, 'UTF-8');

        $message = '
            <p>Hello ' . $safeName . ',</p>
            <p>Your BERPS password has been reset by an administrator.</p>
            <p>
                <strong>Username:</strong> ' . $safeUsername . '<br>
                <strong>Temporary Password:</strong> ' . $safeTempPass . '
            </p>
            <p>Please sign in and change this password right away.</p>
            <p>Thank you,<br>BERPS</p>
        ';

        $this->email->message($message);

        if ($this->email->send()) {
            $this->_set_notice('Temporary password emailed to ' . $email . '.', 'success');
            // Store password in session to display on screen (use userdata like notices)
            $this->session->set_userdata('reset_password_display', [
                'user_name' => $displayName !== '' ? $displayName : $username,
                'username' => $username,
                'temp_password' => $temporaryPassword,
                'email' => $email,
            ]);
        } else {
            // revert password if email fails
            if ($oldHash !== null) {
                $this->User_model->update($id, ['password' => $oldHash]);
            }
            $errorMsg = strip_tags($this->email->print_debugger(['headers']));
            $this->_set_notice('Could not send email. Password was not changed. ' . $errorMsg, 'error');
        }

        redirect('users');
    }


    function _validate_currentpassword()
    {
        $level = strtolower((string) $this->session->userdata('level'));
        if ($level === 'client') {
            $custID = trim((string) $this->session->userdata('client_cust_id'));
            $settingsID = (int) $this->session->userdata('settingsID');
            $stored_hash = $this->Login_model->get_client_portal_password($custID, $settingsID);
        } else {
            $username = $this->session->userdata('username');
            $stored_hash = $this->User_model->get_user_password($username);
        }

        if ($stored_hash && password_verify($this->input->post('currentpassword'), $stored_hash)) {
            return TRUE;
        } else {
            $this->form_validation->set_message('_validate_currentpassword', 'Wrong current password.');
            return FALSE;
        }
    }

    private function generate_temporary_password($length = 10)
    {
        $alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz23456789@#$%';
        $password = '';
        $maxIndex = strlen($alphabet) - 1;

        for ($i = 0; $i < $length; $i++) {
            $password .= $alphabet[random_int(0, $maxIndex)];
        }

        return $password;
    }

    private function _set_notice($message, $type = 'success')
    {
        $this->session->set_userdata('users_notice', $message);
        $this->session->set_userdata('users_notice_type', $type === 'error' ? 'error' : 'success');
    }

    private function _can_manage_users()
    {
        $level = strtolower((string) $this->session->userdata('level'));
        return in_array($level, ['admin', 'pos admin'], true);
    }

    private function _load_current_company_feature_access()
    {
        if ($this->companyFeatureAccessLoaded) {
            return;
        }

        $this->companyFeatureAccessLoaded = true;
        $this->companyFeatureRestrictionsActive = false;
        $this->enabledCompanyFeatures = [];

        $settingsID = (int) $this->session->userdata('settingsID');
        if ($settingsID <= 0 || !$this->db->table_exists('company_features')) {
            return;
        }

        $featureRows = $this->db
            ->select('feature_key')
            ->where('settingsID', $settingsID)
            ->where('is_enabled', 1)
            ->get('company_features')
            ->result();

        if (empty($featureRows)) {
            return;
        }

        $this->companyFeatureRestrictionsActive = true;
        foreach ($featureRows as $featureRow) {
            $featureKey = trim((string) ($featureRow->feature_key ?? ''));
            if ($featureKey !== '') {
                $this->enabledCompanyFeatures[] = $featureKey;
            }
        }

        $this->enabledCompanyFeatures = array_values(array_unique($this->enabledCompanyFeatures));
    }

    private function _company_has_feature($featureKey)
    {
        $featureKey = trim((string) $featureKey);
        if ($featureKey === '') {
            return false;
        }

        $this->_load_current_company_feature_access();

        if (!$this->companyFeatureRestrictionsActive) {
            return true;
        }

        return in_array($featureKey, $this->enabledCompanyFeatures, true);
    }

    private function _can_manage_pos_roles()
    {
        return $this->_company_has_feature('pos');
    }

    private function _is_pos_role($position)
    {
        $position = strtolower(trim((string) $position));
        return in_array($position, ['manager', 'cashier', 'pos admin', 'pos staff'], true);
    }

    private function _deny_users_access()
    {
        $level = strtolower((string) $this->session->userdata('level'));
        if ($level === 'client') {
            redirect('Page/clientDashboard');
        }

        echo 'Access Denied';
        exit;
    }
}
