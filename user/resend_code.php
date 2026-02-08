<?php
session_start();
require_once __DIR__ . '/../db/db.php';
require_once __DIR__ . '/send_sms_helper.php';

if (!isset($_SESSION['reset_user_id'])) {
    header('Location: password_recovery.php');
    exit();
}

$user_id = $_SESSION['reset_user_id'];
$stmt = $pdo->prepare("SELECT id, phone, fullname FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if (!$user) {
    $_SESSION['error'] = 'ไม่พบผู้ใช้';
    header('Location: password_recovery.php');
    exit();
}

$verification_code = rand(100000, 999999);
$stmt = $pdo->prepare("UPDATE users SET verification_code = ?, code_expire = DATE_ADD(NOW(), INTERVAL 5 MINUTE) WHERE id = ?");
$stmt->execute([$verification_code, $user_id]);

if (sendSmsOtp($user['phone'], $verification_code, $user['fullname'])) {
    $_SESSION['success'] = 'ส่งรหัส OTP ใหม่เรียบร้อย';
} else {
    $_SESSION['error'] = 'ไม่สามารถส่งรหัส OTP ได้ กรุณาลองอีกครั้ง';
}

header('Location: password_recovery.php');
exit();
