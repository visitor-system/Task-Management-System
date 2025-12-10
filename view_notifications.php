<?php 
session_start();
include 'db_connect.php';

// Check if user is logged in
if(!isset($_SESSION['login_id'])){
	die("Access denied. Please login first.");
}

$user_id = intval($_SESSION['login_id']);
$notifications = $conn->query("SELECT n.*, t.task FROM notifications n LEFT JOIN task_list t ON t.id = n.task_id WHERE n.user_id = $user_id ORDER BY n.date_created DESC LIMIT 50");
?>
<div class="container-fluid">
	<div class="card">
		<div class="card-header">
			<h5 class="card-title">Notifications</h5>
		</div>
		<div class="card-body">
			<?php if($notifications && $notifications->num_rows > 0): ?>
				<ul class="list-group">
					<?php while($notif = $notifications->fetch_assoc()): ?>
						<li class="list-group-item <?php echo $notif['is_read'] == 0 ? 'list-group-item-warning' : '' ?>">
							<div class="d-flex justify-content-between align-items-start">
								<div class="flex-grow-1">
									<h6 class="mb-1"><?php echo $notif['title'] ?></h6>
									<p class="mb-1"><?php echo $notif['message'] ?></p>
									<small class="text-muted"><?php echo date("M d, Y h:i A", strtotime($notif['date_created'])) ?></small>
								</div>
								<div>
									<?php if($notif['task_id']): ?>
										<a href="./index.php?page=view_task&id=<?php echo $notif['task_id'] ?>" class="btn btn-sm btn-primary">View Task</a>
									<?php endif; ?>
									<?php if($notif['is_read'] == 0): ?>
										<button class="btn btn-sm btn-secondary mark_read" data-id="<?php echo $notif['id'] ?>">Mark Read</button>
									<?php endif; ?>
								</div>
							</div>
						</li>
					<?php endwhile; ?>
				</ul>
			<?php else: ?>
				<div class="alert alert-info">No notifications found.</div>
			<?php endif; ?>
		</div>
	</div>
</div>

<script>
	$('.mark_read').click(function(){
		var id = $(this).attr('data-id');
		$.ajax({
			url:'ajax.php?action=mark_notification_read',
			method:'POST',
			data:{id:id},
			success:function(resp){
				if(resp == 1){
					location.reload();
				}
			}
		})
	})
</script>

