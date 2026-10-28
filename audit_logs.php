
<?php
require_once "auth.php";
requireAdmin();

logAction($conn, "VIEW_AUDIT_LOGS", "Admin viewed audit logs");

$result = $conn->query(
    "SELECT a.id, a.action, a.description, a.created_at,
            u.username
     FROM audit_logs a
     LEFT JOIN users u ON a.user_id = u.id
     ORDER BY a.id DESC
     LIMIT 100"
);

function e($value): string {
    return htmlspecialchars((string) $value, ENT_QUOTES, "UTF-8");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Audit Logs | StudentHub</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
      rel="stylesheet">
<link rel="stylesheet" href="assets/style.css">
</head>
<body>
<div class="dashboard-layout">
    <aside class="sidebar">
        <div class="sidebar-brand">StudentHub<span>.</span></div>
        <div class="sidebar-label">MAIN MENU</div>
        <a class="menu-link" href="dashboard.php">▦ &nbsp; Dashboard</a>
        <a class="menu-link active" href="audit_logs.php">◷ &nbsp; Audit Logs</a>
        <div class="sidebar-label">ACCOUNT</div>
        <a class="menu-link" href="logout.php">↪ &nbsp; Logout</a>

        <div class="sidebar-bottom">
            <div class="avatar">A</div>
            <div>
                <strong><?= e($_SESSION["username"]) ?></strong>
                <small>Administrator</small>
            </div>
        </div>
    </aside>

    <main class="main-content">
        <header class="topbar">
            <div>
                <h4>Audit Logs</h4>
                <p>Track important administrative activities.</p>
            </div>
            <span class="role-badge">ADMIN</span>
        </header>

        <section class="table-card">
            <div class="table-heading">
                <div>
                    <h5>Activity History</h5>
                    <p>Latest 100 recorded activities</p>
                </div>
                <a href="dashboard.php" class="btn btn-primary btn-sm">
                    Back to Dashboard
                </a>
            </div>

            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>User</th>
                            <th>Action</th>
                            <th>Description</th>
                            <th>Date & Time</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if ($result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?= (int) $row["id"] ?></td>
                                <td><?= e($row["username"] ?? "Deleted user") ?></td>
                                <td><span class="role-badge">
                                    <?= e($row["action"]) ?>
                                </span></td>
                                <td><?= e($row["description"]) ?></td>
                                <td><?= e($row["created_at"]) ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="5">No activity recorded yet.</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>
</div>
</body>
</html>
