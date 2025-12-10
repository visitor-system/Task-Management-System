-- Migration: add start_date to task_list
-- Date: 2025-11-14

ALTER TABLE `task_list`
ADD COLUMN `start_date` DATE DEFAULT NULL AFTER `priority`;

-- Verify column added:
-- DESCRIBE task_list;
