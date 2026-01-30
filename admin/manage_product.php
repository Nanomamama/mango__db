<?php
require_once 'auth.php';
require_once 'db.php';

/* นับออเดอร์รอยืนยัน */
// $orderCount = 0;
// $res = $conn->query("SELECT COUNT(*) cnt FROM orders WHERE status = 'รอยืนยัน'");
// if ($res) {
//     $orderCount = $res->fetch_assoc()['cnt'];
// }
?>

<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<title>จัดการสินค้า</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
.product-card {
    transition: transform .2s, box-shadow .2s;
}
.product-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 20px rgba(0,0,0,.1);
}
.product-img {
    height: 250px;
    width: 100%;
    object-fit: cover;
}
.stock-low {
    border: 2px solid #dc3545;
}
</style>
</head>

<body>
<?php include 'sidebar.php'; ?>

<div class="container-fluid" style="margin-left:250px">
    <div class="p-4">

        <h2 class="mb-3">📦 จัดการสินค้า</h2>

        <a href="add_product.php" class="btn btn-primary mb-3">➕ เพิ่มสินค้า</a>
        <a href="order_product.php" class="btn btn-warning mb-3">
            <!-- คำสั่งซื้อ
            <?php if ($orderCount > 0): ?>
                <span class="badge bg-danger"><?= $orderCount ?></span>
            <?php endif; ?> -->
        </a>

        <div class="row g-4">

        <?php
        $sql = "SELECT * FROM products ORDER BY product_id DESC";
        $result = $conn->query($sql);

        while ($row = $result->fetch_assoc()):
            $lowStock = ($row['stock'] <= $row['min_stock']);
        ?>

        <div class="col-md-4 col-lg-3">
            <div class="card product-card <?= $lowStock ? 'stock-low' : '' ?>">

                <?php if ($row['image']): ?>
                    <img src="uploads/products/<?= htmlspecialchars($row['image']) ?>" class="card-img-top product-img">
                <?php else: ?>
                    <div class="text-center py-5 text-muted">ไม่มีรูป</div>
                <?php endif; ?>

                <div class="card-body">
                    <h5 class="card-title"><?= htmlspecialchars($row['product_name']) ?></h5>

                    <p class="mb-1">💰 <?= number_format($row['price'],2) ?> บาท</p>
                    <p class="mb-1">📦 คงเหลือ <?= $row['stock'] ?> ชิ้น</p>

                    <?php if ($lowStock): ?>
                        <span class="badge bg-danger">⚠ ใกล้หมด</span>
                    <?php else: ?>
                        <span class="badge bg-success">พร้อมขาย</span>
                    <?php endif; ?>

                    <div class="mt-3 d-flex gap-2">
                        <a href="edit_product.php?id=<?= $row['product_id'] ?>" class="btn btn-warning btn-sm">✏️</a>

                        <a href="delete_product.php?product_id=<?= $row['product_id'] ?>"
                           onclick="return confirm('ยืนยันการลบสินค้า?')"
                           class="btn btn-danger btn-sm">🗑</a>
                    </div>
                </div>
            </div>
        </div>

        <?php endwhile; ?>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
