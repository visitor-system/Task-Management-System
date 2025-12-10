<?php 
include('db_connect.php');

// Fetch department details if editing
if(isset($_GET['id'])){
	$dept = $conn->query("SELECT * FROM departments WHERE id =".$_GET['id']);
	foreach($dept->fetch_array() as $k =>$v){
		$meta[$k] = $v;
	}
}

// Fetch users to populate department head dropdown
$users = $conn->query("SELECT * FROM users WHERE type = 2 ORDER BY firstname, lastname");
?>

<div class="container-fluid">
	<form action="" id="manage-department">	
		<input type="hidden" name="id" value="<?php echo isset($meta['id']) ? $meta['id']: '' ?>">
		
		<div class="form-group">
			<label for="name">Department Name</label>
			<input type="text" name="name" id="name" class="form-control" value="<?php echo isset($meta['name']) ? $meta['name']: '' ?>" required>
		</div>
		
		<div class="form-group">
			<label for="description">Description</label>
			<textarea name="description" id="description" class="form-control" rows="3"><?php echo isset($meta['description']) ? $meta['description']: '' ?></textarea>
		</div>
		
		<div class="form-group">
			<label for="head_id">Department Head</label>
			<select name="head_id" id="head_id" class="custom-select">
				<option value="">Select Department Head</option>
				<?php while($user = $users->fetch_assoc()): ?>
					<option value="<?php echo $user['id'] ?>" <?php echo isset($meta['head_id']) && $meta['head_id'] == $user['id'] ? 'selected' : '' ?>>
						<?php echo ucwords($user['firstname'].' '.$user['lastname']) ?>
					</option>
				<?php endwhile; ?>
			</select>
		</div>

		<!-- Small Save Button -->
		<div class="form-group mt-3">
			<button class="btn btn-primary btn-sm" type="submit">
				<i class="fas fa-save"></i> Save
			</button>
		</div>
	</form>
</div>

<script>
	$('#manage-department').submit(function(e){
		e.preventDefault();
		start_load();
		$.ajax({
			url:'ajax.php?action=save_department',
			data: new FormData($(this)[0]),
		    cache: false,
		    contentType: false,
		    processData: false,
		    method: 'POST',
		    success:function(resp){
				if(resp == 1){
					alert_toast("Data successfully saved",'success');
					setTimeout(function(){
						location.reload();
					},1500);
				}
			}
		})
	})
</script>