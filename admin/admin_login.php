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
            --forest-900: #10251d;
            --forest-800: #18382c;
            --forest-700: #1f5942;
            --forest-600: #277859;
            --leaf-100: #ecf7f1;
            --leaf-50: #f6fbf8;
            --gold-500: #d99b3d;
            --ink-900: #18251f;
            --ink-600: #5b6d65;
            --ink-400: #8c9b95;
            --line: #dfe8e3;
            --danger: #b42318;
            --danger-soft: #fff1f0;
        }

        * {
            box-sizing: border-box;
        }

        body {
            min-height: 100vh;
            margin: 0;
            /* font-family: 'Kanit', sans-serif; */
            font-family: "Prompt", sans-serif;
            color: var(--ink-900);
            background:
                radial-gradient(circle at top left, rgba(217, 155, 61, 0.14), transparent 30%),
                linear-gradient(135deg, #f4faf6 0%, #ffffff 52%, #edf7f2 100%);
        }

        .login-shell {
            min-height: 100vh;
            display: grid;
            grid-template-columns: minmax(0, 1.08fr) minmax(430px, 0.72fr);
        }

        .brand-panel {
            position: relative;
            display: flex;
            align-items: stretch;
            min-height: 100vh;
            overflow: hidden;
            isolation: isolate;
            background: var(--forest-900);
        }

        .brand-panel::before {
            content: "";
            position: absolute;
            inset: 0;
            z-index: -2;
            background-image:
                linear-gradient(105deg, rgba(16, 37, 29, 0.93) 0%, rgba(16, 37, 29, 0.72) 42%, rgba(16, 37, 29, 0.18) 100%),
                url('image/พื้นหลัง-001.jpeg');
            background-size: cover;
            background-position: center;
            transform: scale(1.02);
        }

        .brand-panel::after {
            content: "";
            position: absolute;
            inset: auto 8% 0 auto;
            width: min(34vw, 420px);
            height: min(34vw, 420px);
            z-index: -1;
            border: 1px solid rgba(255, 255, 255, 0.14);
            border-radius: 999px;
            transform: translateY(42%);
        }

        .brand-content {
            width: min(720px, 100%);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: clamp(32px, 5vw, 72px);
            color: #fff;
            animation: enterSoft 700ms ease both;
        }

        .brand-mark {
            display: inline-flex;
            align-items: center;
            gap: 12px;
            width: fit-content;
            color: rgba(255, 255, 255, 0.9);
            font-size: 15px;
            font-weight: 500;
            letter-spacing: 0;
        }

        .brand-mark img {
            width: 46px;
            height: 46px;
            border-radius: 14px;
            object-fit: cover;
            background: rgba(255, 255, 255, 0.9);
            box-shadow: 0 16px 40px rgba(0, 0, 0, 0.22);
        }

        .brand-hero {
            max-width: 650px;
            padding: 64px 0;
        }

        .brand-kicker {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 18px;
            color: #f7dba3;
            font-size: 14px;
            font-weight: 500;
        }

        .brand-kicker::before {
            content: "";
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: var(--gold-500);
            box-shadow: 0 0 0 6px rgba(217, 155, 61, 0.16);
        }

        .brand-title {
            margin: 0;
            font-size: clamp(42px, 6vw, 82px);
            font-weight: 700;
            line-height: 1.04;
            letter-spacing: 0;
        }

        .brand-subtitle {
            max-width: 520px;
            margin: 22px 0 0;
            color: rgba(255, 255, 255, 0.82);
            font-size: clamp(16px, 1.5vw, 20px);
            font-weight: 300;
            line-height: 1.8;
        }

        .brand-meta {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 18px;
            max-width: 620px;
            border-top: 1px solid rgba(255, 255, 255, 0.18);
            padding-top: 24px;
        }

        .brand-meta-item {
            min-width: 0;
        }

        .brand-meta-label {
            display: block;
            color: rgba(255, 255, 255, 0.58);
            font-size: 12px;
            font-weight: 400;
        }

        .brand-meta-value {
            display: block;
            margin-top: 4px;
            color: #fff;
            font-size: 15px;
            font-weight: 500;
        }

        .form-panel {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: clamp(24px, 4vw, 64px);
            background: rgba(255, 255, 255, 0.9);
        }

        .login-card {
            width: min(100%, 430px);
            animation: enterSoft 780ms 80ms ease both;
        }

        .mobile-brand {
            display: none;
            align-items: center;
            gap: 12px;
            margin-bottom: 30px;
        }

        .mobile-brand img {
            width: 48px;
            height: 48px;
            border-radius: 14px;
            object-fit: cover;
        }

        .mobile-brand strong {
            display: block;
            font-size: 18px;
            font-weight: 600;
        }

        .mobile-brand span {
            display: block;
            margin-top: 2px;
            color: var(--ink-600);
            font-size: 13px;
        }

        .form-header {
            margin-bottom: 30px;
        }

        .form-eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 14px;
            color: var(--forest-600);
            font-size: 13px;
            font-weight: 600;
        }

        .form-eyebrow i {
            color: var(--gold-500);
        }

        .form-header h1 {
            margin: 0;
            color: var(--ink-900);
            font-size: clamp(30px, 3vw, 40px);
            font-weight: 700;
            line-height: 1.25;
            letter-spacing: 0;
        }

        .form-header p {
            margin: 12px 0 0;
            color: var(--ink-600);
            font-size: 15px;
            line-height: 1.7;
        }

        .alert-danger {
            gap: 10px;
            border: 1px solid rgba(180, 35, 24, 0.14);
            border-radius: 8px;
            color: var(--danger);
            background: var(--danger-soft);
            font-size: 14px;
        }

        .field-group {
            margin-bottom: 18px;
        }

        .field-label {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 8px;
            color: var(--ink-900);
            font-size: 14px;
            font-weight: 500;
        }

        .input-shell {
            position: relative;
        }

        .input-icon {
            position: absolute;
            left: 16px;
            top: 50%;
            color: var(--ink-400);
            font-size: 15px;
            transform: translateY(-50%);
            pointer-events: none;
        }

        .form-control-lg {
            min-height: 54px;
            border: 1px solid var(--line);
            border-radius: 8px;
            padding: 14px 48px 14px 46px;
            color: var(--ink-900);
            background: #fbfdfc;
            font-size: 15px;
            transition: border-color 180ms ease, box-shadow 180ms ease, background-color 180ms ease;
        }

        .form-control-lg::placeholder {
            color: #a0aaa5;
        }

        .form-control-lg:focus {
            border-color: var(--forest-600);
            background: #fff;
            box-shadow: 0 0 0 4px rgba(39, 120, 89, 0.12);
        }

        .toggle-password {
            position: absolute;
            right: 8px;
            top: 50%;
            width: 40px;
            height: 40px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: 0;
            border-radius: 8px;
            color: var(--ink-400);
            background: transparent;
            transform: translateY(-50%);
            transition: color 180ms ease, background-color 180ms ease;
        }

        .toggle-password:hover,
        .toggle-password:focus-visible {
            color: var(--forest-700);
            background: var(--leaf-100);
            outline: none;
        }

        .login-actions {
            margin-top: 28px;
        }

        .btn-login {
            width: 100%;
            min-height: 54px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            border: 0;
            border-radius: 8px;
            color: #fff;
            background: linear-gradient(135deg, var(--forest-700), var(--forest-600));
            font-size: 16px;
            font-weight: 600;
            box-shadow: 0 18px 36px rgba(31, 89, 66, 0.22);
            transition: transform 180ms ease, box-shadow 180ms ease, filter 180ms ease;
        }

        .btn-login:hover,
        .btn-login:focus-visible {
            color: #fff;
            filter: brightness(1.02);
            transform: translateY(-1px);
            box-shadow: 0 22px 42px rgba(31, 89, 66, 0.28);
            outline: none;
        }

        .btn-login:active {
            transform: translateY(0);
            box-shadow: 0 12px 26px rgba(31, 89, 66, 0.22);
        }

        .security-note {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            margin-top: 22px;
            padding-top: 20px;
            border-top: 1px solid var(--line);
            color: var(--ink-600);
            font-size: 13px;
            line-height: 1.6;
        }

        .security-note i {
            margin-top: 3px;
            color: var(--forest-600);
        }

        @keyframes enterSoft {
            from {
                opacity: 0;
                transform: translateY(18px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @media (max-width: 1080px) {
            .login-shell {
                grid-template-columns: minmax(0, 0.9fr) minmax(400px, 0.8fr);
            }

            .brand-content {
                padding: 42px;
            }

            .brand-meta {
                grid-template-columns: 1fr;
                gap: 12px;
            }
        }

        @media (max-width: 860px) {
            body {
                background:
                    linear-gradient(rgba(246, 251, 248, 0.92), rgba(246, 251, 248, 0.96)),
                    url('image/พื้นหลัง-001.jpeg');
                background-size: cover;
                background-position: center;
            }

            .login-shell {
                display: block;
            }

            .brand-panel {
                display: none;
            }

            .form-panel {
                min-height: 100vh;
                align-items: flex-start;
                padding: 32px 22px;
                background: rgba(255, 255, 255, 0.82);
                backdrop-filter: blur(14px);
            }

            .login-card {
                margin: auto 0;
                padding: 26px 0;
            }

            .mobile-brand {
                display: flex;
            }
        }

        @media (max-width: 420px) {
            .form-panel {
                padding: 24px 18px;
            }

            .form-header {
                margin-bottom: 24px;
            }

            .form-control-lg {
                font-size: 14px;
            }
        }
    </style>
</head>
<body>
    <main class="login-shell">
        <section class="brand-panel" aria-label="สวนลุงเผือก">
            <div class="brand-content">
                <div class="brand-mark">
                    <img src="../admin/image/logo-69.png" alt="โลโก้สวนลุงเผือก">
                    <span>Admin Console</span>
                </div>

                <div class="brand-hero">
                    <!-- <div class="brand-kicker">ระบบจัดการหลังบ้าน</div> -->
                    <h1 class="brand-title">สวนลุงเผือก</h1>
                    <p class="brand-subtitle">
                        เข้าถึงข้อมูลสินค้า การจองคิวออนไลน์ คำสั่งซื้อ และรายงานสำคัญสำหรับผู้ดูแลระบบ
                    </p>
                </div>

                <div class="brand-meta" aria-label="ข้อมูลระบบ">
                    <div class="brand-meta-item">
                        <span class="brand-meta-label">พื้นที่ทำงาน</span>
                        <span class="brand-meta-value">จัดการสินค้า</span>
                    </div>
                    <div class="brand-meta-item">
                        <span class="brand-meta-label">คำสั่งซื้อ</span>
                        <span class="brand-meta-value">ติดตามสถานะ</span>
                    </div>
                    <div class="brand-meta-item">
                        <span class="brand-meta-label">รายงาน</span>
                        <span class="brand-meta-value">สรุปยอดขาย</span>
                    </div>
                </div>
            </div>
        </section>

        <section class="form-panel" aria-label="เข้าสู่ระบบผู้ดูแล">
            <div class="login-card">
                <div class="mobile-brand">
                    <img src="../admin/image/logo-69.png" alt="โลโก้สวนลุงเผือก">
                    <div>
                        <strong>สวนลุงเผือก</strong>
                        <span>Admin Console</span>
                    </div>
                </div>

                <header class="form-header">
                    <!-- <div class="form-eyebrow">
                        <i class="fas fa-shield-halved" aria-hidden="true"></i>
                        สำหรับผู้ดูแลระบบ
                    </div> -->
                    <h1>เข้าสู่ระบบ</h1>
                    <p>กรอกบัญชีผู้ดูแลเพื่อเข้าสู่หน้าจัดการข้อมูลของสวนลุงเผือก</p>
                </header>

                <?php if (isset($_GET['error'])): ?>
                    <div class="alert alert-danger d-flex align-items-center mb-4" role="alert">
                        <i class="fas fa-circle-exclamation" aria-hidden="true"></i>
                        <div><?php echo htmlspecialchars($_GET['error'], ENT_QUOTES, 'UTF-8'); ?></div>
                    </div>
                <?php endif; ?>

                <form method="POST" action="process_login.php">
                    <div class="field-group">
                        <label class="field-label" for="login">ชื่อผู้ใช้หรืออีเมล</label>
                        <div class="input-shell">
                            <i class="fas fa-user input-icon" aria-hidden="true"></i>
                            <input
                                type="text"
                                class="form-control form-control-lg"
                                id="login"
                                name="login"
                                placeholder="admin@suanlungphueak.com"
                                autocomplete="username"
                                required
                            >
                        </div>
                    </div>

                    <div class="field-group">
                        <label class="field-label" for="password">รหัสผ่าน</label>
                        <div class="input-shell">
                            <i class="fas fa-lock input-icon" aria-hidden="true"></i>
                            <input
                                type="password"
                                class="form-control form-control-lg"
                                id="password"
                                name="password"
                                placeholder="กรอกรหัสผ่าน"
                                autocomplete="current-password"
                                required
                            >
                            <button class="toggle-password" type="button" id="togglePassword" aria-label="แสดงรหัสผ่าน">
                                <i class="fas fa-eye" aria-hidden="true"></i>
                            </button>
                        </div>
                    </div>

                    <div class="login-actions">
                        <button type="submit" class="btn-login">
                            เข้าสู่ระบบ
                            <i class="fas fa-arrow-right" aria-hidden="true"></i>
                        </button>
                    </div>
                </form>

                <div class="security-note">
                    <i class="fas fa-lock" aria-hidden="true"></i>
                    <span>ระบบนี้สงวนสิทธิ์สำหรับผู้ดูแลที่ได้รับอนุญาตเท่านั้น โปรดออกจากระบบทุกครั้งหลังใช้งานบนอุปกรณ์สาธารณะ</span>
                </div>
            </div>
        </section>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const togglePassword = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('password');
        const passwordIcon = togglePassword.querySelector('i');

        togglePassword.addEventListener('click', function () {
            const shouldShow = passwordInput.type === 'password';

            passwordInput.type = shouldShow ? 'text' : 'password';
            passwordIcon.classList.toggle('fa-eye', !shouldShow);
            passwordIcon.classList.toggle('fa-eye-slash', shouldShow);
            togglePassword.setAttribute('aria-label', shouldShow ? 'ซ่อนรหัสผ่าน' : 'แสดงรหัสผ่าน');
        });
    </script>
</body>
</html>
