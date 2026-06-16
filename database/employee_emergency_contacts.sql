-- Employee Emergency Contacts Table
-- Tracks emergency contacts for employees (HRIS feature)

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
  `is_primary` TINYINT(1) DEFAULT 0 COMMENT '1 if primary emergency contact',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_empID` (`empID`),
  INDEX `idx_settingsID` (`settingsID`),
  INDEX `idx_is_primary` (`is_primary`),
  FOREIGN KEY (`settingsID`) REFERENCES `settings`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
