<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

ini_set('display_errors', '0');
ini_set('log_errors', '1');
error_reporting(E_ALL);

require_once '../admin/db.php';

header('Content-Type: application/json; charset=utf-8');

set_exception_handler(function($e) {
    error_log("save_comment exception: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Server error']);
    exit;
});

set_error_handler(function($errno, $errstr, $errfile, $errline) {
    error_log("save_comment error [$errno] $errstr in $errfile:$errline");
    throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
});

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
    echo json_encode(['success' => false, 'error' => 'Invalid course']);
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

if (strlen($user_name) > 100 || strlen($comment_text) > 1000) {
    echo json_encode(['success' => false, 'error' => 'ข้อมูลเกินขีดจำกัด']);
    exit;
}

// Sanitize
$user_name = htmlspecialchars($user_name, ENT_QUOTES, 'UTF-8');
$comment_text = htmlspecialchars($comment_text, ENT_QUOTES, 'UTF-8');

mysqli_report(MYSQLI_REPORT_OFF);

// Verify course exists
$checkStmt = $conn->prepare("SELECT courses_id FROM courses WHERE courses_id = ?");
if (!$checkStmt) {
    throw new Exception('DB prepare error');
}

$checkStmt->bind_param('i', $course_id);
$checkStmt->execute();
if ($checkStmt->get_result()->num_rows === 0) {
    echo json_encode(['success' => false, 'error' => 'หลักสูตรไม่พบ']);
    exit;
}
$checkStmt->close();

// Insert comment
$stmt = $conn->prepare("INSERT INTO course_comments (course_id, user_name, comment_text, created_at) VALUES (?, ?, ?, NOW())");
if (!$stmt) {
    throw new Exception('DB prepare error');
}

$stmt->bind_param('iss', $course_id, $user_name, $comment_text);
if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'ความคิดเห็นถูกบันทึกแล้ว']);
} else {
    error_log('Insert comment failed: ' . $stmt->error);
    throw new Exception('Insert failed');
}

$stmt->close();
?>