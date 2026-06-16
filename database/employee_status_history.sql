-- Employee Status History Table
-- Tracks all status changes for employees (HRIS feature)

CREATE TABLE IF NOT EXISTS `employee_status_history` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `empID` VARCHAR(45) NOT NULL,
  `settingsID` INT NOT NULL,
  `old_status` VARCHAR(45) DEFAULT NULL,
  `new_status` VARCHAR(45) NOT NULL,
  `changed_by` INT NOT NULL COMMENT 'User ID who made the change',
  `changed_by_username` VARCHAR(100) NOT NULL,
  `change_reason` TEXT DEFAULT NULL COMMENT 'Optional reason for status change',
  `change_date` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_empID` (`empID`),
  INDEX `idx_settingsID` (`settingsID`),
  INDEX `idx_change_date` (`change_date`),
  FOREIGN KEY (`settingsID`) REFERENCES `settings`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
