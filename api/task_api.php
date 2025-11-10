<?php
// task_api.php
require_once '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['task_id']) && isset($_POST['status'])) {
    $task_id = $_POST['task_id'];
    $status = $_POST['status'];

    // Validate the status to ensure it is one of the allowed values
    $valid_statuses = ['Not Started', 'In Progress', 'Completed', 'On Hold'];
    if (!in_array($status, $valid_statuses)) {
        echo json_encode(['success' => false, 'message' => 'Invalid status']);
        exit();
    }

    // Update task status in the database
    $stmt = $conn->prepare("UPDATE tasks SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $task_id);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Task status updated successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update task status']);
    }
}
?>
