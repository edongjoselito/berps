SET @invoice_expiration_column_exists := (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'invoice'
      AND COLUMN_NAME = 'invoiceExpirationDate'
);

SET @invoice_expiration_alter_sql := IF(
    @invoice_expiration_column_exists = 0,
    'ALTER TABLE `invoice` ADD COLUMN `invoiceExpirationDate` date DEFAULT NULL AFTER `recurringTerminationDate`',
    'SELECT 1'
);

PREPARE invoice_expiration_stmt FROM @invoice_expiration_alter_sql;
EXECUTE invoice_expiration_stmt;
DEALLOCATE PREPARE invoice_expiration_stmt;

UPDATE `invoice`
SET `invoiceExpirationDate` = NULL
WHERE `invoiceExpirationDate` IS NOT NULL
  AND (`invoiceExpirationDate` + 0) = 0;
