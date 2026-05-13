<?php
require_once 'auth.php';
require_once __DIR__ . '/../db/db.php';

// =========================
// นับออเดอร์รอยืนยัน
// =========================
$sql_pending = "SELECT COUNT(*) AS total_pending FROM orders WHERE order_status = 'pending'";
$stmt_pending = $conn->prepare($sql_pending);
$stmt_pending->execute();
$result_pending = $stmt_pending->get_result();
$pending = $result_pending->fetch_assoc();
$total_pending = $pending['total_pending'];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menu Management</title>
    <link rel="stylesheet" href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary: #4361ee;
            --sidebar-bg: #f8f9fa;
            --sidebar-text: #fff;
            --dark-text: #333;
            --sidebar-width: 260px;
        }

        body {
            margin: 0;
            font-family: "Kanit", sans-serif;
            background: #f4f7fe;
        }

        /*ปุ่ม Hamburger */
        .mobile-toggle {
            display: none;
            position: fixed;
            top: 15px;
            left: 15px;
            z-index: 1000;
            background: var(--primary);
            color: white;
            border: none;
            padding: 10px;
            border-radius: 8px;
            cursor: pointer;
            box-shadow: 0 4px 12px rgba(67, 97, 238, 0.3);
        }

        .modern-sidebar {
            background: var(--sidebar-bg);
            width: var(--sidebar-width);
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            z-index: 999;
            box-shadow: 2px 0 24px rgba(0, 0, 0, 0.06);
            display: flex;
            flex-direction: column;
            padding: 20px 0;
            transition: all 0.3s ease;
        }

        /* Overlay สำหรับมือถือ */
        .sidebar-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 998;
        }

        .modern-sidebar .nav {
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 5px;
            padding: 0 15px;
            overflow-y: auto;
        }

        .modern-sidebar .nav-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 15px;
            border-radius: 12px;
            color: var(--dark-text);
            text-decoration: none;
            font-weight: 500;
            transition: all 0.2s;
            position: relative;
        }

        .modern-sidebar .nav-link i {
            font-size: 1.3rem;
            color: #888;
        }

        .modern-sidebar .nav-link:hover,
        .modern-sidebar .nav-link.active {
            background: var(--primary);
            color: white !important;
        }

        .modern-sidebar .nav-link:hover i,
        .modern-sidebar .nav-link.active i {
            color: white;
        }

        /* Notification Badges */
        .notification-dot {
            position: absolute;
            top: 10px;
            right: 10px;
            min-width: 18px;
            height: 18px;
            padding: 0 5px;
            border-radius: 10px;
            background: #ff4757;
            color: #fff;
            font-size: 11px;
            display: flex;
            align-items: center;
            justify-content: center;
            animation: pulseRed 1.5s infinite;
        }

        .notification-badge {
            background: #ff4757;
            color: white;
            border-radius: 50%;
            padding: 2px 6px;
            font-size: 0.7rem;
            position: absolute;
            top: -5px;
            right: -5px;
        }

        /* Responsive Logic */
        @media (max-width: 992px) {
            .modern-sidebar {
                left: calc(var(--sidebar-width) * -1); /* ซ่อน sidebar ไว้ทางซ้าย */
            }

            .modern-sidebar.active {
                left: 0;
            }

            .mobile-toggle {
                display: block;
            }

            .sidebar-overlay.active {
                display: block;
            }
        }

        @keyframes pulseRed {
            0% { transform: scale(1); box-shadow: 0 0 0 0 rgba(255, 71, 87, 0.7); }
            70% { transform: scale(1.1); box-shadow: 0 0 0 10px rgba(255, 71, 87, 0); }
            100% { transform: scale(1); box-shadow: 0 0 0 0 rgba(255, 71, 87, 0); }
        }
    </style>
</head>

<body>

    <!-- ปุ่มเปิดปิดสำหรับมือถือ -->
    <button class="mobile-toggle" id="sidebarToggle">
        <i class='bx bx-menu-alt-left' style="font-size: 24px;"></i>
    </button>

    <!-- พื้นหลังดำเมื่อเปิดเมนูในมือถือ -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <div class="modern-sidebar" id="mainSidebar">
        <div class="logo-img text-center" style="margin-bottom: 25px;">
            <a href="./index.php">
                <img src="../user/image/logo-3.png" alt="Logo" style="width: 140px; margin: 0 auto;">
            </a>
        </div>

        <nav class="nav">
            <a href="./index.php" class="nav-link<?php if (basename($_SERVER['PHP_SELF']) == 'index.php') echo ' active'; ?>">
                <i class='bx bxs-dashboard'></i> Dashboard
            </a>
            
            <a href="./booking_list.php" class="nav-link<?php if (basename($_SERVER['PHP_SELF']) == 'booking_list.php') echo ' active'; ?>">
                <i class='bx bxs-calendar-check'></i> รายการจอง
                <div class="notification-bell ms-auto" style="position: relative;">
                    <i class='bx bxs-bell bell-icon'></i>
                </div>
            </a>

            <a href="./manage_orders.php" class="nav-link<?php if (basename($_SERVER['PHP_SELF']) == 'manage_orders.php') echo ' active'; ?>">
                <i class='bx bxs-package'></i> จัดการคำสั่งซื้อ
                <?php if($total_pending > 0): ?>
                    <span class="notification-dot"><?= (int)$total_pending ?></span>
                <?php endif; ?>
            </a>

            <a href="./manage_product.php" class="nav-link<?php if (basename($_SERVER['PHP_SELF']) == 'manage_product.php') echo ' active'; ?>">
                <i class='bx bxs-shopping-bag'></i> สินค้า
            </a>

            <a href="./edit_courses.php" class="nav-link<?php if (basename($_SERVER['PHP_SELF']) == 'edit_courses.php') echo ' active'; ?>">
                <i class='bx bxs-graduation'></i> กิจกรรมอบรม
            </a>

            <a href="./admin_users.php" class="nav-link<?php if (basename($_SERVER['PHP_SELF']) == 'admin_users.php') echo ' active'; ?>">
                <i class='bx bxs-user-account'></i> จัดการผู้ใช้
            </a>

            <a href="./add_admin.php" class="nav-link<?php if (basename($_SERVER['PHP_SELF']) == 'add_admin.php') echo ' active'; ?>">
                <i class='bx bxs-user-plus'></i> เพิ่มแอดมิน
            </a>

            <a href="logout.php" class="nav-link" style="margin-top: auto; margin-bottom: 20px; color: #ff4757;">
                <i class='bx bx-log-out-circle'></i> ออกจากระบบ
            </a>
        </nav>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // 1. จัดการระบบ Sidebar
            const sidebar = document.getElementById('mainSidebar');
            const toggleBtn = document.getElementById('sidebarToggle');
            const overlay = document.getElementById('sidebarOverlay');

            // ฟังก์ชันสำหรับสลับการเปิด-ปิด
            function toggleSidebar() {
                sidebar.classList.toggle('active');
                overlay.classList.toggle('active');
            }

            // ตรวจสอบว่าปุ่มมีอยู่จริงก่อนใส่ Event
            if (toggleBtn) {
                toggleBtn.addEventListener('click', function(e) {
                    e.preventDefault(); // ป้องกัน default behavior
                    toggleSidebar();
                });
            }

            if (overlay) {
                overlay.addEventListener('click', toggleSidebar);
            }

            // 2. ระบบแจ้งเตือนเดิมของคุณ
            let previousCount = <?= isset($newBookings) ? (int)$newBookings : 0 ?>;

            function checkNotifications() {
                const url = new URL('get_new_bookings.php', window.location.href);
                url.searchParams.set('_', Date.now());
                fetch(url.toString(), { cache: 'no-store' })
                    .then(response => response.json())
                    .then(data => {
                        const bell = document.querySelector('.notification-bell');
                        if (!bell) return;
                        let badge = bell.querySelector('.notification-badge');
                        const count = parseInt(data.count) || 0;

                        if (count > 0) {
                            if (!badge) {
                                badge = document.createElement('span');
                                badge.className = 'notification-badge';
                                bell.appendChild(badge);
                            }
                            badge.textContent = count;
                            if (count > previousCount) {
                                bell.classList.add('flash');
                                setTimeout(() => bell.classList.remove('flash'), 2500);
                            }
                        } else if (badge) {
                            badge.remove();
                        }
                        previousCount = count;
                    })
                    .catch(error => console.error('Fetch error:', error));
            }

            checkNotifications();
            setInterval(checkNotifications, 3000);
        });
    </script>

</body>
</html>