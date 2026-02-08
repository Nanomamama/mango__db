<?php
require_once 'auth.php';
require_once __DIR__ . '/../db/db.php';

header('Content-Type: application/json; charset=utf-8');

$member_id_filter = isset($_GET['member_id']) ? (int)$_GET['member_id'] : 0;
$activities = [];

if ($member_id_filter <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid member ID']);
    exit;
}

try {
    // 1. Get Bookings
    $stmt = $conn->prepare("SELECT booking_code, created_at, status FROM bookings WHERE member_id = ?");
    $stmt->bind_param('i', $member_id_filter);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $activities[] = [
            'type' => 'การจอง',
            'icon' => 'bi-calendar-check',
            'color' => 'bg-info-light text-info',
            'date' => $row['created_at'],
            'description' => 'สร้างการจองใหม่ รหัส #' . htmlspecialchars($row['booking_code']) . ' (สถานะ: ' . htmlspecialchars($row['status']) . ')'
        ];
    }
    $stmt->close();

    // 2. Get Comments
    $stmt = $conn->prepare("SELECT cc.comment_text, cc.created_at, c.course_name 
                            FROM course_comments cc 
                            JOIN courses c ON cc.courses_id = c.courses_id 
                            WHERE cc.member_id = ?");
    $stmt->bind_param('i', $member_id_filter);
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $activities[] = [
                'type' => 'ความคิดเห็น',
                'icon' => 'bi-chat-dots',
                'color' => 'bg-warning-light text-warning',
                'date' => $row['created_at'],
                'description' => 'แสดงความคิดเห็นในกิจกรรม: "' . htmlspecialchars($row['course_name']) . '"'
            ];
        }
        $stmt->close();
    }

    // 3. Get successful logins
    $stmt = $conn->prepare("SELECT attempted_at, ip_address FROM login_logs WHERE member_id = ? AND success = 1");
    $stmt->bind_param('i', $member_id_filter);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $activities[] = [
            'type' => 'เข้าสู่ระบบ',
            'icon' => 'bi-box-arrow-in-right',
            'color' => 'bg-success-light text-success',
            'date' => $row['attempted_at'],
            'description' => 'เข้าสู่ระบบสำเร็จจาก IP: ' . htmlspecialchars($row['ip_address'])
        ];
    }
    $stmt->close();

    // Sort activities by date descending
    if (!empty($activities)) {
        usort($activities, function ($a, $b) {
            return strtotime($b['date']) - strtotime($a['date']);
        });
    }

    echo json_encode(['success' => true, 'activities' => $activities]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()]);
}

?>