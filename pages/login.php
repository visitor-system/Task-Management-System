<?php
// Start session
session_start();

// Handle login logic
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Example validation (replace this with actual DB check)
    if ($username == 'admin' && $password == 'admin123') {
        $_SESSION['user'] = ['name' => 'Host User', 'username' => $username];
        header('Location: index.php');
        exit();
    } else {
        $error = "Invalid username or password!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login - Task Management</title>
<link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
<style>
/* Reset and Global */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Roboto', sans-serif;
    min-height: 100vh;
    display: flex;
    justify-content: center;
    align-items: center;
    overflow: hidden;
    background: url('background.jpg') no-repeat center center/cover;
    position: relative;
}

/* Overlay for slight blur and darkening */
body::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.35); /* dark overlay */
    backdrop-filter: blur(3px);
    z-index: 0;
}

/* Glassmorphic login container */
.login-container {
    position: relative;
    z-index: 1;
    background: rgba(255, 255, 255, 0.3);
    backdrop-filter: blur(25px) saturate(180%);
    -webkit-backdrop-filter: blur(25px) saturate(180%);
    border: 1px solid rgba(255, 255, 255, 0.4);
    border-radius: 30px;
    box-shadow: 0 25px 70px rgba(0, 0, 0, 0.3);
    width: 550px; /* increased width */
    padding: 70px 60px; /* increased padding */
    text-align: center;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.login-container:hover {
    transform: translateY(-7px);
    box-shadow: 0 35px 90px rgba(0, 0, 0, 0.35);
}

.login-container h1 {
    margin-bottom: 40px;
    color: #ffffff;
    font-weight: 700;
    font-size: 36px; /* bigger heading */
    text-shadow: 0 3px 15px rgba(0,0,0,0.5);
}

/* Form Fields */
.form-group {
    margin-bottom: 30px;
    text-align: left;
}

.form-group label {
    display: block;
    margin-bottom: 12px;
    color: #f1f5f9;
    font-weight: 500;
    font-size: 18px; /* bigger label */
}

input {
    width: 100%;
    padding: 18px; /* bigger input */
    border-radius: 20px;
    border: none;
    background: rgba(255, 255, 255, 0.35);
    backdrop-filter: blur(12px);
    color: #1f2937;
    font-size: 18px; /* bigger font */
    transition: all 0.3s ease;
}

input:focus {
    outline: none;
    box-shadow: 0 0 30px rgba(255,255,255,0.5);
    background: rgba(255, 255, 255, 0.45);
}

/* Login Button */
.btn {
    width: 100%;
    padding: 18px; /* bigger button */
    border-radius: 20px;
    border: 1px solid rgba(255, 255, 255, 0.35);
    background: rgba(255, 255, 255, 0.3);
    backdrop-filter: blur(12px);
    color: #1e3a8a;
    font-weight: 700;
    font-size: 18px; /* bigger text */
    cursor: pointer;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    transition: all 0.3s ease;
    margin-top: 20px;
}

.btn:hover {
    background: rgba(255, 255, 255, 0.45);
    box-shadow: 0 15px 50px rgba(0, 0, 0, 0.35);
    transform: translateY(-4px);
}

/* Error Message */
.error {
    color: #ff6b6b;
    margin-top: 25px;
    font-weight: 500;
    font-size: 16px;
    text-shadow: 0 1px 3px rgba(0,0,0,0.3);
}

/* Responsive */
@media (max-width: 600px) {
    .login-container {
        width: 90%;
        padding: 50px 30px;
    }
    .login-container h1 {
        font-size: 28px;
    }
    input, .btn {
        font-size: 16px;
        padding: 14px;
    }
}
</style>
</head>
<body>

<!-- Login Form -->
<div class="login-container">
    <h1>Login</h1>
    <form action="login.php" method="POST">
        <div class="form-group">
            <label for="username">Username</label>
            <input type="text" name="username" id="username" required placeholder="Enter Username">
        </div>
        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" name="password" id="password" required placeholder="Enter Password">
        </div>
        <button type="submit" class="btn">Login</button>
    </form>
    <?php if (isset($error)) { echo "<div class='error'>$error</div>"; } ?>
</div>

</body>
</html>
