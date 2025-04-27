<?php
// filepath: c:\xampp\htdocs\mango\admin\logout.php
session_start();
session_unset(); // ลบข้อมูลทั้งหมดใน Session
session_destroy(); // ทำลาย Session
header("Location: admin_login.php"); // เปลี่ยนเส้นทางไปยังหน้า Login
exit();
?>