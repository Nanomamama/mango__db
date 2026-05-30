<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json; charset=utf-8');

// Keep runtime errors out of the JSON response.
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);

// Ensure the database connection is included
require_once __DIR__ . '/../db/db.php';

// Check if $conn (MySQLi) is available
$use_mysqli = isset($conn) && $conn instanceof mysqli;

// Fallback to PDO if MySQLi is not available
if (!$use_mysqli) {
    if (!isset($pdo) && isset($servername, $username, $password, $dbname)) {
        try {
            $dsn = "mysql:host={$servername};dbname={$dbname};charset=utf8mb4";
            $pdo = new PDO($dsn, $username, $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'DB connection failed', 'debug' => $e->getMessage()]);
            exit;
        }
    }
}

// Get and validate query parameters
$start = isset($_GET['start']) ? $_GET['start'] : date('Y-m-d', strtotime('-15 days'));
$end   = isset($_GET['end'])   ? $_GET['end']   : date('Y-m-d', strtotime('+365 days'));
$current_member_id = isset($_SESSION['member_id']) ? (int)$_SESSION['member_id'] : 0;
$is_admin = isset($_SESSION['admin_id']);

if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $start) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $end)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Invalid date format for start or end']);
    exit;
}

function bookingResponseRow(array $r, bool $is_admin, int $current_member_id): array
{
    $memberId = $r['member_id'] !== null ? (int)$r['member_id'] : null;
    $canSeePrivate = $is_admin || ($current_member_id > 0 && $memberId === $current_member_id);

    return [
        'bookings_id' => $canSeePrivate ? (int)$r['bookings_id'] : null,
        'booking_code' => $canSeePrivate ? $r['booking_code'] : null,
        'member_id' => $canSeePrivate ? $memberId : null,
        'name' => $canSeePrivate ? $r['guest_name'] : 'Booked',
        'email' => $canSeePrivate ? $r['guest_email'] : null,
        'phone' => $canSeePrivate ? $r['guest_phone'] : null,
        'date' => $r['booking_date'],
        'time' => $canSeePrivate ? $r['booking_time'] : null,
        'visitor_count' => $canSeePrivate ? (int)$r['visitor_count'] : null,
        'lunch_request' => $canSeePrivate ? (int)$r['lunch_request'] : null,
        'price_total' => $canSeePrivate ? (float)$r['price_total'] : null,
        'deposit_amount' => $canSeePrivate ? (float)$r['deposit_amount'] : null,
        'balance_amount' => $canSeePrivate ? (float)$r['balance_amount'] : null,
        'status' => $r['status'],
        'is_member_booking' => $canSeePrivate ? (bool)$r['is_member_booking'] : null,
        'attachment_path' => $canSeePrivate ? $r['attachment_path'] : null,
        'payment_slip' => $canSeePrivate ? $r['payment_slip'] : null,
        'payment_qr_path' => $canSeePrivate ? $r['payment_qr_path'] : null,
        'created_at' => $canSeePrivate ? $r['created_at'] : null,
        'updated_at' => $canSeePrivate ? $r['updated_at'] : null
    ];
}

try {
    $out = [];
    if ($use_mysqli) {
        $sql = "SELECT bookings_id, booking_code, member_id, guest_name, guest_email, guest_phone, booking_date, booking_time, visitor_count, lunch_request, price_total, deposit_amount, balance_amount, status, is_member_booking, attachment_path, payment_slip, payment_qr_path, created_at, updated_at
                FROM bookings 
                WHERE booking_date BETWEEN ? AND ? 
                ORDER BY booking_date ASC";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new Exception('Prepare failed: ' . $conn->error);
        }
        $stmt->bind_param('ss', $start, $end);
        $stmt->execute();
        $res = $stmt->get_result();
        if (!$res) {
            throw new Exception('Query execution failed: ' . $conn->error);
        }
        while ($r = $res->fetch_assoc()) {
            $out[] = bookingResponseRow($r, $is_admin, $current_member_id);
        }
        $stmt->close();
    } else {
        if (!isset($pdo)) {
            throw new Exception('No DB connection available');
        }
        $sql = "SELECT bookings_id, booking_code, member_id, guest_name, guest_email, guest_phone, booking_date, booking_time, visitor_count, lunch_request, price_total, deposit_amount, balance_amount, status, is_member_booking, attachment_path, payment_slip, payment_qr_path, created_at, updated_at
                FROM bookings 
                WHERE DATE(booking_date) BETWEEN :start AND :end 
                ORDER BY booking_date ASC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':start' => $start, ':end' => $end]);
        $rows = $stmt->fetchAll();
        foreach ($rows as $r) {
            $out[] = bookingResponseRow($r, $is_admin, $current_member_id);
        }
    }

    echo json_encode($out);

} catch (Exception $e) {
    error_log($e->getMessage()); // Log the error for debugging
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Query failed']);
}
?>
