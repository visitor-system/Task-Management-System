<?php
// Include the database connection
require_once('../includes/db.php');  // Update the path if necessary

// Example: Department-wise task summary
$result = $conn->query("SELECT assigned_to, COUNT(*) as task_count FROM tasks GROUP BY assigned_to");
?>

<?php include('../includes/header.php'); ?>

<div class="reports">
    <h1>Task Reports</h1>
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>User</th>
                    <th>Number of Tasks</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <?php
                    $user = $conn->query("SELECT name FROM users WHERE id = '{$row['assigned_to']}'")->fetch_assoc();
                    ?>
                    <tr>
                        <td><?= $user['name'] ?></td>
                        <td><?= $row['task_count'] ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Inline Styling for the Reports Page -->
<style>
    .reports {
        max-width: 1000px;
        margin: 30px auto;
        background-color: #fff;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    .reports h1 {
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
        color: #fff;
        font-weight: 600;
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

    /* Responsive Design */
    @media (max-width: 768px) {
        table, th, td {
            font-size: 0.9rem;
            padding: 10px;
        }
    }
</style>
