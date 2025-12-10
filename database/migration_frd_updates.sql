-- Database Migration for FRD Requirements
-- Task Management Software Enhancement
-- Date: 01.11.2025

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

-- --------------------------------------------------------
-- Add Department Table
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `departments` (
  `id` int(30) NOT NULL AUTO_INCREMENT,
  `name` varchar(200) NOT NULL,
  `description` text,
  `head_id` int(30) DEFAULT NULL,
  `date_created` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `head_id` (`head_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert sample departments
INSERT INTO `departments` (`id`, `name`, `description`, `head_id`) VALUES
(1, 'IT Department', 'Information Technology Department', 2),
(2, 'HR Department', 'Human Resources Department', NULL),
(3, 'Sales Department', 'Sales and Marketing Department', NULL);

-- --------------------------------------------------------
-- Update users table to add department_id
-- --------------------------------------------------------

ALTER TABLE `users` 
ADD COLUMN `department_id` int(30) DEFAULT NULL AFTER `type`,
ADD KEY `department_id` (`department_id`);

-- Update existing users with departments
UPDATE `users` SET `department_id` = 1 WHERE `id` IN (2,3,4,5);

-- --------------------------------------------------------
-- Update task_list table with new fields
-- --------------------------------------------------------

ALTER TABLE `task_list`
ADD COLUMN `priority` enum('High','Medium','Low') DEFAULT 'Medium' AFTER `status`,
ADD COLUMN `deadline` date DEFAULT NULL AFTER `priority`,
ADD COLUMN `assigned_to` text DEFAULT NULL COMMENT 'Comma-separated user IDs' AFTER `deadline`,
ADD COLUMN `assigned_by` int(30) DEFAULT NULL AFTER `assigned_to`,
ADD COLUMN `department_id` int(30) DEFAULT NULL AFTER `assigned_by`,
ADD COLUMN `progress_remarks` text DEFAULT NULL AFTER `department_id`,
ADD KEY `assigned_by` (`assigned_by`),
ADD KEY `department_id` (`department_id`);

-- Update existing tasks
UPDATE `task_list` SET `priority` = 'Medium', `assigned_by` = 2 WHERE `id` > 0;

-- --------------------------------------------------------
-- Create task_attachments table
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `task_attachments` (
  `id` int(30) NOT NULL AUTO_INCREMENT,
  `task_id` int(30) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_path` text NOT NULL,
  `file_type` varchar(50) DEFAULT NULL,
  `file_size` int(11) DEFAULT NULL,
  `uploaded_by` int(30) NOT NULL,
  `date_created` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `task_id` (`task_id`),
  KEY `uploaded_by` (`uploaded_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Create task_reviews table for approval system
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `task_reviews` (
  `id` int(30) NOT NULL AUTO_INCREMENT,
  `task_id` int(30) NOT NULL,
  `reviewed_by` int(30) NOT NULL,
  `status` enum('Pending','Approved','Rejected','Feedback') DEFAULT 'Pending',
  `remarks` text DEFAULT NULL,
  `date_reviewed` datetime DEFAULT NULL,
  `date_created` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `task_id` (`task_id`),
  KEY `reviewed_by` (`reviewed_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Create notifications table
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `notifications` (
  `id` int(30) NOT NULL AUTO_INCREMENT,
  `user_id` int(30) NOT NULL,
  `type` enum('task_assigned','deadline_reminder','task_completed','status_update','review_request') NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `task_id` int(30) DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `date_created` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `task_id` (`task_id`),
  KEY `is_read` (`is_read`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Create task_progress_log table for daily progress tracking
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `task_progress_log` (
  `id` int(30) NOT NULL AUTO_INCREMENT,
  `task_id` int(30) NOT NULL,
  `user_id` int(30) NOT NULL,
  `status` enum('Not Started','In Progress','Completed','On Hold') DEFAULT 'Not Started',
  `progress_percentage` int(3) DEFAULT 0,
  `remarks` text DEFAULT NULL,
  `date_logged` date NOT NULL,
  `date_created` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `task_id` (`task_id`),
  KEY `user_id` (`user_id`),
  KEY `date_logged` (`date_logged`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Update user_productivity to link with new task system
-- --------------------------------------------------------

ALTER TABLE `user_productivity`
ADD COLUMN `status` enum('Not Started','In Progress','Completed','On Hold') DEFAULT NULL AFTER `task_id`;

COMMIT;

