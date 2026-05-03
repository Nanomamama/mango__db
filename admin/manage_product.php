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
$search_keyword = $_GET['search'] ?? '';

?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>จัดการสินค้า - ระบบหลังบ้าน</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* ============================================
           SENIOR-FRIENDLY DESIGN
           - ตัวหนังสือใหญ่
           - ปุ่มใหญ่ กดง่าย
           - คอนทราสต์สูง
           - ระยะห่างมากขึ้น
        ============================================ */
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Kanit', sans-serif;
            background: #e9ecef;
            font-size: 18px;  /* ฐานใหญ่ขึ้น */
            line-height: 1.6;
        }

        /* SIDEBAR - ขนาดใหญ่ขึ้น */
        .sidebar {
            width: 280px;
            background: linear-gradient(180deg, #1e293b 0%, #0f172a 100%);
            color: #fff;
            position: fixed;
            height: 100vh;
            left: 0;
            top: 0;
            z-index: 100;
            overflow-y: auto;
        }

        .sidebar-header {
            padding: 30px 20px;
            border-bottom: 1px solid rgba(255,255,255,0.15);
            text-align: center;
        }

        .sidebar-header h3 {
            font-size: 26px;
            font-weight: 600;
        }

        .sidebar-header p {
            font-size: 16px;
            opacity: 0.8;
            margin-top: 8px;
        }

        .sidebar-menu {
            list-style: none;
            padding: 20px 0;
        }

        .sidebar-menu li {
            margin-bottom: 4px;
        }

        .sidebar-menu li a {
            display: flex;
            align-items: center;
            padding: 16px 24px;
            color: rgba(255,255,255,0.85);
            text-decoration: none;
            font-size: 18px;
            gap: 14px;
            transition: 0.2s;
        }

        .sidebar-menu li a i {
            width: 28px;
            font-size: 22px;
        }

        .sidebar-menu li a:hover,
        .sidebar-menu li.active a {
            background: rgba(255,255,255,0.12);
            border-left: 5px solid #60a5fa;
            color: white;
        }

        /* MAIN CONTENT */
        .main-content {
            margin-left: 280px;
            padding: 30px 35px;
            min-height: 100vh;
        }

        /* HEADER CARD */
        .header-card {
            background: white;
            border-radius: 28px;
            padding: 24px 32px;
            margin-bottom: 30px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 20px;
        }

        .title-section h1 {
            font-size: 32px;
            font-weight: 700;
            color: #0f172a;
            margin: 0;
        }

        .title-section p {
            font-size: 18px;
            color: #475569;
            margin: 8px 0 0;
        }

        .admin-card {
            display: flex;
            align-items: center;
            gap: 16px;
            background: #f1f5f9;
            padding: 12px 24px;
            border-radius: 80px;
        }

        .admin-card img {
            width: 52px;
            height: 52px;
            border-radius: 50%;
        }

        .admin-card .name {
            font-weight: 600;
            font-size: 18px;
        }

        .admin-card .email {
            font-size: 14px;
            color: #64748b;
        }

        /* FILTER SECTION - ปุ่มใหญ่ขึ้น */
        .filter-section {
            background: white;
            border-radius: 28px;
            padding: 24px 28px;
            margin-bottom: 28px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.04);
        }

        .filter-buttons {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 25px;
        }

        .filter-btn {
            padding: 14px 28px;
            font-size: 18px;
            font-weight: 500;
            border-radius: 60px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            transition: 0.2s;
            border: 2px solid #e2e8f0;
            background: white;
            color: #1e293b;
        }

        .filter-btn i {
            font-size: 20px;
        }

        .filter-btn .badge-count {
            background: #e2e8f0;
            padding: 4px 12px;
            border-radius: 40px;
            font-size: 15px;
            margin-left: 8px;
        }

        .filter-btn.active {
            background: #2563eb;
            border-color: #2563eb;
            color: white;
        }

        .filter-btn.active .badge-count {
            background: rgba(255,255,255,0.25);
            color: white;
        }

        .filter-btn:hover:not(.active) {
            background: #f8fafc;
            border-color: #94a3b8;
            transform: scale(1.02);
        }

        /* SEARCH + ADD BUTTON - ใหญ่ กดง่าย */
        .action-row {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
            margin-top: 10px;
        }

        .search-box {
            display: flex;
            gap: 12px;
            flex: 1;
            max-width: 500px;
        }

        .search-box input {
            flex: 1;
            padding: 14px 20px;
            font-size: 18px;
            border: 2px solid #e2e8f0;
            border-radius: 60px;
            font-family: 'Kanit', sans-serif;
        }

        .search-box input:focus {
            outline: none;
            border-color: #2563eb;
        }

        .search-box button {
            padding: 14px 28px;
            font-size: 18px;
            background: #2563eb;
            color: white;
            border: none;
            border-radius: 60px;
            font-family: 'Kanit', sans-serif;
            font-weight: 500;
            cursor: pointer;
            transition: 0.2s;
        }

        .search-box button:hover {
            background: #1d4ed8;
            transform: scale(1.02);
        }

        .btn-add-large {
            background: #10b981;
            color: white;
            padding: 14px 32px;
            border-radius: 60px;
            text-decoration: none;
            font-size: 18px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 12px;
            transition: 0.2s;
        }

        .btn-add-large:hover {
            background: #059669;
            color: white;
            transform: scale(1.02);
        }

        /* RESULT COUNT */
        .result-info {
            font-size: 18px;
            color: #475569;
            margin-bottom: 20px;
            padding-left: 8px;
        }

        /* TABLE - ใหญ่ อ่านง่าย */
        .table-container {
            background: white;
            border-radius: 28px;
            overflow-x: auto;
            box-shadow: 0 4px 16px rgba(0,0,0,0.05);
        }

        .product-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 900px;
        }

        .product-table th {
            background: #f8fafc;
            padding: 20px 18px;
            font-size: 18px;
            font-weight: 600;
            color: #0f172a;
            border-bottom: 3px solid #e2e8f0;
            text-align: left;
        }

        .product-table td {
            padding: 20px 18px;
            font-size: 17px;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: middle;
        }

        .product-table tr:hover td {
            background: #fefce8;
        }

        /* รูปสินค้า */
        .product-img {
            width: 80px;
            height: 80px;
            border-radius: 16px;
            object-fit: cover;
            background: #f1f5f9;
            border: 1px solid #e2e8f0;
        }

        .no-img {
            width: 80px;
            height: 80px;
            background: #f1f5f9;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #94a3b8;
            border: 1px dashed #cbd5e1;
        }

        /* ชื่อสินค้า */
        .product-name {
            font-weight: 700;
            font-size: 18px;
            color: #0f172a;
        }

        .seasonal-tag {
            background: #fef3c7;
            color: #b45309;
            padding: 4px 12px;
            border-radius: 30px;
            font-size: 13px;
            margin-left: 10px;
        }

        /* ราคา */
        .price-highlight {
            font-weight: 700;
            font-size: 20px;
            color: #10b981;
        }

        /* สถานะ badge */
        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 18px;
            border-radius: 60px;
            font-size: 16px;
            font-weight: 500;
        }

        .badge-active {
            background: #d1fae5;
            color: #065f46;
        }

        .badge-inactive {
            background: #fee2e2;
            color: #991b1b;
        }

        /* ปุ่มจัดการ - ใหญ่ จับง่าย */
        .action-group {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }

        .btn-action {
            padding: 10px 20px;
            border-radius: 50px;
            font-size: 16px;
            font-weight: 500;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: 0.2s;
            border: none;
            cursor: pointer;
        }

        .btn-toggle-on {
            background: #ef4444;
            color: white;
        }

        .btn-toggle-off {
            background: #10b981;
            color: white;
        }

        .btn-edit {
            background: #f59e0b;
            color: white;
        }

        .btn-action:hover {
            transform: translateY(-2px);
            filter: brightness(0.92);
        }

        /* Empty state */
        .empty-box {
            text-align: center;
            padding: 70px 20px;
        }

        .empty-box i {
            font-size: 80px;
            color: #cbd5e1;
        }

        .empty-box h3 {
            font-size: 26px;
            margin-top: 20px;
            color: #475569;
        }

        /* Responsive */
        @media (max-width: 992px) {
            .sidebar {
                transform: translateX(-100%);
                position: fixed;
                z-index: 1050;
            }
            .main-content {
                margin-left: 0;
                padding: 20px;
            }
        }

        @media (max-width: 640px) {
            body {
                font-size: 16px;
            }
            .filter-buttons {
                justify-content: center;
            }
            .action-row {
                flex-direction: column;
            }
            .search-box {
                max-width: 100%;
                width: 100%;
            }
            .btn-add-large {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>

<body>

<?php include 'sidebar.php'; ?>

<div class="main-content">
    <!-- HEADER -->
    <div class="header-card">
        <div class="title-section">
            <h1><i class="bi bi-box-seam me-2"></i>จัดการสินค้า</h1>
            <p>จัดการข้อมูลสินค้าทั้งหมดในระบบ</p>
        </div>
        <div class="admin-card">
            <img src="https://ui-avatars.com/api/?name=<?= urlencode($admin_name) ?>&background=2563eb&color=fff&size=52" alt="admin">
            <div>
                <div class="name"><?= htmlspecialchars($admin_name) ?></div>
                <div class="email"><?= htmlspecialchars($admin_email) ?></div>
            </div>
        </div>
    </div>

    <!-- FILTER -->
    <div class="filter-section">
        <div class="filter-buttons">
            <a href="manage_product.php" class="filter-btn <?= $current_status == 'all' ? 'active' : '' ?>">
                <i class="bi bi-grid-3x3-gap-fill"></i> ทั้งหมด
                <span class="badge-count"><?= $counts['total'] ?? 0 ?></span>
            </a>
            <a href="manage_product.php?status=active" class="filter-btn <?= $current_status == 'active' ? 'active' : '' ?>">
                <i class="bi bi-check-circle-fill"></i> กำลังเปิดขาย
                <span class="badge-count"><?= $counts['active_count'] ?? 0 ?></span>
            </a>
            <a href="manage_product.php?status=inactive" class="filter-btn <?= $current_status == 'inactive' ? 'active' : '' ?>">
                <i class="bi bi-x-circle-fill"></i> ปิดขาย
                <span class="badge-count"><?= $counts['inactive_count'] ?? 0 ?></span>
            </a>
        </div>

        <div class="action-row">
            <form method="GET" action="" class="search-box">
                <?php if ($current_status != 'all'): ?>
                    <input type="hidden" name="status" value="<?= $current_status ?>">
                <?php endif; ?>
                <input type="text" name="search" placeholder="🔍 ค้นหาสินค้า..." value="<?= htmlspecialchars($search_keyword) ?>">
                <button type="submit">ค้นหา</button>
            </form>
            <a href="add_product.php" class="btn-add-large">
                <i class="bi bi-plus-lg"></i> เพิ่มสินค้าใหม่
            </a>
        </div>
    </div>

    <?php
    // Build SQL with filters
    $where_conditions = [];
    $params = [];
    $types = "";

    if (isset($_GET['status']) && in_array($_GET['status'], ['active', 'inactive'])) {
        $where_conditions[] = "status = ?";
        $params[] = $_GET['status'];
        $types .= "s";
    }

    if (!empty($search_keyword)) {
        $where_conditions[] = "(product_name LIKE ? OR category LIKE ?)";
        $search_param = "%$search_keyword%";
        $params[] = $search_param;
        $params[] = $search_param;
        $types .= "ss";
    }

    $where = "";
    if (count($where_conditions) > 0) {
        $where = "WHERE " . implode(" AND ", $where_conditions);
    }

    $sql = "SELECT * FROM products $where ORDER BY product_id DESC";
    
    $stmt = $conn->prepare($sql);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $total_results = $result->num_rows;
    ?>

    <div class="result-info">
        <i class="bi bi-database"></i> พบสินค้าทั้งหมด <strong><?= $total_results ?></strong> รายการ
    </div>

    <!-- TABLE -->
    <div class="table-container">
        <table class="product-table">
            <thead>
                <tr>
                    <th>รูป</th>
                    <th>ชื่อสินค้า</th>
                    <th>หมวดหมู่</th>
                    <th>ราคา</th>
                    <th>หน่วย</th>
                    <th>สถานะ</th>
                    <th>จัดการ</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($total_results > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td>
                                <?php if (!empty($row['product_image'])): ?>
                                    <img src="uploads/products/<?= htmlspecialchars($row['product_image']) ?>" 
                                        class="product-img"
                                        onerror="this.onerror=null; this.src='data:image/svg+xml,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%2280%22%20height%3D%2280%22%20viewBox%3D%220%200%2080%2080%22%3E%3Crect%20width%3D%2280%22%20height%3D%2280%22%20fill%3D%22%23f1f5f9%22%2F%3E%3Ctext%20x%3D%2240%22%20y%3D%2245%22%20text-anchor%3D%22middle%22%20fill%3D%22%2394a3b8%22%3ENo%20Img%3C%2Ftext%3E%3C%2Fsvg%3E'">
                                <?php else: ?>
                                    <div class="no-img">
                                        <i class="bi bi-image fs-2"></i>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="product-name">
                                    <?= htmlspecialchars($row['product_name']) ?>
                                    <?php if ($row['seasonal'] == 1): ?>
                                        <span class="seasonal-tag">
                                            <i class="bi bi-tree-fill"></i> ตามฤดูกาล
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td><?= htmlspecialchars($row['category'] ?? '-') ?></td>
                            <td class="price-highlight"><?= number_format($row['price'], 2) ?> ฿</td>
                            <td><?= htmlspecialchars($row['unit'] ?? '-') ?></td>
                            <td>
                                <?php if ($row['status'] == 'active'): ?>
                                    <span class="status-badge badge-active">
                                        <i class="bi bi-check-circle-fill"></i> พร้อมขาย
                                    </span>
                                <?php else: ?>
                                    <span class="status-badge badge-inactive">
                                        <i class="bi bi-x-circle-fill"></i> ไม่พร้อมขาย
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="action-group">
                                    <a href="toggle_product.php?id=<?= $row['product_id'] ?>&from=manage_product<?= isset($_GET['status']) ? '&status='.$_GET['status'] : '' ?><?= !empty($search_keyword) ? '&search='.urlencode($search_keyword) : '' ?>" 
                                        class="btn-action <?= $row['status'] == 'active' ? 'btn-toggle-on' : 'btn-toggle-off' ?>"
                                        onclick="return confirm('ยืนยันการเปลี่ยนสถานะสินค้า?')">
                                        <i class="bi <?= $row['status'] == 'active' ? 'bi-toggle-off' : 'bi-toggle-on' ?>"></i>
                                        <?= $row['status'] == 'active' ? 'ปิด' : 'เปิด' ?>
                                    </a>
                                    <a href="edit_product.php?id=<?= $row['product_id'] ?>" class="btn-action btn-edit">
                                        <i class="bi bi-pencil-square"></i> แก้ไข
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="empty-box">
                            <i class="bi bi-box-seam"></i>
                            <h3>ไม่พบสินค้า</h3>
                            <p style="font-size: 18px;">ลองเปลี่ยนคำค้นหาหรือเพิ่มสินค้าใหม่</p>
                            <a href="add_product.php" class="btn-add-large" style="display: inline-flex; margin-top: 16px;">
                                <i class="bi bi-plus-lg"></i> เพิ่มสินค้า
                            </a>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<?php $stmt->close(); ?>
</body>
</html>