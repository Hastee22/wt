
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once "db.php";

function requireLogin(): void {
    if (!isset($_SESSION["user_id"])) {
        header("Location: index.php");
        exit;
    }
}

function requireAdmin(): void {
    requireLogin();

    if (($_SESSION["role"] ?? "") !== "admin") {
        http_response_code(403);
        exit(
            '<h2>Access Denied</h2>
             <p>Only administrators can access this page.</p>
             <a href="student.php">Go to Student Page</a>'
        );
    }
}

function logAction(
    mysqli $conn,
    string $action,
    string $description
): void {
    if (!isset($_SESSION["user_id"])) {
        return;
    }

    $userId = (int) $_SESSION["user_id"];

    $stmt = $conn->prepare(
        "INSERT INTO audit_logs (user_id, action, description)
         VALUES (?, ?, ?)"
    );

    $stmt->bind_param(
        "iss",
        $userId,
        $action,
        $description
    );

    $stmt->execute();
}
?>
