<?php
require_once 'db.php';
session_start();

function uploadFiles($files, $target_dir) {
    $uploaded_images = [];
    foreach ($files['tmp_name'] as $key => $tmp_name) {
        $file_name = preg_replace('/[^a-zA-Z0-9._-]/', '_', basename($files['name'][$key]));
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $file_type = finfo_file($finfo, $tmp_name);
        finfo_close($finfo);

        $file_size = $files['size'][$key];
        $target_path = $target_dir . $file_name;

        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        if (!in_array($file_type, $allowed_types)) {
            throw new Exception("ประเภทไฟล์ไม่ถูกต้อง! รองรับเฉพาะ JPG, PNG และ GIF");
        }

        if ($file_size > 2 * 1024 * 1024) {
            throw new Exception("ไฟล์ใหญ่เกินไป! ต้องไม่เกิน 2MB");
        }

        if (!move_uploaded_file($tmp_name, $target_path)) {
            throw new Exception("เกิดข้อผิดพลาดในการอัปโหลดไฟล์!");
        }

        $uploaded_images[] = $file_name;
    }
    return $uploaded_images;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $_SESSION['success'] = "การร้องขอไม่ถูกต้อง!";
        header("Location: add_product.php");
        exit;
    }

    $product_name = htmlspecialchars(trim($_POST['product_name']), ENT_QUOTES, 'UTF-8');
    $product_description = htmlspecialchars(trim($_POST['product_description']), ENT_QUOTES, 'UTF-8');
    $product_price = (float) $_POST['product_price'];
    $product_stock = (int) $_POST['product_stock'];

    try {
        $uploaded_images = [];
        if (!empty($_FILES['product_images']['name'][0])) {
            $uploaded_images = uploadFiles($_FILES['product_images'], "productsimage/");
        }

        $images_json = json_encode($uploaded_images);
        $stmt = $conn->prepare("INSERT INTO products (name, description, price, stock, images) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("ssdss", $product_name, $product_description, $product_price, $product_stock, $images_json);

        if ($stmt->execute()) {
            $_SESSION['success'] = "เพิ่มสินค้าสำเร็จ!";
        } else {
            error_log("Error: " . $stmt->error, 3, "error.log");
            $_SESSION['success'] = "เกิดข้อผิดพลาดในการเพิ่มสินค้า!";
        }

        $stmt->close();
    } catch (Exception $e) {
        $_SESSION['success'] = $e->getMessage();
    }

    header("Location: add_product.php");
    exit;
}
?>

<input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">