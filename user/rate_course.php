<?php
session_start();
header('Content-Type: application/json');

require_once '../admin/db.php';

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['courses_id'], $data['rating']) || !is_numeric($data['rating'])) {
    echo json_encode(['success' => false, 'error' => 'ข้อมูลไม่ครบถ้วน']);
    exit;
}

$course_id = (int)$data['courses_id'];
$rating = (int)$data['rating'];

// ตรวจสอบว่าคอร์สมีอยู่จริง
$checkCourse = $conn->prepare("SELECT courses_id FROM courses WHERE courses_id = ?");
$checkCourse->bind_param('i', $course_id);
$checkCourse->execute();
if (!$checkCourse->get_result()->num_rows) {
    echo json_encode(['success' => false, 'error' => 'ไม่พบหลักสูตร']);
    exit;
}
$checkCourse->close();

// บันทึก rating
$stmt = $conn->prepare("INSERT INTO course_rating (courses_id, rating, user_id) VALUES (?, ?, ?)");
// ถ้าไม่มี user_id ให้ใช้ 0 หรือ session
$user_id = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;
$stmt->bind_param('iii', $course_id, $rating, $user_id);

if ($stmt->execute()) {
    // ดึงค่าเฉลี่ยใหม่
    $avgStmt = $conn->prepare("SELECT AVG(rating) AS avg_rating, COUNT(*) AS cnt FROM course_rating WHERE courses_id = ?");
    $avgStmt->bind_param('i', $course_id);
    $avgStmt->execute();
    $result = $avgStmt->get_result()->fetch_assoc();
    
    echo json_encode([
        'success' => true,
        'avg' => round($result['avg_rating'], 2),
        'count' => $result['cnt']
    ]);
} else {
    echo json_encode(['success' => false, 'error' => $conn->error]);
}

$stmt->close();
?>