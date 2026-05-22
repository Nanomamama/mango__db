<?php
require_once 'auth.php';
requireAdminRole('main');
require_once __DIR__ . '/../db/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function redirectEditProduct(int $productId, string $message): never
{
    $_SESSION['product_error'] = $message;
    header('Location: edit_product.php?id=' . $productId);
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

function deleteProductImage(string $uploadDir, string $filename): void
{
    $uploadRoot = realpath($uploadDir);
    if ($uploadRoot === false || $filename === '' || basename($filename) !== $filename) {
        return;
    }

    $target = realpath($uploadRoot . DIRECTORY_SEPARATOR . $filename);
    if ($target === false || !is_file($target)) {
        return;
    }

    $rootPrefix = rtrim($uploadRoot, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
    if (strncmp($target, $rootPrefix, strlen($rootPrefix)) === 0) {
        unlink($target);
    }
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: manage_product.php');
    exit;
}

$product_id = (int) ($_POST['product_id'] ?? 0);
if ($product_id <= 0) {
    header('Location: manage_product.php?error=' . urlencode('ไม่มีรหัสสินค้า'));
    exit;
}

if (
    empty($_POST['csrf_token']) ||
    empty($_SESSION['csrf_token']) ||
    !hash_equals($_SESSION['csrf_token'], (string) $_POST['csrf_token'])
) {
    redirectEditProduct($product_id, 'การร้องขอไม่ถูกต้อง กรุณาลองใหม่อีกครั้ง');
}

$name = trim((string) ($_POST['product_name'] ?? ''));
$category = trim((string) ($_POST['category'] ?? ''));
$description = trim((string) ($_POST['product_description'] ?? ''));
$price = (float) ($_POST['price'] ?? 0);
$unit = trim((string) ($_POST['unit'] ?? ''));
$seasonal = (int) ($_POST['seasonal'] ?? 0);
$status = (string) ($_POST['status'] ?? 'inactive');
$deleteImage = isset($_POST['delete_image']);

if ($name === '' || $category === '' || $unit === '' || $price < 0) {
    redirectEditProduct($product_id, 'กรุณากรอกข้อมูลสินค้าที่จำเป็นให้ครบถ้วน');
}

if (!in_array($seasonal, [0, 1], true)) {
    $seasonal = 0;
}

if (!in_array($status, ['active', 'inactive'], true)) {
    $status = 'inactive';
}

$oldStmt = $conn->prepare('SELECT product_image FROM products WHERE product_id = ? LIMIT 1');
$oldStmt->bind_param('i', $product_id);
$oldStmt->execute();
$oldProduct = $oldStmt->get_result()->fetch_assoc();
$oldStmt->close();

if (!$oldProduct) {
    header('Location: manage_product.php?error=' . urlencode('ไม่พบสินค้า'));
    exit;
}

$uploadDir = __DIR__ . '/uploads/products/';
$currentImage = (string) ($oldProduct['product_image'] ?? '');
$newImageName = null;

if (!empty($_FILES['product_image']['name'])) {
    if (!is_dir($uploadDir) && !mkdir($uploadDir, 0755, true)) {
        redirectEditProduct($product_id, 'ไม่สามารถสร้างโฟลเดอร์อัปโหลดรูปภาพได้');
    }

    if (!is_uploaded_file($_FILES['product_image']['tmp_name'])) {
        redirectEditProduct($product_id, 'ไม่พบไฟล์รูปภาพที่อัปโหลด');
    }

    $allowedTypes = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/gif' => 'gif',
    ];
    $fileType = mime_content_type($_FILES['product_image']['tmp_name']);
    $fileSize = (int) $_FILES['product_image']['size'];

    if (!isset($allowedTypes[$fileType])) {
        redirectEditProduct($product_id, 'รองรับเฉพาะไฟล์ JPG, PNG และ GIF');
    }

    if ($fileSize > 2 * 1024 * 1024) {
        redirectEditProduct($product_id, 'ไฟล์รูปภาพต้องมีขนาดไม่เกิน 2MB');
    }

    $newImageName = productImageFilename($name, $allowedTypes[$fileType]);
    if (!move_uploaded_file($_FILES['product_image']['tmp_name'], $uploadDir . $newImageName)) {
        redirectEditProduct($product_id, 'ไม่สามารถอัปโหลดรูปภาพได้');
    }

    deleteProductImage($uploadDir, $currentImage);
} elseif ($deleteImage && $currentImage !== '') {
    deleteProductImage($uploadDir, $currentImage);
    $newImageName = '';
}

if ($newImageName !== null) {
    $sql = "UPDATE products SET
            product_name = ?,
            category = ?,
            price = ?,
            unit = ?,
            seasonal = ?,
            status = ?,
            product_image = ?,
            product_description = ?
            WHERE product_id = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ssdsisssi', $name, $category, $price, $unit, $seasonal, $status, $newImageName, $description, $product_id);
} else {
    $sql = "UPDATE products SET
            product_name = ?,
            category = ?,
            price = ?,
            unit = ?,
            seasonal = ?,
            status = ?,
            product_description = ?
            WHERE product_id = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ssdsissi', $name, $category, $price, $unit, $seasonal, $status, $description, $product_id);
}

if ($stmt->execute()) {
    $_SESSION['success'] = 'แก้ไขสินค้าสำเร็จ';
    header('Location: manage_product.php');
} else {
    redirectEditProduct($product_id, 'เกิดข้อผิดพลาด: ' . $stmt->error);
}

$stmt->close();
exit;
