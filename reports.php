<?php 
include 'db_connect.php';

// Check if user is logged in
if(!isset($_SESSION['login_id'])){
	die("Access denied. Please login first.");
}
?>
 <div class="col-md-12">
        <div class="card card-outline card-success">
          <div class="card-header">
            <b>Reports & Analytics</b>
            <div class="card-tools">
            	<button class="btn btn-flat btn-sm bg-gradient-success btn-success" id="print"><i class="fa fa-print"></i> Print</button>
            	<button class="btn btn-flat btn-sm bg-gradient-primary btn-primary" id="export_excel"><i class="fa fa-file-excel"></i> Export Excel</button>
            	<button class="btn btn-flat btn-sm bg-gradient-danger btn-danger" id="export_pdf"><i class="fa fa-file-pdf"></i> Export PDF</button>
            </div>
          </div>
          <div class="card-body">
          	<ul class="nav nav-tabs" id="reportTabs" role="tablist">
          		<li class="nav-item">
          			<a class="nav-link active" id="department-tab" data-toggle="tab" href="#department" role="tab">Department-wise</a>
          		</li>
          		<li class="nav-item">
          			<a class="nav-link" id="employee-tab" data-toggle="tab" href="#employee" role="tab">Employee Performance</a>
          		</li>
          		<li class="nav-item">
          			<a class="nav-link" id="task-aging-tab" data-toggle="tab" href="#task-aging" role="tab">Task Aging</a>
          		</li>
          		<li class="nav-item">
          			<a class="nav-link" id="pending-tab" data-toggle="tab" href="#pending" role="tab">Pending Tasks</a>
          		</li>
          		<li class="nav-item">
          			<a class="nav-link" id="completed-tab" data-toggle="tab" href="#completed" role="tab">Completed Tasks</a>
          		</li>
          	</ul>
          	<div class="tab-content" id="reportTabsContent">
          		<div class="tab-pane fade show active" id="department" role="tabpanel">
          			<div class="table-responsive">
          				<table class="table table-bordered">
          					<thead>
          						<tr>
          							<th>Department</th>
          							<th>Total Tasks</th>
          							<th>Pending</th>
          							<th>In Progress</th>
          							<th>Completed</th>
          							<th>On Hold</th>
          							<th>Overdue</th>
          							<th>Completion Rate</th>
          						</tr>
          					</thead>
          					<tbody>
          						<?php
          						$depts = $conn->query("SELECT * FROM departments ORDER BY name");
          						$dept_count = 0;
          						if($depts && $depts->num_rows > 0):
          							while($dept = $depts->fetch_assoc()):
          								$dept_count++;
          								$dept_id = intval($dept['id']);
          								$total_qry = $conn->query("SELECT COUNT(*) as cnt FROM task_list WHERE department_id = $dept_id");
          								$total = ($total_qry && $total_qry->num_rows > 0) ? $total_qry->fetch_assoc()['cnt'] : 0;
          								$pending_qry = $conn->query("SELECT COUNT(*) as cnt FROM task_list WHERE department_id = $dept_id AND (status = 1 OR status IS NULL)");
          								$pending = ($pending_qry && $pending_qry->num_rows > 0) ? $pending_qry->fetch_assoc()['cnt'] : 0;
          								$in_progress_qry = $conn->query("SELECT COUNT(*) as cnt FROM task_list WHERE department_id = $dept_id AND status = 2");
          								$in_progress = ($in_progress_qry && $in_progress_qry->num_rows > 0) ? $in_progress_qry->fetch_assoc()['cnt'] : 0;
          								$completed_qry = $conn->query("SELECT COUNT(*) as cnt FROM task_list WHERE department_id = $dept_id AND status = 3");
          								$completed = ($completed_qry && $completed_qry->num_rows > 0) ? $completed_qry->fetch_assoc()['cnt'] : 0;
          								$on_hold_qry = $conn->query("SELECT COUNT(*) as cnt FROM task_list WHERE department_id = $dept_id AND status = 4");
          								$on_hold = ($on_hold_qry && $on_hold_qry->num_rows > 0) ? $on_hold_qry->fetch_assoc()['cnt'] : 0;
          								$overdue_qry = $conn->query("SELECT COUNT(*) as cnt FROM task_list WHERE department_id = $dept_id AND deadline IS NOT NULL AND deadline < CURDATE() AND (status != 3 OR status IS NULL)");
          								$overdue = ($overdue_qry && $overdue_qry->num_rows > 0) ? $overdue_qry->fetch_assoc()['cnt'] : 0;
          								$completion_rate = $total > 0 ? ($completed / $total) * 100 : 0;
          							?>
          							<tr>
          								<td><b><?php echo $dept['name'] ?></b></td>
          								<td><?php echo $total ?></td>
          								<td><?php echo $pending ?></td>
          								<td><?php echo $in_progress ?></td>
          								<td><?php echo $completed ?></td>
          								<td><?php echo $on_hold ?></td>
          								<td><span class="badge badge-danger"><?php echo $overdue ?></span></td>
          								<td>
          									<div class="progress">
          										<div class="progress-bar" style="width: <?php echo $completion_rate ?>%"><?php echo number_format($completion_rate, 1) ?>%</div>
          									</div>
          								</td>
          							</tr>
          							<?php endwhile; 
          						else: ?>
          							<tr>
          								<td colspan="8" class="text-center text-muted">
          									<p class="py-3">No departments found. Please create departments first.</p>
          								</td>
          							</tr>
          						<?php endif; ?>
          					</tbody>
          				</table>
          			</div>
          		</div>
          		<div class="tab-pane fade" id="employee" role="tabpanel">
          			<div class="table-responsive">
          				<table class="table table-bordered">
          					<thead>
          						<tr>
          							<th>Employee</th>
          							<th>Department</th>
          							<th>Total Assigned</th>
          							<th>Completed</th>
          							<th>In Progress</th>
          							<th>Pending</th>
          							<th>On Hold</th>
          							<th>Performance</th>
          						</tr>
          					</thead>
          					<tbody>
          						<?php
          						$employees = $conn->query("SELECT u.*, d.name as dept_name FROM users u LEFT JOIN departments d ON d.id = u.department_id WHERE u.type != 1 ORDER BY u.firstname, u.lastname");
          						$emp_count = 0;
          						if($employees && $employees->num_rows > 0):
          							while($emp = $employees->fetch_assoc()):
          								$emp_count++;
          								$emp_id = intval($emp['id']);
          								$total_qry = $conn->query("SELECT COUNT(*) as cnt FROM task_list WHERE assigned_to LIKE '%$emp_id%'");
          								$total = ($total_qry && $total_qry->num_rows > 0) ? $total_qry->fetch_assoc()['cnt'] : 0;
          								$completed_qry = $conn->query("SELECT COUNT(*) as cnt FROM task_list WHERE assigned_to LIKE '%$emp_id%' AND status = 3");
          								$completed = ($completed_qry && $completed_qry->num_rows > 0) ? $completed_qry->fetch_assoc()['cnt'] : 0;
          								$in_progress_qry = $conn->query("SELECT COUNT(*) as cnt FROM task_list WHERE assigned_to LIKE '%$emp_id%' AND status = 2");
          								$in_progress = ($in_progress_qry && $in_progress_qry->num_rows > 0) ? $in_progress_qry->fetch_assoc()['cnt'] : 0;
          								$pending_qry = $conn->query("SELECT COUNT(*) as cnt FROM task_list WHERE assigned_to LIKE '%$emp_id%' AND (status = 1 OR status IS NULL)");
          								$pending = ($pending_qry && $pending_qry->num_rows > 0) ? $pending_qry->fetch_assoc()['cnt'] : 0;
          								$on_hold_qry = $conn->query("SELECT COUNT(*) as cnt FROM task_list WHERE assigned_to LIKE '%$emp_id%' AND status = 4");
          								$on_hold = ($on_hold_qry && $on_hold_qry->num_rows > 0) ? $on_hold_qry->fetch_assoc()['cnt'] : 0;
          								$performance = $total > 0 ? ($completed / $total) * 100 : 0;
          							?>
          							<tr>
          								<td><b><?php echo ucwords($emp['firstname'].' '.$emp['lastname']) ?></b></td>
          								<td><?php echo $emp['dept_name'] ? $emp['dept_name'] : 'N/A' ?></td>
          								<td><?php echo $total ?></td>
          								<td><span class="badge badge-success"><?php echo $completed ?></span></td>
          								<td><span class="badge badge-primary"><?php echo $in_progress ?></span></td>
          								<td><span class="badge badge-warning"><?php echo $pending ?></span></td>
          								<td><span class="badge badge-secondary"><?php echo $on_hold ?></span></td>
          								<td>
          									<div class="progress">
          										<div class="progress-bar bg-success" style="width: <?php echo $performance ?>%"><?php echo number_format($performance, 1) ?>%</div>
          									</div>
          								</td>
          							</tr>
          							<?php endwhile; 
          						else: ?>
          							<tr>
          								<td colspan="8" class="text-center text-muted">
          									<p class="py-3">No employees found.</p>
          								</td>
          							</tr>
          						<?php endif; ?>
          					</tbody>
          				</table>
          			</div>
          		</div>
          			<div class="tab-pane fade" id="task-aging" role="tabpanel">
          				<div class="table-responsive">
          					<table class="table table-bordered">
          						<thead>
          							<tr>
          								<th>Task</th>
          								<th>Department</th>
          								<th>Assigned To</th>
          								<th>Deadline</th>
          								<th>Days Overdue</th>
          								<th>Status</th>
          								<th>Priority</th>
          							</tr>
          						</thead>
          						<tbody>
          							<?php
          							$aging_tasks = $conn->query("SELECT t.*, d.name as dept_name FROM task_list t LEFT JOIN departments d ON d.id = t.department_id WHERE t.deadline IS NOT NULL AND t.deadline < CURDATE() AND (t.status != 3 OR t.status IS NULL) ORDER BY t.deadline ASC");
          						if($aging_tasks && $aging_tasks->num_rows > 0):
          							while($task = $aging_tasks->fetch_assoc()):
          								$days_overdue = (strtotime(date('Y-m-d')) - strtotime($task['deadline'])) / (60 * 60 * 24);
          								$assigned_names = array();
          								if(!empty($task['assigned_to'])){
          									$assigned_users = explode(',', $task['assigned_to']);
          									foreach($assigned_users as $uid){
          										if(!empty($uid)){
          											$u = $conn->query("SELECT CONCAT(firstname,' ',lastname) as name FROM users WHERE id = ".intval($uid));
          											if($u && $u->num_rows > 0){
          												$user_data = $u->fetch_assoc();
          												if($user_data) $assigned_names[] = $user_data['name'];
          											}
          										}
          									}
          								}
          								$status_text = array(1 => "Not Started", 2 => "In Progress", 3 => "Completed", 4 => "On Hold");
          								$status_class = array(1 => "secondary", 2 => "primary", 3 => "success", 4 => "warning");
          								$task_status = isset($task['status']) ? $task['status'] : 1;
          								$priority_colors = array('High' => 'danger', 'Medium' => 'warning', 'Low' => 'info');
          								$task_priority = isset($task['priority']) && !empty($task['priority']) ? $task['priority'] : 'Medium';
          							?>
          								<tr class="table-danger">
          								<td><b><?php echo $task['task'] ?></b></td>
          								<td><?php echo $task['dept_name'] ? $task['dept_name'] : 'N/A' ?></td>
          								<td><?php echo !empty($assigned_names) ? implode(', ', $assigned_names) : 'Not Assigned' ?></td>
          								<td><?php echo date("M d, Y", strtotime($task['deadline'])) ?></td>
          								<td><span class="badge badge-danger"><?php echo number_format($days_overdue) ?> days</span></td>
          								<td>
          									<span class='badge badge-<?php echo isset($status_class[$task_status]) ? $status_class[$task_status] : "secondary" ?>'>
          										<?php echo isset($status_text[$task_status]) ? $status_text[$task_status] : "Not Started" ?>
          									</span>
          								</td>
          								<td>
          									<span class='badge badge-<?php echo isset($priority_colors[$task_priority]) ? $priority_colors[$task_priority] : "warning" ?>'>
          										<?php echo $task_priority ?>
          									</span>
          								</td>
          							</tr>
          								<?php endwhile; 
          								else: ?>
          								<tr>
          									<td colspan="7" class="text-center text-muted">
          										<p class="py-3">No overdue tasks found. All tasks are up to date!</p>
          									</td>
          								</tr>
          							<?php endif; ?>
          					</tbody>
          				</table>
          			</div>
          		</div>
          			<div class="tab-pane fade" id="pending" role="tabpanel">
          				<div class="table-responsive">
          					<table class="table table-bordered">
          						<thead>
          							<tr>
          								<th>Task</th>
          								<th>Department</th>
          								<th>Assigned To</th>
          								<th>Deadline</th>
          								<th>Priority</th>
          							</tr>
          						</thead>
          						<tbody>
          							<?php
          							$pending_tasks = $conn->query("SELECT t.*, d.name as dept_name FROM task_list t LEFT JOIN departments d ON d.id = t.department_id WHERE (t.status = 1 OR t.status IS NULL) ORDER BY CASE WHEN t.priority = 'High' THEN 1 WHEN t.priority = 'Medium' THEN 2 WHEN t.priority = 'Low' THEN 3 ELSE 4 END, t.deadline ASC");
          						if($pending_tasks && $pending_tasks->num_rows > 0):
          							while($task = $pending_tasks->fetch_assoc()):
          								$assigned_names = array();
          								if(!empty($task['assigned_to'])){
          									$assigned_users = explode(',', $task['assigned_to']);
          									foreach($assigned_users as $uid){
          										if(!empty($uid)){
          											$u = $conn->query("SELECT CONCAT(firstname,' ',lastname) as name FROM users WHERE id = ".intval($uid));
          											if($u && $u->num_rows > 0){
          												$user_data = $u->fetch_assoc();
          												if($user_data) $assigned_names[] = $user_data['name'];
          											}
          										}
          									}
          								}
          								$priority_colors = array('High' => 'danger', 'Medium' => 'warning', 'Low' => 'info');
          								$task_priority = isset($task['priority']) && !empty($task['priority']) ? $task['priority'] : 'Medium';
          							?>
          									<tr>
          										<td><b><?php echo $task['task'] ?></b></td>
          										<td><?php echo $task['dept_name'] ? $task['dept_name'] : 'N/A' ?></td>
          										<td><?php echo !empty($assigned_names) ? implode(', ', $assigned_names) : 'Not Assigned' ?></td>
          										<td><?php echo $task['deadline'] ? date("M d, Y", strtotime($task['deadline'])) : 'N/A' ?></td>
          								<td>
          									<span class='badge badge-<?php echo isset($priority_colors[$task_priority]) ? $priority_colors[$task_priority] : "warning" ?>'>
          										<?php echo $task_priority ?>
          									</span>
          								</td>
          							</tr>
          							<?php endwhile; 
          						else: ?>
          							<tr>
          								<td colspan="5" class="text-center text-muted">
          									<p class="py-3">No pending tasks found.</p>
          								</td>
          							</tr>
          						<?php endif; ?>
          					</tbody>
          				</table>
          			</div>
          		</div>
          			<div class="tab-pane fade" id="completed" role="tabpanel">
          				<div class="table-responsive">
          					<table class="table table-bordered">
          						<thead>
          							<tr>
          								<th>Task</th>
          								<th>Department</th>
          								<th>Assigned To</th>
          								<th>Completed Date</th>
          								<th>Deadline</th>
          							</tr>
          						</thead>
          						<tbody>
          							<?php
          							$completed_tasks = $conn->query("SELECT t.*, d.name as dept_name FROM task_list t LEFT JOIN departments d ON d.id = t.department_id WHERE t.status = 3 ORDER BY t.date_created DESC");
          						if($completed_tasks && $completed_tasks->num_rows > 0):
          							while($task = $completed_tasks->fetch_assoc()):
          								$assigned_names = array();
          								if(!empty($task['assigned_to'])){
          									$assigned_users = explode(',', $task['assigned_to']);
          									foreach($assigned_users as $uid){
          										if(!empty($uid)){
          											$u = $conn->query("SELECT CONCAT(firstname,' ',lastname) as name FROM users WHERE id = ".intval($uid));
          											if($u && $u->num_rows > 0){
          												$user_data = $u->fetch_assoc();
          												if($user_data) $assigned_names[] = $user_data['name'];
          											}
          										}
          									}
          								}
          							?>
          							<tr>
          								<td><b><?php echo $task['task'] ?></b></td>
          								<td><?php echo $task['dept_name'] ? $task['dept_name'] : 'N/A' ?></td>
          								<td><?php echo !empty($assigned_names) ? implode(', ', $assigned_names) : 'Not Assigned' ?></td>
          								<td><?php echo date("M d, Y", strtotime($task['date_created'])) ?></td>
          								<td><?php echo $task['deadline'] ? date("M d, Y", strtotime($task['deadline'])) : 'N/A' ?></td>
          							</tr>
          							<?php endwhile; 
          						else: ?>
          							<tr>
          								<td colspan="5" class="text-center text-muted">
          									<p class="py-3">No completed tasks found.</p>
          								</td>
          							</tr>
          						<?php endif; ?>
          					</tbody>
          				</table>
          			</div>
          		</div>
          	</div>
          </div>
        </div>
        </div>
<script>
	$('#print').click(function(){
		start_load()
		var activeTab = $('.tab-pane.active');
		var _h = $('head').clone()
		var _p = activeTab.find('.table-responsive').clone()
		var _d = "<p class='text-center'><b>Report as of (<?php echo date("F d, Y") ?>)</b></p>"
		_p.prepend(_d)
		_p.prepend(_h)
		var nw = window.open("","","width=900,height=600")
		nw.document.write(_p.html())
		nw.document.close()
		nw.print()
		setTimeout(function(){
			nw.close()
			end_load()
		},750)
	})
	$('#export_excel').click(function(){
		var activeTab = $('.tab-pane.active');
		var table = activeTab.find('table').clone();
		var html = table[0].outerHTML;
		var blob = new Blob([html], {type: 'application/vnd.ms-excel'});
		var url = window.URL.createObjectURL(blob);
		var a = document.createElement('a');
		a.href = url;
		a.download = 'report_<?php echo date("Y-m-d") ?>.xls';
		a.click();
	})
	
	$('#export_pdf').click(function(){
		start_load();
		var activeTab = $('.tab-pane.active');
		var table = activeTab.find('table').clone();
		var title = activeTab.find('.nav-link.active').text().trim() || 'Report';
		var date = '<?php echo date("F d, Y") ?>';
		
		// Create print-friendly HTML
		var html = '<!DOCTYPE html><html><head><title>' + title + '</title>';
		html += '<style>body{font-family:Arial,sans-serif;margin:20px;}';
		html += 'table{width:100%;border-collapse:collapse;margin:20px 0;}';
		html += 'th,td{border:1px solid #ddd;padding:8px;text-align:left;}';
		html += 'th{background-color:#4CAF50;color:white;font-weight:bold;}';
		html += 'tr:nth-child(even){background-color:#f2f2f2;}';
		html += 'h2{color:#333;margin-bottom:10px;}</style></head><body>';
		html += '<h2>' + title + '</h2>';
		html += '<p><strong>Generated on:</strong> ' + date + '</p>';
		html += table[0].outerHTML;
		html += '</body></html>';
		
		// Open in new window and print (user can save as PDF)
		var printWindow = window.open('', '_blank');
		printWindow.document.write(html);
		printWindow.document.close();
		setTimeout(function(){
			printWindow.print();
			end_load();
		}, 500);
	})
</script>