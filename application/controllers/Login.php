<?php
class Login extends CI_Controller
{
    function __construct()
    {
        parent::__construct();
        $this->load->model('Login_model');
    }

    function index()
    {
        // If already logged in, redirect to appropriate dashboard
        if ($this->session->userdata('logged_in')) {
            $position = $this->session->userdata('level');
            if ($position === 'Super Admin' || $position === 'System Administrator') {
                redirect('page/superAdmin');
            } else if ($position === 'Admin') {
                redirect('page/admin');
            } else if ($position === 'POS Admin' || $position === 'Manager') {
                redirect('Pos/posAdmin');
            } else if ($position === 'Staff' || $position === 'Encoder') {
                redirect('page/staff');
            } else if ($position === 'POS Staff' || $position === 'Cashier') {
                redirect('Pos/posStaff');
            } else if ($position === 'Client') {
                redirect('page/clientDashboard');
            }
        }
        $this->load->view('landing_page');
    }


    function login()
    {
        // If already logged in, redirect to appropriate dashboard
        if ($this->session->userdata('logged_in')) {
            $position = $this->session->userdata('level');
            if ($position === 'Super Admin' || $position === 'System Administrator') {
                redirect('page/superAdmin');
            } else if ($position === 'Admin') {
                redirect('page/admin');
            } else if ($position === 'POS Admin' || $position === 'Manager') {
                redirect('Pos/posAdmin');
            } else if ($position === 'Staff' || $position === 'Encoder') {
                redirect('page/staff');
            } else if ($position === 'POS Staff' || $position === 'Cashier') {
                redirect('Pos/posStaff');
            } else if ($position === 'Client') {
                redirect('page/clientDashboard');
            }
        }
        $this->load->view('landing_page');
    }

    function signup_page()
    {
        // If already logged in, redirect to appropriate dashboard
        if ($this->session->userdata('logged_in')) {
            $position = $this->session->userdata('level');
            if ($position === 'Super Admin' || $position === 'System Administrator') {
                redirect('page/superAdmin');
            } else if ($position === 'Admin') {
                redirect('page/admin');
            } else if ($position === 'POS Admin' || $position === 'Manager') {
                redirect('Pos/posAdmin');
            } else if ($position === 'Staff' || $position === 'Encoder') {
                redirect('page/staff');
            } else if ($position === 'POS Staff' || $position === 'Cashier') {
                redirect('Pos/posStaff');
            } else if ($position === 'Client') {
                redirect('page/clientDashboard');
            }
        }
        
        // Get enabled signup packages
        $data['enabledPackages'] = array();
        if ($this->db->table_exists('signup_packages')) {
            $this->db->where('is_enabled', 1);
            $this->db->order_by('package_id', 'ASC');
            $result = $this->db->get('signup_packages')->result();
            foreach ($result as $row) {
                $data['enabledPackages'][] = $row->package_id;
            }
        }
        
        // Temporarily force only package 2 for testing
        $data['enabledPackages'] = array('2');
        
        // Get reCAPTCHA settings
        $data['recaptchaEnabled'] = false;
        $data['recaptchaSiteKey'] = '';
        $data['recaptchaVersion'] = 'v2';
        if ($this->db->table_exists('recaptcha_settings')) {
            $this->db->limit(1);
            $recaptchaSettings = $this->db->get('recaptcha_settings')->row();
            if ($recaptchaSettings && $recaptchaSettings->is_enabled && !empty($recaptchaSettings->site_key)) {
                $data['recaptchaEnabled'] = true;
                $data['recaptchaSiteKey'] = $recaptchaSettings->site_key;
                $data['recaptchaVersion'] = $recaptchaSettings->recaptcha_version ? $recaptchaSettings->recaptcha_version : 'v2';
            }
        }
        
        // Pass form data from session if exists (for error recovery)
        $data['form_data'] = $this->session->flashdata('form_data');
        $this->load->view('signup_page', $data);
    }

    public function auth()
    {
        $username = $this->input->post('username', TRUE);
        $password = $this->input->post('password', TRUE);

        $user = $this->Login_model->get_user_by_username($username);

        if ($user && password_verify($password, $user->password)) {
            // Check account status only for new signups (those with confirmation_token)
            if (isset($user->confirmation_token) && !empty($user->confirmation_token) && isset($user->acctStat) && $user->acctStat !== 'Active') {
                $this->session->set_flashdata('msg', 'Your account is not yet confirmed. Please check your email and confirm your account before logging in.');
                redirect('Login/login');
                return;
            }

            $idNumber = $user->IDNumber ?? $user->user_id ?? $user->username;
            $user_data = array(
                'username'    => $user->username,
                'fname'       => $user->fName,
                'mname'       => $user->mName,
                'lname'       => $user->lName,
                'avatar'      => $user->avatar,
                'email'       => $user->email,
                'level'       => $user->position,
                'IDNumber'    => $idNumber,
                'user_id'     => $user->user_id,
                'settingsID'  => $user->settingsID,
                'logged_in'   => TRUE
            );
            $this->session->set_userdata($user_data);

            if ($user->position === 'Super Admin' || $user->position === 'System Administrator') {
                redirect('page/superAdmin');
            } else if ($user->position === 'Admin') {
                redirect('page/admin');
            } else if ($user->position === 'POS Admin' || $user->position === 'Manager') {
                redirect('Pos/posAdmin');
            } else if ($user->position === 'Staff' || $user->position === 'Encoder') {
                redirect('page/staff');
            } else if ($user->position === 'POS Staff' || $user->position === 'Cashier') {
                redirect('Pos/posStaff');
            } else if ($user->position === 'Client') {
                $client = $this->Login_model->get_client_by_identifier($user->username, $user->settingsID ?? null);
                $displayName = trim((string) ($client->Customer ?? ''));
                if ($displayName === '') {
                    $displayName = trim((string) ($user->fName ?? ''));
                }
                if ($displayName === '') {
                    $displayName = trim((string) ($user->username ?? 'Client'));
                }

                $this->session->set_userdata([
                    'level'          => 'Client',
                    'client_cust_id' => $client->CustID ?? null,
                    'client_name'    => $displayName,
                ]);
                redirect('page/clientDashboard');
            } else {
                redirect('login');
            }
        } else {
            $client = $this->Login_model->get_client_by_identifier($username);

            if ($client && !empty($client->portal_password) && password_verify($password, $client->portal_password)) {
                $clientEmail = trim((string) ($client->client_email ?? ''));
                $companyEmail = trim((string) ($client->CompanyEmail ?? ''));
                $displayName = trim((string) ($client->Customer ?? 'Client'));
                $loginName = $clientEmail !== '' ? $clientEmail : (string) $client->CustID;

                $client_data = array(
                    'username'       => $loginName,
                    'fname'          => $displayName,
                    'mname'          => '',
                    'lname'          => '',
                    'avatar'         => '',
                    'email'          => $clientEmail !== '' ? $clientEmail : $companyEmail,
                    'level'          => 'Client',
                    'IDNumber'       => $client->CustID,
                    'user_id'        => null,
                    'settingsID'     => $client->settingsID,
                    'client_cust_id' => $client->CustID,
                    'client_name'    => $displayName,
                    'logged_in'      => TRUE
                );

                $this->session->set_userdata($client_data);
                $this->Login_model->update_client_last_login($client->CustID, $client->settingsID);
                redirect('page/clientDashboard');
                return;
            }

            $this->session->set_flashdata('msg', 'Invalid username or password!');
            redirect('Login/login');
            return;
        }
    }




    function logout()
    {
        $this->session->sess_destroy();
        redirect('login');
    }
    public function forgot_pass()
    {
        // Legacy entry point, keep for compatibility.
        return $this->forgot();
    }

    public function forgot()
    {
        $this->load->helper('form');
        $this->load->library('form_validation');

        if ($this->input->method() === 'post') {
            $this->form_validation->set_rules('username', 'Username', 'trim|required');
            $this->form_validation->set_rules('email', 'Email', 'trim|required|valid_email');

            if ($this->form_validation->run()) {
                $username = $this->input->post('username', TRUE);
                $email = $this->input->post('email', TRUE);
                $user = $this->Login_model->find_user_for_reset($username);

                if ($user) {
                    $token = $this->Login_model->create_password_reset($user, 3600, $email);
                    $sent = $this->Login_model->send_reset_email($user, $token, $email);

                    if ($sent) {
                        $this->session->set_flashdata('success', 'If the account exists, a reset link has been sent to the email provided.');
                    } else {
                        $this->session->set_flashdata('error', 'We could not send the reset email right now. Please try again.');
                    }
                } else {
                    $this->session->set_flashdata('info', 'If the account exists, a reset link has been sent to the email provided.');
                }

                redirect('login/forgot');
            }
        }

        $data['page_title'] = 'Forgot Password';
        $this->load->view('auth_forgot', $data);
    }

    public function reset($selector = null, $validator = null)
    {
        $this->load->helper('form');
        $this->load->library('form_validation');

        if ($this->input->method() === 'post') {
            $selector = $this->input->post('selector', TRUE);
            $validator = $this->input->post('validator', TRUE);

            $this->form_validation->set_rules('password', 'New Password', 'required|min_length[8]');
            $this->form_validation->set_rules('password2', 'Confirm Password', 'required|matches[password]');

            if ($this->form_validation->run()) {
                if (!$this->is_valid_selector($selector) || !$this->is_valid_selector($validator)) {
                    $this->session->set_flashdata('msg', 'Reset link is invalid or has expired.');
                    return redirect('login/forgot');
                }

                $tokenRow = $this->Login_model->find_reset_by_selector($selector);
                if ($tokenRow && $this->Login_model->is_valid_reset($tokenRow, $validator)) {
                    $this->Login_model->update_user_password($tokenRow->user_id, $this->input->post('password', TRUE));
                    $this->Login_model->delete_resets_for_user($tokenRow->user_id);

                    $this->session->set_flashdata('msg', 'Your password has been updated. You can now sign in.');
                    return redirect('login');
                }

                $this->session->set_flashdata('msg', 'Reset link is invalid or has expired.');
                return redirect('login/forgot');
            }
        } else {
            if (!$this->is_valid_selector($selector) || !$this->is_valid_selector($validator)) {
                $this->session->set_flashdata('msg', 'Reset link is invalid or has expired.');
                return redirect('login/forgot');
            }

            $tokenRow = $this->Login_model->find_reset_by_selector($selector);
            if (!$tokenRow || !$this->Login_model->is_valid_reset($tokenRow, $validator)) {
                $this->session->set_flashdata('msg', 'Reset link is invalid or has expired.');
                return redirect('login/forgot');
            }
        }

        $data = [
            'page_title' => 'Reset Password',
            'selector'   => $selector,
            'validator'  => $validator,
        ];

        $this->load->view('auth_reset', $data);
    }

    private function is_valid_selector($value)
    {
        $length = strlen($value);
        return !empty($value) && $length >= 16 && $length <= 128 && ctype_xdigit($value);
    }

    public function signup()
    {
        // Ensure confirmation token columns exist
        $this->_ensure_confirmation_columns();

        // Validate reCAPTCHA if enabled
        if ($this->db->table_exists('recaptcha_settings')) {
            $this->db->limit(1);
            $recaptchaSettings = $this->db->get('recaptcha_settings')->row();
            if ($recaptchaSettings && $recaptchaSettings->is_enabled && !empty($recaptchaSettings->secret_key)) {
                $recaptchaResponse = $this->input->post('g-recaptcha-response');
                if (empty($recaptchaResponse)) {
                    $this->session->set_flashdata('signup_error', 'Please complete the reCAPTCHA verification.');
                    $this->session->set_flashdata('form_data', $this->input->post(NULL, TRUE));
                    redirect('Login/signup_page');
                    return;
                }

                // Verify reCAPTCHA with Google
                $verifyUrl = 'https://www.google.com/recaptcha/api/siteverify';
                $data = array(
                    'secret' => $recaptchaSettings->secret_key,
                    'response' => $recaptchaResponse
                );

                $options = array(
                    'http' => array(
                        'header' => "Content-type: application/x-www-form-urlencoded\r\n",
                        'method' => 'POST',
                        'content' => http_build_query($data)
                    )
                );

                $context = stream_context_create($options);
                $response = file_get_contents($verifyUrl, false, $context);
                $responseKeys = json_decode($response, true);

                if (!$responseKeys['success']) {
                    $this->session->set_flashdata('signup_error', 'reCAPTCHA verification failed. Please try again.');
                    $this->session->set_flashdata('form_data', $this->input->post(NULL, TRUE));
                    redirect('Login/signup_page');
                    return;
                }
            }
        }

        $accountType = trim((string) $this->input->post('accountType'));
        $compName = trim((string) $this->input->post('compName'));
        $compAddress = trim((string) $this->input->post('compAddress'));
        $compTin = trim((string) $this->input->post('compTin'));
        $proprietor = trim((string) $this->input->post('proprietor'));
        $compType = trim((string) $this->input->post('compType'));
        $businessLines = trim((string) $this->input->post('businessLines'));
        $email = trim((string) $this->input->post('email'));
        $password = trim((string) $this->input->post('password'));
        $fName = trim((string) $this->input->post('fName'));
        $lName = trim((string) $this->input->post('lName'));
        $package = trim((string) $this->input->post('package'));

        // Use email as username
        $username = $email;

        // Force company account type
        $accountType = 'company';

        // Email validation
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->session->set_flashdata('signup_error', 'Please enter a valid email address.');
            $this->session->set_flashdata('form_data', $this->input->post(NULL, TRUE));
            redirect('Login/signup_page');
            return;
        }

        // Validation - company fields are now hidden, so no validation needed

        if ($email === '' || $password === '' || $fName === '' || $lName === '') {
            $this->session->set_flashdata('signup_error', 'Please fill in all required fields.');
            $this->session->set_flashdata('form_data', $this->input->post(NULL, TRUE));
            redirect('Login/signup_page');
            return;
        }

        // Force package to 2
        $package = '2';

        // Check if email already exists
        $existingEmail = $this->db->where('email', $email)->get('users')->row();
        if ($existingEmail) {
            $this->session->set_flashdata('signup_error', 'Email already registered. Please use another email.');
            $this->session->set_flashdata('form_data', $this->input->post(NULL, TRUE));
            redirect('Login/signup_page');
            return;
        }

        // Force settingsID to 6
        $settingsID = 6;

        // Update pos_settings if it exists
        $existingSettings = $this->db->where('settingsID', $settingsID)->get('pos_settings')->row();
        if ($existingSettings) {
            $settingsData = array(
                'CompName' => $compName,
                'CompAddress' => $compAddress,
                'CompTin' => $compTin,
                'Proprietor' => $proprietor,
                'CompType' => $compType,
                'BusinessLines' => $businessLines,
            );

            // Add package_id and package_ids if columns exist
            if ($this->db->field_exists('package_id', 'pos_settings')) {
                $settingsData['package_id'] = (int) $package;
            }

            if ($this->db->field_exists('package_ids', 'pos_settings')) {
                $settingsData['package_ids'] = (string) $package;
            }

            $this->db->where('settingsID', $settingsID);
            $this->db->update('pos_settings', $settingsData);
        }

        // Generate confirmation token
        $confirmationToken = bin2hex(random_bytes(32));
        $tokenExpiry = date('Y-m-d H:i:s', strtotime('+24 hours'));

        // Insert into users table
        $userData = array(
            'username' => $username,
            'email' => $email,
            'password' => password_hash($password, PASSWORD_BCRYPT),
            'fName' => $fName,
            'lName' => $lName,
            'position' => 'Admin',
            'acctStat' => 'Pending',
            'settingsID' => $settingsID,
            'confirmation_token' => $confirmationToken,
            'confirmation_token_expiry' => $tokenExpiry,
        );

        // Check if created_at column exists
        if ($this->db->field_exists('created_at', 'users')) {
            $userData['created_at'] = date('Y-m-d H:i:s');
        }

        $this->db->insert('users', $userData);
        $userID = $this->db->insert_id();

        if (!$userID) {
            // Rollback settings insert if user insert fails
            $this->db->where('settingsID', $settingsID);
            $this->db->delete('pos_settings');
            $this->session->set_flashdata('signup_error', 'Failed to create user account. Please try again.');
            $this->session->set_flashdata('form_data', $this->input->post(NULL, TRUE));
            redirect('Login/signup_page');
            return;
        }

        // Send confirmation email
        $emailSent = $this->sendConfirmationEmail($email, $username, $confirmationToken);

        if ($emailSent) {
            $this->session->set_flashdata('signup_success', 'Account created successfully! Please check your email to confirm your account before logging in.');
        } else {
            // Check if running on localhost
            $host = isset($_SERVER['HTTP_HOST']) ? filter_var($_SERVER['HTTP_HOST'], FILTER_SANITIZE_URL) : '';
            $isLocalhost = in_array($host, ['localhost', '127.0.0.1', '::1']) || strpos($host, '192.168.') === 0 || strpos($host, '10.') === 0;

            if ($isLocalhost) {
                // Email failed, provide confirmation link for manual testing on localhost
                $confirmationLink = site_url('Login/confirm/' . $confirmationToken);
                $this->session->set_flashdata('signup_success', 'Account created successfully! Email could not be sent. Please use this link to confirm: <a href="' . htmlspecialchars($confirmationLink, ENT_QUOTES, 'UTF-8') . '">' . htmlspecialchars($confirmationLink, ENT_QUOTES, 'UTF-8') . '</a>');
            } else {
                // Online environment - show support email message
                $this->session->set_flashdata('signup_success', 'Account created successfully! If you did not receive the confirmation email, please contact support at <a href="mailto:support@berps.online">support@berps.online</a>');
            }
        }
        redirect('Login/signup_page');
    }

    private function _ensure_confirmation_columns()
    {
        // Check if confirmation_token column exists
        if (!$this->db->field_exists('confirmation_token', 'users')) {
            $this->db->query("ALTER TABLE users ADD COLUMN confirmation_token VARCHAR(64) NULL");
        }

        // Check if confirmation_token_expiry column exists
        if (!$this->db->field_exists('confirmation_token_expiry', 'users')) {
            $this->db->query("ALTER TABLE users ADD COLUMN confirmation_token_expiry DATETIME NULL");
        }
    }

    private function _applyPackageFeatures($settingsID, $packageId)
    {
        $settingsID = (int) $settingsID;
        
        if ($settingsID <= 0) {
            return false;
        }

        // Define package features
        $packages = array(
            1 => array('invoice', 'deliveries', 'expenses', 'job_order', 'projects', 'support', 'tasks', 'notes', 'calendar'),
            2 => array('tasks', 'notes', 'calendar'),
            3 => array('payroll', 'employee_payroll', 'salary_computation', 'payroll_reports', 'notes'),
            4 => array('pos', 'notes'),
        );

        // Handle "all" package
        if ($packageId === 'all') {
            // Combine all features from all packages
            $features = array();
            foreach ($packages as $pkgFeatures) {
                $features = array_merge($features, $pkgFeatures);
            }
            $features = array_unique($features); // Remove duplicates
        } else {
            $packageId = (int) $packageId;
            if ($packageId <= 0 || !isset($packages[$packageId])) {
                return false;
            }
            $features = $packages[$packageId];
        }
        
        // Feature catalog
        $catalog = array(
            'invoice' => 'Invoice Management',
            'deliveries' => 'Deliveries Management',
            'expenses' => 'Expenses Tracker',
            'job_order' => 'Job Order Management',
            'pos' => 'Point of Sale (POS)',
            'projects' => 'Project Management',
            'support' => 'Customer Support',
            'tasks' => 'Task Management',
            'notes' => 'Notes Module',
            'calendar' => 'Calendar & Scheduling',
            'payroll' => 'Payroll Processing',
            'employee_payroll' => 'Employee Payroll Records',
            'salary_computation' => 'Salary Computation',
            'payroll_reports' => 'Payroll Reports',
        );

        // Ensure company_features table exists
        if (!$this->db->table_exists('company_features')) {
            $this->db->query("
                CREATE TABLE IF NOT EXISTS `company_features` (
                  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                  `settingsID` int(11) NOT NULL,
                  `feature_key` varchar(100) NOT NULL,
                  `feature_name` varchar(255) NOT NULL,
                  `is_enabled` tinyint(1) NOT NULL DEFAULT 1,
                  `created_at` datetime DEFAULT NULL,
                  PRIMARY KEY (`id`),
                  KEY `idx_company_features_settings` (`settingsID`),
                  KEY `idx_company_features_key` (`feature_key`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
            ");
        }

        // Clear existing features for this company
        $this->db->where('settingsID', $settingsID);
        $this->db->delete('company_features');

        // Insert new features
        $timestamp = date('Y-m-d H:i:s');
        foreach ($features as $featureKey) {
            if (isset($catalog[$featureKey])) {
                $this->db->insert('company_features', array(
                    'settingsID' => $settingsID,
                    'feature_key' => $featureKey,
                    'feature_name' => $catalog[$featureKey],
                    'is_enabled' => 1,
                    'created_at' => $timestamp,
                ));
            }
        }

        return true;
    }

    private function sendConfirmationEmail($email, $username, $token)
    {
        $this->load->library('email');
        $this->load->config('email');

        $confirmationLink = site_url('Login/confirm/' . $token);

        $message = $this->load->view('email/confirm_account', array(
            'username' => $username,
            'confirmation_link' => $confirmationLink,
        ), true);

        $fromAddress = $this->config->item('smtp_user');
        if (empty($fromAddress)) {
            $fromAddress = 'no-reply@' . parse_url(base_url(), PHP_URL_HOST);
        }

        $this->email->from($fromAddress, 'BERPS');
        $this->email->to($email);
        $this->email->subject('Confirm Your BERPS Account');
        $this->email->message($message);

        $result = $this->email->send();

        // Log email errors for debugging
        if (!$result) {
            log_message('error', 'Email send failed: ' . $this->email->print_debugger(array('headers')));
        }

        return $result;
    }

    public function confirm($token = null)
    {
        if ($token === null) {
            $this->session->set_flashdata('msg', 'Invalid confirmation link.');
            redirect('login');
            return;
        }

        // Find user with this token
        $user = $this->db->where('confirmation_token', $token)
                          ->where('confirmation_token_expiry >', date('Y-m-d H:i:s'))
                          ->get('users')
                          ->row();

        if (!$user) {
            $this->session->set_flashdata('msg', 'Invalid or expired confirmation link. Please request a new confirmation email.');
            redirect('login');
            return;
        }

        // Update user status to Active
        $this->db->where('user_id', $user->user_id);
        $this->db->update('users', array(
            'acctStat' => 'Active',
            'confirmation_token' => null,
            'confirmation_token_expiry' => null,
        ));

        $this->session->set_flashdata('signup_success', 'Account confirmed successfully! You can now login.');
        redirect('login');
    }
}
