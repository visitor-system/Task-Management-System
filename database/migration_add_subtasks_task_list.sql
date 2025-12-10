-- Migration: add subtasks to task_list
-- Date: 2025-11-14

ALTER TABLE `task_list`
ADD COLUMN `subtasks` TEXT DEFAULT NULL AFTER `description`;

-- Verify column added:
-- DESCRIBE task_list;
