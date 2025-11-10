<?php
// Include the database connection
require_once('../includes/db.php');  // Adjust the path if necessary

// Fetch user tasks based on the logged-in user
$user_id = $_SESSION['user_id'] ?? 0;
$tasks = $conn->query("SELECT * FROM tasks WHERE assigned_to = '$user_id'");
?>

<?php include('../includes/header.php'); ?>

<div class="task-tracking">
    <h1>Track Your Tasks</h1>
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Status</th>
                    <th>Priority</th>
                    <th>Deadline</th>
                    <th>Update Status</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($task = $tasks->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($task['title']) ?></td>
                        <td><?= htmlspecialchars($task['status']) ?></td>
                        <td><?= htmlspecialchars($task['priority']) ?></td>
                        <td><?= htmlspecialchars($task['deadline']) ?></td>
                        <td>
                            <select class="task-status" data-task-id="<?= $task['id'] ?>">
                                <?php
                                $statuses = ['Not Started', 'In Progress', 'Completed', 'On Hold'];
                                foreach ($statuses as $status) {
                                    $selected = ($task['status'] === $status) ? 'selected' : '';
                                    echo "<option value='$status' $selected>$status</option>";
                                }
                                ?>
                            </select>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Inline Styling for Task Tracking Page -->
<style>
    .task-tracking {
        max-width: 1000px;
        margin: 30px auto;
        padding: 20px;
        background-color: #ffffff;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    .task-tracking h1 {
        font-size: 2rem;
        color: #2c3e50;
        margin-bottom: 20px;
        text-align: center;
    }

    .table-container {
        overflow-x: auto;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
    }

    th, td {
        padding: 12px;
        text-align: left;
        border: 1px solid #ddd;
    }

    th {
        background-color: #3498db;
        color: white;
        font-weight: bold;
    }

    td {
        background-color: #f9f9f9;
    }

    tr:nth-child(even) td {
        background-color: #ecf0f1;
    }

    tr:hover {
        background-color: #f1f1f1;
    }

    select.task-status {
        padding: 6px 12px;
        font-size: 1rem;
        border: 1px solid #ddd;
        border-radius: 5px;
        width: 100%;
        max-width: 180px;
    }

    /* Responsive Design */
    @media (max-width: 768px) {
        table, th, td {
            font-size: 0.9rem;
            padding: 10px;
        }
    }
</style>

<!-- Inline JS for handling status update -->
<script>
    // Event listener for status update
    document.querySelectorAll('.task-status').forEach(select => {
        select.addEventListener('change', function() {
            const taskId = this.getAttribute('data-task-id');
            const newStatus = this.value;

            // AJAX request to update status
            const xhr = new XMLHttpRequest();
            xhr.open('POST', 'update_task_status.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.onreadystatechange = function() {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    alert('Task status updated successfully!');
                }
            };
            xhr.send('task_id=' + taskId + '&status=' + newStatus);
        });
    });
</script>
