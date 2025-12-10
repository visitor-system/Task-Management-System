<?php 
include 'db_connect.php';

// Check if user is logged in
if(!isset($_SESSION['login_type']) || !isset($_SESSION['login_id'])){
	die("Access denied. Please login first.");
}

if($_SESSION['login_type'] != 1){
	echo "<div class='alert alert-danger'>Access Denied. Only Administrators can access this page.</div>";
	exit;
}
?>

<div class="col-lg-12">
	<div class="card card-outline card-info">
		<div class="card-header">
			<h5 class="card-title">System Activity & Audit Log</h5>
			<div class="card-tools">
				<button class="btn btn-sm btn-default" onclick="export_activity_log()">
					<i class="fa fa-download"></i> Export
				</button>
			</div>
		</div>
		<div class="card-body">
			<div class="row mb-3">
				<div class="col-md-3">
					<input type="date" id="filter_date_from" class="form-control form-control-sm" placeholder="From Date">
				</div>
				<div class="col-md-3">
					<input type="date" id="filter_date_to" class="form-control form-control-sm" placeholder="To Date">
				</div>
				<div class="col-md-3">
					<select id="filter_module" class="custom-select custom-select-sm">
						<option value="">All Modules</option>
						<option value="task">Task Management</option>
						<option value="user">User Management</option>
						<option value="project">Project Management</option>
						<option value="department">Department Management</option>
						<option value="notification">Notifications</option>
					</select>
				</div>
				<div class="col-md-3">
					<button class="btn btn-sm btn-primary w-100" onclick="load_activity_logs()">
						<i class="fa fa-filter"></i> Filter
					</button>
				</div>
			</div>

			<div class="table-responsive">
				<table class="table table-hover table-sm" id="activity-table">
					<thead class="bg-light">
						<tr>
							<th>#</th>
							<th>Date & Time</th>
							<th>User</th>
							<th>Module</th>
							<th>Action</th>
							<th>Entity Type</th>
							<th>Description</th>
							<th>IP Address</th>
							<th>Status</th>
						</tr>
					</thead>
					<tbody id="activity-logs-body">
						<tr>
							<td colspan="9" class="text-center text-muted">Loading...</td>
						</tr>
					</tbody>
				</table>
			</div>
		</div>
	</div>
</div>

<script>
$(document).ready(function(){
	load_activity_logs();
});

function load_activity_logs(){
	let date_from = $('#filter_date_from').val();
	let date_to = $('#filter_date_to').val();
	let module = $('#filter_module').val();
	
	// Show loading state
	$('#activity-logs-body').html('<tr><td colspan="9" class="text-center text-muted">Loading...</td></tr>');
	
	$.ajax({
		url: 'ajax.php?action=get_audit_logs&from=' + date_from + '&to=' + date_to + '&module=' + module,
		type: 'GET',
		dataType: 'json',
		success: function(data){
			let html = '';
			
			// Check if response contains an error
			if(data && data.error){
				html = '<tr><td colspan="9" class="text-center text-danger">' + data.error + '</td></tr>';
			} else if(data && Array.isArray(data) && data.length > 0){
				let i = 1;
				data.forEach(function(log){
					let status_badge = '<span class="badge badge-success">Success</span>';
					if(log.status === 'failed') status_badge = '<span class="badge badge-danger">Failed</span>';
					else if(log.status === 'pending') status_badge = '<span class="badge badge-warning">Pending</span>';
					
					html += '<tr>';
					html += '<td>' + i + '</td>';
					html += '<td><small>' + (log.date_created ? new Date(log.date_created).toLocaleString() : '-') + '</small></td>';
					html += '<td>' + (log.user_name || 'System') + '</td>';
					html += '<td><span class="badge badge-info">' + (log.module || '-') + '</span></td>';
					html += '<td>' + (log.action || '-') + '</td>';
					html += '<td>' + (log.entity_type || '-') + '</td>';
					html += '<td><small>' + (log.description ? log.description.substring(0, 50) + '...' : '-') + '</small></td>';
					html += '<td><small>' + (log.ip_address || '-') + '</small></td>';
					html += '<td>' + status_badge + '</td>';
					html += '</tr>';
					i++;
				});
			} else {
				html = '<tr><td colspan="9" class="text-center text-muted">No logs found</td></tr>';
			}
			$('#activity-logs-body').html(html);
		},
		error: function(xhr, status, error){
			console.error('Error loading activity logs:', error);
			$('#activity-logs-body').html('<tr><td colspan="9" class="text-center text-danger">Error loading logs. Please try again.</td></tr>');
		}
	});
}

function export_activity_log(){
	alert_toast("Export feature will be implemented soon!", 'info');
}
</script>
