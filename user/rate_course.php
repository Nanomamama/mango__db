<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

ini_set('display_errors', '0');
ini_set('log_errors', '1');
error_reporting(E_ALL);

require_once '../admin/db.php';

header('Content-Type: application/json; charset=utf-8');

// ตั้ง exception/error handlers
set_exception_handler(function($e) {
    error_log("rate_course exception: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Server error']);
    exit;
});

set_error_handler(function($errno, $errstr, $errfile, $errline) {
    error_log("rate_course error [$errno] $errstr in $errfile:$errline");
    throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
});

// อ่าน JSON input
$raw = file_get_contents('php://input');
$data = json_decode($raw, true);

if (!is_array($data)) {
    echo json_encode(['success' => false, 'error' => 'Invalid input']);
    exit;
}

$course_id = isset($data['course_id']) ? (int)$data['course_id'] : 0;
$rating = isset($data['rating']) ? (int)$data['rating'] : 0;

// Validation
if ($course_id <= 0 || $rating < 1 || $rating > 5) {
    echo json_encode(['success' => false, 'error' => 'Invalid course or rating']);
    exit;
}

// Verify course exists
$verifyCourse = $conn->prepare("SELECT courses_id FROM courses WHERE courses_id = ?");
if (!$verifyCourse) {
    throw new Exception('DB prepare error: ' . $conn->error);
}
$verifyCourse->bind_param('i', $course_id);
$verifyCourse->execute();
if ($verifyCourse->get_result()->num_rows === 0) {
    echo json_encode(['success' => false, 'error' => 'Course not found']);
    exit;
}
$verifyCourse->close();

// Get client IP as guest identifier
function getClientIP() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        return $_SERVER['HTTP_CLIENT_IP'];
    }
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $list = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        return trim($list[0]);
    }
    return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
}

$guestId = getClientIP();
$member_id = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;

mysqli_report(MYSQLI_REPORT_OFF);

$conn->begin_transaction();

try {
    // Check if already rated (by member_id or guest_identifier)
    if ($member_id) {
        $stmt = $conn->prepare("SELECT id FROM course_ratings WHERE course_id = ? AND member_id = ?");
        $stmt->bind_param('ii', $course_id, $member_id);
    } else {
        $stmt = $conn->prepare("SELECT id FROM course_ratings WHERE course_id = ? AND guest_identifier = ? AND member_id IS NULL");
        $stmt->bind_param('is', $course_id, $guestId);
    }

    if (!$stmt) {
        throw new Exception('Prepare select failed: ' . $conn->error);
    }

    $stmt->execute();
    $res = $stmt->get_result();

    if ($row = $res->fetch_assoc()) {
        // Update existing rating
        $updateStmt = $conn->prepare("UPDATE course_ratings SET rating = ?, created_at = NOW() WHERE id = ?");
        if (!$updateStmt) {
            throw new Exception('Prepare update failed: ' . $conn->error);
        }
        $updateStmt->bind_param('ii', $rating, $row['id']);
        if (!$updateStmt->execute()) {
            throw new Exception('Update failed: ' . $updateStmt->error);
        }
        $updateStmt->close();
    } else {
        // Insert new rating
        $insertStmt = $conn->prepare("INSERT INTO course_ratings (course_id, member_id, guest_identifier, rating, created_at) VALUES (?, ?, ?, ?, NOW())");
        if (!$insertStmt) {
            throw new Exception('Prepare insert failed: ' . $conn->error);
        }
        $insertStmt->bind_param('iiii', $course_id, $member_id, $guestId, $rating);
        if (!$insertStmt->execute()) {
            throw new Exception('Insert failed: ' . $insertStmt->error);
        }
        $insertStmt->close();
    }

    $stmt->close();

    // Get updated average and count
    $avgStmt = $conn->prepare("SELECT AVG(rating) AS avg_rating, COUNT(*) AS cnt FROM course_ratings WHERE course_id = ?");
    if (!$avgStmt) {
        throw new Exception('Prepare avg failed: ' . $conn->error);
    }

    $avgStmt->bind_param('i', $course_id);
    $avgStmt->execute();
    $avgRes = $avgStmt->get_result()->fetch_assoc();
    $avg = $avgRes['avg_rating'] !== null ? round((float)$avgRes['avg_rating'], 2) : 0.00;
    $count = (int)$avgRes['cnt'];
    $avgStmt->close();

    $conn->commit();

    echo json_encode([
        'success' => true,
        'avg' => number_format($avg, 2, '.', ''),
        'count' => $count
    ]);
    exit;

} catch (Exception $e) {
    $conn->rollback();
    error_log('rate_course transaction error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Server error']);
    exit;
}
?>