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

if (!$conn) {
    json_exit(['success' => false, 'error' => 'เกิดข้อผิดพลาดในการเชื่อมต่อฐานข้อมูล']);
}

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['courses_id'], $data['rating']) || !is_numeric($data['rating'])) {
    json_exit(['success' => false, 'error' => 'ข้อมูลไม่ครบถ้วน']);
}

$courses_id = (int)$data['courses_id'];
$rating = (int)$data['rating'];
$token = $_SESSION['temp_access_token'] ?? '';

if ($rating < 1 || $rating > 5) {
    json_exit(['success' => false, 'error' => 'คะแนนต้องอยู่ระหว่าง 1-5']);
}

// ✅ ตรวจสอบ token
if (!isset($_SESSION['temp_access_token']) || $_SESSION['temp_access_token'] !== $token) {
    json_exit(['success' => false, 'error' => 'กรุณายืนยันรหัสใหม่อีกครั้ง']);
}

if (!isset($_SESSION['temp_access_time']) || (time() - $_SESSION['temp_access_time']) > 300) {
    unset($_SESSION['temp_access_token']);
    json_exit(['success' => false, 'error' => 'รหัสหมดอายุ กรุณายืนยันใหม่']);
}

$checkCourse = $conn->prepare("SELECT courses_id FROM courses WHERE courses_id = ?");
if (!$checkCourse) {
    json_exit(['success' => false, 'error' => 'Database error']);
}
$checkCourse->bind_param('i', $courses_id);
$checkCourse->execute();

$res = $checkCourse->get_result();
if (!$res || $res->num_rows == 0) {
    json_exit(['success' => false, 'error' => 'ไม่พบหลักสูตร']);
}
$checkCourse->close();

$member_id = $_SESSION['member_id'] ?? null;
$guest_identifier = session_id() . '_' . time();

if ($member_id) {
    $stmt = $conn->prepare("
        INSERT INTO course_rating (courses_id, member_id, guest_identifier, rating, created_at)
        VALUES (?, ?, ?, ?, NOW())
    ");
    if (!$stmt) {
        json_exit(['success' => false, 'error' => 'Prepare error']);
    }
    $stmt->bind_param('iisi', $courses_id, $member_id, $guest_identifier, $rating);
} else {
    $stmt = $conn->prepare("
        INSERT INTO course_rating (courses_id, guest_identifier, rating, created_at)
        VALUES (?, ?, ?, NOW())
    ");
    if (!$stmt) {
        json_exit(['success' => false, 'error' => 'Prepare error']);
    }
    $stmt->bind_param("isi", $courses_id, $guest_identifier, $rating);
}

if (!$stmt->execute()) {
    json_exit(['success' => false, 'error' => 'Execute error: ' . $stmt->error]);
}

$stmt->close();

$avgStmt = $conn->prepare("
    SELECT AVG(rating) AS avg_rating, COUNT(*) AS cnt
    FROM course_rating
    WHERE courses_id = ?
");
if (!$avgStmt) {
    json_exit(['success' => false, 'error' => 'Database error']);
}
$avgStmt->bind_param('i', $courses_id);
$avgStmt->execute();
$result = $avgStmt->get_result()->fetch_assoc();
$avgStmt->close();

json_exit([
    'success' => true,
    'avg' => round($result['avg_rating'], 2),
    'count' => $result['cnt'],
    'guest_identifier' => $guest_identifier
]);