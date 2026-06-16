-- BERPS invoice item breakdown support
-- Adds optional quantity, duration unit, and unit price fields so invoices
-- can display line items like "3 months x PHP 5,000.00 / each".

SET @schema_name := DATABASE();
SET @old_sql_mode := @@SESSION.sql_mode;
SET SESSION sql_mode = REPLACE(REPLACE(@@SESSION.sql_mode, 'NO_ZERO_DATE', ''), 'NO_ZERO_IN_DATE', '');

SET @sql := IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = @schema_name AND TABLE_NAME = 'invoice' AND COLUMN_NAME = 'itemQuantity') = 0,
    'ALTER TABLE invoice ADD COLUMN itemQuantity int unsigned NULL AFTER JobDescription',
    'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = @schema_name AND TABLE_NAME = 'invoice' AND COLUMN_NAME = 'itemDurationUnit') = 0,
    'ALTER TABLE invoice ADD COLUMN itemDurationUnit varchar(20) NULL AFTER itemQuantity',
    'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = @schema_name AND TABLE_NAME = 'invoice' AND COLUMN_NAME = 'itemUnitPrice') = 0,
    'ALTER TABLE invoice ADD COLUMN itemUnitPrice double NULL AFTER itemDurationUnit',
    'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

CREATE TABLE IF NOT EXISTS invoice_items (
    itemID int unsigned NOT NULL AUTO_INCREMENT,
    orderID int unsigned NOT NULL,
    settingsID int unsigned NOT NULL,
    lineNo int unsigned NOT NULL DEFAULT 1,
    itemDescription varchar(250) NOT NULL,
    itemQuantity int unsigned NOT NULL DEFAULT 1,
    itemDurationUnit varchar(20) DEFAULT NULL,
    itemUnitPrice double NOT NULL DEFAULT 0,
    lineTotal double NOT NULL DEFAULT 0,
    PRIMARY KEY (itemID),
    KEY idx_invoice_items_order (orderID, settingsID),
    KEY idx_invoice_items_settings (settingsID)
);

INSERT INTO invoice_items (
    orderID,
    settingsID,
    lineNo,
    itemDescription,
    itemQuantity,
    itemDurationUnit,
    itemUnitPrice,
    lineTotal
)
SELECT
    i.orderID,
    i.settingsID,
    1,
    COALESCE(NULLIF(i.JobDescription, ''), 'Invoice item'),
    COALESCE(NULLIF(i.itemQuantity, 0), 1),
    COALESCE(NULLIF(i.itemDurationUnit, ''), 'each'),
    CASE
        WHEN i.itemUnitPrice IS NOT NULL THEN i.itemUnitPrice
        WHEN COALESCE(NULLIF(i.itemQuantity, 0), 0) > 0 THEN ROUND(i.TotalDue / COALESCE(NULLIF(i.itemQuantity, 0), 1), 2)
        ELSE i.TotalDue
    END,
    i.TotalDue
FROM invoice i
LEFT JOIN invoice_items ii
    ON ii.orderID = i.orderID
   AND ii.settingsID = i.settingsID
WHERE ii.itemID IS NULL
  AND COALESCE(i.invoiceStat, '') <> 'Deleted';

SET SESSION sql_mode = @old_sql_mode;
