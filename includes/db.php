<?php
// Database configuration for XAMPP
$host = "localhost";    // MySQL host
$user = "root";         // Default XAMPP MySQL user
$pass = "";             // Default XAMPP MySQL password is empty
$dbname = "task_management_system";   // Your database name

// Create connection
$conn = new mysqli($host, $user, $pass, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Optional: set charset to utf8
$conn->set_charset("utf8");

// Optionally, set the timezone if your application deals with dates
date_default_timezone_set('Asia/Kolkata');
?>
