-- Database Migration for Feature Enhancements
-- Task Management Software - Email Notifications, Reminders, Real-time Updates
-- Date: 14.11.2025

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

-- --------------------------------------------------------
-- 1. Email Notifications Table
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `email_logs` (
  `id` int(30) NOT NULL AUTO_INCREMENT,
  `user_id` int(30) NOT NULL,
  `recipient_email` varchar(255) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `message` longtext NOT NULL,
  `type` enum('task_assigned','deadline_reminder','task_completed','status_update','daily_summary') NOT NULL,
  `task_id` int(30) DEFAULT NULL,
  `sent_status` enum('pending','sent','failed') DEFAULT 'pending',
  `attempts` int(3) DEFAULT 0,
  `last_attempt` datetime DEFAULT NULL,
  `date_created` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `sent_status` (`sent_status`),
  KEY `date_created` (`date_created`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- 2. Email Configuration Settings
-- --------------------------------------------------------

ALTER TABLE `system_settings`
ADD COLUMN `email_from` varchar(255) DEFAULT 'noreply@taskmanagement.com' AFTER `name`,
ADD COLUMN `email_host` varchar(255) DEFAULT 'localhost' AFTER `email_from`,
ADD COLUMN `email_port` int(5) DEFAULT 25 AFTER `email_host`,
ADD COLUMN `email_username` varchar(255) DEFAULT NULL AFTER `email_port`,
ADD COLUMN `email_password` varchar(255) DEFAULT NULL AFTER `email_username`,
ADD COLUMN `email_use_smtp` tinyint(1) DEFAULT 0 AFTER `email_password`,
ADD COLUMN `enable_email_notifications` tinyint(1) DEFAULT 1 AFTER `email_use_smtp`,
ADD COLUMN `enable_deadline_reminders` tinyint(1) DEFAULT 1 AFTER `enable_email_notifications`,
ADD COLUMN `deadline_reminder_days` int(3) DEFAULT 1 AFTER `enable_deadline_reminders`;

-- --------------------------------------------------------
-- 3. Deadline Reminders Table
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `deadline_reminders` (
  `id` int(30) NOT NULL AUTO_INCREMENT,
  `task_id` int(30) NOT NULL,
  `user_id` int(30) NOT NULL,
  `reminder_datetime` datetime NOT NULL,
  `reminder_status` enum('pending','sent','dismissed') DEFAULT 'pending',
  `sent_at` datetime DEFAULT NULL,
  `date_created` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `task_id` (`task_id`),
  KEY `user_id` (`user_id`),
  KEY `reminder_status` (`reminder_status`),
  KEY `reminder_datetime` (`reminder_datetime`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- 4. Daily Summary Schedule
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `email_schedules` (
  `id` int(30) NOT NULL AUTO_INCREMENT,
  `user_id` int(30) NOT NULL,
  `email` varchar(255) NOT NULL,
  `schedule_type` enum('daily','weekly','monthly') DEFAULT 'daily',
  `send_time` time DEFAULT '08:00:00',
  `send_days` varchar(255) DEFAULT 'Mon,Tue,Wed,Thu,Fri' COMMENT 'Comma-separated days or null for daily',
  `enabled` tinyint(1) DEFAULT 1,
  `last_sent` datetime DEFAULT NULL,
  `date_created` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `schedule_type` (`schedule_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- 5. Real-time Activity Log
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `activity_logs` (
  `id` int(30) NOT NULL AUTO_INCREMENT,
  `user_id` int(30) NOT NULL,
  `action` varchar(255) NOT NULL,
  `entity_type` varchar(100) NOT NULL COMMENT 'task, project, user, etc',
  `entity_id` int(30) NOT NULL,
  `old_value` longtext DEFAULT NULL,
  `new_value` longtext DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `date_created` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `entity_type` (`entity_type`),
  KEY `entity_id` (`entity_id`),
  KEY `date_created` (`date_created`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- 6. Task List Enhancements for New Features
-- --------------------------------------------------------

ALTER TABLE `task_list`
ADD COLUMN `reminder_sent` tinyint(1) DEFAULT 0 AFTER `progress_remarks`,
ADD COLUMN `email_notified` tinyint(1) DEFAULT 0 AFTER `reminder_sent`,
ADD COLUMN `last_notified_at` datetime DEFAULT NULL AFTER `email_notified`,
ADD COLUMN `last_reminder_at` datetime DEFAULT NULL AFTER `last_notified_at`;

-- --------------------------------------------------------
-- 7. User Preferences for Notifications
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `user_preferences` (
  `id` int(30) NOT NULL AUTO_INCREMENT,
  `user_id` int(30) NOT NULL UNIQUE,
  `email_on_task_assign` tinyint(1) DEFAULT 1,
  `email_on_status_change` tinyint(1) DEFAULT 1,
  `email_on_deadline_reminder` tinyint(1) DEFAULT 1,
  `email_on_task_completion` tinyint(1) DEFAULT 1,
  `daily_summary_email` tinyint(1) DEFAULT 1,
  `summary_time` time DEFAULT '08:00:00',
  `timezone` varchar(50) DEFAULT 'UTC',
  `date_updated` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- 8. Department Statistics Cache (for enhanced dashboard)
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `department_statistics` (
  `id` int(30) NOT NULL AUTO_INCREMENT,
  `department_id` int(30) NOT NULL,
  `total_tasks` int(11) DEFAULT 0,
  `pending_tasks` int(11) DEFAULT 0,
  `in_progress_tasks` int(11) DEFAULT 0,
  `completed_tasks` int(11) DEFAULT 0,
  `overdue_tasks` int(11) DEFAULT 0,
  `completion_percentage` decimal(5,2) DEFAULT 0.00,
  `last_updated` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `department_id` (`department_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- 9. System Audit Log (for real-time updates & security)
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `system_audit_log` (
  `id` int(30) NOT NULL AUTO_INCREMENT,
  `user_id` int(30) NOT NULL,
  `action` varchar(255) NOT NULL,
  `module` varchar(100) NOT NULL,
  `description` longtext DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `status` enum('success','failed','pending') DEFAULT 'success',
  `date_created` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `module` (`module`),
  KEY `status` (`status`),
  KEY `date_created` (`date_created`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- 10. Insert Default Email Schedule for Admins
-- --------------------------------------------------------

INSERT INTO `email_schedules` (`user_id`, `email`, `schedule_type`, `send_time`, `send_days`, `enabled`) 
SELECT `id`, `email`, 'daily', '08:00:00', 'Mon,Tue,Wed,Thu,Fri', 1 
FROM `users` 
WHERE `type` IN (1, 2) 
ON DUPLICATE KEY UPDATE enabled = VALUES(enabled);

-- --------------------------------------------------------
-- 11. Insert Default User Preferences
-- --------------------------------------------------------

INSERT INTO `user_preferences` (`user_id`, `email_on_task_assign`, `email_on_status_change`, `email_on_deadline_reminder`, `email_on_task_completion`, `daily_summary_email`) 
SELECT `id`, 1, 1, 1, 1, 1 FROM `users` 
WHERE `id` NOT IN (SELECT `user_id` FROM `user_preferences`)
ON DUPLICATE KEY UPDATE user_id = VALUES(user_id);

COMMIT;
