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

$lastCheck = isset($_GET['last_check']) ? (int) $_GET['last_check'] : 0;
$count = 0;
$newCount = 0;
$recentBookings = [];

$countResult = $conn->query("
    SELECT COUNT(*) AS count
    FROM bookings
    WHERE status = 'pending'
");

if ($countResult instanceof mysqli_result) {
    $row = $countResult->fetch_assoc();
    $count = (int) ($row['count'] ?? 0);
    $countResult->close();
} else {
    jsonResponse([
        'success' => false,
        'error' => 'Count query failed',
        'details' => $conn->error,
    ], 500);
}

$stmtNew = $conn->prepare("
    SELECT COUNT(*) AS count
    FROM bookings
    WHERE status = 'pending'
      AND UNIX_TIMESTAMP(created_at) > ?
");

if (!$stmtNew) {
    jsonResponse([
        'success' => false,
        'error' => 'New booking query prepare failed',
        'details' => $conn->error,
    ], 500);
}

$stmtNew->bind_param('i', $lastCheck);
$stmtNew->execute();
$newResult = $stmtNew->get_result();

if ($newResult instanceof mysqli_result) {
    $row = $newResult->fetch_assoc();
    $newCount = (int) ($row['count'] ?? 0);
    $newResult->close();
}

$stmtNew->close();

$recentResult = $conn->query("
    SELECT
        bookings_id AS id,
        booking_code,
        guest_name AS customer_name,
        booking_date,
        booking_time,
        created_at,
        status
    FROM bookings
    WHERE status = 'pending'
    ORDER BY created_at DESC, bookings_id DESC
    LIMIT 5
");

if ($recentResult instanceof mysqli_result) {
    while ($row = $recentResult->fetch_assoc()) {
        $recentBookings[] = [
            'id' => (int) ($row['id'] ?? 0),
            'booking_code' => (string) ($row['booking_code'] ?? ''),
            'customer_name' => (string) ($row['customer_name'] ?? 'ลูกค้าใหม่'),
            'booking_date' => (string) ($row['booking_date'] ?? ''),
            'booking_time' => (string) ($row['booking_time'] ?? ''),
            'created_at' => (string) ($row['created_at'] ?? ''),
            'time_ago' => formatTimeAgo($row['created_at'] ?? null),
            'status' => (string) ($row['status'] ?? 'pending'),
        ];
    }
    $recentResult->close();
} else {
    jsonResponse([
        'success' => false,
        'error' => 'Recent bookings query failed',
        'details' => $conn->error,
    ], 500);
}

echo json_encode([
    'success' => true,
    'count' => $count,
    'new_count' => $newCount,
    'recent_bookings' => $recentBookings,
    'current_time' => time(),
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

$conn->close();
