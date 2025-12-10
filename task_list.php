<?php 
include 'db_connect.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if(!isset($_SESSION['login_id']) || !isset($_SESSION['login_type'])){
    die("Access denied. Please login first.");
}

$user_id = intval($_SESSION['login_id']);
$login_type = intval($_SESSION['login_type']);
$allowed_filters = array('all','today','overdue','assigned_to_me','assigned_by_me');
$active_filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';
if(!in_array($active_filter, $allowed_filters)){
    $active_filter = 'all';
}
?>
<div class="col-lg-12">
    <div class="card card-outline card-primary">
        <div class="card-header">
            <h3 class="card-title">Task List</h3>
            <div class="card-tools">
                <?php if($login_type != 3): ?>
                    <a class="btn btn-block btn-sm btn-primary btn-flat" href="./index.php?page=manage_task"><i class="fa fa-plus"></i> Create New Task</a>
                <?php endif; ?>
            </div>
        </div>

        <!-- Filter Row -->
        <div class="card-header">
            <div class="btn-group btn-group-sm" role="group">
                <a href="?page=task_list" class="btn btn-outline-secondary <?php echo $active_filter == 'all' ? 'active' : '' ?>">All Tasks</a>
                <a href="?page=task_list&filter=today" class="btn btn-outline-secondary <?php echo $active_filter == 'today' ? 'active' : '' ?>"><i class="fa fa-calendar"></i> Today</a>
                <a href="?page=task_list&filter=overdue" class="btn btn-outline-secondary <?php echo $active_filter == 'overdue' ? 'active' : '' ?>"><i class="fa fa-exclamation-circle"></i> Overdue</a>
                <a href="?page=task_list&filter=assigned_to_me" class="btn btn-outline-secondary <?php echo $active_filter == 'assigned_to_me' ? 'active' : '' ?>"><i class="fa fa-user"></i> Assigned to Me</a>
                <?php if($login_type != 3): ?>
                    <a href="?page=task_list&filter=assigned_by_me" class="btn btn-outline-secondary <?php echo $active_filter == 'assigned_by_me' ? 'active' : '' ?>"><i class="fa fa-check"></i> Assigned by Me</a>
                <?php endif; ?>
            </div>
        </div>

        <div class="card-body">
            <table class="table table-hover table-condensed" id="taskTable">
                <colgroup>
                    <col width="5%">
                    <col width="30%">
                    <col width="15%">
                    <col width="15%">
                    <col width="15%">
                    <col width="10%">
                    <col width="10%">
                </colgroup>
                <thead>
                    <tr>
                        <th class="text-center">#</th>
                        <th>Task</th>
                        <th>Department</th>
                        <th>Assigned To</th>
                        <th>Deadline</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $i = 1;
                    $where_clauses = array();

                    // Role logic
                    if($login_type == 3){ // Employee
                        $where_clauses[] = "FIND_IN_SET($user_id, t.assigned_to)";
                    }

                    $task_status = array(1 => "Not Started", 2 => "In Progress", 3 => "Completed", 4 => "On Hold");
                    $status_badges = array(1 => "secondary", 2 => "info", 3 => "success", 4 => "warning");
                    $priority_colors = array('High' => 'danger', 'Medium' => 'warning', 'Low' => 'info');

                    // Filters
                    switch($active_filter){
                        case 'today':
                            $where_clauses[] = "DATE(t.deadline) = CURDATE()";
                            break;
                        case 'overdue':
                            $where_clauses[] = "(t.deadline IS NOT NULL AND t.deadline < CURDATE() AND t.status != 3)";
                            break;
                        case 'assigned_to_me':
                            $where_clauses[] = "FIND_IN_SET($user_id, t.assigned_to)";
                            break;
                        case 'assigned_by_me':
                            $where_clauses[] = "t.assigned_by = $user_id";
                            break;
                    }

                    $where_sql = "";
                    if(count($where_clauses) > 0){
                        $where_sql = " WHERE ".implode(' AND ', $where_clauses)." ";
                    }

                    $qry = $conn->query("SELECT t.*, d.name as dept_name FROM task_list t 
                                        LEFT JOIN departments d ON d.id = t.department_id 
                                        $where_sql 
                                        ORDER BY FIELD(t.priority,'High','Medium','Low'), t.deadline ASC");

                    if($qry && $qry->num_rows > 0):
                        while($row = $qry->fetch_assoc()):
                        $desc = strip_tags(html_entity_decode($row['description']));
                        $desc = str_replace(array("<li>", "</li>"), array("", ", "), $desc);

                        $is_overdue = isset($row['deadline']) && strtotime($row['deadline']) < strtotime(date('Y-m-d')) && $row['status'] != 3;

                        $assigned_users_raw = isset($row['assigned_to']) ? explode(',', $row['assigned_to']) : array();
                        $assigned_names = array();
                        foreach($assigned_users_raw as $uid){
                            $uid = intval(trim($uid));
                            if($uid > 0){
                                $u_qry = $conn->query("SELECT CONCAT(firstname,' ',lastname) as name FROM users WHERE id = $uid");
                                if($u_qry && $u_qry->num_rows > 0){
                                    $u = $u_qry->fetch_assoc();
                                    if($u) $assigned_names[] = $u['name'];
                                }
                            }
                        }
                    ?>
                    <tr class="<?php echo $is_overdue ? 'table-danger' : '' ?>">
                        <td class="text-center"><?php echo $i++ ?></td>
                        <td>
                            <p><b><?php echo ucwords($row['task']) ?></b>
                            <?php if(isset($row['priority'])): ?>
                                <span class="badge badge-<?php echo $priority_colors[$row['priority']] ?? 'secondary' ?>"><?php echo $row['priority'] ?></span>
                            <?php endif; ?>
                            </p>
                            <p class="truncate small text-muted"><?php echo $desc ?></p>
                        </td>
                        <td>
                            <?php if(isset($row['dept_name'])): ?>
                                <span class="badge badge-light"><i class="fa fa-building"></i> <?php echo $row['dept_name'] ?></span>
                            <?php else: ?>
                                <span class="badge badge-light">-</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if(!empty($assigned_names)): ?>
                                <small><?php echo implode('<br>', $assigned_names) ?></small>
                            <?php else: ?>
                                <small class="text-muted">-</small>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if(isset($row['deadline'])): ?>
                                <small class="<?php echo $is_overdue ? 'text-danger font-weight-bold' : 'text-muted' ?>">
                                    <i class="fa fa-calendar"></i> <?php echo date("M d, Y", strtotime($row['deadline'])) ?>
                                    <?php if($is_overdue): ?>
                                        <br><span class="badge badge-danger">Overdue</span>
                                    <?php endif; ?>
                                </small>
                            <?php else: ?>
                                <small class="text-muted">-</small>
                            <?php endif; ?>
                        </td>
                        <td class="text-center">
                            <span class="badge badge-<?php echo $status_badges[$row['status']] ?? 'secondary' ?>">
                                <?php echo $task_status[$row['status']] ?? 'Unknown' ?>
                            </span>
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm" role="group">
                                <a href="./index.php?page=view_task&task_id=<?php echo intval($row['task_id']) ?>" class="btn btn-info"><i class="fa fa-eye"></i></a>

                                <?php if($login_type != 3): ?>
                                <a href="./index.php?page=manage_task&id=<?php echo intval($row['task_id']) ?>" class="btn btn-primary"><i class="fa fa-edit"></i></a>
                                <button type="button" class="btn btn-danger delete_task" data-id="<?php echo intval($row['task_id']) ?>"><i class="fa fa-trash"></i></button>
                                <?php endif; ?>

                                <?php if($row['status'] != 3 && in_array($user_id, $assigned_users_raw)): ?>
                                <a href="./index.php?page=update_task_status&task_id=<?php echo intval($row['task_id']) ?>" class="btn btn-success"><i class="fa fa-check"></i></a>
                                <?php endif; ?>

                                <?php if($login_type == 2 && $row['status'] == 3): ?>
                                <a href="./index.php?page=review_task&task_id=<?php echo intval($row['task_id']) ?>" class="btn btn-warning"><i class="fa fa-list"></i></a>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php 
                        endwhile;
                    else:
                    ?>
                    <tr>
                        <td colspan="7" class="text-center">
                            <div class="alert alert-info">No tasks found.</div>
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
    table p{ margin: unset !important; }
    table td{ vertical-align: middle !important; }
</style>

<script>
$(document).ready(function(){
    $('#taskTable').DataTable({
        responsive: true,
        orderCellsTop: true
    });

   $('.delete_task').click(function(){
    let id = $(this).attr('data-id');
    if(confirm("Are you sure you want to delete this task?")){
        $.ajax({
            url:'ajax.php?action=delete_task',
            method:'POST',
            data:{id:id},
            success:function(resp){
                resp = resp.trim(); // remove any whitespace
                if(resp == "1"){   // compare as string
                    alert("Task deleted successfully.");
                    setTimeout(function(){ location.reload(); },1000);
                } else {
                    alert("Task deletion failed: " + resp);
                }
            },
            error: function(xhr, status, error){
                alert("AJAX error: " + error);
            }
        });
    }
});
});
</script>
