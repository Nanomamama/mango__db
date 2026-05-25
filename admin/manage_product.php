<?php
require_once 'auth.php';
requireAdminRole('main');
require_once __DIR__ . '/../db/db.php';
require_once 'sidebar.php';

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

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}


$adminPageExtraHead = <<<'HTML'
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;14..32,400;14..32,500;14..32,600;14..32,700&display=swap" rel="stylesheet">
<style>
:root {
    --green: #016A70;
    --primary: #2563eb;
    --primary-dark: #1d4ed8;
    --primary-light: #3b82f6;
    --success: #10b981;
    --success-dark: #059669;
    --success-light: #d1fae5;
    --warning: #f59e0b;
    --warning-dark: #d97706;
    --warning-light: #fef3c7;
    --danger: #ef4444;
    --danger-dark: #dc2626;
    --danger-light: #fee2e2;
    --info: #0ea5e9;
    --info-light: #ecfeff;
    --gray-50: #f9fafb;
    --gray-100: #f3f4f6;
    --gray-200: #e5e7eb;
    --gray-300: #d1d5db;
    --gray-400: #9ca3af;
    --gray-500: #6b7280;
    --gray-600: #4b5563;
    --gray-700: #374151;
    --gray-800: #1f2937;
    --gray-900: #111827;
    --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
    --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1);
    --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1);
    --radius-sm: 0.5rem;
    --radius-md: 0.75rem;
    --radius-lg: 1rem;
}

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
    background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
    color: var(--gray-900);
    font-size: 14px;
    line-height: 1.5;
    min-height: 100vh;
}

.page-content.product-page-bg {
    background:
        radial-gradient(circle at top right, rgba(13, 138, 146, 0.12), transparent 28%),
        linear-gradient(180deg, #f8fbfc 0%, #f3f7f8 100%);
}

/* Main Layout Fix */
.admin-local-page {
    margin-left: 0 !important;
    padding: 1.5rem !important;
    min-height: auto !important;
    width: 100%;
    max-width: 1400px;
    margin: 0 auto;
}

/* Header Card */
.header-card {
    position: relative;
    overflow: hidden;
    background: linear-gradient(135deg, #ffffff 0%, #f2fbfb 52%, #e7f7f7 100%);
    border-radius: 28px;
    padding: 1.75rem 2rem;
    margin-bottom: 1rem;
    margin-top: 5px;
    box-shadow: 0 18px 42px rgba(15, 23, 42, 0.08);
    border: 1px solid rgba(1, 106, 112, 0.14);
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 1.25rem;
}

.header-card::after {
    content: "";
    position: absolute;
    inset: auto -60px -80px auto;
    width: 220px;
    height: 220px;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(13, 138, 146, 0.18), rgba(13, 138, 146, 0));
    pointer-events: none;
}

.title-section {
    position: relative;
    z-index: 1;
}

.title-section h1 {
    font-size: 1.75rem;
    font-weight: 700;
    background: linear-gradient(135deg, var(--gray-900), var(--gray-700));
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    margin: 0 0 0.375rem 0;
}

.title-section p {
    font-size: 0.875rem;
    color: var(--gray-500);
    margin: 0;
}

.admin-card {
    display: flex;
    align-items: center;
    gap: 1rem;
    background: linear-gradient(135deg, var(--gray-50), white);
    padding: 0.625rem 1.25rem;
    border-radius: 100px;
    border: 1px solid var(--gray-200);
    box-shadow: var(--shadow-sm);
}

.admin-card img {
    width: 44px;
    height: 44px;
    border-radius: 50%;
    border: 2px solid white;
    box-shadow: var(--shadow-sm);
}

.admin-card .name {
    font-weight: 700;
    font-size: 0.938rem;
    color: var(--gray-900);
}

.admin-card .email {
    font-size: 0.688rem;
    color: var(--gray-500);
}

/* Filter Section */
.filter-section {
    background: white;
    border-radius: 24px;
    padding: 1.5rem;
    margin-bottom: 1.5rem;
    box-shadow: 0 14px 34px rgba(15, 23, 42, 0.06);
    border: 1px solid rgba(1, 106, 112, 0.12);
}

.product-alert {
    display: flex;
    align-items: center;
    gap: 0.625rem;
    margin-bottom: 1rem;
    padding: 0.875rem 1rem;
    border-radius: 16px;
    background: #ecfdf5;
    border: 1px solid rgba(16, 185, 129, 0.24);
    color: #047857;
    font-weight: 700;
}

.filter-buttons {
    display: flex;
    flex-wrap: wrap;
    gap: 0.75rem;
    margin-bottom: 1.5rem;
}

.filter-btn {
    padding: 0.625rem 1.25rem;
    font-size: 0.875rem;
    font-weight: 600;
    border-radius: 100px;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    transition: all 0.2s ease;
    border: 2px solid var(--gray-200);
    background: white;
    color: var(--gray-700);
}

.filter-btn i {
    font-size: 1rem;
}

.filter-btn .badge-count {
    background: var(--gray-100);
    padding: 0.25rem 0.625rem;
    border-radius: 100px;
    font-size: 0.75rem;
    font-weight: 600;
    margin-left: 0.375rem;
}

.filter-btn.active {
    background: var(--green);
    border-color: var(--success);
    color: white;
}

.filter-btn.active .badge-count {
    background: rgba(255, 255, 255, 0.2);
    color: white;
}

.filter-btn:hover:not(.active) {
    background: var(--gray-50);
    border-color: var(--gray-400);
    transform: translateY(-1px);
    box-shadow: var(--shadow-sm);
}

/* Action Row */
.action-row {
    display: flex;
    flex-wrap: wrap;
    justify-content: space-between;
    align-items: center;
    gap: 1rem;
}

.search-box {
    display: flex;
    gap: 0.75rem;
    flex: 1;
    max-width: 500px;
}

.search-box input {
    flex: 1;
    padding: 0.75rem 1.25rem;
    font-size: 0.875rem;
    border: 2px solid var(--gray-200);
    border-radius: 100px;
    font-family: 'Inter', sans-serif;
    transition: all 0.2s ease;
    background: white;
}

.search-box input:focus {
    outline: none;
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
}

.search-box button {
    padding: 0.75rem 1.5rem;
    font-size: 0.875rem;
    font-weight: 600;
    background: var(--primary);
    color: white;
    border: none;
    border-radius: 100px;
    font-family: 'Inter', sans-serif;
    cursor: pointer;
    transition: all 0.2s ease;
}

.search-box button:hover {
    background: var(--primary-dark);
    transform: translateY(-1px);
    box-shadow: var(--shadow-sm);
}

.btn-add-large {
    background: var(--success);
    color: white;
    padding: 0.75rem 1.75rem;
    border-radius: 100px;
    text-decoration: none;
    font-size: 0.875rem;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    transition: all 0.2s ease;
}

.btn-add-large:hover {
    background: var(--success-dark);
    color: white;
    transform: translateY(-1px);
    box-shadow: var(--shadow-sm);
}

/* Result Info */
.result-info {
    font-size: 0.875rem;
    color: var(--gray-600);
    margin-bottom: 1rem;
    padding: 0.5rem 1rem;
    background: white;
    border-radius: 100px;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    border: 1px solid var(--gray-200);
}

.result-info i {
    color: var(--primary);
    font-size: 1rem;
}

.result-info strong {
    color: var(--gray-900);
    font-weight: 700;
}

/* Table Container */
.table-container {
    background: white;
    border-radius: 24px;
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
    box-shadow: 0 14px 34px rgba(15, 23, 42, 0.06);
    border: 1px solid rgba(1, 106, 112, 0.12);
}

.product-table {
    width: 100%;
    border-collapse: collapse;
    min-width: 900px;
}

.product-table th {
    background: linear-gradient(to bottom, var(--gray-50), white);
    padding: 1rem 1.25rem;
    font-size: 0.75rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: var(--gray-600);
    border-bottom: 2px solid var(--gray-200);
    text-align: left;
}

.product-table td {
    padding: 1rem 1.25rem;
    font-size: 0.875rem;
    border-bottom: 1px solid var(--gray-100);
    vertical-align: middle;
}

.product-table tbody tr {
    transition: all 0.2s ease;
}

.product-table tbody tr:hover {
    background: var(--gray-50);
}

/* Product Image */
.product-img {
    width: 65px;
    height: 65px;
    border-radius: 0rem;
    object-fit: cover;
    background: var(--gray-100);
    border: 1px solid var(--gray-200);
}

.no-img {
    width: 65px;
    height: 65px;
    background: var(--gray-100);
    border-radius: 0rem;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--gray-400);
    border: 2px dashed var(--gray-300);
}

.no-img i {
    font-size: 1.5rem;
}

/* Product Name */
.product-name {
    font-weight: 700;
    font-size: 1rem;
    color: var(--gray-900);
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 0.5rem;
    min-width: 0;
    overflow-wrap: anywhere;
}

.seasonal-tag {
    background: var(--warning-light);
    color: var(--warning-dark);
    padding: 0.25rem 0.75rem;
    border-radius: 100px;
    font-size: 0.688rem;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
}

/* Category */
.category-tag {
    display: inline-flex;
    align-items: center;
    gap: 0.375rem;
    padding: 0.25rem 0.75rem;
    background: var(--gray-100);
    border-radius: 100px;
    font-size: 0.75rem;
    font-weight: 500;
    color: var(--gray-700);
}

/* Price */
.price-highlight {
    font-weight: 700;
    font-size: 1rem;
    color: var(--success);
    display: inline-flex;
    align-items: center;
    gap: 0.125rem;
}

/* Status Badge */
.status-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.375rem;
    padding: 0.375rem 1rem;
    border-radius: 100px;
    font-size: 0.75rem;
    font-weight: 600;
}

.badge-active {
    background: var(--success-light);
    color: var(--success-dark);
}

.badge-inactive {
    background: var(--danger-light);
    color: var(--danger-dark);
}

/* Action Buttons */
.action-group {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.btn-action {
    padding: 0.5rem 1rem;
    border-radius: 100px;
    font-size: 0.75rem;
    font-weight: 600;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 0.375rem;
    transition: all 0.2s ease;
    border: none;
    cursor: pointer;
}

.btn-toggle-on {
    background: var(--danger);
    color: white;
}

.btn-toggle-on:hover {
    background: var(--danger-dark);
    transform: translateY(-1px);
    box-shadow: var(--shadow-sm);
}

.btn-toggle-off {
    background: var(--success);
    color: white;
}

.btn-toggle-off:hover {
    background: var(--success-dark);
    transform: translateY(-1px);
    box-shadow: var(--shadow-sm);
}

.btn-edit {
    background: var(--warning);
    color: white;
}

.btn-edit:hover {
    background: var(--warning-dark);
    transform: translateY(-1px);
    box-shadow: var(--shadow-sm);
}

.action-form {
    display: inline-flex;
    margin: 0;
}

/* Empty State */
.empty-box {
    text-align: center;
    padding: 4rem 2rem;
}

.empty-box i {
    font-size: 5rem;
    color: var(--gray-300);
    margin-bottom: 1rem;
}

.empty-box h3 {
    font-size: 1.5rem;
    font-weight: 600;
    color: var(--gray-700);
    margin-bottom: 0.5rem;
}

.empty-box p {
    font-size: 0.875rem;
    color: var(--gray-500);
    margin-bottom: 1.5rem;
}

/* Responsive */
@media (max-width: 768px) {
    .admin-local-page {
        padding: 1rem !important;
    }
    
    .header-card {
        flex-direction: column;
        align-items: flex-start;
        padding: 1.25rem;
    }
    
    .admin-card {
        width: 100%;
        justify-content: center;
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
        flex-direction: column;
    }
    
    .search-box input,
    .search-box button {
        width: 100%;
    }
    
    .btn-add-large {
        width: 100%;
        justify-content: center;
    }
    
    /* Mobile Card Table */
    .table-container {
        overflow-x: visible;
        background: transparent;
        border: 0;
        box-shadow: none;
    }

    .product-table {
        display: block;
        width: 100%;
        min-width: 0;
        border-collapse: separate;
    }

    .product-table thead {
        display: none;
    }

    .product-table tbody {
        display: block;
        width: 100%;
    }
    
    .product-table tbody tr {
        display: block;
        width: 100%;
        margin-bottom: 1rem;
        border: 1px solid var(--gray-200);
        border-radius: var(--radius-md);
        background: white;
        overflow: hidden;
        box-shadow: var(--shadow-sm);
    }
    
    .product-table td {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        width: 100%;
        min-width: 0;
        padding: 0.875rem 1rem;
        border-bottom: 1px solid var(--gray-100);
        text-align: right;
        overflow-wrap: anywhere;
    }
    
    .product-table td:last-child {
        border-bottom: none;
    }
    
    .product-table td::before {
        content: attr(data-label);
        font-weight: 700;
        font-size: 0.75rem;
        color: var(--gray-600);
        text-align: left;
        flex: 0 0 86px;
    }
    
    .product-table td:first-child {
        justify-content: center;
        background: var(--gray-50);
        padding: 1rem;
    }
    
    .product-table td:first-child::before {
        display: none;
    }
    
    .product-name {
        justify-content: flex-end;
        text-align: right;
        max-width: calc(100% - 96px);
    }

    .category-tag,
    .price-highlight,
    .status-badge {
        max-width: calc(100% - 96px);
        text-align: right;
    }
    
    .action-group {
        width: 100%;
        justify-content: flex-end;
    }

    .action-group .btn-action {
        justify-content: center;
        min-width: 72px;
    }

    .product-table .empty-row {
        border: 0;
        box-shadow: none;
        background: transparent;
    }

    .product-table .empty-box {
        display: block;
        width: 100%;
        background: white;
        border: 1px solid var(--gray-200);
        border-radius: var(--radius-md);
        text-align: center;
    }

    .product-table .empty-box::before {
        display: none;
    }
}

@media (max-width: 480px) {
    .title-section h1 {
        font-size: 1.25rem;
    }
    
    .filter-btn {
        padding: 0.5rem 1rem;
        font-size: 0.75rem;
    }
    
    .product-img,
    .no-img {
        width: 50px;
        height: 50px;
    }

    .product-table td {
        gap: 0.75rem;
        padding: 0.75rem 0.875rem;
    }

    .product-table td::before {
        flex-basis: 74px;
    }

    .product-name,
    .category-tag,
    .price-highlight,
    .status-badge {
        max-width: calc(100% - 82px);
    }

    .action-group {
        gap: 0.375rem;
    }

    .action-group .btn-action {
        flex: 1 1 76px;
        padding: 0.5rem 0.75rem;
    }
}
</style>

<style>
/* Fix for sidebar layout */
body {
    margin-left: 0 !important;
    padding: 0 !important;
    max-width: none !important;
    overflow-x: hidden;
}

.page-content {
    width: 100%;
    max-width: 100%;
    padding: 1rem;
}
</style>
HTML;
adminPageStart('จัดการสินค้า');
?>

<div class="admin-local-page">
    <!-- HEADER -->
    <div class="header-card">
        <div class="title-section">
            <h1>จัดการสินค้า</h1>
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
                    <input type="hidden" name="status" value="<?= htmlspecialchars($current_status) ?>">
                <?php endif; ?>
                <input type="text" name="search" placeholder=" ค้นหาสินค้า..." value="<?= htmlspecialchars($search_keyword) ?>">

                <button type="submit">
                    <i class="fa-solid fa-magnifying-glass"></i> ค้นหา
                </button>
            </form>
            <a href="add_product.php" class="btn-add-large">
                <i class="bi bi-plus-lg"></i> เพิ่มสินค้าใหม่
            </a>
        </div>
    </div>

    <?php if (!empty($_SESSION['success'])): ?>
        <div class="product-alert">
            <i class="bi bi-check-circle-fill"></i>
            <?= htmlspecialchars($_SESSION['success'], ENT_QUOTES, 'UTF-8') ?>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php elseif (!empty($_SESSION['product_error'])): ?>
        <div class="product-alert" style="background:#fef2f2;border-color:rgba(239,68,68,.24);color:#991b1b;">
            <i class="bi bi-exclamation-triangle-fill"></i>
            <?= htmlspecialchars($_SESSION['product_error'], ENT_QUOTES, 'UTF-8') ?>
        </div>
        <?php unset($_SESSION['product_error']); ?>
    <?php elseif (!empty($_GET['error'])): ?>
        <div class="product-alert" style="background:#fef2f2;border-color:rgba(239,68,68,.24);color:#991b1b;">
            <i class="bi bi-exclamation-triangle-fill"></i>
            <?= htmlspecialchars((string) $_GET['error'], ENT_QUOTES, 'UTF-8') ?>
        </div>
    <?php endif; ?>

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
                    <th style="width: 80px;">รูป</th>
                    <th>ชื่อสินค้า</th>
                    <th>หมวดหมู่</th>
                    <th style="width: 100px;">ราคา</th>
                    <th style="width: 80px;">หน่วย</th>
                    <th style="width: 110px;">สถานะ</th>
                    <th style="width: 140px;">จัดการ</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($total_results > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td data-label="รูปสินค้า">
                                <?php if (!empty($row['product_image'])): ?>
                                    <img src="../admin/uploads/products/<?= htmlspecialchars($row['product_image']) ?>" 
                                        class="product-img"
                                        alt="<?= htmlspecialchars($row['product_name']) ?>"
                                        onerror="this.src='data:image/svg+xml,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%2265%22%20height%3D%2265%22%20viewBox%3D%220%200%2065%2065%22%3E%3Crect%20width%3D%2265%22%20height%3D%2265%22%20fill%3D%22%23f1f5f9%22%2F%3E%3Ctext%20x%3D%2232.5%22%20y%3D%2238%22%20text-anchor%3D%22middle%22%20fill%3D%22%2394a3b8%22%20font-size%3D%2210%22%3ENo%20Img%3C%2Ftext%3E%3C%2Fsvg%3E'">
                                <?php else: ?>
                                    <div class="no-img">
                                        <i class="bi bi-image"></i>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td data-label="ชื่อสินค้า">
                                <div class="product-name">
                                    <?= htmlspecialchars($row['product_name']) ?>
                                    <?php if ($row['seasonal'] == 1): ?>
                                        <span class="seasonal-tag">
                                            <i class="bi bi-tree-fill"></i> ตามฤดูกาล
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td data-label="หมวดหมู่">
                                <span class="category-tag">
                                    <i class="bi bi-tag-fill"></i>
                                    <?= htmlspecialchars($row['category'] ?? '-') ?>
                                </span>
                            </td>
                            <td data-label="ราคา">
                                <span class="price-highlight">
                                    ฿<?= number_format($row['price'], 2) ?>
                                </span>
                            </td>
                            <td data-label="หน่วย">
                                <?= htmlspecialchars($row['unit'] ?? '-') ?>
                            </td>
                            <td data-label="สถานะ">
                                <?php if ($row['status'] == 'active'): ?>
                                    <span class="status-badge badge-active">
                                        <i class="bi bi-check-circle-fill"></i> พร้อมขาย
                                    </span>
                                <?php else: ?>
                                    <span class="status-badge badge-inactive">
                                        <i class="bi bi-x-circle-fill"></i> ปิดขาย
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td data-label="จัดการ">
                                <div class="action-group">
                                    <form action="toggle_product.php" method="POST" class="action-form" onsubmit="return confirm('ยืนยันการเปลี่ยนสถานะสินค้า?')">
                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">
                                        <input type="hidden" name="id" value="<?= (int) $row['product_id'] ?>">
                                        <input type="hidden" name="status_filter" value="<?= htmlspecialchars($current_status, ENT_QUOTES, 'UTF-8') ?>">
                                        <input type="hidden" name="search" value="<?= htmlspecialchars($search_keyword, ENT_QUOTES, 'UTF-8') ?>">
                                        <button type="submit" class="btn-action <?= $row['status'] == 'active' ? 'btn-toggle-on' : 'btn-toggle-off' ?>">
                                            <i class="bi <?= $row['status'] == 'active' ? 'bi-toggle-off' : 'bi-toggle-on' ?>"></i>
                                            <?= $row['status'] == 'active' ? 'ปิด' : 'เปิด' ?>
                                        </button>
                                    </form>
                                    <a href="edit_product.php?id=<?= $row['product_id'] ?>" class="btn-action btn-edit">
                                        <i class="bi bi-pencil-square"></i> แก้ไข
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr class="empty-row">
                        <td colspan="7" class="empty-box">
                            <i class="bi bi-box-seam"></i>
                            <h3>ไม่มีสินค้าในระบบ</h3>
                            <p>คลิกปุ่มด้านล่างเพื่อเพิ่มสินค้าชิ้นแรกของคุณ</p>
                            <a href="add_product.php" class="btn-add-large" style="display: inline-flex; margin-top: 0.5rem;">
                                <i class="bi bi-plus-lg"></i> เพิ่มสินค้าใหม่
                            </a>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.querySelector('.page-content')?.classList.add('product-page-bg');
</script>

<?php 
$stmt->close();
$conn->close();
?>

<?php adminPageEnd(); ?>
