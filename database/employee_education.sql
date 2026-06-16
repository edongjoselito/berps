-- Employee Education Table
-- Tracks educational background for employees (HRIS feature)

CREATE TABLE IF NOT EXISTS `employee_education` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `empID` VARCHAR(45) NOT NULL,
  `settingsID` INT NOT NULL,
  `institution_name` VARCHAR(255) NOT NULL,
  `degree` VARCHAR(255) NOT NULL,
  `field_of_study` VARCHAR(255) DEFAULT NULL,
  `start_date` DATE NOT NULL,
  `end_date` DATE DEFAULT NULL,
  `is_current` TINYINT(1) DEFAULT 0 COMMENT '1 if currently studying',
  `gpa` DECIMAL(3,2) DEFAULT NULL,
  `description` TEXT DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_empID` (`empID`),
  INDEX `idx_settingsID` (`settingsID`),
  INDEX `idx_end_date` (`end_date`),
  FOREIGN KEY (`settingsID`) REFERENCES `settings`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
