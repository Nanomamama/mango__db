<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เข้าสู่ระบบผู้ดูแล - สวนลุงเผือก</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #277859; /* สีเขียวหลัก */
            --primary-hover: #1e5c44;
            --accent-color: #f5b553;  /* สีส้มเหลือง */
            --bg-light: #f8fcfb;      /* พื้นหลังโทนสว่างอมเขียวจางๆ */
            --text-dark: #2c3e50;
        }

        body {
            font-family: 'Kanit', sans-serif;
            background-color: var(--bg-light);
            height: 100vh;
            overflow: hidden;
        }

        .login-wrapper {
            height: 100vh;
            width: 100%;
            display: flex;
        }

        /* --- ฝั่งซ้าย: รูปภาพและ Branding --- */
        .login-side-image {
            flex: 1;
            /* เปลี่ยน path รูปภาพพื้นหลังตรงนี้ */
            background: linear-gradient(rgba(39, 120, 89, 0.85), rgba(39, 120, 89, 0.7)), url('image/พื้นหลัง-001.jpeg');
            background-size: cover;
            background-position: center;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            color: white;
            text-align: center;
            padding: 40px;
            position: relative;
        }

        .brand-content {
            z-index: 2;
            animation: fadeIn 1s ease-out;
        }

        .brand-content h1 {
            font-weight: 700;
            font-size: 3.5rem;
            margin-bottom: 15px;
            text-shadow: 0 4px 10px rgba(0,0,0,0.2);
        }

        .brand-content p {
            font-size: 1.2rem;
            opacity: 0.9;
            font-weight: 300;
        }

        /* Decoration circles (ตกแต่งฝั่งซ้าย) */
        .circle-deco {
            position: absolute;
            border-radius: 50%;
            background: rgba(255,255,255,0.1);
        }
        .c1 { width: 300px; height: 300px; top: -50px; left: -50px; }
        .c2 { width: 150px; height: 150px; bottom: 10%; right: 10%; }

        /* --- ฝั่งขวา: แบบฟอร์ม --- */
        .login-side-form {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 0 80px;
            background: white;
            position: relative;
            max-width: 600px; /* จำกัดความกว้างไม่ให้ยืดเกินไป */
        }

        .form-header {
            margin-bottom: 40px;
        }

        .form-header .logo-img {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            object-fit: cover;
            margin-bottom: 20px;
            box-shadow: 0 4px 12px rgba(39, 120, 89, 0.2);
        }

        .form-header h2 {
            color: var(--text-dark);
            font-weight: 700;
            font-size: 28px;
        }

        .form-header p {
            color: #7f8c8d;
            font-size: 15px;
        }

        /* ปรับแต่ง Input */
        .custom-input-group {
            margin-bottom: 25px;
            position: relative;
        }

        .custom-input-group label {
            display: block;
            margin-bottom: 8px;
            color: var(--text-dark);
            font-weight: 500;
            font-size: 14px;
        }

        .form-control-lg {
            border: 2px solid #eef2f7;
            background-color: #fcfdfe;
            border-radius: 12px;
            padding: 14px 20px;
            font-size: 15px;
            transition: all 0.3s;
        }

        .form-control-lg:focus {
            border-color: var(--primary-color);
            background-color: #fff;
            box-shadow: 0 0 0 4px rgba(39, 120, 89, 0.1);
        }

        .password-wrapper {
            position: relative;
        }
        
        .toggle-password {
            position: absolute;
            right: 20px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #95a5a6;
            transition: color 0.3s;
        }

        .toggle-password:hover {
            color: var(--primary-color);
        }

        /* ปุ่ม Login */
        .btn-primary-custom {
            background: var(--primary-color);
            border: none;
            padding: 16px;
            border-radius: 12px;
            font-weight: 600;
            font-size: 16px;
            width: 100%;
            color: white;
            box-shadow: 0 10px 20px rgba(39, 120, 89, 0.2);
            transition: all 0.3s ease;
        }

        .btn-primary-custom:hover {
            background: var(--primary-hover);
            transform: translateY(-2px);
            box-shadow: 0 15px 25px rgba(39, 120, 89, 0.3);
        }

        .register-link {
            text-align: center;
            margin-top: 30px;
            font-size: 14px;
            color: #7f8c8d;
        }

        .register-link a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 600;
        }

        .register-link a:hover {
            text-decoration: underline;
        }

        /* Responsive */
        @media (max-width: 992px) {
            .login-side-image {
                display: none; /* ซ่อนรูปภาพเมื่อจอเล็ก */
            }
            .login-side-form {
                flex: none;
                width: 100%;
                max-width: 100%;
                padding: 40px 30px;
            }
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>

    <div class="login-wrapper">
        <div class="login-side-image">
            <div class="circle-deco c1"></div>
            <div class="circle-deco c2"></div>
            <div class="brand-content">
                <h1>สวนลุงเผือก</h1>
                <p>ระบบจัดการหลังบ้านสำหรับผู้ดูแล<br>บริหารจัดการผลผลิตและข้อมูลอย่างมืออาชีพ</p>
            </div>
        </div>

        <div class="login-side-form">
            <div class="form-header">
                <img src="../admin/image/logo-loginadmin.png" alt="Logo" class="logo-img">
                <h2>ยินดีต้อนรับกลับมา! 👋</h2>
                <p>กรุณาเข้าสู่ระบบเพื่อจัดการข้อมูล</p>
            </div>

            <?php if (isset($_GET['error'])): ?>
                <div class="alert alert-danger d-flex align-items-center mb-4" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <div><?php echo htmlspecialchars($_GET['error'], ENT_QUOTES, 'UTF-8'); ?></div>
                </div>
            <?php endif; ?>

            <form method="POST" action="process_login.php">
                <div class="custom-input-group">
                    <label for="login">ชื่อผู้ใช้หรืออีเมล</label>
                    <input type="text" class="form-control form-control-lg" id="login" name="login" placeholder="เช่น admin@suanlungphueak.com" required>
                </div>

                <div class="custom-input-group">
                    <label for="password">รหัสผ่าน</label>
                    <div class="password-wrapper">
                        <input type="password" class="form-control form-control-lg" id="password" name="password" placeholder="••••••••" required>
                        <i class="fas fa-eye toggle-password" id="togglePassword"></i>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="rememberMe">
                        <label class="form-check-label text-muted" style="font-size: 14px;" for="rememberMe">จำการเข้าสู่ระบบ</label>
                    </div>
                    </div>

                <button type="submit" class="btn-primary-custom">
                    เข้าสู่ระบบ <i class="fas fa-arrow-right ms-2"></i>
                </button>

                <div class="register-link">
                    ยังไม่มีบัญชีผู้ดูแล? <a href="./admin_register.php">ลงทะเบียนที่นี่</a>
                </div>
            </form>
            
            <div class="text-center mt-5 text-muted" style="font-size: 12px;">
                <i class="fas fa-lock me-1"></i> Secured by Admin System
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // ฟังก์ชันเปิด/ปิดตาดูรหัสผ่าน
        document.getElementById('togglePassword').addEventListener('click', function() {
            const passwordInput = document.getElementById('password');
            const icon = this;
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
    </script>
</body>
</html>