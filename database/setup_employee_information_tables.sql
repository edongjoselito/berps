-- Employee Information Management Tables Setup
-- Run this script to create all necessary tables for Employee Information Management

-- Employee Employment History Table
CREATE TABLE IF NOT EXISTS `employee_employment_history` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `empID` VARCHAR(45) NOT NULL,
  `settingsID` INT NOT NULL,
  `company_name` VARCHAR(255) NOT NULL,
  `position` VARCHAR(255) NOT NULL,
  `start_date` DATE NOT NULL,
  `end_date` DATE DEFAULT NULL,
  `is_current` TINYINT(1) DEFAULT 0 COMMENT '1 if this is current employment',
  `description` TEXT DEFAULT NULL,
  `reason_for_leaving` TEXT DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_empID` (`empID`),
  INDEX `idx_settingsID` (`settingsID`),
  INDEX `idx_start_date` (`start_date`),
  FOREIGN KEY (`settingsID`) REFERENCES `settings`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Employee Education Table
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

-- Employee Skills and Certifications Table
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

-- Employee Emergency Contacts Table
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

-- Employee Documents Table
CREATE TABLE IF NOT EXISTS `employee_documents` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `empID` VARCHAR(45) NOT NULL,
  `settingsID` INT NOT NULL,
  `document_type` VARCHAR(100) NOT NULL COMMENT 'e.g., Resume, Contract, ID, Certificate, etc.',
  `document_name` VARCHAR(255) NOT NULL,
  `file_path` VARCHAR(500) NOT NULL,
  `file_size` BIGINT DEFAULT NULL COMMENT 'File size in bytes',
  `file_mime_type` VARCHAR(100) DEFAULT NULL,
  `description` TEXT DEFAULT NULL,
  `issue_date` DATE DEFAULT NULL,
  `expiry_date` DATE DEFAULT NULL,
  `is_confidential` TINYINT(1) DEFAULT 0 COMMENT '1 if document is confidential',
  `uploaded_by` INT NOT NULL COMMENT 'User ID who uploaded the document',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_empID` (`empID`),
  INDEX `idx_settingsID` (`settingsID`),
  INDEX `idx_document_type` (`document_type`),
  INDEX `idx_expiry_date` (`expiry_date`),
  FOREIGN KEY (`settingsID`) REFERENCES `settings`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
