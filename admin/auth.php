<?php
session_start();

if (!isset($_SESSION['admin_id'])) {

    // จำหน้าที่กำลังเปิด
    $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];

    // ไปหน้า login
    header("Location: admin_login.php");
    exit;
}

if (!isset($_SESSION['admin_role'])) {
    require_once __DIR__ . '/../db/db.php';

    $stmt = $conn->prepare("SELECT role FROM system_administrator WHERE id = ? LIMIT 1");
    if ($stmt) {
        $stmt->bind_param("i", $_SESSION['admin_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        $admin = $result->fetch_assoc();
        $_SESSION['admin_role'] = $admin['role'] ?? 'sub';
        $stmt->close();
    } else {
        $_SESSION['admin_role'] = 'sub';
    }
}
