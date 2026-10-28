<?php
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

if (!$id || $id < 1) {
    header('Location: index.php?msg=' . urlencode('Invalid event ID.'));
    exit;
}

$stmt = $conn->prepare("SELECT poster FROM events WHERE event_id = ?");
$stmt->bind_param('i', $id);
$stmt->execute();
$event = $stmt->get_result()->fetch_assoc();

if (!$event) {
    header('Location: index.php?msg=' . urlencode('Event not found.'));
    exit;
}

$stmt = $conn->prepare("DELETE FROM events WHERE event_id = ?");
$stmt->bind_param('i', $id);

if ($stmt->execute()) {
    if ($event['poster']) {
        $path = __DIR__ . '/uploads/' . basename($event['poster']);
        if (is_file($path)) {
            unlink($path);
        }
    }

    header('Location: index.php?msg=' . urlencode('Event deleted successfully.'));
} else {
    header('Location: index.php?msg=' . urlencode('Could not delete event.'));
}
exit;
?>