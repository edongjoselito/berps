<?php
defined('BASEPATH') or exit('No direct script access allowed');

class PayrollModel extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->normalizeLegacyPayrollDates();
    }

    private function employeeNameExpression($alias = 'e')
    {
        return "TRIM(CONCAT(COALESCE({$alias}.lName, ''), ', ', COALESCE({$alias}.fName, '')))";
    }

    private function normalizeDate($value)
    {
        $value = trim((string) $value);
        if ($value === '' || $value === '0000-00-00') {
            return null;
        }

        $timestamp = strtotime($value);
        if ($timestamp === false) {
            return null;
        }

        return date('Y-m-d', $timestamp);
    }

    private function normalizeMoney($value)
    {
        $value = is_string($value) ? str_replace(',', '', trim($value)) : $value;
        if ($value === '' || $value === null) {
            return 0.0;
        }

        return round((float) $value, 2);
    }

    private function timestampNow()
    {
        return date('Y-m-d H:i:s');
    }

    private function normalizeLegacyPayrollDates()
    {
        if ($this->db->table_exists('payroll_loans')) {
            $this->db->query("UPDATE `payroll_loans` SET `startDate` = NULL WHERE `startDate` IS NOT NULL AND (`startDate` + 0) = 0");
            $this->db->query("UPDATE `payroll_loans` SET `endDate` = NULL WHERE `endDate` IS NOT NULL AND (`endDate` + 0) = 0");
        }

        if ($this->db->table_exists('payroll_cash_advances')) {
            $this->db->query("UPDATE `payroll_cash_advances` SET `advanceDate` = NULL WHERE `advanceDate` IS NOT NULL AND (`advanceDate` + 0) = 0");
        }
    }

    public function ensurePayrollTables()
    {
        if (!$this->db->table_exists('payroll_profiles')) {
            $this->db->query("
                CREATE TABLE `payroll_profiles` (
                    `profileID` int unsigned NOT NULL AUTO_INCREMENT,
                    `settingsID` int unsigned NOT NULL,
                    `empID` varchar(45) NOT NULL,
                    `monthlySalary` decimal(12,2) NOT NULL DEFAULT 0.00,
                    `philhealthAmount` decimal(12,2) NOT NULL DEFAULT 0.00,
                    `sssAmount` decimal(12,2) NOT NULL DEFAULT 0.00,
                    `pagibigAmount` decimal(12,2) NOT NULL DEFAULT 0.00,
                    `payFrequency` varchar(20) NOT NULL DEFAULT 'monthly',
                    `payrollStatus` varchar(20) NOT NULL DEFAULT 'active',
                    `notes` varchar(255) DEFAULT NULL,
                    `createdAt` datetime DEFAULT CURRENT_TIMESTAMP,
                    `updatedAt` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    PRIMARY KEY (`profileID`),
                    UNIQUE KEY `uniq_payroll_profiles_settings_emp` (`settingsID`, `empID`),
                    KEY `idx_payroll_profiles_settings_status` (`settingsID`, `payrollStatus`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
            ");
        }

        if (!$this->db->table_exists('payroll_loans')) {
            $this->db->query("
                CREATE TABLE `payroll_loans` (
                    `loanID` int unsigned NOT NULL AUTO_INCREMENT,
                    `settingsID` int unsigned NOT NULL,
                    `empID` varchar(45) NOT NULL,
                    `loanType` varchar(60) NOT NULL DEFAULT 'SSS Loan',
                    `principalAmount` decimal(12,2) NOT NULL DEFAULT 0.00,
                    `balanceAmount` decimal(12,2) NOT NULL DEFAULT 0.00,
                    `monthlyDeduction` decimal(12,2) NOT NULL DEFAULT 0.00,
                    `startDate` date DEFAULT NULL,
                    `endDate` date DEFAULT NULL,
                    `status` varchar(20) NOT NULL DEFAULT 'active',
                    `notes` varchar(255) DEFAULT NULL,
                    `createdBy` varchar(120) DEFAULT NULL,
                    `createdAt` datetime DEFAULT CURRENT_TIMESTAMP,
                    `updatedAt` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    PRIMARY KEY (`loanID`),
                    KEY `idx_payroll_loans_settings_emp_status` (`settingsID`, `empID`, `status`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
            ");
        }

        if (!$this->db->table_exists('payroll_cash_advances')) {
            $this->db->query("
                CREATE TABLE `payroll_cash_advances` (
                    `advanceID` int unsigned NOT NULL AUTO_INCREMENT,
                    `settingsID` int unsigned NOT NULL,
                    `empID` varchar(45) NOT NULL,
                    `advanceDate` date DEFAULT NULL,
                    `amount` decimal(12,2) NOT NULL DEFAULT 0.00,
                    `balanceAmount` decimal(12,2) NOT NULL DEFAULT 0.00,
                    `deductionPerPayroll` decimal(12,2) NOT NULL DEFAULT 0.00,
                    `status` varchar(20) NOT NULL DEFAULT 'active',
                    `reason` varchar(255) DEFAULT NULL,
                    `createdBy` varchar(120) DEFAULT NULL,
                    `createdAt` datetime DEFAULT CURRENT_TIMESTAMP,
                    `updatedAt` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    PRIMARY KEY (`advanceID`),
                    KEY `idx_payroll_advances_settings_emp_status` (`settingsID`, `empID`, `status`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
            ");
        }

        if (!$this->db->table_exists('payroll_runs')) {
            $this->db->query("
                CREATE TABLE `payroll_runs` (
                    `payrollID` int unsigned NOT NULL AUTO_INCREMENT,
                    `settingsID` int unsigned NOT NULL,
                    `periodStart` date NOT NULL,
                    `periodEnd` date NOT NULL,
                    `payDate` date NOT NULL,
                    `status` varchar(20) NOT NULL DEFAULT 'posted',
                    `notes` text DEFAULT NULL,
                    `employeeCount` int unsigned NOT NULL DEFAULT 0,
                    `totalGross` decimal(12,2) NOT NULL DEFAULT 0.00,
                    `totalDeductions` decimal(12,2) NOT NULL DEFAULT 0.00,
                    `totalNet` decimal(12,2) NOT NULL DEFAULT 0.00,
                    `createdBy` varchar(120) DEFAULT NULL,
                    `createdAt` datetime DEFAULT CURRENT_TIMESTAMP,
                    `updatedAt` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    PRIMARY KEY (`payrollID`),
                    KEY `idx_payroll_runs_settings_date` (`settingsID`, `payDate`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
            ");
        }

        if (!$this->db->table_exists('payroll_entries')) {
            $this->db->query("
                CREATE TABLE `payroll_entries` (
                    `entryID` int unsigned NOT NULL AUTO_INCREMENT,
                    `payrollID` int unsigned NOT NULL,
                    `settingsID` int unsigned NOT NULL,
                    `empID` varchar(45) NOT NULL,
                    `employeeName` varchar(180) NOT NULL,
                    `payFrequency` varchar(20) NOT NULL DEFAULT 'monthly',
                    `monthlySalary` decimal(12,2) NOT NULL DEFAULT 0.00,
                    `grossPay` decimal(12,2) NOT NULL DEFAULT 0.00,
                    `philhealthAmount` decimal(12,2) NOT NULL DEFAULT 0.00,
                    `sssAmount` decimal(12,2) NOT NULL DEFAULT 0.00,
                    `pagibigAmount` decimal(12,2) NOT NULL DEFAULT 0.00,
                    `loanAmount` decimal(12,2) NOT NULL DEFAULT 0.00,
                    `cashAdvanceAmount` decimal(12,2) NOT NULL DEFAULT 0.00,
                    `otherDeductionAmount` decimal(12,2) NOT NULL DEFAULT 0.00,
                    `totalDeductions` decimal(12,2) NOT NULL DEFAULT 0.00,
                    `netPay` decimal(12,2) NOT NULL DEFAULT 0.00,
                    `remarks` varchar(255) DEFAULT NULL,
                    `createdAt` datetime DEFAULT CURRENT_TIMESTAMP,
                    PRIMARY KEY (`entryID`),
                    KEY `idx_payroll_entries_payroll_emp` (`payrollID`, `empID`),
                    KEY `idx_payroll_entries_settings_emp` (`settingsID`, `empID`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
            ");
        }

        if (!$this->db->table_exists('payroll_entry_items')) {
            $this->db->query("
                CREATE TABLE `payroll_entry_items` (
                    `itemID` int unsigned NOT NULL AUTO_INCREMENT,
                    `entryID` int unsigned NOT NULL,
                    `payrollID` int unsigned NOT NULL,
                    `settingsID` int unsigned NOT NULL,
                    `empID` varchar(45) NOT NULL,
                    `itemType` varchar(30) NOT NULL,
                    `itemLabel` varchar(120) NOT NULL,
                    `referenceID` int unsigned DEFAULT NULL,
                    `amount` decimal(12,2) NOT NULL DEFAULT 0.00,
                    `createdAt` datetime DEFAULT CURRENT_TIMESTAMP,
                    PRIMARY KEY (`itemID`),
                    KEY `idx_payroll_entry_items_entry_type` (`entryID`, `itemType`),
                    KEY `idx_payroll_entry_items_payroll` (`payrollID`),
                    KEY `idx_payroll_entry_items_settings_type` (`settingsID`, `itemType`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
            ");
        }

        $this->normalizeLegacyPayrollDates();
    }

    public function getPayrollEmployees($settingsID)
    {
        $settingsID = (int) $settingsID;
        $nameExpr = $this->employeeNameExpression('e');

        $sql = "
            SELECT
                e.empID,
                e.fName,
                e.mName,
                e.lName,
                {$nameExpr} AS employeeName,
                e.dateHired,
                e.empPosition,
                e.department,
                e.empStat,
                p.profileID,
                COALESCE(p.monthlySalary, 0) AS monthlySalary,
                COALESCE(p.philhealthAmount, 0) AS philhealthAmount,
                COALESCE(p.sssAmount, 0) AS sssAmount,
                COALESCE(p.pagibigAmount, 0) AS pagibigAmount,
                COALESCE(p.payFrequency, 'monthly') AS payFrequency,
                COALESCE(p.payrollStatus, '') AS payrollStatus,
                COALESCE(p.notes, '') AS payrollNotes,
                COALESCE(ls.loanBalance, 0) AS loanBalance,
                COALESCE(ls.loanMonthlyDeduction, 0) AS loanMonthlyDeduction,
                COALESCE(ls.loanCount, 0) AS activeLoanCount,
                COALESCE(cs.advanceBalance, 0) AS advanceBalance,
                COALESCE(cs.advanceDeduction, 0) AS advanceDeduction,
                COALESCE(cs.advanceCount, 0) AS activeAdvanceCount
            FROM employee e
            LEFT JOIN payroll_profiles p
                ON p.settingsID = e.settingsID
                AND p.empID = e.empID
            LEFT JOIN (
                SELECT
                    settingsID,
                    empID,
                    COUNT(*) AS loanCount,
                    SUM(balanceAmount) AS loanBalance,
                    SUM(monthlyDeduction) AS loanMonthlyDeduction
                FROM payroll_loans
                WHERE status = 'active' AND balanceAmount > 0
                GROUP BY settingsID, empID
            ) ls
                ON ls.settingsID = e.settingsID
                AND ls.empID = e.empID
            LEFT JOIN (
                SELECT
                    settingsID,
                    empID,
                    COUNT(*) AS advanceCount,
                    SUM(balanceAmount) AS advanceBalance,
                    SUM(deductionPerPayroll) AS advanceDeduction
                FROM payroll_cash_advances
                WHERE status = 'active' AND balanceAmount > 0
                GROUP BY settingsID, empID
            ) cs
                ON cs.settingsID = e.settingsID
                AND cs.empID = e.empID
            WHERE e.settingsID = ?
              AND e.empStat = 'Active'
            ORDER BY e.lName ASC, e.fName ASC, e.empID ASC
        ";

        return $this->db->query($sql, array($settingsID))->result();
    }

    public function getPayrollProfile($settingsID, $empID)
    {
        return $this->db
            ->where('settingsID', (int) $settingsID)
            ->where('empID', (string) $empID)
            ->get('payroll_profiles', 1)
            ->row();
    }

    public function savePayrollProfile($settingsID, $empID, array $data)
    {
        $payload = array(
            'settingsID' => (int) $settingsID,
            'empID' => trim((string) $empID),
            'monthlySalary' => $this->normalizeMoney($data['monthlySalary'] ?? 0),
            'philhealthAmount' => $this->normalizeMoney($data['philhealthAmount'] ?? 0),
            'sssAmount' => $this->normalizeMoney($data['sssAmount'] ?? 0),
            'pagibigAmount' => $this->normalizeMoney($data['pagibigAmount'] ?? 0),
            'payFrequency' => 'monthly',
            'payrollStatus' => !empty($data['payrollStatus']) ? trim((string) $data['payrollStatus']) : 'active',
            'notes' => trim((string) ($data['notes'] ?? '')),
        );

        $existing = $this->getPayrollProfile($settingsID, $empID);
        if ($existing) {
            $this->db
                ->where('profileID', (int) $existing->profileID)
                ->update('payroll_profiles', $payload);
            return (int) $existing->profileID;
        }

        $this->db->insert('payroll_profiles', $payload);
        return (int) $this->db->insert_id();
    }

    public function createLoan($settingsID, array $data, $createdBy = 'System')
    {
        $payload = array(
            'settingsID' => (int) $settingsID,
            'empID' => trim((string) ($data['empID'] ?? '')),
            'loanType' => trim((string) ($data['loanType'] ?? 'SSS Loan')) ?: 'SSS Loan',
            'principalAmount' => $this->normalizeMoney($data['principalAmount'] ?? 0),
            'balanceAmount' => $this->normalizeMoney($data['balanceAmount'] ?? ($data['principalAmount'] ?? 0)),
            'monthlyDeduction' => $this->normalizeMoney($data['monthlyDeduction'] ?? 0),
            'startDate' => $this->normalizeDate($data['startDate'] ?? null),
            'endDate' => $this->normalizeDate($data['endDate'] ?? null),
            'status' => !empty($data['status']) ? trim((string) $data['status']) : 'active',
            'notes' => trim((string) ($data['notes'] ?? '')),
            'createdBy' => trim((string) $createdBy),
        );

        $this->db->insert('payroll_loans', $payload);
        return (int) $this->db->insert_id();
    }

    public function createCashAdvance($settingsID, array $data, $createdBy = 'System')
    {
        $payload = array(
            'settingsID' => (int) $settingsID,
            'empID' => trim((string) ($data['empID'] ?? '')),
            'advanceDate' => $this->normalizeDate($data['advanceDate'] ?? null) ?: date('Y-m-d'),
            'amount' => $this->normalizeMoney($data['amount'] ?? 0),
            'balanceAmount' => $this->normalizeMoney($data['balanceAmount'] ?? ($data['amount'] ?? 0)),
            'deductionPerPayroll' => $this->normalizeMoney($data['deductionPerPayroll'] ?? 0),
            'status' => !empty($data['status']) ? trim((string) $data['status']) : 'active',
            'reason' => trim((string) ($data['reason'] ?? '')),
            'createdBy' => trim((string) $createdBy),
        );

        $this->db->insert('payroll_cash_advances', $payload);
        return (int) $this->db->insert_id();
    }

    public function getPayrollLoans($settingsID, $empID = '', $status = 'all')
    {
        $settingsID = (int) $settingsID;
        $nameExpr = $this->employeeNameExpression('e');
        $sql = "
            SELECT
                l.*,
                {$nameExpr} AS employeeName,
                e.department,
                e.empPosition
            FROM payroll_loans l
            LEFT JOIN employee e
                ON e.settingsID = l.settingsID
                AND e.empID = l.empID
            WHERE l.settingsID = ?
        ";

        $params = array($settingsID);
        if ($empID !== '') {
            $sql .= " AND l.empID = ? ";
            $params[] = (string) $empID;
        }

        if ($status !== 'all') {
            $sql .= " AND l.status = ? ";
            $params[] = (string) $status;
        }

        $sql .= " ORDER BY l.startDate DESC, l.loanID DESC ";
        return $this->db->query($sql, $params)->result();
    }

    public function getPayrollCashAdvances($settingsID, $empID = '', $status = 'all')
    {
        $settingsID = (int) $settingsID;
        $nameExpr = $this->employeeNameExpression('e');
        $sql = "
            SELECT
                a.*,
                {$nameExpr} AS employeeName,
                e.department,
                e.empPosition
            FROM payroll_cash_advances a
            LEFT JOIN employee e
                ON e.settingsID = a.settingsID
                AND e.empID = a.empID
            WHERE a.settingsID = ?
        ";

        $params = array($settingsID);
        if ($empID !== '') {
            $sql .= " AND a.empID = ? ";
            $params[] = (string) $empID;
        }

        if ($status !== 'all') {
            $sql .= " AND a.status = ? ";
            $params[] = (string) $status;
        }

        $sql .= " ORDER BY a.advanceDate DESC, a.advanceID DESC ";
        return $this->db->query($sql, $params)->result();
    }

    public function getPayrollRuns($settingsID)
    {
        return $this->db
            ->where('settingsID', (int) $settingsID)
            ->order_by('payDate', 'DESC')
            ->order_by('payrollID', 'DESC')
            ->get('payroll_runs')
            ->result();
    }

    public function getPayrollRunsByDateRange($settingsID, $from = null, $to = null, $status = 'posted')
    {
        $this->db->where('settingsID', (int) $settingsID);

        if ($status !== 'all') {
            $this->db->where('status', (string) $status);
        }

        if ($from !== null) {
            $this->db->where('payDate >=', $from);
        }

        if ($to !== null) {
            $this->db->where('payDate <=', $to);
        }

        return $this->db
            ->order_by('payDate', 'DESC')
            ->order_by('payrollID', 'DESC')
            ->get('payroll_runs')
            ->result();
    }

    public function getPayrollRun($settingsID, $payrollID)
    {
        return $this->db
            ->where('settingsID', (int) $settingsID)
            ->where('payrollID', (int) $payrollID)
            ->get('payroll_runs', 1)
            ->row();
    }

    public function getPayrollEntries($settingsID, $payrollID)
    {
        return $this->db
            ->where('settingsID', (int) $settingsID)
            ->where('payrollID', (int) $payrollID)
            ->order_by('employeeName', 'ASC')
            ->order_by('entryID', 'ASC')
            ->get('payroll_entries')
            ->result();
    }

    public function getPayrollEntry($settingsID, $entryID)
    {
        return $this->db
            ->select('pe.*, pr.periodStart, pr.periodEnd, pr.payDate, pr.notes AS payrollNotes, pr.createdBy AS payrollCreatedBy', false)
            ->from('payroll_entries pe')
            ->join('payroll_runs pr', 'pr.payrollID = pe.payrollID AND pr.settingsID = pe.settingsID', 'inner')
            ->where('pe.settingsID', (int) $settingsID)
            ->where('pe.entryID', (int) $entryID)
            ->get()
            ->row();
    }

    public function getPayrollEntryItems($settingsID, $entryID)
    {
        return $this->db
            ->where('settingsID', (int) $settingsID)
            ->where('entryID', (int) $entryID)
            ->order_by('itemID', 'ASC')
            ->get('payroll_entry_items')
            ->result();
    }

    public function getDeductionSummary($settingsID, $from = null, $to = null, $payrollID = null)
    {
        $this->db
            ->select('pei.itemType, pei.itemLabel, SUM(pei.amount) AS totalAmount, COUNT(*) AS itemCount', false)
            ->from('payroll_entry_items pei')
            ->join('payroll_runs pr', 'pr.payrollID = pei.payrollID AND pr.settingsID = pei.settingsID', 'inner')
            ->where('pei.settingsID', (int) $settingsID);

        if ($payrollID !== null) {
            $this->db->where('pei.payrollID', (int) $payrollID);
        }

        if ($from !== null) {
            $this->db->where('pr.payDate >=', $from);
        }

        if ($to !== null) {
            $this->db->where('pr.payDate <=', $to);
        }

        $rows = $this->db
            ->group_by(array('pei.itemType', 'pei.itemLabel'))
            ->order_by('pei.itemType', 'ASC')
            ->order_by('totalAmount', 'DESC')
            ->get()
            ->result();

        $summary = array(
            'philhealth' => 0.0,
            'sss' => 0.0,
            'pagibig' => 0.0,
            'loan' => 0.0,
            'cash_advance' => 0.0,
            'other' => 0.0,
            'lines' => array(),
        );

        foreach ($rows as $row) {
            $type = trim((string) ($row->itemType ?? 'other'));
            $amount = round((float) ($row->totalAmount ?? 0), 2);
            if (!isset($summary[$type])) {
                $summary[$type] = 0.0;
            }
            $summary[$type] += $amount;
            $summary['lines'][] = array(
                'itemType' => $type,
                'itemLabel' => trim((string) ($row->itemLabel ?? $type)),
                'itemCount' => (int) ($row->itemCount ?? 0),
                'totalAmount' => $amount,
            );
        }

        $summary['totalDeductions'] = round(
            (float) $summary['philhealth'] +
            (float) $summary['sss'] +
            (float) $summary['pagibig'] +
            (float) $summary['loan'] +
            (float) $summary['cash_advance'] +
            (float) $summary['other'],
            2
        );

        return $summary;
    }

    private function fetchProfilesForRun($settingsID)
    {
        $settingsID = (int) $settingsID;
        $nameExpr = $this->employeeNameExpression('e');

        $sql = "
            SELECT
                e.empID,
                {$nameExpr} AS employeeName,
                e.department,
                e.empPosition,
                p.profileID,
                COALESCE(p.monthlySalary, 0) AS monthlySalary,
                COALESCE(p.philhealthAmount, 0) AS philhealthAmount,
                COALESCE(p.sssAmount, 0) AS sssAmount,
                COALESCE(p.pagibigAmount, 0) AS pagibigAmount,
                COALESCE(p.payFrequency, 'monthly') AS payFrequency,
                COALESCE(p.notes, '') AS profileNotes
            FROM payroll_profiles p
            INNER JOIN employee e
                ON e.settingsID = p.settingsID
                AND e.empID = p.empID
            WHERE p.settingsID = ?
              AND p.payrollStatus = 'active'
              AND e.empStat = 'Active'
              AND COALESCE(p.monthlySalary, 0) > 0
            ORDER BY e.lName ASC, e.fName ASC, e.empID ASC
        ";

        return $this->db->query($sql, array($settingsID))->result();
    }

    private function fetchActiveLoansForEmployee($settingsID, $empID, $payDate)
    {
        $this->db
            ->where('settingsID', (int) $settingsID)
            ->where('empID', (string) $empID)
            ->where('status', 'active')
            ->where('balanceAmount >', 0)
            ->group_start()
                ->where('startDate IS NULL', null, false)
                ->or_where('(startDate + 0) = 0', null, false)
                ->or_where('startDate <=', $payDate)
            ->group_end()
            ->group_start()
                ->where('endDate IS NULL', null, false)
                ->or_where('(endDate + 0) = 0', null, false)
                ->or_where('endDate >=', $payDate)
            ->group_end()
            ->order_by('startDate', 'ASC')
            ->order_by('loanID', 'ASC');

        return $this->db->get('payroll_loans')->result();
    }

    private function fetchActiveCashAdvancesForEmployee($settingsID, $empID, $payDate)
    {
        $this->db
            ->where('settingsID', (int) $settingsID)
            ->where('empID', (string) $empID)
            ->where('status', 'active')
            ->where('balanceAmount >', 0)
            ->where('advanceDate <=', $payDate)
            ->order_by('advanceDate', 'ASC')
            ->order_by('advanceID', 'ASC');

        return $this->db->get('payroll_cash_advances')->result();
    }

    public function createPayrollRun($settingsID, array $payload, $createdBy = 'System')
    {
        $settingsID = (int) $settingsID;
        if ($settingsID <= 0) {
            return array('success' => false, 'error' => 'Missing payroll business context.');
        }

        $periodStart = $this->normalizeDate($payload['periodStart'] ?? null);
        $periodEnd = $this->normalizeDate($payload['periodEnd'] ?? null);
        $payDate = $this->normalizeDate($payload['payDate'] ?? null);
        $notes = trim((string) ($payload['notes'] ?? ''));

        if ($periodStart === null || $periodEnd === null || $payDate === null) {
            return array('success' => false, 'error' => 'Payroll period start, end, and pay date are required.');
        }

        if (strtotime($periodStart) > strtotime($periodEnd)) {
            $swap = $periodStart;
            $periodStart = $periodEnd;
            $periodEnd = $swap;
        }

        $duplicate = $this->db
            ->where('settingsID', $settingsID)
            ->where('periodStart', $periodStart)
            ->where('periodEnd', $periodEnd)
            ->where('payDate', $payDate)
            ->count_all_results('payroll_runs');

        if ($duplicate > 0) {
            return array('success' => false, 'error' => 'A payroll run with the same period and pay date already exists.');
        }

        $profiles = $this->fetchProfilesForRun($settingsID);
        if (empty($profiles)) {
            return array('success' => false, 'error' => 'No active payroll profiles with a monthly salary were found.');
        }

        $createdBy = trim((string) $createdBy) !== '' ? trim((string) $createdBy) : 'System';
        $timestamp = $this->timestampNow();

        $this->db->trans_begin();

        $this->db->insert('payroll_runs', array(
            'settingsID' => $settingsID,
            'periodStart' => $periodStart,
            'periodEnd' => $periodEnd,
            'payDate' => $payDate,
            'status' => 'posted',
            'notes' => $notes,
            'employeeCount' => 0,
            'totalGross' => 0,
            'totalDeductions' => 0,
            'totalNet' => 0,
            'createdBy' => $createdBy,
            'createdAt' => $timestamp,
        ));

        $payrollID = (int) $this->db->insert_id();
        $employeeCount = 0;
        $totalGross = 0.0;
        $totalDeductions = 0.0;
        $totalNet = 0.0;

        foreach ($profiles as $profile) {
            $empID = trim((string) ($profile->empID ?? ''));
            $employeeName = trim((string) ($profile->employeeName ?? ''));
            if ($empID === '') {
                continue;
            }

            $grossPay = $this->normalizeMoney($profile->monthlySalary ?? 0);
            $philhealth = $this->normalizeMoney($profile->philhealthAmount ?? 0);
            $sss = $this->normalizeMoney($profile->sssAmount ?? 0);
            $pagibig = $this->normalizeMoney($profile->pagibigAmount ?? 0);
            $loanAmount = 0.0;
            $cashAdvanceAmount = 0.0;
            $otherDeductionAmount = 0.0;
            $items = array();

            if ($philhealth > 0) {
                $items[] = array(
                    'itemType' => 'philhealth',
                    'itemLabel' => 'PhilHealth',
                    'referenceID' => null,
                    'amount' => $philhealth,
                );
            }

            if ($sss > 0) {
                $items[] = array(
                    'itemType' => 'sss',
                    'itemLabel' => 'SSS',
                    'referenceID' => null,
                    'amount' => $sss,
                );
            }

            if ($pagibig > 0) {
                $items[] = array(
                    'itemType' => 'pagibig',
                    'itemLabel' => 'Pag-IBIG',
                    'referenceID' => null,
                    'amount' => $pagibig,
                );
            }

            $availableRecovery = max(0, round($grossPay - ($philhealth + $sss + $pagibig), 2));

            foreach ($this->fetchActiveLoansForEmployee($settingsID, $empID, $payDate) as $loan) {
                if ($availableRecovery <= 0) {
                    break;
                }

                $balanceAmount = $this->normalizeMoney($loan->balanceAmount ?? 0);
                $scheduled = $this->normalizeMoney($loan->monthlyDeduction ?? 0);
                $applied = min($balanceAmount, $scheduled, $availableRecovery);
                if ($applied <= 0) {
                    continue;
                }

                $loanAmount += $applied;
                $availableRecovery = round($availableRecovery - $applied, 2);
                $items[] = array(
                    'itemType' => 'loan',
                    'itemLabel' => trim((string) ($loan->loanType ?? 'Loan')),
                    'referenceID' => (int) ($loan->loanID ?? 0),
                    'amount' => $applied,
                );

                $newBalance = round($balanceAmount - $applied, 2);
                $this->db
                    ->where('loanID', (int) $loan->loanID)
                    ->update('payroll_loans', array(
                        'balanceAmount' => $newBalance,
                        'status' => $newBalance <= 0.00001 ? 'paid' : 'active',
                    ));
            }

            foreach ($this->fetchActiveCashAdvancesForEmployee($settingsID, $empID, $payDate) as $advance) {
                if ($availableRecovery <= 0) {
                    break;
                }

                $balanceAmount = $this->normalizeMoney($advance->balanceAmount ?? 0);
                $scheduled = $this->normalizeMoney($advance->deductionPerPayroll ?? 0);
                $applied = min($balanceAmount, $scheduled, $availableRecovery);
                if ($applied <= 0) {
                    continue;
                }

                $cashAdvanceAmount += $applied;
                $availableRecovery = round($availableRecovery - $applied, 2);
                $items[] = array(
                    'itemType' => 'cash_advance',
                    'itemLabel' => 'Cash Advance',
                    'referenceID' => (int) ($advance->advanceID ?? 0),
                    'amount' => $applied,
                );

                $newBalance = round($balanceAmount - $applied, 2);
                $this->db
                    ->where('advanceID', (int) $advance->advanceID)
                    ->update('payroll_cash_advances', array(
                        'balanceAmount' => $newBalance,
                        'status' => $newBalance <= 0.00001 ? 'cleared' : 'active',
                    ));
            }

            $totalEntryDeductions = round($philhealth + $sss + $pagibig + $loanAmount + $cashAdvanceAmount + $otherDeductionAmount, 2);
            $netPay = round($grossPay - $totalEntryDeductions, 2);

            $this->db->insert('payroll_entries', array(
                'payrollID' => $payrollID,
                'settingsID' => $settingsID,
                'empID' => $empID,
                'employeeName' => $employeeName !== '' ? $employeeName : $empID,
                'payFrequency' => 'monthly',
                'monthlySalary' => $grossPay,
                'grossPay' => $grossPay,
                'philhealthAmount' => $philhealth,
                'sssAmount' => $sss,
                'pagibigAmount' => $pagibig,
                'loanAmount' => $loanAmount,
                'cashAdvanceAmount' => $cashAdvanceAmount,
                'otherDeductionAmount' => $otherDeductionAmount,
                'totalDeductions' => $totalEntryDeductions,
                'netPay' => $netPay,
                'remarks' => trim((string) ($profile->profileNotes ?? '')),
                'createdAt' => $timestamp,
            ));

            $entryID = (int) $this->db->insert_id();
            foreach ($items as $item) {
                $this->db->insert('payroll_entry_items', array(
                    'entryID' => $entryID,
                    'payrollID' => $payrollID,
                    'settingsID' => $settingsID,
                    'empID' => $empID,
                    'itemType' => $item['itemType'],
                    'itemLabel' => $item['itemLabel'],
                    'referenceID' => $item['referenceID'],
                    'amount' => $this->normalizeMoney($item['amount']),
                    'createdAt' => $timestamp,
                ));
            }

            $employeeCount++;
            $totalGross += $grossPay;
            $totalDeductions += $totalEntryDeductions;
            $totalNet += $netPay;
        }

        $this->db
            ->where('payrollID', $payrollID)
            ->update('payroll_runs', array(
                'employeeCount' => $employeeCount,
                'totalGross' => round($totalGross, 2),
                'totalDeductions' => round($totalDeductions, 2),
                'totalNet' => round($totalNet, 2),
            ));

        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            return array('success' => false, 'error' => 'Unable to generate payroll right now.');
        }

        $this->db->trans_commit();
        return array(
            'success' => true,
            'payrollID' => $payrollID,
            'employeeCount' => $employeeCount,
        );
    }
}
