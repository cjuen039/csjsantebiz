<?php
session_start();
$mysqli = new mysqli("localhost", "root", "", "work_priorities");

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $confirm = $_POST['confirm_password'];
    $security_question = trim($_POST['security_question']);
    $security_answer = trim($_POST['security_answer']);

    if (empty($username) || empty($password) || empty($security_question) || empty($security_answer)) {
        $error = "All fields are required.";
    } elseif ($password !== $confirm) {
        $error = "Passwords do not match.";
    } else {
        $stmt = $mysqli->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $error = "Username already exists.";
        } else {
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            $answer_hash = password_hash($security_answer, PASSWORD_DEFAULT);

            $insert = $mysqli->prepare("INSERT INTO users (username, password_hash, security_question, security_answer_hash) VALUES (?, ?, ?, ?)");
            $insert->bind_param("ssss", $username, $password_hash, $security_question, $answer_hash);

            if ($insert->execute()) {
                $_SESSION['user_id'] = $insert->insert_id;
                $_SESSION['username'] = $username;
                header("Location: /tasksmngr/public/dashboard.php");
                exit();
            } else {
                $error = "Registration failed. Please try again.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Register - Work Priorities</title>
    <link rel="icon" href="/tasksmngr/images/taskmngr_logo.png" type="image/x-icon">
    <link href="/tasksmngr/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background-image: url('/tasksmngr/images/work-schedule-bg.jpg');
            background-size: cover;
        }

        .card {
            background-color: rgba(255, 255, 255, 0.95);
        }
    </style>

</head>

<body class="bg-light d-flex align-items-center" style="min-height: 100vh;">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="card shadow-sm">
                    <div class="card-body p-4">
                        <h3 class="text-center mb-3">Create Account</h3>
                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                        <?php endif; ?>
                        <form method="POST" novalidate>
                            <div class="mb-3">
                                <label for="username" class="form-label">Username</label>
                                <input type="text" name="username" id="username" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" name="password" id="password" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label for="confirm_password" class="form-label">Confirm Password</label>
                                <input type="password" name="confirm_password" id="confirm_password"
                                    class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label for="security_question" class="form-label">Security Question</label>
                                <select name="security_question" id="security_question" class="form-select" required>
                                    <option value="">Select a question...</option>
                                    <option value="What was your childhood nickname?">What was your childhood nickname?
                                    </option>
                                    <option value="What is the name of your favorite childhood friend?">What is the name
                                        of your favorite childhood friend?</option>
                                    <option value="What is your mother’s maiden name?">What is your mother’s maiden
                                        name?</option>
                                    <option value="What is the name of your first pet?">What is the name of your first
                                        pet?</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="security_answer" class="form-label">Security Answer</label>
                                <input type="text" name="security_answer" id="security_answer" class="form-control"
                                    required>
                            </div>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-success">Register</button>
                            </div>
                            <p class="text-center mt-3">Already have an account? <a
                                    href="/tasksmngr/public/login.php">Login here</a></p>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>