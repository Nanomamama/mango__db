<?php
require_once 'auth.php';
require_once __DIR__ . '/../db/db.php';

$code = $_GET['code'] ?? '';

$stmt = $conn->prepare("SELECT * FROM orders WHERE order_code = ? LIMIT 1");
$stmt->bind_param("s", $code);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();

if (!$order) {
    die("<div style='text-align:center;padding:60px;font-family:sans-serif'>
            <p>ไม่พบคำสั่งซื้อ</p>
            <a href='manage_orders.php'>กลับ</a>
        </div>");
}

$id = $order['order_id'];

$item_stmt = $conn->prepare("
    SELECT oi.*, p.product_image
    FROM order_items oi
    LEFT JOIN products p ON oi.product_id = p.product_id
    WHERE oi.order_id = ?
");
$item_stmt->bind_param("i", $id);
$item_stmt->execute();
$items = $item_stmt->get_result();

$statusMap = [
    'pending'   => ['label' => 'รอยืนยัน',     'color' => 'var(--status-pending-text)', 'bg' => 'var(--status-pending-bg)', 'icon' => 'fa-clock', 'border' => 'var(--status-pending-border)'],
    'approved'  => ['label' => 'ยืนยันแล้ว',    'color' => 'var(--status-approved-text)', 'bg' => 'var(--status-approved-bg)', 'icon' => 'fa-check-circle', 'border' => 'var(--status-approved-border)'],
    'rejected'  => ['label' => 'ถูกปฏิเสธ',     'color' => 'var(--status-rejected-text)', 'bg' => 'var(--status-rejected-bg)', 'icon' => 'fa-times-circle', 'border' => 'var(--status-rejected-border)'],
    'completed' => ['label' => 'เสร็จสมบูรณ์',  'color' => 'var(--status-completed-text)', 'bg' => 'var(--status-completed-bg)', 'icon' => 'fa-check-double', 'border' => 'var(--status-completed-border)'],
];
$s = $statusMap[$order['order_status']] ?? ['label' => $order['order_status'], 'color' => 'var(--gray-600)', 'bg' => 'var(--gray-100)', 'icon' => 'fa-info-circle', 'border' => 'var(--gray-300)'];

$total = 0;
$itemRows = [];
while ($row = $items->fetch_assoc()) {
    $row['_sum'] = $row['price'] * $row['quantity'];
    $total += $row['_sum'];
    $itemRows[] = $row;
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
<title>รายละเอียดออเดอร์ #<?= htmlspecialchars($order['order_code']) ?></title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;14..32,400;14..32,500;14..32,600;14..32,700&display=swap" rel="stylesheet">
<style>
:root {
    /* Primary Colors */
    --primary: #2563eb;
    --primary-dark: #1d4ed8;
    --primary-light: #3b82f6;
    
    /* Status Colors - Pending (รอยืนยัน) */
    --status-pending-text: #92400e;
    --status-pending-bg: #fffbeb;
    --status-pending-border: #fde68a;
    
    /* Status Colors - Approved (ยืนยันแล้ว) */
    --status-approved-text: #166534;
    --status-approved-bg: #f0fdf4;
    --status-approved-border: #bbf7d0;
    
    /* Status Colors - Rejected (ถูกปฏิเสธ) */
    --status-rejected-text: #991b1b;
    --status-rejected-bg: #fef2f2;
    --status-rejected-border: #fecaca;
    
    /* Status Colors - Completed (เสร็จสมบูรณ์) */
    --status-completed-text: #155e75;
    --status-completed-bg: #ecfeff;
    --status-completed-border: #a5f3fc;
    
    /* Gray Scale */
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
    
    /* Semantic Colors */
    --success: #10b981;
    --success-dark: #059669;
    --success-bg: #29ff37;
    --success-text: #065f46;
    
    --danger: #ef4444;
    --danger-dark: #dc2626;
    --danger-bg: #fef2f2;
    --danger-text: #991b1b;
    
    --warning: #f59e0b;
    --warning-bg: #fffbeb;
    --warning-text: #92400e;
    
    --info: #0ea5e9;
    --info-bg: #ecfeff;
    --info-text: #155e75;
    
    /* Shadows */
    --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
    --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
    --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
    
    /* Border Radius */
    --radius-sm: 0.5rem;
    --radius-md: 0.75rem;
    --radius-lg: 1rem;
    
    /* Typography */
    --text-primary: var(--gray-900);
    --text-secondary: var(--gray-600);
    --text-muted: var(--gray-500);
    --text-disabled: var(--gray-400);
}

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
    background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
    color: var(--text-primary);
    font-size: 14px;
    line-height: 1.5;
    min-height: 100vh;
}

.container {
    max-width: 1280px;
    margin: 0 auto;
    padding: 2rem 1.5rem;
}

/* Header Section */
.header {
    background: white;
    border-radius: var(--radius-lg);
    padding: 1.5rem 2rem;
    margin-bottom: 1.5rem;
    box-shadow: var(--shadow-sm);
    border: 1px solid var(--gray-200);
}

.header-content {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 1rem;
}

.order-title h1 {
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--text-primary);
    margin-bottom: 0.25rem;
}

.order-title .order-meta {
    display: flex;
    gap: 1rem;
    flex-wrap: wrap;
    font-size: 0.875rem;
    color: var(--text-muted);
}

.order-meta i {
    margin-right: 0.375rem;
    width: 1rem;
    color: var(--gray-400);
}

.status-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1.25rem;
    border-radius: 100px;
    font-size: 0.875rem;
    font-weight: 600;
    background: <?= $s['bg'] ?>;
    color: <?= $s['color'] ?>;
    border: 1px solid <?= $s['border'] ?>;
    box-shadow: var(--shadow-sm);
}

.status-badge i {
    font-size: 1rem;
}

.btn-back {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1.25rem;
    border-radius: var(--radius-sm);
    background: white;
    color: var(--gray-700);
    font-size: 0.875rem;
    font-weight: 500;
    text-decoration: none;
    border: 1px solid var(--gray-200);
    transition: all 0.2s ease;
}

.btn-back:hover {
    background: var(--gray-50);
    border-color: var(--gray-300);
    transform: translateY(-1px);
    box-shadow: var(--shadow-sm);
}

/* Grid Layout */
.order-grid {
    display: grid;
    grid-template-columns: 360px 1fr;
    gap: 1.5rem;
}

@media (max-width: 768px) {
    .container {
        padding: 1rem;
    }
    
    .order-grid {
        grid-template-columns: 1fr;
        gap: 1rem;
    }
    
    .header-content {
        flex-direction: column;
        align-items: flex-start;
    }
}

/* Cards */
.card {
    background: white;
    border-radius: var(--radius-md);
    box-shadow: var(--shadow-sm);
    border: 1px solid var(--gray-200);
    overflow: hidden;
    margin-bottom: 1.5rem;
    transition: all 0.2s ease;
}

.card:last-child {
    margin-bottom: 0;
}

.card-header {
    padding: 1rem 1.5rem;
    background: linear-gradient(to right, var(--gray-50), white);
    border-bottom: 1px solid var(--gray-200);
}

.card-header h3 {
    font-size: 0.75rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: var(--text-secondary);
    margin: 0;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.card-header h3 i {
    font-size: 0.875rem;
    color: var(--gray-500);
}

.card-body {
    padding: 1.25rem 1.5rem;
}

/* Info Rows */
.info-list {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.info-item {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 1rem;
    padding-bottom: 0.75rem;
    border-bottom: 1px solid var(--gray-100);
}

.info-item:last-child {
    border-bottom: none;
    padding-bottom: 0;
}

.info-label {
    font-size: 0.813rem;
    font-weight: 500;
    color: var(--text-secondary);
    min-width: 80px;
}

.info-value {
    font-size: 0.875rem;
    font-weight: 500;
    color: var(--text-primary);
    text-align: right;
    word-break: break-word;
}

/* Chips */
.chip {
    display: inline-flex;
    align-items: center;
    gap: 0.375rem;
    padding: 0.25rem 0.75rem;
    border-radius: 100px;
    font-size: 0.75rem;
    font-weight: 600;
}

.chip-pickup {
    background: var(--success-bg);
    color: var(--success-text);
}

.chip-delivery {
    background: var(--info-bg);
    color: var(--info-text);
}

/* Timeline */
.timeline {
    list-style: none;
    position: relative;
}

.timeline-item {
    display: flex;
    gap: 1rem;
    padding-bottom: 1.5rem;
    position: relative;
}

.timeline-item:last-child {
    padding-bottom: 0;
}

.timeline-item::before {
    content: '';
    position: absolute;
    left: 1rem;
    top: 2rem;
    bottom: 0;
    width: 2px;
    background: linear-gradient(to bottom, var(--gray-200), transparent);
}

.timeline-item:last-child::before {
    display: none;
}

.timeline-icon {
    width: 2rem;
    height: 2rem;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.875rem;
    z-index: 1;
    flex-shrink: 0;
}

.timeline-icon.done {
    background: var(--success-bg);
    color: var(--success-text);
    border: 2px solid var(--success);
}

.timeline-icon.current {
    background: #eff6ff;
    color: var(--primary);
    border: 2px solid var(--primary);
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
}

.timeline-icon.pending {
    background: var(--gray-100);
    color: var(--gray-400);
    border: 2px solid var(--gray-200);
}

.timeline-icon.rejected {
    background: var(--danger-bg);
    color: var(--danger-text);
    border: 2px solid var(--danger);
}

.timeline-content {
    flex: 1;
}

.timeline-title {
    font-weight: 600;
    font-size: 0.875rem;
    margin-bottom: 0.125rem;
    color: var(--text-primary);
}

.timeline-title.current {
    color: var(--primary);
}

.timeline-title.muted {
    color: var(--text-muted);
}

.timeline-desc {
    font-size: 0.75rem;
    color: var(--text-muted);
}

/* Product Table */
.product-table-wrapper {
    overflow-x: auto;
}

.product-table {
    width: 100%;
    border-collapse: collapse;
}

.product-table thead {
    background: var(--gray-50);
    border-bottom: 1px solid var(--gray-200);
}

.product-table th {
    padding: 0.875rem 1rem;
    text-align: left;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: var(--text-secondary);
}

.product-table td {
    padding: 1rem;
    border-bottom: 1px solid var(--gray-100);
    vertical-align: middle;
}

.product-info {
    display: flex;
    align-items: center;
    gap: 0.875rem;
}

.product-image {
    width: 50px;
    height: 50px;
    border-radius: var(--radius-sm);
    object-fit: cover;
    border: 1px solid var(--gray-200);
    background: var(--gray-50);
}

.product-details {
    flex: 1;
}

.product-name {
    font-weight: 600;
    color: var(--text-primary);
    margin-bottom: 0.25rem;
}

.product-price {
    font-size: 0.75rem;
    color: var(--text-muted);
}

.quantity-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 2.5rem;
    padding: 0.25rem 0.75rem;
    background: var(--gray-100);
    border-radius: 100px;
    font-weight: 600;
    font-size: 0.875rem;
    color: var(--gray-700);
}

.price-amount {
    font-weight: 600;
    color: var(--text-primary);
}

.product-table tfoot td {
    background: var(--gray-50);
    border-top: 2px solid var(--gray-200);
    font-weight: 700;
    font-size: 1rem;
    padding: 1rem;
    color: var(--text-primary);
}

/* Summary Card */
.summary-card {
    background: linear-gradient(135deg, var(--gray-900) 0%, #1e293b 100%);
    border-radius: var(--radius-md);
    padding: 1.25rem 1.5rem;
    margin-bottom: 1rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 1rem;
    box-shadow: var(--shadow-md);
}

.summary-info {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.summary-label {
    font-size: 0.75rem;
    color: var(--gray-400);
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.summary-amount {
    font-size: 1.75rem;
    font-weight: 700;
    color: white;
}

.summary-amount small {
    font-size: 0.875rem;
    font-weight: 400;
    color: var(--gray-400);
    margin-left: 0.25rem;
}

.summary-badge {
    background: rgba(255, 255, 255, 0.1);
    border-radius: 100px;
    padding: 0.5rem 1rem;
    font-weight: 600;
    font-size: 0.875rem;
    color: white;
    border: 1px solid rgba(255, 255, 255, 0.2);
}

/* Alert Box */
.alert {
    padding: 1rem 1.25rem;
    border-radius: var(--radius-sm);
    margin-bottom: 1rem;
    display: flex;
    gap: 0.75rem;
    border-left: 3px solid;
}

.alert-danger {
    background: var(--danger-bg);
    color: var(--danger-text);
    border-left-color: var(--danger);
}

.alert-success {
    background: var(--success-bg);
    color: var(--success-text);
    border-left-color: var(--success);
}

.alert i {
    font-size: 1.125rem;
    flex-shrink: 0;
}

/* Action Card */
.action-card {
    background: white;
    border-radius: var(--radius-md);
    border: 1px solid var(--gray-200);
    padding: 1.25rem 1.5rem;
}

.action-title {
    font-size: 0.75rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: var(--text-secondary);
    margin-bottom: 1rem;
}

.btn-group {
    display: flex;
    gap: 0.75rem;
    flex-wrap: wrap;
}

.btn {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.625rem 1.25rem;
    border-radius: var(--radius-sm);
    font-size: 0.875rem;
    font-weight: 600;
    text-decoration: none;
    cursor: pointer;
    transition: all 0.2s ease;
    border: none;
    font-family: inherit;
}

.btn-primary {
    background: var(--success);
    color: white;
}

.btn-primary:hover {
    background: var(--success-dark);
    transform: translateY(-1px);
    box-shadow: var(--shadow-md);
}

.btn-secondary {
    background: var(--info);
    color: white;
}

.btn-secondary:hover {
    background: #0e7490;
    transform: translateY(-1px);
}

.btn-danger {
    background: var(--danger);
    color: white;
}

.btn-danger:hover {
    background: var(--danger-dark);
    transform: translateY(-1px);
}

.btn-outline-danger {
    background: var(--danger-bg);
    color: var(--danger-text);
    border: 1px solid var(--status-rejected-border);
}

.btn-outline-danger:hover {
    background: var(--danger);
    color: white;
    border-color: var(--danger);
}

/* Reject Form */
.reject-form {
    margin-top: 1rem;
    padding-top: 1rem;
    border-top: 1px solid var(--gray-200);
    animation: slideDown 0.3s ease;
}

@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.form-label {
    display: block;
    font-size: 0.813rem;
    font-weight: 600;
    color: var(--text-primary);
    margin-bottom: 0.5rem;
}

.form-label .required {
    color: var(--danger);
    margin-left: 0.25rem;
}

.form-textarea {
    width: 100%;
    padding: 0.75rem;
    border: 1px solid var(--gray-300);
    border-radius: var(--radius-sm);
    font-family: inherit;
    font-size: 0.875rem;
    resize: vertical;
    transition: all 0.2s ease;
    color: var(--text-primary);
}

.form-textarea:focus {
    outline: none;
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
}

.form-textarea::placeholder {
    color: var(--text-muted);
}

.btn-submit {
    width: 100%;
    margin-top: 0.75rem;
    background: var(--danger);
    color: white;
    justify-content: center;
}

.btn-submit:hover {
    background: var(--danger-dark);
}

/* Responsive Adjustments */
@media (max-width: 640px) {
    .product-table th,
    .product-table td {
        padding: 0.75rem;
    }
    
    .product-image {
        width: 40px;
        height: 40px;
    }
    
    .summary-amount {
        font-size: 1.25rem;
    }
    
    .card-body {
        padding: 1rem;
    }
    
    .card-header {
        padding: 0.75rem 1rem;
    }
}
</style>
</head>
<body>
<div class="container">
    <!-- Header -->
    <div class="header">
        <div class="header-content">
            <div class="order-title">
                <h1>
                    <i class="fas fa-shopping-cart" style="color: var(--primary); margin-right: 0.5rem;"></i>
                    ออเดอร์ #<?= htmlspecialchars($order['order_code']) ?>
                </h1>
                <div class="order-meta">
                    <span><i class="far fa-calendar-alt"></i> <?= date('d/m/Y', strtotime($order['order_date'])) ?></span>
                    <span><i class="far fa-clock"></i> <?= date('H:i น.', strtotime($order['order_date'])) ?></span>
                </div>
            </div>
            <div style="display: flex; gap: 0.75rem; align-items: center;">
                <span class="status-badge">
                    <i class="fas <?= $s['icon'] ?>"></i>
                    <?= $s['label'] ?>
                </span>
                <a href="manage_orders.php" class="btn-back">
                    <i class="fas fa-arrow-left"></i>
                    กลับ
                </a>
            </div>
        </div>
    </div>

    <!-- Grid Layout -->
    <div class="order-grid">
        <!-- Left Column -->
        <div>
            <!-- Customer Card -->
            <div class="card">
                <div class="card-header">
                    <h3><i class="fas fa-user"></i> ข้อมูลลูกค้า</h3>
                </div>
                <div class="card-body">
                    <div class="info-list">
                        <div class="info-item">
                            <span class="info-label">ชื่อ-นามสกุล</span>
                            <span class="info-value"><?= htmlspecialchars($order['customer_name']) ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">เบอร์โทรศัพท์</span>
                            <span class="info-value">
                                <a href="tel:<?= htmlspecialchars($order['customer_phone']) ?>" style="color: var(--primary); text-decoration: none;">
                                    <i class="fas fa-phone-alt" style="font-size: 0.75rem;"></i> <?= htmlspecialchars($order['customer_phone']) ?>
                                </a>
                            </span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">ที่อยู่</span>
                            <span class="info-value"><?= nl2br(htmlspecialchars($order['customer_address'] ?? '-')) ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Delivery Card -->
            <div class="card">
                <div class="card-header">
                    <h3><i class="fas fa-truck"></i> การรับสินค้า</h3>
                </div>
                <div class="card-body">
                    <div class="info-list">
                        <div class="info-item">
                            <span class="info-label">ช่องทาง</span>
                            <span class="info-value">
                                <?php if ($order['receive_type'] === 'pickup'): ?>
                                    <span class="chip chip-pickup">
                                        <i class="fas fa-store"></i> รับที่สวน
                                    </span>
                                <?php else: ?>
                                    <span class="chip chip-delivery">
                                        <i class="fas fa-motorcycle"></i> ส่งถึงบ้าน
                                    </span>
                                <?php endif; ?>
                            </span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">วันที่นัดรับ</span>
                            <span class="info-value" style="color: var(--primary); font-weight: 600;">
                                <?= $order['receive_datetime']
                                    ? date('d/m/Y H:i', strtotime($order['receive_datetime']))
                                    : '<span style="color: var(--text-muted);">ไม่ระบุ</span>' ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Timeline Card -->
            <div class="card">
                <div class="card-header">
                    <h3><i class="fas fa-tasks"></i> สถานะออเดอร์</h3>
                </div>
                <div class="card-body">
                    <?php
                    $flow = ['pending' => 'รอยืนยัน', 'approved' => 'ยืนยันแล้ว', 'completed' => 'เสร็จสมบูรณ์'];
                    $flowKeys = array_keys($flow);
                    $currentIdx = array_search($order['order_status'], $flowKeys);
                    ?>
                    <ul class="timeline">
                    <?php if ($order['order_status'] === 'rejected'): ?>
                        <li class="timeline-item">
                            <div class="timeline-icon rejected">
                                <i class="fas fa-times"></i>
                            </div>
                            <div class="timeline-content">
                                <div class="timeline-title" style="color: var(--danger-text);">ถูกปฏิเสธ</div>
                                <div class="timeline-desc">ไม่สามารถดำเนินการต่อได้</div>
                            </div>
                        </li>
                    <?php else: ?>
                        <?php foreach ($flow as $key => $label):
                            $idx    = array_search($key, $flowKeys);
                            $isDone = $idx < $currentIdx;
                            $isNow  = $idx === $currentIdx;
                        ?>
                        <li class="timeline-item">
                            <div class="timeline-icon <?= $isDone ? 'done' : ($isNow ? 'current' : 'pending') ?>">
                                <i class="fas <?= $isDone ? 'fa-check' : ($isNow ? 'fa-circle' : 'fa-circle') ?>" style="font-size: <?= $isNow ? '0.5rem' : '0.75rem' ?>"></i>
                            </div>
                            <div class="timeline-content">
                                <div class="timeline-title <?= $isNow ? 'current' : ($isDone ? '' : 'muted') ?>">
                                    <?= $label ?>
                                </div>
                                <div class="timeline-desc">
                                    <?= $isDone ? '✓ เสร็จสิ้น' : ($isNow ? 'ดำเนินการอยู่' : 'รอดำเนินการ') ?>
                                </div>
                            </div>
                        </li>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Right Column -->
        <div>
            <!-- Products Card -->
            <div class="card">
                <div class="card-header">
                    <h3><i class="fas fa-boxes"></i> รายการสินค้า</h3>
                </div>
                <div class="product-table-wrapper">
                    <table class="product-table">
                        <thead>
                            <tr>
                                <th>สินค้า</th>
                                <th style="text-align: center;">จำนวน</th>
                                <th style="text-align: right;">ราคา</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($itemRows as $item):
                            $img = !empty($item['product_image'])
                                ? "../admin/uploads/products/" . $item['product_image']
                                : "../assets/no-image.png";
                        ?>
                            <tr>
                                <td>
                                    <div class="product-info">
                                        <img src="<?= htmlspecialchars($img) ?>"
                                             class="product-image" alt=""
                                             onerror="this.src='../assets/no-image.png'">
                                        <div class="product-details">
                                            <div class="product-name"><?= htmlspecialchars($item['product_name']) ?></div>
                                            <div class="product-price">฿<?= number_format($item['price'], 2) ?> / ชิ้น</div>
                                        </div>
                                    </div>
                                </td>
                                <td style="text-align: center;">
                                    <span class="quantity-badge">× <?= $item['quantity'] ?></span>
                                </td>
                                <td style="text-align: right;">
                                    <span class="price-amount">฿<?= number_format($item['_sum'], 2) ?></span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="2" style="text-align: right;">ยอดรวมทั้งสิ้น</td>
                                <td style="text-align: right; font-size: 1.125rem;">฿<?= number_format($total, 2) ?></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <!-- Summary Card -->
            <div class="summary-card">
                <div class="summary-info">
                    <div class="summary-label">ยอดชำระเงิน</div>
                    <div class="summary-amount">
                        ฿<?= number_format($total, 2) ?>
                        <small>บาท</small>
                    </div>
                </div>
                <div class="summary-badge">
                    <i class="fas <?= $s['icon'] ?>"></i> <?= $s['label'] ?>
                </div>
            </div>

            <!-- Rejection Reason -->
            <?php if ($order['order_status'] === 'rejected' && !empty($order['admin_note'])): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle"></i>
                    <div>
                        <strong style="display: block; margin-bottom: 0.25rem;">เหตุผลที่ปฏิเสธ</strong>
                        <?= nl2br(htmlspecialchars($order['admin_note'])) ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Action Card -->
            <div class="action-card">
                <div class="action-title">
                    <i class="fas fa-cog"></i> การดำเนินการ
                </div>

                <?php if ($order['order_status'] === 'pending'): ?>
                    <div class="btn-group">
                        <a href="update_order.php?id=<?= $id ?>&s=approved" class="btn btn-primary">
                            <i class="fas fa-check-circle"></i> ยืนยันออเดอร์
                        </a>
                        <button type="button" class="btn btn-outline-danger" onclick="toggleReject()">
                            <i class="fas fa-times-circle"></i> ปฏิเสธ
                        </button>
                    </div>

                    <div id="rejectBox" style="display: none;">
                        <form action="update_order.php" method="post" class="reject-form">
                            <input type="hidden" name="id" value="<?= $id ?>">
                            <input type="hidden" name="s" value="rejected">
                            <label class="form-label">
                                เหตุผลที่ปฏิเสธ
                                <span class="required">*</span>
                            </label>
                            <textarea name="admin_note" class="form-textarea" rows="3" required
                                placeholder="กรุณาระบุเหตุผลที่ปฏิเสธ เช่น สินค้าหมด, ที่อยู่ไม่ถูกต้อง..."></textarea>
                            <button type="submit" class="btn btn-submit">
                                <i class="fas fa-check"></i> ยืนยันการปฏิเสธ
                            </button>
                        </form>
                    </div>

                <?php elseif ($order['order_status'] === 'approved'): ?>
                    <a href="update_order.php?id=<?= $id ?>&s=completed"
                       onclick="return confirm('ยืนยันว่าออเดอร์นี้เสร็จสมบูรณ์?')"
                       class="btn btn-secondary">
                        <i class="fas fa-flag-checkered"></i> เสร็จสิ้นออเดอร์
                    </a>

                <?php elseif ($order['order_status'] === 'rejected'): ?>
                    <a href="delete_order.php?id=<?= $id ?>"
                       onclick="return confirm('ยืนยันการลบคำสั่งซื้อนี้? การดำเนินการนี้ไม่สามารถกู้คืนได้')"
                       class="btn btn-danger">
                        <i class="fas fa-trash-alt"></i> ลบคำสั่งซื้อ
                    </a>

                <?php elseif ($order['order_status'] === 'completed'): ?>
                    <div class="alert alert-success" style="margin: 0;">
                        <i class="fas fa-check-circle"></i>
                        <div>ออเดอร์นี้เสร็จสมบูรณ์แล้ว</div>
                    </div>

                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
function toggleReject() {
    const box = document.getElementById('rejectBox');
    const isVisible = box.style.display === 'block';
    box.style.display = isVisible ? 'none' : 'block';
    if (!isVisible) {
        box.querySelector('textarea').focus();
    }
}
</script>
</body>
</html>