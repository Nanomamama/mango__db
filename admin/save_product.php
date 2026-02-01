<?php
require_once 'db.php';
session_start();

/* ---------- ตรวจ CSRF ---------- */
if (
    !isset($_POST['csrf_token']) ||
    !isset($_SESSION['csrf_token']) ||
    $_POST['csrf_token'] !== $_SESSION['csrf_token']
) {
    $_SESSION['success'] = "การร้องขอไม่ถูกต้อง";
    header("Location: add_product.php");
    exit;
}

/* ---------- รับค่าจากฟอร์ม ---------- */
$product_name  = trim($_POST['product_name']);
$description   = trim($_POST['product_description']);
$price         = (float) $_POST['product_price'];
$weight        = (float) $_POST['product_weight'];
$stock         = (int) $_POST['product_stock'];
$min_stock     = (int) $_POST['product_min_stock'];

/* ---------- จัดการอัปโหลดรูป ---------- */
$image_name = null;

if (!empty($_FILES['product_image']['name'])) {

    $uploadDir = "uploads/products/";
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
    $file_tmp  = $_FILES['product_image']['tmp_name'];
    $file_size = $_FILES['product_image']['size'];
    $file_type = mime_content_type($file_tmp);

    if (!in_array($file_type, $allowed_types)) {
        $_SESSION['success'] = "รองรับเฉพาะ JPG, PNG, GIF";
        header("Location: add_product.php");
        exit;
    }

    if ($file_size > 2 * 1024 * 1024) {
        $_SESSION['success'] = "ไฟล์ต้องไม่เกิน 2MB";
        header("Location: add_product.php");
        exit;
    }

    $ext = pathinfo($_FILES['product_image']['name'], PATHINFO_EXTENSION);
    $image_name = time() . "_" . uniqid() . "." . $ext;

    move_uploaded_file($file_tmp, $uploadDir . $image_name);
}

/* ---------- INSERT ลงฐานข้อมูล ---------- */
$sql = "INSERT INTO products 
        (product_name, product_description, price, unit, stock_qty, productimage, created_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";

$stmt = $conn->prepare($sql);
$stmt->bind_param(
    "ssddiis",
    $product_name,
    $description,
    $price,
    $weight,
    $stock,
    $min_stock,
    $image_name
);

if ($stmt->execute()) {
    $_SESSION['success'] = "เพิ่มสินค้าสำเร็จ";
} else {
    $_SESSION['success'] = "เกิดข้อผิดพลาดในการบันทึกสินค้า";
}

$stmt->close();
header("Location: add_product.php");
exit;
