-- Zoho Mail integration: per-user account storage
-- Stores OAuth credentials, refresh/access tokens, and account metadata.

CREATE TABLE IF NOT EXISTS `zoho_mail_accounts` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `user_id` INT(11) NOT NULL,
  `settingsID` INT(11) DEFAULT NULL,
  `auth_type` ENUM('oauth','self_client') NOT NULL DEFAULT 'oauth',
  `data_center` VARCHAR(8) NOT NULL DEFAULT 'com'
    COMMENT 'Zoho region: com, eu, in, com.au, com.cn, jp',
  `client_id` VARCHAR(255) DEFAULT NULL,
  `client_secret` VARCHAR(255) DEFAULT NULL,
  `redirect_uri` VARCHAR(512) DEFAULT NULL,
  `scope` VARCHAR(512) NOT NULL DEFAULT 'ZohoMail.messages.ALL,ZohoMail.accounts.READ,ZohoMail.folders.ALL,ZohoMail.attachments.ALL',
  `refresh_token` TEXT DEFAULT NULL,
  `access_token` TEXT DEFAULT NULL,
  `access_token_expires_at` DATETIME DEFAULT NULL,
  `account_id` VARCHAR(64) DEFAULT NULL,
  `primary_email` VARCHAR(255) DEFAULT NULL,
  `display_name` VARCHAR(255) DEFAULT NULL,
  `status` ENUM('disconnected','connected','error') NOT NULL DEFAULT 'disconnected',
  `last_error` TEXT DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_zoho_user` (`user_id`),
  KEY `idx_zoho_settings` (`settingsID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
