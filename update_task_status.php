<?php 
include 'db_connect.php';

// Get task_id from URL
if(isset($_GET['task_id'])){
    $task_id = intval($_GET['task_id']);
    $qry = $conn->query("SELECT * FROM task_list WHERE task_id = $task_id");

    if(!$qry){
        die("Query failed: " . $conn->error);
    }

    $task_data = $qry->fetch_assoc();
    
    if(!$task_data){
        echo "<div class='alert alert-danger'>Task not found with task_id = $task_id</div>";
        $task = $status = $progress_percentage = $progress_remarks = "";
    } else {
        foreach($task_data as $k => $v){
            $$k = $v;
        }
    }
} else {
    echo "<div class='alert alert-danger'>No task_id provided in URL.</div>";
    $task = $status = $progress_percentage = $progress_remarks = "";
}
?>

<div class="container-fluid">
    <form id="update-task-status">
        <input type="hidden" name="task_id" value="<?php echo isset($task_id) ? $task_id : '' ?>">
        
        <div class="form-group">
            <label for="">Task</label>
            <input type="text" class="form-control" value="<?php echo isset($task) ? $task : '' ?>" readonly>
        </div>

        <div class="form-group">
            <label for="">Current Status</label>
            <?php 
            $status_text = array(1 => "Not Started", 2 => "In Progress", 3 => "Completed", 4 => "On Hold");
            $status_class = array(1 => "secondary", 2 => "primary", 3 => "success", 4 => "warning");
            ?>
            <div>
                <span class="badge badge-<?php echo $status_class[isset($status) ? $status : 1] ?>">
                    <?php echo $status_text[isset($status) ? $status : 1] ?>
                </span>
            </div>
        </div>

        <div class="form-group">
            <label for="">Update Status To</label>
            <select name="status" class="custom-select" required>
                <option value="1" <?php echo isset($status) && $status == 1 ? 'selected' : '' ?>>Not Started</option>
                <option value="2" <?php echo isset($status) && $status == 2 ? 'selected' : '' ?>>In Progress</option>
                <option value="3" <?php echo isset($status) && $status == 3 ? 'selected' : '' ?>>Completed</option>
                <option value="4" <?php echo isset($status) && $status == 4 ? 'selected' : '' ?>>On Hold</option>
            </select>
        </div>

        <div class="form-group">
            <label for="">Progress Percentage</label>
            <input type="number" class="form-control" name="progress_percentage" min="0" max="100" value="<?php echo isset($progress_percentage) ? $progress_percentage : 0 ?>">
        </div>

        <div class="form-group">
            <label for="">Remarks / Progress Update</label>
            <textarea name="remarks" class="form-control" rows="5" placeholder="Add your daily progress remarks here..."><?php echo isset($progress_remarks) ? $progress_remarks : '' ?></textarea>
        </div>

        <button type="submit" class="btn btn-primary">Update Status</button>
    </form>
</div>

<script>
$('#update-task-status').submit(function(e){
    e.preventDefault();
    start_load(); // assuming you have this function for loader
    $.ajax({
        url:'save_status.php',
        method:'POST',
        data: new FormData(this),
        cache:false,
        contentType:false,
        processData:false,
        success:function(resp){
            if(resp == 1){
                alert_toast('Task status updated successfully',"success");
                setTimeout(function(){
                    location.reload();
                },1500)
            } else {
                alert_toast(resp || 'Task status update failed',"danger");
            }
        }
    });
});
</script>
