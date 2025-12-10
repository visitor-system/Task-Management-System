<?php
session_start();
include 'db_connect.php';
header('Content-Type: application/json');
error_reporting(0);

$task_id = intval($_POST['task_id'] ?? 0);
$approval_status = trim($_POST['approval_status'] ?? '');
$remarks = trim($_POST['remarks'] ?? '');
$reviewed_by = $_SESSION['login_id'] ?? 0;

// Validate inputs
$valid_status = ['approved','feedback','rejected'];
if($task_id <= 0 || !in_array($approval_status, $valid_status)){
    echo json_encode(['status'=>0,'msg'=>'Invalid data received']);
    exit;
}

// Remarks required for feedback/rejected
if(($approval_status=='feedback'||$approval_status=='rejected') && empty($remarks)){
    echo json_encode(['status'=>0,'msg'=>'Remarks required for this approval status']);
    exit;
}

// Check if task exists
$qry = $conn->query("SELECT * FROM task_list WHERE task_id=$task_id");
if(!$qry || $qry->num_rows==0){
    echo json_encode(['status'=>0,'msg'=>'Task not found']);
    exit;
}

// Check if review already exists
$check = $conn->query("SELECT * FROM task_reviews WHERE task_id=$task_id AND reviewed_by=$reviewed_by");
if($check && $check->num_rows > 0){
    // Update
    $stmt = $conn->prepare("UPDATE task_reviews SET approval_status=?, remarks=? WHERE task_id=? AND reviewed_by=?");
    $stmt->bind_param("ssii",$approval_status,$remarks,$task_id,$reviewed_by);
    $stmt->execute();
    $stmt->close();
} else {
    // Insert
    $stmt = $conn->prepare("INSERT INTO task_reviews (task_id, reviewed_by, approval_status, remarks) VALUES (?,?,?,?)");
    $stmt->bind_param("iiss",$task_id,$reviewed_by,$approval_status,$remarks);
    $stmt->execute();
    $stmt->close();
}

// Optional: update task status in task_list
if($approval_status=='approved'){
    $conn->query("UPDATE task_list SET status=3 WHERE task_id=$task_id");
} else {
    $conn->query("UPDATE task_list SET status=2 WHERE task_id=$task_id");
}

echo json_encode(['status'=>1,'msg'=>'Review saved successfully']);
$conn->close();
exit;
