<?php
class Settings extends CI_Controller{
  function __construct(){
    parent::__construct();
    $this->load->database();
    $this->load->helper('url');
	$this->load->helper('url', 'form');	
	$this->load->library('form_validation');
    $this->load->model('StudentModel');
	$this->load->model('SettingsModel');
	
    if($this->session->userdata('logged_in') !== TRUE){
      redirect('login');
    }
  }

  // default landing: reuse existing section/department flows
  public function index()
  {
    // adjust to your preferred default; Department chosen here
    redirect('Settings/Department');
  }
  
  function Sections ()
  {
    if (!$this->db->table_exists('sections')) {
        $this->_render_settings_placeholder('Sections table is not available.');
        return;
    }

    $result['data']=$this->SettingsModel->getSectionList();
    $this->load->view('settings_sections',$result);
  	  
	if($this->input->post('submit'))
		{
		//get data from the form
		$Section=$this->input->post('Section');
		
		date_default_timezone_set('Asia/Manila'); # add your city to set local time zone
		$now = date('H:i:s A');
		$date=date("Y-m-d");
		$Password=sha1($this->input->post('BirthDate'));
		$Encoder=$this->session->userdata('username');
		
		$description='Encoded a section '.$Section;
		//check if record exist
		$que=$this->db->query("select * from sections where Section='".$Section."'");
		$row = $que->num_rows();
		if($row)
		{
        $this->session->set_flashdata('msg', '<div class="alert alert-danger text-center"><b>Duplicate entry.</b></div>');
        redirect('Settings/Sections');
		}
		else
		{
		//save section
		$que=$this->db->query("insert into sections values('','$Section')");
		$que=$this->db->query("insert into atrail values('','$description','$date','$now','$Encoder','')");
		$this->session->set_flashdata('msg', '<div class="alert alert-success text-center"><b>One record added successfully.</b></div>');
        redirect('Settings/Sections');
		}			
		} 
  }
  
  	//delete Section
	public function deleteSection()
	{
	$id=$this->input->get('id');
	$username=$this->session->userdata('username');
	date_default_timezone_set('Asia/Manila'); # add your city to set local time zone
	$now = date('H:i:s A');
	$date=date("Y-m-d");
	$query=$this->db->query("delete from sections where sectionID='".$id."'");
	$query=$this->db->query("insert into atrail values('','Deleted a Section','$date','$now','$username','$id')");
	redirect('Settings/Sections');
	}
  //delete Course
	public function deleteCourse()
	{
	$id=$this->input->get('id');
	$username=$this->session->userdata('username');
	date_default_timezone_set('Asia/Manila'); # add your city to set local time zone
	$now = date('H:i:s A');
	$date=date("Y-m-d");
	$query=$this->db->query("delete from course_table where courseid='".$id."'");
	$query=$this->db->query("insert into atrail values('','Deleted a Course','$date','$now','$username','$id')");
	redirect('Settings/Department');
	}
  function Department ()
  {
    if (!$this->_ensure_admin_access()) {
      return;
    }

    $deptTable = 'pos_departments';
    if (!$this->db->table_exists($deptTable)) {
        $this->_render_settings_placeholder('Department table is not available.');
        return;
    }

    $settingsID = (int)($this->session->userdata('settingsID') ?? 0);
    $this->_ensurePosActivationKeysTable();

	if($this->input->post('submit'))
		{
		//get data from the form
		$DeptCode=$this->input->post('DeptCode');
		$DeptName=$this->input->post('DeptName');
		$Location=$this->input->post('Location');
		$Notes=$this->input->post('Notes');
		$ActivationKey=$this->input->post('activation_key');

		date_default_timezone_set('Asia/Manila'); # add your city to set local time zone
		$now = date('H:i:s A');
		$date=date("Y-m-d");
		$Password=sha1($this->input->post('BirthDate'));
		$Encoder=$this->session->userdata('username');
		
		$description='Encoded a Branch/Cost Center '.$DeptName;

        if ($settingsID <= 0) {
            $this->session->set_flashdata('msg', '<div class="alert alert-danger text-center"><b>Company settings are missing.</b></div>');
            redirect('Settings/Department');
        }

        if (!$this->_findUnusedBranchActivationKey($settingsID, $ActivationKey)) {
            $this->session->set_flashdata('msg', '<div class="alert alert-danger text-center"><b>A valid unused activation key is required to add a branch.</b></div>');
            redirect('Settings/Department');
        }
		
		//check if record exist
		$exists = $this->db
            ->where('DeptName', $DeptName)
            ->where('settingsID', $settingsID)
            ->get($deptTable)
            ->num_rows();
		if($exists)
		{
		 //redirect('Page/notification_error');
        $this->session->set_flashdata('msg', '<div class="alert alert-danger text-center"><b>Duplicate entry.</b></div>');
        redirect('Settings/Department');
		}
		else
		{
		//save track
        $data = [
            'DeptCode'   => $DeptCode,
            'DeptName'   => $DeptName,
            'Location'   => $Location,
            'Notes'      => $Notes,
            'settingsID' => $settingsID,
        ];

        $this->db->trans_begin();
		$this->db->insert($deptTable, $data);
        $branchId = (int) $this->db->insert_id();
        $keyConsumed = $this->_consumeBranchActivationKey($settingsID, $ActivationKey, $branchId);

        if ($branchId <= 0 || !$keyConsumed || !$this->db->trans_status()) {
            $this->db->trans_rollback();
            $this->session->set_flashdata('msg', '<div class="alert alert-danger text-center"><b>Unable to add branch. Please verify the activation key.</b></div>');
            redirect('Settings/Department');
        }

		$this->db->query("insert into atrail values('','$description','$date','$now','$Encoder','')");
        $this->db->trans_commit();
	    $this->session->set_flashdata('msg', '<div class="alert alert-success text-center"><b>Branch added and activation key consumed successfully.</b></div>');
        redirect('Settings/Department');
		}			
		} 

    // reload fresh data after any post handling
    $result['data']=$this->SettingsModel->getDepartmentList($settingsID);
    $this->load->view('settings_department',$result);
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

    $columns = [
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
    ];

    foreach ($columns as $column => $sql) {
      if (!$this->db->field_exists($column, 'pos_activation_keys')) {
        $this->db->query($sql);
      }
    }
  }

  private function _normalizeBranchActivationKey($key)
  {
    return preg_replace('/[^A-Z0-9]/', '', strtoupper(trim((string) $key)));
  }

  private function _findUnusedBranchActivationKey($settingsID, $activationKey)
  {
    $normalized = $this->_normalizeBranchActivationKey($activationKey);
    if ($settingsID <= 0 || $normalized === '') {
      return null;
    }

    $now = date('Y-m-d H:i:s');
    return $this->db
      ->where('settingsID', (int) $settingsID)
      ->where('key_hash', hash('sha256', $normalized))
      ->where('status', 'unused')
      ->where('(expires_at IS NULL OR expires_at >= ' . $this->db->escape($now) . ')', null, false)
      ->get('pos_activation_keys', 1)
      ->row();
  }

  private function _consumeBranchActivationKey($settingsID, $activationKey, $branchId)
  {
    $key = $this->_findUnusedBranchActivationKey($settingsID, $activationKey);
    if (!$key || (int) $branchId <= 0) {
      return false;
    }

    $this->db
      ->where('id', (int) $key->id)
      ->where('status', 'unused')
      ->update('pos_activation_keys', [
        'status' => 'used',
        'branch_id' => (int) $branchId,
        'used_by' => (int) ($this->session->userdata('user_id') ?? 0),
        'used_at' => date('Y-m-d H:i:s'),
      ]);

    return $this->db->affected_rows() > 0;
  }

  public function InvoiceUnits()
  {
    if (!$this->_ensure_admin_access()) {
      return;
    }

    $settingsID = (int)($this->session->userdata('settingsID') ?? 0);
    if ($settingsID <= 0) {
      $this->_render_settings_placeholder('Company settings are not available for invoice units.');
      return;
    }

    if ($this->input->method() === 'post') {
      $unitID = (int) $this->input->post('unitID');
      $unitName = trim((string) $this->input->post('unitName'));

      if ($unitName === '') {
        $this->session->set_flashdata('danger', 'Unit name is required.');
        redirect($unitID > 0 ? ('Settings/InvoiceUnits?edit=' . $unitID) : 'Settings/InvoiceUnits');
        return;
      }

      if ($this->SettingsModel->invoiceUnitExists($settingsID, $unitName, $unitID > 0 ? $unitID : null)) {
        $this->session->set_flashdata('danger', 'That unit already exists.');
        redirect($unitID > 0 ? ('Settings/InvoiceUnits?edit=' . $unitID) : 'Settings/InvoiceUnits');
        return;
      }

      $savedID = $this->SettingsModel->saveInvoiceUnit($settingsID, $unitName, $unitID > 0 ? $unitID : null);
      if ($savedID > 0) {
        $this->session->set_flashdata('success', $unitID > 0 ? 'Invoice unit updated successfully.' : 'Invoice unit added successfully.');
      } else {
        $this->session->set_flashdata('danger', 'Unable to save the invoice unit.');
      }

      redirect('Settings/InvoiceUnits');
      return;
    }

    $editID = (int) $this->input->get('edit');
    $editUnit = $editID > 0 ? $this->SettingsModel->getInvoiceUnitById($editID, $settingsID) : null;
    if ($editID > 0 && !$editUnit) {
      $this->session->set_flashdata('danger', 'Invoice unit not found.');
      redirect('Settings/InvoiceUnits');
      return;
    }

    $result['data'] = $this->SettingsModel->getInvoiceUnits($settingsID);
    $result['editUnit'] = $editUnit;
    $this->load->view('settings_invoice_units', $result);
  }

  public function deleteInvoiceUnit()
  {
    if (!$this->_ensure_admin_access()) {
      return;
    }

    $settingsID = (int)($this->session->userdata('settingsID') ?? 0);
    $unitID = (int) $this->input->get('id');

    if ($settingsID <= 0 || $unitID <= 0) {
      $this->session->set_flashdata('danger', 'Invalid invoice unit selected.');
      redirect('Settings/InvoiceUnits');
      return;
    }

    $units = $this->SettingsModel->getInvoiceUnits($settingsID);
    if (count($units) <= 1) {
      $this->session->set_flashdata('danger', 'Keep at least one invoice unit available for encoding.');
      redirect('Settings/InvoiceUnits');
      return;
    }

    $unit = $this->SettingsModel->getInvoiceUnitById($unitID, $settingsID);
    if (!$unit) {
      $this->session->set_flashdata('danger', 'Invoice unit not found.');
      redirect('Settings/InvoiceUnits');
      return;
    }

    if ($this->SettingsModel->deleteInvoiceUnit($unitID, $settingsID)) {
      $this->session->set_flashdata('success', 'Invoice unit deleted successfully.');
    } else {
      $this->session->set_flashdata('danger', 'Unable to delete the invoice unit.');
    }

    redirect('Settings/InvoiceUnits');
  }

  public function schoolInfo()
  {
    if (!$this->db->table_exists('srms_settings')) {
        $this->_render_settings_placeholder('School settings table is not available.');
        return;
    }

    $result['data']=$this->SettingsModel->getSchoolInfo();
    $this->load->view('settings_school_info',$result);
	  if($this->input->post('submit'))
		{
		$SchoolName=$this->input->post('SchoolName');
		$SchoolAddress=$this->input->post('SchoolAddress');
		$SchoolHead=$this->input->post('SchoolHead');
		$sHeadPosition=$this->input->post('sHeadPosition');	 
		$Registrar=$this->input->post('Registrar');	 
		$registrarPosition=$this->input->post('registrarPosition');
		$clerk=$this->input->post('clerk');
		$clerkPosition=$this->input->post('clerkPosition');
		$administrative=$this->input->post('administrative');
		$administrativePosition=$this->input->post('administrativePosition');
		$admissionOfficer=$this->input->post('admissionOfficer');
		$accountant=$this->input->post('accountant');
		$cashier=$this->input->post('cashier');
		$cashierPosition=$this->input->post('cashierPosition');
		$PropertyCustodian=$this->input->post('PropertyCustodian');
		$slogan=$this->input->post('slogan');
		
		$Encoder=$this->session->userdata('username');
		$updatedDate=date("Y-m-d");
		$updatedTime=date("h:i:s A") . "\n"; 
		 
		//save profile
		$que=$this->db->query("update srms_settings set SchoolName='$SchoolName',SchoolAddress='$SchoolAddress',SchoolHead='$SchoolHead',sHeadPosition='$sHeadPosition',Registrar='$Registrar',registrarPosition='$registrarPosition',clerk='$clerk',clerkPosition='$clerkPosition',administrative='$administrative',administrativePosition='$administrativePosition',cashier='$cashier',cashierPosition='$cashierPosition',admissionOfficer='$admissionOfficer',accountant='$accountant',PropertyCustodian='$PropertyCustodian',slogan='$slogan'");
		$que=$this->db->query("insert into atrail values('','Updated the School Info','$updatedDate','$updatedTime','$Encoder','')");
		$this->session->set_flashdata('msg', '<div class="alert alert-success text-center"><b>Updated successfully.</b></div>');
        redirect('Settings/schoolInfo');
		}			
		}


  public function loginFormBanner()
  {
	  $this->load->view('settings_login_image');
  }
  public function uploadloginFormImage() 
	{
		$config['upload_path'] = './upload/banners/';
        $config['allowed_types'] = 'jpg|gif|png';
        $config['max_size'] = 15000;
        //$config['max_width'] = 1500;
        //$config['max_height'] = 1500;

        $this->load->library('upload', $config);

        if (!$this->upload->do_upload('nonoy')) 
		{
            $msg = array('error' => $this->upload->display_errors());

           $this->session->set_flashdata('msg', '<div class="alert alert-danger text-center"><b>Error uploading the file.</b></div>');
        } 
		else 
		{
            $data = array('image_metadata' => $this->upload->data());
			//get data from the form
			$username=$this->session->userdata('username');
			//$filename=$this->input->post('nonoy');
			$filename = $this->upload->data('file_name');
			
			$que=$this->db->query("update srms_settings set loginFormImage='$filename'");
			$this->session->set_flashdata('msg', '<div class="alert alert-success text-center"><b>Uploaded Succesfully!</b></div>');
			//$this->load->view('loginFormImage');
			redirect('Settings/loginFormBanner');
        }
    }

  private function _render_settings_placeholder($message = 'Settings module is not configured.')
  {
    $data = [
      'page_title' => 'Settings',
      'message' => $message,
    ];
    $this->load->view('settings_placeholder', $data);
  }

  private function _ensure_admin_access()
  {
    if (strtolower(trim((string) $this->session->userdata('level'))) === 'admin') {
      return true;
    }

    show_404();
    return false;
  }

}
