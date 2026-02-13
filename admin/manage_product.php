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
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Kanit', sans-serif;
            background-color: #f5f7fa;
            font-size: 16px;
        }

        /* เพิ่มขนาดตัวอักษรโดยรวมสำหรับผู้สูงอายุ */
        html {
            font-size: 18px;
        }

        @media (max-width: 768px) {
            html {
                font-size: 16px;
            }
        }

        .main-content {
            margin-left: 250px;
            padding: 25px;
            max-width: calc(100vw - 250px);
            overflow-x: auto;
        }

        .dashboard-header {
            background: linear-gradient(120deg, var(--primary), var(--secondary));
            color: white;
            padding: 1.25rem 1.5rem;
            box-shadow: 0 4px 15px rgba(67, 97, 238, 0.25);
            position: relative;
            overflow: hidden;
            z-index: 10;
            border-radius: 50px;
            margin-bottom: 30px;
        }

        .dashboard-header::before {
            content: "";
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.15) 0%, rgba(255, 255, 255, 0) 70%);
            pointer-events: none;
        }

        .dashboard-title {
            font-size: 2rem;
            font-weight: 600;
            margin: 0;
        }

        .admin-profile {
            display: flex;
            align-items: center;
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px);
            padding: 0.65rem 1.25rem;
            border-radius: 50px;
            transition: all 0.3s ease;
        }

        .admin-profile:hover {
            background: rgba(255, 255, 255, 0.3);
        }

        .admin-profile img {
            width: 42px;
            height: 42px;
            border-radius: 50%;
            margin-right: 12px;
            border: 2px solid rgba(255, 255, 255, 0.5);
        }

        .admin-profile span {
            font-weight: 500;
            color: white;
            font-size: 1rem;
        }

        /* ตารางขนาดใหญ่ อ่านง่าย */
        .table-large {
            font-size: 1rem;
            background-color: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
            border-collapse: separate;
            border-spacing: 0;
            width: 100%;
        }

        .table-large thead tr {
            background-color: #2c3e50;
            color: white;
        }

        .table-large thead tr th {
            padding: 1.1rem 1rem;
            font-weight: 500;
            font-size: 1.1rem;
            border-bottom: none;
            text-align: center;
            vertical-align: middle;
        }

        .table-large thead tr th:first-child {
            border-top-left-radius: 20px;
        }

        .table-large thead tr th:last-child {
            border-top-right-radius: 20px;
        }

        .table-large tbody tr td {
            padding: 1rem 1rem;
            vertical-align: middle;
            border-bottom: 1px solid #e9ecef;
            font-size: 1rem;
        }

        .table-large tbody tr:hover {
            background-color: #f8f9ff;
        }

        .table-large tbody tr:last-child td:first-child {
            border-bottom-left-radius: 20px;
        }

        .table-large tbody tr:last-child td:last-child {
            border-bottom-right-radius: 20px;
        }

        /* รูปสินค้าในตาราง */
        .product-thumb {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 12px;
            border: 2px solid #f0f0f0;
            box-shadow: 0 3px 10px rgba(0,0,0,0.05);
        }

        .no-image-box {
            width: 80px;
            height: 80px;
            background-color: #f8f9fa;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #adb5bd;
            font-size: 0.85rem;
            border: 2px dashed #dee2e6;
        }

        /* ปุ่มขนาดใหญ่ กดง่าย */
        .btn-large-action {
            padding: 0.6rem 1rem;
            font-size: 1rem;
            border-radius: 12px;
            margin: 0 3px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 70px;
        }

        .btn-large-action i {
            font-size: 1.2rem;
            margin-right: 5px;
        }

        .badge-large {
            font-size: 0.9rem;
            padding: 0.6rem 1rem;
            border-radius: 50px;
            font-weight: 500;
            display: inline-block;
        }

        /* ตัวกรองปุ่มใหญ่ */
        .filter-buttons .btn {
            padding: 0.7rem 1.5rem;
            font-size: 1rem;
            border-radius: 50px;
            margin-right: 8px;
        }

        .filter-buttons .btn i {
            margin-right: 6px;
        }

        .filter-buttons .badge {
            font-size: 0.9rem;
            padding: 0.5rem 0.8rem;
            margin-left: 8px;
        }

        /* ช่องค้นหา */
        .search-box {
            background: white;
            border-radius: 50px;
            padding: 0.25rem;
            display: flex;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.03);
            max-width: 350px;
        }

        .search-box input {
            border: none;
            padding: 0.8rem 1.2rem;
            font-size: 1rem;
            border-radius: 50px;
            flex: 1;
        }

        .search-box input:focus {
            outline: none;
            box-shadow: none;
        }

        .search-box button {
            border-radius: 50px;
            padding: 0.6rem 1.5rem;
            margin-right: 5px;
        }

        /* แสดงจำนวนรายการ */
        .result-count {
            font-size: 1.1rem;
            color: #495057;
            padding: 0.7rem 0;
        }

        /* สีปุ่มตามสถานะ */
        .btn-toggle-active {
            background-color: #28a745;
            color: white;
            border: none;
        }
        
        .btn-toggle-inactive {
            background-color: #6c757d;
            color: white;
            border: none;
        }
        
        .btn-edit {
            background-color: #ffc107;
            color: #212529;
            border: none;
        }
        

        /* Responsive */
        @media (max-width: 992px) {
            .main-content {
                margin-left: 0;
                max-width: 100%;
            }
        }
    </style>
</head>

<body>
    <?php include 'sidebar.php'; ?>

    <div class="main-content">
        <div class="container-fluid px-0">
            <!-- Header -->
            <header class="dashboard-header">
                <div class="d-flex justify-content-between align-items-center flex-wrap">
                    <div>
                        <h1 class="dashboard-title mb-0">
                            <i class="bi bi-box-seam me-2"></i>จัดการสินค้า
                        </h1>
                    </div>
                    <div class="d-flex align-items-center gap-3 mt-2 mt-md-0">
                        <div class="admin-profile">
                            <img src="https://ui-avatars.com/api/?name=<?= urlencode($admin_name) ?>&background=random&color=fff&size=42" alt="Admin">
                            <span><?= htmlspecialchars($admin_name) ?></span>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Toolbar: ตัวกรอง + ค้นหา + ปุ่มเพิ่ม -->
            <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
                <div class="filter-buttons mb-2 mb-sm-0">
                    <!-- ปุ่มเพิ่มสินค้า -->
                    <a href="add_product.php" class="btn btn-primary shadow-sm" style="padding: 0.8rem 1.8rem; font-size: 1.1rem; border-radius: 50px;">
                        <i class="bi bi-plus-lg me-1"></i> เพิ่มสินค้าใหม่
                    </a>
                    <a href="manage_product.php" class="btn <?= $current_status == 'all' ? 'btn-dark' : 'btn-outline-dark' ?> shadow-sm">
                        <i class="bi bi-grid-3x3-gap-fill"></i> ทั้งหมด
                        <span class="badge bg-light text-dark"><?= $counts['total'] ?? 0 ?></span>
                    </a>
                    <a href="manage_product.php?status=active" class="btn <?= $current_status == 'active' ? 'btn-success' : 'btn-outline-success' ?> shadow-sm">
                        <i class="bi bi-check-circle-fill"></i> กำลังเปิดขาย
                        <span class="badge bg-light text-dark"><?= $counts['active_count'] ?? 0 ?></span>
                    </a>
                    <a href="manage_product.php?status=inactive" class="btn <?= $current_status == 'inactive' ? 'btn-secondary' : 'btn-outline-secondary' ?> shadow-sm">
                        <i class="bi bi-x-circle-fill"></i> ปิดขาย
                        <span class="badge bg-light text-dark"><?= $counts['inactive_count'] ?? 0 ?></span>
                    </a>
                </div>
                
                <div class="d-flex gap-3 align-items-center">
                    <!-- ฟอร์มค้นหา -->
                    <form method="GET" action="" class="search-box">
                        <?php if ($current_status != 'all'): ?>
                            <input type="hidden" name="status" value="<?= $current_status ?>">
                        <?php endif; ?>
                        <input type="text" name="search" class="form-control border-0" placeholder="ค้นหาสินค้า..." value="<?= htmlspecialchars($search_keyword) ?>">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-search"></i> ค้นหา
                        </button>
                    </form>
                    
                    
                </div>
            </div>

            <!-- แสดงจำนวนผลลัพธ์ -->
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

            <div class="result-count mb-2">
                <i class="bi bi-box me-1"></i> พบสินค้าทั้งหมด <strong><?= $total_results ?></strong> รายการ
            </div>

            <!-- ตารางแสดงสินค้า -->
            <div class="table-responsive">
                <table class="table-large table align-middle">
                    <thead>
                        <tr>
                            <th width="80">รูป</th>
                            <th>ชื่อสินค้า</th>
                            <th>หมวดหมู่</th>
                            <th>ราคา</th>
                            <th>หน่วย</th>
                            <th>สถานะ</th>
                            <th width="220">จัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($total_results > 0): ?>
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td>
                                        <?php if (!empty($row['product_image'])): ?>
                                            <img src="uploads/products/<?= htmlspecialchars($row['product_image']) ?>" 
                                                class="product-thumb"
                                                onerror="this.onerror=null; this.style.display='none'; this.parentNode.innerHTML='<div class=\'no-image-box\'><i class=\'bi bi-image fs-3\'></i></div>';">
                                        <?php else: ?>
                                            <div class="no-image-box">
                                                <i class="bi bi-image fs-3"></i>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="fw-semibold">
                                        <?= htmlspecialchars($row['product_name']) ?>
                                        <?php if ($row['seasonal'] == 1): ?>
                                            <span class="badge bg-warning text-dark ms-2" style="font-size: 0.8rem; padding: 0.4rem 0.8rem;">
                                                <i class="bi bi-tree-fill me-1"></i>ตามฤดูกาล
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars($row['category'] ?? '-') ?></td>
                                    <td class="fw-bold text-success">
                                        <?= number_format($row['price'], 2) ?> ฿
                                    </td>
                                    <td><?= htmlspecialchars($row['unit'] ?? '-') ?></td>
                                    <td>
                                        <?php if ($row['status'] == 'active'): ?>
                                            <span class="badge badge-large bg-success">
                                                <i class="bi bi-check-circle-fill me-1"></i> พร้อมขาย
                                            </span>
                                        <?php else: ?>
                                            <span class="badge badge-large bg-danger">
                                                <i class="bi bi-x-circle-fill me-1"></i> ไม่พร้อมขาย
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="d-flex gap-2">
                                            <!-- ปุ่มเปิด/ปิดสถานะ -->
                                            <a href="toggle_product.php?id=<?= $row['product_id'] ?>&from=manage_product<?= isset($_GET['status']) ? '&status='.$_GET['status'] : '' ?><?= !empty($search_keyword) ? '&search='.urlencode($search_keyword) : '' ?>" 
                                                class="btn btn-large-action <?= $row['status'] == 'active' ? 'btn-toggle-inactive' : 'btn-toggle-active' ?>"
                                                onclick="return confirm('ต้องการเปลี่ยนสถานะสินค้า <?= htmlspecialchars($row['product_name']) ?>?')">
                                                <i class="bi <?= $row['status'] == 'active' ? 'bi-toggle-off' : 'bi-toggle-on' ?>"></i>
                                                <?= $row['status'] == 'active' ? 'ปิด' : 'เปิด' ?>
                                            </a>
                                            
                                            <!-- ปุ่มแก้ไข -->
                                            <a href="edit_product.php?id=<?= $row['product_id'] ?>" 
                                                class="btn btn-large-action btn-edit">
                                                <i class="bi bi-pencil-square"></i> แก้ไข
                                            </a>
                                            
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center py-5">
                                    <div class="my-4">
                                        <i class="bi bi-emoji-frown fs-1 text-muted"></i>
                                        <h4 class="mt-3 text-muted">ไม่พบสินค้า</h4>
                                        <p class="text-muted">ลองเปลี่ยนคำค้นหาหรือเพิ่มสินค้าใหม่</p>
                                        <a href="add_product.php" class="btn btn-primary btn-lg mt-2 px-4">
                                            <i class="bi bi-plus-lg me-1"></i> เพิ่มสินค้า
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- แสดงหมายเลขหน้าแบบง่าย (ถ้ามีหลายหน้า) -->
            <?php if ($total_results > 20): ?>
            <div class="d-flex justify-content-center mt-4">
                <nav aria-label="Page navigation">
                    <ul class="pagination pagination-lg">
                        <li class="page-item disabled">
                            <span class="page-link">«</span>
                        </li>
                        <li class="page-item active"><span class="page-link">1</span></li>
                        <li class="page-item"><span class="page-link">2</span></li>
                        <li class="page-item"><span class="page-link">3</span></li>
                        <li class="page-item">
                            <span class="page-link">»</span>
                        </li>
                    </ul>
                </nav>
            </div>
            <div class="text-center text-muted small">
                * ระบบแสดงเฉพาะรายการล่าสุด 20 รายการ
            </div>
            <?php endif; ?>
            
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <?php $stmt->close(); ?>
</body>

</html>