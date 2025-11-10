<?php
include "todo_database.php";

// Read POST data (JSON)
$data = json_decode(file_get_contents('php://input'), true);

if(isset($data['username']) && !empty(trim($data['username']))){
    $username = trim($data['username']);

    // Default values
    $email = strtolower(str_replace(' ', '', $username)) . "@example.com";
    $password = '123';
    $department_id = 1;
    $role_id = 3;

    // Check if username exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();
    if($stmt->num_rows > 0){
        echo json_encode(['status'=>'error','message'=>'User already exists']);
        exit;
    }

    // Insert new user
    $stmt = $conn->prepare("INSERT INTO users (username, email, password, department_id, role_id) VALUES (?,?,?,?,?)");
    $stmt->bind_param("sssii", $username, $email, $password, $department_id, $role_id);
    if($stmt->execute()){
        echo json_encode(['status'=>'success','id'=>$stmt->insert_id]);
    } else {
        echo json_encode(['status'=>'error','message'=>'Failed to add user']);
    }
} else {
    echo json_encode(['status'=>'error','message'=>'Username required']);
}
?>
