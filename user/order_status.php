<?php
session_start();
require_once __DIR__ . '/../db/db.php';

$member_id = $_SESSION['member_id'] ?? null;
$orders = [];
$search_performed = false;
$error = '';
$success = '';

// ถ้าสมาชิกเข้าสู่ระบบ
if ($member_id) {
    $stmt = $conn->prepare("
        SELECT * FROM orders 
        WHERE member_id = ?
        ORDER BY order_date DESC
    ");
    $stmt->bind_param("i", $member_id);
    $stmt->execute();
    $orders = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

// ถ้ามีการค้นหาโดยเบอร์โทร
if (isset($_POST['phone']) && !empty($_POST['phone'])) {
    $search_performed = true;
    $phone = $_POST['phone'];
    
    // ลบช่องว่างและอักขระพิเศษ
    $phone = trim($phone);
    $phone = preg_replace('/[^0-9]/', '', $phone);
    
    // ตรวจสอบความยาวเบอร์โทร
    if (strlen($phone) < 9 || strlen($phone) > 10) {
        $error = "กรุณากรอกเบอร์โทรศัพท์ให้ถูกต้อง (9-10 หลัก)";
    } else {
        // ตรวจสอบใน database ด้วยรูปแบบต่างๆ
        $stmt = $conn->prepare("
            SELECT * FROM orders 
            WHERE customer_phone LIKE ?
            ORDER BY order_date DESC
        ");
        $search_phone = "%$phone%";
        $stmt->bind_param("s", $search_phone);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $orders = $result->fetch_all(MYSQLI_ASSOC);
            $success = "พบคำสั่งซื้อ " . count($orders) . " รายการ";
        } else {
            $error = "ไม่พบคำสั่งซื้อจากเบอร์โทรศัพท์นี้";
            
            // Debug: ตรวจสอบข้อมูลใน database
            $checkStmt = $conn->prepare("SELECT customer_phone FROM orders LIMIT 5");
            $checkStmt->execute();
            $samplePhones = $checkStmt->get_result()->fetch_all(MYSQLI_ASSOC);
            
            // แสดงตัวอย่างเบอร์โทรใน database (สำหรับ debug)
            // สามารถลบส่วนนี้ได้หลังจากแก้ไขแล้ว
            error_log("Search phone: $phone");
            error_log("Sample phones in DB: " . json_encode($samplePhones));
        }
    }
}
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title>ติดตามสถานะคำสั่งซื้อ</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
<style>
        /* CSS เดิมทั้งหมด */
        :root {
            --white: #ffffff;
            --white-soft: #fafafa;
            --white-mute: #f5f5f5;
            --gray-light: #e5e5e5;
            --gray-soft: #d4d4d4;
            --gray: #a3a3a3;
            --gray-dark: #525252;
            --black: #171717;
            --blue-light: #3b82f6;
            --blue: #2563eb;
            --green-light: #10b981;
            --green: #059669;
            --red-light: #ef4444;
            --red: #dc2626;
            --yellow-light: #f59e0b;
            --yellow: #d97706;
            --indigo-light: #6366f1;
            --indigo: #4f46e5;
            --border-radius: 12px;
            --shadow-sm: 0 1px 3px rgba(0,0,0,0.05);
            --shadow: 0 4px 6px -1px rgba(0,0,0,0.05), 0 2px 4px -1px rgba(0,0,0,0.03);
            --shadow-md: 0 10px 15px -3px rgba(0,0,0,0.05), 0 4px 6px -2px rgba(0,0,0,0.025);
            --shadow-lg: 0 20px 25px -5px rgba(0,0,0,0.05), 0 10px 10px -5px rgba(0,0,0,0.01);
        }
        
        * {
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Noto Sans', sans-serif;
            background-color: var(--white-soft);
            color: var(--black);
            line-height: 1.6;
            margin: 0;
            padding: 0;
            -webkit-font-smoothing: antialiased;
        }
        
        .page-container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 24px 16px;
        }
        
        /* เพิ่ม alert styles */
        .alert-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
            max-width: 400px;
        }
        
        .alert-custom {
            background: var(--white);
            border: 1px solid;
            border-radius: var(--border-radius);
            padding: 16px;
            box-shadow: var(--shadow-lg);
            display: flex;
            align-items: flex-start;
            gap: 12px;
            animation: slideIn 0.3s ease;
            margin-bottom: 10px;
        }
        
        @keyframes slideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        
        .alert-error {
            border-color: var(--red-light);
            background: rgba(239, 68, 68, 0.05);
        }
        
        .alert-success {
            border-color: var(--green-light);
            background: rgba(16, 185, 129, 0.05);
        }
        
        .alert-icon {
            font-size: 20px;
            margin-top: 2px;
        }
        
        .alert-error .alert-icon {
            color: var(--red);
        }
        
        .alert-success .alert-icon {
            color: var(--green);
        }
        
        .alert-content {
            flex: 1;
        }
        
        .alert-title {
            font-weight: 600;
            margin-bottom: 4px;
            font-size: 15px;
        }
        
        .alert-message {
            font-size: 14px;
            color: var(--gray-dark);
        }
        
        .alert-close {
            background: none;
            border: none;
            color: var(--gray);
            cursor: pointer;
            font-size: 18px;
            padding: 0;
            width: 24px;
            height: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 4px;
            transition: all 0.2s ease;
        }
        
        .alert-close:hover {
            background: var(--white-mute);
            color: var(--black);
        }
        
        /* ส่วนอื่นๆ เหมือนเดิม... */
        .back-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 20px;
            background: var(--white);
            border: 1px solid var(--gray-light);
            border-radius: 8px;
            color: var(--gray-dark);
            text-decoration: none;
            font-weight: 500;
            font-size: 14px;
            transition: all 0.2s ease;
            margin-bottom: 20px;
            box-shadow: var(--shadow-sm);
        }
        
        .back-btn:hover {
            background: var(--white-mute);
            border-color: var(--gray-soft);
            transform: translateY(-1px);
            box-shadow: var(--shadow);
            color: var(--black);
        }
        
        .main-header {
            background: var(--white);
            border-radius: var(--border-radius);
            padding: 32px;
            box-shadow: var(--shadow);
            border: 1px solid var(--gray-light);
            text-align: center;
            margin-bottom: 24px;
        }
        
        .header-icon {
            width: 64px;
            height: 64px;
            background: linear-gradient(135deg, var(--indigo-light), var(--indigo));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            color: white;
            font-size: 28px;
        }
        
        .page-title {
            font-size: 28px;
            font-weight: 600;
            color: var(--black);
            margin-bottom: 8px;
        }
        
        .page-subtitle {
            font-size: 16px;
            color: var(--gray-dark);
            margin-bottom: 0;
        }
        
        /* Search Form */
        .search-section {
            background: var(--white);
            border-radius: var(--border-radius);
            padding: 24px;
            box-shadow: var(--shadow);
            border: 1px solid var(--gray-light);
            margin-bottom: 32px;
        }
        
        .form-label {
            font-weight: 500;
            color: var(--black);
            margin-bottom: 8px;
            display: block;
        }
        
        .input-group {
            position: relative;
        }
        
        .input-group-text {
            background: var(--white);
            border: 1px solid var(--gray-light);
            border-right: none;
            padding: 0 16px;
        }
        
        .form-control {
            height: 48px;
            border: 1px solid var(--gray-light);
            border-left: none;
            background: var(--white);
            color: var(--black);
            font-size: 16px;
            transition: all 0.2s ease;
        }
        
        .form-control:focus {
            border-color: var(--indigo);
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
        }
        
        .search-btn {
            background: var(--indigo);
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            font-weight: 500;
            font-size: 16px;
            transition: all 0.2s ease;
            width: 100%;
            margin-top: 16px;
            cursor: pointer;
        }
        
        .search-btn:hover {
            background: var(--indigo-light);
            transform: translateY(-1px);
            box-shadow: var(--shadow);
        }
        
        /* Orders Grid */
        .orders-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 24px;
            margin-bottom: 32px;
        }
        
        @media (max-width: 768px) {
            .orders-grid {
                grid-template-columns: 1fr;
            }
        }
        
        /* Order Card */
        .order-card {
            background: var(--white);
            border-radius: var(--border-radius);
            border: 1px solid var(--gray-light);
            box-shadow: var(--shadow);
            overflow: hidden;
            transition: all 0.3s ease;
            position: relative;
        }
        
        .order-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-lg);
        }
        
        .card-header {
            padding: 20px;
            border-bottom: 1px solid var(--gray-light);
            position: relative;
        }
        
        .status-indicator {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
        }
        
        .status-pending .status-indicator {
            background: linear-gradient(90deg, var(--yellow-light), var(--yellow));
        }
        
        .status-approved .status-indicator {
            background: linear-gradient(90deg, var(--green-light), var(--green));
        }
        
        .status-rejected .status-indicator {
            background: linear-gradient(90deg, var(--red-light), var(--red));
        }
        
        .status-completed .status-indicator {
            background: linear-gradient(90deg, var(--blue-light), var(--blue));
        }
        
        .card-title-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 12px;
        }
        
        .order-code {
            font-size: 16px;
            font-weight: 600;
            color: var(--black);
        }
        
        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .status-pending .status-badge {
            background: rgba(245, 158, 11, 0.1);
            color: var(--yellow);
            border: 1px solid rgba(245, 158, 11, 0.2);
        }
        
        .status-approved .status-badge {
            background: rgba(16, 185, 129, 0.1);
            color: var(--green);
            border: 1px solid rgba(16, 185, 129, 0.2);
        }
        
        .status-rejected .status-badge {
            background: rgba(239, 68, 68, 0.1);
            color: var(--red);
            border: 1px solid rgba(239, 68, 68, 0.2);
        }
        
        .status-completed .status-badge {
            background: rgba(59, 130, 246, 0.1);
            color: var(--blue);
            border: 1px solid rgba(59, 130, 246, 0.2);
        }
        
        .order-date {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
            color: var(--gray);
        }
        
        .card-content {
            padding: 20px;
        }
        
        /* Customer Info */
        .customer-info {
            background: var(--white-mute);
            border-radius: 8px;
            padding: 16px;
            margin-bottom: 16px;
            border: 1px solid var(--gray-light);
        }
        
        .info-row {
            display: flex;
            align-items: flex-start;
            margin-bottom: 12px;
        }
        
        .info-row:last-child {
            margin-bottom: 0;
        }
        
        .info-icon {
            width: 20px;
            color: var(--gray);
            margin-top: 2px;
            flex-shrink: 0;
        }
        
        .info-content {
            margin-left: 12px;
            flex: 1;
        }
        
        .info-label {
            font-size: 12px;
            color: var(--gray);
            margin-bottom: 2px;
        }
        
        .info-value {
            font-size: 14px;
            font-weight: 500;
            color: var(--black);
        }
        
        /* Products Preview */
        .products-preview {
            margin-bottom: 16px;
        }
        
        .section-title {
            font-size: 14px;
            font-weight: 600;
            color: var(--black);
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
            gap: 12px;
        }
        
        @media (max-width: 480px) {
            .products-grid {
                grid-template-columns: 1fr;
            }
        }
        
        .product-item {
            background: var(--white);
            border-radius: 8px;
            border: 1px solid var(--gray-light);
            padding: 12px;
            transition: all 0.2s ease;
        }
        
        .product-item:hover {
            border-color: var(--gray-soft);
            transform: translateY(-2px);
        }
        
        .product-image {
            width: 100%;
            height: 80px;
            object-fit: cover;
            border-radius: 6px;
            margin-bottom: 8px;
            background: var(--white-mute);
        }
        
        .product-name {
            font-size: 13px;
            font-weight: 500;
            color: var(--black);
            margin-bottom: 4px;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            line-height: 1.4;
        }
        
        .product-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 12px;
            color: var(--gray);
        }
        
        /* Order Summary */
        .order-summary {
            background: var(--white-mute);
            border-radius: 8px;
            padding: 16px;
            border: 1px solid var(--gray-light);
            margin-bottom: 16px;
        }
        
        .summary-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 0;
        }
        
        .summary-row:first-child {
            padding-top: 0;
        }
        
        .summary-row:last-child {
            padding-bottom: 0;
            border-top: 1px solid var(--gray-light);
            padding-top: 12px;
            margin-top: 8px;
        }
        
        .summary-label {
            font-size: 14px;
            color: var(--gray-dark);
        }
        
        .summary-value {
            font-size: 14px;
            font-weight: 500;
            color: var(--black);
        }
        
        .summary-total {
            font-size: 16px;
            font-weight: 600;
            color: var(--black);
        }
        
        /* Admin Note */
        .admin-note {
            background: rgba(99, 102, 241, 0.05);
            border-radius: 8px;
            padding: 16px;
            border-left: 3px solid var(--indigo);
            margin-top: 16px;
        }
        
        .note-label {
            font-size: 13px;
            font-weight: 600;
            color: var(--indigo);
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        
        .note-content {
            font-size: 13px;
            color: var(--gray-dark);
            line-height: 1.6;
        }
        
        /* Action Buttons */
        .card-footer {
            padding: 20px;
            border-top: 1px solid var(--gray-light);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .detail-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 16px;
            background: var(--white);
            border: 1px solid var(--gray-light);
            border-radius: 6px;
            color: var(--gray-dark);
            text-decoration: none;
            font-size: 13px;
            font-weight: 500;
            transition: all 0.2s ease;
        }
        
        .detail-btn:hover {
            background: var(--white-mute);
            border-color: var(--gray-soft);
            color: var(--black);
        }
        
        .view-products-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 16px;
            background: var(--indigo);
            border: 1px solid var(--indigo);
            border-radius: 6px;
            color: white;
            text-decoration: none;
            font-size: 13px;
            font-weight: 500;
            transition: all 0.2s ease;
        }
        
        .view-products-btn:hover {
            background: var(--indigo-light);
            transform: translateY(-1px);
            box-shadow: var(--shadow);
            color: white;
        }
        
        /* Empty State */
        .empty-state {
            background: var(--white);
            border-radius: var(--border-radius);
            padding: 64px 32px;
            text-align: center;
            box-shadow: var(--shadow);
            border: 1px solid var(--gray-light);
        }
        
        .empty-icon {
            font-size: 48px;
            color: var(--gray);
            margin-bottom: 20px;
            opacity: 0.5;
        }
        
        .empty-title {
            font-size: 20px;
            font-weight: 600;
            color: var(--black);
            margin-bottom: 12px;
        }
        
        .empty-description {
            font-size: 15px;
            color: var(--gray-dark);
            margin-bottom: 24px;
            max-width: 400px;
            margin-left: auto;
            margin-right: auto;
        }
        
        .empty-action {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 24px;
            background: var(--white);
            border: 1px solid var(--gray-light);
            border-radius: 8px;
            color: var(--gray-dark);
            text-decoration: none;
            font-weight: 500;
            transition: all 0.2s ease;
        }
        
        .empty-action:hover {
            background: var(--white-mute);
            border-color: var(--gray-soft);
            color: var(--black);
        }
        
        /* Orders Count */
        .orders-count {
            font-size: 14px;
            color: var(--gray);
            margin-bottom: 16px;
        }
        
        .count-badge {
            display: inline-block;
            background: var(--white);
            padding: 4px 12px;
            border-radius: 20px;
            font-weight: 500;
            color: var(--gray-dark);
            border: 1px solid var(--gray-light);
        }
        
        /* Loading Animation */
        .loading-spinner {
            display: inline-block;
            width: 16px;
            height: 16px;
            border: 2px solid var(--gray-light);
            border-top-color: var(--indigo);
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
    </style>
</head>

<body>
<?php include __DIR__ . '/navbar.php'; ?>
<?php include __DIR__ . '/fb_chat_button.php'; ?>
    <!-- Alert Container -->
    <div class="alert-container">
        <?php if ($error): ?>
        <div class="alert-custom alert-error" id="errorAlert">
            <i class="bi bi-exclamation-circle alert-icon"></i>
            <div class="alert-content">
                <div class="alert-title">เกิดข้อผิดพลาด</div>
                <div class="alert-message"><?= htmlspecialchars($error) ?></div>
            </div>
            <button class="alert-close" onclick="closeAlert('errorAlert')">&times;</button>
        </div>
        <?php endif; ?>
        
        <?php if ($success): ?>
        <div class="alert-custom alert-success" id="successAlert">
            <i class="bi bi-check-circle alert-icon"></i>
            <div class="alert-content">
                <div class="alert-title">สำเร็จ</div>
                <div class="alert-message"><?= htmlspecialchars($success) ?></div>
            </div>
            <button class="alert-close" onclick="closeAlert('successAlert')">&times;</button>
        </div>
        <?php endif; ?>
    </div>
    
    <div class="page-container">
        <!-- Back Button -->
        <a href="./products.php" class="back-btn">
            <i class="bi bi-arrow-left"></i>
            กลับหน้าหลัก
        </a>
        
        <!-- Main Header -->
        <div class="main-header">
            <div class="header-icon">
                <i class="bi bi-search"></i>
            </div>
            <h1 class="page-title">ติดตามสถานะคำสั่งซื้อ</h1>
            <p class="page-subtitle">ตรวจสอบสถานะและรายละเอียดคำสั่งซื้อของคุณ</p>
        </div>
        
        <!-- Search Form (for non-members) -->
        <?php if (!$member_id): ?>
        <div class="search-section">
            <form method="post" class="mb-0">
                <div class="mb-4">
                    <label for="phone" class="form-label">กรอกเบอร์โทรศัพท์ที่ใช้สั่งซื้อ</label>
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="bi bi-telephone"></i>
                        </span>
                        <input type="tel" 
                               name="phone" 
                               id="phone"
                               class="form-control"
                               placeholder="เช่น 0812345678"
                               required
                               value="<?= isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : '' ?>"
                               title="กรุณากรอกเบอร์โทรศัพท์">
                    </div>
                    <div class="text-sm text-gray mt-2">
                        <i class="bi bi-info-circle"></i>
                        สำหรับลูกค้าทั่วไปที่ไม่ได้เข้าสู่ระบบ
                    </div>
                </div>
                <button type="submit" class="search-btn">
                    <i class="bi bi-search me-2"></i>
                    ค้นหาสถานะ
                </button>
            </form>
        </div>
        <?php endif; ?>
        
        <?php if (!empty($orders)): ?>
            <!-- Orders Count -->
            <div class="orders-count">
                พบคำสั่งซื้อทั้งหมด <span class="count-badge"><?= count($orders) ?> รายการ</span>
            </div>
            
            <!-- Orders Grid -->
            <div class="orders-grid">
                <?php foreach ($orders as $o): ?>
                    <?php
                    // ดึงรายการสินค้าสำหรับออเดอร์นี้
                    $itemStmt = $conn->prepare("
                        SELECT 
                            oi.quantity,
                            oi.price,
                            p.product_name,
                            p.product_image,
                            p.unit
                        FROM order_items oi
                        JOIN products p ON oi.product_id = p.product_id
                        WHERE oi.order_id = ?
                        LIMIT 3
                    ");
                    $itemStmt->bind_param("i", $o['order_id']);
                    $itemStmt->execute();
                    $items = $itemStmt->get_result();
                    
                    // คำนวณยอดรวม
                    $totalStmt = $conn->prepare("
                        SELECT SUM(price * quantity) as total
                        FROM order_items 
                        WHERE order_id = ?
                    ");
                    $totalStmt->bind_param("i", $o['order_id']);
                    $totalStmt->execute();
                    $totalResult = $totalStmt->get_result()->fetch_assoc();
                    $orderTotal = $totalResult['total'] ?? 0;
                    
                    // แปลงสถานะ
                    $statusText = match ($o['order_status']) {
                        'pending' => 'รอยืนยัน',
                        'approved' => 'ยืนยันแล้ว',
                        'rejected' => 'ถูกปฏิเสธ',
                        'completed' => 'เสร็จสมบูรณ์',
                        default => $o['order_status']
                    };
                    ?>
                    
                    <div class="order-card status-<?= $o['order_status'] ?>">
                        <!-- Status Indicator -->
                        <div class="status-indicator"></div>
                        
                        <!-- Card Header -->
                        <div class="card-header">
                            <div class="card-title-row">
                                <div class="order-code">#<?= htmlspecialchars($o['order_code']) ?></div>
                                <span class="status-badge">
                                    <i class="bi bi-circle-fill"></i>
                                    <?= $statusText ?>
                                </span>
                            </div>
                            <div class="order-date">
                                <i class="bi bi-calendar3"></i>
                                <?= date('d/m/Y H:i', strtotime($o['order_date'])) ?>
                            </div>
                        </div>
                        
                        <!-- Card Content -->
                        <div class="card-content">
                            <!-- Customer Information -->
                            <div class="customer-info">
                                <div class="info-row">
                                    <i class="bi bi-person info-icon"></i>
                                    <div class="info-content">
                                        <div class="info-label">ลูกค้า</div>
                                        <div class="info-value"><?= htmlspecialchars($o['customer_name']) ?></div>
                                    </div>
                                </div>
                                <div class="info-row">
                                    <i class="bi bi-telephone info-icon"></i>
                                    <div class="info-content">
                                        <div class="info-label">เบอร์โทรศัพท์</div>
                                        <div class="info-value"><?= htmlspecialchars($o['customer_phone']) ?></div>
                                    </div>
                                </div>
                                <div class="info-row">
                                    <i class="bi bi-truck info-icon"></i>
                                    <div class="info-content">
                                        <div class="info-label">วิธีรับสินค้า</div>
                                        <div class="info-value">
                                            <?= $o['receive_type'] == 'pickup' ? 'รับที่สวน' : 'จัดส่งถึงบ้าน' ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Products Preview -->
                            <div class="products-preview">
                                <div class="section-title">
                                    <i class="bi bi-basket"></i>
                                    รายการสินค้า
                                </div>
                                
                                <?php if ($items->num_rows > 0): ?>
                                    <div class="products-grid">
                                        <?php while ($item = $items->fetch_assoc()): ?>
                                            <div class="product-item">
                                                <?php if (!empty($item['product_image'])): ?>
                                                    <img src="../admin/uploads/products/<?= htmlspecialchars($item['product_image']) ?>" 
                                                         class="product-image" 
                                                         alt="<?= htmlspecialchars($item['product_name']) ?>">
                                                <?php else: ?>
                                                    <div class="product-image d-flex align-items-center justify-content-center">
                                                        <i class="bi bi-image text-gray" style="font-size: 24px;"></i>
                                                    </div>
                                                <?php endif; ?>
                                                <div class="product-name">
                                                    <?= htmlspecialchars($item['product_name']) ?>
                                                </div>
                                                <div class="product-meta">
                                                    <span><?= $item['quantity'] ?> <?= htmlspecialchars($item['unit'] ?? 'ชิ้น') ?></span>
                                                    <span>฿<?= number_format($item['price'], 0) ?></span>
                                                </div>
                                            </div>
                                        <?php endwhile; ?>
                                    </div>
                                <?php else: ?>
                                    <div class="text-center text-gray py-3">
                                        <i class="bi bi-box"></i>
                                        ไม่พบรายการสินค้า
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Order Summary -->
                            <div class="order-summary">
                                <div class="summary-row">
                                    <span class="summary-label">ยอดรวมสินค้า</span>
                                    <span class="summary-value">฿<?= number_format($orderTotal, 2) ?></span>
                                </div>
                                <div class="summary-row">
                                    <span class="summary-label">ค่าจัดส่ง</span>
                                    <span class="summary-value text-success">
                                        <i class="bi bi-check-circle"></i> ฟรี
                                    </span>
                                </div>
                                <div class="summary-row">
                                    <span class="summary-label">รวมทั้งสิ้น</span>
                                    <span class="summary-value summary-total">฿<?= number_format($orderTotal, 2) ?></span>
                                </div>
                            </div>
                            
                            <!-- Admin Note -->
                            <?php if (!empty($o['admin_note'])): ?>
                                <div class="admin-note">
                                    <div class="note-label">
                                        <i class="bi bi-chat-text"></i>
                                        หมายเหตุจากผู้ดูแล
                                    </div>
                                    <div class="note-content">
                                        <?= htmlspecialchars($o['admin_note']) ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Card Footer -->
                        <div class="card-footer">
                            <a href="order_detail.php?id=<?= $o['order_id'] ?>" class="detail-btn">
                                <i class="bi bi-eye"></i>
                                ดูรายละเอียด
                            </a>
                            <a href="order_detail.php?id=<?= $o['order_id'] ?>" class="view-products-btn">
                                <i class="bi bi-list-check"></i>
                                ดูสินค้าทั้งหมด
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
        <?php elseif ($search_performed && empty($orders)): ?>
            <!-- Empty State for Search -->
            <div class="empty-state">
                <div class="empty-icon">
                    <i class="bi bi-search"></i>
                </div>
                <h2 class="empty-title">ไม่พบคำสั่งซื้อ</h2>
                <p class="empty-description">
                    ไม่พบคำสั่งซื้อจากเบอร์โทรศัพท์นี้ 
                    กรุณาตรวจสอบเบอร์โทรศัพท์และลองใหม่อีกครั้ง
                </p>
                <a href="javascript:history.back()" class="empty-action">
                    <i class="bi bi-arrow-left"></i>
                    กลับไปค้นหาใหม่
                </a>
            </div>
            
        <?php elseif (!$member_id && !isset($_POST['phone'])): ?>
            <!-- Empty State for Initial View -->
            <div class="empty-state">
                <div class="empty-icon">
                    <i class="bi bi-clipboard-check"></i>
                </div>
                <h2 class="empty-title">ยังไม่มีการค้นหา</h2>
                <p class="empty-description">
                    กรอกเบอร์โทรศัพท์ด้านบนเพื่อตรวจสอบสถานะคำสั่งซื้อของคุณ
                </p>
            </div>
        <?php endif; ?>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Phone input formatting
        const phoneInput = document.getElementById('phone');
        if (phoneInput) {
            phoneInput.addEventListener('input', function(e) {
                let value = e.target.value.replace(/\D/g, '');
                // อนุญาตให้กรอกแค่ตัวเลขเท่านั้น
                e.target.value = value;
            });
        }
        
        // Add loading state to search button
        const searchForm = document.querySelector('form');
        if (searchForm) {
            searchForm.addEventListener('submit', function() {
                const submitBtn = this.querySelector('button[type="submit"]');
                if (submitBtn) {
                    submitBtn.innerHTML = '<span class="loading-spinner"></span> กำลังค้นหา...';
                    submitBtn.disabled = true;
                }
            });
        }
        
        // Add animation to order cards
        document.addEventListener('DOMContentLoaded', function() {
            const cards = document.querySelectorAll('.order-card');
            cards.forEach((card, index) => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';
                
                setTimeout(() => {
                    card.style.transition = 'all 0.4s ease';
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, index * 100);
            });
        });
        
        // Close alert function
        function closeAlert(alertId) {
            const alert = document.getElementById(alertId);
            if (alert) {
                alert.style.transition = 'all 0.3s ease';
                alert.style.transform = 'translateX(100%)';
                alert.style.opacity = '0';
                
                setTimeout(() => {
                    alert.remove();
                }, 300);
            }
        }
        
        // Auto close alerts after 5 seconds
        setTimeout(() => {
            const alerts = document.querySelectorAll('.alert-custom');
            alerts.forEach(alert => {
                alert.style.transition = 'all 0.3s ease';
                alert.style.transform = 'translateX(100%)';
                alert.style.opacity = '0';
                
                setTimeout(() => {
                    alert.remove();
                }, 300);
            });
        }, 5000);
    </script>
</body>
</html>