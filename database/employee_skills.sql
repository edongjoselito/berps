-- Employee Skills and Certifications Table
-- Tracks skills and certifications for employees (HRIS feature)

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
  INDEX `idx_settingsID` (`settingsID`),
  INDEX `idx_skill_type` (`skill_type`),
  INDEX `idx_expiry_date` (`expiry_date`),
  FOREIGN KEY (`settingsID`) REFERENCES `settings`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
