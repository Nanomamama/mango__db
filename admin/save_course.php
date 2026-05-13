<?php
require_once 'auth.php';
require_once __DIR__ . '/../db/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: edit_courses.php?error=invalid_request');
    exit;
}

$course_name = trim($_POST['course_name'] ?? '');
$course_description = trim($_POST['course_description'] ?? '');

if ($course_name === '' || $course_description === '') {
    header('Location: edit_courses.php?error=' . urlencode('กรุณากรอกชื่อกิจกรรมและคำอธิบาย'));
    exit;
}

if (mb_strlen($course_name, 'UTF-8') > 255) {
    header('Location: edit_courses.php?error=' . urlencode('ชื่อกิจกรรมยาวเกินไป'));
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

function upload_course_image(string $file_key, string $target_dir, array $allowed_types): ?string
{
    if (!isset($_FILES[$file_key]) || $_FILES[$file_key]['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
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

    return $new_filename;
}

try {
    $image1 = upload_course_image('image1', $target_dir, $allowed_types);
    $image2 = upload_course_image('image2', $target_dir, $allowed_types);
    $image3 = upload_course_image('image3', $target_dir, $allowed_types);
} catch (RuntimeException $e) {
    header('Location: edit_courses.php?error=' . urlencode($e->getMessage()));
    exit;
}

$stmt = $conn->prepare('INSERT INTO courses (course_name, course_description, image1, image2, image3) VALUES (?, ?, ?, ?, ?)');
if (!$stmt) {
    header('Location: edit_courses.php?error=' . urlencode($conn->error));
    exit;
}

$stmt->bind_param('sssss', $course_name, $course_description, $image1, $image2, $image3);

if ($stmt->execute()) {
    header('Location: edit_courses.php?success=add');
} else {
    header('Location: edit_courses.php?error=' . urlencode($stmt->error));
}

$stmt->close();
$conn->close();
exit;
