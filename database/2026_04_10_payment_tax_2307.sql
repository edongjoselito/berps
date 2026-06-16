-- BERPS payment tax support for government payments with BIR Form 2307

SET @schema_name := DATABASE();
SET @old_sql_mode := @@SESSION.sql_mode;
SET SESSION sql_mode = REPLACE(REPLACE(@@SESSION.sql_mode, 'NO_ZERO_DATE', ''), 'NO_ZERO_IN_DATE', '');

SET @sql := IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = @schema_name AND TABLE_NAME = 'payments' AND COLUMN_NAME = 'TaxAmount') = 0,
    'ALTER TABLE payments ADD COLUMN TaxAmount double NOT NULL DEFAULT 0 AFTER AmountPaid',
    'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET SESSION sql_mode = @old_sql_mode;
