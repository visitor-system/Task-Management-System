<?php
session_start();
include 'db_connect.php';

if($_SERVER['REQUEST_METHOD'] == 'POST'){
    $task_id = isset($_POST['task_id']) ? intval($_POST['task_id']) : 0;
    $status = isset($_POST['status']) ? intval($_POST['status']) : 1;
    $remarks = isset($_POST['remarks']) ? $conn->real_escape_string($_POST['remarks']) : '';
    $progress_remarks = isset($_POST['progress_remarks']) ? $conn->real_escape_string($_POST['progress_remarks']) : '';

    if($task_id > 0){
        $sql = "UPDATE task_list 
                SET status = ?, remarks = ?, progress_remarks = ? 
                WHERE task_id = ?";
        $stmt = $conn->prepare($sql);
        if(!$stmt){
            echo "Failed to prepare statement: " . $conn->error;
            exit;
        }
        $stmt->bind_param("issi", $status, $remarks, $progress_remarks, $task_id);
        if($stmt->execute()){
            echo 1; // success
        } else {
            echo "Failed to update task: " . $stmt->error;
        }
        $stmt->close();
    } else {
        echo "Invalid Task ID.";
    }
} else {
    echo "Invalid request method.";
}
?>
