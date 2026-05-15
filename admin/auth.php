<?php
session_start();

if (!isset($_SESSION['admin_id'])) {

    // จำหน้าที่กำลังเปิด
    $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];

    // ไปหน้า login
    header("Location: admin_login.php");
    exit;
}
