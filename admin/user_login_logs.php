<?php
require_once 'auth.php';
require_once __DIR__ . '/../db/db.php';

// ดึงชื่อ admin จาก session
$admin_name = $_SESSION['admin_name'] ?? 'Admin';

// ตรวจสอบว่ามีการส่ง member_id มาหรือไม่
$member_id_filter = isset($_GET['member_id']) ? (int)$_GET['member_id'] : 0;
$member_info = '';

$sql = "SELECT ll.member_id, ll.email, ll.attempted_at, ll.ip_address, ll.user_agent, ll.success, m.fullname 
        FROM login_logs ll
        LEFT JOIN members m ON ll.member_id = m.member_id";

if ($member_id_filter > 0) {
    $sql .= " WHERE ll.member_id = ?";
    $stmt = $conn->prepare($sql . " ORDER BY ll.attempted_at DESC");
    $stmt->bind_param("i", $member_id_filter);
    
    // ดึงอีเมลและชื่อของ member มาแสดง
    $info_stmt = $conn->prepare("SELECT fullname, email FROM members WHERE member_id = ?");
    $info_stmt->bind_param("i", $member_id_filter);
    $info_stmt->execute();
    $info_res = $info_stmt->get_result();
    if($info_row = $info_res->fetch_assoc()){
        $member_info = htmlspecialchars($info_row['fullname']) . ' (' . htmlspecialchars($info_row['email']) . ')';
    }
    $info_stmt->close();

} else {
    $stmt = $conn->prepare($sql . " ORDER BY ll.attempted_at DESC");
}

$stmt->execute();
$result = $stmt->get_result();
$logs = [];
while ($row = $result->fetch_assoc()) {
    $logs[] = $row;
}
$stmt->close();
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ประวัติการเข้าสู่ระบบ</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css">
    <style>
        :root {
            --primary: #4361ee;
            --secondary: #3f37c9;
            --success: #2ecc71;
            --danger: #e74a3b;
            --light: #f8f9fa;
            --dark: #212529;
            --border-color: #e9ecef;
        }

        body {
            font-family: 'Kanit', sans-serif;
            background-color: #f5f7fa;
        }
        .container-fluid {
            margin-left: 250px;
        }
        .dashboard-header {
            background: linear-gradient(120deg, #4361ee, #3f37c9);
            color: white;
            padding: 1.2rem 1.5rem;
            border-radius: 16px;
            box-shadow: 0 8px 25px rgba(67, 97, 238, .35);
        }
        .admin-profile {
            display: flex;
            align-items: center;
            background: rgba(255, 255, 255, .2);
            backdrop-filter: blur(10px);
            padding: .5rem 1rem;
            border-radius: 50px;
        }
        .admin-profile img {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            margin-right: 10px;
            border: 2px solid rgba(255, 255, 255, 0.5);
        }
        .card-box {
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, .07);
            padding: 2rem;
            border: none;
        }
        .badge.bg-success-light {
            background-color: rgba(46, 204, 113, 0.15);
            color: #27ae60;
            font-weight: 500;
            padding: 0.5em 0.9em;
        }
        .badge.bg-danger-light {
            background-color: rgba(231, 76, 60, 0.15);
            color: #c0392b;
            font-weight: 500;
            padding: 0.5em 0.9em;
        }

        /* Modern Table Styles */
        .table {
            border-color: var(--border-color);
        }
        #logsTable thead {
            background-color: #f8f9fc;
        }
        #logsTable thead th {
            border-bottom-width: 1px;
            font-weight: 600;
            color: #5a5c69;
            text-transform: uppercase;
            font-size: 0.8rem;
            letter-spacing: 0.5px;
            padding: 1rem;
        }
        #logsTable tbody td {
            padding: 1rem;
            vertical-align: middle;
        }

        /* DataTables Customization */
        .dataTables_wrapper .dataTables_filter input,
        .dataTables_wrapper .dataTables_length select {
            border-radius: 8px;
            padding: 0.5rem 1rem;
            border: 1px solid var(--border-color);
            transition: all 0.3s ease;
        }
        .dataTables_wrapper .dataTables_filter input:focus,
        .dataTables_wrapper .dataTables_length select:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.15);
        }
        .pagination .page-item .page-link {
            border-radius: 8px !important;
            margin: 0 3px;
            border: 1px solid var(--border-color);
            color: var(--primary);
        }
        .pagination .page-item.active .page-link {
            background-color: var(--primary);
            border-color: var(--primary);
            color: white;
        }
        .pagination .page-item.disabled .page-link {
            color: #adb5bd;
        }
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
                        <i class="bi bi-shield-check"></i> 
                        ประวัติการเข้าสู่ระบบ
                        <?php if ($member_id_filter > 0 && $member_info): ?>
                            <span class="fs-6 fw-normal">(สำหรับ: <?= $member_info ?>)</span>
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

            <div class="table-responsive">
                <table id="logsTable" class="table table-hover align-middle" style="width:100%">
                    <thead class="table-light">
                        <tr>
                            <th><i class="bi bi-person me-2"></i>ชื่อผู้ใช้</th>
                            <th><i class="bi bi-envelope me-2"></i>อีเมล</th>
                            <th><i class="bi bi-clock me-2"></i>เวลา</th>
                            <th><i class="bi bi-pc-display-horizontal me-2"></i>IP Address</th>
                            <th><i class="bi bi-window-stack me-2"></i>User Agent</th>
                            <th><i class="bi bi-check2-circle me-2"></i>ผลลัพธ์</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $i = 0; foreach ($logs as $log): $i++; ?>
                            <tr>
                                <td><?= htmlspecialchars($log['fullname'] ?? 'N/A') ?></td>
                                <td><?= htmlspecialchars($log['email']) ?></td>
                                <td><?= htmlspecialchars($log['attempted_at']) ?></td>
                                <td><?= htmlspecialchars($log['ip_address']) ?></td>
                                <td>
                                    <?php
                                        $ua = htmlspecialchars($log['user_agent'] ?? 'N/A');
                                        $short_ua = mb_strimwidth($ua, 0, 40, '...');
                                        $collapse_id = 'ua-collapse-' . $i;
                                    ?>
                                    <span title="<?= $ua ?>"><?= $short_ua ?></span>
                                    <?php if (strlen($ua) > 40): ?>
                                        <a class="btn btn-link btn-sm p-0 ms-1" data-bs-toggle="collapse" href="#<?= $collapse_id ?>" role="button" aria-expanded="false" aria-controls="<?= $collapse_id ?>">
                                            <i class="bi bi-arrows-expand"></i>
                                        </a>
                                        <div class="collapse mt-2" id="<?= $collapse_id ?>">
                                            <small class="text-muted" style="word-break: break-all;"><?= $ua ?></small>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($log['success']): ?>
                                        <span class="badge rounded-pill bg-success-light"><i class="bi bi-check-circle-fill"></i> สำเร็จ</span>
                                    <?php else: ?>
                                        <span class="badge rounded-pill bg-danger-light"><i class="bi bi-x-circle-fill"></i> ไม่สำเร็จ</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#logsTable').DataTable({
                "order": [[ 2, "desc" ]],
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.11.5/i18n/th.json"
                }
            });
        });
    </script>
</body>
</html>