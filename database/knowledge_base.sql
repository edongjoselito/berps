-- Knowledge Base and FAQ Table
CREATE TABLE IF NOT EXISTS `knowledge_base` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `settingsID` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `category` varchar(100) DEFAULT NULL,
  `type` enum('article','faq') DEFAULT 'article',
  `created_by` int(11) NOT NULL,
  `created_by_name` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_by` int(11) DEFAULT NULL,
  `updated_by_name` varchar(255) DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `status` enum('draft','published') DEFAULT 'draft',
  `view_count` int(11) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `settingsID` (`settingsID`),
  KEY `created_by` (`created_by`),
  KEY `type` (`type`),
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
