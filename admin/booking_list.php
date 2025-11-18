<?php
require_once 'auth.php';
require_once 'db.php';

// ดึงชื่อ admin จาก session
$admin_name = $_SESSION['admin_name'] ?? '';
$admin_email = $_SESSION['admin_email'] ?? '';

// --- ส่วนอัปเดตสถานะและลบการจอง (AJAX) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'], $_POST['id'])) {
        $id = (int)$_POST['id'];
        if ($_POST['action'] === 'change_status' && isset($_POST['status'])) {
            $status = $_POST['status'];
            $stmt = $conn->prepare("UPDATE bookings SET status=?, approved_by=?, approved_at=NOW() WHERE id=?");
            $stmt->bind_param("ssi", $status, $admin_name, $id);
            $stmt->execute();
            echo json_encode(['success' => true]);
            exit;
        }
        if ($_POST['action'] === 'delete') {
            $stmt = $conn->prepare("DELETE FROM bookings WHERE id=?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            echo json_encode(['success' => true]);
            exit;
        }
    }
}

// อัปเดตสถานะการดูเมื่อโหลดหน้านี้
$conn->query("UPDATE bookings SET viewed = 1 WHERE viewed = 0");

// ดึงข้อมูลจองจากฐานข้อมูล
$bookings = [];
$result = $conn->query("SELECT *, 
                        DATE_FORMAT(approved_at, '%d/%m/%Y %H:%i') as approved_at_formatted 
                        FROM bookings ORDER BY date ASC");
while ($row = $result->fetch_assoc()) {
    $bookings[] = $row;
}
// แยกข้อมูลตามสถานะ
$approved = array_filter($bookings, fn($b) => $b['status'] === 'อนุมัติแล้ว');
$rejected = array_filter($bookings, fn($b) => $b['status'] === 'ถูกปฏิเสธ');
$pending = array_filter($bookings, fn($b) => $b['status'] === 'รออนุมัติ');
?>


<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ระบบจัดการการจอง - สวนมะม่วงลุงเผือก</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        :root {
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

        * {
            font-family: 'Kanit', sans-serif;
        }

        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #e4e7f1 100%);
            min-height: 100vh;
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

        .stats-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.05);
            padding: 1.5rem;
            text-align: center;
            margin-bottom: 1.5rem;
            transition: all 0.3s ease;
            height: 100%;
        }

        .stats-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 25px rgba(0, 0, 0, 0.1);
        }

        .stats-icon {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            color: var(--primary);
        }

        .stats-value {
            font-size: 2rem;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 0.5rem;
        }

        .stats-label {
            font-size: 0.9rem;
            color: #6c757d;
            font-weight: 500;
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
        }

        .booking-card.pending::after {
            background: linear-gradient(90deg, var(--warning), #f8b400);
        }

        .booking-card.approved::after {
            background: linear-gradient(90deg, var(--success), var(--teal));
        }

        .booking-card.rejected::after {
            background: linear-gradient(90deg, var(--danger), var(--pink));
        }

        .booking-card-header {
            padding: 1rem 1.5rem;
            background: linear-gradient(90deg, rgba(67, 97, 238, 0.1), transparent);
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .booking-card-body {
            padding: 1.5rem;
        }

        .booking-info-item {
            display: flex;
            margin-bottom: 0.8rem;
            align-items: flex-start;
        }

        .booking-info-item i {
            font-size: 1.2rem;
            color: var(--primary);
            margin-right: 10px;
            margin-top: 3px;
            width: 24px;
            text-align: center;
        }

        .booking-info-label {
            font-weight: 500;
            color: #6c757d;
            min-width: 120px;
        }

        .booking-info-value {
            font-weight: 400;
            color: #495057;
        }

        .status-badge {
            padding: 0.35rem 0.65rem;
            border-radius: 50px;
            font-weight: 500;
            font-size: 0.8rem;
        }

        .status-pending {
            background: rgba(246, 194, 62, 0.15);
            color: #f6c23e;
        }

        .status-approved {
            background: rgba(76, 201, 240, 0.15);
            color: #4cc9f0;
        }

        .status-rejected {
            background: rgba(231, 74, 59, 0.15);
            color: #e74a3b;
        }

        .action-btn {
            border-radius: 50px;
            padding: 0.5rem 1.2rem;
            font-weight: 500;
            transition: all 0.3s ease;
            border: none;
            font-size: 0.9rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .action-btn i {
            margin-right: 5px;
        }

        .btn-view {
            background: rgba(67, 97, 238, 0.1);
            color: var(--primary);
        }

        .btn-view:hover {
            background: rgba(67, 97, 238, 0.2);
            color: var(--primary);
        }

        .btn-approve {
            background: rgba(76, 201, 240, 0.1);
            color: var(--success);
        }

        .btn-approve:hover {
            background: rgba(76, 201, 240, 0.2);
            color: var(--success);
        }

        .btn-reject {
            background: rgba(231, 74, 59, 0.1);
            color: var(--danger);
        }

        .btn-reject:hover {
            background: rgba(231, 74, 59, 0.2);
            color: var(--danger);
        }

        .btn-delete {
            background: rgba(231, 74, 59, 0.1);
            color: var(--danger);
        }

        .btn-delete:hover {
            background: rgba(231, 74, 59, 0.2);
            color: var(--danger);
        }

        .search-box {
            position: relative;
            margin-bottom: 1.5rem;
        }

        .search-box input {
            border-radius: 50px;
            padding: 0.75rem 1.5rem;
            border: 1px solid rgba(0, 0, 0, 0.1);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
        }

        .search-box input:focus {
            box-shadow: 0 4px 15px rgba(67, 97, 238, 0.2);
            border-color: var(--primary);
        }

        .search-box i {
            position: absolute;
            right: 20px;
            top: 50%;
            transform: translateY(-50%);
            color: #6c757d;
        }

        .filter-section {
            background: white;
            border-radius: 16px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.05);
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .booking-modal .modal-content {
            border-radius: 16px;
            overflow: hidden;
            border: none;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
        }

        .booking-modal .modal-header {
            background: linear-gradient(120deg, var(--primary), var(--secondary));
            color: white;
            border-bottom: none;
        }

        .booking-modal .btn-close {
            filter: invert(1);
        }

        .nav-tabs .nav-link {
            border: none;
            border-radius: 8px 8px 0 0;
            padding: 0.75rem 1.5rem;
            font-weight: 500;
            color: #6c757d;
            transition: all 0.3s ease;
        }

        .nav-tabs .nav-link.active {
            background: linear-gradient(120deg, var(--primary), var(--secondary));
            color: white;
            box-shadow: 0 4px 10px rgba(67, 97, 238, 0.2);
        }

        .nav-tabs .nav-link:hover:not(.active) {
            background: rgba(67, 97, 238, 0.1);
            color: var(--primary);
        }

        /* CSS สำหรับแสดงข้อมูล Admin ที่อนุมัติ */
        .approval-info {
            background: rgba(67, 97, 238, 0.05);
            border-radius: 8px;
            padding: 0.75rem;
            margin-top: 1rem;
            border-left: 4px solid var(--primary);
        }

        .approval-info-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.3rem;
            font-size: 0.85rem;
        }

        .approval-info-label {
            color: #6c757d;
            font-weight: 500;
        }

        .approval-info-value {
            color: #495057;
            font-weight: 400;
        }

        @media (max-width: 768px) {
            .booking-card-body {
                padding: 1rem;
            }

            .action-btn {
                width: 100%;
                margin-bottom: 0.5rem;
            }

            .btn-group-vertical {
                width: 100%;
            }

            .dashboard-title {
                font-size: 1.5rem;
            }
        }
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
                        <h2 class="dashboard-title mb-0">จัดการรายการจอง</h2>
                    </div>
                    <div class="d-flex align-items-center gap-3 mt-2 mt-md-0">
                        <div class="position-relative">
                            <button class="btn btn-light rounded-circle p-2 shadow-sm position-relative" style="width:44px; height:44px;">
                                <i class="bi bi-bell fs-5"></i>
                                <span class="notification-badge position-absolute top-0 end-0 translate-middle badge rounded-pill bg-danger" style="font-size:0.75rem; min-width:20px; height:20px; display:flex; align-items:center; justify-content:center;">
                                    3
                                </span>
                            </button>
                        </div>
                        <div class="admin-profile">
                            <img src="https://ui-avatars.com/api/?name=<?= urlencode($admin_name) ?>&background=random&color=fff" alt="Admin">
                            <span><?= htmlspecialchars($admin_name) ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <!-- Stats Overview -->
        <div class="row mb-4">
            <div class="col-md-3 col-sm-6">
                <div class="stats-card">
                    <div class="stats-icon">
                        <i class="bi bi-calendar-check"></i>
                    </div>
                    <div class="stats-value"><?= count($bookings) ?></div>
                    <div class="stats-label">การจองทั้งหมด</div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="stats-card">
                    <div class="stats-icon" style="color: #f6c23e;">
                        <i class="bi bi-clock-history"></i>
                    </div>
                    <div class="stats-value"><?= count($pending) ?></div>
                    <div class="stats-label">รออนุมัติ</div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="stats-card">
                    <div class="stats-icon" style="color: #4cc9f0;">
                        <i class="bi bi-check-circle"></i>
                    </div>
                    <div class="stats-value"><?= count($approved) ?></div>
                    <div class="stats-label">อนุมัติแล้ว</div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="stats-card">
                    <div class="stats-icon" style="color: #e74a3b;">
                        <i class="bi bi-x-circle"></i>
                    </div>
                    <div class="stats-value"><?= count($rejected) ?></div>
                    <div class="stats-label">ถูกปฏิเสธ</div>
                </div>
            </div>
        </div>

        <!-- Search and Filter -->
        <div class="filter-section">
            <div class="row">
                <div class="col-md-6">
                    <div class="search-box">
                        <input type="text" class="form-control" placeholder="ค้นหาการจอง...">
                        <i class="bi bi-search"></i>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="d-flex flex-wrap gap-2">
                        <select class="form-select" style="border-radius: 50px;" id="statusFilter">
                            <option value="all" selected>สถานะทั้งหมด</option>
                            <option value="pending">รออนุมัติ</option>
                            <option value="approved">อนุมัติแล้ว</option>
                            <option value="rejected">ถูกปฏิเสธ</option>
                        </select>
                        <select class="form-select" style="border-radius: 50px;" id="sortFilter">
                            <option value="date" selected>เรียงตามวันที่</option>
                            <option value="name">เรียงตามชื่อ</option>
                            <option value="people">เรียงตามจำนวนคน</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabs -->
        <ul class="nav nav-tabs mb-3" id="bookingTab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="all-tab" data-bs-toggle="tab" data-bs-target="#all" type="button" role="tab">
                    ทั้งหมด <span class="badge bg-secondary"><?= count($bookings) ?></span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="pending-tab" data-bs-toggle="tab" data-bs-target="#pending" type="button" role="tab">
                    รออนุมัติ <span class="badge bg-warning"><?= count($pending) ?></span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="approved-tab" data-bs-toggle="tab" data-bs-target="#approved" type="button" role="tab">
                    อนุมัติแล้ว <span class="badge bg-success"><?= count($approved) ?></span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="rejected-tab" data-bs-toggle="tab" data-bs-target="#rejected" type="button" role="tab">
                    ถูกปฏิเสธ <span class="badge bg-danger"><?= count($rejected) ?></span>
                </button>
            </li>
        </ul>

        <!-- Tab Content -->
        <div class="tab-content" id="bookingTabContent">
            <!-- All Tab -->
            <div class="tab-pane fade show active" id="all" role="tabpanel">
                <div class="row">
                    <?php foreach ($bookings as $booking): ?>
                        <div class="col-lg-6 col-xl-4">
                            <div class="booking-card <?= str_replace('แล้ว', '', strtolower($booking['status'])) ?>">
                                <div class="booking-card-header">
                                    <div>
                                        <h5 class="mb-0"><?= htmlspecialchars($booking['name']) ?></h5>
                                        <small>ID: <?= $booking['id'] ?></small>
                                    </div>
                                    <span class="status-badge status-<?= str_replace('แล้ว', '', strtolower($booking['status'])) ?>">
                                        <?= $booking['status'] ?>
                                    </span>
                                </div>
                                <div class="booking-card-body">
                                    <div class="booking-info-item">
                                        <i class="bi bi-calendar-event"></i>
                                        <div>
                                            <div class="booking-info-label">วันที่</div>
                                            <div class="booking-info-value"><?= $booking['date'] ?></div>
                                        </div>
                                    </div>

                                    <div class="booking-info-item">
                                        <i class="bi bi-clock"></i>
                                        <div>
                                            <div class="booking-info-label">เวลา</div>
                                            <div class="booking-info-value"><?= $booking['time'] ?></div>
                                        </div>
                                    </div>

                                    <div class="booking-info-item">
                                        <i class="bi bi-people"></i>
                                        <div>
                                            <div class="booking-info-label">จำนวนคน</div>
                                            <div class="booking-info-value"><?= $booking['people'] ?> คน</div>
                                        </div>
                                    </div>

                                    <div class="booking-info-item">
                                        <i class="bi bi-telephone"></i>
                                        <div>
                                            <div class="booking-info-label">เบอร์โทร</div>
                                            <div class="booking-info-value"><?= $booking['phone'] ?></div>
                                        </div>
                                    </div>

                                    <!-- ส่วนแสดงข้อมูล Admin ที่อนุมัติ -->
                                    <?php if ($booking['status'] !== 'รออนุมัติ' && !empty($booking['approved_by'])): ?>
                                    <div class="approval-info">
                                        <div class="approval-info-item">
                                            <span class="approval-info-label">อนุมัติโดย:</span>
                                            <span class="approval-info-value"><?= htmlspecialchars($booking['approved_by']) ?></span>
                                        </div>
                                        <?php if (!empty($booking['approved_at_formatted'])): ?>
                                        <div class="approval-info-item">
                                            <span class="approval-info-label">เมื่อ:</span>
                                            <span class="approval-info-value"><?= $booking['approved_at_formatted'] ?></span>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                    <?php endif; ?>

                                    <div class="d-flex flex-wrap gap-2 mt-3">
                                        <button class="btn action-btn btn-view view-booking-btn"
                                            data-bs-toggle="modal"
                                            data-bs-target="#bookingModal"
                                            data-booking='<?= htmlspecialchars(json_encode($booking), ENT_QUOTES, 'UTF-8') ?>'>
                                            <i class="bi bi-info-circle"></i> รายละเอียด
                                        </button>

                                        <?php if ($booking['status'] === 'รออนุมัติ'): ?>
                                            <button class="btn action-btn btn-approve" onclick="changeStatus(<?= $booking['id'] ?>, 'อนุมัติแล้ว')">
                                                <i class="bi bi-check-circle"></i> อนุมัติ
                                            </button>
                                            <button class="btn action-btn btn-reject" onclick="changeStatus(<?= $booking['id'] ?>, 'ถูกปฏิเสธ')">
                                                <i class="bi bi-x-circle"></i> ปฏิเสธ
                                            </button>
                                        <?php endif; ?>

                                        <button class="btn action-btn btn-delete" onclick="deleteBooking(<?= $booking['id'] ?>)">
                                            <i class="bi bi-trash"></i> ลบ
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Pending Tab -->
            <div class="tab-pane fade" id="pending" role="tabpanel">
                <div class="row">
                    <?php foreach ($pending as $booking): ?>
                        <div class="col-lg-6 col-xl-4">
                            <div class="booking-card pending">
                                <div class="booking-card-header">
                                    <div>
                                        <h5 class="mb-0"><?= htmlspecialchars($booking['name']) ?></h5>
                                        <small>ID: <?= $booking['id'] ?></small>
                                    </div>
                                    <span class="status-badge status-pending">รออนุมัติ</span>
                                </div>
                                <div class="booking-card-body">
                                    <div class="booking-info-item">
                                        <i class="bi bi-calendar-event"></i>
                                        <div>
                                            <div class="booking-info-label">วันที่</div>
                                            <div class="booking-info-value"><?= $booking['date'] ?></div>
                                        </div>
                                    </div>

                                    <div class="booking-info-item">
                                        <i class="bi bi-clock"></i>
                                        <div>
                                            <div class="booking-info-label">เวลา</div>
                                            <div class="booking-info-value"><?= $booking['time'] ?></div>
                                        </div>
                                    </div>

                                    <div class="booking-info-item">
                                        <i class="bi bi-people"></i>
                                        <div>
                                            <div class="booking-info-label">จำนวนคน</div>
                                            <div class="booking-info-value"><?= $booking['people'] ?> คน</div>
                                        </div>
                                    </div>

                                    <div class="d-flex flex-wrap gap-2 mt-3">
                                        <button class="btn action-btn btn-view view-booking-btn"
                                            data-bs-toggle="modal"
                                            data-bs-target="#bookingModal"
                                            data-booking='<?= htmlspecialchars(json_encode($booking), ENT_QUOTES, 'UTF-8') ?>'>
                                            <i class="bi bi-info-circle"></i> รายละเอียด
                                        </button>

                                        <button class="btn action-btn btn-approve" onclick="changeStatus(<?= $booking['id'] ?>, 'อนุมัติแล้ว')">
                                            <i class="bi bi-check-circle"></i> อนุมัติ
                                        </button>
                                        <button class="btn action-btn btn-reject" onclick="changeStatus(<?= $booking['id'] ?>, 'ถูกปฏิเสธ')">
                                            <i class="bi bi-x-circle"></i> ปฏิเสธ
                                        </button>

                                        <button class="btn action-btn btn-delete" onclick="deleteBooking(<?= $booking['id'] ?>)">
                                            <i class="bi bi-trash"></i> ลบ
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Approved Tab -->
            <div class="tab-pane fade" id="approved" role="tabpanel">
                <div class="row">
                    <?php foreach ($approved as $booking): ?>
                        <div class="col-lg-6 col-xl-4">
                            <div class="booking-card approved">
                                <div class="booking-card-header">
                                    <div>
                                        <h5 class="mb-0"><?= htmlspecialchars($booking['name']) ?></h5>
                                        <small>ID: <?= $booking['id'] ?></small>
                                    </div>
                                    <span class="status-badge status-approved">อนุมัติแล้ว</span>
                                </div>
                                <div class="booking-card-body">
                                    <!-- ส่วนแสดงข้อมูล Admin ที่อนุมัติ -->
                                    <?php if (!empty($booking['approved_by'])): ?>
                                    <div class="approval-info">
                                        <div class="approval-info-item">
                                            <span class="approval-info-label">อนุมัติโดย:</span>
                                            <span class="approval-info-value"><?= htmlspecialchars($booking['approved_by']) ?></span>
                                        </div>
                                        <?php if (!empty($booking['approved_at_formatted'])): ?>
                                        <div class="approval-info-item">
                                            <span class="approval-info-label">เมื่อ:</span>
                                            <span class="approval-info-value"><?= $booking['approved_at_formatted'] ?></span>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                    <?php endif; ?>

                                    <div class="d-flex flex-wrap gap-2 mt-3">
                                        <button class="btn action-btn btn-view view-booking-btn"
                                            data-bs-toggle="modal"
                                            data-bs-target="#bookingModal"
                                            data-booking='<?= htmlspecialchars(json_encode($booking), ENT_QUOTES, 'UTF-8') ?>'>
                                            <i class="bi bi-info-circle"></i> รายละเอียด
                                        </button>

                                        <button class="btn action-btn btn-delete" onclick="deleteBooking(<?= $booking['id'] ?>)">
                                            <i class="bi bi-trash"></i> ลบ
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Rejected Tab -->
            <div class="tab-pane fade" id="rejected" role="tabpanel">
                <div class="row">
                    <?php foreach ($rejected as $booking): ?>
                        <div class="col-lg-6 col-xl-4">
                            <div class="booking-card rejected">
                                <div class="booking-card-header">
                                    <div>
                                        <h5 class="mb-0"><?= htmlspecialchars($booking['name']) ?></h5>
                                        <small>ID: <?= $booking['id'] ?></small>
                                    </div>
                                    <span class="status-badge status-rejected">ถูกปฏิเสธ</span>
                                </div>
                                <div class="booking-card-body">
                                    <!-- ส่วนแสดงข้อมูล Admin ที่อนุมัติ -->
                                    <?php if (!empty($booking['approved_by'])): ?>
                                    <div class="approval-info">
                                        <div class="approval-info-item">
                                            <span class="approval-info-label">ปฏิเสธโดย:</span>
                                            <span class="approval-info-value"><?= htmlspecialchars($booking['approved_by']) ?></span>
                                        </div>
                                        <?php if (!empty($booking['approved_at_formatted'])): ?>
                                        <div class="approval-info-item">
                                            <span class="approval-info-label">เมื่อ:</span>
                                            <span class="approval-info-value"><?= $booking['approved_at_formatted'] ?></span>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                    <?php endif; ?>

                                    <div class="d-flex flex-wrap gap-2 mt-3">
                                        <button class="btn action-btn btn-view view-booking-btn"
                                            data-bs-toggle="modal"
                                            data-bs-target="#bookingModal"
                                            data-booking='<?= htmlspecialchars(json_encode($booking), ENT_QUOTES, 'UTF-8') ?>'>
                                            <i class="bi bi-info-circle"></i> รายละเอียด
                                        </button>

                                        <button class="btn action-btn btn-delete" onclick="deleteBooking(<?= $booking['id'] ?>)">
                                            <i class="bi bi-trash"></i> ลบ
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Booking Detail Modal -->
    <div class="modal fade booking-modal" id="bookingModal" tabindex="-1" aria-labelledby="bookingModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="bookingModalLabel">รายละเอียดการจอง</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" id="bookingDetailTable">
                            <!-- ข้อมูลจะถูกเติมโดย JavaScript -->
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ปิด</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Slip Modal -->
    <div class="modal fade" id="slipModal" tabindex="-1" aria-labelledby="slipModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content bg-transparent border-0">
                <div class="modal-body text-center p-0">
                    <img id="slipModalImg" src="" alt="slip" 
                        style="max-width:100%;max-height:80vh;border-radius:12px;box-shadow:0 4px 24px #0006;">
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.querySelectorAll('.view-booking-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const bookingData = this.getAttribute('data-booking');
            if (!bookingData) return;

            let booking;
            try {
                booking = JSON.parse(bookingData);
            } catch (e) {
                console.error("Invalid JSON:", bookingData);
                return;
            }

            let html = '';
            const fields = [
                { key: 'name', label: 'ชื่อคณะ' },
                { key: 'date', label: 'วันที่จอง' },
                { key: 'time', label: 'เวลา' },
                { key: 'people', label: 'จำนวนผู้เข้าชม' },
                { key: 'status', label: 'สถานะ' },
                { key: 'total_amount', label: 'ยอดรวม', format: v => Number(v).toLocaleString() + ' บาท' },
                { key: 'deposit_amount', label: 'ยอดมัดจำ', format: v => Number(v).toLocaleString() + ' บาท' },
                { key: 'remain_amount', label: 'ยอดคงเหลือ', format: v => Number(v).toLocaleString() + ' บาท' },
                { key: 'phone', label: 'เบอร์โทร' },
                { key: 'approved_by', label: 'อนุมัติโดย' },
                { key: 'approved_at_formatted', label: 'อนุมัติเมื่อ' },
                { key: 'doc', label: 'เอกสาร', format: v => v ? `<a href="../user/Doc/${v}" target="_blank">ดูไฟล์</a>` : '-' },
                { 
                    key: 'slip', 
                    label: 'สลิป', 
                    format: v => v 
                        ? `<img src="../user/Paymentslip-Gardenreservation/${v}" alt="slip" class="slip-img"
                            style="max-width:180px;max-height:180px;cursor:pointer;border-radius:8px;box-shadow:0 2px 8px #0002;"
                            onclick="showSlipModal('../user/Paymentslip-Gardenreservation/${v}')">` 
                        : '-' 
                },
            ];

            fields.forEach(field => {
                let value = booking[field.key] ?? '';
                if (field.format) value = field.format(value);
                html += `
                    <tr>
                        <th style="width:180px; background-color: #f8f9fa;">${field.label}</th>
                        <td>${value}</td>
                    </tr>
                `;
            });

            document.getElementById('bookingDetailTable').innerHTML = html;
        });
    });

    function showSlipModal(src) {
        const modalImg = document.getElementById('slipModalImg');
        modalImg.src = src;

        const slipModal = new bootstrap.Modal(document.getElementById('slipModal'));
        slipModal.show();
    }

        // ฟังก์ชันค้นหาการจอง
        document.querySelector('.search-box input').addEventListener('keyup', function() {
            const searchText = this.value.toLowerCase();
            document.querySelectorAll('.booking-card').forEach(card => {
                const name = card.querySelector('h5').textContent.toLowerCase();
                if (name.includes(searchText)) {
                    card.parentElement.style.display = 'block';
                } else {
                    card.parentElement.style.display = 'none';
                }
            });
        });

        // ฟังก์ชันเปลี่ยนสถานะ (อนุมัติ/ปฏิเสธ)
        function changeStatus(id, newStatus) {
            if (!confirm('คุณต้องการเปลี่ยนสถานะเป็น "' + newStatus + '" ใช่หรือไม่?')) return;
            fetch('booking_list.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: 'action=change_status&id=' + encodeURIComponent(id) + '&status=' + encodeURIComponent(newStatus)
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        alert('เปลี่ยนสถานะเรียบร้อยแล้ว');
                        location.reload();
                    } else {
                        alert('เกิดข้อผิดพลาด');
                    }
                });
        }

        // ฟังก์ชันลบการจอง
        function deleteBooking(id) {
            if (!confirm('คุณต้องการลบการจองนี้ใช่หรือไม่?')) return;
            fetch('booking_list.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: 'action=delete&id=' + encodeURIComponent(id)
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        alert('ลบการจองเรียบร้อยแล้ว');
                        location.reload();
                    } else {
                        alert('เกิดข้อผิดพลาด');
                    }
                });
        }

        function showSlipModal(imageUrl) {
            const slipModal = new bootstrap.Modal(document.getElementById('slipModal'));
            document.getElementById('slipModalImg').src = imageUrl;
            slipModal.show();
        }

        // กรองสถานะ
        document.getElementById('statusFilter').addEventListener('change', function() {
            const val = this.value;
            document.querySelectorAll('.booking-card').forEach(card => {
                const status = card.querySelector('.status-badge').textContent.trim();
                if (
                    val === 'all' ||
                    (val === 'pending' && status === 'รออนุมัติ') ||
                    (val === 'approved' && status === 'อนุมัติแล้ว') ||
                    (val === 'rejected' && status === 'ถูกปฏิเสธ')
                ) {
                    card.parentElement.style.display = 'block';
                } else {
                    card.parentElement.style.display = 'none';
                }
            });
        });

        // เรียงลำดับ
        document.getElementById('sortFilter').addEventListener('change', function() {
            const val = this.value;
            // หา .row ที่อยู่ใน tab ปัจจุบัน
            const activeTab = document.querySelector('.tab-pane.active.show');
            if (!activeTab) return;
            const row = activeTab.querySelector('.row');
            if (!row) return;
            const cards = Array.from(row.children);
            cards.sort((a, b) => {
                if (val === 'name') {
                    const nameA = a.querySelector('h5').textContent.trim();
                    const nameB = b.querySelector('h5').textContent.trim();
                    return nameA.localeCompare(nameB, 'th');
                } else if (val === 'people') {
                    const peopleA = parseInt(a.querySelector('.booking-info-value').textContent) || 0;
                    const peopleB = parseInt(b.querySelector('.booking-info-value').textContent) || 0;
                    return peopleB - peopleA;
                } else { // date
                    const idA = parseInt(a.querySelector('small').textContent.replace('ID: ', ''));
                    const idB = parseInt(b.querySelector('small').textContent.replace('ID: ', ''));
                    return idB - idA; // id มากสุด = ใหม่สุด
                }
            });
            cards.forEach(card => row.appendChild(card));
        });
    </script>
</body>
</html>