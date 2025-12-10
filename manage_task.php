<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($conn)) {
    include 'db_connect.php';
}

// Ensure user logged in
if(!isset($_SESSION['login_id']) || !isset($_SESSION['login_type'])){
    die("Access denied. Please login first.");
}

// Defaults
$today_date = date('Y-m-d');
$task_data = array();

// If editing a task, get task data
$task_id = 0;
if (isset($_GET['id'])) {
    $task_id = intval($_GET['id']);
    $qry = $conn->query("SELECT * FROM task_list WHERE task_id = {$task_id}");
    if ($qry && $qry->num_rows > 0) {
        $task_data = $qry->fetch_assoc();
        foreach ($task_data as $k => $v) {
            $$k = $v;
        }
        if (!isset($due_date) && isset($deadline)) {
            $due_date = $deadline;
        }
        $assigned_to = isset($assigned_to) ? explode(',', $assigned_to) : array();
        $assigned_to = array_map('intval', $assigned_to);
    } else {
        $task_id = 0;
    }
}

// Make sure $id variable exists
if (!isset($id) && $task_id > 0) {
    $id = $task_id;
}

// Fetch users
$all_users = $conn->query("SELECT *, CONCAT(firstname,' ',lastname) as name FROM users WHERE type != 1 ORDER BY firstname, lastname");
$all_users_data = array();
if ($all_users && $all_users->num_rows > 0) {
    while ($u = $all_users->fetch_assoc()) {
        $all_users_data[] = $u;
    }
}

// Fetch departments
$departments = $conn->query("SELECT * FROM departments ORDER BY name");

// Selected department
$selected_dept_name = 'Select Department';
if (isset($department_id) && !empty($department_id)) {
    $dept_qry = $conn->query("SELECT name FROM departments WHERE id = " . intval($department_id));
    if ($dept_qry && $dept_qry->num_rows > 0) {
        $dept_data = $dept_qry->fetch_assoc();
        $selected_dept_name = $dept_data['name'];
    }
}

// Assigned users
$assigned_users = isset($assigned_to) ? $assigned_to : array();
$default_start_date = (isset($start_date) && !empty($start_date)) ? $start_date : $today_date;
$default_due_date = (isset($due_date) && !empty($due_date)) ? $due_date : (isset($deadline) && !empty($deadline) ? $deadline : $default_start_date);

// Reminder variables
$modal_task_id = $task_id;
$existing_reminder = null;
$default_reminder_date = date('Y-m-d\TH:i', strtotime('+1 day 13:00'));
if ($modal_task_id > 0) {
    $reminder_qry = $conn->query("SELECT * FROM deadline_reminders WHERE task_id = {$modal_task_id} AND reminder_status != 'dismissed' ORDER BY reminder_datetime ASC LIMIT 1");
    if ($reminder_qry && $reminder_qry->num_rows > 0) {
        $existing_reminder = $reminder_qry->fetch_assoc();
    }
    $default_reminder_date = $existing_reminder ? date('Y-m-d\TH:i', strtotime($existing_reminder['reminder_datetime'])) : (isset($due_date) ? date('Y-m-d\TH:i', strtotime($due_date . ' 13:00')) : $default_reminder_date);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8" />
<title>Manage Task</title>
<meta name="viewport" content="width=device-width, initial-scale=1" />
<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" />
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.18/summernote-bs4.min.css" rel="stylesheet">

<style>
.task-form-container { background: #fff; border-radius: 8px; padding: 25px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); margin-bottom: 20px; }
.task-header { display: flex; justify-content: space-between; align-items: center; padding: 15px 0; border-bottom: 2px solid #e0e0e0; margin-bottom: 25px; }
.task-tags { display: flex; gap: 10px; flex-wrap: wrap; margin-bottom: 20px; }
.tag-btn { padding: 6px 12px; border-radius: 14px; border: 1px solid #e0e0e0; background: #fafafa; cursor: pointer; font-size: 13px; display: inline-flex; align-items: center; gap: 6px; transition: all 0.3s; }
.tag-btn:hover { background: #e0e0e0; }
.tag-btn.active { background: #2196F3; color: white; border-color: #2196F3; }
.tag-btn.priority-high { background: #f44336; color: white; border-color: #f44336; }
.tag-btn.priority-low { background: #4caf50; color: white; border-color: #4caf50; }
.description-section, .subtask-section, .attachments-section { margin: 20px 0; }
.subtask-item { display: flex; justify-content: space-between; align-items: center; padding: 10px; background: #f9f9f9; border-radius: 5px; margin-bottom: 8px; }
.file-preview { margin-top: 10px; }
.file-preview-item { display: inline-block; margin: 5px; padding: 8px 12px; background: #f0f0f0; border-radius: 5px; font-size: 12px; }
.select2-container { width: 100% !important; }
.reminder-inline { padding: 6px 12px; border-radius: 14px; border: 1px solid #e0e0e0; background: #fafafa; cursor: pointer; font-size: 13px; display: inline-flex; align-items: center; gap: 6px; }
.reminder-inline:hover { background: #e0e0e0; }
.reminder-inline.disabled { opacity: 0.6; cursor: not-allowed; }
.tag-btn input[type="date"], .tag-btn input[type="datetime-local"] { border: none; background: transparent; padding: 0; margin: 0; font-size: 13px; font-weight: bold; color: inherit; width: auto; min-width: 110px; cursor: pointer; }
.tag-btn input[type="date"]::-webkit-calendar-picker-indicator, .tag-btn input[type="datetime-local"]::-webkit-calendar-picker-indicator { cursor: pointer; opacity: 0.7; }
.tag-btn input[type="date"]:focus, .tag-btn input[type="datetime-local"]:focus { outline: none; }
</style>
</head>
<body>
<div class="container mt-4">
<div class="col-lg-12">
<div class="task-form-container">
<form action="" id="manage-task" enctype="multipart/form-data">
<input type="hidden" name="id" value="<?php echo isset($id) && $id ? intval($id) : '' ?>">
<input type="hidden" id="temp_reminder_datetime" value="">

<!-- Header -->
<div class="task-header">
<div class="form-group w-100 mb-0">
<label for="assigned_users"><strong><i class="fa fa-users"></i> Assigned To:</strong></label>
<select name="assigned_to[]" id="assigned_users" class="form-control select2" multiple="multiple">
<?php foreach($all_users_data as $user): ?>
<option value="<?php echo intval($user['id']) ?>" <?php echo in_array(intval($user['id']), $assigned_users) ? 'selected' : '' ?>>
<?php echo htmlspecialchars($user['name']) ?>
</option>
<?php endforeach; ?>
</select>
</div>
</div>

<!-- Task Title -->
<div class="form-group">
<input type="text" class="form-control form-control-lg" name="task" id="task_title" placeholder="Task Title" value="<?php echo isset($task) ? htmlspecialchars($task) : '' ?>" autocomplete="off" required>
</div>

<!-- Tags + Reminder -->
<div class="task-tags align-items-center">
<!-- Department -->
<div class="dropdown">
<button class="tag-btn dropdown-toggle" type="button" data-toggle="dropdown" id="department_btn">
<i class="fa fa-building"></i> <strong id="department_text"><?php echo htmlspecialchars($selected_dept_name) ?></strong>
</button>
<div class="dropdown-menu">
<a class="dropdown-item" href="#" data-dept-id="" data-dept-name="No Department">No Department</a>
<?php 
if($departments && $departments->num_rows > 0):
while($dept = $departments->fetch_assoc()):
?>
<a class="dropdown-item" href="#" data-dept-id="<?php echo intval($dept['id']) ?>" data-dept-name="<?php echo htmlspecialchars($dept['name']) ?>">
<?php echo htmlspecialchars($dept['name']) ?>
</a>
<?php endwhile; endif; ?>
</div>
</div>
<input type="hidden" name="department_id" id="department_id" value="<?php echo isset($department_id) ? intval($department_id) : '' ?>">

<!-- Priority -->
<div class="dropdown">
<button class="tag-btn dropdown-toggle <?php echo (isset($priority) && $priority != 'Medium') ? 'priority-'.strtolower($priority) : '' ?>" type="button" data-toggle="dropdown" id="priority_btn">
<i class="fa fa-exclamation"></i> <strong id="priority_text"><?php echo isset($priority) ? htmlspecialchars($priority) : 'Medium' ?></strong>
</button>
<div class="dropdown-menu">
<a class="dropdown-item priority-high" href="#" data-priority="High"><i class="fa fa-exclamation-circle text-danger"></i> High</a>
<a class="dropdown-item" href="#" data-priority="Medium"><i class="fa fa-exclamation-circle text-warning"></i> Medium</a>
<a class="dropdown-item priority-low" href="#" data-priority="Low"><i class="fa fa-exclamation-circle text-success"></i> Low</a>
</div>
</div>
<input type="hidden" name="priority" id="priority" value="<?php echo isset($priority) ? htmlspecialchars($priority) : 'Medium' ?>">

<!-- Status -->
<div class="dropdown">
<button class="tag-btn dropdown-toggle" type="button" data-toggle="dropdown" id="status_btn">
<i class="fa fa-info-circle"></i> <strong id="status_text"><?php 
if(isset($status)){
$status_text = array(1 => "Not Started", 2 => "In Progress", 3 => "Completed", 4 => "On Hold");
echo htmlspecialchars($status_text[$status] ?? "Not Started");
} else { echo "Not Started"; }
?></strong>
</button>
<div class="dropdown-menu">
<a class="dropdown-item" href="#" data-status="1" data-status-text="Not Started">Not Started</a>
<a class="dropdown-item" href="#" data-status="2" data-status-text="In Progress">In Progress</a>
<a class="dropdown-item" href="#" data-status="3" data-status-text="Completed">Completed</a>
<a class="dropdown-item" href="#" data-status="4" data-status-text="On Hold">On Hold</a>
</div>
</div>
<input type="hidden" name="status" id="status" value="<?php echo isset($status) ? intval($status) : '1' ?>">

<!-- Start / Due Date -->
<div class="tag-btn"><i class="fa fa-calendar-check"></i> <strong>Start:</strong> <input type="date" name="start_date" id="start_date" value="<?php echo htmlspecialchars($default_start_date); ?>" min="<?php echo $today_date; ?>"></div>
<div class="tag-btn"><i class="fa fa-calendar-times"></i> <strong>Due:</strong> <input type="date" name="due_date" id="due_date" value="<?php echo htmlspecialchars($default_due_date); ?>" min="<?php echo htmlspecialchars($default_start_date); ?>"></div>

<!-- Reminder -->
<div class="reminder-inline" id="setReminderBtn" onclick="openReminderModal()"><i class="fa fa-bell"></i> Set Reminder</div>
<input type="hidden" id="reminder_task_id" value="<?php echo $modal_task_id; ?>">

</div>

<!-- Description -->
<div class="description-section">
<label><strong>Description:</strong></label>
<textarea name="description" id="description" class="form-control summernote" rows="8" placeholder="Task Description"><?php echo isset($description) ? htmlspecialchars($description) : '' ?></textarea>
</div>

<!-- Subtasks -->
<div class="subtask-section">
<label><strong>Subtasks:</strong></label>
<div class="input-group mb-2">
<input type="text" class="form-control" id="subtask_input" placeholder="Add subtask title" autocomplete="off">
<div class="input-group-append"><button type="button" class="btn btn-sm btn-primary" onclick="addSubtask()"><i class="fa fa-plus"></i> Add</button></div>
</div>
<div id="subtasks_list"></div>
<input type="hidden" name="subtasks" id="subtasks_hidden" value='<?php echo isset($subtasks) ? json_encode($subtasks) : "[]" ?>'>
</div>

<!-- Attachments -->
<div class="attachments-section">
<label><strong><i class="fa fa-paperclip"></i> Attachments:</strong></label>
<input type="file" class="form-control" id="file-input" name="attachments[]" multiple accept="*/*">
<small class="text-muted">You can select multiple files (PDF, DOC, XLS, Images, etc.)</small>
<div id="file-list" class="file-preview mt-2"></div>
<?php if(isset($id) && $id):
$attachments_qry = $conn->query("SELECT * FROM task_attachments WHERE task_id = ".intval($id));
if($attachments_qry && $attachments_qry->num_rows > 0):
?>
<div class="mt-3">
<label><strong>Existing Attachments:</strong></label>
<ul class="list-group">
<?php while($att = $attachments_qry->fetch_assoc()): ?>
<li class="list-group-item d-flex justify-content-between align-items-center">
<a href="assets/uploads/tasks/<?php echo htmlspecialchars($att['file_path']) ?>" target="_blank"><i class="fa fa-file"></i> <?php echo htmlspecialchars($att['file_name']) ?></a>
<span class="badge badge-secondary"><?php echo number_format($att['file_size']/1024,2) ?> KB</span>
</li>
<?php endwhile; ?>
</ul>
</div>
<?php endif; endif; ?>

</div>

<!-- Submit -->
<div class="d-flex justify-content-start gap-2 mt-4">
<button type="submit" class="btn btn-primary btn-lg"><i class="fa fa-save"></i> Save Task</button>
<button type="button" class="btn btn-secondary btn-lg" onclick="location.href='index.php?page=task_list'"><i class="fa fa-times"></i> Cancel</button>
</div>
</form>
</div></div></div>

<!-- Reminder Modal -->
<div class="modal fade" id="reminderModal" tabindex="-1" role="dialog" aria-labelledby="reminderModalLabel" aria-hidden="true">
<div class="modal-dialog modal-dialog-centered" role="document">
<div class="modal-content">
<div class="modal-header">
<h5 class="modal-title" id="reminderModalLabel">Set Task Reminder</h5>
<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
</div>
<div class="modal-body">
<form id="reminderForm">
<div class="form-group">
<label for="reminder_datetime"><strong>Reminder Date & Time:</strong></label>
<input type="datetime-local" class="form-control" id="reminder_datetime" value="<?php echo $default_reminder_date; ?>" required>
</div>
<input type="hidden" id="reminder_task_id_modal" value="<?php echo $modal_task_id; ?>">
</form>
</div>
<div class="modal-footer">
<button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
<button type="button" class="btn btn-primary" onclick="saveReminder()">Save Reminder</button>
</div>
</div>
</div>
</div>

<!-- JS libs -->
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.full.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.18/summernote-bs4.min.js"></script>

<script>
$(document).ready(function(){
    $('.select2').select2({ width: '100%' });
    $('.summernote').summernote({height:150, toolbar:[['style',['bold','italic','underline','clear']],['para',['ul','ol','paragraph']],['insert',['link','picture']],['view',['fullscreen','codeview']]]});
    $('.dropdown-menu a[data-dept-id]').click(function(e){ e.preventDefault(); let dept_id=$(this).data('dept-id'); let dept_name=$(this).data('dept-name'); $('#department_id').val(dept_id); $('#department_text').text(dept_name); });
    $('.dropdown-menu a[data-priority]').click(function(e){ e.preventDefault(); let priority=$(this).data('priority'); $('#priority').val(priority); $('#priority_text').text(priority); $('#priority_btn').removeClass('priority-high priority-low'); if(priority=='High')$('#priority_btn').addClass('priority-high'); if(priority=='Low')$('#priority_btn').addClass('priority-low'); });
    $('.dropdown-menu a[data-status]').click(function(e){ e.preventDefault(); let status=$(this).data('status'); let text=$(this).data('status-text'); $('#status').val(status); $('#status_text').text(text); });
});

let subtasks=[];
function addSubtask(){ let val=$('#subtask_input').val().trim(); if(val==='') return; subtasks.push(val); renderSubtasks(); $('#subtask_input').val(''); }
function renderSubtasks(){ let html=''; subtasks.forEach((s,i)=>{html+=`<div class="subtask-item">${s} <button type="button" class="btn btn-sm btn-danger" onclick="removeSubtask(${i})"><i class="fa fa-times"></i></button></div>`; }); $('#subtasks_list').html(html); $('#subtasks_hidden').val(JSON.stringify(subtasks)); }
function removeSubtask(index){ subtasks.splice(index,1); renderSubtasks(); }

$('#file-input').on('change',function(){ $('#file-list').html(''); let files=this.files; for(let i=0;i<files.length;i++){ let f=files[i]; $('#file-list').append('<div class="file-preview-item"><i class="fa fa-file"></i> '+f.name+'</div>'); } });

function openReminderModal(){ $('#reminderModal').modal('show'); }
function saveReminder(){ let task_id=$('#reminder_task_id_modal').val(); let datetime=$('#reminder_datetime').val(); if(!datetime){alert('Select date & time'); return;}
if(task_id==''||task_id==0){ $('#temp_reminder_datetime').val(datetime); alert('Reminder saved temporarily. Will be set after saving the task.'); $('#reminderModal').modal('hide'); return; }
$.post('save_task_reminder.php',{task_id:task_id, reminder_datetime:datetime},function(resp){ if(resp==1){ alert('Reminder saved!'); $('#reminderModal').modal('hide'); } else { alert(resp); } }); }

$('#manage-task').submit(function(e){ e.preventDefault(); var form=$(this); var formData=new FormData(this); $.ajax({ url:'ajax.php?action=save_task', method:'POST', data:formData, contentType:false, processData:false, success:function(resp){ if(resp>0){ let temp_rem=$('#temp_reminder_datetime').val(); if(temp_rem){ $.post('save_task_reminder.php',{task_id:resp, reminder_datetime:temp_rem},function(r){ if(r==1) alert('Reminder saved!'); }); } location.href='index.php?page=task_list'; }else{ alert('Failed to save task'); } } }); });
</script>
</body>
</html>
