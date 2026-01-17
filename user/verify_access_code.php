<?php
session_start();
header('Content-Type: application/json');

require_once '../admin/db.php';

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['courses_id'], $data['rating']) || !is_numeric($data['rating'])) {
    echo json_encode(['success' => false, 'error' => 'ข้อมูลไม่ครบถ้วน']);
    exit;
}

$courses_id = (int)$data['courses_id'];
$rating = (int)$data['rating'];

// ตรวจสอบว่า rating อยู่ในช่วง 1-5
if ($rating < 1 || $rating > 5) {
    echo json_encode(['success' => false, 'error' => 'คะแนนต้องอยู่ระหว่าง 1-5']);
    exit;
}

// ตรวจสอบว่าคอร์สมีอยู่จริง
$checkCourse = $conn->prepare("SELECT courses_id FROM courses WHERE courses_id = ?");
$checkCourse->bind_param('i', $courses_id);
$checkCourse->execute();
if (!$checkCourse->get_result()->num_rows) {
    echo json_encode(['success' => false, 'error' => 'ไม่พบหลักสูตร']);
    exit;
}
$checkCourse->close();

// กำหนด member_id และ guest_identifier
$member_id = $_SESSION['member_id'] ?? null;

// ถ้า login แล้ว ใช้ member_id, ถ้าไม่ ใช้ session_id
if ($member_id) {
    // User ที่ login - ใช้ member_id
    $stmt = $conn->prepare("
        INSERT INTO course_rating
        (courses_id, member_id, rating)
        VALUES (?, ?, ?)
        ON DUPLICATE KEY UPDATE rating = ?
    ");
    $stmt->bind_param('iiii', $courses_id, $member_id, $rating, $rating);
} else {
    // Guest - ใช้ guest_identifier
    $guest_id = session_id();
    $stmt = $conn->prepare("
        INSERT INTO course_rating
        (courses_id, guest_identifier, rating)
        VALUES (?, ?, ?)
        ON DUPLICATE KEY UPDATE rating = ?
    ");
    $stmt->bind_param('isii', $courses_id, $guest_id, $rating, $rating);
}

if ($stmt->execute()) {
    // ดึงค่าเฉลี่ยใหม่
    $avgStmt = $conn->prepare("SELECT AVG(rating) AS avg_rating, COUNT(*) AS cnt FROM course_rating WHERE courses_id = ?");
    $avgStmt->bind_param('i', $courses_id);
    $avgStmt->execute();
    $result = $avgStmt->get_result()->fetch_assoc();
    $avgStmt->close();
    
    echo json_encode([
        'success' => true,
        'avg' => round($result['avg_rating'], 2),
        'count' => $result['cnt']
    ]);
} else {
    echo json_encode(['success' => false, 'error' => $conn->error]);
}

$stmt->close();
$conn->close();
?>