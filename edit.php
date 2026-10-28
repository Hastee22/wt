<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
require 'db.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
}

if (!$id || $id < 1) {
    exit('Invalid event ID.');
}

$stmt = $conn->prepare("SELECT * FROM events WHERE event_id = ?");
$stmt->bind_param('i', $id);
$stmt->execute();
$event = $stmt->get_result()->fetch_assoc();

if (!$event) {
    exit('Event not found.');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['event_name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $date = $_POST['event_date'] ?? '';
    $category = trim($_POST['category'] ?? '');
    $status = $_POST['status'] ?? '';
    $poster = $event['poster'];

    if ($name === '' || $description === '' || $date === '' ||
        $category === '' ||
        !in_array($status, ['Open', 'Closed'], true)) {
        $error = 'Please enter valid event details.';
    } else {
        $timestamp = strtotime($date);

        if (!$timestamp || date('Y-m-d', $timestamp) !== $date) {
            $error = 'Invalid event date.';
        }
    }

    $savedPath = null;

    if ($error === '' && isset($_FILES['poster']) &&
        $_FILES['poster']['error'] !== UPLOAD_ERR_NO_FILE) {
        $file = $_FILES['poster'];

        if ($file['error'] !== UPLOAD_ERR_OK ||
            $file['size'] > 2 * 1024 * 1024) {
            $error = 'Upload failed or file exceeds 2 MB.';
        } else {
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mime = $finfo->file($file['tmp_name']);
            $types = [
                'image/jpeg' => 'jpg',
                'image/png' => 'png',
                'image/webp' => 'webp'
            ];
            $info = @getimagesize($file['tmp_name']);

            if (!isset($types[$mime]) || !$info ||
                $info['mime'] !== $mime) {
                $error = 'Only valid JPG, PNG or WebP images are allowed.';
            } else {
                $folder = __DIR__ . '/uploads';

                if (!is_dir($folder) &&
                    !mkdir($folder, 0755, true)) {
                    $error = 'Could not create upload folder.';
                } else {
                    $newPoster = bin2hex(random_bytes(16))
                                 . '.' . $types[$mime];
                    $savedPath = $folder . '/' . $newPoster;

                    if (move_uploaded_file($file['tmp_name'], $savedPath)) {
                        $poster = $newPoster;
                    } else {
                        $error = 'Could not save the poster.';
                        $savedPath = null;
                    }
                }
            }
        }
    }

    if ($error === '') {
        $stmt = $conn->prepare(
            "UPDATE events SET event_name=?, description=?,
             event_date=?, category=?, poster=?, status=?
             WHERE event_id=?"
        );
        $stmt->bind_param(
            'ssssssi', $name, $description, $date,
            $category, $poster, $status, $id
        );

        if ($stmt->execute()) {
            if ($savedPath && $event['poster']) {
                $oldPath = __DIR__ . '/uploads/' .
                           basename($event['poster']);
                if (is_file($oldPath)) {
                    unlink($oldPath);
                }
            }

            header('Location: index.php?msg=' .
                urlencode('Event updated successfully.'));
            exit;
        }

        if ($savedPath && is_file($savedPath)) {
            unlink($savedPath);
        }
        $error = 'Could not update the event.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Edit Event</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-5">
    <div class="card p-4 mx-auto" style="max-width:650px">
        <h2>Edit Event</h2>

        <?php if ($error): ?>
            <div class="alert alert-danger">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form method="post" enctype="multipart/form-data">
            <input type="hidden" name="id" value="<?= $id ?>">

            <label class="form-label">Event Name</label>
            <input class="form-control mb-3" name="event_name"
                   required maxlength="100"
                   value="<?= htmlspecialchars($_POST['event_name'] ?? $event['event_name']) ?>">

            <label class="form-label">Description</label>
            <textarea class="form-control mb-3" name="description"
                      required><?= htmlspecialchars($_POST['description'] ?? $event['description']) ?></textarea>

            <label class="form-label">Date</label>
            <input type="date" class="form-control mb-3" name="event_date"
                   required value="<?= htmlspecialchars($_POST['event_date'] ?? $event['event_date']) ?>">

            <label class="form-label">Category</label>
            <input class="form-control mb-3" name="category"
                   required maxlength="50"
                   value="<?= htmlspecialchars($_POST['category'] ?? $event['category']) ?>">

            <label class="form-label">Status</label>
            <select class="form-select mb-3" name="status">
                <option value="Open"
                    <?= (($_POST['status'] ?? $event['status']) === 'Open') ? 'selected' : '' ?>>
                    Open
                </option>
                <option value="Closed"
                    <?= (($_POST['status'] ?? $event['status']) === 'Closed') ? 'selected' : '' ?>>
                    Closed
                </option>
            </select>

            <?php if ($event['poster']): ?>
                <p>Current poster:</p>
                <img class="mb-3" width="120"
                     src="uploads/<?= rawurlencode(basename($event['poster'])) ?>"
                     alt="Current poster">
            <?php endif; ?>

            <label class="form-label">Replace Poster (optional, max 2 MB)</label>
            <input type="file" class="form-control mb-3" name="poster"
                   accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">

            <button class="btn btn-primary">Update Event</button>
            <a href="index.php" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
</div>
</body>
</html>