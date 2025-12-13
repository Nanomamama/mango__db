<?php
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $product_id  = (int) $_POST['product_id'];
    $name        = trim($_POST['product_name']);
    $description = trim($_POST['product_description']);
    $price       = (float) $_POST['product_price'];
    $stock       = (int) $_POST['product_stock'];

    /* -----------------------------
       จัดการอัปโหลดรูป (รูปเดียว)
    ----------------------------- */
    $newImageName = null;

    if (!empty($_FILES['product_image']['name'])) {

        $uploadDir = "../uploads/products/";
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $ext = pathinfo($_FILES['product_image']['name'], PATHINFO_EXTENSION);
        $newImageName = time() . "_" . uniqid() . "." . $ext;
        $targetPath = $uploadDir . $newImageName;

        move_uploaded_file($_FILES['product_image']['tmp_name'], $targetPath);

        /* ลบรูปเก่า */
        $old = $conn->prepare("SELECT image FROM products WHERE product_id = ?");
        $old->bind_param("i", $product_id);
        $old->execute();
        $oldImg = $old->get_result()->fetch_assoc()['image'];

        if ($oldImg && file_exists($uploadDir . $oldImg)) {
            unlink($uploadDir . $oldImg);
        }
    }

    /* -----------------------------
       UPDATE ข้อมูล
    ----------------------------- */
    if ($newImageName) {
        $sql = "UPDATE products 
                SET product_name=?, description=?, price=?, stock=?, image=?
                WHERE product_id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssdisi", $name, $description, $price, $stock, $newImageName, $product_id);
    } else {
        $sql = "UPDATE products 
                SET product_name=?, description=?, price=?, stock=?
                WHERE product_id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssdii", $name, $description, $price, $stock, $product_id);
    }

    if ($stmt->execute()) {
        header("Location: manage_product.php?success=แก้ไขสินค้าสำเร็จ");
    } else {
        header("Location: manage_product.php?error=เกิดข้อผิดพลาด");
    }

    $stmt->close();
}
?>
