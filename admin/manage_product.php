<?php
require_once 'auth.php';
require_once __DIR__ . '/../db/db.php';
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title>จัดการสินค้า</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .main-content {
            margin-left: 250px;
            padding: 20px;
            max-width: calc(100vw - 250px);
            overflow-x: hidden;
        }

        .product-card {
            transition: transform .2s, box-shadow .2s;
        }

        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, .1);
        }

        .product-img {
            height: 250px;
            width: 100%;
            object-fit: cover;
        }
    </style>
</head>

<body>
    <?php include 'sidebar.php'; ?>

    <div class="main-content">
        <div class="p-4">

            <h2 class="mb-3"> จัดการสินค้า</h2>

            <a href="add_product.php" class="btn btn-primary mb-3"><h5>➕ เพิ่มสินค้า</h5></a>
            <div class="mb-3">
                <a href="manage_product.php"
                    class="btn btn-outline-dark btn-sm"><h6>ทั้งหมด</h6></a>

                <a href="manage_product.php?status=active"
                    class="btn btn-outline-success btn-sm"><h6>กำลังเปิดขาย</h6></a>

                <a href="manage_product.php?status=inactive"
                    class="btn btn-outline-secondary btn-sm"><h6>ปิดขายไว้</h6></a>
            </div>

            <div class="row g-4">

                <?php
                $where = "";
                if (isset($_GET['status'])) {
                    $status = $_GET['status'];
                    if (in_array($status, ['active', 'inactive'])) {
                        $where = "WHERE status = '$status'";
                    }
                }

                $sql = "SELECT * FROM products $where ORDER BY product_id DESC";

                $result = $conn->query($sql);

                while ($row = $result->fetch_assoc()):
                ?>

                    <div class="col-md-4 ">
                        <div class="card product-card">

                            <?php if (!empty($row['product_image'])): ?>
                                <img src="uploads/products/<?= htmlspecialchars($row['product_image']) ?>"
                                    class="card-img-top product-img">
                            <?php else: ?>
                                <div class="text-center py-5 text-muted">ไม่มีรูป</div>
                            <?php endif; ?>

                            <div class="card-body">
                                <h5 class="card-title">
                                    <?= htmlspecialchars($row['product_name']) ?>
                                </h5>

                                <p class="mb-1">หมวด: <?= htmlspecialchars($row['category']) ?></p>
                                <p class="mb-1">💰 <?= number_format($row['price'], 2) ?> บาท / <?= $row['unit'] ?></p>

                                <?php if ($row['status'] == 'active'): ?>
                                    <span class="badge bg-success">พร้อมขาย</span>
                                <?php else: ?>
                                    <span class="badge bg-danger">ไม่พร้อมขาย</span>
                                <?php endif; ?>

                                <?php if ($row['seasonal'] == 1): ?>
                                    <span class="badge bg-warning text-dark ms-1">ตามฤดูกาล</span>
                                <?php endif; ?>

                                <div class="mt-3 d-flex gap-2">
                                    <a href="edit_product.php?id=<?= $row['product_id'] ?>"
                                        class="btn btn-warning btn-sm">✏️</a>

                                    <!-- <a href="delete_product.php?product_id=<?= $row['product_id'] ?>"
                                        onclick="return confirm('ยืนยันการลบสินค้า?')"
                                        class="btn btn-danger btn-sm">🗑</a> -->

                                    <a href="toggle_product.php?id=<?= $row['product_id'] ?>"
                                        class="btn btn-sm <?= $row['status'] == 'active' ? 'btn-secondary' : 'btn-success' ?>"
                                        onclick="return confirm('ต้องการเปลี่ยนสถานะสินค้านี้?')">

                                        <?= $row['status'] == 'active' ? 'ปิดการขาย' : 'เปิดขาย' ?>
                                    </a>

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