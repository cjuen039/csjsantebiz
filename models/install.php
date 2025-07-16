<?php
$errors = [];
$success = false;

// Step 1: Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db_host = $_POST['db_host'] ?? 'localhost';
    $db_user = $_POST['db_user'] ?? '';
    $db_pass = $_POST['db_pass'] ?? '';
    $db_name = $_POST['db_name'] ?? 'work_priorities';
    $admin_user = $_POST['admin_user'] ?? 'admin';
    $admin_pass = $_POST['admin_pass'] ?? '';

    // Test DB connection
    $mysqli = @new mysqli($db_host, $db_user, $db_pass);
    if ($mysqli->connect_error) {
        $errors[] = "Connection failed: " . $mysqli->connect_error;
    } else {
        // Create database
        if (!$mysqli->query("CREATE DATABASE IF NOT EXISTS `$db_name`")) {
            $errors[] = "Error creating database: " . $mysqli->error;
        } else {
            $mysqli->select_db($db_name);

            // Create tables
            $schema = file_get_contents(__DIR__ . '/Knowledgebase/schema.sql');
            if ($mysqli->multi_query($schema)) {
                do {
                    $mysqli->next_result();
                } while ($mysqli->more_results());
            } else {
                $errors[] = "Error importing schema: " . $mysqli->error;
            }

            // Insert admin user
            if ($admin_user && $admin_pass) {
                $hash = password_hash($admin_pass, PASSWORD_DEFAULT);
                $stmt = $mysqli->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
                $stmt->bind_param("ss", $admin_user, $hash);
                $stmt->execute();
                $stmt->close();
            }

            // Create config file
            $config = "<?php\n"
                . "\$db_host = '$db_host';\n"
                . "\$db_user = '$db_user';\n"
                . "\$db_pass = '$db_pass';\n"
                . "\$db_name = '$db_name';\n";
            file_put_contents(__DIR__ . '/config.php', $config);
            $success = true;
        }
        $mysqli->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Task Manager Installer</title>
    <link href="css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="container py-5">
    <h1 class="mb-4">📦 Task Manager Installation</h1>

    <?php if ($success): ?>
        <div class="alert alert-success">✅ Installation successful! You may now <a href="login.php">log in</a>.</div>
    <?php else: ?>
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <strong>Errors occurred:</strong>
                <ul><?php foreach ($errors as $e)
                    echo "<li>$e</li>"; ?></ul>
            </div>
        <?php endif; ?>

        <form method="post" class="card p-4">
            <h5>Database Configuration</h5>
            <div class="mb-3">
                <label>DB Host</label>
                <input type="text" name="db_host" class="form-control" value="localhost" required>
            </div>
            <div class="mb-3">
                <label>DB Username</label>
                <input type="text" name="db_user" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>DB Password</label>
                <input type="password" name="db_pass" class="form-control">
            </div>
            <div class="mb-3">
                <label>DB Name</label>
                <input type="text" name="db_name" class="form-control" value="work_priorities" required>
            </div>

            <h5 class="mt-4">Admin Account (optional)</h5>
            <div class="mb-3">
                <label>Admin Username</label>
                <input type="text" name="admin_user" class="form-control" value="admin">
            </div>
            <div class="mb-3">
                <label>Admin Password</label>
                <input type="password" name="admin_pass" class="form-control">
            </div>

            <button type="submit" class="btn btn-primary">Install Now</button>
        </form>
    <?php endif; ?>
</body>

</html>