<?php 
error_reporting(E_ALL);
ini_set('display_errors', '1');
require 'db.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['event_name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $date = $_POST['event_date'] ?? '';
    $category = trim($_POST['category'] ?? '');
    $status = $_POST['status'] ?? 'Open';
    $poster = null;
    $savedPath = null;

    if ($name === '' || $description === '' ||
        $date === '' || $category === '') {
        $error = 'Please fill in all required fields.';
    } elseif (!in_array($status, ['Open', 'Closed'], true)) {
        $error = 'Invalid event status.';
    } elseif (!checkdate(
        (int)date('m', strtotime($date)),
        (int)date('d', strtotime($date)),
        (int)date('Y', strtotime($date))
    ) || date('Y-m-d', strtotime($date)) !== $date) {
        $error = 'Please enter a valid event date.';
    } else {
        $upload = $_FILES['poster'] ?? null;

        if ($upload && $upload['error'] !== UPLOAD_ERR_NO_FILE) {
            if ($upload['error'] !== UPLOAD_ERR_OK) {
                $error = 'Poster upload failed.';
            } elseif ($upload['size'] > 2 * 1024 * 1024) {
                $error = 'Poster must be 2 MB or smaller.';
            } else {
                $finfo = new finfo(FILEINFO_MIME_TYPE);
                $mime = $finfo->file($upload['tmp_name']);

                $types = [
                    'image/jpeg' => 'jpg',
                    'image/png' => 'png',
                    'image/webp' => 'webp'
                ];

                $imageInfo = @getimagesize($upload['tmp_name']);

                if (!isset($types[$mime]) || !$imageInfo ||
                    $imageInfo['mime'] !== $mime) {
                    $error = 'Only valid JPG, PNG or WebP images are allowed.';
                } else {
                    $folder = __DIR__ . '/uploads';

                    if (!is_dir($folder) &&
                        !mkdir($folder, 0755, true)) {
                        $error = 'Could not create upload folder.';
                    } else {
                        $poster = bin2hex(random_bytes(16))
                                  . '.' . $types[$mime];
                        $savedPath = $folder . '/' . $poster;

                        if (!move_uploaded_file(
                            $upload['tmp_name'], $savedPath
                        )) {
                            $error = 'Could not save the poster.';
                            $savedPath = null;
                        }
                    }
                }
            }
        }

        if ($error === '') {
            $stmt = $conn->prepare(
                "INSERT INTO events
                (event_name, description, event_date, category, poster, status)
                VALUES (?, ?, ?, ?, ?, ?)"
            );

            $stmt->bind_param(
                'ssssss', $name, $description, $date,
                $category, $poster, $status
            );

            if ($stmt->execute()) {
                header('Location: index.php?msg=' .
                    urlencode('Event added successfully.'));
                exit;
            }

            if ($savedPath && is_file($savedPath)) {
                unlink($savedPath);
            }
            $error = 'Could not save the event.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Add Event</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-5">
    <div class="card p-4 mx-auto" style="max-width:650px">
        <h2 class="mb-3">Add New Event</h2>

        <?php if ($error): ?>
            <div class="alert alert-danger">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form method="post" enctype="multipart/form-data">
            <label class="form-label">Event Name</label>
            <input class="form-control mb-3" name="event_name"
                   required maxlength="100"
                   value="<?= htmlspecialchars($_POST['event_name'] ?? '') ?>">

            <label class="form-label">Description</label>
            <textarea class="form-control mb-3" name="description"
                      required><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>

            <label class="form-label">Event Date</label>
            <input type="date" class="form-control mb-3" name="event_date"
                   required value="<?= htmlspecialchars($_POST['event_date'] ?? '') ?>">

            <label class="form-label">Category</label>
            <input class="form-control mb-3" name="category"
                   required maxlength="50"
                   value="<?= htmlspecialchars($_POST['category'] ?? '') ?>">

            <label class="form-label">Event Status</label>
            <select class="form-select mb-3" name="status">
                <option value="Open">Open</option>
                <option value="Closed">Closed</option>
            </select>

            <label class="form-label">Poster (JPG, PNG or WebP; max 2 MB)</label>
            <input type="hidden" name="MAX_FILE_SIZE" value="2097152">
            <input type="file" class="form-control mb-3" name="poster"
                   accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">

            <button class="btn btn-primary">Save Event</button>
            <a href="index.php" class="btn btn-secondary">Back</a>
        </form>
    </div>
</div>
</body>
</html>