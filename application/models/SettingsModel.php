<?php
defined('BASEPATH') or exit('No direct script access allowed');

class SettingsModel extends CI_Model
{
    private $defaultInvoiceUnits = [
        'each',
        'day',
        'week',
        'month',
        'year',
        'pcs',
        'lot',
        'unit',
        'meter',
    ];

    public function __construct()
    {
        parent::__construct();
    }

    public function getSectionList()
    {
        if ($this->db->table_exists('sections')) {
            return $this->db->get('sections')->result();
        }
        return [];
    }

    public function getDepartmentList($settingsID = null)
    {
        $table = 'pos_departments';

        if (!$this->db->table_exists($table)) {
            return [];
        }

        if ($settingsID !== null) {
            $this->db->where('settingsID', (int)$settingsID);
        }

        return $this->db
            ->order_by('DeptName', 'ASC')
            ->get($table)
            ->result();
    }

    public function getSchoolInfo()
    {
        if ($this->db->table_exists('srms_settings')) {
            return $this->db->get('srms_settings')->result();
        }
        return [];
    }

    public function getPosSettings($settingsID)
    {
        if ($this->db->table_exists('pos_settings')) {
            return $this->db->get_where('pos_settings', ['settingsID' => $settingsID])->result();
        }
        return [];
    }

    public function ensureInvoiceUnitsTable()
    {
        if ($this->db->table_exists('invoice_units')) {
            return;
        }

        $this->db->query("
            CREATE TABLE IF NOT EXISTS `invoice_units` (
                `unitID` int unsigned NOT NULL AUTO_INCREMENT,
                `settingsID` int unsigned NOT NULL,
                `unitName` varchar(50) NOT NULL,
                `createdAt` datetime DEFAULT NULL,
                `updatedAt` datetime DEFAULT NULL,
                PRIMARY KEY (`unitID`),
                KEY `idx_invoice_units_settings` (`settingsID`),
                KEY `idx_invoice_units_name` (`unitName`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci
        ");
    }

    public function seedDefaultInvoiceUnits($settingsID)
    {
        $settingsID = (int) $settingsID;
        if ($settingsID <= 0) {
            return;
        }

        $this->ensureInvoiceUnitsTable();

        $existingCount = (int) $this->db
            ->where('settingsID', $settingsID)
            ->from('invoice_units')
            ->count_all_results();

        if ($existingCount > 0) {
            return;
        }

        $timestamp = date('Y-m-d H:i:s');
        foreach ($this->defaultInvoiceUnits as $unitName) {
            $this->db->insert('invoice_units', [
                'settingsID' => $settingsID,
                'unitName' => $unitName,
                'createdAt' => $timestamp,
                'updatedAt' => $timestamp,
            ]);
        }
    }

    public function getInvoiceUnits($settingsID)
    {
        $settingsID = (int) $settingsID;
        if ($settingsID <= 0) {
            return [];
        }

        $this->ensureInvoiceUnitsTable();
        $this->seedDefaultInvoiceUnits($settingsID);

        return $this->db
            ->where('settingsID', $settingsID)
            ->order_by('unitName', 'ASC')
            ->get('invoice_units')
            ->result();
    }

    public function getInvoiceUnitById($unitID, $settingsID)
    {
        $this->ensureInvoiceUnitsTable();

        return $this->db
            ->where('unitID', (int) $unitID)
            ->where('settingsID', (int) $settingsID)
            ->get('invoice_units', 1)
            ->row();
    }

    public function invoiceUnitExists($settingsID, $unitName, $excludeId = null)
    {
        $settingsID = (int) $settingsID;
        $unitName = strtolower(trim((string) $unitName));

        if ($settingsID <= 0 || $unitName === '') {
            return false;
        }

        $this->ensureInvoiceUnitsTable();

        $this->db
            ->from('invoice_units')
            ->where('settingsID', $settingsID)
            ->where('LOWER(unitName) =', $unitName);

        if ($excludeId !== null) {
            $this->db->where('unitID !=', (int) $excludeId);
        }

        return $this->db->count_all_results() > 0;
    }

    public function saveInvoiceUnit($settingsID, $unitName, $unitID = null)
    {
        $settingsID = (int) $settingsID;
        $unitName = strtolower(trim((string) $unitName));

        if ($settingsID <= 0 || $unitName === '') {
            return false;
        }

        $this->ensureInvoiceUnitsTable();
        $timestamp = date('Y-m-d H:i:s');

        $payload = [
            'settingsID' => $settingsID,
            'unitName' => $unitName,
            'updatedAt' => $timestamp,
        ];

        if ($unitID !== null && (int) $unitID > 0) {
            $this->db
                ->where('unitID', (int) $unitID)
                ->where('settingsID', $settingsID)
                ->update('invoice_units', $payload);

            return (int) $unitID;
        }

        $payload['createdAt'] = $timestamp;
        $this->db->insert('invoice_units', $payload);
        return (int) $this->db->insert_id();
    }

    public function deleteInvoiceUnit($unitID, $settingsID)
    {
        $this->ensureInvoiceUnitsTable();

        return $this->db
            ->where('unitID', (int) $unitID)
            ->where('settingsID', (int) $settingsID)
            ->delete('invoice_units');
    }
}
