<?php
require_once 'db.php'; // เชื่อมต่อฐานข้อมูล

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['product_id']);
    $name = htmlspecialchars($_POST['product_name']);
    $description = htmlspecialchars($_POST['product_description']);
    $price = floatval($_POST['product_price']);
    $stock = intval($_POST['product_stock']);

    // จัดการอัปโหลดรูปภาพใหม่ (ถ้ามี)
    $uploaded_images = [];
    if (!empty($_FILES['product_images']['name'][0])) {
        foreach ($_FILES['product_images']['tmp_name'] as $key => $tmp_name) {
            $file_name = basename($_FILES['product_images']['name'][$key]);
            $target_path = "productsimage/" . $file_name;

            if (move_uploaded_file($tmp_name, $target_path)) {
                $uploaded_images[] = $file_name;
            }
        }
    }

    // อัปเดตข้อมูลในฐานข้อมูล
    $images_json = !empty($uploaded_images) ? json_encode($uploaded_images) : null;

    if ($images_json) {
        $query = "UPDATE products SET name = ?, description = ?, price = ?, stock = ?, images = ? WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ssdisi", $name, $description, $price, $stock, $images_json, $id);
    } else {
        $query = "UPDATE products SET name = ?, description = ?, price = ?, stock = ? WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ssdii", $name, $description, $price, $stock, $id);
    }

    if ($stmt->execute()) {
        header("Location: manage_product.php?success=แก้ไขสินค้าสำเร็จ");
    } else {
        header("Location: manage_product.php?error=เกิดข้อผิดพลาดในการแก้ไขสินค้า");
    }

    $stmt->close();
}
?>