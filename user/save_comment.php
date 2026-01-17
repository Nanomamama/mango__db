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

// กำหนด member_id และ guest_identifier
$member_id = $_SESSION['member_id'] ?? null;
$guest_id = $member_id ? null : session_id();

// บันทึกคอมเมนต์พร้อม member_id/guest_identifier
$stmt = $conn->prepare("
    INSERT INTO course_comments 
    (courses_id, name, comment_text, member_id, guest_identifier, created_at) 
    VALUES (?, ?, ?, ?, ?, NOW())
");

$stmt->bind_param('issis', $course_id, $user_name, $comment_text, $member_id, $guest_id);

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
?>