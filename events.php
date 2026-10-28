
<?php
require_once __DIR__ . '/config.php';

$method = $_SERVER['REQUEST_METHOD'];

try {
    // READ and SEARCH
    if ($method === 'GET') {
        $search = trim($_GET['search'] ?? '');

        if ($search !== '') {
            $stmt = $pdo->prepare(
                "SELECT * FROM events
                 WHERE title LIKE :s
                    OR venue LIKE :s
                    OR description LIKE :s
                 ORDER BY event_date ASC, id DESC"
            );

            $stmt->execute(['s' => "%$search%"]);
        } else {
            $stmt = $pdo->query(
                "SELECT * FROM events
                 ORDER BY event_date ASC, id DESC"
            );
        }

        send_json([
            'success' => true,
            'data' => $stmt->fetchAll()
        ]);
    }

    // CREATE
    if ($method === 'POST') {
        $data = read_json_body();

        [$title, $date, $venue, $description] =
            validate_event($data);

        $stmt = $pdo->prepare(
            "INSERT INTO events
             (title, event_date, venue, description)
             VALUES (?, ?, ?, ?)"
        );

        $stmt->execute([
            $title, $date, $venue, $description
        ]);

        send_json([
            'success' => true,
            'message' => 'Event added successfully.',
            'id' => (int)$pdo->lastInsertId()
        ], 201);
    }

    // UPDATE
    if ($method === 'PUT') {
        $id = filter_var(
            $_GET['id'] ?? null,
            FILTER_VALIDATE_INT
        );

        if (!$id || $id < 1) {
            send_json([
                'success' => false,
                'message' => 'Valid event ID is required.'
            ], 400);
        }

        $data = read_json_body();

        [$title, $date, $venue, $description] =
            validate_event($data);

        $stmt = $pdo->prepare(
            "UPDATE events
             SET title = ?, event_date = ?,
                 venue = ?, description = ?
             WHERE id = ?"
        );

        $stmt->execute([
            $title, $date, $venue, $description, $id
        ]);

        $check = $pdo->prepare(
            "SELECT id FROM events WHERE id = ?"
        );
        $check->execute([$id]);

        if (!$check->fetch()) {
            send_json([
                'success' => false,
                'message' => 'Event not found.'
            ], 404);
        }

        send_json([
            'success' => true,
            'message' => 'Event updated successfully.'
        ]);
    }

    // DELETE
    if ($method === 'DELETE') {
        $id = filter_var(
            $_GET['id'] ?? null,
            FILTER_VALIDATE_INT
        );

        if (!$id || $id < 1) {
            send_json([
                'success' => false,
                'message' => 'Valid event ID is required.'
            ], 400);
        }

        $stmt = $pdo->prepare(
            "DELETE FROM events WHERE id = ?"
        );
        $stmt->execute([$id]);

        if ($stmt->rowCount() === 0) {
            send_json([
                'success' => false,
                'message' => 'Event not found.'
            ], 404);
        }

        send_json([
            'success' => true,
            'message' => 'Event deleted successfully.'
        ]);
    }

    header('Allow: GET, POST, PUT, DELETE');

    send_json([
        'success' => false,
        'message' => 'Method not allowed.'
    ], 405);

} catch (Throwable $e) {
    send_json([
        'success' => false,
        'message' => 'Server error. Check PHP and MySQL configuration.'
    ], 500);
}
