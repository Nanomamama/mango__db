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

$memberStatusStmt = $conn->prepare("SELECT status FROM members WHERE member_id = ? LIMIT 1");
if (!$memberStatusStmt) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error']);
    exit;
}
$memberStatusStmt->bind_param("i", $member_id);
$memberStatusStmt->execute();
$memberStatus = $memberStatusStmt->get_result()->fetch_assoc();
$memberStatusStmt->close();

if (!$memberStatus || (int)($memberStatus['status'] ?? 0) !== 1) {
    session_unset();
    session_destroy();
    http_response_code(403);
    echo json_encode(['error' => 'Member account is disabled']);
    exit;
}

function bookingStatusLabel(string $status, ?string $paymentSlip): string
{
    switch ($status) {
        case 'pending':
            return 'รอเจ้าหน้าที่ตรวจสอบ';
        case 'awaiting_payment':
            return $paymentSlip ? 'รอตรวจสอบสลิป' : 'รอชำระเงิน';
        case 'confirmed':
            return 'ยืนยันแล้ว';
        case 'cancelled':
            return 'ยกเลิกแล้ว';
        default:
            return $status;
    }
}

function thaiDate(?string $date): string
{
    if (!$date) return '';
    $timestamp = strtotime($date);
    if (!$timestamp) return $date;

    $months = [
        1 => 'มกราคม',
        2 => 'กุมภาพันธ์',
        3 => 'มีนาคม',
        4 => 'เมษายน',
        5 => 'พฤษภาคม',
        6 => 'มิถุนายน',
        7 => 'กรกฎาคม',
        8 => 'สิงหาคม',
        9 => 'กันยายน',
        10 => 'ตุลาคม',
        11 => 'พฤศจิกายน',
        12 => 'ธันวาคม',
    ];

    return date('j', $timestamp) . ' ' . $months[(int)date('n', $timestamp)] . ' ' . ((int)date('Y', $timestamp) + 543);
}

function thaiDateTime(?string $datetime): string
{
    if (!$datetime) return '';
    $timestamp = strtotime($datetime);
    if (!$timestamp) return $datetime;

    return thaiDate(date('Y-m-d', $timestamp)) . ' เวลา ' . date('H:i', $timestamp) . ' น.';
}

$stmt = $conn->prepare("
    SELECT bookings_id, booking_code, booking_date, booking_time, visitor_count, lunch_request,
           guest_name, guest_email, guest_phone, status, payment_slip, payment_qr_path,
           price_total, deposit_amount, balance_amount, booking_type, attachment_path,
           created_at, updated_at
    FROM bookings 
    WHERE member_id = ? 
    ORDER BY created_at DESC, bookings_id DESC
");

$stmt->bind_param("i", $member_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $status = (string)($row['status'] ?? '');
    $slip = $row['payment_slip'] ?? null;
    $time = $row['booking_time'] ? substr((string)$row['booking_time'], 0, 5) : '';

    $bookings[] = [
        'id' => (int)$row['bookings_id'],
        'bookings_id' => (int)$row['bookings_id'],
        'code' => (string)($row['booking_code'] ?? ''),
        'booking_code' => (string)($row['booking_code'] ?? ''),
        'name' => (string)($row['guest_name'] ?? ''),
        'guest_name' => (string)($row['guest_name'] ?? ''),
        'email' => (string)($row['guest_email'] ?? ''),
        'phone' => (string)($row['guest_phone'] ?? ''),
        'date' => thaiDate($row['booking_date'] ?? ''),
        'booking_date' => (string)($row['booking_date'] ?? ''),
        'time' => $time,
        'booking_time' => $time,
        'visitor_count' => (int)($row['visitor_count'] ?? 0),
        'lunch_request' => (int)($row['lunch_request'] ?? 0),
        'booking_type' => (string)($row['booking_type'] ?? ''),
        'status' => $status,
        'status_label' => bookingStatusLabel($status, $slip),
        'slip' => $slip,
        'payment_slip' => $slip,
        'qr' => $row['payment_qr_path'] ?? null,
        'payment_qr_path' => $row['payment_qr_path'] ?? null,
        'attachment_path' => $row['attachment_path'] ?? null,
        'total_amount' => (float)($row['price_total'] ?? 0),
        'price_total' => (float)($row['price_total'] ?? 0),
        'paid_amount' => (float)($row['deposit_amount'] ?? 0),
        'deposit_amount' => (float)($row['deposit_amount'] ?? 0),
        'remain_amount' => (float)($row['balance_amount'] ?? 0),
        'balance_amount' => (float)($row['balance_amount'] ?? 0),
        'created_at' => (string)($row['created_at'] ?? ''),
        'updated_at' => (string)($row['updated_at'] ?? ''),
        'created_at_display' => thaiDateTime($row['created_at'] ?? ''),
        'updated_at_display' => thaiDateTime($row['updated_at'] ?? ''),
    ];
}
$stmt->close();

echo json_encode([
    'success' => true,
    'data' => $bookings,
], JSON_UNESCAPED_UNICODE);
