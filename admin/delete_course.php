<?php
require_once 'auth.php';
require_once __DIR__ . '/../db/db.php';

function redirect_courses(string $type, string $message): never
{
    header('Location: edit_courses.php?' . $type . '=' . urlencode($message));
    exit;
}

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

function delete_course_rows(mysqli $conn, string $table, int $course_id): void
{
    $stmt = $conn->prepare("DELETE FROM {$table} WHERE courses_id = ?");
    if (!$stmt) {
        throw new RuntimeException($conn->error);
    }

    $stmt->bind_param('i', $course_id);
    $stmt->execute();
    $stmt->close();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['id'])) {
    redirect_courses('error', 'คำขอลบกิจกรรมไม่ถูกต้อง');
}

$id = (int) $_POST['id'];
if ($id <= 0) {
    redirect_courses('error', 'รหัสกิจกรรมไม่ถูกต้อง');
}

$target_dir = realpath(__DIR__ . '/../uploads');

try {
    $stmt_images = $conn->prepare('SELECT image1, image2, image3 FROM courses WHERE courses_id = ? LIMIT 1');
    if (!$stmt_images) {
        throw new RuntimeException($conn->error);
    }

    $stmt_images->bind_param('i', $id);
    $stmt_images->execute();
    $images = $stmt_images->get_result()->fetch_assoc();
    $stmt_images->close();

    if (!$images) {
        redirect_courses('error', 'ไม่พบกิจกรรมที่ต้องการลบ');
    }

    $conn->begin_transaction();

    // Delete related rows explicitly so deletion still works if the live DB
    // does not have ON DELETE CASCADE constraints installed.
    delete_course_rows($conn, 'course_comments', $id);
    delete_course_rows($conn, 'course_rating', $id);

    $stmt = $conn->prepare('DELETE FROM courses WHERE courses_id = ?');
    if (!$stmt) {
        throw new RuntimeException($conn->error);
    }

    $stmt->bind_param('i', $id);
    $stmt->execute();

    if ($stmt->affected_rows !== 1) {
        throw new RuntimeException('ไม่สามารถลบกิจกรรมนี้ได้');
    }

    $stmt->close();
    $conn->commit();

    delete_course_image_file($images['image1'], $target_dir);
    delete_course_image_file($images['image2'], $target_dir);
    delete_course_image_file($images['image3'], $target_dir);

    redirect_courses('success', 'delete');
} catch (Throwable $e) {
    if ($conn instanceof mysqli) {
        try {
            $conn->rollback();
        } catch (Throwable $rollbackError) {
            // Keep the original delete error for the admin-facing message.
        }
    }

    redirect_courses('error', 'ลบกิจกรรมไม่สำเร็จ: ' . $e->getMessage());
} finally {
    if ($conn instanceof mysqli) {
        $conn->close();
    }
}
