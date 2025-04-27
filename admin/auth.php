<?php
// filepath: c:\xampp\htdocs\mango\admin\auth.php
session_start();

// ตรวจสอบว่าผู้ใช้เข้าสู่ระบบหรือไม่
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: admin_login.php"); // เปลี่ยนเส้นทางไปยังหน้า Login
    exit();
}
?>