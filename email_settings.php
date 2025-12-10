<?php 
include 'db_connect.php';
if($_SESSION['login_type'] != 1){
	echo "<div class='alert alert-danger'>Access Denied. Only Administrators can access this page.</div>";
	exit;
}

$settings = $conn->query("SELECT * FROM system_settings LIMIT 1")->fetch_assoc();
?>

<div class="col-lg-8 offset-lg-2">
	<div class="card card-outline card-primary">
		<div class="card-header">
			<h5 class="card-title">Email & Notification Settings</h5>
		</div>
		<div class="card-body">
			<form id="email-settings-form">
				<div class="row">
					<div class="col-md-6">
						<div class="form-group">
							<label for="email_from">Email From Address</label>
							<input type="email" class="form-control form-control-sm" id="email_from" name="email_from" value="<?php echo isset($settings['email_from']) ? $settings['email_from'] : 'noreply@taskmanagement.com'; ?>" required>
							<small class="form-text text-muted">The email address used to send notifications</small>
						</div>
					</div>
					<div class="col-md-6">
						<div class="form-group">
							<label for="email_host">SMTP Host</label>
							<input type="text" class="form-control form-control-sm" id="email_host" name="email_host" value="<?php echo isset($settings['email_host']) ? $settings['email_host'] : 'localhost'; ?>" required>
							<small class="form-text text-muted">SMTP server hostname</small>
						</div>
					</div>
				</div>

				<div class="row">
					<div class="col-md-6">
						<div class="form-group">
							<label for="email_port">SMTP Port</label>
							<input type="number" class="form-control form-control-sm" id="email_port" name="email_port" value="<?php echo isset($settings['email_port']) ? $settings['email_port'] : 25; ?>" required>
							<small class="form-text text-muted">Usually 25, 465, or 587</small>
						</div>
					</div>
					<div class="col-md-6">
						<div class="form-group">
							<label for="email_username">SMTP Username (Optional)</label>
							<input type="text" class="form-control form-control-sm" id="email_username" name="email_username" value="<?php echo isset($settings['email_username']) ? $settings['email_username'] : ''; ?>">
							<small class="form-text text-muted">Leave blank if no authentication needed</small>
						</div>
					</div>
				</div>

				<div class="row">
					<div class="col-md-6">
						<div class="form-group">
							<label for="email_password">SMTP Password (Optional)</label>
							<input type="password" class="form-control form-control-sm" id="email_password" name="email_password" value="<?php echo isset($settings['email_password']) ? $settings['email_password'] : ''; ?>">
							<small class="form-text text-muted">Leave blank if no authentication needed</small>
						</div>
					</div>
					<div class="col-md-6">
						<div class="form-group">
							<label for="email_use_smtp">SMTP Configuration</label>
							<select id="email_use_smtp" name="email_use_smtp" class="custom-select custom-select-sm">
								<option value="0" <?php echo (!isset($settings['email_use_smtp']) || $settings['email_use_smtp'] == 0) ? 'selected' : ''; ?>>Use PHP mail() function</option>
								<option value="1" <?php echo (isset($settings['email_use_smtp']) && $settings['email_use_smtp'] == 1) ? 'selected' : ''; ?>>Use SMTP Server</option>
							</select>
						</div>
					</div>
				</div>

				<hr>
				<h5>Notification Features</h5>

				<div class="row">
					<div class="col-md-6">
						<div class="form-group">
							<div class="custom-control custom-switch">
								<input type="checkbox" class="custom-control-input" id="enable_email_notifications" name="enable_email_notifications" value="1" <?php echo (isset($settings['enable_email_notifications']) && $settings['enable_email_notifications'] == 1) ? 'checked' : ''; ?>>
								<label class="custom-control-label" for="enable_email_notifications">
									Enable Email Notifications
								</label>
							</div>
							<small class="form-text text-muted">Send email notifications for task assignments and updates</small>
						</div>
					</div>
					<div class="col-md-6">
						<div class="form-group">
							<div class="custom-control custom-switch">
								<input type="checkbox" class="custom-control-input" id="enable_deadline_reminders" name="enable_deadline_reminders" value="1" <?php echo (isset($settings['enable_deadline_reminders']) && $settings['enable_deadline_reminders'] == 1) ? 'checked' : ''; ?>>
								<label class="custom-control-label" for="enable_deadline_reminders">
									Enable Deadline Reminders
								</label>
							</div>
							<small class="form-text text-muted">Auto-send deadline reminder emails</small>
						</div>
					</div>
				</div>

				<div class="form-group">
					<label for="deadline_reminder_days">Deadline Reminder (Days Before)</label>
					<input type="number" class="form-control form-control-sm" id="deadline_reminder_days" name="deadline_reminder_days" min="1" max="30" value="<?php echo isset($settings['deadline_reminder_days']) ? $settings['deadline_reminder_days'] : 1; ?>" required>
					<small class="form-text text-muted">Send reminder email X days before task deadline</small>
				</div>

				<div class="form-group">
					<button type="submit" class="btn btn-primary btn-sm">
						<i class="fa fa-save"></i> Save Settings
					</button>
					<button type="button" class="btn btn-secondary btn-sm" onclick="test_email()">
						<i class="fa fa-envelope"></i> Send Test Email
					</button>
				</div>
			</form>
		</div>
	</div>
</div>

<script>
$('#email-settings-form').on('submit', function(e){
	e.preventDefault();
	let form_data = new FormData(this);
	
	$.ajax({
		url: 'ajax.php?action=save_email_settings',
		type: 'POST',
		data: form_data,
		contentType: false,
		processData: false,
		success: function(resp){
			if(resp == 1){
				alert_toast("Email settings saved successfully!", 'success');
			} else {
				alert_toast("Error saving settings", 'error');
			}
		}
	});
});

function test_email(){
	$.ajax({
		url: 'ajax.php?action=send_test_email',
		type: 'POST',
		success: function(resp){
			if(resp == 1){
				alert_toast("Test email sent successfully!", 'success');
			} else {
				alert_toast("Error sending test email", 'error');
			}
		}
	});
}
</script>
