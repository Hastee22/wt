
<?php
require_once "auth.php";
requireAdmin();

logAction($conn, "VIEW_DASHBOARD", "Admin opened dashboard");

function getCount(mysqli $conn, string $table): int {
    $allowed = [
        "users", "students", "events", "registrations"
    ];

    if (!in_array($table, $allowed, true)) {
        return 0;
    }

    $result = $conn->query("SELECT COUNT(*) AS total FROM `$table`");
    return (int) $result->fetch_assoc()["total"];
}

$totalUsers = getCount($conn, "users");
$totalStudents = getCount($conn, "students");
$totalEvents = getCount($conn, "events");
$totalRegistrations = getCount($conn, "registrations");

$latest = $conn->query(
    "SELECT r.id, s.name AS student_name,
            e.title AS event_title, r.created_at
     FROM registrations r
     JOIN students s ON r.student_id = s.id
     JOIN events e ON r.event_id = e.id
     ORDER BY r.created_at DESC, r.id DESC
     LIMIT 5"
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
<title>Admin Dashboard | StudentHub</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
      rel="stylesheet">
<link rel="stylesheet" href="assets/style.css">
</head>
<body>
<div class="dashboard-layout">
    <aside class="sidebar">
        <div class="sidebar-brand">StudentHub<span>.</span></div>
        <div class="sidebar-label">MAIN MENU</div>

        <a class="menu-link active" href="dashboard.php">▦ &nbsp; Dashboard</a>
        <a class="menu-link" href="audit_logs.php">◷ &nbsp; Audit Logs</a>

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
                <h4>Admin Dashboard</h4>
                <p>Welcome back! Here's your platform overview.</p>
            </div>
            <span class="role-badge">ADMIN</span>
        </header>

        <section class="welcome-banner">
            <div>
                <span class="welcome-tag">ADMINISTRATION PANEL</span>
                <h2>Overview & Insights</h2>
                <p>Monitor your students, events and registrations in one place.</p>
            </div>
            <div class="welcome-symbol">✦</div>
        </section>

        <h5 class="section-title">Platform Statistics</h5>

        <div class="row g-4 mb-4">
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="stat-card">
                    <div class="stat-icon purple">♙</div>
                    <p>Total Users</p>
                    <h2><?= $totalUsers ?></h2>
                    <small>Registered accounts</small>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-3">
                <div class="stat-card">
                    <div class="stat-icon blue">▤</div>
                    <p>Total Students</p>
                    <h2><?= $totalStudents ?></h2>
                    <small>Student records</small>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-3">
                <div class="stat-card">
                    <div class="stat-icon orange">◇</div>
                    <p>Total Events</p>
                    <h2><?= $totalEvents ?></h2>
                    <small>Created events</small>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-3">
                <div class="stat-card">
                    <div class="stat-icon green">✓</div>
                    <p>Registrations</p>
                    <h2><?= $totalRegistrations ?></h2>
                    <small>Total registrations</small>
                </div>
            </div>
        </div>

        <section class="table-card">
            <div class="table-heading">
                <div>
                    <h5>Latest Registrations</h5>
                    <p>Recently registered students and their events</p>
                </div>
                <span class="live-badge">● LIVE DATA</span>
            </div>

            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Student</th>
                            <th>Event</th>
                            <th>Registered At</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if ($latest->num_rows > 0): ?>
                        <?php while ($row = $latest->fetch_assoc()): ?>
                            <tr>
                                <td>#<?= (int) $row["id"] ?></td>
                                <td><?= e($row["student_name"]) ?></td>
                                <td><?= e($row["event_title"]) ?></td>
                                <td><?= e($row["created_at"]) ?></td>
                                <td><span class="status-badge">Registered</span></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center py-4">
                                No registrations found.
                            </td>
                        </tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <footer class="page-footer">
            StudentHub Admin Panel · Role-Based Access Control
        </footer>
    </main>
</div>
</body>
</html>
