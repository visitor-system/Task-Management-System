<?php
ob_start();
date_default_timezone_set("Asia/Manila");

include 'db_connect.php';
include 'admin_class.php';
$crud = new Action();

$action = isset($_GET['action']) ? $_GET['action'] : '';

if($action == 'login'){
    $login = $crud->login();
    if($login) echo $login;
}
if($action == 'login2'){
    $login = $crud->login2();
    if($login) echo $login;
}
if($action == 'logout'){
    $logout = $crud->logout();
    if($logout) echo $logout;
}
if($action == 'logout2'){
    $logout = $crud->logout2();
    if($logout) echo $logout;
}

if($action == 'signup'){
    $save = $crud->signup();
    if($save) echo $save;
}
if($action == 'save_user'){
    $save = $crud->save_user();
    if($save) echo $save;
}
if($action == 'update_user'){
    $save = $crud->update_user();
    if($save) echo $save;
}
if($action == 'delete_user'){
    $save = $crud->delete_user();
    if($save) echo $save;
}
if($action == 'save_project'){
    $save = $crud->save_project();
    if($save) echo $save;
}
if($action == 'delete_project'){
    $save = $crud->delete_project();
    if($save) echo $save;
}
if($action == 'save_task'){
    $save = $crud->save_task();
    if($save) echo $save;
}
if($action == 'delete_task'){
    if(isset($_POST['id'])){
        $task_id = intval($_POST['id']);
        $del = $conn->query("DELETE FROM task_list WHERE task_id=$task_id");
        if($del){
            echo 1;
        } else {
            echo "Error deleting task: ".$conn->error;
        }
    } else {
        echo "Error: Task ID missing.";
    }
}
if($action == 'save_progress'){
    $save = $crud->save_progress();
    if($save) echo $save;
}
if($action == 'delete_progress'){
    $save = $crud->delete_progress();
    if($save) echo $save;
}
if($action == 'get_report'){
    $get = $crud->get_report();
    if($get) echo $get;
}
if($action == 'update_task_status'){
    $save = $crud->update_task_status();
    if($save) echo $save;
}

// ===== UPDATED save_task_review =====
if($action == 'save_task_review'){
    @ini_set('display_errors', 0);
    @error_reporting(0);
    while(ob_get_level() > 0){
        @ob_end_clean();
    }
    if(!headers_sent()){
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-cache, must-revalidate');
    }
    $response = ['status' => 0, 'msg' => ''];
    try {
        if(!isset($_POST['task_id'])){
            $response['msg'] = 'Task ID missing.';
            echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PARTIAL_OUTPUT_ON_ERROR);
            flush();
            exit;
        }

        $task_id = intval($_POST['task_id']);
        $approval_status = isset($_POST['approval_status']) ? trim($conn->real_escape_string($_POST['approval_status'])) : '';
        $remarks = isset($_POST['remarks']) ? trim($conn->real_escape_string($_POST['remarks'])) : '';
        
        if(!isset($_SESSION['login_id'])){
            $response['msg'] = 'User not logged in.';
            echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PARTIAL_OUTPUT_ON_ERROR);
            flush();
            exit;
        }
        
        $reviewed_by = intval($_SESSION['login_id']);

        if(empty($approval_status)){
            $response['msg'] = 'Approval status required.';
            echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PARTIAL_OUTPUT_ON_ERROR);
            flush();
            exit;
        }

        if(($approval_status == 'feedback' || $approval_status == 'rejected') && empty($remarks)){
            $response['msg'] = 'Remarks required for feedback or rejected status.';
            echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PARTIAL_OUTPUT_ON_ERROR);
            flush();
            exit;
        }

        $check = $conn->query("SELECT * FROM task_reviews WHERE task_id = $task_id AND reviewed_by = $reviewed_by");
        if(!$check){
            $response['msg'] = 'Database error: ' . $conn->error;
            echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PARTIAL_OUTPUT_ON_ERROR);
            flush();
            exit;
        }
        
        if($check && $check->num_rows > 0){
            $sql = "UPDATE task_reviews SET 
                        approval_status='$approval_status',
                        remarks='$remarks',
                        date_reviewed=NOW()
                    WHERE task_id=$task_id AND reviewed_by=$reviewed_by";
        } else {
            $sql = "INSERT INTO task_reviews (task_id, reviewed_by, approval_status, remarks, date_reviewed) 
                    VALUES ($task_id, $reviewed_by, '$approval_status', '$remarks', NOW())";
        }

        if($conn->query($sql)){
            if($approval_status == 'approved'){
                $update_task = $conn->query("UPDATE task_list SET status=3, approval_status='approved' WHERE id=$task_id");
                if(!$update_task){
                    $response['msg'] = 'Review saved but failed to update task status: ' . $conn->error;
                    echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PARTIAL_OUTPUT_ON_ERROR);
                    flush();
                    exit;
                }
            } elseif($approval_status == 'feedback'){
                $update_task = $conn->query("UPDATE task_list SET status=2, approval_status='feedback' WHERE id=$task_id");
                if(!$update_task){
                    $response['msg'] = 'Review saved but failed to update task status: ' . $conn->error;
                    echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PARTIAL_OUTPUT_ON_ERROR);
                    flush();
                    exit;
                }
            } elseif($approval_status == 'rejected'){
                $update_task = $conn->query("UPDATE task_list SET status=2, approval_status='rejected' WHERE id=$task_id");
                if(!$update_task){
                    $response['msg'] = 'Review saved but failed to update task status: ' . $conn->error;
                    echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PARTIAL_OUTPUT_ON_ERROR);
                    flush();
                    exit;
                }
            }
            $response['status'] = 1;
            $response['msg'] = 'Review saved successfully.';
            echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PARTIAL_OUTPUT_ON_ERROR);
            flush();
        } else {
            $response['msg'] = 'Error saving review: ' . $conn->error;
            echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PARTIAL_OUTPUT_ON_ERROR);
            flush();
        }
    } catch(Exception $e){
        $response['msg'] = 'Unexpected error: ' . $e->getMessage();
        echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PARTIAL_OUTPUT_ON_ERROR);
        flush();
    } catch(Error $e){
        $response['msg'] = 'Fatal error: ' . $e->getMessage();
        echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PARTIAL_OUTPUT_ON_ERROR);
        flush();
    }
    exit;
}

// ===== REMAINING ACTIONS =====
if($action == 'save_department'){
    $save = $crud->save_department();
    if($save) echo $save;
}
if($action == 'delete_department'){
    $save = $crud->delete_department();
    if($save) echo $save;
}
if($action == 'get_task_statistics'){
    $stats = $crud->get_task_statistics();
    echo json_encode($stats);
}
if($action == 'mark_notification_read'){
    extract($_POST);
    $update = $conn->query("UPDATE notifications SET is_read = 1 WHERE id = $id");
    if($update) echo 1;
}
if($action == 'send_reminder_email'){
    $sent = $crud->send_reminder_email();
    echo $sent;
}
if($action == 'check_deadline_reminders'){
    $count = $crud->check_deadline_reminders();
    echo $count;
}
if($action == 'process_scheduled_reminders'){
    $count = $crud->process_scheduled_reminders();
    echo $count;
}
if($action == 'send_task_reminder'){
    if(!isset($_POST['task_id'])){
        echo "Error: Task ID missing.";
        exit;
    }
    $task_id = intval($_POST['task_id']);
    $result = $crud->send_task_reminder($task_id);
    if($result > 0){
        echo 1;
    } else {
        echo "Error: Failed to send reminder. Check if task exists and has assigned users.";
    }
}
if($action == 'save_task_reminder'){
    if(!isset($_POST['task_id']) || !isset($_POST['reminder_datetime'])){
        echo "Error: Task ID or reminder date missing.";
        exit;
    }
    
    $task_id = intval($_POST['task_id']);
    $reminder_datetime = $conn->real_escape_string($_POST['reminder_datetime']);
    $reminder_notification = isset($_POST['reminder_notification']) ? 1 : 0;
    $reminder_mail = isset($_POST['reminder_mail']) ? 1 : 0;
    $send_overdue = isset($_POST['send_overdue_notification']) ? 1 : 0;
    $reminder_id = isset($_POST['reminder_id']) ? intval($_POST['reminder_id']) : 0;
    
    $task_check = $conn->query("SELECT id, assigned_to FROM task_list WHERE id = $task_id");
    if(!$task_check || $task_check->num_rows == 0){
        echo "Error: Task not found.";
        exit;
    }
    
    $task = $task_check->fetch_assoc();
    
    if(empty($task['assigned_to'])){
        echo "Error: No users assigned to this task.";
        exit;
    }
    
    $assigned_users = explode(',', $task['assigned_to']);
    $saved_count = 0;
    foreach($assigned_users as $user_id){
        $user_id = intval(trim($user_id));
        if($user_id > 0){
            $existing = $conn->query("SELECT id FROM deadline_reminders WHERE task_id = $task_id AND user_id = $user_id AND reminder_status != 'dismissed'");
            if($existing && $existing->num_rows > 0){
                $existing_data = $existing->fetch_assoc();
                $update = $conn->query("UPDATE deadline_reminders SET 
                                        reminder_datetime = '$reminder_datetime',
                                        reminder_status = 'pending',
                                        send_notification = $reminder_notification,
                                        send_mail = $reminder_mail,
                                        send_overdue = $send_overdue
                                        WHERE id = " . $existing_data['id']);
                if($update) $saved_count++;
            } else {
                $insert = $conn->query("INSERT INTO deadline_reminders 
                                        (task_id, user_id, reminder_datetime, reminder_status, send_notification, send_mail, send_overdue) 
                                        VALUES ($task_id, $user_id, '$reminder_datetime', 'pending', $reminder_notification, $reminder_mail, $send_overdue)");
                if($insert) $saved_count++;
            }
        }
    }
    
    if($saved_count > 0){
        echo 1;
    } else {
        echo "Error: Failed to save reminder. " . $conn->error;
    }
}
if($action == 'send_daily_summary'){
    $count = $crud->send_daily_summary_email();
    echo $count;
}
if($action == 'get_department_statistics'){
    $stats = $crud->get_department_statistics();
    echo json_encode($stats);
}
if($action == 'get_recent_activities'){
    extract($_GET);
    $limit = isset($limit) ? (int)$limit : 20;
    $activities = $crud->get_recent_activities($limit);
    $data = array();
    while($row = $activities->fetch_assoc()){
        $data[] = $row;
    }
    echo json_encode($data);
}
if($action == 'get_unread_activity_count'){
    $user_id = $_SESSION['login_id'];
    $count = $crud->get_unread_activity_count($user_id);
    echo $count;
}
if($action == 'get_email_stats'){
    $pending = $conn->query("SELECT COUNT(*) as cnt FROM email_logs WHERE sent_status='pending'")->fetch_assoc()['cnt'];
    $sent = $conn->query("SELECT COUNT(*) as cnt FROM email_logs WHERE sent_status='sent'")->fetch_assoc()['cnt'];
    echo json_encode(array('pending'=>$pending,'sent'=>$sent));
}
if($action == 'save_email_settings'){
    extract($_POST);
    $data = "";
    foreach($_POST as $k => $v){
        if(!in_array($k,array('id')) && !is_numeric($k)){
            $v_escaped = $conn->real_escape_string($v);
            if(empty($data)) $data .= " $k='$v_escaped' ";
            else $data .= ", $k='$v_escaped' ";
        }
    }
    $chk = $conn->query("SELECT * FROM system_settings");
    if($chk && $chk->num_rows > 0){
        $save = $conn->query("UPDATE system_settings set $data where id=".$chk->fetch_array()['id']);
    } else {
        $save = $conn->query("INSERT INTO system_settings set $data");
    }
    if($save){
        foreach($_POST as $k => $v){
            if(!is_numeric($k)) $_SESSION['system'][$k]=$v;
        }
        echo 1;
    } else {
        echo "Error: " . $conn->error;
    }
}
if($action == 'send_test_email'){
    $subject = "Test Email - Task Management System";
    $message = "<p>This is a test email from the Task Management System.</p>";
    $message .= "<p>If you received this email, your email configuration is working correctly.</p>";
    $result = $crud->send_email_notification($_SESSION['login_id'], $_SESSION['login_email'], $subject, $message, 'test', null);
    echo $result;
}
if($action == 'get_audit_logs'){
    $from = isset($_GET['from'])&&!empty($_GET['from'])?$_GET['from']:date('Y-m-d',strtotime('-30 days'));
    $to = isset($_GET['to'])&&!empty($_GET['to'])?$_GET['to']:date('Y-m-d');
    $module = isset($_GET['module'])?$_GET['module']:'';
    $where = "WHERE DATE(al.date_created) BETWEEN '$from' AND '$to'";
    if(!empty($module)){
        $where .= " AND al.module='".mysqli_real_escape_string($conn,$module)."'";
    }
    $logs = $conn->query("SELECT al.*, CONCAT(u.firstname,' ',u.lastname) as user_name FROM system_audit_log al 
        LEFT JOIN users u ON al.user_id = u.id 
        $where ORDER BY al.date_created DESC LIMIT 500");
    $data = array();
    while($row=$logs->fetch_assoc()){
        $data[]=$row;
    }
    echo json_encode($data);
}
if($action == 'save_notification_preferences'){
    extract($_POST);
    $email_on_task_assign = isset($email_on_task_assign)?1:0;
    $email_on_status_change = isset($email_on_status_change)?1:0;
    $email_on_deadline_reminder = isset($email_on_deadline_reminder)?1:0;
    $email_on_task_completion = isset($email_on_task_completion)?1:0;
    $daily_summary_email = isset($daily_summary_email)?1:0;
    $summary_time = isset($summary_time)?$summary_time:'08:00:00';
    $timezone = isset($timezone)?$conn->real_escape_string($timezone):'UTC';
    $save = $conn->query("UPDATE user_preferences SET 
        email_on_task_assign=$email_on_task_assign,
        email_on_status_change=$email_on_status_change,
        email_on_deadline_reminder=$email_on_deadline_reminder,
        email_on_task_completion=$email_on_task_completion,
        daily_summary_email=$daily_summary_email,
        summary_time='$summary_time',
        timezone='$timezone'
        WHERE user_id={$_SESSION['login_id']}");
    if($save) echo 1; else echo 0;
}
if($action == 'send_test_notification'){
    $subject = "Test Notification - Task Management System";
    $message = "<h3>Test Notification Email</h3><p>This is a test email to verify your notification preferences.</p>";
    $message .= "<p>You can adjust your notification settings anytime from your profile.</p>";
    $result = $crud->send_email_notification($_SESSION['login_id'], $_SESSION['login_email'], $subject, $message, 'test', null);
    echo $result;
}

ob_end_flush();
?>
