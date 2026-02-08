<?php
require_once 'auth.php';
require_once __DIR__ . '/../db/db.php';

if (!isset($_GET['id'])) {
    header("Location: manage_product.php");
    exit;
}

$id = (int) $_GET['id'];

/* ดึงสถานะปัจจุบัน */
$stmt = $conn->prepare("SELECT status FROM products WHERE product_id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$current = $stmt->get_result()->fetch_assoc()['status'];

/* สลับสถานะ */
$newStatus = ($current === 'active') ? 'inactive' : 'active';

$update = $conn->prepare("UPDATE products SET status = ? WHERE product_id = ?");
$update->bind_param("si", $newStatus, $id);
$update->execute();

header("Location: manage_product.php");
exit;
