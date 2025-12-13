<?php
require_once 'auth.php';
require_once 'db.php';

if (!isset($_GET['product_id'])) {
    header("Location: manage_product.php?error=ไม่มีรหัสสินค้า");
    exit;
}

$product_id = (int) $_GET['product_id'];

/* ---------- ดึงชื่อรูปก่อนลบ ---------- */
$stmt = $conn->prepare("SELECT image FROM products WHERE product_id = ?");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    header("Location: manage_product.php?error=ไม่พบสินค้า");
    exit;
}

$product = $result->fetch_assoc();
$image = $product['image'];
$stmt->close();

/* ---------- ลบข้อมูลสินค้า ---------- */
$stmt = $conn->prepare("DELETE FROM products WHERE product_id = ?");
$stmt->bind_param("i", $product_id);

if ($stmt->execute()) {

    /* ---------- ลบรูปจากโฟลเดอร์ ---------- */
    if (!empty($image)) {
        $image_path = "uploads/products/" . $image;
        if (file_exists($image_path)) {
            unlink($image_path);
        }
    }

    header("Location: manage_product.php?success=ลบสินค้าสำเร็จ");
} else {
    header("Location: manage_product.php?error=ไม่สามารถลบสินค้าได้");
}

$stmt->close();
exit;
