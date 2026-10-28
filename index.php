
<?php
session_start();
require_once "db.php";

if (isset($_SESSION["user_id"])) {
    if ($_SESSION["role"] === "admin") {
        header("Location: dashboard.php");
    } else {
        header("Location: student.php");
    }
    exit;
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST["username"] ?? "");
    $password = $_POST["password"] ?? "";

    $stmt = $conn->prepare(
        "SELECT id, username, password, role
         FROM users WHERE username = ?"
    );

    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user && password_verify($password, $user["password"])) {
        session_regenerate_id(true);

        $_SESSION["user_id"] = (int) $user["id"];
        $_SESSION["username"] = $user["username"];
        $_SESSION["role"] = $user["role"];

        if ($user["role"] === "admin") {
            $log = $conn->prepare(
                "INSERT INTO audit_logs
                 (user_id, action, description)
                 VALUES (?, 'LOGIN', 'Admin logged in')"
            );
            $log->bind_param("i", $user["id"]);
            $log->execute();

            header("Location: dashboard.php");
        } else {
            header("Location: student.php");
        }
        exit;
    } else {
        $error = "Invalid username or password.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Admin Portal | StudentHub</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
      rel="stylesheet">
<link rel="stylesheet" href="assets/style.css">
</head>
<body class="login-page">
<div class="login-card">
    <div class="brand-icon">SH</div>
    <h2>StudentHub</h2>
    <p class="text-secondary">Sign in to your account</p>

    <?php if ($error !== ""): ?>
        <div class="alert alert-danger">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <form method="POST">
        <label class="form-label">Username</label>
        <input type="text" name="username" class="form-control mb-3"
               placeholder="Enter username" required>

        <label class="form-label">Password</label>
        <input type="password" name="password"
               class="form-control mb-4"
               placeholder="Enter password" required>

        <button type="submit" class="btn btn-primary w-100">
            Sign In
        </button>
    </form>

    <div class="demo-note">
        Demo: admin / admin123
    </div>
</div>
</body>
</html>
