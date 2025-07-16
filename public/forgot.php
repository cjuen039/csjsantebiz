<?php
session_start();
$mysqli = new mysqli("localhost", "root", "", "work_priorities");

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['username'])) {
    $username = trim($_POST['username']);
    $stmt = $mysqli->prepare("SELECT id, security_question FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->bind_result($user_id, $security_question);

    if ($stmt->fetch()) {
        $_SESSION['recovery_user_id'] = $user_id;
        $_SESSION['recovery_username'] = $username;
        $_SESSION['security_question'] = $security_question;
        header("Location: /tasksmngr/controllers/verify_answer.php");
        exit();
    } else {
        $error = "Username not found.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title>Forgot Username/Password</title>
    <link rel="icon" href="/tasksmngr/images/taskmngr_logo.png" type="image/x-icon">
    <link rel="stylesheet" href="/tasksmngr/css/bootstrap.min.css">
</head>

<body class="bg-light d-flex align-items-center" style="height: 100vh;">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-5">
                <div class="card shadow">
                    <div class="card-body">
                        <h4 class="card-title text-center mb-4">Account Recovery</h4>
                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger">
                                <?= htmlspecialchars($error) ?>
                            </div>
                        <?php endif; ?>
                        <form method="POST">
                            <div class="mb-3">
                                <label>Enter Your Username</label>
                                <input type="text" name="username" class="form-control" required>
                            </div>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">Next</button>
                            </div>
                            <p class="mt-3 text-center"><a href="/tasksmngr/public/login.php">Back to Login</a></p>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>