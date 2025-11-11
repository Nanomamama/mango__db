<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
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

  <style>
    :root {
      --green: #277859;
      --green-light: #32a877;
      --white: #fff;
      --dark: #222;
      --shadow: rgba(0,0,0,0.08);
    }

    * {
      font-family: 'Prompt', sans-serif;
      box-sizing: border-box;
    }

    .navbar {
      height: 5rem;
      box-shadow: 0 3px 8px var(--shadow);
      transition: background-color 0.4s ease;
    }

    .navbar.scrolled {
      background-color: var(--white);
    }

    .navbar-brand img {
      height: 70px;
      transition: transform 0.3s ease;
    }

    .navbar-brand img:hover {
      transform: scale(1.05);
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

    .cta-btn:hover {
      background: var(--white);
      color: var(--green);
    }

    .offcanvas-header img {
      height: 100px;
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

    .profile-img-wrapper {
      width: 42px;
      height: 42px;
      border-radius: 50%;
      overflow: hidden;
      border: 2px solid var(--green);
      margin-right: 8px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }

    .profile-img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      border-radius: 50%;
    }
  </style>
</head>

<body>

  <nav class="navbar navbar-expand-xl bg-white fixed-top">
    <div class="container">
      <a class="navbar-brand" href="../user/index.php">
        <img src="../user/image/สวนลุงเผือก4-Photoroom.png" alt="สวนลุงเผือก">
      </a>

      <button class="navbar-toggler text-success border-0" type="button" data-bs-toggle="offcanvas" data-bs-target="#navMenu">
        <i class='bx bx-menu' style="font-size:30px;"></i>
      </button>

      <div class="offcanvas offcanvas-end" tabindex="-1" id="navMenu">
        <div class="offcanvas-header">
          <img src="../user/image/สวนลุงเผือก4-Photoroom.png" alt="สวนลุงเผือก">
          <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
        </div>

        <div class="offcanvas-body">
          <ul class="navbar-nav ms-auto mb-2 mb-lg-0 align-items-center">
            <li class="nav-item"><a class="nav-link" href="../user/index.php">หน้าแรก</a></li>
            <li class="nav-item"><a class="nav-link" href="../user/mango_varieties.php">สายพันธุ์ทั้งหมด</a></li>
            <li class="nav-item"><a class="nav-link" href="../user/products.php">สินค้าผลิตภัณฑ์</a></li>
            <li class="nav-item"><a class="nav-link" href="../user/course.php">หลักสูตรการเรียนรู้</a></li>
            <li class="nav-item"><a class="nav-link" href="../user/activities.php">จองวันเข้าดูงาน</a></li>

            <?php if (!isset($_SESSION['member_id'])): ?>
              <li class="nav-item mt-3 mt-xl-0">
                <a href="../user/register.php" class="cta-btn">สมัครสมาชิก</a>
              </li>
            <?php else: ?>
              <li class="nav-item mt-3 mt-xl-0">
                <a href="../user/member_profile.php" class="profile-link">
                  <div class="profile-img-wrapper">
                    <img src="../user/image/profile.png" class="profile-img" alt="โปรไฟล์">
                  </div>
                  <span>โปรไฟล์</span>
                </a>
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
  </script>
</body>
</html>