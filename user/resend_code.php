<?php
session_start();
require_once __DIR__ . '/../db/db.php';
require_once __DIR__ . '/password_reset_mailer.php';

if (!isset($_SESSION['reset_user_id'])) {
    header('Location: password_recovery.php');
    exit();
}

if (!isset($pdo)) {
    try {
        $pdo = new PDO("mysql:host={$servername};dbname={$dbname};charset=utf8mb4", $username, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    } catch (PDOException $e) {
        error_log('Failed to create PDO: ' . $e->getMessage());
        $_SESSION['error'] = 'ไม่สามารถเชื่อมต่อฐานข้อมูลได้';
        header('Location: password_recovery.php');
        exit();
    }
}

$user_id = $_SESSION['reset_user_id'];
$stmt = $pdo->prepare("SELECT member_id AS id, email, fullname FROM members WHERE member_id = ? LIMIT 1");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if (!$user) {
    $_SESSION['error'] = 'ไม่พบผู้ใช้';
    header('Location: password_recovery.php');
    exit();
}

$verification_code = (string) random_int(100000, 999999);
$stmt = $pdo->prepare("UPDATE members SET verification_code = ?, code_expire = DATE_ADD(NOW(), INTERVAL 5 MINUTE) WHERE member_id = ?");
$stmt->execute([$verification_code, $user_id]);

if (sendPasswordResetCodeEmail($user['email'], $verification_code, $user['fullname'])) {
    $_SESSION['current_step'] = 'verify_code';
    $_SESSION['success'] = 'ส่งรหัส OTP ใหม่ไปยังอีเมลของคุณแล้ว';
} else {
    $_SESSION['error'] = 'ไม่สามารถส่งรหัส OTP ทางอีเมลได้ กรุณาลองอีกครั้ง';
}

header('Location: password_recovery.php');
exit();
