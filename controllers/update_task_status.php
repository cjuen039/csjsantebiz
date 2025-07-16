<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    echo "Unauthorized";
    exit();
}

$mysqli = new mysqli("localhost", "root", "", "work_priorities");
if ($mysqli->connect_error) {
    echo "Database connection error";
    exit();
}

$task_id = $_POST['task_id'] ?? null;
$user_id = $_SESSION['user_id'];

if (!$task_id || !is_numeric($task_id)) {
    echo "Invalid task ID";
    exit();
}

// Fetch task to move
$stmt = $mysqli->prepare("SELECT * FROM tasks WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $task_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "Task not found";
    exit();
}

$task = $result->fetch_assoc();

// Start transaction
$mysqli->begin_transaction();

try {
    // Insert into task_completed
    $insert = $mysqli->prepare("INSERT INTO task_completed (user_id, title, description, due_date, quadrant) VALUES (?, ?, ?, ?, ?)");
    $insert->bind_param("isssi", $user_id, $task['title'], $task['description'], $task['due_date'], $task['quadrant']);
    $insert->execute();

    // Delete from tasks
    $delete = $mysqli->prepare("DELETE FROM tasks WHERE id = ? AND user_id = ?");
    $delete->bind_param("ii", $task_id, $user_id);
    $delete->execute();

    $mysqli->commit();
    echo "Success: Task completed and moved.";
} catch (Exception $e) {
    $mysqli->rollback();
    echo "Error processing task: " . $e->getMessage();
}
?>