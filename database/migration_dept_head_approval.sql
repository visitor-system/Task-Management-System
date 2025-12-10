-- =====================================================
-- Department Head Task Approval System Enhancement
-- =====================================================
-- This migration adds approval workflow to task_reviews
-- and task_list tables to implement task approval process
-- =====================================================

-- Step 1: Add approval fields to task_reviews table
-- Adds new column to store approval status and date
ALTER TABLE `task_reviews` 
ADD COLUMN `approval_status` enum('pending','approved','rejected','feedback') DEFAULT 'pending' AFTER `status`,
ADD COLUMN `approval_date` datetime DEFAULT NULL AFTER `date_reviewed`;

-- Step 2: Add approval fields to task_list table
-- Tracks approval status at task level for easy filtering
ALTER TABLE `task_list` 
ADD COLUMN `approval_status` varchar(20) DEFAULT NULL AFTER `status`,
ADD COLUMN `approval_date` datetime DEFAULT NULL AFTER `approval_status`;

-- Step 3: Add index for performance on approval_status
CREATE INDEX `idx_approval_status` ON `task_reviews` (`approval_status`);
CREATE INDEX `idx_task_approval` ON `task_list` (`approval_status`, `department_id`);

-- Step 4: Update task_list table description to include approval workflow note
-- Note: This is a comment for documentation
-- Status flow: 1=Not Started -> 2=In Progress -> (SUBMIT FOR REVIEW) -> Department Head Reviews:
--   - Approved: Status stays 3=Completed, approval_status='approved'
--   - Feedback: Status stays as is, approval_status='feedback' (awaiting revision)
--   - Rejected: Status reverts to 2=In Progress, approval_status='rejected' (for rework)

-- Step 5: Create view for pending approvals (optional, for reporting)
CREATE OR REPLACE VIEW `v_pending_approvals` AS
SELECT 
  t.id,
  t.task,
  t.status,
  p.name as project_name,
  d.name as department_name,
  CONCAT(u.firstname, ' ', u.lastname) as assigned_to_user,
  t.deadline,
  t.date_created,
  t.approval_status,
  tr.remarks,
  CONCAT(rev.firstname, ' ', rev.lastname) as reviewed_by_user
FROM task_list t
LEFT JOIN project_list p ON p.id = t.project_id
LEFT JOIN departments d ON d.id = t.department_id
LEFT JOIN users u ON FIND_IN_SET(u.id, t.assigned_to)
LEFT JOIN task_reviews tr ON tr.task_id = t.id
LEFT JOIN users rev ON rev.id = tr.reviewed_by
WHERE t.status = 3 AND (t.approval_status IS NULL OR t.approval_status = 'pending')
ORDER BY t.deadline ASC;

-- Step 6: Grant appropriate permissions (if using user accounts)
-- GRANT SELECT ON `v_pending_approvals` TO 'app_user'@'localhost';

-- =====================================================
-- End of Department Head Approval Migration
-- =====================================================
-- To apply these changes, run:
-- mysql -u root -p tms_db < database/migration_dept_head_approval.sql
-- =====================================================
