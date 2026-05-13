<?php
require_once 'auth.php';
require_once __DIR__ . '/../db/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['id'])) {
    header('Location: edit_courses.php?error=invalid_request');
    exit;
}

$id = (int)$_POST['id'];
if ($id <= 0) {
    header('Location: edit_courses.php?error=' . urlencode('รหัสกิจกรรมไม่ถูกต้อง'));
    exit;
}

$target_dir = realpath(__DIR__ . '/../uploads');

function delete_course_image_file(?string $filename, ?string $target_dir): void
{
    if (!$filename || !$target_dir) {
        return;
    }

    $path = realpath($target_dir . DIRECTORY_SEPARATOR . basename($filename));
    if ($path !== false && strpos($path, $target_dir . DIRECTORY_SEPARATOR) === 0 && is_file($path)) {
        @unlink($path);
    }
}

$stmt_images = $conn->prepare('SELECT image1, image2, image3 FROM courses WHERE courses_id = ?');
if (!$stmt_images) {
    header('Location: edit_courses.php?error=' . urlencode($conn->error));
    exit;
}

$stmt_images->bind_param('i', $id);
$stmt_images->execute();
$images = $stmt_images->get_result()->fetch_assoc();
$stmt_images->close();

if (!$images) {
    header('Location: edit_courses.php?error=' . urlencode('ไม่พบกิจกรรมที่ต้องการลบ'));
    exit;
}

$stmt = $conn->prepare('DELETE FROM courses WHERE courses_id = ?');
if (!$stmt) {
    header('Location: edit_courses.php?error=' . urlencode($conn->error));
    exit;
}

$stmt->bind_param('i', $id);

if ($stmt->execute()) {
    delete_course_image_file($images['image1'], $target_dir);
    delete_course_image_file($images['image2'], $target_dir);
    delete_course_image_file($images['image3'], $target_dir);
    header('Location: edit_courses.php?success=delete');
} else {
    header('Location: edit_courses.php?error=' . urlencode($stmt->error));
}

$stmt->close();
$conn->close();
exit;
