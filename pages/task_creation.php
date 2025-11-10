<?php
require_once('../includes/db.php');
include('../includes/header.php');

// Include PHPMailer
require '../PHPMailer/src/PHPMailer.php';
require '../PHPMailer/src/Exception.php';
require '../PHPMailer/src/SMTP.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// ===== Add User via AJAX =====
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['new_user_name'])) {
    $new_user_name = trim($_POST['new_user_name']);
    if ($new_user_name !== '') {
        $email = '';
        $stmt = $conn->prepare("INSERT INTO users (name, email) VALUES (?, ?)");
        $stmt->bind_param("ss", $new_user_name, $email);
        if ($stmt->execute()) {
            echo $conn->insert_id; // return new user ID
        } else {
            echo "error"; // return error if failed
        }
    } else {
        echo "empty";
    }
    exit;
}

// ===== Handle Task Creation =====
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['new_user_name'])) {
    $title       = trim($_POST['title']);
    $description = trim($_POST['description']);
    $priority    = $_POST['priority'];
    $start_date  = $_POST['start_date'];
    $due_date    = $_POST['due_date'];
    $assigned_to = (int)$_POST['assigned_to'];
    $department  = (int)$_POST['department'];
    $project     = $_POST['project'];
    $category    = $_POST['category'];
    $type        = $_POST['type'];
    $email       = trim($_POST['email']);
    $reminder    = $_POST['reminder'];
    $attachments = $_FILES['attachments'];

    $error_message = "";

    // Validate assigned user
    $stmt_check_user = $conn->prepare("SELECT id FROM users WHERE id = ?");
    $stmt_check_user->bind_param("i", $assigned_to);
    $stmt_check_user->execute();
    if ($stmt_check_user->get_result()->num_rows === 0) {
        $error_message = "Invalid user selected for assignment.";
    }

    // Validate department
    $stmt_check_dept = $conn->prepare("SELECT id FROM departments WHERE id = ?");
    $stmt_check_dept->bind_param("i", $department);
    $stmt_check_dept->execute();
    if ($stmt_check_dept->get_result()->num_rows === 0) {
        $error_message = "Invalid department selected.";
    }

    // Handle file upload
    $attachment_path = "";
    if (isset($attachments) && $attachments['error'] === 0) {
        $upload_dir = '../uploads/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
        $attachment_path = $upload_dir . basename($attachments['name']);
        if (!move_uploaded_file($attachments['tmp_name'], $attachment_path)) {
            $error_message = "Failed to upload attachment.";
        }
    }

    if ($error_message === "") {
        $stmt = $conn->prepare("INSERT INTO tasks 
            (title, description, priority, start_date, due_date, assigned_to, department, project, category, type, status, attachments, reminder, email) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Not Started', ?, ?, ?)");
        $stmt->bind_param("ssssiiissssss", $title, $description, $priority, $start_date, $due_date, $assigned_to, $department, $project, $category, $type, $attachment_path, $reminder, $email);

        if ($stmt->execute()) {
            // Send email
            if (!empty($email)) {
                $mail = new PHPMailer(true);
                try {
                    $mail->isSMTP();
                    $mail->Host       = 'smtp.gmail.com';
                    $mail->SMTPAuth   = true;
                    $mail->Username   = 'sanikaparitkar@gmail.com'; // your Gmail
                    $mail->Password   = 'exmvkqusuizgbenf';   // Gmail App Password
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                    $mail->Port       = 587;

                    $mail->setFrom('sanikaparitkar@gmail.com', 'Task Management System');
                    $mail->addAddress($email);

                    if (!empty($attachment_path) && file_exists($attachment_path)) {
                        $mail->addAttachment($attachment_path);
                    }

                    $mail->isHTML(true);
                    $mail->Subject = "📋 New Task Assigned: $title";
                    $mail->Body = "<h2>New Task: $title</h2>
                                   <p><strong>Project:</strong> $project</p>
                                   <p><strong>Department:</strong> $department</p>
                                   <p><strong>Priority:</strong> $priority</p>
                                   <p><strong>Category:</strong> $category</p>
                                   <p><strong>Type:</strong> $type</p>
                                   <p><strong>Start Date:</strong> $start_date</p>
                                   <p><strong>Due Date:</strong> $due_date</p>
                                   <p><strong>Description:</strong><br>$description</p>
                                   <p><strong>Reminder:</strong> $reminder</p>";
                    $mail->AltBody = "Task: $title\nProject: $project\nDepartment: $department\nPriority: $priority\nCategory: $category\nType: $type\nStart: $start_date\nDue: $due_date\nDescription:\n$description\nReminder: $reminder";

                    $mail->send();
                    echo "<script>alert('Task created and email sent!'); window.location='';</script>";
                    exit;
                } catch (Exception $e) {
                    echo "<script>alert('Task created, but email could not be sent.');</script>";
                }
            } else {
                echo "<script>alert('Task created successfully!'); window.location='';</script>";
                exit;
            }
        } else {
            echo "<script>alert('Failed to create task: " . $stmt->error . "');</script>";
        }
    } else {
        echo "<script>alert('".$error_message."');</script>";
    }
}
?>

<div class="task-creation-container">
    <h1>📝 Create New Task</h1>
    <form id="task-form" method="POST" action="" enctype="multipart/form-data">
        <!-- Row 1: Title & Department -->
        <div class="form-row">
            <div class="form-group">
                <label for="title">Task Title</label>
                <input type="text" name="title" id="title" placeholder="Enter task title..." required>
            </div>
            <div class="form-group">
                <label for="department">Department</label>
                <select name="department" id="department" required>
                    <option value="" disabled selected>-- Select Department --</option>
                    <?php
                    $departments = $conn->query("SELECT id, name FROM departments");
                    while ($dept = $departments->fetch_assoc()) {
                        echo "<option value='{$dept['id']}'>{$dept['name']}</option>";
                    }
                    ?>
                </select>
            </div>
        </div>

        <!-- Row 2: Project & Priority -->
        <div class="form-row">
            <div class="form-group">
                <label for="project">Project</label>
                <select name="project" id="project" required>
                    <option value="" disabled selected>-- Select Project --</option>
                    <option value="Task Management System">Task Management System</option>
                    <option value="Visitor System">Visitor System</option>
                    <option value="ERP System">ERP System</option>
                </select>
            </div>
            <div class="form-group">
                <label for="priority">Priority</label>
                <select name="priority" id="priority" required>
                    <option value="" disabled selected>-- Select Priority --</option>
                    <option value="High">High</option>
                    <option value="Medium">Medium</option>
                    <option value="Low">Low</option>
                </select>
            </div>
        </div>

        <!-- Row 3: Category & Type -->
        <div class="form-row">
            <div class="form-group">
                <label for="category">Category</label>
                <select name="category" id="category" required>
                    <option value="" disabled selected>-- Select Category --</option>
                    <option value="General">General</option>
                    <option value="Go Set">Go Set</option>
                </select>
            </div>
            <div class="form-group">
                <label for="type">Type</label>
                <select name="type" id="type" required>
                    <option value="" disabled selected>-- Select Type --</option>
                    <option value="Personal Work">Personal Work</option>
                    <option value="Work">Work</option>
                </select>
            </div>
        </div>

        <!-- Row 4: Assigned To & Email -->
        <div class="form-row">
            <div class="form-group">
                <label for="assigned_to">Assign To</label>
                <div class="assigned-container">
                    <select name="assigned_to" id="assigned_to" required>
                        <option value="" disabled selected>Select a user</option>
                        <?php
                        $users = $conn->query("SELECT id, name FROM users");
                        while ($user = $users->fetch_assoc()) {
                            echo "<option value='{$user['id']}'>{$user['name']}</option>";
                        }
                        ?>
                    </select>
                    <button type="button" id="add_user_btn">Add User</button>
                </div>
            </div>
            <div class="form-group">
                <label for="email">Email for Reminder</label>
                <input type="email" name="email" id="email" placeholder="Enter email">
            </div>
        </div>

        <!-- Row 5: Start & Due Date -->
        <div class="form-row">
            <div class="form-group">
                <label for="start_date">Start Date</label>
                <input type="text" name="start_date" class="flatpickr" placeholder="Select start date">
            </div>
            <div class="form-group">
                <label for="due_date">Due Date</label>
                <input type="text" name="due_date" class="flatpickr" placeholder="Select due date">
            </div>
        </div>

        <!-- Row 6: Reminder & Attachments -->
        <div class="form-row">
            <div class="form-group">
                <label for="reminder">Reminder</label>
                <input type="text" name="reminder" class="flatpickr" placeholder="Set reminder">
            </div>
            <div class="form-group">
                <label for="attachments">Attachments</label>
                <input type="file" name="attachments" id="attachments">
            </div>
        </div>

        <!-- Row 7: Description -->
        <div class="form-row">
            <div class="form-group full-width">
                <label for="description">Description</label>
                <textarea name="description" id="description" placeholder="Enter task description..."></textarea>
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn-save">Save Task</button>
            <button type="reset" class="btn-cancel">Cancel</button>
        </div>
    </form>
</div>

<!-- Flatpickr & Add User JS -->
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script>
flatpickr(".flatpickr", { enableTime: true, dateFormat: "Y-m-d H:i", onChange: function(selectedDates, dateStr, instance){instance.close();} });

document.getElementById('add_user_btn').addEventListener('click', function(){
    const userName = prompt("Enter new user name:");
    if(!userName) return;
    
    const xhr = new XMLHttpRequest();
    xhr.open('POST','',true);
    xhr.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
    xhr.onreadystatechange = function(){
        if(xhr.readyState === 4){
            const response = xhr.responseText.trim();
            if(!isNaN(response)){ // success
                const option = document.createElement('option');
                option.value = response;
                option.textContent = userName;
                document.getElementById('assigned_to').appendChild(option);
                document.getElementById('assigned_to').value = response;
            } else {
                alert("Failed to add new user: " + response); // debug info
            }
        }
    };
    xhr.send('new_user_name=' + encodeURIComponent(userName));
});

</script>

<style>
/* Original design preserved */
.task-creation-container { max-width: 900px; margin: 40px auto; background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 6px 15px rgba(0,0,0,0.1);}
.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 15px;}
.form-group { display: flex; flex-direction: column;}
textarea { min-height: 80px; resize: vertical; }
input, textarea, select, button { padding: 10px; border-radius: 5px; border: 1px solid #dcdcdc;}
input:focus, textarea:focus, select:focus { border-color: #3498db; outline: none;}
.assigned-container { display: flex; gap: 10px;}
.assigned-container select { flex: 1;}
.assigned-container button { background: #3498db; color: #fff; border: none; cursor: pointer;}
.assigned-container button:hover { background: #217dbb;}
.btn-save { background: #3498db; color: #fff; border: none; cursor: pointer; padding: 10px 20px; border-radius: 5px;}
.btn-save:hover { background: #217dbb;}
.btn-cancel { background: #e74c3c; color: #fff; border: none; cursor: pointer; padding: 10px 20px; border-radius: 5px;}
.btn-cancel:hover { background: #c0392b;}
.form-group.full-width { grid-column: 1 / -1;}
@media (max-width: 768px) { .form-row { grid-template-columns: 1fr; }}
</style>