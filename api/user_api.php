<?php
// user_api.php
require_once '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Check for login credentials
    if (isset($_POST['username']) && isset($_POST['password'])) {
        $username = $_POST['username'];
        $password = $_POST['password']; // Password should be hashed for real-world use

        // Query the database for the user
        $stmt = $conn->prepare("SELECT id, name, password FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();

            // Verify password (assuming the password is hashed in the database)
            if (password_verify($password, $user['password'])) {
                // Start a session and store user details
                session_start();
                $_SESSION['user'] = $user['name'];
                $_SESSION['user_id'] = $user['id'];

                echo json_encode(['success' => true, 'message' => 'Login successful']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Incorrect password']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'User not found']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Username and password required']);
    }
}
?>
