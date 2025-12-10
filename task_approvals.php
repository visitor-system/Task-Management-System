<?php 


include 'db_connect.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Only Department Heads (login_type = 2)
$login_type = intval($_SESSION['login_type']);
if($login_type != 2){
    echo "<div class='alert alert-danger'>Access Denied! Only Department Heads can access this page.</div>";
    exit;
}

// Get Department ID
$dept_id = 0;
if(isset($_SESSION['login_department_id'])){
    $dept_id = intval($_SESSION['login_department_id']);
} else {
    $user_qry = $conn->query("SELECT department_id FROM users WHERE id = ".intval($_SESSION['login_id']));
    if($user_qry && $user_qry->num_rows > 0){
        $user_data = $user_qry->fetch_assoc();
        $dept_id = intval($user_data['department_id']);
    }
}

$where = " WHERE t.department_id = $dept_id ";

// Filter tabs
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'pending';
$title = '';
$badge_class = '';

switch($filter){
    case 'pending':
        $where .= " AND (t.approval_status IS NULL OR t.approval_status = 'pending') ";
        $title = "Pending Task Approvals";
        $badge_class = "badge-warning";
        break;
    case 'approved':
        $where .= " AND t.approval_status = 'approved' ";
        $title = "Approved Tasks";
        $badge_class = "badge-success";
        break;
    case 'rejected':
        $where .= " AND t.approval_status = 'rejected' ";
        $title = "Rejected Tasks (Reworking)";
        $badge_class = "badge-danger";
        break;
    case 'feedback':
        $where .= " AND t.approval_status = 'feedback' ";
        $title = "Tasks Awaiting Revision";
        $badge_class = "badge-info";
        break;
    case 'all':
    default:
        $title = "All Task Reviews";
        $badge_class = "badge-secondary";
        break;
}
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-lg-12">
            <div class="card card-primary card-outline">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-tasks"></i> <?php echo $title; ?></h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Filter Tabs -->
                    <div class="mb-3">
                        <ul class="nav nav-tabs" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link <?php echo $filter == 'pending' ? 'active' : '' ?>" href="?page=task_approvals&filter=pending">
                                    <span class="badge badge-warning">Pending</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?php echo $filter == 'approved' ? 'active' : '' ?>" href="?page=task_approvals&filter=approved">
                                    <span class="badge badge-success">Approved</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?php echo $filter == 'feedback' ? 'active' : '' ?>" href="?page=task_approvals&filter=feedback">
                                    <span class="badge badge-info">Feedback</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?php echo $filter == 'rejected' ? 'active' : '' ?>" href="?page=task_approvals&filter=rejected">
                                    <span class="badge badge-danger">Rejected</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?php echo $filter == 'all' ? 'active' : '' ?>" href="?page=task_approvals&filter=all">
                                    <span class="badge badge-secondary">All Reviews</span>
                                </a>
                            </li>
                        </ul>
                    </div>

                    <table class="table table-hover table-sm" id="approval_table">
                        <thead>
                            <tr>
                                <th>Task</th>
                                <th>Department</th>
                                <th>Assigned To</th>
                                <th>Status</th>
                                <th>Deadline</th>
                                <th>Approval Status</th>
                                <th>Created</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $status_text = array(1 => "Not Started", 2 => "In Progress", 3 => "Completed", 4 => "On Hold");
                            $status_class = array(1 => "secondary", 2 => "primary", 3 => "success", 4 => "warning");

                            $qry = $conn->query("SELECT t.*, d.name as dept_name,
                                (SELECT GROUP_CONCAT(CONCAT(u.firstname,' ',u.lastname) SEPARATOR ', ') 
                                 FROM users u 
                                 WHERE FIND_IN_SET(u.id, t.assigned_to) > 0) as assigned_user 
                                FROM task_list t 
                                LEFT JOIN departments d ON d.id = t.department_id 
                                $where 
                                ORDER BY t.deadline ASC, t.date_created DESC");

                            if($qry && $qry->num_rows > 0){
                                while($row = $qry->fetch_assoc()):
                                    $approval_status = isset($row['approval_status']) ? $row['approval_status'] : 'pending';
                                    $approval_badge = 'badge-secondary';
                                    if($approval_status == 'approved') $approval_badge = 'badge-success';
                                    elseif($approval_status == 'rejected') $approval_badge = 'badge-danger';
                                    elseif($approval_status == 'feedback') $approval_badge = 'badge-info';
                                    elseif($approval_status == 'pending') $approval_badge = 'badge-warning';
                            ?>
                                <tr>
                                    <td>
                                        <small class="text-muted">Task ID: <?php echo $row['task_id'] ?></small><br>
                                        <strong><?php echo substr($row['task'], 0, 50) ?></strong>
                                    </td>
                                    <td><?php echo $row['dept_name'] ?></td>
                                    <td><?php echo $row['assigned_user'] ?></td>
                                    <td>
                                        <span class="badge badge-<?php echo $status_class[$row['status']] ?>">
                                            <?php echo $status_text[$row['status']] ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php 
                                        if(!empty($row['deadline'])){
                                            $deadline = new DateTime($row['deadline']);
                                            $now = new DateTime();
                                            $interval = $now->diff($deadline);
                                            if($now > $deadline){
                                                echo '<span class="badge badge-danger">OVERDUE: '.$interval->format('%a days ago').'</span>';
                                            } else {
                                                echo '<span class="badge badge-info">'.$interval->format('%a days left').'</span>';
                                            }
                                        } else {
                                            echo '<span class="text-muted">-</span>';
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <span class="badge <?php echo $approval_badge ?> p-2">
                                            <?php echo ucfirst($approval_status) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <small><?php echo date('M d, Y', strtotime($row['date_created'])) ?></small>
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-primary review_task" data-task_id="<?php echo $row['task_id'] ?>">
                                            <i class="fas fa-eye"></i> Review
                                        </button>
                                    </td>
                                </tr>
                            <?php 
                                endwhile;
                            } else {
                                echo '<tr><td colspan="8" class="text-center text-muted p-4">No tasks found.</td></tr>';
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function(){
        $('#approval_table').DataTable({
            columnDefs: [
                {orderable: false, targets: 7}
            ],
            order: [[4, 'asc']],
            pageLength: 15,
            responsive: true
        });

        // Open review modal
        $(document).on('click', '.review_task', function(){
            var task_id = $(this).attr('data-task_id');
            uni_modal("Review Task", "review_task.php?task_id="+task_id);
        });
    });
</script>
