<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

$awaiting_qr_payment_count = 0;

if (isset($_SESSION['member_id'])) {
  if (!isset($conn) || !($conn instanceof mysqli)) {
    require_once __DIR__ . '/../db/db.php';
  }

  if (isset($conn) && $conn instanceof mysqli) {
    $member_id = (int) $_SESSION['member_id'];
    $stmt_qr_notice = $conn->prepare("
      SELECT COUNT(*) AS total
      FROM bookings
      WHERE member_id = ?
        AND status = 'awaiting_payment'
        AND (payment_slip IS NULL OR payment_slip = '')
    ");

    if ($stmt_qr_notice) {
      $stmt_qr_notice->bind_param("i", $member_id);
      $stmt_qr_notice->execute();
      $result_qr_notice = $stmt_qr_notice->get_result();
      if ($row_qr_notice = $result_qr_notice->fetch_assoc()) {
        $awaiting_qr_payment_count = (int) ($row_qr_notice['total'] ?? 0);
      }
      $stmt_qr_notice->close();
    }
  }
}
?>
<!DOCTYPE html>
<html lang="th">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>สวนลุงเผือก | Navbar</title>

  <!-- Bootstrap 5 -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Google Font -->
  <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;600;700&display=swap" rel="stylesheet">
  <!-- Boxicons -->
  <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
  <!-- โหลด Remix Icon -->
  <link href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" rel="stylesheet">

  <style>
    :root {
      --green: #016A70;
      --green-light: #32a877;
      --white: #fff;
      --dark: #222;
      --shadow: rgba(0, 0, 0, 0.08);
    }

    * {
      font-family: 'Prompt', sans-serif;
      box-sizing: border-box;
    }

    /* Logo Classes */
    .navbar-brand {
      display: inline-flex;
      align-items: center;
      flex-shrink: 0;
      padding: 0;
      margin-right: 1.5rem;
    }

    .logo {
      display: block;
      object-fit: contain;
      object-position: center;
      transition: transform 0.3s ease, filter 0.3s ease;
    }

    .logo:hover {
      transform: scale(1.05);
      filter: drop-shadow(0 4px 10px rgba(1, 106, 112, 0.16));
    }

    .logo--navbar {
      width: clamp(145px, 13vw, 190px);
      height: 58px;
      max-width: 100%;
    }

    .logo--offcanvas {
      width: 220px;
      height: 95px;
      max-width: 72vw;
    }

    .navbar {
      min-height: 80px;
      box-shadow: 0 3px 8px var(--shadow);
      transition: background-color 0.4s ease;
      padding-top: 0.5rem;
      padding-bottom: 0.5rem;
      position: sticky;
      /* เปลี่ยนจาก fixed */
      top: 0;
      z-index: 1000;
      background-color: var(--white);
    }

    .navbar.scrolled {
      background-color: var(--white);
    }

    .navbar-nav .nav-link {
      color: #444;
      font-weight: 500;
      position: relative;
      margin-right: 1rem;
      transition: color 0.3s ease;
    }

    .navbar-nav .nav-link:hover {
      color: #000;
    }

    .navbar-nav .nav-link::after {
      content: "";
      position: absolute;
      bottom: -4px;
      left: 0;
      width: 0%;
      height: 3px;
      background: var(--green);
      transition: width 0.3s;
    }

    .navbar-nav .nav-link:hover::after {
      width: 100%;
    }

    .booking-alert-link {
      display: inline-flex;
      align-items: center;
      gap: 6px;
    }

    .booking-alert-badge {
      min-width: 18px;
      height: 18px;
      padding: 0 5px;
      border-radius: 999px;
      background: #dc3545;
      color: #fff;
      font-size: 0.7rem;
      font-weight: 700;
      line-height: 18px;
      text-align: center;
      box-shadow: 0 0 0 2px #fff;
    }

    .navbar-toggler.booking-menu-alert {
      display: inline-flex;
      flex-direction: column;
      align-items: center;
      gap: 1px;
      color: #dc3545 !important;
      animation: bookingMenuPulse 1s ease-in-out infinite;
    }

    .hamburger-alert-text {
      color: #dc3545;
      font-size: 0.68rem;
      font-weight: 700;
      line-height: 1.15;
      white-space: nowrap;
    }

    @keyframes bookingMenuPulse {

      0%,
      100% {
        transform: scale(1);
        filter: drop-shadow(0 0 0 rgba(220, 53, 69, 0));
      }

      50% {
        transform: scale(1.08);
        filter: drop-shadow(0 0 8px rgba(220, 53, 69, 0.55));
      }
    }

    .cta-btn {
      border-radius: 25px;
      padding: 0.5rem 1.5rem;
      background: var(--green);
      color: var(--white);
      font-weight: 600;
      text-decoration: none;
      border: 2px solid var(--green);
      transition: all 0.3s ease;
    }

    .cta-btns {
      border-radius: 25px;
      padding: 0.5rem 1.5rem;
      background: var(--white);
      color: var(--green);
      font-weight: 600;
      text-decoration: none;
      border: 2px solid var(--green);
      transition: all 0.3s ease;
    }

    .cta-btns:hover {
      background: var(--green);
      color: var(--white);
    }

    .logout-btn {
      background: #dc3545;
      border-color: #dc3545;
      color: #fff;
    }

    .logout-btn:hover {
      background: #bb2d3b;
      border-color: #b02a37;
      color: #fff;
    }

    /* Profile */
    .profile-link {
      display: flex;
      align-items: center;
      text-decoration: none;
      color: var(--green);
      font-weight: 600;
      transition: color 0.3s ease;
    }

    .profile-link:hover {
      color: #000;
      opacity: 0.8;
    }

    .profile-menu {
      position: relative;
      list-style: none;
    }

    .profile-menu summary {
      list-style: none;
      cursor: pointer;
      display: inline-flex;
      align-items: center;
      color: var(--green);
      font-weight: 600;
      user-select: none;
    }

    .profile-menu summary::-webkit-details-marker {
      display: none;
    }

    .profile-menu summary::after {
      content: "";
      display: inline-block;
      margin-left: 8px;
      vertical-align: middle;
      border-top: 0.35em solid;
      border-right: 0.35em solid transparent;
      border-bottom: 0;
      border-left: 0.35em solid transparent;
    }

    .profile-menu[open] summary::after {
      transform: rotate(180deg);
    }

    .profile-dropdown-menu {
      border: 1px solid rgba(1, 106, 112, 0.12);
      border-radius: 8px;
      box-shadow: 0 10px 30px rgba(15, 23, 42, 0.12);
      padding: 8px;
      min-width: 220px;
      display: block;
      position: absolute;
      top: calc(100% + 10px);
      right: 0;
      z-index: 5000;
      background: #fff;
    }

    .profile-dropdown-menu .dropdown-item {
      display: flex;
      align-items: center;
      gap: 10px;
      border-radius: 6px;
      padding: 10px 12px;
      font-weight: 500;
    }

    .profile-dropdown-menu .dropdown-item:hover {
      background: #f0fdfa;
      color: var(--green);
    }

    .profile-dropdown-menu .dropdown-item.text-danger:hover {
      background: #fff1f2;
      color: #dc3545 !important;
    }

    .profile-img-wrapper {
      width: 42px;
      height: 42px;
      border-radius: 50%;
      overflow: hidden;
      border: 2px solid var(--green);
      margin-right: 8px;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }

    .profile-img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      border-radius: 50%;
    }

    /* ปรับปรุง responsive สำหรับหน้าจอขนาดกลาง */
    @media (max-width: 1400px) {
      .navbar-nav .nav-link {
        margin-right: 0.75rem;
        font-size: 0.95rem;
      }
    }

    @media (max-width: 1200px) {
      .navbar-nav .nav-link {
        margin-right: 0.5rem;
        font-size: 0.9rem;
      }

      .cta-btn {
        padding: 0.4rem 1.2rem;
        font-size: 0.9rem;
      }

      .logo--navbar {
        width: 165px;
        height: 56px;
      }
    }

    @media (max-width: 576px) {
      .navbar {
        min-height: 72px;
      }

      .navbar-brand {
        margin-right: 0.75rem;
      }

      .logo--navbar {
        width: 145px;
        height: 50px;
      }

      .logo--offcanvas {
        width: 190px;
        height: 82px;
      }
    }

    @media (min-width: 1200px) {
      .navbar-expand-xl .navbar-toggler.booking-menu-alert {
        display: none;
      }
    }
  </style>
</head>

<body>

  <nav class="navbar navbar-expand-xl bg-white sticky-top">
    <div class="container">
      <a class="navbar-brand" href="../user/index.php">
        <img src="../user/image/logo-navbar.png" alt="สวนลุงเผือก" class="logo logo--navbar" width="190" height="58" decoding="async">
      </a>

      <button class="navbar-toggler text-dark border-0 <?= $awaiting_qr_payment_count > 0 ? 'booking-menu-alert' : '' ?>" type="button" data-bs-toggle="offcanvas" data-bs-target="#navMenu" aria-label="เปิดเมนูนำทาง">
        <i class='bx bx-menu' style="font-size:30px;"></i>
        <?php if ($awaiting_qr_payment_count > 0): ?>
        <?php endif; ?>
      </button>

      <div class="offcanvas offcanvas-end" tabindex="-1" id="navMenu">
        <div class="offcanvas-header">
          <img src="../user/image/logo-navbar.png" alt="สวนลุงเผือก" class="logo logo--offcanvas" width="220" height="95" decoding="async">
          <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
        </div>

        <div class="offcanvas-body">
          <ul class="navbar-nav ms-auto mb-2 mb-lg-0 align-items-center">
            <li class="nav-item"><a class="nav-link" href="index.php">หน้าแรก</a></li>
            <li class="nav-item">
              <a class="nav-link booking-alert-link" href="bookings.php">
                <span>จองคิวออนไลน์</span>
                <?php if ($awaiting_qr_payment_count > 0): ?>
                  <span class="booking-alert-badge" title="มี QR Code รอชำระเงิน">
                    <?= $awaiting_qr_payment_count > 9 ? '9+' : $awaiting_qr_payment_count ?>
                  </span>
                <?php endif; ?>
              </a>
            </li>
            <li class="nav-item"><a class="nav-link" href="course.php">กิจกรรมอบรม</a></li>
            <li class="nav-item"><a class="nav-link" href="products.php">สั่งซื้อสินค้า</a></li>


            <?php if (!isset($_SESSION['member_id'])): ?>
              <li class="nav-item mt-3 mt-xl-0">
                <a href="member_login.php" class="cta-btns ">เข้าสู่ระบบ</a>
                <a href="register.php" class="cta-btn ">สมัครสมาชิก</a>
              </li>
            <?php else: ?>
              
              <li class="nav-item mt-3 mt-xl-0">
                <details class="profile-menu" id="profileMenuDropdown">
                  <summary class="profile-link" aria-label="เปิดเมนูโปรไฟล์">
                    <div class="profile-img-wrapper">
                      <img src="image/profile.png" class="profile-img" alt="โปรไฟล์">
                    </div>
                    <span>โปรไฟล์</span>
                  </summary>
                  <ul class="dropdown-menu dropdown-menu-end profile-dropdown-menu">
                    <li>
                      <a class="dropdown-item" href="member_profile.php">
                        <i class='bx bx-user-circle'></i>
                        ดูโปรไฟล์ของฉัน
                      </a>
                    </li>
                    <li>
                      <a class="dropdown-item" href="member_profile.php?edit=1">
                        <i class='bx bx-edit'></i>
                        แก้ไขโปรไฟล์
                      </a>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                    <a class="dropdown-item text-danger js-logout-confirm" href="member_logout.php">
                      <i class='bx bx-log-out'></i>
                      ออกจากระบบ
                    </a>
                    </li>
                  </ul>
                </details>
              </li>
              
            <?php endif; ?>
          </ul>
        </div>
      </div>
    </div>
  </nav>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    // Navbar scroll effect
    window.addEventListener('scroll', () => {
      const navbar = document.querySelector('.navbar');
      if (window.scrollY > 80) {
        navbar.classList.add('scrolled');
      } else {
        navbar.classList.remove('scrolled');
      }
    });

    document.addEventListener('click', function(event) {
      document.querySelectorAll('.profile-menu[open]').forEach(menu => {
        if (!menu.contains(event.target)) {
          menu.removeAttribute('open');
        }
      });
    });

    document.addEventListener('keydown', function(event) {
      if (event.key !== 'Escape') return;
      document.querySelectorAll('.profile-menu[open]').forEach(menu => {
        menu.removeAttribute('open');
      });
    });

    document.addEventListener('click', function(event) {
      const logoutLink = event.target.closest('.js-logout-confirm');
      if (!logoutLink) return;

      if (!confirm('คุณต้องการออกจากระบบใช่หรือไม่?')) {
        event.preventDefault();
      }
    });
  </script>
</body>

</html>
