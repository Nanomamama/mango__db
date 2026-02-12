<?php
require_once 'auth.php';
require_once __DIR__ . '/../db/db.php';

// ดึงชื่อ admin จาก session
$admin_name = $_SESSION['admin_name'] ?? '';
$admin_email = $_SESSION['admin_email'] ?? '';

// Get counts for filter buttons
$sql_counts = "SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active_count,
    SUM(CASE WHEN status = 'inactive' THEN 1 ELSE 0 END) as inactive_count
    FROM products";
$counts_result = $conn->query($sql_counts);
$counts = $counts_result->fetch_assoc();

// Get current status for active button
$current_status = $_GET['status'] ?? 'all';

?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title>จัดการสินค้า</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>

        :root {
            --bs-primary-rgb: 67, 97, 238;
            --bs-success-rgb: 46, 204, 113;
            --bs-danger-rgb: 231, 76, 60;

            --primary: #4361ee;
            --secondary: #3f37c9;
            --success: #4cc9f0;
            --info: #36b9cc;
            --warning: #f6c23e;
            --danger: #e74a3b;
            --light: #f8f9fa;
            --dark: #212529;
            --purple: #7209b7;
            --teal: #20c997;
            --pink: #e83e8c;
            --cyan: #0dcaf0;
            --mango: #FFC107;
            --mango-dark: #E6A000;
        }

        body {
            font-family: 'Kanit', sans-serif;
            background-color: #f5f7fa;
        }

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


        .dashboard-header {
            background: linear-gradient(120deg, var(--primary), var(--secondary));
            color: white;
            padding: 1rem;
            box-shadow: 0 4px 12px rgba(67, 97, 238, 0.3);
            position: relative;
            overflow: hidden;
            z-index: 10;
            border-radius: 50px;
        }

        .dashboard-header::before {
            content: "";
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 0%, rgba(255, 255, 255, 0) 70%);
            pointer-events: none;
        }

        .admin-profile {
            display: flex;
            align-items: center;
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px);
            padding: 0.5rem 1rem;
            border-radius: 50px;
            transition: all 0.3s ease;
        }

        .admin-profile:hover {
            background: rgba(255, 255, 255, 0.3);
        }

        .admin-profile img {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            margin-right: 10px;
            border: 2px solid rgba(255, 255, 255, 0.5);
        }

        .admin-profile span {
            font-weight: 500;
            color: white;
            font-size: 0.9rem;
        }

    </style>
</head>

<body>
    <?php include 'sidebar.php'; ?>

    <div class="main-content">
        <div class="p-4">

                        <!-- Header -->
            <header class="dashboard-header">
                <div class="container">
                    <div class="d-flex justify-content-between align-items-center flex-wrap">
                        <div>
                            <h1 class="dashboard-title mb-0">จัดการสินค้า</h1>
                        </div>
                        <div class="d-flex align-items-center gap-3 mt-2 mt-md-0">
                            <div class="admin-profile">
                                <img src="https://ui-avatars.com/api/?name=<?= urlencode($admin_name) ?>&background=random&color=fff" alt="Admin">
                                <span><?= htmlspecialchars($admin_name) ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </header>
            <br>
            <div class="d-flex justify-content-between align-items-center mb-4">
                <!-- Filter Buttons -->
                <div class="d-flex gap-2">
                    <a href="manage_product.php" class="btn <?= $current_status == 'all' ? 'btn-dark' : 'btn-outline-dark' ?> rounded-pill">
                        <i class="bi bi-grid-3x3-gap-fill"></i> ทั้งหมด
                        <span class="badge text-bg-light ms-1"><?= $counts['total'] ?? 0 ?></span>
                    </a>
                    <a href="manage_product.php?status=active" class="btn <?= $current_status == 'active' ? 'btn-success' : 'btn-outline-success' ?> rounded-pill">
                        <i class="bi bi-check-circle-fill"></i> กำลังเปิดขาย
                        <span class="badge text-bg-light ms-1"><?= $counts['active_count'] ?? 0 ?></span>
                    </a>
                    <a href="manage_product.php?status=inactive" class="btn <?= $current_status == 'inactive' ? 'btn-secondary' : 'btn-outline-secondary' ?> rounded-pill">
                        <i class="bi bi-x-circle-fill"></i> ปิดขายไว้
                        <span class="badge text-bg-light ms-1"><?= $counts['inactive_count'] ?? 0 ?></span>
                    </a>
                </div>
                <!-- Add Product Button -->
                <a href="add_product.php" class="btn btn-primary rounded-pill shadow-sm"><i class="bi bi-plus-lg me-1"></i> เพิ่มสินค้าใหม่</a>
            </div>

            <div class="row g-4">

                <?php
                $where = "";
                if (isset($_GET['status'])) {
                    $status = $_GET['status'];
                    if (in_array($status, ['active', 'inactive'], true)) {
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