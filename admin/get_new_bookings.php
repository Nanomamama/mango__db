<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json; charset=UTF-8');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

if (!isset($_SESSION['admin_id'])) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'error' => 'Unauthorized',
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

require_once __DIR__ . '/../db/db.php';
require_once __DIR__ . '/booking_notification_lib.php';

$conn->set_charset('utf8mb4');

function jsonResponse(array $payload, int $statusCode = 200): void
{
    http_response_code($statusCode);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function formatTimeAgo(?string $dateTime): string
{
    if (empty($dateTime)) {
        return 'ไม่ทราบเวลา';
    }

    $timestamp = strtotime($dateTime);
    if ($timestamp === false) {
        return 'ไม่ทราบเวลา';
    }

    $diff = time() - $timestamp;

    if ($diff < 60) {
        return 'เมื่อสักครู่';
    }

    if ($diff < 3600) {
        return floor($diff / 60) . ' นาทีที่แล้ว';
    }

    if ($diff < 86400) {
        return floor($diff / 3600) . ' ชั่วโมงที่แล้ว';
    }

    return floor($diff / 86400) . ' วันที่แล้ว';
}

function bookingStatusLabel(?string $status): string
{
    $labels = [
        'pending' => 'รอดำเนินการ',
        'awaiting_payment' => 'รอตรวจสอบชำระเงิน',
        'confirmed' => 'ยืนยันแล้ว',
        'completed' => 'เสร็จสิ้น',
        'no_show' => 'ไม่มาตามนัด',
        'cancelled' => 'ยกเลิก',
    ];

    return $labels[$status ?? ''] ?? ($status ?: 'ไม่ระบุสถานะ');
}

if (!admin_booking_notifications_ensure_mysqli($conn)) {
    jsonResponse([
        'success' => false,
        'error' => 'Notification table is not available',
        'details' => $conn->error,
    ], 500);
}

$count = 0;
$recentBookings = [];

$countResult = $conn->query("
    SELECT COUNT(*) AS count
    FROM admin_notifications
    WHERE ref_table = 'bookings'
      AND read_at IS NULL
");

if ($countResult instanceof mysqli_result) {
    $row = $countResult->fetch_assoc();
    $count = (int)($row['count'] ?? 0);
    $countResult->close();
} else {
    jsonResponse([
        'success' => false,
        'error' => 'Count query failed',
        'details' => $conn->error,
    ], 500);
}

$stmtRecent = $conn->prepare("
    SELECT
        n.id AS notification_id,
        n.type AS notification_type,
        n.created_at AS notification_time,
        b.bookings_id AS id,
        b.booking_code,
        b.guest_name AS customer_name,
        b.booking_date,
        b.booking_time,
        b.created_at,
        b.updated_at,
        b.status
    FROM admin_notifications n
    INNER JOIN bookings b ON b.bookings_id = n.ref_id
    WHERE n.ref_table = 'bookings'
      AND n.read_at IS NULL
    ORDER BY n.created_at DESC, n.id DESC
    LIMIT 10
");

if (!$stmtRecent) {
    jsonResponse([
        'success' => false,
        'error' => 'Recent bookings query prepare failed',
        'details' => $conn->error,
    ], 500);
}

$stmtRecent->execute();
$recentResult = $stmtRecent->get_result();

if ($recentResult instanceof mysqli_result) {
    while ($row = $recentResult->fetch_assoc()) {
        $status = (string)($row['status'] ?? '');
        $notificationTime = (string)($row['notification_time'] ?? $row['updated_at'] ?? $row['created_at'] ?? '');

        $recentBookings[] = [
            'notification_id' => (int)($row['notification_id'] ?? 0),
            'id' => (int)($row['id'] ?? 0),
            'booking_code' => (string)($row['booking_code'] ?? ''),
            'customer_name' => (string)($row['customer_name'] ?? 'ลูกค้าใหม่'),
            'booking_date' => (string)($row['booking_date'] ?? ''),
            'booking_time' => (string)($row['booking_time'] ?? ''),
            'created_at' => (string)($row['created_at'] ?? ''),
            'updated_at' => (string)($row['updated_at'] ?? ''),
            'notification_time' => $notificationTime,
            'notification_type' => (string)($row['notification_type'] ?? 'booking_created'),
            'time_ago' => formatTimeAgo($notificationTime),
            'status' => $status,
            'status_label' => bookingStatusLabel($status),
        ];
    }
    $recentResult->close();
}

$stmtRecent->close();

echo json_encode([
    'success' => true,
    'count' => $count,
    'new_count' => $count,
    'recent_bookings' => $recentBookings,
    'current_time' => time(),
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

$conn->close();
