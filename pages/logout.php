<?php
session_start();

if (isset($_POST['confirm'])) {
    // User confirmed logout
    session_unset();
    session_destroy();
    header("Location: login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Logout Confirmation</title>
<style>
    body { font-family: Arial, sans-serif; display: flex; justify-content: center; align-items: center; height: 100vh; background: #f0f4f8; }
    .logout-box { background: #fff; padding: 40px; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); text-align: center; }
    .btn { padding: 10px 25px; border-radius: 8px; border: none; margin: 10px; cursor: pointer; font-weight: bold; }
    .btn-yes { background: #3b82f6; color: #fff; }
    .btn-no { background: #ef4444; color: #fff; }
</style>
</head>
<body>

<div class="logout-box">
    <h2>Are you sure you want to log out?</h2>
    <form method="post">
        <button type="submit" name="confirm" class="btn btn-yes">Yes, log me out</button>
        <button type="button" onclick="window.location.href='index.php';" class="btn btn-no">Cancel</button>
    </form>
</div>

</body>
</html>
