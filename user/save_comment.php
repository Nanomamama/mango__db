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

if (empty($user_name) || empty($comment_text)) {
    echo json_encode(['success' => false, 'error' => 'กรุณากรอกข้อมูลให้ครบถ้วน']);
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

// บันทึก comment
$stmt = $conn->prepare("INSERT INTO course_comments (courses_id, user_name, comment_text) VALUES (?, ?, ?)");
$stmt->bind_param('iss', $course_id, $user_name, $comment_text);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => $conn->error]);
}

$stmt->close();
?>