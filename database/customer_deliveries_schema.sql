-- =====================================================
-- Customer Delivery System Schema
-- MySQL-compatible version for direct execution
-- =====================================================

SET FOREIGN_KEY_CHECKS = 0;

-- -----------------------------------------------------
-- 1) Add deliveryID to payments table if not yet added
-- -----------------------------------------------------
-- Run this only once. If deliveryID already exists, skip this block.

ALTER TABLE `payments`
  ADD COLUMN `deliveryID` INT UNSIGNED NULL DEFAULT NULL AFTER `InvoiceNo`,
  ADD INDEX `idx_payments_delivery` (`deliveryID`);

-- -----------------------------------------------------
-- 2) Customer Deliveries Table
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `customer_deliveries` (
  `deliveryID` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `deliveryNo` VARCHAR(20) NOT NULL DEFAULT '',
  `invoiceNo` VARCHAR(14) DEFAULT NULL COMMENT 'Link to related invoice (optional)',
  `orderID` INT UNSIGNED DEFAULT NULL COMMENT 'Link to job order (optional)',
  `customerID` INT UNSIGNED DEFAULT NULL COMMENT 'Customer reference',
  `customerName` VARCHAR(250) NOT NULL DEFAULT '',
  `customerAddress` VARCHAR(150) NOT NULL DEFAULT '',
  `deliveryDate` DATE NOT NULL,
  `totalAmount` DOUBLE NOT NULL DEFAULT 0,
  `amountPaid` DOUBLE NOT NULL DEFAULT 0,
  `balance` DOUBLE NOT NULL DEFAULT 0,
  `deliveryStatus` ENUM('pending', 'delivered', 'partial', 'cancelled') NOT NULL DEFAULT 'pending',
  `paymentStatus` ENUM('unpaid', 'partial', 'paid') NOT NULL DEFAULT 'unpaid',
  `receivedBy` VARCHAR(150) NOT NULL DEFAULT '' COMMENT 'Who received the delivery',
  `deliveredBy` VARCHAR(200) NOT NULL DEFAULT '' COMMENT 'Who delivered',
  `notes` TEXT DEFAULT NULL,
  `settingsID` INT UNSIGNED NOT NULL DEFAULT 1,
  `createdAt` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updatedAt` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`deliveryID`),
  UNIQUE KEY `idx_delivery_no` (`deliveryNo`, `settingsID`),
  KEY `idx_delivery_invoice` (`invoiceNo`),
  KEY `idx_delivery_order` (`orderID`),
  KEY `idx_delivery_customer` (`customerID`),
  KEY `idx_delivery_date` (`deliveryDate`),
  KEY `idx_delivery_status` (`deliveryStatus`),
  KEY `idx_payment_status` (`paymentStatus`),
  KEY `idx_delivery_settings` (`settingsID`),
  CONSTRAINT `fk_delivery_order` FOREIGN KEY (`orderID`) REFERENCES `invoice` (`orderID`) ON DELETE SET NULL,
  CONSTRAINT `fk_delivery_settings` FOREIGN KEY (`settingsID`) REFERENCES `settings` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- 3) Customer Delivery Items Table
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `customer_delivery_items` (
  `itemID` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `deliveryID` INT UNSIGNED NOT NULL,
  `lineNo` INT UNSIGNED NOT NULL DEFAULT 1,
  `itemDescription` VARCHAR(250) NOT NULL DEFAULT '',
  `itemQuantity` INT UNSIGNED NOT NULL DEFAULT 1,
  `itemUnit` VARCHAR(20) DEFAULT NULL COMMENT 'e.g., pcs, kg, hours',
  `itemUnitPrice` DOUBLE NOT NULL DEFAULT 0,
  `lineTotal` DOUBLE NOT NULL DEFAULT 0,
  `serialNo` VARCHAR(45) DEFAULT NULL COMMENT 'For serialized items',
  `model` VARCHAR(45) DEFAULT NULL,
  `brand` VARCHAR(45) DEFAULT NULL,
  `settingsID` INT UNSIGNED NOT NULL DEFAULT 1,
  `createdAt` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`itemID`),
  KEY `idx_delivery_item_delivery` (`deliveryID`, `settingsID`),
  KEY `idx_delivery_item_settings` (`settingsID`),
  CONSTRAINT `fk_delivery_item_delivery` FOREIGN KEY (`deliveryID`) REFERENCES `customer_deliveries` (`deliveryID`) ON DELETE CASCADE,
  CONSTRAINT `fk_delivery_item_settings` FOREIGN KEY (`settingsID`) REFERENCES `settings` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- 4) Delivery Number Sequence Table
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `delivery_number_sequence` (
  `settingsID` INT UNSIGNED NOT NULL,
  `lastNumber` INT UNSIGNED NOT NULL DEFAULT 0,
  `prefix` VARCHAR(10) NOT NULL DEFAULT 'DR',
  PRIMARY KEY (`settingsID`),
  CONSTRAINT `fk_delivery_seq_settings` FOREIGN KEY (`settingsID`) REFERENCES `settings` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Default sequence row
INSERT IGNORE INTO `delivery_number_sequence` (`settingsID`, `lastNumber`, `prefix`)
VALUES (1, 0, 'DR');

-- -----------------------------------------------------
-- 5) Drop triggers first so re-import will not fail
-- -----------------------------------------------------
DROP TRIGGER IF EXISTS `before_delivery_insert`;
DROP TRIGGER IF EXISTS `before_delivery_item_insert`;
DROP TRIGGER IF EXISTS `before_delivery_item_update`;
DROP TRIGGER IF EXISTS `after_delivery_item_insert_balance`;
DROP TRIGGER IF EXISTS `after_delivery_item_update_balance`;
DROP TRIGGER IF EXISTS `after_delivery_item_delete_balance`;

-- -----------------------------------------------------
-- 6) Trigger: Auto-generate delivery number
-- -----------------------------------------------------
DELIMITER $$

CREATE TRIGGER `before_delivery_insert`
BEFORE INSERT ON `customer_deliveries`
FOR EACH ROW
BEGIN
    DECLARE next_number INT DEFAULT 0;
    DECLARE seq_prefix VARCHAR(10) DEFAULT 'DR';

    IF NEW.deliveryDate IS NULL OR NEW.deliveryDate = '0000-00-00' THEN
        SET NEW.deliveryDate = CURDATE();
    END IF;

    IF NEW.deliveryNo IS NULL OR NEW.deliveryNo = '' THEN

        INSERT IGNORE INTO `delivery_number_sequence` (`settingsID`, `lastNumber`, `prefix`)
        VALUES (NEW.settingsID, 0, 'DR');

        UPDATE `delivery_number_sequence`
        SET `lastNumber` = `lastNumber` + 1
        WHERE `settingsID` = NEW.settingsID;

        SELECT `lastNumber`, `prefix`
        INTO next_number, seq_prefix
        FROM `delivery_number_sequence`
        WHERE `settingsID` = NEW.settingsID
        LIMIT 1;

        SET NEW.deliveryNo = CONCAT(seq_prefix, '-', LPAD(next_number, 4, '0'));
    END IF;
END$$

-- -----------------------------------------------------
-- 7) Trigger: Auto-calculate line total
-- -----------------------------------------------------
CREATE TRIGGER `before_delivery_item_insert`
BEFORE INSERT ON `customer_delivery_items`
FOR EACH ROW
BEGIN
    SET NEW.lineTotal = NEW.itemQuantity * NEW.itemUnitPrice;
END$$

CREATE TRIGGER `before_delivery_item_update`
BEFORE UPDATE ON `customer_delivery_items`
FOR EACH ROW
BEGIN
    SET NEW.lineTotal = NEW.itemQuantity * NEW.itemUnitPrice;
END$$

-- -----------------------------------------------------
-- 8) Trigger: Update delivery totals after item insert
-- -----------------------------------------------------
CREATE TRIGGER `after_delivery_item_insert_balance`
AFTER INSERT ON `customer_delivery_items`
FOR EACH ROW
BEGIN
    UPDATE `customer_deliveries`
    SET
        `totalAmount` = (
            SELECT COALESCE(SUM(`lineTotal`), 0)
            FROM `customer_delivery_items`
            WHERE `deliveryID` = NEW.deliveryID
        ),
        `balance` = (
            SELECT COALESCE(SUM(`lineTotal`), 0)
            FROM `customer_delivery_items`
            WHERE `deliveryID` = NEW.deliveryID
        ) - `amountPaid`
    WHERE `deliveryID` = NEW.deliveryID;
END$$

-- -----------------------------------------------------
-- 9) Trigger: Update delivery totals after item update
-- -----------------------------------------------------
CREATE TRIGGER `after_delivery_item_update_balance`
AFTER UPDATE ON `customer_delivery_items`
FOR EACH ROW
BEGIN
    UPDATE `customer_deliveries`
    SET
        `totalAmount` = (
            SELECT COALESCE(SUM(`lineTotal`), 0)
            FROM `customer_delivery_items`
            WHERE `deliveryID` = NEW.deliveryID
        ),
        `balance` = (
            SELECT COALESCE(SUM(`lineTotal`), 0)
            FROM `customer_delivery_items`
            WHERE `deliveryID` = NEW.deliveryID
        ) - `amountPaid`
    WHERE `deliveryID` = NEW.deliveryID;
END$$

-- -----------------------------------------------------
-- 10) Trigger: Update delivery totals after item delete
-- -----------------------------------------------------
CREATE TRIGGER `after_delivery_item_delete_balance`
AFTER DELETE ON `customer_delivery_items`
FOR EACH ROW
BEGIN
    UPDATE `customer_deliveries`
    SET
        `totalAmount` = (
            SELECT COALESCE(SUM(`lineTotal`), 0)
            FROM `customer_delivery_items`
            WHERE `deliveryID` = OLD.deliveryID
        ),
        `balance` = (
            SELECT COALESCE(SUM(`lineTotal`), 0)
            FROM `customer_delivery_items`
            WHERE `deliveryID` = OLD.deliveryID
        ) - `amountPaid`
    WHERE `deliveryID` = OLD.deliveryID;
END$$

DELIMITER ;

SET FOREIGN_KEY_CHECKS = 1;