-- Migration for Project Enhancements
-- Tables for attachments, subtasks, and reminders

CREATE TABLE IF NOT EXISTS `project_attachments` (
  `id` int(30) NOT NULL AUTO_INCREMENT,
  `project_id` int(30) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_path` text NOT NULL,
  `file_type` varchar(50) DEFAULT NULL,
  `file_size` int(11) DEFAULT NULL,
  `uploaded_by` int(30) NOT NULL,
  `date_created` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `project_id` (`project_id`),
  KEY `uploaded_by` (`uploaded_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `project_subtasks` (
  `id` int(30) NOT NULL AUTO_INCREMENT,
  `project_id` int(30) NOT NULL,
  `title` varchar(255) NOT NULL,
  `completed` tinyint(1) DEFAULT 0,
  `date_created` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `project_id` (`project_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `project_reminders` (
  `id` int(30) NOT NULL AUTO_INCREMENT,
  `project_id` int(30) NOT NULL,
  `reminder_datetime` datetime NOT NULL,
  `email` varchar(255) NOT NULL,
  `sent` tinyint(1) DEFAULT 0,
  `date_created` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `project_id` (`project_id`),
  KEY `reminder_datetime` (`reminder_datetime`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Add category and priority fields to project_list if not exists
ALTER TABLE `project_list` 
ADD COLUMN IF NOT EXISTS `category` varchar(50) DEFAULT 'General' AFTER `status`,
ADD COLUMN IF NOT EXISTS `priority` enum('High','Medium','Low') DEFAULT 'Medium' AFTER `category`;

