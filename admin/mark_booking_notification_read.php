<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json; charset=UTF-8');
header('Cache-Control: no-cache, no-store, must-revalidate');

if (!isset($_SESSION['admin_id'])) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'error' => 'Unauthorized',
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'error' => 'Method not allowed',
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

require_once __DIR__ . '/../db/db.php';
require_once __DIR__ . '/booking_notification_lib.php';

$conn->set_charset('utf8mb4');

if (!admin_booking_notifications_ensure_mysqli($conn)) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Notification table is not available',
        'details' => $conn->error,
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

$rawBody = file_get_contents('php://input');
$payload = json_decode($rawBody ?: '{}', true);

if (!is_array($payload)) {
    $payload = [];
}

$action = (string)($payload['action'] ?? $_POST['action'] ?? '');
$notificationId = (int)($payload['notification_id'] ?? $_POST['notification_id'] ?? 0);

if ($action === 'all') {
    $ok = $conn->query("
        UPDATE admin_notifications
        SET read_at = NOW()
        WHERE ref_table = 'bookings'
          AND read_at IS NULL
    ");

    if (!$ok) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => 'Mark all as read failed',
            'details' => $conn->error,
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    echo json_encode([
        'success' => true,
        'updated' => $conn->affected_rows,
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

if ($notificationId <= 0) {
    http_response_code(422);
    echo json_encode([
        'success' => false,
        'error' => 'notification_id is required',
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

$stmt = $conn->prepare("
    UPDATE admin_notifications
    SET read_at = NOW()
    WHERE id = ?
      AND ref_table = 'bookings'
      AND read_at IS NULL
");

if (!$stmt) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Mark as read prepare failed',
        'details' => $conn->error,
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

$stmt->bind_param('i', $notificationId);
$ok = $stmt->execute();
$updated = $stmt->affected_rows;
$stmt->close();

if (!$ok) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Mark as read failed',
        'details' => $conn->error,
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

echo json_encode([
    'success' => true,
    'updated' => $updated,
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
