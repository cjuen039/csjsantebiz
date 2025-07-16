<?php
session_start();
$mysqli = new mysqli("localhost", "root", "", "work_priorities");

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST["username"]);
    $password = $_POST["password"];

    $stmt = $mysqli->prepare("SELECT id, password_hash FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->bind_result($id, $hash);

    if ($stmt->fetch() && password_verify($password, $hash)) {
        $_SESSION['user_id'] = $id;
        $_SESSION['username'] = $username;
        header("Location: /tasksmngr/public/dashboard.php");
        exit();
    } else {
        $error = "Invalid credentials.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Login - Work Priorities</title>
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
            background-color: rgba(255, 255, 255, 0.85);
            border-radius: 12px;
        }
    </style>
</head>

<body class="d-flex align-items-center" style="min-height: 100vh;">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-5">
                <div class="card shadow overlay">
                    <div class="card-body p-4">
                        <h3 class="text-center mb-4">Login</h3>
                        <?php if (!empty($error)): ?>
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
                            <div class="d-grid">
                                <button type="submit" class="btn btn-success">Login</button>
                            </div>
                        </form>
                        <div class="mt-3 text-center">
                            <a href="/tasksmngr/public/register.php">Don't have an account? Register</a>
                        </div>
                        <div class="mt-2 text-center small">
                            <a href="/tasksmngr/public/forgot.php">Forgot Password?</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>