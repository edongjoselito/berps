CREATE TABLE IF NOT EXISTS `user_reminders` (
  `reminder_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `settingsID` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text,
  `frequency` enum('daily','weekly','monthly','yearly') NOT NULL,
  `start_date` date NOT NULL,
  `next_reminder_date` date NOT NULL,
  `reminder_days_before` int(11) DEFAULT 3,
  `is_active` tinyint(1) DEFAULT 1,
  `last_sent_date` date DEFAULT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`reminder_id`),
  KEY `user_id` (`user_id`),
  KEY `settingsID` (`settingsID`),
  KEY `next_reminder_date` (`next_reminder_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
