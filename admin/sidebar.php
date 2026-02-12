<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>manu</title>
    <link rel="stylesheet" href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Kanit:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Prompt:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary: #4361ee;
            --secondary: #3f37c9;
            --sidebar-bg: #232946;
            --sidebar-active: #4e73df;
            --sidebar-hover: #eebbc3;
            --sidebar-text: #fff;
            --sidebar-icon: #a1a1aa;
            --sidebar-radius: 18px;
            --light: #f8f9fa;
        }

        .modern-sidebar {
            font-family: "Kanit", sans-serif;
            background: var(--light);
            color: #671515ff;
            width: 240px;
            min-height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            z-index: 100;
            box-shadow: 2px 0 24px rgba(0, 0, 0, 0.06);
            display: flex;
            flex-direction: column;
            padding: 32px 0 16px 0;
        }

        .logo-subtitle {
            font-size: 0.85rem;
            font-weight: 300;
            color: rgba(255, 255, 255, 0.7);
            text-align: center;
            letter-spacing: 1px;
        }

        .modern-sidebar .nav {
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 8px;
            padding: 0 18px;
        }

        .modern-sidebar .nav-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 18px;
            border-radius: 12px;
            color: #333333;
            font-size: 1.08rem;
            font-weight: 500;
            text-decoration: none;
            transition: background 0.2s, color 0.2s;
        }

        .modern-sidebar .nav-link i {
            font-size: 1.3rem;
            color: #888;
            transition: color 0.2s;
        }

        .modern-sidebar .nav-link:hover,
        .modern-sidebar .nav-link.active {
            background: var(--primary);
            color: var(--sidebar-text) !important;
        }

        .modern-sidebar .nav-link:hover i,
        .modern-sidebar .nav-link.active i {
            color: var(--sidebar-text);
        }

        .modern-sidebar .sidebar-footer {
            text-align: center;
            font-size: 0.95rem;
            color: var(--primary);
            margin-top: 24px;
            padding-bottom: 8px;
        }

        .notification-bell {
            position: relative;
            display: inline-block;
            margin-left: auto;
        }

        .notification-badge {
            background-color: #ff4757;
            color: white;
            border-radius: 50%;
            padding: 3px 6px;
            min-width: 18px;
            height: 18px;
            font-size: 0.7rem;
            font-weight: bold;
            display: flex;
            align-items: center;
            justify-content: center;
            position: absolute;
            top: -8px;
            right: -8px;
            animation: pulse 1.5s infinite ease-in-out;
            box-shadow: 0 0 5px rgba(255, 71, 87, 0.5);
        }

        .bell-icon {
            font-size: 1.3rem;
            color: #888;
            transition: all 0.3s ease;
        }

        .notification-bell.has-notification .bell-icon {
            animation: bellShake 0.5s ease-in-out infinite;
            transform-origin: top center;
            display: inline-block;
        }

        .notification-bell.flash {
            box-shadow: 0 0 12px 4px rgba(255, 71, 87, 0.28);
            transform: scale(1.03);
            transition: transform 0.2s, box-shadow 0.2s;
        }

        @keyframes bellShake {
            0% {
                transform: rotate(0deg);
            }

            25% {
                transform: rotate(15deg);
            }

            50% {
                transform: rotate(-15deg);
            }

            75% {
                transform: rotate(10deg);
            }

            100% {
                transform: rotate(0deg);
            }
        }

        /* อนิเมชันการเต้นของตัวเลข */
        @keyframes pulse {
            0% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.1);
            }

            100% {
                transform: scale(1);
            }
        }
    </style>
</head>

<body>
    <div class="modern-sidebar">
        <div class="logo-img mb-5 text-center">
            <a href="./index.php">
                <img src="../user/image/logo-3.png" alt="Logo" style="width: 170px; display: block; margin: 0 auto 10px auto;">
            </a>
        </div>
        <nav class="nav flex-column">
            <a href="./index.php" class="nav-link<?php if (basename($_SERVER['PHP_SELF']) == 'index.php') echo ' active'; ?>">
                <i class='bx bxs-dashboard'></i> Dashboard
            </a>
            <a href="./booking_list.php" class="nav-link<?php if (basename($_SERVER['PHP_SELF']) == 'booking_list.php') echo ' active'; ?>">
                <i class='bx bxs-calendar-check'></i> รายการจอง
                <div class="notification-bell ms-auto">
                    <i class='bx bxs-bell bell-icon'></i>

                </div>
            </a>

            <a href="./manage_orders.php" class="nav-link<?php if (basename($_SERVER['PHP_SELF']) == 'manage_orders.php') echo ' active'; ?>">
                <i class='bx bxs-package'></i> จัดการคำสั่งซื้อ
            </a>

            <a href="./manage_product.php" class="nav-link<?php if (basename($_SERVER['PHP_SELF']) == 'manage_product.php') echo ' active'; ?>">
                <i class='bx bxs-package'></i> สินค้า
            </a>

            <a href="./edit_courses.php" class="nav-link<?php if (basename($_SERVER['PHP_SELF']) == 'edit_courses.php') echo ' active'; ?>">
                <i class='bx bxs-graduation'></i> กิจกรรมอบรม
            </a>


            <a href="./admin_users.php" class="nav-link<?php if (basename($_SERVER['PHP_SELF']) == 'admin_users.php') echo ' active'; ?>">
                <i class='bx bxs-user-account'></i> จัดการข้อมูลผู้ใช้
            </a>

            <a href="./add_admin.php" class="nav-link<?php if (basename($_SERVER['PHP_SELF']) == 'add_admin.php') echo ' active'; ?>">
                <i class='bx bxs-user-plus'></i> เพิ่มผู้ดูแลระบบ
            </a>

            <a href="logout.php" class="nav-link mt-5">
                <i class='bx bx-log-out-circle'></i> ออกจากระบบ
            </a>
        </nav>
    </div>


    <script>
        // ตรวจสอบการจองใหม่ (cache-busted) และแจ้งเตือนเมื่อมีรายการใหม่
        // เริ่มต้นจากค่าที่ server-rendered เพื่อลดการแจ้งเตือนเมื่อโหลดหน้า
        let previousCount = <?= isset($newBookings) ? (int)$newBookings : 0 ?>;

        function playBeep() {
            try {
                const ctx = new(window.AudioContext || window.webkitAudioContext)();
                const o = ctx.createOscillator();
                const g = ctx.createGain();
                o.type = 'sine';
                o.frequency.value = 880;
                g.gain.value = 0.05;
                o.connect(g);
                g.connect(ctx.destination);
                o.start();
                setTimeout(() => {
                    o.stop();
                    ctx.close();
                }, 180);
            } catch (e) {
                // ignore audio errors (some browsers block autoplay)
            }
        }

        function checkNotifications() {
            const url = new URL('get_new_bookings.php', window.location.href);
            url.searchParams.set('_', Date.now());
            fetch(url.toString(), {
                    cache: 'no-store'
                })
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
                        bell.classList.add('has-notification');

                        // ถ้าเพิ่มขึ้นจากเดิม ให้เล่นเสียงและกระพริบ
                        if (count > previousCount) {
                            // playBeep(); // ปิดเสียงแจ้งเตือนตามคำขอ
                            bell.classList.add('flash');
                            setTimeout(() => bell.classList.remove('flash'), 2500);
                        }
                    } else {
                        if (badge) badge.remove();
                        bell.classList.remove('has-notification');
                    }

                    previousCount = count;
                })
                .catch(error => console.error('Fetch error:', error));
        }

        // เรียกครั้งแรกเมื่อโหลดหน้า และตั้งเวลาเรียกทุก 3 วินาที
        document.addEventListener('DOMContentLoaded', function() {
            checkNotifications();
            setInterval(checkNotifications, 3000);
        });

        // อัปเดตเมื่อคลิกเมนู
        const bookingLink = document.querySelector('a[href="./booking_list.php"]');
        if (bookingLink) {
            bookingLink.addEventListener('click', function() {
                setTimeout(checkNotifications, 300);
            });
        }
    </script>

</body>

</html>