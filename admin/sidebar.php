<link rel="stylesheet" href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css">
<link href="https://fonts.googleapis.com/css2?family=Kanit:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Prompt:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&display=swap" rel="stylesheet">
<!-- <link rel="stylesheet" href="../css/style.css"> -->
<style>
    :root {
        --sidebar-bg: #232946;
        --sidebar-active: #4e73df;
        --sidebar-hover: #eebbc3;
        --sidebar-text: #fff;
        --sidebar-icon: #a1a1aa;
        --sidebar-radius: 18px;
    }
    .modern-sidebar {
        font-family: "Kanit", sans-serif;
        background: var(--sidebar-bg);
        color: var(--sidebar-text);
        width: 240px;
        min-height: 100vh;
        position: fixed;
        left: 0;
        top: 0;
        z-index: 100;
        box-shadow: 2px 0 16px rgba(0,0,0,0.07);
        /* border-top-right-radius: var(--sidebar-radius);
        border-bottom-right-radius: var(--sidebar-radius); */
        display: flex;
        flex-direction: column;
        padding: 32px 0 16px 0;
    }
    .modern-sidebar .sidebar-logo {
        font-size: 1.6rem;
        font-weight: bold;
        letter-spacing: 1px;
        color: var(--sidebar-active);
        text-align: center;
        margin-bottom: 32px;
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
        color: var(--sidebar-text);
        font-size: 1.08rem;
        font-weight: 500;
        text-decoration: none;
        transition: background 0.2s, color 0.2s;
    }
    .modern-sidebar .nav-link i {
        font-size: 1.3rem;
        color: var(--sidebar-icon);
        transition: color 0.2s;
    }
    .modern-sidebar .nav-link:hover, .modern-sidebar .nav-link.active {
        background: var(--sidebar-active);
        color: #fff !important;
    }
    .modern-sidebar .nav-link:hover i, .modern-sidebar .nav-link.active i {
        color: #fff;
    }
    .modern-sidebar .sidebar-footer {
        text-align: center;
        font-size: 0.95rem;
        color: #bcbcbc;
        margin-top: 24px;
        padding-bottom: 8px;
    }
</style>
<div class="modern-sidebar">
    <div class="sidebar-logo">
        <i class='bx bxs-leaf'></i> Mango Admin
    </div>
    <nav class="nav flex-column">
        <a href="./index.php" class="nav-link<?php if(basename($_SERVER['PHP_SELF'])=='index.php') echo ' active'; ?>">
            <i class='bx bxs-dashboard'></i> Dashboard
        </a>
        <a href="./manage_mango.php" class="nav-link<?php if(basename($_SERVER['PHP_SELF'])=='manage_mango.php') echo ' active'; ?>">
            <i class='bx bx-detail'></i> สายพันธุ์มะม่วง
        </a>
        <a href="./manage_product.php" class="nav-link<?php if(basename($_SERVER['PHP_SELF'])=='manage_product.php') echo ' active'; ?>">
            <i class='bx bx-package'></i> สินค้าผลิตภัณฑ์
        </a>
        <a href="./edit_courses.php" class="nav-link<?php if(basename($_SERVER['PHP_SELF'])=='edit_courses.php') echo ' active'; ?>">
            <i class='bx bx-book'></i> หลักสูตร
        </a>
        <a href="./booking_list.php" class="nav-link<?php if(basename($_SERVER['PHP_SELF'])=='booking_list.php') echo ' active'; ?>">
            <i class='bx bxs-calendar'></i> ตารางรายการจอง
        </a>
        <a href="./update_calendar_view.php" class="nav-link<?php if(basename($_SERVER['PHP_SELF'])=='update_calendar_view.php') echo ' active'; ?>">
            <i class='bx bxs-calendar'></i> อัพเดทปฏิทิน
        </a>
        <a href="logout.php" class="nav-link mt-5">
            <i class='bx bx-log-out'></i> ออกจากระบบ
        </a>
    </nav>
    <div class="sidebar-footer">
        &copy; <?= date('Y') ?> Mango Admin
    </div>
</div>