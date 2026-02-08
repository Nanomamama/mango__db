<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../db/db.php';

if (!isset($_SESSION['member_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$member_id = (int)$_SESSION['member_id'];
$bookings = [];

$stmt = $conn->prepare("
    SELECT bookings_id, booking_date, booking_time, guest_name, status, rejection_reason, 
           approved_at, approved_by, payment_slip, price_total, deposit_amount, balance_amount 
    FROM bookings 
    WHERE member_id = ? 
    ORDER BY bookings_id DESC
");

$stmt->bind_param("i", $member_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $bookings[] = $row;
}
$stmt->close();

echo json_encode($bookings);
