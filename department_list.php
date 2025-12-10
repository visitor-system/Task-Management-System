<?php include'db_connect.php' ?>
<div class="col-lg-12">
	<div class="card card-outline card-success">
		<div class="card-header">
			<div class="card-tools">
				<a class="btn btn-block btn-sm btn-default btn-flat border-primary" href="./index.php?page=manage_department"><i class="fa fa-plus"></i> Add New Department</a>
			</div>
		</div>
		<div class="card-body">
			<table class="table tabe-hover table-condensed" id="list">
				<thead>
					<tr>
						<th class="text-center">#</th>
						<th>Department Name</th>
						<th>Description</th>
						<th>Department Head</th>
						<th>Action</th>
					</tr>
				</thead>
				<tbody>
					<?php
					$i = 1;
					$qry = $conn->query("SELECT d.*, CONCAT(u.firstname,' ',u.lastname) as head_name FROM departments d LEFT JOIN users u ON u.id = d.head_id ORDER BY d.name asc");
					while($row= $qry->fetch_assoc()):
					?>
					<tr>
						<td class="text-center"><?php echo $i++ ?></td>
						<td><b><?php echo ucwords($row['name']) ?></b></td>
						<td><?php echo $row['description'] ?></td>
						<td><?php echo $row['head_name'] ? ucwords($row['head_name']) : '<span class="text-muted">Not Assigned</span>' ?></td>
						<td class="text-center">
							<button type="button" class="btn btn-default btn-sm btn-flat border-info wave-effect text-info dropdown-toggle" data-toggle="dropdown" aria-expanded="true">
		                      Action
		                    </button>
		                    <div class="dropdown-menu" style="">
		                      <a class="dropdown-item" href="./index.php?page=manage_department&id=<?php echo $row['id'] ?>">Edit</a>
		                      <div class="dropdown-divider"></div>
		                      <a class="dropdown-item delete_department" href="javascript:void(0)" data-id="<?php echo $row['id'] ?>">Delete</a>
		                    </div>
						</td>
					</tr>	
				<?php endwhile; ?>
				</tbody>
			</table>
		</div>
	</div>
</div>
<script>
	$(document).ready(function(){
		$('#list').dataTable()
	$('.delete_department').click(function(){
		_conf("Are you sure to delete this department?","delete_department",[$(this).attr('data-id')])
	})
	})
	function delete_department($id){
		start_load()
		$.ajax({
			url:'ajax.php?action=delete_department',
			method:'POST',
			data:{id:$id},
			success:function(resp){
				if(resp==1){
					alert_toast("Data successfully deleted",'success')
					setTimeout(function(){
						location.reload()
					},1500)
				}
			}
		})
	}
</script>

