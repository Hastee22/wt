
<?php
header('Content-Type: application/json; charset=utf-8');

$host = 'localhost';
$dbname = 'studenthub_p13';
$username = 'root';
$password = ''; // Change this if your MySQL has a password.

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database connection failed.'
    ]);
    exit;
}

function send_json(array $data, int $status = 200): void {
    http_response_code($status);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

function read_json_body(): array {
    $data = json_decode(file_get_contents('php://input'), true);

    if (!is_array($data)) {
        send_json([
            'success' => false,
            'message' => 'Invalid JSON request body.'
        ], 400);
    }

    return $data;
}

function validate_event(array $data): array {
    $title = trim((string)($data['title'] ?? ''));
    $event_date = trim((string)($data['event_date'] ?? ''));
    $venue = trim((string)($data['venue'] ?? ''));
    $description = trim((string)($data['description'] ?? ''));

    if ($title === '' || $event_date === '' || $venue === '') {
        send_json([
            'success' => false,
            'message' => 'Title, date and venue are required.'
        ], 422);
    }

    if (strlen($title) > 120 || strlen($venue) > 150) {
        send_json([
            'success' => false,
            'message' => 'Title or venue is too long.'
        ], 422);
    }

    $date = DateTime::createFromFormat('!Y-m-d', $event_date);

    if (!$date || $date->format('Y-m-d') !== $event_date) {
        send_json([
            'success' => false,
            'message' => 'Enter a valid date.'
        ], 422);
    }

    return [$title, $event_date, $venue, $description];
}
