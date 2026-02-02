<?php
require_once 'auth.php';
require_once 'db.php';

// ดึงชื่อ admin จาก session
$admin_name = $_SESSION['admin_name'] ?? 'Admin';

// ตรวจสอบว่ามีการส่ง member_id มาหรือไม่
$member_id_filter = isset($_GET['member_id']) ? (int)$_GET['member_id'] : 0;
$member_info = '';
$activities = [];

if ($member_id_filter > 0) {
    // ดึงอีเมลและชื่อของ member มาแสดง
    $info_stmt = $conn->prepare("SELECT fullname, email FROM members WHERE member_id = ?");
    $info_stmt->bind_param("i", $member_id_filter);
    $info_stmt->execute();
    $info_res = $info_stmt->get_result();
    if ($info_row = $info_res->fetch_assoc()) {
        $member_info = htmlspecialchars($info_row['fullname']) . ' (' . htmlspecialchars($info_row['email']) . ')';
    }
    $info_stmt->close();

    // 1. Get Bookings
    $stmt = $conn->prepare("SELECT booking_code, created_at, status FROM bookings WHERE member_id = ?");
    $stmt->bind_param('i', $member_id_filter);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $activities[] = [
            'type' => 'การจอง',
            'icon' => 'bi-calendar-check',
            'color' => 'bg-info-light text-info',
            'date' => $row['created_at'],
            'description' => 'สร้างการจองใหม่ รหัส #' . htmlspecialchars($row['booking_code']) . ' (สถานะ: ' . htmlspecialchars($row['status']) . ')'
        ];
    }
    $stmt->close();

    // 2. Get Comments (ต้อง ALTER TABLE ก่อน)
    $stmt = $conn->prepare("SELECT cc.comment_text, cc.created_at, c.course_name 
                            FROM course_comments cc 
                            JOIN courses c ON cc.courses_id = c.courses_id 
                            WHERE cc.member_id = ?");
    $stmt->bind_param('i', $member_id_filter);
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $activities[] = [
                'type' => 'ความคิดเห็น',
                'icon' => 'bi-chat-dots',
                'color' => 'bg-warning-light text-warning',
                'date' => $row['created_at'],
                'description' => 'แสดงความคิดเห็นในกิจกรรม: "' . htmlspecialchars($row['course_name']) . '"'
            ];
        }
        $stmt->close();
    }

    // 3. Get successful logins
    $stmt = $conn->prepare("SELECT attempted_at, ip_address FROM login_logs WHERE member_id = ? AND success = 1");
    $stmt->bind_param('i', $member_id_filter);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $activities[] = [
            'type' => 'เข้าสู่ระบบ',
            'icon' => 'bi-box-arrow-in-right',
            'color' => 'bg-success-light text-success',
            'date' => $row['attempted_at'],
            'description' => 'เข้าสู่ระบบสำเร็จจาก IP: ' . htmlspecialchars($row['ip_address'])
        ];
    }
    $stmt->close();

    // Sort activities by date descending
    usort($activities, function ($a, $b) {
        return strtotime($b['date']) - strtotime($a['date']);
    });
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ประวัติกิจกรรมผู้ใช้</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root { --primary: #4361ee; --secondary: #3f37c9; --success: #2ecc71; --danger: #e74a3b; --warning: #f39c12; --info: #36b9cc; --light: #f8f9fa; --dark: #212529; --border-color: #e9ecef; }
        body { font-family: 'Kanit', sans-serif; background-color: #f5f7fa; }
        .container-fluid { margin-left: 250px; }
        .dashboard-header { background: linear-gradient(120deg, #4361ee, #3f37c9); color: white; padding: 1.2rem 1.5rem; border-radius: 16px; box-shadow: 0 8px 25px rgba(67, 97, 238, .35); }
        .admin-profile { display: flex; align-items: center; background: rgba(255, 255, 255, .2); backdrop-filter: blur(10px); padding: .5rem 1rem; border-radius: 50px; }
        .admin-profile img { width: 36px; height: 36px; border-radius: 50%; margin-right: 10px; border: 2px solid rgba(255, 255, 255, 0.5); }
        .card-box { background: white; border-radius: 20px; box-shadow: 0 10px 30px rgba(0, 0, 0, .07); padding: 2rem; border: none; }
        .badge.bg-info-light { background-color: rgba(54, 185, 204, 0.15); color: #36b9cc; }
        .badge.bg-warning-light { background-color: rgba(243, 156, 18, 0.15); color: #f39c12; }
        .badge.bg-success-light { background-color: rgba(46, 204, 113, 0.15); color: #27ae60; }
        .badge.bg-secondary-light { background-color: rgba(133, 135, 150, 0.15); color: #858796; }

        .timeline { position: relative; padding-left: 30px; }
        .timeline::before { content: ''; position: absolute; left: 15px; top: 0; bottom: 0; width: 2px; background-color: var(--border-color); }
        .timeline-item { position: relative; margin-bottom: 2rem; }
        .timeline-icon { position: absolute; left: -23px; top: 0; width: 36px; height: 36px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; border: 2px solid white; }
        .timeline-content { background-color: #f8f9fc; border-radius: 12px; padding: 1rem 1.5rem; border: 1px solid var(--border-color); }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>

    <div class="container-fluid p-4">
        <!-- HEADER -->
        <header class="dashboard-header mb-4">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="mb-0">
                        <i class="bi bi-list-check"></i> 
                        ประวัติกิจกรรมผู้ใช้
                        <?php if ($member_id_filter > 0 && !empty($member_info)): ?>
                            <span class="fs-6 fw-normal">(สำหรับ: <?= $member_info ?>)</span>
                        <?php elseif ($member_id_filter > 0): ?>
                            <span class="fs-6 fw-normal">(สำหรับ Member ID: <?= $member_id_filter ?>)</span>
                        <?php endif; ?>
                    </h4>
                </div>
                <div class="admin-profile">
                    <img src="https://ui-avatars.com/api/?name=<?= urlencode($admin_name) ?>&background=random&color=fff" alt="Admin">
                    <span><?= htmlspecialchars($admin_name) ?></span>
                </div>
            </div>
        </header>

        <!-- CONTENT -->
        <div class="card-box">
            <div class="d-flex justify-content-end mb-3">
                <a href="admin_users.php" class="btn btn-secondary btn-sm">
                    <i class="bi bi-arrow-left"></i> กลับหน้าจัดการผู้ใช้
                </a>
            </div>

            <div class="timeline">
                <?php if (empty($activities)): ?>
                    <div class="text-center text-muted p-5">
                        <i class="bi bi-folder-x fs-1"></i>
                        <p class="mt-3">ไม่พบกิจกรรมสำหรับผู้ใช้นี้</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($activities as $activity): ?>
                        <div class="timeline-item">
                            <div class="timeline-icon <?= $activity['color'] ?>">
                                <i class="bi <?= $activity['icon'] ?>"></i>
                            </div>
                            <div class="timeline-content">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h6 class="fw-bold mb-0"><?= $activity['type'] ?></h6>
                                    <small class="text-muted"><?= htmlspecialchars($activity['date']) ?></small>
                                </div>
                                <p class="mb-0 mt-1"><?= $activity['description'] ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>