<?php
// save_task_reminder.php
ob_start();
date_default_timezone_set("Asia/Manila");

include 'db_connect.php';

// Ensure task_id and reminder_datetime are provided
if (!isset($_POST['task_id']) || !isset($_POST['reminder_datetime'])) {
    echo "Error: Task ID or reminder date missing.";
    exit;
}

$task_id = intval($_POST['task_id']);
$reminder_datetime = $conn->real_escape_string($_POST['reminder_datetime']);
$reminder_notification = isset($_POST['reminder_notification']) ? 1 : 0;
$reminder_mail = isset($_POST['reminder_mail']) ? 1 : 0;
$send_overdue = isset($_POST['send_overdue_notification']) ? 1 : 0;

// Prevent saving reminder for invalid task
if ($task_id <= 0) {
    echo "Error: Invalid task ID.";
    exit;
}

// Check if task exists
$task_check = $conn->query("SELECT assigned_to FROM task_list WHERE id = $task_id");
if (!$task_check || $task_check->num_rows == 0) {
    echo "Error: Task not found.";
    exit;
}

// Fetch assigned users
$task = $task_check->fetch_assoc();
if (empty($task['assigned_to'])) {
    echo "Error: No users assigned to this task.";
    exit;
}

$assigned_users = explode(',', $task['assigned_to']);
$saved_count = 0;

// Add columns if missing
$columns_check = $conn->query("SHOW COLUMNS FROM deadline_reminders LIKE 'send_notification'");
if ($columns_check->num_rows == 0) {
    $conn->query("ALTER TABLE deadline_reminders ADD COLUMN send_notification tinyint(1) DEFAULT 1 AFTER reminder_status");
}
$columns_check = $conn->query("SHOW COLUMNS FROM deadline_reminders LIKE 'send_mail'");
if ($columns_check->num_rows == 0) {
    $conn->query("ALTER TABLE deadline_reminders ADD COLUMN send_mail tinyint(1) DEFAULT 1 AFTER send_notification");
}
$columns_check = $conn->query("SHOW COLUMNS FROM deadline_reminders LIKE 'send_overdue'");
if ($columns_check->num_rows == 0) {
    $conn->query("ALTER TABLE deadline_reminders ADD COLUMN send_overdue tinyint(1) DEFAULT 1 AFTER send_mail");
}

// Loop through assigned users
foreach ($assigned_users as $user_id) {
    $user_id = intval(trim($user_id));
    if ($user_id > 0) {
        // Check if reminder already exists for this user & task
        $existing = $conn->query("SELECT id FROM deadline_reminders WHERE task_id = $task_id AND user_id = $user_id AND reminder_status != 'dismissed'");
        
        if ($existing && $existing->num_rows > 0) {
            $existing_data = $existing->fetch_assoc();
            // Update existing reminder
            $update = $conn->query("UPDATE deadline_reminders SET 
                                    reminder_datetime = '$reminder_datetime',
                                    reminder_status = 'pending',
                                    send_notification = $reminder_notification,
                                    send_mail = $reminder_mail,
                                    send_overdue = $send_overdue
                                    WHERE id = " . $existing_data['id']);
            if ($update) $saved_count++;
        } else {
            // Insert new reminder
            $insert = $conn->query("INSERT INTO deadline_reminders 
                                    (task_id, user_id, reminder_datetime, reminder_status, send_notification, send_mail, send_overdue) 
                                    VALUES ($task_id, $user_id, '$reminder_datetime', 'pending', $reminder_notification, $reminder_mail, $send_overdue)");
            if ($insert) $saved_count++;
        }
    }
}

// Response
if ($saved_count > 0) {
    echo 1;
} else {
    echo "Error: Failed to save reminder. " . $conn->error;
}

ob_end_flush();
