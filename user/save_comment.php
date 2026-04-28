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

function build_guest_identifier($course_id, $incoming_guest_id) {
    if (!isset($_SESSION['course_guest_identifiers']) || !is_array($_SESSION['course_guest_identifiers'])) {
        $_SESSION['course_guest_identifiers'] = [];
    }

    if (!empty($_SESSION['member_id'])) {
        return 'member_' . (int)$_SESSION['member_id'];
    }

    if ($incoming_guest_id !== '') {
        $_SESSION['course_guest_identifiers'][$course_id] = $incoming_guest_id;
        return $incoming_guest_id;
    }

    if (!isset($_SESSION['course_guest_identifiers'][$course_id])) {
        $booking_id = $_SESSION['temp_booking_id'] ?? 'guest';
        $_SESSION['course_guest_identifiers'][$course_id] = substr(
            hash('sha256', session_id() . '|' . $booking_id . '|' . $course_id),
            0,
            32
        ) . '_' . $booking_id;
    }

    return $_SESSION['course_guest_identifiers'][$course_id];
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_exit(['success' => false, 'error' => 'Invalid method']);
}

if (!$conn) {
    json_exit(['success' => false, 'error' => 'เกิดข้อผิดพลาดในการเชื่อมต่อฐานข้อมูล']);
}

$data = json_decode(file_get_contents('php://input'), true);

$courseId = (int)($data['courses_id'] ?? 0);
$userName = trim($data['user_name'] ?? '');
$commentText = trim($data['comment_text'] ?? '');
$incomingGuestId = trim($data['guest_identifier'] ?? '');
$token = $_SESSION['temp_access_token'] ?? '';

if ($courseId <= 0 || $userName === '' || $commentText === '') {
    json_exit(['success' => false, 'error' => 'กรุณากรอกข้อมูลให้ครบถ้วน']);
}

if (!isset($_SESSION['temp_access_token']) || $_SESSION['temp_access_token'] !== $token) {
    json_exit(['success' => false, 'error' => 'กรุณายืนยันรหัสใหม่อีกครั้ง']);
}

if (!isset($_SESSION['temp_access_time']) || (time() - $_SESSION['temp_access_time']) > 300) {
    unset($_SESSION['temp_access_token']);
    json_exit(['success' => false, 'error' => 'รหัสหมดอายุ กรุณายืนยันใหม่']);
}

$userName = mb_substr($userName, 0, 100);
$commentText = mb_substr($commentText, 0, 1000);
$member_id = isset($_SESSION['member_id']) ? (int)$_SESSION['member_id'] : null;
$guestId = build_guest_identifier($courseId, $incomingGuestId);
$mode = 'created';

if ($member_id) {
    $checkStmt = $conn->prepare("
        SELECT comment_id
        FROM course_comments
        WHERE courses_id = ? AND member_id = ?
        LIMIT 1
    ");
    if (!$checkStmt) {
        json_exit(['success' => false, 'error' => 'Prepare error: ' . $conn->error]);
    }
    $checkStmt->bind_param('ii', $courseId, $member_id);
    $checkStmt->execute();
    $existing = $checkStmt->get_result()->fetch_assoc();
    $checkStmt->close();

    if ($existing) {
        $stmt = $conn->prepare("
            UPDATE course_comments
            SET name = ?, comment_text = ?, guest_identifier = ?, created_at = NOW()
            WHERE comment_id = ?
        ");
        if (!$stmt) {
            json_exit(['success' => false, 'error' => 'Prepare error: ' . $conn->error]);
        }
        $comment_id = (int)$existing['comment_id'];
        $stmt->bind_param('sssi', $userName, $commentText, $guestId, $comment_id);
        $mode = 'updated';
    } else {
        $stmt = $conn->prepare("
            INSERT INTO course_comments (courses_id, member_id, name, comment_text, guest_identifier, created_at)
            VALUES (?, ?, ?, ?, ?, NOW())
        ");
        if (!$stmt) {
            json_exit(['success' => false, 'error' => 'Prepare error: ' . $conn->error]);
        }
        $stmt->bind_param('iisss', $courseId, $member_id, $userName, $commentText, $guestId);
    }
} else {
    $checkStmt = $conn->prepare("
        SELECT comment_id
        FROM course_comments
        WHERE courses_id = ? AND guest_identifier = ?
        LIMIT 1
    ");
    if (!$checkStmt) {
        json_exit(['success' => false, 'error' => 'Prepare error: ' . $conn->error]);
    }
    $checkStmt->bind_param('is', $courseId, $guestId);
    $checkStmt->execute();
    $existing = $checkStmt->get_result()->fetch_assoc();
    $checkStmt->close();

    if ($existing) {
        $stmt = $conn->prepare("
            UPDATE course_comments
            SET name = ?, comment_text = ?, created_at = NOW()
            WHERE comment_id = ?
        ");
        if (!$stmt) {
            json_exit(['success' => false, 'error' => 'Prepare error: ' . $conn->error]);
        }
        $comment_id = (int)$existing['comment_id'];
        $stmt->bind_param('ssi', $userName, $commentText, $comment_id);
        $mode = 'updated';
    } else {
        $stmt = $conn->prepare("
            INSERT INTO course_comments (courses_id, member_id, name, comment_text, guest_identifier, created_at)
            VALUES (?, ?, ?, ?, ?, NOW())
        ");
        if (!$stmt) {
            json_exit(['success' => false, 'error' => 'Prepare error: ' . $conn->error]);
        }
        $stmt->bind_param('iisss', $courseId, $member_id, $userName, $commentText, $guestId);
    }
}

if (!$stmt->execute()) {
    $error = $stmt->error;
    $stmt->close();
    json_exit(['success' => false, 'error' => 'Execute error: ' . $error]);
}

$stmt->close();

unset($_SESSION['temp_access_token']);
unset($_SESSION['temp_access_time']);

json_exit([
    'success' => true,
    'mode' => $mode,
    'comment' => [
        'user_name' => htmlspecialchars($userName, ENT_QUOTES, 'UTF-8'),
        'comment_text' => nl2br(htmlspecialchars($commentText, ENT_QUOTES, 'UTF-8')),
        'created_at' => date('Y-m-d H:i:s')
    ]
]);
