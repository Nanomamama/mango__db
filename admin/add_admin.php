<?php
require_once 'auth.php';

// ตรวจสอบ session และสร้าง CSRF Token
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เพิ่มผู้ดูแลระบบใหม่ | Modern Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        :root {
            --primary: #4361ee;
            --primary-light: #6c8cff;
            --primary-soft: #eef2ff;
            --success: #2ecc71;
            --danger: #e74c3c;
            --warning: #f39c12;
            --text-heading: #1e293b;
            --text-body: #334155;
            --text-muted: #64748b;
            --border-light: #e2e8f0;
            --bg-light: #f8fafc;
            --card-bg: rgba(255, 255, 255, 0.9);
            --shadow-sm: 0 8px 20px rgba(0,0,0,0.02);
            --shadow-md: 0 12px 30px rgba(0,0,0,0.05);
            --shadow-lg: 0 20px 40px rgba(0,0,0,0.08);
            --glass-border: 1px solid rgba(255,255,255,0.5);
            --border-radius-card: 28px;
            --border-radius-element: 14px;
        }

        body {
            font-family: 'Kanit', sans-serif;
            background: radial-gradient(circle at 10% 30%, #f1f5f9 0%, #e6ecf4 100%);
            color: var(--text-body);
            line-height: 1.6;
            min-height: 100vh;
        }

        /* ===== SIDEBAR ADJUSTMENT ===== */
        .main-content {
            margin-left: 260px;
            padding: 2rem 2.5rem;
            transition: margin-left 0.25s ease;
        }

        /* ===== MAIN CARD — WIDE & MODERN ===== */
        .card-form {
            background: var(--card-bg);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: var(--glass-border);
            border-radius: var(--border-radius-card);
            padding: 2.8rem 3rem;
            box-shadow: var(--shadow-lg);
            width: 100%;
            max-width: 900px;
            margin: 0 auto;
            border: 1px solid rgba(255,255,255,0.8);
            animation: fadeInUp 0.6s ease-out;
        }

        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* ===== HEADER ===== */
        .page-header {
            text-align: center;
            margin-bottom: 2.5rem;
            padding-bottom: 1.8rem;
            border-bottom: 2px dashed rgba(67, 97, 238, 0.15);
        }
        .page-title {
            font-weight: 700;
            color: var(--primary);
            font-size: 2.2rem;
            letter-spacing: -0.5px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
        }
        .page-subtitle {
            color: var(--text-muted);
            font-size: 1.1rem;
            font-weight: 300;
            margin-top: 0.25rem;
        }

        /* ===== FORM CONTROLS ===== */
        .form-label {
            font-weight: 500;
            color: var(--text-heading);
            margin-bottom: 0.5rem;
            font-size: 0.95rem;
            letter-spacing: 0.3px;
        }
        .form-control, .form-select {
            border-radius: var(--border-radius-element);
            padding: 12px 18px;
            border: 1.5px solid var(--border-light);
            background-color: white;
            transition: all 0.2s ease;
            font-size: 1rem;
            box-shadow: inset 0 2px 4px rgba(0,0,0,0.01);
        }
        .form-control:focus, .form-select:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(67, 97, 238, 0.12);
            background-color: white;
        }

        /* ===== INPUT GROUP (PASSWORD TOGGLE) ===== */
        .input-group-text {
            background-color: white;
            border: 1.5px solid var(--border-light);
            border-left: none;
            border-radius: 0 var(--border-radius-element) var(--border-radius-element) 0;
            padding: 0 18px;
            color: var(--text-muted);
            cursor: pointer;
            transition: all 0.2s;
        }
        .input-group-text:hover {
            color: var(--primary);
            background-color: var(--primary-soft);
        }
        .input-group .form-control {
            border-right: none;
            border-radius: var(--border-radius-element) 0 0 var(--border-radius-element);
        }

        /* ===== BUTTONS ===== */
        .btn {
            padding: 12px 32px;
            font-weight: 500;
            border-radius: 40px;
            transition: all 0.25s cubic-bezier(0.02, 0.88, 0.41, 1.01);
            border: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            font-size: 1rem;
            letter-spacing: 0.3px;
        }
        .btn-primary {
            background: linear-gradient(145deg, var(--primary), #3a56d4);
            color: white;
            box-shadow: 0 6px 14px rgba(67, 97, 238, 0.25);
        }
        .btn-primary:hover {
            background: linear-gradient(145deg, #3a56d4, #2a46b0);
            transform: translateY(-3px);
            box-shadow: 0 12px 20px rgba(67, 97, 238, 0.35);
        }

        /* ===== PASSWORD STRENGTH METER ===== */
        .password-strength {
            margin-top: 0.5rem;
            height: 6px;
            border-radius: 50px;
            background: #e9ecef;
            overflow: hidden;
        }
        .strength-bar {
            height: 100%;
            width: 0%;
            transition: width 0.3s ease, background 0.3s ease;
            border-radius: 50px;
        }
        .strength-text {
            font-size: 0.8rem;
            margin-top: 0.2rem;
            display: block;
            color: var(--text-muted);
        }
        .strength-weak { background: #e74c3c; }
        .strength-medium { background: #f39c12; }
        .strength-strong { background: #2ecc71; }

        /* ===== REAL-TIME VALIDATION FEEDBACK ===== */
        .invalid-feedback {
            display: none;
            width: 100%;
            margin-top: 0.25rem;
            font-size: 0.85rem;
            color: var(--danger);
        }
        .is-invalid {
            border-color: var(--danger) !important;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 12 12' width='12' height='12' fill='none' stroke='%23e74c3c'%3e%3ccircle cx='6' cy='6' r='4.5'/%3e%3cpath stroke-linejoin='round' d='M5.8 3.6h.4L6 6.5z'/%3e%3ccircle cx='6' cy='8.2' r='.6' fill='%23e74c3c' stroke='none'/%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right calc(0.375em + 0.1875rem) center;
            background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
        }
        .is-valid {
            border-color: var(--success) !important;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 8 8'%3e%3cpath fill='%232ecc71' d='M2.3 6.73L.6 4.53c-.4-1.04.46-1.4 1.1-.8l1.1 1.4 3.4-3.8c.6-.63 1.6-.27 1.2.7l-4 4.6c-.43.5-.8.4-1.1.1z'/%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right calc(0.375em + 0.1875rem) center;
            background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
        }
        .password-match-feedback {
            font-size: 0.85rem;
            margin-top: 0.25rem;
        }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 1200px) {
            .main-content { margin-left: 80px; }
            .card-form { padding: 2rem; }
        }
        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
                padding: 1.5rem 1rem;
            }
            .card-form { padding: 1.8rem; }
            .page-title { font-size: 1.8rem; }
        }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>

    <div class="main-content">
        <div class="card-form">
            <div class="page-header">
                <h1 class="page-title">
                    <i class="bi bi-shield-plus" style="background: none; -webkit-text-fill-color: var(--primary);"></i>
                    เพิ่มผู้ดูแลระบบ
                </h1>
                <p class="page-subtitle">กรอกข้อมูลเพื่อสร้างบัญชีผู้ดูแลระบบใหม่</p>
            </div>

            <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success d-flex align-items-center alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i>
                <div>
                    สร้างบัญชีผู้ดูแลระบบใหม่สำเร็จแล้ว!
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php endif; ?>

            <form action="save_admin.php" method="POST" id="addAdminForm" class="needs-validation" novalidate>
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

                <!-- Username -->
                <div class="mb-4">
                    <label for="username" class="form-label">
                        <i class="bi bi-person-circle me-1"></i> ชื่อผู้ใช้ (Username) <span class="text-danger">*</span>
                    </label>
                    <input type="text" class="form-control" id="username" name="username" 
                           placeholder="เช่น admin01, manager" required>
                    <div class="invalid-feedback">กรุณากรอกชื่อผู้ใช้</div>
                </div>

                <!-- Email -->
                <div class="mb-4">
                    <label for="email" class="form-label">
                        <i class="bi bi-envelope-fill me-1"></i> อีเมล <span class="text-danger">*</span>
                    </label>
                    <input type="email" class="form-control" id="email" name="email" 
                           placeholder="admin@example.com" required>
                    <div class="invalid-feedback">กรุณากรอกอีเมลให้ถูกต้อง</div>
                </div>

                <!-- Password -->
                <div class="mb-4">
                    <label for="password" class="form-label">
                        <i class="bi bi-lock-fill me-1"></i> รหัสผ่าน <span class="text-danger">*</span>
                    </label>
                    <div class="input-group">
                        <input type="password" class="form-control" id="password" name="password" 
                               placeholder="สร้างรหัสผ่านที่ปลอดภัย" required>
                        <span class="input-group-text password-toggle" id="togglePassword">
                            <i class="bi bi-eye-slash"></i>
                        </span>
                    </div>
                    <!-- Password Strength Meter -->
                    <div class="password-strength mt-2">
                        <div class="strength-bar" id="strengthBar"></div>
                    </div>
                    <span class="strength-text" id="strengthText">ระดับความปลอดภัย</span>
                    <div class="invalid-feedback">กรุณากรอกรหัสผ่าน</div>
                </div>

                <!-- Confirm Password -->
                <div class="mb-4">
                    <label for="confirm_password" class="form-label">
                        <i class="bi bi-check2-circle me-1"></i> ยืนยันรหัสผ่าน <span class="text-danger">*</span>
                    </label>
                    <div class="input-group">
                        <input type="password" class="form-control" id="confirm_password" 
                               name="confirm_password" placeholder="กรอกรหัสผ่านอีกครั้ง" required>
                        <span class="input-group-text password-toggle" id="toggleConfirmPassword">
                            <i class="bi bi-eye-slash"></i>
                        </span>
                    </div>
                    <div id="passwordMatchFeedback" class="password-match-feedback"></div>
                </div>

                <!-- Submit Button -->
                <div class="d-flex justify-content-end mt-5 pt-3 border-top border-2" 
                     style="border-color: rgba(67,97,238,0.1) !important;">
                    <button type="submit" class="btn btn-primary px-5">
                        <i class="bi bi-person-check-fill"></i> สร้างบัญชี
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        (function() {
            'use strict';

            // -------- PASSWORD TOGGLE --------
            function setupPasswordToggle(inputId, toggleId) {
                const input = document.getElementById(inputId);
                const toggle = document.getElementById(toggleId);
                if (!input || !toggle) return;

                toggle.addEventListener('click', function() {
                    const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
                    input.setAttribute('type', type);
                    // เปลี่ยนไอคอน
                    const icon = this.querySelector('i');
                    icon.classList.toggle('bi-eye');
                    icon.classList.toggle('bi-eye-slash');
                });
            }

            setupPasswordToggle('password', 'togglePassword');
            setupPasswordToggle('confirm_password', 'toggleConfirmPassword');

            // -------- PASSWORD STRENGTH METER --------
            const passwordInput = document.getElementById('password');
            const strengthBar = document.getElementById('strengthBar');
            const strengthText = document.getElementById('strengthText');

            function checkPasswordStrength(password) {
                let strength = 0;
                // ความยาว >= 8
                if (password.length >= 8) strength += 1;
                // มีตัวเลข
                if (/\d/.test(password)) strength += 1;
                // มีตัวพิมพ์ใหญ่และพิมพ์เล็ก
                if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength += 1;
                // มีอักขระพิเศษ
                if (/[!@#$%^&*(),.?":{}|<>]/.test(password)) strength += 1;
                return strength;
            }

            passwordInput.addEventListener('input', function() {
                const password = this.value;
                const strength = checkPasswordStrength(password);
                
                // ปรับความกว้างและสีของแถบ
                let width = 0;
                let level = '';
                let colorClass = '';

                if (password.length === 0) {
                    width = 0;
                    level = 'ระดับความปลอดภัย';
                    colorClass = '';
                } else if (strength <= 1) {
                    width = 25;
                    level = 'อ่อน';
                    colorClass = 'strength-weak';
                } else if (strength === 2) {
                    width = 50;
                    level = 'ปานกลาง';
                    colorClass = 'strength-medium';
                } else if (strength === 3) {
                    width = 75;
                    level = 'ดี';
                    colorClass = 'strength-strong';
                } else {
                    width = 100;
                    level = 'แข็งแรงมาก';
                    colorClass = 'strength-strong';
                }

                strengthBar.style.width = width + '%';
                strengthBar.className = 'strength-bar ' + colorClass;
                strengthText.textContent = 'ระดับความปลอดภัย: ' + level;
            });

            // -------- REAL-TIME PASSWORD MATCH VALIDATION --------
            const confirmInput = document.getElementById('confirm_password');
            const passwordMatchFeedback = document.getElementById('passwordMatchFeedback');

            function validatePasswordMatch() {
                const password = passwordInput.value;
                const confirm = confirmInput.value;

                if (confirm.length === 0) {
                    passwordMatchFeedback.innerHTML = '';
                    confirmInput.classList.remove('is-valid', 'is-invalid');
                    return;
                }

                if (password === confirm) {
                    confirmInput.classList.add('is-valid');
                    confirmInput.classList.remove('is-invalid');
                    passwordMatchFeedback.innerHTML = '<span class="text-success"><i class="bi bi-check-circle-fill me-1"></i>รหัสผ่านตรงกัน</span>';
                } else {
                    confirmInput.classList.add('is-invalid');
                    confirmInput.classList.remove('is-valid');
                    passwordMatchFeedback.innerHTML = '<span class="text-danger"><i class="bi bi-exclamation-circle-fill me-1"></i>รหัสผ่านไม่ตรงกัน</span>';
                }
            }

            passwordInput.addEventListener('input', validatePasswordMatch);
            confirmInput.addEventListener('input', validatePasswordMatch);

            // -------- BOOTSTRAP FORM VALIDATION + ป้องกัน submit เมื่อรหัสไม่ตรงกัน --------
            const form = document.getElementById('addAdminForm');
            form.addEventListener('submit', function(event) {
                // ตรวจสอบความถูกต้องของ Bootstrap
                if (!form.checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();
                }

                // ตรวจสอบรหัสผ่านตรงกัน
                if (passwordInput.value !== confirmInput.value) {
                    event.preventDefault();
                    event.stopPropagation();
                    confirmInput.classList.add('is-invalid');
                    passwordMatchFeedback.innerHTML = '<span class="text-danger"><i class="bi bi-exclamation-circle-fill me-1"></i>รหัสผ่านไม่ตรงกัน</span>';
                }

                form.classList.add('was-validated');
            }, false);

        })();
    </script>
</body>
</html>