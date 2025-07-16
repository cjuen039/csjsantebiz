<?php
session_start();
$mysqli = new mysqli("localhost", "root", "", "work_priorities");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $title = $_POST['title'];
    $description = $_POST['description'] ?? '';
    $due_date = $_POST['due_date'] ?? NULL;
    $quadrant = $_POST['quadrant'];

    $stmt = $mysqli->prepare("INSERT INTO tasks (user_id, title, description, due_date, quadrant, status) VALUES (?, ?, ?, ?, ?, 'pending')");
    $stmt->bind_param("isssi", $user_id, $title, $description, $due_date, $quadrant);
    $stmt->execute();

    header("Location: /tasksmngr/public/dashboard.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Add Task</title>
    <link rel="icon" href="/tasksmngr/images/taskmngr_logo.png" type="image/x-icon">
    <link href="/tasksmngr/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">
    <div class="container py-5">
        <h2 class="mb-4">Add New Task</h2>
        <form action="/tasksmngr/controllers/add_task.php" method="POST">
            <div class="mb-3">
                <label for="title" class="form-label">Task Title</label>
                <input type="text" class="form-control" name="title" required>
            </div>
            <div class="mb-3">
                <label for="description" class="form-label">Description</label>
                <textarea class="form-control" name="description" rows="3"></textarea>
            </div>
            <div class="mb-3">
                <label for="due_date" class="form-label">Due Date</label>
                <input type="date" class="form-control" name="due_date">
            </div>
            <div class="mb-3">
                <label for="quadrant" class="form-label">Set Priorities</label>
                <select class="form-select" name="quadrant" required>
                    <option value="1">Urgent & Important</option>
                    <option value="2">Not Urgent but Important</option>
                    <option value="3">Urgent but Not Important</option>
                    <option value="4">Not Urgent & Not Important</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Save Task</button>
        </form>
    </div>
</body>

</html>