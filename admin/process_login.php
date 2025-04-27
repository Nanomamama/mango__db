<?php
// filepath: c:\xampp\htdocs\mango\admin\process_login.php
session_start();
require_once 'db.php'; // ไฟล์เชื่อมต่อฐานข้อมูล

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // ตรวจสอบข้อมูลในฐานข้อมูล
    $stmt = $conn->prepare("SELECT id, password FROM admin_users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($id, $hashed_password);
        $stmt->fetch();

        // ตรวจสอบรหัสผ่าน
        if (password_verify($password, $hashed_password)) {
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_id'] = $id;
            header("Location: index.php"); // เปลี่ยนเส้นทางไปยังหน้า Dashboard
            exit();
        } else {
            $error_message = "รหัสผ่านไม่ถูกต้อง";
        }
    } else {
        $error_message = "ไม่พบชื่อผู้ใช้";
    }

    $stmt->close();
    $conn->close();
}

// แสดงข้อความข้อผิดพลาดและเปลี่ยนเส้นทางกลับไปยังหน้า Login
header("Location: admin_login.php?error=" . urlencode($error_message));
exit();
?>