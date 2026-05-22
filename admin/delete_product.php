<?php
require_once 'auth.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'auth.php';
requireAdminRole('main');

$_SESSION['product_error'] = 'ระบบนี้ไม่อนุญาตให้ลบสินค้า กรุณาใช้ปุ่มปิดขายแทน';
header('Location: manage_product.php');
exit;
