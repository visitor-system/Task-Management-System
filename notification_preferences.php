<?php 
// Check if session is started, otherwise start it
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if(!isset($_SESSION['login_id'])){
    die("Access denied. Please login first.");
}

include 'db_connect.php';
$user_id = intval($_SESSION['login_id']);

// Query user preferences
$prefs_query = $conn->query("SELECT * FROM user_preferences WHERE user_id = $user_id");
if($prefs_query && $prefs_query->num_rows > 0){
    $prefs = $prefs_query->fetch_assoc();
} else {
    $prefs = null;
}

// If no preferences exist, create defaults
if(!$prefs){
    $insert_query = $conn->query("INSERT INTO user_preferences (user_id, email_on_task_assign, email_on_status_change, email_on_deadline_reminder, email_on_task_completion, daily_summary_email, summary_time, timezone) VALUES ($user_id, 1, 1, 1, 1, 0, '08:00:00', 'UTC')");
    if($insert_query){
        $prefs_query = $conn->query("SELECT * FROM user_preferences WHERE user_id = $user_id");
        if($prefs_query && $prefs_query->num_rows > 0){
            $prefs = $prefs_query->fetch_assoc();
        }
    }
    // Set default values if query still fails
    if(!$prefs){
        $prefs = [
            'email_on_task_assign' => 1,
            'email_on_status_change' => 1,
            'email_on_deadline_reminder' => 1,
            'email_on_task_completion' => 1,
            'daily_summary_email' => 0,
            'summary_time' => '08:00:00',
            'timezone' => 'UTC'
        ];
    }
}
?>

<div class="col-lg-8 offset-lg-2">
	<div class="card card-outline card-info">
		<div class="card-header">
			<h5 class="card-title">Notification Preferences</h5>
		</div>
		<div class="card-body">
			<form id="notification-preferences-form">
				<input type="hidden" name="user_id" value="<?php echo $user_id; ?>">
				
				<h6 class="text-primary mb-3">Email Notification Settings</h6>
				
				<div class="form-group">
					<div class="custom-control custom-checkbox">
						<input type="checkbox" class="custom-control-input" id="email_on_task_assign" name="email_on_task_assign" value="1" <?php echo (isset($prefs['email_on_task_assign']) && $prefs['email_on_task_assign'] == 1) ? 'checked' : ''; ?>>
						<label class="custom-control-label" for="email_on_task_assign">
							Send email when a task is assigned to me
						</label>
					</div>
				</div>

				<div class="form-group">
					<div class="custom-control custom-checkbox">
						<input type="checkbox" class="custom-control-input" id="email_on_status_change" name="email_on_status_change" value="1" <?php echo (isset($prefs['email_on_status_change']) && $prefs['email_on_status_change'] == 1) ? 'checked' : ''; ?>>
						<label class="custom-control-label" for="email_on_status_change">
							Send email when task status changes
						</label>
					</div>
				</div>

				<div class="form-group">
					<div class="custom-control custom-checkbox">
						<input type="checkbox" class="custom-control-input" id="email_on_deadline_reminder" name="email_on_deadline_reminder" value="1" <?php echo (isset($prefs['email_on_deadline_reminder']) && $prefs['email_on_deadline_reminder'] == 1) ? 'checked' : ''; ?>>
						<label class="custom-control-label" for="email_on_deadline_reminder">
							Send deadline reminder emails
						</label>
					</div>
				</div>

				<div class="form-group">
					<div class="custom-control custom-checkbox">
						<input type="checkbox" class="custom-control-input" id="email_on_task_completion" name="email_on_task_completion" value="1" <?php echo (isset($prefs['email_on_task_completion']) && $prefs['email_on_task_completion'] == 1) ? 'checked' : ''; ?>>
						<label class="custom-control-label" for="email_on_task_completion">
							Send email when tasks are completed
						</label>
					</div>
				</div>

				<hr>
				<h6 class="text-primary mb-3">Daily Summary Settings</h6>

				<div class="form-group">
					<div class="custom-control custom-checkbox">
						<input type="checkbox" class="custom-control-input" id="daily_summary_email" name="daily_summary_email" value="1" <?php echo (isset($prefs['daily_summary_email']) && $prefs['daily_summary_email'] == 1) ? 'checked' : ''; ?>>
						<label class="custom-control-label" for="daily_summary_email">
							Send daily task summary email
						</label>
					</div>
				</div>

				<div class="form-group">
					<label for="summary_time">Preferred Summary Email Time</label>
					<input type="time" class="form-control form-control-sm" id="summary_time" name="summary_time" value="<?php echo isset($prefs['summary_time']) ? htmlspecialchars($prefs['summary_time']) : '08:00:00'; ?>">
					<small class="form-text text-muted">You will receive the daily summary at this time each morning</small>
				</div>

				<div class="form-group">
					<label for="timezone">Your Timezone</label>
					<select id="timezone" name="timezone" class="custom-select custom-select-sm">
						<option value="UTC" <?php echo (isset($prefs['timezone']) && $prefs['timezone'] == 'UTC') ? 'selected' : ''; ?>>UTC</option>
						<option value="Asia/Kolkata" <?php echo (isset($prefs['timezone']) && $prefs['timezone'] == 'Asia/Kolkata') ? 'selected' : ''; ?>>Asia/Kolkata (IST)</option>
						<option value="America/New_York" <?php echo (isset($prefs['timezone']) && $prefs['timezone'] == 'America/New_York') ? 'selected' : ''; ?>>America/New_York (EST)</option>
						<option value="America/Chicago" <?php echo (isset($prefs['timezone']) && $prefs['timezone'] == 'America/Chicago') ? 'selected' : ''; ?>>America/Chicago (CST)</option>
						<option value="America/Los_Angeles" <?php echo (isset($prefs['timezone']) && $prefs['timezone'] == 'America/Los_Angeles') ? 'selected' : ''; ?>>America/Los_Angeles (PST)</option>
						<option value="Europe/London" <?php echo (isset($prefs['timezone']) && $prefs['timezone'] == 'Europe/London') ? 'selected' : ''; ?>>Europe/London (GMT)</option>
						<option value="Europe/Paris" <?php echo (isset($prefs['timezone']) && $prefs['timezone'] == 'Europe/Paris') ? 'selected' : ''; ?>>Europe/Paris (CET)</option>
						<option value="Australia/Sydney" <?php echo (isset($prefs['timezone']) && $prefs['timezone'] == 'Australia/Sydney') ? 'selected' : ''; ?>>Australia/Sydney (AEDT)</option>
						<option value="Asia/Tokyo" <?php echo (isset($prefs['timezone']) && $prefs['timezone'] == 'Asia/Tokyo') ? 'selected' : ''; ?>>Asia/Tokyo (JST)</option>
					</select>
				</div>

				<hr>
				<div class="form-group">
					<button type="submit" class="btn btn-primary btn-sm">
						<i class="fa fa-save"></i> Save Preferences
					</button>
					<button type="button" class="btn btn-secondary btn-sm" onclick="send_test_notification()">
						<i class="fa fa-envelope"></i> Send Test Email
					</button>
				</div>
			</form>
		</div>
	</div>
</div>

<script>
$('#notification-preferences-form').on('submit', function(e){
	e.preventDefault();
	let form_data = $(this).serialize();
	
	$.ajax({
		url: 'ajax.php?action=save_notification_preferences',
		type: 'POST',
		data: form_data,
		success: function(resp){
			if(resp == 1){
				alert_toast("Preferences saved successfully!", 'success');
			} else {
				alert_toast("Error saving preferences", 'error');
			}
		}
	});
});

function send_test_notification(){
	$.ajax({
		url: 'ajax.php?action=send_test_notification',
		type: 'POST',
		success: function(resp){
			if(resp == 1){
				alert_toast("Test notification email sent!", 'success');
			} else {
				alert_toast("Error sending test notification", 'error');
			}
		}
	});
}
</script>
