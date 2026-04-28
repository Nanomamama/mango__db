<?php
ob_start();
error_reporting(0);
header('Content-Type: application/json; charset=utf-8');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../db/db.php';

function json_exit($arr) {
    ob_clean();
    echo json_encode($arr, JSON_UNESCAPED_UNICODE);
    exit;
}

function build_guest_identifier($courses_id) {
    if (!isset($_SESSION['course_guest_identifiers']) || !is_array($_SESSION['course_guest_identifiers'])) {
        $_SESSION['course_guest_identifiers'] = [];
    }

    if (!empty($_SESSION['member_id'])) {
        return 'member_' . (int)$_SESSION['member_id'];
    }

    if (!isset($_SESSION['course_guest_identifiers'][$courses_id])) {
        $booking_id = $_SESSION['temp_booking_id'] ?? 'guest';
        $_SESSION['course_guest_identifiers'][$courses_id] = substr(
            hash('sha256', session_id() . '|' . $booking_id . '|' . $courses_id),
            0,
            32
        ) . '_' . $booking_id;
    }

    return $_SESSION['course_guest_identifiers'][$courses_id];
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
if (!$res || $res->num_rows === 0) {
    $checkCourse->close();
    json_exit(['success' => false, 'error' => 'ไม่พบหลักสูตร']);
}
$checkCourse->close();

$member_id = isset($_SESSION['member_id']) ? (int)$_SESSION['member_id'] : null;
$guest_identifier = build_guest_identifier($courses_id);
$mode = 'created';

if ($member_id) {
    $checkStmt = $conn->prepare("
        SELECT rating_id
        FROM course_rating
        WHERE courses_id = ? AND member_id = ?
        LIMIT 1
    ");
    if (!$checkStmt) {
        json_exit(['success' => false, 'error' => 'Prepare error']);
    }
    $checkStmt->bind_param('ii', $courses_id, $member_id);
    $checkStmt->execute();
    $existing = $checkStmt->get_result()->fetch_assoc();
    $checkStmt->close();

    if ($existing) {
        $stmt = $conn->prepare("
            UPDATE course_rating
            SET rating = ?, guest_identifier = ?, created_at = NOW()
            WHERE rating_id = ?
        ");
        if (!$stmt) {
            json_exit(['success' => false, 'error' => 'Prepare error']);
        }
        $rating_id = (int)$existing['rating_id'];
        $stmt->bind_param('isi', $rating, $guest_identifier, $rating_id);
        $mode = 'updated';
    } else {
        $stmt = $conn->prepare("
            INSERT INTO course_rating (courses_id, member_id, guest_identifier, rating, created_at)
            VALUES (?, ?, ?, ?, NOW())
        ");
        if (!$stmt) {
            json_exit(['success' => false, 'error' => 'Prepare error']);
        }
        $stmt->bind_param('iisi', $courses_id, $member_id, $guest_identifier, $rating);
    }
} else {
    $checkStmt = $conn->prepare("
        SELECT rating_id
        FROM course_rating
        WHERE courses_id = ? AND guest_identifier = ?
        LIMIT 1
    ");
    if (!$checkStmt) {
        json_exit(['success' => false, 'error' => 'Prepare error']);
    }
    $checkStmt->bind_param('is', $courses_id, $guest_identifier);
    $checkStmt->execute();
    $existing = $checkStmt->get_result()->fetch_assoc();
    $checkStmt->close();

    if ($existing) {
        $stmt = $conn->prepare("
            UPDATE course_rating
            SET rating = ?, created_at = NOW()
            WHERE rating_id = ?
        ");
        if (!$stmt) {
            json_exit(['success' => false, 'error' => 'Prepare error']);
        }
        $rating_id = (int)$existing['rating_id'];
        $stmt->bind_param('ii', $rating, $rating_id);
        $mode = 'updated';
    } else {
        $stmt = $conn->prepare("
            INSERT INTO course_rating (courses_id, guest_identifier, rating, created_at)
            VALUES (?, ?, ?, NOW())
        ");
        if (!$stmt) {
            json_exit(['success' => false, 'error' => 'Prepare error']);
        }
        $stmt->bind_param('isi', $courses_id, $guest_identifier, $rating);
    }
}

if (!$stmt->execute()) {
    $error = $stmt->error;
    $stmt->close();
    json_exit(['success' => false, 'error' => 'Execute error: ' . $error]);
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
    'avg' => round((float)$result['avg_rating'], 2),
    'count' => (int)$result['cnt'],
    'guest_identifier' => $guest_identifier,
    'mode' => $mode
]);
