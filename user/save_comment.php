<?php
session_start();
header('Content-Type: application/json');

require_once '../admin/db.php';

// Robust error handling: convert warnings/notices to exceptions and ensure JSON error responses
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/save_comment_errors.log');

set_error_handler(function($severity, $message, $file, $line) {
    throw new ErrorException($message, 0, $severity, $file, $line);
});

register_shutdown_function(function() {
    $err = error_get_last();
    if ($err) {
        file_put_contents(__DIR__ . '/save_comment_errors.log', date('c') . " SHUTDOWN " . json_encode($err) . PHP_EOL, FILE_APPEND);
        if (!headers_sent()) header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'Internal server error']);
    }
});

try {

    $data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['course_id'], $data['user_name'], $data['comment_text'])) {
    echo json_encode(['success' => false, 'error' => 'ข้อมูลไม่ครบถ้วน']);
    exit;
}

$course_id = (int)$data['course_id'];
$user_name = trim($data['user_name']);
$comment_text = trim($data['comment_text']);

// Validation
if (empty($user_name) || empty($comment_text)) {
    echo json_encode(['success' => false, 'error' => 'กรุณากรอกข้อมูลให้ครบถ้วน']);
    exit;
}

if (strlen($comment_text) > 1000) {
    echo json_encode(['success' => false, 'error' => 'ความคิดเห็นยาวเกินไป (สูงสุด 1000 ตัวอักษร)']);
    exit;
}

// ตรวจสอบว่าคอร์สมีอยู่จริง
$checkCourse = $conn->prepare("SELECT courses_id FROM courses WHERE courses_id = ?");
$checkCourse->bind_param('i', $course_id);
$checkCourse->execute();
if (!$checkCourse->get_result()->num_rows) {
    echo json_encode(['success' => false, 'error' => 'ไม่พบหลักสูตร']);
    exit;
}
$checkCourse->close();

// ตรวจสอบสิทธิ์เข้าถึงคอมเมนต์ (ต้องยืนยันรหัสก่อน)
if (!isset($_SESSION['course_access']) || !in_array($course_id, $_SESSION['course_access'])) {
    echo json_encode(['success' => false, 'error' => 'คุณยังไม่ได้ยืนยันการเข้าร่วมกิจกรรม']);
    exit;
}


// กำหนด identifier สำหรับเก็บในคอลัมน์ guest_identifier
$member_id = $_SESSION['member_id'] ?? null;
$identifier = $member_id ? 'member:' . $member_id : session_id();

// บันทึกคอมเมนต์: ตารางมีคอลัมน์ guest_identifier (varchar), ไม่มี member_id
$stmt = $conn->prepare("
    INSERT INTO course_comments
    (courses_id, guest_identifier, name, comment_text)
    VALUES (?, ?, ?, ?)
");

$stmt->bind_param(
    "isss",
    $course_id,
    $identifier,
    $user_name,
    $comment_text
);


if ($stmt->execute()) {
    echo json_encode([
        'success' => true,
        'message' => 'บันทึกความคิดเห็นสำเร็จ'
    ]);
} else {
    echo json_encode(['success' => false, 'error' => 'เกิดข้อผิดพลาด: ' . $conn->error]);
}

    $stmt->close();
    $conn->close();
} catch (Throwable $e) {
    // Log and return JSON error
    $msg = '[' . date('c') . '] ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine() . PHP_EOL;
    file_put_contents(__DIR__ . '/save_comment_errors.log', $msg, FILE_APPEND);
    if (!headers_sent()) header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Internal server error']);
    exit;
}
?>