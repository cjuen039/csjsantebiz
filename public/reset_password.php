<?php
session_start();
$mysqli = new mysqli("localhost", "root", "", "work_priorities");

if (!isset($_SESSION['reset_allowed']) || !isset($_SESSION['recovery_user_id'])) {
    header("Location: /tasksmngr/public/login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $newPass = password_hash(trim($_POST["password"]), PASSWORD_DEFAULT);
    $userId = $_SESSION['recovery_user_id'];

    $stmt = $mysqli->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
    $stmt->bind_param("si", $newPass, $userId);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        session_destroy();
        $success = "Password successfully updated. <a href='login.php'>Log in now</a>";
    } else {
        $error = "Failed to update password. Please try again.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title>Reset Password</title>
    <link rel="icon" href="/tasksmngr/images/taskmngr_logo.png" type="image/x-icon">
    <link rel="stylesheet" href="/tasksmngr/css/bootstrap.min.css">
</head>

<body class="bg-light d-flex align-items-center" style="height: 100vh;">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-5">
                <div class="card shadow">
                    <div class="card-body">
                        <h4 class="card-title mb-4">Reset Your Password</h4>
                        <?php if (isset($success)): ?>
                            <div class="alert alert-success"><?= $success ?></div>
                        <?php elseif (isset($error)): ?>
                            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                        <?php else: ?>
                            <form method="POST">
                                <div class="mb-3">
                                    <label>New Password</label>
                                    <input type="password" name="password" class="form-control" required>
                                </div>
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-success">Reset Password</button>
                                </div>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>