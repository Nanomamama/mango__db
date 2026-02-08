<?php
require_once __DIR__ . '/../db/db.php';

if (!isset($_GET['code'])) {
    header("Location: products.php");
    exit;
}

$code = $_GET['code'];

$stmt = $conn->prepare("
    SELECT * FROM orders 
    WHERE order_code = ?
");
$stmt->bind_param("s", $code);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();

if (!$order) {
    echo "ไม่พบรายการสั่งซื้อ";
    exit;
}

// ดึงรายการสินค้า
$item = $conn->prepare("
    SELECT * FROM order_items 
    WHERE order_id = ?
");
$item->bind_param("i", $order['order_id']);
$item->execute();
$items = $item->get_result();

// Map status to Thai text
$statusMap = [
    'pending' => 'รอยืนยัน',
    'approved' => 'ยืนยันแล้ว',
    'rejected' => 'ถูกปฏิเสธ',
    'completed' => 'เสร็จสมบูรณ์'
];
$statusText = $statusMap[$order['order_status']] ?? $order['order_status'];
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>สั่งซื้อสำเร็จ - สวนลุงเผือก</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        :root {
            --white-primary: #ffffff;
            --white-secondary: #f8f9fa;
            --white-tertiary: #f1f3f5;
            --gray-light: #e9ecef;
            --gray-medium: #adb5bd;
            --gray-dark: #495057;
            --text-primary: #212529;
            --text-secondary: #6c757d;
            --success-light: #d1e7dd;
            --success-dark: #0f5132;
            --warning-light: #fff3cd;
            --warning-dark: #664d03;
            --info-light: #cff4fc;
            --info-dark: #055160;
            --primary-light: #cfe2ff;
            --primary-dark: #052c65;
            --accent-green: #198754;
            --accent-blue: #0d6efd;
            --accent-red: #dc3545;
            --shadow-sm: 0 2px 8px rgba(0,0,0,0.04);
            --shadow-md: 0 4px 12px rgba(0,0,0,0.06);
            --shadow-lg: 0 8px 24px rgba(0,0,0,0.08);
            --radius-sm: 8px;
            --radius-md: 12px;
            --radius-lg: 16px;
        }
        
        body {
            background: linear-gradient(135deg, #f8f9fa, #f1f3f5);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: var(--text-primary);
            padding: 1rem;
        }
        
        .success-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 1rem;
        }
        
        /* Two Column Layout for Desktop */
        .success-layout {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1.5rem;
        }
        
        @media (min-width: 992px) {
            .success-layout {
                grid-template-columns: 1fr 1fr;
                gap: 2rem;
            }
            
            .order-header-section {
                grid-column: 1 / -1;
            }
            
            .order-summary-section {
                grid-row: span 2;
            }
        }
        
        /* Card Styles */
        .success-card {
            background: var(--white-primary);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-md);
            overflow: hidden;
            border: 1px solid var(--gray-light);
            height: fit-content;
        }
        
        .card-header {
            background: linear-gradient(135deg, var(--accent-green), #2ecc71);
            color: white;
            padding: 2rem;
            text-align: center;
            position: relative;
        }
        
        /* Order Header Section */
        .order-header-section {
            margin-bottom: 1.5rem;
        }
        
        .success-icon {
            font-size: 3.5rem;
            margin-bottom: 1rem;
            animation: bounceIn 1s ease;
        }
        
        @keyframes bounceIn {
            0% { transform: scale(0.3); opacity: 0; }
            50% { transform: scale(1.05); }
            70% { transform: scale(0.9); }
            100% { transform: scale(1); opacity: 1; }
        }
        
        .success-title {
            font-size: 1.75rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
        
        .success-subtitle {
            font-size: 1rem;
            opacity: 0.9;
            margin-bottom: 1.5rem;
        }
        
        .order-code-badge {
            background: rgba(255, 255, 255, 0.2);
            border: 2px solid white;
            border-radius: 50px;
            padding: 0.75rem 1.5rem;
            font-size: 1.25rem;
            font-weight: 700;
            letter-spacing: 1px;
            margin: 1rem auto;
            display: inline-block;
            backdrop-filter: blur(10px);
        }
        
        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            border-radius: 50px;
            font-weight: 600;
            font-size: 0.9rem;
            margin-top: 1rem;
        }
        
        .status-pending {
            background: var(--warning-light);
            color: var(--warning-dark);
        }
        
        .status-approved {
            background: var(--success-light);
            color: var(--success-dark);
        }
        
        .status-completed {
            background: var(--info-light);
            color: var(--info-dark);
        }
        
        .status-rejected {
            background: #f8d7da;
            color: #721c24;
        }
        
        /* Card Body */
        .card-body {
            padding: 1.5rem;
        }
        
        .section-header {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 1.25rem;
            padding-bottom: 0.75rem;
            border-bottom: 2px solid var(--gray-light);
        }
        
        .section-icon {
            width: 36px;
            height: 36px;
            background: var(--accent-blue);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        
        .section-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--text-primary);
            margin: 0;
        }
        
        /* Customer Info */
        .info-grid {
            display: grid;
            gap: 0.75rem;
        }
        
        .info-item {
            padding: 0.75rem;
            border-radius: var(--radius-sm);
            background: var(--white-secondary);
            border-left: 3px solid var(--accent-blue);
        }
        
        .info-label {
            font-size: 0.8rem;
            color: var(--text-secondary);
            margin-bottom: 0.25rem;
            font-weight: 500;
        }
        
        .info-value {
            font-weight: 500;
            color: var(--text-primary);
            line-height: 1.4;
        }
        
        /* Order Items */
        .order-items-list {
            max-height: 400px;
            overflow-y: auto;
            margin: 1rem 0;
            padding-right: 0.5rem;
        }
        
        .order-item {
            display: flex;
            align-items: center;
            padding: 1rem;
            border-bottom: 1px solid var(--gray-light);
            transition: background-color 0.2s ease;
        }
        
        .order-item:hover {
            background: var(--white-secondary);
            border-radius: var(--radius-sm);
        }
        
        .item-details {
            flex: 1;
            min-width: 0;
        }
        
        .item-name {
            font-weight: 500;
            color: var(--text-primary);
            margin-bottom: 0.25rem;
            font-size: 0.95rem;
        }
        
        .item-meta {
            display: flex;
            gap: 1rem;
            color: var(--text-secondary);
            font-size: 0.85rem;
        }
        
        .item-total {
            font-weight: 600;
            color: var(--accent-red);
            min-width: 80px;
            text-align: right;
        }
        
        /* Order Summary */
        .order-summary-card {
            background: var(--white-primary);
            border-radius: var(--radius-md);
            padding: 1.5rem;
            border: 1px solid var(--gray-light);
        }
        
        .summary-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .summary-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.75rem 0;
            border-bottom: 1px solid var(--gray-light);
        }
        
        .summary-row:last-child {
            border-bottom: none;
        }
        
        .summary-label {
            color: var(--text-primary);
        }
        
        .summary-value {
            color: var(--text-primary);
            font-weight: 500;
        }
        
        .total-row {
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 2px solid var(--accent-green);
        }
        
        .final-total {
            font-size: 1.5rem;
            color: var(--accent-red);
            font-weight: 700;
        }
        
        .free-shipping {
            color: var(--accent-green);
            font-weight: 600;
        }
        
        /* Action Buttons */
        .action-buttons {
            display: grid;
            grid-template-columns: 1fr;
            gap: 0.75rem;
            margin-top: 2rem;
        }
        
        @media (min-width: 768px) {
            .action-buttons {
                grid-template-columns: repeat(3, 1fr);
            }
        }
        
        .action-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            border-radius: var(--radius-md);
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
            text-align: center;
            border: none;
            cursor: pointer;
            font-size: 0.95rem;
        }
        
        .btn-back {
            background: var(--white-primary);
            border: 2px solid var(--gray-light);
            color: var(--text-primary);
        }
        
        .btn-back:hover {
            background: var(--white-secondary);
            border-color: var(--gray-medium);
            transform: translateY(-2px);
            box-shadow: var(--shadow-sm);
        }
        
        .btn-track {
            background: var(--accent-blue);
            color: white;
            border: 2px solid var(--accent-blue);
        }
        
        .btn-track:hover {
            background: #0b5ed7;
            border-color: #0b5ed7;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(13, 110, 253, 0.3);
        }
        
        .btn-print {
            background: var(--accent-green);
            color: white;
            border: 2px solid var(--accent-green);
        }
        
        .btn-print:hover {
            background: #157347;
            border-color: #157347;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(25, 135, 84, 0.3);
        }
        
        /* Empty State for Items */
        .empty-items {
            text-align: center;
            padding: 2rem;
            color: var(--text-secondary);
        }
        
        .empty-icon {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            color: var(--gray-medium);
        }
        
        /* Responsive Design */
        @media (max-width: 991px) {
            .success-container {
                padding: 0.5rem;
            }
            
            .card-header {
                padding: 1.5rem 1rem;
            }
            
            .success-title {
                font-size: 1.5rem;
            }
            
            .success-icon {
                font-size: 3rem;
            }
            
            .card-body {
                padding: 1.25rem;
            }
            
            .order-code-badge {
                font-size: 1.1rem;
                padding: 0.5rem 1rem;
            }
        }
        
        @media (max-width: 768px) {
            .success-title {
                font-size: 1.35rem;
            }
            
            .success-icon {
                font-size: 2.5rem;
            }
            
            .card-body {
                padding: 1rem;
            }
            
            .section-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.5rem;
            }
            
            .section-icon {
                width: 32px;
                height: 32px;
            }
            
            .action-buttons {
                grid-template-columns: 1fr;
            }
            
            .item-meta {
                flex-wrap: wrap;
                gap: 0.5rem;
            }
        }
        
        @media (max-width: 576px) {
            body {
                padding: 0.5rem;
            }
            
            .success-container {
                padding: 0;
            }
            
            .card-header {
                padding: 1.25rem 0.75rem;
            }
            
            .order-code-badge {
                font-size: 1rem;
                padding: 0.4rem 0.8rem;
            }
            
            .status-badge {
                padding: 0.5rem 1rem;
                font-size: 0.85rem;
            }
            
            .final-total {
                font-size: 1.25rem;
            }
        }
        
        /* Scrollbar Styling */
        .order-items-list::-webkit-scrollbar {
            width: 6px;
        }
        
        .order-items-list::-webkit-scrollbar-track {
            background: var(--white-secondary);
            border-radius: 10px;
        }
        
        .order-items-list::-webkit-scrollbar-thumb {
            background: var(--gray-medium);
            border-radius: 10px;
        }
        
        .order-items-list::-webkit-scrollbar-thumb:hover {
            background: var(--gray-dark);
        }
        
        /* Print Styles */
        @media print {
            body {
                background: white;
                padding: 0;
            }
            
            .success-card {
                box-shadow: none;
                border: 1px solid #ddd;
            }
            
            .action-buttons {
                display: none;
            }
            
            .order-items-list {
                max-height: none;
                overflow: visible;
            }
        }
    </style>
</head>
<body>
    <div class="success-container">
        <!-- Order Header -->
        <div class="success-card order-header-section">
            <div class="card-header">
                <div class="success-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <h1 class="success-title">สั่งซื้อสำเร็จ!</h1>
                <p class="success-subtitle">ขอบคุณสำหรับการสั่งซื้อสินค้ากับเรา</p>
                
                <div class="order-code-badge">
                    <?= htmlspecialchars($order['order_code']) ?>
                </div>
                
                <div class="status-badge status-<?= $order['order_status'] ?>">
                    <i class="fas fa-info-circle"></i>
                    สถานะ: <?= $statusText ?>
                </div>
            </div>
        </div>
        
        <!-- Two Column Layout -->
        <div class="success-layout">
            <!-- Left Column: Customer Info & Order Items -->
            <div class="left-column">
                <!-- Customer Information -->
                <div class="success-card mb-3">
                    <div class="card-body">
                        <div class="section-header">
                            <div class="section-icon">
                                <i class="fas fa-user"></i>
                            </div>
                            <h2 class="section-title">ข้อมูลผู้สั่งซื้อ</h2>
                        </div>
                        
                        <div class="info-grid">
                            <div class="info-item">
                                <div class="info-label">ชื่อ-นามสกุล</div>
                                <div class="info-value"><?= htmlspecialchars($order['customer_name']) ?></div>
                            </div>
                            
                            <div class="info-item">
                                <div class="info-label">เบอร์โทรศัพท์</div>
                                <div class="info-value"><?= htmlspecialchars($order['customer_phone']) ?></div>
                            </div>
                            
                            <div class="info-item">
                                <div class="info-label">ที่อยู่จัดส่ง</div>
                                <div class="info-value"><?= nl2br(htmlspecialchars($order['customer_address'])) ?></div>
                            </div>
                            
                            <div class="info-item">
                                <div class="info-label">วิธีการรับสินค้า</div>
                                <div class="info-value">
                                    <?= $order['receive_type'] == 'pickup' ? 'รับที่สวน' : 'จัดส่งถึงบ้าน' ?>
                                </div>
                            </div>
                            
                            <?php if ($order['receive_datetime']): ?>
                            <div class="info-item">
                                <div class="info-label">วันเวลาที่นัดรับ</div>
                                <div class="info-value">
                                    <?= date('d/m/Y H:i', strtotime($order['receive_datetime'])) ?>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Order Items -->
                <div class="success-card">
                    <div class="card-body">
                        <div class="section-header">
                            <div class="section-icon">
                                <i class="fas fa-shopping-basket"></i>
                            </div>
                            <h2 class="section-title">รายการสินค้า</h2>
                        </div>
                        
                        <div class="order-items-list">
                            <?php 
                            $total = 0;
                            $itemCount = $items->num_rows;
                            
                            if ($itemCount > 0):
                                while($i = $items->fetch_assoc()):
                                    $sum = $i['price'] * $i['quantity'];
                                    $total += $sum;
                            ?>
                            <div class="order-item">
                                <div class="item-details">
                                    <div class="item-name"><?= htmlspecialchars($i['product_name']) ?></div>
                                    <div class="item-meta">
                                        <span>ราคา: ฿<?= number_format($i['price'], 2) ?></span>
                                        <span>จำนวน: <?= $i['quantity'] ?></span>
                                        <span>หน่วย: <?= htmlspecialchars($i['unit'] ?? 'ชิ้น') ?></span>
                                    </div>
                                </div>
                                <div class="item-total">฿<?= number_format($sum, 2) ?></div>
                            </div>
                            <?php 
                                endwhile;
                            else:
                            ?>
                            <div class="empty-items">
                                <div class="empty-icon">
                                    <i class="fas fa-box-open"></i>
                                </div>
                                <p>ไม่พบรายการสินค้า</p>
                            </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="item-count mt-2 text-end">
                            <small class="text-muted">จำนวนรายการ: <?= $itemCount ?> รายการ</small>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Right Column: Order Summary & Actions -->
            <div class="right-column">
                <!-- Order Summary -->
                <div class="success-card order-summary-section">
                    <div class="card-body">
                        <h3 class="summary-title">
                            <i class="fas fa-file-invoice"></i>
                            สรุปยอดสั่งซื้อ
                        </h3>
                        
                        <div class="summary-row">
                            <span class="summary-label">ยอดรวมสินค้า</span>
                            <span class="summary-value">฿<?= number_format($total, 2) ?></span>
                        </div>
                        
                        <div class="summary-row">
                            <span class="summary-label">ค่าจัดส่ง</span>
                            <span class="summary-value free-shipping">ฟรี</span>
                        </div>
                        
                        <div class="summary-row total-row">
                            <span class="summary-label"><strong>รวมทั้งสิ้น</strong></span>
                            <span class="summary-value final-total">฿<?= number_format($total, 2) ?></span>
                        </div>
                        
                        <div class="payment-info mt-3 p-3 bg-light rounded">
                            <small class="text-muted d-block mb-1">
                                <i class="fas fa-info-circle"></i> วิธีการชำระเงิน
                            </small>
                            <small class="text-muted">
                                โอนเงินผ่านธนาคาร หรือ ชำระเงินสดเมื่อได้รับสินค้า
                            </small>
                        </div>
                    </div>
                </div>
                
                <!-- Action Buttons -->
                <div class="success-card mt-3">
                    <div class="card-body">
                        <div class="section-header">
                            <div class="section-icon">
                                <i class="fas fa-cog"></i>
                            </div>
                            <h2 class="section-title">ดำเนินการต่อ</h2>
                        </div>
                        
                        <div class="action-buttons">
                            <a href="products.php" class="action-btn btn-back">
                                <i class="fas fa-arrow-left"></i>
                                ซื้อสินค้าเพิ่ม
                            </a>
                            
                            <a href="order_status.php" class="action-btn btn-track">
                                <i class="fas fa-search"></i>
                                ติดตามสถานะ
                            </a>
                        
                        </div>
                        
                        <div class="order-tips mt-3 p-3 bg-light rounded">
                            <small class="text-muted d-block mb-1">
                                <i class="fas fa-lightbulb"></i> หมายเหตุสำคัญ
                            </small>
                            <small class="text-muted">
                                กรุณาบันทึกหมายเลขคำสั่งซื้อไว้สำหรับติดตามสถานะ<br>
                                ทางเราจะติดต่อกลับเพื่อยืนยันการสั่งซื้อภายใน 24 ชั่วโมง
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // Clear cart from localStorage
        localStorage.removeItem("cart");
        
        // Update cart badge if function exists
        if (typeof updateCartCount === "function") {
            updateCartCount();
        }
        
        // Print order function
        function printOrder() {
            window.print();
        }
        
        // Add smooth scroll to order items if needed
        document.addEventListener('DOMContentLoaded', function() {
            const orderItems = document.querySelector('.order-items-list');
            if (orderItems && orderItems.scrollHeight > 400) {
                orderItems.style.maxHeight = '400px';
            }
        });
        
        // Add animation to summary card
        document.addEventListener('DOMContentLoaded', function() {
            const summaryCard = document.querySelector('.order-summary-section');
            if (summaryCard) {
                summaryCard.style.opacity = '0';
                summaryCard.style.transform = 'translateY(20px)';
                
                setTimeout(() => {
                    summaryCard.style.transition = 'all 0.5s ease';
                    summaryCard.style.opacity = '1';
                    summaryCard.style.transform = 'translateY(0)';
                }, 300);
            }
        });
    </script>
</body>
</html>