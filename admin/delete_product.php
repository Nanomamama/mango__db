<?php
require_once 'db.php'; // เชื่อมต่อฐานข้อมูล

if (isset($_GET['id'])) {
    $id = intval($_GET['id']); // รับค่า id จาก URL และแปลงเป็นตัวเลข

    $query = "DELETE FROM products WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        header("Location: manage_product.php?success=ลบสินค้าสำเร็จ");
    } else {
        header("Location: manage_product.php?error=เกิดข้อผิดพลาดในการลบสินค้า");
    }
    $stmt->close();
} else {
    header("Location: manage_product.php?error=ไม่มี ID สินค้า");
}
?>