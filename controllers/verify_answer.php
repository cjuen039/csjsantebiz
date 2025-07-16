<?php
session_start();
$mysqli = new mysqli("localhost", "root", "", "work_priorities");

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST["username"]);
    $answer = trim($_POST["security_answer"]);

    $stmt = $mysqli->prepare("SELECT id, security_question, security_answer FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($user = $result->fetch_assoc()) {
        if (strcasecmp($user['security_answer'], $answer) === 0) {
            // Answer is correct — allow password reset
            $_SESSION['reset_allowed'] = true;
            $_SESSION['recovery_user_id'] = $user['id'];
            header("Location: /tasksmngr/public/reset_password.php");
            exit();
        } else {
            $error = "Incorrect security answer.";
        }
    } else {
        $error = "Username not found.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title>Verify Security Answer</title>
    <link rel="icon" href="/tasksmngr/images/taskmngr_logo.png" type="image/x-icon">
    <link rel="stylesheet" href="/tasksmngr/css/bootstrap.min.css">
</head>

<body class="bg-light d-flex align-items-center" style="height: 100vh;">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="card shadow">
                    <div class="card-body">
                        <h4 class="card-title mb-3">Account Recovery</h4>
                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                        <?php endif; ?>
                        <form method="POST">
                            <div class="mb-3">
                                <label>Username</label>
                                <input type="text" name="username" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label>Security Answer</label>
                                <input type="text" name="security_answer" class="form-control" required>
                            </div>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">Verify</button>
                            </div>
                            <p class="mt-3 text-center">
                                <a href="/tasksmngr/public/login.php">Back to login</a>
                            </p>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>