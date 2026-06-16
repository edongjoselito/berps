<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Pos extends CI_Controller
{
  private $companyFeatureAccessLoaded = false;
  private $companyFeatureRestrictionsActive = false;
  private $enabledCompanyFeatures = array();

  function __construct()
  {
    parent::__construct();
    $this->load->database();
    $this->load->helper('url');
    $this->load->helper('form');
    $this->load->library('session');
    $this->load->library('form_validation');
    $this->load->model('CashModel');
    $this->load->model('PosProduct_model');
    $this->load->model('PosModel');
    $this->load->model('PosCategory_model');
    date_default_timezone_set('Asia/Manila');

    if ($this->session->userdata('logged_in') !== TRUE) {
      redirect('login');
    }

    if (strtolower(trim((string) $this->session->userdata('level'))) === 'client') {
      redirect('Page/clientDashboard');
    }

    $this->_ensurePosCategoryTable();
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

  public function posStaff()
  {
    if (!$this->_ensure_pos_user(['POS Staff', 'POS Admin'])) {
      return;
    }

    $data = $this->_pos_dashboard_data();
    $data['page_title'] = 'POS Staff Panel';
    $this->load->view('dashboard_pos_staff', $data);
  }

  public function posAdmin()
  {
    if (!$this->_ensure_pos_user(['POS Admin'])) {
      return;
    }

    $data = $this->_pos_dashboard_data();
    $data['page_title'] = 'POS Admin Panel';

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
    $user_id = $this->session->userdata('user_id');
    $data['reminders'] = array();
    if ($this->db->table_exists('user_reminders') && $user_id) {
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

    $this->load->view('dashboard_pos_admin', $data);
  }

  public function posNewTransaction()
  {
    if (!$this->_ensure_pos_user(['POS Staff', 'POS Admin'])) {
      return;
    }

    $settingsID = (int) ($this->session->userdata('settingsID') ?? 0);
    $businessRows = $this->CashModel->businessDetails($settingsID);
    $business = !empty($businessRows) ? $businessRows[0] : null;

    $data = [
      'page_title' => 'New POS Sale',
      'page_subtitle' => 'Create sales with Philippine VAT, senior/PWD discounts, and installment terms.',
      'products' => $this->PosModel->getProductChoices($settingsID),
      'clients' => $this->CashModel->getClients($settingsID),
      'business' => $business,
      'default_tax_profile' => $this->PosModel->getBusinessTaxProfile($settingsID),
      'payment_modes' => ['Cash', 'GCash', 'Bank Transfer', 'Debit/Credit Card', 'Cheque'],
      'notice' => $this->session->flashdata('pos_notice'),
      'notice_type' => $this->session->flashdata('pos_notice_type'),
    ];

    $this->load->view('pos_fullscreen', $data);
  }

  public function posStoreTransaction()
  {
    if (!$this->_ensure_pos_user(['POS Staff', 'POS Admin'])) {
      return;
    }

    if ($this->input->method() !== 'post') {
      redirect('Pos/posNewTransaction');
      return;
    }

    $settingsID = (int) ($this->session->userdata('settingsID') ?? 0);
    $result = $this->PosModel->createSale($settingsID, $this->_current_pos_actor(), [
      'product_id' => (array) $this->input->post('product_id'),
      'quantity' => (array) $this->input->post('quantity'),
      'unit_price' => (array) $this->input->post('unit_price'),
      'customer_id' => $this->input->post('customer_id', true),
      'customer_name' => $this->input->post('customer_name', true),
      'customer_address' => $this->input->post('customer_address', true),
      'customer_tin' => $this->input->post('customer_tin', true),
      'customer_discount_id' => $this->input->post('customer_discount_id', true),
      'discount_type' => $this->input->post('discount_type', true),
      'discount_rate' => $this->input->post('discount_rate', true),
      'discount_value' => $this->input->post('discount_value', true),
      'payment_term' => $this->input->post('payment_term', true),
      'payment_mode' => $this->input->post('payment_mode', true),
      'payment_reference' => $this->input->post('payment_reference', true),
      'initial_payment' => $this->input->post('initial_payment', true),
      'installment_count' => $this->input->post('installment_count', true),
      'first_due_date' => $this->input->post('first_due_date', true),
      'installment_interval_days' => $this->input->post('installment_interval_days', true),
      'transaction_date' => $this->input->post('transaction_date', true),
      'terminal_no' => $this->input->post('terminal_no', true),
      'notes' => $this->input->post('notes', true),
    ]);

    if (!empty($result['success'])) {
      $this->_pos_notice('Sale ' . $result['sale_no'] . ' was saved successfully.');
      redirect('Pos/posNewTransaction');
      return;
    }

    $this->_pos_notice((string) ($result['error'] ?? 'Unable to save the POS sale.'), 'error');
    redirect('Pos/posNewTransaction');
  }

  public function posTransactionHistory()
  {
    if (!$this->_ensure_pos_user(['POS Staff', 'POS Admin'])) {
      return;
    }

    $dateFrom = $this->_normalizeDateInput($this->input->get('date_from')) ?: date('Y-m-01');
    $dateTo = $this->_normalizeDateInput($this->input->get('date_to')) ?: date('Y-m-d');
    list($dateFrom, $dateTo) = $this->_normalize_pos_date_range($dateFrom, $dateTo);
    $status = trim((string) $this->input->get('status'));
    $search = trim((string) $this->input->get('search'));
    $settingsID = (int) ($this->session->userdata('settingsID') ?? 0);

    $sales = $this->PosModel->getSales($settingsID, [
      'date_from' => $dateFrom,
      'date_to' => $dateTo,
      'status' => $status,
      'search' => $search,
      'include_voided' => false,
    ]);

    $data = [
      'page_title' => 'POS Sales History',
      'page_subtitle' => 'Review sales, balances, payment terms, and cashier activity.',
      'sales' => $sales,
      'list_summary' => $this->_build_pos_list_summary($sales),
      'filter_date_from' => $dateFrom,
      'filter_date_to' => $dateTo,
      'filter_status' => $status,
      'filter_search' => $search,
      'void_mode' => false,
      'notice' => $this->session->flashdata('pos_notice'),
      'notice_type' => $this->session->flashdata('pos_notice_type'),
    ];

    $this->load->view('pos_transaction_history', $data);
  }

  public function posReturnsVoids()
  {
    if (!$this->_ensure_pos_user(['POS Staff', 'POS Admin'])) {
      return;
    }

    $dateFrom = $this->_normalizeDateInput($this->input->get('date_from')) ?: date('Y-m-01');
    $dateTo = $this->_normalizeDateInput($this->input->get('date_to')) ?: date('Y-m-d');
    list($dateFrom, $dateTo) = $this->_normalize_pos_date_range($dateFrom, $dateTo);
    $search = trim((string) $this->input->get('search'));
    $settingsID = (int) ($this->session->userdata('settingsID') ?? 0);

    $sales = $this->PosModel->getSales($settingsID, [
      'date_from' => $dateFrom,
      'date_to' => $dateTo,
      'status' => 'Voided',
      'search' => $search,
      'include_voided' => true,
    ]);

    $data = [
      'page_title' => 'Returns / Voids',
      'page_subtitle' => 'Track voided transactions and restored stock. Returns can be handled as voided sales plus a replacement sale.',
      'sales' => $sales,
      'list_summary' => $this->_build_pos_list_summary($sales),
      'filter_date_from' => $dateFrom,
      'filter_date_to' => $dateTo,
      'filter_status' => 'Voided',
      'filter_search' => $search,
      'void_mode' => true,
      'notice' => $this->session->flashdata('pos_notice'),
      'notice_type' => $this->session->flashdata('pos_notice_type'),
    ];

    $this->load->view('pos_transaction_history', $data);
  }

  public function posTransactionDetail($saleId = null)
  {
    if (!$this->_ensure_pos_user(['POS Staff', 'POS Admin'])) {
      return;
    }

    $settingsID = (int) ($this->session->userdata('settingsID') ?? 0);
    $saleId = (int) $saleId;
    if ($saleId <= 0) {
      show_404();
      return;
    }

    $sale = $this->PosModel->getSale($settingsID, $saleId);
    if (!$sale) {
      show_404();
      return;
    }

    $businessRows = $this->CashModel->businessDetails($settingsID);

    $data = [
      'page_title' => 'POS Transaction Detail',
      'sale' => $sale,
      'items' => $this->PosModel->getSaleItems($settingsID, $saleId),
      'payments' => $this->PosModel->getSalePayments($settingsID, $saleId),
      'installments' => $this->PosModel->getInstallmentSchedule($settingsID, $saleId),
      'business' => !empty($businessRows) ? $businessRows[0] : null,
      'can_void' => $this->_can_current_user_manage_pos_admin_actions(),
      'payment_modes' => ['Cash', 'GCash', 'Bank Transfer', 'Debit/Credit Card', 'Cheque'],
      'notice' => $this->session->flashdata('pos_notice'),
      'notice_type' => $this->session->flashdata('pos_notice_type'),
    ];

    $this->load->view('pos_transaction_detail', $data);
  }

  public function posRecordPayment($saleId = null)
  {
    if (!$this->_ensure_pos_user(['POS Staff', 'POS Admin'])) {
      return;
    }

    $saleId = (int) $saleId;
    if ($this->input->method() !== 'post' || $saleId <= 0) {
      redirect('Pos/posTransactionHistory');
      return;
    }

    $settingsID = (int) ($this->session->userdata('settingsID') ?? 0);
    $result = $this->PosModel->recordPayment($settingsID, $saleId, [
      'amount' => $this->input->post('amount', true),
      'payment_date' => $this->input->post('payment_date', true),
      'payment_mode' => $this->input->post('payment_mode', true),
      'reference_no' => $this->input->post('reference_no', true),
      'remarks' => $this->input->post('remarks', true),
      'received_by' => $this->_current_pos_actor(),
    ]);

    if (!empty($result['success'])) {
      $this->_pos_notice('Payment recorded successfully.');
    } else {
      $this->_pos_notice((string) ($result['error'] ?? 'Unable to record the payment.'), 'error');
    }

    redirect('Pos/posTransactionDetail/' . $saleId);
  }

  public function posVoidTransaction($saleId = null)
  {
    if (!$this->_ensure_pos_user(['POS Admin'])) {
      return;
    }

    $saleId = (int) $saleId;
    if ($this->input->method() !== 'post' || $saleId <= 0) {
      redirect('Pos/posTransactionHistory');
      return;
    }

    $settingsID = (int) ($this->session->userdata('settingsID') ?? 0);
    $result = $this->PosModel->voidSale(
      $settingsID,
      $saleId,
      $this->_current_pos_actor(),
      (string) $this->input->post('void_reason', true)
    );

    if (!empty($result['success'])) {
      $this->_pos_notice('Transaction voided and stock restored.');
    } else {
      $this->_pos_notice((string) ($result['error'] ?? 'Unable to void the transaction.'), 'error');
    }

    redirect('Pos/posTransactionDetail/' . $saleId);
  }

  public function posReports()
  {
    if (!$this->_ensure_pos_user(['POS Staff', 'POS Admin'])) {
      return;
    }

    $settingsID = (int) ($this->session->userdata('settingsID') ?? 0);
    $dateFrom = $this->_normalizeDateInput($this->input->get('date_from')) ?: date('Y-m-01');
    $dateTo = $this->_normalizeDateInput($this->input->get('date_to')) ?: date('Y-m-d');
    list($dateFrom, $dateTo) = $this->_normalize_pos_date_range($dateFrom, $dateTo);
    $report = $this->PosModel->reportBundle($settingsID, $dateFrom, $dateTo);

    $data = array_merge($report, [
      'page_title' => 'POS Reports',
      'page_subtitle' => 'Sales, collections, VAT, installments, and inventory insights for your selected period.',
      'notice' => $this->session->flashdata('pos_notice'),
      'notice_type' => $this->session->flashdata('pos_notice_type'),
    ]);

    $this->load->view('pos_reports', $data);
  }

  public function posAddProduct()
  {
    // kept for compatibility; route to product list with add form
    redirect('Pos/posProductList');
  }

  public function posProductList()
  {
    if (!$this->_ensure_pos_user(['POS Staff', 'POS Admin'])) {
      return;
    }

    $settingsID = (int) ($this->session->userdata('settingsID') ?? 0);
    $tenant = $settingsID > 0 ? $settingsID : null;

    // Show all products (including expired) so they can be managed/deleted in one place.
    $products = $this->PosProduct_model->get_all_sorted_by_sku($tenant);
    $expired = $this->PosProduct_model->get_expired($tenant);
    $expiredCount = is_array($expired) ? count($expired) : 0;

    $editId = (int) ($this->session->flashdata('pos_edit_product_id') ?? 0);
    $editProduct = null;
    if ($editId > 0) {
      $editProduct = $this->PosProduct_model->find($editId, $tenant);
    }

    $nextSku = $this->PosProduct_model->generate_next_sku('POS-', $tenant);

    $categories = $this->PosCategory_model->get_all($tenant);
    $data = [
      'products' => $products,
      'pos_categories' => $categories,
      'page_title' => 'Enhanced Product Management',
      'page_subtitle' => 'Industry-specific product entry for different business types',
      'notice' => $this->session->flashdata('pos_notice'),
      'notice_type' => $this->session->flashdata('pos_notice_type'),
      'next_sku' => $nextSku,
      'edit_product' => $editProduct,
      'open_edit_modal' => !empty($editProduct),
    ];

    if ($editProduct) {
      $data['edit_product'] = $editProduct;
      $data['open_edit_modal'] = true;
    }

    if ($expiredCount > 0) {
      $data['notice'] = 'Heads up: ' . $expiredCount . ' product(s) expired and are listed under Expiry Monitoring.';
      $data['notice_type'] = 'error';
    }

    $this->load->view('pos_product_enhanced', $data);
  }

  public function posCategorySettings()
  {
    if (!$this->_ensure_pos_user(['POS Admin'])) {
      return;
    }

    $settingsID = (int) ($this->session->userdata('settingsID') ?? 0);
    $tenant = $settingsID > 0 ? $settingsID : null;

    $categories = $this->PosCategory_model->get_all($tenant);
    $data = [
      'page_title' => 'POS Categories',
      'page_subtitle' => 'Manage product categories used in POS product entry.',
      'categories' => $categories,
      'notice' => $this->session->flashdata('pos_notice'),
      'notice_type' => $this->session->flashdata('pos_notice_type'),
    ];

    $this->load->view('pos_categories', $data);
  }

  public function posCategoryCreate()
  {
    if (!$this->_ensure_pos_user(['POS Admin'])) {
      return;
    }

    $settingsID = (int) ($this->session->userdata('settingsID') ?? 0);
    $tenant = $settingsID > 0 ? $settingsID : null;
    $name = trim((string) $this->input->post('name', true));

    if ($name === '') {
      $this->_pos_notice('Category name is required.', 'error');
      redirect('Pos/posCategorySettings');
      return;
    }

    $created = $this->PosCategory_model->create($name, $tenant);
    if ($created) {
      $this->_pos_notice('Category added.', 'success');
    } else {
      $this->_pos_notice('Unable to add category. It may already exist.', 'error');
    }

    redirect('Pos/posCategorySettings');
  }

  public function posCategoryUpdate()
  {
    if (!$this->_ensure_pos_user(['POS Admin'])) {
      return;
    }

    $settingsID = (int) ($this->session->userdata('settingsID') ?? 0);
    $tenant = $settingsID > 0 ? $settingsID : null;
    $id = (int) $this->input->post('id', true);
    $name = trim((string) $this->input->post('name', true));

    if ($id <= 0 || $name === '') {
      $this->_pos_notice('Invalid category.', 'error');
      redirect('Pos/posCategorySettings');
      return;
    }

    $updated = $this->PosCategory_model->update($id, $name, $tenant);
    if ($updated) {
      $this->_pos_notice('Category updated.', 'success');
    } else {
      $this->_pos_notice('Unable to update category. It may already exist.', 'error');
    }

    redirect('Pos/posCategorySettings');
  }

  public function posCategoryDelete()
  {
    if (!$this->_ensure_pos_user(['POS Admin'])) {
      return;
    }

    $settingsID = (int) ($this->session->userdata('settingsID') ?? 0);
    $tenant = $settingsID > 0 ? $settingsID : null;
    $id = (int) $this->input->get('id');

    if ($id <= 0) {
      $this->_pos_notice('Invalid category.', 'error');
      redirect('Pos/posCategorySettings');
      return;
    }

    $deleted = $this->PosCategory_model->delete($id, $tenant);
    if ($deleted) {
      $this->_pos_notice('Category deleted.', 'success');
    } else {
      $this->_pos_notice('Unable to delete category.', 'error');
    }

    redirect('Pos/posCategorySettings');
  }

  public function posCreateProduct()
  {
    if (!$this->_ensure_pos_user(['POS Staff', 'POS Admin'])) {
      return;
    }

    $settingsID = (int) ($this->session->userdata('settingsID') ?? 0);
    $tenant = $settingsID > 0 ? $settingsID : 0; // default to 0 if not set

    $this->form_validation->set_rules('sku', 'SKU', 'trim');
    $this->form_validation->set_rules('name', 'Name', 'required|trim');
    $this->form_validation->set_rules('category', 'Category', 'required|trim');
    $this->form_validation->set_rules('barcode', 'Barcode', 'trim');
    $this->form_validation->set_rules('unit', 'Unit', 'required|trim');
    $this->form_validation->set_rules('unit_price', 'Unit Price', 'required|numeric');
    $this->form_validation->set_rules('reorder_level', 'Reorder Level', 'required|integer');
    $this->form_validation->set_rules('business_type', 'Business Type', 'required|trim');

    if ($this->form_validation->run() === false) {
      $this->_pos_notice(validation_errors(), 'error');
      return redirect('Pos/posProductList');
    }

    $sku = $this->input->post('sku', true);
    if ($sku === null || $sku === '') {
      $sku = $this->PosProduct_model->generate_next_sku('POS-', $tenant);
    }
    $expiry = $this->_sanitize_expiry_date($this->input->post('expiry_date', true));

    $data = [
      'sku' => $sku,
      'name' => $this->input->post('name', true),
      'category' => $this->input->post('category', true),
      'barcode' => $this->input->post('barcode', true),
      'unit' => $this->input->post('unit', true) ?: 'pcs',
      'unit_cost' => $this->input->post('unit_cost', true) ?: 0,
      'unit_price' => $this->input->post('unit_price', true) ?: 0,
      'stock_qty' => $this->input->post('stock_qty', true) ?: 0,
      'reorder_level' => $this->input->post('reorder_level', true) ?: 5,
      'tax_type' => $this->input->post('tax_type', true) ?: 'vatable',
      'discount_eligible' => $this->input->post('discount_eligible', true) ? 1 : 0,
      'expiry_date' => $expiry,
      'settingsID' => $tenant,
      'status' => 'active',
      'business_type' => $this->input->post('business_type', true),
      'brand' => $this->input->post('brand', true),
    ];

    // Add industry-specific fields based on business type
    $businessType = $this->input->post('business_type', true);

    switch ($businessType) {
      case 'pharmacy':
        $data['generic_name'] = $this->input->post('generic_name', true);
        $data['dosage_form'] = $this->input->post('dosage_form', true);
        $data['strength'] = $this->input->post('strength', true);
        $data['prescription_required'] = $this->input->post('prescription_required', true) ? 1 : 0;
        $data['fda_registration'] = $this->input->post('fda_registration', true);
        $data['drug_classification'] = $this->input->post('drug_classification', true);
        $data['storage_requirements'] = $this->input->post('storage_requirements', true);
        $data['expiry_tracking'] = $this->input->post('expiry_tracking', true) ? 1 : 0;
        break;

      case 'grocery':
        $data['product_type'] = $this->input->post('product_type', true);
        $data['organic_certified'] = $this->input->post('organic_certified', true);
        $allergens = $this->input->post('allergens', true);
        $data['allergens'] = is_array($allergens) ? implode(',', $allergens) : '';
        $data['nutritional_info'] = $this->input->post('nutritional_info', true);
        $data['storage_instructions'] = $this->input->post('storage_instructions', true);
        $data['shelf_life'] = $this->input->post('shelf_life', true);
        $data['country_of_origin'] = $this->input->post('country_of_origin', true);
        break;

      case 'restaurant':
        $data['menu_category'] = $this->input->post('menu_category', true);
        $data['preparation_time'] = $this->input->post('preparation_time', true);
        $data['temperature_requirement'] = $this->input->post('temperature_requirement', true);
        $dietary = $this->input->post('dietary', true);
        $data['dietary_restrictions'] = is_array($dietary) ? implode(',', $dietary) : '';
        $allergenWarnings = $this->input->post('allergen_warnings', true);
        $data['allergen_warnings'] = is_array($allergenWarnings) ? implode(',', $allergenWarnings) : '';
        $data['cooking_method'] = $this->input->post('cooking_method', true);
        $data['spice_level'] = $this->input->post('spice_level', true);
        break;

      case 'electronics':
        $data['electronics_category'] = $this->input->post('electronics_category', true);
        $data['model_number'] = $this->input->post('model_number', true);
        $data['warranty_period'] = $this->input->post('warranty_period', true);
        $data['power_requirements'] = $this->input->post('power_requirements', true);
        $data['technical_specs'] = $this->input->post('technical_specs', true);
        $data['compatibility'] = $this->input->post('compatibility', true);
        $data['color_options'] = $this->input->post('color_options', true);
        $data['serial_tracking'] = $this->input->post('serial_tracking', true) ? 1 : 0;
        break;

      case 'clothing':
        $data['clothing_category'] = $this->input->post('clothing_category', true);
        $data['material'] = $this->input->post('material', true);
        $sizes = $this->input->post('sizes', true);
        $data['sizes'] = is_array($sizes) ? implode(',', $sizes) : '';
        $data['colors'] = $this->input->post('colors', true);
        $data['season'] = $this->input->post('season', true);
        $data['fit_type'] = $this->input->post('fit_type', true);
        $data['care_instructions'] = $this->input->post('care_instructions', true);
        break;

      case 'general':
        $data['description'] = $this->input->post('description', true);
        $data['specifications'] = $this->input->post('specifications', true);
        $data['usage_instructions'] = $this->input->post('usage_instructions', true);
        $data['safety_info'] = $this->input->post('safety_info', true);
        break;
    }

    $inserted = $this->PosProduct_model->insert($data);
    if ($inserted) {
      $this->PosModel->syncProductOpeningStock($tenant, (int) $this->db->insert_id(), (int) $data['stock_qty'], $this->_current_pos_actor());
      $this->_pos_notice('Product added successfully.');
    } else {
      $this->_pos_notice('Could not save product. Please check the data and try again.', 'error');
    }

    redirect('Pos/posProductList');
  }

  public function posEditProduct($id = null, $renderNow = false)
  {
    if (!$this->_ensure_pos_user(['POS Staff', 'POS Admin'])) {
      return;
    }

    $settingsID = (int) ($this->session->userdata('settingsID') ?? 0);
    $tenant = $settingsID > 0 ? $settingsID : null;

    $id = (int) ($id ?? 0);
    if ($id <= 0) {
      $this->_pos_notice('Invalid product selected.', 'error');
      return redirect('Pos/posProductList');
    }

    $product = $this->PosProduct_model->find($id, $tenant);
    if (!$product) {
      $this->_pos_notice('Product not found or may have been removed.', 'error');
      return redirect('Pos/posProductList');
    }

    // Default: set a one-time flag and redirect to the main list so refreshes don't reopen via the edit URL.
    if (!$renderNow) {
      $this->session->set_flashdata('pos_edit_product_id', $id);
      return redirect('Pos/posProductList');
    }

    // Render immediately (used when validation fails so errors are shown)
    $products = $this->PosProduct_model->get_all_sorted_by_sku($tenant);
    $nextSku = $this->PosProduct_model->generate_next_sku('POS-', $tenant);
    $data = [
      'products' => $products,
      'page_title' => 'Product List',
      'page_subtitle' => 'Manage POS items and stock levels.',
      'notice' => $this->session->flashdata('pos_notice'),
      'notice_type' => $this->session->flashdata('pos_notice_type'),
      'next_sku' => $nextSku,
      'edit_product' => $product,
      'open_edit_modal' => true,
    ];

    $this->load->view('pos_product_list', $data);
  }

  public function posUpdateProduct($id = null)
  {
    if (!$this->_ensure_pos_user(['POS Staff', 'POS Admin'])) {
      return;
    }

    $settingsID = (int) ($this->session->userdata('settingsID') ?? 0);
    $tenant = $settingsID > 0 ? $settingsID : null;

    $id = (int) ($id ?? $this->input->post('id', true) ?? 0);
    if ($id <= 0) {
      $this->_pos_notice('Invalid product selected.', 'error');
      return redirect('Pos/posProductList');
    }

    $product = $this->PosProduct_model->find($id, $tenant);
    if (!$product) {
      $this->_pos_notice('Product not found or may have been removed.', 'error');
      return redirect('Pos/posProductList');
    }

    $this->form_validation->set_rules('sku', 'SKU', 'trim');
    $this->form_validation->set_rules('name', 'Name', 'required|trim');
    $this->form_validation->set_rules('category', 'Category', 'trim');
    $this->form_validation->set_rules('barcode', 'Barcode', 'trim');
    $this->form_validation->set_rules('unit', 'Unit', 'trim');
    $this->form_validation->set_rules('unit_cost', 'Unit Cost', 'numeric');
    $this->form_validation->set_rules('unit_price', 'Unit Price', 'required|numeric');
    $this->form_validation->set_rules('stock_qty', 'Stock Qty', 'integer');
    $this->form_validation->set_rules('reorder_level', 'Reorder Level', 'integer');
    $this->form_validation->set_rules('expiry_date', 'Expiry Date', 'trim');

    if ($this->form_validation->run() === false) {
      return $this->posEditProduct($id, true);
    }

    $expiry = $this->_sanitize_expiry_date($this->input->post('expiry_date', true));

    $data = [
      'sku' => $this->input->post('sku', true) ?: $product->sku,
      'name' => $this->input->post('name', true),
      'category' => $this->input->post('category', true),
      'barcode' => $this->input->post('barcode', true),
      'unit' => $this->input->post('unit', true) ?: 'pcs',
      'unit_cost' => ($this->input->post('unit_cost', true) === '' || $this->input->post('unit_cost', true) === null)
        ? 0
        : (float) $this->input->post('unit_cost', true),
      'unit_price' => ($this->input->post('unit_price', true) === '' || $this->input->post('unit_price', true) === null)
        ? 0
        : (float) $this->input->post('unit_price', true),
      'stock_qty' => ($this->input->post('stock_qty', true) === '' || $this->input->post('stock_qty', true) === null)
        ? 0
        : (int) $this->input->post('stock_qty', true),
      'reorder_level' => ($this->input->post('reorder_level', true) === '' || $this->input->post('reorder_level', true) === null)
        ? 5
        : (int) $this->input->post('reorder_level', true),
      'tax_type' => $this->input->post('tax_type', true) ?: 'vatable',
      'discount_eligible' => $this->input->post('discount_eligible', true) ? 1 : 0,
      'expiry_date' => $expiry,
    ];

    $updated = $this->PosProduct_model->update($id, $data, $tenant);
    if ($updated) {
      $this->PosModel->syncProductAdjustment($tenant, $id, (int) ($product->stock_qty ?? 0), (int) $data['stock_qty'], $this->_current_pos_actor());
      $this->_pos_notice('Product updated successfully.');
    } else {
      $this->_pos_notice('No changes were saved.', 'error');
    }

    redirect('Pos/posProductList');
  }

  public function posDeleteProduct($id = null)
  {
    if (!$this->_ensure_pos_user(['POS Staff', 'POS Admin'])) {
      return;
    }

    $settingsID = (int) ($this->session->userdata('settingsID') ?? 0);
    $tenant = $settingsID > 0 ? $settingsID : null;

    $id = (int) ($id ?? 0);
    if ($id <= 0) {
      $this->_pos_notice('Invalid product selected.', 'error');
      return redirect('Pos/posProductList');
    }

    $product = $this->PosProduct_model->find($id, $tenant);
    if (!$product) {
      $this->_pos_notice('Product not found or may have been removed.', 'error');
      return redirect('Pos/posProductList');
    }

    if ($this->PosModel->productHasSales($tenant, $id)) {
      $this->_pos_notice('This product already appears in a recorded sale and can no longer be deleted so the POS history stays intact.', 'error');
      return redirect('Pos/posProductList');
    }

    $deleted = $this->PosProduct_model->delete($id, $tenant);
    if ($deleted) {
      $this->_pos_notice('Product deleted successfully.');
    } else {
      $this->_pos_notice('Unable to delete product. Please try again.', 'error');
    }

    redirect('Pos/posProductList');
  }

  public function posExpiringSoon()
  {
    if (!$this->_ensure_pos_user(['POS Staff', 'POS Admin'])) {
      return;
    }

    $settingsID = (int) ($this->session->userdata('settingsID') ?? 0);
    $tenant = $settingsID > 0 ? $settingsID : null;

    date_default_timezone_set('Asia/Manila');

    $products = $this->PosProduct_model->get_expiring_soon(30, $tenant);
    $data = [
      'page_title' => 'Expiring Soon',
      'products' => $products,
      'mode' => 'soon',
      'empty_text' => 'No products expiring within the next 30 days.',
      'notice' => $this->session->flashdata('pos_notice'),
      'notice_type' => $this->session->flashdata('pos_notice_type'),
    ];

    $this->load->view('pos_expiry_monitor', $data);
  }

  public function posExpiredProducts()
  {
    if (!$this->_ensure_pos_user(['POS Staff', 'POS Admin'])) {
      return;
    }

    $settingsID = (int) ($this->session->userdata('settingsID') ?? 0);
    $tenant = $settingsID > 0 ? $settingsID : null;

    date_default_timezone_set('Asia/Manila');

    $products = $this->PosProduct_model->get_expired($tenant);
    $data = [
      'page_title' => 'Expired Products',
      'page_subtitle' => 'Handle expired inventory.',
      'products' => $products,
      'mode' => 'expired',
      'empty_text' => 'Great! You have no expired products.',
      'notice' => $this->session->flashdata('pos_notice'),
      'notice_type' => $this->session->flashdata('pos_notice_type'),
    ];

    $this->load->view('pos_expiry_monitor', $data);
  }

  public function posStockLevels()
  {
    if (!$this->_ensure_pos_user(['POS Staff', 'POS Admin'])) {
      return;
    }

    $settingsID = (int) ($this->session->userdata('settingsID') ?? 0);
    $tenant = $settingsID > 0 ? $settingsID : null;

    $products = $this->PosProduct_model->get_stock_levels($tenant);
    $data = [
      'page_title' => 'Stock Levels',
      'page_subtitle' => 'Check current stock positions.',
      'products' => $products,
      'mode' => 'stock',
      'empty_text' => 'No products found.',
      'notice' => $this->session->flashdata('pos_notice'),
      'notice_type' => $this->session->flashdata('pos_notice_type'),
    ];

    $this->load->view('pos_stock_levels', $data);
  }

  public function posLowStockItems()
  {
    if (!$this->_ensure_pos_user(['POS Staff', 'POS Admin'])) {
      return;
    }

    $settingsID = (int) ($this->session->userdata('settingsID') ?? 0);
    $tenant = $settingsID > 0 ? $settingsID : null;

    $products = $this->PosProduct_model->get_low_stock(null, $tenant);
    $data = [
      'page_title' => 'Low Stock Items',
      'page_subtitle' => 'Items below reorder threshold.',
      'products' => $products,
      'mode' => 'low',
      'empty_text' => 'No low stock items right now.',
      'notice' => $this->session->flashdata('pos_notice'),
      'notice_type' => $this->session->flashdata('pos_notice_type'),
    ];

    $this->load->view('pos_stock_levels', $data);
  }

  private function _render_pos_page($title, $subtitle = '')
  {
    if (!$this->_ensure_pos_user(['POS Staff', 'POS Admin'])) {
      return;
    }

    $data = [
      'page_title' => $title,
      'page_subtitle' => $subtitle,
    ];
    $this->load->view('pos_placeholder', $data);
  }

  private function _ensure_pos_user(array $allowed_levels)
  {
    if (!$this->_companyHasFeature('pos')) {
      show_error('POS module is not enabled for this company.', 403);
      return false;
    }

    $level = $this->_normalize_pos_role($this->session->userdata('level'));
    if (in_array($level, ['admin', 'system administrator', 'super admin'], true)) {
      return true;
    }

    $allowed = $this->_expand_pos_allowed_levels($allowed_levels);

    if (!in_array($level, $allowed, true)) {
      redirect('login');
      return false;
    }
    return true;
  }

  private function _normalize_pos_role($role)
  {
    return strtolower(trim((string) $role));
  }

  private function _expand_pos_allowed_levels(array $allowed_levels)
  {
    $allowed = [];

    foreach ($allowed_levels as $role) {
      $role = $this->_normalize_pos_role($role);
      if ($role === '') {
        continue;
      }

      $allowed[$role] = $role;

      if ($role === 'pos admin') {
        $allowed['manager'] = 'manager';
      }

      if ($role === 'pos staff') {
        $allowed['staff'] = 'staff';
        $allowed['cashier'] = 'cashier';
      }
    }

    return array_values($allowed);
  }

  private function _loadCurrentCompanyFeatureAccess()
  {
    if ($this->companyFeatureAccessLoaded) {
      return;
    }

    $this->companyFeatureAccessLoaded = true;
    $this->companyFeatureRestrictionsActive = false;
    $this->enabledCompanyFeatures = array();

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

  private function _companyHasFeature($featureKey)
  {
    $featureKey = trim((string) $featureKey);
    if ($featureKey === '') {
      return false;
    }

    $this->_loadCurrentCompanyFeatureAccess();

    if (!$this->companyFeatureRestrictionsActive) {
      return true;
    }

    return in_array($featureKey, $this->enabledCompanyFeatures, true);
  }

  private function _pos_notice($message, $type = 'success')
  {
    $this->session->set_flashdata('pos_notice', $message);
    $this->session->set_flashdata('pos_notice_type', $type === 'error' ? 'error' : 'success');
  }

  /**
   * Normalize expiry date input to Y-m-d or null when blank/invalid.
   */
  private function _sanitize_expiry_date($raw)
  {
    $value = trim((string) $raw);
    if ($value === '' || $value === '0000-00-00') {
      return null;
    }

    // First, try strict Y-m-d (browser date inputs)
    $date = date_create_from_format('Y-m-d', $value);
    $errors = $date ? date_get_last_errors() : null;
    if ($date && isset($errors['error_count']) && $errors['error_count'] === 0 && $errors['warning_count'] === 0) {
      return $date->format('Y-m-d');
    }

    // Fallback: accept any strtotime-parsable date and normalize
    $ts = strtotime($value);
    if ($ts !== false) {
      return date('Y-m-d', $ts);
    }

    return null;
  }

  private function _pos_dashboard_data()
  {
    $settingsID = (int) ($this->session->userdata('settingsID') ?? 0);
    return $this->PosModel->getDashboardMetrics($settingsID);
  }

  private function _current_pos_actor()
  {
    $username = trim((string) $this->session->userdata('username'));
    $name = trim((string) $this->session->userdata('name'));
    $idNumber = trim((string) $this->session->userdata('IDNumber'));

    if ($name !== '') {
      return $name;
    }

    if ($username !== '') {
      return $username;
    }

    return $idNumber !== '' ? $idNumber : 'POS User';
  }

  private function _can_current_user_manage_pos_admin_actions()
  {
    $level = $this->_normalize_pos_role($this->session->userdata('level'));
    return in_array($level, ['admin', 'manager', 'pos admin', 'system administrator', 'super admin'], true);
  }

  private function _build_pos_list_summary($sales)
  {
    $summary = [
      'count' => 0,
      'gross_total' => 0.0,
      'paid_total' => 0.0,
      'balance_total' => 0.0,
      'installment_total' => 0,
    ];

    if (!is_array($sales)) {
      return $summary;
    }

    foreach ($sales as $sale) {
      $summary['count']++;
      $summary['gross_total'] += (float) ($sale->grand_total ?? 0);
      $summary['paid_total'] += (float) ($sale->amount_paid ?? 0);
      $summary['balance_total'] += (float) ($sale->balance_due ?? 0);

      if (strtolower(trim((string) ($sale->payment_term ?? ''))) === 'installment') {
        $summary['installment_total']++;
      }
    }

    $summary['gross_total'] = round($summary['gross_total'], 2);
    $summary['paid_total'] = round($summary['paid_total'], 2);
    $summary['balance_total'] = round($summary['balance_total'], 2);

    return $summary;
  }

  private function _normalize_pos_date_range($dateFrom, $dateTo)
  {
    $dateFrom = $dateFrom ?: date('Y-m-01');
    $dateTo = $dateTo ?: date('Y-m-d');

    if ($dateFrom > $dateTo) {
      $swap = $dateFrom;
      $dateFrom = $dateTo;
      $dateTo = $swap;
    }

    return [$dateFrom, $dateTo];
  }
  public function getDashboardDetails()
  {
    header('Content-Type: application/json');

    if (!$this->_ensure_pos_user(['POS Staff', 'POS Admin'])) {
      echo json_encode(['success' => false, 'message' => 'Unauthorized']);
      return;
    }

    $type = $this->input->post('type');
    if (!$type) {
      echo json_encode(['success' => false, 'message' => 'Type is required']);
      return;
    }

    $settingsID = (int) ($this->session->userdata('settingsID') ?? 0);

    switch ($type) {
      case 'sales-today':
        $data = $this->_salesDetails($settingsID, date('Y-m-d'), date('Y-m-d'));
        break;
      case 'sales-week':
        $data = $this->_salesDetails($settingsID, date('Y-m-d', strtotime('-6 days')), date('Y-m-d'));
        break;
      case 'active-products':
        $data = $this->_productDetails($settingsID, 'active');
        break;
      case 'expired-products':
        $data = $this->_productDetails($settingsID, 'expired');
        break;
      case 'low-stock':
        $data = $this->_productDetails($settingsID, 'low-stock');
        break;
      case 'expiring-soon':
        $data = $this->_productDetails($settingsID, 'expiring-soon');
        break;
      default:
        echo json_encode(['success' => false, 'message' => 'Invalid type']);
        return;
    }

    echo json_encode(['success' => true, 'data' => $data]);
  }

  private function _salesDetails($settingsID, $dateFrom, $dateTo)
  {
    $sales = $this->PosModel->getSales($settingsID, [
      'date_from' => $dateFrom,
      'date_to' => $dateTo,
      'include_voided' => false,
    ]);

    $result = [];
    foreach ($sales as $row) {
      $dateTime = trim((string) ($row->transaction_date ?? '')) . ' ' . trim((string) ($row->transaction_time ?? '00:00:00'));
      $result[] = [
        'id' => $row->sale_no ?? $row->id,
        'date' => date('M j, Y h:i A', strtotime($dateTime)),
        'customer' => $row->customer_name ?? 'Walk-in Customer',
        'amount' => number_format((float) ($row->grand_total ?? 0), 2),
        'status' => $row->status ?? 'Unpaid',
        'view_url' => base_url('Pos/posTransactionDetail/' . (int) ($row->id ?? 0)),
      ];
    }

    return $result;
  }

  private function _productDetails($settingsID, $mode)
  {
    $today = date('Y-m-d');
    $todayPlus30 = date('Y-m-d', strtotime('+30 days'));

    $this->db
      ->select('id, name, category, stock_qty, reorder_level, expiry_date')
      ->from('POS_products')
      ->where('settingsID', (int) $settingsID)
      ->where('status', 'active');

    switch ($mode) {
      case 'expired':
        $this->db
          ->where('expiry_date IS NOT NULL', null, false)
          ->where('expiry_date <', $today)
          ->where('expiry_date !=', '0000-00-00');
        $status = 'Expired';
        break;
      case 'low-stock':
        $this->db->where('stock_qty <= reorder_level', null, false);
        $status = 'Low Stock';
        break;
      case 'expiring-soon':
        $this->db
          ->where('expiry_date IS NOT NULL', null, false)
          ->where('expiry_date >=', $today)
          ->where('expiry_date <=', $todayPlus30)
          ->where('expiry_date !=', '0000-00-00');
        $status = 'Expiring Soon';
        break;
      default:
        $status = 'Active';
        break;
    }

    $rows = $this->db
      ->order_by($mode === 'low-stock' ? 'stock_qty' : 'name', 'ASC')
      ->get()
      ->result();

    $result = [];
    foreach ($rows as $row) {
      $expiryDate = trim((string) ($row->expiry_date ?? ''));
      $result[] = [
        'name' => $row->name ?? '',
        'category' => $row->category ?? '',
        'stock' => (int) ($row->stock_qty ?? 0),
        'reorder_level' => (int) ($row->reorder_level ?? 0),
        'expiry_date' => ($expiryDate !== '' && $expiryDate !== '0000-00-00') ? date('M j, Y', strtotime($expiryDate)) : '-',
        'status' => $status,
        'edit_url' => base_url('Pos/posEditProduct/' . (int) ($row->id ?? 0)),
      ];
    }

    return $result;
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
}
