<?php
require_once 'auth.php';
require_once 'db.php';

// ดึงชื่อ admin จาก session
$admin_name = $_SESSION['admin_name'] ?? '';
$admin_email = $_SESSION['admin_email'] ?? '';

// อัพเดตสถานะหรือการลบผู้ใช้
if (isset($_GET['action'], $_GET['id'])) {
    $id = (int) $_GET['id'];
    if ($_GET['action'] === 'disable') {
        $stmt = $conn->prepare("UPDATE members SET status=0 WHERE id=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
    } elseif ($_GET['action'] === 'enable') {
        $stmt = $conn->prepare("UPDATE members SET status=1 WHERE id=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
    } elseif ($_GET['action'] === 'delete') {
        $stmt = $conn->prepare("DELETE FROM members WHERE id=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
    }
    header("Location: admin_users.php");
    exit;
}

// ดึงข้อมูลผู้ใช้ทั้งหมด
$result = $conn->query("SELECT * FROM members ORDER BY id DESC");
$users = [];
while ($row = $result->fetch_assoc()) {
    $users[] = $row;
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>จัดการผู้ใช้ - ระบบจัดการมังงะ</title>
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
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

        body {
            font-family: 'Kanit', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #e4e7f1 100%);
            color: #333;
            min-height: 100vh;
            overflow-x: hidden;
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

        .user-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
            overflow: hidden;
            margin-bottom: 1.5rem;
            border: none;
            position: relative;
        }

        .user-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 25px rgba(0, 0, 0, 0.1);
        }

        .user-card::after {
            content: "";
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: linear-gradient(90deg, var(--primary), var(--secondary));
        }

        .user-card-header {
            padding: 1rem 1.5rem;
            background: linear-gradient(90deg, rgba(67, 97, 238, 0.1), transparent);
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .user-card-body {
            padding: 1.5rem;
        }

        .user-avatar {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid var(--primary);
            box-shadow: 0 4px 10px rgba(67, 97, 238, 0.2);
        }

        .user-info-item {
            display: flex;
            margin-bottom: 0.8rem;
            align-items: flex-start;
        }

        .user-info-item i {
            font-size: 1.2rem;
            color: var(--primary);
            margin-right: 10px;
            margin-top: 3px;
            width: 24px;
            text-align: center;
        }

        .user-info-label {
            font-weight: 500;
            color: #6c757d;
            min-width: 100px;
        }

        .user-info-value {
            font-weight: 400;
            color: #495057;
        }

        .status-badge {
            padding: 0.35rem 0.65rem;
            border-radius: 50px;
            font-weight: 500;
            font-size: 0.8rem;
        }

        .status-active {
            background: rgba(46, 204, 113, 0.15);
            color: #27ae60;
        }

        .status-inactive {
            background: rgba(231, 76, 60, 0.15);
            color: #c0392b;
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

        .btn-disable {
            background: rgba(231, 74, 59, 0.1);
            color: var(--danger);
        }

        .btn-disable:hover {
            background: rgba(231, 74, 59, 0.2);
            color: var(--danger);
        }

        .btn-enable {
            background: rgba(46, 204, 113, 0.1);
            color: #27ae60;
        }

        .btn-enable:hover {
            background: rgba(46, 204, 113, 0.2);
            color: #27ae60;
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

        .stats-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.05);
            padding: 1.5rem;
            text-align: center;
            margin-bottom: 1.5rem;
            transition: all 0.3s ease;
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

        .user-modal .modal-content {
            border-radius: 16px;
            overflow: hidden;
            border: none;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
        }

        .user-modal .modal-header {
            background: linear-gradient(120deg, var(--primary), var(--secondary));
            color: white;
            border-bottom: none;
        }

        .user-modal .btn-close {
            filter: invert(1);
        }

        @media (max-width: 768px) {
            .user-card-body {
                padding: 1rem;
            }
            
            .action-btn {
                width: 100%;
                margin-bottom: 0.5rem;
            }
            
            .btn-group-vertical {
                width: 100%;
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
                            <h2 class="dashboard-title mb-0">จัดการผู้ใช้งานที่เป็นสมาชิก</h2>
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
                        <i class="bi bi-people-fill"></i>
                    </div>
                    <div class="stats-value"><?php echo count($users); ?></div>
                    <div class="stats-label">ผู้ใช้ทั้งหมด</div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="stats-card">
                    <div class="stats-icon" style="color: #27ae60;">
                        <i class="bi bi-person-check-fill"></i>
                    </div>
                    <div class="stats-value"><?php echo count(array_filter($users, function($u) { return isset($u['status']) && $u['status']; })); ?></div>
                    <div class="stats-label">ผู้ใช้ที่เปิดใช้งาน</div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="stats-card">
                    <div class="stats-icon" style="color: #e74c3c;">
                        <i class="bi bi-person-x-fill"></i>
                    </div>
                    <div class="stats-value"><?php echo count(array_filter($users, function($u) { return isset($u['status']) && !$u['status']; })); ?></div>
                    <div class="stats-label">ผู้ใช้ที่ปิดใช้งาน</div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="stats-card">
                    <div class="stats-icon" style="color: #f39c12;">
                        <i class="bi bi-clock-history"></i>
                    </div>
                    <div class="stats-value">5</div>
                    <div class="stats-label">ลงทะเบียนวันนี้</div>
                </div>
            </div>
        </div>

        <!-- Search and Filter -->
        <div class="filter-section">
            <div class="row">
                <div class="col-md-6">
                    <div class="search-box">
                        <input type="text" class="form-control" placeholder="ค้นหาผู้ใช้...">
                        <i class="bi bi-search"></i>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="d-flex flex-wrap gap-2">
                        <select class="form-select" style="border-radius: 50px;" id="statusFilter">
                            <option value="all" selected>สถานะทั้งหมด</option>
                            <option value="active">เปิดใช้งาน</option>
                            <option value="inactive">ปิดใช้งาน</option>
                        </select>
                        <select class="form-select" style="border-radius: 50px;" id="sortFilter">
                            <option value="date" selected>เรียงตามวันที่</option>
                            <option value="name">เรียงตามชื่อ</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- Users List -->
        <div class="row">
            <?php foreach ($users as $user): ?>
            <div class="col-lg-4 col-md-6">
                <div class="user-card">
                    <div class="user-card-header">
                        <div>
                            <h5 class="mb-0"><?= htmlspecialchars($user['fullname']) ?></h5>
                            <small>ID: <?= $user['id'] ?></small>
                        </div>
                        <span class="status-badge <?= isset($user['status']) && $user['status'] ? 'status-active' : 'status-inactive' ?>">
                            <?= isset($user['status']) && $user['status'] ? 'เปิดใช้งาน' : 'ปิดใช้งาน' ?>
                        </span>
                    </div>
                    <div class="user-card-body">
                        <div class="d-flex align-items-center mb-3">
                            <img src="https://ui-avatars.com/api/?name=<?= urlencode($user['fullname']) ?>&background=4361ee&color=fff" class="user-avatar me-3" alt="User Avatar">
                            <div>
                                <h6 class="mb-0"><?= htmlspecialchars($user['fullname']) ?></h6>
                                <small class="text-muted">สมาชิกตั้งแต่: 12/05/2023</small>
                            </div>
                        </div>
                        
                        <div class="user-info-item">
                            <i class="bi bi-envelope"></i>
                            <div>
                                <div class="user-info-label">อีเมล</div>
                                <div class="user-info-value"><?= htmlspecialchars($user['email']) ?></div>
                            </div>
                        </div>
                        
                        <div class="user-info-item">
                            <i class="bi bi-telephone"></i>
                            <div>
                                <div class="user-info-label">เบอร์โทร</div>
                                <div class="user-info-value"><?= htmlspecialchars($user['phone']) ?></div>
                            </div>
                        </div>
                        
                        <div class="d-flex flex-wrap gap-2 mt-3">
                            <button class="btn action-btn btn-view view-user-btn" 
                                data-bs-toggle="modal" 
                                data-bs-target="#userModal"
                                data-user='<?= htmlspecialchars(json_encode($user), ENT_QUOTES, 'UTF-8') ?>'>
                                <i class="bi bi-info-circle"></i> ดูข้อมูล
                            </button>
                            
                            <?php if (isset($user['status']) && $user['status']): ?>
                                <a href="?action=disable&id=<?= $user['id'] ?>" class="btn action-btn btn-disable">
                                    <i class="bi bi-person-dash"></i> ปิดใช้งาน
                                </a>
                            <?php else: ?>
                                <a href="?action=enable&id=<?= $user['id'] ?>" class="btn action-btn btn-enable">
                                    <i class="bi bi-person-check"></i> เปิดใช้งาน
                                </a>
                            <?php endif; ?>
                            
                            <a href="?action=delete&id=<?= $user['id'] ?>" class="btn action-btn btn-delete" onclick="return confirm('ยืนยันการลบผู้ใช้นี้?')">
                                <i class="bi bi-trash"></i> ลบ
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- User Detail Modal -->
    <div class="modal fade user-modal" id="userModal" tabindex="-1" aria-labelledby="userModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="userModalLabel">ข้อมูลสมาชิก</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="userDetailTable">
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.querySelectorAll('.view-user-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const user = JSON.parse(this.getAttribute('data-user'));
            let html = '';
            for (const key in user) {
                let value = user[key] !== null ? user[key] : '';
                let label = key;
                
                // แปลงชื่อฟิลด์เป็นภาษาไทย
                switch(key) {
                    case 'id': label = 'รหัสผู้ใช้'; break;
                    case 'fullname': label = 'ชื่อ-นามสกุล'; break;
                    case 'email': label = 'อีเมล'; break;
                    case 'phone': label = 'เบอร์โทรศัพท์'; break;
                    case 'status': 
                        label = 'สถานะ'; 
                        value = value ? '<span class="status-badge status-active">เปิดใช้งาน</span>' : '<span class="status-badge status-inactive">ปิดใช้งาน</span>';
                        break;
                    case 'created_at': label = 'วันที่สมัคร'; break;
                }
                
                html += `<tr>
                    <th style="width:180px; background-color: #f8f9fa;">${label}</th>
                    <td>${value}</td>
                </tr>`;
            }
            document.getElementById('userDetailTable').innerHTML = html;
        });
    });
    
    // ฟังก์ชันค้นหาผู้ใช้
    document.querySelector('.search-box input').addEventListener('keyup', function() {
        const searchText = this.value.toLowerCase();
        document.querySelectorAll('.user-card').forEach(card => {
            const name = card.querySelector('h5').textContent.toLowerCase();
            const email = card.querySelector('.user-info-value').textContent.toLowerCase();
            if (name.includes(searchText) || email.includes(searchText)) {
                card.parentElement.style.display = 'block';
            } else {
                card.parentElement.style.display = 'none';
            }
        });
    });
    
    // กรองสถานะ
    document.getElementById('statusFilter').addEventListener('change', function() {
        const val = this.value;
        document.querySelectorAll('.user-card').forEach(card => {
            const status = card.querySelector('.status-badge').textContent.trim();
            if (
                val === 'all' ||
                (val === 'active' && status === 'เปิดใช้งาน') ||
                (val === 'inactive' && status === 'ปิดใช้งาน')
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
        const row = document.querySelector('.row');
        const cards = Array.from(row.children);
        cards.sort((a, b) => {
            if (val === 'name') {
                const nameA = a.querySelector('h5').textContent.trim();
                const nameB = b.querySelector('h5').textContent.trim();
                return nameA.localeCompare(nameB, 'th');
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