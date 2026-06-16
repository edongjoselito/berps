-- BERPS invoice/payment customer linkage + recurring invoice support
-- Safe to run on the existing database. Legacy Customer/CustAddress text fields
-- are intentionally retained for historical fallback data.

SET @schema_name := DATABASE();
SET @old_sql_mode := @@SESSION.sql_mode;
SET SESSION sql_mode = REPLACE(REPLACE(@@SESSION.sql_mode, 'NO_ZERO_DATE', ''), 'NO_ZERO_IN_DATE', '');

SET @sql := IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = @schema_name AND TABLE_NAME = 'invoice' AND COLUMN_NAME = 'CustID') = 0,
    'ALTER TABLE invoice ADD COLUMN CustID varchar(12) NULL AFTER InvoiceNo',
    'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = @schema_name AND TABLE_NAME = 'invoice' AND COLUMN_NAME = 'recurringFrequency') = 0,
    'ALTER TABLE invoice ADD COLUMN recurringFrequency varchar(20) NOT NULL DEFAULT ''none'' AFTER invoiceBy',
    'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = @schema_name AND TABLE_NAME = 'invoice' AND COLUMN_NAME = 'recurringScheduleDate') = 0,
    'ALTER TABLE invoice ADD COLUMN recurringScheduleDate date NULL AFTER recurringFrequency',
    'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = @schema_name AND TABLE_NAME = 'invoice' AND COLUMN_NAME = 'recurringTemplateID') = 0,
    'ALTER TABLE invoice ADD COLUMN recurringTemplateID int unsigned NULL AFTER recurringScheduleDate',
    'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = @schema_name AND TABLE_NAME = 'invoice' AND COLUMN_NAME = 'lastRecurringGeneratedFor') = 0,
    'ALTER TABLE invoice ADD COLUMN lastRecurringGeneratedFor date NULL AFTER recurringTemplateID',
    'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = @schema_name AND TABLE_NAME = 'payments' AND COLUMN_NAME = 'PaymentReference') = 0,
    'ALTER TABLE payments ADD COLUMN PaymentReference varchar(255) NULL AFTER ORNo',
    'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = @schema_name AND TABLE_NAME = 'payments' AND COLUMN_NAME = 'CustID') = 0,
    'ALTER TABLE payments ADD COLUMN CustID varchar(12) NULL AFTER PaymentSource',
    'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS WHERE TABLE_SCHEMA = @schema_name AND TABLE_NAME = 'invoice' AND INDEX_NAME = 'idx_invoice_custid') = 0,
    'ALTER TABLE invoice ADD INDEX idx_invoice_custid (CustID)',
    'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS WHERE TABLE_SCHEMA = @schema_name AND TABLE_NAME = 'invoice' AND INDEX_NAME = 'idx_invoice_recurring') = 0,
    'ALTER TABLE invoice ADD INDEX idx_invoice_recurring (settingsID, invoiceSource, recurringFrequency, recurringTemplateID, recurringScheduleDate)',
    'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS WHERE TABLE_SCHEMA = @schema_name AND TABLE_NAME = 'payments' AND INDEX_NAME = 'idx_payments_custid') = 0,
    'ALTER TABLE payments ADD INDEX idx_payments_custid (CustID)',
    'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

UPDATE invoice i
LEFT JOIN customers c
  ON c.settingsID = i.settingsID
 AND c.Customer = i.Customer
SET i.CustID = c.CustID
WHERE (i.CustID IS NULL OR i.CustID = '')
  AND c.CustID IS NOT NULL;

UPDATE payments p
LEFT JOIN customers c
  ON c.settingsID = p.settingsID
 AND c.Customer = p.Customer
SET p.CustID = c.CustID
WHERE (p.CustID IS NULL OR p.CustID = '')
  AND c.CustID IS NOT NULL;

SET SESSION sql_mode = @old_sql_mode;
