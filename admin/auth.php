<?php
// filepath: c:\xampp\htdocs\mango\admin\auth.php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit;
}
?>