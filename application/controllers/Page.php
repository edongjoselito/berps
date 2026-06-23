<?php
class Page extends CI_Controller
{
  private $invoiceUnitChoicesCache = array();
  private $currentCompanyFeatureKeys = array();
  private $currentCompanyFeatureAccessLoaded = false;
  private $currentCompanyFeatureRestrictionsActive = false;

  function __construct()
  {
    parent::__construct();
    $this->load->database();
    $this->load->helper('url');
    $this->load->helper('form');
    $this->load->library('session');
    $this->load->library('form_validation');
    $this->load->library('user_agent');
    $this->load->helper('text');
    date_default_timezone_set('Asia/Manila'); // ensure consistent timestamps

    // Allow public access to knowledgeBaseView method
    $method = strtolower((string) $this->router->fetch_method());
    if ($this->session->userdata('logged_in') !== TRUE && !in_array($method, array('knowledgebaseview', 'knowledgebaseattachment'), true)) {
      redirect('login');
    }

    if (strtolower(trim((string) $this->session->userdata('level'))) === 'client') {
      $method = strtolower((string) $this->router->fetch_method());
      $allowedClientMethods = array(
        'clientdashboard',
        'clientprofile',
        'customerhistory',
        'paymenthistory',
        'knowledgebase',
        'knowledgebaseview',
        'knowledgebasesearch',
        'knowledgebaseattachment',
        'invoice',
        'clientmytickets',
        'clientticketview',
        'clientreportissue',
        'cancelclientticket',
        'clientrequestedtoday',
        'clientaccomplishedtasks',
        'clientpendingtasks',
        'clientclosedtaskreport',
        'cancelledticketlogs',
        'reopensupportissue',
        'submitclientsupportissue',
        'submitclientticketreply',
      );

      if (!in_array($method, $allowedClientMethods, true)) {
        redirect('Page/clientDashboard');
      }
    }

    $this->load->model('CashModel');
    $this->load->model('RemindersModel');
    $this->load->model('StudentModel');
    $this->load->model('PersonnelModel');
    $this->load->model('SettingsModel');
    $this->load->model('Knowledge_base_model');

    if (in_array($method, array(
      'accountingreports',
      'updateemployee',
      'payrollmodule',
      'payrollsetup',
      'savepayrollprofile',
      'addpayrollloan',
      'addpayrollcashadvance',
      'generatepayroll',
      'payrollrun',
      'payrollruns',
      'payrollpayslip',
      'employeelist',
    ), true)) {
      $this->_loadPayrollModel();
    }

    $this->_ensureInvoiceRecurringTerminationDateColumn();
    $this->_ensureInvoiceExpirationDateColumn();
    $this->_ensureInvoiceDueDateColumn();
    $this->_ensureCoverageOptionColumn();
    $this->_ensureTaskChecklistTable();
    $this->_ensureEmployeeEmailColumn();
    $this->_ensureUserSupportPermissionColumns();
    $this->_ensureProjectsTaskClientCommentColumn();
    $this->_ensureProjectsTaskPointsColumn();

    if (in_array(strtolower(trim((string) $this->session->userdata('level'))), array('admin', 'staff'), true)) {
      $this->_ensureProjectsTaskDueDateColumn();
    }

    $this->_ensureExpenseCategoryTable();
    $this->_ensurePosCategoryTable();
    $this->_ensureClientSupportTables();
    $this->_ensureKnowledgeBaseAttachmentColumns();
    $this->_ensureCompanyFeatureTables();
    $this->_ensureSignupPackagesTable();
    $this->_ensureRecaptchaSettingsTable();
    $this->_ensurePosActivationKeysTable();
    $this->_loadPayrollModel();

    $this->_autoGenerateRecurringInvoicesFromWeb();
  }

  private function _loadPayrollModel()
  {
    if (!isset($this->PayrollModel)) {
      $this->load->model('PayrollModel');
    }

    $this->PayrollModel->ensurePayrollTables();
  }

  private function _ensureKnowledgeBaseAttachmentColumns()
  {
    if (!$this->db->table_exists('knowledge_base')) {
      return;
    }

    if (!$this->db->field_exists('attachment_path', 'knowledge_base')) {
      $this->db->query("ALTER TABLE `knowledge_base` ADD COLUMN `attachment_path` varchar(255) DEFAULT NULL AFTER `content`");
    }

    if (!$this->db->field_exists('attachment_name', 'knowledge_base')) {
      $this->db->query("ALTER TABLE `knowledge_base` ADD COLUMN `attachment_name` varchar(255) DEFAULT NULL AFTER `attachment_path`");
    }
  }

  private function _ensureCompanyFeatureTables()
  {
    if ($this->db->table_exists('pos_settings') && !$this->db->field_exists('package_id', 'pos_settings')) {
      $this->db->query("ALTER TABLE `pos_settings` ADD COLUMN `package_id` TINYINT(3) UNSIGNED DEFAULT NULL");
    }

    if ($this->db->table_exists('pos_settings') && !$this->db->field_exists('package_ids', 'pos_settings')) {
      $this->db->query("ALTER TABLE `pos_settings` ADD COLUMN `package_ids` varchar(255) DEFAULT NULL AFTER `package_id`");
    }

    if (!$this->db->table_exists('company_features')) {
      $this->db->query("
        CREATE TABLE `company_features` (
          `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
          `settingsID` int(11) NOT NULL,
          `feature_key` varchar(100) NOT NULL,
          `feature_name` varchar(255) NOT NULL,
          `is_enabled` tinyint(1) NOT NULL DEFAULT 1,
          `created_at` datetime DEFAULT NULL,
          PRIMARY KEY (`id`),
          UNIQUE KEY `uniq_company_feature` (`settingsID`, `feature_key`),
          KEY `idx_company_feature_settings` (`settingsID`),
          KEY `idx_company_feature_enabled` (`settingsID`, `is_enabled`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
      ");
      return;
    }

    if (!$this->db->field_exists('feature_name', 'company_features')) {
      $this->db->query("ALTER TABLE `company_features` ADD COLUMN `feature_name` varchar(255) NOT NULL AFTER `feature_key`");
    }

    if (!$this->db->field_exists('is_enabled', 'company_features')) {
      $this->db->query("ALTER TABLE `company_features` ADD COLUMN `is_enabled` tinyint(1) NOT NULL DEFAULT 1 AFTER `feature_name`");
    }

    if (!$this->db->field_exists('created_at', 'company_features')) {
      $this->db->query("ALTER TABLE `company_features` ADD COLUMN `created_at` datetime DEFAULT NULL AFTER `is_enabled`");
    }
  }

  private function _ensureSignupPackagesTable()
  {
    if (!$this->db->table_exists('signup_packages')) {
      $this->db->query("
        CREATE TABLE `signup_packages` (
          `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
          `package_id` varchar(10) NOT NULL,
          `is_enabled` tinyint(1) NOT NULL DEFAULT 1,
          `created_at` datetime DEFAULT NULL,
          `updated_at` datetime DEFAULT NULL,
          PRIMARY KEY (`id`),
          UNIQUE KEY `uniq_signup_package` (`package_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
      ");
    }
    
    // Check if table has any records, if not insert defaults
    $count = $this->db->count_all('signup_packages');
    if ($count == 0) {
      // Insert default packages (all enabled by default)
      $defaultPackages = array('all', '1', '2', '3', '4');
      $timestamp = date('Y-m-d H:i:s');
      foreach ($defaultPackages as $pkgId) {
        $this->db->insert('signup_packages', array(
          'package_id' => $pkgId,
          'is_enabled' => 1,
          'created_at' => $timestamp,
          'updated_at' => $timestamp
        ));
      }
    }
  }

  private function _ensurePosActivationKeysTable()
  {
    if (!$this->db->table_exists('pos_activation_keys')) {
      $this->db->query("
        CREATE TABLE `pos_activation_keys` (
          `id` int unsigned NOT NULL AUTO_INCREMENT,
          `settingsID` int unsigned NOT NULL,
          `key_hash` char(64) NOT NULL,
          `key_last4` varchar(8) NOT NULL,
          `status` varchar(20) NOT NULL DEFAULT 'unused',
          `branch_id` int unsigned DEFAULT NULL,
          `generated_by` int unsigned DEFAULT NULL,
          `used_by` int unsigned DEFAULT NULL,
          `generated_at` datetime NOT NULL,
          `used_at` datetime DEFAULT NULL,
          `expires_at` datetime DEFAULT NULL,
          `notes` varchar(255) DEFAULT NULL,
          PRIMARY KEY (`id`),
          UNIQUE KEY `uniq_pos_activation_key_hash` (`key_hash`),
          KEY `idx_pos_activation_keys_settings_status` (`settingsID`, `status`),
          KEY `idx_pos_activation_keys_branch` (`branch_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
      ");
      return;
    }

    $columns = array(
      'settingsID' => "ALTER TABLE `pos_activation_keys` ADD COLUMN `settingsID` int unsigned NOT NULL DEFAULT 0 AFTER `id`",
      'key_hash' => "ALTER TABLE `pos_activation_keys` ADD COLUMN `key_hash` char(64) NOT NULL AFTER `settingsID`",
      'key_last4' => "ALTER TABLE `pos_activation_keys` ADD COLUMN `key_last4` varchar(8) NOT NULL AFTER `key_hash`",
      'status' => "ALTER TABLE `pos_activation_keys` ADD COLUMN `status` varchar(20) NOT NULL DEFAULT 'unused' AFTER `key_last4`",
      'branch_id' => "ALTER TABLE `pos_activation_keys` ADD COLUMN `branch_id` int unsigned DEFAULT NULL AFTER `status`",
      'generated_by' => "ALTER TABLE `pos_activation_keys` ADD COLUMN `generated_by` int unsigned DEFAULT NULL AFTER `branch_id`",
      'used_by' => "ALTER TABLE `pos_activation_keys` ADD COLUMN `used_by` int unsigned DEFAULT NULL AFTER `generated_by`",
      'generated_at' => "ALTER TABLE `pos_activation_keys` ADD COLUMN `generated_at` datetime NOT NULL AFTER `used_by`",
      'used_at' => "ALTER TABLE `pos_activation_keys` ADD COLUMN `used_at` datetime DEFAULT NULL AFTER `generated_at`",
      'expires_at' => "ALTER TABLE `pos_activation_keys` ADD COLUMN `expires_at` datetime DEFAULT NULL AFTER `used_at`",
      'notes' => "ALTER TABLE `pos_activation_keys` ADD COLUMN `notes` varchar(255) DEFAULT NULL AFTER `expires_at`",
    );

    foreach ($columns as $column => $sql) {
      if (!$this->db->field_exists($column, 'pos_activation_keys')) {
        $this->db->query($sql);
      }
    }
  }

  private function _ensureRecaptchaSettingsTable()
  {
    if (!$this->db->table_exists('recaptcha_settings')) {
      $this->db->query("
        CREATE TABLE `recaptcha_settings` (
          `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
          `site_key` varchar(255) NOT NULL,
          `secret_key` varchar(255) NOT NULL,
          `is_enabled` tinyint(1) NOT NULL DEFAULT 0,
          `recaptcha_version` varchar(10) NOT NULL DEFAULT 'v2',
          `created_at` datetime DEFAULT NULL,
          `updated_at` datetime DEFAULT NULL,
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
      ");
      
      // Insert default settings (disabled by default)
      $timestamp = date('Y-m-d H:i:s');
      $this->db->insert('recaptcha_settings', array(
        'site_key' => '',
        'secret_key' => '',
        'is_enabled' => 0,
        'recaptcha_version' => 'v2',
        'created_at' => $timestamp,
        'updated_at' => $timestamp
      ));
    }
  }

  private function _normalizePosActivationKey($key)
  {
    return preg_replace('/[^A-Z0-9]/', '', strtoupper(trim((string) $key)));
  }

  private function _generatePosActivationKeyValue()
  {
    $alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
    $groups = array();

    for ($group = 0; $group < 3; $group++) {
      $chunk = '';
      for ($i = 0; $i < 4; $i++) {
        $chunk .= $alphabet[random_int(0, strlen($alphabet) - 1)];
      }
      $groups[] = $chunk;
    }

    return 'BR-' . implode('-', $groups);
  }

  private function _ensureCompanyBillingTables()
  {
    if ($this->db->table_exists('pos_settings') && !$this->db->field_exists('billing_mode', 'pos_settings')) {
      $this->db->query("ALTER TABLE `pos_settings` ADD COLUMN `billing_mode` varchar(30) NOT NULL DEFAULT 'company'");
    }

    if ($this->db->table_exists('pos_settings') && !$this->db->field_exists('monthly_rate', 'pos_settings')) {
      $this->db->query("ALTER TABLE `pos_settings` ADD COLUMN `monthly_rate` decimal(10,2) NOT NULL DEFAULT 0.00");
    }

    if (!$this->db->table_exists('company_billing_records')) {
      $this->db->query("
        CREATE TABLE `company_billing_records` (
          `billing_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
          `settingsID` int(11) NOT NULL,
          `billing_month` date DEFAULT NULL,
          `billing_mode` varchar(30) NOT NULL DEFAULT 'company',
          `billable_units` int(11) UNSIGNED NOT NULL DEFAULT 1,
          `rate_per_month` decimal(10,2) NOT NULL DEFAULT 0.00,
          `amount_due` decimal(10,2) NOT NULL DEFAULT 0.00,
          `amount_paid` decimal(10,2) NOT NULL DEFAULT 0.00,
          `status` varchar(30) NOT NULL DEFAULT 'unpaid',
          `notes` text DEFAULT NULL,
          `created_at` datetime DEFAULT NULL,
          `updated_at` datetime DEFAULT NULL,
          `paid_at` datetime DEFAULT NULL,
          PRIMARY KEY (`billing_id`),
          KEY `idx_company_billing_settings` (`settingsID`),
          KEY `idx_company_billing_month` (`billing_month`),
          KEY `idx_company_billing_status` (`settingsID`, `status`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
      ");
    } else {
      if (!$this->db->field_exists('billing_mode', 'company_billing_records')) {
        $this->db->query("ALTER TABLE `company_billing_records` ADD COLUMN `billing_mode` varchar(30) NOT NULL DEFAULT 'company' AFTER `billing_month`");
      }

      if (!$this->db->field_exists('billable_units', 'company_billing_records')) {
        $this->db->query("ALTER TABLE `company_billing_records` ADD COLUMN `billable_units` int(11) UNSIGNED NOT NULL DEFAULT 1 AFTER `billing_mode`");
      }

      if (!$this->db->field_exists('rate_per_month', 'company_billing_records')) {
        $this->db->query("ALTER TABLE `company_billing_records` ADD COLUMN `rate_per_month` decimal(10,2) NOT NULL DEFAULT 0.00 AFTER `billable_units`");
      }

      if (!$this->db->field_exists('amount_due', 'company_billing_records')) {
        $this->db->query("ALTER TABLE `company_billing_records` ADD COLUMN `amount_due` decimal(10,2) NOT NULL DEFAULT 0.00 AFTER `rate_per_month`");
      }

      if (!$this->db->field_exists('amount_paid', 'company_billing_records')) {
        $this->db->query("ALTER TABLE `company_billing_records` ADD COLUMN `amount_paid` decimal(10,2) NOT NULL DEFAULT 0.00 AFTER `amount_due`");
      }

      if (!$this->db->field_exists('status', 'company_billing_records')) {
        $this->db->query("ALTER TABLE `company_billing_records` ADD COLUMN `status` varchar(30) NOT NULL DEFAULT 'unpaid' AFTER `amount_paid`");
      }

      if (!$this->db->field_exists('notes', 'company_billing_records')) {
        $this->db->query("ALTER TABLE `company_billing_records` ADD COLUMN `notes` text DEFAULT NULL AFTER `status`");
      }

      if (!$this->db->field_exists('created_at', 'company_billing_records')) {
        $this->db->query("ALTER TABLE `company_billing_records` ADD COLUMN `created_at` datetime DEFAULT NULL AFTER `notes`");
      }

      if (!$this->db->field_exists('updated_at', 'company_billing_records')) {
        $this->db->query("ALTER TABLE `company_billing_records` ADD COLUMN `updated_at` datetime DEFAULT NULL AFTER `created_at`");
      }

      if (!$this->db->field_exists('paid_at', 'company_billing_records')) {
        $this->db->query("ALTER TABLE `company_billing_records` ADD COLUMN `paid_at` datetime DEFAULT NULL AFTER `updated_at`");
      }
    }

    if (!$this->db->table_exists('company_billing_payments')) {
      $this->db->query("
        CREATE TABLE `company_billing_payments` (
          `payment_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
          `billing_id` int(11) UNSIGNED NOT NULL,
          `settingsID` int(11) NOT NULL,
          `amount_paid` decimal(10,2) NOT NULL DEFAULT 0.00,
          `payment_date` date DEFAULT NULL,
          `payment_method` varchar(100) DEFAULT NULL,
          `reference_no` varchar(150) DEFAULT NULL,
          `notes` text DEFAULT NULL,
          `created_at` datetime DEFAULT NULL,
          PRIMARY KEY (`payment_id`),
          KEY `idx_company_billing_payment_record` (`billing_id`),
          KEY `idx_company_billing_payment_settings` (`settingsID`),
          KEY `idx_company_billing_payment_date` (`payment_date`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
      ");
    } else {
      if (!$this->db->field_exists('payment_method', 'company_billing_payments')) {
        $this->db->query("ALTER TABLE `company_billing_payments` ADD COLUMN `payment_method` varchar(100) DEFAULT NULL AFTER `payment_date`");
      }

      if (!$this->db->field_exists('reference_no', 'company_billing_payments')) {
        $this->db->query("ALTER TABLE `company_billing_payments` ADD COLUMN `reference_no` varchar(150) DEFAULT NULL AFTER `payment_method`");
      }

      if (!$this->db->field_exists('notes', 'company_billing_payments')) {
        $this->db->query("ALTER TABLE `company_billing_payments` ADD COLUMN `notes` text DEFAULT NULL AFTER `reference_no`");
      }

      if (!$this->db->field_exists('created_at', 'company_billing_payments')) {
        $this->db->query("ALTER TABLE `company_billing_payments` ADD COLUMN `created_at` datetime DEFAULT NULL AFTER `notes`");
      }
    }
  }

  private function _getCompanyBillingModeOptions()
  {
    return array(
      'company' => array(
        'label' => 'Paid by Company',
        'description' => 'One monthly billing amount is charged to the company.',
        'rate_label' => 'Monthly rate per company',
      ),
      'individual' => array(
        'label' => 'Individually',
        'description' => 'Each active company user is billed separately every month.',
        'rate_label' => 'Monthly rate per active user',
      ),
      'free' => array(
        'label' => 'Free',
        'description' => 'No monthly billing is charged to the company.',
        'rate_label' => 'Monthly rate',
      ),
    );
  }

  private function _normalizeCompanyBillingMode($billingMode)
  {
    $billingMode = strtolower(trim((string) $billingMode));
    $options = $this->_getCompanyBillingModeOptions();

    return isset($options[$billingMode]) ? $billingMode : 'company';
  }

  private function _getCompanyBillingModeLabel($billingMode)
  {
    $billingMode = $this->_normalizeCompanyBillingMode($billingMode);
    $options = $this->_getCompanyBillingModeOptions();

    return (string) ($options[$billingMode]['label'] ?? 'Paid by Company');
  }

  private function _getCompanyBillableUserCount($settingsID)
  {
    $settingsID = (int) $settingsID;
    if ($settingsID <= 0 || !$this->db->table_exists('users')) {
      return 0;
    }

    $this->db->from('users');
    $this->db->where('settingsID', $settingsID);

    if ($this->db->field_exists('position', 'users')) {
      $this->db->where_in('position', array('Admin', 'Manager', 'Encoder', 'Staff', 'Cashier', 'POS Admin', 'POS Staff'));
    }

    if ($this->db->field_exists('acctStat', 'users')) {
      $this->db->where("LOWER(TRIM(COALESCE(acctStat, ''))) = 'active'", null, false);
    }

    $count = (int) $this->db->count_all_results();

    if ($count > 0) {
      return $count;
    }

    $this->db->from('users');
    $this->db->where('settingsID', $settingsID);
    if ($this->db->field_exists('position', 'users')) {
      $this->db->where("LOWER(TRIM(COALESCE(position, ''))) <> 'client'", null, false);
    }

    return (int) $this->db->count_all_results();
  }

  private function _calculateCompanyBillingAmount($billingMode, $ratePerMonth, $billableUnits)
  {
    $billingMode = $this->_normalizeCompanyBillingMode($billingMode);
    $ratePerMonth = round(max(0, (float) $ratePerMonth), 2);
    $billableUnits = max(0, (int) $billableUnits);

    if ($billingMode === 'free') {
      return 0.00;
    }

    if ($billingMode === 'individual') {
      return round($ratePerMonth * $billableUnits, 2);
    }

    return $ratePerMonth;
  }

  private function _resolveCompanyBillingStatus($amountDue, $amountPaid)
  {
    $amountDue = round(max(0, (float) $amountDue), 2);
    $amountPaid = round(max(0, (float) $amountPaid), 2);

    if ($amountDue <= 0) {
      return 'free';
    }

    if ($amountPaid <= 0) {
      return 'unpaid';
    }

    if ($amountPaid >= $amountDue) {
      return 'paid';
    }

    return 'partial';
  }

  private function _normalizeBillingMonthInput($billingMonthInput)
  {
    $billingMonthInput = trim((string) $billingMonthInput);
    if ($billingMonthInput === '') {
      return '';
    }

    if (preg_match('/^\d{4}-\d{2}$/', $billingMonthInput)) {
      $billingMonthInput .= '-01';
    }

    $timestamp = strtotime($billingMonthInput);
    if ($timestamp === false) {
      return '';
    }

    return date('Y-m-01', $timestamp);
  }

  private function _getCompanyBillingAggregates($settingsID)
  {
    $defaults = array(
      'record_count' => 0,
      'total_due' => 0.00,
      'total_paid' => 0.00,
      'outstanding_balance' => 0.00,
    );

    $settingsID = (int) $settingsID;
    if ($settingsID <= 0 || !$this->db->table_exists('company_billing_records')) {
      return $defaults;
    }

    $row = $this->db
      ->select('COUNT(*) AS record_count, COALESCE(SUM(amount_due), 0) AS total_due, COALESCE(SUM(amount_paid), 0) AS total_paid', false)
      ->where('settingsID', $settingsID)
      ->get('company_billing_records')
      ->row();

    $totalDue = round((float) ($row->total_due ?? 0), 2);
    $totalPaid = round((float) ($row->total_paid ?? 0), 2);

    return array(
      'record_count' => (int) ($row->record_count ?? 0),
      'total_due' => $totalDue,
      'total_paid' => $totalPaid,
      'outstanding_balance' => max(0, round($totalDue - $totalPaid, 2)),
    );
  }

  private function _getCompanyLatestBillingRecord($settingsID)
  {
    $settingsID = (int) $settingsID;
    if ($settingsID <= 0 || !$this->db->table_exists('company_billing_records')) {
      return null;
    }

    return $this->db
      ->where('settingsID', $settingsID)
      ->order_by('billing_month', 'DESC')
      ->order_by('billing_id', 'DESC')
      ->limit(1)
      ->get('company_billing_records')
      ->row();
  }

  private function _getCompanyEarliestBillingRecord($settingsID)
  {
    $settingsID = (int) $settingsID;
    if ($settingsID <= 0 || !$this->db->table_exists('company_billing_records')) {
      return null;
    }

    return $this->db
      ->where('settingsID', $settingsID)
      ->order_by('billing_month', 'ASC')
      ->order_by('billing_id', 'ASC')
      ->limit(1)
      ->get('company_billing_records')
      ->row();
  }

  private function _getCompanyBillingRecordByMonth($settingsID, $billingMonth)
  {
    $settingsID = (int) $settingsID;
    $billingMonth = $this->_normalizeBillingMonthInput($billingMonth);

    if ($settingsID <= 0 || $billingMonth === '' || !$this->db->table_exists('company_billing_records')) {
      return null;
    }

    return $this->db
      ->where('settingsID', $settingsID)
      ->where('billing_month', $billingMonth)
      ->limit(1)
      ->get('company_billing_records')
      ->row();
  }

  private function _getNextCompanyBillingMonth($settingsID)
  {
    $latestRecord = $this->_getCompanyLatestBillingRecord($settingsID);
    if (!$latestRecord || empty($latestRecord->billing_month)) {
      return date('Y-m-01');
    }

    $nextTimestamp = strtotime((string) $latestRecord->billing_month . ' +1 month');
    if ($nextTimestamp === false) {
      return date('Y-m-01');
    }

    return date('Y-m-01', $nextTimestamp);
  }

  private function _createCompanyBillingRecord($company, $billingMonth, array $overrides = array())
  {
    $settingsID = (int) ($company->settingsID ?? 0);
    $billingMonth = $this->_normalizeBillingMonthInput($billingMonth);

    if ($settingsID <= 0 || $billingMonth === '') {
      return array('success' => false, 'existing' => false, 'billing_id' => 0, 'message' => 'Invalid billing data');
    }

    $existingRecord = $this->_getCompanyBillingRecordByMonth($settingsID, $billingMonth);
    if ($existingRecord) {
      return array(
        'success' => true,
        'existing' => true,
        'billing_id' => (int) ($existingRecord->billing_id ?? 0),
        'record' => $existingRecord,
      );
    }

    $billingMode = $this->_normalizeCompanyBillingMode($overrides['billing_mode'] ?? ($company->billing_mode ?? 'company'));
    $ratePerMonth = round(max(0, (float) ($overrides['rate_per_month'] ?? ($company->monthly_rate ?? 0))), 2);

    if (array_key_exists('billable_units', $overrides)) {
      $billableUnits = max(0, (int) $overrides['billable_units']);
    } else {
      $billableUnits = $billingMode === 'individual'
        ? $this->_getCompanyBillableUserCount($settingsID)
        : ($billingMode === 'free' ? 0 : 1);
    }

    if ($billingMode === 'company' && $billableUnits <= 0) {
      $billableUnits = 1;
    }

    $amountDue = $this->_calculateCompanyBillingAmount($billingMode, $ratePerMonth, $billableUnits);
    $status = $this->_resolveCompanyBillingStatus($amountDue, 0);
    $notes = trim((string) ($overrides['notes'] ?? ''));

    if ($notes === '' && !empty($overrides['is_auto_generated'])) {
      $notes = 'Recurring monthly billing generated automatically from company setup.';
    }

    $timestamp = date('Y-m-d H:i:s');
    $result = $this->db->insert('company_billing_records', array(
      'settingsID' => $settingsID,
      'billing_month' => $billingMonth,
      'billing_mode' => $billingMode,
      'billable_units' => $billableUnits,
      'rate_per_month' => $ratePerMonth,
      'amount_due' => $amountDue,
      'amount_paid' => 0,
      'status' => $status,
      'notes' => $notes,
      'created_at' => $timestamp,
      'updated_at' => $timestamp,
      'paid_at' => $status === 'paid' ? $timestamp : null,
    ));

    return array(
      'success' => (bool) $result,
      'existing' => false,
      'billing_id' => $result ? (int) $this->db->insert_id() : 0,
      'message' => $result ? 'Billing entry created successfully' : 'Failed to create billing entry',
    );
  }

  private function _syncCompanyRecurringBillingRecords($company, $throughMonth = null)
  {
    $settingsID = (int) ($company->settingsID ?? 0);
    if ($settingsID <= 0 || !$this->db->table_exists('company_billing_records')) {
      return 0;
    }

    $billingMode = $this->_normalizeCompanyBillingMode($company->billing_mode ?? 'company');
    $monthlyRate = round(max(0, (float) ($company->monthly_rate ?? 0)), 2);
    if ($billingMode !== 'free' && $monthlyRate <= 0) {
      return 0;
    }

    $throughMonth = $this->_normalizeBillingMonthInput($throughMonth ?: date('Y-m-01'));
    if ($throughMonth === '') {
      $throughMonth = date('Y-m-01');
    }

    $earliestRecord = $this->_getCompanyEarliestBillingRecord($settingsID);
    $startMonth = $earliestRecord && !empty($earliestRecord->billing_month)
      ? $this->_normalizeBillingMonthInput($earliestRecord->billing_month)
      : $throughMonth;

    if ($startMonth === '') {
      $startMonth = $throughMonth;
    }

    $startDate = DateTime::createFromFormat('Y-m-d', $startMonth);
    $endDate = DateTime::createFromFormat('Y-m-d', $throughMonth);
    if (!$startDate || !$endDate || $startDate > $endDate) {
      return 0;
    }

    $createdCount = 0;
    $cursor = clone $startDate;

    while ($cursor <= $endDate) {
      $billingMonth = $cursor->format('Y-m-01');
      $result = $this->_createCompanyBillingRecord($company, $billingMonth, array(
        'is_auto_generated' => true,
      ));

      if (!empty($result['success']) && empty($result['existing'])) {
        $createdCount++;
      }

      $cursor->modify('+1 month');
    }

    return $createdCount;
  }

  private function _buildCompanyBillingSummary($company)
  {
    $settingsID = (int) ($company->settingsID ?? 0);
    $billingMode = $this->_normalizeCompanyBillingMode($company->billing_mode ?? 'company');
    $monthlyRate = round(max(0, (float) ($company->monthly_rate ?? 0)), 2);
    $billableUsers = $this->_getCompanyBillableUserCount($settingsID);
    $billableUnits = $billingMode === 'individual' ? $billableUsers : ($billingMode === 'free' ? 0 : 1);
    $expectedMonthlyCharge = $this->_calculateCompanyBillingAmount($billingMode, $monthlyRate, $billableUnits);
    $aggregates = $this->_getCompanyBillingAggregates($settingsID);
    $latestRecord = $this->_getCompanyLatestBillingRecord($settingsID);
    $nextBillingMonth = $this->_getNextCompanyBillingMonth($settingsID);

    return array(
      'billing_mode' => $billingMode,
      'billing_mode_label' => $this->_getCompanyBillingModeLabel($billingMode),
      'monthly_rate' => $monthlyRate,
      'billable_users' => $billableUsers,
      'billable_units' => $billableUnits,
      'expected_monthly_charge' => $expectedMonthlyCharge,
      'record_count' => (int) $aggregates['record_count'],
      'total_due' => round((float) $aggregates['total_due'], 2),
      'total_paid' => round((float) $aggregates['total_paid'], 2),
      'outstanding_balance' => round((float) $aggregates['outstanding_balance'], 2),
      'last_record' => $latestRecord,
      'next_billing_month' => $nextBillingMonth,
    );
  }

  private function _getCompanyFeatureCatalog()
  {
    return array(
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
      // Legacy/manual feature keys kept for compatibility with existing tooling.
      'knowledge_base' => 'Knowledge Base',
      'reports' => 'Reports & Analytics',
      'time_tracking' => 'Time Tracking',
      'inventory' => 'Inventory Management',
      'hr' => 'HR Module',
      'finance' => 'Finance Module',
    );
  }

  private function _getCompanyFeaturePackages()
  {
    return array(
      1 => array(
        'title' => 'Package 1: Business Operations Suite',
        'description' => 'A complete solution for managing operations, projects, and customer transactions.',
        'features' => array('invoice', 'deliveries', 'expenses', 'job_order', 'projects', 'support', 'tasks', 'notes', 'calendar'),
      ),
      2 => array(
        'title' => 'Package 2: Task Management Suite',
        'description' => 'Designed for teams that need task tracking and scheduling.',
        'features' => array('tasks', 'notes', 'calendar'),
      ),
      3 => array(
        'title' => 'Package 3: Payroll Management Suite',
        'description' => 'Designed to streamline employee compensation and payroll processing.',
        'features' => array('payroll', 'employee_payroll', 'salary_computation', 'payroll_reports', 'notes'),
      ),
      4 => array(
        'title' => 'Package 4: POS',
        'description' => 'Designed for retail, cashiering, and point-of-sale account access.',
        'features' => array('pos', 'notes'),
      ),
    );
  }

  private function _replaceCompanyFeatures($settingsID, array $features)
  {
    $settingsID = (int) $settingsID;
    if ($settingsID <= 0) {
      return false;
    }

    $catalog = $this->_getCompanyFeatureCatalog();
    $validatedFeatures = array();

    foreach ($features as $featureKey) {
      $featureKey = trim((string) $featureKey);
      if ($featureKey !== '' && isset($catalog[$featureKey])) {
        $validatedFeatures[$featureKey] = $catalog[$featureKey];
      }
    }

    $this->db->where('settingsID', $settingsID);
    $this->db->delete('company_features');

    $timestamp = date('Y-m-d H:i:s');
    foreach ($validatedFeatures as $featureKey => $featureName) {
      $this->db->insert('company_features', array(
        'settingsID' => $settingsID,
        'feature_key' => $featureKey,
        'feature_name' => $featureName,
        'is_enabled' => 1,
        'created_at' => $timestamp,
      ));
    }

    return true;
  }

  private function _normalizeCompanyPackageIds($packageIdsInput)
  {
    $packageDefinitions = $this->_getCompanyFeaturePackages();
    $normalizedPackageIds = array();

    foreach ((array) $packageIdsInput as $packageId) {
      $packageId = (int) $packageId;
      if ($packageId > 0 && isset($packageDefinitions[$packageId])) {
        $normalizedPackageIds[$packageId] = $packageId;
      }
    }

    $normalizedPackageIds = array_values($normalizedPackageIds);
    sort($normalizedPackageIds, SORT_NUMERIC);

    return $normalizedPackageIds;
  }

  private function _getCompanySelectedPackageIds($company)
  {
    if (!$company) {
      return array();
    }

    $rawPackageIds = trim((string) ($company->package_ids ?? ''));
    if ($rawPackageIds !== '') {
      return $this->_normalizeCompanyPackageIds(explode(',', $rawPackageIds));
    }

    $legacyPackageId = isset($company->package_id) ? (int) $company->package_id : 0;
    if ($legacyPackageId > 0) {
      return $this->_normalizeCompanyPackageIds(array($legacyPackageId));
    }

    return array();
  }

  private function _getCombinedPackageFeatures(array $packageIds)
  {
    $packageDefinitions = $this->_getCompanyFeaturePackages();
    $combinedFeatures = array();

    foreach ($this->_normalizeCompanyPackageIds($packageIds) as $packageId) {
      foreach ((array) ($packageDefinitions[$packageId]['features'] ?? array()) as $featureKey) {
        $combinedFeatures[$featureKey] = $featureKey;
      }
    }

    return array_values($combinedFeatures);
  }

  private function _loadCurrentCompanyFeatureAccess()
  {
    if ($this->currentCompanyFeatureAccessLoaded) {
      return;
    }

    $this->currentCompanyFeatureAccessLoaded = true;
    $this->currentCompanyFeatureKeys = array();
    $this->currentCompanyFeatureRestrictionsActive = false;

    $settingsID = (int) $this->session->userdata('settingsID');
    $level = strtolower(trim((string) $this->session->userdata('level')));

    if ($settingsID <= 0 || in_array($level, array('super admin', 'system administrator', 'client', 'manager', 'cashier', 'pos admin', 'pos staff', 'student'), true)) {
      return;
    }

    if (!$this->db->table_exists('company_features')) {
      return;
    }

    $company = $this->db
      ->select('settingsID, package_id, package_ids')
      ->where('settingsID', $settingsID)
      ->limit(1)
      ->get('pos_settings')
      ->row();

    $selectedPackageIds = $this->_getCompanySelectedPackageIds($company);
    if (!empty($selectedPackageIds)) {
      $combinedPackageFeatures = $this->_getCombinedPackageFeatures($selectedPackageIds);
      if (!empty($combinedPackageFeatures)) {
        $featureRows = $this->db
          ->select('feature_key')
          ->where('settingsID', $settingsID)
          ->where('is_enabled', 1)
          ->get('company_features')
          ->result();

        $savedFeatureKeys = array();
        foreach ($featureRows as $featureRow) {
          $savedFeatureKey = trim((string) ($featureRow->feature_key ?? ''));
          if ($savedFeatureKey !== '') {
            $savedFeatureKeys[] = $savedFeatureKey;
          }
        }

        $savedFeatureKeys = array_values(array_unique($savedFeatureKeys));
        sort($savedFeatureKeys, SORT_STRING);
        $normalizedCombinedFeatures = array_values(array_unique($combinedPackageFeatures));
        sort($normalizedCombinedFeatures, SORT_STRING);

        if ($savedFeatureKeys !== $normalizedCombinedFeatures) {
          $this->_replaceCompanyFeatures($settingsID, $normalizedCombinedFeatures);
        }

        $this->currentCompanyFeatureRestrictionsActive = true;
        $this->currentCompanyFeatureKeys = $normalizedCombinedFeatures;
        return;
      }
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

    $this->currentCompanyFeatureRestrictionsActive = true;

    foreach ($featureRows as $featureRow) {
      $featureKey = trim((string) ($featureRow->feature_key ?? ''));
      if ($featureKey !== '') {
        $this->currentCompanyFeatureKeys[] = $featureKey;
      }
    }

    $this->currentCompanyFeatureKeys = array_values(array_unique($this->currentCompanyFeatureKeys));
  }

  private function _companyHasFeature($featureKeys)
  {
    if ($this->_is_system_admin_user()) {
      return true;
    }

    $this->_loadCurrentCompanyFeatureAccess();

    if (!$this->currentCompanyFeatureRestrictionsActive) {
      return true;
    }

    foreach ((array) $featureKeys as $featureKey) {
      $featureKey = trim((string) $featureKey);
      if ($featureKey !== '' && in_array($featureKey, $this->currentCompanyFeatureKeys, true)) {
        return true;
      }
    }

    return false;
  }

  private function _getFeatureAccessMethodMap()
  {
    return array(
      'invlist' => array('invoice'),
      'invoiceentry' => array('invoice'),
      'invoicestatusreport' => array('invoice'),
      'recurringinvoices' => array('invoice'),
      'paymentlist' => array('invoice'),
      'revenuereports' => array('invoice'),
      'yearlyreport' => array('invoice'),
      'taxsummaryreport' => array('invoice'),
      'accountingreports' => array('invoice'),
      'paymentswithtax' => array('invoice'),
      'unifiedpayment' => array('invoice'),
      'customerhistory' => array('invoice', 'deliveries', 'projects', 'support'),
      'cliententry' => array('invoice', 'deliveries', 'projects', 'support'),
      'clientlist' => array('invoice', 'deliveries', 'projects', 'support'),
      'topclientsreport' => array('invoice', 'deliveries', 'projects', 'support'),
      'jolist' => array('job_order'),
      'expensesrange' => array('expenses'),
      'expensesrangedata' => array('expenses'),
      'todaysexpenses' => array('expenses'),
      'deleteexpense' => array('expenses'),
      'expenseslist' => array('expenses'),
      'getyearlyexpensedetails' => array('expenses'),
      'exportyearlyexpenses' => array('expenses'),
      'expensesreport' => array('expenses'),
      'addexpenses' => array('expenses'),
      'downloadexpensetemplate' => array('expenses'),
      'bulkuploadexpenses' => array('expenses'),
      'updateexpenses' => array('expenses'),
      'printexpense' => array('expenses'),
      'customerdeliverylist' => array('deliveries'),
      'newcustomerdelivery' => array('deliveries'),
      'savecustomerdelivery' => array('deliveries'),
      'viewcustomerdelivery' => array('deliveries'),
      'updatecustomerdelivery' => array('deliveries'),
      'deletecustomerdelivery' => array('deliveries'),
      'projectlist' => array('projects'),
      'addproject' => array('projects'),
      'updateproject' => array('projects'),
      'projectdeploymentstatus' => array('projects'),
      'saveprojectdeploymentstatus' => array('projects'),
      'deleteproject' => array('projects'),
      'projectaddtask' => array('tasks'),
      'employeetask' => array('tasks'),
      'employeetaskdata2' => array('tasks'),
      'accomplishments' => array('tasks'),
      'employeeaccomplishment' => array('tasks'),
      'employeeaccomplishmentdata' => array('tasks'),
      'accomplishmentstaff' => array('tasks'),
      'todayaccomplishments' => array('tasks'),
      'ranking' => array('tasks'),
      'bulkuploadtasks' => array('tasks'),
      'downloadtaskbulktemplate' => array('tasks'),
      'updatetask' => array('tasks'),
      'gettaskchecklist' => array('tasks'),
      'savetaskchecklist' => array('tasks'),
      'deletetask' => array('tasks'),
      'addtasknote' => array('tasks'),
      'savetaskcomment' => array('tasks'),
      'taskstat' => array('tasks'),
      'forwardtask' => array('tasks'),
      'taskperproject' => array('tasks'),
      'bulkcloseprojecttasks' => array('tasks'),
      'annualgoals' => array('tasks'),
      'notelist' => array('notes'),
      'updatenote' => array('notes'),
      'deletenote' => array('notes'),
      'supportdashboard' => array('support'),
      'supportissues' => array('support'),
      'supportissueview' => array('support'),
      'cancelledticketlogs' => array('support'),
      'addsupportissuecomment' => array('support'),
      'editsupportissuecomment' => array('support'),
      'deletesupportissuecomment' => array('support'),
      'forwardsupportissue' => array('support'),
      'tagsupportissueuser' => array('support'),
      'closesupportissue' => array('support'),
      'reopensupportissue' => array('support'),
      'posstaff' => array('pos'),
      'posadmin' => array('pos'),
      'posnewtransaction' => array('pos'),
      'posstoretransaction' => array('pos'),
      'postransactionhistory' => array('pos'),
      'posreturnsvoids' => array('pos'),
      'postransactiondetail' => array('pos'),
      'posrecordpayment' => array('pos'),
      'posvoidtransaction' => array('pos'),
      'posreports' => array('pos'),
      'posproductlist' => array('pos'),
      'posproducts' => array('pos'),
      'poscategorysettings' => array('pos'),
      'poscategorycreate' => array('pos'),
      'poscategoryupdate' => array('pos'),
      'poscategorydelete' => array('pos'),
      'poscreateproduct' => array('pos'),
      'poseditproduct' => array('pos'),
      'posupdateproduct' => array('pos'),
      'posdeleteproduct' => array('pos'),
      'posexpiringsoon' => array('pos'),
      'posexpiredproducts' => array('pos'),
      'posstocklevels' => array('pos'),
      'poslowstockitems' => array('pos'),
      'payrollmodule' => array('payroll', 'employee_payroll', 'salary_computation', 'payroll_reports'),
      'payrollsetup' => array('payroll', 'employee_payroll', 'salary_computation', 'payroll_reports'),
      'savepayrollprofile' => array('payroll', 'employee_payroll', 'salary_computation', 'payroll_reports'),
      'addpayrollloan' => array('payroll', 'employee_payroll', 'salary_computation', 'payroll_reports'),
      'addpayrollcashadvance' => array('payroll', 'employee_payroll', 'salary_computation', 'payroll_reports'),
      'generatepayroll' => array('payroll', 'employee_payroll', 'salary_computation', 'payroll_reports'),
      'payrollrun' => array('payroll', 'employee_payroll', 'salary_computation', 'payroll_reports'),
      'payrollruns' => array('payroll', 'employee_payroll', 'salary_computation', 'payroll_reports'),
      'payrollpayslip' => array('payroll', 'employee_payroll', 'salary_computation', 'payroll_reports'),
      'attendancelist' => array('payroll', 'employee_payroll', 'salary_computation', 'payroll_reports'),
      'employeelist' => array('payroll', 'employee_payroll', 'salary_computation', 'payroll_reports'),
      'empdtr' => array('payroll', 'employee_payroll', 'salary_computation', 'payroll_reports'),
      'mydtr' => array('payroll', 'employee_payroll', 'salary_computation', 'payroll_reports'),
    );
  }

  private function _denyUnavailableFeature()
  {
    $message = 'This feature is not included in your company package. Please contact your super admin.';
    $isAjaxRequest = strtolower((string) $this->input->server('HTTP_X_REQUESTED_WITH')) === 'xmlhttprequest';

    if ($isAjaxRequest) {
      header('Content-Type: application/json');
      echo json_encode(array('success' => false, 'message' => $message));
      exit;
    }

    show_error($message, 403, 'Feature Not Available');
    exit;
  }

  private function _enforceCompanyFeatureAccess()
  {
    $level = strtolower(trim((string) $this->session->userdata('level')));
    if (!in_array($level, array('admin', 'staff', 'account'), true)) {
      return;
    }

    $this->_loadCurrentCompanyFeatureAccess();
    if (!$this->currentCompanyFeatureRestrictionsActive) {
      return;
    }

    $method = strtolower((string) $this->router->fetch_method());
    $featureMap = $this->_getFeatureAccessMethodMap();

    if (!isset($featureMap[$method])) {
      return;
    }

    if (!$this->_companyHasFeature($featureMap[$method])) {
      $this->_denyUnavailableFeature();
    }
  }

  private function _normalizeKnowledgeBaseCategory($selectedCategory, $newCategory)
  {
    $selectedCategory = trim((string) $selectedCategory);
    $newCategory = trim((string) $newCategory);

    if ($selectedCategory === 'new') {
      return $newCategory;
    }

    if ($selectedCategory === '' && $newCategory !== '') {
      return $newCategory;
    }

    return $selectedCategory;
  }

  private function _syncKnowledgeBaseCategory($settingsID, $categoryName)
  {
    $settingsID = (int) $settingsID;
    $categoryName = trim((string) $categoryName);

    if ($settingsID <= 0 || $categoryName === '') {
      return;
    }

    $existingCategory = $this->Knowledge_base_model->get_category($settingsID, $categoryName);
    if ($existingCategory) {
      return;
    }

    $this->Knowledge_base_model->insert_category(array(
      'settingsID' => $settingsID,
      'name' => $categoryName,
      'created_at' => date('Y-m-d H:i:s'),
    ));
  }

  private function _handleKnowledgeBaseAttachmentUpload($fieldName = 'attachment')
  {
    $result = array(
      'path' => null,
      'name' => null,
      'error' => '',
    );

    if (!isset($_FILES[$fieldName]) || !is_array($_FILES[$fieldName])) {
      return $result;
    }

    $file = $_FILES[$fieldName];
    $uploadError = (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE);

    if ($uploadError === UPLOAD_ERR_NO_FILE || empty($file['name'])) {
      return $result;
    }

    if ($uploadError !== UPLOAD_ERR_OK) {
      switch ($uploadError) {
        case UPLOAD_ERR_INI_SIZE:
        case UPLOAD_ERR_FORM_SIZE:
          $result['error'] = 'PDF attachment exceeds the maximum allowed upload size.';
          break;
        default:
          $result['error'] = 'PDF attachment upload failed (error code ' . $uploadError . ').';
          break;
      }

      return $result;
    }

    $originalName = trim((string) ($file['name'] ?? ''));
    $fileExtension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
    $fileSize = (int) ($file['size'] ?? 0);

    if ($fileExtension !== 'pdf') {
      $result['error'] = 'Only PDF attachments are allowed.';
      return $result;
    }

    if ($fileSize <= 0) {
      $result['error'] = 'The uploaded PDF appears to be empty.';
      return $result;
    }

    if ($fileSize > (10 * 1024 * 1024)) {
      $result['error'] = 'PDF attachment exceeds the 10MB size limit.';
      return $result;
    }

    $mimeType = function_exists('mime_content_type') ? (string) @mime_content_type($file['tmp_name']) : '';
    if ($mimeType !== '' && !in_array($mimeType, array('application/pdf', 'application/x-pdf', 'application/octet-stream'), true)) {
      $result['error'] = 'The uploaded file is not recognized as a valid PDF.';
      return $result;
    }

    $uploadDir = FCPATH . 'uploads/knowledge_base/';
    if (!is_dir($uploadDir) && !@mkdir($uploadDir, 0755, true) && !is_dir($uploadDir)) {
      $result['error'] = 'Unable to create the knowledge base upload directory.';
      return $result;
    }

    $baseName = preg_replace('/[^A-Za-z0-9_-]+/', '_', pathinfo($originalName, PATHINFO_FILENAME));
    $baseName = trim((string) $baseName, '_-');
    if ($baseName === '') {
      $baseName = 'attachment';
    }

    $storedName = 'kb_' . date('Ymd_His') . '_' . substr(sha1(uniqid((string) mt_rand(), true)), 0, 10) . '_' . $baseName . '.pdf';
    $relativePath = 'uploads/knowledge_base/' . $storedName;
    $fullPath = FCPATH . $relativePath;

    if (!move_uploaded_file($file['tmp_name'], $fullPath)) {
      $result['error'] = 'Unable to save the uploaded PDF attachment.';
      return $result;
    }

    $result['path'] = $relativePath;
    $result['name'] = $originalName;

    return $result;
  }

  private function _deleteKnowledgeBaseAttachmentFile($attachmentPath)
  {
    $attachmentPath = ltrim(trim((string) $attachmentPath), '/');
    if ($attachmentPath === '' || strpos($attachmentPath, 'uploads/knowledge_base/') !== 0) {
      return;
    }

    $fullPath = FCPATH . $attachmentPath;
    if (is_file($fullPath)) {
      @unlink($fullPath);
    }
  }

  private function _ensurePosCategoryTable()
  {
    if (!$this->db->table_exists('pos_categories')) {
      $this->db->query("
        CREATE TABLE `pos_categories` (
          `id` int unsigned NOT NULL AUTO_INCREMENT,
          `name` varchar(120) NOT NULL,
          `settingsID` int unsigned NOT NULL DEFAULT 0,
          `createdAt` datetime DEFAULT CURRENT_TIMESTAMP,
          `updatedAt` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
          PRIMARY KEY (`id`),
          UNIQUE KEY `uniq_pos_categories_settings_name` (`settingsID`, `name`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
      ");
    }

    if (!$this->db->field_exists('settingsID', 'pos_categories')) {
      $this->db->query("ALTER TABLE `pos_categories` ADD COLUMN `settingsID` int unsigned NOT NULL DEFAULT 0 AFTER `name`");
    }
  }

  private function _ensureProjectsTaskDueDateColumn()
  {
    if (!$this->db->field_exists('dueDate', 'projects_task')) {
      $this->db->query("ALTER TABLE `projects_task` ADD COLUMN `dueDate` date DEFAULT NULL AFTER `reportedDate`");
    }

    $this->db->query("UPDATE `projects_task` SET `dueDate` = NULL WHERE `dueDate` IS NOT NULL AND (`dueDate` + 0) = 0");
  }

  private function _ensureProjectsTaskClientCommentColumn()
  {
    if (!$this->db->field_exists('client_comment', 'projects_task')) {
      $this->db->query("ALTER TABLE `projects_task` ADD COLUMN `client_comment` text DEFAULT NULL AFTER `attachment_link`");
    }
  }

  private function _ensureProjectsTaskPointsColumn()
  {
    if (!$this->db->field_exists('points', 'projects_task')) {
      $this->db->query("ALTER TABLE `projects_task` ADD COLUMN `points` INT UNSIGNED NOT NULL DEFAULT 1 AFTER `priority`");
    }
  }

  private function _ensureInvoiceRecurringTerminationDateColumn()
  {
    if (!$this->db->field_exists('recurringTerminationDate', 'invoice')) {
      $this->db->query("ALTER TABLE `invoice` ADD COLUMN `recurringTerminationDate` date DEFAULT NULL AFTER `recurringScheduleDate`");
    }

    $this->db->query("UPDATE `invoice` SET `recurringTerminationDate` = NULL WHERE `recurringTerminationDate` IS NOT NULL AND (`recurringTerminationDate` + 0) = 0");
  }

  private function _ensureInvoiceExpirationDateColumn()
  {
    if (!$this->db->field_exists('invoiceExpirationDate', 'invoice')) {
      $this->db->query("ALTER TABLE `invoice` ADD COLUMN `invoiceExpirationDate` date DEFAULT NULL AFTER `recurringTerminationDate`");
    }

    $this->db->query("UPDATE `invoice` SET `invoiceExpirationDate` = NULL WHERE `invoiceExpirationDate` IS NOT NULL AND (`invoiceExpirationDate` + 0) = 0");
  }

  private function _ensureInvoiceDueDateColumn()
  {
    if (!$this->db->field_exists('DueDate', 'invoice')) {
      $this->db->query("ALTER TABLE `invoice` ADD COLUMN `DueDate` date DEFAULT NULL AFTER `TransDate`");
    }

    $this->db->query("UPDATE `invoice` SET `DueDate` = NULL WHERE `DueDate` IS NOT NULL AND (`DueDate` + 0) = 0");
  }

  private function _ensureCoverageOptionColumn()
  {
    if (!$this->db->field_exists('coverageOption', 'invoice')) {
      $this->db->query("ALTER TABLE `invoice` ADD COLUMN `coverageOption` varchar(20) DEFAULT 'coming' AFTER `recurringFrequency`");
    }

    $this->db->query("
      UPDATE `invoice`
      SET `coverageOption` = 'coming'
      WHERE (`coverageOption` IS NULL OR TRIM(`coverageOption`) = '')
        AND (
          COALESCE(`recurringFrequency`, 'none') = 'none'
          OR COALESCE(`recurringTemplateID`, 0) = 0
        )
    ");

    // Generated recurring invoices should inherit the template coverage direction.
    $this->db->query("
      UPDATE `invoice` AS `generated`
      INNER JOIN `invoice` AS `template`
        ON `template`.`orderID` = `generated`.`recurringTemplateID`
       AND `template`.`settingsID` = `generated`.`settingsID`
      SET `generated`.`coverageOption` = COALESCE(NULLIF(TRIM(`template`.`coverageOption`), ''), 'coming')
      WHERE `generated`.`recurringTemplateID` IS NOT NULL
        AND `generated`.`recurringTemplateID` > 0
        AND (
          `generated`.`coverageOption` IS NULL
          OR TRIM(`generated`.`coverageOption`) = ''
          OR (
            COALESCE(NULLIF(TRIM(`template`.`coverageOption`), ''), 'coming') = 'previous'
            AND `generated`.`coverageOption` = 'coming'
          )
        )
    ");
  }

  private function _ensureTaskChecklistTable()
  {
    if (!$this->db->table_exists('task_checklist')) {
      $this->db->query("
        CREATE TABLE `task_checklist` (
          `checklistID` int unsigned NOT NULL AUTO_INCREMENT,
          `taskID` int NOT NULL,
          `itemDescription` varchar(255) NOT NULL,
          `status` varchar(50) NOT NULL DEFAULT 'Pending',
          `isCompleted` tinyint(1) NOT NULL DEFAULT 0,
          `completedAt` datetime DEFAULT NULL,
          `completedBy` varchar(100) DEFAULT NULL,
          `settingsID` int unsigned NOT NULL,
          `createdAt` datetime DEFAULT CURRENT_TIMESTAMP,
          `updatedAt` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
          PRIMARY KEY (`checklistID`),
          KEY `idx_task_checklist_taskID` (`taskID`),
          KEY `idx_task_checklist_settingsID` (`settingsID`),
          CONSTRAINT `fk_task_checklist_taskID` FOREIGN KEY (`taskID`) REFERENCES `projects_task` (`taskID`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
      ");
    }

    // Add status column if table exists but column doesn't
    if ($this->db->table_exists('task_checklist') && !$this->db->field_exists('status', 'task_checklist')) {
      $this->db->query("ALTER TABLE `task_checklist` ADD COLUMN `status` varchar(50) NOT NULL DEFAULT 'Pending' AFTER `itemDescription`");
    }
  }

  private function _ensureInvoiceAccessColumn()
  {
    if ($this->db->table_exists('customers')) {
      if (!$this->db->field_exists('invoice_access_enabled', 'customers')) {
        $this->db->query("ALTER TABLE `customers` ADD COLUMN `invoice_access_enabled` tinyint(1) NOT NULL DEFAULT 1 AFTER `portal_enabled`");
      }
    }
  }

  private function _ensureExpenseCategoryTable()
  {
    if (!$this->db->table_exists('expensescategory')) {
      $this->db->query("
        CREATE TABLE `expensescategory` (
          `categoryID` int unsigned NOT NULL AUTO_INCREMENT,
          `Category` varchar(120) NOT NULL,
          `settingsID` int unsigned NOT NULL DEFAULT 1,
          `createdAt` datetime DEFAULT CURRENT_TIMESTAMP,
          `updatedAt` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
          PRIMARY KEY (`categoryID`),
          UNIQUE KEY `uniq_expensescategory_settings_category` (`settingsID`, `Category`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
      ");
    }

    if (!$this->db->field_exists('settingsID', 'expensescategory')) {
      $this->db->query("ALTER TABLE `expensescategory` ADD COLUMN `settingsID` int unsigned NOT NULL DEFAULT 1 AFTER `Category`");
    }

    $settingsID = (int) ($this->session->userdata('settingsID') ?? 0);
    if ($settingsID <= 0) {
      return;
    }

    $seedCategories = array(
      'Courier Fee',
      'Meals and Snacks',
      'Office Maintenance',
      'Office Supplies',
      'Permits and Licenses',
      'Salaries and Wages',
      'Taxes',
      'Transportation',
      'Utilities',
      'Other',
    );

    $existingExpenseCategories = $this->db
      ->select('Category')
      ->distinct()
      ->from('expenses')
      ->where('settingsID', $settingsID)
      ->where("TRIM(COALESCE(Category, '')) <> ''", null, false)
      ->get()
      ->result();

    foreach ($existingExpenseCategories as $row) {
      $categoryName = trim((string) ($row->Category ?? ''));
      if ($categoryName !== '') {
        $seedCategories[] = $categoryName;
      }
    }

    $seedCategories = array_values(array_unique(array_filter(array_map(function ($category) {
      return trim((string) $category);
    }, $seedCategories))));

    foreach ($seedCategories as $categoryName) {
      $exists = $this->db
        ->select('categoryID')
        ->from('expensescategory')
        ->where('settingsID', $settingsID)
        ->where('Category', $categoryName)
        ->limit(1)
        ->get()
        ->row();

      if (!$exists) {
        $this->db->insert('expensescategory', array(
          'Category' => $categoryName,
          'settingsID' => $settingsID,
        ));
      }
    }
  }

  private function _ensureEmployeeEmailColumn()
  {
    if ($this->db->table_exists('employee') && !$this->db->field_exists('email', 'employee')) {
      $this->db->query("ALTER TABLE `employee` ADD COLUMN `email` varchar(100) DEFAULT NULL AFTER `lName`");
    }
  }

  private function _ensureUserSupportPermissionColumns()
  {
    if (!$this->db->table_exists('users')) {
      return;
    }

    if (!$this->db->field_exists('support_chat_view', 'users')) {
      $this->db->query("ALTER TABLE `users` ADD COLUMN `support_chat_view` tinyint(1) NOT NULL DEFAULT 1 AFTER `settingsID`");
    }

    if (!$this->db->field_exists('support_chat_reply', 'users')) {
      $this->db->query("ALTER TABLE `users` ADD COLUMN `support_chat_reply` tinyint(1) NOT NULL DEFAULT 1 AFTER `support_chat_view`");
    }
  }

  private function _ensureClientSupportTables()
  {
    $this->_dropSupportSettingsForeignKeys();

    if (!$this->db->table_exists('support_departments')) {
      $this->db->query("
        CREATE TABLE `support_departments` (
          `id` int unsigned NOT NULL AUTO_INCREMENT,
          `department_name` varchar(100) NOT NULL,
          `department_code` varchar(20) NOT NULL,
          `description` text DEFAULT NULL,
          `manager_id` int DEFAULT NULL,
          `email` varchar(100) DEFAULT NULL,
          `phone` varchar(20) DEFAULT NULL,
          `is_active` tinyint(1) NOT NULL DEFAULT 1,
          `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
          `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
          `settingsID` int NOT NULL DEFAULT 0,
          PRIMARY KEY (`id`),
          UNIQUE KEY `uniq_support_departments_settings_code` (`settingsID`, `department_code`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
      ");
    }

    if (!$this->db->table_exists('employee_departments')) {
      $this->db->query("
        CREATE TABLE `employee_departments` (
          `id` int unsigned NOT NULL AUTO_INCREMENT,
          `employee_id` int NOT NULL,
          `department_id` int NOT NULL,
          `role` varchar(50) NOT NULL DEFAULT 'member',
          `is_active` tinyint(1) NOT NULL DEFAULT 1,
          `assigned_at` datetime DEFAULT CURRENT_TIMESTAMP,
          `assigned_by` int DEFAULT NULL,
          `settingsID` int NOT NULL DEFAULT 0,
          PRIMARY KEY (`id`),
          UNIQUE KEY `uniq_employee_department_settings` (`employee_id`, `department_id`, `settingsID`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
      ");
    }

    if (!$this->db->table_exists('support_issues')) {
      $this->db->query("
        CREATE TABLE `support_issues` (
          `id` int unsigned NOT NULL AUTO_INCREMENT,
          `ticket_number` varchar(30) NOT NULL,
          `customer_id` varchar(50) DEFAULT NULL,
          `customer_name` varchar(100) NOT NULL,
          `customer_email` varchar(100) NOT NULL,
          `customer_phone` varchar(30) DEFAULT NULL,
          `department_id` int NOT NULL,
          `project_id` int DEFAULT NULL,
          `task_id` int DEFAULT NULL,
          `assigned_employee_id` int DEFAULT NULL,
          `title` varchar(200) NOT NULL,
          `description` text NOT NULL,
          `category` varchar(50) DEFAULT NULL,
          `priority` varchar(20) NOT NULL DEFAULT 'medium',
          `status` varchar(30) NOT NULL DEFAULT 'open',
          `resolution_details` text DEFAULT NULL,
          `resolution_date` datetime DEFAULT NULL,
          `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
          `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
          `due_date` datetime DEFAULT NULL,
          `resolved_by` int DEFAULT NULL,
          `settingsID` int NOT NULL DEFAULT 0,
          PRIMARY KEY (`id`),
          UNIQUE KEY `uniq_support_ticket_number` (`ticket_number`),
          KEY `idx_support_issues_settings_status` (`settingsID`, `status`),
          KEY `idx_support_issues_department` (`department_id`),
          KEY `idx_support_issues_project` (`project_id`),
          KEY `idx_support_issues_task` (`task_id`),
          KEY `idx_support_issues_assigned` (`assigned_employee_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
      ");
    }

    if ($this->db->table_exists('support_issues')) {
      if (!$this->db->field_exists('project_id', 'support_issues')) {
        $this->db->query("ALTER TABLE `support_issues` ADD COLUMN `project_id` int DEFAULT NULL AFTER `department_id`");
      }
      if (!$this->db->field_exists('task_id', 'support_issues')) {
        $this->db->query("ALTER TABLE `support_issues` ADD COLUMN `task_id` int DEFAULT NULL AFTER `project_id`");
      }
      if (!$this->db->field_exists('reference_link', 'support_issues')) {
        $this->db->query("ALTER TABLE `support_issues` ADD COLUMN `reference_link` varchar(255) DEFAULT NULL AFTER `description`");
      }
      if (!$this->db->field_exists('client_reply_required', 'support_issues')) {
        $this->db->query("ALTER TABLE `support_issues` ADD COLUMN `client_reply_required` tinyint(1) NOT NULL DEFAULT 0 AFTER `status`");
      }
    }

    if (!$this->db->table_exists('support_issue_comments')) {
      $this->db->query("
        CREATE TABLE `support_issue_comments` (
          `id` int unsigned NOT NULL AUTO_INCREMENT,
          `issue_id` int NOT NULL,
          `employee_id` int DEFAULT NULL,
          `customer_comment` tinyint(1) NOT NULL DEFAULT 0,
          `comment` text NOT NULL,
          `internal_note` tinyint(1) NOT NULL DEFAULT 0,
          `attachment_path` varchar(255) DEFAULT NULL,
          `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
          `settingsID` int NOT NULL DEFAULT 0,
          PRIMARY KEY (`id`),
          KEY `idx_support_comments_issue` (`issue_id`),
          KEY `idx_support_comments_employee` (`employee_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
      ");
    }

    if (!$this->db->table_exists('support_notifications')) {
      $this->db->query("
        CREATE TABLE `support_notifications` (
          `id` int unsigned NOT NULL AUTO_INCREMENT,
          `user_id` int NOT NULL,
          `actor_id` int DEFAULT NULL,
          `issue_id` int DEFAULT NULL,
          `department_id` int DEFAULT NULL,
          `notification_type` varchar(30) NOT NULL,
          `title` varchar(200) NOT NULL,
          `message` text NOT NULL,
          `is_read` tinyint(1) NOT NULL DEFAULT 0,
          `action_required` tinyint(1) NOT NULL DEFAULT 0,
          `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
          `read_at` datetime DEFAULT NULL,
          `expires_at` datetime DEFAULT NULL,
          `settingsID` int NOT NULL DEFAULT 0,
          PRIMARY KEY (`id`),
          KEY `idx_support_notifications_user` (`user_id`, `is_read`),
          KEY `idx_support_notifications_issue` (`issue_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
      ");
    }

    if (!$this->db->field_exists('actor_id', 'support_notifications')) {
      $this->db->query("ALTER TABLE `support_notifications` ADD COLUMN `actor_id` int DEFAULT NULL AFTER `user_id`");
    }

    if (!$this->db->table_exists('support_issue_attachments')) {
      $this->db->query("
        CREATE TABLE `support_issue_attachments` (
          `id` int unsigned NOT NULL AUTO_INCREMENT,
          `issue_id` int NOT NULL,
          `comment_id` int DEFAULT NULL,
          `file_name` varchar(255) NOT NULL,
          `file_path` varchar(255) NOT NULL,
          `file_size` int DEFAULT NULL,
          `mime_type` varchar(120) DEFAULT NULL,
          `uploaded_by_type` varchar(20) NOT NULL DEFAULT 'client',
          `uploaded_by` int DEFAULT NULL,
          `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
          `settingsID` int NOT NULL DEFAULT 0,
          PRIMARY KEY (`id`),
          KEY `idx_support_issue_attachments_issue` (`issue_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
      ");
    }

    if (!$this->db->table_exists('support_issue_cancel_logs')) {
      $this->db->query("
        CREATE TABLE `support_issue_cancel_logs` (
          `id` int unsigned NOT NULL AUTO_INCREMENT,
          `issue_id` int NOT NULL,
          `customer_id` varchar(50) DEFAULT NULL,
          `cancelled_by_user_id` int DEFAULT NULL,
          `cancel_note` text DEFAULT NULL,
          `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
          `settingsID` int NOT NULL DEFAULT 0,
          PRIMARY KEY (`id`),
          UNIQUE KEY `uniq_support_issue_cancel_logs_issue_settings` (`issue_id`, `settingsID`),
          KEY `idx_support_issue_cancel_logs_customer` (`customer_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
      ");
    }
  }

  private function _dropSupportSettingsForeignKeys()
  {
    $supportTables = array(
      'support_departments',
      'employee_departments',
      'support_issues',
      'support_issue_comments',
      'support_notifications',
      'support_issue_history',
      'support_sla_tracking',
    );

    foreach ($supportTables as $tableName) {
      if (!$this->db->table_exists($tableName)) {
        continue;
      }

      $query = $this->db->query("
        SELECT CONSTRAINT_NAME
        FROM information_schema.KEY_COLUMN_USAGE
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = ?
          AND COLUMN_NAME = 'settingsID'
          AND REFERENCED_TABLE_NAME = 'settings'
      ", array($tableName));

      foreach ($query->result() as $row) {
        $constraintName = trim((string) ($row->CONSTRAINT_NAME ?? ''));
        if ($constraintName === '') {
          continue;
        }

        $this->db->query("ALTER TABLE `" . $tableName . "` DROP FOREIGN KEY `" . $constraintName . "`");
      }
    }
  }

  private function _autoGenerateRecurringInvoicesFromWeb()
  {
    $level = strtolower(trim((string) $this->session->userdata('level')));
    if (!in_array($level, array('admin', 'staff'), true)) {
      return;
    }

    $settingsID = (int) ($this->session->userdata('settingsID') ?? 0);
    if ($settingsID <= 0) {
      return;
    }

    $method = strtolower((string) $this->router->fetch_method());
    if (in_array($method, array('invlist', 'revenuereports', 'recurringinvoices', 'runrecurringinvoicegenerator'), true)) {
      return;
    }

    $sessionKey = 'recurring_generator_last_run_' . $settingsID;
    $lastRunAt = (int) ($this->session->userdata($sessionKey) ?? 0);
    $now = time();

    if ($lastRunAt > 0 && ($now - $lastRunAt) < 300) {
      return;
    }

    $this->_generateRecurringInvoices($settingsID);
    $this->_markRecurringInvoiceGeneratorRun($settingsID, $now);
  }

  private function _markRecurringInvoiceGeneratorRun($settingsID, $timestamp = null)
  {
    $settingsID = (int) $settingsID;
    if ($settingsID <= 0) {
      return;
    }

    $this->session->set_userdata('recurring_generator_last_run_' . $settingsID, (int) ($timestamp ?? time()));
  }

  private function _is_admin_user()
  {
    return strtolower(trim((string) $this->session->userdata('level'))) === 'admin';
  }

  private function _is_system_admin_user()
  {
    return in_array(strtolower(trim((string) $this->session->userdata('level'))), array('super admin', 'system administrator'), true);
  }

  private function _can_edit_employee($empID)
  {
    if ($this->_is_admin_user()) {
      return true;
    }

    $settingsID = (int) $this->session->userdata('settingsID');
    $sessionEmail = strtolower(trim((string) $this->session->userdata('username')));

    $employee = $this->db
      ->where('empID', (int) $empID)
      ->where('settingsID', $settingsID)
      ->get('employee')
      ->row();

    if (!$employee) {
      return false;
    }

    $empEmail = strtolower(trim((string) ($employee->email ?? '')));
    return $empEmail !== '' && $empEmail === $sessionEmail;
  }

  private function _is_staff_user()
  {
    return in_array(strtolower(trim((string) $this->session->userdata('level'))), array('staff', 'encoder'), true);
  }

  private function _is_client_user()
  {
    return strtolower(trim((string) $this->session->userdata('level'))) === 'client';
  }

  private function _resolveInternalPageReturnPath($value, $default = 'Page/payrollModule')
  {
    $value = trim((string) $value);
    if ($value === '') {
      return $default;
    }

    if (preg_match('/^https?:\/\//i', $value) || strpos($value, '//') === 0) {
      return $default;
    }

    if (strpos($value, 'Page/') === 0) {
      return $value;
    }

    return $default;
  }

  private function _can_current_staff_access_invoice($invoice)
  {
    if (!$this->_is_staff_user()) {
      return true;
    }

    $currentAliases = array_map('strtolower', $this->_currentUserRecordAliases());
    $invoiceBy = trim((string) (is_object($invoice) ? ($invoice->invoiceBy ?? '') : ($invoice['invoiceBy'] ?? '')));

    // Get invoice balance to determine if it's paid or unpaid
    $balance = is_object($invoice) ? (float) ($invoice->Balance ?? 0) : (float) ($invoice['Balance'] ?? 0);

    // Allow access to all unpaid invoices (for payment collection)
    if ($balance > 0) {
      return true;
    }

    // For paid invoices, only allow access if they created it
    return $invoiceBy !== '' && in_array(strtolower($invoiceBy), $currentAliases, true);
  }

  private function _current_client_cust_id()
  {
    return trim((string) $this->session->userdata('client_cust_id'));
  }

  private function _load_current_client()
  {
    if (!$this->_is_client_user()) {
      return null;
    }

    $settingsID = (int) ($this->session->userdata('settingsID') ?? 0);
    $custID = $this->_current_client_cust_id();
    if ($settingsID <= 0 || $custID === '') {
      return null;
    }

    return $this->CashModel->getClientByCustID($settingsID, $custID);
  }

  private function _current_client_matches($custID = '', $customer = '')
  {
    if (!$this->_is_client_user()) {
      return true;
    }

    $currentCustID = $this->_current_client_cust_id();
    $currentCustomer = trim((string) $this->session->userdata('client_name'));
    $custID = trim((string) $custID);
    $customer = trim((string) $customer);

    if ($currentCustID !== '' && $custID !== '') {
      return $currentCustID === $custID;
    }

    return $currentCustomer !== '' && $customer !== '' && strcasecmp($currentCustomer, $customer) === 0;
  }

  private function _normalize_client_portal_enabled($value)
  {
    $value = strtolower(trim((string) $value));
    return in_array($value, array('1', 'true', 'yes', 'on', 'enabled'), true) ? 1 : 0;
  }

  private function _client_portal_email_in_use($settingsID, $clientEmail, $ignoreCustID = '')
  {
    $clientEmail = trim((string) $clientEmail);
    if ($clientEmail === '') {
      return false;
    }

    $this->db->from('customers');
    $this->db->where('settingsID', $settingsID);
    $this->db->where('client_email', $clientEmail);
    if ($ignoreCustID !== '') {
      $this->db->where('CustID !=', $ignoreCustID);
    }

    return $this->db->count_all_results() > 0;
  }

  private function _find_client_portal_user($settingsID, array $identifiers = array())
  {
    $settingsID = (int) $settingsID;
    $identifiers = array_values(array_unique(array_filter(array_map('trim', $identifiers), function ($value) {
      return $value !== '';
    })));

    if ($settingsID <= 0 || empty($identifiers)) {
      return null;
    }

    return $this->db
      ->from('users')
      ->where('settingsID', $settingsID)
      ->where('position', 'Client')
      ->group_start()
      ->where_in('username', $identifiers)
      ->or_where_in('email', $identifiers)
      ->group_end()
      ->order_by('user_id', 'asc')
      ->limit(1)
      ->get()
      ->row();
  }

  private function _client_user_username_in_use($settingsID, $username, $ignoreUserId = 0)
  {
    $settingsID = (int) $settingsID;
    $username = trim((string) $username);
    $ignoreUserId = (int) $ignoreUserId;

    if ($settingsID <= 0 || $username === '') {
      return false;
    }

    $this->db->from('users');
    $this->db->where('settingsID', $settingsID);
    $this->db->where('username', $username);
    if ($ignoreUserId > 0) {
      $this->db->where('user_id !=', $ignoreUserId);
    }

    return $this->db->count_all_results() > 0;
  }

  private function _split_person_name($fullName)
  {
    $fullName = preg_replace('/\s+/', ' ', trim((string) $fullName));
    if ($fullName === '') {
      return array('fName' => '', 'mName' => '', 'lName' => '');
    }

    $parts = explode(' ', $fullName);
    $count = count($parts);

    if ($count === 1) {
      return array(
        'fName' => $parts[0],
        'mName' => '',
        'lName' => '',
      );
    }

    if ($count === 2) {
      return array(
        'fName' => $parts[0],
        'mName' => '',
        'lName' => $parts[1],
      );
    }

    $firstName = array_shift($parts);
    $lastName = array_pop($parts);

    return array(
      'fName' => $firstName,
      'mName' => implode(' ', $parts),
      'lName' => $lastName,
    );
  }

  private function _sync_client_portal_user_account($settingsID, array $customerData, $plainPassword = '', $previousClientEmail = '')
  {
    $settingsID = (int) $settingsID;
    $custID = trim((string) ($customerData['CustID'] ?? ''));
    $clientEmail = trim((string) ($customerData['client_email'] ?? ''));
    $portalEnabled = !empty($customerData['portal_enabled']);
    $existingUser = $this->_find_client_portal_user($settingsID, array($clientEmail, $previousClientEmail, $custID));

    if (!$portalEnabled || $clientEmail === '') {
      if ($existingUser) {
        $this->db->where('user_id', (int) $existingUser->user_id);
        if (!$this->db->delete('users')) {
          return array(false, 'Client portal account could not be removed from the users table.');
        }
      }

      return array(true, '');
    }

    $ignoreUserId = !empty($existingUser->user_id) ? (int) $existingUser->user_id : 0;
    if ($this->_client_user_username_in_use($settingsID, $clientEmail, $ignoreUserId)) {
      return array(false, 'Client email is already used by another user account.');
    }

    // Check if username/email exists globally in users table
    $existingGlobalUser = $this->db->where('username', $clientEmail)
      ->where('user_id !=', $ignoreUserId)
      ->get('users')
      ->row();
    if ($existingGlobalUser) {
      return array(false, 'This email is already registered in the system. Please use a different email.');
    }

    $nameSource = trim((string) ($customerData['ContactPerson'] ?? ''));
    if ($nameSource === '') {
      $nameSource = trim((string) ($customerData['Customer'] ?? ''));
    }

    $nameParts = $this->_split_person_name($nameSource);
    $payload = array(
      'username' => $clientEmail,
      'position' => 'Client',
      'fName' => $nameParts['fName'],
      'mName' => $nameParts['mName'],
      'lName' => $nameParts['lName'],
      'email' => $clientEmail,
      'avatar' => 'avatar.png',
      'acctStat' => 'active',
      'settingsID' => $settingsID,
    );

    if ($plainPassword !== '') {
      $payload['password'] = password_hash($plainPassword, PASSWORD_DEFAULT);
    } elseif (!empty($customerData['portal_password'])) {
      $payload['password'] = $customerData['portal_password'];
    }

    if ($existingUser) {
      $this->db->where('user_id', (int) $existingUser->user_id);
      if (!$this->db->update('users', $payload)) {
        return array(false, 'Client portal account could not be updated in the users table.');
      }

      return array(true, '');
    }

    if (empty($payload['password'])) {
      return array(false, 'Client portal password is required before creating the users table account.');
    }

    $payload['dateCreated'] = date('Y-m-d H:i:s');
    if (!$this->db->insert('users', $payload)) {
      return array(false, 'Client portal account could not be created in the users table.');
    }

    return array(true, '');
  }

  private function _next_client_id_value($settingsID)
  {
    $nextClientId = (string) $settingsID . '10001';
    $latestClientRows = $this->CashModel->getCustID($settingsID);

    if (!empty($latestClientRows) && isset($latestClientRows[0]->CustID) && is_numeric($latestClientRows[0]->CustID)) {
      $nextClientId = (string) (((int) $latestClientRows[0]->CustID) + 1);
    }

    return $nextClientId;
  }

  private function _client_entry_form_values($settingsID)
  {
    $defaults = array(
      'CustID' => $this->_next_client_id_value($settingsID),
      'Customer' => '',
      'Address' => '',
      'Contact' => '',
      'ContactPerson' => '',
      'CompanyEmail' => '',
      'ClientStat' => 'Active',
      'client_source' => '',
      'facebook_link' => '',
      'client_email' => '',
      'notes' => '',
      'sales_agent' => '',
      'portal_enabled' => '0',
      'portal_password' => '',
    );

    $oldValues = $this->session->flashdata('client_form_input');
    if (is_array($oldValues)) {
      $defaults = array_merge($defaults, $oldValues);
    }

    if (trim((string) ($defaults['CustID'] ?? '')) === '') {
      $defaults['CustID'] = $this->_next_client_id_value($settingsID);
    }

    return $defaults;
  }

  private function _handle_client_create($settingsID, $errorRedirect = 'Page/clientEntry')
  {
    $formInput = array(
      'CustID' => trim((string) $this->input->post('CustID', true)),
      'Customer' => trim((string) $this->input->post('Customer', true)),
      'Address' => trim((string) $this->input->post('Address', true)),
      'Contact' => trim((string) $this->input->post('Contact', true)),
      'ContactPerson' => trim((string) $this->input->post('ContactPerson', true)),
      'CompanyEmail' => trim((string) $this->input->post('CompanyEmail', true)),
      'ClientStat' => trim((string) $this->input->post('ClientStat', true)),
      'client_source' => trim((string) $this->input->post('client_source', true)),
      'facebook_link' => trim((string) $this->input->post('facebook_link', true)),
      'client_email' => trim((string) $this->input->post('client_email', true)),
      'notes' => trim((string) $this->input->post('notes', true)),
      'sales_agent' => trim((string) $this->input->post('sales_agent', true)),
      'portal_enabled' => (string) $this->_normalize_client_portal_enabled($this->input->post('portal_enabled', true)),
      'invoice_access_enabled' => (string) $this->_normalize_client_portal_enabled($this->input->post('invoice_access_enabled', true)),
      'portal_password' => trim((string) $this->input->post('portal_password', true)),
    );

    if ($formInput['CustID'] === '') {
      $formInput['CustID'] = $this->_next_client_id_value($settingsID);
    }

    $this->session->set_flashdata('client_form_input', $formInput);

    $CustID = $formInput['CustID'];
    $Customer = $formInput['Customer'];
    $Address = $formInput['Address'];
    $ContactNos = $formInput['Contact'];
    $ContactPerson = $formInput['ContactPerson'];
    $CompanyEmail = $formInput['CompanyEmail'];
    $ClientStat = $formInput['ClientStat'];
    $client_source = $formInput['client_source'];
    $facebook_link = $formInput['facebook_link'];
    $client_email = $formInput['client_email'];
    $notes = $formInput['notes'];
    $sales_agent = $formInput['sales_agent'];
    $portalEnabled = (int) $formInput['portal_enabled'];
    $portalPasswordInput = $formInput['portal_password'];

    $duplicate = $this->db
      ->where('settingsID', $settingsID)
      ->where('LOWER(Customer)', strtolower($Customer))
      ->get('customers')
      ->row();

    if ($duplicate) {
      $this->session->set_flashdata('danger', 'Client/company name already exists.');
      redirect($errorRedirect);
      return;
    }

    if ($portalEnabled && $client_email === '') {
      $this->session->set_flashdata('danger', 'Client email is required when portal access is enabled.');
      redirect($errorRedirect);
      return;
    }

    if ($portalEnabled && $portalPasswordInput === '') {
      $this->session->set_flashdata('danger', 'Set an initial portal password before enabling client access.');
      redirect($errorRedirect);
      return;
    }

    if ($portalEnabled && $this->_client_portal_email_in_use($settingsID, $client_email)) {
      $this->session->set_flashdata('danger', 'Client email is already used by another portal account.');
      redirect($errorRedirect);
      return;
    }

    $data = array(
      'CustID' => $CustID,
      'Customer' => $Customer,
      'Address' => $Address,
      'ContactNos' => $ContactNos,
      'ContactPerson' => $ContactPerson,
      'CompanyEmail' => $CompanyEmail,
      'ClientStat' => $ClientStat,
      'client_source' => $client_source,
      'facebook_link' => $facebook_link,
      'client_email' => $client_email,
      'notes' => $notes,
      'sales_agent' => $sales_agent,
      'portal_enabled' => $portalEnabled,
      'invoice_access_enabled' => (int) $formInput['invoice_access_enabled'],
      'portal_password' => $portalEnabled ? password_hash($portalPasswordInput, PASSWORD_DEFAULT) : null,
      'settingsID' => $settingsID
    );

    $this->db->trans_begin();

    $customerSaved = $this->db->insert('customers', $data);
    $syncResult = array(true, '');

    if ($customerSaved) {
      $syncResult = $this->_sync_client_portal_user_account($settingsID, $data, $portalPasswordInput);
    }

    if (!$customerSaved || !$syncResult[0]) {
      $this->db->trans_rollback();
      $this->session->set_flashdata('danger', $syncResult[1] !== '' ? $syncResult[1] : 'Failed to save client.');
      redirect($errorRedirect);
      return;
    }

    $this->db->trans_commit();
    $this->session->set_flashdata('success', 'Client added successfully.');
    redirect('Page/clientList');
    return;
  }

  private function _can_current_client_access_invoice($invoice)
  {
    if (!$this->_is_client_user()) {
      return true;
    }

    if (!$invoice) {
      return false;
    }

    return $this->_current_client_matches($invoice->CustID ?? '', $invoice->Customer ?? '');
  }

  private function _can_manage_support_issues()
  {
    return $this->_is_admin_user() || $this->_is_staff_user();
  }

  private function _client_support_department_options()
  {
    return array(
      'general' => 'General',
    );
  }

  private function _support_category_options()
  {
    return array(
      'technical' => 'Technical',
      'billing' => 'Billing',
      'general' => 'General',
    );
  }

  private function _normalize_support_category($value)
  {
    $value = strtolower(trim((string) $value));
    if ($value === 'support') {
      $value = 'technical';
    }
    return array_key_exists($value, $this->_support_category_options()) ? $value : 'general';
  }

  private function _support_closed_statuses()
  {
    return array('closed', 'resolved', 'done', 'completed', 'cancelled', 'canceled');
  }

  private function _support_is_closed_status($status)
  {
    return in_array(strtolower(trim((string) $status)), $this->_support_closed_statuses(), true);
  }

  private function _support_status_label($status)
  {
    $status = strtolower(trim((string) $status));
    if ($status === '') {
      $status = 'open';
    }

    return ucwords(str_replace('_', ' ', $status));
  }

  private function _support_department_value_from_employee_department($department)
  {
    $department = strtolower(trim((string) $department));

    if ($department === 'technical' || $department === 'support') {
      return 'technical';
    }

    if ($department === 'billing') {
      return 'billing';
    }

    if ($department === 'general') {
      return 'general';
    }

    return '';
  }

  private function _support_department_for_category($settingsID, $category)
  {
    $settingsID = (int) $settingsID;
    $category = $this->_normalize_support_category($category);

    $targets = array(
      'technical' => array(
        'code' => 'TECH',
        'name' => 'Technical',
        'description' => 'Handles technical issues and system concerns.',
      ),
      'billing' => array(
        'code' => 'BILL',
        'name' => 'Billing',
        'description' => 'Handles billing, invoicing, and payment concerns.',
      ),
      'general' => array(
        'code' => 'GEN',
        'name' => 'General',
        'description' => 'Handles general support requests and routing.',
      ),
    );

    $target = $targets[$category];
    $row = $this->db
      ->from('support_departments')
      ->where('settingsID', $settingsID)
      ->where('is_active', 1)
      ->group_start()
      ->where('department_code', $target['code'])
      ->or_where('LOWER(department_name)', strtolower($target['name']))
      ->group_end()
      ->limit(1)
      ->get()
      ->row();

    if ($row) {
      return $row;
    }

    $this->db->insert('support_departments', array(
      'department_name' => $target['name'],
      'department_code' => $target['code'],
      'description' => $target['description'],
      'settingsID' => $settingsID,
    ));

    $departmentId = (int) $this->db->insert_id();
    return $this->db
      ->from('support_departments')
      ->where('id', $departmentId)
      ->limit(1)
      ->get()
      ->row();
  }

  private function _generate_support_ticket_number($settingsID)
  {
    $settingsID = (int) $settingsID;
    $prefix = 'SUP-' . date('Ymd') . '-';
    $count = (int) $this->db
      ->from('support_issues')
      ->where('settingsID', $settingsID)
      ->like('ticket_number', $prefix, 'after')
      ->count_all_results();

    return $prefix . str_pad((string) ($count + 1), 4, '0', STR_PAD_LEFT);
  }

  private function _create_support_notification($settingsID, $userId, $actorId, $issueId, $departmentId, $type, $title, $message)
  {
    $settingsID = (int) $settingsID;
    $userId = (int) $userId;
    $actorId = (int) $actorId;
    $issueId = (int) $issueId;
    $departmentId = (int) $departmentId;

    if ($settingsID <= 0 || $userId <= 0) {
      return false;
    }

    $exists = $this->db
      ->from('support_notifications')
      ->where('settingsID', $settingsID)
      ->where('user_id', $userId)
      ->where('issue_id', $issueId > 0 ? $issueId : null)
      ->where('notification_type', $type)
      ->where('title', $title)
      ->where('message', $message)
      ->where('created_at >=', date('Y-m-d H:i:s', strtotime('-5 minutes')))
      ->count_all_results();

    if ($exists > 0) {
      return true;
    }

    return $this->db->insert('support_notifications', array(
      'user_id' => $userId,
      'actor_id' => $actorId > 0 ? $actorId : null,
      'issue_id' => $issueId > 0 ? $issueId : null,
      'department_id' => $departmentId > 0 ? $departmentId : null,
      'notification_type' => $type,
      'title' => $title,
      'message' => $message,
      'action_required' => 1,
      'expires_at' => date('Y-m-d H:i:s', strtotime('+7 days')),
      'settingsID' => $settingsID,
    ));
  }

  private function _support_recipient_user_ids($settingsID, $departmentId = 0)
  {
    $settingsID = (int) $settingsID;
    $departmentId = (int) $departmentId;
    $recipientIds = array();

    $adminRows = $this->db
      ->select('user_id')
      ->from('users')
      ->where('settingsID', $settingsID)
      ->where('acctStat', 'active')
      ->where_in('position', array('Admin', 'Manager', 'POS Admin'))
      ->get()
      ->result();

    foreach ($adminRows as $row) {
      $recipientIds[] = (int) $row->user_id;
    }

    if ($departmentId > 0 && $this->db->table_exists('employee_departments')) {
      $departmentRows = $this->db
        ->select('employee_id')
        ->from('employee_departments')
        ->where('settingsID', $settingsID)
        ->where('department_id', $departmentId)
        ->where('is_active', 1)
        ->get()
        ->result();

      foreach ($departmentRows as $row) {
        $recipientIds[] = (int) $row->employee_id;
      }
    }

    $departmentSpecificRecipients = array_diff($recipientIds, array_map(function ($row) {
      return (int) ($row->user_id ?? 0);
    }, $adminRows));

    if ($departmentId > 0 && empty($departmentSpecificRecipients)) {
      $department = $this->db
        ->select('department_name')
        ->from('support_departments')
        ->where('settingsID', $settingsID)
        ->where('id', $departmentId)
        ->limit(1)
        ->get()
        ->row();

      $departmentName = strtolower(trim((string) ($department->department_name ?? '')));
      if ($departmentName !== '') {
        $fallbackUsers = $this->db
          ->select('users.user_id')
          ->from('users')
          ->join('employee', 'employee.empID = users.username AND employee.settingsID = users.settingsID', 'inner')
          ->where('users.settingsID', $settingsID)
          ->where("LOWER(TRIM(COALESCE(employee.department, ''))) = " . $this->db->escape($departmentName), null, false)
          ->get()
          ->result();

        foreach ($fallbackUsers as $row) {
          $recipientIds[] = (int) ($row->user_id ?? 0);
        }
      }
    }

    return array_values(array_unique(array_filter($recipientIds)));
  }

  private function _support_issue_row($issueId, $settingsID)
  {
    $issueId = (int) $issueId;
    $settingsID = (int) $settingsID;

    if ($issueId <= 0 || $settingsID <= 0) {
      return null;
    }

    return $this->db
      ->select('si.*, d.department_name, d.department_code, p.projectDescription, CONCAT(u.fName, " ", u.lName) AS assigned_employee_name')
      ->from('support_issues si')
      ->join('support_departments d', 'd.id = si.department_id', 'left')
      ->join('projects p', 'p.projectID = si.project_id', 'left')
      ->join('users u', 'u.user_id = si.assigned_employee_id', 'left')
      ->where('si.id', $issueId)
      ->where('si.settingsID', $settingsID)
      ->limit(1)
      ->get()
      ->row();
  }

  private function _staff_support_department_ids($settingsID, $userId)
  {
    $settingsID = (int) $settingsID;
    $userId = (int) $userId;

    if ($settingsID <= 0 || $userId <= 0) {
      return array();
    }

    $departmentIds = array();

    if ($this->db->table_exists('employee_departments')) {
      $departmentRows = $this->db
        ->select('department_id')
        ->from('employee_departments')
        ->where('settingsID', $settingsID)
        ->where('employee_id', $userId)
        ->where('is_active', 1)
        ->get()
        ->result();

      foreach ($departmentRows as $row) {
        $departmentIds[] = (int) ($row->department_id ?? 0);
      }
    }

    $departmentIds = array_values(array_unique(array_filter($departmentIds)));

    $employee = $this->_support_employee_row($settingsID, $userId);
    $departmentKey = $this->_support_department_value_from_employee_department((string) ($employee->department ?? ''));
    if (empty($departmentIds) && $departmentKey === '') {
      return array();
    }

    // Support queues are consolidated: any support staff sees tickets across
    // every support department (legacy Technical/Billing/General) so unassigned
    // items from older categories remain visible after the merge.
    $allDepartmentRows = $this->db
      ->select('id')
      ->from('support_departments')
      ->where('settingsID', $settingsID)
      ->get()
      ->result();

    foreach ($allDepartmentRows as $row) {
      $departmentIds[] = (int) ($row->id ?? 0);
    }

    return array_values(array_unique(array_filter($departmentIds)));
  }

  private function _support_client_user_id($settingsID, $customerId)
  {
    $settingsID = (int) $settingsID;
    $customerId = trim((string) $customerId);

    if ($settingsID <= 0 || $customerId === '') {
      return 0;
    }

    $row = $this->db
      ->select('user_id')
      ->from('users')
      ->where('settingsID', $settingsID)
      ->where('position', 'Client')
      ->group_start()
      ->where('username', $customerId)
      ->or_where('email', $customerId)
      ->group_end()
      ->limit(1)
      ->get()
      ->row();

    if ($row) {
      return (int) ($row->user_id ?? 0);
    }

    $customerRow = $this->db
      ->select('client_email')
      ->from('customers')
      ->where('settingsID', $settingsID)
      ->where('CustID', $customerId)
      ->limit(1)
      ->get()
      ->row();

    $clientEmail = trim((string) ($customerRow->client_email ?? ''));
    if ($clientEmail === '') {
      return 0;
    }

    $userRow = $this->db
      ->select('user_id')
      ->from('users')
      ->where('settingsID', $settingsID)
      ->where('position', 'Client')
      ->group_start()
      ->where('username', $clientEmail)
      ->or_where('email', $clientEmail)
      ->group_end()
      ->limit(1)
      ->get()
      ->row();

    return (int) ($userRow->user_id ?? 0);
  }

  private function _employee_account_row($settingsID, $empID, $email = '')
  {
    $settingsID = (int) $settingsID;
    $empID = trim((string) $empID);
    $email = trim((string) $email);

    $this->db
      ->select('user_id, username, email, position, support_chat_view, support_chat_reply')
      ->from('users')
      ->where('settingsID', $settingsID);

    $this->db->group_start();
    if ($empID !== '') {
      $this->db->where('username', $empID);
    }
    if ($email !== '') {
      if ($empID !== '') {
        $this->db->or_where('username', $email);
      } else {
        $this->db->where('username', $email);
      }
      $this->db->or_where('email', $email);
    }
    $this->db->group_end();

    return $this->db->limit(1)->get()->row();
  }

  private function _sync_employee_user_account($settingsID, $empID, $fName, $mName, $lName, $email, $bDate, $supportChatView = 1, $supportChatReply = 1)
  {
    $settingsID = (int) $settingsID;
    $empID = trim((string) $empID);
    $fName = trim((string) $fName);
    $mName = trim((string) $mName);
    $lName = trim((string) $lName);
    $email = strtolower(trim((string) $email));
    $bDate = trim((string) $bDate);
    $supportChatView = (int) $supportChatView ? 1 : 0;
    $supportChatReply = ($supportChatView === 1 && (int) $supportChatReply) ? 1 : 0;

    if ($settingsID <= 0 || $empID === '') {
      return array(false, 'Invalid employee account details.');
    }

    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
      return array(false, 'A valid employee email is required.');
    }

    $existing = $this->_employee_account_row($settingsID, $empID, $email);
    $existingUserId = (int) ($existing->user_id ?? 0);

    $duplicate = $this->db
      ->select('user_id')
      ->from('users')
      ->where('settingsID', $settingsID)
      ->group_start()
      ->where('username', $email)
      ->or_where('email', $email)
      ->group_end()
      ->limit(1)
      ->get()
      ->row();

    if ($duplicate && (int) ($duplicate->user_id ?? 0) !== $existingUserId) {
      return array(false, 'The employee email is already used by another user account.');
    }

    $accountData = array(
      'username' => $email,
      'email' => $email,
      'fName' => $fName,
      'mName' => $mName,
      'lName' => $lName,
      'support_chat_view' => $supportChatView,
      'support_chat_reply' => $supportChatReply,
    );

    if ($existingUserId > 0) {
      $this->db
        ->where('user_id', $existingUserId)
        ->where('settingsID', $settingsID)
        ->update('users', $accountData);

      return array($this->db->affected_rows() >= 0, '');
    }

    $passwordSeed = $bDate !== '' ? $bDate : date('Y-m-d');
    $accountData['password'] = sha1($passwordSeed);
    $accountData['position'] = 'Staff';
    $accountData['avatar'] = 'avatar.png';
    $accountData['acctStat'] = 'active';
    $accountData['dateCreated'] = date('Y-m-d H:i:s');
    $accountData['settingsID'] = $settingsID;

    $created = $this->db->insert('users', $accountData);
    return array($created, $created ? '' : 'Unable to create the employee user account.');
  }

  private function _support_user_chat_permissions($settingsID, $userId)
  {
    $settingsID = (int) $settingsID;
    $userId = (int) $userId;

    if ($this->_is_admin_user()) {
      return array('view' => true, 'reply' => true);
    }

    $row = $this->db
      ->select('support_chat_view, support_chat_reply')
      ->from('users')
      ->where('settingsID', $settingsID)
      ->where('user_id', $userId)
      ->limit(1)
      ->get()
      ->row();

    return array(
      'view' => !empty($row) ? ((int) ($row->support_chat_view ?? 1) === 1) : true,
      'reply' => !empty($row) ? ((int) ($row->support_chat_reply ?? 1) === 1) : true,
    );
  }

  private function _support_email_sender_context($settingsID)
  {
    $settingsID = (int) $settingsID;
    $this->config->load('email');
    $this->load->library('email');

    $settings = $this->db->table_exists('pos_settings')
      ? $this->db->get_where('pos_settings', ['settingsID' => $settingsID])->row()
      : null;
    $business = method_exists($this->CashModel, 'businessDetails') ? ($this->CashModel->businessDetails($settingsID)[0] ?? null) : null;

    $defaultProtocol = trim((string) $this->config->item('protocol'));
    $defaultHost = trim((string) $this->config->item('smtp_host'));
    $defaultUser = trim((string) $this->config->item('smtp_user'));
    $defaultPass = (string) $this->config->item('smtp_pass');
    $defaultPort = (int) $this->config->item('smtp_port');
    $defaultCrypto = trim((string) $this->config->item('smtp_crypto'));
    $defaultTimeout = (int) $this->config->item('smtp_timeout');

    $smtpHost = $settings && !empty($settings->smtp_host) ? trim((string) $settings->smtp_host) : $defaultHost;
    $smtpUser = $settings && !empty($settings->smtp_user) ? trim((string) $settings->smtp_user) : $defaultUser;
    $smtpPass = $settings && isset($settings->smtp_pass) && $settings->smtp_pass !== '' ? (string) $settings->smtp_pass : $defaultPass;
    $smtpPort = $settings && !empty($settings->smtp_port) ? (int) $settings->smtp_port : ($defaultPort ?: 587);
    $smtpCrypto = $settings && !empty($settings->smtp_crypto) ? trim((string) $settings->smtp_crypto) : ($defaultCrypto !== '' ? $defaultCrypto : 'tls');

    if ($smtpHost === '' || $smtpUser === '' || $smtpPass === '') {
      return array('ready' => false);
    }

    $this->email->initialize([
      'protocol' => $defaultProtocol !== '' ? $defaultProtocol : 'smtp',
      'smtp_host' => $smtpHost,
      'smtp_user' => $smtpUser,
      'smtp_pass' => $smtpPass,
      'smtp_port' => $smtpPort,
      'smtp_crypto' => $smtpCrypto,
      'smtp_timeout' => $defaultTimeout ?: 10,
      'mailtype' => 'html',
      'charset' => 'utf-8',
      'newline' => "\r\n",
      'crlf' => "\r\n",
      'wordwrap' => true,
    ]);

    $fromEmail = '';
    foreach ([$smtpUser, trim((string) ($business->Email ?? ''))] as $candidateEmail) {
      if ($candidateEmail !== '' && filter_var($candidateEmail, FILTER_VALIDATE_EMAIL)) {
        $fromEmail = $candidateEmail;
        break;
      }
    }

    if ($fromEmail === '') {
      return array('ready' => false);
    }

    $configuredFromName = trim((string) $this->config->item('from_name'));
    $fromName = '';
    foreach (
      array(
        $configuredFromName,
        trim((string) ($settings->CompName ?? '')),
        trim((string) ($settings->company_name ?? '')),
        trim((string) ($settings->BusinessName ?? '')),
        trim((string) ($settings->business_name ?? '')),
        trim((string) ($business->CompName ?? '')),
        trim((string) ($business->company_name ?? '')),
        trim((string) ($business->BusinessName ?? '')),
        trim((string) ($business->business_name ?? ''))
      ) as $candidateName
    ) {
      if ($candidateName !== '') {
        $fromName = $candidateName;
        break;
      }
    }
    if ($fromName === '') {
      $fromName = 'BERPS';
    }

    return array(
      'ready' => true,
      'fromEmail' => $fromEmail,
      'fromName' => $fromName,
    );
  }

  private function _support_email_label($value, $fallback = 'Not specified')
  {
    $value = trim((string) $value);
    return $value !== '' ? $value : $fallback;
  }

  private function _support_email_priority_label($priority)
  {
    $priority = strtolower(trim((string) $priority));
    if ($priority === '') {
      return 'Not specified';
    }

    return ucwords(str_replace(array('_', '-'), ' ', $priority));
  }

  private function _support_email_ticket_details_html($issue)
  {
    if (!$issue) {
      return '';
    }

    $projectName = $this->_support_email_label($issue->projectDescription ?? $issue->project_label ?? '');
    $departmentName = $this->_support_email_label($issue->department_name ?? $issue->category ?? '');
    $detailRows = array(
      'Ticket Number' => $this->_support_email_label($issue->ticket_number ?? ''),
      'Subject' => $this->_support_email_label($issue->title ?? ''),
      'Status' => $this->_support_email_label($issue->status ?? ''),
      'Priority' => $this->_support_email_priority_label($issue->priority ?? ''),
      'Department' => $departmentName,
      'Project' => $projectName,
      'Client' => $this->_support_email_label($issue->customer_name ?? ''),
      'Email' => $this->_support_email_label($issue->customer_email ?? ''),
    );

    $html = '<div style="margin:24px 0 18px; padding:18px; border:1px solid #dbeafe; border-radius:12px; background:#f8fbff;">';
    $html .= '<h4 style="margin:0 0 14px; color:#1d4ed8; font-size:16px;">Ticket Details</h4>';

    foreach ($detailRows as $label => $value) {
      $html .= '<p style="margin:0 0 8px;"><strong>' . htmlspecialchars($label, ENT_QUOTES, 'UTF-8') . ':</strong> ' . htmlspecialchars($value, ENT_QUOTES, 'UTF-8') . '</p>';
    }

    $description = trim((string) ($issue->description ?? ''));
    if ($description !== '') {
      $html .= '<div style="margin-top:14px;">';
      $html .= '<div style="font-weight:700; margin-bottom:6px;">Report Details</div>';
      $html .= '<div style="white-space:pre-line;">' . htmlspecialchars($description, ENT_QUOTES, 'UTF-8') . '</div>';
      $html .= '</div>';
    }

    $referenceLink = trim((string) ($issue->reference_link ?? ''));
    if ($referenceLink !== '') {
      $safeReferenceLink = htmlspecialchars($referenceLink, ENT_QUOTES, 'UTF-8');
      $html .= '<p style="margin:14px 0 0;"><strong>Reference Link:</strong> <a href="' . $safeReferenceLink . '">' . $safeReferenceLink . '</a></p>';
    }

    $html .= '</div>';

    return $html;
  }

  private function _support_user_email($settingsID, $userId)
  {
    $row = $this->db
      ->select('email, username')
      ->from('users')
      ->where('settingsID', (int) $settingsID)
      ->where('user_id', (int) $userId)
      ->limit(1)
      ->get()
      ->row();

    foreach (array(trim((string) ($row->email ?? '')), trim((string) ($row->username ?? ''))) as $candidate) {
      if ($candidate !== '' && filter_var($candidate, FILTER_VALIDATE_EMAIL)) {
        return $candidate;
      }
    }

    return '';
  }

  private function _send_support_email_notification($settingsID, array $recipientEmails, $subject, $message, $issue = null)
  {
    $context = $this->_support_email_sender_context($settingsID);
    if (empty($context['ready'])) {
      return false;
    }

    $cleanRecipients = array_values(array_unique(array_filter(array_map(function ($email) {
      $email = trim((string) $email);
      return filter_var($email, FILTER_VALIDATE_EMAIL) ? $email : '';
    }, $recipientEmails))));

    if (empty($cleanRecipients)) {
      return false;
    }

    $issueLink = '';
    if ($issue && !empty($issue->id)) {
      $issueLink = site_url('Page/supportIssueView?id=' . (int) $issue->id);
    }

    $body = '<html><body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">';
    $body .= '<h3 style="color:#2563eb;">' . htmlspecialchars((string) $subject, ENT_QUOTES, 'UTF-8') . '</h3>';
    $body .= '<p>' . nl2br(htmlspecialchars((string) $message, ENT_QUOTES, 'UTF-8')) . '</p>';
    if ($issue) {
      $body .= $this->_support_email_ticket_details_html($issue);
      if ($issueLink !== '') {
        $body .= '<p><a href="' . htmlspecialchars($issueLink, ENT_QUOTES, 'UTF-8') . '">Open support ticket</a></p>';
      }
    }
    $body .= '</body></html>';

    foreach ($cleanRecipients as $recipientEmail) {
      $this->email->clear(true);
      $this->email->from($context['fromEmail'], $context['fromName']);
      $this->email->to($recipientEmail);
      $this->email->subject($subject);
      $this->email->message($body);
      $sendResult = false;
      set_error_handler(function () {
        return true;
      });
      try {
        $sendResult = (bool) @$this->email->send();
      } catch (Throwable $exception) {
        $sendResult = false;
      } catch (Exception $exception) {
        $sendResult = false;
      }
      restore_error_handler();

      if (!$sendResult) {
        continue;
      }
    }

    return true;
  }

  private function _sync_employee_support_department($settingsID, $employeeUsername, $departmentName)
  {
    $settingsID = (int) $settingsID;
    $employeeUsername = trim((string) $employeeUsername);
    $departmentKey = $this->_support_department_value_from_employee_department($departmentName);

    if ($settingsID <= 0 || $employeeUsername === '' || !$this->db->table_exists('employee_departments')) {
      return;
    }

    $user = $this->db
      ->select('user_id')
      ->from('users')
      ->where('settingsID', $settingsID)
      ->where('username', $employeeUsername)
      ->limit(1)
      ->get()
      ->row();

    $userId = (int) ($user->user_id ?? 0);
    if ($userId <= 0) {
      return;
    }

    $this->db
      ->where('settingsID', $settingsID)
      ->where('employee_id', $userId)
      ->update('employee_departments', array('is_active' => 0));

    if ($departmentKey === '') {
      return;
    }

    $department = $this->_support_department_for_category($settingsID, $departmentKey);
    $departmentId = (int) ($department->id ?? 0);
    if ($departmentId <= 0) {
      return;
    }

    $existing = $this->db
      ->select('id')
      ->from('employee_departments')
      ->where('settingsID', $settingsID)
      ->where('employee_id', $userId)
      ->where('department_id', $departmentId)
      ->limit(1)
      ->get()
      ->row();

    if ($existing) {
      $this->db
        ->where('id', (int) $existing->id)
        ->update('employee_departments', array(
          'is_active' => 1,
          'assigned_at' => date('Y-m-d H:i:s'),
          'assigned_by' => (int) ($this->session->userdata('user_id') ?? 0),
        ));
      return;
    }

    $this->db->insert('employee_departments', array(
      'employee_id' => $userId,
      'department_id' => $departmentId,
      'role' => 'member',
      'is_active' => 1,
      'assigned_at' => date('Y-m-d H:i:s'),
      'assigned_by' => (int) ($this->session->userdata('user_id') ?? 0),
      'settingsID' => $settingsID,
    ));
  }

  private function _support_issue_attachments($issueId, $settingsID)
  {
    if (!$this->db->table_exists('support_issue_attachments')) {
      return array();
    }

    return $this->db
      ->from('support_issue_attachments')
      ->where('settingsID', (int) $settingsID)
      ->where('issue_id', (int) $issueId)
      ->order_by('created_at', 'ASC')
      ->get()
      ->result();
  }

  private function _handle_support_issue_attachments($issueId, $settingsID, $fieldName = 'attachments', $uploadedByType = 'client', $uploadedBy = 0)
  {
    $issueId = (int) $issueId;
    $settingsID = (int) $settingsID;
    $uploadedBy = (int) $uploadedBy;

    if ($issueId <= 0 || $settingsID <= 0 || !$this->db->table_exists('support_issue_attachments')) {
      return array();
    }

    if (empty($_FILES[$fieldName]['name'])) {
      return array();
    }

    $names = $_FILES[$fieldName]['name'];
    $tmpNames = $_FILES[$fieldName]['tmp_name'];
    $sizes = $_FILES[$fieldName]['size'];
    $errors = $_FILES[$fieldName]['error'];
    $types = $_FILES[$fieldName]['type'];

    if (!is_array($names)) {
      $names = array($names);
      $tmpNames = array($tmpNames);
      $sizes = array($sizes);
      $errors = array($errors);
      $types = array($types);
    }

    $uploadDir = FCPATH . 'uploads/support_attachments/';
    if (!is_dir($uploadDir)) {
      @mkdir($uploadDir, 0777, true);
    }

    $messages = array();
    $allowedExtensions = array('jpg', 'jpeg', 'png', 'gif', 'webp');

    foreach ($names as $index => $originalName) {
      $originalName = trim((string) $originalName);
      if ($originalName === '') {
        continue;
      }

      $errorCode = (int) ($errors[$index] ?? UPLOAD_ERR_NO_FILE);
      if ($errorCode !== UPLOAD_ERR_OK) {
        $messages[] = $originalName . ' was skipped because the upload failed.';
        continue;
      }

      $fileSize = (int) ($sizes[$index] ?? 0);
      if ($fileSize <= 0 || $fileSize > (2 * 1024 * 1024)) {
        $messages[] = $originalName . ' exceeds the 2 MB limit.';
        continue;
      }

      $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
      if (!in_array($extension, $allowedExtensions, true)) {
        $messages[] = $originalName . ' is not a supported image file.';
        continue;
      }

      $storedName = 'support_' . $issueId . '_' . time() . '_' . $index . '.' . $extension;
      $targetPath = $uploadDir . $storedName;
      $tmpName = (string) ($tmpNames[$index] ?? '');

      if ($tmpName === '' || !@move_uploaded_file($tmpName, $targetPath)) {
        $messages[] = $originalName . ' could not be saved.';
        continue;
      }

      $this->db->insert('support_issue_attachments', array(
        'issue_id' => $issueId,
        'file_name' => $originalName,
        'file_path' => 'uploads/support_attachments/' . $storedName,
        'file_size' => $fileSize,
        'mime_type' => trim((string) ($types[$index] ?? '')),
        'uploaded_by_type' => $uploadedByType,
        'uploaded_by' => $uploadedBy > 0 ? $uploadedBy : null,
        'settingsID' => $settingsID,
      ));
    }

    return $messages;
  }

  private function _create_support_issue(array $payload)
  {
    $settingsID = (int) ($payload['settingsID'] ?? 0);
    $departmentId = (int) ($payload['department_id'] ?? 0);
    $category = $this->_normalize_support_category($payload['category'] ?? '');
    $ticketNumber = $this->_generate_support_ticket_number($settingsID);
    $assignedEmployeeId = !empty($payload['assigned_employee_id']) ? (int) $payload['assigned_employee_id'] : null;

    $issueData = array(
      'ticket_number' => $ticketNumber,
      'customer_id' => trim((string) ($payload['customer_id'] ?? '')),
      'customer_name' => trim((string) ($payload['customer_name'] ?? '')),
      'customer_email' => trim((string) ($payload['customer_email'] ?? '')),
      'customer_phone' => trim((string) ($payload['customer_phone'] ?? '')),
      'department_id' => $departmentId,
      'project_id' => !empty($payload['project_id']) ? (int) $payload['project_id'] : null,
      'task_id' => !empty($payload['task_id']) ? (int) $payload['task_id'] : null,
      'assigned_employee_id' => $assignedEmployeeId,
      'title' => trim((string) ($payload['title'] ?? '')),
      'description' => trim((string) ($payload['description'] ?? '')),
      'reference_link' => trim((string) ($payload['reference_link'] ?? '')),
      'category' => $category,
      'priority' => trim((string) ($payload['priority'] ?? 'medium')),
      'status' => trim((string) ($payload['status'] ?? 'awaiting_reply')),
      'settingsID' => $settingsID,
    );

    $this->db->insert('support_issues', $issueData);
    $issueId = (int) $this->db->insert_id();

    if ($issueId <= 0) {
      return null;
    }

    $detailLines = array(trim((string) $issueData['description']));
    if (!empty($payload['project_label'])) {
      $detailLines[] = 'Project: ' . trim((string) $payload['project_label']);
    }
    if (!empty($issueData['reference_link'])) {
      $detailLines[] = 'Reference Link: ' . $issueData['reference_link'];
    }

    $this->db->insert('support_issue_comments', array(
      'issue_id' => $issueId,
      'employee_id' => null,
      'customer_comment' => 1,
      'comment' => implode("\n", array_filter($detailLines)),
      'internal_note' => 0,
      'created_at' => date('Y-m-d H:i:s'),
      'settingsID' => $settingsID,
    ));

    $department = $this->db
      ->select('department_name')
      ->from('support_departments')
      ->where('id', $departmentId)
      ->limit(1)
      ->get()
      ->row();
    $departmentName = trim((string) ($department->department_name ?? 'General'));

    $currentUserId = (int) ($this->session->userdata('user_id') ?? 0);
    foreach ($this->_support_recipient_user_ids($settingsID, $departmentId) as $recipientId) {
      $this->_create_support_notification(
        $settingsID,
        $recipientId,
        $currentUserId,
        $issueId,
        $departmentId,
        'new_issue',
        'New Client Support Issue',
        'Ticket ' . $ticketNumber . ' was submitted under ' . $departmentName . ': ' . $issueData['title']
      );
    }
    $supportEmails = array();
    foreach ($this->_support_recipient_user_ids($settingsID, $departmentId) as $recipientId) {
      $supportEmails[] = $this->_support_user_email($settingsID, $recipientId);
    }
    $this->_send_support_email_notification(
      $settingsID,
      $supportEmails,
      'New Support Ticket ' . $ticketNumber,
      'A new support ticket has been submitted under ' . $departmentName . '.',
      (object) array_merge($issueData, array('id' => $issueId))
    );

    $clientUserId = $this->_support_client_user_id($settingsID, $issueData['customer_id']);
    if ($clientUserId > 0) {
      $this->_create_support_notification(
        $settingsID,
        $clientUserId,
        $currentUserId,
        $issueId,
        $departmentId,
        'submitted',
        'Support Ticket Submitted',
        'Your ticket ' . $ticketNumber . ' has been submitted. A support staff member will reach you shortly.'
      );
    }
    $this->_send_support_email_notification(
      $settingsID,
      array($issueData['customer_email']),
      'Support Ticket Submitted: ' . $ticketNumber,
      'Your ticket has been submitted. A support staff member will reach you shortly.',
      (object) array_merge($issueData, array('id' => $issueId))
    );

    return $this->db
      ->from('support_issues')
      ->where('id', $issueId)
      ->limit(1)
      ->get()
      ->row();
  }

  private function _support_issue_for_user($issueId, $settingsID, $userId)
  {
    $issueId = (int) $issueId;
    $settingsID = (int) $settingsID;
    $userId = (int) $userId;

    if ($issueId <= 0 || $settingsID <= 0) {
      return null;
    }

    $issue = $this->_support_issue_row($issueId, $settingsID);

    if (!$issue) {
      return null;
    }

    if ($this->_is_admin_user()) {
      return $issue;
    }

    if ($this->_is_client_user()) {
      return $this->_current_client_matches($issue->customer_id ?? '', $issue->customer_name ?? '') ? $issue : null;
    }

    if (!$this->_is_staff_user()) {
      return null;
    }

    $allowed = in_array((int) ($issue->department_id ?? 0), $this->_staff_support_department_ids($settingsID, $userId), true);

    if ($allowed || (int) ($issue->assigned_employee_id ?? 0) === $userId) {
      return $issue;
    }

    if ($this->db->table_exists('support_notifications')) {
      $hasNotificationAccess = $this->db
        ->from('support_notifications')
        ->where('settingsID', $settingsID)
        ->where('user_id', $userId)
        ->where('issue_id', $issueId)
        ->count_all_results() > 0;

      if ($hasNotificationAccess) {
        return $issue;
      }
    }

    if ($this->db->table_exists('projects_task')) {
      $relatedTaskId = (int) ($issue->task_id ?? 0);
      if ($relatedTaskId > 0) {
        $hasTaggedTask = $this->db
          ->from('projects_task')
          ->where('settingsID', $settingsID)
          ->where('assignedPerson', $userId)
          ->group_start()
          ->where('taskID', $relatedTaskId)
          ->or_where('forwarded_from', $relatedTaskId)
          ->group_end()
          ->count_all_results() > 0;

        if ($hasTaggedTask) {
          return $issue;
        }
      }
    }

    return null;
  }

  private function _support_assignable_users($settingsID, $departmentId = 0)
  {
    $settingsID = (int) $settingsID;
    $departmentId = (int) $departmentId;

    // Debug logging
    error_log("DEBUG: _support_assignable_users called with settingsID=$settingsID, departmentId=$departmentId");

    $departmentFilter = '';

    // Apply department filtering if specified
    if ($departmentId > 0) {
      // Get department name from support_departments
      $department = $this->db
        ->select('department_name')
        ->from('support_departments')
        ->where('support_departments.settingsID', $settingsID)
        ->where('support_departments.id', $departmentId)
        ->limit(1)
        ->get()
        ->row();

      $departmentName = strtolower(trim((string) ($department->department_name ?? '')));
      error_log("DEBUG: Department name: '$departmentName'");

      if ($departmentName !== '') {
        $departmentFilter = "AND LOWER(TRIM(COALESCE(e.department, ''))) = " . $this->db->escape($departmentName);
      }
    }

    // Build the complete query
    $sql = "
      SELECT e.empID, e.fName, e.mName, e.lName, e.email, e.department, u.user_id, u.username, u.position
      FROM employee e
      INNER JOIN users u ON u.user_id = e.empID AND u.settingsID = e.settingsID
      WHERE e.settingsID = ?
        AND u.acctStat = 'active'
        AND u.position IN ('Admin', 'Manager', 'Encoder', 'Staff', 'Cashier', 'POS Admin', 'POS Staff')
        $departmentFilter
      GROUP BY e.empID
      ORDER BY e.fName ASC, e.lName ASC
    ";

    $users = $this->db->query($sql, array($settingsID))->result();

    error_log("DEBUG: Found " . count($users) . " employees via employee table");

    // If no users found with department filtering, return all active staff as fallback
    if ($departmentId > 0 && empty($users)) {
      error_log("DEBUG: No users found for department $departmentId, returning all active staff as fallback");

      $fallbackSql = "
        SELECT e.empID, e.fName, e.mName, e.lName, e.email, e.department, u.user_id, u.username, u.position
        FROM employee e
        INNER JOIN users u ON u.user_id = e.empID AND u.settingsID = e.settingsID
        WHERE e.settingsID = ?
          AND u.acctStat = 'active'
          AND u.position IN ('Admin', 'Manager', 'Encoder', 'Staff', 'Cashier', 'POS Admin', 'POS Staff')
        GROUP BY e.empID
        ORDER BY e.fName ASC, e.lName ASC
      ";

      $users = $this->db->query($fallbackSql, array($settingsID))->result();

      error_log("DEBUG: Fallback found " . count($users) . " employees");
    }

    error_log("DEBUG: Returning " . count($users) . " employees");
    return $users;
  }

  private function _support_taggable_users($settingsID)
  {
    $settingsID = (int) $settingsID;

    $sql = "
      SELECT
        COALESCE(e.empID, u.user_id) AS empID,
        COALESCE(NULLIF(TRIM(e.fName), ''), NULLIF(TRIM(u.fName), ''), '') AS fName,
        COALESCE(NULLIF(TRIM(e.mName), ''), NULLIF(TRIM(u.mName), ''), '') AS mName,
        COALESCE(NULLIF(TRIM(e.lName), ''), NULLIF(TRIM(u.lName), ''), '') AS lName,
        COALESCE(NULLIF(TRIM(e.email), ''), NULLIF(TRIM(u.email), ''), NULLIF(TRIM(u.username), '')) AS email,
        COALESCE(NULLIF(TRIM(e.department), ''), '') AS department,
        u.user_id,
        u.username,
        u.position
      FROM users u
      LEFT JOIN employee e ON e.empID = u.user_id AND e.settingsID = u.settingsID
      WHERE u.settingsID = ?
        AND u.acctStat = 'active'
        AND u.position IN ('Admin', 'Manager', 'Encoder', 'Staff', 'Cashier', 'POS Admin', 'POS Staff')
      GROUP BY u.user_id
      ORDER BY
        COALESCE(NULLIF(TRIM(e.fName), ''), NULLIF(TRIM(u.fName), ''), u.username) ASC,
        COALESCE(NULLIF(TRIM(e.lName), ''), NULLIF(TRIM(u.lName), ''), '') ASC
    ";

    return $this->db->query($sql, array($settingsID))->result();
  }

  private function _support_employee_row($settingsID, $userId)
  {
    $settingsID = (int) $settingsID;
    $userId = (int) $userId;

    if ($settingsID <= 0 || $userId <= 0) {
      return null;
    }

    $sql = "
      SELECT
        COALESCE(e.empID, u.user_id) AS empID,
        COALESCE(NULLIF(TRIM(e.fName), ''), NULLIF(TRIM(u.fName), ''), '') AS fName,
        COALESCE(NULLIF(TRIM(e.mName), ''), NULLIF(TRIM(u.mName), ''), '') AS mName,
        COALESCE(NULLIF(TRIM(e.lName), ''), NULLIF(TRIM(u.lName), ''), '') AS lName,
        COALESCE(NULLIF(TRIM(e.email), ''), NULLIF(TRIM(u.email), ''), NULLIF(TRIM(u.username), '')) AS email,
        COALESCE(NULLIF(TRIM(e.department), ''), '') AS department,
        u.user_id,
        u.username,
        u.position
      FROM users u
      LEFT JOIN employee e ON e.empID = u.user_id AND e.settingsID = u.settingsID
      WHERE u.settingsID = ?
        AND u.user_id = ?
      LIMIT 1
    ";

    return $this->db->query($sql, array($settingsID, $userId))->row();
  }

  private function _support_employee_name($employee)
  {
    if (!$employee) {
      return '';
    }

    // Use employee table data for name
    $name = trim((string) (($employee->fName ?? '') . ' ' . ($employee->mName ?? '') . ' ' . ($employee->lName ?? '')));
    if ($name !== '') {
      return $name;
    }

    $username = trim((string) ($employee->username ?? ''));
    if ($username !== '') {
      return $username;
    }

    return 'Staff #' . (int) ($employee->user_id ?? 0);
  }

  private function _support_issue_task_priority($priority)
  {
    $priority = strtolower(trim((string) $priority));

    if ($priority === 'high' || $priority === 'urgent') {
      return '1';
    }

    if ($priority === 'low') {
      return '3';
    }

    return '2';
  }

  private function _support_issue_task_row($taskId, $settingsID)
  {
    $taskId = (int) $taskId;
    $settingsID = (int) $settingsID;

    if ($taskId <= 0 || $settingsID <= 0) {
      return null;
    }

    return $this->db
      ->select('taskID, projectID, assignedPerson, taskStat')
      ->from('projects_task')
      ->where('taskID', $taskId)
      ->where('settingsID', $settingsID)
      ->limit(1)
      ->get()
      ->row();
  }

  private function _support_issue_by_task($taskId, $settingsID)
  {
    $taskId = (int) $taskId;
    $settingsID = (int) $settingsID;

    if ($taskId <= 0 || $settingsID <= 0 || !$this->db->table_exists('support_issues')) {
      return null;
    }

    return $this->db
      ->select('*')
      ->from('support_issues')
      ->where('task_id', $taskId)
      ->where('settingsID', $settingsID)
      ->limit(1)
      ->get()
      ->row();
  }

  private function _sync_support_issue_from_task_status($taskId, $taskStat, $note, $settingsID, $userId, $username, $date)
  {
    $taskId = (int) $taskId;
    $settingsID = (int) $settingsID;
    $userId = (int) $userId;
    $taskStat = (string) $taskStat;
    $note = trim((string) $note);
    $username = trim((string) $username);
    $date = trim((string) $date);

    if ($taskId <= 0 || $settingsID <= 0 || $date === '') {
      return;
    }

    $issue = $this->_support_issue_by_task($taskId, $settingsID);
    if (!$issue) {
      return;
    }

    $actorName = $username !== '' ? $username : ('Staff #' . $userId);
    $clientUserId = $this->_support_client_user_id($settingsID, (string) ($issue->customer_id ?? ''));

    if ($taskStat === '0') {
      $updateData = array(
        'status' => 'closed',
        'resolution_date' => $date,
        'resolved_by' => $userId > 0 ? $userId : null,
        'client_reply_required' => 0,
        'updated_at' => $date,
      );

      if ($note !== '') {
        $updateData['resolution_details'] = $note;
      }

      $this->db
        ->where('id', (int) $issue->id)
        ->where('settingsID', $settingsID)
        ->update('support_issues', $updateData);

      $movementComment = 'Support task marked closed by ' . $actorName . '.';
      if ($note !== '') {
        $movementComment .= ' Completion note: ' . $note;
      }

      $this->db->insert('support_issue_comments', array(
        'issue_id' => (int) $issue->id,
        'employee_id' => $userId > 0 ? $userId : null,
        'customer_comment' => 0,
        'comment' => $movementComment,
        'internal_note' => 0,
        'created_at' => date('Y-m-d H:i:s'),
        'settingsID' => $settingsID,
      ));

      if ($clientUserId > 0) {
        $this->_create_support_notification($settingsID, $clientUserId, $userId > 0 ? $userId : null, (int) $issue->id, (int) ($issue->department_id ?? 0), 'closed', 'Support Ticket Closed', 'Your ticket ' . (string) ($issue->ticket_number ?? ('#' . (int) $issue->id)) . ' was marked completed by the support team.');
      }

      $this->_send_support_email_notification(
        $settingsID,
        array((string) ($issue->customer_email ?? '')),
        'Support Ticket Closed: ' . (string) ($issue->ticket_number ?? ''),
        $note !== '' ? $note : 'Your support ticket was marked completed by the support team.',
        $issue
      );

      return;
    }

    if ($this->_support_is_closed_status((string) ($issue->status ?? ''))) {
      $reopenedStatus = ((int) ($issue->assigned_employee_id ?? 0) > 0) ? 'assigned' : 'open';

      $this->db
        ->where('id', (int) $issue->id)
        ->where('settingsID', $settingsID)
        ->update('support_issues', array(
          'status' => $reopenedStatus,
          'client_reply_required' => 0,
          'updated_at' => $date,
        ));

      $movementComment = 'Support task reopened by ' . $actorName . '.';
      if ($note !== '') {
        $movementComment .= ' Update: ' . $note;
      }

      $this->db->insert('support_issue_comments', array(
        'issue_id' => (int) $issue->id,
        'employee_id' => $userId > 0 ? $userId : null,
        'customer_comment' => 0,
        'comment' => $movementComment,
        'internal_note' => 0,
        'created_at' => date('Y-m-d H:i:s'),
        'settingsID' => $settingsID,
      ));

      if ($clientUserId > 0) {
        $this->_create_support_notification($settingsID, $clientUserId, $userId > 0 ? $userId : null, (int) $issue->id, (int) ($issue->department_id ?? 0), 'reopened', 'Support Ticket Reopened', 'Your ticket ' . (string) ($issue->ticket_number ?? ('#' . (int) $issue->id)) . ' was reopened by the support team.');
      }
    }
  }

  private function _ensure_support_issue_project_task($issue, $assignedUserId = 0)
  {
    if (!$issue) {
      return 0;
    }

    $settingsID = (int) ($issue->settingsID ?? 0);
    $issueId = (int) ($issue->id ?? 0);
    $projectId = max(0, (int) ($issue->project_id ?? 0));
    $assignedUserId = (int) $assignedUserId;

    if ($settingsID <= 0 || $issueId <= 0) {
      return 0;
    }

    $existingTask = $this->_support_issue_task_row((int) ($issue->task_id ?? 0), $settingsID);
    if ($existingTask) {
      $taskUpdate = array();

      if ($projectId > 0 && (int) ($existingTask->projectID ?? 0) <= 0) {
        $taskUpdate['projectID'] = $projectId;
      }

      if ($assignedUserId > 0 && (int) ($existingTask->assignedPerson ?? 0) <= 0) {
        $taskUpdate['assignedPerson'] = $assignedUserId;
      }

      if (!empty($taskUpdate)) {
        $this->db
          ->where('taskID', (int) $existingTask->taskID)
          ->where('settingsID', $settingsID)
          ->update('projects_task', $taskUpdate);
      }

      return (int) $existingTask->taskID;
    }

    $reportedDate = date('Y-m-d');
    $createdAt = trim((string) ($issue->created_at ?? ''));
    if ($createdAt !== '') {
      $createdTs = strtotime($createdAt);
      if ($createdTs !== false) {
        $reportedDate = date('Y-m-d', $createdTs);
      }
    }

    $taskLabel = trim((string) ($issue->title ?? ''));
    if ($taskLabel === '') {
      $taskLabel = 'Support Issue ' . trim((string) ($issue->ticket_number ?? $issueId));
    }
    $taskLabel = $this->_normalizeTaskLabel($taskLabel);

    $taskData = array(
      'taskID' => 0,
      'task' => $taskLabel,
      'reportedDate' => $reportedDate,
      'projectID' => $projectId,
      'taskStat' => '1',
      'priority' => $this->_support_issue_task_priority($issue->priority ?? 'medium'),
      'settingsID' => $settingsID,
      'assignedPerson' => $assignedUserId > 0 ? $assignedUserId : (int) ($issue->assigned_employee_id ?? 0),
      'added_by' => 'support_' . trim((string) ($issue->ticket_number ?? $issueId)),
    );

    if ($this->db->field_exists('dueDate', 'projects_task')) {
      $dueDate = null;
      $issueDueDate = trim((string) ($issue->due_date ?? ''));
      if ($issueDueDate !== '') {
        $dueDateTs = strtotime($issueDueDate);
        if ($dueDateTs !== false) {
          $dueDate = date('Y-m-d', $dueDateTs);
        }
      }
      $taskData['dueDate'] = $dueDate ?: $reportedDate;
    }

    if ($this->db->field_exists('attachment_link', 'projects_task')) {
      $taskData['attachment_link'] = null;
    }

    $this->db->insert('projects_task', $taskData);
    $taskId = (int) $this->db->insert_id();

    if ($taskId <= 0) {
      return 0;
    }

    $this->db
      ->where('id', $issueId)
      ->where('settingsID', $settingsID)
      ->update('support_issues', array(
        'task_id' => $taskId,
      ));

    $postedBy = trim((string) ($this->session->userdata('username') ?? 'system'));
    if ($postedBy === '') {
      $postedBy = 'system';
    }

    $note = 'Task created from support ticket ' . trim((string) ($issue->ticket_number ?? ('#' . $issueId))) . '.';
    $description = trim((string) ($issue->description ?? ''));
    if ($description !== '') {
      $note .= ' Details: ' . $description;
    }

    $statData = array(
      'taskID' => $taskId,
      'note' => $note,
      'datePosted' => date('Y-m-d H:i:s'),
      'postedBy' => $postedBy,
      'taskStat' => '1',
    );

    if ($this->db->field_exists('points', 'projects_task_stat')) {
      $statData['points'] = 1;
    }

    $this->db->insert('projects_task_stat', $statData);

    return $taskId;
  }

  private function _assign_support_issue_task($issue, $assignedUserId, $note, $updateIssue = true)
  {
    if (!$issue) {
      return 0;
    }

    $settingsID = (int) ($issue->settingsID ?? 0);
    $issueId = (int) ($issue->id ?? 0);
    $assignedUserId = (int) $assignedUserId;

    if ($settingsID <= 0 || $issueId <= 0 || $assignedUserId <= 0) {
      return 0;
    }

    $taskId = $this->_ensure_support_issue_project_task($issue, $assignedUserId);
    if ($taskId <= 0) {
      return 0;
    }

    $this->db
      ->where('taskID', $taskId)
      ->where('settingsID', $settingsID)
      ->update('projects_task', array(
        'assignedPerson' => $assignedUserId,
      ));

    if ($updateIssue) {
      $this->db
        ->where('id', $issueId)
        ->where('settingsID', $settingsID)
        ->update('support_issues', array(
          'task_id' => $taskId,
          'assigned_employee_id' => $assignedUserId,
          'status' => 'assigned',
        ));
    }

    $postedBy = trim((string) ($this->session->userdata('username') ?? 'system'));
    if ($postedBy === '') {
      $postedBy = 'system';
    }

    $statData = array(
      'taskID' => $taskId,
      'note' => $note,
      'datePosted' => date('Y-m-d H:i:s'),
      'postedBy' => $postedBy,
      'taskStat' => '1',
    );

    if ($this->db->field_exists('points', 'projects_task_stat')) {
      $statData['points'] = 1;
    }

    $this->db->insert('projects_task_stat', $statData);

    return $taskId;
  }

  private function _support_create_tagged_task_copy($issue, $tagUserId, $tagNote = '')
  {
    if (!$issue) {
      return 0;
    }

    $settingsID = (int) ($issue->settingsID ?? 0);
    $issueId = (int) ($issue->id ?? 0);
    $tagUserId = (int) $tagUserId;

    if ($settingsID <= 0 || $issueId <= 0 || $tagUserId <= 0) {
      return 0;
    }

    $currentTaskId = $this->_ensure_support_issue_project_task($issue, (int) ($issue->assigned_employee_id ?? 0));
    if ($currentTaskId <= 0) {
      return 0;
    }

    $originalTask = $this->db
      ->select('*')
      ->from('projects_task')
      ->where('taskID', $currentTaskId)
      ->where('settingsID', $settingsID)
      ->limit(1)
      ->get()
      ->row();

    if (!$originalTask) {
      return 0;
    }

    $employee = $this->_support_employee_row($settingsID, $tagUserId);
    if (!$employee) {
      return 0;
    }

    $employeeName = $this->_support_employee_name($employee);
    $username = trim((string) ($this->session->userdata('username') ?? 'system'));
    if ($username === '') {
      $username = 'system';
    }

    $date = date('Y-m-d H:i:s');
    $taskData = array(
      'taskID' => 0,
      'task' => $this->_normalizeTaskLabel(trim((string) ($originalTask->task ?? '')) . ' [Tagged: ' . $employeeName . ']'),
      'reportedDate' => $originalTask->reportedDate,
      'projectID' => $originalTask->projectID,
      'taskStat' => '1',
      'priority' => $originalTask->priority,
      'settingsID' => $settingsID,
      'assignedPerson' => $tagUserId,
      'added_by' => (string) ($originalTask->added_by ?? $username),
    );

    if ($this->db->field_exists('forwarded_from', 'projects_task')) {
      $taskData['forwarded_from'] = (int) $originalTask->taskID;
    }
    if ($this->db->field_exists('forwarded_to', 'projects_task')) {
      $taskData['forwarded_to'] = $tagUserId;
    }
    if ($this->db->field_exists('forwarded_by', 'projects_task')) {
      $taskData['forwarded_by'] = (int) ($this->session->userdata('user_id') ?? 0);
    }
    if ($this->db->field_exists('forwarded_note', 'projects_task')) {
      $taskData['forwarded_note'] = $tagNote;
    }
    if ($this->db->field_exists('forwarded_date', 'projects_task')) {
      $taskData['forwarded_date'] = $date;
    }

    if ($this->db->field_exists('dueDate', 'projects_task')) {
      $taskData['dueDate'] = $originalTask->dueDate;
    }

    if ($this->db->field_exists('attachment_link', 'projects_task')) {
      $taskData['attachment_link'] = $originalTask->attachment_link;
    }

    $this->db->insert('projects_task', $taskData);
    $taskId = (int) $this->db->insert_id();

    if ($taskId <= 0) {
      return 0;
    }

    $this->db->insert('projects_task_stat', array(
      'taskID' => $taskId,
      'note' => 'Tagged from support ticket ' . trim((string) ($issue->ticket_number ?? ('#' . $issueId))) . ' to ' . $employeeName . ($tagNote !== '' ? '. Note: ' . $tagNote : '.'),
      'datePosted' => $date,
      'postedBy' => $username,
      'taskStat' => '1',
    ));

    return $taskId;
  }

  private function _support_forward_issue_task($issue, $forwardToUserId, $forwardNote = '')
  {
    if (!$issue) {
      return 0;
    }

    $settingsID = (int) ($issue->settingsID ?? 0);
    $issueId = (int) ($issue->id ?? 0);
    $forwardToUserId = (int) $forwardToUserId;

    if ($settingsID <= 0 || $issueId <= 0 || $forwardToUserId <= 0) {
      return 0;
    }

    $currentTaskId = $this->_ensure_support_issue_project_task($issue, (int) ($issue->assigned_employee_id ?? 0));
    if ($currentTaskId <= 0) {
      return 0;
    }

    $requiredColumns = array('forwarded_from', 'forwarded_to', 'forwarded_by', 'forwarded_note', 'forwarded_date');
    foreach ($requiredColumns as $column) {
      if (!$this->db->field_exists($column, 'projects_task')) {
        return $this->_assign_support_issue_task($issue, $forwardToUserId, 'Support issue assigned without forwarded copy because task forwarding columns are missing.');
      }
    }

    $originalTask = $this->db
      ->select('*')
      ->from('projects_task')
      ->where('taskID', $currentTaskId)
      ->where('settingsID', $settingsID)
      ->limit(1)
      ->get()
      ->row();

    if (!$originalTask) {
      return 0;
    }

    $forwardToEmployee = $this->_support_employee_row($settingsID, $forwardToUserId);
    if (!$forwardToEmployee) {
      return 0;
    }

    $originalAssignee = $this->_support_employee_row($settingsID, (int) ($originalTask->assignedPerson ?? 0));
    $originalAssigneeName = $this->_support_employee_name($originalAssignee);
    if ($originalAssigneeName === '') {
      $originalAssigneeName = 'Current assignee';
    }

    $forwardedToName = $this->_support_employee_name($forwardToEmployee);
    $username = trim((string) ($this->session->userdata('username') ?? 'system'));
    if ($username === '') {
      $username = 'system';
    }
    $userId = (int) ($this->session->userdata('user_id') ?? 0);
    $date = date('Y-m-d H:i:s');

    $forwardedTaskData = array(
      'taskID' => 0,
      'task' => $this->_normalizeTaskLabel(trim((string) ($originalTask->task ?? '')) . ' [Forwarded from: ' . $originalAssigneeName . ']'),
      'reportedDate' => $originalTask->reportedDate,
      'projectID' => $originalTask->projectID,
      'taskStat' => '1',
      'priority' => $originalTask->priority,
      'settingsID' => $settingsID,
      'assignedPerson' => $forwardToUserId,
      'added_by' => (string) ($originalTask->added_by ?? $username),
      'forwarded_from' => (int) $originalTask->taskID,
      'forwarded_to' => $forwardToUserId,
      'forwarded_by' => $userId > 0 ? $userId : null,
      'forwarded_note' => $forwardNote,
      'forwarded_date' => $date,
    );

    if ($this->db->field_exists('dueDate', 'projects_task')) {
      $forwardedTaskData['dueDate'] = $originalTask->dueDate;
    }

    if ($this->db->field_exists('attachment_link', 'projects_task')) {
      $forwardedTaskData['attachment_link'] = $originalTask->attachment_link;
    }

    $this->db->insert('projects_task', $forwardedTaskData);
    $forwardedTaskId = (int) $this->db->insert_id();

    if ($forwardedTaskId <= 0) {
      return 0;
    }

    $forwardedTaskNote = 'Task forwarded from ' . $originalAssigneeName . ' to ' . $forwardedToName . ' from support ticket ' . trim((string) ($issue->ticket_number ?? ('#' . $issueId))) . '.';
    if (trim((string) $forwardNote) !== '') {
      $forwardedTaskNote .= ' Note: ' . trim((string) $forwardNote);
    }

    $this->db->insert('projects_task_stat', array(
      'taskID' => $forwardedTaskId,
      'note' => $forwardedTaskNote,
      'datePosted' => $date,
      'postedBy' => $username,
      'taskStat' => '1',
    ));

    $this->db->insert('projects_task_stat', array(
      'taskID' => (int) $originalTask->taskID,
      'note' => 'Task forwarded to ' . $forwardedToName . ' from support ticket ' . trim((string) ($issue->ticket_number ?? ('#' . $issueId))) . '.',
      'datePosted' => $date,
      'postedBy' => $username,
      'taskStat' => '1',
    ));

    $this->db
      ->where('id', $issueId)
      ->where('settingsID', $settingsID)
      ->update('support_issues', array(
        'task_id' => $forwardedTaskId,
        'assigned_employee_id' => $forwardToUserId,
        'status' => 'assigned',
      ));

    return $forwardedTaskId;
  }

  private function _client_support_projects($settingsID, $custID)
  {
    return $this->db
      ->select('projectID, projectDescription, projectCategory')
      ->from('projects')
      ->where('settingsID', (int) $settingsID)
      ->where('CustID', trim((string) $custID))
      ->order_by('projectDescription', 'ASC')
      ->get()
      ->result();
  }

  private function _client_support_issue_rows($settingsID, $custID)
  {
    $this->db
      ->select('si.*, d.department_name, p.projectDescription, CONCAT(u.fName, " ", u.lName) AS assigned_employee_name')
      ->from('support_issues si')
      ->join('support_departments d', 'd.id = si.department_id', 'left')
      ->join('projects p', 'p.projectID = si.project_id', 'left')
      ->join('users u', 'u.user_id = si.assigned_employee_id', 'left')
      ->where('si.settingsID', (int) $settingsID)
      ->where('si.customer_id', trim((string) $custID))
      ->where("TRIM(LOWER(COALESCE(si.status, ''))) IN ('open', 'pending')", null, false)
      ->order_by('si.created_at', 'DESC')
      ->limit(20);

    return $this->db->get()->result();
  }

  private function _client_ticket_rows($settingsID, $custID, $filter = 'open')
  {
    $filter = strtolower(trim((string) $filter));

    $this->db
      ->select('si.*, d.department_name, p.projectDescription, CONCAT(u.fName, " ", u.lName) AS assigned_employee_name')
      ->from('support_issues si')
      ->join('support_departments d', 'd.id = si.department_id', 'left')
      ->join('projects p', 'p.projectID = si.project_id', 'left')
      ->join('users u', 'u.user_id = si.assigned_employee_id', 'left')
      ->where('si.settingsID', (int) $settingsID)
      ->where('si.customer_id', trim((string) $custID));

    if ($this->db->table_exists('support_issue_cancel_logs')) {
      $this->db->join('support_issue_cancel_logs scl', 'scl.issue_id = si.id AND scl.settingsID = si.settingsID', 'left');
      $this->db->where('scl.id IS NULL', null, false);
    } else {
      $this->db->where("LOWER(TRIM(COALESCE(si.status, ''))) NOT IN ('cancelled','canceled')", null, false);
    }

    if ($filter === 'closed') {
      $this->db->where("LOWER(TRIM(COALESCE(si.status, ''))) IN ('closed','resolved','done','completed')", null, false);
    } elseif ($filter !== 'all') {
      $this->db->where("LOWER(TRIM(COALESCE(si.status, ''))) NOT IN ('closed','resolved','done','completed','cancelled','canceled')", null, false);
    }

    $rows = $this->db
      ->order_by('si.created_at', 'DESC')
      ->get()
      ->result();

    foreach ($rows as $row) {
      $row->can_cancel = $this->_client_ticket_can_cancel($row, $settingsID, $custID);
    }

    return $rows;
  }

  private function _cancelled_ticket_log_rows($settingsID, $custID = '')
  {
    $this->db
      ->select("
        si.*,
        d.department_name,
        p.projectDescription,
        CONCAT(u.fName, ' ', u.lName) AS assigned_employee_name,
        COALESCE(scl.cancel_note, cancel_comment.comment) AS cancellation_note,
        COALESCE(scl.created_at, cancel_comment.created_at) AS cancelled_at
      ")
      ->from('support_issues si')
      ->join('support_departments d', 'd.id = si.department_id', 'left')
      ->join('projects p', 'p.projectID = si.project_id', 'left')
      ->join('users u', 'u.user_id = si.assigned_employee_id', 'left')
      ->where('si.settingsID', (int) $settingsID);

    if ($this->db->table_exists('support_issue_cancel_logs')) {
      $this->db->join('support_issue_cancel_logs scl', 'scl.issue_id = si.id AND scl.settingsID = si.settingsID', 'inner');
      $this->db->join(
        'support_issue_comments cancel_comment',
        "cancel_comment.issue_id = si.id
        AND cancel_comment.settingsID = si.settingsID
        AND LOWER(TRIM(COALESCE(cancel_comment.comment, ''))) = 'ticket cancelled by client before support action started.'",
        'left'
      );
    } else {
      $this->db
        ->join(
          'support_issue_comments cancel_comment',
          "cancel_comment.issue_id = si.id
          AND cancel_comment.settingsID = si.settingsID
          AND LOWER(TRIM(COALESCE(cancel_comment.comment, ''))) = 'ticket cancelled by client before support action started.'",
          'left'
        )
        ->group_start()
        ->where('cancel_comment.id IS NOT NULL', null, false)
        ->or_where("LOWER(TRIM(COALESCE(si.status, ''))) IN ('cancelled','canceled')", null, false)
        ->group_end();
    }

    if (trim((string) $custID) !== '') {
      $this->db->where('si.customer_id', trim((string) $custID));
    }

    $rows = $this->db
      ->order_by('COALESCE(cancel_comment.created_at, si.updated_at, si.created_at)', 'DESC', false)
      ->get()
      ->result();

    foreach ($rows as $row) {
      $row->cancelled_by_label = 'Client';
      if (trim((string) ($row->cancelled_at ?? '')) === '') {
        $row->cancelled_at = (string) ($row->updated_at ?? $row->created_at ?? '');
      }
    }

    return $rows;
  }

  private function _client_ticket_can_cancel($issue, $settingsID, $custID)
  {
    if (!$issue) {
      return false;
    }

    if ((int) ($issue->settingsID ?? 0) !== (int) $settingsID) {
      return false;
    }

    if (trim((string) ($issue->customer_id ?? '')) !== trim((string) $custID)) {
      return false;
    }

    if ($this->_support_is_closed_status((string) ($issue->status ?? ''))) {
      return false;
    }

    if ($this->db->table_exists('support_issue_cancel_logs')) {
      $cancelLogCount = (int) $this->db
        ->from('support_issue_cancel_logs')
        ->where('settingsID', (int) $settingsID)
        ->where('issue_id', (int) ($issue->id ?? 0))
        ->count_all_results();

      if ($cancelLogCount > 0) {
        return false;
      }
    }

    if ((int) ($issue->assigned_employee_id ?? 0) > 0) {
      return false;
    }

    if ((int) ($issue->task_id ?? 0) > 0) {
      return false;
    }

    $staffCommentCount = (int) $this->db
      ->from('support_issue_comments')
      ->where('settingsID', (int) $settingsID)
      ->where('issue_id', (int) ($issue->id ?? 0))
      ->where('customer_comment', 0)
      ->count_all_results();

    return $staffCommentCount === 0;
  }

  private function _client_accomplished_task_rows($settingsID, $custID, $limit = 100)
  {
    $completionSubquery = "(SELECT taskID, MAX(ptsID) AS ptsID FROM projects_task_stat WHERE taskStat = '0' GROUP BY taskID) completed_task";

    $this->db
      ->select("
        projects_task.taskID,
        projects_task.task,
        projects_task.reportedDate,
        projects_task.dueDate,
        projects_task.priority,
        projects.projectDescription,
        projects.CustID,
        CONCAT(users.fName, ' ', users.lName) AS assignedPersonName,
        pts.datePosted AS completedDate,
        pts.note AS completionNote,
        pts.postedBy AS completedBy
      ")
      ->from('projects_task')
      ->join('projects', 'projects.projectID = projects_task.projectID', 'left')
      ->join('users', 'users.user_id = projects_task.assignedPerson', 'left')
      ->join($completionSubquery, 'completed_task.taskID = projects_task.taskID', 'left', false)
      ->join('projects_task_stat pts', 'pts.ptsID = completed_task.ptsID', 'left')
      ->where('projects_task.settingsID', (int) $settingsID)
      ->where('projects.CustID', trim((string) $custID))
      ->where('projects_task.taskStat', '0')
      ->order_by('pts.datePosted', 'DESC')
      ->order_by('projects_task.priority', 'ASC')
      ->order_by('projects_task.taskID', 'DESC');

    if ($limit !== null) {
      $this->db->limit((int) $limit);
    }

    return $this->db->get()->result();
  }

  private function _client_requested_today_rows($settingsID, $custID)
  {
    $today = date('Y-m-d');

    $this->db->select("
        projects_task.taskID,
        projects_task.task,
        projects_task.reportedDate,
        projects_task.dueDate,
        projects_task.priority,
        projects.projectDescription,
        projects.CustID,
        CONCAT(users.fName, ' ', users.lName) AS assignedPersonName
    ");
    $this->db->from('projects_task');
    $this->db->join('projects', 'projects.projectID = projects_task.projectID', 'left');
    $this->db->join('users', 'users.user_id = projects_task.assignedPerson', 'left');
    $this->db->where('projects_task.settingsID', (int) $settingsID);
    $this->db->where('projects.CustID', trim((string) $custID));
    $this->db->where('projects_task.reportedDate', $today);
    if ($this->db->field_exists('forwarded_from', 'projects_task')) {
      $this->db->group_start();
      $this->db->where('projects_task.forwarded_from IS NULL', null, false);
      $this->db->or_where('projects_task.forwarded_from', 0);
      $this->db->group_end();
    }
    if ($this->db->field_exists('added_by', 'projects_task')) {
      $this->db->group_start();
      $this->db->where('projects_task.added_by IS NULL', null, false);
      $this->db->or_where('projects_task.added_by NOT LIKE', 'support_%');
      $this->db->group_end();
    }
    $this->db->order_by('projects_task.reportedDate', 'DESC');
    $this->db->order_by('projects_task.priority', 'ASC');
    $this->db->order_by('projects_task.taskID', 'DESC');
    $this->db->limit(100);
    $requestedTodayTasks = $this->db->get()->result();

    $requestedToday = array();
    foreach ($requestedTodayTasks as $taskRow) {
      $requestedToday[] = (object) array(
        'itemType' => 'task',
        'title' => (string) ($taskRow->task ?? ''),
        'projectDescription' => (string) ($taskRow->projectDescription ?? ''),
        'assignedPersonName' => (string) ($taskRow->assignedPersonName ?? ''),
        'reportedDate' => (string) ($taskRow->reportedDate ?? ''),
        'dueDate' => (string) ($taskRow->dueDate ?? ''),
        'priority' => (string) ($taskRow->priority ?? '2'),
        'taskID' => (int) ($taskRow->taskID ?? 0),
      );
    }

    $supportTodayRows = $this->db
      ->select('ticket_number, title, projectDescription, created_at, status')
      ->from('support_issues si')
      ->join('projects p', 'p.projectID = si.project_id', 'left')
      ->where('si.settingsID', (int) $settingsID)
      ->where('si.customer_id', trim((string) $custID))
      ->where('DATE(si.created_at)', $today)
      ->order_by('si.created_at', 'DESC')
      ->get()
      ->result();

    foreach ($supportTodayRows as $issueRow) {
      $requestedToday[] = (object) array(
        'itemType' => 'support',
        'title' => (string) ($issueRow->title ?? ''),
        'projectDescription' => (string) ($issueRow->projectDescription ?? ''),
        'assignedPersonName' => 'Support Team',
        'reportedDate' => (string) ($issueRow->created_at ?? ''),
        'ticketNumber' => (string) ($issueRow->ticket_number ?? ''),
        'status' => (string) ($issueRow->status ?? ''),
      );
    }

    usort($requestedToday, function ($left, $right) {
      $leftDate = strtotime((string) ($left->reportedDate ?? '1970-01-01 00:00:00'));
      $rightDate = strtotime((string) ($right->reportedDate ?? '1970-01-01 00:00:00'));
      return $rightDate <=> $leftDate;
    });

    return $requestedToday;
  }

  private function _client_pending_task_rows($settingsID, $custID)
  {
    $latestCommentSubquery = "(SELECT taskID, MAX(ptsID) AS ptsID FROM projects_task_stat WHERE TRIM(COALESCE(note, '')) <> '' GROUP BY taskID) latest_comment";

    return $this->db
      ->select("
        projects_task.taskID,
        projects_task.task,
        projects_task.reportedDate,
        projects_task.dueDate,
        projects_task.priority,
        projects.projectDescription,
        projects.CustID,
        CONCAT(users.fName, ' ', users.lName) AS assignedPersonName,
        si.id AS supportIssueId,
        si.ticket_number AS supportTicketNumber
      ")
      ->from('projects_task')
      ->join('projects', 'projects.projectID = projects_task.projectID', 'left')
      ->join('users', 'users.user_id = projects_task.assignedPerson', 'left')
      ->join($latestCommentSubquery, 'latest_comment.taskID = projects_task.taskID', 'left', false)
      ->join('projects_task_stat pts', 'pts.ptsID = latest_comment.ptsID', 'left')
      ->join('support_issues si', 'si.task_id = projects_task.taskID AND si.settingsID = projects_task.settingsID', 'left')
      ->where('projects_task.settingsID', (int) $settingsID)
      ->where('projects.CustID', trim((string) $custID))
      ->where('projects_task.taskStat', '1')
      ->order_by('projects_task.dueDate', 'ASC')
      ->order_by('projects_task.priority', 'ASC')
      ->order_by('projects_task.taskID', 'DESC')
      ->limit(100)
      ->get()
      ->result();
  }

  private function _client_project_row($settingsID, $custID, $projectId)
  {
    return $this->db
      ->select('projectID, projectDescription, projectCategory, CustID, settingsID')
      ->from('projects')
      ->where('settingsID', (int) $settingsID)
      ->where('CustID', trim((string) $custID))
      ->where('projectID', (int) $projectId)
      ->limit(1)
      ->get()
      ->row();
  }

  private function _count_unassigned_support_issues($settingsID)
  {
    return (int) $this->db
      ->from('support_issues si')
      ->where('si.settingsID', (int) $settingsID)
      ->group_start()
      ->where('si.assigned_employee_id IS NULL', null, false)
      ->or_where('si.assigned_employee_id', 0)
      ->group_end()
      ->where("LOWER(TRIM(COALESCE(si.status, ''))) NOT IN ('closed','resolved','done','completed')", null, false)
      ->count_all_results();
  }

  private function _count_staff_unassigned_support_issues($settingsID, $userId)
  {
    $settingsID = (int) $settingsID;
    $userId = (int) $userId;

    if ($settingsID <= 0 || $userId <= 0) {
      return 0;
    }

    $departmentIds = $this->_staff_support_department_ids($settingsID, $userId);
    if (empty($departmentIds)) {
      return 0;
    }

    return (int) $this->db
      ->from('support_issues si')
      ->where('si.settingsID', $settingsID)
      ->where_in('si.department_id', $departmentIds)
      ->group_start()
      ->where('si.assigned_employee_id IS NULL', null, false)
      ->or_where('si.assigned_employee_id', 0)
      ->group_end()
      ->where("LOWER(TRIM(COALESCE(si.status, ''))) NOT IN ('closed','resolved','done','completed')", null, false)
      ->count_all_results();
  }

  private function _staff_pending_forwarded_task_ids($settingsID, $userId, $username)
  {
    $settingsID = (int) $settingsID;
    $userId = (int) $userId;
    $username = trim((string) $username);

    if ($settingsID <= 0 || $userId <= 0 || $username === '') {
      return array();
    }

    $usernameSql = $this->db->escape($username);
    $hasForwardedFrom = $this->db->field_exists('forwarded_from', 'projects_task');
    $rows = $this->db
      ->select('t.taskID')
      ->from('projects_task t')
      ->where('t.settingsID', $settingsID)
      ->where('t.assignedPerson', $userId)
      ->where('t.taskStat', '1')
      ->group_start();

    if ($hasForwardedFrom) {
      $this->db->where('t.forwarded_from >', 0);
      $this->db->or_like('t.added_by', 'support_', 'after');
    } else {
      $this->db->like('t.added_by', 'support_', 'after');
    }

    $rows = $this->db
      ->group_end()
      ->where("NOT EXISTS (SELECT 1 FROM projects_task_stat s WHERE s.taskID = t.taskID AND TRIM(COALESCE(s.postedBy, '')) = {$usernameSql})", null, false)
      ->get()
      ->result();

    return array_values(array_unique(array_filter(array_map(function ($row) {
      return (int) ($row->taskID ?? 0);
    }, $rows))));
  }

  private function _mark_forwarded_task_action($taskID, $settingsID, $userId, $username, $note = 'Forwarded task acknowledged.')
  {
    $taskID = (int) $taskID;
    $settingsID = (int) $settingsID;
    $userId = (int) $userId;
    $username = trim((string) $username);
    $note = trim((string) $note);

    if ($taskID <= 0 || $settingsID <= 0 || $userId <= 0 || $username === '' || $note === '') {
      return;
    }

    $taskRow = $this->db
      ->select('taskID, taskStat, forwarded_from, added_by')
      ->from('projects_task')
      ->where('taskID', $taskID)
      ->where('settingsID', $settingsID)
      ->where('assignedPerson', $userId)
      ->limit(1)
      ->get()
      ->row();

    $isForwardedTask = (int) ($taskRow->forwarded_from ?? 0) > 0;
    $isSupportAssignedTask = stripos((string) ($taskRow->added_by ?? ''), 'support_') === 0;

    if (!$taskRow || (!$isForwardedTask && !$isSupportAssignedTask)) {
      return;
    }

    $existingAction = $this->db
      ->select('ptsID')
      ->from('projects_task_stat')
      ->where('taskID', $taskID)
      ->where('TRIM(COALESCE(postedBy, "")) = ' . $this->db->escape($username), null, false)
      ->limit(1)
      ->get()
      ->row();

    if ($existingAction) {
      return;
    }

    $this->db->insert('projects_task_stat', array(
      'taskID' => $taskID,
      'note' => $note,
      'datePosted' => date('Y-m-d H:i:s'),
      'postedBy' => $username,
      'taskStat' => (string) ($taskRow->taskStat ?? '1'),
    ));
  }


  function index()
  {

    if ($this->session->userdata('level') === 'Admin') {

      $this->load->view('dashboard_admin');
    } else {
      echo "Access Denied";
    }
  }

  function admin()
  {

    if ($this->session->userdata('level') === 'Admin') {
      $settingsID = $this->session->userdata('settingsID');
      $user_id = $this->session->userdata('user_id');
      date_default_timezone_set('Asia/Manila'); # add your city to set local time zone

      $date = date('Y-m-d');
      $month = date('n');
      $year = date('Y');

      $result['data'] = $this->CashModel->todaysPayments($settingsID, $date);
      $result['data1'] = $this->CashModel->todayExpenses($settingsID, $date);
      $result['data3'] = $this->CashModel->totalClients($settingsID);
      $result['data4'] = $this->CashModel->receivableCounts($settingsID);
      $result['prospectClients'] = $this->CashModel->getClientsByStatus($settingsID, 'Prospect');
      $result['accomplishedSummary'] = $this->CashModel->getAccomplishedTaskSummary($settingsID, $month, $year);
      $result['taskDueTodayCount'] = $this->CashModel->countOpenTasksDueToday($settingsID, null, $date);
      $result['taskDueSoonCount'] = $this->CashModel->countOpenTasksDueSoon($settingsID, 7, null, $date);
      $result['taskOverdueCount'] = $this->CashModel->countOpenTasksOverdue($settingsID, null, $date);
      $result['taskWithoutDueDateCount'] = $this->CashModel->countOpenTasksWithoutDueDate($settingsID);
      $result['taskDueQueue'] = $this->CashModel->openTaskDueQueue($settingsID, null, 6, 7, $date);
      $result['taskDueWindowDays'] = 7;
      $result['unassignedTicketCount'] = $this->_count_unassigned_support_issues($settingsID);

      // Auto-create user_reminders table if it doesn't exist
      if (!$this->db->table_exists('user_reminders')) {
        $this->db->query("CREATE TABLE IF NOT EXISTS `user_reminders` (
          `reminder_id` int(11) NOT NULL AUTO_INCREMENT,
          `user_id` int(11) NOT NULL,
          `settingsID` int(11) NOT NULL,
          `title` varchar(255) NOT NULL,
          `description` text,
          `frequency` enum('daily','weekly','monthly','yearly') NOT NULL,
          `start_date` date NOT NULL,
          `next_reminder_date` date NOT NULL,
          `reminder_days_before` int(11) DEFAULT 3,
          `is_active` tinyint(1) DEFAULT 1,
          `last_sent_date` date DEFAULT NULL,
          `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
          `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
          PRIMARY KEY (`reminder_id`),
          KEY `user_id` (`user_id`),
          KEY `settingsID` (`settingsID`),
          KEY `next_reminder_date` (`next_reminder_date`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
      }

      // Get user reminders and send email notifications
      $result['reminders'] = array();
      if ($this->db->table_exists('user_reminders')) {
        $result['reminders'] = $this->db
          ->where('user_id', $user_id)
          ->where('is_active', 1)
          ->order_by('next_reminder_date', 'ASC')
          ->get('user_reminders')
          ->result();

        // Check and send email reminders
        $todayDateTime = new DateTime();
        foreach ($result['reminders'] as $reminder) {
          if (!empty($reminder->next_reminder_date)) {
            $reminderDate = new DateTime($reminder->next_reminder_date);
            $daysBefore = (int) ($reminder->reminder_days_before ?? 3);
            $alertDate = clone $reminderDate;
            $alertDate->sub(new DateInterval('P' . $daysBefore . 'D'));

            // Send email if today is within the reminder window and not already sent today
            if ($todayDateTime >= $alertDate && $todayDateTime <= $reminderDate) {
              $lastSent = !empty($reminder->last_sent_date) ? new DateTime($reminder->last_sent_date) : null;
              
              // Only send if not sent today
              if (!$lastSent || $lastSent->format('Y-m-d') !== $todayDateTime->format('Y-m-d')) {
                $user = $this->db->where('user_id', $user_id)->get('users')->row();
                if ($user && !empty($user->email)) {
                  $emailSent = $this->sendReminderEmail($reminder, $user->email, $user->fName);
                  
                  if ($emailSent) {
                    // Update last_sent_date
                    $this->db->where('reminder_id', $reminder->reminder_id);
                    $this->db->update('user_reminders', array('last_sent_date' => $todayDateTime->format('Y-m-d')));
                  }
                }
              }
            }

            // Update next_reminder_date if it has passed
            if ($todayDateTime > $reminderDate) {
              $nextDate = clone $reminderDate;
              switch ($reminder->frequency) {
                case 'daily':
                  $nextDate->add(new DateInterval('P1D'));
                  break;
                case 'weekly':
                  $nextDate->add(new DateInterval('P7D'));
                  break;
                case 'monthly':
                  $nextDate->add(new DateInterval('P1M'));
                  break;
                case 'yearly':
                  $nextDate->add(new DateInterval('P1Y'));
                  break;
              }
              
              // Keep advancing until nextDate is in the future
              while ($nextDate <= $todayDateTime) {
                switch ($reminder->frequency) {
                  case 'daily':
                    $nextDate->add(new DateInterval('P1D'));
                    break;
                  case 'weekly':
                    $nextDate->add(new DateInterval('P7D'));
                    break;
                  case 'monthly':
                    $nextDate->add(new DateInterval('P1M'));
                    break;
                  case 'yearly':
                    $nextDate->add(new DateInterval('P1Y'));
                    break;
                }
              }

              $this->db->where('reminder_id', $reminder->reminder_id);
              $this->db->update('user_reminders', array(
                'next_reminder_date' => $nextDate->format('Y-m-d'),
                'last_sent_date' => null
              ));
            }
          }
        }
      }

      $this->load->view('dashboard_admin', $result);
    } else {
      echo "Access Denied";
    }
  }

  function superAdmin()
  {
    if ($this->_is_system_admin_user()) {
      date_default_timezone_set('Asia/Manila');

      $user_id = $this->session->userdata('user_id');

      // Get all companies/settings
      $result['companies'] = $this->db->get('pos_settings')->result();

      // Get total counts across all companies
      $result['totalCompanies'] = count($result['companies']);
      $result['totalUsers'] = $this->db->count_all('users');
      $result['totalClients'] = $this->db->count_all('customers');

      // Auto-create user_reminders table if it doesn't exist
      if (!$this->db->table_exists('user_reminders')) {
        $this->db->query("CREATE TABLE IF NOT EXISTS `user_reminders` (
          `reminder_id` int(11) NOT NULL AUTO_INCREMENT,
          `user_id` int(11) NOT NULL,
          `settingsID` int(11) NOT NULL,
          `title` varchar(255) NOT NULL,
          `description` text,
          `frequency` enum('daily','weekly','monthly','yearly') NOT NULL,
          `start_date` date NOT NULL,
          `next_reminder_date` date NOT NULL,
          `reminder_days_before` int(11) DEFAULT 3,
          `is_active` tinyint(1) DEFAULT 1,
          `last_sent_date` date DEFAULT NULL,
          `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
          `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
          PRIMARY KEY (`reminder_id`),
          KEY `user_id` (`user_id`),
          KEY `settingsID` (`settingsID`),
          KEY `next_reminder_date` (`next_reminder_date`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
      }

      // Get user reminders and send email notifications
      $result['reminders'] = array();
      if ($this->db->table_exists('user_reminders') && $user_id) {
        $result['reminders'] = $this->db
          ->where('user_id', $user_id)
          ->where('is_active', 1)
          ->order_by('next_reminder_date', 'ASC')
          ->get('user_reminders')
          ->result();

        // Check and send email reminders
        $todayDateTime = new DateTime();
        foreach ($result['reminders'] as $reminder) {
          if (!empty($reminder->next_reminder_date)) {
            $reminderDate = new DateTime($reminder->next_reminder_date);
            $daysBefore = (int) ($reminder->reminder_days_before ?? 3);
            $alertDate = clone $reminderDate;
            $alertDate->sub(new DateInterval('P' . $daysBefore . 'D'));

            // Send email if today is within the reminder window and not already sent today
            if ($todayDateTime >= $alertDate && $todayDateTime <= $reminderDate) {
              $lastSent = !empty($reminder->last_sent_date) ? new DateTime($reminder->last_sent_date) : null;
              
              // Only send if not sent today
              if (!$lastSent || $lastSent->format('Y-m-d') !== $todayDateTime->format('Y-m-d')) {
                $user = $this->db->where('user_id', $user_id)->get('users')->row();
                if ($user && !empty($user->email)) {
                  $emailSent = $this->sendReminderEmail($reminder, $user->email, $user->fName);
                  
                  if ($emailSent) {
                    // Update last_sent_date
                    $this->db->where('reminder_id', $reminder->reminder_id);
                    $this->db->update('user_reminders', array('last_sent_date' => $todayDateTime->format('Y-m-d')));
                  }
                }
              }
            }

            // Update next_reminder_date if it has passed
            if ($todayDateTime > $reminderDate) {
              $nextDate = clone $reminderDate;
              switch ($reminder->frequency) {
                case 'daily':
                  $nextDate->add(new DateInterval('P1D'));
                  break;
                case 'weekly':
                  $nextDate->add(new DateInterval('P7D'));
                  break;
                case 'monthly':
                  $nextDate->add(new DateInterval('P1M'));
                  break;
                case 'yearly':
                  $nextDate->add(new DateInterval('P1Y'));
                  break;
              }
              
              // Keep advancing until nextDate is in the future
              while ($nextDate <= $todayDateTime) {
                switch ($reminder->frequency) {
                  case 'daily':
                    $nextDate->add(new DateInterval('P1D'));
                    break;
                  case 'weekly':
                    $nextDate->add(new DateInterval('P7D'));
                    break;
                  case 'monthly':
                    $nextDate->add(new DateInterval('P1M'));
                    break;
                  case 'yearly':
                    $nextDate->add(new DateInterval('P1Y'));
                    break;
                }
              }

              $this->db->where('reminder_id', $reminder->reminder_id);
              $this->db->update('user_reminders', array(
                'next_reminder_date' => $nextDate->format('Y-m-d'),
                'last_sent_date' => null
              ));
            }
          }
        }
      }

      $this->load->view('dashboard_super_admin', $result);
    } else {
      echo "Access Denied";
    }
  }

  function superAdminCompanies()
  {
    if (!$this->_is_system_admin_user()) {
      echo "Access Denied";
      return;
    }

    $result['companies'] = $this->db->get('pos_settings')->result();
    $result['billingModeOptions'] = $this->_getCompanyBillingModeOptions();
    $this->load->view('super_admin_companies', $result);
  }

  function superAdminBilling()
  {
    if (!$this->_is_system_admin_user()) {
      echo "Access Denied";
      return;
    }

    $companies = $this->db->order_by('CompName', 'ASC')->get('pos_settings')->result();
    $billingSummaries = array();
    $overviewTotals = array(
      'company_count' => count($companies),
      'expected_monthly_charge' => 0.00,
      'total_due' => 0.00,
      'total_paid' => 0.00,
      'outstanding_balance' => 0.00,
    );

    foreach ($companies as $company) {
      $settingsID = (int) ($company->settingsID ?? 0);
      $this->_syncCompanyRecurringBillingRecords($company);
      $billingSummaries[$settingsID] = $this->_buildCompanyBillingSummary($company);
      $overviewTotals['expected_monthly_charge'] += (float) $billingSummaries[$settingsID]['expected_monthly_charge'];
      $overviewTotals['total_due'] += (float) $billingSummaries[$settingsID]['total_due'];
      $overviewTotals['total_paid'] += (float) $billingSummaries[$settingsID]['total_paid'];
      $overviewTotals['outstanding_balance'] += (float) $billingSummaries[$settingsID]['outstanding_balance'];
    }

    $overviewTotals['expected_monthly_charge'] = round($overviewTotals['expected_monthly_charge'], 2);
    $overviewTotals['total_due'] = round($overviewTotals['total_due'], 2);
    $overviewTotals['total_paid'] = round($overviewTotals['total_paid'], 2);
    $overviewTotals['outstanding_balance'] = round($overviewTotals['outstanding_balance'], 2);

    $result['companies'] = $companies;
    $result['billingSummaries'] = $billingSummaries;
    $result['billingModeOptions'] = $this->_getCompanyBillingModeOptions();
    $result['overviewTotals'] = $overviewTotals;
    $this->load->view('super_admin_billing', $result);
  }

  function superAdminCompanyBilling()
  {
    if (!$this->_is_system_admin_user()) {
      echo "Access Denied";
      return;
    }

    $settingsID = (int) $this->input->get('settingsID');
    if ($settingsID <= 0) {
      echo "Invalid company";
      return;
    }

    $company = $this->db->where('settingsID', $settingsID)->get('pos_settings')->row();
    if (!$company) {
      show_404();
      return;
    }

    $generatedRecurringCount = $this->_syncCompanyRecurringBillingRecords($company);
    $billingSummary = $this->_buildCompanyBillingSummary($company);
    
    $billingRecords = array();
    if ($this->db->table_exists('company_billing_records')) {
      $billingRecords = $this->db
        ->where('settingsID', $settingsID)
        ->order_by('billing_month', 'DESC')
        ->order_by('billing_id', 'DESC')
        ->get('company_billing_records')
        ->result();
    }

    $paymentHistory = array();
    if ($this->db->table_exists('company_billing_payments')) {
      $paymentHistory = $this->db
        ->where('settingsID', $settingsID)
        ->order_by('payment_date', 'DESC')
        ->order_by('payment_id', 'DESC')
        ->get('company_billing_payments')
        ->result();
    }

    $result['company'] = $company;
    $result['billingSummary'] = $billingSummary;
    $result['billingRecords'] = $billingRecords;
    $result['paymentHistory'] = $paymentHistory;
    $result['billingModeOptions'] = $this->_getCompanyBillingModeOptions();
    $result['defaultBillingMonth'] = date('Y-m', strtotime((string) ($billingSummary['next_billing_month'] ?? date('Y-m-01'))));
    $result['defaultBillableUnits'] = $billingSummary['billing_mode'] === 'individual' ? $billingSummary['billable_users'] : 1;
    $result['generatedRecurringCount'] = $generatedRecurringCount;
    $this->load->view('super_admin_company_billing', $result);
  }

  function superAdminCompanyFeatures()
  {
    if (!$this->_is_system_admin_user()) {
      echo "Access Denied";
      return;
    }

    $settingsID = (int) $this->input->get('settingsID');

    if ($settingsID <= 0) {
      echo "Invalid company";
      return;
    }

    $this->db->where('settingsID', $settingsID);
    $result['company'] = $this->db->get('pos_settings')->row();
    if (!$result['company']) {
      show_404();
      return;
    }

    $result['currentPackageIds'] = $this->_getCompanySelectedPackageIds($result['company']);
    $result['featurePackages'] = $this->_getCompanyFeaturePackages();
    $result['featureLabels'] = $this->_getCompanyFeatureCatalog();
    $this->load->view('super_admin_company_features', $result);
  }

  function superAdminSignupPackages()
  {
    if ($this->session->userdata('level') !== 'Super Admin') {
      echo "Access Denied";
      return;
    }

    // Get all signup packages with their enabled status
    $this->db->order_by('package_id', 'ASC');
    $result['signupPackages'] = $this->db->get('signup_packages')->result();
    
    // Get package definitions
    $result['featurePackages'] = $this->_getCompanyFeaturePackages();
    $result['featureLabels'] = $this->_getCompanyFeatureCatalog();
    
    $this->load->view('super_admin_signup_packages', $result);
  }

  function saveSignupPackages()
  {
    header('Content-Type: application/json');

    if ($this->session->userdata('level') !== 'Super Admin') {
      echo json_encode(array('success' => false, 'message' => 'Access Denied'));
      return;
    }

    // Ensure table exists
    $this->_ensureSignupPackagesTable();

    $enabledPackages = $this->input->post('enabled_packages');
    
    if (!is_array($enabledPackages)) {
      echo json_encode(array('success' => false, 'message' => 'Invalid data'));
      return;
    }

    // Validate package IDs
    $validPackages = array('all', '1', '2', '3', '4');
    foreach ($enabledPackages as $pkgId) {
      if (!in_array($pkgId, $validPackages)) {
        echo json_encode(array('success' => false, 'message' => 'Invalid package ID'));
        return;
      }
    }

    // Update all packages to disabled first
    $this->db->update('signup_packages', array('is_enabled' => 0));

    // Enable selected packages
    $timestamp = date('Y-m-d H:i:s');
    foreach ($enabledPackages as $pkgId) {
      $this->db->where('package_id', $pkgId);
      $this->db->update('signup_packages', array(
        'is_enabled' => 1,
        'updated_at' => $timestamp
      ));
    }

    echo json_encode(array('success' => true, 'message' => 'Signup packages updated successfully'));
  }

  function resetSignupPackagesToPackage2()
  {
    header('Content-Type: application/json');

    if ($this->session->userdata('level') !== 'Super Admin') {
      echo json_encode(array('success' => false, 'message' => 'Access Denied'));
      return;
    }

    // Ensure table exists
    $this->_ensureSignupPackagesTable();

    // Disable all packages
    $this->db->update('signup_packages', array('is_enabled' => 0));

    // Enable only package 2
    $timestamp = date('Y-m-d H:i:s');
    $this->db->where('package_id', '2');
    $this->db->update('signup_packages', array(
      'is_enabled' => 1,
      'updated_at' => $timestamp
    ));

    echo json_encode(array('success' => true, 'message' => 'Signup packages reset to only Package 2'));
  }

  function superAdminRecaptchaSettings()
  {
    if ($this->session->userdata('level') !== 'Super Admin') {
      echo "Access Denied";
      return;
    }

    // Get reCAPTCHA settings
    $this->db->limit(1);
    $result['recaptchaSettings'] = $this->db->get('recaptcha_settings')->row();
    
    $this->load->view('super_admin_recaptcha_settings', $result);
  }

  function saveRecaptchaSettings()
  {
    header('Content-Type: application/json');

    if ($this->session->userdata('level') !== 'Super Admin') {
      echo json_encode(array('success' => false, 'message' => 'Access Denied'));
      return;
    }

    // Ensure table exists
    $this->_ensureRecaptchaSettingsTable();

    $siteKey = $this->input->post('site_key');
    $secretKey = $this->input->post('secret_key');
    $isEnabled = $this->input->post('is_enabled');
    $recaptchaVersion = $this->input->post('recaptcha_version');

    if (empty($siteKey) || empty($secretKey)) {
      echo json_encode(array('success' => false, 'message' => 'Site Key and Secret Key are required'));
      return;
    }

    // Update settings
    $timestamp = date('Y-m-d H:i:s');
    $updateData = array(
      'site_key' => $siteKey,
      'secret_key' => $secretKey,
      'is_enabled' => $isEnabled ? 1 : 0,
      'recaptcha_version' => $recaptchaVersion ? $recaptchaVersion : 'v2',
      'updated_at' => $timestamp
    );

    $this->db->limit(1);
    $this->db->update('recaptcha_settings', $updateData);

    echo json_encode(array('success' => true, 'message' => 'reCAPTCHA settings updated successfully'));
  }

  function superAdminSettings()
  {
    if ($this->session->userdata('level') !== 'Super Admin') {
      echo "Access Denied";
      return;
    }

    // Get signup packages
    $this->db->order_by('package_id', 'ASC');
    $result['signupPackages'] = $this->db->get('signup_packages')->result();
    
    // Get package definitions
    $result['featurePackages'] = $this->_getCompanyFeaturePackages();
    $result['featureLabels'] = $this->_getCompanyFeatureCatalog();
    
    // Get reCAPTCHA settings
    $this->db->limit(1);
    $result['recaptchaSettings'] = $this->db->get('recaptcha_settings')->row();
    
    $this->load->view('super_admin_settings', $result);
  }

  function superAdminAdmins()
  {
    if (!$this->_is_system_admin_user()) {
      echo "Access Denied";
      return;
    }

    $settingsID = $this->input->get('settingsID');
    $result['companies'] = $this->db->get('pos_settings')->result();

    // Filter admins by settingsID if provided
    if ($settingsID) {
      $this->db->where('settingsID', (int) $settingsID);
    }
    $result['admins'] = $this->db->where('position', 'Admin')->get('users')->result();

    // Pass the filter settingsID to the view
    $result['filterSettingsID'] = $settingsID ? (int) $settingsID : null;

    $this->load->view('super_admin_admins', $result);
  }

  function superAdminUsers()
  {
    if (!$this->_is_system_admin_user()) {
      echo "Access Denied";
      return;
    }

    $settingsID = $this->input->get('settingsID');
    $result['companies'] = $this->db->get('pos_settings')->result();

    // Filter users by settingsID if provided
    if ($settingsID) {
      $this->db->where('settingsID', (int) $settingsID);
    }
    $result['users'] = $this->db->get('users')->result();

    // Pass the filter settingsID to the view
    $result['filterSettingsID'] = $settingsID ? (int) $settingsID : null;

    $this->load->view('super_admin_users', $result);
  }

  function saveSuperAdminUser()
  {
    header('Content-Type: application/json');

    if (!$this->_is_system_admin_user()) {
      echo json_encode(array('success' => false, 'message' => 'Access Denied'));
      return;
    }

    $userId = (int) $this->input->post('user_id');
    $settingsID = (int) $this->input->post('settingsID');
    $email = trim((string) $this->input->post('email'));
    $password = trim((string) $this->input->post('password'));
    $fName = trim((string) $this->input->post('fName'));
    $lName = trim((string) $this->input->post('lName'));
    $position = trim((string) $this->input->post('position'));
    $acctStat = trim((string) $this->input->post('acctStat'));

    // Use email as username
    $username = $email;

    if ($email === '') {
      echo json_encode(array('success' => false, 'message' => 'Email is required'));
      return;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
      echo json_encode(array('success' => false, 'message' => 'Invalid email format'));
      return;
    }

    $data = array(
      'username' => $username,
      'email' => $email,
      'fName' => $fName,
      'lName' => $lName,
      'position' => $position,
      'acctStat' => $acctStat
    );

    if ($password !== '') {
      $data['password'] = password_hash($password, PASSWORD_BCRYPT);
    }

    if ($userId > 0) {
      // Update existing user
      $this->db->where('user_id', $userId);
      $result = $this->db->update('users', $data);
      $dbError = $this->db->error();
      if (!$result) {
        error_log('Update user error: ' . print_r($dbError, true));
        echo json_encode(array('success' => false, 'message' => 'Error saving user: ' . ($dbError['message'] ?? 'Unknown error')));
        return;
      }
    } else {
      // Create new user
      if ($settingsID <= 0) {
        echo json_encode(array('success' => false, 'message' => 'Company is required'));
        return;
      }

      if ($password === '') {
        echo json_encode(array('success' => false, 'message' => 'Password is required for new users'));
        return;
      }

      $data['settingsID'] = $settingsID;
      
      // Only add created_at if the field exists
      if ($this->db->field_exists('created_at', 'users')) {
        $data['created_at'] = date('Y-m-d H:i:s');
      }

      // Check if email already exists (email is used as username)
      $existingEmail = $this->db->where('email', $email)->get('users')->row();
      if ($existingEmail) {
        echo json_encode(array('success' => false, 'message' => 'Email already exists'));
        return;
      }

      $result = $this->db->insert('users', $data);
      $dbError = $this->db->error();
      if (!$result) {
        error_log('Insert user error: ' . print_r($dbError, true));
        echo json_encode(array('success' => false, 'message' => 'Error saving user: ' . ($dbError['message'] ?? 'Unknown error')));
        return;
      }
    }

    echo json_encode(array('success' => true, 'message' => 'User saved successfully'));
  }

  function deleteSuperAdminUser()
  {
    header('Content-Type: application/json');

    if (!$this->_is_system_admin_user()) {
      echo json_encode(array('success' => false, 'message' => 'Access Denied'));
      return;
    }

    $userId = (int) $this->input->post('user_id');

    if ($userId <= 0) {
      echo json_encode(array('success' => false, 'message' => 'Invalid user ID'));
      return;
    }

    $this->db->where('user_id', $userId);
    $result = $this->db->delete('users');

    if ($result) {
      echo json_encode(array('success' => true, 'message' => 'User deleted successfully'));
    } else {
      echo json_encode(array('success' => false, 'message' => 'Error deleting user'));
    }
  }

  function saveCompany()
  {
    header('Content-Type: application/json');

    if (!$this->_is_system_admin_user()) {
      echo json_encode(array('success' => false, 'message' => 'Access Denied'));
      return;
    }

    $settingsID = (int) $this->input->post('settingsID');
    $compName = trim((string) $this->input->post('CompName'));
    $businessName = trim((string) $this->input->post('BusinessName'));
    $compEmail = trim((string) $this->input->post('CompEmail'));
    $compPhone = trim((string) $this->input->post('CompPhone'));
    $compAddress = trim((string) $this->input->post('CompAddress'));
    $compType = trim((string) $this->input->post('CompType'));
    $billingMode = $this->_normalizeCompanyBillingMode($this->input->post('billing_mode'));
    $monthlyRateInput = str_replace(',', '', trim((string) $this->input->post('monthly_rate')));

    if ($billingMode !== 'free' && $monthlyRateInput === '') {
      echo json_encode(array('success' => false, 'message' => 'Monthly rate is required for paid billing modes'));
      return;
    }

    if ($monthlyRateInput === '') {
      $monthlyRate = 0.00;
    } elseif (!is_numeric($monthlyRateInput) || (float) $monthlyRateInput < 0) {
      echo json_encode(array('success' => false, 'message' => 'Monthly rate must be a valid positive amount'));
      return;
    } else {
      $monthlyRate = round((float) $monthlyRateInput, 2);
    }

    if ($compName === '' && $businessName === '') {
      echo json_encode(array('success' => false, 'message' => 'Company name is required'));
      return;
    }

    $data = array(
      'CompName' => $compName,
      'BusinessName' => $businessName !== '' ? $businessName : $compName,
      'CompEmail' => $compEmail,
      'CompPhone' => $compPhone,
      'CompAddress' => $compAddress,
      'billing_mode' => $billingMode,
      'monthly_rate' => $monthlyRate,
    );

    if ($this->db->field_exists('CompType', 'pos_settings')) {
      $data['CompType'] = $compType;
    }

    if ($settingsID > 0) {
      // Update existing
      $this->db->where('settingsID', $settingsID);
      $result = $this->db->update('pos_settings', $data);
    } else {
      // Create new
      $result = $this->db->insert('pos_settings', $data);
      $settingsID = $this->db->insert_id();
    }

    if ($result) {
      echo json_encode(array('success' => true, 'message' => 'Company saved successfully', 'settingsID' => $settingsID));
    } else {
      echo json_encode(array('success' => false, 'message' => 'Failed to save company'));
    }
  }

  function saveCompanyBillingEntry()
  {
    header('Content-Type: application/json');

    if (!$this->_is_system_admin_user()) {
      echo json_encode(array('success' => false, 'message' => 'Access Denied'));
      return;
    }

    $settingsID = (int) $this->input->post('settingsID');
    $billingMonth = $this->_normalizeBillingMonthInput($this->input->post('billing_month'));
    $billingMode = $this->_normalizeCompanyBillingMode($this->input->post('billing_mode'));
    $billableUnits = max(0, (int) $this->input->post('billable_units'));
    $rateInput = str_replace(',', '', trim((string) $this->input->post('rate_per_month')));
    $notes = trim((string) $this->input->post('notes'));

    if ($settingsID <= 0) {
      echo json_encode(array('success' => false, 'message' => 'Invalid company'));
      return;
    }

    if ($billingMonth === '') {
      echo json_encode(array('success' => false, 'message' => 'Billing month is required'));
      return;
    }

    if ($rateInput === '' || !is_numeric($rateInput) || (float) $rateInput < 0) {
      echo json_encode(array('success' => false, 'message' => 'Rate per month must be a valid positive amount'));
      return;
    }

    $company = $this->db->where('settingsID', $settingsID)->get('pos_settings')->row();
    if (!$company) {
      echo json_encode(array('success' => false, 'message' => 'Company not found'));
      return;
    }

    $existingRecord = $this->_getCompanyBillingRecordByMonth($settingsID, $billingMonth);
    if ($existingRecord) {
      echo json_encode(array(
        'success' => false,
        'message' => 'A recurring billing entry for ' . date('F Y', strtotime($billingMonth)) . ' already exists',
      ));
      return;
    }

    $result = $this->_createCompanyBillingRecord($company, $billingMonth, array(
      'billing_mode' => $billingMode,
      'billable_units' => $billableUnits,
      'rate_per_month' => round((float) $rateInput, 2),
      'notes' => $notes,
    ));

    if (empty($result['success'])) {
      echo json_encode(array('success' => false, 'message' => (string) ($result['message'] ?? 'Failed to create billing entry')));
      return;
    }

    $generatedRecurringCount = $this->_syncCompanyRecurringBillingRecords($company);
    $message = 'Billing entry created successfully';
    if ($generatedRecurringCount > 0) {
      $message .= '. ' . $generatedRecurringCount . ' missing recurring month' . ($generatedRecurringCount === 1 ? ' was' : 's were') . ' also generated.';
    }

    echo json_encode(array(
      'success' => true,
      'message' => $message,
      'billing_id' => (int) ($result['billing_id'] ?? 0),
      'generated_count' => $generatedRecurringCount,
    ));
  }

  function recordCompanyBillingPayment()
  {
    header('Content-Type: application/json');

    if (!$this->_is_system_admin_user()) {
      echo json_encode(array('success' => false, 'message' => 'Access Denied'));
      return;
    }

    $billingID = (int) $this->input->post('billing_id');
    $amountInput = str_replace(',', '', trim((string) $this->input->post('amount_paid')));
    $paymentDateInput = trim((string) $this->input->post('payment_date'));
    $paymentMethod = trim((string) $this->input->post('payment_method'));
    $referenceNo = trim((string) $this->input->post('reference_no'));
    $notes = trim((string) $this->input->post('notes'));

    if ($billingID <= 0) {
      echo json_encode(array('success' => false, 'message' => 'Invalid billing entry'));
      return;
    }

    if ($amountInput === '' || !is_numeric($amountInput) || (float) $amountInput <= 0) {
      echo json_encode(array('success' => false, 'message' => 'Payment amount must be greater than zero'));
      return;
    }

    $paymentTimestamp = $paymentDateInput !== '' ? strtotime($paymentDateInput) : false;
    if ($paymentDateInput !== '' && $paymentTimestamp === false) {
      echo json_encode(array('success' => false, 'message' => 'Invalid payment date'));
      return;
    }

    $record = $this->db->where('billing_id', $billingID)->get('company_billing_records')->row();
    if (!$record) {
      echo json_encode(array('success' => false, 'message' => 'Billing entry not found'));
      return;
    }

    $amountDue = round((float) ($record->amount_due ?? 0), 2);
    $currentAmountPaid = round((float) ($record->amount_paid ?? 0), 2);
    if ($amountDue <= 0) {
      echo json_encode(array('success' => false, 'message' => 'This billing entry does not require payment'));
      return;
    }

    if ($currentAmountPaid >= $amountDue) {
      echo json_encode(array('success' => false, 'message' => 'This billing entry is already fully paid'));
      return;
    }

    $newAmountPaid = round($currentAmountPaid + (float) $amountInput, 2);
    $newStatus = $this->_resolveCompanyBillingStatus($amountDue, $newAmountPaid);
    $paymentDate = $paymentTimestamp !== false ? date('Y-m-d', $paymentTimestamp) : date('Y-m-d');
    $paymentDateTime = $paymentTimestamp !== false ? date('Y-m-d 00:00:00', $paymentTimestamp) : date('Y-m-d H:i:s');

    $this->db->trans_start();

    $this->db->insert('company_billing_payments', array(
      'billing_id' => $billingID,
      'settingsID' => (int) ($record->settingsID ?? 0),
      'amount_paid' => round((float) $amountInput, 2),
      'payment_date' => $paymentDate,
      'payment_method' => $paymentMethod,
      'reference_no' => $referenceNo,
      'notes' => $notes,
      'created_at' => date('Y-m-d H:i:s'),
    ));

    $this->db->where('billing_id', $billingID);
    $this->db->update('company_billing_records', array(
      'amount_paid' => $newAmountPaid,
      'status' => $newStatus,
      'paid_at' => $newStatus === 'paid' ? $paymentDateTime : null,
      'updated_at' => date('Y-m-d H:i:s'),
    ));

    $this->db->trans_complete();

    if (!$this->db->trans_status()) {
      echo json_encode(array('success' => false, 'message' => 'Failed to record payment'));
      return;
    }

    echo json_encode(array(
      'success' => true,
      'message' => 'Payment recorded successfully',
      'status' => $newStatus,
      'amount_paid' => $newAmountPaid,
    ));
  }

  function deleteCompany()
  {
    header('Content-Type: application/json');

    if (!$this->_is_system_admin_user()) {
      echo json_encode(array('success' => false, 'message' => 'Access Denied'));
      return;
    }

    $settingsID = (int) $this->input->post('settingsID');

    if ($settingsID <= 0) {
      echo json_encode(array('success' => false, 'message' => 'Invalid company ID'));
      return;
    }

    // Check if company has users or clients
    $userCount = $this->db->where('settingsID', $settingsID)->count_all_results('users');
    $clientCount = $this->db->where('settingsID', $settingsID)->count_all_results('customers');

    if ($userCount > 0 || $clientCount > 0) {
      echo json_encode(array('success' => false, 'message' => 'Cannot delete company with existing users or clients'));
      return;
    }

    $this->db->where('settingsID', $settingsID);
    $result = $this->db->delete('pos_settings');

    if ($this->db->affected_rows() > 0) {
      echo json_encode(array('success' => true, 'message' => 'Company deleted successfully'));
    } else {
      echo json_encode(array('success' => false, 'message' => 'Failed to delete company'));
    }
  }

  function generateBranchActivationKey()
  {
    header('Content-Type: application/json');

    if (!$this->_is_system_admin_user()) {
      echo json_encode(array('success' => false, 'message' => 'Access Denied'));
      return;
    }

    $settingsID = (int) $this->input->post('settingsID');
    $expiresInDays = (int) $this->input->post('expires_in_days');
    $notes = trim((string) $this->input->post('notes'));

    if ($settingsID <= 0) {
      echo json_encode(array('success' => false, 'message' => 'Invalid company'));
      return;
    }

    $company = $this->db->where('settingsID', $settingsID)->get('pos_settings')->row();
    if (!$company) {
      echo json_encode(array('success' => false, 'message' => 'Company not found'));
      return;
    }

    $this->_ensurePosActivationKeysTable();
    $expiresAt = $expiresInDays > 0 ? date('Y-m-d H:i:s', strtotime('+' . min($expiresInDays, 365) . ' days')) : null;
    $keyValue = '';
    $normalized = '';
    $keyHash = '';

    for ($attempt = 0; $attempt < 5; $attempt++) {
      $keyValue = $this->_generatePosActivationKeyValue();
      $normalized = $this->_normalizePosActivationKey($keyValue);
      $keyHash = hash('sha256', $normalized);

      $exists = $this->db
        ->where('key_hash', $keyHash)
        ->from('pos_activation_keys')
        ->count_all_results();

      if ($exists === 0) {
        break;
      }
    }

    if ($keyHash === '') {
      echo json_encode(array('success' => false, 'message' => 'Unable to generate activation key'));
      return;
    }

    $created = $this->db->insert('pos_activation_keys', array(
      'settingsID' => $settingsID,
      'key_hash' => $keyHash,
      'key_last4' => substr($normalized, -4),
      'status' => 'unused',
      'generated_by' => (int) ($this->session->userdata('user_id') ?? 0),
      'generated_at' => date('Y-m-d H:i:s'),
      'expires_at' => $expiresAt,
      'notes' => $notes,
    ));

    if (!$created) {
      echo json_encode(array('success' => false, 'message' => 'Failed to save activation key'));
      return;
    }

    echo json_encode(array(
      'success' => true,
      'message' => 'Branch activation key generated',
      'activation_key' => $keyValue,
      'expires_at' => $expiresAt,
      'company' => $company->CompName ?? $company->BusinessName ?? '',
    ));
  }

  function assignCompanyAdmin()
  {
    header('Content-Type: application/json');

    if (!$this->_is_system_admin_user()) {
      echo json_encode(array('success' => false, 'message' => 'Access Denied'));
      return;
    }

    $settingsID = (int) $this->input->post('settingsID');
    $adminUserId = (int) $this->input->post('admin_user_id');
    $username = trim((string) $this->input->post('username'));
    $email = trim((string) $this->input->post('email'));
    $password = trim((string) $this->input->post('password'));
    $fName = trim((string) $this->input->post('fName'));
    $lName = trim((string) $this->input->post('lName'));

    if ($settingsID <= 0) {
      echo json_encode(array('success' => false, 'message' => 'Invalid company'));
      return;
    }

    if ($adminUserId > 0) {
      // Update existing admin
      $data = array(
        'settingsID' => $settingsID,
        'position' => 'Admin',
      );

      if ($username !== '') $data['username'] = $username;
      if ($email !== '') $data['email'] = $email;
      if ($fName !== '') $data['fName'] = $fName;
      if ($lName !== '') $data['lName'] = $lName;
      if ($password !== '') $data['password'] = password_hash($password, PASSWORD_BCRYPT);

      $this->db->where('user_id', $adminUserId);
      $result = $this->db->update('users', $data);
    } else {
      // Create new admin
      if ($username === '' || $email === '' || $password === '') {
        echo json_encode(array('success' => false, 'message' => 'Username, email, and password are required'));
        return;
      }

      // Check if username exists
      $existing = $this->db->where('username', $username)->get('users')->row();
      if ($existing) {
        echo json_encode(array('success' => false, 'message' => 'Username already exists'));
        return;
      }

      $data = array(
        'username' => $username,
        'email' => $email,
        'password' => password_hash($password, PASSWORD_BCRYPT),
        'fName' => $fName,
        'lName' => $lName,
        'position' => 'Admin',
        'settingsID' => $settingsID,
        'acctStat' => 'Active',
      );

      // Check if created_at column exists
      if ($this->db->field_exists('created_at', 'users')) {
        $data['created_at'] = date('Y-m-d H:i:s');
      }

      $result = $this->db->insert('users', $data);

      if (!$result) {
        $error = $this->db->error();
        echo json_encode(array('success' => false, 'message' => 'Database error: ' . $error['message']));
        return;
      }
    }

    if ($result) {
      echo json_encode(array('success' => true, 'message' => 'Admin assigned successfully'));
    } else {
      echo json_encode(array('success' => false, 'message' => 'Failed to assign admin'));
    }
  }

  public function getCompanyFeatures()
  {
    header('Content-Type: application/json');

    if (!$this->_is_system_admin_user()) {
      echo json_encode(array('success' => false, 'message' => 'Access Denied'));
      return;
    }

    $settingsID = (int) $this->input->get('settingsID');

    if ($settingsID <= 0) {
      echo json_encode(array('success' => false, 'message' => 'Invalid company'));
      return;
    }

    $this->_ensureCompanyFeatureTables();

    $this->db->where('settingsID', $settingsID);
    $this->db->where('is_enabled', 1);
    $query = $this->db->get('company_features');

    $enabledFeatures = array();
    foreach ($query->result() as $feature) {
      $enabledFeatures[] = $feature->feature_key;
    }

    echo json_encode(array('success' => true, 'data' => $enabledFeatures));
  }

  public function saveCompanyFeatures()
  {
    header('Content-Type: application/json');

    if (!$this->_is_system_admin_user()) {
      echo json_encode(array('success' => false, 'message' => 'Access Denied'));
      return;
    }

    $settingsID = (int) $this->input->post('settingsID');
    $features = $this->input->post('features');

    if ($settingsID <= 0) {
      echo json_encode(array('success' => false, 'message' => 'Invalid company'));
      return;
    }

    if (!is_array($features)) {
      $features = array();
    }

    $this->_ensureCompanyFeatureTables();
    $this->_replaceCompanyFeatures($settingsID, $features);
    $this->db->where('settingsID', $settingsID);
    $this->db->update('pos_settings', array('package_id' => null, 'package_ids' => null));

    echo json_encode(array('success' => true, 'message' => 'Features saved successfully'));
  }

  protected function _is_feature_enabled($featureKey)
  {
    return $this->_companyHasFeature(array($featureKey));
  }

  protected function _get_enabled_features()
  {
    // Super Admin has access to all features
    if ($this->_is_system_admin_user()) {
      return array_keys($this->_getCompanyFeatureCatalog());
    }

    $this->_loadCurrentCompanyFeatureAccess();

    if (!$this->currentCompanyFeatureRestrictionsActive) {
      return array_keys($this->_getCompanyFeatureCatalog());
    }

    return $this->currentCompanyFeatureKeys;
  }

  public function getCompanyPackage()
  {
    header('Content-Type: application/json');

    if (!$this->_is_system_admin_user()) {
      echo json_encode(array('success' => false, 'message' => 'Access Denied'));
      return;
    }

    $settingsID = (int) $this->input->get('settingsID');

    if ($settingsID <= 0) {
      echo json_encode(array('success' => false, 'message' => 'Invalid company'));
      return;
    }

    $this->_ensureCompanyFeatureTables();

    $this->db->where('settingsID', $settingsID);
    $query = $this->db->get('pos_settings');
    $company = $query->row();

    if ($company) {
      $selectedPackageIds = $this->_getCompanySelectedPackageIds($company);
      if (!empty($selectedPackageIds)) {
        echo json_encode(array('success' => true, 'data' => $selectedPackageIds));
        return;
      }
    }

    if ($company && isset($company->package_id) && (int) $company->package_id > 0) {
      echo json_encode(array('success' => true, 'data' => array((int) $company->package_id)));
    } else {
      echo json_encode(array('success' => false, 'message' => 'No package selected'));
    }
  }

  public function saveCompanyPackage()
  {
    header('Content-Type: application/json');

    if (!$this->_is_system_admin_user()) {
      echo json_encode(array('success' => false, 'message' => 'Access Denied'));
      return;
    }

    $settingsID = (int) $this->input->post('settingsID');
    $packageIdsInput = $this->input->post('package_ids');
    if (!is_array($packageIdsInput)) {
      $singlePackageId = (int) $this->input->post('package_id');
      $packageIdsInput = $singlePackageId > 0 ? array($singlePackageId) : array();
    }

    if ($settingsID <= 0) {
      echo json_encode(array('success' => false, 'message' => 'Invalid company'));
      return;
    }

    $selectedPackageIds = $this->_normalizeCompanyPackageIds($packageIdsInput);
    if (empty($selectedPackageIds)) {
      echo json_encode(array('success' => false, 'message' => 'Please select at least one package'));
      return;
    }

    $this->_ensureCompanyFeatureTables();

    $combinedFeatures = $this->_getCombinedPackageFeatures($selectedPackageIds);
    $this->db->where('settingsID', $settingsID);
    $this->db->update('pos_settings', array(
      'package_id' => (int) $selectedPackageIds[0],
      'package_ids' => implode(',', $selectedPackageIds),
    ));

    $this->_replaceCompanyFeatures($settingsID, $combinedFeatures);

    echo json_encode(array('success' => true, 'message' => 'Packages saved successfully'));
  }

  public function saveProspectNotes()
  {
    if (!$this->_is_admin_user()) {
      echo json_encode(array('success' => false, 'message' => 'Only admins can save client notes.'));
      return;
    }

    if ($this->input->method(true) !== 'POST') {
      echo json_encode(array('success' => false, 'message' => 'Invalid request method.'));
      return;
    }

    $settingsID = $this->session->userdata('settingsID');
    $custID = (int) $this->input->post('cust_id', true);
    $notes = trim((string) $this->input->post('notes', true));

    if ($custID <= 0) {
      echo json_encode(array('success' => false, 'message' => 'Invalid client ID.'));
      return;
    }

    // Update the client notes
    $data = array(
      'notes' => $notes
    );

    $this->db->where('CustID', $custID);
    $this->db->where('settingsID', $settingsID);

    if ($this->db->update('customers', $data)) {
      echo json_encode(array('success' => true, 'message' => 'Notes saved successfully.'));
    } else {
      echo json_encode(array('success' => false, 'message' => 'Failed to save notes.'));
    }
  }

  public  function staff()
  {
    date_default_timezone_set('Asia/Manila');

    $user_id = $this->session->userdata('user_id');
    $settingsID = $this->session->userdata('settingsID');
    $username = (string) $this->session->userdata('username');
    $month = date('n');
    $year = date('Y');
    $today = date('Y-m-d');

    $data['accomplishedSummary'] = $this->CashModel->getAccomplishedTaskSummary($settingsID, $month, $year);
    $data['dueToday'] = $this->RemindersModel->getDueToday($settingsID, $user_id);
    $data['archivedToday'] = [];
    $data['taskDueTodayCount'] = $this->CashModel->countOpenTasksDueToday($settingsID, $user_id, $today);
    $data['taskDueSoonCount'] = $this->CashModel->countOpenTasksDueSoon($settingsID, 7, $user_id, $today);
    $data['taskOverdueCount'] = $this->CashModel->countOpenTasksOverdue($settingsID, $user_id, $today);
    $data['taskWithoutDueDateCount'] = $this->CashModel->countOpenTasksWithoutDueDate($settingsID, $user_id);
    $data['taskDueQueue'] = $this->CashModel->openTaskDueQueue($settingsID, $user_id, 6, 7, $today);
    $data['taskDueWindowDays'] = 7;
    $data['forwardedTaskCount'] = count($this->_staff_pending_forwarded_task_ids($settingsID, $user_id, $username));
    $data['unassignedTicketCount'] = $this->_count_staff_unassigned_support_issues($settingsID, $user_id);
    
    // Get overdue task details for alert
    $data['overdueTasks'] = $this->CashModel->getOverdueTasks($settingsID, $user_id, $today);
    $data['dueTodayTasks'] = $this->CashModel->getDueTodayTasks($settingsID, $user_id, $today);

    // Auto-create user_reminders table if it doesn't exist
    if (!$this->db->table_exists('user_reminders')) {
      $this->db->query("CREATE TABLE IF NOT EXISTS `user_reminders` (
        `reminder_id` int(11) NOT NULL AUTO_INCREMENT,
        `user_id` int(11) NOT NULL,
        `settingsID` int(11) NOT NULL,
        `title` varchar(255) NOT NULL,
        `description` text,
        `frequency` enum('daily','weekly','monthly','yearly') NOT NULL,
        `start_date` date NOT NULL,
        `next_reminder_date` date NOT NULL,
        `reminder_days_before` int(11) DEFAULT 3,
        `is_active` tinyint(1) DEFAULT 1,
        `last_sent_date` date DEFAULT NULL,
        `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
        `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`reminder_id`),
        KEY `user_id` (`user_id`),
        KEY `settingsID` (`settingsID`),
        KEY `next_reminder_date` (`next_reminder_date`)
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
    }

    // Get user reminders and send email notifications
    $data['reminders'] = array();
    if ($this->db->table_exists('user_reminders')) {
      $data['reminders'] = $this->db
        ->where('user_id', $user_id)
        ->where('is_active', 1)
        ->order_by('next_reminder_date', 'ASC')
        ->get('user_reminders')
        ->result();

      // Check and send email reminders
      $todayDateTime = new DateTime();
      foreach ($data['reminders'] as $reminder) {
        if (!empty($reminder->next_reminder_date)) {
          $reminderDate = new DateTime($reminder->next_reminder_date);
          $daysBefore = (int) ($reminder->reminder_days_before ?? 3);
          $alertDate = clone $reminderDate;
          $alertDate->sub(new DateInterval('P' . $daysBefore . 'D'));

          // Send email if today is within the reminder window and not already sent today
          if ($todayDateTime >= $alertDate && $todayDateTime <= $reminderDate) {
            $lastSent = !empty($reminder->last_sent_date) ? new DateTime($reminder->last_sent_date) : null;
            
            // Only send if not sent today
            if (!$lastSent || $lastSent->format('Y-m-d') !== $todayDateTime->format('Y-m-d')) {
              $user = $this->db->where('user_id', $user_id)->get('users')->row();
              if ($user && !empty($user->email)) {
                $emailSent = $this->sendReminderEmail($reminder, $user->email, $user->fName);
                
                if ($emailSent) {
                  // Update last_sent_date
                  $this->db->where('reminder_id', $reminder->reminder_id);
                  $this->db->update('user_reminders', array('last_sent_date' => $todayDateTime->format('Y-m-d')));
                }
              }
            }
          }

          // Update next_reminder_date if it has passed
          if ($todayDateTime > $reminderDate) {
            $nextDate = clone $reminderDate;
            switch ($reminder->frequency) {
              case 'daily':
                $nextDate->add(new DateInterval('P1D'));
                break;
              case 'weekly':
                $nextDate->add(new DateInterval('P7D'));
                break;
              case 'monthly':
                $nextDate->add(new DateInterval('P1M'));
                break;
              case 'yearly':
                $nextDate->add(new DateInterval('P1Y'));
                break;
            }
            
            // Keep advancing until nextDate is in the future
            while ($nextDate <= $todayDateTime) {
              switch ($reminder->frequency) {
                case 'daily':
                  $nextDate->add(new DateInterval('P1D'));
                  break;
                case 'weekly':
                  $nextDate->add(new DateInterval('P7D'));
                  break;
                case 'monthly':
                  $nextDate->add(new DateInterval('P1M'));
                  break;
                case 'yearly':
                  $nextDate->add(new DateInterval('P1Y'));
                  break;
              }
            }

            $this->db->where('reminder_id', $reminder->reminder_id);
            $this->db->update('user_reminders', array(
              'next_reminder_date' => $nextDate->format('Y-m-d'),
              'last_sent_date' => null
            ));
          }
        }
      }
    }

    $timeNotice = '';
    if ($username !== '') {
      $open = $this->db->query("select * from dtr where logDate=? and IDNumber=? and ((amTimeIn!='' and (amTimeOut='' or amTimeOut is null)) or (pmTimeIn!='' and (pmTimeOut='' or pmTimeOut is null))) order by dtrID desc limit 1", [$today, $username])->row();
      if ($open) {
        $timeNotice = 'You have an open time-in. Please time out to complete today\'s attendance.';
      } else {
        $hasAny = $this->db->query("select dtrID from dtr where logDate=? and IDNumber=? limit 1", [$today, $username])->row();
        if (!$hasAny) {
          $timeNotice = 'Please remember to time in and time out today to record your attendance.';
        }
      }
    }
    $data['time_notice'] = $timeNotice;

    $this->load->view('dashboard_staff', $data);
  }

  function saveReminder()
  {
    header('Content-Type: application/json');

    $user_id = $this->session->userdata('user_id');
    $settingsID = $this->session->userdata('settingsID');

    if (!$user_id || !$settingsID) {
      echo json_encode(array('success' => false, 'message' => 'Authentication required'));
      return;
    }

    if (!$this->db->table_exists('user_reminders')) {
      echo json_encode(array('success' => false, 'message' => 'Reminders table not found'));
      return;
    }

    $reminder_id = (int) $this->input->post('reminder_id');
    $title = trim((string) $this->input->post('title'));
    $description = trim((string) $this->input->post('description'));
    $frequency = trim((string) $this->input->post('frequency'));
    $start_date = trim((string) $this->input->post('start_date'));
    $next_reminder_date = trim((string) $this->input->post('next_reminder_date'));
    $reminder_days_before = (int) $this->input->post('reminder_days_before');

    if ($title === '' || $frequency === '' || $start_date === '') {
      echo json_encode(array('success' => false, 'message' => 'Title, frequency, and start date are required'));
      return;
    }

    if (!in_array($frequency, array('daily', 'weekly', 'monthly', 'yearly'))) {
      echo json_encode(array('success' => false, 'message' => 'Invalid frequency'));
      return;
    }

    $data = array(
      'user_id' => $user_id,
      'settingsID' => $settingsID,
      'title' => $title,
      'description' => $description,
      'frequency' => $frequency,
      'start_date' => $start_date,
      'next_reminder_date' => $next_reminder_date,
      'reminder_days_before' => $reminder_days_before > 0 ? $reminder_days_before : 3,
      'is_active' => 1
    );

    if ($reminder_id > 0) {
      // Update existing reminder
      $this->db->where('reminder_id', $reminder_id);
      $this->db->where('user_id', $user_id);
      $result = $this->db->update('user_reminders', $data);
      $dbError = $this->db->error();
      if (!$result) {
        error_log('Update reminder error: ' . print_r($dbError, true));
        echo json_encode(array('success' => false, 'message' => 'Error saving reminder: ' . ($dbError['message'] ?? 'Unknown error')));
        return;
      }
    } else {
      // Create new reminder
      $result = $this->db->insert('user_reminders', $data);
      $dbError = $this->db->error();
      if (!$result) {
        error_log('Insert reminder error: ' . print_r($dbError, true));
        echo json_encode(array('success' => false, 'message' => 'Error saving reminder: ' . ($dbError['message'] ?? 'Unknown error')));
        return;
      }
    }

    echo json_encode(array('success' => true, 'message' => 'Reminder saved successfully'));
  }

  function deleteReminder()
  {
    header('Content-Type: application/json');

    $user_id = $this->session->userdata('user_id');

    if (!$user_id) {
      echo json_encode(array('success' => false, 'message' => 'Authentication required'));
      return;
    }

    $reminder_id = (int) $this->input->post('reminder_id');

    if ($reminder_id <= 0) {
      echo json_encode(array('success' => false, 'message' => 'Invalid reminder ID'));
      return;
    }

    $this->db->where('reminder_id', $reminder_id);
    $this->db->where('user_id', $user_id);
    $result = $this->db->delete('user_reminders');

    if ($result) {
      echo json_encode(array('success' => true, 'message' => 'Reminder deleted successfully'));
    } else {
      echo json_encode(array('success' => false, 'message' => 'Error deleting reminder'));
    }
  }

  function reminders()
  {
    $user_id = $this->session->userdata('user_id');
    $settingsID = $this->session->userdata('settingsID');

    if (!$user_id || !$settingsID) {
      redirect('login');
      return;
    }

    // Auto-create user_reminders table if it doesn't exist
    if (!$this->db->table_exists('user_reminders')) {
      $this->db->query("CREATE TABLE IF NOT EXISTS `user_reminders` (
        `reminder_id` int(11) NOT NULL AUTO_INCREMENT,
        `user_id` int(11) NOT NULL,
        `settingsID` int(11) NOT NULL,
        `title` varchar(255) NOT NULL,
        `description` text,
        `frequency` enum('daily','weekly','monthly','yearly') NOT NULL,
        `start_date` date NOT NULL,
        `next_reminder_date` date NOT NULL,
        `reminder_days_before` int(11) DEFAULT 3,
        `is_active` tinyint(1) DEFAULT 1,
        `last_sent_date` date DEFAULT NULL,
        `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
        `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`reminder_id`),
        KEY `user_id` (`user_id`),
        KEY `settingsID` (`settingsID`),
        KEY `next_reminder_date` (`next_reminder_date`)
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
    }

    // Get user reminders
    $data['reminders'] = array();
    if ($this->db->table_exists('user_reminders')) {
      $data['reminders'] = $this->db
        ->where('user_id', $user_id)
        ->order_by('next_reminder_date', 'ASC')
        ->get('user_reminders')
        ->result();
    }

    $this->load->view('reminders', $data);
  }

  private function sendReminderEmail($reminder, $user_email, $user_name)
  {
    $this->load->library('email');
    $this->load->config('email');

    $daysDiff = 0;
    if (!empty($reminder->next_reminder_date)) {
      $reminderDate = new DateTime($reminder->next_reminder_date);
      $today = new DateTime();
      $diff = $today->diff($reminderDate);
      $daysDiff = $diff->days;
    }

    $message = $this->load->view('email/reminder_notification', array(
      'user_name' => $user_name,
      'reminder_title' => $reminder->title,
      'reminder_description' => $reminder->description,
      'reminder_date' => $reminder->next_reminder_date,
      'days_remaining' => $daysDiff,
      'frequency' => $reminder->frequency
    ), true);

    $fromAddress = $this->config->item('smtp_user');
    if (empty($fromAddress)) {
      $fromAddress = 'no-reply@' . parse_url(base_url(), PHP_URL_HOST);
    }

    $this->email->from($fromAddress, 'BERPS');
    $this->email->to($user_email);
    $this->email->subject('Reminder: ' . $reminder->title);
    $this->email->message($message);

    $result = $this->email->send();

    if (!$result) {
      log_message('error', 'Reminder email send failed: ' . $this->email->print_debugger(array('headers')));
    }

    return $result;
  }

  public function clientDashboard()
  {
    if (!$this->_is_client_user()) {
      echo "Access Denied";
      return;
    }

    $settingsID = (int) ($this->session->userdata('settingsID') ?? 0);
    $client = $this->_load_current_client();

    if (!$client) {
      show_404();
      return;
    }

    $custID = trim((string) ($client->CustID ?? ''));
    $customer = trim((string) ($client->Customer ?? ''));
    $result['client'] = $client;

    // Check invoice access permission
    $invoiceAccessEnabled = !empty($client->invoice_access_enabled);
    $result['invoice_access_enabled'] = $invoiceAccessEnabled;

    if ($invoiceAccessEnabled) {
      $result['invoices'] = $this->CashModel->clientInvoices($settingsID, $custID, $customer);
      $result['payments'] = $this->CashModel->customerHistory($settingsID, $custID, $customer);
    } else {
      $result['invoices'] = array();
      $result['payments'] = array();
    }

    // Add task-related summary counts and data filtered by customer
    $today = date('Y-m-d');
    $todayNumber = (int) str_replace('-', '', $today);

    // Get CustID for filtering tasks (projects table uses CustID, not customerID)
    $custID = trim((string) ($client->CustID ?? ''));

    // Requested Today - tasks reported today plus support requests submitted today
    $this->db->select("
        projects_task.taskID,
        projects_task.task,
        projects_task.reportedDate,
        projects_task.dueDate,
        projects_task.priority,
        projects.projectDescription,
        projects.CustID,
        CONCAT(users.fName, ' ', users.lName) AS assignedPersonName
    ");
    $this->db->from('projects_task');
    $this->db->join('projects', 'projects.projectID = projects_task.projectID', 'left');
    $this->db->join('users', 'users.user_id = projects_task.assignedPerson', 'left');
    $this->db->where('projects_task.settingsID', $settingsID);
    $this->db->where('projects.CustID', $custID);
    $this->db->where('projects_task.reportedDate', $today);
    if ($this->db->field_exists('forwarded_from', 'projects_task')) {
      $this->db->group_start();
      $this->db->where('projects_task.forwarded_from IS NULL', null, false);
      $this->db->or_where('projects_task.forwarded_from', 0);
      $this->db->group_end();
    }
    if ($this->db->field_exists('added_by', 'projects_task')) {
      $this->db->group_start();
      $this->db->where('projects_task.added_by IS NULL', null, false);
      $this->db->or_where('projects_task.added_by NOT LIKE', 'support_%');
      $this->db->group_end();
    }
    $this->db->order_by('projects_task.reportedDate', 'DESC');
    $this->db->order_by('projects_task.priority', 'ASC');
    $this->db->order_by('projects_task.taskID', 'DESC');
    $this->db->limit(100);
    $requestedTodayTasks = $this->db->get()->result();

    $requestedToday = array();
    foreach ($requestedTodayTasks as $taskRow) {
      $requestedToday[] = (object) array(
        'itemType' => 'task',
        'title' => (string) ($taskRow->task ?? ''),
        'projectDescription' => (string) ($taskRow->projectDescription ?? ''),
        'assignedPersonName' => (string) ($taskRow->assignedPersonName ?? ''),
        'reportedDate' => (string) ($taskRow->reportedDate ?? ''),
      );
    }

    $supportTodayRows = $this->db
      ->select('ticket_number, title, projectDescription, created_at, status')
      ->from('support_issues si')
      ->join('projects p', 'p.projectID = si.project_id', 'left')
      ->where('si.settingsID', $settingsID)
      ->where('si.customer_id', $custID)
      ->where('DATE(si.created_at)', $today)
      ->order_by('si.created_at', 'DESC')
      ->get()
      ->result();

    foreach ($supportTodayRows as $issueRow) {
      $requestedToday[] = (object) array(
        'itemType' => 'support',
        'title' => (string) ($issueRow->title ?? ''),
        'projectDescription' => (string) ($issueRow->projectDescription ?? ''),
        'assignedPersonName' => 'Support Team',
        'reportedDate' => (string) ($issueRow->created_at ?? ''),
        'ticketNumber' => (string) ($issueRow->ticket_number ?? ''),
        'status' => (string) ($issueRow->status ?? ''),
      );
    }

    usort($requestedToday, function ($left, $right) {
      $leftDate = strtotime((string) ($left->reportedDate ?? '1970-01-01 00:00:00'));
      $rightDate = strtotime((string) ($right->reportedDate ?? '1970-01-01 00:00:00'));
      return $rightDate <=> $leftDate;
    });

    $result['requestedToday'] = $requestedToday;
    $result['requestedTodayCount'] = count($requestedToday);

    $result['accomplished'] = $this->_client_accomplished_task_rows($settingsID, $custID);

    // Get actual accomplished count without limit
    $completionSubquery = "(SELECT taskID, MAX(ptsID) AS ptsID FROM projects_task_stat WHERE taskStat = '0' GROUP BY taskID) completed_task";
    $this->db->from('projects_task');
    $this->db->join('projects', 'projects.projectID = projects_task.projectID', 'left');
    $this->db->join($completionSubquery, 'completed_task.taskID = projects_task.taskID', 'left', false);
    $this->db->where('projects_task.settingsID', $settingsID);
    $this->db->where('projects.CustID', $custID);
    $this->db->where('projects_task.taskStat', '0');
    $result['accomplishedCount'] = $this->db->count_all_results();

    // Pending - open tasks (taskStat = '1')
    $this->db->select("
        projects_task.taskID,
        projects_task.task,
        projects_task.reportedDate,
        projects_task.dueDate,
        projects_task.priority,
        projects.projectDescription,
        projects.CustID,
        CONCAT(users.fName, ' ', users.lName) AS assignedPersonName
    ");
    $this->db->from('projects_task');
    $this->db->join('projects', 'projects.projectID = projects_task.projectID', 'left');
    $this->db->join('users', 'users.user_id = projects_task.assignedPerson', 'left');
    $this->db->where('projects_task.settingsID', $settingsID);
    $this->db->where('projects.CustID', $custID);
    $this->db->where('projects_task.taskStat', '1');
    $this->db->order_by('projects_task.dueDate', 'ASC');
    $this->db->order_by('projects_task.priority', 'ASC');
    $this->db->order_by('projects_task.taskID', 'DESC');
    $this->db->limit(100);
    $result['pending'] = $this->db->get()->result();

    // Get actual count without limit
    $this->db->from('projects_task');
    $this->db->join('projects', 'projects.projectID = projects_task.projectID', 'left');
    $this->db->where('projects_task.settingsID', $settingsID);
    $this->db->where('projects.CustID', $custID);
    $this->db->where('projects_task.taskStat', '1');
    $result['pendingCount'] = $this->db->count_all_results();

    $result['myTicketsCount'] = count($this->_client_ticket_rows($settingsID, $custID, 'all'));

    $result['supportDepartmentOptions'] = $this->_client_support_department_options();
    $result['supportProjects'] = $this->_client_support_projects($settingsID, $custID);
    $result['supportIssues'] = $this->_client_support_issue_rows($settingsID, $custID);

    $this->load->view('client_dashboard', $result);
  }

  public function clientMyTickets()
  {
    if (!$this->_is_client_user()) {
      echo 'Access Denied';
      return;
    }

    $settingsID = (int) ($this->session->userdata('settingsID') ?? 0);
    $client = $this->_load_current_client();

    if (!$client) {
      show_404();
      return;
    }

    $filter = strtolower(trim((string) $this->input->get('filter', true)));
    if (!in_array($filter, array('open', 'closed', 'all'), true)) {
      $filter = 'open';
    }

    $result = array(
      'client' => $client,
      'filter' => $filter,
      'tickets' => $this->_client_ticket_rows($settingsID, trim((string) ($client->CustID ?? '')), $filter),
    );

    $this->load->view('client_support_tickets', $result);
  }

  public function clientTicketView()
  {
    if (!$this->_is_client_user()) {
      echo 'Access Denied';
      return;
    }

    $settingsID = (int) ($this->session->userdata('settingsID') ?? 0);
    $userId = (int) ($this->session->userdata('user_id') ?? 0);
    $issueId = (int) $this->input->get('id');
    $issue = $this->_support_issue_for_user($issueId, $settingsID, $userId);

    if (!$issue) {
      show_404();
      return;
    }

    $comments = $this->db
      ->select('c.*, CONCAT(u.fName, " ", u.lName) AS employee_name, si.customer_name')
      ->from('support_issue_comments c')
      ->join('users u', 'u.user_id = c.employee_id', 'left')
      ->join('support_issues si', 'si.id = c.issue_id', 'left')
      ->where('c.settingsID', $settingsID)
      ->where('c.issue_id', $issueId)
      ->where('c.internal_note', 0)
      ->order_by('c.created_at', 'ASC')
      ->get()
      ->result();

    $clientUserId = $this->_support_client_user_id($settingsID, (string) ($issue->customer_id ?? ''));
    if ($clientUserId > 0) {
      $this->db
        ->where('settingsID', $settingsID)
        ->where('user_id', $clientUserId)
        ->where('issue_id', $issueId)
        ->update('support_notifications', array(
          'is_read' => 1,
          'read_at' => date('Y-m-d H:i:s'),
        ));
    }

    $result = array(
      'client' => $this->_load_current_client(),
      'issue' => $issue,
      'comments' => $comments,
      'attachments' => $this->_support_issue_attachments($issueId, $settingsID),
    );

    $this->load->view('client_support_ticket_view', $result);
  }

  public function cancelClientTicket()
  {
    if (!$this->_is_client_user()) {
      echo 'Access Denied';
      return;
    }

    if ($this->input->method(true) !== 'POST') {
      redirect('Page/clientMyTickets');
      return;
    }

    $settingsID = (int) ($this->session->userdata('settingsID') ?? 0);
    $client = $this->_load_current_client();
    if (!$client) {
      show_404();
      return;
    }

    $custID = trim((string) ($client->CustID ?? ''));
    $issueId = (int) $this->input->post('issue_id');
    $issue = $this->_support_issue_for_user($issueId, $settingsID, (int) ($this->session->userdata('user_id') ?? 0));

    if (!$issue || !$this->_client_ticket_can_cancel($issue, $settingsID, $custID)) {
      $this->session->set_flashdata('danger', 'This ticket can no longer be cancelled because support action has already started.');
      redirect('Page/clientMyTickets');
      return;
    }

    $this->db
      ->where('id', $issueId)
      ->where('settingsID', $settingsID)
      ->update('support_issues', array(
        'status' => 'cancelled',
        'updated_at' => date('Y-m-d H:i:s'),
      ));

    $this->db->insert('support_issue_comments', array(
      'issue_id' => $issueId,
      'employee_id' => null,
      'customer_comment' => 1,
      'comment' => 'Ticket cancelled by client before support action started.',
      'internal_note' => 0,
      'created_at' => date('Y-m-d H:i:s'),
      'settingsID' => $settingsID,
    ));

    if ($this->db->table_exists('support_issue_cancel_logs')) {
      $existingCancelLog = $this->db
        ->select('id')
        ->from('support_issue_cancel_logs')
        ->where('settingsID', $settingsID)
        ->where('issue_id', $issueId)
        ->limit(1)
        ->get()
        ->row();

      if (!$existingCancelLog) {
        $this->db->insert('support_issue_cancel_logs', array(
          'issue_id' => $issueId,
          'customer_id' => $custID,
          'cancelled_by_user_id' => (int) ($this->session->userdata('user_id') ?? 0),
          'cancel_note' => 'Ticket cancelled by client before support action started.',
          'settingsID' => $settingsID,
        ));
      }
    }

    $recipientEmails = array();
    $currentUserId = (int) ($this->session->userdata('user_id') ?? 0);
    foreach ($this->_support_recipient_user_ids($settingsID, (int) ($issue->department_id ?? 0)) as $recipientId) {
      $this->_create_support_notification($settingsID, $recipientId, $currentUserId, $issueId, (int) ($issue->department_id ?? 0), 'cancelled', 'Support Ticket Cancelled', 'Ticket ' . $issue->ticket_number . ' was cancelled by the client before support action started.');
      $recipientEmails[] = $this->_support_user_email($settingsID, $recipientId);
    }

    $this->_send_support_email_notification(
      $settingsID,
      $recipientEmails,
      'Support Ticket Cancelled: ' . (string) ($issue->ticket_number ?? ''),
      'The client cancelled this ticket before any support action was taken.',
      $issue
    );

    $this->session->set_flashdata('success', 'Ticket cancelled successfully.');
    redirect('Page/clientMyTickets');
  }

  public function clientReportIssue()
  {
    if (!$this->_is_client_user()) {
      echo 'Access Denied';
      return;
    }

    $settingsID = (int) ($this->session->userdata('settingsID') ?? 0);
    $client = $this->_load_current_client();

    if (!$client) {
      show_404();
      return;
    }

    $result = array(
      'client' => $client,
      'supportDepartmentOptions' => $this->_client_support_department_options(),
      'supportProjects' => $this->_client_support_projects($settingsID, trim((string) ($client->CustID ?? ''))),
    );

    $this->load->view('client_report_issue', $result);
  }

  public function clientAccomplishedTasks()
  {
    if (!$this->_is_client_user()) {
      echo 'Access Denied';
      return;
    }

    $settingsID = (int) ($this->session->userdata('settingsID') ?? 0);
    $client = $this->_load_current_client();

    if (!$client) {
      show_404();
      return;
    }

    $custID = trim((string) ($client->CustID ?? ''));

    // Get actual total count without limit
    $completionSubquery = "(SELECT taskID, MAX(ptsID) AS ptsID FROM projects_task_stat WHERE taskStat = '0' GROUP BY taskID) completed_task";
    $this->db->from('projects_task');
    $this->db->join('projects', 'projects.projectID = projects_task.projectID', 'left');
    $this->db->join($completionSubquery, 'completed_task.taskID = projects_task.taskID', 'left', false);
    $this->db->where('projects_task.settingsID', $settingsID);
    $this->db->where('projects.CustID', $custID);
    $this->db->where('projects_task.taskStat', '0');
    $totalAccomplished = $this->db->count_all_results();

    $result = array(
      'client' => $client,
      'tasks' => $this->_client_accomplished_task_rows($settingsID, $custID),
      'totalAccomplished' => $totalAccomplished,
    );

    $this->load->view('client_accomplished_tasks', $result);
  }

  public function clientRequestedToday()
  {
    if (!$this->_is_client_user()) {
      echo 'Access Denied';
      return;
    }

    $settingsID = (int) ($this->session->userdata('settingsID') ?? 0);
    $client = $this->_load_current_client();

    if (!$client) {
      show_404();
      return;
    }

    $custID = trim((string) ($client->CustID ?? ''));

    $result = array(
      'client' => $client,
      'items' => $this->_client_requested_today_rows($settingsID, $custID),
    );

    $this->load->view('client_requested_today', $result);
  }

  public function clientPendingTasks()
  {
    if (!$this->_is_client_user()) {
      echo 'Access Denied';
      return;
    }

    $settingsID = (int) ($this->session->userdata('settingsID') ?? 0);
    $client = $this->_load_current_client();

    if (!$client) {
      show_404();
      return;
    }

    $custID = trim((string) ($client->CustID ?? ''));

    // Get actual count without limit
    $this->db->from('projects_task');
    $this->db->join('projects', 'projects.projectID = projects_task.projectID', 'left');
    $this->db->where('projects_task.settingsID', (int) $settingsID);
    $this->db->where('projects.CustID', trim((string) $custID));
    $this->db->where('projects_task.taskStat', '1');
    $totalPendingCount = $this->db->count_all_results();

    $result = array(
      'client' => $client,
      'tasks' => $this->_client_pending_task_rows($settingsID, $custID),
      'totalPendingCount' => $totalPendingCount,
    );

    $this->load->view('client_pending_tasks', $result);
  }

  public function clientClosedTaskReport()
  {
    if (!$this->_is_client_user()) {
      echo 'Access Denied';
      return;
    }

    $settingsID = (int) ($this->session->userdata('settingsID') ?? 0);
    $client = $this->_load_current_client();

    if (!$client) {
      show_404();
      return;
    }

    $custID = trim((string) ($client->CustID ?? ''));
    $tasks = $this->_client_accomplished_task_rows($settingsID, $custID, null);

    // Sort by taskID (primary key) descending
    usort($tasks, function ($a, $b) {
      $idA = (int) ($a->taskID ?? 0);
      $idB = (int) ($b->taskID ?? 0);
      return $idB <=> $idA;
    });

    $highPriorityCount = 0;
    $mediumPriorityCount = 0;
    $lowPriorityCount = 0;
    $latestCompletedAt = '';

    foreach ($tasks as $task) {
      $priority = (string) ($task->priority ?? '2');
      if ($priority === '1') {
        $highPriorityCount++;
      } elseif ($priority === '3') {
        $lowPriorityCount++;
      } else {
        $mediumPriorityCount++;
      }

      $completedDate = trim((string) ($task->completedDate ?? ''));
      if ($completedDate !== '' && ($latestCompletedAt === '' || strtotime($completedDate) > strtotime($latestCompletedAt))) {
        $latestCompletedAt = $completedDate;
      }
    }

    $result = array(
      'client' => $client,
      'tasks' => $tasks,
      'totalClosedTasks' => count($tasks),
      'highPriorityCount' => $highPriorityCount,
      'mediumPriorityCount' => $mediumPriorityCount,
      'lowPriorityCount' => $lowPriorityCount,
      'latestCompletedAt' => $latestCompletedAt,
    );

    $this->load->view('client_closed_task_report', $result);
  }

  public function cancelledTicketLogs()
  {
    $isClient = $this->_is_client_user();
    $isAdmin = $this->_is_admin_user();

    if (!$isClient && !$isAdmin) {
      echo 'Access Denied';
      return;
    }

    $settingsID = (int) ($this->session->userdata('settingsID') ?? 0);
    $custID = '';
    $client = null;

    if ($isClient) {
      $client = $this->_load_current_client();

      if (!$client) {
        show_404();
        return;
      }

      $custID = trim((string) ($client->CustID ?? ''));
    }

    $result = array(
      'client' => $client,
      'isClientView' => $isClient,
      'logs' => $this->_cancelled_ticket_log_rows($settingsID, $custID),
    );

    $this->load->view('cancelled_ticket_logs', $result);
  }

  public function submitClientSupportIssue()
  {
    if (!$this->_is_client_user()) {
      echo 'Access Denied';
      return;
    }

    if ($this->input->method(true) !== 'POST') {
      redirect('Page/clientDashboard');
      return;
    }

    $settingsID = (int) ($this->session->userdata('settingsID') ?? 0);
    $client = $this->_load_current_client();

    if (!$client) {
      show_404();
      return;
    }

    $custID = trim((string) ($client->CustID ?? ''));
    $departmentValue = strtolower(trim((string) $this->input->post('department', true)));
    $category = $this->_normalize_support_category($departmentValue);
    // Client form only offers "General", but route it through the Technical
    // workflow so existing support staff get the email and can assign tickets.
    if ($category === 'general') {
      $category = 'technical';
    }
    $projectId = (int) $this->input->post('project_id');
    $title = trim((string) $this->input->post('title', true));
    $description = trim((string) $this->input->post('description', true));
    $referenceLink = trim((string) $this->input->post('reference_link', true));

    if ($title === '' || $description === '') {
      $this->session->set_flashdata('danger', 'Subject and report details are required.');
      redirect('Page/clientReportIssue');
      return;
    }

    // Check for duplicate submission within last 60 seconds
    $recentDuplicate = $this->db
      ->from('support_issues')
      ->where('settingsID', $settingsID)
      ->where('customer_id', $custID)
      ->where('title', $title)
      ->where('created_at >=', date('Y-m-d H:i:s', strtotime('-60 seconds')))
      ->limit(1)
      ->get()
      ->row();

    if ($recentDuplicate) {
      $this->session->set_flashdata('warning', 'A similar ticket was just submitted. Please wait a moment before submitting again.');
      redirect('Page/clientReportIssue');
      return;
    }

    $project = null;
    $projectLabel = 'General';
    if ($projectId > 0) {
      $project = $this->_client_project_row($settingsID, $custID, $projectId);
      if (!$project) {
        $this->session->set_flashdata('danger', 'The selected project does not belong to your account.');
        redirect('Page/clientReportIssue');
        return;
      }
      $projectLabel = trim((string) ($project->projectDescription ?? ''));
    }

    $supportSettingsID = $settingsID;
    if ($project && !empty($project->settingsID)) {
      $supportSettingsID = (int) $project->settingsID;
    }
    if ($supportSettingsID <= 0) {
      $supportSettingsID = $settingsID;
    }

    $department = $this->_support_department_for_category($supportSettingsID, $category);
    $issue = $this->_create_support_issue(array(
      'settingsID' => $supportSettingsID,
      'customer_id' => $custID,
      'customer_name' => trim((string) ($client->Customer ?? '')),
      'customer_email' => trim((string) ($client->client_email ?? $client->CompanyEmail ?? '')),
      'customer_phone' => trim((string) ($client->ContactNos ?? '')),
      'department_id' => (int) ($department->id ?? 0),
      'project_id' => $projectId > 0 ? $projectId : null,
      'project_label' => $projectLabel,
      'title' => $title,
      'description' => $description,
      'reference_link' => $referenceLink,
      'category' => $category,
      'priority' => 'medium',
      'status' => 'awaiting_reply',
    ));

    if (!$issue) {
      $this->session->set_flashdata('danger', 'We could not submit your report right now. Please try again.');
      redirect('Page/clientReportIssue');
      return;
    }

    $attachmentMessages = $this->_handle_support_issue_attachments((int) ($issue->id ?? 0), $supportSettingsID, 'attachments', 'client', 0);

    $successMessage = 'Your ticket has been submitted. A support staff member will reach you shortly.';
    if (!empty($attachmentMessages)) {
      $successMessage .= ' ' . implode(' ', $attachmentMessages);
    }

    $this->session->set_flashdata('success', $successMessage);
    redirect('Page/clientMyTickets');
  }

  public function clientRequestForm()
  {
    if (!$this->_is_client_user()) {
      echo "Access Denied";
      return;
    }

    $settingsID = (int) ($this->session->userdata('settingsID') ?? 0);
    $client = $this->_load_current_client();

    if (!$client) {
      show_404();
      return;
    }

    $custID = trim((string) ($client->CustID ?? ''));

    // Fetch projects for this customer
    $this->db->select('projectID, projectDescription');
    $this->db->from('projects');
    $this->db->where('settingsID', $settingsID);
    $this->db->where('CustID', $custID);
    $this->db->order_by('projectDescription', 'ASC');
    $result['projects'] = $this->db->get()->result();
    $result['client'] = $client;

    $this->load->view('client_request_form', $result);
  }

  public function supportDashboard()
  {
    if (!$this->_can_manage_support_issues()) {
      echo 'Access Denied';
      return;
    }

    $settingsID = (int) ($this->session->userdata('settingsID') ?? 0);
    $userId = (int) ($this->session->userdata('user_id') ?? 0);
    $isStaff = $this->_is_staff_user();

    // Build base scope for staff (only tickets they can see)
    $accessibleIssueIds = array();
    if ($isStaff) {
      $departmentIds = $this->_staff_support_department_ids($settingsID, $userId);

      $directRows = $this->db->select('id')->from('support_issues')
        ->where('settingsID', $settingsID)
        ->where('assigned_employee_id', $userId)
        ->get()->result();
      foreach ($directRows as $r) {
        $accessibleIssueIds[] = (int) $r->id;
      }

      if (!empty($departmentIds)) {
        $deptRows = $this->db->select('id')->from('support_issues')
          ->where('settingsID', $settingsID)
          ->where_in('department_id', $departmentIds)
          ->get()->result();
        foreach ($deptRows as $r) {
          $accessibleIssueIds[] = (int) $r->id;
        }
      }

      $accessibleIssueIds = array_values(array_unique(array_filter($accessibleIssueIds)));
      if (empty($accessibleIssueIds)) {
        $accessibleIssueIds = array(0);
      }
    }

    $applyScope = function ($builder) use ($isStaff, $accessibleIssueIds) {
      if ($isStaff) {
        $builder->where_in('id', $accessibleIssueIds);
      }
      return $builder;
    };

    $closedStatusSql = "LOWER(TRIM(COALESCE(status, ''))) IN ('closed','resolved','done','completed')";
    $openStatusSql   = "LOWER(TRIM(COALESCE(status, ''))) NOT IN ('closed','resolved','done','completed')";

    // Total counts
    $applyScope($this->db->from('support_issues')->where('settingsID', $settingsID));
    $totalTickets = $this->db->count_all_results();

    $applyScope($this->db->from('support_issues')->where('settingsID', $settingsID)->where($openStatusSql, null, false));
    $openTickets = $this->db->count_all_results();

    $applyScope($this->db->from('support_issues')->where('settingsID', $settingsID)->where($closedStatusSql, null, false));
    $closedTickets = $this->db->count_all_results();

    $applyScope($this->db->from('support_issues')->where('settingsID', $settingsID)
      ->group_start()->where('assigned_employee_id IS NULL', null, false)->or_where('assigned_employee_id', 0)->group_end()
      ->where($openStatusSql, null, false));
    $unassignedTickets = $this->db->count_all_results();

    $applyScope($this->db->from('support_issues')->where('settingsID', $settingsID)
      ->where("LOWER(TRIM(COALESCE(status, ''))) = 'awaiting_reply'", null, false));
    $awaitingReplyTickets = $this->db->count_all_results();

    $applyScope($this->db->from('support_issues')->where('settingsID', $settingsID)->where('client_reply_required', 1));
    $clientReplyRequired = $this->db->count_all_results();

    // This month metrics
    $monthStart = date('Y-m-01 00:00:00');
    $applyScope($this->db->from('support_issues')->where('settingsID', $settingsID)->where('created_at >=', $monthStart));
    $thisMonthCreated = $this->db->count_all_results();

    $applyScope($this->db->from('support_issues')->where('settingsID', $settingsID)
      ->where('resolution_date >=', $monthStart)->where($closedStatusSql, null, false));
    $thisMonthClosed = $this->db->count_all_results();

    // Average resolution time (in hours) for closed tickets
    $this->db->select("AVG(TIMESTAMPDIFF(HOUR, created_at, resolution_date)) AS avg_hours", false)
      ->from('support_issues')
      ->where('settingsID', $settingsID)
      ->where($closedStatusSql, null, false)
      ->where('resolution_date IS NOT NULL', null, false);
    if ($isStaff) {
      $this->db->where_in('id', $accessibleIssueIds);
    }
    $avgRow = $this->db->get()->row();
    $avgResolutionHours = $avgRow && isset($avgRow->avg_hours) ? (float) $avgRow->avg_hours : 0.0;

    // Tickets by department
    $this->db->select("d.id, d.department_name, COUNT(si.id) AS ticket_count, SUM(CASE WHEN {$openStatusSql} THEN 1 ELSE 0 END) AS open_count", false)
      ->from('support_issues si')
      ->join('support_departments d', 'd.id = si.department_id', 'left')
      ->where('si.settingsID', $settingsID);
    if ($isStaff) {
      $this->db->where_in('si.id', $accessibleIssueIds);
    }
    $byDepartment = $this->db->group_by('d.id, d.department_name')->order_by('ticket_count', 'DESC')->get()->result();

    // Tickets by priority
    $this->db->select("LOWER(TRIM(COALESCE(priority, 'medium'))) AS priority_key, COUNT(*) AS ticket_count", false)
      ->from('support_issues')
      ->where('settingsID', $settingsID);
    if ($isStaff) {
      $this->db->where_in('id', $accessibleIssueIds);
    }
    $byPriority = $this->db->group_by('priority_key')->order_by('ticket_count', 'DESC')->get()->result();

    // Tickets by status
    $this->db->select("LOWER(TRIM(COALESCE(status, 'open'))) AS status_key, COUNT(*) AS ticket_count", false)
      ->from('support_issues')
      ->where('settingsID', $settingsID);
    if ($isStaff) {
      $this->db->where_in('id', $accessibleIssueIds);
    }
    $byStatus = $this->db->group_by('status_key')->order_by('ticket_count', 'DESC')->get()->result();

    // Tickets by category
    $this->db->select("COALESCE(NULLIF(TRIM(category), ''), 'general') AS category, COUNT(*) AS ticket_count", false)
      ->from('support_issues')
      ->where('settingsID', $settingsID);
    if ($isStaff) {
      $this->db->where_in('id', $accessibleIssueIds);
    }
    $byCategory = $this->db->group_by('category')->order_by('ticket_count', 'DESC')->limit(10)->get()->result();

    // Top assigned employees (workload)
    $this->db->select("si.assigned_employee_id, CONCAT(u.fName, ' ', u.lName) AS employee_name,
        COUNT(*) AS total_assigned,
        SUM(CASE WHEN {$openStatusSql} THEN 1 ELSE 0 END) AS open_assigned,
        SUM(CASE WHEN {$closedStatusSql} THEN 1 ELSE 0 END) AS closed_assigned", false)
      ->from('support_issues si')
      ->join('users u', 'u.user_id = si.assigned_employee_id', 'inner')
      ->where('si.settingsID', $settingsID)
      ->where('si.assigned_employee_id IS NOT NULL', null, false)
      ->where('si.assigned_employee_id >', 0);
    if ($isStaff) {
      $this->db->where_in('si.id', $accessibleIssueIds);
    }
    $byEmployee = $this->db->group_by('si.assigned_employee_id, employee_name')
      ->order_by('total_assigned', 'DESC')
      ->limit(10)
      ->get()->result();

    // Last 14 days created/closed (for trend chart)
    $trendDays = 14;
    $trendLabels = array();
    $createdSeries = array();
    $closedSeries = array();
    for ($i = $trendDays - 1; $i >= 0; $i--) {
      $day = date('Y-m-d', strtotime('-' . $i . ' days'));
      $trendLabels[] = date('M j', strtotime($day));

      $applyScope($this->db->from('support_issues')->where('settingsID', $settingsID)
        ->where('DATE(created_at)', $day));
      $createdSeries[] = (int) $this->db->count_all_results();

      $applyScope($this->db->from('support_issues')->where('settingsID', $settingsID)
        ->where('DATE(resolution_date)', $day)
        ->where($closedStatusSql, null, false));
      $closedSeries[] = (int) $this->db->count_all_results();
    }

    // Recent 10 tickets
    $this->db->select('si.*, d.department_name, CONCAT(u.fName, " ", u.lName) AS assigned_employee_name')
      ->from('support_issues si')
      ->join('support_departments d', 'd.id = si.department_id', 'left')
      ->join('users u', 'u.user_id = si.assigned_employee_id', 'left')
      ->where('si.settingsID', $settingsID);
    if ($isStaff) {
      $this->db->where_in('si.id', $accessibleIssueIds);
    }
    $recentTickets = $this->db->order_by('si.created_at', 'DESC')->limit(10)->get()->result();

    // Oldest open tickets (aging)
    $this->db->select('si.*, d.department_name, CONCAT(u.fName, " ", u.lName) AS assigned_employee_name')
      ->from('support_issues si')
      ->join('support_departments d', 'd.id = si.department_id', 'left')
      ->join('users u', 'u.user_id = si.assigned_employee_id', 'left')
      ->where('si.settingsID', $settingsID)
      ->where($openStatusSql, null, false);
    if ($isStaff) {
      $this->db->where_in('si.id', $accessibleIssueIds);
    }
    $oldestOpenTickets = $this->db->order_by('si.created_at', 'ASC')->limit(8)->get()->result();

    $unreadNotificationCount = 0;
    if ($this->db->table_exists('support_notifications')) {
      $unreadNotificationCount = (int) $this->db->from('support_notifications')
        ->where('settingsID', $settingsID)
        ->where('user_id', $userId)
        ->where('is_read', 0)
        ->count_all_results();
    }

    $result = array(
      'totalTickets' => $totalTickets,
      'openTickets' => $openTickets,
      'closedTickets' => $closedTickets,
      'unassignedTickets' => $unassignedTickets,
      'awaitingReplyTickets' => $awaitingReplyTickets,
      'clientReplyRequired' => $clientReplyRequired,
      'thisMonthCreated' => $thisMonthCreated,
      'thisMonthClosed' => $thisMonthClosed,
      'avgResolutionHours' => $avgResolutionHours,
      'byDepartment' => $byDepartment,
      'byPriority' => $byPriority,
      'byStatus' => $byStatus,
      'byCategory' => $byCategory,
      'byEmployee' => $byEmployee,
      'trendLabels' => $trendLabels,
      'createdSeries' => $createdSeries,
      'closedSeries' => $closedSeries,
      'recentTickets' => $recentTickets,
      'oldestOpenTickets' => $oldestOpenTickets,
      'unreadNotificationCount' => $unreadNotificationCount,
      'isStaffUser' => $isStaff,
    );

    $this->load->view('support_dashboard', $result);
  }

  public function supportIssues()
  {
    if (!$this->_can_manage_support_issues()) {
      echo 'Access Denied';
      return;
    }

    $settingsID = (int) ($this->session->userdata('settingsID') ?? 0);
    $userId = (int) ($this->session->userdata('user_id') ?? 0);
    $scope = strtolower(trim((string) $this->input->get('scope', true)));
    if (!in_array($scope, array('unassigned', 'awaiting_reply', 'open', 'closed', 'all'), true)) {
      $scope = 'open';
    }

    $departmentIds = $this->_is_staff_user()
      ? $this->_staff_support_department_ids($settingsID, $userId)
      : array();

    $accessibleIssueIds = array();
    if ($this->_is_staff_user()) {
      $directIssueRows = $this->db
        ->select('id')
        ->from('support_issues')
        ->where('settingsID', $settingsID)
        ->where('assigned_employee_id', $userId)
        ->get()
        ->result();

      foreach ($directIssueRows as $row) {
        $accessibleIssueIds[] = (int) ($row->id ?? 0);
      }

      if (!empty($departmentIds)) {
        $departmentIssueRows = $this->db
          ->select('id')
          ->from('support_issues')
          ->where('settingsID', $settingsID)
          ->where_in('department_id', $departmentIds)
          ->get()
          ->result();

        foreach ($departmentIssueRows as $row) {
          $accessibleIssueIds[] = (int) ($row->id ?? 0);
        }
      }

      if ($this->db->table_exists('support_notifications')) {
        $notificationIssueRows = $this->db
          ->select('issue_id')
          ->from('support_notifications')
          ->where('settingsID', $settingsID)
          ->where('user_id', $userId)
          ->where('issue_id IS NOT NULL', null, false)
          ->get()
          ->result();

        foreach ($notificationIssueRows as $row) {
          $accessibleIssueIds[] = (int) ($row->issue_id ?? 0);
        }
      }

      if ($this->db->table_exists('projects_task') && $this->db->field_exists('forwarded_from', 'projects_task')) {
        $taskRows = $this->db
          ->select('taskID, forwarded_from')
          ->from('projects_task')
          ->where('settingsID', $settingsID)
          ->where('assignedPerson', $userId)
          ->group_start()
          ->where('forwarded_from IS NOT NULL', null, false)
          ->where('forwarded_from >', 0)
          ->group_end()
          ->get()
          ->result();

        $taskIds = array();
        foreach ($taskRows as $row) {
          $taskIds[] = (int) ($row->taskID ?? 0);
          $taskIds[] = (int) ($row->forwarded_from ?? 0);
        }
        $taskIds = array_values(array_unique(array_filter($taskIds)));

        if (!empty($taskIds)) {
          $issueRowsByTask = $this->db
            ->select('id')
            ->from('support_issues')
            ->where('settingsID', $settingsID)
            ->group_start()
            ->where_in('task_id', $taskIds);
          if ($this->db->field_exists('task_id', 'support_issues')) {
            $issueRowsByTask->or_where_in('task_id', $taskIds);
          }
          $issueRowsByTask = $issueRowsByTask
            ->group_end()
            ->get()
            ->result();

          foreach ($issueRowsByTask as $row) {
            $accessibleIssueIds[] = (int) ($row->id ?? 0);
          }
        }
      }

      $accessibleIssueIds = array_values(array_unique(array_filter($accessibleIssueIds)));

      if (empty($accessibleIssueIds)) {
        $accessibleIssueIds = array(0);
      }
    }

    $this->db
      ->select('si.*, d.department_name, p.projectDescription, CONCAT(u.fName, " ", u.lName) AS assigned_employee_name')
      ->from('support_issues si')
      ->join('support_departments d', 'd.id = si.department_id', 'left')
      ->join('projects p', 'p.projectID = si.project_id', 'left')
      ->join('users u', 'u.user_id = si.assigned_employee_id', 'left')
      ->where('si.settingsID', $settingsID);

    if ($this->_is_staff_user()) {
      $this->db->where_in('si.id', $accessibleIssueIds);
    }

    if ($scope === 'unassigned') {
      $this->db->group_start()
        ->where('si.assigned_employee_id IS NULL', null, false)
        ->or_where('si.assigned_employee_id', 0)
        ->group_end()
        ->where("LOWER(TRIM(COALESCE(si.status, ''))) NOT IN ('closed','resolved','done','completed')", null, false);
    } elseif ($scope === 'awaiting_reply') {
      $this->db->where("LOWER(TRIM(COALESCE(si.status, ''))) = 'awaiting_reply'", null, false);
    } elseif ($scope === 'closed') {
      $this->db->where("LOWER(TRIM(COALESCE(si.status, ''))) IN ('closed','resolved','done','completed')", null, false);
    } elseif ($scope === 'open') {
      $this->db->where("LOWER(TRIM(COALESCE(si.status, ''))) NOT IN ('closed','resolved','done','completed')", null, false);
    }

    $issues = $this->db->order_by('si.created_at', 'DESC')->get()->result();

    $result = array(
      'scope' => $scope,
      'issues' => $issues,
      'unreadNotificationCount' => $this->db
        ->from('support_notifications')
        ->where('settingsID', $settingsID)
        ->where('user_id', $userId)
        ->where('is_read', 0)
        ->count_all_results(),
    );

    $this->load->view('support_issue_list', $result);
  }

  public function supportIssueView()
  {
    if (!$this->_can_manage_support_issues()) {
      echo 'Access Denied';
      return;
    }

    $settingsID = (int) ($this->session->userdata('settingsID') ?? 0);
    $userId = (int) ($this->session->userdata('user_id') ?? 0);
    $issueId = (int) $this->input->get('id');
    $issue = $this->_support_issue_for_user($issueId, $settingsID, $userId);

    if (!$issue) {
      show_404();
      return;
    }

    $chatPermissions = $this->_support_user_chat_permissions($settingsID, $userId);
    if ($this->_is_staff_user() && empty($chatPermissions['view'])) {
      $chatPermissions = array('view' => false, 'reply' => false);
    }

    $manilaNow = (new DateTime('now', new DateTimeZone('Asia/Manila')))->format('Y-m-d H:i:s');
    $this->db
      ->where('settingsID', $settingsID)
      ->where('user_id', $userId)
      ->where('issue_id', $issueId)
      ->update('support_notifications', array(
        'is_read' => 1,
        'read_at' => $manilaNow,
      ));

    $comments = $this->db
      ->select('c.*, CONCAT(u.fName, " ", u.lName) AS employee_name, si.customer_name')
      ->from('support_issue_comments c')
      ->join('users u', 'u.user_id = c.employee_id', 'left')
      ->join('support_issues si', 'si.id = c.issue_id', 'left')
      ->where('c.settingsID', $settingsID)
      ->where('c.issue_id', $issueId)
      ->order_by('c.created_at', 'ASC')
      ->get()
      ->result();

    $result = array(
      'issue' => $issue,
      'comments' => !empty($chatPermissions['view']) ? $comments : array(),
      'assignableUsers' => $this->_support_assignable_users($settingsID, (int) ($issue->department_id ?? 0)),
      'taggableUsers' => $this->_support_taggable_users($settingsID),
      'attachments' => $this->_support_issue_attachments($issueId, $settingsID),
      'canViewChat' => !empty($chatPermissions['view']),
      'canReplyChat' => !empty($chatPermissions['reply']),
      'currentUserId' => $userId,
      'isStaffUser' => $this->_is_staff_user(),
    );

    $this->load->view('support_issue_view', $result);
  }

  public function addSupportIssueComment()
  {
    if (!$this->_can_manage_support_issues()) {
      echo 'Access Denied';
      return;
    }

    $settingsID = (int) ($this->session->userdata('settingsID') ?? 0);
    $userId = (int) ($this->session->userdata('user_id') ?? 0);
    $issueId = (int) $this->input->post('issue_id');
    $comment = trim((string) $this->input->post('comment', true));
    $issue = $this->_support_issue_for_user($issueId, $settingsID, $userId);
    $chatPermissions = $this->_support_user_chat_permissions($settingsID, $userId);

    if (!$issue || $comment === '') {
      $this->session->set_flashdata('danger', 'Comment could not be added.');
      redirect('Page/supportIssueView?id=' . $issueId);
      return;
    }

    if ($this->_is_staff_user() && empty($chatPermissions['reply'])) {
      $this->session->set_flashdata('danger', 'You are not allowed to reply in support ticket chats.');
      redirect('Page/supportIssueView?id=' . $issueId);
      return;
    }

    // Handle comment attachment upload
    $attachmentPath = null;
    $attachmentError = '';
    if (isset($_FILES['comment_attachment']) && $_FILES['comment_attachment']['error'] !== UPLOAD_ERR_NO_FILE) {
      $uploadErr = (int) $_FILES['comment_attachment']['error'];
      if ($uploadErr !== UPLOAD_ERR_OK) {
        if ($uploadErr === UPLOAD_ERR_INI_SIZE || $uploadErr === UPLOAD_ERR_FORM_SIZE) {
          $attachmentError = 'Attachment exceeds the maximum allowed size.';
        } else {
          $attachmentError = 'Attachment upload failed (error code ' . $uploadErr . ').';
        }
      } else {
        $allowedExts = array('pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx');
        $fileSize = (int) $_FILES['comment_attachment']['size'];
        $maxSize = 5 * 1024 * 1024; // 5MB
        $fileExt = strtolower(pathinfo($_FILES['comment_attachment']['name'], PATHINFO_EXTENSION));

        if (!in_array($fileExt, $allowedExts, true)) {
          $attachmentError = 'Attachment file type not allowed. Allowed: PDF, PNG, JPG, DOC, DOCX.';
        } elseif ($fileSize > $maxSize) {
          $attachmentError = 'Attachment exceeds the 5MB size limit.';
        } else {
          $uploadDir = FCPATH . 'uploads/comment_attachments/';
          if (!is_dir($uploadDir) && !mkdir($uploadDir, 0755, true) && !is_dir($uploadDir)) {
            $attachmentError = 'Unable to create upload directory for attachments.';
          } else {
            $fileName = 'comment_' . $issueId . '_' . time() . '_' . uniqid() . '.' . $fileExt;
            $filePath = $uploadDir . $fileName;

            if (move_uploaded_file($_FILES['comment_attachment']['tmp_name'], $filePath)) {
              $attachmentPath = 'uploads/comment_attachments/' . $fileName;
            } else {
              $attachmentError = 'Unable to save the attachment file.';
            }
          }
        }
      }
    }

    $this->db->insert('support_issue_comments', array(
      'issue_id' => $issueId,
      'employee_id' => $userId,
      'customer_comment' => 0,
      'comment' => $comment,
      'internal_note' => 0,
      'attachment_path' => $attachmentPath,
      'created_at' => date('Y-m-d H:i:s'),
      'settingsID' => $settingsID,
    ));

    $this->db
      ->where('id', $issueId)
      ->where('settingsID', $settingsID)
      ->update('support_issues', array(
        'status' => 'open',
        'client_reply_required' => 1,
        'updated_at' => date('Y-m-d H:i:s'),
      ));

    foreach ($this->_support_recipient_user_ids($settingsID, (int) $issue->department_id) as $recipientId) {
      if ($recipientId === $userId) {
        continue;
      }
      $this->_create_support_notification($settingsID, $recipientId, $userId, $issueId, (int) $issue->department_id, 'comment', 'Support Issue Updated', 'A new internal comment was added to ticket ' . $issue->ticket_number . '.');
    }

    $clientUserId = $this->_support_client_user_id($settingsID, (string) ($issue->customer_id ?? ''));
    if ($clientUserId > 0) {
      $this->_create_support_notification($settingsID, $clientUserId, $userId, $issueId, (int) $issue->department_id, 'reply', 'Support Team Replied', 'Your ticket ' . $issue->ticket_number . ' has a new update from the support team.');
    }
    $this->_send_support_email_notification(
      $settingsID,
      array((string) ($issue->customer_email ?? '')),
      'Support Ticket Reply: ' . (string) ($issue->ticket_number ?? ''),
      'A support staff member has replied to your ticket.',
      $issue
    );

    if ($attachmentError !== '') {
      $this->session->set_flashdata('warning', 'Comment added, but the attachment was not saved: ' . $attachmentError);
    } else {
      $this->session->set_flashdata('success', 'Comment added.');
    }
    redirect('Page/supportIssueView?id=' . $issueId);
  }

  public function editSupportIssueComment()
  {
    if (!$this->_can_manage_support_issues()) {
      echo 'Access Denied';
      return;
    }

    $settingsID = (int) ($this->session->userdata('settingsID') ?? 0);
    $userId = (int) ($this->session->userdata('user_id') ?? 0);
    $commentId = (int) $this->input->post('comment_id');
    $issueId = (int) $this->input->post('issue_id');
    $newComment = trim((string) $this->input->post('comment', true));

    $comment = $this->db
      ->select('*')
      ->from('support_issue_comments')
      ->where('id', $commentId)
      ->where('settingsID', $settingsID)
      ->limit(1)
      ->get()
      ->row();

    if (!$comment || (int) $comment->employee_id !== $userId) {
      $this->session->set_flashdata('danger', 'You can only edit your own comments.');
      redirect('Page/supportIssueView?id=' . $issueId);
      return;
    }

    if ($newComment === '') {
      $this->session->set_flashdata('danger', 'Comment cannot be empty.');
      redirect('Page/supportIssueView?id=' . $issueId);
      return;
    }

    $this->db
      ->where('id', $commentId)
      ->where('settingsID', $settingsID)
      ->update('support_issue_comments', array(
        'comment' => $newComment,
      ));

    $this->session->set_flashdata('success', 'Comment updated.');
    redirect('Page/supportIssueView?id=' . $issueId);
  }

  public function deleteSupportIssueComment()
  {
    if (!$this->_can_manage_support_issues()) {
      echo 'Access Denied';
      return;
    }

    $settingsID = (int) ($this->session->userdata('settingsID') ?? 0);
    $userId = (int) ($this->session->userdata('user_id') ?? 0);
    $commentId = (int) $this->input->get('comment_id');
    $issueId = (int) $this->input->get('issue_id');

    $comment = $this->db
      ->select('*')
      ->from('support_issue_comments')
      ->where('id', $commentId)
      ->where('settingsID', $settingsID)
      ->limit(1)
      ->get()
      ->row();

    if (!$comment || (int) $comment->employee_id !== $userId) {
      $this->session->set_flashdata('danger', 'You can only delete your own comments.');
      redirect('Page/supportIssueView?id=' . $issueId);
      return;
    }

    // Delete attachment file if exists
    if (!empty($comment->attachment_path)) {
      $filePath = FCPATH . $comment->attachment_path;
      if (file_exists($filePath)) {
        unlink($filePath);
      }
    }

    $this->db
      ->where('id', $commentId)
      ->where('settingsID', $settingsID)
      ->delete('support_issue_comments');

    $this->session->set_flashdata('success', 'Comment deleted.');
    redirect('Page/supportIssueView?id=' . $issueId);
  }

  public function forwardSupportIssue()
  {
    if (!$this->_can_manage_support_issues()) {
      echo 'Access Denied';
      return;
    }

    $settingsID = (int) ($this->session->userdata('settingsID') ?? 0);
    $userId = (int) ($this->session->userdata('user_id') ?? 0);
    $issueId = (int) $this->input->post('issue_id');
    $forwardTo = (int) $this->input->post('forward_to');
    $forwardNote = trim((string) $this->input->post('forward_note', true));
    $issue = $this->_support_issue_for_user($issueId, $settingsID, $userId);

    if (!$issue || $forwardTo <= 0) {
      $this->session->set_flashdata('danger', 'Issue could not be forwarded.');
      redirect('Page/supportIssueView?id=' . $issueId);
      return;
    }

    $employee = $this->_support_employee_row($settingsID, $forwardTo);
    $allowedUserIds = array_map(function ($row) {
      return (int) ($row->user_id ?? 0);
    }, $this->_support_assignable_users($settingsID, (int) ($issue->department_id ?? 0)));

    if (!$employee || !in_array($forwardTo, $allowedUserIds, true)) {
      $this->session->set_flashdata('danger', 'Selected employee was not found.');
      redirect('Page/supportIssueView?id=' . $issueId);
      return;
    }

    $employeeName = $this->_support_employee_name($employee);
    $forwardMessage = 'Forwarded to ' . $employeeName;
    if ($forwardNote !== '') {
      $forwardMessage .= '. Note: ' . $forwardNote;
    }

    $taskAssignmentNote = 'Support issue assigned to ' . $employeeName . '.';
    if ($forwardNote !== '') {
      $taskAssignmentNote .= ' Note: ' . $forwardNote;
    }

    $forwardedTaskId = $this->_assign_support_issue_task($issue, $forwardTo, $taskAssignmentNote, true);
    if ($forwardedTaskId <= 0) {
      $this->session->set_flashdata('danger', 'Issue was not forwarded because the task record could not be created.');
      redirect('Page/supportIssueView?id=' . $issueId);
      return;
    }

    $this->db
      ->where('id', $issueId)
      ->where('settingsID', $settingsID)
      ->update('support_issues', array(
        'assigned_employee_id' => $forwardTo,
        'task_id' => $forwardedTaskId,
        'status' => 'assigned',
        'updated_at' => date('Y-m-d H:i:s'),
      ));

    $this->db->insert('support_issue_comments', array(
      'issue_id' => $issueId,
      'employee_id' => $userId,
      'customer_comment' => 0,
      'comment' => $forwardMessage,
      'internal_note' => 1,
      'created_at' => date('Y-m-d H:i:s'),
      'settingsID' => $settingsID,
    ));

    $this->_create_support_notification($settingsID, $forwardTo, $userId, $issueId, (int) $issue->department_id, 'assigned', 'Support Issue Forwarded to You', 'Ticket ' . $issue->ticket_number . ' was forwarded to you for action.');
    $this->_send_support_email_notification(
      $settingsID,
      array($this->_support_user_email($settingsID, $forwardTo)),
      'Support Ticket Forwarded: ' . (string) ($issue->ticket_number ?? ''),
      'A support ticket was forwarded to you for action.',
      $issue
    );
    $clientUserId = $this->_support_client_user_id($settingsID, (string) ($issue->customer_id ?? ''));
    if ($clientUserId > 0) {
      $this->_create_support_notification($settingsID, $clientUserId, $userId, $issueId, (int) $issue->department_id, 'assigned', 'Support Ticket Assigned', 'Your ticket ' . $issue->ticket_number . ' is now assigned to ' . $employeeName . '.');
    }
    $this->_send_support_email_notification(
      $settingsID,
      array((string) ($issue->customer_email ?? '')),
      'Support Ticket Assigned: ' . (string) ($issue->ticket_number ?? ''),
      'Your ticket is now assigned to ' . $employeeName . '.',
      $issue
    );
    $this->session->set_flashdata('success', 'Issue forwarded successfully to ' . $employeeName . '.');
    redirect('Page/supportIssueView?id=' . $issueId);
  }

  public function tagSupportIssueUser()
  {
    if (!$this->_can_manage_support_issues()) {
      echo 'Access Denied';
      return;
    }

    $settingsID = (int) ($this->session->userdata('settingsID') ?? 0);
    $userId = (int) ($this->session->userdata('user_id') ?? 0);
    $issueId = (int) $this->input->post('issue_id');
    $tagNote = trim((string) $this->input->post('tag_note', true));
    $issue = $this->_support_issue_for_user($issueId, $settingsID, $userId);
    $tagUserIds = $this->input->post('tag_user_ids');
    if (!is_array($tagUserIds)) {
      $singleUserId = (int) $this->input->post('tag_user_id');
      $tagUserIds = $singleUserId > 0 ? array($singleUserId) : array();
    }
    $tagUserIds = array_values(array_unique(array_filter(array_map('intval', $tagUserIds))));

    if (!$issue || empty($tagUserIds)) {
      $this->session->set_flashdata('danger', 'User could not be tagged.');
      redirect('Page/supportIssueView?id=' . $issueId);
      return;
    }

    $taggedNames = array();
    foreach ($tagUserIds as $tagUserId) {
      $employee = $this->_support_employee_row($settingsID, $tagUserId);
      if (!$employee) {
        continue;
      }

      $employeeName = $this->_support_employee_name($employee);
      $taskNote = 'Assigned from support ticket ' . trim((string) ($issue->ticket_number ?? ('#' . $issueId))) . ' to ' . $employeeName . ' via tagging.';
      if ($tagNote !== '') {
        $taskNote .= ' Note: ' . $tagNote;
      }

      $taskId = $this->_support_create_tagged_task_copy($issue, $tagUserId, $taskNote);
      if ($taskId <= 0) {
        continue;
      }

      $taggedNames[] = $employeeName;
      $this->_create_support_notification($settingsID, $tagUserId, $userId, $issueId, (int) $issue->department_id, 'comment', 'You Were Tagged on a Support Issue', 'Ticket ' . $issue->ticket_number . ' needs your attention.');
      $this->_send_support_email_notification(
        $settingsID,
        array($this->_support_user_email($settingsID, $tagUserId)),
        'Support Ticket Tag: ' . (string) ($issue->ticket_number ?? ''),
        'You were tagged in a support ticket and may need to take action.',
        $issue
      );
    }

    if (empty($taggedNames)) {
      $this->session->set_flashdata('danger', 'Selected employees could not be tagged.');
      redirect('Page/supportIssueView?id=' . $issueId);
      return;
    }

    $tagMessage = 'Tagged ' . implode(', ', $taggedNames);
    if ($tagNote !== '') {
      $tagMessage .= '. Note: ' . $tagNote;
    }

    $this->db->insert('support_issue_comments', array(
      'issue_id' => $issueId,
      'employee_id' => $userId,
      'customer_comment' => 0,
      'comment' => $tagMessage,
      'internal_note' => 1,
      'created_at' => date('Y-m-d H:i:s'),
      'settingsID' => $settingsID,
    ));

    $this->_send_support_email_notification(
      $settingsID,
      array((string) ($issue->customer_email ?? '')),
      'Support Ticket Updated: ' . (string) ($issue->ticket_number ?? ''),
      'Additional support staff were tagged on your ticket.',
      $issue
    );

    $this->session->set_flashdata('success', 'User tagged successfully.');
    redirect('Page/supportIssueView?id=' . $issueId);
  }

  public function closeSupportIssue()
  {
    if (!$this->_can_manage_support_issues()) {
      echo 'Access Denied';
      return;
    }

    $settingsID = (int) ($this->session->userdata('settingsID') ?? 0);
    $userId = (int) ($this->session->userdata('user_id') ?? 0);
    $issueId = (int) $this->input->post('issue_id');
    $closeMessage = trim((string) $this->input->post('close_message', true));
    $issue = $this->_support_issue_for_user($issueId, $settingsID, $userId);

    if (!$issue || $closeMessage === '') {
      $this->session->set_flashdata('danger', 'Please enter the message for the client before closing the ticket.');
      redirect('Page/supportIssueView?id=' . $issueId);
      return;
    }

    $closedAt = date('Y-m-d H:i:s');
    $this->db
      ->where('id', $issueId)
      ->where('settingsID', $settingsID)
      ->update('support_issues', array(
        'status' => 'closed',
        'resolution_details' => $closeMessage,
        'resolution_date' => $closedAt,
        'resolved_by' => $userId,
        'client_reply_required' => 0,
      ));

    if ((int) ($issue->task_id ?? 0) > 0) {
      $this->db
        ->where('taskID', (int) $issue->task_id)
        ->where('settingsID', $settingsID)
        ->update('projects_task', array('taskStat' => '0'));

      $this->db->insert('projects_task_stat', array(
        'taskID' => (int) $issue->task_id,
        'note' => 'Support ticket closed. Message to client: ' . $closeMessage,
        'datePosted' => $closedAt,
        'postedBy' => trim((string) ($this->session->userdata('username') ?? 'system')),
        'taskStat' => '0',
      ));
    }

    $this->db->insert('support_issue_comments', array(
      'issue_id' => $issueId,
      'employee_id' => $userId,
      'customer_comment' => 0,
      'comment' => $closeMessage,
      'internal_note' => 0,
      'created_at' => date('Y-m-d H:i:s'),
      'settingsID' => $settingsID,
    ));

    $clientUserId = $this->_support_client_user_id($settingsID, (string) ($issue->customer_id ?? ''));
    if ($clientUserId > 0) {
      $this->_create_support_notification($settingsID, $clientUserId, $userId, $issueId, (int) $issue->department_id, 'closed', 'Support Ticket Closed', 'Your ticket ' . $issue->ticket_number . ' was marked closed. Open it to view the completion message or re-open it if needed.');
    }
    $this->_send_support_email_notification(
      $settingsID,
      array((string) ($issue->customer_email ?? '')),
      'Support Ticket Closed: ' . (string) ($issue->ticket_number ?? ''),
      $closeMessage,
      $issue
    );

    $this->session->set_flashdata('success', 'Ticket closed successfully.');
    redirect('Page/supportIssueView?id=' . $issueId);
  }

  public function submitClientTicketReply()
  {
    if (!$this->_is_client_user()) {
      echo 'Access Denied';
      return;
    }

    $settingsID = (int) ($this->session->userdata('settingsID') ?? 0);
    $userId = (int) ($this->session->userdata('user_id') ?? 0);
    $issueId = (int) $this->input->post('issue_id');
    $replyMessage = trim((string) $this->input->post('reply_message', true));
    $issue = $this->_support_issue_for_user($issueId, $settingsID, $userId);

    if (!$issue || $replyMessage === '') {
      $this->session->set_flashdata('danger', 'Please enter a message.');
      redirect('Page/clientTicketView?id=' . $issueId);
      return;
    }

    $statusKey = strtolower(trim((string) ($issue->status ?? 'open')));
    $wasClosed = in_array($statusKey, array('closed', 'resolved', 'done', 'completed'), true);

    if ($wasClosed) {
      $this->db
        ->where('id', $issueId)
        ->where('settingsID', $settingsID)
        ->update('support_issues', array(
          'status' => 'reopened',
          'client_reply_required' => 0,
        ));

      if ((int) ($issue->task_id ?? 0) > 0) {
        $this->db
          ->where('taskID', (int) $issue->task_id)
          ->where('settingsID', $settingsID)
          ->update('projects_task', array('taskStat' => '1'));
      }
    }

    $this->db->insert('support_issue_comments', array(
      'issue_id' => $issueId,
      'employee_id' => null,
      'customer_comment' => 1,
      'comment' => $replyMessage,
      'internal_note' => 0,
      'created_at' => date('Y-m-d H:i:s'),
      'settingsID' => $settingsID,
    ));

    foreach ($this->_support_recipient_user_ids($settingsID, (int) ($issue->department_id ?? 0)) as $recipientId) {
      $this->_create_support_notification($settingsID, $recipientId, $userId, $issueId, (int) $issue->department_id, 'client_reply', 'Client Reply', 'Client replied to ticket ' . $issue->ticket_number . '.');
    }

    $replyEmails = array();
    foreach ($this->_support_recipient_user_ids($settingsID, (int) ($issue->department_id ?? 0)) as $recipientId) {
      $replyEmails[] = $this->_support_user_email($settingsID, $recipientId);
    }
    $this->_send_support_email_notification(
      $settingsID,
      $replyEmails,
      'Client Reply: ' . (string) ($issue->ticket_number ?? ''),
      'A client has replied to this support ticket.',
      $issue
    );

    $this->session->set_flashdata('success', $wasClosed ? 'Your ticket has been reopened and reply sent.' : 'Your reply has been sent.');
    redirect('Page/clientTicketView?id=' . $issueId);
  }

  public function reopenSupportIssue()
  {
    if (!$this->_is_client_user()) {
      echo 'Access Denied';
      return;
    }

    $settingsID = (int) ($this->session->userdata('settingsID') ?? 0);
    $userId = (int) ($this->session->userdata('user_id') ?? 0);
    $issueId = (int) $this->input->post('issue_id');
    $reopenMessage = trim((string) $this->input->post('reopen_message', true));
    $issue = $this->_support_issue_for_user($issueId, $settingsID, $userId);

    if (!$issue || $reopenMessage === '') {
      $this->session->set_flashdata('danger', 'Please tell the team why the ticket should be reopened.');
      redirect('Page/clientTicketView?id=' . $issueId);
      return;
    }

    $this->db
      ->where('id', $issueId)
      ->where('settingsID', $settingsID)
      ->update('support_issues', array(
        'status' => 'reopened',
        'client_reply_required' => 0,
      ));

    if ((int) ($issue->task_id ?? 0) > 0) {
      $this->db
        ->where('taskID', (int) $issue->task_id)
        ->where('settingsID', $settingsID)
        ->update('projects_task', array('taskStat' => '1'));
    }

    $this->db->insert('support_issue_comments', array(
      'issue_id' => $issueId,
      'employee_id' => null,
      'customer_comment' => 1,
      'comment' => $reopenMessage,
      'internal_note' => 0,
      'created_at' => date('Y-m-d H:i:s'),
      'settingsID' => $settingsID,
    ));

    foreach ($this->_support_recipient_user_ids($settingsID, (int) ($issue->department_id ?? 0)) as $recipientId) {
      $this->_create_support_notification($settingsID, $recipientId, $userId, $issueId, (int) $issue->department_id, 'reopened', 'Support Ticket Reopened', 'Ticket ' . $issue->ticket_number . ' was reopened by the client.');
    }
    $reopenEmails = array();
    foreach ($this->_support_recipient_user_ids($settingsID, (int) ($issue->department_id ?? 0)) as $recipientId) {
      $reopenEmails[] = $this->_support_user_email($settingsID, $recipientId);
    }
    $this->_send_support_email_notification(
      $settingsID,
      $reopenEmails,
      'Support Ticket Reopened: ' . (string) ($issue->ticket_number ?? ''),
      'A client reopened this support ticket and is waiting for further assistance.',
      $issue
    );

    $this->session->set_flashdata('success', 'Your ticket has been reopened.');
    redirect('Page/clientTicketView?id=' . $issueId);
  }

  public function saveClientRequest()
  {
    if (!$this->_is_client_user()) {
      echo "Access Denied";
      return;
    }

    $settingsID = (int) ($this->session->userdata('settingsID') ?? 0);
    $client = $this->_load_current_client();

    if (!$client) {
      show_404();
      return;
    }

    $custID = trim((string) ($client->CustID ?? ''));

    if ($this->input->method(true) === 'POST') {
      $task = $this->_normalizeTaskLabel($this->input->post('task'));
      $projectID = (int) $this->input->post('projectID');
      $priority = trim((string) $this->input->post('priority', 'Normal'));

      if ($task === '' || $projectID <= 0) {
        $this->session->set_flashdata('danger', 'Task description and project are required.');
        redirect('Page/clientRequestForm');
        return;
      }

      // Verify project belongs to this customer
      $project = $this->db->select('projectID, CustID')
        ->from('projects')
        ->where('projectID', $projectID)
        ->where('CustID', $custID)
        ->where('settingsID', $settingsID)
        ->get()
        ->row();

      if (!$project) {
        $this->session->set_flashdata('danger', 'Invalid project selected.');
        redirect('Page/clientRequestForm');
        return;
      }

      // Get the first admin user as default assignee
      $adminUser = $this->db->select('user_id')
        ->from('users')
        ->where('level', 'Admin')
        ->where('settingsID', $settingsID)
        ->limit(1)
        ->get()
        ->row();

      $assignedPerson = $adminUser ? (int) $adminUser->user_id : 0;

      // Insert into projects_task
      $taskData = [
        'taskID' => 0,
        'task' => $task,
        'reportedDate' => date('Y-m-d'),
        'projectID' => $projectID,
        'taskStat' => '1',
        'priority' => $priority,
        'settingsID' => $settingsID,
        'assignedPerson' => $assignedPerson,
        'added_by' => 'client_' . $custID
      ];

      $this->db->insert('projects_task', $taskData);
      $taskId = (int) $this->db->insert_id();

      if ($taskId > 0) {
        // Add to projects_task_stat
        $statData = [
          'taskID' => $taskId,
          'note' => 'Request submitted by client',
          'points' => 1,
          'datePosted' => date('Y-m-d H:i:s'),
          'postedBy' => 'client_' . $custID,
          'taskStat' => '1'
        ];

        $this->db->insert('projects_task_stat', $statData);

        $this->session->set_flashdata('success', 'Your request has been submitted successfully. Reference ID: #' . $taskId);
      } else {
        $this->session->set_flashdata('danger', 'Failed to submit request. Please try again.');
      }
    }

    redirect('Page/clientRequestForm');
  }

  public function requestedToday()
  {
    if (!$this->_is_client_user()) {
      echo "Access Denied";
      return;
    }

    $settingsID = (int) ($this->session->userdata('settingsID') ?? 0);
    $client = $this->_load_current_client();

    if (!$client) {
      show_404();
      return;
    }

    $custID = trim((string) ($client->CustID ?? ''));
    $today = date('Y-m-d');

    // Fetch tasks requested today
    $this->db->select("
        projects_task.taskID,
        projects_task.task,
        projects_task.reportedDate,
        projects_task.dueDate,
        projects_task.priority,
        projects.projectDescription,
        projects.CustID,
        CONCAT(users.fName, ' ', users.lName) AS assignedPersonName
    ");
    $this->db->from('projects_task');
    $this->db->join('projects', 'projects.projectID = projects_task.projectID', 'left');
    $this->db->join('users', 'users.user_id = projects_task.assignedPerson', 'left');
    $this->db->where('projects_task.settingsID', $settingsID);
    $this->db->where('projects.CustID', $custID);
    $this->db->where('projects_task.reportedDate', $today);
    $this->db->order_by('projects_task.reportedDate', 'DESC');
    $this->db->order_by('projects_task.priority', 'ASC');
    $this->db->order_by('projects_task.taskID', 'DESC');
    $result['tasks'] = $this->db->get()->result();
    $result['client'] = $client;
    $result['pageTitle'] = 'Requested Today';
    $result['pageSubtitle'] = 'Tasks requested today';

    $this->load->view('client_task_list', $result);
  }

  public function accomplished()
  {
    if (!$this->_is_client_user()) {
      echo "Access Denied";
      return;
    }

    $settingsID = (int) ($this->session->userdata('settingsID') ?? 0);
    $client = $this->_load_current_client();

    if (!$client) {
      show_404();
      return;
    }

    $custID = trim((string) ($client->CustID ?? ''));

    // Fetch accomplished tasks
    $this->db->select("
        projects_task.taskID,
        projects_task.task,
        projects_task.reportedDate,
        projects_task.dueDate,
        projects_task.priority,
        projects.projectDescription,
        projects.CustID,
        CONCAT(users.fName, ' ', users.lName) AS assignedPersonName,
        pts.datePosted AS completedDate
    ");
    $this->db->from('projects_task');
    $this->db->join('projects', 'projects.projectID = projects_task.projectID', 'left');
    $this->db->join('users', 'users.user_id = projects_task.assignedPerson', 'left');
    $this->db->join('projects_task_stat pts', 'pts.taskID = projects_task.taskID AND pts.taskStat = "0"', 'left');
    $this->db->where('projects_task.settingsID', $settingsID);
    $this->db->where('projects.CustID', $custID);
    $this->db->where('projects_task.taskStat', '0');
    $this->db->order_by('pts.datePosted', 'DESC');
    $this->db->order_by('projects_task.priority', 'ASC');
    $this->db->order_by('projects_task.taskID', 'DESC');
    $result['tasks'] = $this->db->get()->result();
    $result['client'] = $client;
    $result['pageTitle'] = 'Accomplished';
    $result['pageSubtitle'] = 'Completed tasks';

    $this->load->view('client_task_list', $result);
  }

  public function pending()
  {
    if (!$this->_is_client_user()) {
      echo "Access Denied";
      return;
    }

    $settingsID = (int) ($this->session->userdata('settingsID') ?? 0);
    $client = $this->_load_current_client();

    if (!$client) {
      show_404();
      return;
    }

    $custID = trim((string) ($client->CustID ?? ''));

    // Fetch pending tasks
    $this->db->select("
        projects_task.taskID,
        projects_task.task,
        projects_task.reportedDate,
        projects_task.dueDate,
        projects_task.priority,
        projects.projectDescription,
        projects.CustID,
        CONCAT(users.fName, ' ', users.lName) AS assignedPersonName
    ");
    $this->db->from('projects_task');
    $this->db->join('projects', 'projects.projectID = projects_task.projectID', 'left');
    $this->db->join('users', 'users.user_id = projects_task.assignedPerson', 'left');
    $this->db->where('projects_task.settingsID', $settingsID);
    $this->db->where('projects.CustID', $custID);
    $this->db->where('projects_task.taskStat', '1');
    $this->db->order_by('projects_task.dueDate', 'ASC');
    $this->db->order_by('projects_task.priority', 'ASC');
    $this->db->order_by('projects_task.taskID', 'DESC');
    $result['tasks'] = $this->db->get()->result();
    $result['client'] = $client;
    $result['pageTitle'] = 'Pending';
    $result['pageSubtitle'] = 'Open tasks';

    $this->load->view('client_task_list', $result);
  }

  public function posStaff()
  {
    redirect('Pos/posStaff');
  }

  public function posAdmin()
  {
    redirect('Pos/posAdmin');
  }

  public function posNewTransaction()
  {
    redirect('Pos/posNewTransaction');
  }

  public function posStoreTransaction()
  {
    redirect('Pos/posStoreTransaction', 'location', $this->input->method() === 'post' ? 307 : 302);
  }

  public function posTransactionHistory()
  {
    redirect('Pos/posTransactionHistory');
  }

  public function posReturnsVoids()
  {
    redirect('Pos/posReturnsVoids');
  }

  public function posTransactionDetail($saleId = null)
  {
    redirect('Pos/posTransactionDetail/' . (int) $saleId);
  }

  public function posRecordPayment($saleId = null)
  {
    redirect('Pos/posRecordPayment/' . (int) $saleId, 'location', $this->input->method() === 'post' ? 307 : 302);
  }

  public function posVoidTransaction($saleId = null)
  {
    redirect('Pos/posVoidTransaction/' . (int) $saleId, 'location', $this->input->method() === 'post' ? 307 : 302);
  }

  public function posReports()
  {
    redirect('Pos/posReports');
  }

  public function posAddProduct()
  {
    redirect('Pos/posProductList');
  }

  public function posProductList()
  {
    redirect('Pos/posProductList');
  }

  public function posCategorySettings()
  {
    redirect('Pos/posCategorySettings');
  }

  public function posCategoryCreate()
  {
    redirect('Pos/posCategoryCreate', 'location', $this->input->method() === 'post' ? 307 : 302);
  }

  public function posCategoryUpdate()
  {
    redirect('Pos/posCategoryUpdate', 'location', $this->input->method() === 'post' ? 307 : 302);
  }

  public function posCategoryDelete()
  {
    $id = (int) $this->input->get('id');
    redirect('Pos/posCategoryDelete' . ($id > 0 ? '?id=' . $id : ''));
  }

  public function posCreateProduct()
  {
    redirect('Pos/posCreateProduct', 'location', $this->input->method() === 'post' ? 307 : 302);
  }

  public function posEditProduct($id = null, $renderNow = false)
  {
    redirect('Pos/posEditProduct/' . (int) $id);
  }

  public function posUpdateProduct($id = null)
  {
    redirect('Pos/posUpdateProduct/' . (int) $id, 'location', $this->input->method() === 'post' ? 307 : 302);
  }

  public function posDeleteProduct($id = null)
  {
    redirect('Pos/posDeleteProduct/' . (int) $id);
  }

  public function posExpiringSoon()
  {
    redirect('Pos/posExpiringSoon');
  }

  public function posExpiredProducts()
  {
    redirect('Pos/posExpiredProducts');
  }

  public function posStockLevels()
  {
    redirect('Pos/posStockLevels');
  }

  public function posLowStockItems()
  {
    redirect('Pos/posLowStockItems');
  }

  public function ranking()
  {
    $settingsID = $this->session->userdata('settingsID');
    $defaultYear = date('Y');
    $defaultMonth = date('n');

    $year = $defaultYear;
    $month = null;
    $filterApplied = false;

    if ($this->input->post('filter')) {
      $yearInput = $this->input->post('year');
      $monthInput = $this->input->post('month');

      if ($yearInput === 'all') {
        $year = null;
        $month = null;
      } else {
        $year = !empty($yearInput) ? $yearInput : $defaultYear;
        $month = (!empty($monthInput) && $monthInput !== 'all') ? (int) $monthInput : null;
      }

      $filterApplied = true;
    }

    $data['selected_year'] = $year ?? 'all';
    $data['selected_month'] = $month;
    $data['filter_applied'] = $filterApplied;
    $data['ranking'] = $this->CashModel->getTaskRanking($settingsID, $year, $month);

    $this->load->view('task_ranking', $data);
  }


  function dtr()
  {
    $settingsID = $this->session->userdata('settingsID');
    $id = $this->session->userdata('username');

    $raw = $this->CashModel->dtr($settingsID, $id);
    $result['data'] = $this->_aggregateDtrForUser($raw);
    $this->load->view('dtr', $result);
  }

  function empDTR()
  {

    if ($this->session->userdata('level') === 'Admin') {
      $settingsID = $this->session->userdata('settingsID');
      $selectedId = trim((string) $this->input->get('id'));
      $selectedName = trim((string) $this->input->get('name'));

      $monthInput = (int) $this->input->get('month');
      $yearInput = (int) $this->input->get('year');

      $month = ($monthInput >= 1 && $monthInput <= 12) ? $monthInput : (int) date('n');
      $year = ($yearInput >= 2000 && $yearInput <= 2100) ? $yearInput : (int) date('Y');

      $employees = $this->CashModel->getStaff($settingsID);

      $identifier = $selectedId !== '' ? $selectedId : $selectedName;
      $employee = null;
      if ($identifier !== '') {
        $employee = $this->CashModel->getUserFlexible($settingsID, $identifier);
      }

      $employeeName = 'Employee DTR';
      if ($employee) {
        $employeeName = trim((string) $employee->fName . ' ' . (string) $employee->lName);
      } elseif ($selectedName !== '') {
        $employeeName = $selectedName;
      }

      $dtrIdentifier = $employee && !empty($employee->username) ? (string) $employee->username : $identifier;

      $dataRows = [];
      $taskCounts = [];
      $monthTotalSeconds = 0;
      $presentDays = 0;
      $absentDays = 0;
      $pendingDays = 0;

      if ($dtrIdentifier !== '') {
        $startDate = sprintf('%04d-%02d-01', $year, $month);
        $endDate = date('Y-m-t', strtotime($startDate));

        $raw = $this->CashModel->attendanceListByEmployeeRange($settingsID, $dtrIdentifier, $startDate, $endDate);
        $aggregated = !empty($raw) ? $this->_aggregateDtrForUser($raw) : [];

        $byDate = [];
        foreach ($aggregated as $item) {
          $ts = strtotime((string) $item->logDate);
          if ($ts !== false) {
            $item->logDate = date('Y-m-d', $ts);
          }
          $byDate[$item->logDate] = $item;
        }

        $tasks = $this->CashModel->accomplishmentsStaffFiltered($settingsID, $dtrIdentifier, $month, $year);
        foreach ($tasks as $task) {
          if (!empty($task->datePosted)) {
            $taskDate = date('Y-m-d', strtotime((string) $task->datePosted));
            $taskCounts[$taskDate] = ($taskCounts[$taskDate] ?? 0) + 1;
          }
        }

        $daysInMonth = (int) date('t', strtotime($startDate));
        $today = date('Y-m-d');
        $todayYear = (int) date('Y', strtotime($today));
        $todayMonth = (int) date('n', strtotime($today));
        $todayDay = (int) date('j', strtotime($today));
        $lastDay = $daysInMonth;
        if ($year === $todayYear && $month === $todayMonth) {
          $lastDay = min($daysInMonth, $todayDay);
        }

        for ($day = 1; $day <= $lastDay; $day++) {
          $dateKey = sprintf('%04d-%02d-%02d', $year, $month, $day);
          if (isset($byDate[$dateKey])) {
            $entry = $byDate[$dateKey];
          } else {
            $entry = (object) [
              'logDate' => $dateKey,
              'intervals' => [],
              'am_intervals' => [],
              'pm_intervals' => [],
              'total_seconds' => 0,
              'total_label' => $this->_formatSeconds(0),
            ];
          }
          $entry->is_absent = empty($entry->am_intervals) && empty($entry->pm_intervals);
          $hasOpen = false;
          foreach (['am_intervals', 'pm_intervals'] as $bucket) {
            if (!empty($entry->{$bucket}) && is_array($entry->{$bucket})) {
              foreach ($entry->{$bucket} as $intv) {
                if (!empty($intv['open'])) {
                  $hasOpen = true;
                  break 2;
                }
              }
            }
          }
          $entry->is_pending = !$entry->is_absent && $hasOpen;
          $dataRows[] = $entry;
          if (!empty($entry->total_seconds)) {
            $monthTotalSeconds += (int) $entry->total_seconds;
          }
          if ($entry->is_absent) {
            $absentDays++;
          } elseif ($entry->is_pending) {
            $pendingDays++;
          } else {
            $presentDays++;
          }
        }
      }

      $result = [
        'data' => $dataRows,
        'employees' => $employees,
        'selected_employee' => $dtrIdentifier,
        'selected_employee_name' => $employeeName,
        'selected_month' => $month,
        'selected_year' => $year,
        'task_counts' => $taskCounts,
        'month_total_label' => $this->_formatSeconds($monthTotalSeconds),
        'month_total_seconds' => $monthTotalSeconds,
        'present_days' => $presentDays,
        'absent_days' => $absentDays,
        'pending_days' => $pendingDays,
        'filter_applied' => $dtrIdentifier !== '',
        'today_personnel' => $this->CashModel->getTodayPersonnelWithTimeIn($settingsID, date('Y-m-d')),
        'settingsID' => $settingsID,
      ];

      $this->load->view('dtr_employee', $result);
    } else {
    }
  }

  function myDTR()
  {
    // Allow Staff level users to view their own DTR
    $level = $this->session->userdata('level');
    if (!in_array($level, array('Staff', 'Encoder', 'Admin'), true)) {
      echo "Access Denied";
      return;
    }

    $settingsID = $this->session->userdata('settingsID');
    $username = $this->session->userdata('username');
    $name = $this->session->userdata('name');

    $monthInput = (int) $this->input->get('month');
    $yearInput = (int) $this->input->get('year');

    $month = ($monthInput >= 1 && $monthInput <= 12) ? $monthInput : (int) date('n');
    $year = ($yearInput >= 2000 && $yearInput <= 2100) ? $yearInput : (int) date('Y');

    // Use the logged-in user's username as the DTR identifier
    $dtrIdentifier = $username;
    $employeeName = $name;

    $dataRows = [];
    $taskCounts = [];
    $monthTotalSeconds = 0;
    $presentDays = 0;
    $absentDays = 0;
    $pendingDays = 0;

    if ($dtrIdentifier !== '') {
      $startDate = sprintf('%04d-%02d-01', $year, $month);
      $endDate = date('Y-m-t', strtotime($startDate));

      $raw = $this->CashModel->attendanceListByEmployeeRange($settingsID, $dtrIdentifier, $startDate, $endDate);
      $aggregated = !empty($raw) ? $this->_aggregateDtrForUser($raw) : [];

      $byDate = [];
      foreach ($aggregated as $item) {
        $ts = strtotime((string) $item->logDate);
        if ($ts !== false) {
          $item->logDate = date('Y-m-d', $ts);
        }
        $byDate[$item->logDate] = $item;
      }

      $tasks = $this->CashModel->accomplishmentsStaffFiltered($settingsID, $dtrIdentifier, $month, $year);
      foreach ($tasks as $task) {
        if (!empty($task->datePosted)) {
          $taskDate = date('Y-m-d', strtotime((string) $task->datePosted));
          $taskCounts[$taskDate] = ($taskCounts[$taskDate] ?? 0) + 1;
        }
      }

      $daysInMonth = (int) date('t', strtotime($startDate));
      $today = date('Y-m-d');
      $todayYear = (int) date('Y', strtotime($today));
      $todayMonth = (int) date('n', strtotime($today));
      $todayDay = (int) date('j', strtotime($today));
      $lastDay = $daysInMonth;
      if ($year === $todayYear && $month === $todayMonth) {
        $lastDay = min($daysInMonth, $todayDay);
      }

      for ($day = 1; $day <= $lastDay; $day++) {
        $dateKey = sprintf('%04d-%02d-%02d', $year, $month, $day);
        if (isset($byDate[$dateKey])) {
          $entry = $byDate[$dateKey];
        } else {
          $entry = (object) [
            'logDate' => $dateKey,
            'intervals' => [],
            'am_intervals' => [],
            'pm_intervals' => [],
            'total_seconds' => 0,
            'total_label' => $this->_formatSeconds(0),
          ];
        }
        $entry->is_absent = empty($entry->am_intervals) && empty($entry->pm_intervals);
        $hasOpen = false;
        foreach (['am_intervals', 'pm_intervals'] as $bucket) {
          if (!empty($entry->{$bucket}) && is_array($entry->{$bucket})) {
            foreach ($entry->{$bucket} as $intv) {
              if (!empty($intv['open'])) {
                $hasOpen = true;
                break 2;
              }
            }
          }
        }
        $entry->is_pending = !$entry->is_absent && $hasOpen;
        $dataRows[] = $entry;
        if (!empty($entry->total_seconds)) {
          $monthTotalSeconds += (int) $entry->total_seconds;
        }
        if ($entry->is_absent) {
          $absentDays++;
        } elseif ($entry->is_pending) {
          $pendingDays++;
        } else {
          $presentDays++;
        }
      }
    }

    $result = [
      'data' => $dataRows,
      'employees' => [], // Empty for employee view (no selector needed)
      'selected_employee' => $dtrIdentifier,
      'selected_employee_name' => $employeeName,
      'selected_month' => $month,
      'selected_year' => $year,
      'task_counts' => $taskCounts,
      'month_total_label' => $this->_formatSeconds($monthTotalSeconds),
      'month_total_seconds' => $monthTotalSeconds,
      'present_days' => $presentDays,
      'absent_days' => $absentDays,
      'pending_days' => $pendingDays,
      'filter_applied' => true, // Always true for myDTR
      'is_my_dtr' => true, // Flag to indicate this is personal DTR view
    ];

    $this->load->view('dtr_my', $result);
  }


  function reports()
  {

    if ($this->session->userdata('level') === 'Admin') {
      $settingsID = $this->session->userdata('settingsID');
      $result['data'] = $this->CashModel->totalPayments($settingsID);
      $result['data1'] = $this->CashModel->totalExpenses($settingsID);
      $result['data4'] = $this->CashModel->projectSummary($settingsID);
      $this->load->view('dashboard_reports', $result);
    } else {
      echo "Access Denied";
    }
  }

  function revenueReports()
  {
    if ($this->session->userdata('level') !== 'Admin') {
      echo "Access Denied";
      return;
    }

    $settingsID = (int) $this->session->userdata('settingsID');
    $this->_generateRecurringInvoices($settingsID);
    $this->_markRecurringInvoiceGeneratorRun($settingsID);

    date_default_timezone_set('Asia/Manila');
    $currentYear = date('Y');
    $dateFrom = $this->_normalizeDateInput(trim((string) $this->input->get('date_from')));
    $dateTo = $this->_normalizeDateInput(trim((string) $this->input->get('date_to')));

    if ($dateFrom === null && $dateTo === null) {
      $dateFrom = $currentYear . '-01-01';
      $dateTo = $currentYear . '-12-31';
    } elseif ($dateFrom === null) {
      $dateFrom = $dateTo;
    } elseif ($dateTo === null) {
      $dateTo = $dateFrom;
    }

    if (strtotime($dateFrom) > strtotime($dateTo)) {
      $swap = $dateFrom;
      $dateFrom = $dateTo;
      $dateTo = $swap;
    }

    $frequencyInput = strtolower(trim((string) $this->input->get('recurring_frequency')));
    $allowedFrequencies = array('all', 'daily', 'weekly', 'monthly', 'quarterly', 'yearly');
    $recurringFrequencyFilter = in_array($frequencyInput, $allowedFrequencies, true) ? $frequencyInput : 'all';
    $selectedCustID = trim((string) $this->input->get('cust_id'));
    $selectedPaymentSource = trim((string) $this->input->get('payment_source'));
    $selectedCashier = trim((string) $this->input->get('cashier'));

    $recurringTemplates = $this->CashModel->recurringInvoiceTemplates(
      $settingsID,
      $recurringFrequencyFilter !== 'all' ? $recurringFrequencyFilter : '',
      $selectedCustID
    );
    $acceptedPayments = $this->CashModel->acceptedPaymentReportEntries(
      $settingsID,
      $dateFrom,
      $dateTo,
      $selectedPaymentSource,
      $selectedCashier,
      $selectedCustID
    );

    $recurringReport = $this->_buildRecurringRevenueReport($recurringTemplates, $dateFrom, $dateTo);
    $collectionReport = $this->_buildAcceptedCollectionsReport($acceptedPayments, $dateFrom, $dateTo);
    $businessDetails = $this->CashModel->businessDetails($settingsID);

    $result = array(
      'business' => !empty($businessDetails) ? $businessDetails[0] : null,
      'clients' => $this->CashModel->getClients($settingsID),
      'paymentSources' => $this->CashModel->paymentSources($settingsID),
      'paymentCashiers' => $this->CashModel->paymentCashiers($settingsID),
      'filterDateFrom' => $dateFrom,
      'filterDateTo' => $dateTo,
      'rangeLabel' => $this->_formatReportDateRangeLabel($dateFrom, $dateTo),
      'generatedAt' => date('F j, Y h:i A'),
      'recurringFrequencyFilter' => $recurringFrequencyFilter,
      'selectedCustID' => $selectedCustID,
      'selectedPaymentSource' => $selectedPaymentSource,
      'selectedCashier' => $selectedCashier,
      'recurringTemplates' => $recurringReport['templates'],
      'recurringFrequencySummaries' => $recurringReport['frequencySummaries'],
      'projectedIncomeByMonth' => $recurringReport['monthlyProjection'],
      'projectedRecurringTotals' => $recurringReport['totals'],
      'acceptedPayments' => $collectionReport['entries'],
      'collectionTotals' => $collectionReport['totals'],
      'acceptedPaymentMonthlySummaries' => $collectionReport['monthlySummaries'],
      'acceptedPaymentYearlySummaries' => $collectionReport['yearlySummaries'],
      'acceptedPaymentSourceSummaries' => $collectionReport['sourceSummaries'],
      'acceptedPaymentCashierSummaries' => $collectionReport['cashierSummaries'],
      'monthlyComparisonSummaries' => $this->_buildProjectedActualComparison(
        $recurringReport['monthlyProjection'],
        $collectionReport['monthlySummaries']
      ),
    );

    $this->load->view('revenue_reports', $result);
  }

  function yearlyReport()
  {
    if ($this->session->userdata('level') !== 'Admin') {
      echo "Access Denied";
      return;
    }

    $settingsID = (int) $this->session->userdata('settingsID');
    $this->_generateRecurringInvoices($settingsID);
    $this->_markRecurringInvoiceGeneratorRun($settingsID);

    date_default_timezone_set('Asia/Manila');
    $currentYear = date('Y');
    $selectedYear = trim((string) $this->input->get('year'));

    if ($selectedYear === '') {
      $selectedYear = $currentYear;
    }

    // Validate year input
    if (!is_numeric($selectedYear) || strlen($selectedYear) !== 4 || $selectedYear < 2020 || $selectedYear > $currentYear) {
      $selectedYear = $currentYear;
    }

    // Get available years for dropdown
    $availableYears = array();
    for ($year = $currentYear; $year >= 2020; $year--) {
      $availableYears[] = $year;
    }

    // Get data for the selected year
    $yearStart = $selectedYear . '-01-01';
    $yearEnd = $selectedYear . '-12-31';

    // Get all payments for the year
    $payments = $this->CashModel->acceptedPaymentReportEntries(
      $settingsID,
      $yearStart,
      $yearEnd,
      '', // all payment sources
      '', // all cashiers
      ''  // all customers
    );

    // Get all invoices for the year
    $invoices = $this->CashModel->invList($settingsID);
    $yearInvoices = array_filter($invoices, function ($invoice) use ($selectedYear) {
      $invoiceDate = $invoice->TransDate ?? null;
      if ($invoiceDate && $invoiceDate !== '0000-00-00' && $invoiceDate !== '') {
        $invoiceYear = date('Y', strtotime($invoiceDate));
        return $invoiceYear == $selectedYear;
      }
      return false;
    });

    // Build yearly data
    $monthlyData = $this->_buildYearlyMonthlyData($payments, $yearInvoices, $selectedYear);
    $yearlyTotals = $this->_buildYearlyTotals($payments, $yearInvoices);
    $comparisonData = $this->_buildYearlyComparison($payments, $yearInvoices, $selectedYear);

    $businessDetails = $this->CashModel->businessDetails($settingsID);

    $result = array(
      'business' => !empty($businessDetails) ? $businessDetails[0] : null,
      'selectedYear' => $selectedYear,
      'availableYears' => $availableYears,
      'currentYear' => $currentYear,
      'monthlyData' => $monthlyData,
      'yearlyTotals' => $yearlyTotals,
      'comparisonData' => $comparisonData,
      'generatedAt' => date('F j, Y h:i A'),
    );

    $this->load->view('yearly_report', $result);
  }

  private function _buildYearlyMonthlyData($payments, $invoices, $year)
  {
    $monthlyData = array();

    // Initialize all 12 months
    for ($month = 1; $month <= 12; $month++) {
      $monthName = date('F', mktime(0, 0, 0, $month, 1, $year));
      $monthlyData[$month] = array(
        'month' => $monthName,
        'monthNum' => $month,
        'invoiceCount' => 0,
        'invoiceTotal' => 0,
        'paymentCount' => 0,
        'paymentTotal' => 0,
        'netIncome' => 0
      );
    }

    // Process invoices
    foreach ($invoices as $invoice) {
      $invoiceDate = $invoice->TransDate ?? null;
      if ($invoiceDate && $invoiceDate !== '0000-00-00' && $invoiceDate !== '') {
        $month = (int) date('n', strtotime($invoiceDate));
        if ($month >= 1 && $month <= 12) {
          $monthlyData[$month]['invoiceCount']++;
          $monthlyData[$month]['invoiceTotal'] += (float) ($invoice->TotalDue ?? 0);
        }
      }
    }

    // Process payments
    foreach ($payments as $payment) {
      $paymentDate = $payment->PDate ?? null;
      if ($paymentDate && $paymentDate !== '0000-00-00' && $paymentDate !== '') {
        $month = (int) date('n', strtotime($paymentDate));
        if ($month >= 1 && $month <= 12) {
          $monthlyData[$month]['paymentCount']++;
          $monthlyData[$month]['paymentTotal'] += (float) ($payment->AmountPaid ?? 0);
        }
      }
    }

    // Calculate net income
    foreach ($monthlyData as $month => &$data) {
      $data['netIncome'] = $data['paymentTotal'] - $data['invoiceTotal'];
    }

    return array_values($monthlyData);
  }

  private function _buildYearlyTotals($payments, $invoices)
  {
    $totalInvoices = 0;
    $totalInvoiceAmount = 0;
    $totalPayments = 0;
    $totalPaymentAmount = 0;

    foreach ($invoices as $invoice) {
      $totalInvoices++;
      $totalInvoiceAmount += (float) ($invoice->TotalDue ?? 0);
    }

    foreach ($payments as $payment) {
      $totalPayments++;
      $totalPaymentAmount += (float) ($payment->AmountPaid ?? 0);
    }

    return array(
      'totalInvoices' => $totalInvoices,
      'totalInvoiceAmount' => $totalInvoiceAmount,
      'totalPayments' => $totalPayments,
      'totalPaymentAmount' => $totalPaymentAmount,
      'netIncome' => $totalPaymentAmount - $totalInvoiceAmount,
      'averageInvoiceAmount' => $totalInvoices > 0 ? $totalInvoiceAmount / $totalInvoices : 0,
      'averagePaymentAmount' => $totalPayments > 0 ? $totalPaymentAmount / $totalPayments : 0
    );
  }

  private function _buildYearlyComparison($payments, $invoices, $selectedYear)
  {
    // Get previous year data for comparison
    $previousYear = (int) $selectedYear - 1;
    $previousYearStart = $previousYear . '-01-01';
    $previousYearEnd = $previousYear . '-12-31';

    $settingsID = (int) $this->session->userdata('settingsID');

    $previousPayments = $this->CashModel->acceptedPaymentReportEntries(
      $settingsID,
      $previousYearStart,
      $previousYearEnd,
      '',
      '',
      ''
    );

    $previousInvoices = $this->CashModel->invList($settingsID);
    $previousYearInvoices = array_filter($previousInvoices, function ($invoice) use ($previousYear) {
      $invoiceDate = $invoice->TransDate ?? null;
      if ($invoiceDate && $invoiceDate !== '0000-00-00' && $invoiceDate !== '') {
        $invoiceYear = date('Y', strtotime($invoiceDate));
        return $invoiceYear == $previousYear;
      }
      return false;
    });

    $currentTotalPayments = 0;
    $currentTotalInvoices = 0;
    $previousTotalPayments = 0;
    $previousTotalInvoices = 0;

    foreach ($payments as $payment) {
      $currentTotalPayments += (float) ($payment->AmountPaid ?? 0);
    }

    foreach ($invoices as $invoice) {
      $currentTotalInvoices += (float) ($invoice->TotalDue ?? 0);
    }

    foreach ($previousPayments as $payment) {
      $previousTotalPayments += (float) ($payment->AmountPaid ?? 0);
    }

    foreach ($previousYearInvoices as $invoice) {
      $previousTotalInvoices += (float) ($invoice->TotalDue ?? 0);
    }

    return array(
      'currentYear' => array(
        'totalPayments' => $currentTotalPayments,
        'totalInvoices' => $currentTotalInvoices,
        'netIncome' => $currentTotalPayments - $currentTotalInvoices
      ),
      'previousYear' => array(
        'totalPayments' => $previousTotalPayments,
        'totalInvoices' => $previousTotalInvoices,
        'netIncome' => $previousTotalPayments - $previousTotalInvoices
      ),
      'paymentGrowth' => $previousTotalPayments > 0 ? (($currentTotalPayments - $previousTotalPayments) / $previousTotalPayments) * 100 : 0,
      'invoiceGrowth' => $previousTotalInvoices > 0 ? (($currentTotalInvoices - $previousTotalInvoices) / $previousTotalInvoices) * 100 : 0,
      'netIncomeGrowth' => ($previousTotalPayments - $previousTotalInvoices) > 0 ? ((($currentTotalPayments - $currentTotalInvoices) - ($previousTotalPayments - $previousTotalInvoices)) / ($previousTotalPayments - $previousTotalInvoices)) * 100 : 0
    );
  }

  function invoiceStatusReport()
  {
    if ($this->session->userdata('level') !== 'Admin') {
      echo "Access Denied";
      return;
    }

    $settingsID = (int) $this->session->userdata('settingsID');
    $this->_generateRecurringInvoices($settingsID);
    $this->_markRecurringInvoiceGeneratorRun($settingsID);

    $invoices = $this->CashModel->invoiceStatusReportList($settingsID);
    $this->_attachInvoiceItemsToCollection($invoices, $settingsID);
    $report = $this->_buildInvoiceStatusReport($invoices);

    $result = array(
      'sections' => $report['sections'],
      'overallTotals' => $report['overallTotals'],
      'generatedAt' => date('F j, Y h:i A'),
    );

    $this->load->view('invoice_status_report', $result);
  }

  function recurringInvoices()
  {
    if ($this->session->userdata('level') !== 'Admin') {
      echo "Access Denied";
      return;
    }

    $settingsID = (int) $this->session->userdata('settingsID');
    $generationSummary = $this->_generateRecurringInvoices($settingsID);
    $this->_markRecurringInvoiceGeneratorRun($settingsID);

    $templates = $this->CashModel->recurringInvoiceTemplates($settingsID);
    $templateOrderIDs = array_values(array_unique(array_filter(array_map(function ($row) {
      return (int) ($row->orderID ?? 0);
    }, (array) $templates))));
    $generatedInvoices = $this->CashModel->recurringGeneratedInvoices($settingsID, $templateOrderIDs);
    $terminatedInvoices = $this->CashModel->recurringTerminatedInvoices($settingsID);
    $dashboard = $this->_buildRecurringInvoiceDashboard($templates, $generatedInvoices);

    $result = array(
      'templates' => $dashboard['templates'],
      'generatedInvoices' => $dashboard['generatedInvoices'],
      'terminatedInvoices' => $terminatedInvoices,
      'totals' => $dashboard['totals'],
      'generationSummary' => $generationSummary,
      'lastGeneratorRunAt' => date('F j, Y h:i A'),
    );

    $this->load->view('recurring_invoices', $result);
  }

  function runRecurringInvoiceGenerator()
  {
    if ($this->session->userdata('level') !== 'Admin') {
      echo "Access Denied";
      return;
    }

    $settingsID = (int) $this->session->userdata('settingsID');
    $summary = $this->_generateRecurringInvoices($settingsID);
    $this->_markRecurringInvoiceGeneratorRun($settingsID);

    $generatedCount = (int) ($summary['generatedCount'] ?? 0);
    if ($generatedCount > 0) {
      $this->session->set_flashdata('success', 'Recurring invoice generator created ' . number_format($generatedCount) . ' invoice' . ($generatedCount === 1 ? '' : 's') . '.');
    } else {
      $this->session->set_flashdata('success', 'Recurring invoice generator checked all schedules. No new invoices were needed.');
    }

    redirect('Page/recurringInvoices');
  }

  function generateNextRecurringInvoice()
  {
    if ($this->session->userdata('level') !== 'Admin') {
      $this->session->set_flashdata('danger', 'Access denied. Only admins can generate recurring invoices.');
      redirect('Page/recurringInvoices');
      return;
    }

    if (strtolower((string) $this->input->method()) !== 'post') {
      $this->session->set_flashdata('danger', 'Invalid request method.');
      redirect('Page/recurringInvoices');
      return;
    }

    $settingsID = (int) $this->session->userdata('settingsID');
    $templateID = (int) $this->input->post('id', true);
    if ($templateID <= 0) {
      $this->session->set_flashdata('danger', 'Invalid recurring invoice template.');
      redirect('Page/recurringInvoices');
      return;
    }

    $result = $this->_generateNextRecurringInvoiceForTemplate($settingsID, $templateID);
    if (($result['status'] ?? '') === 'generated') {
      $this->session->set_flashdata(
        'success',
        'Invoice #' . ($result['invoiceNo'] ?? '') . ' was generated for ' . date('F j, Y', strtotime((string) $result['scheduleDate'])) . '.'
      );
    } else {
      $this->session->set_flashdata('danger', (string) ($result['message'] ?? 'The next recurring invoice could not be generated.'));
    }

    redirect('Page/recurringInvoices');
  }

  function deleteRecurringInvoice()
  {
    if ($this->session->userdata('level') !== 'Admin') {
      $this->session->set_flashdata('danger', 'Access denied. Only admins can delete recurring invoices.');
      redirect('Page/recurringInvoices');
      return;
    }

    $settingsID = $this->session->userdata('settingsID');
    $id = (int) $this->input->get('id');
    $deleteChildren = (int) $this->input->get('delete_children') === 1;

    if (!$id) {
      $this->session->set_flashdata('danger', 'Invalid invoice ID.');
      redirect('Page/recurringInvoices');
      return;
    }

    $invoice = $this->CashModel->getInvoiceByOrderID($id, $settingsID);
    if (!$invoice) {
      $this->session->set_flashdata('danger', 'Invoice not found.');
      redirect('Page/recurringInvoices');
      return;
    }

    $isTemplate = empty($invoice->recurringTemplateID) || (int) $invoice->recurringTemplateID === 0;
    $invoiceNo = $invoice->InvoiceNo ?? 'Unknown';

    // If this is a template and delete_children is set, also void all generated children
    if ($isTemplate && $deleteChildren) {
      $this->db->where('recurringTemplateID', $id);
      $this->db->where('settingsID', $settingsID);
      $this->db->where('invoiceStat !=', 'Voided');
      $this->db->update('invoice', array(
        'invoiceStat' => 'Voided',
        'voidReason' => 'Parent recurring template deleted',
        'voidDate' => date('Y-m-d H:i:s'),
        'voidBy' => $this->session->userdata('name') ?: 'Admin',
        'Balance' => 0,
        'AmountPaid' => 0
      ));

      // Delete related invoice items for children
      if ($this->db->table_exists('invoice_items')) {
        $this->db
          ->where_in('orderID', "SELECT orderID FROM invoice WHERE recurringTemplateID = {$id} AND settingsID = {$settingsID}", false)
          ->delete('invoice_items');
      }
    }

    // Void the main invoice (template or generated)
    $this->db->where('orderID', $id);
    $this->db->where('settingsID', $settingsID);
    $this->db->update('invoice', array(
      'invoiceStat' => 'Voided',
      'voidReason' => 'Deleted from recurring invoices page',
      'voidDate' => date('Y-m-d H:i:s'),
      'voidBy' => $this->session->userdata('name') ?: 'Admin',
      'Balance' => 0,
      'AmountPaid' => 0
    ));

    // Delete related invoice items
    if ($this->db->table_exists('invoice_items')) {
      $this->db
        ->where('orderID', $id)
        ->where('settingsID', $settingsID)
        ->delete('invoice_items');
    }

    $this->session->set_flashdata('success', 'Invoice #' . $invoiceNo . ' has been deleted successfully.' . ($isTemplate && $deleteChildren ? ' All generated child invoices were also deleted.' : ''));
    redirect('Page/recurringInvoices');
  }

  private function _resolveInvoiceReportDueDate($invoice)
  {
    $dueDate = $this->_normalizeDateInput(is_object($invoice) ? ($invoice->recurringScheduleDate ?? '') : ($invoice['recurringScheduleDate'] ?? ''));
    if ($dueDate !== null) {
      return $dueDate;
    }

    $dueDate = $this->_normalizeDateInput(is_object($invoice) ? ($invoice->ReceiveDate ?? '') : ($invoice['ReceiveDate'] ?? ''));
    if ($dueDate !== null) {
      return $dueDate;
    }

    return $this->_normalizeDateInput(is_object($invoice) ? ($invoice->TransDate ?? '') : ($invoice['TransDate'] ?? ''));
  }

  private function _resolveRecurringSeriesEndDate($invoice)
  {
    $terminationDate = $this->_normalizeDateInput(is_object($invoice) ? ($invoice->recurringTerminationDate ?? '') : ($invoice['recurringTerminationDate'] ?? ''));
    $expirationDate = $this->_normalizeDateInput(is_object($invoice) ? ($invoice->invoiceExpirationDate ?? '') : ($invoice['invoiceExpirationDate'] ?? ''));

    if ($terminationDate !== null && $expirationDate !== null) {
      return strtotime($terminationDate) <= strtotime($expirationDate) ? $terminationDate : $expirationDate;
    }

    return $terminationDate ?: $expirationDate;
  }

  private function _buildInvoiceStatusReport($invoices)
  {
    $today = date('Y-m-d');
    $sections = array(
      'paid' => array(
        'key' => 'paid',
        'label' => 'Paid Invoices',
        'subtitle' => 'Invoices that are already fully settled.',
        'rows' => array(),
        'count' => 0,
        'totalDue' => 0.0,
        'amountPaid' => 0.0,
        'balance' => 0.0,
      ),
      'unpaid' => array(
        'key' => 'unpaid',
        'label' => 'Unpaid, Not Yet Due',
        'subtitle' => 'Open invoices that still have a balance but have not yet reached the due date.',
        'rows' => array(),
        'count' => 0,
        'totalDue' => 0.0,
        'amountPaid' => 0.0,
        'balance' => 0.0,
      ),
      'overdue' => array(
        'key' => 'overdue',
        'label' => 'Overdue Invoices',
        'subtitle' => 'Invoices with an unpaid balance whose due date has already passed.',
        'rows' => array(),
        'count' => 0,
        'totalDue' => 0.0,
        'amountPaid' => 0.0,
        'balance' => 0.0,
      ),
      'draft' => array(
        'key' => 'draft',
        'label' => 'Draft Invoices',
        'subtitle' => 'Invoices marked as draft and not yet finalized.',
        'rows' => array(),
        'count' => 0,
        'totalDue' => 0.0,
        'amountPaid' => 0.0,
        'balance' => 0.0,
      ),
    );
    $overallTotals = array(
      'invoiceCount' => 0,
      'paidCount' => 0,
      'unpaidCount' => 0,
      'overdueCount' => 0,
      'draftCount' => 0,
      'paidAmount' => 0.0,
      'openAmount' => 0.0,
      'overdueAmount' => 0.0,
      'draftAmount' => 0.0,
    );

    foreach ((array) $invoices as $invoice) {
      $invoiceStatRaw = trim((string) ($invoice->invoiceStat ?? ''));
      $invoiceStat = strtolower($invoiceStatRaw);
      $totalDue = round((float) ($invoice->TotalDue ?? 0), 2);
      $amountPaid = round((float) ($invoice->AmountPaid ?? 0), 2);
      $balance = max(0, round((float) ($invoice->Balance ?? 0), 2));
      $dueDate = $this->_resolveInvoiceReportDueDate($invoice);
      $daysUntilDue = $dueDate !== null
        ? (int) floor((strtotime($dueDate) - strtotime($today)) / 86400)
        : null;
      $paymentStateLabel = $balance <= 0.00001 ? 'Paid' : ($amountPaid > 0 ? 'Partially Paid' : 'Unpaid');
      $description = trim((string) ($invoice->invoiceSummary ?? $invoice->JobDescription ?? ''));
      if ($description === '') {
        $description = 'Invoice item';
      }

      if ($invoiceStat === 'draft') {
        $sectionKey = 'draft';
        $categoryLabel = 'Draft';
        $timingLabel = 'Draft invoice';
      } elseif ($balance <= 0.00001) {
        $sectionKey = 'paid';
        $categoryLabel = 'Paid';
        $timingLabel = $dueDate !== null
          ? ('Closed invoice · due ' . date('M j, Y', strtotime($dueDate)))
          : 'Closed invoice';
      } elseif ($dueDate !== null && strtotime($dueDate) < strtotime($today)) {
        $sectionKey = 'overdue';
        $categoryLabel = 'Overdue';
        $timingLabel = abs((int) $daysUntilDue) . ' day(s) overdue';
      } else {
        $sectionKey = 'unpaid';
        $categoryLabel = 'Open';
        if ($daysUntilDue === null) {
          $timingLabel = 'Due date not set';
        } elseif ($daysUntilDue === 0) {
          $timingLabel = 'Due today';
        } else {
          $timingLabel = $daysUntilDue . ' day(s) until due';
        }
      }

      $row = array(
        'orderID' => (int) ($invoice->orderID ?? 0),
        'InvoiceNo' => (string) ($invoice->InvoiceNo ?? ''),
        'Customer' => (string) ($invoice->Customer ?? ''),
        'CustID' => (string) ($invoice->CustID ?? ''),
        'description' => $description,
        'invoiceDate' => $this->_normalizeDateInput($invoice->TransDate ?? ''),
        'dueDate' => $dueDate,
        'totalDue' => $totalDue,
        'amountPaid' => $amountPaid,
        'balance' => $balance,
        'invoiceBy' => (string) ($invoice->invoiceBy ?? ''),
        'invoiceStat' => $invoiceStatRaw !== '' ? $invoiceStatRaw : 'active',
        'paymentStateLabel' => $paymentStateLabel,
        'categoryLabel' => $categoryLabel,
        'timingLabel' => $timingLabel,
        'isRecurring' => $this->_normalizeRecurringFrequency($invoice->recurringFrequency ?? 'none') !== 'none',
        'recurringFrequencyLabel' => $this->_formatRecurringFrequencyLabel($this->_normalizeRecurringFrequency($invoice->recurringFrequency ?? 'none')),
      );

      $sections[$sectionKey]['rows'][] = $row;
      $sections[$sectionKey]['count']++;
      $sections[$sectionKey]['totalDue'] += $totalDue;
      $sections[$sectionKey]['amountPaid'] += $amountPaid;
      $sections[$sectionKey]['balance'] += $balance;

      $overallTotals['invoiceCount']++;
      if ($sectionKey === 'paid') {
        $overallTotals['paidCount']++;
        $overallTotals['paidAmount'] += $totalDue;
      } elseif ($sectionKey === 'unpaid') {
        $overallTotals['unpaidCount']++;
        $overallTotals['openAmount'] += $balance;
      } elseif ($sectionKey === 'overdue') {
        $overallTotals['overdueCount']++;
        $overallTotals['overdueAmount'] += $balance;
      } elseif ($sectionKey === 'draft') {
        $overallTotals['draftCount']++;
        $overallTotals['draftAmount'] += $totalDue;
      }
    }

    usort($sections['paid']['rows'], function ($left, $right) {
      $leftDate = strtotime((string) ($left['invoiceDate'] ?? '1970-01-01'));
      $rightDate = strtotime((string) ($right['invoiceDate'] ?? '1970-01-01'));
      if ($leftDate !== $rightDate) {
        return $rightDate <=> $leftDate;
      }
      return strcasecmp((string) ($left['Customer'] ?? ''), (string) ($right['Customer'] ?? ''));
    });

    usort($sections['unpaid']['rows'], function ($left, $right) {
      $leftDate = strtotime((string) ($left['dueDate'] ?? '9999-12-31'));
      $rightDate = strtotime((string) ($right['dueDate'] ?? '9999-12-31'));
      if ($leftDate !== $rightDate) {
        return $leftDate <=> $rightDate;
      }
      return strcasecmp((string) ($left['Customer'] ?? ''), (string) ($right['Customer'] ?? ''));
    });

    usort($sections['overdue']['rows'], function ($left, $right) {
      $leftDate = strtotime((string) ($left['dueDate'] ?? '9999-12-31'));
      $rightDate = strtotime((string) ($right['dueDate'] ?? '9999-12-31'));
      if ($leftDate !== $rightDate) {
        return $leftDate <=> $rightDate;
      }
      return strcasecmp((string) ($left['Customer'] ?? ''), (string) ($right['Customer'] ?? ''));
    });

    usort($sections['draft']['rows'], function ($left, $right) {
      $leftDate = strtotime((string) ($left['invoiceDate'] ?? '1970-01-01'));
      $rightDate = strtotime((string) ($right['invoiceDate'] ?? '1970-01-01'));
      if ($leftDate !== $rightDate) {
        return $rightDate <=> $leftDate;
      }
      return strcasecmp((string) ($left['Customer'] ?? ''), (string) ($right['Customer'] ?? ''));
    });

    foreach ($sections as $key => $section) {
      $sections[$key]['totalDue'] = round((float) $section['totalDue'], 2);
      $sections[$key]['amountPaid'] = round((float) $section['amountPaid'], 2);
      $sections[$key]['balance'] = round((float) $section['balance'], 2);
    }

    $overallTotals['paidAmount'] = round((float) $overallTotals['paidAmount'], 2);
    $overallTotals['openAmount'] = round((float) $overallTotals['openAmount'], 2);
    $overallTotals['overdueAmount'] = round((float) $overallTotals['overdueAmount'], 2);
    $overallTotals['draftAmount'] = round((float) $overallTotals['draftAmount'], 2);

    return array(
      'sections' => $sections,
      'overallTotals' => $overallTotals,
    );
  }

  private function _buildRecurringRevenueReport($templates, $dateFrom, $dateTo)
  {
    $monthMap = $this->_initializeMonthSummaryMap($dateFrom, $dateTo, array(
      'occurrenceCount' => 0,
      'amount' => 0.0,
    ));
    $frequencySummaries = array();
    $templateRows = array();
    $totals = array(
      'templateCount' => 0,
      'projectedOccurrenceCount' => 0,
      'projectedAmount' => 0.0,
    );
    $today = date('Y-m-d');

    foreach ((array) $templates as $template) {
      $frequency = $this->_normalizeRecurringFrequency($template->recurringFrequency ?? 'none');
      if ($frequency === 'none') {
        continue;
      }

      $baseScheduleDate = $this->_normalizeDateInput($template->recurringScheduleDate ?? '')
        ?: $this->_normalizeDateInput($template->TransDate ?? '');
      if ($baseScheduleDate === null) {
        continue;
      }

      $seriesEndDate = $this->_resolveRecurringSeriesEndDate($template);
      $rangeOccurrences = $this->_getRecurringOccurrencesWithinRange($baseScheduleDate, $frequency, $dateFrom, $dateTo, $seriesEndDate);
      $projectedOccurrenceCount = count($rangeOccurrences);
      $totalDue = round((float) ($template->TotalDue ?? 0), 2);
      $projectedAmount = round($projectedOccurrenceCount * $totalDue, 2);
      $frequencyLabel = $this->_formatRecurringFrequencyLabel($frequency);

      foreach ($rangeOccurrences as $occurrenceDate) {
        $periodKey = date('Y-m', strtotime($occurrenceDate));
        if (!isset($monthMap[$periodKey])) {
          $monthMap[$periodKey] = array(
            'periodKey' => $periodKey,
            'periodLabel' => date('F Y', strtotime($periodKey . '-01')),
            'occurrenceCount' => 0,
            'amount' => 0.0,
          );
        }

        $monthMap[$periodKey]['occurrenceCount']++;
        $monthMap[$periodKey]['amount'] += $totalDue;
      }

      if (!isset($frequencySummaries[$frequency])) {
        $frequencySummaries[$frequency] = array(
          'frequency' => $frequency,
          'label' => $frequencyLabel,
          'templateCount' => 0,
          'projectedOccurrenceCount' => 0,
          'projectedAmount' => 0.0,
        );
      }

      $frequencySummaries[$frequency]['templateCount']++;
      $frequencySummaries[$frequency]['projectedOccurrenceCount'] += $projectedOccurrenceCount;
      $frequencySummaries[$frequency]['projectedAmount'] += $projectedAmount;

      $templateRows[] = array(
        'orderID' => (int) ($template->orderID ?? 0),
        'InvoiceNo' => (string) ($template->InvoiceNo ?? ''),
        'Customer' => (string) ($template->Customer ?? ''),
        'CustID' => (string) ($template->CustID ?? ''),
        'JobDescription' => (string) ($template->JobDescription ?? ''),
        'frequency' => $frequency,
        'frequencyLabel' => $frequencyLabel,
        'scheduleDate' => $baseScheduleDate,
        'nextBillingDate' => $this->_findNextRecurringOccurrence($baseScheduleDate, $frequency, $today, $seriesEndDate),
        'lastGeneratedFor' => $this->_normalizeDateInput($template->lastRecurringGeneratedFor ?? ''),
        'terminationDate' => $this->_normalizeDateInput($template->recurringTerminationDate ?? ''),
        'expirationDate' => $this->_normalizeDateInput($template->invoiceExpirationDate ?? ''),
        'invoiceSource' => (string) ($template->invoiceSource ?? ''),
        'invoiceBy' => (string) ($template->invoiceBy ?? ''),
        'totalDue' => $totalDue,
        'balance' => round((float) ($template->Balance ?? 0), 2),
        'projectedOccurrenceCount' => $projectedOccurrenceCount,
        'projectedAmount' => $projectedAmount,
      );

      $totals['templateCount']++;
      $totals['projectedOccurrenceCount'] += $projectedOccurrenceCount;
      $totals['projectedAmount'] += $projectedAmount;
    }

    foreach ($monthMap as $periodKey => $summary) {
      $monthMap[$periodKey]['amount'] = round((float) ($summary['amount'] ?? 0), 2);
    }

    foreach ($frequencySummaries as $frequency => $summary) {
      $frequencySummaries[$frequency]['projectedAmount'] = round((float) ($summary['projectedAmount'] ?? 0), 2);
    }

    usort($templateRows, function ($left, $right) {
      $leftSort = $this->_recurringFrequencySortValue($left['frequency'] ?? '');
      $rightSort = $this->_recurringFrequencySortValue($right['frequency'] ?? '');
      if ($leftSort !== $rightSort) {
        return $leftSort <=> $rightSort;
      }

      $leftDate = strtotime((string) ($left['scheduleDate'] ?? '1970-01-01'));
      $rightDate = strtotime((string) ($right['scheduleDate'] ?? '1970-01-01'));
      if ($leftDate !== $rightDate) {
        return $leftDate <=> $rightDate;
      }

      return strcasecmp((string) ($left['Customer'] ?? ''), (string) ($right['Customer'] ?? ''));
    });

    uasort($frequencySummaries, function ($left, $right) {
      $leftSort = $this->_recurringFrequencySortValue($left['frequency'] ?? '');
      $rightSort = $this->_recurringFrequencySortValue($right['frequency'] ?? '');
      return $leftSort <=> $rightSort;
    });

    $totals['projectedAmount'] = round((float) $totals['projectedAmount'], 2);

    return array(
      'templates' => $templateRows,
      'frequencySummaries' => array_values($frequencySummaries),
      'monthlyProjection' => array_values($monthMap),
      'totals' => $totals,
    );
  }

  private function _buildRecurringInvoiceDashboard($templates, $generatedInvoices)
  {
    $today = date('Y-m-d');
    $soonThresholdDate = date('Y-m-d', strtotime($today . ' +10 days'));
    $templatesById = array();
    $generatedByTemplate = array();
    $generatedByTemplateAndSchedule = array();
    $templateRows = array();
    $generatedRows = array();
    $totals = array(
      'templateCount' => 0,
      'dueSoonCount' => 0,
      'readyCount' => 0,
      'needsGenerationCount' => 0,
      'generatedInvoiceCount' => 0,
    );

    foreach ((array) $templates as $template) {
      $templateId = (int) ($template->orderID ?? 0);
      if ($templateId > 0) {
        $templatesById[$templateId] = $template;
      }
    }

    foreach ((array) $generatedInvoices as $generatedInvoice) {
      $templateId = (int) ($generatedInvoice->recurringTemplateID ?? 0);
      if ($templateId <= 0) {
        continue;
      }

      if (!isset($generatedByTemplate[$templateId])) {
        $generatedByTemplate[$templateId] = array();
      }

      $generatedByTemplate[$templateId][] = $generatedInvoice;
      $scheduleDate = $this->_normalizeDateInput($generatedInvoice->recurringScheduleDate ?? '')
        ?: $this->_normalizeDateInput($generatedInvoice->TransDate ?? '');
      if ($scheduleDate !== null) {
        $generatedByTemplateAndSchedule[$templateId . ':' . $scheduleDate] = $generatedInvoice;
      }
    }

    foreach ((array) $templates as $template) {
      $frequency = $this->_normalizeRecurringFrequency($template->recurringFrequency ?? 'none');
      if ($frequency === 'none') {
        continue;
      }

      $templateId = (int) ($template->orderID ?? 0);
      $baseScheduleDate = $this->_normalizeDateInput($template->recurringScheduleDate ?? '')
        ?: $this->_normalizeDateInput($template->TransDate ?? '');
      if ($baseScheduleDate === null) {
        continue;
      }

      $seriesEndDate = $this->_resolveRecurringSeriesEndDate($template);
      $upcomingDueDate = $this->_findNextRecurringOccurrence($baseScheduleDate, $frequency, $today, $seriesEndDate);
      $windowOpensOn = $upcomingDueDate !== null
        ? date('Y-m-d', strtotime($upcomingDueDate . ' -10 days'))
        : null;
      $isDueSoon = $upcomingDueDate !== null && strtotime($upcomingDueDate) <= strtotime($soonThresholdDate);
      $preparedInvoice = null;
      $preparedInvoiceSource = '';

      if ($upcomingDueDate !== null) {
        if ($upcomingDueDate === $baseScheduleDate) {
          $preparedInvoice = $template;
          $preparedInvoiceSource = 'template';
        } else {
          $preparedInvoice = $generatedByTemplateAndSchedule[$templateId . ':' . $upcomingDueDate] ?? null;
          $preparedInvoiceSource = $preparedInvoice ? 'generated' : '';
        }
      }

      if ($preparedInvoice) {
        $statusKey = $preparedInvoiceSource === 'template' ? 'base' : 'ready';
        $statusLabel = $preparedInvoiceSource === 'template' ? 'Template Covers First Due Date' : 'Prepared';
      } elseif ($isDueSoon) {
        $statusKey = 'attention';
        $statusLabel = 'Needs Generation';
      } else {
        $statusKey = 'scheduled';
        $statusLabel = 'Scheduled';
      }

      $daysUntilDue = $upcomingDueDate !== null
        ? max(0, (int) floor((strtotime($upcomingDueDate) - strtotime($today)) / 86400))
        : null;
      $latestGeneratedInvoice = !empty($generatedByTemplate[$templateId]) ? $generatedByTemplate[$templateId][0] : null;
      $frequencyLabel = $this->_formatRecurringFrequencyLabel($frequency);
      $lastGeneratedFor = $this->_normalizeDateInput($template->lastRecurringGeneratedFor ?? '');
      if ($lastGeneratedFor === null || strtotime($lastGeneratedFor) < strtotime($baseScheduleDate)) {
        $lastGeneratedFor = $baseScheduleDate;
      }
      $nextGenerationDate = $this->_advanceRecurringDate($lastGeneratedFor, $frequency);
      if ($seriesEndDate !== null && $nextGenerationDate !== null && strtotime($nextGenerationDate) > strtotime($seriesEndDate)) {
        $nextGenerationDate = null;
      }

      $templateRows[] = array(
        'orderID' => $templateId,
        'InvoiceNo' => (string) ($template->InvoiceNo ?? ''),
        'Customer' => (string) ($template->Customer ?? ''),
        'CustID' => (string) ($template->CustID ?? ''),
        'JobDescription' => (string) ($template->JobDescription ?? ''),
        'frequency' => $frequency,
        'frequencyLabel' => $frequencyLabel,
        'scheduleDate' => $baseScheduleDate,
        'upcomingDueDate' => $upcomingDueDate,
        'windowOpensOn' => $windowOpensOn,
        'terminationDate' => $this->_normalizeDateInput($template->recurringTerminationDate ?? ''),
        'expirationDate' => $this->_normalizeDateInput($template->invoiceExpirationDate ?? ''),
        'daysUntilDue' => $daysUntilDue,
        'isDueSoon' => $isDueSoon,
        'statusKey' => $statusKey,
        'statusLabel' => $statusLabel,
        'totalDue' => round((float) ($template->TotalDue ?? 0), 2),
        'balance' => round((float) ($template->Balance ?? 0), 2),
        'invoiceBy' => (string) ($template->invoiceBy ?? ''),
        'lastGeneratedFor' => $this->_normalizeDateInput($template->lastRecurringGeneratedFor ?? ''),
        'nextGenerationDate' => $nextGenerationDate,
        'canGenerateNext' => $nextGenerationDate !== null,
        'generatedChildCount' => count($generatedByTemplate[$templateId] ?? array()),
        'latestGeneratedInvoiceNo' => $latestGeneratedInvoice ? (string) ($latestGeneratedInvoice->InvoiceNo ?? '') : '',
        'latestGeneratedScheduleDate' => $latestGeneratedInvoice
          ? ($this->_normalizeDateInput($latestGeneratedInvoice->recurringScheduleDate ?? '') ?: $this->_normalizeDateInput($latestGeneratedInvoice->TransDate ?? ''))
          : null,
        'preparedInvoiceOrderID' => $preparedInvoice ? (int) ($preparedInvoice->orderID ?? 0) : 0,
        'preparedInvoiceNo' => $preparedInvoice ? (string) ($preparedInvoice->InvoiceNo ?? '') : '',
        'preparedInvoiceSource' => $preparedInvoiceSource,
      );

      $totals['templateCount']++;
      if ($isDueSoon) {
        $totals['dueSoonCount']++;
      }
      if ($preparedInvoice) {
        $totals['readyCount']++;
      }
      if ($isDueSoon && !$preparedInvoice) {
        $totals['needsGenerationCount']++;
      }
    }

    foreach ((array) $generatedInvoices as $generatedInvoice) {
      $templateId = (int) ($generatedInvoice->recurringTemplateID ?? 0);
      $template = $templatesById[$templateId] ?? null;
      $frequency = $this->_normalizeRecurringFrequency($template->recurringFrequency ?? ($generatedInvoice->recurringFrequency ?? 'none'));
      $generatedRows[] = array(
        'orderID' => (int) ($generatedInvoice->orderID ?? 0),
        'InvoiceNo' => (string) ($generatedInvoice->InvoiceNo ?? ''),
        'Customer' => (string) ($generatedInvoice->Customer ?? ''),
        'CustID' => (string) ($generatedInvoice->CustID ?? ''),
        'JobDescription' => (string) ($generatedInvoice->JobDescription ?? ''),
        'frequencyLabel' => $this->_formatRecurringFrequencyLabel($frequency),
        'scheduleDate' => $this->_normalizeDateInput($generatedInvoice->recurringScheduleDate ?? '')
          ?: $this->_normalizeDateInput($generatedInvoice->TransDate ?? ''),
        'templateOrderID' => $templateId,
        'templateInvoiceNo' => $template ? (string) ($template->InvoiceNo ?? '') : '',
        'templateDescription' => $template ? (string) ($template->JobDescription ?? '') : '',
        'totalDue' => round((float) ($generatedInvoice->TotalDue ?? 0), 2),
        'balance' => round((float) ($generatedInvoice->Balance ?? 0), 2),
      );
    }

    usort($templateRows, function ($left, $right) {
      $leftDueSoon = !empty($left['isDueSoon']) ? 0 : 1;
      $rightDueSoon = !empty($right['isDueSoon']) ? 0 : 1;
      if ($leftDueSoon !== $rightDueSoon) {
        return $leftDueSoon <=> $rightDueSoon;
      }

      $leftDate = strtotime((string) ($left['upcomingDueDate'] ?? '9999-12-31'));
      $rightDate = strtotime((string) ($right['upcomingDueDate'] ?? '9999-12-31'));
      if ($leftDate !== $rightDate) {
        return $leftDate <=> $rightDate;
      }

      $leftSort = $this->_recurringFrequencySortValue($left['frequency'] ?? '');
      $rightSort = $this->_recurringFrequencySortValue($right['frequency'] ?? '');
      if ($leftSort !== $rightSort) {
        return $leftSort <=> $rightSort;
      }

      return strcasecmp((string) ($left['Customer'] ?? ''), (string) ($right['Customer'] ?? ''));
    });

    usort($generatedRows, function ($left, $right) {
      $leftDate = strtotime((string) ($left['scheduleDate'] ?? '1970-01-01'));
      $rightDate = strtotime((string) ($right['scheduleDate'] ?? '1970-01-01'));
      if ($leftDate !== $rightDate) {
        return $rightDate <=> $leftDate;
      }

      return ((int) ($right['orderID'] ?? 0)) <=> ((int) ($left['orderID'] ?? 0));
    });

    $totals['generatedInvoiceCount'] = count($generatedRows);

    return array(
      'templates' => $templateRows,
      'generatedInvoices' => $generatedRows,
      'totals' => $totals,
    );
  }

  private function _buildAcceptedCollectionsReport($entries, $dateFrom, $dateTo)
  {
    $monthMap = $this->_initializeMonthSummaryMap($dateFrom, $dateTo, array(
      'entryCount' => 0,
      'invoiceCount' => 0,
      'cashAmount' => 0.0,
      'taxAmount' => 0.0,
      'grossAmount' => 0.0,
      'invoiceKeys' => array(),
    ));
    $yearMap = $this->_initializeYearSummaryMap($dateFrom, $dateTo, array(
      'entryCount' => 0,
      'invoiceCount' => 0,
      'cashAmount' => 0.0,
      'taxAmount' => 0.0,
      'grossAmount' => 0.0,
      'invoiceKeys' => array(),
    ));
    $sourceSummaries = array();
    $cashierSummaries = array();
    $uniqueInvoices = array();
    $uniqueClients = array();
    $totals = array(
      'entryCount' => 0,
      'invoiceCount' => 0,
      'clientCount' => 0,
      'cashAmount' => 0.0,
      'taxAmount' => 0.0,
      'grossAmount' => 0.0,
    );

    foreach ((array) $entries as $row) {
      $paymentDate = $this->_normalizeDateInput($row->PDate ?? '');
      $cashAmount = round((float) ($row->AmountPaid ?? 0), 2);
      $taxAmount = round((float) ($row->TaxAmount ?? 0), 2);
      $grossAmount = round((float) ($row->GrossAmountPaid ?? ($cashAmount + $taxAmount)), 2);
      $invoiceNo = trim((string) ($row->InvoiceNo ?? ''));
      $invoiceKey = $invoiceNo !== '' ? 'invoice:' . $invoiceNo : 'payment:' . (string) ($row->paymentID ?? '');
      $custID = trim((string) ($row->CustID ?? ''));
      $customerName = trim((string) ($row->Customer ?? ''));
      $clientKey = $custID !== '' ? 'cust:' . $custID : 'name:' . strtolower($customerName !== '' ? $customerName : ('payment-' . (string) ($row->paymentID ?? '0')));
      $sourceLabel = trim((string) ($row->PaymentSource ?? ''));
      $cashierLabel = trim((string) ($row->Cashier ?? ''));
      $sourceLabel = $sourceLabel !== '' ? $sourceLabel : 'Unspecified';
      $cashierLabel = $cashierLabel !== '' ? $cashierLabel : 'Unassigned';

      $totals['entryCount']++;
      $totals['cashAmount'] += $cashAmount;
      $totals['taxAmount'] += $taxAmount;
      $totals['grossAmount'] += $grossAmount;
      $uniqueInvoices[$invoiceKey] = true;
      $uniqueClients[$clientKey] = true;

      if ($paymentDate !== null) {
        $monthKey = date('Y-m', strtotime($paymentDate));
        if (isset($monthMap[$monthKey])) {
          $monthMap[$monthKey]['entryCount']++;
          $monthMap[$monthKey]['cashAmount'] += $cashAmount;
          $monthMap[$monthKey]['taxAmount'] += $taxAmount;
          $monthMap[$monthKey]['grossAmount'] += $grossAmount;
          $monthMap[$monthKey]['invoiceKeys'][$invoiceKey] = true;
        }

        $yearKey = date('Y', strtotime($paymentDate));
        if (isset($yearMap[$yearKey])) {
          $yearMap[$yearKey]['entryCount']++;
          $yearMap[$yearKey]['cashAmount'] += $cashAmount;
          $yearMap[$yearKey]['taxAmount'] += $taxAmount;
          $yearMap[$yearKey]['grossAmount'] += $grossAmount;
          $yearMap[$yearKey]['invoiceKeys'][$invoiceKey] = true;
        }
      }

      if (!isset($sourceSummaries[$sourceLabel])) {
        $sourceSummaries[$sourceLabel] = array(
          'label' => $sourceLabel,
          'entryCount' => 0,
          'cashAmount' => 0.0,
          'taxAmount' => 0.0,
          'grossAmount' => 0.0,
        );
      }

      $sourceSummaries[$sourceLabel]['entryCount']++;
      $sourceSummaries[$sourceLabel]['cashAmount'] += $cashAmount;
      $sourceSummaries[$sourceLabel]['taxAmount'] += $taxAmount;
      $sourceSummaries[$sourceLabel]['grossAmount'] += $grossAmount;

      if (!isset($cashierSummaries[$cashierLabel])) {
        $cashierSummaries[$cashierLabel] = array(
          'label' => $cashierLabel,
          'entryCount' => 0,
          'cashAmount' => 0.0,
          'taxAmount' => 0.0,
          'grossAmount' => 0.0,
        );
      }

      $cashierSummaries[$cashierLabel]['entryCount']++;
      $cashierSummaries[$cashierLabel]['cashAmount'] += $cashAmount;
      $cashierSummaries[$cashierLabel]['taxAmount'] += $taxAmount;
      $cashierSummaries[$cashierLabel]['grossAmount'] += $grossAmount;
    }

    foreach ($monthMap as $periodKey => $summary) {
      $monthMap[$periodKey]['invoiceCount'] = count($summary['invoiceKeys']);
      unset($monthMap[$periodKey]['invoiceKeys']);
      $monthMap[$periodKey]['cashAmount'] = round((float) ($summary['cashAmount'] ?? 0), 2);
      $monthMap[$periodKey]['taxAmount'] = round((float) ($summary['taxAmount'] ?? 0), 2);
      $monthMap[$periodKey]['grossAmount'] = round((float) ($summary['grossAmount'] ?? 0), 2);
    }

    foreach ($yearMap as $yearKey => $summary) {
      $yearMap[$yearKey]['invoiceCount'] = count($summary['invoiceKeys']);
      unset($yearMap[$yearKey]['invoiceKeys']);
      $yearMap[$yearKey]['cashAmount'] = round((float) ($summary['cashAmount'] ?? 0), 2);
      $yearMap[$yearKey]['taxAmount'] = round((float) ($summary['taxAmount'] ?? 0), 2);
      $yearMap[$yearKey]['grossAmount'] = round((float) ($summary['grossAmount'] ?? 0), 2);
    }

    foreach ($sourceSummaries as $key => $summary) {
      $sourceSummaries[$key]['cashAmount'] = round((float) ($summary['cashAmount'] ?? 0), 2);
      $sourceSummaries[$key]['taxAmount'] = round((float) ($summary['taxAmount'] ?? 0), 2);
      $sourceSummaries[$key]['grossAmount'] = round((float) ($summary['grossAmount'] ?? 0), 2);
    }

    foreach ($cashierSummaries as $key => $summary) {
      $cashierSummaries[$key]['cashAmount'] = round((float) ($summary['cashAmount'] ?? 0), 2);
      $cashierSummaries[$key]['taxAmount'] = round((float) ($summary['taxAmount'] ?? 0), 2);
      $cashierSummaries[$key]['grossAmount'] = round((float) ($summary['grossAmount'] ?? 0), 2);
    }

    usort($entries, function ($left, $right) {
      $leftDate = strtotime((string) ($left->PDate ?? '1970-01-01'));
      $rightDate = strtotime((string) ($right->PDate ?? '1970-01-01'));
      if ($leftDate === $rightDate) {
        return ((int) ($right->paymentID ?? 0)) <=> ((int) ($left->paymentID ?? 0));
      }
      return $rightDate <=> $leftDate;
    });

    uasort($sourceSummaries, function ($left, $right) {
      if ((float) ($left['grossAmount'] ?? 0) === (float) ($right['grossAmount'] ?? 0)) {
        return strcasecmp((string) ($left['label'] ?? ''), (string) ($right['label'] ?? ''));
      }
      return ((float) ($right['grossAmount'] ?? 0) <=> (float) ($left['grossAmount'] ?? 0));
    });

    uasort($cashierSummaries, function ($left, $right) {
      if ((float) ($left['grossAmount'] ?? 0) === (float) ($right['grossAmount'] ?? 0)) {
        return strcasecmp((string) ($left['label'] ?? ''), (string) ($right['label'] ?? ''));
      }
      return ((float) ($right['grossAmount'] ?? 0) <=> (float) ($left['grossAmount'] ?? 0));
    });

    $totals['invoiceCount'] = count($uniqueInvoices);
    $totals['clientCount'] = count($uniqueClients);
    $totals['cashAmount'] = round((float) $totals['cashAmount'], 2);
    $totals['taxAmount'] = round((float) $totals['taxAmount'], 2);
    $totals['grossAmount'] = round((float) $totals['grossAmount'], 2);

    return array(
      'entries' => $entries,
      'totals' => $totals,
      'monthlySummaries' => array_values($monthMap),
      'yearlySummaries' => array_values($yearMap),
      'sourceSummaries' => array_values($sourceSummaries),
      'cashierSummaries' => array_values($cashierSummaries),
    );
  }

  private function _buildProjectedActualComparison($projectedMonthly, $actualMonthly)
  {
    $projectedMap = array();
    foreach ((array) $projectedMonthly as $row) {
      $projectedMap[(string) ($row['periodKey'] ?? '')] = $row;
    }

    $actualMap = array();
    foreach ((array) $actualMonthly as $row) {
      $actualMap[(string) ($row['periodKey'] ?? '')] = $row;
    }

    $keys = array_values(array_unique(array_filter(array_merge(array_keys($projectedMap), array_keys($actualMap)))));
    sort($keys);

    $comparison = array();
    foreach ($keys as $periodKey) {
      $projected = $projectedMap[$periodKey] ?? array();
      $actual = $actualMap[$periodKey] ?? array();
      $projectedAmount = round((float) ($projected['amount'] ?? 0), 2);
      $actualGrossAmount = round((float) ($actual['grossAmount'] ?? 0), 2);
      $comparison[] = array(
        'periodKey' => $periodKey,
        'periodLabel' => (string) ($projected['periodLabel'] ?? $actual['periodLabel'] ?? date('F Y', strtotime($periodKey . '-01'))),
        'projectedOccurrenceCount' => (int) ($projected['occurrenceCount'] ?? 0),
        'projectedAmount' => $projectedAmount,
        'actualEntryCount' => (int) ($actual['entryCount'] ?? 0),
        'actualGrossAmount' => $actualGrossAmount,
        'varianceAmount' => round($actualGrossAmount - $projectedAmount, 2),
      );
    }

    return $comparison;
  }

  private function _initializeMonthSummaryMap($dateFrom, $dateTo, $defaults = array())
  {
    $map = array();
    $cursor = date('Y-m-01', strtotime($dateFrom));
    $end = date('Y-m-01', strtotime($dateTo));
    $guard = 0;

    while ($cursor !== false && strtotime($cursor) <= strtotime($end) && $guard < 240) {
      $periodKey = date('Y-m', strtotime($cursor));
      $map[$periodKey] = array_merge(array(
        'periodKey' => $periodKey,
        'periodLabel' => date('F Y', strtotime($periodKey . '-01')),
      ), $defaults);

      $cursor = date('Y-m-01', strtotime('+1 month', strtotime($cursor)));
      $guard++;
    }

    return $map;
  }

  private function _initializeYearSummaryMap($dateFrom, $dateTo, $defaults = array())
  {
    $map = array();
    $startYear = (int) date('Y', strtotime($dateFrom));
    $endYear = (int) date('Y', strtotime($dateTo));
    if ($endYear < $startYear) {
      $swap = $startYear;
      $startYear = $endYear;
      $endYear = $swap;
    }

    for ($year = $startYear; $year <= $endYear; $year++) {
      $map[(string) $year] = array_merge(array(
        'periodKey' => (string) $year,
        'periodLabel' => (string) $year,
      ), $defaults);
    }

    return $map;
  }

  private function _getRecurringOccurrencesWithinRange($baseDate, $frequency, $dateFrom, $dateTo, $endDate = null)
  {
    $baseDate = $this->_normalizeDateInput($baseDate);
    $dateFrom = $this->_normalizeDateInput($dateFrom);
    $dateTo = $this->_normalizeDateInput($dateTo);
    $endDate = $this->_normalizeDateInput($endDate);
    if ($baseDate === null || $dateFrom === null || $dateTo === null) {
      return array();
    }

    $occurrences = array();
    $currentDate = $baseDate;
    $guard = 0;

    while (
      $currentDate !== null
      && strtotime($currentDate) <= strtotime($dateTo)
      && ($endDate === null || strtotime($currentDate) <= strtotime($endDate))
      && $guard < 4000
    ) {
      if (strtotime($currentDate) >= strtotime($dateFrom)) {
        $occurrences[] = $currentDate;
      }

      $currentDate = $this->_advanceRecurringDate($currentDate, $frequency);
      $guard++;
    }

    return $occurrences;
  }

  private function _findNextRecurringOccurrence($baseDate, $frequency, $referenceDate, $endDate = null)
  {
    $currentDate = $this->_normalizeDateInput($baseDate);
    $referenceDate = $this->_normalizeDateInput($referenceDate);
    $endDate = $this->_normalizeDateInput($endDate);
    if ($currentDate === null || $referenceDate === null) {
      return null;
    }

    $guard = 0;
    while (
      $currentDate !== null
      && strtotime($currentDate) < strtotime($referenceDate)
      && ($endDate === null || strtotime($currentDate) <= strtotime($endDate))
      && $guard < 4000
    ) {
      $currentDate = $this->_advanceRecurringDate($currentDate, $frequency);
      $guard++;
    }

    if ($currentDate !== null && $endDate !== null && strtotime($currentDate) > strtotime($endDate)) {
      return null;
    }

    return $currentDate;
  }

  private function _recurringFrequencySortValue($frequency)
  {
    static $sortOrder = array(
      'daily' => 1,
      'weekly' => 2,
      'monthly' => 3,
      'quarterly' => 4,
      'yearly' => 5,
    );

    return isset($sortOrder[$frequency]) ? $sortOrder[$frequency] : 99;
  }

  private function _formatRecurringFrequencyLabel($frequency)
  {
    if ($frequency === 'daily') {
      return 'Daily';
    }
    if ($frequency === 'weekly') {
      return 'Weekly';
    }
    if ($frequency === 'monthly') {
      return 'Monthly';
    }
    if ($frequency === 'quarterly') {
      return 'Quarterly';
    }
    if ($frequency === 'yearly') {
      return 'Yearly';
    }

    return 'Not recurring';
  }

  private function _formatReportDateRangeLabel($dateFrom, $dateTo)
  {
    $dateFrom = $this->_normalizeDateInput($dateFrom);
    $dateTo = $this->_normalizeDateInput($dateTo);
    if ($dateFrom === null && $dateTo === null) {
      return 'All recorded dates';
    }

    if ($dateFrom !== null && $dateTo !== null) {
      $formattedFrom = date('F j, Y', strtotime($dateFrom));
      $formattedTo = date('F j, Y', strtotime($dateTo));
      return $dateFrom === $dateTo ? $formattedFrom : ($formattedFrom . ' to ' . $formattedTo);
    }

    return date('F j, Y', strtotime($dateFrom !== null ? $dateFrom : $dateTo));
  }

  function taxSummaryReport()
  {
    if ($this->session->userdata('level') !== 'Admin') {
      echo "Access Denied";
      return;
    }

    $settingsID = $this->session->userdata('settingsID');
    $dateFromInput = trim((string) $this->input->get('date_from'));
    $dateToInput = trim((string) $this->input->get('date_to'));

    $dateFrom = $this->_normalizeDateInput($dateFromInput);
    $dateTo = $this->_normalizeDateInput($dateToInput);

    if ($dateFrom === null && $dateTo !== null) {
      $dateFrom = $dateTo;
    } elseif ($dateTo === null && $dateFrom !== null) {
      $dateTo = $dateFrom;
    }

    if ($dateFrom !== null && $dateTo !== null && strtotime($dateFrom) > strtotime($dateTo)) {
      $swap = $dateFrom;
      $dateFrom = $dateTo;
      $dateTo = $swap;
    }

    $entries = $this->CashModel->taxPaymentEntries($settingsID, $dateFrom, $dateTo);
    $customerSummaries = array();
    $monthSummaries = array();
    $totals = array(
      'cashAmount' => 0.0,
      'taxAmount' => 0.0,
      'grossAmount' => 0.0,
      'entryCount' => 0,
      'uniqueClients' => 0,
    );
    $uniqueClients = array();

    foreach ($entries as $row) {
      $cashPaid = (float) ($row->AmountPaid ?? 0);
      $taxAmount = (float) ($row->TaxAmount ?? 0);
      $grossAmount = (float) ($row->GrossAmountPaid ?? ($cashPaid + $taxAmount));
      $custID = trim((string) ($row->CustID ?? ''));
      $customerName = trim((string) ($row->Customer ?? ''));
      $customerKey = $custID !== ''
        ? 'cust:' . $custID
        : 'name:' . strtolower($customerName !== '' ? $customerName : ('payment-' . (string) ($row->paymentID ?? '0')));
      $customerLabel = $customerName !== '' ? $customerName : 'Unknown Customer';
      $periodKey = '';
      if (!empty($row->PDate) && trim((string) $row->PDate) !== '0000-00-00') {
        $periodKey = date('Y-m', strtotime((string) $row->PDate));
      }

      $totals['cashAmount'] += $cashPaid;
      $totals['taxAmount'] += $taxAmount;
      $totals['grossAmount'] += $grossAmount;
      $totals['entryCount']++;
      $uniqueClients[$customerKey] = true;

      if (!isset($customerSummaries[$customerKey])) {
        $customerSummaries[$customerKey] = array(
          'label' => $customerLabel,
          'custID' => $custID,
          'entryCount' => 0,
          'cashAmount' => 0.0,
          'taxAmount' => 0.0,
          'grossAmount' => 0.0,
        );
      }

      $customerSummaries[$customerKey]['entryCount']++;
      $customerSummaries[$customerKey]['cashAmount'] += $cashPaid;
      $customerSummaries[$customerKey]['taxAmount'] += $taxAmount;
      $customerSummaries[$customerKey]['grossAmount'] += $grossAmount;

      if ($periodKey !== '') {
        if (!isset($monthSummaries[$periodKey])) {
          $monthSummaries[$periodKey] = array(
            'periodKey' => $periodKey,
            'periodLabel' => date('F Y', strtotime($periodKey . '-01')),
            'entryCount' => 0,
            'cashAmount' => 0.0,
            'taxAmount' => 0.0,
            'grossAmount' => 0.0,
          );
        }

        $monthSummaries[$periodKey]['entryCount']++;
        $monthSummaries[$periodKey]['cashAmount'] += $cashPaid;
        $monthSummaries[$periodKey]['taxAmount'] += $taxAmount;
        $monthSummaries[$periodKey]['grossAmount'] += $grossAmount;
      }
    }

    $totals['cashAmount'] = round($totals['cashAmount'], 2);
    $totals['taxAmount'] = round($totals['taxAmount'], 2);
    $totals['grossAmount'] = round($totals['grossAmount'], 2);
    $totals['uniqueClients'] = count($uniqueClients);

    $customerSummaries = array_values($customerSummaries);
    usort($customerSummaries, function ($left, $right) {
      if ($left['taxAmount'] === $right['taxAmount']) {
        return strcasecmp((string) $left['label'], (string) $right['label']);
      }
      return ($left['taxAmount'] < $right['taxAmount']) ? 1 : -1;
    });

    $monthSummaries = array_values($monthSummaries);
    usort($monthSummaries, function ($left, $right) {
      return strcmp((string) $right['periodKey'], (string) $left['periodKey']);
    });

    $businessDetails = $this->CashModel->businessDetails($settingsID);
    $result = array(
      'entries' => $entries,
      'totals' => $totals,
      'customerSummaries' => $customerSummaries,
      'monthSummaries' => $monthSummaries,
      'filterDateFrom' => $dateFrom,
      'filterDateTo' => $dateTo,
      'business' => !empty($businessDetails) ? $businessDetails[0] : null,
      'autoPrint' => $this->input->get('print') == '1' || strtolower((string) $this->input->get('autoprint')) === 'true',
      'generatedAt' => date('F j, Y h:i A'),
    );

    $this->load->view('tax_summary_report', $result);
  }

  function accountingReports()
  {
    if ($this->session->userdata('level') !== 'Admin') {
      echo "Access Denied";
      return;
    }

    $settingsID = (int) $this->session->userdata('settingsID');
    $dateFrom = $this->_normalizeDateInput(trim((string) $this->input->get('date_from')));
    $dateTo = $this->_normalizeDateInput(trim((string) $this->input->get('date_to')));

    if ($dateFrom === null && $dateTo === null) {
      $dateFrom = date('Y-01-01');
      $dateTo = date('Y-m-d');
    } elseif ($dateFrom === null) {
      $dateFrom = $dateTo;
    } elseif ($dateTo === null) {
      $dateTo = $dateFrom;
    }

    if ($dateFrom !== null && $dateTo !== null && strtotime($dateFrom) > strtotime($dateTo)) {
      $swap = $dateFrom;
      $dateFrom = $dateTo;
      $dateTo = $swap;
    }

    $asOfDate = $dateTo ?: date('Y-m-d');

    $invoiceEntries = $this->CashModel->accountingInvoices($settingsID, $dateFrom, $dateTo);
    $invoicePayments = $this->CashModel->accountingInvoicePayments($settingsID, $dateFrom, $dateTo);
    $invoiceEntriesToDate = $this->CashModel->accountingInvoices($settingsID, null, $asOfDate);
    $invoicePaymentsToDate = $this->CashModel->accountingInvoicePayments($settingsID, null, $asOfDate);

    $expensesRange = $this->CashModel->accountingExpenses($settingsID, $dateFrom, $dateTo);
    $expensesToDate = $this->CashModel->accountingExpenses($settingsID, null, $asOfDate);

    $posSalesRange = $this->CashModel->accountingPosSales($settingsID, $dateFrom, $dateTo);
    $posPaymentsRange = $this->CashModel->accountingPosPayments($settingsID, $dateFrom, $dateTo);
    $posSalesToDate = $this->CashModel->accountingPosSales($settingsID, null, $asOfDate);
    $posPaymentsToDate = $this->CashModel->accountingPosPayments($settingsID, null, $asOfDate);

    $payrollRunsRange = $this->PayrollModel->getPayrollRunsByDateRange($settingsID, $dateFrom, $dateTo, 'posted');
    $payrollRunsToDate = $this->PayrollModel->getPayrollRunsByDateRange($settingsID, null, $asOfDate, 'posted');
    $payrollDeductionsRange = $this->PayrollModel->getDeductionSummary($settingsID, $dateFrom, $dateTo);
    $payrollDeductionsToDate = $this->PayrollModel->getDeductionSummary($settingsID, null, $asOfDate);
    $payrollCashAdvances = $this->PayrollModel->getPayrollCashAdvances($settingsID, '', 'all');

    $invoiceSnapshot = $this->_buildInvoiceReceivableSnapshot($invoiceEntriesToDate, $invoicePaymentsToDate, $asOfDate);
    $posSnapshot = $this->_buildPosReceivableSnapshot($posSalesToDate, $posPaymentsToDate, $asOfDate);
    $reportInvoiceSnapshot = $this->_buildInvoiceReceivableSnapshot($invoiceEntries, $invoicePaymentsToDate, $asOfDate);
    $reportPosSnapshot = $this->_buildPosReceivableSnapshot($posSalesRange, $posPaymentsToDate, $asOfDate);
    $payrollSnapshot = $this->_buildAccountingPayrollSnapshot(
      $payrollRunsRange,
      $payrollRunsToDate,
      $payrollDeductionsRange,
      $payrollDeductionsToDate,
      $payrollCashAdvances,
      $dateFrom,
      $dateTo,
      $asOfDate
    );
    $receivables = $this->_buildAccountingReceivablesReport($reportInvoiceSnapshot, $reportPosSnapshot);
    $incomeStatement = $this->_buildAccountingIncomeStatement($invoiceEntries, $invoicePayments, $expensesRange, $posSalesRange, $posPaymentsRange, $payrollSnapshot);
    $cashFlow = $this->_buildAccountingCashFlow($dateFrom, $dateTo, $invoiceEntries, $invoicePayments, $expensesRange, $posSalesRange, $posPaymentsRange, $payrollRunsRange, $payrollCashAdvances, $payrollSnapshot);
    $expenseSummary = $this->_buildAccountingExpenseSummary($expensesRange, $payrollRunsRange, $payrollSnapshot);
    $balanceSheet = $this->_buildAccountingBalanceSheet(
      $invoiceEntriesToDate,
      $invoicePaymentsToDate,
      $expensesToDate,
      $posSalesToDate,
      $posPaymentsToDate,
      $invoiceSnapshot,
      $posSnapshot,
      $payrollSnapshot
    );

    $businessDetails = $this->CashModel->businessDetails($settingsID);
    $result = array(
      'business' => !empty($businessDetails) ? $businessDetails[0] : null,
      'filterDateFrom' => $dateFrom,
      'filterDateTo' => $dateTo,
      'asOfDate' => $asOfDate,
      'rangeLabel' => $this->_formatReportDateRangeLabel($dateFrom, $dateTo),
      'generatedAt' => date('F j, Y h:i A'),
      'autoPrint' => $this->input->get('print') == '1' || strtolower((string) $this->input->get('autoprint')) === 'true',
      'headlineCards' => array(
        'totalRevenue' => (float) ($incomeStatement['totalRevenue'] ?? 0),
        'netIncome' => (float) ($incomeStatement['netIncome'] ?? 0),
        'cashInflow' => (float) ($cashFlow['totalCashIn'] ?? 0),
        'totalExpenses' => (float) ($expenseSummary['total'] ?? 0),
        'accountsReceivable' => (float) ($receivables['totalReceivable'] ?? 0),
        'taxCredits' => (float) ($cashFlow['invoiceTaxCredits'] ?? 0),
      ),
      'incomeStatement' => $incomeStatement,
      'balanceSheet' => $balanceSheet,
      'cashFlow' => $cashFlow,
      'receivables' => $receivables,
      'expenseSummary' => $expenseSummary,
      'payrollSnapshot' => $payrollSnapshot,
    );

    $this->load->view('accounting_reports', $result);
  }

  private function _emptyAccountingAgingBuckets()
  {
    return array(
      'current' => array(
        'key' => 'current',
        'label' => 'Current',
        'count' => 0,
        'amount' => 0.0,
      ),
      '1_30' => array(
        'key' => '1_30',
        'label' => '1 - 30 Days',
        'count' => 0,
        'amount' => 0.0,
      ),
      '31_60' => array(
        'key' => '31_60',
        'label' => '31 - 60 Days',
        'count' => 0,
        'amount' => 0.0,
      ),
      '61_90' => array(
        'key' => '61_90',
        'label' => '61 - 90 Days',
        'count' => 0,
        'amount' => 0.0,
      ),
      '91_plus' => array(
        'key' => '91_plus',
        'label' => '91+ Days',
        'count' => 0,
        'amount' => 0.0,
      ),
    );
  }

  private function _resolveAccountingAgingBucketKey($daysPastDue)
  {
    if ($daysPastDue === null || $daysPastDue <= 0) {
      return 'current';
    }

    if ($daysPastDue <= 30) {
      return '1_30';
    }

    if ($daysPastDue <= 60) {
      return '31_60';
    }

    if ($daysPastDue <= 90) {
      return '61_90';
    }

    return '91_plus';
  }

  private function _buildInvoiceReceivableSnapshot($invoiceEntries, $invoicePayments, $asOfDate)
  {
    $creditByInvoice = array();
    $cashCollectedToDate = 0.0;
    $taxCreditsToDate = 0.0;

    foreach ((array) $invoicePayments as $payment) {
      $invoiceNo = trim((string) ($payment->InvoiceNo ?? ''));
      if ($invoiceNo === '') {
        continue;
      }

      $cashAmount = round((float) ($payment->AmountPaid ?? 0), 2);
      $taxAmount = round((float) ($payment->TaxAmount ?? 0), 2);
      $creditByInvoice[$invoiceNo] = (float) ($creditByInvoice[$invoiceNo] ?? 0) + $cashAmount + $taxAmount;
      $cashCollectedToDate += $cashAmount;
      $taxCreditsToDate += $taxAmount;
    }

    $agingBuckets = $this->_emptyAccountingAgingBuckets();
    $rows = array();
    $billedToDate = 0.0;
    $openTotal = 0.0;
    $openCount = 0;

    foreach ((array) $invoiceEntries as $invoice) {
      $invoiceNo = trim((string) ($invoice->InvoiceNo ?? ''));
      $totalDue = round((float) ($invoice->TotalDue ?? 0), 2);
      $creditApplied = round((float) ($creditByInvoice[$invoiceNo] ?? 0), 2);
      $balance = round(max(0, $totalDue - $creditApplied), 2);
      $dueDate = $this->_resolveInvoiceReportDueDate($invoice);
      $daysPastDue = $dueDate !== null
        ? (int) floor((strtotime($asOfDate) - strtotime($dueDate)) / 86400)
        : null;

      $billedToDate += $totalDue;

      if ($balance <= 0.00001) {
        continue;
      }

      $bucketKey = $this->_resolveAccountingAgingBucketKey($daysPastDue);
      $agingBuckets[$bucketKey]['count']++;
      $agingBuckets[$bucketKey]['amount'] += $balance;
      $openTotal += $balance;
      $openCount++;

      $rows[] = array(
        'sourceType' => 'invoice',
        'sourceLabel' => trim((string) ($invoice->invoiceSource ?? 'Invoice')) ?: 'Invoice',
        'referenceNo' => $invoiceNo,
        'customer' => trim((string) ($invoice->Customer ?? 'Unknown Customer')),
        'description' => trim((string) ($invoice->JobDescription ?? '')),
        'documentDate' => $this->_normalizeDateInput($invoice->TransDate ?? ''),
        'dueDate' => $dueDate,
        'totalAmount' => $totalDue,
        'creditApplied' => $creditApplied,
        'balance' => $balance,
        'daysPastDue' => $daysPastDue !== null ? max(0, $daysPastDue) : 0,
        'viewUrl' => !empty($invoice->orderID) ? (base_url() . 'Page/invoice?id=' . rawurlencode((string) $invoice->orderID)) : '',
      );
    }

    foreach ($agingBuckets as $key => $bucket) {
      $agingBuckets[$key]['amount'] = round((float) $bucket['amount'], 2);
    }

    usort($rows, function ($left, $right) {
      $leftDate = !empty($left['dueDate']) ? strtotime((string) $left['dueDate']) : strtotime('9999-12-31');
      $rightDate = !empty($right['dueDate']) ? strtotime((string) $right['dueDate']) : strtotime('9999-12-31');
      if ($leftDate !== $rightDate) {
        return $leftDate <=> $rightDate;
      }
      return strcasecmp((string) ($left['customer'] ?? ''), (string) ($right['customer'] ?? ''));
    });

    return array(
      'billedToDate' => round($billedToDate, 2),
      'cashCollectedToDate' => round($cashCollectedToDate, 2),
      'taxCreditsToDate' => round($taxCreditsToDate, 2),
      'totalCreditToDate' => round($cashCollectedToDate + $taxCreditsToDate, 2),
      'openTotal' => round($openTotal, 2),
      'openCount' => $openCount,
      'agingBuckets' => $agingBuckets,
      'rows' => $rows,
    );
  }

  private function _buildPosReceivableSnapshot($sales, $payments, $asOfDate)
  {
    $paymentsBySale = array();
    $cashCollectedToDate = 0.0;

    foreach ((array) $payments as $payment) {
      $saleId = (int) ($payment->sale_id ?? 0);
      if ($saleId <= 0) {
        continue;
      }

      $amount = round((float) ($payment->amount ?? 0), 2);
      $paymentsBySale[$saleId] = (float) ($paymentsBySale[$saleId] ?? 0) + $amount;
      $cashCollectedToDate += $amount;
    }

    $agingBuckets = $this->_emptyAccountingAgingBuckets();
    $rows = array();
    $grossSalesToDate = 0.0;
    $openTotal = 0.0;
    $openCount = 0;

    foreach ((array) $sales as $sale) {
      $saleId = (int) ($sale->id ?? 0);
      $grossAmount = round((float) ($sale->grand_total ?? 0), 2);
      $creditApplied = round((float) ($paymentsBySale[$saleId] ?? 0), 2);
      $balance = round(max(0, $grossAmount - $creditApplied), 2);
      $documentDate = $this->_normalizeDateInput($sale->transaction_date ?? '');
      $dueDate = $this->_normalizeDateInput($sale->first_due_date ?? '') ?: $documentDate;
      $daysPastDue = $dueDate !== null
        ? (int) floor((strtotime($asOfDate) - strtotime($dueDate)) / 86400)
        : null;

      $grossSalesToDate += $grossAmount;

      if ($balance <= 0.00001) {
        continue;
      }

      $bucketKey = $this->_resolveAccountingAgingBucketKey($daysPastDue);
      $agingBuckets[$bucketKey]['count']++;
      $agingBuckets[$bucketKey]['amount'] += $balance;
      $openTotal += $balance;
      $openCount++;

      $rows[] = array(
        'sourceType' => 'pos',
        'sourceLabel' => 'POS ' . (((string) ($sale->payment_term ?? '')) === 'installment' ? 'Installment' : 'Sale'),
        'referenceNo' => trim((string) ($sale->sale_no ?? 'POS Sale')),
        'customer' => trim((string) ($sale->customer_name ?? 'Walk-in Customer')),
        'description' => trim((string) ($sale->notes ?? 'POS sale')),
        'documentDate' => $documentDate,
        'dueDate' => $dueDate,
        'totalAmount' => $grossAmount,
        'creditApplied' => $creditApplied,
        'balance' => $balance,
        'daysPastDue' => $daysPastDue !== null ? max(0, $daysPastDue) : 0,
        'viewUrl' => '',
      );
    }

    foreach ($agingBuckets as $key => $bucket) {
      $agingBuckets[$key]['amount'] = round((float) $bucket['amount'], 2);
    }

    usort($rows, function ($left, $right) {
      $leftDate = !empty($left['dueDate']) ? strtotime((string) $left['dueDate']) : strtotime('9999-12-31');
      $rightDate = !empty($right['dueDate']) ? strtotime((string) $right['dueDate']) : strtotime('9999-12-31');
      if ($leftDate !== $rightDate) {
        return $leftDate <=> $rightDate;
      }
      return strcasecmp((string) ($left['customer'] ?? ''), (string) ($right['customer'] ?? ''));
    });

    return array(
      'grossSalesToDate' => round($grossSalesToDate, 2),
      'cashCollectedToDate' => round($cashCollectedToDate, 2),
      'openTotal' => round($openTotal, 2),
      'openCount' => $openCount,
      'agingBuckets' => $agingBuckets,
      'rows' => $rows,
    );
  }

  private function _buildAccountingReceivablesReport($invoiceSnapshot, $posSnapshot)
  {
    $agingBuckets = $this->_emptyAccountingAgingBuckets();
    foreach ($agingBuckets as $bucketKey => $bucket) {
      $agingBuckets[$bucketKey]['count'] =
        (int) ($invoiceSnapshot['agingBuckets'][$bucketKey]['count'] ?? 0) +
        (int) ($posSnapshot['agingBuckets'][$bucketKey]['count'] ?? 0);
      $agingBuckets[$bucketKey]['amount'] = round(
        (float) ($invoiceSnapshot['agingBuckets'][$bucketKey]['amount'] ?? 0) +
          (float) ($posSnapshot['agingBuckets'][$bucketKey]['amount'] ?? 0),
        2
      );
    }

    $rows = array_merge(
      array_values((array) ($invoiceSnapshot['rows'] ?? array())),
      array_values((array) ($posSnapshot['rows'] ?? array()))
    );

    usort($rows, function ($left, $right) {
      $leftDate = !empty($left['dueDate']) ? strtotime((string) $left['dueDate']) : strtotime('9999-12-31');
      $rightDate = !empty($right['dueDate']) ? strtotime((string) $right['dueDate']) : strtotime('9999-12-31');
      if ($leftDate !== $rightDate) {
        return $leftDate <=> $rightDate;
      }
      return (($right['balance'] ?? 0) <=> ($left['balance'] ?? 0));
    });

    return array(
      'totalReceivable' => round((float) ($invoiceSnapshot['openTotal'] ?? 0) + (float) ($posSnapshot['openTotal'] ?? 0), 2),
      'openCount' => (int) ($invoiceSnapshot['openCount'] ?? 0) + (int) ($posSnapshot['openCount'] ?? 0),
      'invoiceReceivable' => round((float) ($invoiceSnapshot['openTotal'] ?? 0), 2),
      'invoiceOpenCount' => (int) ($invoiceSnapshot['openCount'] ?? 0),
      'posReceivable' => round((float) ($posSnapshot['openTotal'] ?? 0), 2),
      'posOpenCount' => (int) ($posSnapshot['openCount'] ?? 0),
      'agingBuckets' => array_values($agingBuckets),
      'rows' => $rows,
    );
  }

  private function _dateFallsWithinNormalizedRange($dateValue, $dateFrom = null, $dateTo = null)
  {
    $dateValue = $this->_normalizeDateInput($dateValue);
    $dateFrom = $this->_normalizeDateInput($dateFrom);
    $dateTo = $this->_normalizeDateInput($dateTo);

    if ($dateValue === null) {
      return false;
    }

    if ($dateFrom !== null && strtotime($dateValue) < strtotime($dateFrom)) {
      return false;
    }

    if ($dateTo !== null && strtotime($dateValue) > strtotime($dateTo)) {
      return false;
    }

    return true;
  }

  private function _buildAccountingPayrollSnapshot($payrollRunsRange, $payrollRunsToDate, $deductionSummaryRange, $deductionSummaryToDate, $cashAdvances, $dateFrom, $dateTo, $asOfDate)
  {
    $runCount = 0;
    $employeeCount = 0;
    $grossExpense = 0.0;
    $netPaid = 0.0;
    $totalDeductions = 0.0;

    foreach ((array) $payrollRunsRange as $run) {
      $runCount++;
      $employeeCount += (int) ($run->employeeCount ?? 0);
      $grossExpense += round((float) ($run->totalGross ?? 0), 2);
      $netPaid += round((float) ($run->totalNet ?? 0), 2);
      $totalDeductions += round((float) ($run->totalDeductions ?? 0), 2);
    }

    $runCountToDate = 0;
    $employeeCountToDate = 0;
    $grossExpenseToDate = 0.0;
    $netPaidToDate = 0.0;
    $totalDeductionsToDate = 0.0;

    foreach ((array) $payrollRunsToDate as $run) {
      $runCountToDate++;
      $employeeCountToDate += (int) ($run->employeeCount ?? 0);
      $grossExpenseToDate += round((float) ($run->totalGross ?? 0), 2);
      $netPaidToDate += round((float) ($run->totalNet ?? 0), 2);
      $totalDeductionsToDate += round((float) ($run->totalDeductions ?? 0), 2);
    }

    $philhealth = round((float) ($deductionSummaryRange['philhealth'] ?? 0), 2);
    $sss = round((float) ($deductionSummaryRange['sss'] ?? 0), 2);
    $pagibig = round((float) ($deductionSummaryRange['pagibig'] ?? 0), 2);
    $loan = round((float) ($deductionSummaryRange['loan'] ?? 0), 2);
    $cashAdvanceRecoveries = round((float) ($deductionSummaryRange['cash_advance'] ?? 0), 2);
    $other = round((float) ($deductionSummaryRange['other'] ?? 0), 2);

    $philhealthToDate = round((float) ($deductionSummaryToDate['philhealth'] ?? 0), 2);
    $sssToDate = round((float) ($deductionSummaryToDate['sss'] ?? 0), 2);
    $pagibigToDate = round((float) ($deductionSummaryToDate['pagibig'] ?? 0), 2);
    $loanToDate = round((float) ($deductionSummaryToDate['loan'] ?? 0), 2);
    $cashAdvanceRecoveriesToDate = round((float) ($deductionSummaryToDate['cash_advance'] ?? 0), 2);
    $otherToDate = round((float) ($deductionSummaryToDate['other'] ?? 0), 2);

    $statutoryDeductions = round($philhealth + $sss + $pagibig, 2);
    $statutoryDeductionsToDate = round($philhealthToDate + $sssToDate + $pagibigToDate, 2);
    $trackedPayables = round($statutoryDeductions + $loan + $other, 2);
    $trackedPayablesToDate = round($statutoryDeductionsToDate + $loanToDate + $otherToDate, 2);

    $cashAdvancesIssued = 0.0;
    $cashAdvancesIssuedToDate = 0.0;
    $cashAdvancesOutstanding = 0.0;
    $cashAdvanceOpenCount = 0;

    foreach ((array) $cashAdvances as $advance) {
      $advanceDate = $this->_normalizeDateInput($advance->advanceDate ?? '');
      $amount = round((float) ($advance->amount ?? 0), 2);
      $balanceAmount = round((float) ($advance->balanceAmount ?? 0), 2);

      if ($this->_dateFallsWithinNormalizedRange($advanceDate, $dateFrom, $dateTo)) {
        $cashAdvancesIssued += $amount;
      }

      if ($advanceDate !== null && strtotime($advanceDate) <= strtotime($asOfDate)) {
        $cashAdvancesIssuedToDate += $amount;
        if ($balanceAmount > 0.00001) {
          $cashAdvancesOutstanding += $balanceAmount;
          $cashAdvanceOpenCount++;
        }
      }
    }

    $payableLines = array();
    if ($philhealthToDate > 0) {
      $payableLines[] = array('label' => 'PhilHealth Payable', 'amount' => $philhealthToDate);
    }
    if ($sssToDate > 0) {
      $payableLines[] = array('label' => 'SSS Payable', 'amount' => $sssToDate);
    }
    if ($pagibigToDate > 0) {
      $payableLines[] = array('label' => 'Pag-IBIG Payable', 'amount' => $pagibigToDate);
    }
    if ($loanToDate > 0) {
      $payableLines[] = array('label' => 'Loan Deductions Payable', 'amount' => $loanToDate);
    }
    if ($otherToDate > 0) {
      $payableLines[] = array('label' => 'Other Payroll Deductions Payable', 'amount' => $otherToDate);
    }

    return array(
      'runCount' => $runCount,
      'employeeCount' => $employeeCount,
      'grossExpense' => round($grossExpense, 2),
      'netPaid' => round($netPaid, 2),
      'totalDeductions' => round($totalDeductions, 2),
      'philhealth' => $philhealth,
      'sss' => $sss,
      'pagibig' => $pagibig,
      'loan' => $loan,
      'cashAdvanceRecoveries' => $cashAdvanceRecoveries,
      'other' => $other,
      'statutoryDeductions' => $statutoryDeductions,
      'trackedPayables' => $trackedPayables,
      'cashAdvancesIssued' => round($cashAdvancesIssued, 2),
      'toDate' => array(
        'runCount' => $runCountToDate,
        'employeeCount' => $employeeCountToDate,
        'grossExpense' => round($grossExpenseToDate, 2),
        'netPaid' => round($netPaidToDate, 2),
        'totalDeductions' => round($totalDeductionsToDate, 2),
        'philhealth' => $philhealthToDate,
        'sss' => $sssToDate,
        'pagibig' => $pagibigToDate,
        'loan' => $loanToDate,
        'cashAdvanceRecoveries' => $cashAdvanceRecoveriesToDate,
        'other' => $otherToDate,
        'statutoryDeductions' => $statutoryDeductionsToDate,
        'trackedPayables' => $trackedPayablesToDate,
        'cashAdvancesIssued' => round($cashAdvancesIssuedToDate, 2),
        'cashAdvancesOutstanding' => round($cashAdvancesOutstanding, 2),
        'cashAdvanceOpenCount' => $cashAdvanceOpenCount,
      ),
      'payableLines' => $payableLines,
    );
  }

  private function _buildAccountingIncomeStatement($invoiceEntries, $invoicePayments, $expensesRange, $posSalesRange, $posPaymentsRange, $payrollSnapshot = array())
  {
    $revenueSources = array();
    $totalInvoiceRevenue = 0.0;

    foreach ((array) $invoiceEntries as $invoice) {
      $sourceRaw = strtolower(trim((string) ($invoice->invoiceSource ?? 'Invoice')));
      if ($sourceRaw === 'job order') {
        $sourceKey = 'job_order';
        $label = 'Job Order Revenue';
      } elseif ($sourceRaw === 'others' || $sourceRaw === 'invoice' || $sourceRaw === '') {
        $sourceKey = 'other_invoices';
        $label = 'Other Invoice Revenue';
      } else {
        $sourceKey = preg_replace('/[^a-z0-9]+/i', '_', $sourceRaw);
        $label = ucwords(trim((string) ($invoice->invoiceSource ?? 'Invoice'))) . ' Revenue';
      }

      if (!isset($revenueSources[$sourceKey])) {
        $revenueSources[$sourceKey] = array(
          'key' => $sourceKey,
          'label' => $label,
          'count' => 0,
          'amount' => 0.0,
        );
      }

      $amount = round((float) ($invoice->TotalDue ?? 0), 2);
      $revenueSources[$sourceKey]['count']++;
      $revenueSources[$sourceKey]['amount'] += $amount;
      $totalInvoiceRevenue += $amount;
    }

    $posSalesTotal = 0.0;
    foreach ((array) $posSalesRange as $sale) {
      $posSalesTotal += round((float) ($sale->grand_total ?? 0), 2);
    }

    $revenueSources['pos_sales'] = array(
      'key' => 'pos_sales',
      'label' => 'POS Sales Revenue',
      'count' => count((array) $posSalesRange),
      'amount' => round($posSalesTotal, 2),
    );

    $manualExpenses = 0.0;
    foreach ((array) $expensesRange as $expense) {
      $manualExpenses += round((float) ($expense->Amount ?? 0), 2);
    }

    $payrollExpense = round((float) ($payrollSnapshot['grossExpense'] ?? 0), 2);
    $totalExpenses = round($manualExpenses + $payrollExpense, 2);

    $invoiceCashCollections = 0.0;
    $invoiceTaxCredits = 0.0;
    foreach ((array) $invoicePayments as $payment) {
      $invoiceCashCollections += round((float) ($payment->AmountPaid ?? 0), 2);
      $invoiceTaxCredits += round((float) ($payment->TaxAmount ?? 0), 2);
    }

    $posCashCollections = 0.0;
    foreach ((array) $posPaymentsRange as $payment) {
      $posCashCollections += round((float) ($payment->amount ?? 0), 2);
    }

    foreach ($revenueSources as $key => $source) {
      $revenueSources[$key]['amount'] = round((float) $source['amount'], 2);
    }

    uasort($revenueSources, function ($left, $right) {
      if ($left['amount'] === $right['amount']) {
        return strcmp((string) $left['label'], (string) $right['label']);
      }
      return ($left['amount'] < $right['amount']) ? 1 : -1;
    });

    $totalRevenue = round($totalInvoiceRevenue + $posSalesTotal, 2);
    $netIncome = round($totalRevenue - $totalExpenses, 2);

    return array(
      'revenueSources' => array_values($revenueSources),
      'invoiceCount' => count((array) $invoiceEntries),
      'posSalesCount' => count((array) $posSalesRange),
      'totalRevenue' => $totalRevenue,
      'manualExpenses' => round($manualExpenses, 2),
      'payrollExpense' => $payrollExpense,
      'totalExpenses' => round($totalExpenses, 2),
      'netIncome' => $netIncome,
      'invoiceCashCollections' => round($invoiceCashCollections, 2),
      'invoiceTaxCredits' => round($invoiceTaxCredits, 2),
      'invoiceGrossCredits' => round($invoiceCashCollections + $invoiceTaxCredits, 2),
      'posCashCollections' => round($posCashCollections, 2),
      'totalCashCollections' => round($invoiceCashCollections + $posCashCollections, 2),
      'payrollRunCount' => (int) ($payrollSnapshot['runCount'] ?? 0),
      'payrollEmployeeCount' => (int) ($payrollSnapshot['employeeCount'] ?? 0),
      'payrollNetPaid' => round((float) ($payrollSnapshot['netPaid'] ?? 0), 2),
      'payrollDeductions' => round((float) ($payrollSnapshot['totalDeductions'] ?? 0), 2),
    );
  }

  private function _buildAccountingCashFlow($dateFrom, $dateTo, $invoiceEntries, $invoicePayments, $expensesRange, $posSalesRange, $posPaymentsRange, $payrollRunsRange = array(), $payrollCashAdvances = array(), $payrollSnapshot = array())
  {
    $monthMap = $this->_initializeMonthSummaryMap($dateFrom, $dateTo, array(
      'invoiceRevenue' => 0.0,
      'invoiceCash' => 0.0,
      'invoiceTax' => 0.0,
      'posRevenue' => 0.0,
      'posCash' => 0.0,
      'payrollExpense' => 0.0,
      'payrollNet' => 0.0,
      'cashAdvancesIssued' => 0.0,
      'expenses' => 0.0,
      'netIncome' => 0.0,
      'netCash' => 0.0,
    ));

    $invoiceCashCollections = 0.0;
    $invoiceTaxCredits = 0.0;
    $posCashCollections = 0.0;
    $manualExpensesPaid = 0.0;

    foreach ((array) $invoiceEntries as $invoice) {
      $dateKey = $this->_normalizeDateInput($invoice->TransDate ?? '');
      if ($dateKey === null) {
        continue;
      }

      $periodKey = date('Y-m', strtotime($dateKey));
      if (!isset($monthMap[$periodKey])) {
        continue;
      }

      $monthMap[$periodKey]['invoiceRevenue'] += round((float) ($invoice->TotalDue ?? 0), 2);
    }

    foreach ((array) $invoicePayments as $payment) {
      $dateKey = $this->_normalizeDateInput($payment->PDate ?? '');
      $cashAmount = round((float) ($payment->AmountPaid ?? 0), 2);
      $taxAmount = round((float) ($payment->TaxAmount ?? 0), 2);
      $invoiceCashCollections += $cashAmount;
      $invoiceTaxCredits += $taxAmount;

      if ($dateKey === null) {
        continue;
      }

      $periodKey = date('Y-m', strtotime($dateKey));
      if (!isset($monthMap[$periodKey])) {
        continue;
      }

      $monthMap[$periodKey]['invoiceCash'] += $cashAmount;
      $monthMap[$periodKey]['invoiceTax'] += $taxAmount;
    }

    foreach ((array) $posSalesRange as $sale) {
      $dateKey = $this->_normalizeDateInput($sale->transaction_date ?? '');
      if ($dateKey === null) {
        continue;
      }

      $periodKey = date('Y-m', strtotime($dateKey));
      if (!isset($monthMap[$periodKey])) {
        continue;
      }

      $monthMap[$periodKey]['posRevenue'] += round((float) ($sale->grand_total ?? 0), 2);
    }

    foreach ((array) $posPaymentsRange as $payment) {
      $dateKey = $this->_normalizeDateInput($payment->payment_date ?? '');
      $amount = round((float) ($payment->amount ?? 0), 2);
      $posCashCollections += $amount;

      if ($dateKey === null) {
        continue;
      }

      $periodKey = date('Y-m', strtotime($dateKey));
      if (!isset($monthMap[$periodKey])) {
        continue;
      }

      $monthMap[$periodKey]['posCash'] += $amount;
    }

    foreach ((array) $expensesRange as $expense) {
      $dateKey = $this->_normalizeDateInput($expense->ExpenseDate ?? '');
      $amount = round((float) ($expense->Amount ?? 0), 2);
      $manualExpensesPaid += $amount;

      if ($dateKey === null) {
        continue;
      }

      $periodKey = date('Y-m', strtotime($dateKey));
      if (!isset($monthMap[$periodKey])) {
        continue;
      }

      $monthMap[$periodKey]['expenses'] += $amount;
    }

    foreach ((array) $payrollRunsRange as $run) {
      $dateKey = $this->_normalizeDateInput($run->payDate ?? '');
      $grossAmount = round((float) ($run->totalGross ?? 0), 2);
      $netAmount = round((float) ($run->totalNet ?? 0), 2);

      if ($dateKey === null) {
        continue;
      }

      $periodKey = date('Y-m', strtotime($dateKey));
      if (!isset($monthMap[$periodKey])) {
        continue;
      }

      $monthMap[$periodKey]['payrollExpense'] += $grossAmount;
      $monthMap[$periodKey]['payrollNet'] += $netAmount;
    }

    $cashAdvancesIssued = round((float) ($payrollSnapshot['cashAdvancesIssued'] ?? 0), 2);
    $payrollNetPaid = round((float) ($payrollSnapshot['netPaid'] ?? 0), 2);
    $payrollExpense = round((float) ($payrollSnapshot['grossExpense'] ?? 0), 2);
    $payrollDeductions = round((float) ($payrollSnapshot['totalDeductions'] ?? 0), 2);

    foreach ((array) $payrollCashAdvances as $advance) {
      $dateKey = $this->_normalizeDateInput($advance->advanceDate ?? '');
      if (!$this->_dateFallsWithinNormalizedRange($dateKey, $dateFrom, $dateTo)) {
        continue;
      }

      $periodKey = date('Y-m', strtotime($dateKey));
      if (!isset($monthMap[$periodKey])) {
        continue;
      }

      $monthMap[$periodKey]['cashAdvancesIssued'] += round((float) ($advance->amount ?? 0), 2);
    }

    foreach ($monthMap as $key => $row) {
      $monthMap[$key]['invoiceRevenue'] = round((float) $row['invoiceRevenue'], 2);
      $monthMap[$key]['invoiceCash'] = round((float) $row['invoiceCash'], 2);
      $monthMap[$key]['invoiceTax'] = round((float) $row['invoiceTax'], 2);
      $monthMap[$key]['posRevenue'] = round((float) $row['posRevenue'], 2);
      $monthMap[$key]['posCash'] = round((float) $row['posCash'], 2);
      $monthMap[$key]['payrollExpense'] = round((float) $row['payrollExpense'], 2);
      $monthMap[$key]['payrollNet'] = round((float) $row['payrollNet'], 2);
      $monthMap[$key]['cashAdvancesIssued'] = round((float) $row['cashAdvancesIssued'], 2);
      $monthMap[$key]['expenses'] = round((float) $row['expenses'], 2);
      $monthMap[$key]['netIncome'] = round(
        $monthMap[$key]['invoiceRevenue'] +
          $monthMap[$key]['posRevenue'] -
          $monthMap[$key]['expenses'] -
          $monthMap[$key]['payrollExpense'],
        2
      );
      $monthMap[$key]['netCash'] = round(
        $monthMap[$key]['invoiceCash'] +
          $monthMap[$key]['posCash'] -
          $monthMap[$key]['expenses'] -
          $monthMap[$key]['payrollNet'] -
          $monthMap[$key]['cashAdvancesIssued'],
        2
      );
    }

    return array(
      'invoiceCashCollections' => round($invoiceCashCollections, 2),
      'invoiceTaxCredits' => round($invoiceTaxCredits, 2),
      'invoiceGrossCredits' => round($invoiceCashCollections + $invoiceTaxCredits, 2),
      'posCashCollections' => round($posCashCollections, 2),
      'totalCashIn' => round($invoiceCashCollections + $posCashCollections, 2),
      'manualExpensesPaid' => round($manualExpensesPaid, 2),
      'payrollExpense' => $payrollExpense,
      'payrollNetPaid' => $payrollNetPaid,
      'payrollDeductions' => $payrollDeductions,
      'cashAdvancesIssued' => $cashAdvancesIssued,
      'totalCashOut' => round($manualExpensesPaid + $payrollNetPaid + $cashAdvancesIssued, 2),
      'netCashMovement' => round($invoiceCashCollections + $posCashCollections - ($manualExpensesPaid + $payrollNetPaid + $cashAdvancesIssued), 2),
      'monthly' => array_values($monthMap),
    );
  }

  private function _buildAccountingExpenseSummary($expensesRange, $payrollRunsRange = array(), $payrollSnapshot = array())
  {
    $categories = array();
    $total = 0.0;
    $rows = array_values((array) $expensesRange);

    foreach ((array) $expensesRange as $expense) {
      $category = trim((string) ($expense->Category ?? ''));
      if ($category === '') {
        $category = 'Uncategorized';
      }

      if (!isset($categories[$category])) {
        $categories[$category] = array(
          'label' => $category,
          'count' => 0,
          'amount' => 0.0,
          'share' => 0.0,
        );
      }

      $amount = round((float) ($expense->Amount ?? 0), 2);
      $categories[$category]['count']++;
      $categories[$category]['amount'] += $amount;
      $total += $amount;
    }

    $payrollExpense = round((float) ($payrollSnapshot['grossExpense'] ?? 0), 2);
    if ($payrollExpense > 0 || !empty($payrollRunsRange)) {
      $category = 'Salaries and Wages';
      if (!isset($categories[$category])) {
        $categories[$category] = array(
          'label' => $category,
          'count' => 0,
          'amount' => 0.0,
          'share' => 0.0,
        );
      }

      foreach ((array) $payrollRunsRange as $run) {
        $amount = round((float) ($run->totalGross ?? 0), 2);
        $categories[$category]['count']++;
        $categories[$category]['amount'] += $amount;
        $total += $amount;

        $payrollRow = new stdClass();
        $payrollRow->ExpenseDate = $this->_normalizeDateInput($run->payDate ?? '') ?: '';
        $payrollRow->Category = $category;
        $payrollRow->Description = 'Payroll run for ' .
          date('M j, Y', strtotime((string) ($run->periodStart ?? $run->payDate ?? date('Y-m-d')))) .
          ' to ' .
          date('M j, Y', strtotime((string) ($run->periodEnd ?? $run->payDate ?? date('Y-m-d')))) .
          ' (' . (int) ($run->employeeCount ?? 0) . ' employee(s))';
        $payrollRow->processedBy = trim((string) ($run->createdBy ?? 'System'));
        $payrollRow->Cashier = $payrollRow->processedBy;
        $payrollRow->Amount = $amount;
        $payrollRow->entryType = 'payroll';
        $rows[] = $payrollRow;
      }
    }

    foreach ($categories as $key => $row) {
      $categories[$key]['amount'] = round((float) $row['amount'], 2);
      $categories[$key]['share'] = $total > 0 ? round(($categories[$key]['amount'] / $total) * 100, 2) : 0.0;
    }

    uasort($categories, function ($left, $right) {
      if ($left['amount'] === $right['amount']) {
        return strcmp((string) $left['label'], (string) $right['label']);
      }
      return ($left['amount'] < $right['amount']) ? 1 : -1;
    });

    usort($rows, function ($left, $right) {
      $leftDate = trim((string) (is_object($left) ? ($left->ExpenseDate ?? '') : ($left['ExpenseDate'] ?? '')));
      $rightDate = trim((string) (is_object($right) ? ($right->ExpenseDate ?? '') : ($right['ExpenseDate'] ?? '')));
      $leftTime = $leftDate !== '' && $leftDate !== '0000-00-00' ? strtotime($leftDate) : 0;
      $rightTime = $rightDate !== '' && $rightDate !== '0000-00-00' ? strtotime($rightDate) : 0;
      if ($leftTime !== $rightTime) {
        return $rightTime <=> $leftTime;
      }

      $leftAmount = (float) (is_object($left) ? ($left->Amount ?? 0) : ($left['Amount'] ?? 0));
      $rightAmount = (float) (is_object($right) ? ($right->Amount ?? 0) : ($right['Amount'] ?? 0));
      if ($leftAmount !== $rightAmount) {
        return $rightAmount <=> $leftAmount;
      }

      return 0;
    });

    return array(
      'total' => round($total, 2),
      'categoryCount' => count($categories),
      'categories' => array_values($categories),
      'rows' => array_values($rows),
    );
  }

  private function _buildAccountingBalanceSheet($invoiceEntriesToDate, $invoicePaymentsToDate, $expensesToDate, $posSalesToDate, $posPaymentsToDate, $invoiceSnapshot, $posSnapshot, $payrollSnapshot = array())
  {
    $serviceRevenueToDate = 0.0;
    foreach ((array) $invoiceEntriesToDate as $invoice) {
      $serviceRevenueToDate += round((float) ($invoice->TotalDue ?? 0), 2);
    }

    $invoiceCashToDate = 0.0;
    $taxCreditsToDate = 0.0;
    foreach ((array) $invoicePaymentsToDate as $payment) {
      $invoiceCashToDate += round((float) ($payment->AmountPaid ?? 0), 2);
      $taxCreditsToDate += round((float) ($payment->TaxAmount ?? 0), 2);
    }

    $posSalesToDateTotal = 0.0;
    foreach ((array) $posSalesToDate as $sale) {
      $posSalesToDateTotal += round((float) ($sale->grand_total ?? 0), 2);
    }

    $posCashToDate = 0.0;
    foreach ((array) $posPaymentsToDate as $payment) {
      $posCashToDate += round((float) ($payment->amount ?? 0), 2);
    }

    $manualExpensesToDate = 0.0;
    foreach ((array) $expensesToDate as $expense) {
      $manualExpensesToDate += round((float) ($expense->Amount ?? 0), 2);
    }

    $payrollExpenseToDate = round((float) ($payrollSnapshot['toDate']['grossExpense'] ?? 0), 2);
    $payrollNetPaidToDate = round((float) ($payrollSnapshot['toDate']['netPaid'] ?? 0), 2);
    $trackedLiabilities = round((float) ($payrollSnapshot['toDate']['trackedPayables'] ?? 0), 2);
    $cashAdvancesOutstanding = round((float) ($payrollSnapshot['toDate']['cashAdvancesOutstanding'] ?? 0), 2);
    $cashAdvancesIssuedToDate = round((float) ($payrollSnapshot['toDate']['cashAdvancesIssued'] ?? 0), 2);
    $expensesToDateTotal = round($manualExpensesToDate + $payrollExpenseToDate, 2);

    $cashAndCashEquivalents = round($invoiceCashToDate + $posCashToDate - $manualExpensesToDate - $payrollNetPaidToDate - $cashAdvancesIssuedToDate, 2);
    $serviceReceivables = round((float) ($invoiceSnapshot['openTotal'] ?? 0), 2);
    $posReceivables = round((float) ($posSnapshot['openTotal'] ?? 0), 2);
    $taxCreditsReceivable = round($taxCreditsToDate, 2);
    $totalAssets = round($cashAndCashEquivalents + $taxCreditsReceivable + $cashAdvancesOutstanding + $serviceReceivables + $posReceivables, 2);
    $operationalEquity = round($totalAssets - $trackedLiabilities, 2);
    $cumulativeRevenue = round($serviceRevenueToDate + $posSalesToDateTotal, 2);
    $cumulativeNetIncome = round($cumulativeRevenue - $expensesToDateTotal, 2);

    $liabilitiesAndEquity = array();
    foreach ((array) ($payrollSnapshot['payableLines'] ?? array()) as $line) {
      $liabilitiesAndEquity[] = array(
        'label' => (string) ($line['label'] ?? 'Payroll Payable'),
        'amount' => round((float) ($line['amount'] ?? 0), 2),
      );
    }
    $liabilitiesAndEquity[] = array('label' => 'Accumulated Operational Equity', 'amount' => $operationalEquity);

    return array(
      'assets' => array(
        array('label' => 'Cash and Cash Equivalents', 'amount' => $cashAndCashEquivalents),
        array('label' => 'BIR Form 2307 Tax Credits Receivable', 'amount' => $taxCreditsReceivable),
        array('label' => 'Employee Cash Advances Receivable', 'amount' => $cashAdvancesOutstanding),
        array('label' => 'Accounts Receivable - Service Invoices', 'amount' => $serviceReceivables),
        array('label' => 'Accounts Receivable - POS Installments', 'amount' => $posReceivables),
      ),
      'liabilitiesAndEquity' => $liabilitiesAndEquity,
      'serviceRevenueToDate' => $serviceRevenueToDate,
      'posRevenueToDate' => round($posSalesToDateTotal, 2),
      'cumulativeRevenue' => $cumulativeRevenue,
      'manualExpensesToDate' => round($manualExpensesToDate, 2),
      'payrollExpenseToDate' => $payrollExpenseToDate,
      'expensesToDate' => round($expensesToDateTotal, 2),
      'cumulativeNetIncome' => $cumulativeNetIncome,
      'invoiceCashToDate' => round($invoiceCashToDate, 2),
      'posCashToDate' => round($posCashToDate, 2),
      'payrollNetPaidToDate' => $payrollNetPaidToDate,
      'cashAdvancesIssuedToDate' => $cashAdvancesIssuedToDate,
      'cashAdvancesOutstanding' => $cashAdvancesOutstanding,
      'taxCreditsToDate' => $taxCreditsReceivable,
      'serviceReceivables' => $serviceReceivables,
      'posReceivables' => $posReceivables,
      'totalAssets' => $totalAssets,
      'trackedLiabilities' => $trackedLiabilities,
      'totalLiabilitiesAndEquity' => round($trackedLiabilities + $operationalEquity, 2),
      'operationalEquity' => $operationalEquity,
    );
  }

  function customerHistory()
  {
    if ($this->session->userdata('level') === 'Admin' || $this->_is_client_user()) {
      $settingsID = $this->session->userdata('settingsID');
      $custID = trim((string) $this->input->get('cust_id'));
      $customer = trim((string) $this->input->get('customer'));
      $client = null;

      if ($this->_is_client_user()) {
        $client = $this->_load_current_client();
        if (!$client) {
          show_404();
          return;
        }

        $custID = trim((string) ($client->CustID ?? ''));
        $customer = trim((string) ($client->Customer ?? ''));
      }

      $result['data'] = $this->CashModel->customerHistory($settingsID, $custID, $customer);
      if (!$client && $custID !== '') {
        $client = $this->CashModel->getClientByCustID($settingsID, $custID);
      }
      if (!$client && $customer !== '') {
        $client = $this->CashModel->getClientByName($settingsID, $customer);
      }
      $result['filterMonth'] = $this->input->get('filter_month');
      $result['filterYear'] = $this->input->get('filter_year');
      $result['custID'] = $custID;
      $result['customerName'] = $customer;
      $result['client'] = $client;
      $result['backUrl'] = $this->_is_client_user()
        ? base_url() . 'Page/clientDashboard'
        : '';
      $result['backLabel'] = $this->_is_client_user()
        ? 'Back to Dashboard'
        : 'Back to Payments';
      $result['showClientProfileAction'] = !$this->_is_client_user();
      $result['clientProfileLabel'] = $this->_is_client_user() ? 'My Company' : 'View Company';

      $this->load->view('customer_payment_history', $result);
    } else {
      echo "Access Denied";
    }
  }

  function joList()
  {
    $this->_ensureServiceFeesTable();

    if ($this->session->userdata('level') === 'Admin') {
      $settingsID = $this->session->userdata('settingsID');
      $result['data'] = $this->CashModel->joList($settingsID);
      $result['data1'] = $this->CashModel->joInvoiceNo($settingsID);
      $result['data2'] = $this->CashModel->getClients($settingsID);
      $result['serviceFees'] = $this->CashModel->priceList($settingsID);
      $this->load->view('jo_list', $result);
    } else {
      $settingsID = $this->session->userdata('settingsID');
      $staffAliases = $this->_currentUserRecordAliases();
      $result['data'] = $this->CashModel->joListStaff($settingsID, $staffAliases);
      $result['data1'] = $this->CashModel->joInvoiceNoStaff($settingsID, $staffAliases);
      $result['data2'] = $this->CashModel->getClients($settingsID);
      $result['serviceFees'] = $this->CashModel->priceList($settingsID);
      $this->load->view('jo_list', $result);
    }
  }

  function invList()
  {
    if ($this->_is_client_user()) {
      redirect('Page/clientProfile?tab=invoices');
      return;
    }

    $selectedCustomerId = trim((string) $this->input->get('customer'));

    if ($this->session->userdata('level') === 'Admin') {
      $settingsID = $this->session->userdata('settingsID');
      $this->_generateRecurringInvoices($settingsID);
      $this->_markRecurringInvoiceGeneratorRun($settingsID);

      $result['data'] = $this->CashModel->invList($settingsID, $selectedCustomerId);
      $this->_attachInvoiceItemsToCollection($result['data'], $settingsID);
      $result['data1'] = $this->CashModel->joInvoiceNo($settingsID);
      $result['data2'] = $this->CashModel->getClients($settingsID);
      $result['selectedCustomerId'] = $selectedCustomerId;
      $this->load->view('inv_list', $result);
    } else {
      $settingsID = $this->session->userdata('settingsID');
      $staffAliases = $this->_currentUserRecordAliases();
      $this->_generateRecurringInvoices($settingsID);
      $this->_markRecurringInvoiceGeneratorRun($settingsID);

      $result['data'] = $this->CashModel->invListStaff($settingsID, $staffAliases, $selectedCustomerId);
      $this->_attachInvoiceItemsToCollection($result['data'], $settingsID);
      $result['data1'] = $this->CashModel->joInvoiceNoStaff($settingsID, $staffAliases);
      $result['data2'] = $this->CashModel->getClients($settingsID);
      $result['selectedCustomerId'] = $selectedCustomerId;
      $this->load->view('inv_list', $result);
    }
  }

  function invoiceEntry()
  {
    if ($this->_is_client_user()) {
      redirect('Page/clientProfile?tab=invoices');
      return;
    }

    $settingsID = $this->session->userdata('settingsID');
    $id = (int) $this->input->get('id');
    $invoice = null;

    if ($id > 0) {
      if (!$this->_is_admin_user() && !$this->_is_staff_user()) {
        redirect('Page/invList');
        return;
      }

      $invoice = $this->CashModel->getInvoiceByOrderID($id, $settingsID);
      if (!$invoice || (string) ($invoice->invoiceSource ?? '') !== 'Others') {
        show_404();
        return;
      }
    }

    $result['record'] = $invoice;
    $result['invoiceItems'] = $invoice ? $this->_loadInvoiceItems($invoice, $settingsID) : array();
    $result['invoiceUnits'] = $this->SettingsModel->getInvoiceUnits($settingsID);
    $result['data2'] = $this->CashModel->getClients($settingsID);
    $result['isEditMode'] = $invoice !== null;
    $result['nextInvoiceNo'] = $invoice ? (string) $invoice->InvoiceNo : $this->_nextInvoiceNumber($settingsID);
    $result['formAction'] = base_url() . 'Page/' . ($invoice ? 'updateJO' : 'addInvoice');
    $result['backUrl'] = base_url() . 'Page/invList';
    $result['backLabel'] = 'Invoice List';
    $result['formReturnUrl'] = $invoice
      ? (base_url() . 'Page/invoiceEntry?id=' . rawurlencode((string) $invoice->orderID))
      : (base_url() . 'Page/invoiceEntry');

    $this->load->view('invoice_entry', $result);
  }

  function jobOrderEntry()
  {
    if ($this->_is_client_user()) {
      redirect('Page/clientProfile?tab=invoices');
      return;
    }

    $settingsID = $this->session->userdata('settingsID');
    $id = (int) $this->input->get('id');
    $invoice = null;

    if ($id > 0) {
      if (!$this->_is_admin_user()) {
        redirect('Page/joList');
        return;
      }

      $invoice = $this->CashModel->getInvoiceByOrderID($id, $settingsID);
      if (!$invoice || (string) ($invoice->invoiceSource ?? '') !== 'Job Order') {
        show_404();
        return;
      }
    }

    $this->_ensureServiceFeesTable();

    $result['record'] = $invoice;
    $result['data2'] = $this->CashModel->getClients($settingsID);
    $result['serviceFees'] = $this->CashModel->priceList($settingsID);
    $result['isEditMode'] = $invoice !== null;
    $result['nextInvoiceNo'] = $invoice ? (string) $invoice->InvoiceNo : $this->_nextInvoiceNumber($settingsID);
    $result['formAction'] = base_url() . 'Page/' . ($invoice ? 'updateJO' : 'addJO');
    $result['backUrl'] = base_url() . 'Page/joList';
    $result['backLabel'] = 'Job Order List';
    $result['formReturnUrl'] = $invoice
      ? (base_url() . 'Page/jobOrderEntry?id=' . rawurlencode((string) $invoice->orderID))
      : (base_url() . 'Page/jobOrderEntry');

    $this->load->view('job_order_entry', $result);
  }

  function unpaidInvoices()
  {

    $settingsID = $this->session->userdata('settingsID');
    $name = $this->session->userdata('name');
    $result['data'] = $this->CashModel->unpaidInvoices($settingsID);
    $result['data1'] = $this->CashModel->joInvoiceNo($settingsID);
    $this->load->view('invoices_unpaid', $result);
  }

  function addJO()
  {

    if ($this->input->method() === 'post') {
      $settingsID = $this->session->userdata('settingsID');
      $name = $this->_currentUserDisplayName();
      $openInvoice = trim((string) $this->input->post('open_invoice')) === '1';
      $redirectToInput = trim((string) $this->input->post('redirect_to'));
      $InvoiceNo = trim((string) $this->input->post('InvoiceNo'));
      $CustID = trim((string) $this->input->post('CustID'));
      list($CustID, $Customer, $CustAddress) = $this->_resolveCustomerContext(
        $settingsID,
        $CustID,
        $this->input->post('Customer'),
        $this->input->post('CustAddress')
      );
      $TotalDue = is_numeric($this->input->post('TotalDue')) ? (float) $this->input->post('TotalDue') : 0;
      $AmountPaid = is_numeric($this->input->post('AmountPaid')) ? (float) $this->input->post('AmountPaid') : 0;
      $Notes = trim((string) $this->input->post('Notes'));
      list($InvoiceItems, $JobDescription, $TotalDue, $PrimaryItem) = $this->_resolveInvoiceItems(
        $this->input->post('itemDescription'),
        $this->input->post('itemQuantity'),
        $this->input->post('itemDurationUnit'),
        $this->input->post('itemUnitPrice'),
        trim((string) $this->input->post('JobDescription')),
        $TotalDue,
        $settingsID
      );
      $ItemQuantity = $PrimaryItem['itemQuantity'] ?? null;
      $ItemDurationUnit = $PrimaryItem['itemDurationUnit'] ?? null;
      $ItemUnitPrice = $PrimaryItem['itemUnitPrice'] ?? null;

      date_default_timezone_set('Asia/Manila');
      $date = date('Y-m-d');
      if ($AmountPaid < 0) {
        $AmountPaid = 0;
      }
      if ($TotalDue < $AmountPaid) {
        $TotalDue = $AmountPaid;
      }
      $Balance = round($TotalDue - $AmountPaid, 2);

      $invoicePayload = array(
        'InvoiceNo' => $InvoiceNo,
        'CustID' => $CustID !== '' ? $CustID : null,
        'Customer' => $Customer,
        'CustAddress' => $CustAddress,
        'TransDate' => $date,
        'JobDescription' => $JobDescription,
        'itemQuantity' => $ItemQuantity,
        'itemDurationUnit' => $ItemDurationUnit,
        'itemUnitPrice' => $ItemUnitPrice,
        'TotalDue' => $TotalDue,
        'AmountPaid' => $AmountPaid,
        'Balance' => $Balance,
        'ReceiveDate' => $date,
        'Notes' => $Notes,
        'settingsID' => $settingsID,
        'invoiceStat' => 'active',
        'invoiceSource' => 'Job Order',
        'invoiceBy' => $name,
        'recurringFrequency' => 'none',
        'recurringScheduleDate' => null,
        'recurringTemplateID' => null,
        'lastRecurringGeneratedFor' => null,
      );

      $this->db->insert('invoice', $invoicePayload);
      $newOrderID = (int) $this->db->insert_id();
      $this->_persistInvoiceItems($newOrderID, $settingsID, $InvoiceItems);

      if ($AmountPaid > 0) {
        $paymentPayload = array(
          'InvoiceNo' => $InvoiceNo,
          'PDate' => $date,
          'AmountPaid' => $AmountPaid,
          'ORNo' => '',
          'PaymentReference' => '',
          'Cashier' => $name,
          'PaymentSource' => 'Job Order',
          'CustID' => $CustID !== '' ? $CustID : null,
          'Customer' => $Customer,
          'TransDescription' => $JobDescription,
          'ORStat' => 'Valid',
          'TerminalNo' => '',
          'settingsID' => $settingsID,
        );
        $this->db->insert('payments', $paymentPayload);
      }

      if ($openInvoice && $newOrderID > 0) {
        redirect('Page/invoice?id=' . rawurlencode((string) $newOrderID));
        return;
      }

      $allowedRedirects = array('joList', 'invList', 'unpaidInvoices');
      $redirectTo = in_array($redirectToInput, $allowedRedirects, true) ? $redirectToInput : 'joList';
      redirect('Page/' . $redirectTo);
    }
  }

  function addInvoice()
  {

    if ($this->input->method() === 'post') {
      $settingsID = $this->session->userdata('settingsID');
      $Encoder = $this->_currentUserDisplayName();
      $formReturnUrl = trim((string) $this->input->post('form_return_url'));
      $InvoiceNo = trim((string) $this->input->post('InvoiceNo'));
      $CustID = trim((string) $this->input->post('CustID'));
      list($CustID, $Customer, $CustAddress) = $this->_resolveCustomerContext(
        $settingsID,
        $CustID,
        $this->input->post('Customer'),
        $this->input->post('CustAddress')
      );
      $TotalDue = is_numeric($this->input->post('TotalDue')) ? (float) $this->input->post('TotalDue') : 0;
      $AmountPaid = is_numeric($this->input->post('AmountPaid')) ? (float) $this->input->post('AmountPaid') : 0;
      $Notes = trim((string) $this->input->post('Notes'));
      list($InvoiceItems, $JobDescription, $TotalDue, $PrimaryItem) = $this->_resolveInvoiceItems(
        $this->input->post('itemDescription'),
        $this->input->post('itemQuantity'),
        $this->input->post('itemDurationUnit'),
        $this->input->post('itemUnitPrice'),
        trim((string) $this->input->post('JobDescription')),
        $TotalDue,
        $settingsID
      );
      $ItemQuantity = $PrimaryItem['itemQuantity'] ?? null;
      $ItemDurationUnit = $PrimaryItem['itemDurationUnit'] ?? null;
      $ItemUnitPrice = $PrimaryItem['itemUnitPrice'] ?? null;
      $InvoiceDateInput = $this->_normalizeDateInput($this->input->post('TransDate'));
      $DueDateInput = $this->_normalizeDateInput($this->input->post('ReceiveDate'));
      $RecurringFrequency = $this->_normalizeRecurringFrequency($this->input->post('recurringFrequency'));
      $CoverageOption = $this->_normalizeCoverageOption($this->input->post('coverageOption'));
      $RecurringScheduleDate = $this->_normalizeDateInput($this->input->post('recurringScheduleDate'));
      $RecurringTerminationDate = $this->_normalizeDateInput($this->input->post('recurringTerminationDate'));
      $InvoiceExpirationDate = $this->_normalizeDateInput($this->input->post('invoiceExpirationDate'));
      if (trim((string) $this->input->post('isOpenDateInvoice')) === '1') {
        $InvoiceExpirationDate = null;
      }

      if (empty($InvoiceItems)) {
        $this->session->set_flashdata('danger', 'Add at least one invoice entry before saving.');
        redirect($formReturnUrl !== '' ? $formReturnUrl : 'Page/invoiceEntry');
        return;
      }

      // Check for duplicate invoice number
      if ($InvoiceNo !== '') {
        $existingInvoice = $this->db
          ->where('InvoiceNo', $InvoiceNo)
          ->where('settingsID', $settingsID)
          ->where('invoiceStat !=', 'Voided')
          ->get('invoice')
          ->row();
        if ($existingInvoice) {
          $this->session->set_flashdata('danger', 'Invoice number "' . $InvoiceNo . '" already exists. Please use a different invoice number.');
          redirect($formReturnUrl !== '' ? $formReturnUrl : 'Page/invoiceEntry');
          return;
        }
      }

      date_default_timezone_set('Asia/Manila');
      $date = date('Y-m-d');
      if ($AmountPaid < 0) {
        $AmountPaid = 0;
      }
      if ($TotalDue < $AmountPaid) {
        $TotalDue = $AmountPaid;
      }
      $invoiceDate = $InvoiceDateInput ?: $date;
      if ($RecurringFrequency !== 'none' && $RecurringScheduleDate === null) {
        $RecurringScheduleDate = $DueDateInput ?: $invoiceDate;
      }
      if ($RecurringFrequency === 'none') {
        $RecurringTerminationDate = null;
      } elseif ($RecurringTerminationDate !== null && $RecurringScheduleDate !== null && strtotime($RecurringTerminationDate) < strtotime($RecurringScheduleDate)) {
        $this->session->set_flashdata('danger', 'Recurring termination date cannot be earlier than the recurring schedule date.');
        redirect($formReturnUrl !== '' ? $formReturnUrl : 'Page/invoiceEntry');
        return;
      }
      $validityAnchorDate = $RecurringFrequency !== 'none'
        ? ($RecurringScheduleDate ?: $invoiceDate)
        : $invoiceDate;
      if ($InvoiceExpirationDate !== null && strtotime($InvoiceExpirationDate) < strtotime($validityAnchorDate)) {
        $this->session->set_flashdata('danger', 'Invoice expiration date cannot be earlier than the invoice start date.');
        redirect($formReturnUrl !== '' ? $formReturnUrl : 'Page/invoiceEntry');
        return;
      }
      $dueDate = $DueDateInput ?: ($RecurringFrequency !== 'none' ? ($RecurringScheduleDate ?: $invoiceDate) : $invoiceDate);
      $Balance = round($TotalDue - $AmountPaid, 2);

      $invoicePayload = array(
        'InvoiceNo' => $InvoiceNo,
        'CustID' => $CustID !== '' ? $CustID : null,
        'Customer' => $Customer,
        'CustAddress' => $CustAddress,
        'TransDate' => $invoiceDate,
        'JobDescription' => $JobDescription,
        'itemQuantity' => $ItemQuantity,
        'itemDurationUnit' => $ItemDurationUnit,
        'itemUnitPrice' => $ItemUnitPrice,
        'TotalDue' => $TotalDue,
        'AmountPaid' => $AmountPaid,
        'Balance' => $Balance,
        'ReceiveDate' => $dueDate,
        'Notes' => $Notes,
        'settingsID' => $settingsID,
        'invoiceStat' => 'active',
        'invoiceSource' => 'Others',
        'invoiceBy' => $Encoder,
        'recurringFrequency' => $RecurringFrequency,
        'coverageOption' => $RecurringFrequency !== 'none' ? $CoverageOption : null,
        'recurringScheduleDate' => $RecurringFrequency !== 'none' ? $RecurringScheduleDate : null,
        'recurringTerminationDate' => $RecurringFrequency !== 'none' ? $RecurringTerminationDate : null,
        'invoiceExpirationDate' => $InvoiceExpirationDate,
        'recurringTemplateID' => null,
        'lastRecurringGeneratedFor' => $RecurringFrequency !== 'none' ? $RecurringScheduleDate : null,
      );

      $this->db->insert('invoice', $invoicePayload);
      $newOrderID = (int) $this->db->insert_id();
      $this->_persistInvoiceItems($newOrderID, $settingsID, $InvoiceItems);

      if ($AmountPaid > 0) {
        $paymentPayload = array(
          'InvoiceNo' => $InvoiceNo,
          'PDate' => $date,
          'AmountPaid' => $AmountPaid,
          'ORNo' => '',
          'PaymentReference' => trim((string) $this->input->post('PaymentReference')),
          'Cashier' => $Encoder,
          'PaymentSource' => 'Invoice',
          'CustID' => $CustID !== '' ? $CustID : null,
          'Customer' => $Customer,
          'TransDescription' => $JobDescription,
          'ORStat' => 'Valid',
          'TerminalNo' => '',
          'settingsID' => $settingsID,
        );
        $this->db->insert('payments', $paymentPayload);
      }

      redirect('Page/invList');
    }
  }

  public function duplicateInvoice()
  {
    if ($this->_is_client_user()) {
      echo 'Access Denied';
      return;
    }

    $settingsID = (int) ($this->session->userdata('settingsID') ?? 0);
    $orderID = (int) $this->input->get('id');

    if ($orderID <= 0) {
      redirect('Page/invList');
      return;
    }

    $invoice = $this->CashModel->getInvoiceByOrderID($orderID, $settingsID);
    if (!$invoice || (string) ($invoice->invoiceSource ?? '') !== 'Others') {
      show_404();
      return;
    }

    if (!$this->_can_current_staff_access_invoice($invoice)) {
      echo 'Access Denied';
      return;
    }

    $this->_ensureInvoiceItemsTable();
    $invoiceItems = $this->_loadInvoiceItems($invoice, $settingsID);
    $newInvoiceNo = $this->_nextInvoiceNumber($settingsID);
    $encoder = $this->_currentUserDisplayName();
    $today = date('Y-m-d');
    $totalDue = round((float) ($invoice->TotalDue ?? 0), 2);

    $duplicatePayload = array(
      'InvoiceNo' => $newInvoiceNo,
      'CustID' => trim((string) ($invoice->CustID ?? '')) !== '' ? (string) $invoice->CustID : null,
      'Customer' => (string) ($invoice->Customer ?? ''),
      'CustAddress' => (string) ($invoice->CustAddress ?? ''),
      'TransDate' => $today,
      'JobDescription' => (string) ($invoice->JobDescription ?? ''),
      'itemQuantity' => isset($invoice->itemQuantity) ? $invoice->itemQuantity : null,
      'itemDurationUnit' => isset($invoice->itemDurationUnit) ? $invoice->itemDurationUnit : null,
      'itemUnitPrice' => isset($invoice->itemUnitPrice) ? $invoice->itemUnitPrice : null,
      'TotalDue' => $totalDue,
      'AmountPaid' => 0,
      'Balance' => $totalDue,
      'ReceiveDate' => $this->_normalizeDateInput($invoice->ReceiveDate ?? '') ?: $today,
      'Notes' => (string) ($invoice->Notes ?? ''),
      'settingsID' => $settingsID,
      'invoiceStat' => 'active',
      'invoiceSource' => 'Others',
      'invoiceBy' => $encoder,
      'recurringFrequency' => $this->_normalizeRecurringFrequency($invoice->recurringFrequency ?? 'none'),
      'coverageOption' => $this->_normalizeRecurringFrequency($invoice->recurringFrequency ?? 'none') !== 'none'
        ? $this->_normalizeCoverageOption($invoice->coverageOption ?? 'coming')
        : null,
      'recurringScheduleDate' => $this->_normalizeDateInput($invoice->recurringScheduleDate ?? ''),
      'recurringTerminationDate' => $this->_normalizeDateInput($invoice->recurringTerminationDate ?? ''),
      'invoiceExpirationDate' => $this->_normalizeDateInput($invoice->invoiceExpirationDate ?? ''),
      'recurringTemplateID' => null,
      'lastRecurringGeneratedFor' => $this->_normalizeDateInput($invoice->lastRecurringGeneratedFor ?? ''),
    );

    $this->db->insert('invoice', $duplicatePayload);
    $newOrderID = (int) $this->db->insert_id();

    if ($newOrderID <= 0) {
      $this->session->set_flashdata('danger', 'Invoice could not be duplicated. Please try again.');
      redirect('Page/invList');
      return;
    }

    $this->_persistInvoiceItems($newOrderID, $settingsID, $invoiceItems);

    $this->session->set_flashdata('success', 'Invoice duplicated successfully. Review the new copy before sending it out.');
    redirect('Page/invoiceEntry?id=' . rawurlencode((string) $newOrderID));
  }

  function updateJO()
  {
    $settingsID = $this->session->userdata('settingsID');
    $requestMethod = strtolower((string) $this->input->method());
    $id = (int) ($requestMethod === 'post' ? $this->input->post('id') : $this->input->get('id'));
    $returnToInput = trim((string) ($requestMethod === 'post' ? $this->input->post('return_to') : $this->input->get('return_to')));

    $invoice = $id > 0 ? $this->CashModel->getInvoiceByOrderID($id, $settingsID) : null;
    $inferredReturnTo = ($invoice && isset($invoice->invoiceSource) && $invoice->invoiceSource === 'Others') ? 'invList' : 'joList';
    $returnTo = in_array($returnToInput, array('invList', 'joList'), true) ? $returnToInput : $inferredReturnTo;

    if (!$invoice) {
      redirect('Page/' . $returnTo);
      return;
    }

    if ($requestMethod === 'post' && !$this->_is_admin_user() && !$this->_is_staff_user()) {
      redirect('Page/' . $returnTo);
      return;
    }

    if ($requestMethod !== 'post' && $returnTo === 'invList' && (string) ($invoice->invoiceSource ?? '') === 'Others') {
      redirect('Page/invoiceEntry?id=' . rawurlencode((string) $invoice->orderID));
      return;
    }

    if ($requestMethod !== 'post' && (string) ($invoice->invoiceSource ?? '') === 'Job Order') {
      redirect('Page/jobOrderEntry?id=' . rawurlencode((string) $invoice->orderID));
      return;
    }

    if ($requestMethod === 'post') {
      $oldCustomer = (string) $invoice->Customer;
      $oldCustID = trim((string) $invoice->CustID);
      $formReturnUrl = trim((string) $this->input->post('form_return_url'));
      $CustID = trim((string) $this->input->post('CustID'));
      list($CustID, $Customer, $CustAddress) = $this->_resolveCustomerContext(
        $settingsID,
        $CustID,
        $this->input->post('Customer'),
        $this->input->post('CustAddress')
      );
      $Notes = trim((string) $this->input->post('Notes'));
      $TotalDue = is_numeric($this->input->post('TotalDue')) ? (float) $this->input->post('TotalDue') : (float) $invoice->TotalDue;
      $AmountPaid = (float) $invoice->AmountPaid;
      list($InvoiceItems, $JobDescription, $TotalDue, $PrimaryItem) = $this->_resolveInvoiceItems(
        $this->input->post('itemDescription'),
        $this->input->post('itemQuantity'),
        $this->input->post('itemDurationUnit'),
        $this->input->post('itemUnitPrice'),
        trim((string) $this->input->post('JobDescription')),
        $TotalDue,
        $settingsID,
        $invoice
      );
      $ItemQuantity = $PrimaryItem['itemQuantity'] ?? null;
      $ItemDurationUnit = $PrimaryItem['itemDurationUnit'] ?? null;
      $ItemUnitPrice = $PrimaryItem['itemUnitPrice'] ?? null;
      $InvoiceDateInput = $this->_normalizeDateInput($this->input->post('TransDate'));
      $DueDateInput = $this->_normalizeDateInput($this->input->post('ReceiveDate'));
      $hasRecurringTerminationDateInput = array_key_exists('recurringTerminationDate', $_POST);
      $hasInvoiceExpirationDateInput = array_key_exists('invoiceExpirationDate', $_POST);
      $RecurringFrequency = $invoice->invoiceSource === 'Others'
        ? $this->_normalizeRecurringFrequency($this->input->post('recurringFrequency'))
        : 'none';
      $CoverageOption = $invoice->invoiceSource === 'Others'
        ? $this->_normalizeCoverageOption($this->input->post('coverageOption'))
        : 'coming';
      $RecurringScheduleDate = $invoice->invoiceSource === 'Others'
        ? $this->_normalizeDateInput($this->input->post('recurringScheduleDate'))
        : null;
      $RecurringTerminationDate = $invoice->invoiceSource === 'Others'
        ? ($hasRecurringTerminationDateInput
          ? $this->_normalizeDateInput($this->input->post('recurringTerminationDate'))
          : $this->_normalizeDateInput($invoice->recurringTerminationDate ?? ''))
        : null;
      $InvoiceExpirationDate = $invoice->invoiceSource === 'Others'
        ? ($hasInvoiceExpirationDateInput
          ? $this->_normalizeDateInput($this->input->post('invoiceExpirationDate'))
          : $this->_normalizeDateInput($invoice->invoiceExpirationDate ?? ''))
        : null;
      if (trim((string) $this->input->post('isOpenDateInvoice')) === '1') {
        $InvoiceExpirationDate = null;
      }

      if ($Customer === '') {
        $Customer = $oldCustomer;
      }

      if ($JobDescription === '') {
        $JobDescription = (string) $invoice->JobDescription;
      }

      if (empty($InvoiceItems)) {
        $this->session->set_flashdata('danger', 'Add at least one invoice entry before saving.');
        redirect($formReturnUrl !== '' ? $formReturnUrl : ('Page/invoiceEntry?id=' . rawurlencode((string) $invoice->orderID)));
        return;
      }

      if ($TotalDue < $AmountPaid) {
        $this->session->set_flashdata('danger', 'Invoice total cannot be lower than the amount already paid.');
        redirect($formReturnUrl !== '' ? $formReturnUrl : ('Page/invoiceEntry?id=' . rawurlencode((string) $invoice->orderID)));
        return;
      }

      if ((int) $invoice->recurringTemplateID > 0) {
        $RecurringFrequency = (string) $invoice->recurringFrequency;
        $RecurringScheduleDate = $invoice->recurringScheduleDate ?: null;
        $RecurringTerminationDate = $invoice->recurringTerminationDate ?: null;
      } elseif ($RecurringFrequency !== 'none' && $RecurringScheduleDate === null) {
        $RecurringScheduleDate = $this->_normalizeDateInput($invoice->recurringScheduleDate) ?: $this->_normalizeDateInput($invoice->TransDate);
      } elseif ($RecurringFrequency === 'none') {
        $RecurringScheduleDate = null;
        $RecurringTerminationDate = null;
      }

      if ($RecurringFrequency !== 'none' && $RecurringTerminationDate !== null && $RecurringScheduleDate !== null && strtotime($RecurringTerminationDate) < strtotime($RecurringScheduleDate)) {
        $this->session->set_flashdata('danger', 'Recurring termination date cannot be earlier than the recurring schedule date.');
        redirect($formReturnUrl !== '' ? $formReturnUrl : ('Page/invoiceEntry?id=' . rawurlencode((string) $invoice->orderID)));
        return;
      }

      $InvoiceDate = $InvoiceDateInput
        ?: $this->_normalizeDateInput($invoice->TransDate)
        ?: date('Y-m-d');
      $DueDate = $DueDateInput ?: $this->_normalizeDateInput($invoice->ReceiveDate);
      if ($DueDate === null && $RecurringFrequency !== 'none' && $RecurringScheduleDate !== null) {
        $DueDate = $RecurringScheduleDate;
      }
      if ($DueDate === null) {
        $DueDate = $InvoiceDate;
      }
      $validityAnchorDate = $RecurringFrequency !== 'none'
        ? ($RecurringScheduleDate ?: $InvoiceDate)
        : $InvoiceDate;
      if ($InvoiceExpirationDate !== null && strtotime($InvoiceExpirationDate) < strtotime($validityAnchorDate)) {
        $this->session->set_flashdata('danger', 'Invoice expiration date cannot be earlier than the invoice start date.');
        redirect($formReturnUrl !== '' ? $formReturnUrl : ('Page/invoiceEntry?id=' . rawurlencode((string) $invoice->orderID)));
        return;
      }

      $Balance = round($TotalDue - $AmountPaid, 2);

      // Check for duplicate invoice number (exclude current invoice)
      $newInvoiceNo = trim((string) $this->input->post('InvoiceNo'));
      if ($newInvoiceNo !== '' && $newInvoiceNo !== $invoice->InvoiceNo) {
        $existingInvoice = $this->db
          ->where('InvoiceNo', $newInvoiceNo)
          ->where('settingsID', $settingsID)
          ->where('orderID !=', $invoice->orderID)
          ->where('invoiceStat !=', 'Voided')
          ->get('invoice')
          ->row();
        if ($existingInvoice) {
          $this->session->set_flashdata('danger', 'Invoice number "' . $newInvoiceNo . '" already exists. Please use a different invoice number.');
          redirect($formReturnUrl !== '' ? $formReturnUrl : ('Page/invoiceEntry?id=' . rawurlencode((string) $invoice->orderID)));
          return;
        }
      }

      $this->db->where('orderID', $invoice->orderID);
      $this->db->where('settingsID', $settingsID);
      $invoiceUpdate = array(
        'CustID' => $CustID !== '' ? $CustID : null,
        'Customer' => $Customer,
        'CustAddress' => $CustAddress,
        'TransDate' => $InvoiceDate,
        'JobDescription' => $JobDescription,
        'itemQuantity' => $ItemQuantity,
        'itemDurationUnit' => $ItemDurationUnit,
        'itemUnitPrice' => $ItemUnitPrice,
        'TotalDue' => $TotalDue,
        'Balance' => $Balance,
        'ReceiveDate' => $DueDate,
        'Notes' => $Notes,
        'invoiceExpirationDate' => $InvoiceExpirationDate,
        'InvoiceNo' => trim((string) $this->input->post('InvoiceNo')),
      );

      if ((int) $invoice->recurringTemplateID <= 0) {
        $existingRecurringFrequency = $this->_normalizeRecurringFrequency($invoice->recurringFrequency ?? 'none');
        $existingRecurringScheduleDate = $this->_normalizeDateInput($invoice->recurringScheduleDate)
          ?: $this->_normalizeDateInput($invoice->TransDate);
        $storedRecurringAnchor = $this->_normalizeDateInput($invoice->lastRecurringGeneratedFor);
        $recurringAnchorChanged = ($RecurringFrequency !== $existingRecurringFrequency)
          || ($RecurringScheduleDate !== $existingRecurringScheduleDate);

        $invoiceUpdate['recurringFrequency'] = $RecurringFrequency;
        $invoiceUpdate['coverageOption'] = $RecurringFrequency !== 'none' ? $CoverageOption : null;
        $invoiceUpdate['recurringScheduleDate'] = $RecurringScheduleDate;
        $invoiceUpdate['recurringTerminationDate'] = $RecurringFrequency !== 'none' ? $RecurringTerminationDate : null;
        if ($RecurringFrequency === 'none') {
          $invoiceUpdate['lastRecurringGeneratedFor'] = null;
        } elseif (
          $recurringAnchorChanged
          || $storedRecurringAnchor === null
          || ($RecurringScheduleDate !== null && strtotime($storedRecurringAnchor) < strtotime($RecurringScheduleDate))
        ) {
          $invoiceUpdate['lastRecurringGeneratedFor'] = $RecurringScheduleDate;
        } else {
          $invoiceUpdate['lastRecurringGeneratedFor'] = $storedRecurringAnchor;
        }
      }

      $this->db->update('invoice', $invoiceUpdate);
      $this->_persistInvoiceItems((int) $invoice->orderID, $settingsID, $InvoiceItems);

      if ($Customer !== $oldCustomer || $CustID !== $oldCustID) {
        $this->db->where('InvoiceNo', $invoice->InvoiceNo);
        $this->db->where('settingsID', $settingsID);
        $this->db->update('payments', array(
          'CustID' => $CustID !== '' ? $CustID : null,
          'Customer' => $Customer,
        ));
      }

      redirect('Page/' . $returnTo);
      return;
    }

    $result['record'] = $invoice;
    $result['data'] = array($invoice);
    $result['data2'] = $this->CashModel->getClients($settingsID);
    if ((string) ($invoice->invoiceSource ?? '') !== 'Others') {
      $this->_ensureServiceFeesTable();
      $result['serviceFees'] = $this->CashModel->priceList($settingsID);
    } else {
      $result['serviceFees'] = array();
    }
    $result['returnTo'] = $returnTo;
    $result['pageLabel'] = $returnTo === 'invList' ? 'Invoice' : 'Job Order';
    $result['backUrl'] = base_url() . 'Page/' . $returnTo;
    $result['backLabel'] = $returnTo === 'invList' ? 'Invoice List' : 'Job Order List';
    $this->load->view('update_jo', $result);
  }


  function paymentList()
  {

    $settingsID = $this->session->userdata('settingsID');
    $level = $this->session->userdata('level');
    $name = $this->_currentUserDisplayName();

    date_default_timezone_set('Asia/Manila');
    $today = date('Y-m-d');

    $dateFromInput = trim((string) $this->input->get('date_from'));
    $dateToInput = trim((string) $this->input->get('date_to'));
    $selectedMonth = trim((string) $this->input->get('filter_month'));
    $selectedYear = trim((string) $this->input->get('filter_year'));

    $dateFrom = $this->_normalizeDateInput($dateFromInput);
    $dateTo = $this->_normalizeDateInput($dateToInput);

    if ($dateFrom === null && $dateTo === null) {
      $validMonth = ctype_digit($selectedMonth) && (int) $selectedMonth >= 1 && (int) $selectedMonth <= 12;
      $currentYear = (int) date('Y');
      $validYear = ctype_digit($selectedYear) && (int) $selectedYear >= 2000 && (int) $selectedYear <= ($currentYear + 1);
      if ($validMonth && $validYear) {
        $dateFrom = sprintf('%04d-%02d-01', (int) $selectedYear, (int) $selectedMonth);
        $dateTo = date('Y-m-t', strtotime($dateFrom));
      }
    }

    if ($dateFrom === null && $dateTo !== null) {
      $dateFrom = $dateTo;
    } elseif ($dateTo === null && $dateFrom !== null) {
      $dateTo = $dateFrom;
    }

    if ($dateFrom === null || $dateTo === null) {
      $dateFrom = $today;
      $dateTo = $today;
    }

    if (strtotime($dateFrom) > strtotime($dateTo)) {
      $swap = $dateFrom;
      $dateFrom = $dateTo;
      $dateTo = $swap;
    }

    $result['data'] = array();
    $result['data1'] = array();
    $result['data2'] = $this->CashModel->getClients($settingsID);
    $result['filterDateFrom'] = $dateFrom;
    $result['filterDateTo'] = $dateTo;
    $result['currentDayDate'] = $today;
    $result['showingTodayOnly'] = ($dateFrom === $today && $dateTo === $today);
    $result['todayTotal'] = array();
    $result['filteredTotal'] = array();

    if ($level === 'Admin') {
      $result['data'] = $this->CashModel->paymentListRange($dateFrom, $dateTo, $settingsID);
      $result['data1'] = $result['data'];
      $result['filteredTotal'] = $this->CashModel->totalPaymentsRange($dateFrom, $dateTo, $settingsID);
      $result['todayTotal'] = $this->CashModel->todaysPayments($settingsID, $today);
    } else {
      $result['data'] = $this->CashModel->paymentListRangeStaff($settingsID, $name, $dateFrom, $dateTo);
      $result['data1'] = $result['data'];
      $result['filteredTotal'] = $this->CashModel->totalPaymentsRangeStaff($settingsID, $name, $dateFrom, $dateTo);
      $result['todayTotal'] = $this->CashModel->todaysPaymentsStaff($settingsID, $name);
    }

    $this->load->view('payment_list', $result);
  }

  private function _normalizeDateInput($value)
  {
    $value = trim((string) $value);

    if ($value === '') {
      return null;
    }

    $date = DateTime::createFromFormat('Y-m-d', $value);
    if ($date && $date->format('Y-m-d') === $value) {
      return $value;
    }

    return null;
  }

  private function _normalizeTaskLabel($value)
  {
    $value = preg_replace('/\s+/', ' ', trim((string) $value));

    if ($value === '') {
      return '';
    }

    if (function_exists('mb_strtoupper')) {
      return mb_strtoupper($value, 'UTF-8');
    }

    return strtoupper($value);
  }
  function employeeAccomplishment()
  {
    $settingsID = $this->session->userdata('settingsID');
    // Source staff list from employee table joined to users on email field.
    // Only include employees that have a matching users record (so a valid user_id exists).
    $employees = $this->CashModel->employeeList($settingsID);
    $result['data2'] = array_values(array_filter((array) $employees, function ($row) {
      return isset($row->user_id) && $row->user_id !== null && $row->user_id !== '';
    }));
    $this->load->view('employee_task_accomplishement', $result);
  }
  function employeeAccomplishmentData()
  {
    $settingsID = $this->session->userdata('settingsID');
    $name = $this->input->get('user_id');
    $reportPeriod = trim((string) $this->input->get('report_period'));
    $endDateRaw = trim((string) $this->input->get('end_date'));
    $userLevel = (string) $this->session->userdata('level');

    $selectedMonth = null;
    $selectedYear = null;
    $selectedEndDate = null;
    if ($reportPeriod !== '') {
      $parts = explode('-', $reportPeriod);
      if (count($parts) === 2) {
        $selectedYear = is_numeric($parts[0]) ? (int) $parts[0] : null;
        $selectedMonth = is_numeric($parts[1]) ? (int) $parts[1] : null;
        if ($selectedMonth !== null && ($selectedMonth < 1 || $selectedMonth > 12)) {
          $selectedMonth = null;
        }
        if ($selectedYear !== null && $selectedYear < 2000) {
          $selectedYear = null;
        }
      }
    }

    if ($endDateRaw !== '' && preg_match('/^\\d{4}-\\d{2}-\\d{2}$/', $endDateRaw)) {
      $selectedEndDate = $endDateRaw;
    }

    if ($userLevel === 'Admin' && $selectedMonth && $selectedYear) {
      $result['data'] = $this->CashModel->accomplishmentsStaffFiltered($settingsID, $name, $selectedMonth, $selectedYear, $selectedEndDate);
    } elseif ($userLevel === 'Admin') {
      // Admin with "All months" selected - show all accomplishments up to end_date (if provided)
      $result['data'] = $this->CashModel->accomplishmentsStaffFiltered($settingsID, $name, null, null, $selectedEndDate);
    } else {
      $result['data'] = $this->CashModel->accomplishmentsStaff($settingsID, $name);
      $selectedMonth = null;
      $selectedYear = null;
      $selectedEndDate = null;
    }

    $result['employee'] = $this->CashModel->getUserFlexible($settingsID, $name);
    $result['projects'] = $this->CashModel->getProjectName($settingsID);
    $result['pointsEnabled'] = $this->db->field_exists('points', 'projects_task_stat');
    $result['selectedUserId'] = $name;
    $result['selectedMonth'] = $selectedMonth;
    $result['selectedYear'] = $selectedYear;
    $result['selectedEndDate'] = $selectedEndDate;
    $this->load->view('accomplishments_per_employee', $result);
  }

  function accomplishmentStaff()
  {
    if ($this->session->userdata('level') === 'Admin') {
      $settingsID = $this->session->userdata('settingsID');
      $name = $this->input->get('assignedPerson');
      $date = $this->input->get('date');

      $result['data'] = $this->CashModel->accomplishmentsStaffbyDate($settingsID, $name, $date);
      $result['employee'] = $this->CashModel->getUserFlexible($settingsID, $name);
      $result['projects'] = $this->CashModel->getProjectName($settingsID);
      $result['pointsEnabled'] = $this->db->field_exists('points', 'projects_task_stat');
      $result['selectedUserId'] = $name;
      $result['selectedDate'] = $date;
      $this->load->view('accomplishments_per_employee', $result);
    } else {
      $settingsID = $this->session->userdata('settingsID');

      $name = $this->input->get('assignedPerson');
      $date = $this->input->get('date');

      $result['data'] = $this->CashModel->accomplishmentsStaffbyDate($settingsID, $name, $date);
      $result['employee'] = $this->CashModel->getUserFlexible($settingsID, $name);
      $result['projects'] = $this->CashModel->getProjectName($settingsID);
      $result['pointsEnabled'] = $this->db->field_exists('points', 'projects_task_stat');
      $result['selectedUserId'] = $name;
      $result['selectedDate'] = $date;
      $this->load->view('accomplishments_per_employee', $result);
    }
  }

  public function todayAccomplishments()
  {
    if ($this->session->userdata('level') !== 'Admin') {
      echo 'Access Denied';
      return;
    }

    $settingsID = $this->session->userdata('settingsID');
    $dateParam = $this->input->get('date');
    $today = $dateParam ?: date('Y-m-d');

    $result['selectedDate'] = $today;
    $result['accomplishments'] = $this->CashModel->accomplishmentsByDateAll($settingsID, $today);

    if (!empty($result['accomplishments'])) {
      $firstAcc = $result['accomplishments'][0];
      $identifier = !empty($firstAcc->user_id) ? $firstAcc->user_id : (!empty($firstAcc->username) ? $firstAcc->username : '');
      if ($identifier !== '') {
        $result['employee'] = $this->CashModel->getUserFlexible($settingsID, $identifier);
      }
    }

    $this->load->view('accomplishments_today', $result);
  }

  public function employeeTask()
  {
    if (!$this->_is_admin_user()) {
      redirect('Page/projectAddTask');
      return;
    }

    $settingsID = $this->session->userdata('settingsID');

    $openUserId = (int) $this->input->get('open_user_id');

    // Get filter parameter: 'all', 'with_tasks', 'without_tasks'
    $taskFilter = strtolower(trim((string) $this->input->get('task_filter')));
    if (!in_array($taskFilter, ['all', 'with_tasks', 'without_tasks'], true)) {
      $taskFilter = 'all';
    }

    // Fetch from employee table with user_id join
    $employees = $this->CashModel->employeeList($settingsID);

    if (!empty($employees)) {
      foreach ($employees as $emp) {
        // Use user_id to match with tasks (assignedPerson in projects_task)
        $userId = (int) ($emp->user_id ?? 0);
        $emp->pending_tasks = $userId > 0 ? $this->CashModel->getPendingTasksByEmployee($userId, $settingsID) : [];
        $emp->pending_count = count($emp->pending_tasks);
      }

      // Filter employees based on task_filter
      if ($taskFilter === 'with_tasks') {
        $employees = array_filter($employees, function ($emp) {
          return ($emp->pending_count ?? 0) > 0;
        });
        $employees = array_values($employees); // Reindex array
      } elseif ($taskFilter === 'without_tasks') {
        $employees = array_filter($employees, function ($emp) {
          return ($emp->pending_count ?? 0) === 0;
        });
        $employees = array_values($employees); // Reindex array
      }
    }

    $result['employees'] = $employees;
    $result['projectOptions'] = $this->CashModel->getProjectName($settingsID);
    $result['staffOptions'] = $this->CashModel->employeeList($settingsID);
    $result['isAdmin'] = $this->session->userdata('level') === 'Admin';
    $result['open_user_id'] = $openUserId;
    $result['taskFilter'] = $taskFilter;
    $result['currentUserId'] = $this->session->userdata('user_id');
    $result['currentUserName'] = $this->session->userdata('fName') . ' ' . $this->session->userdata('lName');
    $this->load->view('employee_task_all', $result);
  }

  function collectionsEmployee()
  {

    $settingsID = $this->session->userdata('settingsID');
    $name = $this->input->get('name');

    $result['data'] = $this->CashModel->paymentListRangeStaff($settingsID, $name);
    $result['data1'] = $this->CashModel->totalPaymentsRangeStaff($settingsID, $name);
    $result['data2'] = $this->CashModel->totalPaymentsRangeStaffDate($settingsID, $name);
    $this->load->view('collections_per_employee', $result);
  }

  function paymentRangeData()
  {
    if ($this->session->userdata('level') === 'Admin') {
      if ($this->_is_admin_user()) {
        $settingsID = $this->session->userdata('settingsID');
        $user_id = $this->input->get('user_id');

        $result['openTaskCount'] = $this->CashModel->countOpenTasksStaff($settingsID, $user_id);
        $result['closedTaskCount'] = $this->CashModel->countClosedTasksStaff($settingsID, $user_id);

        $result['data'] = $this->CashModel->taskListStaff($settingsID, $user_id);
        $result['data1'] = $this->CashModel->getProjectName($settingsID);
        $result['data2'] = $this->CashModel->getStaff($settingsID);
        $result['selectedUserId'] = $user_id;

        $this->load->view('project_list_task', $result);
      } else {
        echo "Access Denied";
      }
    }
  }

  function employeeTaskData2()
  {
    if ($this->_is_admin_user()) {
      $settingsID = $this->session->userdata('settingsID');
      $user_id = $this->input->get('name');

      $result['openTaskCount'] = $this->CashModel->countOpenTasksStaff($settingsID, $user_id);
      $result['closedTaskCount'] = $this->CashModel->countClosedTasksStaff($settingsID, $user_id);

      $result['data'] = $this->CashModel->taskListStaff($settingsID, $user_id);
      $result['data1'] = $this->CashModel->getProjectName($settingsID);
      $result['data2'] = $this->CashModel->getStaff($settingsID);
      $result['selectedUserId'] = $user_id;

      $this->load->view('project_list_task', $result);
    } else {
      echo "Access Denied";
    }
  }

  function paymentRange()
  {
    $this->load->view('payment_list_range');
  }

  function paymentRangeDataPost()
  {
    if ($this->session->userdata('level') === 'Admin') {
      $settingsID = $this->session->userdata('settingsID');
      $from = $this->input->post('from');
      $to = $this->input->post('to');
      $result['data'] = $this->CashModel->paymentListRange($from, $to, $settingsID);
      $result['data1'] = $this->CashModel->totalPaymentsRange($from, $to, $settingsID);
      $result['data2'] = $this->CashModel->totalPaymentsRangeDate1($from, $to, $settingsID);
      $result['data3'] = $this->CashModel->totalExpensesRange($from, $to, $settingsID);
      $result['data4'] = $this->CashModel->totalPaymentsPerCashier($from, $to, $settingsID);
      $this->load->view('payment_list_range_data', $result);
    } else {
      $settingsID = $this->session->userdata('settingsID');
      $name = $this->session->userdata('name');

      $result['data'] = $this->CashModel->paymentListRangeStaff($settingsID, $name);
      $result['data1'] = $this->CashModel->totalPaymentsRangeStaff($settingsID, $name);
      $result['data2'] = $this->CashModel->totalPaymentsRangeStaffDate($settingsID, $name);
      $this->load->view('payment_list_range_data', $result);
    }
  }

  function todaysCollection()
  {
    if ($this->session->userdata('level') === 'Admin') {
      $settingsID = $this->session->userdata('settingsID');

      date_default_timezone_set('Asia/Manila'); # add your city to set local time zone
      $from = date("Y-m-d");
      $to = date("Y-m-d");

      $result['data'] = $this->CashModel->paymentListRange($from, $to, $settingsID);
      $result['data1'] = $this->CashModel->totalPaymentsRange($from, $to, $settingsID);
      $result['data2'] = $this->CashModel->totalPaymentsRangeDate1($from, $to, $settingsID);
      $result['data3'] = $this->CashModel->totalExpensesRange($from, $to, $settingsID);
      $result['data4'] = $this->CashModel->totalPaymentsPerCashier($from, $to, $settingsID);
      $this->load->view('payment_list_range_data', $result);
    } else {
      $settingsID = $this->session->userdata('settingsID');
      $name = $this->session->userdata('name');

      $result['data'] = $this->CashModel->paymentListRangeStaff($settingsID, $name);
      $result['data1'] = $this->CashModel->totalPaymentsRangeStaff($settingsID, $name);
      $result['data2'] = $this->CashModel->totalPaymentsRangeStaffDate($settingsID, $name);
      $this->load->view('payment_list_range_data', $result);
    }
  }


  function expensesRange()
  {
    $this->load->view('expenses_list_range');
  }

  function expensesRangeData()
  {

    if ($this->session->userdata('level') === 'Admin') {
      $settingsID = $this->session->userdata('settingsID');

      $from = $this->input->post('from');
      $to = $this->input->post('to');
      $result['data'] = $this->CashModel->expensesListRange($from, $to, $settingsID);
      $result['data1'] = $this->CashModel->totalExpensesRange($from, $to, $settingsID);
      $this->load->view('expenses_list_range_data', $result);
    } else {
      $settingsID = $this->session->userdata('settingsID');
      $name = $this->session->userdata('name');

      $from = $this->input->post('from');
      $to = $this->input->post('to');
      $result['data'] = $this->CashModel->expensesListRangeStaff($from, $to, $settingsID, $name);
      $result['data1'] = $this->CashModel->totalExpensesRangeStaff($from, $to, $settingsID, $name);
      $this->load->view('expenses_list_range_data', $result);
    }
  }

  function todaysExpenses()
  {

    if ($this->session->userdata('level') === 'Admin') {
      $settingsID = $this->session->userdata('settingsID');

      date_default_timezone_set('Asia/Manila'); # add your city to set local time zone
      $from = date("Y-m-d");
      $to = date("Y-m-d");
      $result['data'] = $this->CashModel->expensesListRange($from, $to, $settingsID);
      $result['data1'] = $this->CashModel->totalExpensesRange($from, $to, $settingsID);
      $this->load->view('expenses_list_range_data', $result);
    } else {
      $settingsID = $this->session->userdata('settingsID');
      $name = $this->session->userdata('name');

      $from = $this->input->post('from');
      $to = $this->input->post('to');
      $result['data'] = $this->CashModel->expensesListRangeStaff($from, $to, $settingsID, $name);
      $result['data1'] = $this->CashModel->totalExpensesRangeStaff($from, $to, $settingsID, $name);
      $this->load->view('expenses_list_range_data', $result);
    }
  }


  function paymentListYear()
  {
    $this->load->view('payment_list_year');
    if ($this->input->method() === 'post') {
      $year = $this->input->post('year');
      $result['data'] = $this->CashModel->paymentListYear($year);
      $result['data1'] = $this->CashModel->totalPaymentsYear($year);
      $this->load->view('payment_list_year', $result);
    }
  }

  function addPayment()
  {
    $settingsID = $this->session->userdata('settingsID');
    $name = $this->_currentUserDisplayName();
    $InvoiceNo = trim((string) $this->input->post('InvoiceNo'));
    $AmountPaid = $this->_normalizePaymentAmount($this->input->post('AmountPaid'));
    $TaxAmount = $this->_normalizePaymentAmount($this->input->post('TaxAmount'));
    $ORNo = trim((string) $this->input->post('ORNo'));
    $CustID = trim((string) $this->input->post('CustID'));
    list($CustID, $Customer) = $this->_resolveCustomerContext(
      $settingsID,
      $CustID,
      $this->input->post('Customer')
    );
    $TransDescription = trim((string) $this->input->post('TransDescription'));
    $PaymentReference = trim((string) $this->input->post('PaymentReference'));

    date_default_timezone_set('Asia/Manila');
    $postedDate = trim((string) $this->input->post('PDate'));
    $date = $this->_normalizeDateInput($postedDate) ?: date("Y-m-d");
    $paymentPayload = array(
      'InvoiceNo' => $InvoiceNo,
      'PDate' => $date,
      'AmountPaid' => $AmountPaid,
      'TaxAmount' => $TaxAmount,
      'ORNo' => $ORNo,
      'PaymentReference' => $PaymentReference,
      'Cashier' => $name,
      'PaymentSource' => '',
      'CustID' => $CustID !== '' ? $CustID : null,
      'Customer' => $Customer,
      'TransDescription' => $TransDescription,
      'ORStat' => 'Valid',
      'TerminalNo' => '',
      'settingsID' => $settingsID,
    );
    $this->db->insert('payments', $paymentPayload);
    if ($InvoiceNo !== '') {
      $this->_syncInvoicePaymentTotals($settingsID, $InvoiceNo);
    }
    redirect('Page/paymentList');
  }

  function addPaymentJO()
  {
    $id = trim((string) $this->input->get('id'));
    $InvoiceNo = trim((string) $this->input->get('InvoiceNo'));
    $source = trim((string) $this->input->get('PaymentSource'));
    $returnToInput = trim((string) $this->input->get('return_to'));
    $settingsID = $this->session->userdata('settingsID');
    $invoice = $this->_findInvoiceRecord($settingsID, $id, $InvoiceNo);

    if (!$invoice) {
      show_404();
      return;
    }

    if (!$this->_can_current_staff_access_invoice($invoice)) {
      show_404();
      return;
    }

    if ($InvoiceNo === '') {
      $InvoiceNo = (string) $invoice->InvoiceNo;
    }

    $this->_syncInvoicePaymentTotals($settingsID, $InvoiceNo, (int) $invoice->orderID);
    $invoice = $this->_findInvoiceRecord($settingsID, (string) $invoice->orderID, $InvoiceNo);
    if (!$invoice) {
      show_404();
      return;
    }

    if (!$this->_can_current_staff_access_invoice($invoice)) {
      show_404();
      return;
    }

    if ($source === '') {
      $source = (string) $invoice->invoiceSource;
    }

    $defaultReturnTo = $source === 'Job Order' ? 'joList' : 'invList';
    $allowedReturnTargets = array('invList', 'joList', 'paymentList');
    $returnTo = in_array($returnToInput, $allowedReturnTargets, true) ? $returnToInput : $defaultReturnTo;

    $result['data'] = array($invoice);
    $result['data1'] = $this->CashModel->joInvoiceNoPaymentTotal($InvoiceNo, $settingsID, (string) $invoice->CustID, $invoice->Customer);
    $result['paymentSource'] = $source;
    $result['returnTo'] = $returnTo;
    $this->load->view('add_payment_jo', $result);

    if ($this->input->method() === 'post') {
      $settingsID = $this->session->userdata('settingsID');
      $name = $this->_currentUserDisplayName();

      $orderID = trim((string) $this->input->post('orderID'));
      $InvoiceNo = trim((string) $this->input->post('InvoiceNo'));
      $PDate = $this->_normalizeDateInput($this->input->post('PDate')) ?: date('Y-m-d');
      $AmountPaid = $this->_normalizePaymentAmount($this->input->post('AmountPaid'));
      $TaxAmount = $this->_normalizePaymentAmount($this->input->post('TaxAmount'));
      $ORNo = trim((string) $this->input->post('ORNo'));
      $PaymentReference = trim((string) $this->input->post('PaymentReference'));
      $TransDescription = trim((string) $this->input->post('TransDescription'));
      $Notes = trim((string) $this->input->post('Notes'));
      $returnToInput = trim((string) $this->input->post('return_to'));

      $invoiceRecord = $this->_findInvoiceRecord($settingsID, $orderID, $InvoiceNo);

      if (!$invoiceRecord) {
        show_404();
        return;
      }

      if (!$this->_can_current_staff_access_invoice($invoiceRecord)) {
        show_404();
        return;
      }

      $resolvedSource = $source !== '' ? $source : (string) $invoiceRecord->invoiceSource;
      $defaultReturnTo = $resolvedSource === 'Job Order' ? 'joList' : 'invList';
      $allowedReturnTargets = array('invList', 'joList', 'paymentList');
      $returnTo = in_array($returnToInput, $allowedReturnTargets, true) ? $returnToInput : $defaultReturnTo;

      $previousTotal = (float) $invoiceRecord->AmountPaid;
      $currentBalance = max(0, (float) $invoiceRecord->TotalDue - $previousTotal);
      $creditedAmount = $this->_paymentCreditedAmount($AmountPaid, $TaxAmount);
      $redirectUrl = 'Page/addPaymentJO?id=' . rawurlencode((string) $invoiceRecord->orderID)
        . '&InvoiceNo=' . rawurlencode((string) $invoiceRecord->InvoiceNo)
        . '&PaymentSource=' . rawurlencode($resolvedSource)
        . '&return_to=' . rawurlencode($returnTo);

      if ($currentBalance <= 0) {
        $this->session->set_flashdata('payment_notice', 'Invoice #' . $invoiceRecord->InvoiceNo . ' is already fully paid.');
        redirect($redirectUrl);
        return;
      }

      if ($creditedAmount <= 0) {
        $this->session->set_flashdata('payment_notice', 'Enter a valid amount paid or BIR Form 2307 tax for Invoice #' . $invoiceRecord->InvoiceNo . '.');
        redirect($redirectUrl);
        return;
      }

      if ($creditedAmount - $currentBalance > 0.00001) {
        $this->session->set_flashdata('payment_notice', 'Total credit exceeds the remaining balance of ' . number_format($currentBalance, 2) . ' for Invoice #' . $invoiceRecord->InvoiceNo . '.');
        redirect($redirectUrl);
        return;
      }

      // Optional BIR Form 2307 attachment (allowed regardless of tax amount; upload is no longer required)
      if (isset($_FILES['bir_attachment']) && $_FILES['bir_attachment']['error'] !== UPLOAD_ERR_NO_FILE) {
        if ($_FILES['bir_attachment']['error'] === UPLOAD_ERR_OK) {
          $allowedTypes = array('application/pdf', 'image/jpeg', 'image/jpg', 'image/png');
          $fileType = $_FILES['bir_attachment']['type'];
          $fileSize = $_FILES['bir_attachment']['size'];
          $maxSize = 5 * 1024 * 1024; // 5MB

          if (!in_array($fileType, $allowedTypes, true)) {
            $this->session->set_flashdata('payment_notice', 'Invalid file type for BIR Form 2307. Please upload PDF, JPG, or PNG only.');
            redirect($redirectUrl);
            return;
          }

          if ($fileSize > $maxSize) {
            $this->session->set_flashdata('payment_notice', 'BIR Form 2307 attachment must be less than 5MB.');
            redirect($redirectUrl);
            return;
          }

          $uploadDir = FCPATH . 'uploads' . DIRECTORY_SEPARATOR . 'bir_forms' . DIRECTORY_SEPARATOR;
          if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
          }

          $fileExt = pathinfo($_FILES['bir_attachment']['name'], PATHINFO_EXTENSION);
          $fileName = 'BIR2307_' . $InvoiceNo . '_' . date('Ymd_His') . '_' . uniqid() . '.' . $fileExt;
          $filePath = $uploadDir . $fileName;

          if (!move_uploaded_file($_FILES['bir_attachment']['tmp_name'], $filePath)) {
            $this->session->set_flashdata('payment_notice', 'Failed to save BIR Form 2307 attachment. Please try again.');
            redirect($redirectUrl);
            return;
          }

          $attachmentPath = 'uploads/bir_forms/' . $fileName;
        }
      }

      // Check if Notes column exists in payments table, add if not
      $columnCheck = $this->db->query("SHOW COLUMNS FROM `payments` LIKE 'Notes'");
      $columnExists = $columnCheck->num_rows() > 0;
      if (!$columnExists) {
        $this->db->query("ALTER TABLE `payments` ADD COLUMN `Notes` TEXT NULL AFTER `TransDescription`");
        $this->session->set_flashdata('payment_notice', 'Database updated: Notes column added to payments table.');
      }

      $paymentPayload = array(
        'InvoiceNo' => $InvoiceNo,
        'PDate' => $PDate,
        'AmountPaid' => $AmountPaid,
        'TaxAmount' => $TaxAmount,
        'ORNo' => $ORNo,
        'PaymentReference' => $PaymentReference,
        'Cashier' => $name,
        'PaymentSource' => $resolvedSource,
        'CustID' => trim((string) $invoiceRecord->CustID) !== '' ? $invoiceRecord->CustID : null,
        'Customer' => (string) $invoiceRecord->Customer,
        'TransDescription' => $TransDescription,
        'Notes' => $Notes,
        'ORStat' => 'Valid',
        'TerminalNo' => '',
        'settingsID' => $settingsID,
        'attachment_path' => isset($attachmentPath) ? $attachmentPath : null,
      );
      $this->db->insert('payments', $paymentPayload);
      $this->_syncInvoicePaymentTotals($settingsID, (string) $invoiceRecord->InvoiceNo, (int) $invoiceRecord->orderID);
      $this->session->set_flashdata('payment_notice', 'Payment successfully added.');
      redirect('Page/' . $returnTo);
    }
  }

  function searchPayment()
  {
    $searchTerm = trim((string) $this->input->post('search_term'));
    $settingsID = $this->session->userdata('settingsID');

    if (empty($searchTerm)) {
      echo json_encode(array('success' => false, 'message' => 'Search term is required'));
      return;
    }

    // Clean search term - remove any non-numeric characters for ID search
    $cleanSearchTerm = preg_replace('/[^0-9]/', '', $searchTerm);

    // Search by Payment ID or Invoice No
    $this->db->where('settingsID', $settingsID);
    $this->db->where('ORStat', 'Valid');

    // Try to find by paymentID first, then by InvoiceNo
    if (is_numeric($cleanSearchTerm)) {
      $this->db->group_start();
      $this->db->where('paymentID', $cleanSearchTerm);
      $this->db->or_where('InvoiceNo', $searchTerm); // Keep original for InvoiceNo (might have letters)
      $this->db->group_end();
    } else {
      $this->db->like('InvoiceNo', $searchTerm);
    }

    $this->db->order_by('PDate', 'DESC');
    $this->db->limit(10); // Limit results for performance
    $query = $this->db->get('payments');

    if ($query->num_rows() > 0) {
      $results = $query->result();
      echo json_encode(array('success' => true, 'data' => $results));
    } else {
      echo json_encode(array('success' => false, 'message' => 'No payment found'));
    }
  }

  function paymentsWithTax()
  {
    $settingsID = $this->session->userdata('settingsID');

    // Get date filter parameters
    $fromDate = trim((string) $this->input->get('from_date'));
    $toDate = trim((string) $this->input->get('to_date'));

    // Get all payments with TaxAmount > 0
    $this->db->where('settingsID', $settingsID);
    $this->db->where('TaxAmount >', 0);
    $this->db->where('ORStat', 'Valid');

    // Apply date filter if provided
    if ($fromDate !== '') {
      $this->db->where('PDate >=', $fromDate);
    }
    if ($toDate !== '') {
      $this->db->where('PDate <=', $toDate);
    }

    $this->db->order_by('PDate', 'DESC');
    $query = $this->db->get('payments');

    $data['payments'] = $query->result();
    $data['title'] = 'Payments with BIR Form 2307';
    $data['from_date'] = $fromDate;
    $data['to_date'] = $toDate;

    $this->load->view('includes/head', $data);
    $this->load->view('payments_with_tax', $data);
    $this->load->view('includes/footer');
  }

  function viewBIRAttachment($paymentID = null)
  {
    if (!$paymentID) {
      show_404();
      return;
    }

    $settingsID = $this->session->userdata('settingsID');

    // Get the payment record
    $this->db->where('paymentID', $paymentID);
    $this->db->where('settingsID', $settingsID);
    $query = $this->db->get('payments');

    if ($query->num_rows() === 0) {
      show_404();
      return;
    }

    $payment = $query->row();

    if (empty($payment->attachment_path)) {
      $this->session->set_flashdata('error', 'No attachment found for this payment.');
      redirect('Page/paymentsWithTax');
      return;
    }

    $filePath = FCPATH . $payment->attachment_path;

    if (!file_exists($filePath)) {
      $this->session->set_flashdata('error', 'Attachment file not found.');
      redirect('Page/paymentsWithTax');
      return;
    }

    // Determine content type
    $fileExt = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
    $contentTypes = array(
      'pdf' => 'application/pdf',
      'jpg' => 'image/jpeg',
      'jpeg' => 'image/jpeg',
      'png' => 'image/png'
    );
    $contentType = isset($contentTypes[$fileExt]) ? $contentTypes[$fileExt] : 'application/octet-stream';

    // Output file
    header('Content-Type: ' . $contentType);
    header('Content-Disposition: inline; filename="' . basename($filePath) . '"');
    header('Content-Length: ' . filesize($filePath));
    header('Cache-Control: public, max-age=86400');

    readfile($filePath);
    exit;
  }

  function updatePayment()
  {
    if (!$this->_is_admin_user()) {
      redirect('Page/paymentList');
      return;
    }

    $id = $this->input->get('id');
    $result['data'] = $this->CashModel->updatePayment($id);
    $settingsID = $this->session->userdata('settingsID');
    $result['data2'] = $this->CashModel->getClients($settingsID);
    $this->load->view('update_payment', $result);

    if ($this->input->method() === 'post') {

      $id = $this->input->post('id');
      $existingPayment = isset($result['data'][0]) ? $result['data'][0] : null;
      $InvoiceNo = trim((string) $this->input->post('InvoiceNo'));
      $PDate = $this->_normalizeDateInput($this->input->post('PDate')) ?: date('Y-m-d');
      $AmountPaid = $this->_normalizePaymentAmount($this->input->post('AmountPaid'));
      $TaxAmount = $this->_normalizePaymentAmount($this->input->post('TaxAmount'));
      $ORNo = trim((string) $this->input->post('ORNo'));
      $CustID = trim((string) $this->input->post('CustID'));
      list($CustID, $Customer) = $this->_resolveCustomerContext(
        $settingsID,
        $CustID,
        $this->input->post('Customer')
      );
      $TransDescription = trim((string) $this->input->post('TransDescription'));
      $PaymentReference = trim((string) $this->input->post('PaymentReference'));

      $this->db->where('paymentID', $id);
      $this->db->update('payments', array(
        'InvoiceNo' => $InvoiceNo,
        'PDate' => $PDate,
        'AmountPaid' => $AmountPaid,
        'TaxAmount' => $TaxAmount,
        'ORNo' => $ORNo,
        'PaymentReference' => $PaymentReference,
        'CustID' => $CustID !== '' ? $CustID : null,
        'Customer' => $Customer,
        'TransDescription' => $TransDescription,
      ));

      if ($existingPayment && !empty($existingPayment->InvoiceNo)) {
        $this->_syncInvoicePaymentTotals($settingsID, (string) $existingPayment->InvoiceNo);
      }
      if ($InvoiceNo !== '' && (!$existingPayment || (string) $existingPayment->InvoiceNo !== $InvoiceNo)) {
        $this->_syncInvoicePaymentTotals($settingsID, $InvoiceNo);
      }
      redirect('Page/paymentList');
    }
  }

  function deleteJO()
  {
    $settingsID = $this->session->userdata('settingsID');
    $id = (int) $this->input->get('id');
    $returnToInput = trim((string) $this->input->get('return_to'));
    $invoice = $id > 0 ? $this->CashModel->getInvoiceByOrderID($id, $settingsID) : null;
    $inferredReturnTo = ($invoice && isset($invoice->invoiceSource) && $invoice->invoiceSource === 'Others') ? 'invList' : 'joList';
    $returnTo = in_array($returnToInput, array('invList', 'joList'), true) ? $returnToInput : $inferredReturnTo;

    if (!$this->_is_admin_user()) {
      redirect('Page/' . $returnTo);
      return;
    }

    if ($invoice) {
      $this->db->where('orderID', $invoice->orderID);
      $this->db->where('settingsID', $settingsID);
      $this->db->update('invoice', array(
        'invoiceStat' => 'Deleted',
      ));

      if ($this->db->table_exists('invoice_items')) {
        $this->db
          ->where('orderID', (int) $invoice->orderID)
          ->where('settingsID', $settingsID)
          ->delete('invoice_items');
      }
    }

    redirect('Page/' . $returnTo);
  }

  function voidInvoice()
  {
    $settingsID = $this->session->userdata('settingsID');
    $id = (int) $this->input->post('orderID');
    $voidReason = trim((string) $this->input->post('voidReason', true));
    $returnToInput = trim((string) $this->input->post('return_to'));

    if (!$this->_is_admin_user()) {
      $this->session->set_flashdata('danger', 'Access denied. Only admins can void invoices.');
      redirect('Page/invList');
      return;
    }

    if (!$id) {
      $this->session->set_flashdata('danger', 'Invalid invoice ID.');
      redirect('Page/invList');
      return;
    }

    $invoice = $this->CashModel->getInvoiceByOrderID($id, $settingsID);
    if (!$invoice) {
      $this->session->set_flashdata('danger', 'Invoice not found.');
      redirect('Page/invList');
      return;
    }

    // Update invoice status to Voided
    $this->db->where('orderID', $id);
    $this->db->where('settingsID', $settingsID);
    $this->db->update('invoice', array(
      'invoiceStat' => 'Voided',
      'voidReason' => $voidReason ?: 'No reason provided',
      'voidDate' => date('Y-m-d H:i:s'),
      'voidBy' => $this->session->userdata('name') ?: 'System',
      'Balance' => 0,
      'AmountPaid' => 0
    ));

    // Delete related invoice items
    if ($this->db->table_exists('invoice_items')) {
      $this->db
        ->where('orderID', $id)
        ->where('settingsID', $settingsID)
        ->delete('invoice_items');
    }

    $this->session->set_flashdata('success', 'Invoice #' . $invoice->InvoiceNo . ' has been voided successfully.');
    redirect('Page/' . ($returnToInput === 'joList' ? 'joList' : 'invList'));
  }

  function emailInvoicePDF()
  {
    $settingsID = $this->session->userdata('settingsID');
    $orderID = (int) $this->input->post('orderID');
    $recipientEmail = trim((string) $this->input->post('recipientEmail', true));
    $emailMessage = trim((string) $this->input->post('emailMessage', true));

    if (!$this->session->userdata('user_id') && !$this->session->userdata('username')) {
      $this->session->set_flashdata('danger', 'Please log in to send emails.');
      redirect('Page/invList');
      return;
    }

    if (!$orderID) {
      $this->session->set_flashdata('danger', 'Invalid invoice ID.');
      redirect('Page/invList');
      return;
    }

    if (!$recipientEmail || !filter_var($recipientEmail, FILTER_VALIDATE_EMAIL)) {
      $this->session->set_flashdata('danger', 'Please enter a valid email address.');
      redirect('Page/invList');
      return;
    }

    $invoice = $this->CashModel->getInvoiceByOrderID($orderID, $settingsID);
    if (!$invoice) {
      $this->session->set_flashdata('danger', 'Invoice not found.');
      redirect('Page/invList');
      return;
    }

    // Get business details
    $businessDetails = $this->CashModel->businessDetails($settingsID);
    $business = !empty($businessDetails) ? $businessDetails[0] : null;

    // Get invoice items
    $invoiceItems = [];
    if ($this->db->table_exists('invoice_items')) {
      $this->db->where('orderID', $orderID);
      $this->db->where('settingsID', $settingsID);
      $query = $this->db->get('invoice_items');
      $invoiceItems = $query->result();
    }

    $invoiceFooterData = $this->CashModel->invoiceFooterSettings($settingsID);
    $invoiceFooter = !empty($invoiceFooterData) ? $invoiceFooterData[0] : null;

    // Configure email using app defaults, then allow per-tenant overrides from pos_settings.
    $this->config->load('email');
    $this->load->library('email');

    $settings = $this->db->get_where('pos_settings', ['settingsID' => $settingsID])->row();

    $defaultProtocol = trim((string) $this->config->item('protocol'));
    $defaultHost = trim((string) $this->config->item('smtp_host'));
    $defaultUser = trim((string) $this->config->item('smtp_user'));
    $defaultPass = (string) $this->config->item('smtp_pass');
    $defaultPort = (int) $this->config->item('smtp_port');
    $defaultCrypto = trim((string) $this->config->item('smtp_crypto'));
    $defaultTimeout = (int) $this->config->item('smtp_timeout');

    $smtpHost = $settings && !empty($settings->smtp_host) ? trim((string) $settings->smtp_host) : $defaultHost;
    $smtpUser = $settings && !empty($settings->smtp_user) ? trim((string) $settings->smtp_user) : $defaultUser;
    $smtpPass = $settings && isset($settings->smtp_pass) && $settings->smtp_pass !== '' ? (string) $settings->smtp_pass : $defaultPass;
    $smtpPort = $settings && !empty($settings->smtp_port) ? (int) $settings->smtp_port : ($defaultPort ?: 587);
    $smtpCrypto = $settings && !empty($settings->smtp_crypto) ? trim((string) $settings->smtp_crypto) : ($defaultCrypto !== '' ? $defaultCrypto : 'tls');

    $this->email->initialize([
      'protocol' => $defaultProtocol !== '' ? $defaultProtocol : 'smtp',
      'smtp_host' => $smtpHost,
      'smtp_user' => $smtpUser,
      'smtp_pass' => $smtpPass,
      'smtp_port' => $smtpPort,
      'smtp_crypto' => $smtpCrypto,
      'smtp_timeout' => $defaultTimeout ?: 10,
      'mailtype' => 'html',
      'charset' => 'utf-8',
      'newline' => "\r\n",
      'crlf' => "\r\n",
      'wordwrap' => TRUE
    ]);

    $fromEmail = '';
    foreach ([$smtpUser, trim((string) ($business->Email ?? ''))] as $candidateEmail) {
      if ($candidateEmail !== '' && filter_var($candidateEmail, FILTER_VALIDATE_EMAIL)) {
        $fromEmail = $candidateEmail;
        break;
      }
    }

    if ($fromEmail === '') {
      $this->session->set_flashdata('danger', 'SMTP sender email is not configured. Please update your email settings.');
      redirect('Page/invList');
      return;
    }

    $fromName = trim((string) $this->config->item('from_name'));
    if ($fromName === '') {
      $fromName = trim((string) ($business->CompName ?? $business->BusinessName ?? 'BERPS'));
    }
    if ($fromName === '') {
      $fromName = 'BERPS';
    }

    $invoiceNo = trim((string) ($invoice->InvoiceNo ?? 'N/A'));

    $this->email->from($fromEmail, $fromName);
    $this->email->to($recipientEmail);
    $this->email->subject('Invoice #' . $invoiceNo . ' from ' . $fromName);
    $emailBody = $this->_buildInvoiceEmailHTML($invoice, $invoiceItems, $business, $invoiceFooter, $fromName, $fromEmail, $emailMessage);

    $this->email->message($emailBody);

    if ($this->email->send()) {
      $this->session->set_flashdata('success', 'Invoice #' . $invoiceNo . ' has been sent to ' . $recipientEmail . ' successfully.');
    } else {
      $this->session->set_flashdata('danger', 'Failed to send email. Please check your email configuration.');
    }

    redirect('Page/invList');
  }

  private function _buildInvoiceEmailHTML($invoice, $items, $business, $invoiceFooter, $fromName, $fromEmail, $emailMessage = '')
  {
    $companyName = trim((string) ($business->CompName ?? $business->BusinessName ?? 'BERPS'));
    $companyAddress = trim((string) ($business->CompAddress ?? ''));
    $companyTin = trim((string) ($business->CompTin ?? ''));
    $companyType = trim((string) ($business->CompType ?? ''));

    $invoiceNo = trim((string) ($invoice->InvoiceNo ?? ''));
    $customer = trim((string) ($invoice->Customer ?? ''));
    $customerAddress = trim((string) ($invoice->CustAddress ?? ''));
    $transactionDateRaw = trim((string) ($invoice->TransDate ?? ''));
    $transactionDate = ($transactionDateRaw !== '' && $transactionDateRaw !== '0000-00-00')
      ? date('F j, Y', strtotime($transactionDateRaw))
      : 'Not specified';
    $receiveDateRaw = trim((string) ($invoice->ReceiveDate ?? ''));
    $notes = trim((string) ($invoice->Notes ?? ''));
    $orderID = trim((string) ($invoice->orderID ?? ''));
    $recurringFrequency = trim((string) ($invoice->recurringFrequency ?? 'none'));
    $recurringScheduleRaw = trim((string) ($invoice->recurringScheduleDate ?? ''));
    $recurringSchedule = ($recurringScheduleRaw !== '' && $recurringScheduleRaw !== '0000-00-00')
      ? date('F j, Y', strtotime($recurringScheduleRaw))
      : '';

    if ($notes === '' && $invoiceFooter) {
      $defaultNotes = array();
      $hasBank1 = !empty($invoiceFooter->bank_name_1) && !empty($invoiceFooter->bank_account_name_1) && !empty($invoiceFooter->bank_account_no_1);
      $hasBank2 = !empty($invoiceFooter->bank_name_2) && !empty($invoiceFooter->bank_account_name_2) && !empty($invoiceFooter->bank_account_no_2);

      if ($hasBank1 || $hasBank2) {
        $defaultNotes[] = 'Payment Information:';
        $defaultNotes[] = 'You may deposit your payment to:';

        if ($hasBank1) {
          $defaultNotes[] = '';
          $defaultNotes[] = 'Bank Name: ' . $invoiceFooter->bank_name_1;
          $defaultNotes[] = 'Account Name: ' . $invoiceFooter->bank_account_name_1;
          $defaultNotes[] = 'Account No.: ' . $invoiceFooter->bank_account_no_1;
        }

        if ($hasBank2) {
          $defaultNotes[] = '';
          $defaultNotes[] = 'Bank Name: ' . $invoiceFooter->bank_name_2;
          $defaultNotes[] = 'Account Name: ' . $invoiceFooter->bank_account_name_2;
          $defaultNotes[] = 'Account No.: ' . $invoiceFooter->bank_account_no_2;
        }
      }

      if (!empty($invoiceFooter->contact_email) || !empty($invoiceFooter->contact_phone)) {
        $defaultNotes[] = '';
        $defaultNotes[] = 'Thank you for doing business with us! If you have any questions, please feel free to contact us at';
        $contactParts = array();
        if (!empty($invoiceFooter->contact_email)) {
          $contactParts[] = 'Email: ' . $invoiceFooter->contact_email;
        }
        if (!empty($invoiceFooter->contact_phone)) {
          $contactParts[] = 'Call us at: ' . $invoiceFooter->contact_phone;
        }
        $defaultNotes[] = implode(' | ', $contactParts);
      }

      if (!empty($invoiceFooter->footer_disclaimer)) {
        $defaultNotes[] = '';
        $defaultNotes[] = $invoiceFooter->footer_disclaimer;
      }

      if (!empty($defaultNotes)) {
        $notes = implode("\n", $defaultNotes);
      }
    }

    $dueDateRaw = ($recurringScheduleRaw !== '' && $recurringScheduleRaw !== '0000-00-00')
      ? $recurringScheduleRaw
      : (($receiveDateRaw !== '' && $receiveDateRaw !== '0000-00-00') ? $receiveDateRaw : $transactionDateRaw);
    $dueDate = ($dueDateRaw !== '' && $dueDateRaw !== '0000-00-00')
      ? date('F j, Y', strtotime($dueDateRaw))
      : 'Not specified';

    $coveredMonths = '';
    if ($recurringFrequency !== '' && $recurringFrequency !== 'none' && $recurringScheduleRaw !== '') {
      $startDate = new DateTime($recurringScheduleRaw);
      $endDate = clone $startDate;
      $coverageOption = $invoice->coverageOption ?? 'coming';
      switch ($recurringFrequency) {
        case 'daily':
          break;
        case 'weekly':
          if ($coverageOption === 'previous') {
            $startDate->modify('-6 days');
            $endDate = clone $startDate;
            $endDate->modify('+6 days');
          } else {
            $endDate->modify('+6 days');
          }
          break;
        case 'monthly':
          if ($coverageOption === 'previous') {
            $startDate->modify('-1 month');
            $endDate = clone $startDate;
            $endDate->modify('+1 month')->modify('-1 day');
          } else {
            $endDate->modify('+1 month')->modify('-1 day');
          }
          break;
        case 'quarterly':
          // Quarterly: use calendar quarters based on coverageOption
          $year = (int)$startDate->format('Y');
          $month = (int)$startDate->format('n');
          $quarter = ceil($month / 3);

          if ($coverageOption === 'previous') {
            // Previous quarter
            $quarter--;
            if ($quarter < 1) {
              $quarter = 4;
              $year--;
            }
          }

          // Calculate start and end of the quarter
          $startMonth = ($quarter - 1) * 3 + 1;
          $endMonth = $quarter * 3;

          $startDate = new DateTime("$year-$startMonth-01");
          $endDate = new DateTime("$year-$endMonth-01");
          $endDate->modify('+1 month')->modify('-1 day');
          break;
        case 'yearly':
          if ($coverageOption === 'previous') {
            $startDate->modify('-1 year');
            $endDate = clone $startDate;
            $endDate->modify('+1 year')->modify('-1 day');
          } else {
            $endDate->modify('+1 year')->modify('-1 day');
          }
          break;
        default:
          $endDate = null;
          break;
      }
      if ($endDate instanceof DateTime) {
        $coveredMonths = 'From ' . date('M d, Y', $startDate->getTimestamp()) . ' To ' . date('M d, Y', $endDate->getTimestamp());
      }
    }

    $totalDue = (float) ($invoice->TotalDue ?? 0);
    $amountPaid = (float) ($invoice->AmountPaid ?? 0);
    $balance = (float) ($invoice->Balance ?? 0);
    $statusLabel = 'Unpaid';
    $statusBackground = '#fef3c7';
    $statusColor = '#92400e';
    if ($balance <= 0) {
      $statusLabel = 'Paid';
      $statusBackground = '#dcfce7';
      $statusColor = '#166534';
    } elseif ($amountPaid > 0) {
      $statusLabel = 'Partially Paid';
      $statusBackground = '#dbeafe';
      $statusColor = '#1d4ed8';
    }

    $lineItems = is_array($items) ? $items : array();
    if (empty($lineItems)) {
      $legacyQuantity = (isset($invoice->itemQuantity) && is_numeric($invoice->itemQuantity) && (float) $invoice->itemQuantity > 0)
        ? (float) $invoice->itemQuantity
        : 1;
      $legacyUnit = trim((string) ($invoice->itemDurationUnit ?? 'each'));
      $legacyUnitPrice = (isset($invoice->itemUnitPrice) && is_numeric($invoice->itemUnitPrice))
        ? (float) $invoice->itemUnitPrice
        : ($legacyQuantity > 0 ? ($totalDue / $legacyQuantity) : $totalDue);

      $lineItems[] = array(
        'itemDescription' => trim((string) ($invoice->JobDescription ?? '')) !== '' ? (string) $invoice->JobDescription : 'Invoice item',
        'itemQuantity' => $legacyQuantity,
        'itemDurationUnit' => $legacyUnit !== '' ? $legacyUnit : 'each',
        'itemUnitPrice' => $legacyUnitPrice,
        'lineTotal' => $totalDue,
      );
    }

    $lineRowsHtml = '';
    foreach ($lineItems as $lineItem) {
      if (is_object($lineItem)) {
        $lineItem = (array) $lineItem;
      }

      $itemDescription = trim((string) ($lineItem['itemDescription'] ?? $lineItem['particulars'] ?? $lineItem['description'] ?? 'Invoice item'));
      $itemQuantity = (isset($lineItem['itemQuantity']) && is_numeric($lineItem['itemQuantity']) && (float) $lineItem['itemQuantity'] > 0)
        ? (float) $lineItem['itemQuantity']
        : ((isset($lineItem['quantity']) && is_numeric($lineItem['quantity']) && (float) $lineItem['quantity'] > 0) ? (float) $lineItem['quantity'] : 1);
      $itemDurationUnit = trim((string) ($lineItem['itemDurationUnit'] ?? 'each'));
      $itemUnitPrice = (isset($lineItem['itemUnitPrice']) && is_numeric($lineItem['itemUnitPrice']))
        ? (float) $lineItem['itemUnitPrice']
        : ((isset($lineItem['unit_price']) && is_numeric($lineItem['unit_price'])) ? (float) $lineItem['unit_price'] : 0);
      $lineTotal = (isset($lineItem['lineTotal']) && is_numeric($lineItem['lineTotal']))
        ? (float) $lineItem['lineTotal']
        : round($itemQuantity * $itemUnitPrice, 2);
      if ($itemUnitPrice <= 0 && $itemQuantity > 0 && $lineTotal > 0) {
        $itemUnitPrice = round($lineTotal / $itemQuantity, 2);
      }

      $itemQuantityDisplay = (abs($itemQuantity - round($itemQuantity)) < 0.00001)
        ? (string) ((int) round($itemQuantity))
        : number_format($itemQuantity, 2);
      $itemDurationLabel = '';
      if ($itemDurationUnit !== '' && $itemDurationUnit !== 'each') {
        $itemDurationLabel = ($itemQuantity == 1 || preg_match('/s$/i', $itemDurationUnit))
          ? $itemDurationUnit
          : $itemDurationUnit . 's';
      }
      $rateUnitLabel = $itemDurationUnit !== '' ? $itemDurationUnit : 'each';
      $itemBreakdownText = $itemDurationLabel !== ''
        ? ($itemQuantityDisplay . ' ' . $itemDurationLabel . ' x PHP ' . number_format($itemUnitPrice, 2) . ' / ' . $rateUnitLabel)
        : ($itemQuantityDisplay . ' x PHP ' . number_format($itemUnitPrice, 2) . ' / ' . $rateUnitLabel);

      $lineRowsHtml .= '<tr>';
      $lineRowsHtml .= '<td style="padding:14px 12px; border-top:1px solid #e5e7eb; color:#64748b; font-size:14px; vertical-align:top;">' . htmlspecialchars($itemQuantityDisplay, ENT_QUOTES, 'UTF-8') . '</td>';
      $lineRowsHtml .= '<td style="padding:14px 12px; border-top:1px solid #e5e7eb; vertical-align:top;">';
      $lineRowsHtml .= '<div style="font-size:15px; font-weight:700; color:#0f172a; margin-bottom:4px;">' . htmlspecialchars($itemDescription !== '' ? $itemDescription : 'Invoice item', ENT_QUOTES, 'UTF-8') . '</div>';
      $lineRowsHtml .= '<div style="font-size:13px; color:#475569; line-height:1.6;">' . htmlspecialchars($itemBreakdownText, ENT_QUOTES, 'UTF-8') . '</div>';
      if ($coveredMonths !== '') {
        $lineRowsHtml .= '<div style="font-size:12px; color:#1d4ed8; line-height:1.6; margin-top:4px;">' . htmlspecialchars($coveredMonths, ENT_QUOTES, 'UTF-8') . '</div>';
      }
      $lineRowsHtml .= '</td>';
      $lineRowsHtml .= '<td style="padding:14px 12px; border-top:1px solid #e5e7eb; color:#0f172a; font-size:14px; text-align:right; vertical-align:top;">' . number_format($itemUnitPrice, 2) . '</td>';
      $lineRowsHtml .= '<td style="padding:14px 12px; border-top:1px solid #e5e7eb; color:#0f172a; font-size:14px; font-weight:700; text-align:right; vertical-align:top;">' . number_format($lineTotal, 2) . '</td>';
      $lineRowsHtml .= '</tr>';
    }

    $messageBlockHtml = '';
    $emailMessage = trim((string) $emailMessage);
    if ($emailMessage !== '') {
      $messageBlockHtml .= '<tr><td style="padding:0 0 18px 0;">';
      $messageBlockHtml .= '<div style="padding:14px 16px; border:1px solid #dbeafe; background:#f8fbff; border-radius:14px; font-size:14px; line-height:1.7; color:#334155;">' . nl2br(htmlspecialchars($emailMessage, ENT_QUOTES, 'UTF-8')) . '</div>';
      $messageBlockHtml .= '</td></tr>';
    }

    $notesHtml = $notes !== ''
      ? nl2br(htmlspecialchars($notes, ENT_QUOTES, 'UTF-8'))
      : 'Please contact us for payment instructions.';

    $safeCompanyName = htmlspecialchars($companyName, ENT_QUOTES, 'UTF-8');
    $safeCompanyAddress = htmlspecialchars($companyAddress, ENT_QUOTES, 'UTF-8');
    $safeCompanyTin = htmlspecialchars($companyTin, ENT_QUOTES, 'UTF-8');
    $safeCompanyType = htmlspecialchars($companyType, ENT_QUOTES, 'UTF-8');
    $safeInvoiceNo = htmlspecialchars($invoiceNo, ENT_QUOTES, 'UTF-8');
    $safeCustomer = htmlspecialchars($customer !== '' ? $customer : 'Walk-in Customer', ENT_QUOTES, 'UTF-8');
    $safeCustomerAddress = $customerAddress !== '' ? nl2br(htmlspecialchars($customerAddress, ENT_QUOTES, 'UTF-8')) : 'No customer address on file.';
    $safeTransactionDate = htmlspecialchars($transactionDate, ENT_QUOTES, 'UTF-8');
    $safeDueDate = htmlspecialchars($dueDate, ENT_QUOTES, 'UTF-8');
    $safeReferenceId = htmlspecialchars($orderID !== '' ? $orderID : '—', ENT_QUOTES, 'UTF-8');
    $safeGenerated = htmlspecialchars(date('M j, Y g:i A'), ENT_QUOTES, 'UTF-8');
    $safeRecurringFrequency = htmlspecialchars(ucfirst($recurringFrequency), ENT_QUOTES, 'UTF-8');
    $safeRecurringSchedule = htmlspecialchars($recurringSchedule !== '' ? $recurringSchedule : 'Not specified', ENT_QUOTES, 'UTF-8');
    $safeStatusLabel = htmlspecialchars($statusLabel, ENT_QUOTES, 'UTF-8');

    $html = '<html><body style="margin:0; padding:0; background:#f6f8fc; font-family:Arial, sans-serif; color:#0f172a;">';
    $html .= '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#f6f8fc; border-collapse:collapse;">';
    $html .= '<tr><td style="padding:24px 12px;">';
    $html .= '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="max-width:960px; margin:0 auto; background:#ffffff; border:1px solid #dbe4f0; border-radius:22px; border-collapse:separate;">';
    $html .= '<tr><td style="padding:28px 30px 22px; border-bottom:1px solid #e5edf7; background:#f8fbff; border-top-left-radius:22px; border-top-right-radius:22px;">';
    $html .= '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="border-collapse:collapse;">';
    $html .= '<tr>';
    $html .= '<td style="vertical-align:top; padding-right:18px;">';
    $html .= '<div style="font-size:30px; font-weight:700; line-height:1.2; color:#0f172a;">' . $safeCompanyName . '</div>';
    $html .= '<div style="margin-top:10px; font-size:13px; line-height:1.7; color:#475569;">';
    if ($companyAddress !== '') {
      $html .= '<div>' . $safeCompanyAddress . '</div>';
    }
    if ($companyTin !== '') {
      $html .= '<div>TIN: ' . $safeCompanyTin . '</div>';
    }
    if ($companyType !== '') {
      $html .= '<div>' . $safeCompanyType . '</div>';
    }
    $html .= '</div>';
    $html .= '</td>';
    $html .= '<td style="width:230px; vertical-align:top; text-align:right;">';
    $html .= '<div style="display:inline-block; padding:7px 12px; border-radius:999px; background:#e0e7ff; color:#1d4ed8; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:1px;">Official Invoice</div>';
    $html .= '<div style="margin-top:10px; font-size:32px; font-weight:700; color:#0f172a;">Invoice</div>';
    $html .= '<div style="margin-top:12px; display:inline-block; padding:7px 14px; border-radius:999px; background:' . $statusBackground . '; color:' . $statusColor . '; font-size:12px; font-weight:700;">' . $safeStatusLabel . '</div>';
    $html .= '</td>';
    $html .= '</tr>';
    $html .= '</table>';
    $html .= '</td></tr>';

    $html .= '<tr><td style="padding:24px 30px 0;">';
    $html .= '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="border-collapse:separate;">';
    $html .= $messageBlockHtml;
    $html .= '<tr>';
    $html .= '<td style="width:46%; vertical-align:top; padding:0 10px 18px 0;">';
    $html .= '<div style="padding:18px 18px 16px; border:1px solid #e5edf7; border-radius:18px; background:#ffffff;">';
    $html .= '<div style="font-size:11px; letter-spacing:1px; text-transform:uppercase; font-weight:700; color:#64748b; margin-bottom:10px;">Bill To</div>';
    $html .= '<div style="font-size:20px; font-weight:700; color:#0f172a; margin-bottom:8px;">' . $safeCustomer . '</div>';
    $html .= '<div style="font-size:14px; line-height:1.7; color:#475569;">' . $safeCustomerAddress . '</div>';
    $html .= '</div>';
    $html .= '</td>';
    $html .= '<td style="width:54%; vertical-align:top; padding:0 0 18px 10px;">';
    $html .= '<div style="padding:18px 18px 16px; border:1px solid #e5edf7; border-radius:18px; background:#fcfdff;">';
    $html .= '<div style="font-size:11px; letter-spacing:1px; text-transform:uppercase; font-weight:700; color:#64748b; margin-bottom:12px;">Invoice Details</div>';
    $html .= '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="border-collapse:collapse;">';
    $html .= '<tr><td style="padding:6px 0; color:#64748b; font-size:13px;">Invoice No.</td><td style="padding:6px 0; color:#0f172a; font-size:13px; font-weight:700; text-align:right;">' . $safeInvoiceNo . '</td></tr>';
    $html .= '<tr><td style="padding:6px 0; color:#64748b; font-size:13px;">Invoice Date</td><td style="padding:6px 0; color:#0f172a; font-size:13px; font-weight:700; text-align:right;">' . $safeTransactionDate . '</td></tr>';
    $html .= '<tr><td style="padding:6px 0; color:#64748b; font-size:13px;">Due Date</td><td style="padding:6px 0; color:#0f172a; font-size:13px; font-weight:700; text-align:right;">' . $safeDueDate . '</td></tr>';
    $html .= '<tr><td style="padding:6px 0; color:#64748b; font-size:13px;">Reference ID</td><td style="padding:6px 0; color:#0f172a; font-size:13px; font-weight:700; text-align:right;">' . $safeReferenceId . '</td></tr>';
    $html .= '<tr><td style="padding:6px 0; color:#64748b; font-size:13px;">Generated</td><td style="padding:6px 0; color:#0f172a; font-size:13px; font-weight:700; text-align:right;">' . $safeGenerated . '</td></tr>';
    if ($recurringFrequency !== '' && $recurringFrequency !== 'none') {
      $html .= '<tr><td style="padding:6px 0; color:#64748b; font-size:13px;">Recurring</td><td style="padding:6px 0; color:#0f172a; font-size:13px; font-weight:700; text-align:right;">' . $safeRecurringFrequency . '</td></tr>';
      $html .= '<tr><td style="padding:6px 0; color:#64748b; font-size:13px;">Schedule Date</td><td style="padding:6px 0; color:#0f172a; font-size:13px; font-weight:700; text-align:right;">' . $safeRecurringSchedule . '</td></tr>';
    }
    $html .= '</table>';
    $html .= '</div>';
    $html .= '</td>';
    $html .= '</tr>';
    $html .= '</table>';
    $html .= '</td></tr>';

    $html .= '<tr><td style="padding:0 30px;">';
    $html .= '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="border:1px solid #e5edf7; border-radius:18px; border-collapse:separate; overflow:hidden;">';
    $html .= '<thead><tr style="background:#f8fbff;">';
    $html .= '<th style="padding:14px 12px; font-size:12px; letter-spacing:0.8px; text-transform:uppercase; color:#64748b; text-align:left;">Qty</th>';
    $html .= '<th style="padding:14px 12px; font-size:12px; letter-spacing:0.8px; text-transform:uppercase; color:#64748b; text-align:left;">Description</th>';
    $html .= '<th style="padding:14px 12px; font-size:12px; letter-spacing:0.8px; text-transform:uppercase; color:#64748b; text-align:right;">Unit Cost</th>';
    $html .= '<th style="padding:14px 12px; font-size:12px; letter-spacing:0.8px; text-transform:uppercase; color:#64748b; text-align:right;">Total</th>';
    $html .= '</tr></thead><tbody>' . $lineRowsHtml . '</tbody></table>';
    $html .= '</td></tr>';

    $html .= '<tr><td style="padding:22px 30px 0;">';
    $html .= '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="border-collapse:collapse;">';
    $html .= '<tr>';
    $html .= '<td style="width:58%; vertical-align:top; padding:0 12px 0 0;">';
    $html .= '<div style="padding:18px; border:1px solid #e5edf7; border-radius:18px; background:#ffffff;">';
    $html .= '<div style="font-size:14px; font-weight:700; color:#0f172a; margin-bottom:10px;">Notes</div>';
    $html .= '<div style="font-size:13px; line-height:1.75; color:#475569;">' . $notesHtml . '</div>';
    $html .= '</div>';
    $html .= '</td>';
    $html .= '<td style="width:42%; vertical-align:top; padding:0 0 0 12px;">';
    $html .= '<div style="padding:18px; border:1px solid #dbeafe; border-radius:18px; background:#f8fbff;">';
    $html .= '<div style="font-size:14px; font-weight:700; color:#0f172a; margin-bottom:10px;">Summary</div>';
    $html .= '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="border-collapse:collapse;">';
    $html .= '<tr><td style="padding:7px 0; color:#64748b; font-size:13px;">Total Due</td><td style="padding:7px 0; color:#0f172a; font-size:13px; text-align:right;">' . number_format($totalDue, 2) . '</td></tr>';
    $html .= '<tr><td style="padding:7px 0; color:#64748b; font-size:13px;">Amount Paid</td><td style="padding:7px 0; color:#0f172a; font-size:13px; text-align:right;">' . number_format($amountPaid, 2) . '</td></tr>';
    $html .= '<tr><td style="padding:10px 0 0; border-top:1px solid #dbe4f0; color:#0f172a; font-size:15px; font-weight:700;">Balance</td><td style="padding:10px 0 0; border-top:1px solid #dbe4f0; color:#1d4ed8; font-size:18px; font-weight:700; text-align:right;">' . number_format($balance, 2) . '</td></tr>';
    $html .= '</table>';
    $html .= '</div>';
    $html .= '</td>';
    $html .= '</tr>';
    $html .= '</table>';
    $html .= '</td></tr>';

    $html .= '<tr><td style="padding:24px 30px 30px;">';
    $html .= '<div style="padding-top:18px; border-top:1px solid #e5edf7; text-align:center;">';
    $html .= '<div style="font-size:12px; letter-spacing:1px; text-transform:uppercase; font-weight:700; color:#64748b;">Generated from BERPS.</div>';
    $html .= '<div style="margin-top:6px; font-size:12px; letter-spacing:1px; text-transform:uppercase; font-weight:700; color:#94a3b8;">Powered by Softtech Solutions and Services Co.</div>';
    $html .= '</div>';
    $html .= '</td></tr>';

    $html .= '</table>';
    $html .= '</td></tr></table>';
    $html .= '</body></html>';

    return $html;
  }

  private function _buildInvoicePDFHTML($invoice, $items, $business)
  {
    $settingsID = (int) ($invoice->settingsID ?? $this->session->userdata('settingsID') ?? 0);
    $invoiceFooter = null;
    if ($settingsID > 0) {
      $invoiceFooterData = $this->CashModel->invoiceFooterSettings($settingsID);
      $invoiceFooter = !empty($invoiceFooterData) ? $invoiceFooterData[0] : null;
    }

    return $this->load->view('invoice', array(
      'invoice' => $invoice,
      'invoiceItems' => $items,
      'business' => $business,
      'invoiceFooter' => $invoiceFooter,
      'autoPrint' => false,
      'isPdfRender' => true,
      'backUrl' => '',
      'backLabel' => '',
    ), true);
  }

  private function _generatePDF($html, $invoiceNo)
  {
    // Suppress deprecation warnings from dompdf for PHP 8+ compatibility
    $errorLevel = error_reporting();
    error_reporting($errorLevel & ~E_DEPRECATED & ~E_WARNING);

    // Check if dompdf is available via Composer (vendor directory)
    $vendorPath = APPPATH . '../vendor/autoload.php';
    if (file_exists($vendorPath)) {
      require_once $vendorPath;
      try {
        $dompdf = new Dompdf\Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        error_reporting($errorLevel);
        return $dompdf->output();
      } catch (Throwable $e) {
        error_reporting($errorLevel);
        // Fall through to next method
      }
    }

    // Check for standalone dompdf installation
    $dompdfPath = APPPATH . '../vendors/dompdf/autoload.inc.php';
    if (file_exists($dompdfPath)) {
      require_once $dompdfPath;
      $dompdf = new Dompdf\Dompdf();
      $dompdf->loadHtml($html);
      $dompdf->setPaper('A4', 'portrait');
      $dompdf->render();
      error_reporting($errorLevel);
      return $dompdf->output();
    }

    // Check for TCPDF
    $tcpdfPath = APPPATH . '../vendors/tcpdf/tcpdf.php';
    if (file_exists($tcpdfPath)) {
      require_once $tcpdfPath;
      $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
      $pdf->SetCreator(PDF_CREATOR);
      $pdf->SetAuthor('Softech Services');
      $pdf->SetTitle('Invoice ' . $invoiceNo);
      $pdf->setPrintHeader(false);
      $pdf->setPrintFooter(false);
      $pdf->AddPage();
      $pdf->writeHTML($html, true, false, true, false, '');
      error_reporting($errorLevel);
      return $pdf->Output('', 'S');
    }

    // Fallback: Try to use wkhtmltopdf if available on server
    $wkhtmltopdf = shell_exec('which wkhtmltopdf');
    if ($wkhtmltopdf) {
      $tempHtml = tempnam(sys_get_temp_dir(), 'inv_') . '.html';
      $tempPdf = tempnam(sys_get_temp_dir(), 'inv_') . '.pdf';
      file_put_contents($tempHtml, $html);
      shell_exec('wkhtmltopdf --quiet "' . $tempHtml . '" "' . $tempPdf . '"');
      if (file_exists($tempPdf)) {
        $pdfContent = file_get_contents($tempPdf);
        unlink($tempHtml);
        unlink($tempPdf);
        error_reporting($errorLevel);
        return $pdfContent;
      }
      unlink($tempHtml);
    }

    // Last resort: Return HTML as a "PDF-like" attachment (will need manual conversion)
    // or generate a simple text-based PDF structure
    error_reporting($errorLevel);
    return $this->_generateSimplePDF($html, $invoiceNo);
  }

  private function _generateSimplePDF($html, $invoiceNo)
  {
    // Extract invoice data from HTML and create formatted text-based PDF
    // Parse the new invoice layout structure

    // Extract company info
    preg_match('/<h1[^>]*>([^<]+)/', $html, $companyMatch);
    $companyName = isset($companyMatch[1]) ? trim($companyMatch[1]) : 'BERPS';

    // Extract invoice number from title
    preg_match('/<title>Invoice\s*([^<]+)/', $html, $titleMatch);
    $invNo = isset($titleMatch[1]) ? trim($titleMatch[1]) : $invoiceNo;

    // Extract customer from Bill To section
    preg_match('/Bill To<\/p>\s*<h3[^>]*>([^<]+)/', $html, $customerMatch);
    $customer = isset($customerMatch[1]) ? trim($customerMatch[1]) : '';

    // Extract invoice details from table
    preg_match('/Invoice No\.\/td>\s*<td[^>]*>([^<]+)/', $html, $invNoMatch);
    $invoiceNoDisplay = isset($invNoMatch[1]) ? trim($invNoMatch[1]) : $invNo;

    preg_match('/Invoice Date<\/td>\s*<td[^>]*>([^<]+)/', $html, $dateMatch);
    $invDate = isset($dateMatch[1]) ? trim($dateMatch[1]) : '';

    preg_match('/Due Date<\/td>\s*<td[^>]*>([^<]+)/', $html, $dueMatch);
    $dueDate = isset($dueMatch[1]) ? trim($dueMatch[1]) : '';

    // Extract status label
    preg_match('/>(UNPAID|PAID|PARTIAL)</', $html, $statusMatch);
    $status = isset($statusMatch[1]) ? trim($statusMatch[1]) : '';

    // Extract totals from summary
    preg_match('/Total Due<\/td>\s*<td[^>]*>([0-9,.]+)/', $html, $totalMatch);
    $total = isset($totalMatch[1]) ? trim($totalMatch[1]) : '0.00';

    preg_match('/Balance<\/td>\s*<td[^>]*>([0-9,.]+)/', $html, $balanceMatch);
    $balance = isset($balanceMatch[1]) ? trim($balanceMatch[1]) : '0.00';

    preg_match('/Amount Paid<\/td>\s*<td[^>]*>([0-9,.]+)/', $html, $paidMatch);
    $amountPaid = isset($paidMatch[1]) ? trim($paidMatch[1]) : '0.00';

    // Extract items from table rows - match the new structure
    preg_match_all('/<td[^>]*>(\d+(?:\.\d+)?)<\/td>\s*<td[^>]*>\s*<div[^>]*>([^<]+)<\/div>\s*<div[^>]*>([^<]+)<\/td>\s*<td[^>]*>([0-9,.]+)<\/td>\s*<td[^>]*>([0-9,.]+)<\/td>/', $html, $itemMatches, PREG_SET_ORDER);

    // Create formatted PDF content
    $pdf = "%PDF-1.4\n";
    $pdf .= "1 0 obj\n<< /Type /Catalog /Pages 2 0 R >>\nendobj\n";
    $pdf .= "2 0 obj\n<< /Type /Pages /Kids [3 0 R] /Count 1 >>\nendobj\n";
    $pdf .= "3 0 obj\n<< /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] /Contents 4 0 R /Resources << /Font << /F1 5 0 R >> >> >>\nendobj\n";

    // Build content stream with proper formatting
    $content = "BT\n/F1 18 Tf\n50 760 Td\n(" . $this->_pdfEscape($companyName) . ") Tj\nET\n";
    $content .= "BT\n/F1 12 Tf\n50 740 Td\n(Invoice #" . $this->_pdfEscape($invoiceNoDisplay) . ") Tj\nET\n";

    $y = 710;

    // Status badge
    if ($status) {
      $content .= "BT\n/F1 10 Tf\n500 " . $y . " Td\n(" . $this->_pdfEscape($status) . ") Tj\nET\n";
    }

    // Invoice Details
    $y -= 25;
    $content .= "BT\n/F1 10 Tf\n50 " . $y . " Td\n(Invoice Date: " . $this->_pdfEscape($invDate) . ") Tj\nET\n";
    $content .= "BT\n/F1 10 Tf\n300 " . $y . " Td\n(Due Date: " . $this->_pdfEscape($dueDate) . ") Tj\nET\n";
    $y -= 20;

    // Bill To
    $content .= "BT\n/F1 11 Tf\n50 " . $y . " Td\n(Bill To: " . $this->_pdfEscape($customer) . ") Tj\nET\n";
    $y -= 30;

    // Items Header
    $content .= "BT\n/F1 9 Tf\n50 " . $y . " Td\n(Qty) Tj\nET\n";
    $content .= "BT\n/F1 9 Tf\n80 " . $y . " Td\n(Description) Tj\nET\n";
    $content .= "BT\n/F1 9 Tf\n380 " . $y . " Td\n(Unit Cost) Tj\nET\n";
    $content .= "BT\n/F1 9 Tf\n480 " . $y . " Td\n(Total) Tj\nET\n";
    $y -= 15;
    $content .= "BT\n/F1 9 Tf\n50 " . $y . " Td\n(------------------------------------------------------------------------------) Tj\nET\n";
    $y -= 12;

    // Items
    foreach ($itemMatches as $match) {
      if ($y < 200) break; // Page limit

      $qty = $this->_pdfEscape($match[1]);
      $desc = substr($this->_pdfEscape($match[2]), 0, 40);
      $breakdown = $this->_pdfEscape($match[3]);
      $unitCost = $this->_pdfEscape($match[4]);
      $lineTotal = $this->_pdfEscape($match[5]);

      $content .= "BT\n/F1 9 Tf\n50 " . $y . " Td\n(" . $qty . ") Tj\nET\n";
      $content .= "BT\n/F1 9 Tf\n80 " . $y . " Td\n(" . $desc . ") Tj\nET\n";
      $content .= "BT\n/F1 8 Tf\n80 " . ($y - 10) . " Td\n(" . $breakdown . ") Tj\nET\n";
      $content .= "BT\n/F1 9 Tf\n380 " . $y . " Td\n(" . $unitCost . ") Tj\nET\n";
      $content .= "BT\n/F1 9 Tf\n480 " . $y . " Td\n(" . $lineTotal . ") Tj\nET\n";
      $y -= 25;
    }

    $y -= 10;
    $content .= "BT\n/F1 9 Tf\n50 " . $y . " Td\n(------------------------------------------------------------------------------) Tj\nET\n";
    $y -= 20;

    // Summary (right aligned)
    $content .= "BT\n/F1 10 Tf\n380 " . $y . " Td\n(Total Due:) Tj\nET\n";
    $content .= "BT\n/F1 10 Tf\n480 " . $y . " Td\n(" . $total . ") Tj\nET\n";
    $y -= 15;
    $content .= "BT\n/F1 10 Tf\n380 " . $y . " Td\n(Amount Paid:) Tj\nET\n";
    $content .= "BT\n/F1 10 Tf\n480 " . $y . " Td\n(" . $amountPaid . ") Tj\nET\n";
    $y -= 15;
    $content .= "BT\n/F1 11 Tf\n380 " . $y . " Td\n(Balance:) Tj\nET\n";
    $content .= "BT\n/F1 11 Tf\n480 " . $y . " Td\n(" . $balance . ") Tj\nET\n";
    $y -= 30;

    // Footer
    $content .= "BT\n/F1 9 Tf\n306 " . $y . " Td\n(Thank you for your business!) Tj\nET\n";
    $y -= 12;
    $content .= "BT\n/F1 8 Tf\n306 " . $y . " Td\n(Generated from BERPS) Tj\nET\n";

    $contentLength = strlen($content);
    $pdf .= "4 0 obj\n<< /Length " . $contentLength . " >>\nstream\n" . $content . "\nendstream\nendobj\n";
    $pdf .= "5 0 obj\n<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>\nendobj\n";

    $offsets = [];
    $offsets[1] = strpos($pdf, "1 0 obj");
    $offsets[2] = strpos($pdf, "2 0 obj");
    $offsets[3] = strpos($pdf, "3 0 obj");
    $offsets[4] = strpos($pdf, "4 0 obj");
    $offsets[5] = strpos($pdf, "5 0 obj");

    $xrefOffset = strlen($pdf);
    $pdf .= "xref\n0 6\n0000000000 65535 f \n";
    foreach ($offsets as $i => $offset) {
      $pdf .= sprintf("%010d 00000 n \n", $offset);
    }

    $pdf .= "trailer\n<< /Size 6 /Root 1 0 R >>\nstartxref\n" . $xrefOffset . "\n%%EOF";

    return $pdf;
  }

  private function _pdfEscape($text)
  {
    return str_replace(['(', ')', '\\'], ['\\(', '\\)', '\\\\'], $text);
  }

  function voidInvoiceReport()
  {
    if ($this->session->userdata('level') === 'Admin') {
      $settingsID = $this->session->userdata('settingsID');
      $result['data'] = $this->CashModel->voidInvoicesList($settingsID);
      $this->_attachInvoiceItemsToCollection($result['data'], $settingsID);
      $this->load->view('void_invoice_report', $result);
    } else {
      $settingsID = $this->session->userdata('settingsID');
      $name = $this->session->userdata('name');
      $result['data'] = $this->CashModel->voidInvoicesListStaff($settingsID, $name);
      $this->_attachInvoiceItemsToCollection($result['data'], $settingsID);
      $this->load->view('void_invoice_report', $result);
    }
  }

  function voidPayment()
  {
    if (!$this->_is_admin_user()) {
      $this->session->set_flashdata('danger', 'Access denied. Only admins can void payments.');
      redirect('Page/paymentList');
      return;
    }

    $settingsID = $this->session->userdata('settingsID');
    $id = (int) $this->input->post('paymentID');
    $voidReason = trim((string) $this->input->post('voidReason', true));

    if (!$id) {
      $this->session->set_flashdata('danger', 'Invalid payment ID.');
      redirect('Page/paymentList');
      return;
    }

    // Get payment data
    $payment = $this->db
      ->where('paymentID', $id)
      ->where('settingsID', $settingsID)
      ->get('payments')
      ->row();

    if (!$payment) {
      $this->session->set_flashdata('danger', 'Payment record not found.');
      redirect('Page/paymentList');
      return;
    }

    // Update payment status to Voided
    $this->db->where('paymentID', $id);
    $this->db->where('settingsID', $settingsID);
    $this->db->update('payments', array(
      'ORStat' => 'Voided',
      'voidReason' => $voidReason ?: 'No reason provided',
      'voidDate' => date('Y-m-d H:i:s'),
      'voidBy' => $this->session->userdata('name') ?: 'System'
    ));

    // Sync invoice totals if payment was linked to an invoice
    if (!empty($payment->InvoiceNo)) {
      $this->_syncInvoicePaymentTotals($settingsID, (string) $payment->InvoiceNo);
    }

    $this->session->set_flashdata('success', 'Payment has been voided successfully.');
    redirect('Page/paymentList');
  }

  function voidPaymentReport()
  {
    if ($this->session->userdata('level') === 'Admin') {
      $settingsID = $this->session->userdata('settingsID');
      $result['data'] = $this->CashModel->voidPaymentsList($settingsID);
      $this->load->view('void_payment_report', $result);
    } else {
      $settingsID = $this->session->userdata('settingsID');
      $name = $this->session->userdata('name');
      $result['data'] = $this->CashModel->voidPaymentsListStaff($settingsID, $name);
      $this->load->view('void_payment_report', $result);
    }
  }

  function deletePayment()
  {
    if (!$this->_is_admin_user()) {
      redirect('Page/paymentList');
      return;
    }

    $id = $this->input->get('id');
    $settingsID = $this->session->userdata('settingsID');
    $payment = $this->db
      ->where('paymentID', $id)
      ->where('settingsID', $settingsID)
      ->get('payments')
      ->row();

    $this->db->where('paymentID', $id);
    $this->db->where('settingsID', $settingsID);
    $this->db->update('payments', array('ORStat' => 'Deleted'));

    if ($payment && !empty($payment->InvoiceNo)) {
      $this->_syncInvoicePaymentTotals($settingsID, (string) $payment->InvoiceNo);
    }
    redirect('Page/paymentList');
  }

  function deleteExpense()
  {
    $id = $this->input->get('id');

    if (!$id) {
      $this->session->set_flashdata('danger', 'Invalid expense ID.');
      redirect('Page/expensesList');
      return;
    }

    $this->db->where('expensesid', $id);
    $query = $this->db->get('expenses');

    if ($query->num_rows() === 0) {
      $this->session->set_flashdata('danger', 'Expense record not found.');
      redirect('Page/expensesList');
      return;
    }

    $this->db->where('expensesid', $id);
    $this->db->delete('expenses');

    $this->session->set_flashdata('success', 'Expense record has been deleted successfully.');
    redirect('Page/expensesList');
  }


  function expensesList()
  {
    $settingsID = $this->session->userdata('settingsID');
    $isAdmin = $this->session->userdata('level') === 'Admin';
    $name = $this->session->userdata('name');

    // Get date range from query params or default to today
    $fromDate = $this->input->get('from');
    $toDate = $this->input->get('to');

    // Default to current day if no dates provided
    if (empty($fromDate) || empty($toDate)) {
      $fromDate = date('Y-m-d');
      $toDate = date('Y-m-d');
    }

    // Validate dates
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fromDate) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $toDate)) {
      $fromDate = date('Y-m-d');
      $toDate = date('Y-m-d');
    }

    $result['fromDate'] = $fromDate;
    $result['toDate'] = $toDate;
    $result['isFiltered'] = ($this->input->get('from') !== null || $this->input->get('to') !== null);

    if ($isAdmin) {
      $result['data'] = $this->CashModel->expensesListRange($fromDate, $toDate, $settingsID);
      $result['data1'] = $this->CashModel->totalExpensesRange($fromDate, $toDate, $settingsID);
    } else {
      $result['data'] = $this->CashModel->expensesListRangeStaff($fromDate, $toDate, $settingsID, $name);
      $result['data1'] = $this->CashModel->totalExpensesRangeStaff($fromDate, $toDate, $settingsID, $name);
    }
    $result['expenseCategories'] = $this->CashModel->getExpenseCategories($settingsID);

    // Calculate yearly expense statistics
    $currentYear = date('Y');
    $previousYear = date('Y') - 1;

    // Get current year expenses
    $currentYearFrom = $currentYear . '-01-01';
    $currentYearTo = $currentYear . '-12-31';

    if ($isAdmin) {
      $currentYearData = $this->CashModel->totalExpensesRange($currentYearFrom, $currentYearTo, $settingsID);
      $previousYearData = $this->CashModel->totalExpensesRange(($previousYear . '-01-01'), ($previousYear . '-12-31'), $settingsID);
    } else {
      $currentYearData = $this->CashModel->totalExpensesRangeStaff($currentYearFrom, $currentYearTo, $settingsID, $name);
      $previousYearData = $this->CashModel->totalExpensesRangeStaff(($previousYear . '-01-01'), ($previousYear . '-12-31'), $settingsID, $name);
    }

    $currentYearExpenses = !empty($currentYearData) && isset($currentYearData[0]->Total) ? (float) $currentYearData[0]->Total : 0;
    $previousYearExpenses = !empty($previousYearData) && isset($previousYearData[0]->Total) ? (float) $previousYearData[0]->Total : 0;

    // Calculate yearly average (per month)
    $yearlyAverage = $currentYearExpenses > 0 ? $currentYearExpenses / 12 : 0;

    // Calculate year-over-year change percentage
    $yearOverYearChange = 0;
    if ($previousYearExpenses > 0) {
      $yearOverYearChange = (($currentYearExpenses - $previousYearExpenses) / $previousYearExpenses) * 100;
    }

    $result['currentYearExpenses'] = $currentYearExpenses;
    $result['previousYearExpenses'] = $previousYearExpenses;
    $result['yearlyAverage'] = $yearlyAverage;
    $result['yearOverYearChange'] = $yearOverYearChange;

    $this->load->view('expenses_list', $result);
  }

  function getYearlyExpenseDetails()
  {
    header('Content-Type: application/json');

    $year = $this->input->post('year');
    $type = $this->input->post('type');

    if (!$year) {
      echo json_encode(['success' => false, 'message' => 'Year is required']);
      return;
    }

    $settingsID = $this->session->userdata('settingsID');
    $isAdmin = $this->session->userdata('level') === 'Admin';
    $name = $this->session->userdata('name');

    // Determine which year to show based on type
    $selectedYear = $year;
    if ($type === 'previous-year') {
      $selectedYear = $year;
    }

    // Get expense data for the selected year
    $yearFrom = $selectedYear . '-01-01';
    $yearTo = $selectedYear . '-12-31';

    if ($isAdmin) {
      $expenses = $this->CashModel->expensesListRange($yearFrom, $yearTo, $settingsID);
    } else {
      $expenses = $this->CashModel->expensesListRangeStaff($yearFrom, $yearTo, $settingsID, $name);
    }

    // Calculate monthly breakdown
    $monthlyBreakdown = [];
    $categoryTotals = [];
    $totalExpenses = 0;

    // Initialize months
    for ($month = 1; $month <= 12; $month++) {
      $monthlyBreakdown[$month] = [
        'month' => date('F', mktime(0, 0, 0, $month, 1, $selectedYear)),
        'amount' => 0,
        'count' => 0
      ];
    }

    // Process expenses
    if (!empty($expenses)) {
      foreach ($expenses as $expense) {
        $month = (int) date('n', strtotime($expense->ExpenseDate));
        $amount = (float) $expense->Amount;
        $category = trim($expense->Category);

        // Update monthly breakdown
        if (isset($monthlyBreakdown[$month])) {
          $monthlyBreakdown[$month]['amount'] += $amount;
          $monthlyBreakdown[$month]['count']++;
        }

        // Update category totals
        if (!isset($categoryTotals[$category])) {
          $categoryTotals[$category] = 0;
        }
        $categoryTotals[$category] += $amount;

        $totalExpenses += $amount;
      }
    }

    // Format monthly breakdown
    $formattedMonthly = [];
    foreach ($monthlyBreakdown as $month) {
      $formattedMonthly[] = [
        'month' => $month['month'],
        'amount' => $month['amount'],
        'amountFormatted' => number_format($month['amount'], 2),
        'count' => $month['count']
      ];
    }

    // Format category breakdown
    $formattedCategories = [];
    arsort($categoryTotals); // Sort by amount descending
    foreach ($categoryTotals as $category => $amount) {
      $percentage = $totalExpenses > 0 ? ($amount / $totalExpenses) * 100 : 0;
      $formattedCategories[] = [
        'category' => $category,
        'amount' => $amount,
        'amountFormatted' => number_format($amount, 2),
        'percentage' => number_format($percentage, 1)
      ];
    }

    // Calculate monthly average
    $monthlyAverage = $totalExpenses > 0 ? $totalExpenses / 12 : 0;

    $response = [
      'success' => true,
      'data' => [
        'totalExpenses' => $totalExpenses,
        'totalExpensesFormatted' => number_format($totalExpenses, 2),
        'monthlyAverage' => $monthlyAverage,
        'monthlyAverageFormatted' => number_format($monthlyAverage, 2),
        'totalRecords' => count($expenses),
        'monthlyBreakdown' => $formattedMonthly,
        'categoryBreakdown' => $formattedCategories,
        'year' => $selectedYear
      ]
    ];

    echo json_encode($response);
  }

  function getDashboardDetails()
  {
    redirect('Pos/getDashboardDetails');
  }
  function exportYearlyExpenses()
  {
    $year = $this->input->get('year');

    if (!$year) {
      $year = date('Y');
    }

    $settingsID = $this->session->userdata('settingsID');
    $isAdmin = $this->session->userdata('level') === 'Admin';
    $name = $this->session->userdata('name');

    // Get expense data for the selected year
    $yearFrom = $year . '-01-01';
    $yearTo = $year . '-12-31';

    if ($isAdmin) {
      $expenses = $this->CashModel->expensesListRange($yearFrom, $yearTo, $settingsID);
    } else {
      $expenses = $this->CashModel->expensesListRangeStaff($yearFrom, $yearTo, $settingsID, $name);
    }

    // Calculate monthly breakdown
    $monthlyBreakdown = [];
    $categoryTotals = [];
    $totalExpenses = 0;

    // Initialize months
    for ($month = 1; $month <= 12; $month++) {
      $monthlyBreakdown[$month] = [
        'month' => date('F', mktime(0, 0, 0, $month, 1, (int)$year)),
        'amount' => 0,
        'count' => 0
      ];
    }

    // Process expenses
    if (!empty($expenses)) {
      foreach ($expenses as $expense) {
        $month = (int) date('n', strtotime($expense->ExpenseDate));
        $amount = (float) $expense->Amount;
        $category = trim($expense->Category);

        // Update monthly breakdown
        if (isset($monthlyBreakdown[$month])) {
          $monthlyBreakdown[$month]['amount'] += $amount;
          $monthlyBreakdown[$month]['count']++;
        }

        // Update category totals
        if (!isset($categoryTotals[$category])) {
          $categoryTotals[$category] = 0;
        }
        $categoryTotals[$category] += $amount;

        $totalExpenses += $amount;
      }
    }

    // Prepare CSV data
    $csv_data = [];
    $csv_data[] = ['Yearly Expense Report - ' . $year];
    $csv_data[] = ['Generated on: ' . date('F j, Y h:i A')];
    $csv_data[] = [];
    $csv_data[] = ['Summary'];
    $csv_data[] = ['Total Expenses', number_format($totalExpenses, 2)];
    $csv_data[] = ['Monthly Average', number_format($totalExpenses / 12, 2)];
    $csv_data[] = ['Total Records', count($expenses)];
    $csv_data[] = [];
    $csv_data[] = ['Monthly Breakdown'];
    $csv_data[] = ['Month', 'Amount', 'Count'];

    foreach ($monthlyBreakdown as $month) {
      $csv_data[] = [
        $month['month'],
        number_format($month['amount'], 2),
        $month['count']
      ];
    }

    $csv_data[] = [];
    $csv_data[] = ['Category Breakdown'];
    $csv_data[] = ['Category', 'Amount', 'Percentage'];

    arsort($categoryTotals);
    foreach ($categoryTotals as $category => $amount) {
      $percentage = $totalExpenses > 0 ? ($amount / $totalExpenses) * 100 : 0;
      $csv_data[] = [
        $category,
        number_format($amount, 2),
        number_format($percentage, 1) . '%'
      ];
    }

    $csv_data[] = [];
    $csv_data[] = ['Detailed Expense Records'];
    $csv_data[] = ['Date', 'Description', 'Amount', 'Category', 'Responsible'];

    if (!empty($expenses)) {
      foreach ($expenses as $expense) {
        $csv_data[] = [
          $expense->ExpenseDate,
          $expense->Description,
          number_format($expense->Amount, 2),
          $expense->Category,
          $expense->Responsible
        ];
      }
    }

    // Generate CSV file
    $filename = 'yearly_expenses_' . $year . '_' . date('Y-m-d_H-i-s') . '.csv';

    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header('Expires: 0');
    header('Pragma: public');

    $output = fopen('php://output', 'w');

    foreach ($csv_data as $row) {
      fputcsv($output, $row);
    }

    fclose($output);
    exit;
  }

  function expensesReport()
  {
    $settingsID = $this->session->userdata('settingsID');
    $isAdmin = $this->session->userdata('level') === 'Admin';
    $name = $this->session->userdata('name');

    // Get date range from query params or default to current month
    $fromDate = $this->input->get('from');
    $toDate = $this->input->get('to');

    // Default to current month if no dates provided
    if (empty($fromDate) || empty($toDate)) {
      $fromDate = date('Y-m-01');
      $toDate = date('Y-m-t');
    }

    // Validate dates
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fromDate) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $toDate)) {
      $fromDate = date('Y-m-01');
      $toDate = date('Y-m-t');
    }

    $result['fromDate'] = $fromDate;
    $result['toDate'] = $toDate;

    if ($isAdmin) {
      $result['data'] = $this->CashModel->expensesListRange($fromDate, $toDate, $settingsID);
      $result['data1'] = $this->CashModel->totalExpensesRange($fromDate, $toDate, $settingsID);
    } else {
      $result['data'] = $this->CashModel->expensesListRangeStaff($fromDate, $toDate, $settingsID, $name);
      $result['data1'] = $this->CashModel->totalExpensesRangeStaff($fromDate, $toDate, $settingsID, $name);
    }

    // Load business details for report header
    $result['business'] = $this->CashModel->businessDetails($settingsID);

    $this->load->view('expenses_report', $result);
  }

  function topClientsReport()
  {
    if ($this->session->userdata('level') !== 'Admin') {
      echo "Access Denied";
      return;
    }

    $settingsID = $this->session->userdata('settingsID');

    // Get date range from query params or default to current year
    $dateFrom = $this->input->get('from');
    $dateTo = $this->input->get('to');
    $limit = (int) $this->input->get('limit') ?: 20;

    // Default to current year if no dates provided
    if (empty($dateFrom) || empty($dateTo)) {
      $dateFrom = date('Y-01-01');
      $dateTo = date('Y-m-d');
    }

    // Validate dates
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateFrom) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateTo)) {
      $dateFrom = date('Y-01-01');
      $dateTo = date('Y-m-d');
    }

    // Ensure from date is not after to date
    if (strtotime($dateFrom) > strtotime($dateTo)) {
      $swap = $dateFrom;
      $dateFrom = $dateTo;
      $dateTo = $swap;
    }

    // Get top clients data
    $topClients = $this->CashModel->topClientsByPayments($settingsID, $dateFrom, $dateTo, $limit);

    // Calculate totals
    $totalPaid = 0;
    $totalTax = 0;
    $totalGross = 0;
    $totalPayments = 0;

    foreach ($topClients as $client) {
      $totalPaid += (float) ($client->totalAmountPaid ?? 0);
      $totalTax += (float) ($client->totalTaxAmount ?? 0);
      $totalGross += (float) ($client->totalGrossAmount ?? 0);
      $totalPayments += (int) ($client->paymentCount ?? 0);
    }

    $result = array(
      'fromDate' => $dateFrom,
      'toDate' => $dateTo,
      'limit' => $limit,
      'topClients' => $topClients,
      'totals' => array(
        'totalPaid' => $totalPaid,
        'totalTax' => $totalTax,
        'totalGross' => $totalGross,
        'totalPayments' => $totalPayments,
      ),
      'business' => $this->CashModel->businessDetails($settingsID),
      'generatedAt' => date('F j, Y h:i A'),
    );

    $this->load->view('top_clients_report', $result);
  }

  function addExpenses()
  {
    if ($this->input->method() === 'post') {
      $settingsID = $this->session->userdata('settingsID');
      $name = $this->session->userdata('name');

      $ExpenseDate = $this->input->post('ExpenseDate', true);
      $Description = trim($this->input->post('Description', true));
      $Amount = $this->input->post('Amount');
      $Responsible = trim($this->input->post('Responsible', true));
      $Category = $this->input->post('Category', true);

      // Validation
      if (empty($ExpenseDate) || empty($Description) || empty($Amount) || empty($Responsible) || empty($Category)) {
        $this->session->set_flashdata('danger', 'Please fill in all required fields.');
        redirect('Page/expensesList');
        return;
      }

      if (!is_numeric($Amount) || $Amount < 0) {
        $this->session->set_flashdata('danger', 'Amount must be a positive number.');
        redirect('Page/expensesList');
        return;
      }

      // Handle file upload
      $attachmentPath = null;
      if (!empty($_FILES['attachment']['name'])) {
        $file = $_FILES['attachment'];

        // Check for upload errors
        if ($file['error'] !== UPLOAD_ERR_OK) {
          $this->session->set_flashdata('danger', 'File upload error. Please try again.');
          redirect('Page/expensesList');
          return;
        }

        // Validate file size (5MB max)
        if ($file['size'] > 5 * 1024 * 1024) {
          $this->session->set_flashdata('danger', 'File size exceeds 5MB limit.');
          redirect('Page/expensesList');
          return;
        }

        // Validate file type
        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'application/pdf'];
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'pdf'];

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if (!in_array($mimeType, $allowedTypes) || !in_array($fileExtension, $allowedExtensions)) {
          $this->session->set_flashdata('danger', 'Invalid file type. Only JPG, JPEG, PNG, and PDF files are allowed.');
          redirect('Page/expensesList');
          return;
        }

        // Generate filename with date format: yyyy-mm-dd-filename
        $originalFileName = pathinfo($file['name'], PATHINFO_FILENAME);
        $cleanFileName = preg_replace('/[^a-zA-Z0-9_-]/', '', $originalFileName);
        $datePrefix = date('Y-m-d');
        $newFileName = $datePrefix . '-' . $cleanFileName . '.' . $fileExtension;
        $uploadPath = 'uploads/expenses/';

        // Create directory if it doesn't exist
        if (!is_dir($uploadPath)) {
          mkdir($uploadPath, 0755, true);
        }

        $fullPath = $uploadPath . $newFileName;

        // Move uploaded file
        if (move_uploaded_file($file['tmp_name'], $fullPath)) {
          $attachmentPath = $fullPath;
        } else {
          $this->session->set_flashdata('danger', 'Failed to upload file. Please try again.');
          redirect('Page/expensesList');
          return;
        }
      }

      $data = array(
        'Description' => $Description,
        'Amount' => $Amount,
        'Responsible' => $Responsible,
        'ExpenseDate' => $ExpenseDate,
        'Category' => $Category,
        'settingsID' => $settingsID,
        'processedBy' => $name,
        'attachment' => $attachmentPath
      );

      $this->db->insert('expenses', $data);

      if ($this->db->affected_rows() > 0) {
        $this->session->set_flashdata('success', 'Expense has been added successfully.');
      } else {
        $this->session->set_flashdata('danger', 'Failed to add expense. Please try again.');
      }

      redirect('Page/expensesList');
    }

    // Display the add form for GET requests
    $settingsID = $this->session->userdata('settingsID');
    $result['expenseCategories'] = $this->CashModel->getExpenseCategories($settingsID);
    $this->load->view('add_expense', $result);
  }

  function downloadExpenseTemplate()
  {
    // Generate CSV template for bulk expense upload
    $filename = 'expense_upload_template.csv';

    // Set headers for CSV download
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Pragma: no-cache');
    header('Expires: 0');

    // Create output stream
    $output = fopen('php://output', 'w');

    // Add UTF-8 BOM for Excel compatibility
    fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

    // Add header row
    fputcsv($output, array('Date', 'Amount', 'Description', 'Category', 'Responsible'));

    // Add sample data rows
    fputcsv($output, array('2025-01-15', '1500.00', 'Office Supplies', 'Office', 'John Doe'));
    fputcsv($output, array('2025-01-16', '2500.50', 'Transportation', 'Travel', 'Jane Smith'));
    fputcsv($output, array('2025-01-17', '500.00', 'Client Meeting Lunch', 'Meals', 'John Doe'));

    fclose($output);
    exit;
  }

  function bulkUploadExpenses()
  {
    if ($this->input->method() !== 'post') {
      redirect('Page/expensesList');
      return;
    }

    $settingsID = $this->session->userdata('settingsID');
    $name = $this->session->userdata('name');
    $defaultCategory = trim((string) $this->input->post('default_category', true));

    // Check if file was uploaded
    if (!isset($_FILES['expense_file']) || $_FILES['expense_file']['error'] !== UPLOAD_ERR_OK) {
      $this->session->set_flashdata('danger', 'No file uploaded or upload error occurred.');
      redirect('Page/expensesList');
      return;
    }

    $file = $_FILES['expense_file'];
    $fileTmpPath = $file['tmp_name'];
    $fileName = $file['name'];
    $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

    // Validate file extension
    $allowedExtensions = array('csv', 'xlsx', 'xls');
    if (!in_array($fileExtension, $allowedExtensions)) {
      $this->session->set_flashdata('danger', 'Invalid file format. Please upload CSV or Excel (.xlsx/.xls) files only.');
      redirect('Page/expensesList');
      return;
    }

    // Validate file size (max 5MB)
    if ($file['size'] > 5 * 1024 * 1024) {
      $this->session->set_flashdata('danger', 'File size exceeds 5MB limit.');
      redirect('Page/expensesList');
      return;
    }

    $processed = 0;
    $failed = 0;
    $errors = array();

    // Process CSV files directly
    if ($fileExtension === 'csv') {
      $handle = fopen($fileTmpPath, 'r');
      if (!$handle) {
        $this->session->set_flashdata('danger', 'Failed to read the uploaded file.');
        redirect('Page/expensesList');
        return;
      }

      // Skip BOM if present
      $bom = fread($handle, 3);
      if ($bom !== chr(0xEF) . chr(0xBB) . chr(0xBF)) {
        rewind($handle);
      }

      // Read header row
      $header = fgetcsv($handle);
      if (!$header) {
        fclose($handle);
        $this->session->set_flashdata('danger', 'CSV file appears to be empty or invalid.');
        redirect('Page/expensesList');
        return;
      }

      // Normalize header
      $header = array_map('strtolower', array_map('trim', $header));

      // Map column indices
      $dateIndex = array_search('date', $header);
      $amountIndex = array_search('amount', $header);
      $descIndex = array_search('description', $header);
      $categoryIndex = array_search('category', $header);
      $responsibleIndex = array_search('responsible', $header);

      // Validate required columns exist
      if ($dateIndex === false || $amountIndex === false || $descIndex === false) {
        fclose($handle);
        $this->session->set_flashdata('danger', 'CSV must contain Date, Amount, and Description columns.');
        redirect('Page/expensesList');
        return;
      }

      $rowNumber = 1;
      while (($row = fgetcsv($handle)) !== false) {
        $rowNumber++;

        // Skip empty rows
        if (empty(array_filter($row, 'strlen'))) {
          continue;
        }

        $expenseDate = isset($row[$dateIndex]) ? trim($row[$dateIndex]) : '';
        $amount = isset($row[$amountIndex]) ? trim($row[$amountIndex]) : '';
        $description = isset($row[$descIndex]) ? trim($row[$descIndex]) : '';
        $category = ($categoryIndex !== false && isset($row[$categoryIndex])) ? trim($row[$categoryIndex]) : '';
        $responsible = ($responsibleIndex !== false && isset($row[$responsibleIndex])) ? trim($row[$responsibleIndex]) : '';

        // Use default category if specified and row category is empty
        if (empty($category) && !empty($defaultCategory)) {
          $category = $defaultCategory;
        }

        // Validate row data
        if (empty($expenseDate) || empty($amount) || empty($description)) {
          $failed++;
          $errors[] = "Row {$rowNumber}: Missing required fields";
          continue;
        }

        // Validate date format
        $dateObj = DateTime::createFromFormat('Y-m-d', $expenseDate);
        if (!$dateObj || $dateObj->format('Y-m-d') !== $expenseDate) {
          // Try other common formats
          $dateObj = DateTime::createFromFormat('m/d/Y', $expenseDate);
          if ($dateObj) {
            $expenseDate = $dateObj->format('Y-m-d');
          } else {
            $dateObj = DateTime::createFromFormat('d/m/Y', $expenseDate);
            if ($dateObj) {
              $expenseDate = $dateObj->format('Y-m-d');
            }
          }
        }

        if (!$dateObj) {
          $failed++;
          $errors[] = "Row {$rowNumber}: Invalid date format '{$expenseDate}'. Use YYYY-MM-DD.";
          continue;
        }

        // Validate amount is numeric
        if (!is_numeric($amount) || $amount < 0) {
          $failed++;
          $errors[] = "Row {$rowNumber}: Invalid amount '{$amount}'. Must be a positive number.";
          continue;
        }

        // Insert expense
        $data = array(
          'Description' => $description,
          'Amount' => $amount,
          'Responsible' => !empty($responsible) ? $responsible : $name,
          'ExpenseDate' => $expenseDate,
          'Category' => !empty($category) ? $category : 'Uncategorized',
          'settingsID' => $settingsID,
          'processedBy' => $name
        );

        $this->db->insert('expenses', $data);
        if ($this->db->affected_rows() > 0) {
          $processed++;
        } else {
          $failed++;
          $errors[] = "Row {$rowNumber}: Database insert failed";
        }
      }

      fclose($handle);
    } else {
      // For Excel files, inform user to convert to CSV
      $this->session->set_flashdata('danger', 'Excel files (.xlsx/.xls) require PHPSpreadsheet library. Please save your file as CSV format and upload again.');
      redirect('Page/expensesList');
      return;
    }

    // Set result message
    if ($processed > 0) {
      $message = "Successfully imported {$processed} expense(s).";
      if ($failed > 0) {
        $message .= " {$failed} record(s) failed.";
        // Store first few errors in flashdata
        $errorSummary = array_slice($errors, 0, 5);
        foreach ($errorSummary as $error) {
          $this->session->set_flashdata('danger', $error);
        }
        if (count($errors) > 5) {
          $this->session->set_flashdata('danger', '... and ' . (count($errors) - 5) . ' more errors');
        }
      }
      $this->session->set_flashdata('success', $message);
    } else {
      $this->session->set_flashdata('danger', 'No expenses were imported. Please check your file format.');
    }

    redirect('Page/expensesList');
  }

  function updateClient()
  {
    if (!$this->_is_admin_user()) {
      redirect('Page/clientList');
      return;
    }

    $id = $this->input->get('id');
    $result['data'] = $this->CashModel->updateClient($id);
    $this->load->view('update_client', $result);
    if ($this->input->post('updateclient')) {
      $CustID = $this->input->get('id');
      $Customer = $this->input->post('Customer');
      $Address = $this->input->post('Address');
      $Contact = $this->input->post('Contact');
      $ContactPerson = $this->input->post('ContactPerson');
      $CompanyEmail = $this->input->post('CompanyEmail');

      $que = $this->db->query("update customers set Customer='$Customer',Address='$Address',ContactNos='$Contact',ContactPerson='$ContactPerson',CompanyEmail='$CompanyEmail' where CustID='" . $id . "'");
      redirect('Page/clientList');
    }
  }



  function updateEmployee()
  {
    $id = $this->input->get('id');
    if (!$this->_can_edit_employee($id)) {
      $currentFilter = trim((string) $this->input->get('status'));
      if ($currentFilter !== '') {
        redirect('Page/employeeList?status=' . urlencode($currentFilter));
      } else {
        redirect('Page/employeeList');
      }
      return;
    }

    $isOwnProfile = !$this->_is_admin_user();
    if ($this->input->post('updateemployee')) {
      $settingsID = (int) $this->session->userdata('settingsID');
      $fName = trim((string) $this->input->post('fName'));
      $mName = trim((string) $this->input->post('mName'));
      $lName = trim((string) $this->input->post('lName'));
      $email = strtolower(trim((string) $this->input->post('email')));
      $supportChatView = (int) $this->input->post('support_chat_view') ? 1 : 0;
      $supportChatReply = (int) $this->input->post('support_chat_reply') ? 1 : 0;
      $supportChatView = (int) $this->input->post('support_chat_view') ? 1 : 0;
      $supportChatReply = (int) $this->input->post('support_chat_reply') ? 1 : 0;
      $dateHired = $this->input->post('dateHired');
      $empPosition = trim((string) $this->input->post('empPosition'));
      $department = trim((string) $this->input->post('department'));
      $bDate = $this->input->post('bDate');
      $empStat = trim((string) $this->input->post('empStat'));
      $statusChangeReason = trim((string) $this->input->post('statusChangeReason'));

      if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $this->session->set_flashdata('danger', 'Please enter a valid employee email.');
        redirect('Page/updateEmployee?id=' . rawurlencode((string) $id));
        return;
      }

      $this->db->trans_begin();

      // Get current employee status for history tracking
      $currentEmployee = $this->db->where('empID', $id)->get('employee')->row();
      $oldStatus = $currentEmployee ? trim((string) $currentEmployee->empStat) : '';

      $this->db
        ->where('empID', $id)
        ->update('employee', array(
          'fName' => $fName,
          'mName' => $mName,
          'lName' => $lName,
          'email' => $email,
          'dateHired' => $dateHired,
          'empPosition' => $empPosition,
          'department' => $department,
          'bDate' => $bDate,
          'empStat' => $empStat,
        ));

      // Track status change if status changed
      if ($oldStatus !== '' && $oldStatus !== $empStat && $this->db->table_exists('employee_status_history')) {
        $currentUserId = (int) ($this->session->userdata('user_id') ?? 0);
        $currentUsername = trim((string) ($this->session->userdata('username') ?? 'system'));
        
        $this->db->insert('employee_status_history', array(
          'empID' => $id,
          'settingsID' => $settingsID,
          'old_status' => $oldStatus,
          'new_status' => $empStat,
          'changed_by' => $currentUserId,
          'changed_by_username' => $currentUsername,
          'change_reason' => $statusChangeReason !== '' ? $statusChangeReason : null,
          'change_date' => date('Y-m-d H:i:s'),
        ));
      }

      $syncResult = $this->_sync_employee_user_account($settingsID, $id, $fName, $mName, $lName, $email, (string) $bDate, $supportChatView, $supportChatReply);
      if (!$syncResult[0]) {
        $this->db->trans_rollback();
        $this->session->set_flashdata('danger', $syncResult[1] !== '' ? $syncResult[1] : 'Unable to update the employee user account.');
        redirect('Page/updateEmployee?id=' . rawurlencode((string) $id));
        return;
      }

      if (!$isOwnProfile) {
        $this->_sync_employee_support_department($settingsID, $email, $department);

        $this->PayrollModel->savePayrollProfile($settingsID, $id, array(
          'monthlySalary' => $this->input->post('monthlySalary'),
          'philhealthAmount' => $this->input->post('philhealthAmount'),
          'sssAmount' => $this->input->post('sssAmount'),
          'pagibigAmount' => $this->input->post('pagibigAmount'),
          'notes' => $this->input->post('payrollNotes'),
          'payrollStatus' => $this->input->post('payrollStatus') ?: 'active',
        ));
      }

      $this->db->trans_commit();
      $this->session->set_flashdata('success', 'Employee updated successfully.');

      redirect('Page/updateEmployee?id=' . rawurlencode((string) $id));
      return;
    }

    $settingsID = (int) $this->session->userdata('settingsID');
    $result['data'] = $this->CashModel->updateEmployee($id);
    if (!empty($result['data']) && empty($result['data'][0]->email)) {
      $linkedUser = $this->_employee_account_row($settingsID, $id);
      if ($linkedUser && !empty($linkedUser->email)) {
        $result['data'][0]->email = $linkedUser->email;
      } elseif ($linkedUser && !empty($linkedUser->username) && filter_var($linkedUser->username, FILTER_VALIDATE_EMAIL)) {
        $result['data'][0]->email = $linkedUser->username;
      }
      $result['supportChatView'] = isset($linkedUser->support_chat_view) ? (int) $linkedUser->support_chat_view : 1;
      $result['supportChatReply'] = isset($linkedUser->support_chat_reply) ? (int) $linkedUser->support_chat_reply : 1;
    } else {
      $result['supportChatView'] = 1;
      $result['supportChatReply'] = 1;
    }
    $result['payrollProfile'] = $this->PayrollModel->getPayrollProfile($settingsID, $id);
    
    // Fetch employee status history
    if ($this->db->table_exists('employee_status_history')) {
      $this->db->where('empID', $id);
      $this->db->where('settingsID', $settingsID);
      $this->db->order_by('change_date', 'DESC');
      $result['statusHistory'] = $this->db->get('employee_status_history')->result();
    } else {
      $result['statusHistory'] = array();
    }
    
    $this->load->view('update_employee', $result);
  }

  function updateExpenses()
  {
    $id = $this->input->get('id');

    // If no ID provided, redirect to list
    if (!$id && $this->input->method() !== 'post') {
      redirect('Page/expensesList');
      return;
    }

    // Handle POST request (update operation)
    if ($this->input->method() === 'post') {
      $id = $this->input->post('id');
      $ExpenseDate = $this->input->post('ExpenseDate', true);
      $Description = trim($this->input->post('Description', true));
      $Amount = $this->input->post('Amount');
      $Responsible = trim($this->input->post('Responsible', true));
      $Category = $this->input->post('Category', true);
      $removeAttachment = $this->input->post('remove_attachment');

      // Validation
      if (empty($ExpenseDate) || empty($Description) || empty($Amount) || empty($Responsible) || empty($Category)) {
        $this->session->set_flashdata('danger', 'Please fill in all required fields.');
        redirect('Page/updateExpenses?id=' . $id);
        return;
      }

      if (!is_numeric($Amount) || $Amount < 0) {
        $this->session->set_flashdata('danger', 'Amount must be a positive number.');
        redirect('Page/updateExpenses?id=' . $id);
        return;
      }

      // Get current expense to check existing attachment
      $currentExpense = $this->db->get_where('expenses', ['expensesid' => $id])->row();
      if (!$currentExpense) {
        $this->session->set_flashdata('danger', 'Expense record not found.');
        redirect('Page/expensesList');
        return;
      }

      // Handle attachment updates
      $attachmentPath = $currentExpense->attachment; // Keep current attachment by default

      // Remove attachment if checkbox is checked
      if ($removeAttachment == '1') {
        // Delete old file if it exists
        if (!empty($currentExpense->attachment) && file_exists($currentExpense->attachment)) {
          unlink($currentExpense->attachment);
        }
        $attachmentPath = null;
      }

      // Handle new file upload
      if (!empty($_FILES['attachment']['name'])) {
        $file = $_FILES['attachment'];

        // Check for upload errors
        if ($file['error'] !== UPLOAD_ERR_OK) {
          $this->session->set_flashdata('danger', 'File upload error. Please try again.');
          redirect('Page/updateExpenses?id=' . $id);
          return;
        }

        // Validate file size (5MB max)
        if ($file['size'] > 5 * 1024 * 1024) {
          $this->session->set_flashdata('danger', 'File size exceeds 5MB limit.');
          redirect('Page/updateExpenses?id=' . $id);
          return;
        }

        // Validate file type
        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'application/pdf'];
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'pdf'];

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if (!in_array($mimeType, $allowedTypes) || !in_array($fileExtension, $allowedExtensions)) {
          $this->session->set_flashdata('danger', 'Invalid file type. Only JPG, JPEG, PNG, and PDF files are allowed.');
          redirect('Page/updateExpenses?id=' . $id);
          return;
        }

        // Generate filename with date format: yyyy-mm-dd-filename
        $originalFileName = pathinfo($file['name'], PATHINFO_FILENAME);
        $cleanFileName = preg_replace('/[^a-zA-Z0-9_-]/', '', $originalFileName);
        $datePrefix = date('Y-m-d');
        $newFileName = $datePrefix . '-' . $cleanFileName . '.' . $fileExtension;
        $uploadPath = 'uploads/expenses/';

        // Create directory if it doesn't exist
        if (!is_dir($uploadPath)) {
          mkdir($uploadPath, 0755, true);
        }

        $fullPath = $uploadPath . $newFileName;

        // Delete old file if it exists
        if (!empty($currentExpense->attachment) && file_exists($currentExpense->attachment)) {
          unlink($currentExpense->attachment);
        }

        // Move uploaded file
        if (move_uploaded_file($file['tmp_name'], $fullPath)) {
          $attachmentPath = $fullPath;
        } else {
          $this->session->set_flashdata('danger', 'Failed to upload file. Please try again.');
          redirect('Page/updateExpenses?id=' . $id);
          return;
        }
      }

      $data = array(
        'Description' => $Description,
        'Amount' => $Amount,
        'Responsible' => $Responsible,
        'ExpenseDate' => $ExpenseDate,
        'Category' => $Category,
        'attachment' => $attachmentPath
      );

      $this->db->where('expensesid', $id);
      $this->db->update('expenses', $data);

      if ($this->db->affected_rows() >= 0) {
        $this->session->set_flashdata('success', 'Expense has been updated successfully.');
      } else {
        $this->session->set_flashdata('danger', 'Failed to update expense. Please try again.');
      }

      redirect('Page/expensesList');
      return;
    }

    // Handle GET request (display form)
    $result['data'] = $this->CashModel->updateExpenses($id);
    $settingsID = $this->session->userdata('settingsID');
    $result['expenseCategories'] = $this->CashModel->getExpenseCategories($settingsID);

    if (empty($result['data'])) {
      $this->session->set_flashdata('danger', 'Expense record not found.');
      redirect('Page/expensesList');
      return;
    }

    $this->load->view('update_expense', $result);
  }

  function printExpense()
  {
    $id = $this->input->get('id');

    if (!$id) {
      $this->session->set_flashdata('danger', 'Invalid expense ID.');
      redirect('Page/expensesList');
      return;
    }

    $settingsID = $this->session->userdata('settingsID');

    // Load expense data
    $expenseData = $this->CashModel->updateExpenses($id);

    if (empty($expenseData)) {
      $this->session->set_flashdata('danger', 'Expense record not found.');
      redirect('Page/expensesList');
      return;
    }

    // Verify expense belongs to current settings
    if ($expenseData[0]->settingsID != $settingsID) {
      $this->session->set_flashdata('danger', 'Access denied.');
      redirect('Page/expensesList');
      return;
    }

    // Load business details
    $businessDetails = $this->CashModel->businessDetails($settingsID);

    $result['expense'] = $expenseData[0];
    $result['business'] = !empty($businessDetails) ? $businessDetails[0] : null;
    $result['autoPrint'] = $this->input->get('print') == '1' || strtolower((string) $this->input->get('autoprint')) === 'true';

    $this->load->view('expense_print', $result);
  }

  public function addProject()
  {
    if ($this->input->method() === 'post') {
      $settingsID = $this->session->userdata('settingsID');

      $projectDescription = trim($this->input->post('projectDescription', true));
      $projectCategory = trim($this->input->post('projectCategory', true));
      $contractDate = $this->input->post('contractDate', true);
      $projectCostRaw = (string) $this->input->post('projectCost', true);
      $projectCost = $projectCostRaw !== '' ? (float) preg_replace('/[^\d.]/', '', $projectCostRaw) : 0;
      $contactPerson = trim($this->input->post('contactPerson', true));
      $otherDetails = trim($this->input->post('otherDetails', true));
      $CustID = trim($this->input->post('CustID', true));
      $Customer = trim($this->input->post('Customer', true));
      $Address = trim($this->input->post('Address', true));

      if ($projectDescription === '' || $projectCategory === '' || $contractDate === '') {
        $this->session->set_flashdata('danger', 'Please fill in all required fields.');
        redirect('Page/addProject');
        return;
      }

      $payload = [
        'projectDescription' => $projectDescription,
        'projectCategory' => $projectCategory,
        'contractDate' => $contractDate,
        'projectCost' => $projectCost,
        'contactPerson' => $contactPerson,
        'CustID' => $CustID,
        'Customer' => $Customer,
        'Address' => $Address,
        'otherDetails' => $otherDetails,
        'settingsID' => $settingsID,
      ];

      $ok = $this->db->insert('projects', $payload);

      if ($ok) {
        $this->session->set_flashdata('success', 'Project saved successfully.');
        $this->session->set_flashdata('toast_type', 'success');
        $this->session->set_flashdata('toast_text', 'Project saved successfully');
      } else {
        $this->session->set_flashdata('danger', 'Save failed. Please try again.');
        $this->session->set_flashdata('toast_type', 'error');
        $this->session->set_flashdata('toast_text', 'Save failed');
      }

      redirect('Page/projectList');
      return;
    }

    $settingsID = $this->session->userdata('settingsID');

    $result['data'] = $this->CashModel->getprojectcat();
    $result['clients'] = $this->db
      ->where('settingsID', $settingsID)
      ->order_by('Customer', 'ASC')
      ->get('customers')
      ->result();

    $this->load->view('add_project', $result);
  }


  public function updateProject()
  {
    $id = $this->input->get('id');
    $settingsID = (int) $this->session->userdata('settingsID');

    if ($this->input->method() === 'post') {
      $id = $this->input->post('id') ?: $id;

      if (!empty($id)) {
        $projectCostRaw = (string) $this->input->post('projectCost', true);
        $projectCostClean = preg_replace('/[^\d.]/', '', $projectCostRaw);
        $projectCost = ($projectCostClean !== '' && is_numeric($projectCostClean)) ? (float) $projectCostClean : 0;

        $updateData = [
          'projectDescription' => trim((string) $this->input->post('projectDescription', true)),
          'projectCategory' => trim((string) $this->input->post('projectCategory', true)),
          'contractDate' => $this->input->post('contractDate', true),
          'projectCost' => $projectCost,
          'contactPerson' => trim((string) $this->input->post('contactPerson', true)),
          'CustID' => trim((string) $this->input->post('CustID', true)),
          'Customer' => trim((string) $this->input->post('Customer', true)),
          'Address' => trim((string) $this->input->post('Address', true)),
          'otherDetails' => trim((string) $this->input->post('otherDetails', true)),
        ];

        $ok = $this->db
          ->where('projectID', $id)
          ->where('settingsID', $settingsID)
          ->update('projects', $updateData);

        if ($ok) {
          $this->session->set_flashdata('success', 'Project updated successfully.');
          $this->session->set_flashdata('toast_type', 'success');
          $this->session->set_flashdata('toast_text', 'Project updated');
        } else {
          $this->session->set_flashdata('danger', 'Update failed. Please try again.');
          $this->session->set_flashdata('toast_type', 'error');
          $this->session->set_flashdata('toast_text', 'Update failed');
        }
      }

      redirect('Page/projectList');
      return;
    }

    $result['categories'] = $this->CashModel->getprojectcat();

    $records = !empty($id) ? $this->CashModel->updateProject($id) : [];
    $result['project'] = !empty($records) ? $records[0] : null;

    $result['clients'] = $this->db
      ->where('settingsID', $settingsID)
      ->order_by('Customer', 'ASC')
      ->get('customers')
      ->result();

    $this->load->view('update_project', $result);
  }

  public function projectList()
  {
    $level = strtolower(trim((string) $this->session->userdata('level')));
    $settingsID = (int) $this->session->userdata('settingsID');
    $user_id = (int) $this->session->userdata('user_id');

    // fallback if user_id is not directly stored in session
    if ($user_id <= 0) {
      $username = trim((string) $this->session->userdata('username'));
      if ($username !== '') {
        $userRow = $this->db
          ->select('user_id')
          ->where('username', $username)
          ->limit(1)
          ->get('users')
          ->row();

        if ($userRow && isset($userRow->user_id)) {
          $user_id = (int) $userRow->user_id;
        }
      }
    }

    if ($level === 'admin') {
      $result['data'] = $this->CashModel->projectList($settingsID);
      $this->load->view('project_list', $result);
      return;
    }

    if (in_array($level, array('staff', 'encoder'), true)) {
      $result['data'] = $this->CashModel->projectListForStaff($settingsID, $user_id);
      $this->load->view('project_list', $result);
      return;
    }

    echo "Access Denied";
  }

  public function projectDeploymentStatus()
  {
    $settingsID = (int) $this->session->userdata('settingsID');
    $projectID = (int) $this->input->get('projectID');
    $level = strtolower(trim((string) $this->session->userdata('level')));
    $currentUserId = (int) $this->session->userdata('user_id');

    if ($currentUserId <= 0) {
      $username = trim((string) $this->session->userdata('username'));
      if ($username !== '') {
        $userRow = $this->db
          ->select('user_id')
          ->where('username', $username)
          ->limit(1)
          ->get('users')
          ->row();

        if ($userRow && isset($userRow->user_id)) {
          $currentUserId = (int) $userRow->user_id;
        }
      }
    }

    if ($projectID <= 0) {
      $this->session->set_flashdata('danger', 'Invalid project selected.');
      redirect('Page/projectList');
      return;
    }

    $project = $this->db
      ->where('projectID', $projectID)
      ->where('settingsID', $settingsID)
      ->get('projects')
      ->row();

    if (!$project) {
      $this->session->set_flashdata('danger', 'Project not found.');
      redirect('Page/projectList');
      return;
    }

    $existing = $this->db
      ->where('projectID', $projectID)
      ->where('settingsID', $settingsID)
      ->get('project_deployment_status')
      ->result();

    if (empty($existing)) {
      $defaultItems = [
        'Memorandum of Agreement (MOA)',
        'Sub-domain creation',
        'Uploading of Files',
        'Database creation',
        'Database migration',
        'Configuration Setup - School Name',
        'Configuration Setup - School Address',
        'Configuration Setup - Signatories',
        'Configuration Setup - Login Form Image',
        'Configuration Setup - Login Form Logo',
        'Configuration Setup - Letterhead',
        'Email Setup',
        'Mobile Application - Android',
        'Mobile Application - iOS',
        'Testing - Admin',
        'Testing - Registrar',
        'Testing - Accounting',
        'Preparation of User Accounts - Admin',
        'Preparation of User Accounts - Registrar',
        'Preparation of User Accounts - Accounting',
        'Turnover of User Accounts',
        'Creation of Group Chat for Technical Support',
        'Database Connection for Backup'
      ];

      $batch = [];
      foreach ($defaultItems as $item) {
        $batch[] = [
          'projectID' => $projectID,
          'user_id' => null,
          'item_name' => $item,
          'status_value' => 'Pending',
          'remarks' => '',
          'notes' => '',
          'settingsID' => $settingsID
        ];
      }

      if (!empty($batch)) {
        $this->db->insert_batch('project_deployment_status', $batch);
      }
    }

    $result['project'] = $project;
    $result['items'] = $this->db
      ->where('projectID', $projectID)
      ->where('settingsID', $settingsID)
      ->order_by('id', 'ASC')
      ->get('project_deployment_status')
      ->result();

    $result['users'] = $this->db
      ->select('e.empID, e.fName, e.mName, e.lName, e.email, e.department, u.user_id, u.username, u.position')
      ->from('employee e')
      ->join('users u', 'u.email = e.email AND u.settingsID = e.settingsID', 'inner')
      ->where('e.settingsID', $settingsID)
      ->where('u.acctStat', 'Active')
      ->where_in('u.position', array('Admin', 'Manager', 'Encoder', 'Staff', 'Cashier', 'POS Admin', 'POS Staff'))
      ->group_by('e.empID')
      ->order_by('e.lName', 'ASC')
      ->order_by('e.fName', 'ASC')
      ->get()
      ->result();

    $result['currentUserId'] = $currentUserId;
    $result['isAdmin'] = ($level === 'admin');
    $result['isStaff'] = in_array($level, array('staff', 'encoder'), true);

    // Calculate status percentage breakdown
    $statusBreakdown = array(
      'Pending' => 0,
      'Done' => 0,
      'Partially Done' => 0,
      'Not Applicable' => 0
    );
    $totalItems = count($result['items']);

    if ($totalItems > 0) {
      foreach ($result['items'] as $item) {
        $status = trim((string) $item->status_value);
        if (array_key_exists($status, $statusBreakdown)) {
          $statusBreakdown[$status]++;
        }
      }

      // Calculate percentages
      foreach ($statusBreakdown as $status => $count) {
        $statusBreakdown[$status] = array(
          'count' => $count,
          'percentage' => round(($count / $totalItems) * 100, 1)
        );
      }
    }

    $result['statusBreakdown'] = $statusBreakdown;
    $result['totalItems'] = $totalItems;

    $this->load->view('project_deployment_status', $result);
  }

  public function saveProjectDeploymentStatus()
  {
    $settingsID = (int) $this->session->userdata('settingsID');
    $projectID = (int) $this->input->post('projectID');
    $level = strtolower(trim((string) $this->session->userdata('level')));
    $isAdmin = ($level === 'admin');

    $currentUserId = (int) $this->session->userdata('user_id');
    if ($currentUserId <= 0) {
      $username = trim((string) $this->session->userdata('username'));
      if ($username !== '') {
        $userRow = $this->db
          ->select('user_id')
          ->where('username', $username)
          ->limit(1)
          ->get('users')
          ->row();

        if ($userRow && isset($userRow->user_id)) {
          $currentUserId = (int) $userRow->user_id;
        }
      }
    }

    $statusRows = $this->input->post('status');
    $remarksRows = $this->input->post('remarks');
    $notes = trim((string) $this->input->post('notes', true));
    $removeFiles = $this->input->post('remove_attachment');
    $userRows = $this->input->post('user_id');

    if ($projectID <= 0 || empty($statusRows)) {
      $this->session->set_flashdata('danger', 'No deployment data to save.');
      redirect('Page/projectList');
      return;
    }

    $uploadPath = FCPATH . 'uploads/project_deployment/';
    if (!is_dir($uploadPath)) {
      @mkdir($uploadPath, 0777, true);
    }

    $this->load->library('upload');

    foreach ($statusRows as $id => $statusValue) {
      $id = (int) $id;
      $remarksValue = isset($remarksRows[$id]) ? trim((string) $remarksRows[$id]) : '';
      $assignedUserId = isset($userRows[$id]) ? (int) $userRows[$id] : null;

      $row = $this->db
        ->where('id', $id)
        ->where('projectID', $projectID)
        ->where('settingsID', $settingsID)
        ->get('project_deployment_status')
        ->row();

      if (!$row) {
        continue;
      }

      // Staff can edit only rows assigned to them
      if (!$isAdmin) {
        if ((int) $row->user_id !== (int) $currentUserId) {
          continue;
        }
      }

      $updateData = [
        'status_value' => trim((string) $statusValue),
        'remarks' => $remarksValue,
        'settingsID' => $settingsID
      ];

      // Only admin can reassign users and save notes
      if ($isAdmin) {
        $updateData['user_id'] = $assignedUserId > 0 ? $assignedUserId : null;
        $updateData['notes'] = $notes;
      }

      $isMOA = isset($row->item_name) && trim((string) $row->item_name) === 'Memorandum of Agreement (MOA)';

      if ($isMOA) {
        if (isset($removeFiles[$id]) && (string) $removeFiles[$id] === '1') {
          if (!empty($row->attachment_file)) {
            $oldFile = FCPATH . $row->attachment_file;
            if (is_file($oldFile)) {
              @unlink($oldFile);
            }
          }
          $updateData['attachment_file'] = null;
        }

        if (
          isset($_FILES['moa_file']['name'][$id]) &&
          $_FILES['moa_file']['name'][$id] !== ''
        ) {
          $_FILES['single_moa']['name'] = $_FILES['moa_file']['name'][$id];
          $_FILES['single_moa']['type'] = $_FILES['moa_file']['type'][$id];
          $_FILES['single_moa']['tmp_name'] = $_FILES['moa_file']['tmp_name'][$id];
          $_FILES['single_moa']['error'] = $_FILES['moa_file']['error'][$id];
          $_FILES['single_moa']['size'] = $_FILES['moa_file']['size'][$id];

          $config = [
            'upload_path' => $uploadPath,
            'allowed_types' => 'pdf',
            'max_size' => 5120,
            'file_name' => 'MOA_' . $projectID . '_' . time() . '_' . $id,
            'overwrite' => false
          ];

          $this->upload->initialize($config);

          if ($this->upload->do_upload('single_moa')) {
            $uploadData = $this->upload->data();

            if (!empty($row->attachment_file)) {
              $oldFile = FCPATH . $row->attachment_file;
              if (is_file($oldFile)) {
                @unlink($oldFile);
              }
            }

            $updateData['attachment_file'] = 'uploads/project_deployment/' . $uploadData['file_name'];
          } else {
            $this->session->set_flashdata('danger', $this->upload->display_errors('', ''));
            redirect('Page/projectDeploymentStatus?projectID=' . $projectID);
            return;
          }
        }
      }

      $this->db->where('id', $id);
      $this->db->where('projectID', $projectID);
      $this->db->where('settingsID', $settingsID);
      $this->db->update('project_deployment_status', $updateData);
    }

    $this->session->set_flashdata('success', 'Deployment status updated successfully.');
    redirect('Page/projectDeploymentStatus?projectID=' . $projectID);
  }
  function deleteProject()
  {
    $id = $this->input->get('id');
    $que = $this->db->query("delete from projects where projectID='" . $id . "'");
    redirect('Page/projectList');
  }


  function productList()
  {
    $settingsID = $this->session->userdata('settingsID');
    $result['data'] = $this->CashModel->productList($settingsID);
    $this->load->view('product_list', $result);
  }

  function addProduct()
  {
    $settingsID = $this->session->userdata('settingsID');
    $productName = $this->input->post('productName');
    $productLine = $this->input->post('productLine');

    $que = $this->db->query("insert into items values('0','$productName','$productLine','$settingsID')");

    redirect('Page/productList');
  }

  function deleteProduct()
  {
    $id = $this->input->get('id');
    $que = $this->db->query("delete from items where itemID='" . $id . "'");
    redirect('Page/productList');
  }

  function updateProduct()
  {
    $id = $this->input->get('id');
    $result['data'] = $this->CashModel->updateProduct($id);
    $this->load->view('update_product', $result);

    if ($this->input->method() === 'post') {

      $id = $this->input->post('id');
      $productName = $this->input->post('productName');
      $productLine = $this->input->post('productLine');

      $que = $this->db->query("update items set productName='$productName',productLine='$productLine' where itemID='" . $id . "'");
      redirect('Page/productList');
    }
  }

  function productDelivery()
  {
    $id = $this->input->get('id');
    $result['data'] = $this->CashModel->updateProduct($id);
    $this->load->view('product_delivery', $result);

    if ($this->input->method() === 'post') {

      $id = $this->input->post('id');
      $settingsID = $this->session->userdata('settingsID');

      $Supplier = $this->input->post('Supplier');
      $productCode = $this->input->post('productCode');
      $productDescription = $this->input->post('productDescription');
      $QtyDelivered = $this->input->post('QtyDelivered');
      $prodUnit = $this->input->post('prodUnit');
      $Notes = $this->input->post('Notes');
      $purchasePrice = $this->input->post('purchasePrice');
      $sellingPrice = $this->input->post('sellingPrice');
      $serialNo = $this->input->post('serialNo');
      $model = $this->input->post('model');
      $brand = $this->input->post('brand');

      date_default_timezone_set('Asia/Manila'); # add your city to set local time zone

      $DDate = date('Y-m-d H:i:s');

      $que = $this->db->query("insert into delivery(Supplier, productCode, productDescription, QtyDelivered, prodUnit, DDate, Notes, itemID, settingsID, purchasePrice, sellingPrice, serialNo, model, brand, itemStat) values ('$Supplier','$productCode','$productDescription','$QtyDelivered','$prodUnit','$DDate','$Notes','$id','$settingsID','$purchasePrice','$sellingPrice','$serialNo','$model','$brand','Available')");
      redirect('Page/productList');
    }
  }

  function deliveryList()
  {
    $settingsID = $this->session->userdata('settingsID');
    $id = $this->input->get('id');
    $result['data'] = $this->CashModel->deliveryList($settingsID, $id);
    $this->load->view('delivery_list', $result);
  }


  function deleteEmployee()
  {
    $id = $this->input->get('id');
    $que = $this->db->query("delete from employee where empID='" . $id . "'");
    $currentFilter = trim((string) $this->input->get('status'));
    if ($currentFilter !== '') {
      redirect('Page/employeeList?status=' . urlencode($currentFilter));
    } else {
      redirect('Page/employeeList');
    }
  }

  public function clientEntry()
  {
    $settingsID = $this->session->userdata('settingsID');

    if ($this->_is_client_user()) {
      redirect('Page/clientDashboard');
      return;
    }

    if ($this->input->post('addclient')) {
      $this->_handle_client_create($settingsID, 'Page/clientEntry');
      return;
    }

    $result = array(
      'formValues' => $this->_client_entry_form_values($settingsID),
      'backUrl' => base_url() . 'Page/clientList',
    );

    $this->load->view('client_entry', $result);
  }

  public function clientList()
  {
    $settingsID = $this->session->userdata('settingsID');

    if ($this->_is_client_user()) {
      redirect('Page/clientDashboard');
      return;
    }

    // Ensure invoice access column exists
    $this->_ensureInvoiceAccessColumn();

    // ADD CLIENT
    if ($this->input->post('addclient')) {
      $this->_handle_client_create($settingsID, 'Page/clientList');
      return;
    }

    // UPDATE CLIENT
    if ($this->input->post('updateclient')) {
      if (!$this->_is_admin_user()) {
        $this->session->set_flashdata('danger', 'Only admins can update client profiles.');
        redirect('Page/clientList');
        return;
      }

      $existingClient = $this->db
        ->where('CustID', trim((string) $this->input->post('CustID', true)))
        ->where('settingsID', $settingsID)
        ->get('customers')
        ->row_array();

      if (empty($existingClient)) {
        $this->session->set_flashdata('danger', 'Client record not found.');
        redirect('Page/clientList');
        return;
      }

      $CustID = trim((string) $this->input->post('CustID', true));
      $Customer = trim((string) $this->input->post('Customer', true));
      $Address = trim((string) $this->input->post('Address', true));
      $ContactNos = trim((string) $this->input->post('Contact', true));
      $ContactPerson = trim((string) $this->input->post('ContactPerson', true));
      $CompanyEmail = trim((string) $this->input->post('CompanyEmail', true));
      $ClientStat = trim((string) $this->input->post('ClientStat', true));

      // NEW FIELDS
      $client_source = trim((string) $this->input->post('client_source', true));
      $facebook_link = trim((string) $this->input->post('facebook_link', true));
      $client_email = trim((string) $this->input->post('client_email', true));
      $notes = trim((string) $this->input->post('notes', true));
      $sales_agent = trim((string) $this->input->post('sales_agent', true));
      $created_at = trim((string) $this->input->post('created_at', true));
      $portalEnabled = $this->_normalize_client_portal_enabled($this->input->post('portal_enabled', true));
      $invoiceAccessEnabled = $this->_normalize_client_portal_enabled($this->input->post('invoice_access_enabled', true));
      $portalPasswordInput = trim((string) $this->input->post('portal_password', true));

      if ($portalEnabled && $client_email === '') {
        $this->session->set_flashdata('danger', 'Client email is required when portal access is enabled.');
        redirect('Page/clientList');
        return;
      }

      if ($portalEnabled && $this->_client_portal_email_in_use($settingsID, $client_email, $CustID)) {
        $this->session->set_flashdata('danger', 'Client email is already used by another portal account.');
        redirect('Page/clientList');
        return;
      }

      if ($portalEnabled && $portalPasswordInput === '' && empty($existingClient['portal_password'])) {
        $this->session->set_flashdata('danger', 'Set an initial portal password before enabling client access.');
        redirect('Page/clientList');
        return;
      }

      $data = array(
        'Customer' => $Customer,
        'Address' => $Address,
        'ContactNos' => $ContactNos,
        'ContactPerson' => $ContactPerson,
        'CompanyEmail' => $CompanyEmail,
        'ClientStat' => $ClientStat,
        'client_source' => $client_source,
        'facebook_link' => $facebook_link,
        'client_email' => $client_email,
        'sales_agent'    => $sales_agent,
        'notes' => $notes,
        'portal_enabled' => $portalEnabled,
        'invoice_access_enabled' => (int) $invoiceAccessEnabled
      );

      if ($created_at !== '') {
        $data['created_at'] = $created_at;
      }

      if ($portalPasswordInput !== '') {
        $data['portal_password'] = password_hash($portalPasswordInput, PASSWORD_DEFAULT);
      } elseif (!$portalEnabled) {
        $data['portal_password'] = null;
      }

      $clientForSync = array_merge($existingClient, $data, array('CustID' => $CustID));
      $previousClientEmail = trim((string) ($existingClient['client_email'] ?? ''));

      $this->db->trans_begin();

      $this->db->where('CustID', $CustID);
      $this->db->where('settingsID', $settingsID);
      $updated = $this->db->update('customers', $data);
      $syncResult = array(true, '');

      if ($updated) {
        $syncResult = $this->_sync_client_portal_user_account($settingsID, $clientForSync, $portalPasswordInput, $previousClientEmail);
      }

      if (!$updated || !$syncResult[0]) {
        $this->db->trans_rollback();
        $this->session->set_flashdata('danger', $syncResult[1] !== '' ? $syncResult[1] : 'Failed to update client.');
        redirect('Page/clientList');
        return;
      }

      $this->db->trans_commit();
      $this->session->set_flashdata('success', 'Client updated successfully.');

      redirect('Page/clientList');
      return;
    }

    // DELETE CLIENT
    if ($this->input->post('deleteclient')) {
      if (!$this->_is_admin_user()) {
        $this->session->set_flashdata('danger', 'Only admins can delete client profiles.');
        redirect('Page/clientList');
        return;
      }

      $CustID = trim((string) $this->input->post('CustID', true));

      $this->db->where('CustID', $CustID);
      $this->db->where('settingsID', $settingsID);

      if ($this->db->delete('customers')) {
        $this->session->set_flashdata('success', 'Client deleted successfully.');
      } else {
        $this->session->set_flashdata('danger', 'Failed to delete client.');
      }

      redirect('Page/clientList');
      return;
    }

    $result['data'] = $this->CashModel->clientList($settingsID);
    $result['data1'] = $this->CashModel->getCustID($settingsID);
    $this->load->view('client_list', $result);
  }

  public function clientProfile()
  {
    $settingsID = $this->session->userdata('settingsID');
    $custID = trim((string) $this->input->get('cust_id'));
    $customer = trim((string) $this->input->get('customer'));
    $activeTab = trim((string) $this->input->get('tab'));

    if ($this->_is_client_user()) {
      $client = $this->_load_current_client();
      if (!$client) {
        show_404();
        return;
      }

      $custID = trim((string) ($client->CustID ?? ''));
      $customer = trim((string) ($client->Customer ?? ''));
    }

    $client = null;
    if ($custID !== '') {
      $client = $this->CashModel->getClientByCustID($settingsID, $custID);
    }

    if (!$client && $customer !== '') {
      $client = $this->CashModel->getClientByName($settingsID, $customer);
      if ($client && !empty($client->CustID)) {
        $custID = (string) $client->CustID;
      }
    }

    if (!$client) {
      show_404();
      return;
    }

    if ($this->_is_client_user() && !$this->_current_client_matches($client->CustID ?? '', $client->Customer ?? '')) {
      show_404();
      return;
    }

    if ($customer === '' && !empty($client->Customer)) {
      $customer = (string) $client->Customer;
    }

    $result['client'] = $client;

    // Check invoice access permission
    $invoiceAccessEnabled = !empty($client->invoice_access_enabled);
    $result['invoice_access_enabled'] = $invoiceAccessEnabled;

    if ($invoiceAccessEnabled) {
      $result['invoices'] = $this->CashModel->clientInvoices($settingsID, $custID, $customer);
      $this->_attachInvoiceItemsToCollection($result['invoices'], $settingsID);
      $result['payments'] = $this->CashModel->clientPayments($settingsID, $custID, $customer);
    } else {
      $result['invoices'] = array();
      $result['payments'] = array();
    }

    // Get ticket data for the customer
    $this->db->where('customer_id', $custID);
    $this->db->where('settingsID', $settingsID);
    $this->db->order_by('created_at', 'DESC');
    $allTickets = $this->db->get('support_issues')->result();

    $ticketCounts = array(
      'total' => 0,
      'open' => 0,
      'closed' => 0
    );

    $openTickets = array();
    $closedTickets = array();

    if (!empty($allTickets)) {
      $ticketCounts['total'] = count($allTickets);
      foreach ($allTickets as $ticket) {
        $status = trim((string) ($ticket->status ?? ''));
        if (strtolower($status) === 'open') {
          $ticketCounts['open']++;
          $openTickets[] = $ticket;
        } elseif (strtolower($status) === 'closed') {
          $ticketCounts['closed']++;
          $closedTickets[] = $ticket;
        }
      }
    }

    $result['ticketCounts'] = $ticketCounts;
    $result['openTickets'] = $openTickets;
    $result['closedTickets'] = $closedTickets;

    $result['backUrl'] = $this->_is_client_user() ? base_url() . 'Page/clientDashboard' : base_url() . 'Page/clientList';
    $result['backLabel'] = $this->_is_client_user() ? 'Back to Dashboard' : 'Back to Client List';
    $result['activeTab'] = in_array($activeTab, ['invoices', 'payments', 'tickets']) ? $activeTab : 'info';
    $this->load->view('client_profile', $result);
  }


  function priceListProduct()
  {
    $settingsID = $this->session->userdata('settingsID');

    $result['data'] = $this->CashModel->priceListProduct($settingsID);
    $this->load->view('price_list_product', $result);
  }


  function priceList()
  {
    $settingsID = $this->session->userdata('settingsID');
    $this->_ensureServiceFeesTable();

    if ($this->input->method() === 'post' && $this->input->post('updateservice')) {
      $feesID = (int) $this->input->post('feesID');
      $FeesDescription = trim((string) $this->input->post('FeesDescription'));
      $subCategory = trim((string) $this->input->post('subCategory'));
      $feeDetails = trim((string) $this->input->post('feeDetails'));
      $Amount = is_numeric($this->input->post('Amount')) ? (float) $this->input->post('Amount') : 0;

      if ($feesID > 0 && $FeesDescription !== '') {
        $this->db
          ->where('feesID', $feesID)
          ->where('settingsID', $settingsID)
          ->update('service_fees', array(
            'FeesDescription' => $FeesDescription,
            'subCategory' => $subCategory !== '' ? $subCategory : null,
            'feeDetails' => $feeDetails !== '' ? $feeDetails : null,
            'Amount' => $Amount,
          ));
      }

      redirect('Page/priceList');
      return;
    }

    if ($this->input->method() === 'post' && $this->input->post('addservice')) {
      $FeesDescription = trim((string) $this->input->post('FeesDescription'));
      $subCategory = trim((string) $this->input->post('subCategory'));
      $feeDetails = trim((string) $this->input->post('feeDetails'));
      $Amount = is_numeric($this->input->post('Amount')) ? (float) $this->input->post('Amount') : 0;

      if ($FeesDescription !== '') {
        $this->db->insert('service_fees', array(
          'FeesDescription' => $FeesDescription,
          'subCategory' => $subCategory !== '' ? $subCategory : null,
          'feeDetails' => $feeDetails !== '' ? $feeDetails : null,
          'Amount' => $Amount,
          'settingsID' => $settingsID,
        ));
      }
      redirect('Page/priceList');
      return;
    }

    $result['data'] = $this->CashModel->priceList($settingsID);

    $this->load->view('price_list_service', $result);
  }


  function deleteFees()
  {
    $settingsID = $this->session->userdata('settingsID');
    $feesID = (int) $this->input->get('id');
    $this->_ensureServiceFeesTable();
    $this->db
      ->where('feesID', $feesID)
      ->where('settingsID', $settingsID)
      ->delete('service_fees');
    redirect('Page/priceList');
  }



  function noteList()
  {
    $user = $this->session->userdata('username');
    $settingsID = $this->session->userdata('settingsID');

    $this->load->helper('text');

    if ($this->input->post('addnote')) {
      $noteTitle = $this->input->post('noteTitle');
      $noteDescription = $this->input->post('noteDescription');
      $noteTags = $this->input->post('noteTags');
      date_default_timezone_set('Asia/Manila');
      $date = date("Y-m-d");

      // Check if tags column exists, if not add it
      if (!$this->db->field_exists('tags', 'notes')) {
        $this->db->query("ALTER TABLE notes ADD COLUMN tags VARCHAR(255) DEFAULT NULL");
      }

      $data = array(
        'noteDate' => $date,
        'title' => $noteTitle,
        'noteDescription' => $noteDescription,
        'tags' => $noteTags,
        'notedBy' => $user,
        'settingsID' => $settingsID,
        'noteStat' => 'Active'
      );

      $this->db->insert('notes', $data);
      redirect('Page/noteList');
    }

    if ($this->input->post('delete_note')) {
      $noteID = $this->input->post('noteID');

      if (!empty($noteID)) {
        $this->db->where('noteID', $noteID);
        $this->db->update('notes', ['noteStat' => 'Removed']);
      }

      redirect('Page/noteList');
    }

    $result['data'] = $this->CashModel->noteList($user, $settingsID);
    $this->load->view('note_list', $result);
  }


  public function updateNote()
  {
    $noteID = $this->input->post('noteID');
    $title = $this->input->post('noteTitle');
    $description = $this->input->post('noteDescription');
    $tags = $this->input->post('noteTags');

    // Check if tags column exists, if not add it
    if (!$this->db->field_exists('tags', 'notes')) {
      $this->db->query("ALTER TABLE notes ADD COLUMN tags VARCHAR(255) DEFAULT NULL");
    }

    $this->db->where('noteID', $noteID);
    $this->db->update('notes', [
      'title' => $title,
      'noteDescription' => $description,
      'tags' => $tags
    ]);

    redirect('Page/noteList');
  }

  public function toggleFavorite()
  {
    $noteID = $this->input->post('note_id');
    $isFavorite = $this->input->post('is_favorite');

    header('Content-Type: application/json');

    if (empty($noteID)) {
      echo json_encode(['success' => false, 'error' => 'Note ID required']);
      return;
    }

    // Check if is_favorite column exists, if not add it
    if (!$this->db->field_exists('is_favorite', 'notes')) {
      $this->db->query("ALTER TABLE notes ADD COLUMN is_favorite INT DEFAULT 0");
    }

    $this->db->where('noteID', $noteID);
    $this->db->update('notes', ['is_favorite' => $isFavorite]);

    echo json_encode(['success' => true]);
  }

  public function aiSummary()
  {
    $description = $this->input->post('description');
    $noteID = $this->input->post('note_id');

    header('Content-Type: application/json');

    if (empty($description)) {
      echo json_encode(['success' => false, 'error' => 'No description provided']);
      return;
    }

    // Strip HTML tags for AI processing
    $plainText = strip_tags($description);
    $plainText = trim($plainText);

    if (strlen($plainText) < 50) {
      echo json_encode(['success' => false, 'error' => 'Content too short to summarize']);
      return;
    }

    // OpenAI API configuration
    $apiKey = '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';

    if ($apiKey === 'YOUR_OPENAI_API_KEY') {
      // Fallback: Simple bullet point extraction without AI
      $sentences = preg_split('/[.!?]+/', $plainText);
      $summary = '<ul class="ai-summary-list">';
      foreach (array_slice($sentences, 0, 5) as $sentence) {
        $sentence = trim($sentence);
        if (!empty($sentence)) {
          $summary .= '<li>' . htmlspecialchars($sentence, ENT_QUOTES, 'UTF-8') . '</li>';
        }
      }
      $summary .= '</ul>';
      echo json_encode(['success' => true, 'summary' => $summary]);
      return;
    }

    // OpenAI API call
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://api.openai.com/v1/chat/completions');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
      'Content-Type: application/json',
      'Authorization: Bearer ' . $apiKey
    ]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    $prompt = "Summarize the following meeting notes into a bulleted list of key points. Keep it concise and actionable:\n\n" . $plainText;

    $data = [
      'model' => 'gpt-3.5-turbo',
      'messages' => [
        ['role' => 'system', 'content' => 'You are a helpful assistant that summarizes meeting notes into clear, actionable bullet points.'],
        ['role' => 'user', 'content' => $prompt]
      ],
      'max_tokens' => 500,
      'temperature' => 0.7
    ];

    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    if ($curlError) {
      echo json_encode(['success' => false, 'error' => 'CURL error: ' . $curlError]);
      return;
    }

    if ($httpCode === 200) {
      $result = json_decode($response, true);
      if (isset($result['choices'][0]['message']['content'])) {
        $aiSummary = $result['choices'][0]['message']['content'];
        // Convert markdown bullets to HTML
        $htmlSummary = nl2br($aiSummary);
        $htmlSummary = preg_replace('/^\- /m', '<li>', $htmlSummary);
        $htmlSummary = preg_replace('/(?<!<li>)(^|\n)(?!<li>)/m', '$1', $htmlSummary);
        $htmlSummary = '<ul class="ai-summary-list">' . $htmlSummary . '</ul>';
        echo json_encode(['success' => true, 'summary' => $htmlSummary]);
      } else {
        echo json_encode(['success' => false, 'error' => 'Invalid API response: ' . $response]);
      }
    } elseif ($httpCode === 429) {
      // Fallback to simple bullet point extraction when quota exceeded
      $sentences = preg_split('/[.!?]+/', $plainText);
      $summary = '<ul class="ai-summary-list">';
      foreach (array_slice($sentences, 0, 5) as $sentence) {
        $sentence = trim($sentence);
        if (!empty($sentence)) {
          $summary .= '<li>' . htmlspecialchars($sentence, ENT_QUOTES, 'UTF-8') . '</li>';
        }
      }
      $summary .= '</ul>';
      echo json_encode(['success' => true, 'summary' => $summary, 'fallback' => true]);
    } else {
      echo json_encode(['success' => false, 'error' => 'API request failed with HTTP code ' . $httpCode . ': ' . $response]);
    }
  }



  function deleteNote()
  {
    $noteID = $this->input->get('id');
    $que = $this->db->query("update notes set noteStat='Removed' where noteID='" . $noteID . "'");
    redirect('Page/noteList');
  }


  function deleteClient()
  {
    if (!$this->_is_admin_user()) {
      redirect('Page/clientList');
      return;
    }

    $CustID = $this->input->get('id');
    $que = $this->db->query("update customers set ClientStat='Inactive' where CustID='" . $CustID . "'");
    redirect('Page/clientList');
  }

  public function payrollModule()
  {
    if (!$this->_is_admin_user()) {
      echo "Access Denied";
      return;
    }

    $settingsID = (int) $this->session->userdata('settingsID');
    $dateFrom = $this->_normalizeDateInput(trim((string) $this->input->get('date_from')));
    $dateTo = $this->_normalizeDateInput(trim((string) $this->input->get('date_to')));

    if ($dateFrom === null && $dateTo === null) {
      $dateFrom = date('Y-01-01');
      $dateTo = date('Y-m-d');
    } elseif ($dateFrom === null) {
      $dateFrom = $dateTo;
    } elseif ($dateTo === null) {
      $dateTo = $dateFrom;
    }

    if ($dateFrom !== null && $dateTo !== null && strtotime($dateFrom) > strtotime($dateTo)) {
      $swap = $dateFrom;
      $dateFrom = $dateTo;
      $dateTo = $swap;
    }

    $employees = $this->PayrollModel->getPayrollEmployees($settingsID);
    $payrollRuns = $this->PayrollModel->getPayrollRuns($settingsID);
    $activeLoans = $this->PayrollModel->getPayrollLoans($settingsID, '', 'active');
    $activeCashAdvances = $this->PayrollModel->getPayrollCashAdvances($settingsID, '', 'active');
    $deductionSummary = $this->PayrollModel->getDeductionSummary($settingsID, $dateFrom, $dateTo, null);
    $businessDetails = $this->CashModel->businessDetails($settingsID);

    $configuredEmployeeCount = 0;
    $totalMonthlySalary = 0.0;
    $activeLoanBalance = 0.0;
    $activeAdvanceBalance = 0.0;

    foreach ($employees as $employee) {
      if ((int) ($employee->profileID ?? 0) > 0 && (float) ($employee->monthlySalary ?? 0) > 0) {
        $configuredEmployeeCount++;
        $totalMonthlySalary += (float) ($employee->monthlySalary ?? 0);
      }

      $activeLoanBalance += (float) ($employee->loanBalance ?? 0);
      $activeAdvanceBalance += (float) ($employee->advanceBalance ?? 0);
    }

    $latestRun = !empty($payrollRuns) ? $payrollRuns[0] : null;
    $result = array(
      'business' => !empty($businessDetails) ? $businessDetails[0] : null,
      'employees' => $employees,
      'payrollRuns' => $payrollRuns,
      'activeLoans' => $activeLoans,
      'activeCashAdvances' => $activeCashAdvances,
      'deductionSummary' => $deductionSummary,
      'filterDateFrom' => $dateFrom,
      'filterDateTo' => $dateTo,
      'rangeLabel' => $this->_formatReportDateRangeLabel($dateFrom, $dateTo),
      'generatedAt' => date('F j, Y h:i A'),
      'notice' => $this->session->flashdata('payroll_notice'),
      'noticeType' => $this->session->flashdata('payroll_notice_type'),
      'summaryCards' => array(
        'configuredEmployeeCount' => $configuredEmployeeCount,
        'totalMonthlySalary' => round($totalMonthlySalary, 2),
        'activeLoanBalance' => round($activeLoanBalance, 2),
        'activeAdvanceBalance' => round($activeAdvanceBalance, 2),
        'payrollRunCount' => count($payrollRuns),
        'latestRunNet' => $latestRun ? (float) ($latestRun->totalNet ?? 0) : 0.0,
      ),
    );

    $this->load->view('payroll_module', $result);
  }

  public function payrollSetup()
  {
    if (!$this->_is_admin_user()) {
      echo "Access Denied";
      return;
    }

    $settingsID = (int) $this->session->userdata('settingsID');
    $employees = $this->PayrollModel->getPayrollEmployees($settingsID);
    $activeLoans = $this->PayrollModel->getPayrollLoans($settingsID, '', 'active');
    $activeCashAdvances = $this->PayrollModel->getPayrollCashAdvances($settingsID, '', 'active');
    $businessDetails = $this->CashModel->businessDetails($settingsID);

    $result = array(
      'business' => !empty($businessDetails) ? $businessDetails[0] : null,
      'employees' => $employees,
      'activeLoans' => $activeLoans,
      'activeCashAdvances' => $activeCashAdvances,
      'notice' => $this->session->flashdata('payroll_notice'),
      'noticeType' => $this->session->flashdata('payroll_notice_type'),
    );

    $this->load->view('payroll_setup', $result);
  }

  public function savePayrollProfile()
  {
    if (!$this->_is_admin_user()) {
      redirect('Page/payrollModule');
      return;
    }

    $settingsID = (int) $this->session->userdata('settingsID');
    $empID = trim((string) $this->input->post('empID'));
    $returnPath = $this->_resolveInternalPageReturnPath($this->input->post('return_to'), 'Page/payrollModule');

    if ($empID === '') {
      $this->session->set_flashdata('payroll_notice', 'Please select an employee before saving payroll settings.');
      $this->session->set_flashdata('payroll_notice_type', 'danger');
      redirect($returnPath);
      return;
    }

    $this->PayrollModel->savePayrollProfile($settingsID, $empID, array(
      'monthlySalary' => $this->input->post('monthlySalary'),
      'philhealthAmount' => $this->input->post('philhealthAmount'),
      'sssAmount' => $this->input->post('sssAmount'),
      'pagibigAmount' => $this->input->post('pagibigAmount'),
      'notes' => $this->input->post('payrollNotes'),
      'payrollStatus' => $this->input->post('payrollStatus') ?: 'active',
    ));

    $this->session->set_flashdata('payroll_notice', 'Payroll profile saved successfully.');
    $this->session->set_flashdata('payroll_notice_type', 'success');
    redirect($returnPath);
  }

  public function addPayrollLoan()
  {
    if (!$this->_is_admin_user()) {
      redirect('Page/payrollModule');
      return;
    }

    $settingsID = (int) $this->session->userdata('settingsID');
    $empID = trim((string) $this->input->post('empID'));
    $returnPath = $this->_resolveInternalPageReturnPath($this->input->post('return_to'), 'Page/payrollModule');

    if ($empID === '') {
      $this->session->set_flashdata('payroll_notice', 'Please select an employee before posting a loan.');
      $this->session->set_flashdata('payroll_notice_type', 'danger');
      redirect($returnPath);
      return;
    }

    $principalAmount = (float) $this->input->post('principalAmount');
    $monthlyDeduction = (float) $this->input->post('monthlyDeduction');
    if ($principalAmount <= 0 || $monthlyDeduction <= 0) {
      $this->session->set_flashdata('payroll_notice', 'Loan principal amount and monthly deduction must both be greater than zero.');
      $this->session->set_flashdata('payroll_notice_type', 'danger');
      redirect($returnPath);
      return;
    }

    $this->PayrollModel->createLoan($settingsID, array(
      'empID' => $empID,
      'loanType' => $this->input->post('loanType'),
      'principalAmount' => $principalAmount,
      'monthlyDeduction' => $monthlyDeduction,
      'startDate' => $this->input->post('startDate'),
      'endDate' => $this->input->post('endDate'),
      'notes' => $this->input->post('notes'),
      'status' => 'active',
    ), $this->_currentUserDisplayName());

    $this->session->set_flashdata('payroll_notice', 'Payroll loan recorded successfully.');
    $this->session->set_flashdata('payroll_notice_type', 'success');
    redirect($returnPath);
  }

  public function addPayrollCashAdvance()
  {
    if (!$this->_is_admin_user()) {
      redirect('Page/payrollModule');
      return;
    }

    $settingsID = (int) $this->session->userdata('settingsID');
    $empID = trim((string) $this->input->post('empID'));
    $returnPath = $this->_resolveInternalPageReturnPath($this->input->post('return_to'), 'Page/payrollModule');

    if ($empID === '') {
      $this->session->set_flashdata('payroll_notice', 'Please select an employee before recording a cash advance.');
      $this->session->set_flashdata('payroll_notice_type', 'danger');
      redirect($returnPath);
      return;
    }

    $amount = (float) $this->input->post('amount');
    $deductionPerPayroll = (float) $this->input->post('deductionPerPayroll');
    if ($amount <= 0 || $deductionPerPayroll <= 0) {
      $this->session->set_flashdata('payroll_notice', 'Cash advance amount and payroll deduction must both be greater than zero.');
      $this->session->set_flashdata('payroll_notice_type', 'danger');
      redirect($returnPath);
      return;
    }

    $this->PayrollModel->createCashAdvance($settingsID, array(
      'empID' => $empID,
      'advanceDate' => $this->input->post('advanceDate'),
      'amount' => $amount,
      'deductionPerPayroll' => $deductionPerPayroll,
      'reason' => $this->input->post('reason'),
      'status' => 'active',
    ), $this->_currentUserDisplayName());

    $this->session->set_flashdata('payroll_notice', 'Cash advance recorded successfully.');
    $this->session->set_flashdata('payroll_notice_type', 'success');
    redirect($returnPath);
  }

  public function generatePayroll()
  {
    if (!$this->_is_admin_user()) {
      redirect('Page/payrollModule');
      return;
    }

    $settingsID = (int) $this->session->userdata('settingsID');
    $result = $this->PayrollModel->createPayrollRun($settingsID, array(
      'periodStart' => $this->input->post('periodStart'),
      'periodEnd' => $this->input->post('periodEnd'),
      'payDate' => $this->input->post('payDate'),
      'notes' => $this->input->post('notes'),
    ), $this->_currentUserDisplayName());

    if (empty($result['success'])) {
      $this->session->set_flashdata('payroll_notice', (string) ($result['error'] ?? 'Unable to generate payroll.'));
      $this->session->set_flashdata('payroll_notice_type', 'danger');
      redirect('Page/payrollModule');
      return;
    }

    $this->session->set_flashdata('payroll_notice', 'Payroll generated successfully for ' . (int) ($result['employeeCount'] ?? 0) . ' employee(s).');
    $this->session->set_flashdata('payroll_notice_type', 'success');
    redirect('Page/payrollRun?id=' . rawurlencode((string) ($result['payrollID'] ?? 0)));
  }

  public function payrollRun()
  {
    if (!$this->_is_admin_user()) {
      echo "Access Denied";
      return;
    }

    $settingsID = (int) $this->session->userdata('settingsID');
    $payrollID = (int) $this->input->get('id');
    $run = $this->PayrollModel->getPayrollRun($settingsID, $payrollID);
    if (!$run) {
      redirect('Page/payrollModule');
      return;
    }

    $entries = $this->PayrollModel->getPayrollEntries($settingsID, $payrollID);
    $deductionSummary = $this->PayrollModel->getDeductionSummary($settingsID, null, null, $payrollID);
    $businessDetails = $this->CashModel->businessDetails($settingsID);

    $result = array(
      'business' => !empty($businessDetails) ? $businessDetails[0] : null,
      'run' => $run,
      'entries' => $entries,
      'deductionSummary' => $deductionSummary,
      'notice' => $this->session->flashdata('payroll_notice'),
      'noticeType' => $this->session->flashdata('payroll_notice_type'),
    );

    $this->load->view('payroll_run', $result);
  }

  public function payrollRuns()
  {
    if (!$this->_is_admin_user()) {
      echo "Access Denied";
      return;
    }

    $settingsID = (int) $this->session->userdata('settingsID');
    $payrollRuns = $this->PayrollModel->getPayrollRuns($settingsID);
    $businessDetails = $this->CashModel->businessDetails($settingsID);

    $result = array(
      'business' => !empty($businessDetails) ? $businessDetails[0] : null,
      'payrollRuns' => $payrollRuns,
      'notice' => $this->session->flashdata('payroll_notice'),
      'noticeType' => $this->session->flashdata('payroll_notice_type'),
    );

    $this->load->view('payroll_runs', $result);
  }

  public function payrollPayslip()
  {
    if (!$this->_is_admin_user()) {
      echo "Access Denied";
      return;
    }

    $settingsID = (int) $this->session->userdata('settingsID');
    $entryID = (int) $this->input->get('id');
    $entry = $this->PayrollModel->getPayrollEntry($settingsID, $entryID);
    if (!$entry) {
      redirect('Page/payrollModule');
      return;
    }

    $items = $this->PayrollModel->getPayrollEntryItems($settingsID, $entryID);
    $businessDetails = $this->CashModel->businessDetails($settingsID);
    $result = array(
      'business' => !empty($businessDetails) ? $businessDetails[0] : null,
      'entry' => $entry,
      'items' => $items,
      'autoPrint' => $this->input->get('print') == '1' || strtolower((string) $this->input->get('autoprint')) === 'true',
    );

    $this->load->view('payroll_payslip', $result);
  }


  function employeeList()
  {
    if (!$this->_is_admin_user()) {
      redirect('Page/admin');
      return;
    }

    if ($this->input->post('addemployee')) {
      $settingsID = (int) $this->session->userdata('settingsID');
      $empID = trim((string) $this->input->post('empID'));

      if ($empID === '' || $this->db->where('empID', $empID)->limit(1)->count_all_results('employee') > 0) {
        $empID = $this->CashModel->generateNextEmployeeId($settingsID);
      }

      $fName = trim((string) $this->input->post('fName'));
      $mName = trim((string) $this->input->post('mName'));
      $lName = trim((string) $this->input->post('lName'));
      $email = strtolower(trim((string) $this->input->post('email')));

      $dateHired = $this->input->post('dateHired');
      $empPosition = trim((string) $this->input->post('empPosition'));
      $department = trim((string) $this->input->post('department'));
      $bDate = $this->input->post('bDate');

      if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $this->session->set_flashdata('msg', '<div class="alert alert-danger alert-dismissible fade show" role="alert">A valid employee email is required.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
        redirect('Page/employeeList');
        return;
      }

      $dateHired = !empty($dateHired) ? $dateHired : date('Y-m-d');
      $bDate = !empty($bDate) ? $bDate : '1970-01-01';

      date_default_timezone_set('Asia/Manila');

      $this->db->trans_start();

      $this->db->insert('employee', [
        'empID' => $empID,
        'fName' => $fName,
        'mName' => $mName,
        'lName' => $lName,
        'email' => $email,
        'dateHired' => $dateHired,
        'empPosition' => $empPosition,
        'department' => $department,
        'bDate' => $bDate,
        'empStat' => 'Active',
        'settingsID' => $settingsID,
      ]);
      $syncResult = $this->_sync_employee_user_account($settingsID, $empID, $fName, $mName, $lName, $email, (string) $bDate, $supportChatView, $supportChatReply);
      if (!$syncResult[0]) {
        $this->db->trans_rollback();
        $this->session->set_flashdata('msg', '<div class="alert alert-danger alert-dismissible fade show" role="alert">' . htmlspecialchars($syncResult[1] !== '' ? $syncResult[1] : 'Unable to create the employee user account.', ENT_QUOTES, 'UTF-8') . '<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
        redirect('Page/employeeList');
        return;
      }

      $this->_sync_employee_support_department($settingsID, $email, $department);

      $this->db->trans_complete();

      if ($this->db->trans_status() === FALSE) {
        $this->session->set_flashdata('msg', '<div class="alert alert-danger alert-dismissible fade show" role="alert">Unable to add employee. Please try again.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
      } else {
        $this->PayrollModel->savePayrollProfile($settingsID, $empID, array(
          'monthlySalary' => $this->input->post('monthlySalary'),
          'philhealthAmount' => $this->input->post('philhealthAmount'),
          'sssAmount' => $this->input->post('sssAmount'),
          'pagibigAmount' => $this->input->post('pagibigAmount'),
          'notes' => $this->input->post('payrollNotes'),
          'payrollStatus' => $this->input->post('payrollStatus') ?: 'active',
        ));

        $this->session->set_flashdata('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert">Employee added successfully.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
      }

      $currentFilter = trim((string) $this->input->get('status'));
      if ($currentFilter !== '') {
        redirect('Page/employeeList?status=' . urlencode($currentFilter));
      } else {
        redirect('Page/employeeList');
      }
      return;
    }

    $settingsID = (int) $this->session->userdata('settingsID');
    $statusFilter = trim((string) $this->input->get('status'));
    if ($statusFilter === '') {
      $statusFilter = 'Active';
    }
    $result['statusFilter'] = $statusFilter;
    $result['data'] = $this->CashModel->employeeList($settingsID, $statusFilter);
    $result['data1'] = $this->CashModel->getEmpID($settingsID);
    $result['nextEmployeeId'] = $this->CashModel->generateNextEmployeeId($settingsID);
    $result['payrollEmployees'] = $this->PayrollModel->getPayrollEmployees($settingsID);
    $this->load->view('employee_list', $result);
  }

  public function employmentHistory()
  {
    if (!$this->_is_admin_user()) {
      redirect('Page/admin');
      return;
    }

    $settingsID = (int) $this->session->userdata('settingsID');
    
    // Auto-create table if it doesn't exist
    if (!$this->db->table_exists('employee_employment_history')) {
      $this->db->query("
        CREATE TABLE IF NOT EXISTS `employee_employment_history` (
          `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
          `empID` VARCHAR(45) NOT NULL,
          `settingsID` INT NOT NULL,
          `company_name` VARCHAR(255) NOT NULL,
          `position` VARCHAR(255) NOT NULL,
          `start_date` DATE NOT NULL,
          `end_date` DATE DEFAULT NULL,
          `is_current` TINYINT(1) DEFAULT 0,
          `description` TEXT DEFAULT NULL,
          `reason_for_leaving` TEXT DEFAULT NULL,
          `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
          `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
          PRIMARY KEY (`id`),
          INDEX `idx_empID` (`empID`),
          INDEX `idx_settingsID` (`settingsID`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
      ");
    }

    // Handle delete
    if ($this->input->get('delete_id')) {
      $delete_id = (int) $this->input->get('delete_id');
      $this->db->where('id', $delete_id);
      $this->db->where('settingsID', $settingsID);
      $this->db->delete('employee_employment_history');
      $this->session->set_flashdata('success', 'Employment history deleted successfully');
      redirect('Page/employmentHistory');
      return;
    }

    // Handle form submission (add or edit)
    if ($this->input->post('add_employment_history') || $this->input->post('edit_employment_history')) {
      $edit_id = $this->input->post('edit_id') ? (int) $this->input->post('edit_id') : null;
      
      $data = array(
        'empID' => $this->input->post('empID'),
        'company_name' => $this->input->post('company_name'),
        'position' => $this->input->post('position'),
        'start_date' => $this->input->post('start_date'),
        'end_date' => $this->input->post('end_date') ? $this->input->post('end_date') : NULL,
        'is_current' => $this->input->post('is_current') ? 1 : 0,
        'description' => $this->input->post('description'),
        'reason_for_leaving' => $this->input->post('reason_for_leaving')
      );
      
      if ($edit_id) {
        // Update existing record
        $this->db->where('id', $edit_id);
        $this->db->where('settingsID', $settingsID);
        $this->db->update('employee_employment_history', $data);
        $this->session->set_flashdata('success', 'Employment history updated successfully');
      } else {
        // Add new record
        $data['settingsID'] = $settingsID;
        $this->db->insert('employee_employment_history', $data);
        $this->session->set_flashdata('success', 'Employment history added successfully');
      }
      
      redirect('Page/employmentHistory');
      return;
    }

    $result['employees'] = $this->CashModel->employeeList($settingsID, 'all');
    $result['employmentHistory'] = array();
    
    if ($this->db->table_exists('employee_employment_history')) {
      $this->db->where('settingsID', $settingsID);
      $this->db->order_by('start_date', 'DESC');
      $result['employmentHistory'] = $this->db->get('employee_employment_history')->result();
    }
    
    $this->load->view('employment_history', $result);
  }

  public function employeeEducation()
  {
    if (!$this->_is_admin_user()) {
      redirect('Page/admin');
      return;
    }

    $settingsID = (int) $this->session->userdata('settingsID');
    
    // Auto-create table if it doesn't exist
    if (!$this->db->table_exists('employee_education')) {
      $this->db->query("
        CREATE TABLE IF NOT EXISTS `employee_education` (
          `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
          `empID` VARCHAR(45) NOT NULL,
          `settingsID` INT NOT NULL,
          `institution_name` VARCHAR(255) NOT NULL,
          `degree` VARCHAR(255) NOT NULL,
          `field_of_study` VARCHAR(255) DEFAULT NULL,
          `start_date` DATE NOT NULL,
          `end_date` DATE DEFAULT NULL,
          `is_current` TINYINT(1) DEFAULT 0,
          `gpa` DECIMAL(3,2) DEFAULT NULL,
          `description` TEXT DEFAULT NULL,
          `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
          `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
          PRIMARY KEY (`id`),
          INDEX `idx_empID` (`empID`),
          INDEX `idx_settingsID` (`settingsID`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
      ");
    }

    // Handle delete
    if ($this->input->get('delete_id')) {
      $delete_id = (int) $this->input->get('delete_id');
      $this->db->where('id', $delete_id);
      $this->db->where('settingsID', $settingsID);
      $this->db->delete('employee_education');
      $this->session->set_flashdata('success', 'Education record deleted successfully');
      redirect('Page/employeeEducation');
      return;
    }

    // Handle form submission (add or edit)
    if ($this->input->post('add_education') || $this->input->post('edit_education')) {
      $edit_id = $this->input->post('edit_id') ? (int) $this->input->post('edit_id') : null;
      
      $data = array(
        'empID' => $this->input->post('empID'),
        'institution_name' => $this->input->post('institution_name'),
        'degree' => $this->input->post('degree'),
        'field_of_study' => $this->input->post('field_of_study'),
        'start_date' => $this->input->post('start_date'),
        'end_date' => $this->input->post('end_date') ? $this->input->post('end_date') : NULL,
        'is_current' => $this->input->post('is_current') ? 1 : 0,
        'gpa' => $this->input->post('gpa') ? $this->input->post('gpa') : NULL,
        'description' => $this->input->post('description')
      );
      
      if ($edit_id) {
        $this->db->where('id', $edit_id);
        $this->db->where('settingsID', $settingsID);
        $this->db->update('employee_education', $data);
        $this->session->set_flashdata('success', 'Education record updated successfully');
      } else {
        $data['settingsID'] = $settingsID;
        $this->db->insert('employee_education', $data);
        $this->session->set_flashdata('success', 'Education record added successfully');
      }
      
      redirect('Page/employeeEducation');
      return;
    }

    $result['employees'] = $this->CashModel->employeeList($settingsID, 'all');
    $result['education'] = array();
    
    if ($this->db->table_exists('employee_education')) {
      $this->db->where('settingsID', $settingsID);
      $this->db->order_by('end_date', 'DESC');
      $result['education'] = $this->db->get('employee_education')->result();
    }
    
    $this->load->view('employee_education', $result);
  }

  public function employeeSkills()
  {
    if (!$this->_is_admin_user()) {
      redirect('Page/admin');
      return;
    }

    $settingsID = (int) $this->session->userdata('settingsID');
    
    // Auto-create table if it doesn't exist
    if (!$this->db->table_exists('employee_skills')) {
      $this->db->query("
        CREATE TABLE IF NOT EXISTS `employee_skills` (
          `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
          `empID` VARCHAR(45) NOT NULL,
          `settingsID` INT NOT NULL,
          `skill_name` VARCHAR(255) NOT NULL,
          `skill_type` ENUM('skill', 'certification', 'license') NOT NULL DEFAULT 'skill',
          `proficiency_level` ENUM('beginner', 'intermediate', 'advanced', 'expert') DEFAULT 'intermediate',
          `issuing_organization` VARCHAR(255) DEFAULT NULL,
          `issue_date` DATE DEFAULT NULL,
          `expiry_date` DATE DEFAULT NULL,
          `credential_number` VARCHAR(100) DEFAULT NULL,
          `description` TEXT DEFAULT NULL,
          `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
          `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
          PRIMARY KEY (`id`),
          INDEX `idx_empID` (`empID`),
          INDEX `idx_settingsID` (`settingsID`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
      ");
    }

    // Handle delete
    if ($this->input->get('delete_id')) {
      $delete_id = (int) $this->input->get('delete_id');
      $this->db->where('id', $delete_id);
      $this->db->where('settingsID', $settingsID);
      $this->db->delete('employee_skills');
      $this->session->set_flashdata('success', 'Skill/Certification deleted successfully');
      redirect('Page/employeeSkills');
      return;
    }

    // Handle form submission (add or edit)
    if ($this->input->post('add_skill') || $this->input->post('edit_skill')) {
      $edit_id = $this->input->post('edit_id') ? (int) $this->input->post('edit_id') : null;
      
      $data = array(
        'empID' => $this->input->post('empID'),
        'skill_name' => $this->input->post('skill_name'),
        'skill_type' => $this->input->post('skill_type'),
        'proficiency_level' => $this->input->post('proficiency_level'),
        'issuing_organization' => $this->input->post('issuing_organization'),
        'issue_date' => $this->input->post('issue_date') ? $this->input->post('issue_date') : NULL,
        'expiry_date' => $this->input->post('expiry_date') ? $this->input->post('expiry_date') : NULL,
        'credential_number' => $this->input->post('credential_number'),
        'description' => $this->input->post('description')
      );
      
      if ($edit_id) {
        $this->db->where('id', $edit_id);
        $this->db->where('settingsID', $settingsID);
        $this->db->update('employee_skills', $data);
        $this->session->set_flashdata('success', 'Skill/Certification updated successfully');
      } else {
        $data['settingsID'] = $settingsID;
        $this->db->insert('employee_skills', $data);
        $this->session->set_flashdata('success', 'Skill/Certification added successfully');
      }
      
      redirect('Page/employeeSkills');
      return;
    }

    $result['employees'] = $this->CashModel->employeeList($settingsID, 'all');
    $result['skills'] = array();
    
    if ($this->db->table_exists('employee_skills')) {
      $this->db->where('settingsID', $settingsID);
      $this->db->order_by('skill_name', 'ASC');
      $result['skills'] = $this->db->get('employee_skills')->result();
    }
    
    $this->load->view('employee_skills', $result);
  }

  public function employeeEmergencyContacts()
  {
    if (!$this->_is_admin_user()) {
      redirect('Page/admin');
      return;
    }

    $settingsID = (int) $this->session->userdata('settingsID');
    
    // Auto-create table if it doesn't exist
    if (!$this->db->table_exists('employee_emergency_contacts')) {
      $this->db->query("
        CREATE TABLE IF NOT EXISTS `employee_emergency_contacts` (
          `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
          `empID` VARCHAR(45) NOT NULL,
          `settingsID` INT NOT NULL,
          `contact_name` VARCHAR(255) NOT NULL,
          `relationship` VARCHAR(100) NOT NULL,
          `phone_number` VARCHAR(50) NOT NULL,
          `alternative_phone` VARCHAR(50) DEFAULT NULL,
          `email` VARCHAR(255) DEFAULT NULL,
          `address` TEXT DEFAULT NULL,
          `is_primary` TINYINT(1) DEFAULT 0,
          `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
          `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
          PRIMARY KEY (`id`),
          INDEX `idx_empID` (`empID`),
          INDEX `idx_settingsID` (`settingsID`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
      ");
    }

    // Handle delete
    if ($this->input->get('delete_id')) {
      $delete_id = (int) $this->input->get('delete_id');
      $this->db->where('id', $delete_id);
      $this->db->where('settingsID', $settingsID);
      $this->db->delete('employee_emergency_contacts');
      $this->session->set_flashdata('success', 'Emergency contact deleted successfully');
      redirect('Page/employeeEmergencyContacts');
      return;
    }

    // Handle form submission (add or edit)
    if ($this->input->post('add_emergency_contact') || $this->input->post('edit_emergency_contact')) {
      $edit_id = $this->input->post('edit_id') ? (int) $this->input->post('edit_id') : null;
      
      $data = array(
        'empID' => $this->input->post('empID'),
        'contact_name' => $this->input->post('contact_name'),
        'relationship' => $this->input->post('relationship'),
        'phone_number' => $this->input->post('phone_number'),
        'alternative_phone' => $this->input->post('alternative_phone'),
        'email' => $this->input->post('email'),
        'address' => $this->input->post('address'),
        'is_primary' => $this->input->post('is_primary') ? 1 : 0
      );
      
      if ($edit_id) {
        $this->db->where('id', $edit_id);
        $this->db->where('settingsID', $settingsID);
        $this->db->update('employee_emergency_contacts', $data);
        $this->session->set_flashdata('success', 'Emergency contact updated successfully');
      } else {
        $data['settingsID'] = $settingsID;
        $this->db->insert('employee_emergency_contacts', $data);
        $this->session->set_flashdata('success', 'Emergency contact added successfully');
      }
      
      redirect('Page/employeeEmergencyContacts');
      return;
    }

    $result['employees'] = $this->CashModel->employeeList($settingsID, 'all');
    $result['emergencyContacts'] = array();
    
    if ($this->db->table_exists('employee_emergency_contacts')) {
      $this->db->where('settingsID', $settingsID);
      $this->db->order_by('is_primary', 'DESC');
      $result['emergencyContacts'] = $this->db->get('employee_emergency_contacts')->result();
    }
    
    $this->load->view('employee_emergency_contacts', $result);
  }

  public function employeeDocuments()
  {
    if (!$this->_is_admin_user()) {
      redirect('Page/admin');
      return;
    }

    $settingsID = (int) $this->session->userdata('settingsID');
    
    // Auto-create table if it doesn't exist
    if (!$this->db->table_exists('employee_documents')) {
      $this->db->query("
        CREATE TABLE IF NOT EXISTS `employee_documents` (
          `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
          `empID` VARCHAR(45) NOT NULL,
          `settingsID` INT NOT NULL,
          `document_type` VARCHAR(100) NOT NULL,
          `document_name` VARCHAR(255) NOT NULL,
          `file_path` VARCHAR(500) NOT NULL,
          `file_size` BIGINT DEFAULT NULL,
          `file_mime_type` VARCHAR(100) DEFAULT NULL,
          `description` TEXT DEFAULT NULL,
          `issue_date` DATE DEFAULT NULL,
          `expiry_date` DATE DEFAULT NULL,
          `is_confidential` TINYINT(1) DEFAULT 0,
          `uploaded_by` INT NOT NULL,
          `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
          `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
          PRIMARY KEY (`id`),
          INDEX `idx_empID` (`empID`),
          INDEX `idx_settingsID` (`settingsID`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
      ");
    }

    // Handle delete
    if ($this->input->get('delete_id')) {
      $delete_id = (int) $this->input->get('delete_id');
      
      // Get file path before deleting
      $this->db->where('id', $delete_id);
      $this->db->where('settingsID', $settingsID);
      $doc = $this->db->get('employee_documents')->row();
      
      if ($doc && !empty($doc->file_path)) {
        $file_path = FCPATH . $doc->file_path;
        if (file_exists($file_path)) {
          unlink($file_path);
        }
      }
      
      $this->db->where('id', $delete_id);
      $this->db->where('settingsID', $settingsID);
      $this->db->delete('employee_documents');
      $this->session->set_flashdata('success', 'Document deleted successfully');
      redirect('Page/employeeDocuments');
      return;
    }

    // Handle form submission (add or edit)
    if ($this->input->post('upload_document') || $this->input->post('edit_document')) {
      $user_id = (int) $this->session->userdata('user_id');
      $edit_id = $this->input->post('edit_id') ? (int) $this->input->post('edit_id') : null;
      
      // Handle file upload
      $file_path = '';
      $file_size = 0;
      $file_mime_type = '';
      
      if (isset($_FILES['document_file']) && $_FILES['document_file']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = FCPATH . 'uploads/employee_documents/';
        if (!is_dir($upload_dir)) {
          mkdir($upload_dir, 0755, true);
        }
        
        $file_name = time() . '_' . $_FILES['document_file']['name'];
        $file_path = 'uploads/employee_documents/' . $file_name;
        
        if (move_uploaded_file($_FILES['document_file']['tmp_name'], $upload_dir . $file_name)) {
          $file_size = $_FILES['document_file']['size'];
          $file_mime_type = $_FILES['document_file']['type'];
        }
      }
      
      if ($file_path || $edit_id) {
        $data = array(
          'empID' => $this->input->post('empID'),
          'document_type' => $this->input->post('document_type'),
          'document_name' => $this->input->post('document_name'),
          'description' => $this->input->post('description'),
          'issue_date' => $this->input->post('issue_date') ? $this->input->post('issue_date') : NULL,
          'expiry_date' => $this->input->post('expiry_date') ? $this->input->post('expiry_date') : NULL,
          'is_confidential' => $this->input->post('is_confidential') ? 1 : 0
        );
        
        if ($file_path) {
          $data['file_path'] = $file_path;
          $data['file_size'] = $file_size;
          $data['file_mime_type'] = $file_mime_type;
        }
        
        if ($edit_id) {
          $this->db->where('id', $edit_id);
          $this->db->where('settingsID', $settingsID);
          $this->db->update('employee_documents', $data);
          $this->session->set_flashdata('success', 'Document updated successfully');
        } else {
          $data['settingsID'] = $settingsID;
          $data['uploaded_by'] = $user_id;
          $this->db->insert('employee_documents', $data);
          $this->session->set_flashdata('success', 'Document uploaded successfully');
        }
        
        redirect('Page/employeeDocuments');
        return;
      } else {
        $this->session->set_flashdata('danger', 'Failed to upload file');
      }
    }

    $result['employees'] = $this->CashModel->employeeList($settingsID, 'all');
    $result['documents'] = array();
    
    if ($this->db->table_exists('employee_documents')) {
      $this->db->where('settingsID', $settingsID);
      $this->db->order_by('created_at', 'DESC');
      $result['documents'] = $this->db->get('employee_documents')->result();
    }
    
    $this->load->view('employee_documents', $result);
  }

  public function annualGoals()
  {
    $settingsID = $this->session->userdata('settingsID');
    $level = $this->session->userdata('level');

    if (!$settingsID) {
      redirect('Login');
      return;
    }

    // Only admin level users can access this page
    if ($level !== 'Admin') {
      $this->session->set_flashdata('error', 'You do not have permission to access this page.');
      redirect('Page/dashboard');
      return;
    }

    // Handle form submissions (Admin only)
    if ($level === 'Admin' && $this->input->post('saveGoal')) {
      $year = (int) $this->input->post('goalYear');
      $data = array(
        'settingsID' => $settingsID,
        'goalYear' => $year,
        'targetClients' => (int) $this->input->post('targetClients'),
        'targetIncome' => (float) $this->input->post('targetIncome'),
        'notes' => $this->input->post('notes', true),
        'createdBy' => $this->session->userdata('name') ?: 'System'
      );

      $this->CashModel->saveAnnualGoal($data);
      $this->session->set_flashdata('success', 'Annual goals for ' . $year . ' saved successfully.');
      redirect('Page/annualGoals');
      return;
    }

    if ($level === 'Admin' && $this->input->post('deleteGoal')) {
      $goalID = (int) $this->input->post('goalID');
      $this->CashModel->deleteAnnualGoal($goalID, $settingsID);
      $this->session->set_flashdata('success', 'Goal deleted successfully.');
      redirect('Page/annualGoals');
      return;
    }

    // Get all goals
    $goals = $this->CashModel->getAnnualGoals($settingsID);
    $result['goals'] = $goals;

    // Calculate progress for each goal
    $result['progressData'] = array();
    foreach ($goals as $goal) {
      $result['progressData'][$goal->goalYear] = $this->CashModel->getYearlyProgress($settingsID, $goal->goalYear);
    }

    // Current year goal for quick view
    $currentYear = date('Y');
    $result['currentGoal'] = $this->CashModel->getAnnualGoalByYear($settingsID, $currentYear);
    $result['currentProgress'] = $this->CashModel->getYearlyProgress($settingsID, $currentYear);
    $result['isAdmin'] = ($level === 'Admin');
    $result['currentYear'] = $currentYear;

    $this->load->view('annual_goals', $result);
  }

  public function annualGoalsReport()
  {
    $settingsID = $this->session->userdata('settingsID');
    $level = $this->session->userdata('level');

    if (!$settingsID) {
      redirect('Login');
      return;
    }

    // Only admin level users can access this page
    if ($level !== 'Admin') {
      $this->session->set_flashdata('error', 'You do not have permission to access this page.');
      redirect('Page/dashboard');
      return;
    }

    $year = (int) $this->input->get('year');
    if (!$year) {
      $year = date('Y');
    }

    $result['goal'] = $this->CashModel->getAnnualGoalByYear($settingsID, $year);
    $result['progress'] = $this->CashModel->getYearlyProgress($settingsID, $year);
    $result['year'] = $year;
    $result['isAdmin'] = ($level === 'Admin');

    // Monthly breakdown
    $result['monthlyData'] = array();
    for ($m = 1; $m <= 12; $m++) {
      $result['monthlyData'][$m] = array(
        'clients' => $this->_getMonthlyClientCount($settingsID, $year, $m),
        'income' => $this->_getMonthlyIncome($settingsID, $year, $m)
      );
    }

    $this->load->view('annual_goals_report', $result);
  }

  private function _getMonthlyClientCount($settingsID, $year, $month)
  {
    return $this->db
      ->where('settingsID', $settingsID)
      ->where('MONTH(created_at)', $month)
      ->where('YEAR(created_at)', $year)
      ->where("COALESCE(ClientStat, '') !=", 'Deleted')
      ->count_all_results('customers');
  }

  private function _getMonthlyIncome($settingsID, $year, $month)
  {
    $result = $this->db
      ->select('SUM(AmountPaid) as total')
      ->where('settingsID', $settingsID)
      ->where('MONTH(PDate)', $month)
      ->where('YEAR(PDate)', $year)
      ->where('ORStat', 'valid')
      ->get('payments')
      ->row();
    return (float) ($result->total ?? 0);
  }

  public function attendanceList()
  {
    $username = (string) $this->session->userdata('username');
    $settingsID = $this->session->userdata('settingsID');
    $level = (string) $this->session->userdata('level');

    if ($username === '') {
      redirect('Login');
      return;
    }

    // ✅ NEW: accept ?date=YYYY-MM-DD and treat it like from/to
    $date = trim((string) $this->input->get('date'));

    $from = trim((string) $this->input->get('from'));
    $to = trim((string) $this->input->get('to'));

    // If date is provided, override range
    if ($date !== '') {
      $from = $to = $date;
    }

    // default range = today..today
    if ($from === '' && $to === '') {
      $from = $to = date('Y-m-d');
    } else {
      if ($from === '')
        $from = $to;
      if ($to === '')
        $to = $from;
    }

    // normalize swap
    if ($from !== '' && $to !== '' && strtotime($from) > strtotime($to)) {
      $tmp = $from;
      $from = $to;
      $to = $tmp;
    }

    // ✅ Use the SAME working range methods
    if ($level === 'Admin') {
      $raw = $this->CashModel->attendanceListByRange($settingsID, $from, $to);
      $result = $this->_aggregateAttendance($raw, true);
    } else {
      $raw = $this->CashModel->attendanceListByEmployeeRange($settingsID, $username, $from, $to);
      $result = $this->_aggregateAttendance($raw, false);
    }

    $result['range_from'] = $from;
    $result['range_to'] = $to;

    $this->load->view('attendance_list', $result);
  }



  private function _aggregateAttendance($rows, $isAdmin)
  {
    $grouped = [];
    $grandTotals = [];
    $overallSeconds = 0;

    foreach ($rows as $row) {
      $empKey = !empty($row->IDNumber) ? $row->IDNumber : (!empty($row->user_id) ? $row->user_id : (!empty($row->username) ? $row->username : ''));
      $dateKey = $row->logDate;
      $fullKey = $empKey . '|' . $dateKey;

      // Build visible time breakdown entries. Closed intervals count toward totals,
      // while open punches still render the recorded time-in for visibility.
      $intervals = [];
      foreach (
        [
          ['time_in' => $row->amTimeIn ?? '', 'time_out' => $row->amTimeOut ?? ''],
          ['time_in' => $row->pmTimeIn ?? '', 'time_out' => $row->pmTimeOut ?? ''],
        ] as $pair
      ) {
        $timeIn = trim((string) $pair['time_in']);
        $timeOut = trim((string) $pair['time_out']);

        if ($timeIn === '') {
          continue;
        }

        $start = $this->_parseTime($dateKey, $timeIn);
        if (!$start) {
          continue;
        }

        if ($timeOut !== '') {
          $end = $this->_parseTime($dateKey, $timeOut);
          if ($end && $end > $start) {
            $intervals[] = [
              'label' => date('g:i A', $start) . ' - ' . date('g:i A', $end),
              'seconds' => $end - $start,
              'start' => $start,
            ];
            continue;
          }
        }

        $intervals[] = [
          'label' => date('g:i A', $start) . ' - Time out pending',
          'seconds' => 0,
          'start' => $start,
        ];
      }

      if (!isset($grouped[$fullKey])) {
        $grouped[$fullKey] = [
          'logDate' => $dateKey,
          'IDNumber' => $row->IDNumber ?? '',
          'user_id' => $row->user_id ?? null,
          'username' => $row->username ?? null,
          'fName' => $row->fName ?? '',
          'mName' => $row->mName ?? '',
          'lName' => $row->lName ?? '',
          'accomplishment_count' => 0,
          'has_time_in' => false,
          'intervals' => [],
          'total_seconds' => 0,
        ];
      }

      if (!empty($row->amTimeIn) || !empty($row->pmTimeIn)) {
        $grouped[$fullKey]['has_time_in'] = true;
      }

      // accumulate intervals and totals
      foreach ($intervals as $intv) {
        $secs = isset($intv['seconds']) ? (int) $intv['seconds'] : 0;
        $grouped[$fullKey]['intervals'][] = $intv;
        if ($secs <= 0) {
          continue;
        }
        $grouped[$fullKey]['total_seconds'] += $secs;
        if ($empKey !== '') {
          $grandTotals[$empKey] = ($grandTotals[$empKey] ?? 0) + $secs;
        }
        $overallSeconds += $secs;
      }

      // accomplishment count per day
      if ($empKey !== '' && !empty($row->logDate)) {
        $grouped[$fullKey]['accomplishment_count'] = $this->CashModel->accomplishmentCountForDate($this->session->userdata('settingsID'), $empKey, $row->logDate);
      }
    }

    // Format labels
    foreach ($grouped as &$g) {
      if (!empty($g['intervals'])) {
        usort($g['intervals'], function ($a, $b) {
          return ($a['start'] ?? 0) <=> ($b['start'] ?? 0);
        });
      }
      $g['total_hours_label'] = $this->_formatSeconds($g['total_seconds']);
      $empKey = !empty($g['IDNumber']) ? $g['IDNumber'] : (!empty($g['user_id']) ? $g['user_id'] : (!empty($g['username']) ? $g['username'] : ''));
      $g['grand_total_label'] = $empKey !== '' ? $this->_formatSeconds($grandTotals[$empKey] ?? 0) : $g['total_hours_label'];
    }
    unset($g);

    $grandTotalAll = $this->_formatSeconds($overallSeconds);

    return [
      'data' => array_map(function ($item) {
        return (object) $item;
      }, array_values($grouped)),
      'grand_total_all' => $grandTotalAll,
    ];
  }

  private function _formatSeconds($seconds)
  {
    $hours = floor($seconds / 3600);
    $minutes = floor(($seconds % 3600) / 60);
    return sprintf('%02d:%02d', $hours, $minutes);
  }

  /**
   * Parse a time string (either 12h with AM/PM or 24h) combined with a date into a timestamp.
   * Handles malformed values like "13:56:14 PM" by attempting multiple formats.
   */
  private function _parseTime($date, $timeStr)
  {
    $timeStr = trim((string) $timeStr);
    if ($timeStr === '') {
      return null;
    }

    $formats = [
      'Y-m-d g:i:s A',
      'Y-m-d g:i A',
      'Y-m-d H:i:s',
      'Y-m-d H:i',
      'Y-m-d H:i:s A',
      'Y-m-d H:i A',
    ];

    foreach ($formats as $fmt) {
      $dt = \DateTime::createFromFormat($fmt, $date . ' ' . $timeStr);
      if ($dt instanceof \DateTime && empty($dt::getLastErrors()['warning_count']) && empty($dt::getLastErrors()['error_count'])) {
        return $dt->getTimestamp();
      }
    }

    // If still failing, strip trailing AM/PM and retry 24h
    $stripped = preg_replace('/\\s*(AM|PM)$/i', '', $timeStr);
    if ($stripped !== $timeStr) {
      foreach (['Y-m-d H:i:s', 'Y-m-d H:i'] as $fmt) {
        $dt = \DateTime::createFromFormat($fmt, $date . ' ' . $stripped);
        if ($dt instanceof \DateTime && empty($dt::getLastErrors()['warning_count']) && empty($dt::getLastErrors()['error_count'])) {
          return $dt->getTimestamp();
        }
      }
    }

    $ts = strtotime($date . ' ' . $timeStr);
    return $ts !== false ? $ts : null;
  }

  private function _aggregateDtrForUser($rows)
  {
    $grouped = [];

    foreach ($rows as $row) {
      $dateKey = $row->logDate;
      if (!isset($grouped[$dateKey])) {
        $grouped[$dateKey] = [
          'logDate' => $dateKey,
          'intervals' => [],
          'am_intervals' => [],
          'pm_intervals' => [],
          'total_seconds' => 0,
        ];
      }

      $intervals = [];
      if (!empty($row->amTimeIn) && !empty($row->amTimeOut)) {
        $start = $this->_parseTime($dateKey, $row->amTimeIn);
        $end = $this->_parseTime($dateKey, $row->amTimeOut);
        if ($start && $end && $end > $start) {
          $intervals[] = [$start, $end];
        }
      } elseif (!empty($row->amTimeIn) && empty($row->amTimeOut)) {
        $start = $this->_parseTime($dateKey, $row->amTimeIn);
        if ($start) {
          $intervals[] = ['open' => true, 'label' => date('g:i A', $start) . ' - pending', 'start' => $start];
        }
      }
      if (!empty($row->pmTimeIn) && !empty($row->pmTimeOut)) {
        $start = $this->_parseTime($dateKey, $row->pmTimeIn);
        $end = $this->_parseTime($dateKey, $row->pmTimeOut);
        if ($start && $end && $end > $start) {
          $intervals[] = [$start, $end];
        }
      } elseif (!empty($row->pmTimeIn) && empty($row->pmTimeOut)) {
        $start = $this->_parseTime($dateKey, $row->pmTimeIn);
        if ($start) {
          $intervals[] = ['open' => true, 'label' => date('g:i A', $start) . ' - pending', 'start' => $start];
        }
      }

      foreach ($intervals as $intv) {
        if (isset($intv['open'])) {
          $grouped[$dateKey]['intervals'][] = [
            'label' => $intv['label'],
            'seconds' => 0,
            'open' => true,
            'start' => $intv['start'] ?? 0,
          ];
          $bucket = 'am_intervals';
          if (isset($intv['start']) && (int) date('G', $intv['start']) >= 12) {
            $bucket = 'pm_intervals';
          } elseif (stripos($intv['label'], 'PM') !== false) {
            $bucket = 'pm_intervals';
          }
          $grouped[$dateKey][$bucket][] = [
            'label' => $intv['label'],
            'seconds' => 0,
            'open' => true,
            'start' => $intv['start'] ?? 0,
          ];
        } else {
          $secs = $intv[1] - $intv[0];
          $label = date('g:i A', $intv[0]) . ' - ' . date('g:i A', $intv[1]);
          $grouped[$dateKey]['intervals'][] = [
            'label' => $label,
            'seconds' => $secs,
            'open' => false,
            'start' => $intv[0],
          ];
          $bucket = 'am_intervals';
          $endHour = (int) date('G', $intv[1]);
          $startHour = (int) date('G', $intv[0]);
          if ($endHour >= 12 || $startHour >= 12) {
            $bucket = 'pm_intervals';
          }
          $grouped[$dateKey][$bucket][] = [
            'label' => $label,
            'seconds' => $secs,
            'open' => false,
            'start' => $intv[0],
          ];
          $grouped[$dateKey]['total_seconds'] += $secs;
        }
      }
    }

    // format totals
    foreach ($grouped as &$g) {
      if (!empty($g['intervals'])) {
        usort($g['intervals'], function ($a, $b) {
          return ($a['start'] ?? 0) <=> ($b['start'] ?? 0);
        });
      }
      if (!empty($g['am_intervals'])) {
        usort($g['am_intervals'], function ($a, $b) {
          return ($a['start'] ?? 0) <=> ($b['start'] ?? 0);
        });
      }
      if (!empty($g['pm_intervals'])) {
        usort($g['pm_intervals'], function ($a, $b) {
          return ($a['start'] ?? 0) <=> ($b['start'] ?? 0);
        });
      }
      $g['total_label'] = $this->_formatSeconds($g['total_seconds']);
    }
    unset($g);

    // return as objects for easier use in views
    return array_map(function ($item) {
      return (object) $item;
    }, array_values($grouped));
  }








  public function accomplishments()
  {
    $settingsID = $this->session->userdata('settingsID');
    $user_id = $this->session->userdata('user_id');
    $level = $this->session->userdata('level');

    $data['data'] = [];
    $data['filter_applied'] = false;
    $data['selected_month'] = date('n');
    $data['selected_year'] = date('Y');

    if ($this->input->post('filter')) {
      $month = $this->input->post('month');
      $year = $this->input->post('year');

      if ($level === 'Admin') {
        $data['data'] = $this->CashModel->accomplishmentsAdminFiltered($settingsID, $month, $year);
      } else {
        $data['data'] = $this->CashModel->accomplishmentsStaffFiltered($settingsID, $user_id, $month, $year);
      }

      $data['filter_applied'] = true;
      $data['selected_month'] = $month;
      $data['selected_year'] = $year;
    } elseif ($level === 'Admin') {
      $data['data'] = $this->CashModel->accomplishmentsAdminFiltered($settingsID, $data['selected_month'], $data['selected_year']);
    } else {
      $data['data'] = $this->CashModel->accomplishmentsStaffFiltered($settingsID, $user_id, $data['selected_month'], $data['selected_year']);
    }

    $data['pointsEnabled'] = $this->db->field_exists('points', 'projects_task_stat');
    $this->load->view('accomplishments', $data);
  }


  function empProfile()
  {

    if ($this->session->userdata('level') === 'Admin') {
      $settingsID = $this->session->userdata('settingsID');
      $name = $this->input->get('name');
      $date = $this->input->get('date');
      $id = $this->input->get('id');

      $result['data'] = $this->CashModel->accomplishmentsStaffbyDate($settingsID, $name, $date);
      $result['data2'] = $this->CashModel->updateEmployee($id);
      $this->load->view('employee_profile', $result);
    } else {
      echo 'Access Denied';
    }
  }

  function accomplishmentsPerEmployee()
  {

    if ($this->session->userdata('level') === 'Admin') {
      $settingsID = $this->session->userdata('settingsID');
      $name = $this->input->get('name');
      $date = $this->input->get('date');

      $result['data'] = $this->CashModel->accomplishmentsStaffbyDate($settingsID, $name, $date);
      $result['employee'] = $this->CashModel->getUserFlexible($settingsID, $name);
      $result['projects'] = $this->CashModel->getProjectName($settingsID);
      $result['pointsEnabled'] = $this->db->field_exists('points', 'projects_task_stat');
      $result['selectedUserId'] = $name;
      $result['selectedDate'] = $date;
      $this->load->view('accomplishments_per_employee', $result);
    } else {
      echo 'Access Denied';
    }
  }

  public function addAccomplishment()
  {
    if (strtolower(trim((string) $this->session->userdata('level'))) !== 'admin') {
      echo 'Access Denied';
      return;
    }

    if ($this->input->method() === 'post') {
      $settingsID = $this->session->userdata('settingsID');
      $assignedPerson = trim((string) $this->input->post('assignedPerson'));
      $task = $this->_normalizeTaskLabel($this->input->post('task'));
      $project = $this->input->post('project');
      $priority = (string) $this->input->post('priority');
      $note = trim((string) $this->input->post('note'));
      $pointsRaw = $this->input->post('points');
      $dateOnly = trim((string) $this->input->post('accomplishedDate'));

      if ($task === '' || $assignedPerson === '' || $project === null || $project === '') {
        $this->session->set_flashdata('danger', 'Task, project, and assigned employee are required.');
      } else {
        if ($dateOnly === '') {
          $dateOnly = date('Y-m-d');
        }
        $dateTs = strtotime($dateOnly);
        if ($dateTs === false) {
          $dateTs = strtotime(date('Y-m-d'));
          $dateOnly = date('Y-m-d');
        }

        if (!in_array($priority, ['1', '2', '3'], true)) {
          $priority = '2';
        }

        $points = is_numeric($pointsRaw) ? (int) $pointsRaw : 1;
        if ($points < 1) {
          $points = 1;
        }

        date_default_timezone_set('Asia/Manila');
        $datePosted = date('Y-m-d H:i:s', $dateTs);
        $addedBy = trim((string) $this->session->userdata('username'));
        if ($addedBy === '') {
          $addedBy = 'system';
        }

        $taskData = [
          'task' => $task,
          'reportedDate' => $dateOnly,
          'dueDate' => $dateOnly,
          'projectID' => $project,
          'taskStat' => '0',
          'priority' => $priority,
          'settingsID' => $settingsID,
          'assignedPerson' => $assignedPerson,
          'attachment_link' => null,
          'added_by' => $addedBy
        ];

        if ($this->db->field_exists('completed_by', 'projects_task')) {
          if (is_numeric($assignedPerson)) {
            $taskData['completed_by'] = (int) $assignedPerson;
          } else {
            $assignedUser = $this->CashModel->getUserFlexible($settingsID, $assignedPerson);
            if ($assignedUser && isset($assignedUser->user_id) && is_numeric($assignedUser->user_id)) {
              $taskData['completed_by'] = (int) $assignedUser->user_id;
            }
          }
        }

        $this->db->trans_begin();

        $taskInsertOk = $this->db->insert('projects_task', $taskData);
        $taskId = (int) $this->db->insert_id();

        if (!$taskInsertOk || $taskId <= 0) {
          $this->db->trans_rollback();
          $dbError = $this->db->error();
          $errorMsg = !empty($dbError['message']) ? (' ' . $dbError['message']) : '';
          $this->session->set_flashdata('danger', 'Unable to add accomplishment.' . $errorMsg);
        } else {
          $statData = [
            'taskID' => $taskId,
            'note' => $note,
            'datePosted' => $datePosted,
            'postedBy' => $addedBy,
            'taskStat' => '0'
          ];

          if ($this->db->field_exists('points', 'projects_task_stat')) {
            $statData['points'] = $points;
          }

          $statInsertOk = $this->db->insert('projects_task_stat', $statData);
          if (!$statInsertOk) {
            $this->db->trans_rollback();
            $dbError = $this->db->error();
            $errorMsg = !empty($dbError['message']) ? (' ' . $dbError['message']) : '';
            $this->session->set_flashdata('danger', 'Unable to add accomplishment.' . $errorMsg);
          } else {
            $this->db->trans_commit();
            $this->session->set_flashdata('success', 'Accomplishment added successfully.');
          }
        }
      }
    }

    $returnName = $this->input->post('return_name');
    $returnDate = $this->input->post('return_date');

    $returnUserId = trim((string) $this->input->post('return_user_id'));
    $returnReportPeriod = trim((string) $this->input->post('return_report_period'));
    $returnEndDate = trim((string) $this->input->post('return_end_date'));

    if ($returnUserId !== '') {
      $params = ['user_id' => $returnUserId];
      if ($returnReportPeriod !== '') {
        $params['report_period'] = $returnReportPeriod;
      }
      if ($returnEndDate !== '') {
        $params['end_date'] = $returnEndDate;
      }
      $qs = http_build_query($params);
      redirect('Page/employeeAccomplishmentData' . ($qs ? ('?' . $qs) : ''));
      return;
    }

    if ($returnName !== null && $returnDate !== null) {
      redirect('Page/accomplishmentsPerEmployee?name=' . urlencode((string) $returnName) . '&date=' . urlencode((string) $returnDate));
      return;
    }

    redirect('Page/accomplishments');
  }

  public function updateAccomplishmentPoints()
  {
    if ($this->session->userdata('level') !== 'Admin') {
      echo 'Access Denied';
      return;
    }

    if ($this->input->method() === 'post') {
      $ptsId = $this->input->post('pts_id');
      $pointsRaw = $this->input->post('points');

      if (!$this->db->field_exists('points', 'projects_task_stat')) {
        $this->session->set_flashdata('danger', 'Points column is missing. Please update the database schema.');
      } elseif ($ptsId === null || $ptsId === '') {
        $this->session->set_flashdata('danger', 'No accomplishment selected for points update.');
      } else {
        $points = is_numeric($pointsRaw) ? (int) $pointsRaw : 1;
        if ($points < 0) {
          $points = 0;
        }

        $this->db->where('ptsID', $ptsId);
        $this->db->update('projects_task_stat', ['points' => $points]);

        if ($this->db->affected_rows() > 0) {
          $this->session->set_flashdata('success', 'Accomplishment points updated.');
        } else {
          $this->session->set_flashdata('danger', 'No points were updated. Please try again.');
        }
      }
    }

    $returnName = $this->input->post('return_name');
    $returnDate = $this->input->post('return_date');

    $returnUserId = trim((string) $this->input->post('return_user_id'));
    $returnReportPeriod = trim((string) $this->input->post('return_report_period'));
    $returnEndDate = trim((string) $this->input->post('return_end_date'));

    if ($returnUserId !== '') {
      $params = ['user_id' => $returnUserId];
      if ($returnReportPeriod !== '') {
        $params['report_period'] = $returnReportPeriod;
      }
      if ($returnEndDate !== '') {
        $params['end_date'] = $returnEndDate;
      }
      $qs = http_build_query($params);
      redirect('Page/employeeAccomplishmentData' . ($qs ? ('?' . $qs) : ''));
      return;
    }

    if ($returnName !== null && $returnDate !== null) {
      redirect('Page/accomplishmentsPerEmployee?name=' . urlencode((string) $returnName) . '&date=' . urlencode((string) $returnDate));
      return;
    }

    redirect('Page/accomplishments');
  }

  public function updateAccomplishmentComment()
  {
    if ($this->session->userdata('level') !== 'Admin') {
      echo 'Access Denied';
      return;
    }

    if ($this->input->method() === 'post') {
      $ptsId = $this->input->post('pts_id');
      $note = $this->input->post('note');

      if ($ptsId === null || $ptsId === '') {
        $this->session->set_flashdata('danger', 'No accomplishment selected for comment update.');
      } else {
        $exists = $this->db->select('ptsID')
          ->from('projects_task_stat')
          ->where('ptsID', $ptsId)
          ->limit(1)
          ->get()
          ->row();

        if (!$exists) {
          $this->session->set_flashdata('danger', 'Comment update failed. Please try again.');
        } else {
          $this->db->where('ptsID', $ptsId);
          $this->db->update('projects_task_stat', ['note' => $note]);
          $this->session->set_flashdata('success', 'Accomplishment comment updated.');
        }
      }
    }

    $returnName = $this->input->post('return_name');
    $returnDate = $this->input->post('return_date');

    $returnUserId = trim((string) $this->input->post('return_user_id'));
    $returnReportPeriod = trim((string) $this->input->post('return_report_period'));
    $returnEndDate = trim((string) $this->input->post('return_end_date'));

    if ($returnUserId !== '') {
      $params = ['user_id' => $returnUserId];
      if ($returnReportPeriod !== '') {
        $params['report_period'] = $returnReportPeriod;
      }
      if ($returnEndDate !== '') {
        $params['end_date'] = $returnEndDate;
      }
      $qs = http_build_query($params);
      redirect('Page/employeeAccomplishmentData' . ($qs ? ('?' . $qs) : ''));
      return;
    }

    if ($returnName !== null && $returnDate !== null) {
      redirect('Page/accomplishmentsPerEmployee?name=' . urlencode((string) $returnName) . '&date=' . urlencode((string) $returnDate));
      return;
    }

    redirect('Page/accomplishments');
  }

  public function updateAccomplishment()
  {
    if ($this->session->userdata('level') !== 'Admin') {
      echo 'Access Denied';
      return;
    }

    if ($this->input->method() === 'post') {
      $taskId = $this->input->post('task_id');
      $ptsId = $this->input->post('pts_id');
      $task = $this->_normalizeTaskLabel($this->input->post('task'));
      $project = $this->input->post('project');
      $priority = $this->input->post('priority');
      $note = $this->input->post('note');
      $dateOnly = $this->input->post('accomplishedDate');
      $statusInput = $this->input->post('task_status');
      if ($statusInput === null) {
        $statusInput = $this->input->post('taskStat');
      }
      $reopenTask = $this->input->post('reopen_task') ? true : false;

      if ((empty($taskId) || (int) $taskId === 0) && !empty($ptsId)) {
        $taskLookup = $this->db->select('taskID')
          ->from('projects_task_stat')
          ->where('ptsID', $ptsId)
          ->limit(1)
          ->get()
          ->row();

        if ($taskLookup && !empty($taskLookup->taskID)) {
          $taskId = $taskLookup->taskID;
        }
      }

      if ((empty($taskId) || (int) $taskId === 0) && (empty($ptsId) || (int) $ptsId === 0)) {
        $this->session->set_flashdata('danger', 'No accomplishment selected for update.');
        $returnName = $this->input->post('return_name');
        $returnDate = $this->input->post('return_date');

        $returnUserId = trim((string) $this->input->post('return_user_id'));
        $returnReportPeriod = trim((string) $this->input->post('return_report_period'));
        $returnEndDate = trim((string) $this->input->post('return_end_date'));

        if ($returnUserId !== '') {
          $params = ['user_id' => $returnUserId];
          if ($returnReportPeriod !== '') {
            $params['report_period'] = $returnReportPeriod;
          }
          if ($returnEndDate !== '') {
            $params['end_date'] = $returnEndDate;
          }
          $qs = http_build_query($params);
          redirect('Page/employeeAccomplishmentData' . ($qs ? ('?' . $qs) : ''));
          return;
        }

        if ($returnName !== null && $returnDate !== null) {
          redirect('Page/accomplishmentsPerEmployee?name=' . urlencode((string) $returnName) . '&date=' . urlencode((string) $returnDate));
          return;
        }

        redirect('Page/accomplishments');
        return;
      }

      if ($dateOnly === '') {
        $dateOnly = date('Y-m-d');
      }

      if ($priority === '' || $priority === null) {
        $priority = '2';
      }

      date_default_timezone_set('Asia/Manila');
      $datePosted = date('Y-m-d H:i:s', strtotime((string) $dateOnly));

      if ($statusInput !== null && $statusInput !== '') {
        $taskStatus = ((string) $statusInput === '1') ? '1' : '0';
        $reopenTask = ($taskStatus === '1');
      } else {
        $taskStatus = $reopenTask ? '1' : '0';
      }

      $this->db->where('taskID', $taskId);
      $this->db->update('projects_task', [
        'task' => $task,
        'projectID' => $project,
        'priority' => $priority,
        'taskStat' => $taskStatus
      ]);

      // Sync calendar event completion status
      if ($this->db->table_exists('calendar_events')) {
        $is_completed = ($taskStatus === '0') ? 1 : 0;
        $this->db->where('task_id', $taskId);
        $this->db->update('calendar_events', ['is_completed' => $is_completed]);
      }

      if (!empty($ptsId)) {
        $this->db->where('ptsID', $ptsId);
        $this->db->update('projects_task_stat', [
          'note' => $note,
          'datePosted' => $datePosted,
          'taskStat' => '0'
        ]);
      }

      if ($reopenTask && !empty($taskId)) {
        $username = (string) $this->session->userdata('username');
        $reopenDate = date('Y-m-d H:i:s');
        $reopenNote = $username !== '' ? ('Reopened by ' . $username) : 'Reopened by admin';

        $this->db->insert('projects_task_stat', [
          'taskID' => $taskId,
          'note' => $reopenNote,
          'datePosted' => $reopenDate,
          'postedBy' => $username,
          'taskStat' => '1'
        ]);
      }

      if ($reopenTask) {
        $this->session->set_flashdata('success', 'Accomplishment updated and task reopened.');
      } else {
        $this->session->set_flashdata('success', 'Accomplishment updated successfully.');
      }
    }

    $returnName = $this->input->post('return_name');
    $returnDate = $this->input->post('return_date');

    $returnUserId = trim((string) $this->input->post('return_user_id'));
    $returnReportPeriod = trim((string) $this->input->post('return_report_period'));
    $returnEndDate = trim((string) $this->input->post('return_end_date'));

    if ($returnUserId !== '') {
      $params = ['user_id' => $returnUserId];
      if ($returnReportPeriod !== '') {
        $params['report_period'] = $returnReportPeriod;
      }
      if ($returnEndDate !== '') {
        $params['end_date'] = $returnEndDate;
      }
      $qs = http_build_query($params);
      redirect('Page/employeeAccomplishmentData' . ($qs ? ('?' . $qs) : ''));
      return;
    }

    if ($returnName !== null && $returnDate !== null) {
      redirect('Page/accomplishmentsPerEmployee?name=' . urlencode((string) $returnName) . '&date=' . urlencode((string) $returnDate));
      return;
    }

    redirect('Page/accomplishments');
  }

  public function deleteAccomplishment()
  {
    if ($this->session->userdata('level') !== 'Admin') {
      echo 'Access Denied';
      return;
    }

    $ptsId = $this->input->get('ptsId');
    $taskId = $this->input->get('taskId');

    if ($ptsId) {
      $this->db->where('ptsID', $ptsId);
      $this->db->delete('projects_task_stat');
    }

    if ($taskId) {
      $latest = $this->db->select('taskStat')
        ->from('projects_task_stat')
        ->where('taskID', $taskId)
        ->order_by('datePosted', 'desc')
        ->order_by('ptsID', 'desc')
        ->limit(1)
        ->get()
        ->row();

      $nextStatus = $latest ? $latest->taskStat : '1';

      $this->db->where('taskID', $taskId);
      $this->db->update('projects_task', ['taskStat' => $nextStatus]);
    }

    $this->session->set_flashdata('success', 'Accomplishment deleted successfully.');

    $returnName = $this->input->get('name');
    $returnDate = $this->input->get('date');

    $returnUserId = trim((string) $this->input->get('return_user_id'));
    $returnReportPeriod = trim((string) $this->input->get('return_report_period'));
    $returnEndDate = trim((string) $this->input->get('return_end_date'));

    if ($returnUserId !== '') {
      $params = ['user_id' => $returnUserId];
      if ($returnReportPeriod !== '') {
        $params['report_period'] = $returnReportPeriod;
      }
      if ($returnEndDate !== '') {
        $params['end_date'] = $returnEndDate;
      }
      $qs = http_build_query($params);
      redirect('Page/employeeAccomplishmentData' . ($qs ? ('?' . $qs) : ''));
      return;
    }

    if ($returnName !== null && $returnDate !== null) {
      redirect('Page/accomplishmentsPerEmployee?name=' . urlencode((string) $returnName) . '&date=' . urlencode((string) $returnDate));
      return;
    }

    redirect('Page/accomplishments');
  }

  public function bday_today()
  {
    $level = strtolower((string) $this->session->userdata('level'));
    if ($level === 'student') {
      show_error('Access denied', 403);
      return;
    }

    $settingsID = $this->session->userdata('settingsID');
    $data['celebrants'] = [];
    if (!empty($settingsID)) {
      $data['celebrants'] = $this->CashModel->getBirthdayCelebrantsToday($settingsID);
    }

    $this->load->view('bday_today', $data);
  }

  public function bday_month()
  {
    $level = strtolower((string) $this->session->userdata('level'));
    if ($level === 'student') {
      show_error('Access denied', 403);
      return;
    }

    $settingsID = $this->session->userdata('settingsID');
    $data['celebrants'] = [];
    if (!empty($settingsID)) {
      $data['celebrants'] = $this->CashModel->getBirthdayCelebrantsMonth($settingsID);
    }

    $this->load->view('bday_month', $data);
  }

  private function _bulkTaskColumnIndex($headerMap, array $aliases)
  {
    foreach ($aliases as $alias) {
      $normalizedAlias = strtolower(trim((string) $alias));
      if (array_key_exists($normalizedAlias, $headerMap)) {
        return $headerMap[$normalizedAlias];
      }
    }

    return false;
  }

  private function _bulkTaskNormalizeDateValue($rawValue, $defaultDate = '')
  {
    $rawValue = trim((string) $rawValue);
    if ($rawValue === '') {
      return $defaultDate;
    }

    if (is_numeric($rawValue)) {
      $excelSerial = (float) $rawValue;
      if ($excelSerial > 0) {
        $timestamp = ($excelSerial - 25569) * 86400;
        if ($timestamp > 0) {
          return gmdate('Y-m-d', (int) round($timestamp));
        }
      }
    }

    $formats = array('Y-m-d', 'm/d/Y', 'd/m/Y', 'm-d-Y', 'd-m-Y');
    foreach ($formats as $format) {
      $dateObj = DateTime::createFromFormat($format, $rawValue);
      if ($dateObj && $dateObj->format($format) === $rawValue) {
        return $dateObj->format('Y-m-d');
      }
    }

    $timestamp = strtotime($rawValue);
    if ($timestamp !== false) {
      return date('Y-m-d', $timestamp);
    }

    return '';
  }

  private function _bulkTaskNormalizePriority($rawValue)
  {
    $rawValue = strtolower(trim((string) $rawValue));
    if ($rawValue === '1' || $rawValue === 'high') {
      return '1';
    }
    if ($rawValue === '3' || $rawValue === 'low') {
      return '3';
    }
    return '2';
  }

  private function _bulkTaskResolveProjectId($settingsID, $projectIdRaw, $projectNameRaw = '')
  {
    $settingsID = (int) $settingsID;
    $projectIdRaw = trim((string) $projectIdRaw);
    $projectNameRaw = trim((string) $projectNameRaw);

    if ($projectIdRaw !== '' && ctype_digit($projectIdRaw)) {
      $projectRow = $this->db
        ->select('projectID')
        ->from('projects')
        ->where('settingsID', $settingsID)
        ->where('projectID', (int) $projectIdRaw)
        ->limit(1)
        ->get()
        ->row();

      if ($projectRow) {
        return (int) $projectRow->projectID;
      }
    }

    if ($projectNameRaw !== '') {
      $projectRow = $this->db
        ->select('projectID')
        ->from('projects')
        ->where('settingsID', $settingsID)
        ->group_start()
        ->where('projectDescription', $projectNameRaw)
        ->or_where('projectCategory', $projectNameRaw)
        ->group_end()
        ->limit(1)
        ->get()
        ->row();

      if ($projectRow) {
        return (int) $projectRow->projectID;
      }
    }

    return 0;
  }

  private function _bulkTaskReadCsvRows($filePath)
  {
    $handle = fopen($filePath, 'r');
    if (!$handle) {
      return array();
    }

    $rows = array();
    $bom = fread($handle, 3);
    if ($bom !== chr(0xEF) . chr(0xBB) . chr(0xBF)) {
      rewind($handle);
    }

    while (($row = fgetcsv($handle)) !== false) {
      $rows[] = $row;
    }

    fclose($handle);
    return $rows;
  }

  private function _bulkTaskExcelColumnToIndex($columnLetters)
  {
    $columnLetters = strtoupper(trim((string) $columnLetters));
    $index = 0;
    $length = strlen($columnLetters);

    for ($i = 0; $i < $length; $i++) {
      $index = ($index * 26) + (ord($columnLetters[$i]) - 64);
    }

    return max(0, $index - 1);
  }

  private function _bulkTaskExtractXlsxText($cellNode, array $sharedStrings)
  {
    $mainNs = 'http://schemas.openxmlformats.org/spreadsheetml/2006/main';
    $cellChildren = $cellNode->children($mainNs);
    $cellType = (string) $cellNode['t'];

    if ($cellType === 'inlineStr') {
      if (isset($cellChildren->is->t)) {
        return (string) $cellChildren->is->t;
      }
      if (isset($cellChildren->is->r)) {
        $text = '';
        foreach ($cellChildren->is->r as $run) {
          $text .= (string) $run->t;
        }
        return $text;
      }
      return '';
    }

    if (!isset($cellChildren->v)) {
      return '';
    }

    $value = (string) $cellChildren->v;
    if ($cellType === 's') {
      $sharedIndex = (int) $value;
      return isset($sharedStrings[$sharedIndex]) ? $sharedStrings[$sharedIndex] : '';
    }

    return $value;
  }

  private function _bulkTaskReadXlsxRows($filePath)
  {
    if (!class_exists('ZipArchive') || !function_exists('simplexml_load_string')) {
      return null;
    }

    $zip = new ZipArchive();
    if ($zip->open($filePath) !== true) {
      return null;
    }

    $sharedStrings = array();
    $sharedStringsXml = $zip->getFromName('xl/sharedStrings.xml');
    if ($sharedStringsXml !== false) {
      $sharedXml = @simplexml_load_string($sharedStringsXml);
      $mainNs = 'http://schemas.openxmlformats.org/spreadsheetml/2006/main';
      if ($sharedXml) {
        $sharedChildren = $sharedXml->children($mainNs);
      } else {
        $sharedChildren = null;
      }
      if ($sharedChildren && isset($sharedChildren->si)) {
        foreach ($sharedChildren->si as $stringItem) {
          if (isset($stringItem->t)) {
            $sharedStrings[] = (string) $stringItem->t;
            continue;
          }

          $assembled = '';
          if (isset($stringItem->r)) {
            foreach ($stringItem->r as $run) {
              $assembled .= (string) $run->t;
            }
          }
          $sharedStrings[] = $assembled;
        }
      }
    }

    $worksheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
    if ($worksheetXml === false) {
      $zip->close();
      return null;
    }

    $sheetXml = @simplexml_load_string($worksheetXml);
    $mainNs = 'http://schemas.openxmlformats.org/spreadsheetml/2006/main';
    $sheetChildren = $sheetXml ? $sheetXml->children($mainNs) : null;
    if (!$sheetChildren || !isset($sheetChildren->sheetData)) {
      $zip->close();
      return null;
    }

    $rows = array();
    foreach ($sheetChildren->sheetData->row as $rowNode) {
      $rowValues = array();
      foreach ($rowNode->c as $cellNode) {
        $cellReference = (string) $cellNode['r'];
        preg_match('/[A-Z]+/i', $cellReference, $matches);
        $columnLetters = isset($matches[0]) ? $matches[0] : '';
        $columnIndex = $this->_bulkTaskExcelColumnToIndex($columnLetters);
        $rowValues[$columnIndex] = $this->_bulkTaskExtractXlsxText($cellNode, $sharedStrings);
      }

      if (!empty($rowValues)) {
        ksort($rowValues);
        $maxIndex = max(array_keys($rowValues));
        $normalized = array_fill(0, $maxIndex + 1, '');
        foreach ($rowValues as $index => $value) {
          $normalized[$index] = $value;
        }
        $rows[] = $normalized;
      }
    }

    $zip->close();
    return $rows;
  }

  public function downloadTaskBulkTemplate()
  {
    $filename = 'task_bulk_upload_template.csv';

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Pragma: no-cache');
    header('Expires: 0');

    $output = fopen('php://output', 'w');
    fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

    fputcsv($output, array('Task', 'Project ID', 'Project Name', 'Reported Date', 'Due Date', 'Priority', 'Assigned User ID', 'Attachment Link'));
    fputcsv($output, array('Prepare onboarding checklist', '79', 'SRMS (College) for AMYA', '2026-04-17', '2026-04-19', 'High', '6', 'https://example.com/checklist'));
    fputcsv($output, array('Verify billing module updates', '79', 'SRMS (College) for AMYA', '2026-04-17', '2026-04-20', 'Medium', '8', ''));

    fclose($output);
    exit;
  }

  public function bulkUploadTasks()
  {
    if ($this->input->method() !== 'post') {
      redirect('Page/projectAddTask');
      return;
    }

    $settingsID = (int) ($this->session->userdata('settingsID') ?? 0);
    $isAdmin = $this->_is_admin_user();
    $currentUserId = (int) ($this->session->userdata('user_id') ?? 0);
    $username = trim((string) ($this->session->userdata('username') ?? 'system'));
    if ($username === '') {
      $username = 'system';
    }

    if (!isset($_FILES['task_file']) || $_FILES['task_file']['error'] !== UPLOAD_ERR_OK) {
      $this->session->set_flashdata('danger', 'No task file uploaded or upload error occurred.');
      redirect('Page/projectAddTask');
      return;
    }

    $file = $_FILES['task_file'];
    $fileTmpPath = $file['tmp_name'];
    $fileName = $file['name'];
    $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

    if (!in_array($fileExtension, array('csv', 'xlsx'), true)) {
      $this->session->set_flashdata('danger', 'Invalid file format. Please upload CSV or Excel (.xlsx) files only.');
      redirect('Page/projectAddTask');
      return;
    }

    if ((int) $file['size'] > 5 * 1024 * 1024) {
      $this->session->set_flashdata('danger', 'File size exceeds 5MB limit.');
      redirect('Page/projectAddTask');
      return;
    }

    $rows = ($fileExtension === 'csv')
      ? $this->_bulkTaskReadCsvRows($fileTmpPath)
      : $this->_bulkTaskReadXlsxRows($fileTmpPath);

    if ($rows === null && $fileExtension === 'xlsx') {
      $this->session->set_flashdata('danger', 'Unable to read the Excel file. Please upload CSV or Excel (.xlsx) generated from the provided template.');
      redirect('Page/projectAddTask');
      return;
    }

    if (empty($rows)) {
      $this->session->set_flashdata('danger', 'The uploaded task file is empty.');
      redirect('Page/projectAddTask');
      return;
    }

    $header = array_shift($rows);
    $headerMap = array();
    foreach ((array) $header as $index => $columnName) {
      $normalized = strtolower(trim((string) $columnName));
      if ($normalized !== '') {
        $headerMap[$normalized] = $index;
      }
    }

    $taskIndex = $this->_bulkTaskColumnIndex($headerMap, array('task', 'task name', 'task_name'));
    $projectIdIndex = $this->_bulkTaskColumnIndex($headerMap, array('project id', 'project_id', 'projectid'));
    $projectNameIndex = $this->_bulkTaskColumnIndex($headerMap, array('project name', 'project_name', 'project', 'project description', 'project_description'));
    $reportedDateIndex = $this->_bulkTaskColumnIndex($headerMap, array('reported date', 'reported_date', 'reported'));
    $dueDateIndex = $this->_bulkTaskColumnIndex($headerMap, array('due date', 'due_date', 'due'));
    $priorityIndex = $this->_bulkTaskColumnIndex($headerMap, array('priority'));
    $assignedUserIndex = $this->_bulkTaskColumnIndex($headerMap, array('assigned user id', 'assigned_user_id', 'assignedperson', 'assigned person', 'assigned'));
    $attachmentIndex = $this->_bulkTaskColumnIndex($headerMap, array('attachment link', 'attachment_link', 'attachment', 'url'));

    if ($taskIndex === false || ($projectIdIndex === false && $projectNameIndex === false)) {
      $this->session->set_flashdata('danger', 'Task upload must include Task and either Project ID or Project Name columns.');
      redirect('Page/projectAddTask');
      return;
    }

    $processed = 0;
    $failed = 0;
    $errors = array();
    $defaultReportedDate = date('Y-m-d');

    foreach ($rows as $rowOffset => $row) {
      $rowNumber = $rowOffset + 2;

      if (empty(array_filter((array) $row, function ($value) {
        return trim((string) $value) !== '';
      }))) {
        continue;
      }

      $taskName = isset($row[$taskIndex]) ? $this->_normalizeTaskLabel($row[$taskIndex]) : '';
      $projectIdRaw = ($projectIdIndex !== false && isset($row[$projectIdIndex])) ? trim((string) $row[$projectIdIndex]) : '';
      $projectNameRaw = ($projectNameIndex !== false && isset($row[$projectNameIndex])) ? trim((string) $row[$projectNameIndex]) : '';
      $reportedDate = ($reportedDateIndex !== false && isset($row[$reportedDateIndex]))
        ? $this->_bulkTaskNormalizeDateValue($row[$reportedDateIndex], $defaultReportedDate)
        : $defaultReportedDate;
      $dueDate = ($dueDateIndex !== false && isset($row[$dueDateIndex]))
        ? $this->_bulkTaskNormalizeDateValue($row[$dueDateIndex], $reportedDate)
        : $reportedDate;
      $priority = ($priorityIndex !== false && isset($row[$priorityIndex]))
        ? $this->_bulkTaskNormalizePriority($row[$priorityIndex])
        : '2';
      $attachmentLink = ($attachmentIndex !== false && isset($row[$attachmentIndex])) ? trim((string) $row[$attachmentIndex]) : null;
      $assignedUserId = $isAdmin
        ? (($assignedUserIndex !== false && isset($row[$assignedUserIndex]) && ctype_digit(trim((string) $row[$assignedUserIndex]))) ? (int) trim((string) $row[$assignedUserIndex]) : 0)
        : $currentUserId;

      if ($taskName === '') {
        $failed++;
        $errors[] = 'Row ' . $rowNumber . ': Task is required.';
        continue;
      }

      $projectId = $this->_bulkTaskResolveProjectId($settingsID, $projectIdRaw, $projectNameRaw);
      if ($projectId <= 0) {
        $failed++;
        $errors[] = 'Row ' . $rowNumber . ': Project could not be matched.';
        continue;
      }

      if ($reportedDate === '') {
        $failed++;
        $errors[] = 'Row ' . $rowNumber . ': Reported Date is invalid.';
        continue;
      }

      if ($dueDate === '') {
        $dueDate = $reportedDate;
      }

      if ($isAdmin && $assignedUserId <= 0) {
        $failed++;
        $errors[] = 'Row ' . $rowNumber . ': Assigned User ID is required for admin bulk upload.';
        continue;
      }

      if ($assignedUserId > 0) {
        $assignedUser = $this->db
          ->select('user_id')
          ->from('users')
          ->where('settingsID', $settingsID)
          ->where('user_id', $assignedUserId)
          ->limit(1)
          ->get()
          ->row();

        if (!$assignedUser) {
          $failed++;
          $errors[] = 'Row ' . $rowNumber . ': Assigned User ID was not found.';
          continue;
        }
      }

      $taskData = array(
        'taskID' => 0,
        'task' => $taskName,
        'reportedDate' => $reportedDate,
        'dueDate' => $dueDate,
        'projectID' => $projectId,
        'taskStat' => '1',
        'priority' => $priority,
        'settingsID' => $settingsID,
        'assignedPerson' => $assignedUserId,
        'attachment_link' => ($attachmentLink !== '' ? $attachmentLink : null),
        'added_by' => $username,
      );

      $inserted = $this->db->insert('projects_task', $taskData);
      if ($inserted && $this->db->affected_rows() > 0) {
        $processed++;
      } else {
        $failed++;
        $errors[] = 'Row ' . $rowNumber . ': Database insert failed.';
      }
    }

    if ($processed > 0) {
      $message = 'Successfully imported ' . $processed . ' task(s).';
      if ($failed > 0) {
        $message .= ' ' . $failed . ' record(s) failed.';
        $message .= ' ' . implode(' ', array_slice($errors, 0, 5));
      }
      $this->session->set_flashdata('success', $message);
    } else {
      $message = 'No tasks were imported.';
      if (!empty($errors)) {
        $message .= ' ' . implode(' ', array_slice($errors, 0, 5));
      }
      $this->session->set_flashdata('danger', $message);
    }

    redirect('Page/projectAddTask');
  }


	  public function projectAddTask()
	  {
	    $isAdmin = $this->_is_admin_user();
	    $settingsID = $this->session->userdata('settingsID');
	    date_default_timezone_set('Asia/Manila');
	    $today = date('Y-m-d');
	    $taskWindowDays = 7;

    // Get status filter from URL (open, closed, or all)
    $statusFilter = strtolower(trim((string) $this->input->get('status')));
    if (!in_array($statusFilter, ['open', 'closed', 'all'], true)) {
      $statusFilter = 'open'; // Default to open tasks
    }
    $taskScope = strtolower(trim((string) $this->input->get('scope')));
    if (!in_array($taskScope, ['forwarded'], true)) {
      $taskScope = '';
    }

    if ($isAdmin) {
      $taskData = $this->CashModel->taskList($settingsID);
      $result['openTaskCount'] = $this->CashModel->countOpenTasks($settingsID);
      $result['closedTaskCount'] = $this->CashModel->countClosedTasks($settingsID);
      $result['dueTodayTaskCount'] = $this->CashModel->countOpenTasksDueToday($settingsID, null, $today);
      $result['dueSoonTaskCount'] = $this->CashModel->countOpenTasksDueSoon($settingsID, $taskWindowDays, null, $today);
      $result['overdueTaskCount'] = $this->CashModel->countOpenTasksOverdue($settingsID, null, $today);
      $result['undatedTaskCount'] = $this->CashModel->countOpenTasksWithoutDueDate($settingsID);
      $result['taskDueQueue'] = $this->CashModel->openTaskDueQueue($settingsID, null, 6, $taskWindowDays, $today);
    } else {
      $user_id = $this->session->userdata('user_id');
      $username = trim((string) ($this->session->userdata('username') ?? 'system'));

      $taskData = $this->CashModel->taskListStaff($settingsID, $user_id);
      $result['openTaskCount'] = $this->CashModel->countOpenTasksStaff($settingsID, $user_id);
      $result['closedTaskCount'] = $this->CashModel->countClosedTasksStaff($settingsID, $user_id);
      $result['dueTodayTaskCount'] = $this->CashModel->countOpenTasksDueToday($settingsID, $user_id, $today);
      $result['dueSoonTaskCount'] = $this->CashModel->countOpenTasksDueSoon($settingsID, $taskWindowDays, $user_id, $today);
      $result['overdueTaskCount'] = $this->CashModel->countOpenTasksOverdue($settingsID, $user_id, $today);
      $result['undatedTaskCount'] = $this->CashModel->countOpenTasksWithoutDueDate($settingsID, $user_id);
      $result['taskDueQueue'] = $this->CashModel->openTaskDueQueue($settingsID, $user_id, 6, $taskWindowDays, $today);

      if ($taskScope === 'forwarded') {
        $pendingForwardedIds = $this->_staff_pending_forwarded_task_ids($settingsID, $user_id, $username);
        $taskData = array_values(array_filter((array) $taskData, function ($task) use ($pendingForwardedIds) {
          return in_array((int) ($task->taskID ?? 0), $pendingForwardedIds, true);
        }));
      }
    }

    // Filter tasks based on status (0 = Closed, 1 = Open)
    $filteredData = array_filter($taskData, function ($task) use ($statusFilter) {
      $taskStat = (string) ($task->taskStat ?? '1');
      if ($statusFilter === 'open') {
        return $taskStat === '1';
      } elseif ($statusFilter === 'closed') {
        return $taskStat === '0';
      }
      return true; // 'all' - show everything
    });
    // Reindex array to ensure consecutive keys (important for DataTables)
    $result['data'] = array_values($filteredData);

    $result['statusFilter'] = $statusFilter;
    $result['taskScope'] = $taskScope;
    $result['taskDueWindowDays'] = $taskWindowDays;

    // Load staff list from employee table (needed for Forward Task modal)
    $result['data2'] = $this->CashModel->employeeList($settingsID);

    $result['data1'] = $this->CashModel->getProjectName($settingsID);

    // Check if company is on Package 2 (Task Management Suite)
    $isPackage2 = false;
    if ($settingsID > 0 && $this->db->table_exists('company_features')) {
        $featureRows = $this->db
            ->select('feature_key')
            ->from('company_features')
            ->where('settingsID', $settingsID)
            ->where('is_enabled', 1)
            ->get()
            ->result();
        $enabledFeatures = array();
        foreach ($featureRows as $row) {
            $featureKey = trim((string) ($row->feature_key ?? ''));
            if ($featureKey !== '') {
                $enabledFeatures[] = $featureKey;
            }
        }
        $package2Features = array('tasks', 'notes', 'calendar');
        $isPackage2 = count($enabledFeatures) === count($package2Features) && 
                     count(array_intersect($enabledFeatures, $package2Features)) === count($package2Features);
    }
    $result['isPackage2'] = $isPackage2;
    $result['hasTimeInToday'] = true;
    if (!$isAdmin && !$isPackage2) {
      $username = trim((string) ($this->session->userdata('username') ?? ''));
      $result['hasTimeInToday'] = $this->CashModel->hasTimeInToday($settingsID, $username, $today);
    }

    $result['currentUserId'] = $this->session->userdata('user_id');
    $result['currentUser'] = $this->session->userdata('fName') . ' ' . $this->session->userdata('lName');

    $this->load->view('project_list_task', $result);


    if ($this->input->post('add_task')) {
      $username = $this->session->userdata('username');

      // Check if user has timed in today before allowing task creation (skip for Admin and Package 2)
      if (!$isPackage2) {
        date_default_timezone_set('Asia/Manila');
        $today = date('Y-m-d');
        $hasTimeIn = $this->CashModel->hasTimeInToday($settingsID, $username, $today);

        if (!$hasTimeIn && !$isAdmin) {
          $this->session->set_flashdata('danger', 'You need to attend first before you can add a task.');
          redirect('Page/projectAddTask');
          return;
        }
      }


      $task = $this->_normalizeTaskLabel($this->input->post('task'));
      $reportedDate = $this->_normalizeDateInput($this->input->post('reportedDate')) ?: date('Y-m-d');
      $dueDate = $this->_normalizeDateInput($this->input->post('dueDate')) ?: $reportedDate;
      $project = $this->input->post('project');
      $priority = $this->input->post('priority');
      $attachmentLink = trim((string) $this->input->post('attachment_link'));
      $attachmentLink = $attachmentLink !== '' ? $attachmentLink : null;
      $assignedTo = $isAdmin ? $this->input->post('assignedPerson') : $user_id;

      $pointsRaw = $this->input->post('points');
      $points = is_numeric($pointsRaw) ? (int) $pointsRaw : 1;
      if ($points < 1) {
        $points = 1;
      }

      $added_by = $this->session->userdata('username');

      $taskInsert = [
        'taskID' => 0,
        'task' => $task,
        'reportedDate' => $reportedDate,
        'dueDate' => $dueDate,
        'projectID' => $project,
        'taskStat' => '1',
        'priority' => $priority,
        'settingsID' => $settingsID,
        'assignedPerson' => $assignedTo,
        'attachment_link' => $attachmentLink,
        'added_by' => $added_by
      ];

      if ($this->db->field_exists('points', 'projects_task')) {
        $taskInsert['points'] = $points;
      }

      $this->db->insert('projects_task', $taskInsert);

      $taskId = (int) $this->db->insert_id();

      // Create calendar events for all assigned users
      if ($taskId > 0 && $this->db->table_exists('calendar_events')) {
        // Parse assignedPerson to get all assigned users (supports comma-separated list)
        $assignedUsers = array();
        if (strpos($assignedTo, ',') !== false) {
          $assignedUsers = array_map('trim', explode(',', $assignedTo));
        } else {
          $assignedUsers[] = $assignedTo;
        }

        // Get user IDs for assigned usernames
        $userIds = array();
        foreach ($assignedUsers as $assignedUser) {
          if (is_numeric($assignedUser)) {
            $userIds[] = (int) $assignedUser;
          } else {
            // Lookup user_id from username
            $user = $this->db->where('username', $assignedUser)
                              ->where('settingsID', $settingsID)
                              ->get('users')
                              ->row();
            if ($user && isset($user->user_id)) {
              $userIds[] = (int) $user->user_id;
            }
          }
        }

        // Create a calendar event for each assigned user
        foreach ($userIds as $userId) {
          if ($userId > 0) {
            $eventData = [
              'title' => $task,
              'description' => 'Task: ' . $task,
              'start_date' => $reportedDate,
              'end_date' => $dueDate,
              'all_day' => 1,
              'color' => $priority == '1' ? '#dc3545' : ($priority == '2' ? '#ffc107' : '#28a745'),
              'event_type' => 'task',
              'user_id' => $userId,
              'settingsID' => $settingsID,
              'status' => 'active',
              'task_id' => $taskId,
              'is_public' => 1,
              'is_completed' => 1,
              'created_at' => date('Y-m-d H:i:s'),
              'updated_at' => date('Y-m-d H:i:s')
            ];
            $this->db->insert('calendar_events', $eventData);
          }
        }
      }

      // Save checklist items if any
      $checklistItems = $this->input->post('checklist_items');
      if (!empty($checklistItems) && $taskId > 0) {
        foreach ($checklistItems as $item) {
          $item = trim((string) $item);
          if ($item !== '') {
            $this->db->insert('task_checklist', [
              'taskID' => $taskId,
              'itemDescription' => $item,
              'isCompleted' => 0,
              'settingsID' => $settingsID
            ]);
          }
        }
      }

      $this->session->set_flashdata('success', 'New task has been successfully added.');
      redirect('Page/projectAddTask');
    }
  }


  public function updateTask()
  {
    if ($this->input->method(true) === 'POST' && $this->input->post('taskID') !== null) {
      $username = $this->session->userdata('username');
      $settingsID = $this->session->userdata('settingsID');
      $isAdmin = $this->_is_admin_user();

      // Check if company is on Package 2
      $isPackage2 = false;
      if ($settingsID > 0 && $this->db->table_exists('company_features')) {
          $featureRows = $this->db
              ->select('feature_key')
              ->from('company_features')
              ->where('settingsID', $settingsID)
              ->where('is_enabled', 1)
              ->get()
              ->result();
          $enabledFeatures = array();
          foreach ($featureRows as $row) {
              $featureKey = trim((string) ($row->feature_key ?? ''));
              if ($featureKey !== '') {
                  $enabledFeatures[] = $featureKey;
              }
          }
          $package2Features = array('tasks', 'notes', 'calendar');
          $isPackage2 = count($enabledFeatures) === count($package2Features) && 
                       count(array_intersect($enabledFeatures, $package2Features)) === count($package2Features);
      }

      // Check if user has timed in today before allowing task update (skip for Admin and Package 2)
      if (!$isPackage2) {
        date_default_timezone_set('Asia/Manila');
        $today = date('Y-m-d');
        $hasTimeIn = $this->CashModel->hasTimeInToday($settingsID, $username, $today);

        if (!$hasTimeIn && !$isAdmin) {
          $this->session->set_flashdata('danger', 'You need to attend first before you can update task.');
          redirect('Page/projectAddTask');
          return;
        }
      }

      $taskID = (int) $this->input->post('taskID');
      $task = $this->_normalizeTaskLabel($this->input->post('task'));
      $priority = trim((string) $this->input->post('priority'));
      $project = trim((string) $this->input->post('project'));
      $reportedDate = $this->_normalizeDateInput($this->input->post('reportedDate')) ?: date('Y-m-d');
      $dueDate = $this->_normalizeDateInput($this->input->post('dueDate')) ?: $reportedDate;
      $attachmentLink = trim((string) $this->input->post('attachment_link'));
      $attachmentLink = $attachmentLink !== '' ? $attachmentLink : null;

      if ($taskID <= 0) {
        $this->session->set_flashdata('danger', 'Unable to update task because the task record was not identified.');
        redirect('Page/projectAddTask');
        return;
      }

      $data = [
        'task' => $task,
        'priority' => $priority,
        'projectID' => $project,
        'reportedDate' => $reportedDate,
        'dueDate' => $dueDate,
        'attachment_link' => $attachmentLink
      ];


      if ($isAdmin) {
        $data['assignedPerson'] = $this->input->post('assignedPerson');
        $data['client_comment'] = trim((string) $this->input->post('client_comment'));
      }

      $this->db->where('taskID', $taskID);
      $this->db->where('settingsID', $settingsID);
      $this->db->update('projects_task', $data);

      // Sync calendar events with assigned users
      if ($this->db->table_exists('calendar_events') && $isAdmin && isset($data['assignedPerson'])) {
        $assignedTo = $data['assignedPerson'];
        
        // Parse assignedPerson to get all assigned users (supports comma-separated list)
        $assignedUsers = array();
        if (strpos($assignedTo, ',') !== false) {
          $assignedUsers = array_map('trim', explode(',', $assignedTo));
        } else {
          $assignedUsers[] = $assignedTo;
        }

        // Get user IDs for assigned usernames
        $userIds = array();
        foreach ($assignedUsers as $assignedUser) {
          if (is_numeric($assignedUser)) {
            $userIds[] = (int) $assignedUser;
          } else {
            // Lookup user_id from username
            $user = $this->db->where('username', $assignedUser)
                              ->where('settingsID', $settingsID)
                              ->get('users')
                              ->row();
            if ($user && isset($user->user_id)) {
              $userIds[] = (int) $user->user_id;
            }
          }
        }

        // Delete existing calendar events for this task
        $this->db->where('task_id', $taskID);
        $this->db->where('settingsID', $settingsID);
        $this->db->delete('calendar_events');

        // Create a calendar event for each assigned user
        foreach ($userIds as $userId) {
          if ($userId > 0) {
            $eventData = [
              'title' => $task,
              'description' => 'Task: ' . $task,
              'start_date' => $reportedDate,
              'end_date' => $dueDate,
              'all_day' => 1,
              'color' => $priority == '1' ? '#dc3545' : ($priority == '2' ? '#ffc107' : '#28a745'),
              'event_type' => 'task',
              'user_id' => $userId,
              'settingsID' => $settingsID,
              'status' => 'active',
              'task_id' => $taskID,
              'is_public' => 1,
              'is_completed' => 1,
              'created_at' => date('Y-m-d H:i:s'),
              'updated_at' => date('Y-m-d H:i:s')
            ];
            $this->db->insert('calendar_events', $eventData);
          }
        }
      } elseif ($this->db->table_exists('calendar_events')) {
        // Update existing calendar events if not changing assignments
        $this->db->where('task_id', $taskID);
        $this->db->where('settingsID', $settingsID);
        $eventQuery = $this->db->get('calendar_events');
        
        if ($eventQuery->num_rows() > 0) {
          $eventData = [
            'title' => $task,
            'description' => 'Task: ' . $task,
            'start_date' => $reportedDate,
            'end_date' => $dueDate,
            'color' => $priority == '1' ? '#dc3545' : ($priority == '2' ? '#ffc107' : '#28a745'),
            'updated_at' => date('Y-m-d H:i:s')
          ];
          $this->db->where('task_id', $taskID);
          $this->db->where('settingsID', $settingsID);
          $this->db->update('calendar_events', $eventData);
        }
      }

      if (!$isAdmin) {
        $this->_mark_forwarded_task_action($taskID, (int) $settingsID, (int) ($this->session->userdata('user_id') ?? 0), $username, 'Forwarded task updated.');
      }

      $this->session->set_flashdata('success', 'Task updated successfully.');
    }

    redirect('Page/projectAddTask');
  }

  public function getTaskChecklist()
  {
    $taskId = (int) $this->input->post('task_id');
    $settingsID = $this->session->userdata('settingsID');

    // Debug logging
    error_log("getTaskChecklist - TaskID: " . $taskId . ", SettingsID: " . $settingsID);

    $this->db->select('checklistID, itemDescription, status, isCompleted');
    $this->db->from('task_checklist');
    $this->db->where('taskID', $taskId);
    $this->db->where('settingsID', $settingsID);
    $this->db->order_by('checklistID', 'ASC');

    $query = $this->db->get();
    $items = $query->result();

    error_log("getTaskChecklist - Found items: " . count($items));

    header('Content-Type: application/json');
    echo json_encode(['data' => $items]);
  }

  public function saveTaskChecklist()
  {
    $taskId = (int) $this->input->post('task_id');
    $checklistItems = $this->input->post('checklist_items');
    $settingsID = $this->session->userdata('settingsID');
    $username = $this->session->userdata('username');

    // Debug logging
    error_log("saveTaskChecklist - TaskID: " . $taskId . ", SettingsID: " . $settingsID);
    error_log("saveTaskChecklist - Items: " . print_r($checklistItems, true));

    if ($taskId <= 0 || empty($checklistItems)) {
      header('Content-Type: application/json');
      echo json_encode(['success' => false, 'message' => 'Invalid data', 'debug' => ['taskId' => $taskId, 'items_count' => count($checklistItems ?? [])]]);
      return;
    }

    // Delete existing checklist items for this task
    $this->db->where('taskID', $taskId);
    $this->db->where('settingsID', $settingsID);
    $this->db->delete('task_checklist');

    // Insert new checklist items
    foreach ($checklistItems as $item) {
      $itemDescription = trim((string) ($item['itemDescription'] ?? ''));
      $status = trim((string) ($item['status'] ?? 'Pending'));
      $isCompleted = (int) ($item['isCompleted'] ?? 0);

      if ($itemDescription !== '') {
        $data = [
          'taskID' => $taskId,
          'itemDescription' => $itemDescription,
          'status' => $status,
          'isCompleted' => $isCompleted,
          'settingsID' => $settingsID
        ];

        if ($isCompleted) {
          $data['completedAt'] = date('Y-m-d H:i:s');
          $data['completedBy'] = $username;
        }

        $this->db->insert('task_checklist', $data);
      }
    }

    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'message' => 'Checklist saved successfully']);
  }

  public function deleteTask($taskID)
  {
    $settingsID = $this->session->userdata('settingsID');
    $openUserId = (int) $this->input->get('open_user_id');
    $currentUsername = trim((string) $this->session->userdata('username'));
    $isAdmin = $this->_is_admin_user();

    // Fetch the task to check who created it
    $task = $this->db->where('taskID', $taskID)
      ->where('settingsID', $settingsID)
      ->get('projects_task')
      ->row();

    if (!$task) {
      $this->session->set_flashdata('danger', 'Task not found.');
      redirect('Page/projectAddTask');
      return;
    }

    $taskCreator = trim((string) ($task->added_by ?? ''));

    // Allow if admin OR if the current user is the one who created the task
    $canDelete = $isAdmin || ($taskCreator !== '' && $taskCreator === $currentUsername);

    if (!$canDelete) {
      $this->session->set_flashdata('danger', 'You can only delete tasks that you created.');
      redirect('Page/projectAddTask');
      return;
    }

    $this->db->where('taskID', $taskID);
    $this->db->where('settingsID', $settingsID);
    $this->db->delete('projects_task');

    $this->session->set_flashdata('success', 'Task deleted successfully.');
    if ($openUserId > 0) {
      redirect('Page/employeeTask?open_user_id=' . $openUserId);
      return;
    }

    redirect('Page/employeeTask');
  }


  public function addTaskNote()
  {
    $taskID = $this->input->post('dataid');
    $note = $this->input->post('note');
    $taskStat = $this->input->post('taskStat');
    $username = $this->session->userdata('username');
    $settingsID = $this->session->userdata('settingsID');
    $user_id = $this->session->userdata('user_id');

    $redirectTarget = isset($_SERVER['HTTP_REFERER']) && $_SERVER['HTTP_REFERER'] !== ''
      ? $_SERVER['HTTP_REFERER']
      : 'Page/projectAddTask';

    // Check if company is on Package 2
    $isPackage2 = false;
    if ($settingsID > 0 && $this->db->table_exists('company_features')) {
        $featureRows = $this->db
            ->select('feature_key')
            ->from('company_features')
            ->where('settingsID', $settingsID)
            ->where('is_enabled', 1)
            ->get()
            ->result();
        $enabledFeatures = array();
        foreach ($featureRows as $row) {
            $featureKey = trim((string) ($row->feature_key ?? ''));
            if ($featureKey !== '') {
                $enabledFeatures[] = $featureKey;
            }
        }
        $package2Features = array('tasks', 'notes', 'calendar');
        $isPackage2 = count($enabledFeatures) === count($package2Features) && 
                     count(array_intersect($enabledFeatures, $package2Features)) === count($package2Features);
    }

    $isAdmin = $this->_is_admin_user();

    // Check if user has timed in today before allowing task status update (skip for Admin and Package 2)
    if (!$isPackage2) {
      date_default_timezone_set('Asia/Manila');
      $today = date('Y-m-d');
      $hasTimeIn = $this->CashModel->hasTimeInToday($settingsID, $username, $today);

      if (!$hasTimeIn && !$isAdmin) {
        $this->session->set_flashdata('danger', 'You need to time in first before you can update task status.');
        redirect($redirectTarget);
        return;
      }
    }

    date_default_timezone_set('Asia/Manila');
    $date = date('Y-m-d H:i:s');

    $closeStatRow = [
      'taskID' => $taskID,
      'note' => $note,
      'datePosted' => $date,
      'postedBy' => $username,
      'taskStat' => $taskStat
    ];

    if (
      (string) $taskStat === '0'
      && $this->db->field_exists('points', 'projects_task_stat')
      && $this->db->field_exists('points', 'projects_task')
    ) {
      $taskPointsRow = $this->db
        ->select('points')
        ->from('projects_task')
        ->where('taskID', $taskID)
        ->limit(1)
        ->get()
        ->row();
      if ($taskPointsRow && isset($taskPointsRow->points) && is_numeric($taskPointsRow->points)) {
        $closeStatRow['points'] = (int) $taskPointsRow->points;
      }
    }

    $this->db->insert('projects_task_stat', $closeStatRow);


    $this->db->where('taskID', $taskID);
    $this->db->update('projects_task', [
      'taskStat' => $taskStat,
      'completed_by' => ($taskStat === '0' ? $user_id : null) // Track who actually completed the task
    ]);

    // Sync calendar event completion status
    if ($this->db->table_exists('calendar_events')) {
      $is_completed = ($taskStat === '0') ? 0 : 1;
      $this->db->where('task_id', $taskID);
      $this->db->update('calendar_events', ['is_completed' => $is_completed]);
    }

    $this->_sync_support_issue_from_task_status($taskID, $taskStat, $note, $settingsID, $user_id, $username, $date);

    // Handle race condition for forwarded tasks
    // Whoever completes first gets the points - close the other task
    if ((string) $taskStat === '0') {
      // Check if this is a forwarded task (has forwarded_from)
      $forwardedTask = $this->db
        ->select('forwarded_from, assignedPerson')
        ->from('projects_task')
        ->where('taskID', $taskID)
        ->limit(1)
        ->get()
        ->row();

      if ($forwardedTask && !empty($forwardedTask->forwarded_from)) {
        // This is a forwarded task being completed - close the original
        $originalTask = $this->db
          ->select('taskID, taskStat, assignedPerson')
          ->from('projects_task')
          ->where('taskID', $forwardedTask->forwarded_from)
          ->limit(1)
          ->get()
          ->row();

        if ($originalTask && $originalTask->taskStat == '1') {
          // Original is still open - close it (forwarded person wins)
          $this->db->where('taskID', $originalTask->taskID);
          $this->db->update('projects_task', [
            'taskStat' => '0',
            'completed_by' => $user_id, // Track who completed (the forwarded person)
            'completed_by_forward' => '1'
          ]);

          // Add note to original task
          $this->db->insert('projects_task_stat', [
            'taskID' => $originalTask->taskID,
            'note' => 'Task completed by forwarded assignee (race condition). Points go to forwarded person.',
            'datePosted' => $date,
            'postedBy' => $username,
            'taskStat' => '0'
          ]);
        }
      }

      // Count completed checklist items for accomplishments
      $completedChecklistCount = $this->db
        ->where('taskID', $taskID)
        ->where('isCompleted', 1)
        ->count_all_results('task_checklist');

      // Update points based on checklist completion
      if ($completedChecklistCount > 0) {
        // Add multiple entries for each completed checklist item
        for ($i = 0; $i < $completedChecklistCount; $i++) {
          $this->db->insert('projects_task_stat', [
            'taskID' => $taskID,
            'note' => 'Checklist item completed #' . ($i + 1),
            'points' => 1,
            'datePosted' => $date,
            'postedBy' => $username,
            'taskStat' => '0'
          ]);
        }
      }

      // Check if this is the original task that has forwarded copies
      $forwardedCopies = $this->db
        ->select('taskID, taskStat, assignedPerson')
        ->from('projects_task')
        ->where('forwarded_from', $taskID)
        ->where('taskStat', '1') // Only get open ones
        ->get()
        ->result();

      if (!empty($forwardedCopies)) {
        // Original completed first - close all forwarded copies
        foreach ($forwardedCopies as $copy) {
          $this->db->where('taskID', $copy->taskID);
          $this->db->update('projects_task', [
            'taskStat' => '0',
            'completed_by' => $user_id, // Track who completed (the original person)
            'completed_by_forward' => '1'
          ]);

          // Add note to forwarded task
          $this->db->insert('projects_task_stat', [
            'taskID' => $copy->taskID,
            'note' => 'Task closed because original assignee completed it first. Points go to original assignee.',
            'datePosted' => $date,
            'postedBy' => $username,
            'taskStat' => '0'
          ]);
        }
      }
    }

    $this->load->model('Notification_model', 'notifications');

    if ((string) $taskStat === '0') {
      $taskDetails = $this->db
        ->select('t.task, t.assignedPerson, t.projectID, p.projectDescription, u.fName, u.lName')
        ->from('projects_task t')
        ->join('projects p', 'p.projectID = t.projectID', 'left')
        ->join('users u', 'u.user_id = t.assignedPerson', 'left')
        ->where('t.taskID', $taskID)
        ->limit(1)
        ->get()
        ->row();

      if ($taskDetails) {
        $settingsID = (string) $this->session->userdata('settingsID');
        $firstName = isset($taskDetails->fName) ? (string) $taskDetails->fName : '';
        $lastName = isset($taskDetails->lName) ? (string) $taskDetails->lName : '';
        $fullName = trim($firstName . ' ' . $lastName);

        $taskLabel = isset($taskDetails->task) && $taskDetails->task !== '' ? $taskDetails->task : '#' . $taskID;
        $title = 'Task accomplished: ' . $taskLabel;

        $noteText = trim((string) $note);
        $messageParts = [];

        $projectDescription = isset($taskDetails->projectDescription) ? (string) $taskDetails->projectDescription : '';
        if ($projectDescription !== '') {
          $messageParts[] = 'Project: ' . $projectDescription;
        }

        if ($noteText !== '') {
          $messageParts[] = 'Notes: ' . $noteText;
        }

        $message = implode(' - ', $messageParts);
        if ($message === '') {
          $message = 'Marked as completed by ' . ($fullName !== '' ? $fullName : 'team member');
        }

        // For notification, use the current user (who actually completed) not just assignedPerson
        // This ensures forwarded tasks give points to the person who actually completed it
        $completedBy = $user_id; // The person who just marked it complete
        $userId = is_numeric($completedBy) ? (int) $completedBy : null;

        $this->notifications->create_accomplishment_notification([
          'settings_id' => $settingsID,
          'task_id' => (int) $taskID,
          'user_id' => $userId,
          'title' => $title,
          'message' => $message,
          'link' => base_url('Page/taskStat?id=' . $taskID),
          'created_at' => $date,
        ]);
      }
    } else {
      $this->notifications->resolve_task_notifications((int) $taskID);
    }


    $this->session->set_flashdata('success', 'Task updated successfully.');

    redirect($redirectTarget);
  }
  public function dayAccomplishments()
  {
    if ($this->session->userdata('level') !== 'Admin') {
      echo 'Access Denied';
      return;
    }

    $settingsID = $this->session->userdata('settingsID');
    $dateParam = trim((string) $this->input->get('date'));
    $date = (preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateParam)) ? $dateParam : date('Y-m-d');

    $selectedLabel = date('F j, Y', strtotime($date));
    $isToday = ($date === date('Y-m-d'));

    $result = [];
    $result['selectedDate'] = $date;                 // keep your existing key
    $result['selectedLabel'] = $selectedLabel;        // ✅ add
    $result['isToday'] = $isToday;              // ✅ add
    $result['pageTitle'] = $isToday ? "Today's Accomplishments" : ("Accomplishments (" . $selectedLabel . ")"); // ✅ add

    $result['accomplishments'] = $this->CashModel->accomplishmentsByDateAll($settingsID, $date);

    $this->load->view('accomplishments_today', $result);
  }


  public function saveTaskComment()
  {
    if ($this->session->userdata('level') !== 'Admin') {
      echo 'Access Denied';
      return;
    }

    if ($this->input->method() !== 'post') {
      redirect('Page/projectAddTask');
      return;
    }

    $taskId = $this->input->post('task_id');
    $ptsId = $this->input->post('pts_id');
    $note = trim((string) $this->input->post('note'));

    if ($taskId === null || $taskId === '') {
      $this->session->set_flashdata('danger', 'No task selected for comment update.');
      redirect('Page/projectAddTask');
      return;
    }

    if ($note === '' && ($ptsId === null || $ptsId === '')) {
      $this->session->set_flashdata('danger', 'Comment cannot be empty.');
      redirect('Page/projectAddTask');
      return;
    }

    date_default_timezone_set('Asia/Manila');
    $date = date('Y-m-d H:i:s');
    $username = (string) $this->session->userdata('username');

    if ($ptsId !== null && $ptsId !== '') {
      $exists = $this->db
        ->select('ptsID')
        ->from('projects_task_stat')
        ->where('ptsID', $ptsId)
        ->where('taskID', $taskId)
        ->limit(1)
        ->get()
        ->row();

      if (!$exists) {
        $this->session->set_flashdata('danger', 'Comment update failed. Please try again.');
      } else {
        $this->db->where('ptsID', $ptsId);
        $this->db->update('projects_task_stat', ['note' => $note]);
        $this->db->where('taskID', $taskId);
        $this->db->update('projects_task', ['client_comment' => $note]);
        $this->session->set_flashdata('success', 'Task comment updated.');
      }
    } else {
      $taskRow = $this->db
        ->select('taskStat')
        ->from('projects_task')
        ->where('taskID', $taskId)
        ->limit(1)
        ->get()
        ->row();

      if (!$taskRow) {
        $this->session->set_flashdata('danger', 'Task not found for comment.');
      } else {
        $this->db->insert('projects_task_stat', [
          'taskID' => $taskId,
          'note' => $note,
          'datePosted' => $date,
          'postedBy' => $username,
          'taskStat' => $taskRow->taskStat
        ]);
        $this->db->where('taskID', $taskId);
        $this->db->update('projects_task', ['client_comment' => $note]);
        $this->session->set_flashdata('success', 'Task comment saved.');
      }
    }

    $redirectUrl = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'Page/projectAddTask';
    redirect($redirectUrl);
  }


  function taskStat()
  {
    $taskID = $this->input->get('id');
    $result['data'] = $this->CashModel->taskStat($taskID);
    $this->load->view('taskstat_list_stat', $result);
  }

  public function forwardTask()
  {
    $taskID = $this->input->post('taskID');

    // Proceed if we have a taskID from POST
    if (empty($taskID)) {
      $this->session->set_flashdata('danger', 'No task selected for forwarding.');
      redirect('Page/projectAddTask');
      return;
    }
    $forwardTo = (int) $this->input->post('forwardTo');
    $forwardNote = $this->input->post('forwardNote');
    $settingsID = $this->session->userdata('settingsID');
    $username = $this->session->userdata('username');
    $user_id = $this->session->userdata('user_id');

    // Validate required fields
    if (empty($taskID) || $forwardTo <= 0) {
      $this->session->set_flashdata('danger', 'Please select an employee to forward the task to.');
      redirect('Page/projectAddTask');
      return;
    }

    // Check if company is on Package 2
    $isPackage2 = false;
    if ($settingsID > 0 && $this->db->table_exists('company_features')) {
        $featureRows = $this->db
            ->select('feature_key')
            ->from('company_features')
            ->where('settingsID', $settingsID)
            ->where('is_enabled', 1)
            ->get()
            ->result();
        $enabledFeatures = array();
        foreach ($featureRows as $row) {
            $featureKey = trim((string) ($row->feature_key ?? ''));
            if ($featureKey !== '') {
                $enabledFeatures[] = $featureKey;
            }
        }
        $package2Features = array('tasks', 'notes', 'calendar');
        $isPackage2 = count($enabledFeatures) === count($package2Features) && 
                     count(array_intersect($enabledFeatures, $package2Features)) === count($package2Features);
    }

    // Check if user has timed in today (skip for Package 2)
    if (!$isPackage2) {
      date_default_timezone_set('Asia/Manila');
      $today = date('Y-m-d');
      $hasTimeIn = $this->CashModel->hasTimeInToday($settingsID, $username, $today);

      if (!$hasTimeIn) {
        $this->session->set_flashdata('danger', 'You need to attend first before you can forward a task.');
        redirect('Page/projectAddTask');
        return;
      }
    }

    // Get original task details
    $originalTask = $this->db
      ->select('*')
      ->from('projects_task')
      ->where('taskID', $taskID)
      ->limit(1)
      ->get()
      ->row();

    if (!$originalTask) {
      $this->session->set_flashdata('danger', 'Task not found.');
      redirect('Page/projectAddTask');
      return;
    }

    // Get original assignee details
    $originalAssignee = $this->db
      ->select('fName, lName, user_id')
      ->from('users')
      ->where('user_id', $originalTask->assignedPerson)
      ->limit(1)
      ->get()
      ->row();

    // Get new assignee details
    $newAssignee = $this->db
      ->select('fName, lName, user_id, username')
      ->from('users')
      ->where('user_id', $forwardTo)
      ->limit(1)
      ->get()
      ->row();

    if (!$newAssignee) {
      $this->session->set_flashdata('danger', 'Selected employee not found.');
      redirect('Page/projectAddTask');
      return;
    }

    // Check if database columns exist
    $requiredColumns = ['forwarded_from', 'forwarded_to', 'forwarded_by', 'forwarded_note', 'forwarded_date', 'completed_by_forward'];
    $existingColumns = $this->db->list_fields('projects_task');
    $missingColumns = array_diff($requiredColumns, $existingColumns);

    if (!empty($missingColumns)) {
      $this->session->set_flashdata('danger', 'Database error: Missing columns - ' . implode(', ', $missingColumns) . '. Please run the migration SQL file.');
      redirect('Page/projectAddTask');
      return;
    }

    date_default_timezone_set('Asia/Manila');
    $date = date('Y-m-d H:i:s');

    try {
      // Create forwarded task for the new assignee
      $this->db->insert('projects_task', [
        'taskID' => 0,
        'task' => $this->_normalizeTaskLabel($originalTask->task . ' [Forwarded from: ' . ($originalAssignee ? ($originalAssignee->fName . ' ' . $originalAssignee->lName) : 'Unknown') . ']'),
        'reportedDate' => $originalTask->reportedDate,
        'dueDate' => isset($originalTask->dueDate) && trim((string) $originalTask->dueDate) !== '' ? $originalTask->dueDate : null,
        'projectID' => $originalTask->projectID,
        'taskStat' => '1',
        'priority' => $originalTask->priority,
        'settingsID' => $settingsID,
        'assignedPerson' => $forwardTo,
        'attachment_link' => $originalTask->attachment_link,
        'added_by' => $username,
        'forwarded_from' => $originalTask->taskID,
        'forwarded_to' => $forwardTo,
        'forwarded_by' => $user_id,
        'forwarded_note' => $forwardNote,
        'forwarded_date' => $date
      ]);

      $forwardedTaskID = $this->db->insert_id();

      if (!$forwardedTaskID) {
        throw new Exception('Failed to create forwarded task.');
      }

      // Add note to forwarded task status history
      $this->db->insert('projects_task_stat', [
        'taskID' => $forwardedTaskID,
        'note' => 'Task forwarded from ' . ($originalAssignee ? ($originalAssignee->fName . ' ' . $originalAssignee->lName) : 'Unknown') . '. Note: ' . $forwardNote,
        'datePosted' => $date,
        'postedBy' => $username,
        'taskStat' => '1'
      ]);

      // Keep original task open - just add note that it was forwarded
      // Do NOT mark as forwarded (status 2) - allow race condition where whoever completes first wins
      $this->db->insert('projects_task_stat', [
        'taskID' => $taskID,
        'note' => 'Task forwarded to ' . ($newAssignee->fName . ' ' . $newAssignee->lName) . '. Both can work on it - whoever completes first gets the points. Note: ' . $forwardNote,
        'datePosted' => $date,
        'postedBy' => $username,
        'taskStat' => '1'
      ]);

      $this->session->set_flashdata('success', 'Task forwarded successfully to ' . $newAssignee->fName . ' ' . $newAssignee->lName . '. Both of you can work on it - whoever completes first gets the points!');
    } catch (Exception $e) {
      $this->session->set_flashdata('danger', 'Error forwarding task: ' . $e->getMessage());
    }

    redirect('Page/projectAddTask');
  }

  public function taskPerProject()
  {
    $settingsID = $this->session->userdata('settingsID');
    $user_id = $this->session->userdata('user_id');
    $projectID = $this->input->get('projectID');

    $statusParam = $this->input->get('status'); // "closed" or null
    $taskStatus = ($statusParam === 'closed') ? 0 : 1; // default open

    $result['data'] = $this->CashModel->taskPerProject($settingsID, $user_id, $projectID, $taskStatus);
    $result['projectID'] = $projectID;
    $result['status'] = $taskStatus;
    $result['pointsEnabled'] = $this->db->field_exists('points', 'projects_task_stat');

    $this->load->view('tasks_list_project', $result);
  }

  public function bulkCloseProjectTasks()
  {
    $settingsID = $this->session->userdata('settingsID');
    $username = $this->session->userdata('username');
    $user_id = $this->session->userdata('user_id');
    $projectID = $this->input->post('projectID');

    $redirectBack = isset($_SERVER['HTTP_REFERER']) && $_SERVER['HTTP_REFERER'] !== ''
      ? $_SERVER['HTTP_REFERER']
      : 'Page/projectList';
    $pointsEnabled = $this->db->field_exists('points', 'projects_task_stat');
    $awardPoints = !$pointsEnabled || ((string) $this->input->post('bulkCloseAwardPoints') !== '0');

    if (empty($projectID)) {
      $this->session->set_flashdata('danger', 'Missing project reference for bulk close.');
      redirect($redirectBack);
      return;
    }

    // Check if company is on Package 2
    $isPackage2 = false;
    if ($settingsID > 0 && $this->db->table_exists('company_features')) {
        $featureRows = $this->db
            ->select('feature_key')
            ->from('company_features')
            ->where('settingsID', $settingsID)
            ->where('is_enabled', 1)
            ->get()
            ->result();
        $enabledFeatures = array();
        foreach ($featureRows as $row) {
            $featureKey = trim((string) ($row->feature_key ?? ''));
            if ($featureKey !== '') {
                $enabledFeatures[] = $featureKey;
            }
        }
        $package2Features = array('tasks', 'notes', 'calendar');
        $isPackage2 = count($enabledFeatures) === count($package2Features) && 
                     count(array_intersect($enabledFeatures, $package2Features)) === count($package2Features);
    }

    $isAdmin = $this->_is_admin_user();

    // Check if user has timed in today (skip for Admin and Package 2)
    if (!$isPackage2) {
      date_default_timezone_set('Asia/Manila');
      $today = date('Y-m-d');
      $hasTimeIn = $this->CashModel->hasTimeInToday($settingsID, $username, $today);

      if (!$hasTimeIn && !$isAdmin) {
        $this->session->set_flashdata('danger', 'You need to time in first before you can update task status.');
        redirect($redirectBack);
        return;
      }
    }

    $openTasks = $this->db
      ->select('taskID')
      ->from('projects_task')
      ->where('settingsID', $settingsID)
      ->where('projectID', $projectID)
      ->where('taskStat', 1)
      ->get()
      ->result();

    if (empty($openTasks)) {
      $this->session->set_flashdata('danger', 'No open tasks to close for this project.');
      redirect($redirectBack);
      return;
    }

    $datePosted = date('Y-m-d H:i:s');
    $closedCount = 0;

    foreach ($openTasks as $task) {
      $taskID = (int) $task->taskID;

      $statData = [
        'taskID' => $taskID,
        'note' => 'Closed via bulk action.',
        'datePosted' => $datePosted,
        'postedBy' => $username,
        'taskStat' => 0,
      ];

      if ($pointsEnabled) {
        $statData['points'] = $awardPoints ? 1 : 0;
      }

      $this->db->insert('projects_task_stat', $statData);

      $this->db->where('taskID', $taskID);
      $this->db->update('projects_task', [
        'taskStat' => 0,
        'completed_by' => $user_id,
      ]);

      $this->load->model('Notification_model', 'notifications');
      $this->notifications->resolve_task_notifications($taskID);

      $closedCount++;
    }

    $successMessage = 'Closed ' . $closedCount . ' open task' . ($closedCount === 1 ? '' : 's') . ' for this project.';
    if ($pointsEnabled) {
      $successMessage .= $awardPoints
        ? ' Points were credited to the assigned personnel.'
        : ' No points were credited.';
    }

    $this->session->set_flashdata('success', $successMessage);
    redirect($redirectBack);
  }




  public function changeDP()
  {
    $this->load->view('upload_profile_pic');
  }

  public function uploadProfPic()
  {
    $username = (string) $this->session->userdata('username');

    if ($username === '') {
      redirect('login');
      return;
    }

    $config = [
      'upload_path' => FCPATH . 'upload/profile/',
      'allowed_types' => 'jpg|jpeg|png|gif',
      'max_size' => 2048,
      'file_ext_tolower' => TRUE,
      'encrypt_name' => TRUE,
      'remove_spaces' => TRUE,
    ];

    if (!is_dir($config['upload_path'])) {
      if (!@mkdir($config['upload_path'], 0755, TRUE) && !is_dir($config['upload_path'])) {
        $this->session->set_flashdata('msg', '<div class="alert alert-danger text-center">Unable to prepare upload directory.</div>');
        redirect('Page/changeDP');
        return;
      }
    }

    $this->load->library('upload', $config);

    if (!$this->upload->do_upload('nonoy')) {
      $this->session->set_flashdata('msg', '<div class="alert alert-danger text-center">' . $this->upload->display_errors('', '') . '</div>');
      redirect('Page/changeDP');
      return;
    }

    $filename = $this->upload->data('file_name');

    $row = $this->db->select('avatar')->from('users')->where('username', $username)->get()->row();
    if (!$row && $this->db->table_exists('o_users')) {
      $row = $this->db->select('avatar')->from('o_users')->where('username', $username)->get()->row();
    }

    if ($row && $row->avatar && strtolower($row->avatar) !== 'avatar.png') {
      $old = $config['upload_path'] . $row->avatar;
      if (is_file($old)) {
        @unlink($old);
      }
    }

    $this->db->where('username', $username)->update('users', ['avatar' => $filename]);

    if ($this->db->table_exists('o_users')) {
      $this->db->where('username', $username)->update('o_users', ['avatar' => $filename]);
    }

    $this->session->set_userdata('avatar', $filename);
    $this->session->set_flashdata('msg', '<div class="alert alert-success text-center"><b>Profile picture updated successfully.</b></div>');
    redirect('Page/changeDP');
  }

  function paymentHistory()
  {
    $settingsID = $this->session->userdata('settingsID');
    $reference = trim((string) $this->input->get('id'));
    $invoiceNo = trim((string) $this->input->get('invoice_no'));
    $invoice = $this->_findInvoiceRecord($settingsID, $reference, $invoiceNo);

    if ($invoice) {
      $this->_syncInvoicePaymentTotals($settingsID, (string) $invoice->InvoiceNo, (int) $invoice->orderID);
      $invoice = $this->_findInvoiceRecord($settingsID, (string) $invoice->orderID, (string) $invoice->InvoiceNo);
    }

    if ($this->_is_client_user()) {
      if (!$invoice || !$this->_can_current_client_access_invoice($invoice)) {
        show_404();
        return;
      }
    }

    if ($this->_is_staff_user()) {
      if (!$invoice || !$this->_can_current_staff_access_invoice($invoice)) {
        show_404();
        return;
      }
    }

    $result['invoice'] = $invoice;

    if ($invoice) {
      $result['data'] = $this->CashModel->paymentHistory($invoice->InvoiceNo, $settingsID, (string) $invoice->CustID, $invoice->Customer);
    } else {
      $lookupInvoiceNo = $invoiceNo !== '' ? $invoiceNo : $reference;
      $result['data'] = $this->CashModel->paymentHistory($lookupInvoiceNo, $settingsID);
    }

    if ($this->_is_client_user()) {
      $result['backUrl'] = base_url() . 'Page/clientProfile?tab=invoices';
      $result['backLabel'] = 'Back to My Invoices';
      $result['clientMode'] = true;
    }

    $this->load->view('payment_history', $result);
  }

  function invoice()
  {
    $id = trim((string) $this->input->get('id'));
    $invoiceNo = trim((string) $this->input->get('invoice_no'));
    $settingsID = $this->session->userdata('settingsID');
    $invoice = $this->_findInvoiceRecord($settingsID, $id, $invoiceNo);

    if (!$invoice) {
      show_404();
      return;
    }

    if ($this->_is_client_user() && !$this->_can_current_client_access_invoice($invoice)) {
      show_404();
      return;
    }

    if (!$this->_can_current_staff_access_invoice($invoice)) {
      show_404();
      return;
    }

    if (trim((string) $invoice->CustAddress) === '') {
      $client = trim((string) $invoice->CustID) !== ''
        ? $this->CashModel->getClientByCustID($settingsID, (string) $invoice->CustID)
        : $this->CashModel->getClientByName($settingsID, $invoice->Customer);
      if ($client && !empty($client->Address)) {
        $invoice->CustAddress = $client->Address;
        if (trim((string) $invoice->Customer) === '' && !empty($client->Customer)) {
          $invoice->Customer = $client->Customer;
        }
      }
    }

    $businessDetails = $this->CashModel->businessDetails($settingsID);
    $invoiceFooter = $this->CashModel->invoiceFooterSettings($settingsID);
    $result['invoice'] = $invoice;
    $result['invoiceItems'] = $this->_loadInvoiceItems($invoice, $settingsID);
    $result['business'] = !empty($businessDetails) ? $businessDetails[0] : null;
    $result['invoiceFooter'] = !empty($invoiceFooter) ? $invoiceFooter[0] : null;
    $result['autoPrint'] = $this->input->get('print') == '1' || strtolower((string) $this->input->get('autoprint')) === 'true';
    if ($this->_is_client_user()) {
      $result['backUrl'] = base_url() . 'Page/clientProfile?tab=invoices';
      $result['backLabel'] = 'Back to My Invoices';
    }
    $this->load->view('invoice', $result);
  }

  function printJobOrderForm()
  {
    $id = (int) $this->input->get('id');
    $settingsID = $this->session->userdata('settingsID');

    if ($id <= 0) {
      show_404();
      return;
    }

    if (!$settingsID) {
      echo "Error: No settingsID found in session.";
      return;
    }

    $jobOrder = $this->CashModel->getJobOrderByID($id, $settingsID);

    if (!$jobOrder) {
      echo "Error: Job order with ID $id not found for settingsID $settingsID.";
      return;
    }

    $businessDetails = $this->CashModel->businessDetails($settingsID);

    $result['jobOrder'] = $jobOrder;
    $result['business'] = !empty($businessDetails) ? $businessDetails[0] : null;
    $result['printDate'] = date('F j, Y h:i A');

    $this->load->view('print_job_order_form', $result);
  }

  private function _resolveCustomerContext($settingsID, $custID = '', $fallbackCustomer = '', $fallbackAddress = '')
  {
    $custID = trim((string) $custID);
    $fallbackCustomer = trim((string) $fallbackCustomer);
    $fallbackAddress = trim((string) $fallbackAddress);
    $client = null;

    if ($custID !== '') {
      $client = $this->CashModel->getClientByCustID($settingsID, $custID);
    }

    if (!$client && $fallbackCustomer !== '') {
      $client = $this->CashModel->getClientByName($settingsID, $fallbackCustomer);
      if ($client && !empty($client->CustID)) {
        $custID = (string) $client->CustID;
      }
    }

    $customer = $client && !empty($client->Customer) ? (string) $client->Customer : $fallbackCustomer;
    $address = $client && !empty($client->Address) ? (string) $client->Address : $fallbackAddress;

    return array($custID, $customer, $address);
  }

  private function _currentUserDisplayName()
  {
    $name = trim((string) $this->session->userdata('name'));
    if ($name !== '') {
      return $name;
    }

    $username = trim((string) $this->session->userdata('username'));
    if ($username !== '') {
      return $username;
    }

    return 'System';
  }

  private function _currentUserRecordAliases()
  {
    $aliases = array();
    $seen = array();

    foreach (
      array(
        $this->session->userdata('name'),
        $this->session->userdata('username'),
      ) as $value
    ) {
      $value = trim((string) $value);
      if ($value === '') {
        continue;
      }

      $key = strtolower($value);
      if (isset($seen[$key])) {
        continue;
      }

      $seen[$key] = true;
      $aliases[] = $value;
    }

    if (empty($aliases)) {
      $aliases[] = $this->_currentUserDisplayName();
    }

    return $aliases;
  }

  private function _normalizePaymentAmount($value)
  {
    if ($value === null) {
      return 0;
    }

    $value = trim((string) $value);
    if ($value === '' || !is_numeric($value)) {
      return 0;
    }

    return round(max(0, (float) $value), 2);
  }

  private function _paymentCreditedAmount($amountPaid, $taxAmount = 0)
  {
    return round(
      max(0, (float) $amountPaid) + max(0, (float) $taxAmount),
      2
    );
  }

  private function _ensureServiceFeesTable()
  {
    if (!$this->db->table_exists('service_fees')) {
      $this->db->query("
        CREATE TABLE IF NOT EXISTS `service_fees` (
          `feesID` int unsigned NOT NULL AUTO_INCREMENT,
          `FeesDescription` varchar(100) NOT NULL DEFAULT '',
          `subCategory` varchar(120) DEFAULT NULL,
          `feeDetails` varchar(250) DEFAULT NULL,
          `Amount` double NOT NULL DEFAULT 0,
          `settingsID` int NOT NULL,
          PRIMARY KEY (`feesID`)
        ) ENGINE=InnoDB DEFAULT CHARSET=latin1
      ");
      return;
    }

    if (!$this->db->field_exists('subCategory', 'service_fees')) {
      $this->db->query("ALTER TABLE `service_fees` ADD COLUMN `subCategory` varchar(120) DEFAULT NULL AFTER `FeesDescription`");
    }
  }

  private function _ensureInvoiceItemsTable()
  {
    if ($this->db->table_exists('invoice_items')) {
      return;
    }

    $this->db->query("
      CREATE TABLE IF NOT EXISTS `invoice_items` (
        `itemID` int unsigned NOT NULL AUTO_INCREMENT,
        `orderID` int unsigned NOT NULL,
        `settingsID` int unsigned NOT NULL,
        `lineNo` int unsigned NOT NULL DEFAULT 1,
        `itemDescription` varchar(250) NOT NULL,
        `itemQuantity` int unsigned NOT NULL DEFAULT 1,
        `itemDurationUnit` varchar(20) DEFAULT NULL,
        `itemUnitPrice` double NOT NULL DEFAULT 0,
        `lineTotal` double NOT NULL DEFAULT 0,
        PRIMARY KEY (`itemID`),
        KEY `idx_invoice_items_order` (`orderID`, `settingsID`),
        KEY `idx_invoice_items_settings` (`settingsID`)
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci
    ");
  }

  private function _normalizeInvoiceItemDescription($value)
  {
    $value = trim((string) $value);

    // Strip duplicate quantity/price breakdown patterns like "(1 month @ PHP 3,700.00)"
    // Keep only the first occurrence and remove subsequent ones
    $pattern = '/\s*\(\d+(?:\.\d+)?\s+\w+\s+@\s+PHP\s+[\d,]+\.\d{2}\)/i';

    if (preg_match_all($pattern, $value, $matches, PREG_OFFSET_CAPTURE)) {
      if (count($matches[0]) > 1) {
        // Keep text up to the end of first match, strip the rest
        $firstMatchEnd = $matches[0][0][1] + strlen($matches[0][0][0]);
        $value = substr($value, 0, $firstMatchEnd);
      }
    }

    return $value;
  }

  private function _normalizeInvoiceItemRecord($item, $fallbackDescription = 'Invoice item', $fallbackTotal = 0, $settingsID = null, $allowUnknownUnit = false)
  {
    $description = $this->_normalizeInvoiceItemDescription(is_array($item) ? ($item['itemDescription'] ?? '') : ($item->itemDescription ?? ''));
    $quantity = $this->_normalizeInvoiceItemQuantity(is_array($item) ? ($item['itemQuantity'] ?? null) : ($item->itemQuantity ?? null));
    $durationUnit = $this->_normalizeInvoiceItemUnit(
      is_array($item) ? ($item['itemDurationUnit'] ?? null) : ($item->itemDurationUnit ?? null),
      $settingsID,
      $allowUnknownUnit
    );
    $unitPrice = $this->_normalizeInvoiceItemAmount(is_array($item) ? ($item['itemUnitPrice'] ?? null) : ($item->itemUnitPrice ?? null));
    $lineTotal = $this->_normalizeInvoiceItemAmount(is_array($item) ? ($item['lineTotal'] ?? null) : ($item->lineTotal ?? null));
    $fallbackDescription = $this->_normalizeInvoiceItemDescription($fallbackDescription);
    $fallbackTotal = round(max(0, (float) $fallbackTotal), 2);

    $hasMeaningfulValue = $description !== ''
      || $quantity !== null
      || $durationUnit !== null
      || $unitPrice !== null
      || $lineTotal !== null
      || $fallbackDescription !== ''
      || $fallbackTotal > 0;

    if (!$hasMeaningfulValue) {
      return null;
    }

    if ($quantity === null) {
      $quantity = 1;
    }

    if ($durationUnit === null) {
      $durationUnit = $this->_getDefaultInvoiceItemUnit($settingsID);
    }

    if ($lineTotal === null) {
      if ($unitPrice !== null) {
        $lineTotal = round($quantity * $unitPrice, 2);
      } else {
        $lineTotal = $fallbackTotal;
      }
    }

    if ($unitPrice === null) {
      $unitPrice = $quantity > 0 ? round($lineTotal / max($quantity, 1), 2) : $lineTotal;
    }

    if ($description === '') {
      $description = $fallbackDescription !== '' ? $fallbackDescription : 'Invoice item';
    }

    return array(
      'itemDescription' => $description,
      'itemQuantity' => $quantity,
      'itemDurationUnit' => $durationUnit,
      'itemUnitPrice' => round(max(0, (float) $unitPrice), 2),
      'lineTotal' => round(max(0, (float) $lineTotal), 2),
    );
  }

  private function _buildLegacyInvoiceItem($invoice)
  {
    if (!$invoice) {
      return null;
    }

    return $this->_normalizeInvoiceItemRecord(array(
      'itemDescription' => isset($invoice->JobDescription) ? $invoice->JobDescription : '',
      'itemQuantity' => isset($invoice->itemQuantity) ? $invoice->itemQuantity : null,
      'itemDurationUnit' => isset($invoice->itemDurationUnit) ? $invoice->itemDurationUnit : null,
      'itemUnitPrice' => isset($invoice->itemUnitPrice) ? $invoice->itemUnitPrice : null,
      'lineTotal' => isset($invoice->TotalDue) ? $invoice->TotalDue : 0,
    ), isset($invoice->JobDescription) ? (string) $invoice->JobDescription : 'Invoice item', isset($invoice->TotalDue) ? (float) $invoice->TotalDue : 0, isset($invoice->settingsID) ? (int) $invoice->settingsID : null, true);
  }

  private function _summarizeInvoiceItems($items, $fallbackDescription = '')
  {
    $fallbackDescription = $this->_normalizeInvoiceItemDescription($fallbackDescription);
    if (empty($items)) {
      return $fallbackDescription;
    }

    $primaryDescription = $this->_normalizeInvoiceItemDescription($items[0]['itemDescription'] ?? '');
    if ($primaryDescription === '') {
      $primaryDescription = $fallbackDescription !== '' ? $fallbackDescription : 'Invoice item';
    }

    $extraCount = count($items) - 1;
    if ($extraCount <= 0) {
      return $primaryDescription;
    }

    return $primaryDescription . ' +' . $extraCount . ' more item' . ($extraCount === 1 ? '' : 's');
  }

  private function _getItemizedInvoiceDescription($items, $fallbackDescription = '')
  {
    $fallbackDescription = $this->_normalizeInvoiceItemDescription($fallbackDescription);
    if (empty($items)) {
      return $fallbackDescription;
    }

    $itemDescriptions = array();
    foreach ($items as $item) {
      $description = $this->_normalizeInvoiceItemDescription($item['itemDescription'] ?? '');
      $quantity = isset($item['itemQuantity']) && is_numeric($item['itemQuantity']) && (float) $item['itemQuantity'] > 0
        ? (float) $item['itemQuantity']
        : 1;
      $unit = trim((string) ($item['itemDurationUnit'] ?? ''));
      $unitPrice = isset($item['itemUnitPrice']) && is_numeric($item['itemUnitPrice'])
        ? (float) $item['itemUnitPrice']
        : 0;
      $lineTotal = isset($item['lineTotal']) && is_numeric($item['lineTotal'])
        ? (float) $item['lineTotal']
        : round($quantity * $unitPrice, 2);

      // Format quantity display
      $quantityDisplay = (abs($quantity - round($quantity)) < 0.00001)
        ? (string) ((int) round($quantity))
        : number_format($quantity, 2);

      // Build item description - only add quantity/price if not already present
      $itemText = $description;
      $breakdownPattern = '(' . $quantityDisplay . ' ' . ($unit !== '' ? $unit : 'item');
      if (($quantity > 1 || $unit !== '') && stripos($description, $breakdownPattern) === false) {
        $unitLabel = $unit !== '' ? $unit : 'item';
        $itemText .= ' (' . $quantityDisplay . ' ' . $unitLabel;
        if ($unitPrice > 0) {
          $itemText .= ' @ PHP ' . number_format($unitPrice, 2);
        }
        $itemText .= ')';
      }

      $itemDescriptions[] = $itemText;
    }

    return implode("\n", $itemDescriptions);
  }

  private function _resolveInvoiceItems($descriptionInputs, $quantityInputs, $unitInputs, $unitPriceInputs, $fallbackDescription = '', $fallbackTotal = 0, $settingsID = null, $existingInvoice = null)
  {
    $descriptions = is_array($descriptionInputs) ? $descriptionInputs : array($descriptionInputs);
    $quantities = is_array($quantityInputs) ? $quantityInputs : array($quantityInputs);
    $units = is_array($unitInputs) ? $unitInputs : array($unitInputs);
    $unitPrices = is_array($unitPriceInputs) ? $unitPriceInputs : array($unitPriceInputs);

    $items = array();
    $rowCount = max(count($descriptions), count($quantities), count($units), count($unitPrices), 1);
    for ($index = 0; $index < $rowCount; $index++) {
      $description = $descriptions[$index] ?? '';
      $quantity = $quantities[$index] ?? null;
      $unit = $units[$index] ?? null;
      $unitPrice = $unitPrices[$index] ?? null;

      $rowHasValue = trim((string) $description) !== ''
        || trim((string) $quantity) !== ''
        || trim((string) $unit) !== ''
        || trim((string) $unitPrice) !== '';

      if (!$rowHasValue) {
        continue;
      }

      $item = $this->_normalizeInvoiceItemRecord(array(
        'itemDescription' => $description,
        'itemQuantity' => $quantity,
        'itemDurationUnit' => $unit,
        'itemUnitPrice' => $unitPrice,
      ), $fallbackDescription !== '' ? $fallbackDescription : 'Invoice item', 0, $settingsID, $existingInvoice !== null);

      if ($item !== null) {
        $items[] = $item;
      }
    }

    if (empty($items) && $existingInvoice) {
      $legacyItem = $this->_buildLegacyInvoiceItem($existingInvoice);
      if ($legacyItem !== null) {
        $items[] = $legacyItem;
      }
    }

    if (empty($items) && ($this->_normalizeInvoiceItemDescription($fallbackDescription) !== '' || (float) $fallbackTotal > 0)) {
      $defaultItem = $this->_normalizeInvoiceItemRecord(array(
        'itemDescription' => $fallbackDescription,
        'itemQuantity' => 1,
        'itemDurationUnit' => $this->_getDefaultInvoiceItemUnit($settingsID),
        'itemUnitPrice' => max(0, (float) $fallbackTotal),
        'lineTotal' => max(0, (float) $fallbackTotal),
      ), $fallbackDescription !== '' ? $fallbackDescription : 'Invoice item', $fallbackTotal, $settingsID, $existingInvoice !== null);
      if ($defaultItem !== null) {
        $items[] = $defaultItem;
      }
    }

    $totalDue = 0;
    foreach ($items as $item) {
      $totalDue += (float) ($item['lineTotal'] ?? 0);
    }
    $totalDue = round($totalDue, 2);

    return array(
      $items,
      $this->_getItemizedInvoiceDescription($items, $fallbackDescription),
      $totalDue,
      !empty($items) ? $items[0] : null,
    );
  }

  private function _loadInvoiceItems($invoice, $settingsID)
  {
    if (!$invoice) {
      return array();
    }

    $this->_ensureInvoiceItemsTable();
    $rows = $this->CashModel->getInvoiceItems((int) $invoice->orderID, $settingsID);
    $items = array();

    foreach ($rows as $row) {
      $item = $this->_normalizeInvoiceItemRecord($row, (string) ($row['itemDescription'] ?? $invoice->JobDescription ?? 'Invoice item'), 0, $settingsID, true);
      if ($item !== null) {
        $items[] = $item;
      }
    }

    if (empty($items)) {
      $legacyItem = $this->_buildLegacyInvoiceItem($invoice);
      if ($legacyItem !== null) {
        $items[] = $legacyItem;
      }
    }

    return $items;
  }

  private function _persistInvoiceItems($orderID, $settingsID, $items)
  {
    $orderID = (int) $orderID;
    if ($orderID <= 0) {
      return;
    }

    $this->_ensureInvoiceItemsTable();
    $this->CashModel->replaceInvoiceItems($orderID, $settingsID, $items);
  }

  private function _attachInvoiceItemsToCollection(&$invoices, $settingsID)
  {
    if (empty($invoices) || !is_array($invoices)) {
      return;
    }

    $this->_ensureInvoiceItemsTable();
    $orderIDs = array();
    foreach ($invoices as $invoice) {
      if (isset($invoice->orderID)) {
        $orderIDs[] = (int) $invoice->orderID;
      }
    }

    $itemsByOrder = $this->CashModel->getInvoiceItemsByOrderIDs($orderIDs, $settingsID);
    foreach ($invoices as $invoice) {
      $invoiceItems = array();
      $rows = isset($itemsByOrder[(int) $invoice->orderID]) ? $itemsByOrder[(int) $invoice->orderID] : array();
      foreach ($rows as $row) {
        $item = $this->_normalizeInvoiceItemRecord($row, (string) ($row['itemDescription'] ?? $invoice->JobDescription ?? 'Invoice item'), 0, $settingsID, true);
        if ($item !== null) {
          $invoiceItems[] = $item;
        }
      }

      if (empty($invoiceItems)) {
        $legacyItem = $this->_buildLegacyInvoiceItem($invoice);
        if ($legacyItem !== null) {
          $invoiceItems[] = $legacyItem;
        }
      }

      $invoice->invoiceItems = $invoiceItems;
      $invoice->invoiceSummary = $this->_getItemizedInvoiceDescription($invoiceItems, (string) ($invoice->JobDescription ?? ''));
    }
  }

  private function _syncInvoicePaymentTotals($settingsID, $invoiceNo = '', $orderID = 0)
  {
    $settingsID = (int) $settingsID;
    $invoiceNo = trim((string) $invoiceNo);
    $orderID = (int) $orderID;

    if ($settingsID <= 0) {
      return;
    }

    $invoice = null;
    if ($orderID > 0) {
      $invoice = $this->CashModel->getInvoiceByOrderID($orderID, $settingsID);
    }

    if (!$invoice && $invoiceNo !== '') {
      $invoice = $this->CashModel->getInvoiceByInvoiceNo($invoiceNo, $settingsID);
    }

    if (!$invoice) {
      return;
    }

    $sumRow = $this->db
      ->select('COALESCE(SUM(AmountPaid + COALESCE(TaxAmount, 0)), 0) AS total_paid', false)
      ->from('payments')
      ->where('settingsID', $settingsID)
      ->where('InvoiceNo', (string) $invoice->InvoiceNo)
      ->where('ORStat', 'Valid')
      ->get()
      ->row();

    $totalPaid = round((float) ($sumRow->total_paid ?? 0), 2);
    $balance = max(0, round((float) $invoice->TotalDue - $totalPaid, 2));

    $this->db
      ->where('orderID', (int) $invoice->orderID)
      ->where('settingsID', $settingsID)
      ->update('invoice', array(
        'AmountPaid' => $totalPaid,
        'Balance' => $balance,
      ));

    // Update delivery status when invoice is paid
    $this->load->model('DeliveryModel');
    $this->DeliveryModel->update_delivery_status_by_invoice((string) $invoice->InvoiceNo, $settingsID);
  }

  private function _normalizeRecurringFrequency($value)
  {
    $value = strtolower(trim((string) $value));
    return in_array($value, array('daily', 'weekly', 'monthly', 'quarterly', 'yearly'), true) ? $value : 'none';
  }

  private function _normalizeCoverageOption($value)
  {
    return strtolower(trim((string) $value)) === 'previous' ? 'previous' : 'coming';
  }

  private function _normalizeInvoiceItemQuantity($value)
  {
    if ($value === null) {
      return null;
    }

    $value = trim((string) $value);
    if ($value === '' || !is_numeric($value)) {
      return null;
    }

    $quantity = (int) round((float) $value);
    return $quantity > 0 ? $quantity : null;
  }

  private function _normalizeInvoiceItemUnit($value, $settingsID = null, $allowUnknown = false)
  {
    $value = strtolower(trim((string) $value));
    if ($value === '') {
      return null;
    }

    if (in_array($value, $this->_getInvoiceUnitChoices($settingsID), true)) {
      return $value;
    }

    return $allowUnknown ? $value : null;
  }

  private function _normalizeInvoiceItemAmount($value)
  {
    if ($value === null) {
      return null;
    }

    $value = trim((string) $value);
    if ($value === '' || !is_numeric($value)) {
      return null;
    }

    return round((float) $value, 2);
  }

  private function _resolveInvoiceItemBreakdown($quantityInput, $unitInput, $unitPriceInput, $totalDue, $settingsID = null, $existingInvoice = null)
  {
    $ItemQuantity = $this->_normalizeInvoiceItemQuantity($quantityInput);
    $ItemDurationUnit = $this->_normalizeInvoiceItemUnit($unitInput, $settingsID, $existingInvoice !== null);
    $ItemUnitPrice = $this->_normalizeInvoiceItemAmount($unitPriceInput);
    $TotalDue = round((float) $totalDue, 2);

    if ($ItemQuantity === null && $existingInvoice && isset($existingInvoice->itemQuantity)) {
      $ItemQuantity = $this->_normalizeInvoiceItemQuantity($existingInvoice->itemQuantity);
    }

    if ($ItemDurationUnit === null && $existingInvoice && isset($existingInvoice->itemDurationUnit)) {
      $ItemDurationUnit = $this->_normalizeInvoiceItemUnit($existingInvoice->itemDurationUnit, $settingsID, true);
    }

    if ($ItemUnitPrice === null && $existingInvoice && isset($existingInvoice->itemUnitPrice)) {
      $ItemUnitPrice = $this->_normalizeInvoiceItemAmount($existingInvoice->itemUnitPrice);
    }

    if ($ItemQuantity === null && $ItemUnitPrice !== null) {
      $ItemQuantity = 1;
    }

    if ($ItemQuantity !== null && $ItemDurationUnit === null) {
      $ItemDurationUnit = $this->_getDefaultInvoiceItemUnit($settingsID);
    }

    if ($ItemQuantity !== null && $ItemUnitPrice !== null) {
      $TotalDue = round($ItemQuantity * $ItemUnitPrice, 2);
    } elseif ($ItemQuantity !== null && $TotalDue >= 0) {
      $ItemUnitPrice = round($TotalDue / max($ItemQuantity, 1), 2);
    }

    return array($ItemQuantity, $ItemDurationUnit, $ItemUnitPrice, $TotalDue);
  }

  private function _nextInvoiceNumber($settingsID)
  {
    $row = $this->db
      ->select('MAX(CAST(InvoiceNo AS UNSIGNED)) AS max_invoice_no', false)
      ->from('invoice')
      ->where('settingsID', $settingsID)
      ->get()
      ->row();

    $maxInvoiceNo = ($row && isset($row->max_invoice_no) && is_numeric($row->max_invoice_no))
      ? (int) $row->max_invoice_no
      : 100000;

    return (string) ($maxInvoiceNo + 1);
  }

  private function _advanceRecurringDate($date, $frequency)
  {
    $normalizedDate = $this->_normalizeDateInput($date);
    if ($normalizedDate === null) {
      return null;
    }

    if ($frequency === 'daily') {
      $modifier = '+1 day';
    } elseif ($frequency === 'weekly') {
      $modifier = '+1 week';
    } elseif ($frequency === 'quarterly') {
      $modifier = '+3 months';
    } elseif ($frequency === 'yearly') {
      $modifier = '+1 year';
    } else {
      $modifier = '+1 month';
    }
    return date('Y-m-d', strtotime($modifier, strtotime($normalizedDate)));
  }

  private function _createRecurringInvoiceOccurrence($template, $settingsID, $frequency, $scheduleDate)
  {
    $existingOccurrence = $this->db
      ->select('orderID, InvoiceNo')
      ->from('invoice')
      ->where('settingsID', $settingsID)
      ->group_start()
      ->where('orderID', (int) $template->orderID)
      ->or_where('recurringTemplateID', (int) $template->orderID)
      ->group_end()
      ->where('recurringScheduleDate', $scheduleDate)
      ->limit(1)
      ->get()
      ->row();

    if ($existingOccurrence) {
      return array(
        'status' => 'existing',
        'orderID' => (int) ($existingOccurrence->orderID ?? 0),
        'invoiceNo' => (string) ($existingOccurrence->InvoiceNo ?? ''),
        'scheduleDate' => $scheduleDate,
      );
    }

    $nextInvoiceNo = $this->_nextInvoiceNumber($settingsID);
    $generatedPayload = array(
      'InvoiceNo' => $nextInvoiceNo,
      'CustID' => trim((string) $template->CustID) !== '' ? $template->CustID : null,
      'Customer' => (string) $template->Customer,
      'CustAddress' => (string) $template->CustAddress,
      'TransDate' => $scheduleDate,
      'JobDescription' => (string) $template->JobDescription,
      'itemQuantity' => isset($template->itemQuantity) ? $template->itemQuantity : null,
      'itemDurationUnit' => isset($template->itemDurationUnit) ? $template->itemDurationUnit : null,
      'itemUnitPrice' => isset($template->itemUnitPrice) ? $template->itemUnitPrice : null,
      'TotalDue' => (float) $template->TotalDue,
      'AmountPaid' => 0,
      'Balance' => (float) $template->TotalDue,
      'ReceiveDate' => $scheduleDate,
      'Notes' => (string) $template->Notes,
      'settingsID' => $settingsID,
      'invoiceStat' => 'active',
      'invoiceSource' => (string) $template->invoiceSource,
      'invoiceBy' => trim((string) $template->invoiceBy) !== '' ? (string) $template->invoiceBy : $this->_currentUserDisplayName(),
      'recurringFrequency' => $frequency,
      'coverageOption' => $this->_normalizeCoverageOption($template->coverageOption ?? 'coming'),
      'recurringScheduleDate' => $scheduleDate,
      'recurringTerminationDate' => $this->_normalizeDateInput($template->recurringTerminationDate ?? ''),
      'invoiceExpirationDate' => $this->_normalizeDateInput($template->invoiceExpirationDate ?? ''),
      'recurringTemplateID' => (int) $template->orderID,
      'lastRecurringGeneratedFor' => null,
    );

    if (!$this->db->insert('invoice', $generatedPayload)) {
      return array('status' => 'error', 'message' => 'The invoice record could not be saved.');
    }

    $generatedOrderID = (int) $this->db->insert_id();
    if ($generatedOrderID <= 0) {
      return array('status' => 'error', 'message' => 'The generated invoice could not be identified.');
    }

    $this->_persistInvoiceItems($generatedOrderID, $settingsID, $this->_loadInvoiceItems($template, $settingsID));

    return array(
      'status' => 'generated',
      'orderID' => $generatedOrderID,
      'invoiceNo' => $nextInvoiceNo,
      'scheduleDate' => $scheduleDate,
    );
  }

  private function _generateNextRecurringInvoiceForTemplate($settingsID, $templateID)
  {
    $template = $this->CashModel->getInvoiceByOrderID((int) $templateID, $settingsID);
    if (!$template) {
      return array('status' => 'error', 'message' => 'Recurring invoice template not found.');
    }

    if (
      (string) ($template->invoiceStat ?? '') !== 'active'
      || (string) ($template->invoiceSource ?? '') !== 'Others'
      || (int) ($template->recurringTemplateID ?? 0) !== 0
    ) {
      return array('status' => 'error', 'message' => 'This invoice is not an active recurring template.');
    }

    $frequency = $this->_normalizeRecurringFrequency($template->recurringFrequency ?? 'none');
    if ($frequency === 'none') {
      return array('status' => 'error', 'message' => 'This template does not have a valid recurring frequency.');
    }

    $this->_ensureInvoiceItemsTable();
    $this->db->trans_begin();
    $lockedTemplate = $this->db->query(
      'SELECT orderID FROM invoice WHERE orderID = ? AND settingsID = ? FOR UPDATE',
      array((int) $templateID, (int) $settingsID)
    )->row();
    if (!$lockedTemplate) {
      $this->db->trans_rollback();
      return array('status' => 'error', 'message' => 'Recurring invoice template not found.');
    }

    $template = $this->CashModel->getInvoiceByOrderID((int) $templateID, $settingsID);
    if (!$template) {
      $this->db->trans_rollback();
      return array('status' => 'error', 'message' => 'Recurring invoice template not found.');
    }

    $frequency = $this->_normalizeRecurringFrequency($template->recurringFrequency ?? 'none');
    if (
      (string) ($template->invoiceStat ?? '') !== 'active'
      || (string) ($template->invoiceSource ?? '') !== 'Others'
      || (int) ($template->recurringTemplateID ?? 0) !== 0
      || $frequency === 'none'
    ) {
      $this->db->trans_rollback();
      return array('status' => 'error', 'message' => 'This invoice is no longer an active recurring template.');
    }

    $baseScheduleDate = $this->_normalizeDateInput($template->recurringScheduleDate ?? '')
      ?: $this->_normalizeDateInput($template->TransDate ?? '');
    if ($baseScheduleDate === null) {
      $this->db->trans_rollback();
      return array('status' => 'error', 'message' => 'This template does not have a valid schedule date.');
    }

    $lastGeneratedFor = $this->_normalizeDateInput($template->lastRecurringGeneratedFor ?? '');
    if ($lastGeneratedFor === null || strtotime($lastGeneratedFor) < strtotime($baseScheduleDate)) {
      $lastGeneratedFor = $baseScheduleDate;
    }

    $seriesEndDate = $this->_resolveRecurringSeriesEndDate($template);
    $nextScheduleDate = $this->_advanceRecurringDate($lastGeneratedFor, $frequency);
    $guard = 0;

    while ($nextScheduleDate !== null && $guard < 4000) {
      if ($seriesEndDate !== null && strtotime($nextScheduleDate) > strtotime($seriesEndDate)) {
        $this->db->trans_rollback();
        return array('status' => 'ended', 'message' => 'This recurring schedule has no remaining invoice dates.');
      }

      $result = $this->_createRecurringInvoiceOccurrence($template, $settingsID, $frequency, $nextScheduleDate);
      if (($result['status'] ?? '') === 'error') {
        $this->db->trans_rollback();
        return $result;
      }

      $this->db
        ->where('orderID', (int) $template->orderID)
        ->where('settingsID', $settingsID)
        ->update('invoice', array('lastRecurringGeneratedFor' => $nextScheduleDate));

      if (($result['status'] ?? '') === 'generated') {
        if ($this->db->trans_status() === false) {
          $this->db->trans_rollback();
          return array('status' => 'error', 'message' => 'The generated invoice could not be committed.');
        }

        $this->db->trans_commit();
        return $result;
      }

      $nextScheduleDate = $this->_advanceRecurringDate($nextScheduleDate, $frequency);
      $guard++;
    }

    $this->db->trans_rollback();
    return array('status' => 'error', 'message' => 'The next recurring invoice date could not be determined.');
  }

  private function _generateRecurringInvoices($settingsID)
  {
    date_default_timezone_set('Asia/Manila');
    $today = date('Y-m-d');
    $summary = array(
      'checkedAt' => date('Y-m-d H:i:s'),
      'templateCount' => 0,
      'generatedCount' => 0,
      'existingCount' => 0,
    );

    $templates = $this->db
      ->select('orderID')
      ->from('invoice')
      ->where('settingsID', $settingsID)
      ->where('invoiceStat', 'active')
      ->where('invoiceSource', 'Others')
      ->where_in('recurringFrequency', array('daily', 'weekly', 'monthly', 'quarterly', 'yearly'))
      ->group_start()
      ->where('recurringTemplateID IS NULL', null, false)
      ->or_where('recurringTemplateID', 0)
      ->group_end()
      ->order_by('orderID', 'ASC')
      ->get()
      ->result();

    if (empty($templates)) {
      return $summary;
    }

    foreach ($templates as $templateMeta) {
      $template = $this->CashModel->getInvoiceByOrderID((int) $templateMeta->orderID, $settingsID);
      if (!$template) {
        continue;
      }

      $frequency = $this->_normalizeRecurringFrequency($template->recurringFrequency);
      if ($frequency === 'none') {
        continue;
      }

      $baseScheduleDate = $this->_normalizeDateInput($template->recurringScheduleDate) ?: $this->_normalizeDateInput($template->TransDate);
      if ($baseScheduleDate === null) {
        continue;
      }

      $seriesEndDate = $this->_resolveRecurringSeriesEndDate($template);
      if ($seriesEndDate !== null && strtotime($today) > strtotime($seriesEndDate)) {
        continue;
      }

      $summary['templateCount']++;

      $lastGeneratedFor = $this->_normalizeDateInput($template->lastRecurringGeneratedFor);
      if ($lastGeneratedFor === null || strtotime($lastGeneratedFor) < strtotime($baseScheduleDate)) {
        $lastGeneratedFor = $baseScheduleDate;
      }
      $nextScheduleDate = $this->_advanceRecurringDate($lastGeneratedFor, $frequency);

      while (
        $nextScheduleDate !== null
        && ($seriesEndDate === null || strtotime($nextScheduleDate) <= strtotime($seriesEndDate))
        && strtotime($today) >= strtotime($nextScheduleDate . ' -10 days')
      ) {
        $generationResult = $this->_createRecurringInvoiceOccurrence($template, $settingsID, $frequency, $nextScheduleDate);
        if (($generationResult['status'] ?? '') === 'generated') {
          $summary['generatedCount']++;
        } elseif (($generationResult['status'] ?? '') === 'existing') {
          $summary['existingCount']++;
        } else {
          break;
        }

        $this->db
          ->where('orderID', (int) $template->orderID)
          ->where('settingsID', $settingsID)
          ->update('invoice', array('lastRecurringGeneratedFor' => $nextScheduleDate));

        $lastGeneratedFor = $nextScheduleDate;
        $nextScheduleDate = $this->_advanceRecurringDate($lastGeneratedFor, $frequency);
      }
    }

    return $summary;
  }

  private function _findInvoiceRecord($settingsID, $reference = '', $invoiceNo = '')
  {
    if ($invoiceNo !== '') {
      $invoice = $this->CashModel->getInvoiceByInvoiceNo($invoiceNo, $settingsID);
      if ($invoice) {
        return $invoice;
      }
    }

    if ($reference === '') {
      return null;
    }

    if (ctype_digit($reference)) {
      $invoice = $this->CashModel->getInvoiceByOrderID((int) $reference, $settingsID);
      if ($invoice) {
        return $invoice;
      }
    }

    return $this->CashModel->getInvoiceByInvoiceNo($reference, $settingsID);
  }

  private function _detectTimeSlot($timestamp = null)
  {
    $timestamp = $timestamp ?? time();
    $hour = (int) date('G', $timestamp);
    return $hour < 12 ? 'am' : 'pm';
  }

  private function _autoTimeIn($slotOverride = null)
  {
    date_default_timezone_set('Asia/Manila'); # add your city to set local time zone
    $logDate = date("Y-m-d");
    $now = date('H:i:s A');
    $slot = $slotOverride ? strtolower($slotOverride) : $this->_detectTimeSlot();

    $settingsID = $this->session->userdata('settingsID');
    $IDNumber = $this->session->userdata('username');

    $openTimeIn = $this->db->query("select * from dtr where logDate='$logDate' and IDNumber='$IDNumber' and ((amTimeIn!='' and (amTimeOut='' or amTimeOut is null)) or (pmTimeIn!='' and (pmTimeOut='' or pmTimeOut is null))) order by dtrID desc limit 1")->row();
    if ($openTimeIn) {
      $this->session->set_flashdata('msg', '<div class="alert alert-danger text-center"><b>Please time-out first.</b></div>');
      redirect('Page/dtr');
    }

    $latest = $this->db->query("select * from dtr where logDate='$logDate' and IDNumber='$IDNumber' order by dtrID desc limit 1")->row();

    if ($slot === 'pm') {
      if ($latest && empty($latest->pmTimeIn)) {
        $this->db->query("update dtr set pmTimeIn='$now', pmTimeInStat='Closed', pmTimeOutStat='Open' where dtrID='{$latest->dtrID}'");
      } else {
        $this->db->query("insert into dtr values('0','$logDate','','','$now','','$IDNumber','Open','Open','Closed','Open','$settingsID')");
      }
      $slotLabel = 'PM';
    } else {
      if ($latest && empty($latest->amTimeIn)) {
        $this->db->query("update dtr set amTimeIn='$now', amTimeInStat='Closed', amTimeOutStat='Open' where dtrID='{$latest->dtrID}'");
      } else {
        $this->db->query("insert into dtr values('0','$logDate','$now','','','','$IDNumber','Closed','Open','Open','Open','$settingsID')");
      }
      $slotLabel = 'AM';
    }

    $this->session->set_flashdata('msg', '<div class="alert alert-success text-center"><b>Time-in recorded (auto: ' . $slotLabel . ').</b></div>');
    redirect('Page/dtr');
  }

  private function _autoTimeOut($slotOverride = null)
  {

    date_default_timezone_set('Asia/Manila'); # add your city to set local time zone
    $logDate = date("Y-m-d");
    $now = date('H:i:s A');
    $slot = $slotOverride ? strtolower($slotOverride) : $this->_detectTimeSlot();

    $IDNumber = $this->session->userdata('username');

    $openAm = $this->db->query("select * from dtr where logDate='$logDate' and IDNumber='$IDNumber' and amTimeIn!='' and (amTimeOut='' or amTimeOut is null) order by dtrID desc limit 1")->row();
    $openPm = $this->db->query("select * from dtr where logDate='$logDate' and IDNumber='$IDNumber' and pmTimeIn!='' and (pmTimeOut='' or pmTimeOut is null) order by dtrID desc limit 1")->row();

    $targetSlot = '';
    $targetRow = null;

    if ($slot === 'pm') {
      if ($openPm) {
        $targetSlot = 'pm';
        $targetRow = $openPm;
      } elseif ($openAm) {
        $targetSlot = 'am';
        $targetRow = $openAm;
      }
    } else {
      if ($openAm) {
        $targetSlot = 'am';
        $targetRow = $openAm;
      } elseif ($openPm) {
        $targetSlot = 'pm';
        $targetRow = $openPm;
      }
    }

    if ($targetSlot === 'am') {
      $this->db->query("update dtr set amTimeOut='$now',amTimeOutStat='Closed' where dtrID='{$targetRow->dtrID}'");
      $this->session->set_flashdata('msg', '<div class="alert alert-success text-center"><b>Time-out recorded for AM slot.</b></div>');
    } elseif ($targetSlot === 'pm') {
      $this->db->query("update dtr set pmTimeOut='$now', pmTimeOutStat='Closed' where dtrID='{$targetRow->dtrID}'");
      $this->session->set_flashdata('msg', '<div class="alert alert-success text-center"><b>Time-out recorded for PM slot.</b></div>');
    } else {
      $this->session->set_flashdata('msg', '<div class="alert alert-danger text-center"><b>No open time-in found.</b></div>');
    }

    redirect('Page/dtr');
  }

  function amTimeIn()
  {
    $this->_autoTimeIn();
  }

  function amTimeOut()
  {
    $this->_autoTimeOut();
  }

  function pmTimeIn()
  {
    // Backward compatibility: still route through the auto-detect logic.
    $this->_autoTimeIn('pm');
  }

  function pmTimeOut()
  {
    // Backward compatibility: still route through the auto-detect logic.
    $this->_autoTimeOut('pm');
  }
  function businessDetails()
  {
    $settingsID = $this->session->userdata('settingsID');

    if (!$this->db->field_exists('BusinessLines', 'pos_settings')) {
      $this->db->query("ALTER TABLE `pos_settings` ADD COLUMN `BusinessLines` text DEFAULT NULL AFTER `CompType`");
    }

    if ($this->input->method() === 'post') {
      $CompName = trim((string) $this->input->post('CompName', true));
      $CompAddress = trim((string) $this->input->post('CompAddress', true));
      $CompTin = trim((string) $this->input->post('CompTin', true));
      $Proprietor = trim((string) $this->input->post('Proprietor', true));
      $CompType = trim((string) $this->input->post('CompType', true));
      $businessLinesInput = $this->input->post('business_lines');
      $businessLines = [];
      if (is_array($businessLinesInput)) {
        foreach ($businessLinesInput as $line) {
          $line = trim((string) $line);
          if ($line !== '') {
            $businessLines[] = $line;
          }
        }
      }
      $businessLines = array_values(array_unique($businessLines));

      if ($CompName === '') {
        $this->session->set_flashdata('danger', 'Company name is required.');
        redirect('Page/businessDetails');
        return;
      }

      $payload = array(
        'CompName' => $CompName,
        'CompAddress' => $CompAddress,
        'CompTin' => $CompTin,
        'Proprietor' => $Proprietor,
        'CompType' => $CompType,
        'BusinessLines' => json_encode($businessLines),
      );

      $existing = $this->db
        ->where('settingsID', $settingsID)
        ->get('pos_settings')
        ->row();

      if ($existing) {
        $this->db->where('settingsID', $settingsID);
        $this->db->update('pos_settings', $payload);
      } else {
        $payload['settingsID'] = $settingsID;
        $this->db->insert('pos_settings', $payload);
      }

      // Save invoice footer settings
      $this->_ensureInvoiceFooterSettingsTable();
      $footerPayload = array(
        'bank_name_1' => trim((string) $this->input->post('bank_name_1', true)),
        'bank_account_name_1' => trim((string) $this->input->post('bank_account_name_1', true)),
        'bank_account_no_1' => trim((string) $this->input->post('bank_account_no_1', true)),
        'bank_name_2' => trim((string) $this->input->post('bank_name_2', true)),
        'bank_account_name_2' => trim((string) $this->input->post('bank_account_name_2', true)),
        'bank_account_no_2' => trim((string) $this->input->post('bank_account_no_2', true)),
        'contact_email' => trim((string) $this->input->post('contact_email', true)),
        'contact_phone' => trim((string) $this->input->post('contact_phone', true)),
        'footer_disclaimer' => trim((string) $this->input->post('footer_disclaimer', true)),
      );

      $existingFooter = $this->db
        ->where('settingsID', $settingsID)
        ->get('invoice_footer_settings')
        ->row();

      if ($existingFooter) {
        $this->db->where('settingsID', $settingsID);
        $this->db->update('invoice_footer_settings', $footerPayload);
      } else {
        $footerPayload['settingsID'] = $settingsID;
        $this->db->insert('invoice_footer_settings', $footerPayload);
      }

      $this->session->set_flashdata('success', 'Business details updated successfully.');
      redirect('Page/businessDetails');
      return;
    }

    $result['data'] = $this->CashModel->businessDetails($settingsID);
    $result['invoiceFooter'] = $this->CashModel->invoiceFooterSettings($settingsID);
    $this->load->view('business_details', $result);
  }

  private function _ensureInvoiceFooterSettingsTable()
  {
    if ($this->db->table_exists('invoice_footer_settings')) {
      return;
    }

    $this->db->query("
      CREATE TABLE IF NOT EXISTS `invoice_footer_settings` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `settingsID` int(11) NOT NULL,
        `bank_name_1` varchar(255) DEFAULT NULL,
        `bank_account_name_1` varchar(255) DEFAULT NULL,
        `bank_account_no_1` varchar(255) DEFAULT NULL,
        `bank_name_2` varchar(255) DEFAULT NULL,
        `bank_account_name_2` varchar(255) DEFAULT NULL,
        `bank_account_no_2` varchar(255) DEFAULT NULL,
        `contact_email` varchar(255) DEFAULT NULL,
        `contact_phone` varchar(100) DEFAULT NULL,
        `footer_disclaimer` text DEFAULT NULL,
        `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        UNIQUE KEY `idx_settings` (`settingsID`)
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci
    ");
  }


  public function staffprofile()
  {

    if (!$this->session->userdata('username')) {
      redirect('Login');
      return;
    }

    $level = $this->session->userdata('level');
    $id = '';


    if ($level === 'Admin' || $level === 'HR Admin') {
      $id = trim($this->input->get('id'));
    }


    if ($id === '') {
      $id = trim($this->session->userdata('username'));
    }

    if ($id === '') {
      show_error('Missing username for staff profile.', 400);
      return;
    }


    $profile = $this->StudentModel->staffProfile($id);

    if (!$profile) {
      show_error(
        'User account not found for username: ' . htmlspecialchars($id),
        404
      );
      return;
    }


    $pics = $this->PersonnelModel->profilepic($id);
    if (!is_array($pics)) {
      $pics = [];
    }

    // Look up the matching employee record by email so Edit Profile links work
    $empID = '';
    $profileEmail = trim((string) ($profile->email ?? ''));
    if ($profileEmail !== '') {
      $settingsID = (int) $this->session->userdata('settingsID');
      $employeeRow = $this->db
        ->where('email', $profileEmail)
        ->where('settingsID', $settingsID)
        ->get('employee')
        ->row();
      if ($employeeRow && isset($employeeRow->empID)) {
        $empID = (string) $employeeRow->empID;
      }
    }

    $result = [];
    $result['data'] = $profile;
    $result['data1'] = $pics;
    $result['empID'] = $empID;



    $this->load->view('profile_page_staff', $result);
  }

  private function _getInvoiceUnitChoices($settingsID)
  {
    $settingsID = (int) $settingsID;
    if ($settingsID <= 0) {
      return array('each');
    }

    if (isset($this->invoiceUnitChoicesCache[$settingsID])) {
      return $this->invoiceUnitChoicesCache[$settingsID];
    }

    $choices = array();
    $rows = $this->SettingsModel->getInvoiceUnits($settingsID);
    foreach ($rows as $row) {
      $unitName = strtolower(trim((string) ($row->unitName ?? '')));
      if ($unitName !== '') {
        $choices[$unitName] = $unitName;
      }
    }

    if (empty($choices)) {
      $choices['each'] = 'each';
    }

    $this->invoiceUnitChoicesCache[$settingsID] = array_values($choices);
    return $this->invoiceUnitChoicesCache[$settingsID];
  }

  private function _getDefaultInvoiceItemUnit($settingsID)
  {
    $choices = $this->_getInvoiceUnitChoices($settingsID);
    return !empty($choices) ? (string) $choices[0] : 'each';
  }

  // Customer Delivery Methods

  function customerDeliveryList()
  {
    if (!in_array($this->session->userdata('level'), ['Admin', 'Account', 'Staff', 'Encoder'])) {
      echo "Access Denied";
      return;
    }

    $settingsID = $this->session->userdata('settingsID');
    $statusFilter = $this->input->get('status');
    $userLevel = $this->session->userdata('level');
    $username = $this->session->userdata('username');

    // Auto-create customer delivery tables if they don't exist
    $this->_ensureCustomerDeliveryTables();

    // Sync payment data from payments table to customer_deliveries
    $this->_syncDeliveryPayments($settingsID);

    $this->load->model('DeliveryModel');

    // For Staff users, filter to show only deliveries they encoded
    if (in_array($userLevel, array('Staff', 'Encoder'), true)) {
      $result['data'] = $this->DeliveryModel->get_deliveries_by_user($settingsID, $username, $statusFilter);
    } else {
      $result['data'] = $this->DeliveryModel->get_deliveries($settingsID, $statusFilter);
    }

    $result['data2'] = $this->CashModel->getClients($settingsID);
    $this->load->view('customer_delivery_list', $result);
  }

  function newCustomerDelivery()
  {
    if (!in_array($this->session->userdata('level'), ['Admin', 'Account', 'Staff', 'Encoder'])) {
      echo "Access Denied";
      return;
    }

    $settingsID = $this->session->userdata('settingsID');

    // Auto-create customer delivery tables if they don't exist
    $this->_ensureCustomerDeliveryTables();

    $this->load->model('DeliveryModel');

    $result['data2'] = $this->CashModel->getClients($settingsID);
    $result['serviceFees'] = $this->CashModel->priceList($settingsID);
    $result['data3'] = $this->CashModel->joInvoiceNo($settingsID);
    $this->load->view('new_customer_delivery', $result);
  }

  function saveCustomerDelivery()
  {
    if (!in_array($this->session->userdata('level'), ['Admin', 'Account', 'Staff', 'Encoder'])) {
      echo "Access Denied";
      return;
    }

    // Auto-create customer delivery tables if they don't exist
    $this->_ensureCustomerDeliveryTables();

    $this->load->model('DeliveryModel');

    $orderID = $this->input->post('orderID');
    $customerID = $this->input->post('customerID');
    $invoiceNo = $this->input->post('invoiceNo');
    $notes = $this->input->post('notes');

    $deliveryNo = $this->input->post('deliveryNo');

    $deliveryData = [
      'deliveryNo' => !empty($deliveryNo) ? $deliveryNo : NULL,
      'invoiceNo' => !empty($invoiceNo) ? $invoiceNo : NULL,
      'orderID' => !empty($orderID) ? $orderID : NULL,
      'customerID' => !empty($customerID) ? $customerID : NULL,
      'customerName' => $this->input->post('customerName'),
      'customerAddress' => $this->input->post('customerAddress'),
      'deliveryDate' => $this->input->post('deliveryDate'),
      'deliveryStatus' => 'pending',
      'paymentStatus' => 'unpaid',
      'receivedBy' => $this->input->post('receivedBy'),
      'deliveredBy' => $this->session->userdata('username'),
      'notes' => !empty($notes) ? $notes : NULL
    ];

    $deliveryID = $this->DeliveryModel->create_delivery($deliveryData);

    // Add delivery items
    $itemDescriptions = $this->input->post('itemDescription');
    $itemQuantities = $this->input->post('itemQuantity');
    $itemUnits = $this->input->post('itemUnit');
    $itemUnitPrices = $this->input->post('itemUnitPrice');
    $serialNos = $this->input->post('serialNo');
    $models = $this->input->post('model');
    $brands = $this->input->post('brand');

    $totalAmount = 0;
    if (!empty($itemDescriptions)) {
      foreach ($itemDescriptions as $key => $desc) {
        if (!empty($desc)) {
          $quantity = $itemQuantities[$key] ?? 1;
          $unitPrice = $itemUnitPrices[$key] ?? 0;
          $lineTotal = $quantity * $unitPrice;
          $totalAmount += $lineTotal;

          $itemData = [
            'deliveryID' => $deliveryID,
            'lineNo' => $key + 1,
            'itemDescription' => $desc,
            'itemQuantity' => $quantity,
            'itemUnit' => $itemUnits[$key] ?? '',
            'itemUnitPrice' => $unitPrice,
            'serialNo' => $serialNos[$key] ?? '',
            'model' => $models[$key] ?? '',
            'brand' => $brands[$key] ?? ''
          ];
          $this->DeliveryModel->add_delivery_item($itemData);
        }
      }
    }

    // Update delivery with calculated totals
    $this->DeliveryModel->update_delivery_totals($deliveryID, $totalAmount);

    // Create corresponding invoice record for this delivery
    $settingsID = $this->session->userdata('settingsID');
    $this->load->model('CashModel');

    // Generate invoice number if not provided
    $invoiceNumber = !empty($invoiceNo) ? $invoiceNo : 'INV-DEL-' . $deliveryNo;

    $invoiceData = [
      'InvoiceNo' => $invoiceNumber,
      'CustID' => !empty($customerID) ? $customerID : '',
      'TransDate' => date('Y-m-d'),
      'DueDate' => date('Y-m-d', strtotime('+30 days')),
      'TotalDue' => $totalAmount,
      'AmountPaid' => 0,
      'Balance' => $totalAmount,
      'invoiceStat' => 'active',
      'invoiceSource' => 'Delivery',
      'orderID' => NULL,
      'JobDescription' => 'Delivery: ' . $deliveryNo . ' - ' . $notes,
      'InvoiceBy' => $this->session->userdata('username'),
      'settingsID' => $settingsID
    ];

    // Insert invoice record
    $this->db->insert('invoice', $invoiceData);
    $invoiceOrderID = $this->db->insert_id();

    // Create invoice items from delivery items
    $this->_ensureInvoiceItemsTable();
    if (!empty($itemDescriptions)) {
      foreach ($itemDescriptions as $key => $desc) {
        if (!empty($desc)) {
          $quantity = $itemQuantities[$key] ?? 1;
          $unitPrice = $itemUnitPrices[$key] ?? 0;
          $lineTotal = $quantity * $unitPrice;

          $invoiceItemData = [
            'orderID' => $invoiceOrderID,
            'settingsID' => $settingsID,
            'lineNo' => $key + 1,
            'itemDescription' => $desc,
            'itemQuantity' => $quantity,
            'itemDurationUnit' => $itemUnits[$key] ?? 'pcs',
            'itemUnitPrice' => $unitPrice,
            'lineTotal' => $lineTotal
          ];
          $this->db->insert('invoice_items', $invoiceItemData);
        }
      }
    }

    // Update delivery with invoice number and orderID reference
    $this->db->where('deliveryID', $deliveryID);
    $this->db->update('customer_deliveries', [
      'invoiceNo' => $invoiceNumber,
      'orderID' => $invoiceOrderID
    ]);

    $this->session->set_flashdata('success', 'Delivery created successfully with invoice ' . $invoiceNumber);
    redirect('Page/customerDeliveryList');
  }

  function viewCustomerDelivery()
  {
    if (!in_array($this->session->userdata('level'), ['Admin', 'Account', 'Staff', 'Encoder'])) {
      echo "Access Denied";
      return;
    }

    $deliveryNo = $this->input->get('deliveryNo');
    $customerName = $this->input->get('customer');
    $settingsID = $this->session->userdata('settingsID');
    $userLevel = $this->session->userdata('level');
    $username = $this->session->userdata('username');

    $this->load->model('DeliveryModel');

    // Get the delivery information
    $result['delivery'] = $this->DeliveryModel->get_grouped_delivery($deliveryNo, $customerName, $settingsID);

    if (!$result['delivery']) {
      show_404();
      return;
    }

    // For Staff users, check if they encoded this delivery
    if (in_array($userLevel, array('Staff', 'Encoder'), true) && $result['delivery']->deliveredBy !== $username) {
      echo "Access Denied - You can only view deliveries you encoded";
      return;
    }

    // Get all deliveries in this group (for detailed view)
    $result['items'] = $this->DeliveryModel->get_deliveries_by_group($deliveryNo, $customerName, $settingsID);

    $this->load->view('view_customer_delivery', $result);
  }

  function updateCustomerDeliveryStatus()
  {
    if (!in_array($this->session->userdata('level'), ['Admin', 'Account', 'Staff', 'Encoder'])) {
      echo "Access Denied";
      return;
    }

    $deliveryID = $this->input->post('deliveryID');
    $deliveryStatus = $this->input->post('deliveryStatus');
    $settingsID = $this->session->userdata('settingsID');
    $this->load->model('DeliveryModel');

    $this->DeliveryModel->update_delivery_status($deliveryID, $deliveryStatus);

    $this->session->set_flashdata('success', 'Delivery status updated successfully');
    redirect('Page/customerDeliveryList');
  }

  function deleteCustomerDelivery()
  {
    if (!in_array($this->session->userdata('level'), ['Admin', 'Account', 'Staff', 'Encoder'])) {
      echo "Access Denied";
      return;
    }

    $deliveryNo = $this->input->get('deliveryNo');
    $customerName = $this->input->get('customer');
    $settingsID = $this->session->userdata('settingsID');

    // Auto-create customer delivery tables if they don't exist
    $this->_ensureCustomerDeliveryTables();

    $this->load->model('DeliveryModel');

    // Check if delivery can be deleted (business logic validation)
    $deliveryInfo = $this->DeliveryModel->get_grouped_delivery_info($deliveryNo, $customerName, $settingsID);

    if (!$deliveryInfo) {
      $this->session->set_flashdata('danger', 'Delivery not found');
      redirect('Page/customerDeliveryList');
      return;
    }

    // Check if delivery is already delivered
    if ($deliveryInfo->deliveryStatus === 'delivered') {
      $this->session->set_flashdata('danger', 'Cannot delete delivery that is already delivered');
      redirect('Page/customerDeliveryList');
      return;
    }

    // Delete all deliveries with the same deliveryNo and customer
    $this->DeliveryModel->delete_grouped_delivery($deliveryNo, $customerName, $settingsID);

    $this->session->set_flashdata('success', 'Delivery deleted successfully');
    redirect('Page/customerDeliveryList');
  }

  function getUserName($username)
  {
    $this->db->select('CONCAT(fName, " ", lName) as fullName');
    $this->db->from('users');
    $this->db->where('username', $username);
    $this->db->limit(1);

    $result = $this->db->get()->row();
    return $result ? $result->fullName : $username;
  }

  function getNextDeliveryNo()
  {
    $this->load->model('DeliveryModel');
    $settingsID = $this->session->userdata('settingsID');

    // Get the next delivery number
    $nextDeliveryNo = $this->DeliveryModel->get_next_delivery_no($settingsID);

    header('Content-Type: application/json');
    echo json_encode([
      'success' => true,
      'deliveryNo' => $nextDeliveryNo
    ]);
  }

  function getNextInvoiceNo()
  {
    $this->load->model('CashModel');
    $settingsID = $this->session->userdata('settingsID');

    // Get the next invoice number
    $nextInvoiceNo = $this->CashModel->get_next_invoice_no($settingsID);

    header('Content-Type: application/json');
    echo json_encode([
      'success' => true,
      'invoiceNo' => $nextInvoiceNo
    ]);
  }

  function printDeliveryReceipt()
  {
    if (!in_array($this->session->userdata('level'), ['Admin', 'Account', 'Staff', 'Encoder'])) {
      echo "Access Denied";
      return;
    }

    $deliveryNo = $this->input->get('deliveryNo');
    $customerName = $this->input->get('customer');
    $settingsID = $this->session->userdata('settingsID');

    // Auto-create customer delivery tables if they don't exist
    $this->_ensureCustomerDeliveryTables();

    $this->load->model('DeliveryModel');

    // Get grouped delivery information
    $result['delivery'] = $this->DeliveryModel->get_grouped_delivery($deliveryNo, $customerName, $settingsID);

    // Get all individual deliveries for this group to get items
    $result['deliveries'] = $this->DeliveryModel->get_deliveries_by_group($deliveryNo, $customerName, $settingsID);

    // Get company info from pos_settings filtered by settingsID
    $this->load->model('SettingsModel');
    $result['company'] = $this->SettingsModel->getPosSettings($settingsID);

    // Get user information for delivered by
    $username = $this->session->userdata('username');
    $result['deliveredByName'] = $this->getUserName($username);

    $this->load->view('print_delivery_receipt', $result);
  }

  function updateDeliveryStatus()
  {
    if (!in_array($this->session->userdata('level'), ['Admin', 'Account', 'Staff', 'Encoder'])) {
      echo "Access Denied";
      return;
    }

    $deliveryNo = $this->input->get('deliveryNo');
    $customerName = $this->input->get('customer');
    $deliveryStatus = $this->input->get('status');
    $settingsID = $this->session->userdata('settingsID');

    $this->load->model('DeliveryModel');

    // Update all deliveries with the same deliveryNo and customerName
    $this->DeliveryModel->update_grouped_delivery_status($deliveryNo, $customerName, $deliveryStatus, $settingsID);

    $this->session->set_flashdata('success', 'Delivery status updated successfully');
    redirect('Page/customerDeliveryList');
  }

  function deliveryPayment()
  {
    if (!in_array($this->session->userdata('level'), ['Admin', 'Account', 'Staff', 'Encoder'])) {
      echo "Access Denied";
      return;
    }

    $deliveryNo = $this->input->get('deliveryNo');
    $customerName = $this->input->get('customer');
    $settingsID = $this->session->userdata('settingsID');

    // Auto-create customer delivery tables if they don't exist
    $this->_ensureCustomerDeliveryTables();

    $this->load->model('DeliveryModel');

    $result['delivery'] = $this->DeliveryModel->get_grouped_delivery($deliveryNo, $customerName, $settingsID);
    $result['data2'] = $this->CashModel->getClients($settingsID);

    // Get payment history for this delivery
    $result['paymentHistory'] = $this->CashModel->paymentHistory($deliveryNo, $settingsID);

    $this->load->view('delivery_payment', $result);
  }

  function saveDeliveryPayment()
  {
    if (!in_array($this->session->userdata('level'), ['Admin', 'Account', 'Staff', 'Encoder'])) {
      echo "Access Denied";
      return;
    }

    $deliveryNo = $this->input->post('deliveryNo');
    $customerName = $this->input->post('customerName');
    $amountPaid = $this->input->post('paymentAmount');
    $taxAmount = $this->input->post('taxAmount');
    $paymentDate = $this->input->post('paymentDate');
    $paymentMode = $this->input->post('paymentMode');
    $referenceNo = $this->input->post('referenceNo');
    $paymentNotes = $this->input->post('paymentNotes');
    $settingsID = $this->session->userdata('settingsID');

    // Auto-create customer delivery tables if they don't exist
    $this->_ensureCustomerDeliveryTables();

    $this->load->model('DeliveryModel');

    // Handle file upload if tax amount > 0
    $attachmentPath = null;
    if ($taxAmount > 0) {
      if (isset($_FILES['birAttachment']) && $_FILES['birAttachment']['error'] == 0) {
        $config['upload_path'] = './uploads/bir_attachments/';
        $config['allowed_types'] = 'pdf|jpg|jpeg|png';
        $config['max_size'] = 5120; // 5MB
        $config['file_name'] = 'BIR_' . $deliveryNo . '_' . time() . '_' . rand(1000, 9999);

        if (!is_dir($config['upload_path'])) {
          mkdir($config['upload_path'], 0755, true);
        }

        $this->load->library('upload', $config);

        if (!$this->upload->do_upload('birAttachment')) {
          $this->session->set_flashdata('danger', 'File upload failed: ' . $this->upload->display_errors());
          redirect('Page/deliveryPayment?deliveryNo=' . urlencode($deliveryNo) . '&customer=' . urlencode($customerName));
          return;
        }

        $uploadData = $this->upload->data();
        $attachmentPath = $config['upload_path'] . $uploadData['file_name'];
      } else {
        $this->session->set_flashdata('danger', 'BIR Form 2307 attachment is required when tax amount is greater than 0');
        redirect('Page/deliveryPayment?deliveryNo=' . urlencode($deliveryNo) . '&customer=' . urlencode($customerName));
        return;
      }
    }

    // Create payment record in main payments table for accounting reports
    if ($amountPaid > 0 || $taxAmount > 0) {
      $paymentData = [
        'InvoiceNo' => $deliveryNo,
        'Customer' => $customerName,
        'AmountPaid' => $amountPaid,
        'TaxAmount' => $taxAmount,
        'PDate' => $paymentDate,
        'PaymentReference' => $referenceNo,
        'PaymentSource' => $paymentMode, // Use PaymentSource for payment mode
        'ORStat' => 'Valid',
        'Cashier' => $this->session->userdata('username'),
        'settingsID' => $settingsID,
        'TransDescription' => 'Payment for delivery ' . $deliveryNo,
        'paymentID' => 'DEL_' . $deliveryNo . '_' . date('YmdHis')
      ];

      $this->db->insert('payments', $paymentData);

      // If there's an attachment, we could store it in a separate table or as a note
      if ($attachmentPath) {
        // For now, we'll add attachment info to the description
        $this->db->where('paymentID', 'DEL_' . $deliveryNo . '_' . date('YmdHis'));
        $this->db->update('payments', [
          'TransDescription' => 'Payment for delivery ' . $deliveryNo . ' (BIR Form attached: ' . basename($attachmentPath) . ')'
        ]);
      }
    }

    // Update payment for all deliveries with the same deliveryNo and customerName
    $totalPaymentAmount = (float)$amountPaid + (float)$taxAmount;
    $this->DeliveryModel->update_grouped_delivery_payment($deliveryNo, $customerName, $totalPaymentAmount, $settingsID);

    $this->session->set_flashdata('success', 'Payment added successfully');
    redirect('Page/customerDeliveryList');
  }

  function unifiedPayment()
  {
    $level = strtolower(trim((string) $this->session->userdata('level')));
    if (!in_array($level, array('admin', 'staff'), true)) {
      echo "Access Denied";
      return;
    }

    $settingsID = $this->session->userdata('settingsID');

    // Auto-create customer delivery tables if they don't exist
    $this->_ensureCustomerDeliveryTables();

    // Get all unpaid invoices from all sources
    $invoices = $this->_searchInvoices('', $settingsID);
    $jobOrders = $this->_searchJobOrders('', $settingsID);
    $deliveries = $this->_searchDeliveries('', $settingsID);

    // Debug: Log results
    error_log('Unified Payment Debug - Invoices: ' . count($invoices) . ', Job Orders: ' . count($jobOrders) . ', Deliveries: ' . count($deliveries));

    // Debug: Check if invoice table exists and has data
    if ($this->db->table_exists('invoice')) {
      $totalInvoices = $this->db->count_all('invoice');
      error_log('Total invoices in database: ' . $totalInvoices);

      // Check unpaid specifically
      $this->db->where('settingsID', $settingsID);
      $this->db->where('Balance >', 0);
      $unpaidCount = $this->db->count_all_results('invoice');
      error_log('Unpaid invoices for settingsID ' . $settingsID . ': ' . $unpaidCount);
    }

    $result['invoices'] = $invoices;
    $result['jobOrders'] = $jobOrders;
    $result['deliveries'] = $deliveries;

    $this->load->view('unified_payment', $result);
  }

  function searchPaymentDocuments()
  {
    if ($this->session->userdata('level') !== 'Admin') {
      echo json_encode([]);
      return;
    }

    $type = $this->input->post('type');
    $search = $this->input->post('search');
    $settingsID = $this->session->userdata('settingsID');

    $results = array();

    try {
      switch ($type) {
        case 'invoice':
          $results = $this->_searchInvoices($search, $settingsID);
          break;
        case 'job-order':
          $results = $this->_searchJobOrders($search, $settingsID);
          break;
        case 'delivery':
          $results = $this->_searchDeliveries($search, $settingsID);
          break;
        default:
          $results = array();
      }
    } catch (Exception $e) {
      // Log error and return empty results
      error_log('Search error: ' . $e->getMessage());
      $results = array();
    }

    echo json_encode($results);
  }

  function saveUnifiedPayment()
  {
    if ($this->session->userdata('level') !== 'Admin') {
      echo "Access Denied";
      return;
    }

    $paymentType = $this->input->post('paymentType');
    $referenceId = $this->input->post('referenceId');
    $referenceNumber = $this->input->post('referenceNumber');
    $amountPaid = $this->input->post('paymentAmount');
    $taxAmount = $this->input->post('taxAmount');
    $paymentDate = $this->input->post('paymentDate');
    $paymentMode = $this->input->post('paymentMode');
    $referenceNo = $this->input->post('referenceNo');
    $paymentNotes = $this->input->post('paymentNotes');
    $settingsID = $this->session->userdata('settingsID');

    // Handle file upload if tax amount > 0
    $attachmentPath = null;
    if ($taxAmount > 0) {
      if (isset($_FILES['birAttachment']) && $_FILES['birAttachment']['error'] == 0) {
        $config['upload_path'] = './uploads/bir_attachments/';
        $config['allowed_types'] = 'pdf|jpg|jpeg|png';
        $config['max_size'] = 5120; // 5MB
        $config['file_name'] = 'BIR_' . $referenceNumber . '_' . time() . '_' . rand(1000, 9999);

        if (!is_dir($config['upload_path'])) {
          mkdir($config['upload_path'], 0755, true);
        }

        $this->load->library('upload', $config);

        if (!$this->upload->do_upload('birAttachment')) {
          $this->session->set_flashdata('danger', 'File upload failed: ' . $this->upload->display_errors());
          redirect('Page/unifiedPayment');
          return;
        }

        $uploadData = $this->upload->data();
        $attachmentPath = $config['upload_path'] . $uploadData['file_name'];
      } else {
        $this->session->set_flashdata('danger', 'BIR Form 2307 attachment is required when tax amount is greater than 0');
        redirect('Page/unifiedPayment');
        return;
      }
    }

    // Create payment record in main payments table
    if ($amountPaid > 0 || $taxAmount > 0) {
      $paymentData = [
        'InvoiceNo' => $referenceNumber,
        'AmountPaid' => $amountPaid,
        'TaxAmount' => $taxAmount,
        'PDate' => $paymentDate,
        'PaymentReference' => $referenceNo,
        'PaymentSource' => $paymentMode,
        'ORStat' => 'Valid',
        'Cashier' => $this->session->userdata('username'),
        'settingsID' => $settingsID,
        'TransDescription' => 'Payment for ' . $paymentType . ' ' . $referenceNumber,
        'paymentID' => strtoupper(substr($paymentType, 0, 3)) . '_' . $referenceNumber . '_' . date('YmdHis')
      ];

      $this->db->insert('payments', $paymentData);

      // If there's an attachment, add info to description
      if ($attachmentPath) {
        $this->db->where('paymentID', $paymentData['paymentID']);
        $this->db->update('payments', [
          'TransDescription' => 'Payment for ' . $paymentType . ' ' . $referenceNumber . ' (BIR Form attached: ' . basename($attachmentPath) . ')'
        ]);
      }
    }

    // Update the specific document based on type
    switch ($paymentType) {
      case 'invoice':
        $this->_updateInvoicePayment($referenceId, $amountPaid, $taxAmount, $settingsID);
        break;
      case 'job-order':
        $this->_updateJobOrderPayment($referenceId, $amountPaid, $taxAmount, $settingsID);
        break;
      case 'delivery':
        $this->_updateDeliveryPayment($referenceId, $referenceNumber, $amountPaid, $settingsID);
        break;
    }

    $this->session->set_flashdata('success', 'Payment added successfully');
    redirect('Page/paymentList');
  }

  private function _searchInvoices($search, $settingsID)
  {
    try {
      $this->db->select('i.TransDate as date, i.InvoiceNo as number, i.Customer as customer, i.TotalDue as amount, i.Balance as remainingBalance, i.AmountPaid as amountPaid, i.invoiceStat as status');
      $this->db->from('invoice i');
      $this->db->where('i.settingsID', $settingsID);
      $this->db->where('i.invoiceStat !=', 'Voided');
      $this->db->where('i.Balance >', 0); // Only unpaid invoices

      if (!empty($search)) {
        $this->db->group_start();
        $this->db->like('i.InvoiceNo', $search);
        $this->db->or_like('i.Customer', $search);
        $this->db->group_end();
      }

      $this->db->order_by('i.TransDate', 'DESC');
      $this->db->limit(20);

      $query = $this->db->get();

      if (!$query) {
        error_log('Invoice search query failed');
        return array();
      }

      $results = $query->result();
      error_log('Invoice search results count: ' . count($results));

      foreach ($results as $result) {
        $result->id = $result->number;
        $result->status = ucfirst($result->status);
        error_log('Invoice found: ' . $result->number . ' - Balance: ' . $result->remainingBalance);
      }

      return $results;
    } catch (Exception $e) {
      error_log('Invoice search error: ' . $e->getMessage());
      return array();
    }
  }

  private function _searchJobOrders($search, $settingsID)
  {
    try {
      // Check if joborder table exists before querying
      if (!$this->db->table_exists('joborder')) {
        return array();
      }

      $this->db->select('j.TransDate as date, j.InvoiceNo as number, j.Customer as customer, j.TotalDue as amount, j.Balance as remainingBalance, j.AmountPaid as amountPaid, j.invoiceStat as status');
      $this->db->from('joborder j');
      $this->db->where('j.settingsID', $settingsID);
      $this->db->where('j.invoiceStat !=', 'Voided');
      $this->db->where('j.Balance >', 0); // Only unpaid job orders

      if (!empty($search)) {
        $this->db->group_start();
        $this->db->like('j.InvoiceNo', $search);
        $this->db->or_like('j.Customer', $search);
        $this->db->group_end();
      }

      $this->db->order_by('j.TransDate', 'DESC');
      $this->db->limit(20);

      $query = $this->db->get();

      if (!$query) {
        return array();
      }

      $results = $query->result();

      foreach ($results as $result) {
        $result->id = $result->number;
        $result->status = ucfirst($result->status);
      }

      return $results;
    } catch (Exception $e) {
      error_log('Job order search error: ' . $e->getMessage());
      return array();
    }
  }

  private function _searchDeliveries($search, $settingsID)
  {
    try {
      // Auto-create customer delivery tables if they don't exist
      $this->_ensureCustomerDeliveryTables();

      // Check if table exists before querying
      if (!$this->db->table_exists('customer_deliveries')) {
        return array();
      }

      $this->db->select('d.deliveryNo as number, d.customerName as customer, d.balance as remainingBalance, d.amountPaid as amountPaid, d.totalAmount as amount, d.deliveryStatus as status, d.deliveryDate as date');
      $this->db->from('customer_deliveries d');
      $this->db->where('d.settingsID', $settingsID);
      $this->db->where('d.balance >', 0); // Only unpaid deliveries

      if (!empty($search)) {
        $this->db->group_start();
        $this->db->like('d.deliveryNo', $search);
        $this->db->or_like('d.customerName', $search);
        $this->db->group_end();
      }

      $this->db->group_by('d.deliveryNo, d.customerName');
      $this->db->order_by('d.deliveryDate', 'DESC');
      $this->db->limit(20);

      $query = $this->db->get();

      if (!$query) {
        return array();
      }

      $results = $query->result();

      foreach ($results as $result) {
        $result->id = $result->number;
        $result->status = ucfirst($result->status);
      }

      return $results;
    } catch (Exception $e) {
      error_log('Delivery search error: ' . $e->getMessage());
      return array();
    }
  }

  private function _updateInvoicePayment($invoiceId, $amountPaid, $taxAmount, $settingsID)
  {
    // Get current invoice
    $this->db->where('InvoiceNo', $invoiceId);
    $this->db->where('settingsID', $settingsID);
    $invoice = $this->db->get('invoice')->row();

    if ($invoice) {
      $newAmountPaid = $invoice->AmountPaid + $amountPaid;
      $newBalance = $invoice->TotalDue - $newAmountPaid;

      $status = 'Pending';
      if ($newBalance <= 0) {
        $status = 'Paid';
      } elseif ($newAmountPaid > 0) {
        $status = 'Partial';
      }

      $this->db->where('InvoiceNo', $invoiceId);
      $this->db->where('settingsID', $settingsID);
      $this->db->update('invoice', [
        'AmountPaid' => $newAmountPaid,
        'Balance' => max(0, $newBalance),
        'invoiceStat' => $status
      ]);
    }
  }

  private function _updateJobOrderPayment($jobOrderId, $amountPaid, $taxAmount, $settingsID)
  {
    // Get current job order
    $this->db->where('InvoiceNo', $jobOrderId);
    $this->db->where('settingsID', $settingsID);
    $jobOrder = $this->db->get('joborder')->row();

    if ($jobOrder) {
      $newAmountPaid = $jobOrder->AmountPaid + $amountPaid;
      $newBalance = $jobOrder->TotalDue - $newAmountPaid;

      $status = 'Pending';
      if ($newBalance <= 0) {
        $status = 'Paid';
      } elseif ($newAmountPaid > 0) {
        $status = 'Partial';
      }

      $this->db->where('InvoiceNo', $jobOrderId);
      $this->db->where('settingsID', $settingsID);
      $this->db->update('joborder', [
        'AmountPaid' => $newAmountPaid,
        'Balance' => max(0, $newBalance),
        'invoiceStat' => $status
      ]);
    }
  }

  private function _updateDeliveryPayment($deliveryId, $deliveryNo, $amountPaid, $settingsID)
  {
    $this->load->model('DeliveryModel');
    $this->DeliveryModel->update_grouped_delivery_payment($deliveryNo, '', $amountPaid, $settingsID);
  }

  function editCustomerDelivery()
  {
    if (!in_array($this->session->userdata('level'), ['Admin', 'Account', 'Staff', 'Encoder'])) {
      echo "Access Denied";
      return;
    }

    $deliveryID = $this->input->get('id');
    $settingsID = $this->session->userdata('settingsID');

    // Auto-create customer delivery tables if they don't exist
    $this->_ensureCustomerDeliveryTables();

    $this->load->model('DeliveryModel');

    $result['delivery'] = $this->DeliveryModel->get_delivery($deliveryID, $settingsID);
    $result['items'] = $this->DeliveryModel->get_delivery_items($deliveryID, $settingsID);
    $result['data2'] = $this->CashModel->getClients($settingsID);
    $this->load->view('edit_customer_delivery', $result);
  }

  function updateCustomerDelivery()
  {
    if (!in_array($this->session->userdata('level'), ['Admin', 'Account', 'Staff', 'Encoder'])) {
      echo "Access Denied";
      return;
    }

    // Auto-create customer delivery tables if they don't exist
    $this->_ensureCustomerDeliveryTables();

    $this->load->model('DeliveryModel');

    $deliveryID = $this->input->post('deliveryID');
    $customerID = $this->input->post('customerID');
    $invoiceNo = $this->input->post('invoiceNo');
    $notes = $this->input->post('notes');

    $deliveryData = [
      'invoiceNo' => !empty($invoiceNo) ? $invoiceNo : NULL,
      'customerID' => !empty($customerID) ? $customerID : NULL,
      'customerName' => $this->input->post('customerName'),
      'customerAddress' => $this->input->post('customerAddress'),
      'deliveryDate' => $this->input->post('deliveryDate'),
      'receivedBy' => $this->input->post('receivedBy'),
      'notes' => !empty($notes) ? $notes : NULL,
      'updatedAt' => date('Y-m-d H:i:s')
    ];

    // Update delivery
    $this->DeliveryModel->update_delivery($deliveryID, $deliveryData);

    // Get existing items to remove
    $existingItems = $this->DeliveryModel->get_delivery_items($deliveryID, $this->session->userdata('settingsID'));
    $existingItemIDs = array();
    foreach ($existingItems as $item) {
      $existingItemIDs[] = $item->itemID;
    }

    // Process items
    $itemIDs = $this->input->post('itemID');
    $itemDescriptions = $this->input->post('itemDescription');
    $itemQuantities = $this->input->post('itemQuantity');
    $itemUnits = $this->input->post('itemUnit');
    $itemUnitPrices = $this->input->post('itemUnitPrice');
    $serialNos = $this->input->post('serialNo');
    $models = $this->input->post('model');
    $brands = $this->input->post('brand');

    $totalAmount = 0;
    $processedItemIDs = array();

    if (!empty($itemDescriptions)) {
      foreach ($itemDescriptions as $key => $desc) {
        if (!empty($desc)) {
          $quantity = $itemQuantities[$key] ?? 1;
          $unitPrice = $itemUnitPrices[$key] ?? 0;
          $lineTotal = $quantity * $unitPrice;
          $totalAmount += $lineTotal;

          $itemData = [
            'deliveryID' => $deliveryID,
            'lineNo' => $key + 1,
            'itemDescription' => $desc,
            'itemQuantity' => $quantity,
            'itemUnit' => $itemUnits[$key] ?? '',
            'itemUnitPrice' => $unitPrice,
            'serialNo' => $serialNos[$key] ?? '',
            'model' => $models[$key] ?? '',
            'brand' => $brands[$key] ?? ''
          ];

          $itemID = $itemIDs[$key] ?? null;

          if ($itemID && in_array($itemID, $existingItemIDs)) {
            // Update existing item
            $this->DeliveryModel->update_delivery_item($itemID, $itemData);
            $processedItemIDs[] = $itemID;
          } else {
            // Add new item
            $this->DeliveryModel->add_delivery_item($itemData);
          }
        }
      }
    }

    // Remove items that were deleted
    $itemsToRemove = array_diff($existingItemIDs, $processedItemIDs);
    foreach ($itemsToRemove as $itemID) {
      $this->DeliveryModel->delete_delivery_item($itemID);
    }

    // Update delivery with calculated totals
    $this->DeliveryModel->update_delivery_totals($deliveryID, $totalAmount);

    $this->session->set_flashdata('success', 'Delivery updated successfully');
    redirect('Page/customerDeliveryList');
  }

  private function _ensureCustomerDeliveryTables()
  {
    // Check and create customer_deliveries table
    if (!$this->db->table_exists('customer_deliveries')) {
      $this->db->query("
        CREATE TABLE `customer_deliveries` (
          `deliveryID` int unsigned NOT NULL AUTO_INCREMENT,
          `deliveryNo` varchar(50) NOT NULL,
          `invoiceNo` varchar(50) DEFAULT NULL,
          `customerID` int DEFAULT NULL,
          `customerName` varchar(255) NOT NULL,
          `deliveryDate` date NOT NULL,
          `deliveryStatus` enum('Pending','In Transit','Delivered','Cancelled') NOT NULL DEFAULT 'Pending',
          `totalAmount` decimal(15,2) NOT NULL DEFAULT 0.00,
          `amountPaid` decimal(15,2) NOT NULL DEFAULT 0.00,
          `balance` decimal(15,2) NOT NULL DEFAULT 0.00,
          `deliveryPerson` varchar(100) DEFAULT NULL,
          `receivedBy` varchar(100) DEFAULT NULL,
          `notes` text,
          `settingsID` int unsigned NOT NULL,
          `createdAt` datetime DEFAULT CURRENT_TIMESTAMP,
          `updatedAt` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
          PRIMARY KEY (`deliveryID`),
          KEY `idx_delivery_settings` (`settingsID`),
          KEY `idx_delivery_customer` (`customerID`),
          KEY `idx_delivery_no` (`deliveryNo`),
          KEY `idx_delivery_date` (`deliveryDate`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
      ");
    }

    // Add missing columns if they don't exist
    if ($this->db->table_exists('customer_deliveries')) {
      $columns = $this->db->field_data('customer_deliveries');
      $column_names = array_column($columns, 'name');

      if (!in_array('receivedBy', $column_names)) {
        $this->db->query("ALTER TABLE customer_deliveries ADD COLUMN receivedBy VARCHAR(100) AFTER deliveryPerson");
      }

      if (!in_array('deliveredBy', $column_names)) {
        $this->db->query("ALTER TABLE customer_deliveries ADD COLUMN deliveredBy VARCHAR(100) AFTER receivedBy");
      }
    }
  }

  private function _ensureCustomerDeliveryItemsTable()
  {
    if (!$this->db->table_exists('customer_delivery_items')) {
      $this->db->query("
        CREATE TABLE `customer_delivery_items` (
          deliveryID INT NOT NULL,
          lineNo INT NOT NULL DEFAULT 1,
          itemDescription TEXT,
          itemQuantity INT NOT NULL DEFAULT 1,
          itemUnit VARCHAR(50),
          itemUnitPrice DECIMAL(10,2) DEFAULT 0.00,
          serialNo VARCHAR(100),
          model VARCHAR(100),
          brand VARCHAR(100),
          productID INT,
          productName VARCHAR(255),
          productDescription TEXT,
          quantity INT NOT NULL DEFAULT 1,
          unitPrice DECIMAL(10,2) DEFAULT 0.00,
          totalPrice DECIMAL(10,2) DEFAULT 0.00,
          weight DECIMAL(8,2),
          dimensions VARCHAR(50),
          specialInstructions TEXT,
          createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
          settingsID INT NOT NULL,
          INDEX idx_settingsID (settingsID),
          INDEX idx_deliveryID (deliveryID),
          INDEX idx_productID (productID),
          FOREIGN KEY (deliveryID) REFERENCES customer_deliveries(deliveryID) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
      ");
    } else {
      // Check if missing columns exist and add them
      $columns = $this->db->field_data('customer_delivery_items');
      $column_names = array_column($columns, 'name');

      $missing_columns = [
        'lineNo' => "ADD COLUMN lineNo INT NOT NULL DEFAULT 1 AFTER deliveryID",
        'itemDescription' => "ADD COLUMN itemDescription TEXT AFTER lineNo",
        'itemQuantity' => "ADD COLUMN itemQuantity INT NOT NULL DEFAULT 1 AFTER itemDescription",
        'itemUnit' => "ADD COLUMN itemUnit VARCHAR(50) AFTER itemQuantity",
        'itemUnitPrice' => "ADD COLUMN itemUnitPrice DECIMAL(10,2) DEFAULT 0.00 AFTER itemUnit",
        'serialNo' => "ADD COLUMN serialNo VARCHAR(100) AFTER itemUnitPrice",
        'model' => "ADD COLUMN model VARCHAR(100) AFTER serialNo",
        'brand' => "ADD COLUMN brand VARCHAR(100) AFTER model"
      ];

      foreach ($missing_columns as $column => $alter_sql) {
        if (!in_array($column, $column_names)) {
          $this->db->query("ALTER TABLE customer_delivery_items $alter_sql");
        }
      }
    }
  }

  private function _syncDeliveryPayments($settingsID)
  {
    // Temporarily disabled to prevent incorrect payment application
    // The payment sync was causing new deliveries to show payments from other customers
    // with similar invoice numbers. This needs to be redesigned to properly match
    // payments to deliveries without cross-contamination.
    return;

    // Original sync logic commented out for now
    /*
    // Get all delivery payments from payments table with customer info
    $this->db->select('p.InvoiceNo, p.Customer, SUM(p.AmountPaid + COALESCE(p.TaxAmount, 0)) as totalPaid, COUNT(*) as paymentCount');
    $this->db->from('payments p');
    $this->db->where('p.settingsID', $settingsID);
    $this->db->where('p.ORStat', 'Valid');
    $this->db->like('p.InvoiceNo', 'DEL'); // Only delivery payments
    $this->db->where('p.Customer IS NOT NULL');
    $this->db->where('p.Customer !=', '');
    $this->db->group_by(['p.InvoiceNo', 'p.Customer']);
    $deliveryPayments = $this->db->get()->result();
    
    foreach ($deliveryPayments as $payment) {
      // First, check if there's an exact match for both deliveryNo and customerName
      $this->db->where('deliveryNo', $payment->InvoiceNo);
      $this->db->where('customerName', $payment->Customer);
      $this->db->where('settingsID', $settingsID);
      
      $delivery = $this->db->get('customer_deliveries')->row();
      if ($delivery) {
        // Additional validation: only apply payments if the delivery amount matches or is reasonable
        // This prevents applying large payments to small deliveries or vice versa
        if ($payment->totalPaid <= ($delivery->totalAmount * 1.1)) { // Allow 10% tolerance
          $newBalance = $delivery->totalAmount - $payment->totalPaid;
          
          $paymentStatus = 'unpaid';
          if ($newBalance <= 0) {
            $paymentStatus = 'paid';
          } elseif ($payment->totalPaid > 0) {
            $paymentStatus = 'partial';
          }
          
          $this->db->update('customer_deliveries', [
            'amountPaid' => $payment->totalPaid,
            'balance' => max(0, $newBalance),
            'paymentStatus' => $paymentStatus,
            'updatedAt' => date('Y-m-d H:i:s')
          ]);
        }
      }
    }
    */
  }

  // Knowledge Base and FAQ Methods
  function knowledgeBase()
  {
    $level = strtolower(trim((string) $this->session->userdata('level')));
    $settingsID = $this->session->userdata('settingsID');
    $userID = $this->session->userdata('user_id');

    if (!in_array($level, ['admin', 'manager', 'encoder', 'staff', 'cashier', 'pos admin', 'pos staff', 'client'])) {
      echo "Access Denied";
      return;
    }

    $type = trim((string) ($this->input->get('type') ?? ''));
    $type = $type !== '' ? $type : null;
    $category = trim((string) ($this->input->get('category') ?? ''));
    $category = $category !== '' ? $category : null;

    if ($level === 'client') {
      // Clients can only see published articles
      $data['articles'] = $this->Knowledge_base_model->get_all($settingsID, $type, 'published', $category);
      $data['categories'] = $this->Knowledge_base_model->get_categories($settingsID, 'published', $type);
      $data['is_client'] = true;
      $this->load->view('knowledge_base_client', $data);
    } else {
      // Staff can see all their own articles and all published articles
      $myArticles = $this->Knowledge_base_model->get_by_user($settingsID, $userID, $type, $category);
      $publishedArticles = $this->Knowledge_base_model->get_all($settingsID, $type, 'published', $category);
      $data['my_articles'] = $myArticles;
      $data['published_articles'] = $publishedArticles;
      $data['categories'] = $this->Knowledge_base_model->get_categories($settingsID);
      $data['is_client'] = false;
      $this->load->view('knowledge_base', $data);
    }
  }

  function knowledgeBaseCreate()
  {
    $level = strtolower(trim((string) $this->session->userdata('level')));
    $settingsID = $this->session->userdata('settingsID');
    $userID = $this->session->userdata('user_id');
    $userName = trim(($this->session->userdata('fName') ?? '') . ' ' . ($this->session->userdata('lName') ?? ''));

    if (!in_array($level, ['admin', 'manager', 'encoder', 'staff', 'cashier', 'pos admin', 'pos staff'])) {
      echo "Access Denied";
      return;
    }

    if ($this->input->post()) {
      if (empty($userID)) {
        $this->session->set_flashdata('kb_error', 'User session not found. Please login again.');
        redirect('Page/knowledgeBase');
        return;
      }

      $categoryName = $this->_normalizeKnowledgeBaseCategory($this->input->post('category'), $this->input->post('newCategory'));
      $attachmentUpload = $this->_handleKnowledgeBaseAttachmentUpload('attachment');

      if ($attachmentUpload['error'] !== '') {
        $data['categories'] = $this->Knowledge_base_model->get_categories($settingsID);
        $data['kb_error'] = $attachmentUpload['error'];
        $data['form_values'] = array(
          'title' => $this->input->post('title'),
          'content' => $this->input->post('content'),
          'category' => $this->input->post('category'),
          'newCategory' => $this->input->post('newCategory'),
          'type' => $this->input->post('type'),
          'status' => $this->input->post('status'),
        );
        $this->load->view('knowledge_base_create', $data);
        return;
      }

      $data = array(
        'settingsID' => $settingsID,
        'title' => $this->input->post('title'),
        'content' => $this->input->post('content'),
        'category' => $categoryName !== '' ? $categoryName : null,
        'type' => $this->input->post('type'),
        'created_by' => $userID,
        'created_by_name' => $userName,
        'status' => $this->input->post('status') ?? 'draft'
      );

      if (!empty($attachmentUpload['path'])) {
        $data['attachment_path'] = $attachmentUpload['path'];
        $data['attachment_name'] = $attachmentUpload['name'];
      }

      $articleId = $this->Knowledge_base_model->insert($data);
      if (!$articleId) {
        if (!empty($attachmentUpload['path'])) {
          $this->_deleteKnowledgeBaseAttachmentFile($attachmentUpload['path']);
        }

        $data['categories'] = $this->Knowledge_base_model->get_categories($settingsID);
        $data['kb_error'] = 'Unable to create the knowledge base article. Please try again.';
        $data['form_values'] = array(
          'title' => $this->input->post('title'),
          'content' => $this->input->post('content'),
          'category' => $this->input->post('category'),
          'newCategory' => $this->input->post('newCategory'),
          'type' => $this->input->post('type'),
          'status' => $this->input->post('status'),
        );
        $this->load->view('knowledge_base_create', $data);
        return;
      }

      $this->_syncKnowledgeBaseCategory($settingsID, $categoryName);
      $this->session->set_flashdata('kb_success', 'Article created successfully.');
      redirect('Page/knowledgeBase');
      return;
    }

    $data['categories'] = $this->Knowledge_base_model->get_categories($settingsID);
    $this->load->view('knowledge_base_create', $data);
  }

  function knowledgeBaseEdit($id)
  {
    $level = strtolower(trim((string) $this->session->userdata('level')));
    $settingsID = $this->session->userdata('settingsID');
    $userID = $this->session->userdata('user_id');
    $userName = trim(($this->session->userdata('fName') ?? '') . ' ' . ($this->session->userdata('lName') ?? ''));

    if (!in_array($level, ['admin', 'manager', 'encoder', 'staff', 'cashier', 'pos admin', 'pos staff'])) {
      echo "Access Denied";
      return;
    }

    $article = $this->Knowledge_base_model->get_by_id($id);

    if (!$article) {
      show_404();
      return;
    }

    // Check if user created this article
    if ($article->created_by != $userID) {
      $this->session->set_flashdata('kb_error', 'You can only edit your own articles.');
      redirect('Page/knowledgeBase');
      return;
    }

    if ($this->input->post()) {
      $categoryName = $this->_normalizeKnowledgeBaseCategory($this->input->post('category'), $this->input->post('newCategory'));
      $attachmentUpload = $this->_handleKnowledgeBaseAttachmentUpload('attachment');

      if ($attachmentUpload['error'] !== '') {
        $articleForm = clone $article;
        $articleForm->title = $this->input->post('title');
        $articleForm->content = $this->input->post('content');
        $articleForm->category = $categoryName;
        $articleForm->type = $this->input->post('type');
        $articleForm->status = $this->input->post('status') ?? 'draft';

        $data['article'] = $articleForm;
        $data['categories'] = $this->Knowledge_base_model->get_categories($settingsID);
        $data['kb_error'] = $attachmentUpload['error'];
        $this->load->view('knowledge_base_edit', $data);
        return;
      }

      $existingAttachmentPath = trim((string) ($article->attachment_path ?? ''));
      $existingAttachmentName = trim((string) ($article->attachment_name ?? ''));
      $removeAttachment = $this->input->post('remove_attachment') === '1';
      $attachmentPath = $existingAttachmentPath !== '' ? $existingAttachmentPath : null;
      $attachmentName = $existingAttachmentName !== '' ? $existingAttachmentName : null;
      $deleteOldAttachment = false;

      if ($removeAttachment) {
        $attachmentPath = null;
        $attachmentName = null;
        $deleteOldAttachment = ($existingAttachmentPath !== '');
      }

      if (!empty($attachmentUpload['path'])) {
        $attachmentPath = $attachmentUpload['path'];
        $attachmentName = $attachmentUpload['name'];
        $deleteOldAttachment = ($existingAttachmentPath !== '');
      }

      $data = array(
        'title' => $this->input->post('title'),
        'content' => $this->input->post('content'),
        'category' => $categoryName !== '' ? $categoryName : null,
        'type' => $this->input->post('type'),
        'updated_by' => $userID,
        'updated_by_name' => $userName,
        'status' => $this->input->post('status') ?? 'draft',
        'attachment_path' => $attachmentPath,
        'attachment_name' => $attachmentName,
      );

      $updated = $this->Knowledge_base_model->update($id, $data);
      if (!$updated) {
        if (!empty($attachmentUpload['path'])) {
          $this->_deleteKnowledgeBaseAttachmentFile($attachmentUpload['path']);
        }

        $articleForm = clone $article;
        $articleForm->title = $data['title'];
        $articleForm->content = $data['content'];
        $articleForm->category = $data['category'];
        $articleForm->type = $data['type'];
        $articleForm->status = $data['status'];
        $articleForm->attachment_path = $data['attachment_path'];
        $articleForm->attachment_name = $data['attachment_name'];

        $data['article'] = $articleForm;
        $data['categories'] = $this->Knowledge_base_model->get_categories($settingsID);
        $data['kb_error'] = 'Unable to update the knowledge base article. Please try again.';
        $this->load->view('knowledge_base_edit', $data);
        return;
      }

      if ($deleteOldAttachment && $existingAttachmentPath !== '' && $existingAttachmentPath !== $attachmentPath) {
        $this->_deleteKnowledgeBaseAttachmentFile($existingAttachmentPath);
      }

      $this->_syncKnowledgeBaseCategory($settingsID, $categoryName);
      $this->session->set_flashdata('kb_success', 'Article updated successfully.');
      redirect('Page/knowledgeBase');
      return;
    }

    $data['article'] = $article;
    $data['categories'] = $this->Knowledge_base_model->get_categories($settingsID);
    $this->load->view('knowledge_base_edit', $data);
  }

  function knowledgeBaseView($id)
  {
    $level = strtolower(trim((string) $this->session->userdata('level')));
    $settingsID = $this->session->userdata('settingsID');
    $isLoggedIn = $this->session->userdata('logged_in') === TRUE;

    $article = $this->Knowledge_base_model->get_by_id($id);

    if (!$article) {
      show_404();
      return;
    }

    // Allow public access only for published articles
    if (!$isLoggedIn && $article->status !== 'published') {
      echo "Access Denied - This article is not publicly available.";
      return;
    }

    // If logged in, check if user has appropriate level
    if ($isLoggedIn && !in_array($level, ['admin', 'manager', 'encoder', 'staff', 'cashier', 'pos admin', 'pos staff', 'client'])) {
      echo "Access Denied";
      return;
    }

    // Increment view count
    $this->Knowledge_base_model->increment_view_count($id);

    $data['article'] = $article;
    $data['is_client'] = ($level === 'client');
    $data['is_public'] = !$isLoggedIn;

    if (!$isLoggedIn) {
      // Public view - use a simple layout without sidebar
      $this->load->view('knowledge_base_view_public', $data);
    } elseif ($level === 'client') {
      $this->load->view('knowledge_base_view_client', $data);
    } else {
      $this->load->view('knowledge_base_view', $data);
    }
  }

  function knowledgeBaseAttachment($id)
  {
    $level = strtolower(trim((string) $this->session->userdata('level')));
    $isLoggedIn = $this->session->userdata('logged_in') === TRUE;

    $article = $this->Knowledge_base_model->get_by_id($id);
    if (!$article) {
      show_404();
      return;
    }

    if (!$isLoggedIn && $article->status !== 'published') {
      echo "Access Denied - This attachment is not publicly available.";
      return;
    }

    if ($isLoggedIn && !in_array($level, ['admin', 'manager', 'encoder', 'staff', 'cashier', 'pos admin', 'pos staff', 'client'])) {
      echo "Access Denied";
      return;
    }

    $attachmentPath = ltrim(trim((string) ($article->attachment_path ?? '')), '/');
    if ($attachmentPath === '') {
      show_404();
      return;
    }

    $filePath = FCPATH . $attachmentPath;
    if (!is_file($filePath)) {
      show_404();
      return;
    }

    $downloadName = trim((string) ($article->attachment_name ?? ''));
    if ($downloadName === '') {
      $downloadName = basename($filePath);
    }

    $downloadName = str_replace(array('"', '\\', "\r", "\n"), '', $downloadName);
    $fileSize = @filesize($filePath);

    header('Content-Type: application/pdf');
    if ($fileSize !== false) {
      header('Content-Length: ' . $fileSize);
    }
    header("Content-Disposition: inline; filename=\"" . $downloadName . "\"; filename*=UTF-8''" . rawurlencode($downloadName));
    header('X-Content-Type-Options: nosniff');
    readfile($filePath);
    exit;
  }

  function knowledgeBaseDelete($id)
  {
    $level = strtolower(trim((string) $this->session->userdata('level')));
    $userID = $this->session->userdata('user_id') ?? $this->session->userdata('id');

    if (!in_array($level, ['admin', 'manager', 'encoder', 'staff', 'cashier', 'pos admin', 'pos staff'])) {
      echo "Access Denied";
      return;
    }

    $article = $this->Knowledge_base_model->get_by_id($id);

    if (!$article) {
      show_404();
      return;
    }

    // Check if user created this article
    if ($article->created_by != $userID) {
      $this->session->set_flashdata('kb_error', 'You can only delete your own articles.');
      redirect('Page/knowledgeBase');
      return;
    }

    $attachmentPath = trim((string) ($article->attachment_path ?? ''));
    $deleted = $this->Knowledge_base_model->delete($id);
    if ($deleted && $attachmentPath !== '') {
      $this->_deleteKnowledgeBaseAttachmentFile($attachmentPath);
    }

    $this->session->set_flashdata('kb_success', 'Article deleted successfully.');
    redirect('Page/knowledgeBase');
  }

  function knowledgeBaseSearch()
  {
    $level = strtolower(trim((string) $this->session->userdata('level')));
    $settingsID = $this->session->userdata('settingsID');

    if (!in_array($level, ['admin', 'manager', 'encoder', 'staff', 'cashier', 'pos admin', 'pos staff', 'client'])) {
      echo "Access Denied";
      return;
    }

    $query = $this->input->get('q');
    $type = $this->input->get('type') ?? null;

    if (empty($query)) {
      redirect('Page/knowledgeBase');
      return;
    }

    $results = $this->Knowledge_base_model->search($settingsID, $query, $type);
    $data['results'] = $results;
    $data['query'] = $query;
    $data['is_client'] = ($level === 'client');

    if ($level === 'client') {
      $this->load->view('knowledge_base_search_client', $data);
    } else {
      $this->load->view('knowledge_base_search', $data);
    }
  }

  function knowledgeBaseSettings()
  {
    $level = strtolower(trim((string) $this->session->userdata('level')));
    $settingsID = $this->session->userdata('settingsID');

    if (!in_array($level, ['admin', 'manager', 'encoder', 'staff', 'cashier', 'pos admin', 'pos staff'])) {
      echo "Access Denied";
      return;
    }

    // Handle add category
    if ($this->input->server('REQUEST_METHOD') === 'POST') {
      $newCategory = trim($this->input->post('new_category'));
      if (!empty($newCategory)) {
        // Check if category already exists
        $existing = $this->Knowledge_base_model->get_category($settingsID, $newCategory);

        if (!$existing) {
          // Insert category into categories table (table will be auto-created if needed)
          $data = [
            'settingsID' => $settingsID,
            'name' => $newCategory,
            'created_at' => date('Y-m-d H:i:s')
          ];
          try {
            $this->Knowledge_base_model->insert_category($data);
            $this->session->set_flashdata('kb_success', 'Category added successfully.');
          } catch (Exception $e) {
            $this->session->set_flashdata('kb_error', 'Error adding category: ' . $e->getMessage());
          }
        } else {
          $this->session->set_flashdata('kb_error', 'Category already exists.');
        }
      } else {
        $this->session->set_flashdata('kb_error', 'Category name cannot be empty.');
      }
      redirect('Page/knowledgeBaseSettings');
      return;
    }

    // Get categories and statistics
    $categories = $this->Knowledge_base_model->get_categories($settingsID);
    $allArticles = $this->Knowledge_base_model->get_all($settingsID, null, null);
    $publishedArticles = $this->Knowledge_base_model->get_all($settingsID, null, 'published');

    $data['categories'] = $categories;
    $data['total_articles'] = count($allArticles);
    $data['published_articles'] = count($publishedArticles);

    $this->load->view('knowledge_base_settings', $data);
  }

  function knowledgeBaseEditCategory()
  {
    $level = strtolower(trim((string) $this->session->userdata('level')));
    $settingsID = $this->session->userdata('settingsID');

    if (!in_array($level, ['admin', 'manager', 'encoder', 'staff', 'cashier', 'pos admin', 'pos staff'])) {
      echo "Access Denied";
      return;
    }

    $oldCategory = trim($this->input->post('old_category'));
    $newCategory = trim($this->input->post('new_category'));

    if (empty($oldCategory) || empty($newCategory)) {
      $this->session->set_flashdata('kb_error', 'Category names cannot be empty.');
      redirect('Page/knowledgeBaseSettings');
      return;
    }

    if ($oldCategory === $newCategory) {
      $this->session->set_flashdata('kb_error', 'New category name is the same as the old one.');
      redirect('Page/knowledgeBaseSettings');
      return;
    }

    // Check if new category already exists
    $existing = $this->Knowledge_base_model->get_category($settingsID, $newCategory);

    if ($existing) {
      $this->session->set_flashdata('kb_error', 'Category already exists.');
      redirect('Page/knowledgeBaseSettings');
      return;
    }

    // Update category in categories table
    $this->Knowledge_base_model->update_category($settingsID, $oldCategory, $newCategory);

    // Update all articles with the old category to the new category
    $this->db->where('settingsID', $settingsID);
    $this->db->where('category', $oldCategory);
    $this->db->update('knowledge_base', ['category' => $newCategory]);

    $this->session->set_flashdata('kb_success', 'Category updated successfully.');
    redirect('Page/knowledgeBaseSettings');
  }

  function knowledgeBaseDeleteCategory($category)
  {
    $level = strtolower(trim((string) $this->session->userdata('level')));
    $settingsID = $this->session->userdata('settingsID');

    if (!in_array($level, ['admin', 'manager', 'encoder', 'staff', 'cashier', 'pos admin', 'pos staff'])) {
      echo "Access Denied";
      return;
    }

    $category = urldecode($category);

    if (empty($category)) {
      $this->session->set_flashdata('kb_error', 'Invalid category.');
      redirect('Page/knowledgeBaseSettings');
      return;
    }

    // Delete category from categories table
    $this->Knowledge_base_model->delete_category($settingsID, $category);

    // Set all articles with this category to uncategorized (empty string)
    $this->db->where('settingsID', $settingsID);
    $this->db->where('category', $category);
    $this->db->update('knowledge_base', ['category' => '']);

    $this->session->set_flashdata('kb_success', 'Category deleted successfully. Articles have been set to uncategorized.');
    redirect('Page/knowledgeBaseSettings');
  }
}
