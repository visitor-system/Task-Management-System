// Document Ready Function
document.addEventListener("DOMContentLoaded", function() {

    // Handle form submissions for task creation (validation example)
    const taskForm = document.querySelector('#task-form');
    if (taskForm) {
        taskForm.addEventListener('submit', function(event) {
            const title = document.querySelector('input[name="title"]').value;
            const description = document.querySelector('textarea[name="description"]').value;
            const priority = document.querySelector('select[name="priority"]').value;
            const deadline = document.querySelector('input[name="deadline"]').value;
            const assignedTo = document.querySelector('select[name="assigned_to"]').value;

            if (!title || !description || !priority || !deadline || !assignedTo) {
                event.preventDefault(); // Prevent form submission if validation fails
                alert('Please fill in all fields.');
            }
        });
    }

    // Handle task status change
    const statusSelects = document.querySelectorAll('.task-status');
    statusSelects.forEach(select => {
        select.addEventListener('change', function(event) {
            const taskId = event.target.dataset.taskId;
            const newStatus = event.target.value;
            updateTaskStatus(taskId, newStatus);
        });
    });

    // Function to update task status via AJAX
    function updateTaskStatus(taskId, status) {
        const xhr = new XMLHttpRequest();
        xhr.open('POST', '/api/task_api.php', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.onreadystatechange = function() {
            if (xhr.readyState === 4 && xhr.status === 200) {
                const response = JSON.parse(xhr.responseText);
                if (response.success) {
                    alert('Task status updated successfully');
                } else {
                    alert('Failed to update task status');
                }
            }
        };
        xhr.send('task_id=' + taskId + '&status=' + status);
    }

    // Task creation success message
    const successMessage = document.querySelector('.success-message');
    if (successMessage) {
        setTimeout(() => {
            successMessage.style.display = 'none';
        }, 3000); // Hide after 3 seconds
    }

});
