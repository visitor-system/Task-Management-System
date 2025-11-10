<?php
// Start session (if not already started)
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Task Management System</title>
    <link rel="stylesheet" href="styles.css"> <!-- You can link your external stylesheet -->
</head>
<body>

<!-- Navbar Section -->
<header>
    <nav class="navbar">
        <div class="navbar-logo">
            <!-- Logo (you can replace the image URL with your logo path) -->
            <img src="../includes/PDVS.jpg" alt="Logo" class="logo">
            <span class="system-name">Task Management System</span>
        </div>
        <ul class="navbar-links">
            <li><a href="dashboard.php">Dashboard</a></li>
            <li><a href="task_creation.php">Create Task</a></li>
            <li><a href="task_tracking.php">Track Tasks</a></li>
            <li><a href="reports.php">Reports</a></li>
            <li><a href="../pages/logout.php">Logout</a></li>
        </ul>
    </nav>
</header>

<!-- Add any other page content here -->

</body>
</html>

<style>
/* Basic styles for the header and navbar */
.navbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    background-color: #007bff;
    padding: 15px 30px;
    color: white;
}

.navbar-logo {
    display: flex;
    align-items: center;
}

.navbar-logo .logo {
    height: 40px; /* Adjust logo size */
    margin-right: 15px;
}

.navbar-logo .system-name {
    font-size: 1.6rem;
    font-weight: bold;
    color: white;
}

.navbar-links {
    display: flex;
    gap: 20px;
}

.navbar-links li {
    list-style: none;
}

.navbar-links a {
    text-decoration: none;
    color: white;
    font-size: 1rem;
    padding: 8px 15px;
    border-radius: 5px;
    transition: background-color 0.3s;
}

.navbar-links a:hover {
    background-color: #0056b3;
}

.navbar-links a.active {
    background-color: #0056b3;
}
</style>
