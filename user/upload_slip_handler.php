<?php
session_start();
require_once '../admin/db.php';

// ตรวจสอบการล็อกอิน
if (!isset($_SESSION['member_id'])) {
    header('Location: login.php?error=not_logged_in');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: receipt.php');
    exit;
}

$booking_id = isset($_POST['booking_id']) ? intval($_POST['booking_id']) : 0;
if (!$booking_id) {
    header('Location: receipt.php?error=missing_id');
    exit;
}

// ตรวจสอบว่าผู้ใช้มีสิทธิ์อัปโหลดสลิปสำหรับการจองนี้
$member_id = $_SESSION['member_id'];
$check_stmt = $conn->prepare("SELECT id FROM bookings WHERE id = ? AND member_id = ?");
$check_stmt->bind_param('ii', $booking_id, $member_id);
$check_stmt->execute();
$check_result = $check_stmt->get_result();

if ($check_result->num_rows === 0) {
    $check_stmt->close();
    header('Location: receipt.php?error=unauthorized');
    exit;
}
$check_stmt->close();

if (!isset($_FILES['slip']) || $_FILES['slip']['error'] !== UPLOAD_ERR_OK) {
    header('Location: receipt.php?id=' . $booking_id . '&error=upload_failed');
    exit;
}

$allowed_types = ['image/jpeg', 'image/png', 'application/pdf', 'image/jpg'];
$allowed_ext = ['jpg', 'jpeg', 'png', 'pdf'];
$max_file_size = 5 * 1024 * 1024; // 5MB

$file = $_FILES['slip'];
$tmp = $file['tmp_name'];
$file_size = $file['size'];
$original_name = $file['name'];
$ext = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));

// ตรวจสอบขนาดไฟล์
if ($file_size > $max_file_size) {
    header('Location: receipt.php?id=' . $booking_id . '&error=size');
    exit;
}

// ตรวจสอบประเภทไฟล์ (ทั้ง MIME type และ extension)
$mime_type = mime_content_type($tmp);
if (!in_array($mime_type, $allowed_types) || !in_array($ext, $allowed_ext)) {
    header('Location: receipt.php?id=' . $booking_id . '&error=invalid_type');
    exit;
}

// ตรวจสอบว่าไฟล์เป็นภาพจริงๆ (สำหรับไฟล์ภาพ)
if (strpos($mime_type, 'image/') === 0) {
    $image_info = getimagesize($tmp);
    if (!$image_info) {
        header('Location: receipt.php?id=' . $booking_id . '&error=invalid_image');
        exit;
    }
}

// สร้างโฟลเดอร์เป้าหมาย
$targetDir = __DIR__ . DIRECTORY_SEPARATOR . 'Paymentslip-Gardenreservation' . DIRECTORY_SEPARATOR;
if (!file_exists($targetDir)) {
    if (!mkdir($targetDir, 0755, true)) {
        header('Location: receipt.php?id=' . $booking_id . '&error=dir_create_failed');
        exit;
    }
}

// สร้างชื่อไฟล์ใหม่ที่ไม่ซ้ำ
$fileName = date('Ymd_His') . '_' . uniqid() . '.' . $ext;
$targetPath = $targetDir . $fileName;

// อัปโหลดไฟล์
if (!move_uploaded_file($tmp, $targetPath)) {
    header('Location: receipt.php?id=' . $booking_id . '&error=move_failed');
    exit;
}

// อัปเดตฐานข้อมูล
$stmt = $conn->prepare("UPDATE bookings SET slip = ?, status = 'รออนุมัติ' WHERE id = ?");
if (!$stmt) {
    // ลบไฟล์ที่อัปโหลดถ้าอัปเดตฐานข้อมูลล้มเหลว
    @unlink($targetPath);
    header('Location: receipt.php?id=' . $booking_id . '&error=db_prepare');
    exit;
}

$stmt->bind_param('si', $fileName, $booking_id);
if ($stmt->execute()) {
    $stmt->close();
    header('Location: receipt.php?id=' . $booking_id . '&upload=success');
    exit;
} else {
    $stmt->close();
    // ลบไฟล์ที่อัปโหลดถ้าอัปเดตฐานข้อมูลล้มเหลว
    @unlink($targetPath);
    header('Location: receipt.php?id=' . $booking_id . '&error=db_update');
    exit;
}

?>