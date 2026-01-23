<?php
session_start();
header('Content-Type: application/json');

require_once '../admin/db.php';

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['courses_id'], $data['code'])) {
    echo json_encode(['success' => false, 'error' => 'ข้อมูลไม่ครบถ้วน']);
    exit;
}

$courses_id = (int)$data['courses_id'];
$code = trim($data['code']);

if ($code === '') {
    echo json_encode(['success' => false, 'error' => 'กรุณากรอกรหัส']);
    exit;
}


// Check bookings table for matching comment_code with approved status (guests allowed)
$stmt = $conn->prepare("SELECT bookings_id FROM bookings WHERE comment_code = ? AND status = 'อนุมัติแล้ว' LIMIT 1");
$stmt->bind_param('s', $code);

$stmt->execute();
$res = $stmt->get_result();
if ($res && $res->num_rows > 0) {
    // mark access for this course in session
    if (!isset($_SESSION['course_access']) || !is_array($_SESSION['course_access'])) $_SESSION['course_access'] = [];
    if (!in_array($courses_id, $_SESSION['course_access'])) $_SESSION['course_access'][] = $courses_id;

    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => 'รหัสไม่ถูกต้องหรือยังไม่ได้รับการอนุมัติ']);
}

$stmt->close();
$conn->close();

?>