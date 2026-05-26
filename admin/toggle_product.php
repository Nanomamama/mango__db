<?php
require_once 'auth.php';
requireAdminRole('main');
require_once __DIR__ . '/../db/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: manage_product.php');
    exit;
}

if (
    empty($_POST['csrf_token']) ||
    empty($_SESSION['csrf_token']) ||
    !hash_equals($_SESSION['csrf_token'], (string) $_POST['csrf_token'])
) {
    $_SESSION['product_error'] = 'การร้องขอไม่ถูกต้อง กรุณาลองใหม่อีกครั้ง';
    header('Location: manage_product.php');
    exit;
}

$id = (int) ($_POST['id'] ?? 0);
if ($id <= 0) {
    header('Location: manage_product.php');
    exit;
}

$stmt = $conn->prepare('SELECT status FROM products WHERE product_id = ? LIMIT 1');
$stmt->bind_param('i', $id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$product) {
    $_SESSION['product_error'] = 'ไม่พบสินค้า';
    header('Location: manage_product.php');
    exit;
}

$newStatus = $product['status'] === 'active' ? 'inactive' : 'active';
$update = $conn->prepare('UPDATE products SET status = ? WHERE product_id = ?');
$update->bind_param('si', $newStatus, $id);
$update->execute();
$update->close();

$_SESSION['success'] = $newStatus === 'active' ? 'เปิดขายสินค้าแล้ว' : 'ปิดขายสินค้าแล้ว';

$query = [];
if (!empty($_POST['status_filter']) && in_array($_POST['status_filter'], ['active', 'inactive'], true)) {
    $query['status'] = $_POST['status_filter'];
}
if (!empty($_POST['search'])) {
    $query['search'] = $_POST['search'];
}
if (!empty($_POST['category'])) {
    $query['category'] = $_POST['category'];
}

header('Location: manage_product.php' . ($query ? '?' . http_build_query($query) : ''));
exit;
