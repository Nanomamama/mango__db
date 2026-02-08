<?php
session_start();
require_once __DIR__ . '/../db/db.php';

function get_client_ip() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) return $_SERVER['HTTP_CLIENT_IP'];
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) return explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0];
    return $_SERVER['REMOTE_ADDR'] ?? 'unknown';
}

function insert_login_log($conn, $member_id, $email, $ip, $ua, $success, $reason) {
    $sql = "INSERT INTO login_logs (member_id, email, ip_address, user_agent, success, reason) VALUES (?, ?, ?, ?, ?, ? )";
    $logStmt = $conn->prepare($sql);
    if ($logStmt) {
        $logStmt->bind_param("isssis", $member_id, $email, $ip, $ua, $success, $reason);
        $logStmt->execute();
        $logStmt->close();
    }
}

// ตรวจสอบว่ามาจากการ POST จริง
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        // บันทึกความพยายามที่มีข้อมูลไม่ครบ
        $ip = get_client_ip();
        $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
        insert_login_log($conn, 0, $email, $ip, $ua, 0, 'missing_fields');
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
            $ip = get_client_ip();
            $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
            $mid = isset($row['member_id']) ? (int)$row['member_id'] : 0;
            insert_login_log($conn, $mid, $email, $ip, $ua, 0, 'disabled');
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

            // บันทึกล็อกอินสำเร็จ
            $ip = get_client_ip();
            $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
            $mid = isset($row['member_id']) ? (int)$row['member_id'] : 0;
            insert_login_log($conn, $mid, $email, $ip, $ua, 1, 'success');

            header("Location: index.php");
            exit;
        } else {
            // บันทึกรหัสผ่านผิด
            $ip = get_client_ip();
            $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
            $mid = isset($row['member_id']) ? (int)$row['member_id'] : 0;
            insert_login_log($conn, $mid, $email, $ip, $ua, 0, 'wrong_password');
            echo "<script>alert('รหัสผ่านไม่ถูกต้อง');history.back();</script>";
            exit;
        }
    } else {
        // บันทึกไม่พบอีเมล
        $ip = get_client_ip();
        $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
        insert_login_log($conn, 0, $email, $ip, $ua, 0, 'not_found');
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
