<?php
require_once 'auth.php';
require_once 'db.php';

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $query = "SELECT * FROM products WHERE product_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $product = $result->fetch_assoc();
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
<title>แก้ไขสินค้า</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
.container-box {
    max-width: 900px;
    background: #edeaff;
    padding: 2rem;
    border-radius: 1rem;
    box-shadow: 0 0 10px rgba(0,0,0,.05);
}
.container {
    margin-left: 250px;
}
</style>
</head>

<body>
<?php include 'sidebar.php'; ?>

<div class="container">
<div class="container-box mx-auto mt-5">
<h2 class="mb-4"><strong>แก้ไขสินค้า</strong></h2>

<form action="update_product.php" method="POST" enctype="multipart/form-data">
<input type="hidden" name="product_id" value="<?= $product['product_id'] ?>">

<div class="row">

<!-- คอลัมน์ 1 -->
<div class="col-md-4">
    <div class="mb-3">
        <label class="form-label">ชื่อสินค้า</label>
        <input type="text" class="form-control"
               name="product_name"
               value="<?= htmlspecialchars($product['product_name']) ?>" required>
    </div>

    <div class="mb-3">
        <label class="form-label">หมวดสินค้า</label>
        <input type="text" class="form-control"
               name="category"
               value="<?= htmlspecialchars($product['category']) ?>" required>
    </div>

    <div class="mb-3">
        <label class="form-label">รายละเอียดสินค้า</label>
        <textarea class="form-control"
                  name="product_description"
                  rows="4"><?= htmlspecialchars($product['product_description']) ?></textarea>
    </div>
</div>

<!-- คอลัมน์ 2 -->
<div class="col-md-4">
    <div class="mb-3">
        <label class="form-label">ราคา (บาท)</label>
        <input type="number" class="form-control"
               name="price"
               value="<?= $product['price'] ?>" required>
    </div>

    <div class="mb-3">
        <label class="form-label">หน่วย</label>
        <input type="text" class="form-control"
               name="unit"
               value="<?= htmlspecialchars($product['unit']) ?>">
    </div>
</div>

<!-- คอลัมน์ 3 -->
<div class="col-md-4">
    <div class="mb-3">
        <label class="form-label">ตามฤดูกาล</label>
        <select name="seasonal" class="form-select">
            <option value="1" <?= $product['seasonal']==1?'selected':'' ?>>ใช่</option>
            <option value="0" <?= $product['seasonal']==0?'selected':'' ?>>ไม่ใช่</option>
        </select>
    </div>

    <div class="mb-3">
        <label class="form-label">สถานะสินค้า</label>
        <select name="status" class="form-select">
            <option value="active" <?= $product['status']=='active'?'selected':'' ?>>
                เปิดขาย
            </option>
            <option value="inactive" <?= $product['status']=='inactive'?'selected':'' ?>>
                ปิดขาย
            </option>
        </select>
        <small class="text-muted">
            * ถ้าปิดขาย สินค้าจะไม่แสดงให้ลูกค้าเห็น
        </small>
    </div>

    <div class="mb-3">
        <label class="form-label">รูปสินค้า</label>
        <input type="file" class="form-control"
               name="product_image" accept="image/*">

        <?php if (!empty($product['product_image'])): ?>
            <img src="uploads/products/<?= htmlspecialchars($product['product_image']) ?>"
                 class="img-fluid mt-2 border rounded">
        <?php endif; ?>
    </div>
</div>

</div>

<div class="d-flex justify-content-between mt-4">
    <a href="manage_product.php" class="btn btn-secondary">🔙 กลับ</a>
    <button type="submit" class="btn btn-success">💾 บันทึกการแก้ไข</button>
</div>

</form>
</div>
</div>
</body>
</html>
