
<?php
require_once "db.php";

$accounts = [
    ["admin", "admin123", "admin"],
    ["student", "student123", "student"]
];

$stmt = $conn->prepare(
    "INSERT INTO users (username, password, role)
     VALUES (?, ?, ?)
     ON DUPLICATE KEY UPDATE username = username"
);

foreach ($accounts as $account) {
    [$user, $pass, $role] = $account;
    $hash = password_hash($pass, PASSWORD_DEFAULT);
    $stmt->bind_param("sss", $user, $hash, $role);
    $stmt->execute();
}

echo "Demo accounts created successfully. ";
echo "Admin: admin / admin123 | Student: student / student123";
?>
