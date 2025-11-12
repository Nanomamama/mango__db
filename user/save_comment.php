<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ปิดการแสดง error เป็น HTML
ini_set('display_errors', '0');
ini_set('log_errors', '1');
error_reporting(E_ALL);

require_once '../admin/db.php';

// ส่ง JSON header
header('Content-Type: application/json; charset=utf-8');

// ตั้ง exception handler ให้ส่ง JSON
set_exception_handler(function($e) {
    error_log("save_comment exception: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Server error']);
    exit;
});

// ตั้ง error handler ให้ throw exception
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    error_log("save_comment error [$errno] $errstr in $errfile:$errline");
    throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
});

// อ่าน JSON input
$raw = file_get_contents('php://input');
$data = json_decode($raw, true);

if (!is_array($data)) {
    echo json_encode(['success' => false, 'error' => 'Invalid input']);
    exit;
}

$course_id = isset($data['course_id']) ? (int)$data['course_id'] : 0;
$user_name = trim($data['user_name'] ?? '');
$comment_text = trim($data['comment_text'] ?? '');

// Validation
if ($course_id <= 0) {
    echo json_encode(['success' => false, 'error' => 'Invalid course id']);
    exit;
}

if (strlen($user_name) === 0) {
    echo json_encode(['success' => false, 'error' => 'กรุณากรอกชื่อของคุณ']);
    exit;
}

if (strlen($comment_text) === 0) {
    echo json_encode(['success' => false, 'error' => 'กรุณากรอกความคิดเห็น']);
    exit;
}

if (strlen($user_name) > 100) {
    echo json_encode(['success' => false, 'error' => 'ชื่อต้องไม่เกิน 100 ตัวอักษร']);
    exit;
}

if (strlen($comment_text) > 1000) {
    echo json_encode(['success' => false, 'error' => 'ความคิดเห็นต้องไม่เกิน 1000 ตัวอักษร']);
    exit;
}

// Sanitize
$user_name = htmlspecialchars($user_name, ENT_QUOTES, 'UTF-8');
$comment_text = htmlspecialchars($comment_text, ENT_QUOTES, 'UTF-8');

// ตรวจสอบว่าหลักสูตรมีอยู่จริง
mysqli_report(MYSQLI_REPORT_OFF);

$checkStmt = $conn->prepare("SELECT id FROM courses WHERE id = ?");
if (!$checkStmt) {
    error_log('check prepare error: ' . $conn->error);
    echo json_encode(['success' => false, 'error' => 'Database error']);
    exit;
}

$checkStmt->bind_param('i', $course_id);
$checkStmt->execute();
$checkResult = $checkStmt->get_result();

if ($checkResult->num_rows === 0) {
    error_log('course not found: ' . $course_id);
    echo json_encode(['success' => false, 'error' => 'หลักสูตรไม่พบ']);
    $checkStmt->close();
    exit;
}

$checkStmt->close();

// บันทึกความคิดเห็น
$stmt = $conn->prepare("INSERT INTO course_comments (course_id, user_name, comment_text) VALUES (?, ?, ?)");

if (!$stmt) {
    error_log('insert prepare error: ' . $conn->error);
    echo json_encode(['success' => false, 'error' => 'Database prepare error']);
    exit;
}

$stmt->bind_param('iss', $course_id, $user_name, $comment_text);

if ($stmt->execute()) {
    error_log("Comment saved - course_id: $course_id, user: $user_name");
    echo json_encode(['success' => true, 'message' => 'ความคิดเห็นถูกบันทึกแล้ว']);
} else {
    error_log('insert execute error: ' . $stmt->error);
    echo json_encode(['success' => false, 'error' => 'Database execute error']);
}

$stmt->close();
?>