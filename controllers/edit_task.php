<?php
session_start();
$mysqli = new mysqli("localhost", "root", "", "work_priorities");

if (!isset($_SESSION['user_id'])) {
    header("Location: /tasksmngr/public/login.php");
    exit();
}

$task_id = $_GET['id'] ?? null;
$user_id = $_SESSION['user_id'];

if (!$task_id) {
    header("Location: /tasksmngr/public/dashboard.php");
    exit();
}

// Get existing task
$stmt = $mysqli->prepare("SELECT * FROM tasks WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $task_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
$task = $result->fetch_assoc();

if (!$task) {
    echo "Task not found.";
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $due_date = $_POST['due_date'];
    $quadrant = $_POST['quadrant'];

    $update = $mysqli->prepare("UPDATE tasks SET title = ?, description = ?, due_date = ?, quadrant = ? WHERE id = ? AND user_id = ?");
    $update->bind_param("sssiii", $title, $description, $due_date, $quadrant, $task_id, $user_id);
    $update->execute();

    header("Location: /tasksmngr/public/dashboard.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Edit Task</title>
    <link rel="icon" href="/tasksmngr/images/taskmngr_logo.png" type="image/x-icon">
    <link href="/tasksmngr/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">
    <div class="container py-5">
        <h2 class="mb-4">Edit Task</h2>
        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Title</label>
                <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($task['title']) ?>"
                    required>
            </div>
            <div class="mb-3">
                <label class="form-label">Description</label>
                <textarea name="description"
                    class="form-control"><?= htmlspecialchars($task['description']) ?></textarea>
            </div>
            <div class="mb-3">
                <label class="form-label">Due Date</label>
                <input type="date" name="due_date" class="form-control"
                    value="<?= htmlspecialchars($task['due_date']) ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Quadrant</label>
                <select name="quadrant" class="form-select" required>
                    <?php
                    $quadrants = [
                        1 => 'Urgent & Important',
                        2 => 'Not Urgent but Important',
                        3 => 'Urgent but Not Important',
                        4 => 'Not Urgent & Not Important'
                    ];
                    foreach ($quadrants as $key => $label) {
                        $selected = $task['quadrant'] == $key ? 'selected' : '';
                        echo "<option value=\"$key\" $selected>$label</option>";
                    }
                    ?>
                </select>
            </div>
            <button type="submit" class="btn btn-success">Save Changes</button>
            <a href="/tasksmngr/public/dashboard.php" class="btn btn-secondary ms-2">Cancel</a>
        </form>
    </div>
</body>

</html>