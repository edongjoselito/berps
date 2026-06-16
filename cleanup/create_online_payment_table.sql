-- Table used to track PayMongo (and other online-gateway) payment attempts
-- linked to an invoice. Rows are created when the client hits the "Pay
-- Online" button. Row is flipped to status='paid' either by webhook or by a
-- polled status check.

CREATE TABLE IF NOT EXISTS `online_payment` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `settingsID` INT(11) NOT NULL,
  `orderID` INT(11) DEFAULT NULL,
  `InvoiceNo` VARCHAR(100) DEFAULT NULL,
  `CustID` VARCHAR(100) DEFAULT NULL,
  `Customer` VARCHAR(255) DEFAULT NULL,
  `provider` VARCHAR(50) NOT NULL DEFAULT 'paymongo',
  `payment_method` VARCHAR(50) DEFAULT 'qrph',
  `amount` DECIMAL(14,2) NOT NULL DEFAULT 0.00,
  `currency` VARCHAR(10) NOT NULL DEFAULT 'PHP',
  `source_id` VARCHAR(255) DEFAULT NULL,
  `payment_intent_id` VARCHAR(255) DEFAULT NULL,
  `payment_method_id` VARCHAR(255) DEFAULT NULL,
  `checkout_id` VARCHAR(255) DEFAULT NULL,
  `checkout_url` TEXT DEFAULT NULL,
  `qr_code_url` TEXT DEFAULT NULL,
  `qr_code_data` LONGTEXT DEFAULT NULL,
  `status` VARCHAR(50) NOT NULL DEFAULT 'pending',
  `paymongo_payment_id` VARCHAR(255) DEFAULT NULL,
  `paid_at` DATETIME DEFAULT NULL,
  `expires_at` DATETIME DEFAULT NULL,
  `reference_no` VARCHAR(255) DEFAULT NULL,
  `raw_create_response` LONGTEXT DEFAULT NULL,
  `raw_webhook_payload` LONGTEXT DEFAULT NULL,
  `client_ip` VARCHAR(64) DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_online_payment_invoice` (`settingsID`, `InvoiceNo`),
  KEY `idx_online_payment_order` (`settingsID`, `orderID`),
  KEY `idx_online_payment_source` (`source_id`),
  KEY `idx_online_payment_intent` (`payment_intent_id`),
  KEY `idx_online_payment_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
