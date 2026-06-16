-- Employee Documents Table
-- Tracks documents and records for employees (HRIS feature)

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
