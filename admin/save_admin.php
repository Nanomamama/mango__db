<?php
// filepath: c:\xampp\htdocs\mango\admin\save_admin.php

require_once 'db.php';
session_start();
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    die("CSRF Token ไม่ถูกต้อง");
}


// ตรวจสอบการเชื่อมต่อ
if ($conn->connect_error) {
    die("การเชื่อมต่อล้มเหลว: " . $conn->connect_error);
}

// รับข้อมูลจากฟอร์ม
$username = htmlspecialchars($_POST['username'], ENT_QUOTES, 'UTF-8');
$email = htmlspecialchars($_POST['email'], ENT_QUOTES, 'UTF-8');
$password = $_POST['password'];
$confirm_password = $_POST['confirm_password'];

// ตรวจสอบรหัสผ่าน
if ($password !== $confirm_password) {
    die("รหัสผ่านไม่ตรงกัน");
}

// เข้ารหัสรหัสผ่าน
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// ตรวจสอบว่ามีผู้ใช้ที่มีชื่อผู้ใช้นี้อยู่แล้วหรือไม่
$stmt = $conn->prepare("SELECT id FROM admin_users WHERE username = ? OR email = ?");
$stmt->bind_param("ss", $username, $email);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    die("Username หรือ Email นี้มีอยู่ในระบบแล้ว");
}
$stmt->close();

// บันทึกข้อมูลลงในฐานข้อมูล
$stmt = $conn->prepare("INSERT INTO admin_users (username, email, password) VALUES (?, ?, ?)");
$stmt->bind_param("sss", $username, $email, $hashed_password);

if ($stmt->execute()) {
    header("Location: admin_login.php");
    exit();
} else {
    echo "เกิดข้อผิดพลาด: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>

<form action="./save_admin.php" method="POST">