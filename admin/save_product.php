<?php
require_once 'auth.php';
requireAdminRole('main');
require_once __DIR__ . '/../db/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function redirectAddProduct(string $message): never
{
    $_SESSION['product_error'] = $message;
    header('Location: add_product.php');
    exit;
}

function productImageFilename(string $productName, string $extension): string
{
    $base = trim(mb_strtolower($productName, 'UTF-8'));
    $base = preg_replace('/[^\p{L}\p{N}]+/u', '-', $base) ?? '';
    $base = trim($base, '-');

    if ($base === '') {
        $base = 'product';
    }

    return $base . '-' . date('YmdHis') . '-' . bin2hex(random_bytes(4)) . '.' . $extension;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: add_product.php');
    exit;
}

if (
    empty($_POST['csrf_token']) ||
    empty($_SESSION['csrf_token']) ||
    !hash_equals($_SESSION['csrf_token'], (string) $_POST['csrf_token'])
) {
    redirectAddProduct('การร้องขอไม่ถูกต้อง กรุณาลองใหม่อีกครั้ง');
}

$product_name = trim((string) ($_POST['product_name'] ?? ''));
$category = trim((string) ($_POST['category'] ?? ''));
$description = trim((string) ($_POST['product_description'] ?? ''));
$price = (float) ($_POST['price'] ?? 0);
$unit = trim((string) ($_POST['unit'] ?? ''));
$seasonal = (int) ($_POST['seasonal'] ?? 0);
$status = (string) ($_POST['status'] ?? 'inactive');

if ($product_name === '' || $category === '' || $unit === '' || $price < 0) {
    redirectAddProduct('กรุณากรอกข้อมูลสินค้าที่จำเป็นให้ครบถ้วน');
}

if (!in_array($seasonal, [0, 1], true)) {
    $seasonal = 0;
}

if (!in_array($status, ['active', 'inactive'], true)) {
    $status = 'inactive';
}

$image_name = null;
$uploadDir = __DIR__ . '/uploads/products/';

if (!empty($_FILES['product_image']['name'])) {
    if (!is_dir($uploadDir) && !mkdir($uploadDir, 0755, true)) {
        redirectAddProduct('ไม่สามารถสร้างโฟลเดอร์อัปโหลดรูปภาพได้');
    }

    if (!is_uploaded_file($_FILES['product_image']['tmp_name'])) {
        redirectAddProduct('ไม่พบไฟล์รูปภาพที่อัปโหลด');
    }

    $allowedTypes = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/gif' => 'gif',
    ];
    $fileType = mime_content_type($_FILES['product_image']['tmp_name']);
    $fileSize = (int) $_FILES['product_image']['size'];

    if (!isset($allowedTypes[$fileType])) {
        redirectAddProduct('รองรับเฉพาะไฟล์ JPG, PNG และ GIF');
    }

    if ($fileSize > 2 * 1024 * 1024) {
        redirectAddProduct('ไฟล์รูปภาพต้องมีขนาดไม่เกิน 2MB');
    }

    $image_name = productImageFilename($product_name, $allowedTypes[$fileType]);
    if (!move_uploaded_file($_FILES['product_image']['tmp_name'], $uploadDir . $image_name)) {
        redirectAddProduct('ไม่สามารถอัปโหลดรูปภาพได้');
    }
}

$sql = "INSERT INTO products
        (product_name, category, price, unit, seasonal, status, product_image, product_description)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);
$stmt->bind_param(
    'ssdsisss',
    $product_name,
    $category,
    $price,
    $unit,
    $seasonal,
    $status,
    $image_name,
    $description
);

if ($stmt->execute()) {
    $_SESSION['success'] = 'เพิ่มสินค้าสำเร็จ';
    header('Location: manage_product.php');
} else {
    redirectAddProduct('เกิดข้อผิดพลาด: ' . $stmt->error);
}

$stmt->close();
exit;
