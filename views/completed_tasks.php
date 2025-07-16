<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: /tasksmngr/public/login.php");
    exit();
}

// Database connection
$mysqli = new mysqli("localhost", "root", "", "work_priorities");
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

// Fetch completed tasks from task_completed table by status and user
$user_id = $_SESSION['user_id'];
$status = 'completed';
$stmt = $mysqli->prepare("SELECT * FROM task_completed WHERE status = ? AND user_id = ? ORDER BY due_date DESC");
$stmt->bind_param("si", $status, $user_id);
$stmt->execute();
$tasks = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Completed Tasks</title>
    <link rel="icon" href="/tasksmngr/images/taskmngr_logo.png" type="image/x-icon">
    <link href="/tasksmngr/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="container py-5">
    <h1 class="mb-4">Completed Tasks</h1>
    <a href="/tasksmngr/public/dashboard.php" class="btn btn-secondary mb-3">← Back to Dashboard</a>

    <ul class="list-group">
        <?php if ($tasks->num_rows > 0): ?>
            <?php while ($task = $tasks->fetch_assoc()): ?>
                <li class="list-group-item">
                    <strong><?= htmlspecialchars($task['title']) ?></strong>
                    <span class="badge bg-success ms-2"><?= ucfirst($task['status']) ?></span><br>
                    <small><?= htmlspecialchars($task['description']) ?></small><br>
                    <small class="text-muted">Due: <?= htmlspecialchars($task['due_date']) ?></small>
                </li>
            <?php endwhile; ?>
        <?php else: ?>
            <li class="list-group-item text-muted">No completed tasks.</li>
        <?php endif; ?>
    </ul>

</body>

</html>