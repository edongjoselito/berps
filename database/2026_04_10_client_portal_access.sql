-- BERPS client portal access support
-- Adds portal login controls directly to the customers table.

SET @schema_name := DATABASE();
SET @old_sql_mode := @@SESSION.sql_mode;
SET SESSION sql_mode = REPLACE(REPLACE(@@SESSION.sql_mode, 'NO_ZERO_DATE', ''), 'NO_ZERO_IN_DATE', '');

SET @sql := IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = @schema_name AND TABLE_NAME = 'customers' AND COLUMN_NAME = 'portal_enabled') = 0,
    'ALTER TABLE customers ADD COLUMN portal_enabled tinyint(1) NOT NULL DEFAULT 0 AFTER client_email',
    'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = @schema_name AND TABLE_NAME = 'customers' AND COLUMN_NAME = 'portal_password') = 0,
    'ALTER TABLE customers ADD COLUMN portal_password varchar(255) NULL AFTER portal_enabled',
    'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = @schema_name AND TABLE_NAME = 'customers' AND COLUMN_NAME = 'portal_last_login') = 0,
    'ALTER TABLE customers ADD COLUMN portal_last_login datetime NULL AFTER portal_password',
    'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS WHERE TABLE_SCHEMA = @schema_name AND TABLE_NAME = 'customers' AND INDEX_NAME = 'idx_customers_client_email') = 0,
    'ALTER TABLE customers ADD INDEX idx_customers_client_email (settingsID, client_email)',
    'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET SESSION sql_mode = @old_sql_mode;
