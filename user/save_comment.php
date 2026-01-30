<?php


ob_start();
error_reporting(0);
header('Content-Type: application/json; charset=utf-8');

if (session_status() === PHP_SESSION_NONE) session_start();
require_once '../admin/db.php';

function json_exit($arr) {
    ob_clean();
    echo json_encode($arr, JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_exit(['success' => false, 'error' => 'Invalid method']);
}


$data = json_decode(file_get_contents('php://input'), true);

$courseId   = (int)($data['courses_id'] ?? 0);
$userName   = trim($data['user_name'] ?? '');
$commentText= trim($data['comment_text'] ?? '');
$guestId    = trim($data['guest_identifier'] ?? '');
$token = $_SESSION['temp_access_token'] ?? '';


if ($courseId <= 0 || $userName === '' || $commentText === '') {
    json_exit(['success' => false, 'error' => 'กรุณากรอกข้อมูลให้ครบถ้วน']);
}

// ✅ ตรวจสอบ token
if (!isset($_SESSION['temp_access_token']) || $_SESSION['temp_access_token'] !== $token) {
    json_exit(['success' => false, 'error' => 'กรุณายืนยันรหัสใหม่อีกครั้ง']);
}

//ตรวจสอบว่า token หมดอายุหรือยัง (5 นาที)
if (!isset($_SESSION['temp_access_time']) || (time() - $_SESSION['temp_access_time']) > 300) {
    unset($_SESSION['temp_access_token']);
    json_exit(['success' => false, 'error' => 'รหัสหมดอายุ กรุณายืนยันใหม่']);
}

$userName = mb_substr($userName, 0, 100);
$commentText = mb_substr($commentText, 0, 1000);

$stmt = $conn->prepare("
    INSERT INTO course_comments (courses_id, name, comment_text, guest_identifier, created_at)
    VALUES (?, ?, ?, ?, NOW())
");

if (!$stmt) {
    json_exit(['success' => false, 'error' => 'Prepare error: ' . $conn->error]);
}

$stmt->bind_param('isss', $courseId, $userName, $commentText, $guestId);

if (!$stmt->execute()) {
    json_exit(['success' => false, 'error' => 'Execute error: ' . $stmt->error]);
}

$stmt->close();

// ✅ ลบ token หลังใช้งาน (ใช้ได้แค่ครั้งเดียว)
unset($_SESSION['temp_access_token']);
unset($_SESSION['temp_access_time']);

json_exit([
    'success' => true,
    'comment' => [
        'user_name' => htmlspecialchars($userName, ENT_QUOTES, 'UTF-8'),
        'comment_text' => nl2br(htmlspecialchars($commentText, ENT_QUOTES, 'UTF-8')),
        'created_at' => date('Y-m-d H:i:s')
    ]
]);