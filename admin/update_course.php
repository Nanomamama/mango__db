<?php
require_once 'auth.php';
require_once __DIR__ . '/../db/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: edit_courses.php?error=invalid_request');
    exit;
}

$courses_id = (int)($_POST['id'] ?? 0);
$course_name = trim($_POST['course_name'] ?? '');
$course_description = trim($_POST['course_description'] ?? '');

if ($courses_id <= 0) {
    header('Location: edit_courses.php?error=' . urlencode('รหัสกิจกรรมไม่ถูกต้อง'));
    exit;
}

if ($course_name === '' || $course_description === '') {
    header('Location: edit_courses.php?error=' . urlencode('กรุณากรอกชื่อกิจกรรมและคำอธิบาย'));
    exit;
}

if (mb_strlen($course_name, 'UTF-8') > 255) {
    header('Location: edit_courses.php?error=' . urlencode('ชื่อกิจกรรมยาวเกินไป'));
    exit;
}

$stmt_old = $conn->prepare('SELECT image1, image2, image3 FROM courses WHERE courses_id = ?');
if (!$stmt_old) {
    header('Location: edit_courses.php?error=' . urlencode($conn->error));
    exit;
}

$stmt_old->bind_param('i', $courses_id);
$stmt_old->execute();
$old_images = $stmt_old->get_result()->fetch_assoc();
$stmt_old->close();

if (!$old_images) {
    header('Location: edit_courses.php?error=' . urlencode('ไม่พบกิจกรรมที่ต้องการแก้ไข'));
    exit;
}

$target_dir = realpath(__DIR__ . '/../uploads');
if ($target_dir === false) {
    $upload_root = __DIR__ . '/../uploads';
    if (!mkdir($upload_root, 0755, true) && !is_dir($upload_root)) {
        header('Location: edit_courses.php?error=' . urlencode('ไม่สามารถสร้างโฟลเดอร์อัปโหลดได้'));
        exit;
    }
    $target_dir = realpath($upload_root);
}

$allowed_types = [
    'image/jpeg' => 'jpg',
    'image/png' => 'png',
    'image/gif' => 'gif',
    'image/webp' => 'webp',
];

function delete_course_image_file(?string $filename, string $target_dir): void
{
    if (!$filename) {
        return;
    }

    $path = realpath($target_dir . DIRECTORY_SEPARATOR . basename($filename));
    if ($path !== false && strpos($path, $target_dir . DIRECTORY_SEPARATOR) === 0 && is_file($path)) {
        @unlink($path);
    }
}

function upload_course_image_or_keep(string $file_key, ?string $current_image, string $target_dir, array $allowed_types, bool $remove_image = false): ?string
{
    if (!isset($_FILES[$file_key]) || $_FILES[$file_key]['error'] === UPLOAD_ERR_NO_FILE) {
        if ($remove_image) {
            delete_course_image_file($current_image, $target_dir);
            return null;
        }

        return $current_image;
    }

    if ($_FILES[$file_key]['error'] !== UPLOAD_ERR_OK) {
        throw new RuntimeException("อัปโหลดรูป {$file_key} ไม่สำเร็จ");
    }

    if (!is_uploaded_file($_FILES[$file_key]['tmp_name'])) {
        throw new RuntimeException("ไฟล์ {$file_key} ไม่ถูกต้อง");
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime_type = $finfo->file($_FILES[$file_key]['tmp_name']);
    if (!isset($allowed_types[$mime_type])) {
        throw new RuntimeException("ไฟล์ {$file_key} ต้องเป็น JPG, PNG, GIF หรือ WEBP");
    }

    $new_filename = uniqid('course_', true) . '.' . $allowed_types[$mime_type];
    $target_path = $target_dir . DIRECTORY_SEPARATOR . $new_filename;

    if (!move_uploaded_file($_FILES[$file_key]['tmp_name'], $target_path)) {
        throw new RuntimeException("ไม่สามารถบันทึกรูป {$file_key} ได้");
    }

    delete_course_image_file($current_image, $target_dir);
    return $new_filename;
}

try {
    $image1 = upload_course_image_or_keep('image1', $old_images['image1'], $target_dir, $allowed_types, isset($_POST['remove_image1']));
    $image2 = upload_course_image_or_keep('image2', $old_images['image2'], $target_dir, $allowed_types, isset($_POST['remove_image2']));
    $image3 = upload_course_image_or_keep('image3', $old_images['image3'], $target_dir, $allowed_types, isset($_POST['remove_image3']));
} catch (RuntimeException $e) {
    header('Location: edit_courses.php?error=' . urlencode($e->getMessage()));
    exit;
}

$stmt = $conn->prepare('UPDATE courses SET course_name = ?, course_description = ?, image1 = ?, image2 = ?, image3 = ? WHERE courses_id = ?');
if (!$stmt) {
    header('Location: edit_courses.php?error=' . urlencode($conn->error));
    exit;
}

$stmt->bind_param('sssssi', $course_name, $course_description, $image1, $image2, $image3, $courses_id);

if ($stmt->execute()) {
    header('Location: edit_courses.php?success=update');
} else {
    header('Location: edit_courses.php?error=' . urlencode($stmt->error));
}

$stmt->close();
$conn->close();
exit;
