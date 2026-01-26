<?php
session_start();
header('Content-Type: application/json');

require_once '../admin/db.php';

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['course_id'], $data['user_name'], $data['comment_text'])) {
    echo json_encode(['success' => false, 'error' => 'ข้อมูลไม่ครบถ้วน']);
    exit;
}

$course_id = (int)$data['course_id'];
$user_name = trim($data['user_name']);
$comment_text = trim($data['comment_text']);

// รับ guest_identifier จาก request (ถ้ามี) หรือสร้างใหม่
$guest_identifier = $data['guest_identifier'] ?? (session_id() . '_' . time());

if (empty($user_name) || empty($comment_text)) {
    echo json_encode(['success' => false, 'error' => 'กรุณากรอกข้อมูลให้ครบถ้วน']);
    exit;
}

// ตรวจสอบสิทธิ์เข้าถึง
if (!isset($_SESSION['course_access']) || !in_array($course_id, $_SESSION['course_access'])) {
    echo json_encode(['success' => false, 'error' => 'คุณยังไม่ได้ยืนยันการเข้าร่วมกิจกรรม']);
    exit;
}

$member_id = $_SESSION['member_id'] ?? null;

$stmt = $conn->prepare("
    INSERT INTO course_comments (courses_id, member_id, guest_identifier, name, comment_text, created_at)
    VALUES (?, ?, ?, ?, ?, NOW())
");

if (!$stmt) {
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $conn->error]);
    exit;
}

$member_id_val = $member_id ? $member_id : null;
$stmt->bind_param('iisss', $course_id, $member_id_val, $guest_identifier, $user_name, $comment_text);

if ($stmt->execute()) {
    echo json_encode([
        'success' => true,
        'guest_identifier' => $guest_identifier
    ]);
} else {
    echo json_encode(['success' => false, 'error' => 'Execute error: ' . $stmt->error]);
}

$stmt->close();
$conn->close();