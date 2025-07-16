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

// Automatically mark expired tasks
$mysqli->query("UPDATE tasks SET status = 'expired' WHERE due_date < CURDATE() AND status = 'pending'");

// Fetch tasks by quadrant
function getTasks($quadrant)
{
    global $mysqli;
    $user_id = $_SESSION['user_id'];
    $stmt = $mysqli->prepare("SELECT * FROM tasks WHERE quadrant = ? AND user_id = ? ORDER BY due_date ASC");
    $stmt->bind_param("ii", $quadrant, $user_id);
    $stmt->execute();
    return $stmt->get_result();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Work Priorities Matrix</title>
    <link rel="icon" href="/tasksmngr/images/taskmngr_logo.png" type="image/x-icon">
    <link href="/tasksmngr/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-image: url('/tasksmngr/images/work-schedule-bg.jpg');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
        }

        .overlay {
            backdrop-filter: blur(5px);
            background-color: rgba(255, 255, 255, 0.9);
            padding: 2rem;
            border-radius: 15px;
        }

        .quadrant-card {
            height: 360px;
            display: flex;
            flex-direction: column;
        }

        .quadrant-card .list-group {
            overflow-y: auto;
            flex: 1;
            scrollbar-width: thin;
            scrollbar-color: #ccc transparent;
        }

        .quadrant-card .list-group::-webkit-scrollbar {
            width: 6px;
        }

        .quadrant-card .list-group::-webkit-scrollbar-thumb {
            background-color: rgba(0, 0, 0, 0.2);
            border-radius: 10px;
        }

        .quadrant-card .list-group-item {
            word-wrap: break-word;
        }

        .header-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .user-info {
            font-size: 0.95rem;
        }

        @keyframes shake {
            0% {
                transform: rotate(0deg);
            }

            25% {
                transform: rotate(-15deg);
            }

            50% {
                transform: rotate(15deg);
            }

            75% {
                transform: rotate(-10deg);
            }

            100% {
                transform: rotate(0deg);
            }
        }

        .shake-reminder {
            animation: shake 1s infinite;
        }
    </style>
</head>

<body>
    <div class="container py-5 overlay my-5">
        <div class="header-bar mb-4">
            <div>
                <h1 class="mb-1"><b>Work Priorities Task Manager</b></h1>
                <p class="text-muted mb-0">
                    The distance between you and your goals is narrowed by the little steps you take in your everyday
                    life.
                </p>
            </div>
            <div class="text-end user-info">
                Logged in as <strong><?= htmlspecialchars($_SESSION['username'] ?? 'User') ?></strong><br>
                <a href="/tasksmngr/public/logout.php" class="btn btn-sm btn-outline-secondary mt-2">Logout</a>
            </div>
        </div>

        <div class="mb-4 text-end">
            <a href="/tasksmngr/controllers/add_task.php" class="btn btn-primary">+ Add Task</a>
            <a href="/tasksmngr/views/completed_tasks.php" class="btn btn-outline-success">View Completed Tasks</a>
        </div>

        <div class="row g-4">
            <?php
            $quadrants = [
                1 => 'Urgent & Important',
                2 => 'Not Urgent but Important',
                3 => 'Urgent but Not Important',
                4 => 'Not Urgent & Not Important'
            ];
            foreach ($quadrants as $q => $title):
                $tasks = getTasks($q);
                ?>
                <div class="col-md-6">
                    <div class="card quadrant-card shadow">
                        <div class="card-header bg-<?=
                            $q == 1 ? 'danger' :
                            ($q == 2 ? 'success' :
                                ($q == 3 ? 'warning' : 'secondary')) ?> text-white">
                            <strong><?= $title ?></strong>
                        </div>
                        <ul class="list-group list-group-flush">
                            <?php if ($tasks->num_rows > 0): ?>
                                <?php while ($task = $tasks->fetch_assoc()): ?>
                                    <li class="list-group-item task-item d-flex justify-content-between align-items-start"
                                        data-title="<?= htmlspecialchars($task['title']) ?>"
                                        data-due="<?= htmlspecialchars($task['due_date']) ?>">

                                        <div class="flex-grow-1">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <strong><?= htmlspecialchars($task['title']) ?></strong>
                                                <span class="badge bg-<?=
                                                    $task['status'] === 'completed' ? 'success' :
                                                    ($task['status'] === 'expired' ? 'danger' : 'secondary') ?>">
                                                    <?= ucfirst($task['status']) ?>
                                                </span>
                                            </div>
                                            <small><?= htmlspecialchars($task['description']) ?></small><br>
                                            <small class="text-muted">Due: <?= htmlspecialchars($task['due_date']) ?></small>

                                            <div class="d-flex flex-column flex-md-row gap-2 mt-2">
                                                <form class="complete-form" data-task-id="<?= $task['id'] ?>">
                                                    <input type="hidden" name="task_id" value="<?= $task['id'] ?>">
                                                    <input type="hidden" name="status" value="completed">
                                                    <button type="submit" class="btn btn-sm btn-outline-success">
                                                        Mark Completed
                                                    </button>
                                                </form>
                                                <a href="/tasksmngr/controllers/edit_task.php?id=<?= $task['id'] ?>"
                                                    class="btn btn-sm btn-outline-primary">
                                                    Edit
                                                </a>
                                            </div>
                                        </div>

                                        <button class="btn btn-outline-warning btn-sm ms-2 reminder-btn" title="Show Reminder">
                                            🔔
                                        </button>
                                    </li>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <li class="list-group-item text-muted">No tasks yet.</li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Completion Success Modal -->
    <div class="modal fade" id="completionModal" tabindex="-1" aria-labelledby="completionModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title" id="completionModalLabel">Task Completed</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    The task was marked as completed successfully.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-success" data-bs-dismiss="modal">OK</button>
                </div>
            </div>
        </div>
    </div>
    <!-- Reminder Modal -->
    <div class="modal fade" id="reminderModal" tabindex="-1" aria-labelledby="reminderModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title" id="reminderModalLabel">Task Reminder</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="reminderModalBody">
                    <!-- Populated by JavaScript -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-warning" data-bs-dismiss="modal">Got it</button>
                </div>
            </div>
        </div>
    </div>
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const completionModal = new bootstrap.Modal(document.getElementById('completionModal'));
            const reminderModal = new bootstrap.Modal(document.getElementById('reminderModal'));
            const reminderModalBody = document.getElementById('reminderModalBody');

            const today = new Date();
            today.setHours(0, 0, 0, 0);
            const oneDayAhead = new Date(today);
            oneDayAhead.setDate(today.getDate() + 1);

            // Reminder and shaking bell
            document.querySelectorAll(".task-item").forEach(taskItem => {
                const title = taskItem.dataset.title;
                const dueDateStr = taskItem.dataset.due;
                const dueDate = new Date(dueDateStr);
                dueDate.setHours(0, 0, 0, 0);

                const today = new Date();
                today.setHours(0, 0, 0, 0);

                const oneDayBefore = new Date(dueDate);
                oneDayBefore.setDate(dueDate.getDate() - 1);

                const reminderBtn = taskItem.querySelector(".reminder-btn");

                // Add shaking if today is between 1 day before and due date
                if (today.getTime() >= oneDayBefore.getTime() && today.getTime() <= dueDate.getTime()) {
                    reminderBtn.classList.add("shake-reminder");
                }
                // Show reminder modal on click
                reminderBtn.addEventListener("click", () => {
                    const today = new Date();
                    today.setHours(0, 0, 0, 0);

                    const oneDayBefore = new Date(dueDate);
                    oneDayBefore.setDate(dueDate.getDate() - 1);

                    if (dueDate.getTime() === today.getTime()) {
                        // Task is due today
                        reminderModalBody.innerHTML = `<p>📌 "<strong>${title}</strong>" is <strong>due today</strong> (${dueDateStr}).</p>`;
                    } else if (today.getTime() === oneDayBefore.getTime()) {
                        // Task is due tomorrow
                        reminderModalBody.innerHTML = `<p>⏰ "<strong>${title}</strong>" is due <strong>tomorrow</strong> (${dueDateStr}).</p>`;
                    } else {
                        // Task is due on another day
                        reminderModalBody.innerHTML = `<p>This task is due on <strong>${dueDateStr}</strong>.</p>`;
                    }

                    reminderModal.show();
                });
            });

            // Task completion
            document.querySelectorAll(".complete-form").forEach(form => {
                form.addEventListener("submit", function (e) {
                    e.preventDefault();
                    const formData = new FormData(form);
                    fetch("/tasksmngr/controllers/update_task_status.php", {
                        method: "POST",
                        body: formData
                    })
                        .then(res => res.text())
                        .then(response => {
                            if (response.toLowerCase().includes("success")) {
                                const taskItem = form.closest(".task-item");
                                taskItem.remove();
                                completionModal.show();
                            } else {
                                alert("Failed to update task. Please refresh and try again.");
                                console.error("Response:", response);
                            }
                        })
                        .catch(err => {
                            console.error("Error updating task:", err);
                            alert("Error marking task as completed.");
                        });
                });
            });
        });
    </script>
    <script src="/tasksmngr/js/bootstrap.bundle.min.js"></script>
</body>

</html>