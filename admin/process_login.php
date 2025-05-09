<?php
// filepath: c:\xampp\htdocs\mango\admin\process_login.php
session_start();
require_once 'db.php'; // ไฟล์เชื่อมต่อฐานข้อมูล

$login = $_POST['login'] ?? '';
$password = $_POST['password'] ?? '';

if (!$login || !$password) {
    header("Location: admin_login.php?error=กรุณากรอกข้อมูลให้ครบถ้วน");
    exit;
}

// ค้นหาจาก username หรือ email
$stmt = $conn->prepare("SELECT * FROM admin_users WHERE username = ? OR email = ? LIMIT 1");
$stmt->bind_param("ss", $login, $login);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if ($user && password_verify($password, $user['password'])) {
    $_SESSION['username'] = $user['username'];
    $_SESSION['admin_id'] = $user['id'];
    header("Location: index.php");
    exit;
} else {
    header("Location: admin_login.php?error=ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง");
    exit;
}
?>