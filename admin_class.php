<?php
session_start();
ini_set('display_errors', 1);
Class Action {
	private $db;

	public function __construct() {
		ob_start();
   	include 'db_connect.php';
    
    $this->db = $conn;
	}
	function __destruct() {
	    $this->db->close();
	    ob_end_flush();
	}

	function login(){
		extract($_POST);
			$qry = $this->db->query("SELECT *,concat(firstname,' ',lastname) as name FROM users where email = '".$email."' and password = '".md5($password)."'  ");
		if($qry->num_rows > 0){
			foreach ($qry->fetch_array() as $key => $value) {
				if($key != 'password' && !is_numeric($key))
					$_SESSION['login_'.$key] = $value;
			}
				return 1;
		}else{
			return 2;
		}
	}
	function logout(){
		session_destroy();
		foreach ($_SESSION as $key => $value) {
			unset($_SESSION[$key]);
		}
		header("location:login.php");
	}
	function login2(){
		extract($_POST);
			$qry = $this->db->query("SELECT *,concat(lastname,', ',firstname,' ',middlename) as name FROM students where student_code = '".$student_code."' ");
		if($qry->num_rows > 0){
			foreach ($qry->fetch_array() as $key => $value) {
				if($key != 'password' && !is_numeric($key))
					$_SESSION['rs_'.$key] = $value;
			}
				return 1;
		}else{
			return 3;
		}
	}
	function save_user(){
		extract($_POST);
		$data = "";
		foreach($_POST as $k => $v){
			if(!in_array($k, array('id','cpass','password')) && !is_numeric($k)){
				if(empty($data)){
					$data .= " $k='$v' ";
				}else{
					$data .= ", $k='$v' ";
				}
			}
		}
		if(!empty($password)){
					$data .= ", password=md5('$password') ";

		}
		$check = $this->db->query("SELECT * FROM users where email ='$email' ".(!empty($id) ? " and id != {$id} " : ''))->num_rows;
		if($check > 0){
			return 2;
			exit;
		}
		if(isset($_FILES['img']) && $_FILES['img']['tmp_name'] != ''){
			$fname = strtotime(date('y-m-d H:i')).'_'.$_FILES['img']['name'];
			$move = move_uploaded_file($_FILES['img']['tmp_name'],'assets/uploads/'. $fname);
			$data .= ", avatar = '$fname' ";

		}
		if(empty($id)){
			$save = $this->db->query("INSERT INTO users set $data");
		}else{
			$save = $this->db->query("UPDATE users set $data where id = $id");
		}

		if($save){
			return 1;
		}
	}
	function signup(){
		extract($_POST);
		$data = "";
		foreach($_POST as $k => $v){
			if(!in_array($k, array('id','cpass')) && !is_numeric($k)){
				if($k =='password'){
					if(empty($v))
						continue;
					$v = md5($v);

				}
				if(empty($data)){
					$data .= " $k='$v' ";
				}else{
					$data .= ", $k='$v' ";
				}
			}
		}

		$check = $this->db->query("SELECT * FROM users where email ='$email' ".(!empty($id) ? " and id != {$id} " : ''))->num_rows;
		if($check > 0){
			return 2;
			exit;
		}
		if(isset($_FILES['img']) && $_FILES['img']['tmp_name'] != ''){
			$fname = strtotime(date('y-m-d H:i')).'_'.$_FILES['img']['name'];
			$move = move_uploaded_file($_FILES['img']['tmp_name'],'assets/uploads/'. $fname);
			$data .= ", avatar = '$fname' ";

		}
		if(empty($id)){
			$save = $this->db->query("INSERT INTO users set $data");

		}else{
			$save = $this->db->query("UPDATE users set $data where id = $id");
		}

		if($save){
			if(empty($id))
				$id = $this->db->insert_id;
			foreach ($_POST as $key => $value) {
				if(!in_array($key, array('id','cpass','password')) && !is_numeric($key))
					$_SESSION['login_'.$key] = $value;
			}
					$_SESSION['login_id'] = $id;
				if(isset($_FILES['img']) && !empty($_FILES['img']['tmp_name']))
					$_SESSION['login_avatar'] = $fname;
			return 1;
		}
	}

	function update_user(){
		extract($_POST);
		$data = "";
		foreach($_POST as $k => $v){
			if(!in_array($k, array('id','cpass','table','password')) && !is_numeric($k)){
				
				if(empty($data)){
					$data .= " $k='$v' ";
				}else{
					$data .= ", $k='$v' ";
				}
			}
		}
		$check = $this->db->query("SELECT * FROM users where email ='$email' ".(!empty($id) ? " and id != {$id} " : ''))->num_rows;
		if($check > 0){
			return 2;
			exit;
		}
		if(isset($_FILES['img']) && $_FILES['img']['tmp_name'] != ''){
			$fname = strtotime(date('y-m-d H:i')).'_'.$_FILES['img']['name'];
			$move = move_uploaded_file($_FILES['img']['tmp_name'],'assets/uploads/'. $fname);
			$data .= ", avatar = '$fname' ";

		}
		if(!empty($password))
			$data .= " ,password=md5('$password') ";
		if(empty($id)){
			$save = $this->db->query("INSERT INTO users set $data");
		}else{
			$save = $this->db->query("UPDATE users set $data where id = $id");
		}

		if($save){
			foreach ($_POST as $key => $value) {
				if($key != 'password' && !is_numeric($key))
					$_SESSION['login_'.$key] = $value;
			}
			if(isset($_FILES['img']) && !empty($_FILES['img']['tmp_name']))
					$_SESSION['login_avatar'] = $fname;
			return 1;
		}
	}
	function delete_user(){
		extract($_POST);
		$delete = $this->db->query("DELETE FROM users where id = ".$id);
		if($delete)
			return 1;
	}
	function save_system_settings(){
		extract($_POST);
		$data = '';
		foreach($_POST as $k => $v){
			if(!is_numeric($k)){
				if(empty($data)){
					$data .= " $k='$v' ";
				}else{
					$data .= ", $k='$v' ";
				}
			}
		}
		if($_FILES['cover']['tmp_name'] != ''){
			$fname = strtotime(date('y-m-d H:i')).'_'.$_FILES['cover']['name'];
			$move = move_uploaded_file($_FILES['cover']['tmp_name'],'../assets/uploads/'. $fname);
			$data .= ", cover_img = '$fname' ";

		}
		$chk = $this->db->query("SELECT * FROM system_settings");
		if($chk->num_rows > 0){
			$save = $this->db->query("UPDATE system_settings set $data where id =".$chk->fetch_array()['id']);
		}else{
			$save = $this->db->query("INSERT INTO system_settings set $data");
		}
		if($save){
			foreach($_POST as $k => $v){
				if(!is_numeric($k)){
					$_SESSION['system'][$k] = $v;
				}
			}
			if($_FILES['cover']['tmp_name'] != ''){
				$_SESSION['system']['cover_img'] = $fname;
			}
			return 1;
		}
	}
	function save_image(){
		extract($_FILES['file']);
		if(!empty($tmp_name)){
			$fname = strtotime(date("Y-m-d H:i"))."_".(str_replace(" ","-",$name));
			$move = move_uploaded_file($tmp_name,'assets/uploads/'. $fname);
			$protocol = strtolower(substr($_SERVER["SERVER_PROTOCOL"],0,5))=='https'?'https':'http';
			$hostName = $_SERVER['HTTP_HOST'];
			$path =explode('/',$_SERVER['PHP_SELF']);
			$currentPath = '/'.$path[1]; 
			if($move){
				return $protocol.'://'.$hostName.$currentPath.'/assets/uploads/'.$fname;
			}
		}
	}

	function save_task(){
		extract($_POST);
		$data = "";
		
		// Map due_date to deadline for backward compatibility
		if(isset($due_date) && !empty($due_date)){
			$_POST['deadline'] = $due_date;
		}
		
		foreach($_POST as $k => $v){
			if(!in_array($k, array('id','assigned_to','attachments','due_date','start_date')) && !is_numeric($k)){
				if($k == 'description' || $k == 'progress_remarks'){
					$v = $this->db->real_escape_string(htmlentities(str_replace("'","&#x2019;",$v)));
				} else {
					$v = $this->db->real_escape_string($v);
				}
				if(empty($data)){
					$data .= " $k='$v' ";
				}else{
					$data .= ", $k='$v' ";
				}
			}
		}
		
		// Add start_date if provided
		if(isset($start_date) && !empty($start_date)){
			$start_date_escaped = $this->db->real_escape_string($start_date);
			if(empty($data)){
				$data .= " start_date='$start_date_escaped' ";
			}else{
				$data .= ", start_date='$start_date_escaped' ";
			}
		}
		if(isset($assigned_to) && is_array($assigned_to)){
			$assigned_to = array_map('intval', $assigned_to);
			$data .= ", assigned_to='".implode(',',$assigned_to)."' ";
		}
		if(empty($id)){
			if(!isset($assigned_by))
				$data .= ", assigned_by={$_SESSION['login_id']} ";
			$save = $this->db->query("INSERT INTO task_list set $data");
			$task_id = $this->db->insert_id;
		}else{
			$task_id = intval($id);
			$save = $this->db->query("UPDATE task_list set $data where id = $task_id");
		}
		if($save){
			// Handle file attachments - now process after task is created/updated
			if(isset($_FILES['attachments']) && !empty($_FILES['attachments']['name'][0])){
				$this->save_task_attachments($task_id, $_FILES['attachments']);
			}
			// Create notifications for assigned users
			if(isset($assigned_to) && is_array($assigned_to)){
				$task_name = isset($_POST['task']) ? $_POST['task'] : 'New Task';
				$this->create_notification($assigned_to, 'task_assigned', 'New Task Assigned', "You have been assigned a new task: ".$task_name, $task_id);
			}
			return 1;
		} else {
			// Return DB error message for debugging
			return 'DB_ERROR: ' . $this->db->error;
		}
	}
	function save_task_attachments($task_id, $files){
		$upload_dir = 'assets/uploads/tasks/';
		if(!is_dir($upload_dir)){
			mkdir($upload_dir, 0777, true);
		}
		
		$task_id = intval($task_id);
		
		foreach($files['name'] as $key => $name){
			if(!empty($name) && !empty($files['tmp_name'][$key])){
				// Create unique filename with timestamp
				$timestamp = time();
				$extension = pathinfo($name, PATHINFO_EXTENSION);
				$fname = $timestamp . '_' . basename($name);
				$file_path = $upload_dir . $fname;
				
				// Validate and move file
				if(move_uploaded_file($files['tmp_name'][$key], $file_path)){
					$file_size = intval($files['size'][$key]);
					$file_type = $this->db->real_escape_string($files['type'][$key]);
					$file_name = $this->db->real_escape_string($name);
					$user_id = intval($_SESSION['login_id']);
					
					// Insert into database
					$insert = $this->db->query("INSERT INTO task_attachments (task_id, file_name, file_path, file_type, file_size, uploaded_by, date_created) VALUES ($task_id, '$file_name', '$fname', '$file_type', $file_size, $user_id, NOW())");
					
					if(!$insert){
						// If database insert fails, remove the uploaded file
						unlink($file_path);
					}
				}
			}
		}
	}

	function update_task_status(){
		extract($_POST);
		$update = $this->db->query("UPDATE task_list set status='$status' ".($progress_remarks ? ", progress_remarks='".htmlentities($progress_remarks)."' " : "")." where task_id = $id");
		if($update){
			// Log progress
			$this->db->query("INSERT INTO task_progress_log (task_id, user_id, status, progress_percentage, remarks, date_logged) VALUES ($id, {$_SESSION['login_id']}, '$status', ".($progress_percentage ? $progress_percentage : 0).", '".htmlentities($remarks)."', CURDATE())");
			// Get task creator
			$task = $this->db->query("SELECT assigned_by, task FROM task_list WHERE task_id = $task_id")->fetch_assoc();
			if($task['assigned_by']){
				$this->create_notification(array($task['assigned_by']), 'status_update', 'Task Status Updated', "Task '{$task['task']}' status has been updated to: $status", $task_id);
			}
			return 1;
		}
	}
	function save_task_review(){
		extract($_POST);
		
		// Use new approval_status field if present, otherwise fall back to old status field
		$approval = isset($approval_status) ? $approval_status : (isset($status) ? $status : 'pending');
		$approval = strtolower($approval);
		
		// Map old values to new ones for backward compatibility
		if($approval == 'approved') $approval = 'approved';
		elseif($approval == 'rejected') $approval = 'rejected';
		elseif($approval == 'feedback') $approval = 'feedback';
		else $approval = 'pending';

		$remarks = isset($remarks) ? $this->db->real_escape_string(htmlentities($remarks)) : '';
		$task_id = intval($task_id);
		$reviewed_by = intval($_SESSION['login_id']);

		// Insert or update review record
		$data = "task_id='$task_id', reviewed_by=$reviewed_by, approval_status='$approval', remarks='$remarks'";
		if($approval != 'pending')
			$data .= ", date_reviewed=NOW()";

		$save = $this->db->query("INSERT INTO task_reviews set $data ON DUPLICATE KEY UPDATE $data");
		
		if($save){
			// Get task details
			$task = $this->db->query("SELECT assigned_to, task, status FROM task_list WHERE id = $task_id")->fetch_assoc();
			
			// Handle different approval decisions
			if($approval == 'approved'){
				// Only change status if task is in Completed status (3)
				// Department head approval moves it to truly completed
				$this->db->query("UPDATE task_list set status=3, approval_status='approved', approval_date=NOW() where id = $task_id");
				$notification_msg = "Your task '{$task['task']}' has been APPROVED by department head. Status changed to Completed.";
				
				// Log activity
				$this->log_activity("Task Approved", "Department head approved task: {$task['task']}", $task_id);
			}
			elseif($approval == 'rejected'){
				// Reopen task back to In Progress for rework
				$this->db->query("UPDATE task_list set status=2, approval_status='rejected', approval_date=NOW() where id = $task_id");
				$notification_msg = "Your task '{$task['task']}' has been REJECTED by department head. Please review feedback and resubmit.";
				
				// Log activity
				$this->log_activity("Task Rejected", "Department head rejected task: {$task['task']}, requiring rework", $task_id);
			}
			elseif($approval == 'feedback'){
				// Keep task as is, send feedback notification
				$this->db->query("UPDATE task_list set approval_status='feedback', approval_date=NOW() where id = $task_id");
				$notification_msg = "Your task '{$task['task']}' needs revision. Department head sent feedback - please review and resubmit.";
				
				// Log activity
				$this->log_activity("Task Feedback", "Department head requested feedback on task: {$task['task']}", $task_id);
			}
			else {
				$notification_msg = "Your task '{$task['task']}' review is pending.";
			}

			// Notify task assignee(s)
			if($task['assigned_to']){
				$user_ids = explode(',', $task['assigned_to']);
				$user_ids = array_map('intval', array_map('trim', $user_ids));
				$user_ids = array_filter($user_ids);
				
				if(!empty($user_ids)){
					$title = $approval == 'approved' ? 'Task Approved ✓' : ($approval == 'rejected' ? 'Task Rejected - Rework Needed' : 'Task Feedback Received');
					$this->create_notification($user_ids, 'task_review', $title, $notification_msg, $task_id);
					
					// Also send email if notifications are enabled - suppress errors to allow task operations to complete
					if(isset($_SESSION['system']['email_notifications_enabled']) && $_SESSION['system']['email_notifications_enabled']){
						foreach($user_ids as $uid){
							@$this->send_email_notification($uid, $title, $notification_msg."\n\nFeedback: ".$remarks, $task_id);
						}
					}
				}
			}
			
			return 1;
		}
		return 0;
	}
	function save_department(){
		extract($_POST);
		$data = "";
		foreach($_POST as $k => $v){
			if(!in_array($k, array('id')) && !is_numeric($k)){
				if($k == 'description')
					$v = htmlentities(str_replace("'","&#x2019;",$v));
				if(empty($data)){
					$data .= " $k='$v' ";
				}else{
					$data .= ", $k='$v' ";
				}
			}
		}
		if(empty($id)){
			$save = $this->db->query("INSERT INTO departments set $data");
		}else{
			$save = $this->db->query("UPDATE departments set $data where id = $id");
		}
		if($save){
			return 1;
		}
	}
	function delete_department(){
		extract($_POST);
		$delete = $this->db->query("DELETE FROM departments where id = $id");
		if($delete){
			return 1;
		}
	}
	function get_task_statistics(){
		$where = "";
		if($_SESSION['login_type'] == 2){
			$where = " where assigned_by = '{$_SESSION['login_id']}' OR assigned_to LIKE '%{$_SESSION['login_id']}%' ";
		}elseif($_SESSION['login_type'] == 3){
			$where = " where assigned_to LIKE '%{$_SESSION['login_id']}%' ";
		}
		$stats = array();
		$stats['total'] = $this->db->query("SELECT * FROM task_list $where")->num_rows;
		$stats['pending'] = $this->db->query("SELECT * FROM task_list $where AND status = 1")->num_rows;
		$stats['in_progress'] = $this->db->query("SELECT * FROM task_list $where AND status = 2")->num_rows;
		$stats['completed'] = $this->db->query("SELECT * FROM task_list $where AND status = 3")->num_rows;
		$stats['on_hold'] = $this->db->query("SELECT * FROM task_list $where AND status = 4")->num_rows;
		$stats['overdue'] = $this->db->query("SELECT * FROM task_list $where AND deadline < CURDATE() AND status != 3")->num_rows;
		$stats['today'] = $this->db->query("SELECT * FROM task_list $where AND deadline = CURDATE()")->num_rows;
		return $stats;
	}
	function delete_task(){
		extract($_POST);
		$delete = $this->db->query("DELETE FROM task_list where id = $id");
		if($delete){
			return 1;
		}
	}
	function save_progress(){
		extract($_POST);
		$data = "";
		foreach($_POST as $k => $v){
			if(!in_array($k, array('id')) && !is_numeric($k)){
				if($k == 'comment')
					$v = htmlentities(str_replace("'","&#x2019;",$v));
				if(empty($data)){
					$data .= " $k='$v' ";
				}else{
					$data .= ", $k='$v' ";
				}
			}
		}
		$dur = abs(strtotime("2020-01-01 ".$end_time)) - abs(strtotime("2020-01-01 ".$start_time));
		$dur = $dur / (60 * 60);
		$data .= ", time_rendered='$dur' ";
		// echo "INSERT INTO user_productivity set $data"; exit;
		if(empty($id)){
			$data .= ", user_id={$_SESSION['login_id']} ";
			
			$save = $this->db->query("INSERT INTO user_productivity set $data");
		}else{
			$save = $this->db->query("UPDATE user_productivity set $data where id = $id");
		}
		if($save){
			return 1;
		}
	}
	function delete_progress(){
		extract($_POST);
		$delete = $this->db->query("DELETE FROM user_productivity where id = $id");
		if($delete){
			return 1;
		}
	}
	function get_report(){
		extract($_POST);
		$data = array();
		$get = $this->db->query("SELECT t.*,p.name as ticket_for FROM ticket_list t inner join pricing p on p.id = t.pricing_id where date(t.date_created) between '$date_from' and '$date_to' order by unix_timestamp(t.date_created) desc ");
		while($row= $get->fetch_assoc()){
			$row['date_created'] = date("M d, Y",strtotime($row['date_created']));
			$row['name'] = ucwords($row['name']);
			$row['adult_price'] = number_format($row['adult_price'],2);
			$row['child_price'] = number_format($row['child_price'],2);
			$row['amount'] = number_format($row['amount'],2);
			$data[]=$row;
		}
		return json_encode($data);

	}

	// ========== NEW FEATURE METHODS ==========

	// 1. FEATURE: Email Notifications
	function send_email_notification($user_id, $email, $subject, $message, $type, $task_id = null){
		// Check if email notifications are enabled
		$settings = $this->db->query("SELECT enable_email_notifications, email_use_smtp, email_from, email_host, email_port, email_username, email_password FROM system_settings LIMIT 1");
		if($settings && $settings->num_rows > 0){
			$email_settings = $settings->fetch_assoc();
			if(isset($email_settings['enable_email_notifications']) && $email_settings['enable_email_notifications'] == 0){
				// Email notifications disabled
				return 0;
			}
		} else {
			// No settings found, assume disabled
			return 0;
		}
		
		$system_email = isset($email_settings['email_from']) && !empty($email_settings['email_from']) ? $email_settings['email_from'] : 'noreply@taskmanagement.com';
		
		$html_message = "<html><body style='font-family: Arial, sans-serif; background-color: #f4f4f4;'>";
		$html_message .= "<div style='max-width: 600px; margin: 20px auto; background-color: white; padding: 20px; border-radius: 5px; box-shadow: 0 0 10px rgba(0,0,0,0.1);'>";
		$html_message .= "<h2 style='color: #2196F3; border-bottom: 2px solid #2196F3; padding-bottom: 10px;'>" . htmlspecialchars($subject) . "</h2>";
		$html_message .= "<div style='margin: 20px 0; line-height: 1.6;'>" . $message . "</div>";
		$html_message .= "<hr style='border: none; border-top: 1px solid #ddd; margin: 20px 0;'>";
		$html_message .= "<p style='color: #666; font-size: 12px; text-align: center;'>This is an automated email from Task Management System. Please do not reply directly.</p>";
		$html_message .= "</div></body></html>";
		
		// Log email in database
		$email_escaped = $this->db->real_escape_string($email);
		$subject_escaped = $this->db->real_escape_string($subject);
		$message_escaped = $this->db->real_escape_string($message);
		
		$log_query = "INSERT INTO email_logs (user_id, recipient_email, subject, message, type, task_id, sent_status) 
						VALUES ($user_id, '$email_escaped', '$subject_escaped', '$message_escaped', '$type', ".($task_id ? $task_id : 'NULL').", 'pending')";
		$this->db->query($log_query);
		
		$email_sent = false;
		
		// Send email using SMTP or PHP mail()
		try {
			// Check if SMTP is enabled
			if(isset($email_settings['email_use_smtp']) && $email_settings['email_use_smtp'] == 1 && !empty($email_settings['email_host'])){
				// Use SMTP
				$email_sent = $this->send_email_via_smtp(
					$email_settings['email_host'],
					$email_settings['email_port'] ? intval($email_settings['email_port']) : 25,
					$email_settings['email_username'] ?? '',
					$email_settings['email_password'] ?? '',
					$system_email,
					$email,
					$subject,
					$html_message
				);
			} else {
				// Use PHP mail() function
				$headers = "From: " . $system_email . "\r\n";
				$headers .= "Reply-To: " . $system_email . "\r\n";
				$headers .= "Content-Type: text/html; charset=UTF-8\r\n";
				$headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";
				
				$email_sent = @mail($email, $subject, $html_message, $headers);
			}
			
			if($email_sent){
				$this->db->query("UPDATE email_logs SET sent_status = 'sent', last_attempt = NOW() WHERE recipient_email = '$email_escaped' AND sent_status = 'pending' ORDER BY id DESC LIMIT 1");
				return 1;
			} else {
				$this->db->query("UPDATE email_logs SET sent_status = 'failed', attempts = attempts + 1, last_attempt = NOW() WHERE recipient_email = '$email_escaped' AND sent_status = 'pending' ORDER BY id DESC LIMIT 1");
				return 0;
			}
		} catch (Exception $e) {
			// Log email attempt failure
			$error_msg = $this->db->real_escape_string($e->getMessage());
			$this->db->query("UPDATE email_logs SET sent_status = 'failed', attempts = attempts + 1, last_attempt = NOW() WHERE recipient_email = '$email_escaped' AND sent_status = 'pending' ORDER BY id DESC LIMIT 1");
			return 0;
		}
	}
	
	// SMTP email sending function
	private function send_email_via_smtp($host, $port, $username, $password, $from_email, $to_email, $subject, $message){
		// Use SSL for port 465, regular connection for others
		if($port == 465){
			$context = stream_context_create();
			$smtp = @stream_socket_client("ssl://" . $host . ":" . $port, $errno, $errstr, 30, STREAM_CLIENT_CONNECT, $context);
		} else {
			$smtp = @fsockopen($host, $port, $errno, $errstr, 30);
		}
		
		if(!$smtp){
			error_log("SMTP Connection failed: $errstr ($errno)");
			return false;
		}
		
		// Read initial response
		$response = fgets($smtp, 515);
		if(substr($response, 0, 3) != '220'){
			fclose($smtp);
			error_log("SMTP Initial response failed: " . trim($response));
			return false;
		}
		
		// Send EHLO
		fputs($smtp, "EHLO " . $host . "\r\n");
		$response = fgets($smtp, 515);
		
		// If authentication is required
		if(!empty($username) && !empty($password)){
			// Start TLS if port is 587 (not 465 which already uses SSL)
			if($port == 587){
				fputs($smtp, "STARTTLS\r\n");
				$response = fgets($smtp, 515);
				if(substr($response, 0, 3) == '220'){
					stream_socket_enable_crypto($smtp, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
					fputs($smtp, "EHLO " . $host . "\r\n");
					$response = fgets($smtp, 515);
				}
			}
			
			// Authenticate
			fputs($smtp, "AUTH LOGIN\r\n");
			$response = fgets($smtp, 515);
			if(substr($response, 0, 3) != '334'){
				fclose($smtp);
				error_log("SMTP AUTH LOGIN failed: " . trim($response));
				return false;
			}
			
			fputs($smtp, base64_encode($username) . "\r\n");
			$response = fgets($smtp, 515);
			if(substr($response, 0, 3) != '334'){
				fclose($smtp);
				error_log("SMTP Username failed: " . trim($response));
				return false;
			}
			
			fputs($smtp, base64_encode($password) . "\r\n");
			$response = fgets($smtp, 515);
			if(substr($response, 0, 3) != '235'){
				fclose($smtp);
				error_log("SMTP Authentication failed: " . trim($response));
				return false;
			}
		}
		
		// Send MAIL FROM
		fputs($smtp, "MAIL FROM: <" . $from_email . ">\r\n");
		$response = fgets($smtp, 515);
		if(substr($response, 0, 3) != '250'){
			fclose($smtp);
			error_log("SMTP MAIL FROM failed: " . trim($response));
			return false;
		}
		
		// Send RCPT TO
		fputs($smtp, "RCPT TO: <" . $to_email . ">\r\n");
		$response = fgets($smtp, 515);
		if(substr($response, 0, 3) != '250'){
			fclose($smtp);
			error_log("SMTP RCPT TO failed: " . trim($response));
			return false;
		}
		
		// Send DATA
		fputs($smtp, "DATA\r\n");
		$response = fgets($smtp, 515);
		if(substr($response, 0, 3) != '354'){
			fclose($smtp);
			error_log("SMTP DATA failed: " . trim($response));
			return false;
		}
		
		// Send email headers and body
		$email_data = "From: " . $from_email . "\r\n";
		$email_data .= "To: " . $to_email . "\r\n";
		$email_data .= "Subject: " . $subject . "\r\n";
		$email_data .= "MIME-Version: 1.0\r\n";
		$email_data .= "Content-Type: text/html; charset=UTF-8\r\n";
		$email_data .= "\r\n";
		$email_data .= $message . "\r\n";
		$email_data .= ".\r\n";
		
		fputs($smtp, $email_data);
		$response = fgets($smtp, 515);
		
		// Quit
		fputs($smtp, "QUIT\r\n");
		fclose($smtp);
		
		$success = (substr($response, 0, 3) == '250');
		if(!$success){
			error_log("SMTP Send failed: " . trim($response));
		}
		return $success;
	}

	// Enhanced create_notification with email support
	function create_notification($user_ids, $type, $title, $message, $task_id = null){
		if(!is_array($user_ids))
			$user_ids = array($user_ids);
		
		$type_escaped = $this->db->real_escape_string($type);
		$title_escaped = $this->db->real_escape_string($title);
		$message_escaped = $this->db->real_escape_string($message);
		
		foreach($user_ids as $user_id){
			$uid = intval($user_id);
			$tid = $task_id ? intval($task_id) : null;
			// Insert system notification (safe values)
			$this->db->query("INSERT INTO notifications (user_id, type, title, message, task_id) VALUES ($uid, '$type_escaped', '$title_escaped', '$message_escaped', ".($tid ? $tid : 'NULL').")");
			
			// Skip email sending if SMTP is not configured - only send system notifications
			// Email sending can be enabled later via email settings configuration
		}
	}

	// 2. FEATURE: Daily Task Summary Email
	function send_daily_summary_email(){
		// Email notifications disabled by default - configure SMTP in settings to enable
		return 0;
	}

	// Process scheduled reminders from deadline_reminders table
	function process_scheduled_reminders(){
		$now = date('Y-m-d H:i:s');
		$reminders = $this->db->query("SELECT dr.*, t.task, t.due_date, t.status, t.assigned_to, 
										d.name as dept_name, u.email, CONCAT(u.firstname, ' ', u.lastname) as user_name
										FROM deadline_reminders dr
										INNER JOIN task_list t ON t.id = dr.task_id
										INNER JOIN users u ON u.id = dr.user_id
										LEFT JOIN departments d ON d.id = t.department_id
										WHERE dr.reminder_status = 'pending'
										AND dr.reminder_datetime <= '$now'
										AND t.status != 3");
		
		$sent_count = 0;
		while($reminder = $reminders->fetch_assoc()){
			// Send notification if enabled
			if(isset($reminder['send_notification']) && $reminder['send_notification'] == 1){
				// Create system notification
				$this->create_notification(
					array($reminder['user_id']),
					'reminder',
					'Task Reminder: ' . $reminder['task'],
					'You have a scheduled reminder for task: ' . $reminder['task'],
					$reminder['task_id']
				);
			}
			
			// Send email if enabled
			if(isset($reminder['send_mail']) && $reminder['send_mail'] == 1){
				$status_text = $this->get_status_text($reminder['status']);
				$due_date = !empty($reminder['due_date']) ? date('F d, Y', strtotime($reminder['due_date'])) : 'Not set';
				$days_left = '';
				if(!empty($reminder['due_date'])){
					$days = (strtotime($reminder['due_date']) - time()) / (60 * 60 * 24);
					if($days < 0){
						$days_left = abs(floor($days)) . " days overdue";
					} else if($days == 0){
						$days_left = "Due today";
					} else {
						$days_left = floor($days) . " days remaining";
					}
				}
				
				$subject = "Scheduled Reminder: " . $reminder['task'];
				$message = "<h3>Task Reminder</h3>";
				$message .= "<p>Hello " . htmlspecialchars($reminder['user_name']) . ",</p>";
				$message .= "<p>This is your scheduled reminder for the following task:</p>";
				$message .= "<div style='background: #f9f9f9; padding: 15px; border-left: 4px solid #2196F3; margin: 15px 0;'>";
				$message .= "<p><strong>Task:</strong> " . htmlspecialchars($reminder['task']) . "</p>";
				$message .= "<p><strong>Department:</strong> " . htmlspecialchars($reminder['dept_name'] ? $reminder['dept_name'] : 'N/A') . "</p>";
				$message .= "<p><strong>Status:</strong> " . $status_text . "</p>";
				$message .= "<p><strong>Due Date:</strong> " . $due_date;
				if(!empty($days_left)){
					$message .= " <span style='color: " . (strpos($days_left, 'overdue') !== false ? '#f44336' : '#ff9800') . ";'><strong>(" . $days_left . ")</strong></span>";
				}
				$message .= "</p>";
				$message .= "</div>";
				$message .= "<p>Please review and update the task status as needed.</p>";
				
				$this->send_email_notification($reminder['user_id'], $reminder['email'], $subject, $message, 'deadline_reminder', $reminder['task_id']);
			}
			
			// Mark reminder as sent
			$this->db->query("UPDATE deadline_reminders SET reminder_status = 'sent', sent_at = NOW() WHERE id = " . $reminder['id']);
			$sent_count++;
		}
		
		// Handle overdue notifications if enabled
		$overdue_reminders = $this->db->query("SELECT dr.*, t.task, t.due_date, t.status, 
												d.name as dept_name, u.email, CONCAT(u.firstname, ' ', u.lastname) as user_name
												FROM deadline_reminders dr
												INNER JOIN task_list t ON t.id = dr.task_id
												INNER JOIN users u ON u.id = dr.user_id
												LEFT JOIN departments d ON d.id = t.department_id
												WHERE dr.reminder_status = 'sent'
												AND dr.send_overdue = 1
												AND DATE(t.due_date) < CURDATE()
												AND t.status != 3
												AND (DATE(dr.sent_at) != CURDATE() OR dr.sent_at IS NULL)");
		
		while($overdue = $overdue_reminders->fetch_assoc()){
			$days_overdue = floor((strtotime(date('Y-m-d')) - strtotime($overdue['due_date'])) / (60 * 60 * 24));
			
			if(isset($overdue['send_mail']) && $overdue['send_mail'] == 1){
				$subject = "URGENT: Overdue Task - " . $overdue['task'];
				$message = "<h3 style='color: #f44336;'>⚠️ Overdue Task Reminder</h3>";
				$message .= "<p>Hello " . htmlspecialchars($overdue['user_name']) . ",</p>";
				$message .= "<p><strong style='color: #f44336;'>This task is overdue and requires immediate attention:</strong></p>";
				$message .= "<div style='background: #ffebee; padding: 15px; border-left: 4px solid #f44336; margin: 15px 0;'>";
				$message .= "<p><strong>Task:</strong> " . htmlspecialchars($overdue['task']) . "</p>";
				$message .= "<p><strong>Department:</strong> " . htmlspecialchars($overdue['dept_name'] ? $overdue['dept_name'] : 'N/A') . "</p>";
				$message .= "<p><strong>Due Date:</strong> " . date('F d, Y', strtotime($overdue['due_date'])) . " <span style='color: #f44336;'><strong>(Overdue by $days_overdue day(s))</strong></span></p>";
				$message .= "</div>";
				$message .= "<p>Please complete this task as soon as possible.</p>";
				
				$this->send_email_notification($overdue['user_id'], $overdue['email'], $subject, $message, 'deadline_reminder', $overdue['task_id']);
			}
			
			// Update sent_at to prevent duplicate daily emails
			$this->db->query("UPDATE deadline_reminders SET sent_at = NOW() WHERE id = " . $overdue['id']);
			$sent_count++;
		}
		
		return $sent_count;
	}

	// 3. FEATURE: Deadline Reminders
	function check_deadline_reminders(){
		// First process scheduled reminders (always process these, regardless of settings)
		$scheduled_count = $this->process_scheduled_reminders();
		
		// Check if automatic deadline reminders are enabled
		$settings = $this->db->query("SELECT enable_deadline_reminders, deadline_reminder_days FROM system_settings LIMIT 1");
		if(!$settings || $settings->num_rows == 0){
			return $scheduled_count; // Return scheduled count even if settings not found
		}
		$email_settings = $settings->fetch_assoc();
		
		if(!isset($email_settings['enable_deadline_reminders']) || $email_settings['enable_deadline_reminders'] == 0){
			return $scheduled_count; // Return scheduled count even if automatic reminders disabled
		}
		
		$reminder_days = isset($email_settings['deadline_reminder_days']) ? intval($email_settings['deadline_reminder_days']) : 1;
		$reminder_date = date('Y-m-d', strtotime("+$reminder_days days"));
		$today = date('Y-m-d');
		
		$sent_count = 0;
		
		// 1. Send reminders for tasks due in X days
		$tasks = $this->db->query("SELECT t.*, d.name as dept_name 
									FROM task_list t 
									LEFT JOIN departments d ON d.id = t.department_id 
									WHERE DATE(t.due_date) = '$reminder_date' 
									AND t.status != 3 
									AND (t.reminder_sent = 0 OR t.reminder_sent IS NULL)");
		
		while($task = $tasks->fetch_assoc()){
			$assigned_users = explode(',', $task['assigned_to']);
			foreach($assigned_users as $user_id){
				$user_id = intval(trim($user_id));
				if($user_id > 0){
					$user = $this->db->query("SELECT email, CONCAT(firstname, ' ', lastname) as name FROM users WHERE id = $user_id")->fetch_assoc();
					if($user){
						$status_text = $this->get_status_text($task['status']);
						$subject = "Task Reminder: " . $task['task'] . " - Due in $reminder_days day(s)";
						$message = "<h3>Task Deadline Reminder</h3>";
						$message .= "<p>Hello " . htmlspecialchars($user['name']) . ",</p>";
						$message .= "<p>This is a reminder that you have a task with an upcoming deadline:</p>";
						$message .= "<div style='background: #f9f9f9; padding: 15px; border-left: 4px solid #2196F3; margin: 15px 0;'>";
						$message .= "<p><strong>Task:</strong> " . htmlspecialchars($task['task']) . "</p>";
						$message .= "<p><strong>Department:</strong> " . htmlspecialchars($task['dept_name'] ? $task['dept_name'] : 'N/A') . "</p>";
						$message .= "<p><strong>Status:</strong> " . $status_text . "</p>";
						$message .= "<p><strong>Due Date:</strong> " . date('F d, Y', strtotime($task['due_date'])) . " <span style='color: #ff9800;'><strong>(In $reminder_days day(s))</strong></span></p>";
						if(!empty($task['description'])){
							$message .= "<p><strong>Description:</strong> " . nl2br(htmlspecialchars($task['description'])) . "</p>";
						}
						$message .= "</div>";
						$message .= "<p>Please ensure this task is completed before the deadline.</p>";
						
						$this->send_email_notification($user_id, $user['email'], $subject, $message, 'deadline_reminder', $task['id']);
						$sent_count++;
					}
				}
			}
			
			// Mark reminder as sent
			$this->db->query("UPDATE task_list SET reminder_sent = 1, last_reminder_at = NOW() WHERE id = " . $task['id']);
		}
		
		// 2. Send daily reminders for overdue tasks (only once per day per task)
		$overdue_tasks = $this->db->query("SELECT t.*, d.name as dept_name 
											FROM task_list t 
											LEFT JOIN departments d ON d.id = t.department_id 
											WHERE DATE(t.due_date) < '$today' 
											AND t.status != 3 
											AND (DATE(t.last_reminder_at) != '$today' OR t.last_reminder_at IS NULL)");
		
		while($task = $overdue_tasks->fetch_assoc()){
			$assigned_users = explode(',', $task['assigned_to']);
			$days_overdue = floor((strtotime($today) - strtotime($task['due_date'])) / (60 * 60 * 24));
			
			foreach($assigned_users as $user_id){
				$user_id = intval(trim($user_id));
				if($user_id > 0){
					$user = $this->db->query("SELECT email, CONCAT(firstname, ' ', lastname) as name FROM users WHERE id = $user_id")->fetch_assoc();
					if($user){
						$status_text = $this->get_status_text($task['status']);
						$subject = "URGENT: Overdue Task - " . $task['task'];
						$message = "<h3 style='color: #f44336;'>⚠️ Overdue Task Reminder</h3>";
						$message .= "<p>Hello " . htmlspecialchars($user['name']) . ",</p>";
						$message .= "<p><strong style='color: #f44336;'>This task is overdue and requires immediate attention:</strong></p>";
						$message .= "<div style='background: #ffebee; padding: 15px; border-left: 4px solid #f44336; margin: 15px 0;'>";
						$message .= "<p><strong>Task:</strong> " . htmlspecialchars($task['task']) . "</p>";
						$message .= "<p><strong>Department:</strong> " . htmlspecialchars($task['dept_name'] ? $task['dept_name'] : 'N/A') . "</p>";
						$message .= "<p><strong>Status:</strong> " . $status_text . "</p>";
						$message .= "<p><strong>Due Date:</strong> " . date('F d, Y', strtotime($task['due_date'])) . " <span style='color: #f44336;'><strong>(Overdue by $days_overdue day(s))</strong></span></p>";
						if(!empty($task['description'])){
							$message .= "<p><strong>Description:</strong> " . nl2br(htmlspecialchars($task['description'])) . "</p>";
						}
						$message .= "</div>";
						$message .= "<p>Please complete this task as soon as possible.</p>";
						
						$this->send_email_notification($user_id, $user['email'], $subject, $message, 'deadline_reminder', $task['id']);
						$sent_count++;
					}
				}
			}
			
			// Update last reminder time (daily reminder sent)
			$this->db->query("UPDATE task_list SET last_reminder_at = NOW() WHERE id = " . $task['id']);
		}
		
		return $scheduled_count + $sent_count;
	}
	
	// Send reminder for a specific task
	function send_task_reminder($task_id){
		$task = $this->db->query("SELECT t.*, d.name as dept_name 
									FROM task_list t 
									LEFT JOIN departments d ON d.id = t.department_id 
									WHERE t.id = " . intval($task_id))->fetch_assoc();
		
		if(!$task){
			return 0;
		}
		
		$assigned_users = explode(',', $task['assigned_to']);
		$sent_count = 0;
		
		foreach($assigned_users as $user_id){
			$user_id = intval(trim($user_id));
			if($user_id > 0){
				$user = $this->db->query("SELECT email, CONCAT(firstname, ' ', lastname) as name FROM users WHERE id = $user_id")->fetch_assoc();
				if($user){
					$status_text = $this->get_status_text($task['status']);
					$due_date = !empty($task['due_date']) ? date('F d, Y', strtotime($task['due_date'])) : 'Not set';
					$days_left = '';
					if(!empty($task['due_date'])){
						$days = (strtotime($task['due_date']) - time()) / (60 * 60 * 24);
						if($days < 0){
							$days_left = abs(floor($days)) . " days overdue";
						} else if($days == 0){
							$days_left = "Due today";
						} else {
							$days_left = floor($days) . " days remaining";
						}
					}
					
					$subject = "Task Reminder: " . $task['task'];
					$message = "<h3>Task Reminder</h3>";
					$message .= "<p>Hello " . htmlspecialchars($user['name']) . ",</p>";
					$message .= "<p>This is a reminder about your assigned task:</p>";
					$message .= "<div style='background: #f9f9f9; padding: 15px; border-left: 4px solid #2196F3; margin: 15px 0;'>";
					$message .= "<p><strong>Task:</strong> " . htmlspecialchars($task['task']) . "</p>";
					$message .= "<p><strong>Department:</strong> " . htmlspecialchars($task['dept_name'] ? $task['dept_name'] : 'N/A') . "</p>";
					$message .= "<p><strong>Status:</strong> " . $status_text . "</p>";
					$message .= "<p><strong>Due Date:</strong> " . $due_date;
					if(!empty($days_left)){
						$message .= " <span style='color: " . (strpos($days_left, 'overdue') !== false ? '#f44336' : '#ff9800') . ";'><strong>(" . $days_left . ")</strong></span>";
					}
					$message .= "</p>";
					if(!empty($task['description'])){
						$message .= "<p><strong>Description:</strong> " . nl2br(htmlspecialchars($task['description'])) . "</p>";
					}
					$message .= "</div>";
					$message .= "<p>Please review and update the task status as needed.</p>";
					
					$this->send_email_notification($user_id, $user['email'], $subject, $message, 'deadline_reminder', $task['id']);
					$sent_count++;
				}
			}
		}
		
		// Update last reminder time
		$this->db->query("UPDATE task_list SET last_reminder_at = NOW() WHERE id = " . intval($task_id));
		
		return $sent_count;
	}

	// Helper function to get status text
	function get_status_text($status){
		$status_text = array(1 => "Not Started", 2 => "In Progress", 3 => "Completed", 4 => "On Hold");
		return isset($status_text[$status]) ? $status_text[$status] : "Unknown";
	}

	// 4. FEATURE: Enhanced Dashboard Statistics with Department Breakdown
	function get_department_statistics(){
		$departments = $this->db->query("SELECT * FROM departments ORDER BY name");
		$stats = array();
		
		while($dept = $departments->fetch_assoc()){
			$dept_id = $dept['id'];
			$total = $this->db->query("SELECT COUNT(*) as cnt FROM task_list WHERE department_id = $dept_id")->fetch_assoc()['cnt'];
			$pending = $this->db->query("SELECT COUNT(*) as cnt FROM task_list WHERE department_id = $dept_id AND status = 1")->fetch_assoc()['cnt'];
			$in_progress = $this->db->query("SELECT COUNT(*) as cnt FROM task_list WHERE department_id = $dept_id AND status = 2")->fetch_assoc()['cnt'];
			$completed = $this->db->query("SELECT COUNT(*) as cnt FROM task_list WHERE department_id = $dept_id AND status = 3")->fetch_assoc()['cnt'];
			$overdue = $this->db->query("SELECT COUNT(*) as cnt FROM task_list WHERE department_id = $dept_id AND deadline < CURDATE() AND status != 3")->fetch_assoc()['cnt'];
			
			$completion_percent = $total > 0 ? ($completed / $total) * 100 : 0;
			
			$stats[] = array(
				'id' => $dept['id'],
				'name' => $dept['name'],
				'total' => $total,
				'pending' => $pending,
				'in_progress' => $in_progress,
				'completed' => $completed,
				'overdue' => $overdue,
				'completion_percentage' => number_format($completion_percent, 2)
			);
			
			// Update cache
			$this->db->query("INSERT INTO department_statistics (department_id, total_tasks, pending_tasks, in_progress_tasks, completed_tasks, overdue_tasks, completion_percentage) 
							   VALUES ($dept_id, $total, $pending, $in_progress, $completed, $overdue, $completion_percent) 
							   ON DUPLICATE KEY UPDATE total_tasks = $total, pending_tasks = $pending, in_progress_tasks = $in_progress, completed_tasks = $completed, overdue_tasks = $overdue, completion_percentage = $completion_percent");
		}
		
		return $stats;
	}

	// 5. FEATURE: Activity Logging for Real-time Updates
	function log_activity($action, $entity_type, $entity_id, $old_value = null, $new_value = null){
		$ip_address = isset($_SERVER['REMOTE_ADDR']) ? $this->db->real_escape_string($_SERVER['REMOTE_ADDR']) : 'UNKNOWN';
		$action_escaped = $this->db->real_escape_string($action);
		$entity_escaped = $this->db->real_escape_string($entity_type);
		
		$this->db->query("INSERT INTO activity_logs (user_id, action, entity_type, entity_id, old_value, new_value, ip_address) 
						   VALUES ({$_SESSION['login_id']}, '$action_escaped', '$entity_escaped', $entity_id, ".($old_value ? "'".htmlspecialchars($old_value)."'" : "NULL").", ".($new_value ? "'".htmlspecialchars($new_value)."'" : "NULL").", '$ip_address')");
	}

	// 6. FEATURE: System Audit Logging for Security & Compliance
	function log_audit($module, $action, $description = null, $status = 'success'){
		$ip_address = isset($_SERVER['REMOTE_ADDR']) ? $this->db->real_escape_string($_SERVER['REMOTE_ADDR']) : 'UNKNOWN';
		$user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? $this->db->real_escape_string($_SERVER['HTTP_USER_AGENT']) : 'UNKNOWN';
		$action_escaped = $this->db->real_escape_string($action);
		$module_escaped = $this->db->real_escape_string($module);
		$desc_escaped = $this->db->real_escape_string($description);
		
		$this->db->query("INSERT INTO system_audit_log (user_id, action, module, description, ip_address, user_agent, status) 
						   VALUES ({$_SESSION['login_id']}, '$action_escaped', '$module_escaped', '$desc_escaped', '$ip_address', '$user_agent', '$status')");
	}

	// Get recent activities for real-time dashboard
	function get_recent_activities($limit = 20){
		$activities = $this->db->query("SELECT al.*, CONCAT(u.firstname, ' ', u.lastname) as user_name FROM activity_logs al 
										LEFT JOIN users u ON al.user_id = u.id 
										ORDER BY al.date_created DESC LIMIT $limit");
		return $activities;
	}

	// Get unread activity count for notifications
	function get_unread_activity_count($user_id){
		$count = $this->db->query("SELECT COUNT(*) as cnt FROM activity_logs WHERE date_created > DATE_SUB(NOW(), INTERVAL 1 DAY)")->fetch_assoc()['cnt'];
		return $count;
	}

}

