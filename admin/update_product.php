<?php
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $product_id  = (int) $_POST['product_id'];
    $name        = trim($_POST['product_name']);
    $category    = trim($_POST['category']);
    $description = trim($_POST['product_description']);
    $price       = (float) $_POST['price'];
    $unit        = trim($_POST['unit']);
    $seasonal    = (int) $_POST['seasonal'];
    $status      = trim($_POST['status']);

    /* -----------------------------
       จัดการอัปโหลดรูป
    ----------------------------- */
    $newImageName = null;
    $uploadDir = "uploads/products/";

    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    if (!empty($_FILES['product_image']['name'])) {

        $ext = pathinfo($_FILES['product_image']['name'], PATHINFO_EXTENSION);
        $newImageName = time() . "_" . uniqid() . "." . $ext;
        $targetPath = $uploadDir . $newImageName;

        move_uploaded_file($_FILES['product_image']['tmp_name'], $targetPath);

        /* ลบรูปเก่า */
        $old = $conn->prepare("SELECT product_image FROM products WHERE product_id = ?");
        $old->bind_param("i", $product_id);
        $old->execute();
        $oldImg = $old->get_result()->fetch_assoc()['product_image'];

        if ($oldImg && file_exists($uploadDir . $oldImg)) {
            unlink($uploadDir . $oldImg);
        }
    }

    /* -----------------------------
       UPDATE ข้อมูล
    ----------------------------- */
    if ($newImageName) {
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
        $stmt->bind_param(
            "ssdssissi",
            $name,
            $category,
            $price,
            $unit,
            $seasonal,
            $status,
            $newImageName,
            $description,
            $product_id
        );
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
        $stmt->bind_param(
            "ssdssisi",
            $name,
            $category,
            $price,
            $unit,
            $seasonal,
            $status,
            $description,
            $product_id
        );
    }

    if ($stmt->execute()) {
        header("Location: manage_product.php?success=แก้ไขสินค้าสำเร็จ");
    } else {
        header("Location: manage_product.php?error=" . $stmt->error);
    }

    $stmt->close();
}
?>
