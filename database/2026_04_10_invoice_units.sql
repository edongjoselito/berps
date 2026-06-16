CREATE TABLE IF NOT EXISTS `invoice_units` (
    `unitID` int unsigned NOT NULL AUTO_INCREMENT,
    `settingsID` int unsigned NOT NULL,
    `unitName` varchar(50) NOT NULL,
    `createdAt` datetime DEFAULT NULL,
    `updatedAt` datetime DEFAULT NULL,
    PRIMARY KEY (`unitID`),
    KEY `idx_invoice_units_settings` (`settingsID`),
    KEY `idx_invoice_units_name` (`unitName`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Default unit values are seeded automatically by the application
-- the first time a company/settings profile loads invoice units.
