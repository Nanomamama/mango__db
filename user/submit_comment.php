<?php
// submit_comment.php

header('Content-Type: application/json');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ต้องมั่นใจว่ามีการเชื่อมต่อ db.php ที่ถูกต้อง
require_once '../admin/db.php'; 

// ตรวจสอบวิธีการร้องขอ
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid request method.']);
    exit;
}

// รับข้อมูล JSON
$input = file_get_contents('php://input');
$data = json_decode($input, true);

$courseId = $data['courses_id'] ?? 0;
$userName = $data['user_name'] ?? '';
$commentText = $data['comment_text'] ?? '';

// การตรวจสอบข้อมูลพื้นฐาน
if (!is_numeric($courseId) || $courseId <= 0 || empty($userName) || empty($commentText)) {
    echo json_encode(['success' => false, 'error' => 'กรุณากรอกข้อมูลให้ครบถ้วน.']);
    exit;
}

// จำกัดความยาวของชื่อและคอมเมนต์ (ถ้าจำเป็น)
$userName = substr(trim($userName), 0, 100);
$commentText = substr(trim($commentText), 0, 500);

// บันทึกเข้าฐานข้อมูล
try {
    $stmt = $conn->prepare("INSERT INTO course_comments (course_id, user_name, comment_text) VALUES (?, ?, ?)");
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    $stmt->bind_param('iss', $courseId, $userName, $commentText);
    $stmt->execute();
    $stmt->close();

    // ดึงข้อมูลคอมเมนต์ที่เพิ่งเพิ่มไปเพื่อส่งกลับไปแสดงผล
    $newComment = [
        'user_name' => htmlspecialchars($userName),
        'comment_text' => nl2br(htmlspecialchars($commentText)),
        'created_at' => date('Y-m-d H:i:s') // หรือ format อื่นที่ต้องการ
    ];

    echo json_encode(['success' => true, 'comment' => $newComment]);

} catch (Exception $e) {
    // บันทึกข้อผิดพลาดจริงใน Log (ไม่แสดงให้ผู้ใช้เห็น)
    error_log("Comment submission error: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'ไม่สามารถบันทึกความคิดเห็นได้.']);
}

$conn->close();
?>