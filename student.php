
<?php
require_once "auth.php";
requireLogin();

if (($_SESSION["role"] ?? "") === "admin") {
    header("Location: dashboard.php");
    exit;
}

if (($_SESSION["role"] ?? "") !== "student") {
    http_response_code(403);
    exit("Access denied.");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Student Portal | StudentHub</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
      rel="stylesheet">
<link rel="stylesheet" href="assets/style.css">
</head>
<body class="login-page">
<div class="login-card">
    <div class="brand-icon">SH</div>
    <h2>Student Portal</h2>
    <p class="text-secondary">
        Welcome, <?= htmlspecialchars($_SESSION["username"]) ?>!
    </p>
    <div class="alert alert-info">
        You are logged in as a student. Administrative options are unavailable.
    </div>
    <a href="logout.php" class="btn btn-primary w-100">Logout</a>
</div>
</body>
</html>
