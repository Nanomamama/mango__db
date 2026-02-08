<?php 
session_start();
require_once __DIR__ . '/../db/db.php';

$member_id = $_SESSION['member_id'] ?? null;
$orders = [];
$search_performed = false;

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

if (isset($_POST['phone'])) {
    $search_performed = true;
    $phone = $_POST['phone'];
    
    $stmt = $conn->prepare("
        SELECT * FROM orders 
        WHERE customer_phone = ?
        ORDER BY order_date DESC
    ");
    $stmt->bind_param("s", $phone);
    $stmt->execute();
    $orders = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
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
        :root {
            --white-primary: #ffffff;
            --white-secondary: #f8f9fa;
            --white-tertiary: #f1f3f5;
            --gray-light: #e9ecef;
            --gray-medium: #adb5bd;
            --gray-dark: #495057;
            --text-primary: #212529;
            --text-secondary: #6c757d;
            --accent-blue: #0d6efd;
            --accent-light-blue: #e7f1ff;
            --success-light: #d1e7dd;
            --warning-light: #fff3cd;
            --danger-light: #f8d7da;
            --info-light: #cff4fc;
            --border-radius-sm: 8px;
            --border-radius-md: 12px;
            --border-radius-lg: 16px;
            --shadow-sm: 0 2px 8px rgba(0,0,0,0.04);
            --shadow-md: 0 4px 12px rgba(0,0,0,0.06);
            --shadow-lg: 0 8px 24px rgba(0,0,0,0.08);
        }
        
        body {
            background: linear-gradient(135deg, #f8f9fa, #f1f3f5);
            min-height: 100vh;
            color: var(--text-primary);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .container-main {
            max-width: 750px;
            margin: 0 auto;
        }
        
        .header-card {
            background: var(--white-primary);
            border-radius: var(--border-radius-lg);
            box-shadow: var(--shadow-lg);
            padding: 2rem;
            margin-bottom: 1.5rem;
            border: 1px solid var(--gray-light);
        }
        
        .order-card {
            background: var(--white-primary);
            border-radius: var(--border-radius-md);
            box-shadow: var(--shadow-md);
            margin-bottom: 1.25rem;
            padding: 1.5rem;
            border: 1px solid var(--gray-light);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .order-card:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-lg);
        }
        
        .order-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 5px;
            height: 100%;
        }
        
        .order-card.pending::before { background: #ffc107; }
        .order-card.approved::before { background: #198754; }
        .order-card.rejected::before { background: #dc3545; }
        .order-card.completed::before { background: #0d6efd; }
        
        .order-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
            padding-bottom: 0.75rem;
            border-bottom: 1px solid var(--gray-light);
        }
        
        .order-code {
            font-weight: 700;
            font-size: 1.1rem;
            color: var(--accent-blue);
            letter-spacing: 0.5px;
        }
        
        .status-badge {
            font-size: 0.85rem;
            font-weight: 600;
            padding: 0.4rem 1rem;
            border-radius: 50px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .status-pending { 
            background: var(--warning-light); 
            color: #856404; 
        }
        
        .status-approved { 
            background: var(--success-light); 
            color: #155724; 
        }
        
        .status-rejected { 
            background: var(--danger-light); 
            color: #721c24; 
        }
        
        .status-completed { 
            background: var(--accent-light-blue); 
            color: #004085; 
        }
        
        .customer-info {
            background: var(--white-secondary);
            border-radius: var(--border-radius-sm);
            padding: 1rem;
            margin-bottom: 1rem;
        }
        
        .info-item {
            display: flex;
            align-items: center;
            margin-bottom: 0.5rem;
        }
        
        .info-item:last-child {
            margin-bottom: 0;
        }
        
        .info-icon {
            width: 24px;
            text-align: center;
            margin-right: 0.75rem;
            color: var(--text-secondary);
        }
        
        .admin-note {
            background: var(--white-tertiary);
            border-radius: var(--border-radius-sm);
            padding: 1rem;
            border-left: 3px solid var(--accent-blue);
        }
        
        .back-btn {
            background: var(--white-primary);
            border: 1px solid var(--gray-light);
            border-radius: 50px;
            padding: 0.75rem 1.5rem;
            box-shadow: var(--shadow-sm);
            transition: all 0.2s ease;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .back-btn:hover {
            background: var(--white-secondary);
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }
        
        .search-form {
            background: var(--white-primary);
            border-radius: var(--border-radius-md);
            padding: 1.5rem;
            box-shadow: var(--shadow-md);
            border: 1px solid var(--gray-light);
        }
        
        .form-control {
            border: 1px solid var(--gray-light);
            border-radius: var(--border-radius-sm);
            padding: 0.75rem 1rem;
            font-size: 1rem;
            transition: all 0.2s ease;
        }
        
        .form-control:focus {
            border-color: var(--accent-blue);
            box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.1);
        }
        
        .btn-primary {
            background: var(--accent-blue);
            border: none;
            border-radius: var(--border-radius-sm);
            padding: 0.75rem;
            font-weight: 600;
            transition: all 0.2s ease;
        }
        
        .btn-primary:hover {
            background: #0b5ed7;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(13, 110, 253, 0.2);
        }
        
        .empty-state {
            text-align: center;
            padding: 3rem 1rem;
            color: var(--text-secondary);
        }
        
        .empty-icon {
            font-size: 4rem;
            margin-bottom: 1.5rem;
            color: var(--gray-medium);
        }
        
        .order-date {
            color: var(--text-secondary);
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        @media (max-width: 768px) {
            .header-card, .order-card, .search-form {
                padding: 1.25rem;
            }
            
            .order-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.75rem;
            }
            
            .order-code {
                font-size: 1rem;
            }
            
            .status-badge {
                align-self: flex-start;
            }
        }
    </style>
</head>
<body>
    <div class="container container-main py-4">
        <!-- ปุ่มกลับหน้าหลัก -->
        <div class="mb-4">
            <a href="./products.php" class="back-btn text-decoration-none">
                <i class="bi bi-arrow-left"></i>
                กลับหน้าหลัก
            </a>
        </div>
        
        <div class="header-card">
            <div class="text-center mb-4">
                <h1 class="h3 fw-bold text-dark mb-2">
                    <i class="bi bi-search me-2"></i>ติดตามสถานะคำสั่งซื้อ
                </h1>
                <p class="text-muted mb-0">ตรวจสอบสถานะและรายละเอียดคำสั่งซื้อของคุณ</p>
            </div>
            
            <?php if (!$member_id): ?>
            <div class="search-form">
                <form method="post" class="mb-0">
                    <div class="mb-3">
                        <label for="phone" class="form-label fw-medium">กรอกเบอร์โทรศัพท์ที่ใช้สั่งซื้อ</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white">
                                <i class="bi bi-telephone text-primary"></i>
                            </span>
                            <input type="text" name="phone" id="phone" 
                                   class="form-control form-control-lg" 
                                   placeholder="เช่น 0812345678" 
                                   required
                                   value="<?= isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : '' ?>">
                        </div>
                        <div class="form-text mt-2">สำหรับลูกค้าทั่วไปที่ไม่ได้เข้าสู่ระบบ</div>
                    </div>
                    <button type="submit" class="btn btn-primary w-100 btn-lg">
                        <i class="bi bi-search me-2"></i>ค้นหาสถานะ
                    </button>
                </form>
            </div>
            <?php endif; ?>
        </div>
        
        <?php if (!empty($orders)): ?>
            <div class="mb-3">
                <h2 class="h5 fw-bold text-dark">
                    <i class="bi bi-card-checklist me-2"></i>รายการคำสั่งซื้อ
                    <span class="badge bg-light text-dark ms-2"><?= count($orders) ?> รายการ</span>
                </h2>
            </div>
            
            <?php foreach($orders as $o): ?>
                <?php
                $statusText = match($o['order_status']){
                    'pending' => 'รอยืนยัน',
                    'approved' => 'ยืนยันแล้ว',
                    'rejected' => 'ถูกปฏิเสธ',
                    'completed' => 'เสร็จสมบูรณ์',
                    default => $o['order_status']
                };
                
                $statusClass = match($o['order_status']){
                    'pending' => 'status-pending',
                    'approved' => 'status-approved',
                    'rejected' => 'status-rejected',
                    'completed' => 'status-completed',
                    default => ''
                };
                ?>
                
                <div class="order-card <?= $o['order_status'] ?>">
                    <div class="order-header">
                        <div>
                            <div class="order-code">#<?= htmlspecialchars($o['order_code']) ?></div>
                            <div class="order-date mt-1">
                                <i class="bi bi-calendar3"></i>
                                <?= date('d/m/Y H:i', strtotime($o['order_date'])) ?>
                            </div>
                        </div>
                        <span class="status-badge <?= $statusClass ?>">
                            <?= $statusText ?>
                        </span>
                    </div>
                    
                    <div class="customer-info">
                        <div class="info-item">
                            <div class="info-icon">
                                <i class="bi bi-person"></i>
                            </div>
                            <div>
                                <strong>ชื่อลูกค้า:</strong> <?= htmlspecialchars($o['customer_name']) ?>
                            </div>
                        </div>
                        
                        <div class="info-item">
                            <div class="info-icon">
                                <i class="bi bi-telephone"></i>
                            </div>
                            <div>
                                <strong>เบอร์โทรศัพท์:</strong> <?= htmlspecialchars($o['customer_phone']) ?>
                            </div>
                        </div>
                        
                        <div class="info-item">
                            <div class="info-icon">
                                <i class="bi bi-truck"></i>
                            </div>
                            <div>
                                <strong>วิธีการรับสินค้า:</strong>
                                <?= $o['receive_type'] == 'pickup' ? 'รับที่สวน' : 'จัดส่งถึงบ้าน' ?>
                            </div>
                        </div>
                    </div>
                    
                    <?php if (!empty($o['admin_note'])): ?>
                    <div class="admin-note">
                        <div class="d-flex align-items-start mb-1">
                            <i class="bi bi-chat-text me-2 mt-1 text-primary"></i>
                            <strong>หมายเหตุจากผู้ดูแล:</strong>
                        </div>
                        <div class="ms-4">
                            <?= htmlspecialchars($o['admin_note']) ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
                
            <?php endforeach; ?>
            
        <?php elseif($search_performed): ?>
            <div class="header-card text-center">
                <div class="empty-state">
                    <div class="empty-icon">
                        <i class="bi bi-search"></i>
                    </div>
                    <h4 class="fw-bold text-dark mb-3">ไม่พบคำสั่งซื้อ</h4>
                    <p class="text-muted mb-4">
                        ไม่พบคำสั่งซื้อจากเบอร์โทรศัพท์นี้<br>
                        กรุณาตรวจสอบเบอร์โทรศัพท์และลองใหม่อีกครั้ง
                    </p>
                    <a href="javascript:history.back()" class="btn btn-outline-primary">
                        <i class="bi bi-arrow-left me-2"></i>กลับไปค้นหาใหม่
                    </a>
                </div>
            </div>
            
        <?php elseif(!$member_id && !isset($_POST['phone'])): ?>
            <div class="header-card text-center">
                <div class="empty-state">
                    <div class="empty-icon">
                        <i class="bi bi-clipboard-check"></i>
                    </div>
                    <h4 class="fw-bold text-dark mb-3">ยังไม่มีการค้นหา</h4>
                    <p class="text-muted">
                        กรอกเบอร์โทรศัพท์ด้านบนเพื่อตรวจสอบสถานะคำสั่งซื้อของคุณ
                    </p>
                </div>
            </div>
        <?php endif; ?>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>