<?php
session_start();
header('Content-Type: application/json');

require_once '../admin/db.php';

if (!$conn) {
    echo json_encode(['success' => false, 'error' => 'เกิดข้อผิดพลาดในการเชื่อมต่อฐานข้อมูล']);
    exit;
}

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
if (!$checkCourse) {
    echo json_encode(['success' => false, 'error' => 'Database error']);
    exit;
}
$checkCourse->bind_param('i', $courses_id);
$checkCourse->execute();
if (!$checkCourse->get_result()->num_rows) {
    echo json_encode(['success' => false, 'error' => 'ไม่พบหลักสูตร']);
    exit;
}
$checkCourse->close();

// ตรวจสอบสิทธิ์เข้าถึง (ต้องยืนยันรหัสก่อน)
if (!isset($_SESSION['course_access'])) {
    $_SESSION['course_access'] = [];
}

if (!in_array($courses_id, $_SESSION['course_access'])) {
    echo json_encode(['success' => false, 'error' => 'คุณยังไม่ได้ยืนยันการเข้าร่วมกิจกรรม']);
    exit;
}

// สร้าง unique identifier สำหรับการให้คะแนนแต่ละครั้ง
// Format: session_id + timestamp เพื่อให้แต่ละครั้งที่เข้ารหัสใหม่จะได้ identifier ใหม่
$member_id = $_SESSION['member_id'] ?? null;
$guest_identifier = session_id() . '_' . time();

// บันทึกคะแนน (INSERT ใหม่ทุกครั้ง - ไม่ UPDATE)
if ($member_id) {
    // User ที่ login - เก็บทั้ง member_id และ guest_identifier
    $stmt = $conn->prepare("
        INSERT INTO course_rating (courses_id, member_id, guest_identifier, rating, created_at)
        VALUES (?, ?, ?, ?, NOW())
    ");
    if (!$stmt) {
        echo json_encode(['success' => false, 'error' => 'Prepare error: ' . $conn->error]);
        exit;
    }
    $stmt->bind_param('iisi', $courses_id, $member_id, $guest_identifier, $rating);
} else {
    // Guest - เก็บเฉพาะ guest_identifier
    $stmt = $conn->prepare("
        INSERT INTO course_rating (courses_id, guest_identifier, rating, created_at)
        VALUES (?, ?, ?, NOW())
    ");
    if (!$stmt) {
        echo json_encode(['success' => false, 'error' => 'Prepare error: ' . $conn->error]);
        exit;
    }
    $stmt->bind_param("isi", $courses_id, $guest_identifier, $rating);
}

if ($stmt->execute()) {
    // ดึงค่าเฉลี่ยและจำนวนคะแนนทั้งหมด
    $avgStmt = $conn->prepare("
        SELECT AVG(rating) AS avg_rating, COUNT(*) AS cnt 
        FROM course_rating 
        WHERE courses_id = ?
    ");
    if (!$avgStmt) {
        echo json_encode(['success' => false, 'error' => 'Database error']);
        exit;
    }
    $avgStmt->bind_param('i', $courses_id);
    $avgStmt->execute();
    $result = $avgStmt->get_result()->fetch_assoc();
    $avgStmt->close();

    echo json_encode([
        'success' => true,
        'avg' => round($result['avg_rating'], 2),
        'count' => $result['cnt'],
        'guest_identifier' => $guest_identifier // ส่งกลับเพื่อใช้กับคอมเมนต์
    ]);
} else {
    echo json_encode(['success' => false, 'error' => 'Execute error: ' . $stmt->error]);
}

$stmt->close();
$conn->close();