<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เข้าสู่ระบบ - แบบฟอร์มทันสมัย</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600;700&family=Mitr:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #4361ee;
            --secondary: #3a0ca3;
            --accent: #f72585;
            --success: #4cc9f0;
            --light: #f8f9fa;
            --dark: #212529;
            --success-dark: rgb(20, 58, 44);
            --success-end: rgba(13, 201, 132, 1);
        }
        
        body {
            font-family: 'Kanit', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #e4e7f1 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            position: relative;
            overflow-x: hidden;
        }
        
        body::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 300px;
            background: linear-gradient(to right, var(--success-dark), var(--success-end));
            clip-path: polygon(0 0, 100% 0, 100% 80%, 0 100%);
            z-index: -1;
        }
        
        .login-container {
            max-width: 1000px;
            width: 100%;
            margin: 0 auto;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 15px 50px rgba(67, 97, 238, 0.25);
            animation: fadeIn 0.8s ease-out;
        }
        
        .login-hero {
            background: linear-gradient(to bottom right, var(--success-dark), var(--success-end));
            color: white;
            padding: 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }
        
        .login-hero::before {
            content: "";
            position: absolute;
            top: -50px;
            right: -50px;
            width: 200px;
            height: 200px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.1);
        }
        
        .login-hero::after {
            content: "";
            position: absolute;
            bottom: -30px;
            left: -30px;
            width: 150px;
            height: 150px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.1);
        }
        
        .hero-title {
            font-family: 'Mitr', sans-serif;
            font-weight: 700;
            font-size: 2.5rem;
            margin-bottom: 15px;
            position: relative;
            z-index: 2;
            text-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }
        
        .hero-subtitle {
            font-size: 1.1rem;
            opacity: 0.9;
            position: relative;
            z-index: 2;
            line-height: 1.6;
        }
        
        .hero-features {
            margin-top: 30px;
            position: relative;
            z-index: 2;
        }
        
        .feature-item {
            display: flex;
            align-items: center;
            margin-bottom: 12px;
            font-size: 0.95rem;
        }
        
        .feature-item i {
            margin-right: 10px;
            color: var(--success);
            background: rgba(255, 255, 255, 0.2);
            width: 28px;
            height: 28px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .login-form {
            background: #fff;
            padding: 40px;
        }
        
        .form-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .form-title {
            font-family: 'Mitr', sans-serif;
            font-weight: 600;
            color: var(--success-end);
            font-size: 2rem;
            margin-bottom: 8px;
            position: relative;
            display: inline-block;
        }
        
        .form-title::after {
            content: "";
            position: absolute;
            bottom: -5px;
            left: 25%;
            width: 50%;
            height: 3px;
            background: var(--accent);
            border-radius: 10px;
        }
        
        .form-subtitle {
            color: #6c757d;
            font-size: 1rem;
        }
        
        .form-group {
            position: relative;
            margin-bottom: 25px;
        }
        
        .form-label {
            font-weight: 500;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            color: #495057;
        }
        
        .form-label i {
            margin-right: 8px;
            color: var(--success-end);
        }
        
        .form-control {
            border-radius: 12px;
            padding: 14px 20px 14px 45px;
            border: 2px solid #e9ecef;
            transition: all 0.3s ease;
            font-size: 1rem;
        }
        
        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.15);
        }
        
        .btn-submit {
            background: linear-gradient(to right, var(--success-dark), var(--success-end));
            border: none;
            border-radius: 30px;
            padding: 14px;
            font-size: 1.1rem;
            font-weight: 500;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(67, 97, 238, 0.3);
            position: relative;
            overflow: hidden;
        }
        
        .btn-submit:hover {
            transform: translateY(-3px);
            box-shadow: 0 7px 20px rgba(67, 97, 238, 0.4);
        }
        
        .btn-submit::after {
            content: "";
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: rgba(255, 255, 255, 0.1);
            transform: rotate(30deg);
            transition: all 0.6s ease;
        }
        
        .btn-submit:hover::after {
            transform: rotate(30deg) translate(20%, 20%);
        }
        
        .password-container {
            position: relative;
        }
        
        .toggle-password {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #6c757d;
            z-index: 10;
            transition: color 0.3s ease;
        }
        
        .toggle-password:hover {
            color: var(--primary);
        }
        
        .form-footer {
            text-align: center;
            margin-top: 25px;
            color: #6c757d;
            font-size: 0.95rem;
        }
        
        .form-footer a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .form-footer a:hover {
            color: var(--secondary);
            text-decoration: underline;
        }
        
        /* Animation for form elements */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .form-group {
            animation: fadeIn 0.5s ease-out forwards;
            opacity: 0;
        }
        
        .form-group:nth-child(1) { animation-delay: 0.1s; }
        .form-group:nth-child(2) { animation-delay: 0.2s; }
        .form-group:nth-child(3) { animation-delay: 0.3s; }
        
        /* Floating animation for hero section */
        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
            100% { transform: translateY(0px); }
        }
        
        .hero-icon {
            font-size: 3rem;
            margin-bottom: 20px;
            color: var(--success-end);
            animation: float 4s ease-in-out infinite;
            text-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        /* Responsive design */
        @media (max-width: 992px) {
            .login-container {
                max-width: 700px;
            }
            
            .hero-title {
                font-size: 2.1rem;
            }
        }
        
        @media (max-width: 768px) {
            .login-container {
                flex-direction: column;
            }
            
            .login-hero {
                padding: 30px 20px;
            }
            
            .login-form {
                padding: 30px 20px;
            }
            
            .hero-title {
                font-size: 1.8rem;
            }
            
            .form-title {
                font-size: 1.7rem;
            }
        }
        
        @media (max-width: 576px) {
            .hero-title {
                font-size: 1.6rem;
            }
            
            .form-title {
                font-size: 1.5rem;
            }
            
            .form-control {
                padding-left: 40px;
            }
        }
    </style>
</head>
<body>
    <?php
       
        if (isset($_SESSION['error'])) {
            echo '<div class="alert alert-danger text-center">'.$_SESSION['error'].'</div>';
            unset($_SESSION['error']);
        }
    ?>

    <div class="login-container shadow-lg">
        <div class="row g-0">
            <div class="col-lg-5">
                <div class="login-hero">
                    <div class="text-center">
                        <div class="hero-icon">
                            <i class="fas fa-user-check"></i>
                        </div>
                        <h1 class="hero-title">ยินดีต้อนรับกลับมา</h1>
                        <p class="hero-subtitle">เข้าสู่ระบบเพื่อเข้าถึงบริการพิเศษและอัปเดตข้อมูลล่าสุดสำหรับสมาชิก</p>
                    </div>
                    
                    <div class="hero-features">
                        <div class="feature-item">
                            <i class="fas fa-check"></i>
                            <span>เข้าถึงบริการและข้อมูลส่วนตัว</span>
                        </div>
                        <div class="feature-item">
                            <i class="fas fa-check"></i>
                            <span>จัดการข้อมูลส่วนตัวและบัญชี</span>
                        </div>
                        <div class="feature-item">
                            <i class="fas fa-check"></i>
                            <span>รับข้อเสนอพิเศษสำหรับสมาชิก</span>
                        </div>
                        <div class="feature-item">
                            <i class="fas fa-check"></i>
                            <span>บันทึกประวัติการใช้งานล่าสุด</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-7">
                <div class="login-form">
                    <div class="form-header">
                        <h2 class="form-title">เข้าสู่ระบบ</h2>
                        <p class="form-subtitle">กรุณากรอกข้อมูลเพื่อเข้าสู่ระบบบัญชีของคุณ</p>
                    </div>
                    
                    <form action="login_check.php" method="POST" id="loginForm">
                        <div class="form-group">
                            <label class="form-label"><i class="fas fa-envelope"></i> อีเมล์</label>
                            <input type="email" class="form-control" name="email" required placeholder="example@domain.com">
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label"><i class="fas fa-lock"></i> รหัสผ่าน</label>
                            <div class="password-container">
                                <input type="password" class="form-control" name="password" id="password" required minlength="6" placeholder="กรอกรหัสผ่านของคุณ">
                                <i class="toggle-password fas fa-eye" id="togglePassword"></i>
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="rememberMe">
                                <label class="form-check-label" for="rememberMe">จดจำฉัน</label>
                            </div>
                            <a href="password_recovery.php" class="text-decoration-none">ลืมรหัสผ่าน?</a>
                        </div>
                        
                        <div class="form-group mt-4">
                            <button type="submit" class="btn btn-submit w-100">
                                <i class="fas fa-sign-in-alt me-2"></i> เข้าสู่ระบบ
                            </button>
                        </div>
                        
                        <div class="text-center my-4 position-relative">
                            <hr>
                        </div>

                    </form>
                    
                    <div class="form-footer">
                        <p>ยังไม่มีบัญชี? <a href="register.php">สมัครสมาชิกใหม่</a></p>
                        <p class="mt-2">© 2023 ชุมชนของเรา. สงวนลิขสิทธิ์</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript สำหรับการแสดง/ซ่อนรหัสผ่าน -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const togglePassword = document.getElementById('togglePassword');
            const passwordInput = document.getElementById('password');
            
            if (togglePassword && passwordInput) {
                togglePassword.addEventListener('click', function() {
                    // สลับประเภทของ input ระหว่าง password และ text
                    const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                    passwordInput.setAttribute('type', type);
                    
                    // สลับไอคอนระหว่างตาเปิดและตาปิด
                    this.classList.toggle('fa-eye');
                    this.classList.toggle('fa-eye-slash');
                });
            }
            
            // เพิ่มการตรวจสอบฟอร์มเบื้องต้น
            const loginForm = document.getElementById('loginForm');
            if (loginForm) {
                loginForm.addEventListener('submit', function(event) {
                    const email = this.querySelector('input[name="email"]').value;
                    const password = this.querySelector('input[name="password"]').value;
                    
                    if (!email || !password) {
                        event.preventDefault();
                        alert('กรุณากรอกอีเมล์และรหัสผ่าน');
                        return false;
                    }
                    
                    if (password.length < 6) {
                        event.preventDefault();
                        alert('รหัสผ่านต้องมีความยาวอย่างน้อย 6 ตัวอักษร');
                        return false;
                    }
                });
            }
        });
    </script>
</body>
</html>