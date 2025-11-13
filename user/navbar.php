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
    .logo {
      display: inline-block;
      transition: transform 0.3s ease;
    }

    .logo:hover {
      transform: scale(1.05);
    }

    .logo--navbar {
      height: 50px;
      width: auto;
    }

    .logo--offcanvas {
      height: 50px;
      width: auto;
    }

    .navbar {
      min-height: 80px;
      box-shadow: 0 3px 8px var(--shadow);
      transition: background-color 0.4s ease;
      padding-top: 0.5rem;
      padding-bottom: 0.5rem;
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
    }
  </style>
</head>

<body>

  <nav class="navbar navbar-expand-xl bg-white fixed-top">
    <div class="container">
      <a class="navbar-brand" href="../user/index.php">
        <img src="../user/image/logo-3.png" alt="สวนลุงเผือก" class="logo logo--navbar">
      </a>

      <button class="navbar-toggler text-dark border-0" type="button" data-bs-toggle="offcanvas" data-bs-target="#navMenu">
        <i class='bx bx-menu' style="font-size:30px;"></i>
      </button>

      <div class="offcanvas offcanvas-end" tabindex="-1" id="navMenu">
        <div class="offcanvas-header">
          <img src="../user/image/logo-3.png" alt="สวนลุงเผือก" class="logo logo--offcanvas">
          <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
        </div>

        <div class="offcanvas-body">
          <ul class="navbar-nav ms-auto mb-2 mb-lg-0 align-items-center">
            <li class="nav-item"><a class="nav-link" href="../user/index.php">หน้าแรก</a></li>
            <li class="nav-item"><a class="nav-link" href="../user/mango_varieties.php">สายพันธุ์ทั้งหมด</a></li>
            <li class="nav-item"><a class="nav-link" href="../user/products.php">สินค้าผลิตภัณฑ์</a></li>
            <li class="nav-item"><a class="nav-link" href="../user/course.php">หลักสูตรการเรียนรู้</a></li>
            <li class="nav-item"><a class="nav-link" href="../user/activities.php">จองวันเข้าดูงาน</a></li>
            <li class="nav-item"><a class="nav-link" href="../user/contact.php">ติดต่อเรา</a></li>

            <?php if (!isset($_SESSION['member_id'])): ?>
              <li class="nav-item mt-3 mt-xl-0">
                <a href="../user/member_login.php" class="cta-btns">เข้าสู่ระบบ</a>
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