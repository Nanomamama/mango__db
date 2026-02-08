<?php
require_once 'auth.php';
require_once __DIR__ . '/../db/db.php';

$status = $_GET['status'] ?? 'all';

/* ================== QUERY LIST ORDER ================== */
$where = "";
if ($status != 'all') {
    $where = "WHERE o.order_status = '$status'";
}

$sql = "
SELECT 
    o.*,
    COALESCE(SUM(oi.quantity * oi.price),0) as total_amount
FROM orders o
LEFT JOIN order_items oi ON o.order_id = oi.order_id
$where
GROUP BY o.order_id
ORDER BY o.order_date DESC
";

$result = $conn->query($sql);


/* ================== QUERY STATS ================== */
$today = date('Y-m-d');

$sql_stats = "
SELECT 
    COUNT(DISTINCT o.order_id) as total_count,
    SUM(CASE WHEN o.order_status = 'pending' THEN 1 ELSE 0 END) as pending_count,
    SUM(CASE WHEN o.order_status = 'approved' THEN 1 ELSE 0 END) as approved_count,
    SUM(CASE WHEN o.order_status = 'rejected' THEN 1 ELSE 0 END) as rejected_count,
    SUM(CASE WHEN DATE(o.order_date) = '$today' THEN 1 ELSE 0 END) as today_count,
    COALESCE(SUM(oi.quantity * oi.price),0) as total_amount
FROM orders o
LEFT JOIN order_items oi ON o.order_id = oi.order_id
";

$result_stats = $conn->query($sql_stats);
$stats = $result_stats->fetch_assoc();
?>


<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>จัดการคำสั่งซื้อ - ระบบร้านสวนสุขใจ</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #2ecc71;
            --secondary-color: #27ae60;
            --warning-color: #f39c12;
            --danger-color: #e74c3c;
            --info-color: #3498db;
        }

        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', 'Prompt', sans-serif;
            padding: 20px;
            margin-left: 250px;
            padding: 20px;
            max-width: calc(100vw - 250px);
            overflow-x: hidden;
        }

        .stat-card {
            background-color: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            transition: transform 0.3s;
            height: 100%;
            margin-bottom: 20px;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-bottom: 15px;
        }

        .icon-all {
            background-color: rgba(149, 165, 166, 0.1);
            color: #7f8c8d;
        }

        .icon-pending {
            background-color: rgba(243, 156, 18, 0.1);
            color: var(--warning-color);
        }

        .icon-approved {
            background-color: rgba(46, 204, 113, 0.1);
            color: var(--primary-color);
        }

        .icon-rejected {
            background-color: rgba(231, 76, 60, 0.1);
            color: var(--danger-color);
        }

        .icon-today {
            background-color: rgba(52, 152, 219, 0.1);
            color: var(--info-color);
        }

        .icon-revenue {
            background-color: rgba(155, 89, 182, 0.1);
            color: #9b59b6;
        }

        .stat-number {
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 5px;
        }

        .stat-title {
            color: #666;
            font-weight: 500;
            font-size: 0.9rem;
        }

        .dashboard-card {
            background-color: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            margin-bottom: 25px;
        }

        .card-header {
            background-color: transparent;
            border-bottom: 2px solid #f0f0f0;
            padding-bottom: 15px;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .card-title {
            font-weight: 700;
            color: #2c3e50;
            font-size: 1.3rem;
            margin: 0;
        }

        .status-badge {
            padding: 6px 15px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
        }

        .badge-pending {
            background-color: #fff3cd;
            color: #856404;
        }

        .badge-approved {
            background-color: #d4edda;
            color: #155724;
        }

        .badge-rejected {
            background-color: #f8d7da;
            color: #721c24;
        }


        .btn-action {
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 0.85rem;
            text-decoration: none;
            margin-right: 5px;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .btn-approve {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .btn-approve:hover {
            background-color: #c3e6cb;
        }

        .btn-reject {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .btn-reject:hover {
            background-color: #f5c6cb;
        }

        .btn-view {
            background-color: #cce5ff;
            color: #004085;
            border: 1px solid #b8daff;
        }

        .btn-view:hover {
            background-color: #b8daff;
        }

        .btn-filter {
            padding: 8px 20px;
            border-radius: 20px;
            text-decoration: none;
            margin-right: 10px;
            font-weight: 500;
            border: 2px solid transparent;
            transition: all 0.3s;
        }

        .btn-filter-all {
            background-color: #e9ecef;
            color: #495057;
        }

        .btn-filter-all.active {
            background-color: #6c757d;
            color: white;
            border-color: #6c757d;
        }

        .btn-filter-pending {
            background-color: #fff3cd;
            color: #856404;
        }

        .btn-filter-pending.active {
            background-color: #ffc107;
            color: #212529;
            border-color: #ffc107;
        }

        .btn-filter-approved {
            background-color: #d4edda;
            color: #155724;
        }

        .btn-filter-approved.active {
            background-color: #28a745;
            color: white;
            border-color: #28a745;
        }

        .btn-filter-rejected {
            background-color: #f8d7da;
            color: #721c24;
        }

        .btn-filter-rejected.active {
            background-color: #dc3545;
            color: white;
            border-color: #dc3545;
        }

        .navbar {
            background-color: white;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            padding: 15px 25px;
            border-radius: 10px;

        }

        @media (max-width: 768px) {
            .order-table {
                display: block;
                overflow-x: auto;
            }
        }

        .order-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
        }

        @media (max-width: 992px) {
            .order-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 576px) {
            .order-grid {
                grid-template-columns: 1fr;
            }
        }

        .order-card {
            background: #fff;
            border-radius: 12px;
            padding: 15px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .order-card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #eee;
            padding-bottom: 8px;
            margin-bottom: 10px;
        }

        .order-card-body p {
            margin: 4px 0;
            font-size: 14px;
        }

        .order-card-body .total {
            font-size: 18px;
            font-weight: bold;
            color: #2ecc71;
            margin-top: 10px;
        }

        .order-card-footer {
            margin-top: 10px;
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
        }
    </style>
</head>

<body>
    <?php include 'sidebar.php'; ?>
    <!-- Navbar -->
    <div class="navbar">
        <div class="d-flex justify-content-between align-items-center w-100">
            <div>
                <h4 class="mb-0"><i class="fas fa-shopping-cart text-primary"></i> จัดการคำสั่งซื้อ</h4>
                <small class="text-muted">ตรวจสอบและจัดการคำสั่งซื้อทั้งหมด</small>
            </div>

            <div class="d-flex gap-3">
                <a href="dashboard.php" class="btn btn-outline-primary btn-sm">
                    <i class="fas fa-home"></i> แดชบอร์ด
                </a>
                <a href="manage_product.php" class="btn btn-outline-success btn-sm">
                    <i class="fas fa-box"></i> จัดการสินค้า
                </a>
            </div>
        </div>
    </div>
    <br>
    <!-- สถิติออเดอร์ -->
    <div class="row mb-4">
        <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
            <div class="stat-card">
                <div class="stat-icon icon-all">
                    <i class="fas fa-shopping-cart"></i>
                </div>
                <div class="stat-number"><?= number_format($stats['total_count']?? 0) ?></div>
                <div class="stat-title">ออเดอร์ทั้งหมด</div>
            </div>
        </div>

        <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
            <div class="stat-card">
                <div class="stat-icon icon-pending">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-number"><?= number_format($stats['pending_count'] ?? 0) ?></div>
                <div class="stat-title">รอยืนยัน</div>
            </div>
        </div>

        <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
            <div class="stat-card">
                <div class="stat-icon icon-approved">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-number"><?= number_format($stats['approved_count']?? 0) ?></div>
                <div class="stat-title">ยืนยันแล้ว</div>
            </div>
        </div>

        <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
            <div class="stat-card">
                <div class="stat-icon icon-rejected">
                    <i class="fas fa-times-circle"></i>
                </div>
                <div class="stat-number"><?= number_format($stats['rejected_count']?? 0) ?></div>
                <div class="stat-title">ปฏิเสธแล้ว</div>
            </div>
        </div>

        <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
            <div class="stat-card">
                <div class="stat-icon icon-today">
                    <i class="fas fa-calendar-day"></i>
                </div>
                <div class="stat-number"><?= number_format($stats['today_count']?? 0) ?></div>
                <div class="stat-title">วันนี้</div>
            </div>
        </div>

        <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
            <div class="stat-card">
                <div class="stat-icon icon-revenue">
                    <i class="fas fa-money-bill-wave"></i>
                </div>
                <div class="stat-number"><?= number_format($stats['total_amount'], 2) ?></div>
                <div class="stat-title">ยอดรวม (บาท)</div>
            </div>
        </div>
    </div>

    <!-- ฟิลเตอร์สถานะ -->
    <div class="dashboard-card">
        <div class="card-header">
            <h5 class="card-title"><i class="fas fa-filter"></i> กรองตามสถานะ</h5>
        </div>

        <div class="mb-4">
            <a href="?status=all"
                class="btn-filter btn-filter-all <?= $status == 'all' ? 'active' : '' ?>">
                <i class="fas fa-list"></i> ทั้งหมด (<?= number_format($stats['total_count']?? 0) ?>)
            </a>

            <a href="?status=pending"
                class="btn-filter btn-filter-pending <?= $status == 'pending' ? 'active' : '' ?>">
                <i class="fas fa-clock"></i> รอยืนยัน (<?= number_format($stats['pending_count']?? 0) ?>)
            </a>

            <a href="?status=approved"
                class="btn-filter btn-filter-approved <?= $status == 'approved' ? 'active' : '' ?>">
                <i class="fas fa-check-circle"></i> ยืนยันแล้ว (<?= number_format($stats['approved_count']?? 0) ?>)
            </a>

            <a href="?status=rejected"
                class="btn-filter btn-filter-rejected <?= $status == 'rejected' ? 'active' : '' ?>">
                <i class="fas fa-times-circle"></i> ปฏิเสธแล้ว (<?= number_format($stats['rejected_count']?? 0) ?>)
            </a>
        </div>
    </div>

    <!-- ตารางออเดอร์ -->
    <div class="dashboard-card">
        <div class="card-header">
            <h5 class="card-title">
                <i class="fas fa-table"></i> รายการคำสั่งซื้อ
                <?php if ($status != 'all'): ?>
                    - <span class="text-primary"><?= $status == 'pending' ? 'รอยืนยัน' : ($status == 'approved' ? 'ยืนยันแล้ว' : 'ปฏิเสธแล้ว') ?></span>
                <?php endif; ?>
            </h5>
            <small>แสดง <?= $result->num_rows ?> รายการ</small>
        </div>

        <div class="order-grid">
            <?php if ($result->num_rows > 0): ?>
                <?php while ($order = $result->fetch_assoc()): ?>
                    <div class="order-card">

                        <!-- Header -->
                        <div class="order-card-header">
                            <div>
                                <strong>#<?= htmlspecialchars($order['order_code']) ?></strong><br>
                                <small><?= date('d/m/Y', strtotime($order['order_date'])) ?></small>
                            </div>
                            <div>
                                <?php if ($order['order_status'] == 'pending'): ?>
                                    <span class="status-badge badge-pending">รอยืนยัน</span>
                                <?php elseif ($order['order_status'] == 'approved'): ?>
                                    <span class="status-badge badge-approved">ยืนยันแล้ว</span>
                                <?php elseif ($order['order_status'] == 'rejected'): ?>
                                    <span class="status-badge badge-rejected">ปฏิเสธแล้ว</span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Body -->
                        <div class="order-card-body">
                            <p><strong>ลูกค้า:</strong> <?= htmlspecialchars($order['customer_name']) ?></p>
                            <p>
                                <i class="fas fa-phone"></i>
                                <?= htmlspecialchars($order['customer_phone']) ?>
                            </p>
                            <p>
                                <strong>การรับ:</strong>
                                <?= $order['receive_type'] == 'pickup' ? 'รับเองที่ร้าน' : 'ส่งให้' ?>
                            </p>
                            <p>
                                <strong>วันรับ/ส่ง:</strong>
                                <?= !empty($order['receive_datetime'])
                                    ? date('d/m/Y H:i', strtotime($order['receive_datetime']))
                                    : '-' ?>
                            </p>
                            <strong class="text-success">
                                <?= number_format($order['total_amount'], 2) ?>
                            </strong> บาท

                        </div>

                        <!-- Footer -->
                        <div class="order-card-footer">
                            <?php if ($order['order_status'] == 'pending'): ?>
                                <a href="update_order.php?id=<?= $order['order_id'] ?>&s=approved"
                                    class="btn-action btn-approve"
                                    onclick="return confirm('ยืนยันการอนุมัติออเดอร์นี้?')">
                                    ✔ ยืนยัน
                                </a>

                                <a href="update_order.php?id=<?= $order['order_id'] ?>&s=rejected"
                                    class="btn-action btn-reject"
                                    onclick="return confirm('ปฏิเสธออเดอร์นี้?')">
                                    ✖ ปฏิเสธ
                                </a>
                            <?php endif; ?>

                            <a href="order_detail.php?id=<?= $order['order_id'] ?>"
                                class="btn-action btn-view">
                                👁 ดูรายละเอียด
                            </a>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div style="grid-column:1/-1; text-align:center; padding:40px; color:#777;">
                    <i class="fas fa-shopping-cart fa-3x"></i><br><br>
                    ไม่พบข้อมูลคำสั่งซื้อ
                </div>
            <?php endif; ?>
        </div>

    </div>

    <!-- การดำเนินการด่วน -->
    <div class="row">
        <div class="col-md-6">
            <div class="dashboard-card">
                <div class="card-header">
                    <h5 class="card-title"><i class="fas fa-bolt"></i> การดำเนินการด่วน</h5>
                </div>

                <div class="row g-3">
                    <div class="col-6">
                        <a href="?status=pending" class="btn btn-warning w-100 py-3">
                            <i class="fas fa-clock fa-2x mb-2"></i><br>
                            ออเดอร์รอยืนยัน<br>
                            <small><?=number_format($stats['pending']?? 0) ?> รายการ</small>
                        </a>
                    </div>

                    <div class="col-6">
                        <a href="#" class="btn btn-info w-100 py-3">
                            <i class="fas fa-chart-bar fa-2x mb-2"></i><br>
                            สรุปรายงาน<br>
                            <small>ดูสถิติทั้งหมด</small>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="dashboard-card">
                <div class="card-header">
                    <h5 class="card-title"><i class="fas fa-info-circle"></i> สถิติวันนี้</h5>
                </div>

                <div class="row mb-4">
                    <div class="col">ทั้งหมด: <?= $stats['total_count'] ?></div>
                    <div class="col">รอยืนยัน: <?= $stats['pending_count'] ?></div>
                    <div class="col">ยืนยันแล้ว: <?= $stats['approved_count'] ?></div>
                    <div class="col">ปฏิเสธ: <?= $stats['rejected_count'] ?></div>
                    <div class="col">วันนี้: <?= $stats['today_count'] ?></div>
                    <div class="col text-success">
                        ยอดรวม: <?= number_format($stats['total_amount'], 2) ?> บาท
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="mt-5 pt-4 border-top text-center text-muted">
        <p class="mb-0">ระบบจัดการ </p>
        <small>© <?= date('Y') ?> All rights reserved</small>
    </footer>

    <!-- JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // ยืนยันการเปลี่ยนแปลงสถานะ
        document.querySelectorAll('a[href*="update_order.php"]').forEach(link => {
            link.addEventListener('click', function(e) {
                const action = this.textContent.includes('ยืนยัน') ? 'อนุมัติ' : 'ปฏิเสธ';
                if (!confirm(`คุณแน่ใจที่จะ${action}ออเดอร์นี้?`)) {
                    e.preventDefault();
                }
            });
        });

        // ค้นหาออเดอร์
        const searchInput = document.createElement('input');
        searchInput.type = 'text';
        searchInput.className = 'form-control mb-3';
        searchInput.placeholder = 'ค้นหาออเดอร์... (รหัส, ชื่อ, เบอร์โทร)';
        searchInput.id = 'searchOrders';

        const cardHeader = document.querySelector('.dashboard-card .card-header');
        if (cardHeader) {
            cardHeader.parentNode.insertBefore(searchInput, cardHeader.nextElementSibling);
        }

        document.getElementById('searchOrders').addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            document.querySelectorAll('.order-table tbody tr').forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(searchTerm) ? '' : 'none';
            });
        });
    </script>
</body>

</html>