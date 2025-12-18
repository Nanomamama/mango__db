<?php
session_start();
require_once '../admin/db.php';

// ตรวจสอบว่ามาจากการ POST จริง
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        echo "<script>alert('กรุณากรอกอีเมลและรหัสผ่าน');history.back();</script>";
        exit;
    }

    // ตรวจสอบสถานะผู้ใช้ด้วย: ดึงฟิลด์ `status` มาด้วย
    $stmt = $conn->prepare("SELECT member_id, fullname, password, status FROM members WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        // หากมีการปิดใช้งาน (status == 0) ให้บล็อกการล็อกอิน
        if (isset($row['status']) && (int)$row['status'] === 0) {
            echo "<script>alert('บัญชีของคุณถูกปิดใช้งาน โปรดติดต่อผู้ดูแลระบบ');history.back();</script>";
            exit;
        }

        if (password_verify($password, $row['password'])) {
            // ตั้งค่า session
            $_SESSION['member_id'] = $row['member_id'];
            $_SESSION['fullname'] = $row['fullname'];
            $_SESSION['email'] = $email;

            // ป้องกัน session fixation
            session_regenerate_id(true);

            header("Location: index.php");
            exit;
        } else {
            echo "<script>alert('รหัสผ่านไม่ถูกต้อง');history.back();</script>";
            exit;
        }
    } else {
        echo "<script>alert('ไม่พบอีเมล์นี้ในระบบ');history.back();</script>";
        exit;
    }

    $stmt->close();
    $conn->close();
} else {
    // ถ้าเข้ามาหน้านี้โดยตรง (ไม่ใช่ POST)
    header("Location: member_login.php");
    exit;
}
