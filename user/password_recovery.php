<?php
session_start();

require_once __DIR__ . '/../db/db.php';
require_once __DIR__ . '/password_reset_mailer.php';

if (!isset($pdo)) {
    try {
        $pdo = new PDO("mysql:host={$servername};dbname={$dbname};charset=utf8mb4", $username, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    } catch (PDOException $e) {
        error_log('Failed to create PDO: ' . $e->getMessage());
        $error = 'ไม่สามารถเชื่อมต่อฐานข้อมูลได้';
    }
}

function generateVerificationCode(): string
{
    return (string) random_int(100000, 999999);
}

function resetPasswordRecoverySession(): void
{
    unset($_SESSION['reset_user_id'], $_SESSION['reset_email'], $_SESSION['verified'], $_SESSION['current_step']);
}

if (isset($_SESSION['error'])) {
    $error = $_SESSION['error'];
    unset($_SESSION['error']);
}

if (!isset($_SESSION['current_step'])) {
    $_SESSION['current_step'] = 'enter_email';
}

if ($_SESSION['current_step'] === 'new_password' && empty($_SESSION['verified'])) {
    $_SESSION['current_step'] = 'enter_email';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($pdo)) {
    if (isset($_POST['email'])) {
        $email = strtolower(trim((string) $_POST['email']));

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'กรุณากรอกอีเมลให้ถูกต้อง';
        } else {
            try {
                $stmt = $pdo->prepare('SELECT member_id AS id, fullname, email FROM members WHERE email = ? LIMIT 1');
                $stmt->execute([$email]);
                $user = $stmt->fetch();
            } catch (PDOException $e) {
                error_log('PDO error: ' . $e->getMessage());
                $error = 'เกิดข้อผิดพลาดในการตรวจสอบอีเมล';
                $user = false;
            }

            if (!empty($user)) {
                $verificationCode = generateVerificationCode();

                try {
                    $stmt = $pdo->prepare('UPDATE members SET verification_code = ?, code_expire = DATE_ADD(NOW(), INTERVAL 5 MINUTE) WHERE member_id = ?');
                    $stmt->execute([$verificationCode, $user['id']]);
                } catch (PDOException $e) {
                    error_log('PDO error: ' . $e->getMessage());
                    $error = 'เกิดข้อผิดพลาดในการบันทึกรหัสยืนยัน';
                }

                if (!isset($error) && sendPasswordResetCodeEmail($user['email'], $verificationCode, $user['fullname'])) {
                    $_SESSION['reset_user_id'] = $user['id'];
                    $_SESSION['reset_email'] = $user['email'];
                    $_SESSION['current_step'] = 'verify_code';
                    unset($_SESSION['verified']);
                    $_SESSION['success'] = 'เราได้ส่งรหัสยืนยัน 6 หลักไปยังอีเมลของคุณแล้ว';
                    header('Location: ' . $_SERVER['PHP_SELF']);
                    exit();
                }

                if (!isset($error)) {
                    $error = 'ไม่สามารถส่งรหัสยืนยันทางอีเมลได้ กรุณาลองอีกครั้ง';
                }
            } else {
                $error = 'ไม่พบบัญชีผู้ใช้ที่เกี่ยวข้องกับอีเมลนี้';
            }
        }
    } elseif (isset($_POST['verification_code'])) {
        $enteredCode = preg_replace('/\D/', '', (string) $_POST['verification_code']);
        $userId = $_SESSION['reset_user_id'] ?? null;

        if (!$userId) {
            $error = 'กรุณาเริ่มขั้นตอนกู้คืนรหัสผ่านใหม่';
            $_SESSION['current_step'] = 'enter_email';
        } elseif (strlen($enteredCode) !== 6) {
            $error = 'กรุณากรอกรหัสยืนยัน 6 หลัก';
        } else {
            try {
                $stmt = $pdo->prepare('SELECT member_id AS id, verification_code, code_expire FROM members WHERE member_id = ? AND verification_code IS NOT NULL LIMIT 1');
                $stmt->execute([$userId]);
                $user = $stmt->fetch();
            } catch (PDOException $e) {
                error_log('PDO error: ' . $e->getMessage());
                $error = 'เกิดข้อผิดพลาดในการตรวจสอบรหัสยืนยัน';
                $user = false;
            }

            if (!empty($user)) {
                if (empty($user['code_expire']) || strtotime($user['code_expire']) < time()) {
                    $error = 'รหัสยืนยันหมดอายุแล้ว กรุณาขอรหัสใหม่';
                    $_SESSION['current_step'] = 'enter_email';
                } elseif (hash_equals((string) $user['verification_code'], $enteredCode)) {
                    $_SESSION['verified'] = true;
                    $_SESSION['current_step'] = 'new_password';
                    header('Location: ' . $_SERVER['PHP_SELF']);
                    exit();
                } else {
                    $error = 'รหัสยืนยันไม่ถูกต้อง';
                }
            } else {
                $error = 'ไม่พบรหัสยืนยันที่ถูกต้อง กรุณาขอรหัสใหม่';
                $_SESSION['current_step'] = 'enter_email';
            }
        }
    } elseif (isset($_POST['new_password'], $_POST['confirm_password'])) {
        $userId = $_SESSION['reset_user_id'] ?? null;

        if (empty($_SESSION['verified']) || $_SESSION['current_step'] !== 'new_password' || !$userId) {
            $error = 'กรุณายืนยันรหัส OTP ก่อนตั้งรหัสผ่านใหม่';
            $_SESSION['current_step'] = 'enter_email';
        } else {
            $newPassword = (string) $_POST['new_password'];
            $confirmPassword = (string) $_POST['confirm_password'];

            if (strlen($newPassword) < 6) {
                $error = 'รหัสผ่านต้องมีอย่างน้อย 6 ตัวอักษร';
            } elseif ($newPassword !== $confirmPassword) {
                $error = 'รหัสผ่านไม่ตรงกัน';
            } else {
                try {
                    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare('UPDATE members SET password = ?, verification_code = NULL, code_expire = NULL WHERE member_id = ?');
                    $stmt->execute([$hashedPassword, $userId]);

                    resetPasswordRecoverySession();
                    $_SESSION['success'] = 'ตั้งรหัสผ่านใหม่เรียบร้อยแล้ว คุณสามารถเข้าสู่ระบบได้ตอนนี้';
                    header('Location: member_login.php');
                    exit();
                } catch (PDOException $e) {
                    error_log('PDO error: ' . $e->getMessage());
                    $error = 'เกิดข้อผิดพลาดในการอัปเดตรหัสผ่าน';
                }
            }
        }
    }
}

$currentStep = $_SESSION['current_step'] ?? 'enter_email';
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>กู้คืนรหัสผ่าน</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600;700&family=Mitr:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #4361ee;
            --success: #4cc9f0;
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

        .recovery-container {
            max-width: 500px;
            width: 100%;
            margin: 0 auto;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 15px 50px rgba(67, 97, 238, 0.25);
            animation: fadeIn 0.8s ease-out;
            background: #fff;
        }

        .recovery-header {
            background: linear-gradient(to right, var(--success-dark), var(--success-end));
            color: white;
            padding: 25px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .recovery-title {
            font-family: 'Mitr', sans-serif;
            font-weight: 700;
            font-size: 1.8rem;
            margin-bottom: 0;
            position: relative;
            z-index: 2;
        }

        .recovery-body {
            padding: 30px;
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
            padding: 14px 20px;
            border: 2px solid #e9ecef;
            transition: all 0.3s ease;
            font-size: 1rem;
        }

        .form-control:focus,
        .verification-input:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.15);
            outline: none;
        }

        .btn-submit {
            background: linear-gradient(to right, var(--success-dark), var(--success-end));
            border: none;
            border-radius: 30px;
            padding: 14px;
            font-size: 1.1rem;
            font-weight: 500;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(67, 97, 238, 0.3);
            width: 100%;
            color: white;
        }

        .btn-submit:hover {
            transform: translateY(-3px);
            box-shadow: 0 7px 20px rgba(67, 97, 238, 0.4);
        }

        .steps {
            display: flex;
            justify-content: space-between;
            margin-bottom: 48px;
            position: relative;
        }

        .steps::before {
            content: "";
            position: absolute;
            top: 20px;
            left: 0;
            right: 0;
            height: 2px;
            background: #e9ecef;
            z-index: 1;
        }

        .step {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: white;
            border: 2px solid #e9ecef;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            position: relative;
            z-index: 2;
        }

        .step.active {
            background: var(--success-end);
            color: white;
            border-color: var(--success-end);
        }

        .step.completed {
            background: var(--success);
            color: white;
            border-color: var(--success);
        }

        .step-label {
            position: absolute;
            top: 45px;
            left: 50%;
            transform: translateX(-50%);
            white-space: nowrap;
            font-size: 0.8rem;
            color: #6c757d;
        }

        .step.active .step-label {
            color: var(--success-end);
            font-weight: 500;
        }

        .verification-inputs {
            display: flex;
            justify-content: space-between;
            gap: 8px;
            margin-bottom: 25px;
        }

        .verification-input {
            width: 50px;
            height: 60px;
            text-align: center;
            font-size: 1.5rem;
            border-radius: 10px;
            border: 2px solid #e9ecef;
            transition: all 0.3s ease;
        }

        .resend-link,
        .login-link {
            text-align: center;
            margin-top: 20px;
        }

        .resend-link a,
        .login-link a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
        }

        .resend-link a:hover,
        .login-link a:hover {
            text-decoration: underline;
        }

        .alert {
            border-radius: 12px;
            padding: 12px 20px;
            margin-bottom: 20px;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>
    <div class="recovery-container shadow-lg">
        <div class="recovery-header">
            <h2 class="recovery-title"><i class="fas fa-key"></i> กู้คืนรหัสผ่าน</h2>
        </div>

        <div class="recovery-body">
            <div class="steps">
                <div class="step <?php echo ($currentStep === 'enter_email') ? 'active' : 'completed'; ?>">
                    1
                    <span class="step-label">กรอกอีเมล</span>
                </div>
                <div class="step <?php echo ($currentStep === 'verify_code') ? 'active' : (($currentStep === 'new_password') ? 'completed' : ''); ?>">
                    2
                    <span class="step-label">ยืนยันรหัส</span>
                </div>
                <div class="step <?php echo ($currentStep === 'new_password') ? 'active' : ''; ?>">
                    3
                    <span class="step-label">รหัสผ่านใหม่</span>
                </div>
            </div>

            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($_SESSION['success'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['success']); ?>
                </div>
            <?php endif; ?>

            <?php if (isset($error)): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?>
                </div>
            <?php endif; ?>

            <?php if ($currentStep === 'enter_email'): ?>
                <form method="POST" action="">
                    <div class="form-group">
                        <label class="form-label"><i class="fas fa-envelope"></i> อีเมลที่ลงทะเบียน</label>
                        <input type="email" class="form-control" name="email" required placeholder="example@domain.com" autocomplete="email">
                    </div>

                    <button type="submit" class="btn-submit">
                        <i class="fas fa-paper-plane me-2"></i> ส่งรหัสยืนยัน
                    </button>
                </form>

                <div class="login-link">
                    <a href="member_login.php"><i class="fas fa-arrow-left me-1"></i> กลับไปหน้าเข้าสู่ระบบ</a>
                </div>

            <?php elseif ($currentStep === 'verify_code'): ?>
                <p class="text-center">เราได้ส่งรหัสยืนยัน 6 หลักไปยังอีเมลของคุณแล้ว กรุณากรอกรหัสที่ได้รับ</p>

                <form method="POST" action="" id="verifyForm">
                    <div class="verification-inputs">
                        <input type="text" class="verification-input" maxlength="1" inputmode="numeric" pattern="[0-9]" required autofocus>
                        <input type="text" class="verification-input" maxlength="1" inputmode="numeric" pattern="[0-9]" required>
                        <input type="text" class="verification-input" maxlength="1" inputmode="numeric" pattern="[0-9]" required>
                        <input type="text" class="verification-input" maxlength="1" inputmode="numeric" pattern="[0-9]" required>
                        <input type="text" class="verification-input" maxlength="1" inputmode="numeric" pattern="[0-9]" required>
                        <input type="text" class="verification-input" maxlength="1" inputmode="numeric" pattern="[0-9]" required>
                    </div>

                    <input type="hidden" name="verification_code" id="fullCode">

                    <button type="submit" class="btn-submit">
                        <i class="fas fa-check-circle me-2"></i> ยืนยันรหัส
                    </button>
                </form>

                <div class="resend-link">
                    ไม่ได้รับรหัส? <a href="#" onclick="resendCode(event)">ส่งรหัสใหม่</a>
                </div>

                <script>
                    const inputs = document.querySelectorAll('.verification-input');
                    const fullCodeInput = document.getElementById('fullCode');
                    const verifyForm = document.getElementById('verifyForm');

                    function updateFullCode() {
                        fullCodeInput.value = Array.from(inputs).map(input => input.value).join('');
                    }

                    inputs.forEach((input, index) => {
                        input.addEventListener('input', function () {
                            this.value = this.value.replace(/\D/g, '').slice(0, 1);
                            if (this.value.length === 1 && index < inputs.length - 1) {
                                inputs[index + 1].focus();
                            }
                            updateFullCode();
                        });

                        input.addEventListener('keydown', function (e) {
                            if (e.key === 'Backspace' && this.value === '' && index > 0) {
                                inputs[index - 1].focus();
                            }
                        });

                        input.addEventListener('paste', function (e) {
                            const pasted = (e.clipboardData || window.clipboardData).getData('text').replace(/\D/g, '').slice(0, 6);
                            if (pasted.length > 1) {
                                e.preventDefault();
                                inputs.forEach((item, pastedIndex) => {
                                    item.value = pasted[pastedIndex] || '';
                                });
                                updateFullCode();
                                inputs[Math.min(pasted.length, inputs.length) - 1].focus();
                            }
                        });
                    });

                    verifyForm.addEventListener('submit', updateFullCode);

                    function resendCode(event) {
                        event.preventDefault();
                        if (confirm('ต้องการส่งรหัสยืนยันใหม่ไปยังอีเมลนี้ใช่หรือไม่?')) {
                            window.location.href = 'resend_code.php';
                        }
                    }
                </script>

            <?php elseif ($currentStep === 'new_password'): ?>
                <p class="text-center">กรุณาตั้งรหัสผ่านใหม่สำหรับบัญชีของคุณ</p>

                <form method="POST" action="">
                    <div class="form-group">
                        <label class="form-label"><i class="fas fa-lock"></i> รหัสผ่านใหม่</label>
                        <input type="password" class="form-control" name="new_password" id="newPassword" required minlength="6" placeholder="รหัสผ่านใหม่" autocomplete="new-password">
                    </div>

                    <div class="form-group">
                        <label class="form-label"><i class="fas fa-lock"></i> ยืนยันรหัสผ่านใหม่</label>
                        <input type="password" class="form-control" name="confirm_password" id="confirmPassword" required minlength="6" placeholder="ยืนยันรหัสผ่านใหม่" autocomplete="new-password">
                        <div id="passwordMatch" class="mt-2"></div>
                    </div>

                    <button type="submit" class="btn-submit">
                        <i class="fas fa-save me-2"></i> ตั้งรหัสผ่านใหม่
                    </button>
                </form>

                <script>
                    const newPassword = document.getElementById('newPassword');
                    const confirmPassword = document.getElementById('confirmPassword');
                    const passwordMatch = document.getElementById('passwordMatch');

                    function validatePassword() {
                        if (confirmPassword.value === '') {
                            passwordMatch.innerHTML = '';
                        } else if (newPassword.value !== confirmPassword.value) {
                            passwordMatch.innerHTML = '<div class="text-danger small"><i class="fas fa-times-circle"></i> รหัสผ่านไม่ตรงกัน</div>';
                        } else {
                            passwordMatch.innerHTML = '<div class="text-success small"><i class="fas fa-check-circle"></i> รหัสผ่านตรงกัน</div>';
                        }
                    }

                    newPassword.addEventListener('input', validatePassword);
                    confirmPassword.addEventListener('input', validatePassword);
                </script>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
