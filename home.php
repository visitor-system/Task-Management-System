<?php 
include('db_connect.php');

// Check if user is logged in
if(!isset($_SESSION['login_id']) || !isset($_SESSION['login_type'])){
	die("Access denied. Please login first.");
}

// Helper function to safely add conditions
function append_condition($base, $condition){
  return empty($base) ? "WHERE $condition" : "$base AND $condition";
}

// Setup base variables
$user_id = intval($_SESSION['login_id']);
$login_type = intval($_SESSION['login_type']);
$active_filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';
$filter_labels = array(
  'all' => 'All Tasks',
  'today' => "Today's Tasks",
  'overdue' => 'Overdue Tasks',
  'assigned_to_me' => 'Assigned to Me',
  'assigned_by_me' => 'Assigned by Me'
);
if(!array_key_exists($active_filter, $filter_labels)){
  $active_filter = 'all';
}
$active_filter_label = $filter_labels[$active_filter];

// Build role-based conditions
$where_task = "";
$where_task_alias = "";

if($login_type == 2){
  $condition_raw = "(assigned_by = $user_id OR FIND_IN_SET($user_id, assigned_to))";
  $where_task = append_condition($where_task, $condition_raw);
  $where_task_alias = append_condition($where_task_alias, "(t.assigned_by = $user_id OR FIND_IN_SET($user_id, t.assigned_to))");
}elseif($login_type == 3){
  $where_task = append_condition($where_task, "FIND_IN_SET($user_id, assigned_to)");
  $where_task_alias = append_condition($where_task_alias, "FIND_IN_SET($user_id, t.assigned_to)");
}

// Task summary counts with proper error handling
$total_qry = $conn->query("SELECT COUNT(*) as cnt FROM task_list $where_task");
$total_tasks = ($total_qry && $total_qry->num_rows > 0) ? $total_qry->fetch_assoc()['cnt'] : 0;

$overdue_qry = $conn->query("SELECT COUNT(*) as cnt FROM task_list " . append_condition($where_task, "deadline < CURDATE() AND status != 3"));
$overdue_tasks = ($overdue_qry && $overdue_qry->num_rows > 0) ? $overdue_qry->fetch_assoc()['cnt'] : 0;

$today_qry = $conn->query("SELECT COUNT(*) as cnt FROM task_list " . append_condition($where_task, "DATE(deadline) = CURDATE()"));
$today_tasks = ($today_qry && $today_qry->num_rows > 0) ? $today_qry->fetch_assoc()['cnt'] : 0;

// Get status-based counts
$pending_qry = $conn->query("SELECT COUNT(*) as cnt FROM task_list " . append_condition($where_task, "status = 1"));
$pending_tasks = ($pending_qry && $pending_qry->num_rows > 0) ? $pending_qry->fetch_assoc()['cnt'] : 0;

$in_progress_qry = $conn->query("SELECT COUNT(*) as cnt FROM task_list " . append_condition($where_task, "status = 2"));
$in_progress_tasks = ($in_progress_qry && $in_progress_qry->num_rows > 0) ? $in_progress_qry->fetch_assoc()['cnt'] : 0;

$completed_qry = $conn->query("SELECT COUNT(*) as cnt FROM task_list " . append_condition($where_task, "status = 3"));
$completed_tasks = ($completed_qry && $completed_qry->num_rows > 0) ? $completed_qry->fetch_assoc()['cnt'] : 0;

$on_hold_qry = $conn->query("SELECT COUNT(*) as cnt FROM task_list " . append_condition($where_task, "status = 4"));
$on_hold_tasks = ($on_hold_qry && $on_hold_qry->num_rows > 0) ? $on_hold_qry->fetch_assoc()['cnt'] : 0;

// Get unread notifications
$notif_qry = $conn->query("SELECT COUNT(*) as cnt FROM notifications WHERE user_id = $user_id AND is_read = 0");
$unread_notifications = ($notif_qry && $notif_qry->num_rows > 0) ? $notif_qry->fetch_assoc()['cnt'] : 0;

// Apply active filter to recent tasks / quick view
$recent_where = $where_task_alias;
switch($active_filter){
  case 'today':
    $recent_where = append_condition($recent_where, "DATE(t.deadline) = CURDATE()");
    break;
  case 'overdue':
    $recent_where = append_condition($recent_where, "(t.deadline IS NOT NULL AND t.deadline < CURDATE() AND t.status != 3)");
    break;
  case 'assigned_to_me':
    $recent_where = append_condition($recent_where, "FIND_IN_SET($user_id, t.assigned_to)");
    break;
  case 'assigned_by_me':
    $recent_where = append_condition($recent_where, "t.assigned_by = $user_id");
    break;
  default:
    // no additional condition
    break;
}

$task_list_filter_param = $active_filter != 'all' ? '&filter='.$active_filter : '';
?>
<!-- Info boxes -->
<div class="col-12">
  <div class="card">
    <div class="card-body">
      <div class="row">
        <div class="col-md-8">
          <h4>Welcome <?php echo isset($_SESSION['login_name']) ? $_SESSION['login_name'] : 'User' ?>!</h4>
          <p class="text-muted">Task Management Dashboard</p>
        </div>
        <div class="col-md-4 text-right">
          <?php if($unread_notifications > 0): ?>
            <a href="javascript:void(0)" class="btn btn-warning btn-sm" onclick="view_notifications()">
              <i class="fa fa-bell"></i> Notifications (<?php echo $unread_notifications ?>)
            </a>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</div>
<hr>

<!-- Task Statistics -->
<div class="row">
  <div class="col-lg-3 col-6">
    <div class="small-box bg-info">
      <div class="inner">
        <h3><?php echo $total_tasks ?></h3>
        <p>Total Tasks</p>
      </div>
      <div class="icon"><i class="fa fa-tasks"></i></div>
    </div>
  </div>
  <div class="col-lg-3 col-6">
    <div class="small-box bg-warning">
      <div class="inner">
        <h3><?php echo $today_tasks ?></h3>
        <p>Today's Tasks</p>
      </div>
      <div class="icon"><i class="fa fa-calendar-day"></i></div>
    </div>
  </div>
  <div class="col-lg-3 col-6">
    <div class="small-box bg-danger">
      <div class="inner">
        <h3><?php echo $overdue_tasks ?></h3>
        <p>Overdue Tasks</p>
      </div>
      <div class="icon"><i class="fa fa-exclamation-triangle"></i></div>
    </div>
  </div>
</div>

<!-- Quick Filters -->
<div class="row mb-3">
  <div class="col-12">
    <div class="card">
      <div class="card-header">
        <h5 class="card-title">Quick Filters</h5>
      </div>
      <div class="card-body">
        <div class="btn-group" role="group">
          <button type="button" class="btn btn-sm btn-outline-primary <?php echo $active_filter == 'today' ? 'active text-white' : '' ?>" onclick="apply_filter('today')">
            <i class="fa fa-calendar-day"></i> Today's Tasks (<?php echo $today_tasks ?>)
          </button>
          <button type="button" class="btn btn-sm btn-outline-danger <?php echo $active_filter == 'overdue' ? 'active text-white' : '' ?>" onclick="apply_filter('overdue')">
            <i class="fa fa-exclamation-triangle"></i> Overdue (<?php echo $overdue_tasks ?>)
          </button>
          <button type="button" class="btn btn-sm btn-outline-info <?php echo $active_filter == 'assigned_to_me' ? 'active text-white' : '' ?>" onclick="apply_filter('assigned_to_me')">
            <i class="fa fa-user"></i> Assigned to Me
          </button>
          <?php if($login_type != 3): ?>
          <button type="button" class="btn btn-sm btn-outline-success <?php echo $active_filter == 'assigned_by_me' ? 'active text-white' : '' ?>" onclick="apply_filter('assigned_by_me')">
            <i class="fa fa-user-check"></i> Assigned by Me
          </button>
          <?php endif; ?>
          <button type="button" class="btn btn-sm btn-outline-secondary <?php echo $active_filter == 'all' ? 'active text-white' : '' ?>" onclick="apply_filter('all')">
            <i class="fa fa-list"></i> All Tasks
          </button>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Recent Tasks -->
<div class="row">
  <div class="col-md-12">
    <div class="card card-outline card-success">
      <div class="card-header d-flex align-items-center justify-content-between">
        <div>
          <b>Recent Tasks</b>
          <small class="text-muted ml-2">(<?php echo $active_filter_label; ?>)</small>
        </div>
        <div class="card-tools">
          <a href="./index.php?page=task_list<?php echo $task_list_filter_param; ?>" class="btn btn-sm btn-primary">
            <i class="fa fa-list"></i> View All Tasks
          </a>
        </div>
      </div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table m-0 table-hover">
            <colgroup>
              <col width="5%">
              <col width="30%">
              <col width="20%">
              <col width="15%">
              <col width="15%">
              <col width="15%">
            </colgroup>
            <thead>
              <tr>
                <th>#</th>
                <th>Task</th>
                <th>Department</th>
                <th>Deadline</th>
                <th>Status</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
            <?php
            $i = 1;
            $task_status = array(1 => "Not Started", 2 => "In Progress", 3 => "Completed", 4 => "On Hold");
            $status_badges = array(1 => "secondary", 2 => "info", 3 => "success", 4 => "warning");
            
            $recent_tasks_qry = $conn->query("SELECT t.*, d.name as dept_name FROM task_list t 
                                              LEFT JOIN departments d ON d.id = t.department_id 
                                              $recent_where 
                                              ORDER BY t.date_created DESC LIMIT 10");
            
            if($recent_tasks_qry && $recent_tasks_qry->num_rows > 0):
              while($row = $recent_tasks_qry->fetch_assoc()):
                $is_overdue = isset($row['deadline']) && strtotime($row['deadline']) < strtotime(date('Y-m-d')) && $row['status'] != 3;
            ?>
              <tr class="<?php echo $is_overdue ? 'table-danger' : '' ?>">
                <td><?php echo $i++ ?></td>
                <td>
                  <strong><?php echo ucwords($row['task']) ?></strong>
                  <?php if(isset($row['priority'])): ?>
                    <span class="badge badge-<?php echo $row['priority'] == 'High' ? 'danger' : ($row['priority'] == 'Medium' ? 'warning' : 'info') ?>">
                      <?php echo $row['priority'] ?>
                    </span>
                  <?php endif; ?>
                </td>
                <td><?php echo $row['dept_name'] ? $row['dept_name'] : 'N/A' ?></td>
                <td>
                  <?php if(isset($row['deadline'])): ?>
                    <small class="<?php echo $is_overdue ? 'text-danger font-weight-bold' : 'text-muted' ?>">
                      <?php echo date("M d, Y", strtotime($row['deadline'])) ?>
                      <?php if($is_overdue): ?>
                        <br><span class="badge badge-danger">Overdue</span>
                      <?php endif; ?>
                    </small>
                  <?php else: ?>
                    <small class="text-muted">N/A</small>
                  <?php endif; ?>
                </td>
                <td>
                  <span class="badge badge-<?php echo $status_badges[$row['status']] ?? 'secondary' ?>">
                    <?php echo $task_status[$row['status']] ?? 'Unknown' ?>
                  </span>
                </td>
                <td>
                  <a class="btn btn-primary btn-sm" href="./index.php?page=view_task&task_id=<?php echo $row['task_id'] ?>">
                    <i class="fas fa-eye"></i> View
                  </a>
                </td>
              </tr>
            <?php 
              endwhile;
            else: 
            ?>
              <tr>
                <td colspan="6" class="text-center text-muted p-3">No tasks found</td>
              </tr>
            <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Department Statistics (New Feature: Enhanced Dashboard) -->
<?php if($login_type == 1): // Admin only ?>
<div class="row mt-4">
  <div class="col-12">
    <div class="card card-outline card-primary">
      <div class="card-header">
        <h5 class="card-title"><i class="fa fa-chart-bar"></i> Department-wise Task Summary</h5>
      </div>
      <div class="card-body">
        <div class="row" id="department-stats-container">
          <?php 
          $departments = $conn->query("SELECT * FROM departments ORDER BY name");
          if($departments && $departments->num_rows > 0):
            while($dept = $departments->fetch_assoc()):
              $dept_id = intval($dept['id']);
              
              $total_qry = $conn->query("SELECT COUNT(*) as cnt FROM task_list WHERE department_id = $dept_id");
              $total = ($total_qry && $total_qry->num_rows > 0) ? $total_qry->fetch_assoc()['cnt'] : 0;
              
              $pending_qry = $conn->query("SELECT COUNT(*) as cnt FROM task_list WHERE department_id = $dept_id AND status = 1");
              $pending = ($pending_qry && $pending_qry->num_rows > 0) ? $pending_qry->fetch_assoc()['cnt'] : 0;
              
              $in_progress_qry = $conn->query("SELECT COUNT(*) as cnt FROM task_list WHERE department_id = $dept_id AND status = 2");
              $in_progress = ($in_progress_qry && $in_progress_qry->num_rows > 0) ? $in_progress_qry->fetch_assoc()['cnt'] : 0;
              
              $completed_qry = $conn->query("SELECT COUNT(*) as cnt FROM task_list WHERE department_id = $dept_id AND status = 3");
              $completed = ($completed_qry && $completed_qry->num_rows > 0) ? $completed_qry->fetch_assoc()['cnt'] : 0;
              
              $overdue_qry = $conn->query("SELECT COUNT(*) as cnt FROM task_list WHERE department_id = $dept_id AND deadline < CURDATE() AND status != 3");
              $overdue = ($overdue_qry && $overdue_qry->num_rows > 0) ? $overdue_qry->fetch_assoc()['cnt'] : 0;
              
              $completion_percent = $total > 0 ? ($completed / $total) * 100 : 0;
          ?>
          <div class="col-md-6 col-lg-4 mb-3">
            <div class="card border-left-primary shadow h-100 py-2">
              <div class="card-body">
                <div class="text-primary font-weight-bold text-uppercase mb-2"><?php echo htmlspecialchars($dept['name']); ?></div>
                <div class="h5 mb-3 font-weight-bold"><?php echo $total; ?> Total</div>
                <div class="row no-gutters align-items-center">
                  <div class="col mr-2">
                    <div class="text-xs font-weight-bold text-secondary text-uppercase mb-1">Progress</div>
                    <div class="progress progress-sm">
                      <div class="progress-bar bg-success" role="progressbar" style="width: <?php echo $completion_percent; ?>%" aria-valuenow="<?php echo $completion_percent; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                  </div>
                  <div class="col-auto">
                    <div class="text-xs font-weight-bold text-success"><?php echo number_format($completion_percent, 1); ?>%</div>
                  </div>
                </div>
                <hr>
                <div class="small">
                  <span class="badge badge-info">Pending: <?php echo $pending; ?></span>
                  <span class="badge badge-warning">In Progress: <?php echo $in_progress; ?></span>
                  <span class="badge badge-success">Completed: <?php echo $completed; ?></span>
                  <?php if($overdue > 0): ?>
                    <span class="badge badge-danger">Overdue: <?php echo $overdue; ?></span>
                  <?php endif; ?>
                </div>
              </div>
            </div>
          </div>
          <?php 
            endwhile;
          else:
          ?>
          <div class="col-12">
            <div class="alert alert-info">No departments found.</div>
          </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</div>
<?php endif; ?>



  
  <div class="col-md-6">
    <div class="card card-outline card-warning">
      <div class="card-header">
        <h5 class="card-title"><i class="fa fa-envelope"></i> Email Notifications Status</h5>
      </div>
      <div class="card-body">
        <div class="row">
          <div class="col-6">
            <div class="text-center">
              <h3 class="text-primary" id="pending-emails-count">0</h3>
              <p class="text-muted small">Pending Emails</p>
            </div>
          </div>
          <div class="col-6">
            <div class="text-center">
              <h3 class="text-success" id="sent-emails-count">0</h3>
              <p class="text-muted small">Sent Emails</p>
            </div>
          </div>
        </div>
        <hr>
        <div class="small">
          <p><strong>Next Daily Summary:</strong> <span id="next-summary-time">8:00 AM</span></p>
          <p><strong>Deadline Reminders:</strong> 
            <span class="badge badge-success" id="reminder-status">Enabled</span>
          </p>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
function apply_filter(filter){
  var url = './index.php?page=home';
  if(filter && filter !== 'all'){
    url += '&filter=' + filter;
  }
  window.location.href = url;
}
function view_notifications(){
  uni_modal('Notifications', 'view_notifications.php', 'large');
}

// Load recent activities on page load
$(document).ready(function(){
  load_recent_activities();
  load_email_stats();
  // Refresh activities every 60 seconds
  setInterval(load_recent_activities, 60000);
  setInterval(load_email_stats, 120000);
});

function load_recent_activities(){
  $.ajax({
    url: 'ajax.php?action=get_recent_activities&limit=10',
    type: 'GET',
    dataType: 'json',
    success: function(data){
      let html = '';
      if(data.length > 0){
        data.forEach(function(activity){
          let icon = 'fa-edit';
          if(activity.action.includes('delete')) icon = 'fa-trash text-danger';
          else if(activity.action.includes('create')) icon = 'fa-plus text-success';
          else if(activity.action.includes('update')) icon = 'fa-sync text-info';
          
          html += '<div class="p-2 border-bottom small"><i class="fa '+icon+'"></i> <strong>' + activity.user_name + '</strong> ' + activity.action + ' ' + activity.entity_type + ' <small class="text-muted">' + new Date(activity.date_created).toLocaleString() + '</small></div>';
        });
      } else {
        html = '<div class="text-center p-3 text-muted">No recent activities</div>';
      }
      $('#recent-activities-container').html(html);
    }
  });
}

function load_email_stats(){
  $.ajax({
    url: 'ajax.php?action=get_email_stats',
    type: 'GET',
    dataType: 'json',
    success: function(data){
      $('#pending-emails-count').text(data.pending || 0);
      $('#sent-emails-count').text(data.sent || 0);
    }
  });
}
</script>

