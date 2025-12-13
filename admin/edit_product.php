<?php
require_once 'auth.php';
require_once 'db.php'; // เชื่อมต่อฐานข้อมูล

if (isset($_GET['id'])) {
    $id = intval($_GET['id']); // รับค่า id จาก URL และแปลงเป็นตัวเลข
    $query = "SELECT * FROM products WHERE product_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $products = $result->fetch_assoc(); // ดึงข้อมูลสินค้า
    } else {
        echo "ไม่พบสินค้าที่ต้องการแก้ไข";
        exit;
    }
} else {
    echo "ไม่มี ID สินค้า";
    exit;
}
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>แก้ไขสินค้า</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/product.css">
</head>

<body>
    <div class="container mt-5">
        <h2 class="mb-4">แก้ไขสินค้า</h2>

        <form action="update_product.php" method="POST" enctype="multipart/form-data">
            <!-- ส่ง ID สินค้าไปด้วย -->
            <input type="hidden" name="product_id" value="<?= $products['product_id'] ?>">

            <div class="mb-3">
                <label class="form-label">ชื่อสินค้า</label>
                <input type="text" class="form-control" name="product_name" value="<?= htmlspecialchars($products['product_name']) ?>" required>
            </div>

            <div class="mb-3">
                <label class="form-label">รายละเอียดสินค้า</label>
                <textarea class="form-control" name="product_description" rows="3" required><?= htmlspecialchars($products['description']) ?></textarea>
            </div>

            <div class="mb-3">
                <label class="form-label">ราคา (บาท)</label>
                <input type="number" class="form-control" name="product_price" value="<?= htmlspecialchars($products['price']) ?>" min="1" required>
            </div>

            <div class="mb-3">
                <label class="form-label">จำนวนสินค้าคงเหลือ</label>
                <input type="number" class="form-control" name="product_stock" value="<?= htmlspecialchars($products['stock']) ?>" min="0" required>
            </div>

            <div class="mb-3">
                <label class="form-label">รูปสินค้า (อัปโหลดใหม่หากต้องการเปลี่ยน)</label>
                <input type="file" class="form-control" name="product_images[]" accept="image/*" multiple>

                <div>
                    <?php if (!empty($products['image'])): ?>
                        <div class="mt-2">
                            <small>รูปปัจจุบัน:</small><br>
                            <img src="uploads/products/<?= htmlspecialchars($products['image']) ?>"
                                width="250" alt="รูปสินค้า"
                                class="border rounded">
                        </div>
                    <?php else: ?>
                        <p class="text-muted">ไม่มีรูปสินค้า</p>
                    <?php endif; ?>

                </div>
            </div>

            <button type="submit" class="btn btn-success">💾 บันทึกการเปลี่ยนแปลง</button>
            <a href="manage_product.php" class="btn btn-secondary">🔙 ย้อนกลับ</a>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>