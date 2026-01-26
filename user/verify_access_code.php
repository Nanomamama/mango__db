<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
error_reporting(0);
ob_start();

require_once '../admin/db.php';

function json_exit($arr) {
    ob_clean();
    echo json_encode($arr, JSON_UNESCAPED_UNICODE);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['code'])) {
    json_exit(['success' => false, 'error' => 'ข้อมูลไม่ครบถ้วน']);
}

$code4 = trim($data['code']);

if (!preg_match('/^\d{4}$/', $code4)) {
    json_exit(['success' => false, 'error' => 'กรุณากรอกเลข 4 หลัก']);
}

// ป้องกัน brute force
if (!isset($_SESSION['access_attempts'])) {
    $_SESSION['access_attempts'] = 0;
    $_SESSION['access_last_attempt'] = time();
}

if (time() - $_SESSION['access_last_attempt'] > 300) {
    $_SESSION['access_attempts'] = 0;
}

if ($_SESSION['access_attempts'] >= 5) {
    json_exit(['success' => false, 'error' => 'คุณพยายามมากเกินไป กรุณารอ 5 นาที']);
}

$stmt = $conn->prepare("
    SELECT bookings_id, booking_code
    FROM bookings
    WHERE booking_code LIKE CONCAT('%', ?)
      AND status = 'confirmed'
    LIMIT 1
");

if (!$stmt) {
    json_exit(['success' => false, 'error' => 'เกิดข้อผิดพลาดในระบบ']);
}

$stmt->bind_param('s', $code4);
$stmt->execute();
$res = $stmt->get_result();

if ($res && $res->num_rows > 0) {
    // ✅ สำเร็จ - สร้าง temporary token
    $_SESSION['access_attempts'] = 0;
    
    $booking = $res->fetch_assoc();
    
    // สร้าง token สำหรับใช้แค่ครั้งเดียว
    $token = bin2hex(random_bytes(16));
    $_SESSION['temp_access_token'] = $token;
    $_SESSION['temp_access_time'] = time();
    $_SESSION['temp_booking_id'] = $booking['bookings_id'];
    
    json_exit([
        'success' => true, 
        'message' => 'ยืนยันสำเร็จ',
        'token' => $token
    ]);
} else {
    // ❌ ไม่พบ
    $_SESSION['access_attempts']++;
    $_SESSION['access_last_attempt'] = time();
    
    $remaining = 5 - $_SESSION['access_attempts'];
    $error = 'รหัสไม่ถูกต้อง';
    
    if ($remaining > 0) {
        $error .= " (เหลือโอกาส $remaining ครั้ง)";
    }
    
    json_exit(['success' => false, 'error' => $error]);
}

$stmt->close();
$conn->close();