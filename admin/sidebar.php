<?php

/**
 * Mango AI - Modern Responsive Admin Layout
 * UI/UX Upgrade + Animation + Glassmorphism
 */

declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

mysqli_report(MYSQLI_REPORT_OFF);

// --------------------------------------------------
// Security Headers
// --------------------------------------------------
if (!headers_sent()) {

    header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' https://cdnjs.cloudflare.com https://cdn.jsdelivr.net https://unpkg.com; style-src 'self' 'unsafe-inline' https://cdnjs.cloudflare.com https://cdn.jsdelivr.net https://fonts.googleapis.com https://unpkg.com; font-src 'self' https://fonts.gstatic.com https://cdnjs.cloudflare.com; img-src 'self' data: https:; connect-src 'self'; frame-src 'self' https://www.youtube.com; frame-ancestors 'self'; base-uri 'self'; form-action 'self'");

    header("X-Content-Type-Options: nosniff");
    header("X-Frame-Options: SAMEORIGIN");
    header("Referrer-Policy: strict-origin-when-cross-origin");
}

require_once __DIR__ . '/../db/db.php';

// --------------------------------------------------
// Pending Orders
// --------------------------------------------------
$total_pending = 0;

if (adminTableExists($conn, 'orders')) {

    $sql_pending = "
        SELECT COUNT(*) AS total_pending
        FROM orders
        WHERE order_status = 'pending'
    ";

    $stmt_pending = $conn->prepare($sql_pending);

    if ($stmt_pending) {

        $stmt_pending->execute();

        $result_pending = $stmt_pending->get_result();

        $pending = $result_pending->fetch_assoc();

        $total_pending = (int)($pending['total_pending'] ?? 0);

        $stmt_pending->close();
    }
}

// --------------------------------------------------
// Functions
// --------------------------------------------------
function h(?string $value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function adminTableExists(mysqli $conn, string $table): bool
{
    $safeTable = $conn->real_escape_string($table);

    $result = $conn->query("
        SHOW TABLES LIKE '{$safeTable}'
    ");

    $exists = (
        $result instanceof mysqli_result &&
        $result->num_rows > 0
    );

    if ($result instanceof mysqli_result) {
        $result->close();
    }

    return $exists;
}

// --------------------------------------------------
// Layout Start
// --------------------------------------------------
function adminPageStart(string $title): void
{
    global $total_pending, $adminPageExtraHead;

    $currentPage = basename($_SERVER['PHP_SELF']);

?>

    <!DOCTYPE html>
    <html lang="th">

    <head>

        <meta charset="UTF-8">

        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <title><?= h($title) ?> | Mango Admin</title>

        <!-- Boxicons -->
        <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">

        <!-- Font Awesome -->
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">

        <!-- Google Font -->
        <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600;700&display=swap" rel="stylesheet">

        <?php if (!empty($adminPageExtraHead)) {
            echo $adminPageExtraHead;
        } ?>

        <style>
            :root {

                /* =========================
               PROFESSIONAL COLOR SYSTEM
            ========================= */

                --green: #016A70;
                --green-dark: #01545a;
                --green-light: #0d8a92;
                --green-soft: rgba(1, 106, 112, 0.08);

                --white: #ffffff;
                --bg: #f4f8f9;
                --bg-soft: #f8fafc;

                --text: #0f172a;
                --text-soft: #64748b;

                --border: #e2e8f0;

                --danger: #ef4444;

                --shadow-sm: 0 4px 12px rgba(15, 23, 42, 0.04);
                --shadow-md: 0 12px 30px rgba(15, 23, 42, 0.08);
                --shadow-lg: 0 20px 45px rgba(15, 23, 42, 0.10);

                --radius-lg: 28px;
                --radius-md: 20px;
                --radius-sm: 14px;

                --sidebar-width: 280px;
            }

            * {
                margin: 0;
                padding: 0;
                box-sizing: border-box;
            }

            html {
                scroll-behavior: smooth;
            }

            body {

                font-family: 'Kanit', sans-serif;

                background: linear-gradient(180deg, #ffffff 0%, #f7fbfb 100%);

                color: var(--text);

                overflow-x: hidden;
            }

            /* =====================================================
           SIDEBAR
            ===================================================== */

            .modern-sidebar {

                position: fixed;

                top: 0;
                left: 0;

                width: var(--sidebar-width);
                height: 100vh;

                background: var(--white);

                border-right: 1px solid var(--border);

                z-index: 999;

                display: flex;
                flex-direction: column;

                padding: 22px 16px;

                overflow-y: auto;

                transition: .35s ease;

                box-shadow: var(--shadow-md);

                animation: sidebarFade .6s ease;
            }

            @keyframes sidebarFade {

                from {
                    opacity: 0;
                    transform: translateX(-25px);
                }

                to {
                    opacity: 1;
                    transform: translateX(0);
                }
            }

            .logo-img {

                text-align: center;

                margin-bottom: 34px;
            }

            .logo-img img {

                width: 145px;

                transition: .35s ease;
            }

            .logo-img img:hover {

                transform: scale(1.04);
            }

            /* =====================================================
           NAVIGATION
         ===================================================== */

            .nav {

                display: flex;

                flex-direction: column;

                gap: 10px;

                flex: 1;
            }

            .nav-link {

                position: relative;

                display: flex;

                align-items: center;

                gap: 14px;

                padding: 15px 18px;

                border-radius: 18px;

                color: var(--text-soft);

                text-decoration: none;

                font-size: 15px;

                font-weight: 500;

                transition: .25s ease;

                overflow: hidden;
            }

            .nav-link i {

                font-size: 1.35rem;

                transition: .25s ease;
            }

            .nav-link::before {

                content: '';

                position: absolute;

                inset: 0;

                background: linear-gradient(135deg,
                        rgba(1, 106, 112, .08),
                        rgba(1, 106, 112, .03));

                opacity: 0;

                transition: .25s ease;

                z-index: -1;
            }

            .nav-link:hover::before,
            .nav-link.active::before {

                opacity: 1;
            }

            .nav-link:hover {

                color: var(--green);

                transform: translateX(4px);
            }

            .nav-link:hover i {

                color: var(--green);

                transform: scale(1.1);
            }

            .nav-link.active {

                background: var(--green);

                color: var(--white);

                box-shadow:
                    0 10px 24px rgba(1, 106, 112, .18);
            }

            .nav-link.active i {

                color: var(--white);
            }

            /* =====================================================
           NOTIFICATION DOT
            ===================================================== */

            .notification-dot {

                position: absolute;

                top: 10px;
                right: 12px;

                min-width: 22px;
                height: 22px;

                padding: 0 6px;

                border-radius: 999px;

                background: var(--danger);

                color: white;

                display: flex;

                align-items: center;

                justify-content: center;

                font-size: 11px;

                font-weight: 700;

                animation: pulse 1.6s infinite;
            }

            @keyframes pulse {

                0% {
                    transform: scale(1);
                    box-shadow: 0 0 0 0 rgba(239, 68, 68, .45);
                }

                70% {
                    transform: scale(1.12);
                    box-shadow: 0 0 0 12px rgba(239, 68, 68, 0);
                }

                100% {
                    transform: scale(1);
                    box-shadow: 0 0 0 0 rgba(239, 68, 68, 0);
                }
            }

            /* =====================================================
           MAIN CONTENT
            ===================================================== */

            .main-content {

                margin-left: var(--sidebar-width);

                min-height: 100vh;

                background: var(--bg);

                transition: .35s ease;
            }

            /* =====================================================
           TOPBAR
            ===================================================== */

            .topbar {

                position: sticky;

                top: 0;

                z-index: 100;

                padding: 20px 30px;

                display: flex;

                justify-content: space-between;

                align-items: center;

                background: rgba(255, 255, 255, .92);

                backdrop-filter: blur(14px);

                border-bottom: 1px solid var(--border);

                animation: topbarFade .5s ease;
            }

            @keyframes topbarFade {

                from {
                    opacity: 0;
                    transform: translateY(-12px);
                }

                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }

            .page-title {

                font-size: 1.35rem;

                font-weight: 700;

                color: var(--text);
            }

            .page-date {

                color: var(--text-soft);

                font-size: 14px;

                font-weight: 500;
            }

            .admin-topbar {

                position: sticky;

                top: 0;

                z-index: 20;

                display: flex;

                align-items: center;

                justify-content: space-between;

                gap: 16px;

                padding: 16px 30px;

                background: rgba(255, 255, 255, .92);

                backdrop-filter: blur(14px);

                border-bottom: 1px solid var(--border);

                animation: topbarFade .5s ease;
            }

            .admin-topbar-left {

                display: flex;

                align-items: center;

                gap: 12px;

                min-width: 0;
            }

            .admin-topbar-title {

                margin: 0;

                color: var(--text-soft);

                font-size: .82rem;

                font-weight: 700;

                letter-spacing: .18em;

                text-transform: uppercase;

                white-space: nowrap;

                overflow: hidden;

                text-overflow: ellipsis;
            }

            .admin-topbar-right {

                display: flex;

                align-items: center;

                justify-content: flex-end;

                flex-wrap: wrap;

                gap: 12px 16px;

                color: #94a3b8;

                font-size: .78rem;

                font-weight: 500;
            }

            .admin-topbar-link {

                display: inline-flex;

                align-items: center;

                gap: 6px;

                padding: 8px 12px;

                border-radius: 12px;

                background: #f1f5f9;

                color: var(--green);

                text-decoration: none;

                transition: .2s ease;
            }

            .admin-topbar-link:hover {

                background: #ecfdf5;
            }

            .admin-topbar-date {

                display: inline-flex;

                align-items: center;

                gap: 6px;
            }

            /* =====================================================
           PAGE CONTENT
            ===================================================== */

            .page-content {

                padding: 30px;

                background: transparent;

                animation: contentFade .5s ease;
            }

            @keyframes contentFade {

                from {
                    opacity: 0;
                    transform: translateY(20px);
                }

                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }

            /* =====================================================
           MOBILE BUTTON
            ===================================================== */

            .mobile-toggle {

                display: none;

                width: 46px;
                height: 46px;

                border: none;

                border-radius: 14px;

                background: var(--green);

                color: white;

                cursor: pointer;

                transition: .25s ease;

                box-shadow:
                    0 10px 20px rgba(1, 106, 112, .18);
            }

            .mobile-toggle:hover {

                transform: translateY(-2px);
            }

            .mobile-toggle i {

                font-size: 1.5rem;
            }

            /* =====================================================
           OVERLAY
            ===================================================== */

            .sidebar-overlay {

                position: fixed;

                inset: 0;

                background: rgba(15, 23, 42, .45);

                backdrop-filter: blur(4px);

                opacity: 0;

                visibility: hidden;

                transition: .3s ease;

                z-index: 998;
            }

            .sidebar-overlay.active {

                opacity: 1;

                visibility: visible;
            }

            /* =====================================================
           SCROLLBAR
            ===================================================== */

            ::-webkit-scrollbar {

                width: 8px;
            }

            ::-webkit-scrollbar-thumb {

                background: #d1d5db;

                border-radius: 999px;
            }

            ::-webkit-scrollbar-thumb:hover {

                background: #9ca3af;
            }

            /* =====================================================
           RESPONSIVE
            ===================================================== */

            @media(max-width:1024px) {

                .modern-sidebar {

                    transform: translateX(-100%);
                }

                .modern-sidebar.active {

                    transform: translateX(0);
                }

                .main-content {

                    margin-left: 0;
                }

                .mobile-toggle {

                    display: flex;

                    align-items: center;

                    justify-content: center;
                }
            }

            @media(max-width:640px) {

                .modern-sidebar {

                    width: 85%;
                }

                .topbar {

                    padding: 16px;
                }

                .admin-topbar {

                    padding: 16px 20px;
                }

                .page-content {

                    padding: 20px;
                }

                .page-title {

                    font-size: 1.1rem;
                }
            }

            /* Animation กระดิ่ง */
            @keyframes bellRing {
                0% {
                    transform: rotate(0);
                }

                20% {
                    transform: rotate(15deg);
                }

                40% {
                    transform: rotate(-10deg);
                }

                60% {
                    transform: rotate(5deg);
                }

                80% {
                    transform: rotate(-5deg);
                }

                100% {
                    transform: rotate(0);
                }
            }

            .bell-ring {
                animation: bellRing 0.6s ease-in-out;
            }

            /* Badge pulse */
            @keyframes badgePulse {
                0% {
                    transform: scale(1);
                }

                50% {
                    transform: scale(1.2);
                }

                100% {
                    transform: scale(1);
                }
            }

            .badge-pulse {
                animation: badgePulse 0.4s ease-in-out;
            }

            .notification-bell-btn {
                position: relative;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                width: 46px;
                height: 46px;
                border: 1px solid rgba(226, 232, 240, 0.95);
                border-radius: 16px;
                background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
                color: #475569;
                box-shadow: 0 10px 24px rgba(15, 23, 42, 0.06);
            }

            .notification-bell-btn:hover {
                background: #f8fafc;
                color: var(--green);
                transform: translateY(-1px);
            }

            .notification-bell-btn i {
                font-size: 1.05rem;
            }

            .notification-dropdown-menu {
                position: absolute;
                top: calc(100% + 12px);
                right: 0;
                width: min(360px, calc(100vw - 32px));
                border: 1px solid rgba(226, 232, 240, 0.96);
                border-radius: 22px;
                background: rgba(255, 255, 255, 0.98);
                box-shadow: 0 28px 60px rgba(15, 23, 42, 0.18);
                overflow: hidden;
                z-index: 50;
                backdrop-filter: blur(16px);
                animation: dropdownFade 0.2s ease-out;
                transform-origin: top right;
            }

            .notification-dropdown-header {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 12px;
                padding: 16px 18px 14px;
                background: linear-gradient(180deg, #fbfefd 0%, #f4fbfa 100%);
                border-bottom: 1px solid #eef2f7;
            }

            .notification-dropdown-title {
                display: flex;
                align-items: center;
                gap: 10px;
                margin: 0;
                font-size: 0.95rem;
                font-weight: 700;
                color: #1f2937;
            }

            .notification-dropdown-title-icon {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                width: 34px;
                height: 34px;
                border-radius: 12px;
                background: rgba(1, 106, 112, 0.1);
                color: var(--green);
            }

            .notification-dropdown-caption {
                font-size: 0.72rem;
                font-weight: 700;
                letter-spacing: 0.08em;
                text-transform: uppercase;
                color: #94a3b8;
            }

            .notification-dropdown-body {
                max-height: 360px;
                overflow-y: auto;
                padding: 8px;
                background: #ffffff;
            }

            .notification-empty {
                display: grid;
                place-items: center;
                gap: 10px;
                padding: 32px 18px;
                text-align: center;
                color: #94a3b8;
            }

            .notification-empty-icon {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                width: 56px;
                height: 56px;
                border-radius: 18px;
                background: linear-gradient(180deg, #f8fafc 0%, #eef6f5 100%);
                color: var(--green);
                font-size: 1.35rem;
                box-shadow: inset 0 0 0 1px rgba(1, 106, 112, 0.08);
            }

            .notification-empty-title {
                font-size: 0.95rem;
                font-weight: 700;
                color: #334155;
            }

            .notification-empty-text {
                max-width: 220px;
                font-size: 0.8rem;
                line-height: 1.6;
            }

            .notification-item {
                display: block;
                padding: 14px;
                border: 1px solid transparent;
                border-radius: 16px;
                text-decoration: none;
                transition: all 0.2s ease;
            }

            .notification-item:hover {
                background: linear-gradient(180deg, #f8fffd 0%, #effaf7 100%);
                border-color: rgba(1, 106, 112, 0.12);
                transform: translateY(-1px);
            }

            .notification-item + .notification-item {
                margin-top: 8px;
            }

            .notification-item-row {
                display: flex;
                align-items: flex-start;
                gap: 12px;
            }

            .notification-item-icon {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                width: 40px;
                height: 40px;
                border-radius: 14px;
                background: linear-gradient(180deg, #ecfdf5 0%, #dcfce7 100%);
                color: #0f766e;
                flex-shrink: 0;
            }

            .notification-item-content {
                flex: 1;
                min-width: 0;
            }

            .notification-item-title {
                margin: 0;
                color: #0f172a;
                font-size: 0.9rem;
                font-weight: 600;
                line-height: 1.4;
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
            }

            .notification-item-subtitle {
                margin-top: 4px;
                color: #64748b;
                font-size: 0.78rem;
                line-height: 1.5;
            }

            .notification-item-time {
                display: inline-flex;
                align-items: center;
                gap: 6px;
                margin-top: 8px;
                color: #0f766e;
                font-size: 0.76rem;
                font-weight: 600;
            }

            .notification-item-status {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                width: 10px;
                height: 10px;
                margin-top: 6px;
                border-radius: 999px;
                background: #22c55e;
                box-shadow: 0 0 0 4px rgba(34, 197, 94, 0.12);
                flex-shrink: 0;
            }

            .notification-dropdown-footer {
                padding: 10px;
                background: linear-gradient(180deg, #fbfefd 0%, #f8fafc 100%);
                border-top: 1px solid #eef2f7;
            }

            .notification-dropdown-link {
                display: flex;
                align-items: center;
                justify-content: center;
                gap: 8px;
                width: 100%;
                padding: 11px 14px;
                border-radius: 14px;
                background: rgba(1, 106, 112, 0.08);
                color: var(--green);
                font-size: 0.82rem;
                font-weight: 700;
                text-decoration: none;
                transition: 0.2s ease;
            }

            .notification-dropdown-link:hover {
                background: rgba(1, 106, 112, 0.14);
            }

            @keyframes dropdownFade {
                from {
                    opacity: 0;
                    transform: translateY(-10px) scale(0.95);
                }

                to {
                    opacity: 1;
                    transform: translateY(0) scale(1);
                }
            }
        </style>

    </head>

    <body>

        <!-- Overlay -->
        <div class="sidebar-overlay" id="sidebarOverlay"></div>

        <!-- Sidebar -->
        <aside class="modern-sidebar" id="adminSidebar">

            <div class="logo-img">
                <a href="./index.php">
                    <img src="../user/image/logo-3.png" alt="Logo">
                </a>
            </div>

            <nav class="nav">

                <a href="./index.php"
                    class="nav-link <?= $currentPage === 'index.php' ? 'active' : '' ?>">

                    <i class='bx bxs-dashboard'></i>
                    Dashboard

                </a>

                <a href="./booking_list.php"
                    class="nav-link <?= $currentPage === 'booking_list.php' ? 'active' : '' ?>">

                    <i class='bx bxs-calendar-check'></i>
                    รายการจอง

                </a>

                <a href="./manage_orders.php"
                    class="nav-link <?= $currentPage === 'manage_orders.php' ? 'active' : '' ?>">

                    <i class='bx bxs-package'></i>
                    จัดการคำสั่งซื้อ

                    <?php if ($total_pending > 0): ?>

                        <span class="notification-dot">
                            <?= (int)$total_pending ?>
                        </span>

                    <?php endif; ?>

                </a>

                <a href="./manage_product.php"
                    class="nav-link <?= $currentPage === 'manage_product.php' ? 'active' : '' ?>">

                    <i class='bx bxs-shopping-bag'></i>
                    สินค้า

                </a>

                <a href="./edit_courses.php"
                    class="nav-link <?= $currentPage === 'edit_courses.php' ? 'active' : '' ?>">

                    <i class='bx bxs-graduation'></i>
                    กิจกรรมอบรม

                </a>

                <a href="./admin_users.php"
                    class="nav-link <?= $currentPage === 'admin_users.php' ? 'active' : '' ?>">

                    <i class='bx bxs-user-account'></i>
                    จัดการผู้ใช้

                </a>

                <a href="./add_admin.php"
                    class="nav-link <?= $currentPage === 'add_admin.php' ? 'active' : '' ?>">

                    <i class='bx bxs-user-plus'></i>
                    เพิ่มแอดมิน

                </a>

                <a href="logout.php"
                    class="nav-link"
                    style="margin-top:auto; color:#ef4444;">

                    <i class='bx bx-log-out-circle'></i>
                    ออกจากระบบ

                </a>

            </nav>

        </aside>

        <!-- Main -->
        <main class="main-content">

            <!-- Header -->
            <header class="admin-topbar bg-white border-b border-slate-200 px-4 sm:px-6 lg:px-8 py-4 sm:py-5 flex items-center justify-between gap-4 sticky top-0 z-20">
                <div class="admin-topbar-left flex items-center gap-3 min-w-0">
                    <button
                        type="button"
                        id="adminSidebarToggle"
                        class="mobile-toggle inline-flex h-11 w-11 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-700 shadow-sm hover:bg-slate-50 lg:hidden"
                        aria-label="Open sidebar"
                        aria-controls="adminSidebar"
                        aria-expanded="false">
                        <i class="fas fa-bars"></i>
                    </button>
                    <h1 class="admin-topbar-title text-sm font-bold text-slate-500 uppercase tracking-widest truncate"><?= h($title) ?></h1>
                </div>

                <div class="admin-topbar-right text-xs text-slate-400 font-medium flex items-center gap-3 sm:gap-4 flex-wrap justify-end">

                    <!-- ========== BELL NOTIFICATION ========== -->
                    <div class="notification-dropdown" style="position: relative;">
                        <button
                            id="notificationBell"
                            class="notification-bell-btn relative p-2 rounded-xl hover:bg-slate-100 transition-all duration-200"
                            style="background: transparent; border: none; cursor: pointer;"
                            aria-label="การแจ้งเตือน">
                            <i class="fas fa-bell text-xl text-slate-600"></i>
                            <span
                                id="notificationBadge"
                                class="notification-badge absolute -top-1 -right-1 min-w-[18px] h-[18px] px-1 rounded-full bg-red-500 text-white text-[10px] font-bold flex items-center justify-center"
                                style="display: none; box-shadow: 0 0 0 2px white;">
                                0
                            </span>
                        </button>

                        <!-- Dropdown แสดงรายการแจ้งเตือน -->
                        <div
                            id="notificationDropdown"
                            class="notification-dropdown-menu"
                            style="display: none;">
                            <div class="notification-dropdown-header">
                                <div>
                                    <h3 class="notification-dropdown-title">
                                        <span class="notification-dropdown-title-icon">
                                            <i class="fas fa-bell"></i>
                                        </span>
                                    การแจ้งเตือน
                                    </h3>
                                </div>
                                <span class="notification-dropdown-caption">Booking updates</span>
                            </div>
                            <div id="notificationList" class="notification-dropdown-body">
                                <div class="notification-empty">
                                    <span class="notification-empty-icon">
                                        <i class="fas fa-inbox"></i>
                                    </span>
                                    ไม่มีการแจ้งเตือนใหม่
                                </div>
                            </div>
                            <div class="notification-dropdown-footer">
                                <a href="./booking_list.php" class="notification-dropdown-link">
                                    ดูการจองทั้งหมด →
                                </a>
                            </div>
                        </div>
                    </div>

                    <span class="admin-topbar-date"><i class="far fa-calendar-alt"></i> <?= date('d M Y') ?></span>
                    <a href="../user/index.php" target="_blank" class="admin-topbar-link px-3 py-1 bg-slate-100 rounded-lg text-green-600 hover:bg-green-50 transition-colors">
                        <i class="fas fa-external-link-alt"></i> ดูหน้าเว็บ
                    </a>
                </div>
            </header>

            <!-- Content -->
            <div class="page-content">

            <?php
        }

        // --------------------------------------------------
        // Layout End
        // --------------------------------------------------
        function adminPageEnd(): void
        {
            ?>

            </div>

        </main>

        <script>
            const sidebar = document.getElementById('adminSidebar');
            const overlay = document.getElementById('sidebarOverlay');
            const toggleBtn = document.getElementById('adminSidebarToggle');

            function toggleSidebar() {

                sidebar.classList.toggle('active');
                overlay.classList.toggle('active');

                if (toggleBtn) {
                    toggleBtn.setAttribute('aria-expanded', sidebar.classList.contains('active') ? 'true' : 'false');
                }

            }

            if (toggleBtn) {
                toggleBtn.addEventListener('click', toggleSidebar);
            }

            if (overlay) {
                overlay.addEventListener('click', toggleSidebar);
            }

            // Smooth Active Hover Animation
            const navLinks = document.querySelectorAll('.nav-link');

            navLinks.forEach(link => {

                link.addEventListener('mouseenter', () => {
                    link.style.transition = '.25s ease';
                });

            });
        </script>

        <!-- เพิ่ม JavaScript สำหรับระบบแจ้งเตือน -->
        <script>
            // Notification System
            class NotificationManager {
                constructor() {
                    this.lastCheck = localStorage.getItem('lastBookingCheck') || Math.floor(Date.now() / 1000);
                    this.currentCount = 0;
                    this.checkInterval = null;
                    this.isDropdownOpen = false;
                    this.audio = null;

                    this.init();
                }

                init() {
                    this.cacheElements();
                    this.bindEvents();
                    this.startPolling();
                    this.setupAudio();
                }

                cacheElements() {
                    this.bellBtn = document.getElementById('notificationBell');
                    this.badge = document.getElementById('notificationBadge');
                    this.dropdown = document.getElementById('notificationDropdown');
                    this.notificationList = document.getElementById('notificationList');
                }

                bindEvents() {
                    // คลิกที่กระดิ่ง
                    if (this.bellBtn) {
                        this.bellBtn.addEventListener('click', (e) => {
                            e.stopPropagation();
                            this.toggleDropdown();
                            if (this.currentCount > 0) {
                                this.markAsRead();
                            }
                        });
                    }

                    // คลิกนอก dropdown
                    document.addEventListener('click', (e) => {
                        if (this.dropdown && !this.dropdown.contains(e.target) &&
                            this.bellBtn && !this.bellBtn.contains(e.target)) {
                            this.closeDropdown();
                        }
                    });

                    // Keyboard shortcut: Alt + N
                    document.addEventListener('keydown', (e) => {
                        if (e.altKey && e.key === 'n') {
                            e.preventDefault();
                            this.toggleDropdown();
                        }
                    });
                }

                setupAudio() {
                    // เล่นเสียงเมื่อมีการจองใหม่ (optional)
                    try {
                        this.audio = new Audio('https://www.soundjay.com/misc/sounds/bell-ringing-05.mp3');
                        this.audio.volume = 0.3;
                    } catch (e) {
                        console.log('Audio not supported');
                    }
                }

                startPolling() {
                    // ตรวจสอบทุก 10 วินาที
                    this.checkInterval = setInterval(() => {
                        this.checkNewBookings();
                    }, 10000);

                    // ตรวจสอบทันที
                    this.checkNewBookings();
                }

                async checkNewBookings() {
                    try {
                        const response = await fetch(`get_new_bookings.php?last_check=${this.lastCheck}&t=${Date.now()}`, {
                            cache: 'no-store'
                        });
                        const data = await response.json();

                        if (data.success) {
                            const newCount = Number.parseInt(data.new_count ?? data.count ?? 0, 10) || 0;

                            if (newCount > this.currentCount) {
                                // มีการจองใหม่!
                                const diff = newCount - this.currentCount;
                                this.showNewBookingNotification(diff, data.recent_bookings);

                                // เล่นเสียง
                                if (this.audio) {
                                    this.audio.play().catch(e => console.log('Audio play blocked'));
                                }

                                // กระดิ่งสั่น
                                this.ringBell();
                            }

                            this.currentCount = newCount;
                            this.updateBadge(newCount);

                            if (Array.isArray(data.recent_bookings) && data.recent_bookings.length > 0) {
                                this.updateNotificationList(data.recent_bookings);
                            } else {
                                this.updateNotificationList([]);
                            }

                            // อัปเดตเวลาเช็คล่าสุด
                            this.lastCheck = data.current_time;
                            localStorage.setItem('lastBookingCheck', this.lastCheck);
                        }
                    } catch (error) {
                        console.error('Error checking bookings:', error);
                    }
                }

                updateBadge(count) {
                    if (!this.badge) return;

                    if (count > 0) {
                        this.badge.style.display = 'flex';
                        this.badge.textContent = count > 99 ? '99+' : count;
                    } else {
                        this.badge.style.display = 'none';
                    }
                }

                ringBell() {
                    if (!this.bellBtn) return;

                    const bellIcon = this.bellBtn.querySelector('i');
                    if (bellIcon) {
                        bellIcon.classList.add('bell-ring');
                        setTimeout(() => {
                            bellIcon.classList.remove('bell-ring');
                        }, 600);
                    }

                    // Badge pulse
                    if (this.badge) {
                        this.badge.classList.add('badge-pulse');
                        setTimeout(() => {
                            this.badge.classList.remove('badge-pulse');
                        }, 400);
                    }
                }

                showNewBookingNotification(count, recentBookings) {
                    // แสดง Toast Notification (ถ้า浏览器支持)
                    if ('Notification' in window && Notification.permission === 'granted') {
                        const title = `มีการจองใหม่ ${count} รายการ`;
                        const body = recentBookings && recentBookings[0] ?
                            `${recentBookings[0].customer_name} - ${recentBookings[0].time_ago}` :
                            'คลิกเพื่อดูรายละเอียด';
                        new Notification(title, {
                            body,
                            icon: '../user/image/logo-3.png'
                        });
                    } else if ('Notification' in window && Notification.permission !== 'denied') {
                        Notification.requestPermission();
                    }

                    // แสดงข้อความแจ้งเตือนแบบสั้นๆ (Snackbar)
                    this.showSnackbar(`มีการจองใหม่ ${count} รายการ`, 'info');
                }

                showSnackbar(message, type = 'info') {
                    // สร้าง snackbar element
                    let snackbar = document.getElementById('globalSnackbar');
                    if (!snackbar) {
                        snackbar = document.createElement('div');
                        snackbar.id = 'globalSnackbar';
                        snackbar.style.cssText = `
                position: fixed;
                bottom: 20px;
                right: 20px;
                padding: 12px 20px;
                background: #1f2937;
                color: white;
                border-radius: 12px;
                font-size: 14px;
                z-index: 9999;
                box-shadow: 0 10px 25px rgba(0,0,0,0.1);
                transform: translateX(400px);
                transition: transform 0.3s ease;
                display: flex;
                align-items: center;
                gap: 10px;
            `;
                        document.body.appendChild(snackbar);
                    }

                    const icon = type === 'info' ? '🔔' : '⚠️';
                    snackbar.innerHTML = `${icon} ${message}`;
                    snackbar.style.transform = 'translateX(0)';

                    setTimeout(() => {
                        snackbar.style.transform = 'translateX(400px)';
                    }, 3000);
                }

                toggleDropdown() {
                    this.isDropdownOpen = !this.isDropdownOpen;
                    this.dropdown.style.display = this.isDropdownOpen ? 'block' : 'none';
                }

                closeDropdown() {
                    this.isDropdownOpen = false;
                    if (this.dropdown) {
                        this.dropdown.style.display = 'none';
                    }
                }

                markAsRead() {
                    // ส่ง request เพื่อ mark as read
                    localStorage.setItem('lastBookingCheck', String(this.lastCheck));
                }

                updateNotificationList(bookings) {
                    if (!this.notificationList) return;

                    if (!bookings || bookings.length === 0) {
                        this.notificationList.innerHTML = `
                <div class="notification-empty">
                    <span class="notification-empty-icon">
                        <i class="fas fa-inbox"></i>
                    </span>
                    ไม่มีการแจ้งเตือนใหม่
                </div>
            `;
                        return;
                    }

                    let html = '';
                    bookings.forEach(booking => {
                        html += `
                <a href="booking_list.php?highlight=${booking.id}" 
                   class="notification-item">
                    <div class="notification-item-row">
                        <div class="notification-item-icon">
                            <i class="fas fa-calendar-check"></i>
                        </div>
                        <div class="notification-item-content">
                            <p class="notification-item-title">
                                ${escapeHtml(booking.customer_name)}
                            </p>
                            <p class="notification-item-subtitle">
                                ${escapeHtml(booking.booking_date || 'รอ确认')}
                            </p>
                            <p class="notification-item-time">
                                <i class="far fa-clock"></i> ${booking.time_ago}
                            </p>
                        </div>
                        <span class="notification-item-status"></span>
                    </div>
                </a>
            `;
                    });

                    this.notificationList.innerHTML = html;
                }
            }

            // Helper function
            function escapeHtml(text) {
                if (!text) return '';
                const div = document.createElement('div');
                div.textContent = text;
                return div.innerHTML;
            }

            // Initialize เมื่อโหลดเสร็จ
            document.addEventListener('DOMContentLoaded', () => {
                window.notificationManager = new NotificationManager();

                // Update notification badge and list without reloading the page.
                // The booking count and recent notifications are refreshed every 10 seconds by checkNewBookings().
                if (window.location.pathname.includes('booking_list.php')) {
                    console.log('Booking list notifications are polling without page reload.');
                }
            });
        </script>

    </body>

    </html>

<?php
        }
?>
