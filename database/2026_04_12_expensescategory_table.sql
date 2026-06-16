CREATE TABLE IF NOT EXISTS `expensescategory` (
    `categoryID` int unsigned NOT NULL AUTO_INCREMENT,
    `Category` varchar(120) NOT NULL,
    `settingsID` int unsigned NOT NULL DEFAULT 1,
    `createdAt` datetime DEFAULT CURRENT_TIMESTAMP,
    `updatedAt` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`categoryID`),
    UNIQUE KEY `uniq_expensescategory_settings_category` (`settingsID`, `Category`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
