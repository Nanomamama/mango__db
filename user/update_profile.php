<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
if (!isset($_SESSION['member_id'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

$sessionToken = $_SESSION['csrf_token'] ?? '';
$postedToken = $_POST['csrf_token'] ?? '';
if (!is_string($postedToken) || $sessionToken === '' || !hash_equals($sessionToken, $postedToken)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'คำขอไม่ถูกต้อง กรุณารีเฟรชหน้าแล้วลองใหม่']);
    exit;
}

require_once __DIR__ . '/../db/db.php';

$member_id = (int)$_SESSION['member_id'];

$fullname = isset($_POST['fullname']) ? trim($_POST['fullname']) : '';
$phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$address = isset($_POST['address']) ? trim($_POST['address']) : '';
$province_id = isset($_POST['province_id']) && is_numeric($_POST['province_id']) ? (int)$_POST['province_id'] : null;
$district_id = isset($_POST['district_id']) && is_numeric($_POST['district_id']) ? (int)$_POST['district_id'] : null;
$subdistrict_id = isset($_POST['subdistrict_id']) && is_numeric($_POST['subdistrict_id']) ? (int)$_POST['subdistrict_id'] : null;
$zipcode = isset($_POST['zipcode']) ? trim($_POST['zipcode']) : '';

// basic validation
if ($fullname === '') {
    echo json_encode(['success' => false, 'error' => 'กรุณากรอกชื่อ-นามสกุล']);
    exit;
}

if ($phone !== '' && !preg_match('/^[0-9+\-\s()]{8,20}$/', $phone)) {
    echo json_encode(['success' => false, 'error' => 'กรุณากรอกเบอร์โทรศัพท์ให้ถูกต้อง']);
    exit;
}

if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'error' => 'กรุณากรอกอีเมลให้ถูกต้อง']);
    exit;
}

if ($zipcode !== '' && !preg_match('/^[0-9]{5}$/', $zipcode)) {
    echo json_encode(['success' => false, 'error' => 'กรุณากรอกรหัสไปรษณีย์ 5 หลัก']);
    exit;
}

// update statement
$query = "UPDATE members SET fullname = ?, phone = ?, email = ?, address = ?, province_id = ?, district_id = ?, subdistrict_id = ?, zipcode = ? WHERE member_id = ?";
$stmt = $conn->prepare($query);
if (!$stmt) {
    echo json_encode(['success' => false, 'error' => 'Prepare failed: ' . $conn->error]);
    exit;
}

$stmt->bind_param('ssssiiisi', $fullname, $phone, $email, $address, $province_id, $district_id, $subdistrict_id, $zipcode, $member_id);
$ok = $stmt->execute();
$stmt->close();

if ($ok) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => 'Database error']);
}

?>
