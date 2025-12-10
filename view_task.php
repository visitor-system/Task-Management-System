<?php
// view_task.php

// Start session safely
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

include 'db_connect.php';

// Check if task_id is provided
if(!isset($_GET['task_id']) || empty($_GET['task_id'])){
    die("Task not found.");
}

$task_id = intval($_GET['task_id']);

// Fetch task
$task_qry = $conn->query("SELECT * FROM task_list WHERE task_id = {$task_id}");
if(!$task_qry || $task_qry->num_rows == 0){
    die("Task not found.");
}

$task = $task_qry->fetch_assoc();

// Get Department
$department_name = 'N/A';
if(!empty($task['department_id'])){
    $dept_qry = $conn->query("SELECT name FROM departments WHERE id = ".intval($task['department_id']));
    if($dept_qry && $dept_qry->num_rows > 0){
        $dept_data = $dept_qry->fetch_assoc();
        $department_name = $dept_data['name'];
    }
}

// Get Assigned Users
$assigned_names = [];
if(!empty($task['assigned_to'])){
    $ids = array_map('intval', explode(',', $task['assigned_to']));
    if(count($ids) > 0){
        $user_qry = $conn->query("SELECT firstname, lastname FROM users WHERE id IN (".implode(',', $ids).")");
        while($u = $user_qry->fetch_assoc()){
            $assigned_names[] = ucwords($u['firstname'].' '.$u['lastname']);
        }
    }
}

// Get Attachments
$attachments = $conn->query("SELECT * FROM task_attachments WHERE task_id = {$task_id}");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Task Details</title>
<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
<style>
.task-container {
    background: #fff;
    border-radius: 12px;
    padding: 25px 30px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}
.task-header {
    font-size: 1.6rem;
    font-weight: 600;
    color: #2c3e50;
    margin-bottom: 20px;
    border-bottom: 3px solid #007bff;
    padding-bottom: 10px;
}
.task-info dt { font-weight: 600; color: #34495e; }
.task-info dd { margin-bottom: 15px; color: #2f3640; }
.list-group-item a { color: #007bff; text-decoration: none; }
.list-group-item a:hover { text-decoration: underline; }
</style>
</head>
<body>
<div class="container mt-3">
    <div class="task-container">
        <h4 class="task-header"><i class="fas fa-tasks"></i> Task Details</h4>

        <div class="row">
            <!-- Left Column -->
            <div class="col-md-6 task-info">
                <dl>
                    <dt>Task Title</dt>
                    <dd><?php echo ucwords(htmlspecialchars($task['task'])); ?></dd>
                </dl>

                <dl>
                    <dt>Description</dt>
                    <dd><?php echo html_entity_decode($task['description']); ?></dd>
                </dl>

                <dl>
                    <dt>Priority</dt>
                    <dd><?php echo !empty($task['priority']) ? htmlspecialchars($task['priority']) : 'Not Set'; ?></dd>
                </dl>

                <dl>
                    <dt>Deadline</dt>
                    <dd><?php echo !empty($task['deadline']) ? date("M d, Y", strtotime($task['deadline'])) : 'Not Set'; ?></dd>
                </dl>
            </div>

            <!-- Right Column -->
            <div class="col-md-6 task-info">
                <dl>
                    <dt>Status</dt>
                    <dd>
                        <?php 
                        $status = intval($task['status']);
                        if($status == 1) echo "<span class='badge badge-secondary'>Not Started</span>";
                        elseif($status == 2) echo "<span class='badge badge-primary'>In Progress</span>";
                        elseif($status == 3) echo "<span class='badge badge-success'>Completed</span>";
                        elseif($status == 4) echo "<span class='badge badge-warning'>On Hold</span>";
                        else echo "<span class='badge badge-dark'>Unknown</span>";
                        ?>
                    </dd>
                </dl>

                <dl>
                    <dt>Department</dt>
                    <dd><?php echo htmlspecialchars($department_name); ?></dd>
                </dl>

                <dl>
                    <dt>Assigned To</dt>
                    <dd><?php echo count($assigned_names) > 0 ? implode(', ', $assigned_names) : 'Not Assigned'; ?></dd>
                </dl>
            </div>
        </div>

        <!-- Attachments -->
        <hr>
        <div class="row">
            <div class="col-md-12">
                <dl>
                    <dt>Attachments</dt>
                    <dd>
                        <?php
                        if($attachments && $attachments->num_rows > 0){
                            echo '<ul class="list-group">';
                            while($att = $attachments->fetch_assoc()){
                                echo '<li class="list-group-item d-flex justify-content-between align-items-center">
                                        <a href="assets/uploads/tasks/'.htmlspecialchars($att['file_path']).'" target="_blank">'.htmlspecialchars($att['file_name']).'</a>
                                        <span class="badge badge-secondary">'.number_format($att['file_size']/1024,2).' KB</span>
                                      </li>';
                            }
                            echo '</ul>';
                        } else {
                            echo 'No attachments';
                        }
                        ?>
                    </dd>
                </dl>
            </div>
        </div>
    </div>
</div>
</body>
</html>
