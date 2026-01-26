<?php
require_once 'auth.php';
require_once 'db.php';

// CSRF token generation
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

// ดึงชื่อ admin จาก session
$admin_name = $_SESSION['admin_name'] ?? 'Admin';
$admin_email = $_SESSION['admin_email'] ?? '';

// จัดการการอัปเดตสถานะการจอง
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['id'])) {
    // ตรวจ CSRF
    $postedToken = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $postedToken)) {
        http_response_code(403);
        echo 'Invalid CSRF token';
        exit;
    }

    $id = (int) $_POST['id'];
    $action = $_POST['action'];
    
    if ($action === 'confirm') {
        $stmt = $conn->prepare("UPDATE bookings SET status='confirmed', updated_at=NOW() WHERE bookings_id=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
    } elseif ($action === 'cancel') {
        $stmt = $conn->prepare("UPDATE bookings SET status='cancelled', updated_at=NOW() WHERE bookings_id=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
    } elseif ($action === 'delete') {
        $stmt = $conn->prepare("DELETE FROM bookings WHERE bookings_id=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
    }
    header("Location: booking_list.php");
    exit;
}

// ดึงข้อมูลการจองทั้งหมด
$result = $conn->query("SELECT * FROM bookings ORDER BY bookings_id DESC");
$bookings = [];
while ($row = $result->fetch_assoc()) {
    $bookings[] = $row;
}

// นับจำนวนสถานะต่างๆ สำหรับ Stats Card
$stats = [
    'pending' => 0,
    'confirmed' => 0,
    'cancelled' => 0,
    'total' => count($bookings)
];
foreach ($bookings as $b) {
    if (isset($stats[$b['status']])) {
        $stats[$b['status']]++;
    }
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>จัดการการจอง - ระบบ Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        :root {
            --primary: #4361ee;
            --secondary: #3f37c9;
            --success: #2ecc71;
            --info: #36b9cc;
            --warning: #f6c23e;
            --danger: #e74a3b;
            --light: #f8f9fa;
            --dark: #212529;
        }

        body {
            font-family: 'Kanit', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #e4e7f1 100%);
            color: #333;
            min-height: 100vh;
        }

        .dashboard-header {
            background: linear-gradient(120deg, var(--primary), var(--secondary));
            color: white;
            padding: 1rem;
            box-shadow: 0 4px 12px rgba(67, 97, 238, 0.3);
            border-radius: 50px;
            margin-bottom: 2rem;
        }

        .admin-profile {
            display: flex;
            align-items: center;
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px);
            padding: 0.5rem 1rem;
            border-radius: 50px;
        }

        .admin-profile img {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            margin-right: 10px;
            border: 2px solid rgba(255, 255, 255, 0.5);
        }

        .booking-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
            overflow: hidden;
            margin-bottom: 1.5rem;
            border: none;
            position: relative;
        }

        .booking-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 25px rgba(0, 0, 0, 0.1);
        }

        .booking-card::after {
            content: "";
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: linear-gradient(90deg, var(--primary), var(--secondary));
        }

        .booking-card-header {
            padding: 1rem 1.5rem;
            background: linear-gradient(90deg, rgba(67, 97, 238, 0.05), transparent);
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .status-badge {
            padding: 0.35rem 0.8rem;
            border-radius: 50px;
            font-weight: 500;
            font-size: 0.85rem;
        }

        .status-pending { background: rgba(246, 194, 62, 0.15); color: #d39e00; }
        .status-confirmed { background: rgba(46, 204, 113, 0.15); color: #27ae60; }
        .status-cancelled { background: rgba(231, 76, 60, 0.15); color: #c0392b; }

        .info-label { font-weight: 500; color: #6c757d; min-width: 120px; display: inline-block; }
        .info-value { color: #2d3436; font-weight: 400; }

        .action-btn {
            border-radius: 50px;
            padding: 0.5rem 1.2rem;
            font-weight: 500;
            transition: all 0.3s ease;
            border: none;
            font-size: 0.9rem;
            display: inline-flex;
            align-items: center;
        }

        .btn-confirm { background: rgba(46, 204, 113, 0.1); color: #27ae60; }
        .btn-confirm:hover { background: #27ae60; color: white; }
        
        .btn-cancel { background: rgba(231, 76, 60, 0.1); color: #c0392b; }
        .btn-cancel:hover { background: #c0392b; color: white; }

        .stats-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.05);
            padding: 1.5rem;
            text-align: center;
            margin-bottom: 1.5rem;
            border: none;
        }
        
        .stats-icon { font-size: 2rem; margin-bottom: 0.5rem; }
        .stats-value { font-size: 1.8rem; font-weight: 700; }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>

<div class="p-4" style="margin-left: 250px; flex: 1;">
        <!-- Header -->
        <header class="dashboard-header pb-4 mb-4">
                <div class="container">
                    <div class="d-flex justify-content-between align-items-center flex-wrap">
                        <div>
                            <h2 class="dashboard-title mb-0">จัดการผู้ใช้งานที่เป็นสมาชิก</h2>
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

    <!-- Stats -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="stats-card">
                <div class="stats-icon text-primary"><i class="bi bi-list-ul"></i></div>
                <div class="stats-value"><?= $stats['total'] ?></div>
                <div class="text-muted">ทั้งหมด</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-card">
                <div class="stats-icon text-warning"><i class="bi bi-clock-history"></i></div>
                <div class="stats-value"><?= $stats['pending'] ?></div>
                <div class="text-muted">รอยืนยัน</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-card">
                <div class="stats-icon text-success"><i class="bi bi-check-circle"></i></div>
                <div class="stats-value"><?= $stats['confirmed'] ?></div>
                <div class="text-muted">ยืนยันแล้ว</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-card">
                <div class="stats-icon text-danger"><i class="bi bi-x-circle"></i></div>
                <div class="stats-value"><?= $stats['cancelled'] ?></div>
                <div class="text-muted">ยกเลิกแล้ว</div>
            </div>
        </div>
    </div>

    <!-- Booking List -->
    <div class="row">
        <?php if (empty($bookings)): ?>
            <div class="col-12 text-center py-5">
                <i class="bi bi-inbox fs-1 text-muted"></i>
                <p class="mt-3 text-muted">ไม่พบข้อมูลการจอง</p>
            </div>
        <?php else: ?>
            <?php foreach ($bookings as $booking): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="booking-card">
                        <div class="booking-card-header">
                            <span class="fw-bold text-primary">#<?= htmlspecialchars($booking['booking_code']) ?></span>
                            <span class="status-badge status-<?= $booking['status'] ?>">
                                <?php
                                    if($booking['status'] == 'pending') echo '<i class="bi bi-hourglass-split me-1"></i> รอยืนยัน';
                                    elseif($booking['status'] == 'confirmed') echo '<i class="bi bi-check-circle-fill me-1"></i> ยืนยันแล้ว';
                                    else echo '<i class="bi bi-x-circle-fill me-1"></i> ยกเลิกแล้ว';
                                ?>
                            </span>
                        </div>
                        <div class="p-4">
                            <div class="mb-2">
                                <span class="info-label"><i class="bi bi-person me-2"></i>ลูกค้า:</span>
                                <span class="info-value"><?= htmlspecialchars($booking['guest_name']) ?></span>
                            </div>
                            <div class="mb-2">
                                <span class="info-label"><i class="bi bi-calendar-event me-2"></i>วันที่:</span>
                                <span class="info-value"><?= date('d/m/Y', strtotime($booking['booking_date'])) ?></span>
                            </div>
                            <div class="mb-2">
                                <span class="info-label"><i class="bi bi-clock me-2"></i>เวลา:</span>
                                <span class="info-value"><?= date('H:i', strtotime($booking['booking_time'])) ?> น.</span>
                            </div>
                            <div class="mb-2">
                                <span class="info-label"><i class="bi bi-people me-2"></i>จำนวน:</span>
                                <span class="info-value"><?= $booking['visitor_count'] ?> ท่าน</span>
                            </div>
                            <div class="mb-3">
                                <span class="info-label"><i class="bi bi-currency-dollar me-2"></i>ยอดรวม:</span>
                                <span class="info-value fw-bold text-dark">฿<?= number_format($booking['price_total'], 2) ?></span>
                            </div>

                            <hr class="my-3 opacity-50">

                            <div class="d-flex gap-2 justify-content-end">
                                <?php if ($booking['status'] == 'pending'): ?>
                                    <form method="POST" onsubmit="return confirm('ยืนยันการจองนี้ใช่หรือไม่?')">
                                        <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                                        <input type="hidden" name="id" value="<?= $booking['bookings_id'] ?>">
                                        <input type="hidden" name="action" value="confirm">
                                        <button type="submit" class="action-btn btn-confirm">
                                            <i class="bi bi-check2-circle me-1"></i> ยืนยัน
                                        </button>
                                    </form>
                                    <form method="POST" onsubmit="return confirm('ต้องการยกเลิกการจองนี้ใช่หรือไม่?')">
                                        <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                                        <input type="hidden" name="id" value="<?= $booking['bookings_id'] ?>">
                                        <input type="hidden" name="action" value="cancel">
                                        <button type="submit" class="action-btn btn-cancel">
                                            <i class="bi bi-x-lg me-1"></i> ยกเลิก
                                        </button>
                                    </form>
                                <?php endif; ?>
                                <button class="btn btn-light rounded-pill px-3" onclick="viewDetails(<?= htmlspecialchars(json_encode($booking)) ?>)">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Modal สำหรับดูรายละเอียด (Optional) -->
<div class="modal fade" id="detailModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0 bg-light">
                <h5 class="modal-title fw-bold">รายละเอียดการจอง</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div id="modalBody" class="modal-body p-4">
                <!-- ข้อมูลจะถูกใส่ด้วย JS -->
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function viewDetails(data) {
        const modalBody = document.getElementById('modalBody');
        modalBody.innerHTML = `
            <div class="text-center mb-4">
                <div class="display-6 fw-bold text-primary">#${data.booking_code}</div>
                <div class="text-muted">สถานะ: ${data.status}</div>
            </div>
            <div class="row g-3">
                <div class="col-6"><small class="text-muted d-block">ชื่อลูกค้า</small> <strong>${data.guest_name}</strong></div>
                <div class="col-6"><small class="text-muted d-block">เบอร์โทรศัพท์</small> <strong>${data.guest_phone}</strong></div>
                <div class="col-6"><small class="text-muted d-block">อีเมล</small> <strong>${data.guest_email || '-'}</strong></div>
                <div class="col-6"><small class="text-muted d-block">ประเภทการจอง</small> <strong>${data.booking_type}</strong></div>
                <div class="col-12"><hr></div>
                <div class="col-6"><small class="text-muted d-block">ยอดรวม</small> <strong class="text-dark">฿${parseFloat(data.price_total).toLocaleString()}</strong></div>
                <div class="col-6"><small class="text-muted d-block">เงินมัดจำ</small> <strong class="text-success">฿${parseFloat(data.deposit_amount).toLocaleString()}</strong></div>
                <div class="col-12"><small class="text-muted d-block">หลักฐานการชำระเงิน</small> 
                    ${data.attachment_path ? `<a href="${data.attachment_path}" target="_blank" class="btn btn-sm btn-outline-primary mt-2 w-100">ดูไฟล์แนบ</a>` : '<span class="text-danger">ไม่มีไฟล์แนบ</span>'}
                </div>
            </div>
        `;
        new bootstrap.Modal(document.getElementById('detailModal')).show();
    }
</script>
</body>
</html>