<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
include 'db_connect.php';

// Get task_id from GET
$task_id = intval($_GET['task_id'] ?? 0);
if($task_id <= 0){
    die("Invalid Task ID.");
}

// Fetch task info
$qry = $conn->query("SELECT t.*, d.name as dept_name 
                     FROM task_list t 
                     LEFT JOIN departments d ON d.id = t.department_id 
                     WHERE t.task_id = $task_id");
if(!$qry || $qry->num_rows == 0){
    die("Task not found.");
}
$task_data = $qry->fetch_assoc();

// Assigned users
$assigned_names = [];
if(!empty($task_data['assigned_to'])){
    $uids = explode(",", $task_data['assigned_to']);
    foreach($uids as $uid){
        $uid = intval($uid);
        if($uid > 0){
            $uqry = $conn->query("SELECT CONCAT(firstname,' ',lastname) as name FROM users WHERE id=$uid");
            if($uqry && $uqry->num_rows>0){
                $u = $uqry->fetch_assoc();
                $assigned_names[] = $u['name'];
            }
        }
    }
}

// Status text & badge (same as task_list)
$status_text = [1=>"Not Started",2=>"In Progress",3=>"Completed",4=>"On Hold"];
$status_class = [1=>"secondary",2=>"info",3=>"success",4=>"warning"];
$current_status = $task_data['status'] ?? 1;

// Fetch existing review (if any)
$review_q = $conn->query("SELECT * FROM task_reviews WHERE task_id=$task_id AND reviewed_by=".$_SESSION['login_id']);
$review_approval_status = '';
$review_remarks = '';
if($review_q && $review_q->num_rows>0){
    $rev = $review_q->fetch_assoc();
    $review_approval_status = $rev['approval_status'];
    $review_remarks = $rev['remarks'];
}
?>
<div class="container-fluid">
    <form id="review-task">
        <input type="hidden" name="task_id" value="<?php echo $task_id ?>">

        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label>Task</label>
                    <input type="text" class="form-control" value="<?php echo $task_data['task'] ?>" readonly>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label>Department</label>
                    <input type="text" class="form-control" value="<?php echo $task_data['dept_name'] ?? '-' ?>" readonly>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label>Assigned To</label>
                    <input type="text" class="form-control" value="<?php echo !empty($assigned_names) ? implode(", ",$assigned_names) : 'Not assigned' ?>" readonly>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label>Current Status</label>
                    <div>
                        <span class="badge badge-<?php echo $status_class[$current_status] ?>">
                            <?php echo $status_text[$current_status] ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <hr>
        <h5><strong>Department Head Review & Approval</strong></h5>

        <div class="form-group">
            <label>Approval Decision <span class="text-danger">*</span></label>
            <div class="btn-group btn-group-toggle d-flex" data-toggle="buttons" style="width:100%">
                <label class="btn btn-outline-success flex-fill <?php echo $review_approval_status=='approved'?'active':'' ?>">
                    <input type="radio" name="approval_status" value="approved" <?php echo $review_approval_status=='approved'?'checked':'' ?>> Approve
                </label>
                <label class="btn btn-outline-warning flex-fill <?php echo $review_approval_status=='feedback'?'active':'' ?>">
                    <input type="radio" name="approval_status" value="feedback" <?php echo $review_approval_status=='feedback'?'checked':'' ?>> Need Feedback
                </label>
                <label class="btn btn-outline-danger flex-fill <?php echo $review_approval_status=='rejected'?'active':'' ?>">
                    <input type="radio" name="approval_status" value="rejected" <?php echo $review_approval_status=='rejected'?'checked':'' ?>> Reject
                </label>
            </div>
        </div>

        <div class="form-group">
            <label>Remarks / Feedback / Rejection Reason</label>
            <textarea name="remarks" class="form-control" rows="5" placeholder="Add remarks..."><?php echo $review_remarks ?></textarea>
        </div>

        <button type="submit" class="btn btn-primary">Submit Review</button>
    </form>
</div>

<script>
$('#review-task').submit(function(e){
    e.preventDefault();
    var formData = new FormData(this);

    // Validate
    if(!$('input[name="approval_status"]:checked').length){
        alert('Select an approval decision!');
        return;
    }
    var status = $('input[name="approval_status"]:checked').val();
    var remarks = $('textarea[name="remarks"]').val().trim();
    if((status=='feedback'||status=='rejected') && !remarks){
        alert('Remarks required for feedback/rejected.');
        return;
    }

   $.ajax({
    url: 'save_review.php',
    method: 'POST',
    data: formData,
    dataType: 'json',
    success: function(data){
        if(data && data.status == 1){
            alert('Review saved successfully.');
            // Close the modal
            $('#uni_modal').modal('hide');

            // Refresh the task approvals table
            $.ajax({
                url: 'task_approvals.php?filter=<?php echo $filter; ?>',
                success: function(res){
                    // Replace table container content with new content
                    $('#approval_table').closest('.card-body').html(
                        $(res).find('#approval_table').closest('.card-body').html()
                    );
                },
                error: function(xhr, status, err){
                    console.error('Error refreshing table:', err);
                }
            });
        } else {
            alert(data.msg || 'Error saving review.');
        }
    },
    error: function(xhr, status, err){
        console.error(xhr.responseText);
        alert('AJAX request failed: ' + err);
    }
});

});
</script>
