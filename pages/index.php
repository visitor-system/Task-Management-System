<?php
// Start session
session_start();

// Include the database connection file
require_once('../includes/db.php');

// Fetch task counts
$user_id = $_SESSION['user_id'] ?? 0;

$tasks_count = [
    'total' => 0,
    'pending' => 0,
    'in_progress' => 0,
    'completed' => 0
];

$result = $conn->query("SELECT status, COUNT(*) as count FROM tasks WHERE assigned_to = '$user_id' GROUP BY status");
while ($row = $result->fetch_assoc()) {
    $status = strtolower(str_replace(' ', '_', $row['status']));
    $tasks_count[$status] = $row['count'];
}
$tasks_count['total'] = array_sum($tasks_count);
?>

<?php include('../includes/header.php'); ?>

<!-- Dashboard Content -->
<div class="dashboard-container">
    <div class="dashboard-header">
        <h1>Welcome !</h1>
        <p class="subheading">Efficient task management at your fingertips.</p>
    </div>

    <!-- Task Summary Section -->
    <div class="task-summary">
        <div class="task-card">
            <div class="task-card-title">Total Tasks</div>
            <div class="task-card-count"><?= $tasks_count['total'] ?></div>
        </div>
        <div class="task-card">
            <div class="task-card-title">Pending</div>
            <div class="task-card-count"><?= $tasks_count['pending'] ?></div>
        </div>
        <div class="task-card">
            <div class="task-card-title">In Progress</div>
            <div class="task-card-count"><?= $tasks_count['in_progress'] ?></div>
        </div>
        <div class="task-card">
            <div class="task-card-title">Completed</div>
            <div class="task-card-count"><?= $tasks_count['completed'] ?></div>
        </div>
    </div>
</div>

<!-- Inline CSS for Dashboard Page -->
<style>
    /* Dashboard container */
    .dashboard-container {
        background: #f4f7f9;
        padding: 30px;
        margin: 20px auto;
        border-radius: 12px;
        max-width: 1200px;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    }

    /* Header section */
    .dashboard-header {
        text-align: center;
        margin-bottom: 25px;
    }

    .dashboard-header h1 {
        font-size: 2rem;
        color: #34495e;
        margin-bottom: 10px;
        font-weight: 600;
    }

    .subheading {
        font-size: 1.1rem;
        color: #7f8c8d;
    }

    /* Task Summary Cards Layout */
    .task-summary {
        display: flex;
        justify-content: space-between;
        gap: 20px;
        flex-wrap: nowrap; /* Ensure cards stay in a single line */
    }

    .task-card {
        background: #3498db; /* Uniform color for all cards */
        color: #fff;
        border-radius: 8px;
        padding: 25px;
        text-align: center;
        width: 22%; /* Adjusted for all cards to be in one line */
        min-width: 240px;
        box-shadow: 0 6px 15px rgba(0, 0, 0, 0.08);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .task-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.1);
    }

    .task-card-title {
        font-size: 1.2rem;
        font-weight: 500;
        color: #fff;
    }

    .task-card-count {
        font-size: 2rem;
        font-weight: 700;
        color: #fff;
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .task-summary {
            flex-direction: row;
            align-items: center;
            overflow-x: auto; /* Enable horizontal scroll */
        }

        .task-card {
            width: 220px; /* Adjust card width on smaller screens */
        }
    }
</style>

</body>
</html>
