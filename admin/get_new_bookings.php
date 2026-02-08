<?php
session_start();
header('Content-Type: application/json');
header('Cache-Control: no-cache, must-revalidate');

// ตรวจสอบสิทธิ์การเข้าถึง
if (!isset($_SESSION['admin_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

    require_once __DIR__ . '/../db/db.php';

$result = $conn->query("SELECT COUNT(*) as count FROM bookings WHERE status = 'pending'");
$row = $result->fetch_assoc();
$count = $row['count'] ?? 0;

echo json_encode(['count' => $count]);

$conn->close();