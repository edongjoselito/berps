CREATE TABLE IF NOT EXISTS `payroll_profiles` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `payroll_loans` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `payroll_cash_advances` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `payroll_runs` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `payroll_entries` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `payroll_entry_items` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
